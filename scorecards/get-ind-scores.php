<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");
include_once("../functions/calendar-labels.php");
include_once("../functions/perspOrg-scores.php");
//if(isset($_POST['objectId']))
//{
	
	$objectId = $_POST['objectId'];
	$objectType = $_POST['objectType'];
	$objectPeriod = $_POST['objectPeriod'];
	$objectDate = $_POST['objectDate'];
	$valuesCount = $_POST['valuesCount'];
	$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
	$objectDate = strtotime($objectDate);
	$objectDate = date("Y-m-d", strtotime("+1 month", $objectDate));
	/*
	$objectId = "org1";
	$objectType = "organization";
	$objectPeriod = "months";
	$objectDate = "2014-07-01";
	$valuesCount = 12;
	*/
	
	$objectId = indChildIds($objectId);
	
	switch($objectPeriod)
	{
		case "days":
		{
			daysAsIs($objectId, $objectDate, $valuesCount);
			break;
		}
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "weeks":
		{
			//daysInWeeks($objectId, $objectDate, $valuesCount,  $red, $green, $darkgreen, $blue, $gaugeType);
			weeksAsIs($objectId, $objectDate, $valuesCount);
			break;
		}
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "months":
		{
			monthsAsIs($objectId, $objectDate, $valuesCount);
			break;
		}
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "quarters":
		{
			quartersAsIs($objectId, $objectDate, $valuesCount);
			break;
		}	
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "halfyear":
		{
			halfYearsAsIs($objectId, $objectDate, $valuesCount);
			break;
		}
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "years":
		{
			yearsAsIs($objectId, $objectDate, $valuesCount);
			break;
		}
	}
flush();
//}
exit;
?>