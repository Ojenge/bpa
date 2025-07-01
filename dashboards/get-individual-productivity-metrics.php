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
    
    // Get team members with comprehensive individual metrics
    $teamQuery = mysqli_query($connect, "
        SELECT 
            u.user_id,
            u.display_name,
            u.title,
            u.photo,
            u.last_sign_in_stamp,
            u.email
        FROM uc_users u
        WHERE u.department = '$departmentId'
        AND u.active = 1
        AND u.title != 'Executive Assistant'
        ORDER BY u.display_name
    ");
    
    $individuals = array();
    
    while ($member = mysqli_fetch_assoc($teamQuery)) {
        $userId = $member['user_id'];
        
        // Get comprehensive productivity metrics
        $metricsQuery = mysqli_query($connect, "
            SELECT 
                AVG(ist.percentageCompletion) as productivity,
                COUNT(DISTINCT i.id) as totalTasks,
                COUNT(DISTINCT CASE WHEN ist.percentageCompletion >= 100 THEN i.id END) as completedTasks,
                COUNT(DISTINCT CASE WHEN ist.percentageCompletion >= 50 AND ist.percentageCompletion < 100 THEN i.id END) as inProgressTasks,
                COUNT(DISTINCT CASE WHEN ist.percentageCompletion < 50 THEN i.id END) as behindTasks,
                MAX(ist.updatedOn) as lastUpdate
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
        $metricsResult = mysqli_fetch_assoc($metricsQuery);
        
        $productivityScore = round($metricsResult['productivity'] ?? 0, 1);
        $totalTasks = $metricsResult['totalTasks'] ?? 0;
        $completedTasks = $metricsResult['completedTasks'] ?? 0;
        $inProgressTasks = $metricsResult['inProgressTasks'] ?? 0;
        $behindTasks = $metricsResult['behindTasks'] ?? 0;
        $lastUpdate = $metricsResult['lastUpdate'];
        
        // Calculate efficiency ratio
        $efficiency = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
        
        // Get previous period metrics for trend calculation
        $previousMetricsQuery = mysqli_query($connect, "
            SELECT AVG(ist.percentageCompletion) as prevProductivity
            FROM initiative_status ist
            INNER JOIN initiative i ON ist.initiativeId = i.id
            WHERE i.projectManager = '$userId'
            AND $previousDateCondition
        ");
        $previousMetricsResult = mysqli_fetch_assoc($previousMetricsQuery);
        $previousProductivity = $previousMetricsResult['prevProductivity'] ?? 0;
        
        // Calculate trend
        $trend = $productivityScore - $previousProductivity;
        
        // Get average completion time
        $completionTimeQuery = mysqli_query($connect, "
            SELECT 
                AVG(DATEDIFF(i.completionDate, i.createdOn)) as avgCompletionTime,
                COUNT(*) as completedCount
            FROM initiative i
            WHERE i.projectManager = '$userId'
            AND i.completionDate IS NOT NULL
            AND i.completionDate >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        ");
        $completionTimeResult = mysqli_fetch_assoc($completionTimeQuery);
        $avgCompletionTime = round($completionTimeResult['avgCompletionTime'] ?? 0, 1);
        $completedCount = $completionTimeResult['completedCount'] ?? 0;
        
        // Get current workload
        $workloadQuery = mysqli_query($connect, "
            SELECT 
                COUNT(*) as activeInitiatives,
                SUM(CASE WHEN i.priority = 'High' THEN 1 ELSE 0 END) as highPriorityTasks,
                SUM(CASE WHEN i.priority = 'Medium' THEN 1 ELSE 0 END) as mediumPriorityTasks,
                SUM(CASE WHEN i.priority = 'Low' THEN 1 ELSE 0 END) as lowPriorityTasks
            FROM initiative i
            WHERE i.projectManager = '$userId'
            AND i.completionDate IS NULL
        ");
        $workloadResult = mysqli_fetch_assoc($workloadQuery);
        $activeInitiatives = $workloadResult['activeInitiatives'] ?? 0;
        $highPriorityTasks = $workloadResult['highPriorityTasks'] ?? 0;
        $mediumPriorityTasks = $workloadResult['mediumPriorityTasks'] ?? 0;
        $lowPriorityTasks = $workloadResult['lowPriorityTasks'] ?? 0;
        
        // Determine workload status
        $workloadStatus = 'Optimal';
        if ($activeInitiatives > 6 || $highPriorityTasks > 3) {
            $workloadStatus = 'High';
        } elseif ($activeInitiatives > 4 || $highPriorityTasks > 1) {
            $workloadStatus = 'Medium';
        } elseif ($activeInitiatives < 2) {
            $workloadStatus = 'Low';
        }
        
        // Get quality metrics (based on initiative ratings if available)
        $qualityQuery = mysqli_query($connect, "
            SELECT AVG(i.rating) as avgRating
            FROM initiative i
            WHERE i.projectManager = '$userId'
            AND i.rating IS NOT NULL
            AND i.completionDate >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        ");
        $qualityResult = mysqli_fetch_assoc($qualityQuery);
        $qualityScore = round($qualityResult['avgRating'] ?? 0, 1);
        
        // Get collaboration metrics (initiatives with multiple team members)
        $collaborationQuery = mysqli_query($connect, "
            SELECT 
                COUNT(DISTINCT i.id) as collaborativeInitiatives,
                COUNT(DISTINCT it.userId) as uniqueCollaborators
            FROM initiative i
            LEFT JOIN initiative_team it ON it.initiativeId = i.id
            WHERE i.projectManager = '$userId'
            AND i.completionDate >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
            GROUP BY i.projectManager
        ");
        $collaborationResult = mysqli_fetch_assoc($collaborationQuery);
        $collaborativeInitiatives = $collaborationResult['collaborativeInitiatives'] ?? 0;
        $uniqueCollaborators = $collaborationResult['uniqueCollaborators'] ?? 0;
        
        // Calculate collaboration score
        $collaborationScore = $totalTasks > 0 ? round(($collaborativeInitiatives / $totalTasks) * 100, 1) : 0;
        
        // Get innovation metrics (new initiatives created)
        $innovationQuery = mysqli_query($connect, "
            SELECT COUNT(*) as newInitiatives
            FROM initiative i
            WHERE i.projectManager = '$userId'
            AND i.createdOn >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
        ");
        $innovationResult = mysqli_fetch_assoc($innovationQuery);
        $newInitiatives = $innovationResult['newInitiatives'] ?? 0;
        
        // Determine activity status
        $activityStatus = 'active';
        if ($lastUpdate && strtotime($lastUpdate) < strtotime('-2 weeks')) {
            $activityStatus = 'inactive';
        } elseif (!$lastUpdate) {
            $activityStatus = 'no_activity';
        }
        
        // Calculate overall performance score
        $performanceFactors = array(
            'productivity' => $productivityScore * 0.4,
            'efficiency' => $efficiency * 0.3,
            'quality' => $qualityScore * 10 * 0.2, // Convert 5-point scale to 100-point
            'collaboration' => $collaborationScore * 0.1
        );
        $overallScore = array_sum($performanceFactors);
        
        // Format photo path
        $photoPath = $member['photo'] ? $member['photo'] : '../images/profilePics/default.png';
        
        // Add to individuals array
        $individuals[] = array(
            'userId' => $userId,
            'name' => $member['display_name'],
            'title' => $member['title'],
            'email' => $member['email'],
            'photo' => $photoPath,
            'productivityScore' => $productivityScore,
            'efficiency' => $efficiency,
            'overallScore' => round($overallScore, 1),
            'trend' => $trend,
            'tasksCompleted' => $completedTasks,
            'totalTasks' => $totalTasks,
            'inProgressTasks' => $inProgressTasks,
            'behindTasks' => $behindTasks,
            'activeInitiatives' => $activeInitiatives,
            'workloadStatus' => $workloadStatus,
            'workloadBreakdown' => array(
                'high' => $highPriorityTasks,
                'medium' => $mediumPriorityTasks,
                'low' => $lowPriorityTasks
            ),
            'avgCompletionTime' => $avgCompletionTime,
            'qualityScore' => $qualityScore,
            'collaborationScore' => $collaborationScore,
            'uniqueCollaborators' => $uniqueCollaborators,
            'newInitiatives' => $newInitiatives,
            'lastUpdate' => $lastUpdate ? date('M j, Y', strtotime($lastUpdate)) : 'Never',
            'activityStatus' => $activityStatus,
            'performanceFactors' => $performanceFactors
        );
    }
    
    // Sort individuals by overall score (descending)
    usort($individuals, function($a, $b) {
        return $b['overallScore'] <=> $a['overallScore'];
    });
    
    $response = array(
        'individuals' => $individuals,
        'summary' => array(
            'totalIndividuals' => count($individuals),
            'avgProductivity' => count($individuals) > 0 ? round(array_sum(array_column($individuals, 'productivityScore')) / count($individuals), 1) : 0,
            'avgEfficiency' => count($individuals) > 0 ? round(array_sum(array_column($individuals, 'efficiency')) / count($individuals), 1) : 0,
            'avgOverallScore' => count($individuals) > 0 ? round(array_sum(array_column($individuals, 'overallScore')) / count($individuals), 1) : 0,
            'topPerformers' => count(array_filter($individuals, function($ind) { return $ind['overallScore'] >= 80; })),
            'needsAttention' => count(array_filter($individuals, function($ind) { return $ind['overallScore'] < 60 || $ind['activityStatus'] !== 'active'; }))
        )
    );
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching individual productivity metrics: ' . $e->getMessage();
}

// Helper functions
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
