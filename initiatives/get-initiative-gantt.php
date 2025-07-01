<?php
include('../config/config_msqli.php');
require_once("../admin/models/config.php");

date_default_timezone_set('Africa/Nairobi');

if($_POST['userId'] != "defaultUser") $userId = $_POST['userId'];
else $userId = $loggedInUser->user_id;

$data = array();
$projectQuery = "SELECT initiative.id, initiative.name, initiative.startDate, initiative.dueDate, uc_users.photo, uc_users.display_name 
FROM initiative, uc_users
WHERE initiative.projectManager = '$userId'
AND uc_users.user_id = '$userId'
AND initiative.startDate IS NOT NULL
AND initiative.dueDate IS NOT NULL";
$projectResult = mysqli_query($GLOBALS["___mysqli_ston"], $projectQuery);
$count = 0;
while($row = mysqli_fetch_assoc($projectResult))
{
	$data[$count]["startYear"] = date("Y",strtotime($row["startDate"]));
	$data[$count]["startMonth"] = date("m",strtotime($row["startDate"]));
	$data[$count]["startDay"] = date("d",strtotime($row["startDate"]));
	$data[$count]["endYear"] = date("Y",strtotime($row["dueDate"]));
	$data[$count]["endMonth"] = date("m",strtotime($row["dueDate"]));
	$data[$count]["endDay"] = date("d",strtotime($row["dueDate"]));
	$data[$count]["name"] = $row["name"];
	$data[$count]["owner"] = $row["display_name"];
	$data[$count]["assignee"] = $row["photo"];
	
	$id = $row["id"];
	$completed = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT percentageCompletion FROM initiative_status WHERE initiativeId = '$id' ORDER BY updatedOn DESC LIMIT 1");
	$completed = mysqli_fetch_assoc($completed);
	$data[$count]["completed"]  = $completed["percentageCompletion"];
	$count++;
}

print_r(json_encode($data));
?>