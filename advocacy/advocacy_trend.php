<?php 
//require_once("admin/models/config.php");
require_once('config.php');
//if (!securePage($_SERVER['PHP_SELF'])){die();}
//@$userPermission = fetchUserPermissions($loggedInUser->user_id);
?>
<style>
table.reportTable
{
	font-size: 13px;
	border-collapse: collapse; 
	border-top: 1px solid #9baff1; 
	border-bottom: 1px solid #9baff1;
	font-family:"Trebuchet MS", Arial, Helvetica, sans-serif;
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
table.unformatted
{
	font-size: 13px;
	border-collapse: collapse; 
	border-top: 1px solid white; 
	border-bottom: 1px solid white;
	font-family:"Trebuchet MS", Arial, Helvetica, sans-serif;
}
table.unformatted td
{
	padding: 0px; 
	border-right: 1px solid white; 
	border-left: 1px solid white; 
	border-top: 1px solid white;
}
.vMenu
{
	background:#d9d9d9;
	padding:4px/*padding for top, bottom*/ 7px /*padding for left, right*/;
	text-decoration:none;
	border-bottom:1px solid #eeeeee;
	border-top:1px solid #cccccc;
	border-left:5px solid #333333;
	color:#333333;
}
.vMenu:hover
{
	border-left-color:#0099FF;
	color:#0066FF;
	background:#c4c4c4;
}
</style>
<script src="../dojo/dojo.js" data-dojo-config="async: true, parseOnLoad: true"></script>
<script src="../../highCharts404/js/adapters/standalone-framework.js"></script>
<script src="../../highCharts414/js/highcharts.js"></script>
<script src="../../highCharts414/js/themes/sand-signika.js"></script>

<script>
require([
"dojo/dom",
"dojo/request",
"dojo/domReady!"], function(dom, request)
{
request.post("get-trend.php",{
		handleAs: "json"
	}).then(function(trend) 
	{	
		
		var count = 0, red = [], labels = [], yellow = [], green = [];
		while(count < trend.length)
		{
			red[count] = parseInt(trend[count].red,10);
			yellow[count] = parseInt(trend[count].yellow,10);
			green[count] = parseInt(trend[count].green,10);
			labels[count] = trend[count].date;
			count++;
		}
		
		chart = new Highcharts.Chart({
		chart: {
			renderTo: 'kiboTrend',
			type: 'area'
		},
	     title: {
            text: 'Work Plan Performance Over Time'
        },
        subtitle: {
            text: ''
        },
        xAxis: {
            categories: labels,
            tickmarkPlacement: 'on',
            title: {
                enabled: false
            }
        },
        yAxis: {
            title: {
                text: 'Number of Work Plans'
            }
        },
        tooltip: {
            shared: true,
            //valueSuffix: ' millions'
        },
        plotOptions: {
            area: {
                stacking: 'normal',
                lineColor: '#666666',
                lineWidth: 1,
                marker: {
                    lineWidth: 1,
                    lineColor: '#666666'
                }
            }
        },
        series: [{
            name: 'Red',
            data: red,
			color: 'red'
        }, {
            name: 'Yellow',
            data: yellow,
			color: 'yellow'
        }, {
            name: 'Green',
            data: green,
			color: 'green'
        }],
		credits: {
		enabled: false
	  }
    });
	
	})
	
})
</script>
 <table width='90%'>
    	<tr>
        	<td valign="top" align="center" height="120px">
				<div style="width:100px;">
					<?php logo();?>
                </div>
                </td>
                <td rowspan="2" align="center"><div id="kiboTrend" style="width:900px; height:400px;"></div></td>
                </tr>
                <tr><td valign="top">
                <div style="">
                <?php workplansMenu(); ?>
                <div id='editPermission' style="display:none">Can Edit</div>
                    <?php 
					/*if(!empty($userPermission))
					{
						foreach($userPermission as $id)
						{
							if($id["permission_id"] == "8")//Menu with access to workplans only
							{
								workplansMenu();
								?>
								<div id='editPermission' style="display:none">No Edit</div>
								<?php
							}
							else
							{
								menu();	
								?>
								<div id='editPermission' style="display:none">Can Edit</div>
								<?php
							}
						}
					}*/
					?><br>
                </div>
            </td>
            
        </tr>
    </table>
