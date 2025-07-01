<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
//if (!securePage($_SERVER['PHP_SELF'])){die();}

//Prevent the user visiting the logged in page if he is not logged in
if(!isUserLoggedIn()) { header("Location: index.php"); die(); }

if(!empty($_POST["password"]))
{
	//echo "Hapa ni kwa user password";
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
		else if(minMaxRange(8,50,$password_new))
		{	
			$errors[] = lang("ACCOUNT_NEW_PASSWORD_LENGTH",array(8,50));
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

if(!empty($_POST["displayname"]))
{
	$email = $_POST["email"];
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
	$displayName = $_POST["displayname"];
	$department = $_POST["department"];
	$reportsTo = $_POST["reportsTo"];
	$user_id = $loggedInUser->user_id;
	
	mysqli_query($mysqli, "UPDATE uc_users SET display_name = '$displayName', reportsTo = '$reportsTo', department = '$department' WHERE id = '$user_id'");
	echo "<br>User Details Have Been Updated";
}

echo resultBlock($errors,$successes);

?>
<ul class="nav nav-tabs"  role="tablist">
  <li class="nav-item" role="presentation"><button class="nav-link active" id="tab-one" data-bs-toggle="tab" data-bs-target="#tabOne" type="button" role="tab" aria-controls="tabOne" aria-selected="true">Password</button></li>
  <li class="nav-item" role="presentation"><button class="nav-link" id="tab-two" data-bs-toggle="tab" data-bs-target="#tabTwo" type="button" role="tab" aria-controls="tabTwo" aria-selected="false">User Details</button></li>
</ul>

<div class='tab-content'>

<div class='tab-pane fade show active' id='tabOne' role='tabpanel' aria-labelledby='tab-one'>
	<table>
	<tr><td>Current Password:</td><td><input type='password' name='password' id='password'/></td></tr>
	<tr><td>Email Address:</td><td><input type='text' name='email' id='email' value=<?php echo $loggedInUser->email; ?> /></td></tr>
	<tr><td>New Password:</td><td><input type='password' name='passwordc' id='passwordc' /></td></tr>
	<tr><td>Confirm Password:</td><td><input type='password' name='passwordcheck' id='passwordcheck' /></td></tr>
	<tr><td>&nbsp;</td><td><input type='submit' onclick='userPasswordUpdate()' value='Update'/></td></tr>
	</table>
</div>

<div class='tab-pane fade' id='tabTwo' role='tabpanel' aria-labelledby='tab-two'>
<?php 
$user_id = $loggedInUser->user_id;
$userDetails = mysqli_query($mysqli, "SELECT display_name, email, department, reportsTo, title from uc_users WHERE id = $user_id"); 
while($row = mysqli_fetch_assoc($userDetails))
{
	$display_name = $row["display_name"];
	$title = $row["title"];
	$email = $row["email"];
	$userDepartmentId = $row["department"];
	$userDepartment = mysqli_query($mysqli, "SELECT name FROM organization WHERE id = '$userDepartmentId'");
	$userDepartment = mysqli_fetch_assoc($userDepartment);
	$userDepartment = $userDepartment["name"];

	$reportsToId = $row["reportsTo"];
	$reportsToQuery = mysqli_query($mysqli, "SELECT display_name FROM uc_users WHERE user_id = '$reportsToId'");
	$reportsToResult = mysqli_fetch_assoc($reportsToQuery);
	if(mysqli_num_rows($reportsToQuery) > 0) $reportsTo = $reportsToResult["display_name"];
	else $reportsTo = "";
}
?>
<br><br>
	<table>
	<tr><td>Display Name:</td><td><input type='text' name='displayname' id='displayname' value='<?php echo $display_name; ?>'/></td></tr>
	<tr>
		<td>Reports To:</td>
		<td>
			<select name="reportsTo" id="reportsTo">
				<?php 
				$userNames = mysqli_query($mysqli, "SELECT id, user_id, display_name FROM uc_users");
				while ($row = mysqli_fetch_assoc($userNames))
				{
					$userName = $row["display_name"];
					//$id = $row["id"];
					$user_id = $row["user_id"];
					if($reportsToId == $user_id)
					echo "<option selected='selected' value='$user_id'>$userName</option>";
					else
					echo "<option value='$user_id'>$userName</option>";
				}
				?>
			</select>
		</td>
	</tr>
	<tr><td>Email:</td><td><input type='text' name='email' id='email' value='<?php echo $email; ?>'/></td></tr>
	<tr><td>Title:</td><td><input type='text' name='title' id='title' value='<?php echo $title; ?>'/></td></tr>
	<tr><td>Department:</td>
		<td>
			<select name="department" id="department">
				<?php 
				$departmentNames = mysqli_query($mysqli, "SELECT id, name FROM organization");
				while ($row = mysqli_fetch_assoc($departmentNames))
				{
					$departmentName = $row["name"];
					$departmentId = $row["id"];
					if($userDepartmentId == $departmentId)
					echo "<option selected='selected' value='$departmentId'>$departmentName</option>";
					else
					echo "<option value='$departmentId'>$departmentName</option>";
				}
				?>
			</select>
		</td>
	</tr>
	<tr><td>&nbsp;</td><td><input type='submit' onclick='userSettingsUpdate()' value='Update'/></td></tr>
	</table>
</div>

</div>

