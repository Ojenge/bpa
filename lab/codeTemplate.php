<html>
<head>
    <link rel="stylesheet" href="../../../dijit/themes/soria/soria.css" media="all">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../../bootstrap/5.0.0/dist/css/bootstrap.min.css" media="all">
    <!--
.gray-100 {
  background-color: rgba(150, 150, 150, 0.06) !important;
}

.gray-200 {
  background-color: rgba(150, 150, 150, 0.1) !important;
}

.gray-300 {
  background-color: rgba(150, 150, 150, 0.2) !important;
}

    -->
</head>
<body class="soria">
<button type="button" class="btn btn-primary" id="buttonKRA" onclick="coreValueDialogShow()">New Core Value</button>
<div id="coreValueId" style='display:none;'></div>
<div id="attributeId" style='display:none;'></div>
<div id="attributeScoreId" style='display:none;'></div>
<div id="coreValueContent"></div>
<script type="text/javascript" src="../../../jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript" src="../../../bootstrap/5.0.0/dist/js/bootstrap.min.js"></script>
<script type="text/javascript" src="../../../dojo/dojo.js"></script>

<script type="text/javascript">
require([
    "dojo/dom",
    "dojo/request",
    "dijit/Dialog",
    "dijit/form/Button",
    "dojo/domReady!"
], function(dom, request, Dialog, Button) 
{

})
</script>

</body>
</html>