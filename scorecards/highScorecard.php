<?php 
require_once("../admin/models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
$userPermission = fetchUserPermissions($loggedInUser->user_id);
$view = "True";
foreach($userPermission as $id)
{
	if($id["permission_id"] == "2" || $id["permission_id"] == "3" || $id["permission_id"] == "3000")
	$view = "False";
}
if(@$userPermission[1]["permission_id"] == "2" && $view == "True") $view = "False";
if(@$userPermission[1]["permission_id"] == "3" && $view == "True") $view = "False";
if(@$userPermission[1]["permission_id"] == "3000" && $view == "True") $view = "False";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=10,IE=9,IE=8" />
<link rel="stylesheet" href="https://accent-analytics.com/dijit/themes/soria/soria.css">
<style>
	@import "https://accent-analytics.com/dojox/grid/resources/Grid.css";
	@import "https://accent-analytics.com/dojox/grid/resources/soriaGrid.css";
	@import "css/pageLayout.css";
	@import "css/tableStyle.css";
	@import "https://accent-analytics.com/dojox/editor/plugins/resources/css/PageBreak.css";
	@import "https://accent-analytics.com/dojox/editor/plugins/resources/css/ShowBlockNodes.css";
	@import "https://accent-analytics.com/dojox/editor/plugins/resources/css/Preview.css";
	@import "https://accent-analytics.com/dojox/editor/plugins/resources/css/InsertEntity.css";
	@import "https://accent-analytics.com/dojox/editor/plugins/resources/css/FindReplace.css";
	@import "https://accent-analytics.com/dojox/editor/plugins/resources/css/Breadcrumb.css";
	@import "https://accent-analytics.com/dojox/editor/plugins/resources/css/CollapsibleToolbar.css";
	@import "https://accent-analytics.com/dojox/editor/plugins/resources/css/Blockquote.css";
	@import "https://accent-analytics.com/dojox/editor/plugins/resources/css/PasteFromWord.css";
	@import "https://accent-analytics.com/dojox/editor/plugins/resources/css/TextColor.css";
	@import "https://accent-analytics.com/dojox/editor/plugins/resources/css/InsertAnchor.css";
	@import "https://accent-analytics.com/dojox/editor/plugins/resources/editorPlugins.css";
	@import "https://accent-analytics.com/dojox/editor/plugins/resources/css/StatusBar.css"; 
	@import "https://accent-analytics.com/dojox/editor/plugins/resources/css/LocalImage.css";
	@import "https://accent-analytics.com/dojox/form/resources/FileUploader.css";
	@import "https://accent-analytics.com/dojox/editor/plugins/resources/css/SafePaste.css";
	/*.dojoxLegendNode {border: 1px solid #ccc; margin: 5px 10px 5px 10px; padding: 3px}
	.dojoxLegendText {vertical-align: text-top; padding-right: 10px}*/
</style>
<style type="text/css">
.myButtons .dijitButtonNode
{
    width:160px;
	font-size:12px;
}
.menuList li {
  display: block;
  margin: 0 -22px;
  overflow: visible;
}
.menuList li + li {
  border-top: 1px solid #ccc;
  width:150px;
}
.menuList li a {
  color: #555;
  padding: 0px 18px;
  margin: 0 -18px;
  text-decoration: none;
  font: 13px/20px 'Lucida Grande', Tahoma, Verdana, sans-serif;
 }
.menuList li a:hover {
  color: blue;
}
.myFont{
	font-size: 11pt;
	font-family: Calibri;
	font-weight: 100;
}
.csvTable {
	border: 1px solid #B0CBEF;
	border-width: 1px 0px 0px 1px;
	font-size: 11pt;
	font-family: Calibri;
	font-weight: 100;
	border-spacing: 0px;
	border-collapse: collapse;
}
.csvTable TH {
	background-image: url(images/excel-2007-header-bg.gif);
	background-repeat: repeat-x; 
	font-weight: normal;
	font-size: 14px;
	border: 1px solid #9EB6CE;
	border-width: 0px 1px 1px 0px;
	height: 17px;
}
.csvTable TD {
	border: 0px;
	background-color: white;
	padding: 0px 4px 0px 2px;
	border: 1px solid #D0D7E5;
	border-width: 0px 1px 1px 0px;
}
.csvTable TD B {
	border: 0px;
	background-color: white;
	font-weight: bold;
}
.csvTable TD.heading {
	background-color: #E4ECF7;
	text-align: center;
	border: 1px solid #9EB6CE;
	border-width: 0px 1px 1px 0px;
}
.csvTable TH.heading {
	background-image: url(images/excel-2007-header-left.gif);
	background-repeat: none;
}

</style>
<!--<script src="../../highCharts414/js/adapters/standalone-framework.js"></script>
<script src="../../highCharts414/js/highcharts.js"></script>
<script src="../../highCharts414/js/highcharts-more.js"></script>
<script src="../../highCharts404/js/modules/drilldown.js"></script>
<script src="../../highCharts414/js/themes/sand-signika.js"></script>-->

<script src="js/measure.js"></script>
<script type="text/javascript">
//var gaugeValue;
//var gauge3, gauge4, chart;
//var indicators;

var layout, toSave, dbBaseline, dbTarget, dbStretch, dbBest, csvGrid, gridTrue, savingId;//csvImport
require([
"dijit/registry",
"dijit/form/HorizontalSlider",

"dojo/dom-construct",
'dojo/date',
'dojo/date/locale',
"dojo/window",
"dojo/dom",
"dojo/on",
"dojo/dom-style",
"dojo/parser",
"dojo/request",
"dojo/aspect",

'dojo/_base/lang',
'dojo/_base/connect', 
"dijit/Dialog",
"dijit/TooltipDialog",
"dijit/popup",

'dojox/fx/scroll',
'dojox/fx',
//for the chart		

"dojox/gauges/BarGauge",
"dojox/gauges/BarIndicator",

//Editor Plugins
"dijit/InlineEditBox",
"dijit/Editor",
"dijit/_editor/plugins/AlwaysShowToolbar",
"dijit/_editor/plugins/Print",
"dijit/_editor/plugins/FontChoice",
"dijit/_editor/plugins/EnterKeyHandling",
"dijit/_editor/plugins/ToggleDir",
"dojox/editor/plugins/ToolbarLineBreak",
"dojox/editor/plugins/TablePlugins",
"dojox/editor/plugins/PageBreak",
"dojox/editor/plugins/ShowBlockNodes",
"dojox/editor/plugins/Preview",
"dojox/editor/plugins/InsertEntity",
"dojox/editor/plugins/Blockquote",
"dijit/_editor/plugins/LinkDialog",
"dojox/editor/plugins/NormalizeIndentOutdent",
"dojox/editor/plugins/FindReplace",
"dojox/editor/plugins/Breadcrumb",
"dojox/editor/plugins/PasteFromWord",
"dojox/editor/plugins/TablePlugins",
"dojox/editor/plugins/NormalizeStyle",
"dojox/editor/plugins/TextColor",
"dojox/editor/plugins/InsertAnchor",
"dojox/editor/plugins/StatusBar",
"dojox/editor/plugins/LocalImage",
"dojox/editor/plugins/UploadImage",
"dojox/editor/plugins/AutoUrlLink",
"dojox/editor/plugins/ResizeTableColumn",
"dojox/editor/plugins/SafePaste",
"dojox/json/query",

"dojo/domReady!"],
function(registry, HorizontalSlider, domConstruct, date, locale, win, dom, on, domStyle, parser, request, aspect, lang, connect, Dialog, TooltipDialog, popup, scroll, highlight, BarGauge, BarIndicator, InlineEditBox, Editor){

//if(dijit.byId("appLayout")){dijit.byId("appLayout").destroyRecursive();}
//domStyle.set(dom.byId("auditButton"), "width", "200px");
//domStyle.set(dom.byId("auditButton"), "display", "none");
//domStyle.set(dom.byId("updateButton"), 'width', '200px');
/*
if(dijit.byId("bulkMeasureDialogGoal")) dijit.byId("bulkMeasureDialogGoal").destroyRecursive();
if(dijit.byId("bulkMeasureDialog2")) dijit.byId("bulkMeasureDialog2").destroyRecursive();
if(dijit.byId("bulkMeasureDialog3")) dijit.byId("bulkMeasureDialog3").destroyRecursive();
if(dijit.byId("bulkMeasureDialog4")) dijit.byId("bulkMeasureDialog4").destroyRecursive();*/
if(dijit.byId("updateButton")) dijit.byId('updateButton').destroy(true);
//if(dijit.byId("auditButton")) dijit.byId('auditButton').destroy(true);
if(dijit.byId("kpiAuditTrailButton")) dijit.byId('kpiAuditTrailButton').destroyRecursive();
if(dijit.byId("kpiAuditTrailDialog")) dijit.byId('kpiAuditTrailDialog').destroyRecursive();
if(dijit.byId("previousCheckbox")) dijit.byId('previousCheckbox').destroyRecursive();
if(dijit.byId("divConversation")) dijit.byId('divConversation').destroyRecursive();

//parser.parse();
//***************************************************************************************************###
//The chart	
//Trying HighCharts. LTK 23-05-2015
//if(chart) {chart.destroy();}
chart = new Highcharts.Chart({
//chart = new Highstock.stockChart({
chart: {
	renderTo: 'chartDiv'
	},
title: {
	text: null
},
subtitle: {
	text: null
},
xAxis: {
	//type: 'datetime',
	min: 0, 
	max: 11
},
yAxis: {
	gridLineColor: 'transparent',
	min:0,
	max:10,
	endOnTick: false,
	startOnTick: false,
	//minPadding: 0,
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
	/*formatter: function() {
		var point = this.point,
			s = this.x +': <b>'+ this.y +'</b><br/>';
		return s;
	},*/
	crosshairs: true,
    shared: true
},
series: [
{//0
	name: 'Score',
	type: 'spline',
	color: 'black',
	shadow: {
            color: 'white',
            width: 8,
            offsetX: 0,
            offsetY: 0
        },
	//lineWidth: 5,
	zIndex: 5
},
{//1 - using arearanges since plot bands do not allow for moving values (changing targets in this case)
	name: 'Red',
	type: 'arearange',
	pointPlacement: -0.5,
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
	type: 'arearange',
	pointPlacement: -0.5,
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
	type: 'arearange',
	pointPlacement: -0.5,
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
	type: 'arearange',
	pointPlacement: -0.5,
	color: 'darkgreen',
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
},
{//7
	name: 'Blue',
	type: 'arearange',
	color: 'Blue',
	lineWidth: 0,
	marker: {
       enabled: false
    },
	enableMouseTracking: false,
	zIndex: 0
}],
exporting: {enabled: false},
credits: {enabled: false},
legend:{enabled: false},
plotOptions: {
	spline: {
		dataLabels: {
			enabled: true,
			style: {
			color: 'black',
			textShadow: '0px 1px 2px white'
			}
		}
	}
}
});
//domConstruct.destroy('chart');
//***************************************************************************************************###
//The Gauge

Highcharts.theme1 = {
   chart: {
        backgroundColor: null,
		backgroundImage: "images/themes/sand.png"
	}
}

Highcharts.setOptions(Highcharts.theme1);

gauge = new Highcharts.Chart
({
	chart: 
	{
		renderTo: 'myGauge',
		type: 'gauge',
		//backgroundImage: null,
		//backgroundColor: null,
		height: 180,
		width: 180,
		margin: [0,0,0,0],
		spacingBottom: 0,
	},
	title: {text: null},
	pane: 
	{
//	        center: ['50%', '100%'],
		startAngle: -90,
		endAngle: 90,
		background: null
	}, 
	plotOptions: {
		gauge: {
			 dataLabels: {enabled: true},
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
			enabled: false,
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
			to: 2,
			outerRadius: '100%',
			innerRadius: '1%',
			color: '#ff0000', // red
			id:'red'
		}, {
			from: 2,
			to: 4,
			outerRadius: '100%',
			innerRadius: '1%',
			color: '#FFD900', // yellow
			id: 'yellow'
		}, 
		{
			from: 4,
			to: 6,
			outerRadius: '100%',
			innerRadius: '1%',
			color: '#33CC00', // green
			id: 'green'
		}, 
		{
			from: 6,
			to: 8,
			outerRadius: '100%',
			innerRadius: '1%',
			color: '#006600', // darkGreen
			id: 'darkGreen'
		}, 
		{
			from: 8,
			to: 10,
			outerRadius: '100%',
			innerRadius: '1%',
			color: '#0000FF', // blue
			id: 'blue'
		}]        
	},
	series: 
	[{
		score: 'Score',
		data: [1.5]
	}],
	credits: {enabled: false}
});
gauge.renderer.image('images/gaugeOverlay.png', 0, 0, 177, 99).add().attr({zIndex:100});

//gauge.plotBGImage.hide();
/***********************************************************************************
Editor Saving Functions.
************************************************************************************/				
function editorHandler(editorContent)
{
	//if(interpretationSavingId.value == null) interpretationSavingId.set("value", kpiGlobalId);
	//var saveId = dom.byId("intepretationSave");
	//alert(interpretationSavingId.value);
	//console.log(kpiGlobalId + ', content for Intepretation: ' + editorContent);
	request.post("scorecards/save-editor-content.php",{
	//handleAs: "json",
	data: {
		Type: "interpretation",
		//objectId: interpretationSavingId.value,
		objectId: kpiGlobalId,
		saveContent: editorContent,
		period: period,
		creator: dom.byId('userIdJs').innerHTML
		}						
		}).then(function(data) 
		{
			/*
			dom.byId("msgContent").innerHTML = "Interpretation comments successfully saved.";
			domStyle.set(dom.byId("msgContent"), 'display', 'block');
			var msgTimeout = setTimeout(function(){
					domStyle.set(dom.byId("msgContent"), 'display', 'none');
			},2000);*/
		});
}

function editorHandler2(editorContent2)
{
	//if(wayForwardSavingId.value == null) wayForwardSavingId.set("value", kpiGlobalId);
	//var saveId = dom.byId("intepretationSave");
	//console.log(kpiGlobalId + ', content for wayForward: ' + editorContent2);
	//alert(interpretationSavingId.value);
	request.post("scorecards/save-editor-content.php",{
	//handleAs: "json",
	data: {
		Type: "wayForward",
		objectId: kpiGlobalId,
		saveContent: editorContent2,
		period: period,
		creator: dom.byId('userIdJs').innerHTML
	}						
	}).then(function(data) 
	{/*
		dom.byId("msgContent").innerHTML = "Way forward comments successfully saved.";
		domStyle.set(dom.byId("msgContent"), 'display', 'block');
		var msgTimeout = setTimeout(function(){
				domStyle.set(dom.byId("msgContent"), 'display', 'none');
		},2000);*/
	});
}
/***********************************************************************************
Create Editor for Notes.
************************************************************************************/				
if(dijit.byId("interpretation")){
dijit.byId("interpretation").destroy(true);
}
if(view == "True")
{
	var notesEditor = new InlineEditBox({
	renderAsHtml: true,
	autoSave: false,
	disabled:true,
	}, "interpretation");
}
else
{
	var notesEditor = new InlineEditBox({
	editor: Editor,
	renderAsHtml: true,
	autoSave: false,
	onChange: editorHandler,
	onClick: editorScroll,
	//url: "editorSave.txt",
	//name: "notesEditorContent",
	//data-myid:'mainEditor',
	//title:"Editor",
	editorParams: {height: '90', 
	extraPlugins:['dijit._editor.plugins.AlwaysShowToolbar', 
		'preview', 'print', 'selectAll', 'findreplace', 'pastefromword', '|',
		'pageBreak', 'insertHorizontalRule', 'blockquote', 'toggleDir', 
		'superscript', 'subscript', 'foreColor', 'hiliteColor', 'removeFormat', '||',
		'delete', 'fontName', 'fontSize', 'formatBlock', '||',
		'insertEntity', 'createLink', 'unlink', 'insertanchor', 
		//{name: 'dojox.editor.plugins.UploadImage', command: 'uploadImage',					
		{name: 'dojox.editor.plugins.UploadImage', command: 'uploadImage', 
		uploadable:true,
		uploadUrl: 'https://accent-analytics.com/dojo10.3/dojox/form/tests/UploadFile.php', 
		//baseImageUrl: 'https://accent-analytics.com/dojo_1_9_1/dojox/form/tests/',
		downloadPath: 'https://accent-analytics.com/dojo10.3/dojox/form/tests/uploads/',
		tempImageUrl:'../dojox/editor/plugins/resources/images/busy.gif'
		},
		'insertTable', 'modifyTable', 'deleteTableRow', 'deleteTableColumn', 'colorTableCell', 
		'tableContextMenu', 'insertTableColumnBefore', 'insertTableColumnAfter',
		{name: 'dojox.editor.plugins.TablePlugins', command: 'InsertTableRowBefore'},
		{name: 'dojox.editor.plugins.TablePlugins', command: 'InsertTableRowAfter'},
		{name: 'dojox.editor.plugins.TablePlugins', command: 'resizeTableColumn'},]},
	}, "interpretation");
}
/*var saveEditor = dijit.byId("interpretation");
notesEditor.on('save', function(){
	console.log('Save :-)');
	})
notesEditor.on('cancel', function(){
	console.log('Cancel :-)');
	})
*/
if(dijit.byId("wayForward")) dijit.byId("wayForward").destroy(true);

if(view == "True")
{
	var notesEditor2 = new InlineEditBox({
	renderAsHtml: true,
	autoSave: false,
	disabled:true,
	}, "wayForward");
}
else
{
	var notesEditor2 = new InlineEditBox({
	editor: Editor,
	renderAsHtml: true,
	autoSave: false,
	onChange: editorHandler2,
	onClick: editorScroll,
	//data-myid:'mainEditor',
	//title:"Editor",
	editorParams: {height: '90', 
	extraPlugins:['dijit._editor.plugins.AlwaysShowToolbar', 
		'preview', 'print', 'selectAll', 'findreplace', 'pastefromword', '|',
		'pageBreak', 'insertHorizontalRule', 'blockquote', 'toggleDir', 
		'superscript', 'subscript', 'foreColor', 'hiliteColor', 'removeFormat', '||',
		'delete', 'fontName', 'fontSize', 'formatBlock', '||',
		'insertEntity', 'createLink', 'unlink', 'insertanchor', 
		{name: 'dojox.editor.plugins.UploadImage', command: 'uploadImage', 
		uploadable:true,
		uploadUrl: 'https://accent-analytics.com/dojo10.3/dojox/form/tests/UploadFile.php', 
		//baseImageUrl: 'https://accent-analytics.com/dojo_1_9_1/dojox/form/tests/',
		downloadPath: 'https://accent-analytics.com/dojo10.3/dojox/form/tests/uploads/',
		tempImageUrl:'../dojox/editor/plugins/resources/images/busy.gif'
		},
		'insertTable', 'modifyTable', 'deleteTableRow', 'deleteTableColumn', 'colorTableCell', 
		'tableContextMenu', 'insertTableColumnBefore', 'insertTableColumnAfter',
		{name: 'dojox.editor.plugins.TablePlugins', command: 'InsertTableRowBefore'},
		{name: 'dojox.editor.plugins.TablePlugins', command: 'InsertTableRowAfter'},
		{name: 'dojox.editor.plugins.TablePlugins', command: 'resizeTableColumn'},]},
	}, "wayForward");

/***********************************************************************************
End of Editor.
************************************************************************************/
}
domStyle.set(dom.byId("divDevelopmentPlan"), "display", "none");

function editorScroll()
{
	var delayFunction = setTimeout(function() 
	{
		dom.byId("scrollIntoView").scrollIntoView();
	},100);
}
aspect.after(editorScroll)
{dom.byId("scrollIntoView").scrollIntoView();}

changePlot = function()
{
	var type = dojo.byId("plot").value		
	if(type == "Smart(XmR) Chart")
	{
		chartType = "XmR";
		updateChart();				
	}
	else if (type == "Lines")
	{
		chartType = "9Steps";
		updateChart();
	}
}

csvImport = function()
{
	//domStyle.set(registry.byId("csvDropdown").domNode, 'display', 'none');
	csvImportVar = 'true';
	registry.byId("csvImport").show();
	request.post("scorecards/get-kpi-details.php",{
	handleAs: "json",
	data: {
		kpiId: kpiGlobalId
	}						
	}).then(function(kpiData) 
	{
		gaugeType = kpiData.gaugeType;
		dbBaseline = kpiData.baseline;
		dbTarget = kpiData.target;
		dbStretch = kpiData.stretch;
		dbBest = kpiData.best;
		savingId = kpiData.id;
		savingId = savingId++;
		switch(kpiData.gaugeType)
		{
			case "goalOnly":
			{
				layout = [[
					{ field: "date", name: "Date", width: 10 },
					{ field: "actual", name: "Actual", width: 10, editable: true },
					{ field: "target", name: "Target", width: 10 }
				  ]];
				dom.byId("csvFormat").innerHTML = "<span class='myFont'>Measure Name: <b>"+ kpiData.name + "</b><br>Instructions<ol><li>Arrange your csv file as follows before uploading your data (additional columns will be ingored)</li><li>Empty Baseline and Target cells will be saved with default values while rows with empty actual figures will be ignored</li><li>Dates should be in the format m/d/yyyy</li></ol></span><br><table class='csvTable' width='40%'><tr><th class='heading'>&nbsp;</th><th>A</th><th>B</th><th>C</th></tr><tr><td align='left' valign='bottom' class='heading'>1</td><td>date</td><td>actual</td><td>target</td></tr><tr><td align='left' valign='bottom' class='heading'>2</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td align='left' valign='bottom' class='heading'>3</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td align='left' valign='bottom' class='heading'>4</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></table>";
				break;	
			}
			case "threeColor":
			{
				layout = [[
					{ field: "date", name: "Date", width: 10 },
					{ field: "actual", name: "Actual", width: 10, editable: true },
					{ field: "baseline", name: "Baseline", width: 10 },
					{ field: "target", name: "Target", width: 10 }
				  ]];
				dom.byId("csvFormat").innerHTML = "<span class='myFont'>Measure Name: <b>"+ kpiData.name + "</b><br>Instructions<ol><li>Arrange your csv file as follows before uploading your data (additional columns will be ingored)</li><li>Empty Baseline and Target cells will be saved with default values while rows with empty actual figures will be ignored</li><li>Dates should be in the format mm/dd/yyyy</li></ol></span><br><table class='csvTable' width='40%'><tr><th class='heading'>&nbsp;</th><th>A</th><th>B</th><th>C</th><th>D</th></tr><tr><td align='left' valign='bottom' class='heading'>1</td><td>date</td><td>actual</td><td>baseline</td><td>target</td></tr><tr><td align='left' valign='bottom' class='heading'>2</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td align='left' valign='bottom' class='heading'>3</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td align='left' valign='bottom' class='heading'>4</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></table>";
				break;	
			}
			case "fourColor":
			{
				layout = [[
					{ field: "date", name: "Date", width: 10 },
					{ field: "actual", name: "Actual", width: 10, editable: true },
					{ field: "baseline", name: "Baseline", width: 10 },
					{ field: "target", name: "Target", width: 10 },
					{ field: "stretch_target", name: "Stretch Target", width: 10 }
				  ]];
				  dom.byId("csvFormat").innerHTML = "<span class='myFont'>Measure Name: <b>"+ kpiData.name + "</b><br>Instructions<ol><li>Arrange your csv file as follows before uploading your data (additional columns will be ingored)</li><li>Empty Baseline and Target cells will be saved with default values while rows with empty actual figures will be ignored</li><li>Dates should be in the format mm/dd/yyyy</li></ol></span><br><table class='csvTable' width='40%'><tr><th class='heading'>&nbsp;</th><th>A</th><th>B</th><th>C</th><th>D</th><th>E</th></tr><tr><td align='left' valign='bottom' class='heading'>1</td><td>date</td><td>actual</td><td>baseline</td><td>target</td><td>stretch_target</td></tr><tr><td align='left' valign='bottom' class='heading'>2</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td align='left' valign='bottom' class='heading'>3</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td align='left' valign='bottom' class='heading'>4</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></table>";
				break;	
			}
			case "fiveColor":
			{
				layout = [[
					{ field: "date", name: "Date", width: 10 },
					{ field: "actual", name: "Actual", width: 10, editable: true },
					{ field: "baseline", name: "Baseline", width: 10 },
					{ field: "target", name: "Target", width: 10 },
					{ field: "stretch_target", name: "Stretch Target", width: 10 },
					{ field: "best_value", name: "Best Value", width: 10 }
				  ]];
				  dom.byId("csvFormat").innerHTML = "<span class='myFont'>Measure Name: <b>"+ kpiData.name + "</b><br>Instructions<ol><li>Arrange your csv file as follows before uploading your data (additional columns will be ingored)</li><li>Empty Baseline and Target cells will be saved with default values while rows with empty actual figures will be ignored</li><li>Dates should be in the format mm/dd/yyyy</li></ol></span><br><table class='csvTable' width='40%'><tr><th class='heading'>&nbsp;</th><th>A</th><th>B</th><th>C</th><th>D</th><th>E</th><th>F</th></tr><tr><td align='left' valign='bottom' class='heading'>1</td><td>date</td><td>actual</td><td>baseline</td><td>target</td><td>stretch_target</td><td>best</td></tr><tr><td align='left' valign='bottom' class='heading'>2</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td align='left' valign='bottom' class='heading'>3</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td align='left' valign='bottom' class='heading'>4</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></table>";
				break;	
			}
		}
	});	
	//hideFileUploadDialog();
}

hideFileUploadDialog = function()
{
	registry.byId("newCsvFileDialog").onCancel();
	registry.byId("csvImport").show();
	//if(dijit.byId("gridContainer")) dijit.byId("gridContainer").destroy();
	var csvFile = dijit.byId("csvUploader").getFileList();
	csvFile = "../upload/images/" + csvFile[0].name;
	//csvFile = "upload/images/kpi3.csv";
	var csvStore = new CsvStore({url:csvFile});
	//var dataStore = new Memory({ data: csvStore });
	if(gridTrue == "True")
	{
		csvGrid.set("store", csvStore);
		//grid.store = csvStore;
		csvGrid._refresh();
	}
	else
	{
		domStyle.set(dom.byId("gridContainer"), "display", "block");
		gridTrue = "True"
		csvGrid = new DataGrid({
			id: 'csvGrid',
			store: csvStore,
			structure: layout
		},'gridContainer');
		csvGrid.startup();
	}
	function isValidDate(dateString)
	{
		// First check for the pattern
		if(!/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(dateString))
			return false;

		// Parse the date parts to integers
		var parts = dateString.split("/");
		var day = parseInt(parts[1], 10);
		var month = parseInt(parts[0], 10);
		var year = parseInt(parts[2], 10);
	
		// Check the ranges of month and year
		if(year < 1900 || year > 3000 || month == 0 || month > 12) return false;
	
		var monthLength = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];
	
		// Adjust for leap years
		if(year % 400 == 0 || (year % 100 != 0 && year % 4 == 0)) monthLength[1] = 29;
	
		// Check the range of the day
		return day > 0 && day <= monthLength[month - 1];
	};
	function isNumeric(n) {
	  return !isNaN(parseFloat(n)) && isFinite(n);
	}
	function completed(items, findResult)
	{
		switch(gaugeType)
		{
			case "goalOnly":
			{
				var dateError = 0, actualError = 0, targetError = 0, csvErrors = "";
				var csvAttributes = "\'" + csvStore.getAttributes(items[0]) + "\'";
				csvAttributes = csvAttributes.split(",")
				//console.log(csvAttributes[0] + "; " + csvAttributes[1] + "; " + csvAttributes[2] + "; " + csvAttributes[3]);
				if(csvAttributes[0] != "\'date") csvErrors = csvErrors + "First column should be labeled 'date'";
				if(csvAttributes[1] != "actual") csvErrors = csvErrors + "Second column should be labeled 'actual'";
				if(csvAttributes[2] != "target\'") csvErrors = csvErrors + "Third column should be labeled 'target'";
				toSave = "[";
				var lastI = items.length - 1;
				var lastActual = csvStore.getValue(items[lastI], "actual");
				for(var i = 0; i < items.length; i++)
				{
					var date = csvStore.getValue(items[i], "date");
					var actual = csvStore.getValue(items[i], "actual");
					var target = csvStore.getValue(items[i], "target");
					if(actual == ' ' || actual == null || actual == undefined)
					{
						//do nothing
					}
					else
					{
						if(target == ' ' || target == null || target == undefined) target = dbTarget;
						
						if(isValidDate(date) == false) dateError++;
						if(isNumeric(actual) == false) actualError++;
						if(isNumeric(target) == false) targetError++;
						if(i < items.length-1)
						{
							if(lastActual == undefined && i == items.length-2)
							toSave = toSave + "{\"id\": " + savingId + ", \"date\": \"" + date + "\", \"actual\": " + actual + ", \"green\": " + target + "}";
							else
							toSave = toSave + "{\"id\": " + savingId + ", \"date\": \"" + date + "\", \"actual\": " + actual + ", \"green\": " + target + "},";
						}
						else toSave = toSave + "{\"id\": " + savingId + ", \"date\": \"" + date + "\", \"actual\": " + actual + ", \"green\": " + target + "}";
						savingId++;
					}
				}
			  toSave = toSave + "]";
			  //console.log(toSave);
			  if(dateError > 0) 
			  {
				  if(dateError == 1)
				  csvErrors = csvErrors + "<br>One of the dates is not in the correct format";
				  else
				  csvErrors = csvErrors + "<br>" + dateError + " dates are not in the correct format";
			  }
			  if(actualError > 0) 
			  {
				  if(actualError == 1)
				  csvErrors = csvErrors + "<br>One of the actual values is not numeric";
				  else
				  csvErrors = csvErrors + "<br>" + actualError + " actual values are not numeric";
			  }
			  dom.byId("csvErrors").innerHTML = csvErrors;
			  if(dateError == 0 && actualError == 0 && targetError == 0)
				dijit.byId("csvSaveButton").set('disabled', false);
			  else dijit.byId("csvSaveButton").set('disabled', true);
				break;	
			}
			case "threeColor":
			{
				//console.log("Testing functions: " + "1. getLabel => " + csvStore.getLabel(items[0]) + "2. getIdentity => " + csvStore.getIdentity(items[0]) + "3. getFeatures => " + JSON.stringify(csvStore.getFeatures())+ "4. getAttributes => " + csvStore.getAttributes(items[0]));
				//console.log("getAttributes => " + csvStore.getAttributes(items[0]));
				var dateError = 0, actualError = 0, baselineError = 0, targetError = 0, csvErrors = "";
				var csvAttributes = "\'" + csvStore.getAttributes(items[0]) + "\'";
				csvAttributes = csvAttributes.split(",")
				console.log(csvAttributes[0] + "; " + csvAttributes[1] + "; " + csvAttributes[2] + "; " + csvAttributes[3]);
				if(csvAttributes[0] != "\'date") csvErrors = csvErrors + "First column should be labeled 'date'";
				if(csvAttributes[1] != "actual") csvErrors = csvErrors + "Second column should be labeled 'actual'";
				if(csvAttributes[2] != "baseline") csvErrors = csvErrors + "Third column should be labeled 'baseline'";
				if(csvAttributes[3] != "target\'") csvErrors = csvErrors + "Fourth column should be labeled 'target'";
				toSave = "[";
				var lastI = items.length - 1;
				var lastActual = csvStore.getValue(items[lastI], "actual");
				for(var i = 0; i < items.length; i++)
				{
					var date = csvStore.getValue(items[i], "date");
					var actual = csvStore.getValue(items[i], "actual");
					var baseline = csvStore.getValue(items[i], "baseline");
					var target = csvStore.getValue(items[i], "target");
					console.log("actual => " + actual)
					if(actual == ' ' || actual == null || actual == undefined)
					{
						//do nothing
						console.log("1st. actual => " + actual)
					}
					else
					{
						if(baseline == ' ' || baseline == null || baseline == undefined) {baseline = dbBaseline; }
						if(target == ' ' || target == null || target == undefined) target = dbTarget;
						
						if(isValidDate(date) == false) dateError++;
						if(isNumeric(actual) == false) actualError++;
						if(isNumeric(baseline) == false) baselineError++;
						if(isNumeric(target) == false) targetError++;
						if(i < items.length-1)
						{
							if(lastActual == undefined && i == items.length-2)
							toSave = toSave + "{\"id\": " + savingId + ", \"date\": \"" + date + "\", \"actual\": " + actual + ", \"red\": " + baseline + ", \"green\": " + target + "}";
							else
							toSave = toSave + "{\"id\": " + savingId + ", \"date\": \"" + date + "\", \"actual\": " + actual + ", \"red\": " + baseline + ", \"green\": " + target + "},";
						}
						else toSave = toSave + "{\"id\": " + savingId + ", \"date\": \"" + date + "\", \"actual\": " + actual + ", \"red\": " + baseline + ", \"green\": " + target + "}";
						savingId++;
					}
				}
			  toSave = toSave + "]";
			  //console.log(toSave);
			  if(dateError > 0) 
			  {
				  if(dateError == 1)
				  csvErrors = csvErrors + "<br>One of the dates is not in the correct format";
				  else
				  csvErrors = csvErrors + "<br>" + dateError + " dates are not in the correct format";
			  }
			  if(actualError > 0) 
			  {
				  if(actualError == 1)
				  csvErrors = csvErrors + "<br>One of the actual values is not numeric";
				  else
				  csvErrors = csvErrors + "<br>" + actualError + " actual values are not numeric";
			  }
			  if(baselineError > 0) 
			  {
				  if(baselineError == 1)
				  csvErrors = csvErrors + "<br>One of the baseline values is not numeric";
				  else
				  csvErrors = csvErrors + "<br>" + baselineError + " baseline values are not numeric";
			  }
			  dom.byId("csvErrors").innerHTML = csvErrors;
			  if(dateError == 0 && actualError == 0 && baselineError == 0 && targetError == 0)
				dijit.byId("csvSaveButton").set('disabled', false);
			  else dijit.byId("csvSaveButton").set('disabled', true);
				break;	
			}
			case "fourColor":
			{
				var dateError = 0, actualError = 0, baselineError = 0, targetError = 0, stretchError = 0, csvErrors = "";
				var csvAttributes = "\'" + csvStore.getAttributes(items[0]) + "\'";
				csvAttributes = csvAttributes.split(",")
				//console.log(csvAttributes[0] + "; " + csvAttributes[1] + "; " + csvAttributes[2] + "; " + csvAttributes[3]);
				if(csvAttributes[0] != "\'date") csvErrors = csvErrors + "First column should be labeled 'date'";
				if(csvAttributes[1] != "actual") csvErrors = csvErrors + "Second column should be labeled 'actual'";
				if(csvAttributes[2] != "baseline") csvErrors = csvErrors + "Third column should be labeled 'baseline'";
				if(csvAttributes[3] != "target") csvErrors = csvErrors + "Fourth column should be labeled 'target'";
				if(csvAttributes[4] != "stretch_target\'") csvErrors = csvErrors + "Fifth column should be labeled 'stretch_target'";
				toSave = "[";
				var lastI = items.length - 1;
				var lastActual = csvStore.getValue(items[lastI], "actual");
				for(var i = 0; i < items.length; i++)
				{
					var date = csvStore.getValue(items[i], "date");
					var actual = csvStore.getValue(items[i], "actual");
					var baseline = csvStore.getValue(items[i], "baseline");
					var target = csvStore.getValue(items[i], "target");
					var stretch = csvStore.getValue(items[i], "stretch_target");
					if(actual == ' ' || actual == null || actual == undefined)
					{
						//do nothing
					}
					else
					{
						if(baseline == ' ' || baseline == null || baseline == undefined) {baseline = dbBaseline; }
						if(target == ' ' || target == null || target == undefined) target = dbTarget;
						if(stretch == ' ' || stretch == null || stretch == undefined) stretch = dbStretch;
						
						if(isValidDate(date) == false) dateError++;
						if(isNumeric(actual) == false) actualError++;
						if(isNumeric(baseline) == false && baseline != '') baselineError++;
						if(isNumeric(target) == false) targetError++;
						if(isNumeric(stretch) == false) stretchError++;
						if(i < items.length-1)
						{
							if(lastActual == undefined && i == items.length-2)
							toSave = toSave + "{\"id\": " + savingId + ", \"date\": \"" + date + "\", \"actual\": " + actual + ", \"red\": " + baseline + ", \"green\": " + target + ", \"darkgreen\": " + stretch + "}";
							else
							toSave = toSave + "{\"id\": " + savingId + ", \"date\": \"" + date + "\", \"actual\": " + actual + ", \"red\": " + baseline + ", \"green\": " + target + ", \"darkgreen\": " + stretch + "},";
						}
						else toSave = toSave + "{\"id\": " + savingId + ", \"date\": \"" + date + "\", \"actual\": " + actual + ", \"red\": " + baseline + ", \"green\": " + target + ", \"darkgreen\": " + stretch + "}";
						savingId++;
					}
				}
			  toSave = toSave + "]";
			  //console.log(toSave);
			  if(dateError > 0) 
			  {
				  if(dateError == 1)
				  csvErrors = csvErrors + "<br>One of the dates is not in the correct format";
				  else
				  csvErrors = csvErrors + "<br>" + dateError + " dates are not in the correct format";
			  }
			  if(actualError > 0) 
			  {
				  if(actualError == 1)
				  csvErrors = csvErrors + "<br>One of the actual values is not numeric";
				  else
				  csvErrors = csvErrors + "<br>" + actualError + " actual values are not numeric";
			  }
			  if(baselineError > 0) 
			  {
				  if(baselineError == 1)
				  csvErrors = csvErrors + "<br>One of the baseline values is not numeric";
				  else
				  csvErrors = csvErrors + "<br>" + baselineError + " baseline values are not numeric";
			  }
			  if(stretchError > 0) 
			  {
				  if(stretchError == 1)
				  csvErrors = csvErrors + "<br>One of the stretch target values is not numeric";
				  else
				  csvErrors = csvErrors + "<br>" + stretchError + " stretch target values are not numeric";
			  }
			  dom.byId("csvErrors").innerHTML = csvErrors;
			  if(dateError == 0 && actualError == 0 && baselineError == 0 && targetError == 0 && stretchError == 0)
				dijit.byId("csvSaveButton").set('disabled', false);
			  else dijit.byId("csvSaveButton").set('disabled', true);
				
				break;	
			}
			case "fiveColor":
			{
				var dateError = 0, actualError = 0, baselineError = 0, targetError = 0, stretchError = 0, bestError = 0, csvErrors = "";
				var csvAttributes = "\'" + csvStore.getAttributes(items[0]) + "\'";
				csvAttributes = csvAttributes.split(",")
				//console.log(csvAttributes[0] + "; " + csvAttributes[1] + "; " + csvAttributes[2] + "; " + csvAttributes[3]);
				if(csvAttributes[0] != "\'date") csvErrors = csvErrors + "First column should be labeled 'date'";
				if(csvAttributes[1] != "actual") csvErrors = csvErrors + "Second column should be labeled 'actual'";
				if(csvAttributes[2] != "baseline") csvErrors = csvErrors + "Third column should be labeled 'baseline'";
				if(csvAttributes[3] != "target") csvErrors = csvErrors + "Fourth column should be labeled 'target'";
				if(csvAttributes[4] != "stretch_target") csvErrors = csvErrors + "Fifth column should be labeled 'stretch_target'";
				if(csvAttributes[5] != "best_target\'") csvErrors = csvErrors + "Sixth column should be labeled 'best_target'";
				toSave = "[";
				var lastI = items.length - 1;
				var lastActual = csvStore.getValue(items[lastI], "actual");
				for(var i = 0; i < items.length; i++)
				{
					var date = csvStore.getValue(items[i], "date");
					var actual = csvStore.getValue(items[i], "actual");
					var baseline = csvStore.getValue(items[i], "baseline");
					var target = csvStore.getValue(items[i], "target");
					var stretch = csvStore.getValue(items[i], "stretch_target");
					var best = csvStore.getValue(items[i], "best_target");
					if(actual == ' ' || actual == null || actual == undefined)
					{
						//do nothing
					}
					else
					{
						if(baseline == ' ' || baseline == null || baseline == undefined) {baseline = dbBaseline; }
						if(target == ' ' || target == null || target == undefined) target = dbTarget;
						if(stretch == ' ' || stretch == null || stretch == undefined) stretch = dbStretch;
						if(best == ' ' || best == null || best == undefined) best = dbBest;
						
						if(isValidDate(date) == false) dateError++;
						if(isNumeric(actual) == false) actualError++;
						if(isNumeric(baseline) == false) baselineError++;
						if(isNumeric(target) == false) targetError++;
						if(isNumeric(stretch) == false) stretchError++;
						if(isNumeric(best) == false) bestError++;
						if(i < items.length-1)
						{
							if(lastActual == undefined && i == items.length-2)
							toSave = toSave + "{\"id\": " + savingId + ", \"date\": \"" + date + "\", \"actual\": " + actual + ", \"red\": " + baseline + ", \"green\": " + target + ", \"darkgreen\": " + stretch + ", \"blue\": " + best + "}";
							else
							toSave = toSave + "{\"id\": " + savingId + ", \"date\": \"" + date + "\", \"actual\": " + actual + ", \"red\": " + baseline + ", \"green\": " + target + ", \"darkgreen\": " + stretch + ", \"blue\": " + best + "},";
						}
						else toSave = toSave + "{\"id\": " + savingId + ", \"date\": \"" + date + "\", \"actual\": " + actual + ", \"red\": " + baseline + ", \"green\": " + target + ", \"darkgreen\": " + stretch + ", \"blue\": " + best + "}";
						savingId++;
					}
				}
			  toSave = toSave + "]";
			  //console.log(toSave);
			  if(dateError > 0) 
			  {
				  if(dateError == 1)
				  csvErrors = csvErrors + "<br>One of the dates is not in the correct format";
				  else
				  csvErrors = csvErrors + "<br>" + dateError + " dates are not in the correct format";
			  }
			  if(actualError > 0) 
			  {
				  if(actualError == 1)
				  csvErrors = csvErrors + "<br>One of the actual values is not numeric";
				  else
				  csvErrors = csvErrors + "<br>" + actualError + " actual values are not numeric";
			  }
			  if(baselineError > 0) 
			  {
				  if(baselineError == 1)
				  csvErrors = csvErrors + "<br>One of the baseline values is not numeric";
				  else
				  csvErrors = csvErrors + "<br>" + baselineError + " baseline values are not numeric";
			  }
			  if(stretchError > 0) 
			  {
				  if(stretchError == 1)
				  csvErrors = csvErrors + "<br>One of the stretch target values is not numeric";
				  else
				  csvErrors = csvErrors + "<br>" + stretchError + " stretch target values are not numeric";
			  }
			  if(bestError > 0) 
			  {
				  if(bestError == 1)
				  csvErrors = csvErrors + "<br>One of the best target values is not numeric";
				  else
				  csvErrors = csvErrors + "<br>" + bestError + " best target values are not numeric";
			  }
			  dom.byId("csvErrors").innerHTML = csvErrors;
			  if(dateError == 0 && actualError == 0 && baselineError == 0 && targetError == 0 && stretchError == 0 && bestError == 0)
				dijit.byId("csvSaveButton").set('disabled', false);
			  else dijit.byId("csvSaveButton").set('disabled', true);
				break;	
			}
		}
	}	
	csvStore.fetch({onComplete: completed});
}
showFileUploadDialog = function()
{ 
	registry.byId("newCsvFileDialog").show();
}
csvSaveData = function()
{
	registry.byId("csvImport").onCancel();
	console.log(toSave);
	saveMeasureValues(toSave);	
}
dbConnect = function()
{
	console.log("Carry out DB Connect here :-)");	
}
var slider = new HorizontalSlider({
	name: "chartSlider",
	value: 12,
	minimum: 1,
	maximum: 36,
	discreteValues: 36,
	intermediateChanges: false,
	style: "width:150px;",
	onChange: function(value){
		dom.byId("sliderValue").innerHTML = value;
		valuesCount = value;
		updateChart();
	}
}, "chartSlider").startup();
})
</script>
</head>
<body class="soria">
<div id="msgContent" style="display:block; position:absolute; top:0%; left:50%; color:white; background-color:green;"></div>
<div id="bodyContent">
<div id="divGroup1">
    <div id="divObjectiveName" style="display:none;">
        <div data-dojo-id="scorecardItemTitle" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Objective Name'">
            <div id="objectiveName"></div>
         </div>
    </div>
    <!--
    *********************************************************************************
    Gauge/Speedometer
    *********************************************************************************
    -->
     <div style="clear:left"></div>
     
     <div id="divGauge" style="display:none; margin-top:3px;">
		    <div data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Last Recorded Score'">
                <table align="center"><tr><td align="center"><div id="myGauge" align="center" style="border-radius:5; width:180px; height:130px; text-align:center;"><!--<background-image:url(images/themes/sand.png);--></div></td></tr></table>
                
            </div>
    </div>
 </div>  
    <!-- Description and Owner-->
    <div id="divGroup2">
        <div id="divObjectiveDescription" style="display:none">
        <div id="divPhoto">
            <div data-dojo-id="photoTitle" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Staff Photo'" style="float:right; width:200px;margin-top:0px;margin-left:3px;">
                <div id="photo"><img src="../upload/images/default.jpg" max-width="190" height="122" align="middle" /></div>
            </div>
        </div>
            <div data-dojo-id="descriptionTitle" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Description & Key Result Area'">
                <div id="objectiveDescription"></div>
            </div>
        </div>
        
        <div id="divOwner" style="display:none">
            <div data-dojo-id="ownerTitle" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Objective Owner & Team'">
                <div id="objectiveOwner"></div>
                <div id="objectiveTeam"></div>
            </div>
        </div>
    </div>
    <div style="clear:left"></div>
    <!-- End of Description and Owner-->
<!--
*********************************************************************************
Chart and Legend
*********************************************************************************
-->
<div id="divChart" style="display:none; float:left;">
    <div data-dojo-id="measureChart" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Actual Performance Over Time'" style="overflow:auto;">
	    <div id="chartDiv" style="height:280px; width:840px;"></div>
	</div>
</div>

<div id="divChartType" style="display:none; float:left;">
  <div data-dojo-id="measureValues" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Measure Updates'">
	    
   <?php if($view == "False" || $view == "Application") {?>
   <ul class="menuList">
    <li><a href="#" onclick="bulkEntry()">Update Measure Values</a></li>
    <li><a href="#" onclick="csvImport()">Import CSV Data</a></li>
    <li><a href="#" onclick="auditTrail()">View Audit Trail</a></li>
    <!--<li><a href="#" onclick="dbConnect()">Database Connect</a></li>-->
    <li>
    	<div id="trPrevious" style="font: 13px/20px 'Lucida Grande', Tahoma, Verdana, sans-serif; color:#555">Show Previous Period <input id="previousCheckbox" data-dojo-type="dijit/form/CheckBox" onClick="showPrevious()"/>
        </div> 
    </li>
    <li><span style="font: 13px/20px 'Lucida Grande', Tahoma, Verdana, sans-serif; color:#555">Change Chart Type</span><br>
    <select id="plot" onChange="changePlot()" style="width:100px;"  name="plot">
        <option value="Lines">Lines</option>
        <option value="Smart(XmR) Chart">Smart(XmR) Chart</option>
        <!--<option value="Columns">Columns</option>
        <option value="Bars">Bars</option>
        <option value="Areas">Areas</option>
        <option value="Pie">Pie</option>-->
    </select>
    </li>
    <li>
    	<span style="font: 13px/20px 'Lucida Grande', Tahoma, Verdana, sans-serif; color:#555">No of Chart Values:</span>
        <div id="sliderValue" style="font: 13px/20px 'Lucida Grande', Tahoma, Verdana, sans-serif; color:#555; float:right;"></div>
    	<div id="chartSlider"></div>
    </li>
   </ul>
   <?php } 
   else
   {
   ?>
    <ul class="menuList">
    <li>
    	<div id="trPrevious" style="font: 13px/20px 'Lucida Grande', Tahoma, Verdana, sans-serif; color:#555">Show Previous Period <input id="previousCheckbox" data-dojo-type="dijit/form/CheckBox" onClick="showPrevious()"/>
        </div> 
    </li>
    <li><span style="font: 13px/20px 'Lucida Grande', Tahoma, Verdana, sans-serif; color:#555">Change Chart Type</span><br>
    <select id="plot" onChange="changePlot()" style="width:100px;"  name="plot">
        <option value="Lines">Lines</option>
        <option value="Smart(XmR) Chart">Smart(XmR) Chart</option>
        <!--<option value="Columns">Columns</option>
        <option value="Bars">Bars</option>
        <option value="Areas">Areas</option>
        <option value="Pie">Pie</option>-->
    </select>
    </li> 
   </ul>
   <?php }?>
    </div>
</div>

<!--
*********************************************************************************
Measures
*********************************************************************************
-->
<div style="clear:left"></div>
<br><br>
<div id="divMeasures" style="display:none; float:left;">
    <div data-dojo-id="measureTitle" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Measures'">
	    <div id="measureContent"></div>
    </div>
</div>
<!--
*********************************************************************************
Initiatives
*********************************************************************************
-->
<div id="divInitiatives" style="display:none; float:left;">
<div data-dojo-id="initiativeTitle" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Initiatives'">
 <div id="initiativeContent"></div></div>
</div>

<div id="divCascadedTo" style="display:none; float:left;">
    <div data-dojo-id="cascadedTitle" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Cascaded To'">
     <div id="cascadedContent"></div>
	</div>
</div>
<div style="clear:left"></div>

<div id="divDevelopmentPlan" style="display:none">
    <div data-dojo-id="developmentPlanTitle" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Personal Development Plan'">
    	<div id="pdp"></div>
	</div>
</div>

<div id="divCoreValues" style="display:none">
    <div data-dojo-id="coreValuesTitle" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Core Values'">
    	<div id="coreValuesScorecardPage"></div>
	</div>
</div>

<div style="clear:left"></div>

<div id="divNotes" style="display:none">
	<div data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Interpretation'">
    	<div id="interpretation"></div>
    </div>
</div>

<div style="clear:left"></div>

<div id="divNotes2" style="display:none">
  <div data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Way Forward'">
  	<div id="wayForward"></div>
  </div>
</div>

<div style="clear:left"></div>
<div style="clear:left"></div>

<div id="divScorecardConversation" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Performance Conversations', open: true" style=" margin-left:-2px; display:none;">
	<!--<div id="userId" style="display:none;"><?php //echo "ind".$loggedInUser->user_id; ?></div>-->
	<div id="conversationHistory"></div>
    <table class="table table-sm table-condensed table-responsive table-bordeless">
        <tr>
        <td colspan="">
        <div contenteditable="true" id='conversation' style="width:100%; height:50px; padding:5px; border:1px solid #ccc; overflow-y:scroll;"></div>
        </td>
        </tr>
        <tr><td colspan="" align="left"><button type='button' class="btn btn-sm btn-outline-primary" onclick="postComment()" id="submitId">Post New Comment</button></td></tr>
    </table>
</div>
<div style="clear:left"></div>
<div class="dijitHidden">
	<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'Measure Audit Trail'" id="kpiAuditTrailDialog" style="font-size:11px;">
		<div id="kpiAuditContent" style="width:500px;"></div>
        <button data-dojo-type="dijit/form/Button" onclick="closeAudit" type="submit">Close</button>
    </div>
</div>

<div class="dijitHidden">
	<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'CSV Import'" id="csvImport" style="font-size:11px;">
		<div id="csvFormat"></div><br>
        <div id="csvErrors" class='myFont' style="color:#FF0000;"></div>
        <div id="gridContainer" style="width:500px; display:none; height:300px; overflow:scroll;"></div>
        <div>
        
        <button data-dojo-type="dijit/form/Button" onClick="showFileUploadDialog()" data-dojo-props="onClick:showFileUploadDialog" type="submit">Upload CSV File</button>
        <button data-dojo-type="dijit/form/Button" onClick="csvSaveData()" type="submit" disabled id="csvSaveButton">Save CSV Data</button></div>
</div>
</div>

<div class="dijitHidden">
    <div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New CSV File'" id="newCsvFileDialog">
    <table>
    <tr>
        <td colspan="2">
            <form method="post" action="../upload/UploadFile.php" id="csvForm" enctype="multipart/form-data" >
                <fieldset>
                    <legend>Upload File</legend>
                    <input name="uploadedfile" multiple type="file" id="csvUploader" dojoType="dojox/form/Uploader" label="Select File" >
                    <!--<input type="text" name="album" value="Summer Vacation" />
                    <input type="text" name="year" value="2011" />-->
                    <input type="hidden" name="hiddenKpiId" id="hiddenKpiId"/>
                    <input type="submit" label="Upload" dojoType="dijit/form/Button" />
                    <div id="csvFiles" dojoType="dojox/form/uploader/FileList" uploaderId="csvUploader"></div>
                </fieldset>
            </form>
        </td>
    </td>
    </tr>
    <tr>
        <td>
        <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:hideFileUploadDialog" type="submit">Finish</button>
        </td>
    </tr>
    </table>
    </div>
</div>

</div><!--end of body div-->
<div id="divIntro">Please Select a Scorecard Item on the Left Pane to View Details</div>

<div class="dijitHidden">
	<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'Bulk Measure Data Entry'" id="bulkMeasureDialogGoal" style="font-size:11px;">
		<div id="gridKpi" style="width:500px;"></div>
        <button data-dojo-type="dijit/form/Button" onclick="bulkEntry('updateBulk')" type="submit">Finish</button>
    </div>
</div> 

<div class="dijitHidden">
	<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'Bulk Measure Data Entry'" id="bulkMeasureDialog2" style="font-size:11px;">
		<div id="gridKpi2" style="width:500px;"></div>
        <button data-dojo-type="dijit/form/Button" onclick="bulkEntry('updateBulk')" type="submit">Finish</button>
    </div>
</div> 

<div class="dijitHidden">
	<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'Bulk Measure Data Entry'" id="bulkMeasureDialog3" style="font-size:11px;">
		<div id="gridKpi3" style="width:500px;"></div>
        <button data-dojo-type="dijit/form/Button" onclick="bulkEntry('updateBulk')" type="submit">Finish</button>
    </div>
</div> 
<div class="dijitHidden">
	<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'Bulk Measure Data Entry'" id="bulkMeasureDialog4" style="font-size:11px;">
		<div id="gridKpi4" style="width:500px;"></div>
        <button data-dojo-type="dijit/form/Button" onclick="bulkEntry('updateBulk')" type="submit">Finish</button>
    </div>
</div> 

<button type="button" id="scrollIntoView" style="display:compact; width:0px; height:0px; margin:0px;" ></button>
</body>
</html>