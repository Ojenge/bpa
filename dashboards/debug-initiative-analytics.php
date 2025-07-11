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
    <title>Debug - Initiative Analytics</title>
    
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
            Debug - Initiative Analytics
        </h1>
        
        <!-- Debug Information -->
        <div class="debug-section">
            <h4>Debug Information</h4>
            <div id="debugInfo">
                <p><strong>Department ID:</strong> <span class="debug-info"><?php echo htmlspecialchars($departmentId); ?></span></p>
                <p><strong>Department Name:</strong> <span class="debug-info"><?php echo htmlspecialchars($departmentName); ?></span></p>
                <p><strong>Database Connection:</strong> <span class="debug-success">Connected</span></p>
                <p><strong>Current Time:</strong> <span class="debug-info"><?php echo date('Y-m-d H:i:s'); ?></span></p>
            </div>
        </div>
        
        <!-- Test Data Loading -->
        <div class="debug-section">
            <h4>Test Data Loading</h4>
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
            
            <div class="mt-3">
                <button class="btn btn-primary" onclick="testDataLoading()">
                    <i class="fas fa-sync-alt me-1"></i> Test Data Loading
                </button>
                <button class="btn btn-secondary" onclick="testDirectAPI()">
                    <i class="fas fa-code me-1"></i> Test Direct API
                </button>
            </div>
            
            <div id="testResults" class="mt-3"></div>
        </div>
        
        <!-- Console Output -->
        <div class="debug-section">
            <h4>Console Output</h4>
            <div id="consoleOutput" style="background: #000; color: #00ff00; padding: 1rem; border-radius: 0.25rem; font-family: monospace; height: 300px; overflow-y: auto;">
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

        // Test data loading function
        function testDataLoading() {
            logToConsole('Starting data loading test...', 'info');
            
            // Test portfolio metrics
            logToConsole('Testing portfolio metrics API...', 'info');
            fetch('get-portfolio-metrics.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `departmentId=${currentDepartmentId}&period=${currentPeriod}&date=${currentDate}&statusFilter=${currentStatusFilter}`
            })
            .then(response => {
                logToConsole(`Response status: ${response.status}`, 'info');
                return response.json();
            })
            .then(data => {
                logToConsole('Portfolio metrics data received:', 'success');
                logToConsole(JSON.stringify(data, null, 2), 'info');
                
                // Update UI
                document.getElementById('totalProjects').textContent = data.totalProjects || '--';
                document.getElementById('portfolioValue').textContent = formatCurrency(data.portfolioValue || 0);
                document.getElementById('avgCompletion').textContent = (data.avgCompletion || '--') + '%';
                document.getElementById('portfolioROI').textContent = (data.portfolioROI || '--') + '%';
                
                logToConsole('UI updated successfully', 'success');
            })
            .catch(error => {
                logToConsole(`Error loading portfolio metrics: ${error.message}`, 'error');
            });
        }

        // Test direct API function
        function testDirectAPI() {
            logToConsole('Testing direct API call...', 'info');
            
            const formData = new FormData();
            formData.append('departmentId', currentDepartmentId);
            formData.append('period', currentPeriod);
            formData.append('date', currentDate);
            formData.append('statusFilter', currentStatusFilter);
            
            fetch('get-portfolio-metrics.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                logToConsole(`Direct API response status: ${response.status}`, 'info');
                return response.text();
            })
            .then(text => {
                logToConsole('Raw response:', 'info');
                logToConsole(text, 'info');
                
                try {
                    const data = JSON.parse(text);
                    logToConsole('Parsed JSON successfully', 'success');
                    logToConsole(JSON.stringify(data, null, 2), 'info');
                } catch (e) {
                    logToConsole(`JSON parse error: ${e.message}`, 'error');
                }
            })
            .catch(error => {
                logToConsole(`Direct API error: ${error.message}`, 'error');
            });
        }

        // Utility function
        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount || 0);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            logToConsole('Debug page loaded successfully', 'success');
            logToConsole(`Current department ID: ${currentDepartmentId}`, 'info');
            logToConsole(`Current period: ${currentPeriod}`, 'info');
            logToConsole(`Current date: ${currentDate}`, 'info');
            
            // Auto-test after 1 second
            setTimeout(() => {
                logToConsole('Auto-testing data loading...', 'info');
                testDataLoading();
            }, 1000);
        });
    </script>
</body>
</html> 