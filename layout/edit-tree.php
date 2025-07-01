<?php 
include_once("../config/config_mysqli.php");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if database connection exists
if (!isset($connect) || !$connect) {
    $error = "Database connection not available";
    //file_put_contents("edit-tree-debug.log", $error . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(["error" => $error]);
    exit;
}

// Log the request for debugging
$debug_log = "edit-tree.php called at " . date('Y-m-d H:i:s') . "\n";
$debug_log .= "POST data: " . print_r($_POST, true) . "\n";

// Validate and sanitize input parameters
$objectType = isset($_POST['tree_type']) ? mysqli_real_escape_string($connect, trim($_POST['tree_type'])) : '';
$objectId = isset($_POST['tree_id']) ? mysqli_real_escape_string($connect, trim($_POST['tree_id'])) : '';

$debug_log .= "Sanitized - objectType: '$objectType', objectId: '$objectId'\n";

// Validate required parameters
if (empty($objectType) || empty($objectId)) {
    $error = "Error: Missing required parameters. tree_type: '$objectType', tree_id: '$objectId'";
    $debug_log .= $error . "\n";
    //file_put_contents("edit-tree-debug.log", $debug_log, FILE_APPEND);
    http_response_code(400);
    echo json_encode(["error" => $error]);
    exit;
}

// Build query based on object type
$tree_query = "";

if($objectType == 'objective') {
    // Use LEFT JOIN to handle cases where objectiveteam entries might not exist
    $tree_query = "SELECT
        objective.id,
        objective.name,
        objective.description,
        objective.outcome,
        objective.linkedObject,
        objective.owner,
        objective.cascadedfrom,
        objective.weight,
        objective.sortColumn,
        GROUP_CONCAT(DISTINCT uc_users.display_name SEPARATOR ', ') AS updater
    FROM objective
    LEFT JOIN objectiveteam ON objective.id = objectiveteam.objectiveId
    LEFT JOIN uc_users ON objectiveteam.userId = uc_users.user_id
    WHERE objective.id = '$objectId'
    GROUP BY objective.id";
}
else if($objectType == 'measure') {
    $tree_query = "SELECT
        measure.id,
        measure.name,
        measure.calendarType,
        measure.measureType,
        measure.description,
        measure.linkedObject,
        measure.dataType,
        measure.aggregationType,
        measure.owner,
        measure.updater,
        measure.location,
        measure.red,
        measure.blue,
        measure.green,
        measure.darkgreen,
        measure.parentMeasure,
        measure.gaugeType,
        measure.weight,
        measure.archive,
        measure.sort,
        measure.tags
    FROM measure
    WHERE measure.id = '$objectId'";
}
else {
    // For other object types (organization, perspective, individual, etc.)
    // Validate table name to prevent SQL injection and invalid table errors
    $validTables = ['organization', 'perspective', 'individual', 'initiative'];
    if (!in_array($objectType, $validTables)) {
        $error = "Invalid object type: '$objectType'. Valid types are: " . implode(', ', $validTables) . ", objective, measure";
        $debug_log .= $error . "\n";
        //file_put_contents("edit-tree-debug.log", $debug_log, FILE_APPEND);
        http_response_code(400);
        echo json_encode(["error" => $error]);
        exit;
    }
    $tree_query = "SELECT * FROM `$objectType` WHERE id = '$objectId'";
}

$debug_log .= "Query: $tree_query\n";

// Execute query with error handling
$tree_result = mysqli_query($connect, $tree_query);

if (!$tree_result) {
    $error = "Database query failed: " . mysqli_error($connect);
    $debug_log .= $error . "\n";
    //file_put_contents("edit-tree-debug.log", $debug_log, FILE_APPEND);
    http_response_code(500);
    echo json_encode(["error" => $error]);
    exit;
}

$tree_row_count = mysqli_num_rows($tree_result);
$debug_log .= "Rows found: $tree_row_count\n";

if ($tree_row_count == 0) {
    $error = "No records found for objectType: '$objectType', objectId: '$objectId'";
    $debug_log .= $error . "\n";
    //file_put_contents("edit-tree-debug.log", $debug_log, FILE_APPEND);
    echo json_encode(["error" => $error]);
    exit;
}

$count = 1;
$results = [];

while($row = mysqli_fetch_assoc($tree_result)) {
    $results[] = $row;
    $count++;
}

$debug_log .= "Results: " . print_r($results, true) . "\n";
//file_put_contents("edit-tree-debug.log", $debug_log, FILE_APPEND);

// Return JSON response
header('Content-Type: application/json');
if (count($results) == 1) {
    echo json_encode($results[0]);
} else {
    echo json_encode($results);
}

flush();
exit;
?>