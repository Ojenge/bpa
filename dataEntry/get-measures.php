<?php
include_once("../config/config_mysqli.php");
include_once("../admin/models/config.php");

@$objectId = $_POST['objectId'];
@$objectPeriod = $_POST['objectPeriod'];
@$objectDate = $_POST['objectDate'];

if(strlen($objectDate) == 4) 
{
	$year = $objectDate;
	$month = date("m");
	$objectDate = $objectDate."-12-30"; //upgrade to php 8 gave an error of wrong date format if date isn't complete. LTK 29Dec2024 1740hrs at Amboseli Serena :-)
}
else 
{
	$year = date("Y", strtotime($objectDate));
	$month = date("m", strtotime($objectDate));
	$objectDate = $objectDate."-30"; //upgrade to php 8 gave an error of wrong date format if date isn't complete. LTK 29Dec2024 1740hrs at Amboseli Serena :-)
}

//@$objectId = 'ind2';
//@$objectPeriod = 'Months';
//@$objectDate = '2023-08-27';

$table = "measuremonths";

$get_gauge = "SELECT id, name, gaugeType, calendarType, measureType, green, tags 
FROM measure 
WHERE linkedObject = '$objectId' AND measureType = 'Standard KPI' AND archive = 'No'
OR updater = '$objectId' AND measureType = 'Standard KPI' AND archive = 'No'
OR owner = '$objectId' AND measureType = 'Standard KPI' AND archive = 'No'
ORDER BY linkedObject ASC";
$get_gauge_result = mysqli_query($GLOBALS["___mysqli_ston"], $get_gauge);
//if(mysqli_num_rows(get_gauge_result) == 0) 
//{
//	echo "No measures";
//}
$currentUser = "ind".$loggedInUser->user_id;
echo "<div class='border border-primary rounded-3' style='overflow:hidden;'><table class='table table-hover table-responsive table-bordered table-sm table-condensed table-striped'>";
	echo "<tr class='table-primary'>";
		echo "<th>Measure</th>";
		echo "<th style='white-space:nowrap;'>Owner</th>";
		/*echo "<th>Updater</th>";*///I don't think this makes a difference from years of experience. The owner and updater generally tend to be the same person apart from places where the champion updates for most staff.
		echo "<th>Actual</th>";
		echo "<th>Target</th>";
		echo "<th colspan='2' style='text-align:center;'>Score</th>";
		echo "<th>Frequency</th>";
		echo "<th style='white-space:nowrap;'>Last Update</th>";
		echo "<th>Status</th>";
		if($objectId == $currentUser) echo "<th></th>";
		echo "<th>Comments</th>";
	echo "</tr>";
echo "<tbody>";
while($row = mysqli_fetch_assoc($get_gauge_result))
{
	$calendarType = $row['calendarType'];
	switch($calendarType)
	{
		case "Daily":
		{
			$table = "measuredays";
			break;	
		}
		case "Weekly":
		{
			$table = "measureweeks";
			break;	
		}
		case "Monthly":
		{
			$table = "measuremonths";
			break;	
		}
		case "Quarterly":
		{
			$table = "measurequarters";
			break;	
		}
		case "Bi-Annually":
		{
			$table = "measurehalfyear";
			break;	
		}
		case "Yearly":
		{
			$table = "measureyears";
			break;	
		}
	}
	$kpiId = $row['id'];
	echo "<tr>";
	echo "<td>".$row["name"]."</td>";
	$indMeasure_query = "SELECT date, actual, green, 3score FROM $table WHERE measureId = '$kpiId' AND date <= '$objectDate' ORDER BY date DESC LIMIT 1";
	//$indMeasure_query = "SELECT AVG(3score) FROM $table WHERE measureId = '$kpiId' AND date LIKE '$objectDate%'";
	$indMeasure_result = mysqli_query($GLOBALS["___mysqli_ston"], $indMeasure_query);
	$indMeasure_count = mysqli_num_rows($indMeasure_result);
	$indKpiRow = mysqli_fetch_assoc($indMeasure_result);
	
	//"Measure Type".$row["measureType"];
	$measureId = preg_replace('/^\D+/', '', $row["id"]);
	
	$getUpdater = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT 
					 u.display_name as 'updater', 
					 u1.display_name as 'owner'
				FROM `measure` m
				JOIN `uc_users` u on u.user_id = m.updater
				JOIN `uc_users` u1 on u1.user_id = m.owner
				WHERE m.id = '$kpiId'");
	$updaterRow = mysqli_fetch_assoc($getUpdater);
	
	echo "<td style='white-space:nowrap;'>".$updaterRow["owner"]."</td>";
	/*echo "<td>".$updaterRow["updater"]."</td>";*/
	if($indMeasure_count < 1) echo "<td></td>";
	else echo "<td>".number_format($indKpiRow["actual"])."</td>";
	echo "<td>".number_format($row["green"])."</td>";
	
	if($indMeasure_count < 1) echo "<td style='text-align:center;' colspan='2'>"."No Score"."</td>"; 
	else 
	{
		if($indKpiRow["3score"] < 3.3) 
		{
			echo "<td class='border-end-0'><div class='red3d'></div></td><td class='border-start-0' style='text-align:center;'>".round($indKpiRow["3score"],2)."</td>";//red
		}
		else if($indKpiRow["3score"] >= 3.3 && $indKpiRow["3score"] < 6.67) 
		{
			echo "<td class='border-end-0'><div class='yellow3d'></div></td><td class='border-start-0' style='text-align:center;'>".round($indKpiRow["3score"],2)."</td>";//yellow
		}
		else if($indKpiRow["3score"] >= 6.67) 
		{
			echo "<td class='border-end-0'><div class='green3d'></div></td><td class='border-start-0' style='text-align:center;'>".round($indKpiRow["3score"],2)."</td>";//green
		}
		else 
		{
			echo "<td class='border-end-0'><div class='grey3d'></div></td><td class='border-start-0' style='text-align:center;'>".round($indKpiRow["3score"],2)."</td>";//grey
		}
	}
	
	echo "<td style='white-space:nowrap;'>".$calendarType."</td>";
	//if($indKpiRow["date"] < "2000-01-01") echo "<td></td>";
	if($indMeasure_count < 1) echo "<td></td>";
	else
	{
		switch($table)
		{
			case "measuremonths":
			{
				echo "<td style='white-space:nowrap;'>".date("M Y",strtotime($indKpiRow["date"]))."</td>";
				break;
			}
			case "measurequarters":
			{
				$month = date("m",strtotime($indKpiRow["date"]))."</td>";
				if($month > 0 && $month < 4)
				echo "<td style='white-space:nowrap;'>"."Q1 ".date("Y",strtotime($indKpiRow["date"]))."</td>";
				else if ($month > 3 && $month < 7)
				echo "<td style='white-space:nowrap;'>"."Q2 ".date("Y",strtotime($indKpiRow["date"]))."</td>";
				else if ($month > 6 && $month < 10)
				echo "<td style='white-space:nowrap;'>"."Q3 ".date("Y",strtotime($indKpiRow["date"]))."</td>";
				else
				echo "<td style='white-space:nowrap;'>"."Q4 ".date("Y",strtotime($indKpiRow["date"]))."</td>";
				break;
			}
			case "measurehalfyear":
			{
				$month = date("m",strtotime($indKpiRow["date"]));
				if($month > 1 && $month < 7)
				echo "<td style='white-space:nowrap;'>"."HY1 ".date("Y",strtotime($indKpiRow["date"]))."</td>";
				else 
				echo "<td style='white-space:nowrap;'>"."HY2 ".date("Y",strtotime($indKpiRow["date"]))."</td>";
				break;
			}
			case "measureyears":
			{
				echo "<td style='white-space:nowrap;'>".date("Y",strtotime($indKpiRow["date"]))."</td>";
				break;
			}
			default:
			{
				echo "<td style='white-space:nowrap;'>".$indKpiRow["date"]."</td>";
				break;	
			}
		}
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
					onchange='updateStatus(\"$kpiId\", \"measure\", this.value)'>
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
		echo "<a href='#' onClick='myBulkEntry(".$measureId.")'>Update</a>";
		echo " <button class='btn btn-outline-info btn-sm' onClick='openTagDialog(\"".$kpiId."\", \"measure\")'>Manage Tags</button>";
		echo "</td>";
	}
	
	$conversationQuery = mysqli_query($GLOBALS["___mysqli_ston"],"SELECT conversation.note AS note, conversation.date AS date, uc_users.display_name AS commenter 
	FROM conversation, uc_users 
	WHERE conversation.linkedId = '$kpiId' 
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
				<input onchange="updateComment('<?php echo $kpiId; ?>', '<?php echo "ind".$loggedInUser->user_id; ?>', this.value)" 
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
        	<input onchange="updateComment('<?php echo $kpiId; ?>', '<?php echo "ind".$loggedInUser->user_id; ?>', this.value)" style="width:100%; height:50px; padding:5px; border:1px solid #ccc; overflow-y:scroll;">
            <button style="float:right" class='btn btn-outline-primary btn-sm' onClick='refreshDataEntryPage()'>Save</button>
        </td>
		<?php
	}
	echo "</tr>";
}
echo "</tbody>";
echo "</table></div>";
?>