<?php
include_once("../../config/config_mysqli.php");

$id = $_POST["id"];

//retrieve and display all the strategic results
$result = mysqli_query($connect, "SELECT * FROM strategic_results WHERE id = '$id'");

while ($row = mysqli_fetch_assoc($result)) {
	$output = json_encode(array(
		'id' => $row['id'],
		'priority' => htmlspecialchars($row['priority']),
		'result' => htmlspecialchars($row['result'])
	));
}

echo $output;

// Close the database connection
mysqli_close($connect);

?>