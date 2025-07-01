<?php
include_once("../config/config_mysqli.php");
//if(isset($_POST['objectId']))
//{
	
	@$objectId = $_POST['objectId'];
	@$objectPeriod = $_POST['objectPeriod'];
	@$objectDate = $_POST['objectDate'];
	//$objectDate = strtotime($objectDate);
	//$objectDate = date("Y-m-d", strtotime("-1 month", $objectDate));// get the score of the previous month
	
	//$objectId = "kpi15";
	//$objectType = "measure";
	//$objectDate = "2015-07";
	//$objectPeriod = "months";
	
	$get_gauge = "SELECT gaugeType, calendarType, name FROM measure WHERE id = '$objectId'";
	$get_gauge_result=mysqli_query($connect, $get_gauge);
	$gauge = mysqli_fetch_assoc($get_gauge_result);
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
	if($measureCount == 0) 
	{
		$score["score"] = "No Score";
		$score["gaugeType"] = $gauge["gaugeType"];
		$score["lastDate"] = "No Score";
		//$score["kpiName"] = $kpiName;
		$final = "{\"score\":\"".$score["score"]."\",\"gaugeType\":\"".$score["gaugeType"]."\",\"kpiName\":\"".$kpiName."\"}";
	}
	else
	{
		$score["gaugeType"] = $gauge["gaugeType"];
		$score["score"] = $measure_score["3score"];
		$score["lastDate"] = $measure_score["lastDate"];
		$score["kpiName"] = $kpiName;
		$final = "{\"score\":\"".round($score["score"],2)."\",\"gaugeType\":\"".$score["gaugeType"]."\",\"kpiName\":\"".$kpiName."\"".",\"lastDate\":\"".$score["lastDate"]."\"}";
		if($score["score"] == NULL || $score["score"] == '') 
		{
			$score["score"] = "No Score";
			$final = "{\"score\":\"".$score["score"]."\",\"gaugeType\":\"".$score["gaugeType"]."\",\"kpiName\":\"".$kpiName.",\"lastDate\":\"".$score["lastDate"]."\"}";
		}
	}
	echo $final;
	//file_put_contents("kpiGauge.txt", "Score: $final, $objectId, $calendarType, $measureCount");
	flush();
?>