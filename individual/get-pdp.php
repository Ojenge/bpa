<?php
include_once("../config/config_mysqli.php");
$pdpId = $_POST["pdpId"];
$result = mysqli_query($connect, "SELECT * FROM pdp WHERE id = '$pdpId'");
$result = mysqli_fetch_assoc($result);

echo json_encode($result);
?>