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
  "nodeDataArray": [ 
{"key":"persp", "text":"Perspectives", "isGroup":true, "category":"Pool", "location":"26.59666633605957 0.5"},
{"key":"financial", "text":"Financial", "isGroup":true, "group":"persp", "color":"lightyellow", "location":"50.11951383436751 0.5", "size":"100 99.63916524733138"},
{"key":"customer", "text":"Customer", "isGroup":true, "group":"persp", "color":"lightblue", "location":"50.11951383436751 101.5", "size":"645 105.42284902418685"},
{"key":"processes", "text":"Processes", "isGroup":true, "group":"persp", "color":"lightyellow", "location":"50.11951383436751 208.5", "size":"645 111.22284444655013"},
{"key":"capacity", "text":"Capacity", "isGroup":true, "group":"persp", "color":"lightblue", "location":"50.11951383436751 321.5", "size":"645 99.63916524733138"},
{"key":"obj29", "text":"Increase Revenues", "group":"financial", "fill":"'.getColor("obj29").'", "location":"340.11951383436747 30"},
{"key":"obj30", "text":"Improve Product Margins", "group":"financial", "fill":"'.getColor("obj30").'", "location":"20 100"},
{"key":"obj31", "text":"Reduce Operational Costs", "group":"financial", "fill":"'.getColor("obj31").'", "location":"320.11951383436747 100"},
{"key":"obj32", "text":"Increase Sales", "group":"financial", "fill":"'.getColor("obj32").'", "location":"615 100"},

{"key":"grp1", "text":"", "group":"customer", "isGroup":"true", "fill":"vanilla", "location":"165 130", "size":"430 55"},
{"key":"obj33", "text":"Strengthen Customer Relationships", "group":"grp1", "fill":"'.getColor("obj33").'", "location":"180 140"},
{"key":"obj34", "text":"Improve Brand Awareness", "group":"grp1", "fill":"'.getColor("obj34").'", "location":"410 140"},

{"key":"obj35", "text":"Increase Product Accessibility", "group":"customer", "fill":"'.getColor("obj35").'", "location":"615 150"},

{"key":"grp2", "text":"", "group":"processes", "isGroup":"true", "fill":"yellow", "location":"160 250", "size":"330 55"},
{"key":"obj36", "text":"Strengthen R & D", "group":"grp2", "fill":"'.getColor("obj36").'", "location":"40 250"},
{"key":"obj37", "text":"Renovate Existing Products", "group":"grp2", "fill":"'.getColor("obj37").'", "location":"170 250"},

{"key":"obj38", "text":"Enhance Communication", "group":"processes", "fill":"'.getColor("obj38").'", "location":"389.1195138343675 250"},
{"key":"obj39", "text":"Improve Route to Market", "group":"processes", "fill":"'.getColor("obj39").'", "location":"583.1195138343676 250"},
{"key":"obj40", "text":"Enhance Knowledge and Skills", "group":"capacity", "fill":"'.getColor("obj40").'", "location":"62.11951383436751 350"},
{"key":"obj41", "text":"Improve Performance Management", "group":"capacity", "fill":"'.getColor("obj41").'", "location":"298.1195138343675 350"},
{"key":"obj42", "text":"Strengthen Organizational Culture", "group":"capacity", "fill":"'.getColor("obj42").'", "location":"302.1195138343675 450"},
{"key":"obj43", "text":"Increase Use of Technology", "group":"capacity", "fill":"'.getColor("obj43").'", "location":"574.1195138343676 350"}
 ],
  "linkDataArray": [ 
{"from":"obj42", "to":"obj41"},
{"from":"obj41", "to":"obj38"},
{"from":"obj43", "to":"obj39"},
{"from":"obj39", "to":"obj35"},
{"from":"obj30", "to":"obj29"},
{"from":"obj31", "to":"obj29"},
{"from":"obj35", "to":"obj32"},
{"from":"obj32", "to":"obj29"},
{"from":"grp1", "to":"obj32"},
{"from":"grp2", "to":"obj30"},
{"from":"obj40", "to":"grp2"},
{"from":"obj38", "to":"grp1"}
 ]}';
?>