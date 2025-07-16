<?php
//include("../lab/header.html");
include("scores-functions.2.0.php");

@$orgId = $_POST['orgId'];
@$globalDate = "2025-07-06";

$organization = getOrganization($orgId);
echo "<br>Scorecard Summary for ".$organization["name"]."<br>";

// Organization has Perspectives
$perspCount = count(getPerspectives($orgId));//Equal to table row numbers
$perspectives = getPerspectives($orgId);

$table = "<table class='table table-sm table-condensed'>";
$table .= "<thead class='bg-secondary text-white'>"; 
$table .= "</thead>"; 
$table .= "<tbody>";
$table .= "<tr><td>";

$table .= "</td></tr>";
for($i = 0; $i < $perspCount; $i++)
{
    $perspScore = perspObjKpiScore($perspectives[$i]["id"]);
    $perspColor = getColor($perspScore);
    $rowId = $perspectives[$i]["id"];
    $rowIdRef = "#".$rowId;
   
    if($perspColor == "bg-success")
        $table .= "<tr class='table-primary' ><td class='border-bottom-0'><div class='float-start green3d'></div>&nbsp;".$perspectives[$i]["name"].$perspectives[$i]["icon"]."</td></tr>";
    elseif($perspColor == "bg-warning")
        $table .= "<tr class='table-primary'><td class='border-bottom-0'><div class='float-start yellow3d'></div>&nbsp;".$perspectives[$i]["name"].$perspectives[$i]["icon"]."</td></tr>";
    elseif($perspColor == "bg-danger")
        $table .= "<tr class='table-primary'><td class='border-bottom-0'><div class='float-start red3d'></div>&nbsp;".$perspectives[$i]["name"].$perspectives[$i]["icon"]."</td></tr>";
    else
    {
        $table .= "<tr class='table-primary'>";
        $table .= "<td class='border-bottom-0'>";
        $table .= '<button class="float-start btn btn-sm btn-link" type="button" data-bs-toggle="collapse" data-bs-target="'.$rowIdRef.'" aria-expanded="true" aria-controls="'.$rowId.'">
        <i class="bi bi-chevron-expand fs-6"></i>
                        </button>&nbsp;';
        //$table .= "</td><td>";
        $table .= "<div class='float-start grey3d'></div>&nbsp;";
        $table .= $perspectives[$i]["name"];
        $table .= $perspectives[$i]["icon"];
        $table .= "</td></tr>";
    }
    //$table .= "<tr style='background-color: rgba(150, 150, 150, 0.2) !important;'><td>".$perspectives[$i]["name"]."</td></tr>";
    //$table .= "<tr class='bg-gray-300'>".$perspectives[$i]["name"]."</td></tr>";//Expected this to work but it doesn't so hacked as above.

    $objectiveTable = "<table id='".$rowId."' class='table table-sm m-0 p-0 table-bordered collapse show'>";
    $objectiveTable .= "<thead class=''>";
    $objectiveTable .= "<tr>";
    $objectiveTable .= "<th scope='col' class='col-2' colspan='2'>Objective</th>";
    $objectiveTable .= "<th scope='col' class='col-2'>Measure</th>";
    $objectiveTable .= "<th scope='col' class='col-1'>Target</th>";
    $objectiveTable .= "<th scope='col' class='col-1' colspan='2'>Actual</th>";
    $objectiveTable .= "<th scope='col' class='col-3'>Initiatives</th>";
    $objectiveTable .= "</tr>";
    $objectiveTable .= "</thead>";
    $objectiveTable .= "<tbody>";

        $objectives = getObjectives($perspectives[$i]["id"]);
        $objCount = count($objectives);
        if($objCount == 0)
        {
            $objectiveTable .= "<tr><td>No objectives assigned</td></tr>";
        }
        else
        {
            for($j = 0; $j < $objCount; $j++)
            {
                //Add measures and initiatives to the objective
                $kpis = getMeasures($objectives[$j]["id"]);
                $kpiCount = count($kpis);
                $rowSpan = $kpiCount + 1;
                //$objectiveTable .= '<tr><td rowspan='.$rowSpan.'>'.$objectives[$j]["name"]." (".$kpiCount.")</td></tr>";
                $objectiveTable .= '<tr><td rowspan='.$rowSpan.'>'.$objectives[$j]["name"]."</td>";
                
                $objectiveScore = getObjScore($objectives[$j]["id"]);
                $objectiveColor = getColor($objectiveScore);
                if($objectiveColor == "bg-success")//bg-success bg-warning bg-danger table-secondary
                $objectiveTable .= "<td rowspan=".$rowSpan."><div class='green3d'></div></td></tr>";
                else if($objectiveColor == "bg-warning")
                $objectiveTable .= "<td rowspan=".$rowSpan."><div class='yellow3d'></div></td></tr>";
                else if($objectiveColor == "bg-danger")
                $objectiveTable .= "<td rowspan=".$rowSpan."><div class='red3d'></div></td></tr>";
                else
                $objectiveTable .= "<td rowspan=".$rowSpan."><div class='grey3d'></div></td></tr>";
                
                $initiatives = getInitiatives($objectives[$j]["id"], $globalDate);
                
                if($kpiCount > 0)
                {
                    for($k = 0; $k < $kpiCount; $k++)
                    {
                        if($k % 2 == 0)
                        {
                            //$objectiveTable .= '<tr class="bg-danger">';//didn't work as expected
                            $objectiveTable .= '<tr>';
                        }
                        else
                        $objectiveTable .= '<tr>';

                        $objectiveTable .= '<td class="">'.$kpis[$k]["name"]."</td>"
                        .'<td class="">'.$kpis[$k]["green"].'</td>'
                        .'<td class="">'.$kpis[$k]["actual"].'</td>';

                        $kpiScore = getKpiScore($kpis[$k]["id"]);
                        $kpiColor = getColor($kpiScore);
                        if($kpiColor == "bg-success")//bg-success bg-warning bg-danger table-secondary
                        $objectiveTable .= '<td class=""><div class="green3d"></div></td>';
                        else if($kpiColor == "bg-warning")
                        $objectiveTable .= '<td class=""><div class="yellow3d"></div></td>';
                        else if($kpiColor == "bg-danger")
                        $objectiveTable .= '<td class=""><div class="red3d"></div></td>';
                        else
                        $objectiveTable .= '<td class=""><div class="grey3d"></div></td>';

                        if($k == 0)
                        $objectiveTable .= '<td rowspan="'.$kpiCount.'">'.$initiatives.'</td>';//both rowspan and kpiCount work??? rowspan cuts through though
                        
                        $objectiveTable .= "</tr>";
                    }
                }
                else
                {
                    $objectiveTable .= "<tr><td>No KPIs assigned</td><td>-</td><td>-</td></tr>";
                }
            }
        }
        $objectiveTable .= "</tbody>";
        $objectiveTable .= "</table>";
    $table .= '<tr><td class="border-bottom-0">'.$objectiveTable."</td></tr>";
}
$table .= "</tbody>";
$table .= "</table>";
echo $table;
?>