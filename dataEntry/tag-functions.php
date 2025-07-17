<?php
require_once("../admin/models/config.php");
include_once("../config/config_mysqli.php");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to save tags for measures
function saveMeasureTags($measureId, $tags) {
    global $GLOBALS;
    
    // Debug logging
    error_log("saveMeasureTags called with measureId: $measureId, tags: " . print_r($tags, true));
    
    // Validate inputs
    if (empty($measureId)) {
        error_log("Error: measureId is empty");
        return array("success" => false, "message" => "Measure ID is required");
    }
    
    if (!is_array($tags)) {
        error_log("Error: tags is not an array: " . gettype($tags));
        return array("success" => false, "message" => "Tags must be an array");
    }
    
    $tagsJson = json_encode($tags);
    $measureId = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $measureId);
    $tagsJson = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $tagsJson);
    
    // Debug the query
    $query = "UPDATE measure SET tags = '$tagsJson' WHERE id = '$measureId'";
    error_log("Executing query: $query");
    
    $result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
    
    if ($result) {
        $affectedRows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
        error_log("Query successful. Affected rows: $affectedRows");
        return array("success" => true, "message" => "Tags saved successfully", "affected_rows" => $affectedRows);
    } else {
        $error = mysqli_error($GLOBALS["___mysqli_ston"]);
        error_log("Query failed: $error");
        return array("success" => false, "message" => "Error saving tags: " . $error);
    }
}

// Function to save tags for initiatives
function saveInitiativeTags($initiativeId, $tags) {
    global $GLOBALS;
    
    // Debug logging
    error_log("saveInitiativeTags called with initiativeId: $initiativeId, tags: " . print_r($tags, true));
    
    // Validate inputs
    if (empty($initiativeId)) {
        error_log("Error: initiativeId is empty");
        return array("success" => false, "message" => "Initiative ID is required");
    }
    
    if (!is_array($tags)) {
        error_log("Error: tags is not an array: " . gettype($tags));
        return array("success" => false, "message" => "Tags must be an array");
    }
    
    // Check if tags column exists
    $checkColumn = mysqli_query($GLOBALS["___mysqli_ston"], "SHOW COLUMNS FROM initiative LIKE 'tags'");
    if (mysqli_num_rows($checkColumn) == 0) {
        error_log("Error: tags column does not exist in initiative table");
        return array("success" => false, "message" => "Tags column does not exist. Please run the database migration script.");
    }
    
    $tagsJson = json_encode($tags);
    $initiativeId = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $initiativeId);
    $tagsJson = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $tagsJson);
    
    // Debug the query
    $query = "UPDATE initiative SET tags = '$tagsJson' WHERE id = '$initiativeId'";
    error_log("Executing query: $query");
    
    $result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
    
    if ($result) {
        $affectedRows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
        error_log("Query successful. Affected rows: $affectedRows");
        return array("success" => true, "message" => "Tags saved successfully", "affected_rows" => $affectedRows);
    } else {
        $error = mysqli_error($GLOBALS["___mysqli_ston"]);
        error_log("Query failed: $error");
        return array("success" => false, "message" => "Error saving tags: " . $error);
    }
}

// Function to get tags for measures
function getMeasureTags($measureId) {
    global $GLOBALS;
    
    $measureId = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $measureId);
    $query = "SELECT tags FROM measure WHERE id = '$measureId'";
    $result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $tags = json_decode($row['tags'], true);
        return $tags ? $tags : array();
    }
    
    return array();
}

// Function to get tags for initiatives
function getInitiativeTags($initiativeId) {
    global $GLOBALS;
    
    // Check if tags column exists
    $checkColumn = mysqli_query($GLOBALS["___mysqli_ston"], "SHOW COLUMNS FROM initiative LIKE 'tags'");
    if (mysqli_num_rows($checkColumn) == 0) {
        error_log("Error: tags column does not exist in initiative table");
        return array();
    }
    
    $initiativeId = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $initiativeId);
    $query = "SELECT tags FROM initiative WHERE id = '$initiativeId'";
    $result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $tags = json_decode($row['tags'], true);
        return $tags ? $tags : array();
    }
    
    return array();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug incoming request
    error_log("POST request received: " . print_r($_POST, true));
    
    $action = $_POST['action'] ?? '';
    $response = array();
    
    switch ($action) {
        case 'save_measure_tags':
            $measureId = $_POST['measureId'] ?? '';
            $tags = $_POST['tags'] ?? array();
            
            // Handle JSON string if tags is passed as JSON
            if (is_string($tags) && !empty($tags)) {
                $decodedTags = json_decode($tags, true);
                if ($decodedTags !== null) {
                    $tags = $decodedTags;
                }
            }
            
            error_log("Processing save_measure_tags - measureId: $measureId, tags: " . print_r($tags, true));
            $response = saveMeasureTags($measureId, $tags);
            break;
            
        case 'save_initiative_tags':
            $initiativeId = $_POST['initiativeId'] ?? '';
            $tags = $_POST['tags'] ?? array();
            
            // Handle JSON string if tags is passed as JSON
            if (is_string($tags) && !empty($tags)) {
                $decodedTags = json_decode($tags, true);
                if ($decodedTags !== null) {
                    $tags = $decodedTags;
                }
            }
            
            error_log("Processing save_initiative_tags - initiativeId: $initiativeId, tags: " . print_r($tags, true));
            $response = saveInitiativeTags($initiativeId, $tags);
            break;
            
        case 'get_measure_tags':
            $measureId = $_POST['measureId'] ?? '';
            $response = array("success" => true, "tags" => getMeasureTags($measureId));
            break;
            
        case 'get_initiative_tags':
            $initiativeId = $_POST['initiativeId'] ?? '';
            $response = array("success" => true, "tags" => getInitiativeTags($initiativeId));
            break;
            
        default:
            $response = array("success" => false, "message" => "Invalid action: $action");
    }
    
    error_log("Sending response: " . print_r($response, true));
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?> 