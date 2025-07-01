<?php
include_once("../config/config_mysqli.php");
	
$measure_score_query = "SELECT measuremonths.id, measure.name, measuremonths.3score, measuremonths.red, measuremonths.green, measuremonths.actual, measuremonths.date
FROM measuremonths, measure 
WHERE measuremonths.measureId = measure.id
AND measure.gaugeType = 'threeColor'";
	
$measure_score_result = mysqli_query($connect, $measure_score_query);
$measureCount = mysqli_num_rows($measure_score_result);

echo "<br>Total Number of Measures Updated = ".$measureCount;
echo "<br><table class='table table-hover table-condensed table-bordered table-responsive table-striped table-sm'>";
echo "<tr>
<th>ID</th>
<th>Measure Name</th>
<th>DB Score</th>
<th>Original Score</th>
<th>Baseline</th>
<th>Target</th>
<th>Actual</th>
<th>Date</th>
</tr>";
while($row = mysqli_fetch_assoc($measure_score_result))
{
	$id = $row["id"];
	$red = $row["red"];
	$green = $row["green"];
	$actual = $row["actual"];
	
	if($actual >= $green) $score = 10;
	else if($actual <= $red) $score = 0;
	else
	{
		$score = ((abs($actual) - abs($red))/(abs($green) - abs($red)) * ((1/3)+3)) + ((1/3)+3);
					if($score > 10) $score = 10;
					if($score < 0) $score = 0;
		$score = round($score, 2);
	}		
	
	mysqli_query($connect, "UPDATE measuremonths SET 3score = '$score' WHERE id = '$id'");
}
$measure_score_result_two = mysqli_query($connect, $measure_score_query);
while($rowTwo = mysqli_fetch_assoc($measure_score_result_two))
{
	$id = $rowTwo["id"];
	$red = $rowTwo["red"];
	$green = $rowTwo["green"];
	$actual = $rowTwo["actual"];
	
	if($actual >= $green) $score = 10;
	else if($actual <= $red) $score = 0;
	else
	{
		$score = ((abs($actual) - abs($red))/(abs($green) - abs($red)) * ((1/3)+3)) + ((1/3)+3);
					if($score > 10) $score = 10;
					if($score < 0) $score = 0;
		$score = round($score, 2);
	}	
	
	echo "<tr><td>".$id."</td><td>"
	.$rowTwo["name"]."</td>"."</td><td>"
	.$rowTwo["3score"]."</td>"."</td><td>"
	.$score."</td>"."</td><td>"
	.$red."</td>"."</td><td>"
	.$green."</td>"."</td><td>"
	.$actual."</td>"."</td><td>"
	.$rowTwo["date"]."</td>";
}
echo "</table>";

flush();
?>