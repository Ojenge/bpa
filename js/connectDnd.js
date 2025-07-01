var check = new Array();
for (i = 1; i <= 3; i++)
{
check[i] = false;
}
var dateString = 'false', valueString = 'false', nameString = 'false', dateSource;
var gridCopy, scorecardStore2, scorecardStore, formulaArea;
function unique(arr){
	//	create a unique list of items from the passed array
	//	(removing duplicates).  This is quick and dirty.

	//	first, set up a hashtable for unique objects.
	var obj = {};
	for(var i=0,l=arr.length; i<l; i++){
		if(!(arr[i] in obj)){
			obj[arr[i]] = true;
		}
	}

	//	now push the unique objects back into an array, and return it.
	var ret = [];
	for(var p in obj){
		ret.push(p);
	}
	ret.sort();
	return ret;
}

//	our "main" function
require([
"dijit/form/Select",
"dojo/request",
//"dijit/form/FilteringSelect",
	"dojo/store/Memory", "dojo/store/Observable", 
	//"dojo/aspect", 
	"dojo/topic", 
	"dojo/on",
	"dojo/dom",
	"dojo/json",
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
function(Select, request, Memory, Observable, topic, on, dom, json, List, Grid, Selection, Keyboard, DnD, Hider, declare, arrayUtil, DnDSource, lang, Deferred){

/*var data = [
		{id:1,Name:"% of Automated Processes",Perspective:"Organizational Capacity",
		Objective:"Improve Use of Technology",Organization:"Automation Department",Target:"100",Actual:"99"},
		{id:2,Name:"No of BI Offerings",Perspective:"Financial",
		Objective:"Increase Revenue",Organization:"BSEA",Target:"100",Actual:"95"},
		{id:3,Name:"Measure 3",Perspective:"Financial",
		Objective:"Increase Revenue",Organization:"BSEA",Target:"100",Actual:"95"}			
	];*/

request.post("layout/get-dnd.php",{
handleAs: "json"
}).then(function(dndData)
{
//alert(dndData);
var data = dndData;

var data2 = [];

//var stateData = [{id:1, State: "Kenya"},{id:2, State:"Uganda"}];

scorecardStore = Observable(Memory({data: data}));
scorecardStore2 = Observable(Memory({data: data2}));

		// Create the main grid to appear below the organization/perspective/objective lists.
	var grid = new (declare([Grid, Selection, Keyboard, DnD, Hider]))({
		//bufferRows: 5,
		//farOffRemoval: 400,
		//dndSourceType: "grid-row",
		store: scorecardStore,
		columns: {
			Name: "Name",
			//Actual: "Actual",
			//Target: "Target",
			Organization: "Organization",							
			Perspective: "Perspective",
			Objective: "Objective"							
		},
		dndParams:
		{
			//onDrop: alert("Helllo!")
		}
		//isSource: true
		//dndSourceType: "grid-row"
	}, "gridConnect");
	
	 gridCopy = new (declare([Grid, Selection, Keyboard, DnD]))({
		//bufferRows: 5,
		//farOffRemoval: 400,
		//dndSourceType: "grid-row",
		store: scorecardStore2,
		columns: {
			Name: "Name",
			Organization: "Organization",							
			//Perspective: "Perspective",
			//Objective: "Objective"							
		},
		dndParams: 
		{
			//onDrop: alert(this.row(this.targetAnchor)),
				//var store = this.gridCopy.scorecardStore2
				//alert("Hello there");
				//},
			//accept: ["dgrid-row"],
			isSource: true,
			//allowNested: true, // also pick up indirect children w/ dojoDndItem class
			//checkAcceptance: function(source, nodes) {return source !== this; }//don't self accept
		}
		
	}, "gridCopyConnect");

	// define a List constructor with the features we want mixed in,
	// for use by the three lists in the top region
	var scorecardList = declare([List, Selection, Keyboard, DnD]);

	//	define our three lists for the top.
	var organizations = new scorecardList({ selectionMode: "single" }, "orgConnect");
	var perspectives = new scorecardList({ selectionMode: "extended" }, "perspConnect");
	var objectives = new scorecardList({ selectionMode: "single" }, "objConnect");

	//	create the unique lists and render them
	var g = unique(arrayUtil.map(scorecardStore.data, function(item){ return item.Organization; })),
		art = unique(arrayUtil.map(scorecardStore.data, function(item){ return item.Perspective; })),
		obj = unique(arrayUtil.map(scorecardStore.data, function(item){ return item.Objective; }));
		
	g.unshift("All (" + g.length + " Organizations/Departments" + (g.length != 1 ? "s" : "") + ")");
	art.unshift("All (" + art.length + " Perspectives" + (art.length != 1 ? "s" : "") + ")");
	obj.unshift("All (" + obj.length + " Objectives" + (obj.length != 1 ? "s" : "") + ")");
	
	organizations.renderArray(g);
	perspectives.renderArray(art);
	objectives.renderArray(obj);

	var currentOrganization; // updated on organization select

	//	start listening for selections on the lists.
	organizations.on("dgrid-select", function(e){
		//	filter the objectives, perspectives and grid
		var	row = e.rows[0],
			filter = currentOrganization = row.data,
			art;
		if(row.id == "0"){
			//	remove filtering
			art = unique(arrayUtil.map(scorecardStore.data, function(item){ return item.Perspective; }));
			grid.query = {};
		} else {
			//	create filtering
			art = unique(arrayUtil.map(arrayUtil.filter(scorecardStore.data, function(item){ return item.Organization == filter; }), function(item){ return item.Perspective; }));
			grid.query = { "Organization": filter };
		}
		art.unshift("All (" + art.length + " Perspective" + (art.length != 1 ? "s" : "") + ")");
		
		perspectives.refresh();	//	clear contents
		perspectives.renderArray(art);
		perspectives.select(0); //	reselect "all", triggering objectives+grid refresh
	});

	perspectives.on("dgrid-select", function(e){
		//	filter the objectives, grid
		var row = e.rows[0],
			filter = row.data, obj;
		if(row.id == "0"){
			if(organizations.selection[0]){
				//	remove filtering entirely
				obj = unique(arrayUtil.map(scorecardStore.data, function(item){ return item.Objective; }));
			} else {
				//	filter only by organization
				obj = unique(arrayUtil.map(arrayUtil.filter(scorecardStore.data, function(item){ return item.Organization == currentOrganization; }), function(item){ return item.Objective; }));
			}
			delete grid.query.Perspective;
		} else {
			//	create filter based on perspective
			obj = unique(arrayUtil.map(arrayUtil.filter(scorecardStore.data, function(item){ return item.Perspective == filter; }), function(item){ return item.Objective; }));
			grid.query.Perspective = filter;
		}
		obj.unshift("All (" + obj.length + " Objective" + (obj.length != 1 ? "s" : "") + ")");

		objectives.refresh(); //	clear contents
		objectives.renderArray(obj);
		objectives.select(0); //	reselect "all" item, triggering grid refresh
	});


	objectives.on("dgrid-select", function(e){
		//	filter the grid
		var row = e.rows[0],
			filter = row.data;
			if(row.id == "0"){
			// show all objectives
			delete grid.query.Objective;
		} else {
			grid.query.Objective = filter;
		}
		grid.refresh();
	});

	//	set the initial selections on the lists.
	organizations.select(0);
	
	//var store = Observable(Memory({data: data}));
	//var droppedValue = scorecardStore.get(1);
	//droppedValue = droppedValue.getValue(grid.getItem(0), "Name");
	//alert("Value: " + droppedValue.Name);
	
	
	function afterDrop()
	{
		if(newValue == undefined)
		{
			if(dom.byId("dateNode").innerHTML != '' && dateString == "false")
			{
				var myString = dom.byId("dateNode").innerHTML;
				var myStringShort = myString.match("width=\"200\">(.*)</td><td>");
				console.log("Date => " + myStringShort[1]);
				dom.byId("dateColumn").innerHTML = myStringShort[1];
				dateString = "true";
			}
			else if(dom.byId("valueNode").innerHTML != '' && valueString == 'false')
			{
				var myString = dom.byId("valueNode").innerHTML;
				var myStringShort = myString.match("width=\"200\">(.*)</td><td>");
				console.log("Value => " + myStringShort[1]);
				dom.byId("valueColumn").innerHTML = myStringShort[1];
				valueString == 'true';
			}
			else if(dom.byId("nameNode").innerHTML != '' && nameString == 'false')
			{
				var myString = dom.byId("nameNode").innerHTML;
				var myStringShort = myString.match("width=\"200\">(.*)</td><td>");
				console.log("Name => " + myStringShort[1]);
				dom.byId("nameColumn").innerHTML = myStringShort[1];
				valueString == 'true';
			}
			else
			{
				console.log(dom.byId("valueNode").innerHTML);
				if(dom.byId("dateNode").innerHTML == '' && dateString == "true") dateString == "false";
				if(dom.byId("valueNode").innerHTML == '' && valueString == "true") valueString == "false";
				if(dom.byId("nameNode").innerHTML == '' && nameString == "true") nameString == "false";
				console.log("Stuff dropped back");	
			}
			var dropWait = setTimeout(function()
			{
				console.log("dateSource => " + dateSource.getAllNodes().length);
				console.log("nameSource => " + nameSource.getAllNodes().length);
				console.log("valueSource => " + valueSource.getAllNodes().length);
			},300);
		}
		else
		{			
			console.log(newValue + " has been added :-). Check value is: " + check[i]);
			//formulaArea = dojo.byId("formula");
			//var droppedValue = scorecardStore.get();
			var droppedId = scorecardStore.index;
			//var droppedItem = scorecardStore;
			//droppedValue = JSON.stringify(droppedValue);
			var i, index, checkItem;
		
			//alert(formulaArea.value);
			
			for (i = 1; i <= 3; i++)
			{
				if(droppedId[i] == undefined && check[i] == false)
				{	
					var newValue = scorecardStore2.get(i);
					newValue = newValue.ObjId;
					check[i] = true;
					//console.log(newValue + " has been added :-). Check value is: " + check[i]);
					dom.byId("formula").innerHTML = newValue;
					//formulaArea.innerHTML = formulaArea.innerHTML + newValue;
					//formulaArea.focus();
				}
				else
				{//alert(droppedId[i]);
				}
					
			}
		}
		//var test = gridCopy.getItem(event.rowIndex);
		//alert(test.Name);
		//alert(gridCopy.row(targetAnchor))
	};
					
	topic.subscribe("/dnd/drop", afterDrop);
	
var stateData = [
{ label: "Daily", value: "Daily" },
{ label: "Weekly", value: "Weekly" },
{ label: "Monthly", value: "Monthly", selected: true },
{ label: "Quarterly", value: "Quarterly" },
{ label: "Bi-Annually", value: "Bi-Annually" },
{ label: "Yearly", value: "Yearly" }
];

	
var mySelect = new Select({
name: "select2",
id: "collectionFrequency", // Add id to register with dijit registry
options: stateData
},"collectionFrequency");
mySelect.startup();

});		

}	//	end "main" function



);	//	end require