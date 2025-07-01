<?php
include_once("config/config_mysqli.php");
include_once("functions/functions.php");
include_once("functions/calendar-labels.php");
include_once("functions/perspOrg-scores.php");

function cascadeReportTwo() //LTK 07 March 2021 1933hrs
{
	global $connect;
	$childIds = orgChildIds("org1");
	$query = mysqli_query($connect, "SELECT objective.name AS name, objective.id AS id, objective.linkedObject AS linkedObject, objective.cascadedfrom AS cascadedFrom FROM objective WHERE objective.id IN ($childIds) ORDER BY linkedObject");
	
	echo "<table class='table table-condensed table-responsive table-bordered table-hover table-sm table-striped border-primary rounded'>";
	
	echo "<tr class='table-info'><th>Objective Name</th><th>Score</th><th>Organization</th><th>Cascaded From</th><th>Score</th><th>Organization Cascaded From</th></tr>";
	while($row = mysqli_fetch_array($query))
	{
		$objId = $row["id"];
		$today = date("Y-m-d");
		$objScore = objective_score($objId, $today, "table");
		
		if($objScore >= 6.67) 
		{
			$objScore = '<div class="greenLight"></div>';
		}
		else if($objScore < 6.67 && $objScore >= 3.33) 
		{
			$objScore = '<div class="yellowLight"></div>';
		}
		else if($objScore < 3.33 && $objScore > 0) 
		{
			$objScore = '<div class="redLight"></div>';
		}
		else 
		{
			$objScore = '<div class="greyLight"></div>';
		}
		
		$parentId = $row["linkedObject"];
		$switch = substr($parentId, 0, 3);
		switch($switch)
		{
			case "org":
			{
				$orgName =mysqli_query($connect, "SELECT name FROM organization WHERE id = '$parentId'");
				$orgName = mysqli_fetch_assoc($orgName);
				$orgName = $orgName["name"];
				break;	
			}
			case "per":
			{
				$orgName =mysqli_query($connect, "SELECT organization.name FROM organization, perspective WHERE perspective.id = '$parentId' AND organization.id = perspective.parentId");
				$orgName = mysqli_fetch_assoc($orgName);
				$orgName = $orgName["name"];
				break;	
			}	
		}
		$cascadedId = $row["cascadedFrom"];
		$motherObjScore = objective_score($cascadedId, $today, "months");
		if($motherObjScore >= 6.67) 
		{
			$motherObjScore = '<div class="greenLight"></div>';
		}
		else if($motherObjScore < 6.67 && $motherObjScore >= 3.33) 
		{
			$motherObjScore = '<div class="yellowLight"></div>';
		}
		else if($motherObjScore < 3.33 && $motherObjScore > 0) 
		{
			$motherObjScore = '<div class="redLight"></div>';
		}
		else 
		{
			$motherObjScore = '<div class="greyLight"></div>';
		}
		if($cascadedId == "" || $cascadedId == NULL) $motherObjScore = "";
		$cascadedObjName =mysqli_query($connect, "SELECT name, linkedObject FROM objective WHERE id = '$cascadedId'");
		$cascadedObjName = mysqli_fetch_assoc($cascadedObjName);
		$linkedObject = @$cascadedObjName["linkedObject"];
		$cascadedObjName = @$cascadedObjName["name"];
	
		if($linkedObject !== null)
		{
			$switch = substr($linkedObject, 0, 3);
			switch($switch)
			{
				case "org":
				{
					$orgCascadeName =mysqli_query($connect, "SELECT name FROM organization WHERE id = '$linkedObject'");
					$orgCascadeName = mysqli_fetch_assoc($orgCascadeName);
					$orgCascadeName = $orgCascadeName["name"];
					break;	
				}
				case "per":
				{
					$orgCascadeName =mysqli_query($connect, "SELECT organization.name FROM organization, perspective WHERE perspective.id = '$linkedObject' AND organization.id = perspective.parentId");
					$orgCascadeName = mysqli_fetch_assoc($orgCascadeName);
					$orgCascadeName = $orgCascadeName["name"];
					break;	
				}	
			}
		}
		else $orgCascadeName = "";
		
		echo "<tr><td>".$row["name"]."</td><td>".$objScore."</td><td>".$orgName."</td><td>".$cascadedObjName."</td><td>".$motherObjScore."</td><td>".$orgCascadeName."</td></tr>";
		$orgCascadeName = "";
	}
	echo "</table>";
}
cascadeReportTwo();
?>