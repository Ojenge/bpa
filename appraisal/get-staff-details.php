<?php
include_once("../config/config_mysqli.php");

//$id = $_POST['id'];
$id = 'ind62';

$return = "<table class='table-striped table-bordered table-hover'>";
$return = $return."<tr><th colspan='5'>Project Appraisal</th></tr>";
$return = $return."<tr><td class='success !important;'>Name</td><td>Due Date</td><td>Completion Date</td><td>Weight</td><td>Score</td></tr>";
$initiatives = mysqli_query($connect, "SELECT name, dueDate, completionDate, deliverable, deliverableStatus FROM initiative WHERE projectManager = '$id' ORDER BY id ASC");
while($initiativeRow = mysqli_fetch_array($initiatives))
{
	$initiativeScore = '';
	$return = $return."<tr><td>".$initiativeRow["name"]."</td><td>".$initiativeRow["dueDate"]."</td><td>".$initiativeRow["completionDate"]."</td><td></td><td>".$initiativeScore."</td></tr>";
}
//$return = $return."</table><table class='table-striped table-bordered table-hover'>";
	
	$perspectives = mysqli_query($connect, "SELECT id, name FROM perspective WHERE parentId = '$id' ORDER BY CAST(Replace(id, 'persp', '') AS UNSIGNED) ASC");
	while($rowPerspective = mysqli_fetch_array($perspectives))
	{
		//echo "<br>Id: ".$rowPerspective["id"];
		$return = $return."<tr><th colspan='5'>".$rowPerspective["name"]."</th></tr>";
		$return = $return."<tr class='blue-grey lighten-4 !important'><td>Name</td><td>Target</td><td>Actual</td><td>Weight</td><td>Score</td></tr>";
		$perspId = $rowPerspective["id"];
		$measures = mysqli_query($connect, "SELECT id, name, calendarType, green, weight FROM measure where linkedObject = '$perspId'");
		while($rowMeasure = mysqli_fetch_array($measures))
		{
			$return = $return."<tr><td>".$rowMeasure["name"]."</td><td>".$rowMeasure["green"]."</td><td>";
			$measure_id = $rowMeasure["id"];
			$measure_table = $rowMeasure["calendarType"];
			switch($measure_table)
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
					//$objectDate = date("Y", strtotime($objectDate));
					break;	
				}
			}
			$measureActual = mysqli_query($connect, "SELECT actual FROM $table WHERE measureId = '$measure_id' ORDER BY date DESC LIMIT 1");
			$measureActual = mysqli_fetch_array($measureActual);
			if($rowMeasure["green"] > 0)
			$score = 17.5*($measureActual["actual"]/$rowMeasure["green"]);
			$return = $return.$measureActual["actual"]."</td><td></td><td>".$score."</td></tr>";
		}
	}
	$return = $return."</table>";
	echo $return;
?>