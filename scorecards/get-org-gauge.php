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
	@$valuesCount = 1;
	
	//$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
	//$objectDate = strtotime($objectDate);
	//$objectDate = date("Y-m-d", strtotime("+1 month", $objectDate));
	
	//$objectId = "org1";
	//$objectType = "organization";
	//$objectPeriod = "months";
	//$objectDate = "2016-11";
	
	$objectId = orgChildIds($objectId);
	switch($objectPeriod)
	{
		case "days":
		{
			echo daysAsIs($objectId, $objectDate, $valuesCount);
			break;
		}
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "weeks":
		{
			//daysInWeeks($objectId, $objectDate, $valuesCount,  $red, $green, $darkgreen, $blue, $gaugeType);
			echo weeksAsIs($objectId, $objectDate, $valuesCount);
			break;
		}
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "months":
		{
			echo monthsAsIs($objectId, $objectDate, $valuesCount);
			break;
		}
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "quarters":
		{
			echo quartersAsIs($objectId, $objectDate, $valuesCount);
			break;
		}	
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "halfYears":
		{
			//$objectDate = strtotime ( '-1 years' , strtotime ( $objectDate.'-01-01' ) ) ;
			//$objectDate = date ( 'Y-m-d' , $objectDate );
			echo halfYearsAsIs($objectId, $objectDate, $valuesCount);
			break;
		}
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "years":
		{
			//$objectDate = strtotime ( '+1 years' , strtotime ( $objectDate.'-01-01' ) ) ;
			//$objectDate = date ( 'Y-m-d' , $objectDate );
			echo yearsAsIs($objectId, $objectDate, $valuesCount);
			break;
		}
	}
flush();
//}
exit;
?>