<link rel="stylesheet" href="../../../bootstrap/3.3.7/dist/css/bootstrap.min.css">
<style type="text/css">
table.reportTable
{
	font-size: 13px; 
	border-collapse: collapse; 
	border-top: 1px solid #9baff1; 
	border-bottom: 1px solid #9baff1;
}
table.reportTable th
{
	padding: 3px; 
	border-right: 1px solid #aabcfe; 
	border-left: 1px solid #aabcfe; 
	border-top: 1px solid #aabcfe;
	font-weight:bold;
}
table.reportTable td
{
	padding: 3px; 
	border-right: 1px solid #aabcfe; 
	border-left: 1px solid #aabcfe; 
	border-top: 1px solid #aabcfe;
}
.pdfIcon {
  background-image: url(images/icons/pdfIcon16.png);
  background-repeat: no-repeat;
  width: 16px;
  height: 16px;
  text-align: center;
}
</style>
<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="../../../bootstrap/3.3.7/dist/js/bootstrap.min.js"></script>-->
<script>
var dbReport = "false";

require([
"dojo/request",
"dojo/dom",
"dojo/domReady!"
], function(request, dom)
{
	/*if(dijit.byId("newCustomReportDialog")) dijit.byId("newCustomReportDialog").destroyRecursive();
	if(dijit.byId("newReportDialog")) dijit.byId("newReportDialog").destroyRecursive();
	if(dijit.byId("newInitiativeReportDialog")) dijit.byId("newInitiativeReportDialog").destroyRecursive();
	if(dijit.byId("newCascadeReportDialog")) dijit.byId("newCascadeReportDialog").destroyRecursive();
	if(dijit.byId("selectObjectDialog")) dijit.byId("selectObjectDialog").destroyRecursive();*/
	
	var waitForReport = setTimeout(function()
	{
		getDepartmentReport(0, 'org1');
		dom.byId("idParkingLot").innerHTML = 'org1';
	},1000);
	
	moveRight = function()
	{
		//console.log("Current id is " + dom.byId("idParkingLot").innerHTML);
		request.post("get-next-id.php", 
		{
			//handleAs: "json",
			data:{
					orgId: dom.byId("idParkingLot").innerHTML
				}
		}).then(function(currentId)
		{
			dom.byId("idParkingLot").innerHTML = currentId;
			getDepartmentReport(0, currentId);
		})
	}
	moveLeft = function()
	{
		request.post("get-previous-id.php", 
		{
			//handleAs: "json",
			data:{
					orgId: dom.byId("idParkingLot").innerHTML
				}
		}).then(function(currentId)
		{
			dom.byId("idParkingLot").innerHTML = currentId;
			getDepartmentReport(0, currentId);
		})
	}
	firstReport = function()
	{
		getDepartmentReport(0, 'org1');
		dom.byId("idParkingLot").innerHTML = 'org1';
	}
	request.post("get-departments-select.php", 
	{
		//handleAs: "json",
		//data:{
		//		orgId: dom.byId("idParkingLot").innerHTML
		//	}
	}).then(function(departmentList)
	{
		var waitForList = setTimeout(function()
	{
		dom.byId("departmentList").innerHTML = departmentList;
	},1000);
		
	})
})

</script>
<script type="text/javascript" src="js/report.js"></script>
<script type="text/javascript" src="js/dndNoMeasure.js"></script>

<div id="idParkingLot" style="display:none"></div>
<button class="btn btn-primary btn-sm" onclick="moveLeft();" title="Previous Page">
  <span class="fa fa-arrow-left" style="height:18px !important;"></span>
</button>
<button type="button" class="btn btn-primary btn-sm" onClick="moveRight()" title="Next Page">
  <span class="fa fa-arrow-right" style="height:18px !important;"></span>
</button>
<button class="btn btn-primary btn-sm"  onclick="firstReport();">Home</button>&nbsp;

<div class="dropdown" style="float:right;">
    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
        Select Department
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1" id="departmentList"></ul>
</div>

<div id="displayReport"></div>