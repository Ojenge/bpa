<?php

//include_once("../config.php");
include_once("../../../bpa/analytics/reports/scores-functions.2.0.php");

//@$objectId = $_POST['objectId'];
//@$objectPeriod = $_POST['objectPeriod'];
//@$objectDate = $_POST['objectDate'];

$objectDate = date("Y-m-d",strtotime("2023-03"."-01"));
//$objectDate = strtotime($objectDate);
//$objectDate = date("Y-m-d", strtotime("+1 month", $objectDate));

//echo $test = objective_score('obj44', "2015-07", 'table');

echo '{ "class": "go.GraphLinksModel",
  "nodes": [ 


{"key":"customer", "text":"", "isGroup":true,  "color":"lightblue", "location":"50.11951383436751 101.5", "size":""},
{"key":"obj1", "text":"Engaging more with our customers", "group":"customer", "fill":"whitesmoke", "location":"340.11951383436747 30"},
{"key":"obj2", "text":"Our Products meet customer needs", "group":"customer", "fill":"whitesmoke", "location":"20 100"},
{"key":"obj3", "text":"Our profits are growing", "group":"customer", "fill":"whitesmoke", "location":"320.11951383436747 100"},
{"key":"obj4", "text":"We are  making more money from sales", "group":"customer", "fill":"whitesmoke", "location":"615 100"},

{"key":"grp1", "text":"", "group":"customer", "isGroup":"true", "fill":"vanilla", "location":"165 130", "size":"430 55"},
{"key":"obj5", "text":"Strengthen Customer Relationships", "group":"grp1", "fill":"whitesmoke", "location":"180 140"},
{"key":"obj6", "text":"Improve Brand Awareness", "group":"grp1", "fill":"whitesmoke", "location":"410 140"}
 ],
  "links": [ 
{"from":"obj34", "to":"obj33"}
 ]}';
?>