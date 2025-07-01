<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");
//if(isset($_POST['objectId']))
//{
	
	@$objectId = $_POST['objectId'];
	@$objectType = $_POST['objectType'];
	@$objectDate = $_POST['objectDate'];
	@$objectPeriod = $_POST['objectPeriod'];
	
	/*$objectId = "kpi588";
	$objectType = "measure";
	$objectDate = "2022-05";
	$objectPeriod = "months";*/
	
	//$objectId = "kpi1";
	$measure_period = mysqli_query($connect, "SELECT calendarType, gaugeType FROM measure where id = '$objectId'");
	$period_row = mysqli_fetch_assoc($measure_period);

	if (!$period_row) {
		echo "[]";
		exit;
	}

	$gaugeType = $period_row["gaugeType"];
	$calendarType = $period_row["calendarType"];

	switch($calendarType)
	{
		case 'Daily':
		{
			$table = "measuredays";
			
			//$measure_query="SELECT * FROM $table WHERE measureId = '$objectId' AND date <= '$objectDate%' ORDER BY date";
			$measure_query="SELECT * FROM $table WHERE measureId = '$objectId' ORDER BY date DESC";
			$measure_result=mysqli_query($connect, $measure_query);
			$row_count = mysqli_num_rows($measure_result);
			
			if($row_count == 0)
			{
				$defaults_results = mysqli_query($connect, "SELECT red, green, darkGreen, blue FROM measure WHERE id = '$objectId'");
				$defaults_row = mysqli_fetch_array($defaults_results);
				$red = $defaults_row["red"];
				$green = $defaults_row["green"];
				if(preg_match('/[a-zA-Z]/', $green) == 1) $green = calculatedKpi($objectId);
				$darkGreen = $defaults_row["darkGreen"];
				$blue = $defaults_row["blue"];
				
				$data["gaugeType"] = $gaugeType;
				$data = json_encode($data);
				//echo "[".$data."]";
				$data = NULL;
			}
			else
			{
				$defaults_results = mysqli_query($connect, "SELECT red, green, darkGreen, blue FROM measure WHERE id = '$objectId'");
				$defaults_row = mysqli_fetch_array($defaults_results);
				$red = $defaults_row["red"];
				$green = $defaults_row["green"];
				if(preg_match('/[a-zA-Z]/', $green) == 1) $green = calculatedKpi($objectId);
				$darkGreen = $defaults_row["darkGreen"];
				$blue = $defaults_row["blue"];
				
				$myData = "[";
				$count = 0;
				//echo "[";
				//$data = array();
				while($row = mysqli_fetch_assoc($measure_result))
				{
					$data["id"] = (int)$row["id"];
					$data["gaugeType"] = $gaugeType;
					$data["actual"] = (float)$row["actual"];
					
					if($row["red"] == NULL) $data["red"] = $red; else $data["red"] = (float)$row["red"];
					if($row["green"] == NULL) $data["green"] = $green; else $data["green"] = (float)$row["green"];
					if($row["darkgreen"] == NULL) $data["darkgreen"] = $darkGreen; else $data["darkgreen"] = (float)$row["darkgreen"];
					if($row["blue"] == NULL) $data["blue"] = $blue; else $data["blue"] = (float)$row["blue"];
					
					//$data["red"] = (int)$row["red"];
					//$data["green"] = (int)$row["green"];
					//$data["darkgreen"] = (int)$row["darkgreen"];
					//$data["blue"] = (int)$row["blue"];
					$data["date"] = date('d-F-Y',strtotime($row['date']));
					$data = json_encode($data);
					//echo $data;
					if($count == 0)
					$myData = $myData.$data;
					else
					$myData = $myData.",".$data;
					
					$data = NULL;
					$count++;
					//if($count < $row_count) echo ",";
				}
				$myData = $myData."]";
				//echo "]";
			}
		
			//echo $myData;
			$myData = @json_decode($myData);
			$siku = date('d-F-Y',strtotime($objectDate));
			if(!$myData) {}
			else
			{
			for ($i = 1; $i<=12; $i++)
				{
					foreach ($myData as $newItems)
					{
						if($newItems->date == $siku)
						{
							//echo "<br>Data for ".$siku." there and ".$newItems->date;
							$myFinal[$i] = json_encode($newItems);
						}
					}
					$siku = date("d-F-Y", strtotime("-1 day", strtotime($siku)));
				}
			}
			//var_dump($myFinal);
			$data = NULL;
			$sikuTena = date('d-F-Y',strtotime($objectDate));
			$newId = mysqli_query($connect, "SELECT MAX(id) FROM $table");
			$newId = mysqli_fetch_assoc($newId);
			$newId = (int)$newId["MAX(id)"];
			$newId = $newId+13;
		
			for ($i = 1; $i<=12; $i++)
			{
				if(@!$myFinal[$i])
				{
					//echo "Adding empty at position $i with date $sikuTena<br>";
					$data["id"] = $newId;
					$data["gaugeType"] = $gaugeType;
					$data["actual"] = "";
					$data["red"] = $red;
					$data["green"] = $green;
					$data["darkgreen"] = $darkGreen;
					$data["blue"] = $blue;
					$data["date"] = $sikuTena;
					
					$data = json_encode($data);
					$myFinal[$i] = $data;
					$newId--;
					$data = NULL;
				}
				$sikuTena = date("d-F-Y", strtotime("-1 day", strtotime($sikuTena)));	
			}
			//var_dump($myFinal);
			echo "[";
			for ($i = 1; $i<=12; $i++)
			{
				echo $myFinal[$i];
				if($i<12) echo ", ";
			}
			echo "]";
			break;	
		}
		case 'Weekly':
		{
			$table = "measureweeks";
			
			//$measure_query="SELECT * FROM $table WHERE measureId = '$objectId' AND date <= '$objectDate%' ORDER BY date";
			$measure_query="SELECT * FROM $table WHERE measureId = '$objectId' ORDER BY date DESC";
			$measure_result=mysqli_query($connect, $measure_query);
			$row_count = mysqli_num_rows($measure_result);
			
			if($row_count == 0)
			{
				$defaults_results = mysqli_query($connect, "SELECT red, green, darkGreen, blue FROM measure WHERE id = '$objectId'");
				$defaults_row = mysqli_fetch_array($defaults_results);
				$red = $defaults_row["red"];
				$green = $defaults_row["green"];
				if(preg_match('/[a-zA-Z]/', $green) == 1) $green = calculatedKpi($objectId);
				$darkGreen = $defaults_row["darkGreen"];
				$blue = $defaults_row["blue"];
				
				$data["gaugeType"] = $gaugeType;
				$data = json_encode($data);
				//echo "[".$data."]";
				$data = NULL;
			}
			else
			{
				$defaults_results = mysqli_query($connect, "SELECT red, green, darkGreen, blue FROM measure WHERE id = '$objectId'");
				$defaults_row = mysqli_fetch_array($defaults_results);
				$red = $defaults_row["red"];
				$green = $defaults_row["green"];
				if(preg_match('/[a-zA-Z]/', $green) == 1) $green = calculatedKpi($objectId);
				$darkGreen = $defaults_row["darkGreen"];
				$blue = $defaults_row["blue"];
				
				$myData = "[";
				$count = 0;
				//echo "[";
				//$data = array();
				while($row = mysqli_fetch_assoc($measure_result))
				{
					$data["id"] = (int)$row["id"];
					$data["gaugeType"] = $gaugeType;
					$data["actual"] = (float)$row["actual"];
					
					if($row["red"] == NULL) $data["red"] = $red; else $data["red"] = (float)$row["red"];
					if($row["green"] == NULL) $data["green"] = $green; else $data["green"] = (float)$row["green"];
					if($row["darkgreen"] == NULL) $data["darkgreen"] = $darkGreen; else $data["darkgreen"] = (float)$row["darkgreen"];
					if($row["blue"] == NULL) $data["blue"] = $blue; else $data["blue"] = (float)$row["blue"];
					
					//$data["red"] = (int)$row["red"];
					//$data["green"] = (int)$row["green"];
					//$data["darkgreen"] = (int)$row["darkgreen"];
					//$data["blue"] = (int)$row["blue"];
					$data["date"] = date('d-M-Y',strtotime($row['date']));
					$data = json_encode($data);
					//echo $data;
					if($count == 0)
					$myData = $myData.$data;
					else
					$myData = $myData.",".$data;
					
					$data = NULL;
					$count++;
					//if($count < $row_count) echo ",";
				}
				$myData = $myData."]";
				//echo "]";
			}
		
			//echo $myData;
			$myData = @json_decode($myData);
			$day = date('w', strtotime($objectDate));
			$week_start = strtotime($objectDate);
			$week_start = date('d-M-Y', strtotime('-'.$day.' days', $week_start));
			
			$siku = $week_start;
			//$siku = date('d-F-Y',strtotime($objectDate));
			if(!$myData) {}
			else
			{
			for ($i = 1; $i<=12; $i++)
				{
					foreach ($myData as $newItems)
					{
						if($newItems->date == $siku)
						{
							//echo "<br>Data for ".$siku." there";
							$myFinal[$i] = json_encode($newItems);
						}
					}
					$siku = date("d-M-Y", strtotime("-7 day", strtotime($siku)));
				}
			}
			//var_dump($myFinal);
			$data = NULL;
			$day = date('w', strtotime($objectDate));
			$week_start = strtotime($objectDate);
			$week_start = date('d-M-Y', strtotime('-'.$day.' days', $week_start));
			$sikuTena = $week_start;
			//$week = date("W");
			//$week = $date->format("W");
			//$sikuTena = date('d-F-Y',strtotime($objectDate));
			$newId = mysqli_query($connect, "SELECT MAX(id) FROM $table");
			$newId = mysqli_fetch_assoc($newId);
			$newId = (int)$newId["MAX(id)"];
			$newId = $newId+13;
		
			for ($i = 1; $i<=12; $i++)
			{
				if(@!$myFinal[$i])
				{
					//echo "Adding empty at position $i with date $sikuTena<br>";
					$data["id"] = $newId;
					$data["gaugeType"] = $gaugeType;
					$data["actual"] = "";
					$data["red"] = $red;
					$data["green"] = $green;
					$data["darkgreen"] = $darkGreen;
					$data["blue"] = $blue;
					$weekNumber = date("W", strtotime($sikuTena));
					$data["date"] = $sikuTena.', Wk '.$weekNumber;
					
					$data = json_encode($data);
					$myFinal[$i] = $data;
					$newId--;
					$data = NULL;
				}
				$sikuTena = date("d-M-Y", strtotime("-7 day", strtotime($sikuTena)));
			}
			//var_dump($myFinal);
			echo "[";
			for ($i = 1; $i<=12; $i++)
			{
				echo $myFinal[$i];
				if($i<12) echo ", ";
			}
			echo "]";
			break;	
		}
		case 'Monthly':
		{
			$table = "measuremonths";
			$updater = "";
			//$measure_query="SELECT * FROM $table WHERE measureId = '$objectId' AND date <= '$objectDate%' ORDER BY date";
			$measure_query="SELECT * FROM $table WHERE measureId = '$objectId' ORDER BY date DESC";
			$measure_result=mysqli_query($connect, $measure_query);
			$row_count = mysqli_num_rows($measure_result);
			
			if($row_count == 0)
			{
				$defaults_results = mysqli_query($connect, "SELECT red, green, darkGreen, blue FROM measure WHERE id = '$objectId'");
				$defaults_row = mysqli_fetch_array($defaults_results);
				$red = $defaults_row["red"];
				$green = $defaults_row["green"];
				if(preg_match('/[a-zA-Z]/', $green) == 1) $green = calculatedKpi($objectId);
				$darkGreen = $defaults_row["darkGreen"];
				$blue = $defaults_row["blue"];
				
				$data["gaugeType"] = $gaugeType;
				$data = json_encode($data);
				//echo "[".$data."]";
				$data = NULL;
			}
			else
			{
				$defaults_results = mysqli_query($connect, "SELECT red, green, darkGreen, blue FROM measure WHERE id = '$objectId'");
				$defaults_row = mysqli_fetch_array($defaults_results);
				$red = $defaults_row["red"];
				$green = $defaults_row["green"];
				if(preg_match('/[a-zA-Z]/', $green) == 1) $green = calculatedKpi($objectId);
				$darkGreen = $defaults_row["darkGreen"];
				$blue = $defaults_row["blue"];
				
				$myData = "[";
				$count = 0;
				//echo "[";
				//$data = array();
				while($row = mysqli_fetch_assoc($measure_result))
				{
					$data["id"] = (int)$row["id"];
					$data["gaugeType"] = $gaugeType;
					$data["actual"] = (float)$row["actual"];
					$updater = $row["updater"];
					if($updater == "Accent Import") $updater = "SAGE";
					else
					{
						$updaterQuery = mysqli_query($connect, "SELECT display_name FROM uc_users WHERE user_id == '$updater'");
						$updaterResult = mysqli_fetch_array($updaterQuery);	
						$updater = $updaterResult["display_name"];
					}
					$data["updater"] = $updater;
					
					if($row["red"] == NULL) $data["red"] = $red; else $data["red"] = (float)$row["red"];
					if($row["green"] == NULL) $data["green"] = $green; else $data["green"] = (float)$row["green"];
					if($row["darkgreen"] == NULL) $data["darkgreen"] = $darkGreen; else $data["darkgreen"] = (float)$row["darkgreen"];
					if($row["blue"] == NULL) $data["blue"] = $blue; else $data["blue"] = (float)$row["blue"];
					
					//$data["red"] = (int)$row["red"];
					//$data["green"] = (int)$row["green"];
					//$data["darkgreen"] = (int)$row["darkgreen"];
					//$data["blue"] = (int)$row["blue"];
					$data["date"] = date('F-Y',strtotime($row['date']));
					$data = json_encode($data);
					//echo $data;
					if($count == 0)
					$myData = $myData.$data;
					else
					$myData = $myData.",".$data;
					
					$data = NULL;
					$count++;
					//if($count < $row_count) echo ",";
				}
				$myData = $myData."]";
				//echo "]";
			}
		
			//echo $myData;
			$myData = @json_decode($myData);
			$siku = date('F-Y',strtotime($objectDate));
			if(!$myData) {}
			else
			{
			for ($i = 1; $i<=12; $i++)
				{
					foreach ($myData as $newItems)
					{
						if($newItems->date == $siku)
						{
							//echo "<br>Data for ".$siku." there";
							$myFinal[$i] = json_encode($newItems);
						}
					}
					$siku = date("F-Y", strtotime("-1 month", strtotime($siku)));
				}
			}
			//var_dump($myFinal);
			$data = NULL;
			$sikuTena = date('F-Y',strtotime($objectDate));
			$newId = mysqli_query($connect, "SELECT MAX(id) FROM $table");
			$newId = mysqli_fetch_assoc($newId);
			$newId = (int)$newId["MAX(id)"];
			$newId = $newId+13;
		
			for ($i = 1; $i<=12; $i++)
			{
				if(@!$myFinal[$i])
				{
					//echo "Adding empty at position $i with date $sikuTena<br>";
					$data["id"] = $newId;
					$data["gaugeType"] = $gaugeType;
					$data["actual"] = "";
					$data["updater"] = $updater;
					$data["red"] = $red;
					$data["green"] = $green;
					$data["darkgreen"] = $darkGreen;
					$data["blue"] = $blue;
					$data["date"] = $sikuTena;
					
					$data = json_encode($data);
					$myFinal[$i] = $data;
					$newId--;
					$data = NULL;
				}
				$sikuTena = date("F-Y", strtotime("-1 month", strtotime($sikuTena)));	
			}
			//var_dump($myFinal);
			echo "[";
			for ($i = 1; $i<=12; $i++)
			{
				echo $myFinal[$i];
				if($i<12) echo ", ";
			}
			echo "]";
			break;	
		}
		case 'Quarterly':
		{
			$table = "measurequarters";
			//$measure_query="SELECT * FROM $table WHERE measureId = '$objectId' AND date <= '$objectDate%' ORDER BY date";
			$measure_query="SELECT * FROM $table WHERE measureId = '$objectId' ORDER BY date DESC";
			$measure_result=mysqli_query($connect, $measure_query);
			$row_count = mysqli_num_rows($measure_result);
			
			if($row_count == 0)
			{
				$defaults_results = mysqli_query($connect, "SELECT red, green, darkGreen, blue FROM measure WHERE id = '$objectId'");
				$defaults_row = mysqli_fetch_array($defaults_results);
				$red = $defaults_row["red"];
				$green = $defaults_row["green"];
				if(preg_match('/[a-zA-Z]/', $green) == 1) $green = calculatedKpi($objectId);
				$darkGreen = $defaults_row["darkGreen"];
				$blue = $defaults_row["blue"];
				
				$data["gaugeType"] = $gaugeType;
				$data = json_encode($data);
				//echo "[".$data."]";
				$data = NULL;
			}
			else
			{
				$defaults_results = mysqli_query($connect, "SELECT red, green, darkGreen, blue FROM measure WHERE id = '$objectId'");
				$defaults_row = mysqli_fetch_array($defaults_results);
				$red = $defaults_row["red"];
				$green = $defaults_row["green"];
				if(preg_match('/[a-zA-Z]/', $green) == 1) $green = calculatedKpi($objectId);
				$darkGreen = $defaults_row["darkGreen"];
				$blue = $defaults_row["blue"];
				
				$myData = "[";
				$count = 0;
				//echo "[";
				//$data = array();
				while($row = mysqli_fetch_assoc($measure_result))
				{
					$data["id"] = (int)$row["id"];
					$data["gaugeType"] = $gaugeType;
					$data["actual"] = (float)$row["actual"];
					
					if($row["red"] == NULL) $data["red"] = $red; else $data["red"] = (float)$row["red"];
					if($row["green"] == NULL) $data["green"] = $green; else $data["green"] = (float)$row["green"];
					if($row["darkgreen"] == NULL) $data["darkgreen"] = $darkGreen; else $data["darkgreen"] = (float)$row["darkgreen"];
					if($row["blue"] == NULL) $data["blue"] = $blue; else $data["blue"] = (float)$row["blue"];
					
					//$data["red"] = (int)$row["red"];
					//$data["green"] = (int)$row["green"];
					//$data["darkgreen"] = (int)$row["darkgreen"];
					//$data["blue"] = (int)$row["blue"];
					$data["date"] = date('F-Y',strtotime($row['date']));
					$data = json_encode($data);
					//echo $data;
					if($count == 0)
					$myData = $myData.$data;
					else
					$myData = $myData.",".$data;
					
					$data = NULL;
					$count++;
					//if($count < $row_count) echo ",";
				}
				$myData = $myData."]";
				//echo "]";
			}
			//echo $objectDate;
			$quarterMonth = date("m",strtotime($objectDate));
			switch($quarterMonth)
			{
				case '02':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m", strtotime("-1 month", $objectDate));
					break;
				}
				case '03':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m-d", strtotime("-2 month", $objectDate));
					break;
				}
				case '05':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m-d", strtotime("-1 month", $objectDate));
					break;
				}
				case '06':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m-d", strtotime("-2 month", $objectDate));
					break;
				}
				case '08':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m-d", strtotime("-1 month", $objectDate));
					break;
				}
				case '09':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m-d", strtotime("-2 month", $objectDate));
					break;
				}
				case '11':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m-d", strtotime("-1 month", $objectDate));
					break;
				}
				case '12':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m-d", strtotime("-2 month", $objectDate));
					//$objectDate = date("Y-m-d", strtotime("+1 years", $objectDate));
					break;
				}
			}
					//echo $myData;
			$myData = @json_decode($myData);
			$siku = date('F-Y',strtotime($objectDate));
			if(!$myData) {}
			else
			{
			for ($i = 1; $i<=12; $i++)
				{
					foreach ($myData as $newItems)
					{
						if($newItems->date == $siku)
						{
							//echo "<br>Data for ".$siku." there";
							$myFinal[$i] = json_encode($newItems);
						}
					}
					$siku = date("F-Y", strtotime("-3 month", strtotime($siku)));
				}
			}
			//var_dump($myFinal);
			$data = NULL;
			$sikuTena = date('F-Y',strtotime($objectDate));
			$newId = mysqli_query($connect, "SELECT MAX(id) FROM $table");
			$newId = mysqli_fetch_assoc($newId);
			$newId = (int)$newId["MAX(id)"];
			$newId = $newId+13;
		
			for ($i = 1; $i<=12; $i++)
			{
				if(@!$myFinal[$i])
				{
					//echo "Adding empty at position $i with date $sikuTena<br>";
					$data["id"] = $newId;
					$data["gaugeType"] = $gaugeType;
					$data["actual"] = "";
					$data["red"] = $red;
					$data["green"] = $green;
					$data["darkgreen"] = $darkGreen;
					$data["blue"] = $blue;
					$data["date"] = $sikuTena;
					
					$data = json_encode($data);
					$myFinal[$i] = $data;
					$newId--;
					$data = NULL;
				}
				$sikuTena = date("F-Y", strtotime("-3 month", strtotime($sikuTena)));	
			}
			//var_dump($myFinal);
			echo "[";
			for ($i = 1; $i<=12; $i++)
			{
				echo $myFinal[$i];
				if($i<12) echo ", ";
			}
			echo "]";
			break;	
		}
		case 'Bi-Annually':
		{
			$table = "measurehalfyear";
			//$measure_query="SELECT * FROM $table WHERE measureId = '$objectId' AND date <= '$objectDate%' ORDER BY date";
			$measure_query="SELECT * FROM $table WHERE measureId = '$objectId' ORDER BY date DESC";
			$measure_result=mysqli_query($connect, $measure_query);
			$row_count = mysqli_num_rows($measure_result);
			
			if($row_count == 0)
			{
				$defaults_results = mysqli_query($connect, "SELECT red, green, darkGreen, blue FROM measure WHERE id = '$objectId'");
				$defaults_row = mysqli_fetch_array($defaults_results);
				$red = $defaults_row["red"];
				$green = $defaults_row["green"];
				if(preg_match('/[a-zA-Z]/', $green) == 1) $green = calculatedKpi($objectId);
				$darkGreen = $defaults_row["darkGreen"];
				$blue = $defaults_row["blue"];
				
				$data["gaugeType"] = $gaugeType;
				$data = json_encode($data);
				//echo "[".$data."]";
				$data = NULL;
			}
			else
			{
				$defaults_results = mysqli_query($connect, "SELECT red, green, darkGreen, blue FROM measure WHERE id = '$objectId'");
				$defaults_row = mysqli_fetch_array($defaults_results);
				$red = $defaults_row["red"];
				$green = $defaults_row["green"];
				if(preg_match('/[a-zA-Z]/', $green) == 1) $green = calculatedKpi($objectId);
				$darkGreen = $defaults_row["darkGreen"];
				$blue = $defaults_row["blue"];
				
				$myData = "[";
				$count = 0;
				//echo "[";
				//$data = array();
				while($row = mysqli_fetch_assoc($measure_result))
				{
					$data["id"] = (int)$row["id"];
					$data["gaugeType"] = $gaugeType;
					$data["actual"] = (float)$row["actual"];
					
					if($row["red"] == NULL) $data["red"] = $red; else $data["red"] = (float)$row["red"];
					if($row["green"] == NULL) $data["green"] = $green; else $data["green"] = (float)$row["green"];
					if($row["darkgreen"] == NULL) $data["darkgreen"] = $darkGreen; else $data["darkgreen"] = (float)$row["darkgreen"];
					if($row["blue"] == NULL) $data["blue"] = $blue; else $data["blue"] = (float)$row["blue"];
					
					//$data["red"] = (int)$row["red"];
					//$data["green"] = (int)$row["green"];
					//$data["darkgreen"] = (int)$row["darkgreen"];
					//$data["blue"] = (int)$row["blue"];
					$data["date"] = date('F-Y',strtotime($row['date']));
					$data = json_encode($data);
					//echo $data;
					if($count == 0)
					$myData = $myData.$data;
					else
					$myData = $myData.",".$data;
					
					$data = NULL;
					$count++;
					//if($count < $row_count) echo ",";
				}
				$myData = $myData."]";
				//echo "]";
			}
			$halfYearMonth = date("m",strtotime($objectDate));
			switch($halfYearMonth)
			{
				case '02':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m", strtotime("+5 month", $objectDate));
					break;
				}
				case '03':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m-d", strtotime("+4 month", $objectDate));
					break;
				}
				case '04':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m-d", strtotime("+3 month", $objectDate));
					break;
				}
				case '05':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m-d", strtotime("+2 month", $objectDate));
					break;
				}
				case '06':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m-d", strtotime("+1 month", $objectDate));
					break;
				}
				case '08':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m-d", strtotime("+5 month", $objectDate));
					break;
				}
				case '09':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m-d", strtotime("+4 month", $objectDate));
					break;
				}
				case '10':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m-d", strtotime("+3 month", $objectDate));
					break;
				}
				case '11':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m-d", strtotime("+2 month", $objectDate));
					break;
				}
				case '12':
				{
					$objectDate = strtotime($objectDate);
					$objectDate = date("Y-m-d", strtotime("+1 month +1 year", $objectDate));
					//$objectDate = date("Y-m-d", strtotime("+1 years", $objectDate));
					break;
				}
			}
			
			//echo $myData;
			$myData = @json_decode($myData);
			$siku = date('F-Y',strtotime($objectDate));
			if(!$myData) {}
			else
			{
			for ($i = 1; $i<=12; $i++)
				{
					foreach ($myData as $newItems)
					{
						if($newItems->date == $siku)
						{
							//echo "<br>Data for ".$siku." there";
							$myFinal[$i] = json_encode($newItems);
						}
					}
					$siku = date("F-Y", strtotime("-6 month", strtotime($siku)));
				}
			}
			//var_dump($myFinal);
			$data = NULL;
			$sikuTena = date('F-Y',strtotime($objectDate));
			$newId = mysqli_query($connect, "SELECT MAX(id) FROM $table");
			$newId = mysqli_fetch_assoc($newId);
			$newId = (int)$newId["MAX(id)"];
			$newId = $newId+13;
		
			for ($i = 1; $i<=12; $i++)
			{
				if(@!$myFinal[$i])
				{
					//echo "Adding empty at position $i with date $sikuTena<br>";
					$data["id"] = $newId;
					$data["gaugeType"] = $gaugeType;
					$data["actual"] = "";
					$data["red"] = $red;
					$data["green"] = $green;
					$data["darkgreen"] = $darkGreen;
					$data["blue"] = $blue;
					$data["date"] = $sikuTena;
					
					$data = json_encode($data);
					$myFinal[$i] = $data;
					$newId--;
					$data = NULL;
				}
				$sikuTena = date("F-Y", strtotime("-6 month", strtotime($sikuTena)));	
			}
			//var_dump($myFinal);
			echo "[";
			for ($i = 1; $i<=12; $i++)
			{
				echo $myFinal[$i];
				if($i<12) echo ", ";
			}
			echo "]";
			break;	
		}
		case 'Yearly':
		{
			$table = "measureyears";
			//$measure_query="SELECT * FROM $table WHERE measureId = '$objectId' AND date <= '$objectDate%' ORDER BY date";
			$measure_query="SELECT * FROM $table WHERE measureId = '$objectId' ORDER BY date DESC";
			$measure_result=mysqli_query($connect, $measure_query);
			$row_count = mysqli_num_rows($measure_result);
			
			if($row_count == 0)
			{
				$defaults_results = mysqli_query($connect, "SELECT red, green, darkGreen, blue FROM measure WHERE id = '$objectId'");
				$defaults_row = mysqli_fetch_array($defaults_results);
				$red = $defaults_row["red"];
				$green = $defaults_row["green"];
				if(preg_match('/[a-zA-Z]/', $green) == 1) $green = calculatedKpi($objectId);
				$darkGreen = $defaults_row["darkGreen"];
				$blue = $defaults_row["blue"];
				
				$data["gaugeType"] = $gaugeType;
				$data = json_encode($data);
				//echo "[".$data."]";
				$data = NULL;
			}
			else
			{
				$defaults_results = mysqli_query($connect, "SELECT red, green, darkGreen, blue FROM measure WHERE id = '$objectId'");
				$defaults_row = mysqli_fetch_array($defaults_results);
				$red = $defaults_row["red"];
				$green = $defaults_row["green"];
				if(preg_match('/[a-zA-Z]/', $green) == 1) $green = calculatedKpi($objectId);
				$darkGreen = $defaults_row["darkGreen"];
				$blue = $defaults_row["blue"];
				
				$myData = "[";
				$count = 0;
				//echo "[";
				//$data = array();
				while($row = mysqli_fetch_assoc($measure_result))
				{
					$data["id"] = (int)$row["id"];
					$data["gaugeType"] = $gaugeType;
					$data["actual"] = (float)$row["actual"];
					
					if($row["red"] == NULL) $data["red"] = $red; else $data["red"] = (float)$row["red"];
					if($row["green"] == NULL) $data["green"] = $green; else $data["green"] = (float)$row["green"];
					if($row["darkgreen"] == NULL) $data["darkgreen"] = $darkGreen; else $data["darkgreen"] = (float)$row["darkgreen"];
					if($row["blue"] == NULL) $data["blue"] = $blue; else $data["blue"] = (float)$row["blue"];
					
					//$data["red"] = (int)$row["red"];
					//$data["green"] = (int)$row["green"];
					//$data["darkgreen"] = (int)$row["darkgreen"];
					//$data["blue"] = (int)$row["blue"];
					$data["date"] = date('Y',strtotime($row['date']));
					$data = json_encode($data);
					//echo $data;
					if($count == 0)
					$myData = $myData.$data;
					else
					$myData = $myData.",".$data;
					
					$data = NULL;
					$count++;
					//if($count < $row_count) echo ",";
				}
				$myData = $myData."]";
				//echo "]";
			}
		
			//echo $myData;
			$myData = @json_decode($myData);
			//echo $myData;
			$siku = date('F-Y',strtotime($objectDate));
			if(!$myData) {
				//echo "test";
				}
			else
			{
			for ($i = 1; $i<=12; $i++)
				{
					foreach ($myData as $newItems)
					{
						//echo $newItems->date.', '.date('Y',strtotime($siku)).'<br>'; 
						if($newItems->date == date('Y',strtotime($siku)))
						{
							//echo "<br>Data for ".$siku." there";
							$myFinal[$i] = json_encode($newItems);
						}
					}
					$siku = date("F-Y", strtotime("-1 year", strtotime($siku)));
				}
			}
			//var_dump($myFinal);
			$data = NULL;
			$sikuTena = date('F-Y',strtotime($objectDate));
			$newId = mysqli_query($connect, "SELECT MAX(id) FROM $table");
			$newId = mysqli_fetch_assoc($newId);
			$newId = (int)$newId["MAX(id)"];
			$newId = $newId+13;
		
			for ($i = 1; $i<=12; $i++)
			{
				if(@!$myFinal[$i])
				{
					//echo "Adding empty at position $i with date $sikuTena<br>";
					$data["id"] = $newId;
					$data["gaugeType"] = $gaugeType;
					$data["actual"] = "";
					$data["red"] = $red;
					$data["green"] = $green;
					$data["darkgreen"] = $darkGreen;
					$data["blue"] = $blue;
					$data["date"] = date("Y", strtotime($sikuTena));
					
					$data = json_encode($data);
					$myFinal[$i] = $data;
					$newId--;
					$data = NULL;
				}
				$sikuTena = date("F-Y", strtotime("-1 year", strtotime($sikuTena)));	
			}
			//var_dump($myFinal);
			echo "[";
			for ($i = 1; $i<=12; $i++)
			{
				echo $myFinal[$i];
				if($i<12) echo ", ";
			}
			echo "]";
			break;	
		}	
	}	
	flush();
//}
exit;
?>