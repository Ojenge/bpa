<?php
include_once("config_mysqli.php");
date_default_timezone_set('Africa/Nairobi');

$directive_query="SELECT * FROM advocacy_trend ORDER BY date ASC";

$directive_result = mysqli_query($connect, $directive_query);
$directive_count = mysqli_num_rows($directive_result);
$count = 1;
echo "[";

while($row = mysqli_fetch_assoc($directive_result))
{
	$dir_row["id"] = $row["id"]; if ($dir_row["id"] == NULL) $dir_row["id"] = '';
	$dir_row["date"] = date('M-Y',strtotime($row["date"])); if ($dir_row["date"] == NULL) $dir_row["date"] = '';
	$dir_row["red"] = $row["red"]; if ($dir_row["red"] == NULL) $dir_row["red"] = '';
	$dir_row["orange"] = $row["orange"]; if ($dir_row["orange"] == NULL) $dir_row["orange"] = '';
	$dir_row["yellow"] = $row["yellow"]; if ($dir_row["yellow"] == NULL) $dir_row["yellow"] = '';
	$dir_row["green"] = $row["green"]; if ($dir_row["green"] == NULL) $dir_row["green"] = '';
	$dir_row["blue"] = $row["blue"]; if ($dir_row["blue"] == NULL) $dir_row["blue"] = NULL;

	echo json_encode($dir_row);
	if($count < $directive_count) echo ",";
	$dir_row = NULL;
	$count++;
}
echo "]";
?>