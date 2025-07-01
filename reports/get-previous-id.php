<?php
include_once("../config/config_mysqli.php");
@$orgId = $_POST['orgId'];
$query = mysqli_query($connect, "SELECT id FROM organization WHERE id < '$orgId' ORDER BY id DESC LIMIT 1");
$result = mysqli_fetch_array($query);
echo $result["id"];
?>