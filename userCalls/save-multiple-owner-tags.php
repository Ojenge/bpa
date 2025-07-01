<?php
require_once("../admin/models/config.php");
$tags = $_GET['tags'];

file_put_contents("save-tags.txt", "Are we getting here? ".$tags);
?>