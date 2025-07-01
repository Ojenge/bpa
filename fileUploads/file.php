<?php
include_once("../config/config_msqli.php");

$name = $_POST["inputName"];

$projectId = preg_replace('/[^0-9]/', '', $name);

if (isset($_FILES[$name])) 
{	
	$fileName = $_FILES[$name]['name'][0];
	
	$temp = explode(".", $_FILES[$name]["name"][0]);
	$newFileName = round(microtime(true)) . '.' . end($temp);
	
	$fileLocation = "uploads/".$newFileName;//changing the file name to avoid duplicates menace.
	$fileSize = $_FILES[$name]['size'][0];
	$fileLocationMysql = "https://haco.accent-analytics.com/fileUploads/".$fileLocation;
    
	$path = $_FILES[$name]['name'][0];
	$ext = pathinfo($path, PATHINFO_EXTENSION);
	//$ext = 'jpg';
	$type = "image";
	switch($ext)
	{
		case "pdf":
		{
			$type = "pdf";
			break;	
		}
		case "xls":
		{
			$type = "office";
			break;	
		}
		case "xlsx":
		{
			$type = "office";
			break;	
		}
		case "doc":
		{
			$type = "office";
			break;	
		}
		case "ppt":
		{
			$type = "office";
			break;	
		}
		default:
		{
			$type = "image";
			break;	
		}
	}
	
	move_uploaded_file($_FILES[$name]['tmp_name'][0], $fileLocation);
	
	mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO initiative_evidence (initiativeId, name, location, size, type) 
	VALUES ('$projectId', '$fileName', '$fileLocationMysql', '$fileSize', '$type')") or file_put_contents("uploadError.txt", "Can't save with error => ".mysqli_error());
}
echo "{}";
?>