<!DOCTYPE html>
<!-- release v4.4.9, copyright 2014 - 2018 Kartik Visweswaran -->
<!--suppress JSUnresolvedLibraryURL -->
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title></title>
    <link rel="stylesheet" href="https://accent-analytics.com/bootstrap/4.5.0/dist/css/bootstrap.min.css" media="all">
    <link href="https://accent-analytics.com/bootstrap_fileinput/5.2.6/css/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
    <link href="https://accent-analytics.com/font-awesome-5.15.3/css/all.css" media="all" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="https://accent-analytics.com/dijit/themes/soria/soria.css" media="all">
    
	<script src="https://accent-analytics.com/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://accent-analytics.com/bootstrap_fileinput/5.2.6/js/fileinput.min.js" type="text/javascript"></script>
    <script src="https://accent-analytics.com/bootstrap_fileinput/5.2.6/themes/fas/theme.min.js" type="text/javascript"></script>
    <script src="https://accent-analytics.com/popper/popper.min.js" type="text/javascript"></script>
    <script src="https://accent-analytics.com/bootstrap/4.5.0/dist/js/bootstrap.min.js"></script>
    
	<script type="text/javascript" src="https://accent-analytics.com/dojo/dojo.js" data-dojo-config="async: true, parseOnLoad:false"></script>
</head>
<body class="soria">
	<i class="fa fa-paperclip" aria-hidden="true"></i>
    <!--<div class="file-loading"><input id="fileUpload" name="input-pd[]" type="file" multiple></div>-->
</body>
<script>
require([
"dojo/dom",
"dojo/request",
"dijit/Dialog",
"dojo/domReady!"], function(dom, request, Dialog)
{	
supportingDocuments = function(objectId)
{
	var content = '<div class="file-loading"><input id="fileUpload" name="input-pd[]" type="file" multiple></div>'
	//content = content + '<button onClick="closeFileDialog()">Back</button>';
	
	fileDialog = new Dialog({
		title: "Supporting / Related Documents",
		content: content,
		style: "width: 80%"
	});
	fileDialog.show().then(function() {
    fileDialog.resize();//Dialog was not centered on load. Adding this centered it!!! LTK 11 May 2021 1314 Hrs
   // fileDialog.resize();
	});
	
	closeFileDialog = function()
	{
		fileDialog.hide();
	}
	
	request.post("database/get-documents.php",{
	handleAs: "json",
	data: {
		objectId: objectId
	}						
	}).then(function(evidence) 
	{
		var evidenceCount = 0, inputFileCount = 0, fileId, inputFile = ''; var inputName;
		
		//dom.byId("fileUpload").innerHTML = evidence.fileUpload;
		
		var documentCount = 0;
		var initialPreviewLinks = [];
		var initialPreviewConfigLinks = [];
		fileId = "#"+evidence.id;
		inputName = evidence.id;
		while(documentCount < evidence.documents.length)
		{
			initialPreviewLinks[documentCount] = evidence.documents[documentCount].documentLocation;
			initialPreviewConfigLinks[documentCount] = 
			{
				type: evidence.documents[documentCount].documentType,
				caption:evidence.documents[documentCount].documentName, 
				size: evidence.documents[documentCount].documentSize, 
				width: "120px", 
				url:"delete.php", 
				downloadUrl: evidence.documents[documentCount].documentLocation, 
				key: evidence.documents[documentCount].documentId
			};
			documentCount++;
		}
		//$(fileId).fileinput({
		$("#fileUpload").fileinput({
			'theme': 'fas',
			uploadUrl: 'file.php',
			overwriteInitial: false,
			showUpload: true,
			showCaption: false,
			browseClass: "btn btn-outline-primary btn-sm",
			removeClass: "btn btn-outline-primary btn-sm ",
			removeLabel: "Delete",
			uploadClass: "btn btn-outline-primary btn-sm ",
			//fileType: "any",
			previewFileType: "any",
			initialPreviewFileType: 'image',
			initialPreviewAsData: true,
			initialPreview: initialPreviewLinks,
			initialPreviewConfig: initialPreviewConfigLinks, 
			deleteUrl: "delete.php",
			uploadExtraData:{inputName:inputName},
			layoutTemplates: {
            main2: '{preview}\n' +
					'<div class="kv-upload-progress hide"></div>\n' +
					'{browse}\n{cancel}\n{upload}\n{remove}\n' +
					"<button class='btn btn-outline-primary btn-sm' onClick='closeFileDialog()'>" +
					"<i class='fa fa-window-close'></i> Close Window</button>\n" +
					"</div>\n",
					
			preview:'<div class="file-preview {class}">\n' +
					'    <div class="{dropClass}">\n' +
					'    <div class="file-preview-thumbnails">\n' +
					'    </div>\n' +
					'    <div class="clearfix"></div>' +
					'    <div class="file-preview-status text-center text-success"></div>\n' +
					'    <div class="kv-fileinput-error"></div>\n' +
					'    </div>\n' +
					'</div>',
			/*modal: '<div class="modal-dialog modal-lg{rtl}" role="document">\n' +
					'  <div class="modal-content">\n' +
					'    <div class="modal-header">\n' +
					'      <h6 class="modal-title">{heading} <small><span class="kv-zoom-title"></span></small></h6>\n' +
					'      <div class="kv-zoom-actions pull-right">{toggleheader}{fullscreen}{borderless}{close}\n'+
					/*"			<button class='btn btn-outline-secondary btn-sm' onClick='closeModal()'>" +
					"			<i class='fa fa-window-close'></i></button>\n" +*/
					/*'		</div>\n' +
					'    </div>\n' +
					'    <div class="modal-body">\n' +
					'      <div class="floating-buttons"></div>\n' +
					'      <div class="kv-zoom-body file-zoom-content"></div>\n' + '{prev} {next}\n' +
					'    </div>\n' +
					'  </div>\n' +
					'</div>\n'*/
        	}
		});
		closeModal = function()
		{
			$('.modal').modal('hide');//Preview modal wasn't working so added this to bruteforce it to close. LTK 11 May 2021 2125hrs. Problem occurs when you use bootstrap 5.0.0
			$.fn.modal.Constructor.prototype.enforceFocus;
		}
	});//end of request.post for get-documents.php
}//end of function supportingDocuments
supportingDocuments("3");
});//end of dojo request
</script>
</html>