require([
"dojo/request"],
function(request)
{
window.initiativeGantt = function(userId)
//function initiativeGantt(userId)
{
	request.post("initiatives/get-initiative-gantt.php",{
	handleAs: "json",
	data: {
		userId: userId
	}
	}).then(function(ganttData) 
	{
		var	projectNames = [];// = ['Prototyping', 'Development', 'Testing'];
		var projectData = [];
		var startYear, startMonth, startDay, start;
		var endYear, endMonth, endDay, end;
		var count = 0;
		var completed, owner, name;
		while(count < ganttData.length)
		{
			//projectNames[count] = ganttData[count].name;
			
			startYear = Number(ganttData[count].startYear);
			startMonth = Number(ganttData[count].startMonth);
			startDay = Number(ganttData[count].startDay);
			start = Date.UTC(startYear, startMonth, startDay);
			
			endYear = Number(ganttData[count].endYear);
			endMonth = Number(ganttData[count].endMonth);
			endDay = Number(ganttData[count].endDay);
			end = Date.UTC(endYear, endMonth, endDay);
			
			completed = parseFloat(ganttData[count].completed)/100;
			owner = ganttData[count].owner;
			name = ganttData[count].name;
			//console.log(completed);
			//const leo = new Date(end);
			//console.log(leo.toUTCString());
			
			projectData[count] = {start:start, end:end, name:name, assignee:ganttData[count].assignee, completed: completed, owner: owner};
			count++;
		}
		//var today = new Date(),
		//day = 1000 * 60 * 60 * 24,
		// Utility functions
		var dateFormat = Highcharts.dateFormat,
		defined = Highcharts.defined,
		isObject = Highcharts.isObject;
		var day = 1000 * 60 * 60 * 24;
		var chart = new Highcharts.ganttChart('gantt', {
			styledMode: true,
			yAxis: {
				//categories: []
			},
			xAxis: {
				currentDateIndicator: true,
				tickInterval: 1000 * 60 * 60 * 24 * 30,// 1 month
				dateTimeLabelFormats: {
                month: '%b'
                //year: '%y'
           		},
			},
			 navigator: {
				enabled: true,
				liveRedraw: true,
				series: {
					type: 'gantt',
					pointPlacement: 0.5,
					pointPadding: 0.25
				},
				yAxis: {
					//min: 0,
					//max: 3,
					//reversed: true,
					
				}
			},
			scrollbar: {
				enabled: true
			},
			rangeSelector: {
				enabled: true,
				selected: 5
			},
			tooltip: {
						pointFormatter: function () {
							var point = this,
								format = '%a %d %b %Y',
								/*
									%a: Short weekday, like 'Mon'.
									%A: Long weekday, like 'Monday'.
									%d: Two digit day of the month, 01 to 31.
									%e: Day of the month, 1 through 31.
									%b: Short month, like 'Jan'.
									%B: Long month, like 'January'.
									%m: Two digit month number, 01 through 12.
									%y: Two digits year, like 09 for 2009.
									%Y: Four digits year, like 2009.
									%H: Two digits hours in 24h format, 00 through 23.
									%I: Two digits hours in 12h format, 00 through 11.
									%l (Lower case L): Hours in 12h format, 1 through 11.
									%M: Two digits minutes, 00 through 59.
									%p: Upper case AM or PM.
									%P: Lower case AM or PM.
									%S: Two digits seconds, 00 through 59
								*/
								//completed = options.completed,
								//amount = isObject(completed) ? completed.amount : completed,
								//status = ((amount || 0) * 100) + '%',
								lines;
				
							lines = [{
								value: point.name,
								style: 'font-weight: bold;'
							}, {
								title: 'Start',
								value: dateFormat(format, point.start)
							}, {
								//visible: !options.milestone,
								title: 'End',
								value: dateFormat(format, point.end)
							}, {
								title: 'Status',
								value: point.completed*100+"% complete"
							}, {
								title: 'Owner',
								value: point.owner || 'unassigned'
							}];
				
							return lines.reduce(function (str, line) {
								var s = '',
									style = (
										defined(line.style) ? line.style : 'font-size: 0.8em;'
									);
								if (line.visible !== false) {
									s = (
										'<span style="' + style + '">' +
										(defined(line.title) ? line.title + ': ' : '') +
										(defined(line.value) ? line.value : '') +
										'</span><br/>'
									);
								}
								return str + s;
							}, '');
						}
				},
			series: [{
				name: 'Initiatives',
				data: projectData,
				dataLabels: [{
					enabled: true,
					format: '<div style="width: 20px; height: 20px; overflow: hidden; border-radius: 50%; margin-left: -25px">' +
						'<img src="{point.assignee}" ' +
						'style="width: 30px; margin-left: -5px; margin-top: -2px"></div>',
					useHTML: true,
					align: 'left'
				}]
			}],
			credits: {enabled: false}
		});
	});
}
});