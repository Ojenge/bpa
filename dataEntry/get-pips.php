<?php
error_reporting(0); //To show all - change 0 to E_ALL
ini_set('display_errors', 0); //To show all - change 0 to 1
require_once("../admin/models/config.php");
include_once("../config/config_mysqli.php");

@$objectId = $_POST['objectId'];
//@$objectPeriod = $_POST['objectPeriod'];
//@$objectDate = $_POST['objectDate'];

//file_put_contents("track.txt", "objectId = $objectId");

//$pip_query="SELECT * FROM pdp WHERE indId = '$objectId' AND archive != 'Yes'"; //You will need to archive these later. LTK 06May2021 0810 Hours
$pip_query="SELECT * FROM pdp WHERE indId = '$objectId'";
$pip_result = mysqli_query($GLOBALS["___mysqli_ston"], $pip_query);
$pip_count = mysqli_num_rows($pip_result);

$currentUser = "ind".$loggedInUser->user_id;
echo "<div class='border border-primary rounded-3' style='overflow:hidden;'><table class='table table-hover table-responsive table-bordered table-sm table-condensed table-striped'>";
	echo "<tr class='table-primary'>";
		echo "<th>Competency/Skill Gap</th>";
		echo "<th>Intervention</th>";
		echo "<th style='text-align:center; white-space:nowrap;'>Start Date</th>";
		echo "<th colspan='2' style='text-align:center; white-space:nowrap;'>Due Date</th>";
		echo "<th>Resource</th>";
		echo "<th>Comments</th>";
		if($objectId == $currentUser) echo "<th></th>";
	echo "</tr>";
echo "<tbody>";
$pipCounter = 1;
while($row = mysqli_fetch_assoc($pip_result))
{
	$id = $row["id"];
	echo "<tr>";
	//echo "<td>".$pipCounter."</td>";
	echo "<td>".$row["skillGap"]."</td>";
	echo "<td>".$row["intervention"]."</td>";
	if($row["startDate"] == NULL || $row["startDate"] == '0000-00-00' || $row["startDate"] == '1970-01-01')
	{
		$color = "grey3d";
		echo "<td class='border-end-0'><div class='$color'></div></td><td class='border-start-0' style='text-align:center; white-space:nowrap;'>No Start Date</td>";
	}
	else
	{
		$startDate = date("d M Y",strtotime($row["startDate"]));
		echo "<td class='border-start-0' style='text-align:center; white-space:nowrap;'>".$startDate."</td>";
	}
	if($row["dueDate"] <= date("Y-m-d") && $row["completionDate"] == NULL)
	$color = "red3d";
	
	else if ($row["dueDate"] < $row["completionDate"] && $row["completionDate"] != NULL)
	$color = "yellow3d";
	
	else if ($row["completionDate"] <= $row["dueDate"] && $row["completionDate"] != NULL)
	$color = "green3d";
	
	else $color = "grey3d";
	
	if($row["dueDate"] == NULL || $row["dueDate"] == '0000-00-00' || $row["dueDate"] == '1970-01-01')
	{
		$color = "grey3d";
		echo "<td class='border-end-0'><div class='$color'></div></td><td class='border-start-0' style='text-align:center; white-space:nowrap;'>No Due Date</td>";
	}
	else
	{
		$dueDate = date("d M Y",strtotime($row["dueDate"]));
		echo "<td class='border-end-0'><div class='$color'></div></td><td class='border-start-0' style='text-align:center; white-space:nowrap;'>".$dueDate."</td>";
	}
	echo "<td>".$row["resource"]."</td>";
	echo "<td>".$row["comments"]."</td>";
	if($objectId == $currentUser) echo "<td><a href='#' onClick='editPip(".$row["id"].")'>Update</a></td>";
}
echo "</tbody>";
echo "</table></div>";
?>