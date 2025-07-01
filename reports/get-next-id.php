<?php
include_once("../config/config_mysqli.php");
$orgId = isset($_POST['orgId']) ? $_POST['orgId'] : '';

if (empty($orgId)) {
	echo json_encode(['error' => 'Missing orgId parameter']);
	exit;
}

$query = mysqli_query($connect, "SELECT id FROM organization WHERE id > '$orgId' LIMIT 1");
if (!$query) {
	echo json_encode(['error' => 'Database query failed: ' . mysqli_error($connect)]);
	exit;
}

$result = mysqli_fetch_array($query);
echo $result ? $result["id"] : '';
?>