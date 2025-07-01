<?php
include_once("config_mysqli.php");
$count = 1;
$obj = mysqli_query($connect, "SELECT * FROM objcommentary");
$num = mysqli_num_rows($obj);
echo "[";
while($row = mysqli_fetch_array($obj))
{
	$data["id"] = (int)$row["id"];
	$data["objName"] = $row["name"];
	$data["objOwner"] = $row["owner"];
	$data["objDescr"] = $row["description"];
	$data["objOutcome"] = $row["outcome"];
	$data["objFrom"] = $row["from"];
	$data["objTo"] = $row["to"];
	$data["objKpi"] = $row["measure"];
	$data["objInitiative"] = $row["initiative"];
	$data["objTarget"] = $row["target"];
	$data["objLinkedTo"] = $row["linkedTo"];
	$data = json_encode($data);
	echo $data;
	if($count < $num) echo ",";
	$data = NULL;
	$count++;
}
echo "]";
?>