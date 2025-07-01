<?php
include_once("../../config/config_msqli.php");

$objectId = $_POST["objectId"];
//$objectId = "393";

$objectId = preg_replace("/[^0-9]/", "", $objectId );//Remove non numeric characters

/*$project_result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT 
initiative.id AS initiativeId, 
initiative_evidence.id AS evidenceId, 
initiative_evidence.name, 
initiative_evidence.location, 
initiative_evidence.size,
initiative_evidence.type
FROM initiative, initiative_evidence
WHERE initiative_evidence.initiativeId = initiative.id
AND initiative.id = '$objectId'
") or file_put_contents("evidence.txt", "Error => ".mysqli_error());*/

$project_result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT 
initiative_evidence.id AS evidenceId, 
initiative_evidence.name, 
initiative_evidence.location, 
initiative_evidence.size,
initiative_evidence.type
FROM initiative_evidence
WHERE initiativeId = '$objectId'
") or file_put_contents("evidence.txt", "Error => ".mysqli_error());

//$project_count = mysqli_num_rows($project_result);
$count = 0;
$fileId = "project".$objectId;
$data["id"] = $fileId;
$data["fileUpload"] = '<div class="file-loading"><input id="'.$fileId.'" name="'.$fileId.'[]" type="file"></div>';

$documents = array();

while($row = mysqli_fetch_assoc($project_result))
{
	$evidenceData["documentId"] = $row["evidenceId"];
	$evidenceData["documentName"] = $row["name"];
	$evidenceData["documentLocation"] = $row["location"];
	$evidenceData["documentSize"] = $row["size"];
	$evidenceData["documentType"] = $row["type"];

	$documents[$count] = $evidenceData;
	$evidenceData = NULL;
	
	$count++;
}
$data["documents"] = $documents;
echo json_encode($data);
?>