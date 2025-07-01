<?php
include_once("config_mysqli.php");

@$dataItem = $_POST['dataItem'];

mysqli_query($connect, "INSERT INTO commentarydataitem (id, name) VALUES ('', '$dataItem')");

$tree_id_result = mysqli_query($connect, "SELECT MAX(id) FROM commentarydataitem");
$tree_array = mysqli_fetch_array($tree_id_result);
$tree_id = $tree_array[0];

echo $tree_id;
?>
