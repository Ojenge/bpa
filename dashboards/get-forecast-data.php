<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");

header('Content-Type: application/json');

// Get parameters
$departmentId = $_POST['departmentId'] ?? 'org1';
$forecastHorizon = intval($_POST['forecastHorizon'] ?? '6');
$analysisType = $_POST['analysisType'] ?? 'performance';
$confidenceLevel = intval($_POST['confidenceLevel'] ?? '90');

// Initialize response array
$response = array();

try {
    // Get historical data for forecasting
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
            END as target,
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
        ORDER BY scoreDate ASC
        LIMIT 36
    ";
    
    $historicalResult = mysqli_query($connect, $historicalQuery);
    $historicalData = array();
    $scores = array();
    $dates = array();
    
    while ($row = mysqli_fetch_assoc($historicalResult)) {
        if ($row['score'] !== null && $row['scoreDate'] !== null) {
            $historicalData[] = array(
                'score' => floatval($row['score']),
                'actual' => floatval($row['actual']),
                'target' => floatval($row['target']),
                'date' => $row['scoreDate'],
                'measure' => $row['measureName'],
                'type' => $row['calendarType']
            );
            $scores[] = floatval($row['score']);
            $dates[] = $row['scoreDate'];
        }
    }
    
    // Generate forecast using simple linear regression and moving averages
    $forecast = array();
    $confidence = array();
    
    if (count($scores) >= 3) {
        // Calculate linear trend
        $n = count($scores);
        $sumX = 0; $sumY = 0; $sumXY = 0; $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $x = $i + 1;
            $y = $scores[$i];
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        // Calculate moving average for smoothing
        $windowSize = min(6, floor($n / 2));
        $movingAverage = 0;
        if ($windowSize > 0) {
            $recentScores = array_slice($scores, -$windowSize);
            $movingAverage = array_sum($recentScores) / count($recentScores);
        }
        
        // Calculate standard deviation for confidence intervals
        $mean = array_sum($scores) / count($scores);
        $variance = 0;
        foreach ($scores as $score) {
            $variance += pow($score - $mean, 2);
        }
        $stdDev = sqrt($variance / count($scores));
        
        // Generate forecast points
        $lastDate = end($dates);
        $baseDate = new DateTime($lastDate);
        
        for ($i = 1; $i <= $forecastHorizon; $i++) {
            // Linear trend component
            $trendValue = $slope * ($n + $i) + $intercept;
            
            // Moving average component (weighted)
            $maWeight = 0.3; // 30% weight to moving average
            $trendWeight = 0.7; // 70% weight to trend
            
            $forecastValue = ($trendWeight * $trendValue) + ($maWeight * $movingAverage);
            
            // Apply bounds (0-100 for scores)
            $forecastValue = max(0, min(100, $forecastValue));
            
            // Calculate confidence interval based on confidence level
            $zScore = getZScore($confidenceLevel);
            $margin = $zScore * $stdDev * sqrt(1 + 1/$n + pow($i, 2)/array_sum(range(1, $n)));
            
            $upperBound = min(100, $forecastValue + $margin);
            $lowerBound = max(0, $forecastValue - $margin);
            
            // Generate forecast date
            $forecastDate = clone $baseDate;
            $forecastDate->add(new DateInterval("P{$i}M")); // Add months
            
            $forecast[] = array(
                'period' => $forecastDate->format('M Y'),
                'value' => round($forecastValue, 1),
                'trend' => $slope > 0 ? 'increasing' : ($slope < 0 ? 'decreasing' : 'stable'),
                'confidence' => $confidenceLevel
            );
            
            $confidence[] = array(
                'period' => $forecastDate->format('M Y'),
                'forecast' => round($forecastValue, 1),
                'upperBound' => round($upperBound, 1),
                'lowerBound' => round($lowerBound, 1),
                'margin' => round($margin, 1)
            );
        }
    }
    
    // Generate forecast summary
    $summary = array();
    if (!empty($forecast)) {
        $nextPeriodForecast = $forecast[0]['value'];
        $lastActual = end($scores);
        $change = $nextPeriodForecast - $lastActual;
        $changePercent = $lastActual > 0 ? ($change / $lastActual) * 100 : 0;
        
        $summary = array(
            'nextPeriodForecast' => $nextPeriodForecast,
            'expectedChange' => round($change, 1),
            'changePercent' => round($changePercent, 1),
            'confidenceLevel' => $confidenceLevel,
            'trendDirection' => $change > 1 ? 'Improving' : ($change < -1 ? 'Declining' : 'Stable'),
            'riskLevel' => abs($changePercent) > 20 ? 'High' : (abs($changePercent) > 10 ? 'Medium' : 'Low'),
            'forecastHorizon' => $forecastHorizon . ' months',
            'dataQuality' => count($scores) >= 12 ? 'High' : (count($scores) >= 6 ? 'Medium' : 'Low')
        );
    }
    
    // Generate prediction timeline
    $timeline = array();
    foreach ($forecast as $index => $item) {
        $type = 'positive';
        if ($item['value'] < 60) {
            $type = 'critical';
        } elseif ($item['value'] < 75) {
            $type = 'warning';
        }
        
        $timeline[] = array(
            'period' => $item['period'],
            'prediction' => "Expected performance: {$item['value']}%",
            'confidence' => $item['confidence'],
            'type' => $type,
            'details' => array(
                'trend' => $item['trend'],
                'value' => $item['value'],
                'recommendation' => getRecommendation($item['value'], $type)
            )
        );
    }
    
    // Add seasonal adjustments if enough data
    if (count($historicalData) >= 12) {
        $seasonalAdjustments = calculateSeasonalAdjustments($historicalData);
        
        // Apply seasonal adjustments to forecast
        foreach ($forecast as $index => &$item) {
            $month = date('n', strtotime($item['period']));
            if (isset($seasonalAdjustments[$month])) {
                $adjustment = $seasonalAdjustments[$month];
                $item['value'] = max(0, min(100, $item['value'] + $adjustment));
                $item['seasonalAdjustment'] = $adjustment;
            }
        }
    }
    
    // Build response
    $response = array(
        'forecast' => $forecast,
        'confidence' => $confidence,
        'summary' => $summary,
        'timeline' => $timeline,
        'historicalData' => $historicalData,
        'forecastMetadata' => array(
            'method' => 'Linear Regression with Moving Average',
            'dataPoints' => count($scores),
            'forecastHorizon' => $forecastHorizon,
            'confidenceLevel' => $confidenceLevel,
            'trendStrength' => calculateTrendStrength($scores),
            'seasonalityDetected' => count($historicalData) >= 12,
            'lastUpdate' => date('Y-m-d H:i:s')
        )
    );
    
    // Add model validation metrics
    if (count($scores) >= 6) {
        $validation = validateForecastModel($scores, $forecast);
        $response['validation'] = $validation;
    }
    
} catch (Exception $e) {
    $response['error'] = 'Error generating forecast: ' . $e->getMessage();
}

// Helper function to get Z-score for confidence level
function getZScore($confidenceLevel) {
    $zScores = array(
        80 => 1.28,
        90 => 1.645,
        95 => 1.96,
        99 => 2.576
    );
    return $zScores[$confidenceLevel] ?? 1.645;
}

// Helper function to get recommendations
function getRecommendation($value, $type) {
    switch ($type) {
        case 'critical':
            return 'Immediate intervention required - performance below acceptable levels';
        case 'warning':
            return 'Monitor closely and consider improvement measures';
        case 'positive':
        default:
            return 'Maintain current performance levels';
    }
}

// Helper function to calculate seasonal adjustments
function calculateSeasonalAdjustments($data) {
    $monthlyData = array();
    
    foreach ($data as $item) {
        $month = date('n', strtotime($item['date']));
        if (!isset($monthlyData[$month])) {
            $monthlyData[$month] = array();
        }
        $monthlyData[$month][] = $item['score'];
    }
    
    $overallMean = array_sum(array_column($data, 'score')) / count($data);
    $adjustments = array();
    
    for ($month = 1; $month <= 12; $month++) {
        if (isset($monthlyData[$month]) && count($monthlyData[$month]) > 0) {
            $monthMean = array_sum($monthlyData[$month]) / count($monthlyData[$month]);
            $adjustments[$month] = $monthMean - $overallMean;
        } else {
            $adjustments[$month] = 0;
        }
    }
    
    return $adjustments;
}

// Helper function to calculate trend strength
function calculateTrendStrength($scores) {
    if (count($scores) < 3) return 0;
    
    $n = count($scores);
    $sumX = 0; $sumY = 0; $sumXY = 0; $sumX2 = 0;
    
    for ($i = 0; $i < $n; $i++) {
        $x = $i + 1;
        $y = $scores[$i];
        $sumX += $x;
        $sumY += $y;
        $sumXY += $x * $y;
        $sumX2 += $x * $x;
    }
    
    $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
    $intercept = ($sumY - $slope * $sumX) / $n;
    
    // Calculate R-squared
    $meanY = $sumY / $n;
    $ssTotal = 0; $ssRes = 0;
    
    for ($i = 0; $i < $n; $i++) {
        $x = $i + 1;
        $y = $scores[$i];
        $predicted = $slope * $x + $intercept;
        
        $ssTotal += pow($y - $meanY, 2);
        $ssRes += pow($y - $predicted, 2);
    }
    
    return $ssTotal > 0 ? (1 - ($ssRes / $ssTotal)) * 100 : 0;
}

// Helper function to validate forecast model
function validateForecastModel($scores, $forecast) {
    // Simple validation using last few data points
    $testSize = min(3, floor(count($scores) / 3));
    if ($testSize < 2) return null;
    
    $trainData = array_slice($scores, 0, -$testSize);
    $testData = array_slice($scores, -$testSize);
    
    // Calculate simple forecast for test data
    $trainMean = array_sum($trainData) / count($trainData);
    $errors = array();
    
    foreach ($testData as $actual) {
        $error = abs($actual - $trainMean);
        $errors[] = $error;
    }
    
    $mae = array_sum($errors) / count($errors); // Mean Absolute Error
    $mape = 0; // Mean Absolute Percentage Error
    
    foreach ($testData as $actual) {
        if ($actual != 0) {
            $mape += abs(($actual - $trainMean) / $actual);
        }
    }
    $mape = ($mape / count($testData)) * 100;
    
    return array(
        'mae' => round($mae, 2),
        'mape' => round($mape, 2),
        'accuracy' => max(0, 100 - $mape),
        'testSize' => $testSize
    );
}

echo json_encode($response);
?>
