<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../dijit/themes/soria/soria.css">
<style type="text/css">
	@import "../../dojox/grid/resources/Grid.css";
	@import "../../dojox/grid/resources/tundraGrid.css";
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
<script src="../dojo/dojo.js" data-dojo-config="async: true, parseOnLoad:true"></script>
<script>
require([ 
"dojox/grid/DataGrid",
"dojo/store/Memory",
"dojo/store/Observable",
"dojo/store/DataStore",
"dojox/data/CsvStore",
"dijit/registry",
"dijit/Dialog",
"dijit/form/Button",
"dojo/request",
"dojo/dom",
"dojo/dom-style",
'dojo/domReady!'], function (DataGrid, Memory, Observable, DataStore, CsvStore, registry, Dialog, Button, request, dom, domStyle) {
    var gaugeType, layout, toSave, dbBaseline, dbTarget, dbStretch, dbBest, grid, gridTrue, savingId;
	var kpiId = 'kpi2';
	request.post("get-kpi-details.php",{
	handleAs: "json",
	data: {
		kpiId: kpiId
	}						
	}).then(function(kpiData) 
	{
		gaugeType = kpiData.gaugeType;
		dbBaseline = kpiData.baseline;
		dbTarget = kpiData.target;
		savingId = kpiData.id;
		switch(kpiData.gaugeType)
		{
			case "goalOnly":
			{
				layout = [[
					{ field: "date", name: "Date", width: 10 },
					{ field: "actual", name: "Actual", width: 10, editable: true },
					{ field: "target", name: "Target", width: 10 }
				  ]];
				  dom.byId("gridContainer").innerHTML = "<table><tr><th>Table Structure</th></tr><tr><td>Date</td><td>Actual</td><td>Baseline</td><td>Target</td></tr><tr><td></td><td></td><td></td><td></td></tr></table>";
				  dom.byId("gridContainer").innerHTML = tableStructure;
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
				break;	
			}
		}
	});			

	hideFileUploadDialog = function()
	{
		registry.byId("newCsvFileDialog").onCancel();
		//if(dijit.byId("gridContainer")) dijit.byId("gridContainer").destroy();
		var csvFile = dijit.byId("csvUploader").getFileList();
		csvFile = "upload/images/" + csvFile[0].name;
		var csvStore = new CsvStore({url:csvFile});
		//var dataStore = new Memory({ data: csvStore });
		if(gridTrue == "True")
		{
			grid.set("store", csvStore);
			//grid.store = csvStore;
			grid._refresh();
		}
		else
		{
			domStyle.set(dom.byId("gridContainer"), "display", "block");
			gridTrue = "True"
			grid = new DataGrid({
				id: 'grid',
				store: csvStore,
				structure: layout
			},'gridContainer');
			grid.startup();
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
			if(year < 1000 || year > 3000 || month == 0 || month > 12)
				return false;
		
			var monthLength = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];
		
			// Adjust for leap years
			if(year % 400 == 0 || (year % 100 != 0 && year % 4 == 0))
				monthLength[1] = 29;
		
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
					break;	
				}
				case "threeColor":
				{
					//console.log("Testing functions: " + "1. getLabel => " + csvStore.getLabel(items[0]) + "2. getIdentity => " + csvStore.getIdentity(items[0]) + "3. getFeatures => " + JSON.stringify(csvStore.getFeatures())+ "4. getAttributes => " + csvStore.getAttributes(items[0]));
					//console.log("getAttributes => " + csvStore.getAttributes(items[0]));
					var dateError = 0, actualError = 0, baselineError = 0, targetError = 0, csvErrors = "";
					var csvAttributes = "\'" + csvStore.getAttributes(items[0]) + "\'";
					csvAttributes = csvAttributes.split(",")
					//console.log(csvAttributes[0] + "; " + csvAttributes[1] + "; " + csvAttributes[2] + "; " + csvAttributes[3]);
					if(csvAttributes[0] != "\'date") csvErrors = csvErrors + "First column should be labeled 'date'";
					if(csvAttributes[1] != "actual") csvErrors = csvErrors + "Second column should be labeled 'actual'";
					if(csvAttributes[2] != "baseline") csvErrors = csvErrors + "Third column should be labeled 'baseline'";
					if(csvAttributes[3] != "target\'") csvErrors = csvErrors + "Fourth column should be labeled 'target'";
					toSave = "[";
					for(var i = 0; i < items.length; i++)
					{
						var date = csvStore.getValue(items[i], "date");
						var actual = csvStore.getValue(items[i], "actual");
						var baseline = csvStore.getValue(items[i], "baseline");
						var target = csvStore.getValue(items[i], "target");
						if(actual == ' ' || actual == null || actual == undefined)
						{
							//do nothing
						}
						else
						{
							if(baseline == ' ' || baseline == null || baseline == undefined) {baseline = dbBaseline; }
							if(target == ' ' || target == null || target == undefined) target = dbTarget;
							
							if(isValidDate(date) == false) dateError++;
							if(isNumeric(actual) == false) actualError++;
							if(isNumeric(baseline) == false) baselineError++;
							if(isNumeric(target) == false) targetError++;
							if(i < items.length-1) toSave = toSave + "{\"id\": " + savingId + ", \"date\": " + date + ", \"actual\": " + actual + ", \"red\": " + baseline + ", \"green\": " + target + "},";
							else toSave = toSave + "{\"id\": " + savingId + ", \"date\": " + date + ", \"actual\": " + actual + ", \"red\": " + baseline + ", \"green\": " + target + "}";
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
				  if(dateError == 0 && actualError == 0 && baselineError == 0 && targetError == 0)
					dijit.byId("csvSaveButton").set('disabled', false);
				  else dijit.byId("csvSaveButton").set('disabled', true);
					break;	
				}
				case "fourColor":
				{
					break;	
				}
				case "fiveColor":
				{
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
		console.log(toSave);
		//saveMeasureValues(toSave);	
	}
});
</script>
</head>
<body class="soria">
<div id="csvFormat"></div><br>
<div id="csvErrors" class='myFont' style="color:#FF0000;"></div>
<div id="gridContainer" style="width:650px; height:300px; display:none; overflow:scroll;"></div>
<div><button data-dojo-type="dijit/form/Button" onClick="showFileUploadDialog()" data-dojo-props="onClick:showFileUploadDialog" type="submit">Upload CSV File</button><button data-dojo-type="dijit/form/Button" onClick="csvSaveData()" type="submit" disabled id="csvSaveButton">Save CSV Data</button></div>    
<div class="dijitHidden">
<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New CSV File'" id="newCsvFileDialog">
<table>
<tr>
    <td colspan="2">
        <form method="post" action="upload/UploadFile.php" id="csvForm" enctype="multipart/form-data" >
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
</body>
</html>