<?php
include_once("config_mysqli.php");

@$process = $_POST['process'];

mysqli_query($connect, "INSERT INTO commentaryprocess (id, name) VALUES ('', '$process')");

$tree_id_result = mysqli_query($connect, "SELECT MAX(id) FROM commentaryprocess");
$tree_array = mysqli_fetch_array($tree_id_result);
$tree_id = $tree_array[0];

echo $tree_id;
?>