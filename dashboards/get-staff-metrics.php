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
    // Get department staff
    $staffQuery = "
        SELECT 
            u.user_id,
            u.display_name,
            u.email,
            u.role,
            u.active,
            u.last_login,
            u.date_added,
            COUNT(DISTINCT i.id) as projectCount,
            AVG(ist.percentageCompletion) as avgProjectCompletion
        FROM uc_users u
        LEFT JOIN initiative i ON u.user_id = i.projectManager
        LEFT JOIN initiative_status ist ON i.id = ist.initiativeId
        WHERE u.department = '$departmentId'
        AND u.active = 1
        GROUP BY u.user_id, u.display_name, u.email, u.role, u.active, u.last_login, u.date_added
        ORDER BY u.display_name
    ";
    
    $staffResult = mysqli_query($connect, $staffQuery);
    
    $totalStaff = 0;
    $totalPerformance = 0;
    $totalEngagement = 0;
    $totalDevelopment = 0;
    
    $performanceDistribution = array(
        'excellent' => 0,
        'good' => 0,
        'average' => 0,
        'needs_attention' => 0
    );
    
    $teamComposition = array();
    $roleCount = array();
    
    while ($staff = mysqli_fetch_assoc($staffResult)) {
        $totalStaff++;
        
        // Calculate performance score (based on project completion and activity)
        $projectCompletion = $staff['avgProjectCompletion'] ?? 0;
        $projectCount = $staff['projectCount'] ?? 0;
        $lastLogin = $staff['last_login'];
        
        // Performance calculation (simplified)
        $performanceScore = 0;
        if ($projectCount > 0) {
            $performanceScore = $projectCompletion;
        } else {
            $performanceScore = 50; // Base score for staff without projects
        }
        
        // Adjust based on activity (last login)
        if ($lastLogin) {
            $daysSinceLogin = (time() - strtotime($lastLogin)) / (60 * 60 * 24);
            if ($daysSinceLogin <= 7) {
                $performanceScore += 10; // Bonus for recent activity
            } elseif ($daysSinceLogin > 30) {
                $performanceScore -= 10; // Penalty for inactivity
            }
        }
        
        $performanceScore = max(0, min(100, $performanceScore)); // Clamp to 0-100
        $totalPerformance += $performanceScore;
        
        // Calculate engagement score (simplified based on activity and project involvement)
        $engagementScore = 50; // Base engagement
        if ($projectCount > 0) {
            $engagementScore += min(30, $projectCount * 10); // Up to 30 points for projects
        }
        if ($lastLogin && $daysSinceLogin <= 7) {
            $engagementScore += 20; // Recent activity bonus
        }
        $engagementScore = max(0, min(100, $engagementScore));
        $totalEngagement += $engagementScore;
        
        // Calculate development rate (simplified based on tenure and role progression)
        $tenure = (time() - strtotime($staff['date_added'])) / (60 * 60 * 24 * 365); // Years
        $developmentScore = min(100, 60 + ($tenure * 10)); // Base + tenure bonus
        $totalDevelopment += $developmentScore;
        
        // Categorize performance
        if ($performanceScore >= 90) {
            $performanceDistribution['excellent']++;
        } elseif ($performanceScore >= 75) {
            $performanceDistribution['good']++;
        } elseif ($performanceScore >= 60) {
            $performanceDistribution['average']++;
        } else {
            $performanceDistribution['needs_attention']++;
        }
        
        // Count roles
        $role = $staff['role'] ?? 'General';
        if (!isset($roleCount[$role])) {
            $roleCount[$role] = 0;
        }
        $roleCount[$role]++;
    }
    
    // Calculate averages
    $avgPerformance = $totalStaff > 0 ? round($totalPerformance / $totalStaff, 1) : 0;
    $avgEngagement = $totalStaff > 0 ? round($totalEngagement / $totalStaff, 1) : 0;
    $avgDevelopment = $totalStaff > 0 ? round($totalDevelopment / $totalStaff, 1) : 0;
    
    // Build team composition from role count
    foreach ($roleCount as $role => $count) {
        $teamComposition[] = array(
            'role' => $role,
            'count' => $count,
            'percentage' => $totalStaff > 0 ? round(($count / $totalStaff) * 100, 1) : 0
        );
    }
    
    // Get engagement overview
    $highEngagement = 0;
    $mediumEngagement = 0;
    $lowEngagement = 0;
    
    // Re-query to categorize engagement
    $staffResult = mysqli_query($connect, $staffQuery);
    while ($staff = mysqli_fetch_assoc($staffResult)) {
        $projectCount = $staff['projectCount'] ?? 0;
        $lastLogin = $staff['last_login'];
        $daysSinceLogin = $lastLogin ? (time() - strtotime($lastLogin)) / (60 * 60 * 24) : 999;
        
        $engagementScore = 50;
        if ($projectCount > 0) {
            $engagementScore += min(30, $projectCount * 10);
        }
        if ($daysSinceLogin <= 7) {
            $engagementScore += 20;
        }
        $engagementScore = max(0, min(100, $engagementScore));
        
        if ($engagementScore >= 80) {
            $highEngagement++;
        } elseif ($engagementScore >= 60) {
            $mediumEngagement++;
        } else {
            $lowEngagement++;
        }
    }
    
    // Calculate satisfaction (simplified)
    $avgSatisfaction = ($avgPerformance + $avgEngagement) / 2;
    
    $engagementOverview = array(
        'highEngagement' => $highEngagement,
        'mediumEngagement' => $mediumEngagement,
        'lowEngagement' => $lowEngagement,
        'avgSatisfaction' => round($avgSatisfaction, 1)
    );
    
    // Get historical trends (last 6 months)
    $performanceTrends = array();
    for ($i = 5; $i >= 0; $i--) {
        $monthDate = date('Y-m', strtotime("-$i months"));
        
        // Simplified trend calculation based on project completions
        $trendQuery = mysqli_query($connect, "
            SELECT 
                AVG(ist.percentageCompletion) as avgCompletion,
                COUNT(DISTINCT u.user_id) as activeUsers
            FROM uc_users u
            LEFT JOIN initiative i ON u.user_id = i.projectManager
            LEFT JOIN initiative_status ist ON i.id = ist.initiativeId
            WHERE u.department = '$departmentId'
            AND u.active = 1
            AND DATE_FORMAT(ist.updatedOn, '%Y-%m') = '$monthDate'
        ");
        
        $trendResult = mysqli_fetch_assoc($trendQuery);
        $performanceTrends[] = array(
            'month' => date('M', strtotime($monthDate . '-01')),
            'performance' => round($trendResult['avgCompletion'] ?? 0, 1),
            'activeUsers' => $trendResult['activeUsers'] ?? 0
        );
    }
    
    // Build response
    $response = array(
        'totalStaff' => $totalStaff,
        'avgPerformance' => $avgPerformance,
        'engagementScore' => $avgEngagement,
        'developmentRate' => $avgDevelopment,
        'performanceDistribution' => $performanceDistribution,
        'teamComposition' => $teamComposition,
        'engagementOverview' => $engagementOverview,
        'performanceTrends' => $performanceTrends
    );
    
    // Add additional metrics
    $response['additionalMetrics'] = array(
        'activeStaff' => $totalStaff, // All queried staff are active
        'projectManagers' => count(array_filter($teamComposition, function($role) {
            return stripos($role['role'], 'manager') !== false;
        })),
        'avgTenure' => calculateAverageTenure($departmentId, $connect),
        'retentionRate' => calculateRetentionRate($departmentId, $connect),
        'skillDiversity' => count($teamComposition)
    );
    
    // Add performance insights
    $insights = array();
    
    if ($avgPerformance >= 85) {
        $insights[] = array(
            'type' => 'success',
            'title' => 'High Performance Team',
            'message' => "Team average performance of {$avgPerformance}% indicates excellent productivity"
        );
    } elseif ($avgPerformance < 60) {
        $insights[] = array(
            'type' => 'warning',
            'title' => 'Performance Improvement Needed',
            'message' => "Team average performance of {$avgPerformance}% requires attention and support"
        );
    }
    
    if ($avgEngagement >= 80) {
        $insights[] = array(
            'type' => 'success',
            'title' => 'High Engagement',
            'message' => "Team engagement score of {$avgEngagement}% shows strong team morale"
        );
    } elseif ($avgEngagement < 60) {
        $insights[] = array(
            'type' => 'warning',
            'title' => 'Engagement Concerns',
            'message' => "Team engagement score of {$avgEngagement}% may indicate morale issues"
        );
    }
    
    if ($performanceDistribution['needs_attention'] > 0) {
        $insights[] = array(
            'type' => 'info',
            'title' => 'Development Opportunities',
            'message' => "{$performanceDistribution['needs_attention']} team members could benefit from additional support"
        );
    }
    
    $response['insights'] = $insights;
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching staff metrics: ' . $e->getMessage();
}

// Helper function to calculate average tenure
function calculateAverageTenure($departmentId, $connect) {
    $query = mysqli_query($connect, "
        SELECT AVG(DATEDIFF(NOW(), date_added) / 365) as avgTenure
        FROM uc_users 
        WHERE department = '$departmentId' 
        AND active = 1
    ");
    $result = mysqli_fetch_assoc($query);
    return round($result['avgTenure'] ?? 0, 1);
}

// Helper function to calculate retention rate
function calculateRetentionRate($departmentId, $connect) {
    $totalQuery = mysqli_query($connect, "
        SELECT COUNT(*) as total
        FROM uc_users 
        WHERE department = '$departmentId'
        AND date_added <= DATE_SUB(NOW(), INTERVAL 1 YEAR)
    ");
    $totalResult = mysqli_fetch_assoc($totalQuery);
    $total = $totalResult['total'] ?? 0;
    
    $activeQuery = mysqli_query($connect, "
        SELECT COUNT(*) as active
        FROM uc_users 
        WHERE department = '$departmentId'
        AND date_added <= DATE_SUB(NOW(), INTERVAL 1 YEAR)
        AND active = 1
    ");
    $activeResult = mysqli_fetch_assoc($activeQuery);
    $active = $activeResult['active'] ?? 0;
    
    return $total > 0 ? round(($active / $total) * 100, 1) : 100;
}

echo json_encode($response);
?>
