<?php
include_once("config_mysqli.php");
$count = 1;
$kpi = mysqli_query($connect, "SELECT * FROM kpicommentary");
$num = mysqli_num_rows($kpi);
echo "[";
while($row = mysqli_fetch_array($kpi))
{
	$data["id"] = $row["id"];
	$data["kpiName"] = $row["kpiName"];
	$data["kpiQuantitative"] = $row["kpiQuantitative"];
	$data["dateCreated"] = $row["dateCreated"];
	$data["defStatus"] = $row["defStatus"];
	$data["dateModified"] = $row["dateModified"];
	$data["reportStatus"] = $row["reportStatus"];
	$data["abbreviation"] = $row["abbreviation"];
	$data["lifePriority"] = $row["lifePriority"];
	$data["kpiIntegrity"] = $row["kpiIntegrity"];
	$data["kpiLevel"] = $row["kpiLevel"];
	$data["kpiGoal"] = $row["kpiGoal"];
	$data["kpiUnit"] = $row["kpiUnit"];
	$data["kpiDescr"] = $row["kpiDescr"];
	$data["kpiIntent"] = $row["kpiIntent"];
	$data["kpiProcess"] = $row["kpiProcess"];
	$data["kpiStakeholder"] = $row["kpiStakeholder"];
	$data["kpiRelationship"] = $row["kpiRelationship"];
	$data["kpiFormula"] = $row["kpiFormula"];
	$data["kpiFrequency"] = $row["kpiFrequency"];
	$data["kpiDrill"] = $row["kpiDrill"];
	$data["kpiComparison"] = $row["kpiComparison"];
	$data["kpiMethod"] = $row["kpiMethod"];
	$data["kpiPresentNotes"] = $row["kpiPresentNotes"];
	$data["kpiFrequency2"] = $row["kpiFrequency2"];
	$data["kpiResponse"] = $row["kpiResponse"];
	$data["kpiOwnerDefinition"] = $row["kpiOwnerDefinition"];
	$data["kpiOwnerPerformance"] = $row["kpiOwnerPerformance"];
	$data["kpiNotes"] = $row["kpiNotes"];
	$data["kpiOwnerReporting"] = $row["kpiOwnerReporting"];
	
	$data = json_encode($data);
	echo $data;
	if($count < $num) echo ",";
	$data = NULL;
	$count++;
}
echo "]";
?>