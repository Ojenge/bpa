<?php

include_once("../config/config_msqli.php");

function getScore($kpiArray)
{
	$totalScore = NULL;
	$scoreCount = 0;
	for($i=0; $i<count($kpiArray); $i++)
	{
		$kpiId = $kpiArray[$i]["id"];
		$kpiQuery = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT gaugeType, calendarType FROM measure WHERE id = '$kpiId'");
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
		$kpi = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT actual, red, green, darkGreen FROM $table WHERE measureId = '$kpiId' ORDER BY date DESC LIMIT 1");
		$kpiCount = mysqli_num_rows($kpi);
		$kpi = mysqli_fetch_assoc($kpi);
		
		$actual = $kpi["actual"];
		$red = $kpi["red"];
		$green = $kpi["green"];
		
		if($kpiCount == 0) 
		{
			//Do nothing - don't compute and count on kpis without actuals
		}
		else
		{
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
					$score = ((abs($actual) - abs($red))/(abs($green) - abs($red)) * ((1/3)+3)) + ((1/3)+3);
					if($score > 10) $score = 10;
					if($score < 0) $score = 0;
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
	$ids = array();
	$result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, name FROM organization WHERE id = '$orgId'");
	$row = mysqli_fetch_assoc($result);
	$ids["name"] = $row["name"];
	$ids["id"] = $row["id"];
	//$ids["score"] = getOrgScore($orgId);
	mysqli_free_result($result);
	return $ids;
}
function getPerspectives($orgId)
{
	$arrayPosition = 0;
	$ids = array();
	$result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, name FROM perspective WHERE parentId = '$orgId'");
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
	$ids = array();
	$arrayPosition = 0;
	$result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, name FROM objective WHERE linkedObject = '$parentId'");
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
	$ids = array();
	$arrayPosition = 0;
	$result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, name, calendarType FROM measure WHERE linkedObject = '$parentId'");
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
		$result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT actual, green, darkGreen FROM $table WHERE measureId = '".$row["id"]."' ORDER BY date DESC LIMIT 1");
		$actual = mysqli_fetch_assoc($result);
		$actualCount = mysqli_num_rows($result);
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
	$initiatives = "<table>";
	$initiativeCount = 1;
	$result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT initiative.id AS id, initiative.name AS name, initiative.dueDate AS dueDate 
	FROM initiative, initiativeimpact 
	WHERE initiativeimpact.linkedobjectid = '$objId' 
	AND initiativeimpact.initiativeid = initiative.id
	AND initiative.archive != 'Yes'");
	while($row = mysqli_fetch_assoc($result))
	{
		$id = $row["id"];
		$dueDate = $row["dueDate"];
		$resultStatus = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT percentageCompletion, status 
		FROM initiative_status 
		WHERE initiativeId = '$id'
		AND updatedOn <=  '$globalDate%'
		ORDER BY updatedOn DESC LIMIT 1") or file_put_contents("error.txt", "Error = > ".mysqli_error());
		$resultStatus = mysqli_fetch_assoc($resultStatus);
		$status = $resultStatus["status"];
		$percent = $resultStatus["percentageCompletion"];
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
	$initiatives = "";
	$result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, name, dueDate FROM initiative WHERE projectManager = '$objId' AND archive != 'Yes'") or file_put_contents("error.txt", "Error = > ".mysqli_error());
	while($row = mysqli_fetch_assoc($result))
	{
		$id = $row["id"];
		$dueDate = $row["dueDate"];
		//file_put_contents("noLinks.txt", "objId = $id; dueDate => ".$globalDate);
		$resultStatus = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT percentageCompletion, status 
		FROM initiative_status 
		WHERE initiativeId = '$id'
		AND updatedOn <=  '$globalDate%'
		ORDER BY updatedOn DESC LIMIT 1") or file_put_contents("error.txt", "Error = > ".mysqli_error());
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

function individualScore($objectId, $globalDate) //trying to simplify life LTK 10 March 2021 0215hours; Adding date select functionality. LTK 20Aug2021 1530Hrs
{
	//file_put_contents("score.txt", "objectId = $objectId, globalDate = $globalDate");
	$totalScore = 0;
	
	/*$avScore = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT initiativeid 
	FROM initiativeimpact 
	WHERE initiativeimpact.linkedobjectid = '$objectId'");*/
	$avScore = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id 
	FROM initiative 
	WHERE projectManager = '$objectId' AND archive = 'No'");
	$count = mysqli_num_rows($avScore);
	$globalDate = strtotime($globalDate);
    $globalDate = date("Y-m", strtotime("+1 month", $globalDate)); //Added this since the date wasn't returning initiatives updated within the same month. LTK 30 Oct 2015 2315hrs
	while($row = mysqli_fetch_assoc($avScore))
	{
		$id = $row["id"];
		//file_put_contents("track.txt", "\nNumber of initiatives for $objectId under date $globalDate? are ".$count. " with ID $id", FILE_APPEND);
		$actualQuery = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT percentageCompletion 
		FROM initiative_status 
		WHERE initiativeId = '$id'
		AND updatedOn <  '$globalDate%'
		ORDER BY updatedOn DESC LIMIT 1");
        $countTwo = mysqli_num_rows($actualQuery);
		$actualResult = mysqli_fetch_assoc($actualQuery);
        //file_put_contents("track.txt", "\nScore for id $id under date $globalDate => ".$actualResult["percentageCompletion"], FILE_APPEND);
		$totalScore = $totalScore + $actualResult["percentageCompletion"];
	}
	if($totalScore == 0 || $count == 0)
	{
		$avScore = "No Score";
	}
	else
	{
		$avScore = $totalScore / $count;
		$avScore = $avScore / 10;
	}

	//Include KPIS for those individuals who have allocated measures
	$kpiScore = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, calendarType 
	FROM measure 
	WHERE owner = '$objectId'");
	$kpiCount = mysqli_num_rows($kpiScore);
	$kpiScoreTotal = 0;
	while($kpiRow = mysqli_fetch_array($kpiScore))
	{
		$calendarType = $kpiRow['calendarType'];
		$measureId = $kpiRow['id'];
		switch($calendarType)
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
				$objectDate = date("Y", strtotime($objectDate));
				break;	
			}
		}
		$measure_score_query = "SELECT 3score, date AS lastDate FROM $table WHERE measureId = '$measureId' ORDER BY date DESC LIMIT 1";
		$measure_score_result=mysqli_query($GLOBALS["___mysqli_ston"], $measure_score_query);
		$measure_score = mysqli_fetch_assoc($measure_score_result);
		$kpiScoreTotal = $kpiScoreTotal + $measure_score["3score"];
	}

	if($kpiScoreTotal == 0 || $kpiCount == 0)
	{
		//Do nothing
	}
	else
	{
		//file_put_contents("kpi.txt", "We have a score = $kpiScoreTotal");
		$kpiScoreTotal = $kpiScoreTotal / $kpiCount;
		if($objectId == "ind8")
		{
			$avScore = $avScore*15 + $kpiScoreTotal*85;
			$avScore = $avScore / (15+85);
		}
		else $avScore = ($avScore + $kpiScoreTotal) / 2;
	}

	return number_format((float)$avScore, 2);
}

$score = individualScore("ind94", "2021-12%");

echo "Score = ".$score;

function getIndividuals($orgId, $globalDate)
{
	$individuals = "<table>";
	$result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, user_id, display_name 
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