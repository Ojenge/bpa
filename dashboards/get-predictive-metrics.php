<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");

header('Content-Type: application/json');

// Get parameters
$departmentId = $_POST['departmentId'] ?? 'org1';
$forecastHorizon = $_POST['forecastHorizon'] ?? '6';
$analysisType = $_POST['analysisType'] ?? 'performance';
$confidenceLevel = $_POST['confidenceLevel'] ?? '90';

// Initialize response array
$response = array();

try {
    // Get historical performance data for trend analysis
    $historicalQuery = "
        SELECT 
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
            END as scoreDate,
            m.name as measureName,
            m.calendarType
        FROM measure m
        LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
            AND mm.date >= DATE_SUB(NOW(), INTERVAL 24 MONTH)
        LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
            AND mq.date >= DATE_SUB(NOW(), INTERVAL 72 MONTH)
        LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
            AND my.date >= DATE_SUB(NOW(), INTERVAL 5 YEAR)
        WHERE m.linkedObject = '$departmentId'
        AND (
            (m.calendarType = 'Monthly' AND mm.3score IS NOT NULL)
            OR (m.calendarType = 'Quarterly' AND mq.3score IS NOT NULL)
            OR (m.calendarType = 'Yearly' AND my.3score IS NOT NULL)
        )
        ORDER BY scoreDate DESC
        LIMIT 50
    ";
    
    $historicalResult = mysqli_query($connect, $historicalQuery);
    $historicalData = array();
    $scores = array();
    
    while ($row = mysqli_fetch_assoc($historicalResult)) {
        if ($row['score'] !== null && $row['scoreDate'] !== null) {
            $historicalData[] = array(
                'score' => floatval($row['score']),
                'date' => $row['scoreDate'],
                'measure' => $row['measureName'],
                'type' => $row['calendarType']
            );
            $scores[] = floatval($row['score']);
        }
    }
    
    // Calculate forecast accuracy based on historical variance
    $forecastAccuracy = 0;
    if (count($scores) > 1) {
        $mean = array_sum($scores) / count($scores);
        $variance = 0;
        foreach ($scores as $score) {
            $variance += pow($score - $mean, 2);
        }
        $variance = $variance / count($scores);
        $stdDev = sqrt($variance);
        
        // Calculate accuracy as inverse of coefficient of variation
        $coefficientOfVariation = $mean > 0 ? ($stdDev / $mean) : 1;
        $forecastAccuracy = max(0, min(100, 100 - ($coefficientOfVariation * 100)));
    }
    
    // Calculate trend strength using linear regression
    $trendStrength = 0;
    if (count($historicalData) >= 3) {
        $n = count($historicalData);
        $sumX = 0; $sumY = 0; $sumXY = 0; $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $x = $i + 1; // Time index
            $y = $historicalData[$i]['score'];
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        // Calculate R-squared for trend strength
        $meanY = $sumY / $n;
        $ssTotal = 0; $ssRes = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $x = $i + 1;
            $y = $historicalData[$i]['score'];
            $predicted = $slope * $x + $intercept;
            
            $ssTotal += pow($y - $meanY, 2);
            $ssRes += pow($y - $predicted, 2);
        }
        
        $rSquared = $ssTotal > 0 ? 1 - ($ssRes / $ssTotal) : 0;
        $trendStrength = max(0, min(100, $rSquared * 100));
    }
    
    // Detect anomalies using statistical methods
    $anomaliesDetected = 0;
    $anomalies = array();
    
    if (count($scores) > 5) {
        $mean = array_sum($scores) / count($scores);
        $stdDev = sqrt(array_sum(array_map(function($x) use ($mean) { 
            return pow($x - $mean, 2); 
        }, $scores)) / count($scores));
        
        $threshold = 2 * $stdDev; // 2 standard deviations
        
        foreach ($historicalData as $index => $data) {
            if (abs($data['score'] - $mean) > $threshold) {
                $anomaliesDetected++;
                $anomalies[] = array(
                    'date' => $data['date'],
                    'score' => $data['score'],
                    'deviation' => abs($data['score'] - $mean),
                    'severity' => abs($data['score'] - $mean) > (3 * $stdDev) ? 'high' : 'medium'
                );
            }
        }
    }
    
    // Generate early warnings based on trend analysis
    $earlyWarnings = 0;
    $warnings = array();
    
    // Check for declining trends
    if (count($historicalData) >= 5) {
        $recentScores = array_slice($scores, 0, 5);
        $recentMean = array_sum($recentScores) / count($recentScores);
        $overallMean = array_sum($scores) / count($scores);
        
        if ($recentMean < $overallMean * 0.9) { // 10% decline
            $earlyWarnings++;
            $warnings[] = array(
                'type' => 'declining_performance',
                'severity' => 'medium',
                'message' => 'Performance showing declining trend',
                'recommendation' => 'Review processes and implement improvement measures'
            );
        }
        
        // Check for high volatility
        $recentStdDev = sqrt(array_sum(array_map(function($x) use ($recentMean) { 
            return pow($x - $recentMean, 2); 
        }, $recentScores)) / count($recentScores));
        
        if ($recentStdDev > $overallMean * 0.2) { // High volatility
            $earlyWarnings++;
            $warnings[] = array(
                'type' => 'high_volatility',
                'severity' => 'low',
                'message' => 'Performance showing high volatility',
                'recommendation' => 'Investigate causes of performance fluctuations'
            );
        }
    }
    
    // Get project-based predictions
    $projectQuery = "
        SELECT 
            COUNT(DISTINCT i.id) as totalProjects,
            AVG(ist.percentageCompletion) as avgCompletion,
            COUNT(DISTINCT CASE WHEN i.dueDate < NOW() AND ist.percentageCompletion < 100 THEN i.id END) as overdueProjects,
            COUNT(DISTINCT CASE WHEN i.dueDate BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY) THEN i.id END) as upcomingDeadlines
        FROM initiative i
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        LEFT JOIN initiative_status ist ON i.id = ist.initiativeId
        WHERE u.department = '$departmentId'
        AND (ist.updatedOn = (
            SELECT MAX(ist2.updatedOn) 
            FROM initiative_status ist2 
            WHERE ist2.initiativeId = ist.initiativeId
        ) OR ist.updatedOn IS NULL)
    ";
    
    $projectResult = mysqli_query($connect, $projectQuery);
    $projectData = mysqli_fetch_assoc($projectResult);
    
    // Add project-based warnings
    if ($projectData['overdueProjects'] > 0) {
        $earlyWarnings++;
        $warnings[] = array(
            'type' => 'overdue_projects',
            'severity' => 'high',
            'message' => $projectData['overdueProjects'] . ' projects are overdue',
            'recommendation' => 'Prioritize overdue project completion'
        );
    }
    
    if ($projectData['upcomingDeadlines'] > 3) {
        $earlyWarnings++;
        $warnings[] = array(
            'type' => 'upcoming_deadlines',
            'severity' => 'medium',
            'message' => $projectData['upcomingDeadlines'] . ' projects due within 30 days',
            'recommendation' => 'Review project timelines and resource allocation'
        );
    }
    
    // Build trend indicators
    $trends = array(
        'accuracy' => array(
            'direction' => $forecastAccuracy >= 80 ? 'up' : ($forecastAccuracy >= 60 ? 'stable' : 'down'),
            'icon' => 'fas fa-target',
            'text' => $forecastAccuracy >= 80 ? 'High accuracy' : ($forecastAccuracy >= 60 ? 'Moderate accuracy' : 'Low accuracy')
        ),
        'strength' => array(
            'direction' => $trendStrength >= 70 ? 'up' : ($trendStrength >= 40 ? 'stable' : 'down'),
            'icon' => 'fas fa-chart-line',
            'text' => $trendStrength >= 70 ? 'Strong trend' : ($trendStrength >= 40 ? 'Moderate trend' : 'Weak trend')
        ),
        'warnings' => array(
            'direction' => $earlyWarnings == 0 ? 'up' : ($earlyWarnings <= 2 ? 'stable' : 'down'),
            'icon' => 'fas fa-exclamation-triangle',
            'text' => $earlyWarnings == 0 ? 'No warnings' : ($earlyWarnings <= 2 ? 'Few warnings' : 'Multiple warnings')
        ),
        'anomalies' => array(
            'direction' => $anomaliesDetected == 0 ? 'up' : ($anomaliesDetected <= 2 ? 'stable' : 'down'),
            'icon' => 'fas fa-search',
            'text' => $anomaliesDetected == 0 ? 'No anomalies' : ($anomaliesDetected <= 2 ? 'Few anomalies' : 'Multiple anomalies')
        )
    );
    
    // Build response
    $response = array(
        'forecastAccuracy' => round($forecastAccuracy, 1),
        'trendStrength' => round($trendStrength, 1),
        'earlyWarnings' => $earlyWarnings,
        'anomaliesDetected' => $anomaliesDetected,
        'trends' => $trends,
        'historicalData' => array_slice($historicalData, 0, 20), // Last 20 data points
        'warnings' => $warnings,
        'anomalies' => array_slice($anomalies, 0, 10), // Top 10 anomalies
        'projectMetrics' => $projectData,
        'analysisMetadata' => array(
            'dataPoints' => count($historicalData),
            'timeRange' => count($historicalData) > 0 ? 
                array(
                    'from' => end($historicalData)['date'],
                    'to' => $historicalData[0]['date']
                ) : null,
            'confidenceLevel' => $confidenceLevel,
            'forecastHorizon' => $forecastHorizon,
            'analysisType' => $analysisType
        )
    );
    
    // Add insights based on analysis
    $insights = array();
    
    if ($forecastAccuracy >= 85) {
        $insights[] = array(
            'type' => 'success',
            'title' => 'High Prediction Accuracy',
            'message' => "Forecast accuracy of {$forecastAccuracy}% indicates reliable predictions"
        );
    } elseif ($forecastAccuracy < 60) {
        $insights[] = array(
            'type' => 'warning',
            'title' => 'Low Prediction Accuracy',
            'message' => "Forecast accuracy of {$forecastAccuracy}% suggests high uncertainty"
        );
    }
    
    if ($trendStrength >= 80) {
        $insights[] = array(
            'type' => 'info',
            'title' => 'Strong Trend Pattern',
            'message' => "Trend strength of {$trendStrength}% shows clear directional movement"
        );
    }
    
    if ($anomaliesDetected > 0) {
        $insights[] = array(
            'type' => 'warning',
            'title' => 'Anomalies Detected',
            'message' => "{$anomaliesDetected} anomalies found - investigate unusual patterns"
        );
    }
    
    $response['insights'] = $insights;
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching predictive metrics: ' . $e->getMessage();
}

echo json_encode($response);
?>
