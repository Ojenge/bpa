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
    // Get overall achievement based on KPI scores
    $achievementQuery = mysqli_query($connect, "
        SELECT 
            AVG(CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.3score
                WHEN m.calendarType = 'Quarterly' THEN mq.3score
                WHEN m.calendarType = 'Yearly' THEN my.3score
                ELSE 0
            END) as avgScore
        FROM measure m
        LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
        LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
        LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
        WHERE m.linkedObject = '$departmentId'
        AND (
            (m.calendarType = 'Monthly' AND DATE_FORMAT(mm.date, '%Y-%m') = '" . date('Y-m', strtotime($objectDate)) . "')
            OR (m.calendarType = 'Quarterly' AND QUARTER(mq.date) = " . ceil(date('n', strtotime($objectDate)) / 3) . " AND YEAR(mq.date) = " . date('Y', strtotime($objectDate)) . ")
            OR (m.calendarType = 'Yearly' AND YEAR(my.date) = " . date('Y', strtotime($objectDate)) . ")
        )
    ");
    $achievementResult = mysqli_fetch_assoc($achievementQuery);
    $overallAchievement = round(($achievementResult['avgScore'] ?? 0) * 20, 1); // Convert 5-point scale to percentage
    $response['overallAchievement'] = $overallAchievement;
    
    // Count goals on track (score >= 3)
    $onTrackQuery = mysqli_query($connect, "
        SELECT COUNT(*) as onTrackCount
        FROM measure m
        LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
        LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
        LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
        WHERE m.linkedObject = '$departmentId'
        AND (
            (m.calendarType = 'Monthly' AND mm.3score >= 3 AND DATE_FORMAT(mm.date, '%Y-%m') = '" . date('Y-m', strtotime($objectDate)) . "')
            OR (m.calendarType = 'Quarterly' AND mq.3score >= 3 AND QUARTER(mq.date) = " . ceil(date('n', strtotime($objectDate)) / 3) . " AND YEAR(mq.date) = " . date('Y', strtotime($objectDate)) . ")
            OR (m.calendarType = 'Yearly' AND my.3score >= 3 AND YEAR(my.date) = " . date('Y', strtotime($objectDate)) . ")
        )
    ");
    $onTrackResult = mysqli_fetch_assoc($onTrackQuery);
    $response['goalsOnTrack'] = $onTrackResult['onTrackCount'] ?? 0;
    
    // Count milestones completed (initiatives completed this period)
    $milestonesQuery = mysqli_query($connect, "
        SELECT COUNT(*) as completedCount
        FROM initiative i
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        WHERE u.department = '$departmentId'
        AND i.completionDate IS NOT NULL
        AND DATE_FORMAT(i.completionDate, '%Y-%m') = '" . date('Y-m', strtotime($objectDate)) . "'
    ");
    $milestonesResult = mysqli_fetch_assoc($milestonesQuery);
    $response['milestonesCompleted'] = $milestonesResult['completedCount'] ?? 0;
    
    // Calculate forecast accuracy (based on historical performance vs targets)
    $forecastQuery = mysqli_query($connect, "
        SELECT 
            AVG(ABS(
                CASE 
                    WHEN m.calendarType = 'Monthly' THEN mm.actual - mm.green
                    WHEN m.calendarType = 'Quarterly' THEN mq.actual - mq.green
                    WHEN m.calendarType = 'Yearly' THEN my.actual - my.green
                    ELSE 0
                END
            ) / NULLIF(
                CASE 
                    WHEN m.calendarType = 'Monthly' THEN mm.green
                    WHEN m.calendarType = 'Quarterly' THEN mq.green
                    WHEN m.calendarType = 'Yearly' THEN my.green
                    ELSE 1
                END, 0
            )) as avgDeviation
        FROM measure m
        LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
        LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
        LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
        WHERE m.linkedObject = '$departmentId'
        AND (
            (m.calendarType = 'Monthly' AND mm.date >= DATE_SUB(NOW(), INTERVAL 6 MONTH))
            OR (m.calendarType = 'Quarterly' AND mq.date >= DATE_SUB(NOW(), INTERVAL 18 MONTH))
            OR (m.calendarType = 'Yearly' AND my.date >= DATE_SUB(NOW(), INTERVAL 3 YEAR))
        )
    ");
    $forecastResult = mysqli_fetch_assoc($forecastQuery);
    $avgDeviation = $forecastResult['avgDeviation'] ?? 0;
    $forecastAccuracy = max(0, 100 - ($avgDeviation * 100));
    $response['forecastAccuracy'] = round($forecastAccuracy, 1);
    
    // Build forecasts object with trend indicators
    $response['forecasts'] = array(
        'achievement' => array(
            'trend' => $overallAchievement >= 80 ? 'positive' : ($overallAchievement >= 60 ? 'neutral' : 'negative'),
            'icon' => 'fas fa-chart-line',
            'text' => $overallAchievement >= 80 ? 'On track for targets' : 'May need intervention'
        ),
        'onTrack' => array(
            'trend' => 'positive',
            'icon' => 'fas fa-check-circle',
            'text' => 'Meeting expectations'
        ),
        'milestones' => array(
            'trend' => 'neutral',
            'icon' => 'fas fa-flag-checkered',
            'text' => 'This period'
        ),
        'accuracy' => array(
            'trend' => $forecastAccuracy >= 80 ? 'positive' : 'neutral',
            'icon' => 'fas fa-target',
            'text' => 'Prediction quality'
        )
    );
    
    // Get additional metrics for breakdown
    $totalGoalsQuery = mysqli_query($connect, "
        SELECT COUNT(*) as totalGoals
        FROM measure m
        WHERE m.linkedObject = '$departmentId'
    ");
    $totalGoalsResult = mysqli_fetch_assoc($totalGoalsQuery);
    $totalGoals = $totalGoalsResult['totalGoals'] ?? 0;
    
    $atRiskGoals = max(0, $totalGoals - $response['goalsOnTrack']);
    
    $response['breakdown'] = array(
        'onTrack' => $response['goalsOnTrack'],
        'atRisk' => $atRiskGoals,
        'total' => $totalGoals
    );
    
    // Get department objectives progress
    $objectivesQuery = mysqli_query($connect, "
        SELECT 
            o.id,
            o.name,
            o.outcome,
            AVG(CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.3score
                WHEN m.calendarType = 'Quarterly' THEN mq.3score
                WHEN m.calendarType = 'Yearly' THEN my.3score
                ELSE 0
            END) as avgScore
        FROM objective o
        LEFT JOIN measure m ON m.linkedObject = o.id
        LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
        LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
        LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
        WHERE o.linkedObject = '$departmentId'
        GROUP BY o.id, o.name, o.outcome
    ");
    
    $objectives = array();
    while ($obj = mysqli_fetch_assoc($objectivesQuery)) {
        $objectives[] = array(
            'id' => $obj['id'],
            'name' => $obj['name'],
            'outcome' => $obj['outcome'],
            'progress' => round(($obj['avgScore'] ?? 0) * 20, 1) // Convert to percentage
        );
    }
    $response['objectives'] = $objectives;
    
    // Get recent achievements
    $achievementsQuery = mysqli_query($connect, "
        SELECT 
            i.name as initiativeName,
            i.completionDate,
            u.display_name as managerName
        FROM initiative i
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        WHERE u.department = '$departmentId'
        AND i.completionDate IS NOT NULL
        AND i.completionDate >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        ORDER BY i.completionDate DESC
        LIMIT 5
    ");
    
    $achievements = array();
    while ($achievement = mysqli_fetch_assoc($achievementsQuery)) {
        $achievements[] = array(
            'name' => $achievement['initiativeName'],
            'completionDate' => date('M j, Y', strtotime($achievement['completionDate'])),
            'manager' => $achievement['managerName']
        );
    }
    $response['recentAchievements'] = $achievements;
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching goal achievement metrics: ' . $e->getMessage();
}

echo json_encode($response);
?>
