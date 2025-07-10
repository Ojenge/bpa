<?php
include_once("../config/config_mysqli.php");
$cellId = $_POST["cellId"];

mysqli_query($connect, "DELETE FROM import_months WHERE id = '$cellId'") or file_put_contents("deleteError.txt", "Error => ".mysqli_error($connect));
?>