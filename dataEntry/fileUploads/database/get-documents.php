<?php
include_once("../../config/config.php");

$project_result = mysqli_query($connect, "SELECT id FROM initiative WHERE id IN ('1', '2')") or file_put_contents("evidence.txt", "Error => ".mysqli_error());
$project_count = mysqli_num_rows($project_result);
$count = 1;
echo "[";
while($row = mysqli_fetch_assoc($project_result))
{
	$fileId = "project".$row["id"];
	$projectId = $row["id"];
	$data["id"] = $fileId;
	$data["fileUpload"] = '<form enctype="multipart/form-data"><div class="file-loading"><input id="'.$fileId.'" name="'.$fileId.'" type="file" multiple></div></form>';
	
	$evidence_result = mysqli_query($connect, "SELECT * FROM initiative_evidence WHERE initiativeId = '$projectId'") or file_put_contents("evidence.txt", "Error => ".mysqli_error());
	$evidenceCount = mysqli_num_rows($evidence_result);
	$countTwo = 0;
	$documents = array();
	while($rowEvidence = mysqli_fetch_assoc($evidence_result))
	{
		$evidenceData["documentId"] = $rowEvidence["id"];
		$evidenceData["documentName"] = $rowEvidence["name"];
		$evidenceData["documentLocation"] = $rowEvidence["location"];
		$evidenceData["documentSize"] = $rowEvidence["size"];
		//if($countTwo < $project_count) $documents[] = json_encode($evidenceData).",";
		$documents[$countTwo] = $evidenceData;
		$evidenceData = NULL;
		$countTwo++;
	}
	$data["documents"] = $documents;
	echo json_encode($data);
	if($count < $project_count) echo ",";
	$data = NULL;
	$count++;
}
echo "]";
?>