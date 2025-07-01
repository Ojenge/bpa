<?php
include_once("config_mysqli.php");
$newCategory = $_POST["newCategory"];
@mysqli_query($connect, "INSERT INTO advocacy_category (id, name) VALUES ('', '$newCategory')");
?>