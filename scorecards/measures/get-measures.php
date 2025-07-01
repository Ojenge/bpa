<?php
//ini_set('dilay_errors',0);
include_once("../../config/config_mysqli.php");

$user_query="SELECT id, name, linkedObject FROM measure";
$user_result = mysqli_query($connect, $user_query) or die("Could not query measure table");

$row_count = mysqli_num_rows($user_result) or die("Could not count rows");
if ($row_count == null) exit;

$count = 1;
echo "{ \"identifier\": \"Measure\", \"label\": \"Measure\", \"items\": [";
while($row = mysqli_fetch_assoc($user_result))
{
	$data["id"] = $count;
	$data["kpiId"] = $row["id"];
	
	$linkedObject = $row["linkedObject"];
	
	switch(substr($linkedObject, 0, 3))
	{
		case "kpi":
		{
			$parentQuery = "SELECT name FROM measure WHERE id = '$linkedObject'";
			break;	
		}
		case "obj":
		{
			$parentQuery = "SELECT name FROM objective WHERE id = '$linkedObject'";
			break;	
		}
		case "persp":
		{
			$parentQuery = "SELECT name FROM perspective WHERE id = '$linkedObject'";
			break;	
		}
		case "org":
		{
			$parentQuery = "SELECT name FROM organization WHERE id = '$linkedObject'";
			break;	
		}
		case "ind":
		{
			$parentQuery = "SELECT display_name AS name FROM uc_users WHERE user_id = '$linkedObject'";
			break;	
		}	
	}
	
	$parentResult = mysqli_query($connect, $parentQuery) or die("Could not query parent table");
	$parent = mysqli_fetch_assoc($parentResult);
	
	$data["Measure"] = $row["name"];
	$data["Parent Measure"] = "[".$parent["name"]."] ".$row["name"];
	
	$data = json_encode($data);
	echo $data;
	if($count<$row_count) echo ", ";
	$data = NULL;
	$count++;
}
echo "]}";
?>