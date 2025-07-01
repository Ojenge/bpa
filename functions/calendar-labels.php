<?php
include_once("../config/config_mysqli.php");

//halfYearLabels($startYear, "2016-01");
function halfYearLabels($date)
{
	global $connect;
	$year = date('Y', strtotime($date));
	$startYear = mysqli_query($connect, "SELECT value FROM settings WHERE item = 'Financial Year Start Date'");
	$startYear = @mysqli_fetch_assoc($startYear);
	$startYear = $startYear["value"];

	if(strlen($date) == 4)
	{
		if(date('Y') == $date)
		$date = date("Y-m-01", strtotime($date));
		
		else 
		$date = date("$date-12-01", strtotime($date));
	}
	if(strlen($date) == 7)
	{
		$date = date("Y-m-01", strtotime($date));
	}
	$date = substr($date, 5, -3);
	$startYear = substr($startYear, 5, -3);
	if($date <= $startYear+5) return "HY 1, $year";
	else return "HY 2, $year";
}
//echo $jaribio = quarterLabels("2014-07-01");
function quarterLabels2($date)
{
	global $connect;
	$startMonthQ = mysqli_query($connect, "SELECT value FROM settings WHERE item = 'Financial Year Start Date'");
	$startMonthQ = mysqli_fetch_assoc($startMonthQ);
	$startMonthQ = $startMonthQ["value"];
	$startMonthQ = date('m',$startMonthQ);
	
	switch(date("m", strtotime($date)))
	{
		case "01":
		{
			return "Q1 ".date('Y', strtotime($date));
			break;	
		}
		case "04":
		{
			return "Q2 ".date('Y', strtotime($date));
			break;	
		}
		case "07":
		{
			return "Q3 ".date('Y', strtotime($date));
			break;	
		}
		case "10":
		{
			return "Q4 ".date('Y', strtotime($date));
			break;	
		}	
	}
}
function quarterLabels($date)
{
	global $connect;
	$year = date('Y', strtotime($date));
	$startYearQ = mysqli_query($connect, "SELECT value FROM settings WHERE item = 'Financial Year Start Date'");
	$startYearQ = mysqli_fetch_assoc($startYearQ);
	$startYearQ = $startYearQ["value"];
	$month1 = date('m', strtotime($startYearQ));
	$month2 = date('m', strtotime($date));
	$monthDiff = abs($month2 - $month1);
	/*if(strlen($date) == 4)
	{
		if(date('Y') == $date)
		$date = date("Y-m-01", strtotime($date));
		
		else 
		$date = date("$date-12-01", strtotime($date));
	}
	if(strlen($date) == 7)
	{
		$date = date("Y-m-01", strtotime($date));
	}
	$date = substr($date, 0, -3);
	$startYearQ = substr($startYearQ, 0, -3);
	*/
	//$startYearQ = new DateTime($startYearQ);
	//$date = new DateTime($date);
	
	//$startYearQ = new DateTime("2014-07");
	//$date = new DateTime("2014-9");
	
	//$interval = $startYearQ->diff($date);
	//$interval = $date->diff($startYearQ);
	//$monthDiff =  $interval->m;
	//$monthDiff =  $interval->format('%m');
	//$monthDiff = monthsDif($date2, $date1);
	//echo $monthDiff = (($interval->format('%y') * 12) + $interval->format('%m'));
	//echo "<br>$monthDiff, $month1, $month2";
	if($monthDiff >= 0 && $monthDiff <= 2) return "Q1, $year";
	if($monthDiff >= 3 && $monthDiff <= 5) return "Q2, $year";
	if($monthDiff >= 6 && $monthDiff <= 8) return "Q3, $year";
	if($monthDiff >= 9 && $monthDiff <=11) return "Q4, $year";
	/*
	if($monthDiff >= 1 && $monthDiff <= 3 && $date < $startYearQ) return "Q4, $year";
	if($monthDiff >= 4 && $monthDiff <= 6 && $date < $startYearQ) return "Q3, $year";
	if($monthDiff >= 7 && $monthDiff <= 9 && $date < $startYearQ) return "Q2, $year";
	if($monthDiff >=10 && $monthDiff <=12 && $date < $startYearQ) return "Q1, $year";*/
	//if($monthDiff == 0 && $date < $startYearQ) return "Q1, $year";
	if($monthDiff == 0) return "Q1, $year";
}
function monthsDif($start, $end)
{
    // Assume YYYY-mm-dd - as is common MYSQL format
    $splitStart = explode('-', $start);
    $splitEnd = explode('-', $end);

    if (is_array($splitStart) && is_array($splitEnd)) {
        $startYear = $splitStart[0];
        $startMonth = $splitStart[1];
        $endYear = $splitEnd[0];
        $endMonth = $splitEnd[1];

        $difYears = $endYear - $startYear;
        $difMonth = $endMonth - $startMonth;

        if (0 == $difYears && 0 == $difMonth) { // month and year are same
            return 0;
        }
        else if (0 == $difYears && $difMonth > 0) { // same year, dif months
            return $difMonth;
        }
        else if (1 == $difYears) {
            $startToEnd = 13 - $startMonth; // months remaining in start year(13 to include final month
            return ($startToEnd + $endMonth); // above + end month date
        }
        else if ($difYears > 1) {
            $startToEnd = 13 - $startMonth; // months remaining in start year 
            $yearsRemaing = $difYears - 2;  // minus the years of the start and the end year
            $remainingMonths = 12 * $yearsRemaing; // tally up remaining months
            $totalMonths = $startToEnd + $remainingMonths + $endMonth; // Monthsleft + full years in between + months of last year
            return $totalMonths;
        }
    }
    else {
        return false;
    }
}
?>