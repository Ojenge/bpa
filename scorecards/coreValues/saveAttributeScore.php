<?php
include_once("../../config/config_mysqli.php");
include_once("../../functions/functions.php");

$attributeScore = $_POST["attributeScore"];
$attributeScoreDate = $_POST["attributeScoreDate"];
$editStateAttributeScore = $_POST["editStateAttributeScore"];
$attributeId = $_POST["attributeId"];
$staffId = $_POST["staffId"];

//if($editStateAttributeScore === "edit" || isset($_POST["attributeScoreId"]) ) {
if($editStateAttributeScore === "edit" ) {
		// If in edit mode, update the existing strategic result
	$id = $_POST["attributeScoreId"];
	mysqli_query($connect, "UPDATE core_value_attribute_score SET score = '$attributeScore', date = '$attributeScoreDate' WHERE id = '$id'");
} else {
	//mysqli_query($connect, "SELECT date FROM core_value_attribute_score WHERE id = '$attributeId' ORDER BY id DESC LIMIT 1");
	mysqli_query($connect, "INSERT INTO core_value_attribute_score VALUES (default, '$attributeId', '$attributeScore', '$attributeScoreDate', '$staffId')") or file_put_contents("error_log.txt", mysqli_error($connect));
}

//echo '<div class="alert alert-success" role="alert">Strategic Result added successfully!</div>';

$output = getCoreValues($staffId); // Call the function to get the updated list of KRAs

echo $output;

?>