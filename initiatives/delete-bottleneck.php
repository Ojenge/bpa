<?php
include_once("../config/config.php");

if(!empty($_POST['id'])) $id = $_POST['id'];

//mysqli_query($connect, "DELETE FROM initiative_issue WHERE id = '$id'");
file_put_contents("bottleneck.txt", "\n\t DELETE issue number $id", FILE_APPEND);
?>