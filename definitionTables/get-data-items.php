<?php
include_once("config_mysqli.php");
	
$user_query="SELECT id, name FROM commentarydataitem";
$user_result=mysqli_query($connect, $user_query) or die("Could not query uc_users table");;

$row_count = mysqli_num_rows($user_result);// or die("Could not count rows");
if ($row_count == null) exit;

$count = 1;
echo "{ \"identifier\": \"DataItem\", \"label\": \"DataItem\", \"items\": [";
while($row = mysqli_fetch_assoc($user_result))
{
	$data["id"] = $row["id"];
	$data["DataItem"] = $row["name"];
	$data = json_encode($data);
	echo $data;
	if($count<$row_count) echo ", ";
	$data = NULL;
	$count++;
}
echo "]}";
?>