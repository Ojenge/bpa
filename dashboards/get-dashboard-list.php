<?php
include_once("../config/config_mysqli.php");
//if(isset($_POST['objectId']))
//{
	$objectId = $_POST['objectId'];
	//$measureId = 6;
	//$objectId = "org2";
	$db_query="SELECT id, name FROM dashboard WHERE linkedId = '$objectId'";
	$db_result=mysqli_query($connect, $db_query) or die("Could not query dashboard table");;
	$row_count = mysqli_num_rows($db_result) or die("Could not count rows");
	if ($row_count == null) exit;
	echo "[";
	$count = 1;
	while($row = mysqli_fetch_assoc($db_result))
	{
		$data["id"] = $row["id"];
		$data["name"] = $row["name"];
		$data = json_encode($data);
		echo $data;
		$data = NULL;
		$count++;
		if($count-1 < $row_count) echo ",";
	}
	echo "]";
	flush();
//}
exit;
?>