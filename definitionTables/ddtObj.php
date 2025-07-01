<!DOCTYPE html>
<html >
<head>
<link rel="stylesheet" href="../dijit/themes/claro/claro.css">
<style>
label{display:block; float:left;}​ <!--or: textarea { vertical-align: top; }​-->
textarea{
    border: none;
    width: 100%;
    -webkit-box-sizing: border-box; /* <=iOS4, <= Android  2.3 */
    -moz-box-sizing: border-box; /* FF1+ */
    box-sizing: border-box; /* Chrome, IE8, Opera, Safari 5.1*/
    }
	

</style>

<script>
require(["dojo/store/Memory", "dojo/json", "dojo/request", "dojox/mvc/equals", "dijit/form/FilteringSelect", "dijit/form/Button",  "dojo/domReady!"], 
function(Memory, json, request, equals, FilteringSelect){
var orgId, perspId, select1, perspStore;
request.post("get-perspectives.php",{
handleAs: "json",
data: {
}						
}).then(function(perspData)
{			
	perspStore = new Memory({data:perspData});
	//perspStore = new Observable(perspStore);
	select1 = new FilteringSelect({
	name: "perspSelectManager",
	//displayedValue: managerDisplay,
	//placeHolder: "Select a User",
	store: perspStore,
	searchAttr: "Perspective",
	maxHeight: -1, 
	onChange: function(){
		perspId = this.item.id;
	}
	}, "objPersp");
	select1.startup();
});
request.post("get-organizations.php",{
handleAs: "json",
data: {
}						
}).then(function(orgData)
{			
	var orgStore = new Memory({data:orgData});
	
	select2 = new FilteringSelect({
	name: "orgSelectManager",
	//displayedValue: managerDisplay,
	//placeHolder: "Select a User",
	store: orgStore,
	searchAttr: "Organization",
	maxHeight: -1, 
	onChange: function(){
		orgId = this.item.id;
	}
	}, "orgName");
	select2.startup();
});
addPersp = function()
{
	dijit.byId("newPerspDialog").show();
}
savePersp = function()
{
	dijit.byId("newPerspDialog").hide();
	var perspName = dojo.byId("perspName").value;
	var orgName = dojo.byId("orgName").value;
	//alert(orgId);
	
	request.post("save-tree.php",{
	//handleAs: "json",
	data: {
		tree_edit: "NULL",
		tree_type: "perspective",
		tree_parent: orgId,
		tree_name: perspName
	}						
	}).then(function(treeId)
	{
		childItem = 
		{
			name: perspName,
			id: treeId,
			parent: orgId,
			type: "don't edit",
			overwrite: true
		};
		perspItem =
		{
			name: perspName,
			id: treeId	
		}
		governmentStore.put(childItem);
		perspStore.put({id: treeId, Perspective: perspName});
		//select1.startup();
		//alert("Saved perspective: " + perspName + treeId);
		//tree.startup();
	})
}
	
/*var storeData2 = [{id:1, objName: 'Improve Customer Satisfaction', objOwner: 'Tom Oiende', objDescr: "Description goes here", objOutcome: "Outcome goes here...", objFrom: "From goes here...", objTo: "Destination to goes here...", objKpi: "Candidate Measures go here...", objInitiative: "Candidate initiatives go here", objTarget: "3.01", objLinkedTo: "org4"},
    {id:2, objName: 'Improve Brand Image', objOwner: 'Kimani Kim', objDescr: "Description goes here", objOutcome: "Outcome goes here...", objFrom: "From goes here...", objTo: "Destination to goes here...", objKpi: "Candidate Measures go here...", objInitiative: "Candidate initiatives go here", objTarget: "3.01", objLinkedTo: "org4"},
    {id:3, objName: 'Increase Profitability', objOwner: 'Ole Safari', objDescr: "Description goes here", objOutcome: "Outcome goes here...", objFrom: "From goes here...", objTo: "Destination to goes here...", objKpi: "Candidate Measures go here...", objInitiative: "Candidate initiatives go here", objTarget: "3.01", objLinkedTo: "org4"},
    {id:4, objName: 'Reduce Costs', objOwner: 'Mwana Aisha', objDescr: "Description goes here", objOutcome: "Outcome goes here...", objFrom: "From goes here...", objTo: "Destination to goes here...", objKpi: "Candidate Measures go here...", objInitiative: "Candidate initiatives go here", objTarget: "3.01", objLinkedTo: "org4" }];*/

var storeData;

request.post("get-commentary.php",{
handleAs: "json",
data: {
}						
}).then(function(data) 
{
	//alert(json.stringify(data));
	storeData = data;
	//alert(storeData + storeData2);
  
var objStore = new Memory({data: storeData});

var totalItems = storeData.length;   // How many total items should we expect.
var currentCount = 1;     // Current size of the page.
var newItem = "False";
var objLinkedTo = "org4";
var edit;

var firstObject = objStore.get(currentCount);
//alert(json.stringify(objStore));
dojo.byId("objName").value = firstObject.objName;
//alert(firstObject.objName);
dojo.byId("objPersp").value = firstObject.objPersp;
dojo.byId("objOwner").value = firstObject.objOwner;
dojo.byId("objDescr").value = firstObject.objDescr;
dojo.byId("objOutcome").value = firstObject.objOutcome;
dojo.byId("objFrom").value = firstObject.objFrom;
dojo.byId("objTo").value = firstObject.objTo;
dojo.byId("objKpi").value = firstObject.objKpi;
dojo.byId("objInitiative").value = firstObject.objInitiative;
dojo.byId("objTarget").value = firstObject.objTarget;
//dojo.byId("objLinkedTo").value = firstObject.objLinkedTo;
dojo.byId("objNo").innerHTML = firstObject.id;

previousPage = function()
{
	var editData = {};
	editData["id"] = currentCount;
	editData["objName"] = dojo.byId("objName").value;
	editData["objPersp"] = dojo.byId("objPersp").value;
	editData["objOwner"] = dojo.byId("objOwner").value;
	editData["objDescr"] = dojo.byId("objDescr").value;
	editData["objOutcome"] = dojo.byId("objOutcome").value;
	editData["objFrom"] = dojo.byId("objFrom").value;
	editData["objTo"] = dojo.byId("objTo").value;
	editData["objKpi"] = dojo.byId("objKpi").value;
	editData["objInitiative"] = dojo.byId("objInitiative").value;
	editData["objTarget"] = dojo.byId("objTarget").value;
	editData["objLinkedTo"] = "org4";
	
	var previousData = objStore.get(currentCount);
	
	var isSimilar = equals(editData, previousData);
	if(isSimilar)
		edit = "False";
	else
		{
			objStore.remove(editData["id"]);
			objStore.put({id:editData["id"], objName: editData["objName"], objPersp: editData["objPersp"], objOwner: editData["objOwner"], objDescr: editData["objDescr"], objOutcome: editData["objOutcome"], objFrom: editData["objFrom"], objTo: editData["objTo"], objKpi: editData["objKpi"], objInitiative: editData["objInitiative"], objTarget: editData["objTarget"], objLinkedTo: "org4"});
			edit = "True";
			request.post("save-commentary.php",{
			//handleAs: "json",
			data: {
				edit: edit,
				id:editData["id"],
				objName: editData["objName"],
				objOwner: editData["objOwner"],
				objPersp: editData["objPersp"],
				//objTeam: newTeam,
				objDescr: editData["objDescr"],
				objOutcome: editData["objOutcome"],
				objFrom: ["objFrom"],
				objTo: ["objTo"],
				objKpi: ["objKpi"],
				objInitiative: ["objInitiative"],
				objTarget: ["objTarget"],
				objLinkedTo: "org4"
			}						
			}).then(function() 
			{
				edit = "False";
			});		
		}
			
	if(newItem == "True" && dojo.byId("objName").value != "")//add and save new item
	{
		var newId = totalItems + 1;
		var newName = dojo.byId("objName").value;
		var newPersp = dojo.byId("objPersp").value;
		var newOwner = dojo.byId("objOwner").value;
		var newDescr = dojo.byId("objDescr").value;
		var newOutcome = dojo.byId("objOutcome").value;
		var newFrom = dojo.byId("objFrom").value;
		var newTo = dojo.byId("objTo").value;
		var newKpi = dojo.byId("objKpi").value;
		var newInitiative = dojo.byId("objInitiative").value;
		var newTarget = dojo.byId("objTarget").value;
		//var objLinkedTo = dojo.byId("objLinkedTo").value;
		//alert("Save details");
		objStore.put({id:newId, objName: newName, objOwner: newOwner, objPersp: newPersp, objDescr: newDescr, objOutcome: newOutcome, objFrom: newFrom, objTo: newTo, objKpi: newKpi, objInitiative: newInitiative, objTarget: newTarget, objLinkedTo: "org4"});
		request.post("save-commentary.php",{
		//handleAs: "json",
		data: {
				id:newId,
				objName: newName,
				objOwner: newOwner,
				objPersp: newPersp,
				//objTeam: newTeam,
				objDescr: newDescr,
				objOutcome: newOutcome,
				objFrom: newFrom,
				objTo: newTo,
				objKpi: newKpi,
				objInitiative: newInitiative,
				objTarget: newTarget,
				objLinkedTo: "org4"// this should not be hard coded!!! - link it to newPersp id!
			}						
		}).then(function(data) 
		{
			//alert(data);
		});
		
		if(newName != null)
		{
			request.post("save-tree.php",{
			handleAs: "json",
			data: {
				tree_edit: "NULL",
				tree_type: "objective",
				tree_parent: newPersp,
				tree_name: newName,
				kpiDescription: newDescr,
				kpiOutcome: newOutcome
			}						
			}).then(function()
			{})
		}
		newItem = "False";
		totalItems = newId;
	}
	currentCount--;
	if(currentCount <= 0)
	{
		currentCount = 1;
		return;
	}
	firstObject = objStore.get(currentCount);
	dojo.byId("objName").value = firstObject.objName;
	dojo.byId("objOwner").value = firstObject.objOwner;
	dojo.byId("objPersp").value = firstObject.objPersp;
	dojo.byId("objDescr").value = firstObject.objDescr;
	dojo.byId("objOutcome").value = firstObject.objOutcome;
	dojo.byId("objFrom").value = firstObject.objFrom;
	dojo.byId("objTo").value = firstObject.objTo;
	dojo.byId("objKpi").value = firstObject.objKpi;
	dojo.byId("objInitiative").value = firstObject.objInitiative;
	dojo.byId("objTarget").value = firstObject.objTarget;
	//dojo.byId("objLinkedTo").value = firstObject.objLinkedTo;
	dojo.byId("objNo").innerHTML = firstObject.id;
}
nextPage = function()
{
	var editData = {};
	editData["id"] = currentCount;
	editData["objName"] = dojo.byId("objName").value;
	editData["objOwner"] = dojo.byId("objOwner").value;
	editData["objPersp"] = dojo.byId("objPersp").value;
	editData["objDescr"] = dojo.byId("objDescr").value;
	editData["objOutcome"] = dojo.byId("objOutcome").value;
	editData["objFrom"] = dojo.byId("objFrom").value;
	editData["objTo"] = dojo.byId("objTo").value;
	editData["objKpi"] = dojo.byId("objKpi").value;
	editData["objInitiative"] = dojo.byId("objInitiative").value;
	editData["objTarget"] = dojo.byId("objTarget").value;
	editData["objLinkedTo"] = "org4";
	
	var previousData = objStore.get(currentCount);
	
	var isSimilar = equals(editData, previousData);
	if(isSimilar)
		edit = "False";
	else
		{
			objStore.remove(editData["id"]);
			objStore.put({id:editData["id"], objName: editData["objName"], objPersp: editData["objPersp"], objOwner: editData["objOwner"], objDescr: editData["objDescr"], objOutcome: editData["objOutcome"], objFrom: editData["objFrom"], objTo: editData["objTo"], objKpi: editData["objKpi"], objInitiative: editData["objInitiative"], objTarget: editData["objTarget"], objLinkedTo: "org4"});
			edit = "True";
			request.post("save-commentary.php",{
			//handleAs: "json",
			data: {
				edit: edit,
				id:editData["id"],
				objName: editData["objName"],
				objPersp: editData["objPersp"],
				objOwner: editData["objOwner"],
				//objTeam: newTeam,
				objDescr: editData["objDescr"],
				objOutcome: editData["objOutcome"],
				objFrom: ["objFrom"],
				objTo: ["objTo"],
				objKpi: ["objKpi"],
				objInitiative: ["objInitiative"],
				objTarget: ["objTarget"],
				objLinkedTo: "org4"
			}						
			}).then(function() 
			{
				edit = "False";
			});
			//alert(newName);
			if(newName!=null)
			{
				request.post("save-tree.php",{
				handleAs: "json",
				data: {
					tree_edit: "NULL",
					tree_type: "objective",
					tree_parent: newPersp,
					tree_name: newName,
					kpiDescription: newDescr,
					kpiOutcome: newOutcome
				}						
				}).then(function()
				{})
			}
		}
	
	if(newItem == "True" && dojo.byId("objName").value != "")//add and save new item
	{
		var newId = totalItems + 1;
		var newName = dojo.byId("objName").value;
		var newOwner = dojo.byId("objOwner").value;
		var newPersp = dojo.byId("objPersp").value;
		var newDescr = dojo.byId("objDescr").value;
		var newOutcome = dojo.byId("objOutcome").value;
		var newFrom = dojo.byId("objFrom").value;
		var newTo = dojo.byId("objTo").value;
		var newKpi = dojo.byId("objKpi").value;
		var newInitiative = dojo.byId("objInitiative").value;
		var newTarget = dojo.byId("objTarget").value;
		//var objLinkedTo = dojo.byId("objLinkedTo").value;
		//alert("Save details");
		objStore.put({id:newId, objName: newName, objOwner: newOwner, objPersp: newPersp, objDescr: newDescr, objOutcome: newOutcome, objFrom: newFrom, objTo: newTo, objKpi: newKpi, objInitiative: newInitiative, objTarget: newTarget, objLinkedTo: "org4"});
		request.post("save-commentary.php",{
		//handleAs: "json",
		data: {
				id:newId,
				objName: newName,
				objOwner: newOwner,
				objPersp: newPersp,
				//objTeam: newTeam,
				objDescr: newDescr,
				objOutcome: newOutcome,
				objFrom: newFrom,
				objTo: newTo,
				objKpi: newKpi,
				objInitiative: newInitiative,
				objTarget: newTarget,
				objLinkedTo: "org4"
			}						
		}).then(function(data) 
		{
			//alert(data);
		});
		if(newName != null)
		{
			request.post("save-tree.php",{
			handleAs: "json",
			data: {
				tree_edit: "NULL",
				tree_type: "objective",
				tree_parent: newPersp,
				tree_name: newName,
				kpiDescription: newDescr,
				kpiOutcome: newOutcome
			}						
			}).then(function()
			{})
		}
		newItem = "False";
		totalItems = newId;
	}
	currentCount++;
	if(currentCount > totalItems)//create new page for a new objective commentary
	{
		currentCount = totalItems+1;
		dojo.byId("objName").value = null;
		dojo.byId("objOwner").value = null;
		dojo.byId("objPersp").value = null;
		dojo.byId("objDescr").value = null;
		dojo.byId("objOutcome").value = null;
		dojo.byId("objFrom").value = null;
		dojo.byId("objTo").value = null;
		dojo.byId("objKpi").value = null;
		dojo.byId("objInitiative").value = null;
		dojo.byId("objTarget").value = null;
		//dojo.byId("objLinkedTo").value = null;
		dojo.byId("objNo").innerHTML = currentCount;
		newItem = "True";
	}
	else
	{
		firstObject = objStore.get(currentCount);
		dojo.byId("objName").value = firstObject.objName;
		dojo.byId("objOwner").value = firstObject.objOwner;
		dojo.byId("objPersp").value = firstObject.objPersp;
		dojo.byId("objDescr").value = firstObject.objDescr;
		dojo.byId("objOutcome").value = firstObject.objOutcome;
		dojo.byId("objFrom").value = firstObject.objFrom;
		dojo.byId("objTo").value = firstObject.objTo;
		dojo.byId("objKpi").value = firstObject.objKpi;
		dojo.byId("objInitiative").value = firstObject.objInitiative;
		dojo.byId("objTarget").value = firstObject.objTarget;
		//dojo.byId("objLinkedTo").value = firstObject.objLinkedTo;
		dojo.byId("objNo").innerHTML = firstObject.id;
	}
}
deletePage = function()
{
	objStore.remove(currentCount);
	//alert("test:" + currentCount);
	request.post("save-commentary.php",{
	//handleAs: "json",
	data: {
		delCommentary: "True",
		id: currentCount
	}						
	}).then(function(data) 
	{
		//alert("Deleted? "+data);
		delCommentary = "False";
	});	
	request.post("delete-tree.php",{
		handleAs: "json",
		data: {
			tree_type: "objective",
			tree_id: currentCount,
		}						
		}).then(function()
		{})
}

});

});
</script>
</head>
<body class="claro">
    
    <table width="100%">
    <tr>
    	<td width="20%"><button data-dojo-type="dijit/form/Button" onClick="previousPage()" type="submit">Previous</button>
        <button data-dojo-type="dijit/form/Button" onClick="nextPage()" type="submit">Next</button></td>
        <td width="80%" align="right"><button data-dojo-type="dijit/form/Button" onClick="deletePage()" type="submit">Delete</button></td>
    </tr>
    </table>
   
    <table width="100%" height="90%" style="border: 1px solid black; border-collapse:collapse;">
    <tr><th colspan="2" style="border-bottom:1px solid black;background-color: #aabcfe; padding:5px;"><b>Strategic Objectives Commentary Worksheet</b></th>
    <td align="center" style="border-bottom:1px solid black; background-color: #aabcfe;">Objective No: <span id="objNo"></span></td></tr>
    <tr><td colspan="2" style="border-bottom:1px solid black;">
    <label><strong>Objective Name:</strong></label><input type='text' name='objName' id="objName" size="80%" style="border:hidden;"/></td>
    <td style="border-bottom:1px solid black;">
        <table cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
        <tr>
        <td><label><strong>Perspective:</strong></label><input type='text' id="objPersp" style="border:hidden;"/></td>
        <td><div style="float:left" onClick="addPersp()">(add)</div></td>
        </tr>
        </table>
    </td>
    </td>
    </tr>
    <tr><td colspan="3" style="border-bottom:1px solid black;">
    <label><strong>Objective Owner:</strong></label><input type='text' name='objOwner' id="objOwner" size="100%" style="border:hidden;"/></td></tr>
    <tr><td colspan="3" style="border-bottom:1px solid black;">
    <label><strong>Objective Team:</strong></label><input type='text' name='objTeam' id="objTeam" size="100%"  style="border:hidden;"/></td></tr>
    <tr valign="top">
    	<td rowspan="2"  style="border-bottom:1px solid black;"><strong>Commentary:</strong><br>(This means...; this includes...)<br><br><br>If we do this what will it look like?</td>
        <td colspan="2"  style="border-bottom:1px solid black;border-left:1px solid black;">
            <table width="100%" height="100%" style="border-collapse:collapse;">
                <tr>
                    <td valign="top" width="15%" style="border-right:1px dotted black;"><strong>Description:</strong></td>
                    <td height="100%"><textarea name='objDescr' id="objDescr" 
            style="width: 100%; height:100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border:hidden;"></textarea>
            </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
    	<td colspan="2" style="border-left:1px solid black;border-bottom:1px solid black;">
            <table width="100%" height="100%" style="border-collapse:collapse;">
                <tr>
                    <td valign="top" width="15%" style="border-right:1px dotted black;"><strong>Outcome:</strong></td>
                    <td height="100%"><textarea name='objOutcome' id="objOutcome" 
                style="width: 100%; height:100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border:hidden;"></textarea>
            </td>
                </tr>
            </table>
        </td>
    </tr>
    
    <tr>
    	<td rowspan="2"  style="border-right:1px solid black; border-bottom:1px solid black;" valign="top"><strong>Destination:</strong><br>What are you moving to?<br><br>What change do you want to create?</td>
        <td><label><strong>From:</strong></label>
        </td>
        <td><label><strong>To:</strong></label>
        </td>
    </tr>
    <tr><td style="border-bottom:1px solid black;" height="100%"><textarea name='objFrom' id="objFrom" 
       	style="width: 100%; height:100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;"></textarea>
        </td>
        <td style="border-bottom:1px solid black;" height="100%">
        <textarea name='objTo' id="objTo" 
        style="width: 100%; height:100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;"></textarea>
        </td>
    </tr>
    <tr>
    	<td rowspan="2" style="border-right:1px solid black; border-bottom:1px solid black;"><strong>Measures/Targets:</strong><br>How will we know if we are changing the results?<br><br>What does great performance look like?</td>
        <td>
        <label><strong>Performance Measure:</strong></label>
        </td>
        <td><label><strong>Target:</strong></label></td>
    </tr>
    
    <tr><td height="100%"><textarea name='objKpi' id="objKpi" 
            style="width:100%; height:100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;"></textarea>
        </td>
        <td height="100%">
       <textarea name='objTarget' id="objTarget" style="width:100%; height:100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;"></textarea>
        </td>
    </tr>
    
     <tr>
    	<td width="30%" style="border-right:1px solid black;"><strong>Initiatives:</strong><br>What projects will move performance of our Objectives toward our targets?</td>
        <td colspan="2" width="70%" style="border-top:1px solid black;"><textarea name='objInitiative' id="objInitiative"  style="border: none; width: 100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;"></textarea></td>
    </tr>
    </table>
    
    <div class="dijitHidden">
        <div data-dojo-type="dijit/Dialog" data-dojo-props="title:'New Perspective'" id="newPerspDialog">
        	<table>
            	<tr>
                <td>
                	Perspective Name
                </td>
                <td><input type='text' style='width:90%' id='perspName'/></td>
                </tr>
                <tr>
                <td>
                	Organization
                </td>
                <td><input type='text' style='width:90%' id='orgName'/></td>
                </tr>
                
                <tr>
                    <td>
                    <button data-dojo-type="dijit/form/Button" data-dojo-props="onClick:savePersp" type="submit">Finish</button>
                    </td>
                </tr>
            </table>
   		</div>
	</div>
    
</body>
</html>