<?php
//ini_set('dilay_errors',0);
include_once("../../config/config_mysqli.php");

$coreValue_query="SELECT id, result FROM core_value";
$coreValue_result = mysqli_query($connect, $coreValue_query) or die("Could not query core value table");

$row_count = mysqli_num_rows($coreValue_result) or die("Could not count rows");
if ($row_count == null) exit;

$count = 1;
echo "{ \"identifier\": \"coreValue\", \"label\": \"coreValue\", \"items\": [";
while($row = mysqli_fetch_assoc($kra_result))
{
	$data["id"] = $count;
	$data["coreValueId"] = $row["id"];
	
	$data["coreValueName"] = $row["value"];
	
	$data = json_encode($data);
	echo $data;
	if($count<$row_count) echo ", ";
	$data = NULL;
	$count++;
}
echo "]}";
?>