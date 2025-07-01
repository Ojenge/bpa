<?php 
include_once("../config/config_mysqli.php");

@$objectId = $_POST['id'];
$weightUpdateMode = $_POST['id'];
//$objectId = 'kpi40';

//file_put_contents("weights.txt", "objectParent = $objectParent and objectId = $objectId");

$objectType = substr($objectId, 0, 3);
$total = 0;

switch($objectType)
{
	case "kpi":
	{
		$query = mysqli_query($connect, "SELECT id, name, weight, linkedObject FROM measure WHERE linkedObject = (SELECT linkedObject FROM measure WHERE id = '$objectId')");
		echo "<table class='table table-sm'>"
		."<tr><th colspan='2' class='table-secondary'>Measures/KPIs</th</tr>"
		."<tr class='table-light'><td><i>Name</i></td><td><i>Weight</i></td>";
		while($row = mysqli_fetch_array($query))
		{
			$weight = is_numeric($row["weight"]) ? floatval($row["weight"]) * 100 : 0;
			echo "<tr><td>".$row["name"]."</td><td><input name='weights' type='number' min='0' max='100' step='1' style='width:90%' value='".$weight."' onKeyUp='weightsTotal(\"".$row["id"]."\",this.value)' onChange='weightsTotal(\"".$row["id"]."\",this.value)'/></td></tr>";
			$total = $total + (is_numeric($row["weight"]) ? floatval($row["weight"]) : 0);
			$linkedObject = $row["linkedObject"];
		}
		//if($weightUpdateMode == "New")
		
		$queryInitiative = mysqli_query($connect, "SELECT id, name, weight FROM initiative WHERE projectManager = '$linkedObject'");
		echo "<tr><th colspan='2' class='table-secondary'>Initiatives/Activities</th</tr>"
		."<tr class='table-light'><td><i>Name</i></td><td><i>Weight</i></td>";
		while($rowInitiative = mysqli_fetch_array($queryInitiative))
		{
			$weight = is_numeric($rowInitiative["weight"]) ? floatval($rowInitiative["weight"]) * 100 : 0;
			echo "<tr><td>".$rowInitiative["name"]."</td><td><input name='weights' type='number' min='0' max='100' step='1' style='width:90%' value='".$weight."' onKeyUp='weightsTotal(\"".$rowInitiative["id"]."\",this.value)' onChange='weightsTotal(\"".$rowInitiative["id"]."\",this.value)'/></td></tr>";
			$total = $total + (is_numeric($rowInitiative["weight"]) ? floatval($rowInitiative["weight"]) : 0);
		}

		$total = $total * 100; //making it easier for user to deal with larger numbers
		
		echo "<tr><td>Total</td><td><div id='weightsTotal'>".$total."</div></td><tr>";
		echo "</table>";
		break;
	}
	case "obj":
	{
		$query = mysqli_query($connect, "SELECT id, name, weight FROM objective WHERE linkedObject = (SELECT linkedObject FROM objective WHERE id = '$objectId')");
		echo "<table><tr><td>Name</td><td>Weight</td>";
		while($row = mysqli_fetch_array($query))
		{
			$weight = is_numeric($row["weight"]) ? floatval($row["weight"]) * 100 : 0;
			echo "<tr><td>".$row["name"]."</td><td><input name='weights' type='number' min='0' max='100' step='1' style='width:90%' value='".$weight."' onKeyUp='weightsTotal(\"".$row["id"]."\",this.value)' onChange='weightsTotal(\"".$row["id"]."\",this.value)'/></td></tr>";
			$total = $total + (is_numeric($row["weight"]) ? floatval($row["weight"]) : 0);
		}
		//if($weightUpdateMode == "New")
		
		$total = $total * 100; //making it easier for user to deal with larger numbers
		
		echo "<tr><td>Total</td><td><div id='weightsTotal'>".$total."</div></td><tr>";
		echo "</table>";
		break;
	}
	case "per":
	{
		$query = mysqli_query($connect, "SELECT id, name, weight FROM perspective WHERE parentId = (SELECT parentId FROM perspective WHERE id = '$objectId')");
		echo "<table><tr><td>Name</td><td>Weight</td>";
		while($row = mysqli_fetch_array($query))
		{
			$weight = is_numeric($row["weight"]) ? floatval($row["weight"]) * 100 : 0;
			echo "<tr><td>".$row["name"]."</td><td><input name='weights' type='number' min='0' max='100' step='1' style='width:90%' value='".$weight."' onKeyUp='weightsTotal(\"".$row["id"]."\",this.value)' onChange='weightsTotal(\"".$row["id"]."\",this.value)'/></td></tr>";
			$total = $total + (is_numeric($row["weight"]) ? floatval($row["weight"]) : 0);
		}
		//if($weightUpdateMode == "New")
		
		$total = $total * 100; //making it easier for user to deal with larger numbers
		
		echo "<tr><td>Total</td><td><div id='weightsTotal'>".$total."</div></td><tr>";
		echo "</table>";
		break;
	}
	case "org":
	{
		$query = mysqli_query($connect, "SELECT id, name, weight FROM organization WHERE cascadedfrom = (SELECT cascadedfrom FROM organization WHERE id = '$objectId')");
		echo "<table><tr><td>Name</td><td>Weight</td>";
		while($row = mysqli_fetch_array($query))
		{
			$weight = is_numeric($row["weight"]) ? floatval($row["weight"]) * 100 : 0;
			echo "<tr><td>".$row["name"]."</td><td><input name='weights' type='number' min='0' max='100' step='1' style='width:90%' value='".$weight."' onKeyUp='weightsTotal(\"".$row["id"]."\",this.value)' onChange='weightsTotal(\"".$row["id"]."\",this.value)'/></td></tr>";
			$total = $total + (is_numeric($row["weight"]) ? floatval($row["weight"]) : 0);
		}
		//if($weightUpdateMode == "New")
		
		$total = $total * 100; //making it easier for user to deal with larger numbers
		
		echo "<tr><td>Total</td><td><div id='weightsTotal'>".$total."</div></td><tr>";
		echo "</table>";
		break;
	}
}
?>