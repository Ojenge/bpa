<?php
include_once("../config/config_mysqli.php");

$projects = @mysqli_query($connect, "SELECT * FROM dlp_moi WHERE longitude != ''") or die("Error => ".mysqli_error());
$projectCount = @mysqli_num_rows($projects);
$whileCount = 1;
$coordinateId = 0;
echo "[";
while($row = mysqli_fetch_array($projects))
{
	$data["id"] = $coordinateId;
	$data["county"] = $row["county"];
	$data["subCounty"] = $row["subCounty"];
	$data["school"] = $row["school"];
	$data["category"] = "-";
	$data["zone"] = $row["zone"];
	$data["tabletsDelivered"] = $row["ldd"];
	$data["totalEnrollment"] = "-";
	$data["longitude"] = (float)$row["longitude"];
	$data["latitude"] = (float)$row["latitude"];

	if($whileCount == $projectCount) echo json_encode($data);
	else echo json_encode($data).",";
	$whileCount++;
	$coordinateId++;
	$data = NULL;
}
echo "]";
?>