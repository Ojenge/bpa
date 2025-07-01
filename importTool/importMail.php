<?php
include_once("../analytics/config_mysqli.php");
include_once("import_functions.php");
include_once("../analytics/scorecards/functions.php");
include_once("../mathParser/math_parser.php");

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;

function getAttachment()//Testing function - avoids repeated calls to mail server when coding.
{
	$filename = "YTD_Sales_October.xlsx";
	$mailDate = "02-Oct-2022 21:30:41 +0000";
	return array($filename, $mailDate);
}

echo '<table border="1">';
$query = mysqli_query($connect, "SELECT * FROM import_map WHERE fireDay = '1'");
$count = 1;
$mailArray = [];
while($row = mysqli_fetch_array($query))
{
	$previousId = $row["kpi"];
	$id = $row["kpi"];
	echo "<tr><td colspan='8'>Processing $id; previousId: $previousId</td><td></td></tr>";
	//get last import to decide how far back to read mail - consider using db stored run date as opposed to this which can be complicated
	//$queryDate = mysqli_query($connect, "SELECT date FROM import_data WHERE kpi = '$id' ORDER BY date DESC LIMIT 1");
	//$lastDate = mysqli_fetch_assoc($queryDate);
	
	//if($lastDate == "") $lastDate = date("d F Y", strtotime("-1 day")); 
	//temp - a day from today then change to month after code tests well.
	
	$lastDate = "03 October 2022"; //For initial run. Delete after first import.
	
	$subject = $row["emailSubject"];
	$from = $row["sender"];
	
	//$mailArray = getMailAttachment($lastDate, $subject, $from);
	$mailArray = getAttachment();// For testing; comment and uncomment the above line when done
	$filename = $mailArray[0];
	
    //strip the date off the last timezone crap. It's causing me a lot of unnecessary challenges. 01Sep2022 2233hrs
	$mailArray[1] = substr($mailArray[1], 0, 20);

	$siku = DateTime::createFromFormat('d-M-Y H:i:s', $mailArray[1]);//16-May-2022 05:01:40
	
	//$siku = DateTime::createFromFormat('d-M-Y H:i:s e', $mailDate);//16-May-2022 05:01:40 +0000
	
	//DateTime error messages
	var_dump(DateTime::createFromFormat('d-M-Y H:i:s',$mailArray[1]));
	echo "<br>";
	var_dump(DateTime::getLastErrors());
	
	$emailDate = $siku->format('Y-m-d H:i:s');
	$emailYear = $siku->format('Y');
	$emailMonth = $siku->format('m');
	
	//echo "<tr><td colspan='8'>Filename = $filename and Date = $emailDate</td></tr>";
		
	$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
	$reader->setReadDataOnly(true); //ignore styling, data validation,... and just read cell data
	$spreadsheet = $reader->load($filename);
	
	switch($row["frequency"])
	{
		case "monthly":
		{
            echo "Here 1";
			$queryCells = mysqli_query($connect, "SELECT month, value, target, name FROM import_months WHERE measureId = '$id'");
			/*
			value  => Cell Number with measure actual
			target => Cell Number with measure target
			name   => Cell Number with measure name
			*/
			while($cellRow = mysqli_fetch_array($queryCells))//open the file and read the cells below...
			{
                echo "Here 2";
				if($cellRow["month"] == "Current Month") //For imports that have a distinct file each month.
				$date = date("Y-m-d", strtotime($emailYear."-".$emailMonth."-01"));
				else
				$date = date("Y-m-d", strtotime($emailYear."-".$cellRow["month"]."-01"));//For imports where all months are in one file
				
				$name = $spreadsheet->getActiveSheet()->getCell($cellRow["name"]);
				
				$expressionArray = [];
				$actualInput = $cellRow["value"];
                
				$expressionArrayTarget = [];
                $targetInput = $cellRow["target"];
				
				$pattern = '/\+/';
				$replacement = '$0 ';
				$actualOutput = preg_replace($pattern,$replacement,$actualInput);
				if($targetInput != NULL || $targetInput != "") $targetOutput = preg_replace($pattern,$replacement,$targetInput);
				echo "Here 3 $actualInput<br>";
				$actualInput = $actualOutput;
				$targetInput = $targetOutput;
				$pattern = '/\+/';
				$replacement = ' $0';
				$actualOutput = preg_replace($pattern,$replacement,$actualInput);
				if($targetInput != NULL || $targetInput != "") $targetOutput = preg_replace($pattern,$replacement,$targetInput);
				echo "Here 4 $actualOutput<br>";
				$expressionArray = str_word_count($actualOutput, 1, '0..9+-*/');
				
                if($targetInput != NULL || $targetInput != "") $expressionArrayTarget = str_word_count($targetOutput, 1, '0..9+-*/');
				//print_r($expressionArray);
				$count = 0;
				foreach($expressionArray as $tempArray)
				{
					if($tempArray != "+" && $tempArray != "-" && $tempArray != "*" && $tempArray != "/" && $tempArray != "100")
					$expressionArray[$count] = $spreadsheet->getActiveSheet()->getCell($tempArray);
                    echo "Here 5 $tempArray => ".$expressionArray[$count]."<br>";
					$count++;
				}
				$expressionArray = implode(" ", $expressionArray);
				echo "Here 6 $expressionArray => $actual";
                $actual = mathParser($expressionArray);
				
                unset($expressionArray); 
				$actual = (string)$actual;//Convert to string since gettype($actual) shows spreadsheet reads this as an object
				echo "Here 7 $actual";
                if($actual == "") break; //No need of saving zero or empty values.
				echo "Here 8 $actual";
				$target = NULL;
				if($targetInput != NULL || $targetInput != "") 
				{
					$count = 0;
					foreach($expressionArrayTarget as $tempArrayTarget)
					{
						if($tempArrayTarget != "+" && $tempArrayTarget != "-" && $tempArrayTarget != "*" && $tempArrayTarget != "/" && $tempArrayTarget != "100")
						$expressionArrayTarget[$count] = $spreadsheet->getActiveSheet()->getCell($tempArrayTarget);
						$count++;
					}
					
					$expressionArrayTarget = implode(" ", $expressionArrayTarget);
					
					$target = mathParser($expressionArrayTarget);
					unset($expressionArrayTarget); 
					$target = (string)$target;//Convert to string since gettype($target) shows spreadsheet reads this as an object
				}
				if($target == "0" || $target == "") $target = NULL;
				
				$checkDuplicate = mysqli_query($connect, "SELECT id FROM import_data 
				WHERE measureId = '$id' 
				AND value = '$actual' 
				AND period = '$date'");
				
				echo "<tr><td></td><td colspan='7'>Processing $id; value = $actual; period: $date</td></tr>";
				
				if(mysqli_num_rows($checkDuplicate) > 0)
				{
					echo "<tr><td colspan='8'>Found duplicates for $id for date: $date</td></tr>";
					//Check whether data has been updated manually since last import and replace with latest imported values. 
					//Imported data always takes precedence
					$calendarType = mysqli_query($connect, "SELECT calendarType FROM measure WHERE id = '$id'");
					$calendarType = mysqli_fetch_assoc($calendarType);
					$calendarType = $calendarType["calendarType"];
					$table = getMySQLTable($calendarType);
					
					$checkManualUpdate = mysqli_query($connect, "SELECT updater FROM $table 
					WHERE date = '$date' 
					AND measureId = '$id'
					AND updater != 'Accent Import'");
					
					if(mysqli_num_rows($checkManualUpdate) > 0)//Change done manually and needs to be overriden
					{
						echo "<tr><td colspan='8'>Change for $id done manually and needs to be overriden</td></tr>"; 
						saveSingleMeasureValue($id, $date, $actual, "Accent Import", $target);
					}
					
					//Update email date and file name only since values haven't changed...
					mysqli_query($connect, "UPDATE import_data SET date = '$emailDate', file = '$filename'
					WHERE measureId = '$id' 
					AND measureName = '$name' 
					AND value = '$actual' 
					AND period = '$date' 
					AND sender = '$from'");
				}
				else
				{
					echo "<tr><td> Saving for: ".$id."</td><td>".$name."</td><td>".$actual."</td><td>".$target."</td><td>".$date."</td><td>".$from."</td><td>".$actualInput."</td><td>".$targetInput."</td></tr>";
					
					/* Block during testing. Remove when code is working okay.*/
					mysqli_query($connect, "INSERT INTO import_data (file, measureId, measureName, value, period, sender, date) 
					VALUES ('$filename', '$id', '$name', '$actual', '$date', '$from', '$emailDate')") or die("Couldn't save");
					
					saveSingleMeasureValue($id, $date, $actual, "Accent Import", $target);
				}
			}
			break;	
		}
		default:
		{
			//do nothing	
		}
	}
	if($previousId != $id)
	{
		echo "<tr><td colspan='8'>Finished importing for KPI $id</td></tr>";	
	}
	$count++;
}
echo "</table>";
?>