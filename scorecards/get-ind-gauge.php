<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");
include_once("../functions/calendar-labels.php");
include_once("../functions/perspOrg-scores.php");

@$objectId = $_POST['objectId'];
@$objectPeriod = $_POST['objectPeriod'];
@$objectDate = $_POST['objectDate'];
@$objectDate = date("Y-m-d",strtotime($objectDate."-01"));

//$objectId = 'ind46';
//$objectDate = '2016-11';
//$objectPeriod = 'months';
echo $score = individualScore($objectId, $objectDate, $objectPeriod);
//file_put_contents("aIndGauge.txt", "$objectId, $objectDate, $objectPeriod");

flush();
?>