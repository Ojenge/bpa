<link rel="stylesheet" href="css/connectDnd.css">
<link rel="stylesheet" href="../dijit/themes/soria/soria.css">
<!--links for DataTables CSS and js files-->
<link rel="stylesheet" href="css/jquery.dataTables.min.css">
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<style>
	@import url("../../dojo/resources/dojo.css");
	@import url("../../dojo/resources/dnd.css");
	@import url("css/site-2.css");
	@import url("css/dnd.css");
</style>

<script src="../../dojo/dojo.js" data-dojo-config="async: true, parseOnLoad:true"></script>
<script type="text/javascript" src="js/connectDnd.js"></script>
<script type="text/javascript">
require([
//"dijit/registry",
"dojo/dom-class",
"dojo/request",
"dojo/dom",
"dojo/dom-style",
'dojo/dnd/Source',
//"dojo/json",
//"dijit/form/Button",
"dijit/layout/TabContainer",
"dijit/layout/ContentPane",
"dojo/domReady!"
], function(domClass, request, dom, domStyle, Source, TabContainer, ContentPane)
{
	//if(dijit.byId("selectObjectDialog")) dijit.byId("selectObjectDialog").destroyRecursive();
	var tabWait = setTimeout(function(){
		//on load ensure that the link tab is loaded first before any other
		//the rest are diasabled by default
		dijit.byId("sqlTab").set('disabled', true);
		//dijit.byId("linkTab").set('disabled', true);
		dijit.byId("scheduleTab").set('disabled', true);
		dijit.byId("dbNextButton").set('disabled', true);
	}, 630)

	//checks the type of db selected by the user
	//and blanks out the fields accordingly
	dbTypeFunction = function()
	{
		if(dijit.byId("dbType").value == "")
		{
			dijit.byId("host").set('disabled', true);
			dijit.byId("user").set('disabled', true);
			dijit.byId("password").set('disabled', true);
			dijit.byId("dbName").set('disabled', true);
			dijit.byId("port").set('disabled', true);
			dijit.byId("port").set("value", '');
			dom.byId("dbTypeVar").innerHTML = "";
		}else{
			dijit.byId("host").set('disabled', false);
			dijit.byId("user").set('disabled', false);
			dijit.byId("password").set('disabled', false);
			dijit.byId("dbName").set('disabled', false);
			dijit.byId("port").set('disabled', false);
			dom.byId("dbTypeVar").innerHTML = dijit.byId("dbType").get("value");

			//sets the default value of the ports based on convention
			switch(dijit.byId("dbType").get("value"))
			{
				case "MySQL":
				{
					dijit.byId("port").set("value", '3306');
					break;
				}
				case "Oracle":
				{
					dijit.byId("port").set("value", '1521');
					break;
				}
				case "MS SQL":
				{
					dijit.byId("port").set("value", '1433');
					break;
				}
				case "PG SQL":
				{
					dijit.byId("port").set("value", '5432');
					break;
				}
			}//end of switch statement
		}//end of else statement
	}//end of dbTypeFunction

	dbConnectFunction = function()
	{
		dijit.byId("sqlTab").set('disabled', false);
	}//end of dbConnectFunction

	//called when you are done with a particular tab
	////in this case the sql tab
	activateNext = function()
	{
		//console.log("host: " + dijit.byId("host").get("value").length);
		//removed the blank password check cause some dbs don't have passwords
		// && dijit.byId("password").get("value") != ''
		if(dijit.byId("host").get("value").length >= 3 && dijit.byId("user").get("value") != '' && dijit.byId("dbName").get("value") != '' && dijit.byId("port").get("value") != '')
		{
			dijit.byId("dbNextButton").set('disabled', false);
			dijit.byId("sqlTab").set('disabled', false);
		}
		else
		{
			dijit.byId("dbNextButton").set('disabled', true);
			dijit.byId("sqlTab").set('disabled', true);
		}
	}//end of activateNext Function

	dbNext = function()
	{
		var pane = dijit.byId('sqlTab');
		var container = dijit.byId('connectContainer'); // you may also be able to do pane.getParent(), but i'm not sure
		container.selectChild(pane);
	}//end of dbNext function

	dbBack = function()
	{
		var pane = dijit.byId('linkTab');
		var container = dijit.byId('connectContainer'); // you may also be able to do pane.getParent(), but i'm not sure
		container.selectChild(pane);
	}//end of dbBack function

	sqlNext = function()
	{
		var pane = dijit.byId('scheduleTab');
		var container = dijit.byId('connectContainer'); // you may also be able to do pane.getParent(), but i'm not sure
		container.selectChild(pane);
	}//end of sqlNext function

	sqlBack = function()
	{
		var pane = dijit.byId('dbTab');
		var container = dijit.byId('connectContainer'); // you may also be able to do pane.getParent(), but i'm not sure
		container.selectChild(pane);
	}//end of sqlBack function

	//the function after linke Measure tab.
	linkBack = function()
	{
		var pane = dijit.byId('sqlTab');
		var container = dijit.byId('connectContainer'); // you may also be able to do pane.getParent(), but i'm not sure
		container.selectChild(pane);
	}//end of linkBack function

	linkNext = function()
	{
		var pane = dijit.byId('dbTab');
		var container = dijit.byId('connectContainer'); // you may also be able to do pane.getParent(), but i'm not sure
		container.selectChild(pane);
	}//end of linkNext function

	scheduleBack = function()
	{
		var pane = dijit.byId('linkTab');
		var container = dijit.byId('connectContainer'); // you may also be able to do pane.getParent(), but i'm not sure
		container.selectChild(pane);
	}//end of scheduleBack function
	scheduleFinish = function()
	{
		console.log("Finish Connect Process");
	}//end of scheduleFinish function
	sqlRun = function()
	{
		request.post("connect-run-sql.php",
		{
			handleAs: "json",
			data: {
					dbType: dom.byId("dbTypeVar").innerHTML,
					host:dijit.byId("host").get("value"),
					user: dijit.byId("user").get("value"),
					password: dijit.byId("password").get("value"),
					dbName: dijit.byId("dbName").get("value"),
					port: dijit.byId("port").get("value"),
					sql: dijit.byId("sql").get("value")
				}
		}).then(function(sqlResults)
		{
			console.log(typeof(sqlResults));
			dojo.byId("sqlResults").innerHTML =sqlResults;
			//var results = new Source("sqlResults", { accept: [ "inStock", "outOfStock" ] });
			//var results = new Source("sqlResults");
			// console.log(typeof(results));
			// results.insertNodes(false, sqlResults);
			// results.forInItems(function(item, id, map){
			// 	domClass.add(id, item.type[0]);
			// });
		});
	}
	dateSource = new Source("dateNode");
	valueSource = new Source("valueNode");
	nameSource = new Source("nameNode");
});
</script>
<body class="soria">
<div>
	<!--my variables-->
    <div id="dbTypeVar" style="display:none;"></div>
</div>
<div data-dojo-type="dijit/layout/TabContainer" style="width: 950px; height: 700px;" tabPosition="left-h" tabStrip="true" id="connectContainer">

<!--Start of the Link Measure Tab -->
	<div data-dojo-type="dijit/layout/ContentPane" title="Link Measure" selected="true" id="linkTab">
		<table>
		<tr>
				<td valign="top">
						<table><tr>
								<td valign="top" style="border-top:3px solid #00F;">
										<div id="orgConnect" style="width:250px; height:200px; vertical-align:top;"></div></td>
								<td style="border-top:3px solid #00F;"><div id="perspConnect" style="width:250px; height:200px; top:0px;"></div></td>
								<td style="border-top:3px solid #00F;"><div id="objConnect" style="width:250px; height:200px; top:0px;"></div></td></tr>
						</table>
				</td>
		</tr>
		<tr>
				<td>
								<div id="gridConnect" style="height:200px; width:750px;"></div>
				</td>
		</tr>
		<tr>
				<td>
						<table><tr>
								<td><div id="gridCopyConnect" style="height:100px; width:300px;"></div></td>
								<td valign="top">
										<label style="font-size:14px; font-weight:bold; vertical-align:top;"></label>
										<div id="formula"></div>
								</td>
						</tr></table>
				</td>
		</tr>
		<tr>
		<td align="right">
		<!--<button data-dojo-type="dijit/form/Button" onClick="linkBack()" type="submit" id="linkNextButton">Back</button>-->
		<button data-dojo-type="dijit/form/Button" onClick="linkNext()" type="submit" id="linkBackButton">Next</button>
		</td>
		</tr>
		</table>
	</div>
	<!--End of the Link Measure Tab-->

	<!--The database select tab-->
	<div data-dojo-type="dijit/layout/ContentPane" title="Database Connect" id="dbTab">
    <br>
    <table>
    <tr>
        <td>Name of Connection</td><td><input type="text"  id="connectionName" data-dojo-type="dijit/form/TextBox" onKeyUp="activateNext()" /></td>
    </tr>
    </table>
    <br>
    <hr>
    <br>
    <table>
    <tr>
        <td>Select Database Type</td><td><select id="dbType" data-dojo-type="dijit/form/FilteringSelect" onChange="dbTypeFunction()">
                    <option value=""></option>
                    <option value="MySQL">MySQL</option>
                    <option value="Oracle">Oracle</option>
                    <option value="MS SQL">MS SQL</option>
                    <option value="PG SQL">PG SQL</option>
            	</select></td>
    </tr>
    </table>
    <br>
    <hr>
    <br>
    <table>
    <tr>
        <td>Host:</td>
				<td><input type="text"  id="host" data-dojo-type="dijit/form/TextBox" disabled onKeyUp="activateNext()" /></td>
    </tr>
    <tr>
        <td>Username:</td>
				<td><input type="text"  id="user" data-dojo-type="dijit/form/TextBox" disabled onKeyUp="activateNext()" /></td>
    </tr>
    <tr>
        <td>Password:</td>
				<td><input type="password"  id="password" data-dojo-type="dijit/form/TextBox" disabled onKeyUp="activateNext()" /></td>
    </tr>
    <tr>
        <td>Database Name:</td>
				<td><input type="text"  id="dbName" data-dojo-type="dijit/form/TextBox" disabled onKeyUp="activateNext()" /></td>
    </tr>
    <tr>
        <td>Port</td>
				<td><input type="text" id="port" data-dojo-type="dijit/form/TextBox" disabled onKeyUp="activateNext()" /></td>
    </tr>
    <tr>
			<td></td>
			<td></td>
		</tr>
    <tr>
			<td></td>
			<td align="right">
				<button data-dojo-type="dijit/form/Button" onClick="dbBack()" type="submit" id="dbBackButton">Back</button>
				<button data-dojo-type="dijit/form/Button" onClick="dbNext()" type="submit" id="dbNextButton">Next</button>
			</td>
		</tr>
    </table>
  </div>
	<!--End of the database select tab-->

	<!--Data display part of the module -->
  <div data-dojo-type="dijit/layout/ContentPane" title="SQL (Read Data)" id="sqlTab">
  	<table>
    <tr>
        <td>SQL:</td>
				<td><textarea  rows='8' cols="60" id="sql" data-dojo-type="dijit/form/TextArea" ></textarea></td>
    </tr>
    <tr>
    	<td></td>
    	<td><button data-dojo-type="dijit/form/Button" onClick="sqlRun()" type="submit" id="sqlRunButton">Run SQL</button></td>
    </tr>
    <tr>
    	<td></td>
      <td>
				<table>
            	<tr>
                	<td>SQL Results</td>
              </tr>
              <tr>
                <td valign="top">
									<div>
										<div class="resultsContainer">
												<div id="sqlResults" class="container">
												</div>
										</div>
									</div>
                    <!-- <table><tr><td>Results</td></tr>
                    <tr><td>
											<div class="resultsContainer">
													<div id="sqlResults" class="container">
													</div>
											</div>
                    </td></tr>
                    </table> -->

                </td>
                <td valign="top">
                	<table>
                    	<tr><td>Date</td></tr>
                        <tr><td>
                        	<div class="dateContainer">
                            	<div id="dateColumn" style="display:none;"></div>
                            	<ol id="dateNode" class="container"></ol>
                        	</div>
                        </td></tr>
                        <tr><td>Value</td></tr>
                        <tr><td>
                        	<div class="valueContainer">
                            	<div id="valueColumn" style="display:none;"></div>
                            	<ol id="valueNode" class="container"></ol>
                        	</div>
                        </td></tr>
                        <tr><td>Name of Measure</td></tr>
                        <tr><td>
                        <div class="nameContainer">
                        	<div id="nameColumn" style="display:none;"></div>
                            <ol id="nameNode" class="container"></ol>
                        </div>
                        </td></tr>
                    </table>
                </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr><td></td>
    <td align="right">
    <button data-dojo-type="dijit/form/Button" onClick="sqlBack()" type="submit" id="sqlNextButton">Back</button>
    <button data-dojo-type="dijit/form/Button" onClick="sqlNext()" type="submit" id="sqlBackButton">Next</button>
    </td>
    </tr>
    </table>
  </div>

	<!-- Schedule import part of the module-->
  <div data-dojo-type="dijit/layout/ContentPane" title="Schedule Import" id="scheduleTab">
    Lorem ipsum and all around - last...
    <table>
	    <tr>
		    <td align="right">
		    <button data-dojo-type="dijit/form/Button" onClick="scheduleBack()" type="submit" id="scheduleNextButton">Back</button>
		    <button data-dojo-type="dijit/form/Button" onClick="scheduleFinish()" type="submit" id="scheduleFinishButton">Finish</button>
		    </td>
	    </tr>
    </table>
  </div>
	<!--end pf schedule import part of the module-->
</div>
</body>
<script type="text/javascript">
$(document).ready( function () {
    $('#table_data').DataTable();
} );

</script>
