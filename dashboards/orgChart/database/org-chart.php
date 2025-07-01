<?php

include_once("../../../config/config_mysqli.php");
include_once("../../../admin/models/config.php");
include_once("../../../reports/scores-functions.2.0.php");
//include_once("../../../reports/scores-functions.2.0.php");

@$objectId = $_POST['objectId'];
@$objectPeriod = $_POST['objectPeriod'];
@$objectDate = $_POST['objectDate'];
@$objectDate = date("Y-m-d",strtotime($objectDate."-01"));

//@$objectId = "org1";
//@$objectPeriod = "months";
//@$objectDate = "2024-03-03";
//@$objectDate = date("Y-m-d",strtotime($objectDate."-01"));

//$userPermission = fetchUserPermissions($loggedInUser->user_id);
$userPermission = fetchUserPermissions("16");

//echo json_encode($userPermission, JSON_PRETTY_PRINT);

$showAllUsers = "False";
$permittedUsers = array();
$permCount = 0;
foreach($userPermission as $id)
{
	if($id["permission_id"] == "2") $showAllUsers = "True"; //Administrator Role
	$permittedUsers[$permCount] = $id["permission_id"];
	$permCount++;
}

//echo getColor("ind2")."<br><br>";

$staffQuery = mysqli_query($connect, "SELECT user_id, user_name, display_name, reportsTo, photo, title, department
FROM uc_users
WHERE department <> 'org0'
AND title <> 'Executive Assistant'
ORDER by reportsTo") or file_put_contents("error.txt", "Error=> ".mysqli_error($connect));
$orgArray = array();
$orgArrayCumm = array();
$count = 0;
$id = 1;
//file_put_contents("orgChart.txt", "");
//echo '<pre>'; print_r($userPermission); echo '</pre>';
//echo '<pre>'; print_r($permittedUsers); echo '</pre>';
$permittedUsers = array(10, 49, 11, 7);
while($row = mysqli_fetch_array($staffQuery))
{
	$orgArray[$count]["id"] = $id;
	$orgArray[$count]["user_id"] = $row["user_id"];
	$orgArray[$count]["user_name"] = $row["user_name"];
	$orgArray[$count]["photo"] = $row["photo"];
	$orgArray[$count]["title"] = $row["title"];
	$orgArray[$count]["department"] = $row["department"];
	$orgArray[$count]["name"] = $row["display_name"];
	$orgArray[$count]["reportsTo"] = $row["reportsTo"];
	
	$permittedUser = preg_replace("/[^0-9]/", "", $row["user_id"] );
	$inRights = in_array($permittedUser, $permittedUsers);//This isn't working. Review. LTK 24 Jul 2021 1906 hours
	//$inRights = array_search($permittedUser, $userPermission);
	//file_put_contents("orgChart.txt", "\npermittedUser = $permittedUser; inRights = $inRights;" .json_encode($permittedUsers), FILE_APPEND);
	//echo "<br>$count. showAllUsers = $showAllUsers; permittedUser = $permittedUser; inRights = $inRights";
	$orgArray[$count]["className"] = getColor($row["user_id"]);
	if($showAllUsers == "True" || $inRights) //show colors for admins or those with given rights to speficic users
	{
		//echo "If";
		$score = individualScore($row["user_id"], $objectDate);
		$orgArray[$count]["className"] = getColor($score);
	}
	else
	{
		//echo "Else";
		//$orgArray[$count]["className"] = "permittedUsers";
		$orgArray[$count]["className"] = "grey";
	}
	$count++;
	$id++;
	//echo '<pre>'; print_r($orgArray); echo '</pre>';
}

//echo '<pre>'; print_r($orgArray); echo '</pre>';
//echo "Size: ".sizeof($orgArray);
function findParent(&$array, $parentid = 0, $childarray = array())	 // make $array modifiable
{ 
    foreach($array as $i=>&$row) 									 // make $row modifiable
	{
        if($parentid)												 // if not zero
		{
            if($row['id' ] == $parentid)							 // found parent
			{
                $row['children'][] = $childarray;					 // append child to parent's nodes subarray
            }
			elseif(isset($row['children']))							 // go down rabbit hole looking for parent
			{
                findParent($row['children'], $parentid, $childarray);// look deeper for parent while preserving the initial parent_id and row
            }                                             			 // else continue;
        }
		elseif($row['reportsTo'])									 // child requires adoption
		{
            unset($array[$i]); 										 // remove child from level because it will be store elsewhere and won't be its own 
																	 // parent (reduce iterations in next loop & avoid infinite recursion)
            findParent($array, $row['reportsTo'], $row);    		 // look for parent using parent_id while carrying the entire row as the childarray
        }                                                 			 // else continue;
    }
    return $array;                                        			 // return the modified array
}

$ceo = array_search("Board",array_column($orgArray, 'reportsTo'));
$orgArray[$ceo]["reportsTo"] = 0;

for($i=1; $i<sizeof($orgArray); $i++)
{
	$parent = array_search($orgArray[$i]["reportsTo"],array_column($orgArray, 'user_id'));
	$orgArray[$i]["reportsTo"] = $orgArray[$parent]["id"];
}
//echo "<pre>";
$org = json_encode(findParent($orgArray), JSON_PRETTY_PRINT);
$org = substr($org, 1);
$org = substr($org, 0, -1);
echo $org;
//echo '</pre>';
/*	
echo '
{
	"id": "3", "name": "Maryanne Musangi", "title": "Managing Director", "className": "",
	"children": [{ "id": "2","name": "Nicholas Kihara", "title": "ICT Manager", "className": "'.$nicholas.'",
					"children":[{ "id": "4","name": "Name", "title": "ICT Assistant", "className": "'.$test.'"},
							{ "id": "5","name": "Name", "title": "ICT Support", "className": "'.$test.'"}]
				}]
}';*/
?>