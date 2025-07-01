<?php

include_once("../config/config_mysqli.php");
//if(isset($_POST['objectId']))
//{
	
	$objectId = $_POST['objectId'];
	$objectType = $_POST['objectType'];
	$objectPeriod = $_POST['objectPeriod'];
	$objectDate = $_POST['objectDate'];
	@$valuesCount = $_POST['valuesCount'];
	//$objectDate = strtotime($objectDate);
	//$objectDate = date("Y-m-d", strtotime("+1 month", $objectDate));
	
	file_put_contents("xmr.txt", "Id: $objectId, Type: $objectType, Period: $objectPeriod, Date: $objectDate, Count: $valuesCount");
	

	$objectId = "kpi1974";
	$objectType = "measure";
	$objectType = "Universal Health Coverage";
	$objectPeriod = "months";
	$objectDate = "2020-03";
	$valuesCount = 12;
	$objectDate = strtotime($objectDate);
	$objectDate = date("Y-m-d", strtotime("+1 month", $objectDate));
	
	$getCalendar = mysqli_query($connect, "SELECT calendarType FROM measure WHERE id = '$objectId'");
	$getCalendar = mysqli_fetch_assoc($getCalendar);
	$getCalendar = $getCalendar["calendarType"];

switch($getCalendar)
{
	case 'Daily':
	{
		$objectDate = date("Y-m-d",strtotime($objectDate));
		$table = 'measuredays';
		if($objectPeriod == 'months')
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m-d", strtotime("+1 month", $objectDate));	
		}
		else if($objectPeriod == 'quarters')
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m-d", strtotime("+3 month", $objectDate));	
		}
		else if($objectPeriod == 'years')
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m-d", strtotime("+2 year", $objectDate));	
		}
		// || $objectPeriod == 'quarters' || $objectPeriod == 'halfyear' || $objectPeriod == 'years'
		break;	
	}
	case 'Weekly':
	{
		$table = 'measureweeks';
		$objectDate = date("Y-m-d",strtotime($objectDate));
		break;	
	}
	case 'Monthly':
	{
		$table = 'measuremonths';
		$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
		break;	
	}
	case 'Quarterly':
	{
		//$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
		$table = 'measurequarters';
		break;	
	}
	case 'Bi-Annually':
	{
		$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
		$table = 'measurehalfyear';
		break;	
	}
	case 'Yearly':
	{
		$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
		$table = 'measureyears';
		break;	
	}
}
/*
	switch($objectPeriod)
	{
		case "days":
		{
			$objectDate = date("Y-m-d",strtotime($objectDate));
			$table = "measuredays";
			break;
		}
		case "weeks":
		{
			$objectDate = date("Y-m-d",strtotime($objectDate));
			$table = "measureweeks";
			break;
		}
		case "months":
		{
			$objectDate = date("Y-m",strtotime($objectDate));
			$table = "measuremonths";
			break;
		}
		case "quarters":
		{
			$objectDate = date("Y-m",strtotime($objectDate));
			$table = "measurequarters";
			break;
		}	
		case "halfYears":
		{
			$objectDate = date("Y-m",strtotime($objectDate));
			$table = "measurehalfyear";
			break;
		}
		case "years":
		{
			$objectDate = date("Y",strtotime($objectDate));
			$table = "measureyears";
			break;
		}
	}
	*/
	
	if($objectType == "individual")
	{
		$measure_id_query="SELECT id FROM measure where linkedObject = '$objectId'";
		$measure_id_result=mysqli_query($connect, $measure_id_query);
		$count = 0;
		$count_two = 0;
		//$cum_avg_score = 0;
		while($row = mysqli_fetch_assoc($measure_id_result))
		{
			$measure_id_array[$count] = $row['id'];
			//$measure_id = $row['id'];
			//$objective_score_query = "SELECT AVG(measuremonths.score) FROM measuremonths WHERE measureId LIKE '$measure_id'";
			//$objective_score_result=mysqli_query($connect, $objective_score_query);
			//$objective_score = mysqli_fetch_assoc($objective_score_result);
			//$cum_avg_score = $cum_avg_score + $objective_score['AVG(measuremonths.score)'];
			//echo "<br>".$cum_avg_score = $cum_avg_score/$count;
			$count++;
			// End of average score		
		}
		$where_in = implode("','", $measure_id_array);
		$where_in = "'".$where_in."'";
		$objective_query="SELECT AVG(score), date FROM $table WHERE measureId IN ($where_in) AND date <= '$objectDate%' GROUP BY date ORDER BY date";
		$objective_result=mysqli_query($connect, $objective_query);
		$row_count = mysqli_num_rows($objective_result);
		
		echo "[";
		while($row = mysqli_fetch_assoc($objective_result))
		{
			//$data["id"] = (int)$row['measureId'];
			$data["score"] = (float)$row["AVG(score)"];
			$data["date"] = date('M-y',strtotime($row["date"]));
			//$data["red"] = (int)$row["red"];
			//$data["yellow"] = (int)$row["yellow"];
			//$data["green"] = (int)$row["green"];
			//$data["darkgreen"] = (int)$row["darkgreen"];
			$data = json_encode($data);
			echo $data;
			if($count_two < $row_count-1)echo ",";
			//echo $count_two." and ".$row_count;
			$data = NULL;
			$count_two++;
		}
		//echo ",{\"Score\":".$cum_avg_score."}";
		echo "]";
	}
	else
	{
		
		$objective_query="SELECT a.UNPL AS UNPL, a.LNPL AS LNPL, a.centralLine AS centralLine, a.date AS date, a.actual AS actual FROM (SELECT UNPL, LNPL, centralLine, date, actual FROM $table WHERE measureId = '$objectId' AND date <= '$objectDate%' ORDER BY date DESC LIMIT 0, $valuesCount) a ORDER BY date ASC";
		//$objective_query="SELECT * FROM measuremonths where measureId = 4 ORDER BY date";
		$objective_result=mysqli_query($connect, $objective_query) or file_put_contents("xmrError.txt", "Error => ".mysqli_error());
		$row_count = mysqli_num_rows($objective_result);
		
		$count = 0;
		//echo "[".$table.', '.$objectId.', '.$objectDate;
		echo "[";
		//$data = array();
		while($row = mysqli_fetch_assoc($objective_result))
		{
			//$data["id"] = (int)$row['id'];
			$data["unpl"] = (float)$row["UNPL"];
			$data["lnpl"] = (float)$row["LNPL"];
			$data["actual"] = (float)$row["actual"];
			$data["centralLine"] = (float)$row["centralLine"];
			$data["date"] = date('M-y',strtotime($row["date"]));
			//$data["green"] = (int)$row["green"];
			//$data["date"] = date('F',strtotime($row['date']));
			$data = json_encode($data);
			echo $data;
			$data = null;
			$count++;
			if($count < $row_count) echo ",";
		}
		echo "]";
	}
	flush();
//}
exit;
?>