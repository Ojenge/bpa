<?php
include_once("../config/config_mysqli.php");

@$filter = $_POST['filter'];
//@$filter = 'All';

if($filter == "All")
$listUsers = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT user_id, display_name FROM uc_users WHERE department <> 'Accent' ORDER BY department ASC");
else
$listUsers = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT user_id, display_name, department FROM uc_users WHERE department = '$filter' OR department = 'org1' ORDER BY department ASC");

while($row = mysqli_fetch_array($listUsers))
{
	echo '<option value="'.$row["user_id"].'">'.$row["display_name"].'</option>';
}
?>