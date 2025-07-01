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
@$forecastHorizon = $_POST['forecastHorizon'] ?? $_GET['forecastHorizon'] ?? '6';

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
    <title>Predictive & Trend Analytics - <?php echo htmlspecialchars($departmentName); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="../css/myBootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="../js/chart.min.js"></script>
    <!-- Custom CSS -->
    <link href="../css/dashboardTables.css" rel="stylesheet">
    
    <style>
        .predictive-card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease-in-out;
        }
        
        .predictive-card:hover {
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
        
        .predictive-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
        
        .forecast-card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .warning-card {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .alert-card {
            background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .trend-indicator {
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
        
        .nav-pills .nav-link.active {
            background-color: #667eea;
        }
        
        .predictive-selector {
            background: white;
            border-radius: 0.5rem;
            padding: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1rem;
        }
        
        .prediction-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s ease;
        }
        
        .prediction-item:hover {
            background-color: #f8f9fa;
        }
        
        .prediction-item:last-child {
            border-bottom: none;
        }
        
        .prediction-icon {
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
        
        .prediction-positive { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
        .prediction-neutral { background: linear-gradient(135deg, #17a2b8, #20c997); color: white; }
        .prediction-negative { background: linear-gradient(135deg, #ffc107, #fd7e14); color: white; }
        .prediction-critical { background: linear-gradient(135deg, #dc3545, #e74c3c); color: white; }
        
        .prediction-info {
            flex-grow: 1;
            margin-left: 1rem;
        }
        
        .confidence-indicator {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-left: 1rem;
        }
        
        .confidence-high { background-color: #28a745; color: white; }
        .confidence-medium { background-color: #ffc107; color: white; }
        .confidence-low { background-color: #dc3545; color: white; }
        
        .forecast-timeline {
            position: relative;
            padding-left: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .forecast-timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.5rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #667eea;
        }
        
        .forecast-timeline::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 1.2rem;
            width: 2px;
            height: calc(100% - 0.7rem);
            background-color: #e9ecef;
        }
        
        .forecast-timeline:last-child::after {
            display: none;
        }
        
        .forecast-positive::before {
            background-color: #28a745;
        }
        
        .forecast-warning::before {
            background-color: #ffc107;
        }
        
        .forecast-critical::before {
            background-color: #dc3545;
        }
        
        .anomaly-detection {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .anomaly-detection:last-child {
            border-bottom: none;
        }
        
        .anomaly-name {
            font-weight: 500;
            flex-grow: 1;
        }
        
        .anomaly-values {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .anomaly-score {
            font-weight: bold;
            color: #667eea;
        }
        
        .anomaly-severity {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }
        
        .severity-high {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .severity-medium {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .severity-low {
            background-color: #d4edda;
            color: #155724;
        }
        
        .scenario-card {
            border-left: 4px solid #667eea;
            margin-bottom: 1rem;
        }
        
        .scenario-card.optimistic {
            border-left-color: #28a745;
        }
        
        .scenario-card.pessimistic {
            border-left-color: #dc3545;
        }
        
        .scenario-card.realistic {
            border-left-color: #ffc107;
        }
        
        .prediction-accuracy {
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin: 0.5rem 0;
        }
        
        .accuracy-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .early-warning {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .warning-high { background-color: #dc3545; color: white; }
        .warning-medium { background-color: #ffc107; color: white; }
        .warning-low { background-color: #28a745; color: white; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="predictive-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-chart-line me-3"></i>
                        Predictive & Trend Analytics
                    </h1>
                    <p class="mb-0 opacity-75">
                        <?php echo htmlspecialchars($departmentName); ?> - Forecasting, trends, and early warning systems
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-light btn-sm" onclick="refreshPredictiveData()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="exportPredictions()">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="configurePredictions()">
                            <i class="fas fa-cog me-1"></i> Configure
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Predictive Controls -->
        <div class="predictive-selector">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label for="departmentSelect" class="form-label">Department:</label>
                    <select class="form-select form-select-sm" id="departmentSelect" onchange="changeDepartment()">
                        <!-- Options will be loaded dynamically -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="forecastHorizonSelect" class="form-label">Forecast Horizon:</label>
                    <select class="form-select form-select-sm" id="forecastHorizonSelect" onchange="changeForecastHorizon()">
                        <option value="3">3 Months</option>
                        <option value="6" selected>6 Months</option>
                        <option value="12">12 Months</option>
                        <option value="24">24 Months</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="analysisTypeSelect" class="form-label">Analysis Type:</label>
                    <select class="form-select form-select-sm" id="analysisTypeSelect" onchange="changeAnalysisType()">
                        <option value="performance">Performance Trends</option>
                        <option value="goals">Goal Achievement</option>
                        <option value="resources">Resource Utilization</option>
                        <option value="risks">Risk Assessment</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="confidenceLevel" class="form-label">Confidence Level:</label>
                    <select class="form-select form-select-sm" id="confidenceLevel" onchange="changeConfidenceLevel()">
                        <option value="80">80%</option>
                        <option value="90" selected>90%</option>
                        <option value="95">95%</option>
                        <option value="99">99%</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Key Predictive Metrics -->
        <div class="row">
            <div class="col-md-3">
                <div class="forecast-card">
                    <div class="metric-value" id="forecastAccuracy">--</div>
                    <div class="metric-label">Forecast Accuracy</div>
                    <div class="trend-indicator" id="accuracyTrend">
                        <i class="fas fa-target"></i> Prediction quality
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="trendStrength">--</div>
                    <div class="metric-label">Trend Strength</div>
                    <div class="trend-indicator" id="trendIndicator">
                        <i class="fas fa-chart-line"></i> Pattern confidence
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="warning-card">
                    <div class="metric-value" id="earlyWarnings">--</div>
                    <div class="metric-label">Early Warnings</div>
                    <div class="trend-indicator" id="warningsTrend">
                        <i class="fas fa-exclamation-triangle"></i> Risk alerts
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert-card">
                    <div class="metric-value" id="anomaliesDetected">--</div>
                    <div class="metric-label">Anomalies Detected</div>
                    <div class="trend-indicator" id="anomaliesTrend">
                        <i class="fas fa-search"></i> Pattern deviations
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-pills mb-4" id="predictiveTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="forecasting-tab" data-bs-toggle="pill" data-bs-target="#forecasting" type="button" role="tab">
                    <i class="fas fa-crystal-ball me-2"></i>Forecasting
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="trends-tab" data-bs-toggle="pill" data-bs-target="#trends" type="button" role="tab">
                    <i class="fas fa-chart-line me-2"></i>Trend Analysis
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="warnings-tab" data-bs-toggle="pill" data-bs-target="#warnings" type="button" role="tab">
                    <i class="fas fa-exclamation-triangle me-2"></i>Early Warnings
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="anomalies-tab" data-bs-toggle="pill" data-bs-target="#anomalies" type="button" role="tab">
                    <i class="fas fa-search me-2"></i>Anomaly Detection
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="scenarios-tab" data-bs-toggle="pill" data-bs-target="#scenarios" type="button" role="tab">
                    <i class="fas fa-sitemap me-2"></i>Scenario Planning
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="insights-tab" data-bs-toggle="pill" data-bs-target="#insights" type="button" role="tab">
                    <i class="fas fa-lightbulb me-2"></i>Predictive Insights
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="predictiveTabContent">
            <!-- Forecasting Tab -->
            <div class="tab-pane fade show active" id="forecasting" role="tabpanel">
                <div class="row">
                    <!-- Forecast Chart -->
                    <div class="col-md-8">
                        <div class="card predictive-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-crystal-ball me-2"></i>Performance Forecast
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="forecastChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Forecast Summary -->
                    <div class="col-md-4">
                        <div class="card predictive-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-list me-2"></i>Forecast Summary
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="forecastSummary">
                                    <!-- Forecast summary will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Prediction Timeline -->
                    <div class="col-md-6">
                        <div class="card predictive-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-alt me-2"></i>Prediction Timeline
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="predictionTimeline">
                                    <!-- Prediction timeline will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Confidence Intervals -->
                    <div class="col-md-6">
                        <div class="card predictive-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-area me-2"></i>Confidence Intervals
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="confidenceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trend Analysis Tab -->
            <div class="tab-pane fade" id="trends" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card predictive-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Historical Trends
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="trendsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card predictive-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Trend Indicators
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="trendIndicators">
                                    <!-- Trend indicators will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card predictive-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-wave-square me-2"></i>Seasonality Analysis
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="seasonalityChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card predictive-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>Trend Decomposition
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="decompositionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Early Warnings Tab -->
            <div class="tab-pane fade" id="warnings" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card predictive-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Early Warning System
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="earlyWarningsList">
                                    <!-- Early warnings will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card predictive-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-shield-alt me-2"></i>Risk Levels
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="riskLevelsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Anomaly Detection Tab -->
            <div class="tab-pane fade" id="anomalies" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card predictive-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-search me-2"></i>Anomaly Detection
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="anomaliesList">
                                    <!-- Anomalies will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card predictive-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-scatter me-2"></i>Anomaly Patterns
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="anomalyPatternsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scenario Planning Tab -->
            <div class="tab-pane fade" id="scenarios" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card predictive-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-sitemap me-2"></i>Scenario Analysis
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="scenarioChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card predictive-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-list-alt me-2"></i>Scenario Outcomes
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="scenarioOutcomes">
                                    <!-- Scenario outcomes will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Predictive Insights Tab -->
            <div class="tab-pane fade" id="insights" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card predictive-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>Predictive Insights
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="predictiveInsights">
                                    <!-- Predictive insights will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card predictive-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bullseye me-2"></i>Recommendations
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

                <div class="row">
                    <div class="col-12">
                        <div class="card predictive-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Model Performance
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="modelPerformanceChart"></canvas>
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
            let currentForecastHorizon = '<?php echo $forecastHorizon; ?>';
            let currentPeriod = '<?php echo $objectPeriod; ?>';
            let currentDate = '<?php echo $objectDate; ?>';
            let currentAnalysisType = 'performance';
            let currentConfidenceLevel = '90';

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadDepartmentOptions();
            loadPredictiveData();
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

        // Load predictive data
        function loadPredictiveData() {
            loadPredictiveMetrics();
            loadForecastData();
            loadTrendAnalysis();
            loadEarlyWarnings();
            loadAnomalyDetection();
            loadScenarioPlanning();
            loadPredictiveInsights();
        }

        // Load predictive metrics
        function loadPredictiveMetrics() {
            fetch('get-predictive-metrics.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${currentDepartmentId}&forecastHorizon=${currentForecastHorizon}&analysisType=${currentAnalysisType}&confidenceLevel=${currentConfidenceLevel}`
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('forecastAccuracy').textContent = (data.forecastAccuracy || '--') + '%';
                document.getElementById('trendStrength').textContent = (data.trendStrength || '--') + '%';
                document.getElementById('earlyWarnings').textContent = data.earlyWarnings || '--';
                document.getElementById('anomaliesDetected').textContent = data.anomaliesDetected || '--';

                // Update trend indicators
                updateTrendIndicators(data.trends);
            })
            .catch(error => {
                console.error('Error loading predictive metrics:', error);
            });
        }

        // Load forecast data
        function loadForecastData() {
            fetch('get-forecast-data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${currentDepartmentId}&forecastHorizon=${currentForecastHorizon}&analysisType=${currentAnalysisType}&confidenceLevel=${currentConfidenceLevel}`
            })
            .then(response => response.json())
            .then(data => {
                updateForecastChart(data.forecast);
                updateConfidenceChart(data.confidence);
                renderForecastSummary(data.summary);
                renderPredictionTimeline(data.timeline);
            })
            .catch(error => {
                console.error('Error loading forecast data:', error);
            });
        }

        // Initialize charts
        function initializeCharts() {
            initForecastChart();
            initConfidenceChart();
            initTrendsChart();
            initSeasonalityChart();
            initDecompositionChart();
            initRiskLevelsChart();
            initAnomalyPatternsChart();
            initScenarioChart();
            initModelPerformanceChart();
        }

        // Initialize forecast chart
        function initForecastChart() {
            const ctx = document.getElementById('forecastChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Historical Data',
                        data: [75, 78, 82, 80, 85, 88, 90, 87, 89, 92, 88, 85],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: false
                    }, {
                        label: 'Forecast',
                        data: [null, null, null, null, null, null, null, null, 89, 91, 93, 95],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderDash: [5, 5],
                        tension: 0.4,
                        fill: false
                    }, {
                        label: 'Confidence Interval',
                        data: [null, null, null, null, null, null, null, null, 85, 87, 89, 91],
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        borderDash: [2, 2],
                        tension: 0.4,
                        fill: '+1'
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
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    }
                }
            });
        }

        // Initialize confidence chart
        function initConfidenceChart() {
            const ctx = document.getElementById('confidenceChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5', 'Month 6'],
                    datasets: [{
                        label: 'Upper Bound',
                        data: [95, 97, 98, 99, 100, 102],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        fill: '+1'
                    }, {
                        label: 'Forecast',
                        data: [90, 92, 94, 95, 96, 98],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.2)',
                        fill: false
                    }, {
                        label: 'Lower Bound',
                        data: [85, 87, 90, 91, 92, 94],
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        fill: false
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

        // Initialize trends chart
        function initTrendsChart() {
            const ctx = document.getElementById('trendsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Q1 2023', 'Q2 2023', 'Q3 2023', 'Q4 2023', 'Q1 2024', 'Q2 2024'],
                    datasets: [{
                        label: 'Performance Trend',
                        data: [75, 78, 82, 85, 88, 90],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Moving Average',
                        data: [76, 78, 81, 83, 86, 88],
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
        function initSeasonalityChart() {
            const ctx = document.getElementById('seasonalityChart').getContext('2d');
            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Seasonal Pattern',
                        data: [75, 78, 85, 88, 92, 95, 90, 85, 88, 90, 85, 80],
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

        function initDecompositionChart() {
            const ctx = document.getElementById('decompositionChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Trend', 'Seasonal', 'Residual'],
                    datasets: [{
                        label: 'Component Strength',
                        data: [75, 15, 10],
                        backgroundColor: ['#667eea', '#28a745', '#ffc107']
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

        function initRiskLevelsChart() {
            const ctx = document.getElementById('riskLevelsChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Low Risk', 'Medium Risk', 'High Risk', 'Critical Risk'],
                    datasets: [{
                        data: [60, 25, 12, 3],
                        backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545']
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

        function initAnomalyPatternsChart() {
            const ctx = document.getElementById('anomalyPatternsChart').getContext('2d');
            new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'Normal Data',
                        data: [{x: 1, y: 85}, {x: 2, y: 88}, {x: 3, y: 90}, {x: 4, y: 87}],
                        backgroundColor: '#28a745'
                    }, {
                        label: 'Anomalies',
                        data: [{x: 2.5, y: 65}, {x: 3.5, y: 105}],
                        backgroundColor: '#dc3545'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Time Period'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Performance Score'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function initScenarioChart() {
            const ctx = document.getElementById('scenarioChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Current', 'Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5', 'Month 6'],
                    datasets: [{
                        label: 'Optimistic Scenario',
                        data: [85, 88, 92, 95, 98, 100, 102],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: false
                    }, {
                        label: 'Realistic Scenario',
                        data: [85, 86, 88, 90, 92, 94, 95],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: false
                    }, {
                        label: 'Pessimistic Scenario',
                        data: [85, 83, 80, 78, 75, 73, 70],
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
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
                            max: 110
                        }
                    }
                }
            });
        }

        function initModelPerformanceChart() {
            const ctx = document.getElementById('modelPerformanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Accuracy', 'Precision', 'Recall', 'F1-Score'],
                    datasets: [{
                        label: 'Model Performance',
                        data: [92, 88, 90, 89],
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

        // Utility functions
        function updateTrendIndicators(trends) {
            if (trends) {
                updateTrendElement('accuracyTrend', trends.accuracy);
                updateTrendElement('trendIndicator', trends.strength);
                updateTrendElement('warningsTrend', trends.warnings);
                updateTrendElement('anomaliesTrend', trends.anomalies);
            }
        }

        function updateTrendElement(elementId, trend) {
            const element = document.getElementById(elementId);
            if (element && trend) {
                let className = 'trend-indicator trend-stable';

                if (trend.direction === 'up') {
                    className = 'trend-indicator trend-up';
                } else if (trend.direction === 'down') {
                    className = 'trend-indicator trend-down';
                }

                element.innerHTML = `<i class="${trend.icon}"></i> ${trend.text}`;
                element.className = className;
            }
        }

        // Update functions for charts
        function updateForecastChart(data) {
            console.log('Updating forecast chart with:', data);
        }

        function updateConfidenceChart(data) {
            console.log('Updating confidence chart with:', data);
        }

        function renderForecastSummary(summary) {
            const container = document.getElementById('forecastSummary');
            if (summary) {
                container.innerHTML = `
                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <h6 class="text-primary">${summary.nextPeriodForecast || 0}%</h6>
                            <small class="text-muted">Next Period Forecast</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h6 class="text-success">${summary.confidenceLevel || 0}%</h6>
                            <small class="text-muted">Confidence</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h6 class="text-info">${summary.trendDirection || 'Stable'}</h6>
                            <small class="text-muted">Trend</small>
                        </div>
                        <div class="col-12">
                            <h6 class="text-warning">${summary.riskLevel || 'Low'}</h6>
                            <small class="text-muted">Risk Level</small>
                        </div>
                    </div>
                `;
            }
        }

        function renderPredictionTimeline(timeline) {
            const container = document.getElementById('predictionTimeline');
            if (!timeline || timeline.length === 0) {
                container.innerHTML = '<p class="text-muted">No prediction timeline available.</p>';
                return;
            }

            let html = '';
            timeline.forEach(item => {
                const timelineClass = getForecastClass(item.type);

                html += `
                    <div class="forecast-timeline ${timelineClass}">
                        <h6 class="mb-1">${item.period}</h6>
                        <p class="mb-1 text-muted small">${item.prediction}</p>
                        <small class="text-muted">Confidence: ${item.confidence}%</small>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function getForecastClass(type) {
            switch (type?.toLowerCase()) {
                case 'positive': return 'forecast-positive';
                case 'warning': return 'forecast-warning';
                case 'critical': return 'forecast-critical';
                default: return '';
            }
        }

        // Event handlers
        function changeDepartment() {
            currentDepartmentId = document.getElementById('departmentSelect').value;
            loadPredictiveData();
        }

        function changeForecastHorizon() {
            currentForecastHorizon = document.getElementById('forecastHorizonSelect').value;
            loadPredictiveData();
        }

        function changeAnalysisType() {
            currentAnalysisType = document.getElementById('analysisTypeSelect').value;
            loadPredictiveData();
        }

        function changeConfidenceLevel() {
            currentConfidenceLevel = document.getElementById('confidenceLevel').value;
            loadPredictiveData();
        }

        function refreshPredictiveData() {
            loadPredictiveData();
        }

        function exportPredictions() {
            window.print();
        }

        function configurePredictions() {
            alert('Prediction configuration functionality would open a settings panel');
        }

        // Load remaining functions (stubs for now)
        function loadTrendAnalysis() {
            // Implementation for trend analysis
        }

        function loadEarlyWarnings() {
            // Implementation for early warnings
        }

        function loadAnomalyDetection() {
            // Implementation for anomaly detection
        }

        function loadScenarioPlanning() {
            // Implementation for scenario planning
        }

        function loadPredictiveInsights() {
            // Implementation for predictive insights
        }

        })(); // Close IIFE
    </script>
</body>
</html>
