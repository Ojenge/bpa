<?php
include_once("../config/config_mysqli.php");
$id = 0;
$scorecard_query="SELECT organization.id AS orgId, organization.name AS orgName FROM organization";
$scorecard_result=mysqli_query($connect, $scorecard_query);
echo "[";
while($orgRow = mysqli_fetch_assoc($scorecard_result))
{
	$orgId = $orgRow["orgId"];
	$perspectives = mysqli_query($connect, "SELECT perspective.id AS perspId, perspective.name AS perspName FROM perspective WHERE parentId = '$orgId'");
	if(mysqli_num_rows($perspectives) > 0)//organization has perspectives
	{
		while($perspRow = mysqli_fetch_assoc($perspectives))
		{
			$perspId = $perspRow["perspId"];
			$objectives = mysqli_query($connect, "SELECT objective.id AS objId, objective.name AS objName FROM objective WHERE linkedObject = '$perspId'");
			if(mysqli_num_rows($objectives) > 0)//perspective has objectives
			{
				while($objRow = mysqli_fetch_assoc($objectives))
				{
					$objId = $objRow["objId"];
					$measures = mysqli_query($connect, "SELECT measure.id AS kpiId, measure.name AS kpiName 
					FROM measure WHERE linkedObject = '$objId'");
					if(mysqli_num_rows($measures) > 0)// objective has measures
					{
						while($kpiRow = mysqli_fetch_assoc($measures))
						{
							$scorecard_row["id"] = $id;
							$scorecard_row["Organization"] = $orgRow["orgName"];
							$scorecard_row["orgId"] = $orgRow["orgId"];
							$scorecard_row["Perspective"] = $perspRow["perspName"];
							$scorecard_row["perspId"] = $perspRow["perspId"];
							$scorecard_row["Objective"] = $objRow["objName"];
							$scorecard_row["objId"] = $objRow["objId"];
							$scorecard_row["Measure"] = $kpiRow["kpiName"];
							$scorecard_row["kpiId"] = $kpiRow["kpiId"];
							$scorecard_data = json_encode($scorecard_row);
							echo $scorecard_data.",";
							$id++;
						}
					}
					else //objective has no measures
					{
						$scorecard_row4["id"] = $id;
						$scorecard_row4["Organization"] = $orgRow["orgName"];
						$scorecard_row4["orgId"] = $orgRow["orgId"];
						$scorecard_row4["Perspective"] = $perspRow["perspName"];
						$scorecard_row4["perspId"] = $perspRow["perspId"];
						$scorecard_row4["Objective"] = $objRow["objName"];
						$scorecard_row4["objId"] = $objRow["objId"];
						$scorecard_data = json_encode($scorecard_row4);
						echo $scorecard_data.",";
						$id++;
					}
				}
			}
			else //perspective has no objectives
			{
				$scorecard_row3["id"] = $id;
				$scorecard_row3["Organization"] = $orgRow["orgName"];
				$scorecard_row3["orgId"] = $orgRow["orgId"];
				$scorecard_row3["Perspective"] = $perspRow["perspName"];
				$scorecard_row3["perspId"] = $perspRow["perspId"];
				$scorecard_data = json_encode($scorecard_row3);
				echo $scorecard_data.",";
				$id++;
			}
		}
	}
	else//organization has no perspectives but does it have objectives?
	{
		$orgId = $orgRow["orgId"];
		$orgObjectives = mysqli_query($connect, "SELECT objective.id AS objId, objective.name AS objName 
		FROM objective WHERE linkedObject = '$orgId'");
		if(mysqli_num_rows($orgObjectives) > 0)// organization has objectives but no perspectives
		{
			while($orgObjRow = mysqli_fetch_assoc($orgObjectives))
			{	
				$objId = $orgObjRow["objId"];
				$orgObjKpis = mysqli_query($connect, "SELECT measure.id AS kpiId, measure.name AS kpiName 
				FROM measure WHERE linkedObject = '$objId'");
				if(mysqli_num_rows($orgObjKpis) > 0)// organization has objectives and measures but no perspectives
				{
					while($orgObjKpiRow = mysqli_fetch_assoc($orgObjKpis))
					{
						$scorecard_row7["id"] = $id;
						$scorecard_row7["Organization"] = $orgRow["orgName"];
						$scorecard_row7["orgId"] = $orgRow["orgId"];
						$scorecard_row7["Objective"] = $orgObjRow["objName"];
						$scorecard_row7["objId"] = $orgObjRow["objId"];
						$scorecard_row["Measure"] = $orgObjKpiRow["kpiName"];
						$scorecard_row["kpiId"] = $orgObjKpiRow["kpiId"];
						$scorecard_data = json_encode($scorecard_row7);
						echo $scorecard_data.",";
						$id++;
					}
				}
				else //organization has objectives only
				{
					$scorecard_row5["id"] = $id;
					$scorecard_row5["Organization"] = $orgRow["orgName"];
					$scorecard_row5["orgId"] = $orgRow["orgId"];
					$scorecard_row5["Objective"] = $orgObjRow["objName"];
					$scorecard_row5["objId"] = $orgObjRow["objId"];
					$scorecard_data = json_encode($scorecard_row5);
					echo $scorecard_data.",";
					$id++;
				}
			}
		}
		else//organization has no perspectives and objectives
		{
			$orgId = $orgRow["orgId"];
			$orgMeasures = mysqli_query($connect, "SELECT measure.id AS kpiId, measure.name AS kpiName 
			FROM measure WHERE linkedObject = '$orgId'");
			if(mysqli_num_rows($orgObjectives) > 0)// organization has measures but no perspectives and no objectives
			{
				while($orgKpiRow = mysqli_fetch_assoc($orgObjectives))
				{	
					$scorecard_row6["id"] = $id;
					$scorecard_row6["Organization"] = $orgRow["orgName"];
					$scorecard_row6["orgId"] = $orgRow["orgId"];
					$scorecard_row6["Measure"] = $orgKpiRow["kpiName"];
					$scorecard_row6["objId"] = $orgKpiRow["kpiId"];
					$scorecard_data = json_encode($scorecard_row6);
					echo $scorecard_data.",";
					$id++;
				}
			}
			else//organization has no perspectives, objectives or measures
			{
				$scorecard_row2["id"] = $id;
				$scorecard_row2["Organization"] = $orgRow["orgName"];
				$scorecard_row2["orgId"] = $orgRow["orgId"];
				$scorecard_data = json_encode($scorecard_row2);
				echo $scorecard_data.",";
				$id++;
			}
		}
	}
}
echo "{}]";
?>