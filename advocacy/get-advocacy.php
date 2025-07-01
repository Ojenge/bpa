<?php
include_once("config_mysqli.php");
include_once("functionsAdvocacy.php");
//if(isset($_POST['objectId']))
//{
	@$advocacyId = $_POST['advocacyId'];
	//$advocacyId = 1;
	//$objectType = $_POST["objectType"].$calendar;
	//$objectType = "measuremonths";
	$initiative_query="SELECT * FROM advocacy WHERE id = '$advocacyId'";
	$initiative_result=mysqli_query($connect, $initiative_query);
	$row_count = mysqli_num_rows($initiative_result);
	if ($row_count == NULL) exit;

	$row = mysqli_fetch_assoc($initiative_result);
	$data["id"] = $row["id"];
	$data["name"] = $row["name"];
	$ownerId = $row["owner"];
	
	$manager_result = mysqli_query($connect, "SELECT display_name FROM uc_users WHERE user_id = '$ownerId'");
	$manager_row = @mysqli_fetch_assoc($manager_result);
	$data["owner"] = $manager_row['display_name'];
	
	$data["ownerId"] = $ownerId;
	$data["category"] = $row["category"];
	$data["type"] = $row["type"];
	
	
	if($row["dueDate"] < date("Y-m-d") && $row["completionDate"] == NULL) $data["initiativeColor"] = "red";
	else if ($row["dueDate"] > date("Y-m-d") && $row["completionDate"] == NULL) $data["initiativeColor"] = "#ffd900";
	else $data["initiativeColor"] = "green";
	
	if($row['startDate']!= NULL) $data["startDate"] = date('d-M-Y',strtotime($row['startDate'])); else $data["startDate"] = '';
	if($row['dueDate']!= NULL) $data["dueDate"] = date('d-M-Y',strtotime($row['dueDate'])); else $data["dueDate"] = '';
	if($row['completionDate']!= NULL) $data["completionDate"] = date('d-M-Y',strtotime($row['completionDate'])); else $data["completionDate"] = '';
		
	$data["status"] = $row["status"];
	$data["color"] = getAdvocacyColor($row["status"]);
	$data["agency"] = $row["agency"];
	$data["meaning"] = $row["meaning"];
	$data = json_encode($data);
	echo $data;
	//$data = null;
	
	//echo $data["Link"];
	flush();
//}
exit;
?>