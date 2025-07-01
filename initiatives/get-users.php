<?php
ini_set('display_errors',0);
include_once("../config/config_mysqli.php");
	
$user_query="SELECT DISTINCT(uc_users.user_id), uc_users.display_name FROM uc_users, uc_user_permission_matches 
WHERE uc_users.id = uc_user_permission_matches.user_id AND uc_user_permission_matches.permission_id IN ('2','3') ";
$user_result=@mysqli_query($connect, $user_query) or die("Could not query uc_users table");;

$row_count = mysqli_num_rows($user_result) or die("Could not count rows");
if ($row_count == null) exit;

//$data["Team"] = NULL;
$count = 1;
echo "{ \"identifier\": \"User\", \"label\": \"User\", \"items\": [";
while($row = mysqli_fetch_assoc($user_result))
{
	$data["id"] = $row["user_id"];
	$data["User"] = $row["display_name"];
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