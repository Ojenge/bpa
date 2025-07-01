<?php
// Use absolute path from the directory where this file is located
include_once(__DIR__ . "/../config/config_mysqli.php");
function menu($userPermission)
{	
	global $connect;
	/*
	<div class='vMenu'><a href='#' onClick='homeCalendar();'>My Calendar</a></div>
	<div class='vMenu'><a href='#' onClick='pduDbDeliveryBook();'>The Delivery Book</a></div>*/
	/*$menuList = "
		<div class='vMenu'><a href='#' onClick='indDashboard();'>Personal Dashboard</a></div>
		<div class='vMenu'><a href='#' onClick='myDataEntry();'>My Measures & Tasks</a></div>
		<div class='vMenu'><a href='#' onClick='orgChart();'>Org Structure</a></div>
		<div class='vMenu'><a href='#' onClick='stratMap();'>Strategy Map</a></div>";*/

		$menuList = "
		<p class='text-secondary fs-6' style='cursor:pointer' onClick='indDashboard();'><i class='bi bi-speedometer text-success'></i> Personal Dashboard</p>
		<div style='margin-left: 20px; border-left: 2px solid #e9ecef; padding-left: 10px;'>
			<p class='text-secondary fs-6' style='cursor:pointer; font-size: 0.85rem;' onClick='dashboardsOverview();'><i class='bi bi-grid-3x3-gap text-info'></i> Analytics Overview</p>
			<p class='text-secondary fs-6' style='cursor:pointer; font-size: 0.85rem;' onClick='departmentDashboard();'><i class='bi bi-building text-primary'></i> Department Performance</p>
			<p class='text-secondary fs-6' style='cursor:pointer; font-size: 0.85rem;' onClick='executiveSummary();'><i class='bi bi-people text-success'></i> Executive Summary</p>
			<p class='text-secondary fs-6' style='cursor:pointer; font-size: 0.85rem;' onClick='teamProductivityDashboard();'><i class='bi bi-graph-up text-warning'></i> Team Productivity</p>
			<p class='text-secondary fs-6' style='cursor:pointer; font-size: 0.85rem;' onClick='goalTrackingDashboard();'><i class='bi bi-bullseye text-danger'></i> Goal Tracking</p>
			<p class='text-secondary fs-6' style='cursor:pointer; font-size: 0.85rem;' onClick='performanceHeatMaps();'><i class='bi bi-thermometer-half text-info'></i> Performance Heat Maps</p>
			<p class='text-secondary fs-6' style='cursor:pointer; font-size: 0.85rem;' onClick='staffManagementDashboard();'><i class='bi bi-person-gear text-secondary'></i> Staff Management</p>
			<p class='text-secondary fs-6' style='cursor:pointer; font-size: 0.85rem;' onClick='predictiveAnalytics();'><i class='bi bi-graph-up-arrow text-primary'></i> Predictive Analytics</p>
			<p class='text-secondary fs-6' style='cursor:pointer; font-size: 0.85rem;' onClick='initiativeProjectAnalytics();'><i class='bi bi-kanban text-success'></i> Initiative Analytics</p>
		</div>
		<p class='text-secondary fs-6' style='cursor:pointer' onClick='myDataEntry();'><i class='bi bi-bar-chart-line text-warning'></i> My Measures & Tasks</p>
		<p class='text-secondary fs-6' style='cursor:pointer' onClick='orgChart();'><i class='bi bi-diagram-3 text-danger'></i> Org Structure</p>
        <p class='text-secondary fs-6' style='cursor:pointer' onClick='stratMap();'><i class='bi bi-compass text-primary'></i> Strategy Map</p>";
		/*<div class='vMenu'><a href='#' onClick='bookMarks();'>My Bookmarks</a></div>
		<div id='myBookmarks'></div>";*/ //No longer necessary.
		echo "$menuList";
		
	foreach($userPermission as $id)
	{
		$permissionId = $id["permission_id"];
		//file_put_contents("aMenu.txt", "\nID = ".$permissionId, FILE_APPEND);
		$getFunctions = mysqli_query($connect, "SELECT name, callFunction, icon FROM uc_permissions WHERE id = '$permissionId' AND status = 'Active' AND orgId LIKE 'func%'") or file_put_contents("aMenu.txt","Error => ".mysqli_error($connect));
		while($row = mysqli_fetch_array($getFunctions))
		{
			$function = $row["callFunction"];

			if (strpos($function, '/') !== false) 
			{
			echo "<div class='vMenu' style='margin-top:3px; border-radius:3px; white-space:nowrap;'><a target='_blank' href='".$row["callFunction"]."'>".$row["name"]."</a></div>";
			}
			else
			//echo "<div class='vMenu' style='margin-top:3px; border-radius:3px; white-space:nowrap;'><a href='#' onClick='".$row["function"]."'>".$row["name"]."</a></div>";
            echo "<p class='text-secondary fs-6' style='cursor:pointer' onClick='".$row["callFunction"]."'>".$row["icon"]." ".$row["name"]."</p>";
		}
	}
}

function directivesMenu()
{
	echo "
	<div class='vMenu' style='margin-top:6px; border-radius:3px;'><a href='admin/logout.php'>Logout</a></div>
	<div class='vMenu' style='margin-top:3px; border-radius:3px; white-space:nowrap;'><a href='pdu_db_directives.php'>Presidential Directives</a></div>";	
}
function logo()
{
	echo 
	"<div id='curveGreen'><div id='green'></div></div>
	<div id='curveWhite2'><div id='white2'></div></div>
	<div id='curveRed'><div id='red'></div></div>
	<div id='curveWhite'><div id='white'></div></div>
	<div id='curveBlack'><div id='black'></div></div>
	<div id='coat_of_arms' align='center'><img src='images/coat_of_arms.png' width='86' height='80'/><br><span style='font-weight:bold; font-size:12px;'>President's Delivery Unit</span></div>";			
}
?>