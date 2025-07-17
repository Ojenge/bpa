// Tag Management Functions
require([
    "dojo/request",
    "dojo/dom",
    "dojo/dom-style",
    "dojo/dom-construct",
    "dojo/json",
    "dijit/Dialog",
    "dijit/form/Button",
    "dijit/form/FilteringSelect",
    "dojo/domReady!"
], function(request, dom, domStyle, domConstruct, json, Dialog, Button, FilteringSelect) {

// Global variables
var currentItemId = null;
var currentItemType = null; // 'measure' or 'initiative'

// Helper function to safely find elements by text content (replaces :contains selector)
function findElementByText(selector, text) {
    try {
        // First try the original selector
        var elements = document.querySelectorAll(selector);
        for (var i = 0; i < elements.length; i++) {
            if (elements[i].textContent && elements[i].textContent.includes(text)) {
                return elements[i];
            }
        }
        return null;
    } catch (e) {
        console.warn("Invalid selector:", selector, e);
        return null;
    }
}

// Helper function to safely find all elements by text content
function findAllElementsByText(selector, text) {
    try {
        var elements = document.querySelectorAll(selector);
        var matches = [];
        for (var i = 0; i < elements.length; i++) {
            if (elements[i].textContent && elements[i].textContent.includes(text)) {
                matches.push(elements[i]);
            }
        }
        return matches;
    } catch (e) {
        console.warn("Invalid selector:", selector, e);
        return [];
    }
}

// Function to open tag management dialog
window.openTagDialog = function(itemId, itemType) {
    currentItemId = itemId;
    currentItemType = itemType;
    
    // Create dialog if it doesn't exist
    if (!dijit.byId("tagManagementDialog")) {
        createTagDialog();
    }
    
    // Load current tags
    loadTags(itemId, itemType);
    
    // Show dialog
    dijit.byId("tagManagementDialog").show();
};

// Function to create tag management dialog
function createTagDialog() {
    var dialogContent = `
        <div style="padding: 20px; min-width: 400px;">
            <h4>Manage Status Tags</h4>
            <div style="margin-bottom: 15px;">
                <label for="statusSelect">Status:</label>
                <select id="statusSelect" style="width: 100%; margin-top: 5px;">
                    <option value="">Select Status</option>
                    <option value="approved">Approved</option>
                    <option value="needs_review">Needs Review</option>
                </select>
            </div>
            <div style="margin-bottom: 15px;">
                <label for="tagNotes">Notes (optional):</label>
                <textarea id="tagNotes" style="width: 100%; height: 60px; margin-top: 5px;" placeholder="Add any additional notes..."></textarea>
            </div>
            <div id="currentTags" style="margin-bottom: 15px;">
                <strong>Current Tags:</strong>
                <div id="tagsList" style="margin-top: 5px;"></div>
            </div>
            <div style="text-align: right;">
                <button id="addTagBtn" class="btn btn-primary btn-sm">Add Tag</button>
                <button id="saveTagsBtn" class="btn btn-success btn-sm" style="margin-left: 10px;">Save All</button>
                <button id="cancelTagsBtn" class="btn btn-secondary btn-sm" style="margin-left: 10px;">Cancel</button>
            </div>
        </div>
    `;
    
    var dialog = new Dialog({
        id: "tagManagementDialog",
        title: "Tag Management",
        content: dialogContent,
        style: "width: 500px;"
    });
    
    dialog.startup();
    
    // Add event listeners
    dom.byId("addTagBtn").onclick = addTag;
    dom.byId("saveTagsBtn").onclick = saveTags;
    dom.byId("cancelTagsBtn").onclick = function() {
        dijit.byId("tagManagementDialog").hide();
    };
}

// Function to load current tags
function loadTags(itemId, itemType) {
    var action = itemType === 'measure' ? 'get_measure_tags' : 'get_initiative_tags';
    var idField = itemType === 'measure' ? 'measureId' : 'initiativeId';
    
    request.post("/bpa/dataEntry/tag-functions.php", {
        handleAs: "json",
        data: {
            action: action,
            [idField]: itemId
        }
    }).then(function(response) {
        if (response.success) {
            displayCurrentTags(response.tags);
        } else {
            console.error("Error loading tags:", response.message);
        }
    }).otherwise(function(error) {
        console.error("Error loading tags:", error);
    });
}

// Function to display current tags
function displayCurrentTags(tags) {
    var tagsList = dom.byId("tagsList");
    tagsList.innerHTML = "";
    
    if (tags && tags.length > 0) {
        tags.forEach(function(tag, index) {
            var tagElement = domConstruct.create("div", {
                style: "margin: 5px 0; padding: 5px; border: 1px solid #ddd; border-radius: 3px; background-color: #f9f9f9;",
                innerHTML: `
                    <span class="badge ${getStatusClass(tag.status)}">${getStatusText(tag.status)}</span>
                    ${tag.notes ? '<span style="margin-left: 10px;">' + tag.notes + '</span>' : ''}
                    <button onclick="removeTag(${index})" class="btn btn-danger btn-sm" style="float: right; margin-left: 5px;">Remove</button>
                `
            });
            domConstruct.place(tagElement, tagsList);
        });
    } else {
        tagsList.innerHTML = "<em>No tags set</em>";
    }
}

// Function to add a new tag
function addTag() {
    var statusSelect = dom.byId("statusSelect");
    var tagNotes = dom.byId("tagNotes");
    
    var status = statusSelect.value;
    var notes = tagNotes.value.trim();
    
    if (!status) {
        alert("Please select a status");
        return;
    }
    
    // Get current tags
    var tagsList = dom.byId("tagsList");
    var currentTags = [];
    
    // Parse existing tags from the display
    var tagElements = tagsList.querySelectorAll("div");
    tagElements.forEach(function(element) {
        var statusBadge = element.querySelector(".badge");
        if (statusBadge) {
            var statusText = statusBadge.textContent;
            var statusValue = getStatusValue(statusText);
            var notesSpan = element.querySelector("span:not(.badge)");
            var notesText = notesSpan ? notesSpan.textContent.trim() : "";
            
            currentTags.push({
                status: statusValue,
                notes: notesText
            });
        }
    });
    
    // Add new tag
    currentTags.push({
        status: status,
        notes: notes
    });
    
    // Display updated tags
    displayCurrentTags(currentTags);
    
    // Clear form
    statusSelect.value = "";
    tagNotes.value = "";
}

// Function to remove a tag
window.removeTag = function(index) {
    var tagsList = dom.byId("tagsList");
    var tagElements = tagsList.querySelectorAll("div");
    
    if (tagElements[index]) {
        domConstruct.destroy(tagElements[index]);
    }
};

// Function to save all tags
function saveTags() {
    var tagsList = dom.byId("tagsList");
    var currentTags = [];
    
    // Parse existing tags from the display
    var tagElements = tagsList.querySelectorAll("div");
    tagElements.forEach(function(element) {
        var statusBadge = element.querySelector(".badge");
        if (statusBadge) {
            var statusText = statusBadge.textContent;
            var statusValue = getStatusValue(statusText);
            var notesSpan = element.querySelector("span:not(.badge)");
            var notesText = notesSpan ? notesSpan.textContent.trim() : "";
            
            currentTags.push({
                status: statusValue,
                notes: notesText
            });
        }
    });
    
    // Save tags
    var action = currentItemType === 'measure' ? 'save_measure_tags' : 'save_initiative_tags';
    var idField = currentItemType === 'measure' ? 'measureId' : 'initiativeId';
    
    request.post("/bpa/dataEntry/tag-functions.php", {
        handleAs: "json",
        data: {
            action: action,
            [idField]: currentItemId,
            tags: currentTags
        }
    }).then(function(response) {
        if (response.success) {
            alert("Tags saved successfully!");
            dijit.byId("tagManagementDialog").hide();
            // Refresh the page to show updated tags
            if (typeof refreshDataEntryPage === 'function') {
                refreshDataEntryPage();
            }
        } else {
            alert("Error saving tags: " + response.message);
        }
    }).otherwise(function(error) {
        alert("Error saving tags: " + error);
    });
}

// Helper functions
function getStatusClass(status) {
    switch (status) {
        case 'approved':
            return 'bg-success';
        case 'needs_review':
            return 'bg-warning';
        default:
            return 'bg-secondary';
    }
}

function getStatusText(status) {
    switch (status) {
        case 'approved':
            return 'Approved';
        case 'needs_review':
            return 'Needs Review';
        default:
            return status;
    }
}

function getStatusValue(statusText) {
    switch (statusText) {
        case 'Approved':
            return 'approved';
        case 'Needs Review':
            return 'needs_review';
        default:
            return statusText.toLowerCase().replace(' ', '_');
    }
}

// Function to update status via dropdown
window.updateStatus = function(itemId, itemType, newStatus) {
    if (!newStatus || newStatus === '') {
        return; // No change selected
    }
    
    var tags = [];
    
    if (newStatus === 'remove') {
        // Remove all status tags
        tags = [];
    } else {
        // Add the new status tag
        tags = [{
            status: newStatus,
            notes: ''
        }];
    }
    
    // Save the updated tags
    var action = itemType === 'measure' ? 'save_measure_tags' : 'save_initiative_tags';
    var idField = itemType === 'measure' ? 'measureId' : 'initiativeId';
    
    request.post("/bpa/dataEntry/tag-functions.php", {
        handleAs: "json",
        data: {
            action: action,
            [idField]: itemId,
            tags: tags
        }
    }).then(function(response) {
        if (response.success) {
            // Show success message
            showStatusMessage("Status updated successfully!", "success");
            // Refresh the page to show updated status
            if (typeof refreshDataEntryPage === 'function') {
                setTimeout(refreshDataEntryPage, 500);
            }
        } else {
            showStatusMessage("Error updating status: " + response.message, "error");
        }
    }).otherwise(function(error) {
        showStatusMessage("Error updating status: " + error, "error");
    });
};

// Function to show status messages
function showStatusMessage(message, type) {
    // Create or update message element
    var messageElement = document.getElementById('statusMessage');
    if (!messageElement) {
        messageElement = domConstruct.create("div", {
            id: "statusMessage",
            style: "position: fixed; top: 20px; right: 20px; z-index: 9999; padding: 10px 15px; border-radius: 5px; color: white; font-weight: bold;"
        });
        document.body.appendChild(messageElement);
    }
    
    // Set message content and style
    messageElement.innerHTML = message;
    if (type === 'success') {
        messageElement.style.backgroundColor = '#28a745';
    } else {
        messageElement.style.backgroundColor = '#dc3545';
    }
    
    // Auto-hide after 3 seconds
    setTimeout(function() {
        if (messageElement.parentNode) {
            domConstruct.destroy(messageElement);
        }
    }, 3000);
}

// Add tag management buttons to measures and initiatives
window.addTagButtons = function() {
    try {
        // Add tag buttons to measures
        var measureRows = document.querySelectorAll("#myMeasureContent table tbody tr");
        measureRows.forEach(function(row) {
            var cells = row.querySelectorAll("td");
            if (cells.length > 0) {
                var measureName = cells[0].textContent;
                var measureId = extractMeasureId(measureName);
                if (measureId) {
                    var tagButton = domConstruct.create("button", {
                        className: "btn btn-outline-info btn-sm",
                        innerHTML: "Manage Tags",
                        style: "margin-left: 5px;",
                        onclick: "openTagDialog('" + measureId + "', 'measure')"
                    });
                    cells[0].appendChild(tagButton);
                }
            }
        });
    } catch (e) {
        console.warn("Error in addTagButtons for measures:", e);
    }
    
    // Add tag buttons to initiatives
    var initiativeRows = document.querySelectorAll("#myInitiativeContent table tbody tr");
    initiativeRows.forEach(function(row) {
        var cells = row.querySelectorAll("td");
        if (cells.length > 1) {
            var initiativeName = cells[1].textContent;
            var initiativeId = extractInitiativeId(row);
            if (initiativeId) {
                var tagButton = domConstruct.create("button", {
                    className: "btn btn-outline-info btn-sm",
                    innerHTML: "Manage Tags",
                    style: "margin-left: 5px;",
                    onclick: "openTagDialog('" + initiativeId + "', 'initiative')"
                });
                cells[1].appendChild(tagButton);
            }
        }
    });
};

// Helper function to extract measure ID
function extractMeasureId(measureName) {
    try {
        // Find the measure element by searching through all table cells
        var allCells = document.querySelectorAll("#myMeasureContent table tbody tr td");
        var measureElement = null;
        
        for (var i = 0; i < allCells.length; i++) {
            if (allCells[i].textContent && allCells[i].textContent.trim() === measureName.trim()) {
                measureElement = allCells[i];
                break;
            }
        }
        
        if (measureElement) {
            var updateLink = measureElement.parentNode.querySelector("a[onclick*='myBulkEntry']");
            if (updateLink) {
                var match = updateLink.getAttribute("onclick").match(/myBulkEntry\((\d+)\)/);
                return match ? 'kpi' + match[1] : null;
            }
        }
        return null;
    } catch (e) {
        console.warn("Error in extractMeasureId for measure:", measureName, e);
        return null;
    }
}

// Helper function to extract initiative ID
function extractInitiativeId(row) {
    var updateLink = row.querySelector("a[onclick*='editInitiative']");
    if (updateLink) {
        var match = updateLink.getAttribute("onclick").match(/editInitiative\((\d+)\)/);
        return match ? match[1] : null;
    }
    return null;
}

// Global error handler for invalid selectors
window.addEventListener('error', function(e) {
    if (e.message && e.message.includes(":contains") && e.message.includes("is not a valid selector")) {
        console.warn("Caught invalid :contains selector error:", e.message);
        console.warn("Stack trace:", e.error ? e.error.stack : "No stack trace available");
        e.preventDefault();
        return false;
    }
});

// Override querySelector to handle :contains selectors gracefully
var originalQuerySelector = document.querySelector;
var originalQuerySelectorAll = document.querySelectorAll;

document.querySelector = function(selector) {
    if (selector && selector.includes(':contains')) {
        console.warn("Attempted to use :contains selector with querySelector:", selector);
        console.warn("Use findElementByText() function instead");
        return null;
    }
    return originalQuerySelector.call(this, selector);
};

document.querySelectorAll = function(selector) {
    if (selector && selector.includes(':contains')) {
        console.warn("Attempted to use :contains selector with querySelectorAll:", selector);
        console.warn("Use findAllElementsByText() function instead");
        return [];
    }
    return originalQuerySelectorAll.call(this, selector);
};

}); 