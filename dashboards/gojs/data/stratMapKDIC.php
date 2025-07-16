<?php
//include_once("../config.php");
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/bpa/reports/scores-functions.2.0.php";
include_once($path);
//@$objectId = $_POST['objectId'];
//@$objectPeriod = $_POST['objectPeriod'];
//@$objectDate = $_POST['objectDate'];

//$objectDate = strtotime($objectDate);
//$objectDate = date("Y-m-d", strtotime("+1 month", $objectDate));

//echo $test = objective_score('obj44', "2015-07", 'table');

function getMapColor($objective)
{
  $score = getObjScore($objective);
  if ($score === null || $score === "") return "lightgray";
  else return $color = getColor($score);
}

$stratMap = '{ "class": "go.GraphLinksModel",
  "nodeDataArray": [ 
{"key":"persp", "text":"Perspectives", "isGroup":true, "category":"Pool", "location":"26.59666633605957 0.5"},
{"key":"financial", "text":"Financial", "isGroup":true, "group":"persp", "color":"lightyellow", "location":"50.11951383436751 0.5", "size":"100 99.63916524733138"},
{"key":"customer", "text":"Customer", "isGroup":true, "group":"persp", "color":"lightblue", "location":"50.11951383436751 101.5", "size":"645 105.42284902418685"},
{"key":"processes", "text":"Processes", "isGroup":true, "group":"persp", "color":"lightyellow", "location":"50.11951383436751 208.5", "size":"645 111.22284444655013"},
{"key":"capacity", "text":"Capacity", "isGroup":true, "group":"persp", "color":"lightblue", "location":"50.11951383436751 321.5", "size":"645 99.63916524733138"},';

$stratMap .= '{"key":"obj29", "text":"Enhance Depositor\nProtection & Compensation", "group":"financial", "fill":"'.getMapColor("obj29").'", "location":"59.18666782379151 30"},
{"key":"obj30", "text":"Prudent Management of\nDeposit Insurance Fund", "group":"financial", "fill":"'.getMapColor("obj30").'", "location":"259.1866678237915 30"},
{"key":"obj31", "text":"Reduce the Ammount\nOwed by Debtors in Financial\nInstitutions in Liquidation", "group":"financial", "fill":"'.getMapColor("obj31").'", "location":"440.30618165815895 23"},
{"key":"obj32", "text":"Enhance Prudence in the\nUtilisation of Resources", "group":"financial", "fill":"'.getMapColor("obj32").'", "location":"653.1866678237915 30"},';

//Grouping no longer necessary for the current strat map; leaving this here for future reference. LTK 16Jul2025 1113hrs
/*$stratMap .= '{"key":"grp1", "text":"", "group":"customer", "isGroup":"true", "fill":"vanilla", "location":"165 130", "size":"430 55"},
{"key":"obj33", "text":"Strengthen Customer Relationships", "group":"grp1", "fill":"'.getMapColor("obj33").'", "location":"180 140"},
{"key":"obj34", "text":"Improve Brand Awareness", "group":"grp1", "fill":"'.getMapColor("obj34").'", "location":"410 140"},*/

$stratMap .= '{"key":"obj35", "text":"Establish & Enhance Strategic\nCollaborations and Partnerships\nwith Relevant Stakeholders", "group":"customer", "fill":"'.getMapColor("obj35").'", "location":"82 122"},
{"key":"obj35", "text":"Improve Public Awareness Index\nfrom 14% to 28% by 2028", "group":"customer", "fill":"'.getMapColor("obj35").'", "location":"340 129"},
{"key":"obj35", "text":"Wind up Financial Institutions\nin Liquidation", "group":"customer", "fill":"'.getMapColor("obj35").'", "location":"605 129"},';

$stratMap .= '{"key":"obj38", "text":"Automate Processes & Digitise Records", "group":"processes", "fill":"'.getMapColor("obj38").'", "location":"64 250"},
{"key":"obj39", "text":"Enhance Risk Minimization", "group":"processes", "fill":"'.getMapColor("obj39").'", "location":"395 250"},
{"key":"obj39", "text":"Standardize Processes", "group":"processes", "fill":"'.getMapColor("obj39").'", "location":"660 250"},';

$stratMap .= '{"key":"obj40", "text":"Strengthen the Regulatory\nFramework", "group":"capacity", "fill":"'.getMapColor("obj40").'", "location":"60 345"},
{"key":"obj41", "text":"Strengthen Early Intervention\nSystems Framework", "group":"capacity", "fill":"'.getMapColor("obj41").'", "location":"257 345"},
{"key":"obj42", "text":"Improve Crisis Management\nFramework", "group":"capacity", "fill":"'.getMapColor("obj42").'", "location":"463.1195138343675 345"},
{"key":"obj43", "text":"Attract, Acquire &\nRetain Talent", "group":"capacity", "fill":"'.getMapColor("obj43").'", "location":"673.1195138343676 345"},
{"key":"obj40", "text":"Strengthen Employee\nMorale & Motivation", "group":"capacity", "fill":"'.getMapColor("obj40").'", "location":"63 430"},
{"key":"obj41", "text":"Institutionalise Performance\nManagement & Staff Productivity", "group":"capacity", "fill":"'.getMapColor("obj41").'", "location":"230 430"},
{"key":"obj42", "text":"Improve the Regulatory\nFramework to Strengthen\nResolution of Problem Banks", "group":"capacity", "fill":"'.getMapColor("obj42").'", "location":"460 423"},
{"key":"obj43", "text":"Build a Vibrant & Cohesive\nOrganizational Culture", "group":"capacity", "fill":"'.getMapColor("obj43").'", "location":"662 430"}
 ],';

$stratMap .= '"linkDataArray": []}';

 echo $stratMap;

 /*$stratMapPackingLot .= '"linkDataArray": [ 
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
   ]}';*/
?>