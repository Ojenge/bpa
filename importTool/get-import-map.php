<?php
include_once("../config/config_mysqli.php");

$query="SELECT * FROM import_map";
	
$result = mysqli_query($connect, $query) or file_put_contents("error.txt", "Can't query");
$import_count = mysqli_num_rows($result);

$count = 1;
echo "[";
while($row = mysqli_fetch_assoc($result))
{
	$data["id"] = $row["id"];
	$data["measureId"] = $row["kpi"];
	
	$kpi = $row["kpi"];
	$kpi = mysqli_query($connect, "SELECT name FROM measure WHERE id = '$kpi'");
	$kpi = mysqli_fetch_assoc($kpi);
	$data["kpi"] = $kpi["name"];
	
	$data["emailSubject"] = $row["emailSubject"];
	$data["frequency"] = $row["frequency"];
	$data["sender"] = $row["sender"];
	
	$data["admin"] =
		'<a class="deleteMap ml10" href="javascript:void(0)" title="Delete">'.
			'<i class="glyphicon glyphicon-trash"></i>'.
        '</a>';
	
	echo json_encode($data, JSON_PRETTY_PRINT);
	
	if($count < $import_count) echo ",";
	$data = NULL;
	$count++;
}
echo "]";
?>