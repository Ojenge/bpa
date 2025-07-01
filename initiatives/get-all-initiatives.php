<?php
include('../config/config_msqli.php');
include('../functions/functions-rights.php');
include('../functions/scorecard-associations.php');
//header('Content-Type: application/json');

//$filter = $_POST['filter'];
$userId = $_POST['userId'];
require_once("../admin/models/config.php");

$userPermission = fetchUserPermissions($loggedInUser->user_id);

$role = "";
$idsArray = getUserIDs($userPermission, $role, $loggedInUser->user_id);
$size = sizeof($idsArray);
file_put_contents("size.txt", "IDS = ".$size);
if(sizeof($idsArray) > 0)
{
	$ids = join("','",$idsArray);
	$ids = $ids."', '".$userId; //Include your own Initiatives!
}
else
$ids = $userId;
file_put_contents("aError.txt", "IDS = ".$ids);

// Get the list of initiatives as saved
$project_query="SELECT * FROM initiative WHERE projectManager IN ('$ids') ORDER BY lastUpdated DESC";
$project_result = mysqli_query($GLOBALS["___mysqli_ston"], $project_query) or file_put_contents("aError.txt", "Error => ".mysqli_error($GLOBALS["___mysqli_ston"]));
$project_count = mysqli_num_rows($project_result);

$count = 1;

echo "[";
while($row = mysqli_fetch_assoc($project_result))
{
	$data["id"] = $row["id"];
	$data["s_no"] = $row["id"];
	$data["name"] = $row["name"];
	if($row["budget"] == NULL) $data["budget"] = ""; else $data["budget"] = number_format($row["budget"]);
	if($row["startDate"] == NULL) $data["startDate"] = "";
	else $data["startDate"] = date("d M Y", strtotime($row["startDate"]));
	if($row["dueDate"] == NULL) $data["dueDate"] = "";
	else $data["dueDate"] = date("d M Y", strtotime($row["dueDate"]));
		$owner = $row["projectManager"];
		$owner = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT display_name FROM uc_users WHERE user_id = '$owner'");
		$owner = mysqli_fetch_assoc($owner);
	$data["owner"] = ($owner && isset($owner["display_name"])) ? $owner["display_name"] : "";
		$id = $row["id"];
		$status = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT percentageCompletion FROM initiative_status WHERE initiativeId = '$id' ORDER BY updatedOn DESC LIMIT 1");
		$status = mysqli_fetch_assoc($status);
	if(!$status || $status["percentageCompletion"] == NULL) $data["status"] = '';
	else $data["status"] = $status["percentageCompletion"]."%";
	
	$impact = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT linkedobjectid FROM initiativeimpact WHERE initiativeId = '$id'");
	$impact = mysqli_fetch_assoc($impact);
	if($impact && isset($impact["linkedobjectid"])) {
		$impact = $impact["linkedobjectid"];
		switch(substr($impact, 0, 3))
		{
			case "ind":
			{
				$parentOrg = getParentOrganization($impact);
				$impact_query = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT name FROM individual WHERE id = '$impact'");
				$impact_result = mysqli_fetch_assoc($impact_query);
				$data["impact"] = ($impact_result && isset($impact_result["name"])) ? $impact_result["name"]." [".$parentOrg." Ind]" : "Unknown Individual";
				break;
			}
			case "org":
			{
				$parentOrg = getParentOrganization($impact);
				$impact_query = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT name FROM organization WHERE id = '$impact'");
				$impact_result = mysqli_fetch_assoc($impact_query);
				$data["impact"] = ($impact_result && isset($impact_result["name"])) ? $impact_result["name"]." [".$parentOrg." Org]" : "Unknown Organization";
				break;
			}
			case "obj":
			{
				$parentOrg = getParentOrganization($impact);
				$impact_query = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT name FROM objective WHERE id = '$impact'");
				$impact_result = mysqli_fetch_assoc($impact_query);
				$data["impact"] = ($impact_result && isset($impact_result["name"])) ? $impact_result["name"]." [".$parentOrg." Obj]" : "Unknown Objective";
				break;
			}
			case "kpi":
			{
				$parentOrg = getParentOrganization($impact);
				$impact_query = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT name FROM measure WHERE id = '$impact'");
				$impact_result = mysqli_fetch_assoc($impact_query);
				$data["impact"] = ($impact_result && isset($impact_result["name"])) ? $impact_result["name"]." [".$parentOrg." Kpi]" : "Unknown KPI";
				break;
			}
		}
	} else {
		$data["impact"] = "No Impact Linked";
	}
	
	if($row["archive"] == 'Yes')
	{
		$data["admin"] = 
        '<span style="white-space:nowrap;"><a href="javascript:void(0)" title="Edit Initiative"'
		."onclick='editInitiative(\"".$row["id"]."\")'"
		.'><i class="fa fa-edit"></i>'
        .'</a>'
		."<a id='archive' href='javascript:void(0)' title='Archive Initiative' onclick='archiveInitiative(\"".$row["id"]."\")'><i style='color:red;' class='fa fa-archive'></i></a>"
		."<a href='javascript:void(0)' title='Supporting Documents' onclick='supportingDocuments(\"".$row["id"]."\")'><i class='fa fa-paperclip'></i></a>"
		."<a href='javascript:void(0)' title='Delete Initiative' onclick='confirmDeleteInitiative(\"".$row["id"]."\")'>"
		.'<i class="fa fa-trash"></i>'
        .'</a></span>';	
	}
	else
	{
	$data["admin"] = 
        '<span style="white-space:nowrap;"><a href="javascript:void(0)" title="Edit Initiative"'
		."onclick='editInitiative(\"".$row["id"]."\")'"
		.'><i class="fa fa-edit"></i>'
        .'</a>'
		."<a id='archive' href='javascript:void(0)' title='Archive Initiative' onclick='archiveInitiative(\"".$row["id"]."\")'><i class='fa fa-archive'></i></a>"
		."<a href='javascript:void(0)' title='Supporting Documents' onclick='supportingDocuments(\"".$row["id"]."\")'><i class='fa fa-paperclip'></i></a>"
		."<a href='javascript:void(0)' title='Delete Initiative' onclick='confirmDeleteInitiative(\"".$row["id"]."\")'>"
		.'<i class="fa fa-trash"></i>'
        .'</a></span>';
	}
	echo json_encode($data, JSON_PRETTY_PRINT);
	
	if($count < $project_count) echo ",";
	$data = NULL;
	$count++;
}
echo "]";
?>