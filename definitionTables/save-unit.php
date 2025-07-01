<?php
include_once("config_mysqli.php");

@$unit = $_POST['unit'];

mysqli_query($connect, "INSERT INTO commentaryworkunit (id, name) VALUES ('', '$unit')");

$tree_id_result = mysqli_query($connect, "SELECT MAX(id) FROM commentaryworkunit");
$tree_array = mysqli_fetch_array($tree_id_result);
$tree_id = $tree_array[0];

echo $tree_id;
?>