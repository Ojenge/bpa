<?php
include_once("../config/config_mysqli.php");

$userId = $_POST["userId"];
$initiativeId = $_POST["initiativeId"];

//file_put_contents("remove.txt", "Couldn't delete $userId and $initiativeId");

//$userId = 'ind1';
//$objectiveId = 'persp1';

mysqli_query($connect, "DELETE FROM initiativeteam WHERE user_id = '$userId' AND initiative_id = '$initiativeId'") or file_put_contents("remove.txt", "Couldn't delete");
$query = mysqli_query($connect, "SELECT DISTINCT uc_users.user_id, uc_users.display_name AS user, uc_users.email AS email, uc_users.telephone AS telephone FROM uc_users, initiativeteam 
WHERE uc_users.user_id = initiativeteam.user_id AND initiativeteam.initiative_id = '$initiativeId'");
echo "<table>";
while($row = mysqli_fetch_assoc($query))
{
	$userId = $row["user_id"];
	echo "<tr><td>".$row["user"]."</td>".
	"<td>".$row["email"]."</td>".
	"<td>".$row["telephone"]."</td>".
	"<td>".
	'<a class="remove ml10" href="javascript:removeMember(\''.$userId.'\')" title="Remove">
		<button type="button" class="btn btn-default btn-xs">
		  <span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Remove
		</button>
	</a>'.
	"</td></tr>";
}
echo "</table>";
?>