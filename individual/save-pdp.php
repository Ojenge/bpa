<?php
include_once("../config/config_mysqli.php");

if(!empty($_POST['userId'])) $userId = $_POST["userId"]; 
else //get logged in user since pdp can now be added away from scorecard tree. Assumption here is one is creating their own PIP.
{
	require_once("admin/models/config.php");
	$userId = "ind".$loggedInUser->user_id;
};

if(!empty($_POST['pdpSkillGapInput'])) $pdpSkillGapInput = $_POST["pdpSkillGapInput"]; else $pdpSkillGapInput = NULL;
if(!empty($_POST['pdpInterventionInput'])) $pdpInterventionInput = $_POST["pdpInterventionInput"]; else $pdpInterventionInput = NULL;
if(!empty($_POST['pdpCommentsInput'])) $pdpCommentsInput = $_POST["pdpCommentsInput"]; else $pdpCommentsInput = NULL;
if(!empty($_POST['pdpResourceInput'])) $pdpResourceInput = $_POST["pdpResourceInput"]; else $pdpResourceInput = NULL;

if(!empty($_POST['pdpStartInput'])) $pdpStartInput = date('Y-m-d',strtotime($_POST["pdpStartInput"])); else $pdpStartInput = NULL;
if(!empty($_POST['pdpDueInput'])) $pdpDueInput = date('Y-m-d',strtotime($_POST["pdpDueInput"])); else $pdpDueInput = NULL;
if(!empty($_POST['pdpCompleteInput'])) $pdpCompleteInput = date('Y-m-d',strtotime($_POST["pdpCompleteInput"])); else $pdpCompleteInput = NULL;

if(!empty($_POST['toEdit']) && $_POST['toEdit'] == "Edit")
{
	$pdpId = $_POST['pdpId'];
	mysqli_query($connect, "UPDATE pdp SET 
	skillGap = '$pdpSkillGapInput', 
	intervention = '$pdpInterventionInput', 
	startDate=".($pdpStartInput == NULL ? "NULL" : "'$pdpStartInput'").", 
	dueDate = ".($pdpDueInput == NULL ? "NULL" : "'$pdpDueInput'").", 
	completionDate = ".($pdpCompleteInput == NULL ? "NULL" : "'$pdpCompleteInput'").", 
	resource = '$pdpResourceInput', 
	comments = '$pdpCommentsInput' WHERE id = '$pdpId'") or file_put_contents("pdp.txt", "Could Not Edit PDP for: ".$pdpId." with error => ".mysqli_error()); 
}
else if(!empty($_POST['toEdit']) && $_POST['toEdit'] == "Delete")
{
	$pdpId = $_POST['pdpId'];
	mysqli_query($connect, "DELETE FROM pdp WHERE id = '$pdpId'");
}
else
{
$id = mysqli_query($connect, "SELECT MAX(id) AS id FROM pdp");
$id = mysqli_fetch_assoc($id);
$id = $id["id"] + 1;

//file_put_contents("apdp.txt", "$id, $userId, $pdpSkillGapInput, $pdpInterventionInput, $pdpStartInput, $pdpDueInput, $pdpResourceInput, $pdpCommentsInput");

mysqli_query($connect, "INSERT INTO pdp 
VALUES('$id', '$userId', '$pdpSkillGapInput', '$pdpInterventionInput', 
".($pdpStartInput == NULL ? "NULL" : "'$pdpStartInput'").", 
".($pdpDueInput == NULL ? "NULL" : "'$pdpDueInput'").", 
".($pdpCompleteInput == NULL ? "NULL" : "'$pdpCompleteInput'").", 
'$pdpResourceInput', '$pdpCommentsInput', 'No', CURRENT_TIMESTAMP())") or file_put_contents("pdp.txt", "Could Not Save PDP for: ".$id." with error => ".mysqli_error());;
}
?>