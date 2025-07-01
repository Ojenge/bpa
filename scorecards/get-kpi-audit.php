<?php
include_once("../config/config_mysqli.php");
@$kpiId = $_POST['objectId'];
//$kpiId = "kpi19";
$measureName = mysqli_query($connect, "SELECT name FROM measure WHERE id = '$kpiId'");
$measureName = mysqli_fetch_assoc($measureName);
$measureName  = $measureName["name"];
$auditResult = mysqli_query($connect, "SELECT date, COUNT(date) as numDate FROM kpi_audit WHERE measureId = '$kpiId' GROUP BY date HAVING COUNT(*) > 1");
if(mysqli_num_rows($auditResult) > 0)
{
	echo "<table><tr><td>Measure: $measureName</td></tr>";
	while($row = mysqli_fetch_assoc($auditResult))
	{
		$date = $row["date"];
		$auditDetails =	mysqli_query($connect, "SELECT actual, updater, time FROM kpi_audit WHERE measureId = '$kpiId' AND date = '$date'");
		while($row = mysqli_fetch_assoc($auditDetails))
		{
			$updaterId = $row["updater"];
			$updaterName = mysqli_query($connect, "SELECT display_name FROM uc_users WHERE user_id = '$updaterId'");
			$updaterName = mysqli_fetch_assoc($updaterName);
			$updaterName  = $updaterName["display_name"];
			echo "<tr><td>".$updaterName." changed value to ". $row["actual"]." on ".date('d M Y',$row["time"])." at ".date('H:i:s',$row["time"])."</td></tr>";
		}
	}
	echo "</table>";
}
else
{
	echo "Measure: $measureName<br>None of the original values have been changed for this measure";	
}
?>