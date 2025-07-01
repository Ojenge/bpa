<?php
include_once("config_mysqli.php");
include_once("functionsAdvocacy.php");
//if(isset($_POST['objectId']))
//{
//	if($objectId == $_POST['objectId']-1) exit;
	//$measureId = $_POST['measureId'];
	//$measureId = 6;
	@$advocacyCategory = $_POST['advocacyCategory'];
	//$advocacyCategory = "President";
	
	if($advocacyCategory == "All")
	{
		$initiativeList_query="SELECT name, id, status, type FROM advocacy ORDER BY type";
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
			$data["status"] = $row["status"];
			$data["color"] = getAdvocacyColor($row["status"]);
			$data["type"] = $row["type"];
			$data = json_encode($data);
			echo $data;
			$data = NULL;
			$count++;
			if($count-1 < $row_count) echo ",";	
		}
	}	
	else
	{
		$initiativeList_query="SELECT name, id, status, type FROM advocacy WHERE category = '$advocacyCategory' ORDER BY type";
		
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
			$data["status"] = $row["status"];
			$data["color"] = getAdvocacyColor($row["status"]);
			$data["type"] = $row["type"];
			$data = json_encode($data);
			echo $data;
			$data = NULL;
			$count++;
			if($count-1 < $row_count) echo ",";	
		}
	}
	echo "]";

	flush();
//}
exit;
?>