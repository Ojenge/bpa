<?php
/**
 * Dashboard Router
 * Handles routing and navigation between different dashboard types
 */

include_once("../config/config_mysqli.php");
include_once("../admin/models/config.php");

// Check if user is logged in
if (!isset($loggedInUser)) {
    header("Location: ../index.php");
    exit;
}

// Get dashboard type from request
$dashboardType = $_GET['type'] ?? $_POST['type'] ?? 'overview';
$departmentId = $_GET['departmentId'] ?? $_POST['departmentId'] ?? null;

// Dashboard configuration
$dashboards = [
    'overview' => [
        'title' => 'Analytics Overview',
        'file' => 'index.php',
        'description' => 'Main dashboard selection and overview'
    ],
    'department' => [
        'title' => 'Department Performance',
        'file' => 'department-performance-dashboard.php',
        'description' => 'Department analytics and performance metrics'
    ],
    'executive' => [
        'title' => 'Executive Summary',
        'file' => 'executive-summary.php',
        'description' => 'High-level organizational overview'
    ],
    'individual' => [
        'title' => 'Individual Performance',
        'file' => 'indDashboard.php',
        'description' => 'Personal performance dashboard'
    ],
    'team-productivity' => [
        'title' => 'Team Productivity',
        'file' => 'team-productivity-analytics.php',
        'description' => 'Team productivity analytics and insights'
    ],
    'goal-tracking' => [
        'title' => 'Goal Achievement',
        'file' => 'goal-achievement-tracking.php',
        'description' => 'Goal tracking and achievement metrics'
    ],
    'performance-heatmaps' => [
        'title' => 'Performance Heat Maps',
        'file' => 'performance-heat-maps.php',
        'description' => 'Visual performance heat maps'
    ],
    'staff-management' => [
        'title' => 'Staff Management',
        'file' => 'staff-management-analytics.php',
        'description' => 'Staff management and analytics'
    ],
    'predictive' => [
        'title' => 'Predictive Analytics',
        'file' => 'predictive-trend-analytics.php',
        'description' => 'Predictive analytics and trend analysis'
    ],
    'initiatives' => [
        'title' => 'Initiative Analytics',
        'file' => 'initiative-project-analytics.php',
        'description' => 'Initiative and project analytics'
    ],
    'comparative' => [
        'title' => 'Comparative Analysis',
        'file' => 'comparative-department-analysis.php',
        'description' => 'Comparative department analysis'
    ]
];

// Validate dashboard type
if (!isset($dashboards[$dashboardType])) {
    $dashboardType = 'overview';
}

$dashboard = $dashboards[$dashboardType];

// Build query parameters for dashboard
$queryParams = [];
if ($departmentId) {
    $queryParams['departmentId'] = $departmentId;
}
if (isset($_GET['period'])) {
    $queryParams['period'] = $_GET['period'];
}
if (isset($_GET['date'])) {
    $queryParams['date'] = $_GET['date'];
}

$queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';

// Check if file exists
$dashboardFile = __DIR__ . '/' . $dashboard['file'];
if (!file_exists($dashboardFile)) {
    echo "<div class='alert alert-danger'>Dashboard file not found: " . htmlspecialchars($dashboard['file']) . "</div>";
    exit;
}

// Set headers for proper content type
header('Content-Type: text/html; charset=UTF-8');

// Include the dashboard file
$_GET = array_merge($_GET, $queryParams);
$_REQUEST = array_merge($_REQUEST, $queryParams);

include $dashboardFile;
?>
