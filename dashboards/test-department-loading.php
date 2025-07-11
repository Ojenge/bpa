<?php
include_once("../config/config_mysqli.php");
include_once("../admin/models/config.php");
include_once("../functions/functions.php");

date_default_timezone_set('Africa/Nairobi');

// Get parameters
@$departmentId = $_POST['departmentId'] ?? $_GET['departmentId'] ?? 'org1';

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
    <title>Test Department Loading</title>
    
    <!-- Bootstrap CSS -->
    <link href="/bpa/css/myBootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .test-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .test-success {
            color: #28a745;
            font-weight: bold;
        }
        
        .test-error {
            color: #dc3545;
            font-weight: bold;
        }
        
        .test-info {
            color: #17a2b8;
            font-weight: bold;
        }
        
        .console-output {
            background: #000;
            color: #00ff00;
            padding: 1rem;
            border-radius: 0.25rem;
            font-family: monospace;
            height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <h1 class="mb-4">
            <i class="fas fa-test-tube me-3"></i>
            Test Department Loading
        </h1>
        
        <!-- Test Information -->
        <div class="test-section">
            <h4>Test Information</h4>
            <div id="testInfo">
                <p><strong>Department ID:</strong> <span class="test-info"><?php echo htmlspecialchars($departmentId); ?></span></p>
                <p><strong>Department Name:</strong> <span class="test-info"><?php echo htmlspecialchars($departmentName); ?></span></p>
                <p><strong>Current Time:</strong> <span class="test-info"><?php echo date('Y-m-d H:i:s'); ?></span></p>
            </div>
        </div>
        
        <!-- Department Select Test -->
        <div class="test-section">
            <h4>Department Select Test</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="departmentSelect" class="form-label">Department:</label>
                    <select class="form-select form-select-sm" id="departmentSelect" onchange="changeDepartment()">
                        <!-- Options will be loaded dynamically -->
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="mt-4">
                        <button class="btn btn-primary" onclick="testLoadDepartments()">
                            <i class="fas fa-sync-alt me-1"></i> Load Departments
                        </button>
                        <button class="btn btn-secondary" onclick="testChangeDepartment()">
                            <i class="fas fa-exchange-alt me-1"></i> Test Change
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <div id="departmentTestResults"></div>
            </div>
        </div>
        
        <!-- API Test -->
        <div class="test-section">
            <h4>API Test</h4>
            <div class="row">
                <div class="col-md-6">
                    <button class="btn btn-success" onclick="testDepartmentAPI()">
                        <i class="fas fa-code me-1"></i> Test Department API
                    </button>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-info" onclick="testPortfolioAPI()">
                        <i class="fas fa-chart-bar me-1"></i> Test Portfolio API
                    </button>
                </div>
            </div>
            
            <div class="mt-3">
                <div id="apiTestResults"></div>
            </div>
        </div>
        
        <!-- Console Output -->
        <div class="test-section">
            <h4>Console Output</h4>
            <div id="consoleOutput" class="console-output">
                <!-- Console messages will appear here -->
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentDepartmentId = '<?php echo $departmentId; ?>';
        let currentPeriod = 'months';
        let currentDate = '<?php echo date("Y-m"); ?>';
        let currentStatusFilter = 'all';

        // Console logging function
        function logToConsole(message, type = 'info') {
            const consoleDiv = document.getElementById('consoleOutput');
            const timestamp = new Date().toLocaleTimeString();
            const color = type === 'error' ? '#ff6b6b' : type === 'success' ? '#51cf66' : '#00ff00';
            consoleDiv.innerHTML += `<div style="color: ${color};">[${timestamp}] ${message}</div>`;
            consoleDiv.scrollTop = consoleDiv.scrollHeight;
            console.log(message);
        }

        // Test department loading function
        function testLoadDepartments() {
            logToConsole('Testing department loading...', 'info');
            
            const resultsDiv = document.getElementById('departmentTestResults');
            resultsDiv.innerHTML = '<div class="alert alert-info">Loading departments...</div>';
            
            fetch('/bpa/dashboards/get-department-list.php')
                .then(response => {
                    logToConsole(`Department API response status: ${response.status}`, 'info');
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    logToConsole('Department data received:', 'success');
                    logToConsole(JSON.stringify(data, null, 2), 'info');
                    
                    const select = document.getElementById('departmentSelect');
                    if (select) {
                        select.innerHTML = '';

                        if (data.departments && data.departments.length > 0) {
                            data.departments.forEach(dept => {
                                const option = document.createElement('option');
                                option.value = dept.id;
                                option.textContent = dept.name;
                                option.selected = dept.id === currentDepartmentId;
                                select.appendChild(option);
                            });
                            
                            resultsDiv.innerHTML = `<div class="alert alert-success">Successfully loaded ${data.departments.length} departments</div>`;
                            logToConsole(`Loaded ${data.departments.length} departments into select`, 'success');
                        } else {
                            resultsDiv.innerHTML = '<div class="alert alert-warning">No departments found</div>';
                            logToConsole('No departments found in response', 'error');
                        }
                    } else {
                        resultsDiv.innerHTML = '<div class="alert alert-danger">Department select element not found</div>';
                        logToConsole('Department select element not found', 'error');
                    }
                })
                .catch(error => {
                    logToConsole(`Error loading departments: ${error.message}`, 'error');
                    resultsDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                });
        }

        // Test change department function
        function changeDepartment() {
            const select = document.getElementById('departmentSelect');
            if (select) {
                const newDepartmentId = select.value;
                logToConsole(`Department changed from ${currentDepartmentId} to ${newDepartmentId}`, 'info');
                currentDepartmentId = newDepartmentId;
                
                // Test loading portfolio data for the new department
                testPortfolioAPI();
            } else {
                logToConsole('Department select element not found', 'error');
            }
        }

        // Test change department function (manual trigger)
        function testChangeDepartment() {
            logToConsole('Manually testing department change...', 'info');
            changeDepartment();
        }

        // Test department API directly
        function testDepartmentAPI() {
            logToConsole('Testing department API directly...', 'info');
            
            const resultsDiv = document.getElementById('apiTestResults');
            resultsDiv.innerHTML = '<div class="alert alert-info">Testing department API...</div>';
            
            fetch('/bpa/dashboards/get-department-list.php')
                .then(response => response.text())
                .then(text => {
                    logToConsole('Raw API response:', 'info');
                    logToConsole(text, 'info');
                    
                    try {
                        const data = JSON.parse(text);
                        resultsDiv.innerHTML = `<div class="alert alert-success">API working! Found ${data.departments?.length || 0} departments</div>`;
                        logToConsole('JSON parsed successfully', 'success');
                    } catch (e) {
                        resultsDiv.innerHTML = `<div class="alert alert-danger">JSON parse error: ${e.message}</div>`;
                        logToConsole(`JSON parse error: ${e.message}`, 'error');
                    }
                })
                .catch(error => {
                    resultsDiv.innerHTML = `<div class="alert alert-danger">API error: ${error.message}</div>`;
                    logToConsole(`API error: ${error.message}`, 'error');
                });
        }

        // Test portfolio API
        function testPortfolioAPI() {
            logToConsole('Testing portfolio API...', 'info');
            
            const resultsDiv = document.getElementById('apiTestResults');
            resultsDiv.innerHTML = '<div class="alert alert-info">Testing portfolio API...</div>';
            
            fetch('/bpa/dashboards/get-portfolio-metrics.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${currentDepartmentId}&period=${currentPeriod}&date=${currentDate}&statusFilter=${currentStatusFilter}`
            })
            .then(response => {
                logToConsole(`Portfolio API response status: ${response.status}`, 'info');
                return response.text();
            })
            .then(text => {
                logToConsole('Portfolio API raw response:', 'info');
                logToConsole(text, 'info');
                
                try {
                    const data = JSON.parse(text);
                    resultsDiv.innerHTML = `<div class="alert alert-success">Portfolio API working! Total projects: ${data.totalProjects || 0}</div>`;
                    logToConsole('Portfolio data parsed successfully', 'success');
                } catch (e) {
                    resultsDiv.innerHTML = `<div class="alert alert-danger">Portfolio JSON parse error: ${e.message}</div>`;
                    logToConsole(`Portfolio JSON parse error: ${e.message}`, 'error');
                }
            })
            .catch(error => {
                resultsDiv.innerHTML = `<div class="alert alert-danger">Portfolio API error: ${error.message}</div>`;
                logToConsole(`Portfolio API error: ${error.message}`, 'error');
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            logToConsole('Test page loaded successfully', 'success');
            logToConsole(`Current department ID: ${currentDepartmentId}`, 'info');
            
            // Auto-test department loading after 1 second
            setTimeout(() => {
                logToConsole('Auto-testing department loading...', 'info');
                testLoadDepartments();
            }, 1000);
        });
    </script>
</body>
</html> 