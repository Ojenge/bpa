<?php
include_once("../config/config_mysqli.php");

@$tree_parent = $_POST['tree_parent'];
@$tree_name = $_POST['tree_name'];
@$kpiLinkedId = $_POST['kpiLinkedId'];

mysqli_query($connect, "INSERT INTO measurelinks (measure_id, linked_id, link_type) VALUES ('$kpiLinkedId', '$tree_parent', 'Measure')");
	
mysqli_query($connect, "INSERT INTO tree (id, name, parent, type, linked) VALUES ('$kpiLinkedId', '$tree_name', '$tree_parent', 'measure', 'yes')");

$tree_id = mysqli_query($connect, "SELECT MAX(id) AS tree_id FROM tree WHERE id = '$kpiLinkedId' AND parent = '$tree_parent'");
$tree_id = mysqli_fetch_assoc($tree_id);
$tree_id = $tree_id["tree_id"];

echo $tree_id;
?>