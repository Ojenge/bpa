<?php
error_reporting(0); //To show all - change 0 to E_ALL
ini_set('display_errors', 0); //To show all - change 0 to 1
//require_once("../models/config.php");
include_once("../config_msqli.php");

@$objectId = $_POST['objectId'];
@$objectDate = $_POST['objectDate'];

//@$objectId = "ind1";
//@$objectDate = "2021-07";

if(strlen($objectDate) == 7) $objectDate = $objectDate."-30";
if(strlen($objectDate) == 4) $objectDate = $objectDate."-12-30";

//file_put_contents("track.txt", "objectId = $objectId, objectDate = $objectDate");

$query = "SELECT interpretation FROM note WHERE objectId = '$objectId' AND date <= '$objectDate%' ORDER BY date DESC LIMIT 1";
$result = mysqli_query($GLOBALS["___mysqli_ston"], $query) or file_put_contents("error.txt", "Error => ".mysqli_error($GLOBALS["___mysqli_ston"]));

$row = mysqli_fetch_assoc($result) or file_put_contents("error.txt", "Error => ".mysqli_error($GLOBALS["___mysqli_ston"]));

if(mysqli_num_rows($result) > 0)
echo $row["interpretation"];

else echo "";

//file_put_contents("track.txt", "objectId = $objectId, objectDate = $objectDate, note = ".$row["interpretation"]);

?>