<?php
include_once("../config/config_mysqli.php");

$reportName = $_POST['reportName'];
$selectedObjects = $_POST["selectedObjects"];
$reportType = $_POST["reportType"];
$linkedTo = $_POST['linkedTo'];
switch($reportType)
{
	case "cascadeReport":
	{
		mysqli_query($connect, "INSERT INTO report (reportName, selectedObjects, linkedTo, Type) VALUES ('$reportName', '$selectedObjects', '$linkedTo', '$reportType')");
		break;
	}
	case "customReport":
	{
		//$displayColumnsId = $_POST["displayColumnsId"]; 
		$displayColumnsKpi = $_POST["displayColumnsKpi"]; 
		$displayColumnsOrg = $_POST["displayColumnsOrg"];
		$displayColumnsOrgScore = $_POST["displayColumnsOrgScore"];
		$displayColumnsPersp = $_POST["displayColumnsPersp"];
		$displayColumnsPerspScore = $_POST["displayColumnsPerspScore"];
		$displayColumnsObj = $_POST["displayColumnsObj"];
		$displayColumnsObjScore = $_POST["displayColumnsObjScore"];
		$displayColumnsOwner = $_POST["displayColumnsOwner"]; 
		$displayColumnsUpdater = $_POST["displayColumnsUpdater"]; 
		$displayColumnsScore = $_POST["displayColumnsScore"]; 
		$displayColumnsActual = $_POST["displayColumnsActual"]; 
		$displayColumnsTarget = $_POST["displayColumnsTarget"]; 
		$displayColumnsVariance = $_POST["displayColumnsVariance"]; 
		$displayColumnsPercentVariance = $_POST["displayColumnsPercentVariance"];
		$redFilter = $_POST['displayRedFilter'];
		$greyFilter = $_POST['displayGreyFilter'];
		$greenFilter = $_POST['displayGreenFilter'];
		$displayInitiativeGroup = $_POST['displayInitiativeGroup'];
		$displayInitiativeFilter = $_POST['displayInitiativeFilter'];
		
		//file_put_contents('test.txt',$reportName." - ".$displayColumnsKpi." - ".$displayColumnsOwner." - ".$displayColumnsVariance);
		
		mysqli_query($connect, "INSERT INTO report (reportName, selectedObjects, Measure, Organization, orgScore, Perspective, perspScore, Objective, objScore,Owner, Updater, Score, Actual, Green, Variance, PercentVariance, linkedTo, Type, redFilter, greyFilter, greenFilter, initiativeFilter, initiativeGroup) 
		VALUES ('$reportName', '$selectedObjects', '$displayColumnsKpi', '$displayColumnsOrg', '$displayColumnsOrgScore', '$displayColumnsPersp', '$displayColumnsPerspScore', '$displayColumnsObj', '$displayColumnsObjScore', '$displayColumnsOwner', '$displayColumnsUpdater', '$displayColumnsScore', '$displayColumnsActual', '$displayColumnsTarget', '$displayColumnsVariance', '$displayColumnsPercentVariance', '$linkedTo', '$reportType', '$redFilter', '$greyFilter', '$greenFilter', '$displayInitiativeFilter', '$displayInitiativeGroup')");
		break;	
	}
	case "initiativeReport":
	{
		mysqli_query($connect, "INSERT INTO report (reportName, selectedObjects, linkedTo, Type) VALUES ('$reportName', '$selectedObjects', '$linkedTo', '$reportType')");
		
		$id = mysqli_query($connect, "SELECT MAX(id) AS id FROM report");
		$id = mysqli_fetch_assoc($id);
		$id = $id['id'];
		$displayInitSponsor = $_POST['displayInitSponsor'];
		$displayInitOwner = $_POST['displayInitOwner'];
		$displayInitBudget = $_POST['displayInitBudget'];
		$displayInitCost = $_POST['displayInitCost'];
		$displayInitStart = $_POST['displayInitStart'];
		$displayInitDue = $_POST['displayInitDue'];
		$displayInitComplete = $_POST['displayInitComplete'];
		$displayInitDeliverable = $_POST['displayInitDeliverable'];
		$displayInitDeliverableStatus = $_POST['displayInitDeliverableStatus'];
		$displayInitParent = $_POST['displayInitParent'];
		$displayInitRedFilter = $_POST['displayInitRedFilter'];
		$displayInitGreyFilter = $_POST['displayInitGreyFilter'];
		$displayInitGreenFilter = $_POST['displayInitGreenFilter'];
		
		$redFilter = $_POST['displayInitRedFilter'];
		$greyFilter = $_POST['displayInitGreyFilter'];
		$greenFilter = $_POST['displayInitGreenFilter'];
		
		mysqli_query($connect, "INSERT INTO report_init (id, sponsor, owner, budget, cost, start, due, completed, deliverable, deliverableStatus, parent, red, yellow, green) 
		VALUES ('$id','$displayInitSponsor', '$displayInitOwner', '$displayInitBudget', '$displayInitCost', '$displayInitStart', '$displayInitDue', '$displayInitComplete', '$displayInitDeliverable', '$displayInitDeliverableStatus', '$displayInitParent', '$displayInitRedFilter', '$displayInitGreyFilter', '$displayInitGreenFilter')");
		break;	
	}
}
?>