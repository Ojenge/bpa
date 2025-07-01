<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Forms posted
if(!empty($_POST))
{
	//Delete permission levels
	if(!empty($_POST['toDelete'])){
		$deletions = $_POST['toDelete'];
		if ($deletion_count = deletePermission($deletions)){
		$successes[] = lang("PERMISSION_DELETIONS_SUCCESSFUL", array($deletion_count));
		}
	}
	
	//Create new permission level
	if(!empty($_POST['newPermission'])) {
		$permission = trim($_POST['newPermission']);
		
		//Validate request
		if (permissionNameExists($permission)){
			$errors[] = lang("PERMISSION_NAME_IN_USE", array($permission));
		}
		elseif (minMaxRange(1, 50, $permission)){
			$errors[] = lang("PERMISSION_CHAR_LIMIT", array(1, 50));	
		}
		else{
			if (createPermission($permission)) {
			$successes[] = lang("PERMISSION_CREATION_SUCCESSFUL", array($permission));
		}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
	}
}

$permissionData = fetchModulePermissions(); //Retrieve list of function permissions

require_once("models/header.php");

echo "
</div>
<div id='main'>";

echo resultBlock($errors,$successes);

echo "
<table class='table table-hover table-condensed table-bordered table-responsive'>
<tr>
<th colspan='3' align='center'>Accent Analytics Modules</th>
</tr>
<tr>
<th>Module Name</th><th align='center'>Activate/Deactivate</th><th align='center'>Home Page</th>
</tr>";

//List each permission level
foreach ($permissionData as $v1) {
	if(substr($v1['orgId'],0,3) == 'org' || $v1['orgId'] == 'root')
	{
		echo "
		<tr>
		
		<td><a href='#' onClick='adminPermInfo(".$v1['id'].")'> ".$v1['name']."</a></td>
		<td>Organization</td>
		</tr>";
	}
	else if(substr($v1['orgId'],0,3) == 'ind')
	{
		echo "
		<tr>
		
		<td><a href='#' onClick='adminPermInfo(".$v1['id'].")'> ".$v1['name']."</a></td>
		<td>Individual</td>
		</tr>";
	}
	else
	{ 
		echo "
		<tr>
		<td><a href='#' onClick='adminPermInfo(".$v1['id'].")'> ".$v1['name']."</a></td>
		<td><a href='#' onClick='adminActivate(".$v1['id'].",\"".$v1['status']."\")'> ".$v1['status']."</a></td>
		<td><a href='#' onClick='adminHome(".$v1['id'].",\"".$v1['home']."\")'> ".$v1['home']."</a></td>
		</tr>";
	}	
}
echo "
</table>
<p>
<label>New Permission:</label>
<input type='text' name='newPermission' id='newPermission'/>                                
<input type='submit' name='Submit' onclick='adminAddPerm()' value='Submit' />
</div>";
?>