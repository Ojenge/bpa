<?php
//include("../phpJobScheduler/firepjs.php");
//error_reporting(E_ALL);
//ini_set('display_errors', 'On');
require_once("admin/models/config.php");
require_once("menu/menu_functions.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
$userPermission = fetchUserPermissions($loggedInUser->user_id);
$view = "False";$applicationUser = "False";$updater = "False";

$showAdmin = "False";

foreach($userPermission as $id)
{
	//file_put_contents("bpa.txt", "\t\n=> Permission Id: ".$id["permission_id"], FILE_APPEND);
	if($id["permission_id"] == "1") $view = "Viewer";
	if($id["permission_id"] == "2") 
    {
		$view = "Administrator";
		$showAdmin = "True";
	}
    if($id["permission_id"] == "3") $view = "Application";
	if($id["permission_id"] == "3000") $view = "Board";
	if($id["permission_id"] == "9")//Commissioners
	{
		header("Location: Commissioners"); 
	}
	
	if($id["permission_id"] == "31") $showAdmin = "True";
}
if(!empty($_GET))
{
	//Coming from email login; open Measures and Tasks :-)
	?>
		<script type="text/javascript">
			var waitALittle = setTimeout(function(){
			myDataEntry();
			},3000)
		</script>
	<?php
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Accent Analytics</title>
    <link rel="shortcut icon" href="images/favicons/favicon.ico">
	<link rel="stylesheet" href="css/style.css" media="all">
    <link rel="stylesheet" href="css/trafficLights.css" media="all">
	<link rel="stylesheet" href="https://accent-analytics.com/dijit/themes/soria/soria.css" media="all">
    <link rel="stylesheet" href="https://accent-analytics.com/dojox/editor/plugins/resources/css/Save.css"  media="all"/>
    <!--<link rel="stylesheet" href="css/dTuned.css">-->
	<!--<link rel="stylesheet" href="css/navigableDnd.css" media="all">
    <link rel="stylesheet" href="css/dashboardTables.css" media="all">
    <link rel="stylesheet" href="css/mapDetails.css" type="text/css" media="all">-->
    
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://accent-analytics.com/bootstrap/5.0.0/dist/css/bootstrap.min.css" media="all">
    <link rel="stylesheet" href="https://accent-analytics.com/bootstrap_table/1.18.3/bootstrap-table.min.css" media="all">
    <link href="https://accent-analytics.com/bootstrap_fileinput/css/fileinput.min.css" media="screen" rel="stylesheet" type="text/css"/>
    <link href="https://accent-analytics.com/font-awesome-5.15.3/css/all.css" media="all" rel="stylesheet" type="text/css"/>

    <link rel="stylesheet" href="https://accent-analytics.com/virtualSelect/dist/virtual-select.min.css" />
    
</head>
<style>
@import "https://accent-analytics.com/dojox/form/resources/CheckedMultiSelect.css";
@import "https://accent-analytics.com/dojox/widget/Calendar/Calendar.css";
@import "https://accent-analytics.com/dojox/calendar/themes/soria/Calendar.css";
@import "https://accent-analytics.com/dojox/calendar/themes/nihilo/Calendar.css";
@import "https://accent-analytics.com/dojox/layout/resources/ExpandoPane.css";
    /* colors the underlay black instead of white
     * We're using '.claro .dijitDialogUnderlay' as our selector,
     * to match the specificity in claro.css */
    .soria .dijitDialogUnderlay { background:#000;
	display:inline-table}
.pdfIcon {
  background-image: url(images/icons/pdfIcon16.png);
  background-repeat: no-repeat;
  width: 16px;
  height: 16px;
  text-align: center;
}
.vMenu
{
	background:#d9d9d9;
	padding:4px/*padding for top, bottom*/ 7px /*padding for left, right*/;
	text-decoration:none;
	border-bottom:1px solid #eeeeee;
	border-top:1px solid #cccccc;
	border-left:5px solid #333333;
	color:#333333;
}
.vMenu:hover
{
	border-left-color:#0099FF;
	color:#0066FF;
	background:#c4c4c4;
}
.vMenuRed
{
	background:#d9d9d9;
	padding:4px/*padding for top, bottom*/ 7px /*padding for left, right*/;
	text-decoration:none;
	border-bottom:1px solid #eeeeee;
	border-top:1px solid #cccccc;
	border-left:5px solid #333333;
	color:#F00;
}
.vMenuRed:hover
{
	border-left-color:#0099FF;
	color:#0066FF;
	background:#c4c4c4;
}
.menuTable
{
	 margin-top:0px;
	 font-family:'Trebuchet MS', Arial, Helvetica, sans-serif;
	 font-size:14px
}
@media print
{
	.bpaPrint
	{
		display:block !important; 
		overflow: visible !important; 
		height: auto !important;
		clear:both !important;
		overflow-y:visible !important;
	}
}
.cpStyles
{
	background-color:#0CF;
}
</style>
<body class="bpaPrint soria">
<!--
Having a hard time tracking active scorecard or initiative items across the system. Use a div to hold the active id at any point in time here so that the different files can all access it otherwise, seems one has to keep creating divs for ids across the system LTK 04 Apr 2021 1205 hours
***************************************************
Global ID Holder
***************************************************-->
<div id="selectedElement" style="display:none;"></div>
<div id="editSaveDelete" style="display:none;"></div><!--Again, carrying variable around to state whether we are editing, saving or deleting makes it hard to keep tab. This should hopefully make it easier LTK 04 Apr 2021 2045 hours-->

<div id='viewRights' style='display:none;'><?php echo $view; ?></div>
    <!-- Start of content pane for top menu bar-->
        <div data-dojo-type="dijit/MenuBar" data-dojo-props="region: 'top', id: 'topBar'" class="d-print-none">
            <div id="mainMenu" data-dojo-type="dijit/MenuBar">
                <div id="home" data-dojo-type="dijit/MenuBarItem"><img src='images/favicons/accent.png' width="24"/>&nbsp; Home</div>
                <!--<div id="scorecards" data-dojo-type="dijit/MenuBarItem">Scorecards</div>-->
                <div id="scorecards" data-dojo-type="dijit/PopupMenuBarItem">
                    <span>Scorecards</span>
                    <div id="bscMenu" data-dojo-type="dijit/Menu">
                        <div id="bsc" data-dojo-type="dijit/MenuItem">Balanced Scorecard</div>
                        <div id="performanceContract" data-dojo-type="dijit/MenuItem">Performance Contract</div>
                       <!-- <div data-dojo-type="dijit/MenuItem">Advocacy Scorecard</div>
                        <div id="admin3" data-dojo-type="dijit/MenuItem">Admin 3</div>-->
                    </div>
            	</div>
                <div id="initiatives" data-dojo-type="dijit/MenuBarItem">Initiatives</div>

                <!--<div id="dashboards" data-dojo-type="dijit/PopupMenuBarItem">
                    <span>Dashboards</span>
                    <div id="dbMenu" data-dojo-type="dijit/Menu">
                    </div>
            	</div>-->
                <div id="reports" data-dojo-type="dijit/MenuBarItem">Reports</div>
                 <div id="calendarMenu" data-dojo-type="dijit/MenuBarItem">Calendar</div>
                <!--<div id="inboxMenu" data-dojo-type="dijit/MenuBarItem">Inbox</div>-->
                 <?php if($view == "False") {?>
                 <!--<div id="definitionTables" data-dojo-type="dijit/MenuBarItem">Definition Tables</div>-->
                 <?php } ?>
                <!-- Start of Admin sub menu cluster-->
                <div id="administration" data-dojo-type="dijit/PopupMenuBarItem">
                    <span>Settings</span>
                    <div id="adminMenu" data-dojo-type="dijit/Menu">
                        <div id="admin" data-dojo-type="dijit/MenuItem">Admin</div>
                      </div>
            	</div><!-- End of Admin sub menu cluster-->
            <div id="menuSeparator" data-dojo-type="dijit/MenuBarItem" data-dojo-props='disabled:true' style="display:none">|</div>
            <div id="dynamicMenu" data-dojo-type="dijit/MenuBarItem"></div>

            <div id="logOut" style="float:right" data-dojo-type="dijit/MenuBarItem">Log Out</div>
            <div id="displayName" style="float:right" data-dojo-type="dijit/MenuBarItem"><?php echo $loggedInUser->displayname; ?></div>
			<div id="userIdJs" style="display:none;"><?php echo "ind".$loggedInUser->user_id; ?></div><!--Static - doesn't change-->
            <div id="userIdInd" style="display:none;"><?php echo "ind".$loggedInUser->user_id;?></div><!--Dynamic - can be changed-->
            <div id="Periods" style="float:right" data-dojo-type="dijit/PopupMenuBarItem"  data-dojo-id="dojoDisplayDate">
                <span><?php echo date("M-Y");?></span>
                    <div id="periodsMenu" data-dojo-type="dijit/Menu">

                       <div id="day" data-dojo-type="dijit/MenuBarItem">Day
                       		 <div data-dojo-type="dijit/TooltipDialog" id="dayDialog">
                                 <div dojoType="dojox/widget/DailyCalendar" data-dojo-props="isDisabledDate:dojo.date.locale.isWeekend" id="calDayOnly"  style="background-color: white;">
                                    <script type="dojo/aspect" data-dojo-advice="after" data-dojo-method="onValueSelected" data-dojo-args="value">
                                    	closeDay();
                                      //alert(value);
                                    </script>
                                </div>
                            </div>
                       </div>

                       <!--<div id="week" data-dojo-type="dijit/MenuBarItem">Week</div>-->

                       <div id="month" data-dojo-type="dijit/MenuBarItem">Month
                        <div data-dojo-type="dijit/TooltipDialog" id="monthDialog">
                         <div dojoType="dojox/widget/MonthlyCalendar" id="calMonthOnly" style="background-color: white;">
                            <script type="dojo/aspect" data-dojo-advice="after" data-dojo-method="onValueSelected" data-dojo-args="value">
                                closeMonth();
                              //alert(value);
                            </script>
                        </div>
                        </div>
                       </div>

             |      <div id="quarter" data-dojo-type="dijit/MenuBarItem">Quarter
                        <div data-dojo-type="dijit/TooltipDialog" id="quarterDialog">
                         <div dojoType="dojox/widget/QuarterlyCalendarLTK" id="calQuarterOnly" style="background-color: white;">
							<script type="dojo/aspect" data-dojo-advice="after" data-dojo-method="onValueSelected" data-dojo-args="value">
                                closeQuarter();
                              //alert(value);
                            </script>
                        </div>
                        </div>
                       </div>

                       <div id="halfYear" data-dojo-type="dijit/MenuBarItem">1/2 Year
                       <div data-dojo-type="dijit/TooltipDialog" id="halfYearDialog">
                         <div dojoType="dojox/widget/HalfYearlyCalendarLTK" id="calHalfYearOnly" style="background-color: white;">
							<script type="dojo/aspect" data-dojo-advice="after" data-dojo-method="onValueSelected" data-dojo-args="value">
                                closeHalfYear();
                              //alert(value);
                            </script>
                        </div>
                        </div>
                       </div>

                       <div id="year" data-dojo-type="dijit/MenuBarItem">Year
                           	<div data-dojo-type="dijit/TooltipDialog" id="yearDialog">
                                 <div dojoType="dojox/widget/YearlyCalendar" id="calYearOnly" style="background-color: white;">
                                    <script type="dojo/aspect" data-dojo-advice="after" data-dojo-method="onValueSelected" data-dojo-args="value">
                                    	closeYear();
                                      //alert(value);
                                    </script>
                                </div>
                       		</div>
                       </div>

                </div>
            </div><!-- End of Date sub menu cluster-->
 <div id="menuSeparator2" data-dojo-type="dijit/MenuBarItem" data-dojo-props='disabled:true' style="float:right">|</div>
<!-- <div id="bookMarkIcon" style="float:right;" data-dojo-type="dijit/MenuBarItem" onClick="getBookmarkName">
 	<img src="images/icons/bookMarkIcon16_2.png" title="Bookmark">
 </div>
 <div id="pdfIcon" style="float:right" data-dojo-type="dijit/MenuBarItem" data-dojo-props='disabled:true' onClick="toPDF">
 	<img src="images/icons/pdfIcon16.png" title="To PDF">
 </div>-->
 <div id="printerIcon" style="float:right" data-dojo-type="dijit/MenuBarItem" onClick="toPrint">
 	<img src="images/icons/printerIcon16_2.png" title="Print">
 </div>
  <!--<div id="mailIcon" style="float:right" data-dojo-type="dijit/MenuBarItem" data-dojo-props='disabled:true' onClick="toEmail">
 	<img src="images/icons/mailIcon16.png" title="Email">
 </div>-->
            </div>
        </div>
    	<!-- End of content pane for top menu bar-->

    <!-- Start of main application layout-->
	<div id="appLayout" class="demoLayout"	data-dojo-type="dijit/layout/BorderContainer" data-dojo-props="liveSplitters:false">

        <!-- Start of left pane-->
        <div id="leftCol" class="d-print-none edgePanel" style="font-size:12px;"
            data-dojo-type="dojox/layout/ExpandoPane"
            data-dojo-props="region: 'left', splitter: true">
            
            <div id="tabContainer" style="font-size:12px;"
            data-dojo-type="dijit/layout/TabContainer"
            data-dojo-props="region: 'left'"  tabPosition="bottom"  tabStrip="true">
        	<div id="myTab" data-dojo-type='dijit/layout/ContentPane'>
            <div id="tree" style="display:none;">  
            <button id="collapse" data-dojo-type="dijit/form/Button" data-dojo-props="onClick:collapseTree" type="button">Collapse Tree</button>
            <button id="expand" data-dojo-type="dijit/form/Button" data-dojo-props="onClick:expandTree" type="button">Expand Tree</button>

            </div>
            <div id="userSettings" style="display:none;">
            	<a href="#" onClick="userSettings();">Change User Details</a><br>
             <?php if($showAdmin == "True") {?>
                <a href="#" onClick="adminConfiguration();">Admin Configuration</a><br>
                <a href="#" onClick="adminUsers();">Admin Users</a><br>
                <a href="#" onClick="adminUpdaters();">Admin Updaters</a><br>
                <a href="#" onClick="adminPermissions();">Module Permissions</a><br>
                <!-- <a href="#" onClick="adminPages();">Admin Pages</a>-->
                <a href="#" onClick="scheduler();">Mail/Alerts Scheduler</a><br>
                <a href="#" onClick="notificationAdmin();">Notification Management (New)</a><br>
                <a href="#" onClick="recomputeScores();">Updated Scoring</a><br>
                <a href="#" onClick="restoreScores();">Original Scoring</a><br>
                <?php } ?>
            </div>

            <div id="definitionTables" style="display:none;">
			<?php if($view == "Administrator" || $view == "Application") {?>
            	<a href="#" onClick="objComm();">Objective Commentaries</a><br>
                <a href="#" onClick="kpiDesign();">Measure Design</a><br>
                <a href="#" onClick="kpiDef();">Measure Definition Dictionary</a><br>
                <!--<a href="#" onClick="initDef();">Initiative Definitions</a><br>-->
            <?php }?>
            </div>
            <div id="homeLinks" style="display:block;">
            <?php if($view == "Board") {?>
            <!--displayBookmark = function(itemType, menuType, itemId, itemName) Line 297-->
            	<a href="#" onClick="displayBookmark('report','Reports','19', 'Scorecard Summary');">Scorecard Summary</a><br>
                <a href="#" onClick="displayAdvocacy();">Advocacy Scorecard</a><br>
                <a href="#" onClick="displayStratMap();">Strategy Map</a><br>
                <a href="#" onClick="displayOrgStructure();">Organizational Structure</a><br>
                <!--<a href="#" onClick="initDef();">Initiative Definitions</a><br>-->
            <?php } else {?>
            	
                <!--<a href="#" onClick="initDef();">Initiative Definitions</a><br>
                -->

                <?php echo menu($userPermission); }?>
                <br>
                <div id='dbProjectsKey'></div>
            </div> 
        </div>

        </div>
        </div>
        <!-- End of left pane-->
	</div>
    <!-- End of main application layout-->
    <div class="dijitHidden">
        <div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New Individual'" id="newIndividualDialog">
        	<table>
            	<tr>
                <td>
                	Individual Name
                </td>
                <td><input type='text' style='width:90%' id='indName'/></td>
                </tr>
                <tr>
                    <td colspan="2">
                    	<form method="post" action="upload/UploadFile.php" id="myForm" enctype="multipart/form-data" >
                        	<fieldset>
                                <legend>Upload Photo</legend>
                                <input name="uploadedfile" multiple type="file" id="uploader" dojoType="dojox/form/Uploader" label="Select Photo" >
                                <!--<input type="text" name="album" value="Summer Vacation" />
                                <input type="text" name="year" value="2011" />-->
                                <input type="hidden" name="hiddenIndId" id="hiddenIndId"/>
                                <input type="submit" label="Upload" dojoType="dijit/form/Button" />
                                <div id="files" dojoType="dojox/form/uploader/FileList" uploaderId="uploader"></div>
                            </fieldset>
                        </form>
                	</td>
                </td>
                </tr>
                <tr>
                    <td>
                    <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:hideIndividualAddDialog" type="submit">Finish</button>
                    </td>
                </tr>
            </table>
   		</div>
	</div>

<div class="dijitHidden">
	<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New Linked Measure'" id="linkedMeasureDialog">
    	<table>
        	<tr>
            	<td>Select Measure</td><td><input type='text' style='width:90%' id='kpiList'></td>
            </tr>
            <tr>
            	<td>
        		<button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:hideLinkedMeasureDialog" type="submit">Finish</button>
                <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:cancelLinkedMeasureDialog" type="submit">Cancel</button>
        		</td>
        	</tr>
        </table>
    </div>
</div>

<div class="dijitHidden">
	<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'Edit Weights'" id="weightsDialog">
    	<table>
            <tr><td><div id="weightsContent"></div></td></tr>
            <tr>
            	<td>
        		<button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:saveWeightsDialog" type="submit">Save</button>
                <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:cancelWeightsDialog" type="submit">Cancel</button>
        		</td>
        	</tr>
        </table>
    </div>
</div>

  <div class="dijitHidden">
    <!-- dialog that gets its content via ajax, uses loading message -->
        <div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New Performance Measure'" id="newMeasureDialog">
        <!--<form  data-dojo-type="dojox/form/Manager" id="newMeasureForm">-->
        <table>
        <tr> 
            <td id="tdMeasureName">Measure Name:</td>
            <td><input type="text"  id="kpiName" data-dojo-type="dijit/form/TextBox" /></td>
        </tr>
        <tr id="addDescription">   

            <td>Description:</td>
            <td><input type="text"  id="kpiDescription" data-dojo-type="dijit/form/TextArea" /></td>
        </tr>
         <tr id="addOutcome">     
            <td>Outcome:</td>
            <td><input type="text"  id="kpiOutcome" data-dojo-type="dijit/form/TextBox" /></td>
        </tr>
        <tr id="addKRA">     
            <td>Key Result Area:</td>
            <td><div id="strategicResult"></div></td>
        </tr>
        <tr id="addMission">     
            <td>Mission:</td>
            <td><input type="text"  id="kpiMission" data-dojo-type="dijit/form/TextArea" /></td>
        </tr>
        <tr id="addVision">     
            <td>Vision:</td>
            <td><input type="text"  id="kpiVision" data-dojo-type="dijit/form/TextArea" /></td>
        </tr>
        <tr id="addValues">     
            <td>Values:</td>
            <td><input type="text"  id="kpiValues" data-dojo-type="dijit/form/TextArea" /></td>
        </tr>
        <tr id="addCollectionFrequency">
        	<td><!-- dijit/form/FilteringSelect -->Collection Frequency:</td>
            <td>
				<div id="collectionFrequency"></div>
            </td>
         </tr>
         <tr id="addMeasureType">
        	<td>Type of Measure:</td>
            <td><select id="measureType" data-dojo-type="dijit/form/FilteringSelect">
                    <option value="Standard KPI">Standard KPI</option>
                    <option value="Core Value">Core Value</option>
                    <!--<option value="projectMeasure">Project Measure</option>-->
            	</select></td>
         </tr>
         <tr id="addDataType">
        	<td><!-- radio buttons:  dijit/form/FilteringSelect -->Data Type:</td>
            <td><select id="dataType" data-dojo-type="dijit/form/FilteringSelect">
                    <option value="Standard">Standard</option>
                    <option value="Percentage(%)">Percentage(%)</option>
                    <option value="Currency">Currency</option>
            	</select></td>
         </tr>
         <tr id="addAggregationType"><td><!-- radio buttons:  dijit/form/RadioButton -->Aggregation Type</td>
             <td><input type="radio" id="aggregationTypeSum" name="aggregate" data-dojo-type="dijit/form/RadioButton" />
                <label for="aggregationTypeSum">Sum</label>       
                <input type="radio" id="aggregationTypeAvg" name="aggregate" data-dojo-type="dijit/form/RadioButton" />
                <label for="aggregationTypeAvg">Average</label>
                <input type="radio" id="aggregationTypeFinal" name="aggregate" data-dojo-type="dijit/form/RadioButton" checked='checked'/>
                <label for="aggregationTypeFinal">Last Value</label>
             </td>
           </tr>
          <tr id="addWeight">
            <td>Weight:</td>
            <td>
            	<table>
                	<tr>
                    <td><input type="number" id="weight" min="0" max="100" step="1" style="width:90px;" data-dojo-type="dijit/form/NumberSpinner" />%</td>
                    <!--<td><button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:editWeights" type="submit">Edit Weight</button></td> made this part of the tree sub-menus LTK 12May24 0914hrs-->
                    </tr>
                </table>
            </td>
           </tr>
         <tr id="addMeasureOwner">
        	 <td id="addMeasureOwnerTitle"><!-- radio buttons:  dijit/form/FilteringSelect -->Measure Owner(s):</td>
            <td><!--<input type='text' style='width:90%' id='kpiOwner'/>--><div id='kpiOwner'></div></td>
         </tr>
         
        <!--treating this as owner. not been practical to implement this. an additional bureacracy. LTK 10May24 1857hrs 
            <tr id="addIsUpdater"><td>Is Owner Updater?</td>
             <td><input id="updaterCheckbox" data-dojo-type="dijit/form/CheckBox" onClick="updaterChecked()"/></td>
         </tr>
         
         <tr id="addUpdater">
         	<td id="addUpdaterTitle">Measure Updater</td>
            <td>
            <input type='text' style='width:90%' id='kpiUpdater'></td>
         </tr>-->

		 <tr id="addCascade">
         	<td id="addCascadeTitle">Cascaded From</td>
            <td>
            <input type='text' style='width:90%' id='kpiCascade'></td>
         </tr>
         
        <tr id="addScoringType">
         	<td>
                <div data-dojo-type="dijit/form/DropDownButton" data-dojo-props='dropDownPosition:["above"]'>
                <span>Select Scoring Type</span><!-- Text for the button -->
                <!-- The dialog portion -->
                    <div data-dojo-type="dijit/TooltipDialog" id="scoringDialog">
                        <table>
                        <tr>
                        <td>
                        <label for="goalOnly">Goal Only</label>
                        <div><input type="image" src="images/Goal.png" name="image" onClick="goalClicked()"></div>
                        </td>
                        <td>
                        <label for="3Color">3 Color Scoring</label>
                        <!--<div id="zoomin" data-dojo-type="dijit.form.Button" style="background-image:url(images/3Color.png);">
                            <span>zoomin</span>
                        </div>-->
                        <div><input type="image" src="images/3Color.png" name="image" onClick="threeColorClicked()"></div>
                        </td></tr>
                        <tr><td>
                        <label for="4Color" style="display:inline-block;width:100px;">4 Color Scoring</label>
                        <div id="4Color"><input type="image" src="images/4Color.png" name="image" onClick="fourColorClicked()"></div>
                        </td><td>
                        <label for="5Color" style="display:inline-block;width:100px;">5 Color Scoring</label>
                        <div id="5Color"><input type="image" src="images/5Color.png" name="image" onClick="fiveColorClicked()"></div>
                        </td></tr></table>
                    </div>
                </div>
             </td>
             <td>
             <table><tr><td>
             <div id="selectedScoringType"></div>
             </td><td>

<div data-dojo-type="dijit/form/DropDownButton" data-dojo-props="dropDownPosition:['above-centered']"  id="thresholdDialog">
    <span>Set Thresholds</span><!-- Text for the button -->
    <div data-dojo-type="dijit/TooltipDialog" id="thresholdTooltip">
        <table>
        <tr id="trBest">                  
            <td align="right">Best</td>
            <td><input data-dojo-type="dijit/form/TextBox" id="blue" style="width:90px;" /></td>
            <td>Input Type</td>
            <td>
            <select name="blueType" id="blueType" data-dojo-type="dijit/form/FilteringSelect" 
                    data-dojo-props='onChange:calculatedGoal' style="width:90px;">
                <option value="Manual">Manual</option>
                <option value="Calculated">Calculated</option>
            </select> 
            </td> 
        </tr>
        <tr id="trStretch">
            <td align="right">Stretch Target</td>
            <td> <input data-dojo-type="dijit/form/TextBox" id="darkGreen" style="width:90px;" /></td>
            <td>Input Type</td>
            <td>
            <select name="darkGreenType" id="darkGreenType" data-dojo-type="dijit/form/FilteringSelect" 
                    data-dojo-props='onChange:calculatedGoal' style="width:90px;">
                <option value="Manual">Manual</option>
                <option value="Calculated">Calculated</option>
            </select>                     
            </td>
        </tr>
		<tr>
            <td align="right">Target</td>
            <td><input data-dojo-type="dijit/form/TextBox" id="green" style="width:90px;" /></td>
            
            <td>Input Type</td>
            <td>
            <select name="greenType" id="greenType" data-dojo-type="dijit/form/FilteringSelect" 
                    data-dojo-props='onChange:calculatedGoal' style="width:90px;">
                <option value="Manual">Manual</option>
                <option value="Calculated">Calculated</option>
            </select>
            </td>
		</tr>
        <tr id="trBaseline">     
            <td align="right">Baseline</td><td> <input data-dojo-type="dijit/form/TextBox" id="red" style="width:90px;" /></td>
            <td>Input Type</td>
            <td>
            <select name="redType" id="redType" data-dojo-type="dijit/form/FilteringSelect" 
                    data-dojo-props='onChange:calculatedGoal' style="width:90px;">
                <option value="Manual">Manual</option>
                <option value="Calculated">Calculated</option>
            </select>
            </td> 
        </tr>
        <tr><td colspan="2">     
        <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:hideThresholdsAddDialog" type="submit">Finish</button>
        </td></tr>
        </table>
    </div><!-- The dialog portion -->
</div>
                 </td></tr></table>
             </td>
         </tr>
         <!--<tr id="">
            <td colspan="2" id="teamNames"></td>
         </tr>-->
         <tr>
            <td colspan="2">
            	<div id="divThresholds"></div>
            </td>
         </tr>
         <tr id="addArchive"><td>Archive:</td>
             <td>
                <input type="radio" id="archiveNo" name="archive" data-dojo-type="dijit/form/RadioButton" checked='checked'/>
                <label for="archiveNo">No</label>
                <input type="radio" id="archiveYes" name="archive" data-dojo-type="dijit/form/RadioButton" />
                <label for="archiveYes">Yes</label>
             </td>
         </tr>
         <tr>
            <td colspan="2">
            <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:hideMeasureAddDialog" type="submit">Finish</button>
            </td>
           </tr>
</table>
</form>
         </div>
    </div>

<div class="dijitHidden">
	<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'Calculated Measure Formula'" id="calculatedMeasureDialog" style="font-size:11px;">
<table>
<tr>
    <td valign="top">
        <table><tr>
            <td valign="top" style="border-top:3px solid #00F;">
            	<div id="organizations" style="width:250px; height:200px; vertical-align:top;"></div></td>
            <td style="border-top:3px solid #00F;"><div id="perspectives" style="width:250px; height:200px; top:0px;"></div></td>
            <td style="border-top:3px solid #00F;"><div id="objectives" style="width:250px; height:200px; top:0px;"></div></td></tr>
        </table>
    </td>
</tr>
<tr>
    <td>
            <div id="grid" style="height:200px; width:750px;"></div>
    </td>
</tr>
<tr>
    <td>
    	<table><tr>
            <td><div id="gridCopy" style="height:100px; width:300px;"></div></td>
            <td valign="top">
                <label style="font-size:14px; font-weight:bold; vertical-align:top;">Formula = </label>
                <textarea id="formula"></textarea>
            </td>
        </tr></table>
    </td>
</tr>
</table>
<button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:calculatedMeasureResults" type="submit">Finish</button>
   </div>
</div>

<div class="dijitHidden">
	<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New Bookmark'" id="bookmarkDialog" style="font-size:11px;">
        <table>
        <tr>
            <td width="9%" valign="top"><strong>Bookmark Name</strong></td>
            <td width="24%"><input type='text' style='width:90%' id='bookmarkNameInput'/></td>
        </tr>
		</table>
        <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:toBookmark" type="submit">Finish</button>
    </div>
</div>

<div class="dijitHidden">
	<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'Rename Bookmark'" id="bookmarkRenameDialog" style="font-size:11px;">
        <table>
        <tr>
            <td width="12%" valign="top"><strong>Bookmark's New Name</strong></td>
            <td width="24%"><input type='text' style='width:90%' id='bookmarkRenameInput'/><div id="bookMarkId" style="display:none"></div></td>
        </tr>
		</table>
        <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:renameBookMark" type="submit">Finish</button>
    </div>
</div>

<div class="dijitHidden">
	<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New Personal Development Plan'" id="pdpDialog" style="font-size:11px;">
        <table id="pdpDialog-table">
    <tr>
        <td width="9%" valign="top"><strong>Competency/Skill Gap</strong></td>
        <td width="24%" id="pdpSkillGap"><input type='text' style='width:90%' id='pdpSkillGapInput'/></td>
        <td width="9%" valign="top"><strong>Intervention</strong></td>
        <td width="24%" id="pdpIntervention"><input type='text' style='width:90%' id='pdpInterventionInput'/></td>
     </tr>
     <tr>
        <td width="9%"></td>
        <td width="24%"></td>
        <td></td>
        <td width="24%" id="pdpComments" rowspan="2" colspan="3"><input type='text' style='width:90%' id='pdpCommentsInput'/></td>
	</tr>
    <tr>
        <td width="9%" valign="top"><strong>Resource</strong></td>
        <td width="24%" id="pdpResource"><input type='text' style='width:90%' id='pdpResourceInput'/></td>
        <td width="9%" valign="top"><strong>Comments</strong></td>
     </tr>
     <tr>
        <td width="9%" valign="top"><strong>Start Date</strong></td>
        <td width="24%" id="pdpStart">
            <input id="pdpStartInput" data-dojo-id="pdpStartDojo" type="text" data-dojo-type="dijit/form/DateTextBox"
            onChange="pdpDueDojo.constraints.min = arguments[0]; pdpCompleteDojo.constraints.min = arguments[0];" data-dojo-props="constraints:{datePattern: 'dd-MMM-yyyy'}"/>
        </td>
        <td width="9%" valign="top"><strong>Due Date</strong></td>
        <td width="24%" id="pdpDue">
            <input id="pdpDueInput" data-dojo-id="pdpDueDojo" type="text" data-dojo-type="dijit/form/DateTextBox"
            onChange="pdpStartDojo.constraints.max = arguments[0];" data-dojo-props="constraints:{datePattern: 'dd-MMM-yyyy'}"/>
        </td>
        <td width="9%" valign="top"><strong>Completion Date</strong></td>
        <td width="24%" id="pdpComplete">
        <input id="pdpCompleteInput" data-dojo-id="pdpCompleteDojo" type="text" data-dojo-type="dijit/form/DateTextBox"
            data-dojo-props="constraints:{datePattern: 'dd-MMM-yyyy'}"/>
       </td>
    </tr>
</table>
		<button data-dojo-type="dijit/form/Button" onClick="savePdp('<?php echo 'Save';?>','<?php echo 'null';?>')" type="submit">Finish</button>
   </div>
</div>

<!--<script src="../../highCharts901/code/highcharts.js" type="text/javascript"></script>-->
<script src="https://accent-analytics.com/highStock901/code/highstock.js" type="text/javascript"></script>
<script>
	var Highstock = Highcharts;
    Highcharts = null;
</script>
<script src="https://accent-analytics.com/highChartsGantt901/code/highcharts-gantt.js" type="text/javascript"></script>
<script src="https://accent-analytics.com/highStock901/code/highcharts-more.js"></script>

<script src="https://accent-analytics.com/highStock901/code/modules/drilldown.js"></script>
<script src="https://accent-analytics.com/highStock901/code/modules/no-data-to-display.js" type="text/javascript"></script>

<!--<script src="../../highCharts901/code/modules/grouped-categories.js"></script>-->
<!--<script src="../../highCharts404/js/regression.js"></script>-->

<!--<script src="https://openlayers.org/en/v3.1.1/build/ol.js" type="text/javascript"></script>-->

<script src="https://accent-analytics.com/jquery/3.6.0/jquery.min.js"></script>

<script src="https://accent-analytics.com/popper/popper.min.js" type="text/javascript"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css">
<script src="https://accent-analytics.com/bootstrap/5.0.0/dist/js/bootstrap.min.js"></script>
<script src="https://accent-analytics.com/bootstrap_table/1.18.3/bootstrap-table.min.js"></script>
<script src="https://accent-analytics.com/bootstrap_table/1.18.3/extensions/filter-control/bootstrap-table-filter-control.min.js"></script>
<script src="https://accent-analytics.com/bootstrap_fileinput/js/fileinput.min.js" type="text/javascript"></script>
<script src="https://accent-analytics.com/bootstrap_fileinput/themes/fas/theme.min.js" type="text/javascript"></script>

<script src="https://accent-analytics.com/virtualSelect/dist/virtual-select.min.js"></script>

<link rel="stylesheet" href="css/trafficLights.css" media="all">

<script type="text/javascript" src="https://accent-analytics.com/dojo/dojo.js"></script>
<script type="text/javascript" src="js/measure.js"></script>
<script type="text/javascript" src="js/initiative.js"></script>
<script type="text/javascript" src="js/documents.js"></script>
<script type="text/javascript" src="js/highScript.js"></script>
<script type="text/javascript" src="js/navigableDnd.js"></script>

  <!--<div style="display:none"><span id="lastSelected"></span></div>  -->
</body>
</html>