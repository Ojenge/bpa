<?php
include_once("../config/config_mysqli.php");
include_once("../functions/save-tree-bulk-kpi.php");

// Enable error reporting for debugging
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// Input validation and sanitization
$tree_edit = isset($_POST['tree_edit']) ? mysqli_real_escape_string($connect, $_POST['tree_edit']) : '';
$objectType = isset($_POST['tree_type']) ? mysqli_real_escape_string($connect, $_POST['tree_type']) : '';
$tree_parent = isset($_POST['tree_parent']) ? mysqli_real_escape_string($connect, $_POST['tree_parent']) : '';

// Fallback: if tree_type is missing but an individual id is provided, assume operation targets an individual
if (empty($objectType) && isset($_POST['indId']) && !empty($_POST['indId'])) {
    $objectType = 'individual';
}

// Validate required fields
if (empty($objectType)) {
    file_put_contents("error.txt", "Error: Missing required field 'tree_type'");
    die("Error: Missing required field 'tree_type'");
}

switch($objectType)
{
	case "organization":
	{
		if($tree_edit == "editMe")
		{
			$tree_id = isset($_POST["tree_id"]) ? mysqli_real_escape_string($connect, $_POST["tree_id"]) : '';
			if (empty($tree_id)) {
				file_put_contents("error.txt", "Error: Missing tree_id for organization edit");
				die("Error: Missing tree_id for organization edit");
			}
		}
		else
		{
			// Use transaction for ID generation to prevent race conditions
			mysqli_autocommit($connect, FALSE);

			$tree_id_result = mysqli_query($connect, "SELECT MAX(CAST(SUBSTRING(id, 4, length(id)-3) AS UNSIGNED)) FROM organization");
			if (!$tree_id_result) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error generating organization ID: " . mysqli_error($connect));
				die("Error generating organization ID");
			}

			$tree_array = mysqli_fetch_array($tree_id_result);
			$tree_id = ($tree_array[0] ? $tree_array[0] : 0) + 1;
			$tree_id = "org".$tree_id;

			mysqli_commit($connect);
			mysqli_autocommit($connect, TRUE);
		}
		break;
	}
	case "perspective":
	{
		if($tree_edit == "editMe")
		{
			$tree_id = isset($_POST["tree_id"]) ? mysqli_real_escape_string($connect, $_POST["tree_id"]) : '';
			if (empty($tree_id)) {
				file_put_contents("error.txt", "Error: Missing tree_id for perspective edit");
				die("Error: Missing tree_id for perspective edit");
			}
		}
		else
		{
			// Use transaction for ID generation to prevent race conditions
			mysqli_autocommit($connect, FALSE);

			$tree_id_result = mysqli_query($connect, "SELECT MAX(CAST(SUBSTRING(id, 6, length(id)-5) AS UNSIGNED)) FROM perspective");
			if (!$tree_id_result) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error generating perspective ID: " . mysqli_error($connect));
				die("Error generating perspective ID");
			}

			$tree_array = mysqli_fetch_array($tree_id_result);
			$tree_id = ($tree_array[0] ? $tree_array[0] : 0) + 1;
			$tree_id = "persp".$tree_id;

			mysqli_commit($connect);
			mysqli_autocommit($connect, TRUE);
		}
		break;
	}
	case "objective":
	{
		if($tree_edit == "editMe")
		{
			$tree_id = isset($_POST["tree_id"]) ? mysqli_real_escape_string($connect, $_POST["tree_id"]) : '';
			if (empty($tree_id)) {
				file_put_contents("error.txt", "Error: Missing tree_id for objective edit");
				die("Error: Missing tree_id for objective edit");
			}
		}
		else
		{
			// Use transaction for ID generation to prevent race conditions
			mysqli_autocommit($connect, FALSE);

			$tree_id_result = mysqli_query($connect, "SELECT MAX(CAST(SUBSTRING(id, 4, length(id)-3) AS UNSIGNED)) FROM objective");
			if (!$tree_id_result) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error generating objective ID: " . mysqli_error($connect));
				die("Error generating objective ID");
			}

			$tree_array = mysqli_fetch_array($tree_id_result);
			$tree_id = ($tree_array[0] ? $tree_array[0] : 0) + 1;
			$tree_id = "obj".$tree_id;

			// Update objectiveteam table - this seems to be updating the wrong record
			// This should probably be done after the objective is created, not before
			// Commenting out for now as it seems incorrect
			// mysqli_query($connect, "UPDATE objectiveteam SET objectiveId = '$tree_id' WHERE objectiveId = '$tree_parent'");

			mysqli_commit($connect);
			mysqli_autocommit($connect, TRUE);
		}
		break;
	}
	case "measure":
	{
		if($tree_edit == "editMe")
		{
			$tree_id = isset($_POST["tree_id"]) ? mysqli_real_escape_string($connect, $_POST["tree_id"]) : '';
			if (empty($tree_id)) {
				file_put_contents("error.txt", "Error: Missing tree_id for measure edit");
				die("Error: Missing tree_id for measure edit");
			}
		}
		else
		{
			// For measures, ID generation is handled in save_bulk_kpi function
			// We don't need to generate it here
			$tree_id = null;
		}
		break;
	}
	case "individual":
	{
		if($tree_edit == "editMe")
		{
			$tree_id = isset($_POST["tree_id"]) ? mysqli_real_escape_string($connect, $_POST["tree_id"]) : '';
		}
		else
		{
			// When adding a new individual we expect an existing user id passed via indId
			$tree_id = isset($_POST['indId']) ? mysqli_real_escape_string($connect, $_POST['indId']) : '';
		}
		break;
	}
}

// Sanitize and validate input fields
$tree_name = isset($_POST['tree_name']) ? mysqli_real_escape_string($connect, $_POST['tree_name']) : '';
if (empty($tree_name)) {
    file_put_contents("error.txt", "Error: Missing required field 'tree_name'");
    die("Error: Missing required field 'tree_name'");
}

// Optional fields with proper sanitization
$collectionFrequency = isset($_POST['collectionFrequency']) ? mysqli_real_escape_string($connect, $_POST['collectionFrequency']) : '';
$updaterCheckbox = isset($_POST['updaterCheckbox']) ? mysqli_real_escape_string($connect, $_POST['updaterCheckbox']) : '';
$kpiDescription = isset($_POST['kpiDescription']) ? mysqli_real_escape_string($connect, $_POST['kpiDescription']) : '';
$kpiOutcome = isset($_POST['kpiOutcome']) ? mysqli_real_escape_string($connect, $_POST['kpiOutcome']) : '';
$kpiMission = isset($_POST['kpiMission']) ? mysqli_real_escape_string($connect, $_POST['kpiMission']) : '';
$kpiVision = isset($_POST['kpiVision']) ? mysqli_real_escape_string($connect, $_POST['kpiVision']) : '';
$kpiValues = isset($_POST['kpiValues']) ? mysqli_real_escape_string($connect, $_POST['kpiValues']) : '';
$thresholdType = isset($_POST['thresholdType']) ? mysqli_real_escape_string($connect, $_POST['thresholdType']) : '';
$kpiUpdater = isset($_POST['kpiUpdater']) ? mysqli_real_escape_string($connect, $_POST['kpiUpdater']) : '';
$kpiOwnerTags = isset($_POST['kpiOwnerTags']) ? mysqli_real_escape_string($connect, $_POST['kpiOwnerTags']) : '';
$kpiOwner = isset($_POST['kpiOwner']) ? mysqli_real_escape_string($connect, $_POST['kpiOwner']) : '';
$measureType = isset($_POST['measureType']) ? mysqli_real_escape_string($connect, $_POST['measureType']) : '';
$dataType = isset($_POST['dataType']) ? mysqli_real_escape_string($connect, $_POST['dataType']) : '';
$aggregationType = isset($_POST['aggregationType']) ? mysqli_real_escape_string($connect, $_POST['aggregationType']) : '';
$kraListId = isset($_POST['kraListId']) ? mysqli_real_escape_string($connect, $_POST['kraListId']) : '';

// Numeric fields with proper validation and sanitization
$darkGreen = isset($_POST['darkGreen']) ? str_replace(',', '', mysqli_real_escape_string($connect, $_POST['darkGreen'])) : '0';
$blue = isset($_POST['blue']) ? str_replace(',', '', mysqli_real_escape_string($connect, $_POST['blue'])) : '0';
$green = isset($_POST['green']) ? str_replace(',', '', mysqli_real_escape_string($connect, $_POST['green'])) : '0';
$red = isset($_POST['red']) ? str_replace(',', '', mysqli_real_escape_string($connect, $_POST['red'])) : '0';
$weight = isset($_POST['weight']) ? mysqli_real_escape_string($connect, $_POST['weight']) : '1';
$archive = isset($_POST['archive']) ? mysqli_real_escape_string($connect, $_POST['archive']) : 'No';
$kpiCascade = isset($_POST['kpiCascade']) ? mysqli_real_escape_string($connect, $_POST['kpiCascade']) : '';
$indPhoto = isset($_POST['indPhoto']) ? mysqli_real_escape_string($connect, $_POST['indPhoto']) : '';
$sort = 3000;

switch($objectType)
{
	case "organization":
	{
		if($tree_edit == "editMe")
		{
			// Use transaction for atomic updates
			mysqli_autocommit($connect, FALSE);

			$update_org = mysqli_query($connect, "UPDATE organization SET name = '$tree_name', mission = '$kpiMission', vision = '$kpiVision', valuez = '$kpiValues', weight='$weight' WHERE id = '$tree_id'");
			if (!$update_org) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error updating organization: " . mysqli_error($connect));
				die("Error updating organization");
			}

			$update_tree = mysqli_query($connect, "UPDATE tree SET name='$tree_name' WHERE id='$tree_id'");
			if (!$update_tree) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error updating tree: " . mysqli_error($connect));
				die("Error updating tree");
			}

			$update_permissions = mysqli_query($connect, "UPDATE uc_permissions SET name='$tree_name' WHERE orgId='$tree_id'");
			if (!$update_permissions) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error updating permissions: " . mysqli_error($connect));
				die("Error updating permissions");
			}

			mysqli_commit($connect);
			mysqli_autocommit($connect, TRUE);
		}
		else
		{
			// Use transaction for atomic inserts
			mysqli_autocommit($connect, FALSE);

			$insert_org = mysqli_query($connect, "INSERT INTO organization (id, name, mission, vision, valuez, cascadedfrom, weight)
			VALUES ('$tree_id', '$tree_name', '$kpiMission', '$kpiVision', '$kpiValues', '$tree_parent', '$weight')");
			if (!$insert_org) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error inserting organization: " . mysqli_error($connect));
				die("Error inserting organization");
			}

			$insert_tree = mysqli_query($connect, "INSERT INTO tree (id, name, parent, type, linked, sort) VALUES ('$tree_id', '$tree_name', '$tree_parent', 'organization', 'no', '3000')");
			if (!$insert_tree) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error inserting tree: " . mysqli_error($connect));
				die("Error inserting tree");
			}

			$permission = mysqli_query($connect, "SELECT MAX(id) as id FROM uc_permissions");
			if (!$permission) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error getting max permission ID: " . mysqli_error($connect));
				die("Error getting max permission ID");
			}

			$permission = mysqli_fetch_array($permission);
			$permissionId = ($permission[0] ? $permission[0] : 0) + 1;

			$insert_permission = mysqli_query($connect, "INSERT INTO uc_permissions (id, name, orgId, status, callFunction, url, home) VALUES ('$permissionId', '$tree_name', '$tree_id', 'Active', '', '', 'No')");
			if (!$insert_permission) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error inserting permission: " . mysqli_error($connect));
				die("Error inserting permission");
			}

			mysqli_commit($connect);
			mysqli_autocommit($connect, TRUE);
		}
		echo $tree_id;
		break;
	}
	case "perspective":
	{
		if($tree_edit == "editMe")
		{
			// Use transaction for atomic updates
			mysqli_autocommit($connect, FALSE);

			$update_perspective = mysqli_query($connect, "UPDATE perspective SET name = '$tree_name', weight='$weight' WHERE id = '$tree_id'");
			if (!$update_perspective) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error updating perspective: " . mysqli_error($connect));
				die("Error updating perspective");
			}

			$update_tree = mysqli_query($connect, "UPDATE tree SET name='$tree_name' WHERE id='$tree_id'");
			if (!$update_tree) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error updating tree: " . mysqli_error($connect));
				die("Error updating tree");
			}

			mysqli_commit($connect);
			mysqli_autocommit($connect, TRUE);
		}
		else
		{
			// Use transaction for atomic inserts and weight calculations
			mysqli_autocommit($connect, FALSE);

			$insert_perspective = mysqli_query($connect, "INSERT INTO perspective (id, name, parentId, weight) VALUES ('$tree_id', '$tree_name', '$tree_parent', '$weight')");
			if (!$insert_perspective) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error inserting perspective: " . mysqli_error($connect));
				die("Error inserting perspective");
			}

			$insert_tree = mysqli_query($connect, "INSERT INTO tree (id, name, parent, type, linked, sort) VALUES ('$tree_id', '$tree_name', '$tree_parent', 'perspective', 'no', '3000')");
			if (!$insert_tree) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error inserting tree: " . mysqli_error($connect));
				die("Error inserting tree");
			}

			// Calculate and update weights for all perspectives with the same parent
			$query = mysqli_query($connect, "SELECT COUNT(id) AS idCount FROM perspective WHERE parentId = '$tree_parent'");
			if (!$query) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error counting perspectives: " . mysqli_error($connect));
				die("Error counting perspectives");
			}

			$weightCount = mysqli_fetch_assoc($query);
			if ($weightCount["idCount"] > 0) {
				$calculated_weight = 1 / $weightCount["idCount"];
				$calculated_weight = number_format((float)$calculated_weight, 2, '.', '');

				$update_weights = mysqli_query($connect, "UPDATE perspective SET weight = '$calculated_weight' WHERE parentId = '$tree_parent'");
				if (!$update_weights) {
					mysqli_rollback($connect);
					file_put_contents("error.txt", "Error updating perspective weights: " . mysqli_error($connect));
					die("Error updating perspective weights");
				}
			}

			mysqli_commit($connect);
			mysqli_autocommit($connect, TRUE);
		}
		echo $tree_id;
		break;
	}
	case "objective":
	{
		if($tree_edit == "editMe")
		{
			// Use transaction for atomic updates
			mysqli_autocommit($connect, FALSE);

			$update_objective = mysqli_query($connect, "UPDATE objective SET name = '$tree_name', description = '$kpiDescription', outcome = '$kpiOutcome',
			owner='$kpiOwner', cascadedfrom = '$kpiCascade', weight='$weight', sortColumn='$sort', tags='$kpiOwnerTags' WHERE id = '$tree_id'");
			if (!$update_objective) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error updating objective: " . mysqli_error($connect));
				die("Error updating objective");
			}

			$update_tree = mysqli_query($connect, "UPDATE tree SET name='$tree_name' WHERE id='$tree_id'");
			if (!$update_tree) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error updating tree: " . mysqli_error($connect));
				die("Error updating tree");
			}

			$update_kra_link = mysqli_query($connect, "UPDATE objective_kra_map SET kraId = '$kraListId' WHERE objectiveId='$tree_id'");
			if(mysqli_affected_rows($connect) == 0) 
			{
				$insert_kra_link = mysqli_query($connect, "INSERT INTO objective_kra_map (id, objectiveId, kraId) VALUES (default, '$tree_id', '$kraListId')");
				if (!$insert_kra_link) {
					mysqli_rollback($connect);
					file_put_contents("error.txt", "Error inserting KRA link: " . mysqli_error($connect));
					die("Error inserting KRA link");
				}
			} 
			elseif (!$update_kra_link) 
			{
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error updating KRA link: " . mysqli_error($connect));
				die("Error updating KRA link");
			}

			mysqli_commit($connect);
			mysqli_autocommit($connect, TRUE);
		}
		else
		{
			// Use transaction for atomic inserts and weight calculations
			mysqli_autocommit($connect, FALSE);

			$insert_objective = mysqli_query($connect, "INSERT INTO objective
			(id, name, description, outcome, linkedObject, owner, cascadedfrom, weight, sortColumn, tags) VALUES
			('$tree_id', '$tree_name', '$kpiDescription', '$kpiOutcome', '$tree_parent', '$kpiOwner', '$kpiCascade', '$weight', '$sort', '$kpiOwnerTags')");
			if (!$insert_objective) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error inserting objective: " . mysqli_error($connect));
				die("Error inserting objective");
			}

			$insert_tree = mysqli_query($connect, "INSERT INTO tree (id, name, parent, type, linked, sort) VALUES ('$tree_id', '$tree_name', '$tree_parent', 'objective', 'no', '3000')");
			if (!$insert_tree) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error inserting tree: " . mysqli_error($connect));
				die("Error inserting tree");
			}

			// Calculate and update weights for all objectives with the same linkedObject
			$query = mysqli_query($connect, "SELECT COUNT(id) AS idCount FROM objective WHERE linkedObject = '$tree_parent'");
			if (!$query) {
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error counting objectives: " . mysqli_error($connect));
				die("Error counting objectives");
			}

			$weightCount = mysqli_fetch_assoc($query);
			if ($weightCount["idCount"] > 0) {
				$calculated_weight = 1 / $weightCount["idCount"];
				$calculated_weight = number_format((float)$calculated_weight, 2, '.', '');

				$update_weights = mysqli_query($connect, "UPDATE objective SET weight = '$calculated_weight' WHERE linkedObject = '$tree_parent'");
				if (!$update_weights) {
					mysqli_rollback($connect);
					file_put_contents("error.txt", "Error updating objective weights: " . mysqli_error($connect));
					die("Error updating objective weights");
				}
			}

			//Link the new objective to a KRA
			$insert_kra_link = mysqli_query($connect, "INSERT INTO objective_kra_map (id, objectiveId, kraId) VALUES (default, '$tree_id', '$kraListId')");
				if (!$insert_kra_link) {
					mysqli_rollback($connect);
					file_put_contents("error.txt", "Error inserting KRA link: " . mysqli_error($connect));
					die("Error inserting KRA link");
				}

			mysqli_commit($connect);
			mysqli_autocommit($connect, TRUE);
		}
		echo $tree_id;
		break;
	}
	case "measure":
	{
		if($tree_edit == "editMe")
		{
			$originalName_query = mysqli_query($connect, "SELECT name FROM measure WHERE id = '$tree_id'") or file_put_contents("error.txt", "Error getting original measure name: " . mysqli_error($connect));

			$originalName = mysqli_fetch_assoc($originalName_query);
			$originalName = $originalName["name"];

			$similarKPIs = mysqli_query($connect, "SELECT id, owner FROM measure WHERE name = '$originalName' AND tags = (SELECT tags FROM measure WHERE id = '$tree_id')") or file_put_contents("error.txt", "Error getting similar KPIs: " . mysqli_error($connect));
				
			while($row = mysqli_fetch_array($similarKPIs))
			{
				$kpiId = $row["id"];
				//$kpiOwnerFromDB = $row["owner"];

				$update_measure = mysqli_query($connect, "UPDATE measure SET name = '$tree_name', calendarType = '$collectionFrequency', measureType = '$measureType',
				description = '$kpiDescription', dataType='$dataType', aggregationType = '$aggregationType', owner='$kpiOwner', updater='$kpiOwner', red='$red', blue='$blue', green = '$green', darkGreen = '$darkGreen', gaugeType = '$thresholdType', archive = '$archive', tags = '$kpiOwnerTags' WHERE id = '$kpiId'") or file_put_contents("error.txt", "Error updating measure: " . mysqli_error($connect));
					
				$update_tree = mysqli_query($connect, "UPDATE tree SET name='$tree_name' WHERE id='$kpiId'") or file_put_contents("error.txt", "Error updating tree: " . mysqli_error($connect));
			}
		}

		else
		{
			$staffArray = json_decode($kpiOwnerTags, true);
			$totalStaff = 0;
			$idArray = array();

			// Check if JSON decode was successful and we have valid data
			if($staffArray && is_array($staffArray) && count($staffArray) > 0)
			{
				$totalStaff = sizeof($staffArray);
				$count = 0;
				foreach($staffArray as $items)
				{
					if(isset($items["value"]) && !empty($items["value"]))
					{
						$idArray[$count] = mysqli_real_escape_string($connect, $items["value"]);
						$count++;
					}
				}
				$totalStaff = $count; // Update total to actual valid entries
			}

			// If no valid staff found, create measure with default owner
			if($totalStaff == 0)
			{
				//file_put_contents("measure_debug.txt", "No valid staff found, using default owner\n", FILE_APPEND);
				// Use tree_parent as default owner (the parent objective/perspective owner)
				try {
					save_bulk_kpi($tree_parent, $tree_name, $collectionFrequency, $kpiDescription, $thresholdType, $kpiOwner, $kpiOwner, $measureType, $dataType, $aggregationType, $darkGreen, $blue, $green, $red, $archive, $sort, $kpiOwnerTags);
					// Get the last created measure ID to return
					$last_id_query = mysqli_query($connect, "SELECT MAX(CAST(SUBSTRING(id, 4, length(id)-3) AS UNSIGNED)) as max_id FROM measure");
					if ($last_id_query) {
						$last_id_result = mysqli_fetch_array($last_id_query);
						$tree_id = "kpi" . $last_id_result['max_id'];
					}
				} catch (Exception $e) {
					file_put_contents("error.txt", "Error creating measure: " . $e->getMessage());
					die("Error creating measure");
				}
			}
			else
			{
				// Create measure for each valid staff member
				for($i = 0; $i < $totalStaff; $i++)
				{
					try {
						save_bulk_kpi($idArray[$i], $tree_name, $collectionFrequency, $kpiDescription, $thresholdType, $idArray[$i], $idArray[$i], $measureType, $dataType, $aggregationType, $darkGreen, $blue, $green, $red, $archive, $sort, $kpiOwnerTags);
					} catch (Exception $e) {
						file_put_contents("error.txt", "Error creating measure for staff " . $idArray[$i] . ": " . $e->getMessage());
						// Continue with other staff members
					}
				}
				// Get the last created measure ID to return
				$last_id_query = mysqli_query($connect, "SELECT MAX(CAST(SUBSTRING(id, 4, length(id)-3) AS UNSIGNED)) as max_id FROM measure");
				if ($last_id_query) {
					$last_id_result = mysqli_fetch_array($last_id_query);
					$tree_id = "kpi" . $last_id_result['max_id'];
				}
			}
		}
		echo $tree_id;
		break;
	}
	case "individual":
	{
		if($tree_edit == "editMe")
		{
			// Update existing individual name and cascadedFrom (parent department) if needed
			mysqli_autocommit($connect, FALSE);
			
			$update_individual = mysqli_query($connect, "UPDATE individual SET name = '$tree_name', cascadedFrom = '$tree_parent' WHERE id = '$tree_id'");
			if(!$update_individual){
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error updating individual: " . mysqli_error($connect));
				die("Error updating individual");
			}
			
			$update_tree = mysqli_query($connect, "UPDATE tree SET name = '$tree_name', parent='$tree_parent' WHERE id = '$tree_id'");
			if(!$update_tree){
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error updating tree (individual): " . mysqli_error($connect));
				die("Error updating tree");
			}

			mysqli_commit($connect);
			mysqli_autocommit($connect, TRUE);
		}
		else
		{
			// Insert new individual (if not already present)
			mysqli_autocommit($connect, FALSE);
			
			// Avoid duplicate inserts
			$exists_check = mysqli_query($connect, "SELECT id FROM individual WHERE id = '$tree_id'");
			if(!$exists_check){
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error checking individual existence: " . mysqli_error($connect));
				die("Error checking individual existence");
			}
			if(mysqli_num_rows($exists_check) == 0){
				$insert_individual = mysqli_query($connect, "INSERT INTO individual (id, name, cascadedFrom, photo) VALUES ('$tree_id', '$tree_name', '$tree_parent', '$indPhoto')");
				if(!$insert_individual){
					mysqli_rollback($connect);
					file_put_contents("error.txt", "Error inserting individual: " . mysqli_error($connect));
					die("Error inserting individual");
				}
			}
			// Insert into tree (avoid duplicates)
			$tree_exists = mysqli_query($connect, "SELECT id FROM tree WHERE id = '$tree_id'");
			if(!$tree_exists){
				mysqli_rollback($connect);
				file_put_contents("error.txt", "Error checking tree existence for individual: " . mysqli_error($connect));
				die("Error checking tree existence");
			}
			if(mysqli_num_rows($tree_exists) == 0){
				$insert_tree = mysqli_query($connect, "INSERT INTO tree (id, name, parent, type, linked, sort) VALUES ('$tree_id', '$tree_name', '$tree_parent', 'individual', 'no', '3000')");
				if(!$insert_tree){
					mysqli_rollback($connect);
					file_put_contents("error.txt", "Error inserting individual into tree: " . mysqli_error($connect));
					die("Error inserting individual into tree");
				}
			}
			// Optionally update user's department in uc_users table
			@mysqli_query($connect, "UPDATE uc_users SET department = '$tree_parent' WHERE user_id = '$tree_id'");
			// Add individual to permissions (thought this was being handled by save-individual.php, but let's ensure it)
			mysqli_query($connect, "INSERT INTO uc_permissions (id, name, orgId, status, callFunction, url, home, icon) VALUES (default, '$tree_name', '$tree_id', 'Active', NULL, NULL, 'No', NULL)") or file_put_contents("saveIndividualerror.txt", "Error => ".mysqli_error($connect));

			mysqli_commit($connect);
			mysqli_autocommit($connect, TRUE);
		}
		echo $tree_id;
		break;
	}
}
?>