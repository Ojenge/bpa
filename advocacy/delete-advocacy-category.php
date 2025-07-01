<?php
include_once("config_mysqli.php");
$category = $_POST["category"];
@mysqli_query($connect, "UPDATE advocacy SET category = 'Uncategorised' WHERE category = '$category'");
@mysqli_query($connect, "DELETE FROM advocacy_category WHERE name = '$category'");
?>