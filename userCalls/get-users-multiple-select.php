<?php
ini_set('display_errors',1);
include_once("../config/config_mysqli.php");
/*
require_once("models/config.php");

$userPermission = fetchUserPermissions($loggedInUser->user_id);
$role = "";

foreach($userPermission as $id)
{
	if($id["permission_id"] == "2") $role = "Administrator";
}

if($role == "Administrator")
{
	$user_query = "SELECT DISTINCT(uc_users.user_id), uc_users.display_name
	FROM uc_users 
	WHERE uc_users.department <> 'org0'";	
}
else
{
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
}*/

$user_query = "SELECT DISTINCT(uc_users.user_id), uc_users.display_name
	FROM uc_users 
	WHERE uc_users.department <> 'org0'";	

$user_result=mysqli_query($connect, $user_query) or die("Could not query uc_users table");;

$row_count = mysqli_num_rows($user_result) or die("Could not count rows");
if ($row_count == null) exit;

//$data["Team"] = NULL;
$count = 1;
echo "{ \"users\": [";
while($row = mysqli_fetch_assoc($user_result))
{
	$data["id"] = $row["user_id"];
	$data["user"] = $row["display_name"];
	//if ($count == 1) $data["selected"] = "true";
	//else $data["User"] = $data["User"].", ".$row3["firstName"]." ".$row3["lastName"];
	$data = json_encode($data);
	echo $data;
	if($count<$row_count) echo ", ";
	$data = NULL;
	$count++;
}
echo "]}";

//echo ',"selected":["ind2","ind7"]}'
?>