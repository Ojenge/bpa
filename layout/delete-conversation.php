<?php
include_once("../config/config_mysqli.php");
if(!empty($_POST['id']))
{
	$id = $_POST['id'];
	@mysqli_query($connect, "DELETE FROM conversation WHERE id = '$id'");
}
?>