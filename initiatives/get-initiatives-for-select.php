<?php
include_once("../config/config_mysqli.php");
$initiativeList_query="SELECT name, id FROM initiative";
$initiativeList_result=mysqli_query($connect, $initiativeList_query);
$row_count = mysqli_num_rows($initiativeList_result);
if ($row_count == 0) {
    echo "{ \"identifier\": \"Initiative\", \"label\": \"Initiative\", \"items\": []}";
    exit;
}
$count = 1;
echo "{ \"identifier\": \"Initiative\", \"label\": \"Initiative\", \"items\": [";
while($row = mysqli_fetch_assoc($initiativeList_result))
{
	$data["id"] = $row["id"];
	$data["Initiative"] = $row["name"];
	
	$data = json_encode($data);
	echo $data;
	$data = NULL;
	$count++;
	if($count-1 < $row_count) echo ",";	
}
echo "]}";
?>