<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");
include_once("../reports/scores-functions.2.0.php");

header('Content-Type: application/json');

// Get parameters
$departmentId = $_POST['departmentId'] ?? 'org1';
$objectPeriod = $_POST['objectPeriod'] ?? 'months';
$objectDate = $_POST['objectDate'] ?? date("Y-m");
$timePeriod = $_POST['timePeriod'] ?? 'month';

// Initialize response array
$response = array();

try {
    // Calculate date range based on time period
    $dateCondition = getDateCondition($timePeriod, $objectDate);
    
    // Get average productivity (based on initiative completion rates)
    $productivityQuery = mysqli_query($connect, "
        SELECT 
            AVG(ist.percentageCompletion) as avgProductivity,
            COUNT(DISTINCT i.id) as totalInitiatives,
            COUNT(DISTINCT i.projectManager) as activeMembers
        FROM initiative_status ist
        INNER JOIN initiative i ON ist.initiativeId = i.id
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        WHERE u.department = '$departmentId'
        AND u.active = 1
        AND $dateCondition
        AND ist.updatedOn = (
            SELECT MAX(ist2.updatedOn) 
            FROM initiative_status ist2 
            WHERE ist2.initiativeId = ist.initiativeId
        )
    ");
    $productivityResult = mysqli_fetch_assoc($productivityQuery);
    $response['avgProductivity'] = round($productivityResult['avgProductivity'] ?? 0, 1);
    
    // Calculate efficiency ratio (completed vs planned tasks)
    $efficiencyQuery = mysqli_query($connect, "
        SELECT 
            AVG(CASE WHEN ist.percentageCompletion >= 100 THEN 100 ELSE ist.percentageCompletion END) as efficiency
        FROM initiative_status ist
        INNER JOIN initiative i ON ist.initiativeId = i.id
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        WHERE u.department = '$departmentId'
        AND u.active = 1
        AND $dateCondition
    ");
    $efficiencyResult = mysqli_fetch_assoc($efficiencyQuery);
    $response['efficiencyRatio'] = round($efficiencyResult['efficiency'] ?? 0, 1);
    
    // Calculate output per member (initiatives completed per person)
    $outputQuery = mysqli_query($connect, "
        SELECT 
            COUNT(DISTINCT CASE WHEN ist.percentageCompletion >= 100 THEN i.id END) as completedInitiatives,
            COUNT(DISTINCT u.user_id) as totalMembers
        FROM uc_users u
        LEFT JOIN initiative i ON i.projectManager = u.user_id
        LEFT JOIN initiative_status ist ON ist.initiativeId = i.id
        WHERE u.department = '$departmentId'
        AND u.active = 1
        AND ($dateCondition OR ist.updatedOn IS NULL)
    ");
    $outputResult = mysqli_fetch_assoc($outputQuery);
    $totalMembers = $outputResult['totalMembers'] ?? 1;
    $completedInitiatives = $outputResult['completedInitiatives'] ?? 0;
    $response['outputPerMember'] = $totalMembers > 0 ? round($completedInitiatives / $totalMembers, 1) : 0;
    
    // Calculate workload balance (distribution of tasks across team)
    $workloadQuery = mysqli_query($connect, "
        SELECT 
            u.user_id,
            COUNT(i.id) as taskCount
        FROM uc_users u
        LEFT JOIN initiative i ON i.projectManager = u.user_id AND i.completionDate IS NULL
        WHERE u.department = '$departmentId'
        AND u.active = 1
        GROUP BY u.user_id
    ");
    
    $taskCounts = array();
    while ($row = mysqli_fetch_assoc($workloadQuery)) {
        $taskCounts[] = $row['taskCount'];
    }
    
    if (!empty($taskCounts)) {
        $avgTasks = array_sum($taskCounts) / count($taskCounts);
        $variance = 0;
        foreach ($taskCounts as $count) {
            $variance += pow($count - $avgTasks, 2);
        }
        $variance = $variance / count($taskCounts);
        $standardDeviation = sqrt($variance);
        
        // Calculate balance score (lower deviation = better balance)
        $maxPossibleDeviation = $avgTasks; // Theoretical maximum
        $balanceScore = $maxPossibleDeviation > 0 ? 
            max(0, 100 - (($standardDeviation / $maxPossibleDeviation) * 100)) : 100;
        $response['workloadBalance'] = round($balanceScore, 1);
    } else {
        $response['workloadBalance'] = 100;
    }
    
    // Calculate trends (compare with previous period)
    $previousDateCondition = getPreviousDateCondition($timePeriod, $objectDate);
    
    // Previous productivity
    $prevProductivityQuery = mysqli_query($connect, "
        SELECT AVG(ist.percentageCompletion) as prevProductivity
        FROM initiative_status ist
        INNER JOIN initiative i ON ist.initiativeId = i.id
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        WHERE u.department = '$departmentId'
        AND u.active = 1
        AND $previousDateCondition
    ");
    $prevProductivityResult = mysqli_fetch_assoc($prevProductivityQuery);
    $prevProductivity = $prevProductivityResult['prevProductivity'] ?? 0;
    
    $productivityDiff = $response['avgProductivity'] - $prevProductivity;
    
    // Previous efficiency
    $prevEfficiencyQuery = mysqli_query($connect, "
        SELECT AVG(ist.percentageCompletion) as prevEfficiency
        FROM initiative_status ist
        INNER JOIN initiative i ON ist.initiativeId = i.id
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        WHERE u.department = '$departmentId'
        AND u.active = 1
        AND $previousDateCondition
    ");
    $prevEfficiencyResult = mysqli_fetch_assoc($prevEfficiencyQuery);
    $prevEfficiency = $prevEfficiencyResult['prevEfficiency'] ?? 0;
    
    $efficiencyDiff = $response['efficiencyRatio'] - $prevEfficiency;
    
    // Build trends object
    $response['trends'] = array(
        'productivity' => array(
            'direction' => $productivityDiff > 2 ? 'up' : ($productivityDiff < -2 ? 'down' : 'stable'),
            'text' => abs($productivityDiff) > 0.1 ? 
                (($productivityDiff > 0 ? '+' : '') . round($productivityDiff, 1) . '% from last period') : 
                'No significant change'
        ),
        'efficiency' => array(
            'direction' => $efficiencyDiff > 2 ? 'up' : ($efficiencyDiff < -2 ? 'down' : 'stable'),
            'text' => 'Completed vs Planned'
        ),
        'output' => array(
            'direction' => 'stable',
            'text' => 'Tasks completed'
        ),
        'workload' => array(
            'direction' => 'stable',
            'text' => 'Distribution score'
        )
    );
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching productivity metrics: ' . $e->getMessage();
}

// Helper function to get date condition based on time period
function getDateCondition($timePeriod, $objectDate) {
    switch ($timePeriod) {
        case 'week':
            return "ist.updatedOn >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
        case 'month':
            return "DATE_FORMAT(ist.updatedOn, '%Y-%m') = '" . date('Y-m', strtotime($objectDate)) . "'";
        case 'quarter':
            $quarter = ceil(date('n', strtotime($objectDate)) / 3);
            $year = date('Y', strtotime($objectDate));
            return "QUARTER(ist.updatedOn) = $quarter AND YEAR(ist.updatedOn) = $year";
        case 'year':
            return "YEAR(ist.updatedOn) = " . date('Y', strtotime($objectDate));
        default:
            return "ist.updatedOn >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    }
}

// Helper function to get previous period date condition
function getPreviousDateCondition($timePeriod, $objectDate) {
    switch ($timePeriod) {
        case 'week':
            return "ist.updatedOn >= DATE_SUB(NOW(), INTERVAL 2 WEEK) AND ist.updatedOn < DATE_SUB(NOW(), INTERVAL 1 WEEK)";
        case 'month':
            $prevMonth = date('Y-m', strtotime($objectDate . ' -1 month'));
            return "DATE_FORMAT(ist.updatedOn, '%Y-%m') = '$prevMonth'";
        case 'quarter':
            $quarter = ceil(date('n', strtotime($objectDate)) / 3);
            $year = date('Y', strtotime($objectDate));
            $prevQuarter = $quarter - 1;
            $prevYear = $year;
            if ($prevQuarter <= 0) {
                $prevQuarter = 4;
                $prevYear = $year - 1;
            }
            return "QUARTER(ist.updatedOn) = $prevQuarter AND YEAR(ist.updatedOn) = $prevYear";
        case 'year':
            $prevYear = date('Y', strtotime($objectDate)) - 1;
            return "YEAR(ist.updatedOn) = $prevYear";
        default:
            $prevMonth = date('Y-m', strtotime($objectDate . ' -1 month'));
            return "DATE_FORMAT(ist.updatedOn, '%Y-%m') = '$prevMonth'";
    }
}

echo json_encode($response);
?>
