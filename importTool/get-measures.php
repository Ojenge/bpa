<?php
include_once("../analytics/config_mysqli.php");

$query="SELECT id, name FROM measure";
	
$result = mysqli_query($connect, $query) or file_put_contents("error.txt", "Can't query");
$measure_count = mysqli_num_rows($result);

$count = 1;
echo "[";
while($row = mysqli_fetch_assoc($result))
{
	$data["value"] = $row["id"];
	$data["text"] = $row["name"];

	echo json_encode($data, JSON_PRETTY_PRINT);
	
	if($count < $measure_count) echo ",";
	$data = NULL;
	$count++;
}
echo "]";
?>