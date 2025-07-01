<?
include_once("../config/config_mysqli.php");

$objectId = $_POST["objectId"];
$globalDate = $_POST["globalDate"];
$measureId = $_POST["measureId"];
$measureScore = $_POST["measureScore"];

//file_put_contents("reviewedScore.txt", "objectId = $objectId; globalDate = $globalDate; measureId = $measureId; measureScore = $measureScore");
date_default_timezone_set('Africa/Nairobi');
$date = date('Y-m-d');

mysqli_query($connect, "INSERT INTO supervisor_score (id, object_id, supervisor_id, score, period) VALUES ('', '$measureId', '$objectId', '$measureScore', '$globalDate')");
?>