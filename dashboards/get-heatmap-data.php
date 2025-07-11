<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");

header('Content-Type: application/json');

// Get parameters
$departmentId = $_POST['departmentId'] ?? 'org1';
$type = $_POST['type'] ?? 'overview';
$metric = $_POST['metric'] ?? 'overall';
$period = $_POST['period'] ?? 'months';
$date = $_POST['date'] ?? date("Y-m");

// Initialize response array
$response = array();

try {
    switch ($type) {
        case 'overview':
            $response = getOverviewHeatMap($departmentId, $metric, $period, $date, $connect);
            break;
        case 'team':
            $response = getTeamHeatMap($departmentId, $metric, $period, $date, $connect);
            break;
        case 'departments':
            $response = getDepartmentHeatMap($departmentId, $metric, $period, $date, $connect);
            break;
        case 'matrix':
            $response = getMatrixHeatMap($departmentId, $metric, $period, $date, $connect);
            break;
        default:
            $response['error'] = 'Invalid heat map type';
    }
} catch (Exception $e) {
    $response['error'] = 'Error generating heat map data: ' . $e->getMessage();
}

// Function to get overview heat map data
function getOverviewHeatMap($departmentId, $metric, $period, $date, $connect) {
    $heatMapData = array();
    $distribution = array();
    $summary = array();
    $hotSpots = array();
    
    // Get measures for the department
    $measuresQuery = "
        SELECT 
            m.id,
            m.name,
            CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.3score
                WHEN m.calendarType = 'Quarterly' THEN mq.3score
                WHEN m.calendarType = 'Yearly' THEN my.3score
                ELSE 0
            END as score,
            CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.actual
                WHEN m.calendarType = 'Quarterly' THEN mq.actual
                WHEN m.calendarType = 'Yearly' THEN my.actual
                ELSE 0
            END as actual,
            CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.green
                WHEN m.calendarType = 'Quarterly' THEN mq.green
                WHEN m.calendarType = 'Yearly' THEN my.green
                ELSE 0
            END as target
        FROM measure m
        LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
            AND DATE_FORMAT(mm.date, '%Y-%m') = '" . date('Y-m', strtotime($date)) . "'
        LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
            AND QUARTER(mq.date) = " . ceil(date('n', strtotime($date)) / 3) . " AND YEAR(mq.date) = " . date('Y', strtotime($date)) . "
        LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
            AND YEAR(my.date) = " . date('Y', strtotime($date)) . "
        WHERE m.linkedObject = '$departmentId'
        AND (
            (m.calendarType = 'Monthly' AND mm.3score IS NOT NULL)
            OR (m.calendarType = 'Quarterly' AND mq.3score IS NOT NULL)
            OR (m.calendarType = 'Yearly' AND my.3score IS NOT NULL)
        )
        ORDER BY m.name
        LIMIT 20
    ";
    
    $measuresResult = mysqli_query($connect, $measuresQuery);
    $scores = array();
    
    while ($measure = mysqli_fetch_assoc($measuresResult)) {
        $score = floatval($measure['score']);
        $scores[] = $score;
        
        $heatMapData[] = array(
            'id' => $measure['id'],
            'name' => $measure['name'],
            'score' => $score,
            'actual' => floatval($measure['actual']),
            'target' => floatval($measure['target']),
            'details' => "Actual: {$measure['actual']}, Target: {$measure['target']}"
        );
    }
    
    // Calculate distribution
    $distribution = array(
        'excellent' => count(array_filter($scores, function($s) { return $s >= 90; })),
        'good' => count(array_filter($scores, function($s) { return $s >= 75 && $s < 90; })),
        'average' => count(array_filter($scores, function($s) { return $s >= 60 && $s < 75; })),
        'poor' => count(array_filter($scores, function($s) { return $s >= 40 && $s < 60; })),
        'critical' => count(array_filter($scores, function($s) { return $s < 40; }))
    );
    
    // Calculate summary
    if (count($scores) > 0) {
        $summary = array(
            'avgScore' => round(array_sum($scores) / count($scores), 1),
            'topPerformers' => $distribution['excellent'],
            'needsImprovement' => $distribution['poor'] + $distribution['critical'],
            'scoreRange' => round(max($scores) - min($scores), 1)
        );
    }
    
    // Identify hot spots
    foreach ($heatMapData as $item) {
        if ($item['score'] >= 95) {
            $hotSpots[] = array(
                'area' => $item['name'],
                'type' => 'high',
                'description' => 'Exceptional performance area'
            );
        } elseif ($item['score'] < 40) {
            $hotSpots[] = array(
                'area' => $item['name'],
                'type' => 'low',
                'description' => 'Critical attention required'
            );
        }
    }
    
    return array(
        'heatMapData' => $heatMapData,
        'distribution' => $distribution,
        'summary' => $summary,
        'hotSpots' => array_slice($hotSpots, 0, 5) // Top 5 hot spots
    );
}

// Function to get team heat map data
function getTeamHeatMap($departmentId, $metric, $period, $date, $connect) {
    $teamData = array();
    $trends = array();
    $analytics = array();
    
    // Get staff performance data
    $staffQuery = "
        SELECT 
            u.user_id,
            u.display_name,
            COALESCE(u.role, 'General') as role,
            COUNT(DISTINCT i.id) as projectCount,
            AVG(ist.percentageCompletion) as avgCompletion,
            MAX(ist.updatedOn) as lastUpdate
        FROM uc_users u
        LEFT JOIN initiative i ON u.user_id = i.projectManager
        LEFT JOIN initiative_status ist ON i.id = ist.initiativeId
        WHERE u.department = '$departmentId'
        AND u.active = 1
        GROUP BY u.user_id, u.display_name, u.role
        ORDER BY u.display_name
    ";
    
    $staffResult = mysqli_query($connect, $staffQuery);
    $totalExperience = 0;
    $totalProjectLoad = 0;
    $staffCount = 0;
    
    while ($staff = mysqli_fetch_assoc($staffResult)) {
        $projectCount = intval($staff['projectCount']);
        $avgCompletion = floatval($staff['avgCompletion']);
        
        // Calculate performance score
        $score = 50; // Base score
        if ($projectCount > 0) {
            $score = $avgCompletion;
        }
        
        // Adjust based on project load
        if ($projectCount > 3) {
            $score += 10; // Bonus for handling multiple projects
        }
        
        $score = max(0, min(100, $score));
        
        $teamData[] = array(
            'id' => $staff['user_id'],
            'name' => $staff['display_name'],
            'role' => $staff['role'] ?? 'General',
            'score' => $score,
            'projectCount' => $projectCount,
            'experience' => 1.0, // Default experience since we can't calculate it
            'lastUpdate' => $staff['lastUpdate']
        );
        
        $totalExperience += 1.0; // Default experience
        $totalProjectLoad += min(100, $projectCount * 25); // Assume 25% per project
        $staffCount++;
    }
    
    // Generate trends (simplified)
    $trends = array(
        'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        'data' => [75, 78, 82, 80, 85, 88] // Sample trend data
    );
    
    // Calculate analytics
    $analytics = array(
        'teamSize' => $staffCount,
        'avgExperience' => $staffCount > 0 ? round($totalExperience / $staffCount, 1) : 0,
        'projectLoad' => $staffCount > 0 ? round($totalProjectLoad / $staffCount, 1) : 0,
        'skillGap' => 25 // Simplified skill gap calculation
    );
    
    return array(
        'teamData' => $teamData,
        'trends' => $trends,
        'analytics' => $analytics
    );
}

// Function to get department comparison heat map
function getDepartmentHeatMap($departmentId, $metric, $period, $date, $connect) {
    $departments = array();
    
    // Get all departments for comparison
    $deptQuery = "
        SELECT DISTINCT o.id, o.name
        FROM organization o
        INNER JOIN measure m ON o.id = m.linkedObject
        WHERE o.id LIKE 'org%'
        ORDER BY o.name
        LIMIT 12
    ";
    
    $deptResult = mysqli_query($connect, $deptQuery);
    
    while ($dept = mysqli_fetch_assoc($deptResult)) {
        $deptId = $dept['id'];
        
        // Get department performance
        $perfQuery = "
            SELECT 
                AVG(CASE 
                    WHEN m.calendarType = 'Monthly' THEN mm.3score
                    WHEN m.calendarType = 'Quarterly' THEN mq.3score
                    WHEN m.calendarType = 'Yearly' THEN my.3score
                    ELSE 0
                END) as avgScore,
                COUNT(DISTINCT u.user_id) as staffCount
            FROM measure m
            LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
                AND DATE_FORMAT(mm.date, '%Y-%m') = '" . date('Y-m', strtotime($date)) . "'
            LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
                AND QUARTER(mq.date) = " . ceil(date('n', strtotime($date)) / 3) . " AND YEAR(mq.date) = " . date('Y', strtotime($date)) . "
            LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
                AND YEAR(my.date) = " . date('Y', strtotime($date)) . "
            LEFT JOIN uc_users u ON u.department = m.linkedObject AND u.active = 1
            WHERE m.linkedObject = '$deptId'
            AND (
                (m.calendarType = 'Monthly' AND mm.3score IS NOT NULL)
                OR (m.calendarType = 'Quarterly' AND mq.3score IS NOT NULL)
                OR (m.calendarType = 'Yearly' AND my.3score IS NOT NULL)
            )
        ";
        
        $perfResult = mysqli_query($connect, $perfQuery);
        $perfData = mysqli_fetch_assoc($perfResult);
        
        $score = floatval($perfData['avgScore']) ?: 0;
        $staffCount = intval($perfData['staffCount']) ?: 0;
        
        $departments[] = array(
            'id' => $deptId,
            'name' => $dept['name'],
            'score' => $score,
            'staffCount' => $staffCount,
            'isCurrentDept' => $deptId === $departmentId
        );
    }
    
    return array(
        'departments' => $departments
    );
}

// Function to get matrix heat map data
function getMatrixHeatMap($departmentId, $metric, $period, $date, $connect) {
    $matrix = array();
    
    // Create a sample performance matrix
    $categories = ['Goals', 'Projects', 'Quality', 'Efficiency', 'Innovation'];
    $timeFrames = ['Q1', 'Q2', 'Q3', 'Q4'];
    
    foreach ($categories as $category) {
        $row = array('category' => $category, 'data' => array());
        foreach ($timeFrames as $timeFrame) {
            // Generate sample data (in real implementation, this would come from actual data)
            $score = rand(40, 100);
            $row['data'][] = array(
                'timeFrame' => $timeFrame,
                'score' => $score,
                'details' => "$category performance in $timeFrame: $score%"
            );
        }
        $matrix[] = $row;
    }
    
    return array(
        'matrix' => $matrix,
        'categories' => $categories,
        'timeFrames' => $timeFrames
    );
}

echo json_encode($response);
?>
