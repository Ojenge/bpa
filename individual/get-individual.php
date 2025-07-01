<?php
include_once("../config/config_msqli.php");
include_once("../reports/scores-functions.2.0.php");

@$objectId = $_POST['indId'];
@$objectDate = $_POST['globalDate'];

if(strlen($objectDate) == 4) 
{
	$year = $objectDate;
	$month = date("m");
	$previousPeriod = date("Y-m", strtotime("-1 year", $objectDate));
}
else 
{
	$year = date("Y", strtotime($objectDate));
	$month = date("m", strtotime($objectDate));
	$previousPeriod = date("Y-m", strtotime("-1 month", strtotime($objectDate)));
	//file_put_contents("previous.txt", "$objectDate => ".$previousPeriod);
}

$junAppraisal = $year."-".$month."-30";//Making it flexible so that one can change the month for review. LTK 23Aug2021 1123Hrs
$junAppraisal = date("Y-m", strtotime($junAppraisal));

$decAppraisal = $year."-12-31";
$decAppraisal = date("Y-m", strtotime($decAppraisal));

//file_put_contents("aDate.txt", "June => ".$junAppraisal. " Dec = ".$decAppraisal);
$table = "measuremonths";

//$objectId = "ind20";
//$objectType = "individual";
//$objectDate = '2021-05';
$individual_query="SELECT 
uc_users.display_name AS name, 
uc_users.title AS title, 
uc_users.photo AS photo, 
uc_users.reportsTo AS reportsTo, 
organization.name AS department, 
organization.mission, 
organization.vision, 
organization.valuez 
FROM uc_users, organization
WHERE uc_users.user_id = '$objectId' AND uc_users.department = organization.id";
$individual_result = mysqli_query($GLOBALS["___mysqli_ston"], $individual_query);
$ind_row = mysqli_fetch_assoc($individual_result);

$reportsTo = $ind_row["reportsTo"];
$reportsTo = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT display_name FROM uc_users WHERE user_id = '$reportsTo'");
$reportsTo = mysqli_fetch_assoc($reportsTo);
if($reportsTo["display_name"] == NULL) $ind_row["reportsTo"] = "";
else $ind_row["reportsTo"] = $reportsTo["display_name"];

$ind_row["indScore"] = individualScore($objectId, $objectDate);

$ind_row["indScorePrevious"] = individualScore($objectId, $previousPeriod);

$initiatives = "<tr><td colspan='4'><b>Initiatives</b></td></tr>";

$initiatives = $initiatives.getInitiativesIndividuals($objectId, $objectDate);
//file_put_contents("initiatives.txt", "initiatives = $initiatives");
//$ind_row["initiatives"] = $initiatives."<tr><td>Name</td><td colspan='3'>Initiative Name</td></tr>";
$ind_row["initiatives"] = $initiatives;
$ind_data = json_encode($ind_row);
echo $ind_data;
?>