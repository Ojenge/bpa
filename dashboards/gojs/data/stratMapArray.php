<?php

//include_once("../config.php");
//include_once("../../../bpa/analytics/functions.php");
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/bpa/functions/functions.php";
include_once($path);

//@$objectId = $_POST['objectId'];
//@$objectPeriod = $_POST['objectPeriod'];
//@$objectDate = $_POST['objectDate'];

$objectDate = date("Y-m-d",strtotime("2021-03"."-01"));
//$objectDate = strtotime($objectDate);
//$objectDate = date("Y-m-d", strtotime("+1 month", $objectDate));

//echo $test = objective_score('obj44', "2015-07", 'table');

function getColor($objId)
{
	$score = objective_score($objId, $objectDate, 'table');
	//echo "Score = $score";
	$color = return_color($score, "threeColor");
	if($color) return $color;
	else return "whitesmoke";
}

echo '{ "class": "go.GraphLinksModel",
  "nodeDataArray": [ 
{"key":"persp", "text":"Perspectives", "isGroup":true, "category":"Pool", "location":"26.59666633605957 0.5"},
{"key":"financial", "text":"Financial", "isGroup":true, "group":"persp", "color":"lightyellow", "location":"50.11951383436751 0.5", "size":"100 99.63916524733138"},
{"key":"customer", "text":"Customer", "isGroup":true, "group":"persp", "color":"lightblue", "location":"50.11951383436751 101.5", "size":"645 105.42284902418685"},
{"key":"processes", "text":"Processes", "isGroup":true, "group":"persp", "color":"lightyellow", "location":"50.11951383436751 208.5", "size":"645 111.22284444655013"},
{"key":"capacity", "text":"Capacity", "isGroup":true, "group":"persp", "color":"lightblue", "location":"50.11951383436751 321.5", "size":"645 99.63916524733138"},
{"key":"obj21", "text":"Increase Revenues", "group":"financial", "fill":"whitesmoke", "location":"340.11951383436747 30"},
{"key":"obj22", "text":"Improve Product Margins", "group":"financial", "fill":"whitesmoke", "location":"20 100"},
{"key":"obj23", "text":"Reduce Operational Costs", "group":"financial", "fill":"whitesmoke", "location":"320.11951383436747 100"},
{"key":"obj24", "text":"Increase Sales", "group":"financial", "fill":"whitesmoke", "location":"615 100"},

{"key":"grp1", "text":"", "group":"customer", "isGroup":"true", "fill":"vanilla", "location":"165 130", "size":"430 55"},
{"key":"obj26", "text":"Strengthen Customer Relationships", "group":"grp1", "fill":"whitesmoke", "location":"180 140"},
{"key":"obj25", "text":"Improve Brand Awareness", "group":"grp1", "fill":"whitesmoke", "location":"410 140"},

{"key":"obj27", "text":"Increase Product Accessibility", "group":"customer", "fill":"whitesmoke", "location":"615 150"},

{"key":"grp2", "text":"", "group":"processes", "isGroup":"true", "fill":"yellow", "location":"160 250", "size":"330 55"},
{"key":"obj28", "text":"Strengthen R & D", "group":"grp2", "fill":"whitesmoke", "location":"40 250"},
{"key":"obj29", "text":"Renovate Existing Products", "group":"grp2", "fill":"whitesmoke", "location":"170 250"},

{"key":"obj30", "text":"Enhance Communication", "group":"processes", "fill":"whitesmoke", "location":"389.1195138343675 250"},
{"key":"obj31", "text":"Improve Route to Market", "group":"processes", "fill":"whitesmoke", "location":"583.1195138343676 250"},
{"key":"obj32", "text":"Enhance Knowledge and Skills", "group":"capacity", "fill":"whitesmoke", "location":"62.11951383436751 350"},
{"key":"obj33", "text":"Improve Performance Management", "group":"capacity", "fill":"whitesmoke", "location":"298.1195138343675 350"},
{"key":"obj34", "text":"Strengthen Organizational Culture", "group":"capacity", "fill":"whitesmoke", "location":"302.1195138343675 450"},
{"key":"obj35", "text":"Increase Use of Technology", "group":"capacity", "fill":"whitesmoke", "location":"574.1195138343676 350"}
 ],
  "linkDataArray": [ 
{"from":"obj34", "to":"obj33"},
{"from":"obj33", "to":"obj30"},
{"from":"obj35", "to":"obj31"},
{"from":"obj31", "to":"obj27"},
{"from":"obj22", "to":"obj21"},
{"from":"obj23", "to":"obj21"},
{"from":"obj27", "to":"obj24"},
{"from":"obj24", "to":"obj21"},
{"from":"grp1", "to":"obj24"},
{"from":"grp2", "to":"obj22"},
{"from":"obj32", "to":"grp2"},
{"from":"obj30", "to":"grp1"}
 ]}';
?>