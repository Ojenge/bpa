<DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Organization Chart</title>
  <link rel="icon" href="images/kdic/kdic-logo-mini.png">
  <link rel="stylesheet" href="dashboards/orgChart/css/font-awesome.min.css">
  <link rel="stylesheet" href="dashboards/orgChart/css/jquery.orgchart.css">
  <link rel="stylesheet" href="dashboards/orgChart/css/style.css">
  <style type="text/css">
    .orgchart { background: #fff; }
    .orgchart td.left, .orgchart td.right, .orgchart td.top { border-color: #aaa; }
    .orgchart td.down { background-color: #F00; color: #F00; border-color: #F00;}
    
	.orgchart .green .title { background-color: #090; }
    .orgchart .green .content { border-color: #090; }
    
	.orgchart .amber .title { background-color:#FC0; }
    .orgchart .amber .content { border-color: #FC0; }
    
	.orgchart .red .title { background-color:#F00; }
    .orgchart .red .content { border-color: #F00; }
	
	.orgchart .grey .title { background-color:#A9A9A9;}
    .orgchart .grey .content { border-color: #A9A9A9; }
	
	.orgchart .second-menu-icon {
      transition: opacity .5s;
      opacity: 0;
      right: -5px;
      top: -5px;
      z-index: 2;
      color: rgba(68, 157, 68, 0.5);
      font-size: 18px;
      position: absolute;
    }
    .orgchart .second-menu-icon:hover { color: #449d44; }
    .orgchart .node:hover .second-menu-icon { opacity: 1; }
    .orgchart .node .second-menu {
      display: none;
      position: absolute;
      top: 0;
      right: -70px;
      border-radius: 35px;
      box-shadow: 0 0 10px 1px #999;
      background-color: #fff;
      z-index: 1;
    }
    .orgchart .node .second-menu .avatar {
      width: 60px;
      height: 60px;
      border-radius: 30px;
      float: left;
      margin: 5px;
    }
	
	.orgchart .assistant-node {
	  display: inline-block;
	  margin: 0;
	  padding: 3px;
	  border: 2px dashed transparent;
	  text-align: center;
	  width: 130px;
	  left: 140px;
	  top: 20px;
	  z-index: 2;
	  position: absolute;
	}
	.orgchart .assistant-node .connector {
	  border-left: rgba(217, 83, 79, 0.8) dashed 2px;
	  border-bottom: rgba(217, 83, 79, 0.8) dashed 2px;
	  position: absolute;
	  left: -75px;
	  width: 72px;
	  height: 0px;
	  top: 30px;
	}
  </style>
</head>
<body>
<div id="chart-container"></div>
<!--<script type="text/javascript" src="dashboards/orgChart/js/jquery.min.js"></script>-->
<script type="text/javascript" src="dashboards/orgChart/js/jquery.orgchart.js"></script>
<script type="text/javascript">
$(function() 
{
	$('#chart-container').orgchart({
	'data':'dashboards/orgChart/database/org-chart.php',
	'nodeContent': 'title',
	'verticalLevel': 5,
	'visibleLevel': 10,
	'nodeID': 'user_id',
	/*'direction': 'l2r',*/
	'createNode': function($node, data) 
	{
		var secondMenuIcon = $('<i>', 
		{
			  'class': 'fa fa-info-circle second-menu-icon',
			  click: function() 
			  {
				$(this).siblings('.second-menu').toggle();
			  }
		});
		var secondMenu = '<div class="second-menu"><img class="avatar" src="' + data.photo + '"></div>';
		$node.append(secondMenuIcon).append(secondMenu);
		$node.on('click', function(event) 
		{
			orgDrillDown(data.user_id);
		})
		
		if (data.title === "CEO") {
          var assistantNode =
            '<div class="assistant-node"><div class="connector"/><div class="title"><i class="fa fa-user-circle-o symbol"></i>Executive Assistant</div><div class="content">Executive Assistant</div><i class="edge verticalEdge bottomEdge fa"></i></div>';
          //$node.append(assistantNode);
        }
	}
});
/*
$('#chart-container').find('.node').on('click', function () {
  alert(JSON.stringify($(this).data('nodeData')));
});*/
});
</script>
<?php
require_once("../../admin/models/config.php");
?>
<script type="text/javascript">
require([
'dojo/dom', 
"dojo/request",
"dijit/TooltipDialog",
"dijit/popup",
"dojo/on",
'dojo/domReady!'			
], function(dom, request,TooltipDialog, popup, on)
{
orgDrillDown = function(personalId)
{
//console.log("globalDate = " + globalDate + "; period = " + period + "; personalId = " + personalId);//this test is to check if the global variables are set correctly and it works well.
request.post("individual/get-appraisal.php",{
handleAs: "json",
data: {
	//objectId: kpiGlobalId,
	objectDate: globalDate,
	objectPeriod: period,
	objectId: personalId,
	objectType: 'individual'
}						
}).then(function(data) 
{
	if (data['photo'] == undefined) dom.byId("personalPhoto").innerHTML = "<img class='rounded-3' src='../../upload/images/default.jpg' max-width='200' height='122'  />";
	else dom.byId("personalPhoto").innerHTML = "<img class='rounded-3' src='"+data['photo']+"' max-width='200' height='122' align='middle' />";
	
	var combinedData = null;
	//var cascadedScore;
	var bgColor;
	combinedData = "<table class='table table-sm table-condensed'>";
	if(data["Cascaded From Score"] == "grey")
	{
		bgColor = "#D0D0D0";
		data["Cascaded From Score"] = "No Score";
	}
	else if(data["Cascaded From Score"]<3.3){bgColor = "FF0000"}
	else if (data["Cascaded From Score"]>=3.3 && data["Cascaded From Score"]<6.7){bgColor = "#FFFF00"}
	else if (data["Cascaded From Score"]>=6.7 && data["Cascaded From Score"]<=10.0){bgColor = "#009900"}
	else bgColor = '#D0D0D0';
	
	if(data['name'] == undefined) data['name'] = 'No name';
	if(data['title'] == undefined) data['title'] = 'No title';
	if(data['department'] == undefined) data['department'] = 'No Department';
	if(data['Cascaded From Score'] == undefined) data['Cascaded From Score'] = 'No score';
	
	//Showing default appraisal date. LTK 23Aug2021 0900Hrs
	var monthsArray = [];
	monthsArray["01"] = "January";
	monthsArray["02"] = "February";
	monthsArray["03"] = "March";
	monthsArray["04"] = "April";
	monthsArray["05"] = "May";
	monthsArray["06"] = "June";
	monthsArray["07"] = "July";
	monthsArray["08"] = "August";
	monthsArray["09"] = "September";
	monthsArray["10"] = "October";
	monthsArray["11"] = "November";
	monthsArray["12"] = "December";
	var appraisalYear = globalDate.substring(0, 4);
	var appraisalMonth = globalDate.slice(-2)
	var appraisalDate = monthsArray[appraisalMonth] + " " + appraisalYear;
	//dom.byId("appraisalDate").innerHTML = appraisalDate; //Showing relevant appraisal date. LTK 23Aug2021 0833Hrs
	
	combinedData = combinedData + "<tr><th>Name:</th><td>"+data['name']+"</td><th>Position:</th><td>"+data['title']+"</td></tr><tr><th>Department:</th><td>"+data["department"]+"</td><th>Supervisor's Name:</th><td>"+data['reportsTo']+"</td></tr><tr><th style='display:none;' class='appraisalNode' colspan='2'>PF NO:</th><td style='display:none;' class='appraisalNode' colspan='2'></td></tr><tr><th style='display:none;' class='appraisalNode' colspan='2'>Appraisal Date</th><td style='display:none;' class='appraisalNode' colspan='2'><div id='appraisalDate'>"+appraisalDate+"</div></td></tr>";
	
	dom.byId('personalDetails').innerHTML = combinedData+"</table><br>";
	
	/*combinedData = null;
	combinedData = "<table>";
	combinedData = combinedData + "<tr><td>"+data["Cascaded From"]+"</td><td>"+data["Cascaded From Score"]+"</td></tr>";
	dojo.byId("cascadedIndContent").innerHTML = combinedData+"</table>";*/
	var monthsArrayShort = [];
	monthsArrayShort["01"] = "Jan";
	monthsArrayShort["02"] = "Feb";
	monthsArrayShort["03"] = "Mar";
	monthsArrayShort["04"] = "Apr";
	monthsArrayShort["05"] = "May";
	monthsArrayShort["06"] = "Jun";
	monthsArrayShort["07"] = "Jul";
	monthsArrayShort["08"] = "Aug";
	monthsArrayShort["09"] = "Sep";
	monthsArrayShort["10"] = "Oct";
	monthsArrayShort["11"] = "Nov";
	monthsArrayShort["12"] = "Dec";
	
	var halfMonth = globalDate.slice(-2)
	var halfDate = monthsArrayShort[halfMonth];
	
	combinedData = null;
	var smartCount = 1, indCount = 0; var indScore = 0;
	var smartName, textColor, bgColor, bgColorInitiative,  smartWeight, cascadedFrom, initiativeName, initiativeStatus, initiativePercentage, initiativeDue, initiativeDueRaw, initiativeStatusDetails, initiativeDeliverable, initiativeScope, initiativeHalfYear, initiativeFullYear, initPercent;
	var combinedData = '<div class="border border-primary rounded-3" style="overflow:hidden;"><table class="table table-condensed table-responsive table-bordered table-hover table-sm table-striped"><tr class="table-primary"><th colspan="10">Section 1: Key Performance Areas</th></tr><tr><th>Departmental Goal/ Result</th><th>Performance Measure</th><th>Target</th><th>Initiative</th><th class="text-nowrap">Due Date</th><th>Status</th><th>Actual Accomplishment/ Status Details</th><th style="display:none;" class="appraisalNode">Actuals(%): Half Year: Jan - '+ halfDate +'</th><th style="display:none;" class="appraisalNode">Actuals(%): Full Year: Jan - Dec</th></tr>';
	while(smartCount <= data["Measure Count"])
	{
		cascadedFrom = "Cascaded From"+smartCount;
		initiativeName = "Initiative Name"+smartCount;
		initiativeDue = "Initiative Due"+smartCount;
		initiativeDueRaw = "Initiative Due Raw"+smartCount;
		initiativeStatus = "Initiative Status"+smartCount;
		initiativePercentage = "Initiative Percentage"+smartCount;
		initiativeStatusDetails = "Initiative Status Details"+smartCount;
		initiativeDeliverable = "Initiative Deliverable"+smartCount;
		initiativeScope = "Initiative Scope"+smartCount;
		initiativeHalfYear = "halfYear"+smartCount;
		initiativeFullYear = "fullYear"+smartCount;
		
		const currentDate = new Date(globalDate);
		const dueDate = new Date(data[initiativeDueRaw]);
		
		//console.log("Global = " + d + "; Due Date = " + d2);
		
		//if(data[initiativeStatus] == "Behind Schedule") 
		if(dueDate < currentDate && Number(data[initiativePercentage]) < 100)
		{
			bgColorInitiative = "bg-danger";
			textColor = "red";
			data[initiativeStatus] = "Behind Schedule";
		}
		else if(currentDate < dueDate && Number(data[initiativePercentage]) < 100) 
		{
			bgColorInitiative = "bg-warning";
			textColor = "gold";
			data[initiativeStatus] = "On Track";
		}
		else if(data[initiativeStatus] == "Completed" || Number(data[initiativePercentage]) == 100) 
		{
			bgColorInitiative = "bg-success";
			textColor = "green";
			data[initiativeStatus] = "Completed";
		}
		else 
		{
			bgColorInitiative = "#FFFFFF";
			textColor = "#000";
		}
			
		combinedData = combinedData + "<tr><td class='fw-light'>"+data[cascadedFrom]+"</td><td>"+data[initiativeDeliverable]+"</td><td>"+data[initiativeScope]+"</td><td>"+data[initiativeName]+'</td><td>'+data[initiativeDue]+'</td><td align="center"><div class="rounded-circle trafficLightBootstrap '+bgColorInitiative+'"></div>'+data[initiativeStatus]+"</td><td>"+"<span style='color:"+textColor+"'>Completion Rate: "+data[initiativePercentage]+"%</span><br>"+data[initiativeStatusDetails]+"</td><td style='display:none;' class='appraisalNode'>"+data[initiativeHalfYear]+"</td><td style='display:none;' class='appraisalNode'>"+data[initiativeFullYear]+"</td></tr>";
			
		//initPercent = Number(data[initiativePercentage]);//This was constantly giving the year to date score as opposed to respnding to the period selected. LTK 25Aug2021 1032Hrs
		if(Number(data[initiativeHalfYear]) > 0)
		{
			initPercent = Number(data[initiativeHalfYear]);
			indScore = indScore + parseInt(initPercent);
			indCount++;
		}
		//console.log("Score = "+indScore + " initPercent = " + initPercent);
		
		smartCount++;
	}
	
	dojo.byId("measurePersonalContent").innerHTML = combinedData+"</table></div><br>";
	
	//Adding a performance measure section on the personal dashboard. LTK 16Aug2021 1259hours
	combinedData = null;
	var kpiCount = 1;
	var kpiName, kpiColor, kpiBgColor, kpiWeight, kpiTarget, kpiBaseline, kpiActual;
	var combinedData = '<div class="border border-primary rounded-3" style="overflow:hidden;"><table class="table table-condensed table-responsive table-bordered table-hover table-sm table-striped"><tr class="table-primary"><th colspan="10">Section 2: Performance Measures</th></tr><tr><th>Measure</th><th>Target</th><th>Actual</th></tr>';
	while(kpiCount <= data["coreValueCount"])
	{
		kpiName = "coreValue"+kpiCount;
		//kpiBaseline = "Initiative Due"+kpiCount;
		kpiTarget = "coreValueTarget"+kpiCount;
		kpiActual = "coreValueActual"+kpiCount;
		kpiScore = "coreValueScore"+kpiCount;
		kpiWeight = "coreValueWeight"+kpiCount;
			
		combinedData = combinedData + "<tr><td class='fw-light'>"+data[kpiName]+'</td><td>'+data[kpiTarget]+'</td><td>'+data[kpiActual]+"</td></tr>";
			
		kpiCount++;
	}
	
	dojo.byId("kpiPersonalContent").innerHTML = combinedData+"</table></div><br>";
	
	request.post("individual/get-performance-summary.php",{
	//handleAs: "json",
	data: {
		objectId: personalId,
		globalDate: globalDate
	}						
	}).then(function(performanceSummary) 
	{
		dojo.byId("personalSummary").innerHTML = performanceSummary;
	})//End of request.post("individual/get-performance-summary.php"
});

var appraisalWait = setTimeout(function()
{
	showAppraisal();
},100)

}//end of personalDashboard Function
orgDrillDown("ind1");
/*showAppraisal = function()
{
	if(dom.byId("appraisalCheck").checked == true)
	{//show appraisal form
		query(".appraisalNode").style("display", "table-cell");
		query(".signature").style("display", "table");
	}
	else 
	{//hide appraisal form
		query(".appraisalNode").style("display", "none");
		query(".signature").style("display", "none");
	}
}*/
})
</script>
<!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">-->
<br><br>

<div>

<table>
	<tr>
		<td><div id="personalDetails"></div>Personal Details</td>
		<td valign="top"><div id="personalPhoto"></div></td>
	</tr>
	<tr><td colspan="2"> <div id="measurePersonalContent"></div></td></tr>
	<tr><td colspan="2"> <div id="kpiPersonalContent"></div></td></tr>
	<tr><td colspan="2"> <div id="personalSummary"></div></td></tr>
	<tr><td colspan="2"> <div id="overallPersonalContent"></div></td></tr>
</table>
</body>
</html>