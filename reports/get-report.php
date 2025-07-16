<?php
error_reporting(E_ERROR | E_PARSE);
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");
include_once("../functions/calendar-labels.php");
include_once("../functions/perspOrg-scores.php");
function groupInitiatives($initiativeId)
{
	global $connect;
	$initiativeQuery = mysqli_query($connect, "SELECT id, name FROM initiative WHERE id = '$initiativeId'");
	$initiativeNumRows = mysqli_num_rows($initiativeQuery);
	$initiativeCount = 1;
	echo "{\"reportType\":\"initiativeGroup\", \"reportName\":\"".$data["reportName"]."\", \"displayInitiatives\": \"".$row["initiativeFilter"]."\", \"Initiatives\":[";
	while($initiativeResult = mysqli_fetch_assoc($initiativeQuery))
	{
		$initiativeId = $initiativeResult["id"];
		$initiativeKpisQuery = mysqli_query($connect, "SELECT measure.name AS name, measure.id AS id, measure.calendarType AS calendarType 
		FROM measure, initiativeimpact 
		WHERE measure.id = initiativeimpact.linkedobjectid
		AND initiativeimpact.initiativeid = '$initiativeId'");
		$kpiCount = 1;
		$initiativeData["initiative"] = $initiativeResult["name"];
		$table = 'measuremonths';
		while($initiativeKpisResult = mysqli_fetch_assoc($initiativeKpisQuery))
		{
			$initiativeData["kpi".$kpiCount] = $initiativeKpisResult["name"];
			$thiKpiId = $initiativeKpisResult["id"];
			$thiKpiTable = $initiativeKpisResult["calendarType"];
			switch($thiKpiTable)
			{
				case 'Daily':
				{
					$table = 'measuredays';
					break;
				}
				case 'Weekly':
				{
					$table = 'measureweeks';
					break;
				}
				case 'Monthly':
				{
					$table = 'measuremonths';
					break;
				}
				case 'Quarterly':
				{
					$table = 'measurequarters';
					break;
				}
				case 'Bi-Annually':
				{
					$table = 'measurehalfyear';
					break;
				}
				case 'Yearly':
				{
					$table = 'measureyears';
					break;
				}
			}
			$thiKpiQuery = mysqli_query($connect, "SELECT MAX(Date), actual, green FROM $table WHERE measureId = '$thiKpiId'");
			$thiKpiResult = mysqli_fetch_assoc($thiKpiQuery);
			$initiativeData["kpiActual".$kpiCount] = $thiKpiResult["actual"];
			$initiativeData["kpiGreen".$kpiCount] = $thiKpiResult["green"];
			$kpiCount++;
		}
		$initiativeData["kpiCount"] = $kpiCount-1;
		$initiativeData = json_encode($initiativeData);
		echo $initiativeData;
		if($initiativeCount < $initiativeNumRows) echo ",";
		$initiativeCount++;
		$initiativeData = NULL;
	}
	echo "]}";
}
@$reportId = $_POST["reportId"];
@$objectDate = $_POST["globalDate"];
//global $reportId;
//global $objectDate;
//global $table;
//$reportId = '1';
//$objectDate = '2021-03';

$get_report_type = mysqli_query($connect, "SELECT Type FROM report WHERE Id = '$reportId'");
$get_report_type = mysqli_fetch_assoc($get_report_type);
@$reportType = $get_report_type["Type"];

switch($reportType)
{
	case "initiativeReport":
	{
		initiativeReport($reportId, $reportType, $objectDate);
		break;	
	}
	//case "summaryReport":
	case "customReport":
	{
		if (isset($_POST["orgId"])) customReport($reportId, $_POST["orgId"], $objectDate);//use the $reportType variable to carry the organization id since it is really not being used. No need to create another function that does the same thing.
		else customReport($reportId, $reportType, $objectDate);
		break;	
	}
	case "cascadeReport":
	{
		cascadeReport($reportType, $objectDate);
		break;	
	}	
}

function cascadeReport($reportType, $objectDate)
{
	global $connect;
	$cascadeOrgName = NULL;
	$cascadeReport = mysqli_query($connect, "SELECT objective.id AS objId, objective.name AS objName, objective.cascadedFrom AS cascadedFrom, objective.linkedObject AS linkedObject FROM objective WHERE cascadedfrom IS NOT NULL");
	//echo "<table border='1'><tr><td>Linked To</td><td>Obj Name</td><td>Cascaded From</td><td>Cascaded From Objective</td></tr>";
	$count = 1;
	$num_rows = mysqli_num_rows($cascadeReport);
	echo "[";
	while($row = mysqli_fetch_array($cascadeReport))
	{
		$objId = $row["objId"];
		//echo "<tr>";
		
		$orgLink = mysqli_query($connect, "SELECT organization.name AS orgName, organization.id AS orgId FROM organization, objective 
			WHERE organization.id = objective.linkedObject AND objective.id = '$objId'");
			if(mysqli_num_rows($orgLink) == NULL)
			{
				$orgLink = mysqli_query($connect, "SELECT organization.name AS orgName FROM organization, perspective, objective WHERE perspective.parentId = organization.id AND objective.linkedObject = perspective.id AND objective.id = '$objId'");
				$row3 = mysqli_fetch_assoc($orgLink);
			}
			else
			$row3 = mysqli_fetch_assoc($orgLink);
	
		if($row3["orgName"]==NULL || $row3["orgName"]=='') $data["linkOrgName"] = ''; else $data["linkOrgName"] = $row3["orgName"];
		if($row["objName"]==NULL || $row["objName"]=='') $data["ObjectiveName"] = ''; else $data["ObjectiveName"] = $row["objName"];
		//echo "<td>".$row3["orgName"]."</td>";
		//echo "<td>".$objName = $row["objName"]."</td>";
			$orgCascade = mysqli_query($connect, "SELECT organization.name AS orgName, organization.id AS orgId FROM organization, objective 
			WHERE organization.id = objective.cascadedFrom AND objective.id = '$objId'");
			$row2 = mysqli_fetch_assoc($orgCascade);
			//$cascadeOrgName = $row2["orgName"];
			$cascadeOrgId = $row2["orgId"];
		
		if($row2["orgName"] == NULL || $row2["orgName"] == '') $data["cascadeOrgName"] = ''; else $data["cascadeOrgName"] = $row2["orgName"];
		//echo "<td>".$cascadeOrgName."</td>";
			$objCascade = mysqli_query($connect, "SELECT name FROM objective WHERE objective.cascadedFrom = '$cascadeOrgId'");
			$row3 = mysqli_fetch_assoc($objCascade);
	if($row3["name"] == '' || $row3["name"] == NULL) $data["cascadeObj"] = ''; else $data["cascadeObj"] = $row3["name"];
	if($count == 1)
		$data["reportType"] = $reportType;
	$data = json_encode($data);
	echo $data;
	if($count < $num_rows)
	echo ",";
	$count++;
	$data = NULL;
	//	echo "<td>".$row3["name"]."</td></tr>";
	}
	echo "]";	
}
customReport('0', 'customReport', '2025-07');
function customReport($reportId, $reportType, $objectDate)
{
	global $connect;
	$objectFilter = "";
	$colSpan = 0;
	$colHeaders = "<tr>";
	$get_report = mysqli_query($connect, "SELECT * FROM report WHERE id = '$reportId'");
	//echo 'Test'.$colHeaders;
	while($row = mysqli_fetch_array($get_report))
	{
		//$data["reportType"] = $reportType; -> doesn't add any value.
		$data["reportType"] = "customReport";
		$data["reportName"] = $row["reportName"];
		$objectFilter = $row["selectedObjects"];
		$data["displayColumnsId"] = $row["displayId"];
		
		if($row["initiativeGroup"] == "true")
		{
			$colSpan++;
			$colHeaders = $colHeaders."<th>Initiative</th>";
			groupInitiatives($objectFilter);
			exit;
		}
		echo "{";
		$data["displayColumnsOrg"] = $row["Organization"];
		if($row["Organization"] == "true")
		{
			$colSpan++;
			$colHeaders = $colHeaders."<th>Organization</th>";
		}
		$data["displayColumnsOrgScore"] = $row["orgScore"];
		if($row["orgScore"] == "true")
		{
			$colSpan++;
			$colHeaders = $colHeaders."<th>Score</th>";
		}
		$data["displayColumnsPersp"] = $row["Perspective"];
		if($row["Perspective"] == "true")
		{
			$colSpan++;
			$colHeaders = $colHeaders."<th>Perspective</th>";
		}
		$data["displayColumnsPerspScore"] = $row["perspScore"];
		if($row["perspScore"] == "true")
		{
			$colSpan++;
			$colHeaders = $colHeaders."<th>Score</th>";
		}
		$data["displayColumnsObj"] = $row["Objective"];
		if($row["Objective"] == "true")
		{
			$colSpan++;
			$colHeaders = $colHeaders."<th>Objective</th>";
		}
		$data["displayColumnsObjScore"] = $row["objScore"];
		if($row["objScore"] == "true")
		{
			$colSpan++;
			$colHeaders = $colHeaders."<th>Score</th>";
		}
		$data["displayColumnsName"] = $row["Measure"];
		if($row["Measure"] == "true")
		{
			$colSpan++;
			$colHeaders = $colHeaders."<th>Measure</th>";
		}
		$data["displayColumnsOwner"] = $row["Owner"];
		if($row["Owner"] == "true")
		{
			$colSpan++;
			$colHeaders = $colHeaders."<th>Owner</th>";
		}
		$data["displayColumnsUpdater"] = $row["Updater"];
		if($row["Updater"] == "true")
		{
			$colSpan++;
			$colHeaders = $colHeaders."<th>Updater</th>";
		}
		$data["displayColumnsScore"] = $row["Score"];
		if($row["Score"] == "true")
		{
			$colSpan++;
			$colHeaders = $colHeaders."<th>Score</th>";
		}
		$data["displayColumnsActual"] = $row["Actual"];
		if($row["Actual"] == "true")
		{
			$colSpan++;
			$colHeaders = $colHeaders."<th>Actual</th>";
		}
		$data["displayColumnsRed"] = $row["Red"];
		if($row["Red"] == "true")
		{
			$colSpan++;
			$colHeaders = $colHeaders."<th>Baseline</th>";
		}
		$data["displayColumnsGreen"] = $row["Green"];
		if($row["Green"] == "true")
		{
			$colSpan++;
			$colHeaders = $colHeaders."<th>Target</th>";
		}
		$data["displayColumnsVariance"] = $row["Variance"];
		if($row["Variance"] == "true")
		{
			$colSpan++;
			$colHeaders = $colHeaders."<th>Variance</th>";
		}
		$data["displayColumnsPercentVariance"] = $row["PercentVariance"];
		if($row["PercentVariance"] == "true")
		{
			$colSpan++;
			$colHeaders = $colHeaders."<th>% Variance</th>";
		}
		$data["displayInitiatives"] = $row["initiativeFilter"];
		if($row["initiativeFilter"] == "true" && $row["initiativeGroup"] == "false")
		{
			$colSpan++;
			$colHeaders = $colHeaders."<th>Initiatives/Due Date</th>";
		}
		$data["groupInitiatives"] = $row["initiativeGroup"];
		
		$colHeaders = $colHeaders."</tr>";
		$data["colSpan"] = $colSpan;
		$data["colHeaders"] = $colHeaders;
		$data = json_encode($data);
		$data = substr($data, 1, -1);
		echo $data.",";
	}
	//echo "\"Measure\":[";
	echo "[";
	$data = NULL;
	
	/***************************************************************************************************
	Code to get scorecard and then initiative details...
	****************************************************************************************************/
	$initiativeIdPool = NULL;
	$noOrganization = "false"; $noPerspective = "false"; $noObjective = "false";
	$orgRowCount = 0; $perspRowCount = 0; $objRowCount = 0; $kpiRowCount = 0;
	$id = 0;
	//$table = "measuremonths";
	//$table = "measurequarters";
	//$objectDate = '2015-05-30';
	//echo "<br><b>Filter: ".$objectFilter."</b><br>";
	if($reportType != "customReport") $scorecard_query="SELECT organization.id AS orgId, organization.name AS orgName FROM organization WHERE id = '$reportType'";
	else if($objectFilter != NULL)
	$scorecard_query="SELECT organization.id AS orgId, organization.name AS orgName FROM organization WHERE id IN('$objectFilter')";
	else
	$scorecard_query = "SELECT organization.id AS orgId, organization.name AS orgName FROM organization";
	$scorecard_result = mysqli_query($connect, $scorecard_query);
	$scorecard_count = mysqli_num_rows($scorecard_result);
	if($scorecard_count == 0)
	{
		//echo "No organization";
		$scorecard_result = mysqli_query($connect, "SELECT organization.id AS orgId ,organization.name AS orgName FROM organization LIMIT 1");
		$noOrganization = "true";
	}
	while($orgRow = mysqli_fetch_assoc($scorecard_result))
	{
		if($noOrganization == "true")
			$orgId = NULL;
		else
			$orgId = $orgRow["orgId"];
		
		if($objectFilter != NULL)
			$perspectives = mysqli_query($connect, "SELECT perspective.id AS perspId, perspective.name AS perspName 
			FROM perspective WHERE parentID = '$orgId' OR id IN ('$objectFilter')");
		else
			$perspectives = mysqli_query($connect, "SELECT perspective.id AS perspId, perspective.name AS perspName 
			FROM perspective WHERE parentId = '$orgId'");
		
		$perspective_count = mysqli_num_rows($perspectives);
		$perspRowCount = $perspective_count;
		if($perspective_count == 0)
		{
			//echo "No perspective";
			$perspectives = mysqli_query($connect, "SELECT perspective.id AS perspId ,perspective.name AS perspName FROM perspective LIMIT 1");
			$noPerspective = "true";
		}
		if(mysqli_num_rows($perspectives) > 0)//organization has perspectives
		{
			while($perspRow = mysqli_fetch_assoc($perspectives))
			{
				if($noPerspective == "true")
					$perspId = NULL;
				else
					$perspId = $perspRow["perspId"];
				
				if($objectFilter != NULL)
					$objectives = mysqli_query($connect, "SELECT objective.id AS objId, objective.name AS objName 
					FROM objective WHERE linkedObject = '$perspId' OR id IN ('$objectFilter')");
				else
					$objectives = mysqli_query($connect, "SELECT objective.id AS objId, objective.name AS objName 
					FROM objective WHERE linkedObject = '$perspId'");
			
				$objective_count = mysqli_num_rows($objectives);
				$objRowCount = $objective_count;
				if($objective_count == 0)
				{
					//echo "No objective";
					$objectives = mysqli_query($connect, "SELECT objective.id AS objId ,objective.name AS objName FROM objective LIMIT 1");
					$noObjective = "true";
				}		
			
				if(mysqli_num_rows($objectives) > 0)//perspective has objectives
				{
					while($objRow = mysqli_fetch_assoc($objectives))
					{
						$orgScoreRound = NULL;
						$perpsScoreRound = NULL;
						$objScoreRound = NULL;
						$kpiScoreRound = NULL;
						if($noObjective == "true")
							$objId = NULL;
						else
							$objId = $objRow["objId"];
						
						if($objectFilter != NULL)
							$measures = mysqli_query($connect, "SELECT measure.id AS kpiId, measure.name AS kpiName, 
							measure.owner AS kpiOwner, measure.updater AS kpiUpdater, measure.green AS kpiGreen,
							 measure.red AS kpiRed, measure.gaugeType AS gaugeType, measure.calendarType AS calendarType 
							FROM measure WHERE linkedObject = '$objId' OR id IN ('$objectFilter')");
						else
							$measures = mysqli_query($connect, "SELECT measure.id AS kpiId, measure.name AS kpiName, 
							measure.owner AS kpiOwner, measure.updater AS kpiUpdater, measure.green AS kpiGreen,
							 measure.red AS kpiRed, measure.gaugeType AS gaugeType, measure.calendarType AS calendarType 
							FROM measure WHERE linkedObject = '$objId'");
							
						if(mysqli_num_rows($measures) > 0)// objective has measures
						{
							$kpiRowCount = mysqli_num_rows($measures);
							while($kpiRow = mysqli_fetch_assoc($measures))
							{
								$orgScoreRound = NULL;
								$perpsScoreRound = NULL;
								$objScoreRound = NULL;
								$kpiScoreRound = NULL;
						
								$thiKpiTable = $kpiRow["calendarType"];
								switch($thiKpiTable)
								{
									case 'Daily':
									{
										$table = 'measuredays';
										break;
									}
									case 'Weekly':
									{
										$table = 'measureweeks';
										break;
									}
									case 'Monthly':
									{
										$table = 'measuremonths';
										break;
									}
									case 'Quarterly':
									{
										$table = 'measurequarters';
										break;
									}
									case 'Bi-Annually':
									{
										$table = 'measurehalfyear';
										break;
									}
									case 'Yearly':
									{
										$table = 'measureyears';
										break;
									}
								}
								$scorecard_row["id"] = $id;
								$scorecard_row["Organization"] = $orgRow["orgName"];
								$scorecard_row["orgId"] = $orgRow["orgId"];
								$orgId = $orgRow["orgId"];
								
								//$orgScoreRound = organization_score($organization_score, $objectDate, $table);
								//$orgScoreRound = round($orgScoreRound,2);
								//if($orgScoreRound == 0) $orgScoreRound = '';
								$objectId = orgChildIds($orgRow["orgId"]);
								$objectPeriod = "months";
								$valuesCount = 1;
								switch($objectPeriod)
								{
									case "days":
									{
										$orgScoreRound = daysAsIs($objectId, $objectDate, $valuesCount);
										break;
									}
									/*******************************************************************************************************************
									$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
									********************************************************************************************************************/
									case "weeks":
									{
										//daysInWeeks($objectId, $objectDate, $valuesCount,  $red, $green, $darkgreen, $blue, $gaugeType);
										$orgScoreRound = weeksAsIs($objectId, $objectDate, $valuesCount);
										break;
									}
									/*******************************************************************************************************************
									$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
									********************************************************************************************************************/
									case "months":
									{
										$orgScoreRound = monthsAsIsSingle($objectId, $objectDate, $valuesCount);
										break;
									}
									/*******************************************************************************************************************
									$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
									********************************************************************************************************************/
									case "quarters":
									{
										$orgScoreRound = quartersAsIs($objectId, $objectDate, $valuesCount);
										break;
									}	
									/*******************************************************************************************************************
									$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
									********************************************************************************************************************/
									case "halfYears":
									{
										//$objectDate = strtotime ( '-1 years' , strtotime ( $objectDate.'-01-01' ) ) ;
										//$objectDate = date ( 'Y-m-d' , $objectDate );
										$orgScoreRound = halfYearsAsIs($objectId, $objectDate, $valuesCount);
										break;
									}
									/*******************************************************************************************************************
									$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
									********************************************************************************************************************/
									case "years":
									{
										//$objectDate = strtotime ( '+1 years' , strtotime ( $objectDate.'-01-01' ) ) ;
										//$objectDate = date ( 'Y-m-d' , $objectDate );
										$orgScoreRound = yearsAsIs($objectId, $objectDate, $valuesCount);
										break;
									}
								}
								$orgScoreRound = round($orgScoreRound,2);
								if($orgScoreRound == 0) $orgScoreRound = '';
								
								$scorecard_row["orgScore"] = $orgScoreRound;
								
								$scorecard_row["orgColor"] = return_color($orgScoreRound, "threeColor");
								$scorecard_row["orgRowCount"] = $orgRowCount;
								//$scorecard_row["orgScore"] = "10";
								
								$scorecard_row["Perspective"] = $perspRow["perspName"];
								$scorecard_row["perspId"] = $perspRow["perspId"];
								$perspective_score = $perspRow["perspId"];
								$perspScoreRound = perspective_score($perspective_score, $objectDate, $table);
								$perspScoreRound = round($perspScoreRound,2);
								if($perspScoreRound == 0) $perspScoreRound = '';
								$scorecard_row["perspScore"] = $perspScoreRound;
								$scorecard_row["perspColor"] = return_color($perspScoreRound, "threeColor");
								$scorecard_row["perspRowCount"] = $perspRowCount;
								//$scorecard_row["perspScore"] = "10";
								
								$scorecard_row["Objective"] = $objRow["objName"];
								$scorecard_row["objId"] = $objRow["objId"];
								$objective_score = $objRow["objId"];
								$objId = $objRow["objId"]; //for retrieving initiatives
								$objScoreRound = objective_score($objective_score, $objectDate, $table);
								$objScoreRound = round($objScoreRound,2);
								if($objScoreRound == 0) $objScoreRound = '';
								$scorecard_row["objScore"] = $objScoreRound;
								$scorecard_row["objColor"] = return_color($objScoreRound, "threeColor");
								$scorecard_row["objRowCount"] = $objRowCount;
								//$scorecard_row["objScore"] = "10";
																
								$scorecard_row["Measure"] = $kpiRow["kpiName"];
								$scorecard_row["Owner"] = $kpiRow["kpiOwner"];
								$scorecard_row["Updater"] = $kpiRow["kpiUpdater"];
								$scorecard_row["Green"] = $kpiRow["kpiGreen"];
								$scorecard_row["Red"] = $kpiRow["kpiRed"];
								$gaugeType = $kpiRow["gaugeType"];
								//$scorecard_row["gaugeType"] = $kpiRow["gaugeType"];
								$scorecard_row["kpiId"] = $kpiRow["kpiId"];
								$scorecard_row["kpiRowCount"] = $kpiRowCount;
								$kpiId = $kpiRow["kpiId"];
								
								$initiativeCount = 0;
								$initiatives = "<table width='100%'>";
								$initiativeQuery = mysqli_query($connect, "SELECT initiative.name AS name, initiative.dueDate AS dueDate, initiative.completionDate AS completionDate FROM initiative, initiativeimpact 
								WHERE initiative.id = initiativeimpact.initiativeid AND initiativeimpact.linkedobjectid = '$objId'");
								while($initiativeRow = mysqli_fetch_assoc($initiativeQuery))
								{
									//$persp_row["dueDate".$count] = $row["dueDate"];
									if($initiativeRow["dueDate"] <= date("Y-m-d") && $initiativeRow["completionDate"] == NULL)
										$color = "red";
									else if ($initiativeRow["dueDate"] < date("Y-m-d") && $initiativeRow["completionDate"] != NULL)
										$color = "green";
									else if ($initiativeRow["completionDate"] > $initiativeRow["dueDate"] && $initiativeRow["completionDate"] != NULL)
										$color = "yellow";
									else $color = "#FFFFFF";
									if($initiativeRow["dueDate"] == NULL || $initiativeRow["dueDate"] == '0000-00-00' || $initiativeRow["dueDate"] == '1970-01-01')
									{
										$color = "#D0D0D0";//light grey color
									}
									
									//if($initiativeCount == 0)
									//$initiatives = $initiativeRow["name"];
									//else
									$initiatives = $initiatives."<tr><td style='border-top:1px solid white;border-left:1px solid white;'>".$initiativeRow["name"]."</td><td width='10%' bgcolor='".$color."'>".$initiativeRow["dueDate"]."</td></tr>";
									$initiativeCount++;
								}
								$scorecard_row["Initiatives"] = $initiatives."</table>";
								
								$individualCount = 0;
								$individuals = "<table width='100%'>";
								$individualQuery = mysqli_query($connect, "SELECT uc_users.user_id AS user_id, uc_users.display_name AS display_name
								FROM uc_users, individual
								WHERE uc_users.user_id = individual.id
								AND individual.cascadedFrom = '$orgId'
								AND uc_users.id != '1' 
								AND uc_users.id != '19' 
								AND uc_users.id != '22' 
								AND uc_users.id != '29' 
								AND uc_users.id != '58'");
								while($individualRow = mysqli_fetch_assoc($individualQuery))
								{
									//$indScore = individualScore($individualRow["user_id"], $objectDate, $objectPeriod);
									$indScore = individualScore($individualRow["user_id"]);
									$indScore = round($indScore,2);
									$indColor = return_color($indScore, "threeColor");
									$individuals = $individuals."<tr><td style='border-top:1px solid white;border-left:1px solid white;'>".$individualRow["display_name"]."</td><td width='10%' bgcolor='".$indColor."'>".$indScore."</td></tr>";
									$individualCount++;
								}
								$scorecard_row["Individuals"] = $individuals."</table>";
	
								//get measure scores, actuals and targets.
								$scores_query = mysqli_query($connect, "SELECT 3score, Actual FROM $table 
								WHERE measureId = '$kpiId' ORDER BY date DESC LIMIT 1");
								file_put_contents("atest.txt", "table = $table and date = $objectDate and measureid = $kpiId");
								$scores_result = mysqli_fetch_assoc($scores_query);
								$kpiScoreRound = $scores_result["3score"];
								$kpiScoreRound = round($kpiScoreRound,2);
								if($kpiScoreRound == 0) $kpiScoreRound = '';
								$scorecard_row["Score"] = $kpiScoreRound;
								$scoreColor =  $scores_result["3score"];
								$scorecard_row["scoreColor"] = return_color($scoreColor, $gaugeType);
								$kpiActualRound = $scores_result["Actual"];
								//$kpiActualRound = round($kpiActualRound,2);
								//if($kpiActualRound == 0) $kpiActualRound = '';
								$scorecard_row["Actual"] = $kpiActualRound;
								
								$scorecard_data = json_encode($scorecard_row);
								echo $scorecard_data.",";
								$id++;
							}
							
						}
						else //objective has no measures
						{
							$scorecard_row4["id"] = $id;
							$scorecard_row4["Organization"] = $orgRow["orgName"];
							$scorecard_row4["orgId"] = $orgRow["orgId"];
							$scorecard_row4["Perspective"] = $perspRow["perspName"];
							$scorecard_row4["perspId"] = $perspRow["perspId"];
							$scorecard_row4["Objective"] = $objRow["objName"];
							$scorecard_row4["objId"] = $objRow["objId"];
							$scorecard_data = json_encode($scorecard_row4);
							echo $scorecard_data.",";
							$id++;
						}
					}
				}
				else //perspective has no objectives
				{
					$scorecard_row3["id"] = $id;
					$scorecard_row3["Organization"] = $orgRow["orgName"];
					$scorecard_row3["orgId"] = $orgRow["orgId"];
					$scorecard_row3["Perspective"] = $perspRow["perspName"];
					$scorecard_row3["perspId"] = $perspRow["perspId"];
					$scorecard_data = json_encode($scorecard_row3);
					echo $scorecard_data.",";
					$id++;
				}
			}
		}
		else//organization has no perspectives but does it have objectives?
		{
			$orgId = $orgRow["orgId"];
			if($objectFilter != NULL)
				$orgObjectives = mysqli_query($connect, "SELECT objective.id AS objId, objective.name AS objName 
				FROM objective WHERE linkedObject = '$orgId' OR id IN('$objectFilter')");
			else
				$orgObjectives = mysqli_query($connect, "SELECT objective.id AS objId, objective.name AS objName 
				FROM objective WHERE linkedObject = '$orgId'");
			if(mysqli_num_rows($orgObjectives) > 0)// organization has objectives but no perspectives
			{
				while($orgObjRow = mysqli_fetch_assoc($orgObjectives))
				{	
					$objId = $orgObjRow["objId"];
					if($objectFilter != NULL)
					$orgObjKpis = mysqli_query($connect, "SELECT measure.id AS kpiId, measure.name AS kpiName, 
						measure.owner AS kpiOwner, measure.updater AS kpiUpdater, 
						measure.green AS kpiGreen, measure.red AS kpiRed 
						FROM measure WHERE linkedObject = '$objId' OR id IN('$objectFilter')");
					else
					$orgObjKpis = mysqli_query($connect, "SELECT measure.id AS kpiId, measure.name AS kpiName, 
						measure.owner AS kpiOwner, measure.updater AS kpiUpdater, 
						measure.green AS kpiGreen, measure.red AS kpiRed 
						FROM measure WHERE linkedObject = '$objId'");
					if(mysqli_num_rows($orgObjKpis) > 0)// organization has objectives and measures but no perspectives
					{
						//echo "<br><strong>Ero: ".$objId."</strong><br>";
					
						while($orgObjKpiRow = mysqli_fetch_assoc($orgObjKpis))
						{
							$scorecard_row7["id"] = $id;
							$scorecard_row7["Organization"] = $orgRow["orgName"];
							$scorecard_row7["orgId"] = $orgRow["orgId"];
							$scorecard_row7["Objective"] = $orgObjRow["objName"];
							$scorecard_row7["objId"] = $orgObjRow["objId"];
							$scorecard_row7["Measure"] = $orgObjKpiRow["kpiName"];
							//commented out the items with $kpiRow since they were creating an error. LTK (23.09.14)
							//$scorecard_row7["Owner"] = $kpiRow["kpiOwner"];
							//$scorecard_row7["Updater"] = $kpiRow["kpiUpdater"];
							$scorecard_row7["Owner"] = $orgObjKpiRow["kpiOwner"];
							$scorecard_row7["Updater"] = $orgObjKpiRow["kpiUpdater"];
							$scorecard_row7["kpiId"] = $orgObjKpiRow["kpiId"];
							//$scorecard_row7["Green"] = $kpiRow["kpiGreen"];
							//$scorecard_row7["Red"] = $kpiRow["kpiRed"];
							$scorecard_row7["Green"] = $orgObjKpiRow["kpiGreen"];
							$scorecard_row7["Red"] = $orgObjKpiRow["kpiRed"];
							
							//get measure scores, actuals and targets.
							$kpiId = $orgObjKpiRow["kpiId"]; //added this line since $kpiId was being indicated as undefined below. LTK (23.09.14)
							
							$initiativeCount = 0;
							$initiatives = NULL;
							$initiativeQuery = mysqli_query($connect, "SELECT initiative.name AS name FROM initiative, initiativeimpact 
							WHERE initiative.id = initiativeimpact.initiativeid AND initiativeimpact.linkedobjectid = '$kpiId'");
							while($initiativeRow = mysqli_fetch_assoc($initiativeQuery))
							{
								if($initiativeCount == 0)
								$initiatives = $initiativeRow["name"];
								else
								$initiatives = $initiatives.", ".$initiativeRow["name"];
								$initiativeCount++;
								//echo "<br><strong>Hapa? $initiatives</strong><br>";
							}
							$scorecard_row7["Initiatives"] = $initiatives;
							
							$individualCount = 0;
							$individuals = "<table width='100%'>";
							$individualQuery = mysqli_query($connect, "SELECT uc_users.user_id AS user_id, uc_users.display_name AS display_name
							FROM uc_users, individual
							WHERE uc_users.user_id = individual.id
							AND individual.cascadedFrom = '$orgId'
							AND uc_users.id != '1' 
							AND uc_users.id != '19' 
							AND uc_users.id != '22' 
							AND uc_users.id != '29' 
							AND uc_users.id != '58'");
							while($individualRow = mysqli_fetch_assoc($individualQuery))
							{
								$indScore = individualScore($individualRow["user_id"], $objectDate, $objectPeriod);
								$indColor = return_color($indScore, "threeColor");
								$individuals = $individuals."<tr><td style='border-top:1px solid white;border-left:1px solid white;'>".$individualRow["display_name"]."</td><td width='10%' bgcolor='".$indColor."'>".$indScore."</td></tr>";
								$individualCount++;
							}
							$scorecard_row7["Individuals"] = $individuals."</table>";
							
							$scores_query=@mysqli_query($connect, "SELECT 3score, Actual FROM $table 
							WHERE measureId = '$kpiId' AND date <= '$objectDate' ORDER BY date DESC LIMIT 1");
							$scores_result=@mysqli_fetch_assoc($scores_query);
							$kpiScoreRound7 = $scores_result["3score"];
							$kpiScoreRound7 = round($kpiScoreRound7,2);
							$scorecard_row7["Score"] = $kpiScoreRound7;
							$kpiActualRound7 =  $scores_result["Actual"];
							$kpiActualRound7 = round($kpiActualRound7,2);
							$scorecard_row7["Actual"] = $kpiActualRound7;
							
							$scorecard_data = json_encode($scorecard_row7);
							echo $scorecard_data.",";
							$id++;
						}
					}
					else //organization has objectives only
					{
						$scorecard_row5["id"] = $id;
						$scorecard_row5["Organization"] = $orgRow["orgName"];
						$scorecard_row5["orgId"] = $orgRow["orgId"];
						$scorecard_row5["Objective"] = $orgObjRow["objName"];
						$scorecard_row5["objId"] = $orgObjRow["objId"];
						$scorecard_data = json_encode($scorecard_row5);
						echo $scorecard_data.",";
						$id++;
					}
				}
			}
			else//organization has no perspectives and objectives
			{
				$orgId = $orgRow["orgId"];
				if($objectFilter != NULL)
				$orgMeasures = mysqli_query($connect, "SELECT measure.id AS kpiId, measure.name AS kpiName, 
						measure.owner AS kpiOwner, measure.updater AS kpiUpdater, measure.green AS kpiGreen,
						 measure.red AS kpiRed 
						FROM measure WHERE linkedObject = '$orgId' OR id IN ('$objectFilter')");
				else
				$orgMeasures = mysqli_query($connect, "SELECT measure.id AS kpiId, measure.name AS kpiName, 
						measure.owner AS kpiOwner, measure.updater AS kpiUpdater, measure.green AS kpiGreen,
						 measure.red AS kpiRed 
						FROM measure WHERE linkedObject = '$orgId'");
				
				if(mysqli_num_rows($orgMeasures) > 0)// organization has measures but no perspectives and no objectives
				{
					while($orgKpiRow = mysqli_fetch_assoc($orgMeasures))
					{	
						$scorecard_row6["id"] = $id;
 						$scorecard_row6["Organization"] = $orgRow["orgName"];
						$scorecard_row6["orgId"] = $orgRow["orgId"];
						$scorecard_row6["Measure"] = $orgKpiRow["kpiName"];
						$scorecard_row6["objId"] = $orgKpiRow["objId"];
						$scorecard_row6["kpiId"] = $orgKpiRow["kpiId"];
						$scorecard_row6["Owner"] = $orgKpiRow["kpiOwner"];
						$scorecard_row6["Updater"] = $orgKpiRow["kpiUpdater"];
						$scorecard_row6["kpiId"] = $orgKpiRow["kpiId"];
						$scorecard_row6["Green"] = $orgKpiRow["kpiGreen"];
						$scorecard_row6["Red"] = $orgKpiRow["kpiRed"];
						
						$kpiId = $orgKpiRow["kpiId"]; //added this line since $kpiId was being indicated as undefined below. LTK (23.09.14)
							
						$initiativeCount = 0;
						$initiatives = NULL;
						$initiativeQuery = mysqli_query($connect, "SELECT initiative.name AS name FROM initiative, initiativeimpact 
						WHERE initiative.id = initiativeimpact.initiativeid AND initiativeimpact.linkedobjectid = '$kpiId'");
						while($initiativeRow = mysqli_fetch_assoc($initiativeQuery))
						{
							if($initiativeCount == 0)
							$initiatives = $initiativeRow["name"];
							else
							$initiatives = $initiatives.", ".$initiativeRow["name"];
							$initiativeCount++;
							//echo "<br><strong>Hapa? $initiatives</strong><br>";
						}
						$scorecard_row6["Initiatives"] = $initiatives;
						
						$individualCount = 0;
						$individuals = "<table width='100%'>";
						$individualQuery = mysqli_query($connect, "SELECT uc_users.user_id AS user_id, uc_users.display_name AS display_name
						FROM uc_users, individual
						WHERE uc_users.user_id = individual.id
						AND individual.cascadedFrom = '$orgId'
						AND uc_users.id != '1' 
						AND uc_users.id != '19' 
						AND uc_users.id != '22' 
						AND uc_users.id != '29' 
						AND uc_users.id != '58'");
						while($individualRow = mysqli_fetch_assoc($individualQuery))
						{
							$indScore = individualScore($individualRow["user_id"], $objectDate, $objectPeriod);
							$indColor = return_color($indScore, "threeColor");
							$individuals = $individuals."<tr><td style='border-top:1px solid white;border-left:1px solid white;'>".$individualRow["display_name"]."</td><td width='10%' bgcolor='".$indColor."'>".$indScore."</td></tr>";
							$individualCount++;
						}
						$scorecard_row6["Individuals"] = $individuals."</table>";
						
						//get measure scores, actuals and targets.
						$scores_query=mysqli_query($connect, "SELECT 3score, Actual FROM $table 
						WHERE measureId = '$kpiId' AND date <= '$objectDate' ORDER BY date DESC LIMIT 1");
						$scores_result=mysqli_fetch_assoc($scores_query);
						$scorecard_row6["Score"] = $scores_result["3score"];
						$scorecard_row6["Actual"] = $scores_result["Actual"];
						
						$scorecard_data = json_encode($scorecard_row6);
						echo $scorecard_data.",";
						$id++;
					}
				}
				else//organization has no perspectives, objectives or measures
				{
					$scorecard_row2["id"] = $id;
					$scorecard_row2["Organization"] = $orgRow["orgName"];
					$scorecard_row2["orgId"] = $orgRow["orgId"];
					$scorecard_data = json_encode($scorecard_row2);
					echo $scorecard_data.",";
					$id++;
				}
			}
		}
	}
	/***************************************************************************************************
	End of code to get scorecard details...
	****************************************************************************************************/
	echo "{}]";
	//echo "]}";
}

//initiativeReport(12, 'initiativeReport', '2017-07');
function initiativeReport($reportId, $reportType, $objectDate)
{
	global $connect;
	$get_report = mysqli_query($connect, "SELECT report.reportName AS reportName, report.selectedObjects AS selectedObjects, report_init.sponsor AS sponsor, report_init.owner AS owner, report_init.budget AS budget, report_init.cost AS cost, report_init.start AS start, report_init.due AS due, report_init.completed AS completed, report_init.deliverable AS deliverable, report_init.deliverableStatus AS deliverableStatus, report_init.parent AS parent, report_init.red AS red, report_init.yellow AS yellow, report_init.green AS green FROM report, report_init 
	WHERE report.id = '$reportId' AND report_init.id = '$reportId'");
	//echo "{
	//	\"Title\":\"Report Details\",
	//	<br><br>\"Structure\":[";
	echo "{";
	$row = mysqli_fetch_assoc($get_report) or die('cant get report details for initiative');
	
	$colSpan = 0;
	$colHeaders = "<tr>";
	
	$data["reportType"] = $reportType;
	$data["reportName"] = $row["reportName"];
	$colSpan++;
	$colHeaders = $colHeaders."<th>Name</th>";
	
	$data["selectedObjects"] = $row["selectedObjects"];
	
	if($row["sponsor"] == "true")
	{
		$colSpan++;
		$colHeaders = $colHeaders."<th>Sponsor</th>";
		$data['displayInitSponsor'] = $row['sponsor'];
	}
	if($row["owner"] == "true")
	{
		$colSpan++;
		$colHeaders = $colHeaders."<th>Owner</th>";
		$data['displayInitOwner'] = $row['owner'];
	}
	if($row["budget"] == "true")
	{
		$colSpan++;
		$colHeaders = $colHeaders."<th>Budget</th>";
		$data['displayInitBudget'] = $row['budget'];
	}
	if($row["cost"] == "true")
	{
		$colSpan++;
		$colHeaders = $colHeaders."<th>Cost</th>";
		$data['displayInitCost'] = $row['cost'];
	}
	if($row["start"] == "true")
	{
		$colSpan++;
		$colHeaders = $colHeaders."<th>Start</th>";
		$data['displayInitStart'] = $row['start'];
	}
	if($row["due"] == "true")
	{
		$colSpan++;
		$colHeaders = $colHeaders."<th>Due</th>";
		$data['displayInitDue'] = $row['due'];
	}
	if($row["completed"] == "true")
	{
		$colSpan++;
		$colHeaders = $colHeaders."<th>Completion Date</th>";
		$data['displayInitComplete'] = $row['completed'];
	}
	if($row["deliverable"] == "true")
	{
		$colSpan++;
		$colHeaders = $colHeaders."<th>Deliverable</th>";
		$data['displayInitDeliverable'] = $row['deliverable'];
	}
	if($row["deliverableStatus"] == "true")
	{
		$colSpan++;
		$colHeaders = $colHeaders."<th>Deliverable Status</th>";
		$data['displayInitDeliverableStatus'] = $row['deliverableStatus'];
	}
	if($row["parent"] == "true")
	{
		$colSpan++;
		$colHeaders = $colHeaders."<th>Impacted Objective</th>";
		$data['displayInitParent'] = $row['parent'];
	}
	$data['displayInitRedFilter'] = $row['red'];
	$data['displayInitGreyFilter'] = $row['yellow'];
	$data['displayInitGreenFilter'] = $row['green'];
	
	$colHeaders = $colHeaders."</tr>";
	$data["colSpan"] = $colSpan;
	$data["colHeaders"] = $colHeaders;
	
	$data = json_encode($data);
	$data = substr($data, 1, -1);
	echo $data.",";
	
	$data = NULL;
	if($row["selectedObjects"] == NULL || $row["selectedObjects"] == '')
	{
		$get_report = mysqli_query($connect, "SELECT initiative.id, initiative.name, initiative.sponsor, initiative.projectManager, initiative.budget, initiative.damage, initiative.startDate, initiative.dueDate, initiative.completionDate, initiative.completionStatus, initiative.deliverable, initiative.deliverableStatus, initiative.parent FROM initiative");
	}
	else
	{
		$idSwitch = substr($row["selectedObjects"], 0, 3);
		$objectId = $row["selectedObjects"];
		switch($idSwitch)
		{
			case "org":
			{
				$initiativeIn = orgChildIds($objectId);
				break;
			}
			case "per":
			{
				$initiativeIn = perspChildIds($objectId);
				break;
			}
			case "obj":
			{
				$initiativeIn = objChildIds($objectId);
				break;
			}
			case "kpi":
			{
				$initiativeIn = kpiChildIds($objectId);
				break;
			}
			case "ind":
			{
				$initiativeIn = indChildIds($objectId);
				break;
			}
		}
		$get_report = mysqli_query($connect, "SELECT initiative.id, initiative.name, initiative.sponsor, initiative.projectManager, initiative.budget, initiative.damage, initiative.startDate, initiative.dueDate, initiative.completionDate, initiative.completionStatus, initiative.deliverable, initiative.deliverableStatus, initiative.parent FROM initiative, initiativeimpact WHERE initiative.id = initiativeimpact.initiativeid AND initiativeimpact.linkedobjectid IN ($initiativeIn)");
	}
	//echo '<br>'.$initiativeIn;
	
	
	$count = 1;
	$row_count = mysqli_num_rows($get_report);
	echo "\"Initiative\":[";
	while($initRow = mysqli_fetch_array($get_report))
	{
		$data["name"] = $initRow["name"];
		//echo $row["budget"];
		if($row["sponsor"] == "true")
		{
			/*$sponsorId = $initRow["sponsor"];
			$names = mysqli_query($connect, "SELECT display_name FROM uc_users WHERE user_id = '$sponsorId'");
			$names = mysqli_fetch_assoc($names);
			if($names["display_name"] == NULL || $names["display_name"] == '') $data["sponsor"] = ''; 
			else $data["sponsor"] = $names["display_name"];  */
			$data["sponsor"] = "";
		}
		if($row["owner"] == "true")
		{
			$ownerId = $initRow["projectManager"];
			$names = mysqli_query($connect, "SELECT display_name FROM uc_users WHERE user_id = '$ownerId'");
			$names = mysqli_fetch_assoc($names);
			if($names["display_name"] == NULL || $names["display_name"] == '')$data["owner"] = '';
			else $data["owner"] = $names["display_name"];  
		}
		if($row["budget"] == "true")
		{
			if($initRow["budget"] == NULL)
			$data["budget"] = "";
			else $data["budget"] = $initRow["budget"];
		}
		if($row["cost"] == "true")
		{
			if($initRow["damage"] == NULL)
			$data["damage"] = "";
			else
			$data["damage"] = $initRow["damage"];
			
			if($data["damage"] == NULL) $data["damageColor"] = "grey";
			else if($data["damage"] > $data["budget"]) $data["damageColor"] = "red";
			else $data["damageColor"] = "green";			
		}
		if($row["start"] == "true")
		{
			$data["startDate"] = $initRow["startDate"];
		}
		if($row["due"] == "true")
		{
			$data["dueDate"] = $initRow["dueDate"];
			if($initRow["dueDate"] < date("Y-m-d")) $data["initiativeColor"] = "red";
			else if ($initRow["dueDate"] > date("Y-m-d") && $initRow["completionDate"] == NULL) $data["initiativeColor"] = "#ffd900";
			else $data["initiativeColor"] = "green";			
		}
		if($row["completed"] == "true")
		{
			if($initRow["completionDate"] == NULL)
			$data["completionDate"] = "";
			else
			$data["completionDate"] = $initRow["completionDate"];
			if($initRow["completionStatus"] == NULL)
			$data["completionStatus"] = "";
			else
			$data["completionStatus"] = $initRow["completionStatus"]."%";
		}
		if($row["deliverable"] == "true")
		{
			$data["deliverable"] = $initRow["deliverable"];
		}
		if($row["deliverableStatus"] == "true")
		{
			$deliverableId = $initRow["deliverableStatus"];
			if($initRow["deliverableStatus"] == "on")
			{
				$data["deliverableColor"] = "green";
				$data["deliverableStatus"] = "Delivered";
			}
			else if($initRow["deliverableStatus"] == "off" && $initRow["dueDate"] < date("Y-m-d"))
			{
				$data["deliverableColor"] = "red";
				$data["deliverableStatus"] = "Overdue";
			}
			else
			{
				$data["deliverableColor"] = "yellow";
				$data["deliverableStatus"] = "In Progress";
			}
		}
		if($row["parent"] == "true")
		{
			$initiativeId = $initRow["id"];
			$initiativeLink_query="SELECT objective.name
			FROM objective, initiativeimpact 
			WHERE initiativeimpact.initiativeid LIKE '$initiativeId' 
			AND objective.id = initiativeimpact.linkedobjectid";
			$initiativeLink_result=mysqli_query($connect, $initiativeLink_query) or die("Could not query initiative Links table");;
			$initRow2 = mysqli_fetch_assoc($initiativeLink_result);
			if($initRow2["name"] == NULL)
			$data["link"] = "";
			else
			$data["link"] = $initRow2["name"];
		}
		
		//$data["parent"] = $row["parent"];
		$data = json_encode($data);
		echo $data;
		$data = NULL;
		if($count < $row_count) echo ", ";
		$count++;
		
		//if ($count == 2) exit;
	}
	echo "]}";
	//echo "]";
}
?>