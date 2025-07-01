<?php
/*********************************************************************************************************************
BSC Traditional Scoring:
**********************************************************************************************************************/
function traditionalScoring($gaugeType, $actual, $red, $green, $darkGreen, $blue)
{
	global $goalScore, $threeScore, $fourScore, $fiveScore;
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
			$newGreen = $red + ($green - $red)/2; //Introduces a green that allows us to have 10 as 100% not 6.67 which was difficult to explain to clients and quite punitive at the same time. LTK 29Aug2022 15.38Hrs

			$threeScore = ((abs($actual) - abs($red))/(abs($newGreen) - abs($red)) * ((1/3)+3)) + ((1/3)+3);
			if($threeScore > 10) $threeScore = 10;
			if($threeScore < 0) $threeScore = 0;
			if($actual < $red && $actual < $green && $red < $green) $threeScore = 0; //taking care of negative actual values
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

function getMySQLTable($calendarType)
{
	$table = "";
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
	return $table;
}

function saveSingleMeasureValue($id, $date, $actual, $updater, $target = NULL)
{
	global $connect, $goalScore, $threeScore, $fourScore, $fiveScore;
	
	$kpiDetails = mysqli_query($connect, "SELECT calendarType, red, blue, green, darkGreen, gaugeType FROM measure WHERE id = '$id'");
	if (!$kpiDetails) {
		error_log("Database query failed in saveSingleMeasureValue: " . mysqli_error($connect));
		return false;
	}
	$kpiDetails_result = mysqli_fetch_assoc($kpiDetails);

	// Check if query returned results before accessing array offsets
	if ($kpiDetails_result) {
		$blue = $kpiDetails_result["blue"];
		if($target == NULL) //User or Import System did not provide/have budgets/targets/etc. LTK 29Aug2022 1527hrs
		{
			$green = $kpiDetails_result["green"];
			$red = $kpiDetails_result["red"];
		}
		else
		{
			$green = $target;
			if($kpiDetails_result["green"] > $kpiDetails_result["red"]) $red = $green * 0.5; //work with 50% as default baseline so as not to be punitive
			else $red = $green * 1.3; //lower values better e.g. expenses.
		}
		$darkGreen = $kpiDetails_result["darkGreen"];
		$gaugeType = $kpiDetails_result["gaugeType"];
		$calendarType = $kpiDetails_result["calendarType"];
	} else {
		// Set default values if no measure found
		error_log("No measure found with id: $id in saveSingleMeasureValue");
		return false;
	}
	
	$table = getMySQLTable($calendarType);
	
	//Check whether value for given date already exists
	$checkKPI = mysqli_query($connect, "SELECT id FROM `$table` WHERE measureID = '$id' AND date = '$date'");
	if(mysqli_num_rows($checkKPI) > 0)
	{
		mysqli_query($connect, "DELETE FROM `$table` WHERE measureId = '$id' AND date = '$date'");	
	}
	
	traditionalScoring($gaugeType, $actual, $red, $green, $darkGreen, $blue); //returns global $goalScore, $threeScore, $fourScore, $fiveScore;
	
	mysqli_query($connect, "INSERT INTO `$table` 
	(`id`, `measureId`, `date`, `actual`, `red`, `blue`, `green`, `darkgreen`, `2score`, `3score`, `4score`, `5score`, `updater`) 
	VALUES (DEFAULT, '$id', '$date', '".$actual."', '".$red."', '".$blue."', '".$green."', '".$darkGreen."', '".$goalScore."', '".$threeScore."', '".$fourScore."', '".$fiveScore."', '".$updater."')") or file_put_contents("kpiSaveError.txt","\r\n Did not save Updater = $updater; Date = ".$date." & id ->".$id." actual=>".$actual. "; red=>" .$red. "; green=>" .$green. "; darkGreen=>" .$darkGreen. "; blue=>" .$blue." & Scores: goalScore=>$goalScore, threeScore=>$threeScore, fourScore=>$fourScore, fiveScore=>$fiveScore in table $table ".mysqli_error($connect), FILE_APPEND);
	
	//Check if measure is linked to others and save for these as well
	$linkedKPI = mysqli_query($connect, "SELECT linkedId FROM import_links WHERE measureId = '$id'");
	if(mysqli_num_rows($linkedKPI) > 0)
	{
		while($row = mysqli_fetch_array($linkedKPI))
		{
			$linkedId = $row["linkedId"];
			mysqli_query($connect, "INSERT INTO `$table` 
	(`id`, `measureId`, `date`, `actual`, `red`, `blue`, `green`, `darkgreen`, `2score`, `3score`, `4score`, `5score`, `updater`) 
	VALUES (DEFAULT, '$linkedId', '$date', '".$actual."', '".$red."', '".$blue."', '".$green."', '".$darkGreen."', '".$goalScore."', '".$threeScore."', '".$fourScore."', '".$fiveScore."', '".$updater."')") or file_put_contents("kpiLinkedSaveError.txt","\r\n Did not save Updater = $updater; Date = ".$date." & id ->".$linkedId." actual=>".$actual. "; red=>" .$red. "; green=>" .$green. "; darkGreen=>" .$darkGreen. "; blue=>" .$blue." & Scores: goalScore=>$goalScore, threeScore=>$threeScore, fourScore=>$fourScore, fiveScore=>$fiveScore in table $table ".mysqli_error($connect), FILE_APPEND);
		}
	}
}
?>