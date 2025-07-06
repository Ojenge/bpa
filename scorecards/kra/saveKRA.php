<?php
include_once("../../config/config_mysqli.php");
include_once("functions.php");

$strategicPriority = $_POST["strategicPriority"];
$strategicResult = $_POST["strategicResult"];
$editState = $_POST["editState"];

if($editState === "edit") {
	// If in edit mode, update the existing strategic result
	$id = $_POST["id"];
	mysqli_query($connect, "UPDATE strategic_results SET priority = '$strategicPriority', result = '$strategicResult' WHERE id = '$id'");
} else {
	// If in new mode, insert a new strategic result
	$id = null; // ID will be auto-incremented
	mysqli_query($connect, "INSERT INTO strategic_results VALUES (default, '$strategicPriority', '$strategicResult')");
}

//echo '<div class="alert alert-success" role="alert">Strategic Result added successfully!</div>';

$output = getKRAs(); // Call the function to get the updated list of KRAs

echo $output;

?>