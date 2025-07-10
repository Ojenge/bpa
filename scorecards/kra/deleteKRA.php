<?php
include_once("../../config/config_mysqli.php");
include_once("../../functions/functions.php");

$id = $_POST["id"];

mysqli_query($connect, "DELETE FROM strategic_results WHERE id = '$id'");

$output = getKRAs(); // Call the function to get the updated list of KRAs

echo $output;

?>