<?php
ini_set('display_errors',0);
// Handle both web requests and direct PHP execution
$config_path = file_exists("../config/config_mysqli.php") ? "../config/config_mysqli.php" : "config/config_mysqli.php";
include_once($config_path);
	
$user_query="SELECT id, name, outcome, linkedObject FROM objective ORDER BY linkedObject";
$user_result = mysqli_query($GLOBALS["___mysqli_ston"], $user_query) or die("Could not query objective table");;

$row_count = mysqli_num_rows($user_result) or die("Could not count rows");
if ($row_count == null) exit;

//$data["Team"] = NULL;
$count = 1;
echo "{ \"identifier\": \"Objective\", \"label\": \"Objective\", \"items\": [";
while($row = mysqli_fetch_assoc($user_result))
{
	switch(substr($row["linkedObject"], 0, 3))
	{
		case "per":
		{
			$linkedObject = $row["linkedObject"];
			$parent = mysqli_query($GLOBALS["___mysqli_ston"], "Select organization.name, perspective.parentId FROM perspective, organization WHERE perspective.id = '$linkedObject' AND organization.id = perspective.parentId");
			$parent = mysqli_fetch_assoc($parent);
			$parent = $parent["name"];
			break;	
		}
		case "org":
		{
			$linkedObject = $row["linkedObject"];
			$parent = mysqli_query($GLOBALS["___mysqli_ston"], "Select name FROM organization WHERE id = '$linkedObject'");
			$parent = mysqli_fetch_assoc($parent);
			$parent = $parent["name"];
			break;	
		}	
	}
	
	$data["id"] = $count;
	$data["objectiveId"] = $row["id"];
	if($row["outcome"] != "")
	$data["Objective"] = "<b>".$parent."</b> ".$row["name"]." => <i>".$row["outcome"]."</i>";
	else
	$data["Objective"] = "<b>".$parent."</b> ".$row["name"];
	//if ($count == 1) $data["selected"] = "true";
	//else $data["User"] = $data["User"].", ".$row3["firstName"]." ".$row3["lastName"];
	$data = json_encode($data);
	echo $data;
	if($count<$row_count) echo ", ";
	$data = NULL;
	$count++;
}
echo "]}";
?>