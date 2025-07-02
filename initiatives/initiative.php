<?php 
require_once("../admin/models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
$userPermission = fetchUserPermissions($loggedInUser->user_id);
$view = "True";
foreach($userPermission as $id)
{
	if($id["permission_id"] == "2" || $id["permission_id"] == "3" || $id["permission_id"] == "3000")
	$view = "False";
}?>
<html>
<head>

<style>
@import "css/_Gauge.css";
.vtop {
    display: inline-block;
    vertical-align: top;
    float: none;
}
#gantt
{
	max-width: 3300px;
	margin: 1em auto;
}
</style>
</head>
<body>
<!--<script src="../bootstrap/3.3.7/dist/js/bootstrap.min.js"></script>
<script src="../bootstrap_table/bootstrap-table.js"></script>
<script src="../x-editable/bootstrap-editable.js"></script>
<script src="../x-editable/bootstrap-table-editable.js"></script>-->

<!--<script src="../../highChartsGantt901/code/highcharts-gantt.js" type="text/javascript"></script>-->
<script type="text/javascript" src="js/initiative.js"></script>
<script type="text/javascript" src="js/gantt.js"></script>
<script type="text/javascript" src="js/pdp.js"></script>
<script type="text/javascript">
//var select1, select2, select3, select;
var mode = "browser", updateMode = "Edit";
var $table;
require([
"dojo/dom",
"dojo/dom-style",
"dojo/dom-class",
"dojo/store/Memory",
"dojo/parser",
"dijit/TitlePane",	
"dijit/Menu",
"dijit/MenuItem",
"dijit/form/DateTextBox",
"dijit/form/FilteringSelect",
"dojox/gauges/GlossyHorizontalGauge",		
//'dojox/widget/Calendar',
"dojo/request",
"dojo/json",
	
"dojo/domReady!"
], function(dom, domStyle, domClass, Memory, parser, TitlePane, Menu, MenuItem, DateTextBox, FilteringSelect, GlossyHorizontalGauge, request, json){

request.post("functions/functions-rights.php",{
handleAs: "json",
data: {
	functionToCall:"getUserList"
}						
}).then(function(userData) 
{			
	var userStore = new Memory({data:userData});
	
	var userSelect = new FilteringSelect({
	name: "userListName",
	//placeHolder: "Select a User",
	store: userStore,
	searchAttr: "User",
	maxHeight: -1, 
	onChange: function(){
		//dom.byId("userIdInd").innerHTML = this.item.id;
		initiativeGantt(this.item.id);
	}
	}, "userListGantt").startup();
});

initiativeGantt("defaultUser");

if (dijit.byId("initiativeStatusGauge")) 
{
	dijit.byId("initiativeStatusGauge").destroy(true);
}		
var initiativeStatusGauge = null;
// create an  Horizontal Gauge
initiativeStatusGauge = new GlossyHorizontalGauge({
	background: [255, 255, 255, 0],
	id: "initiativeStatusGauge",
	title: "Value",
	majorTicksColor:"000000",
	minorTicksColor:"000000",
	//minorTicksInterval:200,
	width: 220,
	height: 40,
	hideValue: true
	//hideValues:"true"
}, dojo.byId("initiativeGauge"));

initiativeStatusGauge.startup();

function changeGaugeColor(v)
{
	initiativeStatusGauge.set('color', v);
}
//changeGaugeColor("003399");
//changeNeedleColor("0099cc");
changeNeedleColor("");
function changeNeedleColor(v)
{
	initiativeStatusGauge.set('markerColor', v);
}

function getColumns()
{
	return [
	{	
		field: 's_no',
		title: 'S/N',
		valign: 'top',
		sortable: true
	},
	{	
		field: 'name',
		title: 'Name',
		valign: 'top',
		sortable: true
	},
	{	
		field: 'budget',
		title: 'Budget',
		valign: 'top',
		sortable: true
	},
	{	
		field: 'startDate',
		title: 'Start Date',
		valign: 'top',
		sortable: true
	},
	{	
		field: 'dueDate',
		title: 'Due Date',
		valign: 'top',
		formatter: 'dueDateFormatter',
		sortable: true
	},
	{	
		field: 'owner',
		title: 'Project Owner',
		filterControl: 'select',
		sortable: true
	},
	{	
		field: 'status',
		title: 'Status',
		filterControl: 'select',
		sortable: true
	},
	{	
		field: 'impact',
		title: 'Linked To',
		filterControl: 'select',
		sortable: true
	},
	{
		field: 'admin',
		title: 'Admin',
		//events:'actionEvents',
		valign: 'top',
		align: 'center'
	}];
}

$table = $('#tableInitiative');
 	
$table.bootstrapTable({
	url: "initiatives/get-all-initiatives.php",
	method: 'post',
	contentType: 'application/x-www-form-urlencoded',
	queryParams: function (p) 
	{
		return {
			filter:'1',
			userId: dom.byId('userIdJs').innerHTML
			};
	},
	columns: getColumns(),
	language: 
	{
		infoEmpty: "No Initiatives Returned",
	}
});

$table.on('expand-row.bs.table', function (e, index, row, $detail) 
{
	$detail.html('Loading details...');
	$.each(row, function (key, value) 
	{
	 if(key == "id")
	 {
		request.post("initiatives/get-initiative-details.php",{
			data: { id: value }				
		}).then(function(returnedDetails) 
		{
			$detail.html(returnedDetails.replace(/\n/g, '<br>'));
			//dom.byId("expandedProject").innerHTML = value;
			
			/*request.post("initiatives/get-team.php",{
				data: {initiativeId: value}
			}).then(function(team)
			{
				var teamMembers = "teamMembers"+value;
				dom.byId(teamMembers).innerHTML = team;
			});*/
			
		
		});
	 }
	});
});

$table.on('collapse-row.bs.table', function (e, index, row, $detail) 
{
	
});

refreshInitiativeTable = function()
{
	$table.bootstrapTable("refresh", {
		url: "initiatives/get-all-initiatives.php",
		silent: true
	});
}

function getColumnsPIP()
{
	return [
	{	
		field: 's_no',
		title: 'S/N',
		valign: 'top',
		sortable: true
	},
	{	
		field: 'skillGap',
		title: 'Skill Gap',
		valign: 'top',
		sortable: true
	},
	{	
		field: 'intervention',
		title: 'Intervention',
		valign: 'top',
		sortable: true
	},
	{	
		field: 'startDate',
		title: 'Start Date',
		valign: 'top',
		sortable: true
	},
	{	
		field: 'dueDate',
		title: 'Due Date',
		valign: 'top',
		formatter: 'dueDateFormatter',
		sortable: true
	},
	{	
		field: 'owner',
		title: 'Owner',
		filterControl: 'select',
		sortable: true
	},
	{	
		field: 'comments',
		title: 'Comments',
		sortable: true
	},
	{
		field: 'admin',
		title: 'Admin',
		//events:'actionEvents',
		valign: 'top',
		align: 'center'
	}];
}

$tablePIP = $('#tablePIP');
	
$tablePIP.bootstrapTable({
	url: "individual/get-all-pips.php",
	method: 'post',
	contentType: 'application/x-www-form-urlencoded',
	queryParams: function (p) 
	{
		return {
			filter:'1'
			//filter: dom.byId('filterId').innerHTML
			};
	},
	columns: getColumnsPIP(),
	language: 
	{
		infoEmpty: "No Personal Improvement Plans Returned",
	}
});

});
function rowStyle(row, index) 
{
    var classes = ['table-active', 'table-primary', 'table-info', 'table-warning', 'table-danger', 'table-success'];
    if (index % 2 === 0 && index / 2 < classes.length) 
	{
       // return {classes: classes[1]};
    }
    return {
		/*css: {
			color: 'blue'
		  }*/
		};
}
function buttons() 
{
    return {
      btnAdd: {
        text: 'New Initiative',
        icon: 'fa-plus',
        event: function () 
		{
          editInitiative("New");
        },
        attributes: 
		{
          title: 'Add a new initiative'
        }
      }
    }
}
function buttonsPIP() 
{
    return {
      btnAdd: {
        text: 'New PIP',
        icon: 'fa-plus',
        event: function () 
		{
			document.getElementById("pdpSkillGapInput").value = "";
			document.getElementById("pdpInterventionInput").value = "";
			document.getElementById("pdpCommentsInput").value = "";
			document.getElementById("pdpResourceInput").value = "";
			document.getElementById("pdpStartInput").value = "";
			document.getElementById("pdpDueInput").value = "";
			document.getElementById("pdpCompleteInput").value = "";
			dijit.byId("pdpDialog").show();
        },
        attributes: 
		{
          title: 'Add a new PIP'
        }
      }
    }
}
function dueDateFormatter(value, row) 
{
	var today = new Date();
	var dd = String(today.getDate()).padStart(2, '0');
	//var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
	var yyyy = today.getFullYear();
	const month = today.toLocaleString('default', { month: 'short' }); //long, short, narrow
	today = dd + ' ' + month + ' ' + yyyy;
	today = new Date(today);
	var dbDate = new Date("01 Jan 1970");
	var color = "red";
	if(today > dbDate)
	return '<div style="color: ' + color + '">' +
      
      value +
      '</div>'
	//var icon = row.dueDate % 2 === 0 ? 'fa-star' : 'fa-star-and-crescent'
	//return '<i class="fa ' + icon + '"></i> ' + value
}

</script>

<nav>
  <div class="nav nav-tabs" role="tablist">
  <!-- Home Tab  -->
    <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Home</button>
<!-- All Initiative Tab  -->
    <button class="nav-link" id="nav-allInitiatives-tab" data-bs-toggle="tab" data-bs-target="#nav-allInitiatives" type="button" role="tab" aria-controls="nav-allInitiatives" aria-selected="false">All Initiatives</button>
    <!-- Gantt Tab  -->
    <button class="nav-link" id="nav-gantt-tab" data-bs-toggle="tab" data-bs-target="#nav-gantt" type="button" role="tab" aria-controls="nav-gantt" aria-selected="false">Gantt Chart</button>
    <button class="nav-link" id="nav-pip-tab" data-bs-toggle="tab" data-bs-target="#nav-pip" type="button" role="tab" aria-controls="nav-pip" aria-selected="false">Personal Development Plans</button>
    <div id="initMsgContent" style="margin-left: auto; margin-right: auto; color:#090; font-weight:400;"></div>
  </div>
</nav>
<div class="tab-content">
<div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
<div class="border border-primary rounded-3">
<table class="table table-bordered table-responsive table-sm table-condensed table-striped">
<tr>
<td class="border-end-0" align="left">
<a href='javascript:void(0)' title='Previous' data-toggle='tooltip' onclick='previous()'><i class='fa fa-arrow-left'></i></a>
<a href='javascript:void(0)' title='Next' data-toggle='tooltip' onclick='next()'><i class='fa fa-arrow-right'></i></a>
</td>
<td class="border-start-0 " colspan="3" align="right">
<?php if($view == "False") {?>
<a href='javascript:void(0)' title='Edit Initiative' data-toggle='tooltip' onclick='editInitiative()'><i class='fa fa-edit'></i></a>
<a id='archive' href='javascript:void(0)' title='Archive Initiative' data-toggle='tooltip' onclick='archiveInitiative()'><i class='fa fa-archive'></i></a>
<a href='javascript:void(0)' title='Delete Initiative' data-toggle='tooltip' onclick='confirmDeleteInitiative()'><i class='fa fa-trash'></i></a>     
<?php }?>
</td>
</tr>
    <tr>
    	<td colspan="4"><strong>Initiative Name</strong><br><div id="initiativeNameDiv"></div></td>
    </tr>
    <tr>
    	<td class="col-3"><strong>Sponsor</strong><br><div id="sponsorDiv"></div></td>
        <td class="col-3"><strong>Project Owner</strong><br><div id="managerDiv"></div></td>
        <td class="col-3"><strong>Parent Initiative</strong><br><div id="parentDiv"></div></td>
        <td class="col-3"><strong>Linked Scorecard Element:</strong><br><div id="objectImpactedDiv"></div></td>
    </tr>
    <tr>
        <td class="col-3"><strong>Deliverable</strong><br><table><tr><td><div id="tdDeliverableColor"></div></td><td><div id="deliverableDiv"></div></td></tr></table></td>
        <td class="col-3"><strong>Target/Scope</strong><br><div id="scopeDiv"></div></td>
        <td class="col-3"><strong>Budget</strong><br><div id="budgetDiv"></div></td>
        <td class="col-3"><strong>Cost So Far</strong><br><table><tr><td><div id="tdDamageColor"></div></td><td><div id="damageDiv"></div></td></tr></table></td>
    </tr>
    <tr>
        <td class="col-3"><strong>Start Date</strong><br><div id="startDateDiv"></div></td>
        <td class="col-3"><strong>End Date</strong><br><div id="endDateDiv"></div></td>
        <td class="col-3"><strong>Completion Date</strong><br><div id="completionDateDiv"></div></td>
        <td class="col-3"><div id="initiativeGauge"></div></td>
    </tr>
    <tr>
    	<td class="col-3"><strong>Status</strong><br><div id="statusDiv"></div></td>
        <td class="col-3"><strong>Completion Rate</strong><br><div id="percentageCompletionDiv"></div></td>
        <td class="col-3"><strong>Interpretation</strong><br><div id="statusDetailsDiv"></div></td>
        <td class="col-3"><strong>Way Forward</strong><br><div id="statusNotesDiv"></div></td>
    </tr>
</table>
</div>

<div id="divConversation" data-dojo-type="dijit/TitlePane" data-dojo-props="title:'Initiative/Project Conversations', open: true" style=" margin-left:-2px; display:none;">
        <!--<div id="userId" style="display:none;"><?php //echo "ind".$loggedInUser->user_id; ?></div>-->
        <div id="conversationHistory"></div>
        <table width="100%"><!--
            <tr><td colspan="2">Project Commentary</td></tr>
            <tr><td>Recepients</td><td></td></tr>-->
            <tr><td colspan=""><div contenteditable="true" id='conversation' style="width:90%; height:50px; padding:5px; border:1px solid #ccc; overflow-y:scroll;"></div></td></tr>
            <tr><td colspan="" align="left"><button type='button' class="btn btn-sm btn-outline-primary" onClick="postComment()">Post New Comment</button></td></tr>
        </table>
    </div>
</div>
  <div class="tab-pane fade" id="nav-allInitiatives" role="tabpanel" aria-labelledby="nav-allInitiatives-tab">
  <div class="border border-primary rounded-3">
  <table id="tableInitiative"
		data-id-field="id"
		data-unique-id="id"
		data-pagination="true"
		data-toolbar="#toolbar" 
		data-show-columns="true"
		data-show-toggle="false"
		data-search="true" 
		data-show-pagination-switch="true"
		data-show-export="true" 
		data-striped="true"
		data-classes="table table-hover table-responsive table-bordered table-sm table-condensed table-striped"
		data-detail-view="true"
        data-buttons="buttons"
        data-filter-control="true"
		data-show-footer="false"
		data-row-style="rowStyle"
		data-visible="true">
	</table>
	</div>
  </div>
<div class="tab-pane fade" id="nav-gantt" role="tabpanel" aria-labelledby="nav-gantt-tab">
	<div valign="top" class="noPrint" style="position:absolute; left:0px;"><input style='width:90%' id='userListGantt'/></div>
    <br>
	<div id="gantt"></div>
</div>
<div class="tab-pane fade" id="nav-pip" role="tabpanel" aria-labelledby="nav-pip-tab">
	<div class="border border-primary rounded-3">
  <table id="tablePIP"
		data-id-field="id"
		data-unique-id="id"
		data-pagination="true"
		data-toolbar="#toolbar" 
		data-show-columns="true"
		data-show-toggle="false"
		data-search="true" 
		data-show-pagination-switch="true"
		data-show-export="true" 
		data-striped="true"
		data-classes="table table-hover table-responsive table-bordered table-sm table-condensed table-striped"
		data-detail-view="true"
        data-buttons="buttonsPIP"
        data-filter-control="true"
		data-show-footer="false"
		data-row-style="rowStyle"
		data-visible="true">
	</table>
	</div>
</div>
</div>

<?php include("initiative-edit-form.html") ?>

</body>
</html>