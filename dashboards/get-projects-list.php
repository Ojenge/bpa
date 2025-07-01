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
    // Get detailed project information
    $projectsQuery = "
        SELECT 
            i.id,
            i.name,
            i.budget,
            i.startDate,
            i.dueDate,
            i.completionDate,
            i.deliverable,
            i.scope,
            i.type,
            u.display_name as managerName,
            u.user_id as managerId,
            s.display_name as sponsorName,
            ist.percentageCompletion,
            ist.status,
            ist.details,
            ist.notes,
            ist.updatedOn as lastUpdate,
            ist.updatedBy,
            ii.linkedobjectid as linkedObject
        FROM initiative i
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        LEFT JOIN uc_users s ON i.sponsor = s.user_id
        LEFT JOIN initiative_status ist ON i.id = ist.initiativeId
        LEFT JOIN initiativeimpact ii ON i.id = ii.initiativeid
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
    
    $projects = array();
    
    while ($project = mysqli_fetch_assoc($projectsResult)) {
        $projectId = $project['id'];
        $completion = $project['percentageCompletion'] ?? 0;
        $budget = $project['budget'] ?? 0;
        $dueDate = $project['dueDate'];
        $completionDate = $project['completionDate'];
        $startDate = $project['startDate'];
        
        // Calculate project status
        $status = 'not-started';
        $riskLevel = 'low';
        
        if ($completionDate || $completion >= 100) {
            $status = 'completed';
            $riskLevel = 'low';
        } elseif ($dueDate && new DateTime($dueDate) < new DateTime() && $completion < 100) {
            $status = 'overdue';
            $riskLevel = 'high';
        } elseif ($completion > 0 && $completion < 75) {
            $status = 'at-risk';
            $riskLevel = 'medium';
        } elseif ($completion > 0) {
            $status = 'in-progress';
            $riskLevel = 'low';
        }
        
        // Calculate budget utilization (simplified)
        $budgetUtilization = $completion; // Assume budget utilization matches completion
        
        // Calculate days remaining
        $daysRemaining = 0;
        if ($dueDate && !$completionDate) {
            $today = new DateTime();
            $due = new DateTime($dueDate);
            $interval = $today->diff($due);
            $daysRemaining = $interval->invert ? -$interval->days : $interval->days;
        }
        
        // Get project issues
        $issuesQuery = mysqli_query($connect, "
            SELECT COUNT(*) as issueCount,
                   SUM(CASE WHEN severity = 'High' THEN 1 ELSE 0 END) as highSeverityCount
            FROM initiative_issue 
            WHERE initiativeId = '$projectId' 
            AND status != 'Resolved'
        ");
        $issuesResult = mysqli_fetch_assoc($issuesQuery);
        $issueCount = $issuesResult['issueCount'] ?? 0;
        $highSeverityIssues = $issuesResult['highSeverityCount'] ?? 0;
        
        // Adjust risk level based on issues
        if ($highSeverityIssues > 0) {
            $riskLevel = 'high';
        } elseif ($issueCount > 2) {
            $riskLevel = 'medium';
        }
        
        // Get linked object information
        $linkedObjectName = 'Not linked';
        if ($project['linkedObject']) {
            $linkedId = $project['linkedObject'];
            $objectType = substr($linkedId, 0, 3);
            
            switch ($objectType) {
                case 'org':
                    $linkedQuery = mysqli_query($connect, "SELECT name FROM organization WHERE id = '$linkedId'");
                    break;
                case 'obj':
                    $linkedQuery = mysqli_query($connect, "SELECT name FROM objective WHERE id = '$linkedId'");
                    break;
                case 'kpi':
                    $linkedQuery = mysqli_query($connect, "SELECT name FROM measure WHERE id = '$linkedId'");
                    break;
                default:
                    $linkedQuery = null;
            }
            
            if ($linkedQuery) {
                $linkedResult = mysqli_fetch_assoc($linkedQuery);
                $linkedObjectName = $linkedResult['name'] ?? 'Unknown';
            }
        }
        
        // Calculate estimated completion date
        $estimatedCompletion = 'Unknown';
        if ($completion > 0 && $completion < 100 && $startDate) {
            $start = new DateTime($startDate);
            $today = new DateTime();
            $daysPassed = $start->diff($today)->days;
            
            if ($daysPassed > 0) {
                $progressRate = $completion / $daysPassed;
                $remainingProgress = 100 - $completion;
                $daysToComplete = $remainingProgress / $progressRate;
                
                $estimatedDate = clone $today;
                $estimatedDate->add(new DateInterval('P' . round($daysToComplete) . 'D'));
                $estimatedCompletion = $estimatedDate->format('M j, Y');
            }
        } elseif ($completion >= 100) {
            $estimatedCompletion = 'Completed';
        }
        
        // Get recent status updates
        $recentUpdatesQuery = mysqli_query($connect, "
            SELECT status, details, updatedOn, updatedBy
            FROM initiative_status 
            WHERE initiativeId = '$projectId'
            ORDER BY updatedOn DESC
            LIMIT 3
        ");
        
        $recentUpdates = array();
        while ($update = mysqli_fetch_assoc($recentUpdatesQuery)) {
            $recentUpdates[] = array(
                'status' => $update['status'],
                'details' => $update['details'],
                'date' => date('M j, Y', strtotime($update['updatedOn'])),
                'updatedBy' => $update['updatedBy']
            );
        }
        
        $projects[] = array(
            'id' => $projectId,
            'name' => $project['name'],
            'manager' => $project['managerName'] ?? 'Unassigned',
            'managerId' => $project['managerId'],
            'sponsor' => $project['sponsorName'] ?? 'Not assigned',
            'budget' => $budget,
            'budgetUtilization' => $budgetUtilization,
            'startDate' => $startDate ? date('M j, Y', strtotime($startDate)) : 'Not set',
            'dueDate' => $dueDate ? date('M j, Y', strtotime($dueDate)) : 'Not set',
            'completionDate' => $completionDate ? date('M j, Y', strtotime($completionDate)) : null,
            'completion' => $completion,
            'status' => $status,
            'riskLevel' => $riskLevel,
            'daysRemaining' => $daysRemaining,
            'issueCount' => $issueCount,
            'highSeverityIssues' => $highSeverityIssues,
            'deliverable' => $project['deliverable'] ?? 'Not specified',
            'scope' => $project['scope'] ?? 'Not defined',
            'type' => $project['type'] ?? 'General',
            'linkedObject' => $linkedObjectName,
            'estimatedCompletion' => $estimatedCompletion,
            'lastUpdate' => $project['lastUpdate'] ? date('M j, Y', strtotime($project['lastUpdate'])) : 'Never updated',
            'recentUpdates' => $recentUpdates,
            'currentStatus' => $project['status'] ?? 'No status',
            'statusDetails' => $project['details'] ?? 'No details available',
            'notes' => $project['notes'] ?? ''
        );
    }
    
    // Calculate summary statistics
    $totalProjects = count($projects);
    $completedProjects = count(array_filter($projects, function($p) { return $p['status'] === 'completed'; }));
    $activeProjects = count(array_filter($projects, function($p) { return in_array($p['status'], ['in-progress', 'at-risk']); }));
    $overdueProjects = count(array_filter($projects, function($p) { return $p['status'] === 'overdue'; }));
    $totalBudget = array_sum(array_column($projects, 'budget'));
    $avgCompletion = $totalProjects > 0 ? round(array_sum(array_column($projects, 'completion')) / $totalProjects, 1) : 0;
    
    $response = array(
        'projects' => $projects,
        'summary' => array(
            'total' => $totalProjects,
            'completed' => $completedProjects,
            'active' => $activeProjects,
            'overdue' => $overdueProjects,
            'totalBudget' => $totalBudget,
            'avgCompletion' => $avgCompletion,
            'completionRate' => $totalProjects > 0 ? round(($completedProjects / $totalProjects) * 100, 1) : 0
        )
    );
    
    // Add portfolio health indicators
    $highRiskProjects = count(array_filter($projects, function($p) { return $p['riskLevel'] === 'high'; }));
    $mediumRiskProjects = count(array_filter($projects, function($p) { return $p['riskLevel'] === 'medium'; }));
    $lowRiskProjects = count(array_filter($projects, function($p) { return $p['riskLevel'] === 'low'; }));
    
    $response['riskDistribution'] = array(
        'high' => $highRiskProjects,
        'medium' => $mediumRiskProjects,
        'low' => $lowRiskProjects
    );
    
    // Add performance insights
    $onTimeProjects = count(array_filter($projects, function($p) { 
        return $p['status'] === 'completed' || ($p['daysRemaining'] >= 0 && $p['status'] !== 'overdue'); 
    }));
    
    $response['performanceInsights'] = array(
        'onTimeDelivery' => $totalProjects > 0 ? round(($onTimeProjects / $totalProjects) * 100, 1) : 0,
        'budgetEfficiency' => $totalBudget > 0 ? round((array_sum(array_column($projects, 'budgetUtilization')) / count($projects)), 1) : 0,
        'averageProjectDuration' => calculateAverageProjectDuration($projects),
        'successRate' => $totalProjects > 0 ? round(($completedProjects / $totalProjects) * 100, 1) : 0
    );
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching projects list: ' . $e->getMessage();
}

// Helper function to calculate average project duration
function calculateAverageProjectDuration($projects) {
    $durations = array();
    
    foreach ($projects as $project) {
        if ($project['startDate'] !== 'Not set' && $project['completionDate']) {
            $start = new DateTime(str_replace(',', '', $project['startDate']));
            $end = new DateTime(str_replace(',', '', $project['completionDate']));
            $duration = $start->diff($end)->days;
            $durations[] = $duration;
        }
    }
    
    return !empty($durations) ? round(array_sum($durations) / count($durations), 0) : 0;
}

echo json_encode($response);
?>
