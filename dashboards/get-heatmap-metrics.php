<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");

header('Content-Type: application/json');

// Get parameters
$departmentId = $_POST['departmentId'] ?? 'org1';
$heatMapType = $_POST['heatMapType'] ?? 'performance';
$metric = $_POST['metric'] ?? 'overall';
$period = $_POST['period'] ?? 'months';
$date = $_POST['date'] ?? date("Y-m");

// Initialize response array
$response = array();

try {
    // Get performance data for heat map metrics
    $performanceQuery = "
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
            m.id as measureId,
            m.calendarType
        FROM measure m
        LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
            AND mm.date = '$date'
        LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
            AND mq.date = '$date'
        LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
            AND my.date = '$date'
        WHERE m.linkedObject = '$departmentId'
        AND (
            (m.calendarType = 'Monthly' AND mm.3score IS NOT NULL)
            OR (m.calendarType = 'Quarterly' AND mq.3score IS NOT NULL)
            OR (m.calendarType = 'Yearly' AND my.3score IS NOT NULL)
        )
        ORDER BY m.name
    ";
    
    $performanceResult = mysqli_query($connect, $performanceQuery);
    $scores = array();
    $measureCount = 0;
    
    while ($row = mysqli_fetch_assoc($performanceResult)) {
        if ($row['score'] !== null) {
            $scores[] = floatval($row['score']);
            $measureCount++;
        }
    }
    
    // Calculate average performance
    $avgPerformance = 0;
    if (count($scores) > 0) {
        $avgPerformance = array_sum($scores) / count($scores);
    }
    
    // Count performance categories
    $topPerformers = 0;
    $needsAttention = 0;
    $performanceRange = 0;
    
    if (count($scores) > 0) {
        $topPerformers = count(array_filter($scores, function($score) {
            return $score >= 90;
        }));
        
        $needsAttention = count(array_filter($scores, function($score) {
            return $score < 60;
        }));
        
        $performanceRange = max($scores) - min($scores);
    }
    
    // Get staff performance data
    $staffQuery = "
        SELECT 
            u.user_id,
            u.display_name,
            u.role,
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
    $staffPerformance = array();
    
    while ($staff = mysqli_fetch_assoc($staffResult)) {
        $projectCount = $staff['projectCount'] ?? 0;
        $avgCompletion = $staff['avgCompletion'] ?? 0;
        
        // Calculate staff performance score
        $staffScore = 50; // Base score
        if ($projectCount > 0) {
            $staffScore = $avgCompletion;
        }
        
        // Adjust based on project count
        if ($projectCount > 3) {
            $staffScore += 10; // Bonus for handling multiple projects
        }
        
        $staffScore = max(0, min(100, $staffScore));
        $staffPerformance[] = $staffScore;
    }
    
    // Calculate staff-based metrics
    $staffTopPerformers = 0;
    $staffNeedsAttention = 0;
    
    if (count($staffPerformance) > 0) {
        $staffTopPerformers = count(array_filter($staffPerformance, function($score) {
            return $score >= 90;
        }));
        
        $staffNeedsAttention = count(array_filter($staffPerformance, function($score) {
            return $score < 60;
        }));
    }
    
    // Get historical data for trend analysis
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
            END as scoreDate
        FROM measure m
        LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
            AND mm.date >= DATE_SUB('$date', INTERVAL 6 MONTH)
        LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
            AND mq.date >= DATE_SUB('$date', INTERVAL 18 MONTH)
        LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
            AND my.date >= DATE_SUB('$date', INTERVAL 3 YEAR)
        WHERE m.linkedObject = '$departmentId'
        AND (
            (m.calendarType = 'Monthly' AND mm.3score IS NOT NULL)
            OR (m.calendarType = 'Quarterly' AND mq.3score IS NOT NULL)
            OR (m.calendarType = 'Yearly' AND my.3score IS NOT NULL)
        )
        ORDER BY scoreDate ASC
    ";
    
    $historicalResult = mysqli_query($connect, $historicalQuery);
    $historicalScores = array();
    
    while ($row = mysqli_fetch_assoc($historicalResult)) {
        if ($row['score'] !== null) {
            $historicalScores[] = floatval($row['score']);
        }
    }
    
    // Calculate trends
    $performanceTrend = calculateTrend($historicalScores, 'performance');
    $topPerformersTrend = calculateTrend(array($staffTopPerformers), 'count');
    $attentionTrend = calculateTrend(array($staffNeedsAttention), 'attention');
    $rangeTrend = calculateTrend(array($performanceRange), 'range');
    
    // Build trends object
    $trends = array(
        'performance' => $performanceTrend,
        'topPerformers' => $topPerformersTrend,
        'attention' => $attentionTrend,
        'range' => $rangeTrend
    );
    
    // Build response
    $response = array(
        'avgPerformance' => round($avgPerformance, 1),
        'topPerformers' => $topPerformers + $staffTopPerformers,
        'needsAttention' => $needsAttention + $staffNeedsAttention,
        'performanceRange' => round($performanceRange, 1),
        'trends' => $trends,
        'measureCount' => $measureCount,
        'staffCount' => count($staffPerformance),
        'metadata' => array(
            'departmentId' => $departmentId,
            'heatMapType' => $heatMapType,
            'metric' => $metric,
            'period' => $period,
            'date' => $date,
            'dataPoints' => count($scores) + count($staffPerformance),
            'lastUpdate' => date('Y-m-d H:i:s')
        )
    );
    
    // Add performance distribution
    $distribution = array(
        'excellent' => count(array_filter(array_merge($scores, $staffPerformance), function($score) {
            return $score >= 90;
        })),
        'good' => count(array_filter(array_merge($scores, $staffPerformance), function($score) {
            return $score >= 75 && $score < 90;
        })),
        'average' => count(array_filter(array_merge($scores, $staffPerformance), function($score) {
            return $score >= 60 && $score < 75;
        })),
        'poor' => count(array_filter(array_merge($scores, $staffPerformance), function($score) {
            return $score >= 40 && $score < 60;
        })),
        'critical' => count(array_filter(array_merge($scores, $staffPerformance), function($score) {
            return $score < 40;
        }))
    );
    
    $response['distribution'] = $distribution;
    
    // Add insights
    $insights = array();
    
    if ($avgPerformance >= 85) {
        $insights[] = array(
            'type' => 'success',
            'title' => 'Excellent Performance',
            'message' => "Average performance of {$avgPerformance}% indicates strong departmental results"
        );
    } elseif ($avgPerformance < 60) {
        $insights[] = array(
            'type' => 'warning',
            'title' => 'Performance Concerns',
            'message' => "Average performance of {$avgPerformance}% requires immediate attention"
        );
    }
    
    if ($needsAttention + $staffNeedsAttention > 0) {
        $total = $needsAttention + $staffNeedsAttention;
        $insights[] = array(
            'type' => 'info',
            'title' => 'Improvement Opportunities',
            'message' => "{$total} areas/staff members need performance improvement"
        );
    }
    
    if ($performanceRange > 40) {
        $insights[] = array(
            'type' => 'warning',
            'title' => 'High Performance Variance',
            'message' => "Performance range of {$performanceRange}% indicates inconsistent results"
        );
    }
    
    $response['insights'] = $insights;
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching heat map metrics: ' . $e->getMessage();
}

// Helper function to calculate trend
function calculateTrend($data, $type) {
    if (count($data) < 2) {
        return array(
            'direction' => 'stable',
            'icon' => 'fas fa-minus',
            'text' => 'No trend data'
        );
    }
    
    $recent = end($data);
    $previous = $data[count($data) - 2];
    $change = $recent - $previous;
    
    $direction = 'stable';
    $icon = 'fas fa-minus';
    $text = 'Stable';
    
    if ($type === 'attention') {
        // For attention metrics, down is good
        if ($change < -1) {
            $direction = 'up';
            $icon = 'fas fa-arrow-down';
            $text = 'Improving';
        } elseif ($change > 1) {
            $direction = 'down';
            $icon = 'fas fa-arrow-up';
            $text = 'Needs focus';
        }
    } else {
        // For performance metrics, up is good
        if ($change > 1) {
            $direction = 'up';
            $icon = 'fas fa-arrow-up';
            $text = 'Improving';
        } elseif ($change < -1) {
            $direction = 'down';
            $icon = 'fas fa-arrow-down';
            $text = 'Declining';
        }
    }
    
    return array(
        'direction' => $direction,
        'icon' => $icon,
        'text' => $text,
        'change' => round($change, 1)
    );
}

echo json_encode($response);
?>
