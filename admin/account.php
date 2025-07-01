<?php
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
require_once("models/header.php");
	
//include("left-nav.php");



echo "
<table>
<tr><td>Name</td><td>$loggedInUser->displayname</td></tr> 
<tr><td>Title</td><td>$loggedInUser->title</td></tr>
<tr><td>Account registered on</td><td>".date("M d, Y", $loggedInUser->signupTimeStamp())."</td></tr>
</table>";
?>
<script type="text/javascript">
require(["dojo/request", "dojo/json", "dojo/dom-style", "dojo/dom", "dijit/registry"],function(request, json, domStyle, dom, registry){
userSettings = function()
{
	request.post("admin/user_settings.php",{

	}).then(function(settingsData)
	{
		dojo.byId("main").innerHTML = settingsData;
	});
}
userPasswordUpdate = function()
{
	//alert("We here?");
	var pwd = dojo.byId("password").value;
	var pwdc = dojo.byId("passwordc").value;
	var pwdCheck = dojo.byId("passwordcheck").value;
	var email = dojo.byId("email").value;
	
	//alert("first move :-): "+email);
	
	request.post("admin/user_settings.php",{
	//handleAs: "json",
	data: {
		password: pwd,
		passwordc: pwdc,
		passwordcheck: pwdCheck,
		email: email
	}
	}).then(function(data)
	{
		dojo.byId("main").innerHTML = data;
	});
}
notificationAdmin = function()
{
	request.post("notifications/admin_notifications.php",{
		//handleAs: "json",
		data: {
			
		}						
		}).then(function(data) 
		{
			//alert(data);
			dojo.byId("main").innerHTML = data;
		});	

}
userSettingsUpdate = function()
{
	//alert("We here?");
	var displayname = dojo.byId("displayname").value;
	var reportsTo = dojo.byId("reportsTo").value;
	var email = dojo.byId("email").value;
	var title = dojo.byId("title").value;
	var department = dojo.byId("department").value;
	
	//alert("first move :-): "+email);
	
	request.post("admin/user_settings.php",{
	//handleAs: "json",
	data: {
		displayname: displayname,
		reportsTo: reportsTo,
		email: email,
		title: title,
		department: department
	}
	}).then(function(data)
	{
		dojo.byId("main").innerHTML = data;
	});
}
adminConfiguration = function()
{
	request.post("admin/admin_configuration.php",{
	//handleAs: "json",
	data: {

	}
	}).then(function(data)
	{
		dojo.byId("main").innerHTML = data;
	});
}
adminConfigUpdate = function()
{
	//alert(dojo.byId("settings[2]").value);
	var settings = [];
	settings[1] = dojo.byId("settings[1]").value;
	settings[2] = dojo.byId("settings[2]").value;
	settings[3] = dojo.byId("settings[3]").value;
	settings[4] = dojo.byId("settings[4]").value;
	settings[5] = dojo.byId("settings[5]").value;
	settings[6] = dojo.byId("settings[6]").value;
	settings[7] = dojo.byId("settings[7]").value;
	settings[8] = dojo.byId("settings[8]").value;
	settings[9] = dojo.byId("settings[9]").value;
	settings = dojo.toJson(settings);
	request.post("admin/admin_configuration.php",{
	//handleAs: "json",
	data: {
		settings: settings
	}
	}).then(function(data)
	{
		//alert("success");
		dojo.byId("main").innerHTML = data;
	});
}
adminUsers = function()
{
	request.post("admin/admin_users.php",{
		//handleAs: "json",
		data: {

		}
		}).then(function(data)
		{
			//alert(data);
			dojo.byId("main").innerHTML = data;
		});
}
notificationAdmin = function()
{
	request.post("notifications/admin_notifications.php",{
		//handleAs: "json",
		data: {

		}
		}).then(function(data)
		{
			//alert(data);
			dojo.byId("main").innerHTML = data;
		});
}
adminUserDelete = function()
{
	request.post("admin/get_admin_users.php",{
	handleAs: "json"
	}).then(function(data)
	{
		var toDelete = [];
		var count = 0, idCount = 0;
		while(count < data.length)
		{
			if(dojo.byId("toDelete["+data[count].id+"]"))
			{
				if(dojo.byId("toDelete["+data[count].id+"]").checked == 1)
				{
					toDelete[idCount] = data[count].id;
					idCount++;
				}
			}
			count++;
		}
		request.post("admin/admin_users.php",{
		//handleAs: "json",
		data: {
			toDelete: toDelete
		}
		}).then(function(data)
		{
			dojo.byId("main").innerHTML = data;
		});
	});

}
adminUserInfo = function(id)
{
	request.post("admin/admin_user.php",{
		//handleAs: "json",
		data: {
			id:id
		}
		}).then(function(data)
		{
			dojo.byId("main").innerHTML = data;
		});
}
updateUserInfo = function()
{
	var toDelete = [], toReset = [], activate, addPermission = [], removePermission = [];
	var userId = dojo.byId("userId").innerHTML;
	
	request.post("admin/get_admin_user_permissions.php",{
	handleAs: "json"
	}).then(function(data)
	{
		var toDelete = [];
		var count = 0, idCount = 0;
		while(count < data.length)
		{
			if(dojo.byId("addPermission["+data[count].id+"]"))
			{
				if(dojo.byId("addPermission["+data[count].id+"]").checked == 1)
				{
					addPermission[idCount] = data[count].id;
					idCount++;
				}
			}
			count++;	
		}
		count = 0, idCount = 0;
		while(count < data.length)
		{
			if(dojo.byId("removePermission["+data[count].id+"]"))
			{
				if(dojo.byId("removePermission["+data[count].id+"]").checked == 1)
				{
					removePermission[idCount] = data[count].id;
					idCount++;
				}
			}
			count++;	
		}
		
		if(dojo.byId("toDelete["+userId+"]"))
		{
			if(dojo.byId("toDelete["+userId+"]").checked == 1)
			{
				toDelete[0] = userId;
			}
		}
		if(dojo.byId("toReset["+userId+"]"))
		{
			if(dojo.byId("toReset["+userId+"]").checked == 1)
			{
				toReset[0] = userId;
			}
		}
		//else toDelete = null
		if(dojo.byId("activate"))
		{
			if(dojo.byId("activate").checked == 1)
			activate = "activate";
		}
		else activate = null;
		
		request.post("admin/admin_user.php",{
		//handleAs: "json",
		data: {
			display: dojo.byId("display").value,
			email: dojo.byId("email").value,
			title: dojo.byId("title").value,
			toDelete: toDelete,
			toReset: toReset,
			activate: activate,
			id: dojo.byId("userId").innerHTML,
			removePermission: removePermission,
			addPermission: addPermission
		}
		}).then(function(data)
		{
			dojo.byId("main").innerHTML = data;
		});
	});
}
adminPermissions = function()
{
	request.post("admin/admin_permissions.php",{
		//handleAs: "json",
		data: {

		}
		}).then(function(data)
		{
			dojo.byId("main").innerHTML = data;
		});
}
adminAddPerm = function()
{
	//alert("in INfo");
	var toDelete = [];
	/*var deleteCount = 1;
	var userId = dojo.byId("userId").innerHTML;
	if(dojo.byId("toDelete["+userId+"]").checked == 1)
	toDelete[userId] = userId;
	*/
	request.post("admin/admin_permissions.php",{
		//handleAs: "json",
		data: {
			newPermission: dojo.byId("newPermission").value,
			//toDelete: toDelete
		}
		}).then(function(data)
		{
			dojo.byId("main").innerHTML = data;
		});
}
adminPermInfo = function(id)
{//alert("perm info");
	request.post("admin/admin_permission.php",{
		//handleAs: "json",
		data: {
			id:id
		}
		}).then(function(data)
		{
			dojo.byId("main").innerHTML = data;
		});
}
updatePermInfo = function(id)
{//alert("perm info");
	request.post("admin/admin_permission.php",{
		//handleAs: "json",
		data: {
			id:id
		}
		}).then(function(data)
		{
			dojo.byId("main").innerHTML = data;
		});
}
adminActivate = function(id, status)
{//alert("perm info");
	request.post("models/activateModule.php",{
		//handleAs: "json",
		data: {
			id:id,
			status: status
		}						
		}).then(function() 
		{
			adminPermissions();
			//dojo.byId("main").innerHTML = data;
		});	
}
adminHome = function(id, homePage)
{//alert("perm info");
	request.post("models/activateHome.php",{
		//handleAs: "json",
		data: {
			id:id
		}						
		}).then(function() 
		{
			adminPermissions();
			//dojo.byId("main").innerHTML = data;
		});	
}
adminPages = function()
{
	request.post("admin/admin_pages.php",{
		//handleAs: "json",
		data: {

		}
		}).then(function(data)
		{
			dojo.byId("main").innerHTML = data;
		});
}
adminPageInfo = function(id)
{

	request.post("admin/admin_page.php",{
		//handleAs: "json",
		data: {
			id:id,
			update: 0
		}
		}).then(function(data)
		{
			//alert("ehe!"+data);
			dojo.byId("main").innerHTML = data;
		});
}
updatePageInfo = function()
{
	var toDelete = [], addPermission = [], removePermission = [], private;
	//if (dojo.byId('private').checked == 1) private = 'Yes';
	
	request.post("admin/get_admin_user_permissions.php",{
	handleAs: "json"
	}).then(function(data)
	{
		if(dojo.byId("private"))
		{
			if(dojo.byId("private").checked == 1)
			private = "Yes";
		}
		else private = null;
		
		var count = 0, idCount = 0;
		while(count < data.length)
		{
			if(dojo.byId("addPermission["+data[count].id+"]"))
			{
				if(dojo.byId("addPermission["+data[count].id+"]").checked == 1)
				{
					addPermission[idCount] = data[count].id;
					idCount++;
				}
			}
			count++;	
		}
		count = 0, idCount = 0;
		while(count < data.length)
		{
			if(dojo.byId("removePermission["+data[count].id+"]"))
			{
				if(dojo.byId("removePermission["+data[count].id+"]").checked == 1)
				{
					removePermission[idCount] = data[count].id;
					idCount++;
				}
			}
			count++;	
		}
		//alert(":-)" + dom.byId('private').value + 'for id: ' + dom.byId('pageId').innerHTML);
		request.post("admin/admin_page.php",{
			//handleAs: "json",
			data: {
			id: dojo.byId("pageId").innerHTML,
			private: private,
			update: 1,
			addPermission: addPermission,
			removePermission: removePermission
			}
			}).then(function(data)
			{
				//alert(data);
				dojo.byId("main").innerHTML = data;
			});
	});
}
recomputeScores = function()
{
	request.post("scorecards/recompute-scores.php",{
		//handleAs: "json",
		data: {
			
		}						
		}).then(function(data) 
		{
			//alert(data);
			dojo.byId("main").innerHTML = data;
		});	
}
restoreScores = function()
{
	request.post("scorecards/restore-scores.php",{
		//handleAs: "json",
		data: {

		}
		}).then(function(data)
		{
			//alert(data);
			dojo.byId("main").innerHTML = data;
		});
}

// Notification Management Functions
createNotificationSchedule = function() {
	// Check if modal already exists
	let scheduleModal = document.getElementById('scheduleModal');
	if (!scheduleModal) {
		// Create modal and then show it
		createScheduleModal().then(() => {
			showScheduleModal();
		});
	} else {
		// Modal exists, show it directly
		showScheduleModal();
	}
}

showScheduleModal = function() {
	// Clear any existing focus to prevent aria-hidden warnings
	if (document.activeElement && document.activeElement.blur) {
		document.activeElement.blur();
	}

	// Reset form to create mode
	resetScheduleModalToCreateMode();

	// Show modal with proper focus management
	const scheduleModal = document.getElementById('scheduleModal');
	if (scheduleModal) {
		const modal = new bootstrap.Modal(scheduleModal, {
			focus: true,
			keyboard: true
		});

		// Handle modal events for better accessibility (one-time listener)
		scheduleModal.addEventListener('shown.bs.modal', function() {
			// Focus on the first input field when modal is shown
			const firstInput = scheduleModal.querySelector('input[type="text"], textarea, select');
			if (firstInput) {
				setTimeout(() => firstInput.focus(), 100);
			}
		}, { once: true });

		modal.show();
	}
}

resetScheduleModalToCreateMode = function() {
	// Reset form
	const scheduleForm = document.getElementById('scheduleForm');
	if (scheduleForm) {
		scheduleForm.reset();
	}

	// Remove schedule ID if it exists
	const scheduleIdInput = document.getElementById('schedule_id');
	if (scheduleIdInput) {
		scheduleIdInput.remove();
	}

	const scheduleModalLabel = document.getElementById('scheduleModalLabel');
	if (scheduleModalLabel) {
		scheduleModalLabel.textContent = 'Create New Notification Schedule';
	}

	const scheduleAction = document.getElementById('scheduleAction');
	if (scheduleAction) {
		scheduleAction.value = 'create_schedule';
	}

	// Update submit button text
	const submitButton = document.querySelector('#scheduleForm button[type="submit"]');
	if (submitButton) {
		submitButton.innerHTML = 'Create Schedule';
	}
}

createNotificationTemplate = function() {
	// Check if modal already exists
	let templateModal = document.getElementById('templateModal');
	if (!templateModal) {
		// Create modal and then show it
		createTemplateModal().then(() => {
			showTemplateModal();
		});
	} else {
		// Modal exists, show it directly
		showTemplateModal();
	}
}

showTemplateModal = function() {
	// Clear any existing focus to prevent aria-hidden warnings
	if (document.activeElement && document.activeElement.blur) {
		document.activeElement.blur();
	}

	// Reset form
	const templateForm = document.getElementById('templateForm');
	if (templateForm) {
		templateForm.reset();
	}

	const templateModalLabel = document.getElementById('templateModalLabel');
	if (templateModalLabel) {
		templateModalLabel.textContent = 'Create New Email Template';
	}

	const templateAction = document.getElementById('templateAction');
	if (templateAction) {
		templateAction.value = 'create_template';
	}

	// Show modal with proper focus management
	const templateModal = document.getElementById('templateModal');
	if (templateModal) {
		const modal = new bootstrap.Modal(templateModal, {
			focus: true,
			keyboard: true
		});

		// Handle modal events for better accessibility (one-time listener)
		templateModal.addEventListener('shown.bs.modal', function() {
			// Focus on the first input field when modal is shown
			const firstInput = templateModal.querySelector('input[type="text"], textarea, select');
			if (firstInput) {
				setTimeout(() => firstInput.focus(), 100);
			}
		}, { once: true });

		modal.show();
	}
}

viewNotificationLogs = function() {
	// Check if modal already exists
	let logsModal = document.getElementById('logsModal');
	if (!logsModal) {
		// Create modal and then show it
		createLogsModal();
	}

	// Now get the elements after modal creation
	showLogsModal();
}

showLogsModal = function() {
	// Clear any existing focus to prevent aria-hidden warnings
	if (document.activeElement && document.activeElement.blur) {
		document.activeElement.blur();
	}

	const logsModal = document.getElementById('logsModal');
	const logsContent = document.getElementById('logsContent');

	// Set loading state if element exists
	if (logsContent) {
		logsContent.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading logs...</div>';
	}

	// Show modal with proper focus management
	if (logsModal) {
		const modal = new bootstrap.Modal(logsModal, {
			focus: true,
			keyboard: true
		});

		// Handle modal events for better accessibility (one-time listener)
		logsModal.addEventListener('shown.bs.modal', function() {
			// Focus on the modal content when shown
			setTimeout(() => {
				if (logsContent) {
					logsContent.focus();
				}
			}, 100);
		}, { once: true });

		modal.show();
	}

	// Fetch logs via AJAX
	fetch('notifications/admin_notifications.php?action=get_logs')
		.then(response => response.json())
		.then(data => {
			const currentLogsContent = document.getElementById('logsContent');
			if (data.success) {
				displayLogs(data.logs);
			} else {
				if (currentLogsContent) {
					currentLogsContent.innerHTML = '<div class="alert alert-danger">Error loading logs: ' + data.error + '</div>';
				}
			}
		})
		.catch(error => {
			const currentLogsContent = document.getElementById('logsContent');
			if (currentLogsContent) {
				currentLogsContent.innerHTML = '<div class="alert alert-danger">Error loading logs: ' + error.message + '</div>';
			}
		});
}

createLogsModal = function() {
	const modalHtml = `
		<div class="modal fade" id="logsModal" tabindex="-1" aria-labelledby="logsModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="logsModalLabel">
							<i class="fas fa-list"></i> Notification Logs
						</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div id="logsContent" tabindex="-1" role="region" aria-label="Notification logs content">
							<!-- Logs will be loaded here -->
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
	`;

	document.body.insertAdjacentHTML('beforeend', modalHtml);
}

displayLogs = function(logs) {
	const logsContent = document.getElementById('logsContent');

	if (!logsContent) {
		console.error('logsContent element not found');
		return;
	}

	if (logs.length === 0) {
		logsContent.innerHTML = '<div class="alert alert-info">No notification logs found.</div>';
		return;
	}

	let html = `
		<div class="table-responsive">
			<table class="table table-striped table-hover">
				<thead class="table-dark">
					<tr>
						<th>Date/Time</th>
						<th>Schedule</th>
						<th>User</th>
						<th>Subject</th>
						<th>Status</th>
						<th>Error</th>
					</tr>
				</thead>
				<tbody>
	`;

	logs.forEach(log => {
		const statusBadge = log.status === 'sent'
			? '<span class="badge bg-success">Sent</span>'
			: '<span class="badge bg-danger">Failed</span>';

		const errorMessage = log.error_message
			? '<small class="text-danger">' + log.error_message + '</small>'
			: '-';

		html += `
			<tr>
				<td>${log.sent_date}</td>
				<td>${log.schedule_name || 'N/A'}</td>
				<td>${log.user_name || log.user_email || 'Unknown'}</td>
				<td>${log.subject}</td>
				<td>${statusBadge}</td>
				<td>${errorMessage}</td>
			</tr>
		`;
	});

	html += `
				</tbody>
			</table>
		</div>
	`;

	logsContent.innerHTML = html;
}

createScheduleModal = function() {
	// First, get the templates and other data via AJAX and return a Promise
	return fetch('notifications/admin_notifications.php?action=get_form_data')
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				const modalHtml = createScheduleModalHTML(data.templates, data.departments, data.users);
				document.body.insertAdjacentHTML('beforeend', modalHtml);

				// Set default start date to now
				const now = new Date();
				now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
				document.getElementById('start_date').value = now.toISOString().slice(0, 16);
				return true; // Success
			} else {
				alert('Error loading form data: ' + data.error);
				return false; // Failure
			}
		})
		.catch(error => {
			alert('Error loading form data: ' + error.message);
			return false; // Failure
		});
}

createScheduleModalHTML = function(templates, departments, users) {
	let templateOptions = '<option value="">Select Template</option>';
	templates.forEach(template => {
		templateOptions += `<option value="${template.id}">${template.name}</option>`;
	});

	let departmentOptions = '<option value="">Select Department</option>';
	departments.forEach(dept => {
		departmentOptions += `<option value="${dept}">${dept}</option>`;
	});

	let userOptions = '';
	users.forEach(user => {
		userOptions += `<option value="${user.id}">${user.display_name} (${user.email})</option>`;
	});

	return `
		<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="scheduleModalLabel">Create New Notification Schedule</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<form id="scheduleForm" onsubmit="submitScheduleForm(event)">
						<div class="modal-body">
							<input type="hidden" name="action" id="scheduleAction" value="create_schedule">

							<div class="row">
								<div class="col-md-6">
									<div class="mb-3">
										<label for="schedule_name" class="form-label">Schedule Name *</label>
										<input type="text" class="form-control" name="schedule_name" id="schedule_name" required>
									</div>
								</div>
								<div class="col-md-6">
									<div class="mb-3">
										<label for="template_id" class="form-label">Email Template *</label>
										<select class="form-select" name="template_id" id="template_id" required>
											${templateOptions}
										</select>
									</div>
								</div>
							</div>

							<div class="mb-3">
								<label for="schedule_description" class="form-label">Description</label>
								<textarea class="form-control" name="schedule_description" id="schedule_description" rows="2"></textarea>
							</div>

							<div class="row">
								<div class="col-md-6">
									<div class="mb-3">
										<label for="frequency" class="form-label">Frequency *</label>
										<select class="form-select" name="frequency" id="frequency" required onchange="updateFrequencyHelp()">
											<option value="">Select Frequency</option>
											<option value="hourly">Hourly</option>
											<option value="daily">Daily</option>
											<option value="weekly">Weekly</option>
											<option value="monthly">Monthly</option>
										</select>
									</div>
								</div>
								<div class="col-md-6">
									<div class="mb-3">
										<label for="frequency_value" class="form-label">Every <span id="frequencyUnit">unit(s)</span> *</label>
										<input type="number" class="form-control" name="frequency_value" id="frequency_value" min="1" value="1" required>
										<div class="form-text" id="frequencyHelp">Specify interval</div>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-6">
									<div class="mb-3">
										<label for="start_date" class="form-label">Start Date *</label>
										<input type="datetime-local" class="form-control" name="start_date" id="start_date" required>
									</div>
								</div>
								<div class="col-md-6">
									<div class="mb-3">
										<label for="end_date" class="form-label">End Date (Optional)</label>
										<input type="datetime-local" class="form-control" name="end_date" id="end_date">
									</div>
								</div>
							</div>

							<div class="mb-3">
								<label class="form-label">Target Users *</label>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="target_type" id="target_all" value="all_users" checked onchange="toggleTargetOptions()">
									<label class="form-check-label" for="target_all">All Users</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="target_type" id="target_department" value="department" onchange="toggleTargetOptions()">
									<label class="form-check-label" for="target_department">By Department</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="target_type" id="target_specific" value="specific_users" onchange="toggleTargetOptions()">
									<label class="form-check-label" for="target_specific">Specific Users</label>
								</div>
							</div>

							<div id="departmentSelect" class="mb-3" style="display: none;">
								<label for="target_department_select" class="form-label">Select Department</label>
								<select class="form-select" name="target_department" id="target_department_select">
									${departmentOptions}
								</select>
							</div>

							<div id="userSelect" class="mb-3" style="display: none;">
								<label for="selected_users" class="form-label">Select Users</label>
								<select class="form-select" name="selected_users[]" id="selected_users" multiple size="5">
									${userOptions}
								</select>
								<div class="form-text">Hold Ctrl/Cmd to select multiple users</div>
							</div>

							<div class="form-check">
								<input class="form-check-input" type="checkbox" name="schedule_active" id="schedule_active" checked>
								<label class="form-check-label" for="schedule_active">Active</label>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-primary">Create Schedule</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	`;
}

updateFrequencyHelp = function() {
	const frequencyElement = document.getElementById('frequency');
	const frequencyUnit = document.getElementById('frequencyUnit');
	const frequencyHelp = document.getElementById('frequencyHelp');

	if (!frequencyElement || !frequencyUnit || !frequencyHelp) {
		return; // Elements not found, exit gracefully
	}

	const frequency = frequencyElement.value;

	switch(frequency) {
		case 'hourly':
			frequencyUnit.textContent = 'hour(s)';
			frequencyHelp.textContent = 'e.g., Every 2 hours';
			break;
		case 'daily':
			frequencyUnit.textContent = 'day(s)';
			frequencyHelp.textContent = 'e.g., Every 3 days';
			break;
		case 'weekly':
			frequencyUnit.textContent = 'week(s)';
			frequencyHelp.textContent = 'e.g., Every 2 weeks';
			break;
		case 'monthly':
			frequencyUnit.textContent = 'month(s)';
			frequencyHelp.textContent = 'e.g., Every 1 month';
			break;
		default:
			frequencyUnit.textContent = 'unit(s)';
			frequencyHelp.textContent = 'Specify interval';
	}
}

toggleTargetOptions = function() {
	const targetTypeElement = document.querySelector('input[name="target_type"]:checked');
	const departmentSelect = document.getElementById('departmentSelect');
	const userSelect = document.getElementById('userSelect');

	if (!targetTypeElement || !departmentSelect || !userSelect) {
		return; // Elements not found, exit gracefully
	}

	const targetType = targetTypeElement.value;
	departmentSelect.style.display = targetType === 'department' ? 'block' : 'none';
	userSelect.style.display = targetType === 'specific_users' ? 'block' : 'none';
}

createTemplateModal = function() {
	// Get form data via AJAX and return a Promise
	return fetch('notifications/admin_notifications.php?action=get_form_data')
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				// Debug: Log the placeholders structure
				console.log('Placeholders data:', data.placeholders);
				const modalHtml = createTemplateModalHTML(data.email_types, data.placeholders);
				document.body.insertAdjacentHTML('beforeend', modalHtml);
				return true; // Success
			} else {
				alert('Error loading form data: ' + data.error);
				return false; // Failure
			}
		})
		.catch(error => {
			alert('Error loading form data: ' + error.message);
			return false; // Failure
		});
}

// Notification Management Action Functions
editNotificationSchedule = function(id) {
	// Fetch schedule data
	fetch('notifications/admin_notifications.php?action=get_schedule&id=' + id)
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				// Check if modal already exists
				let scheduleModal = document.getElementById('scheduleModal');
				if (!scheduleModal) {
					// Create modal and then populate it
					createScheduleModal().then(() => {
						populateEditScheduleModal(data.schedule);
					});
				} else {
					// Modal exists, populate it directly
					populateEditScheduleModal(data.schedule);
				}
			} else {
				alert('Error loading schedule: ' + data.error);
			}
		})
		.catch(error => {
			alert('Error loading schedule: ' + error.message);
		});
}

populateEditScheduleModal = function(schedule) {
	// Update modal title and action
	const modalLabel = document.getElementById('scheduleModalLabel');
	if (modalLabel) {
		modalLabel.textContent = 'Edit Notification Schedule';
	}

	const actionInput = document.getElementById('scheduleAction');
	if (actionInput) {
		actionInput.value = 'update_schedule';
	}

	// Add hidden schedule ID field
	let scheduleIdInput = document.getElementById('schedule_id');
	if (!scheduleIdInput) {
		scheduleIdInput = document.createElement('input');
		scheduleIdInput.type = 'hidden';
		scheduleIdInput.name = 'schedule_id';
		scheduleIdInput.id = 'schedule_id';
		document.getElementById('scheduleForm').appendChild(scheduleIdInput);
	}
	scheduleIdInput.value = schedule.id;

	// Update submit button text
	const submitButton = document.querySelector('#scheduleForm button[type="submit"]');
	if (submitButton) {
		submitButton.innerHTML = 'Update Schedule';
	}

	// Populate form fields
	const fields = {
		'schedule_name': schedule.name,
		'schedule_description': schedule.description,
		'template_id': schedule.template_id,
		'frequency': schedule.frequency,
		'frequency_value': schedule.frequency_value,
		'start_date': schedule.start_date ? schedule.start_date.replace(' ', 'T').substring(0, 16) : '',
		'end_date': schedule.end_date ? schedule.end_date.replace(' ', 'T').substring(0, 16) : ''
	};

	for (const [fieldName, value] of Object.entries(fields)) {
		const field = document.getElementById(fieldName);
		if (field) {
			field.value = value;
		}
	}

	// Handle active checkbox
	const activeCheckbox = document.getElementById('schedule_active');
	if (activeCheckbox) {
		activeCheckbox.checked = schedule.is_active == 1;
	}

	// Handle target type
	if (schedule.target_users) {
		const targetType = schedule.target_users.type;
		const targetRadio = document.querySelector(`input[name="target_type"][value="${targetType}"]`);
		if (targetRadio) {
			targetRadio.checked = true;
			// Trigger change event to show/hide relevant sections
			if (typeof toggleTargetOptions === 'function') {
				toggleTargetOptions();
			}
		}

		// Set specific values based on target type
		if (targetType === 'department' && schedule.target_users.department) {
			const deptSelect = document.getElementById('target_department');
			if (deptSelect) {
				deptSelect.value = schedule.target_users.department;
			}
		} else if (targetType === 'specific_users' && schedule.target_users.user_ids) {
			// Handle specific users selection (this would need more complex implementation)
			console.log('Specific users:', schedule.target_users.user_ids);
		}
	}

	// Show the modal
	const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
	modal.show();
}

deleteNotificationSchedule = function(id) {
	// Show confirmation dialog with more details
	const confirmMessage = 'Are you sure you want to delete this notification schedule?\n\n' +
		'This action cannot be undone and will:\n' +
		'• Remove the schedule permanently\n' +
		'• Stop all future notifications for this schedule\n' +
		'• Keep existing notification logs for audit purposes\n\n' +
		'Click OK to proceed with deletion.';

	if (confirm(confirmMessage)) {
		// Show loading state
		showNotificationMessage('Deleting schedule...', 'info');

		// Send delete request
		fetch('notifications/admin_notifications.php?action=delete_schedule&id=' + id)
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					// Show success message
					showNotificationMessage('Schedule deleted successfully!', 'success');

					// Reload the notification admin interface to show updated list
					setTimeout(() => {
						if (typeof notificationAdmin === 'function') {
							notificationAdmin();
						} else {
							window.location.reload();
						}
					}, 1500);
				} else {
					showNotificationMessage('Error deleting schedule: ' + data.error, 'error');
				}
			})
			.catch(error => {
				console.error('Error:', error);
				showNotificationMessage('Error deleting schedule: ' + error.message, 'error');
			});
	}
}

editNotificationTemplate = function(id) {
	alert('Edit template functionality will be implemented. Template ID: ' + id);
}

deleteNotificationTemplate = function(id) {
	if (confirm('Are you sure you want to delete this template?')) {
		alert('Delete template functionality will be implemented. Template ID: ' + id);
	}
}

previewNotificationTemplate = function(id) {
	alert('Preview template functionality will be implemented. Template ID: ' + id);
}

testNotificationSystem = function() {
	alert('Test system functionality will be implemented');
}

createTemplateModalHTML = function(email_types, placeholders) {
	let emailTypeOptions = '<option value="">Select Type</option>';
	for (const [key, label] of Object.entries(email_types)) {
		emailTypeOptions += `<option value="${key}">${label}</option>`;
	}

	let placeholderGroups = [];
	try {
		for (const [category, items] of Object.entries(placeholders)) {
			// Handle the case where items is an object with key-value pairs
			let placeholderList;
			if (Array.isArray(items)) {
				// If items is an array, use it directly
				placeholderList = items.map(item => `{{${item}}}`);
			} else if (typeof items === 'object' && items !== null) {
				// If items is an object, extract the keys (which are the placeholders)
				placeholderList = Object.keys(items);
			} else {
				// Fallback for unexpected data types
				console.warn('Unexpected data type for placeholders category:', category, items);
				placeholderList = [];
			}
			placeholderGroups.push(`<strong>${category.charAt(0).toUpperCase() + category.slice(1)}:</strong> ${placeholderList.join(', ')}`);
		}
	} catch (error) {
		console.error('Error processing placeholders:', error, placeholders);
		placeholderGroups = ['<em>Error loading placeholders</em>'];
	}
	const placeholderText = placeholderGroups.join('<br>');

	return `
		<div class="modal fade" id="templateModal" tabindex="-1" aria-labelledby="templateModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="templateModalLabel">Create New Email Template</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<form id="templateForm" onsubmit="submitTemplateForm(event)">
						<div class="modal-body">
							<input type="hidden" name="action" id="templateAction" value="create_template">

							<div class="row">
								<div class="col-md-8">
									<div class="mb-3">
										<label for="template_name" class="form-label">Template Name *</label>
										<input type="text" class="form-control" name="template_name" id="template_name" required>
									</div>
								</div>
								<div class="col-md-4">
									<div class="mb-3">
										<label for="email_type" class="form-label">Email Type *</label>
										<select class="form-select" name="email_type" id="email_type" required>
											${emailTypeOptions}
										</select>
									</div>
								</div>
							</div>

							<div class="mb-3">
								<label for="template_description" class="form-label">Description</label>
								<textarea class="form-control" name="template_description" id="template_description" rows="2"></textarea>
							</div>

							<div class="mb-3">
								<label for="subject" class="form-label">Email Subject *</label>
								<input type="text" class="form-control" name="subject" id="subject" required>
								<div class="form-text">You can use placeholders like {{user_name}}, {{user_email}}, etc.</div>
							</div>

							<div class="mb-3">
								<label for="body_template" class="form-label">Email Body Template *</label>
								<textarea class="form-control" name="body_template" id="body_template" rows="8" required></textarea>
								<div class="form-text">
									<strong>Available placeholders:</strong><br>
									${placeholderText}
								</div>
							</div>

							<div class="form-check">
								<input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
								<label class="form-check-label" for="is_active">Active</label>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-primary">Create Template</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	`;
}

// AJAX Form Submission Functions
submitScheduleForm = function(event) {
	event.preventDefault(); // Prevent default form submission

	const form = document.getElementById('scheduleForm');
	const formData = new FormData(form);
	const submitButton = form.querySelector('button[type="submit"]');
	const action = formData.get('action');
	const isUpdate = action === 'update_schedule';

	// Disable submit button and show loading state
	submitButton.disabled = true;
	submitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${isUpdate ? 'Updating...' : 'Creating...'}`;

	fetch('notifications/admin_notifications.php', {
		method: 'POST',
		body: formData
	})
	.then(response => response.text())
	.then(data => {
		// Close the modal
		const modal = bootstrap.Modal.getInstance(document.getElementById('scheduleModal'));
		modal.hide();

		// Show success message
		const successMessage = isUpdate ? 'Schedule updated successfully!' : 'Schedule created successfully!';
		showNotificationMessage(successMessage, 'success');

		// Reload the notification admin interface to show updated schedules list
		setTimeout(() => {
			if (typeof notificationAdmin === 'function') {
				notificationAdmin();
			} else {
				window.location.reload();
			}
		}, 1500);
	})
	.catch(error => {
		console.error('Error:', error);
		const errorMessage = isUpdate ? 'Error updating schedule: ' : 'Error creating schedule: ';
		showNotificationMessage(errorMessage + error.message, 'error');
	})
	.finally(() => {
		// Re-enable submit button
		submitButton.disabled = false;
		submitButton.innerHTML = isUpdate ? 'Update Schedule' : 'Create Schedule';
	});
}

submitTemplateForm = function(event) {
	event.preventDefault(); // Prevent default form submission

	const form = document.getElementById('templateForm');
	const formData = new FormData(form);
	const submitButton = form.querySelector('button[type="submit"]');

	// Disable submit button and show loading state
	submitButton.disabled = true;
	submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';

	fetch('notifications/admin_notifications.php', {
		method: 'POST',
		body: formData
	})
	.then(response => response.text())
	.then(data => {
		// Close the modal
		const modal = bootstrap.Modal.getInstance(document.getElementById('templateModal'));
		modal.hide();

		// Show success message
		showNotificationMessage('Template created successfully!', 'success');

		// Reload the notification admin interface to show updated templates list
		setTimeout(() => {
			if (typeof notificationAdmin === 'function') {
				notificationAdmin();
			} else {
				window.location.reload();
			}
		}, 1500);
	})
	.catch(error => {
		console.error('Error:', error);
		showNotificationMessage('Error creating template: ' + error.message, 'error');
	})
	.finally(() => {
		// Re-enable submit button
		submitButton.disabled = false;
		submitButton.innerHTML = 'Create Template';
	});
}

showNotificationMessage = function(message, type) {
	// Remove any existing notification
	const existingAlert = document.querySelector('.notification-alert');
	if (existingAlert) {
		existingAlert.remove();
	}

	// Create new notification
	let alertClass, iconClass;
	switch(type) {
		case 'success':
			alertClass = 'alert-success';
			iconClass = 'fa-check-circle';
			break;
		case 'info':
			alertClass = 'alert-info';
			iconClass = 'fa-info-circle';
			break;
		case 'warning':
			alertClass = 'alert-warning';
			iconClass = 'fa-exclamation-triangle';
			break;
		case 'error':
		default:
			alertClass = 'alert-danger';
			iconClass = 'fa-exclamation-triangle';
			break;
	}

	const alertHtml = `
		<div class="alert ${alertClass} alert-dismissible fade show notification-alert" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
			<i class="fas ${iconClass}"></i> ${message}
			<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
		</div>
	`;

	document.body.insertAdjacentHTML('beforeend', alertHtml);

	// Auto-remove after 5 seconds (except for info messages which are shorter)
	const autoRemoveTime = type === 'info' ? 2000 : 5000;
	setTimeout(() => {
		const alert = document.querySelector('.notification-alert');
		if (alert) {
			alert.remove();
		}
	}, autoRemoveTime);
}
});
</script>
<div id="main"></div>