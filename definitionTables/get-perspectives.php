<?php
include_once("config_mysqli.php");
	
$user_query="SELECT id, name FROM perspective";
$user_result=mysqli_query($connect, $user_query) or die("Could not query uc_users table");;

$row_count = mysqli_num_rows($user_result) or die("Could not count rows");
if ($row_count == null) exit;

//$data["Team"] = NULL;
$count = 1;
echo "{ \"identifier\": \"Perspective\", \"label\": \"Perspective\", \"items\": [";
//echo "{ \"identifier\": \"id\", \"label\": \"id\", \"items\": [";
//echo "[";
while($row = mysqli_fetch_assoc($user_result))
{
	//$data["id"] = (string)$count;
	$data["id"] = $row["id"];
	$data["Perspective"] = $row["name"];
	//if ($count == 1) $data["selected"] = "true";
	//else $data["User"] = $data["User"].", ".$row3["firstName"]." ".$row3["lastName"];
	$data = json_encode($data);
	echo $data;
	if($count<$row_count) echo ", ";
	$data = NULL;
	$count++;
}
echo "]}";
//echo "]";
?>