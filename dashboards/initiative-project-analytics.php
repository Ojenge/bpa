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
@$portfolioView = $_POST['portfolioView'] ?? $_GET['portfolioView'] ?? 'overview';

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
    <title>Initiative & Project Analytics - <?php echo htmlspecialchars($departmentName); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="/bpa/css/myBootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="/bpa/js/chart.min.js"></script>
    <!-- Custom CSS -->
    <link href="/bpa/css/dashboardTables.css" rel="stylesheet">
    
    <style>
        .portfolio-card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease-in-out;
        }
        
        .portfolio-card:hover {
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
        
        .portfolio-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
        
        .project-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s ease;
        }
        
        .project-item:hover {
            background-color: #f8f9fa;
        }
        
        .project-item:last-child {
            border-bottom: none;
        }
        
        .project-status {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .status-not-started { background: linear-gradient(135deg, #6c757d, #adb5bd); color: white; }
        .status-in-progress { background: linear-gradient(135deg, #17a2b8, #20c997); color: white; }
        .status-at-risk { background: linear-gradient(135deg, #ffc107, #fd7e14); color: white; }
        .status-completed { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
        .status-overdue { background: linear-gradient(135deg, #dc3545, #e74c3c); color: white; }
        
        .project-info {
            flex-grow: 1;
            margin-left: 1rem;
        }
        
        .progress-indicator {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-left: 1rem;
            position: relative;
        }
        
        .progress-ring {
            width: 80px;
            height: 80px;
            position: relative;
        }
        
        .progress-ring-circle {
            width: 100%;
            height: 100%;
            fill: transparent;
            stroke-width: 8;
            stroke-dasharray: 251.2; /* 2 * PI * 40 */
            stroke-dashoffset: 251.2;
            stroke-linecap: round;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        
        .progress-ring-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .budget-bar {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 0.5rem 0;
            position: relative;
        }
        
        .budget-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        
        .budget-marker {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 3px;
            background-color: #dc3545;
            z-index: 10;
        }
        
        .risk-indicator {
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }
        
        .risk-low { color: #28a745; }
        .risk-medium { color: #ffc107; }
        .risk-high { color: #dc3545; }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 1rem 0;
        }
        
        .nav-pills .nav-link.active {
            background-color: #667eea;
        }
        
        .portfolio-selector {
            background: white;
            border-radius: 0.5rem;
            padding: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1rem;
        }
        
        .roi-card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .timeline-item {
            position: relative;
            padding-left: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.5rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #667eea;
        }
        
        .timeline-item::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 1.2rem;
            width: 2px;
            height: calc(100% - 0.7rem);
            background-color: #e9ecef;
        }
        
        .timeline-item:last-child::after {
            display: none;
        }
        
        .timeline-overdue::before {
            background-color: #dc3545;
        }
        
        .timeline-completed::before {
            background-color: #28a745;
        }
        
        .timeline-upcoming::before {
            background-color: #ffc107;
        }
        
        .resource-allocation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .resource-allocation:last-child {
            border-bottom: none;
        }
        
        .resource-name {
            font-weight: 500;
            flex-grow: 1;
        }
        
        .resource-values {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .value-allocated {
            font-weight: bold;
            color: #667eea;
        }
        
        .value-utilized {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .value-efficiency {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }
        
        .efficiency-high {
            background-color: #d4edda;
            color: #155724;
        }
        
        .efficiency-medium {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .efficiency-low {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .project-card {
            border-left: 4px solid #667eea;
            margin-bottom: 1rem;
        }
        
        .project-card.overdue {
            border-left-color: #dc3545;
        }
        
        .project-card.completed {
            border-left-color: #28a745;
        }
        
        .project-card.at-risk {
            border-left-color: #ffc107;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="portfolio-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-project-diagram me-3"></i>
                        Initiative & Project Analytics
                    </h1>
                    <p class="mb-0 opacity-75">
                        <?php echo htmlspecialchars($departmentName); ?> - Portfolio management and project insights
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-light btn-sm" onclick="refreshPortfolio()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="exportPortfolio()">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="newProject()">
                            <i class="fas fa-plus me-1"></i> New Project
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Portfolio Controls -->
        <div class="portfolio-selector">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label for="departmentSelect" class="form-label">Department:</label>
                    <select class="form-select form-select-sm" id="departmentSelect" onchange="changeDepartment()">
                        <!-- Options will be loaded dynamically -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="portfolioViewSelect" class="form-label">Portfolio View:</label>
                    <select class="form-select form-select-sm" id="portfolioViewSelect" onchange="changePortfolioView()">
                        <option value="overview">Portfolio Overview</option>
                        <option value="timeline">Timeline Analysis</option>
                        <option value="resource">Resource Allocation</option>
                        <option value="risk">Risk Assessment</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="periodSelect" class="form-label">Time Period:</label>
                    <select class="form-select form-select-sm" id="periodSelect" onchange="changePeriod()">
                        <option value="current">Current Period</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                        <option value="all">All Projects</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label">Status Filter:</label>
                    <select class="form-select form-select-sm" id="statusFilter" onchange="changeStatusFilter()">
                        <option value="all">All Projects</option>
                        <option value="active">Active Only</option>
                        <option value="completed">Completed</option>
                        <option value="at-risk">At Risk</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Key Portfolio Metrics -->
        <div class="row">
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="totalProjects">--</div>
                    <div class="metric-label">Total Projects</div>
                    <div class="risk-indicator" id="projectsTrend">
                        <i class="fas fa-project-diagram"></i> Active portfolio
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="portfolioValue">--</div>
                    <div class="metric-label">Portfolio Value</div>
                    <div class="risk-indicator" id="valueTrend">
                        <i class="fas fa-dollar-sign"></i> Total budget
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="avgCompletion">--</div>
                    <div class="metric-label">Avg Completion</div>
                    <div class="risk-indicator" id="completionTrend">
                        <i class="fas fa-chart-line"></i> Portfolio progress
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="roi-card">
                    <div class="metric-value" id="portfolioROI">--</div>
                    <div class="metric-label">Portfolio ROI</div>
                    <div class="risk-indicator" id="roiTrend">
                        <i class="fas fa-chart-bar"></i> Return on investment
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-pills mb-4" id="portfolioTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                    <i class="fas fa-chart-pie me-2"></i>Portfolio Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="projects-tab" data-bs-toggle="pill" data-bs-target="#projects" type="button" role="tab">
                    <i class="fas fa-tasks me-2"></i>Project Details
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="timeline-tab" data-bs-toggle="pill" data-bs-target="#timeline" type="button" role="tab">
                    <i class="fas fa-calendar-alt me-2"></i>Timeline Analysis
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="resources-tab" data-bs-toggle="pill" data-bs-target="#resources" type="button" role="tab">
                    <i class="fas fa-users me-2"></i>Resource Allocation
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="risk-tab" data-bs-toggle="pill" data-bs-target="#risk" type="button" role="tab">
                    <i class="fas fa-exclamation-triangle me-2"></i>Risk Assessment
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="roi-tab" data-bs-toggle="pill" data-bs-target="#roi" type="button" role="tab">
                    <i class="fas fa-chart-line me-2"></i>ROI Analysis
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="portfolioTabContent">
            <!-- Portfolio Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="row">
                    <!-- Portfolio Status Distribution -->
                    <div class="col-md-6">
                        <div class="card portfolio-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>Portfolio Status Distribution
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="portfolioStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Budget Allocation -->
                    <div class="col-md-6">
                        <div class="card portfolio-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-dollar-sign me-2"></i>Budget Allocation
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="budgetAllocationChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Portfolio Performance -->
                    <div class="col-md-8">
                        <div class="card portfolio-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Portfolio Performance Trends
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="portfolioPerformanceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Key Metrics Summary -->
                    <div class="col-md-4">
                        <div class="card portfolio-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-tachometer-alt me-2"></i>Key Metrics
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="keyMetricsSummary">
                                    <!-- Key metrics will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Details Tab -->
            <div class="tab-pane fade" id="projects" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card portfolio-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-tasks me-2"></i>Project Portfolio Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="projectsList">
                                    <!-- Projects will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline Analysis Tab -->
            <div class="tab-pane fade" id="timeline" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card portfolio-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-alt me-2"></i>Project Timeline
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="timelineChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card portfolio-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-clock me-2"></i>Critical Milestones
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="criticalMilestones">
                                    <!-- Critical milestones will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resource Allocation Tab -->
            <div class="tab-pane fade" id="resources" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card portfolio-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users me-2"></i>Resource Allocation Overview
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="resourceAllocation">
                                    <!-- Resource allocation will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card portfolio-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Resource Utilization
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="resourceUtilizationChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Risk Assessment Tab -->
            <div class="tab-pane fade" id="risk" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card portfolio-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Risk Assessment Matrix
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="riskMatrixChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card portfolio-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-shield-alt me-2"></i>Risk Mitigation
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="riskMitigation">
                                    <!-- Risk mitigation strategies will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card portfolio-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-list me-2"></i>Project Risk Register
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="riskRegister">
                                    <!-- Risk register will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROI Analysis Tab -->
            <div class="tab-pane fade" id="roi" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card portfolio-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>ROI Performance
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="roiPerformanceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card portfolio-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calculator me-2"></i>Cost-Benefit Analysis
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="costBenefitChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card portfolio-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-table me-2"></i>ROI Analysis Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="roiAnalysisDetails">
                                    <!-- ROI analysis details will be loaded here -->
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
        // Global variables
        let currentDepartmentId = '<?php echo $departmentId; ?>';
        let currentPortfolioView = '<?php echo $portfolioView; ?>';
        let currentPeriod = '<?php echo $objectPeriod; ?>';
        let currentDate = '<?php echo $objectDate; ?>';
        let currentStatusFilter = 'all';

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadDepartmentOptions();
            loadPortfolioData();
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

        // Load portfolio data
        function loadPortfolioData() {
            loadPortfolioMetrics();
            loadProjectsList();
            loadTimelineAnalysis();
            loadResourceAllocation();
            loadRiskAssessment();
            loadROIAnalysis();
        }

        // Load portfolio metrics
        function loadPortfolioMetrics() {
            fetch('get-portfolio-metrics.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${currentDepartmentId}&period=${currentPeriod}&date=${currentDate}&statusFilter=${currentStatusFilter}`
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('totalProjects').textContent = data.totalProjects || '--';
                document.getElementById('portfolioValue').textContent = formatCurrency(data.portfolioValue || 0);
                document.getElementById('avgCompletion').textContent = (data.avgCompletion || '--') + '%';
                document.getElementById('portfolioROI').textContent = (data.portfolioROI || '--') + '%';

                // Update key metrics summary
                updateKeyMetricsSummary(data.keyMetrics);

                // Update charts
                updatePortfolioStatusChart(data.statusDistribution);
                updateBudgetAllocationChart(data.budgetAllocation);
                updatePortfolioPerformanceChart(data.performanceTrends);
            })
            .catch(error => {
                console.error('Error loading portfolio metrics:', error);
            });
        }

        // Load projects list
        function loadProjectsList() {
            fetch('get-projects-list.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${currentDepartmentId}&period=${currentPeriod}&date=${currentDate}&statusFilter=${currentStatusFilter}`
            })
            .then(response => response.json())
            .then(data => {
                renderProjectsList(data.projects);
            })
            .catch(error => {
                console.error('Error loading projects list:', error);
            });
        }

        // Render projects list
        function renderProjectsList(projects) {
            const container = document.getElementById('projectsList');
            if (!projects || projects.length === 0) {
                container.innerHTML = '<p class="text-muted">No projects found for the selected criteria.</p>';
                return;
            }

            let html = '';
            projects.forEach(project => {
                const statusClass = getProjectStatusClass(project.status, project.completion, project.dueDate);
                const riskClass = getRiskClass(project.riskLevel);

                html += `
                    <div class="project-item">
                        <div class="project-status ${statusClass}">
                            ${Math.round(project.completion || 0)}%
                        </div>
                        <div class="project-info">
                            <h6 class="mb-1">${project.name}</h6>
                            <div class="budget-bar">
                                <div class="budget-fill bg-primary" style="width: ${project.budgetUtilization || 0}%"></div>
                                <div class="budget-marker" style="left: 100%"></div>
                            </div>
                            <small class="text-muted">
                                Budget: ${formatCurrency(project.budget)} |
                                Manager: ${project.manager} |
                                Due: ${project.dueDate || 'Not set'}
                            </small>
                        </div>
                        <div class="progress-ring">
                            <svg width="80" height="80">
                                <circle class="progress-ring-circle"
                                        cx="40" cy="40" r="30"
                                        stroke="#667eea"
                                        style="stroke-dashoffset: ${251.2 - (251.2 * (project.completion || 0) / 100)}">
                                </circle>
                            </svg>
                            <div class="progress-ring-text">${Math.round(project.completion || 0)}%</div>
                        </div>
                        <div class="risk-indicator ms-2 ${riskClass}">
                            <i class="fas fa-exclamation-triangle"></i>
                            <br><small>${project.riskLevel || 'Low'}</small>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Load timeline analysis
        function loadTimelineAnalysis() {
            fetch('get-timeline-analysis.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${currentDepartmentId}&period=${currentPeriod}&date=${currentDate}`
            })
            .then(response => response.json())
            .then(data => {
                updateTimelineChart(data.timeline);
                renderCriticalMilestones(data.milestones);
            })
            .catch(error => {
                console.error('Error loading timeline analysis:', error);
            });
        }

        // Render critical milestones
        function renderCriticalMilestones(milestones) {
            const container = document.getElementById('criticalMilestones');
            if (!milestones || milestones.length === 0) {
                container.innerHTML = '<p class="text-muted">No critical milestones found.</p>';
                return;
            }

            let html = '';
            milestones.forEach(milestone => {
                const timelineClass = getTimelineClass(milestone.status, milestone.dueDate);

                html += `
                    <div class="timeline-item ${timelineClass}">
                        <h6 class="mb-1">${milestone.projectName}</h6>
                        <p class="mb-1 text-muted small">${milestone.description}</p>
                        <small class="text-muted">Due: ${milestone.dueDate}</small>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Load resource allocation
        function loadResourceAllocation() {
            fetch('get-resource-allocation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${currentDepartmentId}&period=${currentPeriod}&date=${currentDate}`
            })
            .then(response => response.json())
            .then(data => {
                renderResourceAllocation(data.resources);
                updateResourceUtilizationChart(data.utilization);
            })
            .catch(error => {
                console.error('Error loading resource allocation:', error);
            });
        }

        // Initialize charts
        function initializeCharts() {
            initPortfolioStatusChart();
            initBudgetAllocationChart();
            initPortfolioPerformanceChart();
            initTimelineChart();
            initResourceUtilizationChart();
            initRiskMatrixChart();
            initROIPerformanceChart();
            initCostBenefitChart();
        }

        // Initialize portfolio status chart
        function initPortfolioStatusChart() {
            const ctx = document.getElementById('portfolioStatusChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'In Progress', 'At Risk', 'Not Started', 'Overdue'],
                    datasets: [{
                        data: [25, 40, 15, 15, 5],
                        backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#6c757d', '#dc3545']
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

        // Initialize budget allocation chart
        function initBudgetAllocationChart() {
            const ctx = document.getElementById('budgetAllocationChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Strategic', 'Operational', 'Innovation', 'Maintenance'],
                    datasets: [{
                        label: 'Budget Allocation',
                        data: [450000, 320000, 180000, 150000],
                        backgroundColor: ['#667eea', '#28a745', '#ffc107', '#17a2b8']
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
                            ticks: {
                                callback: function(value) {
                                    return '$' + (value / 1000) + 'K';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Initialize portfolio performance chart
        function initPortfolioPerformanceChart() {
            const ctx = document.getElementById('portfolioPerformanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Portfolio Completion',
                        data: [15, 25, 35, 45, 60, 75],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Budget Utilization',
                        data: [20, 30, 40, 50, 65, 80],
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

        // Initialize timeline chart
        function initTimelineChart() {
            const ctx = document.getElementById('timelineChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Q1', 'Q2', 'Q3', 'Q4'],
                    datasets: [{
                        label: 'Planned Projects',
                        data: [8, 12, 10, 6],
                        backgroundColor: '#667eea'
                    }, {
                        label: 'Completed Projects',
                        data: [6, 9, 7, 4],
                        backgroundColor: '#28a745'
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

        // Initialize resource utilization chart
        function initResourceUtilizationChart() {
            const ctx = document.getElementById('resourceUtilizationChart').getContext('2d');
            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: ['Staff', 'Budget', 'Technology', 'Time', 'Expertise'],
                    datasets: [{
                        label: 'Utilization %',
                        data: [85, 75, 90, 80, 70],
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

        // Initialize risk matrix chart
        function initRiskMatrixChart() {
            const ctx = document.getElementById('riskMatrixChart').getContext('2d');
            new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'High Risk',
                        data: [{x: 4, y: 4}, {x: 5, y: 3}],
                        backgroundColor: '#dc3545'
                    }, {
                        label: 'Medium Risk',
                        data: [{x: 3, y: 3}, {x: 2, y: 4}, {x: 4, y: 2}],
                        backgroundColor: '#ffc107'
                    }, {
                        label: 'Low Risk',
                        data: [{x: 1, y: 2}, {x: 2, y: 1}, {x: 1, y: 1}],
                        backgroundColor: '#28a745'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Probability'
                            },
                            min: 0,
                            max: 5
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Impact'
                            },
                            min: 0,
                            max: 5
                        }
                    }
                }
            });
        }

        // Initialize ROI performance chart
        function initROIPerformanceChart() {
            const ctx = document.getElementById('roiPerformanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Project A', 'Project B', 'Project C', 'Project D', 'Project E'],
                    datasets: [{
                        label: 'ROI %',
                        data: [25, 18, 32, 15, 28],
                        backgroundColor: function(context) {
                            const value = context.parsed.y;
                            if (value >= 25) return '#28a745';
                            if (value >= 15) return '#ffc107';
                            return '#dc3545';
                        }
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
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Initialize cost-benefit chart
        function initCostBenefitChart() {
            const ctx = document.getElementById('costBenefitChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Year 1', 'Year 2', 'Year 3', 'Year 4', 'Year 5'],
                    datasets: [{
                        label: 'Cumulative Costs',
                        data: [500000, 750000, 900000, 1000000, 1050000],
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4,
                        fill: false
                    }, {
                        label: 'Cumulative Benefits',
                        data: [200000, 600000, 1200000, 1800000, 2500000],
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
                            ticks: {
                                callback: function(value) {
                                    return '$' + (value / 1000) + 'K';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Utility functions
        function getProjectStatusClass(status, completion, dueDate) {
            if (completion >= 100) return 'status-completed';
            if (new Date(dueDate) < new Date() && completion < 100) return 'status-overdue';
            if (completion >= 75) return 'status-in-progress';
            if (completion > 0) return 'status-in-progress';
            return 'status-not-started';
        }

        function getRiskClass(riskLevel) {
            switch (riskLevel?.toLowerCase()) {
                case 'high': return 'risk-high';
                case 'medium': return 'risk-medium';
                case 'low':
                default: return 'risk-low';
            }
        }

        function getTimelineClass(status, dueDate) {
            if (status === 'completed') return 'timeline-completed';
            if (new Date(dueDate) < new Date()) return 'timeline-overdue';
            return 'timeline-upcoming';
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount || 0);
        }

        // Update functions for charts
        function updatePortfolioStatusChart(data) {
            console.log('Updating portfolio status chart with:', data);
        }

        function updateBudgetAllocationChart(data) {
            console.log('Updating budget allocation chart with:', data);
        }

        function updatePortfolioPerformanceChart(data) {
            console.log('Updating portfolio performance chart with:', data);
        }

        function updateTimelineChart(data) {
            console.log('Updating timeline chart with:', data);
        }

        function updateResourceUtilizationChart(data) {
            console.log('Updating resource utilization chart with:', data);
        }

        function updateKeyMetricsSummary(metrics) {
            const container = document.getElementById('keyMetricsSummary');
            if (metrics) {
                container.innerHTML = `
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h6 class="text-success">${metrics.onTime || 0}</h6>
                            <small class="text-muted">On Time</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h6 class="text-warning">${metrics.atRisk || 0}</h6>
                            <small class="text-muted">At Risk</small>
                        </div>
                        <div class="col-6">
                            <h6 class="text-info">${metrics.budgetUtilization || 0}%</h6>
                            <small class="text-muted">Budget Used</small>
                        </div>
                        <div class="col-6">
                            <h6 class="text-primary">${metrics.resourceUtilization || 0}%</h6>
                            <small class="text-muted">Resources Used</small>
                        </div>
                    </div>
                `;
            }
        }

        // Event handlers
        function changeDepartment() {
            currentDepartmentId = document.getElementById('departmentSelect').value;
            loadPortfolioData();
        }

        function changePortfolioView() {
            currentPortfolioView = document.getElementById('portfolioViewSelect').value;
            loadPortfolioData();
        }

        function changePeriod() {
            currentPeriod = document.getElementById('periodSelect').value;
            loadPortfolioData();
        }

        function changeStatusFilter() {
            currentStatusFilter = document.getElementById('statusFilter').value;
            loadPortfolioData();
        }

        function refreshPortfolio() {
            loadPortfolioData();
        }

        function exportPortfolio() {
            window.print();
        }

        function newProject() {
            alert('New Project functionality would open a project creation form');
        }

        // Load remaining functions (stubs for now)
        function loadRiskAssessment() {
            // Implementation for risk assessment
        }

        function loadROIAnalysis() {
            // Implementation for ROI analysis
        }

        function renderResourceAllocation(resources) {
            // Implementation for resource allocation rendering
        }
    </script>
</body>
</html>
