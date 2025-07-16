<?php
//include_once("../config/config_mysqli.php");
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/bpa/config/config_mysqli.php";
include_once($path);
//include_once("functions/calendar-labels.php");
//include_once("functions/perspOrg-scores.php");
//echo return_color(5.0000, "fourColor");

function return_color($score, $gaugeType)
{
	//$score = (int)$score;
	if($score == 'grey') 
	{
		return '#F2F1EF';
		exit;
	}
	else if($score == '' || $score == NULL) 
	{
		return '#F2F1EF';
		exit;
	}
	else
	{
		switch($gaugeType)
		{
			case "goalOnly":
			{ 
				$scoreColor = $score >= 5 ? "green" : "red"; 
				break;	
			}
			case "threeColor":
			{
				if($score >= 6.67) $scoreColor = "green";
				else if ($score < 6.67 && $score >=3.33) $scoreColor = "yellow";
				else if( $score < 3.33 && $score > 0) $scoreColor = "red";
				else $scoreColor = '#F2F1EF';
				break;	
			}
			case "fourColor":
			{
				if($score >= 7.5) $scoreColor = "darkGreen";
				if($score < 7.5 && $score >= 5) $scoreColor = "green";
				if( $score < 5 && $score >= 2.5) $scoreColor = "yellow";
				if( $score < 2.5) $scoreColor = "red";
				break;	
			}
		}
		return @$scoreColor;
	}
}
/*********************************************************************************************************************
BSC Traditional Scoring:
**********************************************************************************************************************/

function traditionalScoring($gaugeType, $actual, $red, $green, $darkGreen, $blue)
{
	global $goalScore, $threeScore, $fourScore, $fiveScore;

	// Convert string values to numeric, handle "NULL" strings
	$actual = ($actual === "NULL" || $actual === "" || $actual === null) ? 0 : (float)$actual;
	$red = ($red === "NULL" || $red === "" || $red === null) ? 0 : (float)$red;
	$green = ($green === "NULL" || $green === "" || $green === null) ? 0 : (float)$green;
	$darkGreen = ($darkGreen === "NULL" || $darkGreen === "" || $darkGreen === null) ? 0 : (float)$darkGreen;
	$blue = ($blue === "NULL" || $blue === "" || $blue === null) ? 0 : (float)$blue;

	//file_put_contents("scoreFunction.txt","\r\ngaugeType=>$gaugeType, actual=>$actual, red=>$red, green=>$green, darkGreen=>$darkGreen, blue=>$blue",FILE_APPEND);
	switch($gaugeType)
	{
		case "goalOnly":
		{
			/*********************************************************************************************************************
			1. 2 Color: Red Green
			*********************************************************************************************************************/
			if(abs($actual) < abs($green))
			$goalScore = 0;
			else
			$goalScore = 10;
			break;	
		}
		case "threeColor":
		{
			/*********************************************************************************************************************
			2. 3 Color: Red Yellow Green
			*********************************************************************************************************************/
			$denominator = abs($green) - abs($red);
			if($denominator == 0) {
				$threeScore = 0;
			} else {
				$threeScore = ((abs($actual) - abs($red))/$denominator * ((1/3)+3)) + ((1/3)+3);
			}
			if($threeScore > 10) $threeScore = 10;
			if($threeScore < 0) $threeScore = 0;
			return $threeScore;
			//echo "<br>** $actual, $red, $green = ".$threeScore." **<br>";
			exit;
			break;
		}
		case "fourColor":
		{
			/*********************************************************************************************************************
			3. 4 Color: Red Yellow Green DarkGreen
			*********************************************************************************************************************/
			if(abs($actual) <= abs($green))
			$fourScore = ((abs($actual) - abs($red))/(abs($green) - abs($red)) * 2.5) + 2.5;
			else if (abs($actual) > abs($green) && abs($actual) <= abs($darkGreen))
			$fourScore = ((abs($actual) - abs($green))/(abs($darkGreen) - abs($green)) * 2.5) + 5;
			else if(abs($actual) > abs($darkGreen))
			$fourScore = ((abs($actual) - abs($darkGreen))/(abs($darkGreen)) * 2.5) + 7.5;
			if($fourScore > 10) $fourScore = 10;
			if($fourScore < 0) $fourScore = 0;	
			break;	
		}
		case "fiveColor":
		{
			/*********************************************************************************************************************
			4. 5 Color: Red Yellow Green DarkGreen Blue
			*********************************************************************************************************************/
			if(abs($actual) <= abs($green))
			$fiveScore = ((abs($actual) - abs($red))/(abs($green) - abs($red)) * 2) + 2;
			else if (abs($actual) > abs($green) && abs($actual) <= $darkGreen)
			$fiveScore = ((abs($actual) - abs($green))/(abs($darkGreen) - abs($green)) * 2) + 4;
			else if(abs($actual) > $darkGreen && abs($actual) <= abs($blue))
			$fiveScore = ((abs($actual) - abs($darkGreen))/(abs($blue) - abs($darkGreen)) * 2) + 6;
			else if(abs($actual) > abs($blue))
			$fiveScore = ((abs($actual) - abs($blue))/(abs($blue)) * 2) + 8;
			if($fiveScore > 10) $fiveScore = 10;
			if($fiveScore < 0) $fiveScore = 0;
			break;	
		}
	}
}
function kpiGauge($objectId, $objectDate)
{
	global $connect;
	$get_gauge = "SELECT gaugeType, calendarType, name FROM measure WHERE id = '$objectId'";
	$get_gauge_result=mysqli_query($connect, $get_gauge);
	$gauge = mysqli_fetch_assoc($get_gauge_result);
	if(!$gauge) return "No Score"; // Handle case where measure doesn't exist
	$calendarType = $gauge['calendarType'];
	$kpiName = $gauge['name'];
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
	$measure_score_query = "SELECT 3score, date AS lastDate FROM $table WHERE measureId = '$objectId' ORDER BY date DESC LIMIT 1";
		
	$measure_score_result=mysqli_query($connect, $measure_score_query);
	$measure_score = mysqli_fetch_assoc($measure_score_result);
	$measureCount = mysqli_num_rows($measure_score_result);
	if($measureCount == 0 || !$measure_score)
	{
		$score["score"] = "No Score";
		$score["gaugeType"] = $gauge["gaugeType"];
		$score["lastDate"] = "No Score";
		//$score["kpiName"] = $kpiName;
		$final = $score["score"];
	}
	else
	{
		$score["gaugeType"] = $gauge["gaugeType"];
		$score["score"] = $measure_score["3score"];
		$score["lastDate"] = $measure_score["lastDate"];
		$score["kpiName"] = $kpiName;
		$final = round($score["score"],2);
		if($score["score"] == NULL || $score["score"] == '') 
		{
			$score["score"] = "No Score";
			$final = $score["score"];
		}
	}
	return $final;
	//file_put_contents("kpiGauge.txt", "Score: $final, $objectId, $calendarType, $measureCount");
	flush();
}
function orgChildIds($objectId)
{
	global $connect;
	// Level One - get organizations under the subject in question
	$organization_level_query = "SELECT id FROM organization WHERE cascadedfrom LIKE '$objectId' OR id = '$objectId'";
	$organization_level_result = mysqli_query($connect, $organization_level_query);
	if(mysqli_num_rows($organization_level_result) > 0)
	{
		$organization_count = 0;
		while($row=mysqli_fetch_assoc($organization_level_result))
		{
			$org_id_store[$organization_count] = (string)$row["id"];
			$organization_count++;
		}
	$org_id_store = implode("','",$org_id_store);
	$org_id_store = "'".$org_id_store."'";
	}
	else $org_id_store = NULL;
	//$ind_while_in = implode("','", $org_id_store);
	//$ind_while_in = "'".$ind_while_in."'";
	//if($ind_id_store != NULL)$ind_while_in = $ind_while_in.",".$org_id_store;
	//Level One point one - get individuals under the subject in question
	$individual_level_query = "SELECT user_id FROM uc_users WHERE department IN ($org_id_store)";
	$individual_level_result = mysqli_query($connect, $individual_level_query);
	if(mysqli_num_rows($individual_level_result) > 0)
	{
		$individual_count = 0;
		while($row=mysqli_fetch_assoc($individual_level_result))
		{
			$ind_id_store[$individual_count] = (string)$row["user_id"];
			$ind_id_store[$individual_count];
			$individual_count++;
		}
	
	$ind_id_store = implode("','",$ind_id_store);
	$ind_id_store = "'".$ind_id_store."'";
	}
	else $ind_id_store = NULL;
	$persp_while_in = $org_id_store.$ind_id_store;
	
	//Level Two - get respective perspectives
	//$persp_while_in = implode("','", $org_id_store);
	//$persp_while_in = "'".$persp_while_in."'";
	//if($ind_id_store != NULL)$persp_while_in = $persp_while_in.",".$ind_id_store;
	//var_dump($persp_while_in);
	//echo $persp_while_in."<br>";
	$perspective_level_query = "SELECT id FROM perspective WHERE parentId IN ($persp_while_in)";
	$perspective_level_result = mysqli_query($connect, $perspective_level_query);
	if(@mysqli_num_rows($perspective_level_result) > 0)
	{
		$perspective_count = 0;
		while($row = mysqli_fetch_assoc($perspective_level_result))
		{
			$persp_id_store[$perspective_count] = $row["id"];
			$perspective_count++;
		}	
		$obj_id_while_in = @implode("','",$persp_id_store);
		$obj_id_while_in = "'".$obj_id_while_in."',".$persp_while_in;
	}
	else $obj_id_while_in = $persp_while_in;
	//echo $obj_id_while_in;
	//Level Three - get objectives
	$objective_level_query = "SELECT id FROM objective WHERE (linkedObject) IN ($obj_id_while_in)";
	$objective_level_result = mysqli_query($connect, $objective_level_query);
	if(@mysqli_num_rows($objective_level_result) > 0)
	{
		$objective_count = 0;
		while($row = mysqli_fetch_assoc($objective_level_result))
		{
			$obj_id_store[$objective_count] = $row["id"];
			$objective_count++;
		}
	$kpi_id_while_in = @implode("','",$obj_id_store);
	$kpi_id_while_in = "'".$kpi_id_while_in."'";
	$final_while_in = $kpi_id_while_in.",".$obj_id_while_in;
	}
	else
	{
		$final_while_in = $obj_id_while_in;	
	}
	$objectId = $final_while_in;
	return $objectId;
}

function perspChildIds($objectId)
{
	global $connect;
	$objective_level_query = "SELECT id FROM objective WHERE linkedObject = '$objectId'";
	$objective_level_result = mysqli_query($connect, $objective_level_query);
	if(mysqli_num_rows($objective_level_result) > 0)
	{
		$objective_count = 0;
		while($row = mysqli_fetch_assoc($objective_level_result))
		{
			$obj_id_store[$objective_count] = $row["id"];
			$objective_count++;
		}
		$obj_id_while_in = implode("','",$obj_id_store);
		$obj_id_while_in = "'".$obj_id_while_in."'";
		$final_while_in = $obj_id_while_in.",'".$objectId."'";
	}
	else
	{
		$final_while_in = "'".$objectId."'";
	}
	$objectId = $final_while_in;
	return $objectId;
}

function objChildIds($objectId)
{
	global $connect;
	$objective_level_query = "SELECT id FROM objective WHERE linkedObject = '$objectId'";
	$objective_level_result = mysqli_query($connect, $objective_level_query);
	if(mysqli_num_rows($objective_level_result) > 0)
	{
		$objective_count = 0;
		while($row = mysqli_fetch_assoc($objective_level_result))
		{
			$obj_id_store[$objective_count] = $row["id"];
			$objective_count++;
		}
		$obj_id_while_in = implode("','",$obj_id_store);
		$obj_id_while_in = "'".$obj_id_while_in."'";
		$final_while_in = $obj_id_while_in.",'".$objectId."'";
	}
	else
	{
		$final_while_in = "'".$objectId."'";	
	}
	$objectId = $final_while_in;
	return $objectId;
}

function indChildIds($objectId)
{
	global $connect;
	// Level One - get user id
	$organization_level_query = "SELECT user_id FROM uc_users WHERE user_id = '$objectId'";
	$organization_level_result = mysqli_query($connect, $organization_level_query);
	if(mysqli_num_rows($organization_level_result) > 0)
	{
		$organization_count = 0;
		while($row=mysqli_fetch_assoc($organization_level_result))
		{
			$org_id_store[$organization_count] = (string)$row["user_id"];
			$organization_count++;
		}
	$org_id_store = implode("','",$org_id_store);
	$org_id_store = "'".$org_id_store."'";
	}
	else $org_id_store = NULL;
	//Level One point one - get individuals under the subject in question - skipped this since we don't factor this in individual scores
	
	//Level Two - get respective perspectives
	$perspective_level_query = "SELECT id FROM perspective WHERE parentId IN ($org_id_store)";
	$perspective_level_result = mysqli_query($connect, $perspective_level_query);
	if(@mysqli_num_rows($perspective_level_result) > 0)
	{
		$perspective_count = 0;
		while($row = mysqli_fetch_assoc($perspective_level_result))
		{
			$persp_id_store[$perspective_count] = $row["id"];
			$perspective_count++;
		}	
		$obj_id_while_in = @implode("','",$persp_id_store);
		$obj_id_while_in = "'".$obj_id_while_in."',".$org_id_store;
	}
	else $obj_id_while_in = $org_id_store;
	//echo $obj_id_while_in;
	//Level Three - get objectives
	$objective_level_query = "SELECT id FROM objective WHERE (linkedObject) IN ($obj_id_while_in)";
	$objective_level_result = mysqli_query($connect, $objective_level_query);
	if(@mysqli_num_rows($objective_level_result) > 0)
	{
		$objective_count = 0;
		while($row = mysqli_fetch_assoc($objective_level_result))
		{
			$obj_id_store[$objective_count] = $row["id"];
			$objective_count++;
		}
	$kpi_id_while_in = @implode("','",$obj_id_store);
	$kpi_id_while_in = "'".$kpi_id_while_in."'";
	$final_while_in = $kpi_id_while_in.",".$obj_id_while_in;
	}
	else
	{
		$final_while_in = $obj_id_while_in;	
	}
	
	$objectId = $final_while_in;
	return $objectId;
}
//echo indChildIds("ind46");
function objective_score($objective_id, $objectDate, $table)
{
	global $connect;
	$measure_query="SELECT id, name, calendarType, weight FROM measure WHERE linkedObject = '$objective_id'
	OR id = (SELECT measure_id FROM measurelinks WHERE linked_id = '$objective_id')"; //added the OR portion to factor in linked measures. LTK 15Aug2021 1152hrs
	$measure_result=mysqli_query($connect, $measure_query);
	$count = 0; $objScore = NULL;
	if(@mysqli_num_rows($measure_result) > 0)
	{
	while($row = mysqli_fetch_assoc($measure_result))
	{
		$obj_row["Measure Count"] = $count;
		$obj_row["Measure".$count] = $row["name"];
		$measure_id = $row["id"];
		$measure_table = $row["calendarType"];
		$weight = $row["weight"];
		switch($measure_table)
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
		$measure_score_query = "SELECT 3score, date AS lastDate FROM $table WHERE measureId = '$measure_id' ORDER BY date DESC LIMIT 1";
		
		$measure_score_result = mysqli_query($connect, $measure_score_query);
		$row = mysqli_fetch_assoc($measure_score_result);
		if(mysqli_num_rows($measure_score_result) > 0)
		{
			$score = $row["3score"];
			if($score == 0)
			{
				$objScore = $objScore + 0;
				$count++;
			}
			else if($score != NULL || $score != ' ')
			{
				// Convert to numeric values to prevent string multiplication error
				$score = (float)$score;
				$weight = (float)$weight;
				$score = $score * $weight; //LTK 9th March 2021 0055hours. Adding weight computation functionality
				//file_put_contents("weight.txt", "\nscore = $score; weight = $weight", FILE_APPEND);
				$objScore = $objScore + round($score, 2);
				$count++;
			}
		}
	}
	}
	if($objScore == 0) return "0";	
	else if($objScore == NULL || $objScore == '') return "\"No Score\"";
	else return $objScore;
}

function cascaded_departments_score($organization_id, $objectDate, $table)
{
	global $connect;
	$objectId = $organization_id;
	$organization_level_query = "SELECT id FROM organization WHERE cascadedfrom LIKE '$objectId' OR id = '$objectId'";
	$organization_level_result = mysqli_query($connect, $organization_level_query);
	if(mysqli_num_rows($organization_level_result) > 0)
	{
		$organization_count = 0;
		while($row=mysqli_fetch_assoc($organization_level_result))
		{
			$org_id_store[$organization_count] = (string)$row["id"];
			$organization_count++;
		}
		$org_while_in = implode("','", $org_id_store);
		$org_while_in = "'".$org_while_in."'";
	}
	else
	$org_while_in = NULL;
	//Level One point one - get individuals under the subject in question
	$individual_level_query = "SELECT user_id FROM uc_users WHERE department = '$objectId'";
	$individual_level_result = mysqli_query($connect, $individual_level_query);
	if(mysqli_num_rows($individual_level_result) > 0)
	{
		$individual_count = 0;
		while($row=mysqli_fetch_assoc($individual_level_result))
		{
			$ind_id_store[$individual_count] = (string)$row["user_id"];
			$ind_id_store[$individual_count];
			$individual_count++;
		}
		$ind_id_store = implode("','",$ind_id_store);
		$ind_id_store = "'".$ind_id_store."'";
		$persp_while_in = $org_while_in.','.$ind_id_store;
	}
	else $persp_while_in = $org_while_in;
	
	//Level Two - get respective perspectives
	$perspective_level_query = "SELECT id FROM perspective WHERE parentId IN ($persp_while_in)";
	$perspective_level_result = mysqli_query($connect, $perspective_level_query);
	if(@mysqli_num_rows($perspective_level_result) > 0)
	{
		$perspective_count = 0;
		while($row = mysqli_fetch_assoc($perspective_level_result))
		{
			$persp_id_store[$perspective_count] = $row["id"];
			$perspective_count++;
		}	
		$obj_id_while_in = implode("','",$persp_id_store);
		$obj_id_while_in = "'".$obj_id_while_in."',".$persp_while_in;
	}
	else $obj_id_while_in = $persp_while_in;
	
	//Level Three - get objectives
	$objective_level_query = "SELECT id FROM objective WHERE linkedObject IN ($obj_id_while_in)";
	$objective_level_result = mysqli_query($connect, $objective_level_query);
	if(@mysqli_num_rows($objective_level_result) > 0)
	{
		$objective_count = 0;
		while($row = mysqli_fetch_assoc($objective_level_result))
		{
			$obj_id_store[$objective_count] = $row["id"];
			$objective_count++;
		}
	$kpi_id_while_in = implode("','",$obj_id_store);
	$kpi_id_while_in = "'".$kpi_id_while_in."'";
	$final_while_in = $kpi_id_while_in.",".$obj_id_while_in;
	}
	else
	{
		$final_while_in = $obj_id_while_in;	
	}
	//Level Four - Get Measures :-)
	$kpi_level_query = "SELECT id, calendarType FROM measure WHERE linkedObject IN ($final_while_in)";
	$kpi_level_result = mysqli_query($connect, $kpi_level_query);
	if(@mysqli_num_rows($kpi_level_result) > 0)
	{
		$scoreCount = 0;
		$finalScore = NULL;
		while($row = mysqli_fetch_assoc($kpi_level_result))
		{
			$id = $row["id"];
			$calendarType = $row['calendarType'];
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
					break;	
				}
			}
			
			//$scoreQuery = mysqli_query($connect, "SELECT AVG(3score) FROM $table WHERE measureId ='$id' AND date LIKE '$objectDate%' AND 3score IS NOT NULL");
			//$scoreQuery = mysqli_query($connect, "SELECT AVG(3score) FROM $table WHERE measureId ='$id' AND date <= '$objectDate%' ORDER BY date DESC LIMIT 1");Seems i'd forgotten to update this from AVG(3score) to 3score
			$scoreQuery = mysqli_query($connect, "SELECT 3score FROM $table WHERE measureId ='$id' AND date LIKE '$objectDate%' ORDER BY date DESC LIMIT 1");
			$scoreResult = mysqli_fetch_array($scoreQuery);
			if($scoreResult && ($scoreResult['3score'] != NULL && $scoreResult['3score'] != ''))
			{
				$finalScore = $finalScore + $scoreResult['3score'];
				$scoreCount++;
			}
		}
		if($finalScore == 0) $finalScore = "0";
		else
		$finalScore = $finalScore / $scoreCount;
	}
	else
	{
	 return 'grey';
	 exit;	
	}
	if(@$finalScore) return floor($finalScore * 100) / 100;
	else return "grey";
	//return $score;
}

function organization_score($organization_id, $objectDate, $table)
{
	global $connect;
	$finalScore = NULL;
	$scoreCount = 0;
	$perspective_query = mysqli_query($connect, "SELECT id, weight FROM perspective WHERE parentId = '$organization_id'");
	while($row = @mysqli_fetch_assoc($perspective_query))
	{
		$perspective_id = $row['id'];
		$weight = $row['weight'];

		$returnedScore = perspective_score($perspective_id, $objectDate, $table);
		//file_put_contents("weight.txt", "weight = $weight and score  = $returnedScore");
		// Convert to numeric values to prevent string multiplication error
		$returnedScore = (float)$returnedScore;
		$weight = (float)$weight;
		$finalScore = $finalScore + ($returnedScore * $weight);
		if($returnedScore == NULL || $returnedScore == '' || $returnedScore == 'grey' || $returnedScore == 0) {} else $scoreCount++;
	}
	if($finalScore == 0) $finalScore = 0;
	else
	return $finalScore = round($finalScore, 2);
}

function perspective_score($perspective_id, $objectDate, $table)
{
	global $connect;
	$finalScore = NULL;
	$scoreCount = 0;
	$perspective_query = mysqli_query($connect, "SELECT id, weight FROM objective WHERE linkedObject = '$perspective_id'");
	while($row = mysqli_fetch_assoc($perspective_query))
	{
		$objective_id = $row['id'];
		$weight = $row['weight'];
		$returnedScore = objective_score($objective_id, $objectDate, $table);
		// Convert to numeric values to prevent string multiplication error
		$returnedScore = (float)$returnedScore;
		$weight = (float)$weight;
		$finalScore = $finalScore + ($returnedScore * $weight);
		if($returnedScore == NULL || $returnedScore == '' || $returnedScore == 'grey' || $returnedScore == 0) {} else $scoreCount++;
	}
	if($finalScore == 0) $finalScore = 0;
	else
	return $finalScore = round($finalScore, 2);
}
function individualScore($objectId, $globalDate) //trying to simplify life LTK 10 March 2021 0215hours; Adding date select functionality. LTK 20Aug2021 1530Hrs
{
	global $connect;
	//file_put_contents("score.txt", "objectId = $objectId, globalDate = $globalDate");
	$totalScore = null;
	$avScore = null;
	/*$avScore = mysqli_query($connect, "SELECT initiativeid 
	FROM initiativeimpact 
	WHERE initiativeimpact.linkedobjectid = '$objectId'");*/
	$avScore = mysqli_query($connect, "SELECT id 
	FROM initiative 
	WHERE projectManager = '$objectId' AND archive = 'No'");
	$count = mysqli_num_rows($avScore);
	$globalDate = strtotime($globalDate);
    $globalDate = date("Y-m-d", strtotime("+1 month", $globalDate)); //Added this since the date wasn't returning initiatives updated within the same month. LTK 30 Oct 2015 2315hrs
	while($row = mysqli_fetch_assoc($avScore))
	{
		$id = $row["id"];
		//file_put_contents("track.txt", "\nNumber of initiatives for $objectId under date $globalDate? are ".$count. " with ID $id", FILE_APPEND);
		$actualQuery = mysqli_query($connect, "SELECT percentageCompletion
		FROM initiative_status
		WHERE initiativeId = '$id'
		AND updatedOn LIKE '$globalDate%'
		ORDER BY updatedOn DESC LIMIT 1");
		$actualResult = mysqli_fetch_assoc($actualQuery);
        //file_put_contents("track.txt", "\nScore for id $id under date $globalDate => ".$actualResult["percentageCompletion"], FILE_APPEND);
		if($actualResult && !empty($actualResult["percentageCompletion"])) $totalScore = $totalScore + $actualResult["percentageCompletion"];
	}
	if($totalScore == 0 || $count == 0)
	{
		$avScore = null;
	}
	else
	{
		$avScore = $totalScore / $count;
		$avScore = $avScore / 10;
	}

	//Include KPIS for those individuals who have allocated measures
	$kpiScore = mysqli_query($connect, "SELECT id, calendarType 
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
				break;
			}
		}
		$measure_score_query = "SELECT 3score, date AS lastDate FROM $table WHERE measureId = '$measureId' ORDER BY date DESC LIMIT 1";
		$measure_score_result=mysqli_query($connect, $measure_score_query);
		$measure_score = mysqli_fetch_assoc($measure_score_result);
		if($measure_score && !empty($measure_score["3score"])) $kpiScoreTotal = $kpiScoreTotal + $measure_score["3score"];
	}

	if($kpiScoreTotal == 0 || $kpiCount == 0 || empty($kpiScoreTotal))
	{
		//Do nothing
	}
	else
	{
		//file_put_contents("kpi.txt", "We have a score = $kpiScoreTotal; to be divided with kpiCount of $kpiCount. Current avScore = $avScore");
		$kpiScoreTotal = $kpiScoreTotal / $kpiCount;
		$avScore = ($avScore + $kpiScoreTotal) / 2;
	}
	//return $avScore;
	return number_format((float)$avScore, 2);
}
function individualScoreDelete($objectId) //trying to simplify life LTK 10 March 2021 0215hours
{
	global $connect;
	$totalScore = 0;
	//$avScore = mysqli_query($connect, "SELECT initiativeid FROM initiativeimpact WHERE initiativeimpact.linkedobjectid = '$objectId'");// The original assumption here was that they were linked not assigned to owners. 
	$avScore = mysqli_query($connect, "SELECT id FROM initiative WHERE projectManager = '$objectId'");
	$count = mysqli_num_rows($avScore);
	//file_put_contents("functions/avScore.txt", "\nCount = $count", FILE_APPEND);
	while($row = mysqli_fetch_array($avScore))
	{
		$id = $row["id"];
		$actual = mysqli_query($connect, "SELECT percentageCompletion FROM initiative_status WHERE initiativeId = '$id' ORDER BY updatedOn DESC LIMIT 1");
		$actual = mysqli_fetch_assoc($actual);
		if($actual && $actual["percentageCompletion"] != NULL) $totalScore = $totalScore + $actual["percentageCompletion"];
	}
	$avScore = $totalScore / $count;
	$avScore = $avScore / 10;
	return $avScore;
}
//echo individualScore("ind2");
function individualScoreOld($objectId, $objectDate, $objectPeriod)
{ 
	global $connect;
	//Level One - Measures Added Directly to the Individual
	
	$childIds = indChildIds($objectId);
	
		switch($objectPeriod)
		{
			case "days":
			{
				$kpiIndScore = daysAsIs($childIds, $objectDate, 1);
				break;
			}
			/*******************************************************************************************************************
			$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
			********************************************************************************************************************/
			case "weeks":
			{
				//daysInWeeks($objectId, $objectDate, $valuesCount,  $red, $green, $darkgreen, $blue, $gaugeType);
				$kpiIndScore = weeksAsIs($childIds, $objectDate, 1);
				break;
			}
			/*******************************************************************************************************************
			$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
			********************************************************************************************************************/
			case "months":
			{
				
				$kpiIndScore = monthsAsIs($childIds, $objectDate, 1);
				break;
			}
			/*******************************************************************************************************************
			$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
			********************************************************************************************************************/
			case "quarters":
			{
				$kpiIndScore = quartersAsIs($childIds, $objectDate, 1);
				break;
			}	
			/*******************************************************************************************************************
			$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
			********************************************************************************************************************/
			case "halfyear":
			{
				$kpiIndScore = halfYearsAsIs($childIds, $objectDate, 1);
				break;
			}
			/*******************************************************************************************************************
			$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
			********************************************************************************************************************/
			case "years":
			{
				$kpiIndScore = yearsAsIs($childIds, $objectDate, 1);
				break;
			}
		}
		$kpiIndScore = round($kpiIndScore, 2);
	
	$objective_query = "SELECT id FROM objective WHERE linkedObject = '$objectId'";
	$objective_result = mysqli_query($connect, $objective_query);
	$objIndScore = NULL; $objIndCount = NULL;
	if(@mysqli_num_rows($objective_result) > 0)
	{
		while($row = mysqli_fetch_assoc($objective_result))
		{
			$objective_id = $row["id"];
			$score = objective_score($objective_id, $objectDate, 'measuremonths');
			//echo "score = $score<br>";
			if($score != NULL) 
			{
				$objIndScore = $objIndScore + round($score, 2);
				$objIndCount++;
			}
		}
		if($objIndScore == NULL || $objIndScore == ''){}
		else $objIndScore = $objIndScore/$objIndCount;
	}
	else $objIndScore = NULL;
	
	$perspective_query = "SELECT id FROM perspective WHERE linkedObject = '$objectId'";
	$perspective_result = mysqli_query($connect, $perspective_query);
	$perspIndScore = NULL; $perspIndCount = NULL;
	if(@mysqli_num_rows($perspective_result) > 0)
	{
		while($row = mysqli_fetch_assoc($perspective_result))
		{
			$perspective_id = $row["id"];
			$score = perspective_score($perspective_id, $objectDate, 'measuremonths');
			if($score != NULL) 
			{
				$perspIndScore = $perspIndScore + round($score, 2);
				$perspIndCount++;
			}
		}
		if($perspIndScore == NULL || $perspIndScore == ''){}
		else $perspIndScore = $perspIndScore/$perspIndCount;
	}
	else $perspIndScore = NULL;
	
	if($kpiIndScore >= 0 && $objIndScore == NULL && $perspIndScore == NULL)
	$finalIndScore = $kpiIndScore;
	else if($kpiIndScore == NULL && $objIndScore >= 0 && $perspIndScore == NULL)
	$finalIndScore = $objIndScore;
	else if($kpiIndScore == NULL && $objIndScore == NULL && $perspIndScore >= 0)
	$finalIndScore = $perspIndScore;
	else if ($kpiIndScore >= 0 && $objIndScore >= 0 && $perspIndScore == NULL)
	$finalIndScore = ($kpiIndScore + $objIndScore)/2;
	else if ($kpiIndScore >= 0 && $objIndScore == NULL && $perspIndScore >= 0)
	$finalIndScore = ($kpiIndScore + $perspIndScore)/2;
	else if ($kpiIndScore == NULL && $objIndScore >= 0 && $perspIndScore >= 0)
	$finalIndScore = ($objIndScore + $perspIndScore)/2;
	else if($kpiIndScore >= 0 && $objIndScore >= 0 && $perspIndScore >= 0)
	$finalIndScore = ($kpiIndScore + $objIndScore + $perspIndScore)/3;
	
	//echo "<br>Final Measure Score: ".$finalIndScore."<br>";
	//echo "".$objectId;
	$finalIndScore;//ignore this; do nothing
	//Score indidivuals essentioall on their completion of tasks; let us park the idea of using measures for now. LTK 07 March 2021 0104hrs
	$initiative_query="SELECT DISTINCT(initiative_status.initiativeId), initiative_status.percentageCompletion AS Score FROM initiative_status, initiative WHERE initiative.id = initiative_status.initiativeId AND initiative.projectManager = '$objectId' AND initiative_status.percentageCompletion IS NOT NULL";
	$initiative_result=mysqli_query($connect, $initiative_query);
	$row_count = mysqli_num_rows($initiative_result);
	$score = ""; $count = 0;
	if ($row_count == NULL) 
	{
		return NULL;
		exit;
	}
	else
	{
		while($row = mysqli_fetch_array($initiative_result))	
		{
			$score = $score + $row["Score"];
			$count++;
		}
		$score = $score/$count;
	}
	//echo "<br>Score = ".$score;
	$score = traditionalScoring("threeColor", $score, 50, 90, 100, 100);//traditionalScoring($gaugeType, $actual, $red, $green, $darkGreen, $blue)
	//echo "<br>".$score;
	return $score;
}
//individualScore("ind2", "2021-03-07", 'months');
function calculatedKpi($kpiPost)
{
	global $connect;
	$firstMeasure = mysqli_query($connect, "SELECT LENGTH(LEFT(green,LOCATE('-',green) - 1)) AS length, LEFT(green,LOCATE('-',green) - 1) AS firstKpi FROM measure WHERE id = '$kpiPost' AND LENGTH(LEFT(green,LOCATE('-',green) - 1)) <= 7");
	$firstMeasure = mysqli_fetch_array($firstMeasure);
	$firstKpiLength = $firstMeasure['length'];
	
	//positive
	if($firstKpiLength == 0)
	{
	$firstMeasure = mysqli_query($connect, "SELECT LENGTH(LEFT(green,LOCATE('+',green) - 1)) AS length, LEFT(green,LOCATE('+',green) - 1) AS firstKpi FROM measure WHERE id = '$kpiPost' AND LENGTH(LEFT(green,LOCATE('+',green) - 1)) <= 7");
	$firstMeasure = mysqli_fetch_array($firstMeasure);
	$firstKpiLength = $firstMeasure['length'];
	}
	//division
	if($firstKpiLength == 0)
	{
	$firstMeasure = mysqli_query($connect, "SELECT LENGTH(LEFT(green,LOCATE('/',green) - 1)) AS length, LEFT(green,LOCATE('/',green) - 1) AS firstKpi FROM measure WHERE id = '$kpiPost' AND LENGTH(LEFT(green,LOCATE('/',green) - 1)) <= 7");
	$firstMeasure = mysqli_fetch_array($firstMeasure);
	$firstKpiLength = $firstMeasure['length'];
	}
	//multiplication
	if($firstKpiLength == 0)
	{
	$firstMeasure = mysqli_query($connect, "SELECT LENGTH(LEFT(green,LOCATE('*',green) - 1)) AS length, LEFT(green,LOCATE('*',green) - 1) AS firstKpi FROM measure WHERE id = '$kpiPost' AND LENGTH(LEFT(green,LOCATE('*',green) - 1)) <= 7");
	$firstMeasure = mysqli_fetch_array($firstMeasure);
	//$firstKpiLength = $firstMeasure['length'];
	}
	$firstKpi = $firstMeasure['firstKpi'];
	
	$firstValue = mysqli_query($connect, "SELECT green FROM measure WHERE id = '$firstKpi'");
	$firstValue = mysqli_fetch_array($firstValue);
	$firstValue = $firstValue['green'];
	
	$query = 'SELECT id, name, green, 
	ROUND (
			(
				LENGTH(green) - 
				LENGTH(REPLACE(REPLACE(REPLACE(REPLACE(green, "+", ""), "-", ""), "/", ""), "*", ""))
			)/(LENGTH("+"))
		) AS count
	FROM measure
	WHERE abs(green) = 0
	HAVING count >= 1';
	
	$result = mysqli_query($connect, $query);
	$row = mysqli_fetch_array($result);
	
	//echo $row['id'].' = '.$row['green'].' '.'(-70 = 3 + 80 - 90 * 10)<br><br>';
	$string = $row['green'];
	$string = str_split($string);
	$formulaSize = count($string);
	$count = $firstKpiLength; $cumm = $firstValue;
	$nextSign = $string[$count];
	$tempStore = NULL;
	while($count < $formulaSize)
	{
		if($string[$count] == '+' || $string[$count] == '-' || $string[$count] == '*' || $string[$count] == '/')
		{
			$kpiResult = mysqli_query($connect, "SELECT green FROM measure WHERE id = '$tempStore'");
			$green = mysqli_fetch_array($kpiResult);
			$green = $green['green'];
			switch($nextSign)
			{
				case "+":
				{
					//echo $cumm.' + '.$green.' (plus)<br><br>';
					$cumm = $cumm + $green;
					$nextSign = $string[$count];
					break;	
				}
				case "-":
				{
					$cumm = $cumm - $green;
					$nextSign = $string[$count];
					break;	
				}
				case "*":
				{
					$cumm = $cumm * $green;
					$nextSign = $string[$count];	
					break;	
				}
				case "/":
				{
					$cumm = $cumm / $green;
					$nextSign = $string[$count];
					break;	
				}	
			}
			$tempStore = NULL;
		}
		else
		{
			$tempStore = $tempStore.$string[$count];	
		}
		$count++;	
	}
	//echo $kpi;
	$lastMeasure = mysqli_query($connect, "select SUBSTRING_INDEX(green,'*',-1) AS lastKpi from measure WHERE id = '$kpiPost'");
	$lastMeasure = mysqli_fetch_array($lastMeasure);
	$lastKpi = $lastMeasure['lastKpi'];
	
	$lastValue = mysqli_query($connect, "SELECT green FROM measure WHERE id = '$lastKpi'");
	$lastValue = mysqli_fetch_array($lastValue);
	$lastValue = $lastValue['green'];
	switch($nextSign)
	{
		case "+":
		{
			return $cumm = $cumm + $lastValue;
			break;	
		}
		case "-":
		{
			return $cumm = $cumm - $lastValue;
			break;	
		}
		case "*":
		{
			return $cumm = $cumm * $lastValue;	
			break;	
		}
		case "/":
		{
			return $cumm = $cumm / $lastValue;
			break;	
		}	
	}
}
?>