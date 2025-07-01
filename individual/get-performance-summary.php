<?php
include_once("../config/config_msqli.php");
include_once("../functions/functions.php");
if(isset($_POST['objectId']))
{
	$objectId = $_POST['objectId'];
	$globalDate = $_POST['globalDate'];

	//$objectId = "ind2";
	//$globalDate = "2025-06";

	if(strlen($globalDate) == 4) 
	{
		$year = $globalDate;
		$month = date("m");
	}
	else 
	{
		$year = date("Y", strtotime($globalDate));
		$month = date("m", strtotime($globalDate));
	}
	$query="SELECT interpretation FROM note WHERE objectId = '$objectId' ORDER BY date DESC LIMIT 1";

	$result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
	$row = mysqli_fetch_assoc($result);
	
	echo"<div class='border border-primary rounded-3' style='overflow:hidden;'><table class='table table-condensed table-responsive table-bordered table-sm'>";
	echo "<tr class='table-primary'><th>Section 3: Full Year Performance Review</th></tr>";
	
	if(!empty($row['interpretation']))
	echo "<tr><td class='fw-light'>".$row['interpretation']."</td></tr>";
	else 
	echo "<tr><td class='fw-light'></td></tr>";
	
	echo"</table></div><br>";
	
	$query="SELECT skillGap, intervention, startDate, dueDate, comments FROM pdp WHERE indId = '$objectId' AND dueDate LIKE '$year%'";

	$result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
	
	echo"<div class='border border-primary rounded-3' style='overflow:hidden;'><table class='table table-condensed table-responsive table-bordered table-hover table-sm table-striped'>";
	echo "<tr><th colspan='5' class='table-primary'>Section 4: Personal Development Plan</hd></tr>";
	echo "<tr><th>Skill Gap</th><th>Intervention</th><th>Start Date</th><th>Due Date</th><th>Comments</th></tr>";
	while($row = mysqli_fetch_assoc($result))
	{
		echo "<tr><td>".$row['skillGap']."</td><td>".$row['intervention']."</td><td>".$row['startDate']."</td><td>".$row['dueDate']."</td><td>".$row['comments']."</td></tr>";
	}
	echo"</table></div><br>";
	
	//Totals Table
	$indScore = individualScore($objectId, $globalDate) * 10;
	echo "<div class='border border-primary rounded-3' style='overflow:hidden;'><table class='table table-condensed table-bordered table-hover table-sm table-striped rounded'><tr><th colspan='5' class='table-primary'>Section 5: Overall Performance</th></tr>";
	
	echo "<tr><th>CLASSIFICATION</th><th>RATING GUIDE</th><th>OVERALL SCORE</th></tr>";
	
	if($indScore >= 90)
	echo "<tr><td>Outstanding Performance</td><td>Achieving above 90% of the agreed target</td><td>".$indScore."%</td></tr>";
	else echo "<tr><td>Outstanding Performance</td><td>Achieving above 90% of the agreed target</td><td></td></tr>";
	
	if($indScore >= 70 && $indScore < 90)
	echo "<tr><td>Achieved Performance</td><td>Achieving between 70 - 90% of the agreed target</td><td>".$indScore."%</td></tr>";
	else echo "<tr><td>Achieved Performance</td><td>Achieving between 70 - 90% of the agreed target</td><td></td></tr>";
	
	if($indScore >= 50 && $indScore < 70)
	echo "<tr><td>Moderate Performance</td><td>Achieving between 50 - 69% of the agreed target</td><td>".$indScore."%</td></tr>";
	else echo "<tr><td>Moderate Performance</td><td>Achieving between 50 - 69% of the agreed target</td><td></td></tr>";
	
	if($indScore > 0 && $indScore < 50)
	echo "<tr><td>Low Performance</td><td>Achieving less than 50 of the agreed target</td><td>".$indScore."%</td></tr>";
	else echo "<tr><td>Low Performance</td><td>Achieving less than 50 of the agreed target</td><td></td></tr>";
	
	echo "</table></div>";
	
	echo "<br><table class='table table-condensed table-sm signature' style='display:none;'><tr><th colspan='4'>SIGNATURE</th></tr><tr><th><br><br>Employee (Appraisee)</th><td></td><th><br><br>Date</th><td></td></tr></tr><tr><th><br><br>HOD (Appraiser)</th><td></td><th><br><br>Date</th><td></td></tr></tr><tr><th><br><br>Reviewer (HR/MD)</th><td></td><th><br><br>Date</th><td></td></tr></table>";
}
else echo "No id selected.";
?>