<?php
/**
 * Admin Interface for Notification Management
 *
 * This page provides an admin interface for managing email notifications
 *
 * @author Collins Ojenge
 * @version 1.0
 * @date 2025-06-14
 */

require_once("../admin/models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

require_once('../config/config_mysqli.php');
require_once('notification_scheduler.php');
require_once('notification_templates.php');

$scheduler = new NotificationScheduler($connect);
$templateManager = new NotificationTemplateManager($connect);

// Handle AJAX requests FIRST before any HTML output
if (isset($_GET['action'])) {
    // Prevent any output buffering issues
    ob_clean();
    header('Content-Type: application/json');

    try {
        switch ($_GET['action']) {
        case 'get_schedule':
            if (isset($_GET['id'])) {
                $schedule = $scheduler->getScheduleById($_GET['id']);
                if ($schedule) {
                    echo json_encode(['success' => true, 'schedule' => $schedule]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Schedule not found']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Schedule ID not provided']);
            }
            exit;

        case 'get_logs':
            try {
                // Query to get notification logs with related data
                $sql = "SELECT
                            nl.id,
                            nl.schedule_id,
                            nl.user_id,
                            nl.subject,
                            nl.sent_date,
                            nl.status,
                            nl.error_message,
                            ns.name as schedule_name,
                            u.display_name as user_name,
                            u.email as user_email
                        FROM notification_logs nl
                        LEFT JOIN notification_schedules ns ON nl.schedule_id = ns.id
                        LEFT JOIN uc_users u ON nl.user_id = u.id
                        ORDER BY nl.sent_date DESC
                        LIMIT 100";

                $result = mysqli_query($connect, $sql);

                if (!$result) {
                    throw new Exception('Database query failed: ' . mysqli_error($connect));
                }

                $logs = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    // Format the date for display
                    $row['sent_date'] = date('Y-m-d H:i:s', strtotime($row['sent_date']));
                    $logs[] = $row;
                }

                echo json_encode([
                    'success' => true,
                    'logs' => $logs
                ]);

            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
            exit;

        case 'get_form_data':
            try {
                // Get templates
                $templates = $templateManager->getAllTemplates();

                // Get departments
                $dept_result = mysqli_query($connect, "SELECT DISTINCT department FROM uc_users WHERE active = 1 AND department IS NOT NULL AND department != '' ORDER BY department");
                $departments = [];
                while ($dept = mysqli_fetch_assoc($dept_result)) {
                    $departments[] = $dept['department'];
                }

                // Get users
                $users_result = mysqli_query($connect, "SELECT id, display_name, email FROM uc_users WHERE active = 1 ORDER BY display_name");
                $users = [];
                while ($user = mysqli_fetch_assoc($users_result)) {
                    $users[] = $user;
                }

                // Get email types
                $email_types = $templateManager->getEmailTypes();

                // Get placeholders
                $placeholders = $templateManager->getTemplatePlaceholders();

                echo json_encode([
                    'success' => true,
                    'templates' => $templates,
                    'departments' => $departments,
                    'users' => $users,
                    'email_types' => $email_types,
                    'placeholders' => $placeholders
                ]);

            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
            exit;

        case 'delete_schedule':
            if (isset($_GET['id'])) {
                $schedule_id = (int)$_GET['id'];
                if ($scheduler->deleteSchedule($schedule_id)) {
                    echo json_encode(['success' => true, 'message' => 'Schedule deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to delete schedule']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Schedule ID not provided']);
            }
            exit;

        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        exit;
    }
}

// Get user ID from the logged in user object
$current_user_id = null;
if (isset($loggedInUser) && is_object($loggedInUser)) {
    $current_user_id = $loggedInUser->user_id;
} elseif (isset($_SESSION['user_id'])) {
    // Fallback for direct session access
    $current_user_id = $_SESSION['user_id'];
}

// Check if user is properly logged in
if (!$current_user_id) {
    die('Error: User not properly logged in. Please log in and try again.');
}

// Handle form submissions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_template':
                $template_data = [
                    'name' => $_POST['template_name'],
                    'description' => $_POST['template_description'],
                    'email_type' => $_POST['email_type'],
                    'subject' => $_POST['subject'],
                    'body_template' => $_POST['body_template'],
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'created_by' => $current_user_id
                ];
                
                if ($templateManager->createTemplate($template_data)) {
                    $message = 'Template created successfully!';
                } else {
                    $error = 'Failed to create template.';
                }
                break;
                
            case 'create_schedule':
                $target_users = [];
                if ($_POST['target_type'] === 'all_users') {
                    $target_users = ['type' => 'all_users'];
                } elseif ($_POST['target_type'] === 'specific_users') {
                    $target_users = [
                        'type' => 'specific_users',
                        'user_ids' => isset($_POST['selected_users']) ? $_POST['selected_users'] : []
                    ];
                } elseif ($_POST['target_type'] === 'department') {
                    $target_users = [
                        'type' => 'department',
                        'department' => $_POST['target_department']
                    ];
                }
                
                $schedule_data = [
                    'name' => $_POST['schedule_name'],
                    'description' => $_POST['schedule_description'],
                    'template_id' => $_POST['template_id'],
                    'frequency' => $_POST['frequency'],
                    'frequency_value' => $_POST['frequency_value'],
                    'start_date' => $_POST['start_date'],
                    'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                    'is_active' => isset($_POST['schedule_active']) ? 1 : 0,
                    'target_users' => $target_users,
                    'created_by' => $current_user_id
                ];
                
                if ($scheduler->createSchedule($schedule_data)) {
                    $message = 'Schedule created successfully!';
                } else {
                    $error = 'Failed to create schedule.';
                }
                break;

            case 'update_schedule':
                $target_users = [];
                if ($_POST['target_type'] === 'all_users') {
                    $target_users = ['type' => 'all_users'];
                } elseif ($_POST['target_type'] === 'specific_users') {
                    $target_users = [
                        'type' => 'specific_users',
                        'user_ids' => isset($_POST['selected_users']) ? $_POST['selected_users'] : []
                    ];
                } elseif ($_POST['target_type'] === 'department') {
                    $target_users = [
                        'type' => 'department',
                        'department' => $_POST['target_department']
                    ];
                }

                $schedule_data = [
                    'id' => $_POST['schedule_id'],
                    'name' => $_POST['schedule_name'],
                    'description' => $_POST['schedule_description'],
                    'template_id' => $_POST['template_id'],
                    'frequency' => $_POST['frequency'],
                    'frequency_value' => $_POST['frequency_value'],
                    'start_date' => $_POST['start_date'],
                    'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                    'is_active' => isset($_POST['schedule_active']) ? 1 : 0,
                    'target_users' => $target_users
                ];

                if ($scheduler->updateSchedule($schedule_data)) {
                    $message = 'Schedule updated successfully!';
                } else {
                    $error = 'Failed to update schedule.';
                }
                break;


        }
    }
}



// Get data for display
$templates = $templateManager->getAllTemplates();
$email_types = $templateManager->getEmailTypes();
$placeholders = $templateManager->getTemplatePlaceholders();

// Get all users for targeting
$users_result = mysqli_query($connect, "SELECT id, display_name, email, department FROM uc_users WHERE active = 1 ORDER BY display_name");
$users = [];
while ($user = mysqli_fetch_assoc($users_result)) {
    $users[] = $user;
}

// Get departments
$dept_result = mysqli_query($connect, "SELECT DISTINCT department FROM uc_users WHERE active = 1 AND department IS NOT NULL AND department != '' ORDER BY department");
$departments = [];
while ($dept = mysqli_fetch_assoc($dept_result)) {
    $departments[] = $dept['department'];
}

// Get existing schedules
$schedules_result = mysqli_query($connect, "
    SELECT ns.*, nt.name as template_name 
    FROM notification_schedules ns 
    JOIN notification_templates nt ON ns.template_id = nt.id 
    ORDER BY ns.created_date DESC
");
$schedules = [];
while ($schedule = mysqli_fetch_assoc($schedules_result)) {
    $schedules[] = $schedule;
}
echo "
</div>
<div id='main'>";

echo resultBlock($errors,$successes);

echo "
<h3><i class='fas fa-bell'></i> Notification Management</h3>";

if ($message) {
    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>";
    echo htmlspecialchars($message);
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
    echo "</div>";
}

if ($error) {
    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>";
    echo htmlspecialchars($error);
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
    echo "</div>";
}

// Display notification schedules table
echo "
<div class='row mt-4'>
    <div class='col-12'>
        <h4><i class='fas fa-calendar-alt'></i> Active Notification Schedules</h4>
        <div class='table-responsive'>
            <table class='table table-striped table-hover'>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Template</th>
                        <th>Frequency</th>
                        <th>Start Date</th>
                        <th>Last Executed</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>";

// Display schedules
foreach ($schedules as $schedule) {
    $status_class = $schedule['is_active'] ? 'text-success' : 'text-danger';
    $status_text = $schedule['is_active'] ? 'Active' : 'Inactive';
    $last_executed = $schedule['last_executed'] ? date('Y-m-d H:i', strtotime($schedule['last_executed'])) : 'Never';

    echo "<tr>
        <td>" . htmlspecialchars($schedule['name']) . "</td>
        <td>" . htmlspecialchars($schedule['template_name']) . "</td>
        <td>" . ucfirst($schedule['frequency']) . " (" . $schedule['frequency_value'] . ")</td>
        <td>" . date('Y-m-d H:i', strtotime($schedule['start_date'])) . "</td>
        <td>$last_executed</td>
        <td><span class='$status_class'><strong>$status_text</strong></span></td>
        <td>
            <button class='btn btn-sm btn-primary' onclick='editNotificationSchedule({$schedule['id']})'>
                <i class='fas fa-edit'></i> Edit
            </button>
            <button class='btn btn-sm btn-danger' onclick='deleteNotificationSchedule({$schedule['id']})'>
                <i class='fas fa-trash'></i> Delete
            </button>
        </td>
    </tr>";
}

echo "
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class='row mt-4'>
    <div class='col-12'>
        <h4><i class='fas fa-file-alt'></i> Email Templates</h4>
        <div class='table-responsive'>
            <table class='table table-striped table-hover'>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>";

// Display templates
foreach ($templates as $template) {
    $status_class = $template['is_active'] ? 'text-success' : 'text-danger';
    $status_text = $template['is_active'] ? 'Active' : 'Inactive';
    $subject_preview = htmlspecialchars(substr($template['subject'], 0, 50)) . (strlen($template['subject']) > 50 ? '...' : '');
    $type_label = htmlspecialchars($email_types[$template['email_type']] ?? $template['email_type']);

    echo "<tr>
        <td>" . htmlspecialchars($template['name']) . "</td>
        <td>$type_label</td>
        <td>$subject_preview</td>
        <td><span class='$status_class'><strong>$status_text</strong></span></td>
        <td>
            <button class='btn btn-sm btn-info' onclick='previewNotificationTemplate({$template['id']})'>
                <i class='fas fa-eye'></i> Preview
            </button>
            <button class='btn btn-sm btn-primary' onclick='editNotificationTemplate({$template['id']})'>
                <i class='fas fa-edit'></i> Edit
            </button>
            <button class='btn btn-sm btn-danger' onclick='deleteNotificationTemplate({$template['id']})'>
                <i class='fas fa-trash'></i> Delete
            </button>
        </td>
    </tr>";
}

echo "
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class='row mt-4'>
    <div class='col-12'>
        <p><strong>Quick Actions:</strong></p>
        <button class='btn btn-success me-2' onclick='createNotificationSchedule()'>
            <i class='fas fa-plus'></i> Create New Schedule
        </button>
        <button class='btn btn-info me-2' onclick='createNotificationTemplate()'>
            <i class='fas fa-file-plus'></i> Create New Template
        </button>
        <button class='btn btn-warning me-2' onclick='viewNotificationLogs()'>
            <i class='fas fa-list'></i> View Logs
        </button>
        <button class='btn btn-secondary' onclick='testNotificationSystem()'>
            <i class='fas fa-vial'></i> Test System
        </button>
    </div>
</div>
</div>";

?>

<script>
function editNotificationSchedule(id) {
    alert('Edit schedule functionality will be implemented. Schedule ID: ' + id);
}

function deleteNotificationSchedule(id) {
    if (confirm('Are you sure you want to delete this schedule?')) {
        alert('Delete schedule functionality will be implemented. Schedule ID: ' + id);
    }
}

function editNotificationTemplate(id) {
    alert('Edit template functionality will be implemented. Template ID: ' + id);
}

function deleteNotificationTemplate(id) {
    if (confirm('Are you sure you want to delete this template?')) {
        alert('Delete template functionality will be implemented. Template ID: ' + id);
    }
}

function previewNotificationTemplate(id) {
    alert('Preview template functionality will be implemented. Template ID: ' + id);
}

function createNotificationSchedule() {
    // Check if modal already exists
    let scheduleModal = document.getElementById('scheduleModal');
    if (!scheduleModal) {
        createScheduleModal();
    }

    // Reset form
    document.getElementById('scheduleForm').reset();
    document.getElementById('scheduleModalLabel').textContent = 'Create New Notification Schedule';
    document.getElementById('scheduleAction').value = 'create_schedule';

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
    modal.show();
}

function createNotificationTemplate() {
    // Check if modal already exists
    let templateModal = document.getElementById('templateModal');
    if (!templateModal) {
        createTemplateModal();
    }

    // Reset form
    document.getElementById('templateForm').reset();
    document.getElementById('templateModalLabel').textContent = 'Create New Email Template';
    document.getElementById('templateAction').value = 'create_template';

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('templateModal'));
    modal.show();
}

function viewNotificationLogs() {
    // Show loading state
    const logsModal = document.getElementById('logsModal');
    const logsContent = document.getElementById('logsContent');

    if (!logsModal) {
        // Create modal if it doesn't exist
        createLogsModal();
    }

    logsContent.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading logs...</div>';

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('logsModal'));
    modal.show();

    // Fetch logs via AJAX
    fetch('admin_notifications.php?action=get_logs')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayLogs(data.logs);
            } else {
                logsContent.innerHTML = '<div class="alert alert-danger">Error loading logs: ' + data.error + '</div>';
            }
        })
        .catch(error => {
            logsContent.innerHTML = '<div class="alert alert-danger">Error loading logs: ' + error.message + '</div>';
        });
}

function createLogsModal() {
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
                        <div id="logsContent">
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

function displayLogs(logs) {
    const logsContent = document.getElementById('logsContent');

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

function createScheduleModal() {
    const modalHtml = `
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
                                            <option value="">Select Template</option>
                                            <?php foreach ($templates as $template): ?>
                                                <option value="<?= $template['id'] ?>"><?= htmlspecialchars($template['name']) ?></option>
                                            <?php endforeach; ?>
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
                                <label for="target_department" class="form-label">Select Department</label>
                                <select class="form-select" name="target_department" id="target_department_select">
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="userSelect" class="mb-3" style="display: none;">
                                <label for="selected_users" class="form-label">Select Users</label>
                                <select class="form-select" name="selected_users[]" id="selected_users" multiple size="5">
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['display_name']) ?> (<?= htmlspecialchars($user['email']) ?>)</option>
                                    <?php endforeach; ?>
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

    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Set default start date to now
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.getElementById('start_date').value = now.toISOString().slice(0, 16);
}

function updateFrequencyHelp() {
    const frequency = document.getElementById('frequency').value;
    const frequencyUnit = document.getElementById('frequencyUnit');
    const frequencyHelp = document.getElementById('frequencyHelp');

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

function toggleTargetOptions() {
    const targetType = document.querySelector('input[name="target_type"]:checked').value;
    const departmentSelect = document.getElementById('departmentSelect');
    const userSelect = document.getElementById('userSelect');

    departmentSelect.style.display = targetType === 'department' ? 'block' : 'none';
    userSelect.style.display = targetType === 'specific_users' ? 'block' : 'none';
}

function createTemplateModal() {
    const modalHtml = `
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
                                            <option value="">Select Type</option>
                                            <?php foreach ($email_types as $key => $label): ?>
                                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                                            <?php endforeach; ?>
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
                                    <?php
                                    $placeholder_groups = [];
                                    foreach ($placeholders as $category => $items) {
                                        $placeholder_list = array_map(function($item) { return '{{' . $item . '}}'; }, $items);
                                        $placeholder_groups[] = '<strong>' . ucfirst($category) . ':</strong> ' . implode(', ', $placeholder_list);
                                    }
                                    echo implode('<br>', $placeholder_groups);
                                    ?>
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

    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function testNotificationSystem() {
    alert('Test system functionality will be implemented');
}

function submitScheduleForm(event) {
    event.preventDefault(); // Prevent default form submission

    const form = document.getElementById('scheduleForm');
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
        const modal = bootstrap.Modal.getInstance(document.getElementById('scheduleModal'));
        modal.hide();

        // Show success message
        showNotificationMessage('Schedule created successfully!', 'success');

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
        showNotificationMessage('Error creating schedule: ' + error.message, 'error');
    })
    .finally(() => {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.innerHTML = 'Create Schedule';
    });
}

function submitTemplateForm(event) {
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

function showNotificationMessage(message, type) {
    // Remove any existing notification
    const existingAlert = document.querySelector('.notification-alert');
    if (existingAlert) {
        existingAlert.remove();
    }

    // Create new notification
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';

    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show notification-alert" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fas ${iconClass}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', alertHtml);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.notification-alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}
</script>
