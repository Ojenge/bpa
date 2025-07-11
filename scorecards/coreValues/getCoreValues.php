<?php
include_once("../../config/config_mysqli.php");
include_once("functions.php");

if(isset($_GET['mainMenuState']))
    $mainMenuState = $_GET['mainMenuState'];

if(isset($_GET['staff']))
    $staff = $_GET['staff'];

$output = getCoreValues($mainMenuState, $staff); // Call the function to get the list of KRAs

echo $output;

?>