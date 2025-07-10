<?php
// Test script for save-kpi.php
// This script helps debug issues with KPI value updates

include_once("../config/config_mysqli.php");

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>KPI Save Test Script</h2>";

// Test database connection
if (!isset($connect) || !$connect) {
    echo "<p style='color: red;'>❌ Database connection failed</p>";
    exit;
} else {
    echo "<p style='color: green;'>✅ Database connection successful</p>";
}

// Test measure table
$testQuery = mysqli_query($connect, "SELECT COUNT(*) as count FROM measure LIMIT 1");
if ($testQuery) {
    $result = mysqli_fetch_assoc($testQuery);
    echo "<p style='color: green;'>✅ Measure table accessible. Total measures: " . $result['count'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ Measure table query failed: " . mysqli_error($connect) . "</p>";
}

// Test measuremonths table
$testQuery = mysqli_query($connect, "SELECT COUNT(*) as count FROM measuremonths LIMIT 1");
if ($testQuery) {
    $result = mysqli_fetch_assoc($testQuery);
    echo "<p style='color: green;'>✅ Measuremonths table accessible. Total records: " . $result['count'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ Measuremonths table query failed: " . mysqli_error($connect) . "</p>";
}

// Test kpi_audit table
$testQuery = mysqli_query($connect, "SELECT COUNT(*) as count FROM kpi_audit LIMIT 1");
if ($testQuery) {
    $result = mysqli_fetch_assoc($testQuery);
    echo "<p style='color: green;'>✅ KPI audit table accessible. Total records: " . $result['count'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ KPI audit table query failed: " . mysqli_error($connect) . "</p>";
}

// Show sample measure data
echo "<h3>Sample Measures:</h3>";
$sampleQuery = mysqli_query($connect, "SELECT id, name, calendarType, gaugeType FROM measure LIMIT 5");
if ($sampleQuery) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Calendar Type</th><th>Gauge Type</th></tr>";
    while ($row = mysqli_fetch_assoc($sampleQuery)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['calendarType']) . "</td>";
        echo "<td>" . htmlspecialchars($row['gaugeType']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Failed to fetch sample measures: " . mysqli_error($connect) . "</p>";
}

// Test POST simulation
echo "<h3>Test POST Data:</h3>";
echo "<form method='post' action='save-kpi.php'>";
echo "<p><strong>KPI ID:</strong> <input type='text' name='objectId' value='' placeholder='Enter KPI ID'></p>";
echo "<p><strong>Updater:</strong> <input type='text' name='updater' value='test_user'></p>";
echo "<p><strong>KPI Values Array (JSON):</strong></p>";
echo "<textarea name='kpiValuesArray' rows='10' cols='80' placeholder='Enter JSON array of KPI values'>[
  {
    \"id\": \"test_id_1\",
    \"date\": \"2024-01\",
    \"actual\": \"85\",
    \"red\": \"60\",
    \"green\": \"80\",
    \"darkgreen\": \"90\",
    \"blue\": \"95\",
    \"gaugeType\": \"fourColor\"
  }
]</textarea>";
echo "<br><br>";
echo "<input type='submit' value='Test Save KPI'>";
echo "</form>";

// Show recent error logs if they exist
echo "<h3>Recent Error Logs:</h3>";
$errorFiles = ['kpiSaveError.txt', 'kpiAuditError.txt', 'checkXmR.txt'];
foreach ($errorFiles as $file) {
    if (file_exists($file)) {
        echo "<h4>$file:</h4>";
        echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 200px; overflow-y: auto;'>";
        echo htmlspecialchars(file_get_contents($file));
        echo "</pre>";
    } else {
        echo "<p>No $file found</p>";
    }
}

echo "<h3>PHP Error Log:</h3>";
$phpErrorLog = ini_get('error_log');
if ($phpErrorLog && file_exists($phpErrorLog)) {
    echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 200px; overflow-y: auto;'>";
    echo htmlspecialchars(file_get_contents($phpErrorLog));
    echo "</pre>";
} else {
    echo "<p>No PHP error log found or accessible</p>";
}
?> 