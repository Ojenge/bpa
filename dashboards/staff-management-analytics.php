<?php
include_once("../config/config_mysqli.php");
include_once("../admin/models/config.php");
include_once("../functions/functions.php");
include_once("../functions/calendar-labels.php");
include_once("../functions/perspOrg-scores.php");
include_once("../reports/scores-functions.2.0.php");

date_default_timezone_set('Africa/Nairobi');

// Get parameters
@$departmentId = $_POST['departmentId'] ?? $_GET['departmentId'] ?? 'org1';
@$objectPeriod = $_POST['objectPeriod'] ?? $_GET['objectPeriod'] ?? 'months';
@$objectDate = $_POST['objectDate'] ?? $_GET['objectDate'] ?? date("Y-m");
@$staffView = $_POST['staffView'] ?? $_GET['staffView'] ?? 'overview';

// Ensure we have a valid department ID
if (empty($departmentId)) {
    $departmentId = 'org1';
}

// Get department information
$deptQuery = mysqli_query($connect, "SELECT id, name FROM organization WHERE id = '$departmentId'");
$deptInfo = mysqli_fetch_assoc($deptQuery);

if (!$deptInfo) {
    echo "<div class='alert alert-danger'>Department not found.</div>";
    exit;
}

$departmentName = $deptInfo['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management Analytics - <?php echo htmlspecialchars($departmentName); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="../css/myBootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="../js/chart.min.js"></script>
    <!-- Custom CSS -->
    <link href="../css/dashboardTables.css" rel="stylesheet">
    
    <style>
        .staff-card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease-in-out;
        }
        
        .staff-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .metric-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .metric-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .staff-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
        
        .staff-member {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s ease;
        }
        
        .staff-member:hover {
            background-color: #f8f9fa;
        }
        
        .staff-member:last-child {
            border-bottom: none;
        }
        
        .staff-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .staff-info {
            flex-grow: 1;
            margin-left: 1rem;
        }
        
        .performance-indicator {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-left: 1rem;
        }
        
        .perf-excellent { background-color: #28a745; color: white; }
        .perf-good { background-color: #17a2b8; color: white; }
        .perf-average { background-color: #ffc107; color: white; }
        .perf-poor { background-color: #dc3545; color: white; }
        
        .skill-bar {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 0.5rem 0;
            position: relative;
        }
        
        .skill-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        
        .engagement-indicator {
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }
        
        .engagement-high { color: #28a745; }
        .engagement-medium { color: #ffc107; }
        .engagement-low { color: #dc3545; }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 1rem 0;
        }
        
        .nav-pills .nav-link.active {
            background-color: #667eea;
        }
        
        .staff-selector {
            background: white;
            border-radius: 0.5rem;
            padding: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1rem;
        }
        
        .development-card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .succession-item {
            position: relative;
            padding-left: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .succession-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.5rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #667eea;
        }
        
        .succession-item::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 1.2rem;
            width: 2px;
            height: calc(100% - 0.7rem);
            background-color: #e9ecef;
        }
        
        .succession-item:last-child::after {
            display: none;
        }
        
        .succession-ready::before {
            background-color: #28a745;
        }
        
        .succession-developing::before {
            background-color: #ffc107;
        }
        
        .succession-gap::before {
            background-color: #dc3545;
        }
        
        .skill-assessment {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .skill-assessment:last-child {
            border-bottom: none;
        }
        
        .skill-name {
            font-weight: 500;
            flex-grow: 1;
        }
        
        .skill-values {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .skill-current {
            font-weight: bold;
            color: #667eea;
        }
        
        .skill-target {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .skill-gap {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }
        
        .gap-positive {
            background-color: #d4edda;
            color: #155724;
        }
        
        .gap-negative {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .gap-neutral {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .staff-profile-card {
            border-left: 4px solid #667eea;
            margin-bottom: 1rem;
        }
        
        .staff-profile-card.high-performer {
            border-left-color: #28a745;
        }
        
        .staff-profile-card.needs-attention {
            border-left-color: #ffc107;
        }
        
        .staff-profile-card.at-risk {
            border-left-color: #dc3545;
        }
        
        .development-progress {
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin: 0.5rem 0;
        }
        
        .development-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .engagement-score {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .score-high { background-color: #28a745; color: white; }
        .score-medium { background-color: #ffc107; color: white; }
        .score-low { background-color: #dc3545; color: white; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="staff-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-users me-3"></i>
                        Staff Management Analytics
                    </h1>
                    <p class="mb-0 opacity-75">
                        <?php echo htmlspecialchars($departmentName); ?> - Performance, engagement, and development insights
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-light btn-sm" onclick="refreshStaffData()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="exportStaffReport()">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="addStaffMember()">
                            <i class="fas fa-user-plus me-1"></i> Add Staff
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Controls -->
        <div class="staff-selector">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label for="departmentSelect" class="form-label">Department:</label>
                    <select class="form-select form-select-sm" id="departmentSelect" onchange="changeDepartment()">
                        <!-- Options will be loaded dynamically -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="staffViewSelect" class="form-label">View Type:</label>
                    <select class="form-select form-select-sm" id="staffViewSelect" onchange="changeStaffView()">
                        <option value="overview">Staff Overview</option>
                        <option value="performance">Performance Analysis</option>
                        <option value="engagement">Engagement Metrics</option>
                        <option value="development">Development Tracking</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="periodSelect" class="form-label">Time Period:</label>
                    <select class="form-select form-select-sm" id="periodSelect" onchange="changePeriod()">
                        <option value="current">Current Period</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                        <option value="all">All Time</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="performanceFilter" class="form-label">Performance Filter:</label>
                    <select class="form-select form-select-sm" id="performanceFilter" onchange="changePerformanceFilter()">
                        <option value="all">All Staff</option>
                        <option value="high">High Performers</option>
                        <option value="average">Average Performers</option>
                        <option value="needs-attention">Needs Attention</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Key Staff Metrics -->
        <div class="row">
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="totalStaff">--</div>
                    <div class="metric-label">Total Staff</div>
                    <div class="engagement-indicator" id="staffTrend">
                        <i class="fas fa-users"></i> Active employees
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="avgPerformance">--</div>
                    <div class="metric-label">Avg Performance</div>
                    <div class="engagement-indicator" id="performanceTrend">
                        <i class="fas fa-chart-line"></i> Performance score
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="engagementScore">--</div>
                    <div class="metric-label">Engagement Score</div>
                    <div class="engagement-indicator" id="engagementTrend">
                        <i class="fas fa-heart"></i> Team engagement
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="development-card">
                    <div class="metric-value" id="developmentRate">--</div>
                    <div class="metric-label">Development Rate</div>
                    <div class="engagement-indicator" id="developmentTrend">
                        <i class="fas fa-graduation-cap"></i> Skills growth
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-pills mb-4" id="staffTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                    <i class="fas fa-chart-pie me-2"></i>Staff Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="performance-tab" data-bs-toggle="pill" data-bs-target="#performance" type="button" role="tab">
                    <i class="fas fa-chart-bar me-2"></i>Performance Analysis
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="engagement-tab" data-bs-toggle="pill" data-bs-target="#engagement" type="button" role="tab">
                    <i class="fas fa-heart me-2"></i>Engagement Metrics
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="skills-tab" data-bs-toggle="pill" data-bs-target="#skills" type="button" role="tab">
                    <i class="fas fa-cogs me-2"></i>Skills & Development
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="succession-tab" data-bs-toggle="pill" data-bs-target="#succession" type="button" role="tab">
                    <i class="fas fa-sitemap me-2"></i>Succession Planning
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="insights-tab" data-bs-toggle="pill" data-bs-target="#insights" type="button" role="tab">
                    <i class="fas fa-lightbulb me-2"></i>HR Insights
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="staffTabContent">
            <!-- Staff Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="row">
                    <!-- Staff List -->
                    <div class="col-md-8">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users me-2"></i>Department Staff
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="staffList">
                                    <!-- Staff members will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Distribution -->
                    <div class="col-md-4">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>Performance Distribution
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="performanceDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Team Composition -->
                    <div class="col-md-6">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users-cog me-2"></i>Team Composition
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="teamCompositionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Engagement Overview -->
                    <div class="col-md-6">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-heart me-2"></i>Engagement Overview
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="engagementOverview">
                                    <!-- Engagement overview will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Analysis Tab -->
            <div class="tab-pane fade" id="performance" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Performance Trends
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="performanceTrendsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-trophy me-2"></i>Top Performers
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="topPerformers">
                                    <!-- Top performers will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Individual Performance Analysis
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="individualPerformance">
                                    <!-- Individual performance analysis will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Engagement Metrics Tab -->
            <div class="tab-pane fade" id="engagement" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-heart me-2"></i>Engagement Metrics
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="engagementMetricsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-clock me-2"></i>Activity Patterns
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="activityPatternsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-comments me-2"></i>Engagement Insights
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="engagementInsights">
                                    <!-- Engagement insights will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Skills & Development Tab -->
            <div class="tab-pane fade" id="skills" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-cogs me-2"></i>Skills Gap Analysis
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="skillsGapAnalysis">
                                    <!-- Skills gap analysis will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-graduation-cap me-2"></i>Development Progress
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="developmentProgressChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-radar me-2"></i>Team Skills Matrix
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="skillsMatrixChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Succession Planning Tab -->
            <div class="tab-pane fade" id="succession" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-sitemap me-2"></i>Succession Pipeline
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="successionPipeline">
                                    <!-- Succession pipeline will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Succession Risks
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="successionRisks">
                                    <!-- Succession risks will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users me-2"></i>Readiness Assessment
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="readinessAssessmentChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HR Insights Tab -->
            <div class="tab-pane fade" id="insights" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>HR Insights
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="hrInsights">
                                    <!-- HR insights will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bullseye me-2"></i>Action Items
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="actionItems">
                                    <!-- Action items will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card staff-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Predictive Analytics
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="predictiveAnalyticsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="../js/bootstrap.bundle.min.js"></script>

    <script>
        // Wrap in IIFE to avoid global variable conflicts
        (function() {
            // Local variables for this dashboard
            let currentDepartmentId = '<?php echo $departmentId; ?>';
            let currentStaffView = '<?php echo $staffView; ?>';
            let currentPeriod = '<?php echo $objectPeriod; ?>';
            let currentDate = '<?php echo $objectDate; ?>';
            let currentPerformanceFilter = 'all';

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadDepartmentOptions();
            loadStaffData();
            initializeCharts();
        });

        // Load department options
        function loadDepartmentOptions() {
            fetch('get-department-list.php')
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('departmentSelect');
                    select.innerHTML = '';

                    data.departments.forEach(dept => {
                        const option = document.createElement('option');
                        option.value = dept.id;
                        option.textContent = dept.name;
                        option.selected = dept.id === currentDepartmentId;
                        select.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading departments:', error);
                });
        }

        // Load staff data
        function loadStaffData() {
            loadStaffMetrics();
            loadStaffList();
            loadPerformanceAnalysis();
            loadEngagementMetrics();
            loadSkillsAnalysis();
            loadSuccessionPlanning();
            loadHRInsights();
        }

        // Load staff metrics
        function loadStaffMetrics() {
            fetch('get-staff-metrics.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${currentDepartmentId}&period=${currentPeriod}&date=${currentDate}&performanceFilter=${currentPerformanceFilter}`
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('totalStaff').textContent = data.totalStaff || '--';
                document.getElementById('avgPerformance').textContent = (data.avgPerformance || '--') + '%';
                document.getElementById('engagementScore').textContent = (data.engagementScore || '--') + '%';
                document.getElementById('developmentRate').textContent = (data.developmentRate || '--') + '%';

                // Update charts
                updatePerformanceDistributionChart(data.performanceDistribution);
                updateTeamCompositionChart(data.teamComposition);
                updateEngagementOverview(data.engagementOverview);
            })
            .catch(error => {
                console.error('Error loading staff metrics:', error);
            });
        }

        // Load staff list
        function loadStaffList() {
            fetch('get-staff-list.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${currentDepartmentId}&period=${currentPeriod}&date=${currentDate}&performanceFilter=${currentPerformanceFilter}`
            })
            .then(response => response.json())
            .then(data => {
                renderStaffList(data.staff);
            })
            .catch(error => {
                console.error('Error loading staff list:', error);
            });
        }

        // Render staff list
        function renderStaffList(staff) {
            const container = document.getElementById('staffList');
            if (!staff || staff.length === 0) {
                container.innerHTML = '<p class="text-muted">No staff members found for the selected criteria.</p>';
                return;
            }

            let html = '';
            staff.forEach(member => {
                const initials = getInitials(member.name);
                const perfClass = getPerformanceClass(member.performanceScore);
                const engagementClass = getEngagementClass(member.engagementScore);

                html += `
                    <div class="staff-member">
                        <div class="staff-avatar">
                            ${initials}
                        </div>
                        <div class="staff-info">
                            <h6 class="mb-1">${member.name}</h6>
                            <div class="skill-bar">
                                <div class="skill-fill bg-primary" style="width: ${member.performanceScore || 0}%"></div>
                            </div>
                            <small class="text-muted">
                                ${member.role} | Projects: ${member.projectCount} |
                                Last Active: ${member.lastActivity || 'Unknown'}
                            </small>
                        </div>
                        <div class="performance-indicator ${perfClass}">
                            ${Math.round(member.performanceScore || 0)}%
                        </div>
                        <div class="engagement-score ${getEngagementScoreClass(member.engagementScore)} ms-2">
                            ${Math.round(member.engagementScore || 0)}
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Initialize charts
        function initializeCharts() {
            initPerformanceDistributionChart();
            initTeamCompositionChart();
            initPerformanceTrendsChart();
            initEngagementMetricsChart();
            initActivityPatternsChart();
            initDevelopmentProgressChart();
            initSkillsMatrixChart();
            initReadinessAssessmentChart();
            initPredictiveAnalyticsChart();
        }

        // Initialize performance distribution chart
        function initPerformanceDistributionChart() {
            const ctx = document.getElementById('performanceDistributionChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Excellent (90%+)', 'Good (75-89%)', 'Average (60-74%)', 'Needs Attention (<60%)'],
                    datasets: [{
                        data: [3, 8, 5, 2],
                        backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Initialize team composition chart
        function initTeamCompositionChart() {
            const ctx = document.getElementById('teamCompositionChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Manager', 'Senior', 'Mid-level', 'Junior', 'Intern'],
                    datasets: [{
                        label: 'Staff Count',
                        data: [2, 4, 6, 5, 1],
                        backgroundColor: ['#667eea', '#28a745', '#17a2b8', '#ffc107', '#6c757d']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Initialize performance trends chart
        function initPerformanceTrendsChart() {
            const ctx = document.getElementById('performanceTrendsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Team Average',
                        data: [75, 78, 82, 80, 85, 88],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Top Performer',
                        data: [88, 90, 92, 91, 94, 95],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }

        // Initialize remaining charts
        function initEngagementMetricsChart() {
            const ctx = document.getElementById('engagementMetricsChart').getContext('2d');
            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: ['Satisfaction', 'Motivation', 'Collaboration', 'Innovation', 'Commitment'],
                    datasets: [{
                        label: 'Team Average',
                        data: [85, 78, 92, 75, 88],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.2)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }

        function initActivityPatternsChart() {
            const ctx = document.getElementById('activityPatternsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                    datasets: [{
                        label: 'Activity Level',
                        data: [85, 90, 88, 92, 78],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }

        function initDevelopmentProgressChart() {
            const ctx = document.getElementById('developmentProgressChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Technical', 'Leadership', 'Communication', 'Problem Solving'],
                    datasets: [{
                        label: 'Progress %',
                        data: [75, 60, 85, 70],
                        backgroundColor: '#667eea'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }

        function initSkillsMatrixChart() {
            const ctx = document.getElementById('skillsMatrixChart').getContext('2d');
            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: ['Technical', 'Leadership', 'Communication', 'Analytics', 'Project Mgmt', 'Innovation'],
                    datasets: [{
                        label: 'Current Level',
                        data: [75, 60, 85, 70, 80, 65],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.2)'
                    }, {
                        label: 'Target Level',
                        data: [85, 75, 90, 80, 85, 75],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }

        function initReadinessAssessmentChart() {
            const ctx = document.getElementById('readinessAssessmentChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Ready Now', 'Ready in 1 Year', 'Ready in 2+ Years', 'Not Ready'],
                    datasets: [{
                        label: 'Staff Count',
                        data: [3, 5, 7, 3],
                        backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function initPredictiveAnalyticsChart() {
            const ctx = document.getElementById('predictiveAnalyticsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Current', 'Next Quarter', '6 Months', '1 Year'],
                    datasets: [{
                        label: 'Predicted Performance',
                        data: [85, 87, 90, 92],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderDash: [5, 5],
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Engagement Forecast',
                        data: [78, 80, 82, 85],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderDash: [10, 5],
                        tension: 0.4,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }

        // Utility functions
        function getInitials(name) {
            return name.split(' ').map(n => n[0]).join('').toUpperCase();
        }

        function getPerformanceClass(score) {
            if (score >= 90) return 'perf-excellent';
            if (score >= 75) return 'perf-good';
            if (score >= 60) return 'perf-average';
            return 'perf-poor';
        }

        function getEngagementClass(score) {
            if (score >= 80) return 'engagement-high';
            if (score >= 60) return 'engagement-medium';
            return 'engagement-low';
        }

        function getEngagementScoreClass(score) {
            if (score >= 80) return 'score-high';
            if (score >= 60) return 'score-medium';
            return 'score-low';
        }

        // Update functions for charts
        function updatePerformanceDistributionChart(data) {
            console.log('Updating performance distribution chart with:', data);
        }

        function updateTeamCompositionChart(data) {
            console.log('Updating team composition chart with:', data);
        }

        function updateEngagementOverview(data) {
            const container = document.getElementById('engagementOverview');
            if (data) {
                container.innerHTML = `
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h6 class="text-success">${data.highEngagement || 0}</h6>
                            <small class="text-muted">High Engagement</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h6 class="text-warning">${data.mediumEngagement || 0}</h6>
                            <small class="text-muted">Medium Engagement</small>
                        </div>
                        <div class="col-6">
                            <h6 class="text-danger">${data.lowEngagement || 0}</h6>
                            <small class="text-muted">Low Engagement</small>
                        </div>
                        <div class="col-6">
                            <h6 class="text-info">${data.avgSatisfaction || 0}%</h6>
                            <small class="text-muted">Avg Satisfaction</small>
                        </div>
                    </div>
                `;
            }
        }

        // Event handlers
        function changeDepartment() {
            currentDepartmentId = document.getElementById('departmentSelect').value;
            loadStaffData();
        }

        function changeStaffView() {
            currentStaffView = document.getElementById('staffViewSelect').value;
            loadStaffData();
        }

        function changePeriod() {
            currentPeriod = document.getElementById('periodSelect').value;
            loadStaffData();
        }

        function changePerformanceFilter() {
            currentPerformanceFilter = document.getElementById('performanceFilter').value;
            loadStaffData();
        }

        function refreshStaffData() {
            loadStaffData();
        }

        function exportStaffReport() {
            window.print();
        }

        function addStaffMember() {
            alert('Add Staff Member functionality would open a staff creation form');
        }

        // Load remaining functions (stubs for now)
        function loadPerformanceAnalysis() {
            // Implementation for performance analysis
        }

        function loadEngagementMetrics() {
            // Implementation for engagement metrics
        }

        function loadSkillsAnalysis() {
            // Implementation for skills analysis
        }

        function loadSuccessionPlanning() {
            // Implementation for succession planning
        }

        function loadHRInsights() {
            // Implementation for HR insights
        }

        })(); // Close IIFE
    </script>
</body>
</html>
