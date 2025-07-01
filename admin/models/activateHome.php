<?php
include_once("db-settings.php");
//Update module status

$id = $_POST['id'];
$homePage = "Yes";
$reset = "No";
global $mysqli;
$db_table_prefix = "uc_";

$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."permissions
	SET 
	home = ?");
$stmt->bind_param("s", $reset);
$result = $stmt->execute();
$stmt->close();	

$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."permissions
	SET 
	home = ?
	WHERE
	id = ?");
$stmt->bind_param("si", $homePage, $id);
$result = $stmt->execute();
$stmt->close();	
//return $result;	

?>