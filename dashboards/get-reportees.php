<?php
include_once("../config/config_mysqli.php");
include_once("../admin/models/config.php");
include_once("../functions/functions.php");
include_once("../functions/calendar-labels.php");
include_once("../functions/perspOrg-scores.php");

date_default_timezone_set('Africa/Nairobi');
@$objectId = $_POST['filter'];
@$objectPeriod = $_POST['objectPeriod'];
@$objectDate = $_POST['objectDate'];
$todaysDay = date("d");
@$objectDate = date("Y-m-d",strtotime($objectDate."-30")); //Changed this from 01 to 30 - was not accurate - excludes the rest of the month when getting results on a day past 01. Need to update this across the system. LTK 27 Jun 2021 0051 Hrs
//@$objectDate = date("Y-m-d",strtotime($objectDate.$todaysDay)); //Possibly More precise

$oneMonthAgo = new \DateTime('1 month ago');
$oneMonthAgo = $oneMonthAgo->format('Y-m-d');

//file_put_contents("track.txt", "objectId = $objectId, objectDate = $objectDate");

function previousPeriodQuery($objectPeriod, $objectDate, $userId)
{
	switch($objectPeriod)
	{
		case "months":
		{
			$periodQuery = "SELECT AVG(o.percentageCompletion) AS indScore
			FROM `initiative_status` o                    
				LEFT JOIN `initiative_status` b             
				ON o.initiativeId = b.initiativeId 
				AND o.updatedOn < b.updatedOn
			WHERE b.updatedOn is NULL                 
			AND o.initiativeId IN (SELECT id FROM initiative WHERE projectManager = '$userId')
			AND o.updatedOn <= '$objectDate' - INTERVAL 1 MONTH
			AND o.percentageCompletion != 0";
			break;	
		}
		case "years":
		{
			$periodQuery = "SELECT AVG(o.percentageCompletion) AS indScore
			FROM `initiative_status` o                    
				LEFT JOIN `initiative_status` b             
				ON o.initiativeId = b.initiativeId 
				AND o.updatedOn < b.updatedOn
			WHERE b.updatedOn is NULL                 
			AND o.initiativeId IN (SELECT id FROM initiative WHERE projectManager = '$userId')
			AND o.updatedOn <= '$objectDate' - INTERVAL 1 YEAR
			AND o.percentageCompletion != 0";
			break;	
		}
		default:
		{
			$periodQuery = "SELECT AVG(o.percentageCompletion) AS indScore
			FROM `initiative_status` o                    
				LEFT JOIN `initiative_status` b             
				ON o.initiativeId = b.initiativeId 
				AND o.updatedOn < b.updatedOn
			WHERE b.updatedOn is NULL                 
			AND o.initiativeId IN (SELECT id FROM initiative WHERE projectManager = '$userId')
			AND o.updatedOn <= '$objectDate' - INTERVAL 1 MONTH
			AND o.percentageCompletion != 0";
			break;	
		}	
	}
	return $periodQuery;
}

$staffQuery = mysqli_query($connect, "SELECT uc_users.user_id, uc_users.user_name, uc_users.display_name, uc_users.reportsTo, uc_users.photo, uc_users.title, uc_users.last_sign_in_stamp, organization.name  
FROM uc_users, organization
WHERE reportsTo = '$objectId '
AND title <> 'Executive Assistant'
AND uc_users.department = organization.id
ORDER by reportsTo") or file_put_contents("error.txt", "Error=> ".mysqli_error($connect));

$staffCount = mysqli_num_rows($staffQuery);
$count = 1;
$data = array();
echo '<div class="container mt-3">'
.'<div class="card">'
.'<div class="card-header bg-light bg-gradient">Departmental Staff</div>'
.'<div class="card-body">'
//.'<div class="list-group">'
//.'<li class="list-group-item">'
.'<table class="table table-sm">'
."<tr><td></td><td></td><td colspan='2'>Score</td><td>Total initiatives</td><td>Initiatives updated within 1 month</td><td>Last login</td></tr>";

while($row = mysqli_fetch_array($staffQuery))
{
	if($row['last_sign_in_stamp'] == 0) $lastSignIn = "Never";
	else $lastSignIn = date("j M, Y", $row['last_sign_in_stamp']);
	
	$userId = $row["user_id"];
	$data["id"] = $row["user_id"];
	$taskCount = mysqli_query($connect, "SELECT COUNT(id) AS count FROM initiative WHERE projectManager = '$userId'");
	$taskCount = mysqli_fetch_array($taskCount);
	$data["taskCount"] = $taskCount["count"];
	
	/*$updateCount = mysqli_query($connect, "SELECT COUNT(DISTINCT(initiative.id)) AS count 
	FROM initiative, initiative_status 
	WHERE initiative.projectManager = '$userId'
	AND initiative_status.initiativeId = initiative.id
	AND initiative_status.updatedOn > NOW() - INTERVAL 1 MONTH");*/
	
	$updateCount = mysqli_query($connect, "SELECT COUNT(DISTINCT(initiative.id)) AS count 
	FROM initiative, initiative_status 
	WHERE initiative.projectManager = '$userId'
	AND initiative_status.initiativeId = initiative.id
	AND initiative_status.updatedOn > '$objectDate' - INTERVAL 1 MONTH");
	
	$updateCount = mysqli_fetch_array($updateCount);
	$data["updateCount"] = $updateCount["count"];
	
	$indScore = individualScore($userId, $oneMonthAgo) * 10;
	
	if($indScore == NULL) $indScore = "";
	else $indScore = round($indScore, 2)."%";
	
	$periodQuery = previousPeriodQuery($objectPeriod, $objectDate, $userId);
	
	$indScorePrevious = mysqli_query($connect, $periodQuery) or file_put_contents("error.txt", "Error => ".mysqli_error($connect).mysqli_error($periodQuery));
	$indScorePrevious = mysqli_fetch_array($indScorePrevious);
	
	if($indScorePrevious["indScore"] == NULL) $indScorePrevious = "";
	else $indScorePrevious = round($indScorePrevious["indScore"], 2)."%";
	
	$data["indScorePrevious"] = $indScorePrevious;
	
	if($indScorePrevious == "" || $indScore == "") $indScoreTrend = "";
	else if($indScorePrevious < $indScore) $indScoreTrend = '<i class="fa fa-arrow-up text-success" style="float:right;"></i>';
	else if($indScorePrevious > $indScore) $indScoreTrend = '<i class="fa fa-arrow-down text-danger" style="float:right;"></i>';
	else $indScoreTrend = '<i class="fas fa-arrows-alt-h text-warning" style="float:right;"></i>';
	
	$data["indScore"] = $indScore." ".$indScoreTrend;

	if($row["photo"] == "") $photo = '<image height="40" width="30" src="upload/images/default_grey.png" class="d-inline-block rounded-circle border border-primary">';
            else $photo = '<image height="40" src="'.$row["photo"].'" class="d-inline-block rounded-circle border border-primary">';
	
	if($indScore == "") $trafficLight = '<div class="rounded-circle trafficLightBootstrap bg-white" style="float:left;"></div>';
	else if($indScore > 0 && $indScore < 50) $trafficLight = '<div class="red3d" style="float:left;"></div>';
	else if($indScore >= 50 && $indScore < 69) $trafficLight = '<div class="yellow3d" style="float:left;"></div>';
	else if($indScore >= 70) $trafficLight = '<div class="green3d" style="float:left;"></div>';
	
	echo "<tr>"
	.'<td>'
	.$photo
	.'</td>'
	.'<td>'
	.$row["display_name"]
	.'</td>'
	.'<td>'
	.$trafficLight
	.'</td>'
	.'<td>'
	.$indScore
	.'</td>'
	.'<td>'
	.$taskCount["count"]
	.'</td>'
	.'<td>'
	.$updateCount["count"]
	.'</td>'
	.'<td>'
	.$lastSignIn
	.'</td>'
	.'</tr>';
	$data["display_name"] = '<span style="white-space:nowrap">'.$row["display_name"]."</span>";
	$data["title"] = '<span style="white-space:nowrap">'.$row["title"]."</span>";
	$data["name"] = $row["name"];
	$data["lastSignIn"] = '<span style="white-space:nowrap">'.$lastSignIn."</span>";
	
	//echo json_encode($data);
	$data = NULL;
	//if($count < $staffCount) echo ",";
	$count++;
	//getSubordinates($row["user_id"], $connect);
}
echo '</table>'
//.'</li>'//End of list-group-item
//.'</div>'//End of list-group
.'<div>'//End of card-body
.'<div>'//End of card
.'<div>';//End of container
?>