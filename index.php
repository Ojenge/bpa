<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once("admin/models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
//require_once("models/header.php");
//header("Cache-Control: no-cache, must-revalidate");
//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
//Prevent the user visiting the logging in page if he/she is already logged in
if(isUserLoggedIn()) {
//echo 'user logged in';
//header("Location: bpa.php");
header("Location: bpa.php");
die();
}
if(!empty($_GET))//Coming through mail update link - auto login user
{
	include_once("mail/functions.php");
	$userId = $_GET["jina"];
	$userId = cryptString($userId, "d");
	$substr = substr($userId, 0, 3);
	$id = str_replace($substr, "", $userId);
	//mysqli_query("");
	$userDetails = fetchUserDetails(NULL, NULL, $id);
	//Construct a new logged in user object
	//Transfer some db data to the session object
	$loggedInUser = new loggedInUser();
	echo $loggedInUser->email = $userDetails["email"];
	$loggedInUser->user_id = $userDetails["id"];
	$loggedInUser->hash_pw = $userDetails["password"];
	$loggedInUser->title = $userDetails["title"];
	$loggedInUser->displayname = $userDetails["display_name"];
	$loggedInUser->username = $userDetails["user_name"];
	$loggedInUser->ministry = $userDetails["title"];
	//Update last sign in
	$loggedInUser->updateLastSignIn();
	$_SESSION["userCakeUser"] = $loggedInUser;

	//Redirect to user account page
	header("Location: bpa.php?update");
}
//Forms posted
if(!empty($_POST))
{
	$errors = array();
	$username = sanitize(trim($_POST["username"]));
	$password = trim($_POST["password"]);

	//Perform some validation
	//Feel free to edit / change as required
	if($username == "")
	{
		$errors[] = lang("ACCOUNT_SPECIFY_USERNAME");
	}
	if($password == "")
	{
		$errors[] = lang("ACCOUNT_SPECIFY_PASSWORD");
	}

	if(count($errors) == 0)
	{
		//A security note here, never tell the user which credential was incorrect
		if(!usernameExists($username))
		{
			$errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID");
			//$errors[] = "The username $username does not exist";
		}
		else
		{
			$userdetails = fetchUserDetails($username);
			//See if the user's account is activated
			if($userdetails["active"]==0)
			{
				$errors[] = lang("ACCOUNT_INACTIVE");
			}
			else
			{
				//Hash the password and use the salt from the database to compare the password.
				$entered_pass = generateHash($password,$userdetails["password"]);

				if($entered_pass != $userdetails["password"])
				{
					//Again, we know the password is at fault here, but lets not give away the combination incase of someone bruteforcing
					$errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID");
				}
				else
				{
					//Passwords match! we're good to go'

					//Construct a new logged in user object
					//Transfer some db data to the session object
					$loggedInUser = new loggedInUser();
					$loggedInUser->email = $userdetails["email"];
					$loggedInUser->user_id = $userdetails["id"];
					$loggedInUser->hash_pw = $userdetails["password"];
					$loggedInUser->title = $userdetails["title"];
					$loggedInUser->displayname = $userdetails["display_name"];
					$loggedInUser->username = $userdetails["user_name"];
					$loggedInUser->ministry = $userdetails["title"];
					
					//$loggedInUser->last_login = $userdetails["last_sign_in_stamp"];
					//file_put_contents("lastLogin.txt", "\t\n Last login = ".$userdetails["last_sign_in_stamp"].", department: ".$userdetails["agency"],FILE_APPEND);
					if($userdetails["last_sign_in_stamp"] == 0 && $userdetails["title"] == "Regional Commissioner")
					{
						$loggedInUser->updateLastSignIn();
						$_SESSION["userCakeUser"] = $loggedInUser;
						//force change of password
						header("Location: change_password_form.php");
					}
					else
					{
						//Update last sign in
						$loggedInUser->updateLastSignIn();
						$_SESSION["userCakeUser"] = $loggedInUser;
	
						//Redirect to user account page
						header("Location: bpa.php");
					}
					ob_flush();
					die();
				}
			}
		}
	}
}
//require_once("models/header.php");
?>
<!DOCTYPE html><head>
<meta http-equiv="Cache-control" content="no-cache">
<meta http-equiv="Expires" content="-1">
<title>Accent Analytics</title>
<link rel="stylesheet" href="https://accent-analytics.com/dijit/themes/soria/soria.css">
<link rel="stylesheet" href="css/style.css" media="screen">
</head>

<style>
.dijitDialogCloseIcon { display:none }
</style>

<script src="https://accent-analytics.com/dojo/dojo.js" data-dojo-config="isDebug: true, async: true, parseOnLoad: true"></script>
        <script>
            require(["dojo/ready", 
			"dojo/aspect",
			"dijit/Dialog", 
			"dijit/form/ComboBox", "dijit/Menu", "dijit/MenuItem", "dojox/form/PasswordValidator","dijit/form/ValidationTextBox","dijit/form/Form",'dijit/form/TextBox',"dijit/form/SimpleTextarea","dijit/form/Button","dojo/domReady!"]
            , function(ready, aspect){
                ready(function(){
                    console.debug("Ready!");
                    dialogOne.show();
                });

				userSettingsUpdate = function()
				{
					//alert("We here?");
					var pwd = dojo.byId("password").value;
					var pwdc = dojo.byId("passwordc").value;
					var pwdCheck = dojo.byId("passwordcheck").value;
					var email = dojo.byId("email").value;
					
					//alert("first move :-): "+email);
					
					request.post("user_settings.php",{
					//handleAs: "json",
					data: {
						password: pwd,
						passwordc: pwdc,
						passwordcheck: pwdCheck,
						email: email
					}						
					}).then(function(data) 
					{
						dojo.byId("main").innerHTML = data;
					});
				}

            });

        </script>

<body class='soria'>
<div id="loginPage">
    <div data-dojo-id ="dialogOne" data-dojo-type = "dijit.Dialog" closeable="false" title = "Accent Analytics" style="display: none; text-align:center; background-color:#4a2d1d">
        <!--<div id='regbox'  style="background-color:#4a2d1d; margin-top:0px;">-->
        <div id='regbox'>
<form name="loginForm" id="loginId" action='<?php $_SERVER['PHP_SELF'] ?>' method='post'>
<!--<table bgcolor="#4a2d1d" style="margin-top:0px;">-->
<table>
<tr>
	<td colspan="2" align="center">
	    <table>
	    <tr>
	    <td valign="middle" style="margin-left:0; margin-right:0"><img src='images/BPALogo.png'/></td>
        <td valign="middle"><em>&nbsp;&nbsp;&nbsp;</em></td>
        <td valign="middle"><img src='images/kdic/kdic-logo-mini.png' style="margin-left:6px;"/></td>
	    </tr>
	    </table>	
	</td>
</tr>
<tr><td>&nbsp;</td></tr>

<tr>
<td>Username</td>
<td>
	<input id="userid" name="username" class="m-my-account-userid" maxlength="80" autocomplete="off" placeholder="Username" type="text">
</td>
</tr>
<tr>
<td>Password</td>
<td>
	<input id="passwd" name="password" class="m-my-account-password" maxlength="20" autocomplete="off" placeholder="Password" type="password">
</td>
</tr>
<tr>
<td></td>
<td>
	<button type="submit" name="submit" id="header-my-account-sign-in" class="analytics-click m-button-default"  title="Sign In">Sign In</button>
</td>
</tr>
<tr><td colspan="2"><a href="register.php">Register</a>&nbsp;|&nbsp;<a href="admin_forgot_password.php">Forgot Password</a></td></tr>
<tr><td height="12px"></td></tr>
<tr style="border-top:1px solid black;"><td style="border-top:1px solid #CCC; color:#999" colspan="2">Contact: admin@accent-analytics.com</td></tr>
</table>
</form>
            <?php echo resultBlock($errors,$successes); ?>
        </div>
    </div>
</div>
</body>
</html>