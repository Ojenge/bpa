<?php
include('../config/config_msqli.php');
include('../functions/functions-rights.php');
//header('Content-Type: application/json');

//$filter = $_POST['filter'];
require_once("../admin/models/config.php");
$userPermission = fetchUserPermissions($loggedInUser->user_id);
$role = "";
$userId = "ind".$loggedInUser->user_id;
$idsArray = getUserIDs($userPermission, $role, $userId);

$ids = join("','",$idsArray);
$ids = $ids."', 'ind".$loggedInUser->user_id; //Include your own PIPs!


// Get the list of initiatives as saved
$project_query="SELECT * FROM pdp WHERE indId IN ('$ids') ORDER BY lastUpdated DESC";
$project_result = mysqli_query($GLOBALS["___mysqli_ston"], $project_query) or file_put_contents("aError.txt", "Error => ".mysqli_error());
$project_count = mysqli_num_rows($project_result);

$count = 1;


echo "[";
while($row = mysqli_fetch_assoc($project_result))
{
	$data["id"] = $row["indId"];
	$data["s_no"] = $row["id"];
	$data["skillGap"] = $row["skillGap"];
	$data["intervention"] = $row["intervention"];
	if($row["startDate"] == NULL) $data["startDate"] = "";
	else $data["startDate"] = date("d M Y", strtotime($row["startDate"]));
	if($row["dueDate"] == NULL) $data["dueDate"] = "";
	else $data["dueDate"] = date("d M Y", strtotime($row["dueDate"]));
	if($row["completionDate"] == NULL) $data["completionDate"] = "";
	else $data["dueDcompletionDateate"] = date("d M Y", strtotime($row["completionDate"]));
		
		$owner = $row["indId"];
		$owner = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT display_name FROM uc_users WHERE user_id = '$owner'");
		$owner = mysqli_fetch_assoc($owner);
	$data["owner"] = $owner["display_name"];;
	$data["resource"] = $row["resource"];
	$data["comments"] = $row["comments"];
	if($row["archive"] == 'Yes')
	{
		$data["admin"] = 
        '<a href="javascript:void(0)" title="Edit PIP"'
		."onclick='editPdp(\"".$row["id"]."\")'"
		.'><i class="fa fa-edit"></i>'
        .'</a>'
		."<a id='archive' href='javascript:void(0)' title='Archive PIP' onclick='archivePIP(\"".$row["id"]."\")'><i style='color:red;' class='fa fa-archive'></i></a>"
		."<a href='javascript:void(0)' title='Delete PIP' onclick='confirmDeletePIP(\"".$row["id"]."\")'>"
		.'<i class="fa fa-trash"></i>'
        .'</a>';	
	}
	else
	{
	$data["admin"] = 
        '<a href="javascript:void(0)" title="Edit PIP"'
		."onclick='editPdp(\"".$row["id"]."\")'"
		.'><i class="fa fa-edit"></i>'
        .'</a>'
		."<a id='archive' href='javascript:void(0)' title='Archive PIP' onclick='archivePIP(\"".$row["id"]."\")'><i class='fa fa-archive'></i></a>"
		."<a href='javascript:void(0)' title='Delete PIP' onclick='confirmDeletePIP(\"".$row["id"]."\")'>"
		.'<i class="fa fa-trash"></i>'
        .'</a>';
	}
	echo json_encode($data, JSON_PRETTY_PRINT);
	
	if($count < $project_count) echo ",";
	$data = NULL;
	$count++;
}
echo "]";
?>