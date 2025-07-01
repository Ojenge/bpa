<?php
include_once("../config/config_mysqli.php");

$treeId = $_POST["treeId"];

//$userId = 'ind1';
//$objectiveId = 'persp1';

mysqli_query($connect, "DELETE FROM objectiveteam WHERE objectiveId = '$treeId'");
?>