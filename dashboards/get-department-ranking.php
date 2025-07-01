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
    // Get all departments with comprehensive metrics
    $departmentsQuery = mysqli_query($connect, "
        SELECT 
            o.id,
            o.name,
            COUNT(DISTINCT u.user_id) as staffCount,
            COUNT(DISTINCT i.id) as totalInitiatives,
            COUNT(DISTINCT CASE WHEN i.completionDate IS NULL THEN i.id END) as activeInitiatives
        FROM organization o
        LEFT JOIN uc_users u ON u.department = o.id AND u.active = 1
        LEFT JOIN initiative i ON i.projectManager = u.user_id
        WHERE o.id != 'org0' 
        AND o.showInTree = 'Yes'
        GROUP BY o.id, o.name
        ORDER BY o.name
    ");
    
    $departments = array();
    $performanceData = array();
    $distributionData = array('excellent' => 0, 'good' => 0, 'average' => 0, 'poor' => 0);
    
    while ($dept = mysqli_fetch_assoc($departmentsQuery)) {
        $deptId = $dept['id'];
        $deptName = $dept['name'];
        $staffCount = $dept['staffCount'] ?? 0;
        $totalInitiatives = $dept['totalInitiatives'] ?? 0;
        $activeInitiatives = $dept['activeInitiatives'] ?? 0;
        
        // Get department score based on selected metric
        $score = getDepartmentMetricScore($deptId, $metric, $period, $date);
        
        // Get trend data (compare with previous period)
        $previousScore = getDepartmentMetricScore($deptId, $metric, $period, getPreviousDate($period, $date));
        $trend = $score - $previousScore;
        
        // Get additional performance metrics
        $performanceMetrics = getDepartmentPerformanceMetrics($deptId, $period, $date);
        
        // Categorize performance for distribution
        if ($score >= 90) {
            $distributionData['excellent']++;
        } elseif ($score >= 75) {
            $distributionData['good']++;
        } elseif ($score >= 60) {
            $distributionData['average']++;
        } else {
            $distributionData['poor']++;
        }
        
        $departments[] = array(
            'id' => $deptId,
            'name' => $deptName,
            'score' => round($score, 1),
            'trend' => round($trend, 1),
            'staffCount' => $staffCount,
            'initiatives' => $activeInitiatives,
            'totalInitiatives' => $totalInitiatives,
            'isCurrentDept' => $deptId === $departmentId,
            'metrics' => $performanceMetrics
        );
        
        // Add to performance data for chart
        $performanceData[] = array(
            'name' => $deptName,
            'score' => round($score, 1),
            'isCurrentDept' => $deptId === $departmentId
        );
    }
    
    // Sort departments by score (descending)
    usort($departments, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });
    
    // Sort performance data by score (descending)
    usort($performanceData, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });
    
    $response = array(
        'ranking' => $departments,
        'performance' => $performanceData,
        'distribution' => $distributionData
    );
    
    // Add ranking insights
    $currentDeptRank = 0;
    foreach ($departments as $index => $dept) {
        if ($dept['isCurrentDept']) {
            $currentDeptRank = $index + 1;
            break;
        }
    }
    
    $totalDepartments = count($departments);
    $topPerformer = !empty($departments) ? $departments[0] : null;
    $avgScore = !empty($departments) ? array_sum(array_column($departments, 'score')) / count($departments) : 0;
    
    $response['summary'] = array(
        'currentRank' => $currentDeptRank,
        'totalDepartments' => $totalDepartments,
        'topPerformer' => $topPerformer,
        'avgScore' => round($avgScore, 1),
        'percentileRank' => $totalDepartments > 0 ? round((($totalDepartments - $currentDeptRank + 1) / $totalDepartments) * 100, 1) : 0
    );
    
    // Add competitive analysis
    $competitiveGaps = array();
    if ($topPerformer && !$topPerformer['isCurrentDept']) {
        $currentDept = array_filter($departments, function($d) use ($departmentId) {
            return $d['id'] === $departmentId;
        });
        $currentDept = !empty($currentDept) ? array_values($currentDept)[0] : null;
        
        if ($currentDept) {
            $competitiveGaps = array(
                'scoreGap' => round($topPerformer['score'] - $currentDept['score'], 1),
                'staffEfficiency' => $currentDept['staffCount'] > 0 ? round($currentDept['score'] / $currentDept['staffCount'], 2) : 0,
                'initiativeEfficiency' => $currentDept['initiatives'] > 0 ? round($currentDept['score'] / $currentDept['initiatives'], 2) : 0,
                'improvementNeeded' => round(($topPerformer['score'] - $currentDept['score']) / $topPerformer['score'] * 100, 1)
            );
        }
    }
    
    $response['competitiveAnalysis'] = $competitiveGaps;
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching department ranking: ' . $e->getMessage();
}

// Helper function to get department metric score
function getDepartmentMetricScore($departmentId, $metric, $period, $date) {
    global $connect;
    
    switch ($metric) {
        case 'productivity':
            return getDepartmentProductivityScore($departmentId, $period, $date);
        case 'efficiency':
            return getDepartmentEfficiencyScore($departmentId, $period, $date);
        case 'goals':
            return getDepartmentGoalScore($departmentId, $period, $date);
        case 'overall':
        default:
            return getOrgScore($departmentId) ?? 0;
    }
}

// Helper function to get department productivity score
function getDepartmentProductivityScore($departmentId, $period, $date) {
    global $connect;
    
    $dateCondition = getDateCondition($period, $date);
    
    $query = mysqli_query($connect, "
        SELECT AVG(ist.percentageCompletion) as avgCompletion
        FROM initiative_status ist
        INNER JOIN initiative i ON ist.initiativeId = i.id
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        WHERE u.department = '$departmentId'
        AND $dateCondition
        AND ist.updatedOn = (
            SELECT MAX(ist2.updatedOn) 
            FROM initiative_status ist2 
            WHERE ist2.initiativeId = ist.initiativeId
        )
    ");
    
    $result = mysqli_fetch_assoc($query);
    return $result['avgCompletion'] ?? 0;
}

// Helper function to get department efficiency score
function getDepartmentEfficiencyScore($departmentId, $period, $date) {
    global $connect;
    
    $dateCondition = getDateCondition($period, $date);
    
    $query = mysqli_query($connect, "
        SELECT 
            COUNT(DISTINCT CASE WHEN ist.percentageCompletion >= 100 THEN i.id END) as completed,
            COUNT(DISTINCT i.id) as total
        FROM initiative_status ist
        INNER JOIN initiative i ON ist.initiativeId = i.id
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        WHERE u.department = '$departmentId'
        AND $dateCondition
    ");
    
    $result = mysqli_fetch_assoc($query);
    $total = $result['total'] ?? 0;
    $completed = $result['completed'] ?? 0;
    
    return $total > 0 ? ($completed / $total) * 100 : 0;
}

// Helper function to get department goal score
function getDepartmentGoalScore($departmentId, $period, $date) {
    global $connect;
    
    $query = mysqli_query($connect, "
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
    ");
    
    $result = mysqli_fetch_assoc($query);
    return ($result['avgScore'] ?? 0) * 20; // Convert 5-point scale to percentage
}

// Helper function to get department performance metrics
function getDepartmentPerformanceMetrics($departmentId, $period, $date) {
    return array(
        'productivity' => getDepartmentProductivityScore($departmentId, $period, $date),
        'efficiency' => getDepartmentEfficiencyScore($departmentId, $period, $date),
        'goals' => getDepartmentGoalScore($departmentId, $period, $date)
    );
}

// Helper function to get date condition
function getDateCondition($period, $date) {
    switch ($period) {
        case 'month':
            return "DATE_FORMAT(ist.updatedOn, '%Y-%m') = '" . date('Y-m', strtotime($date)) . "'";
        case 'quarter':
            $quarter = ceil(date('n', strtotime($date)) / 3);
            $year = date('Y', strtotime($date));
            return "QUARTER(ist.updatedOn) = $quarter AND YEAR(ist.updatedOn) = $year";
        case 'year':
            return "YEAR(ist.updatedOn) = " . date('Y', strtotime($date));
        default:
            return "ist.updatedOn >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    }
}

// Helper function to get previous date
function getPreviousDate($period, $date) {
    switch ($period) {
        case 'month':
            return date('Y-m', strtotime($date . ' -1 month'));
        case 'quarter':
            return date('Y-m', strtotime($date . ' -3 months'));
        case 'year':
            return date('Y-m', strtotime($date . ' -1 year'));
        default:
            return date('Y-m', strtotime($date . ' -1 month'));
    }
}

echo json_encode($response);
?>
