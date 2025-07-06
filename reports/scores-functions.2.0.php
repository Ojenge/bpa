<?php
//file_put_contents("test.txt", "Are we getting here?");
//include("../../config/config_mysqli.php");
include("../functions/functions.php");

function getScoreFromTarget($actual, $red, $green)
{
	$newGreen = $red + ($green - $red)/2;
	file_put_contents("lee.txt", "withing getScoreFromTarget");
	$score = ((abs($actual) - abs($red))/(abs($newGreen) - abs($red)) * ((1/3)+3)) + ((1/3)+3);
	if($score > 10) $score = 10;
	if($score < 0) $score = 0;
	if($actual < $red && $actual < $green && $red < $green) $score = 0; //taking care of negative actual values
	$score = number_format((float)$score, 2);
	return $score;
}

function getScore($kpiArray)
{
	global $connect;
	$totalScore = NULL;
	$scoreCount = 0;
	for($i=0; $i<count($kpiArray); $i++)
	{
		$kpiId = $kpiArray[$i]["id"];
		$kpiQuery = mysqli_query($connect, "SELECT gaugeType, calendarType FROM measure WHERE id = '$kpiId'");
		$kpiResult = mysqli_fetch_assoc($kpiQuery);
		$kpiTable = $kpiResult['calendarType'];
		$kpiGauge = $kpiResult['gaugeType'];
		switch($kpiTable)
		{
			case "Daily":
			{
				$table = "measuredays";
				break;	
			}
			case "Weekly":
			{
				$table = "measureweeks";
				break;	
			}
			case "Monthly":
			{
				$table = "measuremonths";
				break;	
			}
			case "Quarterly":
			{
				$table = "measurequarters";
				break;	
			}
			case "Bi-Annually":
			{
				$table = "measurehalfyear";
				break;	
			}
			case "Yearly":
			{
				$table = "measureyears";
				//$objectDate = date("Y", strtotime($objectDate));
				break;	
			}
		}//end switch
		$kpi = mysqli_query($connect, "SELECT actual, red, green, darkGreen FROM $table WHERE measureId = '$kpiId' ORDER BY date DESC LIMIT 1");
		$kpiCount = mysqli_num_rows($kpi);
		$kpi = mysqli_fetch_assoc($kpi);
		
		if($kpiCount == 0) 
		{
			//Do nothing - don't compute and count on kpis without actuals
			$actual = "";
			$red = "";
			$green = "";
		}
		else
		{
			$actual = $kpi["actual"];
			$red = $kpi["red"];
			$green = $kpi["green"];

			$score = NULL;
			switch($kpiGauge)
			{
				case "goalOnly":
				{
					if(abs($actual) < abs($green))
					$score = 0;
					else
					$score = 10;
					break;	
				}
				case "threeColor":
				{
					$newGreen = $red + ($green - $red)/2;

					$score = ((abs($actual) - abs($red))/(abs($newGreen) - abs($red)) * ((1/3)+3)) + ((1/3)+3);
					if($score > 10) $score = 10;
					if($score < 0) $score = 0;
					if($actual < $red && $actual < $green && $red < $green) $score = 0; //taking care of negative actual values
					break;	
				}
				case "fourColor":
				{
					if(abs($actual) <= abs($green))
					$score = ((abs($actual) - abs($red))/(abs($green) - abs($red)) * 2.5) + 2.5;
					else if (abs($actual) > abs($green) && abs($actual) <= abs($darkGreen))
					$score = ((abs($actual) - abs($green))/(abs($darkGreen) - abs($green)) * 2.5) + 5;
					else if(abs($actual) > abs($darkGreen))
					$score = ((abs($actual) - abs($darkGreen))/(abs($darkGreen)) * 2.5) + 7.5;
					if($score > 10) $score = 10;
					if($score < 0) $score = 0;	
					break;	
				}
				case "fiveColor":
				{
					if(abs($actual) <= abs($green))
					$score = ((abs($actual) - abs($red))/(abs($green) - abs($red)) * 2) + 2;
					else if (abs($actual) > abs($green) && abs($actual) <= $darkGreen)
					$score = ((abs($actual) - abs($green))/(abs($darkGreen) - abs($green)) * 2) + 4;
					else if(abs($actual) > $darkGreen && abs($actual) <= abs($blue))
					$score = ((abs($actual) - abs($darkGreen))/(abs($blue) - abs($darkGreen)) * 2) + 6;
					else if(abs($actual) > abs($blue))
					$score = ((abs($actual) - abs($blue))/(abs($blue)) * 2) + 8;
					if($score > 10) $score = 10;
					if($score < 0) $score = 0;
					break;	
				}
			}//End of switch
			$scoreCount++;
			$totalScore = $totalScore + $score;
		}
	}
	if($totalScore == 0 || $scoreCount == 0) $finalScore = "";
	else {
		$finalScore = $totalScore / $scoreCount;
		$finalScore = number_format((float)$finalScore, 2);
	}
	return $finalScore;
}
function getColor($score)
{
	if($score >= 6.67) $color = "bg-success";
	else if($score >= 3.33 && $score < 6.67) $color = "bg-warning";
	else if($score > 0) $color = "bg-danger";
	else $color = "table-secondary";
	return $color;
}
function getOrgScore($orgId)
{//Check structure of organization and call relevant function
	global $connect;
if(count(getPerspectives($orgId)) > 0)
	{//Organization has perspectives
		$objCount = 0;
		$perspCount = count(getPerspectives($orgId));
		$perspectives = getPerspectives($orgId);
		for($i = 0; $i < $perspCount; $i++)
		{//check if one or more of the perspectives have objectives
			$objCount = $objCount + count(getObjectives($perspectives[$i]["id"]));
		}
		if($objCount > 0)
		{//There's a perspective with objectives: assume full structure
			return orgPerspObjKpiScore($orgId);
		}
		else
		{//If no objective then assume KPIs are assigned directly to the perspective
			return orgPerspKpiScore($orgId);	
		}
	}
	else
	{//Organization doesn't have perspecctives
		if(count(getObjectives($orgId)) > 0)
		{//organization has objectives
			return orgObjKpiScore($orgId);	
		}
		else
		{//No objectives so assumption here is that organization has at least some measures
			return orgKpiScore($orgId);
		}
	}
}
function getPerspScore($perspId)
{//Check structure of perspective and call relevant function
	$objCount = count(getObjectives($perspId));
	if($objCount > 0)
	{//Perspective has objectives: assume full structure
		return perspObjKpiScore($perspId);
	}
	else
	{//No objectives so assumption here is that perspective has at least some measures
		return perspKpiScore($perspId);
	}
}

function getOrganization($orgId)
{
	global $connect;
	$ids = array();
	$result = mysqli_query($connect, "SELECT id, name FROM organization WHERE id = '$orgId'");
	$row = mysqli_fetch_assoc($result);
	$ids["name"] = $row["name"];
	$ids["id"] = $row["id"];
	//$ids["score"] = getOrgScore($orgId);
	mysqli_free_result($result);
	return $ids;
}
function getPerspectives($orgId)
{
	global $connect;
	$arrayPosition = 0;
	$ids = array();
	$result = mysqli_query($connect, "SELECT id, name FROM perspective WHERE parentId = '$orgId'");
	while($row = mysqli_fetch_array($result))
	{
		$ids[$arrayPosition]["id"] = $row["id"];
		$ids[$arrayPosition]["name"] = $row["name"];
		$arrayPosition++;	
	}
	mysqli_free_result($result);
	return $ids;
}
function getObjectives($parentId)
{
	global $connect;
	$ids = array();
	$arrayPosition = 0;
	$result = mysqli_query($connect, "SELECT id, name FROM objective WHERE linkedObject = '$parentId' ORDER BY sortColumn");
	while($row = mysqli_fetch_array($result))
	{
		$ids[$arrayPosition]["id"] = $row["id"];
		$ids[$arrayPosition]["name"] = $row["name"];
		$arrayPosition++;	
	}
	mysqli_free_result($result);
	return $ids;
}
function getMeasures($parentId)
{
	global $connect;
	$ids = array();
	$arrayPosition = 0;
	$result = mysqli_query($connect, "SELECT id, name, calendarType FROM measure WHERE linkedObject = '$parentId'");
	$count = mysqli_num_rows($result);
	while($row = mysqli_fetch_array($result))
	{
		$ids[$arrayPosition]["id"] = $row["id"];
		$ids[$arrayPosition]["name"] = $row["name"];
		switch($row["calendarType"])
		{
			case "Daily":
			{
				$table = "measuredays";
				break;	
			}
			case "Weekly":
			{
				$table = "measureweeks";
				break;	
			}
			case "Monthly":
			{
				$table = "measuremonths";
				break;	
			}
			case "Quarterly":
			{
				$table = "measurequarters";
				break;	
			}
			case "Bi-Annually":
			{
				$table = "measurehalfyear";
				break;	
			}
			case "Yearly":
			{
				$table = "measureyears";
				//$objectDate = date("Y", strtotime($objectDate));
				break;	
			}
		}//end switch
		$resultActual = mysqli_query($connect, "SELECT actual, green, darkGreen FROM $table WHERE measureId = '".$row["id"]."' ORDER BY date DESC LIMIT 1");
		$actual = mysqli_fetch_assoc($resultActual);
		$actualCount = mysqli_num_rows($resultActual);
		if($actualCount > 0)
		{
			$ids[$arrayPosition]["actual"] = $actual["actual"];
			$ids[$arrayPosition]["green"] = $actual["green"];
		}
		else
		{
			$ids[$arrayPosition]["actual"] = "";
			$ids[$arrayPosition]["green"] = "";
		}
		$arrayPosition++;	
	}
	//mysqli_free_result($result);
	return $ids;
}
function getInitiativeColor($percent, $dueDate, $globalDate, $status)
{
	if($dueDate < $globalDate && $percent < 100)
	{
		$color = "bg-danger";
		$textColor = "red";
		$status = "Behind Schedule";
	}
	else if($globalDate < $dueDate && $percent < 100) 
	{
		$color = "bg-warning";
		$textColor = "gold";
		$status = "On Track";
	}
	else if($status == "Completed" || $percent == 100) 
	{ 
		$color = "bg-success";
		$textColor = "green";
		$status = "Completed";
	}
	else 
	{
		$color = "#FFFFFF";
		$textColor = "#000";
	}
	return $color;
}
function getInitiatives($objId, $globalDate)
{
	global $connect;
	$initiatives = "<table>";
	$initiativeCount = 1;
	$result = mysqli_query($connect, "SELECT initiative.id AS id, initiative.name AS name, initiative.dueDate AS dueDate 
	FROM initiative, initiativeimpact 
	WHERE initiativeimpact.linkedobjectid = '$objId' 
	AND initiativeimpact.initiativeid = initiative.id");//The Org Scorecard transends years hence the need to pull all initiatives; removed archived exclusion. LTK 05 June 2022 2048 hrs
	while($row = mysqli_fetch_assoc($result))
	{
		$id = $row["id"];
		$dueDate = $row["dueDate"];
		$resultStatus = mysqli_query($connect, "SELECT percentageCompletion, status 
		FROM initiative_status 
		WHERE initiativeId = '$id'
		ORDER BY updatedOn DESC LIMIT 1") or file_put_contents("error.txt", "Error = > ".mysqli_error());
		//Removed AND updatedOn <=  '$globalDate%' from above query to allow for updates done after the year has passed. This is rather punitive and not practical. LTK 11.01.23 0912hrs
		$resultStatusCount = mysqli_num_rows($resultStatus);
		$resultStatus = mysqli_fetch_assoc($resultStatus);
		
		if($resultStatusCount > 0)
		{
			$status = $resultStatus["status"];
			$percent = $resultStatus["percentageCompletion"];
		}
		else
		{
			$status = "-";
			$percent = "-";
		}
		
		$colorInitiative = getInitiativeColor($percent, $dueDate, $globalDate, $status);
		if($percent != "") $percent = $percent."%";
		
		$initiatives = $initiatives."<tr><td valign='top'>".$initiativeCount.'</td><td id="init'.$id.'" style="cursor: pointer; text-decoration: underline; color: blue;" onClick="getInitContent('.$id.')" onMouseOut="removeTooltip()">'.$row["name"]."</td><td valign='top'>".$percent.'</td><td valign="top"><div class="rounded-circle trafficLightBootstrap '.$colorInitiative.'"></div></td></tr>';
		$initiativeCount++;
	}
	$initiatives = $initiatives."</table>";
	return $initiatives;
}

function getInitiativesIndividuals($objId, $globalDate)
{
	global $connect;
	$initiatives = "";
	//$result = mysqli_query($connect, "SELECT id, name, dueDate FROM initiative WHERE projectManager = '$objId' AND archive != 'Yes'") or file_put_contents("error.txt", "Error = > ".mysqli_error());
	$result = mysqli_query($connect, "SELECT id, name, dueDate FROM initiative WHERE projectManager = '$objId'") or file_put_contents("error.txt", "Error = > ".mysqli_error());//The Org Scorecard transends years hence the need to pull all initiatives. LTK 05 June 2022 2048 hrs
	while($row = mysqli_fetch_assoc($result))
	{
		$id = $row["id"];
		$dueDate = $row["dueDate"];
		//file_put_contents("noLinks.txt", "objId = $id; dueDate => ".$globalDate);
		$resultStatus = mysqli_query($connect, "SELECT percentageCompletion, status 
		FROM initiative_status 
		WHERE initiativeId = '$id'
		ORDER BY updatedOn DESC LIMIT 1") or file_put_contents("error.txt", "Error = > ".mysqli_error());
		//Removed AND updatedOn <=  '$globalDate%' from above query to allow for updates done after the year has passed. This is rather punitive and not practical. LTK 11.01.23 0912hrs
		$resultStatus = mysqli_fetch_assoc($resultStatus);
		$status = $resultStatus["status"];
		$percent = $resultStatus["percentageCompletion"];
		$colorInitiative = getInitiativeColor($percent, $dueDate, $globalDate, $status);
		if($percent != "") $percent = $percent."%";
		
		$initiatives = $initiatives.'<tr><td colspan="3">'.$row["name"]."</td><td style='white-space:nowrap;' valign='top'>".$percent.'<div style="float:right;" class="rounded-circle trafficLightBootstrap '.$colorInitiative.'"></div></td></tr>';
	}
	
	return $initiatives;
}
//echo "<table>".getInitiativesIndividuals("ind2", "2021-09")."</table>";


function getIndividuals($orgId, $globalDate)
{
	global $connect;
	$individuals = "<table>";
	$result = mysqli_query($connect, "SELECT id, user_id, display_name 
	FROM uc_users 
	WHERE department = '$orgId'
	AND title != 'Managing Director'");
	while($row = mysqli_fetch_assoc($result))
	{	
	//onClick="getInitContent('.$id.')"
		$individuals = $individuals.'<tr>'.
		'<td style="cursor: pointer; text-decoration: underline; color: blue;" id="'.$row["user_id"].'" onClick="getIndContent('.$row["id"].')">'.
		$row["display_name"].
		'</td>'.
		'<td valign="top">'.individualScore($row["user_id"], $globalDate).'</td>'.
		'</tr>';
	}
	$individuals = $individuals."</table>";
	return $individuals;
}
function array_flatten($array)
{
	$result = array();
	foreach($array as $arr)
	{
		$result = array_merge($result , $arr);
	}
	return $result;
} 
function orgPerspObjKpiScore($orgId)
{
//1.0 Full Structure: Org, Persp, Obj, KPI
$kpiArray = array();
$kpiCount = 0;
$perspCount = count(getPerspectives($orgId));

if($perspCount > 0)
{
	$perspectives = getPerspectives($orgId);
	for($i = 0; $i < $perspCount; $i++)
	{
		$objCount = count(getObjectives($perspectives[$i]["id"]));
		if($objCount > 0)
		{
			$objectives = getObjectives($perspectives[$i]["id"]);
		
			for($j = 0; $j < $objCount; $j++)
			{
				$kpiArray[$kpiCount] = getMeasures($objectives[$j]["id"]);
				$kpiCount ++;
			}
		}
	}
	
	$kpiArray = array_flatten($kpiArray);
	
	//$json = json_encode($kpiArray, JSON_PRETTY_PRINT); 
	//printf("<pre>%s</pre>", $json);
	//print_r($json);
	return getScore($kpiArray);
}
}
function orgPerspKpiScore($orgId)
{
//2.0 Incomplete Structure: Org, Persp, KPI
$kpiArray = array();
$kpiCount = 0;

$perspCount = count(getPerspectives($orgId));

//Setup the details for the table structure before diplaying the contents
if($perspCount > 0)//There are perspectives to be displayed
{
	$perspectives = getPerspectives($orgId);
	for($i = 0; $i < $perspCount; $i++)
	{
		//echo $perspectives[$i]["id"];
		$kpiPerspCount = count(getMeasures($perspectives[$i]["id"]));
		
		//Check whether the perspective has measures?
		if($kpiPerspCount > 0)
		{
			$kpiArray[$kpiCount] = getMeasures($perspectives[$i]["id"]);
			$kpiCount ++;
		}
	}
	$kpiArray = array_flatten($kpiArray);
	
	return getScore($kpiArray);
}

}
function orgObjKpiScore($orgId)
{
//3.0 Incomplete Structure: Org, Obj, KPI
$kpiArray = array();
$kpiCount = 0;
$objCount = count(getObjectives($orgId));

//Setup the details for the table structure before diplaying the contents
if($objCount > 0)//There are objectives to be displayed
{
	$objectives = getObjectives($orgId);
	for($i = 0; $i < $objCount; $i++)
	{
		$kpiCounter = count(getMeasures($objectives[$i]["id"]));
		
		//Check whether the objective has measures?
		if($kpiCounter > 0)
		{
			$kpiArray[$kpiCount] = getMeasures($objectives[$i]["id"]);
			$kpiCount++;
		}
	}
	$kpiArray = array_flatten($kpiArray);
	return getScore($kpiArray);
}

}
function orgKpiScore($orgId)
{
	//4.0 Incomplete Structure: Org, KPI
	$kpiArray = array();
	$kpiCount = 0;
	
	$kpiOrgCount = count(getMeasures($orgId));
	
	//Setup the details for the table structure before diplaying the contents
	if($kpiOrgCount > 0)//There are measures to be displayed
	{
		$kpiArray[$kpiCount] = getMeasures($orgId);
		$kpiCount++;
	}
	$kpiArray = array_flatten($kpiArray);
	
	return getScore($kpiArray);
}
function perspObjKpiScore($perspId)
{
	$kpiArray = array();
	$kpiCount = 0;
	$objCount = count(getObjectives($perspId));
	if($objCount > 0)
	{
		$objectives = getObjectives($perspId);
	
		for($j = 0; $j < $objCount; $j++)
		{
			$kpiArray[$kpiCount] = getMeasures($objectives[$j]["id"]);
			$kpiCount ++;
		}
		$kpiArray = array_flatten($kpiArray);
	}
	return getScore($kpiArray);
}
function perspKpiScore($perspId)
{
	$kpiArray = array();
	$kpiCount = 0;
	
	$kpiPerspCount = count(getMeasures($perspId));
	
	//Check whether the perspective has measures?
	if($kpiPerspCount > 0)
	{
		$kpiArray[$kpiCount] = getMeasures($perspId);
		$kpiCount ++;
	}
	$kpiArray = array_flatten($kpiArray);
	return getScore($kpiArray);
}
function getObjScore($objId)
{
	//3.0 Incomplete Structure: Org, Obj, KPI
	$kpiArray = array();
	$kpiCount = 0;
	$kpiCounter = count(getMeasures($objId));
	
	//Check whether the objective has measures?
	if($kpiCounter > 0)
	{
		$kpiArray[$kpiCount] = getMeasures($objId);
		$kpiCount++;
	}
	$kpiArray = array_flatten($kpiArray);
	return getScore($kpiArray);
}
function getKpiScore($kpiId)
{
	$kpiArray = array();
	$kpiArray[0]["id"] = $kpiId;
	return getScore($kpiArray);
}
?>