<?php 
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
//require_once("models/header.php");
$userPermission = fetchUserPermissions($loggedInUser->user_id);
//var_dump($userPermission);
$view = "True";
foreach($userPermission as $id)
{
	if($id["permission_id"] == "2")
	$view = "False";
	else if($id["permission_id"] == "3")
	$view = "Application";
}
if(@$userPermission[1]["permission_id"] == "2" && $view == "True") $view = "False";
if(@$userPermission[1]["permission_id"] == "3" && $view == "True") $view = "Application";
echo $view;
?>