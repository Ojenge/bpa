<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");

header('Content-Type: application/json');

// Get parameters
$departmentId = $_POST['departmentId'] ?? 'org1';
$period = $_POST['period'] ?? 'months';
$date = $_POST['date'] ?? date("Y-m");

// Initialize response array
$response = array();

try {
    // Get timeline data for projects
    $timelineQuery = "
        SELECT 
            i.id,
            i.name,
            i.startDate,
            i.dueDate,
            i.completionDate,
            i.deliverable,
            u.display_name as managerName,
            ist.percentageCompletion,
            ist.status
        FROM initiative i
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        LEFT JOIN initiative_status ist ON i.id = ist.initiativeId
        WHERE u.department = '$departmentId'
        AND (ist.updatedOn = (
            SELECT MAX(ist2.updatedOn) 
            FROM initiative_status ist2 
            WHERE ist2.initiativeId = ist.initiativeId
        ) OR ist.updatedOn IS NULL)
        ORDER BY i.startDate ASC
    ";
    
    $timelineResult = mysqli_query($connect, $timelineQuery);
    
    $timeline = array();
    $milestones = array();
    $quarterlyData = array('Q1' => 0, 'Q2' => 0, 'Q3' => 0, 'Q4' => 0);
    $monthlyData = array();
    
    // Initialize monthly data for current year
    for ($i = 1; $i <= 12; $i++) {
        $monthKey = date('Y') . '-' . sprintf('%02d', $i);
        $monthlyData[$monthKey] = array(
            'planned' => 0,
            'started' => 0,
            'completed' => 0,
            'overdue' => 0
        );
    }
    
    while ($project = mysqli_fetch_assoc($timelineResult)) {
        $projectId = $project['id'];
        $projectName = $project['name'];
        $startDate = $project['startDate'];
        $dueDate = $project['dueDate'];
        $completionDate = $project['completionDate'];
        $completion = $project['percentageCompletion'] ?? 0;
        $manager = $project['managerName'];
        
        // Determine project status for timeline
        $status = 'planned';
        $statusClass = 'timeline-upcoming';
        
        if ($completionDate || $completion >= 100) {
            $status = 'completed';
            $statusClass = 'timeline-completed';
        } elseif ($dueDate && new DateTime($dueDate) < new DateTime() && $completion < 100) {
            $status = 'overdue';
            $statusClass = 'timeline-overdue';
        } elseif ($completion > 0) {
            $status = 'in-progress';
            $statusClass = 'timeline-current';
        }
        
        // Add to timeline
        $timeline[] = array(
            'id' => $projectId,
            'name' => $projectName,
            'manager' => $manager,
            'startDate' => $startDate,
            'dueDate' => $dueDate,
            'completionDate' => $completionDate,
            'completion' => $completion,
            'status' => $status,
            'statusClass' => $statusClass,
            'deliverable' => $project['deliverable'] ?? 'Not specified'
        );
        
        // Update quarterly data
        if ($dueDate) {
            $quarter = 'Q' . ceil(date('n', strtotime($dueDate)) / 3);
            $quarterlyData[$quarter]++;
        }
        
        // Update monthly data
        if ($startDate) {
            $startMonth = date('Y-m', strtotime($startDate));
            if (isset($monthlyData[$startMonth])) {
                $monthlyData[$startMonth]['started']++;
            }
        }
        
        if ($dueDate) {
            $dueMonth = date('Y-m', strtotime($dueDate));
            if (isset($monthlyData[$dueMonth])) {
                $monthlyData[$dueMonth]['planned']++;
            }
        }
        
        if ($completionDate) {
            $completionMonth = date('Y-m', strtotime($completionDate));
            if (isset($monthlyData[$completionMonth])) {
                $monthlyData[$completionMonth]['completed']++;
            }
        }
        
        // Check for overdue
        if ($status === 'overdue') {
            $currentMonth = date('Y-m');
            if (isset($monthlyData[$currentMonth])) {
                $monthlyData[$currentMonth]['overdue']++;
            }
        }
        
        // Add critical milestones (upcoming deadlines within 30 days)
        if ($dueDate && !$completionDate && $completion < 100) {
            $daysUntilDue = (new DateTime($dueDate))->diff(new DateTime())->days;
            $isOverdue = new DateTime($dueDate) < new DateTime();
            
            if ($daysUntilDue <= 30 || $isOverdue) {
                $milestones[] = array(
                    'projectName' => $projectName,
                    'description' => $project['deliverable'] ?? 'Project completion',
                    'dueDate' => date('M j, Y', strtotime($dueDate)),
                    'daysRemaining' => $isOverdue ? -$daysUntilDue : $daysUntilDue,
                    'status' => $isOverdue ? 'overdue' : 'upcoming',
                    'completion' => $completion,
                    'manager' => $manager,
                    'priority' => $isOverdue ? 'high' : ($daysUntilDue <= 7 ? 'high' : 'medium')
                );
            }
        }
    }
    
    // Sort milestones by due date
    usort($milestones, function($a, $b) {
        return strtotime(str_replace(',', '', $a['dueDate'])) - strtotime(str_replace(',', '', $b['dueDate']));
    });
    
    // Get project dependencies and critical path analysis
    $dependenciesQuery = "
        SELECT 
            i1.name as projectName,
            i2.name as dependsOnProject,
            i1.startDate,
            i1.dueDate,
            i2.completionDate as dependencyCompletion
        FROM initiative i1
        INNER JOIN uc_users u1 ON i1.projectManager = u1.user_id
        LEFT JOIN initiative i2 ON i1.dependsOn = i2.id
        WHERE u1.department = '$departmentId'
        AND i1.dependsOn IS NOT NULL
        ORDER BY i1.startDate
    ";
    
    $dependenciesResult = mysqli_query($connect, $dependenciesQuery);
    $dependencies = array();
    $blockedProjects = 0;
    
    while ($dependency = mysqli_fetch_assoc($dependenciesResult)) {
        $isBlocked = !$dependency['dependencyCompletion'] && 
                    new DateTime($dependency['startDate']) <= new DateTime();
        
        if ($isBlocked) {
            $blockedProjects++;
        }
        
        $dependencies[] = array(
            'project' => $dependency['projectName'],
            'dependsOn' => $dependency['dependsOnProject'],
            'isBlocked' => $isBlocked,
            'startDate' => $dependency['startDate'],
            'dueDate' => $dependency['dueDate']
        );
    }
    
    // Calculate timeline metrics
    $totalProjects = count($timeline);
    $completedProjects = count(array_filter($timeline, function($p) { return $p['status'] === 'completed'; }));
    $overdueProjects = count(array_filter($timeline, function($p) { return $p['status'] === 'overdue'; }));
    $onTrackProjects = count(array_filter($timeline, function($p) { 
        return in_array($p['status'], ['completed', 'in-progress']) && $p['status'] !== 'overdue'; 
    }));
    
    // Build response
    $response = array(
        'timeline' => $timeline,
        'milestones' => array_slice($milestones, 0, 10), // Top 10 critical milestones
        'quarterlyData' => $quarterlyData,
        'monthlyData' => $monthlyData,
        'dependencies' => $dependencies,
        'metrics' => array(
            'totalProjects' => $totalProjects,
            'completedProjects' => $completedProjects,
            'overdueProjects' => $overdueProjects,
            'onTrackProjects' => $onTrackProjects,
            'blockedProjects' => $blockedProjects,
            'onTimeDeliveryRate' => $totalProjects > 0 ? round(($completedProjects / $totalProjects) * 100, 1) : 0,
            'criticalMilestones' => count($milestones)
        )
    );
    
    // Add timeline insights
    $insights = array();
    
    if ($overdueProjects > 0) {
        $insights[] = array(
            'type' => 'warning',
            'title' => 'Overdue Projects',
            'message' => "$overdueProjects projects are overdue and need immediate attention"
        );
    }
    
    if ($blockedProjects > 0) {
        $insights[] = array(
            'type' => 'danger',
            'title' => 'Blocked Projects',
            'message' => "$blockedProjects projects are blocked by incomplete dependencies"
        );
    }
    
    $upcomingDeadlines = count(array_filter($milestones, function($m) { 
        return $m['status'] === 'upcoming' && $m['daysRemaining'] <= 7; 
    }));
    
    if ($upcomingDeadlines > 0) {
        $insights[] = array(
            'type' => 'info',
            'title' => 'Upcoming Deadlines',
            'message' => "$upcomingDeadlines critical deadlines in the next 7 days"
        );
    }
    
    $response['insights'] = $insights;
    
    // Add resource timeline analysis
    $resourceTimelineQuery = "
        SELECT 
            DATE_FORMAT(i.startDate, '%Y-%m') as month,
            COUNT(DISTINCT i.id) as projectsStarted,
            COUNT(DISTINCT u.user_id) as resourcesNeeded
        FROM initiative i
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        WHERE u.department = '$departmentId'
        AND i.startDate >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(i.startDate, '%Y-%m')
        ORDER BY month
    ";
    
    $resourceTimelineResult = mysqli_query($connect, $resourceTimelineQuery);
    $resourceTimeline = array();
    
    while ($resource = mysqli_fetch_assoc($resourceTimelineResult)) {
        $resourceTimeline[] = array(
            'month' => $resource['month'],
            'projectsStarted' => $resource['projectsStarted'],
            'resourcesNeeded' => $resource['resourcesNeeded']
        );
    }
    
    $response['resourceTimeline'] = $resourceTimeline;
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching timeline analysis: ' . $e->getMessage();
}

echo json_encode($response);
?>
