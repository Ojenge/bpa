<?php
include('../config/config_msqli.php');
function getParentOrganization($linkedObject) //LTK 03May2021 1542 Hours
{
	switch(substr($linkedObject, 0, 3))
	{
		case "kpi":
		{//leaving this blank for now - not anticipating realistic situations where kpis will be parents of other kpis
			
			break;	
		}
		case "obj":
		{
			//Get Objective Parent
			$objParent = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT linkedObject FROM objective WHERE id = '$linkedObject'");
			$objParent = mysqli_fetch_array($objParent);
			$objParent = $objParent["linkedObject"];
			if(substr($objParent, 0, 3) == "per")//Get Perspective Parent if Objective linked to Perspective
			{ 
				$perspParent = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT parentId FROM perspective WHERE id = '$objParent'");
				$perspParent = mysqli_fetch_array($perspParent);
				$perspParent = $perspParent["parentId"];
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
	return $orgName["name"];
}
?>