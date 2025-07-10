<?php
include_once("../../config/config_mysqli.php");
include_once("../../functions/functions.php");

$output = getKRAs(); // Call the function to get the list of KRAs

echo $output;

?>