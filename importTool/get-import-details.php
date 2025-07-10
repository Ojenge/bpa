<?php
include_once("../config/config_mysqli.php");

$id = $_POST['id'];

$query="SELECT * FROM import_months WHERE measureId = '$id'";
//$query="SELECT * FROM import_months";
	
$result = mysqli_query($connect, $query) or file_put_contents("error.txt", "Can't query");
$import_count = mysqli_num_rows($result);

$count = 1;
echo "[";
while($row = mysqli_fetch_assoc($result))
{
	$data["id"] = $row["id"];
	
	$data["month"] = $row["month"];
	$data["value"] = $row["value"];
	$data["target"] = $row["target"];
	$data["name"] = $row["name"];
	
	$data["admin"] =
		'<a class="deleteCell ml10" href="javascript:void(0)" title="Delete">'.
			'<i class="glyphicon glyphicon-trash"></i>'.
        '</a>';
	
	echo json_encode($data, JSON_PRETTY_PRINT);
	
	if($count < $import_count) echo ",";
	$data = NULL;
	$count++;
}
echo "]";
?>