
<?php
include_once("../config/config_mysqli.php");
include_once("../admin/models/config.php");
//include_once("../functions/functions.php");
//include_once("../functions/calendar-labels.php");
//include_once("../functions/perspOrg-scores.php");
include_once("../reports/scores-functions.2.0.php");

date_default_timezone_set('Africa/Nairobi');
@$objectId = "ind".$loggedInUser->user_id;
@$objectPeriod = $_POST['objectPeriod'];
@$objectDate = $_POST['objectDate'];
$todaysDay = date("d");
@$objectDate = date("Y-m-d",strtotime($objectDate."-30")); //Changed this from 01 to 30 - was not accurate - excludes the rest of the month when getting results on a day past 01. Need to update this across the system. LTK 27 Jun 2021 0051 Hrs
//@$objectDate = date("Y-m-d",strtotime($objectDate.$todaysDay)); //Possibly More precise

$oneMonthAgo = new \DateTime('1 month ago');
$oneMonthAgo = $oneMonthAgo->format('Y-m-d');

$trafficLight = '<div class="rounded-circle trafficLightBootstrap bg-white" style="float:left;"></div>';

//echo "Object Date = ".$objectDate." and oneMonthAgo = $oneMonthAgo";
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
			
			/*$periodQuery = "SELECT AVG(initiative_status.percentageCompletion) AS indScore
		FROM initiative, initiative_status 
		WHERE initiative_status.updatedOn = 
		(SELECT MAX(updatedOn) FROM initiative_status WHERE initiative.projectManager = '$userId' 
		 AND initiative_status.initiativeId = initiative.id 
		 AND initiative_status.percentageCompletion != 0 
		 AND initiative_status.updatedOn <= '$objectDate' - INTERVAL 1 MONTH)";*/
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

$staffQuery = mysqli_query($connect, "SELECT uc_users.user_id, uc_users.display_name, uc_users.photo, uc_users.title, uc_users.last_sign_in_stamp, organization.name  
FROM uc_users, organization
WHERE uc_users.reportsTo = 'ind7'
AND uc_users.title <> 'Executive Assistant'
AND uc_users.department = organization.id
ORDER by uc_users.reportsTo") or file_put_contents("error.txt", "Error=> ".mysqli_error($connect));

$staffCount = mysqli_num_rows($staffQuery);
$count = 1;
$data = array();

echo '<div class="container mt-2">';
echo '<div class="card">'
.'<div class="card-header bg-light bg-gradient">Heads of Departments</div>'
.'</div>';

echo '<div class="mt-2 row row-cols-'.$staffCount.'">';
while($row = mysqli_fetch_array($staffQuery))
{
	if($row['last_sign_in_stamp'] == 0) $lastSignIn = "Never";
	else $lastSignIn = date("j M Y", $row['last_sign_in_stamp']);
	
	$userId = $row["user_id"];
	$data["id"] = $row["user_id"];
	$taskCount = mysqli_query($connect, "SELECT COUNT(id) AS count FROM initiative WHERE projectManager = '$userId'");
	$taskCount = mysqli_fetch_array($taskCount);
	$data["taskCount"] = $taskCount["count"];
	
	$updateCount = mysqli_query($connect, "SELECT COUNT(DISTINCT(initiative.id)) AS count 
	FROM initiative, initiative_status 
	WHERE initiative.projectManager = '$userId'
	AND initiative_status.initiativeId = initiative.id
	AND initiative_status.updatedOn > '$objectDate' - INTERVAL 1 MONTH");
	
	$updateCount = mysqli_fetch_array($updateCount);
	$data["updateCount"] = $updateCount["count"];
	//$data["updateCount"] = '3';
	
	$indScore = individualScore($userId, $oneMonthAgo) * 10;
	//echo "User = $userId; ".$oneMonthAgo." indScore = $indScore";
	if($indScore == "") $trafficLight = '<div class="rounded-circle trafficLightBootstrap bg-white" style="float:left;"></div>';
	else if($indScore > 0 && $indScore < 50) $trafficLight = '<div class="red3d" style="float:left;"></div>';
	else if($indScore >= 50 && $indScore < 69) $trafficLight = '<div class="yellow3d" style="float:left;"></div>';
	else if($indScore >= 70) $trafficLight = '<div class="green3d" style="float:left;"></div>';
	
	if($indScore == NULL) $indScore = "";
	else $indScore = round($indScore, 2)."%";
	
	$periodQuery = previousPeriodQuery($objectPeriod, $objectDate, $userId);
	
	$indScorePrevious = mysqli_query($connect, $periodQuery) or file_put_contents("error.txt", "Error => ".mysqli_error($connect).mysqli_error($periodQuery));
	$indScorePrevious = mysqli_fetch_array($indScorePrevious);
	
	if($indScorePrevious["indScore"] == NULL) $indScorePrevious = "";
	else $indScorePrevious = round($indScorePrevious["indScore"], 2)."%";
	
	$data["indScorePrevious"] = $indScorePrevious;
	
	//$data["indScorePrevious"] = "33";
	//$indScore = "30";
	
	if($indScorePrevious == "" || $indScore == "") $indScoreTrend = "";
	else if($indScorePrevious < $indScore) $indScoreTrend = '<i class="fa fa-arrow-up text-success" style="float:right;"></i>';
	else if($indScorePrevious > $indScore) $indScoreTrend = '<i class="fa fa-arrow-down text-danger" style="float:right;"></i>';
	else $indScoreTrend = '<i class="fas fa-arrows-alt-h text-warning" style="float:right;"></i>';
	
	//if ($row['photo'] == undefined) $photo = "<img class='rounded-3' src='upload/images/default.jpg' max-width='200' height='122'  />";
	//else $photo = "<img class='rounded-3' src='".$row['photo']."' max-width='200' height='122' align='middle' />";
	
	echo '<div class="col">';
	echo '<div class="card bg-light h-100" style="cursor: pointer;" onclick=\'listReportees("'.$row["user_id"].'","Staff");\'>'
            .'<div class="card-body">';

			echo '<table>';
			if($row["photo"] == "") 
			{
				echo '<tr>'
				.'<td colspan="2"><image height="60" width="50" src="upload/images/default_grey.png" class="d-inline-block align-text-top rounded-circle border border-primary"></td>'
				.'</tr>';

				echo '<tr>';
				if(str_word_count($row["display_name"]) == "2") echo '<td colspan="2"><h6 class="card-title">'.$row["display_name"].'</h6><br></td>';
				else echo '<td colspan="2"><h6 class="card-title">'.$row["display_name"].'</h6></td>';
				echo '</tr>';
			}
            else 
			{
				echo '<tr>'
				.'<td colspan="2"><image height="60" width="50" src="'.$row["photo"].'" class="d-inline-block align-text-top rounded-circle border border-primary"></td>'
				.'</tr>';

				echo '<tr>';
				if(str_word_count($row["display_name"]) == "2") echo '<td colspan="2"><h6 class="card-title">'.$row["display_name"].'</h6><br></td>';
				else echo '<td colspan="2"><h6 class="card-title">'.$row["display_name"].'</h6></td>';
				echo '</tr>';
			}
            echo '<tr>'
				.'<td>'.$trafficLight.'</td>'
				.'<td><p class="card-text">'.$indScore.'</p></td>'
				.'</tr>'
				.'<tr>'
				.'<td colspan="2"><p><span class="card-text"><small class="text-muted fst-italic fw-lighter">Login: '.$lastSignIn.'</small></span></p></td>'
				.'</tr>'
				.'</table>'
            .'</div>'//end of card-body
        .'</div>'//end of card
        .'</div>';//end of col

	$data["indScore"] = $indScore." ".$indScoreTrend;
	
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
echo '</div>';//end of row
echo '</div>';//end of container 1

?>