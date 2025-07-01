<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Log incoming POST data for debugging
    error_log("format-shared-ids.php received POST data: " . print_r($_POST, true));

    // Check if POST data exists
    if (!isset($_POST['non_measures']) && !isset($_POST['shared_id'])) {
        // Return empty result instead of throwing error for better UX
        echo json_encode(["selected" => [], "debug" => "No parameters received"]);
        exit;
    }

    // Get the shared_id data - prioritize shared_id over non_measures
    $shared_id = null;
    if(isset($_POST['shared_id']) && !empty($_POST['shared_id'])) {
        $shared_id = $_POST['shared_id'];
    } elseif(isset($_POST['non_measures']) && !empty($_POST['non_measures'])) {
        $shared_id = $_POST['non_measures'];
    }

    // Validate that shared_id is not empty
    if (empty($shared_id) || $shared_id === 'null' || $shared_id === 'undefined') {
        echo json_encode(["selected" => [], "debug" => "Empty or invalid data received"]);
        exit;
    }

    // Try to decode JSON data
    $staffArray = json_decode($shared_id, true);

    // Check for JSON decode errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        // If it's not valid JSON, treat it as a simple string value
        error_log("format-shared-ids.php: Not valid JSON, treating as simple value: " . $shared_id);
        echo json_encode(["selected" => [$shared_id], "debug" => "Treated as simple value"]);
        exit;
    }

    // Validate that staffArray is an array
    if (!is_array($staffArray)) {
        // If it's not an array after JSON decode, treat the decoded value as a single item
        echo json_encode(["selected" => [$staffArray], "debug" => "Single value after JSON decode"]);
        exit;
    }

    // Build the response array
    $selectedValues = [];
    foreach($staffArray as $items) {
        // Validate that each item has a 'value' key
        if (is_array($items) && isset($items["value"])) {
            $selectedValues[] = $items["value"];
        }
    }

    // Return JSON response
    echo json_encode(["selected" => $selectedValues]);

} catch (Exception $e) {
    // Log error for debugging
    error_log("format-shared-ids.php error: " . $e->getMessage());
    error_log("POST data was: " . print_r($_POST, true));

    // Return error response with more debugging info
    echo json_encode([
        "error" => $e->getMessage(),
        "selected" => [],
        "debug" => [
            "post_data" => $_POST,
            "error_message" => $e->getMessage()
        ]
    ]);
}
?>