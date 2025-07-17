<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");
include_once("../functions/calendar-labels.php");
include_once("../functions/perspOrg-scores.php");
//if(isset($_POST['objectId']))
//{
	
	@$objectId = $_POST['objectId'];
	@$objectType = $_POST['objectType'];
	@$objectPeriod = $_POST['objectPeriod'];
	@$objectDate = $_POST['objectDate'];
	@$valuesCount = $_POST['valuesCount'];
	@$mainMenuState = $_POST['mainMenuState'];
	//$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
	//$objectDate = strtotime($objectDate);
	//$objectDate = date("Y-m-d", strtotime("+1 month", $objectDate));
	
	//$objectId = "org1";
	//$objectType = "organization";
	//$objectPeriod = "months";
	//$objectDate = "2015-07";
	
	$objectId = orgChildIds($objectId, $mainMenuState);
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
		case "halfYears":
		{
			//$objectDate = strtotime ( '-1 years' , strtotime ( $objectDate.'-01-01' ) ) ;
			//$objectDate = date ( 'Y-m-d' , $objectDate );
			halfYearsAsIs($objectId, $objectDate, $valuesCount);
			break;
		}
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "years":
		{
			//$objectDate = strtotime ( '+1 years' , strtotime ( $objectDate.'-01-01' ) ) ;
			//$objectDate = date ( 'Y-m-d' , $objectDate );
			yearsAsIs($objectId, $objectDate, $valuesCount);
			break;
		}
	}
flush();
//}
exit;
?>