<?php 
include_once("../config/config_mysqli.php");

$bookMarkId = $_POST["bookMarkId"];
$action = $_POST["action"];
$newName = $_POST["newName"];

if($action == 'delete') mysqli_query($connect, "DELETE FROM bookmark WHERE id = '$bookMarkId'");
else mysqli_query($connect, "UPDATE bookmark SET name = '$newName' WHERE id = '$bookMarkId'");

?>