<?php
error_reporting(0); //To show all - change 0 to E_ALL
ini_set('display_errors', 0); //To show all - change 0 to 1
require_once("../admin/models/config.php");
include_once("../config/config_mysqli.php");

@$objectId = $_POST['objectId'];
@$objectPeriod = $_POST['objectPeriod'];
@$objectDate = $_POST['objectDate'];

//file_put_contents("track.txt", "objectId = $objectId; objectDate = $objectDate");

function numberToRoman($number) {
    $map = array('m' => 1000, 'cm' => 900, 'd' => 500, 'cd' => 400, 'c' => 100, 'xc' => 90, 'l' => 50, 'xl' => 40, 'x' => 10, 'ix' => 9, 'v' => 5, 'iv' => 4, 'i' => 1);
    $returnValue = '';
    while ($number > 0) {
        foreach ($map as $roman => $int) {
            if($number >= $int) {
                $number -= $int;
                $returnValue .= $roman;
                break;
            }
        }
    }
    return $returnValue;
}
if(strlen($objectDate) == 4) 
{
	$year = $objectDate;
	$month = date("m");
}
else 
{
	$year = date("Y", strtotime($objectDate));
	$month = date("m", strtotime($objectDate));
}
$initiative_query="SELECT id, name, deliverable, startDate, dueDate, completionDate, parent, tags FROM initiative WHERE projectManager = '$objectId' AND archive != 'Yes'";
$initiative_result = mysqli_query($GLOBALS["___mysqli_ston"], $initiative_query);
$initiative_count = mysqli_num_rows($initiative_result);

$currentUser = "ind".$loggedInUser->user_id;
echo "<div class='border border-primary rounded-3' style='overflow:hidden;'><table class='table table-hover table-responsive table-bordered table-sm table-condensed table-striped'>";
	echo "<tr class='table-primary'>";
		echo "<th></th>";
		echo "<th>Activity/Task</th>";
		echo "<th>Deliverable</th>";
		echo "<th style='white-space:nowrap;'>Objective(s) Impacted</th>";
		echo "<th colspan='2' style='text-align:center; white-space:nowrap;'>Due Date</th>";
		echo "<th>Status</th>";
		if($objectId == $currentUser) echo "<th></th>";
		echo "<th>Comments</th>";
	echo "</tr>";
echo "<tbody>";
$initiativeCounter = 1;
while($row = mysqli_fetch_assoc($initiative_result))
{
	//Check if parent is owned by current user. If yes, skip and display as a task below, otherwise, display it as an initiative for this user. LTK 15May2021 0832Hrs
	$parentInitiative = $row["parent"];
	$parentInitiative = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT projectManager FROM initiative WHERE id = '$parentInitiative'");
	$parentInitiative = mysqli_fetch_assoc($parentInitiative);
	
	if($parentInitiative["projectManager"] == $objectId)
	{
		//Skip. Do not display as Initiative but as a task which is covered below.	
	}
	else
	{
		echo "<tr>";
		echo "<td>".$initiativeCounter."</td>";
		//echo "<td>".$row["id"]."</td>";
		echo "<td>".$row["name"]."</td>";
		echo "<td>".$row["deliverable"]."</td>";
		//$ind_row["dueDate".$count] = $row["dueDate"];
		//$ind_row["startDate".$count] = $row["startDate"];
		//$ind_row["completionDate".$count] = $row["completionDate"];
		if($row["dueDate"] <= date("Y-m-d") && $row["completionDate"] == NULL)
		//$color = "#eca1a6";//red
		$color = "red3d";
		else if ($row["dueDate"] < $row["completionDate"] && $row["completionDate"] != NULL)
		//$color = "#ffef96";//yellow
		$color = "yellow3d";
		else if ($row["completionDate"] <= $row["dueDate"] && $row["completionDate"] != NULL)
		//$color = "#b5e7a0";//green
		$color = "green3d";
		else $color = "grey3d";
		
		$link_id = $row["id"];
		//echo "<strong>".$link_id."</strong><br>";
		$linkedObjective_query = "SELECT objective.name 
		FROM objective, initiativeimpact 
		WHERE initiativeimpact.initiativeid = '$link_id' 
		AND objective.id = initiativeimpact.linkedobjectid";
		$linkedObjective_result = mysqli_query($GLOBALS["___mysqli_ston"], $linkedObjective_query);
		$count = mysqli_num_rows($linkedObjective_result);
		if($count < 1) echo "<td></td>";
		else
		{
			echo "<td>";
			while($linkedObjective_row = mysqli_fetch_assoc($linkedObjective_result))
			{
				echo $linkedObjective_row["name"];
			}
			echo "</td>";
		}
		if($row["dueDate"] == NULL || $row["dueDate"] == '0000-00-00' || $row["dueDate"] == '1970-01-01')
		{
			$color = "grey3d";
			echo "<td class='border-end-0'><div class='$color'></div></td><td class='border-start-0' style='text-align:center; white-space:nowrap;'>No Due Date</td>";
		}
		else
		{
			$dueDate = date("d M Y",strtotime($row["dueDate"]));
			echo "<td class='border-end-0'><div class='$color'></div></td><td class='border-start-0' style='text-align:center; white-space:nowrap;'>".$dueDate."</td>";
		}
		// Display tags/status with dropdown
		$tags = json_decode(isset($row['tags']) ? $row['tags'] : '[]', true);
		$currentStatus = '';
		if ($tags && is_array($tags)) {
			foreach ($tags as $tag) {
				if (isset($tag['status'])) {
					$currentStatus = $tag['status'];
					break; // Use the first status found
				}
			}
		}
		
		$statusHtml = '';
		if ($currentStatus) {
			$statusClass = '';
			$statusText = '';
			switch ($currentStatus) {
				case 'approved':
					$statusClass = 'badge bg-success';
					$statusText = 'Approved';
					break;
				case 'needs_review':
					$statusClass = 'badge bg-warning';
					$statusText = 'Needs Review';
					break;
				default:
					$statusClass = 'badge bg-secondary';
					$statusText = $currentStatus;
			}
			$statusHtml = "<span class='$statusClass me-1'>$statusText</span>";
		} else {
			$statusHtml = "<span class='badge bg-light text-dark'>No Status</span>";
		}
		
		// Create dropdown for status selection
		$dropdownHtml = "
			<div style='display: flex; align-items: center; gap: 5px;'>
				$statusHtml
				<select class='form-select form-select-sm' style='width: auto; min-width: 120px;' 
						onchange='updateStatus(\"".$row["id"]."\", \"initiative\", this.value)'>
					<option value=''>Change Status</option>
					<option value='approved'" . ($currentStatus === 'approved' ? ' selected' : '') . ">Approved</option>
					<option value='needs_review'" . ($currentStatus === 'needs_review' ? ' selected' : '') . ">Needs Review</option>
					<option value='remove'>Remove Status</option>
				</select>
			</div>
		";
		
		echo "<td>$dropdownHtml</td>";
		
		if($objectId == $currentUser) {
			echo "<td>";
			echo "<a href='#' onClick='editInitiative(".$row["id"].")'>Update</a>";
			echo " <button class='btn btn-outline-info btn-sm' onClick='openTagDialog(\"".$row["id"]."\", \"initiative\")'>Manage Tags</button>";
			echo "</td>";
		}
		
		$conversationQuery = mysqli_query($GLOBALS["___mysqli_ston"],"SELECT conversation.note AS note, conversation.date AS date, uc_users.display_name AS commenter 
		FROM conversation, uc_users 
		WHERE conversation.linkedId = '$link_id' 
		AND uc_users.user_id = conversation.senderId
		ORDER BY date DESC");
	
		$conversationCount = mysqli_num_rows($conversationQuery);
		$counter = 0;
		if($conversationCount > 0)
		{
		?>
		<td>
		<?php
			while($conversationResult = mysqli_fetch_array($conversationQuery))
			{ 
				if($counter == 0) 
				{
					?>
					<input onchange="updateComment('<?php echo $link_id; ?>', '<?php echo "ind".$loggedInUser->user_id; ?>', this.value)" 
					style="width:100%; padding:5px; border:1px solid #ccc; overflow-y:scroll;" value="<?php echo $conversationResult["note"]; ?>"/>
					<i>Comment by: <?php echo $conversationResult["commenter"]; ?></i>
					<button style="float:right" class='btn btn-outline-primary btn-sm' onClick='refreshDataEntryPage()'>Save</button>
					<?php
					$counter++;
				}
				else
				{
					if($counter == 1) 
					{
						echo "<br><br>Previous Comments"; 
					}
					$date = date("d M Y",strtotime($conversationResult["date"]));
					echo "<br><i>".$counter." ".$conversationResult["note"]." by <strong>".$conversationResult["commenter"]."</strong> on ".$date."</i>"; 
					$counter++;
				}
			}
			?>
		</td>
			<?php
		}
		else 
		{
			?>
			<td>
				<input onchange="updateComment('<?php echo $link_id; ?>', '<?php echo "ind".$loggedInUser->user_id; ?>', this.value)" style="width:100%; height:50px; padding:5px; border:1px solid #ccc; overflow-y:scroll;">
				<button style="float:right" class='btn btn-outline-primary btn-sm' onClick='refreshDataEntryPage()'>Save</button>
			</td>
			<?php
		}
		echo "</tr>";
		
		//Get child tasks
		//$parent = $row["id"];
		$task_query = "SELECT id, name, deliverable, startDate, dueDate, completionDate, tags FROM initiative WHERE parent = '$link_id'";
		$task_result = mysqli_query($GLOBALS["___mysqli_ston"], $task_query);
		$task_count = mysqli_num_rows($task_result);
		$taskCounter = 1;
		if($task_count < 1) {}
		else
		{
			while($rowTask = mysqli_fetch_assoc($task_result))
			{
				$taskCounterRoman = numberToRoman($taskCounter);
				echo "<tr>";
				echo "<td></td>";
				echo "<td>&nbsp;&nbsp;&nbsp;".$taskCounterRoman.". ".$rowTask["name"]."</td>";
				echo "<td>".$rowTask["deliverable"]."</td>";
				//$ind_row["dueDate".$count] = $row["dueDate"];
				//$ind_row["startDate".$count] = $row["startDate"];
				//$ind_row["completionDate".$count] = $row["completionDate"];
				if($rowTask["dueDate"] <= date("Y-m-d") && $rowTask["completionDate"] == NULL)
				$color = "red3d";
				else if ($rowTask["dueDate"] < $rowTask["completionDate"] && $rowTask["completionDate"] != NULL)
				$color = "yellow3d";
				else if ($rowTask["completionDate"] <= $rowTask["dueDate"] && $rowTask["completionDate"] != NULL)
				$color = "green3d";
				else $color = "#FFFFFF";
				
				$link_idTask = $rowTask["id"];
				//echo "<strong>".$link_id."</strong><br>";
				$linkedObjective_query = "SELECT objective.name 
				FROM objective, initiativeimpact 
				WHERE initiativeimpact.initiativeid = '$link_idTask' 
				AND objective.id = initiativeimpact.linkedobjectid";
				$linkedObjective_result = mysqli_query($GLOBALS["___mysqli_ston"], $linkedObjective_query);
				$count = mysqli_num_rows($linkedObjective_result);
				if($count < 1) echo "<td></td>";
				else
				{
					echo "<td>";
					while($linkedObjective_row = mysqli_fetch_assoc($linkedObjective_result))
					{
						echo $linkedObjective_row["name"];
					}
					echo "</td>";
				}
				if($rowTask["dueDate"] == NULL || $rowTask["dueDate"] == '0000-00-00' || $rowTask["dueDate"] == '1970-01-01')
				{
					$color = "grey3d";
					echo "<td class='border-end-0' style='white-space:nowrap;'><div class='$color'></div></td><td class='border-start-0' style='text-align:center; white-space:nowrap;'>No Due Date</td>";
				}
				else
				{
					$dueDate = date("d M Y",strtotime($rowTask["dueDate"]));
					echo "<td class='border-end-0'><div class='$color'></div></td><td class='border-start-0' style='white-space:nowrap;'>".$dueDate."</td>";
				}
				// Display tags/status for tasks with dropdown
				$taskTags = json_decode(isset($rowTask['tags']) ? $rowTask['tags'] : '[]', true);
				$taskCurrentStatus = '';
				if ($taskTags && is_array($taskTags)) {
					foreach ($taskTags as $tag) {
						if (isset($tag['status'])) {
							$taskCurrentStatus = $tag['status'];
							break; // Use the first status found
						}
					}
				}
				
				$taskStatusHtml = '';
				if ($taskCurrentStatus) {
					$statusClass = '';
					$statusText = '';
					switch ($taskCurrentStatus) {
						case 'approved':
							$statusClass = 'badge bg-success';
							$statusText = 'Approved';
							break;
						case 'needs_review':
							$statusClass = 'badge bg-warning';
							$statusText = 'Needs Review';
							break;
						default:
							$statusClass = 'badge bg-secondary';
							$statusText = $taskCurrentStatus;
					}
					$taskStatusHtml = "<span class='$statusClass me-1'>$statusText</span>";
				} else {
					$taskStatusHtml = "<span class='badge bg-light text-dark'>No Status</span>";
				}
				
				// Create dropdown for task status selection
				$taskDropdownHtml = "
					<div style='display: flex; align-items: center; gap: 5px;'>
						$taskStatusHtml
						<select class='form-select form-select-sm' style='width: auto; min-width: 120px;' 
								onchange='updateStatus(\"".$rowTask["id"]."\", \"initiative\", this.value)'>
							<option value=''>Change Status</option>
							<option value='approved'" . ($taskCurrentStatus === 'approved' ? ' selected' : '') . ">Approved</option>
							<option value='needs_review'" . ($taskCurrentStatus === 'needs_review' ? ' selected' : '') . ">Needs Review</option>
							<option value='remove'>Remove Status</option>
						</select>
					</div>
				";
				
				echo "<td>$taskDropdownHtml</td>";
				
				if($objectId == $currentUser) {
					echo "<td>";
					echo "<a href='#' onClick='editInitiative(".$rowTask["id"].")'>Update</a>";
					echo " <button class='btn btn-outline-info btn-sm' onClick='openTagDialog(\"".$rowTask["id"]."\", \"initiative\")'>Manage Tags</button>";
					echo "</td>";
				}
				
				$conversationQuery = mysqli_query($GLOBALS["___mysqli_ston"],"SELECT conversation.note AS note, conversation.date AS date, uc_users.display_name AS commenter 
				FROM conversation, uc_users 
				WHERE conversation.linkedId = '$link_idTask' 
				AND uc_users.user_id = conversation.senderId
				ORDER BY date");
				
				$conversationCount = mysqli_num_rows($conversationQuery);
				$counter = 0;
				if($conversationCount > 0)
				{
				?>
				<td>
				<?php
					while($conversationResult = mysqli_fetch_array($conversationQuery))
					{ 
						if($counter == 0) 
						{
							?>
							<input onchange="updateComment('<?php echo $link_idTask; ?>', '<?php echo "ind".$loggedInUser->user_id; ?>', this.value)" 
							style="width:100%; padding:5px; border:1px solid #ccc; overflow-y:scroll;" value="<?php echo $conversationResult["note"]; ?>"/>
							<i>Comment by: <?php echo $conversationResult["commenter"]; ?></i>
							<button style="float:right" class='btn btn-outline-primary btn-sm' onClick='refreshDataEntryPage()'>Save</button>
							<?php
							$counter++;
						}
						else
						{
							if($counter == 1) 
							{
								echo "<br><br>Previous Comments"; 
							}
							$date = date("d M Y",strtotime($conversationResult["date"]));
							echo "<br><i>".$counter." ".$conversationResult["note"]." by <strong>".$conversationResult["commenter"]."</strong> on ".$date."</i>"; 
							$counter++;
						}
					}
					?>
				</td>
					<?php
				}
				else 
				{
					?>
					<td>
						<input onchange="updateComment('<?php echo $link_idTask; ?>', '<?php echo "ind".$loggedInUser->user_id; ?>', this.value)" style="width:100%; height:50px; padding:5px; border:1px solid #ccc; overflow-y:scroll;">
						<button style="float:right" class='btn btn-outline-primary btn-sm' onClick='refreshDataEntryPage()'>Save</button>
						<!--//Comments save automatically as they are captured. Therefore, this button doesn't really save but gives us a chance to refresh the page when a comment is made/edited.. LTK 15May2021 0750Hrs (After a refreshing Saturday 5km morning run :-))-->
					</td>
					<?php
				}
				
				echo "</tr>";
				$taskCounter++;
			}	
		}
		$initiativeCounter++;
	}
}
echo "</tbody>";
echo "</table></div>";
?>