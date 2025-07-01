<?php
include_once("../config/config_mysqli.php");

$query = mysqli_query($connect, "SELECT DISTINCT tree.id, initiativeimpact.linkedobjectid, initiative.projectManager 
FROM initiativeimpact, tree, initiative 
WHERE initiativeimpact.linkedobjectid = tree.id 
OR initiative.projectManager = tree.id"); //Updated this to ensure we are not picking items that are not on the tree. LTK 05 Apr 2021 1101 hours
$count = 0;
$array = array();
while($row = mysqli_fetch_assoc($query))
{
	$array[$count]["id"] = $row["id"];
	$count++;
}
$array = array_unique($array, SORT_REGULAR); //removing duplicates for an associative array
print_r(json_encode($array)); //should have been using arrays before printing to json before!!! LTK 05 Apr 2021 1115 hours
?>