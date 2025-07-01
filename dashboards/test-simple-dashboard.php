<?php
include_once("../config/config_mysqli.php");
include_once("../admin/models/config.php");
include_once("../functions/functions.php");

// Get parameters
$departmentId = $_GET['departmentId'] ?? 'org1';
$objectPeriod = $_GET['objectPeriod'] ?? 'months';
$objectDate = $_GET['objectDate'] ?? date("Y-m");

// Get department information
$deptQuery = mysqli_query($connect, "SELECT id, name FROM organization WHERE id = '$departmentId'");
$deptInfo = mysqli_fetch_assoc($deptQuery);
$departmentName = $deptInfo['name'] ?? 'Unknown Department';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Dashboard Test - <?php echo htmlspecialchars($departmentName); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="/analytics.local/css/myBootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
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
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <h1 class="mb-4">
            <i class="fas fa-building me-3"></i>
            <?php echo htmlspecialchars($departmentName); ?> Dashboard Test
        </h1>
        
        <!-- Debug Info -->
        <div class="alert alert-info">
            <strong>Debug Info:</strong><br>
            Department ID: <?php echo htmlspecialchars($departmentId); ?><br>
            Object Period: <?php echo htmlspecialchars($objectPeriod); ?><br>
            Object Date: <?php echo htmlspecialchars($objectDate); ?>
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
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="dashboardTabContent">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="row">
                    <!-- Key Metrics Cards -->
                    <div class="col-md-3">
                        <div class="metric-card">
                            <div class="metric-value" id="overallScore">Loading...</div>
                            <div class="metric-label">Overall Department Score</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-card">
                            <div class="metric-value" id="teamSize">Loading...</div>
                            <div class="metric-label">Team Members</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-card">
                            <div class="metric-value" id="activeInitiatives">Loading...</div>
                            <div class="metric-label">Active Initiatives</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-card">
                            <div class="metric-value" id="completionRate">Loading...</div>
                            <div class="metric-label">Completion Rate</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>API Response Debug</h5>
                            </div>
                            <div class="card-body">
                                <div id="apiDebug"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Performance Tab -->
            <div class="tab-pane fade" id="team" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Team Performance Data</h5>
                            </div>
                            <div class="card-body">
                                <div id="teamPerformanceList">Loading team data...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="/analytics.local/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Dashboard parameters
        const departmentId = '<?php echo $departmentId; ?>';
        const objectPeriod = '<?php echo $objectPeriod; ?>';
        const objectDate = '<?php echo $objectDate; ?>';

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dashboard initializing with:', {
                departmentId: departmentId,
                objectPeriod: objectPeriod,
                objectDate: objectDate
            });
            
            loadDashboardData();
        });

        // Load dashboard data
        function loadDashboardData() {
            loadOverviewMetrics();
            loadTeamPerformance();
        }

        // Load overview metrics
        function loadOverviewMetrics() {
            console.log('Loading overview metrics...');
            
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
                
                // Update UI
                document.getElementById('overallScore').textContent = data.overallScore || '--';
                document.getElementById('teamSize').textContent = data.teamSize || '--';
                document.getElementById('activeInitiatives').textContent = data.activeInitiatives || '--';
                document.getElementById('completionRate').textContent = (data.completionRate || '--') + '%';
                
                // Show debug info
                document.getElementById('apiDebug').innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;
            })
            .catch(error => {
                console.error('Error loading overview metrics:', error);
                document.getElementById('overallScore').textContent = 'Error';
                document.getElementById('teamSize').textContent = 'Error';
                document.getElementById('activeInitiatives').textContent = 'Error';
                document.getElementById('completionRate').textContent = 'Error';
                document.getElementById('apiDebug').innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
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
            })
            .catch(error => {
                console.error('Error loading team performance:', error);
                document.getElementById('teamPerformanceList').innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            });
        }

        // Render team performance list
        function renderTeamPerformanceList(teamList) {
            const container = document.getElementById('teamPerformanceList');
            if (!teamList || teamList.length === 0) {
                container.innerHTML = '<p class="text-muted">No team data available.</p>';
                return;
            }

            let html = '<div class="list-group">';
            teamList.forEach(member => {
                html += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${member.name}</h6>
                                <p class="mb-1 text-muted">${member.title}</p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary">${member.score}%</span>
                                <small class="text-muted d-block">${member.initiatives} initiatives</small>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';

            container.innerHTML = html;
        }
    </script>
</body>
</html>
