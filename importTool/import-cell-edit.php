<?php
include_once("../analytics/config_mysqli.php");

$id = $_POST['pk'];
$updated = $_POST['value'];
$column = $_POST['name'];
$parentId = $_GET['parentId'];

//file_put_contents("saveCell.txt", "\n=>$id, $updated, $column, $parentId", FILE_APPEND);

//date_default_timezone_set('Africa/Nairobi');
//$today = date('Y-m-d H:i:s');

if($id == "New")
{
	$idQuery = mysqli_query($connect, "SELECT MAX(id) AS id FROM import_months") or file_put_contents("saveMap.txt", "\r\n => UNABLE TO GET MAX(id)", FILE_APPEND);
	$idRow = mysqli_fetch_assoc($idQuery);
	$id = $idRow["id"] + 1;
	mysqli_query($connect, "INSERT INTO import_months (id, measureId, month, value, target, name) 
	VALUES ('$id', '$parentId', '', '', '', '')") or file_put_contents("saveMap.txt", "\r\n => ERROR = ".mysqli_query($connect), FILE_APPEND);
}

$updated = mysqli_real_escape_string($connect, $updated);
		
mysqli_query($connect, "UPDATE import_months SET $column = '$updated' WHERE id = '$id'") or file_put_contents("saveMap.txt", "\r\n => UNABLE TO UPDATE ".$id.", ".$column.", ".$updated." with error => ".mysqli_error($connect), FILE_APPEND);

echo '{"success":"true","pk":"'.$id.'","value":"'.$updated.'"}';
?>