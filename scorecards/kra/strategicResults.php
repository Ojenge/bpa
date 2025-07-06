<html>
<head>
    <link rel="stylesheet" href="../../../dijit/themes/soria/soria.css" media="all">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../../bootstrap/5.0.0/dist/css/bootstrap.min.css" media="all">
</head>
<body class="soria">
<button type="button" class="btn btn-primary" id="buttonKRA" onclick="kraDialogShow()">New Strategic Result</button>
<div id="kraId" style='display:none;'></div>
<div id="kraContent"></div>
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
    var editState = "";

    request.post("getKRAs.php",{
        data: {}
    }).then(function(savedKRA)
    {
        dom.byId("kraContent").innerHTML = savedKRA;
    });
            
    var content = '<div class="mb-3">';
    content += '<label for="strategicPriority" class="form-label">Strategic Priority</label>';
    content += '<span class="form-text"> (Strategic Issue)</span>';
    content += '<input type="text" class="form-control" id="strategicPriority">';
    content += '</div>';
    content += '<div class="mb-3">';
    content += '<label for="strategicResult" class="form-label">Strategic Result</label>';
    content += '<span class="form-text"> (Key Result Area)</span>';
    content += '<input type="text" class="form-control" id="strategicResult">';
    content += '</div>';
    content += '<div class="mb-3" id="errorMsgKRA"></div>';
    content += '<button type="cancel" onclick="kraDialogHideNoSave()" class="btn btn-outline-primary">Cancel</button>';
    content += ' <button type="submit" onclick="kraDialogHide()" class="btn btn-outline-primary">Submit</button>';
    
    var kraDialog = new Dialog({
            title: "Add Strategic Priority (KRA)",
            content: content,
            style: "width: 390px",
            id: "kraDialog"
        });

    kraDialogShow = function() 
    {
        editState = "new";
        kraDialog.show();
    };

    kraDialogHide = function() 
    {   
        var strategicPriority = dom.byId("strategicPriority").value;
        var strategicResult = dom.byId("strategicResult").value;
        if (strategicPriority === "" || strategicResult === "") 
        {
            dom.byId("errorMsgKRA").innerHTML = '<div class="alert alert-danger" role="alert">Please fill in all the fields</div>';
            return;
        }
        else if (editState === "delete") 
        {
            request.post("deleteKRA.php", {
                data: { id: dom.byId("kraId").innerHTML }
            }).then(function(returnedData) 
            {
                dom.byId("kraContent").innerHTML = returnedData;
            });
            kraDialog.hide();
            dom.byId("strategicPriority").value = '';
            dom.byId("strategicResult").value = '';
            dom.byId("errorMsgKRA").innerHTML = '';
        }
        else 
        {
            kraDialog.hide();
            request.post("saveKRA.php",{
            data: {
                strategicPriority: strategicPriority,
                strategicResult: strategicResult,
                editState: editState,
                id: dom.byId("kraId").innerHTML
            }
            }).then(function(returnedData)
            {
                    dom.byId("strategicPriority").value = '';
                    dom.byId("strategicResult").value = '';
                    dom.byId("kraContent").innerHTML = returnedData;
            });
            dom.byId("errorMsgKRA").innerHTML = '';
        }
    };

    kraDialogHideNoSave = function() 
    {
        dom.byId("strategicPriority").value = '';
        dom.byId("strategicResult").value = '';
        dom.byId("errorMsgKRA").innerHTML = '';
        kraDialog.hide();
    }

    editKRA = function(id) 
    {
        editState = "edit";
        dom.byId("kraId").innerHTML = id;
        request.post("getKRA.php", {
            data: { id: id },
            handleAs: "json"
        }).then(function(kraData) 
        {
            dom.byId("strategicPriority").value = kraData.priority;
            dom.byId("strategicResult").value = kraData.result;
            kraDialog.show();
        });
    }

    deleteKRA = function(id) 
    {
        editState = "delete";
        dom.byId("kraId").innerHTML = id;
        request.post("getKRA.php", {
            data: { id: id },
            handleAs: "json"
        }).then(function(kraData) 
        {
            dom.byId("strategicPriority").value = kraData.priority;
            dom.byId("strategicResult").value = kraData.result;
            dom.byId("errorMsgKRA").innerHTML = '<div class="alert alert-danger" role="alert">Are you sure you want to delete this Strategic Result?</div>';
            kraDialog.show();
        });
    };
});
</script>

</body>
</html>