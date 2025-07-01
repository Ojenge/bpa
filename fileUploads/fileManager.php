<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>File Manager</title>

<link href="../upload/analytics/gok/fileUploads/css/fileinput.css" media="all" rel="stylesheet" type="text/css"/>
<link href="file:///Macintosh HD/Applications/MAMP/htdocs/font-awesome/font-awesome.min.css" media="all" rel="stylesheet" type="text/css"/>
<link href="../upload/analytics/gok/fileUploads/themes/explorer-fa/theme.css" media="all" rel="stylesheet" type="text/css"/>

</head>

<body>
<script>
var fileId = "#project"+value;
var projectId = value;
var inputName = "project"+value;
request.post("../analytics/gok/gokProjects/get-evidence.php",{
	handleAs: "json",
	data: { projectId: projectId }				
}).then(function(evidence) 
{
	var count = 0;
	var initialPreviewLinks = [];
	var initialPreviewConfigLinks = [];
	while(count < evidence.length)
	{
		initialPreviewLinks[count] = evidence[count].documentLocation;
		initialPreviewConfigLinks[count] = {
				caption:evidence[count].documentName, 
				size: evidence[count].documentSize, 
				width: "120px", 
				url:"../analytics/gok/fileUploads/delete.php", 
				downloadUrl: evidence[count].documentLocation, 
				key: evidence[count].documentId
			};
		count++;
	}
	$(fileId).fileinput({
		//'theme': 'explorer-fa',
		'theme': 'fa',
		'uploadUrl': '../analytics/gok/fileUploads/file.php',
		overwriteInitial: false,
		showUpload: true,
		showCaption: true,
		dropZoneEnabled: true,
		maxFileCount: 10,
		browseClass: "btn btn-primary btn-sm",
		removeClass: "btn btn-primary btn-sm ",
		uploadClass: "btn btn-primary btn-sm ",
		fileType: "any",
		overwriteInitial: false,
		initialPreviewAsData: true,
		initialPreview: initialPreviewLinks,
		initialPreviewConfig: initialPreviewConfigLinks,
		uploadExtraData:{inputName:inputName, type: "Initiative"}
	});
})
</script>
</body>
</html>