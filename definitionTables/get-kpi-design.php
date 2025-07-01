<?php
include_once("config_mysqli.php");
$count = 1;
$obj = mysqli_query($connect, "SELECT * FROM kpidesign");
$num = mysqli_num_rows($obj);
echo "[";
while($row = mysqli_fetch_array($obj))
{
	$data["id"] = (int)$row["id"];
	$data["objNameD"] = $row["objName"];
	$data["sensory"] = $row["sensory"];
	$data["potential"] = $row["potential"];
	$data["picture"] = $row["picture"];
	$data["kpiName"] = $row["kpiName"];
	$data = json_encode($data);
	echo $data;
	if($count < $num) echo ",";
	$data = NULL;
	$count++;
}
echo "]";
?>