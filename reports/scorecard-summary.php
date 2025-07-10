<div id="idParkingLot" style="display:none"></div>
<button class="btn btn-primary btn-sm" onclick="moveLeft();" title="Previous Page">
  <span class="fa fa-arrow-left" style="height:18px !important;"></span>
</button>
<button type="button" class="btn btn-primary btn-sm" onClick="moveRight()" title="Next Page">
  <span class="fa fa-arrow-right" style="height:18px !important;"></span>
</button>
<button class="btn btn-primary btn-sm"  onclick="firstReport();">Home</button>&nbsp;

<div class="btn-group" style="float:right;">
  <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
    Select Department
  </button>
  <ul class="dropdown-menu" id="departmentList"></ul>
</div>

<div id="scorecardSummary"></div>

<script>
require([
"dojo/request",
"dojo/dom",
"dojo/domReady!"
], function(request, dom)
{	
    request.post("reports/get-departments-select.php", 
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
		},1);
		
	});

    getScorecardSummaryReport = function(orgId)
	{
		request.post("reports/get-scorecard-summary.php", 
		{
			data:{
					orgId: orgId
					//globalDate: globalDate
				}
		}).then(function(report)
		{
			dom.byId("scorecardSummary").innerHTML = report;
		})
	}
    getScorecardSummaryReport("org1");
    dom.byId("idParkingLot").innerHTML = "org1";

    moveRight = function()
	{
		//console.log("Current id is " + dom.byId("idParkingLot").innerHTML);
		request.post("reports/get-next-id.php", 
		{
			//handleAs: "json",
			data:{
					orgId: dom.byId("idParkingLot").innerHTML,
				}
		}).then(function(currentId)
		{
			dom.byId("idParkingLot").innerHTML = currentId;
			getScorecardSummaryReport(currentId);
		})
	}

    moveLeft = function()
	{
		request.post("reports/get-previous-id.php", 
		{
			//handleAs: "json",
			data:{
					orgId: dom.byId("idParkingLot").innerHTML
				}
		}).then(function(currentId)
		{
			dom.byId("idParkingLot").innerHTML = currentId;
			getScorecardSummaryReport(currentId);
		})
	}

    firstReport = function()
	{
		getScorecardSummaryReport('org1');
		dom.byId("idParkingLot").innerHTML = "org1";
	}

});
</script>