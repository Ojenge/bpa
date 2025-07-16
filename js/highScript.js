var kpiGlobalId, kpiGlobalName, kpiGlobalType, gaugeType, globalDate, mainMenuState ,selectedReport, kpiOwnerId, kpiUpdaterId, indNameId, initiativeStore, initiativeImpactId, tnAdd, reportListContent, objTooltipDialog, removeTooltip, updaterCheckbox = "False", selectedGauge, pdpEdit, pdpEditId, globalParent, ownerUpdaterStore;

// Safety function to check if chart is available before operations
function safeChartOperation(operation) {
	if (typeof chart !== 'undefined' && chart !== null) {
		try {
			return operation();
		} catch (e) {
			console.warn('Chart operation failed:', e);
			return false;
		}
	}
	return false;
}

// Helper function to get the correct conversation div ID based on current module
function getConversationDivId() {
	if(mainMenuState == "Initiatives") {
		return "divInitiativeConversation";
	} else if(kpiGlobalType == "advocacy") {
		return "divAdvocacyConversation";
	} else {
		return "divScorecardConversation";
	}
}
var gauge3, gauge4, chart, indicators, gaugeValue, cp, pdpId, view = "True", setFormula, valuesCount = 12, dataType, currency, valueIndicator3 = null, gridUpdateType, kpiListId, kpiName, csvImportVar = 'false';
var upperLimit=[], lowerLimit=[], redLimit=0, greenLimit=0;//limits for line chart thresholds
var tree, governmentStore; //this is to allow the definition tables to update the tree
var treeCreated = "False"; //this is to ensure certain aspects of the tree are created only once;
var period = "months";
var chartType = "9Steps";
var tdDomNode; //2D array variable used by cba_balanceSheet.php to hold chart dom ids. Putting this here so as to be able to destroy as appropriate
var colorCode = "grey";
var currentChild, that, returnCheck = "False", countEnd, childrenCount = 0; childrenLead = 0;
globalDate = new Date();

//globalDate.setMonth(globalDate.getMonth() - 1);//Temporary hack for HACO December review in January. Will prevent the need to keep changing the date to December. LTK 08 Jan 2022 21:53Hrs
require([
	"dijit/registry",
	"dojo/ready",
	"dojo/aspect",
	"dojo/json",
	//"dojo/Deferred",
	"dojo/on",
	"dojo/dom-construct",
	"dojo/dom-attr",
	"dojo/dom-style",
	"dojo/dom-style",
	"dojo/dom",
	'dojo/date',
	'dojo/date/locale',

	"dojox/layout/ContentPane",
	"dojox/layout/ExpandoPane",
	"dijit/TitlePane",
	"dijit/Editor",
	"dijit/form/FilteringSelect",
	"dijit/Tooltip",
	"dijit/TooltipDialog",
	"dijit/popup",

	"dojo/query",
	"dojo/parser",

	"dojo/_base/array",

	"dojo/store/Memory",
	"dojo/store/Observable",
	"dojox/charting/StoreSeries",

	"dojox/gantt/GanttChart",
	"dojox/gantt/GanttProjectItem",
	"dojox/gantt/GanttTaskItem",
	"dojox/widget/MonthlyCalendar",

	"dojox/charting/action2d/Tooltip",
	"dojox/charting/action2d/Magnify",

	"dijit/Tree",
	"dijit/tree/ObjectStoreModel",
	"dijit/tree/dndSource",
	//"dojo/text!http://localhost/analytics.local/layout/tree.php",
	"dojo/text!/bpa/layout/tree.php",
	"dojo/text!/bpa/layout/treePc.php",
	"dojo/data/ItemFileReadStore",
	"dojo/data/ItemFileWriteStore",
	"dojox/gfx",
	"dojo/request",
	"dojo/_base/fx",
	"dojo/fx/Toggler",
	"dojo/fx",

	//"dojo/query!css2",
	"dijit/Menu",
	"dijit/MenuItem",
	"dijit/MenuSeparator",
	"dijit/MenuBar",
	"dijit/MenuBarItem",
	"dijit/PopupMenuItem",
	"dijit/PopupMenuBarItem",
	//"dijit/MenuSeparator",
	"dijit/form/ComboButton",
	"dijit/layout/BorderContainer",
	"dijit/layout/TabContainer",

	"dijit/WidgetSet",
	"dijit/_editor/plugins/TextColor",
	"dijit/_editor/plugins/LinkDialog",

	"dijit/Dialog",

	"dijit/form/Button",
	"dijit/form/ComboBox",
	"dijit/form/DropDownButton",
	"dijit/form/TextBox",

	"dijit/form/CheckBox"

	//"dojo/domReady!"
], function(registry, ready, aspect, json, on, domConstruct, domAttr, domStyle, style, dom, date, locale, ContentPane, ExpandoPane,TitlePane, Editor, FilteringSelect,Tootltip, TooltipDialog, popup, query, parser, array, Memory, Observable, StoreSeries, GanttChart, GanttProjectItem, GanttTaskItem, MonthlyCalendar, Tooltip, Magnify, Tree, ObjectStoreModel, dndSource, bscData, pcData, ItemFileReadStore, ItemFileWriteStore, gfx, request, baseFx, Toggler, coreFx, Menu, MenuItem, MenuSeparator, MenuBarItem, PopupMenuItem, ComboButton, CheckBox){

//Global Variables Go Here...
var saveGoal, saveRed, thresholdType, indName, kpiDescription, kpiOutcome, kraListId, kraName, collectionFrequency, kpiType, aggregationType, kpiOwner, kpiUpdater, darkGreen, green, blue, red, darkGreenType, greenType, blueType, redType, kpiMission, kpiVision, kpiValues, weight, formula, kpiCascade;

// VirtualSelect state tracking
var virtualSelectReady = false;
var myOptionsAjax = [];

//tree global variables
var childItem, tnAddType, tnEdit, edit, tnEditType, tnEditHolder, tnName, tnWeight;

//tree menu global variables
var tnMenuItem;

var tabTimeout = setTimeout(function(){
domStyle.set(dijit.byId("myTab").controlButton.domNode, "visibility", "hidden");
	},3000);

globalDate = locale.format(globalDate, {
				selector: "date",
				datePattern:"yyyy-MM"
				});
objTooltipDialog = new TooltipDialog({
					id: 'objTooltipDialog',
					//style: "width: 300px;",
					content: "Initial Content",
					//position: "right",
					onMouseLeave: function(){
						popup.close(objTooltipDialog);
					}
				});
removeTooltip = function()
{
	popup.close(objTooltipDialog);
}
request.post("admin/viewer.php", {
	}).then(function(returnView){
		view = returnView;
		//console.log("view = " + view);
		});
//console.log("(outside) view = " + view);
request.post("userCalls/get-users.php",{
handleAs: "json",
data: {
}
}).then(function(userData)
{
	ownerUpdaterStore = new Memory({data:userData});

	/*selectKpiOwner = new FilteringSelect({
	name: "kpiOwner",
	//displayedValue: managerDisplay,
	//placeHolder: "Select a User",
	store: ownerUpdaterStore,
	searchAttr: "User",
	maxHeight: -1,
	onChange: function(){
		kpiOwnerId = this.item.id; //shouldn't this be this.items.id as per: {"identifier":"User","label":"User","items":[{"id":"ind1","User":"Shazia Hamid"},{"id":"ind12","User":"Jason"}]};
		if(updaterCheckbox == "True") dijit.byId('kpiUpdater').set('value', this.item.User);
	}
	}, "kpiOwner");
	selectKpiOwner.startup();*/
/*
	selectKpiUpdater = new FilteringSelect({
	name: "kpiUpdater",
	//displayedValue: managerDisplay,
	//placeHolder: "Select a User",
	store: ownerUpdaterStore,
	searchAttr: "User",
	maxHeight: -1,
	onChange: function(){
		kpiUpdaterId = this.item.id;
		if(dom.byId("tdMeasureName").innerHTML == "Objective Name")
		{
			//save team to database and add their names to dialog window
			request.post("scorecards/save-objective-team.php",{
				data: {
					userId: this.item.id,
					objectiveId: kpiGlobalId
				}
			}).then(function(users)
			{
				dom.byId("teamNames").innerHTML = 'Team: ' + users;
			})
		}
		else
		{
			//do nothing
		}
	}
	}, "kpiUpdater");
	selectKpiUpdater.startup();*/ //Experience shows that this distinction between owner and updated is not practically applicable. Treat them as one. LTK 10May24 1855hrs

	selectIndName = new FilteringSelect({
	name: "indName",
	//displayedValue: managerDisplay,
	//placeHolder: "Select a User",
	store: ownerUpdaterStore,
	searchAttr: "User",
	maxHeight: -1,
	onChange: function(){
		indNameId = this.item.id;
		dom.byId('hiddenIndId').value = this.item.id;
	}
	}, "indName");
	selectIndName.startup();
});

/************************
Start of Multiple Select
************************/
var listWait = setTimeout(function(){
//var tagId = "#tag"+value;
$.getJSON( 'userCalls/get-users-multiple-select.php', function( data ) {
	myOptionsAjax = []; var items = []; var count = 0;
		
		$.each( data.users, function( key, val ) 
		{
			items['label'] = val.user;
			items['value'] = val.id;
			myOptionsAjax.push(items);
			items = [];
			//console.log("We are getting here count = " + count + " user = " + val.user + " and id = " + val.id);
			count++;
		});
		
	var initialSelect = data.selected;

	// Check if VirtualSelect is available before initializing
	if (typeof VirtualSelect !== 'undefined' && VirtualSelect.init) {
		try {
			VirtualSelect.init({
			//ele: '#multipleStaffSelect',
			ele: "#kpiOwner",
			options: myOptionsAjax,
			multiple: true,
			search: true,
			placeholder: 'Staff',
			optionsCount: 5,
			selectAllText: 'Select all',
			//selectedValue: initialSelect,
			showSelectedOptionsFirst: true,
			silentInitialValueSet: true,
			hasOptionDescription: false
			//hideClearButton: false
			//selectAllText: 'Select all', //works when search is disabled
			//noOfDisplayValues: 50
		});
		console.log("VirtualSelect initialized successfully for kpiOwner");
		virtualSelectReady = true;
		} catch (error) {
			console.error("Error initializing VirtualSelect:", error);
			// Fallback: create a regular select element with user options
			var kpiOwnerElement = document.querySelector("#kpiOwner");
			if (kpiOwnerElement && myOptionsAjax && myOptionsAjax.length > 0) {
				var selectHTML = '<select multiple name="kpiOwnerSelect" style="width: 100%; height: 100px;">';
				for (var i = 0; i < myOptionsAjax.length; i++) {
					selectHTML += '<option value="' + myOptionsAjax[i].value + '">' + myOptionsAjax[i].label + '</option>';
				}
				selectHTML += '</select>';
				kpiOwnerElement.innerHTML = selectHTML;
				console.log("Created fallback select with " + myOptionsAjax.length + " options");
				virtualSelectReady = true;
			} else if (kpiOwnerElement) {
				kpiOwnerElement.innerHTML = '<select multiple name="kpiOwnerSelect"><option value="">No options available</option></select>';
				virtualSelectReady = true;
			}
		}
	} else {
		console.error("VirtualSelect library not loaded");
		// Fallback: create a regular select element with user options
		var kpiOwnerElement = document.querySelector("#kpiOwner");
		if (kpiOwnerElement && myOptionsAjax && myOptionsAjax.length > 0) {
			var selectHTML = '<select multiple name="kpiOwnerSelect" style="width: 100%; height: 100px;">';
			for (var i = 0; i < myOptionsAjax.length; i++) {
				selectHTML += '<option value="' + myOptionsAjax[i].value + '">' + myOptionsAjax[i].label + '</option>';
			}
			selectHTML += '</select>';
			kpiOwnerElement.innerHTML = selectHTML;
			console.log("Created fallback select with " + myOptionsAjax.length + " options (VirtualSelect not available)");
			virtualSelectReady = true;
		} else if (kpiOwnerElement) {
			kpiOwnerElement.innerHTML = '<select multiple name="kpiOwnerSelect"><option value="">VirtualSelect not available</option></select>';
			virtualSelectReady = true;
		}
	}
});
	/*document.querySelector("#kpiOwner").addEventListener('afterClose', function() 
	{
		var selected = document.querySelector("#kpiOwner").selectedOptions;
		var tags = JSON.stringify(selected);
		//console.log("Selected: " + tags);
		request.post("save-multiple-owner-tags.php",{
			data: { 
				tags: tags,
				//id: value 
			}				
		}).then(function() 
		{
			//console.log("Tags saved successfully");
		});
	});*/
},500);
/************************
End of Multiple Select
************************/

var tempwait = setTimeout(function(){
request.post("scorecards/measures/get-measures.php",{
handleAs: "json",
data: {
}
}).then(function(measureData)
{
	var measureStore = new Memory({data:measureData});

	selectKpi = new FilteringSelect({
	name: "kpiList",
	//displayedValue: managerDisplay,
	//placeHolder: "Select a User",
	store: measureStore,
	searchAttr: "Parent Measure",
	maxHeight: -1,
	onChange: function(){
		kpiListId = this.item.kpiId;
		kpiName = this.item.Measure;
	}
	}, "kpiList");
	selectKpi.startup();
	//console.log("Should have created the measure list");
});
},2000)

var tempwait = setTimeout(function(){
request.post("layout/get-objectives.php",{
handleAs: "json",
data: {
}
}).then(function(objectiveData)
{//alert("we getting here?")
	var objectiveStore = new Memory({data:objectiveData});

	selectObjective = new FilteringSelect({
	name: "kpiCascade",
	//displayedValue: managerDisplay,
	//placeHolder: "Select a User",
	store: objectiveStore,
	searchAttr: "Objective",
	maxHeight: -1,
	labelAttr: "Objective",
	labelType: "html",
	onChange: function(){
		kpiCascade = this.item.objectiveId;
		//objectiveName = this.item.Objective;
	}
	}, "kpiCascade");
	selectObjective.startup();
	//console.log("Should have created the objective list");
});
},2000)

var tempwait = setTimeout(function(){
	request.post("scorecards/kra/getKRASelect.php",{
	handleAs: "json",
	data: {
	}
	}).then(function(kraData)
	{
		var kraStore = new Memory({data:kraData});
	
		selectKra = new FilteringSelect({
		name: "strategicResult",
		//displayedValue: managerDisplay,
		//placeHolder: "Select a User",
		store: kraStore,
		searchAttr: "kraName", //make sure the search attribute matches the key value in the data otherwise it will not work.
		maxHeight: -1,
		onChange: function(){
			kraListId = this.item.kraId;
			kraName = this.item.kraName;
		}
		}, "strategicResult");
		selectKra.startup();
	});
	},2000)

	// Function to ensure collection frequency dropdown is created
	ensureCollectionFrequencyDropdown = function() {
		// Check if dropdown already exists
		var existingWidget = registry.byId("collectionFrequency");
		if (existingWidget) {
			console.log("Collection frequency dropdown already exists");
			return;
		}

		// Check if the DOM element exists
		var element = dom.byId("collectionFrequency");
		if (!element) {
			console.warn("collectionFrequency DOM element not found");
			return;
		}

		// Create the dropdown
		try {
			require(["dijit/form/Select"], function(Select) {
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
					id: "collectionFrequency",
					options: stateData
				}, "collectionFrequency");
				mySelect.startup();
				console.log("Collection frequency dropdown created successfully");
			});
		} catch (error) {
			console.error("Error creating collection frequency dropdown:", error);
		}
	};

	// set up the store to get the tree data, plus define the method
	// to query the children of a node
adminUpdaters = function()
{
	cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
	href:"../admin/updaters/index.php"
	});
	cp.placeAt("appLayout");
	
	domConstruct.destroy('toolbar');
	domConstruct.destroy('table');
	domConstruct.destroy('updateSummary');
	domConstruct.destroy('dashboard');
	domConstruct.destroy('tableSummary');
	domConstruct.empty(cp);
}
scheduler = function () 
{
	cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
		href: "../phpJobScheduler/pjsfiles/index.php"
	});
	cp.placeAt("appLayout");
	domConstruct.destroy('modifyId');
	domConstruct.destroy('modifyName');
	domConstruct.destroy('scriptpath');
	domConstruct.destroy('time_last_fired');
	domConstruct.destroy('modifyMinutes');
	domConstruct.destroy('modifyHours');
	domConstruct.destroy('modifyDays');
	domConstruct.destroy('modifyWeeks');
	domConstruct.destroy('run_only_once');
	domConstruct.destroy('paused');
	domConstruct.destroy('multipleStaffSelect');
	domConstruct.destroy('button');
}
schedulerLogs = function () 
{
	cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
		href: "../phpJobScheduler/pjsfiles/error-logs.php"
	});
	cp.placeAt("appLayout");
}
schedulerModify = function () 
{
	cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
		href: "../phpJobScheduler/pjsfiles/index.php?add=1"
	});
	cp.placeAt("appLayout");
}
schedulerEdit = function (id) 
{
	cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
		href: "../phpJobScheduler/pjsfiles/modify.php?id="+id
	});
	cp.placeAt("appLayout");
}
schedulerSave = function () 
{
	var selectedStaff = JSON.stringify(document.querySelector('#multipleStaffSelect').selectedOptions);
	request.post("../phpJobScheduler/pjsfiles/add-modify.php",{
	//handleAs: "json",
	data: {
		id: document.getElementById('modifyId').value,
		name: document.getElementById('modifyName').value,
		minutes: document.getElementById('modifyMinutes').value,
		hours: document.getElementById('modifyHours').value,
		days: document.getElementById('modifyDays').value,
		weeks: document.getElementById('modifyWeeks').value,
		scriptpath: document.getElementById('scriptpath').value,
		run_only_once: document.getElementById('run_only_once').value,
		paused: document.getElementById('paused').value,
		time_last_fired: document.getElementById('time_last_fired').value,
		selectedStaff: selectedStaff
	}
	}).then(function()
	{
		scheduler();
	})
}
accentUsage = function()
{
	mainMenuState = "Accent Usage";
	cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
	//href:"dashboards/executive-summary.php?objectDate="+globalDate+"&objectPeriod="+period
	href:"dashboards/execSummary.html"
	});
	cp.placeAt("appLayout");
	domConstruct.destroy("executiveDbHome");
	domConstruct.destroy("reportees");
	domConstruct.destroy("detailsTable");
	domConstruct.destroy("execSummary");
}

notificationPreferences = function()
{
	mainMenuState = "Notification Preferences";
	cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
		href: "notifications/user_preferences.php"
	});
	cp.placeAt("appLayout");
}
notificationAdmin = function()
{
	mainMenuState = "Notification Management";
	cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
		href: "notifications/admin_notifications.php"
	});
	cp.placeAt("appLayout");
}
staffScores = function()
{
	mainMenuState = "Staff Scores";
	cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
	href:"reports/admin_user_scores.php"
	});
	cp.placeAt("appLayout");
	//domConstruct.destroy("loadingPanel");
	//domConstruct.destroy("hint");
	//domConstruct.destroy("divIndChartType");
}
departmentStaffScores = function()
{
	mainMenuState = "Department Staff Report";
	cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
	//href:"reports/departmentsStaffReport.php" //old structure that was too buggy
	href:"reports/scorecard-summary.php"
	});
	cp.placeAt("appLayout");
	
	domConstruct.destroy('idParkingLot');
	domConstruct.destroy('departmentList');
	domConstruct.destroy('scorecardSummary');
}
cascadeReportTwo = function () 
{
	mainMenuState = "Cascade Report";
	cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
		href: "reports/get-cascade-report.php"
	});
	cp.placeAt("appLayout");
}
pduDbHome = function()
{
	mainMenuState = "Home";
	
	request.post("layout/get_home_page.php",{
	}).then(function(homePage) 
	{
		//alert("Home Page = " + homePage);
		//domStyle.set(dom.byId("landingImage"), "display", "block");
		cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
			//href:"../analytics.local/gok/bigFour/index.html"
			href:homePage
			});
			cp.placeAt("appLayout");
			var treeTimeout = setTimeout(function()
			{
				domStyle.set(dom.byId("tree"), "display", "none");
				domStyle.set(dom.byId("expandCollapse"), "display", "none");
			},200);
			/*dom.byId("dbProjectsKey").innerHTML = "<table style='border:1px solid #999; padding:6px; border-radius:3px;'><tr><th colspan='2'>Project Key</th></tr><tr><td><div id='circle1'></div></td><td>With Major Issues</td></tr><tr><td><div id='circle2'></div></td><td>With Minor Issues</td></tr><tr><td><div id='circle3'></div></td><td>On Track</td></tr></table>";*/
		});	
		
		domConstruct.destroy('pillarDetails');
}


pcSummaries = function()
{
	cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
		href:"reports/pcSummaries.php"
		});
		cp.placeAt("appLayout");
		
		domConstruct.destroy('displayReport');
		domConstruct.destroy('idParkingLot');
		domConstruct.destroy('selectDepartment');
		domConstruct.destroy('departmentList');
		domConstruct.empty(cp);
		//domConstruct.destroy("appLayout");
}
procurementPlan = function()
{
	mainMenuState = "Procurement Plan";
	cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
	//href:"dashboards/executive-summary.php?objectDate="+globalDate+"&objectPeriod="+period
	href:"dashboards/kdicProcurementPlan/index.html"
	});
	cp.placeAt("appLayout");
	domConstruct.destroy("table");
	//domConstruct.destroy("hint");
	//domConstruct.destroy("divIndChartType");
}
indDashboard = function()
	{
		//domStyle.set(dom.byId("landingImage"), "display", "none");
		//dojo.empty(cp);

		// Safely handle ContentPane transition
		if (cp && cp.cancel) {
			cp.cancel();
		}

		mainMenuState = "Personal Dashboard";
		cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
			//href:"indDashboard.php"
			href:"individual/indAppraisal.php"
			});
			cp.placeAt("appLayout");
			domConstruct.destroy("appraisalDate");
			domConstruct.destroy("indDescription");
			domConstruct.destroy("indPhoto");
			domConstruct.destroy("userList");
			if(dijit.byId("userList")) dijit.byId("userList").destroy(true);
			domConstruct.destroy("appraisalCheck");
			domConstruct.destroy("measureIndContent");
			domConstruct.destroy("kpiContent");
			domConstruct.destroy("performanceSummary");
			domConstruct.destroy("overallContent");

			if(dijit.byId("scoreEditDialog")) 
			{
				dijit.byId("scoreEditDialog").destroy(true);
				//if(dijit.byId("scoreEditDialog")) dijit.byId("scoreEditDialog").destroyRecursive();
			}
			domConstruct.destroy("reviewMeasureName");
			domConstruct.destroy("reviewMeasureScore");
			
			domConstruct.destroy("scrollIntoView");
			dom.byId("userIdInd").innerHTML = dom.byId('userIdJs').innerHTML; //reset to logged in user when opening re-opening the page
	}
	orgChart = function () {
		cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
			href: "dashboards/orgChart/index.php"
		});
		cp.placeAt("appLayout");
		domConstruct.destroy("chart-container");
		domConstruct.destroy("personalDetails");
		domConstruct.destroy("personalPhoto");
		domConstruct.destroy("measurePersonalContent");
		domConstruct.destroy("kpiPersonalContent");
		domConstruct.destroy("personalSummary");
		domConstruct.destroy("overallPersonalContent");
	}
	stratMap = function () {
		cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
			href: "dashboards/gojs/stratMap.html"
		});
		cp.placeAt("appLayout");
	
		domConstruct.destroy("myDiagramDiv");
		domConstruct.destroy("mapDrillDown");
	}
	dashboardEditor = function () {
		cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
			href: "../gojs-mark/samples/stratMapHaco.html"
		});
		cp.placeAt("appLayout");
		domConstruct.destroy("sample");
		domConstruct.destroy("myDiagramDiv");
		domConstruct.destroy("saveButton");
		domConstruct.destroy("mySavedModel");
		domConstruct.destroy("mapDrillDown");
	}
	resultsMap = function () {
		cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
			href: "../gojs-mark/samples/resultsMap.html"
		});
		cp.placeAt("appLayout");
		domConstruct.destroy("myDiagramDiv2");
		domConstruct.destroy("saveButton2");
		domConstruct.destroy("mySavedModel2");
	}
	
	bookMarks = function()
	{
		var userId = dom.byId('userIdJs').innerHTML;
		cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
			content: "Please select a bookmark on the left hand side pane."
			//href:"bookMark.php"
			});
			cp.placeAt("appLayout");

			request.post("layout/get-bookmarks.php",{
			handleAs: "json",
			data: {
				userId: userId
			}
			}).then(function(bookmarkList)
			{
				var bookmarkCount = 0;
				var bookmarkContent = "", itemType, bookMarkId, menuType, itemId, itemName;
				while(bookmarkCount < bookmarkList.length)
				{
					itemType = bookmarkList[bookmarkCount].itemType;
					bookMarkId = bookmarkList[bookmarkCount].id;
					menuType = bookmarkList[bookmarkCount].menuType;
					itemId = bookmarkList[bookmarkCount].itemId;
					itemName = bookmarkList[bookmarkCount].name;
					//console.log(itemType + ', ' + menuType +', ' + itemId +', ' + itemName);
					bookmarkContent = bookmarkContent + "&nbsp;&nbsp;&nbsp;<a href='#' onClick='displayBookmark(\""+ itemType +"\",\""+menuType+"\",\""+itemId+"\",\""+itemName+"\");'>" + bookmarkList[bookmarkCount].name + "</a> <img src='images/icons/delete.png' onclick='deleteBookMark(\""+bookMarkId+"\")' style='cursor:pointer;' /> <img src='images/icons/edit.png' onclick='getBookMarkName(\""+bookMarkId+"\")' style='cursor:pointer;' /><br>";
					bookmarkCount++;
				}
				//alert(JSON.stringify(bookmarkList));
				dom.byId("myBookmarks").innerHTML = bookmarkContent;
			});
	}
	deleteBookMark = function(bookMarkId)
	{
		request.post("layout/delete-bookmark.php",{
		handleAs: "json",
		data: {
			bookMarkId: bookMarkId,
			action: "delete",
			newName: ''
		}
		}).then(function()
		{
			//bookmark deleted message
			bookMarks();
		})
	}
	getBookMarkName = function(bookMarkId)
	{
		dijit.byId("bookmarkRenameDialog").show();
		dom.byId("bookMarkId").innerHTML = bookMarkId;
	}
	displayStratMap = function()
	{
		//mainMenuState = "Strategy Map";
		cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
		href:"StratMap.php"
		});
		cp.placeAt("appLayout");
		domStyle.set(dom.byId("userSettings"), "display", "none");
		domStyle.set(dom.byId("coreValues"), "display", "none");
		domStyle.set(dom.byId("tree"), "display", "none");
		domStyle.set(dom.byId("expandCollapse"), "display", "none");
		domStyle.set(dom.byId("definitionTables"), "display", "none");
		domStyle.set(dom.byId("homeLinks"), "display", "block");
		//domStyle.set(dom.byId("advocacyLinks"), "display", "none");
		domStyle.set(registry.byId("menuSeparator").domNode, 'display', 'none');
		dojo.byId("dynamicMenu").innerHTML = "";
		domConstruct.destroy("stratMap");
	}
	displayOrgStructure = function()
	{
		cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
		href:"db_org_structure_longhorn.php"
		});
		cp.placeAt("appLayout");
		domStyle.set(dom.byId("userSettings"), "display", "none");
		domStyle.set(dom.byId("coreValues"), "display", "none");
		domStyle.set(dom.byId("tree"), "display", "none");
		domStyle.set(dom.byId("expandCollapse"), "display", "none");
		domStyle.set(dom.byId("definitionTables"), "display", "none");
		domStyle.set(dom.byId("homeLinks"), "display", "block");
		//domStyle.set(dom.byId("advocacyLinks"), "display", "none");
		domStyle.set(registry.byId("menuSeparator").domNode, 'display', 'none');
		dojo.byId("dynamicMenu").innerHTML = "";
		domConstruct.destroy("orgStructure");
		domConstruct.destroy("objTooltipDialog");
		domConstruct.destroy("orgStructure");
	}
	displayBookmark = function(itemType, menuType, itemId, itemName)//There is dire need to review how this works - too much code real estate being taken up here. LTK 03May2021 1859 Hours
	{
		//Deleted all. Review approach in future if necessary. LTK 14May2021 1708 Hrs
	}

	// Dashboard Navigation Functions
	// Helper function for safe ContentPane transitions
	safeContentPaneTransition = function(href, menuState) {
		try {
			console.log('safeContentPaneTransition called with href:', href, 'menuState:', menuState);
			
			// Cancel any pending requests
			if (cp && typeof cp.cancel === 'function') {
				cp.cancel();
			}

			// Destroy existing ContentPane if it exists
			if (cp && typeof cp.destroyRecursive === 'function') {
				cp.destroyRecursive();
			}

			// Set the menu state
			mainMenuState = menuState;

			// Create new ContentPane with enhanced functionality
			cp = new ContentPane({
				region: "center",
				"class": "bpaPrint",
				href: href,
				onLoad: function() {
					console.log('ContentPane loaded successfully:', href);
					
					// Add a small delay to ensure DOM is fully ready
					setTimeout(function() {
						console.log('ContentPane DOM ready, checking for initialization functions...');
						
						// Check if the loaded page has any initialization functions
						if (typeof window.loadDepartmentOptions === 'function') {
							console.log('Found loadDepartmentOptions function, calling it...');
							window.loadDepartmentOptions();
						}
						
						if (typeof window.loadPortfolioData === 'function') {
							console.log('Found loadPortfolioData function, calling it...');
							window.loadPortfolioData();
						}
						
						if (typeof window.initializeCharts === 'function') {
							console.log('Found initializeCharts function, calling it...');
							window.initializeCharts();
						}
					}, 500);
				},
				onError: function(error) {
					console.error('ContentPane error:', error);
					this.set('content', '<div class="alert alert-danger">Error loading dashboard. Please try again. Error: ' + error + '</div>');
				}
			});

			// Place the new ContentPane
			cp.placeAt("appLayout");
			console.log('ContentPane placed at appLayout');

		} catch (error) {
			console.error('Error in safeContentPaneTransition:', error);
		}
	}

	dashboardsOverview = function()
	{
		safeContentPaneTransition("dashboards/index.php", "Dashboards Overview");
	}

	departmentDashboard = function(departmentId)
	{
		// If no department ID is provided, use the default or show department selection
		if (!departmentId) {
			// Try to get user's department from global context or use default
			departmentId = (typeof loggedInUser !== 'undefined' && loggedInUser.department) ? loggedInUser.department : 'org1';
		}

		console.log('Loading department dashboard for department:', departmentId);

		var url = "dashboards/department-performance-dashboard.php?departmentId=" + encodeURIComponent(departmentId);
		
		// Enhanced ContentPane with better error handling and callback
		try {
			// Cancel any pending requests
			if (cp && typeof cp.cancel === 'function') {
				cp.cancel();
			}

			// Destroy existing ContentPane if it exists
			if (cp && typeof cp.destroyRecursive === 'function') {
				cp.destroyRecursive();
			}

			// Set the menu state
			mainMenuState = "Department Dashboard";

			// Create new ContentPane with enhanced error handling
			cp = new ContentPane({
				region: "center",
				"class": "bpaPrint",
				href: url,
				onLoad: function() {
					console.log('Department dashboard loaded successfully');
					// Ensure dashboard functions are available in the loaded context
					this.set('content', this.get('content') + '<script>console.log("Dashboard content loaded");</script>');
				},
				onError: function(error) {
					console.error('ContentPane error:', error);
					this.set('content', '<div class="alert alert-danger">Error loading dashboard. Please try again. Error: ' + error + '</div>');
				}
			});

			// Place the new ContentPane
			cp.placeAt("appLayout");

		} catch (error) {
			console.error('Error in departmentDashboard:', error);
			// Fallback: try to load directly
			window.location.href = url;
		}
	}

	// Enhanced department dashboard with department selection
	departmentDashboardWithSelection = function()
	{
		safeContentPaneTransition("dashboards/index.php", "Dashboard Selection");
	}

	// Function to open department dashboard with specific department
	openDepartmentDashboard = function(departmentId) {
		if (!departmentId) {
			// Get selected department from dropdown if available
			var selectElement = document.getElementById('deptSelect');
			if (selectElement) {
				departmentId = selectElement.value;
			}
		}
		departmentDashboard(departmentId);
	}

	executiveSummary = function()
	{
		// Load the executive summary dashboard with enhanced functionality
		try {
			// Cancel any pending requests
			if (cp && typeof cp.cancel === 'function') {
				cp.cancel();
			}

			// Destroy existing ContentPane if it exists
			if (cp && typeof cp.destroyRecursive === 'function') {
				cp.destroyRecursive();
			}

			// Set the menu state
			mainMenuState = "Executive Summary";

			// Create new ContentPane with enhanced functionality
			cp = new ContentPane({
				region: "center",
				"class": "bpaPrint",
				href: "dashboards/executive-summary.php",
				onLoad: function() {
					console.log('Executive summary dashboard loaded successfully');
					// Initialize executive summary functionality after load
					initializeExecutiveSummary();
				},
				onError: function(error) {
					console.error('ContentPane error:', error);
					this.set('content', '<div class="alert alert-danger">Error loading executive summary. Please try again.</div>');
				}
			});

			// Place the new ContentPane
			cp.placeAt("appLayout");

		} catch (error) {
			console.error('Error in executiveSummary:', error);
			// Fallback: try to load directly
			window.location.href = "dashboards/executive-summary.php";
		}
	}

	// Initialize executive summary dashboard functionality
	initializeExecutiveSummary = function() {
		console.log('initializeExecutiveSummary called');
		
		// Wait for DOM to be ready
		setTimeout(function() {
			console.log('DOM ready, initializing executive summary...');
			triggerExecutiveSummaryDataLoad();
		}, 200);
	}

	// Trigger executive summary data loading
	triggerExecutiveSummaryDataLoad = function() {
		console.log('Triggering executive summary data load...');
		
		// Check if the dashboard is loaded and has the required elements
		const totalStaffElement = document.getElementById('totalStaff');
		if (!totalStaffElement) {
			console.log('Executive summary elements not found, dashboard may not be fully loaded');
			return;
		}
		
		// Check if data is already loaded
		if (totalStaffElement.textContent && totalStaffElement.textContent !== '--' && !totalStaffElement.textContent.includes('spinner')) {
			console.log('Executive summary data already loaded');
			return;
		}
		
		console.log('Loading executive summary data...');
		loadExecutiveSummaryData();
		initializeExecutiveCharts();
		setupExecutiveEventHandlers();
	}

	// Load executive summary data
	loadExecutiveSummaryData = function() {
		console.log('Loading executive summary data...');
		
		// Load key metrics
		console.log('Loading executive metrics...');
		loadExecutiveMetrics();
		
		// Load team performance data
		console.log('Loading team performance data...');
		loadTeamPerformanceData();
		
		// Load performance trends
		console.log('Loading performance trends...');
		loadPerformanceTrends();
		
		// Load department overview
		console.log('Loading department overview...');
		loadDepartmentOverview();
	}

	// Load executive metrics
	loadExecutiveMetrics = function() {
		// Get current date and period
		const objectDate = new Date().toISOString().slice(0, 7); // YYYY-MM format
		const objectPeriod = 'months';
		
		console.log('Loading executive metrics with params:', { objectId: 'ind3', objectPeriod, objectDate });
		
		// Show loading state for metrics
		showMetricsLoadingState();
		
		const requestBody = `objectId=ind3&objectPeriod=${objectPeriod}&objectDate=${objectDate}`;
		console.log('Request body:', requestBody);
		
		fetch('/bpa/dashboards/get-exec-summary-details.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: requestBody
		})
		.then(response => {
			console.log('Response status:', response.status);
			console.log('Response headers:', response.headers);
			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}
			return response.text(); // Get response as text first for debugging
		})
		.then(text => {
			console.log('Raw response:', text);
			try {
				const data = JSON.parse(text);
				console.log('Executive metrics loaded:', data);
				updateExecutiveMetrics(data);
				hideMetricsLoadingState();
			} catch (parseError) {
				console.error('Error parsing JSON:', parseError);
				console.error('Raw response was:', text);
				throw new Error('Invalid JSON response from server');
			}
		})
		.catch(error => {
			console.error('Error loading executive metrics:', error);
			showMetricsErrorState(error);
			hideMetricsLoadingState();
		});
	}

	// Show loading state for metrics
	showMetricsLoadingState = function() {
		const metricCards = ['totalStaff', 'activeInitiatives', 'avgPerformance', 'lastUpdate'];
		metricCards.forEach(id => {
			const element = document.getElementById(id);
			if (element) {
				element.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
			}
		});
	}

	// Hide loading state for metrics
	hideMetricsLoadingState = function() {
		// Loading state will be replaced by actual data
	}

	// Show error state for metrics
	showMetricsErrorState = function(error) {
		const metricCards = ['totalStaff', 'activeInitiatives', 'avgPerformance', 'lastUpdate'];
		metricCards.forEach(id => {
			const element = document.getElementById(id);
			if (element) {
				element.innerHTML = '<i class="fas fa-exclamation-triangle text-warning"></i>';
				element.title = 'Error loading data: ' + error.message;
			}
		});
	}

	// Update executive metrics display
	updateExecutiveMetrics = function(data) {
		console.log('Updating executive metrics with data:', data);
		
		if (!data || data.length === 0) {
			console.log('No data to display');
			return;
		}
		
		// Calculate totals
		let totalInitiatives = 0;
		let totalPerformance = 0;
		let performanceCount = 0;
		let activeStaff = 0;
		
		data.forEach(staff => {
			console.log('Processing staff member:', staff);
			
			if (staff.taskCount) {
				totalInitiatives += parseInt(staff.taskCount);
			}
			
			// Handle performance score - the API returns it as "XX.XX% " (with space and trend icon)
			if (staff.indScore && staff.indScore.trim() !== ' ') {
				// Extract just the percentage part before the space
				const scorePart = staff.indScore.split(' ')[0];
				const perfValue = parseFloat(scorePart.replace('%', ''));
				if (!isNaN(perfValue)) {
					totalPerformance += perfValue;
					performanceCount++;
					console.log('Added performance score:', perfValue);
				}
			}
			
			// Check if staff has signed in (not "Never")
			if (staff.lastSignIn && !staff.lastSignIn.includes('Never')) {
				activeStaff++;
			}
		});
		
		console.log('Calculated totals:', {
			totalInitiatives,
			totalPerformance,
			performanceCount,
			activeStaff,
			totalStaff: data.length
		});
		
		// Update metric cards
		const totalStaffElement = document.getElementById('totalStaff');
		const activeInitiativesElement = document.getElementById('activeInitiatives');
		const avgPerformanceElement = document.getElementById('avgPerformance');
		const lastUpdateElement = document.getElementById('lastUpdate');
		
		if (totalStaffElement) {
			totalStaffElement.textContent = data.length;
			console.log('Updated total staff:', data.length);
		}
		
		if (activeInitiativesElement) {
			activeInitiativesElement.textContent = totalInitiatives;
			console.log('Updated active initiatives:', totalInitiatives);
		}
		
		if (avgPerformanceElement) {
			if (performanceCount > 0) {
				const avgPerf = (totalPerformance / performanceCount).toFixed(1);
				avgPerformanceElement.textContent = avgPerf + '%';
				console.log('Updated average performance:', avgPerf + '%');
			} else {
				avgPerformanceElement.textContent = 'N/A';
				console.log('No performance data available');
			}
		}
		
		if (lastUpdateElement) {
			lastUpdateElement.textContent = new Date().toLocaleDateString();
			console.log('Updated last update date');
		}
		
		// Update team performance table if it exists
		updateTeamPerformanceTable(data);
	}

	// Update team performance table
	updateTeamPerformanceTable = function(data) {
		const tableBody = document.querySelector('.table-executive tbody');
		if (!tableBody) return;
		
		// Clear existing rows
		tableBody.innerHTML = '';
		
		data.forEach(staff => {
			const row = document.createElement('tr');
			row.className = 'header expand';
			
			row.innerHTML = `
				<td>${staff.display_name}</td>
				<td>${staff.title}</td>
				<td>${staff.name || ''}</td>
				<td style="text-align:center">${staff.taskCount || 0}</td>
				<td style="text-align:center">${staff.updateCount || 0}</td>
				<td style="text-align:center">${staff.indScorePrevious || ''}</td>
				<td style="text-align:center" class="border-end-0">${staff.indScore || ''}</td>
				<td style="text-align:center" class="border-start-0">${staff.indScoreTrend || ''}</td>
				<td>${staff.lastSignIn || ''}</td>
			`;
			
			tableBody.appendChild(row);
		});
		
		// Reinitialize table expand/collapse functionality
		setupTableExpandCollapse();
	}

	// Load team performance data
	loadTeamPerformanceData = function() {
		// This function can be expanded to load additional team performance data
		console.log('Loading team performance data...');
	}

	// Load performance trends
	loadPerformanceTrends = function() {
		console.log('Loading performance trends...');
		loadChartData('performance_trends');
	}

	// Load department overview
	loadDepartmentOverview = function() {
		console.log('Loading department overview...');
		loadChartData('department_performance');
		loadChartData('initiative_status');
	}

	// Load chart data from server
	loadChartData = function(chartType) {
		const objectDate = new Date().toISOString().slice(0, 7); // YYYY-MM format
		const objectPeriod = 'months';
		
		console.log(`Loading chart data for ${chartType} with params:`, { chartType, objectPeriod, objectDate });
		
		// Show loading state for chart
		showChartLoadingState(chartType);
		
		const requestBody = `chartType=${chartType}&objectPeriod=${objectPeriod}&objectDate=${objectDate}`;
		console.log(`Request body for ${chartType}:`, requestBody);
		
		fetch('/bpa/dashboards/get-executive-chart-data.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: requestBody
		})
		.then(response => {
			console.log(`Response status for ${chartType}:`, response.status);
			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}
			return response.text(); // Get response as text first for debugging
		})
		.then(text => {
			console.log(`Raw response for ${chartType}:`, text);
			try {
				const data = JSON.parse(text);
				console.log(`Chart data loaded for ${chartType}:`, data);
				if (data.error) {
					throw new Error(data.error);
				}
				updateChart(chartType, data);
				hideChartLoadingState(chartType);
			} catch (parseError) {
				console.error(`Error parsing JSON for ${chartType}:`, parseError);
				console.error(`Raw response was:`, text);
				throw new Error('Invalid JSON response from server');
			}
		})
		.catch(error => {
			console.error(`Error loading chart data for ${chartType}:`, error);
			showChartErrorState(chartType, error);
			hideChartLoadingState(chartType);
		});
	}

	// Show loading state for chart
	showChartLoadingState = function(chartType) {
		const chartId = getChartId(chartType);
		const canvas = document.getElementById(chartId);
		if (canvas) {
			const ctx = canvas.getContext('2d');
			ctx.clearRect(0, 0, canvas.width, canvas.height);
			ctx.fillStyle = '#f8f9fa';
			ctx.fillRect(0, 0, canvas.width, canvas.height);
			ctx.fillStyle = '#6c757d';
			ctx.font = '14px Arial';
			ctx.textAlign = 'center';
			ctx.fillText('Loading...', canvas.width / 2, canvas.height / 2);
		}
	}

	// Hide loading state for chart
	hideChartLoadingState = function(chartType) {
		// Loading state will be replaced by chart data
	}

	// Show error state for chart
	showChartErrorState = function(chartType, error) {
		const chartId = getChartId(chartType);
		const canvas = document.getElementById(chartId);
		if (canvas) {
			const ctx = canvas.getContext('2d');
			ctx.clearRect(0, 0, canvas.width, canvas.height);
			ctx.fillStyle = '#f8f9fa';
			ctx.fillRect(0, 0, canvas.width, canvas.height);
			ctx.fillStyle = '#dc3545';
			ctx.font = '12px Arial';
			ctx.textAlign = 'center';
			ctx.fillText('Error loading data', canvas.width / 2, canvas.height / 2 - 10);
			ctx.fillText(error.message.substring(0, 30) + '...', canvas.width / 2, canvas.height / 2 + 10);
		}
	}

	// Get chart ID from chart type
	getChartId = function(chartType) {
		switch(chartType) {
			case 'performance_trends':
				return 'performanceTrendChart';
			case 'department_performance':
				return 'departmentPerformanceChart';
			case 'initiative_status':
				return 'initiativeStatusChart';
			default:
				return '';
		}
	}

	// Update chart with new data
	updateChart = function(chartType, data) {
		switch(chartType) {
			case 'performance_trends':
				updatePerformanceTrendChart(data);
				break;
			case 'department_performance':
				updateDepartmentPerformanceChart(data);
				break;
			case 'initiative_status':
				updateInitiativeStatusChart(data);
				break;
		}
	}

	// Initialize executive charts
	initializeExecutiveCharts = function() {
		console.log('Initializing executive charts...');
		
		// Check if Chart.js is available
		if (typeof Chart === 'undefined') {
			console.warn('Chart.js not available. Charts will not be initialized.');
			return;
		}
		
		// Initialize performance trend chart if container exists
		if (document.getElementById('performanceTrendChart')) {
			initPerformanceTrendChart();
		}
		
		// Initialize department performance chart if container exists
		if (document.getElementById('departmentPerformanceChart')) {
			initDepartmentPerformanceChart();
		}
		
		// Initialize initiative status chart if container exists
		if (document.getElementById('initiativeStatusChart')) {
			initInitiativeStatusChart();
		}
	}

	// Initialize performance trend chart
	initPerformanceTrendChart = function() {
		const ctx = document.getElementById('performanceTrendChart');
		if (!ctx) return;
		
		// Initialize with empty data - will be populated by loadChartData
		const chartData = {
			labels: [],
			datasets: [{
				label: 'Average Performance',
				data: [],
				borderColor: '#667eea',
				backgroundColor: 'rgba(102, 126, 234, 0.1)',
				tension: 0.4
			}]
		};
		
		window.performanceTrendChart = new Chart(ctx, {
			type: 'line',
			data: chartData,
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: false
					}
				},
				scales: {
					y: {
						beginAtZero: true,
						max: 100
					}
				}
			}
		});
	}

	// Initialize department performance chart
	initDepartmentPerformanceChart = function() {
		const ctx = document.getElementById('departmentPerformanceChart');
		if (!ctx) return;
		
		// Initialize with empty data - will be populated by loadChartData
		const chartData = {
			labels: [],
			datasets: [{
				label: 'Performance Score',
				data: [],
				backgroundColor: []
			}]
		};
		
		window.departmentPerformanceChart = new Chart(ctx, {
			type: 'bar',
			data: chartData,
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: false
					}
				},
				scales: {
					y: {
						beginAtZero: true,
						max: 100
					}
				}
			}
		});
	}

	// Initialize initiative status chart
	initInitiativeStatusChart = function() {
		const ctx = document.getElementById('initiativeStatusChart');
		if (!ctx) return;
		
		// Initialize with empty data - will be populated by loadChartData
		const chartData = {
			labels: [],
			datasets: [{
				data: [],
				backgroundColor: []
			}]
		};
		
		window.initiativeStatusChart = new Chart(ctx, {
			type: 'doughnut',
			data: chartData,
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						position: 'bottom'
					}
				}
			}
		});
	}

	// Update performance trend chart
	updatePerformanceTrendChart = function(data) {
		if (window.performanceTrendChart && data && data.labels) {
			window.performanceTrendChart.data.labels = data.labels;
			window.performanceTrendChart.data.datasets[0].data = data.datasets[0].data;
			window.performanceTrendChart.update();
		}
	}

	// Update department performance chart
	updateDepartmentPerformanceChart = function(data) {
		if (window.departmentPerformanceChart && data && data.labels) {
			window.departmentPerformanceChart.data.labels = data.labels;
			window.departmentPerformanceChart.data.datasets[0].data = data.datasets[0].data;
			window.departmentPerformanceChart.data.datasets[0].backgroundColor = data.datasets[0].backgroundColor;
			window.departmentPerformanceChart.update();
		}
	}

	// Update initiative status chart
	updateInitiativeStatusChart = function(data) {
		if (window.initiativeStatusChart && data && data.labels) {
			window.initiativeStatusChart.data.labels = data.labels;
			window.initiativeStatusChart.data.datasets[0].data = data.datasets[0].data;
			window.initiativeStatusChart.data.datasets[0].backgroundColor = data.datasets[0].backgroundColor;
			window.initiativeStatusChart.update();
		}
	}

	// Setup executive event handlers
	setupExecutiveEventHandlers = function() {
		// Setup table expand/collapse functionality
		setupTableExpandCollapse();
		
		// Setup refresh button
		const refreshBtn = document.querySelector('button[onclick="refreshDashboard()"]');
		if (refreshBtn) {
			refreshBtn.onclick = function() {
				loadExecutiveSummaryData();
			};
		}
		
		// Setup export button
		const exportBtn = document.querySelector('button[onclick="exportReport()"]');
		if (exportBtn) {
			exportBtn.onclick = function() {
				window.print();
			};
		}

		// Setup time period selector if it exists
		const timePeriodSelect = document.getElementById('timePeriodSelect');
		if (timePeriodSelect) {
			timePeriodSelect.onchange = function() {
				loadExecutiveSummaryData();
			};
		}

		// Setup date range selector if it exists
		const dateRangeSelect = document.getElementById('dateRangeSelect');
		if (dateRangeSelect) {
			dateRangeSelect.onchange = function() {
				loadExecutiveSummaryData();
			};
		}
	}

	// Enhanced refresh function for executive summary
	refreshExecutiveDashboard = function() {
		console.log('Refreshing executive dashboard...');
		
		// Show loading indicator
		showLoadingIndicator();
		
		// Reload all data
		loadExecutiveSummaryData();
		
		// Hide loading indicator after a delay
		setTimeout(hideLoadingIndicator, 2000);
	}

	// Show loading indicator
	showLoadingIndicator = function() {
		const loadingDiv = document.createElement('div');
		loadingDiv.id = 'executiveLoadingIndicator';
		loadingDiv.innerHTML = `
			<div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); 
						background: rgba(0,0,0,0.8); color: white; padding: 20px; 
						border-radius: 10px; z-index: 9999;">
				<i class="fas fa-spinner fa-spin me-2"></i>
				Loading Executive Summary Data...
			</div>
		`;
		document.body.appendChild(loadingDiv);
	}

	// Hide loading indicator
	hideLoadingIndicator = function() {
		const loadingDiv = document.getElementById('executiveLoadingIndicator');
		if (loadingDiv) {
			loadingDiv.remove();
		}
	}

	// Test function for debugging - can be called from browser console
	testExecutiveSummary = function() {
		console.log('Testing executive summary functionality...');
		console.log('Chart.js available:', typeof Chart !== 'undefined');
		console.log('jQuery available:', typeof $ !== 'undefined');
		console.log('initializeExecutiveSummary function available:', typeof initializeExecutiveSummary === 'function');
		
		// Test if we can access the DOM elements
		console.log('Performance trend chart element:', document.getElementById('performanceTrendChart'));
		console.log('Department performance chart element:', document.getElementById('departmentPerformanceChart'));
		console.log('Initiative status chart element:', document.getElementById('initiativeStatusChart'));
		
		// Test if we can access the metric elements
		console.log('Total staff element:', document.getElementById('totalStaff'));
		console.log('Active initiatives element:', document.getElementById('activeInitiatives'));
		console.log('Average performance element:', document.getElementById('avgPerformance'));
		console.log('Last update element:', document.getElementById('lastUpdate'));
		
		// Try to initialize if function is available
		if (typeof initializeExecutiveSummary === 'function') {
			console.log('Calling initializeExecutiveSummary...');
			initializeExecutiveSummary();
		} else {
			console.log('initializeExecutiveSummary function not available');
		}
	}

	// Setup table expand/collapse functionality
	setupTableExpandCollapse = function() {
		// Remove existing event listeners
		$(document).off('click', 'tr.header');
		
		// Add new event listeners
		$(document).on('click', 'tr.header', function() {
			$(this).toggleClass('expand').nextUntil('tr.header').slideToggle(100);
		});
	}

	teamProductivityDashboard = function()
	{
		safeContentPaneTransition("dashboards/team-productivity-analytics.php", "Team Productivity");
	}

	goalTrackingDashboard = function()
	{
		safeContentPaneTransition("dashboards/goal-achievement-tracking.php", "Goal Tracking");
	}

	performanceHeatMaps = function()
	{
		console.log('Loading Performance Heat Maps dashboard...');
		safeContentPaneTransition("dashboards/performance-heat-maps.php", "Performance Heat Maps");
		
		// Wait for the page to load, then trigger data loading
		setTimeout(function() {
			console.log('Performance heat maps dashboard loaded, triggering data load...');
			// The dashboard has its own initialization, but we can trigger it manually if needed
			triggerHeatMapDataLoad();
		}, 2000); // Increased timeout to ensure dashboard is fully loaded
	}

	// Trigger heat map data loading
	triggerHeatMapDataLoad = function() {
		console.log('Triggering heat map data load...');
		
		// Check if the dashboard is loaded and has the required elements
		const mainHeatMap = document.getElementById('mainHeatMap');
		const departmentSelect = document.getElementById('departmentSelect');
		
		if (!mainHeatMap) {
			console.log('Heat map container not found, dashboard may not be fully loaded');
			// Try again after a delay
			setTimeout(function() {
				triggerHeatMapDataLoad();
			}, 1000);
			return;
		}
		
		console.log('Dashboard elements found, checking department options...');
		
		// Check if department options are loaded
		if (!departmentSelect || departmentSelect.options.length <= 1) {
			console.log('Department options not loaded, loading them now...');
			loadDepartmentOptions();
			
			// Wait for departments to load before loading data
			setTimeout(function() {
				loadHeatMapData();
			}, 1000);
		} else {
			console.log('Department options already loaded, loading heat map data...');
			loadHeatMapData();
		}
	}

	// Load department options for performance heat maps
	loadDepartmentOptions = function() {
		console.log('Loading department options...');
		
		const departmentSelect = document.getElementById('departmentSelect');
		if (!departmentSelect) {
			console.log('Department select not found - element may not exist yet');
			// Try again after a short delay
			setTimeout(function() {
				loadDepartmentOptions();
			}, 500);
			return;
		}
		
		console.log('Department select found, current options:', departmentSelect.options.length);
		
		// Check if options are already loaded
		if (departmentSelect.options.length > 1) {
			console.log('Department options already loaded');
			return;
		}
		
		fetch('/bpa/dashboards/get-department-list.php')
			.then(response => {
				console.log('Department API response status:', response.status);
				if (!response.ok) {
					throw new Error(`HTTP error! status: ${response.status}`);
				}
				return response.json();
			})
			.then(data => {
				console.log('Department data received:', data);
				
				// Clear existing options
				departmentSelect.innerHTML = '';
				
				// Add department options
				if (data.departments && data.departments.length > 0) {
					data.departments.forEach(dept => {
						const option = document.createElement('option');
						option.value = dept.id;
						option.textContent = dept.name;
						departmentSelect.appendChild(option);
					});
					
					// Set default selection to first department
					if (data.departments.length > 0) {
						departmentSelect.value = data.departments[0].id;
						console.log('Set default department to:', data.departments[0].name);
					}
					
					console.log('Department options loaded successfully, total options:', departmentSelect.options.length);
				} else {
					console.log('No departments found in response');
					// Add a default option if no departments
					const defaultOption = document.createElement('option');
					defaultOption.value = '';
					defaultOption.textContent = 'No departments available';
					departmentSelect.appendChild(defaultOption);
				}
			})
			.catch(error => {
				console.error('Error loading department options:', error);
				// Add error option
				const errorOption = document.createElement('option');
				errorOption.value = '';
				errorOption.textContent = 'Error loading departments';
				departmentSelect.appendChild(errorOption);
			});
	}

	// Initialize basic heat map charts when enhanced functions are not available
	initializeBasicHeatMapCharts = function() {
		console.log('Initializing basic heat map charts...');
		
		// Load heat map data
		loadHeatMapData();
		
		// Initialize basic charts if Chart.js is available
		if (typeof Chart !== 'undefined') {
			// Initialize basic charts here if needed
			console.log('Chart.js available for basic heat map charts');
		}
	}

	// Load heat map data
	loadHeatMapData = function() {
		console.log('Loading heat map data...');
		
		// Get current parameters
		const departmentSelect = document.getElementById('departmentSelect');
		const departmentId = departmentSelect ? departmentSelect.value : 'org1';
		let heatMapType = document.getElementById('heatMapTypeSelect')?.value || 'performance';
		const metric = document.getElementById('metricSelect')?.value || 'overall';
		const period = document.getElementById('periodSelect')?.value || 'months';
		const date = new Date().toISOString().slice(0, 7); // YYYY-MM format
		
		// Map heat map type values to API expected values
		const heatMapTypeMap = {
			'performance': 'overview',
			'team': 'team',
			'departments': 'departments',
			'matrix': 'matrix'
		};
		
		heatMapType = heatMapTypeMap[heatMapType] || 'overview';
		
		console.log('Department select found:', !!departmentSelect);
		console.log('Selected department ID:', departmentId);
		console.log('Original heat map type:', document.getElementById('heatMapTypeSelect')?.value);
		console.log('Mapped heat map type:', heatMapType);
		
		console.log('Loading heat map data with params:', { departmentId, heatMapType, metric, period, date });
		
		// Show loading state
		showHeatMapLoadingState();
		
		// Use URL-encoded form data instead of FormData for better compatibility
		const formData = `departmentId=${encodeURIComponent(departmentId)}&type=${encodeURIComponent(heatMapType)}&metric=${encodeURIComponent(metric)}&period=${encodeURIComponent(period)}&date=${encodeURIComponent(date)}`;
		
		fetch('/bpa/dashboards/get-heatmap-data.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: formData
		})
		.then(response => {
			console.log('Heat map response status:', response.status);
			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}
			return response.json();
		})
		.then(data => {
			console.log('Heat map data loaded:', data);
			updateHeatMapDisplay(data);
			hideHeatMapLoadingState();
		})
		.catch(error => {
			console.error('Error loading heat map data:', error);
			showHeatMapErrorState(error);
			hideHeatMapLoadingState();
		});
	}

	// Show loading state for heat maps
	showHeatMapLoadingState = function() {
		const containers = ['mainHeatMap', 'distributionChart', 'teamTrendsChart'];
		containers.forEach(id => {
			const element = document.getElementById(id);
			if (element) {
				element.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><br><small>Loading heat map data...</small></div>';
			}
		});
	}

	// Hide loading state for heat maps
	hideHeatMapLoadingState = function() {
		// Loading state will be replaced by actual data
	}

	// Show error state for heat maps
	showHeatMapErrorState = function(error) {
		const containers = ['mainHeatMap', 'distributionChart', 'teamTrendsChart'];
		containers.forEach(id => {
			const element = document.getElementById(id);
			if (element) {
				element.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error loading heat map data: ' + error.message + '</div>';
			}
		});
	}

	// Update heat map display
	updateHeatMapDisplay = function(data) {
		if (!data || data.error) {
			console.error('Heat map data error:', data?.error);
			return;
		}
		
		// Update heat map container
		const heatMapContainer = document.getElementById('mainHeatMap');
		if (heatMapContainer && data.heatMapData) {
			renderHeatMap(heatMapContainer, data.heatMapData);
		}
		
		// Update distribution chart
		if (data.distribution && typeof Chart !== 'undefined') {
			updateDistributionChart(data.distribution);
		}
		
		// Update trends chart
		if (data.trends && typeof Chart !== 'undefined') {
			updateTeamTrendsChart(data.trends);
		}
		
		// Update summary
		if (data.summary) {
			updateHeatMapSummary(data.summary);
		}
		
		// Update hot spots
		if (data.hotSpots) {
			updateHotSpots(data.hotSpots);
		}
	}

	// Render heat map
	renderHeatMap = function(container, heatMapData) {
		if (!container || !heatMapData || heatMapData.length === 0) {
			container.innerHTML = '<p class="text-muted">No heat map data available.</p>';
			return;
		}
		
		let html = '<div class="heatmap-grid">';
		heatMapData.forEach(item => {
			const score = parseFloat(item.score);
			let colorClass = 'heat-critical';
			if (score >= 90) colorClass = 'heat-excellent';
			else if (score >= 75) colorClass = 'heat-good';
			else if (score >= 60) colorClass = 'heat-average';
			else if (score >= 40) colorClass = 'heat-poor';
			
			html += `
				<div class="heat-cell ${colorClass}" 
					 onclick="showDetailModal(${JSON.stringify(item).replace(/"/g, '&quot;')})"
					 title="${item.name}: ${score}%">
					<div class="heat-cell-content">
						<div class="heat-cell-name">${item.name}</div>
						<div class="heat-cell-score">${score}%</div>
					</div>
				</div>
			`;
		});
		html += '</div>';
		
		container.innerHTML = html;
	}

	// Update distribution chart
	updateDistributionChart = function(distribution) {
		const ctx = document.getElementById('distributionChart');
		if (!ctx) return;
		
		const chart = new Chart(ctx, {
			type: 'doughnut',
			data: {
				labels: ['Excellent (90%+)', 'Good (75-89%)', 'Average (60-74%)', 'Poor (40-59%)', 'Critical (<40%)'],
				datasets: [{
					data: [
						distribution.excellent || 0,
						distribution.good || 0,
						distribution.average || 0,
						distribution.poor || 0,
						distribution.critical || 0
					],
					backgroundColor: [
						'#28a745',
						'#20c997',
						'#ffc107',
						'#fd7e14',
						'#dc3545'
					]
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						position: 'bottom'
					}
				}
			}
		});
	}

	// Update trends chart
	updateTeamTrendsChart = function(trends) {
		const ctx = document.getElementById('teamTrendsChart');
		if (!ctx) return;
		
		const chart = new Chart(ctx, {
			type: 'line',
			data: {
				labels: trends.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
				datasets: [{
					label: 'Performance Trend',
					data: trends.data || [75, 78, 82, 79, 85, 88],
					borderColor: '#667eea',
					backgroundColor: 'rgba(102, 126, 234, 0.1)',
					tension: 0.4
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: false
					}
				},
				scales: {
					y: {
						beginAtZero: true,
						max: 100
					}
				}
			}
		});
	}

	// Update heat map summary
	updateHeatMapSummary = function(summary) {
		const summaryContainer = document.getElementById('performanceSummary');
		if (summaryContainer && summary) {
			summaryContainer.innerHTML = `
				<div class="row text-center">
					<div class="col-6 mb-3">
						<h6 class="text-primary">${summary.avgScore || 0}%</h6>
						<small class="text-muted">Average Score</small>
					</div>
					<div class="col-6 mb-3">
						<h6 class="text-success">${summary.topPerformers || 0}</h6>
						<small class="text-muted">Top Performers</small>
					</div>
					<div class="col-6">
						<h6 class="text-warning">${summary.needsImprovement || 0}</h6>
						<small class="text-muted">Needs Improvement</small>
					</div>
					<div class="col-6">
						<h6 class="text-info">${summary.scoreRange || 0}%</h6>
						<small class="text-muted">Score Range</small>
					</div>
				</div>
			`;
		}
	}

	// Update hot spots
	updateHotSpots = function(hotSpots) {
		const hotSpotsContainer = document.getElementById('hotSpotsAnalysis');
		if (hotSpotsContainer && hotSpots) {
			if (hotSpots.length === 0) {
				hotSpotsContainer.innerHTML = '<p class="text-muted">No hot spots identified.</p>';
				return;
			}
			
			let html = '';
			hotSpots.forEach(spot => {
				const typeClass = spot.type === 'high' ? 'text-success' : 'text-danger';
				const icon = spot.type === 'high' ? 'fa-fire' : 'fa-exclamation-triangle';
				
				html += `
					<div class="d-flex align-items-center mb-2">
						<i class="fas ${icon} ${typeClass} me-2"></i>
						<div>
							<strong>${spot.area}</strong><br>
							<small class="text-muted">${spot.description}</small>
						</div>
					</div>
				`;
			});
			
			hotSpotsContainer.innerHTML = html;
		}
	}

	// Test function to verify dashboard functionality
	testDashboardFunctions = function() {
		console.log('Testing dashboard functions...');
		
		// Test executive summary function
		console.log('Testing executiveSummary function...');
		if (typeof executiveSummary === 'function') {
			console.log('✓ executiveSummary function exists');
		} else {
			console.log('✗ executiveSummary function missing');
		}
		
		// Test performance heat maps function
		console.log('Testing performanceHeatMaps function...');
		if (typeof performanceHeatMaps === 'function') {
			console.log('✓ performanceHeatMaps function exists');
		} else {
			console.log('✗ performanceHeatMaps function missing');
		}
		
		// Test data loading functions
		console.log('Testing data loading functions...');
		if (typeof loadExecutiveSummaryData === 'function') {
			console.log('✓ loadExecutiveSummaryData function exists');
		} else {
			console.log('✗ loadExecutiveSummaryData function missing');
		}
		
		if (typeof loadHeatMapData === 'function') {
			console.log('✓ loadHeatMapData function exists');
		} else {
			console.log('✗ loadHeatMapData function missing');
		}
		
		if (typeof loadDepartmentOptions === 'function') {
			console.log('✓ loadDepartmentOptions function exists');
		} else {
			console.log('✗ loadDepartmentOptions function missing');
		}
		
		// Test API endpoints
		console.log('Testing API endpoints...');
		fetch('/bpa/dashboards/get-exec-summary-details.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: 'objectId=ind7&objectPeriod=months&objectDate=' + new Date().toISOString().slice(0, 7)
		})
		.then(response => {
			console.log('✓ Executive summary API endpoint accessible (status:', response.status, ')');
		})
		.catch(error => {
			console.log('✗ Executive summary API endpoint error:', error.message);
		});
		
		const formData = new FormData();
		formData.append('departmentId', 'org1');
		formData.append('type', 'overview');
		formData.append('metric', 'overall');
		formData.append('period', 'months');
		formData.append('date', new Date().toISOString().slice(0, 7));
		
		fetch('/bpa/dashboards/get-heatmap-data.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: `departmentId=org1&type=overview&metric=overall&period=months&date=${new Date().toISOString().slice(0, 7)}`
		})
		.then(response => {
			console.log('✓ Heat map API endpoint accessible (status:', response.status, ')');
		})
		.catch(error => {
			console.log('✗ Heat map API endpoint error:', error.message);
		});
	}

	// Manual trigger functions for testing
	manualLoadExecutiveSummary = function() {
		console.log('Manually triggering executive summary data load...');
		if (typeof triggerExecutiveSummaryDataLoad === 'function') {
			triggerExecutiveSummaryDataLoad();
		} else {
			console.log('triggerExecutiveSummaryDataLoad function not available');
			// Fallback to direct data loading
			if (typeof loadExecutiveSummaryData === 'function') {
				loadExecutiveSummaryData();
			}
		}
	}

	manualLoadHeatMaps = function() {
		console.log('Manually triggering heat map data load...');
		if (typeof triggerHeatMapDataLoad === 'function') {
			triggerHeatMapDataLoad();
		} else {
			console.log('triggerHeatMapDataLoad function not available');
			// Fallback to direct data loading
			if (typeof loadHeatMapData === 'function') {
				loadHeatMapData();
			}
		}
	}

	manualLoadDepartments = function() {
		console.log('Manually loading department options...');
		if (typeof loadDepartmentOptions === 'function') {
			loadDepartmentOptions();
		} else {
			console.log('loadDepartmentOptions function not available');
		}
	}

	checkDashboardElements = function() {
		console.log('Checking dashboard elements...');
		
		const elements = [
			'departmentSelect',
			'mainHeatMap',
			'distributionChart',
			'teamTrendsChart',
			'performanceSummary',
			'hotSpotsAnalysis'
		];
		
		elements.forEach(id => {
			const element = document.getElementById(id);
			console.log(`${id}: ${element ? 'Found' : 'Not found'}`);
			if (element && element.tagName === 'SELECT') {
				console.log(`  - Options: ${element.options.length}`);
				console.log(`  - Value: ${element.value}`);
				if (element.options.length > 0) {
					console.log(`  - First option: ${element.options[0].text}`);
				}
			}
		});
	}

	forceReloadDepartments = function() {
		console.log('Force reloading departments...');
		const departmentSelect = document.getElementById('departmentSelect');
		if (departmentSelect) {
			// Clear existing options
			departmentSelect.innerHTML = '';
			console.log('Cleared existing department options');
			
			// Reload departments
			loadDepartmentOptions();
		} else {
			console.log('Department select not found');
		}
	}

	staffManagementDashboard = function()
	{
		safeContentPaneTransition("dashboards/staff-management-analytics.php", "Staff Management");
	}

	predictiveAnalytics = function()
	{
		safeContentPaneTransition("dashboards/predictive-trend-analytics.php", "Predictive Analytics");
	}

	initiativeProjectAnalytics = function()
	{
		safeContentPaneTransition("dashboards/initiative-project-analytics.php", "Initiative Analytics");
	}

	// Global stub functions for dashboard-specific functionality
	// These prevent errors when dashboard-specific functions are called from global context
	// Functions are called from onchange attributes in dashboard select elements
	// When dashboards are loaded via ContentPane, these provide safe fallbacks
	changeStaffView = function() {
		// If we're in the staff management dashboard context, delegate to the dashboard function
		if (typeof window.staffManagementDashboard !== 'undefined' && window.staffManagementDashboard.changeStaffView) {
			window.staffManagementDashboard.changeStaffView();
		} else {
			// Otherwise, navigate to the staff management dashboard
			staffManagementDashboard();
		}
	}

	changeDepartment = function() {
		console.log('changeDepartment called from global context');
		
		// Check if we're in the performance heat maps dashboard
		const departmentSelect = document.getElementById('departmentSelect');
		if (departmentSelect) {
			console.log('Found department select in performance heat maps dashboard');
			
			// Get the selected department
			const selectedDepartmentId = departmentSelect.value;
			console.log('Selected department ID:', selectedDepartmentId);
			
			// Update the current department ID and reload data
			// Since the dashboard functions are in an IIFE, we need to trigger the data reload manually
			triggerHeatMapDataLoad();
		} else {
			console.log('Department select not found, may not be in performance heat maps dashboard');
		}
	}

	changePerformanceFilter = function() {
		// Similar stub for performance filter changes
		if (typeof window.staffManagementDashboard !== 'undefined' && window.staffManagementDashboard.changePerformanceFilter) {
			window.staffManagementDashboard.changePerformanceFilter();
		} else {
			console.log('changePerformanceFilter called from global context');
		}
	}

	// Additional dashboard function stubs
	changePeriod = function() {
		console.log('changePeriod called from global context');
	}

	changeForecastHorizon = function() {
		console.log('changeForecastHorizon called from global context');
	}

	changeAnalysisType = function() {
		console.log('changeAnalysisType called from global context');
	}

	changeConfidenceLevel = function() {
		console.log('changeConfidenceLevel called from global context');
	}

	refreshDashboard = function() {
		console.log('refreshDashboard called from global context');
		location.reload();
	}

	exportReport = function() {
		console.log('exportReport called from global context');
		window.print();
	}

	// Additional time period and filter functions
	updateTimePeriod = function() {
		console.log('updateTimePeriod called from global context');
	}

	changeStatusFilter = function() {
		console.log('changeStatusFilter called from global context');
	}

	changeMetric = function() {
		console.log('changeMetric called from global context');
	}

	changeHeatMapType = function() {
		console.log('changeHeatMapType called from global context');
	}

	// Additional dashboard-specific functions that were found in onchange attributes
	changePortfolioView = function() {
		console.log('changePortfolioView called from global context');
	}

	// Generic utility functions for dashboards
	changeTimeRange = function() {
		console.log('changeTimeRange called from global context');
	}

	updateDateRange = function() {
		console.log('updateDateRange called from global context');
	}

	toggleView = function() {
		console.log('toggleView called from global context');
	}

	filterData = function() {
		console.log('filterData called from global context');
	}
	myDataEntry = function()
	{
		
		if(dijit.byId("newInitiativeDialog")) 
		{
			//console.log("Destroying newInitiativeDialog");
			dijit.byId("newInitiativeDialog").destroy(true);
			if(dijit.byId("newInitiativeDialog")) dijit.byId("newInitiativeDialog").destroyRecursive();
		}
			
			domConstruct.destroy("dataEntryUserId");
			domConstruct.destroy("individualInput");
			if(dijit.byId("individualInput"))
				dijit.byId("individualInput").destroy(true);
			domConstruct.destroy("msgContent");
			domConstruct.destroy("myDivMeasures");
			domConstruct.destroy("myMeasureContent");
			domConstruct.destroy("myDivInitiatives");
			domConstruct.destroy("myInitiativeContent");
			domConstruct.destroy("myDivValues");
			domConstruct.destroy("myValuesContent");
			domConstruct.destroy("myDivDevelopmentPlan");
			domConstruct.destroy("myPdp");
			
			domConstruct.destroy("myDivInterpretation");
			domConstruct.destroy("myInterpretation");
			if(dijit.byId("myInterpretation"))
			{
				dijit.byId("myInterpretation").destroy(true);
				if(dijit.byId("myInterpretation")) 
				{
					dijit.byId("myInterpretation").destroyRecursive();
				}
			}
			
			if(dijit.byId("bulkMeasureDialogGoal"))
			{
				dijit.byId("bulkMeasureDialogGoal").destroy(true);
				if(dijit.byId("bulkMeasureDialogGoal")) dijit.byId("bulkMeasureDialogGoal").destroyRecursive();	
			}
			domConstruct.destroy("gridKpi");
			
			if(dijit.byId("bulkMeasureDialog2"))
			{//console.log("this id exists");
				dijit.byId("bulkMeasureDialog2").destroy(true);
				if(dijit.byId("bulkMeasureDialog2"))
				{//console.log("and it did not get cleared above");
					dijit.byId("bulkMeasureDialog2").destroyRecursive();
				}
			}
			domConstruct.destroy("gridKpi2");
			
			if(dijit.byId("bulkMeasureDialog3"))
			{
				dijit.byId("bulkMeasureDialog3").destroy(true);
				if(dijit.byId("bulkMeasureDialog3")) 
				{
					dijit.byId("bulkMeasureDialog3").destroyRecursive();
				}
			}
			domConstruct.destroy("gridKpi3");
			
			if(dijit.byId("bulkMeasureDialog4"))
			{
				dijit.byId("bulkMeasureDialog4").destroy(true);
				if(dijit.byId("bulkMeasureDialog4")) dijit.byId("bulkMeasureDialog4").destroyRecursive();
			}
			domConstruct.destroy("gridKpi4");
			
			//Remember to copy paste the list below whenever you update the initiative form since these are shared with initiative.php. LTK 03May2021 2109 Hours
			if(dijit.byId("initiativeListSelect")) dijit.byId("initiativeListSelect").destroy(true);
			if(dijit.byId("initiativeStartInput")) dijit.byId("initiativeStartInput").destroy(true);
			if(dijit.byId("newInitiativeManagerInput")) dijit.byId("newInitiativeManagerInput").destroy(true);
			if(dijit.byId("initiativeManagerInput")) dijit.byId("initiativeManagerInput").destroy(true);
			if(dijit.byId("initiativeSponsorInput")) dijit.byId("initiativeSponsorInput").destroy(true);
			if(dijit.byId("initiativeDueInput")) dijit.byId("initiativeDueInput").destroy(true);
			if(dijit.byId("initiativeCompleteInput")) dijit.byId("initiativeCompleteInput").destroy(true);

			domConstruct.destroy("nav-home-tab"); //LTK 16 March 2021 0751 hours
			domConstruct.destroy("nav-allInitiatives-tab");
			domConstruct.destroy("nav-home");
			domConstruct.destroy("nav-gantt-tab");
			domConstruct.destroy("nav-gantt");
			domConstruct.destroy("initiativeNameDiv");
			domConstruct.destroy("objectImpactedDiv")
			domConstruct.destroy("archive");
			domConstruct.destroy("sponsorDiv");
			domConstruct.destroy("managerDiv");
			domConstruct.destroy("parentDiv");
			domConstruct.destroy("deliverableDiv");
			domConstruct.destroy("scopeDiv");
			domConstruct.destroy("budgetDiv");
			domConstruct.destroy("tdDamageColor");
			domConstruct.destroy("damageDiv");
			domConstruct.destroy("startDateDiv");
			domConstruct.destroy("endDateDiv");
			domConstruct.destroy("completionDateDiv");
			domConstruct.destroy("initiativeGauge");
			domConstruct.destroy("statusDiv");//LTK. 25.03.2018 12.45am
			domConstruct.destroy("percentageCompletionDiv");
			domConstruct.destroy("statusDetailsDiv");
			domConstruct.destroy("statusNotesDiv");
			domConstruct.destroy("testTd");
			
			domConstruct.destroy("gantt");
			// Properly destroy conversation widgets
			if(registry.byId("divConversation")) {
				registry.byId("divConversation").destroyRecursive();
			}
			if(registry.byId("divAdvocacyConversation")) {
				registry.byId("divAdvocacyConversation").destroyRecursive();
			}
			if(registry.byId("divInitiativeConversation")) {
				registry.byId("divInitiativeConversation").destroyRecursive();
			}
			if(registry.byId("divScorecardConversation")) {
				registry.byId("divScorecardConversation").destroyRecursive();
			}
			// Clean up DOM elements after widget destruction
			domConstruct.destroy('divConversation');
			domConstruct.destroy('divAdvocacyConversation');
			domConstruct.destroy('divInitiativeConversation');
			domConstruct.destroy('divScorecardConversation');
			domConstruct.destroy('conversationHistory');
			domConstruct.destroy('conversation');
			domConstruct.destroy('submitId');

			domConstruct.destroy("nav-allInitiatives");
			domConstruct.destroy("tableInitiative");
			
			domConstruct.destroy("newInitiativeDialog-table");
			
			domConstruct.destroy("initiativeName");
			if(dijit.byId("initiativeNameInput")) dijit.byId("initiativeNameInput").destroy(true);
			domConstruct.destroy("initiativeDeliverable");
			domConstruct.destroy("initiativeDeliverableInput");
			domConstruct.destroy("initiativeScope");
			if(dijit.byId("scopeInput")) dijit.byId("scopeInput").destroy(true);
			domConstruct.destroy("initiativeDeliverable");
			domConstruct.destroy("deliverableStatusInput");
			domConstruct.destroy("initiativeSponsor");
			domConstruct.destroy("initiativeSponsorInput");
			domConstruct.destroy("initiativeManager");
			domConstruct.destroy("initiativeManagerInput");
			domConstruct.destroy("initiativeTeam");
			
			//domConstruct.destroy("initiativeTeamInput");
			if(dijit.byId("initiativeTeamInput")) dijit.byId("initiativeTeamInput").destroy(true);
			domConstruct.destroy("teamMembers");
			domConstruct.destroy("initiativeParent");
			domConstruct.destroy("initiativeParentInput");
			if(dijit.byId("initiativeParentInput")) 
			{
				dijit.byId("initiativeParentInput").destroyRecursive();
				if(dijit.byId("initiativeParentInput")) dijit.byId("initiativeParentInput").destroy(true);
			}
			domConstruct.destroy("initiativeLink");
			//domConstruct.destroy("initiativeLinkInput");
			if(dijit.byId("initiativeLinkInput")) 
			{
				dijit.byId("initiativeLinkInput").destroyRecursive();
				if(dijit.byId("initiativeLinkInput")) dijit.byId("initiativeLinkInput").destroy(true);
			}
			domConstruct.destroy("initiativeBudget");
			domConstruct.destroy("initiativeBudgetInput");
			domConstruct.destroy("initiativeCost");
			domConstruct.destroy("initiativeCostInput");
			
			domConstruct.destroy("initiativeStart");
			domConstruct.destroy("initiativeStartInput");
			if(dijit.byId("initiativeStartInput")) dijit.byId("initiativeStartInput").destroy(true);
			
			domConstruct.destroy("initiativeDue");
			domConstruct.destroy("initiativeDueInput");
			if(dijit.byId("initiativeDueInput")) dijit.byId("initiativeDueInput").destroy(true);
			
			domConstruct.destroy("initiativeComplete");
			domConstruct.destroy("initiativeCompleteInput");
			if(dijit.byId("initiativeComplete")) dijit.byId("initiativeComplete").destroy(true);
			
			domConstruct.destroy("initiativeStatus");
			domConstruct.destroy("initiativeStatusInput");
			domConstruct.destroy("initiativeStatusDetails");
			if(dijit.byId("initiativeStatusDetailsInput")) 
			{
				dijit.byId("initiativeStatusDetailsInput").destroy(true);
				if(dijit.byId("initiativeStatusDetailsInput")) dijit.byId("initiativeStatusDetailsInput").destroyRecursive();
			}
			domConstruct.destroy("initiativeNotes");
			if(dijit.byId("initiativeNotesInput")) dijit.byId("initiativeNotesInput").destroy(true);
			domConstruct.destroy("percentageCompletion");
			domConstruct.destroy("percentageCompletionInput");
			
			//Clearing the ones for PIP LTK 06May2021 0823Hrs
			if(dijit.byId("pdpDialog"))
			{//console.log(" Destroying pdpDialog")
				dijit.byId("pdpDialog").destroy(true);
				if(dijit.byId("pdpDialog")) dijit.byId("pdpDialog").destroyRecursive();
			}
			if(dijit.byId("newInitiativeDialog")) 
			{//console.log("Destroying newInitiativeDialog");
				dijit.byId("newInitiativeDialog").destroy(true);
				dijit.byId("newInitiativeDialog").destroyRecursive();
			}
			domConstruct.destroy("pdpDialog-table");
			domConstruct.destroy("pdpSkillGap");
			domConstruct.destroy("pdpSkillGapInput");
			domConstruct.destroy("pdpIntervention");
			domConstruct.destroy("pdpInterventionInput");
			domConstruct.destroy("pdpComments");
			domConstruct.destroy("pdpCommentsInput");
			domConstruct.destroy("pdpResource");
			domConstruct.destroy("pdpResourceInput");
			domConstruct.destroy("pdpStart");
			if(dijit.byId("pdpStartInput")) dijit.byId("pdpStartInput").destroy(true);
			domConstruct.destroy("pdpDue");
			if(dijit.byId("pdpDueInput")) dijit.byId("pdpDueInput").destroy(true);
			domConstruct.destroy("pdpComplete");
			if(dijit.byId("pdpCompleteInput")) dijit.byId("pdpCompleteInput").destroy(true);
			
			//var cpWait = setTimeout(function(){
			cp = new ContentPane({
			region: "center",
			"class": "bpaPrint",
				href:"dataEntry/myDataEntry.php"
				});
				cp.placeAt("appLayout");
		//},300);
		
	}
goHome = function()
{
mainMenuState = "Home";
	cp = new ContentPane({
		region: "center",
		"class": "bpaPrint",
	href:"dashboards/indDashboard.php"
	//href:"hacoVisualBoard.png"
	});
	cp.placeAt("appLayout");

	/*
	domStyle.set(dom.byId("userSettings"), "display", "none");

	domStyle.set(dom.byId("definitionTables"), "display", "none");
	domStyle.set(dom.byId("homeLinks"), "display", "block");*/
	var treeTimeout = setTimeout(function()
	{
		domStyle.set(dom.byId("tree"), "display", "none");
		domStyle.set(dom.byId("expandCollapse"), "display", "none");
	},200);

	
	domConstruct.destroy("divIndGroup1");

	domConstruct.destroy("divIndGauge");
	domConstruct.destroy("divIndWeight");
	domConstruct.destroy("myIndGauge");

	domConstruct.destroy("divIndGroup2");
	domConstruct.destroy("divIndDescription");
	domConstruct.destroy("divIndPhoto");
	domConstruct.destroy("indPhoto");
	domConstruct.destroy("indDescription");

	domConstruct.destroy("divIndChart");
	domConstruct.destroy("indChart");

	domConstruct.destroy("divIndInitiatives");
	domConstruct.destroy("initiativeIndContent");

	domConstruct.destroy("divIndCascadedTo");
	domConstruct.destroy("cascadedIndContent");

	domConstruct.destroy("divIndDevelopmentPlan");
	domConstruct.destroy("IndPdp");

	domConstruct.destroy("divIndNotes");
	domConstruct.destroy("indInterpretation");
	domConstruct.destroy("divIndNotes2");
	domConstruct.destroy("indWayForward");

	domConstruct.destroy("scrollIntoView");
}
var homeWait = setTimeout(function()
{
if(dojo.byId('viewRights').innerHTML == 'Viewer') pduDbHome();
//else goHome();
else pduDbHome();
},2000)
//goHome();

/************************************************************************* 
 Start of governmentStore/tree function
 ************************************************************************/
treeFunction = function(dataSource)
{
	if(dataSource == "pcData")
	{
		governmentStore = new Memory({
			data: json.parse(pcData),
			getChildren: function(object)
			{
				return this.query({parent: object.id});
			}
		});	

		if(pcData.length <= 2) window.location.href = "logout.php";
	}
	else
	{
		governmentStore = new Memory({
			data: json.parse(bscData),
			getChildren: function(object)
			{
				return this.query({parent: object.id});
			}
		});	

		if(bscData.length <= 2) window.location.href = "logout.php";
	}

	// To support dynamic data changes, including DnD,
	// the store must support put(child, {parent: parent}).
	// But dojo/store/Memory doesn't, so we have to implement it.
	// Since our store is relational, that just amounts to setting child.parent
	// to the parent's id.
	aspect.around(governmentStore, "put", function(originalPut){
		return function(obj, options){
			if(options && options.parent){
				obj.parent = options.parent.id;
				request.post("layout/update-tree-parent.php",{
					data: {
						parentId: options.parent.id,
						objectId: kpiGlobalId
						}
					})
			}
			return originalPut.call(governmentStore, obj, options);
		}
	});

	// give store Observable interface so Tree can track updates
	governmentStore = new Observable(governmentStore);

	// create model to interface Tree to store
	var model = new ObjectStoreModel({
		store: governmentStore,

		// query to get root node
		query: {id: "root"},

		mayHaveChildren: function(object){
			// Normally the object would have some attribute (like a type) that would tell us if
			// it might have children.   But since our data is local we'll just check if there
			// actually are children or not.
			return this.store.getChildren(object).length > 0;
		}
	});

	if(dijit.byId("tree")) 
	{
		//dijit.byId("tree").destroyRecursive();
		//domConstruct.destroy("tree");
		dijit.byId("tree").destroy(true);
		dijit.byId("treeMenu").destroy(true);
		dijit.byId("organization").destroy(true);
		dijit.byId("individual").destroy(true);
		dijit.byId("perspective").destroy(true);
		dijit.byId("objective").destroy(true);
		dijit.byId("measure").destroy(true);
		dijit.byId("linkedMeasure").destroy(true);
		dijit.byId("edit").destroy(true);
		dijit.byId("editWeight").destroy(true);
		dijit.byId("delete").destroy(true);
		dijit.byId("task").destroy(true);
		dijit.byId("pdpMenu").destroy(true);
		dijit.byId("report").destroy(true);
		dijit.byId("dashboard").destroy(true);
	}

	if(dom.byId('viewRights').innerHTML == 'Administrator')
	{
		var treeCreateTimeout = setTimeout(function()
		{
		tree = new Tree({
		model: model,
		dndController: dndSource,
		//persist: false,
		autoExpand:true,
		showRoot:false,
		//openOnClick:true,
		getIconClass: function(item, opened)
		{
			if(item.type == 'objective')
			{
				//return "dijitIconConfigure";
				//return "dijitIconApplication";
				return "dijitIconKey";
			}
			else if (item.type == 'organization')
				return (opened ? "dijitFolderOpened" : "dijitFolderClosed");
			else if (item.type == 'perspective')
				return "dijitIconTable";
			else if (item.type == 'individual')
				return "dijitIconUsers";
			else if (item.linked == 'yes')
				return "dijitIconConfigure";
			else
				return "dijitIconChart";
		},
		getDomNodeById: function(id) // new function to find DOM node
		{
			return this._itemNodesMap[id][0]; //error TypeError: this._itemNodesMap[id] is undefined shows up when the tree is collapsed.
			//Running the app with the tree in expanded mode as a result
		},
		getChildItems: function(item)
		{
			dojo.forEach(this.model.store.getChildren(item), function(node)
			{
					this.getChildItems(node);
			}, this);
		}
		}, "tree");
		tree.startup();
			var ngolaAdmin = setTimeout(function()
			{
				postTreeCreation();
				postTreeCreationTwo();
				collapseTree();
			},1000)
		},30);
	}
	else
	{
		tree = new Tree({
		model: model,
		//dndController: dndSource,
		//persist: false,
		autoExpand:true, //open the tree in expanded mode
		showRoot:false, //hides the root to have a rootless tree :-)
		//openOnClick:true, //practical when navigating the tree - this unfortunately prevented scorecard items from loading content.
		getIconClass: function(item, opened)
		{
			if(item.type == 'objective')
			{
				//return "dijitIconConfigure";
				//return "dijitIconApplication";
				return "dijitIconKey";
			}
			else if (item.type == 'organization')
				return (opened ? "dijitFolderOpened" : "dijitFolderClosed");
			else if (item.type == 'perspective')
				return "dijitIconTable";
			else if (item.type == 'individual')
				return "dijitIconUsers";
			else
				return "dijitIconChart";
		},
		getDomNodeById: function(id) // new function to find DOM node
		{
			return this._itemNodesMap[id][0]; //error TypeError: this._itemNodesMap[id] is undefined shows up when the tree is collapsed.
			//Running the app with the tree in expanded mode as a result
		},
		getChildItems: function(item)
		{
			dojo.forEach(this.model.store.getChildren(item), function(node)
			{
					this.getChildItems(node);
			}, this);
		}
		}, "tree");
		tree.startup();
			var ngolaAppUser = setTimeout(function()
			{
				postTreeCreation();
				postTreeCreationTwo();
				collapseTree();
			},1000)
	}
	//if(registry.byId("tree")) registry.byId("tree").destroyRecursive();
}
/************************************************************************* 
 End of governmentStore tree function
 ************************************************************************/
//treeFunction("bscData");

postTreeCreation = function()
{
	tree.on("click",function(object)
	{
		switch(mainMenuState)
		{
			case "Home":
			{

				break;
			}
			case "Scorecards":
			{
				//domStyle.set(dom.byId("divIntro"), "display", "none");
				dojo.byId("dynamicMenu").innerHTML = null;
				var objectId = object.id;
				var objectType = object.type;
				kpiGlobalName = object.name;
				tnAdd = object.id;
				scorecardMain(objectId, objectType);
				postComment();
				break; //end of mainMenuItem case for Scorecard
			}
			case "performanceContract":
			{
				//domStyle.set(dom.byId("divIntro"), "display", "none");
				dojo.byId("dynamicMenu").innerHTML = null;
				var objectId = object.id;
				var objectType = object.type;
				kpiGlobalName = object.name;
				tnAdd = object.id;
				scorecardMain(objectId, objectType);
				postComment();
				break; //end of mainMenuItem case for Performance Contract
			}
			case "Initiatives":
			{
				kpiGlobalId = object.id;
				kpiGlobalType = "initiative";
				tnAdd = object.id;
				var objectType = object.type;
				var objectName = object.name;
				initiativeMain(tnAdd,objectType,objectName);
				break;
			}
			case "Dashboards":
			{
				kpiGlobalId = object.id;
				kpiGlobalType = "dashboard";
				//alert(object.id);
				request.post("dashboards/get-dashboard-list.php",{
					handleAs: "json",
					data: {
						objectId: object.id
						//objectType: object.type
					}
					}).then(function(dbListData)
					{
						var dbCount = 0;
						var dbListItem;
						var dbSelectList = "<select id='dbList' onChange='dbListFunction()'>";
						while(dbCount < dbListData.length)
						{
							dbListItem = dbListData[dbCount];
							dbSelectList = dbSelectList + "<option value='"+ dbListItem.name + "'>" + dbListItem.name + "</option>"
							dbCount++;
						}
						//alert(dbList[0].name);
						dbSelectList = dbSelectList + "</select>";
						dojo.byId("dbSelectDiv").innerHTML = dbSelectList;
					});
				break;
			}
			case "Reports":
			{
				//alert("We here?");
				kpiGlobalId = object.id;
				kpiGlobalType = "report";
				tnAdd = object.id;
				domStyle.set(registry.byId("menuSeparator").domNode, 'display', 'inline-table');
				dojo.byId("dynamicMenu").innerHTML = "<i>Reports List</>";
				
				request.post("reports/get-report-list.php",
				{
					handleAs: "json",
					data:{
						linkedTo: tnAdd
						}
				}).then(function(reportList)
				{
					//reportList = [{"id":"8","Name":"ffgfg"},{"id":"9","Name":"ffgfg"},{"id":"10","Name":"dfdfdf"}];
					if(reportList.length == 0) reportListContent = "No Report(s) for Selected Item";
					else
					{
						var count = 0;
						reportListContent = "";
						while(count < reportList.length)
						{
							reportListContent = reportListContent + "<a href=\"#\" onClick=\"getReport("+reportList[count].id+");\">"+reportList[count].Name+"</a><br>";
							count++;
						}
					}
				});

				var reportTimer = setTimeout(function()
				{
					//alert("got items: " + reportListContent);
					if (dijit.byId("myTooltipDialog"))
					{
						dijit.byId("myTooltipDialog").destroy(true);
						//alert("nimeharibiwa");
					}

					var myTooltipDialog = new TooltipDialog({
						id: 'myTooltipDialog',
						//style: "width: 300px;",
						content: reportListContent,
						onMouseLeave: function(){
							popup.close(myTooltipDialog);
						}
					});

					on(dom.byId('dynamicMenu'), 'mouseover', function(){
						popup.open({
							popup: myTooltipDialog,
							around: dom.byId('dynamicMenu')
						});
					});
				},1000);
				break;
			}
		}
	});
}


 collapseTree = function()
 {
	 tree.collapseAll();
 }
 expandTree = function()
 {
	 tree.expandAll();
	 //alert(dom.byId("tree").innerHTML);
 }
	//************************************************************
	//handler for clicks on task context menu items
	//************************************************************
	function onTreeItemCopy()
	{
		// retrieve the id representing the tree item clicked
		var tnCopy = dijit.byNode(this.getParent().currentTarget);
	}
	
	// Function to get kpiOwner values with proper fallback handling
	getKpiOwnerValues = function() 
	{
		var selected = document.querySelector("#kpiOwner").getSelectedOptions();
		var tags = JSON.stringify(selected);

		return tags;
	}

	//adding measure dialog functions
	updaterChecked = function()
	{
		if(dijit.byId('updaterCheckbox').checked == true)
		{
			dijit.byId("kpiUpdater").set('value',dijit.byId("kpiOwner").value);
			dijit.byId("kpiUpdater").set('disabled', true);
			updaterCheckbox = "True";
		}
		else
		{
			dijit.byId("kpiUpdater").set('disabled', false);
			updaterCheckbox = "False";
		}
	}
	goalClicked = function ()
	{
		dijit.byId("scoringDialog").onCancel();
		dom.byId("selectedScoringType").innerHTML = "Goal Only";
		domStyle.set(registry.byId("thresholdDialog").domNode, 'display', 'block');
		thresholdType = "goalOnly";
		domStyle.set(dom.byId("trBest"), 'display', 'none');
		domStyle.set(dom.byId("trStretch"), 'display', 'none');
		//domStyle.set(dom.byId("trTarget").domNode, 'display', 'table-row');
		domStyle.set(dom.byId("trBaseline"), 'display', 'none');
		selectedGauge = 'goalOnly';
	}
	threeColorClicked = function ()
	{
		dijit.byId("scoringDialog").onCancel();
		dom.byId("selectedScoringType").innerHTML = "Three Color";
		domStyle.set(registry.byId("thresholdDialog").domNode, 'display', 'block');
		thresholdType = "threeColor";
		domStyle.set(dom.byId("trBest"), 'display', 'none');
		domStyle.set(dom.byId("trStretch"), 'display', 'none');
		domStyle.set(dom.byId("trBaseline"), 'display', 'table-row');
		selectedGauge = 'threeColor';
	}
	fourColorClicked = function ()
	{
		dijit.byId("scoringDialog").onCancel();
		dom.byId("selectedScoringType").innerHTML = "Four Color";
		domStyle.set(registry.byId("thresholdDialog").domNode, 'display', 'block');
		thresholdType = "fourColor";
		domStyle.set(dom.byId("trBest"), 'display', 'none');
		domStyle.set(dom.byId("trStretch"), 'display', 'table-row');
		domStyle.set(dom.byId("trBaseline"), 'display', 'table-row');
		selectedGauge = 'fourColor';
	}
	fiveColorClicked = function ()
	{
		dijit.byId("scoringDialog").onCancel();
		dom.byId("selectedScoringType").innerHTML = "Five Color";
		domStyle.set(registry.byId("thresholdDialog").domNode, 'display', 'block');
		thresholdType = "fiveColor";
		domStyle.set(dom.byId("trBest"), 'display', 'table-row');
		domStyle.set(dom.byId("trStretch"), 'display', 'table-row');
		domStyle.set(dom.byId("trBaseline"), 'display', 'table-row');
		selectedGauge = 'fiveColor';
	}
	showIndividualAddDialog = function()
	{
		tnEditType = "individual";
		registry.byId("newIndividualDialog").show();
		if(edit == "editMe")
		{
			//to edit
		}
	}
	hideIndividualAddDialog = function()
	{
		//var treeId = tnEditHolder.item.id;
		registry.byId("newIndividualDialog").onCancel();
		indName = dojo.byId("indName").value;
		var indPhoto = dijit.byId("uploader").getFileList();
		//var photoName = indPhoto[0].name;
		var hidden = dom.byId('hiddenIndId').value;
		//    alert("1 " + indName + " 2 " + tnAdd + " 3 " + " 4 " + hidden);
		request.post("layout/save-tree.php",
		{
			//handleAs: "json",
			data: {
					tree_name: indName,
					tree_parent:tnAdd,
					indId: dom.byId('hiddenIndId').value,
					//indPhoto: indPhoto[0].name
					indPhoto: "Error"
				}
		}).then(function(treeId)
		{
			childItem =
				{
					name: indName,
					id: treeId,
					parent: tnAdd,
					type: tnEditType,
					overwrite: true
				};
				governmentStore.put(childItem);
				tree.startup();
		});
	}
	showMeasureAddDialog = function()
	{
		registry.byId("newMeasureDialog").show();
		domStyle.set(registry.byId("thresholdDialog").domNode, 'display', 'none');

		// Ensure collection frequency dropdown is created
		ensureCollectionFrequencyDropdown();

		if(edit == "editMe")
		{
			domStyle.set(dom.byId("addWeight"), "display", "table-row");
			if(tnEditType == "perspective")
			{
				dijit.byId("newMeasureDialog").set("title", "Edit Perspective");
				dom.byId("tdMeasureName").innerHTML = "Perspective Name";
				//dojo.byId("kpiName").value = object.id;
				domStyle.set(dom.byId("addDescription"), "display", "none");
				domStyle.set(dom.byId("addOutcome"), "display", "none");
				domStyle.set(dom.byId("addMission"), "display", "none");
				domStyle.set(dom.byId("addVision"), "display", "none");
				domStyle.set(dom.byId("addValues"), "display", "none");
				domStyle.set(dom.byId("addCollectionFrequency"), "display", "none");
				domStyle.set(dom.byId("addMeasureType"), "display", "none");
				domStyle.set(dom.byId("addDataType"), "display", "none");
				domStyle.set(dom.byId("addAggregationType"), "display", "none");
				domStyle.set(dom.byId("addMeasureOwner"), "display", "none");
				//domStyle.set(dom.byId("addIsUpdater"), "display", "none");
				//domStyle.set(dom.byId("addUpdater"), "display", "none");
				domStyle.set(dom.byId("addScoringType"), "display", "none");
				domStyle.set(dom.byId("addCascade"), "display", "none");
				
			}
			else if(tnEditType == "organization")
			{
				dijit.byId("newMeasureDialog").set("title", "Edit Organization");
				dom.byId("tdMeasureName").innerHTML = "Organization Name";
				domStyle.set(dom.byId("addMission"), "display", "table-row");
				domStyle.set(dom.byId("addVision"), "display", "table-row");
				domStyle.set(dom.byId("addValues"), "display", "table-row");
				domStyle.set(dom.byId("addDescription"), "display", "none");
				domStyle.set(dom.byId("addOutcome"), "display", "none");
				domStyle.set(dom.byId("addCollectionFrequency"), "display", "none");
				domStyle.set(dom.byId("addMeasureType"), "display", "none");
				domStyle.set(dom.byId("addDataType"), "display", "none");
				domStyle.set(dom.byId("addAggregationType"), "display", "none");
				domStyle.set(dom.byId("addMeasureOwner"), "display", "none");
				//domStyle.set(dom.byId("addIsUpdater"), "display", "none");
				//domStyle.set(dom.byId("addUpdater"), "display", "none");
				domStyle.set(dom.byId("addScoringType"), "display", "none");
				domStyle.set(dom.byId("addCascade"), "display", "none");
			}
			else if(tnEditType == "objective")
			{
				dijit.byId("newMeasureDialog").set("title", "Edit Objective");
				dom.byId("tdMeasureName").innerHTML = "Objective Name";
				dom.byId("addMeasureOwnerTitle").innerHTML = "Objective Owner/Team";
				//dom.byId("addUpdaterTitle").innerHTML = "Add User to Team";
				//dom.byId("teamNames").innerHTML = 'Team: ';
				domStyle.set(dom.byId("addDescription"), "display", "table-row");
				domStyle.set(dom.byId("addMeasureOwner"), "display", "table-row");
				domStyle.set(dom.byId("addOutcome"), "display", "table-row");
				//domStyle.set(dom.byId("addUpdater"), "display", "table-row");
				domStyle.set(dom.byId("addMission"), "display", "none");
				domStyle.set(dom.byId("addVision"), "display", "none");
				domStyle.set(dom.byId("addValues"), "display", "none");
				domStyle.set(dom.byId("addCollectionFrequency"), "display", "none");
				domStyle.set(dom.byId("addMeasureType"), "display", "none");
				domStyle.set(dom.byId("addDataType"), "display", "none");
				domStyle.set(dom.byId("addAggregationType"), "display", "none");
				//domStyle.set(dom.byId("addIsUpdater"), "display", "none");
				domStyle.set(dom.byId("addScoringType"), "display", "none");
				domStyle.set(dom.byId("addCascade"), "display", "table-row");
				dojo.byId("divThresholds").innerHTML = '';
				dojo.byId("selectedScoringType").innerHTML = '';
				
			}
			else if(tnEditType == "measure")
			{
				dijit.byId("newMeasureDialog").set("title", "Edit Measure");
				dom.byId("tdMeasureName").innerHTML = "Measure Name";
				dom.byId("addMeasureOwnerTitle").innerHTML = "Measure Owner(s)";
				domStyle.set(dom.byId("addOutcome"), "display", "none");
				domStyle.set(dom.byId("addMission"), "display", "none");
				domStyle.set(dom.byId("addVision"), "display", "none");
				domStyle.set(dom.byId("addValues"), "display", "none");
				domStyle.set(dom.byId("addDescription"), "display", "table-row");
				domStyle.set(dom.byId("addCollectionFrequency"), "display", "table-row");
				domStyle.set(dom.byId("addMeasureType"), "display", "table-row");
				domStyle.set(dom.byId("addDataType"), "display", "table-row");
				domStyle.set(dom.byId("addAggregationType"), "display", "table-row");
				domStyle.set(dom.byId("addMeasureOwner"), "display", "table-row");
				//domStyle.set(dom.byId("addIsUpdater"), "display", "table-row");
				//domStyle.set(dom.byId("addUpdater"), "display", "table-row");
				domStyle.set(dom.byId("addScoringType"), "display", "table-row");
				domStyle.set(dom.byId("addCascade"), "display", "none");
			}
			else if(tnEditType == "individual")
			{
				dijit.byId("newMeasureDialog").set("title", "Add Individual");
				dom.byId("addMeasureOwnerTitle").innerHTML = "Individual Name";
				domStyle.set(dom.byId("tdMeasureName"), "display", "none");
				domStyle.set(dom.byId("addOutcome"), "display", "none");
				domStyle.set(dom.byId("addMission"), "display", "none");
				domStyle.set(dom.byId("addVision"), "display", "none");
				domStyle.set(dom.byId("addValues"), "display", "none");
				domStyle.set(dom.byId("addDescription"), "display", "none");
				domStyle.set(dom.byId("addCollectionFrequency"), "display", "none");
				domStyle.set(dom.byId("addMeasureType"), "display", "none");
				domStyle.set(dom.byId("addDataType"), "display", "none");
				domStyle.set(dom.byId("addAggregationType"), "display", "none");
				domStyle.set(dom.byId("addMeasureOwner"), "display", "table-row");
				//domStyle.set(dom.byId("addIsUpdater"), "display", "none");
				//domStyle.set(dom.byId("addUpdater"), "display", "none");
				domStyle.set(dom.byId("addScoringType"), "display", "none");
				domStyle.set(dom.byId("addCascade"), "display", "none");
			}

			request.post("layout/edit-tree.php",{
			handleAs: "json",
			data: {
				tree_id: tnEdit,
				tree_type: tnEditType
				//tree_edit: "editMe"
			}
			}).then(function(data) {
				//console.log(JSON.stringify(data));
				dijit.byId("kpiName").set('value', data["name"]);
				dijit.byId("kpiDescription").set('value', data["description"]);
				dijit.byId("kpiOutcome").set('value', data["outcome"]);
				dijit.byId("kpiMission").set('value', data["mission"]);
				dijit.byId("kpiVision").set('value', data["vision"]);
				dijit.byId("kpiValues").set('value', data["valuez"]);

				// Fix: Add null check for collectionFrequency widget
				var collectionFrequencyWidget = dijit.byId("collectionFrequency");
				if (collectionFrequencyWidget && collectionFrequencyWidget.set) {
					collectionFrequencyWidget.set('value', data["calendarType"]);
				} else {
					console.warn("collectionFrequency widget not found when setting value from data");
				}
				dijit.byId("measureType").set('value', data["measureType"]);
				dijit.byId("dataType").set('value', data["dataType"]);
				dijit.byId("kpiCascade").set('value', data["cascadedFrom"]);
				
				if(data["aggregationType"] == "Sum") dijit.byId("aggregationTypeSum").set('checked', true);
				else if(data["aggregationType"] == "Average") dijit.byId("aggregationTypeAvg").set('checked', true);
				else if(data["aggregationType"] == "Last Value") dijit.byId("aggregationTypeFinal").set('checked', true);
				
				kpiOwnerId = data["owner"];
				//kpiUpdaterId = data["updater"];

				request.post("scorecards/format-shared-ids.php",{
					handleAs: "json",
					data: {
						shared_id: data["tags"],
						non_measures: data["owner"]
					}
					}).then(function(value) {
						//var ngojea = setTimeout(function(){
						//value =	JSON.stringify(value);
						value = value.selected;

						// Safely set VirtualSelect value
						try {
							var kpiOwnerElement = document.querySelector('#kpiOwner');
							if (kpiOwnerElement && typeof kpiOwnerElement.setValue === 'function') {
								kpiOwnerElement.setValue(value);
								//console.log("VirtualSelect value set successfully");
							} else {
								// Fallback: try to set value on a regular select element
								var selectElement = kpiOwnerElement ? kpiOwnerElement.querySelector('select') : null;
								if (selectElement) {
									// Clear existing selections
									for (var i = 0; i < selectElement.options.length; i++) {
										selectElement.options[i].selected = false;
									}
									// Set new selections
									if (Array.isArray(value)) {
										for (var j = 0; j < value.length; j++) {
											for (var k = 0; k < selectElement.options.length; k++) {
												if (selectElement.options[k].value === value[j]) {
													selectElement.options[k].selected = true;
													break;
												}
											}
										}
									}
									console.log("Fallback select value set successfully");
								} else {
									console.log("Neither VirtualSelect nor fallback select available");
								}
							}
						} catch (error) {
							console.error("Error setting owner value:", error);
						}
						//console.log(value);
						//},300);
					});
				
				//var localOwner = data["owner"];
				//localOwner = ownerUpdaterStore.query({id:localOwner});
				//localOwner = localOwner[0].User;
				//var localUpdater = data["updater"];
				//localUpdater = ownerUpdaterStore.query({id:localUpdater});
				//localUpdater = localUpdater[0].User;
				
				//dijit.byId("kpiOwner").set('value', localOwner); /*Update this to work with virtualSelect*/
				//dijit.byId("kpiUpdater").set('value', localUpdater);
				
				dijit.byId("weight").set('value', data["weight"]*100);
				//dom.byId("weight").innerHTML = data["weight"]*100;
				
				if(data["archive"] == "No") dijit.byId("archiveNo").set('checked', true);
				else if(data["archive"] == "Yes") dijit.byId("archiveYes").set('checked', true);
				/*
				if(tnEditType == "objective")
				{
					if(data["updater"] == null) dom.byId("teamNames").innerHTML = 'Team: No Team Members';
					else dom.byId("teamNames").innerHTML = 'Team: ' + data["updater"];
				}
				else  dom.byId("teamNames").innerHTML = '';*/
				
				dijit.byId("blue").set('value', data["blue"]);
				dijit.byId("darkGreen").set('value', data["darkGreen"]);
				dijit.byId("green").set('value', data["green"]);
				dijit.byId("red").set('value', data["red"]);
				
				domStyle.set(registry.byId("thresholdDialog").domNode, 'display', 'block');
				switch(data["gaugeType"])
				{
					case "goalOnly":
					{
						dom.byId("selectedScoringType").innerHTML = "Goal Only";
						thresholdType = "goalOnly";
						domStyle.set(dom.byId("trBest"), 'display', 'none');
						domStyle.set(dom.byId("trStretch"), 'display', 'none');
						domStyle.set(dom.byId("trBaseline"), 'display', 'none');
						dojo.byId("divThresholds").innerHTML = "Target: " + data["green"];
						selectedGauge = 'goalOnly';
						break;	
					}
					case "threeColor":
					{
						dom.byId("selectedScoringType").innerHTML = "Three Color";
						thresholdType = "threeColor";
						domStyle.set(dom.byId("trBest"), 'display', 'none');
						domStyle.set(dom.byId("trStretch"), 'display', 'none');
						domStyle.set(dom.byId("trBaseline"), 'display', 'table-row');
						selectedGauge = 'threeColor';
						dojo.byId("divThresholds").innerHTML =
						"Target: " + data["green"] +
						", Baseline: " + data["red"];
						break;	
					}
					case "fourColor":
					{
						dom.byId("selectedScoringType").innerHTML = "Four Color";
						thresholdType = "fourColor";
						domStyle.set(dom.byId("trBest"), 'display', 'none');
						domStyle.set(dom.byId("trStretch"), 'display', 'table-row');
						domStyle.set(dom.byId("trBaseline"), 'display', 'table-row');
						selectedGauge = 'fourColor';
						dojo.byId("divThresholds").innerHTML = 
						"Stretch Target: " + data["darkGreen"] +
						", Target: " + data["green"] +
						", Baseline: " + data["red"];
						break;	
					}
					case "fiveColor":
					{
						dom.byId("selectedScoringType").innerHTML = "Five Color";
						thresholdType = "fiveColor";
						domStyle.set(dom.byId("trBest"), 'display', 'table-row');
						domStyle.set(dom.byId("trStretch"), 'display', 'table-row');
						domStyle.set(dom.byId("trBaseline"), 'display', 'table-row');
						selectedGauge = 'fiveColor';
						dojo.byId("divThresholds").innerHTML =
						"Best: " + data["blue"] +
						", Stretch Target: " + data["darkGreen"] +
						", Target: " + data["green"] +
						", Baseline: " + data["red"];
						break;	
					}
					default:
					{
						dom.byId("selectedScoringType").innerHTML = "";
						thresholdType = "fiveColor";
						domStyle.set(dom.byId("trBest"), 'display', 'none');
						domStyle.set(dom.byId("trStretch"), 'display', 'none');
						domStyle.set(dom.byId("trBaseline"), 'display', 'none');
						selectedGauge = '';
						dojo.byId("divThresholds").innerHTML = "";
						break;	
					}
				}				
			});
		}
		else
		{
			domStyle.set(dom.byId("addWeight"), "display", "none");
			if(tnMenuItem == "New Perspective")
			{
				tnEditType = "perspective";
				dijit.byId("newMeasureDialog").set("title", "Add Perspective");
				dom.byId("tdMeasureName").innerHTML = "Perspective Name";
				dojo.byId("kpiName").value = '';
				domStyle.set(dom.byId("addDescription"), "display", "none");
				domStyle.set(dom.byId("addOutcome"), "display", "none");
				domStyle.set(dom.byId("addMission"), "display", "none");
				domStyle.set(dom.byId("addVision"), "display", "none");
				domStyle.set(dom.byId("addValues"), "display", "none");
				domStyle.set(dom.byId("addCollectionFrequency"), "display", "none");
				domStyle.set(dom.byId("addMeasureType"), "display", "none");
				domStyle.set(dom.byId("addDataType"), "display", "none");
				domStyle.set(dom.byId("addAggregationType"), "display", "none");
				domStyle.set(dom.byId("addMeasureOwner"), "display", "none");
				//domStyle.set(dom.byId("addIsUpdater"), "display", "none");
				//domStyle.set(dom.byId("addUpdater"), "display", "none");
				//dom.byId("teamNames").innerHTML = '';
				domStyle.set(dom.byId("addScoringType"), "display", "none");
				dojo.byId("divThresholds").innerHTML = '';
				dojo.byId("selectedScoringType").innerHTML = '';
				domStyle.set(dom.byId("addCascade"), "display", "none");
		}
			else if(tnMenuItem == "New Organization")
			{
				tnEditType = "organization";
				dijit.byId("newMeasureDialog").set("title", "Add Organization");
				dom.byId("tdMeasureName").innerHTML = "Organization Name";
				dijit.byId("kpiDescription").set("value", '');
				dijit.byId("kpiName").set("value", '');
				dijit.byId("kpiOutcome").set("value", '');
				//dijit.byId("kpiOwner").set("value", ''); /*Update This*/
				//dijit.byId("kpiUpdater").set("value", '');
				domStyle.set(dom.byId("addMission"), "display", "table-row");
				domStyle.set(dom.byId("addVision"), "display", "table-row");
				domStyle.set(dom.byId("addValues"), "display", "table-row");
				domStyle.set(dom.byId("addDescription"), "display", "none");
				domStyle.set(dom.byId("addOutcome"), "display", "none");
				domStyle.set(dom.byId("addCollectionFrequency"), "display", "none");
				domStyle.set(dom.byId("addMeasureType"), "display", "none");
				domStyle.set(dom.byId("addDataType"), "display", "none");
				domStyle.set(dom.byId("addAggregationType"), "display", "none");
				domStyle.set(dom.byId("addMeasureOwner"), "display", "none");
				//domStyle.set(dom.byId("addIsUpdater"), "display", "none");
				//dom.byId("teamNames").innerHTML = '';
				//domStyle.set(dom.byId("addUpdater"), "display", "none");
				domStyle.set(dom.byId("addScoringType"), "display", "none");
				dojo.byId("divThresholds").innerHTML = '';
				dojo.byId("selectedScoringType").innerHTML = '';
				domStyle.set(dom.byId("addCascade"), "display", "none");
			}
			else if(tnMenuItem == "New Objective")
			{
				tnEditType = "objective";
				//alert(tnEditType);
				dijit.byId("newMeasureDialog").set("title", "Add Objective");
				dom.byId("tdMeasureName").innerHTML = "Objective Name";
				dom.byId("addMeasureOwnerTitle").innerHTML = "Objective Owner";
				//dom.byId("addUpdaterTitle").innerHTML = "Add User to Team";
				domStyle.set(dom.byId("addDescription"), "display", "table-row");
				domStyle.set(dom.byId("addOutcome"), "display", "table-row");
				domStyle.set(dom.byId("addMeasureOwner"), "display", "table-row");
				//domStyle.set(dom.byId("addUpdater"), "display", "table-row");
				//dom.byId("teamNames").innerHTML = 'Team: ';
				domStyle.set(dom.byId("addMission"), "display", "none");
				domStyle.set(dom.byId("addVision"), "display", "none");
				domStyle.set(dom.byId("addValues"), "display", "none");
				domStyle.set(dom.byId("addCollectionFrequency"), "display", "none");
				domStyle.set(dom.byId("addMeasureType"), "display", "none");
				domStyle.set(dom.byId("addDataType"), "display", "none");
				domStyle.set(dom.byId("addAggregationType"), "display", "none");
				//domStyle.set(dom.byId("addIsUpdater"), "display", "none");
				domStyle.set(dom.byId("addScoringType"), "display", "none");
				dijit.byId("kpiDescription").set("value", '');
				dijit.byId("kpiName").set("value", '');
				dijit.byId("kpiOutcome").set("value", '');
				//dijit.byId("kpiOwner").set("value", '');/*Update This*/
				//dijit.byId("kpiUpdater").set("value", '');
				dojo.byId("divThresholds").innerHTML = '';
				dojo.byId("selectedScoringType").innerHTML = '';
				
				//dijit.byId("kpiUpdater").set('disabled', false);
				//dijit.byId('updaterCheckbox').set('checked', false);
				//updaterCheckbox = "False";
				dijit.byId("blue").set("value", '');
				dijit.byId("darkGreen").set("value", '');
				dijit.byId("green").set("value", '');
				dijit.byId("red").set("value", '');
				dijit.byId("weight").set('value', '');
				domStyle.set(registry.byId("thresholdDialog").domNode, "display", "none");
				dijit.byId("kpiCascade").set("value", '');
				
				domStyle.set(dom.byId("addCascade"), "display", "table-row");
				//delete objective team members related to 'this' parent
				request.post("layout/delete-team.php",{
				handleAs: "json",
				data: {
					treeId: tnAdd
					//tree_type: tnEditType
					//tree_edit: "editMe"
					}
				})
			}
			else if(tnMenuItem == "New Measure")
			{
				tnEditType = "measure";
				dijit.byId("newMeasureDialog").set("title", "Add Measure");
				dom.byId("tdMeasureName").innerHTML = "Measure Name";
				//dom.byId("addUpdaterTitle").innerHTML = "Measure Updater: ";
				dom.byId("addMeasureOwnerTitle").innerHTML = "Measure Owner(s)";
				domStyle.set(dom.byId("addOutcome"), "display", "none");
				domStyle.set(dom.byId("addMission"), "display", "none");
				domStyle.set(dom.byId("addVision"), "display", "none");
				domStyle.set(dom.byId("addValues"), "display", "none");
				domStyle.set(dom.byId("addDescription"), "display", "table-row");
				domStyle.set(dom.byId("addCollectionFrequency"), "display", "table-row");
				domStyle.set(dom.byId("addMeasureType"), "display", "table-row");
				domStyle.set(dom.byId("addDataType"), "display", "table-row");
				domStyle.set(dom.byId("addAggregationType"), "display", "table-row");
				domStyle.set(dom.byId("addMeasureOwner"), "display", "table-row");
				//domStyle.set(dom.byId("addIsUpdater"), "display", "table-row");
				//dom.byId("teamNames").innerHTML = '';
				//domStyle.set(dom.byId("addUpdater"), "display", "table-row");
				domStyle.set(dom.byId("addScoringType"), "display", "table-row");
				domStyle.set(dom.byId("addCascade"), "display", "none");
				dijit.byId("kpiDescription").set("value", '');
				dijit.byId("kpiName").set("value", '');
				//dijit.byId("kpiOwner").set("value", '');
				//dijit.byId("kpiUpdater").set("value", '');
				dijit.byId("dataType").set("value", '');
				dojo.byId("divThresholds").innerHTML = '';
				dojo.byId("selectedScoringType").innerHTML = '';
				//dijit.byId("kpiUpdater").set('disabled', false);
				//dijit.byId('updaterCheckbox').set('checked', false);
				//updaterCheckbox = "False";
				dijit.byId("blue").set("value", '');
				dijit.byId("darkGreen").set("value", '');
				dijit.byId("green").set("value", '');
				dijit.byId("red").set("value", '');
				dijit.byId("weight").set('value', '');
				domStyle.set(registry.byId("thresholdDialog").domNode, "display", "none");
				dijit.byId("aggregationTypeFinal").set('checked', true);
				dijit.byId("kpiCascade").set("value", '');
			}
			
		}
	}
	hideMeasureAddDialog = function()
	{
		//console.log(dijit.byId("collectionFrequency")); // Add this line

		// Fix: Add null check to prevent "Cannot read properties of undefined" error
		var collectionFrequencyWidget = dijit.byId("collectionFrequency");
		if (collectionFrequencyWidget) {
			// Try different methods to get the value
			if (collectionFrequencyWidget.get && typeof collectionFrequencyWidget.get === 'function') {
				collectionFrequency = collectionFrequencyWidget.get('value');
			} else if (collectionFrequencyWidget.value !== undefined) {
				collectionFrequency = collectionFrequencyWidget.value;
			} else {
				collectionFrequency = "Monthly"; // Default value
				console.warn("collectionFrequency widget found but value is undefined, using default: Monthly");
			}
		} else {
			// Fallback: try to get value from DOM element or set default
			var collectionFrequencyElement = dojo.byId("collectionFrequency");
			if (collectionFrequencyElement && collectionFrequencyElement.value) {
				collectionFrequency = collectionFrequencyElement.value;
			} else {
				collectionFrequency = "Monthly"; // Default value
				console.warn("collectionFrequency widget not found, using default value: Monthly");
			}
		}
		if(dojo.byId("aggregationTypeSum").checked == true) aggregationType  = "Sum";
		else if(dojo.byId("aggregationTypeAvg").checked == true) aggregationType  = "Average";
		else if(dojo.byId("aggregationTypeFinal").checked == true) aggregationType  = "Last Value";
		kpiDescription = dojo.byId("kpiDescription").value;
		kpiOutcome = dojo.byId("kpiOutcome").value;
		kpiMission = dojo.byId("kpiMission").value;
		kpiVision = dojo.byId("kpiVision").value;
		kpiValues = dojo.byId("kpiValues").value;
		//thresholdType = dojo.byId("thresholdType").value;
		//if (dojo.byId("updaterCheckbox").value == true) kpiUpdater = dojo.byId("kpiOwner").value
		//if (updaterCheckbox == "True") kpiUpdater = dojo.byId("kpiOwner").value
		//else kpiUpdater = dojo.byId("kpiUpdater").value;
		//if (updaterCheckbox == "True") kpiUpdater = kpiOwnerId;
		//else kpiUpdater = kpiUpdaterId;
		//kpiOwner = kpiOwnerId;//Was saving proper name of owner; how now? Changing this to save ID instead. LTK 04.01.2018
		//kpiOwner = document.querySelector('#kpiOwner').value;

		var tagsOwner = getKpiOwnerValues();
		kpiOwner = JSON.parse(tagsOwner);
		kpiOwner = kpiOwner[0].value;

		kpiName = dojo.byId("kpiName").value;
		measureType = dojo.byId("measureType").value;
		dataType = dojo.byId("dataType").value;
		darkGreen = dojo.byId("darkGreen").value;
		blue = dojo.byId("blue").value;
		green = dojo.byId("green").value;
		red = dojo.byId("red").value;
		darkGreenType = dojo.byId("darkGreenType").value;
		blueType = dojo.byId("blueType").value;
		greenType = dojo.byId("greenType").value;
		redType = dojo.byId("redType").value;
		formula = dojo.byId("formula").value;
		weight = dijit.byId("weight").get('value');
		kpiCascade = kpiCascade;
		if(dojo.byId("archiveNo").checked == true) archive  = "No";
		else if(dojo.byId("archiveYes").checked == true) archive  = "Yes";
		
		if(edit == undefined)
		{
			var treeId = null;
			//editMe == "";
		}
		else {
			if(tnEditHolder == undefined)
			{}
			else
			var treeId = tnEditHolder.item.id;

		//tnEditHolder = dijit.byNode(this.getParent().currentTarget);
		//var treeId = tnEditHolder.item.id;
		}
		registry.byId("newMeasureDialog").hide(); //putting this here to see whether the owner tags will pick up properly
		console.log("Edit type is "+ edit + "; mainMenuState = " + mainMenuState);
		//tnContent = object.name;
		request.post("layout/save-tree.php",{
			// The URL of the request
			data: {
				tree_edit: edit,
				tree_name: kpiName,
				tree_parent: tnAdd,
				tree_id: treeId,
				tree_type: tnEditType,
				collectionFrequency: collectionFrequency,
				//updaterCheckbox: updaterCheckbox,
				kpiDescription: kpiDescription,
				kpiOutcome: kpiOutcome,
				kpiMission: kpiMission,
				kpiVision: kpiVision,
				kpiValues: kpiValues,
				thresholdType: thresholdType,
				kpiOwner: kpiOwner,
				kpiOwnerTags: tagsOwner,
				measureType: measureType,
				dataType: dataType,
				darkGreen: darkGreen,
				blue: blue,
				green: green,
				red: red,
				darkGreenType: darkGreenType,
				greenType: greenType,
				blueType: blueType,
				redType: redType,
				aggregationType: aggregationType,
				weight: weight,
				archive: archive,
				kpiCascade: kpiCascade,
				kraListId: kraListId,
				mainMenuState: mainMenuState //adding this to help distinguish trees
			}
			}).then(function(treeId) {
				//alert("After save id is: "+treeId+" and name is: "+ kpiName);
				if(edit == "editMe")
				{
					//rename tree item
					tnEditHolder.item.name = kpiName;
					governmentStore.put(tnEditHolder.item);
					tree.startup();
				}
				else
				{//alert("Saving...");
					childItem =
					{
						name: kpiName,
						id: treeId,
						parent: tnAdd,
						type: tnEditType,
						overwrite: true
					};
					governmentStore.put(childItem);
					//tree.startup();
					//tree.startup();//tree not refreshing - added 2nd startup and it seems to work!
					//alert("item added: Name:"+ kpiName + ", Id: "+ treeId + ", Parent: "+tnAdd + ", Edit Type: "+tnEditType);
				}
			});
	}
	hideThresholdsAddDialog = function()
	{
		//registry.byId("newMeasureDialog").show();
		dijit.byId("thresholdTooltip").onCancel();
		saveRed = dojo.byId("red").value;
		saveTarget = dojo.byId("green").value;
		saveBlue = dojo.byId("blue").value;
		saveStretchTarget = dojo.byId("darkGreen").value;
		switch(selectedGauge)
		{
			case "goalOnly":
			{
				dojo.byId("divThresholds").innerHTML = "Target: " + saveTarget;
				break;
			}
			case "threeColor":
			{
				dojo.byId("divThresholds").innerHTML =
				"Target: " + saveTarget +
				", Baseline: " + saveRed;
				break;
			}
			case "fourColor":
			{
				dojo.byId("divThresholds").innerHTML =
				"Stretch Target: " + saveStretchTarget +
				", Target: " + saveTarget +
				", Baseline: " + saveRed;
				break;
			}
			case "fiveColor":
			{
				dojo.byId("divThresholds").innerHTML =
				"Best: " + saveBlue +
				", Stretch Target: " + saveStretchTarget +
				", Target: " + saveTarget +
				", Baseline: " + saveRed;
				break;
			}
		}
		//alert(dojo.byId("red").value + dojo.byId("goal").value);
		//alert("Not so good");
   }
   calculatedGoal = function ()
   {
    	if(dijit.byId("darkGreenType").get('value')=="Calculated")
		{
    		dijit.byId("darkGreen").set('disabled', true);
			registry.byId("calculatedMeasureDialog").show();
			setFormula = "darkGreen";
  		}
		else dijit.byId("darkGreen").set('disabled', false);

		if(dijit.byId("greenType").get('value')=="Calculated")
		{
    		dijit.byId("green").set('disabled', true);
			registry.byId("calculatedMeasureDialog").show();
			setFormula = "green";
  		}
		else dijit.byId("green").set('disabled', false);

		if(dijit.byId("blueType").get('value')=="Calculated")
		{
    		dijit.byId("blue").set('disabled', true);
			registry.byId("calculatedMeasureDialog").show();
			setFormula = "blue";
  		}
		else dijit.byId("blue").set('disabled', false);

		if(dijit.byId("redType").get('value')=="Calculated")
		{
    		dijit.byId("red").set('disabled', true);
			registry.byId("calculatedMeasureDialog").show();
			setFormula = "red";
  		}
		else dijit.byId("red").set('disabled', false);
   }
   calculatedMeasureResults = function()
   {//do whatever here :-)
		//alert(dom.byId("formula").value + setFormula);
		switch(setFormula)
		{
			case "darkGreen":
			{
				dijit.byId("darkGreen").set('value', dom.byId("formula").value);
				break;
			}
			case "green":
			{
				dijit.byId("green").set('value', dom.byId("formula").value);
				break;
			}
			case "blue":
			{
				dijit.byId("blue").set('value', dom.byId("formula").value);
				break;
			}
			case "red":
			{
				dijit.byId("red").set('value', dom.byId("formula").value);
				break;
			}
		}
	}
	function onIndividualAdd()
	{
		tnAdd = dijit.byNode(this.getParent().currentTarget);
		tnEditType = tnAdd.item.type;
		tnAdd = tnAdd.item.id;
		//dijit.byId("new").get("label");
		tnMenuItem = this.get("label");
		showIndividualAddDialog();
	}
	function onPdpAdd()
	{
		tnAdd = dijit.byNode(this.getParent().currentTarget);
		tnEditType = tnAdd.item.type;
		tnAdd = tnAdd.item.id;
		tnMenuItem = this.get("label");

		//tnEditType = "individual";
		registry.byId("pdpDialog").show();
		if(edit == "editMe")
		{
			//to edit
		}
	}
	function onTreeItemAdd()
	{
		// retrieve the id representing the tree item clicked
		tnAdd = dijit.byNode(this.getParent().currentTarget);
		tnEditType = tnAdd.item.type;
		tnAdd = tnAdd.item.id;
		tnMenuItem = this.get("label");
		edit = "dontEditMe";
		showMeasureAddDialog();
	}
	function addLinkedMeasure()
	{
		// retrieve the id representing the tree item clicked
		tnAdd = dijit.byNode(this.getParent().currentTarget);
		tnEditType = tnAdd.item.type;
		tnAdd = tnAdd.item.id;
		tnMenuItem = this.get("label");
		registry.byId("linkedMeasureDialog").show();
	}
	cancelLinkedMeasureDialog = function()
	{
		registry.byId("linkedMeasureDialog").hide();
	}
	hideLinkedMeasureDialog = function()
	{
		registry.byId("linkedMeasureDialog").hide();
		request.post("layout/save-linked-tree.php",{
			data: {
				tree_name: kpiName,
				tree_parent: tnAdd,
				kpiLinkedId: kpiListId
			}
			}).then(function(treeId) 
			{
				childItem =
				{
					name: kpiName,
					id: treeId,
					parent: tnAdd,
					type: "measure",
					overwrite: true
				};
				governmentStore.put(childItem);
				//tree.startup();
				//tree.startup();//tree not refreshing - added 2nd startup and it seems to work!
				//alert("item added: Name:"+ kpiName + ", Id: "+ treeId + ", Parent: "+tnAdd + ", Edit Type: "+tnEditType);
			});
	}
	editWeights = function()
	{
		tnWeight = dijit.byNode(this.getParent().currentTarget);
		console.log("Changing weights of " + tnWeight.item.id);
		registry.byId("weightsDialog").show();
		request.post("scorecards/edit-weights.php",{
			//handleAs: "json",
			data:
			{
				id: tnWeight.item.id
				//parent: tnAdd
			}
			}).then(function(weightsData)
			{
				dom.byId("weightsContent").innerHTML = weightsData;
			});
	}
	weightsTotal = function(updatedId, updatedValue)
	{
		var arr = document.getElementsByName('weights');
		var tot=0;
		for(var i=0;i<arr.length;i++)
		{
			if(parseFloat(arr[i].value)) tot += parseFloat(arr[i].value);
		}
		if(tot <= 100)//Save a weight change only when the total is less than 100
		{
			request.post("scorecards/total-weight.php",{
				//handleAs: "json",
				data:
				{
					id: updatedId,
					weight: updatedValue
				}
				}).then(function(weightsTotal)
				{
					dom.byId("weightsTotal").innerHTML = weightsTotal;
				});
		}
		else //Present user with previous set of weights if last change left the total weights greater than 100. Well, that was an easier fix than I thought (a bit of an anti-climax) - should sleep earlier than anticipated today :-) LTK 04May2021 2206 Hours
		{
			request.post("scorecards/edit-weights.php",{
			//handleAs: "json",
			data:
			{
				id: updatedId
				//parent: tnAdd
			}
			}).then(function(weightsData)
			{
				dom.byId("weightsContent").innerHTML = weightsData;
				var ngojeaWeights = setTimeout(function()
				{
					dom.byId("weightsTotal").innerHTML = "Not Saved. Total weight values<br>cannot be greater than 100%";
				},300);
				//console.log("Total = " + dom.byId("weightsTotal").innerHTML);
			});
		}
		if(tnEdit == updatedId) dijit.byId("weight").set('value', updatedValue);
	}
	saveWeightsDialog = function()
	{
		registry.byId("weightsDialog").hide();
		
		//Removed the following for the time being. Too many "Approval" emails being sent. To review this process at a later stage. LTK 15May2024 1116hrs
        /*request.post("mail/mailWeights.php",{
        //handleAs: "json",
        data:
        {
            selectedMesureId: tnWeight,
            globalDate: globalDate
        }
        }).then(function(message)
        {
            //console.log("Following Email Successfully Sent: <br>" + message);
        });*/
	}
	cancelWeightsDialog = function()
	{
		registry.byId("weightsDialog").onCancel();
	}
	function onTreeItemEdit()
	{
		tnEditHolder = dijit.byNode(this.getParent().currentTarget);
		tnEdit = tnEditHolder.item.id;
		//tnAdd = tnEditHolder.item.id;
		tnEditType = tnEditHolder.item.type;
		//alert(tnEditType);
		edit = "editMe";
		if(tnEditType == "individual")
			showIndividualAddDialog();
		else
			showMeasureAddDialog();
		//tnEdit.item.name = prompt("Enter a new name for the object");
		//governmentStore.put(tnRename.item);
	}
	function onTreeItemDelete()
	{
		var tnDelete = dijit.byNode(this.getParent().currentTarget);
		request.post("layout/delete-tree.php",{
			handleAs: "json",
			data:
			{
				tree_id: tnDelete.item.id,
				tree_type: tnDelete.item.type
			}
			}).then(function(){
				governmentStore.remove(tnDelete.item.id);
			});
	}
	function onTreeItemInitiative()
	{
		// retrieve the id representing the tree item clicked
		tnAdd = dijit.byNode(this.getParent().currentTarget);
		tnName = tnAdd.item.name;
		tnAdd = tnAdd.item.id;
		initiativeImpactId = tnAdd; //having moved functions to initiative.js, makes it hard then to pass variables. Bringing this back here to resolve the issue. 13 April 2021 1616hours
		tnMenuItem = this.get("label");
		registry.byId("newInitiativeDialog").show();
		
		dijit.byId("newInitiativeDialog").set("title", "New Initiative");
		
		managerSelect.set("value", "");
		sponsorSelect.set("value", "");
		parentSelect.set("value", "");
		
		dijit.byId("initiativeNameInput").set("value", "");
		dom.byId("initiativeDeliverableInput").value = "";
		dom.byId("deliverableStatusInput").checked = false;
		dom.byId("initiativeBudgetInput").value = "";
		dom.byId("initiativeStartInput").value = "";
		dom.byId("initiativeDueInput").value = "";
		dom.byId("initiativeParentInput").value = "";
		
		dom.byId("initiativeLinkInput").value = tnName;
		dijit.byId("initiativeLinkInput").set('disabled', true);
		
		dom.byId("initiativeCompleteInput").value = "";
		dom.byId("initiativeCostInput").value = "";
		dom.byId("initiativeStatusInput").value = "";
		dijit.byId("initiativeStatusDetailsInput").value = "";
		dijit.byId("scopeInput").set("value", "");
		dom.byId("percentageCompletionInput").value = "";
		dijit.byId("initiativeStatusDetailsInput").set("value", "");
		dijit.byId("initiativeNotesInput").set("value", "");
		
		dom.byId("editSaveDelete").innerHTML = "Save";
	}
	function onTreeItemReport()
	{
		// retrieve the id representing the tree item clicked
		tnAdd = dijit.byNode(this.getParent().currentTarget);
		//tnEditType = tnAdd.item.type;
		tnName = tnAdd.item.name;
		tnAdd = tnAdd.item.id;
		//dijit.byId("new").get("label");
		tnMenuItem = this.get("label");
		//alert(tnAdd + tnEditType + tnName + tnMenuItem);
		registry.byId("newReportDialog").show();
		//dom.byId("initiativeLinkInput").value = tnName;
	}

postTreeCreationTwo = function()
{
	if(view == "Application")
	{
		//Tree Menu
		tree.on("MouseDown", function(ev,node)
		{
			//var tnPersp = dijit.byNode(this.getParent().currentTarget);
			var here=dijit.getEnclosingWidget(ev.target);
			this.set('selectedNode',here);
			kpiGlobalId = here.item.id;
			//tnPersp = tnPersp.item.type;
	
			if(ev.button == 2 && here.item.type == "perspective")
			{
				var interval = setTimeout(function()
				{
					if(mainMenuState == "Scorecards")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "table-row");
						domStyle.set(dom.byId("measure"), "display", "table-row");
						domStyle.set(dom.byId("linkedMeasure"), "display", "table-row");
						domStyle.set(dom.byId("edit"), "display", "table-row");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Initiatives")
					{
						domStyle.set(dom.byId("task"), "display", "table-row");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("task"), "display", "table-row");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Dashboards")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "table-row");
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Reports")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "table-row");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
				},180);
			}
			else if(ev.button == 2 && here.item.type == "organization")
			{
				var interval = setTimeout(function()
				{
					if(mainMenuState == "Scorecards")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "table-row");
						domStyle.set(dom.byId("individual"), "display", "table-row");
						domStyle.set(dom.byId("perspective"), "display", "table-row");
						domStyle.set(dom.byId("objective"), "display", "table-row");
						domStyle.set(dom.byId("measure"), "display", "table-row");
						domStyle.set(dom.byId("linkedMeasure"), "display", "table-row");
						domStyle.set(dom.byId("edit"), "display", "table-row");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Initiatives")
					{
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("task"), "display", "table-row");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Dashboards")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "table-row");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Reports")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "table-row");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
				},180);
			}
			else if(ev.button == 2 && here.item.type == "objective")
			{
				var interval = setTimeout(function()
				{
					if(mainMenuState == "Scorecards")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "table-row");
						domStyle.set(dom.byId("linkedMeasure"), "display", "table-row");
						domStyle.set(dom.byId("edit"), "display", "table-row");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Initiatives")
					{
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("task"), "display", "table-row");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Dashboards")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "table-row");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Reports")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "table-row");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
				},180);
			}
			else if(ev.button == 2 && here.item.type == "measure")
			{
				var interval = setTimeout(function()
				{
					if(mainMenuState == "Scorecards")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "table-row");
						domStyle.set(dom.byId("linkedMeasure"), "display", "table-row");
						domStyle.set(dom.byId("edit"), "display", "table-row");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Initiatives")
					{
						domStyle.set(dom.byId("task"), "display", "table-row");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Dashboards")
					{
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "table-row");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
						domStyle.set(dom.byId("cut"), "display", "none");
	
					}
					else if(mainMenuState == "Reports")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "table-row");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
				},180);
			}
			else if(ev.button == 2 && here.item.type == "individual")
			{
				var interval = setTimeout(function()
				{
					if(mainMenuState == "Scorecards")
					{
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "table-row");
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "table-row");
						domStyle.set(dom.byId("measure"), "display", "table-row");
						domStyle.set(dom.byId("linkedMeasure"), "display", "table-row");
						domStyle.set(dom.byId("edit"), "display", "table-row");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Initiatives")
					{
						domStyle.set(dom.byId("task"), "display", "table-row");
						domStyle.set(dom.byId("pdpMenu"), "display", "table-row");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
	
					else if(mainMenuState == "Dashboards")
					{
						domStyle.set(dom.byId("dashboard"), "display", "table-row");
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Reports")
					{
						domStyle.set(dom.byId("report"), "display", "table-row");
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
				},180);
				//dijit.byId("new").set("label", "New Measure");
				//dijit.byId("new").delete;
			}
			//domStyle.set(dom.byId("organization"), "display", "none");
		});
		var treeMenu = new Menu({
			id: "treeMenu",
			targetNodeIds: ["tree"],
			selector: ".dijitTreeNode"
		});
		treeMenu.addChild(new MenuItem({
			id: "organization",
			label: "New Organization",
			iconClass: "dijitIconFile",
			onClick: onTreeItemAdd
		}) );
		treeMenu.addChild(new MenuItem({
			id: "individual",
			label: "New Individual",
			iconClass: "dijitIconFile",
			onClick: onIndividualAdd
		}) );
		treeMenu.addChild(new MenuItem({
			id: "perspective",
			label: "New Perspective",
			iconClass: "dijitIconFile",
			onClick: onTreeItemAdd
		}) );
		treeMenu.addChild(new MenuItem({
			id: "objective",
			label: "New Objective",
			iconClass: "dijitIconFile",
			onClick: onTreeItemAdd
		}) );
		treeMenu.addChild(new MenuItem({
			id: "measure",
			label: "New Measure",
			iconClass: "dijitIconFile",
			onClick: onTreeItemAdd
		}) );
		treeMenu.addChild(new MenuItem({
			id: "linkedMeasure",
			label: "Linked Measure",
			iconClass: "dijitIconFile",
			onClick: addLinkedMeasure
		}) );
		treeMenu.addChild(new MenuSeparator());
		treeMenu.addChild(new MenuItem({
			id: "edit",
			label: "Edit",
			iconClass: "dijitIconEditTask",
			onClick: onTreeItemEdit
		}) );
		treeMenu.addChild(new MenuItem({
			id: "editWeight",
			label: "Edit Weights",
			iconClass: "dijitIconEdit",
			onClick: editWeights
		}) );
		/*
		treeMenu.addChild(new MenuItem({
			id: "copy",
			label: "Copy",
			iconClass: "dijitIconCopy",
			onClick: onTreeItemCopy
		}) );
		treeMenu.addChild(new MenuItem({
			id: "cut",
			label: "Cut",
			iconClass: "dijitIconCut",
			//onClick: onTreeItemAdd
		}) );*/
		treeMenu.addChild(new MenuItem({
			id: "delete",
			label: "Delete",
			iconClass: "dijitIconDelete",
			onClick: onTreeItemDelete
		}) );
	
		//*************************************************************
		//initiative menu
		treeMenu.addChild(new MenuItem({
			id: "task",
			label: "Add Initiative",
			iconClass: "dijitIconFile",
			onClick: onTreeItemInitiative
		}) );
		treeMenu.addChild(new MenuItem({
			id: "pdpMenu",
			label: "Add Personal Development Plan",
			iconClass: "dijitIconFile",
			onClick: onPdpAdd
		}) );
		treeMenu.addChild(new MenuItem({
			id: "report",
			label: "Add Report",
			iconClass: "dijitIconFile",
			onClick: onTreeItemReport
		}) );
		treeMenu.addChild(new MenuItem({
			id: "dashboard",
			label: "Add Dashboard",
			iconClass: "dijitIconFile",
			onClick: onTreeItemAdd
		}) );
	
		treeMenu.startup();
		//End of Tree Menu
		//@@@**********************************************************@@@
	}
	else if(view == "False")
	{
		//console.log("view == False");
		//@@@**********************************************************@@@
		//Tree Menu
		tree.on("MouseDown", function(ev,node)
		{
			//var tnPersp = dijit.byNode(this.getParent().currentTarget);
	
			var here=dijit.getEnclosingWidget(ev.target);
			this.set('selectedNode',here);
			kpiGlobalId = here.item.id;
			//tnPersp = tnPersp.item.type;
	
			if(ev.button == 2 && here.item.type == "perspective")
			{
				var interval = setTimeout(function()
				{
					if(mainMenuState == "Scorecards")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "table-row");
						domStyle.set(dom.byId("measure"), "display", "table-row");
						domStyle.set(dom.byId("linkedMeasure"), "display", "table-row");
						domStyle.set(dom.byId("edit"), "display", "table-row");
						domStyle.set(dom.byId("editWeight"), "display", "table-row");
						domStyle.set(dom.byId("delete"), "display", "table-row");
					}
					else if(mainMenuState == "Initiatives")
					{
						domStyle.set(dom.byId("task"), "display", "table-row");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("task"), "display", "table-row");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("editWeight"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
	
					else if(mainMenuState == "Dashboards")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "table-row");
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("editWeight"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Reports")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "table-row");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("editWeight"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
				},180);
			}
			else if(ev.button == 2 && here.item.type == "organization")
			{
				var interval = setTimeout(function()
				{
					if(mainMenuState == "Scorecards")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "table-row");
						domStyle.set(dom.byId("individual"), "display", "table-row");
						domStyle.set(dom.byId("perspective"), "display", "table-row");
						domStyle.set(dom.byId("objective"), "display", "table-row");
						domStyle.set(dom.byId("measure"), "display", "table-row");
						domStyle.set(dom.byId("linkedMeasure"), "display", "table-row");
						domStyle.set(dom.byId("edit"), "display", "table-row");
						domStyle.set(dom.byId("editWeight"), "display", "table-row");
						domStyle.set(dom.byId("delete"), "display", "table-row");
					}
					else if(mainMenuState == "Initiatives")
					{
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("task"), "display", "table-row");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("editWeight"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
	
					else if(mainMenuState == "Dashboards")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "table-row");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("editWeight"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Reports")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "table-row");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("editWeight"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
				},180);
				//domConstruct.destroy('jaribio');
			}
			else if(ev.button == 2 && here.item.type == "objective")
			{
				var interval = setTimeout(function()
				{
					if(mainMenuState == "Scorecards")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "table-row");
						domStyle.set(dom.byId("linkedMeasure"), "display", "table-row");
						domStyle.set(dom.byId("edit"), "display", "table-row");
						domStyle.set(dom.byId("editWeight"), "display", "table-row");
						domStyle.set(dom.byId("delete"), "display", "table-row");
					}
					else if(mainMenuState == "Initiatives")
					{
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("task"), "display", "table-row");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("editWeight"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
	
					else if(mainMenuState == "Dashboards")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "table-row");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("editWeight"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Reports")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "table-row");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("editWeight"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
				},180);
			}
			else if(ev.button == 2 && here.item.type == "measure")
			{
				var interval = setTimeout(function()
				{
					if(mainMenuState == "Scorecards")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "table-row");
						domStyle.set(dom.byId("editWeight"), "display", "table-row");
						domStyle.set(dom.byId("delete"), "display", "table-row");
					}
					else if(mainMenuState == "Initiatives")
					{
						domStyle.set(dom.byId("task"), "display", "table-row");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("editWeight"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
	
					else if(mainMenuState == "Dashboards")
					{
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "table-row");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("editWeight"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
						domStyle.set(dom.byId("cut"), "display", "none");
	
					}
					else if(mainMenuState == "Reports")
					{
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "table-row");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("editWeight"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
				},180);
			}
			else if(ev.button == 2 && here.item.type == "individual")
			{
				var interval = setTimeout(function()
				{
					if(mainMenuState == "Scorecards")
					{
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "table-row");
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "table-row");
						domStyle.set(dom.byId("measure"), "display", "table-row");
						domStyle.set(dom.byId("linkedMeasure"), "display", "table-row");
						domStyle.set(dom.byId("edit"), "display", "table-row");
						domStyle.set(dom.byId("editWeight"), "display", "table-row");
						domStyle.set(dom.byId("delete"), "display", "table-row");
					}
					else if(mainMenuState == "Initiatives")
					{
						domStyle.set(dom.byId("task"), "display", "table-row");
						domStyle.set(dom.byId("pdpMenu"), "display", "table-row");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("editWeight"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Dashboards")
					{
						domStyle.set(dom.byId("dashboard"), "display", "table-row");
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("report"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("editWeight"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
					else if(mainMenuState == "Reports")
					{
						domStyle.set(dom.byId("report"), "display", "table-row");
						domStyle.set(dom.byId("task"), "display", "none");
						domStyle.set(dom.byId("pdpMenu"), "display", "none");
						domStyle.set(dom.byId("dashboard"), "display", "none");
						domStyle.set(dom.byId("organization"), "display", "none");
						domStyle.set(dom.byId("individual"), "display", "none");
						domStyle.set(dom.byId("perspective"), "display", "none");
						domStyle.set(dom.byId("objective"), "display", "none");
						domStyle.set(dom.byId("measure"), "display", "none");
						domStyle.set(dom.byId("linkedMeasure"), "display", "none");
						domStyle.set(dom.byId("edit"), "display", "none");
						domStyle.set(dom.byId("editWeight"), "display", "none");
						domStyle.set(dom.byId("delete"), "display", "none");
					}
				},180);
			}
		});
		var treeMenu = new Menu({
			id: "treeMenu",
			targetNodeIds: ["tree"],
			selector: ".dijitTreeNode"
		});
		treeMenu.addChild(new MenuItem({
			id: "organization",
			label: "New Organization",
			iconClass: "dijitIconFile",
			onClick: onTreeItemAdd
		}) );
		treeMenu.addChild(new MenuItem({
			id: "individual",
			label: "New Individual",
			iconClass: "dijitIconFile",
			onClick: onIndividualAdd
		}) );
		treeMenu.addChild(new MenuItem({
			id: "perspective",
			label: "New Perspective",
			iconClass: "dijitIconFile",
			onClick: onTreeItemAdd
		}) );
		treeMenu.addChild(new MenuItem({
			id: "objective",
			label: "New Objective",
			iconClass: "dijitIconFile",
			onClick: onTreeItemAdd
		}) );
		treeMenu.addChild(new MenuItem({
			id: "measure",
			label: "New Measure",
			iconClass: "dijitIconFile",
			onClick: onTreeItemAdd
		}) );
		treeMenu.addChild(new MenuItem({
			id: "linkedMeasure",
			label: "Linked Measure",
			iconClass: "dijitIconFile",
			onClick: addLinkedMeasure
		}) );
		treeMenu.addChild(new MenuSeparator());
		treeMenu.addChild(new MenuItem({
			id: "edit",
			label: "Edit",
			iconClass: "dijitIconEditTask",
			onClick: onTreeItemEdit
		}) );
		treeMenu.addChild(new MenuItem({
			id: "editWeight",
			label: "Edit Weights",
			iconClass: "dijitIconEdit",
			onClick: editWeights
		}) );
		/*
		treeMenu.addChild(new MenuItem({
			id: "copy",
			label: "Copy",
			iconClass: "dijitIconCopy",
			onClick: onTreeItemCopy
		}) );
		treeMenu.addChild(new MenuItem({
			id: "cut",
			label: "Cut",
			iconClass: "dijitIconCut",
			//onClick: onTreeItemAdd
		}) );*/
		treeMenu.addChild(new MenuItem({
			id: "delete",
			label: "Delete",
			iconClass: "dijitIconDelete",
			onClick: onTreeItemDelete
		}) );
	
		//*************************************************************
		//initiative menu
		treeMenu.addChild(new MenuItem({
			id: "task",
			label: "Add Initiative",
			iconClass: "dijitIconFile",
			onClick: onTreeItemInitiative
		}) );
		treeMenu.addChild(new MenuItem({
			id: "pdpMenu",
			label: "Add Personal Development Plan",
			iconClass: "dijitIconFile",
			onClick: onPdpAdd
		}) );
		treeMenu.addChild(new MenuItem({
			id: "report",
			label: "Add Report",
			iconClass: "dijitIconFile",
			onClick: onTreeItemReport
		}) );
		treeMenu.addChild(new MenuItem({
			id: "dashboard",
			label: "Add Dashboard",
			iconClass: "dijitIconFile",
			onClick: onTreeItemAdd
		}) );
	
		treeMenu.startup();
		//End of Tree Menu
		//@@@**********************************************************@@@
	}
	else
	{
		console.log("view == True");
		//do nothing. don't show menu for viewers.
	}
}
/*var ngola = setTimeout(function()
{

},2000)*/

bscMenuCase = function()
{
	//you may need to put the content in the case in a function if you are to use the same to the Performance Contract
	cp = new ContentPane({
	region: "center",
	"class": "bpaPrint",
		href:"scorecards/highScorecard.php"
		//style:"overflow:auto"
		});
		cp.placeAt("appLayout");
		domStyle.set(dom.byId("userSettings"), "display", "none");
		domStyle.set(dom.byId("coreValues"), "display", "none");
		domStyle.set(dom.byId("expandCollapse"), "display", "block");
		domStyle.set(dom.byId("tree"), "display", "block");
		domStyle.set(dom.byId("definitionTables"), "display", "none");
		domStyle.set(dom.byId("homeLinks"), "display", "none");
		//domStyle.set(dom.byId("advocacyLinks"), "display", "none");
		domStyle.set(registry.byId("menuSeparator").domNode, 'display', 'none');
		dojo.byId("dynamicMenu").innerHTML = "";
		if(dijit.byId("updateMeasureDialog")){
		dijit.byId("updateMeasureDialog").destroy(true);
		//dijit.byId("updateMeasureDialog").destroyRecursive();
		}
		//dijit.byId("updateMeasureDialog").destroyRecursive();
		domConstruct.destroy('divGroup1');
		domConstruct.destroy("divIntro");
		domConstruct.destroy("plot");
		domConstruct.destroy("myGauge");
		domConstruct.destroy('divChart');
		domConstruct.destroy('chartDiv');
		domConstruct.destroy('notes');
		domConstruct.destroy('objectiveName');
		domConstruct.destroy('objectiveDescription');
		domConstruct.destroy('objectiveOwner');
		domConstruct.destroy('objectiveTeam');
		domConstruct.destroy('divGroup2');
		domConstruct.destroy('divMeasures');
		domConstruct.destroy('divInitiatives');
		domConstruct.destroy('divCascadedTo');
		domConstruct.destroy('divDevelopmentPlan');
		domConstruct.destroy('divCoreValues');
		domConstruct.destroy('divNotes');
		domConstruct.destroy('divNotes2');
		domConstruct.destroy('divMeasureNotes');//19th January 2017. Incorporating a module to handle PC notes.
		domConstruct.destroy('measureNotes');
		domConstruct.destroy('scoreGauge4');
		domConstruct.destroy('divChartType');
		domConstruct.destroy('chartLegend');
		domConstruct.destroy('previousCheckbox');
		domConstruct.destroy('msgContent');
		domConstruct.destroy('bodyContent');
		domConstruct.destroy('cascadedContent');
		domConstruct.destroy('pdp');
		domConstruct.destroy('coreValuesScorecardPage');
		domConstruct.destroy('interpretation');
		domConstruct.destroy('wayForward');
		if(dijit.byId("divConversation")) dijit.byId("divConversation").destroyRecursive();
		if(dijit.byId("divConversation")) dijit.byId("divConversation").destroy(true);
		// Properly destroy conversation widgets
		if(registry.byId("divAdvocacyConversation")) {
			registry.byId("divAdvocacyConversation").destroyRecursive();
		}
		if(registry.byId("divInitiativeConversation")) {
			registry.byId("divInitiativeConversation").destroyRecursive();
		}
		if(registry.byId("divScorecardConversation")) {
			registry.byId("divScorecardConversation").destroyRecursive();
		}
		// Clean up DOM elements after widget destruction
		domConstruct.destroy('divAdvocacyConversation');
		domConstruct.destroy('divInitiativeConversation');
		domConstruct.destroy('divScorecardConversation');
		domConstruct.destroy('conversationHistory');
		domConstruct.destroy('conversation');
		domConstruct.destroy('submitId');
		if(dijit.byId("kpiAuditTrailDialog")) dijit.byId("kpiAuditTrailDialog").destroyRecursive();
		if(dijit.byId("kpiAuditTrailDialog")) dijit.byId("kpiAuditTrailDialog").destroy(true);
		domConstruct.destroy('kpiAuditContent');
		domConstruct.destroy('scrollIntoView');
		if(dijit.byId("csvImport")) dijit.byId("csvImport").destroy(true);
		if(dijit.byId("newCsvFileDialog")) dijit.byId("newCsvFileDialog").destroy(true);
		domConstruct.destroy('csvFormat');
		domConstruct.destroy('csvErrors');
		domConstruct.destroy('gridContainer');
		if(dijit.byId("csvSaveButton")) dijit.byId("csvSaveButton").destroy(true);//domConstruct.destroy('csvSaveButton');
		domConstruct.destroy('csvForm');
		if(dijit.byId("csvUploader")) dijit.byId("csvUploader").destroy(true);//domConstruct.destroy('csvUploader');
		domConstruct.destroy('hiddenKpiId');
		if(dijit.byId("csvFiles")) dijit.byId("csvFiles").destroy(true);//domConstruct.destroy('csvFiles');
		if(dijit.byId("chartSlider")) dijit.byId("chartSlider").destroy(true);
		
		if(dijit.byId("bulkMeasureDialogGoal"))
			dijit.byId("bulkMeasureDialogGoal").destroyRecursive();
		if(dijit.byId("bulkMeasureDialogGoal")) dijit.byId("bulkMeasureDialogGoal").destroy(true);	
			domConstruct.destroy("gridKpi");
		
		if(dijit.byId("bulkMeasureDialog2"))
			dijit.byId("bulkMeasureDialog2").destroyRecursive();
		if(dijit.byId("bulkMeasureDialog2")) dijit.byId("bulkMeasureDialog2").destroy(true);	
		domConstruct.destroy("gridKpi2");
		
		if(dijit.byId("bulkMeasureDialog3"))
			dijit.byId("bulkMeasureDialog3").destroyRecursive();
		if(dijit.byId("bulkMeasureDialog3")) dijit.byId("bulkMeasureDialog3").destroy(true);
		domConstruct.destroy("gridKpi3");
		
		if(dijit.byId("bulkMeasureDialog4"))
			dijit.byId("bulkMeasureDialog4").destroyRecursive();
		if(dijit.byId("bulkMeasureDialog4")) dijit.byId("bulkMeasureDialog4").destroy(true);
		domConstruct.destroy("gridKpi4");
		//domConstruct.destroy('updateButton');
		//dijit.byId('kpiAuditTrailButton').destroy();
		//if(dojo.byId("divConversation") != null) dijit.byId('divConversation').destroyRecursive();
}

/****************************************************************************************
Menu item selection handler
*****************************************************************************************/
var onItemSelect = function(event)
{
	//dom.byId("lastSelected").innerHTML = this.get("label");
	var menuItemId = this.get("id");
	switch(menuItemId)
	{
		case "day":
		{
			//alert("Tuko kwa date na id ni: " + menuItemId);
			//registry.byId("dateDialog").show();
			//registry.byId("calMonthOnly").value = "null";
			popup.open({
            popup: registry.byId("calDayOnly"),
            around: dom.byId("Periods")
        	});
			break;
		}
		case "week":
		{
			popup.open({
				popup: registry.byId("calWeekOnly"),
				around: dom.byId("Periods")
			});
			break;
		}
		case "month":
		{
			popup.open({
				popup: registry.byId("calMonthOnly"),
				around: dom.byId("Periods")
			});
			break;
		}
		case "quarter":
		{
			popup.open({
				popup: registry.byId("calQuarterOnly"),
				around: dom.byId("Periods")
			});
			break;
		}
		case "halfYear":
		{
			popup.open({
				popup: registry.byId("calHalfYearOnly"),
				around: dom.byId("Periods")
			});
			break;
		}
		case "year":
		{
			popup.open({
				popup: registry.byId("calYearOnly"),
				around: dom.byId("Periods")
			});
			break;
		}
		//var menuItem = this.get("label");//was repeating this after the menu related functions that were sandwiched in between
		//var menuItemId = this.get("id");
		case "home":
		{
			//console.log(dojo.byId('viewRights').innerHTML);
			if(dojo.byId('viewRights').innerHTML == 'Viewer') pduDbProjects();
			else pduDbHome();
			domStyle.set(dom.byId("userSettings"), "display", "none");
			domStyle.set(dom.byId("coreValues"), "display", "none");
			domStyle.set(dom.byId("tree"), "display", "none");
			domStyle.set(dom.byId("expandCollapse"), "display", "none");
			domStyle.set(dom.byId("definitionTables"), "display", "none");
			domStyle.set(dom.byId("homeLinks"), "display", "block");
			//domStyle.set(dom.byId("advocacyLinks"), "display", "none");
			domStyle.set(registry.byId("menuSeparator").domNode, 'display', 'none');
			break;
		}
		case "home_previous":
		{
			//var bc = dom.byId("appLayout");
			mainMenuState = "Home";
			if(dojo.byId('viewRights').innerHTML == 'Board')
			{
				displayBookmark('report','Reports','19','Scorecard Summary');
			}
			else
			{
				cp = new ContentPane({
			region: "center",
			"class": "bpaPrint",
				href:"dashboards/indDashboard.php"
				//href:"../dojox/calendar/tests/calendar.html"
				});
				cp.placeAt("appLayout");
			}
				domStyle.set(dom.byId("userSettings"), "display", "none");
				domStyle.set(dom.byId("coreValues"), "display", "none");
				domStyle.set(dom.byId("tree"), "display", "none");
				domStyle.set(dom.byId("expandCollapse"), "display", "none");
				domStyle.set(dom.byId("definitionTables"), "display", "none");
				domStyle.set(dom.byId("homeLinks"), "display", "block");
				//domStyle.set(dom.byId("advocacyLinks"), "display", "none");
				domStyle.set(registry.byId("menuSeparator").domNode, 'display', 'none');
				dojo.byId("dynamicMenu").innerHTML = "";

				
				domConstruct.destroy("divIndGroup1");

				domConstruct.destroy("divIndGauge");
				domConstruct.destroy("divIndWeight");
				domConstruct.destroy("myIndGauge");

				domConstruct.destroy("divIndGroup2");
				domConstruct.destroy("divIndDescription");
				domConstruct.destroy("divIndPhoto");
				domConstruct.destroy("indPhoto");
				domConstruct.destroy("indDescription");

				domConstruct.destroy("divIndChart");
				domConstruct.destroy("indChart");

				domConstruct.destroy("divIndInitiatives");
				domConstruct.destroy("initiativeIndContent");

				domConstruct.destroy("divIndCascadedTo");
				domConstruct.destroy("cascadedIndContent");

				domConstruct.destroy("divIndDevelopmentPlan");
				domConstruct.destroy("IndPdp");

				domConstruct.destroy("divIndNotes");
				domConstruct.destroy("indInterpretation");
				domConstruct.destroy("divIndNotes2");
				domConstruct.destroy("indWayForward");

				domConstruct.destroy("scrollIntoView");
				break;
		}
		case "bsc":
		{
			mainMenuState = "Scorecards";
			treeFunction("bscData");
			var interval = setTimeout(function()//wait for tree to finish loading
			{
			//tree.collapseAll();//starting the tree on expand mode doesn't show newly added nodes so using this as a work around. LTK 30.11.15
			bscMenuCase();	
			},3000);
			break;
		}
		case "performanceContract":
		{
			mainMenuState = "performanceContract";
			treeFunction("pcData");
			var interval = setTimeout(function()//wait for tree to finish loading
			{
				bscMenuCase();
			},1000);
			//tree.collapseAll();//starting the tree on expand mode doesn't show newly added nodes so using this as a work around. LTK 30.11.15
			break;
		}
		case "initiatives":
		{
			treeFunction("bscData");

			//var bc = dom.byId("appLayout");
			mainMenuState = "Initiatives";
			cp = new ContentPane({
				href:"initiatives/initiative.php",
				region: "center"
				//style: "width: 100px; height:200px; position:absolute; left: 1px; "
				});
				cp.placeAt("appLayout");
				
				//dijit.byId('initiativeGauge').destroyRecursive();
				//dijit.byId('initiativeStatusGauge').destroyRecursive();
				//domConstruct.destroy("newInitiativeDialog");
				domStyle.set(dom.byId("userSettings"), "display", "none");
				domStyle.set(dom.byId("coreValues"), "display", "none");
				domStyle.set(dom.byId("expandCollapse"), "display", "block");
				domStyle.set(dom.byId("tree"), "display", "block");
				domStyle.set(dom.byId("definitionTables"), "display", "none");
				domStyle.set(dom.byId("homeLinks"), "display", "none");
				//domStyle.set(dom.byId("advocacyLinks"), "display", "none");
				domStyle.set(registry.byId("menuSeparator").domNode, 'display', 'none');
				dojo.byId("dynamicMenu").innerHTML = "";
				
				if(dijit.byId("initiativeListSelect")) dijit.byId("initiativeListSelect").destroy(true);
				if(dijit.byId("initiativeStartInput")) dijit.byId("initiativeStartInput").destroy(true);
				if(dijit.byId("newInitiativeManagerInput")) dijit.byId("newInitiativeManagerInput").destroy(true);
				if(dijit.byId("initiativeManagerInput")) dijit.byId("initiativeManagerInput").destroy(true);
				if(dijit.byId("initiativeSponsorInput")) dijit.byId("initiativeSponsorInput").destroy(true);
				if(dijit.byId("initiativeDueInput")) dijit.byId("initiativeDueInput").destroy(true);
				if(dijit.byId("initiativeCompleteInput")) dijit.byId("initiativeCompleteInput").destroy(true);

				domConstruct.destroy("nav-home-tab"); //LTK 16 March 2021 0751 hours
				domConstruct.destroy("nav-allInitiatives-tab");
				domConstruct.destroy("nav-home");
				domConstruct.destroy("nav-gantt-tab");
				domConstruct.destroy("nav-gantt");
				domConstruct.destroy("initiativeNameDiv");
				domConstruct.destroy("objectImpactedDiv")
				domConstruct.destroy("archive");
				domConstruct.destroy("sponsorDiv");
				domConstruct.destroy("managerDiv");
				domConstruct.destroy("parentDiv");
				domConstruct.destroy("deliverableDiv");
				domConstruct.destroy("scopeDiv");
				domConstruct.destroy("budgetDiv");
				domConstruct.destroy("tdDamageColor");
				domConstruct.destroy("damageDiv");
				domConstruct.destroy("startDateDiv");
				domConstruct.destroy("endDateDiv");
				domConstruct.destroy("completionDateDiv");
				domConstruct.destroy("initiativeGauge");
				domConstruct.destroy("statusDiv");//LTK. 25.03.2018 12.45am
				domConstruct.destroy("percentageCompletionDiv");
				domConstruct.destroy("statusDetailsDiv");
				domConstruct.destroy("statusNotesDiv");
				domConstruct.destroy("testTd");
				
				domConstruct.destroy("gantt");
				// Properly destroy conversation widgets
				if(registry.byId("divConversation")) {
					registry.byId("divConversation").destroyRecursive();
				}
				if(registry.byId("divAdvocacyConversation")) {
					registry.byId("divAdvocacyConversation").destroyRecursive();
				}
				if(registry.byId("divInitiativeConversation")) {
					registry.byId("divInitiativeConversation").destroyRecursive();
				}
				if(registry.byId("divScorecardConversation")) {
					registry.byId("divScorecardConversation").destroyRecursive();
				}
				// Clean up DOM elements after widget destruction
				domConstruct.destroy('divConversation');
				domConstruct.destroy('divAdvocacyConversation');
				domConstruct.destroy('divInitiativeConversation');
				domConstruct.destroy('divScorecardConversation');
				domConstruct.destroy('conversationHistory');
				domConstruct.destroy('conversation');
				domConstruct.destroy('submitId');

				domConstruct.destroy("nav-allInitiatives");
				domConstruct.destroy("table");
				
				if(dijit.byId("newInitiativeDialog")) dijit.byId("newInitiativeDialog").destroy(true);
				domConstruct.destroy("newInitiativeDialog-table");
				domConstruct.destroy("initiativeName");
				if(dijit.byId("initiativeNameInput")) dijit.byId("initiativeNameInput").destroy(true);
				domConstruct.destroy("initiativeDeliverable");
				domConstruct.destroy("initiativeDeliverableInput");
				domConstruct.destroy("initiativeScope");
				if(dijit.byId("scopeInput")) dijit.byId("scopeInput").destroy(true);
				domConstruct.destroy("initiativeDeliverable");
				domConstruct.destroy("deliverableStatusInput");
				domConstruct.destroy("initiativeSponsor");
				domConstruct.destroy("initiativeSponsorInput");
				domConstruct.destroy("initiativeManager");
				domConstruct.destroy("initiativeManagerInput");
				domConstruct.destroy("initiativeTeam");
				domConstruct.destroy("initiativeTeamInput");
				if(dijit.byId("initiativeTeamInput")) dijit.byId("initiativeTeamInput").destroy(true);
				domConstruct.destroy("teamMembers");
				domConstruct.destroy("initiativeParent");
				domConstruct.destroy("initiativeParentInput");
				if(dijit.byId("initiativeParentInput")) 
				{
					dijit.byId("initiativeParentInput").destroyRecursive();
					if(dijit.byId("initiativeParentInput")) dijit.byId("initiativeParentInput").destroy(true);
				}
				domConstruct.destroy("initiativeLink");
				domConstruct.destroy("initiativeLinkInput");
				if(dijit.byId("initiativeLinkInput")) 
				{
					dijit.byId("initiativeLinkInput").destroyRecursive();
					if(dijit.byId("initiativeLinkInput")) dijit.byId("initiativeLinkInput").destroy(true);
				}
				domConstruct.destroy("initiativeBudget");
				domConstruct.destroy("initiativeBudgetInput");
				domConstruct.destroy("initiativeCost");
				domConstruct.destroy("initiativeCostInput");
				
				domConstruct.destroy("initiativeStart");
				domConstruct.destroy("initiativeStartInput");
				if(dijit.byId("initiativeStartInput")) dijit.byId("initiativeStartInput").destroy(true);
				
				domConstruct.destroy("initiativeDue");
				domConstruct.destroy("initiativeDueInput");
				if(dijit.byId("initiativeDueInput")) dijit.byId("initiativeDueInput").destroy(true);
				
				domConstruct.destroy("initiativeComplete");
				domConstruct.destroy("initiativeCompleteInput");
				if(dijit.byId("initiativeComplete")) dijit.byId("initiativeComplete").destroy(true);
				
				domConstruct.destroy("initiativeStatus");
				domConstruct.destroy("initiativeStatusInput");
				domConstruct.destroy("initiativeStatusDetails");
				if(dijit.byId("initiativeStatusDetailsInput")) 
				{
					dijit.byId("initiativeStatusDetailsInput").destroyRecursive();
					if(dijit.byId("initiativeStatusDetailsInput")) dijit.byId("initiativeStatusDetailsInput").destroy(true);
				}
				domConstruct.destroy("initiativeNotes");
				if(dijit.byId("initiativeNotesInput")) dijit.byId("initiativeNotesInput").destroy(true);
				domConstruct.destroy("percentageCompletion");
				domConstruct.destroy("percentageCompletionInput");
				
				domConstruct.destroy("userListGantt");
				if(dijit.byId("userListGantt")) 
				{
					dijit.byId("userListGantt").destroyRecursive();
					if(dijit.byId("userListGantt")) dijit.byId("userListGantt").destroy(true);
				}
				
				domConstruct.destroy("nav-pip-tab");
				domConstruct.destroy("nav-pip");
				domConstruct.destroy("tablePIP");
				
				var reportTimer = setTimeout(function()
				{
					//Color the scorecard elements with initiatives
					request.post("initiatives/get-initiative-elements.php",{
					handleAs: "json"
					}).then(function(data)
					{
						//tree.expandAll();
						var initiativeCounter = 0;
						while(initiativeCounter < data.length)
						{
							//console.log("linkedObject id = " + data[initiativeCounter].id);
							var trial = tree.getDomNodeById(data[initiativeCounter].id);
							trial.labelNode.style.color = "#006400";
							trial.labelNode.style.fontWeight = "bold";
							//trial.labelNode.style.backgroundColor = "green";
							initiativeCounter++;
						}
						collapseTree();
					});
				},2000);

				break;
		}
		case "flagshipProjects":
		{
			//mainMenuState = "Strategy Map";
			cp = new ContentPane({
			region: "center",
			"class": "bpaPrint",
			href:"pdu_db_home.php"
			});
			cp.placeAt("appLayout");
			domStyle.set(dom.byId("userSettings"), "display", "none");
			domStyle.set(dom.byId("coreValues"), "display", "none");
			domStyle.set(dom.byId("tree"), "display", "none");
			domStyle.set(dom.byId("expandCollapse"), "display", "none");
			domStyle.set(dom.byId("definitionTables"), "display", "none");
			domStyle.set(dom.byId("homeLinks"), "display", "block");
			//domStyle.set(dom.byId("advocacyLinks"), "display", "none");
			domStyle.set(registry.byId("menuSeparator").domNode, 'display', 'none');
			dojo.byId("dynamicMenu").innerHTML = "";
			break;
		}
		case "reports":
		{
			mainMenuState = "Reports";
			treeFunction("bscData");
			cp = new ContentPane({
				region: "center",
				href:"reports/report.php"
				});
			cp.placeAt("appLayout");
			domStyle.set(dom.byId("userSettings"), "display", "none");
			domStyle.set(dom.byId("coreValues"), "display", "none");
			domStyle.set(dom.byId("tree"), "display", "block");
			domStyle.set(dom.byId("expandCollapse"), "display", "block");
			domStyle.set(dom.byId("definitionTables"), "display", "none");
			domStyle.set(dom.byId("homeLinks"), "display", "none");
			//domStyle.set(dom.byId("advocacyLinks"), "display", "none");
			domStyle.set(registry.byId("menuSeparator").domNode, 'display', 'none');
			dojo.byId("dynamicMenu").innerHTML = "";
			domConstruct.destroy('displayReport');
			//domConstruct.destroy('redReportName');
			domConstruct.destroy('tdRedReportName');
			domConstruct.destroy('selectedObjects');
			domConstruct.destroy('selectedObjectsIds');
			domConstruct.destroy('columnsToShow');
			domConstruct.destroy('columnOrg');
			domConstruct.destroy('columnOrgScore');
			domConstruct.destroy('columnPersp');
			domConstruct.destroy('columnPerspScore');
			domConstruct.destroy('columnObj');
			domConstruct.destroy('columnObjScore');
			domConstruct.destroy('columnKpi');
			domConstruct.destroy('columnOwner');
			domConstruct.destroy('columnUpdater');
			domConstruct.destroy('columnScore');
			domConstruct.destroy('columnActual');
			domConstruct.destroy('columnTarget');
			domConstruct.destroy('columnVariance');
			domConstruct.destroy('columnPercentVariance');
			domConstruct.destroy('redFilter');
			domConstruct.destroy('greyFilter');
			domConstruct.destroy('greenFilter');
			domConstruct.destroy('initiativeFilter');
			domConstruct.destroy('initiativeGroup');
			domConstruct.destroy('newInitiativeReportDialog');
			//domConstruct.destroy('initiativeReportName');
			domConstruct.destroy('tdInitiativeReportName');
			domConstruct.destroy('selectedInitObjects');
			domConstruct.destroy('selectedInitObjectsIds');
			domConstruct.destroy('initColumnsToShow');
			domConstruct.destroy('initSponsor');
			domConstruct.destroy('initOwner');
			domConstruct.destroy('initBudget');
			domConstruct.destroy('initCost');
			domConstruct.destroy('initStart');
			domConstruct.destroy('initDue');
			domConstruct.destroy('initComplete');
			domConstruct.destroy('initDeliverable');
			domConstruct.destroy('initDeliverableStatus');
			domConstruct.destroy('initParent');
			domConstruct.destroy('initRedFilter');
			domConstruct.destroy('initGreyFilter');
			domConstruct.destroy('initGreenFilter');
			domConstruct.destroy('tdCascadeReportName');
			//domConstruct.destroy('cascadeReportName');
			domConstruct.destroy('selectedCascadeObjects');
			domConstruct.destroy('selectedCascadeObjectsIds');
			domConstruct.destroy('organizationsReport');
			domConstruct.destroy('perspectivesReport');
			domConstruct.destroy('objectivesReport');
			domConstruct.destroy('measuresReport');
			domConstruct.destroy('gridCopyReport');
			domConstruct.destroy('droppedItems');

			if(dijit.byId("redReportName")) dijit.byId("redReportName").destroy(true);
			if(dijit.byId("initiativeReportName")) dijit.byId("initiativeReportName").destroy(true);
			if(dijit.byId("cascadeReportName")) dijit.byId("cascadeReportName").destroy(true);
			if(dijit.byId("newCustomReportDialog")) dijit.byId("newCustomReportDialog").destroy(true);
			if(dijit.byId("newReportDialog")) dijit.byId("newReportDialog").destroy(true);
			if(dijit.byId("newInitiativeReportDialog")) dijit.byId("newInitiativeReportDialog").destroy(true);
			if(dijit.byId("newCascadeReportDialog")) dijit.byId("newCascadeReportDialog").destroy(true);
			if(dijit.byId("selectObjectDialog")) dijit.byId("selectObjectDialog").destroy(true);
			var reportTimer = setTimeout(function()
			{
				//Color the scorecard elements with reports
				request.post("reports/get-report-elements.php",{
				handleAs: "json"
				}).then(function(data)
				{
					var reportCounter = 0;
					while(reportCounter < data.length)
					{
						var trial = tree.getDomNodeById(data[reportCounter].id);
						trial.labelNode.style.color = "#006400";
						trial.labelNode.style.fontWeight = "bold";
						//trial.labelNode.style.backgroundColor = "green";
						reportCounter++;
					}
					collapseTree();
				});
			},2000);
			break;
		}
		case "calendarMenu":
		{
			mainMenuState = "Calendar";
			cp = new ContentPane({
			region: "center",
			"class": "bpaPrint cpStyles",
				href:"calendar/index.html"
				});
				cp.placeAt("appLayout");
				domStyle.set(dom.byId("userSettings"), "display", "none");
				domStyle.set(dom.byId("coreValues"), "display", "none");
				domStyle.set(dom.byId("tree"), "display", "none");
				domStyle.set(dom.byId("expandCollapse"), "display", "none");
				domStyle.set(dom.byId("homeLinks"), "display", "true");
				//domStyle.set(registry.byId("menuSeparator").domNode, 'display', 'none');
				//dojo.byId("dynamicMenu").innerHTML = "";
				domConstruct.destroy("wrap");
				domConstruct.destroy("calendar");
			break;
		}
		case "admin":
		{
			mainMenuState = "Admin";
			//var bc = dom.byId("appLayout");

			cp = new ContentPane({
			region: "center",
			"class": "bpaPrint",
				href:"admin/account.php"
				});
				cp.placeAt("appLayout");
				//bc.addChild(cp);
				//bc.startup();
				domStyle.set(dom.byId("userSettings"), "display", "block");
				domStyle.set(dom.byId("coreValues"), "display", "none");
				domStyle.set(dom.byId("tree"), "display", "none");
				domStyle.set(dom.byId("expandCollapse"), "display", "none");
				domStyle.set(dom.byId("definitionTables"), "display", "none");
				domStyle.set(dom.byId("homeLinks"), "display", "none");
				//domStyle.set(dom.byId("advocacyLinks"), "display", "none");
				domStyle.set(registry.byId("menuSeparator").domNode, 'display', 'none');
				dojo.byId("dynamicMenu").innerHTML = "";
				domConstruct.destroy("main");
			break;
		}
		case "coreValuesMenu":
		{
			mainMenuState = "coreValuesMenu";
			cp = new ContentPane({
			region: "center",
			"class": "bpaPrint",
				href:"scorecards/coreValues/getCoreValues.php?mainMenuState=coreValuesMenu"
				});
				cp.placeAt("appLayout");
				domStyle.set(dom.byId("coreValues"), "display", "block");
				domStyle.set(dom.byId("userSettings"), "display", "none");
				domStyle.set(dom.byId("tree"), "display", "none");
				domStyle.set(dom.byId("expandCollapse"), "display", "none");
				domStyle.set(dom.byId("definitionTables"), "display", "none");
				domStyle.set(dom.byId("homeLinks"), "display", "none");
				//domStyle.set(dom.byId("advocacyLinks"), "display", "none");
				//domStyle.set(registry.byId("menuSeparator").domNode, 'display', 'none');
				//dojo.byId("dynamicMenu").innerHTML = "";
				
				//domConstruct.destroy("coreValueId");
				//domConstruct.destroy("attributeId");
				//domConstruct.destroy("attributeScoreId");
				
				/*domConstruct.destroy("coreValue");
				domConstruct.destroy("coreValueDescription");
				domConstruct.destroy("errorMsgCoreValue");
				domConstruct.destroy("attribute");
				domConstruct.destroy("attributeDescription");
				domConstruct.destroy("errorMsgAttribute");
				domConstruct.destroy("errorMsgCoreValue");
				domConstruct.destroy("attributeScore");
				domConstruct.destroy("attributeScoreDate");
				domConstruct.destroy("attributeScoreList");
				domConstruct.destroy("errorMsgAttributeScore");
				domConstruct.destroy("attributeScoreDialog");
				domConstruct.destroy("coreValueDialog");*/
			break;
		}
		case "logOut":
		{
			window.location="logout.php";
			break;
		}
	}//End of Case Switch for mainMenuState.

};
/****************************************************************************************
End of menu item selection handler
*****************************************************************************************/

/****************************************************************************************
Start of menu related functions
*****************************************************************************************/
onCalendarChange = function()//Refresh Main Page Content
{
	switch(mainMenuState)
	{
		case "Scorecards":
		{
			updateChart();
			break;	
		}
		case "Personal Dashboard": //Adding the ability of the Personal Dashboard to show previous years. Should be applicable to all daashboards and reports. LTK 16May2021 1544Hrs
		{
			indPerformance();//Not sure this function can be accessed from here but let's see... It could :-) LTK 16May2021 1532Hrs
			var appraisalDateWait = setTimeout(function()
			{
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
				dom.byId("appraisalDate").innerHTML = appraisalDate; //Showing relevant appraisal date. LTK 23Aug2021 0833Hrs
			},330);
			break;	
		}
		case "Accent Usage":
		{
			accentUsage();
			break;	
		}
	}	
}
closeDay = function()
{
	//alert(registry.byId("calMonthOnly").value);
	popup.close(registry.byId("calDayOnly"));
	var newDateLabel = registry.byId("calDayOnly").value;
	var phpDateLabel = newDateLabel;
	newDateLabel = locale.format(newDateLabel, {
						selector: "date",
						datePattern:"dd-MMM-yyyy"
						});
	//alert(newDateLabel);
	dojoDisplayDate.set("label", newDateLabel);
	period = "days";
	phpDateLabel = locale.format(phpDateLabel, {
						selector: "date",
						datePattern:"yyyy-MM-dd"
						});
	globalDate = phpDateLabel;
	onCalendarChange();//Refresh Main Page Content. LTK 27 June 2021, 0036hrs
}
closeWeek = function()
{
	//alert(registry.byId("calMonthOnly").value);
	popup.close(registry.byId("calWeekOnly"));
	var newDateLabel = registry.byId("calWeekOnly").value;
	var phpDateLabel = newDateLabel;
	newDateLabel = locale.format(newDateLabel, {
						selector: "date",
						datePattern:"MMM-yyyy"
						});
	//alert(newDateLabel);
	dojoDisplayDate.set("label", newDateLabel);
	period = "weeks";
	phpDateLabel = locale.format(phpDateLabel, {
						selector: "date",
						datePattern:"yyyy-MM"
						});
	globalDate = phpDateLabel;
	onCalendarChange();
}
closeMonth = function()
{
	//alert(registry.byId("calMonthOnly").value);
	popup.close(registry.byId("calMonthOnly"));
	var newDateLabel = registry.byId("calMonthOnly").value;
	var phpDateLabel = newDateLabel;
	newDateLabel = locale.format(newDateLabel, {
						selector: "date",
						datePattern:"MMM-yyyy"
						});
	//alert(newDateLabel);
	dojoDisplayDate.set("label", newDateLabel);
	period = "months";
	phpDateLabel = locale.format(phpDateLabel, {
						selector: "date",
						datePattern:"yyyy-MM"
						});
	globalDate = phpDateLabel;
	onCalendarChange();
}
closeQuarter = function()
{
	popup.close(registry.byId("calQuarterOnly"));
	//alert("We here? "+registry.byId("calQuarterOnly").value);
	var newDateLabel = registry.byId("calQuarterOnly").value;
	var phpDateLabel = newDateLabel;
	newDateLabel = locale.format(newDateLabel, {
						selector: "date",
						datePattern:"qQ"
						});
	switch(newDateLabel)
	{
		case "01":
		{
			newDateLabel = "Quarter 1";
			break;
		}
		case "02":
		{
			newDateLabel = "Quarter 2";
			break;
		}
		case "03":
		{
			newDateLabel = "Quarter 3";
			break;
		}
		case "04":
		{
			newDateLabel = "Quarter 4";
			break;
		}
	}
	dojoDisplayDate.set("label", newDateLabel);
	period = "quarters";
	phpDateLabel = locale.format(phpDateLabel, {
						selector: "date",
						datePattern:"yyyy-MM"
						});
	globalDate = phpDateLabel;
	onCalendarChange();
}
closeHalfYear = function()
{
	popup.close(registry.byId("calHalfYearOnly"));
	var newDateLabel = registry.byId("calHalfYearOnly").value;
	var phpDateLabel = newDateLabel;
	newDateLabel = locale.format(newDateLabel, {
						selector: "date",
						datePattern:"qQ"
						});

	switch(newDateLabel)
	{
		case "01":
		{
			newDateLabel = "Half Year 1";
			break;
		}
		case "02":
		{
			newDateLabel = "Half Year 1";
			break;
		}
		case "03":
		{
			newDateLabel = "Half Year 2";
			break;
		}
		case "04":
		{
			newDateLabel = "Half Year 2";
			break;
		}
	}

	dojoDisplayDate.set("label", newDateLabel);
	period = "halfYears";
	phpDateLabel = locale.format(phpDateLabel, {
						selector: "date",
						datePattern:"yyyy-MM"
						});
	globalDate = phpDateLabel;
	onCalendarChange();
}
closeYear = function()
{
	popup.close(registry.byId("calYearOnly"));
	var newDateLabel = registry.byId("calYearOnly").value;
	newDateLabel = locale.format(newDateLabel, {
						selector: "date",
						datePattern:"yyyy"
						});
	dojoDisplayDate.set("label", newDateLabel);
	period = "years";
	globalDate = newDateLabel;
	onCalendarChange();
}
toEmail = function()
{
	//alert("To Email");
	//dojo.byId("displayReportCopy").innerHTML = win.body().innerHTML;
	/*var emailData = dom.byId("reportTitle").innerHTML + dom.byId("displayReport").innerHTML;
	//alert(emailData);
	request.post("../mailer/examples/mail2.php",
	{
		//handleAs: "json",
		data:{
			emailData: emailData
			}
	}).then(function(emailStatus)
	{
		//alert(emailStatus);

	});*/
}
toPrint = function()
{
	dijit.byId("leftCol").toggle(); //close the ExpandoPane for center contentPane to fit entire window. LTK 18May21 1537Hrs.
	
	var leftCol = setTimeout(function()
	{
		window.print();	
	},600);//Wait for the panes to resize before printing.
	var leftColTwo = setTimeout(function()
	{
		dijit.byId("leftCol").toggle();	
	},1200);//reset while print dialog is still open.	
}
toPDF = function()
{
	//alert("to PDF");
	/*var pdfData = dom.byId("reportTitle").innerHTML + dom.byId("displayReport").innerHTML;
	//var pdfData = dom.byId("appLayout").innerHTML;
	request.post("lab/toPdf.php",
	{
		//handleAs: "json",
		data:{
			pdfData: pdfData
			}
	}).then(function(pdfName)
	{
		window.open(pdfName);
		//window.location.href = "somepage.php?";
	});*/
}
getBookmarkName = function()
{
	dijit.byId("bookmarkDialog").show();
}
renameBookMark = function()
{
	request.post("layout/delete-bookmark.php",{
	handleAs: "json",
	data: {
		bookMarkId: dom.byId("bookMarkId").innerHTML,
		action: "rename",
		newName: dom.byId('bookmarkRenameInput').value
	}
	}).then(function()
	{
		//bookmark rename message
		bookMarks();
	})
}
toBookmark = function()
{
	dijit.byId("bookmarkDialog").hide();
	var userId = dom.byId('userIdJs').innerHTML;
	//alert("Book Mark: "+ kpiGlobalId + " - " + kpiGlobalType + " - " + mainMenuState + " - " + userId);
	if(kpiGlobalId != undefined && kpiGlobalType != undefined && mainMenuState != undefined && userId != undefined)
	{
		if(kpiGlobalType == "initiative") kpiGlobalId = dom.byId("selectedElement").innerHTML;
		if(kpiGlobalType == "report") kpiGlobalId = selectedReport;
		if(mainMenuState == "Strategy Map") kpiGlobalId = "map";
		if(mainMenuState == "Organizational Structure - Finance") kpiGlobalId = "orgStructure";
		request.post("layout/save-bookmark.php",
		{
			//handleAs: "json",
			data:{
				userId: userId,
				kpiGlobalId: kpiGlobalId,
				kpiGlobalType: kpiGlobalType,
				mainMenuState: mainMenuState,
				bookMarkName: dom.byId("bookmarkNameInput").value
				}
		}).then(function()
		{
			//window.open(pdfName);
			//window.location.href = "somepage.php?";
		});
	}
}
/****************************************************************************************
End of menu related functions
*****************************************************************************************/

//dijit.byId("appLayout").destroyRecursive();
// Parse only if widgets don't already exist to prevent duplicate registration
try {
	parser.parse();
} catch(e) {
	console.warn("Parser error (likely duplicate widget registration):", e.message);
	// If parsing fails due to duplicate registration, try to clean up and re-parse
	if(e.message.includes("already registered")) {
		console.log("Attempting to clean up duplicate widgets and re-parse...");
		// Additional cleanup if needed
		setTimeout(function() {
			try {
				parser.parse();
			} catch(e2) {
				console.error("Second parse attempt failed:", e2.message);
			}
		}, 100);
	}
}

var setClickHandler = function(item){
	item.on("click", onItemSelect);
};
registry.byClass("dijit.MenuItem").forEach(setClickHandler);
registry.byClass("dijit.MenuBarItem").forEach(setClickHandler);

savePdp = function(edit, id)
{
	if(edit == "Delete" || pdpEdit == 'Edit')
	{
		if(edit == "Delete")
		{
			pdpEdit = "Delete";
			pdpId = id;
		}
		else pdpId = pdpEditId;
		//console.log("edit = " + edit + " pdpEdit " + pdpEdit + " id = " + pdpId);
		request.post("individual/save-pdp.php",{
		//handleAs: "json",
		data: {
				userId : tnAdd,
				toEdit: pdpEdit,
				pdpId: pdpId,
				pdpSkillGapInput : dom.byId("pdpSkillGapInput").value,
				pdpInterventionInput : dom.byId("pdpInterventionInput").value,
				pdpCommentsInput : dom.byId("pdpCommentsInput").value,
				pdpResourceInput : dom.byId("pdpResourceInput").value,
				pdpStartInput : dom.byId("pdpStartInput").value,
				pdpDueInput : dom.byId("pdpDueInput").value,
				pdpCompleteInput : dom.byId("pdpCompleteInput").value
		}
		}).then(function(){
			pdpEdit = null;
			dom.byId("initMsgContent").innerHTML = "Personal Development Plan Has Been Updated Created";
			domStyle.set(dom.byId("initMsgContent"), 'display', 'block');
			var msgTimeout = setTimeout(function(){
					domStyle.set(dom.byId("initMsgContent"), 'display', 'none');
			},2000);
		});
	}
	else
	{
		request.post("individual/save-pdp.php",{
		//handleAs: "json",
		data: {
				userId : tnAdd,
				toEdit: pdpEdit,
				//pdpId: pdpId,
				pdpSkillGapInput : dom.byId("pdpSkillGapInput").value,
				pdpInterventionInput : dom.byId("pdpInterventionInput").value,
				pdpCommentsInput : dom.byId("pdpCommentsInput").value,
				pdpResourceInput : dom.byId("pdpResourceInput").value,
				pdpStartInput : dom.byId("pdpStartInput").value,
				pdpDueInput : dom.byId("pdpDueInput").value,
				pdpCompleteInput : dom.byId("pdpCompleteInput").value
		}
		}).then(function(){
			pdpEdit = null;
			dom.byId("initMsgContent").innerHTML = "Personal Development Plan Has Been Successfully Created";
			domStyle.set(dom.byId("initMsgContent"), 'display', 'block');
			var msgTimeout = setTimeout(function(){
					domStyle.set(dom.byId("initMsgContent"), 'display', 'none');
			},2000);
		});
	}
}
editPdp = function(id)
{
	pdpEdit = 'Edit';
	pdpEditId = id;
	request.post("individual/get-pdp.php",{
		handleAs: "json",
		data:{
			pdpId: pdpEditId
			}
	}).then(function(pdpData){
		dom.byId("pdpSkillGapInput").value = pdpData.skillGap;
		dom.byId("pdpInterventionInput").value = pdpData["intervention"];
		dom.byId("pdpCommentsInput").value = pdpData["comments"];
		dom.byId("pdpResourceInput").value = pdpData["resource"];
		dom.byId("pdpStartInput").value = pdpData["startDate"];
		dom.byId("pdpDueInput").value = pdpData["dueDate"];
		dom.byId("pdpCompleteInput").value = pdpData["completionDate"];
	})
	dijit.byId("pdpDialog").show();
}

updateChart = function() /*** #scorecardMap ***/
{
	switch(kpiGlobalType)
		{
			case "measure":
			{
					domStyle.set(dom.byId("chartDiv"), "display", 'block');
					domStyle.set(dom.byId("divChart"), "display", "block");
					request.post("scorecards/get-kpi-gauge.php",
					{
						handleAs: "json",
						data:
						{
							objectId: kpiGlobalId,
							objectType: kpiGlobalType,
							objectPeriod: period,
							objectDate: globalDate
						}
					}).then(function(kpiGauge)
					{
						if(kpiGauge.gaugeType == "goalOnly")
						{
								domStyle.set(dom.byId("divGauge"), "display", "block");
								gauge.yAxis[0].removePlotBand('red');
								gauge.yAxis[0].removePlotBand('yellow');
								gauge.yAxis[0].removePlotBand('green');
								gauge.yAxis[0].removePlotBand('blue');
								gauge.yAxis[0].removePlotBand('darkGreen');
								gauge.yAxis[0].addPlotBand({
									color: '#ff0000',//red
									from: 0,
									to: 5,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'red'
								});
								gauge.yAxis[0].addPlotBand({
									color: '#33CC00',//green
									from: 5,
									to: 10,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'green'
								});
								if(kpiGauge.score == 'No Score')
								{
									//console.log('Type: '+ kpiGauge.gaugeType + ', Score: ' + kpiGauge.score)
									gauge.series[0].points[0].update(null);
									gauge.series[0].options.dial.radius = 0;
									gauge.series[0].isDirty = true;
									gauge.series[0].update({name:kpiGauge.kpiName}, true);
									gauge.redraw();
								}
								else
								{
									//console.log('Type: '+ kpiGauge.gaugeType + ', Score: ' + kpiGauge.score)
									gauge.series[0].options.dial.radius = '100%';
									gauge.series[0].isDirty = true;
									gauge.redraw();
									var score = parseFloat(kpiGauge.score);
									score = Math.round(score * 100) / 100;
									gauge.series[0].points[0].update(score);
									gauge.series[0].update({name:kpiGauge.kpiName}, true);
								}
						}
						else if(kpiGauge.gaugeType == "threeColor")
						{
								domStyle.set(dom.byId("divGauge"), "display", "block");
								gauge.yAxis[0].removePlotBand('red');
								gauge.yAxis[0].removePlotBand('yellow');
								gauge.yAxis[0].removePlotBand('green');
								gauge.yAxis[0].removePlotBand('blue');
								gauge.yAxis[0].removePlotBand('darkGreen');
								gauge.yAxis[0].addPlotBand({
									color: '#ff0000',//red
									from: 0,
									to: 3.33,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'red'
								});
								gauge.yAxis[0].addPlotBand({
									color: '#FFD900',//yellow
									from: 3.33,
									to: 6.67,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'yellow'
								});
								gauge.yAxis[0].addPlotBand({
									color: '#33CC00',//green
									from: 6.67,
									to: 10,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'green'
								});
								if(kpiGauge.score == 'No Score')
								{
									//console.log('Type: '+ kpiGauge.gaugeType + ', Score: ' + kpiGauge.score)
									gauge.series[0].points[0].update(null);
									gauge.series[0].options.dial.radius = 0;
									gauge.series[0].isDirty = true;
									gauge.series[0].update({name:kpiGauge.kpiName}, true);
									gauge.redraw();
								}
								else
								{
									//console.log('Type: '+ kpiGauge.gaugeType + ', Score: ' + kpiGauge.score)
									gauge.series[0].options.dial.radius = '100%';
									gauge.series[0].isDirty = true;
									gauge.redraw();
									var score = parseFloat(kpiGauge.score);
									score = Math.round(score * 100) / 100;
									gauge.series[0].points[0].update(score);
									gauge.series[0].update({name:kpiGauge.kpiName}, true);
								}
						}
						else if(kpiGauge.gaugeType == "fourColor")
						{
							domStyle.set(dom.byId("divGauge"), "display", "block");
								gauge.yAxis[0].removePlotBand('red');
								gauge.yAxis[0].removePlotBand('yellow');
								gauge.yAxis[0].removePlotBand('green');
								gauge.yAxis[0].removePlotBand('blue');
								gauge.yAxis[0].removePlotBand('darkGreen');
								gauge.yAxis[0].addPlotBand({
									color: '#ff0000',//red
									from: 0,
									to: 2.5,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'red'
								});
								gauge.yAxis[0].addPlotBand({
									color: '#FFD900',//yellow
									from: 2.25,
									to: 5,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'yellow'
								});
								gauge.yAxis[0].addPlotBand({
									color: '#33CC00',//green
									from: 5,
									to: 7.5,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'green'
								});
								gauge.yAxis[0].addPlotBand({
									color: '#006600',//darkGreen
									from: 7.5,
									to: 10,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'darkGreen'
								});
								if(kpiGauge.score == 'No Score')
								{
									//console.log('Type: '+ kpiGauge.gaugeType + ', Score: ' + kpiGauge.score)
									gauge.series[0].points[0].update(null);
									gauge.series[0].options.dial.radius = 0;
									gauge.series[0].isDirty = true;
									gauge.series[0].update({name:kpiGauge.kpiName}, true);
									gauge.redraw();
								}
								else
								{
									//console.log('Type: '+ kpiGauge.gaugeType + ', Score: ' + kpiGauge.score)
									gauge.series[0].options.dial.radius = '100%';
									gauge.series[0].isDirty = true;
									gauge.redraw();
									var score = parseFloat(kpiGauge.score);
									score = Math.round(score * 100) / 100;
									gauge.series[0].points[0].update(score);
									gauge.series[0].update({name:kpiGauge.kpiName}, true);
								}
						}
						else if(kpiGauge.gaugeType == "fiveColor")
						{
							domStyle.set(dom.byId("divGauge"), "display", "block");
								gauge.yAxis[0].removePlotBand('red');
								gauge.yAxis[0].removePlotBand('yellow');
								gauge.yAxis[0].removePlotBand('green');
								gauge.yAxis[0].removePlotBand('blue');
								gauge.yAxis[0].removePlotBand('darkGreen');
								gauge.yAxis[0].addPlotBand({
									color: '#ff0000',//red
									from: 0,
									to: 2,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'red'
								});
								gauge.yAxis[0].addPlotBand({
									color: '#FFD900',//yellow
									from: 2,
									to: 4,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'yellow'
								});
								gauge.yAxis[0].addPlotBand({
									color: '#33CC00',//green
									from: 4,
									to: 6,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'green'
								});
								gauge.yAxis[0].addPlotBand({
									color: '#006600',//darkGreen
									from: 6,
									to: 8,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'darkGreen'
								});
								gauge.yAxis[0].addPlotBand({
									color: '#0000FF',//blue
									from: 8,
									to: 10,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'blue'
								});
								if(kpiGauge.score == 'No Score')
								{
									gauge.series[0].points[0].update(null);
									gauge.series[0].options.dial.radius = 0;
									gauge.series[0].isDirty = true;
									gauge.series[0].update({name:kpiGauge.kpiName}, true);
									gauge.redraw();
								}
								else
								{
									//console.log('Type: '+ kpiGauge.gaugeType + ', Score: ' + kpiGauge.score)
									gauge.series[0].options.dial.radius = '100%';
									gauge.series[0].isDirty = true;
									gauge.redraw();
									var score = parseFloat(kpiGauge.score);
									score = Math.round(score * 100) / 100;
									gauge.series[0].points[0].update(score);
									gauge.series[0].update({name:kpiGauge.kpiName}, true);
								}
						}
						gaugeType = kpiGauge.gaugeType;
					});
					//domStyle.set(dom.byId("myGauge3"), "display", "none");
					//alert(gaugeType);
				/*Hide or Show the datalabels when valuesCount increases or decreases to avoid overlapping labels*/
				if(valuesCount > 12)
				{
					chart.series[0].update(
					{
						dataLabels:{
							enabled:false
						}
					},true);
				}
				else
				{
					chart.series[0].update(
					{
						dataLabels:{
							enabled:true
						}
					},true);
				}
				if(chartType == "XmR")
				{
					request.post("scorecards/get-XmR-data.php",{
					handleAs: "json",
					data: {
						objectId: kpiGlobalId,
						objectDate: globalDate,
						objectType: kpiGlobalName,
						objectPeriod: period,
						valuesCount: valuesCount
					}
					}).then(function(XmRData)
					{
						var categories = [], kpiScore = [], scoreCount, range = [], centralLine = [], unpl = [], lnpl = [];
						scoreCount = XmRData.length-1;
						while(scoreCount >= 0)
						{
							if(XmRData[scoreCount].actual == null)
							{
								kpiScore[scoreCount] = null
							}
							else
							{
								categories[scoreCount] = XmRData[scoreCount].date
								kpiScore[scoreCount] = parseFloat(XmRData[scoreCount].actual);
								//kpiScore[scoreCount] = {name: XmRData[scoreCount].date, y: parseFloat(XmRData[scoreCount].actual,10) };
							}
							unpl[scoreCount] = XmRData[scoreCount].unpl;
							lnpl[scoreCount] = XmRData[scoreCount].lnpl;
							range[scoreCount] = [XmRData[scoreCount].lnpl, XmRData[scoreCount].unpl];
							centralLine[scoreCount] = [XmRData[scoreCount].date, XmRData[scoreCount].centralLine];
							//unpl[scoreCount] = XmRData[scoreCount].unpl;
							scoreCount--;
						}
						var yMaximum = Math.max.apply(null, unpl);
						var yMaximumTemp = Math.max.apply(null, kpiScore);
						if(yMaximumTemp > yMaximum) yMaximum = yMaximumTemp;

						var yMinimum = Math.min.apply(null, lnpl);
						var yMinimumTemp = Math.min.apply(null, kpiScore);
						if(yMinimumTemp < yMinimum) yMinimum = yMinimumTemp;

						var xMaximum = XmRData.length - 1.5;
						chart.yAxis[0].setExtremes(yMinimum,yMaximum);
						chart.xAxis[0].setExtremes(0.5,xMaximum);
						chart.yAxis[0].plotLinesAndBands[0].svgElem.hide();
						chart.yAxis[0].plotLinesAndBands[1].svgElem.hide();
						chart.yAxis[0].plotLinesAndBands[2].svgElem.hide();
						//chart.xAxis[0].setCategories(categories, false);
						chart.series[1].hide();//Red Line = lnpl
						chart.series[2].hide();//Yellow Line = centralLine
						chart.series[3].hide();//Green = unpl
						chart.series[4].hide();//Dark Green
						chart.series[5].show();//Central Line
						chart.series[6].show();//Range
						chart.series[7].hide();//Blue
						chart.yAxis[0].plotLinesAndBands[0].svgElem.hide();
						chart.yAxis[0].plotLinesAndBands[1].svgElem.hide();
						chart.yAxis[0].plotLinesAndBands[2].svgElem.hide();

						chart.series[5].update(
						{
							data: centralLine,
							name: 'central line',
						},false);
						chart.series[6].update({
							data: range,
							name: 'range',
						},false);
						chart.xAxis[0].setCategories(categories, false);
						chart.series[0].update(
						{
							tooltip:{
								//valueSuffix: '',
								//valueDecimals: 2
								crosshairs: true,
								shared: true,
								//useHTML: true,
								//headerFormat: '<small>{point.key}</small><table>',
								//pointFormat: '<tr><td style="color: {series.color};"><b>-></b></td><td>{series.name}:</td>' +
								//pointFormat: '<tr><td>{series.name}: &bull; (circular bullet) &raquo; (two greater thans) &rArr; (double arrow) &radic; (tick)</td>' +
								//'<td style="text-align: right"><b>{point.y}</b></td></tr>',
								//footerFormat: '</table>'
							},
							data: kpiScore,
							name: 'kpi'
						},true);
						//chart.series[0].setData(kpiScore,true);

						if(XmRData.length <= 3)
						chart.showNoData("A minimum of 4 points are needed to compute and display XmR Charts.<br>The XmR Chart will display when data captured reaches this number of data points.<br><br>Reference:  http://staceybarr.com/measure-up/three-things-you-need-on-every-kpi-graph/");
					});
				}
				else
				{
					//alert("kpi id: "+objectId+"kpi type: "+objectType+" period: "+period+" kpi date: "+globalDate);
					request.post("scorecards/get-kpi-scores.php",{
					handleAs: "json",
					data: {
						objectId: kpiGlobalId,
						objectType: kpiGlobalType,
						objectPeriod: period,
						objectDate: globalDate,
						valuesCount: valuesCount,
						previousPeriod: 'False'
					}
					}).then(function(kpiData)
					{
						//console.log(JSON.stringify(kpiData));
						var categories = [], kpiScore = [], kpiScoreLimit = [], scoreCount = 0, kpiRed = [], kpiGreen = [], kpiDarkGreen = [], kpiBlue = [], kpiYellow = [], kpiLimit = [], nullCounter = 0;
						lowerLimit = [];
						upperLimit = [];
						while(scoreCount < kpiData.length)
						{
							categories[scoreCount] = kpiData[scoreCount].date;
							if(kpiData[scoreCount].actual == null)
							{
								kpiScore[scoreCount] = null;
								kpiScoreLimit[scoreCount] = null;
								nullCounter++;
							}
							else
							{
								kpiScore[scoreCount] = parseFloat(kpiData[scoreCount].actual);
								kpiScoreLimit[scoreCount] = parseFloat(kpiData[scoreCount].actual);
							}
							switch(kpiData[0].gaugeType)
							{
							case 'goalOnly':
							{
								if(kpiData[scoreCount].green < 0)
								{
									if(kpiData[scoreCount].green*2 < greenLimit) greenLimit = kpiData[scoreCount].green*2;

									kpiRed[scoreCount] = [0, kpiData[scoreCount].green];
									lowerLimit[scoreCount] = kpiData[scoreCount].green * 2;
									kpiGreen[scoreCount] = [kpiData[scoreCount].green, lowerLimit[scoreCount]];
									//lines below add color backgrounds to the last point on chart
									kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, lowerLimit[kpiData.length-1]];
									kpiRed[kpiData.length] = [0, kpiData[kpiData.length-1].green];
								}
								else
								{
									if(kpiData[scoreCount].green*2 > greenLimit) greenLimit = kpiData[scoreCount].green*2;

									kpiRed[scoreCount] = [0, kpiData[scoreCount].green];
									lowerLimit[scoreCount] = [0, kpiData[scoreCount].green];
									upperLimit[scoreCount] = kpiData[scoreCount].green * 2;
									kpiGreen[scoreCount] = [kpiData[scoreCount].green, upperLimit[scoreCount]];
									//console.log("red = "+ kpiRed[scoreCount] + " upper = " + upperLimit[scoreCount] + " green = " + kpiGreen[scoreCount]);
									//lines below add color backgrounds to the last point on chart
									kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, upperLimit[kpiData.length-1]];
									kpiRed[kpiData.length] = [0, kpiData[kpiData.length-1].green];
								}
								break;
							}
							case 'threeColor':
							{
								if(kpiData[scoreCount].green < 0 && kpiData[scoreCount].red  > 0)
								{
									if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
									else redLimit = kpiData[scoreCount].red;

									if(kpiData[scoreCount].green < greenLimit) greenLimit = kpiData[scoreCount].green;
									else greenLimit = kpiData[scoreCount].green;

									lowerLimit[scoreCount] = Math.abs(kpiData[scoreCount].green - kpiData[scoreCount].red);
									lowerLimit[scoreCount] = kpiData[scoreCount].green - lowerLimit[scoreCount];
									kpiGreen[scoreCount] = [lowerLimit[scoreCount], kpiData[scoreCount].green];
									kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];

									upperLimit[scoreCount] = Math.abs(kpiData[scoreCount].green - kpiData[scoreCount].red);
									upperLimit[scoreCount] = kpiData[scoreCount].red + upperLimit[scoreCount];
									kpiRed[scoreCount] = [kpiData[scoreCount].red, upperLimit[scoreCount]];
									//lines below add color backgrounds to the last point on chart
									kpiGreen[kpiData.length] = [lowerLimit[kpiData.length-1], kpiData[kpiData.length-1].green];
									kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, upperLimit[kpiData.length-1]];
									kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];

								}
								else if(kpiData[scoreCount].red > kpiData[scoreCount].green)
								{
									//if(kpiData[scoreCount].green < 0 && kpiData[scoreCount].red  < 0)
									//{
										var layerSize = kpiData[scoreCount].red - kpiData[scoreCount].green;
										redLimit = kpiData[scoreCount].red;
										greenLimit = kpiData[scoreCount].green;
										
										upperLimit[scoreCount] = kpiData[scoreCount].red + layerSize;
										kpiRed[scoreCount] = [kpiData[scoreCount].red, upperLimit[scoreCount]];
										kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];
										lowerLimit[scoreCount] = kpiData[scoreCount].green - layerSize;
										
										kpiGreen[scoreCount] = [lowerLimit[scoreCount], kpiData[scoreCount].green];
										//lines below add color backgrounds to the last point on chart
										kpiGreen[kpiData.length] = [lowerLimit[kpiData.length-1], kpiData[kpiData.length-1].green];
										kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, upperLimit[kpiData.length-1]];
										kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];
										
										//console.log("UpperLimit = "+ upperLimit[scoreCount] + "; Red = " + kpiRed[scoreCount] + "; Yellow = " + kpiYellow[scoreCount] + "; Green = " + kpiGreen[scoreCount] + "; Actual = " + kpiData[scoreCount].actual + "; Lowerlimit = " + lowerLimit[scoreCount] + "; Layersize = " + layerSize + "; RedLimit = " + redLimit + "; GreenLimit = " + greenLimit);

									//}
								}
								else if(kpiData[scoreCount].green < 0 && kpiData[scoreCount].red  < 0 && kpiData[scoreCount].red < kpiData[scoreCount].green)
								{
									if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
									else redLimit = kpiData[scoreCount].red;

									if(kpiData[scoreCount].green < greenLimit) greenLimit = kpiData[scoreCount].green;
									else greenLimit = kpiData[scoreCount].green;

									lowerLimit[scoreCount] = kpiData[scoreCount].green + kpiData[scoreCount].red;
									lowerLimit[scoreCount] = lowerLimit[scoreCount] + kpiData[scoreCount].red;
									kpiRed[scoreCount] = [kpiData[scoreCount].red, lowerLimit[scoreCount]];
									kpiYellow[scoreCount] = [kpiData[scoreCount].green,kpiData[scoreCount].red];

									upperLimit[scoreCount] = kpiData[scoreCount].green + kpiData[scoreCount].red;
									upperLimit[scoreCount] = kpiData[scoreCount].green - upperLimit[scoreCount];
									kpiGreen[scoreCount] = [kpiData[scoreCount].green,upperLimit[scoreCount]];
									//lines below add color backgrounds to the last point on chart
									kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, upperLimit[kpiData.length-1]];
									kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, lowerLimit[kpiData.length-1]];
									kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].red];
								}
								else
								{
									if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
									else redLimit = kpiData[scoreCount].red;

									if(kpiData[scoreCount].green < greenLimit) greenLimit = kpiData[scoreCount].green;
									else greenLimit = kpiData[scoreCount].green;
									
									lowerLimit[scoreCount] = kpiData[scoreCount].green - kpiData[scoreCount].red;
									lowerLimit[scoreCount] = kpiData[scoreCount].red - lowerLimit[scoreCount];
									kpiRed[scoreCount] = [kpiData[scoreCount].red, lowerLimit[scoreCount]]
									kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];

									upperLimit[scoreCount] = kpiData[scoreCount].green - kpiData[scoreCount].red;
									upperLimit[scoreCount] = kpiData[scoreCount].green + upperLimit[scoreCount];
									kpiGreen[scoreCount] = [kpiData[scoreCount].green, upperLimit[scoreCount]];
									
									//lines below add color backgrounds to the last point on chart
									kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, upperLimit[kpiData.length-1]];
									kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, lowerLimit[kpiData.length-1]];
									kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];
									
									//console.log("Yellow: " + kpiYellow[scoreCount] + "; Green: " + kpiGreen[scoreCount] + "; Red: " + kpiRed[scoreCount]);
								}
								break;
							}
							case 'fourColor':
							{
								if(kpiData[scoreCount].green < 0 && kpiData[scoreCount].red  > 0)
								{
									if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
									else redLimit = kpiData[scoreCount].red;

									if(kpiData[scoreCount].darkgreen < greenLimit) greenLimit = kpiData[scoreCount].darkgreen;
									else greenLimit = kpiData[scoreCount].darkgreen;

									lowerLimit[scoreCount] = Math.abs(kpiData[scoreCount].darkgreen - kpiData[scoreCount].red);
									lowerLimit[scoreCount] = kpiData[scoreCount].darkgreen - lowerLimit[scoreCount];
									kpiGreen[scoreCount] = [kpiData[scoreCount].green, kpiData[scoreCount].darkgreen];
									kpiDarkGreen[scoreCount] = [lowerLimit[scoreCount], kpiData[scoreCount].darkgreen];
									kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];

									upperLimit[scoreCount] = Math.abs(kpiData[scoreCount].darkgreen - kpiData[scoreCount].red);
									upperLimit[scoreCount] = kpiData[scoreCount].red + upperLimit[scoreCount];
									kpiRed[scoreCount] = [kpiData[scoreCount].red, upperLimit[scoreCount]];

									//lines below add color backgrounds to the last point on chart
									kpiDarkGreen[kpiData.length] = [lowerLimit[kpiData.length-1], kpiData[kpiData.length-1].darkgreen];
									kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].darkgreen];
									kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, upperLimit[kpiData.length-1]];
									kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];

								}
								else if(kpiData[scoreCount].red > kpiData[scoreCount].green)
								{
									if(kpiData[scoreCount].red > redLimit) redLimit = kpiData[scoreCount].red;
									else redLimit = kpiData[scoreCount].red;

									if(kpiData[scoreCount].darkgreen < greenLimit) greenLimit = kpiData[scoreCount].darkgreen;
									else greenLimit = kpiData[scoreCount].darkgreen;
									
									upperLimit[scoreCount] = kpiData[scoreCount].red - kpiData[scoreCount].darkgreen;
									upperLimit[scoreCount] = kpiData[scoreCount].red + upperLimit[scoreCount];
									kpiRed[scoreCount] = [kpiData[scoreCount].red, upperLimit[scoreCount]];
									kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];
									kpiGreen[scoreCount] = [kpiData[scoreCount].green,kpiData[scoreCount].darkgreen];
									lowerLimit[scoreCount] = kpiData[scoreCount].darkgreen - kpiData[scoreCount].red;
									lowerLimit[scoreCount] = kpiData[scoreCount].darkgreen + lowerLimit[scoreCount];

									kpiDarkGreen[scoreCount] = [upperLimit[scoreCount], kpiData[scoreCount].darkgreen];
									
									//lines below add color backgrounds to the last point on chart
									kpiDarkGreen[kpiData.length] = [upperLimit[kpiData.length-1], kpiData[kpiData.length-1].darkgreen];
									kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].darkgreen];
									kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, upperLimit[kpiData.length-1]];
									kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];
								}
								else if(kpiData[scoreCount].green < 0 && kpiData[scoreCount].red  < 0 && kpiData[scoreCount].red < kpiData[scoreCount].green)
								{
									if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
									else redLimit = kpiData[scoreCount].red;

									if(kpiData[scoreCount].darkgreen < greenLimit) greenLimit = kpiData[scoreCount].darkgreen;
									else greenLimit = kpiData[scoreCount].darkgreen;

									lowerLimit[scoreCount] = kpiData[scoreCount].darkgreen + kpiData[scoreCount].red;
									lowerLimit[scoreCount] = lowerLimit[scoreCount] + kpiData[scoreCount].red;
									kpiRed[scoreCount] = [kpiData[scoreCount].red, lowerLimit[scoreCount]];
									kpiYellow[scoreCount] = [kpiData[scoreCount].green,kpiData[scoreCount].red];
									kpiGreen[scoreCount] = [kpiData[scoreCount].green,kpiData[scoreCount].darkgreen];
									upperLimit[scoreCount] = kpiData[scoreCount].darkgreen + kpiData[scoreCount].red;
									upperLimit[scoreCount] = kpiData[scoreCount].darkgreen - upperLimit[scoreCount];
									kpiDarkGreen[scoreCount] = [kpiData[scoreCount].darkgreen,upperLimit[scoreCount]];

									//lines below add color backgrounds to the last point on chart
									kpiDarkGreen[kpiData.length] = [kpiData[kpiData.length-1].darkgreen, upperLimit[kpiData.length-1]];
									kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].darkgreen];
									kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, lowerLimit[kpiData.length-1]];
									kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].red];
								}
								else
								{
									if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
									else redLimit = kpiData[scoreCount].red;

									if(kpiData[scoreCount].darkgreen < greenLimit) greenLimit = kpiData[scoreCount].darkgreen;
									else greenLimit = kpiData[scoreCount].darkgreen;

									lowerLimit[scoreCount] = kpiData[scoreCount].darkgreen - kpiData[scoreCount].red;
									lowerLimit[scoreCount] = kpiData[scoreCount].red - lowerLimit[scoreCount];
									kpiRed[scoreCount] = [kpiData[scoreCount].red, lowerLimit[scoreCount]]
									kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];
									kpiGreen[scoreCount] = [kpiData[scoreCount].green, kpiData[scoreCount].darkgreen];
									upperLimit[scoreCount] = kpiData[scoreCount].darkgreen - kpiData[scoreCount].red;
									upperLimit[scoreCount] = kpiData[scoreCount].darkgreen + upperLimit[scoreCount];
									kpiDarkGreen[scoreCount] = [kpiData[scoreCount].darkgreen, upperLimit[scoreCount]];

									//lines below add color backgrounds to the last point on chart
									kpiDarkGreen[kpiData.length] = [kpiData[kpiData.length-1].darkgreen, upperLimit[kpiData.length-1]];
									kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].darkgreen];
									kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, lowerLimit[kpiData.length-1]];
									kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];
								}
								break;
							}
							case 'fiveColor':
							{
								if(kpiData[scoreCount].green < 0 && kpiData[scoreCount].red  > 0)
								{
									if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
									else redLimit = kpiData[scoreCount].red;

									if(kpiData[scoreCount].blue < greenLimit) greenLimit = kpiData[scoreCount].blue;
									else greenLimit = kpiData[scoreCount].blue;

									lowerLimit[scoreCount] = Math.abs(kpiData[scoreCount].blue - kpiData[scoreCount].red);
									lowerLimit[scoreCount] = kpiData[scoreCount].blue - lowerLimit[scoreCount];
									kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];
									kpiGreen[scoreCount] = [kpiData[scoreCount].green, kpiData[scoreCount].darkgreen];
									kpiDarkGreen[scoreCount] = [kpiData[scoreCount].darkgreen, kpiData[scoreCount].blue];
									kpiBlue[scoreCount] = [kpiData[scoreCount].blue, lowerLimit[scoreCount]];

									upperLimit[scoreCount] = Math.abs(kpiData[scoreCount].blue - kpiData[scoreCount].red);
									upperLimit[scoreCount] = kpiData[scoreCount].red + upperLimit[scoreCount];
									kpiRed[scoreCount] = [kpiData[scoreCount].red, upperLimit[scoreCount]];

									//lines below add color backgrounds to the last point on chart
									kpiBlue[kpiData.length] = [kpiData[kpiData.length-1].blue, lowerLimit[kpiData.length-1]];
									kpiDarkGreen[kpiData.length] = [kpiData[kpiData.length-1].darkgreen, kpiData[kpiData.length-1].blue];
									kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].darkgreen];
									kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, upperLimit[kpiData.length-1]];
									kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];

								}
								else if(kpiData[scoreCount].red > kpiData[scoreCount].green)
								{
									if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
									else redLimit = kpiData[scoreCount].red;

									if(kpiData[scoreCount].green < greenLimit) greenLimit = kpiData[scoreCount].blue;
									else greenLimit = kpiData[scoreCount].blue;

									lowerLimit[scoreCount] = kpiData[scoreCount].red - kpiData[scoreCount].blue;
									lowerLimit[scoreCount] = kpiData[scoreCount].red + lowerLimit[scoreCount];
									kpiRed[scoreCount] = [kpiData[scoreCount].red, lowerLimit[scoreCount]];

									kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];
									kpiGreen[scoreCount] = [kpiData[scoreCount].green,kpiData[scoreCount].darkgreen];
									kpiDarkGreen[scoreCount] = [kpiData[scoreCount].darkgreen, kpiData[scoreCount].blue];

									upperLimit[scoreCount] = kpiData[scoreCount].blue - kpiData[scoreCount].red;
									upperLimit[scoreCount] = kpiData[scoreCount].blue + upperLimit[scoreCount];
									kpiBlue[scoreCount] = [kpiData[scoreCount].blue, upperLimit[scoreCount]];

									//lines below add color backgrounds to the last point on chart
									kpiBlue[kpiData.length] = [kpiData[kpiData.length-1].blue, upperLimit[kpiData.length-1]];
									kpiDarkGreen[kpiData.length] = [kpiData[kpiData.length-1].darkgreen, kpiData[kpiData.length-1].blue];
									kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].darkgreen];
									kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, lowerLimit[kpiData.length-1]];
									kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];
								}
								else if(kpiData[scoreCount].green < 0 && kpiData[scoreCount].red  < 0 && kpiData[scoreCount].red < kpiData[scoreCount].green)
								{
									if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
									else redLimit = kpiData[scoreCount].red;

									if(kpiData[scoreCount].green < greenLimit) greenLimit = kpiData[scoreCount].blue;
									else greenLimit = kpiData[scoreCount].blue;

									lowerLimit[scoreCount] = kpiData[scoreCount].blue + kpiData[scoreCount].red;
									lowerLimit[scoreCount] = lowerLimit[scoreCount] + kpiData[scoreCount].red;
									kpiRed[scoreCount] = [kpiData[scoreCount].red, lowerLimit[scoreCount]];

									kpiYellow[scoreCount] = [kpiData[scoreCount].green,kpiData[scoreCount].red];
									kpiGreen[scoreCount] = [kpiData[scoreCount].green,kpiData[scoreCount].darkgreen];
									kpiDarkGreen[scoreCount] = [kpiData[scoreCount].darkgreen,kpiData[scoreCount].blue];

									upperLimit[scoreCount] = kpiData[scoreCount].blue + kpiData[scoreCount].red;
									upperLimit[scoreCount] = kpiData[scoreCount].blue - upperLimit[scoreCount];
									kpiBlue[scoreCount] = [kpiData[scoreCount].blue, upperLimit[scoreCount]];

									//lines below add color backgrounds to the last point on chart
									kpiBlue[kpiData.length] = [kpiData[kpiData.length-1].blue, upperLimit[kpiData.length-1]];
									kpiDarkGreen[kpiData.length] = [kpiData[kpiData.length-1].darkgreen, kpiData[kpiData.length-1].blue];
									kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].darkgreen];
									kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, lowerLimit[kpiData.length-1]];
									kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].red];
								}
								else
								{
									if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
									else redLimit = kpiData[scoreCount].red;

									if(kpiData[scoreCount].green < greenLimit) greenLimit = kpiData[scoreCount].blue;
									else greenLimit = kpiData[scoreCount].blue;

									lowerLimit[scoreCount] = kpiData[scoreCount].blue - kpiData[scoreCount].red;
									lowerLimit[scoreCount] = kpiData[scoreCount].red - lowerLimit[scoreCount];
									kpiRed[scoreCount] = [kpiData[scoreCount].red, lowerLimit[scoreCount]]

									kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];
									kpiGreen[scoreCount] = [kpiData[scoreCount].green, kpiData[scoreCount].darkgreen];
									kpiDarkGreen[scoreCount] = [kpiData[scoreCount].darkgreen, kpiData[scoreCount].blue];

									upperLimit[scoreCount] = kpiData[scoreCount].blue - kpiData[scoreCount].red;
									upperLimit[scoreCount] = kpiData[scoreCount].blue + upperLimit[scoreCount];
									kpiBlue[scoreCount] = [kpiData[scoreCount].blue, upperLimit[scoreCount]];

									//lines below add color backgrounds to the last point on chart
									kpiBlue[kpiData.length] = [kpiData[kpiData.length-1].blue, upperLimit[kpiData.length-1]];
									kpiDarkGreen[kpiData.length] = [kpiData[kpiData.length-1].darkgreen, kpiData[kpiData.length-1].blue];
									kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].darkgreen];
									kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, lowerLimit[kpiData.length-1]];
									kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];
								}
								break;
							}
							}//end of color switch
							categories[scoreCount] = kpiData[scoreCount].date;
							scoreCount++;
						}
						//chart.series[0].data.length = 0;
						chart.yAxis[0].plotLinesAndBands[0].svgElem.hide();
						chart.yAxis[0].plotLinesAndBands[1].svgElem.hide();
						chart.yAxis[0].plotLinesAndBands[2].svgElem.hide();
						chart.xAxis[0].setCategories(categories, false);
						if(nullCounter == kpiData.length) chart.showNoData("No Measure Data to Display");
						//console.log('nullCounter ' + nullCounter + ', length ' + kpiData.length);
						if(dataTypeDisplay == 'Percentage')
						{
							chart.series[0].update({
							tooltip:{
								valueSuffix: ' %',
								//valueDecimals: 2
								crosshairs: true,
								shared: true
							},
							data: kpiScore,
							name: kpiGlobalName
							},true);
						}
						else if(dataTypeDisplay == 'Currency')
						{
							chart.series[0].update({
							tooltip:{
								valuePrefix: currency+' ',
								valueDecimals: 2,
								headerFormat: 'Period: {point.key}<br>',
								pointFormat: "Measure: {series.name}<br>Value: <b>{point.y}</b><br/>",
								crosshairs: true,
								shared: true
							},
							data: kpiScore,
							name: kpiGlobalName
							},true);
						}
						else
						{
							chart.series[0].update({
							tooltip:{
								/*useHTML: true,
								headerFormat: '<small>{point.key}</small><table>',
								pointFormat: '<tr><td style="color: {series.color}">{series.name}: </td>' +
									'<td style="text-align: right"><b>{point.y} EUR</b></td></tr>',
								footerFormat: '</table>'*/
								//valuePrefix: currency+' ',
								//valueDecimals: 2,
								headerFormat: 'Period: {point.key}<br>',
								pointFormat: "Measure: {series.name}<br>Value: <b>{point.y}</b><br/>",
								crosshairs: true,
								shared: true
							},
							data: kpiScore,
							name: kpiGlobalName,
							shared: true
							},true);
						}
						switch(kpiData[0].gaugeType)
						{
							case 'goalOnly':
							{
								if(greenLimit < 0)
								{
									chart.yAxis[0].setExtremes(greenLimit, 0);
								}
								else
								{
									chart.yAxis[0].setExtremes(0, greenLimit);
								}
								/****************************************************************************************************
									Check if actual values should form the upper or lower limits respectively
									*/
									//var sasa = Math.min.apply(Math, kpiScoreLimit)
									//var poa = Math.min.apply(null, lowerLimit)
									//console.log ("kpiScoreLimit = " + sasa + " lowerLimit = " + poa);
									if(Math.min.apply(Math, kpiScoreLimit) < Math.min.apply(null, lowerLimit))
									{
										chart.yAxis[0].setExtremes(Math.min.apply(Math, kpiScoreLimit), Math.max.apply(null, upperLimit));
										var size = kpiRed.length;
										for (var i = 0; i < size; i++)
										{
											//added lines above to  add color backgrounds to the last point on chart hence need to reflect added values below for all combinations
											//kpiRed[i] = [parseFloat(kpiData[0].red), Math.min.apply(Math, kpiScoreLimit)];
											kpiRed[i] = [parseFloat(kpiRed[i]), Math.min.apply(Math, kpiScoreLimit)];
										}
									}
									if(Math.max.apply(Math, kpiScoreLimit) > Math.max.apply(null, upperLimit))
									{
										chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(Math, kpiScoreLimit));
										var size = kpiRed.length;
										for (var i = 0; i < size; i++)
										{
											//kpiGreen[i] = [parseFloat(kpiData[0].green), Math.max.apply(Math, kpiScoreLimit)];
											kpiGreen[i] = [parseFloat(kpiGreen[i]), Math.max.apply(Math, kpiScoreLimit)];
										}
									}
									/******************************************************************************************************/

								chart.series[1].show();//Red Line
								chart.series[2].hide();//Yellow Line
								chart.series[3].show();//Green Line
								chart.series[4].hide();//Dark Green Line
								chart.series[5].hide();//centralLine
								chart.series[6].hide();//XmR ranges
								chart.series[7].hide();//Blue
								chart.xAxis[0].setExtremes(0,valuesCount-1);
								chart.series[1].update({
									data: kpiRed,
									name: 'red'
									},false);
								chart.series[3].update({
									data: kpiGreen,
									name: 'green'
									},true);
								redLimit = 0; greenLimit = 0;
								break;
							}
							case 'threeColor':
							{
								//console.log('Lower Limit ' + lowerLimit + ' Upper Limit: '+ upperLimit);
								if(greenLimit < 0 && redLimit > 0)
								{
									chart.series[1].show();//Red Line
									chart.series[2].show();//Yellow Line
									chart.series[3].show();//Green
									chart.series[4].hide();//Dark Green
									chart.series[5].hide();//centralLine
									chart.series[6].hide();//XmR ranges
									chart.series[7].hide();//Blue
									chart.xAxis[0].setExtremes(0,valuesCount-1);
									chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(null, upperLimit));
									/****************************************************************************************************
									Check if actual values should form the upper or lower limits respectively
									*/
									if(Math.min.apply(Math, kpiScoreLimit) < Math.min.apply(null, lowerLimit))
									{
										chart.yAxis[0].setExtremes(Math.min.apply(Math, kpiScoreLimit), Math.max.apply(null, upperLimit));
										var size = kpiRed.length;
										for (var i = 0; i < size; i++)
										{
											//kpiRed[i] = [parseFloat(kpiRed[0].red), Math.min.apply(Math, kpiScoreLimit)];
											kpiRed[i] = [parseFloat(kpiRed[i].red), Math.min.apply(Math, kpiScoreLimit)];
										}
									}
									if(Math.max.apply(Math, kpiScoreLimit) > Math.max.apply(null, upperLimit))
									{
										chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(Math, kpiScoreLimit));
										var size = kpiRed.length;
										for (var i = 0; i < size; i++)
										{
											//kpiGreen[i] = [parseFloat(kpiData[0].green), Math.max.apply(Math, kpiScoreLimit)];
											kpiGreen[i] = [parseFloat(kpiGreen[i]), Math.max.apply(Math, kpiScoreLimit)];
										}
									}
									/******************************************************************************************************/

									chart.series[1].update({
										data: kpiRed,
										name: 'red'
										},false);
									chart.series[2].update({
										data: kpiYellow,
										name: 'yellow'
										},false);
									chart.series[3].update({
										data: kpiGreen,
										name: 'green'
										},true);
								}
								else
								{
									var moja = Math.min.apply(Math, kpiScoreLimit);
									var mbili = Math.min.apply(null, lowerLimit);
									var tatu = Math.max.apply(null, upperLimit);
									//console.log ("kpiScoreLimit = " + moja + "lowerLimit = " + mbili + " upperLimit = " + tatu + " redLimit = " + redLimit + " greenLimit = " + greenLimit);
									
									if(redLimit > greenLimit)
									{
										chart.yAxis[0].setExtremes(Math.max.apply(null, lowerLimit), Math.min.apply(null, upperLimit));
										
										if(Math.min.apply(Math, kpiScoreLimit) < Math.min.apply(null, lowerLimit) && Math.max.apply(Math, kpiScoreLimit) > Math.max.apply(null, upperLimit))
										{
											chart.yAxis[0].setExtremes(Math.min.apply(Math, kpiScoreLimit), Math.max.apply(null, kpiScoreLimit));
											var size = kpiRed.length;
											for(var i = 0; i < size; i++)
											{
												kpiRed[i] = [parseFloat(kpiData[0].red), Math.max.apply(Math, kpiScoreLimit)];
												kpiGreen[i] = [parseFloat(kpiData[0].green), Math.min.apply(Math, kpiScoreLimit)];
											}
										}
										else if(Math.min.apply(Math, kpiScoreLimit) < Math.min.apply(null, lowerLimit))
										{
											chart.yAxis[0].setExtremes(Math.min.apply(Math, kpiScoreLimit), Math.max.apply(null, upperLimit));
											var size = kpiGreen.length;
											for(var i = 0; i < size; i++)
											{
												kpiGreen[i] = [parseFloat(kpiData[0].green), Math.min.apply(Math, kpiScoreLimit)];
											}
										}
										else if(Math.max.apply(Math, kpiScoreLimit) > Math.max.apply(null, upperLimit))
										{
											chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(null, kpiScoreLimit));
											var size = kpiRed.length;
											for(var i = 0; i < size; i++)
											{
												kpiRed[i] = [parseFloat(kpiData[0].red), Math.max.apply(Math, kpiScoreLimit)];
											}
										}
									}
									else
									{
										chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(null, upperLimit));
										/****************************************************************************************************
										Check if actual values should form the upper or lower limits respectively
										*/
										if(Math.min.apply(Math, kpiScoreLimit) < Math.min.apply(null, lowerLimit))
										{
											chart.yAxis[0].setExtremes(Math.min.apply(Math, kpiScoreLimit), Math.max.apply(null, upperLimit));
											var size = kpiRed.length;
											for (var i = 0; i < size; i++)
											{
												//kpiRed[i] = [parseFloat(kpiData[0].red), Math.min.apply(Math, kpiScoreLimit)];
												kpiRed[i] = [parseFloat(kpiRed[i]), Math.min.apply(Math, kpiScoreLimit)];
											}
										}
										if(Math.max.apply(Math, kpiScoreLimit) > Math.max.apply(null, upperLimit))
										{
											chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(Math, kpiScoreLimit));
											var size = kpiRed.length;
											for (var i = 0; i < size; i++)
											{
												//kpiGreen[i] = [parseFloat(kpiData[0].green), Math.max.apply(Math, kpiScoreLimit)];
												kpiGreen[i] = [parseFloat(kpiGreen[i]), Math.max.apply(Math, kpiScoreLimit)];
											}
										}
									}
									/******************************************************************************************************/
				//console.log("Red " + json.stringify(kpiRed));
				//console.log("Yellow " + json.stringify(kpiYellow));
				//console.log("Green " + json.stringify(kpiGreen));
									chart.series[1].show();//Red Line
									chart.series[2].show();//Yellow Line
									chart.series[3].show();//Green
									chart.series[4].hide();//Dark Green
									chart.series[5].hide();//centralLine
									chart.series[6].hide();//XmR ranges
									chart.series[7].hide();//Blue
									chart.xAxis[0].setExtremes(0,valuesCount-1);
									chart.series[1].update({
										data: kpiRed,
										name: 'red',
										//threshold: -10
										},false);
									chart.series[2].update({
										data: kpiYellow,
										name: 'yellow',
										//threshold: -3
										},false);
									chart.series[3].update({
										data: kpiGreen,
										name: 'green'
										},true);
								}
								redLimit = 0; greenLimit = 0;
								break;
							}
							case 'fourColor':
							{
								//console.log("redLimit = " + redLimit + " greenLimit = " + greenLimit);
								if(greenLimit < 0 && redLimit > 0)
								{
									var yMaximum = Math.max.apply(null, upperLimit);
									var yMaximumTemp = Math.max.apply(null, kpiScore);
									if(yMaximumTemp > yMaximum) yMaximum = yMaximumTemp;

									var yMinimum = Math.min.apply(null, lowerLimit);
									var yMinimumTemp = Math.min.apply(null, kpiScore);
									if(yMinimumTemp < yMinimum) yMinimum = yMinimumTemp;

									chart.yAxis[0].setExtremes(yMinimum, yMaximum);
									var size = kpiRed.length;
									for (var i = 0; i < size; i++)
									{
										//if(kpiRed[i] == null) kpiRed[i] = [null, null];
										kpiRed[i] = [parseFloat(kpiData[i].red), yMaximum];
									}
									var size = kpiRed.length;
									for (var i = 0; i < size; i++)
									{
										//if(kpiDarkGreen[i] == null) kpiDarkGreen[i] = [null, null];
										kpiDarkGreen[i] = [parseFloat(kpiData[i].darkgreen), yMinimum];
									}

									chart.series[1].show();//Red Line
									chart.series[2].show();//Yellow Line
									chart.series[3].show();//Green
									chart.series[4].show();//Dark Green
									chart.series[5].hide();//centralLine
									chart.series[6].hide();//XmR ranges
									chart.series[7].hide();//Blue
									chart.xAxis[0].setExtremes(0,valuesCount-1);
									chart.series[1].update({
										data: kpiRed,
										name: 'red'
										},false);
									chart.series[2].update({
										data: kpiYellow,
										name: 'yellow'
										},false);
									chart.series[3].update({
										data: kpiGreen,
										name: 'green'
										},false);
									chart.series[4].update({
										data: kpiDarkGreen,
										name: 'darkGreen'
										},true);
								}
								else
								{
									if(redLimit > greenLimit)
									{
										var yMaximum = Math.max.apply(null, upperLimit);
										var yMaximumTemp = Math.max.apply(null, kpiScore);
										if(yMaximumTemp > yMaximum) yMaximum = yMaximumTemp;

										var yMinimum = Math.min.apply(null, lowerLimit);
										var yMinimumTemp = Math.min.apply(null, kpiScore);
										if(yMinimumTemp < yMinimum) yMinimum = yMinimumTemp;
										
										console.log("yMinimum = " + yMinimum + " yMaximum = " + yMaximum);

										chart.yAxis[0].setExtremes(yMinimum, yMaximum);
										var size = kpiRed.length;
										for (var i = 0; i < size; i++)
										{
											//kpiRed[i] = [parseFloat(kpiData[i].red), yMaximum];
											kpiRed[i] = [parseFloat(kpiRed[i]), yMaximum];
										}
										var size = kpiRed.length;
										for (var i = 0; i < size; i++)
										{
											//kpiDarkGreen[i] = [parseFloat(kpiData[i].darkgreen), yMinimum];
											kpiDarkGreen[i] = [parseFloat(kpiDarkGreen[i]), yMinimum];
										}
									}
									else
									{
										var yMaximum = Math.max.apply(null, upperLimit);
										var yMaximumTemp = Math.max.apply(null, kpiScore);
										if(yMaximumTemp > yMaximum) yMaximum = yMaximumTemp;

										var yMinimum = Math.min.apply(null, lowerLimit);
										var yMinimumTemp = Math.min.apply(null, kpiScore);
										if(yMinimumTemp < yMinimum) yMinimum = yMinimumTemp;

										chart.yAxis[0].setExtremes(yMinimum, yMaximum);
										var size = kpiRed.length;
										for (var i = 0; i < size; i++)
										{
											//kpiRed[i] = [parseFloat(kpiData[i].red), yMinimum];
											kpiRed[i] = [parseFloat(kpiRed[i]), yMinimum];
										}
										var size = kpiRed.length;
										for (var i = 0; i < size; i++)
										{
											//kpiDarkGreen[i] = [parseFloat(kpiData[i].darkgreen), yMaximum];
											kpiDarkGreen[i] = [parseFloat(kpiDarkGreen[i]), yMaximum];
										}
									}

									chart.series[1].show();//Red Line
									chart.series[2].show();//Yellow Line
									chart.series[3].show();//Green
									chart.series[4].show();//Dark Green
									chart.series[5].hide();//centralLine
									chart.series[6].hide();//XmR ranges
									chart.series[7].hide();//Blue
									chart.xAxis[0].setExtremes(0,valuesCount-1);
									chart.series[1].update({
										data: kpiRed,
										name: 'red',
										//threshold: -10
										},false);
									chart.series[2].update({
										data: kpiYellow,
										name: 'yellow',
										//threshold: -3
										},false);
									chart.series[3].update({
										data: kpiGreen,
										name: 'green'
										},false);
									chart.series[4].update({
										data: kpiDarkGreen,
										name: 'darkGreen'
										},true);
								}
								//console.log(JSON.stringify(kpiRed));
								redLimit = 0; greenLimit = 0;
								break;
							}
							case 'fiveColor':
							{
								if(greenLimit < 0 && redLimit > 0)
								{
									chart.series[1].show();//Red Line
									chart.series[2].show();//Yellow Line
									chart.series[3].show();//Green
									chart.series[4].show();//Dark Green
									chart.series[5].hide();//centralLine
									chart.series[6].hide();//XmR ranges
									chart.series[7].show();//Blue
									chart.xAxis[0].setExtremes(0,valuesCount-1);
									chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(null, upperLimit));
									/****************************************************************************************************
									Check if actual values should form the upper or lower limits respectively
									*/
									if(Math.min.apply(Math, kpiScoreLimit) < Math.min.apply(null, lowerLimit))
									{
										chart.yAxis[0].setExtremes(Math.min.apply(Math, kpiScoreLimit), Math.max.apply(null, upperLimit));
										var size = kpiRed.length;
										for (var i = 0; i < size; i++)
										{
											//kpiRed[i] = [parseFloat(kpiData[0].red), Math.min.apply(Math, kpiScoreLimit)];
											kpiRed[i] = [parseFloat(kpiRed[i]), Math.min.apply(Math, kpiScoreLimit)];
										}
									}
									if(Math.max.apply(Math, kpiScoreLimit) > Math.max.apply(null, upperLimit))
									{
										chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(Math, kpiScoreLimit));
										var size = kpiRed.length;
										for (var i = 0; i < size; i++)
										{
											//kpiBlue[i] = [parseFloat(kpiData[0].blue), Math.max.apply(Math, kpiScoreLimit)];
											kpiBlue[i] = [parseFloat(kpiBlue[i]), Math.max.apply(Math, kpiScoreLimit)];
										}
									}
									/******************************************************************************************************/

									chart.series[1].update({
										data: kpiRed,
										name: 'red'
										},false);
									chart.series[2].update({
										data: kpiYellow,
										name: 'yellow'
										},false);
									chart.series[3].update({
										data: kpiGreen,
										name: 'green'
										},false);
									chart.series[4].update({
										data: kpiDarkGreen,
										name: 'darkGreen'
										},false);
									chart.series[7].update({
										data: kpiBlue,
										name: 'blue'
										},true);
								}
								else
								{
									if(redLimit > greenLimit)
									chart.yAxis[0].setExtremes(Math.max.apply(null, upperLimit), Math.min.apply(null, lowerLimit));
									else
									chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(null, upperLimit));
									/****************************************************************************************************
									Check if actual values should form the upper or lower limits respectively
									*/
									if(Math.min.apply(Math, kpiScoreLimit) < Math.min.apply(null, lowerLimit))
									{
										chart.yAxis[0].setExtremes(Math.min.apply(Math, kpiScoreLimit), Math.max.apply(null, upperLimit));
										var size = kpiRed.length;
										for (var i = 0; i < size; i++)
										{
											//kpiRed[i] = [parseFloat(kpiData[0].red), Math.min.apply(Math, kpiScoreLimit)];
											kpiRed[i] = [parseFloat(kpiRed[i]), Math.min.apply(Math, kpiScoreLimit)];
										}
									}
									if(Math.max.apply(Math, kpiScoreLimit) > Math.max.apply(null, upperLimit))
									{
										chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(Math, kpiScoreLimit));
										var size = kpiRed.length;
										for (var i = 0; i < size; i++)
										{
											//kpiBlue[i] = [parseFloat(kpiData[0].blue), Math.max.apply(Math, kpiScoreLimit)];
											kpiBlue[i] = [parseFloat(kpiBlue[i]), Math.max.apply(Math, kpiScoreLimit)];
										}
									}
									/******************************************************************************************************/

									chart.series[1].show();//Red Line
									chart.series[2].show();//Yellow Line
									chart.series[3].show();//Green
									chart.series[4].show();//Dark Green
									chart.series[5].hide();//centralLine
									chart.series[6].hide();//XmR ranges
									chart.series[7].show();//Blue
									chart.xAxis[0].setExtremes(0,valuesCount-1);
									chart.series[1].update({
										data: kpiRed,
										name: 'red',
										//threshold: -10
										},false);
									chart.series[2].update({
										data: kpiYellow,
										name: 'yellow',
										//threshold: -3
										},false);
									chart.series[3].update({
										data: kpiGreen,
										name: 'green'
										},false);
									chart.series[4].update({
										data: kpiDarkGreen,
										name: 'darkGreen'
										},false);
									chart.series[7].update({
										data: kpiBlue,
										name: 'blue'
										},true);
								}
								redLimit = 0; greenLimit = 0;
								break;
							}
						}
						chart.redraw();
					});
				}//end of 9Steps Chart type
				break;
			}
			case "objective":
			{
					domStyle.set(dom.byId("chartDiv"), "display", 'block');
					domStyle.set(dom.byId("divChart"), "display", "block");
					//togglerMeasures.show();
					request.post("scorecards/get-obj-scores.php",{
					handleAs: "json",
					data: {
						objectId: kpiGlobalId,
						objectType: kpiGlobalType,
						objectPeriod: period,
						objectDate: globalDate,
						valuesCount: valuesCount
					}
					}).then(function(objectiveData)
					{
						//console.log(JSON.stringify(objectiveData));
						var categories = [], objectiveScore = [], scoreCount = 0;
						while(scoreCount < objectiveData.length)
						{
							categories[scoreCount] = objectiveData[scoreCount].date
							//objectiveScore[scoreCount] = {name: objectiveData[scoreCount].date, y: parseFloat(objectiveData[scoreCount].score,10) };
							if(objectiveData[scoreCount].score == null) objectiveScore[scoreCount] = null
							else
							objectiveScore[scoreCount] = parseFloat(objectiveData[scoreCount].score);
							//categories[scoreCount] = objectiveData[scoreCount].date;
							scoreCount++;
						}
						chart.yAxis[0].plotLinesAndBands[0].svgElem.show();
						chart.yAxis[0].plotLinesAndBands[1].svgElem.show();
						chart.yAxis[0].plotLinesAndBands[2].svgElem.show();
						chart.xAxis[0].setCategories(categories, false);
						chart.yAxis[0].setExtremes(0,10);
						chart.xAxis[0].setExtremes(0,valuesCount-1);
						chart.series[1].hide();
						chart.series[2].hide();
						chart.series[3].hide();
						chart.series[4].hide();
						chart.series[5].hide();
						chart.series[0].setData(objectiveScore, true);
					});
					request.post("scorecards/get-obj-gauge.php",
					 {
						handleAs: "json",
						data: {
							objectId: kpiGlobalId,
							objectType: kpiGlobalType,
							objectPeriod: period,
							objectDate: globalDate
						}
						}).then(function(objGauge)
						{
							//console.log('Objective score: '+objGauge);
							domStyle.set(dom.byId("divGauge"), "display", "block");
							gauge.yAxis[0].removePlotBand('red');
							gauge.yAxis[0].removePlotBand('yellow');
							gauge.yAxis[0].removePlotBand('green');
							gauge.yAxis[0].removePlotBand('blue');
							gauge.yAxis[0].removePlotBand('darkGreen');
							gauge.yAxis[0].addPlotBand({
								color: '#ff0000',//red
								from: 0,
								to: 3.33,
								outerRadius: '100%',
								innerRadius: '1%',
								zIndex:-100,
								id: 'red'
							});
							gauge.yAxis[0].addPlotBand({
								color: '#FFD900',//yellow
								from: 3.33,
								to: 6.67,
								outerRadius: '100%',
								innerRadius: '1%',
								zIndex:-100,
								id: 'yellow'
							});
							gauge.yAxis[0].addPlotBand({
								color: '#33CC00',//green
								from: 6.67,
								to: 10,
								outerRadius: '100%',
								innerRadius: '1%',
								zIndex:-100,
								id: 'green'
							});
							if(objGauge == 'No Score')
							{
								//console.log('Score: ' + objGauge)
								gauge.series[0].points[0].update(null);
								gauge.series[0].options.dial.radius = 0;
								gauge.series[0].isDirty = true;
								gauge.redraw();
							}
							else
							{
								//console.log('Score: ' + objGauge)
								gauge.series[0].options.dial.radius = '100%';
								gauge.series[0].isDirty = true;
								gauge.redraw();
								var score = parseFloat(objGauge);
								score = Math.round(score * 100) / 100;
								gauge.series[0].points[0].update(score);
							}
						});
				break;
			}
			case "perspective":
			{
				domStyle.set(dom.byId("chartDiv"), "display", 'block');
				domStyle.set(dom.byId("divChart"), "display", "block");
				request.post("scorecards/get-persp-scores.php",{
				handleAs: "json",
				data: {
					objectId: kpiGlobalId,
					objectType: kpiGlobalType,
					objectPeriod: period,
					objectDate: globalDate,
					valuesCount: valuesCount
				}
				}).then(function(perspectiveData)
					{
						var categories = [], perspScore = [], scoreCount = 0;
						while(scoreCount < perspectiveData.length)
						{
							categories[scoreCount] = perspectiveData[scoreCount].date
							//objectiveScore[scoreCount] = {name: objectiveData[scoreCount].date, y: parseFloat(objectiveData[scoreCount].score,10) };
							if(perspectiveData[scoreCount].score == null)
							perspScore[scoreCount] = null
							else
							perspScore[scoreCount] = parseFloat(perspectiveData[scoreCount].score);
							//categories[scoreCount] = perspectiveData[scoreCount].date;
							scoreCount++;
						}
						//chart.series[0].data.length = 0;
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
						chart.xAxis[0].setExtremes(0,valuesCount-1);
						chart.series[0].setData(perspScore, true);
				});
				request.post("scorecards/get-persp-gauge.php",
				{
					handleAs: "json",
					data: {
						objectId: kpiGlobalId,
						objectType: kpiGlobalType,
						objectPeriod: period,
						objectDate: globalDate
				}
				}).then(function(perspGauge)
					{
						//console.log('Perspective score: '+perspGauge);
						domStyle.set(dom.byId("divGauge"), "display", "block");
						gauge.yAxis[0].removePlotBand('red');
						gauge.yAxis[0].removePlotBand('yellow');
						gauge.yAxis[0].removePlotBand('green');
						gauge.yAxis[0].removePlotBand('blue');
						gauge.yAxis[0].removePlotBand('darkGreen');
						gauge.yAxis[0].addPlotBand({
							color: '#ff0000',//red
							from: 0,
							to: 3.33,
							outerRadius: '100%',
							innerRadius: '1%',
							zIndex:-100,
							id: 'red'
						});
						gauge.yAxis[0].addPlotBand({
							color: '#FFD900',//yellow
							from: 3.33,
							to: 6.67,
							outerRadius: '100%',
							innerRadius: '1%',
							zIndex:-100,
							id: 'yellow'
						});
						gauge.yAxis[0].addPlotBand({
							color: '#33CC00',//green
							from: 6.67,
							to: 10,
							outerRadius: '100%',
							innerRadius: '1%',
							zIndex:-100,
							id: 'green'
						});
						if(perspGauge == 'No Score')
						{
							//console.log(', Score: ' + perspGauge)
							gauge.series[0].points[0].update(null);
							gauge.series[0].options.dial.radius = 0;
							gauge.series[0].isDirty = true;
							gauge.redraw();
						}
						else
						{
							//console.log('Score: ' + perspGauge)
							gauge.series[0].options.dial.radius = '100%';
							gauge.series[0].isDirty = true;
							gauge.redraw();
							var score = parseFloat(perspGauge);
							score = Math.round(score * 100) / 100;
							gauge.series[0].points[0].update(score);
						}
				});
				break;
			}
			case "organization":
			{
				domStyle.set(dom.byId("chartDiv"), "display", 'block');
				domStyle.set(dom.byId("divChart"), "display", "block");
				//alert("Id:" + kpiGlobalId + ", type: " + kpiGlobalType + ", period: " + period + ", date:" + globalDate);
				request.post("scorecards/get-org-scores.php",{
				handleAs: "json",
				data: {
					objectId: kpiGlobalId,
					objectType: kpiGlobalType,
					objectPeriod: period,
					objectDate: globalDate,
					valuesCount: valuesCount
				}
				}).then(function(orgData)
					{	//alert(json.stringify(orgData));
						//dijit.byId("interpretation").set("value", "organization");

						var categories = [], orgScore = [], scoreCount = 0;
						while(scoreCount < orgData.length)
						{
							categories[scoreCount] = orgData[scoreCount].date
							if(orgData[scoreCount].score == null)
							orgScore[scoreCount] = null
							else
							orgScore[scoreCount] = parseFloat(orgData[scoreCount].score);
							//categories[scoreCount] = orgData[scoreCount].date;
							scoreCount++;
						}
						if (chart && chart.yAxis && chart.yAxis[0] && chart.yAxis[0].plotLinesAndBands) {
							if (chart.yAxis[0].plotLinesAndBands[0] && chart.yAxis[0].plotLinesAndBands[0].svgElem) {
								chart.yAxis[0].plotLinesAndBands[0].svgElem.show();
							}
							if (chart.yAxis[0].plotLinesAndBands[1] && chart.yAxis[0].plotLinesAndBands[1].svgElem) {
								chart.yAxis[0].plotLinesAndBands[1].svgElem.show();
							}
							if (chart.yAxis[0].plotLinesAndBands[2] && chart.yAxis[0].plotLinesAndBands[2].svgElem) {
								chart.yAxis[0].plotLinesAndBands[2].svgElem.show();
							}
						}
						if (chart && chart.xAxis && chart.xAxis[0]) {
							chart.xAxis[0].setCategories(categories, false);
						}
						if (chart && chart.series) {
							if (chart.series[1]) chart.series[1].hide();
							if (chart.series[2]) chart.series[2].hide();
							if (chart.series[3]) chart.series[3].hide();
							if (chart.series[4]) chart.series[4].hide();
							if (chart.series[5]) chart.series[5].hide();
						}
						if (chart && chart.yAxis && chart.yAxis[0]) {
							chart.yAxis[0].setExtremes(0,10);
						}
						if (chart && chart.xAxis && chart.xAxis[0]) {
							chart.xAxis[0].setExtremes(0,valuesCount-1);
						}
						if (chart && chart.series && chart.series[0]) {
							chart.series[0].setData(orgScore, true);
						}

					});
					request.post("scorecards/get-org-gauge.php",
					{
						handleAs: "json",
						data: {
							objectId: kpiGlobalId,
							objectType: kpiGlobalType,
							objectPeriod: period,
							objectDate: globalDate
					}
					}).then(function(orgGauge)
						{
							//console.log('Organization score: '+orgGauge);
							domStyle.set(dom.byId("divGauge"), "display", "block");
							gauge.yAxis[0].removePlotBand('red');
							gauge.yAxis[0].removePlotBand('yellow');
							gauge.yAxis[0].removePlotBand('green');
							gauge.yAxis[0].removePlotBand('blue');
							gauge.yAxis[0].removePlotBand('darkGreen');
							gauge.yAxis[0].addPlotBand({
								color: '#ff0000',//red
								from: 0,
								to: 3.33,
								outerRadius: '100%',
								innerRadius: '1%',
								zIndex:-100,
								id: 'red'
							});
							gauge.yAxis[0].addPlotBand({
								color: '#FFD900',//yellow
								from: 3.33,
								to: 6.67,
								outerRadius: '100%',
								innerRadius: '1%',
								zIndex:-100,
								id: 'yellow'
							});
							gauge.yAxis[0].addPlotBand({
								color: '#33CC00',//green
								from: 6.67,
								to: 10,
								outerRadius: '100%',
								innerRadius: '1%',
								zIndex:-100,
								id: 'green'
							});
							if(orgGauge == '' || orgGauge == 'No Score')
							{
								//console.log(', Score: ' + orgGauge)
								gauge.series[0].points[0].update(null);
								gauge.series[0].options.dial.radius = 0;
								gauge.series[0].isDirty = true;
								gauge.redraw();
							}
							else
							{
								//console.log('Score: ' + orgGauge)
								gauge.series[0].options.dial.radius = '100%';
								gauge.series[0].isDirty = true;
								gauge.redraw();
								var score = parseFloat(orgGauge);
								score = Math.round(score * 100) / 100;
								gauge.series[0].points[0].update(score);
							}
						});
				break;
			}
			case "individual":
			{
				request.post("scorecards/get-ind-scores.php",{
				handleAs: "json",
				data: {
					objectId: kpiGlobalId,
					objectType: kpiGlobalType,
					objectPeriod: period,
					objectDate: globalDate,
					valuesCount: valuesCount
				}
				}).then(function(indData)
				{
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
							indScore[scoreCount] = parseFloat(indData[scoreCount].score);
							categories[scoreCount] = indData[scoreCount].date;
							scoreCount++;
						}
						if(nullCount == indData.length)
						{
							domStyle.set(dom.byId("divChart"), "display", "none");
							domStyle.set(dom.byId("chartDiv"), "display", 'none');
						}
						else
						{
							domStyle.set(dom.byId("chartDiv"), "display", 'block');
							domStyle.set(dom.byId("divChart"), "display", "block");
							if (chart && chart.yAxis && chart.yAxis[0] && chart.yAxis[0].plotLinesAndBands) {
								if (chart.yAxis[0].plotLinesAndBands[0] && chart.yAxis[0].plotLinesAndBands[0].svgElem) {
									chart.yAxis[0].plotLinesAndBands[0].svgElem.show();
								}
								if (chart.yAxis[0].plotLinesAndBands[1] && chart.yAxis[0].plotLinesAndBands[1].svgElem) {
									chart.yAxis[0].plotLinesAndBands[1].svgElem.show();
								}
								if (chart.yAxis[0].plotLinesAndBands[2] && chart.yAxis[0].plotLinesAndBands[2].svgElem) {
									chart.yAxis[0].plotLinesAndBands[2].svgElem.show();
								}
							}
							if (chart && chart.xAxis && chart.xAxis[0]) {
								chart.xAxis[0].setCategories(categories, false);
							}
							if (chart && chart.series) {
								if (chart.series[1]) chart.series[1].hide();
								if (chart.series[2]) chart.series[2].hide();
								if (chart.series[3]) chart.series[3].hide();
								if (chart.series[4]) chart.series[4].hide();
								if (chart.series[5]) chart.series[5].hide();
							}
							if (chart && chart.yAxis && chart.yAxis[0]) {
								chart.yAxis[0].setExtremes(0,10);
							}
							if (chart && chart.xAxis && chart.xAxis[0]) {
								chart.xAxis[0].setExtremes(0,valuesCount-1);
							}
							if (chart && chart.series && chart.series[0]) {
								chart.series[0].setData(indScore, true);
							}
						}
					});//end of request.post get-ind-scores.php
					request.post("scorecards/get-ind-gauge.php",
					{
						handleAs: "json",
						data: {
							objectId: kpiGlobalId,
							objectType: kpiGlobalType,
							objectPeriod: period,
							objectDate: globalDate
						}
					}).then(function(indGauge)
					{
						domStyle.set(dom.byId("divGauge"), "display", "block");
						gauge.yAxis[0].removePlotBand('red');
						gauge.yAxis[0].removePlotBand('yellow');
						gauge.yAxis[0].removePlotBand('green');
						gauge.yAxis[0].removePlotBand('blue');
						gauge.yAxis[0].removePlotBand('darkGreen');
						gauge.yAxis[0].addPlotBand({
							color: '#ff0000',//red
							from: 0,
							to: 3.33,
							outerRadius: '100%',
							innerRadius: '1%',
							zIndex:-100,
							id: 'red'
						});
						gauge.yAxis[0].addPlotBand({
							color: '#FFD900',//yellow
							from: 3.33,
							to: 6.67,
							outerRadius: '100%',
							innerRadius: '1%',
							zIndex:-100,
							id: 'yellow'
						});
						gauge.yAxis[0].addPlotBand({
							color: '#33CC00',//green
							from: 6.67,
							to: 10,
							outerRadius: '100%',
							innerRadius: '1%',
							zIndex:-100,
							id: 'green'
						});
						if(indGauge == 'No Score' || indGauge == '' || indGauge == null)
						{
							//console.log(', Score: ' + indGauge)
							gauge.series[0].points[0].update(null);
							gauge.series[0].options.dial.radius = 0;
							gauge.series[0].isDirty = true;
							gauge.redraw();
						}
						else
						{
							//console.log('Score: ' + indGauge)
							gauge.series[0].options.dial.radius = '100%';
							gauge.series[0].isDirty = true;
							gauge.redraw();
							var score = parseFloat(indGauge);
							score = Math.round(score * 100) / 100;
							gauge.series[0].points[0].update(score);
						}
					});
				break;
			}
		}
}

showPrevious = function() /*** #scorecardMap ***/
{
	if(dijit.byId("previousCheckbox").checked == true)
	{
		request.post("scorecards/get-kpi-scores.php",{
		handleAs: "json",
		data: {
			objectId: kpiGlobalId,
			objectType: kpiGlobalType,
			objectPeriod: period,
			objectDate: globalDate,
			valuesCount: valuesCount,
			previousPeriod: 'True'
		}
		}).then(function(kpiData)
		{
			var previousScore = [], scoreCount = 0;
			while(scoreCount < kpiData.length)
			{
				if(kpiData[scoreCount].actual == null)
				{
					previousScore[scoreCount] = null;
				}
				else
				{
					previousScore[scoreCount] = parseFloat(kpiData[scoreCount].actual);
				}
				scoreCount++;
			}//end of while
			if (chart && chart.addSeries) {
				chart.addSeries({
					name: "Previous Year",
					data: previousScore,
					color: 'black',
					zIndex: 30
				}, true);
			}
		})//end of request.post
	}
	else
	{
		if (chart && chart.series) {
			var seriesLength = chart.series.length;
			for(var i = seriesLength - 1; i > -1; i--) {
				//if(chart.series[i].name.toLowerCase() == 'navigator') {
				if(chart.series[i].name == 'Previous Year') {
					chart.series[i].remove();
				}
			}
		}
	}
}
scorecardMain = function(objectId, objectType) /*** #scorecardMap ***/
{
	var chartStore;
	var chartData;
	var togglerOwner = new Toggler({
	//node: dom.byId('divOwner'),
	showFunc: coreFx.wipeIn,
	hideFunc: coreFx.wipeOut,
	node: "divOwner"
	});
	var togglerMeasures = new Toggler({
	node: dom.byId('divMeasures')
	//node: "ownerTitle"
	});
	//capture event here when a tree node  has been clicked
	kpiGlobalId = objectId;
	kpiGlobalType = objectType;
	request.post("scorecards/get-content.php",{
		handleAs: "json",
		data: {
			objectId: objectId,
			objectType: objectType,
			objectPeriod: period,
			objectDate: globalDate
		}
		}).then(function(data) {
		switch(objectType)
		{
		case "organization":
		{
			kpiGlobalId = objectId;
			kpiGlobalType = objectType;
			//interpretationSavingId.set("value", objectId);
			//wayForwardSavingId.set("value", objectId);
			updateChart();
			//dijit.byId("scoreGauge").indicators[0].set("value", 9);
			//gauge.startup();
			togglerOwner.hide();
			//togglerMeasures.show();
			if (dijit.byId("scorecardItemTitle")) dijit.byId("scorecardItemTitle").set("title", "Organization Name");
			if (dijit.byId("descriptionTitle")) dijit.byId("descriptionTitle").set("title", "Mission, Vision & Values");
			if (dijit.byId("cascadedTitle")) dijit.byId("cascadedTitle").set("title", "Cascading");
			if (dijit.byId("measureTitle")) dijit.byId("measureTitle").set("title", "Perspectives");
			if (dijit.byId("initiativeTitle")) dijit.byId("initiativeTitle").set("title", "Initiatives");

			domStyle.set(dom.byId("divIntro"), "display", "none");
			domStyle.set(dom.byId("divChart"), "display", "block");
			domStyle.set(dom.byId("divObjectiveName"), "display", "block");
			domStyle.set(dom.byId("divObjectiveDescription"), "display", "block");
			domStyle.set(dom.byId("divInitiatives"), "display", "block");
			domStyle.set(dom.byId("divCascadedTo"), "display", "block");
			domStyle.set(dom.byId("divNotes"), "display", "block");
			domStyle.set(dom.byId("divNotes2"), "display", "block");
			// Show the appropriate conversation div based on current module
			var conversationDivId = getConversationDivId();
			if(dom.byId(conversationDivId)) {
				domStyle.set(dom.byId(conversationDivId), "display", "block");
			}

			domStyle.set(dom.byId("divGauge"), "display", "block");
			domStyle.set(dom.byId("divChartType"), "display", "none");
			domStyle.set(dom.byId("divPhoto"), "display", "none");
			domStyle.set(dom.byId("divObjectiveDescription"), "display", "block");
			domStyle.set(dom.byId("divCascadedTo"), "display", "block");
			domStyle.set(dom.byId("divMeasures"), "display", "block");
			domStyle.set(dom.byId("divMeasures"), "width", "33%");
			domStyle.set(dom.byId("divOwner"), "display", "none");
			domStyle.set(dom.byId("divDevelopmentPlan"), "display", "none");
			domStyle.set(dom.byId("divCoreValues"), "display", "none");

			if(data["interpretation"] == null) dijit.byId("interpretation").set("value", '');
			else dijit.byId("interpretation").set("value", data["interpretation"]);
			if (data["wayForward"] == null) dijit.byId("wayForward").set("value", '');
			else dijit.byId("wayForward").set("value", data["wayForward"]);
			if(data["mission"] == null) data["mission"] = 'No Mission';
			if(data["vision"] == null) data["vision"] = 'No Vision';
			if(data["valuez"] == null) data["valuez"] = 'No Values';
			dojo.byId("objectiveName").innerHTML = data["name"];
			dojo.byId("objectiveDescription").innerHTML = "<table width='100%'><tr><td width='80%' align='left'><strong>Mission: </strong>" + data["mission"] + "<br><br><strong>Vision: </strong>" + data["vision"] + "<br><br><strong>Values: </strong>" + data["valuez"] + "</td><td width='20%' align='right'><div><img src='images/logo.png' style='vertical-align:middle' /></div></td></tr></table>";

			var perspectiveCount = 1;
			var perspectiveNumber, bgColor, perspectiveScore, displayPerspScore, perspectiveWeight;
			var combinedData = "<div class='border border-primary rounded-3' style='overflow:hidden;'>";
			combinedData = combinedData + "<table class='table table-bordered table-sm table-condensed table-striped'>";
			combinedData = combinedData + "<tr class='table-primary'><th>Perspective</th><th colspan='2'>Score</th><th>Weight</th></tr>";
			while(perspectiveCount <= data["Perspective Count"])
			{
				perspectiveNumber = "Perspective"+perspectiveCount;
				perspectiveScore = "Perspective Score"+perspectiveCount;
				perspectiveWeight = "Perspective Weight"+perspectiveCount;
				perspectiveWeight = data[perspectiveWeight] * 100;
				displayPerspScore = data[perspectiveScore];
				if(displayPerspScore == "grey") displayPerspScore = "&nbsp; - &nbsp;";

				if(data[perspectiveScore]<3.3 && data[perspectiveScore] > 0){bgColor = "red3d"} 
				else if (data[perspectiveScore]>=3.3 && data[perspectiveScore] < 6.7){bgColor = "yellow3d"} 
				else if(data[perspectiveScore] >= 6.7 && data[perspectiveScore]<=10){bgColor = "green3d"} 
				else{bgColor = "grey3d"}

				combinedData = combinedData + "<tr><td>"+data[perspectiveNumber]+"</td><td class='border-end-0'>"+displayPerspScore+"</td><td class='border-start-0'><div class='"+bgColor+"'></div></td><td>"+perspectiveWeight+"%</td></tr>";
				perspectiveCount++;
			}
			dojo.byId("measureContent").innerHTML = combinedData+"</table></div>";

			combinedData = null;
			var initiativeCount = 1;
			var initiativeNumber, bgColor, dueDate;
			var combinedData = "<div class='border border-primary rounded-3' style='overflow:hidden;'>";
			combinedData = "<table class='table table-bordered table-sm table-condensed table-striped'>";
			while(initiativeCount <= data["Initiative Count"])
			{
				initiativeNumber = "Initiative"+initiativeCount;
				dueDate = "dueDate"+initiativeCount;
				bgColor = "Color"+initiativeCount;
				combinedData = combinedData + "<tr><td>"+data[initiativeNumber]+"</td><td bgcolor='"+data[bgColor]+"' style='white-space:nowrap;'>"+data[dueDate]+"</td></tr>";
				initiativeCount++;
			}
			dojo.byId("initiativeContent").innerHTML = combinedData+"</table></div>";

			combinedData = null;
			var cascadedCount = 1;
			var cascadedNumber, cascadedScore, bgColor, displayScore;
			var combinedData = "<div class='border border-primary rounded-3' style='overflow:hidden;'>";
			combinedData = combinedData + "<table class='table table-bordered table-sm table-condensed table-striped'>";
			if(data["Cascaded Count"] > 0) combinedData = combinedData + "<tr class='table-primary'><th>Cascaded To</th><th colspan='2'>Score</th></tr>";
			while(cascadedCount <= data["Cascaded Count"])
			{
				cascadedNumber = "Cascaded To"+cascadedCount;
				cascadedScore = "Cascaded To Score"+cascadedCount;
				displayScore = data[cascadedScore];
				if(displayScore == "grey") displayScore = "&nbsp; - &nbsp;";

				if(data[cascadedScore]<3.3 && data[cascadedScore] >= 0){bgColor = "red3d"} 
				else if (data[cascadedScore]>=3.3 && data[cascadedScore] <= 6.7){bgColor = "yellow3d"} 
				else if(data[cascadedScore] > 6.7 &&  data[cascadedScore] <= 10){bgColor = "green3d"} 
				else{bgColor = "grey3d"}
				combinedData = combinedData + "<tr><td>"+data[cascadedNumber]+"</td><td class='border-end-0'>"+displayScore+"</td><td  class='border-start-0'><div class='"+bgColor+"'></div></td></tr>";
				cascadedCount++;
			}
			var cascadedCount = 1;
			var cascadedNumber, cascadedScore, bgColor, displayScore;
			if(data["Cascaded From Count"] > 0) combinedData = combinedData + "<tr class='table-primary'><th>Cascaded From</th><th colspan='2'>Score</th></tr>";
			while(cascadedCount <= data["Cascaded From Count"])
			{
				cascadedNumber = "Cascaded From"+cascadedCount;
				cascadedScore = "Cascaded From Score"+cascadedCount;
				displayScore = data[cascadedScore];
				if(displayScore == "grey") displayScore = "&nbsp; - &nbsp;";

				if(data[cascadedScore]<3.3 && data[cascadedScore] >= 0){bgColor = "red3d"} 
				else if (data[cascadedScore]>=3.3 && data[cascadedScore] <= 6.7){bgColor = "yellow3d"} 
				else if(data[cascadedScore] > 6.7 &&  data[cascadedScore] <= 10){bgColor = "green3d"} 
				else{bgColor = "grey3d"}
				combinedData = combinedData + "<tr><td>"+data[cascadedNumber]+"</td><td class='border-end-0'>"+displayScore+"</td><td  class='border-start-0'><div class='"+bgColor+"'></div></td></tr>";
				cascadedCount++;
			}
			
			dojo.byId("cascadedContent").innerHTML = combinedData+"</table></div>";

			break;
		}
		case "perspective":
		{
			kpiGlobalId = objectId;
			kpiGlobalType = objectType;
			//interpretationSavingId.set("value", objectId);
			//wayForwardSavingId.set("value", objectId);
			updateChart();
			togglerOwner.hide();
			//togglerMeasures.show();
			if (dijit.byId("scorecardItemTitle")) dijit.byId("scorecardItemTitle").set("title", "Perspective Name");
			if (dijit.byId("measureTitle")) dijit.byId("measureTitle").set("title", "Objectives");
			if (dijit.byId("initiativeTitle")) dijit.byId("initiativeTitle").set("title", "Initiatives");

			domStyle.set(dom.byId("divIntro"), "display", "none");
			domStyle.set(dom.byId("divChart"), "display", "block");
			domStyle.set(dom.byId("divObjectiveName"), "display", "block");
			domStyle.set(dom.byId("divObjectiveDescription"), "display", "block");
			domStyle.set(dom.byId("divInitiatives"), "display", "block");
			domStyle.set(dom.byId("divCascadedTo"), "display", "block");
			domStyle.set(dom.byId("divNotes"), "display", "block");
			domStyle.set(dom.byId("divNotes2"), "display", "block");
			// Show the appropriate conversation div based on current module
			var conversationDivId = getConversationDivId();
			if(dom.byId(conversationDivId)) {
				domStyle.set(dom.byId(conversationDivId), "display", "block");
			}

			domStyle.set(dom.byId("divGauge"), "display", "block");
			domStyle.set(dom.byId("divPhoto"), "display", "none");
			domStyle.set(dom.byId("divChartType"), "display", "none");
			domStyle.set(dom.byId("divCascadedTo"), "display", "none");

			domStyle.set(dom.byId("divObjectiveDescription"), "display", "none");
			//alert(data["name"]);
			domStyle.set(dom.byId("divOwner"), "display", "none");
			domStyle.set(dom.byId("divDevelopmentPlan"), "display", "none");
			domStyle.set(dom.byId("divCoreValues"), "display", "none");
			domStyle.set(dom.byId("divMeasures"), "display", "block");
			domStyle.set(dom.byId("divInitiatives"), "display", "block");

			if(data["interpretation"] == null) dijit.byId("interpretation").set("value", '');
			else dijit.byId("interpretation").set("value", data["interpretation"]);
			if (data["wayForward"] == null) dijit.byId("wayForward").set("value", '');
			else dijit.byId("wayForward").set("value", data["wayForward"]);
			dojo.byId("objectiveName").innerHTML = data["name"];

			var objectiveCount = 1;
			var objectiveNumber, objectiveScore, bgColor, displayObjScore, objectiveWeight;
			var combinedData = "<div class='border border-primary rounded-3' style='overflow:hidden;'>";
			combinedData = combinedData + "<table class='table table-bordered table-sm table-condensed table-striped'>";
			combinedData = combinedData + "<tr class='table-primary'><th>Objective</th><th colspan='2'>Score</th><th>Weight</th></tr>";
			while(objectiveCount <= data["Objective Count"])
			{
				objectiveNumber = "Objective"+objectiveCount;
				objectiveScore = "Objective Score"+objectiveCount;
				objectiveWeight = "Objective Weight"+objectiveCount;
				objectiveWeight  = data[objectiveWeight]*100;
				displayObjScore = data[objectiveScore];
				if(displayObjScore == "grey") displayObjScore = "&nbsp; - &nbsp;";
				if(data[objectiveScore]<3.3 && data[objectiveScore]>0){bgColor = "red3d"} 
				else if (data[objectiveScore]>=3.3 && data[objectiveScore]<6.7){bgColor = "yellow3d"} 
				else if (data[objectiveScore]>=6.7 && data[objectiveScore]<=10) {bgColor = "green3d"} 
				else{bgColor = "grey3d";}//#D0D0D0 = light grey color
				combinedData = combinedData + "<tr><td>"+data[objectiveNumber]+"</td><td class='border-end-0'>"+displayObjScore+"</td><td class='border-start-0'><div class='"+bgColor+"'></div></td><td>"+objectiveWeight+"%</td></tr>";
				objectiveCount++;
			}
			dojo.byId("measureContent").innerHTML = combinedData+"</table></div>";

			combinedData = null;
			var initiativeCount = 1;
			var initiativeNumber, bgColor, dueDate;
			var combinedData = "<table class='table table-bordered table-sm table-condensed table-striped border-primary rounded'>";
			while(initiativeCount <= data["Initiative Count"])
			{
				initiativeNumber = "Initiative"+initiativeCount;
				dueDate = "dueDate"+initiativeCount;
				bgColor = "Color"+initiativeCount;
				combinedData = combinedData + "<tr><td>"+data[initiativeNumber]+"</td><td bgcolor='"+data[bgColor]+"'  style='white-space:nowrap;'>"+data[dueDate]+"</td></tr>";
				initiativeCount++;
			}
			dojo.byId("initiativeContent").innerHTML = combinedData+"</table>";

			break;
		}
		case "objective":
		{
			kpiGlobalId = objectId;
			kpiGlobalType = objectType;
			//interpretationSavingId.set("value", objectId);
			//wayForwardSavingId.set("value", objectId);
			togglerOwner.show();
			//alert(globalDate);
			//togglerMeasures.show();
			updateChart();
			domStyle.set(dom.byId("divIntro"), "display", "none");
			domStyle.set(dom.byId("divChart"), "display", "block");
			domStyle.set(dom.byId("divObjectiveName"), "display", "block");
			domStyle.set(dom.byId("divObjectiveDescription"), "display", "block");
			domStyle.set(dom.byId("divInitiatives"), "display", "block");
			domStyle.set(dom.byId("divCascadedTo"), "display", "block");
			domStyle.set(dom.byId("divNotes"), "display", "block");
			domStyle.set(dom.byId("divNotes2"), "display", "block");
			// Show the appropriate conversation div based on current module
			var conversationDivId = getConversationDivId();
			if(dom.byId(conversationDivId)) {
				domStyle.set(dom.byId(conversationDivId), "display", "block");
			}

			domStyle.set(dom.byId("divGauge"), "display", "block");
			domStyle.set(dom.byId("divChartType"), "display", "none");
			domStyle.set(dom.byId("divInitiatives"), "display", "block");
			//domStyle.set(dom.byId("objectiveTeam"), "display", "block");
			domStyle.set(dom.byId("divMeasures"), "display", "block");
			domStyle.set(dom.byId("divMeasures"), "width", "33%");
			domStyle.set(dom.byId("divPhoto"), "display", "none");
			domStyle.set(dom.byId("divObjectiveDescription"), "display", "block");
			domStyle.set(dom.byId("divDevelopmentPlan"), "display", "none");
			domStyle.set(dom.byId("divCoreValues"), "display", "none");
			descriptionTitle.set("title", "Description & Outcome");
			scorecardItemTitle.set("title", "Objective Name");
			ownerTitle.set("title", "Other Details");
			measureTitle.set("title", "Measures");
			initiativeTitle.set("title", "Initiatives");
			cascadedTitle.set("title", "Cascading");
			if(data["interpretation"] == null) dijit.byId("interpretation").set("value", '');
			else dijit.byId("interpretation").set("value", data["interpretation"]);
			if (data["wayForward"] == null) dijit.byId("wayForward").set("value", '');
			else dijit.byId("wayForward").set("value", data["wayForward"]);
			dojo.byId("objectiveName").innerHTML = data["name"];
			dojo.byId("objectiveDescription").innerHTML = "<table class='table table-sm table-condensed table-borderless table-responsive mb-0'><tr><th>Description</th><td>" + data["description"] + "</td></tr><tr><th>Key Result Area</th><td>" + data["outcome"] + "</td></tr></table>";
			
			combinedData = null;
			//var teamCount = 1;
			//var teamNumber;
			var combinedData = "";
			/*while(teamCount <= data["Team Count"])
			{
				teamNumber = "Team"+teamCount;
				if(teamCount < data["Team Count"])
				combinedData = combinedData + data[teamNumber] +"; ";
				else
				combinedData = combinedData + data[teamNumber];
				teamCount++;
			}*/
			var weight = data["weight"]*100;
			var team = "";
			//console.log("Length = " + data["owner"].length + "; " + data["owner"]);
			if(data["tags"] !== null && data["tags"].length > 0)
			{
				var owners = JSON.parse(data["tags"]);
				var teamCount = 1;
				for(var i = 0; i < owners.length; i++)
				{
					if(teamCount < owners.length)
					team = team + owners[i].label + "; ";
					else
					team = team + owners[i].label;
					teamCount++;
				}
			}
			else
			team = "Owner/Team not provided";
			dojo.byId("objectiveOwner").innerHTML = "<table class='table table-sm table-condensed table-borderless table-responsive mb-0'><tr><th>Owner/Team </th><td>" + team + "</td></tr><tr><th>Weight</th><td>" + weight + "%" + "</td></tr><tr><td colspan='2' style='text-align: right;' class='fs-6 fw-lighter fst-italic'>(id: "+data["id"]+")</td></tr></table>";

			combinedData = null;
			var measureCount = 1;
			var measureNumber, bgColor, measureScore, displayKpiScore, measureWeight;
			var combinedData = "<div class='border border-primary rounded-3' style='overflow:hidden;'>";
			combinedData = combinedData + "<table class='table table-bordered table-sm table-condensed table-striped'>";
			combinedData = combinedData + "<tr class='table-primary'><th>Measure</th><th colspan='2'>Score</th><th>Weight</th></tr>";
			while(measureCount <= data["Measure Count"])
			{
				measureNumber = "Measure"+measureCount;
				measureScore = "measureScore"+measureCount;
				measureWeight = "measureWeight"+measureCount;
				measureWeight = data[measureWeight]*100;
				measureWeight = measureWeight.toFixed(2);
				displayKpiScore = data[measureScore];
				if(displayKpiScore == "grey") displayKpiScore = "&nbsp; - &nbsp;";
				if(data[measureScore]=='No Score') bgColor = "grey3d"; 
				else if(data[measureScore]<3.3 && data[measureScore]>=0){bgColor = "red3d"} 
				else if (data[measureScore]>=3.3 && data[measureScore]<6.7){bgColor = "yellow3d"} 
				else if (data[measureScore]>=6.7 && data[measureScore]<=10){bgColor = "green3d"} 
				else{bgColor = "grey3d"}
				combinedData = combinedData + "<tr><td>"+data[measureNumber]+"</td><td class='border-end-0'>"+displayKpiScore+"</td><td class='border-start-0'><div class='"+bgColor+"'></div></td><td>"+measureWeight+"%</td></tr>";
				measureCount++;
			}
			dojo.byId("measureContent").innerHTML = combinedData+"</table></div>";

			combinedData = null;
			var initiativeCount = 1;
			var initiativeNumber, initiativeId, bgColor, dueDate;
			var combinedData = "<div class='border border-primary rounded-3' style='overflow:hidden;'>";
			combinedData = combinedData + "<table class='table table-bordered table-sm table-condensed table-striped'>";
			while(initiativeCount <= data["Initiative Count"])
			{
				initiativeNumber = "Initiative"+initiativeCount;
				initiativeId = "InitiativeId"+initiativeCount;
				//alert(data[initiativeId]);
				dueDate = "dueDate"+initiativeCount;
				bgColor = "Color"+initiativeCount;
				combinedData = combinedData + "<tr><td id='init"+data[initiativeId]+"' style='cursor: pointer; text-decoration: underline; color: blue;' onClick='moreDetails("+data[initiativeId]+")' onMouseOut='removeTooltip()'>"+data[initiativeNumber]+"</td><td bgcolor='"+data[bgColor]+"'  style='white-space:nowrap;'>"+data[dueDate]+"</td></tr>";
				initiativeCount++;
			}
			dojo.byId("initiativeContent").innerHTML = combinedData+"</table></div>";

			combinedData = null;
			var cascadedCount = 1;
			var cascadedNumber, cascadedScore, bgColor, displayScore;
			var combinedData = "<div class='border border-primary rounded-3' style='overflow:hidden;'>";
			combinedData = combinedData + "<table class='table table-bordered table-sm table-condensed table-striped'>";
			if(data["Cascaded Count"] > 0) combinedData = combinedData + "<tr class='table-primary'><th>Cascaded To</th><th colspan='2'>Score</th></tr>";
			while(cascadedCount <= data["Cascaded Count"])
			{
				cascadedNumber = "Cascaded To"+cascadedCount;
				cascadedScore = "Cascaded To Score"+cascadedCount;
				displayScore = data[cascadedScore];
				if(displayScore == "grey") displayScore = "&nbsp; - &nbsp;";

				if(data[cascadedScore]<3.3 && data[cascadedScore] > 0){bgColor = "red3d"} 
				else if (data[cascadedScore]>=3.3 && data[cascadedScore] <= 6.7){bgColor = "yellow3d"} 
				else if(data[cascadedScore] > 6.7 &&  data[cascadedScore] <= 10){bgColor = "green3d"} 
				else{bgColor = "grey3d"}
				combinedData = combinedData + "<tr><td>"+data[cascadedNumber]+"</td><td class='border-end-0'>"+displayScore+"</td><td  class='border-start-0'><div class='"+bgColor+"'></div></td></tr>";
				cascadedCount++;
			}
			var cascadedCount = 1;
			var cascadedNumber, cascadedScore, bgColor, displayScore;
			if(data["Cascaded From Count"] > 0) combinedData = combinedData + "<tr class='table-primary'><th>Cascaded From</th><th colspan='2'>Score</th></tr>";
			while(cascadedCount <= data["Cascaded From Count"])
			{
				cascadedNumber = "Cascaded From"+cascadedCount;
				cascadedScore = "Cascaded From Score"+cascadedCount;
				displayScore = data[cascadedScore];
				if(displayScore == "grey") displayScore = "&nbsp; - &nbsp;";

				if(data[cascadedScore]<3.3 && data[cascadedScore] > 0){bgColor = "red3d"} 
				else if (data[cascadedScore]>=3.3 && data[cascadedScore] <= 6.7){bgColor = "yellow3d"} 
				else if(data[cascadedScore] > 6.7 &&  data[cascadedScore] <= 10){bgColor = "green3d"} 
				else{bgColor = "grey3d"}
				combinedData = combinedData + "<tr><td>"+data[cascadedNumber]+"</td><td class='border-end-0'>"+displayScore+"</td><td  class='border-start-0'><div class='"+bgColor+"'></div></td></tr>";
				cascadedCount++;
			}
			
			dojo.byId("cascadedContent").innerHTML = combinedData+"</table></div>";

			moreDetails = function(id)
			{
				getInitContent(id);
			}
			
			break;
		}
		case "measure":
		{
			//interpretationSavingId.set("value", objectId);
			//wayForwardSavingId.set("value", objectId);
			kpiGlobalId = objectId;
			kpiGlobalType = objectType;
			dataTypeDisplay = data['dataType'];
			currency = data['currency'];
			updateChart();
			togglerOwner.show();
			//togglerMeasures.hide();

			domStyle.set(dom.byId("divIntro"), "display", "none");
			domStyle.set(dom.byId("divChart"), "display", "block");
			domStyle.set(dom.byId("divObjectiveName"), "display", "block");
			domStyle.set(dom.byId("divObjectiveDescription"), "display", "block");
			domStyle.set(dom.byId("divInitiatives"), "display", "block");
			domStyle.set(dom.byId("divCascadedTo"), "display", "block");
			domStyle.set(dom.byId("divNotes"), "display", "block");
			domStyle.set(dom.byId("divNotes2"), "display", "block");
			// Show the appropriate conversation div based on current module
			var conversationDivId = getConversationDivId();
			if(dom.byId(conversationDivId)) {
				domStyle.set(dom.byId(conversationDivId), "display", "block");
			}

			//domStyle.set(dom.byId("divGauge3"), "display", "none");
			domStyle.set(dom.byId("divChartType"), "display", "block");
			domStyle.set(dom.byId("divCascadedTo"), "display", "block");
			//domStyle.set(dom.byId("divGauge4"), "display", "none");
			//domStyle.set(dom.byId("divGauge5"), "display", "none");
			//domStyle.set(dom.byId("divGaugeGoal"), "display", "none");
			domStyle.set(dom.byId("divPhoto"), "display", "none");
			domStyle.set(dom.byId("divMeasures"), "display", "none");
			domStyle.set(dom.byId("divDevelopmentPlan"), "display", "none");
			domStyle.set(dom.byId("divCoreValues"), "display", "none");
			domStyle.set(dom.byId("divObjectiveDescription"), "display", "block");

			if(data['calendarType'] == 'Daily' || data['calendarType'] == 'Weekly' || data['calendarType'] == 'Monthly')
			domStyle.set(dom.byId('trPrevious'), 'display', 'block');
			else domStyle.set(dom.byId('trPrevious'), 'display', 'none')

			if (dijit.byId("descriptionTitle")) dijit.byId("descriptionTitle").set("title", "Description");
			if (dijit.byId("ownerTitle")) dijit.byId("ownerTitle").set("title", "Other Measure Details");
			domStyle.set(dom.byId("objectiveTeam"), "display", "none");
			if (dijit.byId("scorecardItemTitle")) dijit.byId("scorecardItemTitle").set("title", "Measure Name");
			if (dijit.byId("initiativeTitle")) dijit.byId("initiativeTitle").set("title", "Initiatives");
			if (dijit.byId("cascadedTitle")) dijit.byId("cascadedTitle").set("title", "Linked Measures");
			dojo.byId("objectiveName").innerHTML = data["name"];

			if(data["interpretation"] == null) dijit.byId("interpretation").set("value", '');
			else dijit.byId("interpretation").set("value", data["interpretation"]);
			if (data["wayForward"] == null) dijit.byId("wayForward").set("value", '');
			else dijit.byId("wayForward").set("value", data["wayForward"]);
			var prefix, suffix;
			//console.log("dataType: " + data["dataType"]);
			switch(data["dataType"])
			{
				case "Standard":
				{
					prefix = "";
					suffix = "";
					break;
				}
				case "Currency":
				{
					prefix = "KShs ";
					suffix = "";
					break;
				}
				case "Percentage(%)":
				{
					prefix = "";
					suffix = "%";
					break;
				}
				default:
				{
					prefix = "";
					suffix = "";
					break;
				}
			}
			switch(data["gaugeType"])
			{
				case "goalOnly":
				{
					var targets = "<table><tr><td>Target:</td><td>"+prefix+parseFloat(data["green"]).toLocaleString()+suffix+"</td></tr></table>";
					break;
				}
				case "threeColor":
				{
					var targets = "<table><tr><td>Baseline:</td><td>"+prefix+parseFloat(data["red"]).toLocaleString()+suffix+"</td></tr><tr><td>Target:</td><td>"+prefix+parseFloat(data["green"]).toLocaleString()+suffix+"</td></tr></table>";
					break;
				}
				case "fourColor":
				{
					var targets = "<table><tr><td>Baseline:</td><td>"+prefix+parseFloat(data["red"]).toLocaleString()+suffix+"</td></tr><tr><td>Target:</td><td>"+prefix+parseFloat(data["green"]).toLocaleString()+suffix+"</td></tr><tr><td>Stretch Target:</td><td>"+prefix+parseFloat(data["darkGreen"]).toLocaleString()+suffix+"</td></tr></table>";
					break;
				}
				case "fiveColor":
				{
					var targets = "<table><tr><td>Baseline:</td><td>"+prefix+parseFloat(data["red"]).toLocaleString()+suffix+"</td></tr><tr><td>Target:</td><td>"+prefix+parseFloat(data["green"]).toLocaleString()+suffix+"</td></tr><tr><td>Stretch Target</td><td>"+prefix+parseFloat(data["darkGreen"]).toLocaleString()+suffix+"</td></tr><tr><td>Best:</td><td>"+prefix+parseFloat(data["blue"]).toLocaleString()+suffix+"</td></tr></table>";
					break;
				}
			}
			var weight = data["weight"]*100;
			dojo.byId("objectiveDescription").innerHTML = "<strong>Description: </strong>" + data["description"];
			dojo.byId("objectiveOwner").innerHTML = "<table class='table table-borderless table-responsive table-sm table-condensed'><tr><td valign='top'><table><tr><td>Owner:</td><td>"+data["owner"]+"</td></tr><tr><td>Updater:</td><td>" + data["updater"] + "</td></tr><tr><td>Update Frequency:</td><td>"+data["calendarType"]+"</td></tr><tr><td>Weight:</td><td>"+weight+"%</td></tr></table></td><td width='30px'></td><td valign='top'><table><tr><td>"+targets+"</td></tr></table>"+data["id"]+"</td></tr></table>";
			combinedData = null;
			var initiativeCount = 1;
			var initiativeNumber, initiativeId, bgColor, dueDate;
			var combinedData = "<table class='table table-bordered table-sm table-condensed table-striped border-primary rounded'>";
			combinedData = combinedData + "<tr class='table-primary'><td>Name</td><td>Due Date</td></tr>";
			while(initiativeCount <= data["Initiative Count"])
			{
				initiativeId = "InitiativeId"+initiativeCount;
				initiativeNumber = "Initiative"+initiativeCount;
				dueDate = "dueDate"+initiativeCount;
				bgColor = "Color"+initiativeCount;
				combinedData = combinedData + "<tr><td id='init"+data[initiativeId]+"' style='cursor: pointer; text-decoration: underline; color: blue;' onClick='moreDetailsKpi("+data[initiativeId]+")' onMouseOut='removeTooltip()'>"+data[initiativeNumber]+"</td><td bgcolor='"+data[bgColor]+"'>"+data[dueDate]+"</td></tr>";
				initiativeCount++;
			}
			dojo.byId("initiativeContent").innerHTML = combinedData+"</table>";

			combinedData = null;
			var cascadedCount = 1;
			var cascadedNumber, bgColor;
			var combinedData = "<table class='table table-bordered table-sm table-condensed table-striped border-primary rounded'>";
			while(cascadedCount <= data["Cascaded Count"])
			{
				cascadedNumber = "Cascaded To"+cascadedCount;
				cascadedScore = "Cascaded To Score"+cascadedCount;
				displayScore = data[cascadedScore];
				if(displayScore == "grey") displayScore = "&nbsp; - &nbsp;";

				if(data[cascadedScore]<3.3 && data[cascadedScore] >= 0){bgColor = "bg-danger"} else if (data[cascadedScore]>=3.3 && data[cascadedScore] <= 6.7){bgColor = "bg-warning"} else if(data[cascadedScore] > 6.7 &&  data[cascadedScore] <= 10){bgColor = "bg-success"} else{bgColor = "bg-secondary"}
				combinedData = combinedData + "<tr><td>"+data[cascadedNumber]+"</td><td class='"+bgColor+"'>"+displayScore+"</td></tr>";
				cascadedCount++;
			}
			dojo.byId("cascadedContent").innerHTML = combinedData+"</table>";

			moreDetailsKpi = function(id)
			{
				getInitContent(id);
			}
			

			break;
		}
		case "individual":
		{
			//interpretationSavingId.set("value", objectId);
			//wayForwardSavingId.set("value", objectId);
			kpiGlobalId = objectId;
			kpiGlobalType = objectType;
			updateChart();
			dojo.byId("hiddenIndId").value = kpiGlobalId;

			domStyle.set(dom.byId("divIntro"), "display", "none");
			domStyle.set(dom.byId("divObjectiveName"), "display", "block");
			domStyle.set(dom.byId("divObjectiveDescription"), "display", "block");
			domStyle.set(dom.byId("divInitiatives"), "display", "block");
			domStyle.set(dom.byId("divCascadedTo"), "display", "block");
			domStyle.set(dom.byId("divNotes"), "display", "block");
			domStyle.set(dom.byId("divNotes2"), "display", "block");
			// Show the appropriate conversation div based on current module
			var conversationDivId = getConversationDivId();
			if(dom.byId(conversationDivId)) {
				domStyle.set(dom.byId(conversationDivId), "display", "block");
			}

			domStyle.set(dom.byId("divChartType"), "display", "none");
			domStyle.set(dom.byId("divInitiatives"), "display", "none");
			domStyle.set(dom.byId("divCascadedTo"), "display", "block");
			domStyle.set(dom.byId("divMeasures"), "display", "block");
			domStyle.set(dom.byId("divMeasures"), "width", "66%");
			domStyle.set(dom.byId("divPhoto"), "display", "block");
			dojo.byId("photo").innerHTML = "<img src='"+ data["photo"] +"' max-width='190' height='122' align='middle' />";
			domStyle.set(dom.byId("divGauge"), "display", "block");
			domStyle.set(dom.byId("divOwner"), "display", "none");
			domStyle.set(dom.byId("divDevelopmentPlan"), "display", "block");
			domStyle.set(dom.byId("divCoreValues"), "display", "block");
			descriptionTitle.set("title", "Individual Details");
			scorecardItemTitle.set("title", "Name");
			measureTitle.set("title", "Assigned Tasks");
			//initiativeTitle.set("title", "Impacts");
			cascadedTitle.set("title", "Cascaded From");
			if(data["interpretation"] == null) dijit.byId("interpretation").set("value", '');
			else dijit.byId("interpretation").set("value", data["interpretation"]);
			if (data["wayForward"] == null) dijit.byId("wayForward").set("value", '');
			else dijit.byId("wayForward").set("value", data["wayForward"]);
			dojo.byId("objectiveName").innerHTML = data["name"];
			
			dojo.byId("objectiveDescription").innerHTML = '<table class="table table-condensed table-sm table-borderless"><tr><th>Designation</th><td>' + data["title"] + "</td></tr><tr><th>Department</th><td>" + data["department"] + "</td></tr><tr><th>Reports To </th><td>" + data["supervisor"] + "</td></tr><tr><td colspan='2' class='fs-6 fw-lighter fst-italic'>User Id : " + data["user_id"] + '</td></tr></table>';

			combinedData = null;
			var smartCount = 1;
			var smartNumber, bgColor, smartScore;
			var combinedData = "<table class='table table-bordered rounded table-sm table-condensed table-striped'>";
			while(smartCount <= data["Measure Count"])
			{
				smartNumber = "Measure"+smartCount;
				smartScore = "Measure Score"+smartCount;
				if(data[smartScore]<3.3 && data[smartScore] >= 0){bgColor = "bg-danger"} else if (data[smartScore]>=3.3 && data[smartScore] <= 6.7){bgColor = "bg-warning"} else if(data[smartScore] > 6.7 &&  data[smartScore] <= 10){bgColor = "bg-success"} else{bgColor = "bg-secondary"}
				//if(data[smartScore]<3.3){bgColor = "red"} else if (data[smartScore]>=3.3 || data[smartScore]<6.7){bgColor = "#FFFF00"} else{bgColor = "#009900"}
				combinedData = combinedData + "<tr><td>"+data[smartNumber]+"</td><td class='"+bgColor+"'>"+data[smartNumber]+"</td></tr>";
				smartCount++;
			}
			dojo.byId("initiativeContent").innerHTML = combinedData+"</table>";

			combinedData = null;
			var taskCount = 1;
			var deliverable, bgColor, trafficLight, objectiveImpacted, taskNumber, taskId, dueDate;
			var combinedData = "<table class='table rounded table-sm table-condensed table-striped mb-0'>";
			combinedData += "<tr>";
			combinedData += "	<thead class='table-primary'>";
			combinedData += "		<th>"+'Activity/Task'+"</th>";
			combinedData += "		<th style='white-sapce:nowrap;'>"+'Objective(s) Impacted'+"</th>";
			combinedData += "		<th colspan='2'>"+'Due Date'+"</th>";
			combinedData += "	</thead>";
			combinedData += "</tr>";
			
			while(taskCount <= data["Initiative Count"])
			{
				objectiveImpacted = "Objective"+taskCount;
				taskNumber = "Initiative"+taskCount;
				taskId = "InitiativeId"+taskCount;
				deliverable = "Deliverable"+taskCount;
				dueDate = "dueDate"+taskCount;
				//alert(data["dueDate1"]);
				bgColor = "Color"+taskCount;
				trafficLight = "trafficLight"+taskCount;
				//console.log("traffic light: " + trafficLight + " = " + data[trafficLight]);
				combinedData = combinedData + "<tr><td style='cursor: pointer;' class='link-primary' id='init"+data[taskId]+"' onClick='moreDetailsTask("+data[taskId]+")' onMouseOut='removeTooltip()'>"+data[taskNumber]+"</td><td>"+data[objectiveImpacted]+"</td><td style='white-space:nowrap;' class='border-end-0'>"+data[dueDate]+"</td><td class='border-start-0'><div class='"+data[trafficLight]+"'></td></div></tr>";
				taskCount++;
			}
			dojo.byId("measureContent").innerHTML = combinedData+"</table>";

			combinedData = null;
			//var cascadedScore;
			var bgColor;
			combinedData = "<table class='table table-sm table-condensed table-striped'>";
			combinedData = combinedData + "<tr><thead class='bg-primary' style='--bs-bg-opacity: .1;'><th>Cascaded From</th><th>Score</th></thead></tr>";
			//while(cascadedCount <= data["Cascaded Count"])
			//{
				//cascadedNumber = "Cascaded To"+cascadedCount;
				//if(cascadedCount==1){bgColor = "#009900"} else if (cascadedCount==2){bgColor = "#FFFF00"} else{bgColor = "red"}
				//console.log(data["Cascaded From Score"] + ' cascaded from ' + data["Cascaded From"]);
				if(data["Cascaded From Score"] == "grey")
				{
					bgColor = "table-secondary";
					data["Cascaded From Score"] = "No Score";
					//console.log(data["Cascaded From Score"] + "grey");
				}
				else if(data["Cascaded From Score"]>0 && data["Cascaded From Score"]<3.3){bgColor = "table-danger"}
				else if (data["Cascaded From Score"]>=3.3 && data["Cascaded From Score"]<=6.7){bgColor = "table-warning"}
				else if (data["Cascaded From Score"]>6.7 && data["Cascaded From Score"]<=10){bgColor = "table-success"}
				else {
					bgColor = "grey";
					data["Cascaded From Score"] = "No Score";
					//console.log(data["Cascaded From Score"] + "No Score");
				}
				if(data["Cascaded From"] == undefined) {data["Cascaded From"] = "No Department";bgColor = "table-secondary";}
				if(data["Cascaded From Score"] == undefined) {data["Cascaded From Score"] = "No Score";bgColor = "table-secondary";}
				combinedData = combinedData + "<tr><td>"+data["Cascaded From"]+"</td><td class='"+bgColor+"'>"+data["Cascaded From Score"]+"</td></tr>";
				//cascadedCount++;
			//}
			dojo.byId("cascadedContent").innerHTML = combinedData+"</table>";
			combinedData = null;
			var pdpCount = 1;
			var skillGap, intervention, pdpStartDate, pdpDueDate, resource, comments, pdpId, pdpColor;
			/*combinedData = "<table style='font-size: 13px; border-collapse: collapse; border-top: 1px solid #9baff1; border-bottom: 1px solid #9baff1;'><tr><td style='text-align:center; padding: 2px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'><strong>"+'Competency/Skill Gap'+"</strong></td><td style='text-align:center; padding: 2px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'><strong>"+'Intevention'+"</strong></td><td style='text-align:center; padding: 2px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'><strong>"+'Start Date'+"</strong></td><td style='text-align:center; padding: 2px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'><strong>"+'Due Date'+"</strong></td><td style='text-align:center; padding: 2px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'><strong>"+'Resource'+"</strong></td><td style='text-align:center; padding: 2px; border-right: 1px solid #aabcfe; border-left: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'><strong>"+'Comments'+"</strong></td><td></td><td style='text-align:center; padding: 2px; border-right: 1px solid #aabcfe; border-top: 1px solid #aabcfe;'></td></tr>";*/
			combinedData = "<table class='table table-sm table-condensed table-striped mb-0'>";
			combinedData += "<tr>";
			combinedData += "<thead class='bg-primary' style='--bs-bg-opacity: .1;'>";
			combinedData += "<th>Competency/Skill Gap</th>";
			combinedData += "<th>Intevention</th>";
			combinedData += "<th>Start Date</th>";
			combinedData += "<th style='text-align:center;' colspan='2'>Due Date</th>";
			combinedData += "<th>Resource</th>";
			combinedData += "<th>Comments</th>";
			combinedData += "<th colspan='2'></th>";
			combinedData += "</thead>";
			combinedData += "</tr>";

			while(pdpCount <= data["pdpCount"])
			{
			skillGap = "skillGap"+pdpCount;
			intervention = "intervention"+pdpCount;
			pdpStartDate = "pdpStartDate"+pdpCount;
			pdpDueDate = "pdpDueDate"+pdpCount;
			resource = "resource"+pdpCount;
			comments = "comments"+pdpCount;
			pdpColor = "pdpColor"+pdpCount;
			pdpId = "pdpId"+pdpCount;
			combinedData = combinedData + "<tr><td>"+data[skillGap]+"</td><td>"+data[intervention]+"</td><td>"+data[pdpStartDate]+"</td><td class='border-end-0'>"+data[pdpDueDate]+"</td><td class='border-start-0'><div class='"+data[pdpColor]+"'></div></td><td>"+data[resource]+"</td><td>"+data[comments]+"</td><td><a class='ml10' href='javascript:void(0)'  Title = 'Edit PDP' data-toggle='tooltip' onClick='editPdp("+data[pdpId]+");'><i class='fa fa-edit'></i></a></td><td><a class='ml10' href='javascript:void(0)' Title = 'Delete PDP' data-toggle='tooltip' onClick='savePdp(\"Delete\","+data[pdpId]+");'><i class='fa fa-trash'></i></a></td></tr>";
			
			/* <a class="ml10" href='javascript:void(0)' title='Edit Initiative' data-toggle='tooltip' onclick='editInitiative()'><i class='glyphicon glyphicon-edit'></i></a>
                        <a class='ml10' href='javascript:void(0)' title='Delete Initiative' data-toggle='tooltip' onclick='deleteInitiative()'><i class='glyphicon glyphicon-trash'></i></a>*/
			
			pdpCount++;
			}
			dojo.byId("pdp").innerHTML = combinedData+"</table>";

			moreDetailsTask = function(id)
			{
				getInitContent(id);
			}
			dom.byId("coreValueStaffId").innerHTML = objectId;
			request.post("scorecards/coreValues/getCoreValues.php?mainMenuState=Scorecard&staff="+objectId,{
				data: {}
			}).then(function(savedCoreValue)
			{
				dom.byId("coreValuesScorecardPage").innerHTML = savedCoreValue;
			});

			/*request.post("scorecards/coreValues/coreValues.php",{
				data: {objectId:objectId}
			}).then(function(coreValuesData)
			{
				console.log("Returning " + coreValuesData);
				dom.byId("coreValuesScorecardPage").innerHTML = coreValuesData;
			});*/
			
			break;
		}
		}
		});
}

getInitContent = function(id)
{
	var initId = "init"+id;
	request.post("initiatives/get-initiative.php",{
	// The URL of the request
	handleAs: "json",
	data: {
		initiativeId: id
	}
}).then(function(initiativeData)
{
	var initContent = "<table class='table table-striped table-responsive table-condensed table-sm table-bordered' style='margin-bottom:0px;'><tr><td><b>Name</b></td><td>"+initiativeData["name"] +"</td><td align='right'><b>Impacts</b></td><td>"+ initiativeData["link"] +"</td></tr><tr><td><b>Owner</b></td><td>"+ initiativeData["manager"] +"</td><td align='right'><b>Completion Date</b></td><td>"+ initiativeData["completionDate"] +"</td></tr><tr><td><b>Start Date</b></td><td>"+ initiativeData["startDate"] +"</td><td align='right'><b>Due Date</b></td><td>"+ initiativeData["dueDate"] + "</td></tr><tr><td><b>Budget</b></td><td>"+ initiativeData["budget"] +"</td><td align='right'><b>Cost So Far</b></td><td>"+ initiativeData["damage"] +"</td></tr><tr><td><b>Parent</b></td><td colspan='3'>"+initiativeData["parent"]+"</td></tr><tr><td><b>Deliverable</b></td><td colspan='3'>"+ initiativeData["deliverable"] +"</td></tr></table><div style='font-size:8px; text-align: right;'><i>id: "+id+"</i></div>";
	
objTooltipDialog.set("content", initContent);
popup.open({
		popup: objTooltipDialog,
		around: dom.byId(initId),
		orient: ["above"]
	});
	//dijit.byId('objTooltipDialog').destroyRecursive();
});	
}

getIndContent = function(id)
{
	var indId = "ind"+id;
	//console.log("Id = " + indId);
	request.post("individual/get-individual.php",{
	// The URL of the request
	handleAs: "json",
	data: {
		indId: indId,
		globalDate: globalDate
	}						
}).then(function(individualData)
{
	var indContent = "<table class='table table-striped table-responsive table-condensed table-sm table-bordered' style='margin-bottom:0px;'><tr><td><b>Name</b></td><td>"+individualData["name"] +"</td><td align='right'><b>Title</b></td><td>"+ individualData["title"] +"</td></tr><tr><td><b>Supervisor</b></td><td>"+ individualData["reportsTo"] +"</td><td align='right'><b>Department</b></td><td>"+ individualData["department"] +"</td></tr><tr><td><b>Current Score</b></td><td>"+ individualData["indScore"] +"</td><td align='right'><b>Previous Period Score<b></td><td>"+individualData["indScorePrevious"]+"</td></tr>"+individualData["initiatives"]+"</table><div style='font-size:8px; text-align: right;'><i>id: "+id+"</i></div>";
	
objTooltipDialog.set("content", indContent);
popup.open({
		popup: objTooltipDialog,
		around: dom.byId(indId),
		orient: ["above"]
	});
	//dijit.byId('objTooltipDialog').destroyRecursive();
});	
}

initiativeMain = function(tnAdd, objectType, objectName)
{
	domStyle.set(registry.byId("menuSeparator").domNode, 'display', 'inline-table');
	dojo.byId("dynamicMenu").innerHTML = "<i>Initiatives List</>";
	request.post("initiatives/get-initiative-list.php",{
	handleAs: "json",
	data: {
		objectId: tnAdd,
		objectType: objectType
	}
	}).then(function(initiativeListContent)
	{
		initiativeStore = new Memory({data:initiativeListContent});// using this to populate select when editing an initiative
		var count = 0;
		/*domConstruct.empty("testTd");
		var div = domConstruct.toDom("<strong>Gantt Chart</strong><div id = 'gantt' class= 'ganttContent'></div>");
		domConstruct.place(div, "testTd");*/
		if(initiativeListContent.length > 0)
		{
			var minDateResults = initiativeListContent[count];
			var myStartDate = new Date(minDateResults.minYear, minDateResults.minMonth, minDateResults.minDay);
		}
		else
		{
			var myStartDate = new Date();
		}
		
		//Initiative List
		var dataForInitiativeListDiv = "";

		while(count < initiativeListContent.length)
		{
			var initiativeResults = initiativeListContent[count];
			dataForInitiativeListDiv = dataForInitiativeListDiv + "<a href=\"#\" onClick=\"initiativeListFunction("+initiativeResults.id+");\">" + initiativeResults.name + "</a><br>";
			count++;
			var temp_date = new Date(initiativeResults.year, initiativeResults.month, initiativeResults.day);
		}
		if(initiativeListContent.length > 0) initiativeListFunction(initiativeListContent[0].id);
		
		//Task List
		var dataForTaskListDiv = "<select id='taskList' size='6' tabindex='1' style='width:100%; overflow-x:auto; border: 1px #9baff1 solid;' onChange='taskListFunction()'>";

		while(count < initiativeListContent.length)
		{
			var taskResults = initiativeListContent[count];
			dataForTaskListDiv = dataForTaskListDiv +
			"<option value='"+ taskResults.name + "'>" + taskResults.name + "</option>"
			count++;
		}
		dataForTaskListDiv = dataForTaskListDiv + "</select>";
		//dojo.byId("taskListDiv").innerHTML = dataForTaskListDiv;
		if (dijit.byId("myTooltipDialog"))
		{
			dijit.byId("myTooltipDialog").destroy(true);
		}
		if(initiativeListContent.length == 0) dataForInitiativeListDiv = "No Initiative for Selected Item";
		var initiativeListTimer = setTimeout(function()
		{
				var myTooltipDialog = new TooltipDialog({
				id: 'myTooltipDialog',
				//style: "width: 300px;",
				content: dataForInitiativeListDiv,
				onMouseLeave: function(){
					popup.close(myTooltipDialog);
				}
			});

			on(dom.byId('dynamicMenu'), 'mouseover', function(){
				popup.open({
					popup: myTooltipDialog,
					around: dom.byId('dynamicMenu')
				});
			});
		},100);			
	});
	//domStyle.set(dom.byId("divConversation"), "display", "none");
}
postComment = function()
{
	if(kpiGlobalType == "initiative") var conversationId = dom.byId("selectedElement").innerHTML;
	else if(kpiGlobalType == "advocacy") var conversationId = dojo.byId("advocacyIdDiv").innerHTML;
	else var conversationId = kpiGlobalId;
	
	var newNote;
	if(dom.byId("conversation").innerHTML == '') newNote = 'empty';
	else newNote = dom.byId("conversation").innerHTML;

	dom.byId("conversationHistory").innerHTML = '';
	
	request.post("scorecards/get-conversation.php",
	{
		handleAs: "json",
		data: {
		newNote: newNote,
		userId: dom.byId("userIdJs").innerHTML,
		kpiGlobalId: conversationId,
		kpiGlobalType: kpiGlobalType
	}
	}).then(function(commentary)
	{
		//alert(commentary);
		dom.byId("conversation").innerHTML = '';
		var commentaries = "<div class='border border-primary rounded-3' style='overflow:hidden;'><table class='table table-sm table-condensed table-responsive table-bordeless' id='notesTable'>";
		var header;
		for(var i = 0; i < commentary.length; i++)
		{
			if(commentary[i].senderId == dom.byId("userIdJs").innerHTML)
			{	
				header = "<tr class='table-success'><td class='border-right-0'>"+ commentary[i].sender +"</td><td class='border-left-0' style='text-align:right;'><i>Last Updated: "+ commentary[i].date +"</i>&nbsp;&nbsp;&nbsp;<a href='javascript:void(0)' title='Edit Conversation' onclick='toEdit("+ commentary[i].id +")'><i class='fa fa-edit'></i></a><a href='javascript:void(0)' title='Delete Conversation' onclick='toDelete("+ commentary[i].id +")'><i class='fa fa-trash'></i></a></td></tr>";

				commentaries = commentaries + header + "<tr><td colspan=3><div id='text"+commentary[i].id+"'>" + commentary[i].note + "</div>";
				commentaries = commentaries + "<div id=editDiv"+commentary[i].id+" style='display:none'><div contenteditable='true' style='width:100%; height:50px; padding:5px; border:1px solid #ccc;' id='editArea"+commentary[i].id+"'>"+ commentary[i].note +"</div>";
				commentaries = commentaries + '<button type="button" class="btn btn-outline-primary btn-sm" onclick="updateComment('+commentary[i].id+')">Update Comment</button>';
				commentaries = commentaries + "</div></td></tr>";
				//dom.byId("conversationHistory").innerHTML = dom.byId("conversationHistory").innerHTML + commentaries;
			}
			else
			{
				//var commentaries = "<table class='table table-condensed table-small table-bordered' id='notesTable'>";
			
				header = "<tr class='table-danger'><td class='border-right-0'>"+ commentary[i].sender +"</td><td class='border-left-0' style='text-align:right;'><i>Last Updated: "+ commentary[i].date +"</i>&nbsp;&nbsp;<i class='fa fa-fw'></i><i class='fa fa-fw'></i></td></tr>";

				commentaries = commentaries + header + "<tr><td colspan=3><div id='text"+commentary[i].id+"'>" + commentary[i].note + "</div></td></tr>";
			}
		}
		//alert(commentaries);
		dom.byId("conversationHistory").innerHTML = commentaries + "</table></div>";
		//domClass.add("button", "btn btn-primary-outline");
		
	});
}//end of post comment function

updateComment = function(id)
{
	var editId = "editDiv"+id;
	var editArea = "editArea"+id;
	//editArea = '\''+editArea+'\'';
	//alert('The id is: ' + editArea);
	var textId = "text"+id;
	domStyle.set(dom.byId(editId), "display", "none");
	domStyle.set(dom.byId(textId), "display", "block");
	//alert(dom.byId(editArea).value + id);
	request.post("scorecards/update-conversation.php",
	{
	//handleAs: "json",
	data: {
		id: id,
		note: dom.byId(editArea).innerHTML
	}
	}).then(function()
	{
		dom.byId("conversationHistory").innerHTML = '';
		dom.byId("conversation").innerHTML = '';
		postComment();
		//alert("Item deleted no " + id);
	})
}
toEdit = function(id)
{
	var editId = "editDiv"+id;
	var textId = "text"+id;
	//editId = ""+editId;
	//textId = ""+textId;
	//console.log("editId = " + editId + ", id = " + id);
	domStyle.set(dom.byId(editId), "display", "block");
	domStyle.set(dom.byId(textId), "display", "none");
	/*var buttonWait = setTimeout(function(){
		$("button").addClass("btn btn-primary-outline");
	},500);*/
}
toDelete = function(id)
{
	//alert(id);
	request.post("layout/delete-conversation.php",
	{
	//handleAs: "json",
	data: {
		id: id
	}
	}).then(function()
	{
		dom.byId("conversationHistory").innerHTML = '';
		dom.byId("conversation").innerHTML = '';
		postComment();
		//alert("Item deleted no " + id);
	})
}

});
