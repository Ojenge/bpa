<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.11.1/bootstrap-table.min.css">
<div id="toolbar">
    <button class="btn btn-primary btn-sm" onClick="directivesNew()">
        <i class="glyphicon glyphicon-plus"></i> New
    </button>
    <button class="btn btn-primary btn-sm" onClick="directivesPrint()">
        <i class="glyphicon glyphicon-print"></i> Print
    </button>
    <button type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-primary btn-sm no-print">
        Select Department<span class="caret no-print"></span>
    </button>&nbsp;
    <ul class="dropdown-menu no-print" aria-labelledby="dLabel">
    <li><a href="#" onClick="changeMinistry(00)" id="id00">All Departments</a></li>
    <li><a href="#" onClick="changeMinistry(01)" id="id01">HR & Admin</a></li>
    <li><a href="#" onClick="changeMinistry(02)" id="id02">ICT</a></li>
    
    </ul>
    <div id="ministryTitle" style="float:right; text-align:center; font-weight:bold; font-size:18px;">ICT</div>
</div>
<table id="table"
data-pagination="true"
data-toolbar="#toolbar" 
data-show-columns="true"
data-show-toggle="true"
data-search="true" 
data-show-pagination-switch="true"
data-show-export="true" 
data-striped="true"
data-classes="table table-hover table-condensed"
data-detail-view="true"
data-show-footer="false"
data-row-style="rowStyle">
<thead>
    <tr>
        
        <th data-field="name">Name</th>
        <th data-field="position">Position</th>
        <th data-field="department">Department</th>
        <th data-field="kpi">KPIs</th>
        <th data-field="projectScore">Project Score</th>
        <th data-field="coreValues">Core Values</th>
        <th data-field="total">Total</th>
        <th data-field="rating">Overall Rating</th>
    </tr>
</thead>
</table>

<script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.11.1/bootstrap-table.min.js"></script>
<script type="text/javascript" src="../../dojo/dojo.js" data-dojo-config="async: true, parseOnLoad:true"></script>

<script>
require([
"dojo/dom",
"dojo/request",
"dojo/store/Memory",
"dijit/Dialog",
"dijit/form/FilteringSelect",
"dojo/domReady!"], function(dom, request, Memory, Dialog, FilteringSelect)
{
	var $table = $('#table');
	$table.bootstrapTable({
		url: "get-staff-summary.php",
		method: 'post',
    	contentType: 'application/x-www-form-urlencoded',
		//queryParams: function (p) {
		//	return {
				//filter: $('#filterId').text()
				//filter: dom.byId('filterId').innerHTML
		//	};
		//},
	});
	//dom.byId("tableHeader").innerHTML = "<h4>"+directives[0].action_by+"</h4>";
	$table.on('expand-row.bs.table', function (e, index, row, $detail) 
	{
		$detail.html('Loading details...');
		$.each(row, function (key, value) 
		{
			if(key == "id")
			{
				request.post("get-staff-details.php",{
					data: { id: value }				
				}).then(function(returnedDetails) 
				{
					$detail.html(returnedDetails.replace(/\n/g, '<br>'));
				})
			}
		});
	})
})

function rowStyle(row, index) 
{
    var classes = ['active', 'success', 'info', 'warning', 'danger'];
    if (index % 2 === 0) 
	{
        return {classes: classes[2]};
    }
    return {};
}
</script>