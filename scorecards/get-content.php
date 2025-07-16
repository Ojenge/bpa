<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");
include_once("../functions/calendar-labels.php");
include_once("../functions/perspOrg-scores.php");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // This will make mysqli throw exceptions
//if(isset($_POST['objectId']))
//{
	

	@$objectId = $_POST['objectId'];
	@$objectType = $_POST["objectType"];
	@$objectPeriod = $_POST['objectPeriod'];
	@$objectDate = $_POST['objectDate'];
	@$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
	//$table = "measure".$objectPeriod;
	//$objectDate = strtotime($objectDate);
	//$objectDate = date("Y-m-d", strtotime("-1 month", $objectDate));
	$table = "measuremonths";
	
	//$objectId = "ind11";
	//$objectType = "individual";
	//$objectDate = '2024-09';
	
	switch($objectType)
	{
		case "organization":
		{	
			$organization_query="SELECT * FROM organization where organization.id = '$objectId'";
			$organization_result=mysqli_query($connect, $organization_query);
			$org_row = mysqli_fetch_assoc($organization_result);
			
			$note_query="SELECT interpretation, wayForward FROM note where objectId = '$objectId'";
			$note_result=mysqli_query($connect, $note_query);
			if(mysqli_num_rows($note_result) > 0)
			{
				$note_row = mysqli_fetch_assoc($note_result);
				$org_row["interpretation"] = $note_row["interpretation"];
				$org_row["wayForward"] = $note_row["wayForward"];
			}
			else
			{
				$kpi_row["interpretation"] = '';
				$kpi_row["wayForward"] = '';
			}
			
			$initiative_query="SELECT initiative.id AS id, initiative.name AS name, initiative.dueDate, initiative.completionDate FROM initiative, initiativeimpact where initiative.id = initiativeimpact.initiativeid and initiativeimpact.linkedobjectid = '$objectId' AND initiative.archive <> 'Yes'";
			$initiative_result=mysqli_query($connect, $initiative_query);
			$count=1;
			while($row = mysqli_fetch_assoc($initiative_result))
			{
				$org_row["Initiative Count"] = $count;
				$org_row["Initiative".$count] = $row["name"];
				$org_row["InitiativeId".$count] = $row["id"];
				$obj_row["dueDate".$count] = $row["dueDate"];
				if($row["dueDate"] <= date("Y-m-d") && $row["completionDate"] == NULL)
					$org_row["Color".$count] = "red";
				else if ($row["dueDate"] < $row["completionDate"] && $row["completionDate"] != NULL)
					$org_row["Color".$count] = "yellow";
				else if ($row["completionDate"] <= $row["dueDate"] && $row["completionDate"] != NULL)
					$org_row["Color".$count] = "green";
				else $org_row["Color".$count] = "#FFFFFF";
					$link_id = $row["id"];
				if($row["dueDate"] == NULL || $row["dueDate"] == '0000-00-00' || $row["dueDate"] == '1970-01-01')
				{
					$org_row["Color".$count] = "#D0D0D0";//light grey color
					$org_row["dueDate".$count] = 'No due date';
				}
				$count++;
			}
			$row = NULL;
			$count = 1;
			$perspective_query="SELECT id, name, weight FROM perspective WHERE parentId = '$objectId'";
			$perspective_result=mysqli_query($connect, $perspective_query);
			while($row = mysqli_fetch_assoc($perspective_result))
			{
				$org_row["Perspective Count"] = $count;
				$org_row["Perspective".$count] = $row["name"];
				$org_row["Perspective Weight".$count] = $row["weight"];
				//echo "<strong>".$row["id"]."</strong>";
				//$score = perspective_score($row["id"],$objectDate, $table);
				//($perspective_id, $objectPeriod, $objectDate)
				
				$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
				$objectDate = strtotime($objectDate);
				$objectDate = date("Y-m-d", strtotime("+1 month", $objectDate));
				
				$score = perspective_score($row["id"], $objectDate, 'Months');
				//echo "<br>Score: ".$score." for ".$row["id"];
				if($score == 'grey' || $score === null || $score === '')
				$org_row["Perspective Score".$count] = 'No Score';
				else
				$org_row["Perspective Score".$count] = round($score, 2);
				$count++;
			}
			$row = NULL;
			$count = 1;
			$cascaded_query="SELECT name, id FROM organization where cascadedfrom = '$objectId'";
			$cascaded_result=mysqli_query($connect, $cascaded_query);
			while($row = mysqli_fetch_assoc($cascaded_result))
			{
				$org_row["Cascaded Count"] = $count;
				$org_row["Cascaded To".$count] = $row["name"];
				//$score = cascaded_departments_score($row["id"], $objectDate, $table);
				$score = cascaded_departments_score($row["id"], $objectDate, $table);
				
				if($score == 'grey' || $score == '')
				$org_row["Cascaded To Score".$count] = 'No Score';
				else
				$org_row["Cascaded To Score".$count] = $score;
				$count++;
			}
			
			$cascaded_query="SELECT id, name FROM organization WHERE id = (SELECT cascadedfrom FROM organization where id = '$objectId')";
			$cascaded_result=mysqli_query($connect, $cascaded_query);
			while($row = mysqli_fetch_assoc($cascaded_result))
			{
				$org_row["Cascaded From Count"] = $count;
				$org_row["Cascaded From".$count] = $row["name"];
				//$score = cascaded_departments_score($row["id"], $objectDate, $table);
				$score = cascaded_departments_score($row["id"], $objectDate, $table);
				
				if($score == 'grey' || $score == '')
				$org_row["Cascaded From Score".$count] = 'No Score';
				else
				$org_row["Cascaded From Score".$count] = $score;
				$count++;
			}
			
			//$org_data = $org_row.$data;
			$org_data = json_encode($org_row);
			echo $org_data;
			break;
		}
		case "perspective":
		{
			$perspective_query="SELECT * FROM perspective where id = '$objectId'";
			$perspective_result=mysqli_query($connect, $perspective_query);
			$persp_row = mysqli_fetch_assoc($perspective_result);
			
			$note_query="SELECT interpretation, wayForward FROM note where objectId = '$objectId'";
			$note_result=mysqli_query($connect, $note_query);
			if(mysqli_num_rows($note_result) > 0)
			{
				$note_row = mysqli_fetch_assoc($note_result);
				$persp_row["interpretation"] = $note_row["interpretation"];
				$persp_row["wayForward"] = $note_row["wayForward"];
			}
			else
			{
				$persp_row["interpretation"] = '';
				$persp_row["wayForward"] = '';
			}
			
			$objective_query="SELECT id, name, weight FROM objective where linkedObject = '$objectId'";
			$objective_result=mysqli_query($connect, $objective_query);
			$count=1;
			$row = NULL;
			while($row = mysqli_fetch_assoc($objective_result))
			{
				$persp_row["Objective Count"] = $count;
				$persp_row["Objective".$count] = $row["name"];
				$persp_row["Objective Weight".$count] = $row["weight"];
				
				$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
				$objectDate = strtotime($objectDate);
				$objectDate = date("Y-m-d", strtotime("+1 month", $objectDate));
				
				$score = objective_score($row["id"],$objectDate, $table);
				if($score == 'No Score' || $score == 0 || $score === null || $score === '')
				$persp_row["Objective Score".$count] = 'No Score';
				else
				$persp_row["Objective Score".$count] = round($score, 2);
				$count++;	
			}
			
			$initiative_query="SELECT initiative.name, initiative.id FROM initiative, initiativeimpact WHERE initiative.id = initiativeimpact.initiativeid AND initiativeimpact.linkedobjectid = '$objectId' AND initiative.archive <> 'Yes'";
			$initiative_result=mysqli_query($connect, $initiative_query);
			$count=1;
			$row = NULL;
			while($row = mysqli_fetch_assoc($initiative_result))
			{
				$persp_row["Initiative Count"] = $count;
				$persp_row["Initiative".$count] = $row["name"];
				$persp_row["InitiativeId".$count] = $row["id"];
				$persp_row["dueDate".$count] = $row["dueDate"];
				if($row["dueDate"] <= date("Y-m-d") && $row["completionDate"] == NULL)
					$persp_row["Color".$count] = "red";
				else if ($row["dueDate"] >= $row["completionDate"] && $row["completionDate"] != NULL)
					$persp_row["Color".$count] = "green";
				else if ($row["completionDate"] > $row["dueDate"] && $row["completionDate"] != NULL)
					$persp_row["Color".$count] = "yellow";
				else $persp_row["Color".$count] = "#FFFFFF";
					$link_id = $row["id"];
				if($row["dueDate"] == NULL || $row["dueDate"] == '0000-00-00' || $row["dueDate"] == '1970-01-01')
				{
					$persp_row["Color".$count] = "#D0D0D0";//light grey color
					$persp_row["dueDate".$count] = 'No due date';
				}
				$count++;
			}
			$persp_data = json_encode($persp_row);
			echo $persp_data;
			break;
		}
		case "objective":
		{
			$objective_query="SELECT * FROM $objectType WHERE id = '$objectId'";
			$objective_result=mysqli_query($connect, $objective_query);
			$obj_row = mysqli_fetch_assoc($objective_result);
			
			$row = NULL;
			/*$team_query="SELECT uc_users.display_name AS teamName
			FROM uc_users, objectiveteam 
			WHERE objectiveteam.objectiveId = '$objectId' 
			AND uc_users.user_id = objectiveteam.userId";
			$team_result=mysqli_query($connect, $team_query);
			$count=1;
			while($row = mysqli_fetch_assoc($team_result))
			{
				$obj_row["Team Count"] = $count;
				$obj_row["Team".$count] = $row["teamName"];
				$count++;
			}*/
			
			$note_query="SELECT interpretation, wayForward FROM note where objectId = '$objectId'";
			$note_result=mysqli_query($connect, $note_query);
			if(mysqli_num_rows($note_result) > 0)
			{
				$note_row = mysqli_fetch_assoc($note_result);
				$obj_row["interpretation"] = $note_row["interpretation"];
				$obj_row["wayForward"] = $note_row["wayForward"];
			}
			else
			{
				$kpi_row["interpretation"] = '';
				$kpi_row["wayForward"] = '';
			}
						
			$row = NULL;
			$initiative_query="SELECT initiative.id AS id, initiative.name AS name, initiative.dueDate AS dueDate, initiative.completionDate AS completionDate FROM initiative, initiativeimpact where initiative.id = initiativeimpact.initiativeid and initiativeimpact.linkedobjectid = '$objectId' AND initiative.archive <> 'Yes'";
			$initiative_result=mysqli_query($connect, $initiative_query);
			$count=1;
			while($row = mysqli_fetch_assoc($initiative_result))
			{
				$obj_row["Initiative Count"] = $count;
				$obj_row["Initiative".$count] = $row["name"];
				$obj_row["InitiativeId".$count] = $row["id"];
				$obj_row["dueDate".$count] = $row["dueDate"];
				if($row["dueDate"] <= date("Y-m-d") && $row["completionDate"] == NULL)
					$obj_row["Color".$count] = "red";
				else if ($row["dueDate"] >= $row["completionDate"] && $row["completionDate"] != NULL)
					$obj_row["Color".$count] = "green";
				else if ($row["completionDate"] > $row["dueDate"] && $row["completionDate"] != NULL)
					$obj_row["Color".$count] = "yellow";
				else $obj_row["Color".$count] = "#FFFFFF";
					$link_id = $row["id"];
				if($row["dueDate"] == NULL || $row["dueDate"] == '0000-00-00' || $row["dueDate"] == '1970-01-01')
				{
					$obj_row["Color".$count] = "#D0D0D0";//light grey color
					$obj_row["dueDate".$count] = 'No due date';
				}
				$count++;
			}
			
			$row = NULL;
			$count = 1;
			$cascaded_query="SELECT id, name, outcome, linkedObject FROM objective where cascadedfrom = '$objectId'";
			$cascaded_result=mysqli_query($connect, $cascaded_query);
			while($row = mysqli_fetch_assoc($cascaded_result))
			{
				switch(substr($row["linkedObject"], 0, 3))
				{
					case "per":
					{
						$linkedObject = $row["linkedObject"];
						$parent = mysqli_query($connect,  "Select organization.name, perspective.parentId FROM perspective, organization WHERE perspective.id = '$linkedObject' AND organization.id = perspective.parentId");
						$parent = mysqli_fetch_assoc($parent);
						$parent = $parent["name"];
						break;	
					}
					case "org":
					{
						$linkedObject = $row["linkedObject"];
						$parent = mysqli_query($connect,  "Select name FROM organization WHERE id = '$linkedObject'");
						$parent = mysqli_fetch_assoc($parent);
						$parent = $parent["name"];
						break;	
					}	
				}
				$obj_row["Cascaded Count"] = $count;
				if($row["outcome"] == "") $obj_row["Cascaded To".$count] = "<strong>".$parent.": </strong>".$row["name"];
				else $obj_row["Cascaded To".$count] = "<strong>".$parent.": </strong>".$row["name"]." => <i>".$row["outcome"]."</i>";
				$score = objective_score($row["id"], $objectDate, $table);
				
				if( $score == 'grey' || $score == '')
				$org_row["Cascaded To Score".$count] = 'No Score';
				else
				$obj_row["Cascaded To Score".$count] = $score;
				$count++;
			}
			
			$cascaded_query="SELECT id, name, outcome, linkedObject FROM objective WHERE id = (SELECT cascadedfrom FROM objective WHERE id = '$objectId')";
			$cascaded_result=mysqli_query($connect, $cascaded_query);
			while($row = mysqli_fetch_assoc($cascaded_result))
			{
				switch(substr($row["linkedObject"], 0, 3))
				{
					case "per":
					{
						$linkedObject = $row["linkedObject"];
						$parent = mysqli_query($connect,  "Select organization.name, perspective.parentId FROM perspective, organization WHERE perspective.id = '$linkedObject' AND organization.id = perspective.parentId");
						$parent = mysqli_fetch_assoc($parent);
						$parent = $parent["name"];
						break;	
					}
					case "org":
					{
						$linkedObject = $row["linkedObject"];
						$parent = mysqli_query($connect,  "Select name FROM organization WHERE id = '$linkedObject'");
						$parent = mysqli_fetch_assoc($parent);
						$parent = $parent["name"];
						break;	
					}	
				}
				$obj_row["Cascaded From Count"] = $count;
				if($row["outcome"] == "") $obj_row["Cascaded To".$count] = "<strong>".$parent.": </strong>".$row["name"];
				else $obj_row["Cascaded From".$count] = "<strong>".$parent.": </strong>".$row["name"]." => <i>".$row["outcome"]."</i>";
				$score = objective_score($row["id"], $objectDate, $table);
				
				if( $score == 'grey' || $score == '')
				$org_row["Cascaded From Score".$count] = 'No Score';
				else
				$obj_row["Cascaded From Score".$count] = $score;
				$count++;
			}
			
			//$measure_query="SELECT measure.name, $table.3score FROM measure, $table WHERE measure.linkedObject = '$objectId' AND $table.measureId = measure.id AND date <= '$objectDate%'";
			$measure_query="SELECT id, name, calendarType, weight FROM measure WHERE linkedObject = '$objectId'
			OR id = (SELECT measure_id FROM measurelinks WHERE linked_id = '$objectId')";//added the OR portion to factor in linked measures. LTK 15Aug2021 1223hrs
			$measure_result=mysqli_query($connect, $measure_query);
			$count = 1;
			while($row = mysqli_fetch_assoc($measure_result))
			{
				$obj_row["Measure Count"] = $count;
				$obj_row["Measure".$count] = $row["name"];
				$obj_row["measureWeight".$count] = $row["weight"];
				$measure_id = $row["id"];
				$measure_table = $row["calendarType"];
				switch($measure_table)
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
				$measure_score_query = "SELECT 3score, date AS lastDate FROM $table WHERE measureId = '$measure_id' ORDER BY date DESC LIMIT 1";
				//$measure_score_query = "SELECT 3score FROM $table WHERE measureId = '$measure_id' AND date <= '$objectDate%' ORDER BY date DESC LIMIT 1";
				//echo $table.'<br>';
				//$measure_score_query = "SELECT AVG(3score) FROM $table WHERE measureId = '$measure_id' AND date LIKE '$objectDate%'";
				$measure_score_result = mysqli_query($connect, $measure_score_query);
				$row = mysqli_fetch_assoc($measure_score_result);
				if($row && isset($row["3score"])) {
					$score = $row["3score"];
					if($score == NULL)
						$obj_row["measureScore".$count] = 'No Score';
					else
						$obj_row["measureScore".$count] = round($score, 2);
				} else {
					$obj_row["measureScore".$count] = 'No Score';
				}
				$count++;
			}
			//$obj_data = $obj_row.$obj_details;
			$obj_data = json_encode($obj_row);
			echo $obj_data;
			break;
		}//end case objective
		case "measure":
		{
			//$objective_query="SELECT * FROM $objectType WHERE id = '$objectId'"; Replaced this with the one below so as to get names of updater and owner after using IDs as opposed to original names. LTK 16.12.2017. Changed JOIN to LEFT JOIN so that results are returned when updater and owner are null. Added IFNULL to prevent the null return from showing to users. LTK 15.04.2021 1047hrs
			$objective_query = "SELECT 
			m.id, m.name, m.calendarType, m.description, m.linkedObject, m.dataType, m.aggregationType, 
			m.location, m.red, m.blue, m.green, m.darkGreen, m.parentMeasure, m.gaugeType, m.weight, 
			IFNULL(u.display_name, 'No Updater') AS 'updater', 
			IFNULL(u1.display_name, 'No Owner') AS 'owner'
			FROM `measure` m
			LEFT JOIN `uc_users` u ON u.user_id = m.updater
			LEFT JOIN `uc_users` u1 ON u1.user_id = m.owner
			WHERE m.id = '$objectId'";
			$objective_result = mysqli_query($connect, $objective_query) or file_put_contents("aUpdater.txt", "Issue here is ".mysqli_error($connect));
			$kpi_row = mysqli_fetch_assoc($objective_result);
			if($kpi_row['dataType'] == 'Currency')
			{
				$currency_query="SELECT value FROM settings WHERE item = 'Currency'";
				$currency_result=mysqli_query($connect, $currency_query);
				$currency_row = mysqli_fetch_assoc($currency_result);
				$kpi_row['currency'] = $currency_row['value'];
			}
			else $kpi_row['currency'] = '';
			
			$note_query="SELECT interpretation, wayForward FROM note WHERE objectId = '$objectId'";
			$note_result=mysqli_query($connect, $note_query);
			if(mysqli_num_rows($note_result) > 0)
			{
				$note_row = mysqli_fetch_assoc($note_result);
				$kpi_row["interpretation"] = $note_row["interpretation"];
				$kpi_row["wayForward"] = $note_row["wayForward"];
			}
			else
			{
				$kpi_row["interpretation"] = '';
				$kpi_row["wayForward"] = '';
			}
			
			$row = NULL;
			$initiative_query="SELECT initiative.name, initiative.id, initiative.dueDate, initiative.completionDate FROM initiative, initiativeimpact WHERE initiative.id = initiativeimpact.initiativeid and initiativeimpact.linkedobjectid = '$objectId' AND initiative.archive <> 'Yes'";
			$initiative_result=mysqli_query($connect, $initiative_query);
			$count=1;
			while($row = mysqli_fetch_assoc($initiative_result))
			{
				$kpi_row["Initiative Count"] = $count;
				$kpi_row["InitiativeId".$count] = $row["id"];
				$kpi_row["Initiative".$count] = $row["name"];
				$kpi_row["dueDate".$count] = $row["dueDate"];
				
				if($row["dueDate"] <= date("Y-m-d") && $row["completionDate"] == NULL)
					$kpi_row["Color".$count] = "red";
				else if ($row["dueDate"] >= $row["completionDate"] && $row["completionDate"] != NULL)
					$kpi_row["Color".$count] = "green";
				else if ($row["completionDate"] > $row["dueDate"] && $row["completionDate"] != NULL)
					$kpi_row["Color".$count] = "yellow";
				else $kpi_row["Color".$count] = "#FFFFFF";
					$link_id = $row["id"];
				if($row["dueDate"] == NULL || $row["dueDate"] == '0000-00-00' || $row["dueDate"] == '1970-01-01')
				{
					$kpi_row["Color".$count] = "#D0D0D0";//light grey color
					$kpi_row["dueDate".$count] = 'No due date';
				}
				$count++;
			}
			$row = NULL;
			$count = 1;	
			$cascaded_query="SELECT measure.name, $table.3score FROM measure, $table where measure.parentMeasure = '$objectId' AND $table.measureId LIKE measure.id AND date LIKE '$objectDate'";
			$cascaded_result=mysqli_query($connect, $cascaded_query);
			while($row = @mysqli_fetch_assoc($cascaded_result))
			{
				$kpi_row["Cascaded Count"] = $count;
				$kpi_row["Cascaded".$count] = $row["name"];
				$kpi_row["Cascaded Score".$count] = $row["3score"];
				$count++;
			}
			//$obj_data = $obj_row.$obj_details;
			$kpi_data = json_encode($kpi_row);
			echo $kpi_data;
			break;
		}
		case "individual":
		{
			//$individual_query="SELECT uc_users.display_name AS name, uc_users.title AS title, uc_users.photo AS photo, organization.mission, organization.vision, organization.valuez FROM uc_users, organization WHERE uc_users.user_id = '$objectId'";
			$individual_query="SELECT a.user_id, a.display_name AS name, 
			c.display_name AS supervisor, 
			a.title, a.photo, b.name AS department 
			FROM uc_users a
			LEFT JOIN organization b ON b.id = a.department
			LEFT JOIN uc_users c ON a.reportsTo = c.user_id
			WHERE a.user_id = '$objectId'"; //Updating to show individual details as opposed to organization details. LTK 09May2024 20:09
			$individual_result=mysqli_query($connect, $individual_query);
			$ind_row = mysqli_fetch_assoc($individual_result);
			
			$note_query="SELECT interpretation, wayForward FROM note WHERE objectId = '$objectId'";
			$note_result=mysqli_query($connect, $note_query);
			if(mysqli_num_rows($note_result) > 0)
			{
				$note_row = mysqli_fetch_assoc($note_result);
				$ind_row["interpretation"] = $note_row["interpretation"];
				$ind_row["wayForward"] = $note_row["wayForward"];
			}
			else
			{
				$ind_row["interpretation"] = '';
				$ind_row["wayForward"] = '';
			}
			
			$get_gauge = "SELECT id, name, gaugeType, calendarType FROM measure WHERE linkedObject = '$objectId' OR updater = '$objectId' OR owner = '$objectId'";
			$get_gauge_result=mysqli_query($connect, $get_gauge);
			$count = 1;
			while($row = mysqli_fetch_assoc($get_gauge_result))
			{
				$calendarType = $row['calendarType'];
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
				$kpiId = $row['id'];
				//echo $kpiId.', '.$table;
				$indMeasure_query = "SELECT date, actual, green, 3score FROM $table WHERE measureId = '$kpiId' AND date <= '$objectDate' ORDER BY date DESC LIMIT 1";
				//$indMeasure_query = "SELECT AVG(3score) FROM $table WHERE measureId = '$kpiId' AND date LIKE '$objectDate%'";
				$indMeasure_result=mysqli_query($connect, $indMeasure_query);
				$indMeasure_count = mysqli_num_rows($indMeasure_result);
				$indKpiRow=mysqli_fetch_assoc($indMeasure_result);
				if($indMeasure_count == 0) $ind_row["Measure Count"] = 0;
				//if($row["name"] == NULL) $row["name"] = '';
				//if($row["3score"] == NULL) $row["3score"] = '';
				$ind_row["Measure Count"] = $count;
				$ind_row["Measure".$count] = $row["name"];
				$ind_row["measureId".$count] = preg_replace('/^\D+/', '', $row["id"]);
				
				$getUpdater = mysqli_query($connect, "SELECT 
								 u.display_name as 'updater', 
								 u1.display_name as 'owner'
							FROM `measure` m
							JOIN `uc_users` u on u.user_id = m.updater
							JOIN `uc_users` u1 on u1.user_id = m.owner
							WHERE m.id = '$kpiId'");
				$updaterRow = mysqli_fetch_assoc($getUpdater);

				if($updaterRow) {
					$ind_row["Measure Owner".$count] = $updaterRow["owner"] ?? 'No Owner';
					$ind_row["Measure Updater".$count] = $updaterRow["updater"] ?? 'No Updater';
				} else {
					$ind_row["Measure Owner".$count] = 'No Owner';
					$ind_row["Measure Updater".$count] = 'No Updater';
				}

				if($indKpiRow && isset($indKpiRow["3score"])) {
					if($indKpiRow["3score"] == NULL || $indKpiRow["3score"] == '')
						$ind_row["Measure Score".$count] = "No Score";
					else
						$ind_row["Measure Score".$count] = round($indKpiRow["3score"],2);
					$ind_row["Measure Actual".$count] = $indKpiRow["actual"] ?? '';
					$ind_row["Measure Target".$count] = $indKpiRow["green"] ?? '';
				} else {
					$ind_row["Measure Score".$count] = "No Score";
					$ind_row["Measure Actual".$count] = '';
					$ind_row["Measure Target".$count] = '';
				}
				$ind_row["Measure Frequency".$count] =$calendarType;
				if($indKpiRow && isset($indKpiRow["date"]) && $indKpiRow["date"]) {
					switch($table)
					{
						case "measuremonths":
						{
							$ind_row["Measure Date".$count] = date("F, Y",strtotime($indKpiRow["date"]));
							break;
						}
						case "measurequarters":
						{
							$month = date("m",strtotime($indKpiRow["date"]));
							if($month > 0 && $month < 4)
							$ind_row["Measure Date".$count] = "Q1 ".date("Y",strtotime($indKpiRow["date"]));
							else if ($month > 3 && $month < 7)
							$ind_row["Measure Date".$count] = "Q2 ".date("Y",strtotime($indKpiRow["date"]));
							else if ($month > 6 && $month < 10)
							$ind_row["Measure Date".$count] = "Q3 ".date("Y",strtotime($indKpiRow["date"]));
							else
							$ind_row["Measure Date".$count] = "Q4 ".date("Y",strtotime($indKpiRow["date"]));
							break;
						}
						case "measurehalfyear":
						{
							$month = date("m",strtotime($indKpiRow["date"]));
							if($month > 1 && $month < 7)
							$ind_row["Measure Date".$count] = "HY1 ".date("Y",strtotime($indKpiRow["date"]));
							else
							$ind_row["Measure Date".$count] = "HY2 ".date("Y",strtotime($indKpiRow["date"]));
							break;
						}
						case "measureyears":
						{
							$ind_row["Measure Date".$count] = date("Y",strtotime($indKpiRow["date"]));
							break;
						}
						default:
						{
							$ind_row["Measure Date".$count] = $indKpiRow["date"];
							break;
						}
					}
				} else {
					$ind_row["Measure Date".$count] = 'No Date';
				}
				$count++;
			}
			$row = NULL;
			//$initiative_query="SELECT initiative.name, initiative.id, initiativedeliverable.deliverable FROM initiative, initiativeimpact, initiativedeliverable WHERE initiative.id = initiativeimpact.initiativeid AND initiativeimpact.linkedobjectid = '$objectId' AND initiativedeliverable.id = initiative.id";
			//$initOwnerId = substr($objectId, 3);
			//echo $objectId;
			$initiative_query="SELECT id, name, deliverable, startDate, dueDate, completionDate 
			FROM initiative 
			WHERE projectManager = '$objectId'
			AND archive <> 'Yes'";
			$initiative_result=mysqli_query($connect, $initiative_query);
			$initiative_count=mysqli_num_rows($initiative_result);
			if($initiative_count == 0) $ind_row["Initiative Count"] = 0;
			$count=1;
			while($row = mysqli_fetch_assoc($initiative_result))
			{
				$ind_row["Initiative Count"] = $count;
				$ind_row["Initiative".$count] = $row["name"];
				$ind_row["InitiativeId".$count] = $row["id"];
				$ind_row["Deliverable".$count] = $row["deliverable"];
				$ind_row["dueDate".$count] = date("d M Y", strtotime($row["dueDate"]));
				$ind_row["startDate".$count] = $row["startDate"];
				$ind_row["completionDate".$count] = $row["completionDate"];
				
				if($row["dueDate"] <= date("Y-m-d") && $row["completionDate"] == NULL)
				{
					$ind_row["Color".$count] = "#eca1a6";//red
					$ind_row["trafficLight".$count] = "red3d";
				}
				else if ($row["dueDate"] >= $row["completionDate"] && $row["completionDate"] != NULL)
				{
					$ind_row["Color".$count] = "#b5e7a0";//green
					$ind_row["trafficLight".$count] = "green3d";
				}
				else if ($row["completionDate"] > $row["dueDate"])
				{
					$ind_row["Color".$count] = "#ffef96";//yellow
					$ind_row["trafficLight".$count] = "yellow3d";
				}
				else 
				{
					$ind_row["Color".$count] = "#FFFFFF";
					$ind_row["trafficLight".$count] = "grey3d";
				}
				$link_id = $row["id"];
				
				$ind_row["Color".$count];
				if($row["dueDate"] == NULL || $row["dueDate"] == '0000-00-00' || $row["dueDate"] == '1970-01-01')
				{
					$ind_row["Color".$count] = "#D0D0D0";//light grey color
					$ind_row["dueDate".$count] = 'No due date';
					$ind_row["trafficLight".$count] = "rounded-circle bg-secondary trafficLightBootstrap";
				}
				//echo "<strong>".$link_id."</strong><br>";
				$linkedObjective_query = "SELECT objective.name 
				FROM objective, initiativeimpact 
				WHERE initiativeimpact.initiativeid = '$link_id' 
				AND objective.id = initiativeimpact.linkedobjectid";
				$linkedObjective_result = mysqli_query($connect, $linkedObjective_query);
				$ind_row["Objective".$count] = NULL;
				while($linkedObjective_row = mysqli_fetch_assoc($linkedObjective_result))
				{
					if ($ind_row["Objective".$count] == NULL)
					$ind_row["Objective".$count] = $linkedObjective_row["name"];
					else
					$ind_row["Objective".$count] = $ind_row["Objective".$count].", ".$linkedObjective_row["name"];
				}
				if ($ind_row["Objective".$count] == NULL) $ind_row["Objective".$count] = " ";
				$count++;
			}
			
			$row = NULL;
			$count = 1;
			$valuesCount = 1;
			$indId = $objectId;
			$cascaded_query="SELECT organization.name AS name, organization.id AS id 
			FROM organization, individual 
			WHERE individual.id = '$objectId' 
			AND organization.id = individual.cascadedFrom";
			$cascaded_result=mysqli_query($connect, $cascaded_query);
			while($row = mysqli_fetch_assoc($cascaded_result))
			{
				//$ind_row["Cascaded Count"] = $count;
				$ind_row["Cascaded From"] = $row["name"];
				//$ind_row["Cascaded From Score"] = organization_score($row["id"], $objectDate, $table);
				
				$objectId = orgChildIds($row["id"]);
				switch($objectPeriod)
				{
					case "days":
					{
						$orgScore = daysAsIs($objectId, $objectDate, $valuesCount);
						break;
					}
					/*******************************************************************************************************************
					$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
					********************************************************************************************************************/
					case "weeks":
					{
						//daysInWeeks($objectId, $objectDate, $valuesCount,  $red, $green, $darkgreen, $blue, $gaugeType);
						$orgScore = weeksAsIs($objectId, $objectDate, $valuesCount);
						break;
					}
					/*******************************************************************************************************************
					$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
					********************************************************************************************************************/
					case "months":
					{
						$orgScore = monthsAsIs($objectId, $objectDate, $valuesCount);
						break;
					}
					/*******************************************************************************************************************
					$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
					********************************************************************************************************************/
					case "quarters":
					{
						$orgScore = quartersAsIs($objectId, $objectDate, $valuesCount);
						break;
					}	
					/*******************************************************************************************************************
					$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
					********************************************************************************************************************/
					case "halfYears":
					{
						//$objectDate = strtotime ( '-1 years' , strtotime ( $objectDate.'-01-01' ) ) ;
						//$objectDate = date ( 'Y-m-d' , $objectDate );
						$orgScore = halfYearsAsIs($objectId, $objectDate, $valuesCount);
						break;
					}
					/*******************************************************************************************************************
					$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
					********************************************************************************************************************/
					case "years":
					{
						//$objectDate = strtotime ( '+1 years' , strtotime ( $objectDate.'-01-01' ) ) ;
						//$objectDate = date ( 'Y-m-d' , $objectDate );
						$orgScore = yearsAsIs($objectId, $objectDate, $valuesCount);
						break;
					}
				}
				if($orgScore === null || $orgScore === '' || $orgScore == 'No Score')
					$ind_row["Cascaded From Score"] = 'No Score';
				else
					$ind_row["Cascaded From Score"] = round($orgScore,2);
				$count++;
			}
			//$obj_data = $obj_row.$obj_details;
			
			$count = 1;
			$pdp_query="SELECT id, skillGap, intervention, startDate, dueDate, completionDate, resource, comments
			FROM pdp
			WHERE indId = '$indId'";
			$pdp_result=mysqli_query($connect, $pdp_query);
			$pdp_count=@mysqli_num_rows($pdp_result);
			if($pdp_count == 0)$ind_row["pdpCount"] = 0;
				
			while($row = @mysqli_fetch_assoc($pdp_result))
			{
				$ind_row["pdpCount"] = $count;
				$ind_row["pdpId".$count] = $row["id"];
				$ind_row["skillGap".$count] = $row["skillGap"];
				$ind_row["intervention".$count] = $row["intervention"];
				$ind_row["pdpStartDate".$count] = $row["startDate"];
				$ind_row["pdpDueDate".$count] = $row["dueDate"];
				$ind_row["resource".$count] = $row["resource"];
				$ind_row["comments".$count] = $row["comments"];
				if($row["dueDate"] <= date("Y-m-d") && $row["completionDate"] == NULL)
					$ind_row["pdpColor".$count] = "rounded-circle bg-danger trafficLightBootstrap";
				else if ($row["dueDate"] < $row["completionDate"] && $row["completionDate"] != NULL)
					$ind_row["pdpColor".$count] = "rounded-circle bg-warning trafficLightBootstrap";
				else if ($row["completionDate"] <= $row["dueDate"] && $row["completionDate"] != NULL)
					$ind_row["pdpColor".$count] = "rounded-circle bg-success trafficLightBootstrap";
				else $ind_row["pdpColor".$count] = "#FFFFFF";
					$link_id = $row["id"];
				if($row["dueDate"] == NULL || $row["dueDate"] == '0000-00-00' || $row["dueDate"] == '1970-01-01')
				{
					$ind_row["pdpColor".$count] = "rounded-circle bg-secondary trafficLightBootstrap";//light grey color
					$ind_row["pdpDueDate".$count] = 'No due date';
				}
				$count++;
			}
			$ind_data = json_encode($ind_row);
			echo $ind_data;
			break;
		}
	}
	flush();
//}
exit;
?>
