<?php
include_once("../config/config.php");

$initiativeList_query="SELECT name, id FROM objective";
	
$initiativeList_result=mysqli_query($connect, $initiativeList_query) or die("Could not query measure month table");;
$row_count = @mysqli_num_rows($initiativeList_result);
if ($row_count == null) exit;
$count = 1;
echo "[";
//$data = array();
while($row = mysqli_fetch_assoc($initiativeList_result))
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