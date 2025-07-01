<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

#[AllowDynamicProperties]
class User 
{
	public $user_active = 0;
	private $clean_email;
	public $status = false;
	private $clean_password;
	private $username;
	private $displayname;
	private $reportsTo;
	private $title;
	private $department;
	public $sql_failure = false;
	public $mail_failure = false;
	public $email_taken = false;
	public $username_taken = false;
	public $displayname_taken = false;
	public $activation_token = 0;
	public $success = NULL;
	
	function __construct($user,$display,$supervisor,$pass,$email,$title,$department)
	{
		//Used for display only
		$this->displayname = $display;
		$this->reportsTo = $supervisor;
		$this->title = $title;
		$this->department = $department;
		
		//Sanitize
		$this->clean_email = sanitize($email);
		$this->clean_password = trim($pass);
		$this->username = sanitize($user);
		
		if(usernameExists($this->username))
		{
			$this->username_taken = true;
		}
		//else if(displayNameExists($this->displayname))
		//{
		//	file_put_contents("registerErrors.txt", "\r\n => Getting here username", FILE_APPEND);
		//	$this->displayname_taken = true;
		//}
		else if(emailExists($this->clean_email))
		{
			$this->email_taken = true;
		}
		else
		{
			//No problems have been found.
			$this->status = true;
		}
	}
	
	public function userCakeAddUser()
	{
		global $mysqli,$emailActivation,$websiteUrl,$db_table_prefix;
		
		//Prevent this function being called if there were construction errors
		if($this->status)
		{
			//Construct a secure hash for the plain text password
			$secure_pass = generateHash($this->clean_password);
			
			//Construct a unique activation token
			$this->activation_token = generateActivationToken();
			
			//Do we need to send out an activation email?
			$emailActivation = "false";//Hack to prevent email error issue - need to fix this though. LTK 17 Jun 2016
			if($emailActivation == "true")
			{
				//User must activate their account first
				$this->user_active = 0;
				
				$mail = new userCakeMail();
				
				//Build the activation message
				$activation_message = lang("ACCOUNT_ACTIVATION_MESSAGE",array($websiteUrl,$this->activation_token));
				
				//Define more if you want to build larger structures
				$hooks = array(
					"searchStrs" => array("#ACTIVATION-MESSAGE","#ACTIVATION-KEY","#USERNAME#"),
					"subjectStrs" => array($activation_message,$this->activation_token,$this->displayname)
					);
				
				/* Build the template - Optional, you can just use the sendMail function 
				Instead to pass a message. */
				
				if(!$mail->newTemplateMsg("new-registration.txt",$hooks))
				{
					$this->mail_failure = true;
				}
				else
				{
					//Send the mail. Specify users email here and subject. 
					//SendMail can have a third parementer for message if you do not wish to build a template.
					
					if(!$mail->sendMail($this->clean_email,"New User"))
					{
						$this->mail_failure = true;
					}
				}
				$this->success = lang("ACCOUNT_REGISTRATION_COMPLETE_TYPE2");
			}
			else
			{
				//Instant account activation
				$this->user_active = 0;//Once you fix the email thing - change this back to 1. LTK 17 Jun 2016
				$this->success = lang("ACCOUNT_REGISTRATION_COMPLETE_TYPE1");
			}	
			
			if(!$this->mail_failure)
			{
				//file_put_contents("registerErrors.txt", "\r\n => reportsTo: ".$this->reportsTo.", Department: ".$this->department, FILE_APPEND);
				//Insert the user into the database providing no errors have been found.
				//$escapedDepartment = @mysql_real_escape_string($this->department);
				$stmt = $mysqli->prepare("INSERT INTO ".$db_table_prefix."users (
					user_name,
					display_name,
					reportsTo,
					password,
					email,
					activation_token,
					last_activation_request,
					lost_password_request, 
					active,
					title,
					department,
					sign_up_stamp,
					last_sign_in_stamp
					)
					VALUES (
					?,
					?,
					?,
					?,
					?,
					?,
					'".time()."',
					'0',
					?,
					?,
					?,
					'".time()."',
					'0'
					)");
				
				$stmt->bind_param("ssssssiss", $this->username, $this->displayname, $this->reportsTo, $secure_pass, $this->clean_email, $this->activation_token, $this->user_active, $this->title, $this->department);
				$stmt->execute();
				$inserted_id = $mysqli->insert_id;
				$stmt->close();
				
				$indId = "ind".$inserted_id;
				
				$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."users
					SET user_id = ?
					WHERE
					id = ?
					");
				$stmt->bind_param("ss", $indId, $inserted_id);
				$result = $stmt->execute();
				$stmt->close();
				
				//Insert default permission into matches table
				$stmt = $mysqli->prepare("INSERT INTO ".$db_table_prefix."user_permission_matches  (
					user_id,
					permission_id
					)
					VALUES (
					?,
					'1'
					)");
				$stmt->bind_param("s", $inserted_id);
				$stmt->execute();
				$stmt->close();
				
				$stmt = $mysqli->prepare("INSERT INTO user_backup
					(id, username, `password`, name)
					VALUES (
					?,
					?,
					?,
					?
					)");
				$stmt->bind_param("ssss", $inserted_id, $this->username, $this->clean_password, $this->displayname);
				$stmt->execute();
				$stmt->close();
				
				$stmt = $mysqli->prepare("INSERT INTO tree
					(id, name, parent, type)
					VALUES (
					?,
					?,
					?,
					'individual'
					)");
				$stmt->bind_param("sss", $indId, $this->displayname, $this->department);
				$stmt->execute();
				$stmt->close();
				
				//Insert new user as a permission to be listed in admin page
				$stmt = $mysqli->prepare("INSERT INTO ".$db_table_prefix."permissions (name, orgId, status, function, url, home) VALUES (?, ?, 'Active', '', '', 'No') ");
				$stmt->bind_param("ss", $this->displayname, $indId);
				$stmt->execute();
				$stmt->close();	
				
				if(!file_exists("https://accent-analytics.com/imageUpload/tempPicExt.txt")) 
				{
					//do nothing - prevent system from saving non-existent picture in db
				}
				else
				{
					//Save uploaded user photo path onto db
					$fileExt = file_get_contents("https://accent-analytics.com/imageUpload/tempPicExt.txt");
					$username = $this->username;
									
					rename("images/profilePics/tempPic.$fileExt","images/profilePics/$username.$fileExt");
									
					$profilePic = "images/profilePics/".$username.".".$fileExt;
					
					$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."users
						SET photo = ?
						WHERE
						id = ?
						");
					$stmt->bind_param("ss", $profilePic, $inserted_id);
					$result = $stmt->execute();
					$stmt->close();
					unlink("https://accent-analytics.com/imageUpload/tempPic.txt"); //delete temporary files
					unlink("https://accent-analytics.com/imageUpload/tempPicExt.txt");
				}	
			}
		}
	}
}
?>