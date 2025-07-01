<?php
include_once("config_mysqli.php");

@$kpiId = $_POST['kpiId'];

$get_kpi = "SELECT name, calendarType, gaugeType, red, blue, green, darkGreen FROM measure WHERE id = '$kpiId'";
$get_kpi_result=mysqli_query($connect, $get_kpi);
$kpi = mysqli_fetch_assoc($get_kpi_result);

$calendarType = $kpi['calendarType'];

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
		break;	
	}
}

$getId = mysqli_query($connect, "SELECT MAX(id) AS id FROM $table");
$getId = mysqli_fetch_assoc($getId);

$data['id'] = $getId['id'];
$data['name'] = $kpi['name'];
$data['gaugeType'] = $kpi['gaugeType'];
$data['calendarType'] = $kpi['calendarType'];
$data['baseline'] = $kpi['red'];
$data['best'] = $kpi['blue'];
$data['target'] = $kpi['green'];
$data['stretch'] = $kpi['darkGreen'];
$data = json_encode($data);

echo $data;

flush();
?>