<?php
include_once("../config/config_mysqli.php");
$json = $_POST["kpiValuesArray"];

$kpiId = $_POST['objectId'];
$tableQuery = mysqli_query($connect, "SELECT calendarType FROM measure WHERE id = '$kpiId'");
$tableResult = mysqli_fetch_assoc($tableQuery);
$calendarType = $tableResult["calendarType"];
$updater = $_POST['updater'];

date_default_timezone_set('Africa/Nairobi');

global $table;
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
//$table = "measuremonths";
$goalScore = NULL;
$threeScore = NULL;
$fourScore = NULL;
$fiveScore = NULL;

global $xmrArray;
$results = json_decode($json,true);

$results = array_reverse($results);

//$gaugeType = mysqli_query($connect, "SELECT gaugeType FROM measure WHERE id = '$kpiId'");
//$gaugeType = mysqli_fetch_assoc($gaugeType);
//global $gaugeType;
$logDate = date('Y-m-d H:i:s');
//file_put_contents("saveKpi.txt",$_POST['csvImportVar']." -> ID:".$kpiId."; \r\n  Data: ".$json);
$dateCount = 0;

$xmrCount = 0;
foreach($results as $items)
{
	//$gaugeType = $items["gaugeType"];
	$dateTemp = $items["date"];
	$actualTemp = $items["actual"];
	
	$xmrArray[$xmrCount][0] = $items["actual"];
	$xmrArray[$xmrCount][5] = $items["id"];
	
	//file_put_contents("checkXmR.txt","\r\n actual at $xmrCount => ".$xmrArray[$xmrCount][0].', '.$xmrArray[$xmrCount][5], FILE_APPEND);
	$xmrCount++;
	
	if($calendarType == 'Daily') 
	{
		$dateXmR = date("Y-m-d",strtotime($items["date"]));
		//file_put_contents("saveKpiError.txt",'\r\n Daily Date => '.$dateXmR,FILE_APPEND);
	}
	else if($calendarType == 'Weekly') 
	{
		if(isset($_POST["csvImportVar"]) && $_POST["csvImportVar"] == 'true') $dateXmR = date("Y-m-d",strtotime($items["date"]));
		else
		{
			$dateTemp = strstr($items["date"], ',', true);
			$dateXmR = date("Y-m-d",strtotime($dateTemp));
		}
	}
	else if($calendarType == 'Yearly') 
	{
		if(isset($_POST["csvImportVar"]) && $_POST["csvImportVar"] == 'true') $dateXmR = date("Y-m-d",strtotime($items["date"]));
		else
		{
			$dateTemp = 'Jan-'.$items["date"];
			$dateXmR = date("Y-01-01",strtotime($dateTemp));
		}
	}
	else 
	{
		if(isset($_POST["csvImportVar"]) && $_POST["csvImportVar"] == 'true') $dateXmR = date("Y-m-d",strtotime($items["date"]));
		else $dateXmR = date("Y-m-d",strtotime("01-".$items["date"]));
	}
	$check_measure = mysqli_query($connect, "SELECT measureId FROM $table WHERE measureId = '$kpiId' AND date = '$dateXmR'");
	$nullValue = "NULL";
	if(mysqli_num_rows($check_measure) > 0)
	{	
		//mysqli_query($connect, "UPDATE $table SET centralLine = NULL, mR = NULL, signalPointer = NULL WHERE measureId = '$id' AND date = '$dateXmR'") or 
		mysqli_query($connect, "DELETE FROM $table WHERE measureId = '$kpiId' AND date = '$dateXmR'");
	}
	//check and delete items in the audit trail that have not changed.
	$check_audit = mysqli_query($connect, "SELECT measureId, actual FROM kpi_audit WHERE measureId = '$kpiId' AND date = '$dateXmR'");
	$auditActual = mysqli_fetch_assoc($check_audit);
	$auditActual = $auditActual["actual"];
	//file_put_contents("checkAudit.txt","$auditActual, $actualTemp");
	if(mysqli_num_rows($check_audit) > 0 && $auditActual == $actualTemp)
	{	
		mysqli_query($connect, "DELETE FROM kpi_audit WHERE measureId = '$kpiId' AND date = '$dateXmR'");
	}
}
$myCount=0;
foreach ($results as $items)
{
	$actual = str_replace(',', '', $items["actual"]);
	$red = str_replace(',', '', $items["red"]);
	$green = str_replace(',', '', $items["green"]);
	$darkGreen = str_replace(',', '', $items["darkgreen"]);
	$blue = str_replace(',', '', $items["blue"]);
	$valueId = $items["id"];
	$gaugeType = $items["gaugeType"];
	
	//if($calendarType == 'Daily') $dateXmR = date("Y-m-d",strtotime($items["date"]));
	if($calendarType == 'Daily') 
	{
		$date = date("Y-m-d",strtotime($items["date"]));
		//file_put_contents("saveKpiError.txt",'\r\n Daily Date => '.$date,FILE_APPEND);
	}
	else if($calendarType == 'Weekly') 
	{
		if(isset($_POST["csvImportVar"]) && $_POST["csvImportVar"] == 'true') $date = date("Y-m-d",strtotime($items["date"]));
		else
		{
			$dateTemp = strstr($items["date"], ',', true);
			$date = date("Y-m-d",strtotime($dateTemp));
		}
	}
	else if($calendarType == 'Yearly') 
	{
		if(isset($_POST["csvImportVar"]) && $_POST["csvImportVar"] == 'true') $date = date("Y-m-d",strtotime($items["date"]));
		else
		{
			$dateTemp = 'Jan-'.$items["date"];
			$date = date("Y-01-01",strtotime($dateTemp));
		}
	}
	else 
	{
		if(isset($_POST["csvImportVar"]) && $_POST["csvImportVar"] == 'true')
		{
			$date = date("Y-m-d",strtotime($items["date"]));
			//file_put_contents("csvDate.txt", $_POST["csvImportVar"]." and date is $date");
		}
		else $date = date("Y-m-d",strtotime("01-".$items["date"]));
	}
	
	/*if(empty($actual)) $actual = 'NULL';*/
	if($actual == "") $actual = "NULL";
	if($red == "") $red = "NULL";
	if($blue == "") $blue = "NULL";
	if($green == "") $green = "NULL";
	if($darkGreen == "") $darkGreen = "NULL";
	
	if($actual == "NULL" && $red == "NULL" && $blue == "NULL" && $green == "NULL" && $darkGreen == "NULL"){}/*do not save null values*/
	else
	{
		traditionalScoring($gaugeType, $actual, $red, $green, $darkGreen, $blue);
		
		if($actual == "NULL") {}//don't insert anything if there is no actual data provided.	
		else
		{
			//file_put_contents("scoreCheck.txt", "goal => ".$goalScore." 3score => ".$threeScore." 4score => ".$fourScore." 4score => ".$fiveScore." updater => ".$updater, FILE_APPEND);
			if($updater == "ind1") $updater = "Accent Import";
			mysqli_query($connect, "INSERT INTO `$table` (`id`, `measureId`, `date`, `actual`, `red`, `blue`, `green`, `darkgreen`, `2score`, `3score`, `4score`, `5score`, `updater`) VALUES ('$valueId', '$kpiId', '$date', '".$actual."', '".$red."', '".$blue."', '".$green."', '".$darkGreen."', '".$goalScore."', '".$threeScore."', '".$fourScore."', '".$fiveScore."', '".$updater."')") or file_put_contents("kpiSaveError.txt","\r\n Did not save ".$date." & ids: ".$valueId."->".$kpiId." actual=>".$actual. "; red=>" .$red. "; green=>" .$green. "; darkGreen=>" .$darkGreen. "; blue=>" .$blue." & Scores: goalScore=>$goalScore, threeScore=>$threeScore, fourScore=>$fourScore, fiveScore=>$fiveScore in table $table", FILE_APPEND);
			$time = time();
			mysqli_query($connect, "INSERT INTO `kpi_audit` (`id`, `measureId`, `date`, `actual`, `red`, `blue`, `green`, `darkgreen`, `2score`, `3score`, `4score`, `5score`, `updater`, `time`) VALUES ('', '$kpiId', '$date', '".$actual."', '".$red."', '".$blue."', '".$green."', '".$darkGreen."', '".$goalScore."', '".$threeScore."', '".$fourScore."', '".$fiveScore."', '".$updater."', '".$time."')") or file_put_contents("kpiAuditError.txt","Did not execute measure save for ".$date." & ids: ".$valueId."->".$kpiId." Values: ".$actual. "; " .$red. "; " .$green. "; " .$darkGreen. "; " .$blue." and Scores: $goalScore, $threeScore, $fourScore, $fiveScore in table $table");
		}
		
		switch($gaugeType)// this ensures 3 score always has something so as to help with data retrieval for parent items.
		{
			case "goalOnly":
			{
				mysqli_query($connect, "UPDATE `$table` SET `3score` = '$goalScore' WHERE id = '$valueId'");
				break;	
			}
			case "threeColor":
			{
				//do nothing
				break;	
			}
			case "fourColor":
			{
				mysqli_query($connect, "UPDATE `$table` SET `3score` = '$fourScore' WHERE id = '$valueId'");
				break;	
			}
			case "fiveColor":
			{
				mysqli_query($connect, "UPDATE `$table` SET `3score` = '$fiveScore' WHERE id = '$valueId'");
				break;	
			}
		}
		
		if($red == "NULL") mysqli_query($connect, "UPDATE `$table` SET `red` = NULL WHERE id = '$valueId'");
		if($blue == "NULL") mysqli_query($connect, "UPDATE `$table` SET `blue` = NULL WHERE id = '$valueId'");
		if($green == "NULL") mysqli_query($connect, "UPDATE `$table` SET `green` = NULL WHERE id = '$valueId'");
		if($darkGreen == "NULL") mysqli_query($connect, "UPDATE `$table` SET `darkgreen` = NULL WHERE id = '$valueId'");
		
		$actual = NULL; $red = NULL; $green = NULL; $darkGreen = NULL; $blue = NULL;
	}
}
XmR($table, $kpiId);

function XmR($table, $kpiId)
{	
	$xmrResult = mysqli_query($connect, "SELECT actual, id FROM $table WHERE measureId = '$kpiId' ORDER BY date ASC");
	$counter = 0;
	while($xmrRow = mysqli_fetch_array($xmrResult))
	{
		$xmrArray[$counter][0] = $xmrRow["actual"];
		$xmrArray[$counter][5] = $xmrRow["id"];
		$counter++;
	}
	$arraySize = sizeof($xmrArray);
	$run = 5;
	$signalPointer = 0;
	$sum = 0; $mR = 0;
	for ($i = 0; $i<$run; $i++)
	{
		$sum = $sum + $xmrArray[$i][0];
		if($i==0) 
		{
			$xmrArray[$i][1] = 0;
			$mR = $mR;
		}
		else 
		{
			$xmrArray[$i][1] = $xmrArray[$i][0] - $xmrArray[$i-1][0];
			$mR = $mR + abs($xmrArray[$i][1]);
		}
	}
	$centralLine = $sum/$run;
	$mRAverage = $mR/$run;
	
	for ($i = 0; $i<$run; $i++)
	{
		$UNPL = $centralLine + $mRAverage*2.66;
		$LNPL = $centralLine - $mRAverage*2.66;
		$xmrArray[$i][3] = $UNPL;
		$xmrArray[$i][4] = $LNPL;
	}
	//Loop through and save centralLine since it needs an average as well as the control limits
	for ($i = 0; $i<$run; $i++)
	{
		$xmrArray[$i][2] = $centralLine;
	}
	//file_put_contents("checkXmR.txt","\r\n Central line for first $run is $centralLine.", FILE_APPEND);
	
	$signalPointer = $run;
	$centralMinus = 0;
	$centralPlus = 0;
	$mR = 0;
	
	for ($i = $run; $i<$arraySize; $i++)
	{
		$xmrArray[$i][1] = $xmrArray[$i][0] - $xmrArray[$i-1][0]; //moving range difference
		$mR = $mR + abs($xmrArray[$i][1]);
		$xmrArray[$i][2] = $centralLine;
		$xmrArray[$i][3] = $UNPL;
		$xmrArray[$i][4] = $LNPL;
		
		if($xmrArray[$i][0] > $centralLine) $centralPlus++;
		if($xmrArray[$i][0] < $centralLine) $centralMinus++;
		if($centralPlus >= 1 && $centralMinus >= 1)
		{
			$centralPlus = 0;
			$centralMinus = 0;
			//$mR = 0;
			//file_put_contents("checkXmR.txt","\r\n Trend broken. Rest values.", FILE_APPEND);
		}
		if($centralPlus >= $run || $centralMinus >= $run)
		{
			$sum = 0;
			for($sumCount = $signalPointer; $sumCount <= $i; $sumCount++)
			{
				//echo $signalPointer.', '.$i.'<br>';
				$sum = $sum + $xmrArray[$sumCount][0];
				$mR = $mR + abs($xmrArray[$sumCount][1]);
			}
			$divisor = ($i - $signalPointer)+1;
			$centralLine = $sum/$divisor;
			$mRAverage = $mR/$divisor;
			//file_put_contents("checkXmR.txt","\r\n i = $i and signalPointer = $signalPointer, centralLine = $centralLine, mRAverage = $mRAverage", FILE_APPEND);
			$UNPL = $centralLine + $mRAverage*2.66;
			$LNPL = $centralLine - $mRAverage*2.66;
			for($sumCount = $signalPointer; $sumCount <= $i; $sumCount++)
			{
				$xmrArray[$sumCount][2] = $centralLine;
				$xmrArray[$sumCount][3] = $UNPL;
				$xmrArray[$sumCount][4] = $LNPL;
			}
			//echo "<b>New centraline </b> => ".$centralLine.'<br>';
			$centralMinus = 0;
			$centralPlus = 0;
			$mR = 0;
			$signalPointer = $i+1;
		}
		//file_put_contents("checkXmR.txt","\r\n i => '".$i.", actual => ".$xmrArray[$i][0].", mR => ".$xmrArray[$i][1].", mRSum => ".$mR.", centralLine => ".$xmrArray[$i][2].", centralMinus => ".$centralMinus.", centralPlus => ".$centralPlus."<br>");
	}
	//file_put_contents("checkXmR.txt","\r\n arraySize => $arraySize", FILE_APPEND);
	for ($i = 0; $i<$arraySize; $i++)
	{
		//file_put_contents("checkXmR.txt","\r\n UPDATE $table SET i => ".$i." actual => ".$xmrArray[$i][0]."UNPL='".$xmrArray[$i][3]."', LNPL='".$xmrArray[$i][4]."', centralLine='".$xmrArray[$i][2]."'WHERE id = '".$xmrArray[$i][5]."", FILE_APPEND);
		mysqli_query($connect, "UPDATE $table SET UNPL='".$xmrArray[$i][3]."', LNPL='".$xmrArray[$i][4]."', centralLine='".$xmrArray[$i][2]."'
		WHERE id = '".$xmrArray[$i][5]."'") or file_put_contents("checkXmR.txt","\r\n Couldn't update $table");
		//echo'i => '.$i.', actual => '.$xmrArray[$i][0].', mR => '.$xmrArray[$i][1].', centralLine => '.$xmrArray[$i][2].', UNPL => '.$xmrArray[$i][3].', LNPL => '.$xmrArray[$i][4].'<br>';
	}
}

/*********************************************************************************************************************
BSC Traditional Scoring:
**********************************************************************************************************************/
function traditionalScoring($gaugeType, $actual, $red, $green, $darkGreen, $blue)
{
	global $goalScore, $threeScore, $fourScore, $fiveScore;
	//file_put_contents("scoreFunction.txt","\r\ngaugeType=>$gaugeType, actual=>$actual, red=>$red, green=>$green, darkGreen=>$darkGreen, blue=>$blue",FILE_APPEND);
	switch($gaugeType)
	{
		case "goalOnly":
		{
			/*********************************************************************************************************************
			1. 2 Color: Red Green
			*********************************************************************************************************************/
			if(abs($actual) < abs($green))
			$goalScore = 0;
			else
			$goalScore = 10;
			break;	
		}
		case "threeColor":
		{
			/*********************************************************************************************************************
			2. 3 Color: Red Yellow Green
			*********************************************************************************************************************/
			$threeScore = ((abs($actual) - abs($red))/(abs($green) - abs($red)) * ((1/3)+3)) + ((1/3)+3);
			if($threeScore > 10) $threeScore = 10;
			if($threeScore < 0) $threeScore = 0;
			break;	
		}
		case "fourColor":
		{
			/*********************************************************************************************************************
			3. 4 Color: Red Yellow Green DarkGreen
			*********************************************************************************************************************/
			if(abs($actual) <= abs($green))
			$fourScore = ((abs($actual) - abs($red))/(abs($green) - abs($red)) * 2.5) + 2.5;
			else if (abs($actual) > abs($green) && abs($actual) <= abs($darkGreen))
			$fourScore = ((abs($actual) - abs($green))/(abs($darkGreen) - abs($green)) * 2.5) + 5;
			else if(abs($actual) > abs($darkGreen))
			$fourScore = ((abs($actual) - abs($darkGreen))/(abs($darkGreen)) * 2.5) + 7.5;
			if($fourScore > 10) $fourScore = 10;
			if($fourScore < 0) $fourScore = 0;	
			break;	
		}
		case "fiveColor":
		{
			/*********************************************************************************************************************
			4. 5 Color: Red Yellow Green DarkGreen Blue
			*********************************************************************************************************************/
			if(abs($actual) <= abs($green))
			$fiveScore = ((abs($actual) - abs($red))/(abs($green) - abs($red)) * 2) + 2;
			else if (abs($actual) > abs($green) && abs($actual) <= $darkGreen)
			$fiveScore = ((abs($actual) - abs($green))/(abs($darkGreen) - abs($green)) * 2) + 4;
			else if(abs($actual) > $darkGreen && abs($actual) <= abs($blue))
			$fiveScore = ((abs($actual) - abs($darkGreen))/(abs($blue) - abs($darkGreen)) * 2) + 6;
			else if(abs($actual) > abs($blue))
			$fiveScore = ((abs($actual) - abs($blue))/(abs($blue)) * 2) + 8;
			if($fiveScore > 10) $fiveScore = 10;
			if($fiveScore < 0) $fiveScore = 0;
			break;	
		}
	}
}
?>