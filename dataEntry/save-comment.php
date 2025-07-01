<?php
include_once("../functions/cryptString.php");
include_once("../config_msqli.php");

$objectId = $_POST['objectId'];
$loggedInUser = $_POST['loggedInUser'];
$note = $_POST['note'];

//$objectId = "kpi100";
//$objectId = "3";
//$loggedInUser = "ind1";
//$note = "Please provide supporting evidence.";

//Uncomment WHEN DONE WITH EMAIL TESTS!!!!!!
mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO conversation (note, senderId, linkedId) VALUES ('$note', '$loggedInUser', '$objectId')") or file_put_contents("comment.txt", "Couldn't save comment with error => ".mysqli_error($GLOBALS["___mysqli_ston"]));

$authorQuery = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT display_name, email FROM uc_users WHERE user_id = '$loggedInUser'");
$authorResult = mysqli_fetch_assoc($authorQuery);
$author = $authorResult["display_name"];
$from = $authorResult["email"];
$object = "";
switch(substr($objectId, 0, 3))
{
	case "kpi":
	{
		$objectQuery = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT measure.name, uc_users.user_id, uc_users.display_name, uc_users.email 
		FROM measure, uc_users 
		WHERE measure.id = '$objectId'
		AND measure.owner = uc_users.user_id");
		$result = mysqli_fetch_assoc($objectQuery);
		$object = $result["name"];
		$owner = $result["display_name"];
		$userId = $result["user_id"];
		$type = "Measure";
		$to = $result["email"];
		break;	
	}
	default:
	{
		$objectQuery = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT initiative.name, uc_users.user_id, uc_users.display_name, uc_users.email 
		FROM initiative, uc_users 
		WHERE initiative.id = '$objectId'
		AND initiative.projectManager = uc_users.user_id");
		$result = mysqli_fetch_assoc($objectQuery);
		$object = $result["name"];
		$owner = $result["display_name"];
		$userId = $result["user_id"];
		$type = "Initiative";
		$to = $result["email"];
		break;	
	}
}

date_default_timezone_set('Africa/Nairobi');
$dateTime = date('d F Y \a\t h:ia', time());
$userIdCrypt = cryptString($userId, "e");
//$userIdCrypt = cryptString("ind1", "e"); //Test
ob_start(); ?>
<html>
<body>
Dear <?php echo $owner; ?><br><br>

Below is a comment made against your <?php echo $type; ?>.<br>Please note that you can update the status or respond to it at any time by clicking on: <a href="https://haco.accent-analytics.com/analytics/index.php?jina='<?php echo $userIdCrypt; ?>'">Update My <?php echo $type."s"; ?></a>. (You will be logged in automatically)<br><br>
<table rules="all" style="border-color: #666; border:1px solid #666; border-collapse: collapse;" cellpadding="6">
    <tr style="background: #000; color:white;"><td colspan="2"><strong><?php echo $type.": ".$object; ?></strong></td></tr>
    <tr style='background: #eee;'><td colspan='2'></td></tr>
    <tr><td width='150'><strong>Comment</strong></td><td><?php echo $note; ?></td></tr>
    <tr><td width='150'><strong>Written by</strong></td><td><?php echo $author; ?></td></tr>
    <tr><td width='150'><strong>Date/Time</strong></td><td><?php echo $dateTime; ?></td></tr>
</table>
<br>Kind Regards,
<br>Accent Analytics
</body></html>

</body>
</html>
<?php 

$message = ob_get_contents();
ob_end_clean();

if($to == $from)
{
	//do not send email to comment made by self
}
else
{
	//echo $message;
	$subject = "Comment on Your $type";
	$headers = "From: Accent Analytics <admin@accent-analytics.com>\r\n";
	$headers .= "Reply-To: admin@accent-analytics.com\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	
	mail($to, $subject, $message, $headers);
	//mail("lee@accent-analytics.com", $subject, $message, $headers); //For testing
	
	$time = date('Y-m-d H:i:s');
	$headers = mysqli_escape_string($GLOBALS["___mysqli_ston"], $headers);
	$message = mysqli_escape_string($GLOBALS["___mysqli_ston"], $message);
	
	$result = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO mail (title, message, sender, recipient, time, type, user_id) 
	VALUES ('$subject', '$message', '$headers', '$to', '$time', 'actual', '$userId')") or 
	file_put_contents('mailError.txt', 'Cant save message with error '.mysqli_error($result).mysqli_error($GLOBALS["___mysqli_ston"]));
}
?>