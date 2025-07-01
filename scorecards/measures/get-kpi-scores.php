<?php
include_once("../../config/config_mysqli.php");
include_once("../../functions/functions.php");
include_once("../../functions/calendar-labels.php");
include_once("../../functions/calendar-data.php");
//if(isset($_POST['objectId']))
//{

@$objectId = $_POST['objectId'];
@$objectType = $_POST['objectType'];
@$objectPeriod = $_POST['objectPeriod'];//remember here that while all the rest are plural, halfyear is not.
@$objectDate = $_POST['objectDate'];
@$valuesCount = $_POST['valuesCount'];
if(!empty($_POST['previousPeriod']) && $_POST['previousPeriod'] == "True")
{
	$objectDate = strtotime($objectDate);
	$objectDate = date("Y-m-d", strtotime("-1 year", $objectDate));
}

/*$objectId = "kpi17";
$objectType = "measure";
$objectPeriod = "months";
$objectDate = "2015-09";
$valuesCount = 12;*/

$getCalendar = mysqli_query($connect, "SELECT calendarType FROM measure WHERE id = '$objectId'");
$getCalendar = mysqli_fetch_assoc($getCalendar);
$getCalendar = $getCalendar["calendarType"];

switch($getCalendar)
{
	case 'Daily':
	{
		$objectDate = date("Y-m-d",strtotime($objectDate));
		$table = 'measuredays';
		if($objectPeriod == 'months')
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m-d", strtotime("+1 month", $objectDate));	
		}
		else if($objectPeriod == 'quarters')
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m-d", strtotime("+3 month", $objectDate));	
		}
		else if($objectPeriod == 'years')
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m-d", strtotime("+2 year", $objectDate));	
		}
		// || $objectPeriod == 'quarters' || $objectPeriod == 'halfyear' || $objectPeriod == 'years'
		break;	
	}
	case 'Weekly':
	{
		$table = 'measureweeks';
		$objectDate = date("Y-m-d",strtotime($objectDate));
		break;	
	}
	case 'Monthly':
	{
		$table = 'measuremonths';
		$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
		break;	
	}
	case 'Quarterly':
	{
		//$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
		$table = 'measurequarters';
		break;	
	}
	case 'Bi-Annually':
	{
		$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
		$table = 'measurehalfyear';
		break;	
	}
	case 'Yearly':
	{
		$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
		$table = 'measureyears';
		break;	
	}
}

// First get the maximum actual value
$max_actual_result = mysqli_query($connect, "SELECT MAX(actual) as max_actual FROM $table
WHERE measureId = '$objectId' AND date <= '$objectDate%'");
$max_actual_row = mysqli_fetch_array($max_actual_result);
$upperLimit1 = ($max_actual_row && isset($max_actual_row["max_actual"])) ? $max_actual_row["max_actual"] : null;

// Then get the blue value from the row with the maximum actual value
if ($upperLimit1 !== null) {
	$blue_result = mysqli_query($connect, "SELECT blue FROM $table
	WHERE measureId = '$objectId' AND date <= '$objectDate%' AND actual = '$upperLimit1'
	ORDER BY date DESC LIMIT 1");
	$blue_row = mysqli_fetch_array($blue_result);
	$upperLimit2 = ($blue_row && isset($blue_row["blue"])) ? $blue_row["blue"] : null;
} else {
	$upperLimit2 = null;
}

if($objectType == "fiveColor")
{
	if($upperLimit1 > $upperLimit2)	$upperLimit = $upperLimit1;
	else $upperLimit = $upperLimit2 * 1.10;
}
else
{
	$upperLimit = NULL;
}

$defaults_results = mysqli_query($connect, "SELECT red, green, darkgreen, blue, gaugeType, dataType, aggregationType FROM measure WHERE id = '$objectId'");
$defaults_row = mysqli_fetch_array($defaults_results);
$red = $defaults_row["red"];
$green = $defaults_row["green"];
if(preg_match('/[a-zA-Z]/', $green) == 1) $green = calculatedKpi($objectId);
$darkgreen = $defaults_row["darkgreen"];
$blue = $defaults_row["blue"];
$gaugeType = $defaults_row["gaugeType"];
$dataType = $defaults_row["dataType"];
$aggregationType = $defaults_row["aggregationType"];

switch($objectPeriod)
{
	case "days":
	{
		//$objectDate = date("Y-m-d",strtotime($objectDate));
		if($getCalendar == "Daily")
		{
			daysAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType);
		}
		else if($getCalendar == "Weekly")
		{ 
			weeksAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}
		else if($getCalendar == "Monthly")
		{
			monthsAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}
		else if($getCalendar == "Quarterly")
		{
			quartersAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}
		else if($getCalendar == "Bi-Annually")
		{
			halfYearsAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}					
		else if($getCalendar == "Yearly")
		{
			yearsAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}
		break;
	}
	/*******************************************************************************************************************
	$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
	********************************************************************************************************************/
	case "weeks":
	{
		//$objectDate = date("Y-m-d",strtotime($objectDate));
		if($getCalendar == "Daily")
		{
			daysInWeeks($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}
		else if($getCalendar == "Weekly")
		{ 
			weeksAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}
		else if($getCalendar == "Monthly")
		{
			monthsAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}
		else if($getCalendar == "Quarterly")
		{
			quartersAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}
		else if($getCalendar == "Bi-Annually")
		{
			halfYearsAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}					
		else if($getCalendar == "Yearly")
		{
			yearsAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}
		break;
	}
	/*******************************************************************************************************************
	$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
	********************************************************************************************************************/
	case "months":
	{
		if($getCalendar == "Daily")
		{
			inMonths($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, "measuredays", $dataType, $aggregationType);
		}
		else if($getCalendar == "Weekly")
		{ 
			inMonths($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, "measureweeks", $dataType, $aggregationType);
		}
		else if($getCalendar == "Monthly")
		{
			monthsAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}
		else if($getCalendar == "Quarterly")
		{
			quartersAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}
		else if($getCalendar == "Bi-Annually")
		{
			halfYearsAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}					
		else if($getCalendar == "Yearly")
		{
			yearsAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}
		break;
	}
	/*******************************************************************************************************************
	$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
	********************************************************************************************************************/
	case "quarters":
	{
		if($getCalendar == "Daily")
		{
			inQuarters($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, "measuredays", $dataType, $aggregationType);
		}
		else if($getCalendar == "Weekly")
		{ 
			inQuarters($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, "measureweeks", $dataType, $aggregationType);
		}			
		else if($getCalendar == "Monthly")
		{ 
			inQuarters($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, "measuremonths", $dataType, $aggregationType);
		}
		else if($getCalendar == "Quarterly")
		{
			quartersAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}
		else if($getCalendar == "Bi-Annually")
		{
			halfYearsAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}					
		else if($getCalendar == "Yearly")
		{
			yearsAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}
		break;
	}	
	/*******************************************************************************************************************
	$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
	********************************************************************************************************************/
	case "halfYears":
	{
		if($getCalendar == "Daily")
		{
			inHalfs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, "measuredays", $dataType, $aggregationType);
		}
		else if($getCalendar == "Weekly")
		{ 
			inHalfs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, "measureweeks", $dataType, $aggregationType);
		}
		else if($getCalendar == "Monthly")
		{ 
			inHalfs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, "measuremonths", $dataType, $aggregationType);
		}
		else if($getCalendar == "Quarterly")
		{
			inHalfs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, "measurequarters", $dataType, $aggregationType);
		}
		else if($getCalendar == "Bi-Annually")
		{
			halfYearsAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}					
		else if($getCalendar == "Yearly")
		{
			yearsAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}
		break;
	}
	/*******************************************************************************************************************
	$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
	********************************************************************************************************************/
	case "years":
	{
		if($getCalendar == "Daily")
		{
			inYears($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, "measuredays", $dataType, $aggregationType);
		}
		else if($getCalendar == "Weekly")
		{
			inYears($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, "measureweeks", $dataType, $aggregationType);
		}
		else if($getCalendar == "Monthly")
		{
			inYears($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, "measuremonths", $dataType, $aggregationType);
		}
		else if($getCalendar == "Quarterly")
		{
			inYears($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, "measurequarters", $dataType, $aggregationType);
		}
		else if($getCalendar == "Bi-Annually")
		{
			inYears($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, "measurehalfyear", $dataType, $aggregationType);
		}					
		else if($getCalendar == "Yearly")
		{
			yearsAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType);
		}
		break;
	}
}
flush();
//}
exit;
?>