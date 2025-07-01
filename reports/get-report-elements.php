<?php
include_once("../config/config_mysqli.php");

$query = mysqli_query($connect, "SELECT DISTINCT linkedTo FROM report");
$count = 1;
$numRows = mysqli_num_rows($query);
if($numRows > 0)
{
	echo "[";
	while($row = mysqli_fetch_assoc($query))
	{
		$data['id'] = $row["linkedTo"];
		$data = json_encode($data);
		echo $data;
		if($count < $numRows) echo ',';
		$data = NULL;
		$count++;
	}
	echo "]";
}
else 
{
	exit;
}
?>