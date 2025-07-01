<?php
include_once("config_mysqli.php");
$id = $_POST["id"];
@mysqli_query($connect, "DELETE FROM commentaryprocess WHERE id = '$id'");
?>