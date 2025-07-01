<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT'].'/PHPMailer/src/Exception.php';
require $_SERVER['DOCUMENT_ROOT'].'/PHPMailer/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'].'/PHPMailer/src/SMTP.php';

class userCakeMail {
	//UserCake uses a text based system with hooks to replace various strs in txt email templates
	public $contents = NULL;
	
	//Function used for replacing hooks in our templates
	public function newTemplateMsg($template,$additionalHooks)
	{
		global $mail_templates_dir,$debug_mode;
		
		$this->contents = file_get_contents($mail_templates_dir.$template);
		
		//Check to see we can access the file / it has some contents
		if(!$this->contents || empty($this->contents))
		{
			return false;
		}
		else
		{
			//Replace default hooks
			$this->contents = replaceDefaultHook($this->contents);
			
			//Replace defined / custom hooks
			$this->contents = str_replace($additionalHooks["searchStrs"],$additionalHooks["subjectStrs"],$this->contents);
			
			return true;
		}
	}
	
	public function sendMail($email,$subject,$msg = NULL)
	{
		global $websiteName,$emailAddress;
		
		$header = "MIME-Version: 1.0\r\n";
		$header .= "Content-type: text/plain; charset=iso-8859-1\r\n";
		$header .= "From: ". $websiteName . " <" . $emailAddress . ">\r\n";
		
		//Check to see if we sending a template email.
		if($msg == NULL)
			$msg = $this->contents; 
		
		$message = $msg;
		
		$message = wordwrap($message, 70);
		
		//$email = $emailAddress; This would force emails to go to admin instead of the registered user. Quite a hack I must admit :-)
		
		//return @mail($email,$subject,$message,$header);

		$mail = new PHPMailer(true);
		//Server settings
		//$mail->SMTPDebug   = SMTP::DEBUG_SERVER;
		$mail->SMTPDebug   = false;                        
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
		//$mail->addAddress($to, $display_name);      
		$mail->addAddress($email);            //Name is optional
		$mail->addReplyTo('admin@accent-analytics.com', 'Accent Analytics');
		//$mail->addCC('lee@accent-analytics.com');
		$mail->addBCC('lee@accent-analytics.com');
		$mail->addBCC('collins@accent-analytics.com');
		
		$mail->isHTML(true);                                          //Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $message;
		//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
		
		return $mail->send();
	}
}

?>