<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

include_once("../functions/cryptString.php");
include_once("../config/config_mysqli.php");

//Get users listed in phpJobScheduler to email to
$getStaff = mysqli_query($connect, "SELECT selectedStaff FROM phpjobscheduler WHERE name = 'Email My Measures and Initiatives'");
$row = mysqli_fetch_array($getStaff);
$selectedStaff = $row["selectedStaff"];

$selectedStaff = json_decode($selectedStaff, true);

foreach ($selectedStaff as $key => $value)
{
	$userId = $value["value"];
	$getUser = mysqli_query($connect, "SELECT display_name, email FROM uc_users WHERE user_id = '$userId'");
	$getUser = mysqli_fetch_array($getUser);
	$email = $getUser["email"];
	$display_name = $getUser["display_name"];
	
	//Send email for Initiatives whose status is empty or hasn't been updated in a month. Check whether there is need to craft email.
	$updateResult = mysqli_query($connect, "SELECT DISTINCT initiative.id
		FROM initiative, initiative_status 
		WHERE initiative.projectManager = '$userId'
		AND initiative_status.initiativeId = initiative.id
		AND initiative_status.updatedOn < NOW() - INTERVAL 1 MONTH
        
        OR initiative.projectManager = '$userId'
        AND initiative.id NOT IN (SELECT initiativeId FROM initiative_status)") or 
	file_put_contents("errorInitiative.txt", "Cannot select initiatives with error => ".mysqli_error($connect).mysqli_error($updateCount), FILE_APPEND);
	$updateCount = mysqli_num_rows($updateResult);
	
	if($updateCount > 0)
	{
		$to = $email;
		$subject = 'Your Initiatives Need Updates';
		
		$headers = "From: Accent Analytics <admin@accent-analytics.com>\r\n";
		$headers .= "Bcc: lee@accent-analytics.com\r\n";
		$headers .= "Reply-To: admin@accent-analytics.com\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		
		$message = '<html><body>';
		$message .= 'Dear '.$display_name.",<br><br>";
		$userIdCrypt = cryptString($userId, "e");
		
		$message .= 'Below are initiatives whose status has not been updated in the last one month.<br>';
		$message .= 'Please note that you can update the status at any time by clicking on: ';
		$message .= '<a href="https://haco.accent-analytics.com/analytics/index.php?jina='.$userIdCrypt.'">Update My Initiatives</a>.<br><br>';
		$message .= '<table rules="all" style="border-color: #666; border:1px solid #666; border-collapse: collapse;" cellpadding="6">';
		$message .= '<tr style="background: #d4edda; color:black;"><td colspan="5"><strong>Initiatives</strong></td></tr>';
		$message .= "<tr style='background: #e2e3e5;'>
					<td></td>
					<td width='450'><strong>Activity</strong></td>
					<td width='100'><strong>Due Date</strong></td>
					<td width='50'><strong>Status</strong></td>
					<td width='100'><strong>Last Updated</strong></td>
					</tr>";
		
		$user_query = "SELECT DISTINCT initiative.id, initiative.name, initiative.dueDate
		FROM initiative, initiative_status 
		WHERE initiative.projectManager = '$userId'
		AND initiative_status.initiativeId = initiative.id
		AND initiative_status.updatedOn < NOW() - INTERVAL 1 MONTH
		AND initiative_status.status <> 'Completed'
        
        OR initiative.projectManager = '$userId'
        AND initiative.id NOT IN (SELECT initiativeId FROM initiative_status)
		AND initiative_status.status <> 'Completed'"; //Tried combining this with the SQL query that gets the status but ran into a challenge - initiatives without any status recorded as yet return nothing. LTK 12 June 2021 1916hrs
		
		$user_result = mysqli_query($connect, $user_query) or file_put_contents("errorInitiative.txt", "Cannot select initiatives with error => ".mysqli_error($connect).mysqli_error($user_result), FILE_APPEND);
		$count = 1;
		while($row = mysqli_fetch_array($user_result))
		{
			$initiativeId = $row["id"];
			
			$message .= "<tr>";
			$message .= "<td>$count</td>";
			$message .= "<td width='450'>".$row["name"]."</td>";
			
			if($row["dueDate"] == "") $dueDate = "";
			else $dueDate = date("d M Y", strtotime($row["dueDate"]));
			$message .= "<td width='100'>".$dueDate."</td>";
			
			$status_result = mysqli_query($connect, "SELECT status, percentageCompletion, updatedOn FROM initiative_status WHERE initiativeId = '$initiativeId' ORDER BY updatedOn DESC LIMIT 1") or file_put_contents("errorInitiative.txt", "\t\n Cannot select initiative status with error => ".mysqli_error(), FILE_APPEND);
			$status_row = mysqli_fetch_assoc($status_result);
			if($status_row["updatedOn"] == "") 
			{
				$lastUpdate = "";
				$percentSign = "";
			}
			else 
			{
				$lastUpdate = date("d M Y", strtotime($status_row["updatedOn"]));
				$percentSign = "%";
			}
			
			$message .= "<td width='50'>".$status_row["percentageCompletion"]."$percentSign</td>";
			$message .= "<td width='100'>".$lastUpdate."</td>";
			$message .= "</tr>";
			$count++;
		}
		$message .= "</table>";
		$message .= "<br>Kind Regards,<br>";
		$message .= "Accent Analytics";
		$message .= "</body></html>";
		$userCount = mysqli_num_rows($user_result);
		
		if($userCount > 0)
		{
			$mail = new PHPMailer(true);
			try {
    		//Server settings
			//Server settings
			$mail->SMTPDebug   = SMTP::DEBUG_SERVER;                        
			//$mail->isSMTP();                                             
			//$mail->Host        = 'accent-analytics.com';  
			$mail->Host        = 'localhost';             
			//$mail->SMTPAuth    = true;                                    
			//$mail->SMTPAutoTLS = false; 
			//$mail->SMTPKeepAlive = true;
			//$mail->Username    = 'admin@accent-analytics.com';                    
			//$mail->Password    = '$Hermione#1989';                               
			//$mail->SMTPSecure  = PHPMailer::ENCRYPTION_SMTPS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
			$mail->Port        = 25;                                       //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
			
			$mail->setFrom('admin@accent-analytics.com', 'Accent Analytics');
			//$mail->addAddress('lee@accent-analytics.com', 'Lee Kyonze');
			$mail->addAddress($to, $display_name);      
			//$mail->addAddress('lee@accent-analytics.com');            //Name is optional
			$mail->addReplyTo('admin@accent-analytics.com', 'Accent Analytics');
			//$mail->addCC('lee@accent-analytics.com');
			$mail->addBCC('lee@accent-analytics.com');
			$mail->addBCC('collins@accent-analytics.com');
			
			$mail->isHTML(true);                                          //Set email format to HTML
			$mail->Subject = $subject;
			$mail->Body    = $message;
			//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
			
			$mail->send();

			echo "Message below successfully sent<br><br>".$message;
			}catch (Exception $e) {
				echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
			}
			
			date_default_timezone_set('Africa/Nairobi');
			$time = date('Y-m-d H:i:s');
			//mail($to, $subject, $message, $headers);
			//mail("lkyonze@gmail.com", $subject, $message, $headers);//Test
			$message = mysqli_escape_string($message);
			$mailResult = mysqli_query($connect, "INSERT INTO mail (title, message, sender, recipient, time, type, user_id) VALUES ('$subject', '$message', '$headers', '$to', '$time', 'actual', '$userId')") or file_put_contents('mailError.txt', 'Cant save message with error '.mysqli_error($connect).mysqli_error($mailResult));
		}
		else echo "<br><br>No assigned projects to be sent for $display_name.";
		}
	else echo "No initiatives to date<br><br>";
}
?>