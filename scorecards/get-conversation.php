<?php
include_once("../config/config_msqli.php");

@$linkedId = $_POST['kpiGlobalId'];
@$userId = $_POST['userId'];

//$linkedId = '22';
//$userId = "ind1";

if(!empty($_POST['newNote']) && $_POST['newNote'] != 'empty')
{
	$newNote = $_POST['newNote'];
	date_default_timezone_set('Africa/Nairobi');
	$date = date('Y-m-d h:i:s', time());
	mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO conversation (note, senderId, date, linkedId) 
	VALUES ('$newNote', '$userId', '$date', '$linkedId')") or file_put_contents("commentary.txt", "Could not save commentary with error => ".mysqli_error()."$newNote, $userId, $date, $linkedId");
	projectMailer($userId, $newNote, $linkedId);
}

$user_query="SELECT * FROM conversation WHERE linkedId = '$linkedId' ORDER BY id ASC";
$user_result=mysqli_query($GLOBALS["___mysqli_ston"], $user_query);

$row_count = mysqli_num_rows($user_result);
if ($row_count == null) exit;

$count = 1;
echo "[";
while($row = mysqli_fetch_assoc($user_result))
{
	$data["id"] = $row["id"];
	$data["note"] = $row["note"];
	$data["senderId"] = $row["senderId"];
	$id = $data["senderId"];
		$name_query="SELECT display_name FROM uc_users WHERE user_id = '$id'";
		$name_result=mysqli_query($GLOBALS["___mysqli_ston"], $name_query);
		$name_result=mysqli_fetch_assoc($name_result);
	$data["sender"] = $name_result["display_name"];
	$data["date"] = date('d M Y', strtotime($row["date"]));
	$data = json_encode($data);
	echo $data;
	if($count<$row_count) echo ", ";
	$data = NULL;
	$count++;
}
echo "]";

function projectMailer($userId, $newNote, $linkedId)
{
	switch(substr($linkedId, 0, 3))
	{
		case "ind":
		{
			$getProject = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT display_name FROM uc_users WHERE user_id = '$linkedId'") or file_put_contents('commentary.txt',"couldn't connect to initiative table");
			$getProject = mysqli_fetch_assoc($getProject);
			$getProject = $getProject["display_name"];
			break;	
		}
		case "kpi":
		{
			$getProject = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT name FROM measure WHERE id = '$linkedId'") or file_put_contents('commentary.txt',"couldn't connect to initiative table");
			$getProject = mysqli_fetch_assoc($getProject);
			$getProject = $getProject["name"];
			break;	
		}
		default:
		{
			if(!empty($_POST['kpiGlobalType']) && $_POST["kpiGlobalType"] == "advocacy") $getProject = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT name FROM advocacy WHERE id = '$linkedId'") or file_put_contents('commentary.txt',"couldn't connect to advocacy table");
			else $getProject = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT name FROM initiative WHERE id = '$linkedId'") or file_put_contents('commentary.txt',"couldn't connect to initiative table");
			$getProject = mysqli_fetch_assoc($getProject);
			$getProject = $getProject["name"];
			break;	
		}
	}
	
	//$userId = 'ind'.$userId;
	$getUser = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT display_name, email FROM uc_users WHERE user_id = '$userId'") or file_put_contents('commentary.txt',"couldn't connect to uc_users table");;
	$getUser = mysqli_fetch_assoc($getUser);
	$userName = $getUser["display_name"];
	$email = $getUser["email"];
	
	$message = '<html><body>';
	//$message .= '<img src="http://gprs.report/images/PDULogo.gif" alt="PDU Logo" />';
	$message .= '<table rules="all" frame="box" style="border-color: #000;" cellpadding="3">';
	$message .= "<tr><td><strong>Comment:</strong> </td><td>" . $newNote . "</td></tr>";
	$message .= "</table>";
	$message .= "</body></html>";
	
	$to = $email;
	$cleanedFrom = 'admin@accent-analytics.com';
	$subject = $getProject.' Comment by ['.$userName.']';
	
	$headers = "From: " . $cleanedFrom . "\r\n";
	$headers .= "Reply-To: ". $cleanedFrom . "\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	
	if (@mail($to, $subject, $message, $headers)) 
	{
	  //echo 'Your message has been sent.';
	  file_put_contents('commentary.txt','The email with Subject: '.$subject.' and <br>Message: '.$message." has been sent");
	} 
	else 
	{
	  //echo 'There was a problem sending the email.';
	  file_put_contents('commentary.txt','The email to be sent has Subject: '.$subject.' and <br>Message: '.$message);
	}
	return;
}
?>