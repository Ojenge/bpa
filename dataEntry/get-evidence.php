<?php
include_once("../config/config_mysqli.php");

$count = 1;
echo "[";
$projectId = $_POST["projectId"];

//$projectId = "21071";

$evidence_result = mysqli_query($connect, "SELECT * FROM initiative_evidence WHERE initiativeId = '$projectId'") or file_put_contents("evidence.txt", "Error => ".mysqli_error($connect));
$evidenceCount = mysqli_num_rows($evidence_result);

$documents = array();
while($rowEvidence = mysqli_fetch_assoc($evidence_result))
{
	$data["documentId"] = $rowEvidence["id"];
	$data["documentName"] = $rowEvidence["name"];
	$data["documentLocation"] = $rowEvidence["location"];
	$data["documentSize"] = $rowEvidence["size"];
		
	echo json_encode($data);
	if($count < $evidenceCount) echo ",";
	$data = NULL;
	$count++;
}
echo "]";
?>