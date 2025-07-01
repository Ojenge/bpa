<?php
include_once("../../../config/config.php");

$projectId = $_POST["projectId"];
$type = $_POST["type"];

//$projectId = preg_replace('/[^0-9]/', '', $name);
//$temp = explode(".", $_FILES["initiativeEvidence"]["name"]);
//file_put_contents("filea.txt", "\nType = $type \nId = $projectId \nFile Name = $temp");

if (isset($_FILES["initiativeEvidence"])) 
{
	$fileName = $_FILES["initiativeEvidence"]['name'];
	
	file_put_contents("file.txt", "Type = $type \nId = $projectId \nFile Name = $fileName");
	
	$temp = explode(".", $_FILES["initiativeEvidence"]["name"]);
	$newFileName = round(microtime(true)) . '.' . end($temp);
	
	$fileLocation = "../../dataEntry/fileUploads/uploads/".$newFileName;//changing the file name to avoid duplicates menace.
	$fileSize = $_FILES["initiativeEvidence"]['size'];
    
	move_uploaded_file($_FILES["initiativeEvidence"]['tmp_name'], $fileLocation);
	
	$fileLocation = substr($fileLocation, 6); //move upload requires relative location but for thumbnails to occur, saved file location needs to be changes accordingly relative to file path. LTK 26.08.2018
	
	mysqli_query($connect, "INSERT INTO initiative_evidence (initiativeId, name, location, size, type) VALUES ('$projectId', '$fileName', '$fileLocation', '$fileSize', '$type')") or file_put_contents("uploadError.txt", "Can't save coz of ".mysqli_error());
}
echo "{}";
?>