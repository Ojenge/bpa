<?php
include_once("../config/config_mysqli.php");
//$objectParent =$_POST['parent'] ;
$objectId = $_POST['id'];
$weight = $_POST['weight'];

// Validate and sanitize weight input
if (!is_numeric($weight)) {
    echo "0";
    exit;
}

$weight = floatval($weight) / 100;

$objectType = substr($objectId, 0, 3);

switch($objectType)
{
	case "kpi":
	{
		$getSimilarKpis = mysqli_query($connect, "SELECT name, tags FROM measure WHERE id = '$objectId'");
		$getSimilarKpis = mysqli_fetch_assoc($getSimilarKpis);
		$similarKpis = $getSimilarKpis["tags"];
		$similarKpisName = $getSimilarKpis["name"];

		$staffArray = json_decode($similarKpis, true);
		$totalStaff = sizeof($staffArray);

		$count = 0;
		foreach($staffArray as $items)
		{
			$idArray[$count] = $items["value"];
			$count++;
		}
		//file_put_contents("manyWeights.txt", "to save weight($weight) for => $similarKpis from $objectId totalling $totalStaff with the first being $idArray[0]");
		for($i = 0; $i < $totalStaff; $i++)
		{
			//$dualKpiId = $idArray[$i];
			mysqli_query($connect, "UPDATE measure SET weight = '$weight' WHERE owner = '$idArray[$i]' AND name = '$similarKpisName'");
			//file_put_contents("saveWeight.txt", "\n$weight => $idArray[$i] => $similarKpisName", FILE_APPEND);
		}

		$query1 = mysqli_query($connect, "SELECT SUM(weight) AS total, linkedObject FROM measure WHERE linkedObject = (SELECT linkedObject FROM measure WHERE id = '$objectId') GROUP BY linkedObject");
		$row1 = mysqli_fetch_array($query1);
		$total = is_numeric($row1["total"]) ? floatval($row1["total"]) * 100 : 0;

		$linkedObject = $row1["linkedObject"];

		$query2 = mysqli_query($connect, "SELECT SUM(weight) AS total FROM initiative WHERE projectManager = '$linkedObject'");
		$row2 = mysqli_fetch_array($query2);
		$total = $total + (is_numeric($row2["total"]) ? floatval($row2["total"]) * 100 : 0);
		
		echo $total;
		break;	
	}
	case "obj":
	{
		mysqli_query($connect, "UPDATE objective SET weight = '$weight' WHERE id = '$objectId'");

		$query = mysqli_query($connect, "SELECT SUM(weight) AS total FROM objective WHERE linkedObject = (SELECT linkedObject FROM objective WHERE id = '$objectId') GROUP BY linkedObject");

		$row = mysqli_fetch_array($query);

		$total = is_numeric($row["total"]) ? floatval($row["total"]) * 100 : 0;
		
		echo $total;
		break;	
	}
	case "per":
	{
		mysqli_query($connect, "UPDATE perspective SET weight = '$weight' WHERE id = '$objectId'");

		$query = mysqli_query($connect, "SELECT SUM(weight) AS total FROM perspective WHERE parentId = (SELECT parentId FROM perspective WHERE id = '$objectId')");

		$row = mysqli_fetch_array($query);

		$total = is_numeric($row["total"]) ? floatval($row["total"]) * 100 : 0;
		
		echo $total;
		break;	
	}
	case "org":
	{
		mysqli_query($connect, "UPDATE organization SET weight = '$weight' WHERE id = '$objectId'");

		$query = mysqli_query($connect, "SELECT SUM(weight) AS total FROM organization WHERE cascadedfrom = (SELECT cascadedfrom FROM organization WHERE id = '$objectId')");

		$row = mysqli_fetch_array($query);

		$total = is_numeric($row["total"]) ? floatval($row["total"]) * 100 : 0;
		
		echo $total;
		break;	
	}
	default:
	{
		mysqli_query($connect, "UPDATE initiative SET weight = '$weight' WHERE id = '$objectId'");

		$query1 = mysqli_query($connect, "SELECT SUM(weight) AS total, projectManager FROM initiative WHERE projectManager = (SELECT projectManager FROM initiative WHERE id = '$objectId')");
		$row1 = mysqli_fetch_array($query1);
		$total = is_numeric($row1["total"]) ? floatval($row1["total"]) * 100 : 0;

		$projectManager = $row1["projectManager"];

		$query2 = mysqli_query($connect, "SELECT SUM(weight) AS total FROM measure WHERE linkedObject = '$projectManager'");
		$row2 = mysqli_fetch_array($query2);
		$total = $total + (is_numeric($row2["total"]) ? floatval($row2["total"]) * 100 : 0);
		
		echo $total;
		break;	
	}
}
?>