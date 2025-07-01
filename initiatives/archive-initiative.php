<?php
include_once("../config/config_mysqli.php");

if(!empty($_POST['selectedInitiative'])) $selectedInitiative = $_POST['selectedInitiative'];
$toggle = mysqli_query($connect, "SELECT archive FROM initiative WHERE id = '$selectedInitiative'");
$toggle = mysqli_fetch_assoc($toggle);
$toggle = $toggle["archive"];
if($toggle != 'Yes') 
{
	mysqli_query($connect, "UPDATE initiative SET archive = 'Yes' WHERE id = '$selectedInitiative'");
	echo "Yes";
}
else 
{
	mysqli_query($connect, "UPDATE initiative SET archive = 'No' WHERE id = '$selectedInitiative'");
	echo "No";
}

?>