<?php
date_default_timezone_set('Africa/Nairobi');
include_once("../config/config_mysqli.php");

	//$from_id = $_POST["from_id"];
	//picks from two tables

	$db_query="SELECT * FROM dashboard_links";



	$db_result=mysqli_query($connect, $db_query) or die("Could not query dashboard table");;
	$row_count = mysqli_num_rows($db_result) or die("Could not count rows");
	if ($row_count == null) exit;
	echo "[";
	$count = 1;
	while($row = mysqli_fetch_assoc($db_result))
	{
	$data["id"] = $row["id"];
	$data["from_id"] = $row["from_id"];
	$data["to_id"] = $row["to_id"];
	$data = json_encode($data);
	echo $data;
	$data = NULL;
	$count++;
	if($count-1 < $row_count) echo ",";
	}
	echo "]";
	flush();


exit;
?>
