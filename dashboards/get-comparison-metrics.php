<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");
include_once("../reports/scores-functions.2.0.php");

header('Content-Type: application/json');

// Get parameters
$departmentId = $_POST['departmentId'] ?? 'org1';
$comparisonType = $_POST['comparisonType'] ?? 'current';
$period = $_POST['period'] ?? 'month';
$date = $_POST['date'] ?? date("Y-m");
$metric = $_POST['metric'] ?? 'overall';

// Initialize response array
$response = array();

try {
    // Get current department score
    $currentScore = getOrgScore($departmentId);
    if ($currentScore === null || $currentScore === '') {
        $currentScore = 0;
    }
    
    // Get all departments for ranking
    $departmentsQuery = mysqli_query($connect, "
        SELECT 
            o.id,
            o.name,
            COUNT(DISTINCT u.user_id) as staffCount
        FROM organization o
        LEFT JOIN uc_users u ON u.department = o.id AND u.active = 1
        WHERE o.id != 'org0' 
        AND o.showInTree = 'Yes'
        GROUP BY o.id, o.name
        ORDER BY o.name
    ");
    
    $departments = array();
    $scores = array();
    $currentRank = 0;
    $totalDepartments = 0;
    
    while ($dept = mysqli_fetch_assoc($departmentsQuery)) {
        $deptId = $dept['id'];
        $deptScore = getOrgScore($deptId);
        if ($deptScore === null || $deptScore === '') {
            $deptScore = 0;
        }
        
        $departments[] = array(
            'id' => $deptId,
            'name' => $dept['name'],
            'score' => round($deptScore, 1),
            'staffCount' => $dept['staffCount']
        );
        
        $scores[] = $deptScore;
        $totalDepartments++;
    }
    
    // Sort departments by score (descending)
    usort($departments, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });
    
    // Find current department rank
    foreach ($departments as $index => $dept) {
        if ($dept['id'] === $departmentId) {
            $currentRank = $index + 1;
            break;
        }
    }
    
    // Calculate performance gap to leader
    $leaderScore = !empty($departments) ? $departments[0]['score'] : 0;
    $performanceGap = round($leaderScore - $currentScore, 1);
    
    // Calculate benchmark score (organization average)
    $avgScore = !empty($scores) ? array_sum($scores) / count($scores) : 0;
    $benchmarkScore = round($avgScore, 1);
    
    // Calculate improvement rate (compare with previous period)
    $previousPeriodCondition = getPreviousPeriodCondition($period, $date);
    $improvementQuery = mysqli_query($connect, "
        SELECT AVG(ist.percentageCompletion) as prevScore
        FROM initiative_status ist
        INNER JOIN initiative i ON ist.initiativeId = i.id
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        WHERE u.department = '$departmentId'
        AND $previousPeriodCondition
    ");
    $improvementResult = mysqli_fetch_assoc($improvementQuery);
    $previousScore = $improvementResult['prevScore'] ?? 0;
    $improvementRate = $previousScore > 0 ? round((($currentScore - $previousScore) / $previousScore) * 100, 1) : 0;
    
    $response = array(
        'currentRank' => $currentRank,
        'totalDepartments' => $totalDepartments,
        'performanceGap' => $performanceGap,
        'benchmarkScore' => $benchmarkScore,
        'improvementRate' => $improvementRate,
        'currentScore' => round($currentScore, 1)
    );
    
    // Build trends object
    $response['trends'] = array(
        'rank' => array(
            'direction' => 'stable',
            'icon' => 'fas fa-trophy',
            'text' => "Out of $totalDepartments departments"
        ),
        'gap' => array(
            'direction' => $performanceGap > 0 ? 'down' : ($performanceGap < 0 ? 'up' : 'stable'),
            'icon' => 'fas fa-chart-line',
            'text' => 'Performance difference'
        ),
        'benchmark' => array(
            'direction' => $currentScore > $benchmarkScore ? 'up' : ($currentScore < $benchmarkScore ? 'down' : 'stable'),
            'icon' => 'fas fa-target',
            'text' => 'vs Organization average'
        ),
        'improvement' => array(
            'direction' => $improvementRate > 0 ? 'up' : ($improvementRate < 0 ? 'down' : 'stable'),
            'icon' => $improvementRate > 0 ? 'fas fa-arrow-up' : ($improvementRate < 0 ? 'fas fa-arrow-down' : 'fas fa-minus'),
            'text' => 'Month over month'
        )
    );
    
    // Add additional comparative metrics
    $response['comparative'] = array(
        'topPerformer' => !empty($departments) ? $departments[0] : null,
        'organizationAverage' => $benchmarkScore,
        'percentileRank' => $totalDepartments > 0 ? round((($totalDepartments - $currentRank + 1) / $totalDepartments) * 100, 1) : 0,
        'scoreDistribution' => array(
            'excellent' => count(array_filter($scores, function($s) { return $s >= 90; })),
            'good' => count(array_filter($scores, function($s) { return $s >= 75 && $s < 90; })),
            'average' => count(array_filter($scores, function($s) { return $s >= 60 && $s < 75; })),
            'poor' => count(array_filter($scores, function($s) { return $s < 60; }))
        )
    );
    
    // Get department-specific insights
    $insights = array();
    
    // Ranking insight
    if ($currentRank <= 3) {
        $insights[] = array(
            'type' => 'success',
            'title' => 'Top Performer',
            'message' => "Department ranks #{$currentRank} out of {$totalDepartments} departments"
        );
    } elseif ($currentRank > $totalDepartments * 0.7) {
        $insights[] = array(
            'type' => 'warning',
            'title' => 'Performance Opportunity',
            'message' => "Department ranks in bottom 30% - significant improvement potential"
        );
    }
    
    // Gap analysis insight
    if ($performanceGap > 10) {
        $insights[] = array(
            'type' => 'info',
            'title' => 'Gap Analysis',
            'message' => "Performance gap of {$performanceGap}% to top performer indicates room for improvement"
        );
    } elseif ($performanceGap < 5) {
        $insights[] = array(
            'type' => 'success',
            'title' => 'Competitive Performance',
            'message' => "Close to top performer with only {$performanceGap}% gap"
        );
    }
    
    // Improvement trend insight
    if ($improvementRate > 5) {
        $insights[] = array(
            'type' => 'success',
            'title' => 'Positive Momentum',
            'message' => "Strong improvement rate of {$improvementRate}% month-over-month"
        );
    } elseif ($improvementRate < -5) {
        $insights[] = array(
            'type' => 'warning',
            'title' => 'Declining Trend',
            'message' => "Performance declining by {$improvementRate}% - intervention needed"
        );
    }
    
    $response['insights'] = $insights;
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching comparison metrics: ' . $e->getMessage();
}

// Helper function to get previous period condition
function getPreviousPeriodCondition($period, $date) {
    switch ($period) {
        case 'month':
            $prevMonth = date('Y-m', strtotime($date . ' -1 month'));
            return "DATE_FORMAT(ist.updatedOn, '%Y-%m') = '$prevMonth'";
        case 'quarter':
            $quarter = ceil(date('n', strtotime($date)) / 3);
            $year = date('Y', strtotime($date));
            $prevQuarter = $quarter - 1;
            $prevYear = $year;
            if ($prevQuarter <= 0) {
                $prevQuarter = 4;
                $prevYear = $year - 1;
            }
            return "QUARTER(ist.updatedOn) = $prevQuarter AND YEAR(ist.updatedOn) = $prevYear";
        case 'year':
            $prevYear = date('Y', strtotime($date)) - 1;
            return "YEAR(ist.updatedOn) = $prevYear";
        default:
            $prevMonth = date('Y-m', strtotime($date . ' -1 month'));
            return "DATE_FORMAT(ist.updatedOn, '%Y-%m') = '$prevMonth'";
    }
}

echo json_encode($response);
?>
