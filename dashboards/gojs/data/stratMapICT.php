<?php

//include_once("../config.php");
include_once("../../../bpa/analytics/reports/scores-functions.2.0.php");

//@$objectId = $_POST['objectId'];
//@$objectPeriod = $_POST['objectPeriod'];
//@$objectDate = $_POST['objectDate'];


//$objectDate = strtotime($objectDate);
//$objectDate = date("Y-m-d", strtotime("+1 month", $objectDate));

//echo $test = objective_score('obj44', "2015-07", 'table');

echo '{ "class": "go.GraphLinksModel",
  "nodes": [ 
{"key":"persp", "text":"Perspectives", "isGroup":true, "category":"Pool", "location":"26.59666633605957 0.5"},
{"key":"financial", "text":"Financial", "isGroup":true, "group":"persp", "color":"lightyellow", "location":"50.11951383436751 0.5", "size":"645 99.63916524733138"},
{"key":"customer", "text":"Customer", "isGroup":true, "group":"persp", "color":"lightblue", "location":"50.11951383436751 101.5", "size":"645 105.42284902418685"},
{"key":"processes", "text":"Processes", "isGroup":true, "group":"persp", "color":"lightyellow", "location":"50.11951383436751 208.5", "size":"645 111.22284444655013"},
{"key":"capacity", "text":"Capacity", "isGroup":true, "group":"persp", "color":"lightblue", "location":"50.11951383436751 321.5", "size":"645 99.63916524733138"},
{"key":"obj21", "text":"Reduce Operational Costs", "group":"financial", "fill":"'.getColor("obj21").'", "location":"511.11951383436747 42.5"},
{"key":"obj22", "text":"Strengthen Customer Relationships", "group":"customer", "fill":"'.getColor("obj22").'", "location":"62.11951383436751 113.5"},
{"key":"obj23", "text":"Increase Product Availability When Needed", "group":"customer", "fill":"'.getColor("obj23").'", "location":"316.11951383436747 115.5"},
{"key":"obj24", "text":"Increase Automation", "group":"processes", "fill":"'.getColor("obj24").'", "location":"105.15847413596637 223.5"},
{"key":"obj26", "text":"Reduce Waste", "group":"processes", "fill":"'.getColor("obj26").'", "location":"545.1938887208137 238.5"},
{"key":"obj25", "text":"System is Available and Secure", "group":"processes", "fill":"'.getColor("obj25").'", "location":"252.20093189396047 273.5"},
{"key":"obj27", "text":"Improve Use of Technology", "group":"capacity", "fill":"'.getColor("obj27").'", "location":"221.11951383436752 359.5"}
 ],
  "links": [ 
{"from":"obj27", "to":"obj25"},
{"from":"obj27", "to":"obj26"},
{"from":"obj27", "to":"obj24"},
{"from":"obj24", "to":"obj22"},
{"from":"obj24", "to":"obj23"},
{"from":"obj26", "to":"obj21"}
 ]}';
?>