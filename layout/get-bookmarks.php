<?php 
include_once("../config/config_mysqli.php");
$userId = $_POST["userId"];
//$userId = "ind1";
$count = 1;
$query = mysqli_query($connect, "SELECT * FROM bookmark WHERE userId = '$userId'");
$row_count = mysqli_num_rows($query);
echo "[";
while($bookmarks = mysqli_fetch_assoc($query))
{
	echo json_encode($bookmarks);
	if($count < $row_count) echo ", ";
	$count++;		
}
echo "]";
?>