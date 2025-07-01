<?php
include_once("config_mysqli.php");

if($_POST["delCommentary"] == "True")
{
	//file_put_contents("test.txt", "Deleted: ");
	$delId = $_POST["id"];
	mysqli_query($connect, "DELETE FROM objcommentary where id = '$delId'");
	$maxId=mysqli_query($connect, "SELECT MAX(id) AS id FROM objcommentary");
	$maxId=mysqli_fetch_array($maxId);
	$maxId = $maxId["id"];
	mysqli_query($connect, "UPDATE objcommentary SET id='$delId' WHERE id = '$maxId'");
	exit;	
}
$id = $_POST["id"];
$objName = $_POST["objName"];
$objPersp = $_POST["objPersp"];
$objOwner = $_POST["objOwner"];
$objTeam = $_POST["objTeam"];
$objDescr = $_POST["objDescr"];
$objOutcome = $_POST["objOutcome"];
$objFrom = $_POST["objFrom"];
$objTo = $_POST["objTo"];
$objKpi = $_POST["objKpi"];
$objTarget = $_POST["objTarget"];
$objInitiative = $_POST["objInitiative"];
$objLinkedTo = $_POST["objLinkedTo"];

if($_POST["edit"] == "True")
{
	echo "Update statement";	
}
else //save new commentary item
{
	mysqli_query($connect, "INSERT INTO objcommentary VALUES('$id','$objName','$objPersp','$objOwner','$objTeam','$objDescr','$objOutcome','$objFrom','$objTo', '$objKpi', '$objTarget', '$objInitiative', '$objLinkedTo')");
}
//file_put_contents("test.txt", $objLinkedTo);

//mysqli_query($connect, "INSERT INTO objective (id, name, owner, description, outcome, linkedObject) VALUES('$id','$objName','$objOwner','$objDescr','$objOutcome', '$objLinkedTo')");

echo "Objective Commentary Saved Successfully";
?>