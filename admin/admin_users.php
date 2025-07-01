<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Forms posted
if(!empty($_POST))
{
	$deletions = $_POST['toDelete'];
	if ($deletion_count = deleteUsers($deletions)){
		$successes[] = lang("ACCOUNT_DELETIONS_SUCCESSFUL", array($deletion_count));
	}
	else {
		$errors[] = lang("SQL_ERROR");
	}
}


$userData = fetchAllUsers(); //Fetch information for all users

require_once("models/header.php");

echo "
</div>
<div id='main'>";

echo resultBlock($errors,$successes);

echo "
<table class='table table-hover table-condensed table-bordered table-responsive'>
<tr>
<th></th><th>Delete</th><th>Username</th><th>Display Name</th><th>Title/MDA</th><th>Sign Up</th><th>Active</th><th>Last Sign In</th>
</tr>";
date_default_timezone_set('Africa/Nairobi');
$count = 1;
//Cycle through users
foreach ($userData as $v1) {
	echo "
	<tr>
	<td>".$count."</td>
	<td><input type='checkbox' name='toDelete[".$v1['id']."]' id='toDelete[".$v1['id']."]' value='".$v1['id']."'></td>
	<td><a href='#' onClick='adminUserInfo(".$v1['id'].")'> ".$v1['user_name']."</a></td>
	<td>".$v1['display_name']."</td>
	<td>";
	
	$title = $v1['title'];
	include_once("../config/config_mysqli.php");

	// Check if title is numeric (ministry ID) and ministry table exists
	if(is_numeric($title)) {
		// Check if ministry table exists first
		$table_check = @mysqli_query($connect, "SHOW TABLES LIKE 'ministry'");
		if($table_check && mysqli_num_rows($table_check) > 0) {
			$ministry = @mysqli_query($connect, "SELECT name FROM ministry WHERE id = '$title'");
			if($ministry) {
				$ministry_data = @mysqli_fetch_assoc($ministry);
				if($ministry_data && !empty($ministry_data["name"])) {
					echo $ministry_data["name"];
				} else {
					echo "Ministry ID: " . $title;
				}
			} else {
				echo "Ministry ID: " . $title;
			}
		} else {
			// Ministry table doesn't exist, show the ID
			echo "Ministry ID: " . $title;
		}
	} else {
		// Title is not numeric, display as is
		echo $v1['title'];
	}
	
	echo "</td>
	<td>".date("j M, Y H:i:s", $v1['sign_up_stamp'])."</td>
	<td>";
	
	if($v1['active'] == 1) echo "Yes"; else echo "No";
	
	echo "</td>
	<td>
	";
	
	//Interprety last login
	if ($v1['last_sign_in_stamp'] == '0'){
		echo "Never";	
	}
	else {
		//date_default_timezone_set('Africa/Nairobi');
		echo date("j M, Y H:i:s", $v1['last_sign_in_stamp']);
	}
	echo "
	</td>
	</tr>";
	$count++;
}

echo "
</table>
<input type='submit' name='Submit' onclick='adminUserDelete()' value='Delete' />
</div>";
?>
