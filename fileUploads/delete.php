<?php
include_once("../config/config_msqli.php");

$id = $_POST["key"];

$getLocation = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT location FROM initiative_evidence WHERE id = '$id'");
$getLocation = mysqli_fetch_assoc($getLocation);
$fileLocation = $getLocation ["location"];

mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM initiative_evidence WHERE id = '$id'") or file_put_contents("uploadError.txt", "Can't delete coz of ".mysqli_error());

$prefix = '../../../fileUploads/';

if (substr($fileLocation, 0, strlen($prefix)) == $prefix) 
{
	//$fileLocation = substr($fileLocation, strlen($prefix));
}

//file_put_contents("unlink.txt", "File location is ". $fileLocation);
unlink($fileLocation);

echo "{}";
?>