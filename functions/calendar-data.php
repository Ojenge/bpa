<?php

function daysAsIs($objectId, $objectDate, $valuesCount, $red, $green, $darkgreen, $blue, $gaugeType)
{
    global $connect;
	$objective_count = mysqli_query($connect, "SELECT date FROM measuredays WHERE measureId = '$objectId' AND date <= '$objectDate%' ORDER BY date");
	$objective_count = mysqli_num_rows($objective_count);


	$j = $valuesCount-1;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'days';
		$data["date"] = date('d-M-y',strtotime($variable.$objectDate));
		$newdate = date('Y-m-d',strtotime($variable.$objectDate));
		$newdate = $newdate;
		
		$kpi_query = mysqli_query($connect, "SELECT red, green, darkgreen, blue,actual
		FROM measuredays WHERE measureId = '$objectId' AND date LIKE '%$newdate%'");
		
		$line = __LINE__;
		$kpi_result = mysqli_fetch_assoc($kpi_query) or
		file_put_contents("scoresError.txt", "Could not execute kpi query. calendar-data.php. line $line");
		$tempActual = ($kpi_result && isset($kpi_result["actual"])) ? $kpi_result["actual"] : null;
		
		if($tempActual != NULL) $data["actual"] = (float)$tempActual;
		else $data["actual"] = NULL;
		
		if(!$kpi_result || $kpi_result["red"] == NULL)
		{
			if($red == NULL) $data["red"] = NULL;
			else $data["red"] = (float)$red;
		}
		else $data["red"] = (float)$kpi_result["red"];
		if(!$kpi_result || $kpi_result["green"] == NULL) $data["green"] = (float)$green; else $data["green"] = (float)$kpi_result["green"];
		if(!$kpi_result || $kpi_result["darkgreen"] == NULL) $data["darkgreen"] = (float)$darkgreen;
			else $data["darkgreen"] = (float)$kpi_result["darkgreen"];
		if(!$kpi_result || $kpi_result["blue"] == NULL) $data["blue"] = (float)$blue; else $data["blue"] = (float)$kpi_result["blue"];
		
		$data["gaugeType"] = $gaugeType;
		
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j--;
	}
	echo "]";	
}
function weeksAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType)
{
    global $connect;
	$objective_count = mysqli_query($connect, "SELECT date FROM measureweeks WHERE measureId = '$objectId' 
		AND date <= '$objectDate%' ORDER BY date");
	$objective_count = mysqli_num_rows($objective_count);
	$maxSQLResults = $valuesCount+1;
	if($objective_count > $maxSQLResults)
	$objective_offset = $objective_count - $maxSQLResults;
	else
	$objective_offset = 0;
	$objective_query="SELECT actual, red, green, darkgreen, blue, date FROM measureweeks WHERE measureId = '$objectId' 
	AND date < '$objectDate%' ORDER BY date LIMIT $objective_offset, $maxSQLResults";
	$objective_result=mysqli_query($connect, $objective_query);
	$row_count = mysqli_num_rows($objective_result);
	
	if($row_count <= $valuesCount)
	{


	}
	$j = $valuesCount-1;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'weeks';
		$day = date('w', strtotime($objectDate));
		$week_start = strtotime($objectDate);
		$week_start = date('d-M-Y', strtotime('-'.$day.' days', $week_start));
		//$sikuTena = $week_start;
		$weekDate = date('d-M-Y',strtotime($variable.$week_start));
		$weekNumber = date("W", strtotime($weekDate));
		//$data["date"] = $weekDate.'<br>Wk '.$weekNumber;
		$data["date"] = 'Wk '.$weekNumber.' '.$weekDate;
		$newdate = date('Y-m-d',strtotime($variable.$week_start));
		//$newdate = $newdate;
		
		$kpi_query = mysqli_query($connect, "SELECT red, green, darkgreen, blue,actual
		FROM measureweeks WHERE measureId = '$objectId' AND date LIKE '%$newdate%'");
		
		$kpi_result = mysqli_fetch_assoc($kpi_query) or
		file_put_contents("myKpiScores.txt", "Could not execute kpi query. get-kpi-scores.php line 120");
		$tempActual = ($kpi_result && isset($kpi_result["actual"])) ? $kpi_result["actual"] : null;
		
		if($tempActual != NULL) $data["actual"] = (float)$tempActual;
		else $data["actual"] = NULL;
		
		if(!$kpi_result || $kpi_result["red"] == NULL)
		{
			if($red == NULL) $data["red"] = NULL;
			else $data["red"] = (float)$red;
		}
		else $data["red"] = (float)$kpi_result["red"];
		if(!$kpi_result || $kpi_result["green"] == NULL) $data["green"] = (float)$green; else $data["green"] = (float)$kpi_result["green"];
		if(!$kpi_result || $kpi_result["darkgreen"] == NULL) $data["darkgreen"] = (float)$darkgreen;
			else $data["darkgreen"] = (float)$kpi_result["darkgreen"];
		if(!$kpi_result || $kpi_result["blue"] == NULL) $data["blue"] = (float)$blue; else $data["blue"] = (float)$kpi_result["blue"];
		
		$data["gaugeType"] = $gaugeType;
		
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j--;
	}
	echo "]";
}
function monthsAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType)
{
    //include_once("../config_msqli.php");
    global $connect;
	$objective_count = mysqli_query($connect, "SELECT date FROM measuremonths
	WHERE measureId = '$objectId' AND date <= '$objectDate%' ORDER BY date");
	if (!$objective_count) {
	    file_put_contents("myKpiScores.txt", "\r\n Could not execute objective count on line 135 calendar-data.php => ".mysqli_error($connect),FILE_APPEND);
	    return; // Exit the function if query fails
	}
	$objective_count = mysqli_num_rows($objective_count);
	$maxSQLResults = $valuesCount+1;
	if($objective_count > $maxSQLResults)
	$objective_offset = $objective_count - $maxSQLResults;
	else
	$objective_offset = 0;
	$objective_query="SELECT actual, red, green, darkgreen, blue, date FROM measuremonths WHERE measureId = '$objectId' 
	AND date < '$objectDate%' ORDER BY date LIMIT $objective_offset, $maxSQLResults";


	

	$j = $valuesCount-1;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'month';
		//$data["date"] = date('F',strtotime($variable.$objectDate));
		$data["date"] = date('M.Y',strtotime($variable.$objectDate));
		$newdate = date('Y-m',strtotime($variable.$objectDate));
		$newdate = $newdate;
		
		$kpi_query = mysqli_query($connect, "SELECT actual, red, green, darkgreen, blue
		FROM measuremonths WHERE measureId = '$objectId' AND date LIKE '%$newdate%'");
		if (!$kpi_query) {
		    file_put_contents("myKpiScores.txt", "\r\n Could not execute kpi query on line 159 calendar-data.php => ".mysqli_error($connect),FILE_APPEND);
		    continue; // Skip this iteration if query fails
		}

		$kpi_result = mysqli_fetch_assoc($kpi_query);
		if ($kpi_result === false) {
		    file_put_contents("myKpiScores.txt", "\r\n Could not fetch kpi result on line 162 calendar-data.php => ".mysqli_error($connect),FILE_APPEND);
		}
		$tempActual = ($kpi_result && isset($kpi_result["actual"])) ? $kpi_result["actual"] : null;

		file_put_contents("myKpiScores.txt","\nnewdate = $newdate; tempActual = $tempActual", FILE_APPEND);
		
		if($tempActual != NULL) $data["actual"] = (float)$tempActual;
		else $data["actual"] = NULL;
		
		if(!$kpi_result || $kpi_result["red"] == NULL)
		{
			if($red == NULL) $data["red"] = NULL;
			else $data["red"] = (float)$red;
		}
		else $data["red"] = (float)$kpi_result["red"];
		if(!$kpi_result || $kpi_result["green"] == NULL) $data["green"] = (float)$green; else $data["green"] = (float)$kpi_result["green"];
		if(!$kpi_result || $kpi_result["darkgreen"] == NULL) $data["darkgreen"] = (float)$darkgreen;
			else $data["darkgreen"] = (float)$kpi_result["darkgreen"];
		if(!$kpi_result || $kpi_result["blue"] == NULL) $data["blue"] = (float)$blue; else $data["blue"] = (float)$kpi_result["blue"];
		$data["gaugeType"] = $gaugeType;
		
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j--;
	}
	echo "]";	
}
function quartersAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType)
{
    global $connect;
	//echo $objectDate;
	//2015-08-01
	
	//file_put_contents("getKpiScores.txt","$objectDate, $objectPeriod, $objectId, $objectType, $getCalendar");
	$objective_count = mysqli_query($connect, "SELECT date FROM measurequarters 
	WHERE measureId = '$objectId' AND date <= '$objectDate%' ORDER BY date");
	$objective_count = mysqli_num_rows($objective_count);
	$maxSQLResults = $valuesCount+1;
	if($objective_count > $maxSQLResults)
	$objective_offset = $objective_count - $maxSQLResults;
	else
	$objective_offset = 0;
	$objective_query="SELECT actual, red, green, darkgreen, blue, date FROM measurequarters WHERE measureId = '$objectId' 
	AND date < '$objectDate%' ORDER BY date DESC LIMIT $objective_offset, $maxSQLResults";
	$objective_result=mysqli_query($connect, $objective_query);
	$row_count = mysqli_num_rows($objective_result);
	
	if($row_count <= $valuesCount)
	{
		$defaults_resultsDate = mysqli_query($connect, "SELECT date FROM measurequarters WHERE measureId = '$objectId' 
		AND date <= '$objectDate%' ORDER BY date LIMIT 0,1");


	}
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
			$objectDate = date("Y-m", strtotime("-2 month", $objectDate));
			break;
		}
		case '10':
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

	$j = ($valuesCount-1) * 3;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'months';
		$dateToQuarter = date('Y-m',strtotime($variable.$objectDate));
		$tempDate = quarterLabels($dateToQuarter);
		$data["date"] = $tempDate;
		$newdate = date('Y-m', strtotime($variable.$objectDate));
		$newdate = $newdate;
		
		$kpi_query = mysqli_query($connect, "SELECT actual, red, green, darkgreen, blue
		FROM measurequarters WHERE measureId = '$objectId' AND date LIKE '%$newdate%'");
		//file_put_contents("getQuarterKpiScores.txt","\n $objectDate, $objectId, $newdate, $tempDate");
		$kpi_result = mysqli_fetch_assoc($kpi_query) or
		file_put_contents("quarterAsIs.txt", "\r\n Could not execute kpi query at quarterasis on line 283 calendar-data.php", FILE_APPEND);
		$tempActual = ($kpi_result && isset($kpi_result["actual"])) ? $kpi_result["actual"] : null;
		
		if($tempActual != NULL) $data["actual"] = (float)$tempActual;
		else $data["actual"] = NULL;
		
		if(!$kpi_result || $kpi_result["red"] == NULL)
		{
			if($red == NULL) $data["red"] = NULL;
			else $data["red"] = (float)$red;
		}
		else $data["red"] = (float)$kpi_result["red"];
		if(!$kpi_result || $kpi_result["green"] == NULL) $data["green"] = (float)$green; else $data["green"] = (float)$kpi_result["green"];
		if(!$kpi_result || $kpi_result["darkgreen"] == NULL) $data["darkgreen"] = (float)$darkgreen;
			else $data["darkgreen"] = (float)$kpi_result["darkgreen"];
		if(!$kpi_result || $kpi_result["blue"] == NULL) $data["blue"] = (float)$blue; else $data["blue"] = (float)$kpi_result["blue"];
		$data["gaugeType"] = $gaugeType;
		
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j = $j - 3;
	}
	echo "]";
}
function halfYearsAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType)
{
    global $connect;
	$objectDatePlus = date("Y", strtotime('+1 years', strtotime($objectDate)));
	$objectDatePlusFormatted = $objectDatePlus . "-12-31"; // Convert year to end of year date for comparison
	$objective_count = mysqli_query($connect, "SELECT actual FROM measurehalfyear WHERE measureId = '$objectId'
	AND date <= '$objectDatePlusFormatted' ORDER BY date");
	$objective_count = mysqli_num_rows($objective_count);
	if($objective_count > ($valuesCount+1))
	$objective_offset = $objective_count - ($valuesCount+1);
	else
	$objective_offset = 0;
	$maxSQLResults = $valuesCount+1;
	$objective_query="SELECT SUBSTRING(date, -10, 7) as halfyear_part
	FROM measurehalfyear WHERE measureId = '$objectId'
	AND date <= '$objectDatePlusFormatted' GROUP BY SUBSTRING(date, -10, 7) ORDER BY MAX(date) DESC LIMIT $objective_offset, $maxSQLResults";
	$objective_result=mysqli_query($connect, $objective_query);
	$row_count = mysqli_num_rows($objective_result);
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
	

	$j = ($valuesCount-1) * 6;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.' month';
		$halfYrDate = date('Y-m',strtotime($variable.$objectDate));
		$data["date"] = halfYearLabels($halfYrDate);
		$newdate = date('Y-m',strtotime($variable.$objectDate));
		
		$kpi_query = mysqli_query($connect, "SELECT actual, red, green, darkgreen, blue
		FROM measurehalfyear WHERE measureId = '$objectId' AND date LIKE '$newdate%'");
		$kpi_result = mysqli_fetch_assoc($kpi_query);
		$tempActual = ($kpi_result && isset($kpi_result["actual"])) ? $kpi_result["actual"] : null;
		if($tempActual != NULL) $data["actual"] = (float)$tempActual;
		else $data["actual"] = NULL;
		
		if(!$kpi_result || $kpi_result["red"] == NULL)
		{
			if($red == NULL) $data["red"] = NULL;
			else $data["red"] = (float)$red;
		}
		else $data["red"] = (float)$kpi_result["red"];
		if(!$kpi_result || $kpi_result["green"] == NULL) $data["green"] = (float)$green; else $data["green"] = (float)$kpi_result["green"];
		if(!$kpi_result || $kpi_result["darkgreen"] == NULL) $data["darkgreen"] = (float)$darkgreen;
			else $data["darkgreen"] = (float)$kpi_result["darkgreen"];
		if(!$kpi_result || $kpi_result["blue"] == NULL) $data["blue"] = (float)$blue; else $data["blue"] = (float)$kpi_result["blue"];
		$data["gaugeType"] = $gaugeType;
		
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j = $j - 6;
	}
	echo "]";
}
function yearsAsIs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType)
{
    global $connect;
	$objectDatePlus = date("Y", strtotime('+1 years', strtotime($objectDate)));
	$objectDatePlusFormatted = $objectDatePlus . "-12-31"; // Convert year to end of year date for comparison
	$objective_count = mysqli_query($connect, "SELECT actual FROM measureyears WHERE measureId = '$objectId'
	AND date <= '$objectDatePlusFormatted' ORDER BY date");
	$objective_count = mysqli_num_rows($objective_count);
	if($objective_count > ($valuesCount+1))
	$objective_offset = $objective_count - ($valuesCount+1);
	else
	$objective_offset = 0;
	$maxSQLResults = $valuesCount+1;
	$objective_query="SELECT SUBSTRING(date, -10, 4) as year_part
	FROM measureyears WHERE measureId = '$objectId'
	AND date <= '$objectDatePlusFormatted' GROUP BY SUBSTRING(date, -10, 4) ORDER BY MAX(date) DESC LIMIT $objective_offset, $maxSQLResults";
	$objective_result=mysqli_query($connect, $objective_query);
	$row_count = mysqli_num_rows($objective_result);
	

	$j = $valuesCount-1;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'years';
		$data["date"] = date('Y',strtotime($variable.$objectDate));
		$newdate = date('Y',strtotime($variable.$objectDate));
		
		$kpi_query = mysqli_query($connect, "SELECT actual, red, green, darkgreen, blue
		FROM measureyears WHERE measureId = '$objectId' AND date LIKE '$newdate%'");
		$errorDate = date('d M Y H:i:s');
		$kpi_result = mysqli_fetch_assoc($kpi_query) or
		file_put_contents("myKpiScores.txt", "\r\n Could not execute kpi query on line 490 calendar-data.php date =>".$errorDate." => ".mysqli_error($connect), FILE_APPEND);
		$tempActual = ($kpi_result && isset($kpi_result["actual"])) ? $kpi_result["actual"] : null;
		
		if($tempActual != NULL) $data["actual"] = (float)$tempActual;
		else $data["actual"] = NULL;
		
		if(!$kpi_result || $kpi_result["red"] == NULL)
		{
			if($red == NULL) $data["red"] = NULL;
			else $data["red"] = (float)$red;
		}
		else $data["red"] = (float)$kpi_result["red"];
		if(!$kpi_result || $kpi_result["green"] == NULL) $data["green"] = (float)$green; else $data["green"] = (float)$kpi_result["green"];
		if(!$kpi_result || $kpi_result["darkgreen"] == NULL) $data["darkgreen"] = (float)$darkgreen;
			else $data["darkgreen"] = (float)$kpi_result["darkgreen"];
		if(!$kpi_result || $kpi_result["blue"] == NULL) $data["blue"] = (float)$blue; else $data["blue"] = (float)$kpi_result["blue"];
		$data["gaugeType"] = $gaugeType;
		
		$data = json_encode($data);
		echo $data;
		if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j--;
	}
	echo "]";
}
function daysInWeeks($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $dataType, $aggregationType)
{
    global $connect;
	$objective_count = mysqli_query($connect, "SELECT date FROM measuredays WHERE measureId = '$objectId' 
	AND date <= '$objectDate%' ORDER BY date");
	$objective_count = mysqli_num_rows($objective_count);
	$maxSQLResults = $valuesCount+1;
	if($objective_count > $maxSQLResults)
	$objective_offset = $objective_count - $maxSQLResults;
	else
	$objective_offset = 0;
	//echo $objective_offset;
	$objective_query="SELECT WEEK(date) as week_num FROM measuredays WHERE measureId = '$objectId'
	AND date < '$objectDate%' GROUP BY WEEK(date) ORDER BY MAX(date) DESC LIMIT $objective_offset, $maxSQLResults";
	$objective_result=mysqli_query($connect, $objective_query);
	$row_count = mysqli_num_rows($objective_result);
	//echo "<br>";
	

	$j = $valuesCount;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'weeks';
		$data["date"] = date('Y-m-d',strtotime($variable.$objectDate));
		$newdate = date('Y-m-d',strtotime($variable.$objectDate));
		$newdate = $newdate;
		
		$kpi_query = mysqli_query($connect, "SELECT WEEK(date), SUM(red) AS red, SUM(green) AS green, 
		SUM(darkgreen) AS darkgreen, SUM(blue) AS blue, SUM(actual) AS actual
		FROM measuredays WHERE measureId = '$objectId' AND date LIKE '%$newdate%' GROUP BY WEEK(date)");
		
		$kpi_result = mysqli_fetch_assoc($kpi_query) or 
		file_put_contents("myKpiScores.txt", "\r\n Could not execute kpi query on line 526 calendar-data.php",FILE_APPEND);
		$tempActual = $kpi_result["actual"];
		
		if($tempActual != NULL) $data["actual"] = (float)$tempActual;
		else $data["actual"] = NULL;
		
		if($kpi_result["red"] == NULL) 
		{
			if($red == NULL) $data["red"] = NULL;
			else $data["red"] = (float)$red; 
		}
		else $data["red"] = (float)$kpi_result["red"];
		if($kpi_result["green"] == NULL) $data["green"] = (float)$green; else $data["green"] = (float)$kpi_result["green"];
		if($kpi_result["darkgreen"] == NULL) $data["darkgreen"] = (float)$darkgreen; 
			else $data["darkgreen"] = (float)$kpi_result["darkgreen"];
		if($kpi_result["blue"] == NULL) $data["blue"] = (float)$blue; else $data["blue"] = (float)$kpi_result["blue"];
		$data["gaugeType"] = $gaugeType;
		
		$data = json_encode($data);
		echo $data;
		//if($i < $add_values) echo ", ";
		if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j--;
	}
	echo "]";
}
function inMonths($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $table, $dataType, $aggregationType)
{
    global $connect;
//echo $objectDate;
	$objective_count = mysqli_query($connect, "SELECT date FROM $table WHERE measureId = '$objectId' 
	AND date <= '$objectDate%' ORDER BY date");
	$objective_count = mysqli_num_rows($objective_count);
	$maxSQLResults = $valuesCount+1;
	if($objective_count > $maxSQLResults )
	$objective_offset = $objective_count - $maxSQLResults ;
	else
	$objective_offset = 0;
	//echo $objective_offset;
	$objective_query="SELECT SUM(red) AS red, SUM(green) As green, 
	SUM(darkgreen) AS darkgreen, SUM(blue) AS blue, SUM(actual) AS actual FROM $table WHERE measureId = '$objectId' 
	AND date < '$objectDate%' GROUP BY SUBSTRING(date, -10, 7) ORDER BY date LIMIT $objective_offset, $maxSQLResults ";
	$objective_result=mysqli_query($connect, $objective_query);
	$row_count = mysqli_num_rows($objective_result);
	//echo "<br>";
	
	$add_values = $maxSQLResults  - $row_count;
	$count = 0;
	$j = $valuesCount;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'month';
		$data["date"] = date('M-y',strtotime($objectDate.$variable));
		$newdate = date('Y-m',strtotime($objectDate.$variable));
		$newdate = $newdate;
		//echo "Date: ".$newdate;
		//echo $aggregationType;
		switch($aggregationType)
		{
			case 'Sum':
			{
				$kpi_query = mysqli_query($connect, "SELECT SUBSTRING(date, -10, 7), SUM(red) AS red, SUM(green) AS green, 
				SUM(darkgreen) AS darkgreen, SUM(blue) AS blue, SUM(actual) AS actual
				FROM $table WHERE measureId = '$objectId' AND date LIKE '%$newdate%' GROUP BY SUBSTRING(date, -10, 7)");
				break;
			}
			case 'Average':
			{
				$kpi_query = mysqli_query($connect, "SELECT SUBSTRING(date, -10, 7), AVG(red) AS red, AVG(green) AS green, 
				AVG(darkgreen) AS darkgreen, AVG(blue) AS blue, AVG(actual) AS actual
				FROM $table WHERE measureId = '$objectId' AND date LIKE '%$newdate%' GROUP BY SUBSTRING(date, -10, 7)");
				break;
			}
			case 'Last Value':
			{
				$kpi_query = mysqli_query($connect, "SELECT SUBSTRING(date, -10, 7), red, green, darkgreen, blue, actual
				FROM $table WHERE measureId = '$objectId' AND date LIKE '%$newdate%' ORDER BY date DESC LIMIT 1");
				break;
			}
		}
		$kpi_result = mysqli_fetch_assoc($kpi_query) or 
		file_put_contents("myKpiScores.txt", "\r\n Could not execute kpi query on line 608 calendar-data.php",FILE_APPEND);
		$tempActual = $kpi_result["actual"];
		
		if($tempActual != NULL) $data["actual"] = (float)$tempActual;
		else $data["actual"] = NULL;
		
		$data["red"] = (float)$kpi_result['red'];
		if($kpi_result["red"] == NULL) 
		{
			if($red == NULL) $data["red"] = NULL;
			else $data["red"] = (float)$red; 
		}
		//$data["green"] = (float)$green;
		$data["green"] = (float)$kpi_result['green'];
		if($data["green"] == NULL) $data["green"] = (float)$green;
		$data["darkgreen"] = (float)$kpi_result['darkgreen'];
		if($data["darkgreen"] == NULL) $data["darkgreen"] = (float)$darkgreen;
		$data["blue"] = (float)$kpi_result['blue'];
		if($data["blue"] == NULL) $data["blue"] = (float)$blue;
		$data["upperLimit"] = (float)$upperLimit;
		$data["gaugeType"] = $gaugeType;
		
		$data = json_encode($data);
		echo $data;
		//if($i < $add_values) echo ", ";
		if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j--;
	}
	echo "]";
}
function inQuarters($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $table, $dataType, $aggregationType)
{
    global $connect;
	$objective_count = mysqli_query($connect, "SELECT date FROM $table WHERE measureId = '$objectId' 
	AND date <= '$objectDate%' ORDER BY date");
	$objective_count = mysqli_num_rows($objective_count);
	$maxSQLResults = $valuesCount+1;
	if($objective_count > $maxSQLResults )
	$objective_offset = $objective_count - $maxSQLResults ;
	else
	$objective_offset = 0;
	//echo $objective_offset;
	$objective_query="SELECT QUARTER(date) AS quarter, SUM(red) AS red, SUM(green) As green, 
	SUM(darkgreen) AS darkgreen, SUM(blue) AS blue, SUM(actual) AS actual FROM $table WHERE measureId = '$objectId' 
	AND date < '$objectDate%' GROUP BY QUARTER(date) ORDER BY date LIMIT $objective_offset, $maxSQLResults";
	$objective_result=mysqli_query($connect, $objective_query);
	$row_count = mysqli_num_rows($objective_result);
	//echo "<br>";
	
	$add_values = $maxSQLResults  - $row_count;
	$count = 0;
	$j = ($valuesCount-1) * 3;
	//$getCurrentQuarter = mysqli_query($connect, "SELECT QUARTER(date) AS quarter FROM $table WHERE date LIKE '$objectDate'");
	//$getCurrentQuarter = mysqli_fetch_assoc($getCurrentQuarter);
	//$quarter = $getCurrentQuarter["quarter"];
	//$j = $valuesCount;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.'months';
		$newdate = date('Y',strtotime($variable.$objectDate));
		$quarterDate = date('Y-m',strtotime($variable.$objectDate));
		$quarterDate2 = date('Y-m-01', strtotime($quarterDate));
		$quarterDate2 = strtotime($quarterDate2);
		$quarterDate2 = date('Y-m', strtotime('+3 months',$quarterDate2));//was picking exact quarter dates yet build up dates don't need to be in quarters so had to add this to give the range for each quarter in the SQL below.
		$getCurrentQuarter = mysqli_query($connect, "SELECT QUARTER(date) AS quarter FROM $table WHERE date >= '$quarterDate' AND date < '$quarterDate2'");
		$getCurrentQuarter = mysqli_fetch_assoc($getCurrentQuarter);
		$quarter = $getCurrentQuarter["quarter"];
		switch($aggregationType)
		{
			case 'Sum':
			{
				$kpi_query = mysqli_query($connect, "SELECT QUARTER(date) AS quarter, SUM(red) AS red, SUM(green) AS green,
				SUM(darkgreen) AS darkgreen, SUM(blue) AS blue, SUM(actual) AS actual
				FROM $table WHERE measureId = '$objectId' AND QUARTER(date) = '$quarter' AND date LIKE '%$newdate%' GROUP BY QUARTER(date)");
				break;
			}
			case 'Average':
			{
				$kpi_query = mysqli_query($connect, "SELECT QUARTER(date) AS quarter, AVG(red) AS red, AVG(green) AS green,
				AVG(darkgreen) AS darkgreen, AVG(blue) AS blue, AVG(actual) AS actual
				FROM $table WHERE measureId = '$objectId' AND QUARTER(date) = '$quarter' AND date LIKE '%$newdate%' GROUP BY QUARTER(date)");
				break;
			}
			case 'Last Value':
			{
				$kpi_query = mysqli_query($connect, "SELECT QUARTER(date), red, green, darkgreen, blue, actual
				FROM $table WHERE measureId = '$objectId' AND QUARTER(date) = '$quarter' ORDER BY date DESC LIMIT 1");
				break;
			}
		}
		
		$kpi_result = mysqli_fetch_assoc($kpi_query) or 
		file_put_contents("myKpiScores.txt", "\r\n Could not execute kpi query on line 701 calenda-data.php",FILE_APPEND);
		
		//$data["date"] = "Q$quarter, ".$newdate;
		$data["date"] = quarterLabels(date('Y-m',strtotime($variable.$objectDate)));
		//$tempQuarter = $kpi_result["quarter"];
		//if($tempQuarter != NULL) $data["quarter"] = (float)$tempQuarter;
		//else $data["quarter"] = "Q ".$quarter;
		
		$tempActual = $kpi_result["actual"];
		if($tempActual != NULL) $data["actual"] = (float)$tempActual;
		else $data["actual"] = NULL;
		
		$tempRed = $kpi_result["red"];
		
		if($tempRed != NULL) $data["red"] = (float)$tempRed;
		else
		{
			if($red == NULL) $data["red"] = NULL;
			else $data["red"] = (float)$red; 
		}
		
		$tempGreen = $kpi_result["green"];
		if($tempGreen != NULL) $data["green"] = (float)$tempGreen;
		else $data["green"] = (float)$green;
		
		$tempDarkGreen = $kpi_result["darkgreen"];
		if($tempDarkGreen != NULL) $data["darkgreen"] = (float)$tempDarkGreen;
		else $data["darkgreen"] = (float)$darkgreen;
		
		$tempBlue = $kpi_result["blue"];
		if($tempBlue != NULL) $data["blue"] = (float)$tempBlue;
		else $data["blue"] = (float)$blue;

		$data["upperLimit"] = (float)$upperLimit;
		$data["gaugeType"] = $gaugeType;
		
		$data = json_encode($data);
		echo $data;
		//if($i < $add_values) echo ", ";
		if($i < $valuesCount) echo ", "; 
		//echo "$quarterDate->$quarter <br>";
		$data = NULL;
		$j = $j - 3;
	}
	echo "]";
}
function inHalfs($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $table, $dataType, $aggregationType)
{
    global $connect;
	$objective_count = mysqli_query($connect, "SELECT date FROM $table WHERE measureId = '$objectId' 
	AND date <= '$objectDate%' ORDER BY date");
	$objective_count = mysqli_num_rows($objective_count);
	$maxSQLResults = $valuesCount+1;
	if($objective_count > $maxSQLResults)
	$objective_offset = $objective_count - $maxSQLResults;
	else
	$objective_offset = 0;
	//echo $objective_offset;
	
	$objective_query="SELECT QUARTER(date) AS quarter, SUM(red) AS red, SUM(green) As green, 
	SUM(darkgreen) AS darkgreen, SUM(blue) AS blue, SUM(actual) AS actual FROM $table WHERE measureId = '$objectId' 
	AND date <= '$objectDate%' GROUP BY QUARTER(date) ORDER BY date LIMIT $objective_offset, $maxSQLResults";
	$objective_result=mysqli_query($connect, $objective_query);
	$row_count = mysqli_num_rows($objective_result);
	//echo "<br>";
	if($row_count <= $valuesCount)
	{
		$defaults_resultsDate = mysqli_query($connect, "SELECT date FROM $table WHERE measureId = '$objectId' 
		AND date <= '$objectDate%' ORDER BY date DESC LIMIT 0,1");
		$defaults_resultsDate_row = mysqli_fetch_array($defaults_resultsDate);
		$date = $defaults_resultsDate_row["date"];
	}
	
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
		
	$add_values = $maxSQLResults  - $row_count;
	$count = 0;
	$j = ($valuesCount-1) * 6;
	
	$halfYearActual = 0; $halfYearRed = 0; $halfYearGreen = 0; $halfYearDarkGreen = 0; $halfYearBlue = 0;
	//$j = $valuesCount;
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		$variable = "-".$j.' month';
		//$data["date"] = date('Y-m',strtotime($objectDate.$variable));
		$newdate = date('Y-m',strtotime($variable.$objectDate));
		$datePlusSix = strtotime($newdate);
		$datePlusSix = date('Y-m',strtotime("+6 month", $datePlusSix));
		
		$currentQuarters = mysqli_query($connect, "");//retrieve quarters from current half year and use these in the SQL below...
		switch($aggregationType)
		{
			case 'Sum':
			{
				$kpi_query = mysqli_query($connect, "SELECT SUM(red) AS red, SUM(green) AS green,
				SUM(darkgreen) AS darkgreen, SUM(blue) AS blue, SUM(actual) AS actual
				FROM $table WHERE measureId = '$objectId' AND date >= '$newdate%' AND date <= '$datePlusSix%'");
				break;
			}
			case 'Average':
			{
				$kpi_query = mysqli_query($connect, "SELECT AVG(red) AS red, AVG(green) AS green,
				AVG(darkgreen) AS darkgreen, AVG(blue) AS blue, AVG(actual) AS actual
				FROM $table WHERE measureId = '$objectId' AND date >= '$newdate%' AND date <= '$datePlusSix%'");
				break;
			}
			case 'Last Value':
			{
				$kpi_query = mysqli_query($connect, "SELECT QUARTER(date) AS quarter, red, green, darkgreen, blue, actual FROM $table WHERE measureId = '$objectId' AND date >= '$newdate%' AND date < '$datePlusSix%' ORDER BY date DESC LIMIT 1");
				break;
			}
		}
		
		$kpi_result = mysqli_fetch_assoc($kpi_query) or
		file_put_contents("myKpiScores.txt", "\r\n Could not execute kpi query on line 836 calendar-data.php",FILE_APPEND);

		// Calculate quarter from the date instead of from query result
		$quarter = ceil(date('n', strtotime($newdate)) / 3);
		$tempActual = $kpi_result["actual"];
		$tempRed = $kpi_result["red"];
		$tempGreen = $kpi_result["green"];
		$tempDarkGreen = $kpi_result["darkgreen"];
		$tempBlue = $kpi_result["blue"];
		$halfdate = $newdate;
		if($quarter == 3 || $quarter == 4)
		{
			$halfYearDisplay = "HY 2";
			$data["date"] = halfYearLabels($halfdate);
			
			if($tempActual != NULL) $data["actual"] = (float)$tempActual;
			else $data["actual"] = NULL;
			
			if($tempRed != NULL) $data["red"] = (float)$tempRed;
			else
			{
				if($red == NULL) $data["red"] = NULL;
				else $data["red"] = (float)$red; 
			}
			
			if($tempGreen != NULL) $data["green"] = (float)$tempGreen;
			else $data["green"] = (float)$green;
			
			if($tempDarkGreen != NULL) $data["darkgreen"] = (float)$tempDarkGreen;
			else $data["darkgreen"] = (float)$darkgreen;
			
			if($tempBlue != NULL) $data["blue"] = (float)$tempBlue;
			else $data["blue"] = (float)$blue;
			
			$data["upperLimit"] = (float)$upperLimit;
			$data["gaugeType"] = $gaugeType;
		}
		else
		{
			$halfYearDisplay = "HY 1";
			$data["date"] = halfYearLabels($halfdate);
			
			if($tempActual != NULL) $data["actual"] = (float)$tempActual;
			else $data["actual"] = NULL;
			
			if($tempRed != NULL) $data["red"] = (float)$tempRed;
			else $data["red"] = (float)$red;
			
			if($tempGreen != NULL) $data["green"] = (float)$tempGreen;
			else $data["green"] = (float)$green;
			
			if($tempDarkGreen != NULL) $data["darkgreen"] = (float)$tempDarkGreen;
			else $data["darkgreen"] = (float)$darkgreen;
			
			if($tempBlue != NULL) $data["blue"] = (float)$tempBlue;
			else $data["blue"] = (float)$blue;
			
			$data["upperLimit"] = (float)$upperLimit;
			$data["gaugeType"] = $gaugeType;
		}
		
		//if($quarter == 2 || $quarter == 4) 
		//{
			$data = json_encode($data);
			echo $data;
			$data = NULL;
			if($i < $valuesCount) echo ", ";			
			//if($i < $kpiYrCount) echo ", ";
			$halfYearActual = 0; $halfYearRed = 0; $halfYearGreen = 0; $halfYearDarkGreen = 0; $halfYearBlue = 0;
		//}
		$j = $j - 6;
	}
	echo "]";
}
function inYears($objectId, $objectDate, $valuesCount, $upperLimit, $red, $green, $darkgreen, $blue, $gaugeType, $table, $dataType, $aggregationType)
{
    global $connect;
	$objective_count = mysqli_query($connect, "SELECT SUM(red) AS red, SUM(green) As green, SUM(darkgreen) AS darkgreen, 
	SUM(blue) AS blue, SUM(actual) AS actual FROM measuredays WHERE measureId = '$objectId' AND date <= '$objectDate%' ORDER BY date DESC");
	$objective_count = mysqli_num_rows($objective_count);
	$maxSQLResults = $valuesCount+1;
	//echo "<br>";
	if($objective_count > $maxSQLResults)
	$objective_offset = $objective_count - $maxSQLResults;
	else
	$objective_offset = 0;
	
	//echo $objective_offset;
	$objective_query="SELECT SUM(red) AS red, SUM(green) As green, 
	SUM(darkgreen) AS darkgreen, SUM(blue) AS blue, SUM(actual) AS actual FROM $table WHERE measureId = '$objectId' 
	AND date <= '$objectDate%' GROUP BY SUBSTRING(date, -10, 4) ORDER BY date DESC LIMIT $objective_offset, $maxSQLResults";
	$objective_result=mysqli_query($connect, $objective_query);
	$row_count = mysqli_num_rows($objective_result);
	//echo "<br>";

	$add_values = $maxSQLResults - $row_count;
	//$add_values_count = 0;
	//echo $row_count;
	$count = 0;
	//$j = $add_values;
	$j = ($valuesCount-1);
	echo "[";
	for($i = 1; $i <= $valuesCount; $i++)
	{
		//echo "Date: $objectDate and variable $j with add values: $add_values";
		$variable = "-".$j.' years';
		$data["date"] = date('Y',strtotime($variable.$objectDate));
		$newdate = date('Y',strtotime($variable.$objectDate));
		//$newdate = $newdate;
		//echo "Date: ".$newdate;
		//echo 'aggregationType: '.$aggregationType;
		switch($aggregationType)
		{
			case 'Sum':
			{
				//echo "$objectId, $newdate, $table";
				$kpi_query = mysqli_query($connect, "SELECT SUBSTRING(date, -10, 4), SUM(red) AS red, SUM(green) AS green, 
				SUM(darkgreen) AS darkgreen, SUM(blue) AS blue, SUM(actual) AS actual
				FROM $table WHERE measureId = '$objectId' AND date LIKE '%$newdate%' GROUP BY SUBSTRING(date, -10, 4)");
				//$kpi_result = mysqli_fetch_assoc($kpi_query);
				//file_put_contents("debug.txt","\r\n newdate => $newdate, ".$kpi_result["green"],FILE_APPEND);
				break;
			}
			case 'Average':
			{
				$kpi_query = mysqli_query($connect, "SELECT SUBSTRING(date, -10, 4), AVG(red) AS red, AVG(green) AS green, 
				AVG(darkgreen) AS darkgreen, AVG(blue) AS blue, AVG(actual) AS actual
				FROM $table WHERE measureId = '$objectId' AND date LIKE '%$newdate%' GROUP BY SUBSTRING(date, -10, 4)");
				break;
			}
			case 'Last Value':
			{
				$kpi_query = mysqli_query($connect, "SELECT SUBSTRING(date, -10, 4), red, green, darkgreen, blue, actual
				FROM $table WHERE measureId = '$objectId' AND date LIKE '%$newdate%' ORDER BY date DESC LIMIT 1");
				break;
			}
		}
		
		$kpi_result = mysqli_fetch_assoc($kpi_query) or 
		file_put_contents("myKpiScores.txt", "\n\r Could not execute kpi query on line 701 calenda-data.php",FILE_APPEND);
		$tempActual = $kpi_result["actual"];
		
		if($tempActual != NULL) $data["actual"] = (float)$tempActual;
		else $data["actual"] = NULL;
		
		if($red == NULL || '') $data["red"] = NULL;
		else $data["red"] = (float)$red;
		$data["green"] = (float)$green;
		$data["darkgreen"] = (float)$darkgreen;
		$data["blue"] = (float)$blue;
		$data["upperLimit"] = (float)$upperLimit;
		$data["gaugeType"] = $gaugeType;
		
		$data = json_encode($data);
		echo $data;
		//if($i < $add_values) echo ", ";
		if($i < $valuesCount) echo ", ";
		$data = NULL;
		$j--;
	}
	echo "]";	
}
?>