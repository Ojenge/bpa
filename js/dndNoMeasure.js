var organizations, perspectives, objectives, measures, target, gridCopyReport, dataOrg, dataPersp, dataObj, dataKpi, droppedItems = [], droppedCount = 0;

var droppedStore, selectedObject;
//	our "main" function
require([
"dijit/form/Select",
//"dijit/form/FilteringSelect",
"dojo/store/Memory", "dojo/store/Observable", 
//"dojo/aspect", 
"dojo/topic",
"dojo/aspect",
"dojo/on",
"dojo/json",
"dojo/dom",
//"dojo/data/ItemFileReadStore",
"dojo/request",
"dgrid/List",
"dgrid/OnDemandGrid",
"dgrid/Selection",
"dgrid/Keyboard",
"dgrid/extensions/DnD", 
"dgrid/extensions/ColumnHider",
"dojo/_base/declare",
"dojo/_base/array",
//"dgrid/demos/dTuned/data",
//from dnd example..					

"dojo/dnd/Source", 
"dojo/_base/lang", 
"dojo/_base/Deferred",  
//"dgrid/test/data/base",
//"dojox/form/Manager",
"dijit/form/TextBox",		

"dojo/domReady!"
],
function(Select, Memory, Observable, topic, aspect, on, json, dom, request, List, Grid, Selection, Keyboard, DnD, Hider, declare, arrayUtil, DnDSource, lang, Deferred){

var DnDGrid = declare([Grid, Selection, DnD, Keyboard]);
//var reportList = declare([List, Selection, Keyboard, DnD]);
request.post("../scorecards/get-scorecard.php",
{
	handleAs: "json",
	data:{
		//objectId: object.id
		//objectType: object.type
		}
}).then(function(organizationData) 
{
	dataKpi = organizationData;
	
	var dataCount = 0;
	//remove Objectives and Measures from Perspective data store
	while(dataCount < organizationData.length)
	{
		if(organizationData[dataCount].Perspective != null)
		delete organizationData[dataCount].Perspective;
		delete organizationData[dataCount].PerspId;
		delete organizationData[dataCount].Objective;
		delete organizationData[dataCount].ObjId;
		delete organizationData[dataCount].Measure;
		delete organizationData[dataCount].kpiId;
		dataCount++;
	}
	dataOrg = organizationData;
	//alert(JSON.stringify(scorecardData));
});
request.post("../scorecards/get-scorecard.php",
{
	handleAs: "json",
	data:{
		//objectId: object.id
		//objectType: object.type
		}
}).then(function(perspectiveData) 
{
	var dataCount = 0;
	while(dataCount < perspectiveData.length)
	{
		delete perspectiveData[dataCount].Objective;
		delete perspectiveData[dataCount].ObjId;
		delete perspectiveData[dataCount].Measure;
		delete perspectiveData[dataCount].kpiId;
		dataCount++;
	}
	dataPersp = perspectiveData;
	//remove Objectives and Measures from Perspective data store
});
request.post("../scorecards/get-scorecard.php",
{
	handleAs: "json",
	data:{
		//objectId: object.id
		//objectType: object.type
		}
}).then(function(objectiveData) 
{
	var dataCount = 0;
	while(dataCount < objectiveData.length)
	{
		delete objectiveData[dataCount].Measure;
		delete objectiveData[dataCount].kpiId;
		dataCount++;
	}
	dataObj = objectiveData;
});
request.post("../scorecards/get-scorecard.php",
{
	handleAs: "json",
	data:{
		//objectId: object.id
		//objectType: object.type
		}
}).then(function(kpiData) 
{
	dataKpi = kpiData;
	//alert(JSON.stringify(scorecardData));
});
var timer = setTimeout(function()
{
//alert("Sasa: "+dataOrg);

var newOrgArr = [];
var uniqueOrg = {};

//dataOrg = [{"Organization":"Test Organization"}];
dojo.forEach(dataOrg, function(item) 
{
	//alert(item.Organization)
	if (!uniqueOrg[item.Organization]) 
	{
		newOrgArr.push(item);
		uniqueOrg[item.Organization] = item;
	}
});
var orgStore = Observable(Memory({data: newOrgArr}));

var newPerspArr = [];
var uniquePersp = {};
 
dojo.forEach(dataPersp, function(item) 
{
	//alert(item.Perspective)
    if (!uniquePersp[item.Perspective]) 
	{
        newPerspArr.push(item);
        uniquePersp[item.Perspective] = item;
    }
});
var perspStore = Observable(Memory({data: newPerspArr}));

var newObjArr = [];
var uniqueObj = {};
 
dojo.forEach(dataObj, function(item) 
{
	//alert(item.Organization)
    if (!uniqueObj[item.Objective]) 
	{
        newObjArr.push(item);
        uniqueObj[item.Objective] = item;
    }
});
var objStore = Observable(Memory({data: newObjArr}));
droppedStore = Observable(Memory({data: droppedItems}));

//var target = new DnDSource("gridCopyReport", isSource:true); 
target = new DnDGrid({
	columns: [
		{label:"Organization", field:"Organization", sortable: true},
		{label:"Perspective", field:"Perspective", sortable: true},
		{label:"Objective", field:"Objective", sortable: true},
		{label:"Measure", field:"Measure", sortable: true}
	],
	store: droppedStore,
	accept: ["dgrid-row"],
	isSource: true,
	//in: true,
	//out: true,
}, "gridCopyReport");

organizations = new DnDGrid({
store: orgStore,
columns: [
	{label:"Organization", field:"Organization", sortable: true},
	//{label:"Id", field:"Id", sortable: true}
		]
}, "organizationsReport");
//organizations.store.setData(newOrgArr);
organizations.refresh();

perspectives = new DnDGrid({
	store: perspStore,
	columns: [
		{label:"Perspective", field:"Perspective", sortable: true},
		//{label:"Id", field:"id", sortable: true}
	]
}, "perspectivesReport");
perspectives.refresh();

objectives = new DnDGrid({
	store: objStore,
	columns: [
		{label:"Objective", field:"Objective", sortable: true},
		//{label:"Id", field:"id", sortable: true},
		//{label:"Perspective", field:"Perspective", sortable: true}
	]
}, "objectivesReport");
objectives.refresh();

//var reportStore = Observable(Memory({data: dataReport}));
var kpiStore = Observable(Memory({data: dataKpi}));
measures = new DnDGrid({
	store: kpiStore,
	columns: [
		{label:"Measure", field:"Measure", sortable: true},
		//{label:"Id", field:"id", sortable: true},
		//{label:"Objective", field:"Objective", sortable: true}
	]
}, "measuresReport");
measures.refresh();

organizations.on("dgrid-select", function(e){
	selectedObject = 'Organization';
	//	remove filtering
	objectives.query = {};
	measures.query = {};
	//	filter the grid. e.rows[0] = {"id":"undefined","data":{},"element":{"rowIndex":1,"observerIndex":0}}
	var row = e.rows[0],
		filter = row.data.Organization;
	//alert(JSON.stringify(row));
	if(row.id == "0")
	{
		// show all objectives
		delete perspectives.query.Organization;
		delete objectives.query.Organization
	} 
	else 
	{
		perspectives.query.Organization = filter;
		objectives.query.Organization = filter;
		measures.query.Organization = filter;
	}
	perspectives.refresh();
	objectives.refresh();
	measures.refresh();
});

perspectives.on("dgrid-select", function(e){
	selectedObject = 'Perspective';
	measures.query = {};
	//	filter the grid
	var row = e.rows[0],
		filter = row.data.Perspective;
		//alert(JSON.stringify(row));
		if(row.id == "0"){
		// show all objectives
		delete objectives.query.Perspective;
	} else {
		objectives.query.Perspective = filter;
	}
	objectives.refresh();
});

objectives.on("dgrid-select", function(e){
	selectedObject = 'Objective';
	//	filter the grid
	var row = e.rows[0],
		filter = row.data.Objective;
		//alert(filter);
		//alert(JSON.stringify(row));
		if(row.id == "0"){
		// show all objectives
		delete measures.query.Objective;
	} else {
		measures.query.Objective = filter;
	}
	measures.refresh();
});

measures.on("dgrid-select", function(e){
	selectedObject = 'Measure';
});

//	set the initial selections on the lists.
organizations.select(0);
},1000);
	
selectScorecardItems = function()
{
	var saveSelectIds = "";
	var saveSelectNames = "";
	while(droppedCount < droppedItems.length)
	{
		if(selectedObject == "Measure")
		{
			saveSelectIds = saveSelectIds + "," + droppedItems[droppedCount].kpiId;
			saveSelectNames = saveSelectNames + "," + droppedItems[droppedCount].Measure;
		}
		else if (selectedObject == "Objective")
		{
			saveSelectIds = saveSelectIds + "," + droppedItems[droppedCount].objId;
			saveSelectNames = saveSelectNames + "," + droppedItems[droppedCount].Objective;
		}
		else if (selectedObject == "Perspective")
		{
			saveSelectIds = saveSelectIds + "," + droppedItems[droppedCount].perspId;
			saveSelectNames = saveSelectNames + "," + droppedItems[droppedCount].Perspective;
		}
		else if(selectedObject == "Organization")
		{
			saveSelectIds = saveSelectIds + "," + droppedItems[droppedCount].orgId;
			saveSelectNames = saveSelectNames + "," + droppedItems[droppedCount].Organization;
		}
		droppedCount++;
	}
	
	dijit.byId("selectObjectDialog").onCancel();
	if(reportType == "cascadeReport") 
	{
		dijit.byId("newCascadeReportDialog").show();
		saveSelectNames = saveSelectNames.slice(1);
		saveSelectIds = saveSelectIds.slice(1);		
		dom.byId("selectedCascadeObjectsIds").innerHTML = saveSelectIds;
		dom.byId("selectedCascadeObjects").innerHTML = "<b>Selected Scorecard Items: </b>"+saveSelectNames;
	}
	else if(reportType == "initiativeReport") 
	{
		dijit.byId("newInitiativeReportDialog").show();
		saveSelectNames = saveSelectNames.slice(1);
		saveSelectIds = saveSelectIds.slice(1);
		dom.byId("selectedInitObjectsIds").innerHTML = saveSelectIds;
		dom.byId("selectedInitObjects").innerHTML = "<b>Selected Scorecard Items: </b>"+saveSelectNames;
	}
	else 
	{
		dijit.byId("newCustomReportDialog").show();
		saveSelectNames = saveSelectNames.slice(1);
		saveSelectIds = saveSelectIds.slice(1);
		dom.byId("selectedObjectsIds").innerHTML = saveSelectIds;
		dom.byId("selectedObjects").innerHTML = "<b>Selected Scorecard Items: </b>"+saveSelectNames;
	}
}

}	//	end "main" function
);	//	end require