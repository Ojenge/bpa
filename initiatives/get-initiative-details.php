<?php
include('../config/config_msqli.php');

date_default_timezone_set('Africa/Nairobi');

//$initiativeId = "63";
$initiativeId = $_POST["id"];

//$project_result = mysqli_query($GLOBALS["___mysqli_ston"], $project_query);// Sasa hii ilitoka wapi? Preventing the rest of the code from running. LTK 02Jul25 1839hrs

$initiativeLink_query = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT linkedobjectid FROM initiativeimpact WHERE initiativeid = '$initiativeId'") or file_put_contents("aError.txt", "Error is ".mysqli_error($GLOBALS["___mysqli_ston"]));
$row2 = mysqli_fetch_assoc($initiativeLink_query);
$linkedobjectid = ($row2 && isset($row2['linkedobjectid'])) ? $row2['linkedobjectid'] : '';

switch(substr($linkedobjectid,0,3))
{
	case "org":
	{
		$linkName = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT name FROM organization WHERE id = '$linkedobjectid'") or file_put_contents("aError.txt", "Error is ".mysqli_error($GLOBALS["___mysqli_ston"]));;
		$linkName = mysqli_fetch_array($linkName);
		$linkName = $linkName["name"];
		file_put_contents("aInitiative.txt", "Linked object name ".$linkName);
		break;	
	}
	case "per":
	{
		$linkName = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT name FROM perspective WHERE id = '$linkedobjectid'") or file_put_contents("aError.txt", "Error is ".mysqli_error($GLOBALS["___mysqli_ston"]));;
		$linkName = mysqli_fetch_array($linkName);
		$linkName = $linkName["name"];
		break;	
	}
	case "obj":
	{
		$linkName = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT name FROM objective WHERE id = '$linkedobjectid'") or file_put_contents("aError.txt", "Error is ".mysqli_error($GLOBALS["___mysqli_ston"]));;
		$linkName = mysqli_fetch_array($linkName);
		$linkName = $linkName["name"];
		break;	
	}
	case "kpi":
	{
		$linkName = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT name FROM measure WHERE id = '$linkedobjectid'") or file_put_contents("aError.txt", "Error is ".mysqli_error($GLOBALS["___mysqli_ston"]));;
		$linkName = mysqli_fetch_array($linkName);
		$linkName = $linkName["name"];
		break;	
	}
	case "ind":
	{
		$linkName = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT name FROM individual WHERE id = '$linkedobjectid'") or file_put_contents("aError.txt", "Error is ".mysqli_error($GLOBALS["___mysqli_ston"]));;
		$linkName = mysqli_fetch_array($linkName);
		$linkName = $linkName["name"];
		break;	
	}
}

if($linkName == '') $linkName = 'No Parent';

echo"<table class='table table-responsive table-bordered table-sm table-condensed table-striped'>";
echo "<tr class='table-info'><td rowspan='6'>&nbsp;</td></tr>";

$initiative = "SELECT sponsor, damage, completionDate, deliverable, deliverableStatus, scope FROM initiative WHERE id = '$initiativeId'";

$query = mysqli_query($GLOBALS["___mysqli_ston"], $initiative) or file_put_contents("error.txt", "Error => ".mysqli_error($GLOBALS["___mysqli_ston"]));
$result = mysqli_fetch_assoc($query);	

$sponsor = $result['sponsor'];
$sponsor_query = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT display_name FROM uc_users WHERE user_id = '$sponsor'");
$sponsor_result = mysqli_fetch_assoc($sponsor_query);
$sponsor = ($sponsor_result && isset($sponsor_result["display_name"])) ? $sponsor_result["display_name"] : '';

if(isset($result['damage']))
$damage = number_format($result['damage']);
else $damage = "";
if($result['completionDate'] == NULL) $completionDate = "";
else $completionDate = date("d M Y", strtotime($result['completionDate'])); 

if($result['deliverableStatus'] == "off")
$deliverableStatus = "Deliverable Not Met";
else $deliverableStatus = "Deliverable Met";

echo "<tr><td>Sponsor:</td><td>".$sponsor."</td><td>Scope:</td><td>".$result['scope']."</td></tr>";
echo "<tr><td>Completion Date:</td><td>".$completionDate."</td><td>Cost to Date:</td><td>".$damage."</td></tr>";
echo "<tr><td>Deliverable:</td><td>".$result['deliverable']."</td><td>Deliverable Status:</td><td>".$deliverableStatus."</td></tr>";

$status_query="SELECT status, percentageCompletion ,details, notes FROM initiative_status WHERE initiativeId = '$initiativeId' ORDER BY updatedOn DESC LIMIT 1";
$status_result = mysqli_query($GLOBALS["___mysqli_ston"], $status_query);
$row = mysqli_fetch_assoc($status_result);
$status = ($row && isset($row['status'])) ? $row['status'] : '';
$percentageCompletion = ($row && isset($row['percentageCompletion'])) ? $row['percentageCompletion'] : '';
$details = ($row && isset($row['details'])) ? $row['details'] : '';
$notes = ($row && isset($row['notes'])) ? $row['notes'] : '';
echo "<tr><td>Status:</td><td>".$status."</td><td>Percentage Completion:</td><td>".$percentageCompletion."%</td></tr>";
echo "<tr><td>Interpretation:</td><td>".$details."</td><td>Way Forward:</td><td>".$notes."</td></tr>";
echo"</table>";
?>