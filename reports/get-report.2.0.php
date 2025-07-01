<!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">-->
<?php
//header('Content-type: application/json; charset=UTF-8');
include_once("../config/config_msqli.php");
include("scores-functions.2.0.php");
//orgPerspObjKpi("org8");
//orgPerspKpi("org10");
//orgObjKpi("org9");
//orgKpi("org11");
@$orgId = $_POST['orgId'];
@$globalDate = $_POST['globalDate'];
//$orgId = "org11";
checkOrgStructure($orgId, $globalDate);
function checkOrgStructure($orgId, $globalDate)
{//Check structure of organization and call relevant function
	if(count(getPerspectives($orgId)) > 0)
	{//Organization has perspectives
		$objCount = 0;
		$perspCount = count(getPerspectives($orgId));
		$perspectives = getPerspectives($orgId);
		for($i = 0; $i < $perspCount; $i++)
		{//check if one or more of the perspectives have objectives
			$objCount = $objCount + count(getObjectives($perspectives[$i]["id"]));
		}
		if($objCount > 0)
		{//There's a perspective with objectives: assume full structure
			orgPerspObjKpi($orgId, $globalDate);
		}
		else
		{//If no objective then assume KPIs are assigned directly to the perspective
			orgPerspKpi($orgId, $globalDate);	
		}
	}
	else
	{//Organization doesn't have perspecctives
		if(count(getObjectives($orgId)) > 0)
		{//organization has objectives
			orgObjKpi($orgId, $globalDate);	
		}
		else
		{//No objectives so assumption here is that organization has at least some measures
			orgKpi($orgId, $globalDate);
		}
	}
}

function orgPerspObjKpi($orgId, $globalDate)
{
//1.0 Full Structure: Org, Persp, Obj, KPI
$fullReport = array(); $perspArray = array(); $objArray = array();
$rowSpanOrg = 0; $kpiCount = 0;

$orgName = getOrganization($orgId);
$fullReport["orgName"] = $orgName["name"];
$fullReport["orgId"] = $orgName["id"];
$perspCount = count(getPerspectives($orgId));
$rowSpanOrg = $perspCount + 1;

//Setup the details for the table structure before diplaying the contents
if($perspCount > 0)//There are perspectives to be displayed
{
	$perspectives = getPerspectives($orgId);
	for($i = 0; $i < $perspCount; $i++)
	{
		$objCount = count(getObjectives($perspectives[$i]["id"]));
		$perspArray[$i]["name"] = $perspectives[$i]["name"];
		$perspArray[$i]["id"] = $perspectives[$i]["id"];
		
		//Check whether the perspective has objectives?
		if($objCount > 0)
		{
			//add measures first before adding objectives onto the perspective array
			$objectives = getObjectives($perspectives[$i]["id"]);
		
			for($j = 0; $j < $objCount; $j++)
			{
				$objArray[$j]["name"] = $objectives[$j]["name"];
				$objArray[$j]["id"] = $objectives[$j]["id"];
				$objArray[$j]["measures"] = getMeasures($objectives[$j]["id"]);
				$measureCount = count(getMeasures($objectives[$j]["id"]));
				//echo "<br>".$objectives[$j]["id"]." = ".$measureCount;
				$objArray[$j]["kpiCount"] =  $measureCount + 1;
				$rowSpanOrg = $rowSpanOrg + $measureCount + 1;
				$kpiCount = $kpiCount + $measureCount;
			}
			$perspArray[$i]["objCount"] = $objCount + $kpiCount + 1;
			$kpiCount = 0;
			$perspArray[$i]["objectives"] = $objArray;
			$objArray = NULL;
		}
	}
	$fullReport["perspectives"] = $perspArray;
	$perspArray = NULL;
}

//echo '<div class="border border-primary rounded-3" style="overflow:hidden;">';
echo '<table class="table table-bordered table-sm table-condensed table-responsive border border-primary">';
echo "<tr><th colspan='11' class='table-primary'>Scorecard Summary</th></tr>";
echo "<tr>
		<th>Organization</th><th>Score</th>
		<th>Perspective</th><th>Score</th>
		<th>Objective</th><th>Score</th>
		<th>Measure</th><th>Actual</th><th>Score</th>
		<th>Initiatives</th>
		<th>Individuals</th>
	</tr>";
echo "<tr>
		<td rowspan='".$rowSpanOrg."'>".$fullReport["orgName"]."</td>
		<td rowspan='".$rowSpanOrg."' class='".getColor(getOrgScore($fullReport["orgId"]))."'>".getOrgScore($fullReport["orgId"])."</td>
	</tr>";

for($k=0; $k < count($fullReport["perspectives"]); $k++)
{
	echo "<tr>
			<td rowspan=".$fullReport["perspectives"][$k]["objCount"].">".
				//$fullReport["perspectives"][$k]["id"].". ".
				$fullReport["perspectives"][$k]["name"].
			"</td>
			<td rowspan=".$fullReport["perspectives"][$k]["objCount"]." class=".getColor(getPerspScore($fullReport["perspectives"][$k]["id"])).">".
				getPerspScore($fullReport["perspectives"][$k]["id"]).
			"</td>
		</tr>";
	for($l=0; $l < count($fullReport["perspectives"][$k]["objectives"]); $l++)
	{
		$objRowSpan = $fullReport["perspectives"][$k]["objectives"][$l]["kpiCount"];
		echo "<tr class='h-100'>
				<td rowspan=".$objRowSpan.">".
					//$fullReport["perspectives"][$k]["objectives"][$l]["id"]."=>".
					$fullReport["perspectives"][$k]["objectives"][$l]["name"].
				"</td>
				<td rowspan=".$fullReport["perspectives"][$k]["objectives"][$l]["kpiCount"]." class=".getColor(getObjScore($fullReport["perspectives"][$k]["objectives"][$l]["id"])).">".
					getObjScore($fullReport["perspectives"][$k]["objectives"][$l]["id"]).
				"</td></tr>";
		for($m=0; $m < count($fullReport["perspectives"][$k]["objectives"][$l]["measures"]); $m++)
		{
			echo "<tr>
					<td>".
						//$fullReport["perspectives"][$k]["objectives"][$l]["measures"][$m]["id"].". ".
						$fullReport["perspectives"][$k]["objectives"][$l]["measures"][$m]["name"].
					"</td>
					<td>".
						$fullReport["perspectives"][$k]["objectives"][$l]["measures"][$m]["actual"].
					"</td>
					<td class=".getColor(getKpiScore($fullReport["perspectives"][$k]["objectives"][$l]["measures"][$m]["id"])).">".
						getKpiScore($fullReport["perspectives"][$k]["objectives"][$l]["measures"][$m]["id"]).
					"</td>";
			if ($m == 0) 
			{
				$initiativeSpan = $fullReport["perspectives"][$k]["objectives"][$l]["kpiCount"] - 1;
				echo "<td rowspan=".$initiativeSpan.">".
						getInitiatives($fullReport["perspectives"][$k]["objectives"][$l]["id"], $globalDate).
				"</td>";
			}
			if($k == 0 && $l == 0 && $m == 0) 
			{
				$rowSpanOrg = $rowSpanOrg - 3;
				echo "<td rowspan=".$rowSpanOrg.">".
					getIndividuals($fullReport["orgId"], $globalDate).
				"</td>";
			}
			echo "</tr>";	
		}
	}
}
echo "</table>";
//echo "</div>";
}
function orgPerspKpi($orgId, $globalDate)
{
//2.0 Incomplete Structure: Org, Persp, KPI
$fullReport = array(); $perspArray = array(); $objArray = array();
$rowSpanOrg = 0; $kpiCount = 0; $indCheck = "False";

$orgName = getOrganization($orgId);
$fullReport["orgName"] = $orgName["name"];
$fullReport["orgId"] = $orgName["id"];
$perspCount = count(getPerspectives($orgId));
$rowSpanOrg = $perspCount + 1;

//Setup the details for the table structure before diplaying the contents
if($perspCount > 0)//There are perspectives to be displayed
{
	$perspectives = getPerspectives($orgId);
	for($i = 0; $i < $perspCount; $i++)
	{
		$kpiCount = count(getMeasures($perspectives[$i]["id"]));
		$perspArray[$i]["name"] = $perspectives[$i]["name"];
		$perspArray[$i]["id"] = $perspectives[$i]["id"];
		
		//Check whether the perspective has measures?
		if($kpiCount > 0)
		{
			$perspArray[$i]["kpiCount"] = $kpiCount + 1;
			$rowSpanOrg = $rowSpanOrg + $kpiCount;
			$perspArray[$i]["measures"] = getMeasures($perspectives[$i]["id"]);
		}
	}
	$fullReport["perspectives"] = $perspArray;
	$perspArray = NULL;
}

echo '<table class="table table-bordered table-sm table-condensed border-primary">';
echo "<tr><th colspan='8' class='table-primary'>Scorecard Summary</th></tr>";
echo "<tr>
		<th>Organization</th><th>Score</th>
		<th>Perspective</th><th>Score</th>
		<th>Measure</th><th>Score</th>
		<th>Initiatives</th>
		<th>Individuals</th>
	</tr>";
echo "<tr>
		<td rowspan='".$rowSpanOrg."'>".
			$fullReport["orgName"].
		"</td>
		<td rowspan='".$rowSpanOrg."' class='".getColor(getOrgScore($fullReport["orgId"]))."'>".
			getOrgScore($fullReport["orgId"]).
		"</td>
	</tr>";

for($k=0; $k < count($fullReport["perspectives"]); $k++)
{
	echo "<tr>
			<td rowspan=".$fullReport["perspectives"][$k]["kpiCount"].">".
				$fullReport["perspectives"][$k]["id"].". ".$fullReport["perspectives"][$k]["name"].
			"</td>
			<td rowspan=".$fullReport["perspectives"][$k]["kpiCount"]." class='".getColor(getPerspScore($fullReport["perspectives"][$k]["id"]))."'>".
				getPerspScore($fullReport["perspectives"][$k]["id"]).
			"</td>
		</tr>";
	for($l=0; $l < count($fullReport["perspectives"][$k]["measures"]); $l++)
	{
			echo "<tr>
				<td rowspan=".$fullReport["perspectives"][$k]["measures"][$l]["kpiCount"].">".
					$fullReport["perspectives"][$k]["measures"][$l]["id"].". ".
					$fullReport["perspectives"][$k]["measures"][$l]["name"].
				"</td>
				<td class='".getColor(getKpiScore($fullReport["perspectives"][$k]["measures"][$l]["id"]))."'>".
					getKpiScore($fullReport["perspectives"][$k]["measures"][$l]["id"]).
				"</td>
				<td>".
				getInitiatives($fullReport["perspectives"][$k]["measures"][$l]["id"], $globalDate).
				"</td>";
			if($l == 0 && $indCheck == "False") 
			{
				$indCheck = "True";
				$rowSpanOrg = $rowSpanOrg - 2;
				echo "<td rowspan=".$rowSpanOrg.">".
					getIndividuals($fullReport["orgId"], $globalDate).
				"</td>";
			}	
			echo "</tr>";
	}
}
echo "</table>";
}
function orgObjKpi($orgId, $globalDate)
{
//3.0 Incomplete Structure: Org, Obj, KPI
$fullReport = array(); $perspArray = array(); $objArray = array();
$rowSpanOrg = 0; $kpiCount = 0;

$orgName = getOrganization($orgId);
$fullReport["orgName"] = $orgName["name"];
$fullReport["orgId"] = $orgName["id"];
$objCount = count(getObjectives($orgId));
$rowSpanOrg = $objCount + 1;

//Setup the details for the table structure before diplaying the contents
if($objCount > 0)//There are objectives to be displayed
{
	$objectives = getObjectives($orgId);
	for($i = 0; $i < $objCount; $i++)
	{
		$kpiCount = count(getMeasures($objectives[$i]["id"]));
		$objArray[$i]["name"] = $objectives[$i]["name"];
		$objArray[$i]["id"] = $objectives[$i]["id"];
		
		//Check whether the objective has measures?
		if($kpiCount > 0)
		{
			$objArray[$i]["kpiCount"] = $kpiCount + 1;
			$rowSpanOrg = $rowSpanOrg + $kpiCount;
			$objArray[$i]["measures"] = getMeasures($objectives[$i]["id"]);
		}
	}
	$fullReport["objectives"] = $objArray;
	$objArray = NULL;
}

echo '<table class="table table-sm table-responsive table-condensed table-bordered border-primary">';
echo "<tr><th colspan='8' class='table-primary'>Scorecard Summary</th></tr>";
echo "<tr>
		<th>Organization</th><th>Score</th>
		<th>Objective</th><th>Score</th>
		<th>Measure</th><th>Score</th>
		<th>Initiatives</th>
		<th>Individuals</th>		
	</tr>";
echo "<tr>
		<td rowspan='".$rowSpanOrg."'>".
			$fullReport["orgName"].
		"</td>
		<td rowspan='".$rowSpanOrg."' class='".getColor(getOrgScore($fullReport["orgId"]))."'>".
			getOrgScore($fullReport["orgId"]).
		"</td>
	</tr>";
for($k=0; $k < count($fullReport["objectives"]); $k++)
{
	$objRowSpan = @$fullReport["objectives"][$k]["kpiCount"];
	echo "<tr class='h-100'>
			<td rowspan=".$objRowSpan.">".
				//$objRowSpan.". ".
				$fullReport["objectives"][$k]["name"].
			"</td>
			<td rowspan=".$objRowSpan." class='".getColor(getObjScore($fullReport["objectives"][$k]["id"]))."'>".
					getObjScore($fullReport["objectives"][$k]["id"]).
			"</td>
		</tr>";
	for($l=0; $l < count(@$fullReport["objectives"][$k]["measures"]); $l++)
	{
		echo "<tr>
				<td>"
					//.$fullReport["objectives"][$k]["measures"][$l]["id"].". "
					.$fullReport["objectives"][$k]["measures"][$l]["name"].
				"</td>
				<td class='".getColor(getKpiScore($fullReport["objectives"][$k]["measures"][$l]["id"]))."'>".
					getKpiScore($fullReport["objectives"][$k]["measures"][$l]["id"]).
				"</td>";
				$initiativeSpan = $fullReport["objectives"][$k]["kpiCount"]-1;
				if($k == 0)
				{
					echo "<td rowspan='".$initiativeSpan."' >".getInitiatives($fullReport["objectives"][$k]["id"], $globalDate)."</td>";
				}
				//else echo "<td></td>";
				
				if($k == 0 && $l == 0) 
				{
					$rowSpanOrg = $rowSpanOrg - 2;
					echo "<td rowspan='".$rowSpanOrg."'>".
					//echo "<td rowspan='1'>".
						getIndividuals($fullReport["orgId"], $globalDate).
					"</td>";
				}
				
				echo "</tr>";
	}
}
echo "</table>";
}
function orgKpi($orgId, $globalDate)
{
//4.0 Incomplete Structure: Org, KPI
$fullReport = array(); $perspArray = array(); $objArray = array();
$rowSpanOrg = 0;

$orgName = getOrganization($orgId);
$fullReport["orgName"] = $orgName["name"];
$fullReport["orgId"] = $orgName["id"];
$kpiCount = count(getMeasures($orgId)) + 1;
$rowSpanOrg = $kpiCount;

//Setup the details for the table structure before diplaying the contents
if($kpiCount > 0)//There are measures to be displayed
{
	$fullReport["measures"] = getMeasures($orgId);
}

echo '<table class="table table-bordered table-sm table-condensed border-primary">';
echo "<tr><th colspan='6' class='table-primary'>Scorecard Summary</th></tr>";
echo "<tr>
		<th>Organization</th><th>Score</th>
		<th>Measures</th><th>Score</th>
		<th>Initiatives</th>
		<th>Individuals</th>
	</tr>";
echo "<tr>
		<td rowspan='".$rowSpanOrg."'>".
			$fullReport["orgName"]."</td>
		<td rowspan='".$rowSpanOrg."' class='".getColor(getOrgScore($fullReport["orgId"]))."'>".
			getOrgScore($fullReport["orgId"]).
		"</td>
		</tr>";

for($k=0; $k < count($fullReport["measures"]); $k++)
{
	echo "<tr><td>".
	$fullReport["measures"][$k]["id"].". ".
	$fullReport["measures"][$k]["name"].
	"</td>
		<td class='".getColor(getKpiScore($fullReport["measures"][$k]["id"]))."'>".
			getKpiScore($fullReport["measures"][$k]["id"]).
		"</td>";
	
		//$initiativeSpan = $fullReport["measures"][$k]["kpiCount"];
		echo "<td>".
				getInitiatives($fullReport["measures"][$k]["id"], $globalDate).
		"</td>";
		$rowSpanOrg = $rowSpanOrg - 3;
		echo "<td rowspan=".$rowSpanOrg.">".
			getIndividuals($fullReport["orgId"], $globalDate).
		"</td>";
	echo "</tr>";
}
echo "</table>";
}
?>