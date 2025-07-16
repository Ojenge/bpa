var reportType;
var individualsDone = "False";
//var dataOrg; 
/*= [{"id":1,"Organization":"Automation Department"},
			   {"id":2,"Organization":"BSEA",Target:"100"},
			   {"id":3,"Organization":"BSEA",Target:"100"}];*/
require([
//"dojo/store/Memory",
//"dojo/data/ObjectStore",
"dijit/registry",
"dojo/request",
//"dojo/parser",
//"dojo/aspect",
"dojo/dom",
"dojo/json",
//"dijit/form/Button",
//"dojox/json/query",
//"dojox/layout/ContentPane",
//"dojo/domReady!"
], function(registry, request, dom, json)
{
	deleteReport = function(reportId)
	{
		request.post("reports/delete-report.php", 
		{
			handleAs: "json",
			data:{
				reportId: reportId
				}
		}).then(function()
		{
			//console.log("Deleted report number " + reportId);	
		})
	}
	getReport = function(reportId)
	{
		selectedReport = reportId;
		//alert(reportId+', Test');
		//console.log("Global date is: " + globalDate);
		request.post("reports/get-report.php", 
		{
			handleAs: "json",
			data:{
				reportId: reportId,
				globalDate: globalDate
				}
		}).then(function(reportDetails)
		{
			//console.log("Id: " + reportId + ", reportType: " + reportDetails.reportType);
			if(reportDetails.reportType == "initiativeReport")
				displayInitiative(reportDetails, reportId);
			else if (reportDetails.reportType == "customReport")
				displaySummary(reportDetails, reportId);
			else if(reportDetails.reportType == "initiativeGroup")
				displayInitiativeGroup(reportDetails, reportId);
			else displayCascade(reportDetails, reportId);
		});
		//alert("Get report with id: " + reportId);
	}
	
	getDepartmentReport = function(reportId, orgId)
	{
		selectedReport = reportId;
		//alert(reportId+', Test');
		//console.log("Global date is: " + globalDate + " and org is " + orgId + " and report id is " + reportId);
		request.post("reports/get-report.php", 
		{
			handleAs: "json",
			data:{
				reportId: reportId,
				globalDate: globalDate,
				orgId: orgId
				}
		}).then(function(reportDetails)
		{
			//console.log("Id: " + reportId + ", reportType: " + reportDetails.reportType);
			if(reportDetails.reportType == "initiativeReport")
				displayInitiative(reportDetails, reportId);
			else if (reportDetails.reportType == "customReport")
				displaySummary(reportDetails, reportId);
			else if(reportDetails.reportType == "initiativeGroup")
				displayInitiativeGroup(reportDetails, reportId);
			else displayCascade(reportDetails, reportId);
		});
		//alert("Get report with id: " + reportId);
	}
	
	displayCascade = function(data, reportId)
	{
		if(dom.byId('viewRights').innerHTML == 'True')
		var cascadeHTML	= "<table class='reportTable'><tr><th colspan='4' style='background-color: #aabcfe'>Cascade Report</th></tr><tr><th>Cascaded Organization</th><th>Cascaded Objective</th><th>Parent Organization</th><th>Parent Objective</th></tr>";
		else	
		var cascadeHTML	= "<table class='reportTable'><tr><th colspan='4' style='background-color: #aabcfe'>Cascade Report<img src='images/icons/delete.png' align='right' style='cursor:pointer' onclick='deleteReport(" + reportId + ")' /></th></tr><tr><th>Cascaded Organization</th><th>Cascaded Objective</th><th>Parent Organization</th><th>Parent Objective</th></tr>";
		var cascadeCount = 0;
		while(cascadeCount < data.length)
		{
			cascadeHTML = cascadeHTML + "<tr><td>" + data[cascadeCount].linkOrgName + "</td><td>" + data[cascadeCount].ObjectiveName + "</td><td>" + data[cascadeCount].cascadeOrgName + "</td><td>" + data[cascadeCount].cascadeObj + "</td></tr>";
			cascadeCount++;
		}
		cascadeHTML = cascadeHTML + "</table>";
		dojo.byId("displayReport").innerHTML = cascadeHTML;
	}
	
	displaySummary = function(data, reportId)
	{
		//console.log("display summary");
		var colspan = data.colSpan;
		if(dom.byId('viewRights').innerHTML == 'True')
		var reportHTML = "<table class='table table-bordered table-hover table-sm table-condensed table-responsive'><tr><th style='background-color: #aabcfe' colspan='"+ colspan +"'>" + data.reportName + "</th></tr>"+data.colHeaders;
		else
		var reportHTML = "<table class='table table-bordered table-hover table-sm table-condensed table-responsive'><tr><th style='background-color: #aabcfe' colspan='"+ colspan +"'>" + data.reportName + "<img src='images/icons/delete.png' align='right' style='cursor:pointer' onclick='deleteReport(" + reportId + ")' /></th></tr>"+data.colHeaders;
		var reportCount = 0; var reportCountBehind = -1;perspRowVariable = 0;reportCountInfront = 1;
		while(reportCount < data.Measure.length-1)
		{
			reportHTML = reportHTML + "<tr>";
			
			//if(data.displayColumnsId == "true")
			//	reportHTML = reportHTML + "<td>" + data.Measure[reportCount].orgId + "</td>";
			if(data.groupInitiatives == "true")
			{	
				var initiative = data.Measure[reportCount].Initiatives == undefined ? "" : data.Measure[reportCount].Initiatives;
				reportHTML = reportHTML + "<td>" + initiative + "</td>";
			}
			if(data.displayColumnsOrg == "true")
			{
				if(reportCount == 0)
				reportHTML = reportHTML + "<td>" + data.Measure[reportCount].Organization + "</td>";
				else
				{
					if(data.Measure[reportCount].orgId == data.Measure[reportCountBehind].orgId)
					reportHTML = reportHTML + "<td style='border-top:none;'>" + '' + "</td>";
					else
					reportHTML = reportHTML + "<td>" + data.Measure[reportCount].Organization + "</td>";
				}
				//reportHTML = reportHTML + "<td>" + data.Measure[reportCount].Organization + "</td>";
				orgHead = "Organization";
			}
			if(data.displayColumnsOrgScore == "true")
			{	
				//var score = data.Measure[reportCount].Score == undefined ? "" : data.Measure[reportCount].Score;
				var organizationScore = data.Measure[reportCount].orgScore == undefined ? "" : data.Measure[reportCount].orgScore;
				var orgColor = data.Measure[reportCount].orgScore == undefined ? orgColor : data.Measure[reportCount].orgColor;
				if(reportCount == 0)
				reportHTML = reportHTML + "<td bgcolor='" + orgColor + "'>" + organizationScore + "</td>";
				else
				{
					if(data.Measure[reportCount].orgId == data.Measure[reportCountBehind].orgId)
					reportHTML = reportHTML + "<td bgcolor='" + orgColor + "' style='border-top:none;'></td>";
					else
					reportHTML = reportHTML + "<td bgcolor='" + orgColor + "'>" + organizationScore + "</td>";
				}
				//reportHTML = reportHTML + "<td>" + organizationScore + "</td>";
			}
			if(data.displayColumnsPersp == "true")
			{
				//console.log(data.Measure[reportCount].perspRowCount);
				var perspective = data.Measure[reportCount].Perspective == undefined ? "" : data.Measure[reportCount].Perspective;
				if(reportCount == 0)
				{
					reportHTML = reportHTML + "<td rowspan = '"+ '' +"'>" + perspective + "</td>";
				}
				else
				{
					if(data.Measure[reportCount].perspId == data.Measure[reportCountBehind].perspId)
					{
						reportHTML = reportHTML + "<td style='border-top:1px solid white;'>" + '' + "</td>";
					}
					else
					reportHTML = reportHTML + "<td>" + perspective + "</td>";
				}
			}
			if(data.displayColumnsPerspScore == "true")
			{	
				var perspectiveScore = data.Measure[reportCount].perspScore == undefined ? "" : data.Measure[reportCount].perspScore;
				//alert(perspectiveScore);
				if(perspectiveScore == "grey") perspectiveScore = "";
				var perspColor = data.Measure[reportCount].perspScore == undefined ? "" : data.Measure[reportCount].perspColor;
				if(reportCount == 0)
				{
					reportHTML = reportHTML + "<td bgcolor='" + perspColor + "'>" + perspectiveScore + "</td>";
				}
				else
				{
					if(data.Measure[reportCount].perspId == data.Measure[reportCountBehind].perspId)
					{
						reportHTML = reportHTML + "<td bgcolor='" + perspColor + "' style='border-top:none;'></td>";
					}
					else
					reportHTML = reportHTML + "<td bgcolor='" + perspColor + "' rowspan = '"+''+"'>" + perspectiveScore + "</td>";
				}
				
			}
			if(data.displayColumnsObj == "true")
			{	
				var objective = data.Measure[reportCount].Objective == undefined ? "" : data.Measure[reportCount].Objective;
				
				if(reportCount ==0 || data.Measure[reportCount].objId != data.Measure[reportCountBehind].objId)
				{
					reportHTML = reportHTML + "<td valign='top' rowspan = '"+data.Measure[reportCount].kpiRowCount+"'>" + objective + "</td>";
					//console.log("kpiRowCount => "+data.Measure[reportCount].kpiRowCount);
				}
				else
				{}
				//reportHTML = reportHTML + "<td>" + objective + "</td>";
				
			}
			//alert(data.Measure[reportCount].objScore);
			if(data.displayColumnsObjScore == "true")
			{	
				var objectiveScore = data.Measure[reportCount].objScore == undefined ? "" : data.Measure[reportCount].objScore;
				if(objectiveScore == "grey") objectiveScore = "";
				var color = data.Measure[reportCount].Score == undefined ? "" : data.Measure[reportCount].objColor;
				
				if(reportCount ==0 || data.Measure[reportCount].objId != data.Measure[reportCountBehind].objId)
				reportHTML = reportHTML + "<td valign='top' bgcolor='" + color + "' rowspan = '"+data.Measure[reportCount].kpiRowCount+"'>" + objectiveScore + "</td>";
				else
				{}
				//reportHTML = reportHTML + "<td bgcolor='" + color + "'>" + objectiveScore + "</td>";
				
			}
			if(data.displayColumnsName == "true")
			{	
				var measure = data.Measure[reportCount].Measure == undefined ? "" : data.Measure[reportCount].Measure;
				reportHTML = reportHTML + "<td>" + measure + "</td>";
			}
			if(data.displayColumnsOwner == "true")
			{	
				var owner = data.Measure[reportCount].Owner == undefined ? "" : data.Measure[reportCount].Owner;
				reportHTML = reportHTML + "<td>" + owner + "</td>";
			}
			if(data.displayColumnsUpdater == "true")
			{	
				var updater = data.Measure[reportCount].Updater == undefined ? "" : data.Measure[reportCount].Updater;
				reportHTML = reportHTML + "<td>" + updater + "</td>";
			}
			if(data.displayColumnsScore == "true")
			{	
				var score = data.Measure[reportCount].Score == undefined ? "" : data.Measure[reportCount].Score;
				var color = data.Measure[reportCount].Score == undefined ? "" : data.Measure[reportCount].scoreColor;
				reportHTML = reportHTML + "<td bgcolor='" + color + "'>" + score + "</td>";
			}
			if(data.displayColumnsActual == "true")
			{
				var actual = data.Measure[reportCount].Actual == undefined ? "" : data.Measure[reportCount].Actual;
				var color = data.Measure[reportCount].Score == undefined ? "" : data.Measure[reportCount].scoreColor;
				reportHTML = reportHTML + "<td bgcolor='" + color + "'>" + actual + "</td>";	
				//reportHTML = reportHTML + "<td>" + actual + "</td>";
			}
			if(data.displayColumnsRed == "true")
			{	
				var red = data.Measure[reportCount].Red == undefined ? "" : data.Measure[reportCount].Red;
				reportHTML = reportHTML + "<td>" + red + "</td>";
			}
			if(data.displayColumnsGreen == "true")
			{	
				var green = data.Measure[reportCount].Green == undefined ? "" : data.Measure[reportCount].Green;
				reportHTML = reportHTML + "<td>" + green + "</td>";
			}
			
			var variance = data.Measure[reportCount].Green - data.Measure[reportCount].Actual;
			variance = isNaN(variance) ? "" : variance;
			
			var percentVariance = (variance/data.Measure[reportCount].Green)*100;
			percentVariance = isNaN(percentVariance) ? "" : percentVariance+"%";
			
			if(data.displayColumnsVariance == "true")
			{	
				reportHTML = reportHTML + "<td>" + variance + "</td>";
			}
			if(data.displayColumnsPercentVariance == "true")
			{	
				reportHTML = reportHTML + "<td>" + percentVariance + "</td>";
			}
			if(data.displayInitiatives == "true" && data.groupInitiatives == "false")
			{	
				var initiative = data.Measure[reportCount].Initiatives == undefined ? "" : data.Measure[reportCount].Initiatives;
				if(reportCount ==0 || data.Measure[reportCount].objId != data.Measure[reportCountBehind].objId)
				reportHTML = reportHTML + "<td valign='top' rowspan = '"+data.Measure[reportCount].kpiRowCount+"'>" + initiative + "</td>";
				else
				{}
				
				if(individualsDone == "True")
				{}
				else//print only once
				{
					var individual = data.Measure[reportCount].Individuals == undefined ? "" : data.Measure[reportCount].Individuals;
					if(reportCount == 0 || data.Measure[reportCount].objId != data.Measure[reportCountBehind].objId)
					{
						if(reportCount > 0)
						{
							if(data.Measure[reportCount].Individuals == data.Measure[reportCount-1].Individuals)
							{
								//don't print	
								//console.log("Do not print more than once" + data.Measure[reportCount].Individuals + " vs " +  data.Measure[reportCount-1].Individuals);
							}
							else
							reportHTML = reportHTML + "<td valign='top' rowspan = '"+data.Measure[reportCount].kpiRowCount+"'>" + individual + "</td>";
						}
						else
						reportHTML = reportHTML + "<td valign='top' rowspan = '"+data.Measure[reportCount].kpiRowCount+"'>" + individual + "</td>";
					}
					else
					{}
				}
				individualsDone = "True";
				//reportHTML = reportHTML + "<td>" + initiative + "</td>";
			}
			reportHTML = reportHTML + "</tr>";
			individualsDone = "False";
			//data.linkedTo
			//data.selectedObjects
			
			reportCount++;reportCountBehind++;reportCountInfront++;
		}
		//reportHTML = reportHTML + "</table>";
		dojo.byId("displayReport").innerHTML = reportHTML + "</table>";
		//alert(reportHTML);
	}
	displayInitiative = function(data, reportId)
	{
		//alert(json.stringify(data));
		var colspan = data.colSpan;
		if(dom.byId('viewRights').innerHTML == 'True')
		var initiativeHTML = "<table class='reportTable'><tr><th style='background-color: #aabcfe' colspan='"+ colspan +"'>" + data.reportName + "</th></tr>"+data.colHeaders;
		else
		var initiativeHTML = "<table class='reportTable'><tr><th style='background-color: #aabcfe' colspan='"+ colspan +"'>" + data.reportName + "<img src='images/icons/delete.png' align='right' style='cursor:pointer' onclick='deleteReport(" + reportId + ")' /></th></tr>"+data.colHeaders;
		var tdBgColor = '#ffffff', count = 0;
		
		while(count < data.Initiative.length)
		{
			if(count%2==0) tdBgColor = '#ffffff';
			else tdBgColor = '#ffffff';
			
			initiativeHTML = initiativeHTML + "<tr style='background-color: "+ tdBgColor +";'>" + "<td>" + data.Initiative[count].name + "</td>";
			
			if(data.displayInitSponsor == "true")
			{
				initiativeHTML = initiativeHTML + "<td>" + data.Initiative[count].sponsor + "</td>";
			}
			if(data.displayInitOwner == "true")
			{
				initiativeHTML = initiativeHTML + "<td>" + data.Initiative[count].owner + "</td>";
			}
			if(data.displayInitBudget == "true")
			{
				initiativeHTML = initiativeHTML + "<td>" + data.Initiative[count].budget + "</td>";
			}
			if(data.displayInitCost == "true")
			{
				initiativeHTML = initiativeHTML + "<td style='background-color:"+ data.Initiative[count].damageColor +"'>" + data.Initiative[count].damage + "</td>";
			}
			if(data.displayInitStart == "true")
			{
				initiativeHTML = initiativeHTML + "<td>" + data.Initiative[count].startDate + "</td>";
			}
			if(data.displayInitDue == "true")
			{
				initiativeHTML = initiativeHTML + "<td style='background-color:"+ data.Initiative[count].initiativeColor +"'>" + data.Initiative[count].dueDate + "</td>";
			}
			if(data.displayInitComplete == "true")
			{
				initiativeHTML = initiativeHTML + "<td>" + data.Initiative[count].completionDate + "</td>";
				//initiativeHTML = initiativeHTML + "<td>" + data.Initiative[count].completionStatus + "</td>";
			}
			if(data.displayInitDeliverable == "true")
			{
				initiativeHTML = initiativeHTML + "<td style='background-color:" + data.Initiative[count].deliverableColor + ";'>" + data.Initiative[count].deliverable +  "</td>";
			}
			if(data.displayInitDeliverableStatus == "true")
			{
				initiativeHTML = initiativeHTML + "<td style='background-color: " + data.Initiative[count].deliverableColor + ";'>" + data.Initiative[count].deliverableStatus +  "</td>";
			}
			if(data.displayInitParent == "true")
			{
				initiativeHTML = initiativeHTML + "<td>" + data.Initiative[count].link + "</td>";
			}
			initiativeHTML = initiativeHTML + "</tr>";
			count++;
		}
		dojo.byId("displayReport").innerHTML = initiativeHTML + "</table>";
	}
	displayInitiativeGroup = function(data, reportId)
	{
		if(data.displayInitiatives == "true")
		{
			if(dbReport == "true")
			var initiatives = "<table class='reportTable'><tr style='background-color: #aabcfe'><th>Indicator(s)</th><th>Actual</th><th>Target</th></tr>";
			else
			var initiatives = "<table class='reportTable'><tr><th colspan='2'>"+data.reportName+"<img src='images/icons/delete.png' align='right' style='cursor:pointer' onclick='deleteReport(" + reportId + ")' /></th></tr><tr style='background-color: #aabcfe'><th>Initiative</th><th>Indicator</th><th>Actual</th><th>Target</th></tr>";
			var count = 0;
			while(count < data.Initiatives.length)
			{
				initKpis = 1;
				if(data.Initiatives[count].kpiCount > 0)
				{
					while(initKpis <= data.Initiatives[count].kpiCount)
					{
						var thisKpi = "kpi"+initKpis;
						var thisKpiActual = "kpiActual"+initKpis;
						var thisKpiTarget = "kpiGreen"+initKpis;
						//alert(thisKpi + " = " + data.Initiatives[count][thisKpi]);
						if(initKpis == 1)
						initiatives = initiatives+"<tr><td rowspan="+data.Initiatives[count].kpiCount+">"+data.Initiatives[count].initiative+"</td><td>"+data.Initiatives[count][thisKpi]+"</td><td>"+data.Initiatives[count][thisKpiActual]+"</td><td>"+data.Initiatives[count][thisKpiTarget]+"</td></tr>";
						else
						initiatives = initiatives + "<tr><td>"+data.Initiatives[count][thisKpi]+"</td><td>"+data.Initiatives[count][thisKpiActual]+"</td><td>"+data.Initiatives[count][thisKpiTarget]+"</td></tr>";
						initKpis++;
					}
				}
				else
				initiatives = initiatives + "<tr><td>" + data.Initiatives[count].initiative + "</td></tr>";
				count++;
			}
		}
		else
		{
			if(dbReport == "true")
			var initiatives = "<table class='reportTable'><tr style='background-color: #aabcfe'><th>Indicator(s)</th><th>Actual</th><th>Target</th><th>Trend Line</th></tr>";
			else
			var initiatives = "<table class='reportTable'><tr><th>"+data.reportName+"<img src='images/icons/delete.png' align='right' style='cursor:pointer' onclick='deleteReport(" + reportId + ")' /></th></tr><tr style='background-color: #aabcfe'><th>Indicator(s)</th><th>Actual</th><th>Target</th></tr>";
			var count = 0;
			while(count < data.Initiatives.length)
			{
				initKpis = 1;
				if(data.Initiatives[count].kpiCount > 0)
				{
					while(initKpis <= data.Initiatives[count].kpiCount)
					{
						var thisKpi = "kpi"+initKpis;
						var thisKpiActual = "kpiActual"+initKpis;
						var thisKpiTarget = "kpiGreen"+initKpis;
						//var thisKpiStr = "\"" + thisKpi + "\"";
						var bgColor = "white";
						if(data.Initiatives[count][thisKpiActual] < data.Initiatives[count][thisKpiTarget]) bgColor = "#F08080"
						if(data.Initiatives[count][thisKpiActual] >= data.Initiatives[count][thisKpiTarget]) bgColor = "#90EE90"
						initiatives = initiatives + "<tr><td width='51%'><img style='float:left; min-height:30px; visibility:hidden; width:0px;'/>"+data.Initiatives[count][thisKpi]+"</td><td width='8%' align='center' style='background-color:"+bgColor+"'>"+data.Initiatives[count][thisKpiActual]+"</td><td width='8%' align='center'>"+data.Initiatives[count][thisKpiTarget]+"</td><td cellpadding='0' width='30%' id='"+thisKpi+"'></td></tr>";
						initKpis++;
					}
				}
				else
				{}//initiatives = initiatives + "<tr><td>" + data.Initiatives[count].initiative + "</td></tr>";
				count++;
			}
		}
		if(dbReport == "true")
		dojo.byId("dbReport").innerHTML = initiatives + "</table>";
		else
		dojo.byId("displayReport").innerHTML = initiatives + "</table>";
	}
	customReport = function()
	{
		reportType = "customReport";
		dijit.byId("newCustomReportDialog").show();
		dijit.byId("newReportDialog").onCancel();
	}
	summaryReport = function()
	{
		reportType = "summaryReport";
		dijit.byId("newCustomReportDialog").show();
		dijit.byId("newReportDialog").onCancel();
	}
	cascadeReport = function()
	{
		reportType = "cascadeReport";
		dijit.byId("newCascadeReportDialog").show();
		dijit.byId("newReportDialog").onCancel();
	}
	linksReport = function()
	{
		reportType = "linksReport";
		dijit.byId("newCustomReportDialog").show();
		dijit.byId("newReportDialog").onCancel();
	}
	performanceReport = function()
	{
		reportType = "performanceReport";
		dijit.byId("newCustomReportDialog").show();
		dijit.byId("newReportDialog").onCancel();
	}
	initiativeReport = function()
	{
		reportType = "initiativeReport";
		dijit.byId("newInitiativeReportDialog").show();
		dijit.byId("newReportDialog").onCancel();
	}
	showSelectObjectDialog = function()
	{
		//alert("Refreshing");
		organizations.refresh();organizations.refresh();
		perspectives.refresh();perspectives.refresh();
		objectives.refresh();objectives.refresh();
		measures.refresh();measures.refresh();
		target.refresh();target.refresh();
		dijit.byId("selectObjectDialog").show(); 	
	}
	hideReportAddDialog = function()
	{
		dijit.byId("newReportDialog").onCancel();
	}
	hideRedReportDialog = function()
	{
		dijit.byId("newCustomReportDialog").onCancel();
	}
	hideInitiativeReportDialog = function()
	{
		dijit.byId("newInitiativeReportDialog").onCancel();
	}
	hideCascadeReportDialog = function()
	{
		dijit.byId("newCascadeReportDialog").onCancel();
	}
	saveReport = function()
	{
		var reportName = dojo.byId("redReportName").value;
		var selectedObjects;
		if(dom.byId("selectedObjectsIds").innerHTML != null)
		selectedObjects = dom.byId("selectedObjectsIds").innerHTML;
		else
		selectedObjects = null;
		
		var displayColumnsId = false, displayColumnsKpi = false, displayColumnsOrg = false,displayColumnsOrgScore = false, displayColumnsPersp = false, displayColumnsPerspScore = false, displayColumnsObj = false, displayColumnsObjScore = false, displayColumnsOwner = false, displayColumnsUpdater = false, displayColumnsScore = false, displayColumnsActual = false, displayColumnsTarget = false, displayColumnsVariance = false, displayColumnsPercentVariance = false, displayRedFilter = false, displayGreyFilter = false, displayGreenFilter = false, displayInitiativeFilter = false, displayInitiativeGroup = false;
		/*if(dijit.byId("columnId").checked)
			displayColumnsId = "true";*/
		if(dijit.byId("columnKpi").checked)
			displayColumnsKpi = "true";
		if(dijit.byId("columnOrg").checked)
			displayColumnsOrg = "true";
		if(dijit.byId("columnOrgScore").checked)
			displayColumnsOrgScore = "true";
		if(dijit.byId("columnPersp").checked)
			displayColumnsPersp = "true";
		if(dijit.byId("columnPerspScore").checked)
			displayColumnsPerspScore = "true";
		if(dijit.byId("columnObj").checked)
			displayColumnsObj = "true";
		if(dijit.byId("columnObjScore").checked)
			displayColumnsObjScore = "true";
		if(dijit.byId("columnOwner").checked)
			displayColumnsOwner = "true";
		if(dijit.byId("columnUpdater").checked)
			displayColumnsUpdater = "true";
		if(dijit.byId("columnScore").checked)
			displayColumnsScore = "true";
		if(dijit.byId("columnActual").checked)
			displayColumnsActual = "true";
		if(dijit.byId("columnTarget").checked)
			displayColumnsTarget = "true";
		if(dijit.byId("columnVariance").checked)
			displayColumnsVariance = "true";
		if(dijit.byId("columnPercentVariance").checked)
			displayColumnsPercentVariance = "true";
		if(dijit.byId("redFilter").checked)
			displayRedFilter = "true";
		if(dijit.byId("greyFilter").checked)
			displayGreyFilter = "true";
		if(dijit.byId("greenFilter").checked)
			displayGreenFilter = "true";
		if(dijit.byId("initiativeFilter").checked)
			displayInitiativeFilter = "true";
		if(dijit.byId("initiativeGroup").checked)
			displayInitiativeGroup = "true";
			//alert("Name: " + reportName);
			
			//alert(tnAdd);
		
		request.post("save-report.php", 
		{
			//handleAs: "json",
			data:{
					reportName: reportName,
					selectedObjects: selectedObjects,
					//displayColumnsId: displayColumnsId,
					displayColumnsKpi: displayColumnsKpi, 
					displayColumnsOrg: displayColumnsOrg,
					displayColumnsOrgScore: displayColumnsOrgScore,
					displayColumnsPersp: displayColumnsPersp,
					displayColumnsPerspScore: displayColumnsPerspScore,
					displayColumnsObj: displayColumnsObj,
					displayColumnsObjScore: displayColumnsObjScore,
					displayColumnsOwner: displayColumnsOwner, 
					displayColumnsUpdater: displayColumnsUpdater, 
					displayColumnsScore: displayColumnsScore, 
					displayColumnsActual: displayColumnsActual, 
					displayColumnsTarget: displayColumnsTarget, 
					displayColumnsVariance: displayColumnsVariance, 
					displayColumnsPercentVariance: displayColumnsPercentVariance,
					reportType: reportType,
					linkedTo: tnAdd,
					displayRedFilter: displayRedFilter,
					displayGreyFilter: displayGreyFilter,
					displayGreenFilter: displayGreenFilter,
					displayInitiativeFilter: displayInitiativeFilter,
					displayInitiativeGroup: displayInitiativeGroup
				}
		});
	}
	saveInitiativeReport = function()
	{
		var reportName = dojo.byId("initiativeReportName").value;
		var selectedObjects;
		if(dom.byId("selectedInitObjectsIds").innerHTML != null)
		selectedObjects = dom.byId("selectedInitObjectsIds").innerHTML;
		else
		selectedObjects = null;
		var displayInitSponsor = false, displayInitOwner = false, displayInitBudget = false, displayInitCost = false, displayInitStart = false, displayInitDue = false, displayInitComplete = false, displayInitDeliverable = false, displayInitDeliverableStatus = false, displayInitParent = false, displayInitRedFilter = false, displayInitGreyFilter = false, displayInitGreenFilter = false;
		/*if(dijit.byId("columnId").checked)
			displayColumnsId = "true";*/
		if(dijit.byId("initSponsor").checked)
			displayInitSponsor = "true";
		if(dijit.byId("initOwner").checked)
			displayInitOwner = "true";
		if(dijit.byId("initBudget").checked)
			displayInitBudget = "true";
		if(dijit.byId("initCost").checked)
			displayInitCost = "true";
		if(dijit.byId("initStart").checked)
			displayInitStart = "true";
		if(dijit.byId("initDue").checked)
			displayInitDue = "true";
		if(dijit.byId("initComplete").checked)
			displayInitComplete = "true";
		if(dijit.byId("initDeliverable").checked)
			displayInitDeliverable = "true";
		if(dijit.byId("initDeliverableStatus").checked)
			displayInitDeliverableStatus = "true";
		if(dijit.byId("initParent").checked)
			displayInitParent = "true";
		if(dijit.byId("initRedFilter").checked)
			displayInitRedFilter = "true";
		if(dijit.byId("initGreyFilter").checked)
			displayInitGreyFilter = "true";
		if(dijit.byId("initGreenFilter").checked)
			displayInitGreenFilter = "true";
		
		request.post("reports/save-report.php", 
		{
			//handleAs: "json",
			data:{
				reportName: reportName,
				selectedObjects: selectedObjects,
				
				displayInitSponsor : displayInitSponsor,
				displayInitOwner : displayInitOwner,
				displayInitBudget : displayInitBudget,
				displayInitCost : displayInitCost,
				displayInitStart : displayInitStart,
				displayInitDue : displayInitDue,
				displayInitComplete : displayInitComplete,
				displayInitDeliverable : displayInitDeliverable,
				displayInitDeliverableStatus : displayInitDeliverableStatus,
				displayInitParent : displayInitParent,
				displayInitRedFilter : displayInitRedFilter,
				displayInitGreyFilter : displayInitGreyFilter,
				displayInitGreenFilter : displayInitGreenFilter,
			
				reportType: reportType,
				linkedTo: tnAdd
			}
		});
	}
	saveCascadeReport = function()
	{
		var reportName = dojo.byId("cascadeReportName").value;
		var selectedObjects;
		if(dom.byId("selectedCascadeObjectsIds").innerHTML != null)
		selectedObjects = dom.byId("selectedCascadeObjectsIds").innerHTML;
		else
		selectedObjects = null;
		
		request.post("save-report.php", 
		{
			//handleAs: "json",
			data:{
					reportName: reportName,
					selectedObjects: selectedObjects,
					reportType: reportType,
					linkedTo: tnAdd
				}
		});
	}
})