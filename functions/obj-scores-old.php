<?php
function daysAsIs($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType)
{
	$measure_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Daily'";
	$measure_id_result=mysqli_query($connect, $measure_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($measure_id_result))
	{
		$measure_id_array[$count] = $row['id'];
		$count++;
	}
	$where_in = implode("','", $measure_id_array);
	$where_in = "'".$where_in."'";

	$count = 0;
	$j = $valuesCount;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'days';
		$data["date"] = date('Y-m-d',strtotime($variable.$objectDate));
		$newdate = date('Y-m-d',strtotime($variable.$objectDate));
		
		$kpi_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuredays WHERE measureId IN ($where_in) AND date LIKE '%$newdate%'");
		$kpi_result = mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");

		$score = $kpi_result["AVG(3score)"];
		//$data["score"] = round($score, 2);
		
		if($score != NULL) $data["score"] = round($score, 2);
		else $data["score"] = NULL;
		
		$data["red"] = 3.33;
		$data["green"] = 6.67;
		$data["darkGreen"] = 10.0;
				
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j--;
	}
	echo "]";	
}
function weeksAsIs($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType)
{
	$measure_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Weekly'";
	$measure_id_result=mysqli_query($connect, $measure_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($measure_id_result))
	{
		$measure_id_array[$count] = $row['id'];
		$count++;
	}
	$where_in = @implode("','", $measure_id_array);
	$where_in = "'".$where_in."'";
	
	$days_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Daily'";
	$days_id_result=mysqli_query($connect, $days_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($days_id_result))
	{
		$days_id_array[$count] = $row['id'];
		$count++;
	}
	$days_in = @implode("','", $days_id_array);
	$days_in = "'".$days_in."'";

	$count = 0;
	$j = $valuesCount;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'weeks';
		$data["date"] = date('Y-m-d',strtotime($variable.$objectDate));
		$newdate = date('Y-m-d',strtotime($variable.$objectDate));
		$weekDate = date('W',strtotime($variable.$objectDate));
		$kpi_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureweeks WHERE measureId IN ($where_in) AND date LIKE '$newdate%'");
		$kpi_result = mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
		
		$days_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuredays WHERE measureId IN ($days_in) AND WEEK(date) LIKE '$weekDate'");
		$days_result = mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");

		if($kpi_result["AVG(3score)"] == NULL) $score = $days_result["AVG(3score)"];
		else if($days_result["AVG(3score)"] == NULL) $score = $kpi_result["AVG(3score)"];
		else
		$score = ($kpi_result["AVG(3score)"] + $days_result["AVG(3score)"])/2;
		
		if($score != NULL) $data["score"] = round($score, 2);
		else $data["score"] = NULL;
		
		//$data["scoreItemWeek"] = round($kpi_result["AVG(3score)"],2);
		//$data["scoreItemDay"] = round($days_result["AVG(3score)"],2);
		
		$data["red"] = 3.33;
		$data["green"] = 6.67;
		$data["darkGreen"] = 10.0;
				
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j--;
	}
	echo "]";
}
function monthsAsIs($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType)
{
	$year_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Yearly'";
	$year_id_result=mysqli_query($connect, $year_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($year_id_result))
	{
		$year_id_array[$count] = $row['id'];
		$count++;
	}
	$year_in = @implode("','", $year_id_array);
	$year_in = "'".$year_in."'";

	$halfyear_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'HalfYearly'";
	$halfyear_id_result=mysqli_query($connect, $halfyear_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($halfyear_id_result))
	{
		$halfyear_id_array[$count] = $row['id'];
		$count++;
	}
	$halfyear_in = @implode("','", $halfyear_id_array);
	$halfyear_in = "'".$halfyear_in."'";
	
	$quarters_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Quarterly'";
	$quarters_id_result=mysqli_query($connect, $quarters_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($quarters_id_result))
	{
		$quarters_id_array[$count] = $row['id'];
		$count++;
	}
	$quarters_in = @implode("','", $quarters_id_array);
	$quarters_in = "'".$quarters_in."'";
	
	$months_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Monthly'";
	$months_id_result=mysqli_query($connect, $months_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($months_id_result))
	{
		$months_id_array[$count] = $row['id'];
		$count++;
	}
	$months_in = @implode("','", $months_id_array);
	$months_in = "'".$months_in."'";
	
	$weeks_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Weekly'";
	$weeks_id_result=mysqli_query($connect, $weeks_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($weeks_id_result))
	{
		$weeks_id_array[$count] = $row['id'];
		$count++;
	}
	$weeks_in = @implode("','", $weeks_id_array);
	$weeks_in = "'".$weeks_in."'";
	
	$days_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Daily'";
	$days_id_result=mysqli_query($connect, $days_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($days_id_result))
	{
		$days_id_array[$count] = $row['id'];
		$count++;
	}
	$days_in = @implode("','", $days_id_array);
	$days_in = "'".$days_in."'";

	$count = 0;
	$j = $valuesCount;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'month';
		$data["date"] = date('F',strtotime($variable.$objectDate));
		$newdate = date('Y-m',strtotime($variable.$objectDate));
		
		//$variable = "-".$j.'years';
		//$newdate = date('Y',strtotime($variable.$objectDate));
		$data["date"] = $newdate;
		
		$yearDate = date('Y',strtotime($variable.$objectDate));
		$kpi_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureyears WHERE measureId IN ($year_in) AND date LIKE '$yearDate%'");
		$kpi_result = @mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
		//file_put_contents("objScoresError.txt", "\r\n measureyear average is: ".$kpi_result['AVG(3score)'].' => '.$year_in.' and date is '.$yearDate, FILE_APPEND);
		
		$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$newdate%'");
		$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
		
		$quarters_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurequarters WHERE measureId IN ($quarters_in) AND date LIKE '$newdate%'");
		$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
				
		$month_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuremonths WHERE measureId IN ($months_in) AND date LIKE '$newdate%'");
		$months_result = @mysqli_fetch_assoc($month_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
		
		$weeks_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureweeks WHERE measureId IN ($weeks_in) AND date LIKE '$newdate%'");
		$weeks_result = @mysqli_fetch_assoc($weeks_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
		
		$days_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuredays WHERE measureId IN ($days_in) AND date LIKE '$newdate%'");
		$days_result = @mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");

		if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL) 
		$score = $days_result["AVG(3score)"];
		else if ($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $weeks_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $months_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $quarters_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $halfYear_result["AVG(3score)"];
		else if($quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $kpi_result["AVG(3score)"];
		/**********************************************************************************************************************************/
		else if($weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/2;
		else if($kpi_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"])/2;
		else if($kpi_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $months_result["AVG(3score)"])/2;
		else if($weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/2;
		else if($weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/2;
		else if($kpi_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $months_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($months_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL) 
		$score = ($months_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/2;	
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($quarters_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/2;	
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL) 
		$score = ($quarters_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/2;
		/**********************************************************************************************************************************/
		else if($days_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL) 
		$score = ($months_result["AVG(3score)"] + $quarters_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $quarters_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $months_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL)
		$score = ($kpi_result["AVG(3score)"] + $quarters_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL)
		$score = ($kpi_result["AVG(3score)"] + $months_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($months_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;

		else if($days_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL)
		$score = ($weeks_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($weeks_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($weeks_result["AVG(3score)"] + $months_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/3;
		
		else if($weeks_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $quarters_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;
		
		else if($weeks_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $months_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;
		
		else if($weeks_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL)
		$score = ($months_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;
		
		else if($weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($months_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($weeks_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $months_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/3;
		
		else if($months_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;

		else if($months_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/3;
		
		else if($quarters_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $months_result["AVG(3score)"])/3;
		/**********************************************************************************************************************************/
		else if($kpi_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/4;
		else if($days_result["AVG(3score)"] == NULL)
		$score = ($kpi_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/4;
		else if($weeks_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/4;
		else if($months_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/4;
		else if($quarters_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $months_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/4;
		/**********************************************************************************************************************************/
		else $score = ($kpi_result["AVG(3score)"] + $quarters_result["AVG(3score)"] + $months_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $days_result["AVG(3score)"])/5;
		
		if($score != NULL) $data["score"] = round($score, 2);
		else $data["score"] = NULL;
		$data["scoreItemMonth"] = round($kpi_result["AVG(3score)"],2);
		$data["scoreItemWeek"] = round($weeks_result["AVG(3score)"],2);
		$data["scoreItemDay"] = round($days_result["AVG(3score)"],2);
		
		$data["red"] = 3.33;
		$data["green"] = 6.67;
		$data["darkGreen"] = 10.0;
				
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j--;
	}
	echo "]";
}
function monthsAsIsOld($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType)
{
	$months_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Monthly'";
	$months_id_result=mysqli_query($connect, $months_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($months_id_result))
	{
		$months_id_array[$count] = $row['id'];
		$count++;
	}
	$months_in = @implode("','", $months_id_array);
	$months_in = "'".$months_in."'";
	
	$weeks_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Weekly'";
	$weeks_id_result=mysqli_query($connect, $weeks_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($weeks_id_result))
	{
		$weeks_id_array[$count] = $row['id'];
		$count++;
	}
	$weeks_in = @implode("','", $weeks_id_array);
	$weeks_in = "'".$weeks_in."'";
	
	$days_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Daily'";
	$days_id_result=mysqli_query($connect, $days_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($days_id_result))
	{
		$days_id_array[$count] = $row['id'];
		$count++;
	}
	$days_in = @implode("','", $days_id_array);
	$days_in = "'".$days_in."'";

	$count = 0;
	$j = $valuesCount;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'month';
		$data["date"] = date('F',strtotime($variable.$objectDate));
		$newdate = date('Y-m',strtotime($variable.$objectDate));
		$yearDate = date('Y',strtotime($variable.$objectDate));
		
		$year_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureyears WHERE measureId IN ($months_in) AND date LIKE '$yearDate%'");
		$year_result = @mysqli_fetch_assoc($year_query) or file_put_contents("myObjScores.txt", "\r\nCould not execute kpi query", FILE_APPEND);
		
		$kpi_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuremonths WHERE measureId IN ($months_in) AND date LIKE '$newdate%'");
		$kpi_result = @mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
		//echo "Score:".$kpi_result["AVG(3score)"].$months_in;
		$monthDate = date('Y-m',strtotime($variable.$objectDate));
		
		$weeks_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureweeks WHERE measureId IN ($weeks_in) AND date LIKE '$monthDate%'");
		$weeks_result = @mysqli_fetch_assoc($weeks_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
		
		$days_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuredays WHERE measureId IN ($days_in) AND date LIKE '$monthDate%'");
		$days_result = @mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");

		if($kpi_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL) $score = $days_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) $score = $weeks_result["AVG(3score)"];
		else if($weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) $score = $kpi_result["AVG(3score)"];
		else if($weeks_result["AVG(3score)"] == NULL) $score = ($days_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL) $score = ($weeks_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/2;
		else if($kpi_result["AVG(3score)"] == NULL) $score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"])/2;
		else $score = ($kpi_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $days_result["AVG(3score)"])/3;
		
		if($score != NULL) $data["score"] = round($score, 2);
		else $data["score"] = NULL;
		
		//$data["scoreItemMonth"] = round($kpi_result["AVG(3score)"],2);
		//$data["scoreItemWeek"] = round($weeks_result["AVG(3score)"],2);
		//$data["scoreItemDay"] = round($days_result["AVG(3score)"],2);
		
		$data["red"] = 3.33;
		$data["green"] = 6.67;
		$data["darkGreen"] = 10.0;
				
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j--;
	}
	echo "]";	
}
function quartersAsIs($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType)
{
	$quarters_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Quarterly'";
	$quarters_id_result=mysqli_query($connect, $quarters_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($quarters_id_result))
	{
		$quarters_id_array[$count] = $row['id'];
		$count++;
	}
	$quarters_in = @implode("','", $quarters_id_array);
	$quarters_in = "'".$quarters_in."'";
	
	$months_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Monthly'";
	$months_id_result=mysqli_query($connect, $months_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($months_id_result))
	{
		$months_id_array[$count] = $row['id'];
		$count++;
	}
	$months_in = @implode("','", $months_id_array);
	$months_in = "'".$months_in."'";
	
	$weeks_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Weekly'";
	$weeks_id_result=mysqli_query($connect, $weeks_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($weeks_id_result))
	{
		$weeks_id_array[$count] = $row['id'];
		$count++;
	}
	$weeks_in = @implode("','", $weeks_id_array);
	$weeks_in = "'".$weeks_in."'";
	
	$days_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Daily'";
	$days_id_result=mysqli_query($connect, $days_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($days_id_result))
	{
		$days_id_array[$count] = $row['id'];
		$count++;
	}
	$days_in = @implode("','", $days_id_array);
	$days_in = "'".$days_in."'";

	$count = 0;
	$month_count = 0;
	$j = $valuesCount * 3;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'month';
		$variablePlusFour = $j + 3;
		$variablePlusFour = "-".$variablePlusFour.'month';
		$dateToQuarter = date('Y-m',strtotime($variable.$objectDate));
		$tempDate = quarterLabels($dateToQuarter);
		$data["date"] = $tempDate;
		//$dateToQuarter = date('Y-m', strtotime($dateToQuarter));
		$datePlusFour = date('Y-m',strtotime($variablePlusFour.$objectDate));
		
		$kpi_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurequarters 
		WHERE measureId IN ($quarters_in) AND date LIKE '$dateToQuarter%'");
		$kpi_result = @mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
		
		$month_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuremonths 
		WHERE measureId IN ($months_in) AND date >= '$dateToQuarter%' AND date < '$datePlusFour%'");
		$months_result = @mysqli_fetch_assoc($month_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
		
		$weeks_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureweeks 
		WHERE measureId IN ($weeks_in) AND date >= '$dateToQuarter%' AND date < '$datePlusFour%'");
		$weeks_result = @mysqli_fetch_assoc($weeks_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
		
		$days_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuredays 
		WHERE measureId IN ($days_in) AND date >= '$dateToQuarter%' AND date < '$datePlusFour%'");
		$days_result = @mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");

		if($kpi_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL) 
		$score = $days_result["AVG(3score)"];
		
		else if ($kpi_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $weeks_result["AVG(3score)"];
		
		else if($kpi_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $months_result["AVG(3score)"];
		
		else if($months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $kpi_result["AVG(3score)"];
		
		else if($weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/2;
		
		else if($days_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/2;
		
		else if($kpi_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"])/2;
		
		else if($kpi_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = ($months_result["AVG(3score)"] + $weeks_result["AVG(3score)"])/2;
		
		else if($kpi_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $months_result["AVG(3score)"])/2;
		
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL) 
		$score = ($kpi_result["AVG(3score)"] + $months_result["AVG(3score)"])/2;
		
		else if($kpi_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $months_result["AVG(3score)"])/3;
		else if($days_result["AVG(3score)"] == NULL)
		$score = ($kpi_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $months_result["AVG(3score)"])/3;
		else if($weeks_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $months_result["AVG(3score)"])/3;
		else if($months_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/3;
		
		else $score = ($kpi_result["AVG(3score)"] + $months_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $days_result["AVG(3score)"])/4;
		
		if($score != NULL) $data["score"] = round($score, 2);
		else $data["score"] = NULL;
		
		$data["scoreItemMonth"] = round($kpi_result["AVG(3score)"],2);
		$data["scoreItemWeek"] = round($weeks_result["AVG(3score)"],2);
		$data["scoreItemDay"] = round($days_result["AVG(3score)"],2);
		
		$data["red"] = 3.33;
		$data["green"] = 6.67;
		$data["darkGreen"] = 10.0;
				
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j = $j - 3;
	}
	echo "]";
}
function halfYearsAsIs($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType)
{
	$halfyear_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'HalfYearly'";
	$halfyear_id_result=mysqli_query($connect, $halfyear_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($halfyear_id_result))
	{
		$halfyear_id_array[$count] = $row['id'];
		$count++;
	}
	$halfyear_in = @implode("','", $halfyear_id_array);
	$halfyear_in = "'".$halfyear_in."'";
	
	$quarters_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Quarterly'";
	$quarters_id_result=mysqli_query($connect, $quarters_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($quarters_id_result))
	{
		$quarters_id_array[$count] = $row['id'];
		$count++;
	}
	$quarters_in = @implode("','", $quarters_id_array);
	$quarters_in = "'".$quarters_in."'";
	
	$months_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Monthly'";
	$months_id_result=mysqli_query($connect, $months_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($months_id_result))
	{
		$months_id_array[$count] = $row['id'];
		$count++;
	}
	$months_in = @implode("','", $months_id_array);
	$months_in = "'".$months_in."'";
	
	$weeks_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Weekly'";
	$weeks_id_result=mysqli_query($connect, $weeks_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($weeks_id_result))
	{
		$weeks_id_array[$count] = $row['id'];
		$count++;
	}
	$weeks_in = @implode("','", $weeks_id_array);
	$weeks_in = "'".$weeks_in."'";
	
	$days_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Daily'";
	$days_id_result=mysqli_query($connect, $days_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($days_id_result))
	{
		$days_id_array[$count] = $row['id'];
		$count++;
	}
	$days_in = @implode("','", $days_id_array);
	$days_in = "'".$days_in."'";

	$count = 0;
	$j = $valuesCount * 6;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'month';
		$variablePlusSix = $j + 6;
		$variablePlusSix = "-".$variablePlusSix.'month';
		$dateToHalfYear = date('Y-m',strtotime($variable.$objectDate));
		$tempDate = halfYearLabels($dateToHalfYear);
		$data["date"] = $tempDate;
		$newdate = date('Y-m-d',strtotime($variable.$objectDate));
		$datePlusSix = date('Y-m',strtotime($variablePlusSix.$objectDate));
		$month_count = $month_count + 6;
		
		$monthDate = date('Y-m',strtotime($variable.$objectDate));
		
		$kpi_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$newdate%'");
		$kpi_result = @mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
		
		$quarters_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurequarters 
		WHERE measureId IN ($quarters_in) AND date >= '$newdate%' AND date < '$datePlusSix%'");
		$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
				
		$month_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuremonths 
		WHERE measureId IN ($months_in) AND date >= '$newdate%' AND date < '$datePlusSix%'");
		$months_result = @mysqli_fetch_assoc($month_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
		
		$weeks_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureweeks 
		WHERE measureId IN ($weeks_in AND date >= '$newdate%' AND date < '$datePlusSix%'");
		$weeks_result = @mysqli_fetch_assoc($weeks_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
		
		$days_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuredays 
		WHERE measureId IN ($days_in) AND date >= '$newdate%' AND date < '$datePlusSix%'");
		$days_result = @mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");

		if($kpi_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL) 
		$score = $days_result["AVG(3score)"];
		
		else if ($kpi_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $weeks_result["AVG(3score)"];
		
		else if($kpi_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $months_result["AVG(3score)"];
		
		else if($months_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $quarters_result["AVG(3score)"];
		
		else if($months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $kpi_result["AVG(3score)"];
		
		else if($weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/2;
		else if($kpi_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"])/2;
		else if($kpi_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $months_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/2;
		else if($kpi_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $months_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL) 
		$score = ($months_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL) 
		$score = ($months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/2;	
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL) 
		$score = ($quarters_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/2;
		
		else if($days_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL)
		$score = ($kpi_result["AVG(3score)"] + $months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL)
		$score = ($weeks_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL)
		$score = ($weeks_result["AVG(3score)"] + $months_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/3;
		
		else if($weeks_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL)
		$score = ($kpi_result["AVG(3score)"] + $months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($weeks_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $months_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/3;
		
		else if($months_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/3;
		
		else if($quarters_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $months_result["AVG(3score)"])/3;
		
		else if($kpi_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/4;
		else if($days_result["AVG(3score)"] == NULL)
		$score = ($kpi_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/4;
		else if($weeks_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/4;
		else if($months_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/4;
		else if($quarters_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $months_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/4;
		
		else $score = ($kpi_result["AVG(3score)"] + $quarters_result["AVG(3score)"] + $months_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $days_result["AVG(3score)"])/5;
		
		if($score != NULL) $data["score"] = round($score, 2);
		else $data["score"] = NULL;
		$data["scoreItemMonth"] = round($kpi_result["AVG(3score)"],2);
		$data["scoreItemWeek"] = round($weeks_result["AVG(3score)"],2);
		$data["scoreItemDay"] = round($days_result["AVG(3score)"],2);
		
		$data["red"] = 3.33;
		$data["green"] = 6.67;
		$data["darkGreen"] = 10.0;
				
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j = $j - 6;
	}
	echo "]";
}
function yearsAsIs($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType)
{
	$year_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Yearly'";
	$year_id_result=mysqli_query($connect, $year_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($year_id_result))
	{
		$year_id_array[$count] = $row['id'];
		$count++;
	}
	$year_in = @implode("','", $year_id_array);
	$year_in = "'".$year_in."'";

	$halfyear_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'HalfYearly'";
	$halfyear_id_result=mysqli_query($connect, $halfyear_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($halfyear_id_result))
	{
		$halfyear_id_array[$count] = $row['id'];
		$count++;
	}
	$halfyear_in = @implode("','", $halfyear_id_array);
	$halfyear_in = "'".$halfyear_in."'";
	
	$quarters_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Quarterly'";
	$quarters_id_result=mysqli_query($connect, $quarters_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($quarters_id_result))
	{
		$quarters_id_array[$count] = $row['id'];
		$count++;
	}
	$quarters_in = @implode("','", $quarters_id_array);
	$quarters_in = "'".$quarters_in."'";
	
	$months_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Monthly'";
	$months_id_result=mysqli_query($connect, $months_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($months_id_result))
	{
		$months_id_array[$count] = $row['id'];
		$count++;
	}
	$months_in = @implode("','", $months_id_array);
	$months_in = "'".$months_in."'";
	
	$weeks_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Weekly'";
	$weeks_id_result=mysqli_query($connect, $weeks_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($weeks_id_result))
	{
		$weeks_id_array[$count] = $row['id'];
		$count++;
	}
	$weeks_in = @implode("','", $weeks_id_array);
	$weeks_in = "'".$weeks_in."'";
	
	$days_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Daily'";
	$days_id_result=mysqli_query($connect, $days_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($days_id_result))
	{
		$days_id_array[$count] = $row['id'];
		$count++;
	}
	$days_in = @implode("','", $days_id_array);
	$days_in = "'".$days_in."'";

	$count = 0;
	$j = $valuesCount;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'years';
		$newdate = date('Y',strtotime($variable.$objectDate));
		$data["date"] = $newdate;
		
		$kpi_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureyears WHERE measureId IN ($year_in) AND date LIKE '$newdate%'");
		$kpi_result = @mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
		//file_put_contents("objScoresError.txt", "\r\n measureyear average is: ".$kpi_result['AVG(3score)'].' => '.$year_in, FILE_APPEND);
		
		$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$newdate%'");
		$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
		
		$quarters_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurequarters WHERE measureId IN ($quarters_in) AND date LIKE '$newdate%'");
		$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
				
		$month_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuremonths WHERE measureId IN ($months_in) AND date LIKE '$newdate%'");
		$months_result = @mysqli_fetch_assoc($month_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
		
		$weeks_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureweeks WHERE measureId IN ($weeks_in) AND date LIKE '$newdate%'");
		$weeks_result = @mysqli_fetch_assoc($weeks_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");
		
		$days_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuredays WHERE measureId IN ($days_in) AND date LIKE '$newdate%'");
		$days_result = @mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query");

		if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL) 
		$score = $days_result["AVG(3score)"];
		else if ($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $weeks_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $months_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $quarters_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $halfYear_result["AVG(3score)"];
		else if($quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $kpi_result["AVG(3score)"];
		/**********************************************************************************************************************************/
		else if($weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/2;
		else if($kpi_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"])/2;
		else if($kpi_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $months_result["AVG(3score)"])/2;
		else if($weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/2;
		else if($weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/2;
		else if($kpi_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $months_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($months_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/2;
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL) 
		$score = ($months_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/2;	
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($quarters_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/2;	
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL) 
		$score = ($quarters_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/2;
		/**********************************************************************************************************************************/
		else if($days_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL) 
		$score = ($months_result["AVG(3score)"] + $quarters_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $quarters_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $months_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL) 
		$score = ($weeks_result["AVG(3score)"] + $months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL)
		$score = ($kpi_result["AVG(3score)"] + $quarters_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL)
		$score = ($kpi_result["AVG(3score)"] + $months_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($months_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;

		else if($days_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL)
		$score = ($weeks_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($weeks_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($days_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($weeks_result["AVG(3score)"] + $months_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/3;
		
		else if($weeks_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $quarters_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;
		
		else if($weeks_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $months_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;
		
		else if($weeks_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL)
		$score = ($months_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;
		
		else if($weeks_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($months_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($weeks_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $months_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/3;
		
		else if($months_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $halfYear_result["AVG(3score)"])/3;

		else if($months_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/3;
		
		else if($months_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/3;
		
		else if($quarters_result["AVG(3score)"] == NULL && $kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $months_result["AVG(3score)"])/3;
		/**********************************************************************************************************************************/
		else if($kpi_result["AVG(3score)"] == NULL) 
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/4;
		else if($days_result["AVG(3score)"] == NULL)
		$score = ($kpi_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/4;
		else if($weeks_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $months_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/4;
		else if($months_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $kpi_result["AVG(3score)"] + $quarters_result["AVG(3score)"])/4;
		else if($quarters_result["AVG(3score)"] == NULL)
		$score = ($days_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $months_result["AVG(3score)"] + $kpi_result["AVG(3score)"])/4;
		/**********************************************************************************************************************************/
		else $score = ($kpi_result["AVG(3score)"] + $quarters_result["AVG(3score)"] + $months_result["AVG(3score)"] + $weeks_result["AVG(3score)"] + $days_result["AVG(3score)"])/5;
		
		if($score != NULL) $data["score"] = round($score, 2);
		else $data["score"] = NULL;
		$data["scoreItemMonth"] = round($kpi_result["AVG(3score)"],2);
		$data["scoreItemWeek"] = round($weeks_result["AVG(3score)"],2);
		$data["scoreItemDay"] = round($days_result["AVG(3score)"],2);
		
		$data["red"] = 3.33;
		$data["green"] = 6.67;
		$data["darkGreen"] = 10.0;
				
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j--;
	}
	echo "]";
}
?>