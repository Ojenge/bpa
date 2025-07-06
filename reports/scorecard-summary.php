<?php
include("../lab/header.html");
include("scores-functions.2.0.php");

@$orgId = "org2";
@$globalDate = "2025-07-06";

echo "<br>Scorecard summary report for ".$orgId." <br> Date: ".$globalDate."<br>";

// Organization has Perspectives
$perspCount = count(getPerspectives($orgId));//Equal to table row numbers
$perspectives = getPerspectives($orgId);
$table = "<table class='table table-sm'>";
/*$table .= "<thead class='thead-dark'>";
$table .= "<tr>";
$table .= "<th scope='col'>Perspective</th>";
//$table .= "<th scope='col'>Score</th>";
$table .= "<th scope='col'>";
    $titleObjectives = "<table><tr>";
    $titleObjectives .= "<th scope='col' class='col-1'>Objective</th>";
    $titleObjectives .= "<th scope='col' class='col-2'>Measures</th>";
    $titleObjectives .= "<th scope='col' class='col-2'>Initiatives</th>";
    $titleObjectives .= "</tr></table";
$table .= $titleObjectives;
$table .= "</th>";
$table .= "</tr>";
$table .= "</thead>";*/

for($i = 0; $i < $perspCount; $i++)
{
    if($i % 2 == 0)
    {//If even row then add class for background color
       // $table .= "<tr style='background-color: rgba(150, 150, 150, 0.2) !important;'>";
        //$table .= "<tr class='bg-gray-300'>";//Expected this to work but it doesn't so hacked as above.
    }
    else
    {//If odd row then no class
        //$table .= "<tr style='background-color: rgba(150, 150, 150, 0.2) !important;'>";
        //$table .= "<tr>";
    }
    $table .= "<tr class='bg-info'><td class='border-bottom-0'>".$perspectives[$i]["name"]."</td></tr>";
    $perspScore = orgPerspKpiScore($perspectives[$i]["id"]);//this isn't returning the score.
    //$table .= "<td class='border-bottom-0 bg-danger'>score".$perspScore."</td>";
    $objectiveTable = "<table class='table table-sm m-0 p-0 table-bordered'>";
    $objectiveTable .= "<thead class=''>";
    $objectiveTable .= "<tr>";
    $objectiveTable .= "<th scope='col' class='col-3' colspan='2'>Objective</th>";
    $objectiveTable .= "<th scope='col' class='col-3'>Measure</th>";
    $objectiveTable .= "<th scope='col' class='col-1'>Target</th>";
    $objectiveTable .= "<th scope='col' class='col-1' colspan='2'>Actual</th>";
    $objectiveTable .= "<th scope='col' class='col-3'>Initiatives</th>";
    $objectiveTable .= "</tr>";
    $objectiveTable .= "</thead>";

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
                $objectiveTable .= "<td rowspan=".$rowSpan."><div class='yellowLight bg-warning'></div></td></tr>";
                $objectiveScore = orgObjKpiScore($objectives[$j]["id"]);
                //$objectiveTable .= '<td class="col"><div class="yellowLight bg-warning"></div></td>';
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
                        .'<td class="">Target</td>'
                        .'<td class="">Actual</td>'
                        .'<td class=""><div class="yellowLight bg-success"></div></td>';
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
        
        $objectiveTable .= "</table>";
    $table .= '<tr><td class="border-bottom-0">'.$objectiveTable."</td></tr>";
}
$table .= "</table>";
echo $table;
?>