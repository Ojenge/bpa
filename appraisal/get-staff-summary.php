<?php
include_once("../config/config_mysqli.php");

$getIndividuals = mysqli_query($connect, "SELECT name, id FROM individual WHERE id IN ('ind62')");
$staffCount = mysqli_num_rows($getIndividuals);
$count = 1;
echo "[";
while($row = mysqli_fetch_array($getIndividuals))
{
	$staffRow["name"] = $row["name"];
	$staffRow["id"] = $row["id"];
	$staffRow["position"] = "Head of ICT Services";
	$staffRow["department"] = "ICT";
	$staffRow["kpi"] = "114.02";
	$staffRow["projectScore"] = "41.62";
	$staffRow["coreValues"] = "8";
	$staffRow["total"] = "163.64";
	$staffRow["rating"] = "6";
	$indId = $row["id"];
	
	echo json_encode($staffRow);
	if($count < $staffCount) echo ",";
	$staffRow = NULL;
	$count++;
	
	$initiatives = mysqli_query($connect, "SELECT name, dueDate, completionDate, deliverable, deliverableStatus FROM initiative WHERE projectManager = '$indId' ORDER BY id ASC");
	while($initiativeRow = mysqli_fetch_array($initiatives))
	{
		$initiativeRow["name"];
	}
	
	$perspectives = mysqli_query($connect, "SELECT id, name FROM perspective WHERE parentId = '$indId' ORDER BY id ASC");
	while($rowPerspective = mysqli_fetch_array($perspectives))
	{
		$rowPerspective["id"];
		$perspId = $rowPerspective["id"];
		$measures = mysqli_query($connect, "SELECT id, name, calendarType, green, weight FROM measure where linkedObject = '$perspId'");
		while($rowMeasure = mysqli_fetch_array($measures))
		{
			//echo "<br>".$rowMeasure["name"]." - ".$rowMeasure["id"].", Target: ".$rowMeasure["green"];
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
			//echo ", Actual: ".$measureActual["actual"]." weight: 17.5, Score: ".$score;
		}
	}
}
echo "]";
?>