<?php
include_once("../config/config_mysqli.php");
/*
$xmrArraySingle[0] = 3.24;
$xmrArraySingle[1] = 3.23;
$xmrArraySingle[2] = 4.36;
$xmrArraySingle[3] = 2.92;
$xmrArraySingle[4] = 4.37;
$xmrArraySingle[5] = 4.29;
$xmrArraySingle[6] = 3.33;
$xmrArraySingle[7] = 4.69;
$xmrArraySingle[8] = 4.01;
$xmrArraySingle[9] = 4.56;
$xmrArraySingle[10] = 4.59;
$xmrArraySingle[11] = 3.17;
$xmrArraySingle[12] = 4.16;
$xmrArraySingle[13] = 4.74;
$xmrArraySingle[14] = 4.77;
$xmrArraySingle[15] = 6.03;
$xmrArraySingle[16] = 6.31;
$xmrArraySingle[17] = 5.70;
$xmrArraySingle[18] = 6.16;
$xmrArraySingle[19] = 6.42;
$xmrArraySingle[20] = 5.84;
$xmrArraySingle[21] = 7.34;
$xmrArraySingle[22] = 6.00;
*/

$objectId = $_POST['objectId'];
$objectType = $_POST['objectType'];
$objectPeriod = $_POST['objectPeriod'];
$objectDate = $_POST['objectDate'];
@$valuesCount = $_POST['valuesCount'];
$objectDate = strtotime($objectDate);
$objectDate = date("Y-m-d", strtotime("+1 month", $objectDate));

$objectId = "kpi1974";
$objectType = "measure";
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

$xmr_query="SELECT date, actual FROM $table WHERE measureId = '$objectId' AND date <= '$objectDate%' ORDER BY date ASC LIMIT 0, $valuesCount";

$xmr_result=mysqli_query($connect, $xmr_query) or file_put_contents("xmrError.txt", "Error => ".mysqli_error());
$row_count = mysqli_num_rows($xmr_result);

$count = 0;
while($row = mysqli_fetch_assoc($xmr_result))
{
	$xmrArraySingle[$count] = $row["actual"];
	$dateArray[$count] = date('M-y',strtotime($row["date"]));
	$count++;
}

$run = 5;
$actualArraySize = sizeof($actualArray);
$actualArraySize = sizeof($xmrArraySingle);
/*
echo "<table border='1'>";
echo "<tr><td>i</td><td>Pointer 1</td><td>Pointer 2</td><td>Actual</td><td>Central Line</td><td>MR</td><td>Average MR</td><td>Lower Limit</td><td>Upper Limit</td><td>Date</td></tr>";*/
$pointerOne = 1;
$pointerTwo = 1;
$mrPointerOne = 1;
$mrPointerTwo = 1;
$changeCountPlus = 0;
$changeCountMinus = 0;
$exitClause = $actualArraySize - $run;
$date = "Jan-07";

for($j = 0; $j <= $actualArraySize+1; $j++)//Create Moving Range array for upper and lower line computations
{
	//if($j == 0)
	//	$mrArray[$j] = "";
	//else
		$mrArray[$j] = abs($xmrArraySingle[$j+1] - $xmrArraySingle[$j]);
}

for($i=0; $i<$actualArraySize; $i++)
{
	
	if($i == 0)
	{
		$sliceCounter1 = 0;
		//$sliceCounter2 = $run;
		$central = array_slice($xmrArraySingle, $sliceCounter1, $run);
		$central = round(array_sum($central)/$run,2);
		$lowerLimit[0] = "";
		$upperLimit[0] = "";
		
		for($l = 0; $l < $actualArraySize; $l++)//Pre-populate central Array with initial central
		{
			$centralArray[$l] = $central;
		}
		//echo "<td>".$sliceCounter1."</td><td>".$sliceCounter2."</td><td>".$xmrArraySingle[$i]."</td><td>".$centralArray[$i]."</td><td>".$mrArray[$i]."</td><td></td><td>".$lowerLimit[$i]."</td><td>".$upperLimit[$i]."</td>";
		
	}
	else if($i == 1)
	{
		$sliceCounter1 = $run;
		$sliceCounter2 = $run+$run-1;
		$mrSliceCounter1 = 0;
		$mrSliceCounter2 = $run-1;
		$mr = abs($xmrArraySingle[$i] - $xmrArraySingle[$i-1]);
		//$mrAverage = array_slice($mrArray,$mrSliceCounter1, $mrSliceCounter2);
		$mrAverage = array_slice($mrArray,$mrSliceCounter1, $run);
		$mrAverage = round(array_sum($mrAverage)/$run,2);
		for($l = 0; $l < $actualArraySize; $l++)//Pre-populate central Array with initial central
		{
			$lowerLimit[$l] = round($central - $mrAverage*2.66,2);
			$upperLimit[$l] = round($central + $mrAverage*2.66,2);
			$mrAverageArray[$l] = $mrAverage;
		}
		$mrArray[0] = $mrArray[$i];
		$lowerLimit[0] = $lowerLimit[$i];
		$upperLimit[0] =  $upperLimit[$i];
		/*echo "<tr>";
		echo "<td>".$i."</td>";
		echo "<td>".$sliceCounter1."</td><td>".$sliceCounter2."</td><td>".$xmrArraySingle[0]."</td><td>".$centralArray[0]."</td><td>".$mrArray[0]."</td><td>".$mrAverageArray[0]."</td><td>".$lowerLimit[0]."</td><td>".$upperLimit[0]."</td>";
		echo "</tr><tr>";
		echo "<td>".$i."</td>";
		echo "<td>".$sliceCounter1."</td><td>".$sliceCounter2."</td><td>".$xmrArraySingle[$i]."</td><td>".$centralArray[$i]."</td><td>".$mrArray[$i]."</td><td>".$mrAverageArray[$i]."</td><td>".$lowerLimit[$i]."</td><td>".$upperLimit[$i]."</td>";
		echo "</tr>";
		*/
		echo "[";
		echo '{"unpl":'.$upperLimit[0].', "lnpl":'.$lowerLimit[0].', "actual":'.$xmrArraySingle[0].', "centralLine":'.$centralArray[0].', "date":"'.$dateArray[0].'"}';
		echo ",";
		
		echo '{"unpl":'.$upperLimit[$i].', "lnpl":'.$lowerLimit[$i].', "actual":'.$xmrArraySingle[$i].', "centralLine":'.$centralArray[$i].', "date":"'.$dateArray[$i].'"},';
	}
	else
	{
		$mr = abs($xmrArraySingle[$i] - $xmrArraySingle[$i-1]);
		
		if($i > $run)
		{
				$sliceCounter1 = $sliceCounter1+1;
				$sliceCounter2 = $sliceCounter2+1;
		}
		if($i == 2)
		{
			$mrSliceCounter1 = $run;
			$mrSliceCounter2 = $run+$run-1;
		}
		else
		{
			$mrSliceCounter1 = $run+$mrPointerOne;
			$mrSliceCounter2 = ($run+$run-1)+$mrPointerTwo;
		}
		if($i >= $run-1)//do the first review of central after initial run; $run-1 since $run skips one value.
		{
			$nextArraySet = array_slice($xmrArraySingle,$sliceCounter1, $run);
			$nextArraySetSize = sizeof($nextArraySet);
			$changeCountMinus = 0;
			$changeCountPlus = 0;
			for($k = 0; $k < $nextArraySetSize; $k++)//Loop through next set of values to check whether they are all above central line (signal for change)
			{
				if($nextArraySet[$k] > $central) 
				{
					$changeCountPlus++;
					$changeCountMinus = 0;
					//echo "$i => $nextArraySet[$k] is greater than $central => $changeCountPlus<br>";
					if($changeCountPlus == $run || $changeCountMinus == $run)//change central line and control limits since all values are above or below central line
					{
						
						//echo "$i Change = $changeCount and run = $run MR Counter = $mrSliceCounter1<br>";
						$newCentral = array_sum($nextArraySet)/$run;
						$central = $newCentral;
						$mrAverage = array_slice($mrArray, $mrSliceCounter1, $run);
						$mrAverage = round(array_sum($mrAverage)/$run,2);
						
						for($l = $sliceCounter1; $l < $actualArraySize; $l++)//Update central Array with new central
						{
							$centralArray[$l] = $central;
							$lowerLimit[$l] = round($central - $mrAverage*2.66,2);
							$upperLimit[$l] = round($central + $mrAverage*2.66,2);
							$mrAverageArray[$l] = $mrAverage;
						}
						
						$changeCountMinus = 0;
						$changeCountPlus = 0;
						
						//to avoid changing central line earlier than expected, move pointers by run steps
						$pointerOne = $pointerOne + $run-1;
						$pointerTwo = $pointerTwo + $run-1;
						$mrPointerOne = $mrPointerOne + $run-1;
						$mrPointerTwo = $mrPointerTwo + $run-1;
					}
				}
				else 
				{
					if($nextArraySet[$k] < $central)
					{
						$changeCountMinus++;
						$changeCountPlus = 0;
						//echo "$i => $nextArraySet[$k] is less than $central => $changeCountMinus<br>";
						if($changeCountPlus == $run || $changeCountMinus == $run)//change central line and control limits since all values are above central line
						{
							//echo "$i => $nextArraySet[$k] is less than $central => $changeCountMinus<br>";
							//echo "$i Change = $changeCountMinus(less) or $changeCountPlus(more) and run = $run MR Counter = $mrSliceCounter1; Slice Counter = $sliceCounter1 <br>";
							$newCentral = array_sum($nextArraySet)/$run;
							$central = $newCentral;
							$mrAverage = array_slice($mrArray, $mrSliceCounter1, $run);
							$mrAverage = round(array_sum($mrAverage)/$run,2);
							for($l = $sliceCounter1; $l < $actualArraySize; $l++)//Update central Array with new central
							{//echo $sliceCounter1."<br>";
								$centralArray[$l] = $central;
								$lowerLimit[$l] = round($central - $mrAverage*2.66,2);
								$upperLimit[$l] = round($central + $mrAverage*2.66,2);
								$mrAverageArray[$l] = $mrAverage;
							}
														
							$changeCountMinus = 0;
							$changeCountPlus = 0;
							
							//to avoid changing central line earlier than expected, move pointers by run steps
							$pointerOne = $pointerOne + $run-1;
							$pointerTwo = $pointerTwo + $run-1;
							$mrPointerOne = $mrPointerOne + $run-1;
							$mrPointerTwo = $mrPointerTwo + $run-1;
							//$mrSliceCounter1 = $mrSliceCounter1 +$run-1;
						}
					}
				}
			}
			
			
		}
		/*echo "<tr>";
		echo "<td>".$i."</td>";
		echo "<td>".$sliceCounter1."</td><td>".$sliceCounter2."</td><td>".$xmrArraySingle[$i]."</td><td>".$centralArray[$i]."</td><td>".$mrArray[$i]."</td><td>".$mrAverageArray[$i]."</td><td>".$lowerLimit[$i]."</td><td>".$upperLimit[$i]."</td>";
		echo "</tr>";
		*/
		if($i < $actualArraySize-1)
		echo '{"unpl":'.$upperLimit[$i].', "lnpl":'.$lowerLimit[$i].', "actual":'.$xmrArraySingle[$i].', "centralLine":'.$centralArray[$i].', "date":"'.$dateArray[$i].'"},';
		else
		echo '{"unpl":'.$upperLimit[$i].', "lnpl":'.$lowerLimit[$i].', "actual":'.$xmrArraySingle[$i].', "centralLine":'.$centralArray[$i].', "date":"'.$dateArray[$i].'"}]';
		
		$pointerTwo++;
		$pointerOne++;
		$mrPointerOne++;
		$mrPointerTwo++;
	}
	
}
//echo "</table>";
?>