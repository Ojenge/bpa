<link rel="stylesheet" href="css/shapes.css" media="all">
<?php 
//include("../lab/header.html");
include("../reports/scores-functions.2.0.php");

$strategyHouse = '<table class="">'
.'<tr><td colspan="9" class="text-center"><h3>KDIC Strategy House</h3></td></tr>'
.'<tr>'
    .'<td rowspan="3" class="text-white">left span</td>'
    .'<td colspan="7" class="houseRoof"></td>'
    .'<td rowspan="3" class="text-white">right span</td>'
.'</tr>'
.'<tr>'
    .'<td class="housePillar" onclick="pillarDetails(1)">'
        .'<table class="text-center table table-borderless">'
            .'<tr>';
            $kraScore = kraScore("1");
            $kraColor = getColor3d($kraScore);
            $strategyHouse .= '<td class="text-center"><br><div style="margin:auto" class="'.$kraColor.'"></div></td>';
            $strategyHouse .='</tr>'
            .'<tr>'
                .'<td>Deposit<br>Insurance</td>'
            .'</tr>'
        .'</table>'
    .'</td>'
    .'<td>&nbsp;</td>'
    .'<td class="housePillar" onclick="pillarDetails(2)">'
        .'<table class="text-center table table-borderless">'
            .'<tr>';
            $kraScore = kraScore("2");
            $kraColor = getColor3d($kraScore);
            $strategyHouse .= '<td class="text-center"><br><div style="margin:auto" class="'.$kraColor.'"></div></td>';
            $strategyHouse .='</tr>'
            .'<tr>'
                .'<td>Risk<br>Minimisation</td>'
            .'</tr>'
        .'</table>'
    .'</td>'
    .'<td>&nbsp;</td>'
    .'<td class="housePillar" onclick="pillarDetails(3)">'
        .'<table class="text-center table table-borderless">'
            .'<tr>';
            $kraScore = kraScore("3");
            $kraColor = getColor3d($kraScore);
            $strategyHouse .= '<td class="text-center"><br><div style="margin:auto" class="'.$kraColor.'"></div></td>';
            $strategyHouse .='</tr>'
            .'<tr>'
                .'<td>Resolution of<br>Problem Banks</td>'
            .'</tr>'
        .'</table>'
    .'</td>'
    .'<td>&nbsp;</td>'
    .'<td class="housePillar" onclick="pillarDetails(4)">'
        .'<table class="text-center table table-borderless">'
            .'<tr>';
            $kraScore = kraScore("4");
            $kraColor = getColor3d($kraScore);
            $strategyHouse .= '<td class="text-center"><br><div style="margin:auto" class="'.$kraColor.'"></div></td>';
            $strategyHouse .='</tr>'
            .'<tr>'
                .'<td>Insitutional<br>Capacity<br>Development</td>'
            .'</tr>'
        .'</table>'
    .'</td>'
.'</tr>'
.'<tr>'
    .'<td colspan="7" class="houseBeam"></td>'
.'</tr>'
.'<tr>'
    .'<td colspan="9" class="houseFoundation">'
        .'<table class=" text-white m-0 p-0 w-75 float-end">'
        .'<tr>'
            .'<td><div style="margin:auto" class="grey3d"></div></td>'
            .'<td>Professionalism</td>'
            .'<td><div style="margin:auto" class="red3d"></div></td>'
            .'<td>Teamwork</td>'
            .'<td><div style="margin:auto" class="yellow3d"></div></td>'
            .'<td>Customer Focus</td>'
        .'</tr>'
        .'<tr>'
            .'<td><div style="margin:auto" class="rounded-circle bg-secondary trafficLightBootstrap"></div></td>'
            .'<td>Integrity</td>'
            .'<td><div style="margin:auto" class="green3d"></div></td>'
            .'<td>Innovativeness</td>'
            .'<td><div style="margin:auto" class="rounded-circle bg-warning trafficLightBootstrap"></div></td>'
            .'<td>Accountability</td>'
        .'</tr>'
        .'</table'
    .'</td>'
.'</tr>'
.'</table>';

echo $strategyHouse;
//echo "<br><br><br><br><br><br>";
echo '<div id="pillarDetails" class="position-absolute" style="top: 650px;"></div>';
?>

<script>
require([
    "dojo/dom",
    "dojo/request",
    "dojo/domReady!"
], function(dom, request) 
{
pillarDetails = function(pillarId) 
{
    request.post("reports/scores-functions.2.0.php", {
        data: { pillarId: pillarId },
        handleAs: "json"
    }).then(
        function(objData) 
        {
            var objCount = 0;
            var objList = '<table class="table">';
            var objList = objList + '<tr><th>Linked Objectives</th><th>Score</th></tr>';
            var objScore = "";
            var objId = "";
            while(objCount < objData.length)
            {
                objId = objData[objCount].id;
                objList = objList + "<tr><td>"+ objData[objCount].name + "</td><td>" + objData[objCount].score + "</td></tr>";
                objCount++;
            }
            
            dom.byId("pillarDetails").innerHTML = objList + "</table>";
        }
    );
}
});
</script>