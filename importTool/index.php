<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Import Tool</title>
    <link rel="stylesheet" href="../dijit/themes/soria/soria.css" media="all">     
    <link rel="stylesheet" href="../bootstrap/3.4.1/dist/css/bootstrap.min.css">      
    <link rel="stylesheet" href="../bootstrap_table/1.18.3/bootstrap-table.min.css">
    <link rel="stylesheet" href="../x-editable/bootstrap-editable.css">
    <link rel="stylesheet" href="../x-editable/1.5.1/inputs-ext/typeaheadjs/lib/typeahead.js-bootstrap.css">
    <link rel="stylesheet" href="../css/trafficLights.css">
    <link href="../font-awesome-5.15.3/css/fontawesome.min.css" media="all" rel="stylesheet" type="text/css"/>
    <style>
	.nowrap
	{
		white-space:nowrap;
		text-align:center;
	}
	</style>
</head>
<body class="soria">
<div id="expandedImport" style="display:none;"></div>
<ul class="nav nav-tabs">
  <li><a data-toggle="tab" href="#tabOne">Import Map</a></li>
  <li><a data-toggle="tab" href="#tabTwo">Dashboard</a></li>
  
</ul>
<div class="tab-content">
    <div id="tabOne" class="tab-pane fade in active">
        <div id="toolbar">
            <table class="table table-condensed table-borderless">
            <tr>
            <td><button id="button" class="btn btn-primary">New Import</button></td>
            <td><button id="buttonCell" class="btn btn-primary" style="display:none;">New Excel Cell</button></td>
            </tr>
            </table>
        </div>
        <table id="table"
            data-id-field="id"
            data-unique-id="id"
            data-pagination="true"
            data-toolbar="#toolbar" 
            data-show-columns="true"
            data-show-toggle="true"
            data-search="true" 
            
            data-show-search-clear-button="true"
            data-show-pagination-switch="true"
            data-show-export="true" 
            data-striped="true"
            data-classes="table table-hover table-condensed table-sm"
            data-detail-view="true"
           
            data-page-size="25"
            data-page-list="[25, 50, 100, all]"
            data-row-style="rowStyle"
            data-show-fullscreen="false"
            data-buttons-class="primary"
            data-visible="true">
        </table>
        
    </div>
    
    <div id="tabTwo" class="tab-pane fade" style="width:1300px; height:900px;">
		<!--<script>document.getElementById("tabSix").innerHTML='<object type="text/html" data="dashboard/dashboard.php" ></object>';</script>-->
        <object type="text/html" data="dashboard.php" style="overflow:auto;width:100%; height:100%;"></object>
        <!--<iframe src="dashboard.php" scrolling="yes" allowtransparency="true" frameborder="0"></iframe>-->
    </div>
    
    
    
</div><!--End of class="tab-content"-->

<script src="../jquery/3.1.0/jquery.js"></script>
<script src="../popper/popper.min.js"></script>

<script src="../bootstrap/3.4.1/dist/js/bootstrap.min.js"></script>

<script src="../bootstrap_table/1.18.3/bootstrap-table.min.js"></script>
<script src="../bootstrap_table/extensions/export/bootstrap-table-export.js"></script>
<script src="../bootstrap_table/extensions/filter/filter-control.min.js"></script>

<script src="../x-editable/bootstrap-editable.js"></script>
<script src="../x-editable/bootstrap-table-editable.js"></script>
<script src="../x-editable/1.5.1/inputs-ext/typeaheadjs/lib/typeahead.js"></script>
<script src="../x-editable/1.5.1/inputs-ext/typeaheadjs/typeaheadjs.js"></script>

<script src="../table-export/tableExport.min.js"></script>
<script src="../table-export/jsPDF/jspdf.min.js"></script>
<script src="../table-export/jsPDF-AutoTable/jspdf.plugin.autotable.js"></script>

<script type="text/javascript" src="../dojo/dojo.js" data-dojo-config="async: true, parseOnLoad:true"></script>
<script>
require([
//"dojo/parser",
"dojo/dom-style",
"dojo/dom",
"dojo/store/Memory",
"dojo/request",
"dijit/Dialog",
"dijit/form/FilteringSelect",
"dojo/domReady!"], function(domStyle, dom, Memory, request, Dialog, FilteringSelect)
{
	window.actionEvents = 
	{
		'click .deleteMap': function (e, value, row, index) 
		{
			var importId = row["id"];
			request.post("get-import-map-delete.php",{
			data:{
				importId: importId
				}	
			}).then(function(importMap)
				{
					deleteDialog = new Dialog({
						title: "Delete Map",
						content: "<table width='100%'><tr><td colspan='2'>Are you sure you would like to delete the following import map and all its cells?<br><span style='color:red;'>(This action is irreversible)</span)</td></tr><tr><td colspan='2'>"+importMap+"</td></tr><tr><td><button onClick='deleteImport("+importId+")'>Delete</button><button onClick='closeDeleteDialog()'>Cancel</button></td></tr></table>",
						style: "width: 50%"
					});
					deleteDialog.show();
				})	
		},
		'click .deleteCell': function (e, value, row, index) 
		{
			var cellId = row["id"];
			request.post("get-cell-delete.php",{
			data:{
				cellId: cellId
				}	
			}).then(function(importCell)
				{
					deleteDialog = new Dialog({
						title: "Delete Cell",
						content: "<table width='100%'><tr><td colspan='2'>Are you sure you would like to delete the following import cell?<br><span style='color:red;'>(This action is irreversible)</span)</td></tr><tr><td colspan='2'>"+importCell+"</td></tr><tr><td><button onClick='deleteCell("+cellId+")'>Delete</button><button onClick='closeDeleteDialog()'>Cancel</button></td></tr></table>",
						style: "width: 50%"
					});
					deleteDialog.show();
				})	
		}
	}
	
	function getColumns()
	{
			return [
				{	
					field: 'id',
					title: 'ID',
					sortable: true,
					align: 'left',
          			valign: 'top'
				},
				{	
					field: 'measureId',
					title: 'KPI ID',
					sortable: true,
					align: 'left',
          			valign: 'top'
				},
				{	
					field: 'measureId',
					title: 'Measure',
					sortable: true,
					editable: {
						title: 'Type in the Measure',
						type:'select',
						source: 'get-measures.php',
						mode: 'inline',
						url: "import-map-edit.php",
						success: function(response, newValue) {
							$table.bootstrapTable("refresh", {
								//url: "get-expenses.php",
								silent: true
							});
						},
					}
				},
				{	
					field: 'emailSubject',
					title: 'Email Subject',
					sortable: true,
					align: 'center',
          			valign: 'top',
					editable: {
						type:'text',
						mode: 'inline',
						url: "import-map-edit.php",
						success: function(response, newValue) {
							$table.bootstrapTable("refresh", {
								silent: true
							});
						}
					}
				},
				{	
					field: 'frequency',
					title: 'Frequency',
					align: 'left',
          			valign: 'top',
					sortable: true,
					editable: {
						type:'select',
						source: [
							{value: 'daily', text: 'Daily'},
							{value: 'weekly', text: 'Weekly'},
							{value: 'monthly', text: 'Monthly'},
							{value: 'quarterly', text: 'Quarterly'}
						],
						mode: 'inline',
						url: "import-map-edit.php",
						success: function(response, newValue) {
							$table.bootstrapTable("refresh", {
								silent: true
							});
						}
					}
				},
				{	
					field: 'sender',
					title: 'Sender',
					align: 'center',
          			valign: 'top',
					filterControl: "select",
					sortable: true,
					class: 'nowrap',
					editable: {
						type:'text',
						mode: 'inline',
						url: "import-map-edit.php",
						success: function(response, newValue) {
							$table.bootstrapTable("refresh", {
								silent: true
							});
						}
					}
				},
				{
					field: 'admin',
					align: 'center',
          			valign: 'top',
					events:'actionEvents',
					title: 'Admin',
					align: 'center'
				}
			];
	}
	
	function getColumnsTwo()
	{
			return [
				{	
					field: 'id',
					title: 'ID',
					align: 'left',
          			valign: 'top',
				},
				{	
					field: 'month',
					title: 'Period',
					editable: {
						//title: 'Type in the Measure',
						type:'select',
						source: [
							{value: 'Current Month', text: 'Current Month'},
							{value: 'January', text: 'January'},
							{value: 'February', text: 'February'},
							{value: 'March', text: 'March'},
							{value: 'April', text: 'April'},
							{value: 'May', text: 'May'},
							{value: 'June', text: 'June'},
							{value: 'July', text: 'July'},
							{value: 'August', text: 'August'},
							{value: 'September', text: 'September'},
							{value: 'October', text: 'October'},
							{value: 'November', text: 'November'},
							{value: 'December', text: 'December'},
							{value: 'Quarter One', text: 'Quarter One'},
							{value: 'Quarter Two', text: 'Quarter Two'},
							{value: 'Quarter Three', text: 'Quarter Three'},
							{value: 'Quarter Four', text: 'Quarter Four'},
						],
						params:function(params)
						{
							//console.log( params.value + " and " + params.pk + " and " + params.name );
							return params;
						 },
						mode: 'inline',
						url: "import-cell-edit.php?parentId="+dom.byId("expandedImport").innerHTML,
						success: function(response, newValue) {
							$('#tableTwo').bootstrapTable("refresh", {
								url: "get-import-details.php",
								silent: true,
								queryParams: function (p) 
								{
									return {
										id: dom.byId("expandedImport").innerHTML				
									};
								}
							});
						},
						
					}
				},
				{	
					field: 'value',
					title: 'Actual Column',
					sortable: true,
					align: 'center',
          			valign: 'top',
					editable: {
						type:'text',
						mode: 'inline',
						url: "import-cell-edit.php?parentId="+dom.byId("expandedImport").innerHTML,
						success: function(response, newValue) {
							$('#tableTwo').bootstrapTable("refresh", {
								url: "get-import-details.php",
								silent: true,
								queryParams: function (p) 
								{
									return {
										id: dom.byId("expandedImport").innerHTML				
									};
								}
							});
						}
					}
				},
				{	
					field: 'target',
					title: 'Target Column',
					sortable: true,
					align: 'center',
          			valign: 'top',
					editable: {
						type:'text',
						mode: 'inline',
						emptytext: 'No Value',
						url: "import-cell-edit.php?parentId="+dom.byId("expandedImport").innerHTML,
						success: function(response, newValue) {
							$('#tableTwo').bootstrapTable("refresh", {
								url: "get-import-details.php",
								silent: true,
								queryParams: function (p) 
								{
									return {
										id: dom.byId("expandedImport").innerHTML				
									};
								}
							});
						}
					}
				},
				{	
					field: 'name',
					title: 'Name Column',
					align: 'left',
          			valign: 'top',
					sortable: true,
					editable: {
						type:'text',
						mode: 'inline',
						url: "import-cell-edit.php?parentId="+dom.byId("expandedImport").innerHTML,
						success: function(response, newValue) {
							$('#tableTwo').bootstrapTable("refresh", {
								url: "get-import-details.php",
								silent: true,
								queryParams: function (p) 
								{
									return {
										id: dom.byId("expandedImport").innerHTML				
									};
								}
							});
						}
					}
				},
				{
					field: 'admin',
					align: 'center',
          			valign: 'top',
					events:'actionEvents',
					title: 'Admin',
					align: 'center'
				}
			];
	}
	
	var $table = $('#table');
	$table.bootstrapTable({
		url: "get-import-map.php",
		method: 'post',
		contentType: 'application/x-www-form-urlencoded',
        exportTypes: ['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'pdf'],
		columns: getColumns(),
		language: {
			infoEmpty: "No Expenses Returned",
		}
	});
	
	$table.on('expand-row.bs.table', function (e, index, row, $detail) 
	{
		domStyle.set(dom.byId("buttonCell"), 'display', 'block');
		$.each(row, function (key, value) 
		{
			if(key == "measureId")
			{
				dom.byId("expandedImport").innerHTML = value;

				//$detail.html('Loading details...');
				
				$detail.html('<table id="tableTwo" data-id-field="id" data-unique-id="id"></table>').find('#tableTwo').bootstrapTable({
					url: "get-import-details.php",
					method: 'post',
					contentType: 'application/x-www-form-urlencoded',
					queryParams: function (p) 
					{
						return {
							id: value					
						};
					},
					columns: getColumnsTwo()
				})
			}
		});
	});
	
	$table.on('collapse-row.bs.table', function (e, index, row, $detail) 
	{
		domStyle.set(dom.byId("buttonCell"), 'display', 'none');
	});
	
	var $button = $('#button');
	$button.click(function () {
      $table.bootstrapTable('insertRow', {
        index: 0,
        row: {
          id: "New",
          item: 'New Import',
        }
      })
    })
	
	var $buttonCell = $('#buttonCell');
	$buttonCell.click(function () {
      $('#tableTwo').bootstrapTable('insertRow', {
        index: 0,
        row: {
          id: "New",
          item: 'New Cell',
        }
      })
    })
	
	deleteImport = function(importId)
	{
		request.post("delete-import.php",{
			data:{
				importId: importId
			}	
		}).then(function()
		{
			deleteDialog.hide();	
			$('#table').bootstrapTable('refresh', {
					silent: true
				});
		})
	}
	
	deleteCell = function(cellId)
	{
		request.post("delete-cell.php",{
			data:{
				cellId: cellId
			}	
		}).then(function()
		{
			deleteDialog.hide();	
			$('#tableTwo').bootstrapTable('refresh', {
				silent: true,
				queryParams: function (p) 
					{
						return {
							id: dom.byId("expandedImport").innerHTML				
						};
					}
				});
		})
	}
	
	closeDeleteDialog = function()
	{
		deleteDialog.hide();
	}
	
})
</script>
</body>
</html>