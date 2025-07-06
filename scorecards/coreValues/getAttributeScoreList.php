<?php
include_once("../../config/config_mysqli.php");

$attributeId = $_POST["attributeId"];

//retrieve and display all the strategic results
$result = mysqli_query($connect, "SELECT * FROM core_value_attribute_score WHERE attribute_id = '$attributeId' ORDER BY id DESC");
$count = mysqli_num_rows($result);
if($count == 0) {
	$output = "No scores available for this attribute.";
}
else
{
	$counter = 1;
	$output = '<table class="table table-sm">';
	while ($row = mysqli_fetch_assoc($result)) {
		$output .= '<tr>';
		$output .= '<td>' . $counter . '</td>';
		$output .= '<td>' . htmlspecialchars($row['score']) . '</td>';
		$output .= '<td>' . htmlspecialchars($row['date']) . '</td>';
		$output .= '<td><a href="javascript:void(0)" title="Edit This Score" onclick="editThisScore(\'' . $row["id"] . '\')"><i class="bi bi-pencil-square"></i></a>';
		$output .= ' <a href="javascript:void(0)" title="Delete Core Value" onclick="deleteThisScore(\'' . $row["id"] . '\')"><i class="bi bi-trash"></i></a></td>';
		$output .= '<tr>';
		$counter++;
	}
	$output .= '</table>';

}

echo $output;

// Close the database connection
mysqli_close($connect);

?>