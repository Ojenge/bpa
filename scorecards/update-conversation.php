<?php
include_once("../config/config_mysqli.php");


if(!empty($_POST['id']))
{
	$id = $_POST['id'];
	$note = $_POST['note'];
	@mysqli_query($connect, "UPDATE conversation SET note = '$note' WHERE id = '$id'");
}
?>