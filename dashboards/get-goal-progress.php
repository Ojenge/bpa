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
    // Get department goals (measures) with their progress
    $goalsQuery = mysqli_query($connect, "
        SELECT 
            m.id,
            m.name,
            m.calendarType,
            m.gaugeType,
            m.red,
            m.blue,
            m.green,
            m.darkGreen,
            CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.actual
                WHEN m.calendarType = 'Quarterly' THEN mq.actual
                WHEN m.calendarType = 'Yearly' THEN my.actual
                ELSE NULL
            END as actual,
            CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.green
                WHEN m.calendarType = 'Quarterly' THEN mq.green
                WHEN m.calendarType = 'Yearly' THEN my.green
                ELSE m.green
            END as target,
            CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.3score
                WHEN m.calendarType = 'Quarterly' THEN mq.3score
                WHEN m.calendarType = 'Yearly' THEN my.3score
                ELSE 0
            END as score,
            CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.date
                WHEN m.calendarType = 'Quarterly' THEN mq.date
                WHEN m.calendarType = 'Yearly' THEN my.date
                ELSE NULL
            END as lastUpdate
        FROM measure m
        LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
            AND DATE_FORMAT(mm.date, '%Y-%m') = '" . date('Y-m', strtotime($objectDate)) . "'
        LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
            AND QUARTER(mq.date) = " . ceil(date('n', strtotime($objectDate)) / 3) . " 
            AND YEAR(mq.date) = " . date('Y', strtotime($objectDate)) . "
        LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
            AND YEAR(my.date) = " . date('Y', strtotime($objectDate)) . "
        WHERE m.linkedObject = '$departmentId'
        ORDER BY m.name
    ");
    
    $goals = array();
    $totalAchievement = 0;
    $goalCount = 0;
    $onTrackCount = 0;
    $atRiskCount = 0;
    
    while ($goal = mysqli_fetch_assoc($goalsQuery)) {
        $goalId = $goal['id'];
        $goalName = $goal['name'];
        $actual = $goal['actual'] ?? 0;
        $target = $goal['target'] ?? $goal['green'] ?? 0;
        $score = $goal['score'] ?? 0;
        $lastUpdate = $goal['lastUpdate'];
        
        // Calculate achievement percentage based on score
        $achievement = round($score * 20, 1); // Convert 5-point scale to percentage
        
        // Determine status
        $status = 'at-risk';
        if ($score >= 4) {
            $status = 'excellent';
            $onTrackCount++;
        } elseif ($score >= 3) {
            $status = 'on-track';
            $onTrackCount++;
        } else {
            $atRiskCount++;
        }
        
        // Calculate due date (end of current period)
        $dueDate = '';
        switch ($goal['calendarType']) {
            case 'Monthly':
                $dueDate = date('M j, Y', strtotime('last day of ' . $objectDate));
                break;
            case 'Quarterly':
                $quarter = ceil(date('n', strtotime($objectDate)) / 3);
                $year = date('Y', strtotime($objectDate));
                $quarterEndMonth = $quarter * 3;
                $dueDate = date('M j, Y', strtotime("$year-$quarterEndMonth-" . date('t', strtotime("$year-$quarterEndMonth-01"))));
                break;
            case 'Yearly':
                $dueDate = date('M j, Y', strtotime(date('Y', strtotime($objectDate)) . '-12-31'));
                break;
        }
        
        // Get historical progress for trend analysis
        $trendQuery = mysqli_query($connect, "
            SELECT 
                CASE 
                    WHEN m.calendarType = 'Monthly' THEN AVG(mm.3score)
                    WHEN m.calendarType = 'Quarterly' THEN AVG(mq.3score)
                    WHEN m.calendarType = 'Yearly' THEN AVG(my.3score)
                    ELSE 0
                END as avgScore
            FROM measure m
            LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
                AND mm.date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
            LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
                AND mq.date >= DATE_SUB(NOW(), INTERVAL 9 MONTH)
            LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
                AND my.date >= DATE_SUB(NOW(), INTERVAL 2 YEAR)
            WHERE m.id = '$goalId'
        ");
        $trendResult = mysqli_fetch_assoc($trendQuery);
        $historicalAvg = $trendResult['avgScore'] ?? 0;
        
        $trend = 'stable';
        if ($score > $historicalAvg + 0.5) {
            $trend = 'improving';
        } elseif ($score < $historicalAvg - 0.5) {
            $trend = 'declining';
        }
        
        $goals[] = array(
            'id' => $goalId,
            'name' => $goalName,
            'actual' => $actual,
            'target' => $target,
            'achievement' => $achievement,
            'score' => $score,
            'status' => $status,
            'trend' => $trend,
            'dueDate' => $dueDate,
            'lastUpdate' => $lastUpdate ? date('M j, Y', strtotime($lastUpdate)) : 'Not updated',
            'calendarType' => $goal['calendarType']
        );
        
        $totalAchievement += $achievement;
        $goalCount++;
    }
    
    // Calculate overall metrics
    $avgAchievement = $goalCount > 0 ? round($totalAchievement / $goalCount, 1) : 0;
    
    $response = array(
        'goals' => $goals,
        'summary' => array(
            'totalGoals' => $goalCount,
            'avgAchievement' => $avgAchievement,
            'onTrack' => $onTrackCount,
            'atRisk' => $atRiskCount
        ),
        'breakdown' => array(
            'onTrack' => $onTrackCount,
            'atRisk' => $atRiskCount
        )
    );
    
    // Get goal categories breakdown
    $categoriesQuery = mysqli_query($connect, "
        SELECT 
            p.name as perspectiveName,
            COUNT(m.id) as goalCount,
            AVG(CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.3score
                WHEN m.calendarType = 'Quarterly' THEN mq.3score
                WHEN m.calendarType = 'Yearly' THEN my.3score
                ELSE 0
            END) as avgScore
        FROM measure m
        LEFT JOIN perspective p ON m.linkedObject = p.id
        LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
        LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
        LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
        WHERE p.parentId = '$departmentId'
        GROUP BY p.id, p.name
        ORDER BY p.name
    ");
    
    $categories = array();
    while ($category = mysqli_fetch_assoc($categoriesQuery)) {
        $categories[] = array(
            'name' => $category['perspectiveName'],
            'goalCount' => $category['goalCount'],
            'avgScore' => round($category['avgScore'] ?? 0, 1),
            'achievement' => round(($category['avgScore'] ?? 0) * 20, 1)
        );
    }
    $response['categories'] = $categories;
    
    // Get recent goal updates
    $updatesQuery = mysqli_query($connect, "
        SELECT 
            m.name as measureName,
            CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.updater
                WHEN m.calendarType = 'Quarterly' THEN mq.updater
                WHEN m.calendarType = 'Yearly' THEN my.updater
                ELSE NULL
            END as updaterId,
            CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.date
                WHEN m.calendarType = 'Quarterly' THEN mq.date
                WHEN m.calendarType = 'Yearly' THEN my.date
                ELSE NULL
            END as updateDate,
            CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.actual
                WHEN m.calendarType = 'Quarterly' THEN mq.actual
                WHEN m.calendarType = 'Yearly' THEN my.actual
                ELSE NULL
            END as newValue
        FROM measure m
        LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
        LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
        LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
        WHERE m.linkedObject = '$departmentId'
        AND (
            (m.calendarType = 'Monthly' AND mm.date >= DATE_SUB(NOW(), INTERVAL 1 WEEK))
            OR (m.calendarType = 'Quarterly' AND mq.date >= DATE_SUB(NOW(), INTERVAL 1 MONTH))
            OR (m.calendarType = 'Yearly' AND my.date >= DATE_SUB(NOW(), INTERVAL 3 MONTH))
        )
        ORDER BY updateDate DESC
        LIMIT 10
    ");
    
    $recentUpdates = array();
    while ($update = mysqli_fetch_assoc($updatesQuery)) {
        if ($update['updaterId']) {
            $updaterQuery = mysqli_query($connect, "SELECT display_name FROM uc_users WHERE user_id = '" . $update['updaterId'] . "'");
            $updaterResult = mysqli_fetch_assoc($updaterQuery);
            $updaterName = $updaterResult['display_name'] ?? 'Unknown';
            
            $recentUpdates[] = array(
                'measureName' => $update['measureName'],
                'updaterName' => $updaterName,
                'updateDate' => date('M j, Y', strtotime($update['updateDate'])),
                'newValue' => $update['newValue']
            );
        }
    }
    $response['recentUpdates'] = $recentUpdates;
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching goal progress: ' . $e->getMessage();
}

echo json_encode($response);
?>
