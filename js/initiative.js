var managerSelect, sponsorSelect, pdpStaffSelect, parentSelect, teamSelect, impactSelect, sponsorId, managerId, initiativeParentId;

require([
"dojo/dom",
"dojo/dom-style",
"dojo/store/Memory",
"dojo/request",
"dijit/Dialog",
"dijit/form/FilteringSelect",
"dojo/domReady!"],
function(dom, domStyle, Memory, request, Dialog, FilteringSelect)
{
request.post("initiatives/get-initiative-impact.php",{
handleAs: "json",
data: {
}
}).then(function(impactData) 
{			
	//console.log("Are we getting here?");
	var impactStore = new Memory({data:impactData});
	
	impactSelect = new FilteringSelect({
	name: "impactSelect",

	store: impactStore,
	searchAttr: "name",
	maxHeight: -1, 
	onChange: function(){
		//console.log("Id = " + this.item.id);
		initiativeImpactId = this.item.id;
	}
	}, "initiativeLinkInput");
	impactSelect.startup();
});

request.post("userCalls/get-users.php",{
	handleAs: "json",
	data: {
	}
	}).then(function(userData)
	{			
		var userStore = new Memory({data:userData});
		
		managerSelect = new FilteringSelect({
		name: "userSelectManager",
	
		store: userStore,
		searchAttr: "User",
		maxHeight: -1, 
		onChange: function(){
			managerId = this.item.id;
		}
		}, "initiativeManagerInput");
		managerSelect.startup();
	
		sponsorSelect = new FilteringSelect({
		name: "userSelectSponsor",
		store: userStore,
		searchAttr: "User",
		maxHeight: -1, 
		onChange: function(){
			sponsorId = this.item.id;
		}
		}, "initiativeSponsorInput");
		sponsorSelect.startup();
		
		pdpStaffSelect = new FilteringSelect({
	
		store: userStore,
		searchAttr: "User",
		maxHeight: -1, 
		onChange: function(){
			pdpId = this.item.id;
		}
		}, "pdpStaffInput");
		pdpStaffSelect.startup();
	});
request.post("initiatives/get-initiatives-for-select.php",{
	handleAs: "json"
	}).then(function(initiativeList)
	{
		var initiativeListStore = new Memory({data:initiativeList});
		
		parentSelect = new FilteringSelect({
	
		required: false,
		store: initiativeListStore,
		searchAttr: "Initiative",
		maxHeight: -1, 
		onChange: function(){
			initiativeParentId = this.item.id;
		}
		}, "initiativeParentInput");
		parentSelect.startup();
	});
	
request.post("userCalls/get-users.php",{
	handleAs: "json",
	data: {
	}
	}).then(function(userData)
	{			
		var userStore = new Memory({data:userData});
		
		teamSelect = new FilteringSelect({
		name: "userSelectManager",

		store: userStore,
		searchAttr: "User",
		maxHeight: -1,
		onChange: function(){
			userId = this.item.id;
			var currentInitiativeId = dom.byId("selectedElement").innerHTML;

			// Check if we have a valid initiative ID
			if (currentInitiativeId == "New" || currentInitiativeId == "undefined" || !currentInitiativeId || isNaN(currentInitiativeId)) {
				alert("Please save the initiative first before adding team members.");
				this.set("value", ""); // Clear the selection
				return;
			}

			request.post("../initiatives/save-team.php",{
				data: {
					userId: this.item.id,
					initiativeId: currentInitiativeId
				}
			}).then(function(team)
			{
				dom.byId("teamMembers").innerHTML = team;
			})
		}
		}, "initiativeTeamInput");
		teamSelect.startup();
	});
	
saveInitiative = function()
{
	if(dom.byId("deliverableStatusInput").checked == true) var deliverableStatusInput = "on";
	else var deliverableStatusInput = "off";
	//if(dom.byId("scoringInput").checked == true) var scoreInput = "yes";
	//else var scoreInput = "no";
	request.post("initiatives/save-initiative.php",{
	data: {
		editInitiativeStatus: dom.byId("editSaveDelete").innerHTML,
		selectedInitiative: dom.byId("selectedElement").innerHTML,
		initiative_name: dijit.byId("initiativeNameInput").value,
		initiative_deliverable: dom.byId("initiativeDeliverableInput").value,
		initiative_Link: initiativeImpactId,
		initiative_Sponsor: sponsorId,
		initiative_Manager: managerId,
		initiative_Parent: initiativeParentId,
		initiative_Budget: dom.byId("initiativeBudgetInput").value,
		initiative_Damage: dom.byId("initiativeCostInput").value,
		initiative_Start: dom.byId("initiativeStartInput").value,
		initiative_Due: dom.byId("initiativeDueInput").value,
		initiative_deliverableStatus: deliverableStatusInput,
		initiative_Scope: dijit.byId("scopeInput").value,
		initiative_Complete: dom.byId("initiativeCompleteInput").value,
		initiative_Status: dom.byId("initiativeStatusInput").value,
		initiative_Percentage: dom.byId("percentageCompletionInput").value,
		initiative_Status_Details: dijit.byId("initiativeStatusDetailsInput").value,
		initiativeNotes: dijit.byId("initiativeNotesInput").value
	}}).then(function(initiativeId)
	{
		// Update the selectedElement with the returned initiative ID
		dom.byId("selectedElement").innerHTML = initiativeId;
		dom.byId("editSaveDelete").innerHTML = "false";
		dijit.byId("newInitiativeDialog").set("title", "New Initiative");

		// Close the dialog
		dijit.byId("newInitiativeDialog").hide();

		//initiativeListFunction(initiativeId);
		//console.log("Select Element => " + dom.byId("selectedElement").innerHTML)
		var ngoja = setTimeout(function(){
			initiativeListFunction(initiativeId); // Use the returned ID directly
		},1000); // Reduced timeout since we now have the correct ID
	});
	
	try 
	{
		//eval($table);
		$table.bootstrapTable("refresh", {
			url: "initiatives/get-all-initiatives.php",
			silent: true
		});
	} catch (e) 
	{
		console.log("Here Catch");
		refreshDataEntryPage();
	}
}
/**********************************************/
editInitiative = function(initiativeId, presetObjectiveId)
{
	if(dijit.byId("initiativeLinkInput")) dijit.byId("initiativeLinkInput").set('disabled', false);
	if(initiativeId == "New")
	{
		dom.byId("selectedElement").innerHTML = "New";
		managerSelect.set("value", "");
		sponsorSelect.set("value", "");

		parentSelect.set("value", "");

		dom.byId("editSaveDelete").innerHTML = "New";

		dijit.byId("newInitiativeDialog").set("title", "New Initiative");
		dijit.byId("initiativeNameInput").set("value", "");
		dom.byId("initiativeDeliverableInput").value = "";
		dom.byId("deliverableStatusInput").checked = false;
		dom.byId("initiativeBudgetInput").value = "";
		dom.byId("initiativeStartInput").value = "";
		dom.byId("initiativeDueInput").value = "";
		dom.byId("initiativeParentInput").value = "";
		dom.byId("initiativeLinkInput").value = "";
		dom.byId("initiativeCompleteInput").value = "";
		dom.byId("initiativeCostInput").value = "";
		dom.byId("initiativeStatusInput").value = "";
		dijit.byId("initiativeStatusDetailsInput").value = "";
		dijit.byId("scopeInput").set("value", "");
		dom.byId("percentageCompletionInput").value = "";
		dijit.byId("initiativeStatusDetailsInput").set("value", "");
		dijit.byId("initiativeNotesInput").set("value", "");

		// If a preset objective ID is provided, set it
		if(presetObjectiveId) {
			// Set the objective in the dropdown
			if(impactSelect) {
				impactSelect.set("value", presetObjectiveId);
				initiativeImpactId = presetObjectiveId;
			}
		}

		dijit.byId("newInitiativeDialog").show();
	}
	else 
	{
		if(initiativeId == null || initiativeId == "undefined")
		{
			/*we are coming from initiativeListFunction route that saved the id in selectedElement and not from 
			  the bootstrap table route that allows one to carry the id from db*/
			initiativeId = dom.byId("selectedElement").innerHTML;
		}
		else dom.byId("selectedElement").innerHTML = initiativeId;
		
		dijit.byId("newInitiativeDialog").show(); //moved this here to circumvent the error: TypeError: can't access property "replace", html is undefined. This issue occurs, when you are trying to access dijit controls before parsing
		
		request.post("initiatives/get-initiative.php",{
		handleAs: "json",
		data: {
			initiativeId: initiativeId
		}
		}).then(function(initiativeDetails) 
		{
			managerSelect.set("value", initiativeDetails.manager);
			sponsorSelect.set("value", initiativeDetails.sponsor);
			managerId = initiativeDetails.managerId; //Manager and Sponsor would not be saved when editing an existing initiative. LTK 17Jun2021 2157hrs
			sponsorId = initiativeDetails.sponsorId;
			
			parentSelect.set("value", initiativeDetails.parent);
			initiativeParentId = initiativeDetails.parentId;

			//console.log("Manager = " + initiativeDetails.manager + ", Sponsor = " + initiativeDetails.sponsor + ", Parent = " + initiativeDetails.parent);
		
			dom.byId("editSaveDelete").innerHTML = "Edit";
			//console.log("Here");
			dijit.byId("newInitiativeDialog").set("title", "Edit Initiative");
			dijit.byId("initiativeNameInput").set("value", initiativeDetails.name);
			
			dom.byId("initiativeDeliverableInput").value = initiativeDetails.deliverable;
			if(initiativeDetails.deliverableStatus == "on") dom.byId("deliverableStatusInput").checked = true;
			else dom.byId("deliverableStatusInput").checked = false;
			
			dom.byId("initiativeBudgetInput").value = initiativeDetails.budget;
			dom.byId("initiativeStartInput").value = initiativeDetails.startDate;
			dom.byId("initiativeDueInput").value = initiativeDetails.dueDate;
			dom.byId("initiativeLinkInput").value = initiativeDetails.link;
			dom.byId("initiativeCompleteInput").value = initiativeDetails.completionDate;
			dom.byId("initiativeCostInput").value = initiativeDetails.damage;
			dom.byId("initiativeStatusInput").value = initiativeDetails.statusWithoutCircle;
			
			/*Dijit text editor capturing scope was throwing an error when the output is null. LTK 07 Apr 2021 2118hours*/
			if(initiativeDetails.scope == null) initiativeDetails.scope = "";
			dijit.byId("scopeInput").set("value", initiativeDetails.scope);
			dom.byId("percentageCompletionInput").value = initiativeDetails.percentageCompletion;
			
			//dijit.byId("initiativeStatusDetailsInput").set("value", initiativeDetails.statusWithoutCircle);
			//dom.byId("initiativeStatusDetailsInput").value = initiativeDetails.statusWithoutCircle;
			dijit.byId("initiativeStatusDetailsInput").set("value", initiativeDetails.statusDetails);
			dijit.byId("initiativeNotesInput").set("value", initiativeDetails.notes);
		});
	}
}
confirmDeleteInitiative = function(initiativeId)
{
	if(initiativeId == null || initiativeId == "undefined")
	{
		/*we are coming from initiativeListFunction route that saved the id in selectedElement and not from 
		  the bootstrap table route that allows one to carry the id from db*/
		initiativeId = dom.byId("selectedElement").innerHTML;
	}
	else dom.byId("selectedElement").innerHTML = initiativeId;
	
	request.post("initiatives/get-initiative.php",{
		handleAs: "json",
		data: {
			initiativeId: dom.byId("selectedElement").innerHTML
		}
		}).then(function(initiativeDetails) 
		{
			var toDelete = "<table class='table table-hover table-responsive table-bordered table-sm table-condensed table-striped border-primary rounded'><tr><th>Field</th><th>Value</th></tr>";
			toDelete = toDelete + "<tr><td>Name</td><td>"+initiativeDetails.name+"</td></tr>";
			toDelete = toDelete + "<tr><td>Owner</td><td>"+initiativeDetails.manager+"</td></tr>";
			toDelete = toDelete + "<tr><td>Start Date</td><td>"+initiativeDetails.startDate+"</td></tr>";
			toDelete = toDelete + "<tr><td>Due Date</td><td>"+initiativeDetails.dueDate+"</td></tr>";
			toDelete = toDelete + "<tr><td>Linked Scorecard Item</td><td>"+initiativeDetails.link+"</td></tr>";
			toDelete = toDelete + "</table>";
			confirmDeleteDialog = new Dialog({
				title: "Delete Initiative",
				content: "<table width='100%'><tr><td colspan='2'>Are you sure you would like to delete the following initiative? <span style='color:red;'>(This action is irreversible)</span)</td></tr><tr><td colspan='2'>"+toDelete+"</td></tr><tr><td><button onClick='deleteInitiative()'>Delete</button><button onClick='closeConfirmDeleteDialog()'>Cancel</button></td></tr></table>",
				style: "width: 50%"
			});
			confirmDeleteDialog.show();
			/*
			parentSelect.set("value", initiativeDetails.parent);
		
			dom.byId("initiativeBudgetInput").value = initiativeDetails.budget;
			dom.byId("initiativeCompleteInput").value = initiativeDetails.completionDate;
			dom.byId("initiativeCostInput").value = initiativeDetails.damage;
			dom.byId("initiativeStatusInput").value = initiativeDetails.status;
			dijit.byId("scopeInput").set("value", initiativeDetails.scope);
			dom.byId("percentageCompletionInput").value = initiativeDetails.percentageCompletion;*/
		});
}
closeConfirmDeleteDialog = function()
{
	confirmDeleteDialog.hide();
}
/**********************************************/
deleteInitiative = function()
{	
	confirmDeleteDialog.hide()	
	request.post("initiatives/delete-initiative.php",{
	handleAs: "json",
	data: {
		selectedInitiative: dom.byId("selectedElement").innerHTML,
	}
	}).then(function() 
	{
		dojo.byId("initiativeNameDiv").innerHTML = '';
		dojo.byId("deliverableDiv").innerHTML = '';
		dojo.byId("parentDiv").innerHTML = '';
		dojo.byId("budgetDiv").innerHTML = '';
		dojo.byId("sponsorDiv").innerHTML = '';
		dojo.byId("managerDiv").innerHTML = '';
		dojo.byId("startDateDiv").innerHTML = '';
		dojo.byId("endDateDiv").innerHTML = '';
		dojo.byId("objectImpactedDiv").innerHTML = '';
		dojo.byId("completionDateDiv").innerHTML = '';
		dojo.byId("damageDiv").innerHTML = '';
		
		domStyle.set(dom.byId("tdDamageColor"), "backgroundColor", "white");
		domStyle.set(dom.byId("tdDeliverableColor"), "backgroundColor","white");
		
		dijit.byId("initiativeStatusGauge").set('color', "white");
		dijit.byId("initiativeStatusGauge").set('value', 0);
		
		dom.byId("initMsgContent").innerHTML = "Initiative Deleted";
		domStyle.set(dom.byId("initMsgContent"), 'display', 'block');
		var msgTimeout = setTimeout(function(){
				domStyle.set(dom.byId("initMsgContent"), 'display', 'none');
		},3000);
		
		$table.bootstrapTable("refresh", {
				url: "initiatives/get-all-initiatives.php",
				silent: true
			});
	});
}
archiveInitiative = function(initiativeId)
{
	if(initiativeId == null || initiativeId == "undefined")
	{
		/*we are coming from initiativeListFunction route that saved the id in selectedElement and not from 
		  the bootstrap table route that allows one to carry the id from db*/
		initiativeId = dom.byId("selectedElement").innerHTML;
	}
	else dom.byId("selectedElement").innerHTML = initiativeId;
	
	request.post("initiatives/archive-initiative.php",{
	//handleAs: "json",
	data: {
		selectedInitiative: initiativeId
	}
	}).then(function(archive) 
	{
		if(archive == 'Yes')
		{
			domStyle.set(dom.byId("initMsgContent"), 'display', 'block');
			domStyle.set(dom.byId("archive"), "color", "red");
			dom.byId("initMsgContent").innerHTML = "Initiative Archived";
		}
		else
		{
			domStyle.set(dom.byId("initMsgContent"), 'display', 'block');
			domStyle.set(dom.byId("archive"), "color", "green");
			dom.byId("initMsgContent").innerHTML = "Initiative Removed From Archive";
		}
		var msgTimeout = setTimeout(function()
		{
				domStyle.set(dom.byId("initMsgContent"), 'display', 'none');
		},3000);
		
		$table.bootstrapTable("refresh", {
				url: "initiatives/get-all-initiatives.php",
				silent: true
			});
	});
}
removeMember = function(userId)
{
	var currentInitiativeId = dom.byId("selectedElement").innerHTML;

	// Check if we have a valid initiative ID
	if (currentInitiativeId == "New" || currentInitiativeId == "undefined" || !currentInitiativeId || isNaN(currentInitiativeId)) {
		alert("Cannot remove team members from unsaved initiative.");
		return;
	}

	request.post("initiatives/remove-member.php",{
	//handleAs: "json",
	data: {
		userId: userId,
		initiativeId: currentInitiativeId
	}
	}).then(function(team)
	{
		dom.byId("teamMembers").innerHTML = team;
	})

}
initiativeListFunction = function(initiativeId)
{
	//console.log(initiativeId + " ; " + dom.byId("selectedElement").innerHTML);
	if(initiativeId == null || initiativeId == "undefined")
	{
		/*we are coming from initiativeListFunction route that saved the id in selectedElement and not from 
		  the bootstrap table route that allows one to carry the id from db*/
		initiativeId = dom.byId("selectedElement").innerHTML;
	}
	else dom.byId("selectedElement").innerHTML = initiativeId;
	
	//dom.byId("selectedElement").innerHTML = initiativeId;
	request.post("initiatives/get-initiative.php",{
		handleAs: "json",
		data: {
			initiativeId: initiativeId
		}
		}).then(function(initiativeData)
		{
			if(!dojo.byId("initiativeNameDiv")) return;
			dojo.byId("initiativeNameDiv").innerHTML = initiativeData["name"];
			dojo.byId("objectImpactedDiv").innerHTML = initiativeData["link"];
			dojo.byId("sponsorDiv").innerHTML = initiativeData["sponsor"];
			dojo.byId("managerDiv").innerHTML = initiativeData["manager"];
			dojo.byId("parentDiv").innerHTML = initiativeData["parent"];
			dojo.byId("budgetDiv").innerHTML = initiativeData["budget"];
			dojo.byId("damageDiv").innerHTML = initiativeData["damage"];
			dojo.byId("tdDamageColor").innerHTML = initiativeData["damageColor"];
			dojo.byId("startDateDiv").innerHTML = initiativeData["startDate"];
			dojo.byId("endDateDiv").innerHTML = initiativeData["dueDate"];
			dojo.byId("completionDateDiv").innerHTML = initiativeData["completionDate"];

			dojo.byId("deliverableDiv").innerHTML = initiativeData["deliverable"];
			dojo.byId("tdDeliverableColor").innerHTML = initiativeData["deliverableColor"];
			dojo.byId("scopeDiv").innerHTML = initiativeData["scope"];
			dojo.byId("completionDateDiv").innerHTML = initiativeData["completionDate"];
			dojo.byId("damageDiv").innerHTML = initiativeData["damage"];
			if(initiativeData["deliverableStatus"] == "on")
			dojo.byId("deliverableStatusInput").checked = true;
			else
			dojo.byId("deliverableStatusInput").checked = false;

			domStyle.set(dom.byId("tdDamageColor"), "backgroundColor", initiativeData["damageColor"]);
			domStyle.set(dom.byId("tdDeliverableColor"), "backgroundColor",initiativeData["deliverableColor"]);

			dijit.byId("initiativeStatusGauge").set('color', initiativeData["initiativeColor"]);
			dijit.byId("initiativeStatusGauge").set('value', initiativeData["barStatusValue"]);
			dom.byId("selectedElement").innerHTML = initiativeData["id"];
			sponsorId = initiativeData["sponsorId"];
			managerId = initiativeData["managerId"];
			initiativeImpactId = initiativeData["linkId"];
			
			dojo.byId("statusDiv").innerHTML = initiativeData["status"];
			dojo.byId("percentageCompletionDiv").innerHTML = initiativeData["percentageCompletion"];
			dojo.byId("statusDetailsDiv").innerHTML = initiativeData["statusDetails"];
			dojo.byId("statusNotesDiv").innerHTML = initiativeData["notes"];
			
			//dojo.byId("initiativeIssues").innerHTML = initiativeData["issues"];
			
			initiativeParentId = initiativeData["parentId"];
			domStyle.set(dom.byId("divConversation"), "display", "block");
			
			if(initiativeData["archive"] == 'Yes')
			domStyle.set(dom.byId("archive"), "color", "red");
			//else
			//domStyle.set(dom.byId("archive"), "color", "green");
			
			postComment();
			
			request.post("initiatives/get-team.php",{
			//handleAs: "json",
			data: {
				initiativeId: initiativeId
			}						
			}).then(function(team) 
			{
				dom.byId("teamMembers").innerHTML = team;
			})
		});
}
next = function()
{
	request.post("initiatives/next.php",{
		data: {
			initiativeId: dom.byId("selectedElement").innerHTML
		}
		}).then(function(nextId)
		{
			initiativeListFunction(nextId);
			dom.byId("selectedElement").innerHTML = nextId;
		});
}


previous = function()
{
	request.post("initiatives/previous.php",{
		data: {
			initiativeId: dom.byId("selectedElement").innerHTML
		}
		}).then(function(nextId)
		{
			initiativeListFunction(nextId);
			dom.byId("selectedElement").innerHTML = nextId;
		});
}
});