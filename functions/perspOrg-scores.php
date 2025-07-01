<?php
function daysAsIs($objectId, $objectDate, $valuesCount)
{
	global $connect;
	$year_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Yearly'";
	$year_id_result=mysqli_query($connect, $year_id_query);
	$year_id_count=mysqli_num_rows($year_id_result); // Fixed: use result resource, not query string
	$count = 0;
	$count_two = 0;
	$year_id_array = array(); // Initialize array
	if($year_id_count < 1)
	{}
	else
	{
		while($row = mysqli_fetch_assoc($year_id_result))
		{
			$year_id_array[$count] = $row['id'];
			$count++;
		}
	}
	$year_in = !empty($year_id_array) ? implode("','", $year_id_array) : '';
	$year_in = "'".$year_in."'";
	$halfyear_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Bi-Annually'";
	$halfyear_id_result=mysqli_query($connect, $halfyear_id_query);
	$count = 0;
	$count_two = 0;
	$halfyear_id_array = array(); // Initialize array
	while($row = @mysqli_fetch_assoc($halfyear_id_result))
	{
		$halfyear_id_array[$count] = $row['id'];
		$count++;
	}
	$halfyear_in = !empty($halfyear_id_array) ? implode("','", $halfyear_id_array) : '';
	$halfyear_in = "'".$halfyear_in."'";
	
	$quarters_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Quarterly'";
	$quarters_id_result=mysqli_query($connect, $quarters_id_query);
	$count = 0;
	$count_two = 0;
	$quarters_id_array = array(); // Initialize array
	while($row = @mysqli_fetch_assoc($quarters_id_result))
	{
		$quarters_id_array[$count] = $row['id'];
		$count++;
	}
	$quarters_in = !empty($quarters_id_array) ? implode("','", $quarters_id_array) : '';
	$quarters_in = "'".$quarters_in."'";

	$months_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Monthly'";
	$months_id_result=mysqli_query($connect, $months_id_query);
	$count = 0;
	$count_two = 0;
	$months_id_array = array(); // Initialize array
	while($row = @mysqli_fetch_assoc($months_id_result))
	{
		$months_id_array[$count] = $row['id'];
		$count++;
	}
	$months_in = !empty($months_id_array) ? implode("','", $months_id_array) : '';
	$months_in = "'".$months_in."'";

	$weeks_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Weekly'";
	$weeks_id_result=mysqli_query($connect, $weeks_id_query);
	$count = 0;
	$count_two = 0;
	$weeks_id_array = array(); // Initialize array
	while($row = @mysqli_fetch_assoc($weeks_id_result))
	{
		$weeks_id_array[$count] = $row['id'];
		$count++;
	}
	$weeks_in = !empty($weeks_id_array) ? implode("','", $weeks_id_array) : '';
	$weeks_in = "'".$weeks_in."'";

	$days_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Daily'";
	$days_id_result=mysqli_query($connect, $days_id_query);
	$count = 0;
	$count_two = 0;
	$days_id_array = array(); // Initialize array
	while($row = @mysqli_fetch_assoc($days_id_result))
	{
		$days_id_array[$count] = $row['id'];
		$count++;
	}
	$days_in = !empty($days_id_array) ? implode("','", $days_id_array) : '';
	$days_in = "'".$days_in."'";

	$count = 0;
	$j = $valuesCount-1;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'days';
		$data["date"] = date('Y-m-d',strtotime($variable.$objectDate));
		$newdate = date('Y-m-d',strtotime($variable.$objectDate));
		
		//$variable = "-".$j.'years';
		//$newdate = date('Y',strtotime($variable.$objectDate));
		//$data["date"] = $newdate;
		
		$yearDate = date('Y',strtotime($variable.$objectDate));
		$halfYearDateTemp = strtotime($newdate);
		$halfYearDate5 = date('Y-m',strtotime("-5 month", $halfYearDateTemp));
		$halfYearDate4 = date('Y-m',strtotime("-4 month", $halfYearDateTemp));
		$halfYearDate3 = date('Y-m',strtotime("-3 month", $halfYearDateTemp));
		$halfYearDate2 = date('Y-m',strtotime("-2 month", $halfYearDateTemp));
		$halfYearDate1 = date('Y-m',strtotime("-1 month", $halfYearDateTemp));
		
		$kpi_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureyears WHERE measureId IN ($year_in) AND date LIKE '$yearDate%'");
		$kpi_result = @mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if (!$kpi_result) $kpi_result = array("AVG(3score)" => NULL);

		$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$newdate%'");
		$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if (!$halfYear_result) $halfYear_result = array("AVG(3score)" => NULL);
		if($halfYear_result["AVG(3score)"] == NULL)
		{
			$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$halfYearDate5%'");
			$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
			if (!$halfYear_result) $halfYear_result = array("AVG(3score)" => NULL);
			if($halfYear_result["AVG(3score)"] == NULL)
			{
				$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$halfYearDate4%'");
				$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				if (!$halfYear_result) $halfYear_result = array("AVG(3score)" => NULL);
				if($halfYear_result["AVG(3score)"] == NULL)
				{
					$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$halfYearDate3%'");
					$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
					if (!$halfYear_result) $halfYear_result = array("AVG(3score)" => NULL);
				}
				if($halfYear_result["AVG(3score)"] == NULL)
				{
					$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$halfYearDate2%'");
					$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
					if (!$halfYear_result) $halfYear_result = array("AVG(3score)" => NULL);
				}
				if($halfYear_result["AVG(3score)"] == NULL)
				{
				$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$halfYearDate1%'");
					$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
					if (!$halfYear_result) $halfYear_result = array("AVG(3score)" => NULL);
				}
			}
		}
		$quarters_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurequarters WHERE measureId IN ($quarters_in) AND date LIKE '$newdate%'");
		$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if (!$quarters_result) $quarters_result = array("AVG(3score)" => NULL);

		$month_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuremonths WHERE measureId IN ($months_in) AND date LIKE '$newdate%'");
		$months_result = @mysqli_fetch_assoc($month_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if (!$months_result) $months_result = array("AVG(3score)" => NULL);

		$weeks_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureweeks WHERE measureId IN ($weeks_in) AND date LIKE '$newdate%'");
		$weeks_result = @mysqli_fetch_assoc($weeks_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if (!$weeks_result) $weeks_result = array("AVG(3score)" => NULL);

		$days_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuredays WHERE measureId IN ($days_in) AND date LIKE '$newdate%'");
		$days_result = @mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if (!$days_result) $days_result = array("AVG(3score)" => NULL);

		if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL) 
		$score = $days_result["AVG(3score)"];
		else if ($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $weeks_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $months_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
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
		$data["scoreItemMonth"] = ($kpi_result["AVG(3score)"] !== NULL && is_numeric($kpi_result["AVG(3score)"])) ? round($kpi_result["AVG(3score)"],2) : 0;
		$data["scoreItemWeek"] = ($weeks_result["AVG(3score)"] !== NULL && is_numeric($weeks_result["AVG(3score)"])) ? round($weeks_result["AVG(3score)"],2) : 0;
		$data["scoreItemDay"] = ($days_result["AVG(3score)"] !== NULL && is_numeric($days_result["AVG(3score)"])) ? round($days_result["AVG(3score)"],2) : 0;
		
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
function weeksAsIs($objectId, $objectDate, $valuesCount)
{
	global $connect;
	$measure_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Weekly'";
	$measure_id_result=mysqli_query($connect, $measure_id_query);
	$count = 0;
	$count_two = 0;
	$measure_id_array = array(); // Initialize array
	while($row = mysqli_fetch_assoc($measure_id_result))
	{
		$measure_id_array[$count] = $row['id'];
		$count++;
	}
	$where_in = !empty($measure_id_array) ? implode("','", $measure_id_array) : '';
	$where_in = "'".$where_in."'";
	
	$days_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Daily'";
	$days_id_result=mysqli_query($connect, $days_id_query);
	$count = 0;
	$count_two = 0;
	$days_id_array = array(); // Initialize array
	while($row = mysqli_fetch_assoc($days_id_result))
	{
		$days_id_array[$count] = $row['id'];
		$count++;
	}
	$days_in = !empty($days_id_array) ? implode("','", $days_id_array) : '';
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
		$kpi_result = mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if (!$kpi_result) $kpi_result = array("AVG(3score)" => NULL);

		$days_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuredays WHERE measureId IN ($days_in) AND WEEK(date) LIKE '$weekDate'");
		$days_result = mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if (!$days_result) $days_result = array("AVG(3score)" => NULL);

		if($kpi_result["AVG(3score)"] == NULL) $score = $days_result["AVG(3score)"];
		else if($days_result["AVG(3score)"] == NULL) $score = $kpi_result["AVG(3score)"];
		else
		$score = ($kpi_result["AVG(3score)"] + $days_result["AVG(3score)"])/2;
		
		if($score != NULL) $data["score"] = round($score, 2);
		else $data["score"] = NULL;
		
		//$data["scoreItemWeek"] = round($kpi_result["AVG(3score)"],2);
		//$data["scoreItemDay"] = round($days_result["AVG(3score)"],2);
		
		//$data["red"] = 3.33;
		//$data["green"] = 6.67;
		//$data["darkGreen"] = 10.0;
				
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j--;
	}
	echo "]";
}
function monthsAsIs($objectId, $objectDate, $valuesCount)
{
	global $connect;
	$year_id_array = array();
	$halfyear_id_array = array();
	$quarters_id_array = array();
	$months_id_array = array();
	$weeks_id_array = array();
	$days_id_array = array();

	$year_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Yearly'";
	$year_id_result=mysqli_query($connect, $year_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($year_id_result))
	{
		$year_id_array[$count] = $row['id'];
		$count++;
	}
	$year_in = implode("','", $year_id_array);
	$year_in = "'".$year_in."'";
	
	$halfyear_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Bi-Annually'";
	$halfyear_id_result=mysqli_query($connect, $halfyear_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($halfyear_id_result))
	{
		$halfyear_id_array[$count] = $row['id'];
		$count++;
	}
	$halfyear_in = implode("','", $halfyear_id_array);
	$halfyear_in = "'".$halfyear_in."'";
	
	$quarters_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Quarterly'";
	$quarters_id_result=mysqli_query($connect, $quarters_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($quarters_id_result))
	{
		$quarters_id_array[$count] = $row['id'];
		$count++;
	}
	$quarters_in = implode("','", $quarters_id_array);
	$quarters_in = "'".$quarters_in."'";
	
	$months_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Monthly'";
	$months_id_result=mysqli_query($connect, $months_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($months_id_result))
	{
		$months_id_array[$count] = $row['id'];
		$count++;
	}
	$months_in = implode("','", $months_id_array);
	$months_in = "'".$months_in."'";
	
	$weeks_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Weekly'";
	$weeks_id_result=mysqli_query($connect, $weeks_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($weeks_id_result))
	{
		$weeks_id_array[$count] = $row['id'];
		$count++;
	}
	$weeks_in = implode("','", $weeks_id_array);
	$weeks_in = "'".$weeks_in."'";
	
	$days_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Daily'";
	$days_id_result=mysqli_query($connect, $days_id_query);
	$count = 0;
	$count_two = 0;
	while($row = mysqli_fetch_assoc($days_id_result))
	{
		$days_id_array[$count] = $row['id'];
		$count++;
	}
	$days_in = implode("','", $days_id_array);
	$days_in = "'".$days_in."'";

	$count = 0;
	$j = $valuesCount-1;
	if ($valuesCount > 1) echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'month';
		$data["date"] = date('M-Y',strtotime($variable.$objectDate));
		$newdate = date('Y-m',strtotime($variable.$objectDate));
		
		//$variable = "-".$j.'years';
		//$newdate = date('Y',strtotime($variable.$objectDate));
		//$data["date"] = $newdate;
		
		$yearDate = date('Y',strtotime($variable.$objectDate));
		$halfYearDateTemp = strtotime($newdate);
		$halfYearDate5 = date('Y-m',strtotime("-5 month", $halfYearDateTemp));
		$halfYearDate4 = date('Y-m',strtotime("-4 month", $halfYearDateTemp));
		$halfYearDate3 = date('Y-m',strtotime("-3 month", $halfYearDateTemp));
		$halfYearDate2 = date('Y-m',strtotime("-2 month", $halfYearDateTemp));
		$halfYearDate1 = date('Y-m',strtotime("-1 month", $halfYearDateTemp));
		
		$quarterDateTemp = strtotime($newdate);
		$quarterDate3 = date('Y-m',strtotime("-3 month", $quarterDateTemp));
		$quarterDate2 = date('Y-m',strtotime("-2 month", $quarterDateTemp));
		$quarterDate1 = date('Y-m',strtotime("-1 month", $quarterDateTemp));
		
		$kpi_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureyears WHERE measureId IN ($year_in) AND date LIKE '$yearDate%'");
		$kpi_result = @mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				
		$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$newdate%'");
		$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if($halfYear_result["AVG(3score)"] == NULL)
		{
			$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$halfYearDate5%'");
			$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
			if($halfYear_result["AVG(3score)"] == NULL)
			{
				$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$halfYearDate4%'");
				$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				if($halfYear_result["AVG(3score)"] == NULL)
				{
					$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$halfYearDate3%'");
					$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
					
				}
				if($halfYear_result["AVG(3score)"] == NULL)
				{
					$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$halfYearDate2%'");
					$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				}
				if($halfYear_result["AVG(3score)"] == NULL)
				{
				$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$halfYearDate1%'");
					$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				}
			}
		}
		
		$quarters_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurequarters WHERE measureId IN ($quarters_in) AND date LIKE '$newdate%'");
		$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if($quarters_result["AVG(3score)"] == NULL)
		{
			$quarters_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurequarters WHERE measureId IN ($quarters_in) AND date LIKE '$quarterDate3%'");
			$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
			if($quarters_result["AVG(3score)"] == NULL)
			{
				$quarters_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurequarters WHERE measureId IN ($quarters_in) AND date LIKE '$quarterDate2%'");
				$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				if($quarters_result["AVG(3score)"] == NULL)
				{
					$quarters_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurequarters WHERE measureId IN ($quarters_in) AND date LIKE '$quarterDate1%'");
					$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				}
			}
		}
				
		$month_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuremonths WHERE measureId IN ($months_in) AND date LIKE '$newdate%'");
		$months_result = @mysqli_fetch_assoc($month_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$weeks_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureweeks WHERE measureId IN ($weeks_in) AND date LIKE '$newdate%'");
		$weeks_result = @mysqli_fetch_assoc($weeks_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$days_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuredays WHERE measureId IN ($days_in) AND date LIKE '$newdate%'");
		$days_result = @mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);

		if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL) 
		$score = $days_result["AVG(3score)"];
		else if ($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $weeks_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $months_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
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
		$data["scoreItemMonth"] = (!empty($kpi_result["AVG(3score)"]) && is_numeric($kpi_result["AVG(3score)"])) ? round($kpi_result["AVG(3score)"],2) : 0;
		$data["scoreItemWeek"] = (!empty($weeks_result["AVG(3score)"]) && is_numeric($weeks_result["AVG(3score)"])) ? round($weeks_result["AVG(3score)"],2) : 0;
		$data["scoreItemDay"] = (!empty($days_result["AVG(3score)"]) && is_numeric($days_result["AVG(3score)"])) ? round($days_result["AVG(3score)"],2) : 0;
		
		//$data["scoreItemWeek"] = round($weeks_result["AVG(3score)"],2);
		//$data["scoreItemDay"] = round($days_result["AVG(3score)"],2);
		
		$data["red"] = 3.33;
		$data["green"] = 6.67;
		$data["darkGreen"] = 10.0;
				
		$data = json_encode($data);
		if ($valuesCount > 1) echo $data;
		if($i < $valuesCount && $valuesCount > 1) echo ", ";
		$data = NULL;
		$j--;
	}
	if ($valuesCount > 1) echo "]";
	if ($valuesCount == 1) return $score;
}
function quartersAsIs($objectId, $objectDate, $valuesCount)
{
	global $connect;
	$year_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Yearly'";
	$year_id_result=mysqli_query($connect, $year_id_query);
	$count = 0;
	$count_two = 0;
	$year_id_array = array(); // Initialize array
	while($row = mysqli_fetch_assoc($year_id_result))
	{
		$year_id_array[$count] = $row['id'];
		$count++;
	}
	$year_in = !empty($year_id_array) ? implode("','", $year_id_array) : '';
	$year_in = "'".$year_in."'";

	$halfyear_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Bi-Annually'";
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
	
	$quarters_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Quarterly'";
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
	
	$months_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Monthly'";
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
	
	$weeks_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Weekly'";
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
	
	$days_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Daily'";
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
	$quarterMonth = date("m",strtotime($objectDate));
	switch($quarterMonth)
	{
		case '01':
		{
			$objectDate = date("Y-m", strtotime($objectDate));
			break;
		}
		case '02':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m", strtotime("-1 month", $objectDate));
			break;
		}
		case '03':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m", strtotime("-2 month", $objectDate));
			break;
		}
		case '04':
		{
			$objectDate = date("Y-m", strtotime($objectDate));
			break;
		}
		case '05':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m", strtotime("-1 month", $objectDate));
			break;
		}
		case '06':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m", strtotime("-2 month", $objectDate));
			break;
		}
		case '07':
		{
			$objectDate = date("Y-m", strtotime($objectDate));
			break;
		}
		case '08':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m", strtotime("-1 month", $objectDate));
			break;
		}
		case '09':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m-d", strtotime("-2 month", $objectDate));
			break;
		}
		case '07':
		{
			$objectDate = date("Y-m", strtotime($objectDate));
			break;
		}
		case '11':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m", strtotime("-1 month", $objectDate));
			break;
		}
		case '12':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m", strtotime("-2 month", $objectDate));
			//$objectDate = date("Y-m-d", strtotime("+1 years", $objectDate));
			break;
		}
	}
	$count = 0;
	$j = ($valuesCount-1)*3;
	echo "[";
	for($i = 0; $i < $valuesCount; $i++)
	{
		$variablePlusFour = "-".$j.' month';
		$newdate = date('Y-m',strtotime($variablePlusFour.$objectDate));
		$tempDate = quarterLabels($newdate);
		$data["date"] = $tempDate;
		
		//$variable = "-".$j.' years';
		//$objectDate = date('Y-m-d',strtotime($objectDate.'-01-01'));
		$yearDate = date('Y',strtotime($newdate));
		$halfYearDateTemp = strtotime($newdate);
		$halfYearDate = date('Y-m',strtotime("-3 month", $halfYearDateTemp));
		
		$data["date"] = $tempDate;
		
		$kpi_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureyears WHERE measureId IN ($year_in) AND date LIKE '$yearDate%'");
		$kpi_result = @mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$newdate%'");
		$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if($halfYear_result['AVG(3score)'] == NULL)
		{
			$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$halfYearDate%'");		
			$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		}
		$quarters_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurequarters WHERE measureId IN ($quarters_in) AND date LIKE '$newdate%'");
		$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$jQuarterPlus = $j - 3;
		$variablePlusPlusThree = "-".$jQuarterPlus.'month';
		$datePlusThree = date('Y-m',strtotime($variablePlusPlusThree.$objectDate));
		$month_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuremonths WHERE measureId IN ($months_in) AND date >= '$newdate%' AND date < '$datePlusThree%'");
		$months_result = @mysqli_fetch_assoc($month_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
			
		$weeks_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureweeks WHERE measureId IN ($weeks_in) AND date >= '$newdate%' AND date < '$datePlusThree%'");
		$weeks_result = @mysqli_fetch_assoc($weeks_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$days_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuredays WHERE measureId IN ($days_in) AND date LIKE '$newdate%'");
		$days_result = @mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);

		if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL) 
		$score = $days_result["AVG(3score)"];
		else if ($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $weeks_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $months_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
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
		$data["scoreItemMonth"] = ($kpi_result["AVG(3score)"] !== NULL && is_numeric($kpi_result["AVG(3score)"])) ? round($kpi_result["AVG(3score)"],2) : 0;
		$data["scoreItemWeek"] = ($weeks_result["AVG(3score)"] !== NULL && is_numeric($weeks_result["AVG(3score)"])) ? round($weeks_result["AVG(3score)"],2) : 0;
		$data["scoreItemDay"] = ($days_result["AVG(3score)"] !== NULL && is_numeric($days_result["AVG(3score)"])) ? round($days_result["AVG(3score)"],2) : 0;
		
		//$data["red"] = 3.33;
		//$data["green"] = 6.67;
		//$data["darkGreen"] = 10.0;
				
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount-1) echo ", ";

		$data = NULL;
		$j = $j - 3;
	}
	echo "]";
}
function halfYearsAsIs($objectId, $objectDate, $valuesCount)
{
	global $connect;
	$year_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Yearly'";
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

	$halfyear_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Bi-Annually'";
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
	
	$quarters_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Quarterly'";
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
	
	$months_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Monthly'";
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
	
	$weeks_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Weekly'";
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
	
	$days_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Daily'";
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
	$halfYearMonth = date("m",strtotime($objectDate));
	switch($halfYearMonth)
	{
		case '01':
		{
			$objectDate = date("Y-m", strtotime($objectDate));
			break;
		}
		case '02':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m", strtotime("-1 month", $objectDate));
			break;
		}
		case '03':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m", strtotime("-2 month", $objectDate));
			break;
		}
		case '04':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m", strtotime("-3 month", $objectDate));
			break;
		}
		case '05':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m", strtotime("-4 month", $objectDate));
			break;
		}
		case '06':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m", strtotime("-5 month", $objectDate));
			break;
		}
		case '07':
		{
			$objectDate = date("Y-m", strtotime($objectDate));
			break;
		}
		case '08':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m", strtotime("-1 month", $objectDate));
			break;
		}
		case '09':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m", strtotime("-2 month", $objectDate));
			break;
		}
		case '10':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m", strtotime("-3 month", $objectDate));
			break;
		}
		case '11':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m", strtotime("-4 month", $objectDate));
			break;
		}
		case '12':
		{
			$objectDate = strtotime($objectDate);
			$objectDate = date("Y-m", strtotime("-5 month", $objectDate));
			//$objectDate = date("Y-m-d", strtotime("+1 years", $objectDate));
			break;
		}
	}
	$count = 0;
	$j = ($valuesCount-1) * 6;
	echo "[";
	for($i = 0; $i < $valuesCount; $i++)
	{
		$variablePlusSix = "-".$j.'month';
		$jPlus = $j - 6;
		$variablePlusPlusSix = "-".$jPlus.'month';
		$dateToHalfYear = date('Y-m',strtotime($variablePlusSix.$objectDate));
		$tempDate = halfYearLabels($dateToHalfYear);
		$data["date"] = $tempDate;
		$newdate = date('Y-m-d',strtotime($variablePlusSix.$objectDate));
		$datePlusSix = date('Y-m',strtotime($variablePlusPlusSix.$objectDate));
		//$month_count = $month_count + 6;
		
		$monthDate = date('Y-m',strtotime($variablePlusSix.$objectDate));
		$yeardate = date('Y',strtotime($variablePlusSix.$objectDate));
		
		$kpi_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureyears WHERE measureId IN ($year_in) AND date LIKE '$yeardate%'");
		$kpi_result = @mysqli_fetch_assoc($kpi_query) or file_put_contents("error.txt", "\r\nCould not execute kpi query in line 426 get-persp-scores",FILE_APPEND);
		
		$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$monthDate%'");
		$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$quarters_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurequarters WHERE measureId IN ($quarters_in) AND date LIKE '$monthDate%'");
		$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$month_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuremonths WHERE measureId IN ($months_in) AND date >= '$newdate%' AND date < '$datePlusSix%'");
		$months_result = @mysqli_fetch_assoc($month_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				
		$weeks_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureweeks WHERE measureId IN ($weeks_in) AND date LIKE '$newdate%'");
		$weeks_result = @mysqli_fetch_assoc($weeks_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$days_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuredays WHERE measureId IN ($days_in) AND date LIKE '$newdate%'");
		$days_result = @mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);

		if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL) 
		$score = $days_result["AVG(3score)"];
		else if ($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $weeks_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $months_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
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
		$data["scoreItemMonth"] = ($kpi_result["AVG(3score)"] !== NULL && is_numeric($kpi_result["AVG(3score)"])) ? round($kpi_result["AVG(3score)"],2) : 0;
		$data["scoreItemWeek"] = ($weeks_result["AVG(3score)"] !== NULL && is_numeric($weeks_result["AVG(3score)"])) ? round($weeks_result["AVG(3score)"],2) : 0;
		$data["scoreItemDay"] = ($days_result["AVG(3score)"] !== NULL && is_numeric($days_result["AVG(3score)"])) ? round($days_result["AVG(3score)"],2) : 0;
		
		//$data["red"] = 3.33;
		//$data["green"] = 6.67;
		//$data["darkGreen"] = 10.0;
				
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount-1) echo ", ";
		$data = NULL;
		$j = $j - 6;
	}
	echo "]";
}
function yearsAsIs($objectId, $objectDate, $valuesCount)
{
	global $connect;
	$year_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Yearly'";
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

	$halfyear_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Bi-Annually'";
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
	
	$quarters_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Quarterly'";
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
	
	$months_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Monthly'";
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
	
	$weeks_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Weekly'";
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
	
	$days_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Daily'";
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
	$j = $valuesCount-1;
	echo "[";
	for($i = 0; $i < $valuesCount; $i++)
	{
		$variable = "-".$j.' years';
		$objectDate = date('Y-m-d',strtotime($objectDate.'-01-01'));
		$newdate = date('Y',strtotime($variable.$objectDate));
		
		$data["date"] = $newdate;
		
		$kpi_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureyears WHERE measureId IN ($year_in) AND date LIKE '$newdate%'");
		$kpi_result = @mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$newdate%'");
		$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$quarters_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurequarters WHERE measureId IN ($quarters_in) AND date LIKE '$newdate%'");
		$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				
		$month_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuremonths WHERE measureId IN ($months_in) AND date LIKE '$newdate%'");
		$months_result = @mysqli_fetch_assoc($month_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$weeks_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureweeks WHERE measureId IN ($weeks_in) AND date LIKE '$newdate%'");
		$weeks_result = @mysqli_fetch_assoc($weeks_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$days_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuredays WHERE measureId IN ($days_in) AND date LIKE '$newdate%'");
		$days_result = @mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);

		if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL) 
		$score = $days_result["AVG(3score)"];
		else if ($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $weeks_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $months_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
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
		$data["scoreItemMonth"] = ($kpi_result["AVG(3score)"] !== NULL && is_numeric($kpi_result["AVG(3score)"])) ? round($kpi_result["AVG(3score)"],2) : 0;
		$data["scoreItemWeek"] = ($weeks_result["AVG(3score)"] !== NULL && is_numeric($weeks_result["AVG(3score)"])) ? round($weeks_result["AVG(3score)"],2) : 0;
		$data["scoreItemDay"] = ($days_result["AVG(3score)"] !== NULL && is_numeric($days_result["AVG(3score)"])) ? round($days_result["AVG(3score)"],2) : 0;
		
		//$data["red"] = 3.33;
		//$data["green"] = 6.67;
		//$data["darkGreen"] = 10.0;
				
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount-1) echo ", ";
		$data = NULL;
		$j--;
	}
	echo "]";
}
function monthsAsIsSingle($objectId, $objectDate, $valuesCount)
{
	global $connect;
	$year_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Yearly'";
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
	
	$halfyear_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Bi-Annually'";
	$halfyear_id_result=mysqli_query($connect, $halfyear_id_query);
	$count = 0;
	$count_two = 0;
	while($row = @mysqli_fetch_assoc($halfyear_id_result))
	{
		$halfyear_id_array[$count] = $row['id'];
		$count++;
	}
	$halfyear_in = @implode("','", $halfyear_id_array);
	$halfyear_in = "'".$halfyear_in."'";
	
	$quarters_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Quarterly'";
	$quarters_id_result=mysqli_query($connect, $quarters_id_query);
	$count = 0;
	$count_two = 0;
	while($row = @mysqli_fetch_assoc($quarters_id_result))
	{
		$quarters_id_array[$count] = $row['id'];
		$count++;
	}
	$quarters_in = @implode("','", $quarters_id_array);
	$quarters_in = "'".$quarters_in."'";
	
	$months_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Monthly'";
	$months_id_result=mysqli_query($connect, $months_id_query);
	$count = 0;
	$count_two = 0;
	while($row = @mysqli_fetch_assoc($months_id_result))
	{
		$months_id_array[$count] = $row['id'];
		$count++;
	}
	$months_in = @implode("','", $months_id_array);
	$months_in = "'".$months_in."'";
	
	$weeks_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Weekly'";
	$weeks_id_result=mysqli_query($connect, $weeks_id_query);
	$count = 0;
	$count_two = 0;
	while($row = @mysqli_fetch_assoc($weeks_id_result))
	{
		$weeks_id_array[$count] = $row['id'];
		$count++;
	}
	$weeks_in = @implode("','", $weeks_id_array);
	$weeks_in = "'".$weeks_in."'";
	
	$days_id_query="SELECT id FROM measure where linkedObject IN($objectId) AND calendarType = 'Daily'";
	$days_id_result=mysqli_query($connect, $days_id_query);
	$count = 0;
	$count_two = 0;
	while($row = @mysqli_fetch_assoc($days_id_result))
	{
		$days_id_array[$count] = $row['id'];
		$count++;
	}
	$days_in = @implode("','", $days_id_array);
	$days_in = "'".$days_in."'";

	$count = 0;
	$j = $valuesCount-1;
	//echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'month';
		$data["date"] = date('M-Y',strtotime($variable.$objectDate));
		$newdate = date('Y-m',strtotime($variable.$objectDate));
		
		//$variable = "-".$j.'years';
		//$newdate = date('Y',strtotime($variable.$objectDate));
		//$data["date"] = $newdate;
		
		$yearDate = date('Y',strtotime($variable.$objectDate));
		$halfYearDateTemp = strtotime($newdate);
		$halfYearDate5 = date('Y-m',strtotime("-5 month", $halfYearDateTemp));
		$halfYearDate4 = date('Y-m',strtotime("-4 month", $halfYearDateTemp));
		$halfYearDate3 = date('Y-m',strtotime("-3 month", $halfYearDateTemp));
		$halfYearDate2 = date('Y-m',strtotime("-2 month", $halfYearDateTemp));
		$halfYearDate1 = date('Y-m',strtotime("-1 month", $halfYearDateTemp));
		
		$quarterDateTemp = strtotime($newdate);
		$quarterDate3 = date('Y-m',strtotime("-3 month", $quarterDateTemp));
		$quarterDate2 = date('Y-m',strtotime("-2 month", $quarterDateTemp));
		$quarterDate1 = date('Y-m',strtotime("-1 month", $quarterDateTemp));
		
		$kpi_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureyears WHERE measureId IN ($year_in) AND date LIKE '$yearDate%'");
		$kpi_result = @mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				
		$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$newdate%'");
		$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if($halfYear_result["AVG(3score)"] == NULL)
		{
			$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$halfYearDate5%'");
			$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
			if($halfYear_result["AVG(3score)"] == NULL)
			{
				$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$halfYearDate4%'");
				$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				if($halfYear_result["AVG(3score)"] == NULL)
				{
					$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$halfYearDate3%'");
					$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
					
				}
				if($halfYear_result["AVG(3score)"] == NULL)
				{
					$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$halfYearDate2%'");
					$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				}
				if($halfYear_result["AVG(3score)"] == NULL)
				{
				$halfYear_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurehalfyear WHERE measureId IN ($halfyear_in) AND date LIKE '$halfYearDate1%'");
					$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				}
			}
		}
		
		$quarters_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurequarters WHERE measureId IN ($quarters_in) AND date LIKE '$newdate%'");
		$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if($quarters_result["AVG(3score)"] == NULL)
		{
			$quarters_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurequarters WHERE measureId IN ($quarters_in) AND date LIKE '$quarterDate3%'");
			$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
			if($quarters_result["AVG(3score)"] == NULL)
			{
				$quarters_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurequarters WHERE measureId IN ($quarters_in) AND date LIKE '$quarterDate2%'");
				$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				if($quarters_result["AVG(3score)"] == NULL)
				{
					$quarters_query = mysqli_query($connect, "SELECT AVG(3score) FROM measurequarters WHERE measureId IN ($quarters_in) AND date LIKE '$quarterDate1%'");
					$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				}
			}
		}
				
		$month_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuremonths WHERE measureId IN ($months_in) AND date LIKE '$newdate%'");
		$months_result = @mysqli_fetch_assoc($month_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$weeks_query = mysqli_query($connect, "SELECT AVG(3score) FROM measureweeks WHERE measureId IN ($weeks_in) AND date LIKE '$newdate%'");
		$weeks_result = @mysqli_fetch_assoc($weeks_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$days_query = mysqli_query($connect, "SELECT AVG(3score) FROM measuredays WHERE measureId IN ($days_in) AND date LIKE '$newdate%'");
		$days_result = @mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);

		if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL) 
		$score = $days_result["AVG(3score)"];
		else if ($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $weeks_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $quarters_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
		$score = $months_result["AVG(3score)"];
		else if($kpi_result["AVG(3score)"] == NULL && $halfYear_result["AVG(3score)"] == NULL && $months_result["AVG(3score)"] == NULL && $weeks_result["AVG(3score)"] == NULL && $days_result["AVG(3score)"] == NULL) 
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
		$data["scoreItemMonth"] = ($kpi_result["AVG(3score)"] !== NULL && is_numeric($kpi_result["AVG(3score)"])) ? round($kpi_result["AVG(3score)"],2) : 0;
		$data["scoreItemWeek"] = ($weeks_result["AVG(3score)"] !== NULL && is_numeric($weeks_result["AVG(3score)"])) ? round($weeks_result["AVG(3score)"],2) : 0;
		$data["scoreItemDay"] = ($days_result["AVG(3score)"] !== NULL && is_numeric($days_result["AVG(3score)"])) ? round($days_result["AVG(3score)"],2) : 0;
		
		$data["red"] = 3.33;
		$data["green"] = 6.67;
		$data["darkGreen"] = 10.0;
				
		//$data = json_encode($data);
		//echo $data;
		//if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j--;
	}
	return $score;
	//echo "]";
}
?>