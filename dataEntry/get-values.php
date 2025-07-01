<?php
error_reporting(0); //To show all - change 0 to E_ALL
ini_set('display_errors', 0); //To show all - change 0 to 1

include_once("../config/config_mysqli.php");

@$objectId = $_POST['objectId'];
@$objectPeriod = $_POST['objectPeriod'];
@$objectDate = $_POST['objectDate'];

$table = "measuremonths";

$get_gauge = "SELECT id, name, description, gaugeType, calendarType, measureType 
FROM measure 
WHERE linkedObject = '$objectId' AND measureType = 'Core Value'
OR updater = '$objectId' AND measureType = 'Core Value'
OR owner = '$objectId' AND measureType = 'Core Value'
ORDER BY name ASC";
$get_gauge_result = mysqli_query($connect, $get_gauge);
//if(mysqli_num_rows(get_gauge_result) == 0) 
//{
//	echo "No measures";
//}
echo "<div class='border border-primary rounded-3' style='overflow:hidden;'><table class='table table-hover table-responsive table-bordered table-sm table-condensed table-striped'>";
	echo "<tr class='table-primary'>";
		echo "<th>Measure</th>";
		echo "<th>Description</th>";
		
		echo "<th>Actual</th>";
		echo "<th>Target</th>";
		
		echo "<th>Frequency</th>";
		echo "<th>Last Update</th>";
		echo "<th></th>";
	echo "</tr>";
echo "<tbody>";
while($row = mysqli_fetch_assoc($get_gauge_result))
{
	$calendarType = $row['calendarType'];
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
	$kpiId = $row['id'];
	echo "<tr>";
	echo "<td>".$row["name"]."</td>";
	$indMeasure_query = "SELECT date, actual, green, 3score FROM $table WHERE measureId = '$kpiId' AND date <= '$objectDate%' ORDER BY date DESC LIMIT 1";
	//$indMeasure_query = "SELECT AVG(3score) FROM $table WHERE measureId = '$kpiId' AND date LIKE '$objectDate%'";
	$indMeasure_result = mysqli_query($connect, $indMeasure_query);
	$indMeasure_count = mysqli_num_rows($indMeasure_result);
	$indKpiRow = mysqli_fetch_assoc($indMeasure_result);
	
	//"Measure Type".$row["measureType"];
	$measureId = preg_replace('/^\D+/', '', $row["id"]);
	
	$getUpdater = mysqli_query($connect, "SELECT 
					 u.display_name as 'updater', 
					 u1.display_name as 'owner'
				FROM `measure` m
				JOIN `uc_users` u on u.user_id = m.updater
				JOIN `uc_users` u1 on u1.user_id = m.owner
				WHERE m.id = '$kpiId'");
	$updaterRow = mysqli_fetch_assoc($getUpdater);
	
	//echo "<td>".$updaterRow["owner"]."</td>";
	echo "<td>".$row["description"]."</td>";
	
	if($indKpiRow["3score"] == NULL || $indKpiRow["3score"] == '') echo "<td>"."No Score"."</td>"; 
	else 
	{
		if($indKpiRow["3score"] < 3.3) echo "<td class='table-danger'>".round($indKpiRow["actual"],2)."</td>";//red
		else if($indKpiRow["3score"] >= 3.3 && $indKpiRow["3score"] < 6.6) echo "<td class='table-warning'>".round($indKpiRow["actual"],2)."</td>";//yellow
		else if($indKpiRow["3score"] >= 6.6) echo "<td class='table-success'>".round($indKpiRow["actual"],2)."</td>";//green
		else echo "<td class='table-secondary'>".round($indKpiRow["actual"],2)."</td>";//grey
	}
	//echo "<td>".$indKpiRow["actual"]."</td>";
	echo "<td>".$indKpiRow["green"]."</td>";
	echo "<td>".$calendarType."</td>";
	if($indKpiRow["date"] < "2000-01-01") echo "<td>".$indKpiRow["date"]."</td>";
	else
	{
	switch($table)
	{
		case "measuremonths":
		{
			echo "<td>".date("F, Y",strtotime($indKpiRow["date"]))."</td>";
			break;
		}
		case "measurequarters":
		{
			$month = date("m",strtotime($indKpiRow["date"]))."</td>";
			if($month > 0 && $month < 4)
			echo "<td>"."Q1 ".date("Y",strtotime($indKpiRow["date"]))."</td>";
			else if ($month > 3 && $month < 7)
			echo "<td>"."Q2 ".date("Y",strtotime($indKpiRow["date"]))."</td>";
			else if ($month > 6 && $month < 10)
			echo "<td>"."Q3 ".date("Y",strtotime($indKpiRow["date"]))."</td>";
			else
			echo "<td>"."Q4 ".date("Y",strtotime($indKpiRow["date"]))."</td>";
			break;
		}
		case "measurehalfyear":
		{
			$month = date("m",strtotime($indKpiRow["date"]));
			if($month > 0 && $month < 7)
			echo "<td>"."HY1 ".date("Y",strtotime($indKpiRow["date"]))."</td>";
			else 
			echo "<td>"."HY2 ".date("Y",strtotime($indKpiRow["date"]))."</td>";
			break;
		}
		case "measureyears":
		{
			echo "<td>".date("Y",strtotime($indKpiRow["date"]))."</td>";
			break;
		}
		default:
		{
			echo "<td>".$indKpiRow["date"]."</td>";
			break;	
		}
	}
	}
	echo "<td><a href='#' onClick='myBulkEntry(".$measureId.")'>Update</a></td>";
	echo "</tr>";
}
echo "</tbody>";
echo "</table></div>";
?>