<?php
include_once("../config/config_msqli.php");

if(!empty($_POST['selectedPIP'])) $selectedPIP = $_POST['selectedPIP'];

mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM pdp WHERE id = '$selectedPIP'");

?>