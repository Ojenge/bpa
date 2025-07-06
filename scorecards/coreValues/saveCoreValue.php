<?php
include_once("../../config/config_mysqli.php");
include_once("functions.php");

$coreValue = $_POST["coreValue"];
$coreValueDescription = $_POST["coreValueDescription"];
$editState = $_POST["editState"];

if($editState === "edit") {
	// If in edit mode, update the existing strategic result
	$id = $_POST["id"];
	mysqli_query($connect, "UPDATE core_value SET value = '$coreValue', description = '$coreValueDescription' WHERE id = '$id'");
} else {
	// If in new mode, insert a new strategic result
	$id = null; // ID will be auto-incremented
	mysqli_query($connect, "INSERT INTO core_value VALUES (default, '$coreValue', '$coreValueDescription')");
}

//echo '<div class="alert alert-success" role="alert">Strategic Result added successfully!</div>';

$output = getCoreValues(); // Call the function to get the updated list of KRAs

echo $output;

?>