<style type="text/css">
table.reportTable
{
	font-size: 13px; 
	border-collapse: collapse; 
	border-top: 1px solid #9baff1; 
	border-bottom: 1px solid #9baff1;
}
table.reportTable th
{
	padding: 3px; 
	border-right: 1px solid #aabcfe; 
	border-left: 1px solid #aabcfe; 
	border-top: 1px solid #aabcfe;
	font-weight:bold;
}
table.reportTable td
{
	padding: 3px; 
	border-right: 1px solid #aabcfe; 
	border-left: 1px solid #aabcfe; 
	border-top: 1px solid #aabcfe;
}
.pdfIcon {
  background-image: url(images/icons/pdfIcon16.png);
  background-repeat: no-repeat;
  width: 16px;
  height: 16px;
  text-align: center;
}
</style>

<script>
var dbReport = "false";
//var reportType;
//var dataOrg; 
/*= [{"id":1,"Organization":"Automation Department"},
			   {"id":2,"Organization":"BSEA",Target:"100"},
			   {"id":3,"Organization":"BSEA",Target:"100"}];*/
require([
//"dojo/store/Memory",
//"dojo/data/ObjectStore",
//"dijit/registry",
//"dojo/request",
//"dojo/parser",
//"dojo/aspect",
//"dojo/dom",
//"dojo/json",
//"dijit/form/Button",
//"dojox/json/query",
//"dojox/layout/ContentPane",
"dojo/domReady!"
], function()
{
	if(dijit.byId("newCustomReportDialog")) dijit.byId("newCustomReportDialog").destroyRecursive();
	if(dijit.byId("newReportDialog")) dijit.byId("newReportDialog").destroyRecursive();
	if(dijit.byId("newInitiativeReportDialog")) dijit.byId("newInitiativeReportDialog").destroyRecursive();
	if(dijit.byId("newCascadeReportDialog")) dijit.byId("newCascadeReportDialog").destroyRecursive();
	if(dijit.byId("selectObjectDialog")) dijit.byId("selectObjectDialog").destroyRecursive();
})

</script>
<script type="text/javascript" src="js/report.js"></script>
<script type="text/javascript" src="js/dndNoMeasure.js"></script>
<!--<div id="iconsMenu" align="right">
<button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:toEmail, iconClass: 'dijitIconMail', showLabel: false" type="submit">Email</button>
<button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:toPrint, iconClass: 'dijitIconPrint', showLabel: false" type="submit">Print</button>
<button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:toPrint, iconClass: 'dijitIconBookmark', showLabel: false" type="submit">Bookmark</button>
<button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:toPDF, iconClass: 'pdfIcon', showLabel: false" type="submit">To PDF</button>
</div>-->

<div id="displayReport"></div>

<div class="dijitHidden">
    <div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New Report'" id="newReportDialog">
        <table>
        <tr>
        <td><strong>Select Report Type</strong></td>
        <!--<td><strong>Custom Reports</strong></td>-->
        </tr>
        <tr>
        <!--<td><a href="#" onClick="summaryReport();">Scorecard Summary Report</a></td>-->
        <td><a href="#" onClick="customReport();">Custom Report</a></td>
        </tr>
        <tr>
        <td><a href="#" onClick="cascadeReport();">Cascading Report</a></td>
        </tr>
       <!-- <tr>
        <td><a href="#" onClick="linksReport();">Scorecard Links/Relationship Report</a></td>
        </tr>-->
        <tr>
        <td><a href="#" onClick="initiativeReport();">Initiative Report</a></td>
        </tr>
        <tr>
            <td colspan="2" align="right">
                <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:hideReportAddDialog" type="submit">Cancel</button>
            </td>
        </tr>
        </table>
    </div>
</div>

<div class="dijitHidden">
    <div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New Custom Report'" id="newCustomReportDialog">
        <table>
        <tr> 
            <td id="tdRedReportName">Report Name:<input type="text"  id="redReportName" data-dojo-type="dijit/form/TextBox" /></td>
        </tr>
        <tr>
        <td>
        	<button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:showSelectObjectDialog" type="submit">Select Scorecard Object</button></td>
        </tr>
        <tr><td><div id="selectedObjects"></div><div id="selectedObjectsIds" style="display:none"></div></td></tr>
        <tr><td>Select Columns to Show:</td></tr>
        <tr><td>
            <div id="columnsToShow">
            	<table>
                <tr>
                <!--<td><input type="checkbox" id="columnId" data-dojo-type="dijit/form/CheckBox"/></td><td>Id</td>-->
                
                <td><input type="checkbox" id="columnOrg" data-dojo-type="dijit/form/CheckBox"/></td><td>Organization</td>
                <td><input type="checkbox" id="columnOrgScore" data-dojo-type="dijit/form/CheckBox"/></td><td>Organization Score</td>
                <td><input type="checkbox" id="columnPersp" data-dojo-type="dijit/form/CheckBox"/></td><td>Perspective</td>
                <td><input type="checkbox" id="columnPerspScore" data-dojo-type="dijit/form/CheckBox"/></td><td>Perspective Score</td>
                <td><input type="checkbox" id="columnObj" data-dojo-type="dijit/form/CheckBox"/></td><td>Objective</td>
                <td><input type="checkbox" id="columnObjScore" data-dojo-type="dijit/form/CheckBox"/></td><td>Objective Score</td>
                <td><input type="checkbox" id="columnKpi" data-dojo-type="dijit/form/CheckBox" checked="checked"/></td><td>Measure</td>
                </tr><tr>
                <td><input type="checkbox" id="columnOwner" data-dojo-type="dijit/form/CheckBox"/></td><td>Owner</td>
                <td><input type="checkbox" id="columnUpdater" data-dojo-type="dijit/form/CheckBox"/></td><td>Updater</td>
                <td><input type="checkbox" id="columnScore" data-dojo-type="dijit/form/CheckBox"/></td><td>Score</td>
                <td><input type="checkbox" id="columnActual" data-dojo-type="dijit/form/CheckBox" checked="checked"/></td><td>Actual</td>
                <td><input type="checkbox" id="columnTarget" data-dojo-type="dijit/form/CheckBox"/></td><td>Target</td>
                <td><input type="checkbox" id="columnVariance" data-dojo-type="dijit/form/CheckBox"/></td><td>Variance</td>
                <td><input type="checkbox" id="columnPercentVariance" data-dojo-type="dijit/form/CheckBox"/></td><td>% Variance</td>
                </tr>
                </table>
                <table>
                    <tr><td colspan="6">Filters</td></tr>
                    <tr>
                    <td><input type="radio" name="filter" id="redFilter" data-dojo-type="dijit/form/RadioButton" /></td>
                    <td>Show Red Items Only</td>
                    <td><input type="radio" name="filter" id="greyFilter" data-dojo-type="dijit/form/RadioButton"/></td>
                    <td>Show Grey Items Only</td>
                    <td><input type="radio" name="filter" id="greenFilter" data-dojo-type="dijit/form/RadioButton"/></td>
                    <td>Show Green Items Only</td>
                    </tr>
                </table>
                <table>
                    <tr><td colspan="4">Initiatives</td></tr>
                    <tr>
                    <td><input type="checkbox" name="initFilter" id="initiativeFilter" data-dojo-type="dijit/form/CheckBox" /></td>
                    <td>Show Initiatives</td>
                    <td><input type="checkbox" name="initFilter" id="initiativeGroup" data-dojo-type="dijit/form/CheckBox"/></td>
                    <td>Group by Initiative</td>
                    </tr>
                </table>
            </div>
        </td></tr>
        </table>
        <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:hideRedReportDialog" type="submit">Cancel</button>
        <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:saveReport" type="submit">Finish</button>
    </div>
</div>

<div class="dijitHidden">
    <div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New Report'" id="newInitiativeReportDialog">
        <table>
        <tr> 
   <td id="tdInitiativeReportName">Report Name:<input type="text" id="initiativeReportName" data-dojo-type="dijit/form/TextBox" /></td>
        </tr>
        <tr>
        <td>
        	<button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:showSelectObjectDialog" type="submit">Select Scorecard Object</button></td>
        </tr>
        <tr><td><div id="selectedInitObjects"></div><div id="selectedInitObjectsIds" style="display:none"></div></td></tr>
        <tr><td>Select Columns to Show:</td></tr>
        <tr><td>
            <div id="initColumnsToShow">
            	<table>
                <tr>
                <!--<td><input type="checkbox" id="initId" data-dojo-type="dijit/form/CheckBox"/></td><td>Id</td>-->
                
                <td><input type="checkbox" id="initSponsor" data-dojo-type="dijit/form/CheckBox"/></td><td>Sponsor</td>
                <td><input type="checkbox" id="initOwner" data-dojo-type="dijit/form/CheckBox"/></td><td>Owner/Project Manager</td>
                <td><input type="checkbox" id="initBudget" data-dojo-type="dijit/form/CheckBox" checked="checked"/></td><td>Budget</td>
                <td><input type="checkbox" id="initCost" data-dojo-type="dijit/form/CheckBox" checked="checked"/></td><td>Cost So Far</td>
                <td><input type="checkbox" id="initStart" data-dojo-type="dijit/form/CheckBox" checked="checked"/></td><td>Start Date</td>
                <td><input type="checkbox" id="initDue" data-dojo-type="dijit/form/CheckBox" checked="checked"/></td><td>Due Date</td>
                <td><input type="checkbox" id="initComplete" data-dojo-type="dijit/form/CheckBox"/></td><td>Completion Date</td>
                </tr><tr>
                <td><input type="checkbox" id="initDeliverable" data-dojo-type="dijit/form/CheckBox"/></td><td>Deliverable</td>
                <td><input type="checkbox" id="initDeliverableStatus" data-dojo-type="dijit/form/CheckBox"/></td><td>Deliverable Status</td>
                <td><input type="checkbox" id="initParent" data-dojo-type="dijit/form/CheckBox"/></td><td>Parent Initiative</td>
                </tr>
                </table>
                <table>
                    <tr><td colspan="6">Filters</td></tr>
                    <tr>
                    <td><input type="radio" name="filter" id="initRedFilter" data-dojo-type="dijit/form/RadioButton" /></td>
                    <td>Show Red Items Only</td>
                    <td><input type="radio" name="filter" id="initGreyFilter" data-dojo-type="dijit/form/RadioButton"/></td>
                    <td>Show Yellow Items Only</td>
                    <td><input type="radio" name="filter" id="initGreenFilter" data-dojo-type="dijit/form/RadioButton"/></td>
                    <td>Show Green Items Only</td>
                    </tr>
                </table>
            </div>
        </td></tr>
        </table>
        <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:hideInitiativeReportDialog" type="submit">Cancel</button>
        <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:saveInitiativeReport" type="submit">Finish</button>
    </div>
</div>


<div class="dijitHidden">
    <div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New Cascade Report'" id="newCascadeReportDialog">
        <table>
        <tr> 
         <td id="tdCascadeReportName">Report Name:<input type="text"  id="cascadeReportName" data-dojo-type="dijit/form/TextBox" /></td>
        </tr>
        <tr>
        <td>
        	<button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:showSelectObjectDialog" type="submit">Select Scorecard Object</button></td>
        </tr>
        <tr><td><div id="selectedCascadeObjects"></div><div id="selectedCascadeObjectsIds" style="display:none"></div></td></tr>
        </table>
        <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:hideCascadeReportDialog" type="submit">Cancel</button>
        <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:saveCascadeReport" type="submit">Finish</button>
    </div>
</div>

<div class="dijitHidden">
<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'Select Scorecard Object'" id="selectObjectDialog" style="font-size:11px;">
<table>
    <tr>
    <td valign="top">
    <table><tr>
        <td valign="top" style="border-top:3px solid #00F;">
            <div id="organizationsReport" style="width:250px; height:200px; vertical-align:top;"></div></td>
        <td style="border-top:3px solid #00F;"><div id="perspectivesReport" style="width:250px; height:200px; top:0px;"></div></td>
        <td style="border-top:3px solid #00F;"><div id="objectivesReport" style="width:250px; height:200px; top:0px;"></div></td>
        <td style="border-top:3px solid #00F;"><div id="measuresReport" style="width:250px; height:200px; top:0px;"></div></td></tr>
    </table>
    </td>
    </tr>
        <tr>
        <td>
        <table><tr>
            <td><div id="gridCopyReport" style="height:100px; width:600px; border:1px solid #00F;"></div></td>
            </tr>
            </table>
        </td>
        </tr>
        <tr><td><div id="droppedItems"></div></td></tr>
        </table>
        <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:selectScorecardItems" type="submit">Finish</button>
   </div>
</div>