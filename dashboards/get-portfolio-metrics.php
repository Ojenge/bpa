<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");

header('Content-Type: application/json');

// Get parameters
$departmentId = $_POST['departmentId'] ?? 'org1';
$period = $_POST['period'] ?? 'months';
$date = $_POST['date'] ?? date("Y-m");
$statusFilter = $_POST['statusFilter'] ?? 'all';

// Initialize response array
$response = array();

try {
    // Get department projects/initiatives
    $projectsQuery = "
        SELECT 
            i.id,
            i.name,
            i.budget,
            i.startDate,
            i.dueDate,
            i.completionDate,
            u.display_name as managerName,
            ist.percentageCompletion,
            ist.status,
            ist.updatedOn as lastUpdate
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
    
    // Apply status filter
    if ($statusFilter !== 'all') {
        switch ($statusFilter) {
            case 'active':
                $projectsQuery .= " AND (ist.percentageCompletion < 100 OR ist.percentageCompletion IS NULL) AND i.completionDate IS NULL";
                break;
            case 'completed':
                $projectsQuery .= " AND (ist.percentageCompletion >= 100 OR i.completionDate IS NOT NULL)";
                break;
            case 'at-risk':
                $projectsQuery .= " AND i.dueDate < NOW() AND (ist.percentageCompletion < 100 OR ist.percentageCompletion IS NULL)";
                break;
            case 'overdue':
                $projectsQuery .= " AND i.dueDate < NOW() AND (ist.percentageCompletion < 100 OR ist.percentageCompletion IS NULL)";
                break;
        }
    }
    
    $projectsQuery .= " ORDER BY i.startDate DESC";
    
    $projectsResult = mysqli_query($connect, $projectsQuery);
    
    $totalProjects = 0;
    $totalBudget = 0;
    $totalCompletion = 0;
    $completedProjects = 0;
    $activeProjects = 0;
    $atRiskProjects = 0;
    $overdueProjects = 0;
    $notStartedProjects = 0;
    
    $statusDistribution = array(
        'completed' => 0,
        'in_progress' => 0,
        'at_risk' => 0,
        'not_started' => 0,
        'overdue' => 0
    );
    
    $budgetAllocation = array(
        'strategic' => 0,
        'operational' => 0,
        'innovation' => 0,
        'maintenance' => 0
    );
    
    while ($project = mysqli_fetch_assoc($projectsResult)) {
        $totalProjects++;
        $budget = $project['budget'] ?? 0;
        $completion = $project['percentageCompletion'] ?? 0;
        $dueDate = $project['dueDate'];
        $completionDate = $project['completionDate'];
        
        $totalBudget += $budget;
        $totalCompletion += $completion;
        
        // Categorize project status
        if ($completionDate || $completion >= 100) {
            $completedProjects++;
            $statusDistribution['completed']++;
        } elseif ($dueDate && new DateTime($dueDate) < new DateTime() && $completion < 100) {
            $overdueProjects++;
            $statusDistribution['overdue']++;
        } elseif ($completion > 0 && $completion < 75) {
            $atRiskProjects++;
            $statusDistribution['at_risk']++;
        } elseif ($completion > 0) {
            $activeProjects++;
            $statusDistribution['in_progress']++;
        } else {
            $notStartedProjects++;
            $statusDistribution['not_started']++;
        }
        
        // Categorize budget allocation (simplified categorization based on project name/type)
        $projectName = strtolower($project['name']);
        if (strpos($projectName, 'strategic') !== false || strpos($projectName, 'strategy') !== false) {
            $budgetAllocation['strategic'] += $budget;
        } elseif (strpos($projectName, 'innovation') !== false || strpos($projectName, 'research') !== false) {
            $budgetAllocation['innovation'] += $budget;
        } elseif (strpos($projectName, 'maintenance') !== false || strpos($projectName, 'support') !== false) {
            $budgetAllocation['maintenance'] += $budget;
        } else {
            $budgetAllocation['operational'] += $budget;
        }
    }
    
    // Calculate metrics
    $avgCompletion = $totalProjects > 0 ? round($totalCompletion / $totalProjects, 1) : 0;
    $portfolioValue = $totalBudget;
    
    // Calculate ROI (simplified calculation)
    $estimatedBenefits = $totalBudget * 1.25; // Assume 25% benefit over cost
    $actualCosts = $totalBudget * 0.85; // Assume 85% of budget utilized
    $portfolioROI = $actualCosts > 0 ? round((($estimatedBenefits - $actualCosts) / $actualCosts) * 100, 1) : 0;
    
    // Get performance trends (last 6 months)
    $performanceTrends = array();
    for ($i = 5; $i >= 0; $i--) {
        $monthDate = date('Y-m', strtotime("-$i months"));
        
        $trendQuery = mysqli_query($connect, "
            SELECT 
                AVG(ist.percentageCompletion) as avgCompletion,
                COUNT(DISTINCT i.id) as projectCount
            FROM initiative i
            INNER JOIN uc_users u ON i.projectManager = u.user_id
            LEFT JOIN initiative_status ist ON i.id = ist.initiativeId
            WHERE u.department = '$departmentId'
            AND DATE_FORMAT(ist.updatedOn, '%Y-%m') = '$monthDate'
        ");
        
        $trendResult = mysqli_fetch_assoc($trendQuery);
        $performanceTrends[] = array(
            'month' => date('M', strtotime($monthDate . '-01')),
            'completion' => round($trendResult['avgCompletion'] ?? 0, 1),
            'projectCount' => $trendResult['projectCount'] ?? 0
        );
    }
    
    // Build response
    $response = array(
        'totalProjects' => $totalProjects,
        'portfolioValue' => $portfolioValue,
        'avgCompletion' => $avgCompletion,
        'portfolioROI' => $portfolioROI,
        'statusDistribution' => $statusDistribution,
        'budgetAllocation' => $budgetAllocation,
        'performanceTrends' => $performanceTrends,
        'keyMetrics' => array(
            'onTime' => $activeProjects + $completedProjects,
            'atRisk' => $atRiskProjects + $overdueProjects,
            'budgetUtilization' => $totalBudget > 0 ? round(($actualCosts / $totalBudget) * 100, 1) : 0,
            'resourceUtilization' => 85 // Placeholder - would need actual resource data
        )
    );
    
    // Add project health indicators
    $response['healthIndicators'] = array(
        'onTrack' => $completedProjects + $activeProjects,
        'needsAttention' => $atRiskProjects,
        'critical' => $overdueProjects,
        'notStarted' => $notStartedProjects
    );
    
    // Add budget analysis
    $response['budgetAnalysis'] = array(
        'totalAllocated' => $totalBudget,
        'totalUtilized' => $actualCosts,
        'utilizationRate' => $totalBudget > 0 ? round(($actualCosts / $totalBudget) * 100, 1) : 0,
        'remainingBudget' => $totalBudget - $actualCosts,
        'projectedOverrun' => max(0, $actualCosts - $totalBudget)
    );
    
    // Add timeline analysis
    $upcomingDeadlines = 0;
    $overdueCount = 0;
    $completedOnTime = 0;
    
    $timelineQuery = mysqli_query($connect, "
        SELECT 
            i.dueDate,
            i.completionDate,
            ist.percentageCompletion
        FROM initiative i
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        LEFT JOIN initiative_status ist ON i.id = ist.initiativeId
        WHERE u.department = '$departmentId'
        AND i.dueDate IS NOT NULL
    ");
    
    while ($timeline = mysqli_fetch_assoc($timelineQuery)) {
        $dueDate = new DateTime($timeline['dueDate']);
        $completionDate = $timeline['completionDate'] ? new DateTime($timeline['completionDate']) : null;
        $completion = $timeline['percentageCompletion'] ?? 0;
        $now = new DateTime();
        
        if ($completionDate) {
            if ($completionDate <= $dueDate) {
                $completedOnTime++;
            }
        } elseif ($completion >= 100) {
            $completedOnTime++;
        } elseif ($dueDate < $now && $completion < 100) {
            $overdueCount++;
        } elseif ($dueDate <= (new DateTime())->add(new DateInterval('P30D'))) {
            $upcomingDeadlines++;
        }
    }
    
    $response['timelineAnalysis'] = array(
        'upcomingDeadlines' => $upcomingDeadlines,
        'overdueProjects' => $overdueCount,
        'completedOnTime' => $completedOnTime,
        'onTimeDeliveryRate' => $totalProjects > 0 ? round(($completedOnTime / $totalProjects) * 100, 1) : 0
    );
    
    // Add risk assessment summary
    $response['riskSummary'] = array(
        'highRisk' => $overdueProjects,
        'mediumRisk' => $atRiskProjects,
        'lowRisk' => $activeProjects + $completedProjects,
        'riskScore' => calculatePortfolioRiskScore($overdueProjects, $atRiskProjects, $totalProjects)
    );
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching portfolio metrics: ' . $e->getMessage();
}

// Helper function to calculate portfolio risk score
function calculatePortfolioRiskScore($overdueProjects, $atRiskProjects, $totalProjects) {
    if ($totalProjects == 0) return 0;
    
    $riskWeight = ($overdueProjects * 3 + $atRiskProjects * 2) / $totalProjects;
    return min(100, round($riskWeight * 20, 1)); // Scale to 0-100
}

echo json_encode($response);
?>
