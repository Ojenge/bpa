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
<th>Old Score</th>
<th>New Score</th>
<th>Baseline</th>
<th>Target</th>
<th>Green Threshold</th>
<th>Actual</th>
<th>Date</th>
</tr>";
while($row = mysqli_fetch_assoc($measure_score_result))
{
	$id = $row["id"];
	$red = $row["red"] ?? 0;
	$green = $row["green"] ?? 0;
	$actual = $row["actual"] ?? 0;

	// Convert to numeric values and handle null/non-numeric values
	$red = is_numeric($red) ? (float)$red : 0;
	$green = is_numeric($green) ? (float)$green : 0;
	$actual = is_numeric($actual) ? (float)$actual : 0;

	// Avoid division by zero
	$denominator = abs($green) - abs($red);
	if ($denominator == 0) {
		$score = 0;
		$newScore = 0;
	} else {
		$score = ((abs($actual) - abs($red))/$denominator * ((1/3)+3)) + ((1/3)+3);
		if($score > 10) $score = 10;
		if($score < 0) $score = 0;
		$score = round($score, 2);

		//new target = baseline + (target - baseline)/2
		$newGreen = $red + ($green - $red)/2;

		// Recalculate denominator for newScore
		$newDenominator = abs($newGreen) - abs($red);
		if ($newDenominator == 0) {
			$newScore = 0;
		} else {
			$newScore = ((abs($actual) - abs($red))/$newDenominator * ((1/3)+3)) + ((1/3)+3);
			if($newScore > 10) $newScore = 10;
			if($newScore < 0) $newScore = 0;
			if($actual < $red && $actual < $green && $red < $green) $newScore = 0; //taking care of negative actual values
		}
		$newScore = round($newScore, 2);
	}

	mysqli_query($connect, "UPDATE measuremonths SET 3score = '$newScore' WHERE id = '$id'");
}
$measure_score_result_two = mysqli_query($connect, $measure_score_query);
while($rowTwo = mysqli_fetch_assoc($measure_score_result_two))
{
	$id = $rowTwo["id"];
	$red = $rowTwo["red"] ?? 0;
	$green = $rowTwo["green"] ?? 0;
	$actual = $rowTwo["actual"] ?? 0;

	// Convert to numeric values and handle null/non-numeric values
	$red = is_numeric($red) ? (float)$red : 0;
	$green = is_numeric($green) ? (float)$green : 0;
	$actual = is_numeric($actual) ? (float)$actual : 0;

	// Avoid division by zero
	$denominator = abs($green) - abs($red);
	if ($denominator == 0) {
		$score = 0;
		$newScore = 0;
		$newGreen = $green;
	} else {
		$score = ((abs($actual) - abs($red))/$denominator * ((1/3)+3)) + ((1/3)+3);
		if($score > 10) $score = 10;
		if($score < 0) $score = 0;
		$score = round($score, 2);

		//new target = baseline + (target - baseline)/2
		$newGreen = $red + ($green - $red)/2;

		// Recalculate denominator for newScore
		$newDenominator = abs($newGreen) - abs($red);
		if ($newDenominator == 0) {
			$newScore = 0;
		} else {
			$newScore = ((abs($actual) - abs($red))/$newDenominator * ((1/3)+3)) + ((1/3)+3);
			if($newScore > 10) $newScore = 10;
			if($newScore < 0) $newScore = 0;
		}
		$newScore = round($newScore, 2);
	}
	
	echo "<tr><td>".$id."</td><td>"
	.$rowTwo["name"]."</td>"."</td><td>"
	.$rowTwo["3score"]."</td>"."</td><td>"
	.$score."</td>"."</td><td>"
	.$newScore."</td>"."</td><td>"
	.$red."</td>"."</td><td>"
	.$green."</td>"."</td><td>"
	.$newGreen."</td>"."</td><td>"
	.$actual."</td>"."</td><td>"
	.$rowTwo["date"]."</td>";
}
echo "</table>";

flush();
?>