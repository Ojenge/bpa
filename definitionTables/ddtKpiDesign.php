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
var orgId, perspId, select1, perspStore, firstObject;
	
/*var storeData2 = [{id:1, objNameD: 'Improve Customer Satisfaction', potential: 'Tom Oiende', picture: "Description goes here", kpiName: "Outcome goes here...", objFrom: "From goes here...", objTo: "Destination to goes here...", objKpi: "Candidate Measures go here...", objInitiative: "Candidate initiatives go here", objTarget: "3.01", kpiLinkedTo: "org1"},
    {id:2, objNameD: 'Improve Brand Image', potential: 'Kimani Kim', picture: "Description goes here", kpiName: "Outcome goes here...", objFrom: "From goes here...", objTo: "Destination to goes here...", objKpi: "Candidate Measures go here...", objInitiative: "Candidate initiatives go here", objTarget: "3.01", kpiLinkedTo: "org1"},
    {id:3, objNameD: 'Increase Profitability', potential: 'Ole Safari', picture: "Description goes here", kpiName: "Outcome goes here...", objFrom: "From goes here...", objTo: "Destination to goes here...", objKpi: "Candidate Measures go here...", objInitiative: "Candidate initiatives go here", objTarget: "3.01", kpiLinkedTo: "org1"},
    {id:4, objNameD: 'Reduce Costs', potential: 'Mwana Aisha', picture: "Description goes here", kpiName: "Outcome goes here...", objFrom: "From goes here...", objTo: "Destination to goes here...", objKpi: "Candidate Measures go here...", objInitiative: "Candidate initiatives go here", objTarget: "3.01", kpiLinkedTo: "org1" }];*/

var storeData;

request.post("get-kpi-design.php",{
handleAs: "json",
data: {
}						
}).then(function(data) 
{
	var currentCount = 1;     // Current size of the page.
	var newItem = "False";
	var kpiLinkedTo = "org1";
	var edit;
	storeData = data;
		//alert(storeData + storeData2);
	  
	var kpiStore = new Memory({data: storeData});
	
	var totalItems = storeData.length;   // How many total items should we expect.
	
	firstObject = kpiStore.get(currentCount);
	//alert(firstObject);
	dojo.byId("objNameD").value = firstObject.objNameD;
	dojo.byId("sensory").value = firstObject.sensory;
	dojo.byId("potential").value = firstObject.potential;
	dojo.byId("picture").value = firstObject.picture;
	dojo.byId("kpiName").value = firstObject.kpiName;
	//dojo.byId("kpiLinkedTo").value = firstObject.kpiLinkedTo;
	dojo.byId("kpiNo").innerHTML = firstObject.id;

previousPage = function()
{
	var editData = {};
	editData["id"] = currentCount;
	editData["objNameD"] = dojo.byId("objNameD").value;
	editData["sensory"] = dojo.byId("sensory").value;
	editData["potential"] = dojo.byId("potential").value;
	editData["picture"] = dojo.byId("picture").value;
	editData["kpiName"] = dojo.byId("kpiName").value;
	editData["kpiLinkedTo"] = "org1";
	
	var previousData = kpiStore.get(currentCount);
	
	var isSimilar = equals(editData, previousData);
	if(isSimilar)
		edit = "False";
	else
		{
			kpiStore.remove(editData["id"]);
			kpiStore.put({id:editData["id"], objNameD: editData["objNameD"], sensory: editData["sensory"], potential: editData["potential"], picture: editData["picture"], kpiName: editData["kpiName"], kpiLinkedTo: "org1"});
			edit = "True";
			request.post("save-kpi-design.php",{
			//handleAs: "json",
			data: {
				edit: edit,
				id:editData["id"],
				objNameD: editData["objNameD"],
				potential: editData["potential"],
				sensory: editData["sensory"],
				//objTeam: newTeam,
				picture: editData["picture"],
				kpiName: editData["kpiName"],
				kpiLinkedTo: "org1"
			}						
			}).then(function() 
			{
				edit = "False";
			});		
		}
			
	if(newItem == "True" && dojo.byId("objNameD").value != "")//add and save new item
	{
		var newId = totalItems + 1;
		var newName = dojo.byId("objNameD").value;
		var newSensory = dojo.byId("sensory").value;
		var newPotential = dojo.byId("potential").value;
		var newPicture = dojo.byId("picture").value;
		var newKpi = dojo.byId("kpiName").value;
		//var kpiLinkedTo = dojo.byId("kpiLinkedTo").value;
		//alert("Save details");
		kpiStore.put({id:newId, objNameD: newName, potential: newPotential, sensory: newSensory, picture: newPicture, kpiName: newKpi, kpiLinkedTo: "org1"});
		request.post("save-kpi-design.php",{
		//handleAs: "json",
		data: {
				id:newId,
				objNameD: newName,
				potential: newPotential,
				sensory: newSensory,
				picture: newPicture,
				kpiName: newKpi,
				kpiLinkedTo: "org1"// this should not be hard coded!!! - link it to newSensory id!
			}						
		}).then(function(data) 
		{
			//alert(data);
		});
		
		newItem = "False";
		totalItems = newId;
	}
	currentCount--;
	if(currentCount <= 0)
	{
		currentCount = 1;
		return;
	}
	//alert(json.stringify(kpiStore) + currentCount);
	firstObject = kpiStore.get(currentCount);
	//alert(currentCount);
	dojo.byId("objNameD").value = firstObject.objNameD;
	dojo.byId("potential").value = firstObject.potential;
	dojo.byId("sensory").value = firstObject.sensory;
	dojo.byId("picture").value = firstObject.picture;
	dojo.byId("kpiName").value = firstObject.kpiName;
	//dojo.byId("kpiLinkedTo").value = firstObject.kpiLinkedTo;
	dojo.byId("kpiNo").innerHTML = firstObject.id;
}
nextPage = function()
{
	var editData = {};
	editData["id"] = currentCount;
	editData["objNameD"] = dojo.byId("objNameD").value;
	editData["potential"] = dojo.byId("potential").value;
	editData["sensory"] = dojo.byId("sensory").value;
	editData["picture"] = dojo.byId("picture").value;
	editData["kpiName"] = dojo.byId("kpiName").value;
	editData["kpiLinkedTo"] = "org1";
	
	var previousData = kpiStore.get(currentCount);
	
	var isSimilar = equals(editData, previousData);
	if(isSimilar)
		edit = "False";
	else
		{
			kpiStore.remove(editData["id"]);
			kpiStore.put({id:editData["id"], objNameD: editData["objNameD"], sensory: editData["sensory"], potential: editData["potential"], picture: editData["picture"], kpiName: editData["kpiName"], kpiLinkedTo: "org1"});
			edit = "True";
			request.post("save-kpi-design.php",{
			//handleAs: "json",
			data: {
				edit: edit,
				id:editData["id"],
				objNameD: editData["objNameD"],
				sensory: editData["sensory"],
				potential: editData["potential"],
				//objTeam: newTeam,
				picture: editData["picture"],
				kpiName: editData["kpiName"],
				kpiLinkedTo: "org1"
			}						
			}).then(function() 
			{
				edit = "False";
			});
		}
	
	if(newItem == "True" && dojo.byId("objNameD").value != "")//add and save new item
	{
		var newId = totalItems + 1;
		var newName = dojo.byId("objNameD").value;
		var newPotential = dojo.byId("potential").value;
		var newSensory = dojo.byId("sensory").value;
		var newPicture = dojo.byId("picture").value;
		var newKpi = dojo.byId("kpiName").value;
		//var kpiLinkedTo = dojo.byId("kpiLinkedTo").value;
		//alert("Save details");
		kpiStore.put({id:newId, objNameD: newName, potential: newPotential, sensory: newSensory, picture: newPicture, kpiName: newKpi, kpiLinkedTo: "org1"});
		request.post("save-kpi-design.php",{
		//handleAs: "json",
		data: {
				id:newId,
				objNameD: newName,
				potential: newPotential,
				sensory: newSensory,
				//objTeam: newTeam,
				picture: newPicture,
				kpiName: newKpi,
				kpiLinkedTo: "org1"
			}						
		}).then(function(data) 
		{
			//alert(data);
		});
		newItem = "False";
		totalItems = newId;
	}
	currentCount++;
	if(currentCount > totalItems)//create new page for a new objective commentary
	{
		currentCount = totalItems+1;
		dojo.byId("objNameD").value = null;
		dojo.byId("potential").value = null;
		dojo.byId("sensory").value = null;
		dojo.byId("picture").value = null;
		dojo.byId("kpiName").value = null;
		//dojo.byId("kpiLinkedTo").value = null;
		dojo.byId("kpiNo").innerHTML = currentCount;
		newItem = "True";
	}
	else
	{
		firstObject = kpiStore.get(currentCount);
		dojo.byId("objNameD").value = firstObject.objNameD;
		dojo.byId("potential").value = firstObject.potential;
		dojo.byId("sensory").value = firstObject.sensory;
		dojo.byId("picture").value = firstObject.picture;
		dojo.byId("kpiName").value = firstObject.kpiName;
		//dojo.byId("kpiLinkedTo").value = firstObject.kpiLinkedTo;
		dojo.byId("kpiNo").innerHTML = firstObject.id;
	}
}
deletePage = function()
{
	kpiStore.remove(currentCount);
	//alert("test:" + currentCount);
	request.post("save-kpi-design.php",{
	//handleAs: "json",
	data: {
		delDesign: "True",
		id: currentCount
	}						
	}).then(function(data) 
	{
		//alert("Deleted? "+data);
		delDesign = "False";
	});	
}
request.post("layout/get-objectives.php",{
handleAs: "json",
data: {
}						
}).then(function(objData)
{		
	objStore = new Memory({data:objData});
	
	objSelect = new FilteringSelect({
	name: "objSelectManager",
	store: objStore,
	searchAttr: "Objective",
	maxHeight: -1, 
	onChange: function(){
		objId = this.item.id;
	}
	}, "objNameD");
	objSelect.startup();
});


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
   
    <table width="100%" height="90%" style="border: 1px solid black; border-collapse:collapse;" border="1">
    <tr><th style="border-bottom:1px solid black;background-color: #aabcfe; padding:5px;"><b>Measure Design</b></th>
    	<td align="center" style="border-bottom:1px solid black; background-color: #aabcfe;">Measure No: <span id="kpiNo"></span></td>
    </tr>
    <tr>
    	<td colspan="2" style="border-bottom:1px solid black;">
    	<label><strong>Objective Name:</strong></label><input type='text' name='objNameD' id="objNameD" size="80%" style="border:hidden;"/>
    	</td>
    </tr>
    
    <tr><td style="border-right:1px solid black;" width="20%"><strong>begin with the end in mind</strong><br><br>
    <em>write down the result you want to measure, from your Results Map</em>
    </td>
    <td height="100%" width="80%"><textarea name='potential' id="potential" 
            style="width: 100%; height:100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border:hidden;"></textarea>
            </td>
    </tr>
    <tr><td style="border-right:1px solid black;border-top:1px solid black;"><strong>be sensory specific</strong><br><br>
<em>    what would people see, hear, feel or do if this result were actually happening?
avoid using inert language like “enhanced” or “effective” or “accountable” – use sensory rich language as it will be easier to design measures for</em>
    </td>
    <td height="100%"><textarea name='sensory' id="sensory" 
            style="width: 100%; height:100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border:hidden;"></textarea>
            </td>
    </tr>
    <tr><td style="border-right:1px solid black;border-top:1px solid black;"><strong>find potential measures</strong><br><br>
<em><em>    go back to the ‘be sensory specific’ section and list the things you could potentially physically count as evidence of the outcome
for each potential performance measure you list, rate its strength relative to your result, and its feasibility in being brought to life, as High, Medium or Low
</em></em>
    </td>
    <td height="100%"><textarea name='kpiMeasures' id="kpiMeasures" 
            style="width: 100%; height:100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border:hidden;"></textarea>
            </td>
    </tr>
    <tr><td style="border-right:1px solid black;border-top:1px solid black;"><strong>check the bigger picture</strong><br><br>
<em>    what could be the unintended consequences of achieving this result?<br>
what other result might you need to track to avoid these </em>
    </td>
    <td height="100%"><textarea name='picture' id="picture" 
            style="width: 100%; height:100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border:hidden;"></textarea>
            </td>
    </tr>
    <tr><td style="border-right:1px solid black;border-top:1px solid black;"><strong>name the measure(s)</strong><br><br>
<em>    select the best measures above that rated highest for both strength and feasibility<br>
decide what to call the measure, and write a sentence describing it that makes its calculation clear as possible
</em>
    </td>
    <td height="100%" style="border-top:1px solid black;"><textarea name='kpiName' id="kpiName" 
            style="width: 100%; height:100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border:hidden;"></textarea>
            </td>
    </tr>
   </table>
</body>
</html>