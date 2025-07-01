require([
"dojo/dom",
"dojo/request",
"dojo/dom-construct",
"dijit/Dialog",
"dojo/domReady!"], function(dom, request, domConstruct, Dialog)
{	
supportingDocuments = function(objectId)
{
	//var content = '<div class="file-loading"><input id="fileUpload" name="input-pd[]" type="file" multiple></div>'
	//content = content + '<button onClick="closeFileDialog()">Back</button>';
	console.log("Files for " + objectId);
	closeFileDialog = function()
	{
		fileDialog.hide();
	}
	
	request.post("fileUploads/database/get-documents.php",{
	handleAs: "json",
	data: {
		objectId: objectId
	}						
	}).then(function(evidence) 
	{
		var evidenceCount = 0, inputFileCount = 0, fileId, inputFile = ''; var inputName;
		//dom.byId("fileUpload").innerHTML = evidence.fileUpload;
		
			fileDialog = new Dialog({
				title: "Supporting / Related Documents",
				content: evidence.fileUpload,
				style: "width: 80%"
			});
			fileDialog.show().then(function() 
			{
				fileDialog.resize();//Dialog was not centered on load. Adding this centered it!!! LTK 11 May 2021 1314 Hrs
			    fileDialog.resize();
				domConstruct.destroy(evidence.id); //As usual, clean up the id for reuse next time this item is clicked again.
			});
		
		
		var documentCount = 0;
		var initialPreviewLinks = [];
		var initialPreviewConfigLinks = [];
		fileId = "#"+evidence.id;
		//console.log("fileId: " + fileId + "; documentCount: " + evidence.documents.length);
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
				url:"fileUploads/delete.php", 
				downloadUrl: evidence.documents[documentCount].documentLocation, 
				key: evidence.documents[documentCount].documentId
			};
			documentCount++;
		}
		$(fileId).fileinput({
		//$("#fileUpload").fileinput({
			'theme': 'fas',
			uploadUrl: 'fileUploads/file.php',
			overwriteInitial: false,
			showUpload: false,
			showBrowse: false,
			showCaption: false,
			showRemove: false,
			browseClass: "btn btn-outline-primary btn-sm",
			removeClass: "btn btn-outline-primary btn-sm ",
			removeLabel: "Delete",
			uploadClass: "btn btn-outline-primary btn-sm ",
			browseOnZoneClick: true,
			//fileType: "any",
			previewFileType: "any",
			initialPreviewFileType: 'office',
			initialPreviewAsData: true,
			initialPreview: initialPreviewLinks,
			initialPreviewConfig: initialPreviewConfigLinks, 
			deleteUrl: "fileUploads/delete.php",
			uploadExtraData:{inputName:inputName},
			/*fileTypeSettings:
			{
				image: function(vType, vName) {
					return (typeof vType !== "undefined") ? vType.match('image.*') && !vType.match(/(tiff?|wmf)$/i) : vName.match(/\.(gif|png|jpe?g)$/i);
				},
				html: function(vType, vName) {
					return (typeof vType !== "undefined") ? vType == 'text/html' : vName.match(/\.(htm|html)$/i);
				},
				office: function (vType, vName) {
					return vType.match(/(word|excel|powerpoint|office)$/i) ||
						vName.match(/\.(docx?|xlsx?|pptx?|pps|potx?)$/i);
				},
				gdocs: function (vType, vName) {
					return vType.match(/(word|excel|powerpoint|office|iwork-pages|tiff?)$/i) ||
						vName.match(/\.(rtf|docx?|xlsx?|pptx?|pps|potx?|ods|odt|pages|ai|dxf|ttf|tiff?|wmf|e?ps)$/i);
				},
				text: function(vType, vName) {
					return typeof vType !== "undefined" && vType.match('text.*') || vName.match(/\.(txt|md|nfo|php|ini)$/i);
				},
				video: function (vType, vName) {
					return typeof vType !== "undefined" && vType.match(/\.video\/(ogg|mp4|webm)$/i) || vName.match(/\.(og?|mp4|webm)$/i);
				},
				audio: function (vType, vName) {
					return typeof vType !== "undefined" && vType.match(/\.audio\/(ogg|mp3|wav)$/i) || vName.match(/\.(ogg|mp3|wav)$/i);
				},
				flash: function (vType, vName) {
					return typeof vType !== "undefined" && vType == 'application/x-shockwave-flash' || vName.match(/\.(swf)$/i);
				},
				object: function (vType, vName) {
					return true;
				},
				other: function (vType, vName) {
					return true;
				}
			},*/
			layoutTemplates: {
            main2: '{preview}\n' +
					'<div class="kv-upload-progress hide"></div>\n' +
					'{browse}\n{upload}\n{remove}\n' +
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
			modal: '<div class="modal-dialog modal-lg{rtl}" role="document">\n' +
					'  <div class="modal-content">\n' +
					'    <div class="modal-header">\n' +
					'      <h6 class="modal-title">{heading} <small><span class="kv-zoom-title"></span></small></h6>\n' +
					'      <div class="kv-zoom-actions pull-right">{toggleheader}{fullscreen}{borderless}\n'+
					"			<button class='btn btn-outline-secondary btn-sm' onClick='closeModal()'>" +
					"			<i class='fa fa-window-close'></i></button>\n" +
					'		</div>\n' +
					'    </div>\n' +
					'    <div class="modal-body">\n' +
					'      <div class="floating-buttons"></div>\n' +
					'      <div class="kv-zoom-body file-zoom-content"></div>\n' + '{prev} {next}\n' +
					'    </div>\n' +
					'  </div>\n' +
					'</div>\n'
        	}
		});
		$(".button").bind('mousedown', function (event) {
            $(this).trigger('click')
        });
		closeModal = function()
		{
			$('.modal').modal('hide');//Preview modal wasn't working so added this to bruteforce it to close. LTK 11 May 2021 2125hrs. Problem occurs when you use bootstrap 5.0.0
			$.fn.modal.Constructor.prototype.enforceFocus;
		}
	});//end of request.post for get-documents.php
}//end of function supportingDocuments
//supportingDocuments("3");
});//end of dojo request