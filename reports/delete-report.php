<?php
include_once("../config/config_mysqli.php");
$reportId = $_POST["reportId"];
@mysqli_query($connect, "DELETE FROM report WHERE Id = '$reportId'");
@mysqli_query($connect, "DELETE FROM report_init WHERE id = '$reportId'");
?>