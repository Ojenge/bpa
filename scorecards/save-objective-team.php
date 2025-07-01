<?php
include_once("../config/config_mysqli.php");

$userId = $_POST["userId"];
$objectiveId = $_POST["objectiveId"];

//$userId = 'ind1';
//$objectiveId = 'persp1';

$query = mysqli_query($connect, "SELECT userId, objectiveId FROM objectiveteam WHERE userId = '$userId' AND objectiveId = '$objectiveId'");
if(mysqli_num_rows($query) > 0)
{
	mysqli_query($connect, "DELETE FROM objectiveteam WHERE userId = '$userId' AND objectiveId = '$objectiveId'");
	$query = mysqli_query($connect, "SELECT DISTINCT uc_users.display_name AS user FROM uc_users, objectiveteam 
	WHERE uc_users.user_id = objectiveteam.userId AND objectiveteam.objectiveId = '$objectiveId'");
	while($row = mysqli_fetch_assoc($query))
	{
		echo $row["user"].", ";
	}
}
else 
{
	mysqli_query($connect, "INSERT INTO objectiveteam (userId, objectiveId) VALUES ('$userId', '$objectiveId')");
	
	$query = mysqli_query($connect, "SELECT DISTINCT uc_users.display_name AS user FROM uc_users, objectiveteam 
	WHERE uc_users.user_id = objectiveteam.userId AND objectiveteam.objectiveId = '$objectiveId'");
	while($row = mysqli_fetch_assoc($query))
	{
		echo $row["user"].", ";
	}
}
?>