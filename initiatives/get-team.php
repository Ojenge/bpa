<?php
include_once("../config/config_mysqli.php");

$initiativeId = $_POST["initiativeId"];

$query = mysqli_query($connect, "SELECT DISTINCT uc_users.user_id, uc_users.display_name AS user, uc_users.email AS email FROM uc_users, initiativeteam 
WHERE uc_users.user_id = initiativeteam.user_id AND initiativeteam.initiative_id = '$initiativeId'");
echo "<table>";
while($row = mysqli_fetch_assoc($query))
{
	$userId = $row["user_id"];
	echo "<tr><td>".$row["user"]."</td>".
	"<td>".$row["email"]."</td>".
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