<?php
include_once("../config/config_msqli.php");

if(!empty($_POST['selectedPIP'])) $selectedPIP = $_POST['selectedPIP'];
$toggle = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT archive FROM pdp WHERE id = '$selectedPIP'");
$toggle = mysqli_fetch_assoc($toggle);
$toggle = $toggle["archive"];
if($toggle != 'Yes') 
{
	mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE pdp SET archive = 'Yes' WHERE id = '$selectedPIP'");
	echo "Yes";
}
else 
{
	mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE pdp SET archive = 'No' WHERE id = '$selectedPIP'");
	echo "No";
}
?>