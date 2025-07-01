<?php
// Handle both web requests and direct PHP execution
$config_path = file_exists("../config/config_mysqli.php") ? "../config/config_mysqli.php" : "config/config_mysqli.php";
include_once($config_path);
	
$getHome = mysqli_query($connect, "SELECT url FROM uc_permissions WHERE home = 'Yes'") or file_put_contents("aMenu.txt","Error => ".mysqli_error($connect));
$row = mysqli_fetch_assoc($getHome);

$homePage = $row["url"];

echo $homePage;
?>