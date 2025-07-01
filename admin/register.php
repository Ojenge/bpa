	<link rel="stylesheet" href="https://accent-analytics.com/dijit/themes/soria/soria.css" media="all">
<link rel="stylesheet" href="https://accent-analytics.com/bootstrap/4.5.0/dist/css/bootstrap.css" media="all">

<script>


homePage = function()
{
	window.location.href = "index.php";
}
</script>
<script src="https://accent-analytics.com/jquery/3.1.0/jquery.js"></script>
<script src="https://accent-analytics.com/bootstrap/5.0.0/dist/js/bootstrap.js"></script>
<script type="text/javascript" src="https://accent-analytics.com/dojo/dojo.js" data-dojo-config="async: true, parseOnLoad:true"></script>

<script>
require([
"dojo/request",
"dojo/dom",
"dojo/ready",
"dojo/domReady!"
],
function(request, dom, ready){
departmentStaff = function(filter)
{
	if(filter == "All" || filter == null || filter == "undefined") filter = "All";
	else filter = filter.value

	//console.log("Filter = " + filter);

	request.post("get-department-staff.php",{
	data: {
		filter: filter
		}
		}).then(function(users)
		{
			var ngoja = setTimeout(function()
			{
				var supervisorElement = dom.byId("supervisorSelect");
				if(supervisorElement) {
					supervisorElement.innerHTML = users;
				}
			},30);
		});
}

// Use Dojo's ready function to wait for DOM
ready(function() {
	// Add a small delay to ensure all elements are rendered
	setTimeout(function() {
		departmentStaff("All");
	}, 100);
});
})
</script>
<html>
<body>
<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/
error_reporting(0);
require_once("../config/config_mysqli.php");
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Prevent the user visiting the logged in page if he/she is already logged in
if(isUserLoggedIn()) { header("Location: admin/logout.php"); die(); }
$displayUsername = "";
$displayname = "";
$title = "";
$confirm_pass = "";
$email = "";

//Forms posted

if(!empty($_POST))
{
	//file_put_contents("registerErrors.txt", "\nreports to => ".$_POST["reportsTo"], FILE_APPEND);
	$displayUsername = trim($_POST["username"]);
	$errors = array();
	$email = trim($_POST["email"]);
	$username = trim($_POST["username"]);
	$displayname = trim($_POST["displayname"]);
	$reportsTo = trim($_POST["reportsTo"]);
	$title = trim($_POST["title"]);
	$department = trim($_POST["department"]);
	$password = trim($_POST["password"]);
	$confirm_pass = trim($_POST["passwordc"]);
	$captcha = md5($_POST["captcha"]);
	$savedCaptcha = file_get_contents("models/captcha.txt");
	
	//if ($captcha != $_SESSION['captcha'])
	if ($captcha != $savedCaptcha)
	{
		$errors[] = lang("CAPTCHA_FAIL");
	}
	if(minMaxRange(3,33,$username))
	{
		$errors[] = lang("ACCOUNT_USER_CHAR_LIMIT",array(3,33));
	}
	//if($department == '')
	//{
	//	$errors[] = lang("ACCOUNT_SPECIFY_DEPARTMENT");
	//}
	//if(!ctype_alnum($username)){
	//	$errors[] = lang("ACCOUNT_USER_INVALID_CHARACTERS");
	//}
	//if(minMaxRange(5,25,$displayname))
	//{
	//	$errors[] = lang("ACCOUNT_DISPLAY_CHAR_LIMIT",array(5,25));
	//}
	//if(!ctype_alnum($displayname)){
	//	$errors[] = lang("ACCOUNT_DISPLAY_INVALID_CHARACTERS");
	//}
	if(minMaxRange(6,50,$password) && minMaxRange(6,50,$confirm_pass))
	{
		$errors[] = lang("ACCOUNT_PASS_CHAR_LIMIT",array(6,50));
	}
	else if($password != $confirm_pass)
	{
		$errors[] = lang("ACCOUNT_PASS_MISMATCH");
	}
	if(!isValidEmail($email))
	{
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}
	//End data validation
	if(count($errors) == 0)
	{	
		//Construct a user object
		$user = new User($username,$displayname,$reportsTo,$password,$email,$title,$department);
		
		//Checking this flag tells us whether there were any errors such as possible data duplication occured
		if(!$user->status)
		{
			if($user->username_taken) $errors[] = lang("ACCOUNT_USERNAME_IN_USE",array($username));
			//if($user->displayname_taken) $errors[] = lang("ACCOUNT_DISPLAYNAME_IN_USE",array($displayname));
			if($user->email_taken) 	  $errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));		
		}
		else
		{
			//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
			if(!$user->userCakeAddUser())
			{
				if($user->mail_failure) $errors[] = lang("MAIL_ERROR");
				if($user->sql_failure)  $errors[] = lang("SQL_ERROR");
			}
			
			$to = 'lkyonze@gmail.com';
			$subject = 'New Accent User';
			$headers = "From: " . strip_tags("admin@accent-analytics.com") . "\r\n";
			$headers .= "Reply-To: ". strip_tags("admin@accent-analytics.com") . "\r\n";
			//$headers .= "CC: lkyonze@gmail.com\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			$message = "$username user created with email $email from $department.";
			mail($to, $subject, $message, $headers);
		}
	}
	if(count($errors) == 0) {
		$successes[] = $user->success;
	}
}

require_once("models/header.php");

echo "<strong>".resultBlock($errors,$successes)."</strong>";

echo "<div class='border border-success rounded' style='overflow:hidden;'><table style='width:27%; margin-left: auto; margin-right: auto;' class='table table-responsive table-bordered table-sm table-condensed table-striped'>"
."<tr><td colspan='2' align='center'><img src='../images/BPALogo.png'/></td></tr>"
."<tr><td colspan='2' align='center'><h3>User Registration</h3></td></tr>"
."<form name='newUser' action='".$_SERVER['PHP_SELF']."' method='post'>"
."<tr><td><label>User Name:</label></td><td><input type='text' name='username' value='$displayUsername'/></td></tr>"
."<tr><td><label>Full Name:</label></td><td><input type='text' name='displayname'  value='$displayname'/></td></tr>"
."<tr><td><label>Title:</label></td><td><input type='text' name='title' value='$title' /></td></tr>";

echo '<tr><td><label>Department:</label></td><td><select name="department" onchange="departmentStaff(this)">';
$listDepartments = mysqli_query($connect, "SELECT id, name FROM organization WHERE id <> 'org0'");
while($row = mysqli_fetch_array($listDepartments))
{
	echo '<option value="'.$row["id"].'">'.$row["name"].'</option>';
}
echo"</select></td></tr>";
echo '<tr><td><label>Reports To:</label></td><td>'
.'<select name="reportsTo" id="supervisorSelect">'
."</select>"
."</td></tr>";
?>
<tr><td>Profile Photo</td>
<td>
    <input type="file" name="profile_photo" accept="image/*" class="form-control">
    <small class="text-muted">Optional: Upload a profile photo (JPG, PNG, GIF)</small>
</td>
</tr>
<?php
echo "<tr><td><label>Password:</label></td><td><input type='password' name='password'/></td></tr>"
."<tr><td><label>Confirm Password:</label></td><td><input type='password' name='passwordc'/></td></tr>"
."<tr><td><label>Email:</label></td><td><input type='text' name='email' value='$email'/></td></tr>"
."<tr><td><label>Security Code:</label></td><td><img src='models/captcha.php'></td></tr>"
."<tr><td><label>Enter Security Code:</label></td><td><input name='captcha' type='text'></td></tr>"
."<tr><td><label>&nbsp;</td><td><input type='submit' value='Register'/><input type='button' name='home' value='Home' onclick='homePage()'/></td></tr>"

."</form>"
."</table><div>";

echo "<div id='bottom'></div>
</div>";
?>
</body>
</html>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-55687215-1', 'auto');
  ga('send', 'pageview');
</script>