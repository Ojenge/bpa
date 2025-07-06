<?php
include_once("../../config/config_mysqli.php");

$attributeId = $_POST["attributeId"];

//retrieve and display all the strategic results
$result = mysqli_query($connect, "SELECT * FROM core_value_attribute_score WHERE attribute_id = '$attributeId' ORDER BY id DESC LIMIT 1");
$count = mysqli_num_rows($result);
if($count == 0) {
	$output = json_encode(array(
		'attributeId' => $attributeId,
		'score' => 'No score available',
		'date' => 'N/A'
	));
}
else
{
	while ($row = mysqli_fetch_assoc($result)) {
		$output = json_encode(array(
			'id' => $row['id'],
			'score' => htmlspecialchars($row['score']),
			'date' => htmlspecialchars($row['date'])
		));
	}
}

echo $output;

// Close the database connection
mysqli_close($connect);

?>