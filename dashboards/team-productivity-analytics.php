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
    <title>Team Productivity Analytics - <?php echo htmlspecialchars($departmentName); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="../css/myBootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="../js/chart.min.js"></script>
    <!-- Custom CSS -->
    <link href="../css/dashboardTables.css" rel="stylesheet">
    
    <style>
        .productivity-card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease-in-out;
        }
        
        .productivity-card:hover {
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
        
        .productivity-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
        
        .efficiency-bar {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 0.5rem 0;
        }
        
        .efficiency-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        
        .efficiency-excellent { background: linear-gradient(90deg, #28a745, #20c997); }
        .efficiency-good { background: linear-gradient(90deg, #17a2b8, #6f42c1); }
        .efficiency-average { background: linear-gradient(90deg, #ffc107, #fd7e14); }
        .efficiency-poor { background: linear-gradient(90deg, #dc3545, #e83e8c); }
        
        .workload-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }
        
        .workload-high { background-color: #dc3545; }
        .workload-medium { background-color: #ffc107; }
        .workload-low { background-color: #28a745; }
        .workload-optimal { background-color: #17a2b8; }
        
        .productivity-trend {
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }
        
        .trend-up { color: #28a745; }
        .trend-down { color: #dc3545; }
        .trend-stable { color: #6c757d; }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 1rem 0;
        }
        
        .staff-productivity-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s ease;
        }
        
        .staff-productivity-item:hover {
            background-color: #f8f9fa;
        }
        
        .staff-productivity-item:last-child {
            border-bottom: none;
        }
        
        .staff-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 1rem;
            object-fit: cover;
        }
        
        .productivity-metrics {
            flex-grow: 1;
            margin-left: 1rem;
        }
        
        .productivity-score {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        
        .nav-pills .nav-link.active {
            background-color: #667eea;
        }
        
        .table-productivity {
            font-size: 0.9rem;
        }
        
        .badge-productivity {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }
        
        .time-period-selector {
            background: white;
            border-radius: 0.5rem;
            padding: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="productivity-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-chart-bar me-3"></i>
                        Team Productivity Analytics
                    </h1>
                    <p class="mb-0 opacity-75">
                        <?php echo htmlspecialchars($departmentName); ?> - Detailed productivity metrics and efficiency analysis
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="time-period-selector">
                        <select class="form-select form-select-sm" id="timePeriodSelect" onchange="updateTimePeriod()">
                            <option value="week">This Week</option>
                            <option value="month" selected>This Month</option>
                            <option value="quarter">This Quarter</option>
                            <option value="year">This Year</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Productivity Metrics -->
        <div class="row">
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="avgProductivity">--</div>
                    <div class="metric-label">Average Productivity</div>
                    <div class="productivity-trend" id="productivityTrend">
                        <i class="fas fa-arrow-up"></i> +5.2% from last period
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="efficiencyRatio">--</div>
                    <div class="metric-label">Efficiency Ratio</div>
                    <div class="productivity-trend" id="efficiencyTrend">
                        <i class="fas fa-chart-line"></i> Completed vs Planned
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="outputPerMember">--</div>
                    <div class="metric-label">Output per Member</div>
                    <div class="productivity-trend" id="outputTrend">
                        <i class="fas fa-tasks"></i> Tasks completed
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="workloadBalance">--</div>
                    <div class="metric-label">Workload Balance</div>
                    <div class="productivity-trend" id="workloadTrend">
                        <i class="fas fa-balance-scale"></i> Distribution score
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-pills mb-4" id="productivityTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                    <i class="fas fa-chart-line me-2"></i>Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="individual-tab" data-bs-toggle="pill" data-bs-target="#individual" type="button" role="tab">
                    <i class="fas fa-user me-2"></i>Individual Metrics
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="efficiency-tab" data-bs-toggle="pill" data-bs-target="#efficiency" type="button" role="tab">
                    <i class="fas fa-tachometer-alt me-2"></i>Efficiency Analysis
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="workload-tab" data-bs-toggle="pill" data-bs-target="#workload" type="button" role="tab">
                    <i class="fas fa-balance-scale me-2"></i>Workload Distribution
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="trends-tab" data-bs-toggle="pill" data-bs-target="#trends" type="button" role="tab">
                    <i class="fas fa-chart-area me-2"></i>Trends
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="productivityTabContent">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="row">
                    <!-- Team Productivity Overview -->
                    <div class="col-md-8">
                        <div class="card productivity-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users me-2"></i>Team Productivity Overview
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="teamProductivityList">
                                    <!-- Team productivity items will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Productivity Distribution -->
                    <div class="col-md-4">
                        <div class="card productivity-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>Productivity Distribution
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="productivityDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Productivity Trends -->
                    <div class="col-md-12">
                        <div class="card productivity-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Productivity Trends
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="productivityTrendsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Individual Metrics Tab -->
            <div class="tab-pane fade" id="individual" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card productivity-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-table me-2"></i>Individual Productivity Metrics
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-productivity table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Staff Member</th>
                                                <th>Productivity Score</th>
                                                <th>Tasks Completed</th>
                                                <th>Avg Completion Time</th>
                                                <th>Efficiency Ratio</th>
                                                <th>Workload Status</th>
                                                <th>Trend</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="individualMetricsTable">
                                            <!-- Table content will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Efficiency Analysis Tab -->
            <div class="tab-pane fade" id="efficiency" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card productivity-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-clock me-2"></i>Time Efficiency Analysis
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="timeEfficiencyChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card productivity-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-target me-2"></i>Goal Achievement Efficiency
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="goalEfficiencyChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card productivity-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Efficiency Benchmarks
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="efficiencyBenchmarks">
                                    <!-- Efficiency benchmarks will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workload Distribution Tab -->
            <div class="tab-pane fade" id="workload" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card productivity-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-tasks me-2"></i>Current Workload Distribution
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="workloadDistribution">
                                    <!-- Workload distribution will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card productivity-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Workload Alerts
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="workloadAlerts">
                                    <!-- Workload alerts will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card productivity-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>Task Distribution
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="taskDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card productivity-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-balance-scale me-2"></i>Workload Balance Score
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="workloadBalanceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trends Tab -->
            <div class="tab-pane fade" id="trends" role="tabpanel">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card productivity-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-area me-2"></i>Historical Productivity Trends
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="historicalTrendsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card productivity-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Performance Forecasting
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="forecastingChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card productivity-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>Productivity Insights
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="productivityInsights">
                                    <!-- Productivity insights will be loaded here -->
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
            const departmentId = '<?php echo $departmentId; ?>';
            const objectPeriod = '<?php echo $objectPeriod; ?>';
            let objectDate = '<?php echo $objectDate; ?>';
            let currentTimePeriod = 'month';

            // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadProductivityData();
            initializeCharts();
        });

        // Update time period
        function updateTimePeriod() {
            currentTimePeriod = document.getElementById('timePeriodSelect').value;
            loadProductivityData();
            updateCharts();
        }

        // Load productivity data
        function loadProductivityData() {
            loadProductivityMetrics();
            loadTeamProductivity();
            loadIndividualMetrics();
            loadEfficiencyData();
            loadWorkloadData();
            loadTrendsData();
        }

        // Load productivity metrics
        function loadProductivityMetrics() {
            fetch('get-productivity-metrics.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${departmentId}&objectPeriod=${objectPeriod}&objectDate=${objectDate}&timePeriod=${currentTimePeriod}`
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('avgProductivity').textContent = (data.avgProductivity || '--') + '%';
                document.getElementById('efficiencyRatio').textContent = (data.efficiencyRatio || '--') + '%';
                document.getElementById('outputPerMember').textContent = data.outputPerMember || '--';
                document.getElementById('workloadBalance').textContent = (data.workloadBalance || '--') + '%';

                // Update trends
                updateProductivityTrends(data.trends);
            })
            .catch(error => {
                console.error('Error loading productivity metrics:', error);
            });
        }

        // Load team productivity
        function loadTeamProductivity() {
            fetch('get-team-productivity-detailed.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${departmentId}&objectPeriod=${objectPeriod}&objectDate=${objectDate}&timePeriod=${currentTimePeriod}`
            })
            .then(response => response.json())
            .then(data => {
                renderTeamProductivityList(data.teamList);
                updateProductivityDistributionChart(data.distribution);
            })
            .catch(error => {
                console.error('Error loading team productivity:', error);
            });
        }

        // Render team productivity list
        function renderTeamProductivityList(teamList) {
            const container = document.getElementById('teamProductivityList');
            if (!teamList || teamList.length === 0) {
                container.innerHTML = '<p class="text-muted">No team productivity data available.</p>';
                return;
            }

            let html = '';
            teamList.forEach(member => {
                const efficiencyClass = getEfficiencyClass(member.efficiency);
                const workloadClass = getWorkloadClass(member.workloadStatus);
                const trendIcon = getTrendIcon(member.trend);

                html += `
                    <div class="staff-productivity-item">
                        <img src="${member.photo || '../images/profilePics/default.png'}"
                             alt="${member.name}" class="staff-avatar">
                        <div>
                            <div class="fw-bold">${member.name}</div>
                            <small class="text-muted">${member.title}</small>
                        </div>
                        <div class="productivity-metrics">
                            <div class="productivity-score text-primary">${member.productivityScore}%</div>
                            <div class="efficiency-bar">
                                <div class="efficiency-fill ${efficiencyClass}"
                                     style="width: ${member.efficiency}%"></div>
                            </div>
                            <small class="text-muted">Efficiency: ${member.efficiency}%</small>
                        </div>
                        <div class="text-end">
                            <div class="d-flex align-items-center mb-1">
                                <span class="workload-indicator ${workloadClass}"></span>
                                <small>${member.workloadStatus}</small>
                            </div>
                            <div class="productivity-trend">
                                ${trendIcon} ${member.tasksCompleted} tasks
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Get efficiency class based on score
        function getEfficiencyClass(efficiency) {
            if (efficiency >= 90) return 'efficiency-excellent';
            if (efficiency >= 75) return 'efficiency-good';
            if (efficiency >= 60) return 'efficiency-average';
            return 'efficiency-poor';
        }

        // Get workload class
        function getWorkloadClass(status) {
            const classes = {
                'High': 'workload-high',
                'Medium': 'workload-medium',
                'Low': 'workload-low',
                'Optimal': 'workload-optimal'
            };
            return classes[status] || 'workload-medium';
        }

        // Get trend icon
        function getTrendIcon(trend) {
            if (trend > 0) return '<i class="fas fa-arrow-up trend-up"></i>';
            if (trend < 0) return '<i class="fas fa-arrow-down trend-down"></i>';
            return '<i class="fas fa-minus trend-stable"></i>';
        }

        // Load individual metrics
        function loadIndividualMetrics() {
            fetch('get-individual-productivity-metrics.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${departmentId}&objectPeriod=${objectPeriod}&objectDate=${objectDate}&timePeriod=${currentTimePeriod}`
            })
            .then(response => response.json())
            .then(data => {
                renderIndividualMetricsTable(data.individuals);
            })
            .catch(error => {
                console.error('Error loading individual metrics:', error);
            });
        }

        // Render individual metrics table
        function renderIndividualMetricsTable(individuals) {
            const tbody = document.getElementById('individualMetricsTable');
            if (!individuals || individuals.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No individual metrics available.</td></tr>';
                return;
            }

            let html = '';
            individuals.forEach(individual => {
                const trendIcon = getTrendIcon(individual.trend);
                const workloadBadge = getWorkloadBadge(individual.workloadStatus);
                const efficiencyBadge = getEfficiencyBadge(individual.efficiency);

                html += `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="${individual.photo || '../images/profilePics/default.png'}"
                                     alt="${individual.name}" class="staff-avatar me-2" style="width: 32px; height: 32px;">
                                <div>
                                    <div class="fw-bold">${individual.name}</div>
                                    <small class="text-muted">${individual.title}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-primary">${individual.productivityScore}%</span></td>
                        <td>${individual.tasksCompleted}</td>
                        <td>${individual.avgCompletionTime} days</td>
                        <td>${efficiencyBadge}</td>
                        <td>${workloadBadge}</td>
                        <td>${trendIcon}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewIndividualDetails('${individual.userId}')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        // Get workload badge
        function getWorkloadBadge(status) {
            const badges = {
                'High': '<span class="badge bg-danger">High</span>',
                'Medium': '<span class="badge bg-warning">Medium</span>',
                'Low': '<span class="badge bg-success">Low</span>',
                'Optimal': '<span class="badge bg-info">Optimal</span>'
            };
            return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
        }

        // Get efficiency badge
        function getEfficiencyBadge(efficiency) {
            if (efficiency >= 90) return '<span class="badge bg-success">' + efficiency + '%</span>';
            if (efficiency >= 75) return '<span class="badge bg-info">' + efficiency + '%</span>';
            if (efficiency >= 60) return '<span class="badge bg-warning">' + efficiency + '%</span>';
            return '<span class="badge bg-danger">' + efficiency + '%</span>';
        }

        // View individual details
        function viewIndividualDetails(userId) {
            window.open(`individual-productivity-detail.php?userId=${userId}&departmentId=${departmentId}`, '_blank');
        }

        // Load efficiency data
        function loadEfficiencyData() {
            // Implementation for loading efficiency analysis data
            const benchmarksContainer = document.getElementById('efficiencyBenchmarks');
            benchmarksContainer.innerHTML = `
                <div class="row">
                    <div class="col-md-4 text-center">
                        <h4 class="text-success">85%</h4>
                        <small class="text-muted">Time Efficiency</small>
                    </div>
                    <div class="col-md-4 text-center">
                        <h4 class="text-info">92%</h4>
                        <small class="text-muted">Goal Achievement</small>
                    </div>
                    <div class="col-md-4 text-center">
                        <h4 class="text-warning">78%</h4>
                        <small class="text-muted">Resource Utilization</small>
                    </div>
                </div>
            `;
        }

        // Load workload data
        function loadWorkloadData() {
            // Implementation for loading workload distribution data
            const workloadContainer = document.getElementById('workloadDistribution');
            workloadContainer.innerHTML = '<p class="text-muted">Loading workload distribution...</p>';

            const alertsContainer = document.getElementById('workloadAlerts');
            alertsContainer.innerHTML = `
                <div class="alert alert-warning alert-sm">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>3 staff members</strong> are overloaded
                </div>
                <div class="alert alert-info alert-sm">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>2 staff members</strong> have capacity for more work
                </div>
            `;
        }

        // Load trends data
        function loadTrendsData() {
            // Implementation for loading trends and insights
            const insightsContainer = document.getElementById('productivityInsights');
            insightsContainer.innerHTML = `
                <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 text-success">
                                    <i class="fas fa-arrow-up me-1"></i>Productivity Increase
                                </h6>
                                <p class="mb-1 text-muted small">Team productivity increased by 12% this month</p>
                            </div>
                            <small class="text-muted">This month</small>
                        </div>
                    </div>
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Workload Imbalance
                                </h6>
                                <p class="mb-1 text-muted small">Consider redistributing tasks for optimal efficiency</p>
                            </div>
                            <small class="text-muted">Current</small>
                        </div>
                    </div>
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 text-info">
                                    <i class="fas fa-lightbulb me-1"></i>Efficiency Opportunity
                                </h6>
                                <p class="mb-1 text-muted small">Automation could improve task completion by 15%</p>
                            </div>
                            <small class="text-muted">Recommendation</small>
                        </div>
                    </div>
                </div>
            `;
        }

        // Update productivity trends
        function updateProductivityTrends(trends) {
            if (trends) {
                updateTrendElement('productivityTrend', trends.productivity);
                updateTrendElement('efficiencyTrend', trends.efficiency);
                updateTrendElement('outputTrend', trends.output);
                updateTrendElement('workloadTrend', trends.workload);
            }
        }

        // Update individual trend element
        function updateTrendElement(elementId, trend) {
            const element = document.getElementById(elementId);
            if (element && trend) {
                let icon = 'fas fa-minus';
                let className = 'productivity-trend trend-stable';

                if (trend.direction === 'up') {
                    icon = 'fas fa-arrow-up';
                    className = 'productivity-trend trend-up';
                } else if (trend.direction === 'down') {
                    icon = 'fas fa-arrow-down';
                    className = 'productivity-trend trend-down';
                }

                element.innerHTML = `<i class="${icon}"></i> ${trend.text}`;
                element.className = className;
            }
        }

        // Initialize charts
        function initializeCharts() {
            initProductivityDistributionChart();
            initProductivityTrendsChart();
            initTimeEfficiencyChart();
            initGoalEfficiencyChart();
            initTaskDistributionChart();
            initWorkloadBalanceChart();
            initHistoricalTrendsChart();
            initForecastingChart();
        }

        // Initialize productivity distribution chart
        function initProductivityDistributionChart() {
            const ctx = document.getElementById('productivityDistributionChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['High Performers', 'Good Performers', 'Average Performers', 'Needs Improvement'],
                    datasets: [{
                        data: [25, 45, 25, 5],
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

        // Initialize productivity trends chart
        function initProductivityTrendsChart() {
            const ctx = document.getElementById('productivityTrendsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [{
                        label: 'Team Productivity',
                        data: [75, 78, 82, 85],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Efficiency Ratio',
                        data: [70, 73, 76, 80],
                        borderColor: '#764ba2',
                        backgroundColor: 'rgba(118, 75, 162, 0.1)',
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

        // Initialize time efficiency chart
        function initTimeEfficiencyChart() {
            const ctx = document.getElementById('timeEfficiencyChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Planning', 'Execution', 'Review', 'Communication'],
                    datasets: [{
                        label: 'Time Spent (%)',
                        data: [20, 50, 15, 15],
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

        // Initialize goal efficiency chart
        function initGoalEfficiencyChart() {
            const ctx = document.getElementById('goalEfficiencyChart').getContext('2d');
            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: ['Quality', 'Speed', 'Innovation', 'Collaboration', 'Results'],
                    datasets: [{
                        label: 'Current Performance',
                        data: [85, 78, 92, 88, 82],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.2)'
                    }, {
                        label: 'Target Performance',
                        data: [90, 85, 95, 90, 88],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.2)'
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

        // Initialize task distribution chart
        function initTaskDistributionChart() {
            const ctx = document.getElementById('taskDistributionChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['High Priority', 'Medium Priority', 'Low Priority', 'Maintenance'],
                    datasets: [{
                        data: [35, 40, 15, 10],
                        backgroundColor: ['#dc3545', '#ffc107', '#28a745', '#6c757d']
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

        // Initialize workload balance chart
        function initWorkloadBalanceChart() {
            const ctx = document.getElementById('workloadBalanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Overloaded', 'Optimal', 'Underutilized'],
                    datasets: [{
                        label: 'Staff Count',
                        data: [3, 8, 2],
                        backgroundColor: ['#dc3545', '#28a745', '#ffc107']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Initialize historical trends chart
        function initHistoricalTrendsChart() {
            const ctx = document.getElementById('historicalTrendsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Productivity Score',
                        data: [72, 75, 78, 76, 82, 85],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Efficiency Ratio',
                        data: [68, 71, 74, 72, 78, 81],
                        borderColor: '#764ba2',
                        backgroundColor: 'rgba(118, 75, 162, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Output per Member',
                        data: [15, 16, 18, 17, 19, 21],
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
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Initialize forecasting chart
        function initForecastingChart() {
            const ctx = document.getElementById('forecastingChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Current', 'Next Month', '2 Months', '3 Months'],
                    datasets: [{
                        label: 'Predicted Productivity',
                        data: [85, 87, 89, 91],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderDash: [5, 5],
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Conservative Estimate',
                        data: [85, 84, 86, 87],
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

        // Update charts when time period changes
        function updateCharts() {
            // This would update all charts with new data based on the selected time period
            console.log('Updating charts for time period:', currentTimePeriod);
        }

        // Update productivity distribution chart
        function updateProductivityDistributionChart(distribution) {
            // This would be called when real data is loaded
            console.log('Updating productivity distribution chart with:', distribution);
        }

        })(); // Close IIFE
    </script>
</body>
</html>
