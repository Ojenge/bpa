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
    <title>Debug - Initiative Analytics Complete</title>
    
    <!-- Bootstrap CSS -->
    <link href="/bpa/css/myBootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .debug-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .debug-success {
            color: #28a745;
            font-weight: bold;
        }
        
        .debug-error {
            color: #dc3545;
            font-weight: bold;
        }
        
        .debug-info {
            color: #17a2b8;
            font-weight: bold;
        }
        
        .console-output {
            background: #000;
            color: #00ff00;
            padding: 1rem;
            border-radius: 0.25rem;
            font-family: monospace;
            height: 400px;
            overflow-y: auto;
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
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <h1 class="mb-4">
            <i class="fas fa-bug me-3"></i>
            Debug - Initiative Analytics Complete
        </h1>
        
        <!-- Debug Information -->
        <div class="debug-section">
            <h4>Debug Information</h4>
            <div id="debugInfo">
                <p><strong>Department ID:</strong> <span class="debug-info"><?php echo htmlspecialchars($departmentId); ?></span></p>
                <p><strong>Department Name:</strong> <span class="debug-info"><?php echo htmlspecialchars($departmentName); ?></span></p>
                <p><strong>Database Connection:</strong> <span class="debug-success">Connected</span></p>
                <p><strong>Current Time:</strong> <span class="debug-info"><?php echo date('Y-m-d H:i:s'); ?></span></p>
                <p><strong>PHP Version:</strong> <span class="debug-info"><?php echo phpversion(); ?></span></p>
            </div>
        </div>
        
        <!-- Department Controls -->
        <div class="debug-section">
            <h4>Department Controls</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="departmentSelect" class="form-label">Department:</label>
                    <select class="form-select form-select-sm" id="departmentSelect" onchange="changeDepartment()">
                        <!-- Options will be loaded dynamically -->
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="mt-4">
                        <button class="btn btn-primary" onclick="loadDepartments()">
                            <i class="fas fa-sync-alt me-1"></i> Load Departments
                        </button>
                        <button class="btn btn-secondary" onclick="testAllAPIs()">
                            <i class="fas fa-code me-1"></i> Test All APIs
                        </button>
                        <button class="btn btn-success" onclick="loadAllData()">
                            <i class="fas fa-database me-1"></i> Load All Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Metrics Display -->
        <div class="debug-section">
            <h4>Metrics Display</h4>
            <div class="row">
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="metric-value" id="totalProjects">--</div>
                        <div class="metric-label">Total Projects</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="metric-value" id="portfolioValue">--</div>
                        <div class="metric-label">Portfolio Value</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="metric-value" id="avgCompletion">--</div>
                        <div class="metric-label">Avg Completion</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="metric-value" id="portfolioROI">--</div>
                        <div class="metric-label">Portfolio ROI</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- API Test Results -->
        <div class="debug-section">
            <h4>API Test Results</h4>
            <div id="apiTestResults"></div>
        </div>
        
        <!-- Console Output -->
        <div class="debug-section">
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

        // Load departments function
        function loadDepartments() {
            logToConsole('=== LOADING DEPARTMENTS ===', 'info');
            
            const select = document.getElementById('departmentSelect');
            if (!select) {
                logToConsole('ERROR: Department select element not found!', 'error');
                return;
            }
            
            logToConsole('Department select element found', 'success');
            select.innerHTML = '<option value="">Loading departments...</option>';
            
            const url = '/bpa/dashboards/get-department-list.php';
            logToConsole(`Fetching from: ${url}`, 'info');
            
            fetch(url)
                .then(response => {
                    logToConsole(`Response status: ${response.status}`, 'info');
                    logToConsole(`Response headers: ${JSON.stringify([...response.headers.entries()])}`, 'info');
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    logToConsole('JSON data received successfully', 'success');
                    logToConsole(`Data structure: ${JSON.stringify(Object.keys(data))}`, 'info');
                    logToConsole(`Found ${data.departments?.length || 0} departments`, 'info');
                    
                    select.innerHTML = '';
                    
                    if (data.departments && data.departments.length > 0) {
                        data.departments.forEach(dept => {
                            const option = document.createElement('option');
                            option.value = dept.id;
                            option.textContent = dept.name;
                            option.selected = dept.id === currentDepartmentId;
                            select.appendChild(option);
                            logToConsole(`Added department: ${dept.name} (${dept.id})`, 'success');
                        });
                        logToConsole('All departments loaded successfully!', 'success');
                    } else {
                        logToConsole('No departments found in response', 'error');
                        select.innerHTML = '<option value="">No departments found</option>';
                    }
                })
                .catch(error => {
                    logToConsole(`ERROR loading departments: ${error.message}`, 'error');
                    logToConsole(`Error stack: ${error.stack}`, 'error');
                    select.innerHTML = '<option value="">Error loading departments</option>';
                });
        }

        // Change department function
        function changeDepartment() {
            logToConsole('=== DEPARTMENT CHANGE ===', 'info');
            
            const select = document.getElementById('departmentSelect');
            if (!select) {
                logToConsole('ERROR: Department select element not found!', 'error');
                return;
            }
            
            const newDepartmentId = select.value;
            logToConsole(`Department changed from ${currentDepartmentId} to ${newDepartmentId}`, 'info');
            
            if (!newDepartmentId) {
                logToConsole('No department selected', 'error');
                return;
            }
            
            currentDepartmentId = newDepartmentId;
            logToConsole('Reloading data for new department...', 'info');
            loadAllData();
        }

        // Test all APIs function
        function testAllAPIs() {
            logToConsole('=== TESTING ALL APIs ===', 'info');
            
            const resultsDiv = document.getElementById('apiTestResults');
            resultsDiv.innerHTML = '<div class="alert alert-info">Testing all APIs...</div>';
            
            const apis = [
                { name: 'Department List', url: '/bpa/dashboards/get-department-list.php', method: 'GET' },
                { name: 'Portfolio Metrics', url: '/bpa/dashboards/get-portfolio-metrics.php', method: 'POST', data: `departmentId=${currentDepartmentId}&period=${currentPeriod}&date=${currentDate}&statusFilter=${currentStatusFilter}` },
                { name: 'Projects List', url: '/bpa/dashboards/get-projects-list.php', method: 'POST', data: `departmentId=${currentDepartmentId}&period=${currentPeriod}&date=${currentDate}&statusFilter=${currentStatusFilter}` },
                { name: 'Timeline Analysis', url: '/bpa/dashboards/get-timeline-analysis.php', method: 'POST', data: `departmentId=${currentDepartmentId}&period=${currentPeriod}&date=${currentDate}` },
                { name: 'Resource Allocation', url: '/bpa/dashboards/get-resource-allocation.php', method: 'POST', data: `departmentId=${currentDepartmentId}&period=${currentPeriod}&date=${currentDate}` }
            ];
            
            let results = [];
            let completed = 0;
            
            apis.forEach(api => {
                logToConsole(`Testing ${api.name} API...`, 'info');
                
                const options = {
                    method: api.method,
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    }
                };
                
                if (api.data) {
                    options.body = api.data;
                }
                
                fetch(api.url, options)
                    .then(response => {
                        logToConsole(`${api.name} response status: ${response.status}`, 'info');
                        return response.text();
                    })
                    .then(text => {
                        logToConsole(`${api.name} raw response: ${text.substring(0, 200)}...`, 'info');
                        
                        try {
                            const data = JSON.parse(text);
                            results.push(`✅ ${api.name}: Working (${Object.keys(data).length} keys)`);
                            logToConsole(`${api.name} JSON parsed successfully`, 'success');
                        } catch (e) {
                            results.push(`❌ ${api.name}: JSON parse error`);
                            logToConsole(`${api.name} JSON parse error: ${e.message}`, 'error');
                        }
                        
                        completed++;
                        if (completed === apis.length) {
                            resultsDiv.innerHTML = `<div class="alert alert-info"><h5>API Test Results:</h5><ul>${results.map(r => `<li>${r}</li>`).join('')}</ul></div>`;
                        }
                    })
                    .catch(error => {
                        results.push(`❌ ${api.name}: ${error.message}`);
                        logToConsole(`${api.name} error: ${error.message}`, 'error');
                        
                        completed++;
                        if (completed === apis.length) {
                            resultsDiv.innerHTML = `<div class="alert alert-info"><h5>API Test Results:</h5><ul>${results.map(r => `<li>${r}</li>`).join('')}</ul></div>`;
                        }
                    });
            });
        }

        // Load all data function
        function loadAllData() {
            logToConsole('=== LOADING ALL DATA ===', 'info');
            
            // Load portfolio metrics
            logToConsole('Loading portfolio metrics...', 'info');
            fetch('/bpa/dashboards/get-portfolio-metrics.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${currentDepartmentId}&period=${currentPeriod}&date=${currentDate}&statusFilter=${currentStatusFilter}`
            })
            .then(response => {
                logToConsole(`Portfolio metrics response status: ${response.status}`, 'info');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                logToConsole('Portfolio metrics data received', 'success');
                logToConsole(`Data: ${JSON.stringify(data, null, 2)}`, 'info');
                
                // Update UI
                document.getElementById('totalProjects').textContent = data.totalProjects || '--';
                document.getElementById('portfolioValue').textContent = formatCurrency(data.portfolioValue || 0);
                document.getElementById('avgCompletion').textContent = (data.avgCompletion || '--') + '%';
                document.getElementById('portfolioROI').textContent = (data.portfolioROI || '--') + '%';
                
                logToConsole('UI updated successfully', 'success');
            })
            .catch(error => {
                logToConsole(`Error loading portfolio metrics: ${error.message}`, 'error');
                document.getElementById('totalProjects').textContent = 'Error';
            });
        }

        // Utility function
        function formatCurrency(amount) {
            try {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(amount || 0);
            } catch (error) {
                return '$0';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            logToConsole('=== PAGE LOADED ===', 'success');
            logToConsole(`Current department ID: ${currentDepartmentId}`, 'info');
            logToConsole(`Current period: ${currentPeriod}`, 'info');
            logToConsole(`Current date: ${currentDate}`, 'info');
            logToConsole(`Current status filter: ${currentStatusFilter}`, 'info');
            
            // Check if all required elements exist
            const elements = ['departmentSelect', 'totalProjects', 'portfolioValue', 'avgCompletion', 'portfolioROI'];
            elements.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    logToConsole(`✅ Element found: ${id}`, 'success');
                } else {
                    logToConsole(`❌ Element missing: ${id}`, 'error');
                }
            });
            
            // Auto-load departments after 1 second
            setTimeout(() => {
                logToConsole('Auto-loading departments...', 'info');
                loadDepartments();
            }, 1000);
        });
    </script>
</body>
</html> 