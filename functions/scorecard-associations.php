<?php
include('../config/config_msqli.php');
function getParentOrganization($linkedObject) //LTK 03May2021 1542 Hours
{
	if (empty($linkedObject)) {
		return '';
	}
	switch(substr($linkedObject, 0, 3))
	{
		case "kpi":
		{//leaving this blank for now - not anticipating realistic situations where kpis will be parents of other kpis
			
			break;	
		}
		case "obj":
		{
			//Get Objective Parent
			$objParentResult = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT linkedObject FROM objective WHERE id = '$linkedObject'");
			$objParentRow = mysqli_fetch_array($objParentResult);
			$objParent = ($objParentRow && isset($objParentRow["linkedObject"])) ? $objParentRow["linkedObject"] : null;

			if ($objParent !== null && substr($objParent, 0, 3) == "per")//Get Perspective Parent if Objective linked to Perspective
			{ 
				$perspParentResult = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT parentId FROM perspective WHERE id = '$objParent'");
				$perspParentRow = mysqli_fetch_array($perspParentResult);
				$perspParent = ($perspParentRow && isset($perspParentRow["parentId"])) ? $perspParentRow["parentId"] : '';
				$orgQuery = "SELECT name FROM organization WHERE id = '$perspParent'";
			}
			else $orgQuery = "SELECT name FROM organization WHERE id = '$objParent'";
			
			break;	
		}
		case "per":
		{
			$perspParent = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT parentId FROM perspective WHERE id = '$linkedObject'");
			$perspParent = mysqli_fetch_array($perspParent);
			$perspParent = $perspParent["parentId"];
			$orgQuery = "SELECT name FROM organization WHERE id = '$perspParent'";
			break;	
		}
		case "org":
		{
			$orgQuery = "SELECT name FROM organization WHERE id = '$linkedObject'";
			break;	
		}
		case "ind":
		{
			$orgQuery = "SELECT name FROM organization WHERE id = '$linkedObject'";
			break;	
		}	
	}//End of Switch
	$orgResult = mysqli_query($GLOBALS["___mysqli_ston"], $orgQuery);
	$orgName = mysqli_fetch_assoc($orgResult);
	return $orgName && isset($orgName["name"]) ? $orgName["name"] : '';
}
?>