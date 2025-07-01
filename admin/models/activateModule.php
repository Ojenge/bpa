<?php
include_once("db-settings.php");
//Update module status

$id = $_POST['id'];
$status = $_POST['status'];

if($status == "Active") $status = "Inactive";
else $status = "Active";

global $mysqli;
$db_table_prefix = "uc_";
//$status = "Inactive";
$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."permissions
	SET 
	status = ?
	WHERE
	id = ?");
$stmt->bind_param("si", $status, $id);
$result = $stmt->execute();
$stmt->close();	
//return $result;	

?>