<?php
include_once("../../config/config_mysqli.php");
include_once("functions.php");

if(isset($_GET['mainMenuState']))
    $mainMenuState = $_GET['mainMenuState'];

$output = getCoreValues($mainMenuState); // Call the function to get the list of KRAs

echo $output;

?>