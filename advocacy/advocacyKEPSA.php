<?php 
require_once("admin/models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
$userPermission = fetchUserPermissions($loggedInUser->user_id);
$view = "True";
foreach($userPermission as $id)
{
	if($id["permission_id"] == "1")
	$view = "True";
	if($id["permission_id"] == "2")
	$view = "Administrator";
	if($id["permission_id"] == "3")
	$view = "Application";
	if($id["permission_id"] == "3000")
	$view = "Board";
}
?>
<html>
<head>
<style>
/*	@import "../dojo/resources/dojo.css";
	@import "../dijit/themes/dijit.css";
	@import "../dijit/themes/claro/claro.css";
	@import "../dijit/tests/css/dijitTests.css";*/
	@import "css/gantt.css";
	@import "css/_Gauge.css";
	@import "css/tableStyle.css";
</style>

<script type="text/javascript">
var select1, select2, select3;
require([
"dojo/dom",
"dojo/dom-style",
"dojo/store/Memory",
"dojo/parser",	
"dijit/TitlePane",
"dijit/Menu",
"dijit/MenuItem",
"dijit/form/DateTextBox",
"dijit/form/FilteringSelect",
"dojox/gantt/GanttChart",
"dojox/gantt/GanttProjectItem",
"dojox/gantt/GanttTaskItem",
"dojox/gauges/GlossyHorizontalGauge",		
//'dojox/widget/Calendar',
"dojo/request",
"dojo/json",
	
"dojo/domReady!"
], function(dom, domStyle, Memory, parser, TitlePane, Menu, MenuItem, DateTextBox, FilteringSelect, GanttChart, GanttProjectItem, GanttTaskItem, GlossyHorizontalGauge, request, json){
				//parser.parse();
	//var advocacyStore;		

request.post("userCalls/get-users.php",{
handleAs: "json",
data: {
}						
}).then(function(userData) 
{			
	var userStore = new Memory({data:userData});
	
	select1 = new FilteringSelect({
	name: "userSelectManager",
	//displayedValue: managerDisplay,
	//placeHolder: "Select a User",
	store: userStore,
	searchAttr: "User",
	maxHeight: -1, 
	onChange: function(){
		dom.byId("ownerId").innerHTML = this.item.id;
	}
	}, "advocacyOwnerInput");
	select1.startup();
});

editAdvocacy = function()
{
	dom.byId("advocacyEditType").innerHTML = "Edit";
	//select1.set("value", dojo.byId("ownerDiv").innerHTML);
	//select2.set("value", dojo.byId("agencyDiv").innerHTML);
	//editAdvocacyStatus = "true";	
	dijit.byId("newAdvocacyDialog").set("title", "Edit Advocacy");
	dom.byId("advocacyNameInput").value = dojo.byId("advocacyNameDiv").innerHTML;
	dom.byId("advocacyCategoryInput").value = dojo.byId("categoryDiv").innerHTML;
	dom.byId("advocacyTypeInput").value = dojo.byId("typeDiv").innerHTML;
	dom.byId("advocacyStatusInput").value = dojo.byId("statusDiv").innerHTML;
	dom.byId("advocacyMeaningInput").innerHTML = dojo.byId("meaningDiv").innerHTML;
	dom.byId("advocacyOwnerInput").value = dojo.byId("ownerDiv").innerHTML;
	dom.byId("advocacyAgencyInput").value = dojo.byId("agencyDiv").innerHTML;
	dom.byId("advocacyStartInput").value = dojo.byId("advocacyStartDateDiv").innerHTML;
	dom.byId("advocacyDueInput").value = dojo.byId("advocacyEndDateDiv").innerHTML;
	dom.byId("advocacyCompleteInput").value = dojo.byId("advocacyCompletionDateDiv").innerHTML;
	dijit.byId("newAdvocacyDialog").show();
	dijit.byId("newAdvocacyDialog").set("title", "Edit Advocacy Item");
}
newAdvocacy = function()
{
	dom.byId("advocacyEditType").innerHTML = "New";
	dom.byId("advocacyNameInput").value = '';
	dom.byId("advocacyCategoryInput").value = '';
	dom.byId("advocacyTypeInput").value = '';
	dom.byId("advocacyStatusInput").value = '';
	dom.byId("advocacyMeaningInput").innerHTML = '';
	dom.byId("advocacyOwnerInput").value = '';
	dom.byId("advocacyAgencyInput").value = '';
	dom.byId("advocacyStartInput").value = '';
	dom.byId("advocacyDueInput").value = '';
	dom.byId("advocacyCompleteInput").value = '';
	dijit.byId("newAdvocacyDialog").set("title", "New Advocacy Item");
	dijit.byId("newAdvocacyDialog").show();	
}
deleteAdvocacy = function()
{
	dom.byId("advocacyEditType").innerHTML = "Delete";
	saveAdvocacy();	
}
deleteAdvocacyCategory = function(category)
{
	request.post("delete-advocacy-category.php",{
	handleAs: "json",
	data: {
		category: category
	}}).then(function()
	{
		
	})
}
newCategory = function(category)
{
	dijit.byId("categoryRenameDialog").show();
	dom.byId("categoryName").innerHTML = category;	
}
getCategoryName = function(category)
{
	dijit.byId("categoryRenameDialog").show();
	dom.byId("categoryName").innerHTML = category;
}
renameCategory = function()
{
	if(dom.byId("categoryName").innerHTML == "New")
	{
		request.post("save-advocacy-category.php",{
		handleAs: "json",
		data: {
			newCategory: dom.byId('categoryRenameInput').value
		}}).then(function()
		{
			//advocacyList(category);
		})
	}
	else
	{
		request.post("update-advocacy-category.php",{
		handleAs: "json",
		data: {
			category: dom.byId("categoryName").innerHTML,
			newCategory: dom.byId('categoryRenameInput').value
		}}).then(function()
		{
			//advocacyList(category);
		})
	}
}
saveAdvocacy = function()
{
	console.log(dom.byId("advocacyMeaningInput").innerHTML);
	request.post("save-advocacy.php",{
	handleAs: "json",
	data: {
		editAdvocacyStatus: dom.byId("advocacyEditType").innerHTML,
		selectedAdvocacy: dom.byId("advocacyIdDiv").innerHTML,
		advocacyName: dom.byId("advocacyNameInput").value,
		advocacyCategory: dom.byId("advocacyCategoryInput").value,
		advocacyType: dom.byId("advocacyTypeInput").value,
		advocacyStatus: dom.byId("advocacyStatusInput").value,
		advocacyOwner: dom.byId("ownerId").innerHTML,
		advocacyAgency: dom.byId("advocacyAgencyInput").value,
		advocacyMeaning: dom.byId("advocacyMeaningInput").innerHTML,
		advocacyStart: dom.byId("advocacyStartInput").value,
		advocacyDue: dom.byId("advocacyDueInput").value,
		advocacyComplete: dom.byId("advocacyCompleteInput").value
	}}).then(function(advocacyRefresh)
	{
		advocacyListFunction(advocacyRefresh.id);
		advocacyMain(advocacyRefresh.category);
		//alert("Initiative Successfully Saved");
	});
}

		//**************************************************************************************
		//Advocacy Progress Bar
if (dijit.byId("advocacyStatusGauge")) 
{
	dijit.byId("advocacyStatusGauge").destroy(true);
	//alert("nimeharibiwa");
}		
var advocacyStatusGauge = null;

	// create an  Horizontal Gauge
	advocacyStatusGauge = new GlossyHorizontalGauge({
		background: [255, 255, 255, 0],
		id: "advocacyStatusGauge",
		title: "Value",
		majorTicksColor:"000000",
		minorTicksColor:"000000",
		//minorTicksInterval:200,
		width: 220,
		height: 40,
		hideValue: true
		//hideValues:"true"
	}, dojo.byId("advocacyGauge"));
	advocacyStatusGauge.startup();
function changeGaugeColor(v){
	advocacyStatusGauge.set('color', v);
}
//changeGaugeColor("003399");
//changeNeedleColor("0099cc");
changeNeedleColor("");
function changeNeedleColor(v){
	advocacyStatusGauge.set('markerColor', v);
}

advocacySummary = function()
{
	domStyle.set(dom.byId("advocacyGantt"), 'display', 'none');
	domStyle.set(dom.byId("advocacyDetails"), 'display', 'none');
	domStyle.set(dom.byId("advocacyTitlePane"), 'height', '600px');
	domStyle.set(dom.byId("advocacySummary"), 'display', 'block');
	domStyle.set(dom.byId("advocacyPie"), 'display', 'block');
	domStyle.set(dom.byId("advocacyTrend"), 'display', 'block');
	request.post("get-advocacy-summary.php",{
		handleAs: "json"				
	}).then(function(directives) 
	{
		console.log("Returned from get-advocacy-summary.php");
		var length = directives.length-1;
		
		var total = directives[length].RedTotal + directives[length].OrangeTotal + directives[length].YellowTotal + directives[length].GreenTotal + directives[length].BlueTotal; 
		
		var ministryCount = 0;
		var ministryNames=[], ministryBlues=[], ministryGreens=[], ministryYellows=[], ministryReds=[], ministryOranges=[];
		while(ministryCount < directives.length)
		{
			
			ministryNames[ministryCount] = directives[ministryCount].Category;
			ministryBlues[ministryCount] = parseInt(directives[ministryCount].Blue,10);
			ministryGreens[ministryCount] = parseInt(directives[ministryCount].Green,10);
			//if(ministryGreens[ministryCount] == 0) ministryGreens[ministryCount] = null;
			ministryYellows[ministryCount] = parseInt(directives[ministryCount].Yellow,10);
			ministryOranges[ministryCount] = parseInt(directives[ministryCount].Orange,10);
			ministryReds[ministryCount] = parseInt(directives[ministryCount].Red,10);
			ministryCount++;
			console.log("retrieving advocacies");
		}
		Highcharts.setOptions({
		 colors: ['red', 'orange', 'yellow', 'green' , 'blue', '#64E572', '#FF9655', '#FFF263']
		});
		var chart = new Highcharts.Chart({
			chart: {
					renderTo: 'advocacySummary'
					//type: 'column'
        		},
				title: {
						//text: 'Presidential Directives',
						//x: -20 //center
						//align: 'left',
						text: '',
						style: {
							display: 'none'
						}
					},
				subtitle: {
					text: 'Categories<br>Summary',
					//x: -20 //center
					align: 'left'
					},
				xAxis: {
					categories: ministryNames,
					min: 0,
					max: 6,
					labels: {
						rotation: -60,
						//align: "right",
						style: {
							fontSize: '9px',
							fontFamily: 'Verdana, sans-serif',
							width: '50px'
							//fontWeight: "bold"
						},
						step:1
						//formatter: function () {
						//	return this.value.replace(/and/g, '<br/>and');
							//return this.value.replace(/and/g, function('<br/>and', i){if(i === 2) return repl; return match});
						//}
					},
					scrollbar: {enabled:true}
				},
				yAxis: {
					min: 0,
					title: {
						text: 'Total Advocacy Issues'
					},
					stackLabels: {
						enabled: true,
						style: {
							fontWeight: 'bold',
							color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
						}
					}
				},
				/*labels: {
					items: [{
						html: 'Overall Directives Status',
						style: {
							left: '550px',
							top: '5px',
							color: (Highcharts.theme && Highcharts.theme.textColor) || 'black'
						}
					}]
				},*/
				legend: {
					//align: 'right',
					x: 80,
					verticalAlign: 'top',
					y: -5,
					floating: true,
					backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
					borderColor: '#CCC',
					borderWidth: 1,
					shadow: true,
					padding: 10
				},
				credits: {
					  enabled: false
				  },
				tooltip: {
					formatter: function () {
						return '<b>' + this.x + '</b><br/>' +
							this.series.name + ': ' + this.y + ' (<strong>' + this.point.percentage.toFixed(0) + '%)</strong><br/>' +
							'Total: ' + this.point.stackTotal;
					}
				},
				plotOptions: {
					column: {
						stacking: 'normal', //normal or percent
						//pointPadding: 3,
                    	//pointWidth: 40,
						dataLabels: 
						{
							enabled: true,
							color: 'black',
							formatter:function() {	if(this.y != 0) {  return this.y;  }  }
						}
					},
					series: 
					{
						cursor: 'pointer',
						point: 
						{
							events: 
							{
								click: function () 
								{
									//alert('Category: ' + this.category + ', value: ' + this.y);
									advocacyMain(this.category);
								}
							}
						}
					}
				},
				series: //[{data: [29.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4]}]
				[{
					type: 'column',
					name: 'Identified',
					data: ministryReds
				}, {
					type: 'column',
					name: 'Request Made to Government',
					data: ministryOranges
				}, {
					type: 'column',
					name: 'Government Committed',
					data: ministryYellows
				}, {
					type: 'column',
					name: 'Implemented in Progress',
					data: ministryGreens
				},
				{
					type: 'column',
					name: 'Issue Resolved',
					data: ministryBlues
				}],
				 scrollbar: {
					enabled: true,
					barBackgroundColor: 'gray',
					barBorderRadius: 7,
					barBorderWidth: 0,
					buttonBackgroundColor: 'gray',
					buttonBorderWidth: 0,
					buttonArrowColor: 'yellow',
					buttonBorderRadius: 7,
					rifleColor: 'yellow',
					trackBackgroundColor: 'white',
					trackBorderWidth: 1,
					trackBorderColor: 'silver',
					trackBorderRadius: 7
				}
				
			});
			
			var chart = new Highcharts.Chart({
					chart: {
							type: 'pie',
							renderTo: 'advocacyPie',
							//plotBackgroundColor: null,
							//plotBorderWidth: null,
							plotShadow: true,
							//margin: [0, 0, 0, 0],
							//startAngle: -150,
							//marginRight: 60
							//spacingTop: 0,
							//spacingBottom: 0,
							//spacingLeft: 0,
							//spacingRight: 0
							
							},
					title: {
								text: '',
								style: {
									display: 'none'
								}
						    //y: -30
							},
							subtitle:{
								text: "Overall Summary",
								align: "left",
								floating: true,
								y: 80
								},
					tooltip: {
						pointFormat: '{series.name}: <b> {point.y}</b>'
					},
					credits:
					{
						enabled: false	
					},
					plotOptions: {
						pie: {
							allowPointSelect: true,
							cursor: 'pointer',
							dataLabels: {
								enabled: true,
								format: '<b>{point.name}</b>: {point.percentage:.0f} %',
								//distance: 2
							},
							showInLegend: false,
							size: 100
						}
					},
					series: [{
							type: 'pie',
							name: 'Number of Advocacy Issues',
							
							data: [
								{//var total = directives[length].RedTotal + directives[length].YellowTotal + directives[length].GreenTotal;
									y: parseInt(directives[length].RedTotal, 10),
									name: "Identified"
									//tooltip: projects[0].Green+" Positive Mentions"
									//color: 'red'
								},{//var total = directives[length].RedTotal + directives[length].YellowTotal + directives[length].GreenTotal;
									y: parseInt(directives[length].OrangeTotal, 10),
									name: "Request Made to Government"
									//tooltip: projects[0].Green+" Positive Mentions"
									//color: 'red'
								}, {
									y: parseInt(directives[length].YellowTotal, 10),
									name: "Government Committed"
									//tooltip: projects[1].Green+" Positive Mentions"
								},{
									y: parseInt(directives[length].GreenTotal, 10),
									name: "Implementation in Progress"
									//tooltip: projects[2].Green+" Positive Mention"
								},{//var total = directives[length].RedTotal + directives[length].YellowTotal + directives[length].GreenTotal;
									y: parseInt(directives[length].BlueTotal, 10),
									name: "Issue Resolved"
									//tooltip: projects[0].Green+" Positive Mentions"
									//color: 'red'
								}
							]
						}]
				})
	});
	
	request.post("get-trend.php",{
		handleAs: "json"
	}).then(function(trend) 
	{	
		
		var count = 0, labels = [], red = [], orange = [], yellow = [], green = [], blue = [];
		while(count < trend.length)
		{
			if(trend[count].red == null) red[count] = null; else red[count] = parseInt(trend[count].red,10);
			if(trend[count].orange == null) orange[count] = null; else orange[count] = parseInt(trend[count].orange,10);
			if(trend[count].yellow == null) yellow[count] = null; else yellow[count] = parseInt(trend[count].yellow,10);
			if(trend[count].green == null) green[count] = null; else green[count] = parseInt(trend[count].green,10);
			if(trend[count].blue == null) blue[count] = null; else blue[count] = parseInt(trend[count].blue,10);
			labels[count] = trend[count].date;
			count++;
		}
		
		chart = new Highcharts.Chart({
		chart: {
			renderTo: 'advocacyTrend',
			type: 'area'
		},
	     title: {
            text: 'Advocacy Performance Over Time'
        },
        subtitle: {
            text: ''
        },
        xAxis: {
            categories: labels,
            tickmarkPlacement: 'on',
            title: {
                enabled: false
            }
        },
        yAxis: {
            title: {
                text: 'Number of Advocacy Items'
            }
        },
        tooltip: {
            shared: true,
            useHTML: true,
			headerFormat: '<small>{point.key}</small><table>',
			pointFormat: '<tr><td style="color: {series.color};"><b>&diams;</b></td><td>{series.name}:</td>' +
			//pointFormat: '<tr><td>{series.name}: &bull; (circular bullet) &raquo; (two greater thans) &rArr; (double arrow) &radic; (tick)</td>' +
			'<td style="text-align: right"><b>{point.y}</b></td></tr>',
			footerFormat: '</table>'
        },
        plotOptions: {
            area: {
                stacking: 'normal',
                lineColor: '#666666',
                lineWidth: 1,
                marker: {
                    lineWidth: 1,
                    lineColor: '#666666'
                }
            }
        },
        series: [{
            name: 'Identified',
            data: red,
			color: 'red'
        }, {
            name: 'Request Made to Government',
            data: orange,
			color: 'orange'
        }, {
            name: 'Government Committed',
            data: yellow,
			color: 'yellow'
        }, {
            name: 'Implementation in Progress',
            data: green,
			color: 'green'
        },{
            name: 'Issue Resolved',
            data: blue,
			color: 'blue'
        }],
		credits: {
		enabled: false
	  }
    });
	
	})
}
advocacySummary();

request.post("get-categories.php",{
handleAs: "json",
data: {
}						
}).then(function(categories) 
{			
	var categoryStore = new Memory({data:categories});
	
	selectCategory = new FilteringSelect({
	name: "Category",
	//displayedValue: managerDisplay,
	//placeHolder: "Select a User",
	store: categoryStore,
	searchAttr: "Category",
	maxHeight: -1, 
	onChange: function(){
		//kpiOwnerId = this.item.id; //shouldn't this be this.items.id as per: {"identifier":"User","label":"User","items":[{"id":"ind1","User":"Shazia Hamid"},{"id":"ind12","User":"Jason"}]};
		//if(updaterCheckbox == "True") dijit.byId('kpiUpdater').set('value', this.item.User);
	}
	}, "advocacyCategoryInput");
	selectCategory.startup();
})

});
</script>
</head>
<body>
<div id="divAdvocacyTable">
<div data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Advocacy Dashboard'" id="advocacyTitlePane" style="height:390px; overflow:scroll;">
        <div id="advocacyGantt" class= "ganttContent"></div>
        <table>
        <tr>
        	<td width="500px"><div id="advocacyPie" style="width:500px;height:500px;"></div></td>
        	<td height"400px" width="550px"><div id="advocacySummary" width='550px' style="height:500px;"></div></td>
        </tr>
        <tr><td colspan="2" width="1050px" height="400px"><div id="advocacyTrend" style="width:1050px; height:400px;"></div></td></tr>
        </table>
    </div>
</div>
<div id="advocacyDetails">
<table>
<tr>
<!--<td width="18%" valign="top">
    <strong>Advocacy List</strong>
    <div id="advocacyListDiv"></div>
    <strong>Task List</strong>
    <div id="taskListDiv"></div>
</td>-->

<td width="100%" valign="top">
    <table id="box-table-a" width="925px"  style="margin: 4px 4px 0;">
        <tr>
            <td width="490" valign="top"><strong>Advocacy Name</strong><br><div id="advocacyNameDiv"></div></td>
            <td width="200" valign="top"><strong>Category</strong><br><div id="categoryDiv"></div></td>
            <td width="200" valign="top"><strong>Type</strong><br><div id="typeDiv"></div></td>
            <td width="200" valign="top"><strong>Owner</strong><br><div id="ownerDiv"></div></td>
            
            <td width="35" valign="top">
             <?php if($view == "Application") {?>
            	<img src="images/icons/edit.png" onClick="editAdvocacy()" alt="Edit">
            <?php }
			else if($view == "Administrator" || $view == "Board")
			{?>
                <img src="images/icons/edit.png" onClick="editAdvocacy()" alt="Edit">
                <img src="images/icons/delete.png" onClick="deleteAdvocacy()" alt="Delete">		
			<?php } ?>
            </td>
        </tr>
    </table>
    <table id="box-table-b" width="925px" style="margin: 0 4px 0;">
        <tr>
            
            <td width="800" valign="top"><strong>Meaning</strong><br><div id="meaningDiv"></div></td>
            <td width="200" valign="top"><strong>Implementing Agency</strong><br><div id="agencyDiv"></div></td>
        </tr>
    </table>
   
    <table id="box-table-c" width="925px" style="margin: 0 4px 0;">
        <tr>
            <td width="267" valign="top"><strong>Start Date</strong><br><div id="advocacyStartDateDiv"></div></td>
            <td width="267" valign="top"><strong>End Date</strong><br><div id="advocacyEndDateDiv"></div></td>
            <td width="266" valign="top"><strong>Completion Date</strong><br><div id="advocacyCompletionDateDiv"></div></td>
            <td width="400" valign="top"><strong>Status</strong><br><div id="statusDiv"></div></td>
            <td width="100" valign="top"><div id="advocacyGauge"></div></td>
        </tr>
    </table>
</td>
</tr>
</table>
</div>
<div class="dijitHidden">
	<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New Item'" id="newAdvocacyDialog" style="font-size:11px;">
        <div id="advocacyEditType" style="display:none;"></div>
        <div id="advocacyIdDiv" style="display:none"></div>
        <div id="ownerId" style="display:none"></div>
        <table id="newAdvocacyDialog-table">
    <tr>
        <td width="9%" align='right'><strong>Advocacy Name</strong></td>
        <td width="24%" id="advocacyName" colspan='3'><input type='text' style='width:90%' id='advocacyNameInput'/></td>
        <td width="9%" valign="top" align='right'><strong>Meaning</strong></td>
       <!-- <td width="24%" id="advocacyMeaning" rowspan='5'><textarea id='advocacyMeaningInput' rows='9' cols='33'></textarea></td>-->
		 <td width="24%" id="advocacyMeaning" rowspan='5'><div contenteditable="true" id='advocacyMeaningInput' style="width:300px; height:200px; padding:5px; border:1px solid #ccc;"></div></td>
     </tr> 
	 <tr>
		<td width='9%' valign='top' align='right' valign='center'><strong>Category</strong></td>
        <td width="24%" id="advocacyCategory">
        	<div id="advocacyCategoryInput"></div>
        </td>
        <td width="9%" align='right' valign='center'><strong>Type</strong></td>
        <td width="24%" id="advocacyType">
        	<select id="advocacyTypeInput">
            	<option value="Policy">Policy</option>
                <option value="Bill">Bill</option>
                <option value="Project">Project</option>
                <option value="Ease of Doing Business">Ease of Doing Business</option>
                <option value="Enhancing Security">Enhancing Security</option>
                <option value="Improving Governance and Rule of Law">Improving Governance and Rule of Law</option>
                <option value="Infrastructure Development">Infrastructure Development</option>
                <option value="Revamping the Micro, Small and Medium Size Enterprises Sector">Revamping the Micro, Small and Medium Size Enterprises Sector</option>
                <option value="Promoting Productivity and Value Addition in Agriculture">Promoting Productivity and Value Addition in Agriculture</option>
                <option value="Natural Resources Management">Natural Resources Management</option>
                <option value="Expanding Trade and Investment">Expanding Trade and Investment</option>
                <option value="Human Capital Development for Competiveness">Human Capital Development for Competiveness</option>
                <option value="Towards a Culture of High Performance">Towards a Culture of High Performance</option>
            </select>    
        </td>
		
	 </tr>
     <tr>
        <td width="9%" align='right' valign='center'><strong>Owner</strong></td>
        <td width="24%" id="advocacyOwner"><input type='text' style='width:90%' id='advocacyOwnerInput'/></td>
        <td width="9%"  align='right'><strong>Implementing Agency</strong></td>
        <td width="24%" id="advocacyAgency"><input type='text' style='width:90%' id='advocacyAgencyInput'/></td>
		
	</tr>
     <tr>
          
        <td width="9%" align='right'><strong>Start Date</strong></td>
        <td width="24%" id="advocacyStart">        
            <input id="advocacyStartInput" data-dojo-id="advocacyStartDojo" type="text" data-dojo-type="dijit/form/DateTextBox" 
            onChange="advocacyDueDojo.constraints.min = arguments[0]; advocacyCompleteDojo.constraints.min = arguments[0];" data-dojo-props="constraints:{datePattern: 'dd-MMM-yyyy'}"/>
        </td>
        <td width="9%" align='right'><strong>Due Date</strong></td>
        <td width="24%" id="advocacyDue">
            <input id="advocacyDueInput" data-dojo-id="advocacyDueDojo" type="text" data-dojo-type="dijit/form/DateTextBox" 
            onChange="advocacyStartDojo.constraints.max = arguments[0];" data-dojo-props="constraints:{datePattern: 'dd-MMM-yyyy'}"/>
        </td>
        
    </tr>
	<tr>
		<td width="9%" align='right'><strong>Completion Date</strong></td>
        <td width="24%" id="advocacyComplete">
        <input id="advocacyCompleteInput" data-dojo-id="advocacyCompleteDojo" type="text" data-dojo-type="dijit/form/DateTextBox" 
            data-dojo-props="constraints:{datePattern: 'dd-MMM-yyyy'}"/>
       </td>
	   <td width="9%" align='right' valign='center'><strong>Status</strong></td>
        <td width="24%" id="advocacyStatus">
        	<select id="advocacyStatusInput">
            	<option value="Identified">Identified</option>
                <option value="Request Made to Government">Request Made to Government</option>
                <option value="Government Committed">Government Committed</option>
                <option value="Implementation in Progress">Implementation in Progress</option>
                <option value="Issue Resolved">Issue Resolved</option>
            </select>
        </td>
	</tr>
</table>
		<button data-dojo-type="dijit/form/Button" onClick="saveAdvocacy()" type="submit">Finish</button>
   </div>
</div> 

<div style="clear:left"></div>
<div id="divConversation" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Advocacy Conversations', open: false" style=" margin-left:-2px; display:none;">
	<!--<div id="userId" style="display:none;"><?php //echo "ind".$loggedInUser->user_id; ?></div>-->
<div id="conversationHistory"></div>
<table width="100%">
   <tr>
    <td colspan="">
        <div contenteditable="true" id='conversation' style="width:90%; height:50px; padding:5px; border:1px solid #ccc; overflow-y:scroll;"></div>
    </td>				
   </tr>
   <tr>
    <td colspan="" align="left"><button type='submit' onClick="postComment()" id="submitId">Post Comment</button></td>
   </tr>
</table>
</div>

<div class="dijitHidden">
	<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'Edit Category'" id="categoryRenameDialog" style="font-size:11px;">
        <table>
        <tr>
            <td width="12%" valign="top"><strong>Category's New Name</strong></td>
            <td width="24%"><input type='text' style='width:90%' id='categoryRenameInput'/><div id="categoryName" style="display:none"></div></td>
        </tr> 
		</table> 
        <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:renameCategory" type="submit">Finish</button>  
    </div>
</div>

</body>
</html>