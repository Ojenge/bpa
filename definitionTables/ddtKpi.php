<!DOCTYPE html>
<html >
<head>
<link rel="stylesheet" href="../dijit/themes/claro/claro.css">
<style>
label{display:block; float:left;}​ <!--or: textarea { vertical-align: top; }​-->
textarea{
    border: none;
    width: 100%;
    -webkit-box-sizing: border-box; /* <=iOS4, <= Android  2.3 */
    -moz-box-sizing: border-box; /* FF1+ */
    box-sizing: border-box; /* Chrome, IE8, Opera, Safari 5.1*/
    }
</style>
<script>
require(["dojo/store/Memory", "dojo/json", "dojo/request", "dojox/mvc/equals", "dijit/form/FilteringSelect", "dijit/form/Button", "dijit/form/DateTextBox", "dojo/domReady!"], 
function(Memory, json, request, equals, FilteringSelect){
	
var storeData, objStore, objSelect, goalStore, unitStore, processStore, dataItemStore;

request.post("get-kpi-commentary.php",{
handleAs: "json",
data: {
}						
}).then(function(data) 
{
	//alert(data);
	storeData = data;
	//alert(storeData + storeData2);
  
var kpiStore = new Memory({data: storeData});

var totalItems = storeData.length;   // How many total items should we expect.
var currentCount = 1;     // Current size of the page.
var newItem = "False";
var objLinkedTo = "org4";
var edit;

var firstObject = kpiStore.get(currentCount);
//alert(firstObject);
//alert(firstObject.dateCreated);
dojo.byId("kpiNo").innerHTML = firstObject.id;
dojo.byId("kpiName").value = firstObject.kpiName;
dojo.byId("kpiQuantitative").value = firstObject.kpiQuantitative;
dojo.byId("dateCreated").value = firstObject.dateCreated;
dojo.byId("defStatus").value = firstObject.defStatus;
dojo.byId("dateModified").value = firstObject.dateModified;
dojo.byId("reportStatus").value = firstObject.reportStatus;
dojo.byId("abbreviation").value = firstObject.abbreviation;
dojo.byId("lifePriority").value = firstObject.lifePriority;
dojo.byId("kpiIntegrity").value = firstObject.kpiIntegrity;
dojo.byId("kpiObjective").value = firstObject.kpiObjective;
dojo.byId("kpiLevel").value = firstObject.kpiLevel;
dojo.byId("kpiGoal").value = firstObject.kpiGoal;
dojo.byId("kpiUnit").value = firstObject.kpiUnit;
dojo.byId("kpiDescr").value = firstObject.kpiDescr;
dojo.byId("kpiIntent").value = firstObject.kpiIntent;
dojo.byId("kpiProcess").value = firstObject.kpiProcess;
dojo.byId("kpiStakeholder").value = firstObject.kpiStakeholder;
dojo.byId("kpiRelationship").value = firstObject.kpiRelationship;
dojo.byId("kpiFormula").value = firstObject.kpiFormula;
dojo.byId("kpiFrequency").value = firstObject.kpiFrequency;
dojo.byId("kpiDrill").value = firstObject.kpiDrill;
dojo.byId("kpiComparison").value = firstObject.kpiComparison;
dojo.byId("kpiMethod").value = firstObject.kpiMethod;
dojo.byId("kpiPresentNotes").value = firstObject.kpiPresentNotes;
dojo.byId("kpiFrequency2").value = firstObject.kpiFrequency2;
dojo.byId("kpiResponse").value = firstObject.kpiResponse;
dojo.byId("kpiOwnerDefinition").value = firstObject.kpiOwnerDefinition;
dojo.byId("kpiOwnerPerformance").value = firstObject.kpiOwnerPerformance;
dojo.byId("kpiNotes").value = firstObject.kpiNotes;
dojo.byId("kpiOwnerReporting").value = firstObject.kpiOwnerReporting;

previousPage = function()
{
	//editData["objLinkedTo"] = "org4";
	var editData = {};
	editData["id"] = currentCount;
	//dojo.byId("kpiNo").innerHTML;
	editData["kpiName"] = dojo.byId("kpiName").value;
	editData["kpiQuantitative"] = dojo.byId("kpiQuantitative").value;
	editData["dateCreated"] = dojo.byId("dateCreated").value;
	editData["defStatus"] = dojo.byId("defStatus").value;
	editData["dateModified"] = dojo.byId("dateModified").value;
	editData["reportStatus"] = dojo.byId("reportStatus").value;
	editData["abbreviation"] = dojo.byId("abbreviation").value;
	editData["lifePriority"] = dojo.byId("lifePriority").value;
	editData["kpiIntegrity"] = dojo.byId("kpiIntegrity").value;
	editData["kpiObjective"] = dojo.byId("kpiObjective").value;
	editData["kpiLevel"] = dojo.byId("kpiLevel").value;
	editData["kpiGoal"] = dojo.byId("kpiGoal").value;
	editData["kpiUnit"] = dojo.byId("kpiUnit").value;
	editData["kpiDescr"] = dojo.byId("kpiDescr").value;
	editData["kpiIntent"] = dojo.byId("kpiIntent").value;
	editData["kpiProcess"] = dojo.byId("kpiProcess").value;
	editData["kpiStakeholder"] = dojo.byId("kpiStakeholder").value;
	editData["kpiRelationship"] = dojo.byId("kpiRelationship").value;
	editData["kpiFormula"] = dojo.byId("kpiFormula").value;
	editData["kpiFrequency"] = dojo.byId("kpiFrequency").value;
	editData["kpiDrill"] = dojo.byId("kpiDrill").value;
	editData["kpiComparison"] = dojo.byId("kpiComparison").value;
	editData["kpiMethod"] = dojo.byId("kpiMethod").value;
	editData["kpiPresentNotes"] = dojo.byId("kpiPresentNotes").value;
	editData["kpiFrequency2"] = dojo.byId("kpiFrequency2").value;
	editData["kpiResponse"] = dojo.byId("kpiResponse").value;
	editData["kpiOwnerDefinition"] = dojo.byId("kpiOwnerDefinition").value;
	editData["kpiOwnerPerformance"] = dojo.byId("kpiOwnerPerformance").value;
	editData["kpiNotes"] = dojo.byId("kpiNotes").value;
	editData["kpiOwnerReporting"] = dojo.byId("kpiOwnerReporting").value;
	
	var previousData = kpiStore.get(currentCount);
	
	var isSimilar = equals(editData, previousData);
	if(isSimilar)
		edit = "False";
	else
		{
			kpiStore.remove(editData["id"]);
					
			kpiStore.put({id:editData["id"], kpiName: editData["kpiName"], kpiQuantitative: editData["kpiQuantitative"], dateCreated: editData["dateCreated"], defStatus: editData["defStatus"], dateModified: editData["dateModified"], reportStatus: editData["reportStatus"],	abbreviation: editData["abbreviation"], lifePriority: editData["lifePriority"], kpiIntegrity: editData["kpiIntegrity"],	kpiObjective: editData["kpiObjective"],kpiLevel: editData["kpiLevel"],	kpiGoal: editData["kpiGoal"], kpiUnit: editData["kpiUnit"], kpiDescr: editData["kpiDescr"],kpiIntent: editData["kpiIntent"], kpiProcess: editData["kpiProcess"], kpiStakeholder: editData["kpiStakeholder"],	kpiRelationship: editData["kpiRelationship"], kpiFormula: editData["kpiFormula"],	kpiFrequency: editData["kpiFrequency"],	kpiDrill: editData["kpiDrill"],	kpiComparison: editData["kpiComparison"], kpiMethod: editData["kpiMethod"], kpiPresentNotes: editData["kpiPresentNotes"],	kpiFrequency2: editData["kpiFrequency2"], kpiResponse: editData["kpiResponse"], kpiOwnerDefinition: editData["kpiOwnerDefinition"], kpiOwnerPerformance: editData["kpiOwnerPerformance"], kpiNotes: editData["kpiNotes"], kpiOwnerReporting: editData["kpiOwnerReporting"]});
			
			edit = "True";
			request.post("save-kpi-commentary.php",{
			//handleAs: "json",
			data: {
				edit: edit,
				id:editData["id"],
				kpiName: editData["kpiName"],
				
				kpiQuantitative	: editData["kpiQuantitative"],
				dateCreated	: editData["dateCreated"],
				defStatus : editData["defStatus"],
				dateModified : editData["dateModified"],
				reportStatus : editData["reportStatus"],
				abbreviation : editData["abbreviation"],
				lifePriority : editData["lifePriority"],
				kpiIntegrity : editData["kpiIntegrity"],
				kpiObjective : editData["kpiObjective"],
				kpiLevel : editData["kpiLevel"],
				kpiGoal : editData["kpiGoal"],
				kpiUnit : editData["kpiUnit"],
				kpiDescr : editData["kpiDescr"],
				kpiIntent : editData["kpiIntent"],
				kpiProcess : editData["kpiProcess"],
				kpiStakeholder : editData["kpiStakeholder"],
				kpiRelationship : editData["kpiRelationship"],
				kpiFormula : editData["kpiFormula"],
				kpiFrequency : editData["kpiFrequency"],
				kpiDrill : editData["kpiDrill"],
				kpiComparison : editData["kpiComparison"],
				kpiMethod : editData["kpiMethod"],
				kpiPresentNotes : editData["kpiPresentNotes"],
				kpiFrequency2 : editData["kpiFrequency2"],
				kpiResponse : editData["kpiResponse"],
				kpiOwnerDefinition : editData["kpiOwnerDefinition"],
				kpiOwnerPerformance : editData["kpiOwnerPerformance"],
				kpiNotes : editData["kpiNotes"],
				kpiOwnerReporting : editData["kpiOwnerReporting"]
			}						
			}).then(function() 
			{
				edit = "False";
			});		
		}
			
	if(newItem == "True" && dojo.byId("kpiName").value != "")//add and save new item
	{
		var newId = totalItems + 1;
		var newkpiName = dojo.byId("kpiName").value;
		var newkpiQuantitative = dojo.byId("kpiQuantitative").value;
		var newdateCreated = dojo.byId("dateCreated").value;
		var newdefStatus = dojo.byId("defStatus").value;
		var newdateModified = dojo.byId("dateModified").value;
		var newreportStatus = dojo.byId("reportStatus").value;
		var newabbreviation = dojo.byId("abbreviation").value;
		var newlifePriority = dojo.byId("lifePriority").value;
		var newkpiIntegrity = dojo.byId("kpiIntegrity").value;
		var newkpiObjective = dojo.byId("kpiObjective").value;
		var newkpiLevel = dojo.byId("kpiLevel").value;
		var newkpiGoal = dojo.byId("kpiGoal").value;
		var newkpiUnit = dojo.byId("kpiUnit").value;
		var newkpiDescr = dojo.byId("kpiDescr").value;
		var newkpiIntent = dojo.byId("kpiIntent").value;
		var newkpiProcess = dojo.byId("kpiProcess").value;
		var newkpiStakeholder = dojo.byId("kpiStakeholder").value;
		var newkpiRelationship = dojo.byId("kpiRelationship").value;
		var newkpiFormula = dojo.byId("kpiFormula").value;
		var newkpiFrequency = dojo.byId("kpiFrequency").value;
		var newkpiDrill = dojo.byId("kpiDrill").value;
		var newkpiComparison = dojo.byId("kpiComparison").value;
		var newkpiMethod = dojo.byId("kpiMethod").value;
		var newkpiPresentNotes = dojo.byId("kpiPresentNotes").value;
		var newkpiFrequency2 = dojo.byId("kpiFrequency2").value;
		var newkpiResponse = dojo.byId("kpiResponse").value;
		var newkpiOwnerDefinition = dojo.byId("kpiOwnerDefinition").value;
		var newkpiOwnerPerformance = dojo.byId("kpiOwnerPerformance").value;
		var newkpiNotes = dojo.byId("kpiNotes").value;
		var newkpiOwnerReporting = dojo.byId("kpiOwnerReporting").value;
		
		//var objLinkedTo = dojo.byId("objLinkedTo").value;
		//alert("Save details");
		
		kpiStore.put({id:newid, kpiName: newkpiName, kpiQuantitative: newkpiQuantitative, dateCreated: newdateCreated, defStatus: newdefStatus, dateModified: newdateModified, reportStatus: newreportStatus,	abbreviation: newabbreviation, lifePriority: newlifePriority, kpiIntegrity: newkpiIntegrity, kpiObjective: newkpiObjective,	kpiLevel: newkpiLevel,	kpiGoal: newkpiGoal, kpiUnit: newkpiUnit, kpiDescr: newkpiDescr,kpiIntent: newkpiIntent, kpiProcess: newkpiProcess, kpiStakeholder: newkpiStakeholder,	kpiRelationship: newkpiRelationship, kpiFormula: newkpiFormula,	kpiFrequency: newkpiFrequency,	kpiDrill: newkpiDrill,	kpiComparison: newkpiComparison, kpiMethod: newkpiMethod, kpiPresentNotes: newkpiPresentNotes,	kpiFrequency2: newkpiFrequency2, kpiResponse: newkpiResponse, kpiOwnerDefinition: newkpiOwnerDefinition, kpiOwnerPerformance: newkpiOwnerPerformance, kpiNotes: newkpiNotes, kpiOwnerReporting: newkpiOwnerReporting});
		
		request.post("save-kpi-commentary.php",{
		//handleAs: "json",
		data: {
				newId : newId,
				newkpiName : newkpiName,
				newkpiQuantitative : newkpiQuantitative,
				newdateCreated : newdateCreated,
				newdefStatus : newdefStatus,
				newdateModified : newdateModified,
				newreportStatus : newreportStatus,
				newabbreviation : newabbreviation,
				newlifePriority : newlifePriority,
				newkpiIntegrity : newkpiIntegrity,
				newkpiObjective : newkpiObjective,
				newkpiLevel : newkpiLevel,
				newkpiGoal : newkpiGoal,
				newkpiUnit : newkpiUnit,
				newkpiDescr : newkpiDescr,
				newkpiIntent : newkpiIntent,
				newkpiProcess : newkpiProcess,
				newkpiStakeholder : newkpiStakeholder,
				newkpiRelationship : newkpiRelationship,
				newkpiFormula : newkpiFormula,
				newkpiFrequency : newkpiFrequency,
				newkpiDrill : newkpiDrill,
				newkpiComparison : newkpiComparison,
				newkpiMethod : newkpiMethod,
				newkpiPresentNotes : newkpiPresentNotes,
				newkpiFrequency2 : newkpiFrequency2,
				newkpiResponse : newkpiResponse,
				newkpiOwnerDefinition : newkpiOwnerDefinition,
				newkpiOwnerPerformance : newkpiOwnerPerformance,
				newkpiNotes : newkpiNotes,
				newkpiOwnerReporting : newkpiOwnerReporting
			}						
		}).then(function(data) 
		{
			//alert(data);
		});	
		newItem = "False";
		totalItems = newId;
	}
	currentCount--;
	if(currentCount <= 0)
	{
		currentCount = 1;
		return;
	}
	firstObject = kpiStore.get(currentCount);
	
	dojo.byId("kpiNo").innerHTML = firstObject.id;
	dojo.byId("kpiName").value = firstObject.kpiName;
	dojo.byId("kpiQuantitative").value = firstObject.kpiQuantitative;
	dojo.byId("dateCreated").value = firstObject.dateCreated;
	dojo.byId("defStatus").value = firstObject.defStatus;
	dojo.byId("dateModified").value = firstObject.dateModified;
	dojo.byId("reportStatus").value = firstObject.reportStatus;
	dojo.byId("abbreviation").value = firstObject.abbreviation;
	dojo.byId("lifePriority").value = firstObject.lifePriority;
	dojo.byId("kpiIntegrity").value = firstObject.kpiIntegrity;
	dojo.byId("kpiObjective").value = firstObject.kpiObjective;
	dojo.byId("kpiLevel").value = firstObject.kpiLevel;
	dojo.byId("kpiGoal").value = firstObject.kpiGoal;
	dojo.byId("kpiUnit").value = firstObject.kpiUnit;
	dojo.byId("kpiDescr").value = firstObject.kpiDescr;
	dojo.byId("kpiIntent").value = firstObject.kpiIntent;
	dojo.byId("kpiProcess").value = firstObject.kpiProcess;
	dojo.byId("kpiStakeholder").value = firstObject.kpiStakeholder;
	dojo.byId("kpiRelationship").value = firstObject.kpiRelationship;
	dojo.byId("kpiFormula").value = firstObject.kpiFormula;
	dojo.byId("kpiFrequency").value = firstObject.kpiFrequency;
	dojo.byId("kpiDrill").value = firstObject.kpiDrill;
	dojo.byId("kpiComparison").value = firstObject.kpiComparison;
	dojo.byId("kpiMethod").value = firstObject.kpiMethod;
	dojo.byId("kpiPresentNotes").value = firstObject.kpiPresentNotes;
	dojo.byId("kpiFrequency2").value = firstObject.kpiFrequency2;
	dojo.byId("kpiResponse").value = firstObject.kpiResponse;
	dojo.byId("kpiOwnerDefinition").value = firstObject.kpiOwnerDefinition;
	dojo.byId("kpiOwnerPerformance").value = firstObject.kpiOwnerPerformance;
	dojo.byId("kpiNotes").value = firstObject.kpiNotes;
	dojo.byId("kpiOwnerReporting").value = firstObject.kpiOwnerReporting;
}
nextPage = function()
{
	var editData = {};
	editData["id"] = currentCount;
	editData["kpiName"] = dojo.byId("kpiName").value;
	editData["kpiQuantitative"] = dojo.byId("kpiQuantitative").value;
	editData["dateCreated"] = dojo.byId("dateCreated").value;
	editData["defStatus"] = dojo.byId("defStatus").value;
	editData["dateModified"] = dojo.byId("dateModified").value;
	editData["reportStatus"] = dojo.byId("reportStatus").value;
	editData["abbreviation"] = dojo.byId("abbreviation").value;
	editData["lifePriority"] = dojo.byId("lifePriority").value;
	editData["kpiIntegrity"] = dojo.byId("kpiIntegrity").value;
	editData["kpiObjective"] = dojo.byId("kpiObjective").value;
	editData["kpiLevel"] = dojo.byId("kpiLevel").value;
	editData["kpiGoal"] = dojo.byId("kpiGoal").value;
	editData["kpiUnit"] = dojo.byId("kpiUnit").value;
	editData["kpiDescr"] = dojo.byId("kpiDescr").value;
	editData["kpiIntent"] = dojo.byId("kpiIntent").value;
	editData["kpiProcess"] = dojo.byId("kpiProcess").value;
	editData["kpiStakeholder"] = dojo.byId("kpiStakeholder").value;
	editData["kpiRelationship"] = dojo.byId("kpiRelationship").value;
	editData["kpiFormula"] = dojo.byId("kpiFormula").value;
	editData["kpiFrequency"] = dojo.byId("kpiFrequency").value;
	editData["kpiDrill"] = dojo.byId("kpiDrill").value;
	editData["kpiComparison"] = dojo.byId("kpiComparison").value;
	editData["kpiMethod"] = dojo.byId("kpiMethod").value;
	editData["kpiPresentNotes"] = dojo.byId("kpiPresentNotes").value;
	editData["kpiFrequency2"] = dojo.byId("kpiFrequency2").value;
	editData["kpiResponse"] = dojo.byId("kpiResponse").value;
	editData["kpiOwnerDefinition"] = dojo.byId("kpiOwnerDefinition").value;
	editData["kpiOwnerPerformance"] = dojo.byId("kpiOwnerPerformance").value;
	editData["kpiNotes"] = dojo.byId("kpiNotes").value;
	editData["kpiOwnerReporting"] = dojo.byId("kpiOwnerReporting").value;
	
	var previousData = kpiStore.get(currentCount);
	
	var isSimilar = equals(editData, previousData);
	if(isSimilar)
		edit = "False";
	else
		{
			kpiStore.remove(editData["id"]);
			
			kpiStore.put({id:editData["id"], kpiName: editData["kpiName"], kpiQuantitative: editData["kpiQuantitative"], dateCreated: editData["dateCreated"], defStatus: editData["defStatus"], dateModified: editData["dateModified"], reportStatus: editData["reportStatus"],	abbreviation: editData["abbreviation"], lifePriority: editData["lifePriority"], kpiIntegrity: editData["kpiIntegrity"], kpiObjective: editData["kpiObjective"],	kpiLevel: editData["kpiLevel"],	kpiGoal: editData["kpiGoal"], kpiUnit: editData["kpiUnit"], kpiDescr: editData["kpiDescr"],kpiIntent: editData["kpiIntent"], kpiProcess: editData["kpiProcess"], kpiStakeholder: editData["kpiStakeholder"],	kpiRelationship: editData["kpiRelationship"], kpiFormula: editData["kpiFormula"],	kpiFrequency: editData["kpiFrequency"],	kpiDrill: editData["kpiDrill"],	kpiComparison: editData["kpiComparison"], kpiMethod: editData["kpiMethod"], kpiPresentNotes: editData["kpiPresentNotes"],	kpiFrequency2: editData["kpiFrequency2"], kpiResponse: editData["kpiResponse"], kpiOwnerDefinition: editData["kpiOwnerDefinition"], kpiOwnerPerformance: editData["kpiOwnerPerformance"], kpiNotes: editData["kpiNotes"], kpiOwnerReporting: editData["kpiOwnerReporting"]});
			
			edit = "True";
			request.post("save-kpi-commentary.php",{
			//handleAs: "json",
			data: {
				edit: edit,
				id:editData["id"],
				kpiName: editData["kpiName"],
				
				kpiQuantitative	: editData["kpiQuantitative"],
				dateCreated	: editData["dateCreated"],
				defStatus : editData["defStatus"],
				dateModified : editData["dateModified"],
				reportStatus : editData["reportStatus"],
				abbreviation : editData["abbreviation"],
				lifePriority : editData["lifePriority"],
				kpiIntegrity : editData["kpiIntegrity"],
				kpiObjective : editData["kpiObjective"],
				kpiLevel : editData["kpiLevel"],
				kpiGoal : editData["kpiGoal"],
				kpiUnit : editData["kpiUnit"],
				kpiDescr : editData["kpiDescr"],
				kpiIntent : editData["kpiIntent"],
				kpiProcess : editData["kpiProcess"],
				kpiStakeholder : editData["kpiStakeholder"],
				kpiRelationship : editData["kpiRelationship"],
				kpiFormula : editData["kpiFormula"],
				kpiFrequency : editData["kpiFrequency"],
				kpiDrill : editData["kpiDrill"],
				kpiComparison : editData["kpiComparison"],
				kpiMethod : editData["kpiMethod"],
				kpiPresentNotes : editData["kpiPresentNotes"],
				kpiFrequency2 : editData["kpiFrequency2"],
				kpiResponse : editData["kpiResponse"],
				kpiOwnerDefinition : editData["kpiOwnerDefinition"],
				kpiOwnerPerformance : editData["kpiOwnerPerformance"],
				kpiNotes : editData["kpiNotes"],
				kpiOwnerReporting : editData["kpiOwnerReporting"]
			}						
			}).then(function() 
			{
				edit = "False";
			});		
		}
	
	if(newItem == "True" && dojo.byId("kpiName").value != "")//add and save new item
	{
		var newId = totalItems + 1;
		var newkpiName = dojo.byId("kpiName").value;
		var newkpiQuantitative = dojo.byId("kpiQuantitative").value;
		var newdateCreated = dojo.byId("dateCreated").value;
		var newdefStatus = dojo.byId("defStatus").value;
		var newdateModified = dojo.byId("dateModified").value;
		var newreportStatus = dojo.byId("reportStatus").value;
		var newabbreviation = dojo.byId("abbreviation").value;
		var newlifePriority = dojo.byId("lifePriority").value;
		var newkpiIntegrity = dojo.byId("kpiIntegrity").value;
		var newkpiObjective = dojo.byId("kpiObjective").value;
		var newkpiLevel = dojo.byId("kpiLevel").value;
		var newkpiGoal = dojo.byId("kpiGoal").value;
		var newkpiUnit = dojo.byId("kpiUnit").value;
		var newkpiDescr = dojo.byId("kpiDescr").value;
		var newkpiIntent = dojo.byId("kpiIntent").value;
		var newkpiProcess = dojo.byId("kpiProcess").value;
		var newkpiStakeholder = dojo.byId("kpiStakeholder").value;
		var newkpiRelationship = dojo.byId("kpiRelationship").value;
		var newkpiFormula = dojo.byId("kpiFormula").value;
		var newkpiFrequency = dojo.byId("kpiFrequency").value;
		var newkpiDrill = dojo.byId("kpiDrill").value;
		var newkpiComparison = dojo.byId("kpiComparison").value;
		var newkpiMethod = dojo.byId("kpiMethod").value;
		var newkpiPresentNotes = dojo.byId("kpiPresentNotes").value;
		var newkpiFrequency2 = dojo.byId("kpiFrequency2").value;
		var newkpiResponse = dojo.byId("kpiResponse").value;
		var newkpiOwnerDefinition = dojo.byId("kpiOwnerDefinition").value;
		var newkpiOwnerPerformance = dojo.byId("kpiOwnerPerformance").value;
		var newkpiNotes = dojo.byId("kpiNotes").value;
		var newkpiOwnerReporting = dojo.byId("kpiOwnerReporting").value;
		//var objLinkedTo = dojo.byId("objLinkedTo").value;
		//alert("Save details");
		
		kpiStore.put({id:newId, kpiName: newkpiName, kpiQuantitative: newkpiQuantitative, dateCreated: newdateCreated, defStatus: newdefStatus, dateModified: newdateModified, reportStatus: newreportStatus,	abbreviation: newabbreviation, lifePriority: newlifePriority, kpiIntegrity: newkpiIntegrity, kpiObjective: newkpiObjective,	kpiLevel: newkpiLevel,	kpiGoal: newkpiGoal, kpiUnit: newkpiUnit, kpiDescr: newkpiDescr,kpiIntent: newkpiIntent, kpiProcess: newkpiProcess, kpiStakeholder: newkpiStakeholder,	kpiRelationship: newkpiRelationship, kpiFormula: newkpiFormula,	kpiFrequency: newkpiFrequency,	kpiDrill: newkpiDrill,	kpiComparison: newkpiComparison, kpiMethod: newkpiMethod, kpiPresentNotes: newkpiPresentNotes,	kpiFrequency2: newkpiFrequency2, kpiResponse: newkpiResponse, kpiOwnerDefinition: newkpiOwnerDefinition, kpiOwnerPerformance: newkpiOwnerPerformance, kpiNotes: newkpiNotes, kpiOwnerReporting: newkpiOwnerReporting});
		
		request.post("save-kpi-commentary.php",{
		//handleAs: "json",
		data: {
				newId : newId,
				newkpiName : newkpiName,
				newkpiQuantitative : newkpiQuantitative,
				newdateCreated : newdateCreated,
				newdefStatus : newdefStatus,
				newdateModified : newdateModified,
				newreportStatus : newreportStatus,
				newabbreviation : newabbreviation,
				newlifePriority : newlifePriority,
				newkpiIntegrity : newkpiIntegrity,
				newkpiObjective : newkpiObjective,
				newkpiLevel : newkpiLevel,
				newkpiGoal : newkpiGoal,
				newkpiUnit : newkpiUnit,
				newkpiDescr : newkpiDescr,
				newkpiIntent : newkpiIntent,
				newkpiProcess : newkpiProcess,
				newkpiStakeholder : newkpiStakeholder,
				newkpiRelationship : newkpiRelationship,
				newkpiFormula : newkpiFormula,
				newkpiFrequency : newkpiFrequency,
				newkpiDrill : newkpiDrill,
				newkpiComparison : newkpiComparison,
				newkpiMethod : newkpiMethod,
				newkpiPresentNotes : newkpiPresentNotes,
				newkpiFrequency2 : newkpiFrequency2,
				newkpiResponse : newkpiResponse,
				newkpiOwnerDefinition : newkpiOwnerDefinition,
				newkpiOwnerPerformance : newkpiOwnerPerformance,
				newkpiNotes : newkpiNotes,
				newkpiOwnerReporting : newkpiOwnerReporting
			}						
		}).then(function(data) 
		{
			//alert(data);
		});	
		newItem = "False";
		totalItems = newId;
	}
	currentCount++;
	if(currentCount > totalItems)//create new page for a new objective commentary
	{
		dojo.byId("kpiName").value = null;
		
		currentCount = totalItems+1;
		dojo.byId("kpiNo").innerHTML = currentCount;
		dojo.byId("kpiName").value = null;
		dojo.byId("kpiQuantitative").value = null;
		dojo.byId("dateCreated").value = null;
		dojo.byId("defStatus").value = null;
		dojo.byId("dateModified").value = null;
		dojo.byId("reportStatus").value = null;
		dojo.byId("abbreviation").value = null;
		dojo.byId("lifePriority").value = null;
		dojo.byId("kpiIntegrity").value = null;
		dojo.byId("kpiObjective").value = null;
		dojo.byId("kpiLevel").value = null;
		dojo.byId("kpiGoal").value = null;
		dojo.byId("kpiUnit").value = null;
		dojo.byId("kpiDescr").value = null;
		dojo.byId("kpiIntent").value = null;
		dojo.byId("kpiProcess").value = null;
		dojo.byId("kpiStakeholder").value = null;
		dojo.byId("kpiRelationship").value = null;
		dojo.byId("kpiFormula").value = null;
		dojo.byId("kpiFrequency").value = null;
		dojo.byId("kpiDrill").value = null;
		dojo.byId("kpiComparison").value = null;
		dojo.byId("kpiMethod").value = null;
		dojo.byId("kpiPresentNotes").value = null;
		dojo.byId("kpiFrequency2").value = null;
		dojo.byId("kpiResponse").value = null;
		dojo.byId("kpiOwnerDefinition").value = null;
		dojo.byId("kpiOwnerPerformance").value = null;
		dojo.byId("kpiNotes").value = null;
		dojo.byId("kpiOwnerReporting").value = null;
		
		newItem = "True";
	}
	else
	{
		firstObject = kpiStore.get(currentCount);
		dojo.byId("kpiNo").innerHTML = firstObject.id;
		dojo.byId("kpiName").value = firstObject.kpiName;
		dojo.byId("kpiQuantitative").value = firstObject.kpiQuantitative;
		dojo.byId("dateCreated").value = firstObject.dateCreated;
		dojo.byId("defStatus").value = firstObject.defStatus;
		dojo.byId("dateModified").value = firstObject.dateModified;
		dojo.byId("reportStatus").value = firstObject.reportStatus;
		dojo.byId("abbreviation").value = firstObject.abbreviation;
		dojo.byId("lifePriority").value = firstObject.lifePriority;
		dojo.byId("kpiIntegrity").value = firstObject.kpiIntegrity;
		dojo.byId("kpiObjective").value = firstObject.kpiObjective;
		dojo.byId("kpiLevel").value = firstObject.kpiLevel;
		dojo.byId("kpiGoal").value = firstObject.kpiGoal;
		dojo.byId("kpiUnit").value = firstObject.kpiUnit;
		dojo.byId("kpiDescr").value = firstObject.kpiDescr;
		dojo.byId("kpiIntent").value = firstObject.kpiIntent;
		dojo.byId("kpiProcess").value = firstObject.kpiProcess;
		dojo.byId("kpiStakeholder").value = firstObject.kpiStakeholder;
		dojo.byId("kpiRelationship").value = firstObject.kpiRelationship;
		dojo.byId("kpiFormula").value = firstObject.kpiFormula;
		dojo.byId("kpiFrequency").value = firstObject.kpiFrequency;
		dojo.byId("kpiDrill").value = firstObject.kpiDrill;
		dojo.byId("kpiComparison").value = firstObject.kpiComparison;
		dojo.byId("kpiMethod").value = firstObject.kpiMethod;
		dojo.byId("kpiPresentNotes").value = firstObject.kpiPresentNotes;
		dojo.byId("kpiFrequency2").value = firstObject.kpiFrequency2;
		dojo.byId("kpiResponse").value = firstObject.kpiResponse;
		dojo.byId("kpiOwnerDefinition").value = firstObject.kpiOwnerDefinition;
		dojo.byId("kpiOwnerPerformance").value = firstObject.kpiOwnerPerformance;
		dojo.byId("kpiNotes").value = firstObject.kpiNotes;
		dojo.byId("kpiOwnerReporting").value = firstObject.kpiOwnerReporting;
	}
}
deletePage = function()
{
	kpiStore.remove(currentCount);
	//alert("test:" + currentCount);
	request.post("save-kpi-commentary.php",{
	//handleAs: "json",
	data: {
		delCommentary: "True",
		id: currentCount
	}						
	}).then(function(data) 
	{
		//alert("Deleted? "+data);
		delCommentary = "False";
	});	
}

});

request.post("layout/get-objectives.php",{
handleAs: "json",
data: {
}						
}).then(function(objData)
{		
	objStore = new Memory({data:objData});
	
	objSelect = new FilteringSelect({
	name: "objSelectManager",
	store: objStore,
	searchAttr: "Objective",
	maxHeight: -1, 
	onChange: function(){
		objId = this.item.id;
	}
	}, "kpiObjective");
	objSelect.startup();
});

request.post("get-goals.php",{
handleAs: "json",
data: {
}						
}).then(function(goalData)
{		
	if(goalData.length == 0)
	dojo.byId("kpiGoal").value = "Empty. Click on (view goals) to add";
	else
	{
		goalStore = new Memory({data:goalData});
		
		goalSelect = new FilteringSelect({
		name: "goalSelectManager",
		store: goalStore,
		searchAttr: "Goal",
		maxHeight: -1, 
		onChange: function(){
			goalId = this.item.id;
		}
		}, "kpiGoal");
		goalSelect.startup();
	}
});
request.post("get-work-units.php",{
handleAs: "json",
data: {
}						
}).then(function(unitData)
{	
	if(unitData.length == 0)
	dojo.byId("kpiUnit").value = "Empty. Click on (view work units) to add";
	else
	{	
		unitStore = new Memory({data:unitData});
		
		unitSelect = new FilteringSelect({
		name: "unitSelectManager",
		store: unitStore,
		searchAttr: "Unit",
		maxHeight: -1, 
		onChange: function(){
			unitId = this.item.id;
		}
		}, "kpiUnit");
		unitSelect.startup();
	}
});

request.post("get-processes.php",{
handleAs: "json",
data: {
}						
}).then(function(processData)
{		
	if(processData.length == 0)
	dojo.byId("kpiProcess").value = "Empty. Click on (view processes) to add";
	else
	{
		processStore = new Memory({data:processData});
		
		processSelect = new FilteringSelect({
		name: "processSelectManager",
		store: processStore,
		searchAttr: "Process",
		maxHeight: -1, 
		onChange: function(){
			processId = this.item.id;
		}
		}, "kpiProcess");
		processSelect.startup();
	}
});

request.post("get-data-items.php",{
handleAs: "json",
data: {
}						
}).then(function(dataItemData)
{		
	if(dataItemData.length == 0)
	dojo.byId("kpiDataItems").value = "Empty. Click on (view data items) to add";
	else
	{
		dataItemStore = new Memory({data:dataItemData});
		
		dataItemSelect = new FilteringSelect({
		name: "dataItemSelectManager",
		store: dataItemStore,
		searchAttr: "DataItem",
		maxHeight: -1, 
		onChange: function(){
			dataItemId = this.item.id;
		}
		}, "kpiDataItems");
		dataItemSelect.startup();
	}
});

addGoal = function()
{
	request.post("get-goals.php",{
	handleAs: "json",
	data: {
	}						
	}).then(function(goalData)
	{
		//alert(goalData.items.length);
		var goalCount = 0
		var goalDisplayList = "<table cellspacing='0' style:'border-top:1px solid black;'>";
		var goalItem;
		while(goalCount < goalData.items.length)
		{							
			goalItem = goalData.items[goalCount];
			if(goalCount == 0)
			goalDisplayList = goalDisplayList + "<tr><td style='border-top:1px solid black; border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black;'>" + goalItem.Goal + "</td><td style='border-top:1px solid black; border-right:1px solid black;border-bottom:1px solid black;'><a href='#' onClick='deleteGoal("+goalItem.id+");'>Delete</a></td></tr>"
			else
			goalDisplayList = goalDisplayList + "<tr><td style='border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black;'>" + goalItem.Goal + "</td><td style='border-right:1px solid black;border-bottom:1px solid black;'><a href='#' onClick='deleteGoal("+goalItem.id+");'>Delete</a></td></tr>"
			goalCount++;
		}
		goalDisplayList = goalDisplayList + "</table>";
		dojo.byId("previousGoals").innerHTML = goalDisplayList;
	})
	dijit.byId("newGoalDialog").show();
}
saveGoal = function()
{
	dijit.byId("newGoalDialog").hide();
	var goal = dojo.byId("goalInput").value;
	request.post("save-goal.php",{
	handleAs: "json",
	data: {
		goal: goal
	}						
	}).then(function(goalId)
	{
		goalStore.put({id:goalId, Goal: goal});
	})
}
deleteGoal = function(id)
{
	request.post("delete-goal.php",{
	handleAs: "json",
	data: {
		id: id
	}						
	}).then(function()
	{
		addGoal();
		goalStore.remove(id);
	})
}

addUnit = function()
{
	request.post("get-work-units.php",{
	handleAs: "json",
	data: {
	}						
	}).then(function(unitData)
	{
		//alert(goalData.items.length);
		var unitCount = 0
		var unitDisplayList = "<table cellspacing='0' style:'border-top:1px solid black;'>";
		var unitItem;
		while(unitCount < unitData.items.length)
		{							
			unitItem = unitData.items[unitCount];
			if(unitCount == 0)
			unitDisplayList = unitDisplayList + "<tr><td style='border-top:1px solid black; border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black;'>" + unitItem.Unit + "</td><td style='border-top:1px solid black; border-right:1px solid black;border-bottom:1px solid black;'><a href='#' onClick='deleteUnit("+unitItem.id+");'>Delete</a></td></tr>"
			else
			unitDisplayList = unitDisplayList + "<tr><td style='border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black;'>" + unitItem.Unit + "</td><td style='border-right:1px solid black;border-bottom:1px solid black;'><a href='#' onClick='deleteUnit("+unitItem.id+");'>Delete</a></td></tr>"
			unitCount++;
		}
		unitDisplayList = unitDisplayList + "</table>";
		dojo.byId("previousUnits").innerHTML = unitDisplayList;
	})
	dijit.byId("newUnitDialog").show();
}
saveUnit = function()
{
	dijit.byId("newUnitDialog").hide();
	var unit = dojo.byId("unitInput").value;
	request.post("save-unit.php",{
	handleAs: "json",
	data: {
		unit: unit
	}						
	}).then(function(unitId)
	{
		unitStore.put({id:unitId, Unit: unit});
	})
}
deleteUnit = function(id)
{
	request.post("delete-unit.php",{
	handleAs: "json",
	data: {
		id: id
	}						
	}).then(function()
	{
		addUnit();
		unitStore.remove(id);
	})
}

addProcess = function()
{
	request.post("get-processes.php",{
	handleAs: "json",
	data: {
	}						
	}).then(function(processData)
	{
		//alert(goalData.items.length);
		var processCount = 0
		var processDisplayList = "<table cellspacing='0' style:'border-top:1px solid black;'>";
		var processItem;
		while(processCount < processData.items.length)
		{							
			processItem = processData.items[processCount];
			if(processCount == 0)
			processDisplayList = processDisplayList + "<tr><td style='border-top:1px solid black; border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black;'>" + processItem.Process + "</td><td style='border-top:1px solid black; border-right:1px solid black;border-bottom:1px solid black;'><a href='#' onClick='deleteProcess("+processItem.id+");'>Delete</a></td></tr>"
			else
			processDisplayList = processDisplayList + "<tr><td style='border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black;'>" + processItem.Process + "</td><td style='border-right:1px solid black;border-bottom:1px solid black;'><a href='#' onClick='deleteProcess("+processItem.id+");'>Delete</a></td></tr>"
			processCount++;
		}
		processDisplayList = processDisplayList + "</table>";
		dojo.byId("previousProcesses").innerHTML = processDisplayList;
	})
	dijit.byId("newProcessDialog").show();
}
saveProcess = function()
{
	dijit.byId("newProcessDialog").hide();
	var process = dojo.byId("processInput").value;
	alert(process);
	request.post("save-process.php",{
	handleAs: "json",
	data: {
		process: process
	}						
	}).then(function(processId)
	{
		processStore.put({id:processId, Process: process});
	})
}
deleteProcess = function(id)
{
	request.post("delete-process.php",{
	handleAs: "json",
	data: {
		id: id
	}						
	}).then(function()
	{
		addProcess();
		processStore.remove(id);
	})
}
addDataItem = function()
{
	request.post("get-data-items.php",{
	handleAs: "json",
	data: {
	}						
	}).then(function(dataItemData)
	{
		//alert(goalData.items.length);
		var dataItemCount = 0
		var dataItemDisplayList = "<table cellspacing='0' style:'border-top:1px solid black;'>";
		var dataItemItem;
		while(dataItemCount < dataItemData.items.length)
		{							
			dataItemItem = dataItemData.items[dataItemCount];
			if(dataItemCount == 0)
			dataItemDisplayList = dataItemDisplayList + "<tr><td style='border-top:1px solid black; border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black;'>" + dataItemItem.Goal + "</td><td style='border-top:1px solid black; border-right:1px solid black;border-bottom:1px solid black;'><a href='#' onClick='deleteDataItem("+dataItemItem.id+");'>Delete</a></td></tr>"
			else
			dataItemDisplayList = dataItemDisplayList + "<tr><td style='border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black;'>" + dataItemItem.DataItem + "</td><td style='border-right:1px solid black;border-bottom:1px solid black;'><a href='#' onClick='deleteDataItem("+dataItemItem.id+");'>Delete</a></td></tr>"
			dataItemCount++;
		}
		dataItemDisplayList = dataItemDisplayList + "</table>";
		dojo.byId("previousDataItems").innerHTML = dataItemDisplayList;
	})
	dijit.byId("newDataItemDialog").show();
}
saveDataItem = function()
{
	dijit.byId("newDataItemDialog").hide();
	var dataItem = dojo.byId("dataItemInput").value;
	request.post("save-data-item.php",{
	handleAs: "json",
	data: {
		dataItem: dataItem
	}						
	}).then(function(dataItemId)
	{
		dataItemStore.put({id:dataItemId, DataItem: dataItem});
	})
}
deleteDataItem = function(id)
{
	request.post("delete-data-item.php",{
	handleAs: "json",
	data: {
		id: id
	}						
	}).then(function()
	{
		addDataItem();
		dataItemStore.remove(id);
	})
}

});
</script>
</head>
<body class="claro">
    <table width="100%">
    <tr>
    	<td width="20%"><button data-dojo-type="dijit/form/Button" onClick="previousPage()" type="submit">Previous</button>
        <button data-dojo-type="dijit/form/Button" onClick="nextPage()" type="submit">Next</button></td>
        <td width="80%" align="right"><button data-dojo-type="dijit/form/Button" onClick="deletePage()" type="submit">Delete</button></td>
    </tr>
    </table>
   
   <table width="100%" style="border:1px solid black; border-collapse:collapse;">
   <tr><th colspan="2" style="border-bottom:1px solid black;background-color: #aabcfe; padding:5px;"><b>Measure Definition Dictionary</b><table align="right"><tr><td>Measure No:<span id="kpiNo"></span></td></tr></table></th>
   
   	<tr>
    	<td colspan="2" style="border-bottom:1px solid black;"><label>Measure Name:</label><input type='text' name='kpiName' id="kpiName" size="90" style="border:hidden;"/>Quantitative?<input type="checkbox" id="kpiQuantitative"></td>
    </tr>
    <tr>
    	<td nowrap style="border-bottom:1px solid black;">Date Created:&nbsp;&nbsp;<input type="text" name="dateCreated" id="dateCreated" data-dojo-type="dijit/form/DateTextBox"/></td>
        <td nowrap style="border-bottom:1px solid black;">Definition Status:
        	<select id="defStatus" data-dojo-type="dijit/form/FilteringSelect">
            	<option value="notStarted">Not Started</option>
                <option value="complete">Complete</option>
                <option value="inProgress">In Progress</option>
                <option value="parkingLot">Parking Lot</option>
                <option value="underReview">Under Review</option>
            </select>
        </td>
    </tr>
    <tr>
    	<td nowrap  style="border-bottom:1px solid black;">Date Modified:<input type="text" name="dateModified" id="dateModified" data-dojo-type="dijit/form/DateTextBox"/></td>
        <td  style="border-bottom:1px solid black;">Reporting Status:
        	<select id="reportStatus" data-dojo-type="dijit/form/FilteringSelect">
            	<option value="notReported">Not Started</option>
                <option value="beingLife">Being Brought to Life</option>
                <option value="formal">Reported - formal</option>
                <option value="pilot">Reported - pilot</option>
                <option value="underReview">Under Review</option>
            </select>
        </td>
    </tr>
    <tr>
    	<td nowrap style="border-bottom:1px solid black;">Abbreviation:<input type="text" id="abbreviation" style="border:hidden;"/></td>
        <td style="border-bottom:1px solid black;">Bring to Life Priority:
        		<select id="lifePriority" data-dojo-type="dijit/form/FilteringSelect">
            	<option value="Already Alive">Already Alive</option>
                <option value="First Priority">First Priority</option>
                <option value="Second Priority">Second Priority</option>
                <option value="Third Priority">Third Priority</option>
                <option value="On Hold">On Hold</option>
                <option value="Unprioritised">Unprioritised</option>
            </select>
        </td>
    </tr>
    <tr>
        <td valign="top" width="15%" style="border-bottom:1px solid black; border-right:1px solid black;">Description:</td>
        <td style="border-bottom:1px solid black;"><textarea name='kpiDescr' id="kpiDescr" 
        style="width: 100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border:hidden;"></textarea>
        </td>
    </tr>
    <tr>
        <td valign="top" width="15%" style="border-bottom:1px solid black; border-right:1px solid black;">Intent:</td>
        <td style="border-bottom:1px solid black;"><textarea name='kpiIntent' id="kpiIntent" 
        style="width: 100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border:hidden;"></textarea>
        </td>
    </tr>
    <tr><td style="border-bottom:1px solid black; border-right:1px solid black;">Integrity Required</td><td style="border-bottom:1px solid black;"><input type='text' name='kpiIntegrity' id="kpiIntegrity" size="90"  style="border:hidden;"/></td></tr>
    <tr>
    	<td style="border-bottom:1px solid black; border-right:1px solid black;">Where it fits</td>
    	<td style="border-bottom:1px solid black;">
        	<table>
            <tr>
                   <td>Objective</td>
                    <td>
                        <input type='text' style='width:90%' id='kpiObjective'/>
                    </td>
                </tr>
            	<tr>
                    <td>Level</td>
                    <td>
                    	<select id="kpiLevel" data-dojo-type="dijit/form/FilteringSelect">
                            <option value="Process Result Measures">Process Result Measures</option>
                            <option value="Success of Sustainability Measures">Success of Sustainability Measures</option>
                            <option value="Capability Gap Measures">Capability Gap Measures</option>
                            <option value="In Process/Activity Measures">In Process/Activity Measures</option>
                            <option value="Diagnostic Measures">Diagnostic Measures</option>
                        </select>
                    </td>
                </tr>
                <tr><td>Goal</td>
                	<td>
                    	<table><tr><td><input type="text" style='width:90%' id="kpiGoal"/></td>
                        <td><div onClick="addGoal()">(view goals)</div></td></tr></table>
                    </td>
                </tr>
                <tr><td>Work Unit</td>
                	<td>
                    	<table><tr><td><input type="text" style='width:90%' id="kpiUnit"/></td>
                        <td><div onClick="addUnit()">(view work units)</div></td></tr></table>
                    </td>
                </tr>
                <tr><td>Process</td>
                	<td>
                    	<table><tr><td><input type="text" style='width:90%' id="kpiProcess"/></td>
                        <td><div onClick="addProcess()">(view processes)</div></td></tr></table>
                    </td>
                </tr>
                <tr>
                    <td>Stakeholder</td>
                    <td>
                    	<select id="kpiStakeholder" data-dojo-type="dijit/form/FilteringSelect">
                            <option value="Community">Community</option>
                            <option value="Customers">Customers</option>
                            <option value="Employees">Employees</option>
                            <option value="Environment">Environment</option>
                            <option value="Government">Government</option>
                            <option value="Partners/Alliance">Partners/Alliance</option>
                            <option value="Regulators/Industry">Regulators/Industry</option>
                            <option value="Shareholders">Shareholders</option>
                            <option value="Suppliers">Suppliers</option>
                        </select>
                    </td>
                </tr>
                <tr><td>Measure Relationship</td><td>
                	<select id="kpiRelationship" data-dojo-type="dijit/form/FilteringSelect">
                            <option value="Parent(Effect)">Parent(Effect)</option>
                            <option value="Child(Cause)">Child(Cause)</option>
                            <option value="Conflict">Conflict</option>
                            <option value="Companion">Companion</option>
                            <option value="Lead Indicator">Lead Indicator</option>
                            <option value="Proxy">Proxy</option>
                            <option value="Regulators/Industry">Regulators/Industry</option>
                   </select>
                </td></tr>
            </table>
        </td>
   </tr>
   <tr>
       <td style="border-bottom:1px solid black; border-right:1px solid black;">Calculation</td>
       <td style="border-bottom:1px solid black;">
       		<table>
            	<tr><td>Formula</td>
                	<td><input type='text' name='kpiFormula' id="kpiFormula" size="90" value="[numerator]/[denominator]"/></td>
                </tr>
                <tr><td>Frequency</td>
                <td>
                	<select id="kpiFrequency" data-dojo-type="dijit/form/FilteringSelect">
                            <option value="Annually">Annually</option>
                            <option value="Daily">Daily</option>
                            <option value="Fortnightly">Fortnightly</option>
                            <option value="If & When Required">If & When Required</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="Half Yearly">Half Yearly</option>
                            <option value="Weekly">Weekly</option>
                            	
                    </select>
                </td>
                </tr>
                <tr><td>Scope</td>
                <td><textarea name='kpiScope' id="kpiScope" 
        style="width: 100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;"></textarea></td>
                </tr>
                <tr><td>Drilldown Dimensions</td>
                <td>
                <textarea name='kpiDrill' id="kpiDrill" 
        style="width: 100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;"></textarea>
                </td>
                </tr>
                <tr><td>Data Items</td>
                <td>
                <table><tr><td>	<input type="text" id="kpiDataItems"/></td><td><div onClick="addDataItem()">(view data items)</div></td></tr>
                </table>
                </td>
                </tr>
            </table>
       </td>
   </tr>
   <tr>
   		<td style="border-bottom:1px solid black; border-right:1px solid black;">Presentation</td>
        <td style="border-bottom:1px solid black;">
        	<table>
            	<tr>
                    <td>Comparison</td>
                    <td>
                    		<select id="kpiComparison" data-dojo-type="dijit/form/FilteringSelect">
                            <option value="Distribution/Spread">Distribution/Spread</option>
                            <option value="Element to Element">Element to Element</option>
                            <option value="Part with its Whole">Part with its Whole</option>
                            <option value="Point to Point">Point to Point</option>
                            <option value="Trend Over Time">Trend Over Time</option>
                            </select>
                    </td>
                </tr>
                <tr>
                	<td>Method</td>
                    <td>
                    	<select id="kpiMethod" data-dojo-type="dijit/form/FilteringSelect">
                            <option value="Dot Chart">Dot Chart</option>
                            <option value="Histogram">Histogram</option>
                            <option value="Horizontal Bar Chart">Horizontal Bar Chart</option>
                            <option value="Line Chart">Line Chart</option>
                            <option value="Other">Other</option>
                            <option value="Pareto Chart">Pareto Chart</option>
                            <option value="Run Chart">Run Chart</option>
                            <option value="Statistical Process Control Chart">Statistical Process Control Chart</option>
                            <option value="Vertical Bar Chart">Vertical Bar Chart</option>
                        </select>
                    </td>
                </tr>
                <tr><td>Notes</td>
                <td>
                	 <textarea name='kpiPresentNotes' id="kpiPresentNotes" 
        style="width: 100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;"></textarea>
                </td>
                </tr>
                <tr>
                    <td>Frequency</td>
                    <td>
                    	<select id="kpiFrequency2" data-dojo-type="dijit/form/FilteringSelect">
                            <option value="Annually">Annually</option>
                            <option value="Daily">Daily</option>
                            <option value="Fortnightly">Fortnightly</option>
                            <option value="If & When Required">If & When Required</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="Half Yearly">Half Yearly</option>
                            <option value="Weekly">Weekly</option>
                            	
                    </select>
                    </td>
                </tr>
            </table>
        </td>
   </tr>
   <tr><td style="border-bottom:1px solid black; border-right:1px solid black;">Response</td>
   <td style="border-bottom:1px solid black;">
    <textarea name='kpiResponse' id="kpiResponse" 
        style="width: 100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border:hidden;"></textarea>
   </td>
   </tr>
   <tr>
   		<td style="border-bottom:1px solid black; border-right:1px solid black;">Ownership</td>
        <td style="border-bottom:1px solid black;">
        	<table>
            	<tr><td nowrap><label>Reporting</label><input type='text' name='kpiOwnerReporting' id="kpiOwnerReporting" size="90" /></td></tr>
                <tr><td nowrap><label>Definition</label><input type='text' name='kpiOwnerDefinition' id="kpiOwnerDefinition" size="90" /></td></tr>
                <tr><td nowrap><label>Performance</label><input type='text' name='kpiOwnerPerformance' id="kpiOwnerPerformance" size="87" /></td></tr>
            </table>
        </td>
   </tr>
   <tr><td style="border-right:1px solid black;">Notes</td>
   <td>
   	<textarea name='kpiNotes' id="kpiNotes" 
        style="width: 100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border:hidden"></textarea>
   </td>
   </tr>
   </table>

<div class="dijitHidden">
    <div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New Goal'" id="newGoalDialog">
        <table>
            <tr>
            	<td colspan="2" align="center"><div id="previousGoals"></div></td>
            </tr>
            <tr>
                <td>New Goal</td>
                <td><input type='text' id='goalInput'/></td>
            </tr>
            <tr>
                <td colspan="2"><button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:saveGoal" type="submit">Finish</button></td>
            </tr>
        </table>
    </div>
</div>

<div class="dijitHidden">
    <div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New Work Unit'" id="newUnitDialog">
        <table>
            <tr>
            	<td colspan="2"><div id="previousUnits"></div></td>
            </tr>
            <tr>
                <td>New Work Unit</td>
                <td><input type='text' id='unitInput'/></td>
            </tr>
            <tr>
                <td colspan="2"><button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:saveUnit" type="submit">Finish</button></td>
            </tr>
        </table>
    </div>
</div>

<div class="dijitHidden">
    <div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New Process'" id="newProcessDialog">
        <table>
            <tr>
            	<td colspan="2"><div id="previousProcesses"></div></td>
            </tr>
            <tr>
                <td>New Process</td>
                <td><input type='text' id='processInput'/></td>
            </tr>
            <tr>
                <td colspan="2">
                <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:saveProcess" type="submit">Finish</button>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="dijitHidden">
    <div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New Data Item'" id="newDataItemDialog">
        <table>
            <tr>
            	<td colspan="2"><div id="previousDataItems"></div></td>
            </tr>
            <tr>
                <td>New Data Item</td>
                <td>
                <input type='text' id='dataItemInput'/>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:saveDataItem" type="submit">Finish</button>
                </td>
            </tr>
        </table>
    </div>
</div>
 
</body>
</html>