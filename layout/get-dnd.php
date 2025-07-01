<?php
ini_set('display_errors',0);
include_once("../config/config_mysqli.php");
$kpis = mysqli_query($connect, "SELECT id, name, linkedObject FROM measure");
$count = 1;
$num_rows = mysqli_num_rows($kpis);
echo "[";
while($row = mysqli_fetch_array($kpis))
{
	$data["id"] = $count;
	$data["ObjId"] = $row["id"];
	$data["Name"] = $row["name"];
	$linkedObject = $row["linkedObject"];
	
	$obj = mysqli_query($connect, "SELECT id, name, linkedObject FROM objective WHERE id = '$linkedObject'");
	$obj_row = $obj ? mysqli_fetch_assoc($obj) : null;
	$data["Objective"] = ($obj_row && isset($obj_row["name"])) ? $obj_row["name"] : "None";
	$obj_id = ($obj_row && isset($obj_row["linkedObject"])) ? $obj_row["linkedObject"] : null;

	$persp = mysqli_query($connect, "SELECT id, name, parentId FROM perspective WHERE id = '$obj_id'");
	$persp_row = $persp ? mysqli_fetch_assoc($persp) : null;
	$data["Perspective"] = ($persp_row && isset($persp_row["name"])) ? $persp_row["name"] : "None";
	$persp_id = ($persp_row && isset($persp_row["parentId"])) ? $persp_row["parentId"] : null;

	$org = mysqli_query($connect, "SELECT name FROM organization WHERE id = '$persp_id'");
	$org_row = $org ? mysqli_fetch_assoc($org) : null;
	$data["Organization"] = ($org_row && isset($org_row["name"])) ? $org_row["name"] : "None";
	
	$data = json_encode($data);
	echo $data;
	$data = NULL;
	if($count < $num_rows) echo ", ";
	$count++;
}
echo "]";
?>