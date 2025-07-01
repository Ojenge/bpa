<?php 
include_once("../config/config_mysqli.php");
$userId = $_POST['userId'];
$kpiGlobalId = $_POST['kpiGlobalId'];
$kpiGlobalType = $_POST['kpiGlobalType'];
$mainMenuState = $_POST['mainMenuState'];
$bookMarkName = $_POST['bookMarkName'];

mysqli_query($connect, "INSERT INTO bookmark VALUES('', '$bookMarkName', '$mainMenuState','$kpiGlobalType', '$kpiGlobalId', '$userId')");
?>