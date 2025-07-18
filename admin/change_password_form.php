<script src="../dojo/dojo.js" data-dojo-config="isDebug: true, async: true, parseOnLoad: true"></script>
<script>
require(["dojo/dom", 
"dojo/request",
"dojo/domReady!"]
, function(dom, request){
	
	userSettingsUpdate = function()
	{
		//alert("We here?");
		var pwd = dom.byId("password").value;
		var pwdc = dom.byId("passwordc").value;
		var pwdCheck = dom.byId("passwordcheck").value;
		var email = dom.byId("email").value;
		
		console.log("new pwd :-): "+pwd);
		
		request.post("change_password_form.php",{
		//handleAs: "json",
		data: {
			password: pwd,
			passwordc: pwdc,
			passwordcheck: pwdCheck,
			email: email
		}						
		}).then(function(data) 
		{
			dom.byId("main").innerHTML = data;
		});
	}

	homePage = function()
	{
		window.location.href = "admin/logout.php";
	}

});
</script>
<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Prevent the user visiting the logged in page if he is not logged in
if(!isUserLoggedIn()) { header("Location: index.php"); die(); }

if(!empty($_POST))
{
	$errors = array();
	$successes = array();
	$password = $_POST["password"];
	$password_new = $_POST["passwordc"];
	$password_confirm = $_POST["passwordcheck"];
	
	$errors = array();
	$email = $_POST["email"];
	
	//Perform some validation
	//Feel free to edit / change as required
	
	//Confirm the hashes match before updating a users password
	$entered_pass = generateHash($password,$loggedInUser->hash_pw);
	
	if (trim($password) == ""){
		$errors[] = lang("ACCOUNT_SPECIFY_PASSWORD");
	}
	else if($entered_pass != $loggedInUser->hash_pw)
	{
		//No match
		$errors[] = lang("ACCOUNT_PASSWORD_INVALID");
	}	
	if($email != $loggedInUser->email)
	{
		if(trim($email) == "")
		{
			$errors[] = lang("ACCOUNT_SPECIFY_EMAIL");
		}
		else if(!isValidEmail($email))
		{
			$errors[] = lang("ACCOUNT_INVALID_EMAIL");
		}
		else if(emailExists($email))
		{
			$errors[] = lang("ACCOUNT_EMAIL_IN_USE", array($email));	
		}
		
		//End data validation
		if(count($errors) == 0)
		{
			$loggedInUser->updateEmail($email);
			$successes[] = lang("ACCOUNT_EMAIL_UPDATED");
		}
	}
	
	if ($password_new != "" OR $password_confirm != "")
	{
		if(trim($password_new) == "")
		{
			$errors[] = lang("ACCOUNT_SPECIFY_NEW_PASSWORD");
		}
		else if(trim($password_confirm) == "")
		{
			$errors[] = lang("ACCOUNT_SPECIFY_CONFIRM_PASSWORD");
		}
		else if(minMaxRange(6,50,$password_new))
		{	
			$errors[] = lang("ACCOUNT_NEW_PASSWORD_LENGTH",array(6,50));
		}
		else if($password_new != $password_confirm)
		{
			$errors[] = lang("ACCOUNT_PASS_MISMATCH");
		}
		
		//End data validation
		if(count($errors) == 0)
		{
			//Also prevent updating if someone attempts to update with the same password
			$entered_pass_new = generateHash($password_new,$loggedInUser->hash_pw);
			
			if($entered_pass_new == $loggedInUser->hash_pw)
			{
				//Don't update, this fool is trying to update with the same password Â¬Â¬
				$errors[] = lang("ACCOUNT_PASSWORD_NOTHING_TO_UPDATE");
			}
			else
			{
				//This function will create the new hash and update the hash_pw property.
				$loggedInUser->updatePassword($password_new);
				$successes[] = lang("ACCOUNT_PASSWORD_UPDATED");
			}
		}
	}
	if(count($errors) == 0 AND count($successes) == 0){
		$errors[] = lang("NOTHING_TO_UPDATE");
	}
}

//echo resultBlock($errors,$successes);


?>
<div id="main">
<?php
 echo "
<table width='330px' height='330px' align='center' style='background-color:#E0E0E0; border-radius:15px; -moz-border-radius:15px; -webkit-border-radius:15px;'>
<tr><td><strong>".resultBlock($errors,$successes)."</strong></td></tr>
<tr>
	<td colspan='2' align='center'><img src='../images/coat_of_arms_small.png'/></td>
</tr>
<tr><td colspan='2' align='center'><h3>This seems to be your first login<br>Please change password below</h3></td></tr>
<tr><td>Current Password:</td><td><input type='password' name='password' id='password'/></td></tr>
<tr><td>Email Address:</td><td><input type='text' name='email' id='email' value='".$loggedInUser->email."' /></td></tr>
<tr><td>New Password:</td><td><input type='password' name='passwordc' id='passwordc' /></td></tr>
<tr><td>Confirm Password:</td><td><input type='password' name='passwordcheck' id='passwordcheck' /></td></tr>
<tr><td>&nbsp;</td><td>
<input type='submit' onclick='userSettingsUpdate()' value='Update'/>
<input type='button' name='home' value='Home' onclick='homePage()'/>
</td></tr>
</table>";
?>
</div>
