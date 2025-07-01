<?php
include_once("../analytics/config_mysqli.php");
$cellId = $_POST["cellId"];
//$importId = "2";
$getItem = mysqli_query($connect, "SELECT * FROM import_months WHERE id = '$cellId'") or file_put_contents("deleteError.txt", "Error => ".mysqli_error());

echo "<table class='table-striped table-bordered table-hover'><tr><th>Field</th><th>Value</th></tr>";
while($row = mysqli_fetch_assoc($getItem)) 
{   
	foreach ($row as $col => $val) 
	{
		echo "<tr><td>".$col." </td><td> ".$val."</td></tr>";
	}
}
echo "</table>";
?>