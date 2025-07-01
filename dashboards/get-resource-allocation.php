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
    // Get resource allocation data
    $resourceQuery = "
        SELECT 
            u.user_id,
            u.display_name,
            u.email,
            u.role,
            COUNT(DISTINCT i.id) as projectCount,
            AVG(ist.percentageCompletion) as avgCompletion,
            SUM(i.budget) as totalBudgetManaged,
            MAX(ist.updatedOn) as lastActivity
        FROM uc_users u
        LEFT JOIN initiative i ON u.user_id = i.projectManager
        LEFT JOIN initiative_status ist ON i.id = ist.initiativeId
        WHERE u.department = '$departmentId'
        AND u.active = 1
        GROUP BY u.user_id, u.display_name, u.email, u.role
        ORDER BY u.display_name
    ";
    
    $resourceResult = mysqli_query($connect, $resourceQuery);
    
    $resources = array();
    $totalStaff = 0;
    $totalProjects = 0;
    $totalBudget = 0;
    $utilizationData = array();
    
    while ($resource = mysqli_fetch_assoc($resourceResult)) {
        $userId = $resource['user_id'];
        $userName = $resource['display_name'];
        $projectCount = $resource['projectCount'] ?? 0;
        $avgCompletion = $resource['avgCompletion'] ?? 0;
        $budgetManaged = $resource['totalBudgetManaged'] ?? 0;
        $lastActivity = $resource['lastActivity'];
        
        $totalStaff++;
        $totalProjects += $projectCount;
        $totalBudget += $budgetManaged;
        
        // Calculate workload and efficiency
        $workload = min(100, $projectCount * 25); // Assume 25% per project, max 100%
        $efficiency = $avgCompletion > 0 ? round($avgCompletion, 1) : 0;
        
        // Determine efficiency class
        $efficiencyClass = 'efficiency-low';
        if ($efficiency >= 80) {
            $efficiencyClass = 'efficiency-high';
        } elseif ($efficiency >= 60) {
            $efficiencyClass = 'efficiency-medium';
        }
        
        // Get current active projects for this resource
        $activeProjectsQuery = mysqli_query($connect, "
            SELECT 
                i.name,
                i.dueDate,
                ist.percentageCompletion,
                ist.status
            FROM initiative i
            LEFT JOIN initiative_status ist ON i.id = ist.initiativeId
            WHERE i.projectManager = '$userId'
            AND (ist.percentageCompletion < 100 OR ist.percentageCompletion IS NULL)
            AND i.completionDate IS NULL
            ORDER BY i.dueDate ASC
        ");
        
        $activeProjects = array();
        while ($project = mysqli_fetch_assoc($activeProjectsQuery)) {
            $activeProjects[] = array(
                'name' => $project['name'],
                'dueDate' => $project['dueDate'] ? date('M j, Y', strtotime($project['dueDate'])) : 'Not set',
                'completion' => $project['percentageCompletion'] ?? 0,
                'status' => $project['status'] ?? 'No status'
            );
        }
        
        // Calculate availability (simplified)
        $availability = max(0, 100 - $workload);
        
        $resources[] = array(
            'id' => $userId,
            'name' => $userName,
            'email' => $resource['email'],
            'role' => $resource['role'] ?? 'Staff',
            'projectCount' => $projectCount,
            'workload' => $workload,
            'availability' => $availability,
            'efficiency' => $efficiency,
            'efficiencyClass' => $efficiencyClass,
            'budgetManaged' => $budgetManaged,
            'lastActivity' => $lastActivity ? date('M j, Y', strtotime($lastActivity)) : 'No activity',
            'activeProjects' => $activeProjects
        );
        
        // Add to utilization data
        $utilizationData[] = array(
            'name' => $userName,
            'utilization' => $workload,
            'efficiency' => $efficiency
        );
    }
    
    // Calculate department resource metrics
    $avgWorkload = $totalStaff > 0 ? round(array_sum(array_column($resources, 'workload')) / $totalStaff, 1) : 0;
    $avgEfficiency = $totalStaff > 0 ? round(array_sum(array_column($resources, 'efficiency')) / $totalStaff, 1) : 0;
    $avgProjectsPerPerson = $totalStaff > 0 ? round($totalProjects / $totalStaff, 1) : 0;
    
    // Get skill distribution
    $skillsQuery = "
        SELECT 
            u.role,
            COUNT(*) as count,
            AVG(
                (SELECT AVG(ist.percentageCompletion) 
                 FROM initiative i 
                 LEFT JOIN initiative_status ist ON i.id = ist.initiativeId 
                 WHERE i.projectManager = u.user_id)
            ) as avgPerformance
        FROM uc_users u
        WHERE u.department = '$departmentId'
        AND u.active = 1
        GROUP BY u.role
        ORDER BY count DESC
    ";
    
    $skillsResult = mysqli_query($connect, $skillsQuery);
    $skillDistribution = array();
    
    while ($skill = mysqli_fetch_assoc($skillsResult)) {
        $skillDistribution[] = array(
            'role' => $skill['role'] ?? 'General',
            'count' => $skill['count'],
            'avgPerformance' => round($skill['avgPerformance'] ?? 0, 1)
        );
    }
    
    // Get capacity planning data
    $capacityQuery = "
        SELECT 
            DATE_FORMAT(i.startDate, '%Y-%m') as month,
            COUNT(DISTINCT i.id) as projectsStarted,
            COUNT(DISTINCT i.projectManager) as resourcesUsed,
            SUM(i.budget) as budgetAllocated
        FROM initiative i
        INNER JOIN uc_users u ON i.projectManager = u.user_id
        WHERE u.department = '$departmentId'
        AND i.startDate >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(i.startDate, '%Y-%m')
        ORDER BY month
    ";
    
    $capacityResult = mysqli_query($connect, $capacityQuery);
    $capacityData = array();
    
    while ($capacity = mysqli_fetch_assoc($capacityResult)) {
        $capacityData[] = array(
            'month' => date('M Y', strtotime($capacity['month'] . '-01')),
            'projectsStarted' => $capacity['projectsStarted'],
            'resourcesUsed' => $capacity['resourcesUsed'],
            'budgetAllocated' => $capacity['budgetAllocated'],
            'utilizationRate' => $totalStaff > 0 ? round(($capacity['resourcesUsed'] / $totalStaff) * 100, 1) : 0
        );
    }
    
    // Identify resource bottlenecks
    $bottlenecks = array();
    $overutilizedResources = array_filter($resources, function($r) { return $r['workload'] > 80; });
    $underutilizedResources = array_filter($resources, function($r) { return $r['workload'] < 40 && $r['projectCount'] > 0; });
    
    if (!empty($overutilizedResources)) {
        $bottlenecks[] = array(
            'type' => 'overutilization',
            'title' => 'Overutilized Resources',
            'count' => count($overutilizedResources),
            'message' => count($overutilizedResources) . ' team members are overutilized (>80% capacity)',
            'resources' => array_column($overutilizedResources, 'name')
        );
    }
    
    if (!empty($underutilizedResources)) {
        $bottlenecks[] = array(
            'type' => 'underutilization',
            'title' => 'Underutilized Resources',
            'count' => count($underutilizedResources),
            'message' => count($underutilizedResources) . ' team members have capacity for additional projects',
            'resources' => array_column($underutilizedResources, 'name')
        );
    }
    
    // Calculate resource allocation efficiency
    $allocationEfficiency = array();
    foreach ($skillDistribution as $skill) {
        $skillResources = array_filter($resources, function($r) use ($skill) {
            return $r['role'] === $skill['role'];
        });
        
        $avgWorkloadForSkill = !empty($skillResources) ? 
            array_sum(array_column($skillResources, 'workload')) / count($skillResources) : 0;
        
        $allocationEfficiency[] = array(
            'skill' => $skill['role'],
            'count' => $skill['count'],
            'avgWorkload' => round($avgWorkloadForSkill, 1),
            'avgPerformance' => $skill['avgPerformance'],
            'efficiency' => $skill['avgPerformance'] > 0 ? round(($avgWorkloadForSkill / $skill['avgPerformance']) * 100, 1) : 0
        );
    }
    
    // Build response
    $response = array(
        'resources' => $resources,
        'utilization' => $utilizationData,
        'metrics' => array(
            'totalStaff' => $totalStaff,
            'totalProjects' => $totalProjects,
            'totalBudget' => $totalBudget,
            'avgWorkload' => $avgWorkload,
            'avgEfficiency' => $avgEfficiency,
            'avgProjectsPerPerson' => $avgProjectsPerPerson,
            'utilizationRate' => $avgWorkload
        ),
        'skillDistribution' => $skillDistribution,
        'capacityData' => $capacityData,
        'bottlenecks' => $bottlenecks,
        'allocationEfficiency' => $allocationEfficiency
    );
    
    // Add resource recommendations
    $recommendations = array();
    
    if ($avgWorkload > 80) {
        $recommendations[] = array(
            'type' => 'warning',
            'title' => 'High Department Utilization',
            'message' => 'Department utilization is ' . $avgWorkload . '%. Consider hiring additional staff or redistributing workload.',
            'priority' => 'high'
        );
    }
    
    if ($avgEfficiency < 60) {
        $recommendations[] = array(
            'type' => 'info',
            'title' => 'Efficiency Improvement',
            'message' => 'Average efficiency is ' . $avgEfficiency . '%. Consider training or process improvements.',
            'priority' => 'medium'
        );
    }
    
    if (count($overutilizedResources) > 0) {
        $recommendations[] = array(
            'type' => 'danger',
            'title' => 'Resource Rebalancing',
            'message' => 'Redistribute projects from overutilized to underutilized team members.',
            'priority' => 'high'
        );
    }
    
    $response['recommendations'] = $recommendations;
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching resource allocation: ' . $e->getMessage();
}

echo json_encode($response);
?>
