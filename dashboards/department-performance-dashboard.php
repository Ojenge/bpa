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
$deptQuery = mysqli_query($connect, "SELECT id, name, mission, vision FROM organization WHERE id = '$departmentId'");
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
    <title>Department Performance Dashboard - <?php echo htmlspecialchars($departmentName); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="/bpa/css/myBootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="/bpa/js/chart.min.js"></script>
    <!-- Custom CSS -->
    <link href="/bpa/css/dashboardTables.css" rel="stylesheet">
    
    <style>
        .dashboard-card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
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
        
        .trend-indicator {
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }
        
        .trend-up { color: #28a745; }
        .trend-down { color: #dc3545; }
        .trend-neutral { color: #6c757d; }
        
        .performance-gauge {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 0 auto;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 1rem 0;
        }
        
        .staff-performance-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .staff-performance-item:last-child {
            border-bottom: none;
        }
        
        .staff-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 1rem;
            object-fit: cover;
        }
        
        .performance-bar {
            flex-grow: 1;
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            margin: 0 1rem;
            overflow: hidden;
        }
        
        .performance-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .performance-excellent { background-color: #28a745; }
        .performance-good { background-color: #17a2b8; }
        .performance-average { background-color: #ffc107; }
        .performance-poor { background-color: #dc3545; }
        
        .department-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
        
        .nav-pills .nav-link.active {
            background-color: #667eea;
        }
        
        .table-performance {
            font-size: 0.9rem;
        }
        
        .badge-performance {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Department Header -->
        <div class="department-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-building me-3"></i>
                        <?php echo htmlspecialchars($departmentName); ?> Performance Dashboard
                    </h1>
                    <p class="mb-0 opacity-75">
                        Comprehensive performance analytics and insights for department management
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-light btn-sm" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="exportReport()">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-pills mb-4" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                    <i class="fas fa-chart-line me-2"></i>Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="team-tab" data-bs-toggle="pill" data-bs-target="#team" type="button" role="tab">
                    <i class="fas fa-users me-2"></i>Team Performance
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="goals-tab" data-bs-toggle="pill" data-bs-target="#goals" type="button" role="tab">
                    <i class="fas fa-bullseye me-2"></i>Goals & KPIs
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="resources-tab" data-bs-toggle="pill" data-bs-target="#resources" type="button" role="tab">
                    <i class="fas fa-chart-pie me-2"></i>Resources
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="initiatives-tab" data-bs-toggle="pill" data-bs-target="#initiatives" type="button" role="tab">
                    <i class="fas fa-project-diagram me-2"></i>Initiatives
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="dashboardTabContent">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="row">
                    <!-- Key Metrics Cards -->
                    <div class="col-md-3">
                        <div class="metric-card">
                            <div class="metric-value" id="overallScore">--</div>
                            <div class="metric-label">Overall Department Score</div>
                            <div class="trend-indicator" id="scoreTrend">
                                <i class="fas fa-arrow-up"></i> +2.3% from last period
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-card">
                            <div class="metric-value" id="teamSize">--</div>
                            <div class="metric-label">Team Members</div>
                            <div class="trend-indicator" id="teamTrend">
                                <i class="fas fa-users"></i> Active staff count
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-card">
                            <div class="metric-value" id="activeInitiatives">--</div>
                            <div class="metric-label">Active Initiatives</div>
                            <div class="trend-indicator" id="initiativesTrend">
                                <i class="fas fa-project-diagram"></i> In progress
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-card">
                            <div class="metric-value" id="completionRate">--</div>
                            <div class="metric-label">Completion Rate</div>
                            <div class="trend-indicator" id="completionTrend">
                                <i class="fas fa-check-circle"></i> This month
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Performance Trend Chart -->
                    <div class="col-md-8">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Performance Trend
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="performanceTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Department Ranking -->
                    <div class="col-md-4">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-trophy me-2"></i>Department Ranking
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="departmentRanking">
                                    <!-- Ranking content will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Quick Stats -->
                    <div class="col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-tachometer-alt me-2"></i>Quick Statistics
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="border-end">
                                            <h4 class="text-primary" id="avgPerformance">--</h4>
                                            <small class="text-muted">Avg Performance</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="border-end">
                                            <h4 class="text-success" id="topPerformers">--</h4>
                                            <small class="text-muted">Top Performers</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <h4 class="text-warning" id="needsAttention">--</h4>
                                        <small class="text-muted">Needs Attention</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-clock me-2"></i>Recent Activities
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="recentActivities">
                                    <!-- Recent activities will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Performance Tab -->
            <div class="tab-pane fade" id="team" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users me-2"></i>Team Performance Overview
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="teamPerformanceList">
                                    <!-- Team performance items will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card dashboard-card">
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
                    <div class="col-12">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-table me-2"></i>Detailed Team Performance
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-performance table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Staff Member</th>
                                                <th>Title</th>
                                                <th>Current Score</th>
                                                <th>Previous Score</th>
                                                <th>Trend</th>
                                                <th>Initiatives</th>
                                                <th>Last Update</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="teamPerformanceTable">
                                            <!-- Table content will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Goals & KPIs Tab -->
            <div class="tab-pane fade" id="goals" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bullseye me-2"></i>Key Performance Indicators
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="kpiList">
                                    <!-- KPI items will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Goal Achievement Progress
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="goalProgressChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resources Tab -->
            <div class="tab-pane fade" id="resources" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-dollar-sign me-2"></i>Budget Utilization
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="budgetChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user-clock me-2"></i>Staff Allocation
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="staffAllocationChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Initiatives Tab -->
            <div class="tab-pane fade" id="initiatives" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-project-diagram me-2"></i>Department Initiatives
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="initiativesList">
                                    <!-- Initiatives will be loaded here -->
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
            
            // Add global fallback functions for dashboard navigation
            window.refreshDashboard = function() {
                console.log('Refreshing dashboard...');
                location.reload();
            };
            
            window.exportReport = function() {
                console.log('Exporting report...');
                window.print();
            };
            
            // Ensure these functions are available globally
            window.departmentDashboard = function(deptId) {
                if (typeof parent !== 'undefined' && parent.departmentDashboard) {
                    parent.departmentDashboard(deptId);
                } else {
                    console.log('Parent departmentDashboard not available');
                }
            };

                    // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dashboard initializing with:', {
                departmentId: departmentId,
                objectPeriod: objectPeriod,
                objectDate: objectDate
            });
            
            // Add error handling for missing elements
            try {
                loadDashboardData();
                initializeCharts();
            } catch (error) {
                console.error('Error initializing dashboard:', error);
                // Show error message to user
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger';
                errorDiv.innerHTML = '<strong>Dashboard Error:</strong> ' + error.message;
                document.body.insertBefore(errorDiv, document.body.firstChild);
            }
        });

        // Load dashboard data
        function loadDashboardData() {
            loadOverviewMetrics();
            loadTeamPerformance();
            loadKPIs();
            loadInitiatives();
            loadRecentActivities();
        }

        // Load overview metrics
        function loadOverviewMetrics() {
            console.log('Loading overview metrics...');
            
            // Check if elements exist before trying to update them
            const elements = {
                'overallScore': document.getElementById('overallScore'),
                'teamSize': document.getElementById('teamSize'),
                'activeInitiatives': document.getElementById('activeInitiatives'),
                'completionRate': document.getElementById('completionRate'),
                'avgPerformance': document.getElementById('avgPerformance'),
                'topPerformers': document.getElementById('topPerformers'),
                'needsAttention': document.getElementById('needsAttention')
            };
            
            // Check for missing elements
            const missingElements = Object.keys(elements).filter(key => !elements[key]);
            if (missingElements.length > 0) {
                console.error('Missing elements:', missingElements);
            }
            
            fetch('get-department-metrics.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${departmentId}&objectPeriod=${objectPeriod}&objectDate=${objectDate}`
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Overview metrics data:', data);
                
                // Safely update elements that exist
                if (elements.overallScore) elements.overallScore.textContent = data.overallScore || '--';
                if (elements.teamSize) elements.teamSize.textContent = data.teamSize || '--';
                if (elements.activeInitiatives) elements.activeInitiatives.textContent = data.activeInitiatives || '--';
                if (elements.completionRate) elements.completionRate.textContent = (data.completionRate || '--') + '%';
                if (elements.avgPerformance) elements.avgPerformance.textContent = data.avgPerformance || '--';
                if (elements.topPerformers) elements.topPerformers.textContent = data.topPerformers || '--';
                if (elements.needsAttention) elements.needsAttention.textContent = data.needsAttention || '--';

                // Update trends if function exists
                if (typeof updateTrendIndicators === 'function' && data.trends) {
                    updateTrendIndicators(data.trends);
                }
            })
            .catch(error => {
                console.error('Error loading overview metrics:', error);
                // Show error message to user
                Object.values(elements).forEach(element => {
                    if (element) element.textContent = 'Error';
                });
                
                // Show error notification
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger alert-dismissible fade show';
                errorDiv.innerHTML = `
                    <strong>Data Loading Error:</strong> Unable to load dashboard data. 
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.insertBefore(errorDiv, document.body.firstChild);
            });
        }

        // Load team performance
        function loadTeamPerformance() {
            console.log('Loading team performance...');
            fetch('get-team-performance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${departmentId}&objectPeriod=${objectPeriod}&objectDate=${objectDate}`
            })
            .then(response => {
                console.log('Team performance response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Team performance data:', data);
                renderTeamPerformanceList(data.teamList);
                renderTeamPerformanceTable(data.teamDetails);
                updatePerformanceDistributionChart(data.distribution);
            })
            .catch(error => {
                console.error('Error loading team performance:', error);
                // Show error message to user
                const container = document.getElementById('teamPerformanceList');
                if (container) {
                    container.innerHTML = '<p class="text-danger">Error loading team performance data.</p>';
                }
            });
        }

        // Render team performance list
        function renderTeamPerformanceList(teamList) {
            const container = document.getElementById('teamPerformanceList');
            if (!teamList || teamList.length === 0) {
                container.innerHTML = '<p class="text-muted">No team data available.</p>';
                return;
            }

            let html = '';
            teamList.forEach(member => {
                const performanceClass = getPerformanceClass(member.score);
                const performanceWidth = Math.min(member.score, 100);

                html += `
                    <div class="staff-performance-item">
                        <img src="${member.photo || '../images/profilePics/default.png'}"
                             alt="${member.name}" class="staff-avatar">
                        <div>
                            <div class="fw-bold">${member.name}</div>
                            <small class="text-muted">${member.title}</small>
                        </div>
                        <div class="performance-bar">
                            <div class="performance-fill ${performanceClass}"
                                 style="width: ${performanceWidth}%"></div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">${member.score}%</div>
                            <small class="text-muted">${member.initiatives} initiatives</small>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Get performance class based on score
        function getPerformanceClass(score) {
            if (score >= 90) return 'performance-excellent';
            if (score >= 75) return 'performance-good';
            if (score >= 60) return 'performance-average';
            return 'performance-poor';
        }

        // Render team performance table
        function renderTeamPerformanceTable(teamDetails) {
            const tbody = document.getElementById('teamPerformanceTable');
            if (!teamDetails || teamDetails.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No detailed data available.</td></tr>';
                return;
            }

            let html = '';
            teamDetails.forEach(member => {
                const trendIcon = getTrendIcon(member.trend);
                const statusBadge = getStatusBadge(member.status);

                html += `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="${member.photo || '../images/profilePics/default.jpg'}"
                                     alt="${member.name}" class="staff-avatar me-2" style="width: 32px; height: 32px;">
                                ${member.name}
                            </div>
                        </td>
                        <td>${member.title}</td>
                        <td><span class="badge bg-primary">${member.currentScore}%</span></td>
                        <td><span class="badge bg-secondary">${member.previousScore}%</span></td>
                        <td>${trendIcon}</td>
                        <td>${member.initiatives}</td>
                        <td>${member.lastUpdate}</td>
                        <td>${statusBadge}</td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        // Get trend icon
        function getTrendIcon(trend) {
            if (trend > 0) return '<i class="fas fa-arrow-up text-success"></i>';
            if (trend < 0) return '<i class="fas fa-arrow-down text-danger"></i>';
            return '<i class="fas fa-minus text-muted"></i>';
        }

        // Get status badge
        function getStatusBadge(status) {
            const badges = {
                'active': '<span class="badge bg-success">Active</span>',
                'inactive': '<span class="badge bg-secondary">Inactive</span>',
                'attention': '<span class="badge bg-warning">Needs Attention</span>'
            };
            return badges[status] || '<span class="badge bg-light text-dark">Unknown</span>';
        }

        // Initialize charts
        function initializeCharts() {
            initPerformanceTrendChart();
            initPerformanceDistributionChart();
            initGoalProgressChart();
            initBudgetChart();
            initStaffAllocationChart();
        }

        // Initialize performance trend chart
        function initPerformanceTrendChart() {
            const ctx = document.getElementById('performanceTrendChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Department Score',
                        data: [65, 68, 72, 70, 75, 78],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
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

        // Initialize performance distribution chart
        function initPerformanceDistributionChart() {
            const ctx = document.getElementById('performanceDistributionChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Excellent', 'Good', 'Average', 'Poor'],
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

        // Initialize goal progress chart
        function initGoalProgressChart() {
            const ctx = document.getElementById('goalProgressChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Goal 1', 'Goal 2', 'Goal 3', 'Goal 4'],
                    datasets: [{
                        label: 'Progress',
                        data: [85, 92, 78, 65],
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

        // Initialize budget chart
        function initBudgetChart() {
            const ctx = document.getElementById('budgetChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Used', 'Remaining'],
                    datasets: [{
                        data: [65, 35],
                        backgroundColor: ['#667eea', '#e9ecef']
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

        // Initialize staff allocation chart
        function initStaffAllocationChart() {
            const ctx = document.getElementById('staffAllocationChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Project A', 'Project B', 'Project C', 'Operations'],
                    datasets: [{
                        label: 'Staff Hours',
                        data: [120, 80, 60, 200],
                        backgroundColor: '#764ba2'
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

        // Update trend indicators
        function updateTrendIndicators(trends) {
            if (trends) {
                updateTrendElement('scoreTrend', trends.score);
                updateTrendElement('teamTrend', trends.team);
                updateTrendElement('initiativesTrend', trends.initiatives);
                updateTrendElement('completionTrend', trends.completion);
            }
        }

        // Update individual trend element
        function updateTrendElement(elementId, trend) {
            const element = document.getElementById(elementId);
            if (element && trend) {
                let icon = 'fas fa-minus';
                let className = 'trend-neutral';

                if (trend.direction === 'up') {
                    icon = 'fas fa-arrow-up';
                    className = 'trend-up';
                } else if (trend.direction === 'down') {
                    icon = 'fas fa-arrow-down';
                    className = 'trend-down';
                }

                element.innerHTML = `<i class="${icon}"></i> ${trend.text}`;
                element.className = `trend-indicator ${className}`;
            }
        }

        // Refresh dashboard
        function refreshDashboard() {
            loadDashboardData();
        }

        // Export report
        function exportReport() {
            window.print();
        }

        // Load KPIs
        function loadKPIs() {
            // Implementation for loading KPIs
            const kpiContainer = document.getElementById('kpiList');
            kpiContainer.innerHTML = '<p class="text-muted">Loading KPIs...</p>';
        }

        // Load initiatives
        function loadInitiatives() {
            // Implementation for loading initiatives
            const initiativesContainer = document.getElementById('initiativesList');
            initiativesContainer.innerHTML = '<p class="text-muted">Loading initiatives...</p>';
        }

        // Load recent activities
        function loadRecentActivities() {
            const container = document.getElementById('recentActivities');
            container.innerHTML = `
                <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">KPI Updated</h6>
                                <p class="mb-1 text-muted small">Revenue target updated by John Doe</p>
                            </div>
                            <small class="text-muted">2h ago</small>
                        </div>
                    </div>
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Initiative Completed</h6>
                                <p class="mb-1 text-muted small">Project Alpha reached 100% completion</p>
                            </div>
                            <small class="text-muted">4h ago</small>
                        </div>
                    </div>
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Team Member Added</h6>
                                <p class="mb-1 text-muted small">Jane Smith joined the department</p>
                            </div>
                            <small class="text-muted">1d ago</small>
                        </div>
                    </div>
                </div>
            `;
        }

        // Update performance distribution chart
        function updatePerformanceDistributionChart(distribution) {
            // This would be called when real data is loaded
            console.log('Updating performance distribution chart with:', distribution);
        }

        })(); // Close IIFE
    </script>
</body>
</html>
