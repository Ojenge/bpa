<?php
ini_set('display_errors',0);
include_once("config_mysqli.php");
	
$user_query="SELECT name FROM advocacy_category";
$user_result=mysqli_query($connect, $user_query);

$row_count = mysqli_num_rows($user_result);
if ($row_count == null) exit;

//$data["Team"] = NULL;
$count = 1;
echo "{ \"identifier\": \"Category\", \"label\": \"Category\", \"items\": [";
while($row = mysqli_fetch_assoc($user_result))
{
	$data["id"] = $row["id"];
	$data["Category"] = $row["name"];
	//if ($count == 1) $data["selected"] = "true";
	//else $data["User"] = $data["User"].", ".$row3["firstName"]." ".$row3["lastName"];
	$data = json_encode($data);
	echo $data;
	if($count<$row_count) echo ", ";
	$data = NULL;
	$count++;
}
echo "]}";
?>