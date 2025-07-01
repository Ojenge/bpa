<?php
	include_once("../config/config_mysqli.php");

	$calendar = "Y-m-d";
	$content = $_POST['saveContent'];
	$id = $_POST['objectId'];
	$type = $_POST["Type"];
	$period = $_POST["period"];
	$creator = $_POST["creator"];
	$date = date($calendar);
	
	/*
	$calendar = "F";
	$content = "Test content 3";
	$id = "kpi1";
	$type = "wayForward";
	$date = date($calendar);
	*/
	$check_note = mysqli_query($connect, "SELECT objectId, date FROM note WHERE objectId = '$id' AND date = '$date'");
	if(mysqli_num_rows($check_note) > 0)
	{
		mysqli_query($connect, "UPDATE note SET $type = '$content' WHERE objectId = '$id' AND date = '$date'")or file_put_contents('editorSave.txt',"could not update editor on line 23 file save-editor-content.php",FILE_APPEND);	
	}
	else
	{
		if(strlen($content) == 0)
		{
			//no need to save empty comments;
		}
		else
		mysqli_query($connect, "INSERT INTO note (objectId, $type, period, date, creator) VALUES('$id','$content','$period','$date', '$creator')") or file_put_contents('editorSave.txt',"could not insert note on line 32 save-editor-content values '$id','$content','$period','$date', '$creator'".mysqli_error($connect),FILE_APPEND);
	}
?>

