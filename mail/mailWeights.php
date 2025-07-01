<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

include_once("../functions/cryptString.php");
include_once("../config/config_mysqli.php");

$selectedMesureId = $_POST["selectedMesureId"];
//$globalDate = $_POST["globalDate"];

$getUser = mysqli_query($connect, "SELECT user_id, display_name, email FROM uc_users WHERE user_id = (SELECT owner FROM measure WHERE id = '$selectedMesureId')");
$getUser = mysqli_fetch_array($getUser);
$display_name = $getUser["display_name"];
$userId = $getUser["user_id"];

$getSupervisor = mysqli_query($connect, "SELECT display_name, email FROM uc_users WHERE user_id = (SELECT reportsTo FROM uc_users WHERE user_id = '$userId')");
$getSupervisor = mysqli_fetch_array($getSupervisor);
$supervisorName = $getSupervisor["display_name"];
//email = $getSupervisor["email"];
$email = "isaac.sigadah@haco.co.ke";//temporary - uncomment above after trials

$to = $email;
$subject = 'KPIs and Initiatives for '.$display_name;

$headers = "From: Accent Analytics <admin@accent-analytics.com>\r\n";
//$headers .= "Bcc: lee@accent-analytics.com\r\n";
$headers .= "Reply-To: admin@accent-analytics.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

$message = '<html><body>';
$message .= 'Dear '.$supervisorName.",<br><br>";
//$userIdCrypt = cryptString($userId, "e");

$message .= 'Below are the Initiatives and Measures for '.$display_name.' with their corresponding targets and weights. Please review for your concurrence.<br>';
//$message .= 'Please note that you can update the status at any time by clicking on: ';
//$message .= '<a href="https://haco.accent-analytics.com/analytics/index.php?jina='.$userIdCrypt.'">Update My Initiatives</a>.<br><br>';
//$message .= '<table rules="all" style="border-color: #666; border:1px solid #666; border-collapse: collapse;" cellpadding="6">';
//$message .= '<tr style="background: #d4edda; color:black;"><td colspan="5"><strong>Initiatives</strong></td></tr>';
/*$message .= "<tr style='background: #e2e3e5;'>
		<td></td>
		<td width='450'><strong>Activity</strong></td>
		<td width='100'><strong>Due Date</strong></td>
		<td width='50'><strong>Status</strong></td>
		<td width='100'><strong>Last Updated</strong></td>
		</tr>";*/

$queryInitiatives = mysqli_query($connect, "SELECT id, name, startDate, dueDate, weight FROM initiative WHERE projectManager = '$userId' AND archive != 'Yes'");
$message .= '<table rules="all" style="border-color: #666; border:1px solid #666; border-collapse: collapse;" cellpadding="6">';
//$message .= "<table class='table table-sm'>"
$message .= "<tr><th colspan='4' style='background: #d4edda; color:black;'><strong>Initiatives/Activities</strong></th</tr>";
$message .= "<tr style='background: #e2e3e5;'><td><strong>Name</strong></td><td><strong>Start Date</strong></td><td><strong>Due Date</strong></td><td><strong>Weight</strong></td>";
while($row = mysqli_fetch_array($queryInitiatives))
{
$weight = $row["weight"] * 100;
$startDate = date("d M Y", strtotime($row["startDate"]));
$dueDate = date("d M Y", strtotime($row["dueDate"]));
$message.= "<tr><td>".$row["name"]."</td><td>".$startDate."</td><td>".$dueDate."</td><td>".$weight."</td></tr>";
//$total = $total + $row["weight"];
//$linkedObject = $row["linkedObject"];
}
$message .= "</table><br>";
$queryMeasures = mysqli_query($connect, "SELECT id, name, red, green, weight, linkedObject FROM measure WHERE linkedObject = '$userId'");
$message .= '<table rules="all" style="border-color: #666; border:1px solid #666; border-collapse: collapse;" cellpadding="6">';
//$message .= "<table class='table table-sm'>"
$message .= "<tr><th colspan='4' style='background: #d4edda; color:black;'><strong>Measures/KPIs</th</tr>";
$message .= "<tr style='background: #e2e3e5;'><td><strong>Name</strong></td><td><strong>Baseline</strong></td><td><strong>Target</strong></td><td><strong>Weight</strong></td>";
while($row = mysqli_fetch_array($queryMeasures))
{
$weight = $row["weight"] * 100;
$message.= "<tr><td>".$row["name"]."</td><td>".$row["red"]."</td><td>".$row["green"]."</td><td>".$weight."</td></tr>";
//$total = $total + $row["weight"];
//$linkedObject = $row["linkedObject"];
}

$message .= "</table>";
$message .= "<br>Kind Regards,<br>";
$message .= "Accent Analytics";
$message .= "</body></html>";
//$userCount = mysqli_num_rows($user_result);

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
$mail->addCC('lee@accent-analytics.com');
$mail->addCC('collins@accent-analytics.com');
//$mail->addBCC('lee@accent-analytics.com');
//$mail->addBCC('collins@accent-analytics.com');

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
//$message = mysqli_escape_string($message);
file_put_contents("sentMail.txt", "Message Sent:\n".$message);
//$mailResult = mysqli_query($connect, "INSERT INTO mail (title, message, sender, recipient, time, type, user_id) VALUES ('$subject', '$message', '$headers', '$to', '$time', 'actual', '$userId')") or file_put_contents('mailError.txt', 'Cant save message with error '.mysqli_error($connect).mysqli_error($mailResult));
		
?>