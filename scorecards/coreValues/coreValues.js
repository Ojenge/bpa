require([
    "dojo/dom",
    "dojo/request",
    "dijit/Dialog",
    "dijit/form/Button",
    "dojo/domReady!"
], function(dom, request, Dialog, Button) 
{
    var editState = "";
    var editStateAttribute = "";
    var editStateAttributeScore = "";
    
    var content = '<div class="mb-3">';
    content += '<label for="coreValue" class="form-label">Core Value</label>';
    content += '<input type="text" class="form-control" id="coreValue">';
    content += '</div>';
    content += '<div class="mb-3">';
    content += '<label for="coreValueDescription" class="form-label">Description</label>';
    content += '<input type="text" class="form-control" id="coreValueDescription">';
    content += '</div>';
    content += '<div class="mb-3" id="errorMsgCoreValue"></div>';
    content += '<button type="cancel" onclick="coreValueDialogHideNoSave()" class="btn btn-outline-primary">Cancel</button>';
    content += ' <button type="submit" onclick="coreValueDialogHide()" class="btn btn-outline-primary">Save</button>';
    
    var coreValueDialog = new Dialog({
            title: "Add Strategic Priority (KRA)",
            content: content,
            style: "width: 390px",
            id: "coreValueDialog"
        });

    var contentAttribute = '<div class="mb-3">';
    contentAttribute += '<label for="attribute" class="form-label">Attribute</label>';
    contentAttribute += '<input type="text" class="form-control" id="attribute">';
    contentAttribute += '</div>';
    contentAttribute += '<div class="mb-3">';
    contentAttribute += '<label for="attributeDescription" class="form-label">Description</label>';
    contentAttribute += '<input type="text" class="form-control" id="attributeDescription">';
    contentAttribute += '</div>';
    contentAttribute += '<div class="mb-3" id="errorMsgAttribute"></div>';
    contentAttribute += '<button type="cancel" onclick="attributeDialogHideNoSave()" class="btn btn-outline-primary">Cancel</button>';
    contentAttribute += ' <button type="submit" onclick="attributeDialogHide()" class="btn btn-outline-primary">Save</button>';
    
    var attributeDialog = new Dialog({
            title: "Add Attribute to Core Value",
            content: contentAttribute,
            style: "width: 390px",
            id: "attributeDialog"
        });

    var contentAttributeScore = '<div class="mb-3">';
    contentAttributeScore += '<label for="attributeScore" class="form-label">Score</label>';
    contentAttributeScore += '<input type="text" class="form-control" id="attributeScore">';
    contentAttributeScore += '</div>';
    contentAttributeScore += '<div class="mb-3">';
    contentAttributeScore += '<label for="attributeScoreDate" class="form-label">Date</label>';
    contentAttributeScore += '<input type="text" class="form-control" id="attributeScoreDate">';
    contentAttributeScore += '</div>';
    contentAttributeScore += '<div class="mb-3" id="attributeScoreList"></div>';
    contentAttributeScore += '<div class="mb-3" id="errorMsgAttributeScore"></div>';
    contentAttributeScore += '<button type="cancel" onclick="attributeScoreDialogHideNoSave()" class="btn btn-outline-primary">Cancel</button>';
    contentAttributeScore += ' <button type="submit" onclick="attributeScoreDialogHide()" class="btn btn-outline-primary">Save</button>';
    
    var attributeScoreDialog = new Dialog({
            title: "Add Attribute Score",
            content: contentAttributeScore,
            style: "width: 390px",
            id: "attributeScoreDialog"
        });

    coreValueDialogShow = function() 
    {
        editState = "new";
        coreValueDialog.show();
    };

    attributeDialogShow = function($coreValueId) 
    {
        dom.byId("coreValueId").innerHTML = $coreValueId;
        dom.byId("attributeId").innerHTML = "";
        dom.byId("attribute").value = '';
        dom.byId("attributeDescription").value = '';
        dom.byId("errorMsgAttribute").innerHTML = '';
        editStateAttribute = "new";
        attributeDialog.show();
    };

    coreValueDialogHide = function() 
    {   
        var coreValue = dom.byId("coreValue").value;
        var coreValueDescription = dom.byId("coreValueDescription").value;
        if (coreValue === "" || coreValueDescription === "") 
        {
            dom.byId("errorMsgCoreValue").innerHTML = '<div class="alert alert-danger" role="alert">Please fill in all the fields</div>';
            return;
        }
        else if (editState === "delete") 
        {
            request.post("scorecards/coreValues/deleteCoreValue.php", {
                data: { id: dom.byId("coreValueId").innerHTML }
            }).then(function(returnedData) 
            {
                dom.byId("coreValueContent").innerHTML = returnedData;
            });
            coreValueDialog.hide();
            dom.byId("coreValue").value = '';
            dom.byId("coreValueDescription").value = '';
            dom.byId("errorMsgCoreValue").innerHTML = '';
        }
        else 
        {
            coreValueDialog.hide();
            request.post("scorecards/coreValues/saveCoreValue.php",{
            data: {
                coreValue: coreValue,
                coreValueDescription: coreValueDescription,
                editState: editState,
                id: dom.byId("coreValueId").innerHTML
            }
            }).then(function(returnedData)
            {
                    dom.byId("coreValue").value = '';
                    dom.byId("coreValueDescription").value = '';
                    dom.byId("coreValueContent").innerHTML = returnedData;
            });
            dom.byId("errorMsgCoreValue").innerHTML = '';
        }
    };

    attributeDialogHide = function() 
    {   
        var attribute = dom.byId("attribute").value;
        var attributeDescription = dom.byId("attributeDescription").value;
        var coreValueId = dom.byId("coreValueId").innerHTML;

        if (attribute === "" || attributeDescription === "") 
        {
            dom.byId("errorMsgAttribute").innerHTML = '<div class="alert alert-danger" role="alert">Please fill in all the fields</div>';
            return;
        }
        else if (editStateAttribute === "delete") 
        {
            request.post("scorecards/coreValues/deleteAttribute.php", {
                data: { id: dom.byId("attributeId").innerHTML }
            }).then(function(returnedData) 
            {
                dom.byId("coreValueContent").innerHTML = returnedData;
            });
            attributeDialog.hide();
            dom.byId("attribute").value = '';
            dom.byId("attributeDescription").value = '';
            dom.byId("errorMsgAttribute").innerHTML = '';
        }
        else 
        {
            attributeDialog.hide();
            request.post("scorecards/coreValues/saveAttribute.php",{
            data: {
                attribute: attribute,
                attributeDescription: attributeDescription,
                editStateAttribute: editStateAttribute,
                id: dom.byId("attributeId").innerHTML,
                coreValueId: dom.byId("coreValueId").innerHTML
            }
            }).then(function(returnedData)
            {
                    dom.byId("attribute").value = '';
                    dom.byId("attributeDescription").value = '';
                    dom.byId("coreValueContent").innerHTML = returnedData;
            });
            dom.byId("errorMsgAttribute").innerHTML = '';
        }
    };

    attributeScoreDialogHide = function() 
    {   
        var attributeScore = dom.byId("attributeScore").value;
        var attributeScoreDate = dom.byId("attributeScoreDate").value;
        var attributeScoreId = dom.byId("attributeScoreId").innerHTML;

        if (attributeScore === "" || attributeScoreDate === "") 
        {
            dom.byId("errorMsgAttributeScore").innerHTML = '<div class="alert alert-danger" role="alert">Please fill in all the fields</div>';
            return;
        }
        else if (editStateAttributeScore === "delete") 
        {
            request.post("scorecards/coreValues/deleteAttributeScore.php", {
                data: { id: dom.byId("attributeScoreId").innerHTML }
            }).then(function(returnedData) 
            {
                dom.byId("coreValueContent").innerHTML = returnedData;
            });
            attributeScoreDialog.hide();
            dom.byId("attributeScore").value = '';
            dom.byId("attributeScoreData").value = '';
            dom.byId("errorMsgAttributeScore").innerHTML = '';
        }
        else 
        {
            /*console.log("Attribute Score: " + attributeScore + 
                       ", Date: " + attributeScoreDate + 
                       ", Edit State: " + editStateAttributeScore + 
                       ", Attribute ID: " + dom.byId("attributeId").innerHTML + 
                       ", Attribute Score ID: " + dom.byId("attributeScoreId").innerHTML);*/
            attributeScoreDialog.hide();
            request.post("scorecards/coreValues/saveAttributeScore.php",{
            data: {
                attributeScore: attributeScore,
                attributeScoreDate: attributeScoreDate,
                editStateAttributeScore: editStateAttributeScore,
                attributeId: dom.byId("attributeId").innerHTML,
                attributeScoreId: dom.byId("attributeScoreId").innerHTML
            }
            }).then(function(returnedData)
            {
                    dom.byId("attributeScore").value = '';
                    dom.byId("attributeScoreDate").value = '';
                    dom.byId("coreValueContent").innerHTML = returnedData;
            });
            dom.byId("errorMsgAttributeScore").innerHTML = '';
        }
    };

    coreValueDialogHideNoSave = function() 
    {
        dom.byId("coreValue").value = '';
        dom.byId("coreValueDescription").value = '';
        dom.byId("errorMsgCoreValue").innerHTML = '';
        coreValueDialog.hide();
    }

    attributeDialogHideNoSave = function() 
    {
        dom.byId("attribute").value = '';
        dom.byId("attributeDescription").value = '';
        dom.byId("errorMsgAttribute").innerHTML = '';
        attributeDialog.hide();
    }

    editCoreValue = function(id) 
    {
        editState = "edit";
        //dom.byId("coreValueId").innerHTML = id;
        coreValueId = id;
        request.post("scorecards/coreValues/getCoreValue.php", {
            data: { id: id },
            handleAs: "json"
        }).then(function(coreValueData) 
        {
            coreValueDialog.show();
            dom.byId("coreValue").value = coreValueData.value;
            dom.byId("coreValueDescription").value = coreValueData.description;
        });
    }

    editAttribute = function(id) 
    {
        editStateAttribute = "edit";
        dom.byId("attributeId").innerHTML = id;
        request.post("scorecards/coreValues/getAttribute.php", {
            data: { id: id },
            handleAs: "json"
        }).then(function(attributeData) 
        {
            attributeDialog.show();
            dom.byId("attribute").value = attributeData.attribute;
            dom.byId("attributeDescription").value = attributeData.description;
            
        });
    }

    addAttributeScore = function(id) 
    {
        editStateAttributeScore = "new";
        
        request.post("scorecards/coreValues/getAttributeScore.php", {
            data: { attributeId: id },
            handleAs: "json"
        }).then(function(attributeScoreData) 
        {
            dom.byId("attributeScore").value = attributeScoreData.score;
            dom.byId("attributeScoreDate").value = attributeScoreData.date;

            request.post("scorecards/coreValues/getAttributeScoreList.php", {
                data: { attributeId: id }
            }).then(function(attributeScoreListData) 
            {
                attributeScoreDialog.show();
                dom.byId("attributeScoreList").innerHTML = attributeScoreListData;
                
            });
            dom.byId("attributeId").innerHTML = id;
            //attributeScoreDialog.show();
        });
    }

    editThisScore = function(id)
    {
        request.post("scorecards/coreValues/getSpecificScore.php", {
            data: { id: id },
            handleAs: "json"
        }).then(function(thisScoreData) 
        {
            editStateAttributeScore = "edit";
            dom.byId("attributeScore").value = thisScoreData.score;
            dom.byId("attributeScoreDate").value = thisScoreData.date;
            dom.byId("attributeScoreId").innerHTML = id;
        });
    }

    deleteThisScore = function(id)
    {
        request.post("scorecards/coreValues/deleteThisScore.php", {
            data: { id: id }
        }).then(function() 
        {
            request.post("gscorecards/coreValues/etAttributeScoreList.php", {
                data: { attributeId: dom.byId("attributeId").innerHTML }
            }).then(function(attributeScoreListData) 
            {
                dom.byId("attributeScoreList").innerHTML = attributeScoreListData;
            });
        });
    }

    deleteCoreValue = function(id) 
    {
        editState = "delete";
        dom.byId("coreValueId").innerHTML = id;
        request.post("scorecards/coreValues/getCoreValue.php", {
            data: { id: id },
            handleAs: "json"
        }).then(function(coreValueData) 
        {
            coreValueDialog.show();
            dom.byId("coreValue").value = coreValueData.value;
            dom.byId("coreValueDescription").value = coreValueData.description;
            dom.byId("errorMsgCoreValue").innerHTML = '<div class="alert alert-danger" role="alert">Are you sure you want to delete this Core Value?</div>';
            
        });
    };

    deleteAttribute = function(id) 
    {
        editStateAttribute = "delete";
        dom.byId("attributeId").innerHTML = id;
        request.post("scorecards/coreValues/getAttribute.php", {
            data: { id: id },
            handleAs: "json"
        }).then(function(attributeData) 
        {
            attributeDialog.show();
            dom.byId("attribute").value = attributeData.attribute;
            dom.byId("attributeDescription").value = attributeData.description;
            dom.byId("errorMsgAttribute").innerHTML = '<div class="alert alert-danger" role="alert">Are you sure you want to delete this Attribute?</div>';
            
        });
    };
    
});