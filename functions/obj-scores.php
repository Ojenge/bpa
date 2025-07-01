<?php
function weeksAsIs($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType)
{
	global $connect;
	
	$measure_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Weekly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Weekly')";
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
	
	$days_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Daily'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Daily')";
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
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'weeks';
		$data["date"] = date('Y-m-d',strtotime($variable.$objectDate));
		$newdate = date('Y-m-d',strtotime($variable.$objectDate));
		$weekDate = date('W',strtotime($variable.$objectDate));
		$kpi_query = mysqli_query($connect, "SELECT avgScore FROM measureweeks WHERE measureId IN ($where_in) AND date LIKE '$newdate%'");
		$kpi_result = mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$days_query = mysqli_query($connect, "SELECT avgScore FROM measuredays WHERE measureId IN ($days_in) AND WEEK(date) LIKE '$weekDate'");
		$days_result = mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);

		if($kpi_result["avgScore"] == NULL) $score = $days_result["avgScore"];
		else if($days_result["avgScore"] == NULL) $score = $kpi_result["avgScore"];
		else
		$score = ($kpi_result["avgScore"] + $days_result["avgScore"])/2;
		
		if($score != NULL) $data["score"] = round($score, 2);
		else $data["score"] = NULL;
		
		//$data["scoreItemWeek"] = round($kpi_result["avgScore"],2);
		//$data["scoreItemDay"] = round($days_result["avgScore"],2);
		
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
function daysAsIs($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType)
{
	global $connect;
	
	$year_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Yearly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Yearly')";
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

	$halfyear_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Bi-Annually'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Bi-Annually')";
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
	
	$quarters_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Quarterly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Quarterly')";
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
	
	$months_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Monthly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Monthly')";
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
	
	$weeks_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Weekly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Weekly')";
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
	
	$days_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Daily'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Daily')";
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
		
		$quarterDateTemp = strtotime($newdate);
		$quarterDate3 = date('Y-m',strtotime("-3 month", $quarterDateTemp));
		$quarterDate2 = date('Y-m',strtotime("-2 month", $quarterDateTemp));
		$quarterDate1 = date('Y-m',strtotime("-1 month", $quarterDateTemp));
		
		$kpi_query = mysqli_query($connect, "SELECT AVG(measureyears.3score * measure.weight) AS avgScore
		FROM measureyears, measure
		WHERE measureyears.measureId IN ($year_in)
		AND measureyears.measureId = measure.id
		AND date LIKE '$yearDate%'");
		$kpi_result = @mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		//file_put_contents("objScoresError.txt", "\r\n measureyear average is: ".$kpi_result['avgScore'].' => '.$year_in.' and date is '.$yearDate, FILE_APPEND);
		
		$halfYear_query = mysqli_query($connect, "SELECT AVG(measurehalfyear.3score * measure.weight) AS avgScore
		FROM measurehalfyear, measure
		WHERE measurehalfyear.measureId IN ($halfyear_in)
		AND measurehalfyear.measureId = measure.id
		AND date LIKE '$newdate%'");
		$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if($halfYear_result["avgScore"] == NULL)
		{
			$halfYear_query = mysqli_query($connect, "SELECT AVG(measurehalfyear.3score * measure.weight) AS avgScore
		FROM measurehalfyear, measure
		WHERE measurehalfyear.measureId IN ($halfyear_in)
		AND measurehalfyear.measureId = measure.id
		AND date LIKE '$halfYearDate5%'");
			$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
			if($halfYear_result["avgScore"] == NULL)
			{
				$halfYear_query = mysqli_query($connect, "SELECT AVG(measurehalfyear.3score * measure.weight) AS avgScore
		FROM measurehalfyear, measure
		WHERE measurehalfyear.measureId IN ($halfyear_in)
		AND measurehalfyear.measureId = measure.id
		AND date LIKE '$halfYearDate4%'");
				$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				if($halfYear_result["avgScore"] == NULL)
				{
					$halfYear_query = mysqli_query($connect, "SELECT AVG(measurehalfyear.3score * measure.weight) AS avgScore
		FROM measurehalfyear, measure
		WHERE measurehalfyear.measureId IN ($halfyear_in)
		AND measurehalfyear.measureId = measure.id
		AND date LIKE '$halfYearDate3%'");
					$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
					
				}
				if($halfYear_result["avgScore"] == NULL)
				{
					$halfYear_query = mysqli_query($connect, "SELECT AVG(measurehalfyear.3score * measure.weight) AS avgScore
		FROM measurehalfyear, measure
		WHERE measurehalfyear.measureId IN ($halfyear_in)
		AND measurehalfyear.measureId = measure.id
		AND date LIKE '$halfYearDate2%'");
					$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				}
				if($halfYear_result["avgScore"] == NULL)
				{
				$halfYear_query = mysqli_query($connect, "SELECT AVG(measurehalfyear.3score * measure.weight) AS avgScore
		FROM measurehalfyear, measure
		WHERE measurehalfyear.measureId IN ($halfyear_in)
		AND measurehalfyear.measureId = measure.id
		AND date LIKE '$halfYearDate1%'");
					$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				}
			}
		}
		
		$quarters_query = mysqli_query($connect, "SELECT AVG(measurequarters.3score * measure.weight) AS avgScore
		FROM measurequarters, measure
		WHERE measurequarters.measureId IN ($quarters_in)
		AND measurequarters.measureId = measure.id
		AND date LIKE '$newdate%'");
		$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if($quarters_result["avgScore"] == NULL)
		{
			$quarters_query = mysqli_query($connect, "SELECT AVG(measurequarters.3score * measure.weight) AS avgScore
		FROM measurequarters, measure
		WHERE measurequarters.measureId IN ($quarters_in)
		AND measurequarters.measureId = measure.id
		AND date LIKE '$quarterDate3%'");
			$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
			if($quarters_result["avgScore"] == NULL)
			{
				$quarters_query = mysqli_query($connect, "SELECT AVG(measurequarters.3score * measure.weight) AS avgScore
		FROM measurequarters, measure
		WHERE measurequarters.measureId IN ($quarters_in)
		AND measurequarters.measureId = measure.id
		AND date LIKE '$quarterDate2%'");
				$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				if($quarters_result["avgScore"] == NULL)
				{
					$quarters_query = mysqli_query($connect, "SELECT AVG(measurequarters.3score * measure.weight) AS avgScore
		FROM measurequarters, measure
		WHERE measurequarters.measureId IN ($quarters_in)
		AND measurequarters.measureId = measure.id
		AND date LIKE '$quarterDate1%'");
					$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				}
			}
		}
				
		$month_query = mysqli_query($connect, "SELECT AVG(measuremonths.3score * measure.weight) AS avgScore
		FROM measuremonths, measure
		WHERE measuremonths.measureId IN ($months_in)
		AND measuremonths.measureId = measure.id
		AND date LIKE '$newdate%'");
		$months_result = @mysqli_fetch_assoc($month_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$weeks_query = mysqli_query($connect, "SELECT AVG(measureweeks.3score * measure.weight) AS avgScore
		FROM measureweeks, measure
		WHERE measureweeks.measureId IN ($weeks_in)
		AND measureweeks.measureId = measure.id
		AND date LIKE '$newdate%'");
		$weeks_result = @mysqli_fetch_assoc($weeks_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$days_query = mysqli_query($connect, "SELECT AVG(measuredays.3score * measure.weight) AS avgScore
		FROM measuredays, measure
		WHERE measuredays.measureId IN ($days_in)
		AND measuredays.measureId = measure.id
		AND date LIKE '$newdate%'");
		$days_result = @mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);

		if($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL) 
		$score = $days_result["avgScore"];
		else if ($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $weeks_result["avgScore"];
		else if($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $months_result["avgScore"];
		else if($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $quarters_result["avgScore"];
		else if($kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $halfYear_result["avgScore"];
		else if($quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $kpi_result["avgScore"];
		/**********************************************************************************************************************************/
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $kpi_result["avgScore"])/2;
		else if($kpi_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"])/2;
		else if($kpi_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $months_result["avgScore"])/2;
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $quarters_result["avgScore"])/2;
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $halfYear_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $kpi_result["avgScore"])/2;
		else if($kpi_result["avgScore"] == NULL && $days_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $quarters_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $halfYear_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $quarters_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $halfYear_result["avgScore"])/2;	
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($quarters_result["avgScore"] + $kpi_result["avgScore"])/2;	
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL) 
		$score = ($quarters_result["avgScore"] + $halfYear_result["avgScore"])/2;
		/**********************************************************************************************************************************/
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $months_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL)
		$score = ($kpi_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($kpi_result["avgScore"] + $months_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;

		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($weeks_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($weeks_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"] + $kpi_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $months_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $months_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $months_result["avgScore"] + $kpi_result["avgScore"])/3;
		
		else if($months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $halfYear_result["avgScore"])/3;

		else if($months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $kpi_result["avgScore"])/3;
		
		else if($quarters_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"])/3;
		/**********************************************************************************************************************************/
		else if($kpi_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($days_result["avgScore"] == NULL)
		$score = ($kpi_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($weeks_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $kpi_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($months_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($quarters_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"] + $kpi_result["avgScore"])/4;
		/**********************************************************************************************************************************/
		else $score = ($kpi_result["avgScore"] + $quarters_result["avgScore"] + $months_result["avgScore"] + $weeks_result["avgScore"] + $days_result["avgScore"])/5;
		
		if($score != NULL) $data["score"] = round($score, 2);
		else $data["score"] = NULL;
		$data["scoreItemMonth"] = ($kpi_result["avgScore"] !== NULL && is_numeric($kpi_result["avgScore"])) ? round($kpi_result["avgScore"],2) : 0;
		$data["scoreItemWeek"] = ($weeks_result["avgScore"] !== NULL && is_numeric($weeks_result["avgScore"])) ? round($weeks_result["avgScore"],2) : 0;
		$data["scoreItemDay"] = ($days_result["avgScore"] !== NULL && is_numeric($days_result["avgScore"])) ? round($days_result["avgScore"],2) : 0;
		
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
	global $connect;
	
	$year_id_query="SELECT id FROM measure WHERE linkedObject = '$objectId' AND calendarType = 'Yearly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks
			WHERE measurelinks.linked_id = '$objectId'
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Yearly')"; //Adding for linked measures to contribute to scoring. LTK 15Aug2021 1214hrs
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
	
	$halfyear_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Bi-Annually'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks
			WHERE measurelinks.linked_id = '$objectId'
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Bi-Annually')";
	$halfyear_id_result=mysqli_query($connect, $halfyear_id_query);
	$count = 0;
	$count_two = 0;
	$halfyear_id_array = array(); // Initialize array
	while($row = mysqli_fetch_assoc($halfyear_id_result))
	{
		$halfyear_id_array[$count] = $row['id'];
		$count++;
	}
	$halfyear_in = !empty($halfyear_id_array) ? implode("','", $halfyear_id_array) : '';
	$halfyear_in = "'".$halfyear_in."'";
	
	$quarters_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Quarterly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks
			WHERE measurelinks.linked_id = '$objectId'
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Yearly')";
	$quarters_id_result=mysqli_query($connect, $quarters_id_query);
	$count = 0;
	$count_two = 0;
	$quarters_id_array = array(); // Initialize array
	while($row = mysqli_fetch_assoc($quarters_id_result))
	{
		$quarters_id_array[$count] = $row['id'];
		$count++;
	}
	$quarters_in = !empty($quarters_id_array) ? implode("','", $quarters_id_array) : '';
	$quarters_in = "'".$quarters_in."'";
	
	$months_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Monthly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks
			WHERE measurelinks.linked_id = '$objectId'
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Monthly')";
	$months_id_result=mysqli_query($connect, $months_id_query);
	$count = 0;
	$count_two = 0;
	$months_id_array = array(); // Initialize array
	while($row = mysqli_fetch_assoc($months_id_result))
	{
		$months_id_array[$count] = $row['id'];
		$count++;
	}
	$months_in = !empty($months_id_array) ? implode("','", $months_id_array) : '';
	$months_in = "'".$months_in."'";
	
	$weeks_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Weekly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks
			WHERE measurelinks.linked_id = '$objectId'
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Weekly')";
	$weeks_id_result=mysqli_query($connect, $weeks_id_query);
	$count = 0;
	$count_two = 0;
	$weeks_id_array = array(); // Initialize array
	while($row = mysqli_fetch_assoc($weeks_id_result))
	{
		$weeks_id_array[$count] = $row['id'];
		$count++;
	}
	$weeks_in = !empty($weeks_id_array) ? implode("','", $weeks_id_array) : '';
	$weeks_in = "'".$weeks_in."'";
	
	$days_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Daily'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks
			WHERE measurelinks.linked_id = '$objectId'
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Daily')";
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
	$j = $valuesCount-1;
	echo "[";
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
		
		//Application of weights on the scores displayed in trend charts. LTK 15Aug2021 1850hrs
		$kpi_query = mysqli_query($connect, "SELECT AVG(measureyears.3score * measure.weight) AS avgScore
		FROM measureyears, measure
		WHERE measureyears.measureId IN ($year_in)
		AND measureyears.measureId = measure.id
		AND date LIKE '$yearDate%'");
		$kpi_result = @mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "\n526 Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		// Initialize result with default values if null
		if (!$kpi_result) $kpi_result = array("avgScore" => NULL);

		$halfYear_query = mysqli_query($connect, "SELECT AVG(measurehalfyear.3score * measure.weight) AS avgScore
		FROM measurehalfyear, measure
		WHERE measurehalfyear.measureId IN ($halfyear_in)
		AND measurehalfyear.measureId = measure.id
		AND date LIKE '$newdate%'");
		$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "\n525 Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		// Initialize result with default values if null
		if (!$halfYear_result) $halfYear_result = array("avgScore" => NULL);
		if($halfYear_result["avgScore"] == NULL)
		{
			$halfYear_query = mysqli_query($connect, "SELECT AVG(measurehalfyear.3score * measure.weight) AS avgScore
		FROM measurehalfyear, measure
		WHERE measurehalfyear.measureId IN ($halfyear_in)
		AND measurehalfyear.measureId = measure.id
		AND date LIKE '$halfYearDate5%'");
			$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "\n529 Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
			if (!$halfYear_result) $halfYear_result = array("avgScore" => NULL);
			if($halfYear_result["avgScore"] == NULL)
			{
				$halfYear_query = mysqli_query($connect, "SELECT AVG(measurehalfyear.3score * measure.weight) AS avgScore
		FROM measurehalfyear, measure 
		WHERE measurehalfyear.measureId IN ($halfyear_in) 
		AND measurehalfyear.measureId = measure.id
		AND date LIKE '$halfYearDate4%'");
				$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "\n533 Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				if (!$halfYear_result) $halfYear_result = array("avgScore" => NULL);
				if($halfYear_result["avgScore"] == NULL)
				{
					$halfYear_query = mysqli_query($connect, "SELECT AVG(measurehalfyear.3score * measure.weight) AS avgScore
		FROM measurehalfyear, measure 
		WHERE measurehalfyear.measureId IN ($halfyear_in) 
		AND measurehalfyear.measureId = measure.id
		AND date LIKE '$halfYearDate3%'");
					$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "\n537 Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
					if (!$halfYear_result) $halfYear_result = array("avgScore" => NULL);
				}
				if($halfYear_result && isset($halfYear_result["avgScore"]) && $halfYear_result["avgScore"] == NULL)
				{
					$halfYear_query = mysqli_query($connect, "SELECT AVG(measurehalfyear.3score * measure.weight) AS avgScore
		FROM measurehalfyear, measure
		WHERE measurehalfyear.measureId IN ($halfyear_in)
		AND measurehalfyear.measureId = measure.id
		AND date LIKE '$halfYearDate2%'");
					$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "\n543 Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
					if (!$halfYear_result) $halfYear_result = array("avgScore" => NULL);
				}
				if($halfYear_result["avgScore"] == NULL)
				{
				$halfYear_query = mysqli_query($connect, "SELECT AVG(measurehalfyear.3score * measure.weight) AS avgScore
		FROM measurehalfyear, measure
		WHERE measurehalfyear.measureId IN ($halfyear_in)
		AND measurehalfyear.measureId = measure.id
		AND date LIKE '$halfYearDate1%'");
					$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "\n548 Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
					if (!$halfYear_result) $halfYear_result = array("avgScore" => NULL);
				}
			}
		}
		$quarters_query = mysqli_query($connect, "SELECT AVG(measurequarters.3score * measure.weight) AS avgScore
		FROM measurequarters, measure 
		WHERE measurequarters.measureId IN ($quarters_in) 
		AND measurequarters.measureId = measure.id
		AND date LIKE '$newdate%'");
		$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "\n553 Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		// Initialize result with default values if null
		if (!$quarters_result) $quarters_result = array("avgScore" => NULL);
		if($quarters_result["avgScore"] == NULL)
		{
			$quarters_query = mysqli_query($connect, "SELECT AVG(measurequarters.3score * measure.weight) AS avgScore
		FROM measurequarters, measure 
		WHERE measurequarters.measureId IN ($quarters_in) 
		AND measurequarters.measureId = measure.id
		AND date LIKE '$quarterDate3%'");
			$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "\n557 Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
			if (!$quarters_result) $quarters_result = array("avgScore" => NULL);
			if($quarters_result["avgScore"] == NULL)
			{
				$quarters_query = mysqli_query($connect, "SELECT AVG(measurequarters.3score * measure.weight) AS avgScore
		FROM measurequarters, measure
		WHERE measurequarters.measureId IN ($quarters_in)
		AND measurequarters.measureId = measure.id
		AND date LIKE '$quarterDate2%'");
				$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "\n561 Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
				if (!$quarters_result) $quarters_result = array("avgScore" => NULL);
				if($quarters_result["avgScore"] == NULL)
				{
					$quarters_query = mysqli_query($connect, "SELECT AVG(measurequarters.3score * measure.weight) AS avgScore
		FROM measurequarters, measure
		WHERE measurequarters.measureId IN ($quarters_in)
		AND measurequarters.measureId = measure.id
		AND date LIKE '$quarterDate1%'");
					$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "\n565 Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
					if (!$quarters_result) $quarters_result = array("avgScore" => NULL);
				}
			}
		}
				
		$month_query = mysqli_query($connect, "SELECT AVG(measuremonths.3score * measure.weight) AS avgScore
		FROM measuremonths, measure 
		WHERE measuremonths.measureId IN ($months_in) 
		AND measuremonths.measureId = measure.id
		AND date LIKE '$newdate%'");
		$months_result = @mysqli_fetch_assoc($month_query) or file_put_contents("myObjScores.txt", "\n571 Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		// Initialize result with default values if null
		if (!$months_result) $months_result = array("avgScore" => NULL);

		$weeks_query = mysqli_query($connect, "SELECT AVG(measureweeks.3score * measure.weight) AS avgScore
		FROM measureweeks, measure
		WHERE measureweeks.measureId IN ($weeks_in)
		AND measureweeks.measureId = measure.id
		AND date LIKE '$newdate%'");
		$weeks_result = @mysqli_fetch_assoc($weeks_query) or file_put_contents("myObjScores.txt", "\n574 Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		// Initialize result with default values if null
		if (!$weeks_result) $weeks_result = array("avgScore" => NULL);

		$days_query = mysqli_query($connect, "SELECT AVG(measuredays.3score * measure.weight) AS avgScore
		FROM measuredays, measure
		WHERE measuredays.measureId IN ($days_in)
		AND measuredays.measureId = measure.id
		AND date LIKE '$newdate%'");
		$days_result = @mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "\n577 Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		// Initialize result with default values if null
		if (!$days_result) $days_result = array("avgScore" => NULL);

		if($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL) 
		$score = $days_result["avgScore"];
		else if ($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $weeks_result["avgScore"];
		else if($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $months_result["avgScore"];
		else if($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $quarters_result["avgScore"];
		else if($kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $halfYear_result["avgScore"];
		else if($quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $kpi_result["avgScore"];
		/**********************************************************************************************************************************/
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $kpi_result["avgScore"])/2;
		else if($kpi_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"])/2;
		else if($kpi_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $months_result["avgScore"])/2;
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $quarters_result["avgScore"])/2;
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $halfYear_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $kpi_result["avgScore"])/2;
		else if($kpi_result["avgScore"] == NULL && $days_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $quarters_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $halfYear_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $quarters_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $halfYear_result["avgScore"])/2;	
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($quarters_result["avgScore"] + $kpi_result["avgScore"])/2;	
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL) 
		$score = ($quarters_result["avgScore"] + $halfYear_result["avgScore"])/2;
		/**********************************************************************************************************************************/
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $months_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL)
		$score = ($kpi_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($kpi_result["avgScore"] + $months_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;

		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($weeks_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($weeks_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"] + $kpi_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $months_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $months_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $months_result["avgScore"] + $kpi_result["avgScore"])/3;
		
		else if($months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $halfYear_result["avgScore"])/3;

		else if($months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $kpi_result["avgScore"])/3;
		
		else if($quarters_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"])/3;
		/**********************************************************************************************************************************/
		else if($kpi_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($days_result["avgScore"] == NULL)
		$score = ($kpi_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($weeks_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $kpi_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($months_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($quarters_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"] + $kpi_result["avgScore"])/4;
		/**********************************************************************************************************************************/
		else $score = ($kpi_result["avgScore"] + $quarters_result["avgScore"] + $months_result["avgScore"] + $weeks_result["avgScore"] + $days_result["avgScore"])/5;
		//file_put_contents("track.txt", "objectId = $objectId; score = $score");
		if($score != NULL) $data["score"] = round($score, 2);
		else $data["score"] = NULL;
		$data["scoreItemMonth"] = ($kpi_result["avgScore"] !== NULL && is_numeric($kpi_result["avgScore"])) ? round($kpi_result["avgScore"],2) : 0;
		$data["scoreItemWeek"] = ($weeks_result["avgScore"] !== NULL && is_numeric($weeks_result["avgScore"])) ? round($weeks_result["avgScore"],2) : 0;
		$data["scoreItemDay"] = ($days_result["avgScore"] !== NULL && is_numeric($days_result["avgScore"])) ? round($days_result["avgScore"],2) : 0;
		
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
	global $connect;
	
	$year_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Yearly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Yearly')";
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

	$halfyear_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Bi-Annually'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Bi-Annually')";
	$halfyear_id_result=mysqli_query($connect, $halfyear_id_query);
	$count = 0;
	$count_two = 0;
	$halfyear_id_array = array(); // Initialize array
	while($row = mysqli_fetch_assoc($halfyear_id_result))
	{
		$halfyear_id_array[$count] = $row['id'];
		$count++;
	}
	$halfyear_in = !empty($halfyear_id_array) ? implode("','", $halfyear_id_array) : '';
	$halfyear_in = "'".$halfyear_in."'";
	
	$quarters_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Quarterly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Quarterly')";
	$quarters_id_result=mysqli_query($connect, $quarters_id_query);
	$count = 0;
	$count_two = 0;
	$quarters_id_array = array(); // Initialize array
	while($row = mysqli_fetch_assoc($quarters_id_result))
	{
		$quarters_id_array[$count] = $row['id'];
		$count++;
	}
	$quarters_in = !empty($quarters_id_array) ? implode("','", $quarters_id_array) : '';
	$quarters_in = "'".$quarters_in."'";
	
	$months_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Monthly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Monthly')";
	$months_id_result=mysqli_query($connect, $months_id_query);
	$count = 0;
	$count_two = 0;
	$months_id_array = array(); // Initialize array
	while($row = mysqli_fetch_assoc($months_id_result))
	{
		$months_id_array[$count] = $row['id'];
		$count++;
	}
	$months_in = !empty($months_id_array) ? implode("','", $months_id_array) : '';
	$months_in = "'".$months_in."'";
	
	$weeks_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Weekly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Weekly')";
	$weeks_id_result=mysqli_query($connect, $weeks_id_query);
	$count = 0;
	$count_two = 0;
	$weeks_id_array = array(); // Initialize array
	while($row = mysqli_fetch_assoc($weeks_id_result))
	{
		$weeks_id_array[$count] = $row['id'];
		$count++;
	}
	$weeks_in = !empty($weeks_id_array) ? implode("','", $weeks_id_array) : '';
	$weeks_in = "'".$weeks_in."'";
	
	$days_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Daily'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Daily')";
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
	$j = ($valuesCount-1) * 3;
	echo "[";
	for($i = 0; $i < $valuesCount; $i++)
	{
		$variablePlusFour = "-".$j.' month';
		$newdate = date('Y-m',strtotime($variablePlusFour.$objectDate));
		$tempDate = quarterLabels($newdate);
		$data["date"] = $tempDate;
		//$dateToQuarter = date('Y-m', strtotime($dateToQuarter));
		
		$yeardate = date('Y',strtotime($newdate));
		
		$halfYearDate = strtotime($newdate);
		$halfYearDate = date('Y-m',strtotime("-3 month", $halfYearDate));
		
		$kpi_query = mysqli_query($connect, "SELECT AVG(measureyears.3score * measure.weight) AS avgScore
		FROM measureyears, measure
		WHERE measureyears.measureId IN ($year_in)
		AND measureyears.measureId = measure.id
		AND date LIKE '$yeardate%'");
		$kpi_result = @mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if (!$kpi_result) $kpi_result = array("avgScore" => NULL);

		$halfYear_query = mysqli_query($connect, "SELECT AVG(measurehalfyear.3score * measure.weight) AS avgScore
		FROM measurehalfyear, measure
		WHERE measurehalfyear.measureId IN ($halfyear_in)
		AND measurehalfyear.measureId = measure.id
		AND date LIKE '$newdate%'");
		$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if (!$halfYear_result) $halfYear_result = array("avgScore" => NULL);
		if($halfYear_result['avgScore'] == NULL)
		{
			$halfYear_query = mysqli_query($connect, "SELECT AVG(measurehalfyear.3score * measure.weight) AS avgScore
		FROM measurehalfyear, measure
		WHERE measurehalfyear.measureId IN ($halfyear_in)
		AND measurehalfyear.measureId = measure.id
		AND date LIKE '$halfYearDate%'");		
			$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
			if (!$halfYear_result) $halfYear_result = array("avgScore" => NULL);
		}
		$quarters_query = mysqli_query($connect, "SELECT AVG(measurequarters.3score * measure.weight) AS avgScore
		FROM measurequarters, measure
		WHERE measurequarters.measureId IN ($quarters_in)
		AND measurequarters.measureId = measure.id
		AND date LIKE '$newdate%'");
		$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if (!$quarters_result) $quarters_result = array("avgScore" => NULL);
		
		$jQuarterPlus = $j - 3;
		$variablePlusPlusThree = "-".$jQuarterPlus.'month';
		$datePlusThree = date('Y-m',strtotime($variablePlusPlusThree.$objectDate));
		$month_query = mysqli_query($connect, "SELECT AVG(measuremonths.3score * measure.weight) AS avgScore
		FROM measuremonths, measure
		WHERE measuremonths.measureId IN ($months_in)
		AND measuremonths.measureId = measure.id
		AND date >= '$newdate%' AND date < '$datePlusThree%'");
		$months_result = @mysqli_fetch_assoc($month_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if (!$months_result) $months_result = array("avgScore" => NULL);

		$weeks_query = mysqli_query($connect, "SELECT AVG(measureweeks.3score * measure.weight) AS avgScore
		FROM measureweeks, measure
		WHERE measureweeks.measureId IN ($weeks_in)
		AND measureweeks.measureId = measure.id
		AND date >= '$newdate%' AND date < '$datePlusThree%'");
		$weeks_result = @mysqli_fetch_assoc($weeks_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if (!$weeks_result) $weeks_result = array("avgScore" => NULL);

		$days_query = mysqli_query($connect, "SELECT AVG(measuredays.3score * measure.weight) AS avgScore
		FROM measuredays, measure
		WHERE measuredays.measureId IN ($days_in)
		AND measuredays.measureId = measure.id
		AND date LIKE '$newdate%'");
		$days_result = @mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		if (!$days_result) $days_result = array("avgScore" => NULL);

		if($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL) 
		$score = $days_result["avgScore"];
		else if ($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $weeks_result["avgScore"];
		else if($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $months_result["avgScore"];
		else if($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $quarters_result["avgScore"];
		else if($kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $halfYear_result["avgScore"];
		else if($quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $kpi_result["avgScore"];
		/**********************************************************************************************************************************/
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $kpi_result["avgScore"])/2;
		else if($kpi_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"])/2;
		else if($kpi_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $months_result["avgScore"])/2;
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $quarters_result["avgScore"])/2;
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $halfYear_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $kpi_result["avgScore"])/2;
		else if($kpi_result["avgScore"] == NULL && $days_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $quarters_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $halfYear_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $quarters_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $halfYear_result["avgScore"])/2;	
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($quarters_result["avgScore"] + $kpi_result["avgScore"])/2;	
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL) 
		$score = ($quarters_result["avgScore"] + $halfYear_result["avgScore"])/2;
		/**********************************************************************************************************************************/
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $months_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL)
		$score = ($kpi_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($kpi_result["avgScore"] + $months_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;

		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($weeks_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($weeks_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"] + $kpi_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $months_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $months_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $months_result["avgScore"] + $kpi_result["avgScore"])/3;
		
		else if($months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $halfYear_result["avgScore"])/3;

		else if($months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $kpi_result["avgScore"])/3;
		
		else if($quarters_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"])/3;
		/**********************************************************************************************************************************/
		else if($kpi_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($days_result["avgScore"] == NULL)
		$score = ($kpi_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($weeks_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $kpi_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($months_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($quarters_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"] + $kpi_result["avgScore"])/4;
		/**********************************************************************************************************************************/
		else $score = ($kpi_result["avgScore"] + $quarters_result["avgScore"] + $months_result["avgScore"] + $weeks_result["avgScore"] + $days_result["avgScore"])/5;
		
		if($score != NULL) $data["score"] = round($score, 2);
		else $data["score"] = NULL;
		$data["scoreItemMonth"] = ($kpi_result["avgScore"] !== NULL && is_numeric($kpi_result["avgScore"])) ? round($kpi_result["avgScore"],2) : 0;
		$data["scoreItemWeek"] = ($weeks_result["avgScore"] !== NULL && is_numeric($weeks_result["avgScore"])) ? round($weeks_result["avgScore"],2) : 0;
		$data["scoreItemDay"] = ($days_result["avgScore"] !== NULL && is_numeric($days_result["avgScore"])) ? round($days_result["avgScore"],2) : 0;
		
		$data["red"] = 3.33;
		$data["green"] = 6.67;
		$data["darkGreen"] = 10.0;
				
		$data = json_encode($data);
		echo $data;
		
		if($i < $valuesCount-1) echo ", ";
		$data = NULL;
		$j = $j - 3;
	}
	echo "]";
}
function halfYearsAsIs($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType)
{
	global $connect;
	
	$year_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Yearly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Yearly')";
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

	$halfyear_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Bi-Annually'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Bi-Annually')";
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
	
	$quarters_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Quarterly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Quarterly')";
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
	
	$months_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Monthly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Monthly')";
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
	
	$weeks_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Weekly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Weekly')";
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
	
	$days_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Daily'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Daily')";
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
		$dateToHalfYear = date('Y-m',strtotime($variablePlusSix.$objectDate));
		$tempDate = halfYearLabels($dateToHalfYear);
		$data["date"] = $tempDate;
		$newdate = date('Y-m',strtotime($variablePlusSix.$objectDate));
		//$month_count = $month_count + 6;
		
		$monthDate = date('Y-m',strtotime($variablePlusSix.$objectDate));
		$yeardate = date('Y',strtotime($variablePlusSix.$objectDate));
	
		$kpi_query = mysqli_query($connect, "SELECT AVG(measureyears.3score * measure.weight) AS avgScore
		FROM measureyears, measure
		WHERE measureyears.measureId IN ($year_in)
		AND measureyears.measureId = measure.id
		AND date LIKE '$yeardate%'");
		$kpi_result = @mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$halfYear_query = mysqli_query($connect, "SELECT AVG(measurehalfyear.3score * measure.weight) AS avgScore
		FROM measurehalfyear, measure
		WHERE measurehalfyear.measureId IN ($halfyear_in)
		AND measurehalfyear.measureId = measure.id
		AND date LIKE '$newdate%'");
		$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$quarters_query = mysqli_query($connect, "SELECT AVG(measurequarters.3score * measure.weight) AS avgScore
		FROM measurequarters, measure
		WHERE measurequarters.measureId IN ($quarters_in)
		AND measurequarters.measureId = measure.id
		AND date LIKE '$newdate%'");
		$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$jHalfPlus = $j - 6;
		$variablePlusPlusSix = "-".$jHalfPlus.'month';
		$datePlusSix = date('Y-m',strtotime($variablePlusPlusSix.$objectDate));
		$month_query = mysqli_query($connect, "SELECT AVG(measuremonths.3score * measure.weight) AS avgScore
		FROM measuremonths, measure
		WHERE measuremonths.measureId IN ($months_in)
		AND measuremonths.measureId = measure.id
		AND date >= '$newdate%' AND date < '$datePlusSix%'");
		$months_result = @mysqli_fetch_assoc($month_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);

		$weeks_query = mysqli_query($connect, "SELECT AVG(measureweeks.3score * measure.weight) AS avgScore
		FROM measureweeks, measure
		WHERE measureweeks.measureId IN ($weeks_in)
		AND measureweeks.measureId = measure.id
		AND date LIKE '$newdate%'");
		$weeks_result = @mysqli_fetch_assoc($weeks_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$days_query = mysqli_query($connect, "SELECT AVG(measuredays.3score * measure.weight) AS avgScore
		FROM measuredays, measure
		WHERE measuredays.measureId IN ($days_in)
		AND measuredays.measureId = measure.id
		AND date LIKE '$newdate%'");
		$days_result = @mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);

		if($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL) 
		$score = $days_result["avgScore"];
		else if ($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $weeks_result["avgScore"];
		else if($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $months_result["avgScore"];
		else if($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $quarters_result["avgScore"];
		else if($kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $halfYear_result["avgScore"];
		else if($quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $kpi_result["avgScore"];
		/**********************************************************************************************************************************/
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $kpi_result["avgScore"])/2;
		else if($kpi_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"])/2;
		else if($kpi_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $months_result["avgScore"])/2;
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $quarters_result["avgScore"])/2;
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $halfYear_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $kpi_result["avgScore"])/2;
		else if($kpi_result["avgScore"] == NULL && $days_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $quarters_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $halfYear_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $quarters_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $halfYear_result["avgScore"])/2;	
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($quarters_result["avgScore"] + $kpi_result["avgScore"])/2;	
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL) 
		$score = ($quarters_result["avgScore"] + $halfYear_result["avgScore"])/2;
		/**********************************************************************************************************************************/
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $months_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL)
		$score = ($kpi_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($kpi_result["avgScore"] + $months_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;

		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($weeks_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($weeks_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"] + $kpi_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $months_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $months_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $months_result["avgScore"] + $kpi_result["avgScore"])/3;
		
		else if($months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $halfYear_result["avgScore"])/3;

		else if($months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $kpi_result["avgScore"])/3;
		
		else if($quarters_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"])/3;
		/**********************************************************************************************************************************/
		else if($kpi_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($days_result["avgScore"] == NULL)
		$score = ($kpi_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($weeks_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $kpi_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($months_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($quarters_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"] + $kpi_result["avgScore"])/4;
		/**********************************************************************************************************************************/
		else $score = ($kpi_result["avgScore"] + $quarters_result["avgScore"] + $months_result["avgScore"] + $weeks_result["avgScore"] + $days_result["avgScore"])/5;
		
		if($score != NULL) $data["score"] = round($score, 2);
		else $data["score"] = NULL;
		$data["scoreItemMonth"] = ($kpi_result["avgScore"] !== NULL && is_numeric($kpi_result["avgScore"])) ? round($kpi_result["avgScore"],2) : 0;
		$data["scoreItemWeek"] = ($weeks_result["avgScore"] !== NULL && is_numeric($weeks_result["avgScore"])) ? round($weeks_result["avgScore"],2) : 0;
		$data["scoreItemDay"] = ($days_result["avgScore"] !== NULL && is_numeric($days_result["avgScore"])) ? round($days_result["avgScore"],2) : 0;
		
		$data["red"] = 3.33;
		$data["green"] = 6.67;
		$data["darkGreen"] = 10.0;
				
		$data = json_encode($data);
		echo $data;
		
		if($i < $valuesCount-1) echo ", ";
		$data = NULL;
		$j = $j - 6;
	}
	echo "]";
}
function yearsAsIs($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType)
{
	global $connect;
	
	$year_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Yearly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Yearly')";
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

	$halfyear_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Bi-Annually'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Bi-Annually')";
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
	
	$quarters_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Quarterly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Quarterly')";
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
	
	$months_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Monthly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Monthly')";
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
	
	$weeks_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Weekly'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Weekly')";
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
	
	$days_id_query="SELECT id FROM measure where linkedObject = '$objectId' AND calendarType = 'Daily'
	OR id = (SELECT measurelinks.measure_id FROM measure, measurelinks 
			WHERE measurelinks.linked_id = '$objectId' 
			AND measure.id = measurelinks.measure_id
			AND measure.calendarType = 'Daily')";
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
		//$objectDate = strtotime ( '+1 years' , strtotime ( $objectDate.'-01-01' ) ) ;
			//$objectDate = date ( 'Y-m-d' , $objectDate );
		$variable = "-".$j.'years';
		$objectDate = date('Y-m-d',strtotime($objectDate.'-01-01'));
		$newdate = date('Y',strtotime($variable.$objectDate));
		//$newdate = date('Y', $newdate);
		$data["date"] = $newdate;
		 
		$kpi_query = mysqli_query($connect, "SELECT AVG(measureyears.3score * measure.weight) AS avgScore
		FROM measureyears, measure
		WHERE measureyears.measureId IN ($year_in)
		AND measureyears.measureId = measure.id
		AND date LIKE '$newdate%'");
		$kpi_result = @mysqli_fetch_assoc($kpi_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$halfYear_query = mysqli_query($connect, "SELECT AVG(measurehalfyear.3score * measure.weight) AS avgScore
		FROM measurehalfyear, measure
		WHERE measurehalfyear.measureId IN ($halfyear_in)
		AND measurehalfyear.measureId = measure.id
		AND date LIKE '$newdate%'");
		$halfYear_result = @mysqli_fetch_assoc($halfYear_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$quarters_query = mysqli_query($connect, "SELECT AVG(measurequarters.3score * measure.weight) AS avgScore
		FROM measurequarters, measure
		WHERE measurequarters.measureId IN ($quarters_in)
		AND measurequarters.measureId = measure.id
		AND date LIKE '$newdate%'");
		$quarters_result = @mysqli_fetch_assoc($quarters_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$month_query = mysqli_query($connect, "SELECT AVG(measuremonths.3score * measure.weight) AS avgScore
		FROM measuremonths, measure
		WHERE measuremonths.measureId IN ($months_in)
		AND measuremonths.measureId = measure.id
		AND date LIKE '$newdate%'");
		$months_result = @mysqli_fetch_assoc($month_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$weeks_query = mysqli_query($connect, "SELECT AVG(measureweeks.3score * measure.weight) AS avgScore
		FROM measureweeks, measure
		WHERE measureweeks.measureId IN ($weeks_in)
		AND measureweeks.measureId = measure.id
		AND date LIKE '$newdate%'");
		$weeks_result = @mysqli_fetch_assoc($weeks_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		$days_query = mysqli_query($connect, "SELECT AVG(measuredays.3score * measure.weight) AS avgScore
		FROM measuredays, measure
		WHERE measuredays.measureId IN ($days_in)
		AND measuredays.measureId = measure.id
		AND date LIKE '$newdate%'");
		$days_result = @mysqli_fetch_assoc($days_query) or file_put_contents("myObjScores.txt", "Could not execute kpi query => ".mysqli_error($connect), FILE_APPEND);
		
		if($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL) 
		$score = $days_result["avgScore"];
		else if ($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $weeks_result["avgScore"];
		else if($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $months_result["avgScore"];
		else if($kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $quarters_result["avgScore"];
		else if($kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $halfYear_result["avgScore"];
		else if($quarters_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $days_result["avgScore"] == NULL) 
		$score = $kpi_result["avgScore"];
		/**********************************************************************************************************************************/
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $kpi_result["avgScore"])/2;
		else if($kpi_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"])/2;
		else if($kpi_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $months_result["avgScore"])/2;
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $quarters_result["avgScore"])/2;
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $halfYear_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $kpi_result["avgScore"])/2;
		else if($kpi_result["avgScore"] == NULL && $days_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $quarters_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $halfYear_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $quarters_result["avgScore"])/2;
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $halfYear_result["avgScore"])/2;	
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($quarters_result["avgScore"] + $kpi_result["avgScore"])/2;	
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL) 
		$score = ($quarters_result["avgScore"] + $halfYear_result["avgScore"])/2;
		/**********************************************************************************************************************************/
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL) 
		$score = ($months_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $months_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL) 
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL)
		$score = ($kpi_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($kpi_result["avgScore"] + $months_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $weeks_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;

		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($weeks_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($weeks_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($days_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($weeks_result["avgScore"] + $months_result["avgScore"] + $kpi_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $months_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $quarters_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $months_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"] + $halfYear_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $months_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($months_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($weeks_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $months_result["avgScore"] + $kpi_result["avgScore"])/3;
		
		else if($months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $halfYear_result["avgScore"])/3;

		else if($months_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $quarters_result["avgScore"])/3;
		
		else if($months_result["avgScore"] == NULL && $quarters_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $kpi_result["avgScore"])/3;
		
		else if($quarters_result["avgScore"] == NULL && $kpi_result["avgScore"] == NULL && $halfYear_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"])/3;
		/**********************************************************************************************************************************/
		else if($kpi_result["avgScore"] == NULL) 
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($days_result["avgScore"] == NULL)
		$score = ($kpi_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($weeks_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $kpi_result["avgScore"] + $months_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($months_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $kpi_result["avgScore"] + $quarters_result["avgScore"])/4;
		else if($quarters_result["avgScore"] == NULL)
		$score = ($days_result["avgScore"] + $weeks_result["avgScore"] + $months_result["avgScore"] + $kpi_result["avgScore"])/4;
		/**********************************************************************************************************************************/
		else $score = ($kpi_result["avgScore"] + $quarters_result["avgScore"] + $months_result["avgScore"] + $weeks_result["avgScore"] + $days_result["avgScore"])/5;
		
		if($score != NULL) $data["score"] = round($score, 2);
		else $data["score"] = NULL;
		$data["scoreItemMonth"] = ($kpi_result["avgScore"] !== NULL && is_numeric($kpi_result["avgScore"])) ? round($kpi_result["avgScore"],2) : 0;
		$data["scoreItemWeek"] = ($weeks_result["avgScore"] !== NULL && is_numeric($weeks_result["avgScore"])) ? round($weeks_result["avgScore"],2) : 0;
		$data["scoreItemDay"] = ($days_result["avgScore"] !== NULL && is_numeric($days_result["avgScore"])) ? round($days_result["avgScore"],2) : 0;
		
		$data["red"] = 3.33;
		$data["green"] = 6.67;
		$data["darkGreen"] = 10.0;
				
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount-1) echo ", ";
		$data = NULL;
		$j--;
	}
	echo "]";
}
?>