<?php
require_once("../admin/models/config.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>My Data Entry</title>
</head>

<script src="js/measure.js"></script>
<script src="js/initiative.js"></script>
<script>
var myPipEdit = 'Edit';
var myPipEditId;
require([
"dojo/request",
"dojo/dom",
"dojo/dom-style",
"dojo/dom-construct",
"dojo/json",
"dojo/store/Memory",
"dijit/Dialog",
"dijit/Tooltip",
"dijit/TooltipDialog",
"dijit/popup",
"dijit/form/FilteringSelect",
"dijit/form/Button",
"dijit/form/DateTextBox",
"dojox/layout/ContentPane",

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

"dojo/domReady!"
], function(request, dom, domStyle, domConstruct, json, Memory, Dialog, Tooltip, TooltipDialog, popup, FilteringSelect, Button, DateTextBox, ContentPane, InlineEditBox, Editor){

refreshDataEntryPage = function()//Refresh the page when a user makes comments. LTK 15May2021 0749Hrs (After the Saturday 5km morning run :-))
{
	getMeasures();
	getValues();
	getInitiatives();
	getPip();
	getInterpretation();
}

//if(dijit.byId("bulkMeasureDialogGoal")) dijit.byId("bulkMeasureDialogGoal").destroyRecursive();
if(dijit.byId("bulkMeasureDialog2")) 
{	
	//console.log("Deleting bulkMeasureDialog2"); 
	//domConstruct.destroy('bulkMeasureDialog2');
	//dijit.byId("bulkMeasureDialog2").destroy(true);
	//dijit.byId("bulkMeasureDialog2").destroyRecursive();
}
//if(dijit.byId("bulkMeasureDialog3")) dijit.byId("bulkMeasureDialog3").destroyRecursive();
//if(dijit.byId("bulkMeasureDialog4")) dijit.byId("bulkMeasureDialog4").destroyRecursive();

//if(dom.byId("dataEntryUserId").innerHTML == 'ind1')
//{
	domStyle.set(dom.byId("individualInput"), 'display', 'block');
	request.post("userCalls/get-users.php",{
	handleAs: "json",
	data: {
	}
	}).then(function(userData)
	{			//alert(JSON.stringify(userData));
	var ownerStore = new Memory({data:userData});
	
	var sponsorSelect = new FilteringSelect({
	name: "individualInput",
	//placeHolder: "Select a User",
	store: ownerStore,
	searchAttr: "User",
	maxHeight: -1, 
	onChange: function(){
		dom.byId("dataEntryUserId").innerHTML = this.item.id;
		getMeasures();
		getValues();
		getInitiatives();
		getPip();
	}
	}, "individualInput").startup();
	});
//}

getMeasures = function()
{
	request.post("dataEntry/get-measures.php",{
	data: {
		objectId: dom.byId("dataEntryUserId").innerHTML,
		objectPeriod: period,
		objectDate: globalDate
	}						
	}).then(function(data) 
	{
		dojo.byId("myMeasureContent").innerHTML = data;
	})
}
getMeasures();

getValues = function()
{
	request.post("dataEntry/get-values.php",{
	data: {
		objectId: dom.byId("dataEntryUserId").innerHTML,
		objectPeriod: period,
		objectDate: globalDate
	}						
	}).then(function(data) 
	{
		dojo.byId("myValuesContent").innerHTML = data;
	})
}
getValues();

getInitiatives = function()
{
	dom.byId("editSaveDelete").innerHTML = "Edit";
	request.post("dataEntry/get-initiatives.php",{
	data: {
		objectId: dom.byId("dataEntryUserId").innerHTML,
		objectPeriod: period,
		objectDate: globalDate
	}						
	}).then(function(data) {
	
		dojo.byId("myInitiativeContent").innerHTML = data;
		/*
		
		skillGap = "skillGap"+pdpCount;
		intervention = "intervention"+pdpCount;
		startDate = "startDate"+pdpCount;
		dueDate = "dueDate"+pdpCount;
		resource = "resource"+pdpCount;
		comments = "comments"+pdpCount;
		bgColor = "Color"+pdpCount;
		
		dojo.byId("myPdp").innerHTML = combinedData+"</table>";*/
		moreDetailsTask = function(id)
		{
			getMyInitContent(id);
		}
		removeTooltip = function()
		{
			popup.close(objTooltipDialog);
		}
	
	});
}
getInitiatives();

getPip = function()
{
	request.post("dataEntry/get-pips.php",{
	data: {
		objectId: dom.byId("dataEntryUserId").innerHTML,
		//objectPeriod: period,
		//objectDate: globalDate
	}						
	}).then(function(data) 
	{
		dojo.byId("myPdp").innerHTML = data;
	})
}
getPip();

getInterpretation = function()
{
	request.post("dataEntry/get-interpretation.php",{
	data: {
		objectId: dom.byId("dataEntryUserId").innerHTML,
		//objectPeriod: period,
		objectDate: globalDate
	}						
	}).then(function(data) 
	{
		if(data == null) dijit.byId("myInterpretation").set("value", '');
		else dijit.byId("myInterpretation").set("value", data);	
	})
}
getInterpretation();

savePdp = function(id)//Put these later in a single js file since they are also being called in highScript.js LTK 06May2021 0817 Hours
{
	if(myPipEdit == "Delete" || myPipEdit == 'Edit')
	{
		/*if(myPipEdit == "Delete")
		{
			//pdpEdit = "Delete";
			pdpId = id;
		}
		else pdpId = pdpEditId;*/
		//console.log("Value = " + dom.byId("dataEntryUserId").innerHTML)
		request.post("individual/save-pdp.php",{
		//handleAs: "json",
		data: {
				userId : dom.byId("dataEntryUserId").innerHTML,
				toEdit: myPipEdit,
				pdpId: myPipEditId,
				pdpSkillGapInput : dom.byId("pdpSkillGapInput").value,
				pdpInterventionInput : dom.byId("pdpInterventionInput").value,
				pdpCommentsInput : dom.byId("pdpCommentsInput").value,
				pdpResourceInput : dom.byId("pdpResourceInput").value,
				pdpStartInput : dom.byId("pdpStartInput").value,
				pdpDueInput : dom.byId("pdpDueInput").value,
				pdpCompleteInput : dom.byId("pdpCompleteInput").value
		}
		}).then(function(){
			
			myPipEdit = null;
			/*dom.byId("initMsgContent").innerHTML = "Personal Improvement Plan Has Been Updated Updated";
			domStyle.set(dom.byId("initMsgContent"), 'display', 'block');
			var msgTimeout = setTimeout(function(){
					domStyle.set(dom.byId("initMsgContent"), 'display', 'none');
			},2000);*/
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
			/*dom.byId("initMsgContent").innerHTML = "Personal Improvement Plan Has Been Successfully Created";
			domStyle.set(dom.byId("initMsgContent"), 'display', 'block');
			var msgTimeout = setTimeout(function(){
					domStyle.set(dom.byId("initMsgContent"), 'display', 'none');
			},2000);*/
		});
	}
}
editPip = function(id)
{
	//console.log("editPip = " + id);
	myPipEdit = 'Edit';
	myPipEditId = id;
	request.post("individual/get-pdp.php",{
		handleAs: "json",
		data:{
			pdpId: myPipEditId
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

getMyInitContent = function(id)
{
	var initId = "init"+id;
	var initContent = null;
	request.post("initiatives/get-initiative.php",{
	// The URL of the request
	handleAs: "json",
	data: {
		initiativeId: id
	}						
	}).then(function(initiativeData)
	{
		initContent = "<div class='border border-primary rounded-3' style='overflow:hidden;'><table class='table table-striped table-hover table-bordered'><tr><th>Name</th><th>"+initiativeData["name"] +"</th><th>Impacts:</th><th>"+ initiativeData["Link"] +"</td></tr><tr><td>Sponsor</td><td>"+ initiativeData["sponsor"] +"</td><td>Owner:</td><td>"+ initiativeData["manager"] +"</td></tr><tr><td>Parent</td><td>"+initiativeData["Parent"]+"</td><td align='right'>Completion Date:</td><td>"+ initiativeData["completionDate"] +"</td></tr><tr><td>Start Date</td><td>"+ initiativeData["startDate"] +"</td><td align='right'>Due Date:</td><td>"+ initiativeData["dueDate"] + "</td></tr><tr><td>Budget</td><td>"+ initiativeData["budget"] +"</td><td align='right'>Cost So Far:</td><td>"+ initiativeData["damage"] +"</td></tr><tr><td>Deliverable</td><td>"+ initiativeData["deliverable"] +"</td></tr></table></div>";
		
	objTooltipDialog.set("content", initContent);
	popup.open({
			popup: objTooltipDialog,
			around: dom.byId(initId),
			//around: dom.byId(initId),//change to this since it was appearing at the wrong position
			orient: ["above"]
		});
	});	
}

updateComment = function(objectId, loggedInUser, note)
//updateComment = function(note)
{
	//alert(note + " and " + objectId);
	request.post("myDataEntry/save-comment.php",{
	data: {
		objectId: objectId,
		loggedInUser: loggedInUser,
		//objectId: "objectId",
		//loggedInUser: "loggedInUser",
		note:note
	}						
	}).then(function() 
	{
		//try refresh the page	
	})
}

function myEditorHandler(editorContent)
{
	request.post("scorecards/save-editor-content.php",{
	data: {
		Type: "interpretation",
		objectId: dom.byId("dataEntryUserId").innerHTML,
		saveContent: editorContent,
		period: period,
		creator: dom.byId('userIdJs').innerHTML
		}						
		}).then(function(data) 
		{
	
		});
}

var notesEditor = new InlineEditBox({
	editor: Editor,
	renderAsHtml: true,
	autoSave: false,
	onChange: myEditorHandler,
	//onClick: editorScroll,
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
	}, "myInterpretation");


}); //end of require
</script>
<body class="soria">
<div id="dataEntryUserId" style="display:none;"><?php echo "ind".$loggedInUser->user_id; ?></div>
<input style='width:90%; display:none;' id='individualInput'/>
<div id="msgContent" style="display:none; position:absolute; top:0%; left:50%; color:white; background-color:green;"></div>

<!--
*********************************************************************************
Measures
*********************************************************************************
-->
<div id="myDivMeasures">
    <div data-dojo-id="myMeasureTitle" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'My Objective Measures'">
	    <div id="myMeasureContent"></div>
    </div>
</div>
<!--
*********************************************************************************
Initiatives
*********************************************************************************
-->
<div id="myDivInitiatives">
 <div data-dojo-id="myInitiativeTitle" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'My Initiatives/Tasks'">
 	<div id="myInitiativeContent"></div>
 </div>
</div>
<!--
*********************************************************************************
Core Values
*********************************************************************************
-->
<div id="myDivValues" style="display:none;">
    <div data-dojo-id="myMeasureTitle" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Key Values/Personal Related Perfomance'">
	    <div id="myValuesContent"></div>
    </div>
</div>
<!--
*********************************************************************************
Personal Development Plan
*********************************************************************************
-->
<div id="myDivDevelopmentPlan">
 <div data-dojo-id="myDevelopmentPlanTitle" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Personal Development Plan'">
    <div id="myPdp"></div>
 </div>
</div>

<div id="myDivInterpretation">
    <div data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Perfomance Interpretation (Achievements & Personal Assessment)'">
	    <div id="myInterpretation"></div>
    </div>
</div>

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
<!--Reuse code to avoid missing out on updates in the main forms and functions-->
<?php include("../individual/pip-edit-form.php") ?>
<?php include("../initiatives/initiative-edit-form.html") ?>
</body>
</html>