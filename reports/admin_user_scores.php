<style>
.listTable{
	border: 1px solid black;
	border-radius: 6px;
	-moz-border-radius: 6px;
	-webkit-border-radius: 6px;
	-o-border-radius: 6px;
	font-size:14px;
	font-family:"Trebuchet MS", Arial, Helvetica, sans-serif;
}
.listTable th{
	background-color:#009;
    color: white;
	text-align:center;
}
.listTable tr:nth-child(odd){
	background-color: #ebf4fa;
}
.listTable tr:nth-child(even){
	background-color: #fff;
}
.innerTable
{width:100%}
</style>
<?php
require_once("../config/config_mysqli.php");
require_once("../functions/functions.php");
include_once("../functions/calendar-labels.php");
include_once("../functions/perspOrg-scores.php");

$objectDate = date("Y-m-d");
$valuesCount = 1;
$objectPeriod = "months";

$orgQuery = mysqli_query($connect, "SELECT id, name FROM organization ORDER by id");
echo "<table class='listTable'><tr><th>Organization/Department</th><th>Organization Score</th><th>Staff Scores</th>";
while($orgRow = mysqli_fetch_array($orgQuery))
{
	//$orgScore = cascaded_departments_score($orgRow["id"], $objectDate, 'measuremonths');
	
	$objectId = orgChildIds($orgRow["id"]);
	switch($objectPeriod)
	{
		case "days":
		{
			$orgScore = daysAsIs($objectId, $objectDate, $valuesCount);
			break;
		}
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "weeks":
		{
			//daysInWeeks($objectId, $objectDate, $valuesCount,  $red, $green, $darkgreen, $blue, $gaugeType);
			$orgScore = weeksAsIs($objectId, $objectDate, $valuesCount);
			break;
		}
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "months":
		{
			$orgScore = monthsAsIs($objectId, $objectDate, $valuesCount);
			break;
		}
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "quarters":
		{
			$orgScore = quartersAsIs($objectId, $objectDate, $valuesCount);
			break;
		}	
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "halfYears":
		{
			//$objectDate = strtotime ( '-1 years' , strtotime ( $objectDate.'-01-01' ) ) ;
			//$objectDate = date ( 'Y-m-d' , $objectDate );
			$orgScore = halfYearsAsIs($objectId, $objectDate, $valuesCount);
			break;
		}
		/*******************************************************************************************************************
		$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		********************************************************************************************************************/
		case "years":
		{
			//$objectDate = strtotime ( '+1 years' , strtotime ( $objectDate.'-01-01' ) ) ;
			//$objectDate = date ( 'Y-m-d' , $objectDate );
			$orgScore = yearsAsIs($objectId, $objectDate, $valuesCount);
			break;
		}
	}
	$orgScore = $orgScore;
	$orgColor = return_color($orgScore, "threeColor");
	echo "<tr><td>".$orgRow["name"]."</td><td bgcolor='".$orgColor."' align='center'>".$orgScore."</td><td><ol>";
	$orgId = $orgRow["id"];
	$query = mysqli_query($connect, "SELECT uc_users.user_id AS user_id, uc_users.display_name AS display_name
	FROM uc_users, individual
	WHERE uc_users.user_id = individual.id
	AND individual.cascadedFrom = '$orgId'
	AND uc_users.id != '1' 
	AND uc_users.id != '19' 
	AND uc_users.id != '22' 
	AND uc_users.id != '29' 
	AND uc_users.id != '58'");
	while($row = mysqli_fetch_array($query))
	{
		$indScore = individualScore($row["user_id"], $objectDate, $objectPeriod);
		$indScore = round($indScore, 2); 
		if($indScore == 0) {
			$indScore = '-';
			$indColor = '#F2F1EF';
		}
		else $indColor = return_color($indScore, "threeColor");
		echo "<li>".$row["display_name"].":<div style='background-color:".$indColor.";float:right; width:60px; text-align:right;'>".$indScore."</div></li>";
	}
	echo "</ol></td></tr>";
}
echo "</table>";
?>