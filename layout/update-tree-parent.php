<?php
include_once("../config/config_mysqli.php");

@$parentId = $_POST['parentId'];
@$objectId = $_POST['objectId'];

$result = substr($objectId, 0, 3);

switch($result)
{
	case "org":
	{
		mysqli_query($connect, "UPDATE organization SET cascadedfrom = '$parentId' WHERE id = '$objectId'");
		mysqli_query($connect, "UPDATE tree SET parent='$parentId' WHERE id='$objectId'");
		break;	
	}
	case "per":
	{
		mysqli_query($connect, "UPDATE perspective SET parentId = '$parentId' WHERE id = '$objectId'");
		mysqli_query($connect, "UPDATE tree SET parent='$parentId' WHERE id='$objectId'");
		break;	
	}
	case "obj":
	{
			mysqli_query($connect, "UPDATE objective SET linkedObject = '$parentId' WHERE id = '$objectId'");
			mysqli_query($connect, "UPDATE tree SET parent='$parentId' WHERE id='$objectId'");
		break;	
	}
	case "kpi":
	{
			mysqli_query($connect, "UPDATE measure SET linkedObject = '$parentId' WHERE id = '$objectId'");
			mysqli_query($connect, "UPDATE tree SET parent='$parentId' WHERE id='$objectId'");
		break;	
	}	
	case "ind":
	{
			mysqli_query($connect, "UPDATE individual SET cascadedFrom = '$parentId' WHERE id = '$objectId'");
			mysqli_query($connect, "UPDATE tree SET parent='$parentId' WHERE id='$objectId'");
		break;	
	}	
}
?>