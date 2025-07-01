<?php
include_once("../config/config_mysqli.php");
//@$orgId = $_POST['orgId'];
$query = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, name FROM organization ORDER BY id");
while($result = mysqli_fetch_array($query))
{
	$id = '"'.$result["id"].'"';
	$name = $result["name"];
	
	echo "<li><a class='dropdown-item' href='#' onClick='getDepartmentStaffReport($id)'>$name</a></li>";
}
?>