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
    <title>Standalone Dashboard Test</title>
    <link href="/bpa/css/myBootstrap.min.css" rel="stylesheet">
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
        .warning { background-color: #fff3cd; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Standalone Dashboard Test</h1>
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
            <h3>Dashboard Loading Test</h3>
            <button class="btn btn-success" onclick="testDashboardLoading()">Test Dashboard Loading</button>
            <div id="loading-result"></div>
        </div>
        
        <div class="test-card">
            <h3>Direct Dashboard Access</h3>
            <a href="department-performance-dashboard.php?departmentId=<?php echo $departmentId; ?>" 
               class="btn btn-info" target="_blank">Open Dashboard Directly</a>
            <p class="mt-2">This will open the dashboard in a new tab to test if it works when accessed directly.</p>
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
        
        // Test dashboard loading simulation
        function testDashboardLoading() {
            const resultDiv = document.getElementById('loading-result');
            resultDiv.innerHTML = 'Testing dashboard loading...';
            
            // Simulate what happens when the dashboard loads
            const testUrl = 'department-performance-dashboard.php?departmentId=<?php echo $departmentId; ?>';
            
            fetch(testUrl)
                .then(response => {
                    console.log('Dashboard page response status:', response.status);
                    if (response.ok) {
                        return response.text();
                    } else {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                })
                .then(html => {
                    console.log('Dashboard page loaded successfully');
                    resultDiv.innerHTML = '<div class="success">✓ Dashboard page loads successfully</div>';
                    
                    // Check if the page contains expected elements
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const checks = [
                        { selector: 'title', name: 'Page title' },
                        { selector: 'script', name: 'JavaScript' },
                        { selector: 'link[href*="bootstrap"]', name: 'Bootstrap CSS' }
                    ];
                    
                    let checkResults = '<div class="info"><h4>Page Content Check:</h4>';
                    checks.forEach(check => {
                        const elements = doc.querySelectorAll(check.selector);
                        if (elements.length > 0) {
                            checkResults += `<div class="success">✓ ${check.name} found (${elements.length} elements)</div>`;
                        } else {
                            checkResults += `<div class="error">✗ ${check.name} not found</div>`;
                        }
                    });
                    checkResults += '</div>';
                    
                    resultDiv.innerHTML += checkResults;
                })
                .catch(error => {
                    console.error('Dashboard loading error:', error);
                    resultDiv.innerHTML = '<div class="error">✗ Dashboard page failed to load: ' + error.message + '</div>';
                });
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
            
            // Test 3: Check if we can access the dashboard file
            results.push('✓ Testing dashboard file access...');
            
            resultsDiv.innerHTML = results.map(result => '<div>' + result + '</div>').join('');
        });
    </script>
</body>
</html> 