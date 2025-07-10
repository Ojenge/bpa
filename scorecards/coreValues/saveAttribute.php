<?php
include_once("../../config/config_mysqli.php");
include_once("../../functions/functions.php");

$attribute = $_POST["attribute"];
$attributeDescription = $_POST["attributeDescription"];
$editStateAttribute = $_POST["editStateAttribute"];
$core_value_id = $_POST["coreValueId"];

if($editStateAttribute === "edit") {
	// If in edit mode, update the existing strategic result
	$id = $_POST["id"];
	mysqli_query($connect, "UPDATE core_value_attribute SET attribute = '$attribute', description = '$attributeDescription' WHERE id = '$id'");
} else {
	// If in new mode, insert a new strategic result
	$id = null; // ID will be auto-incremented
	mysqli_query($connect, "INSERT INTO core_value_attribute VALUES (default, '$attribute', '$attributeDescription', '$core_value_id')");
}

//echo '<div class="alert alert-success" role="alert">Strategic Result added successfully!</div>';

$output = getCoreValues(); // Call the function to get the updated list of KRAs

echo $output;

?>