<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");

header('Content-Type: application/json');

// Get parameters
$departmentId = $_POST['departmentId'] ?? 'org1';
$objectPeriod = $_POST['objectPeriod'] ?? 'months';
$objectDate = $_POST['objectDate'] ?? date("Y-m");

// Initialize response array
$response = array();

try {
    // Get department KPIs (measures)
    $kpisQuery = mysqli_query($connect, "
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
                WHEN m.calendarType = 'Monthly' THEN mm.red
                WHEN m.calendarType = 'Quarterly' THEN mq.red
                WHEN m.calendarType = 'Yearly' THEN my.red
                ELSE m.red
            END as redThreshold,
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
            END as lastUpdate,
            CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.updater
                WHEN m.calendarType = 'Quarterly' THEN mq.updater
                WHEN m.calendarType = 'Yearly' THEN my.updater
                ELSE NULL
            END as updaterId
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
    
    $kpis = array();
    $performanceData = array();
    $totalScore = 0;
    $kpiCount = 0;
    
    while ($kpi = mysqli_fetch_assoc($kpisQuery)) {
        $kpiId = $kpi['id'];
        $kpiName = $kpi['name'];
        $actual = $kpi['actual'] ?? 0;
        $target = $kpi['target'] ?? $kpi['green'] ?? 0;
        $redThreshold = $kpi['redThreshold'] ?? $kpi['red'] ?? 0;
        $score = $kpi['score'] ?? 0;
        $lastUpdate = $kpi['lastUpdate'];
        $updaterId = $kpi['updaterId'];
        
        // Get updater name
        $updaterName = 'System';
        if ($updaterId) {
            $updaterQuery = mysqli_query($connect, "SELECT display_name FROM uc_users WHERE user_id = '$updaterId'");
            $updaterResult = mysqli_fetch_assoc($updaterQuery);
            $updaterName = $updaterResult['display_name'] ?? 'Unknown';
        }
        
        // Calculate performance percentage
        $performance = round($score * 20, 1); // Convert 5-point scale to percentage
        
        // Determine status based on score
        $status = 'poor';
        $statusColor = 'danger';
        if ($score >= 4) {
            $status = 'excellent';
            $statusColor = 'success';
        } elseif ($score >= 3) {
            $status = 'good';
            $statusColor = 'info';
        } elseif ($score >= 2) {
            $status = 'average';
            $statusColor = 'warning';
        }
        
        // Get historical data for trend analysis
        $trendQuery = mysqli_query($connect, "
            SELECT 
                CASE 
                    WHEN m.calendarType = 'Monthly' THEN mm.3score
                    WHEN m.calendarType = 'Quarterly' THEN mq.3score
                    WHEN m.calendarType = 'Yearly' THEN my.3score
                    ELSE 0
                END as historicalScore,
                CASE 
                    WHEN m.calendarType = 'Monthly' THEN mm.date
                    WHEN m.calendarType = 'Quarterly' THEN mq.date
                    WHEN m.calendarType = 'Yearly' THEN my.date
                    ELSE NULL
                END as scoreDate
            FROM measure m
            LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
                AND mm.date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
                AND mq.date >= DATE_SUB(NOW(), INTERVAL 18 MONTH)
            LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
                AND my.date >= DATE_SUB(NOW(), INTERVAL 3 YEAR)
            WHERE m.id = '$kpiId'
            AND (mm.date IS NOT NULL OR mq.date IS NOT NULL OR my.date IS NOT NULL)
            ORDER BY scoreDate DESC
            LIMIT 6
        ");
        
        $historicalScores = array();
        while ($trend = mysqli_fetch_assoc($trendQuery)) {
            if ($trend['historicalScore'] !== null) {
                $historicalScores[] = $trend['historicalScore'];
            }
        }
        
        // Calculate trend
        $trend = 'stable';
        if (count($historicalScores) >= 2) {
            $recent = array_slice($historicalScores, 0, 2);
            $older = array_slice($historicalScores, 2, 2);
            
            $recentAvg = array_sum($recent) / count($recent);
            $olderAvg = count($older) > 0 ? array_sum($older) / count($older) : $recentAvg;
            
            if ($recentAvg > $olderAvg + 0.3) {
                $trend = 'improving';
            } elseif ($recentAvg < $olderAvg - 0.3) {
                $trend = 'declining';
            }
        }
        
        // Calculate variance (consistency)
        $variance = 0;
        if (count($historicalScores) > 1) {
            $mean = array_sum($historicalScores) / count($historicalScores);
            $squaredDiffs = array_map(function($score) use ($mean) {
                return pow($score - $mean, 2);
            }, $historicalScores);
            $variance = array_sum($squaredDiffs) / count($squaredDiffs);
        }
        $consistency = $variance < 0.5 ? 'high' : ($variance < 1.0 ? 'medium' : 'low');
        
        // Determine gauge type display
        $gaugeTypeDisplay = ucfirst($kpi['gaugeType'] ?? 'standard');
        
        $kpis[] = array(
            'id' => $kpiId,
            'name' => $kpiName,
            'actual' => $actual,
            'target' => $target,
            'redThreshold' => $redThreshold,
            'score' => $score,
            'performance' => $performance,
            'status' => $status,
            'statusColor' => $statusColor,
            'trend' => $trend,
            'consistency' => $consistency,
            'type' => $gaugeTypeDisplay,
            'frequency' => $kpi['calendarType'],
            'lastUpdate' => $lastUpdate ? date('M j, Y', strtotime($lastUpdate)) : 'Not updated',
            'updatedBy' => $updaterName,
            'historicalScores' => array_reverse($historicalScores) // Oldest to newest
        );
        
        // Add to performance data for chart
        $performanceData[] = array(
            'name' => $kpiName,
            'score' => $score,
            'performance' => $performance
        );
        
        $totalScore += $score;
        $kpiCount++;
    }
    
    // Calculate summary statistics
    $avgScore = $kpiCount > 0 ? round($totalScore / $kpiCount, 2) : 0;
    $avgPerformance = round($avgScore * 20, 1);
    
    $excellentKPIs = count(array_filter($kpis, function($kpi) { return $kpi['score'] >= 4; }));
    $goodKPIs = count(array_filter($kpis, function($kpi) { return $kpi['score'] >= 3 && $kpi['score'] < 4; }));
    $averageKPIs = count(array_filter($kpis, function($kpi) { return $kpi['score'] >= 2 && $kpi['score'] < 3; }));
    $poorKPIs = count(array_filter($kpis, function($kpi) { return $kpi['score'] < 2; }));
    
    $response = array(
        'kpis' => $kpis,
        'performance' => $performanceData,
        'summary' => array(
            'total' => $kpiCount,
            'avgScore' => $avgScore,
            'avgPerformance' => $avgPerformance,
            'excellent' => $excellentKPIs,
            'good' => $goodKPIs,
            'average' => $averageKPIs,
            'poor' => $poorKPIs
        )
    );
    
    // Get KPI categories/perspectives
    $categoriesQuery = mysqli_query($connect, "
        SELECT 
            p.name as perspectiveName,
            COUNT(m.id) as kpiCount,
            AVG(CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.3score
                WHEN m.calendarType = 'Quarterly' THEN mq.3score
                WHEN m.calendarType = 'Yearly' THEN my.3score
                ELSE 0
            END) as avgScore
        FROM perspective p
        LEFT JOIN measure m ON m.linkedObject = p.id
        LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
        LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
        LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
        WHERE p.parentId = '$departmentId'
        GROUP BY p.id, p.name
        HAVING kpiCount > 0
        ORDER BY p.name
    ");
    
    $categories = array();
    while ($category = mysqli_fetch_assoc($categoriesQuery)) {
        $categories[] = array(
            'name' => $category['perspectiveName'],
            'kpiCount' => $category['kpiCount'],
            'avgScore' => round($category['avgScore'] ?? 0, 2),
            'performance' => round(($category['avgScore'] ?? 0) * 20, 1)
        );
    }
    $response['categories'] = $categories;
    
    // Get recent KPI updates
    $recentUpdatesQuery = mysqli_query($connect, "
        SELECT 
            m.name as kpiName,
            CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.actual
                WHEN m.calendarType = 'Quarterly' THEN mq.actual
                WHEN m.calendarType = 'Yearly' THEN my.actual
                ELSE NULL
            END as newValue,
            CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.date
                WHEN m.calendarType = 'Quarterly' THEN mq.date
                WHEN m.calendarType = 'Yearly' THEN my.date
                ELSE NULL
            END as updateDate,
            CASE 
                WHEN m.calendarType = 'Monthly' THEN mm.updater
                WHEN m.calendarType = 'Quarterly' THEN mq.updater
                WHEN m.calendarType = 'Yearly' THEN my.updater
                ELSE NULL
            END as updaterId
        FROM measure m
        LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
        LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
        LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
        WHERE m.linkedObject = '$departmentId'
        AND (
            (m.calendarType = 'Monthly' AND mm.date >= DATE_SUB(NOW(), INTERVAL 2 WEEK))
            OR (m.calendarType = 'Quarterly' AND mq.date >= DATE_SUB(NOW(), INTERVAL 1 MONTH))
            OR (m.calendarType = 'Yearly' AND my.date >= DATE_SUB(NOW(), INTERVAL 3 MONTH))
        )
        ORDER BY updateDate DESC
        LIMIT 10
    ");
    
    $recentUpdates = array();
    while ($update = mysqli_fetch_assoc($recentUpdatesQuery)) {
        if ($update['updaterId']) {
            $updaterQuery = mysqli_query($connect, "SELECT display_name FROM uc_users WHERE user_id = '" . $update['updaterId'] . "'");
            $updaterResult = mysqli_fetch_assoc($updaterQuery);
            $updaterName = $updaterResult['display_name'] ?? 'Unknown';
            
            $recentUpdates[] = array(
                'kpiName' => $update['kpiName'],
                'newValue' => $update['newValue'],
                'updateDate' => date('M j, Y', strtotime($update['updateDate'])),
                'updatedBy' => $updaterName
            );
        }
    }
    $response['recentUpdates'] = $recentUpdates;
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching department KPIs: ' . $e->getMessage();
}

echo json_encode($response);
?>
