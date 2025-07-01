<?php
include_once("config/config_mysqli.php");

//date_default_timezone_set('Africa/Nairobi');
//header('Content-Type: application/json');

$query="SELECT DISTINCT item FROM expenses";
	
$result = mysqli_query($connect, $query);
$balances_count = mysqli_num_rows($result);

$count = 1;
echo "[";
while($row = mysqli_fetch_assoc($result))
{
	//$data["id"] = $row["id"];
	$data["name"] = $row["item"];
	
	echo json_encode($data, JSON_PRETTY_PRINT);
	
	if($count < $balances_count) echo ",";
	$data = NULL;
	$count++;
}
echo "]";
?>