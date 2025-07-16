<?php
include_once("../config/config_mysqli.php");
$linkedTo = $_POST["linkedTo"];
//$linkedTo = "org1";
$reportList = mysqli_query($connect, "SELECT reportName, Id FROM report WHERE linkedTo = '$linkedTo'");
$rowsCount = mysqli_num_rows($reportList);
$count = 1;
echo "[";
while($row = mysqli_fetch_array($reportList))
{
	$data["id"] = $row["Id"];
	$data["Name"] = $row["reportName"];
	$list_data = json_encode($data);
	echo $list_data;
	if($count < $rowsCount) echo ",";
	$count++;
}
echo "]";
?>