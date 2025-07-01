<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");
//if(isset($_POST['objectId']))
//{
	
	@$objectId = $_POST['objectId'];
	@$objectPeriod = $_POST['objectPeriod'];
	@$objectDate = $_POST['objectDate'];
	
	$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
	$objectDate = strtotime($objectDate);
	$objectDate = date("Y-m-d", strtotime("+1 month", $objectDate));
	
	//echo $test = objective_score('obj44', "2015-07", 'table');
	echo $score = objective_score($objectId, $objectDate, 'table');
	//file_put_contents("score.txt", "\n$score", FILE_APPEND);
	
	flush();
//}
exit;
?>