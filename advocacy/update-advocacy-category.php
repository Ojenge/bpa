<?php
include_once("config_mysqli.php");
$category = $_POST["category"];
$newCategory = $_POST["newCategory"];
@mysqli_query($connect, "UPDATE advocacy SET category = '$newCategory' WHERE category = '$category'");
@mysqli_query($connect, "UPDATE advocacy_category SET name = '$newCategory' WHERE name = '$category'");
?>