<?php
include_once("config_mysqli.php");

$overall_query="SELECT category,
SUM(IF(status = 'Identified', 1,0)) AS `identified`,
SUM(IF(status = 'Request Made to Government', 1,0)) AS `requestMade`,
SUM(IF(status = 'Government Committed', 1,0)) AS `gvtCommitted`,
SUM(IF(status = 'Implementation in Progress', 1,0)) AS `inProgress`,
SUM(IF(status = 'Issue Resolved', 1,0)) AS `resolved`,
COUNT(status) AS `total`
FROM advocacy
WHERE category != 'Uncategorised'
GROUP BY category
ORDER BY id ASC";

//$overall_query="SELECT COUNT(progress) AS count, progress FROM directives GROUP BY progress";
$overall_result = mysqli_query($connect, $overall_query);
$overall_count = mysqli_num_rows($overall_result);
$count = 1; $red = 0; $orange = 0; $yellow = 0; $green = 0; $blue = 0;
echo "[";
while($row = mysqli_fetch_assoc($overall_result))
{
	$dir_row["Category"] = $row["category"]; 
	$dir_row["Red"] = $row["identified"];
	$red = $red +  $dir_row["Red"];
	$dir_row["Orange"] = $row["requestMade"];
	$orange = $orange +  $dir_row["Orange"];
	$dir_row["Yellow"] = $row["gvtCommitted"];
	$yellow = $yellow +  $dir_row["Yellow"];
	$dir_row["Green"] = $row["inProgress"];
	$green = $green +  $dir_row["Green"];
	$dir_row["Blue"] = $row["resolved"];
	$blue = $blue +  $dir_row["Blue"]; 
	//if ($dir_row["overallRed"] == NULL) $dir_row["overallRed"] = '';
	$totalCheck = $count+1;
	if($count == $overall_count) 
	{
		$dir_row["BlueTotal"] = $blue;
		$dir_row["GreenTotal"] = $green;
		$dir_row["YellowTotal"] = $yellow;
		$dir_row["OrangeTotal"] = $orange;
		$dir_row["RedTotal"] = $red;
	}
	echo json_encode($dir_row);
	if($count < $overall_count) echo ",";
	$dir_row = NULL;
	$count++;
}
echo "]";
?>