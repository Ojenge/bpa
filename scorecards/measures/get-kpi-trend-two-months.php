<?php
include_once("../../config/config_mysqli.php");
include_once("../../functions/functions.php");

@$orgId = $_POST['orgId'];
$orgId = 'org1';

$count = 0; $red = 0; $yellow = 0; $green = 0;
$date_query="SELECT DATE_FORMAT(date, '%Y-%m') AS newDate FROM measuredays
UNION SELECT DATE_FORMAT(date, '%Y-%m') AS newDate FROM measureweeks
UNION SELECT DATE_FORMAT(date, '%Y-%m') AS newDate FROM measuremonths
UNION SELECT DATE_FORMAT(date, '%Y-%m') AS newDate FROM measurequarters
UNION SELECT DATE_FORMAT(date, '%Y-%m') AS newDate FROM measurehalfyear
UNION SELECT DATE_FORMAT(date, '%Y-%m') AS newDate FROM measureyears
ORDER BY newDate DESC LIMIT 2";
$date_result=mysqli_query($connect, $date_query);
$count = 0;
$rowCount = mysqli_num_rows($date_result);
while($row = mysqli_fetch_assoc($date_result))
{
	$trendDate = $row["newDate"];
	
	$day_query=mysqli_query($connect, "SELECT
	SUM(IF(3score <= '3.33', 1,0)) AS `red`,
	SUM(IF(3score > '3.33' && 3score <= '6.67', 1,0)) AS `yellow`,
	SUM(IF(3score > '6.67', 1,0)) AS `green`,
	COUNT(3score) AS `total`
	FROM measuredays
	WHERE date LIKE '$trendDate%'");
	$day_result = mysqli_fetch_array($day_query);
	
	$week_query=mysqli_query($connect, "SELECT
	SUM(IF(3score <= '3.33', 1,0)) AS `red`,
	SUM(IF(3score > '3.33' && 3score <= '6.67', 1,0)) AS `yellow`,
	SUM(IF(3score > '6.67', 1,0)) AS `green`,
	COUNT(3score) AS `total`
	FROM measureweeks
	WHERE date LIKE '$trendDate%'");
	$week_result = mysqli_fetch_array($week_query);
	
	$month_query=mysqli_query($connect, "SELECT
	SUM(IF(3score <= '3.33', 1,0)) AS `red`,
	SUM(IF(3score > '3.33' && 3score <= '6.67', 1,0)) AS `yellow`,
	SUM(IF(3score > '6.67', 1,0)) AS `green`,
	COUNT(3score) AS `total`
	FROM measuremonths
	WHERE date LIKE '$trendDate%'");
	$month_result = mysqli_fetch_array($month_query);
	
	$quarter_query=mysqli_query($connect, "SELECT
	SUM(IF(3score <= '3.33', 1,0)) AS `red`,
	SUM(IF(3score > '3.33' && 3score <= '6.67', 1,0)) AS `yellow`,
	SUM(IF(3score > '6.67', 1,0)) AS `green`,
	COUNT(3score) AS `total`
	FROM measurequarters
	WHERE date LIKE '$trendDate%'");
	$quarter_result = mysqli_fetch_array($quarter_query);
	
	$half_query=mysqli_query($connect, "SELECT
	SUM(IF(3score <= '3.33', 1,0)) AS `red`,
	SUM(IF(3score > '3.33' && 3score <= '6.67', 1,0)) AS `yellow`,
	SUM(IF(3score > '6.67', 1,0)) AS `green`,
	COUNT(3score) AS `total`
	FROM measurehalfyear
	WHERE date LIKE '$trendDate%'");
	$half_result = mysqli_fetch_array($half_query);
	
	$year_query=mysqli_query($connect, "SELECT
	SUM(IF(3score <= '3.33', 1,0)) AS `red`,
	SUM(IF(3score > '3.33' && 3score <= '6.67', 1,0)) AS `yellow`,
	SUM(IF(3score > '6.67', 1,0)) AS `green`,
	COUNT(3score) AS `total`
	FROM measureyears
	WHERE date LIKE '$trendDate%'");
	$year_result = mysqli_fetch_array($year_query);
	
	$trendArray[$count]["green"] = $day_result["green"] + $week_result["green"] + $month_result["green"] + $quarter_result["green"] + $half_result["green"] + $year_result["green"];
	$trendArray[$count]["yellow"] = $day_result["yellow"] + $week_result["yellow"] + $month_result["yellow"] + $quarter_result["yellow"] + $half_result["yellow"] + $year_result["yellow"];
	$trendArray[$count]["red"] = $day_result["red"] + $week_result["red"] + $month_result["red"] + $quarter_result["red"] + $half_result["red"] + $year_result["red"];
	$trendArray[$count]["date"] = $trendDate;
	$count++;
}
$kpi_difference = (($trendArray[0]["green"] - $trendArray[1]["green"])/$trendArray[1]["green"])*100;

if($kpi_difference > 0)
{
	$kpiColor = '#093';
	$kpi_difference = $kpi_difference."% improvement from previous period";
}
else
{
	$kpiColor = 'red';
	$kpi_difference = $kpi_difference."% drop from previous period";
}

$kpi_green = $trendArray[0]["green"];
$kpi_red = $trendArray[0]["red"];

$kpi_total = mysqli_query($connect, "SELECT (SELECT COUNT(id) FROM measuredays) as one,
(SELECT COUNT(id) FROM measureweeks) as two,
(SELECT COUNT(id) FROM measuremonths) as three,
(SELECT COUNT(id) FROM measurequarters) as four,
(SELECT COUNT(id) FROM measurehalfyear) as five,
(SELECT COUNT(id) FROM measureyears) as six");

$kpi_total = mysqli_fetch_array($kpi_total);
$kpi_total = $kpi_total["one"] + $kpi_total["two"] + $kpi_total["three"] + $kpi_total["four"] + $kpi_total["five"] + $kpi_total["six"];

//$newArray = array_reverse($trendArray);
//echo json_encode($trendArray);
//echo "]";
?>