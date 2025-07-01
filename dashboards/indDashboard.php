<?php
require_once("../admin/models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
require_once("../admin/models/header.php");
$userPermission = fetchUserPermissions($loggedInUser->user_id);
?>
<script type="text/javascript">
require([
'dojo/dom', 
'dojo/dom-style',
"dojo/request",
"dojo/store/Memory",
"dojo/store/Observable",
"dijit/Tooltip",
"dijit/TooltipDialog",
"dijit/popup",
"dijit/InlineEditBox",
'dojo/domReady!'			
], function(dom, domStyle, request, Memory, Observable, Tootltip, TooltipDialog, popup, InlineEditBox)
{
indGauge = new Highcharts.Chart
({
	chart: 
	{
		renderTo: 'myIndGauge',
		type: 'gauge',
		height: 180,
		width: 180,
		margin: [0,0,0,0],
		spacingBottom: 0,
		background: null
	},
	title: {
		text: null
	},
	pane: 
	{
//	        center: ['50%', '100%'],
		startAngle: -90,
		endAngle: 90,
		background: null
	}, 
	plotOptions: {
		gauge: {
			 dataLabels: {
				enabled: true
			},
			dial: {
				baseWidth: 5,
				baseLength: '80%',
				radius: '100%',
				rearLength: 0
			}
		}
	},
// the value axis
	yAxis: 
	{
		min: 0,
		max: 10,
		tickLength: 0,
		tickColor: '#FF0000',
		lineColor: 'transparent',
		tickPosition: 'outside',
		minorTickPosition: 'outside',
		tickLength: 0,
		minorTickLength: 0,
		offset: 0,
		distance: 12,
		//categories: ['good', 'bad', 'ugly', 'one'],
		tickPositions: [0, 2, 4, 6, 8, 10],
		labels: 
		{
			enabled: false
			//step: 1,
			//rotation: 'auto',
			//x: 30, 
			//y: -30,
			//formatter: function() 
			//{
			//	return this.axis.categories[this.value - 0.5];
			//}
		},
		plotBands: 
		[{
			from: 0,
			to: 3.33,
			outerRadius: '100%',
			innerRadius: '1%',
			color: '#ff0000', // red
			id:'red'
		}, {
			from: 3.33,
			to: 6.67,
			outerRadius: '100%',
			innerRadius: '1%',
			color: '#FFD900', // yellow
			id: 'yellow'
		},{
			from: 6.67,
			to: 10,
			outerRadius: '100%',
			innerRadius: '1%',
			color: '#33CC00', // green
			id: 'green'
		}]        
	},
	series: 
	[{
		score: 'Score',
		data: [1.5]
	}],
	credits: {enabled: false}
});
indGauge.renderer.image('../images/gaugeOverlay.png', 0, 0, 177, 99).add().attr({zIndex:100});
request.post("../scorecards/get-ind-gauge.php",
{
	handleAs: "json",
	data: {
		objectId: dom.byId("userIdInd").innerHTML,
		objectType: "individual",
		objectPeriod: period,
		objectDate: globalDate
}						
}).then(function(indGaugeScore) 
{
	if(indGaugeScore == '' || indGaugeScore == null)
	{
		indGauge.series[0].points[0].update(null);
		indGauge.series[0].options.dial.radius = 0;
		indGauge.series[0].isDirty = true;
		indGauge.redraw();
	}
	else
	{
		indGauge.series[0].options.dial.radius = '100%';
		indGauge.series[0].isDirty = true;
		
		var score = parseFloat(indGaugeScore);
		score = Math.round(score * 100) / 100;
		indGauge.series[0].points[0].update(score);
		indGauge.redraw();
		console.log("indGauge score => "+score);
		var gaugeWait = setTimeout(function(){
		indGauge.series[0].points[0].update(score);
		indGauge.redraw();
		},300);
	}
});

if(dijit.byId("indInterpretation")){
dijit.byId("indInterpretation").destroy(true);}
var indEditor = new InlineEditBox(
{
	renderAsHtml: true,
	autoSave: false,
	disabled:true,
}, "indInterpretation");

if(dijit.byId("indWayForward")){
dijit.byId("indWayForward").destroy(true);}
var indEditor2 = new InlineEditBox(
{
	renderAsHtml: true,
	autoSave: false,
	disabled:true,
}, "indWayForward");


//***************************************************************************************************###
//The chart	

request.post("../scorecards/get-ind-scores.php",
{
	handleAs: "json",
	data: {
			objectId: dom.byId("userIdInd").innerHTML,
			objectDate: globalDate,
			objectPeriod: period,			
			objectType: 'individual',
			valuesCount: valuesCount
		}						
}).then(function(indData) 
{			//	alert("errrr "+ JSON.stringify(indData));
	chart = new Highcharts.Chart({
	chart: {
		renderTo: 'indChart'
	},
	title: {
		text: null
	},
	subtitle: {
		text: null
	},
	xAxis: {
		//type: 'datetime',
		min: 0.5, 
		max: 10.5
	},
	yAxis: {
		gridLineColor: 'transparent',
		min:0,
		max:10,
		title: {
			text: null
		},//var chart = $('#container').highcharts(); chart.xAxis[0].removePlotBand('plotband-1');
		plotBands: [{
			color: 'red', // Color value
			from: 0, // Start of the plot band
			to: 3.33, // End of the plot band
			id: 'plotband-1'
		  },
		  {
			color: 'yellow', // Color value
			from: 3.33, // Start of the plot band
			to: 6.67, // End of the plot band
			id: 'plotband-2'
		  },
		  {
			color: 'green', // Color value
			from: 6.67, // Start of the plot band
			to: 10, // End of the plot band
			id: 'plotband-3'
		  }]
	},
	tooltip: {
		formatter: function() {
			var point = this.point,
				s = '<b>' + this.series.name + '</b><br>' + this.x +': <b>'+ this.y +'</b><br/>';
			return s;
		},
		crosshairs: true
		//shared: true
	},
	series: [
	{//0
		name: 'Score',
		type: 'spline',
		color: 'blue',
		//data: indScore,
		shadow : true,
		//lineWidth: 5,
		zIndex: 5
	},
	{//1
		name: 'Red',
		type: 'area',
		color: 'red',
		lineWidth: 0,
		marker: {
		   enabled: false
		},
		enableMouseTracking: false,
		zIndex: 4
	},
	{//2
		name: 'Yellow',
		type: 'area',
		color: 'yellow',
		lineWidth: 0,
		marker: {
		   enabled: false
		},
		enableMouseTracking: false,
		zIndex: 3
	},
	{//3
		name: 'Green',
		type: 'area',
		color: 'green',
		lineWidth: 0,
		marker: {
		   enabled: false
		},
		enableMouseTracking: false,
		zIndex: 2
	},
	{//4
		name: 'Dark Green',
		type: 'area',
		color: 'DarkGreen',
		lineWidth: 0,
		marker: {
		   enabled: false
		},
		enableMouseTracking: false,
		zIndex: 1
	},
	{//5
		name: 'centralLine',
		type: 'line',
		color: 'green',
		//fillOpacity: 0.3,
		zIndex: 1
	},
	{//6
		name: 'XmR',
		//data: ranges,
		type: 'arearange',
		lineWidth: 0,
		linkedTo: ':previous',
		color: '#00FF66',
		//color: Highcharts.getOptions().colors[0],
		//fillOpacity: 0.2,
		zIndex: 0
	}],
	exporting: {
		enabled: false
	},
	legend: {
		enabled: false,
			align: 'left',
			layout: 'vertical',
			x: 60,
			//verticalAlign: 'top',
			y: -180,
			floating: true,
			backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
			borderColor: '#CCC',
			borderWidth: 1,
			shadow: true,
			padding: 10,
			labelFormatter: function() {
				  //var total = 0;
				  //for(var i=this.yData.length; i--;) { total += this.yData[i]; };
				  //return this.name + ' (Total: ' + total+')';
				  //return this.name;
			   }
		},
		credits: {
			  enabled: false
		  },
		  plotOptions: {
				spline: {
					dataLabels: {
						enabled: true
					},
				series: {
					shadow: true
				}
			}
		}
	});// end of chart definitions
	
	var categories = [], indScore = [], scoreCount = 0, nullCount = 0;
	while(scoreCount < indData.length)
	{
		categories[scoreCount] = indData[scoreCount].date
		if(indData[scoreCount].score == null)
		{
			indScore[scoreCount] = null;
			nullCount++;
		}
		else
		indScore[scoreCount] = parseInt(indData[scoreCount].score,10);
		categories[scoreCount] = indData[scoreCount].date;
		scoreCount++;	
	}
	if(nullCount == indData.length)
	{
		domStyle.set(dom.byId("divIndChart"), "display", "none");
		domStyle.set(dom.byId("indChart"), "display", 'none');
	}
	else
	{
		domStyle.set(dom.byId("divIndChart"), "display", "block");
		domStyle.set(dom.byId("indChart"), "display", 'block');
		chart.yAxis[0].plotLinesAndBands[0].svgElem.show();
		chart.yAxis[0].plotLinesAndBands[1].svgElem.show();
		chart.yAxis[0].plotLinesAndBands[2].svgElem.show();
		chart.xAxis[0].setCategories(categories, false);
		chart.series[1].hide();
		chart.series[2].hide();
		chart.series[3].hide();
		chart.series[4].hide();
		chart.series[5].hide();
		chart.yAxis[0].setExtremes(0,10);
		chart.xAxis[0].setExtremes(0.5,10.5);
		chart.series[0].setData(indScore, true);
	}
});	// end of then part of get-ind-scores.php

//Get Tasks, Cascaded to, PDP and Notes
request.post("../scorecards/get-content.php",{
handleAs: "json",
data: {
	//objectId: kpiGlobalId,
	objectDate: globalDate,
	objectPeriod: period,
	objectId: dom.byId("userIdInd").innerHTML,
	objectType: 'individual'
}						
}).then(function(data) {
	
	if(data["interpretation"] == null) dijit.byId("indInterpretation").set("value", '');
	else dijit.byId("indInterpretation").set("value", data["interpretation"]);
	if (data["wayForward"] == null) dijit.byId("indWayForward").set("value", '');
	else dijit.byId("indWayForward").set("value", data["wayForward"]);
	if (data['photo'] == undefined) dom.byId("indPhoto").innerHTML = "<img src='../upload/images/default.jpg' max-width='200' height='122' align='middle' />";
	else dom.byId("indPhoto").innerHTML = "<img src='"+data['photo']+"' max-width='200' height='122' align='middle' />";
	
	var combinedData = null;
	//var cascadedScore;
	var bgColor;
	combinedData = "<table style='font-size: 13px; border-collapse: collapse;'>";
	if(data["Cascaded From Score"] == "grey")
	{
		bgColor = "#D0D0D0";
		data["Cascaded From Score"] = "No Score";
	}
	else if(data["Cascaded From Score"]<=3.3){bgColor = "FF0000"}
	else if (data["Cascaded From Score"]>3.3 && data["Cascaded From Score"]<=6.7){bgColor = "#FFFF00"}
	else if (data["Cascaded From Score"]>6.7 && data["Cascaded From Score"]<=10.0){bgColor = "#009900"}
	else bgColor = '#D0D0D0';
	
	if(data['name'] == undefined) data['name'] = 'No name';
	if(data['title'] == undefined) data['title'] = 'No title';
	if(data['Cascaded From'] == undefined) data['Cascaded From'] = 'No department';
	if(data['Cascaded From Score'] == undefined) data['Cascaded From Score'] = 'No score';
	
	combinedData = combinedData + "<tr><td style='padding:6px;'><strong>Name:</strong></td><td style='padding:6px;' colspan='2'>"+data['name']+"</td></tr><tr><td style='padding:6px;'><strong>Title/Position:</strong></td><td style='padding:6px;' colspan='2'>"+data['title']+"</td></tr><tr><td style='padding:6px;'><strong>Department:</strong></td><td style='padding:6px;' colspan='2'>"+data["Cascaded From"]+"</td></tr><tr><td style='padding:6px;'><strong>Department's Score:</strong></td><td align='centre'><table><tr><td><div style='width: 25px;height: 25px;-webkit-border-radius: 25px;-moz-border-radius: 25px;border-radius: 25px;background-color:"+bgColor+";'></div></td><td><div>"+data["Cascaded From Score"]+"</div></td></tr></table></td></tr>";
	dom.byId('indDescription').innerHTML = combinedData+"</table>";
	
	combinedData = null;
	combinedData = "<table style='font-size: 13px; border-collapse: collapse; border-top: 1px solid #9baff1; border-bottom: 1px solid #9baff1;'>";
	combinedData = combinedData + "<tr><td style='padding: 6px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'>"+data["Cascaded From"]+"</td><td bgcolor='"+bgColor+"' style='padding: 6px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'>"+data["Cascaded From Score"]+"</td></tr>";
	dojo.byId("cascadedIndContent").innerHTML = combinedData+"</table>";

	/*
	combinedData = null;
	var smartCount = 1;
	var smartNumber, bgColor, smartScore;
	var combinedData = "<table style='font-size: 13px; border-collapse: collapse; border-top: 1px solid #9baff1; border-bottom: 1px solid #9baff1;'>";
	while(smartCount <= data["Measure Count"])
	{
		smartNumber = "Measure"+smartCount;
		smartScore = "Measure Score"+smartCount;
		if(data[smartScore]<3.3){bgColor = "red"} else if (data[smartScore]>=3.3 || data[smartScore]<6.7){bgColor = "#FFFF00"} else{bgColor = "#009900"}
		combinedData = combinedData + "<tr><td style='padding: 6px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'>"+data[smartNumber]+"</td><td bgcolor='"+bgColor+"'  style='padding: 6px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'>"+smartNumber+"</td></tr>";
		smartCount++;
	}*/
	//dojo.byId("measureIndContent").innerHTML = combinedData+"</table>";
	
	combinedData = null;
	var taskCount = 1;
	var deliverable, bgColor, objectiveImpacted, taskNumber, taskId, dueDate;
	var combinedData = "<table style='font-size: 13px; border-collapse: collapse; border-top: 1px solid #9baff1; border-bottom: 1px solid #9baff1;'><tr><td style='padding: 2px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'><strong>"+'Activity/Task'+"</strong></td><td style='padding: 2px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'><strong>"+'Deliverable'+"</strong></td><td style='padding: 2px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'><strong>"+'Objective(s) Impacted'+"</strong></td><td style='padding: 2px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe; white-space:nowrap;'><strong>"+'Due Date'+"</strong></td></tr>";
	while(taskCount <= data["Initiative Count"])
	{
		objectiveImpacted = "Objective"+taskCount;
		taskNumber = "Initiative"+taskCount;
		taskId = "InitiativeId"+taskCount;
		deliverable = "Deliverable"+taskCount;
		dueDate = "dueDate"+taskCount;
		//alert(data["dueDate1"]);
		bgColor = "Color"+taskCount;
		combinedData = combinedData + "<tr><td style='padding: 4px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe; cursor: pointer; text-decoration: underline; color: blue;' id='init"+data[taskId]+"' onClick='moreDetailsTask("+data[taskId]+")' onMouseOut='removeTooltip()'>"+data[taskNumber]+"</td><td style='padding: 4px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'>"+data[deliverable]+"</td><td style='padding: 4px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'>"+data[objectiveImpacted]+"</td><td bgcolor='"+data[bgColor]+"' style='padding: 4px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'>"+data[dueDate]+"</td></tr>";
		taskCount++;
	}
	if(data["Initiative Count"] == 0) dojo.byId("initiativeIndContent").innerHTML = "No assigned tasks";
	else dojo.byId("initiativeIndContent").innerHTML = combinedData+"</table>";
	
	combinedData = null;
	var pdpCount = 1;
	var skillGap, intervention, startDate, dueDate, resource, comments;
	combinedData = "<table style='font-size: 13px; border-collapse: collapse; border-top: 1px solid #9baff1; border-bottom: 1px solid #9baff1;'><tr><td style='text-align:center; padding: 2px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'><strong>"+'Competency/Skill Gap'+"</strong></td><td style='text-align:center; padding: 2px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'><strong>"+'Intevention'+"</strong></td><td style='text-align:center; padding: 2px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'><strong>"+'Start Date'+"</strong></td><td style='text-align:center; padding: 2px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe; white-space:nowrap;'><strong>"+'Due Date'+"</strong></td><td style='text-align:center; padding: 2px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'><strong>"+'Resource'+"</strong></td><td style='text-align:center; padding: 2px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'><strong>"+'Comments'+"</strong></td></tr>";
	while(pdpCount <= data["pdpCount"])
	{
	skillGap = "skillGap"+pdpCount;
	intervention = "intervention"+pdpCount;
	startDate = "pdpStartDate"+pdpCount;
	dueDate = "pdpDueDate"+pdpCount;
	resource = "resource"+pdpCount;
	comments = "comments"+pdpCount;
	bgColor = "pdpColor"+pdpCount;
	combinedData = combinedData + "<tr><td style='padding: 6px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'>"+data[skillGap]+"</td><td style='padding: 6px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'>"+data[intervention]+"</td><td style='padding: 6px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'>"+data[startDate]+"</td><td  bgcolor='"+data[bgColor]+"' style='padding: 6px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'>"+data[dueDate]+"</td><td style='padding: 6px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'>"+data[resource]+"</td><td style='padding: 6px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'>"+data[comments]+"</td></tr>";
	pdpCount++;
	}
	if(data["pdpCount"] == 0)
	dojo.byId("IndPdp").innerHTML = "No personal development plan";
	else
	dojo.byId("IndPdp").innerHTML = combinedData+"</table>";
	
	moreDetailsTask = function(id)
	{
		getInitContentInd(id);
	}
	removeTooltip = function()
	{
		popup.close(objTooltipDialog);
	}
});

getInitContentInd = function(id)
{
	var initId = "init"+id;
	request.post("../initiatives/get-initiative.php",{
	// The URL of the request
	handleAs: "json",
	data: {
		initiativeId: id
	}						
	}).then(function(initiativeData)
	{
		//if(initiativeData["parent"] == null) initiativeData["parent"] = '';
		var initIndContent = "<table class='initTable'><tr style='background-color:#9baff1'><td>Name</td><td>"+initiativeData["name"] +"</td><td align='right'>Impacts:</td><td>"+ initiativeData["link"] +"</td></tr><tr><td>Sponsor</td><td>"+ initiativeData["sponsor"] +"</td><td align='right'>Owner:</td><td>"+ initiativeData["manager"] +"</td></tr><tr style='background-color:#9baff1'><td>Parent</td><td>"+initiativeData["parent"]+"</td><td align='right'>Completion Date:</td><td>"+ initiativeData["completionDate"] +"</td></tr><tr><td>Start Date</td><td>"+ initiativeData["startDate"] +"</td><td align='right'>Due Date:</td><td>"+ initiativeData["dueDate"] + "</td></tr><tr style='background-color:#9baff1'><td>Budget</td><td>"+ initiativeData["budget"] +"</td><td align='right' style='border-bottom: 1px solid #9baff1;'>Cost So Far:</td><td style='border-bottom: 1px solid #9baff1;'>"+ initiativeData["damage"] +"</td></tr><tr><td>Deliverable</td><td>"+ initiativeData["deliverable"] +"</td></tr></table>";
		
	objTooltipDialog.set("content", initIndContent);
	popup.open({
			popup: objTooltipDialog,
			around: dom.byId(initId),
			orient: ["above"]
		});
	});	
}

});
</script>
<style>
	@import "../css/pageLayoutInd.css";
</style>
</head>
<body>
<div id="userIdInd" style="display:none;"><?php echo "ind".$loggedInUser->user_id; ?></div>
<div>

<div id="divIndGroup1">
   <!-- <div id="divIndName">
        <div data-dojo-id="indNameTitle" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Name'">
            <div id="individualName"></div>
         </div>
    </div>
    
    *********************************************************************************
    Gauge/Speedometer
    *********************************************************************************
    -->
    
    <div style="clear:left"></div>
    
    <div id="divIndGauge" style="min-width:200px;">
        <div data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Current Score'" style="min-width:210px !important; overflow:hidden !important;">
        <table align="center"><tr>
        <td>
        	<div id="divIndWeight" style="float:right; vertical-align:central; 
            font-size:11px; font-style:italic; margin-right:22px; text-align:center"></div>
            <div id="myIndGauge" style="width:180px; height:130px; overflow:hidden;" align="center"></div>
        </td>
        </tr></table>
        </div>
    </div>
    
    
 
 </div>
 <!-- End of divIndGroup1-->

<!-- Description and Owner-->
<div id="divIndGroup2">
        <div id="divIndDescription">
        <div id="divIndPhoto">
            <div data-dojo-type="dijit/TitlePane" 
            data-dojo-props="title:'Staff Photo'" style="float:right; width:200px;margin-top:0px;margin-left:3px;">
                <div id="indPhoto" align="center"></div>
            </div>
        </div>
            <div data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Staff Details'" style="min-width:300px;">
                <div id="indDescription"></div>
            </div>
        </div>
    </div>
    <!-- End of Description and Owner-->
<!--
*********************************************************************************
Chart and Legend
*********************************************************************************
-->
<div id="divIndChart">
    <div id="indChart" style="height:300px; width:950px;"></div> 
</div>
<!--
*********************************************************************************
Initiatives
*********************************************************************************
-->
<div style="clear:left"></div>
<div id="divIndInitiatives" align='left'>
<div data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Assigned Tasks'">
 <div id="initiativeIndContent"></div></div>
</div>

<div id="divIndCascadedTo">
    <div data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Cascaded From'"> <!-- This was initially meant to be Cascaded To with the assumption that individuals could cascaded to others but to keep things simple, assuming now individuals will cascade from Departments -->
     <div id="cascadedIndContent"></div>
	</div>
</div>
<div style="clear:left"></div>

<div id="divIndDevelopmentPlan">
    <div data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Personal Development Plan'">
    <div id="IndPdp" style="width:700px"></div>
</div>
</div>

<div style="clear:left"></div>

<!-- Analytics Dashboard Navigation -->
<div id="divAnalyticsDashboards" style="margin-top: 20px;">
    <div data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Analytics Dashboards'">
        <div style="padding: 15px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">

                <div class="dashboard-nav-card" onclick="window.open('index.php', '_blank')" style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; cursor: pointer; background: #f8f9fa; transition: all 0.3s;">
                    <div style="color: #007bff; font-size: 24px; margin-bottom: 8px;">📊</div>
                    <h6 style="margin: 0 0 5px 0; color: #333;">Analytics Overview</h6>
                    <small style="color: #666;">Main dashboard selection and overview</small>
                </div>

                <div class="dashboard-nav-card" onclick="window.open('department-performance-dashboard.php', '_blank')" style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; cursor: pointer; background: #f8f9fa; transition: all 0.3s;">
                    <div style="color: #28a745; font-size: 24px; margin-bottom: 8px;">🏢</div>
                    <h6 style="margin: 0 0 5px 0; color: #333;">Department Performance</h6>
                    <small style="color: #666;">Department analytics and performance metrics</small>
                </div>

                <div class="dashboard-nav-card" onclick="window.open('executive-summary.php', '_blank')" style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; cursor: pointer; background: #f8f9fa; transition: all 0.3s;">
                    <div style="color: #17a2b8; font-size: 24px; margin-bottom: 8px;">👥</div>
                    <h6 style="margin: 0 0 5px 0; color: #333;">Executive Summary</h6>
                    <small style="color: #666;">High-level organizational overview</small>
                </div>

                <div class="dashboard-nav-card" onclick="window.open('team-productivity-analytics.php', '_blank')" style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; cursor: pointer; background: #f8f9fa; transition: all 0.3s;">
                    <div style="color: #ffc107; font-size: 24px; margin-bottom: 8px;">📈</div>
                    <h6 style="margin: 0 0 5px 0; color: #333;">Team Productivity</h6>
                    <small style="color: #666;">Team productivity analytics and insights</small>
                </div>

                <div class="dashboard-nav-card" onclick="window.open('goal-achievement-tracking.php', '_blank')" style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; cursor: pointer; background: #f8f9fa; transition: all 0.3s;">
                    <div style="color: #dc3545; font-size: 24px; margin-bottom: 8px;">🎯</div>
                    <h6 style="margin: 0 0 5px 0; color: #333;">Goal Tracking</h6>
                    <small style="color: #666;">Goal tracking and achievement metrics</small>
                </div>

                <div class="dashboard-nav-card" onclick="window.open('performance-heat-maps.php', '_blank')" style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; cursor: pointer; background: #f8f9fa; transition: all 0.3s;">
                    <div style="color: #6f42c1; font-size: 24px; margin-bottom: 8px;">🌡️</div>
                    <h6 style="margin: 0 0 5px 0; color: #333;">Performance Heat Maps</h6>
                    <small style="color: #666;">Visual performance heat maps</small>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
.dashboard-nav-card:hover {
    background: #e9ecef !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

<div style="clear:left"></div>

<div id="divIndNotes">
	<div data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Interpretation'" data-dojo-id="interpretationIndSavingId"><div id="indInterpretation"></div></div>
</div>

<div style="clear:left"></div>

<div id="divIndNotes2">
<div data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Way Forward'" data-dojo-id="wayForwardIndSavingId"><div id="indWayForward"></div></div>
</div>
<div style="clear:left"></div>
</div><!--end of body div-->
<button type="button" id="scrollIntoView" style="display:compact; width:0px; height:0px; margin:0px;" ></button>
</body>
</html>