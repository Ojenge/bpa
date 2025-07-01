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
@$comparisonType = $_POST['comparisonType'] ?? $_GET['comparisonType'] ?? 'current';

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
    <title>Comparative Department Analysis - <?php echo htmlspecialchars($departmentName); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="../css/myBootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="../js/chart.min.js"></script>
    <!-- Custom CSS -->
    <link href="../css/dashboardTables.css" rel="stylesheet">
    
    <style>
        .comparison-card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease-in-out;
        }
        
        .comparison-card:hover {
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
        
        .comparison-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
        
        .ranking-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s ease;
        }
        
        .ranking-item:hover {
            background-color: #f8f9fa;
        }
        
        .ranking-item:last-child {
            border-bottom: none;
        }
        
        .ranking-position {
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
        
        .rank-1 { background: linear-gradient(135deg, #ffd700, #ffed4e); color: #333; }
        .rank-2 { background: linear-gradient(135deg, #c0c0c0, #e8e8e8); color: #333; }
        .rank-3 { background: linear-gradient(135deg, #cd7f32, #daa520); color: white; }
        .rank-other { background: linear-gradient(135deg, #6c757d, #adb5bd); color: white; }
        .rank-current { background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: 3px solid #ffc107; }
        
        .department-info {
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
        
        .benchmark-bar {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 0.5rem 0;
            position: relative;
        }
        
        .benchmark-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        
        .benchmark-marker {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 3px;
            background-color: #dc3545;
            z-index: 10;
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
        
        .comparison-selector {
            background: white;
            border-radius: 0.5rem;
            padding: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1rem;
        }
        
        .metric-comparison {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .metric-comparison:last-child {
            border-bottom: none;
        }
        
        .metric-name {
            font-weight: 500;
            flex-grow: 1;
        }
        
        .metric-values {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .value-current {
            font-weight: bold;
            color: #667eea;
        }
        
        .value-benchmark {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .value-difference {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }
        
        .diff-positive {
            background-color: #d4edda;
            color: #155724;
        }
        
        .diff-negative {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .diff-neutral {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .historical-chart {
            height: 250px;
        }
        
        .department-card {
            border-left: 4px solid #667eea;
            margin-bottom: 1rem;
        }
        
        .department-card.current-dept {
            border-left-color: #ffc107;
            background-color: #fff3cd;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="comparison-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-chart-bar me-3"></i>
                        Comparative Department Analysis
                    </h1>
                    <p class="mb-0 opacity-75">
                        <?php echo htmlspecialchars($departmentName); ?> - Benchmarking and performance comparison
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-light btn-sm" onclick="refreshComparison()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="exportComparison()">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparison Controls -->
        <div class="comparison-selector">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label for="departmentSelect" class="form-label">Department:</label>
                    <select class="form-select form-select-sm" id="departmentSelect" onchange="changeDepartment()">
                        <!-- Options will be loaded dynamically -->
                    </select>
                </div>

        <!-- Key Comparison Metrics -->
        <div class="row">
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="currentRank">--</div>
                    <div class="metric-label">Department Rank</div>
                    <div class="trend-indicator" id="rankTrend">
                        <i class="fas fa-trophy"></i> Out of <span id="totalDepartments">--</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="performanceGap">--</div>
                    <div class="metric-label">Gap to Leader</div>
                    <div class="trend-indicator" id="gapTrend">
                        <i class="fas fa-chart-line"></i> Performance difference
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="benchmarkScore">--</div>
                    <div class="metric-label">Benchmark Score</div>
                    <div class="trend-indicator" id="benchmarkTrend">
                        <i class="fas fa-target"></i> vs Industry average
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="improvementRate">--</div>
                    <div class="metric-label">Improvement Rate</div>
                    <div class="trend-indicator" id="improvementTrend">
                        <i class="fas fa-arrow-up"></i> Month over month
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-pills mb-4" id="comparisonTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="ranking-tab" data-bs-toggle="pill" data-bs-target="#ranking" type="button" role="tab">
                    <i class="fas fa-trophy me-2"></i>Department Ranking
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="benchmarking-tab" data-bs-toggle="pill" data-bs-target="#benchmarking" type="button" role="tab">
                    <i class="fas fa-chart-bar me-2"></i>Benchmarking
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="trends-tab" data-bs-toggle="pill" data-bs-target="#trends" type="button" role="tab">
                    <i class="fas fa-chart-line me-2"></i>Historical Trends
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="metrics-tab" data-bs-toggle="pill" data-bs-target="#metrics" type="button" role="tab">
                    <i class="fas fa-tachometer-alt me-2"></i>Metric Comparison
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="insights-tab" data-bs-toggle="pill" data-bs-target="#insights" type="button" role="tab">
                    <i class="fas fa-lightbulb me-2"></i>Insights
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="comparisonTabContent">
            <!-- Department Ranking Tab -->
            <div class="tab-pane fade show active" id="ranking" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card comparison-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-trophy me-2"></i>Department Performance Ranking
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="departmentRankingList">
                                    <!-- Ranking items will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card comparison-card">
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
                        <div class="card comparison-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Comparative Performance Chart
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="comparativePerformanceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Benchmarking Tab -->
            <div class="tab-pane fade" id="benchmarking" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card comparison-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-target me-2"></i>Performance vs Benchmarks
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="benchmarkComparison">
                                    <!-- Benchmark comparisons will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card comparison-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-radar me-2"></i>Multi-dimensional Analysis
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="radarComparisonChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card comparison-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-balance-scale me-2"></i>Detailed Metric Comparison
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="detailedMetricComparison">
                                    <!-- Detailed metrics will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historical Trends Tab -->
            <div class="tab-pane fade" id="trends" role="tabpanel">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card comparison-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Historical Performance Trends
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container historical-chart">
                                    <canvas id="historicalTrendsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card comparison-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-area me-2"></i>Trend Analysis
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="trendAnalysisChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card comparison-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-alt me-2"></i>Seasonal Patterns
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="seasonalPatterns">
                                    <!-- Seasonal analysis will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metric Comparison Tab -->
            <div class="tab-pane fade" id="metrics" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card comparison-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-tachometer-alt me-2"></i>Key Metrics Comparison
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="keyMetricsComparison">
                                    <!-- Key metrics comparison will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Insights Tab -->
            <div class="tab-pane fade" id="insights" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card comparison-card">
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
                        <div class="card comparison-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bullseye me-2"></i>Improvement Opportunities
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="improvementOpportunities">
                                    <!-- Improvement opportunities will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card comparison-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Predictive Analysis
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="predictiveAnalysisChart"></canvas>
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
        // Global variables
        let currentDepartmentId = '<?php echo $departmentId; ?>';
        let currentComparisonType = '<?php echo $comparisonType; ?>';
        let currentPeriod = '<?php echo $objectPeriod; ?>';
        let currentDate = '<?php echo $objectDate; ?>';
        let currentMetric = 'overall';

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadDepartmentOptions();
            loadComparisonData();
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

        // Load comparison data
        function loadComparisonData() {
            loadComparisonMetrics();
            loadDepartmentRanking();
            loadBenchmarkData();
            loadHistoricalTrends();
            loadMetricComparison();
            loadInsights();
        }

        // Load comparison metrics
        function loadComparisonMetrics() {
            fetch('get-comparison-metrics.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${currentDepartmentId}&comparisonType=${currentComparisonType}&period=${currentPeriod}&date=${currentDate}&metric=${currentMetric}`
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('currentRank').textContent = data.currentRank || '--';
                document.getElementById('totalDepartments').textContent = data.totalDepartments || '--';
                document.getElementById('performanceGap').textContent = (data.performanceGap || '--') + '%';
                document.getElementById('benchmarkScore').textContent = (data.benchmarkScore || '--') + '%';
                document.getElementById('improvementRate').textContent = (data.improvementRate || '--') + '%';

                // Update trend indicators
                updateTrendIndicators(data.trends);
            })
            .catch(error => {
                console.error('Error loading comparison metrics:', error);
            });
        }

        // Load department ranking
        function loadDepartmentRanking() {
            fetch('get-department-ranking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${currentDepartmentId}&comparisonType=${currentComparisonType}&period=${currentPeriod}&date=${currentDate}&metric=${currentMetric}`
            })
            .then(response => response.json())
            .then(data => {
                renderDepartmentRanking(data.ranking);
                updatePerformanceDistributionChart(data.distribution);
                updateComparativePerformanceChart(data.performance);
            })
            .catch(error => {
                console.error('Error loading department ranking:', error);
            });
        }

        // Render department ranking
        function renderDepartmentRanking(ranking) {
            const container = document.getElementById('departmentRankingList');
            if (!ranking || ranking.length === 0) {
                container.innerHTML = '<p class="text-muted">No ranking data available.</p>';
                return;
            }

            let html = '';
            ranking.forEach((dept, index) => {
                const position = index + 1;
                const rankClass = getRankClass(position, dept.isCurrentDept);
                const perfClass = getPerformanceClass(dept.score);
                const trendIcon = getTrendIcon(dept.trend);

                html += `
                    <div class="ranking-item">
                        <div class="ranking-position ${rankClass}">
                            ${position}
                        </div>
                        <div class="department-info">
                            <h6 class="mb-1">${dept.name} ${dept.isCurrentDept ? '<span class="badge bg-warning text-dark">Current</span>' : ''}</h6>
                            <div class="benchmark-bar">
                                <div class="benchmark-fill bg-primary" style="width: ${dept.score}%"></div>
                                <div class="benchmark-marker" style="left: 75%"></div>
                            </div>
                            <small class="text-muted">Score: ${dept.score}% | Staff: ${dept.staffCount} | Initiatives: ${dept.initiatives}</small>
                        </div>
                        <div class="performance-indicator ${perfClass}">
                            ${dept.score}%
                        </div>
                        <div class="trend-indicator ms-2">
                            ${trendIcon}
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Get rank class
        function getRankClass(position, isCurrentDept) {
            if (isCurrentDept) return 'rank-current';
            if (position === 1) return 'rank-1';
            if (position === 2) return 'rank-2';
            if (position === 3) return 'rank-3';
            return 'rank-other';
        }

        // Get performance class
        function getPerformanceClass(score) {
            if (score >= 90) return 'perf-excellent';
            if (score >= 75) return 'perf-good';
            if (score >= 60) return 'perf-average';
            return 'perf-poor';
        }

        // Get trend icon
        function getTrendIcon(trend) {
            if (trend > 0) return '<i class="fas fa-arrow-up trend-up"></i>';
            if (trend < 0) return '<i class="fas fa-arrow-down trend-down"></i>';
            return '<i class="fas fa-minus trend-stable"></i>';
        }

        // Load benchmark data
        function loadBenchmarkData() {
            // Implementation for loading benchmark comparison data
            const benchmarkContainer = document.getElementById('benchmarkComparison');
            const detailedContainer = document.getElementById('detailedMetricComparison');

            // Sample benchmark data
            benchmarkContainer.innerHTML = `
                <div class="metric-comparison">
                    <div class="metric-name">Overall Performance</div>
                    <div class="metric-values">
                        <span class="value-current">85%</span>
                        <span class="value-benchmark">78% avg</span>
                        <span class="value-difference diff-positive">+7%</span>
                    </div>
                </div>
                <div class="metric-comparison">
                    <div class="metric-name">Productivity Score</div>
                    <div class="metric-values">
                        <span class="value-current">82%</span>
                        <span class="value-benchmark">80% avg</span>
                        <span class="value-difference diff-positive">+2%</span>
                    </div>
                </div>
                <div class="metric-comparison">
                    <div class="metric-name">Goal Achievement</div>
                    <div class="metric-values">
                        <span class="value-current">75%</span>
                        <span class="value-benchmark">82% avg</span>
                        <span class="value-difference diff-negative">-7%</span>
                    </div>
                </div>
                <div class="metric-comparison">
                    <div class="metric-name">Efficiency Ratio</div>
                    <div class="metric-values">
                        <span class="value-current">88%</span>
                        <span class="value-benchmark">85% avg</span>
                        <span class="value-difference diff-positive">+3%</span>
                    </div>
                </div>
            `;

            // Sample detailed metrics
            detailedContainer.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Metric</th>
                                <th>Current Dept</th>
                                <th>Top Performer</th>
                                <th>Organization Avg</th>
                                <th>Industry Benchmark</th>
                                <th>Gap Analysis</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Revenue per Employee</td>
                                <td class="value-current">$125K</td>
                                <td>$145K</td>
                                <td>$118K</td>
                                <td>$130K</td>
                                <td><span class="diff-negative">-$5K vs benchmark</span></td>
                            </tr>
                            <tr>
                                <td>Customer Satisfaction</td>
                                <td class="value-current">4.2/5</td>
                                <td>4.5/5</td>
                                <td>4.1/5</td>
                                <td>4.0/5</td>
                                <td><span class="diff-positive">+0.2 vs benchmark</span></td>
                            </tr>
                            <tr>
                                <td>Process Efficiency</td>
                                <td class="value-current">88%</td>
                                <td>92%</td>
                                <td>85%</td>
                                <td>82%</td>
                                <td><span class="diff-positive">+6% vs benchmark</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;
        }

        // Load historical trends
        function loadHistoricalTrends() {
            // Implementation for loading historical trends
            const seasonalContainer = document.getElementById('seasonalPatterns');

            seasonalContainer.innerHTML = `
                <div class="row text-center">
                    <div class="col-3">
                        <h5 class="text-primary">Q1</h5>
                        <p class="mb-0">Strong Start</p>
                        <small class="text-muted">+12% growth</small>
                    </div>
                    <div class="col-3">
                        <h5 class="text-success">Q2</h5>
                        <p class="mb-0">Peak Performance</p>
                        <small class="text-muted">+18% growth</small>
                    </div>
                    <div class="col-3">
                        <h5 class="text-warning">Q3</h5>
                        <p class="mb-0">Seasonal Dip</p>
                        <small class="text-muted">-5% decline</small>
                    </div>
                    <div class="col-3">
                        <h5 class="text-info">Q4</h5>
                        <p class="mb-0">Recovery</p>
                        <small class="text-muted">+8% growth</small>
                    </div>
                </div>
            `;
        }

        // Load metric comparison
        function loadMetricComparison() {
            const container = document.getElementById('keyMetricsComparison');

            container.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card department-card">
                            <div class="card-body">
                                <h6 class="card-title">Productivity Metrics</h6>
                                <div class="metric-comparison">
                                    <div class="metric-name">Output per Employee</div>
                                    <div class="metric-values">
                                        <span class="value-current">125 units</span>
                                        <span class="value-benchmark">118 avg</span>
                                        <span class="value-difference diff-positive">+6%</span>
                                    </div>
                                </div>
                                <div class="metric-comparison">
                                    <div class="metric-name">Task Completion Rate</div>
                                    <div class="metric-values">
                                        <span class="value-current">92%</span>
                                        <span class="value-benchmark">88% avg</span>
                                        <span class="value-difference diff-positive">+4%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card department-card">
                            <div class="card-body">
                                <h6 class="card-title">Quality Metrics</h6>
                                <div class="metric-comparison">
                                    <div class="metric-name">Error Rate</div>
                                    <div class="metric-values">
                                        <span class="value-current">2.1%</span>
                                        <span class="value-benchmark">2.8% avg</span>
                                        <span class="value-difference diff-positive">-0.7%</span>
                                    </div>
                                </div>
                                <div class="metric-comparison">
                                    <div class="metric-name">Customer Satisfaction</div>
                                    <div class="metric-values">
                                        <span class="value-current">4.3/5</span>
                                        <span class="value-benchmark">4.1/5 avg</span>
                                        <span class="value-difference diff-positive">+0.2</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Load insights
        function loadInsights() {
            const insightsContainer = document.getElementById('performanceInsights');
            const opportunitiesContainer = document.getElementById('improvementOpportunities');

            insightsContainer.innerHTML = `
                <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 text-success">
                                    <i class="fas fa-arrow-up me-1"></i>Strength: Productivity
                                </h6>
                                <p class="mb-1 text-muted small">Department ranks #2 in productivity metrics</p>
                            </div>
                            <small class="text-muted">Top 15%</small>
                        </div>
                    </div>
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Area for Improvement: Goal Achievement
                                </h6>
                                <p class="mb-1 text-muted small">Below organization average by 7%</p>
                            </div>
                            <small class="text-muted">Bottom 30%</small>
                        </div>
                    </div>
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 text-info">
                                    <i class="fas fa-chart-line me-1"></i>Trend: Consistent Growth
                                </h6>
                                <p class="mb-1 text-muted small">3-month positive trend in key metrics</p>
                            </div>
                            <small class="text-muted">+12%</small>
                        </div>
                    </div>
                </div>
            `;

            opportunitiesContainer.innerHTML = `
                <div class="row">
                    <div class="col-12">
                        <div class="card border-primary">
                            <div class="card-body">
                                <h6 class="text-primary">
                                    <i class="fas fa-bullseye me-2"></i>Quick Win: Goal Setting Process
                                </h6>
                                <p class="card-text">Implement structured goal-setting framework to close 7% gap with top performers.</p>
                                <small class="text-muted">Estimated impact: +5-8% improvement</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="text-success">
                                    <i class="fas fa-users me-2"></i>Long-term: Staff Development
                                </h6>
                                <p class="card-text">Leverage high productivity to mentor other departments and share best practices.</p>
                                <small class="text-muted">Potential organization-wide impact</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Initialize charts
        function initializeCharts() {
            initPerformanceDistributionChart();
            initComparativePerformanceChart();
            initRadarComparisonChart();
            initHistoricalTrendsChart();
            initTrendAnalysisChart();
            initPredictiveAnalysisChart();
        }

        // Initialize performance distribution chart
        function initPerformanceDistributionChart() {
            const ctx = document.getElementById('performanceDistributionChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Excellent (90%+)', 'Good (75-89%)', 'Average (60-74%)', 'Poor (<60%)'],
                    datasets: [{
                        data: [2, 4, 3, 1],
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

        // Initialize comparative performance chart
        function initComparativePerformanceChart() {
            const ctx = document.getElementById('comparativePerformanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Finance', 'Marketing', 'Operations', 'HR', 'IT', 'Sales'],
                    datasets: [{
                        label: 'Performance Score',
                        data: [92, 85, 88, 78, 82, 90],
                        backgroundColor: function(context) {
                            const value = context.parsed.y;
                            if (value >= 90) return '#28a745';
                            if (value >= 75) return '#17a2b8';
                            if (value >= 60) return '#ffc107';
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
                            max: 100
                        }
                    }
                }
            });
        }

        // Initialize radar comparison chart
        function initRadarComparisonChart() {
            const ctx = document.getElementById('radarComparisonChart').getContext('2d');
            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: ['Productivity', 'Quality', 'Efficiency', 'Innovation', 'Collaboration', 'Goals'],
                    datasets: [{
                        label: 'Current Department',
                        data: [85, 78, 92, 75, 88, 82],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.2)'
                    }, {
                        label: 'Top Performer',
                        data: [92, 88, 95, 85, 90, 94],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.2)'
                    }, {
                        label: 'Organization Average',
                        data: [78, 75, 82, 70, 80, 76],
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.2)'
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

        // Initialize historical trends chart
        function initHistoricalTrendsChart() {
            const ctx = document.getElementById('historicalTrendsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Current Department',
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
                    }, {
                        label: 'Organization Average',
                        data: [72, 74, 76, 75, 78, 80],
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
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

        // Initialize trend analysis chart
        function initTrendAnalysisChart() {
            const ctx = document.getElementById('trendAnalysisChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Q1', 'Q2', 'Q3', 'Q4'],
                    datasets: [{
                        label: 'Performance Trend',
                        data: [78, 85, 82, 88],
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

        // Initialize predictive analysis chart
        function initPredictiveAnalysisChart() {
            const ctx = document.getElementById('predictiveAnalysisChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Current', 'Next Month', '2 Months', '3 Months'],
                    datasets: [{
                        label: 'Predicted Performance',
                        data: [88, 90, 92, 94],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderDash: [5, 5],
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Conservative Estimate',
                        data: [88, 87, 89, 91],
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
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

        // Update chart functions
        function updatePerformanceDistributionChart(distribution) {
            console.log('Updating performance distribution chart with:', distribution);
        }

        function updateComparativePerformanceChart(performance) {
            console.log('Updating comparative performance chart with:', performance);
        }

        // Update trend indicators
        function updateTrendIndicators(trends) {
            if (trends) {
                updateTrendElement('rankTrend', trends.rank);
                updateTrendElement('gapTrend', trends.gap);
                updateTrendElement('benchmarkTrend', trends.benchmark);
                updateTrendElement('improvementTrend', trends.improvement);
            }
        }

        // Update individual trend element
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

        // Event handlers
        function changeDepartment() {
            currentDepartmentId = document.getElementById('departmentSelect').value;
            loadComparisonData();
        }

        function changeComparisonType() {
            currentComparisonType = document.getElementById('comparisonTypeSelect').value;
            loadComparisonData();
        }

        function changePeriod() {
            currentPeriod = document.getElementById('periodSelect').value;
            loadComparisonData();
        }

        function changeMetric() {
            currentMetric = document.getElementById('metricSelect').value;
            loadComparisonData();
        }

        function refreshComparison() {
            loadComparisonData();
        }

        function exportComparison() {
            window.print();
        }
    </script>
</body>
</html>
