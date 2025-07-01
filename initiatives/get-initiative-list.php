<?php
include_once("../config/config_mysqli.php");

@$objectId = $_POST['objectId'];
@$objectType = $_POST["objectType"];

//@$objectId = 'ind2';
//@$objectType = 'individual';

$calendar = "months";

if($objectId == "All")
{
	$initiativeList_query="SELECT name, id FROM initiative ORDER BY name ASC";
	$initiativeList_result=mysqli_query($connect, $initiativeList_query);
	$row_count = @mysqli_num_rows($initiativeList_result);
	if ($row_count == null) exit;
	$count = 1;
	echo "[";
	//$data = array();
	while($row = mysqli_fetch_assoc($initiativeList_result))
	{
		$data["id"] = $row["id"];
		$data["name"] = $row["name"];
		
		$data = json_encode($data);
		echo $data;
		$data = NULL;
		$count++;
		if($count-1 < $row_count) echo ",";	
	}
}	
else
{
	$initiativeList_query="SELECT initiative.name, initiative.id, initiative.startDate, 
	initiative.completionDate, initiative.dueDate, initiative.projectManager
	FROM initiative, initiativeimpact, $objectType 
	WHERE $objectType.id = '$objectId'
	AND initiativeimpact.linkedobjectid = '$objectId'
	AND initiative.id = initiativeimpact.initiativeid
	ORDER BY initiative.name ASC";
	$totalDuration = 0;
	$initiativeList_result=mysqli_query($connect, $initiativeList_query);
	$row_count = @mysqli_num_rows($initiativeList_result);
	if ($row_count == NULL) exit;
	$count = 0;
	$data = array();
	while($row = mysqli_fetch_assoc($initiativeList_result))
	{
		$data[$count]["rateComplete"] = 0;
		if($row['dueDate']!= NULL && $row['startDate']!= NULL)
		{
			date_default_timezone_set('Africa/Nairobi');
			$due = new DateTime($row['dueDate']);
			$start = new DateTime($row['startDate']);
			$today = new DateTime(date('Y-m-d'));
			$totalInterval = $due->diff($start);
			$currentInterval = $today->diff($start);
			$totalDuration = $totalInterval->days;
			$currentDuration = $currentInterval->days;
			if($currentDuration >= $totalDuration) $data[$count]["rateComplete"] = 0;
			else
			{
				$data[$count]["rateComplete"] = 100*(($totalDuration - $currentDuration)/$totalDuration);
			}
			if($row['completionDate']!= NULL) $data[$count]["rateComplete"] = 100;
		}
		$data[$count]["id"] = $row["id"];
		$data[$count]["name"] = $row["name"];
		$data[$count]["year"] = date('Y',strtotime($row['startDate']));
		$month = date('m',strtotime($row['startDate']));
		$data[$count]["month"] = $month - 1;
		$data[$count]["day"] = date('d',strtotime($row['startDate']));
			$owner_id = $row["projectManager"];
			$owner_result = mysqli_query($connect, "SELECT display_name from uc_users WHERE user_id = '$owner_id'");
			$owner_row = mysqli_fetch_array($owner_result);
		$data[$count]["owner"] = $owner_row["display_name"];
		
		$durationStart = strtotime($row['startDate']);
		$durationEnd = strtotime($row['dueDate']);
		if($row['dueDate'] == NULL) $data[$count]["duration"] = 8;
		else
		$data[$count]["duration"] = $totalDuration*8;
		
		$minDateQuery = mysqli_query($connect, "SELECT MIN(initiative.startDate) as minDate
					FROM initiative, initiativeimpact, $objectType 
					WHERE $objectType.id = '$objectId'
					AND initiativeimpact.linkedobjectid = '$objectId'
					AND initiative.id = initiativeimpact.initiativeid");
		$minDateResult = mysqli_fetch_assoc($minDateQuery);
		
		$data[$count]["minYear"] = date('Y',strtotime($minDateResult["minDate"]));
		$data[$count]["minMonth"] = date('m',strtotime($minDateResult['minDate']."last month"));
		$data[$count]["minDay"] = date('d',strtotime($minDateResult['minDate']));
					
		$count++;
	}
	if($objectType == 'individual') //for individuals - get those projects they are owners but may not be directly linked to them on the tree
	{
		//echo "start $count for individual ".sizeof($data);
		$initiativeList_query="SELECT initiative.name, initiative.id, initiative.startDate, 
		initiative.completionDate, initiative.dueDate, initiative.projectManager
		FROM initiative 
		WHERE projectManager = '$objectId'
		ORDER BY initiative.name ASC";
		$totalDuration = 0;
		$initiativeList_result=mysqli_query($connect, $initiativeList_query);
		$row_count = @mysqli_num_rows($initiativeList_result);
		if ($row_count == NULL) exit;
		while($row = mysqli_fetch_assoc($initiativeList_result))
		{
			$data[$count]["rateComplete"] = 0;
			if($row['dueDate']!= NULL && $row['startDate']!= NULL)
			{
				date_default_timezone_set('Africa/Nairobi');
				$due = new DateTime($row['dueDate']);
				$start = new DateTime($row['startDate']);
				$today = new DateTime(date('Y-m-d'));
				$totalInterval = $due->diff($start);
				$currentInterval = $today->diff($start);
				$totalDuration = $totalInterval->days;
				$currentDuration = $currentInterval->days;
				if($currentDuration >= $totalDuration) $data[$count]["rateComplete"] = 0;
				else
				{
					$data[$count]["rateComplete"] = 100*(($totalDuration - $currentDuration)/$totalDuration);
				}
				if($row['completionDate']!= NULL) $data[$count]["rateComplete"] = 100;
			}
			//echo $row["name"]."<br>";
			$dataNames = array_column($data, 'name');
			if(in_array($row["name"], $dataNames)) {
				//echo "Found same initiative";
				}
			else
			{
				$data[$count]["id"] = $row["id"];
				$data[$count]["name"] = $row["name"];
				//$data["startDate"] = date('D M d Y H:i:s eP',strtotime($row['startDate']));;
				$data[$count]["year"] = date('Y',strtotime($row['startDate']));
				//$data["month"] = date('m',strtotime($row['startDate']."last month"));
				//$data["month"] = date('m',strtotime($row['startDate']));
				$month = date('m',strtotime($row['startDate']));
				$data[$count]["month"] = $month - 1;
				//$data["month"] = "9";
				$data[$count]["day"] = date('d',strtotime($row['startDate']));
				//$data["date"] = strtotime($row['jsStartDate']);
				$owner_id = $row["projectManager"];
				$owner_result = mysqli_query($connect, "SELECT display_name from uc_users WHERE user_id = '$owner_id'");
				$owner_row = mysqli_fetch_array($owner_result);
				$data[$count]["owner"] = $owner_row["display_name"];
				
				$durationStart = strtotime($row['startDate']);
				$durationEnd = strtotime($row['dueDate']);
				if($row['dueDate'] == NULL) $data[$count]["duration"] = 8;
				else
				$data[$count]["duration"] = $totalDuration*8;
				
				$minDateQuery = mysqli_query($connect, "SELECT MIN(initiative.startDate) as minDate
							FROM initiative 
							WHERE projectManager = '$objectId'");
				$minDateResult = mysqli_fetch_assoc($minDateQuery);
				
				$data[$count]["minYear"] = date('Y',strtotime($minDateResult["minDate"]));
				$data[$count]["minMonth"] = date('m',strtotime($minDateResult['minDate']."last month"));
				$data[$count]["minDay"] = date('d',strtotime($minDateResult['minDate']));
							
				$count++;
			}
		}
	}
	//echo "<br>end $count for individual ".sizeof($data);
}
//$data = array_unique($data, SORT_REGULAR); //removing duplicates for an associative array
//$data = array_keys(array_flip($data));
print_r(json_encode($data));
//flush();
//exit;
?>