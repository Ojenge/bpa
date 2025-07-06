<?php
include_once("../../config/config_mysqli.php");
include_once("functions.php");

$id = $_POST["id"];

mysqli_query($connect, "DELETE FROM core_value_attribute_score WHERE id = '$id'");

//$output = getCoreValues(); // Call the function to get the updated list of Core Values

//echo $output;

?>