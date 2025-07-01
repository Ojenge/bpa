require([
"dojo/dom",
"dojo/dom-style",
"dojo/dom-construct",
"dojo/request",
"dijit/Dialog"
], function(dom, domStyle, domConstruct, request, Dialog){
	mainChart = function()
	{
		chart = new Highcharts.Chart({
		//chart = new Highstock.stockChart({
		chart: {
			renderTo: 'chartDiv'
			},
		title: {
			text: null
		},
		subtitle: {
			text: null
		},
		xAxis: {
			//type: 'datetime',
			min: 0, 
			max: 11
		},
		yAxis: {
			gridLineColor: 'transparent',
			min:0,
			max:10,
			endOnTick: false,
			startOnTick: false,
			//minPadding: 0,
			title: {
				text: null
			},//var chart = $('#container').highcharts(); chart.xAxis[0].removePlotBand('plotband-1');
			plotBands: [{
				color: 'red', // Color value
				from: 0, // Start of the plot band
				to: 3.33, // End of the plot band
				id: 'plotband-1'
			  },
			  {
				color: 'yellow', // Color value
				from: 3.33, // Start of the plot band
				to: 6.67, // End of the plot band
				id: 'plotband-2'
			  },
			  {
				color: 'green', // Color value
				from: 6.67, // Start of the plot band
				to: 10, // End of the plot band
				id: 'plotband-3'
			  }]
		},
		
		tooltip: {
			/*formatter: function() {
				var point = this.point,
					s = this.x +': <b>'+ this.y +'</b><br/>';
				return s;
			},*/
			crosshairs: true,
			shared: true
		},
		series: [
		{//0
			name: 'Score',
			type: 'spline',
			color: 'black',
			shadow: {
					color: 'white',
					width: 8,
					offsetX: 0,
					offsetY: 0
				},
			//lineWidth: 5,
			zIndex: 5
		},
		{//1 - using arearanges since plot bands do not allow for moving values (changing targets in this case)
			name: 'Red',
			type: 'arearange',
			pointPlacement: -0.5,
			color: 'red',
			lineWidth: 0,
			marker: {
			   enabled: false
			},
			enableMouseTracking: false,
			zIndex: 4
		},
		{//2
			name: 'Yellow',
			type: 'arearange',
			pointPlacement: -0.5,
			color: 'yellow',
			lineWidth: 0,
			marker: {
			   enabled: false
			},
			enableMouseTracking: false,
			zIndex: 3
		},
		{//3
			name: 'Green',
			type: 'arearange',
			pointPlacement: -0.5,
			color: 'green',
			lineWidth: 0,
			marker: {
			   enabled: false
			},
			enableMouseTracking: false,
			zIndex: 2
		},
		{//4
			name: 'Dark Green',
			type: 'arearange',
			pointPlacement: -0.5,
			color: 'darkgreen',
			lineWidth: 0,
			marker: {
			   enabled: false
			},
			enableMouseTracking: false,
			zIndex: 1
		},
		{//5
			name: 'centralLine',
			type: 'line',
			color: 'green',
			//fillOpacity: 0.3,
			zIndex: 1
		},
		{//6
			name: 'XmR',
			//data: ranges,
			type: 'arearange',
			lineWidth: 0,
			linkedTo: ':previous',
			color: '#00FF66',
			//color: Highcharts.getOptions().colors[0],
			//fillOpacity: 0.2,
			zIndex: 0
		},
		{//7
			name: 'Blue',
			type: 'arearange',
			color: 'Blue',
			lineWidth: 0,
			marker: {
			   enabled: false
			},
			enableMouseTracking: false,
			zIndex: 0
		}],
		exporting: {enabled: false},
		credits: {enabled: false},
		legend:{enabled: false},
		plotOptions: {
			spline: {
				dataLabels: {
					enabled: true,
					style: {
					color: 'black',
					textShadow: '0px 1px 2px white'
					}
				}
			}
		}
		});
	}//End of mainChart Function
	
	trendLine = function(kpiGlobalType, kpiGlobalId, kpiName, period, globalDate, dataTypeDisplay)
	{
		var chartNode = domConstruct.toDom("<div id='chartDiv'></div>");
		chartDialog = new Dialog({
			title: "Actual Performance Over Time",
			content: chartNode,
			style: "width: 60%",
			onHide: function() {
			  domConstruct.destroy("chartDiv");
		   }
		});
		chartDialog.show().then(function() 
		{
			chartDialog.resize();//Dialog was not centered on load. Adding this centered it!!! LTK 11 May 2021 1314 Hrs
			chartDialog.resize();
		});
		mainChart();
		updateChart(kpiGlobalType, kpiGlobalId, kpiName, period, globalDate, dataTypeDisplay);
		//updateChart("measure", "kpi153", "months", "2021-12", "standard");
		//currency = data['currency'];
	}//End of trendLine Function
	
	/*****************************************************************
	 Global function to update main scorecard chart across the system
	 *****************************************************************/
	updateChart = function(kpiGlobalType, kpiGlobalId, kpiName, period, globalDate, dataTypeDisplay)
	{
		var valuesCount = 12;
		switch(kpiGlobalType)
			{
				case "measure":
				{
					domStyle.set(dom.byId("chartDiv"), "display", 'block');	
					chart.setTitle({text: kpiName});
					/*Hide or Show the datalabels when valuesCount increases or decreases to avoid overlapping labels*/
					if(valuesCount > 12)
					{
						chart.series[0].update(
						{
							dataLabels:{
								enabled:false
							}
						},true);
					}
					else
					{
						chart.series[0].update(
						{
							dataLabels:{
								enabled:true
							}
						},true);
					}
					if(chartType == "XmR")
					{
						request.post("../scorecards/get-XmR-data.php",{
						handleAs: "json",
						data: {
							objectId: kpiGlobalId,
							objectDate: globalDate,
							objectType: kpiGlobalName,
							objectPeriod: period,
							valuesCount: valuesCount
						}
						}).then(function(XmRData)
						{
							var categories = [], kpiScore = [], scoreCount, range = [], centralLine = [], unpl = [], lnpl = [];
							scoreCount = XmRData.length-1;
							while(scoreCount >= 0)
							{
								if(XmRData[scoreCount].actual == null)
								{
									kpiScore[scoreCount] = null
								}
								else
								{
									categories[scoreCount] = XmRData[scoreCount].date
									kpiScore[scoreCount] = parseFloat(XmRData[scoreCount].actual);
									//kpiScore[scoreCount] = {name: XmRData[scoreCount].date, y: parseFloat(XmRData[scoreCount].actual,10) };
								}
								unpl[scoreCount] = XmRData[scoreCount].unpl;
								lnpl[scoreCount] = XmRData[scoreCount].lnpl;
								range[scoreCount] = [XmRData[scoreCount].lnpl, XmRData[scoreCount].unpl];
								centralLine[scoreCount] = [XmRData[scoreCount].date, XmRData[scoreCount].centralLine];
								//unpl[scoreCount] = XmRData[scoreCount].unpl;
								scoreCount--;
							}
							var yMaximum = Math.max.apply(null, unpl);
							var yMaximumTemp = Math.max.apply(null, kpiScore);
							if(yMaximumTemp > yMaximum) yMaximum = yMaximumTemp;
	
							var yMinimum = Math.min.apply(null, lnpl);
							var yMinimumTemp = Math.min.apply(null, kpiScore);
							if(yMinimumTemp < yMinimum) yMinimum = yMinimumTemp;
	
							var xMaximum = XmRData.length - 1.5;
							chart.yAxis[0].setExtremes(yMinimum,yMaximum);
							chart.xAxis[0].setExtremes(0.5,xMaximum);
							chart.yAxis[0].plotLinesAndBands[0].svgElem.hide();
							chart.yAxis[0].plotLinesAndBands[1].svgElem.hide();
							chart.yAxis[0].plotLinesAndBands[2].svgElem.hide();
							//chart.xAxis[0].setCategories(categories, false);
							chart.series[1].hide();//Red Line = lnpl
							chart.series[2].hide();//Yellow Line = centralLine
							chart.series[3].hide();//Green = unpl
							chart.series[4].hide();//Dark Green
							chart.series[5].show();//Central Line
							chart.series[6].show();//Range
							chart.series[7].hide();//Blue
							chart.yAxis[0].plotLinesAndBands[0].svgElem.hide();
							chart.yAxis[0].plotLinesAndBands[1].svgElem.hide();
							chart.yAxis[0].plotLinesAndBands[2].svgElem.hide();
	
							chart.series[5].update(
							{
								data: centralLine,
								name: 'central line',
							},false);
							chart.series[6].update({
								data: range,
								name: 'range',
							},false);
							chart.xAxis[0].setCategories(categories, false);
							chart.series[0].update(
							{
								tooltip:{
									//valueSuffix: '',
									//valueDecimals: 2
									crosshairs: true,
									shared: true,
									//useHTML: true,
									//headerFormat: '<small>{point.key}</small><table>',
									//pointFormat: '<tr><td style="color: {series.color};"><b>-></b></td><td>{series.name}:</td>' +
									//pointFormat: '<tr><td>{series.name}: &bull; (circular bullet) &raquo; (two greater thans) &rArr; (double arrow) &radic; (tick)</td>' +
									//'<td style="text-align: right"><b>{point.y}</b></td></tr>',
									//footerFormat: '</table>'
								},
								data: kpiScore,
								name: 'kpi'
							},true);
							//chart.series[0].setData(kpiScore,true);
	
							if(XmRData.length <= 3)
							chart.showNoData("A minimum of 4 points are needed to compute and display XmR Charts.<br>The XmR Chart will display when data captured reaches this number of data points.<br><br>Reference:  http://staceybarr.com/measure-up/three-things-you-need-on-every-kpi-graph/");
						});
					}
					else
					{
						//alert("kpi id: "+objectId+"kpi type: "+objectType+" period: "+period+" kpi date: "+globalDate);
						request.post("scorecards/get-kpi-scores.php",{
						handleAs: "json",
						data: {
							objectId: kpiGlobalId,
							objectType: kpiGlobalType,
							objectPeriod: period,
							objectDate: globalDate,
							valuesCount: valuesCount,
							previousPeriod: 'False'
						}
						}).then(function(kpiData)
						{
							//console.log(JSON.stringify(kpiData));
							var categories = [], kpiScore = [], kpiScoreLimit = [], scoreCount = 0, kpiRed = [], kpiGreen = [], kpiDarkGreen = [], kpiBlue = [], kpiYellow = [], kpiLimit = [], nullCounter = 0;
							lowerLimit = [];
							upperLimit = [];
							while(scoreCount < kpiData.length)
							{
								categories[scoreCount] = kpiData[scoreCount].date;
								if(kpiData[scoreCount].actual == null)
								{
									kpiScore[scoreCount] = null;
									kpiScoreLimit[scoreCount] = null;
									nullCounter++;
								}
								else
								{
									kpiScore[scoreCount] = parseFloat(kpiData[scoreCount].actual);
									kpiScoreLimit[scoreCount] = parseFloat(kpiData[scoreCount].actual);
								}
								switch(kpiData[0].gaugeType)
								{
								case 'goalOnly':
								{
									if(kpiData[scoreCount].green < 0)
									{
										if(kpiData[scoreCount].green*2 < greenLimit) greenLimit = kpiData[scoreCount].green*2;
	
										kpiRed[scoreCount] = [0, kpiData[scoreCount].green];
										lowerLimit[scoreCount] = kpiData[scoreCount].green * 2;
										kpiGreen[scoreCount] = [kpiData[scoreCount].green, lowerLimit[scoreCount]];
										//lines below add color backgrounds to the last point on chart
										kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, lowerLimit[kpiData.length-1]];
										kpiRed[kpiData.length] = [0, kpiData[kpiData.length-1].green];
									}
									else
									{
										if(kpiData[scoreCount].green*2 > greenLimit) greenLimit = kpiData[scoreCount].green*2;
	
										kpiRed[scoreCount] = [0, kpiData[scoreCount].green];
										lowerLimit[scoreCount] = [0, kpiData[scoreCount].green];
										upperLimit[scoreCount] = kpiData[scoreCount].green * 2;
										kpiGreen[scoreCount] = [kpiData[scoreCount].green, upperLimit[scoreCount]];
										//console.log("red = "+ kpiRed[scoreCount] + " upper = " + upperLimit[scoreCount] + " green = " + kpiGreen[scoreCount]);
										//lines below add color backgrounds to the last point on chart
										kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, upperLimit[kpiData.length-1]];
										kpiRed[kpiData.length] = [0, kpiData[kpiData.length-1].green];
									}
									break;
								}
								case 'threeColor':
								{
									if(kpiData[scoreCount].green < 0 && kpiData[scoreCount].red  > 0)
									{
										if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
										else redLimit = kpiData[scoreCount].red;
	
										if(kpiData[scoreCount].green < greenLimit) greenLimit = kpiData[scoreCount].green;
										else greenLimit = kpiData[scoreCount].green;
	
										lowerLimit[scoreCount] = Math.abs(kpiData[scoreCount].green - kpiData[scoreCount].red);
										lowerLimit[scoreCount] = kpiData[scoreCount].green - lowerLimit[scoreCount];
										kpiGreen[scoreCount] = [lowerLimit[scoreCount], kpiData[scoreCount].green];
										kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];
	
										upperLimit[scoreCount] = Math.abs(kpiData[scoreCount].green - kpiData[scoreCount].red);
										upperLimit[scoreCount] = kpiData[scoreCount].red + upperLimit[scoreCount];
										kpiRed[scoreCount] = [kpiData[scoreCount].red, upperLimit[scoreCount]];
										//lines below add color backgrounds to the last point on chart
										kpiGreen[kpiData.length] = [lowerLimit[kpiData.length-1], kpiData[kpiData.length-1].green];
										kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, upperLimit[kpiData.length-1]];
										kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];
	
									}
									else if(kpiData[scoreCount].red > kpiData[scoreCount].green)
									{
										//if(kpiData[scoreCount].green < 0 && kpiData[scoreCount].red  < 0)
										//{
											var layerSize = kpiData[scoreCount].red - kpiData[scoreCount].green;
											redLimit = kpiData[scoreCount].red;
											greenLimit = kpiData[scoreCount].green;
											
											upperLimit[scoreCount] = kpiData[scoreCount].red + layerSize;
											kpiRed[scoreCount] = [kpiData[scoreCount].red, upperLimit[scoreCount]];
											kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];
											lowerLimit[scoreCount] = kpiData[scoreCount].green - layerSize;
											
											kpiGreen[scoreCount] = [lowerLimit[scoreCount], kpiData[scoreCount].green];
											//lines below add color backgrounds to the last point on chart
											kpiGreen[kpiData.length] = [lowerLimit[kpiData.length-1], kpiData[kpiData.length-1].green];
											kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, upperLimit[kpiData.length-1]];
											kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];
											
											//console.log("UpperLimit = "+ upperLimit[scoreCount] + "; Red = " + kpiRed[scoreCount] + "; Yellow = " + kpiYellow[scoreCount] + "; Green = " + kpiGreen[scoreCount] + "; Actual = " + kpiData[scoreCount].actual + "; Lowerlimit = " + lowerLimit[scoreCount] + "; Layersize = " + layerSize + "; RedLimit = " + redLimit + "; GreenLimit = " + greenLimit);
	
										//}
									}
									else if(kpiData[scoreCount].green < 0 && kpiData[scoreCount].red  < 0 && kpiData[scoreCount].red < kpiData[scoreCount].green)
									{
										if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
										else redLimit = kpiData[scoreCount].red;
	
										if(kpiData[scoreCount].green < greenLimit) greenLimit = kpiData[scoreCount].green;
										else greenLimit = kpiData[scoreCount].green;
	
										lowerLimit[scoreCount] = kpiData[scoreCount].green + kpiData[scoreCount].red;
										lowerLimit[scoreCount] = lowerLimit[scoreCount] + kpiData[scoreCount].red;
										kpiRed[scoreCount] = [kpiData[scoreCount].red, lowerLimit[scoreCount]];
										kpiYellow[scoreCount] = [kpiData[scoreCount].green,kpiData[scoreCount].red];
	
										upperLimit[scoreCount] = kpiData[scoreCount].green + kpiData[scoreCount].red;
										upperLimit[scoreCount] = kpiData[scoreCount].green - upperLimit[scoreCount];
										kpiGreen[scoreCount] = [kpiData[scoreCount].green,upperLimit[scoreCount]];
										//lines below add color backgrounds to the last point on chart
										kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, upperLimit[kpiData.length-1]];
										kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, lowerLimit[kpiData.length-1]];
										kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].red];
									}
									else
									{
										if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
										else redLimit = kpiData[scoreCount].red;
	
										if(kpiData[scoreCount].green < greenLimit) greenLimit = kpiData[scoreCount].green;
										else greenLimit = kpiData[scoreCount].green;
										
										lowerLimit[scoreCount] = kpiData[scoreCount].green - kpiData[scoreCount].red;
										lowerLimit[scoreCount] = kpiData[scoreCount].red - lowerLimit[scoreCount];
										kpiRed[scoreCount] = [kpiData[scoreCount].red, lowerLimit[scoreCount]]
										kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];
	
										upperLimit[scoreCount] = kpiData[scoreCount].green - kpiData[scoreCount].red;
										upperLimit[scoreCount] = kpiData[scoreCount].green + upperLimit[scoreCount];
										kpiGreen[scoreCount] = [kpiData[scoreCount].green, upperLimit[scoreCount]];
										
										//lines below add color backgrounds to the last point on chart
										kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, upperLimit[kpiData.length-1]];
										kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, lowerLimit[kpiData.length-1]];
										kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];
										
										//console.log("Yellow: " + kpiYellow[scoreCount] + "; Green: " + kpiGreen[scoreCount] + "; Red: " + kpiRed[scoreCount]);
									}
									break;
								}
								case 'fourColor':
								{
									if(kpiData[scoreCount].green < 0 && kpiData[scoreCount].red  > 0)
									{
										if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
										else redLimit = kpiData[scoreCount].red;
	
										if(kpiData[scoreCount].darkgreen < greenLimit) greenLimit = kpiData[scoreCount].darkgreen;
										else greenLimit = kpiData[scoreCount].darkgreen;
	
										lowerLimit[scoreCount] = Math.abs(kpiData[scoreCount].darkgreen - kpiData[scoreCount].red);
										lowerLimit[scoreCount] = kpiData[scoreCount].darkgreen - lowerLimit[scoreCount];
										kpiGreen[scoreCount] = [kpiData[scoreCount].green, kpiData[scoreCount].darkgreen];
										kpiDarkGreen[scoreCount] = [lowerLimit[scoreCount], kpiData[scoreCount].darkgreen];
										kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];
	
										upperLimit[scoreCount] = Math.abs(kpiData[scoreCount].darkgreen - kpiData[scoreCount].red);
										upperLimit[scoreCount] = kpiData[scoreCount].red + upperLimit[scoreCount];
										kpiRed[scoreCount] = [kpiData[scoreCount].red, upperLimit[scoreCount]];
	
										//lines below add color backgrounds to the last point on chart
										kpiDarkGreen[kpiData.length] = [lowerLimit[kpiData.length-1], kpiData[kpiData.length-1].darkgreen];
										kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].darkgreen];
										kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, upperLimit[kpiData.length-1]];
										kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];
	
									}
									else if(kpiData[scoreCount].red > kpiData[scoreCount].green)
									{
										if(kpiData[scoreCount].red > redLimit) redLimit = kpiData[scoreCount].red;
										else redLimit = kpiData[scoreCount].red;
	
										if(kpiData[scoreCount].darkgreen < greenLimit) greenLimit = kpiData[scoreCount].darkgreen;
										else greenLimit = kpiData[scoreCount].darkgreen;
										
										upperLimit[scoreCount] = kpiData[scoreCount].red - kpiData[scoreCount].darkgreen;
										upperLimit[scoreCount] = kpiData[scoreCount].red + upperLimit[scoreCount];
										kpiRed[scoreCount] = [kpiData[scoreCount].red, upperLimit[scoreCount]];
										kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];
										kpiGreen[scoreCount] = [kpiData[scoreCount].green,kpiData[scoreCount].darkgreen];
										lowerLimit[scoreCount] = kpiData[scoreCount].darkgreen - kpiData[scoreCount].red;
										lowerLimit[scoreCount] = kpiData[scoreCount].darkgreen + lowerLimit[scoreCount];
	
										kpiDarkGreen[scoreCount] = [upperLimit[scoreCount], kpiData[scoreCount].darkgreen];
										
										//lines below add color backgrounds to the last point on chart
										kpiDarkGreen[kpiData.length] = [upperLimit[kpiData.length-1], kpiData[kpiData.length-1].darkgreen];
										kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].darkgreen];
										kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, upperLimit[kpiData.length-1]];
										kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];
									}
									else if(kpiData[scoreCount].green < 0 && kpiData[scoreCount].red  < 0 && kpiData[scoreCount].red < kpiData[scoreCount].green)
									{
										if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
										else redLimit = kpiData[scoreCount].red;
	
										if(kpiData[scoreCount].darkgreen < greenLimit) greenLimit = kpiData[scoreCount].darkgreen;
										else greenLimit = kpiData[scoreCount].darkgreen;
	
										lowerLimit[scoreCount] = kpiData[scoreCount].darkgreen + kpiData[scoreCount].red;
										lowerLimit[scoreCount] = lowerLimit[scoreCount] + kpiData[scoreCount].red;
										kpiRed[scoreCount] = [kpiData[scoreCount].red, lowerLimit[scoreCount]];
										kpiYellow[scoreCount] = [kpiData[scoreCount].green,kpiData[scoreCount].red];
										kpiGreen[scoreCount] = [kpiData[scoreCount].green,kpiData[scoreCount].darkgreen];
										upperLimit[scoreCount] = kpiData[scoreCount].darkgreen + kpiData[scoreCount].red;
										upperLimit[scoreCount] = kpiData[scoreCount].darkgreen - upperLimit[scoreCount];
										kpiDarkGreen[scoreCount] = [kpiData[scoreCount].darkgreen,upperLimit[scoreCount]];
	
										//lines below add color backgrounds to the last point on chart
										kpiDarkGreen[kpiData.length] = [kpiData[kpiData.length-1].darkgreen, upperLimit[kpiData.length-1]];
										kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].darkgreen];
										kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, lowerLimit[kpiData.length-1]];
										kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].red];
									}
									else
									{
										if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
										else redLimit = kpiData[scoreCount].red;
	
										if(kpiData[scoreCount].darkgreen < greenLimit) greenLimit = kpiData[scoreCount].darkgreen;
										else greenLimit = kpiData[scoreCount].darkgreen;
	
										lowerLimit[scoreCount] = kpiData[scoreCount].darkgreen - kpiData[scoreCount].red;
										lowerLimit[scoreCount] = kpiData[scoreCount].red - lowerLimit[scoreCount];
										kpiRed[scoreCount] = [kpiData[scoreCount].red, lowerLimit[scoreCount]]
										kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];
										kpiGreen[scoreCount] = [kpiData[scoreCount].green, kpiData[scoreCount].darkgreen];
										upperLimit[scoreCount] = kpiData[scoreCount].darkgreen - kpiData[scoreCount].red;
										upperLimit[scoreCount] = kpiData[scoreCount].darkgreen + upperLimit[scoreCount];
										kpiDarkGreen[scoreCount] = [kpiData[scoreCount].darkgreen, upperLimit[scoreCount]];
	
										//lines below add color backgrounds to the last point on chart
										kpiDarkGreen[kpiData.length] = [kpiData[kpiData.length-1].darkgreen, upperLimit[kpiData.length-1]];
										kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].darkgreen];
										kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, lowerLimit[kpiData.length-1]];
										kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];
									}
									break;
								}
								case 'fiveColor':
								{
									if(kpiData[scoreCount].green < 0 && kpiData[scoreCount].red  > 0)
									{
										if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
										else redLimit = kpiData[scoreCount].red;
	
										if(kpiData[scoreCount].blue < greenLimit) greenLimit = kpiData[scoreCount].blue;
										else greenLimit = kpiData[scoreCount].blue;
	
										lowerLimit[scoreCount] = Math.abs(kpiData[scoreCount].blue - kpiData[scoreCount].red);
										lowerLimit[scoreCount] = kpiData[scoreCount].blue - lowerLimit[scoreCount];
										kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];
										kpiGreen[scoreCount] = [kpiData[scoreCount].green, kpiData[scoreCount].darkgreen];
										kpiDarkGreen[scoreCount] = [kpiData[scoreCount].darkgreen, kpiData[scoreCount].blue];
										kpiBlue[scoreCount] = [kpiData[scoreCount].blue, lowerLimit[scoreCount]];
	
										upperLimit[scoreCount] = Math.abs(kpiData[scoreCount].blue - kpiData[scoreCount].red);
										upperLimit[scoreCount] = kpiData[scoreCount].red + upperLimit[scoreCount];
										kpiRed[scoreCount] = [kpiData[scoreCount].red, upperLimit[scoreCount]];
	
										//lines below add color backgrounds to the last point on chart
										kpiBlue[kpiData.length] = [kpiData[kpiData.length-1].blue, lowerLimit[kpiData.length-1]];
										kpiDarkGreen[kpiData.length] = [kpiData[kpiData.length-1].darkgreen, kpiData[kpiData.length-1].blue];
										kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].darkgreen];
										kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, upperLimit[kpiData.length-1]];
										kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];
	
									}
									else if(kpiData[scoreCount].red > kpiData[scoreCount].green)
									{
										if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
										else redLimit = kpiData[scoreCount].red;
	
										if(kpiData[scoreCount].green < greenLimit) greenLimit = kpiData[scoreCount].blue;
										else greenLimit = kpiData[scoreCount].blue;
	
										lowerLimit[scoreCount] = kpiData[scoreCount].red - kpiData[scoreCount].blue;
										lowerLimit[scoreCount] = kpiData[scoreCount].red + lowerLimit[scoreCount];
										kpiRed[scoreCount] = [kpiData[scoreCount].red, lowerLimit[scoreCount]];
	
										kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];
										kpiGreen[scoreCount] = [kpiData[scoreCount].green,kpiData[scoreCount].darkgreen];
										kpiDarkGreen[scoreCount] = [kpiData[scoreCount].darkgreen, kpiData[scoreCount].blue];
	
										upperLimit[scoreCount] = kpiData[scoreCount].blue - kpiData[scoreCount].red;
										upperLimit[scoreCount] = kpiData[scoreCount].blue + upperLimit[scoreCount];
										kpiBlue[scoreCount] = [kpiData[scoreCount].blue, upperLimit[scoreCount]];
	
										//lines below add color backgrounds to the last point on chart
										kpiBlue[kpiData.length] = [kpiData[kpiData.length-1].blue, upperLimit[kpiData.length-1]];
										kpiDarkGreen[kpiData.length] = [kpiData[kpiData.length-1].darkgreen, kpiData[kpiData.length-1].blue];
										kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].darkgreen];
										kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, lowerLimit[kpiData.length-1]];
										kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];
									}
									else if(kpiData[scoreCount].green < 0 && kpiData[scoreCount].red  < 0 && kpiData[scoreCount].red < kpiData[scoreCount].green)
									{
										if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
										else redLimit = kpiData[scoreCount].red;
	
										if(kpiData[scoreCount].green < greenLimit) greenLimit = kpiData[scoreCount].blue;
										else greenLimit = kpiData[scoreCount].blue;
	
										lowerLimit[scoreCount] = kpiData[scoreCount].blue + kpiData[scoreCount].red;
										lowerLimit[scoreCount] = lowerLimit[scoreCount] + kpiData[scoreCount].red;
										kpiRed[scoreCount] = [kpiData[scoreCount].red, lowerLimit[scoreCount]];
	
										kpiYellow[scoreCount] = [kpiData[scoreCount].green,kpiData[scoreCount].red];
										kpiGreen[scoreCount] = [kpiData[scoreCount].green,kpiData[scoreCount].darkgreen];
										kpiDarkGreen[scoreCount] = [kpiData[scoreCount].darkgreen,kpiData[scoreCount].blue];
	
										upperLimit[scoreCount] = kpiData[scoreCount].blue + kpiData[scoreCount].red;
										upperLimit[scoreCount] = kpiData[scoreCount].blue - upperLimit[scoreCount];
										kpiBlue[scoreCount] = [kpiData[scoreCount].blue, upperLimit[scoreCount]];
	
										//lines below add color backgrounds to the last point on chart
										kpiBlue[kpiData.length] = [kpiData[kpiData.length-1].blue, upperLimit[kpiData.length-1]];
										kpiDarkGreen[kpiData.length] = [kpiData[kpiData.length-1].darkgreen, kpiData[kpiData.length-1].blue];
										kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].darkgreen];
										kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, lowerLimit[kpiData.length-1]];
										kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].red];
									}
									else
									{
										if(kpiData[scoreCount].red < redLimit) redLimit = kpiData[scoreCount].red;
										else redLimit = kpiData[scoreCount].red;
	
										if(kpiData[scoreCount].green < greenLimit) greenLimit = kpiData[scoreCount].blue;
										else greenLimit = kpiData[scoreCount].blue;
	
										lowerLimit[scoreCount] = kpiData[scoreCount].blue - kpiData[scoreCount].red;
										lowerLimit[scoreCount] = kpiData[scoreCount].red - lowerLimit[scoreCount];
										kpiRed[scoreCount] = [kpiData[scoreCount].red, lowerLimit[scoreCount]]
	
										kpiYellow[scoreCount] = [kpiData[scoreCount].red, kpiData[scoreCount].green];
										kpiGreen[scoreCount] = [kpiData[scoreCount].green, kpiData[scoreCount].darkgreen];
										kpiDarkGreen[scoreCount] = [kpiData[scoreCount].darkgreen, kpiData[scoreCount].blue];
	
										upperLimit[scoreCount] = kpiData[scoreCount].blue - kpiData[scoreCount].red;
										upperLimit[scoreCount] = kpiData[scoreCount].blue + upperLimit[scoreCount];
										kpiBlue[scoreCount] = [kpiData[scoreCount].blue, upperLimit[scoreCount]];
	
										//lines below add color backgrounds to the last point on chart
										kpiBlue[kpiData.length] = [kpiData[kpiData.length-1].blue, upperLimit[kpiData.length-1]];
										kpiDarkGreen[kpiData.length] = [kpiData[kpiData.length-1].darkgreen, kpiData[kpiData.length-1].blue];
										kpiGreen[kpiData.length] = [kpiData[kpiData.length-1].green, kpiData[kpiData.length-1].darkgreen];
										kpiRed[kpiData.length] = [kpiData[kpiData.length-1].red, lowerLimit[kpiData.length-1]];
										kpiYellow[kpiData.length] = [kpiData[kpiData.length-1].red, kpiData[kpiData.length-1].green];
									}
									break;
								}
								}//end of color switch
								categories[scoreCount] = kpiData[scoreCount].date;
								scoreCount++;
							}
							//chart.series[0].data.length = 0;
							chart.yAxis[0].plotLinesAndBands[0].svgElem.hide();
							chart.yAxis[0].plotLinesAndBands[1].svgElem.hide();
							chart.yAxis[0].plotLinesAndBands[2].svgElem.hide();
							chart.xAxis[0].setCategories(categories, false);
							if(nullCounter == kpiData.length) chart.showNoData("No Measure Data to Display");
							//console.log('nullCounter ' + nullCounter + ', length ' + kpiData.length);
							if(dataTypeDisplay == 'Percentage')
							{
								chart.series[0].update({
								tooltip:{
									valueSuffix: ' %',
									//valueDecimals: 2
									crosshairs: true,
									shared: true
								},
								data: kpiScore,
								name: kpiGlobalName
								},true);
							}
							else if(dataTypeDisplay == 'Currency')
							{
								chart.series[0].update({
								tooltip:{
									valuePrefix: currency+' ',
									valueDecimals: 2,
									headerFormat: 'Period: {point.key}<br>',
									pointFormat: "Measure: {series.name}<br>Value: <b>{point.y}</b><br/>",
									crosshairs: true,
									shared: true
								},
								data: kpiScore,
								name: kpiGlobalName
								},true);
							}
							else
							{
								chart.series[0].update({
								tooltip:{
									/*useHTML: true,
									headerFormat: '<small>{point.key}</small><table>',
									pointFormat: '<tr><td style="color: {series.color}">{series.name}: </td>' +
										'<td style="text-align: right"><b>{point.y} EUR</b></td></tr>',
									footerFormat: '</table>'*/
									//valuePrefix: currency+' ',
									//valueDecimals: 2,
									headerFormat: 'Period: {point.key}<br>',
									pointFormat: "Measure: {series.name}<br>Value: <b>{point.y}</b><br/>",
									crosshairs: true,
									shared: true
								},
								data: kpiScore,
								name: kpiGlobalName,
								shared: true
								},true);
							}
							switch(kpiData[0].gaugeType)
							{
								case 'goalOnly':
								{
									if(greenLimit < 0)
									{
										chart.yAxis[0].setExtremes(greenLimit, 0);
									}
									else
									{
										chart.yAxis[0].setExtremes(0, greenLimit);
									}
									/****************************************************************************************************
										Check if actual values should form the upper or lower limits respectively
										*/
										//var sasa = Math.min.apply(Math, kpiScoreLimit)
										//var poa = Math.min.apply(null, lowerLimit)
										//console.log ("kpiScoreLimit = " + sasa + " lowerLimit = " + poa);
										if(Math.min.apply(Math, kpiScoreLimit) < Math.min.apply(null, lowerLimit))
										{
											chart.yAxis[0].setExtremes(Math.min.apply(Math, kpiScoreLimit), Math.max.apply(null, upperLimit));
											var size = kpiRed.length;
											for (var i = 0; i < size; i++)
											{
												//added lines above to  add color backgrounds to the last point on chart hence need to reflect added values below for all combinations
												//kpiRed[i] = [parseFloat(kpiData[0].red), Math.min.apply(Math, kpiScoreLimit)];
												kpiRed[i] = [parseFloat(kpiRed[i]), Math.min.apply(Math, kpiScoreLimit)];
											}
										}
										if(Math.max.apply(Math, kpiScoreLimit) > Math.max.apply(null, upperLimit))
										{
											chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(Math, kpiScoreLimit));
											var size = kpiRed.length;
											for (var i = 0; i < size; i++)
											{
												//kpiGreen[i] = [parseFloat(kpiData[0].green), Math.max.apply(Math, kpiScoreLimit)];
												kpiGreen[i] = [parseFloat(kpiGreen[i]), Math.max.apply(Math, kpiScoreLimit)];
											}
										}
										/******************************************************************************************************/
	
									chart.series[1].show();//Red Line
									chart.series[2].hide();//Yellow Line
									chart.series[3].show();//Green Line
									chart.series[4].hide();//Dark Green Line
									chart.series[5].hide();//centralLine
									chart.series[6].hide();//XmR ranges
									chart.series[7].hide();//Blue
									chart.xAxis[0].setExtremes(0,valuesCount-1);
									chart.series[1].update({
										data: kpiRed,
										name: 'red'
										},false);
									chart.series[3].update({
										data: kpiGreen,
										name: 'green'
										},true);
									redLimit = 0; greenLimit = 0;
									break;
								}
								case 'threeColor':
								{
									//console.log('Lower Limit ' + lowerLimit + ' Upper Limit: '+ upperLimit);
									if(greenLimit < 0 && redLimit > 0)
									{
										chart.series[1].show();//Red Line
										chart.series[2].show();//Yellow Line
										chart.series[3].show();//Green
										chart.series[4].hide();//Dark Green
										chart.series[5].hide();//centralLine
										chart.series[6].hide();//XmR ranges
										chart.series[7].hide();//Blue
										chart.xAxis[0].setExtremes(0,valuesCount-1);
										chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(null, upperLimit));
										/****************************************************************************************************
										Check if actual values should form the upper or lower limits respectively
										*/
										if(Math.min.apply(Math, kpiScoreLimit) < Math.min.apply(null, lowerLimit))
										{
											chart.yAxis[0].setExtremes(Math.min.apply(Math, kpiScoreLimit), Math.max.apply(null, upperLimit));
											var size = kpiRed.length;
											for (var i = 0; i < size; i++)
											{
												//kpiRed[i] = [parseFloat(kpiRed[0].red), Math.min.apply(Math, kpiScoreLimit)];
												kpiRed[i] = [parseFloat(kpiRed[i].red), Math.min.apply(Math, kpiScoreLimit)];
											}
										}
										if(Math.max.apply(Math, kpiScoreLimit) > Math.max.apply(null, upperLimit))
										{
											chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(Math, kpiScoreLimit));
											var size = kpiRed.length;
											for (var i = 0; i < size; i++)
											{
												//kpiGreen[i] = [parseFloat(kpiData[0].green), Math.max.apply(Math, kpiScoreLimit)];
												kpiGreen[i] = [parseFloat(kpiGreen[i]), Math.max.apply(Math, kpiScoreLimit)];
											}
										}
										/******************************************************************************************************/
	
										chart.series[1].update({
											data: kpiRed,
											name: 'red'
											},false);
										chart.series[2].update({
											data: kpiYellow,
											name: 'yellow'
											},false);
										chart.series[3].update({
											data: kpiGreen,
											name: 'green'
											},true);
									}
									else
									{
										var moja = Math.min.apply(Math, kpiScoreLimit);
										var mbili = Math.min.apply(null, lowerLimit);
										var tatu = Math.max.apply(null, upperLimit);
										//console.log ("kpiScoreLimit = " + moja + "lowerLimit = " + mbili + " upperLimit = " + tatu + " redLimit = " + redLimit + " greenLimit = " + greenLimit);
										
										if(redLimit > greenLimit)
										{
											chart.yAxis[0].setExtremes(Math.max.apply(null, lowerLimit), Math.min.apply(null, upperLimit));
											
											if(Math.min.apply(Math, kpiScoreLimit) < Math.min.apply(null, lowerLimit) && Math.max.apply(Math, kpiScoreLimit) > Math.max.apply(null, upperLimit))
											{
												chart.yAxis[0].setExtremes(Math.min.apply(Math, kpiScoreLimit), Math.max.apply(null, kpiScoreLimit));
												var size = kpiRed.length;
												for(var i = 0; i < size; i++)
												{
													kpiRed[i] = [parseFloat(kpiData[0].red), Math.max.apply(Math, kpiScoreLimit)];
													kpiGreen[i] = [parseFloat(kpiData[0].green), Math.min.apply(Math, kpiScoreLimit)];
												}
											}
											else if(Math.min.apply(Math, kpiScoreLimit) < Math.min.apply(null, lowerLimit))
											{
												chart.yAxis[0].setExtremes(Math.min.apply(Math, kpiScoreLimit), Math.max.apply(null, upperLimit));
												var size = kpiGreen.length;
												for(var i = 0; i < size; i++)
												{
													kpiGreen[i] = [parseFloat(kpiData[0].green), Math.min.apply(Math, kpiScoreLimit)];
												}
											}
											else if(Math.max.apply(Math, kpiScoreLimit) > Math.max.apply(null, upperLimit))
											{
												chart.yAxis[0].setExtremes(Math.min.apply(Math, lowerLimit), Math.max.apply(null, kpiScoreLimit));
												var size = kpiRed.length;
												for(var i = 0; i < size; i++)
												{
													kpiRed[i] = [parseFloat(kpiData[0].red), Math.max.apply(Math, kpiScoreLimit)];
												}
											}
										}
										else
										{
											chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(null, upperLimit));
											/****************************************************************************************************
											Check if actual values should form the upper or lower limits respectively
											*/
											if(Math.min.apply(Math, kpiScoreLimit) < Math.min.apply(null, lowerLimit))
											{
												chart.yAxis[0].setExtremes(Math.min.apply(Math, kpiScoreLimit), Math.max.apply(null, upperLimit));
												var size = kpiRed.length;
												for (var i = 0; i < size; i++)
												{
													//kpiRed[i] = [parseFloat(kpiData[0].red), Math.min.apply(Math, kpiScoreLimit)];
													kpiRed[i] = [parseFloat(kpiRed[i]), Math.min.apply(Math, kpiScoreLimit)];
												}
											}
											if(Math.max.apply(Math, kpiScoreLimit) > Math.max.apply(null, upperLimit))
											{
												chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(Math, kpiScoreLimit));
												var size = kpiRed.length;
												for (var i = 0; i < size; i++)
												{
													//kpiGreen[i] = [parseFloat(kpiData[0].green), Math.max.apply(Math, kpiScoreLimit)];
													kpiGreen[i] = [parseFloat(kpiGreen[i]), Math.max.apply(Math, kpiScoreLimit)];
												}
											}
										}
										/******************************************************************************************************/
					//console.log("Red " + json.stringify(kpiRed));
					//console.log("Yellow " + json.stringify(kpiYellow));
					//console.log("Green " + json.stringify(kpiGreen));
										chart.series[1].show();//Red Line
										chart.series[2].show();//Yellow Line
										chart.series[3].show();//Green
										chart.series[4].hide();//Dark Green
										chart.series[5].hide();//centralLine
										chart.series[6].hide();//XmR ranges
										chart.series[7].hide();//Blue
										chart.xAxis[0].setExtremes(0,valuesCount-1);
										chart.series[1].update({
											data: kpiRed,
											name: 'red',
											//threshold: -10
											},false);
										chart.series[2].update({
											data: kpiYellow,
											name: 'yellow',
											//threshold: -3
											},false);
										chart.series[3].update({
											data: kpiGreen,
											name: 'green'
											},true);
									}
									redLimit = 0; greenLimit = 0;
									break;
								}
								case 'fourColor':
								{
									//console.log("redLimit = " + redLimit + " greenLimit = " + greenLimit);
									if(greenLimit < 0 && redLimit > 0)
									{
										var yMaximum = Math.max.apply(null, upperLimit);
										var yMaximumTemp = Math.max.apply(null, kpiScore);
										if(yMaximumTemp > yMaximum) yMaximum = yMaximumTemp;
	
										var yMinimum = Math.min.apply(null, lowerLimit);
										var yMinimumTemp = Math.min.apply(null, kpiScore);
										if(yMinimumTemp < yMinimum) yMinimum = yMinimumTemp;
	
										chart.yAxis[0].setExtremes(yMinimum, yMaximum);
										var size = kpiRed.length;
										for (var i = 0; i < size; i++)
										{
											//if(kpiRed[i] == null) kpiRed[i] = [null, null];
											kpiRed[i] = [parseFloat(kpiData[i].red), yMaximum];
										}
										var size = kpiRed.length;
										for (var i = 0; i < size; i++)
										{
											//if(kpiDarkGreen[i] == null) kpiDarkGreen[i] = [null, null];
											kpiDarkGreen[i] = [parseFloat(kpiData[i].darkgreen), yMinimum];
										}
	
										chart.series[1].show();//Red Line
										chart.series[2].show();//Yellow Line
										chart.series[3].show();//Green
										chart.series[4].show();//Dark Green
										chart.series[5].hide();//centralLine
										chart.series[6].hide();//XmR ranges
										chart.series[7].hide();//Blue
										chart.xAxis[0].setExtremes(0,valuesCount-1);
										chart.series[1].update({
											data: kpiRed,
											name: 'red'
											},false);
										chart.series[2].update({
											data: kpiYellow,
											name: 'yellow'
											},false);
										chart.series[3].update({
											data: kpiGreen,
											name: 'green'
											},false);
										chart.series[4].update({
											data: kpiDarkGreen,
											name: 'darkGreen'
											},true);
									}
									else
									{
										if(redLimit > greenLimit)
										{
											var yMaximum = Math.max.apply(null, upperLimit);
											var yMaximumTemp = Math.max.apply(null, kpiScore);
											if(yMaximumTemp > yMaximum) yMaximum = yMaximumTemp;
	
											var yMinimum = Math.min.apply(null, lowerLimit);
											var yMinimumTemp = Math.min.apply(null, kpiScore);
											if(yMinimumTemp < yMinimum) yMinimum = yMinimumTemp;
											
											console.log("yMinimum = " + yMinimum + " yMaximum = " + yMaximum);
	
											chart.yAxis[0].setExtremes(yMinimum, yMaximum);
											var size = kpiRed.length;
											for (var i = 0; i < size; i++)
											{
												//kpiRed[i] = [parseFloat(kpiData[i].red), yMaximum];
												kpiRed[i] = [parseFloat(kpiRed[i]), yMaximum];
											}
											var size = kpiRed.length;
											for (var i = 0; i < size; i++)
											{
												//kpiDarkGreen[i] = [parseFloat(kpiData[i].darkgreen), yMinimum];
												kpiDarkGreen[i] = [parseFloat(kpiDarkGreen[i]), yMinimum];
											}
										}
										else
										{
											var yMaximum = Math.max.apply(null, upperLimit);
											var yMaximumTemp = Math.max.apply(null, kpiScore);
											if(yMaximumTemp > yMaximum) yMaximum = yMaximumTemp;
	
											var yMinimum = Math.min.apply(null, lowerLimit);
											var yMinimumTemp = Math.min.apply(null, kpiScore);
											if(yMinimumTemp < yMinimum) yMinimum = yMinimumTemp;
	
											chart.yAxis[0].setExtremes(yMinimum, yMaximum);
											var size = kpiRed.length;
											for (var i = 0; i < size; i++)
											{
												//kpiRed[i] = [parseFloat(kpiData[i].red), yMinimum];
												kpiRed[i] = [parseFloat(kpiRed[i]), yMinimum];
											}
											var size = kpiRed.length;
											for (var i = 0; i < size; i++)
											{
												//kpiDarkGreen[i] = [parseFloat(kpiData[i].darkgreen), yMaximum];
												kpiDarkGreen[i] = [parseFloat(kpiDarkGreen[i]), yMaximum];
											}
										}
	
										chart.series[1].show();//Red Line
										chart.series[2].show();//Yellow Line
										chart.series[3].show();//Green
										chart.series[4].show();//Dark Green
										chart.series[5].hide();//centralLine
										chart.series[6].hide();//XmR ranges
										chart.series[7].hide();//Blue
										chart.xAxis[0].setExtremes(0,valuesCount-1);
										chart.series[1].update({
											data: kpiRed,
											name: 'red',
											//threshold: -10
											},false);
										chart.series[2].update({
											data: kpiYellow,
											name: 'yellow',
											//threshold: -3
											},false);
										chart.series[3].update({
											data: kpiGreen,
											name: 'green'
											},false);
										chart.series[4].update({
											data: kpiDarkGreen,
											name: 'darkGreen'
											},true);
									}
									//console.log(JSON.stringify(kpiRed));
									redLimit = 0; greenLimit = 0;
									break;
								}
								case 'fiveColor':
								{
									if(greenLimit < 0 && redLimit > 0)
									{
										chart.series[1].show();//Red Line
										chart.series[2].show();//Yellow Line
										chart.series[3].show();//Green
										chart.series[4].show();//Dark Green
										chart.series[5].hide();//centralLine
										chart.series[6].hide();//XmR ranges
										chart.series[7].show();//Blue
										chart.xAxis[0].setExtremes(0,valuesCount-1);
										chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(null, upperLimit));
										/****************************************************************************************************
										Check if actual values should form the upper or lower limits respectively
										*/
										if(Math.min.apply(Math, kpiScoreLimit) < Math.min.apply(null, lowerLimit))
										{
											chart.yAxis[0].setExtremes(Math.min.apply(Math, kpiScoreLimit), Math.max.apply(null, upperLimit));
											var size = kpiRed.length;
											for (var i = 0; i < size; i++)
											{
												//kpiRed[i] = [parseFloat(kpiData[0].red), Math.min.apply(Math, kpiScoreLimit)];
												kpiRed[i] = [parseFloat(kpiRed[i]), Math.min.apply(Math, kpiScoreLimit)];
											}
										}
										if(Math.max.apply(Math, kpiScoreLimit) > Math.max.apply(null, upperLimit))
										{
											chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(Math, kpiScoreLimit));
											var size = kpiRed.length;
											for (var i = 0; i < size; i++)
											{
												//kpiBlue[i] = [parseFloat(kpiData[0].blue), Math.max.apply(Math, kpiScoreLimit)];
												kpiBlue[i] = [parseFloat(kpiBlue[i]), Math.max.apply(Math, kpiScoreLimit)];
											}
										}
										/******************************************************************************************************/
	
										chart.series[1].update({
											data: kpiRed,
											name: 'red'
											},false);
										chart.series[2].update({
											data: kpiYellow,
											name: 'yellow'
											},false);
										chart.series[3].update({
											data: kpiGreen,
											name: 'green'
											},false);
										chart.series[4].update({
											data: kpiDarkGreen,
											name: 'darkGreen'
											},false);
										chart.series[7].update({
											data: kpiBlue,
											name: 'blue'
											},true);
									}
									else
									{
										if(redLimit > greenLimit)
										chart.yAxis[0].setExtremes(Math.max.apply(null, upperLimit), Math.min.apply(null, lowerLimit));
										else
										chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(null, upperLimit));
										/****************************************************************************************************
										Check if actual values should form the upper or lower limits respectively
										*/
										if(Math.min.apply(Math, kpiScoreLimit) < Math.min.apply(null, lowerLimit))
										{
											chart.yAxis[0].setExtremes(Math.min.apply(Math, kpiScoreLimit), Math.max.apply(null, upperLimit));
											var size = kpiRed.length;
											for (var i = 0; i < size; i++)
											{
												//kpiRed[i] = [parseFloat(kpiData[0].red), Math.min.apply(Math, kpiScoreLimit)];
												kpiRed[i] = [parseFloat(kpiRed[i]), Math.min.apply(Math, kpiScoreLimit)];
											}
										}
										if(Math.max.apply(Math, kpiScoreLimit) > Math.max.apply(null, upperLimit))
										{
											chart.yAxis[0].setExtremes(Math.min.apply(null, lowerLimit), Math.max.apply(Math, kpiScoreLimit));
											var size = kpiRed.length;
											for (var i = 0; i < size; i++)
											{
												//kpiBlue[i] = [parseFloat(kpiData[0].blue), Math.max.apply(Math, kpiScoreLimit)];
												kpiBlue[i] = [parseFloat(kpiBlue[i]), Math.max.apply(Math, kpiScoreLimit)];
											}
										}
										/******************************************************************************************************/
	
										chart.series[1].show();//Red Line
										chart.series[2].show();//Yellow Line
										chart.series[3].show();//Green
										chart.series[4].show();//Dark Green
										chart.series[5].hide();//centralLine
										chart.series[6].hide();//XmR ranges
										chart.series[7].show();//Blue
										chart.xAxis[0].setExtremes(0,valuesCount-1);
										chart.series[1].update({
											data: kpiRed,
											name: 'red',
											//threshold: -10
											},false);
										chart.series[2].update({
											data: kpiYellow,
											name: 'yellow',
											//threshold: -3
											},false);
										chart.series[3].update({
											data: kpiGreen,
											name: 'green'
											},false);
										chart.series[4].update({
											data: kpiDarkGreen,
											name: 'darkGreen'
											},false);
										chart.series[7].update({
											data: kpiBlue,
											name: 'blue'
											},true);
									}
									redLimit = 0; greenLimit = 0;
									break;
								}
							}
							chart.redraw();
						});
					}//end of 9Steps Chart type
					break;
				}
				case "objective":
				{
						domStyle.set(dom.byId("chartDiv"), "display", 'block');
						domStyle.set(dom.byId("divChart"), "display", "block");
						//togglerMeasures.show();
						request.post("scorecards/get-obj-scores.php",{
						handleAs: "json",
						data: {
							objectId: kpiGlobalId,
							objectType: kpiGlobalType,
							objectPeriod: period,
							objectDate: globalDate,
							valuesCount: valuesCount
						}
						}).then(function(objectiveData)
						{
							//console.log(JSON.stringify(objectiveData));
							var categories = [], objectiveScore = [], scoreCount = 0;
							while(scoreCount < objectiveData.length)
							{
								categories[scoreCount] = objectiveData[scoreCount].date
								//objectiveScore[scoreCount] = {name: objectiveData[scoreCount].date, y: parseFloat(objectiveData[scoreCount].score,10) };
								if(objectiveData[scoreCount].score == null) objectiveScore[scoreCount] = null
								else
								objectiveScore[scoreCount] = parseFloat(objectiveData[scoreCount].score);
								//categories[scoreCount] = objectiveData[scoreCount].date;
								scoreCount++;
							}
							chart.yAxis[0].plotLinesAndBands[0].svgElem.show();
							chart.yAxis[0].plotLinesAndBands[1].svgElem.show();
							chart.yAxis[0].plotLinesAndBands[2].svgElem.show();
							chart.xAxis[0].setCategories(categories, false);
							chart.yAxis[0].setExtremes(0,10);
							chart.xAxis[0].setExtremes(0,valuesCount-1);
							chart.series[1].hide();
							chart.series[2].hide();
							chart.series[3].hide();
							chart.series[4].hide();
							chart.series[5].hide();
							chart.series[0].setData(objectiveScore, true);
						});
						request.post("../scorecards/get-obj-gauge.php",
						 {
							handleAs: "json",
							data: {
								objectId: kpiGlobalId,
								objectType: kpiGlobalType,
								objectPeriod: period,
								objectDate: globalDate
							}
							}).then(function(objGauge)
							{
								//console.log('Objective score: '+objGauge);
								domStyle.set(dom.byId("divGauge"), "display", "block");
								gauge.yAxis[0].removePlotBand('red');
								gauge.yAxis[0].removePlotBand('yellow');
								gauge.yAxis[0].removePlotBand('green');
								gauge.yAxis[0].removePlotBand('blue');
								gauge.yAxis[0].removePlotBand('darkGreen');
								gauge.yAxis[0].addPlotBand({
									color: '#ff0000',//red
									from: 0,
									to: 3.33,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'red'
								});
								gauge.yAxis[0].addPlotBand({
									color: '#FFD900',//yellow
									from: 3.33,
									to: 6.67,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'yellow'
								});
								gauge.yAxis[0].addPlotBand({
									color: '#33CC00',//green
									from: 6.67,
									to: 10,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'green'
								});
								if(objGauge == 'No Score')
								{
									//console.log('Score: ' + objGauge)
									gauge.series[0].points[0].update(null);
									gauge.series[0].options.dial.radius = 0;
									gauge.series[0].isDirty = true;
									gauge.redraw();
								}
								else
								{
									//console.log('Score: ' + objGauge)
									gauge.series[0].options.dial.radius = '100%';
									gauge.series[0].isDirty = true;
									gauge.redraw();
									var score = parseFloat(objGauge);
									score = Math.round(score * 100) / 100;
									gauge.series[0].points[0].update(score);
								}
							});
					break;
				}
				case "perspective":
				{
					domStyle.set(dom.byId("chartDiv"), "display", 'block');
					domStyle.set(dom.byId("divChart"), "display", "block");
					request.post("scorecards/get-persp-scores.php",{
					handleAs: "json",
					data: {
						objectId: kpiGlobalId,
						objectType: kpiGlobalType,
						objectPeriod: period,
						objectDate: globalDate,
						valuesCount: valuesCount
					}
					}).then(function(perspectiveData)
						{
							var categories = [], perspScore = [], scoreCount = 0;
							while(scoreCount < perspectiveData.length)
							{
								categories[scoreCount] = perspectiveData[scoreCount].date
								//objectiveScore[scoreCount] = {name: objectiveData[scoreCount].date, y: parseFloat(objectiveData[scoreCount].score,10) };
								if(perspectiveData[scoreCount].score == null)
								perspScore[scoreCount] = null
								else
								perspScore[scoreCount] = parseFloat(perspectiveData[scoreCount].score);
								//categories[scoreCount] = perspectiveData[scoreCount].date;
								scoreCount++;
							}
							//chart.series[0].data.length = 0;
							chart.yAxis[0].plotLinesAndBands[0].svgElem.show();
							chart.yAxis[0].plotLinesAndBands[1].svgElem.show();
							chart.yAxis[0].plotLinesAndBands[2].svgElem.show();
							chart.xAxis[0].setCategories(categories, false);
							chart.series[1].hide();
							chart.series[2].hide();
							chart.series[3].hide();
							chart.series[4].hide();
							chart.series[5].hide();
							chart.yAxis[0].setExtremes(0,10);
							chart.xAxis[0].setExtremes(0,valuesCount-1);
							chart.series[0].setData(perspScore, true);
					});
					request.post("../scorecards/get-persp-gauge.php",
					{
						handleAs: "json",
						data: {
							objectId: kpiGlobalId,
							objectType: kpiGlobalType,
							objectPeriod: period,
							objectDate: globalDate
					}
					}).then(function(perspGauge)
						{
							//console.log('Perspective score: '+perspGauge);
							domStyle.set(dom.byId("divGauge"), "display", "block");
							gauge.yAxis[0].removePlotBand('red');
							gauge.yAxis[0].removePlotBand('yellow');
							gauge.yAxis[0].removePlotBand('green');
							gauge.yAxis[0].removePlotBand('blue');
							gauge.yAxis[0].removePlotBand('darkGreen');
							gauge.yAxis[0].addPlotBand({
								color: '#ff0000',//red
								from: 0,
								to: 3.33,
								outerRadius: '100%',
								innerRadius: '1%',
								zIndex:-100,
								id: 'red'
							});
							gauge.yAxis[0].addPlotBand({
								color: '#FFD900',//yellow
								from: 3.33,
								to: 6.67,
								outerRadius: '100%',
								innerRadius: '1%',
								zIndex:-100,
								id: 'yellow'
							});
							gauge.yAxis[0].addPlotBand({
								color: '#33CC00',//green
								from: 6.67,
								to: 10,
								outerRadius: '100%',
								innerRadius: '1%',
								zIndex:-100,
								id: 'green'
							});
							if(perspGauge == 'No Score')
							{
								//console.log(', Score: ' + perspGauge)
								gauge.series[0].points[0].update(null);
								gauge.series[0].options.dial.radius = 0;
								gauge.series[0].isDirty = true;
								gauge.redraw();
							}
							else
							{
								//console.log('Score: ' + perspGauge)
								gauge.series[0].options.dial.radius = '100%';
								gauge.series[0].isDirty = true;
								gauge.redraw();
								var score = parseFloat(perspGauge);
								score = Math.round(score * 100) / 100;
								gauge.series[0].points[0].update(score);
							}
					});
					break;
				}
				case "organization":
				{
					domStyle.set(dom.byId("chartDiv"), "display", 'block');
					domStyle.set(dom.byId("divChart"), "display", "block");
					//alert("Id:" + kpiGlobalId + ", type: " + kpiGlobalType + ", period: " + period + ", date:" + globalDate);
					request.post("../scorecards/get-org-scores.php",{
					handleAs: "json",
					data: {
						objectId: kpiGlobalId,
						objectType: kpiGlobalType,
						objectPeriod: period,
						objectDate: globalDate,
						valuesCount: valuesCount
					}
					}).then(function(orgData)
						{	//alert(json.stringify(orgData));
							//dijit.byId("interpretation").set("value", "organization");
	
							var categories = [], orgScore = [], scoreCount = 0;
							while(scoreCount < orgData.length)
							{
								categories[scoreCount] = orgData[scoreCount].date
								if(orgData[scoreCount].score == null)
								orgScore[scoreCount] = null
								else
								orgScore[scoreCount] = parseFloat(orgData[scoreCount].score);
								//categories[scoreCount] = orgData[scoreCount].date;
								scoreCount++;
							}
							chart.yAxis[0].plotLinesAndBands[0].svgElem.show();
							chart.yAxis[0].plotLinesAndBands[1].svgElem.show();
							chart.yAxis[0].plotLinesAndBands[2].svgElem.show();
							chart.xAxis[0].setCategories(categories, false);
							chart.series[1].hide();
							chart.series[2].hide();
							chart.series[3].hide();
							chart.series[4].hide();
							chart.series[5].hide();
							chart.yAxis[0].setExtremes(0,10);
							chart.xAxis[0].setExtremes(0,valuesCount-1);
							chart.series[0].setData(orgScore, true);
	
						});
						request.post("../scorecards/get-org-gauge.php",
						{
							handleAs: "json",
							data: {
								objectId: kpiGlobalId,
								objectType: kpiGlobalType,
								objectPeriod: period,
								objectDate: globalDate
						}
						}).then(function(orgGauge)
							{
								//console.log('Organization score: '+orgGauge);
								domStyle.set(dom.byId("divGauge"), "display", "block");
								gauge.yAxis[0].removePlotBand('red');
								gauge.yAxis[0].removePlotBand('yellow');
								gauge.yAxis[0].removePlotBand('green');
								gauge.yAxis[0].removePlotBand('blue');
								gauge.yAxis[0].removePlotBand('darkGreen');
								gauge.yAxis[0].addPlotBand({
									color: '#ff0000',//red
									from: 0,
									to: 3.33,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'red'
								});
								gauge.yAxis[0].addPlotBand({
									color: '#FFD900',//yellow
									from: 3.33,
									to: 6.67,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'yellow'
								});
								gauge.yAxis[0].addPlotBand({
									color: '#33CC00',//green
									from: 6.67,
									to: 10,
									outerRadius: '100%',
									innerRadius: '1%',
									zIndex:-100,
									id: 'green'
								});
								if(orgGauge == '' || orgGauge == 'No Score')
								{
									//console.log(', Score: ' + orgGauge)
									gauge.series[0].points[0].update(null);
									gauge.series[0].options.dial.radius = 0;
									gauge.series[0].isDirty = true;
									gauge.redraw();
								}
								else
								{
									//console.log('Score: ' + orgGauge)
									gauge.series[0].options.dial.radius = '100%';
									gauge.series[0].isDirty = true;
									gauge.redraw();
									var score = parseFloat(orgGauge);
									score = Math.round(score * 100) / 100;
									gauge.series[0].points[0].update(score);
								}
							});
					break;
				}
				case "individual":
				{
					request.post("scorecards/get-ind-scores.php",{
					handleAs: "json",
					data: {
						objectId: kpiGlobalId,
						objectType: kpiGlobalType,
						objectPeriod: period,
						objectDate: globalDate,
						valuesCount: valuesCount
					}
					}).then(function(indData)
					{
							var categories = [], indScore = [], scoreCount = 0, nullCount = 0;
							while(scoreCount < indData.length)
							{
								categories[scoreCount] = indData[scoreCount].date
								if(indData[scoreCount].score == null)
								{
									indScore[scoreCount] = null;
									nullCount++;
								}
								else
								indScore[scoreCount] = parseFloat(indData[scoreCount].score);
								categories[scoreCount] = indData[scoreCount].date;
								scoreCount++;
							}
							if(nullCount == indData.length)
							{
								domStyle.set(dom.byId("divChart"), "display", "none");
								domStyle.set(dom.byId("chartDiv"), "display", 'none');
							}
							else
							{
								domStyle.set(dom.byId("chartDiv"), "display", 'block');
								domStyle.set(dom.byId("divChart"), "display", "block");
								chart.yAxis[0].plotLinesAndBands[0].svgElem.show();
								chart.yAxis[0].plotLinesAndBands[1].svgElem.show();
								chart.yAxis[0].plotLinesAndBands[2].svgElem.show();
								chart.xAxis[0].setCategories(categories, false);
								chart.series[1].hide();
								chart.series[2].hide();
								chart.series[3].hide();
								chart.series[4].hide();
								chart.series[5].hide();
								chart.yAxis[0].setExtremes(0,10);
								chart.xAxis[0].setExtremes(0,valuesCount-1);
								chart.series[0].setData(indScore, true);
							}
						});//end of request.post get-ind-scores.php
						request.post("../scorecards/get-ind-gauge.php",
						{
							handleAs: "json",
							data: {
								objectId: kpiGlobalId,
								objectType: kpiGlobalType,
								objectPeriod: period,
								objectDate: globalDate
							}
						}).then(function(indGauge)
						{
							domStyle.set(dom.byId("divGauge"), "display", "block");
							gauge.yAxis[0].removePlotBand('red');
							gauge.yAxis[0].removePlotBand('yellow');
							gauge.yAxis[0].removePlotBand('green');
							gauge.yAxis[0].removePlotBand('blue');
							gauge.yAxis[0].removePlotBand('darkGreen');
							gauge.yAxis[0].addPlotBand({
								color: '#ff0000',//red
								from: 0,
								to: 3.33,
								outerRadius: '100%',
								innerRadius: '1%',
								zIndex:-100,
								id: 'red'
							});
							gauge.yAxis[0].addPlotBand({
								color: '#FFD900',//yellow
								from: 3.33,
								to: 6.67,
								outerRadius: '100%',
								innerRadius: '1%',
								zIndex:-100,
								id: 'yellow'
							});
							gauge.yAxis[0].addPlotBand({
								color: '#33CC00',//green
								from: 6.67,
								to: 10,
								outerRadius: '100%',
								innerRadius: '1%',
								zIndex:-100,
								id: 'green'
							});
							if(indGauge == 'No Score' || indGauge == '' || indGauge == null)
							{
								//console.log(', Score: ' + indGauge)
								gauge.series[0].points[0].update(null);
								gauge.series[0].options.dial.radius = 0;
								gauge.series[0].isDirty = true;
								gauge.redraw();
							}
							else
							{
								//console.log('Score: ' + indGauge)
								gauge.series[0].options.dial.radius = '100%';
								gauge.series[0].isDirty = true;
								gauge.redraw();
								var score = parseFloat(indGauge);
								score = Math.round(score * 100) / 100;
								gauge.series[0].points[0].update(score);
							}
						});
					break;
				}
			}
	}//End of updateChart Function
});