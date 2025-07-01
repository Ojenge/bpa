<?php
// Check if we're being called from the analytics directory or a subdirectory
include_once("../config/config_mysqli.php");
function save_bulk_kpi($tree_parent, $tree_name, $collectionFrequency, $kpiDescription, $thresholdType, $kpiUpdater, $kpiOwner, $measureType, $dataType, $aggregationType, $darkGreen, $blue, $green, $red, $archive, $sort, $tags)
{
	global $connect;

	// Use transaction for atomic operations
	mysqli_autocommit($connect, FALSE);

	try {
		// Generate unique ID with proper error handling
		$tree_id_result = mysqli_query($connect, "SELECT MAX(CAST(SUBSTRING(id, 4, length(id)-3) AS UNSIGNED)) FROM measure");
		if (!$tree_id_result) {
			throw new Exception("Error generating measure ID: " . mysqli_error($connect));
		}

		$tree_array = mysqli_fetch_array($tree_id_result);
		$tree_id = ($tree_array[0] ? $tree_array[0] : 0) + 1;
		$tree_id = "kpi".$tree_id;

		// Sanitize inputs
		$tree_name = mysqli_real_escape_string($connect, $tree_name);
		$tree_parent = mysqli_real_escape_string($connect, $tree_parent);
		$collectionFrequency = mysqli_real_escape_string($connect, $collectionFrequency);
		$kpiDescription = mysqli_real_escape_string($connect, $kpiDescription);
		$thresholdType = mysqli_real_escape_string($connect, $thresholdType);
		$kpiUpdater = mysqli_real_escape_string($connect, $kpiUpdater);
		$kpiOwner = mysqli_real_escape_string($connect, $kpiOwner);
		$measureType = mysqli_real_escape_string($connect, $measureType);
		$dataType = mysqli_real_escape_string($connect, $dataType);
		$aggregationType = mysqli_real_escape_string($connect, $aggregationType);
		$archive = mysqli_real_escape_string($connect, $archive);
		$tags = mysqli_real_escape_string($connect, $tags);

		// Clean numeric values
		$darkGreen = str_replace(',', '', $darkGreen);
		$blue = str_replace(',', '', $blue);
		$green = str_replace(',', '', $green);
		$red = str_replace(',', '', $red);

		// Validate numeric values
		if (!is_numeric($darkGreen)) $darkGreen = '0';
		if (!is_numeric($blue)) $blue = '0';
		if (!is_numeric($green)) $green = '0';
		if (!is_numeric($red)) $red = '0';
		if (!is_numeric($sort)) $sort = '3000';

		// Set default values for empty fields
		if($kpiOwner == '') $kpiOwner = 'ind0';//The hack using an ind0 without a name makes sure the get-content for measure does not return empty for other measue values when owner or updater are missing.
		if($kpiUpdater == '') $kpiUpdater = 'ind0';

		// Insert into measure table
		$insert_measure = mysqli_query($connect, "INSERT INTO measure
		(id, name, calendarType, measureType, description, linkedObject, dataType, aggregationType, owner, updater, red, blue, green, darkGreen, gaugeType, weight, archive, sort, tags) VALUES ('$tree_id', '$tree_name', '$collectionFrequency', '$measureType', '$kpiDescription', '$tree_parent', '$dataType', '$aggregationType', '$kpiOwner', '$kpiUpdater', '$red', '$blue', '$green', '$darkGreen', '$thresholdType', '', '$archive', '$sort', '$tags')");

		if (!$insert_measure) {
			throw new Exception("Error inserting measure: " . mysqli_error($connect));
		}

		// Insert into tree table
		$insert_tree = mysqli_query($connect, "INSERT INTO tree (id, name, parent, type, linked, sort) VALUES ('$tree_id', '$tree_name', '$tree_parent', 'measure', 'no', '$sort')");

		if (!$insert_tree) {
			throw new Exception("Error inserting tree: " . mysqli_error($connect));
		}
		/* Removed the equal allocation of weight since it will affect those that have been set. LTK 11May24 1104hrs
		$query = mysqli_query($connect, "SELECT COUNT(id) AS idCount, linkedObject FROM measure WHERE linkedObject = (SELECT linkedObject FROM measure WHERE id = '$tree_id') GROUP BY linkedObject");
		$weightCount = mysqli_fetch_assoc($query);
		if($weightCount["idCount"] == 1) $weight = 1;
		else
		{
			$linkedObject = $weightCount["linkedObject"];
			$weight = 1/$weightCount["idCount"];
			$weight = number_format((float)$weight, 2, '.', '');
		}
		mysqli_query($connect, "UPDATE measure SET weight = '$weight' WHERE linkedObject = '$linkedObject'") or file_put_contents("error.txt", "weight = $weight; Error => ".mysqli_error($connect));
		*/

		// Commit transaction
		mysqli_commit($connect);
		mysqli_autocommit($connect, TRUE);

		// Return the created ID
		return $tree_id;

	} catch (Exception $e) {
		// Rollback transaction on error
		mysqli_rollback($connect);
		mysqli_autocommit($connect, TRUE);

		// Log error
		file_put_contents("error.txt", "Error in save_bulk_kpi: " . $e->getMessage());
		throw $e;
	}

	//echo "Updated $tree_parent of id $tree_id.<br>"; //How this statement has caused me nightmares!!! Saving a new scorecard item doesn't show details due to this convolusion! I will leave it here for the record. LTK 04Nov2024 1040hrs
}
?>