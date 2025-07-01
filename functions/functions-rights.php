<?php
//ini_set('display_errors',0);
include_once("../config/config_msqli.php");

//getUserList();
//$users = getUserIDs($userPermission, $role);
//$ids = join(',',$users);
//echo $ids;
//print_r(json_encode($users));
if(isset($_POST["functionToCall"]) && $_POST["functionToCall"] == "getUserList")
{
	require_once("../admin/models/config.php");
	$userPermission = fetchUserPermissions($loggedInUser->user_id);
	$role = "";
	getUserList($userPermission, $role);
}

function getUserList($userPermission, $role)
{
	foreach($userPermission as $id)
	{
		if($id["permission_id"] == "2") $role = "Administrator";
	}
	
	if($role == "Administrator")
	{
		$user_query = "SELECT DISTINCT(uc_users.user_id), uc_users.display_name
		FROM uc_users 
		WHERE uc_users.department <> 'Accent'";	
	}
	else
	{
		require_once("../models/config.php");
		$userId = $loggedInUser->user_id;
			
		$user_query="SELECT DISTINCT(uc_users.user_id), uc_users.display_name
		FROM uc_users, uc_user_permission_matches 
		WHERE uc_users.department <> 'Accent'
		AND uc_users.id = uc_user_permission_matches.user_id
		AND uc_user_permission_matches.user_id IN 
		(SELECT DISTINCT(uc_users.id)
		FROM `uc_user_permission_matches`, `uc_users`, uc_permissions 
		WHERE uc_user_permission_matches.user_id = '$userId' 
		AND uc_user_permission_matches.permission_id = uc_permissions.id
		AND uc_users.user_id = uc_permissions.orgId)";
	}
	
	$user_result = mysqli_query($GLOBALS["___mysqli_ston"], $user_query) or die("Could not query uc_users table");;
	
	$row_count = mysqli_num_rows($user_result) or die("Could not count rows");
	if ($row_count == NULL) exit;
	
	$count = 1;
	echo "{ \"identifier\": \"User\", \"label\": \"User\", \"items\": [";
	while($row = mysqli_fetch_assoc($user_result))
	{
		$data["id"] = $row["user_id"];
		$data["User"] = $row["display_name"];
		$data = json_encode($data);
		echo $data;
		if($count<$row_count) echo ", ";
		$data = NULL;
		$count++;
	}
	echo "]}";
}

function getUserIDs($userPermission, $role, $userId)
{	
	//file_put_contents("error.txt", "\n1 User => $userId ", FILE_APPEND);
	
	foreach($userPermission as $id)
	{
		if($id["permission_id"] == "2") $role = "Administrator";
	}
	
	if($role == "Administrator")
	{ 
		$user_query = "SELECT DISTINCT(uc_users.user_id), uc_users.display_name
		FROM uc_users 
		WHERE uc_users.department <> 'Accent'";
	}
	else
	{
		//file_put_contents("userId.txt", "User Id: " . $userId);
			 
		/*$user_query="SELECT DISTINCT(uc_users.user_id), uc_users.display_name
		FROM uc_users, uc_user_permission_matches 
		WHERE uc_users.department <> 'Accent'
		AND uc_users.id = uc_user_permission_matches.user_id
		AND uc_user_permission_matches.user_id IN 
		(SELECT DISTINCT(uc_users.id)
		FROM `uc_user_permission_matches`, `uc_users`, uc_permissions 
		WHERE uc_user_permission_matches.user_id = 'ind2' 
		AND uc_user_permission_matches.permission_id = uc_permissions.id
		AND uc_users.user_id = uc_permissions.orgId)";*///Not sure what in the world I was doing with this query. LTK 14 May 2021 1516 Hrs
		
		$user_query="SELECT user_id, display_name FROM uc_users 
		WHERE uc_users.department <> 'Accent'
		AND user_id IN (SELECT DISTINCT(uc_permissions.orgId)
		FROM `uc_user_permission_matches`, uc_permissions, uc_users
		WHERE uc_user_permission_matches.user_id = '20' 
		AND uc_user_permission_matches.permission_id = uc_permissions.id)";
	}
	
	$user_result = mysqli_query($GLOBALS["___mysqli_ston"], $user_query) or file_put_contents("error.txt", "\n2 Could not query uc_users table for $userId with error: ".mysqli_error($GLOBALS["___mysqli_ston"]), FILE_APPEND);

	$row_count = mysqli_num_rows($user_result) or file_put_contents("error.txt", "\n3. Could not count rows; Error => ".mysqli_error($GLOBALS["___mysqli_ston"]), FILE_APPEND);
	if ($row_count == NULL) exit;
	
	$count = 0; $ids = array();
	while($row = mysqli_fetch_assoc($user_result))
	{
		$ids[$count] = $row["user_id"];
		//$ids[$count]["User"] = $row["user_id"];//associative array
		$count++;
	}
	return $ids;
}
?>