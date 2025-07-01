<?php
include_once("../config/config_mysqli.php");
include_once("../admin/models/config.php");

// Check if user is logged in
if (!isset($loggedInUser)) {
    header("Location: ../index.php");
    exit;
}

// Get user's department
$userDepartment = 'org1'; // Default
if (isset($loggedInUser->department)) {
    $userDepartment = $loggedInUser->department;
}

// Get list of departments for selection
$deptQuery = mysqli_query($connect, "
    SELECT id, name 
    FROM organization 
    WHERE id != 'org0' 
    AND showInTree = 'Yes' 
    ORDER BY name
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboards</title>
    
    <!-- Bootstrap CSS -->
    <link href="../css/myBootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .dashboard-card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease-in-out;
        }
        
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .dashboard-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .header-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="header-gradient">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-chart-line me-3"></i>
                        Analytics Dashboards
                    </h1>
                    <p class="mb-0 opacity-75">
                        Comprehensive performance analytics and insights for department management
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group" role="group">
                        <a href="../" class="btn btn-light btn-sm">
                            <i class="fas fa-home me-1"></i> Home
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Selection -->
        <div class="row">
            <!-- Department Performance Dashboard -->
            <div class="col-md-6 col-lg-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="dashboard-icon text-primary">
                            <i class="fas fa-building"></i>
                        </div>
                        <h5 class="card-title">Department Performance</h5>
                        <p class="card-text">
                            Comprehensive department analytics including team productivity, 
                            goal achievement, and resource utilization.
                        </p>
                        <div class="mb-3">
                            <label for="deptSelect" class="form-label">Select Department:</label>
                            <select class="form-select" id="deptSelect">
                                <?php while ($dept = mysqli_fetch_assoc($deptQuery)): ?>
                                    <option value="<?php echo $dept['id']; ?>" 
                                            <?php echo $dept['id'] == $userDepartment ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button class="btn btn-primary" onclick="openDepartmentDashboard()">
                            <i class="fas fa-chart-line me-2"></i>Open Dashboard
                        </button>
                    </div>
                </div>
            </div>

            <!-- Executive Summary -->
            <div class="col-md-6 col-lg-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="dashboard-icon text-success">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="card-title">Executive Summary</h5>
                        <p class="card-text">
                            High-level overview of organizational performance with 
                            staff summaries and key metrics.
                        </p>
                        <a href="executive-summary.php" class="btn btn-success">
                            <i class="fas fa-eye me-2"></i>View Summary
                        </a>
                    </div>
                </div>
            </div>

            <!-- Individual Dashboard -->
            <div class="col-md-6 col-lg-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="dashboard-icon text-info">
                            <i class="fas fa-user"></i>
                        </div>
                        <h5 class="card-title">Individual Performance</h5>
                        <p class="card-text">
                            Personal performance dashboard with KPIs, initiatives, 
                            and development plans.
                        </p>
                        <a href="indDashboard.php" class="btn btn-info">
                            <i class="fas fa-user-chart me-2"></i>My Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Reports -->
            <div class="col-md-6 col-lg-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="dashboard-icon text-warning">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h5 class="card-title">Reports</h5>
                        <p class="card-text">
                            Generate detailed reports for departments, scorecards, 
                            and performance analysis.
                        </p>
                        <a href="../reports/report.php" class="btn btn-warning">
                            <i class="fas fa-chart-bar me-2"></i>View Reports
                        </a>
                    </div>
                </div>
            </div>

            <!-- Organizational Chart -->
            <div class="col-md-6 col-lg-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="dashboard-icon text-secondary">
                            <i class="fas fa-sitemap"></i>
                        </div>
                        <h5 class="card-title">Organizational Chart</h5>
                        <p class="card-text">
                            Interactive organizational structure with performance 
                            indicators and staff details.
                        </p>
                        <a href="orgChart/index.php" class="btn btn-secondary">
                            <i class="fas fa-sitemap me-2"></i>View Chart
                        </a>
                    </div>
                </div>
            </div>

            <!-- Coming Soon -->
            <div class="col-md-6 col-lg-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="dashboard-icon text-muted">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h5 class="card-title">Advanced Analytics</h5>
                        <p class="card-text">
                            Predictive analytics, trend analysis, and advanced 
                            performance insights. Coming soon!
                        </p>
                        <button class="btn btn-outline-secondary" disabled>
                            <i class="fas fa-clock me-2"></i>Coming Soon
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tachometer-alt me-2"></i>Quick Statistics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h3 class="text-primary" id="totalDepartments">--</h3>
                                <small class="text-muted">Total Departments</small>
                            </div>
                            <div class="col-md-3">
                                <h3 class="text-success" id="totalStaff">--</h3>
                                <small class="text-muted">Total Staff</small>
                            </div>
                            <div class="col-md-3">
                                <h3 class="text-info" id="activeInitiatives">--</h3>
                                <small class="text-muted">Active Initiatives</small>
                            </div>
                            <div class="col-md-3">
                                <h3 class="text-warning" id="avgPerformance">--</h3>
                                <small class="text-muted">Avg Performance</small>
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
        // Open department dashboard
        function openDepartmentDashboard() {
            const selectedDept = document.getElementById('deptSelect').value;
            window.open(`department-performance-dashboard.php?departmentId=${selectedDept}`, '_blank');
        }
        
        // Load quick statistics
        document.addEventListener('DOMContentLoaded', function() {
            loadQuickStats();
        });
        
        function loadQuickStats() {
            fetch('get-quick-stats.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalDepartments').textContent = data.totalDepartments || '--';
                    document.getElementById('totalStaff').textContent = data.totalStaff || '--';
                    document.getElementById('activeInitiatives').textContent = data.activeInitiatives || '--';
                    document.getElementById('avgPerformance').textContent = (data.avgPerformance || '--') + '%';
                })
                .catch(error => {
                    console.error('Error loading quick stats:', error);
                });
        }
    </script>
</body>
</html>
