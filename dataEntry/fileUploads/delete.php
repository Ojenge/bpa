<?php
error_reporting(0); //To show all - change 0 to E_ALL
ini_set('display_errors', 0); //To show all - change 0 to 1

include_once("../../config.php");

$id = $_POST["key"];

$getLocation = mysqli_query($connect, "SELECT location FROM initiative_evidence WHERE id = '$id'");
$getLocation = mysqli_fetch_assoc($getLocation);
$fileLocation = $getLocation ["location"];
	
unlink($fileLocation);
mysqli_query($connect, "DELETE FROM initiative_evidence WHERE id = '$id'") or file_put_contents("uploadError.txt", "Can't delete coz of ".mysqli_error());

echo "{}";
?>