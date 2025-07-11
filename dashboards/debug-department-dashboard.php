<?php
include_once("../config/config_mysqli.php");
include_once("../admin/models/config.php");
include_once("../functions/functions.php");
include_once("../functions/calendar-labels.php");
include_once("../functions/perspOrg-scores.php");
include_once("../reports/scores-functions.2.0.php");

date_default_timezone_set('Africa/Nairobi');

// Test parameters
$departmentId = 'org1';
$objectPeriod = 'months';
$objectDate = date("Y-m");

echo "<h1>Department Dashboard Debug</h1>";
echo "<h2>Testing Department ID: $departmentId</h2>";

// Test 1: Check if department exists
echo "<h3>Test 1: Department Information</h3>";
$deptQuery = mysqli_query($connect, "SELECT id, name, mission, vision FROM organization WHERE id = '$departmentId'");
if ($deptQuery) {
    $deptInfo = mysqli_fetch_assoc($deptQuery);
    if ($deptInfo) {
        echo "<p>✓ Department found: " . htmlspecialchars($deptInfo['name']) . "</p>";
    } else {
        echo "<p>✗ Department not found with ID: $departmentId</p>";
    }
} else {
    echo "<p>✗ Database error: " . mysqli_error($connect) . "</p>";
}

// Test 2: Check if getOrgScore function works
echo "<h3>Test 2: Organization Score Function</h3>";
try {
    $overallScore = getOrgScore($departmentId);
    echo "<p>✓ getOrgScore returned: " . ($overallScore !== null ? $overallScore : 'null') . "</p>";
} catch (Exception $e) {
    echo "<p>✗ getOrgScore error: " . $e->getMessage() . "</p>";
}

// Test 3: Check team size
echo "<h3>Test 3: Team Size</h3>";
$teamQuery = mysqli_query($connect, "
    SELECT COUNT(*) as count 
    FROM uc_users 
    WHERE department = '$departmentId' 
    AND active = 1 
    AND title != 'Executive Assistant'
");
if ($teamQuery) {
    $teamResult = mysqli_fetch_assoc($teamQuery);
    echo "<p>✓ Team size: " . ($teamResult['count'] ?? 0) . " staff members</p>";
} else {
    echo "<p>✗ Team query error: " . mysqli_error($connect) . "</p>";
}

// Test 4: Check active initiatives
echo "<h3>Test 4: Active Initiatives</h3>";
$initiativesQuery = mysqli_query($connect, "
    SELECT COUNT(DISTINCT i.id) as count 
    FROM initiative i
    INNER JOIN uc_users u ON i.projectManager = u.user_id
    WHERE u.department = '$departmentId'
    AND i.completionDate IS NULL
");
if ($initiativesQuery) {
    $initiativesResult = mysqli_fetch_assoc($initiativesQuery);
    echo "<p>✓ Active initiatives: " . ($initiativesResult['count'] ?? 0) . "</p>";
} else {
    echo "<p>✗ Initiatives query error: " . mysqli_error($connect) . "</p>";
}

// Test 5: Check completion rate
echo "<h3>Test 5: Completion Rate</h3>";
$currentMonth = date('Y-m', strtotime($objectDate));
$completionQuery = mysqli_query($connect, "
    SELECT 
        AVG(ist.percentageCompletion) as avgCompletion
    FROM initiative_status ist
    INNER JOIN initiative i ON ist.initiativeId = i.id
    INNER JOIN uc_users u ON i.projectManager = u.user_id
    WHERE u.department = '$departmentId'
    AND DATE_FORMAT(ist.updatedOn, '%Y-%m') = '$currentMonth'
    AND ist.updatedOn = (
        SELECT MAX(ist2.updatedOn) 
        FROM initiative_status ist2 
        WHERE ist2.initiativeId = ist.initiativeId
    )
");
if ($completionQuery) {
    $completionResult = mysqli_fetch_assoc($completionQuery);
    echo "<p>✓ Completion rate: " . round($completionResult['avgCompletion'] ?? 0, 1) . "%</p>";
} else {
    echo "<p>✗ Completion query error: " . mysqli_error($connect) . "</p>";
}

// Test 6: Check if required tables exist
echo "<h3>Test 6: Required Tables</h3>";
$tables = ['organization', 'uc_users', 'initiative', 'initiative_status', 'perspective', 'objective', 'measure'];
foreach ($tables as $table) {
    $tableQuery = mysqli_query($connect, "SHOW TABLES LIKE '$table'");
    if ($tableQuery && mysqli_num_rows($tableQuery) > 0) {
        echo "<p>✓ Table '$table' exists</p>";
    } else {
        echo "<p>✗ Table '$table' does not exist</p>";
    }
}

// Test 7: Check API endpoint
echo "<h3>Test 7: API Endpoint Test</h3>";
echo "<p>Testing get-department-metrics.php...</p>";
echo "<div id='api-result'>Loading...</div>";

echo "<script>
fetch('get-department-metrics.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'departmentId=$departmentId&objectPeriod=$objectPeriod&objectDate=$objectDate'
})
.then(response => {
    console.log('Response status:', response.status);
    return response.json();
})
.then(data => {
    console.log('API Response:', data);
    document.getElementById('api-result').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
})
.catch(error => {
    console.error('API Error:', error);
    document.getElementById('api-result').innerHTML = '<p style=\"color: red;\">Error: ' + error.message + '</p>';
});
</script>";

echo "<h3>Test 8: JavaScript Function Test</h3>";
echo "<button onclick='testDepartmentDashboard()'>Test departmentDashboard() Function</button>";
echo "<div id='js-result'></div>";

echo "<script>
function testDepartmentDashboard() {
    console.log('Testing departmentDashboard function...');
    if (typeof departmentDashboard === 'function') {
        document.getElementById('js-result').innerHTML = '<p style=\"color: green;\">✓ departmentDashboard function exists</p>';
        // Don't actually call it to avoid navigation
        console.log('departmentDashboard function found');
    } else {
        document.getElementById('js-result').innerHTML = '<p style=\"color: red;\">✗ departmentDashboard function not found</p>';
        console.log('departmentDashboard function not found');
    }
}

// Test if required JavaScript functions exist
console.log('Checking for required functions...');
console.log('departmentDashboard:', typeof departmentDashboard);
console.log('safeContentPaneTransition:', typeof safeContentPaneTransition);
console.log('fetch:', typeof fetch);
</script>";

echo "<h3>Test 9: Browser Console</h3>";
echo "<p>Check the browser console for any JavaScript errors or warnings.</p>";
echo "<p>Press F12 to open developer tools and check the Console tab.</p>";

echo "<h3>Test 10: Network Requests</h3>";
echo "<p>Check the Network tab in developer tools to see if API requests are being made and what responses are received.</p>";

?> 