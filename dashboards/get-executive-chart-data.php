<?php
include_once("../config/config_mysqli.php");
include_once("../admin/models/config.php");
include_once("../functions/functions.php");
include_once("../functions/calendar-labels.php");
include_once("../functions/perspOrg-scores.php");

date_default_timezone_set('Africa/Nairobi');

// Get parameters
$chartType = $_POST['chartType'] ?? 'performance_trends';
$objectPeriod = $_POST['objectPeriod'] ?? 'months';
$objectDate = $_POST['objectDate'] ?? date("Y-m");

// Set default date if not provided
if (!$objectDate) $objectDate = date("Y-m");
$objectDate = date("Y-m-d",strtotime($objectDate."-30"));

header('Content-Type: application/json');

try {
    switch($chartType) {
        case 'performance_trends':
            echo json_encode(getPerformanceTrendsData($connect, $objectPeriod, $objectDate));
            break;
        case 'department_performance':
            echo json_encode(getDepartmentPerformanceData($connect, $objectPeriod, $objectDate));
            break;
        case 'initiative_status':
            echo json_encode(getInitiativeStatusData($connect, $objectPeriod, $objectDate));
            break;
        default:
            echo json_encode(['error' => 'Invalid chart type']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function getPerformanceTrendsData($connect, $objectPeriod, $objectDate) {
    // Get performance data for the last 6 months
    $data = [];
    $labels = [];
    
    for ($i = 5; $i >= 0; $i--) {
        $date = date("Y-m", strtotime($objectDate . " - $i months"));
        $labels[] = date("M Y", strtotime($date));
        
        // Calculate average performance for this month
        $query = "SELECT AVG(o.percentageCompletion) AS avgPerformance
                  FROM `initiative_status` o                    
                  LEFT JOIN `initiative_status` b             
                  ON o.initiativeId = b.initiativeId 
                  AND o.updatedOn < b.updatedOn
                  WHERE b.updatedOn is NULL                 
                  AND o.updatedOn <= '$date-30'
                  AND o.percentageCompletion != 0";
        
        $result = mysqli_query($connect, $query);
        $row = mysqli_fetch_array($result);
        
        $data[] = $row['avgPerformance'] ? round($row['avgPerformance'], 1) : 0;
    }
    
    return [
        'labels' => $labels,
        'datasets' => [[
            'label' => 'Average Performance',
            'data' => $data,
            'borderColor' => '#667eea',
            'backgroundColor' => 'rgba(102, 126, 234, 0.1)',
            'tension' => 0.4
        ]]
    ];
}

function getDepartmentPerformanceData($connect, $objectPeriod, $objectDate) {
    // Get department performance data
    $query = "SELECT organization.name, AVG(o.percentageCompletion) AS avgPerformance
              FROM `initiative_status` o                    
              LEFT JOIN `initiative_status` b             
              ON o.initiativeId = b.initiativeId 
              AND o.updatedOn < b.updatedOn
              JOIN initiative i ON o.initiativeId = i.id
              JOIN uc_users u ON i.projectManager = u.user_id
              JOIN organization ON u.department = organization.id
              WHERE b.updatedOn is NULL                 
              AND o.updatedOn <= '$objectDate'
              AND o.percentageCompletion != 0
              GROUP BY organization.id, organization.name
              ORDER BY avgPerformance DESC";
    
    $result = mysqli_query($connect, $query);
    
    $labels = [];
    $data = [];
    $colors = [
        'rgba(102, 126, 234, 0.8)',
        'rgba(118, 75, 162, 0.8)',
        'rgba(255, 193, 7, 0.8)',
        'rgba(40, 167, 69, 0.8)',
        'rgba(220, 53, 69, 0.8)',
        'rgba(23, 162, 184, 0.8)'
    ];
    
    $colorIndex = 0;
    while ($row = mysqli_fetch_array($result)) {
        $labels[] = $row['name'];
        $data[] = round($row['avgPerformance'], 1);
        $colorIndex++;
    }
    
    return [
        'labels' => $labels,
        'datasets' => [[
            'label' => 'Performance Score',
            'data' => $data,
            'backgroundColor' => array_slice($colors, 0, count($data))
        ]]
    ];
}

function getInitiativeStatusData($connect, $objectPeriod, $objectDate) {
    // Get initiative status distribution
    $query = "SELECT 
                CASE 
                    WHEN o.percentageCompletion >= 90 THEN 'Completed'
                    WHEN o.percentageCompletion >= 75 THEN 'On Track'
                    WHEN o.percentageCompletion >= 50 THEN 'At Risk'
                    ELSE 'Delayed'
                END AS status,
                COUNT(*) as count
              FROM `initiative_status` o                    
              LEFT JOIN `initiative_status` b             
              ON o.initiativeId = b.initiativeId 
              AND o.updatedOn < b.updatedOn
              WHERE b.updatedOn is NULL                 
              AND o.updatedOn <= '$objectDate'
              AND o.percentageCompletion != 0
              GROUP BY status
              ORDER BY count DESC";
    
    $result = mysqli_query($connect, $query);
    
    $labels = [];
    $data = [];
    $colors = [
        '#28a745', // Completed - Green
        '#17a2b8', // On Track - Blue
        '#ffc107', // At Risk - Yellow
        '#dc3545'  // Delayed - Red
    ];
    
    $colorIndex = 0;
    while ($row = mysqli_fetch_array($result)) {
        $labels[] = $row['status'];
        $data[] = (int)$row['count'];
        $colorIndex++;
    }
    
    return [
        'labels' => $labels,
        'datasets' => [[
            'data' => $data,
            'backgroundColor' => array_slice($colors, 0, count($data))
        ]]
    ];
}
?> 