<?php
include_once("config_mysqli.php");
$id = $_POST["id"];
@mysqli_query($connect, "DELETE FROM commentarygoal WHERE id = '$id'");
?>