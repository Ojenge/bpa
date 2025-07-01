require([
"dojo/dom",
"dojo/dom-style",
"dojo/request",
"dijit/Dialog",
"dojo/domReady!"],
function(dom, domStyle, request, Dialog)
{

confirmDeletePIP = function(pipId)
{
	request.post("../individual/get-pdp.php",{
		handleAs: "json",
		data: {
			pdpId: pipId
		}						
		}).then(function(pipDetails) 
		{
			var toDelete = "<table class='table table-hover table-responsive table-bordered table-sm table-condensed table-striped border-primary rounded'><tr><th>Field</th><th>Value</th></tr>";
			//toDelete = toDelete + "<tr><td>Owner</td><td>"+pipDetails.owner+"</td></tr>";
			toDelete = toDelete + "<tr><td>Skill Gap</td><td>"+pipDetails.skillGap+"</td></tr>";
			toDelete = toDelete + "<tr><td>Intervention</td><td>"+pipDetails.intervention+"</td></tr>";
			toDelete = toDelete + "<tr><td>Start Date</td><td>"+pipDetails.startDate+"</td></tr>";
			toDelete = toDelete + "<tr><td>Due Date</td><td>"+pipDetails.dueDate+"</td></tr>";
			toDelete = toDelete + "<tr><td>Comments</td><td>"+pipDetails.comments+"</td></tr>";
			toDelete = toDelete + "</table>";
			confirmDeletePIPDialog = new Dialog({
				title: "Delete Personal Improvement Plan",
				content: "<table width='100%'><tr><td colspan='2'>Are you sure you would like to delete the following PIP? <span style='color:red;'>(This action is irreversible)</span)</td></tr><tr><td colspan='2'>"+toDelete+"</td></tr><tr><td><button onClick='deletePIP("+pipId+")'>Delete</button><button onClick='closeConfirmDeletePIPDialog()'>Cancel</button></td></tr></table>",
				style: "width: 50%"
			});
			confirmDeletePIPDialog.show();
		});
}
closeConfirmDeletePIPDialog = function()
{
	confirmDeletePIPDialog.hide();
}
/**********************************************/
deletePIP = function(id)
{	
	confirmDeletePIPDialog.hide()	
	request.post("../individual/delete-pip.php",{
	handleAs: "json",
	data: {
		selectedPIP: id,
	}						
	}).then(function() 
	{
		dom.byId("initMsgContent").innerHTML = "PIP Deleted";
		domStyle.set(dom.byId("initMsgContent"), 'display', 'block');
		var msgTimeout = setTimeout(function(){
				domStyle.set(dom.byId("initMsgContent"), 'display', 'none');
		},3000);
		
		$tablePIP.bootstrapTable("refresh", {
				url: "../individual/get-all-pips.php",
				silent: true
			});
	});
}
archivePIP= function(pipId)
{
	request.post("../individual/archive-pip.php",{
	//handleAs: "json",
	data: {
		selectedPIP: pipId
	}						
	}).then(function(archive) 
	{
		if(archive == 'Yes')
		{
			domStyle.set(dom.byId("initMsgContent"), 'display', 'block');
			domStyle.set(dom.byId("archive"), "color", "red");
			dom.byId("initMsgContent").innerHTML = "PIP Archived";
		}
		else
		{
			domStyle.set(dom.byId("initMsgContent"), 'display', 'block');
			domStyle.set(dom.byId("archive"), "color", "green");
			dom.byId("initMsgContent").innerHTML = "PIP Removed From Archive";
		}
		var msgTimeout = setTimeout(function()
		{
				domStyle.set(dom.byId("initMsgContent"), 'display', 'none');
		},3000);
		
		$tablePIP.bootstrapTable("refresh", {
				url: "../individual/get-all-pips.php",
				silent: true
			});
	});
}

});