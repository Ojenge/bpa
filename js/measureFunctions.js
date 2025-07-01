var grid, grid2, grid3, grid4, bulkGrid, gridThreeTrue, gridFourTrue, gridFiveTrue, gridGoalTrue;

require([
"dijit/registry",
"dojo/_base/declare",
"dojo/dom",
"dojo/dom-style",
"dojo/request",
"dojo/json",

"dojo/store/Memory",
"dojo/store/Observable",
"dojo/data/ObjectStore",
"dojox/data/CsvStore",

"dojox/grid/DataGrid",

"dgrid/Grid",
"dgrid/extensions/Pagination",
"dgrid/editor",
"dgrid/Keyboard",
"dgrid/Selection",

"dijit/form/Button",
"dijit/form/NumberSpinner",
"dijit/form/DropDownButton",

"dojo/domReady!"],
function(registry, declare, dom, domStyle, request, json, Memory, Observable, ObjectStore, CsvStore, DataGrid, Grid, Pagination, editor, Keyboard, Selection, Button, NumberSpinner, DropDownButton){
/**********************************************/
auditTrail = function()
{
	request.post("scorecards/measures/get-kpi-audit.php",
	{
		data: {
		objectId: kpiGlobalId
		//objectId: "kpi29"
	}
	}).then(function(kpiAuditData) 
	{
		dom.byId("kpiAuditContent").innerHTML = kpiAuditData;
		registry.byId("kpiAuditTrailDialog").show();
	})
}
/********************************************/
closeAudit = function()
{
	registry.byId("kpiAuditTrailDialog").hide();
}
/**********************************************/
saveMeasureValues = function(dataSave)
{
	//alert(dataSave);
	//kpiUpdateStore.empty();
	//grid2.empty();
	//dijit.byId("bulkMeasureDialog2").destroyRecursive();
	//domConstruct.destroy("gridKpi2");
	
	request.post("scorecards/save-kpi.php",
	{
	//handleAs: "json",
		data:
		{
			objectId: kpiGlobalId,
			kpiValuesArray: dataSave,
			updater: dom.byId("userIdJs").innerHTML,
			csvImportVar: csvImportVar
			//objectPeriod: "January"
		}
	}).then(function() 
	{
		updateChart();
		dom.byId("msgContent").innerHTML = "Measure values successfully saved.";
		domStyle.set(dom.byId("msgContent"), 'display', 'block');
		var msgTimeout = setTimeout(function(){
				domStyle.set(dom.byId("msgContent"), 'display', 'none');
		},2000);
	});
}
/**********************************************/
myBulkEntry = function(kpiId)
{
	kpiGlobalId = "kpi"+kpiId;
	bulkEntry();
}
/**********************************************/
bulkEntry = function(updateBulk)
{
	csvImportVar = 'false';
	if(updateBulk == "updateBulk") 
	{
		var toSave;
		switch(gridUpdateType)
		{
			case "goalOnly":
			{
				toSave = json.stringify(kpiUpdateStore.data);
				break;	
			}
			case "threeColor":
			{
				toSave = json.stringify(kpiUpdateStore2.data);
				break;	
			}
			case "fourColor":
			{
				toSave = json.stringify(kpiUpdateStore3.data);
				break;	
			}
			case "fiveColor":
			{
				toSave = json.stringify(kpiUpdateStore4.data);
				break;	
			} 	
		}
		saveMeasureValues(toSave);
	}
	else
	{
		updateBulk = null;
		//console.log("Id: " + kpiGlobalId + " period: " + period + " kpiType: " + kpiGlobalType + " date: " + globalDate);
		request.post("scorecards/measures/get-kpi-update.php",
		{
			handleAs: "json",
			data: {
			objectId: kpiGlobalId,
			objectPeriod: period,
			objectType: kpiGlobalType,
			objectDate: globalDate
			}
			}).then(function(kpiUpdateData) 
			{
				//alert(JSON.stringify(kpiUpdateData));
				function getColumns(){
					return {
						date: { label: "Date"},
						actual: editor({ label: "Actual", autoSave: true }, "NumberSpinner"),
						green: editor({ label: "Target", autoSave: true }, "NumberSpinner")
					};
				}
				function getColumns2(){
					return {
						date: { label: "Date"},
						actual: editor({ label: "Actual", autoSave: true }, "NumberSpinner"),
						red: editor({ label: "Baseline", autoSave: true }, "NumberSpinner"),
						green: editor({ label: "Target", autoSave: true }, "NumberSpinner")
					};
				}
				function getColumns3(){
					return {
						date: { label: "Date"},
						actual: editor({ label: "Actual", autoSave: true }, "NumberSpinner"),
						red: editor({ label: "Baseline", autoSave: true }, "NumberSpinner"),
						green: editor({ label: "Target", autoSave: true }, "NumberSpinner"),
						darkgreen: editor({ label: "Stretch Target", autoSave: true }, "NumberSpinner")
					};
				}
				function getColumns4(){
					return {
						date: { label: "Date"},
						actual: editor({ label: "Actual", autoSave: true }, "NumberSpinner"),
						red: editor({ label: "Baseline", autoSave: true }, "NumberSpinner"),
						green: editor({ label: "Target", autoSave: true }, "NumberSpinner"),
						darkgreen: editor({ label: "Stretch Target", autoSave: true }, "NumberSpinner"),
						blue: editor({ label: "Best", autoSave: true }, "NumberSpinner")
					};
				}
				
				if(kpiUpdateData[0].gaugeType == "threeColor")
				{
					gridUpdateType = "threeColor";
					if(gridThreeTrue == "True")
					{
						if(kpiUpdateStore2) 
						{
							kpiUpdateStore2 = new Observable (new Memory({ data: kpiUpdateData }));
							grid2.set("store", kpiUpdateStore2);
						}
						grid2.refresh();
						registry.byId("bulkMeasureDialog2").show();
					}
					else
					{
						kpiUpdateStore2 = new Observable (new Memory({ data: kpiUpdateData }));
						bulkGrid = declare([ Grid, Keyboard, Selection, Pagination ]);
						gridThreeTrue = "True";
						grid2 = new bulkGrid({
							store: kpiUpdateStore2,
							rowsPerPage: 6,
							pagingLinks: 1,
							pagingTextBox: true,
							firstLastArrows: true,
							pageSizeOptions: [3, 6, 12],
							columns:getColumns2()
						},"gridKpi2");
						grid2.refresh();
						registry.byId("bulkMeasureDialog2").show();
						grid2.refresh();	
					}
				}//end of threeColor condition
				else if(kpiUpdateData[0].gaugeType == "fourColor")
				{
					gridUpdateType = "fourColor";
					if(gridFourTrue == "True")
					{
						if(kpiUpdateStore3) 
						{
							kpiUpdateStore3 = new Observable (new Memory({ data: kpiUpdateData }));
							grid3.set("store", kpiUpdateStore3);
						}
						grid3.refresh();
						registry.byId("bulkMeasureDialog3").show();	
					}
					else
					{
						kpiUpdateStore3 = new Observable (new Memory({ data: kpiUpdateData }));
						bulkGrid = declare([ Grid, Keyboard, Selection, Pagination ]);
						gridFourTrue = "True";
						grid3 = new bulkGrid({
							store: kpiUpdateStore3,
							rowsPerPage: 6,
							pagingLinks: 1,
							pagingTextBox: true,
							firstLastArrows: true,
							pageSizeOptions: [3, 6, 12],
							columns:getColumns3()
							//selectionMode: "single", // for Selection; only select a single row at a time
								//cellNavigation: false // for Keyboard; allow only row-level keyboard navigation
						},"gridKpi3");
						grid3.refresh();
						registry.byId("bulkMeasureDialog3").show();
						grid3.refresh();
					}
				}
				else if(kpiUpdateData[0].gaugeType == "fiveColor")
				{
					gridUpdateType = "fiveColor";
					if(gridFiveTrue == "True")
					{
						if(kpiUpdateStore4) 
						{
							kpiUpdateStore4 = new Observable (new Memory({ data: kpiUpdateData }));
							grid4.set("store", kpiUpdateStore4);
						}
						grid4.refresh();
						registry.byId("bulkMeasureDialog4").show();	
					}
					else
					{
						kpiUpdateStore4 = new Observable (new Memory({ data: kpiUpdateData }));
						bulkGrid = declare([ Grid, Keyboard, Selection, Pagination ]);
						gridFiveTrue = "True";
						grid4 = new bulkGrid({
							store: kpiUpdateStore4,
							rowsPerPage: 6,
							pagingLinks: 1,
							pagingTextBox: true,
							firstLastArrows: true,
							pageSizeOptions: [3, 6, 12],
							columns:getColumns4()
						},"gridKpi4");
						grid4.refresh();	
						registry.byId("bulkMeasureDialog4").show();
						grid4.refresh();
					}
				}
				else
				{
					gridUpdateType = "goalOnly";
					if(gridGoalTrue == "True")
					{
						//dom.byId("gridKpi").innerHTML = "Why you stressing me?";
						if(kpiUpdateStore)
						{
							kpiUpdateStore = new Observable (new Memory({ data: kpiUpdateData }));
							grid.set("store", kpiUpdateStore);
						}
						grid.refresh();
						registry.byId("bulkMeasureDialogGoal").show();
					}
					else
					{
						kpiUpdateStore = new Observable (new Memory({ data: kpiUpdateData }));
						bulkGrid = declare([ Grid, Keyboard, Selection, Pagination ]);
						gridGoalTrue = "True";
						grid = new bulkGrid({
							store: kpiUpdateStore,
							rowsPerPage: 6,
							pagingLinks: 1,
							pagingTextBox: true,
							firstLastArrows: true,
							pageSizeOptions: [3, 6, 12],
							columns:getColumns()
						},"gridKpi");
						grid.refresh();
						registry.byId("bulkMeasureDialogGoal").show();
						grid.refresh();
					}
				}
		});
	}
}
/**********************************************/
});