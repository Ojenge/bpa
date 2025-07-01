<?php
include_once("config_mysqli.php");

@$goal = $_POST['goal'];

mysqli_query($connect, "INSERT INTO commentarygoal (id, name) VALUES ('', '$goal')");

$tree_id_result = mysqli_query($connect, "SELECT MAX(id) FROM commentarygoal");
$tree_array = mysqli_fetch_array($tree_id_result);
$tree_id = $tree_array[0];

echo $tree_id;
?>