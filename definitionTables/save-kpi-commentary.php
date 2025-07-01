<?php
include_once("config_mysqli.php");

if($_POST["delCommentary"] == "True")
{
	//file_put_contents("test.txt", "Deleted: ");
	$delId = $_POST["id"];
	mysqli_query($connect, "DELETE FROM kpicommentary where id = '$delId'");
	$maxId=mysqli_query($connect, "SELECT MAX(id) AS id FROM kpicommentary");
	$maxId=mysqli_fetch_array($maxId);
	$maxId = $maxId["id"];
	mysqli_query($connect, "UPDATE kpicommentary SET id='$delId' WHERE id = '$maxId'");
	exit;	
}

$id = $_POST["id"];
$kpiName = $_POST["kpiName"];
$kpiQuantitative = $_POST["kpiQuantitative"];
$dateCreated = $_POST["dateCreated"];
$defStatus = $_POST["defStatus"];
$dateModified = $_POST["dateModified"];
$reportStatus = $_POST["reportStatus"];
$abbreviation = $_POST["abbreviation"];
$lifePriority = $_POST["lifePriority"];
$kpiIntegrity = $_POST["kpiIntegrity"];
$kpiLevel = $_POST["kpiLevel"];
$kpiGoal = $_POST["kpiGoal"];
$kpiUnit = $_POST["kpiUnit"];
$kpiDescr = $_POST["kpiDescr"];
$kpiIntent = $_POST["kpiIntent"];
$kpiProcess = $_POST["kpiProcess"];
$kpiStakeholder = $_POST["kpiStakeholder"];
$kpiRelationship = $_POST["kpiRelationship"];
$kpiFormula = $_POST["kpiFormula"];
$kpiFrequency = $_POST["kpiFrequency"];
$kpiDrill = $_POST["kpiDrill"];
$kpiComparison = $_POST["kpiComparison"];
$kpiMethod = $_POST["kpiMethod"];
$kpiPresentNotes = $_POST["kpiPresentNotes"];
$kpiFrequency2 = $_POST["kpiFrequency2"];
$kpiResponse = $_POST["kpiResponse"];
$kpiOwnerDefinition = $_POST["kpiOwnerDefinition"];
$kpiOwnerPerformance = $_POST["kpiOwnerPerformance"];
$kpiNotes = $_POST["kpiNotes"];
$kpiOwnerReporting = $_POST["kpiOwnerReporting"];

if($_POST["edit"] == "True")
{
	echo "Update statement";	
}
else //save new commentary item
{
	mysqli_query($connect, "INSERT INTO kpicommentary VALUES(	
	'$id',
	'$kpiName',
	'$kpiQuantitative',
	'$dateCreated',
	'$defStatus',
	'$dateModified',
	'$reportStatus',
	'$abbreviation',
	'$lifePriority',
	'$kpiIntegrity',
	'$kpiLevel',
	'$kpiGoal',
	'$kpiUnit',
	'$kpiDescr',
	'$kpiIntent',
	'$kpiProcess',
	'$kpiStakeholder',
	'$kpiRelationship',
	'$kpiFormula',
	'$kpiFrequency',
	'$kpiDrill',
	'$kpiComparison',
	'$kpiMethod',
	'$kpiPresentNotes',
	'$kpiFrequency2',
	'$kpiResponse',
	'$kpiOwnerDefinition',
	'$kpiOwnerPerformance',
	'$kpiNotes',
	'$kpiOwnerReporting')");
}
//file_put_contents("test.txt", $objLinkedTo);

//mysqli_query($connect, "INSERT INTO measure (id, name, owner, description, outcome, linkedObject) VALUES('$id','$objName','$objOwner','$objDescr','$objOutcome', '$objLinkedTo')");

echo "Measure Definition Saved Successfully";
?>