<?php
include_once("../config/config_mysqli.php");

/*
$tree_id_result = mysqli_query($connect, "SELECT MAX(CAST(SUBSTRING(id, 4, length(id)-3) AS UNSIGNED)) FROM individual");
$tree_array = mysqli_fetch_array($tree_id_result);
$tree_id = $tree_array[0] + 1;
$tree_id = "ind".$tree_id;*/

$tree_id = $_POST["indId"];
$tree_name = $_POST["tree_name"];
$tree_parent = $_POST["tree_parent"];
$indPhoto = $_POST["indPhoto"];

file_put_contents("saveIndividual.txt", "Are we getting here? ".$tree_id."; ".$indPhoto);

mysqli_query($connect, "INSERT INTO individual (id, name, cascadedfrom, photo) 
			VALUES ('$tree_id', '$tree_name', '$tree_parent', '$indPhoto')") or file_put_contents("error.txt", "Error => ".mysqli_error($connect));
					
mysqli_query($connect, "INSERT INTO tree (id, name, parent, type) VALUES ('$tree_id', '$tree_name', '$tree_parent', 'individual')") or file_put_contents("error.txt", "Error => ".mysqli_error($connect));

mysqli_query($connect, "INSERT INTO uc_permissions (id, name, orgId, status, callFunction, url, home, icon) VALUES ('', '$tree_name', '$tree_id', 'Active', NULL, NULL, 'No', NULL)") or file_put_contents("saveIndividualerror.txt", "Error => ".mysqli_error($connect));

echo $tree_id;
?>