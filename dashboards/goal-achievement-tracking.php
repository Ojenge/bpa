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
    <title>Goal Achievement Tracking - <?php echo htmlspecialchars($departmentName); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="/bpa/css/myBootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="/bpa/js/chart.min.js"></script>
    <!-- Custom CSS -->
    <link href="/bpa/css/dashboardTables.css" rel="stylesheet">
    
    <style>
        .goal-card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease-in-out;
        }
        
        .goal-card:hover {
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
        
        .goal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
        
        .progress-ring {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto;
        }
        
        .progress-ring-circle {
            stroke-width: 8;
            fill: transparent;
            stroke-dasharray: 283;
            stroke-dashoffset: 283;
            stroke-linecap: round;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
            transition: stroke-dashoffset 0.5s ease-in-out;
        }
        
        .progress-ring-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .goal-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s ease;
        }
        
        .goal-item:hover {
            background-color: #f8f9fa;
        }
        
        .goal-item:last-child {
            border-bottom: none;
        }
        
        .goal-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        
        .goal-excellent { background-color: #28a745; color: white; }
        .goal-good { background-color: #17a2b8; color: white; }
        .goal-warning { background-color: #ffc107; color: white; }
        .goal-danger { background-color: #dc3545; color: white; }
        
        .milestone-timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .milestone-timeline::before {
            content: '';
            position: absolute;
            left: 0.75rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        
        .milestone-item {
            position: relative;
            padding: 1rem 0;
        }
        
        .milestone-item::before {
            content: '';
            position: absolute;
            left: -0.5rem;
            top: 1.5rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #6c757d;
        }
        
        .milestone-completed::before {
            background: #28a745;
        }
        
        .milestone-current::before {
            background: #ffc107;
        }
        
        .milestone-future::before {
            background: #dee2e6;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 1rem 0;
        }
        
        .nav-pills .nav-link.active {
            background-color: #667eea;
        }
        
        .forecast-indicator {
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }
        
        .forecast-positive { color: #28a745; }
        .forecast-negative { color: #dc3545; }
        .forecast-neutral { color: #6c757d; }
        
        .kpi-gauge {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto;
        }
        
        .objective-card {
            border-left: 4px solid #667eea;
            margin-bottom: 1rem;
        }
        
        .target-vs-actual {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0.5rem 0;
        }
        
        .target-bar {
            flex-grow: 1;
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            margin: 0 1rem;
            overflow: hidden;
        }
        
        .actual-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="goal-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-bullseye me-3"></i>
                        Goal Achievement Tracking
                    </h1>
                    <p class="mb-0 opacity-75">
                        <?php echo htmlspecialchars($departmentName); ?> - Visual progress tracking and forecasting
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-light btn-sm" onclick="refreshGoals()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="exportGoalReport()">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="setNewGoal()">
                            <i class="fas fa-plus me-1"></i> New Goal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Achievement Metrics -->
        <div class="row">
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="overallAchievement">--</div>
                    <div class="metric-label">Overall Achievement</div>
                    <div class="forecast-indicator" id="achievementForecast">
                        <i class="fas fa-chart-line"></i> On track for targets
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="goalsOnTrack">--</div>
                    <div class="metric-label">Goals On Track</div>
                    <div class="forecast-indicator" id="onTrackForecast">
                        <i class="fas fa-check-circle"></i> Meeting expectations
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="milestonesCompleted">--</div>
                    <div class="metric-label">Milestones Completed</div>
                    <div class="forecast-indicator" id="milestonesForecast">
                        <i class="fas fa-flag-checkered"></i> This period
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="forecastAccuracy">--</div>
                    <div class="metric-label">Forecast Accuracy</div>
                    <div class="forecast-indicator" id="accuracyTrend">
                        <i class="fas fa-target"></i> Prediction quality
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-pills mb-4" id="goalTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                    <i class="fas fa-chart-line me-2"></i>Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="objectives-tab" data-bs-toggle="pill" data-bs-target="#objectives" type="button" role="tab">
                    <i class="fas fa-bullseye me-2"></i>Objectives
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="kpis-tab" data-bs-toggle="pill" data-bs-target="#kpis" type="button" role="tab">
                    <i class="fas fa-tachometer-alt me-2"></i>KPIs
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="milestones-tab" data-bs-toggle="pill" data-bs-target="#milestones" type="button" role="tab">
                    <i class="fas fa-flag me-2"></i>Milestones
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="forecasting-tab" data-bs-toggle="pill" data-bs-target="#forecasting" type="button" role="tab">
                    <i class="fas fa-crystal-ball me-2"></i>Forecasting
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="goalTabContent">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="row">
                    <!-- Goal Progress Overview -->
                    <div class="col-md-8">
                        <div class="card goal-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Goal Progress Overview
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="goalProgressList">
                                    <!-- Goal progress items will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Achievement Summary -->
                    <div class="col-md-4">
                        <div class="card goal-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-trophy me-2"></i>Achievement Summary
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="progress-ring">
                                    <svg width="120" height="120">
                                        <circle class="progress-ring-circle"
                                                cx="60" cy="60" r="45"
                                                stroke="#28a745"
                                                id="achievementRing">
                                        </circle>
                                    </svg>
                                    <div class="progress-ring-text" id="achievementPercentage">--</div>
                                </div>
                                <div class="text-center mt-3">
                                    <h6>Overall Achievement</h6>
                                    <div id="achievementBreakdown">
                                        <!-- Achievement breakdown will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Progress Trends -->
                    <div class="col-md-6">
                        <div class="card goal-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Progress Trends
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="progressTrendsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Goal Categories -->
                    <div class="col-md-6">
                        <div class="card goal-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>Goal Categories
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="goalCategoriesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Objectives Tab -->
            <div class="tab-pane fade" id="objectives" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card goal-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bullseye me-2"></i>Department Objectives
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="objectivesList">
                                    <!-- Objectives will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPIs Tab -->
            <div class="tab-pane fade" id="kpis" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card goal-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-tachometer-alt me-2"></i>Key Performance Indicators
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="kpisList">
                                    <!-- KPIs will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card goal-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>KPI Performance
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="kpiPerformanceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Milestones Tab -->
            <div class="tab-pane fade" id="milestones" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card goal-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-flag me-2"></i>Milestone Timeline
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="milestone-timeline" id="milestoneTimeline">
                                    <!-- Milestone timeline will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card goal-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-check me-2"></i>Upcoming Milestones
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="upcomingMilestones">
                                    <!-- Upcoming milestones will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Forecasting Tab -->
            <div class="tab-pane fade" id="forecasting" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card goal-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Achievement Forecast
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="achievementForecastChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card goal-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Risk Assessment
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="riskAssessment">
                                    <!-- Risk assessment will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card goal-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>Recommendations
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="recommendations">
                                    <!-- Recommendations will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="/bpa/js/bootstrap.bundle.min.js"></script>

    <script>
        // Wrap in IIFE to avoid global variable conflicts
        (function() {
            // Local variables for this dashboard
            const departmentId = '<?php echo $departmentId; ?>';
            const objectPeriod = '<?php echo $objectPeriod; ?>';
            const objectDate = '<?php echo $objectDate; ?>';

            // Initialize dashboard
            document.addEventListener('DOMContentLoaded', function() {
            loadGoalData();
            initializeCharts();
        });

        // Load goal achievement data
        function loadGoalData() {
            loadAchievementMetrics();
            loadGoalProgress();
            loadObjectives();
            loadKPIs();
            loadMilestones();
            loadForecasting();
        }

        // Load achievement metrics
        function loadAchievementMetrics() {
            fetch('get-goal-achievement-metrics.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${departmentId}&objectPeriod=${objectPeriod}&objectDate=${objectDate}`
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('overallAchievement').textContent = (data.overallAchievement || '--') + '%';
                document.getElementById('goalsOnTrack').textContent = data.goalsOnTrack || '--';
                document.getElementById('milestonesCompleted').textContent = data.milestonesCompleted || '--';
                document.getElementById('forecastAccuracy').textContent = (data.forecastAccuracy || '--') + '%';

                // Update achievement ring
                updateAchievementRing(data.overallAchievement || 0);

                // Update forecasts
                updateForecastIndicators(data.forecasts);
            })
            .catch(error => {
                console.error('Error loading achievement metrics:', error);
            });
        }

        // Update achievement ring
        function updateAchievementRing(percentage) {
            const ring = document.getElementById('achievementRing');
            const text = document.getElementById('achievementPercentage');

            if (ring && text) {
                const circumference = 2 * Math.PI * 45; // radius = 45
                const offset = circumference - (percentage / 100) * circumference;

                ring.style.strokeDashoffset = offset;
                text.textContent = percentage + '%';

                // Update color based on percentage
                if (percentage >= 90) {
                    ring.setAttribute('stroke', '#28a745');
                } else if (percentage >= 75) {
                    ring.setAttribute('stroke', '#17a2b8');
                } else if (percentage >= 60) {
                    ring.setAttribute('stroke', '#ffc107');
                } else {
                    ring.setAttribute('stroke', '#dc3545');
                }
            }
        }

        // Load goal progress
        function loadGoalProgress() {
            fetch('get-goal-progress.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${departmentId}&objectPeriod=${objectPeriod}&objectDate=${objectDate}`
            })
            .then(response => response.json())
            .then(data => {
                renderGoalProgressList(data.goals);
                updateAchievementBreakdown(data.breakdown);
            })
            .catch(error => {
                console.error('Error loading goal progress:', error);
            });
        }

        // Render goal progress list
        function renderGoalProgressList(goals) {
            const container = document.getElementById('goalProgressList');
            if (!goals || goals.length === 0) {
                container.innerHTML = '<p class="text-muted">No goals data available.</p>';
                return;
            }

            let html = '';
            goals.forEach(goal => {
                const iconClass = getGoalIconClass(goal.achievement);
                const progressWidth = Math.min(goal.achievement, 100);

                html += `
                    <div class="goal-item">
                        <div class="goal-icon ${iconClass}">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">${goal.name}</h6>
                                <span class="badge bg-primary">${goal.achievement}%</span>
                            </div>
                            <div class="target-vs-actual">
                                <small class="text-muted">Target: ${goal.target}</small>
                                <div class="target-bar">
                                    <div class="actual-fill ${getProgressBarClass(goal.achievement)}"
                                         style="width: ${progressWidth}%"></div>
                                </div>
                                <small class="text-muted">Actual: ${goal.actual}</small>
                            </div>
                            <small class="text-muted">Due: ${goal.dueDate}</small>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Get goal icon class based on achievement
        function getGoalIconClass(achievement) {
            if (achievement >= 90) return 'goal-excellent';
            if (achievement >= 75) return 'goal-good';
            if (achievement >= 60) return 'goal-warning';
            return 'goal-danger';
        }

        // Get progress bar class
        function getProgressBarClass(achievement) {
            if (achievement >= 90) return 'bg-success';
            if (achievement >= 75) return 'bg-info';
            if (achievement >= 60) return 'bg-warning';
            return 'bg-danger';
        }

        // Update achievement breakdown
        function updateAchievementBreakdown(breakdown) {
            const container = document.getElementById('achievementBreakdown');
            if (breakdown) {
                container.innerHTML = `
                    <div class="row text-center">
                        <div class="col-6">
                            <small class="text-success">${breakdown.onTrack || 0}</small>
                            <br><small class="text-muted">On Track</small>
                        </div>
                        <div class="col-6">
                            <small class="text-warning">${breakdown.atRisk || 0}</small>
                            <br><small class="text-muted">At Risk</small>
                        </div>
                    </div>
                `;
            }
        }

        // Load objectives
        function loadObjectives() {
            fetch('get-department-objectives.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${departmentId}&objectPeriod=${objectPeriod}&objectDate=${objectDate}`
            })
            .then(response => response.json())
            .then(data => {
                renderObjectivesList(data.objectives);
            })
            .catch(error => {
                console.error('Error loading objectives:', error);
            });
        }

        // Render objectives list
        function renderObjectivesList(objectives) {
            const container = document.getElementById('objectivesList');
            if (!objectives || objectives.length === 0) {
                container.innerHTML = '<p class="text-muted">No objectives data available.</p>';
                return;
            }

            let html = '';
            objectives.forEach(objective => {
                html += `
                    <div class="card objective-card">
                        <div class="card-body">
                            <h6 class="card-title">${objective.name}</h6>
                            <p class="card-text">${objective.description || 'No description available'}</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Progress: ${objective.progress || 0}%</small>
                                    <div class="progress mt-1">
                                        <div class="progress-bar ${getProgressBarClass(objective.progress || 0)}"
                                             style="width: ${objective.progress || 0}%"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Due Date: ${objective.dueDate || 'Not set'}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Load KPIs
        function loadKPIs() {
            fetch('get-department-kpis.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${departmentId}&objectPeriod=${objectPeriod}&objectDate=${objectDate}`
            })
            .then(response => response.json())
            .then(data => {
                renderKPIsList(data.kpis);
                updateKPIPerformanceChart(data.performance);
            })
            .catch(error => {
                console.error('Error loading KPIs:', error);
            });
        }

        // Render KPIs list
        function renderKPIsList(kpis) {
            const container = document.getElementById('kpisList');
            if (!kpis || kpis.length === 0) {
                container.innerHTML = '<p class="text-muted">No KPIs data available.</p>';
                return;
            }

            let html = '';
            kpis.forEach(kpi => {
                const scoreClass = getScoreClass(kpi.score);

                html += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-1">${kpi.name}</h6>
                                    <small class="text-muted">${kpi.type} | ${kpi.frequency}</small>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="kpi-gauge">
                                        <div class="badge ${scoreClass} p-2">${kpi.score}</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <small class="text-muted">Target</small>
                                    <div class="fw-bold">${kpi.target}</div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <small class="text-muted">Actual</small>
                                    <div class="fw-bold">${kpi.actual}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Get score class based on performance
        function getScoreClass(score) {
            if (score >= 4) return 'bg-success';
            if (score >= 3) return 'bg-info';
            if (score >= 2) return 'bg-warning';
            return 'bg-danger';
        }

        // Load milestones
        function loadMilestones() {
            // Implementation for loading milestones
            const timelineContainer = document.getElementById('milestoneTimeline');
            const upcomingContainer = document.getElementById('upcomingMilestones');

            // Sample milestone timeline
            timelineContainer.innerHTML = `
                <div class="milestone-item milestone-completed">
                    <h6>Q1 Revenue Target</h6>
                    <small class="text-muted">Completed on March 31, 2024</small>
                    <p class="mb-0">Achieved 105% of quarterly revenue target</p>
                </div>
                <div class="milestone-item milestone-completed">
                    <h6>Staff Training Program</h6>
                    <small class="text-muted">Completed on April 15, 2024</small>
                    <p class="mb-0">100% staff completion rate</p>
                </div>
                <div class="milestone-item milestone-current">
                    <h6>Process Optimization</h6>
                    <small class="text-muted">In Progress - Due May 30, 2024</small>
                    <p class="mb-0">75% complete - workflow analysis phase</p>
                </div>
                <div class="milestone-item milestone-future">
                    <h6>Q2 Performance Review</h6>
                    <small class="text-muted">Scheduled for June 30, 2024</small>
                    <p class="mb-0">Comprehensive team performance assessment</p>
                </div>
            `;

            // Sample upcoming milestones
            upcomingContainer.innerHTML = `
                <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Process Optimization</h6>
                                <p class="mb-1 text-muted small">Workflow efficiency improvements</p>
                            </div>
                            <small class="text-warning">5 days</small>
                        </div>
                    </div>
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Q2 Review</h6>
                                <p class="mb-1 text-muted small">Performance assessment</p>
                            </div>
                            <small class="text-info">35 days</small>
                        </div>
                    </div>
                </div>
            `;
        }

        // Load forecasting
        function loadForecasting() {
            // Implementation for loading forecasting data
            const riskContainer = document.getElementById('riskAssessment');
            const recommendationsContainer = document.getElementById('recommendations');

            // Sample risk assessment
            riskContainer.innerHTML = `
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Medium Risk</h6>
                    <p class="mb-1">2 goals may miss targets without intervention</p>
                    <small>Process optimization and staff development goals</small>
                </div>
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Low Risk</h6>
                    <p class="mb-1">3 goals are on track for completion</p>
                    <small>Revenue, training, and quality targets</small>
                </div>
            `;

            // Sample recommendations
            recommendationsContainer.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="text-success">
                                    <i class="fas fa-lightbulb me-2"></i>Accelerate Progress
                                </h6>
                                <p class="card-text">Allocate additional resources to process optimization to meet deadline.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-body">
                                <h6 class="text-info">
                                    <i class="fas fa-users me-2"></i>Team Support
                                </h6>
                                <p class="card-text">Provide additional training support for staff development goals.</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Update forecast indicators
        function updateForecastIndicators(forecasts) {
            if (forecasts) {
                updateForecastElement('achievementForecast', forecasts.achievement);
                updateForecastElement('onTrackForecast', forecasts.onTrack);
                updateForecastElement('milestonesForecast', forecasts.milestones);
                updateForecastElement('accuracyTrend', forecasts.accuracy);
            }
        }

        // Update individual forecast element
        function updateForecastElement(elementId, forecast) {
            const element = document.getElementById(elementId);
            if (element && forecast) {
                let className = 'forecast-indicator forecast-neutral';

                if (forecast.trend === 'positive') {
                    className = 'forecast-indicator forecast-positive';
                } else if (forecast.trend === 'negative') {
                    className = 'forecast-indicator forecast-negative';
                }

                element.innerHTML = `<i class="${forecast.icon}"></i> ${forecast.text}`;
                element.className = className;
            }
        }

        // Initialize charts
        function initializeCharts() {
            initProgressTrendsChart();
            initGoalCategoriesChart();
            initKPIPerformanceChart();
            initAchievementForecastChart();
        }

        // Initialize progress trends chart
        function initProgressTrendsChart() {
            const ctx = document.getElementById('progressTrendsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Goal Achievement',
                        data: [65, 72, 78, 75, 82, 85],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Target Progress',
                        data: [70, 75, 80, 80, 85, 90],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderDash: [5, 5],
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

        // Initialize goal categories chart
        function initGoalCategoriesChart() {
            const ctx = document.getElementById('goalCategoriesChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Financial', 'Operational', 'Strategic', 'Development'],
                    datasets: [{
                        data: [30, 35, 20, 15],
                        backgroundColor: ['#667eea', '#28a745', '#ffc107', '#17a2b8']
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

        // Initialize KPI performance chart
        function initKPIPerformanceChart() {
            const ctx = document.getElementById('kpiPerformanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Revenue', 'Quality', 'Efficiency', 'Satisfaction'],
                    datasets: [{
                        label: 'Performance Score',
                        data: [4.2, 3.8, 4.5, 3.9],
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
                            max: 5
                        }
                    }
                }
            });
        }

        // Initialize achievement forecast chart
        function initAchievementForecastChart() {
            const ctx = document.getElementById('achievementForecastChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Current', 'Next Month', '2 Months', '3 Months'],
                    datasets: [{
                        label: 'Predicted Achievement',
                        data: [85, 88, 91, 94],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderDash: [5, 5],
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Conservative Estimate',
                        data: [85, 86, 88, 90],
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        borderDash: [10, 5],
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

        // Update KPI performance chart
        function updateKPIPerformanceChart(performance) {
            // This would be called when real data is loaded
            console.log('Updating KPI performance chart with:', performance);
        }

        // Utility functions
        function refreshGoals() {
            loadGoalData();
        }

        function exportGoalReport() {
            window.print();
        }

        function setNewGoal() {
            alert('New Goal functionality would open a goal creation form');
        }

        })(); // Close IIFE
    </script>
</body>
</html>
