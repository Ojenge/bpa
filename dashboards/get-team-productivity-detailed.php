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
    $previousDateCondition = getPreviousDateCondition($timePeriod, $objectDate);
    
    // Get team members with detailed productivity metrics
    $teamQuery = mysqli_query($connect, "
        SELECT 
            u.user_id,
            u.display_name,
            u.title,
            u.photo,
            u.last_sign_in_stamp
        FROM uc_users u
        WHERE u.department = '$departmentId'
        AND u.active = 1
        AND u.title != 'Executive Assistant'
        ORDER BY u.display_name
    ");
    
    $teamList = array();
    $distributionData = array('high' => 0, 'good' => 0, 'average' => 0, 'poor' => 0);
    
    while ($member = mysqli_fetch_assoc($teamQuery)) {
        $userId = $member['user_id'];
        
        // Get current period productivity score
        $currentProductivityQuery = mysqli_query($connect, "
            SELECT 
                AVG(ist.percentageCompletion) as productivity,
                COUNT(DISTINCT i.id) as totalTasks,
                COUNT(DISTINCT CASE WHEN ist.percentageCompletion >= 100 THEN i.id END) as completedTasks
            FROM initiative_status ist
            INNER JOIN initiative i ON ist.initiativeId = i.id
            WHERE i.projectManager = '$userId'
            AND $dateCondition
            AND ist.updatedOn = (
                SELECT MAX(ist2.updatedOn) 
                FROM initiative_status ist2 
                WHERE ist2.initiativeId = ist.initiativeId
            )
        ");
        $currentProductivityResult = mysqli_fetch_assoc($currentProductivityQuery);
        $productivityScore = round($currentProductivityResult['productivity'] ?? 0, 1);
        $totalTasks = $currentProductivityResult['totalTasks'] ?? 0;
        $completedTasks = $currentProductivityResult['completedTasks'] ?? 0;
        
        // Calculate efficiency ratio
        $efficiency = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
        
        // Get previous period productivity for trend calculation
        $previousProductivityQuery = mysqli_query($connect, "
            SELECT AVG(ist.percentageCompletion) as prevProductivity
            FROM initiative_status ist
            INNER JOIN initiative i ON ist.initiativeId = i.id
            WHERE i.projectManager = '$userId'
            AND $previousDateCondition
        ");
        $previousProductivityResult = mysqli_fetch_assoc($previousProductivityQuery);
        $previousProductivity = $previousProductivityResult['prevProductivity'] ?? 0;
        
        // Calculate trend
        $trend = $productivityScore - $previousProductivity;
        
        // Get current workload (active initiatives)
        $workloadQuery = mysqli_query($connect, "
            SELECT COUNT(*) as activeInitiatives
            FROM initiative i
            WHERE i.projectManager = '$userId'
            AND i.completionDate IS NULL
        ");
        $workloadResult = mysqli_fetch_assoc($workloadQuery);
        $activeInitiatives = $workloadResult['activeInitiatives'] ?? 0;
        
        // Determine workload status
        $workloadStatus = 'Optimal';
        if ($activeInitiatives > 5) {
            $workloadStatus = 'High';
        } elseif ($activeInitiatives > 3) {
            $workloadStatus = 'Medium';
        } elseif ($activeInitiatives < 2) {
            $workloadStatus = 'Low';
        }
        
        // Get average completion time (days between initiative start and completion)
        $completionTimeQuery = mysqli_query($connect, "
            SELECT AVG(DATEDIFF(i.completionDate, i.createdOn)) as avgCompletionTime
            FROM initiative i
            WHERE i.projectManager = '$userId'
            AND i.completionDate IS NOT NULL
            AND i.completionDate >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        ");
        $completionTimeResult = mysqli_fetch_assoc($completionTimeQuery);
        $avgCompletionTime = round($completionTimeResult['avgCompletionTime'] ?? 0, 1);
        
        // Get last activity
        $lastActivityQuery = mysqli_query($connect, "
            SELECT MAX(ist.updatedOn) as lastActivity
            FROM initiative_status ist
            INNER JOIN initiative i ON ist.initiativeId = i.id
            WHERE i.projectManager = '$userId'
        ");
        $lastActivityResult = mysqli_fetch_assoc($lastActivityQuery);
        $lastActivity = $lastActivityResult['lastActivity'] ?? null;
        
        // Determine activity status
        $activityStatus = 'active';
        if ($lastActivity && strtotime($lastActivity) < strtotime('-2 weeks')) {
            $activityStatus = 'inactive';
        } elseif (!$lastActivity) {
            $activityStatus = 'no_activity';
        }
        
        // Categorize performance for distribution
        if ($productivityScore >= 85) {
            $distributionData['high']++;
        } elseif ($productivityScore >= 70) {
            $distributionData['good']++;
        } elseif ($productivityScore >= 50) {
            $distributionData['average']++;
        } else {
            $distributionData['poor']++;
        }
        
        // Format photo path
        $photoPath = $member['photo'] ? $member['photo'] : '../images/profilePics/default.png';
        
        // Add to team list
        $teamList[] = array(
            'userId' => $userId,
            'name' => $member['display_name'],
            'title' => $member['title'],
            'photo' => $photoPath,
            'productivityScore' => $productivityScore,
            'efficiency' => $efficiency,
            'trend' => $trend,
            'tasksCompleted' => $completedTasks,
            'totalTasks' => $totalTasks,
            'activeInitiatives' => $activeInitiatives,
            'workloadStatus' => $workloadStatus,
            'avgCompletionTime' => $avgCompletionTime,
            'lastActivity' => $lastActivity ? date('M j, Y', strtotime($lastActivity)) : 'Never',
            'activityStatus' => $activityStatus
        );
    }
    
    // Sort team list by productivity score (descending)
    usort($teamList, function($a, $b) {
        return $b['productivityScore'] <=> $a['productivityScore'];
    });
    
    // Calculate team statistics
    $totalMembers = count($teamList);
    $avgProductivity = $totalMembers > 0 ? array_sum(array_column($teamList, 'productivityScore')) / $totalMembers : 0;
    $avgEfficiency = $totalMembers > 0 ? array_sum(array_column($teamList, 'efficiency')) / $totalMembers : 0;
    $totalCompletedTasks = array_sum(array_column($teamList, 'tasksCompleted'));
    $totalActiveTasks = array_sum(array_column($teamList, 'totalTasks'));
    
    // Get top performers and those needing attention
    $topPerformers = array_filter($teamList, function($member) {
        return $member['productivityScore'] >= 80;
    });
    
    $needsAttention = array_filter($teamList, function($member) {
        return $member['productivityScore'] < 60 || $member['activityStatus'] !== 'active';
    });
    
    $response = array(
        'teamList' => $teamList,
        'distribution' => $distributionData,
        'statistics' => array(
            'totalMembers' => $totalMembers,
            'avgProductivity' => round($avgProductivity, 1),
            'avgEfficiency' => round($avgEfficiency, 1),
            'totalCompletedTasks' => $totalCompletedTasks,
            'totalActiveTasks' => $totalActiveTasks,
            'topPerformers' => count($topPerformers),
            'needsAttention' => count($needsAttention)
        )
    );
    
    // Add productivity insights
    $insights = array();
    
    // Top performer insight
    if (!empty($teamList)) {
        $topPerformer = $teamList[0];
        if ($topPerformer['productivityScore'] > 0) {
            $insights[] = array(
                'type' => 'success',
                'title' => 'Top Performer',
                'message' => $topPerformer['name'] . ' leads with ' . $topPerformer['productivityScore'] . '% productivity'
            );
        }
    }
    
    // Efficiency insight
    if ($avgEfficiency > 0) {
        $insights[] = array(
            'type' => 'info',
            'title' => 'Team Efficiency',
            'message' => 'Average efficiency is ' . round($avgEfficiency, 1) . '% across the team'
        );
    }
    
    // Workload balance insight
    $overloadedMembers = array_filter($teamList, function($member) {
        return $member['workloadStatus'] === 'High';
    });
    
    if (!empty($overloadedMembers)) {
        $insights[] = array(
            'type' => 'warning',
            'title' => 'Workload Alert',
            'message' => count($overloadedMembers) . ' team member(s) may be overloaded'
        );
    }
    
    // Inactive members insight
    $inactiveMembers = array_filter($teamList, function($member) {
        return $member['activityStatus'] !== 'active';
    });
    
    if (!empty($inactiveMembers)) {
        $insights[] = array(
            'type' => 'warning',
            'title' => 'Activity Alert',
            'message' => count($inactiveMembers) . ' team member(s) have low recent activity'
        );
    }
    
    $response['insights'] = $insights;
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching team productivity details: ' . $e->getMessage();
}

// Helper functions (same as in get-productivity-metrics.php)
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
