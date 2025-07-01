<?php
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
$userId = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : null);

//Check if selected user exists
if(!$userId || !userIdExists($userId)){
	header("Location: admin_users.php"); die();
}

$userdetails = fetchUserDetails(NULL, NULL, $userId); //Fetch user details

//Forms posted
if(!empty($_POST))
{
	// Initialize displayname for use in success messages
	$displayname = $userdetails['display_name'];

	// Debug logging - only if toReset exists
	if(isset($_POST['toReset']) && is_array($_POST['toReset'])) {
		file_put_contents("resetPwd.txt", "id => ".print_r($_POST['toReset'], true));
	}

	//Reset Password for selected account
	if(isset($_POST['toReset']) && !empty($_POST['toReset'])){
		// Debug logging for password reset
		file_put_contents("resetPwd.txt", "Reset requested for user ID: ".$userId);
		$resets = $_POST['toReset'];
		if ($reset_count = resetPassword($resets)) {
			$successes[] = lang("PASSWORD_RESET_SUCCESSFUL", array($reset_count));
		}
		else {
			$errors[] = lang("SQL_ERROR");
		}
	}

	//Delete selected account
	if(isset($_POST['toDelete']) && !empty($_POST['toDelete'])){
		$deletions = $_POST['toDelete'];
		if ($deletion_count = deleteUsers($deletions)) {
			$successes[] = lang("ACCOUNT_DELETIONS_SUCCESSFUL", array($deletion_count));
		}
		else {
			$errors[] = lang("SQL_ERROR");
		}
	}
	else
	{
		//Update display name
		if (isset($_POST['display']) && !empty($_POST['display']) && $userdetails['display_name'] != $_POST['display']){
			$new_displayname = trim($_POST['display']);

			//Validate display name
			if(displayNameExists($new_displayname))
			{
				$errors[] = lang("ACCOUNT_DISPLAYNAME_IN_USE",array($new_displayname));
			}
			elseif(minMaxRange(5,25,$new_displayname))
			{
				$errors[] = lang("ACCOUNT_DISPLAY_CHAR_LIMIT",array(5,25));
			}
			/*elseif(!ctype_alnum($new_displayname)){
				$errors[] = lang("ACCOUNT_DISPLAY_INVALID_CHARACTERS");
			}*/
			else {
				if (updateDisplayName($userId, $new_displayname)){
					$displayname = $new_displayname; // Update the displayname variable for success messages
					$successes[] = lang("ACCOUNT_DISPLAYNAME_UPDATED", array($displayname));
				}
				else {
					$errors[] = lang("SQL_ERROR");
				}
			}
		}
		
		//Activate account
		if(isset($_POST['activate']) && $_POST['activate'] == "activate"){
			if (setUserActive($userdetails['activation_token'])){
				$successes[] = lang("ACCOUNT_MANUALLY_ACTIVATED", array($displayname));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
		
		//Update email
		if (isset($_POST['email']) && !empty($_POST['email']) && $userdetails['email'] != $_POST['email']){
			$email = trim($_POST["email"]);
			
			//Validate email
			if(!isValidEmail($email))
			{
				$errors[] = lang("ACCOUNT_INVALID_EMAIL");
			}
			elseif(emailExists($email))
			{
				$errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));
			}
			else {
				if (updateEmail($userId, $email)){
					$successes[] = lang("ACCOUNT_EMAIL_UPDATED");
				}
				else {
					$errors[] = lang("SQL_ERROR");
				}
			}
		}
		
		//Update title
		if (isset($_POST['title']) && !empty($_POST['title']) && $userdetails['title'] != $_POST['title']){
			$title = trim($_POST['title']);
			
			//Validate title
			if(minMaxRange(1,50,$title))
			{
				$errors[] = lang("ACCOUNT_TITLE_CHAR_LIMIT",array(1,50));
			}
			else {
				if (updateTitle($userId, $title)){
					$successes[] = lang("ACCOUNT_TITLE_UPDATED", array ($displayname, $title));
				}
				else {
					$errors[] = lang("SQL_ERROR");
				}
			}
		}
		
		//Remove permission level
		if(isset($_POST['removePermission']) && !empty($_POST['removePermission'])){
			$remove = $_POST['removePermission'];
			if ($deletion_count = removePermission($remove, $userId)){
				$successes[] = lang("ACCOUNT_PERMISSION_REMOVED", array ($deletion_count));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
		
		if(isset($_POST['addPermission']) && !empty($_POST['addPermission'])){
			$add = $_POST['addPermission'];
			if ($addition_count = addPermission($add, $userId)){
				$successes[] = lang("ACCOUNT_PERMISSION_ADDED", array ($addition_count));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
		
		$userdetails = fetchUserDetails(NULL, NULL, $userId);
	}
}

$userPermission = fetchUserPermissions($userId);
$permissionData = fetchAllPermissions();

//require_once("models/header.php");

//echo "<div id='main'>";

echo resultBlock($errors,$successes);

echo "
<div class='border border-primary rounded-3' style='overflow:hidden;'><table class='table table-responsive table-bordered table-sm table-condensed'><tr><td valign='top' align='center'><h3>User Information</h3>
<div class='border border-primary rounded-3' style='overflow:hidden;'><table class='table table-responsive table-bordered table-sm table-condensed table-striped'>
<tr>
	<td>ID:</td>
	<td><div id='userId'>".$userdetails['id']."</div></td>
<tr>
	<td>Username:</td>
	<td>".$userdetails['user_name']."</td>
</tr>
<tr>
	<td>Display Name</td>
	<td><input type='text' name='display' id='display' value='".$userdetails['display_name']."'/></td>
</tr>
<tr>
	<td>Email</td>
	<td><input type='text' name='email' id='email' value='".$userdetails['email']."'/></td>
</tr>
<tr>
	<td>Active";
	//Display activation link, if account inactive
	if ($userdetails['active'] == '1') echo "</td><td>Yes</td>";
	else{
		echo "<td>No, Activate:<input type='checkbox' name='activate' id='activate' value='activate'></td>";
	}
	echo "
</tr>
<tr>
	<td>Title:</td>
	<td><input type='text' name='title' id='title' value='".$userdetails['title']."' /></td>
<tr>
	<td>Sign Up:</td>
	<td>".date("j M, Y", $userdetails['sign_up_stamp'])."</td>
</tr>
<tr>
	<td>Last Sign In:</td>
	<td>";
	//Last sign in, interpretation
	if ($userdetails['last_sign_in_stamp'] == '0'){
		echo "Never";	
	}
	else {
		echo date("j M, Y", $userdetails['last_sign_in_stamp']);
	}
	echo "
	</td>
</tr>
<tr>
	<td>Reset Password:</td>
	<td><input type='checkbox' name='toReset[".$userdetails['id']."]' id='toReset[".$userdetails['id']."]' value='".$userdetails['id']."'></td>
</tr>
<tr>
	<td>Delete:</td>
	<td><input type='checkbox' name='toDelete[".$userdetails['id']."]' id='toDelete[".$userdetails['id']."]' value='".$userdetails['id']."'></td>
</tr>
</table></div>
</td>
<td valign='top' align='center'>
<h3>Permission Membership</h3>
<div id='regbox'>

<table class='table table-responsive table-sm table-condensed'>
<tr><td>
	<div class='border border-primary rounded-3' style='overflow:hidden;'><table class='table table-responsive table-bordered table-sm table-condensed table-striped'><tr class='table-success'><th><em>Activated Permissions. Select and Update to Remove</em></th></tr>";
//List of permission levels user is apart of
$permissionType = NULL;
$permissionCount = 0;
$permissionTitle = NULL;
foreach ($permissionData as $v1) {
	if(isset($userPermission[$v1['id']]))
	{
		if($permissionCount == 0)
		{
			$permissionType = !empty($v1['orgId']) ? substr($v1['orgId'], 0, 3) : '';
			echo "<tr class='table-primary'><th>System Permissions</th></tr>";
		}
		$currentOrgType = !empty($v1['orgId']) ? substr($v1['orgId'], 0, 3) : '';
		if($permissionType == $currentOrgType)
		{
			//echo "Same";
		}
		else
		{
			switch($currentOrgType)
			{
				case "ind":
				{
					$permissionTitle = "Individual Scorecards";
					break;
				}
				case "org":
				{
					$permissionTitle = "Organizations/Departments";
					break;
				}
				case "roo":
				{
					$permissionTitle = "Scorecard Tree Root <font color='red'>(a must)</font>";
					break;
				}
				case "fun":
				{
					$permissionTitle = "Module Permissions";
					break;
				}
				default:
				{
					$permissionTitle = "System Permissions";
					break;
				}
			}
			echo "<tr class='table-primary'><th>".$permissionTitle."</th></tr>";
			$permissionType = $currentOrgType;
		}
		echo "<tr><td><input type='checkbox' name='removePermission[".$v1['id']."]' id='removePermission[".$v1['id']."]' value='".$v1['id']."'> ".$v1['name']."</td></tr>";
		$permissionCount++;
	}
}
$permissionType = NULL;
$permissionCount = 0;
$permissionTitle = NULL;
//List of permission levels user is not apart of
echo "</table></div></td><td><div class='border border-primary rounded-3' style='overflow:hidden;'><table class='table table-responsive table-bordered table-sm table-condensed table-striped'><tr class='table-danger'><th><em>Inactive Permissions. Select and Update to Add</em></th></tr>";
foreach ($permissionData as $v1) {
	if(!isset($userPermission[$v1['id']]))
	{
		//echo '<br>permissionType = '. 	 $permissionCount;
		if($permissionCount == 0)
		{
			$permissionType = !empty($v1['orgId']) ? substr($v1['orgId'], 0, 3) : '';
			echo "<tr><th class='table-primary'>System Permissions</th></tr>";
		}
		$currentOrgType = !empty($v1['orgId']) ? substr($v1['orgId'], 0, 3) : '';
		if($permissionType != $currentOrgType)
		{
			switch($currentOrgType)
			{
				case "ind":
				{
					$permissionTitle = "Individual Scorecards";
					break;
				}
				case "org":
				{
					$permissionTitle = "Organizations/Departments";
					break;
				}
				case "roo":
				{
					$permissionTitle = "Scorecard Tree Root<br><font color='red'>(a must)</font>";
					break;
				}
				case "fun":
				{
					$permissionTitle = "Module Permissions";
					break;
				}
				default:
				{
					$permissionTitle = "System Permissions";
					break;
				}
			}
			echo "<tr class='table-primary'><th>".$permissionTitle."</th></tr>";
			$permissionType = $currentOrgType;
		}
		echo "<tr><td><input type='checkbox' name='addPermission[".$v1['id']."]' id='addPermission[".$v1['id']."]' value='".$v1['id']."'> ".$v1['name']."</td></tr>";
		$permissionCount++;
	}
}
echo"
</table></div>
</td>
</tr>
</table></div>
<input type='submit' value='Update' onclick='updateUserInfo()' class='submit' />";
?>