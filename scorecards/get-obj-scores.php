<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");
include_once("../functions/calendar-labels.php");
include_once("../functions/obj-scores.php");
//if(isset($_POST['objectId']))
//{
	
	// Validate and sanitize POST parameters
	$objectId = isset($_POST['objectId']) ? $_POST['objectId'] : '';
	$objectType = isset($_POST['objectType']) ? $_POST['objectType'] : '';
	$objectPeriod = isset($_POST['objectPeriod']) ? $_POST['objectPeriod'] : '';
	$objectDate = isset($_POST['objectDate']) ? $_POST['objectDate'] : '';

	// Exit early if required parameters are missing
	if (empty($objectId) || empty($objectPeriod)) {
		echo json_encode(['error' => 'Missing required parameters']);
		exit;
	}
	
	$valuesCount = isset($_POST['valuesCount']) ? (int)$_POST['valuesCount'] : 1;
	$getCalendar = mysqli_query($connect, "SELECT calendarType FROM measure WHERE id = '$objectId'");
	if (!$getCalendar) {
		echo json_encode(['error' => 'Database query failed: ' . mysqli_error($connect)]);
		exit;
	}
	$getCalendar_result = mysqli_fetch_assoc($getCalendar);
	$getCalendar = $getCalendar_result ? $getCalendar_result["calendarType"] : null;
	if($getCalendar == "monthly") $table = "measuremonths";
	else $table = "measure".$objectPeriod;

	$defaults_results = mysqli_query($connect, "SELECT red, green, darkgreen, blue, gaugeType FROM measure WHERE id = '$objectId'");
	if (!$defaults_results) {
		echo json_encode(['error' => 'Database query failed: ' . mysqli_error($connect)]);
		exit;
	}
	$defaults_row = mysqli_fetch_array($defaults_results);

	// Check if query returned results before accessing array offsets
	if ($defaults_row) {
		$red = $defaults_row["red"];
		$green = $defaults_row["green"];
		$darkgreen = $defaults_row["darkgreen"];
		$blue = $defaults_row["blue"];
		$gaugeType = $defaults_row["gaugeType"];
	} else {
		// Set default values if no measure found
		$red = 0;
		$green = 100;
		$darkgreen = 150;
		$blue = 200;
		$gaugeType = "threeColor";
	}
	switch($objectPeriod)
	{
		case "days":
		{
			daysAsIs($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType);
			break;
		}
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "weeks":
		{
			//daysInWeeks($objectId, $objectDate, $valuesCount,  $red, $green, $darkgreen, $blue, $gaugeType);
			weeksAsIs($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType);
			break;
		}
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "months":
		{
			monthsAsIs($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType);
			break;
		}
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "quarters":
		{
			quartersAsIs($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType);
			break;
		}	
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "halfYears":
		{
			//$objectDate = strtotime ( '-1 years' , strtotime ( $objectDate.'-01-01' ) ) ;
			//$objectDate = date ( 'Y-m-d' , $objectDate );
			halfYearsAsIs($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType);
			break;
		}
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "years":
		{
			//$objectDate = strtotime ( '+1 years' , strtotime ( $objectDate.'-01-01' ) ) ;
			//$objectDate = date ( 'Y-m-d' , $objectDate );
			yearsAsIs($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType);
			break;
		}
	}
flush();
//}
exit;
?>