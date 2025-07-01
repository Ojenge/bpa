<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");

header('Content-Type: application/json');

// Get parameters
$departmentId = $_POST['departmentId'] ?? 'org1';
$period = $_POST['period'] ?? 'months';
$date = $_POST['date'] ?? date("Y-m");
$performanceFilter = $_POST['performanceFilter'] ?? 'all';

// Initialize response array
$response = array();

try {
    // Get detailed staff information
    $staffQuery = "
        SELECT 
            u.user_id,
            u.display_name,
            u.email,
            u.role,
            u.active,
            u.last_login,
            u.date_added,
            u.phone,
            COUNT(DISTINCT i.id) as projectCount,
            AVG(ist.percentageCompletion) as avgProjectCompletion,
            MAX(ist.updatedOn) as lastProjectUpdate
        FROM uc_users u
        LEFT JOIN initiative i ON u.user_id = i.projectManager
        LEFT JOIN initiative_status ist ON i.id = ist.initiativeId
        WHERE u.department = '$departmentId'
        AND u.active = 1
        GROUP BY u.user_id, u.display_name, u.email, u.role, u.active, u.last_login, u.date_added, u.phone
        ORDER BY u.display_name
    ";
    
    $staffResult = mysqli_query($connect, $staffQuery);
    
    $staff = array();
    
    while ($member = mysqli_fetch_assoc($staffResult)) {
        $userId = $member['user_id'];
        $name = $member['display_name'];
        $email = $member['email'];
        $role = $member['role'] ?? 'General';
        $projectCount = $member['projectCount'] ?? 0;
        $avgProjectCompletion = $member['avgProjectCompletion'] ?? 0;
        $lastLogin = $member['last_login'];
        $lastProjectUpdate = $member['lastProjectUpdate'];
        $dateAdded = $member['date_added'];
        
        // Calculate performance score
        $performanceScore = 0;
        if ($projectCount > 0) {
            $performanceScore = $avgProjectCompletion;
        } else {
            $performanceScore = 50; // Base score for staff without projects
        }
        
        // Adjust based on activity
        $daysSinceLogin = 999;
        if ($lastLogin) {
            $daysSinceLogin = (time() - strtotime($lastLogin)) / (60 * 60 * 24);
            if ($daysSinceLogin <= 7) {
                $performanceScore += 10;
            } elseif ($daysSinceLogin > 30) {
                $performanceScore -= 10;
            }
        }
        
        $performanceScore = max(0, min(100, $performanceScore));
        
        // Calculate engagement score
        $engagementScore = 50; // Base engagement
        if ($projectCount > 0) {
            $engagementScore += min(30, $projectCount * 10);
        }
        if ($daysSinceLogin <= 7) {
            $engagementScore += 20;
        }
        $engagementScore = max(0, min(100, $engagementScore));
        
        // Calculate tenure
        $tenure = (time() - strtotime($dateAdded)) / (60 * 60 * 24 * 365); // Years
        
        // Get current projects
        $currentProjectsQuery = mysqli_query($connect, "
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
            LIMIT 3
        ");
        
        $currentProjects = array();
        while ($project = mysqli_fetch_assoc($currentProjectsQuery)) {
            $currentProjects[] = array(
                'name' => $project['name'],
                'dueDate' => $project['dueDate'] ? date('M j, Y', strtotime($project['dueDate'])) : 'Not set',
                'completion' => $project['percentageCompletion'] ?? 0,
                'status' => $project['status'] ?? 'No status'
            );
        }
        
        // Get skills/competencies (simplified - based on role and projects)
        $skills = getStaffSkills($role, $projectCount, $tenure);
        
        // Calculate development progress
        $developmentProgress = min(100, 60 + ($tenure * 10) + ($projectCount * 5));
        
        // Determine performance category
        $performanceCategory = 'needs-attention';
        if ($performanceScore >= 90) {
            $performanceCategory = 'excellent';
        } elseif ($performanceScore >= 75) {
            $performanceCategory = 'good';
        } elseif ($performanceScore >= 60) {
            $performanceCategory = 'average';
        }
        
        // Apply performance filter
        if ($performanceFilter !== 'all') {
            if ($performanceFilter === 'high' && $performanceCategory !== 'excellent') continue;
            if ($performanceFilter === 'average' && !in_array($performanceCategory, ['good', 'average'])) continue;
            if ($performanceFilter === 'needs-attention' && $performanceCategory !== 'needs-attention') continue;
        }
        
        // Get recent achievements
        $achievementsQuery = mysqli_query($connect, "
            SELECT 
                i.name,
                i.completionDate
            FROM initiative i
            WHERE i.projectManager = '$userId'
            AND i.completionDate IS NOT NULL
            AND i.completionDate >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
            ORDER BY i.completionDate DESC
            LIMIT 3
        ");
        
        $recentAchievements = array();
        while ($achievement = mysqli_fetch_assoc($achievementsQuery)) {
            $recentAchievements[] = array(
                'name' => $achievement['name'],
                'completionDate' => date('M j, Y', strtotime($achievement['completionDate']))
            );
        }
        
        // Calculate workload
        $workload = min(100, $projectCount * 25); // Assume 25% per project
        
        // Determine next development goals
        $developmentGoals = getNextDevelopmentGoals($role, $skills, $performanceScore);
        
        $staff[] = array(
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'projectCount' => $projectCount,
            'performanceScore' => round($performanceScore, 1),
            'engagementScore' => round($engagementScore, 1),
            'performanceCategory' => $performanceCategory,
            'tenure' => round($tenure, 1),
            'workload' => $workload,
            'developmentProgress' => round($developmentProgress, 1),
            'lastActivity' => $lastLogin ? date('M j, Y', strtotime($lastLogin)) : 'Never',
            'daysSinceLogin' => round($daysSinceLogin, 0),
            'currentProjects' => $currentProjects,
            'skills' => $skills,
            'recentAchievements' => $recentAchievements,
            'developmentGoals' => $developmentGoals,
            'phone' => $member['phone'] ?? 'Not provided',
            'joinDate' => date('M j, Y', strtotime($dateAdded)),
            'lastProjectUpdate' => $lastProjectUpdate ? date('M j, Y', strtotime($lastProjectUpdate)) : 'No updates'
        );
    }
    
    // Calculate summary statistics
    $totalStaff = count($staff);
    $avgPerformance = $totalStaff > 0 ? round(array_sum(array_column($staff, 'performanceScore')) / $totalStaff, 1) : 0;
    $avgEngagement = $totalStaff > 0 ? round(array_sum(array_column($staff, 'engagementScore')) / $totalStaff, 1) : 0;
    $avgTenure = $totalStaff > 0 ? round(array_sum(array_column($staff, 'tenure')) / $totalStaff, 1) : 0;
    $totalProjects = array_sum(array_column($staff, 'projectCount'));
    
    // Performance distribution
    $excellentCount = count(array_filter($staff, function($s) { return $s['performanceCategory'] === 'excellent'; }));
    $goodCount = count(array_filter($staff, function($s) { return $s['performanceCategory'] === 'good'; }));
    $averageCount = count(array_filter($staff, function($s) { return $s['performanceCategory'] === 'average'; }));
    $needsAttentionCount = count(array_filter($staff, function($s) { return $s['performanceCategory'] === 'needs-attention'; }));
    
    $response = array(
        'staff' => $staff,
        'summary' => array(
            'total' => $totalStaff,
            'avgPerformance' => $avgPerformance,
            'avgEngagement' => $avgEngagement,
            'avgTenure' => $avgTenure,
            'totalProjects' => $totalProjects,
            'avgProjectsPerPerson' => $totalStaff > 0 ? round($totalProjects / $totalStaff, 1) : 0
        ),
        'performanceDistribution' => array(
            'excellent' => $excellentCount,
            'good' => $goodCount,
            'average' => $averageCount,
            'needsAttention' => $needsAttentionCount
        )
    );
    
    // Add team insights
    $insights = array();
    
    $highPerformers = array_filter($staff, function($s) { return $s['performanceScore'] >= 90; });
    if (!empty($highPerformers)) {
        $insights[] = array(
            'type' => 'success',
            'title' => 'Top Performers',
            'message' => count($highPerformers) . ' team members are performing at excellent levels',
            'staff' => array_column($highPerformers, 'name')
        );
    }
    
    $lowEngagement = array_filter($staff, function($s) { return $s['engagementScore'] < 60; });
    if (!empty($lowEngagement)) {
        $insights[] = array(
            'type' => 'warning',
            'title' => 'Engagement Concerns',
            'message' => count($lowEngagement) . ' team members show low engagement levels',
            'staff' => array_column($lowEngagement, 'name')
        );
    }
    
    $overloaded = array_filter($staff, function($s) { return $s['workload'] > 80; });
    if (!empty($overloaded)) {
        $insights[] = array(
            'type' => 'info',
            'title' => 'Workload Management',
            'message' => count($overloaded) . ' team members may be overloaded',
            'staff' => array_column($overloaded, 'name')
        );
    }
    
    $response['insights'] = $insights;
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching staff list: ' . $e->getMessage();
}

// Helper function to get staff skills based on role and experience
function getStaffSkills($role, $projectCount, $tenure) {
    $baseSkills = array(
        'Communication' => min(100, 60 + ($tenure * 5)),
        'Teamwork' => min(100, 70 + ($projectCount * 3)),
        'Problem Solving' => min(100, 50 + ($tenure * 8))
    );
    
    // Add role-specific skills
    switch (strtolower($role)) {
        case 'manager':
        case 'senior manager':
            $baseSkills['Leadership'] = min(100, 70 + ($tenure * 10));
            $baseSkills['Strategic Thinking'] = min(100, 60 + ($tenure * 8));
            break;
        case 'developer':
        case 'senior developer':
            $baseSkills['Technical Skills'] = min(100, 80 + ($tenure * 5));
            $baseSkills['Code Quality'] = min(100, 70 + ($tenure * 6));
            break;
        case 'analyst':
            $baseSkills['Data Analysis'] = min(100, 75 + ($tenure * 7));
            $baseSkills['Research'] = min(100, 80 + ($tenure * 4));
            break;
        default:
            $baseSkills['Domain Knowledge'] = min(100, 60 + ($tenure * 6));
    }
    
    return $baseSkills;
}

// Helper function to get next development goals
function getNextDevelopmentGoals($role, $skills, $performanceScore) {
    $goals = array();
    
    // Find skills that need improvement
    foreach ($skills as $skill => $level) {
        if ($level < 80) {
            $goals[] = "Improve $skill (currently $level%)";
        }
    }
    
    // Add role-specific goals
    if ($performanceScore < 75) {
        $goals[] = "Focus on core performance improvement";
    }
    
    if (stripos($role, 'senior') === false && $performanceScore >= 85) {
        $goals[] = "Consider advancement to senior role";
    }
    
    if (empty($goals)) {
        $goals[] = "Maintain excellent performance";
        $goals[] = "Mentor junior team members";
    }
    
    return array_slice($goals, 0, 3); // Return top 3 goals
}

echo json_encode($response);
?>
