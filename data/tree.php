<?php 
include_once("../config/config_mysqli.php");

$tree_query="SELECT id, name, parent, type FROM tree";
$tree_result=mysqli_query($connect, $tree_query);
$tree_row_count=mysqli_num_rows($tree_result);
$count = 1; 

echo "[";
while($row = mysqli_fetch_assoc($tree_result))
{
	$meta = json_encode($row);
	echo $meta;
	if ($count < $tree_row_count) echo ",";
	$count++;
}
echo "]";

flush();
exit;
?>