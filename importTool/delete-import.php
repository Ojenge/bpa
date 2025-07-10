<?php
include_once("../config/config_mysqli.php");
$importId = $_POST["importId"];

$kpiId = mysqli_query($connect, "SELECT kpi FROM import_map WHERE id = '$importId'");
$kpiId = mysqli_fetch_assoc($kpiId);
$kpiId = $kpiId["kpi"];

//file_put_contents("delete.txt", "importId = $importId and kpiId = $kpiId");

mysqli_query($connect, "DELETE FROM import_map WHERE id = '$importId'") or file_put_contents("deleteError.txt", "Error => ".mysqli_error($connect));
mysqli_query($connect, "DELETE FROM import_months WHERE measureId = '$kpiId'") or file_put_contents("deleteError.txt", "Error => ".mysqli_error($connect));
?>