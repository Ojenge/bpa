<?php
function getMailAttachment($date, $subject, $from)
{
	/*    Downloads attachments from email and saves it to a file.
	 *    Uses PHP IMAP extension, so make sure it is enabled in your php.ini,
	 *    extension=php_imap.dll
	 */
	 
	set_time_limit(3000); 
	//date_default_timezone_set('Africa/Nairobi');
	/* connect to gmail with your credentials */
	//$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
	//$username = 'lkyonze@gmail.com';
	//$password = 'jvyxvajhkujsjhmr';
	
	$hostname = '{accent-analytics.com:993/imap/ssl/novalidate-cert}INBOX';
	$username = 'hacoreports@accent-analytics.com';
	$password = 'HACO2021!';
	
	$inbox = imap_open($hostname,$username,$password) or file_put_contents("emailError.txt", 'Cannot connect to Accent email: ' . imap_last_error());
	
	/* get all new emails. If set to 'ALL' instead of 'NEW' retrieves all the emails, but can be 
	 * resource intensive, so the following variable, $max_emails, puts the limit on the number of emails downloaded.
	 */
	//$date = "14 August 2022";
	//$subject = "Mid Month Reports (Cashburn & OPEX Month on Month)";
	//$from = "lkyonze@gmail.com";
	//echo "<tr><td colspan='8'>Subject = $subject and Date = $date; inbox = $inbox".imap_last_error()."</td></tr>";
	
	$emails = imap_search($inbox, 'SUBJECT "'.$subject.'" FROM "'.$from.'" SINCE "'.$date.'"') or file_put_contents("emailError.txt", 'Cannot search Accent email: ' . imap_last_error());;
	
	$max_emails = 3; /* useful only if the above search is set to 'ALL' */
	
	if($emails) /* if any emails found, iterate through each email */
	{   
		//echo "<tr><td colspan='8'>Found Emails</td></tr>"; 
		$count = 1;
		
		rsort($emails); /* put the newest emails on top */
		
		foreach($emails as $email_number) /* for every email... */
		{
			/*$overview = imap_fetch_overview($inbox,$email_number,0); // get information specific to this email
			echo $overview[0]->date."<br>";
			echo $overview[0]->from."<br>";
			$mailDate = $overview[0]->date;*/
			
			$headers = imap_headerinfo($inbox, $email_number);
			$mailDate = $headers->MailDate;
			$mailFrom = $headers->senderaddress;
			$mailSubject = $headers->subject;
			
			//echo "<tr><td colspan='8'>Found Emails; Date = $mailDate</td></tr>";
			
			//$variable = '4-Dec-2021 21:30:56 +0000 ';
			$mailDate = strval($mailDate);
			$mailDate = trim($mailDate);
			
			/* get mail message, not actually used here. 
			   Refer to http://php.net/manual/en/function.imap-fetchbody.php
			   for details on the third parameter.
			 */
			$message = imap_fetchbody($inbox,$email_number,2);
			
			$structure = imap_fetchstructure($inbox, $email_number); /* get mail structure */
	
			$attachments = array();
			
			if(isset($structure->parts) && count($structure->parts)) /* if any attachments found... */
			{
				//for($i = 0; $i < count($structure->parts); $i++) //Download all the attachments - Original
				for($i = 0; $i < 2; $i++) //Download only the first attachment. LTK 30Aug2022 2327 Hrs
				{
					$attachments[$i] = array(
						'is_attachment' => false,
						'filename' => '',
						'name' => '',
						'attachment' => ''
					);
				
					if($structure->parts[$i]->ifdparameters) 
					{
						foreach($structure->parts[$i]->dparameters as $object) 
						{
							if(strtolower($object->attribute) == 'filename') 
							{
								$attachments[$i]['is_attachment'] = true;
								$attachments[$i]['filename'] = $object->value;
							}
						}
					}
				
					if($structure->parts[$i]->ifparameters) 
					{
						foreach($structure->parts[$i]->parameters as $object) 
						{
							if(strtolower($object->attribute) == 'name') 
							{
								$attachments[$i]['is_attachment'] = true;
								$attachments[$i]['name'] = $object->value;
							}
						}
					}
				
					if($attachments[$i]['is_attachment']) 
					{
						$attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);
						
						if($structure->parts[$i]->encoding == 3) /* 3 = BASE64 encoding */
						{ 
							$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
						}
						elseif($structure->parts[$i]->encoding == 4) /* 4 = QUOTED-PRINTABLE encoding */
						{ 
							$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
						}
					}
				}
			}
			
			foreach($attachments as $attachment) /* iterate through each attachment and save it */
			{
				if($attachment['is_attachment'] == 1)
				{
					$filename = $attachment['name'];

					if(empty($filename)) $filename = $attachment['filename'];
					
					if(empty($filename)) $filename = time() . ".dat";
					
					/* prefix the email number to the filename in case two emails
					 * have the attachment with the same file name.
					 */
					//$fp = fopen("./" . $email_number . "-" . $filename, "w+");
					$fp = fopen("./" . $filename, "w+");
					fwrite($fp, $attachment['attachment']);
					fclose($fp);
				}
			}
			if($count++ >= $max_emails) break;
		}
	} 
	imap_close($inbox); /* close the connection */
	//echo "<tr><td colspan='8'>File Name = $filename; Date = $mailDate</td></tr>";
	return array($filename, $mailDate);
}
?>