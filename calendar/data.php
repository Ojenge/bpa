<?php
include('../config/config_mysqli.php');

$data = array(); 
$count = 0;
$classCount = 0;
$user = "";
/* className: default(transparent), important(red), chill(pink), success(green), info(blue)*/
$className = array();
$className[0] = "important";
$className[1] = "info";
$className[2] = "success";
$className[3] = "chill";
$user = $_POST["user"];
//$user = "ind2";
//file_put_contents("id.txt", "Logged in user = ".$user);
$getInitiatives = mysqli_query($connect, "SELECT id, name, startDate AS start, dueDate AS end 
FROM initiative 
WHERE projectManager = '$user'");
while($row = mysqli_fetch_array($getInitiatives))
{
	$data[$count]["id"] = $row["id"];
	$data[$count]["title"] = $row["name"];
	
	//Date Formats: https://www.php.net/manual/en/datetime.format.php
	$data[$count]["startY"] = date("Y",strtotime($row["start"]));
	$data[$count]["startM"] = date("n",strtotime("-1 month", $row["start"]));//Numeric representation of a month, without leading zeros 
																			//Javascript January is 0 - subtract one month
	$data[$count]["startD"] = date("j",strtotime($row["start"]));//Day of the month without leading zeros
	//$data[$count]["startH"] = date("G",strtotime($row["start"]));//24-hour format of an hour with leading zeros
	$data[$count]["startH"] = '8';
	//$data[$count]["startMin"] = date("i",strtotime($row["start"]));//Minutes with leading zeros - seems there's not minutes without leading zeros.
	$data[$count]["startMin"] = '0';
	
	$data[$count]["endY"] = date("Y",strtotime($row["end"]));
	$data[$count]["endM"] = date("n",strtotime("-1 month", $row["end"]));
	$data[$count]["endD"] = date("j",strtotime($row["end"]));
	$data[$count]["endH"] = '17';
	$data[$count]["endMin"] = '0';
	$data[$count]["className"] = $className[$classCount];
	$data[$count]["allDay"] = false;
	$count++;
	$classCount++;
	if($classCount == 4) $classCount = 0;
}
$json =  json_encode($data, JSON_PRETTY_PRINT); 
echo '{"initiatives":'.$json.'}';
/*
echo '{"initiatives":[{"id": "1", "title": "JSON Event", "startY": "2021", "startM":"4", "startD":"23", "startH": "16", "startMin":"0", "endY": "2021", "endM":"4", "endD":"28", "endH": "16", "endMin":"0", "allDay": "false", "className": "info"},{"id": "2", "title": "Display Once!!!", "startY": "2021", "startM":"4", "startD":"24", "startH": "8", "startMin":"0", "endY": "2021", "endM":"4", "endD":"29", "endH": "8", "endMin":"0", "allDay": "false", "className": "chill"}]}';*/
?>