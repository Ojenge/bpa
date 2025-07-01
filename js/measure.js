var grid, grid2, grid3, grid4, bulkGrid, gridThreeTrue, gridFourTrue, gridFiveTrue, gridGoalTrue;

require([
"dijit/registry",
"dojo/_base/declare",
"dojo/on",
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
"dijit/Dialog",

"dojox/layout/ContentPane",

"dojo/domReady!"],
function(registry, declare, on, dom, domStyle, request, json, Memory, Observable, ObjectStore, CsvStore, DataGrid, Grid, Pagination, editor, Keyboard, Selection, Button, NumberSpinner, DropDownButton, Dialog, ContentPane){
/**********************************************/
auditTrail = function()
{
	request.post("scorecards/get-kpi-audit.php",
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
		},3000);
		
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
		var waitForStore = setTimeout(function(){
			saveMeasureValues(toSave);//Added this to allow store to update before saving. LTK 06 Oct 2021 22:27hrs.
		},300);
		var waitToSaveMeasure = setTimeout(function(){
			if (typeof getMeasures === "function") {getMeasures(); getValues();}
		},1200);
	}
	else
	{
		updateBulk = null;
		//console.log("Id: " + kpiGlobalId + " period: " + period + " kpiType: " + kpiGlobalType + " date: " + globalDate);
		request.post("scorecards/get-kpi-update.php",
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
				var updater = kpiUpdateData[0].updater;
				var importDialogToggle = "False";
				
				importDialogClose = function()
				{
					importDialog.hide();
					importDialogToggle = "True";
				}
				importDialogCloseTwo =  function()
				{
					importDialog.hide();
					dijit.byId("bulkMeasureDialog2").hide();
					importDialogToggle = "True"
				}
							
				importDialog = new Dialog({
				title: "Imported Data",
				content: 'You are updating a measure whose values are being picked from SAGE. Are you sure you want to overide these values?<br><button data-dojo-type="dijit/form/Button" onclick="importDialogClose()" type="submit">Yes</button> <button data-dojo-type="dijit/form/Button" onclick="importDialogCloseTwo()" type="submit">No</button>',
				style: "width: 300px"
				});
				
				function getColumns(){
					if(dom.byId("viewRights").innerHTML == "Administrator")
					return {
						date: { label: "Date"},
						actual: editor({ label: "Actual", autoSave: true }, "NumberSpinner"),
						green: editor({ label: "Target", autoSave: true }, "NumberSpinner"),
						updater: { label: "Updater"}
					};
					else
					return {
						date: { label: "Date"},
						actual: editor({ label: "Actual", autoSave: true }, "NumberSpinner"),
						green: "Target",
						updater: { label: "Updater"}
					};
				}
				function getColumns2(){
					if(dom.byId("viewRights").innerHTML == "Administrator")
					{
						return{
							date: { label: "Date"},
							actual: editor({ label: "Actual", autoSave: true }, "NumberSpinner"),
							red: editor({ label: "Baseline", autoSave: true }, "NumberSpinner"),
							green: editor({ label: "Target", autoSave: true }, "NumberSpinner"),
							updater: { label: "Updater"}
						}
					}
					else
					return {
						date: { label: "Date"},
						actual: editor({ label: "Actual", autoSave: true }, "NumberSpinner"),
						red: "Baseline",
						green: "Target",
						updater: "Updater"
					};
				}
				function getColumns3(){
					if(dom.byId("viewRights").innerHTML == "Administrator")
					{
						return {
							date: { label: "Date"},
							actual: editor({ label: "Actual", autoSave: true }, "NumberSpinner"),
							red: editor({ label: "Baseline", autoSave: true }, "NumberSpinner"),
							green: editor({ label: "Target", autoSave: true }, "NumberSpinner"),
							darkgreen: editor({ label: "Stretch Target", autoSave: true }, "NumberSpinner"),
							updater: { label: "Updater"}
						};
					}
					else
					return {
						date: { label: "Date"},
						actual: editor({ label: "Actual", autoSave: true }, "NumberSpinner"),
						red: "Baseline",
						green: "Target",
						darkgreen: "Stretch Target",
						updater: { label: "Updater"}
					};
				}
				function getColumns4(){
					if(dom.byId("viewRights").innerHTML == "Administrator")
					return {
						date: { label: "Date"},
						actual: editor({ label: "Actual", autoSave: true }, "NumberSpinner"),
						red: editor({ label: "Baseline", autoSave: true }, "NumberSpinner"),
						green: editor({ label: "Target", autoSave: true }, "NumberSpinner"),
						darkgreen: editor({ label: "Stretch Target", autoSave: true }, "NumberSpinner"),
						blue: editor({ label: "Best", autoSave: true }, "NumberSpinner"),
						updater: { label: "Updater"}
					};
					else
					return {
						date: { label: "Date"},
						actual: editor({ label: "Actual", autoSave: true }, "NumberSpinner"),
						red: "Baseline",
						green: "Target",
						darkgreen: "Stretch Target",
						blue: "Best",
						updater: { label: "Updater"}
					};
				}
				
				if(kpiUpdateData[0].gaugeType == "threeColor")
				{
					//grid2.empty();
					//grid2 = null;
					///var uniqid = Date.now();
					//uniqid = "id"+uniqid;
					//console.log("Uniq Id = " + uniqid);
						
					gridUpdateType = "threeColor";
					if(gridThreeTrue == "True")
					{
						kpiUpdateStore2 = new Observable (new Memory({ data: kpiUpdateData }));
						grid2.set("store", kpiUpdateStore2);
						grid2.refresh();
						registry.byId("bulkMeasureDialog2").show();
					}
					else
					{
						if(grid2) //added this to prevent store from loading original kpiUpdateData everytime but work with what is already in Memory. LTK 06 Oct 2021 22:31Hrs
						//But well, the data needs to be loaded every time. Users complained of the form not refreshing so we do need to load kpiUpdateData but avoid creating a new grid. LTK 24 Nov 2021 20:23Hrs
						{
							//console.log("at if(grid2)");
							kpiUpdateStore2 = new Observable (new Memory({ data: kpiUpdateData }));
							grid2.set("store", kpiUpdateStore2);
							grid2.refresh();
						}
						else 
						{
							kpiUpdateStore2 = new Observable (new Memory({ data: kpiUpdateData }));
							bulkGrid = declare([ Grid, Keyboard, Selection, Pagination ]);
							//gridThreeTrue = "True";// Not sure why this is the logic i followed but it has created a nightmare over the years since it blocks a new page being loaded from creating a new grid at the else condition. What a relief thought to finally make this discovery after so long||! LTK 16May2021 2315Hrs
							grid2 = new bulkGrid({
								store: kpiUpdateStore2,
								//selectionMode: 'single',
								rowsPerPage: 6,
								pagingLinks: 1,
								pagingTextBox: true,
								firstLastArrows: true,
								pageSizeOptions: [3, 6, 12],
								columns:getColumns2()
							},"gridKpi2");
							grid2.refresh();
						}
						//registry.byId("bulkMeasureDialog2").show();
						dijit.byId("bulkMeasureDialog2").show();
						grid2.refresh();	
					}
					on(grid2, "click", function(evt)
					{
						if(updater == "SAGE" && importDialogToggle == "False")
						{
							importDialog.show();
						}
					}, true);
					
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
						if(grid3) 
						{
							kpiUpdateStore3 = new Observable (new Memory({ data: kpiUpdateData }));
							grid3.set("store", kpiUpdateStore3);
							grid3.refresh();
						}
						else kpiUpdateStore3 = new Observable (new Memory({ data: kpiUpdateData }));
						bulkGrid = declare([ Grid, Keyboard, Selection, Pagination ]);
						//gridFourTrue = "True";
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
						if(grid4) 
						{
							kpiUpdateStore4 = new Observable (new Memory({ data: kpiUpdateData }));
							grid4.set("store", kpiUpdateStore4);
							grid4.refresh();
						}
						else kpiUpdateStore4 = new Observable (new Memory({ data: kpiUpdateData }));
						bulkGrid = declare([ Grid, Keyboard, Selection, Pagination ]);
						//gridFiveTrue = "True";
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
						if(grid) 
						{
							kpiUpdateStore = new Observable (new Memory({ data: kpiUpdateData }));
							grid.set("store", kpiUpdateStore);
							grid.refresh();
						}
						else kpiUpdateStore = new Observable (new Memory({ data: kpiUpdateData }));
						bulkGrid = declare([ Grid, Keyboard, Selection, Pagination ]);
						//gridGoalTrue = "True";
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