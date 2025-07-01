require([
	"dojo/request",
	"dojo/store/Memory",
	"dojo/store/Observable",
	"dojox/charting/StoreSeries",
], function(request, Memory, Observable, StoreSeries){
updateChart = function()
{
	//alert("Ready for: "+period);
request.post("scorecards/measures/get-kpi-scores.php",{
handleAs: "json",
data: {
	objectId: kpiGlobalId,
	objectType: kpiGlobalName,
	objectPeriod: period,
	objectDate: globalDate
}
}).then(function(chartData) 
	{		alert("data ready for display :-)"+ kpiGlobalType);		
		chartStore = Observable(new Memory({
			data: {
				items: chartData
			}
			}));
		chart.removePlot("redThresholdPlot");
		chart.removeSeries("redThresholdValues");
		chart.removePlot("yellowThresholdPlot");
		chart.removeSeries("yellowThresholdValues");
		chart.removePlot("greenThresholdPlot");
		chart.removeSeries("greenThresholdValues");
		chart.removePlot("darkGreenThresholdPlot");
		chart.removeSeries("darkGreenThresholdValues");
													
		chart.addPlot("redThresholdPlot", {type: "Areas"});
		chart.addSeries("redThresholdValues", new StoreSeries(chartStore, {query: {}}, "red"),
			{plot: "redThresholdPlot", stroke: {color:"#FF0000"}, fill: "#FF0000"});
		
		chart.addPlot("yellowThresholdPlot", {type: "Areas"});
		chart.addSeries("yellowThresholdValues", new StoreSeries(chartStore, {query: {}}, "green"),
			{plot: "yellowThresholdPlot", stroke: {color:"#FFD900"}, fill: "#FFD900"});
			
		chart.addPlot("greenThresholdPlot", {type: "Areas"});
		chart.addSeries("greenThresholdValues", new StoreSeries(chartStore, {query: {}}, "darkgreen"),
			{plot: "greenThresholdPlot", stroke: {color:"#33CC00"}, fill: "#33CC00"});									
			
		chart.addPlot("darkGreenThresholdPlot", {type: "Areas"});
		chart.addSeries("darkGreenThresholdValues", new StoreSeries(chartStore, {query: {}}, "blue"),
			{plot: "darkGreenThresholdPlot", stroke: {color:"#006600"}, fill: "#006600"});
			
		chart.addPlot("blueThresholdPlot", {type: "Areas"});
		chart.addSeries("blueThresholdValues", new StoreSeries(chartStore, {query: {}}, "upperLimit"),
			{plot: "blueThresholdPlot", stroke: {color:"#006600"}, fill: "#0000FF"});
			
		chart.addSeries("Series D", new StoreSeries(chartStore, {query: {}}, "actual"));
			
		var axisStore = new Memory({data:chartData});
		var axisData = new Array();
		//axisData[0] = "[";
		//var axisDate;
		var axisCount = 0;
		axisStore.query({}).forEach(function(axis){						  
			axisData[axisCount] = {value: axisCount+1, text: axis.date};
			axisCount++;
		});
		//axisData[axisCount] = "]";
			
		chart.removeAxis("x");
		chart.addAxis("x", {labels:axisData, majorTick:{length:4}, minorTick:{length:4}});
	
		//chart.moveSeriesToFront();
		//chart.moveSeriesToBack();
		
		chart.render();
	});	
}

})