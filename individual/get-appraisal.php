<?php
include_once("../config/config_msqli.php");
include_once("../functions/functions.php");
include_once("../functions/calendar-labels.php");
include_once("../functions/perspOrg-scores.php");
//if(isset($_POST['objectId']))
//{
	
	@$objectId = $_POST['objectId'];
	//file_put_contents("id.txt", "objectId = $objectId");
	@$objectType = $_POST["objectType"];
	@$objectPeriod = $_POST['objectPeriod'];
	@$objectDate = $_POST['objectDate'];
	//@$objectDate = "2021-12";
	@$originalDate = $_POST['objectDate'];

	/*@$objectId = "ind8";
	@$objectType = "Individual";
	@$objectPeriod = "Months";
	@$objectDate = "2025-06";
	@$originalDate = "2025-06";*/

	//file_put_contents("auser.txt", "Logged In User = ".$loggedInUser->user_id);
				
	//@$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
	//$table = "measure".$objectPeriod;
	if(strlen($objectDate) == 4) 
	{
		$year = $objectDate;
		$month = date("m");
	}
	else 
	{
		$year = date("Y", strtotime($objectDate));
		$month = date("m", strtotime($objectDate));
	}
	
	$junAppraisal = $year."-".$month."-30";//Making it flexible so that one can change the month for review. LTK 23Aug2021 1123Hrs
	$junAppraisal = date("Y-m-d", strtotime($junAppraisal));
	
	$decAppraisal = $year."-12-31";
	$decAppraisal = date("Y-m-d", strtotime($decAppraisal));
	
	//file_put_contents("aDate.txt", "June => ".$junAppraisal. " Dec = ".$decAppraisal);
	$table = "measuremonths";
	
	//$objectId = "ind20";
	//$objectType = "individual";
	//$objectDate = '2021-05';
	$individual_query="SELECT 
	uc_users.display_name AS name, 
	uc_users.title AS title, 
	uc_users.photo AS photo, 
	uc_users.reportsTo AS reportsTo, 
	organization.name AS department, 
	organization.mission, 
	organization.vision, 
	organization.valuez 
	FROM uc_users, organization
	WHERE uc_users.user_id = '$objectId' AND uc_users.department = organization.id";
	$individual_result = mysqli_query($GLOBALS["___mysqli_ston"], $individual_query);
	$ind_row = mysqli_fetch_assoc($individual_result);
	
	$reportsTo = $ind_row["reportsTo"];
    $ind_row["reportsToId"] = $ind_row["reportsTo"];
	$reportsTo = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT display_name FROM uc_users WHERE user_id = '$reportsTo'");
	$countBosses = mysqli_num_rows($reportsTo);
	$reportsTo = mysqli_fetch_assoc($reportsTo);
	
	if($countBosses > 0)
	{
		$ind_row["reportsTo"] = $reportsTo["display_name"];
	}
	else
	{
		$ind_row["reportsTo"] = "";
	}
	
	$note_query="SELECT interpretation, wayForward FROM note WHERE objectId = '$objectId' ORDER BY date DESC LIMIT 1";
	$note_result = mysqli_query($GLOBALS["___mysqli_ston"], $note_query);
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
			
			/*$getInitiatives = "SELECT initiative.id AS initiativeId, initiative.name AS initiativeName, initiative.dueDate AS initiativeDue, initiative.completionStatus AS initiativeStatus, initiative.deliverable AS deliverable, initiative.scope AS scope, initiative.parent AS parent, initiativeimpact.linkedobjectid FROM initiative, initiativeimpact WHERE initiative.id = initiativeimpact.initiativeid AND initiativeimpact.linkedobjectid = '$objectId' AND initiative.archive <> 'Yes'";*///This was based on the premise that the personal dashboard will feature those initiatives that are linked to the individual. This may be complicated because users may not remember to attach initiatives to users and then we miss out on those assigned to individuals yet are attached to other scorecard objects. Use the one below instead... LTK 22-04-2021
			$getInitiatives = "SELECT DISTINCT
			initiative.id AS initiativeId, 
			initiative.name AS initiativeName, 
			initiative.dueDate AS initiativeDue, 
			initiative.completionStatus AS initiativeStatus, 
			initiative.deliverable AS deliverable, 
			initiative.scope AS scope, 
			initiative.parent AS parent,
			initiative.weight AS weight, 
			initiativeimpact.linkedobjectid 
			FROM initiative, initiativeimpact 
			WHERE initiative.id = initiativeimpact.initiativeid AND initiativeimpact.linkedobjectid = '$objectId' AND initiative.archive <> 'Yes'
			OR initiative.id = initiativeimpact.initiativeid AND initiativeimpact.linkedobjectid = '$objectId' AND initiative.archive IS NULL 
			OR initiative.projectManager = '$objectId' AND initiative.archive <> 'Yes' AND initiativeimpact.initiativeid = initiative.id
			OR initiative.projectManager = '$objectId' AND initiative.archive IS NULL AND initiativeimpact.initiativeid = initiative.id";
			
			$getInitiativesDate = "SELECT DISTINCT
			initiative.id AS initiativeId, 
			initiative.name AS initiativeName, 
			initiative.dueDate AS initiativeDue, 
			initiative.completionStatus AS initiativeStatus, 
			initiative.deliverable AS deliverable, 
			initiative.scope AS scope, 
			initiative.parent AS parent, 
			initiative.weight AS weight,
			initiativeimpact.linkedobjectid 
			FROM initiative, initiativeimpact 
			WHERE initiative.id = initiativeimpact.initiativeid AND initiativeimpact.linkedobjectid = '$objectId' 
			OR initiative.projectManager = '$objectId' AND initiativeimpact.initiativeid = initiative.id"; //use archive not dueDate LTK 01Jul25 115hrs
			
			$getInitiatives_result = mysqli_query($GLOBALS["___mysqli_ston"], $getInitiativesDate);
			$ind_row["Measure Count"] = mysqli_num_rows($getInitiatives_result);
			$count = 1;
			while($row = mysqli_fetch_array($getInitiatives_result))
			{
				//$ind_row["Cascaded From".$count] = $getCascadeRow["objetiveName"];
				
				//Get cascaded objective
				$id = $row["initiativeId"];
				$parentId = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT linkedobjectid FROM initiativeimpact WHERE initiativeid = '$id'");
				$parentId = mysqli_fetch_assoc($parentId);
				$parentId = $parentId["linkedobjectid"];
				$parentIdSub = substr($parentId, 0, 3);
				switch($parentIdSub)
				{
					case "kpi":
					{
						$parentNameQuery = "SELECT objective.name AS name 
						FROM objective, measure 
						WHERE objective.id = measure.linkedObject 
						AND measure.id = '$parentId'";
						break;
					}	
					case "obj":
					{
						$parentNameQuery = "SELECT name FROM objective WHERE id = '$parentId'";
						break;
					}
				}
				
				$parentName = mysqli_query($GLOBALS["___mysqli_ston"], $parentNameQuery);
				$parentName = mysqli_fetch_assoc($parentName);
				if($parentName["name"] == NULL) $ind_row["Cascaded From".$count] = " - ";
				else $ind_row["Cascaded From".$count] = $parentName["name"];
				
				$ind_row["Initiative Name".$count] = $row["initiativeName"];
				$ind_row["Initiative Deliverable".$count] = $row["deliverable"];
				$ind_row["Initiative Scope".$count] = $row["scope"];
				
				if($row["weight"] == 0) $ind_row["Initiative Weight".$count] = "";
				else $ind_row["Initiative Weight".$count] = $row["weight"]*100;
				
				if($row["initiativeDue"] == NULL) 
				{
					$ind_row["Initiative Due".$count] = " ";
					$ind_row["Initiative Due Raw".$count] = " ";
				}
				else 
				{
					$ind_row["Initiative Due".$count] = date("d M Y", strtotime($row["initiativeDue"]));
					$ind_row["Initiative Due Raw".$count] = $row["initiativeDue"];
				}
				//$ind_row["Initiative Status".$count] = $getInitiativeRow["initiativeStatus"];
				$initiativeId = $row["initiativeId"];
				
				//$getInitiativeStatus = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT status, percentageCompletion, details, notes FROM initiative_status WHERE initiativeId = '$initiativeId' AND updatedOn LIKE '$year%' ORDER BY updatedOn DESC LIMIT 1");//This one prevented status that has been update after year end from showing but i don't think it is practical. LTK 22.01.23 0900hrs
				$getInitiativeStatus = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT status, percentageCompletion, details, notes FROM initiative_status WHERE initiativeId = '$initiativeId' ORDER BY updatedOn DESC LIMIT 1");
				$getInitiativeStatusRow = mysqli_fetch_assoc($getInitiativeStatus);
				
				if (mysqli_num_rows($getInitiativeStatus) < 1) $ind_row["Initiative Status".$count] = "";
				else $ind_row["Initiative Status".$count] = $getInitiativeStatusRow["status"];
				
				if (mysqli_num_rows($getInitiativeStatus) > 0)
				$ind_row["Initiative Percentage".$count] = (int)$getInitiativeStatusRow["percentageCompletion"];
				
				if (mysqli_num_rows($getInitiativeStatus) < 1) $ind_row["Initiative Status Details".$count] = '';
				else $ind_row["Initiative Status Details".$count] = $getInitiativeStatusRow["details"];
				
				if (mysqli_num_rows($getInitiativeStatus) > 0)
				$ind_row["Initiative Way Forward".$count] = $getInitiativeStatusRow["notes"];
				
				$halfYearQuery = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT percentageCompletion FROM initiative_status WHERE initiativeId = '$initiativeId' AND updatedOn <=  '$junAppraisal' ORDER BY updatedOn DESC LIMIT 1");
				$halfYear = mysqli_fetch_assoc($halfYearQuery);
				
				if (mysqli_num_rows($halfYearQuery) < 1) $ind_row["halfYear".$count] = " "; 
				else $ind_row["halfYear".$count] = $halfYear["percentageCompletion"];
				
				$fullYearQuery = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT percentageCompletion FROM initiative_status WHERE initiativeId = '$initiativeId' AND updatedOn <=  '$decAppraisal' ORDER BY updatedOn DESC LIMIT 1");
				$fullYear = mysqli_fetch_assoc($fullYearQuery);
				
				if (mysqli_num_rows($fullYearQuery) < 1) $ind_row["fullYear".$count] = " "; 
				else $ind_row["fullYear".$count] = $fullYear["percentageCompletion"];

				$ind_row["supportingDocuments".$count] = "<a href='javascript:void(0)' title='Supporting Documents' onclick='supportingDocuments(\"".$id."\")'><i class='fa fa-paperclip'></i></a>";
				$ind_row["initiativeScoreReview".$count] = "<a href='javascript:void(0)' title='Score Review' onclick='scoreReview(\"".$initiativeId."\",\"".$objectId."\",\"".$row['initiativeName']."\")'><i class='fas fa-edit'></i></a>";
				
				$count++;
			}
			$row = NULL;
			
		/***************************************
		Initiative Parent
		****************************************/
		$initiativeParent = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, name, parent FROM initiative WHERE projectManager = '$objectId' AND parent > 0") or file_put_contents("individualError.txt", "\n Can't SELECT FROM initiative with ERROR ".mysqli_error(), FILE_APPEND);
		$count=1;
		$initiative_count = mysqli_num_rows($initiativeParent);
		$ind_row["Initiative Count"] = $initiative_count;
		if($initiative_count == 0) $ind_row["Initiative Count"] = 0;
		while($row = mysqli_fetch_array($initiativeParent))
		{
			$parentId = $row["parent"];
			
			$parentName = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT name FROM initiative WHERE id = '$parentId'");
			$parentCount = mysqli_num_rows($parentName);
			$parentName = mysqli_fetch_array($parentName);

			if($parentCount > 0)
			$ind_row["initiativeParent".$count] = $parentName["name"];
			
			//$initiative_result = mysqli_query($connect, "SELECT id, name, deliverable, startDate, dueDate, completionDate FROM initiative WHERE parent = '$parentId'") or file_put_contents("individualError.txt", "\n Can't SELECT FROM initiative with ERROR ".mysqli_error(), FILE_APPEND);
			
			//$initiative_result = mysqli_query($connect, $initiative_result);
			
		}//end of Initiative Parent loop
		
		//The below section was getting core values but using it now to pull measures for the individual appraisal page. HACO asking us to show measures in addition to the initiatives. LTK 16Aug2021 1204hrs.
			$row = NULL;
			
			//Include measures linked to the individual at the scorecard tree - reduces the need to duplicate shared measures. LTK 21May2022 1323hrs
			$measureQuery = "SELECT DISTINCT measure.id, measure.name, measure.gaugeType, measure.calendarType, measure.measureType, measure.dataType, 
			measure.aggregationType, measure.description, measure.green, measure.weight, measure.sort 
			FROM measure, tree 
			WHERE measure.linkedObject = '$objectId' AND measure.archive = 'No' 
			OR measure.owner = '$objectId' AND measure.archive = 'No'
			OR tree.id = measure.id AND tree.parent = '$objectId' AND tree.linked = 'yes' AND measure.archive = 'No'
			ORDER BY measure.sort, measure.id";
			
			$getCoreValue = mysqli_query($GLOBALS["___mysqli_ston"], $measureQuery) or file_put_contents("individualError.txt", "\n ERROR => Couldn't SELECT FROM measure with error ".mysqli_error(), FILE_APPEND);
			$count = 1;
            
			while($row = mysqli_fetch_assoc($getCoreValue))
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
                        //$objectDate = date("Y-m-d", strtotime($objectDate."-12-31"));
						break;	
					}
				}
				$coreValueId = $row['id'];
				
				switch($row['aggregationType'])
				{
					case "Last Value":
					{
						//file_put_contents("dateOne.txt","Date = $objectDate");
						$aggregationText = "";
                        $coreValueQuery = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT date, actual, green, red, 3score
						FROM $table WHERE measureId = '$coreValueId' 
						AND date LIKE '$year%' 
						ORDER BY date DESC LIMIT 1") or file_put_contents("individualError.txt", "\n ERROR => Couldn't SELECT FROM measure with error ".mysqli_error(), FILE_APPEND);
                        break;	
					}
					case "Sum":
					{//We are getting Year To Date summation
						$aggregationText = " (YTD Total)";
                        $objectDate = date("Y", strtotime($originalDate));
						$coreValueQuery = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SUM(actual) AS actual, 
						SUM(green) AS green,
						SUM(red) AS red, 
						AVG(3score) AS 3score
						FROM $table WHERE measureId = '$coreValueId' 
						AND date LIKE '$year%' 
						") or file_put_contents("individualError.txt", "\n ERROR => Couldn't SELECT $coreValueId FROM $table  FOR THE PERIOD $objectDate with error ".mysqli_error(), FILE_APPEND);
						
						//file_put_contents("sumMeasures.txt", "\nSELECT $coreValueId FROM $table  FOR THE PERIOD $objectDate", FILE_APPEND);
						
						break;	
					}
					case "Average":
					{//We are getting Year To Date average
                        $aggregationText = " (YTD Average)";
						$objectDate = date("Y", strtotime($originalDate));
						$coreValueQuery = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT AVG(actual) AS actual, 
						AVG(green) AS green,
						AVG(red) AS red, 
						AVG(3score) AS 3score
						FROM $table WHERE measureId = '$coreValueId' 
						AND date LIKE '$year%' 
						") or file_put_contents("individualError.txt", "\n ERROR => Couldn't SELECT $coreValueId FROM $table  FOR THE PERIOD $objectDate with error ".mysqli_error(), FILE_APPEND);
												
						break;	
					}
				}

				//$indMeasure_query = "SELECT AVG(3score) FROM $table WHERE measureId = '$kpiId' AND date LIKE '$objectDate%'";
				
				$ind_row["coreValueCount"] = $count;
				$ind_row["coreValue".$count] = $row["name"].$aggregationText;
				$ind_row["coreValueType".$count] = $row["measureType"];
				
				if($row["weight"] == 0) $ind_row["coreValueWeight".$count] = "";
				else $ind_row["coreValueWeight".$count] = (int)$row["weight"]*100;
				
				$ind_row["coreValueDescription".$count] = $row["description"];
				$dataType = $row["dataType"];
				$ind_row["coreValueId".$count] = preg_replace('/^\D+/', '', $row["id"]);
				//updateChart("measure", "kpi153", "months", "2021-12", "standard");
				$ind_row["coreValueTrend".$count] = "<a href='javascript:void(0)' title='Trend Line' onclick='trendLine(\"measure\",\"".$coreValueId."\",\"".$row['name']."\",\"months\",\"".$originalDate."\",\"".$row['dataType']."\")'><i class='fas fa-chart-line'></i></a>";
				$ind_row["coreValueScoreReview".$count] = "<a href='javascript:void(0)' title='Score Review' onclick='scoreReview(\"".$coreValueId."\",\"".$objectId."\",\"".$row['name']."\")'><i class='fas fa-edit'></i></a>";
				
				$getUpdater = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT updater FROM $table WHERE measureId = '$coreValueId' ORDER BY date DESC LIMIT 1");
				
				if(mysqli_num_rows($getUpdater) > 0)
				{
					$getUpdater = mysqli_fetch_assoc($getUpdater);
					if($getUpdater["updater"] == "Accent Import")
					$ind_row["coreValueUpdater".$count] = "SAGE Import";
					else
					{
						$updater = $getUpdater["updater"];
						$updater = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT display_name FROM uc_users WHERE user_id = '$updater'");
						$updater = mysqli_fetch_assoc($updater);
						$ind_row["coreValueUpdater".$count] = $updater["display_name"];
					}
				}
				else $ind_row["coreValueUpdater".$count] = "";
				
				/*$kpiId = $row["id"];
				$getUpdater = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT 
								 u.display_name as 'updater', 
								 u1.display_name as 'owner'
							FROM `measure` m
							JOIN `uc_users` u on u.user_id = m.updater
							JOIN `uc_users` u1 on u1.user_id = m.owner
							WHERE m.id = '$kpiId'");
				$updaterRow = mysqli_fetch_assoc($getUpdater);
				
				$ind_row["coreValueOwner".$count] = $updaterRow["owner"];
				$ind_row["coreValueUpdater".$count] = $updaterRow["updater"];*/

				$lastValueDate = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT date FROM $table WHERE measureId = '$coreValueId' ORDER BY date DESC LIMIT 1");
				if(mysqli_num_rows($lastValueDate) > 0)
				{
					$lastValueDate = mysqli_fetch_assoc($lastValueDate);
					$ind_row["lastValueDate".$count] = date("M Y", strtotime($lastValueDate["date"]));
				}
				else $ind_row["lastValueDate".$count] = "-";

				$coreValueCount = mysqli_num_rows($coreValueQuery);
				$rowScore = mysqli_fetch_array($coreValueQuery);
				
                if($coreValueId == 'kpi190') 
                {
                    //$rowScore["green"] = "3333";
                //file_put_contents("aggregationType.txt", $row['aggregationType']." => ".$coreValueId." => ".$objectDate." => ".$table." => ".$calendarType." => ".$rowScore["green"]);
                }

				if($coreValueCount > 0)
				{
					if($rowScore["3score"] == NULL || $rowScore["3score"] == '') $ind_row["coreValueScore".$count] = "-"; 
					else 
					{
						$rowScore["3score"] = $rowScore["3score"] * 10;
						$rowScore["3score"] = round($rowScore["3score"],2);
						$ind_row["coreValueScore".$count] = $rowScore["3score"];
					}
				}
				else $ind_row["coreValueScore".$count] = "";
				
				if($coreValueCount > 0)
				{
					if($rowScore["actual"] == NULL || $rowScore["actual"] == '') $ind_row["coreValueActual".$count] = " "; 
					else 
					{
						if($dataType == "Currency")
						{
							if(preg_match('/^\d+\.\d+$/',$rowScore["actual"]))
							$ind_row["coreValueActual".$count] = "KSh ".number_format($rowScore["actual"],2);
							else
							$ind_row["coreValueActual".$count] = "KSh ".number_format($rowScore["actual"]);
						}
						else if($dataType == "Percentage(%)")
						{
							if(preg_match('/^\d+\.\d+$/',$rowScore["actual"]))
							$ind_row["coreValueActual".$count] = number_format($rowScore["actual"],2)."%";
							else
							$ind_row["coreValueActual".$count] = number_format($rowScore["actual"])."%";
						}
						else
						{
							if(preg_match('/^\d+\.\d+$/',$rowScore["actual"]))
							{
								if($coreValueId == 'kpi95') //This measure is in 4 decimal places - need to do a function for special considerations on outlier measures LTK 22.01.23 0919hrs
								{
									$ind_row["coreValueActual".$count] = number_format($rowScore["actual"],5);
								}
								else 
								$ind_row["coreValueActual".$count] = number_format($rowScore["actual"],2);
							}
							else
							$ind_row["coreValueActual".$count] = number_format($rowScore["actual"]);
						}
					}
				}
				else $ind_row["coreValueActual".$count] = "";

				if($coreValueCount > 0)
				{
					if($rowScore["red"] == NULL || $rowScore["red"] == '') 
					{
						//$ind_row["coreValueBaseline".$count] = " "; 
						$ind_row["coreValueBaseline".$count] = $row["green"] * 0.7;
					}
					else $ind_row["coreValueBaseline".$count] = number_format($rowScore["red"]);
				}
				else $ind_row["coreValueBaseline".$count] = "";

				if($coreValueCount > 0)
				{

					if($rowScore["green"] == NULL ) $ind_row["coreValueTarget".$count] = number_format($row["green"]);
					else 
					{
						if($dataType == "Currency")
						{
							if(preg_match('/^\d+\.\d+$/',$rowScore["green"]))
							$ind_row["coreValueTarget".$count] = "KSh ".number_format($rowScore["green"],2);
							else
							$ind_row["coreValueTarget".$count] = "KSh ".number_format($rowScore["green"]);
						}
						else if($dataType == "Percentage(%)")
						{
							if(preg_match('/^\d+\.\d+$/',$rowScore["green"]))
							$ind_row["coreValueTarget".$count] = number_format($rowScore["green"],2)."%";
							else
							$ind_row["coreValueTarget".$count] = number_format($rowScore["green"])."%";
						}
						else
						{
							if(preg_match('/^\d+\.\d+$/',$rowScore["green"]))
							$ind_row["coreValueTarget".$count] = number_format($rowScore["green"],2);
							else
							$ind_row["coreValueTarget".$count] = number_format($rowScore["green"]);
						}
					}
				}
				else $ind_row["coreValueTarget".$count] = "";
				
				$ind_row["coreValueFrequency".$count] =$calendarType;
				
				$kpiIdMini = preg_replace('/[^0-9]/', '', $coreValueId);
				//file_put_contents("kpi.txt", "ID => $kpiIdMini");
				$checkDocs = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id FROM initiative_evidence WHERE initiativeId = '$kpiIdMini'");
				if(mysqli_num_rows($checkDocs) > 0)
				$ind_row["coreValueEvidence".$count] = "<a href='javascript:void(0)' title='Supporting Documents' onclick='supportingDocuments(\"".$coreValueId."\")'><i class='fa fa-paperclip text-success'></i></a>";
				else 
				$ind_row["coreValueEvidence".$count] = "<a href='javascript:void(0)' title='Supporting Documents' onclick='supportingDocuments(\"".$coreValueId."\")'><i class='fa fa-paperclip'></i></a>";
				
				switch($table)
				{
					case "measuremonths":
					{
						$ind_row["coreValueDate".$count] = date("F, Y",strtotime($objectDate));
						break;
					}
					case "measurequarters":
					{
						//$month = date("m",strtotime($indKpiRow["date"]));
						if($month > 0 && $month < 4)
						$ind_row["coreValueDate".$count] = "Q1 ".date("Y",strtotime($objectDate));
						else if ($month > 3 && $month < 7)
						$ind_row["coreValueDate".$count] = "Q2 ".date("Y",strtotime($objectDate));
						else if ($month > 6 && $month < 10)
						$ind_row["coreValueDate".$count] = "Q3 ".date("Y",strtotime($objectDate));
						else
						$ind_row["coreValueDate".$count] = "Q4 ".date("Y",strtotime($objectDate));
						break;
					}
					case "measurehalfyear":
					{
						//$month = date("m",strtotime($indKpiRow["date"]));
						if($month > 1 && $month < 7)
						$ind_row["coreValueDate".$count] = "HY1 ".date("Y",strtotime($objectDate));
						else 
						$ind_row["coreValueDate".$count] = "HY2 ".date("Y",strtotime($objectDate));
						break;
					}
					case "measureyears":
					{
						$ind_row["coreValueDate".$count] = date("Y",strtotime($objectDate));
						break;
					}
					default:
					{
						$ind_row["coreValueDate".$count] = $rowScore["date"];
						break;	
					}
				}
				
				$coreValueObjective = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT objective.name AS name, objective.id AS id FROM objective, measure WHERE measure.id = '$coreValueId' AND objective.id = measure.linkedObject") or file_put_contents("individual.txt", "\n ERROR => Couldn't SELECT FROM objective with error ".mysqli_error(), FILE_APPEND);
				$coreValueObjectiveCount = mysqli_num_rows($coreValueObjective);
				$coreValueObjectiveRow = mysqli_fetch_array($coreValueObjective);
				if($coreValueObjectiveCount == 0) $ind_row["coreValueObjective".$count] = " ";
				else $ind_row["coreValueObjective".$count] = $coreValueObjectiveRow["name"];
				
				$count++;
			}
			$row = NULL;
			
			$ind_data = json_encode($ind_row);
			echo $ind_data;
			
	flush();
//}
exit;
?>