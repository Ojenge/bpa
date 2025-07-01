<?php
include_once("../config/config.php");

$bottleneck_id_result = mysqli_query($connect, "SELECT MAX(id) FROM bottleneck");
$bottleneck_array = mysqli_fetch_array($bottleneck_id_result);
$bottleneck_id = $bottleneck_array[0] + 1;

$editBottleneckStatus = NULL;
$selectedBottleneck = NULL;
$initiativeId = NULL;

$issue = NULL;
$wayForward = NULL;
$severity = NULL;
$status = NULL;
$owner = NULL;
date_default_timezone_set('Africa/Nairobi');
$updatedOn = date('Y-m-d H:i:s');
$updatedBy = NULL;

if(!empty($_POST['editBottleneckStatus'])) $editBottleneckStatus = $_POST['editBottleneckStatus'];
if(!empty($_POST['selectedBottleneck'])) $selectedBottleneck = $_POST['selectedBottleneck'];
if(!empty($_POST['initiativeId'])) $link_id = $_POST["initiativeId"];
if(!empty($_POST['issue'])) $issue = $_POST["issue"];
if(!empty($_POST['wayForward'])) $wayForward = $_POST["wayForward"];
if(!empty($_POST['severity'])) $severity = $_POST["severity"];
if(!empty($_POST['status'])) $status = $_POST["status"];
if(!empty($_POST['owner'])) $owner = $_POST["owner"];
if(!empty($_POST['updatedBy'])) $updatedBy = $_POST["updatedBy"];

if($editBottleneckStatus == "true")
{
	mysqli_query($connect, "UPDATE initiative_issue SET 
	issue = '$issue', 
	wayForward = '$wayForward', 
	severity = '$severity', 
	status = '$status', 
	owner = '$owner', 
	updatedOn = '$updatedOn', 
	updatedBy = '$updatedBy', 
	WHERE id = '$selectedBottleneck'");

	echo $selectedBottleneck;
}
else
{
	mysqli_query($connect, "INSERT INTO initiative_issue 
	(id, initiativeId, issue, wayForward, owner, status, severity, updatedBy, updatedOn) 
	VALUES ('$bottleneckId', '$initiativeId', '$issue', '$wayForward', '$owner', '$status', '$severity', '$updatedBy', '$updatedOn'");
}
?>