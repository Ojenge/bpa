<?php
include_once('../config/config_mysqli.php');
include_once('../functions/scorecard-associations.php');

echo "{ \"identifier\": \"name\", \"label\": \"name\", \"items\": ";
//$query = mysqli_query($connect, "SELECT DISTINCT linkedobjectid FROM initiativeimpact");

$count = 0;
$data = array();

$query = mysqli_query($connect, "SELECT user_id, display_name, department FROM uc_users");
while($row = mysqli_fetch_assoc($query))
{
	$data[$count]['id'] = $row["user_id"];
	$linkedObject = $row["department"];
	$parentOrg = getParentOrganization($linkedObject);
	$data[$count]['name'] = "Ind "."[".$parentOrg."] ".$row["display_name"];
	$count++;
}

$query = mysqli_query($connect, "SELECT id, name, linkedObject FROM objective");
while($row = mysqli_fetch_assoc($query))
{
	$data[$count]['id'] = $row["id"];
	$linkedObject = $row["linkedObject"];
	$parentOrg = getParentOrganization($linkedObject);
	$data[$count]['name'] = "Obj "."[".$parentOrg."] ".$row["name"];
	$count++;
}

$query = mysqli_query($connect, "SELECT id, name, linkedObject FROM measure");
while($row = mysqli_fetch_assoc($query))
{
	$data[$count]['id'] = $row["id"];
	$linkedObject = $row["linkedObject"];
	$parentOrg = getParentOrganization($linkedObject);
	$data[$count]['name'] = "Kpi "."[".$parentOrg."] ".$row["name"];
	$count++;
}

print_r(json_encode($data));
echo "}";
?>