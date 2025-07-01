<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");
//if(isset($_POST['objectId']))
//{
	
	@$perspective_id = $_POST['objectId'];
	@$objectPeriod = $_POST['objectPeriod'];
	@$objectDate = $_POST['objectDate'];
	
	$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
	$objectDate = strtotime($objectDate);
	$objectDate = date("Y-m-d", strtotime("+1 month", $objectDate));
	
	//$perspective_id = "persp5";
	//$objectPeriod = "months";
	//$objectDate = "2015-08";
//$score = getPerspGauge($perspective_id, $objectPeriod, $objectDate);
$score = perspective_score($perspective_id, $objectDate, 'measuremonths');
echo $score;
	
	flush();
//}
exit;
?>