<?php
include_once("config_mysqli.php");

$initiative_id_result = @mysqli_query($connect, "SELECT MAX(id) FROM advocacy");
$initiative_array = @mysqli_fetch_array($initiative_id_result);
$initiative_id = $initiative_array[0] + 1;

$editAdvocacyStatus = NULL;
$selectedAdvocacy = NULL;
$advocacyName = NULL;
$advocacyCategory = NULL;
$advocacyType = NULL;
$advocacyOwner = NULL;
$advocacyMeaning = NULL;
$advocacyAgency = NULL;
$advocacyStart = NULL;
$advocacyDue = NULL;
$advocacyStatus = NULL;
$advocacyComplete = NULL;

if(!empty($_POST['editAdvocacyStatus'])) $editAdvocacyStatus = $_POST['editAdvocacyStatus'];
if(!empty($_POST['selectedAdvocacy'])) $selectedAdvocacy = $_POST['selectedAdvocacy'];
//file_put_contents("advocacy.txt", "\r\n Captured  $selectedAdvocacy ", FILE_APPEND);
if(!empty($_POST['advocacyName']))$advocacyName = $_POST["advocacyName"];
if(!empty($_POST['advocacyCategory']))$advocacyCategory = $_POST["advocacyCategory"]; else $advocacyCategory = 'Uncategorised';
if(!empty($_POST['advocacyType']))$advocacyType = $_POST["advocacyType"];
if(!empty($_POST['advocacyOwner']))$advocacyOwner = $_POST["advocacyOwner"];
if(!empty($_POST['advocacyMeaning']))$advocacyMeaning = $_POST["advocacyMeaning"];
if(!empty($_POST['advocacyAgency']))$advocacyAgency = $_POST["advocacyAgency"];
if(!empty($_POST['advocacyStart']))$advocacyStart = $_POST["advocacyStart"];
if(!empty($_POST['advocacyDue']))$advocacyDue = $_POST["advocacyDue"];
if(!empty($_POST['advocacyStatus']))$advocacyStatus = $_POST["advocacyStatus"];
if(!empty($_POST['advocacyComplete']))$advocacyComplete = $_POST["advocacyComplete"];

if(!empty($_POST['advocacyComplete']))$advocacyComplete = date('Y-m-d',strtotime($advocacyComplete)); else $advocacyComplete = NULL;
if(!empty($_POST['advocacyStart']))$advocacyStart = date('Y-m-d',strtotime($advocacyStart)); else $advocacyStart = NULL;
if(!empty($_POST['advocacyDue']))$advocacyDue = date('Y-m-d',strtotime($advocacyDue)); else $advocacyDue = NULL;

date_default_timezone_set('Africa/Nairobi');
$updateDate = date('Y-m-d H:i:s');

if($editAdvocacyStatus == "Edit")
{
	$dbResult = mysqli_query($connect, "SELECT `status`, `updateDate` FROM advocacy WHERE id = '$selectedAdvocacy'");
	$dbResult = mysqli_fetch_assoc($dbResult);
	$dbStatus = $dbResult["status"];
	$dbDate = $dbResult["updateDate"];
	
	$advocacyMeaning = mysql_real_escape_string($advocacyMeaning);
	mysqli_query($connect, "UPDATE advocacy SET name = '$advocacyName', category = '$advocacyCategory', owner = '$advocacyOwner', 
	startDate = ".($advocacyStart == NULL ? "NULL" : "'$advocacyStart'").", dueDate = ".($advocacyDue == NULL ? "NULL" : "'$advocacyDue'").", completionDate = ".($advocacyComplete == NULL ? "NULL" : "'$advocacyComplete'").", status = '$advocacyStatus', 
	meaning = '$advocacyMeaning', type = '$advocacyType', agency = '$advocacyAgency', updateDate = '$updateDate' 
	WHERE id = '$selectedAdvocacy'");
	echo "{\"id\":\"".$selectedAdvocacy."\",\"category\":\"".$advocacyCategory."\"}";
	
	switch($advocacyStatus)
	{
		case "Identified":
		{
			$statusColor = "red";
			break;	
		}
		case "Request Made to Government":
		{
			$statusColor = "orange";
			break;	
		}
		case "Government Committed":
		{
			$statusColor = "yellow";
			break;	
		}
		case "Implementation in Progress":
		{
			$statusColor = "green";
			break;	
		}
		case "Issue Resolved":
		{
			$statusColor = "blue";
			break;	
		}
	}
	switch($dbStatus)
	{
		case "Identified":
		{
			$dbStatusColor = "red";
			break;	
		}
		case "Request Made to Government":
		{
			$dbStatusColor = "orange";
			break;	
		}
		case "Government Committed":
		{
			$dbStatusColor = "yellow";
			break;	
		}
		case "Implementation in Progress":
		{
			$dbStatusColor = "green";
			break;	
		}
		case "Issue Resolved":
		{
			$dbStatusColor = "blue";
			break;	
		}
	}
	if($dbStatus == $advocacyStatus)
	{
		//file_put_contents("workplan.txt","At if($dbStatus == $advocacyStatus) line 112");
	}
	else
	{
		$update_month = date('Y-m');
		$dbDate = date('Y-m', strtotime($dbDate));
		if($update_month == $dbDate)
		{
			$statusCount = mysqli_query($connect, "SELECT $statusColor AS count FROM advocacy_trend WHERE date LIKE '$dbDate%'");
			$statusCount = mysqli_fetch_assoc($statusCount);
			$statusCount = $statusCount["count"];
			
			$dbStatusCount = mysqli_query($connect, "SELECT $dbStatusColor AS count FROM advocacy_trend WHERE date LIKE '$dbDate%'");
			$dbStatusCount = mysqli_fetch_assoc($dbStatusCount);
			$dbStatusCount = $dbStatusCount["count"];
		
			$statusCount = $statusCount + 1;
			$dbStatusCount = $dbStatusCount - 1;
			mysqli_query($connect, "UPDATE advocacy_trend SET $statusColor = '$statusCount' WHERE date LIKE '$dbDate%'");
			mysqli_query($connect, "UPDATE advocacy_trend SET $dbStatusColor = '$dbStatusCount' WHERE date LIKE '$dbDate%'");
		}
		else
		{
			file_put_contents("workplan.txt","At else($update_month == $dbDate) line 135");
			$statusCount = mysqli_query($connect, "SELECT $statusColor AS count FROM advocacy_trend WHERE date LIKE '$dbDate%'");
			$statusCount = mysqli_fetch_assoc($statusCount);
			$statusCount = $statusCount["count"];
			
			$statusCount = $statusCount + 1;
			
			if($statusColor == 'red')
			mysqli_query($connect, "INSERT INTO advocacy_trend (id, date, red, orange, yellow, green, blue) VALUES ('', '$updateDate', '$statusCount', '', '', '', '')");
			else if($statusColor == 'orange')
			mysqli_query($connect, "INSERT INTO advocacy_trend (id, date, red, orange, yellow, green, blue) VALUES ('', '$updateDate', '', '$statusCount', '', '', '')");
			else if($statusColor == 'yellow')
			mysqli_query($connect, "INSERT INTO advocacy_trend (id, date, red, orange, yellow, green, blue) VALUES ('', '$updateDate', '', '', '$statusCount', '', '')");
			else if($statusColor == 'green')
			mysqli_query($connect, "INSERT INTO advocacy_trend (id, date, red, orange, yellow, green, blue) VALUES ('', '$updateDate', '', '', '', '$statusCount', '')");
			else if($statusColor == 'blue')
			mysqli_query($connect, "INSERT INTO advocacy_trend (id, date, red, orange, yellow, green, blue) VALUES ('', '$updateDate', '', '', '', '','$statusCount')");
		}
	}
}
else if($editAdvocacyStatus == "New")
{
	/*mysqli_query($connect, "INSERT INTO advocacy 
	(id, name, category, owner, startDate, dueDate, completionDate, status, meaning, type, agency) 
	VALUES ('$initiative_id', '$advocacyName', '$advocacyCategory', '$advocacyOwner', ".($advocacyStart == NULL ? "NULL" : "'$advocacyStart'").", ".($advocacyDue == NULL ? "NULL" : "'$advocacyDue'").", ".($advocacyComplete == NULL ? "NULL" : "'$advocacyComplete'").", '$advocacyStatus', '$advocacyMeaning', '$advocacyType', '$advocacyAgency')");*/
	/*file_put_contents("advocacy.txt", "INSERT INTO advocacy 
	(id, name, category, owner, startDate, dueDate, completionDate, status, meaning, type, agency) VALUES 
	('$initiative_id', '$advocacyName', '$advocacyCategory', '$advocacyOwner', NULL, NULL, NULL, '$advocacyStatus', '$advocacyMeaning', '$advocacyType', '$advocacyAgency')");*/
	$advocacyMeaning = mysql_real_escape_string($advocacyMeaning);
	mysqli_query($connect, "INSERT INTO advocacy 
	(id, name, category, owner, startDate, dueDate, completionDate, status, meaning, type, agency, updateDate) VALUES 
	('$initiative_id', '$advocacyName', '$advocacyCategory', '$advocacyOwner', NULL, NULL, NULL, '$advocacyStatus', '$advocacyMeaning', '$advocacyType', '$advocacyAgency', '$updateDate')");
	mysqli_query($connect, "UPDATE advocacy SET startDate = ".($advocacyStart == NULL ? "NULL" : "'$advocacyStart'")." WHERE id = '$initiative_id'");
	mysqli_query($connect, "UPDATE advocacy SET dueDate = ".($advocacyDue == NULL ? "NULL" : "'$advocacyDue'")." WHERE id = '$initiative_id'");
	mysqli_query($connect, "UPDATE advocacy SET completionDate = ".($advocacyComplete == NULL ? "NULL" : "'$advocacyComplete'")." WHERE id = '$initiative_id'");
	echo "{\"id\":\"".$initiative_id."\",\"category\":\"".$advocacyCategory."\"}";
	
	switch($advocacyStatus)
	{
		case "Identified":
		{
			$statusColor = "red";
			break;	
		}
		case "Request Made to Government":
		{
			$statusColor = "orange";
			break;	
		}
		case "Government Committed":
		{
			$statusColor = "yellow";
			break;	
		}
		case "Implementation in Progress":
		{
			$statusColor = "green";
			break;	
		}
		case "Issue Resolved":
		{
			$statusColor = "blue";
			break;	
		}
	}
	$checkDate = mysqli_query($connect, "SELECT date, red, orange, yellow, green, blue FROM advocacy_trend ORDER BY id DESC LIMIT 0, 1");
	$checkDate = mysqli_fetch_array($checkDate);
	$rowDate = $checkDate["date"];
	$dbDate = date("Y-m",strtotime($update_date));
	if(date("Y-m",strtotime($rowDate)) == $dbDate)
	{
		$statusCount = mysqli_query($connect, "SELECT $statusColor AS count FROM advocacy_trend WHERE date LIKE '$dbDate%'");
		$statusCount = mysqli_fetch_assoc($statusCount);
		$statusCount = $statusCount["count"];
		//file_put_contents("workplan.txt","statusCount = $statusCount; statusColor = $statusColor");
		$statusCount = $statusCount + 1;
		mysqli_query($connect, "UPDATE trend SET $statusColor = '$statusCount' WHERE date LIKE '$dbDate%'");
	}
	else
	{
		if($statusColor == 'red')
		mysqli_query($connect, "INSERT INTO advocacy_trend (id, date, red, orange, yellow, green, blue) VALUES ('', '$update_date', '1', '', '', '', '')");
		else if($statusColor == 'orange')
		mysqli_query($connect, "INSERT INTO advocacy_trend (id, date, red, orange, yellow, green, blue) VALUES ('', '$update_date', '', '1', '', '', '')");
		else if($statusColor == 'yellow')
		mysqli_query($connect, "INSERT INTO advocacy_trend (id, date, red, orange, yellow, green, blue) VALUES ('', '$update_date', '', '', '1', '', '')");
		else if($statusColor == 'green')
		mysqli_query($connect, "INSERT INTO advocacy_trend (id, date, red, orange, yellow, green, blue) VALUES ('', '$update_date', '', '', '', '1', '')");
		else if($statusColor == 'blue')
		mysqli_query($connect, "INSERT INTO advocacy_trend (id, date, red, orange, yellow, green, blue) VALUES ('', '$update_date', '', '', '', '', '1')");
	}
}
else if($editAdvocacyStatus == "Delete")
{
	mysqli_query($connect, "DELETE FROM advocacy WHERE id = '$selectedAdvocacy'");
	echo "{\"id\":\"".$selectedAdvocacy."\",\"category\":\"".$advocacyCategory."\"}";
}
?>