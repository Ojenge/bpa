<?php
include_once("../config/config_mysqli.php");

// Create new id to save the initiative being added 
$initiative_id_result = mysqli_query($connect, "SELECT MAX(id) FROM initiative") or file_put_contents("errorInitiative.txt", "\t\n Cannot get initiative id with error => ".mysqli_error(), FILE_APPEND);;
$initiative_array = mysqli_fetch_array($initiative_id_result);
$initiative_id = $initiative_array[0] + 1;

$editInitiativeStatus = NULL;
$selectedInitiative = NULL;
$initiative_Link = NULL;
$initiative_Due = NULL;
$initiative_name = NULL;
$initiative_Type = 'Initiative';
$initiative_Parent = NULL;
$initiative_Start = NULL;
$initiative_Budget = NULL;
$initiative_Damage = NULL;
$initiative_Sponsor = NULL;
$initiative_Manager = NULL;
$initiative_Complete = NULL;
$initiative_deliverable = NULL;
$initiative_deliverableStatus = NULL;
$initiative_Score = NULL;
$initiative_Scope = NULL;
$initiative_Status = NULL;
$initiative_Percentage = NULL;
$initiative_Status_Details = NULL;
$initiativeNotes = NULL;

$initiativeLinkedTo = NULL;

if(!empty($_POST['initiativeLinkInput']))$initiativeLinkedTo = $_POST["initiativeLinkInput"];

if(!empty($_POST['editInitiativeStatus'])) $editInitiativeStatus = $_POST['editInitiativeStatus'];
if(!empty($_POST['selectedInitiative'])) $selectedInitiative = $_POST['selectedInitiative'];
//if(!empty($_POST['link_id']))$link_id = $_POST["link_id"];
if(!empty($_POST['initiative_Due']))$initiative_Due = $_POST["initiative_Due"];
if(!empty($_POST['initiative_name']))$initiative_name = $_POST["initiative_name"];

$initiative_name = mysqli_real_escape_string($connect, $initiative_name) ?? '';
//isset($_POST['tree_edit']) ? mysqli_real_escape_string($connect, $_POST['tree_edit']) : '';

if(!empty($_POST['initiative_Parent']))$initiative_Parent = $_POST["initiative_Parent"];
if(!empty($_POST['initiative_Type']))$initiative_Type = $_POST["initiative_Type"];
if(!empty($_POST['initiative_Link']))$initiative_Link = $_POST["initiative_Link"];

if(!empty($_POST['initiative_Start']))$initiative_Start = $_POST["initiative_Start"];
if(!empty($_POST['initiative_Budget']))$initiative_Budget = $_POST["initiative_Budget"];
if(!empty($_POST['initiative_Damage']))$initiative_Damage = $_POST["initiative_Damage"];
if(!empty($_POST['initiative_Sponsor']))$initiative_Sponsor = $_POST["initiative_Sponsor"];
if(!empty($_POST['initiative_Manager']))$initiative_Manager = $_POST["initiative_Manager"];
if(!empty($_POST['initiative_Complete']))$initiative_Complete = $_POST["initiative_Complete"];
if(!empty($_POST['initiative_deliverable']))$initiative_deliverable = $_POST["initiative_deliverable"];
if($initiative_deliverable != null) $initiative_deliverable = mysqli_real_escape_string($connect, $initiative_deliverable); else $initiative_deliverable = '';
if(!empty($_POST['initiative_deliverableStatus']))$initiative_deliverableStatus = $_POST["initiative_deliverableStatus"];
if(!empty($_POST['initiative_Score']))$initiative_Score = $_POST["initiative_Score"];
if(!empty($_POST['initiative_Scope']))$initiative_Scope = $_POST["initiative_Scope"];
if($initiative_Scope != null) $initiative_Scope = mysqli_real_escape_string($connect, $initiative_Scope); else $initiative_Scope = '';
if(!empty($_POST['initiative_Status']))$initiative_Status = $_POST["initiative_Status"];
if(!empty($_POST['initiative_Percentage']))$initiative_Percentage = $_POST["initiative_Percentage"];
if(!empty($_POST['initiative_Status_Details']))$initiative_Status_Details = $_POST["initiative_Status_Details"];
if($initiative_Status_Details != null) $initiative_Status_Details = mysqli_real_escape_string($connect, $initiative_Status_Details); else $initiative_Status_Details = '';
if(!empty($_POST['initiativeNotes']))$initiativeNotes = $_POST["initiativeNotes"];
if($initiativeNotes != null) $initiativeNotes = mysqli_real_escape_string($connect, $initiativeNotes); else $initiativeNotes = '';
if(!empty($_POST['initiative_Complete']))$initiative_Complete = date('Y-m-d',strtotime($initiative_Complete)); else $initiative_Complete = NULL;
if(!empty($_POST['initiative_Start']))$initiative_Start = date('Y-m-d',strtotime($initiative_Start)); else $initiative_Start = NULL;
if(!empty($_POST['initiative_Due']))$initiative_Due = date('Y-m-d',strtotime($initiative_Due)); else $initiative_Due = NULL;

// echo $initiativeLinkedTo; exit;

//file_put_contents("save.txt", "initiative_Link = $initiative_Link");

if($initiative_Complete != NULL || $initiative_Complete != "") //If Initiative Completion Date is provided, set the rest for the user if they forget to capture them accordingly.
{
	$initiative_Percentage = "100";
	$initiative_Status = "Completed";
}

if($editInitiativeStatus == "Edit")
{
	mysqli_query($connect, "UPDATE initiative SET 
	name = '$initiative_name', 
	sponsor = '$initiative_Sponsor', 
	projectManager = '$initiative_Manager', 
	budget=".($initiative_Budget == NULL ? "NULL" : "'$initiative_Budget'").", 
	damage=".($initiative_Damage == NULL ? "NULL" : "'$initiative_Damage'").", 
	startDate=".($initiative_Start == NULL ? "NULL" : "'$initiative_Start'").", 
	dueDate = ".($initiative_Due == NULL ? "NULL" : "'$initiative_Due'").", 
	completionDate = ".($initiative_Complete == NULL ? "NULL" : "'$initiative_Complete'").", 
	deliverable = '$initiative_deliverable', 
	deliverableStatus = '$initiative_deliverableStatus', 
	scope = '$initiative_Scope', 
	type = '$initiative_Type', 
	parent = '$initiative_Parent', 
	no_score = '$initiative_Score' 
	WHERE id = '$selectedInitiative'") or file_put_contents("saveInitiativeError.txt", "Error making an update => ".mysqli_error());
			
	$check = mysqli_query($connect, "SELECT initiativeid FROM initiativeimpact WHERE initiativeid = '$selectedInitiative'");
	$check = mysqli_num_rows($check);
	if($check < 1) mysqli_query($connect, "INSERT INTO initiativeimpact (initiativeid, linkedobjectid) VALUES ('$selectedInitiative', '$initiative_Link')");
	else mysqli_query($connect, "UPDATE initiativeimpact SET linkedobjectid = '$initiative_Link' WHERE initiativeid = '$selectedInitiative'") or file_put_contents("saveImpactError.txt", "Error making an update => ".mysqli_error());
	
	//Compare with most recent value and insert a new one if there is a change.
	$getLastStatus = mysqli_query($connect, "SELECT status, percentageCompletion, details, notes FROM initiative_status WHERE initiativeId = '$selectedInitiative' ORDER BY updatedOn DESC LIMIT 1") or file_put_contents("saveStatusError.txt", "Error making an update => ".mysqli_error());
	$getLastStatus = mysqli_fetch_array($getLastStatus);
	$lastStatus = $getLastStatus["status"];
	$lastPercentage = $getLastStatus["percentageCompletion"];
	$lastDetail = $getLastStatus["details"];
	$lastNote = $getLastStatus["notes"];
	if($initiative_Status == "" && $initiative_Status_Details == "" && $initiativeNotes == "")
	{
		//no need saving nothing	
	}
	else if($lastStatus == $initiative_Status && $lastPercentage == $initiative_Percentage && $lastDetail == $initiative_Status_Details && $lastNote == $initiativeNotes) //$lastPercentage was missing hence it wouldn't update initiative Percentage if it was the only one that was changed. LTK 06Jun2021 1312hrs
	{
		//no need to make any changes.	
	}
	else
	{
		date_default_timezone_set('Africa/Nairobi');
		$updateDate = date('Y-m-d H:i:s');
		
		mysqli_query($connect, "INSERT INTO initiative_status (initiativeId, status, percentageCompletion, details, notes, updatedOn, updatedBy)
		VALUES('$selectedInitiative','$initiative_Status', '$initiative_Percentage', '$initiative_Status_Details', '$initiativeNotes', '$updateDate', 'ind1')") or file_put_contents("errorInitiative.txt", "\t\n Cannot save initiative status with error => ".mysqli_error(), FILE_APPEND);	
	}	
	echo $selectedInitiative;
}
else
{
	date_default_timezone_set('Africa/Nairobi');
	$updateDate = date('Y-m-d H:i:s');
		
	mysqli_query($connect, "INSERT INTO initiative 
	(id, name, sponsor, projectManager, budget, damage, startDate, dueDate, completionDate, deliverable, deliverableStatus, scope, type, parent, no_score, archive, lastUpdated) 
	VALUES ('$initiative_id', '$initiative_name', '$initiative_Sponsor', '$initiative_Manager', ".($initiative_Budget == NULL ? "NULL" : "'$initiative_Budget'").", ".($initiative_Damage == NULL ? "NULL" : "'$initiative_Damage'").", ".($initiative_Start == NULL ? "NULL" : "'$initiative_Start'").", ".($initiative_Due == NULL ? "NULL" : "'$initiative_Due'").", ".($initiative_Complete == NULL ? "NULL" : "'$initiative_Complete'").", '$initiative_deliverable', '$initiative_deliverableStatus', '$initiative_Scope', '$initiative_Type', '$initiative_Parent', '$initiative_Score', 'No', '$updateDate')");
				
	mysqli_query($connect, "INSERT INTO initiativeimpact (initiativeid, linkedobjectid)
	VALUES('$initiative_id','$initiative_Link')");
	
	if(!empty($_POST['initiative_Status']) || !empty($_POST['initiative_Status_Details']))
	{
		//save only when either of status items is provided
		mysqli_query($connect, "INSERT INTO initiative_status (initiativeId, status, percentageCompletion, details, notes, updatedOn, updatedBy)
		VALUES('$initiative_id','$initiative_Status', '$initiative_Percentage', '$initiative_Status_Details', '$initiativeNotes', '$updateDate', 'ind1')");
	}
	echo $initiative_id;
}
?>