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
@$heatMapType = $_POST['heatMapType'] ?? $_GET['heatMapType'] ?? 'performance';

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
    <title>Performance Heat Maps - <?php echo htmlspecialchars($departmentName); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="css/myBootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="js/chart.min.js"></script>
    <!-- D3.js for advanced heat maps -->
    <script src="js/d3.v7.min.js" onerror="handleScriptError('D3.js')"></script>
    <!-- Custom CSS -->
    <link href="css/dashboardTables.css" rel="stylesheet">
    
    <style>
        .heatmap-card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease-in-out;
        }
        
        .heatmap-card:hover {
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
        
        .heatmap-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
        
        .performance-card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .efficiency-card {
            background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .productivity-card {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .heatmap-container {
            position: relative;
            height: 400px;
            margin: 1rem 0;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .nav-pills .nav-link.active {
            background-color: #667eea;
        }
        
        .heatmap-selector {
            background: white;
            border-radius: 0.5rem;
            padding: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1rem;
        }
        
        .heat-cell {
            border-radius: 4px;
            margin: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.8rem;
            color: white;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .heat-cell:hover {
            transform: scale(1.05);
            z-index: 10;
            position: relative;
        }
        
        .heat-excellent { background-color: #28a745; }
        .heat-good { background-color: #20c997; }
        .heat-average { background-color: #ffc107; }
        .heat-poor { background-color: #fd7e14; }
        .heat-critical { background-color: #dc3545; }
        
        .org-heatmap {
            display: grid;
            gap: 4px;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
        }
        
        .team-heatmap {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 8px;
            padding: 1rem;
        }
        
        .staff-heat-cell {
            height: 80px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .staff-heat-cell:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        
        .staff-heat-cell .name {
            font-weight: bold;
            font-size: 0.9rem;
            margin-bottom: 4px;
        }
        
        .staff-heat-cell .score {
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .staff-heat-cell .role {
            font-size: 0.7rem;
            opacity: 0.9;
        }
        
        .department-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            padding: 1rem;
        }
        
        .dept-heat-cell {
            height: 120px;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .dept-heat-cell:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 24px rgba(0,0,0,0.3);
        }
        
        .dept-heat-cell .dept-name {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }
        
        .dept-heat-cell .dept-score {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 4px;
        }
        
        .dept-heat-cell .dept-staff {
            font-size: 0.8rem;
            opacity: 0.9;
        }
        
        .legend {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin: 1rem 0;
            padding: 1rem;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
        
        .legend-label {
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .matrix-heatmap {
            overflow-x: auto;
            padding: 1rem;
        }
        
        .matrix-table {
            min-width: 600px;
            border-collapse: separate;
            border-spacing: 2px;
        }
        
        .matrix-cell {
            width: 60px;
            height: 40px;
            text-align: center;
            vertical-align: middle;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .matrix-cell:hover {
            transform: scale(1.1);
        }
        
        .matrix-header {
            background-color: #6c757d;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 8px;
        }
        
        .tooltip {
            position: absolute;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            pointer-events: none;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .performance-indicator {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 auto 0.5rem;
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
        
        .heatmap-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .control-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .control-label {
            font-size: 0.8rem;
            font-weight: 500;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="heatmap-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-th me-3"></i>
                        Performance Heat Maps
                    </h1>
                    <p class="mb-0 opacity-75">
                        <?php echo htmlspecialchars($departmentName); ?> - Visual performance analysis with color-coded metrics
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-light btn-sm" onclick="refreshHeatMaps()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="exportHeatMaps()">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="configureHeatMaps()">
                            <i class="fas fa-cog me-1"></i> Configure
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Heat Map Controls -->
        <div class="heatmap-selector">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label for="departmentSelect" class="form-label">Department:</label>
                    <select class="form-select form-select-sm" id="departmentSelect" onchange="changeDepartment()">
                        <!-- Options will be loaded dynamically -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="heatMapTypeSelect" class="form-label">Heat Map Type:</label>
                    <select class="form-select form-select-sm" id="heatMapTypeSelect" onchange="changeHeatMapType()">
                        <option value="performance">Performance Overview</option>
                        <option value="team">Team Performance</option>
                        <option value="departments">Department Comparison</option>
                        <option value="matrix">Performance Matrix</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="metricSelect" class="form-label">Primary Metric:</label>
                    <select class="form-select form-select-sm" id="metricSelect" onchange="changeMetric()">
                        <option value="overall">Overall Performance</option>
                        <option value="productivity">Productivity</option>
                        <option value="efficiency">Efficiency</option>
                        <option value="goals">Goal Achievement</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="periodSelect" class="form-label">Time Period:</label>
                    <select class="form-select form-select-sm" id="periodSelect" onchange="changePeriod()">
                        <option value="current">Current Period</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                        <option value="comparison">Period Comparison</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Key Heat Map Metrics -->
        <div class="row">
            <div class="col-md-3">
                <div class="performance-card">
                    <div class="metric-value" id="avgPerformance">--</div>
                    <div class="metric-label">Avg Performance</div>
                    <div class="trend-indicator" id="performanceTrend">
                        <i class="fas fa-chart-line"></i> Performance trend
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="efficiency-card">
                    <div class="metric-value" id="topPerformers">--</div>
                    <div class="metric-label">Top Performers</div>
                    <div class="trend-indicator" id="topPerformersTrend">
                        <i class="fas fa-trophy"></i> High achievers
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="productivity-card">
                    <div class="metric-value" id="needsAttention">--</div>
                    <div class="metric-label">Needs Attention</div>
                    <div class="trend-indicator" id="attentionTrend">
                        <i class="fas fa-exclamation-triangle"></i> Low performers
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="performanceRange">--</div>
                    <div class="metric-label">Performance Range</div>
                    <div class="trend-indicator" id="rangeTrend">
                        <i class="fas fa-arrows-alt-h"></i> Score spread
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Legend -->
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color heat-excellent"></div>
                <span class="legend-label">Excellent (90-100%)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color heat-good"></div>
                <span class="legend-label">Good (75-89%)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color heat-average"></div>
                <span class="legend-label">Average (60-74%)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color heat-poor"></div>
                <span class="legend-label">Poor (40-59%)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color heat-critical"></div>
                <span class="legend-label">Critical (<40%)</span>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-pills mb-4" id="heatMapTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                    <i class="fas fa-th me-2"></i>Performance Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="team-tab" data-bs-toggle="pill" data-bs-target="#team" type="button" role="tab">
                    <i class="fas fa-users me-2"></i>Team Heat Map
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="departments-tab" data-bs-toggle="pill" data-bs-target="#departments" type="button" role="tab">
                    <i class="fas fa-building me-2"></i>Department Comparison
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="matrix-tab" data-bs-toggle="pill" data-bs-target="#matrix" type="button" role="tab">
                    <i class="fas fa-table me-2"></i>Performance Matrix
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="trends-tab" data-bs-toggle="pill" data-bs-target="#trends" type="button" role="tab">
                    <i class="fas fa-chart-line me-2"></i>Trend Heat Maps
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="insights-tab" data-bs-toggle="pill" data-bs-target="#insights" type="button" role="tab">
                    <i class="fas fa-lightbulb me-2"></i>Heat Map Insights
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="heatMapTabContent">
            <!-- Performance Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="row">
                    <!-- Main Heat Map -->
                    <div class="col-md-8">
                        <div class="card heatmap-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-th me-2"></i>Performance Heat Map
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="heatmap-container" id="mainHeatMap">
                                    <!-- Main heat map will be rendered here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Distribution -->
                    <div class="col-md-4">
                        <div class="card heatmap-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>Performance Distribution
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="distributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Performance Summary -->
                    <div class="col-md-6">
                        <div class="card heatmap-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Performance Summary
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="performanceSummary">
                                    <!-- Performance summary will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hot Spots Analysis -->
                    <div class="col-md-6">
                        <div class="card heatmap-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-fire me-2"></i>Performance Hot Spots
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="hotSpotsAnalysis">
                                    <!-- Hot spots analysis will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Heat Map Tab -->
            <div class="tab-pane fade" id="team" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card heatmap-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users me-2"></i>Team Performance Heat Map
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="team-heatmap" id="teamHeatMap">
                                    <!-- Team heat map will be rendered here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card heatmap-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Team Performance Trends
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="teamTrendsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card heatmap-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users-cog me-2"></i>Team Analytics
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="teamAnalytics">
                                    <!-- Team analytics will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Comparison Tab -->
            <div class="tab-pane fade" id="departments" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card heatmap-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-building me-2"></i>Department Performance Comparison
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="department-grid" id="departmentHeatMap">
                                    <!-- Department heat map will be rendered here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Matrix Tab -->
            <div class="tab-pane fade" id="matrix" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card heatmap-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-table me-2"></i>Performance Matrix
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="matrix-heatmap" id="matrixHeatMap">
                                    <!-- Matrix heat map will be rendered here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trend Heat Maps Tab -->
            <div class="tab-pane fade" id="trends" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card heatmap-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Performance Trends
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="heatmap-container" id="trendHeatMap">
                                    <!-- Trend heat map will be rendered here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card heatmap-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-arrows-alt me-2"></i>Performance Changes
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="changesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Heat Map Insights Tab -->
            <div class="tab-pane fade" id="insights" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card heatmap-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>Performance Insights
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="performanceInsights">
                                    <!-- Performance insights will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card heatmap-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bullseye me-2"></i>Action Recommendations
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="actionRecommendations">
                                    <!-- Action recommendations will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card heatmap-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-area me-2"></i>Performance Correlation Analysis
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="correlationChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tooltip for heat map interactions -->
    <div id="heatMapTooltip" class="tooltip"></div>

    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>

    <script>
        // Error handler for failed script loads
        function handleScriptError(scriptName) {
            console.error(`Failed to load ${scriptName}. Some features may not work correctly.`);
            // Show user-friendly error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-warning alert-dismissible fade show';
            errorDiv.innerHTML = `
                <strong>Notice:</strong> ${scriptName} failed to load. Some advanced features may not be available.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.insertBefore(errorDiv, document.body.firstChild);
        }

        // Wrap in IIFE to avoid global variable conflicts
        (function() {
            // Local variables for this dashboard
            let currentDepartmentId = '<?php echo $departmentId; ?>';
            let currentHeatMapType = '<?php echo $heatMapType; ?>';
            let currentPeriod = '<?php echo $objectPeriod; ?>';
            let currentDate = '<?php echo $objectDate; ?>';
            let currentMetric = 'overall';

            // Initialize dashboard
            document.addEventListener('DOMContentLoaded', function() {
            loadDepartmentOptions();
            loadHeatMapData();
            initializeCharts();
            setupTooltips();
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

        // Load heat map data
        function loadHeatMapData() {
            loadHeatMapMetrics();
            loadPerformanceOverview();
            loadTeamHeatMap();
            loadDepartmentComparison();
            loadPerformanceMatrix();
            loadTrendAnalysis();
            loadHeatMapInsights();
        }

        // Load heat map metrics
        function loadHeatMapMetrics() {
            fetch('get-heatmap-metrics.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${currentDepartmentId}&heatMapType=${currentHeatMapType}&metric=${currentMetric}&period=${currentPeriod}&date=${currentDate}`
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('avgPerformance').textContent = (data.avgPerformance || '--') + '%';
                document.getElementById('topPerformers').textContent = data.topPerformers || '--';
                document.getElementById('needsAttention').textContent = data.needsAttention || '--';
                document.getElementById('performanceRange').textContent = (data.performanceRange || '--') + '%';

                // Update trend indicators
                updateTrendIndicators(data.trends);
            })
            .catch(error => {
                console.error('Error loading heat map metrics:', error);
            });
        }

        // Load performance overview
        function loadPerformanceOverview() {
            fetch('get-heatmap-data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${currentDepartmentId}&type=overview&metric=${currentMetric}&period=${currentPeriod}&date=${currentDate}`
            })
            .then(response => response.json())
            .then(data => {
                renderMainHeatMap(data.heatMapData);
                updateDistributionChart(data.distribution);
                renderPerformanceSummary(data.summary);
                renderHotSpotsAnalysis(data.hotSpots);
            })
            .catch(error => {
                console.error('Error loading performance overview:', error);
            });
        }

        // Render main heat map
        function renderMainHeatMap(data) {
            const container = document.getElementById('mainHeatMap');
            if (!data || data.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">No heat map data available.</p>';
                return;
            }

            // Clear existing content
            container.innerHTML = '';

            // Create D3 heat map
            const margin = {top: 20, right: 20, bottom: 30, left: 40};
            const width = container.offsetWidth - margin.left - margin.right;
            const height = 350 - margin.top - margin.bottom;

            const svg = d3.select(container)
                .append('svg')
                .attr('width', width + margin.left + margin.right)
                .attr('height', height + margin.top + margin.bottom)
                .append('g')
                .attr('transform', `translate(${margin.left},${margin.top})`);

            // Create color scale
            const colorScale = d3.scaleSequential(d3.interpolateRdYlGn)
                .domain([0, 100]);

            // Calculate grid dimensions
            const cols = Math.ceil(Math.sqrt(data.length));
            const rows = Math.ceil(data.length / cols);
            const cellWidth = width / cols;
            const cellHeight = height / rows;

            // Create heat map cells
            svg.selectAll('.heat-cell')
                .data(data)
                .enter()
                .append('rect')
                .attr('class', 'heat-cell')
                .attr('x', (d, i) => (i % cols) * cellWidth)
                .attr('y', (d, i) => Math.floor(i / cols) * cellHeight)
                .attr('width', cellWidth - 2)
                .attr('height', cellHeight - 2)
                .attr('fill', d => colorScale(d.score))
                .attr('stroke', '#fff')
                .attr('stroke-width', 1)
                .on('mouseover', function(event, d) {
                    showTooltip(event, d);
                })
                .on('mouseout', hideTooltip)
                .on('click', function(event, d) {
                    showDetailModal(d);
                });

            // Add text labels
            svg.selectAll('.heat-label')
                .data(data)
                .enter()
                .append('text')
                .attr('class', 'heat-label')
                .attr('x', (d, i) => (i % cols) * cellWidth + cellWidth / 2)
                .attr('y', (d, i) => Math.floor(i / cols) * cellHeight + cellHeight / 2)
                .attr('text-anchor', 'middle')
                .attr('dominant-baseline', 'middle')
                .attr('fill', d => d.score < 50 ? 'white' : 'black')
                .attr('font-size', '12px')
                .attr('font-weight', 'bold')
                .text(d => d.name)
                .style('pointer-events', 'none');
        }

        // Load team heat map
        function loadTeamHeatMap() {
            fetch('get-heatmap-data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${currentDepartmentId}&type=team&metric=${currentMetric}&period=${currentPeriod}&date=${currentDate}`
            })
            .then(response => response.json())
            .then(data => {
                renderTeamHeatMap(data.teamData);
                updateTeamTrendsChart(data.trends);
                renderTeamAnalytics(data.analytics);
            })
            .catch(error => {
                console.error('Error loading team heat map:', error);
            });
        }

        // Render team heat map
        function renderTeamHeatMap(data) {
            const container = document.getElementById('teamHeatMap');
            if (!data || data.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">No team data available.</p>';
                return;
            }

            let html = '';
            data.forEach(member => {
                const heatClass = getHeatClass(member.score);

                html += `
                    <div class="staff-heat-cell ${heatClass}" onclick="showStaffDetail('${member.id}')">
                        <div class="name">${member.name}</div>
                        <div class="score">${Math.round(member.score)}%</div>
                        <div class="role">${member.role}</div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Load department comparison
        function loadDepartmentComparison() {
            fetch('get-heatmap-data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${currentDepartmentId}&type=departments&metric=${currentMetric}&period=${currentPeriod}&date=${currentDate}`
            })
            .then(response => response.json())
            .then(data => {
                renderDepartmentHeatMap(data.departments);
            })
            .catch(error => {
                console.error('Error loading department comparison:', error);
            });
        }

        // Render department heat map
        function renderDepartmentHeatMap(data) {
            const container = document.getElementById('departmentHeatMap');
            if (!data || data.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">No department data available.</p>';
                return;
            }

            let html = '';
            data.forEach(dept => {
                const heatClass = getHeatClass(dept.score);

                html += `
                    <div class="dept-heat-cell ${heatClass}" onclick="showDepartmentDetail('${dept.id}')">
                        <div class="dept-name">${dept.name}</div>
                        <div class="dept-score">${Math.round(dept.score)}%</div>
                        <div class="dept-staff">${dept.staffCount} staff</div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Initialize charts
        function initializeCharts() {
            initDistributionChart();
            initTeamTrendsChart();
            initChangesChart();
            initCorrelationChart();
        }

        // Initialize distribution chart
        function initDistributionChart() {
            const ctx = document.getElementById('distributionChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Excellent', 'Good', 'Average', 'Poor', 'Critical'],
                    datasets: [{
                        data: [25, 35, 25, 10, 5],
                        backgroundColor: ['#28a745', '#20c997', '#ffc107', '#fd7e14', '#dc3545']
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

        // Initialize remaining charts
        function initTeamTrendsChart() {
            const ctx = document.getElementById('teamTrendsChart').getContext('2d');
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

        function initChangesChart() {
            const ctx = document.getElementById('changesChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Improved', 'Stable', 'Declined'],
                    datasets: [{
                        label: 'Performance Changes',
                        data: [12, 8, 3],
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545']
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

        function initCorrelationChart() {
            const ctx = document.getElementById('correlationChart').getContext('2d');
            new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'Performance vs Efficiency',
                        data: [
                            {x: 85, y: 90}, {x: 78, y: 82}, {x: 92, y: 95},
                            {x: 65, y: 70}, {x: 88, y: 85}, {x: 75, y: 78}
                        ],
                        backgroundColor: '#667eea'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Performance Score'
                            },
                            beginAtZero: true,
                            max: 100
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Efficiency Score'
                            },
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }

        // Utility functions
        function getHeatClass(score) {
            if (score >= 90) return 'heat-excellent';
            if (score >= 75) return 'heat-good';
            if (score >= 60) return 'heat-average';
            if (score >= 40) return 'heat-poor';
            return 'heat-critical';
        }

        function updateTrendIndicators(trends) {
            if (trends) {
                updateTrendElement('performanceTrend', trends.performance);
                updateTrendElement('topPerformersTrend', trends.topPerformers);
                updateTrendElement('attentionTrend', trends.attention);
                updateTrendElement('rangeTrend', trends.range);
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

        // Tooltip functions
        function setupTooltips() {
            const tooltip = document.getElementById('heatMapTooltip');

            // Setup tooltip styles
            tooltip.style.position = 'absolute';
            tooltip.style.pointerEvents = 'none';
            tooltip.style.opacity = '0';
        }

        function showTooltip(event, data) {
            const tooltip = document.getElementById('heatMapTooltip');

            tooltip.innerHTML = `
                <strong>${data.name}</strong><br>
                Score: ${Math.round(data.score)}%<br>
                ${data.details || ''}
            `;

            tooltip.style.left = (event.pageX + 10) + 'px';
            tooltip.style.top = (event.pageY - 10) + 'px';
            tooltip.style.opacity = '1';
        }

        function hideTooltip() {
            const tooltip = document.getElementById('heatMapTooltip');
            tooltip.style.opacity = '0';
        }

        // Update functions for charts
        function updateDistributionChart(data) {
            console.log('Updating distribution chart with:', data);
        }

        function updateTeamTrendsChart(data) {
            console.log('Updating team trends chart with:', data);
        }

        function renderPerformanceSummary(summary) {
            const container = document.getElementById('performanceSummary');
            if (summary) {
                container.innerHTML = `
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h6 class="text-primary">${summary.avgScore || 0}%</h6>
                            <small class="text-muted">Average Score</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h6 class="text-success">${summary.topPerformers || 0}</h6>
                            <small class="text-muted">Top Performers</small>
                        </div>
                        <div class="col-6">
                            <h6 class="text-warning">${summary.needsImprovement || 0}</h6>
                            <small class="text-muted">Needs Improvement</small>
                        </div>
                        <div class="col-6">
                            <h6 class="text-info">${summary.scoreRange || 0}%</h6>
                            <small class="text-muted">Score Range</small>
                        </div>
                    </div>
                `;
            }
        }

        function renderHotSpotsAnalysis(hotSpots) {
            const container = document.getElementById('hotSpotsAnalysis');
            if (!hotSpots || hotSpots.length === 0) {
                container.innerHTML = '<p class="text-muted">No hot spots identified.</p>';
                return;
            }

            let html = '';
            hotSpots.forEach(spot => {
                const typeClass = spot.type === 'high' ? 'text-success' : 'text-danger';
                const icon = spot.type === 'high' ? 'fa-fire' : 'fa-exclamation-triangle';

                html += `
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas ${icon} ${typeClass} me-2"></i>
                        <div>
                            <strong>${spot.area}</strong><br>
                            <small class="text-muted">${spot.description}</small>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function renderTeamAnalytics(analytics) {
            const container = document.getElementById('teamAnalytics');
            if (analytics) {
                container.innerHTML = `
                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <h6 class="text-primary">${analytics.teamSize || 0}</h6>
                            <small class="text-muted">Team Members</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h6 class="text-success">${analytics.avgExperience || 0} years</h6>
                            <small class="text-muted">Avg Experience</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h6 class="text-info">${analytics.projectLoad || 0}%</h6>
                            <small class="text-muted">Project Load</small>
                        </div>
                        <div class="col-12">
                            <h6 class="text-warning">${analytics.skillGap || 0}%</h6>
                            <small class="text-muted">Skill Gap</small>
                        </div>
                    </div>
                `;
            }
        }

        // Event handlers
        function changeDepartment() {
            currentDepartmentId = document.getElementById('departmentSelect').value;
            loadHeatMapData();
        }

        function changeHeatMapType() {
            currentHeatMapType = document.getElementById('heatMapTypeSelect').value;
            loadHeatMapData();
        }

        function changeMetric() {
            currentMetric = document.getElementById('metricSelect').value;
            loadHeatMapData();
        }

        function changePeriod() {
            currentPeriod = document.getElementById('periodSelect').value;
            loadHeatMapData();
        }

        function refreshHeatMaps() {
            loadHeatMapData();
        }

        function exportHeatMaps() {
            window.print();
        }

        function configureHeatMaps() {
            alert('Heat map configuration functionality would open a settings panel');
        }

        function showStaffDetail(staffId) {
            alert(`Staff detail for ID: ${staffId} would open a detailed view`);
        }

        function showDepartmentDetail(deptId) {
            alert(`Department detail for ID: ${deptId} would open a detailed view`);
        }

        function showDetailModal(data) {
            alert(`Detail modal for ${data.name} with score ${data.score}% would open`);
        }

        // Load remaining functions (stubs for now)
        function loadPerformanceMatrix() {
            // Implementation for performance matrix
        }

        function loadTrendAnalysis() {
            // Implementation for trend analysis
        }

        function loadHeatMapInsights() {
            // Implementation for heat map insights
        }

        })(); // Close IIFE
    </script>
</body>
</html>
