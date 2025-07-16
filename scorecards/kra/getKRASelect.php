<?php
//ini_set('dilay_errors',0);
include_once("../../config/config_mysqli.php");

$kra_query="SELECT id, result FROM strategic_results";
$kra_result = mysqli_query($connect, $kra_query) or die("Could not query measure table");

$row_count = mysqli_num_rows($kra_result) or die("Could not count rows");
if ($row_count == null) exit;

$count = 1;
echo "{ \"identifier\": \"kraName\", \"label\": \"kraName\", \"items\": [";
while($row = mysqli_fetch_assoc($kra_result))
{
	$data["id"] = $count;
	$data["kraId"] = $row["id"];
	
	$data["kraName"] = $row["result"];
	
	$data = json_encode($data);
	echo $data;
	if($count<$row_count) echo ", ";
	$data = NULL;
	$count++;
}
echo "]}";
?>