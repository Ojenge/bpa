<!DOCTYPE html>
<!-- release v4.4.9, copyright 2014 - 2018 Kartik Visweswaran -->
<!--suppress JSUnresolvedLibraryURL -->
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title></title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/fileinput.css" media="all" rel="stylesheet" type="text/css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" media="all" rel="stylesheet" type="text/css"/>
    <link href="themes/explorer-fa/theme.css" media="all" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="https://accent-analytics.com/dijit/themes/soria/soria.css">
    
    
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="js/plugins/sortable.js" type="text/javascript"></script>
    <script src="js/fileinput.js" type="text/javascript"></script>
    <script src="themes/explorer-fa/theme.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" type="text/javascript"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="https://accent-analytics.com/dojo/dojo.js" data-dojo-config="async: true, parseOnLoad:false"></script>
</head>
<body>
	<div style="width:500px;" id="fileUpload"></div>
</body>
<script>
require([
"dojo/dom",
"dojo/request",
"dojo/domReady!"], function(dom, request)
{	
	request.post("database/get-documents.php",{
	handleAs: "json",
	data: {
	}						
	}).then(function(evidence) 
	{
		var evidenceCount = 0, inputFileCount = 0, fileId, inputFile = '', inputName;
		while(inputFileCount < evidence.length)
		{
			inputFile = inputFile + evidence[inputFileCount].fileUpload;
			inputFileCount++;
		}
		dom.byId("fileUpload").innerHTML = inputFile;
		
		while(evidenceCount < evidence.length)
		{
			var documentCount = 0;
			var initialPreviewLinks = [];
			var initialPreviewConfigLinks = [];
			//alert("Here " + evidence[evidenceCount].id);
			fileId = "#"+evidence[evidenceCount].id;
			inputName = evidence[evidenceCount].id;
			while(documentCount < evidence[evidenceCount].documents.length)
			{
				initialPreviewLinks[documentCount] = evidence[evidenceCount].documents[documentCount].documentLocation;
				initialPreviewConfigLinks[documentCount] = {
						caption:evidence[evidenceCount].documents[documentCount].documentName, 
						size: evidence[evidenceCount].documents[documentCount].documentSize, 
						width: "120px", 
						url:"delete.php", 
						downloadUrl: evidence[evidenceCount].documents[documentCount].documentLocation, 
						key: evidence[evidenceCount].documents[documentCount].documentId
					};
				documentCount++;
			}
			$(fileId).fileinput({
				'theme': 'explorer-fa',
				'uploadUrl': 'file.php',
				overwriteInitial: false,
				showUpload: true,
				showCaption: true,
				browseClass: "btn btn-success btn-sm",
				removeClass: "btn btn-success btn-sm ",
				uploadClass: "btn btn-success btn-sm ",
				fileType: "any",
				overwriteInitial: false,
				initialPreviewAsData: true,
				initialPreview: initialPreviewLinks,
				initialPreviewConfig: initialPreviewConfigLinks, 
				deleteUrl: "delete.php",
				uploadExtraData:{inputName:inputName}
			});
			evidenceCount++;
		}
	});//end of request.post for get-documents.php
});//end of dojo request
</script>
</html>