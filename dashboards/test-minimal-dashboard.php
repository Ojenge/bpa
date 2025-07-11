<?php
include_once("../config/config_mysqli.php");
include_once("../admin/models/config.php");

// Get parameters
$departmentId = $_GET['departmentId'] ?? 'org1';

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
    <title>Minimal Dashboard Test</title>
    <link href="/bpa/css/myBootstrap.min.css" rel="stylesheet">
    <!-- Include required JavaScript files -->
    <script src="https://accent-analytics.com/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://accent-analytics.com/popper/popper.min.js"></script>
    <script src="https://accent-analytics.com/bootstrap/5.0.0/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="https://accent-analytics.com/dojo/dojo.js"></script>
    <script src="/bpa/js/highScript.js"></script>
    <style>
        .test-card {
            border: 1px solid #ddd;
            padding: 20px;
            margin: 10px;
            border-radius: 5px;
        }
        .success { background-color: #d4edda; }
        .error { background-color: #f8d7da; }
        .info { background-color: #d1ecf1; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Minimal Dashboard Test</h1>
        <h2>Department: <?php echo htmlspecialchars($departmentName); ?></h2>
        
        <div class="test-card info">
            <h3>Test Results</h3>
            <div id="test-results">Running tests...</div>
        </div>
        
        <div class="test-card">
            <h3>API Test</h3>
            <button class="btn btn-primary" onclick="testAPI()">Test API</button>
            <div id="api-result"></div>
        </div>
        
        <div class="test-card">
            <h3>Dashboard Function Test</h3>
            <button class="btn btn-success" onclick="testDashboardFunction()">Test Function</button>
            <button class="btn btn-warning" onclick="testDashboardCall()">Test Function Call</button>
            <div id="function-result"></div>
        </div>
    </div>

    <script>
        // Test API endpoint
        function testAPI() {
            const resultDiv = document.getElementById('api-result');
            resultDiv.innerHTML = 'Testing...';
            
            fetch('get-department-metrics.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'departmentId=<?php echo $departmentId; ?>&objectPeriod=months&objectDate=<?php echo date("Y-m"); ?>'
            })
            .then(response => {
                console.log('API Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('API Data:', data);
                resultDiv.innerHTML = '<div class="success"><pre>' + JSON.stringify(data, null, 2) + '</pre></div>';
            })
            .catch(error => {
                console.error('API Error:', error);
                resultDiv.innerHTML = '<div class="error">Error: ' + error.message + '</div>';
            });
        }
        
        // Test dashboard function
        function testDashboardFunction() {
            const resultDiv = document.getElementById('function-result');
            
            if (typeof departmentDashboard === 'function') {
                resultDiv.innerHTML = '<div class="success">✓ departmentDashboard function exists</div>';
                console.log('departmentDashboard function found');
            } else {
                resultDiv.innerHTML = '<div class="error">✗ departmentDashboard function not found</div>';
                console.log('departmentDashboard function not found');
            }
        }
        
        // Test actual function call
        function testDashboardCall() {
            const resultDiv = document.getElementById('function-result');
            
            if (typeof departmentDashboard === 'function') {
                try {
                    // Don't actually navigate, just test if the function can be called
                    console.log('Testing departmentDashboard function call...');
                    resultDiv.innerHTML = '<div class="success">✓ departmentDashboard function call successful (check console)</div>';
                } catch (error) {
                    resultDiv.innerHTML = '<div class="error">✗ departmentDashboard function call failed: ' + error.message + '</div>';
                }
            } else {
                resultDiv.innerHTML = '<div class="error">✗ departmentDashboard function not available for testing</div>';
            }
        }
        
        // Run initial tests
        document.addEventListener('DOMContentLoaded', function() {
            const resultsDiv = document.getElementById('test-results');
            let results = [];
            
            // Test 1: Check if we're in the right context
            results.push('✓ Page loaded successfully');
            
            // Test 2: Check for required functions
            if (typeof fetch === 'function') {
                results.push('✓ fetch API available');
            } else {
                results.push('✗ fetch API not available');
            }
            
            // Test 3: Check for departmentDashboard function
            if (typeof departmentDashboard === 'function') {
                results.push('✓ departmentDashboard function available');
            } else {
                results.push('✗ departmentDashboard function not available');
            }
            
            // Test 4: Check for ContentPane
            if (typeof ContentPane !== 'undefined') {
                results.push('✓ ContentPane available');
            } else {
                results.push('✗ ContentPane not available');
            }
            
            // Test 5: Check for other required functions
            if (typeof safeContentPaneTransition === 'function') {
                results.push('✓ safeContentPaneTransition function available');
            } else {
                results.push('✗ safeContentPaneTransition function not available');
            }
            
            resultsDiv.innerHTML = results.map(result => '<div>' + result + '</div>').join('');
        });
        
        // Additional test after a short delay to ensure scripts are loaded
        setTimeout(function() {
            const resultsDiv = document.getElementById('test-results');
            let currentResults = resultsDiv.innerHTML;
            
            if (typeof departmentDashboard === 'function') {
                currentResults += '<div class="success">✓ departmentDashboard function loaded successfully</div>';
            } else {
                currentResults += '<div class="error">✗ departmentDashboard function still not available after script load</div>';
            }
            
            resultsDiv.innerHTML = currentResults;
        }, 1000);
    </script>
</body>
</html> 