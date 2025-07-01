<?php 
include_once("../config/config_mysqli.php");

require_once("../admin/models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

$userId = $loggedInUser->user_id;
//$userId = "ind2";

$tree_rights = mysqli_query($connect, "SELECT uc_permissions.orgId AS orgId
FROM `uc_user_permission_matches`, `uc_permissions`
WHERE uc_user_permission_matches.user_id = '$userId'
AND uc_user_permission_matches.permission_id = uc_permissions.id
AND uc_permissions.orgId != 'NULL'");

$tree_rights_count=mysqli_num_rows($tree_rights);
$orgId = NULL;
$count = 0;
while($row = mysqli_fetch_assoc($tree_rights))
{
	//$orgId = $orgId.'\''.$row['orgId'].'\'';
	$orgId_array[$count] = $row['orgId'];
	//if ($count < $tree_rights_count) $orgId = $orgId.",";
	$count++;
}
$where_in = implode("','", $orgId_array);
$where_in = "'".$where_in."'";

$where_in;

$tree_query="SELECT id, name, parent, type, linked, sort 
FROM tree 
WHERE id IN ($where_in) 
OR id LIKE 'obj%' 
OR id LIKE 'persp%' 
OR id LIKE 'kpi%'
ORDER BY FIELD(type, 'perspective', 'objective', 'measure', 'individual', 'organization'), sort ASC, sort";//This ensures that we have the scorecard items maintaining proper structure inspite of order in which they were captured in the system. It also ensures the individuals are shown directly under their organizations unlike before. LTK 15.04.21 1912hrs
$tree_result=mysqli_query($connect, $tree_query);
$tree_row_count=mysqli_num_rows($tree_result);
$count = 1; 

echo "[";
while($row = mysqli_fetch_assoc($tree_result))
{
	$meta = json_encode($row);
	echo $meta;
	if ($count < $tree_row_count) echo ",";
	$count++;
}
echo "]";

flush();
exit;
?>