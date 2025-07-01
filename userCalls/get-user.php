<?php
require_once("../admin/models/config.php");
//$userId = $_GET['id'];
$userId = 1;

//Check if selected user exists
if(!userIdExists($userId)){
	header("Location: admin/admin_users.php"); die();
}

//$userPermission = fetchUserPermissions($userId);
//$permissionData = fetchAllPermissions();

//echo $userPermission = json_encode($userPermission);
//echo $permissionData = json_encode($permissionData);

$userdetails = fetchUserDetails(NULL, NULL, $userId); //Fetch user details
$userdetails = json_encode($userdetails);
echo $userdetails;
//var_dump($userdetails);
?>