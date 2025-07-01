<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");
include_once("../reports/scores-functions.2.0.php");

header('Content-Type: application/json');

// Get parameters
$departmentId = $_POST['departmentId'] ?? 'org1';
$objectPeriod = $_POST['objectPeriod'] ?? 'months';
$objectDate = $_POST['objectDate'] ?? date("Y-m");

// Initialize response array
$response = array();

try {
    // Get team members with their performance data
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
    $teamDetails = array();
    $performanceDistribution = array('excellent' => 0, 'good' => 0, 'average' => 0, 'poor' => 0);
    
    while ($member = mysqli_fetch_assoc($teamQuery)) {
        $userId = $member['user_id'];
        
        // Get current period performance
        $currentScoreQuery = mysqli_query($connect, "
            SELECT AVG(ist.percentageCompletion) as currentScore
            FROM initiative_status ist
            INNER JOIN initiative i ON ist.initiativeId = i.id
            WHERE i.projectManager = '$userId'
            AND ist.updatedOn >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            AND ist.updatedOn = (
                SELECT MAX(ist2.updatedOn) 
                FROM initiative_status ist2 
                WHERE ist2.initiativeId = ist.initiativeId
            )
        ");
        $currentScoreResult = mysqli_fetch_assoc($currentScoreQuery);
        $currentScore = round($currentScoreResult['currentScore'] ?? 0, 1);
        
        // Get previous period performance
        $previousScoreQuery = mysqli_query($connect, "
            SELECT AVG(ist.percentageCompletion) as previousScore
            FROM initiative_status ist
            INNER JOIN initiative i ON ist.initiativeId = i.id
            WHERE i.projectManager = '$userId'
            AND ist.updatedOn >= DATE_SUB(NOW(), INTERVAL 2 MONTH)
            AND ist.updatedOn < DATE_SUB(NOW(), INTERVAL 1 MONTH)
        ");
        $previousScoreResult = mysqli_fetch_assoc($previousScoreQuery);
        $previousScore = round($previousScoreResult['previousScore'] ?? 0, 1);
        
        // Calculate trend
        $trend = $currentScore - $previousScore;
        
        // Get initiative count
        $initiativeCountQuery = mysqli_query($connect, "
            SELECT COUNT(*) as count
            FROM initiative i
            WHERE i.projectManager = '$userId'
            AND i.completionDate IS NULL
        ");
        $initiativeCountResult = mysqli_fetch_assoc($initiativeCountQuery);
        $initiativeCount = $initiativeCountResult['count'] ?? 0;
        
        // Get last update
        $lastUpdateQuery = mysqli_query($connect, "
            SELECT MAX(ist.updatedOn) as lastUpdate
            FROM initiative_status ist
            INNER JOIN initiative i ON ist.initiativeId = i.id
            WHERE i.projectManager = '$userId'
        ");
        $lastUpdateResult = mysqli_fetch_assoc($lastUpdateQuery);
        $lastUpdate = $lastUpdateResult['lastUpdate'] ?? null;
        
        // Determine status
        $status = 'active';
        if ($lastUpdate && strtotime($lastUpdate) < strtotime('-2 weeks')) {
            $status = 'attention';
        } elseif (!$lastUpdate) {
            $status = 'inactive';
        }
        
        // Determine performance category for distribution
        if ($currentScore >= 90) {
            $performanceDistribution['excellent']++;
        } elseif ($currentScore >= 75) {
            $performanceDistribution['good']++;
        } elseif ($currentScore >= 60) {
            $performanceDistribution['average']++;
        } else {
            $performanceDistribution['poor']++;
        }
        
        // Format photo path
        $photoPath = $member['photo'] ? $member['photo'] : '../images/profilePics/default.png';
        
        // Format last sign in
        $lastSignIn = 'Never';
        if ($member['last_sign_in_stamp'] && $member['last_sign_in_stamp'] > 0) {
            $lastSignIn = date('M j, Y', $member['last_sign_in_stamp']);
        }
        
        // Add to team list (for performance overview)
        $teamList[] = array(
            'user_id' => $userId,
            'name' => $member['display_name'],
            'title' => $member['title'],
            'photo' => $photoPath,
            'score' => $currentScore,
            'initiatives' => $initiativeCount,
            'status' => $status
        );
        
        // Add to detailed team data (for table)
        $teamDetails[] = array(
            'user_id' => $userId,
            'name' => $member['display_name'],
            'title' => $member['title'],
            'photo' => $photoPath,
            'currentScore' => $currentScore,
            'previousScore' => $previousScore,
            'trend' => $trend,
            'initiatives' => $initiativeCount,
            'lastUpdate' => $lastUpdate ? date('M j, Y', strtotime($lastUpdate)) : 'Never',
            'lastSignIn' => $lastSignIn,
            'status' => $status
        );
    }
    
    // Sort team list by score (descending)
    usort($teamList, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });
    
    // Sort team details by score (descending)
    usort($teamDetails, function($a, $b) {
        return $b['currentScore'] <=> $a['currentScore'];
    });
    
    // Calculate team statistics
    $totalMembers = count($teamList);
    $avgScore = $totalMembers > 0 ? array_sum(array_column($teamList, 'score')) / $totalMembers : 0;
    $topPerformers = array_filter($teamList, function($member) {
        return $member['score'] >= 80;
    });
    $needsAttention = array_filter($teamList, function($member) {
        return $member['score'] < 60 || $member['status'] === 'attention';
    });
    
    $response = array(
        'teamList' => $teamList,
        'teamDetails' => $teamDetails,
        'distribution' => $performanceDistribution,
        'statistics' => array(
            'totalMembers' => $totalMembers,
            'avgScore' => round($avgScore, 1),
            'topPerformers' => count($topPerformers),
            'needsAttention' => count($needsAttention)
        )
    );
    
    // Add individual performance insights
    $insights = array();
    
    // Top performer
    if (!empty($teamList)) {
        $topPerformer = $teamList[0];
        if ($topPerformer['score'] > 0) {
            $insights[] = array(
                'type' => 'success',
                'title' => 'Top Performer',
                'message' => $topPerformer['name'] . ' leads with ' . $topPerformer['score'] . '% performance'
            );
        }
    }
    
    // Performance improvement needed
    $lowPerformers = array_filter($teamList, function($member) {
        return $member['score'] < 50;
    });
    if (!empty($lowPerformers)) {
        $insights[] = array(
            'type' => 'warning',
            'title' => 'Performance Alert',
            'message' => count($lowPerformers) . ' team member(s) need performance improvement'
        );
    }
    
    // Inactive members
    $inactiveMembers = array_filter($teamList, function($member) {
        return $member['status'] === 'inactive';
    });
    if (!empty($inactiveMembers)) {
        $insights[] = array(
            'type' => 'info',
            'title' => 'Inactive Members',
            'message' => count($inactiveMembers) . ' team member(s) have no recent activity'
        );
    }
    
    $response['insights'] = $insights;
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching team performance: ' . $e->getMessage();
}

echo json_encode($response);
?>
