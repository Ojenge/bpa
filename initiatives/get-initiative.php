<?php
include_once("../config/config_mysqli.php");
//if(isset($_POST['objectId']))
//{
	@$initiativeId = $_POST['initiativeId'];
	$calendar = "months";
	$linkName = "";
	
	//$objectType = $_POST["objectType"].$calendar;
	//$objectType = "measuremonths";
	$initiative_query="SELECT * FROM initiative WHERE id = '$initiativeId'";
	$initiative_result=mysqli_query($connect, $initiative_query);
	$row_count = mysqli_num_rows($initiative_result);
	if ($row_count == NULL) exit;

	$row = mysqli_fetch_assoc($initiative_result);
	$data["id"] = $row["id"];
	$data["name"] = $row["name"];
	$sponsorId = $row["sponsor"];
	$managerId = $row["projectManager"];
	$data["managerId"] = $managerId;
	$data["sponsorId"] = $sponsorId;
	
	$sponsor_result = mysqli_query($connect, "SELECT display_name FROM uc_users WHERE user_id = '$sponsorId'");
	$sponsor_row = @mysqli_fetch_assoc($sponsor_result);
	$data["sponsor"] = ($sponsor_row && isset($sponsor_row['display_name'])) ? $sponsor_row['display_name'] : '';
	
	$manager_result = mysqli_query($connect, "SELECT display_name FROM uc_users WHERE user_id = '$managerId'");
	$manager_row = @mysqli_fetch_assoc($manager_result);
	$data["manager"] = ($manager_row && isset($manager_row['display_name'])) ? $manager_row['display_name'] : '';
	
	$data["sponsorId"] = $sponsorId;
	$data["managerId"] = $managerId;
	$data["budget"] = $row["budget"];
	
	if($row["damage"] == NULL && $row["budget"] == NULL) 
	{
		$data["damageColor"] = '<div class="rounded-circle bg-light trafficLightBootstrap"></div>';
		$data["damage"] = $row["damage"];
	}
	else if($row["damage"] > $row["budget"]) 
	{
		$data["damageColor"] = '<div class="rounded-circle bg-danger trafficLightBootstrap"></div>';
		$data["damage"] = $row["damage"];
	}
	else 
	{
		$data["damageColor"] = '<div class="rounded-circle bg-success trafficLightBootstrap"></div>';
		$data["damage"] = $row["damage"];
	}
	
	if($data["budget"] == NULL) $data["budget"] = '';
	if($data["damage"] == NULL) $data["damage"] = '';
	
	//if($row["dueDate"] < date("Y-m-d") && $row["completionDate"] == NULL) $data["initiativeColor"] = "red";
	//else if ($row["dueDate"] > date("Y-m-d") && $row["completionDate"] == NULL) $data["initiativeColor"] = "#ffd900";
	//else $data["initiativeColor"] = "green";
	
	if($row["dueDate"] <= date("Y-m-d") && $row["completionDate"] == NULL)
		$data["initiativeColor"] = "red";
	else if ($row["dueDate"] < $row["completionDate"] && $row["completionDate"] != NULL)
		$data["initiativeColor"] = "yellow";
	else if ($row["completionDate"] <= $row["dueDate"] && $row["completionDate"] != NULL)
		$data["initiativeColor"] = "green";
	else $data["initiativeColor"] = "#FFFFFF";
	
	if($row['startDate']!= NULL) $data["startDate"] = date('d-M-Y',strtotime($row['startDate'])); else $data["startDate"] = '';
	if($row['dueDate']!= NULL) $data["dueDate"] = date('d-M-Y',strtotime($row['dueDate'])); else $data["dueDate"] = '';
	if($row['completionDate']!= NULL) $data["completionDate"] = date('d-M-Y',strtotime($row['completionDate'])); else $data["completionDate"] = '';
	
	if($row['dueDate']!= NULL && $row['startDate']!= NULL)
	{
		date_default_timezone_set('Africa/Nairobi');
		$due = new DateTime($row['dueDate']);
		$start = new DateTime($row['startDate']);
		$today = new DateTime(date('Y-m-d'));
		$totalInterval = $due->diff($start);
		$currentInterval = $today->diff($start);
		$totalDuration = $totalInterval->days;
		$currentDuration = $currentInterval->days;
		if($currentDuration >= $totalDuration) $barStatusValue = 0;
		else
		{
			$barStatusValue = 100*(($totalDuration - $currentDuration)/$totalDuration);
		}
		if($row['completionDate']!= NULL) $data["barStatusValue"] = 100;
		else
		$data["barStatusValue"] = $barStatusValue;
		//For years and months: echo "difference " . $interval->y . " years, " . $interval->m." months, ".$interval->d." days "; 
	}
	
	if($row["deliverableStatus"] == "on") 
	{
		$data["deliverableColor"] = '<div class="rounded-circle bg-success trafficLightBootstrap"></div>';
		$data["deliverable"] = $row["deliverable"];
	}
	else if($row["deliverableStatus"] == "off" && $row["dueDate"] < date("Y-m-d")) 
	{
		$data["deliverableColor"] = '<div class="rounded-circle bg-danger trafficLightBootstrap"></div>';
		$data["deliverable"] = $row["deliverable"];
	}
	else 
	{
		$data["deliverableColor"] = '<div class="rounded-circle bg-warning trafficLightBootstrap"></div>';
		$data["deliverable"] = $row["deliverable"];
	}
	
	$data["deliverableStatus"] = $row["deliverableStatus"];

	$data["scope"] = $row["scope"];
	
	$initiativeId = $data["id"];
	$initiativeLink_query=mysqli_query($connect, "SELECT linkedobjectid FROM initiativeimpact WHERE initiativeid = '$initiativeId'") or file_put_contents("aError.txt", "Error is ".mysqli_error($connect));
	$row2 = mysqli_fetch_assoc($initiativeLink_query);
	$linkedobjectid = ($row2 && isset($row2['linkedobjectid'])) ? $row2['linkedobjectid'] : '';

	$linkName = '';
	if($linkedobjectid) {
		switch(substr($linkedobjectid,0,3))
		{
			case "org":
			{
				$linkName_query = mysqli_query($connect, "SELECT name FROM organization WHERE id = '$linkedobjectid'") or file_put_contents("aError.txt", "Error is ".mysqli_error($connect));
				$linkName_result = mysqli_fetch_array($linkName_query);
				$linkName = ($linkName_result && isset($linkName_result["name"])) ? $linkName_result["name"] : '';
				file_put_contents("aInitiative.txt", "Linked object name ".$linkName);
				break;
			}
			case "per":
			{
				$linkName_query = mysqli_query($connect, "SELECT name FROM perspective WHERE id = '$linkedobjectid'") or file_put_contents("aError.txt", "Error is ".mysqli_error($connect));
				$linkName_result = mysqli_fetch_array($linkName_query);
				$linkName = ($linkName_result && isset($linkName_result["name"])) ? $linkName_result["name"] : '';
				break;
			}
			case "obj":
			{
				$linkName_query = mysqli_query($connect, "SELECT name FROM objective WHERE id = '$linkedobjectid'") or file_put_contents("aError.txt", "Error is ".mysqli_error($connect));
				$linkName_result = mysqli_fetch_array($linkName_query);
				$linkName = ($linkName_result && isset($linkName_result["name"])) ? $linkName_result["name"] : '';
				break;
			}
			case "kpi":
			{
				$linkName_query = mysqli_query($connect, "SELECT name FROM measure WHERE id = '$linkedobjectid'") or file_put_contents("aError.txt", "Error is ".mysqli_error($connect));
				$linkName_result = mysqli_fetch_array($linkName_query);
				$linkName = ($linkName_result && isset($linkName_result["name"])) ? $linkName_result["name"] : '';
				break;
			}
		}
	}

	if($linkName == '') $data["link"] = 'No Parent'; else $data["link"] = $linkName;
	$data["linkId"] = $row2["linkedobjectid"];
	
	$parent_id = $row["parent"];
	$parent_result = mysqli_query($connect, "SELECT name FROM initiative WHERE id = '$parent_id'");
	$parent_row = mysqli_fetch_assoc($parent_result);
	$data["parent"] = ($parent_row && isset($parent_row["name"]) && $parent_row["name"] != NULL) ? $parent_row["name"] : '';
	$data["parentId"] = $row["parent"];
	$data["archive"] = $row["archive"];
	
	$status_result = mysqli_query($connect, "SELECT status, percentageCompletion, details, notes FROM initiative_status WHERE initiativeId = '$initiativeId' ORDER BY updatedOn DESC LIMIT 1") or file_put_contents("errorInitiative.txt", "\t\n Cannot select initiative status with error => ".mysqli_error($connect), FILE_APPEND);
	$statusCount = mysqli_num_rows($status_result);
	$status_row = mysqli_fetch_assoc($status_result);
	if($statusCount > 0) 
	{
		if($status_row["status"] == "Behind Schedule")
		{
			$data["status"] = '<table class="table-borderless" style="background-color: #ffffff !important;"><tr class="table-light"><td><div class="rounded-circle bg-danger trafficLightBootstrap"></div></td><td>'.$status_row["status"].'</td></tr></table>';
		}
		else if($status_row["status"] == "On Track")
		{
			$data["status"] = '<table class="table-borderless" style="background-color: #ffffff !important;"><tr class="table-light"><td><div class="rounded-circle bg-warning trafficLightBootstrap"></div></td><td>'.$status_row["status"].'</td></tr></table>';
		}
		else
		{
			$data["status"] = '<table class="table-borderless" style="background-color: #ffffff !important;"><tr class="table-light"><td><div class="rounded-circle bg-success trafficLightBootstrap"></div></td><td>'.$status_row["status"].'</td></tr></table>';
		}

		$data["percentageCompletion"] = (empty($status_row["percentageCompletion"])) ? '' : $status_row["percentageCompletion"];
		$data["statusDetails"] = (empty($status_row["details"])) ? '' : $status_row["details"];
		$data["notes"] = (empty($status_row["notes"])) ? '' : $status_row["notes"];
		$data["statusWithoutCircle"] = (empty($status_row["status"])) ? '' : $status_row["status"];
	}
	else 
	{
		$data["status"] = '<table class="table-borderless" style="background-color: #ffffff !important;"><tr class="table-light"><td><div class="rounded-circle bg-light trafficLightBootstrap"></div></td><td></td></tr></table>';
	}
		
	$issue_result = mysqli_query($connect, "SELECT * FROM initiative_issue WHERE initiativeId = '$initiativeId' ORDER BY updatedOn DESC") or file_put_contents("errorInitiative.txt", "\t\n Cannot select initiative issues with error => ".mysqli_error($connect), FILE_APPEND);
	$issue = "<table><tr><th>Issue</th><th>Way Forward</th><th>Severity</th><th>Status</th><th>Owner</th><th>Last Updated</th></tr>";
	while($issue_row = mysqli_fetch_assoc($issue_result))
	{
		$issueId = $issue_row["id"];
		$issue = $issue."<tr>".
		"<td>".$issue_row["issue"]."</td>".
		"<td>".$issue_row["wayForward"]."</td>".
		"<td>".$issue_row["severity"]."</td>".
		"<td>".$issue_row["status"]."</td>".
		"<td>".$issue_row["owner"]."</td>".
		"<td>".$issue_row["updatedOn"]."</td>".
		"<td><img src='../images/icons/edit.png' onClick='editBottleneck($issueId)'>".
		"<td><img src='../images/icons/delete.png' onClick='deleteBottleneck($issueId)'>".
		"</tr>";
	}
	$data["issues"] = $issue."</table>";
	
	/*
	$initiativeTeam_query="SELECT firstName, lastName
	FROM initiativeteam WHERE id LIKE '$initiativeId' ";
	$initiativeTeam_result=mysqli_query($connect, $initiativeTeam_query) or die("Could not query initiative Team table");;
	
	
	
	//$data["Team"] = NULL;
	$count = 1;
	while($row3 = mysqli_fetch_assoc($initiativeTeam_result))
	{
		if ($count == 1) $data["Team"] = $row3["firstName"]." ".$row3["lastName"];
		else $data["Team"] = $data["Team"].", ".$row3["firstName"]." ".$row3["lastName"];
		$count++;
	}*/
	$data = json_encode($data);
	echo $data;
	//$data = null;
	
	//echo $data["Link"];
	flush();
//}
exit;
?>