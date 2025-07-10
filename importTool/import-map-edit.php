<?php
include_once("../config/config_mysqli.php");

$id = $_POST['pk'];
$updated = $_POST['value'];
$column = $_POST['name'];

file_put_contents("save.txt", "\n=>$id, $updated, $column");
if($column == "measureId") $column = "kpi";

//date_default_timezone_set('Africa/Nairobi');
//$today = date('Y-m-d H:i:s');

if($id == "New")
{
	$idQuery = mysqli_query($connect, "SELECT MAX(id) AS id FROM import_map") or file_put_contents("saveMap.txt", "\r\n => UNABLE TO GET MAX(id)", FILE_APPEND);
	$idRow = mysqli_fetch_assoc($idQuery);
	$id = $idRow["id"] + 1;
	mysqli_query($connect, "INSERT INTO import_map (id, kpi, emailSubject, frequency, sender) 
	VALUES ('$id', '', '', '', '')") or file_put_contents("saveMap.txt", "\r\n => ERROR = ".mysqli_query($connect), FILE_APPEND);
}

$updated = mysqli_real_escape_string($connect, $updated);
		
mysqli_query($connect, "UPDATE import_map SET $column = '$updated' WHERE id = '$id'") or file_put_contents("saveMap.txt", "\r\n => UNABLE TO UPDATE ".$id.", ".$column.", ".$updated." with error => ".mysqli_error($connect), FILE_APPEND);

echo '{"success":"true","pk":"'.$id.'","value":"'.$updated.'"}';
?>