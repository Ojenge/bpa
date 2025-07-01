<div class="dijitHidden">
	<div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New Personal Improvement Plan'" id="pdpDialog" style="font-size:11px;">
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