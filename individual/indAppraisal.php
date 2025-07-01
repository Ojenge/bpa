<link rel="stylesheet" href="css/trafficLights.css" media="screen">
<?php
require_once("../admin/models/config.php");
$userPermission = fetchUserPermissions($loggedInUser->user_id);
?>
<!--<script src="/js/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>-->
<script src="js/scorecard.js"></script>
<script type="text/javascript">
require([
'dojo/query',
'dojo/dom', 
'dojo/dom-style',
"dojo/request",
"dojo/store/Memory",
"dojo/store/Observable",
"dijit/Dialog",
"dijit/TooltipDialog",
"dijit/popup",
"dojo/on",
"dijit/form/FilteringSelect",
"dijit/InlineEditBox",
'dojo/domReady!'			
], function(query, dom, domStyle, request, Memory, Observable, Dialog, TooltipDialog, popup, on, FilteringSelect, InlineEditBox)
{
request.post("userCalls/get-users.php",{
handleAs: "json",
data: {
}
}).then(function(userData) 
{			
	var userStore = new Memory({data:userData});
	
	var userSelect = new FilteringSelect({
	name: "userListName",
	placeHolder: "Select Staff",
	store: userStore,
	searchAttr: "User",
	maxHeight: -1, 
	onChange: function(){
		dom.byId("userIdInd").innerHTML = this.item.id;
		indPerformance();
	}
	}, "userList").startup();
	var waitABit = setTimeout(function()
	{
		indPerformance();
	},300);
});

//Get Tasks, Cascaded to, PDP and Notes
indPerformance = function()
{

if(dom.byId("appraisalCheck").checked == true) showAppraisal();

request.post("individual/get-appraisal.php",{
handleAs: "json",
data: {
	//objectId: kpiGlobalId,
	objectDate: globalDate,
	objectPeriod: period,
	objectId: dom.byId("userIdInd").innerHTML,
	objectType: 'individual'
}						
}).then(function(data) 
{
	var currentUser = dom.byId("userIdInd").innerHTML;
    var loggedInUser = dom.byId("userIdJs").innerHTML;

	//console.log("Selected Date is " + globalDate);
	if (data['photo'] == undefined) dom.byId("indPhoto").innerHTML = "<img class='rounded-3' src='../upload/images/default.jpg' max-width='200' height='122'  />";
	else dom.byId("indPhoto").innerHTML = "<img class='rounded-3' src='"+data['photo']+"' max-width='200' height='122' align='middle' />";
	
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
	
	dom.byId('indDescription').innerHTML = combinedData+"</table><br>";
	
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
	var smartName, textColor, bgColor, bgColorInitiative,  smartWeight, cascadedFrom, initiativeName, initiativeStatus, initiativePercentage, initiativeDue, initiativeDueRaw, initiativeStatusDetails, initiativeDeliverable, initiativeScope, initiativeWeight, initiativeHalfYear, initiativeFullYear, initPercent, initiativeScoreReview;
	if(data["reportsToId"] == loggedInUser)
		var combinedData = '<div class="border border-primary rounded-3" style="overflow:hidden;"><table class="table table-condensed table-responsive table-bordered table-hover table-sm table-striped"><tr class="table-primary"><th colspan="11">Section 1: Initiatives/Projects</th></tr><tr><th>Departmental Goal/ Result</th><th>Performance Measure</th><th>Target</th><th>Initiative</th><th class="text-nowrap">Due Date</th><th>Weight</th><th>Status</th><th>Actual Accomplishment/ Status Details</th><th>Edit Score</th><th style="display:none;" class="appraisalNode">Actuals(%): Half Year: Jan - '+ halfDate +'</th><th style="display:none;" class="appraisalNode">Actuals(%): Full Year: Jan - Dec</th></tr>';
	else
		var combinedData = '<div class="border border-primary rounded-3" style="overflow:hidden;"><table class="table table-condensed table-responsive table-bordered table-hover table-sm table-striped"><tr class="table-primary"><th colspan="10">Section 1: Initiatives/Projects</th></tr><tr><th>Departmental Goal/ Result</th><th>Performance Measure</th><th>Target</th><th>Initiative</th><th class="text-nowrap">Due Date</th><th>Weight</th><th>Status</th><th>Actual Accomplishment/ Status Details</th><th style="display:none;" class="appraisalNode">Actuals(%): Half Year: Jan - '+ halfDate +'</th><th style="display:none;" class="appraisalNode">Actuals(%): Full Year: Jan - Dec</th></tr>';
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
		initiativeWeight = "Initiative Weight"+smartCount;
		initiativeHalfYear = "halfYear"+smartCount;
		initiativeFullYear = "fullYear"+smartCount;
		initiativeScoreReview = "initiativeScoreReview"+smartCount;
		
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
		if(data["reportsToId"] == loggedInUser)
		combinedData = combinedData + "<tr><td class='fw-light'>"+data[cascadedFrom]+"</td><td>"+data[initiativeDeliverable]+"</td><td>"+data[initiativeScope]+"</td><td>"+data[initiativeName]+'</td><td>'+data[initiativeDue]+'</td><td>'+data[initiativeWeight]+'%</td><td class="text-nowrap" align="center"><div class="rounded-circle trafficLightBootstrap '+bgColorInitiative+'"></div>'+data[initiativeStatus]+"</td><td>"+"<span style='color:"+textColor+"'>Completion Rate: "+data[initiativePercentage]+"%</span><br>"+data[initiativeStatusDetails]+"</td><td>"+ data[initiativeScoreReview] +"</td><td style='display:none;' class='appraisalNode'>"+data[initiativeHalfYear]+"</td><td style='display:none;' class='appraisalNode'>"+data[initiativeFullYear]+"</td></tr>";
		else
		combinedData = combinedData + "<tr><td class='fw-light'>"+data[cascadedFrom]+"</td><td>"+data[initiativeDeliverable]+"</td><td>"+data[initiativeScope]+"</td><td>"+data[initiativeName]+'</td><td>'+data[initiativeDue]+'</td><td>'+data[initiativeWeight]+'%</td><td class="text-nowrap" align="center"><div class="rounded-circle trafficLightBootstrap '+bgColorInitiative+'"></div>'+data[initiativeStatus]+"</td><td>"+"<span style='color:"+textColor+"'>Completion Rate: "+data[initiativePercentage]+"%</span><br>"+data[initiativeStatusDetails]+"</td><td style='display:none;' class='appraisalNode'>"+data[initiativeHalfYear]+"</td><td style='display:none;' class='appraisalNode'>"+data[initiativeFullYear]+"</td></tr>";
		
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
	
	dojo.byId("measureIndContent").innerHTML = combinedData+"</table></div><br>";
	
	//Adding a performance measure section on the personal dashboard. LTK 16Aug2021 1259hours
	combinedData = null;
	var kpiCount = 1;
	var kpiName, kpiColor, kpiBgColor, kpiWeight, kpiTarget, kpiBaseline, kpiActual, kpiScore, kpiUpdater, kpiEvidence, kpiLastUpdate, kpiTrendLine, kpiReviewScore;
	
    if(data["reportsToId"] == loggedInUser)
        var combinedData = '<div class="border border-primary rounded-3" style="overflow:hidden;"><table class="table table-condensed table-responsive table-bordered table-hover table-sm table-striped"><tr class="table-primary"><th colspan="11">Section 2: KPIs/Performance Measures</th></tr><tr><th>Measure</th><th style="text-align:right;">Target</th><th style="text-align:right;">Actual</th><th style="text-align:right;">Weight</th><th colspan="2" style="text-align:center;">Score</th><th style="text-align:center;">Updater</th><th style="text-align:center;">Latest Value Date</th><th style="text-align:center;">Attachments</th><th style="text-align:center;">KPI Trend</th><th>Edit Score</th></tr>';
	else
        var combinedData = '<div class="border border-primary rounded-3" style="overflow:hidden;"><table class="table table-condensed table-responsive table-bordered table-hover table-sm table-striped"><tr class="table-primary"><th colspan="10">Section 2: KPIs/Performance Measures</th></tr><tr><th>Measure</th><th style="text-align:right;">Target</th><th style="text-align:right;">Actual</th><th style="text-align:right;">Weight</th><th colspan="2" style="text-align:center;">Score</th><th style="text-align:center;">Updater</th><th style="text-align:center;">Latest Value Date</th><th style="text-align:center;">Attachments</th><th style="text-align:center;">KPI Trend</th></tr>';
    var bgColorMeasure = "#FFFFFF";
	var percentSign = "";
    
	while(kpiCount <= data["coreValueCount"])
	{
		kpiName = "coreValue"+kpiCount;
		//kpiBaseline = "Initiative Due"+kpiCount;
		kpiTarget = "coreValueTarget"+kpiCount;
		kpiBaseline = "coreValueBaseline"+kpiCount;
		kpiActual = "coreValueActual"+kpiCount;
		kpiScore = "coreValueScore"+kpiCount;
		kpiWeight = "coreValueWeight"+kpiCount;
		kpiUpdater = "coreValueUpdater"+kpiCount;
		kpiEvidence = "coreValueEvidence"+kpiCount;
		kpiLastUpdate = "lastValueDate"+kpiCount;
		kpiTrendLine = "coreValueTrend"+kpiCount;
        kpiReviewScore = "coreValueScoreReview"+kpiCount;
        //kpiReportsTo = "reportsToId"+kpiCount;
        //console.log("Reports to => " + data["reportsToId"]);
        //console.log("Logged in user = " + loggedInUser + " and scorecard is for " + currentUser)
		
			if(data[kpiScore] >= 66.67)
			{
				bgColorMeasure = "bg-success";
				percentSign = "%";
			}
			else if(data[kpiScore] < 66.67 && data[kpiScore] >= 33.33)
			{
				bgColorMeasure = "bg-warning";
				percentSign = "%";
			}
			else if(data[kpiScore] < 33.33)
			{
				bgColorMeasure = "bg-danger";
				percentSign = "%";
			}

			if(data[kpiActual] == "" || data[kpiActual] == null || data[kpiScore] == "-")
			{
				bgColorMeasure = "FFFFFF";
				percentSign = "";
				data[kpiUpdater] = "-";
			}
        if(data["reportsToId"] == loggedInUser)
		combinedData = combinedData + "<tr><td class='fw-light'>"+data[kpiName]+'</td><td class="text-nowrap" style="text-align:right;">'+data[kpiTarget]+'</td><td class="text-nowrap" style="text-align:right;">'+data[kpiActual]+'</td><td class="text-nowrap" style="text-align:right;">'+data[kpiWeight]+'%</td><td style="text-align:right;">'+data[kpiScore]+percentSign+'</td><td><div class="rounded-circle trafficLightBootstrap '+bgColorMeasure+'"></div></td><td class="text-nowrap" style="text-align:center;">'+data[kpiUpdater]+'</td><td style="text-align:center;">'+data[kpiLastUpdate]+"</td><td style='text-align:center;'>"+data[kpiEvidence]+"</td><td style='text-align:center;'>"+data[kpiTrendLine]+"</td><td style='text-align:center;'>"+data[kpiReviewScore]+"</td></tr>";
		else
        combinedData = combinedData + "<tr><td class='fw-light'>"+data[kpiName]+'</td><td class="text-nowrap" style="text-align:right;">'+data[kpiTarget]+'</td><td class="text-nowrap" style="text-align:right;">'+data[kpiActual]+'</td><td class="text-nowrap" style="text-align:right;">'+data[kpiWeight]+'%</td><td style="text-align:right;">'+data[kpiScore]+percentSign+'</td><td><div class="rounded-circle trafficLightBootstrap '+bgColorMeasure+'"></div></td><td class="text-nowrap" style="text-align:center;">'+data[kpiUpdater]+'</td><td style="text-align:center;">'+data[kpiLastUpdate]+"</td><td style='text-align:center;'>"+data[kpiEvidence]+"</td><td style='text-align:center;'>"+data[kpiTrendLine]+"</td></tr>";
		
		kpiCount++;
	}
	
	dojo.byId("kpiContent").innerHTML = combinedData+"</table></div><br>";
	
	request.post("individual/get-performance-summary.php",{
	//handleAs: "json",
	data: {
		objectId: dom.byId("userIdInd").innerHTML,
		globalDate: globalDate
	}						
	}).then(function(performanceSummary) 
	{
		dojo.byId("performanceSummary").innerHTML = performanceSummary;
	})//End of request.post("individual/get-performance-summary.php"
});

var appraisalWait = setTimeout(function()
{
	showAppraisal();
},100)

}//End of main function that gets the individual dashboard.

showAppraisal = function()
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
}

scoreReview = function(measureId, objectId, measureName)
{
    dom.byId("reviewMeasureName").innerHTML = measureName;
    dom.byId("selectedElement").innerHTML = objectId;
    dom.byId("editSaveDelete").innerHTML = measureId;
	dijit.byId("scoreEditDialog").show();
}

saveReviewedScore = function()
{
    dijit.byId("scoreEditDialog").hide();
    request.post("individual/save-reviewed-score.php",{
	//handleAs: "json",
	data: {
		objectId: dom.byId("selectedElement").innerHTML,
		globalDate: globalDate,
		measureId: dom.byId("editSaveDelete").innerHTML,
		measureScore: dom.byId("reviewMeasureScore").value
	}						
	}).then(function() 
	{
		//dojo.byId("performanceSummary").innerHTML = performanceSummary;
	})//End of request.post("individual/save-reviseds-score.php"
}

cancelReviewedScore = function()
{
    dijit.byId("scoreEditDialog").hide();
}

});
</script>
<!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">-->

</head>
<body>
<div class="d-print-none" style="position:absolute; right:15px; top:0px;">
     <label class="form-check-label" for="appraisalCheck">Appraisal Format:</label>
     <input id="appraisalCheck" class="form-check-input" type="checkbox" onClick="showAppraisal()"/>
</div>
<div class="d-print-none" style="position:absolute; right:15px; top:30px;"><input style='width:90%' id='userList'/></div>
<table class="">
<tr>
	<td width="1%" valign="top"><div id="indPhoto"></div></td>
	<td width="49%" valign="top"><div id="indDescription"></div></td>
    <td width=""></td>
</tr>
<tr><td colspan="3"> <div id="measureIndContent"></div></td></tr>
<tr><td colspan="3"> <div id="kpiContent"></div></td></tr>
<tr><td colspan="3"> <div id="performanceSummary"></div></td></tr>
<tr><td colspan="3"> <div id="overallContent"></div></td></tr>
</table>

<div class="dijitHidden">
	<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'Edit Score'" id="scoreEditDialog" style="font-size:11px;">
        <table>
        <tr>
            <td width="12%" valign="top"><strong>Name</strong></td>
            <td width="24%"><div id="reviewMeasureName"></div></td>
        </tr>
		<tr>
            <td width="12%" valign="top"><strong>Reviewed Score</strong></td>
            <td width="24%"><input type='text' style='width:90%' id="reviewMeasureScore"/></td>
        </tr>
		</table>
        <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:cancelReviewedScore" type="submit">Cancel</button>
        <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:saveReviewedScore" type="submit">Save Score</button>
    </div>
</div>

<button type="button" id="scrollIntoView"  class="noPrint" style="display:compact; width:0px; height:0px; margin:0px;" ></button>
</body>
</html>