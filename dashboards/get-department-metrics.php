<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");
include_once("../functions/calendar-labels.php");
include_once("../functions/perspOrg-scores.php");
include_once("../reports/scores-functions.2.0.php");

header('Content-Type: application/json');

// Get parameters
$departmentId = $_POST['departmentId'] ?? 'org1';
$objectPeriod = $_POST['objectPeriod'] ?? 'months';
$objectDate = $_POST['objectDate'] ?? date("Y-m");

// Initialize response array
$response = array();

try {
    // Get overall department score
    $overallScore = getOrgScore($departmentId);
    if ($overallScore === null || $overallScore === '') {
        $overallScore = 0;
    }
    $response['overallScore'] = round($overallScore, 1);
    
    // Get team size (number of staff in department)
    $teamQuery = mysqli_query($connect, "
        SELECT COUNT(*) as count 
        FROM uc_users 
        WHERE department = '$departmentId' 
        AND active = 1 
        AND title != 'Executive Assistant'
    ");
    $teamResult = mysqli_fetch_assoc($teamQuery);
    $response['teamSize'] = $teamResult['count'] ?? 0;
    
    // Get active initiatives count
    $initiativesQuery = mysqli_query($connect, "
        SELECT COUNT(DISTINCT i.id) as count 
        FROM initiative i
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        WHERE u.department = '$departmentId'
        AND i.completionDate IS NULL
    ");
    $initiativesResult = mysqli_fetch_assoc($initiativesQuery);
    $response['activeInitiatives'] = $initiativesResult['count'] ?? 0;
    
    // Calculate completion rate for current month
    $currentMonth = date('Y-m', strtotime($objectDate));
    $completionQuery = mysqli_query($connect, "
        SELECT 
            AVG(ist.percentageCompletion) as avgCompletion
        FROM initiative_status ist
        INNER JOIN initiative i ON ist.initiativeId = i.id
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        WHERE u.department = '$departmentId'
        AND DATE_FORMAT(ist.updatedOn, '%Y-%m') = '$currentMonth'
        AND ist.updatedOn = (
            SELECT MAX(ist2.updatedOn) 
            FROM initiative_status ist2 
            WHERE ist2.initiativeId = ist.initiativeId
        )
    ");
    $completionResult = mysqli_fetch_assoc($completionQuery);
    $response['completionRate'] = round($completionResult['avgCompletion'] ?? 0, 1);
    
    // Calculate average performance
    $avgPerformanceQuery = mysqli_query($connect, "
        SELECT AVG(score) as avgScore
        FROM (
            SELECT 
                u.user_id,
                AVG(ist.percentageCompletion) as score
            FROM uc_users u
            LEFT JOIN initiative i ON i.projectManager = u.user_id
            LEFT JOIN initiative_status ist ON ist.initiativeId = i.id
            WHERE u.department = '$departmentId'
            AND u.active = 1
            AND ist.updatedOn >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            GROUP BY u.user_id
        ) as user_scores
    ");
    $avgPerformanceResult = mysqli_fetch_assoc($avgPerformanceQuery);
    $response['avgPerformance'] = round($avgPerformanceResult['avgScore'] ?? 0, 1);
    
    // Count top performers (score >= 80%)
    $topPerformersQuery = mysqli_query($connect, "
        SELECT COUNT(*) as count
        FROM (
            SELECT 
                u.user_id,
                AVG(ist.percentageCompletion) as score
            FROM uc_users u
            LEFT JOIN initiative i ON i.projectManager = u.user_id
            LEFT JOIN initiative_status ist ON ist.initiativeId = i.id
            WHERE u.department = '$departmentId'
            AND u.active = 1
            AND ist.updatedOn >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            GROUP BY u.user_id
            HAVING score >= 80
        ) as top_performers
    ");
    $topPerformersResult = mysqli_fetch_assoc($topPerformersQuery);
    $response['topPerformers'] = $topPerformersResult['count'] ?? 0;
    
    // Count staff needing attention (score < 60% or no recent updates)
    $needsAttentionQuery = mysqli_query($connect, "
        SELECT COUNT(*) as count
        FROM (
            SELECT 
                u.user_id,
                COALESCE(AVG(ist.percentageCompletion), 0) as score,
                MAX(ist.updatedOn) as lastUpdate
            FROM uc_users u
            LEFT JOIN initiative i ON i.projectManager = u.user_id
            LEFT JOIN initiative_status ist ON ist.initiativeId = i.id
            WHERE u.department = '$departmentId'
            AND u.active = 1
            GROUP BY u.user_id
            HAVING score < 60 OR lastUpdate < DATE_SUB(NOW(), INTERVAL 2 WEEK) OR lastUpdate IS NULL
        ) as needs_attention
    ");
    $needsAttentionResult = mysqli_fetch_assoc($needsAttentionQuery);
    $response['needsAttention'] = $needsAttentionResult['count'] ?? 0;
    
    // Calculate trends (compare with previous period)
    $previousMonth = date('Y-m', strtotime($objectDate . ' -1 month'));
    
    // Previous period overall score
    $prevScoreQuery = mysqli_query($connect, "
        SELECT AVG(ist.percentageCompletion) as prevScore
        FROM initiative_status ist
        INNER JOIN initiative i ON ist.initiativeId = i.id
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        WHERE u.department = '$departmentId'
        AND DATE_FORMAT(ist.updatedOn, '%Y-%m') = '$previousMonth'
    ");
    $prevScoreResult = mysqli_fetch_assoc($prevScoreQuery);
    $prevScore = $prevScoreResult['prevScore'] ?? 0;
    
    $scoreDiff = $response['completionRate'] - $prevScore;
    
    // Build trends object
    $response['trends'] = array(
        'score' => array(
            'direction' => $scoreDiff > 2 ? 'up' : ($scoreDiff < -2 ? 'down' : 'neutral'),
            'text' => abs($scoreDiff) > 0.1 ? 
                (($scoreDiff > 0 ? '+' : '') . round($scoreDiff, 1) . '% from last period') : 
                'No significant change'
        ),
        'team' => array(
            'direction' => 'neutral',
            'text' => 'Active staff count'
        ),
        'initiatives' => array(
            'direction' => 'neutral',
            'text' => 'In progress'
        ),
        'completion' => array(
            'direction' => $scoreDiff > 0 ? 'up' : ($scoreDiff < 0 ? 'down' : 'neutral'),
            'text' => 'This month'
        )
    );
    
    // Add department ranking
    $rankingQuery = mysqli_query($connect, "
        SELECT 
            o.id,
            o.name,
            AVG(ist.percentageCompletion) as deptScore
        FROM organization o
        LEFT JOIN uc_users u ON u.department = o.id
        LEFT JOIN initiative i ON i.projectManager = u.user_id
        LEFT JOIN initiative_status ist ON ist.initiativeId = i.id
        WHERE o.id != 'org0'
        AND ist.updatedOn >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        GROUP BY o.id, o.name
        ORDER BY deptScore DESC
    ");
    
    $ranking = array();
    $currentRank = 1;
    $userDeptRank = 0;
    
    while ($row = mysqli_fetch_assoc($rankingQuery)) {
        if ($row['id'] == $departmentId) {
            $userDeptRank = $currentRank;
        }
        $ranking[] = array(
            'rank' => $currentRank,
            'name' => $row['name'],
            'score' => round($row['deptScore'] ?? 0, 1),
            'isCurrentDept' => $row['id'] == $departmentId
        );
        $currentRank++;
    }
    
    $response['ranking'] = $ranking;
    $response['currentRank'] = $userDeptRank;
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching department metrics: ' . $e->getMessage();
}

echo json_encode($response);
?>
