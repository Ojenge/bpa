<?php
include_once("../../config/config_mysqli.php");

$id = $_POST["id"];

//retrieve and display all the strategic results
$result = mysqli_query($connect, "SELECT * FROM core_value WHERE id = '$id'");

while ($row = mysqli_fetch_assoc($result)) {
	$output = json_encode(array(
		'id' => $row['id'],
		'value' => htmlspecialchars($row['value']),
		'description' => htmlspecialchars($row['description'])
	));
}

echo $output;

// Close the database connection
mysqli_close($connect);

?>