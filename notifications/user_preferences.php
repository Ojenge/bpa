<?php
/**
 * User Notification Preferences
 * 
 * This page allows users to manage their notification preferences
 * 
 * @author Collins Ojenge
 * @version 1.0
 * @date 2025-06-14
 */

require_once("../admin/models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

require_once('../config/config_mysqli.php');
require_once('notification_templates.php');

// Get user ID from the logged in user object
$user_id = null;
if (isset($loggedInUser) && is_object($loggedInUser)) {
    $user_id = $loggedInUser->user_id;
} elseif (isset($_SESSION['user_id'])) {
    // Fallback for direct session access
    $user_id = $_SESSION['user_id'];
}

// Check if user is properly logged in
if (!$user_id) {
    header('Location: ../index.php');
    exit;
}

$templateManager = new NotificationTemplateManager($connect);

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_preferences') {
    // Delete existing preferences for this user
    mysqli_query($connect, "DELETE FROM user_notification_preferences WHERE user_id = $user_id");
    
    // Insert new preferences
    $success = true;
    foreach ($_POST['notifications'] as $notification_type => $settings) {
        $is_enabled = isset($settings['enabled']) ? 1 : 0;
        $frequency_override = isset($settings['frequency']) ? mysqli_real_escape_string($connect, $settings['frequency']) : null;
        
        $sql = "INSERT INTO user_notification_preferences 
                (user_id, notification_type, is_enabled, frequency_override, created_date, modified_date) 
                VALUES 
                ($user_id, '$notification_type', $is_enabled, " . 
                ($frequency_override ? "'$frequency_override'" : "NULL") . ", NOW(), NOW())";
        
        if (!mysqli_query($connect, $sql)) {
            $success = false;
            break;
        }
    }
    
    if ($success) {
        $message = 'Preferences saved successfully!';
    } else {
        $error = 'Failed to save preferences. Please try again.';
    }
}

// Get current user preferences
$current_preferences = [];
$prefs_result = mysqli_query($connect, "SELECT * FROM user_notification_preferences WHERE user_id = $user_id");
while ($pref = mysqli_fetch_assoc($prefs_result)) {
    $current_preferences[$pref['notification_type']] = $pref;
}

// Get available email types
$email_types = $templateManager->getEmailTypes();

// Get user information
$user_result = mysqli_query($connect, "SELECT display_name, email FROM uc_users WHERE id = $user_id");
$user_info = mysqli_fetch_assoc($user_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Preferences - KDIC Analytics</title>
    <link href="../../bootstrap/5.2.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .preference-card {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
        .preference-header {
            background-color: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            border-radius: 0.375rem 0.375rem 0 0;
        }
        .preference-body {
            padding: 1rem;
        }
        .notification-toggle {
            transform: scale(1.2);
        }
        .frequency-options {
            margin-top: 0.5rem;
            padding-left: 2rem;
        }
        .disabled-section {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-cog"></i> Notification Preferences</h4>
                        <p class="mb-0 text-muted">Manage your email notification settings</p>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <h6><i class="fas fa-user"></i> User Information</h6>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($user_info['display_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($user_info['email']); ?></p>
                        </div>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="save_preferences">
                            
                            <h6><i class="fas fa-bell"></i> Notification Types</h6>
                            <p class="text-muted">Choose which types of notifications you want to receive and how often.</p>
                            
                            <?php foreach ($email_types as $type => $label): ?>
                                <?php 
                                $is_enabled = isset($current_preferences[$type]) ? $current_preferences[$type]['is_enabled'] : 1;
                                $frequency_override = isset($current_preferences[$type]) ? $current_preferences[$type]['frequency_override'] : null;
                                ?>
                                
                                <div class="preference-card">
                                    <div class="preference-header">
                                        <div class="form-check">
                                            <input class="form-check-input notification-toggle" 
                                                   type="checkbox" 
                                                   id="notification_<?php echo $type; ?>" 
                                                   name="notifications[<?php echo $type; ?>][enabled]" 
                                                   value="1"
                                                   <?php echo $is_enabled ? 'checked' : ''; ?>
                                                   onchange="toggleFrequencyOptions('<?php echo $type; ?>')">
                                            <label class="form-check-label" for="notification_<?php echo $type; ?>">
                                                <strong><?php echo htmlspecialchars($label); ?></strong>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="preference-body" id="frequency_<?php echo $type; ?>" 
                                         <?php echo !$is_enabled ? 'style="display: none;"' : ''; ?>>
                                        <div class="frequency-options">
                                            <label class="form-label">Frequency Override (Optional)</label>
                                            <select class="form-select" name="notifications[<?php echo $type; ?>][frequency]">
                                                <option value="">Use schedule default</option>
                                                <option value="immediate" <?php echo $frequency_override === 'immediate' ? 'selected' : ''; ?>>
                                                    Immediate
                                                </option>
                                                <option value="daily" <?php echo $frequency_override === 'daily' ? 'selected' : ''; ?>>
                                                    Daily digest
                                                </option>
                                                <option value="weekly" <?php echo $frequency_override === 'weekly' ? 'selected' : ''; ?>>
                                                    Weekly digest
                                                </option>
                                                <option value="monthly" <?php echo $frequency_override === 'monthly' ? 'selected' : ''; ?>>
                                                    Monthly digest
                                                </option>
                                            </select>
                                            <small class="form-text text-muted">
                                                Leave as "Use schedule default" to receive notifications according to the system schedule.
                                            </small>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <?php
                                                switch ($type) {
                                                    case 'measure_reminder':
                                                        echo 'Reminds you to update your performance measures when they are overdue.';
                                                        break;
                                                    case 'initiative_update':
                                                        echo 'Reminds you to update the status of your initiatives.';
                                                        break;
                                                    case 'performance_summary':
                                                        echo 'Provides periodic summaries of your performance metrics.';
                                                        break;
                                                    case 'system_announcement':
                                                        echo 'Important system announcements and updates.';
                                                        break;
                                                    case 'deadline_alert':
                                                        echo 'Alerts about upcoming deadlines for your tasks and initiatives.';
                                                        break;
                                                    case 'weekly_digest':
                                                        echo 'Weekly summary of your activities and performance.';
                                                        break;
                                                    case 'monthly_report':
                                                        echo 'Monthly performance reports and analytics.';
                                                        break;
                                                    default:
                                                        echo 'Notifications related to ' . strtolower($label) . '.';
                                                }
                                                ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="mt-4">
                                <h6><i class="fas fa-info-circle"></i> Additional Information</h6>
                                <div class="alert alert-info">
                                    <ul class="mb-0">
                                        <li><strong>Immediate:</strong> Receive notifications as soon as they are triggered</li>
                                        <li><strong>Daily digest:</strong> Receive a summary of notifications once per day</li>
                                        <li><strong>Weekly digest:</strong> Receive a summary of notifications once per week</li>
                                        <li><strong>Monthly digest:</strong> Receive a summary of notifications once per month</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <button type="button" class="btn btn-secondary me-md-2" onclick="resetToDefaults()">
                                    <i class="fas fa-undo"></i> Reset to Defaults
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Preferences
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h6><i class="fas fa-question-circle"></i> Need Help?</h6>
                    </div>
                    <div class="card-body">
                        <p>If you have questions about notifications or need to report an issue:</p>
                        <ul>
                            <li>Contact your system administrator</li>
                            <li>Email: admin@accent-analytics.com</li>
                            <li>Check the system documentation for more details</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../bootstrap/5.2.3/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleFrequencyOptions(type) {
            const checkbox = document.getElementById('notification_' + type);
            const frequencyDiv = document.getElementById('frequency_' + type);
            
            if (checkbox.checked) {
                frequencyDiv.style.display = 'block';
            } else {
                frequencyDiv.style.display = 'none';
            }
        }
        
        function resetToDefaults() {
            if (confirm('Are you sure you want to reset all preferences to defaults? This will enable all notification types.')) {
                // Enable all checkboxes
                document.querySelectorAll('.notification-toggle').forEach(checkbox => {
                    checkbox.checked = true;
                    const type = checkbox.id.replace('notification_', '');
                    toggleFrequencyOptions(type);
                });
                
                // Reset all frequency selects to default
                document.querySelectorAll('select[name*="frequency"]').forEach(select => {
                    select.value = '';
                });
            }
        }
        
        // Initialize frequency options visibility on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.notification-toggle').forEach(checkbox => {
                const type = checkbox.id.replace('notification_', '');
                toggleFrequencyOptions(type);
            });
        });
    </script>
</body>
</html>
