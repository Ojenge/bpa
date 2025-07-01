<?php
/**
 * Integration Helper for Notification System
 * 
 * This file provides easy integration functions for the main application
 * 
 * @author Collins Ojenge
 * @version 1.0
 * @date 2025-06-14
 */

require_once('notification_scheduler.php');
require_once('notification_templates.php');

class NotificationIntegration {
    
    private $db;
    private $scheduler;
    private $templateManager;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
        $this->scheduler = new NotificationScheduler($database_connection);
        $this->templateManager = new NotificationTemplateManager($database_connection);
    }
    
    /**
     * Send immediate notification to specific user
     * 
     * @param int $user_id
     * @param string $template_type
     * @param array $custom_data
     * @return bool
     */
    public function sendImmediateNotification($user_id, $template_type, $custom_data = []) {
        try {
            // Get user info
            $user_result = mysqli_query($this->db, "SELECT * FROM uc_users WHERE id = $user_id AND active = 1");
            $user = mysqli_fetch_assoc($user_result);
            
            if (!$user) {
                return false;
            }
            
            // Get template
            $templates = $this->templateManager->getTemplatesByType($template_type);
            if (empty($templates)) {
                return false;
            }
            
            $template = $templates[0]; // Use first template of this type
            
            // Process template with custom data
            $processed = $this->processTemplateWithData($template, $user, $custom_data);
            
            // Send email
            return $this->sendEmail($user, $processed);
            
        } catch (Exception $e) {
            error_log("Failed to send immediate notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a quick notification schedule
     * 
     * @param string $name
     * @param string $template_type
     * @param string $frequency
     * @param array $target_users
     * @param int $created_by
     * @return bool|int
     */
    public function createQuickSchedule($name, $template_type, $frequency, $target_users, $created_by) {
        // Get template
        $templates = $this->templateManager->getTemplatesByType($template_type);
        if (empty($templates)) {
            return false;
        }
        
        $schedule_data = [
            'name' => $name,
            'description' => "Quick schedule for $template_type notifications",
            'template_id' => $templates[0]['id'],
            'frequency' => $frequency,
            'frequency_value' => 1,
            'start_date' => date('Y-m-d H:i:s'),
            'end_date' => null,
            'is_active' => 1,
            'target_users' => $target_users,
            'created_by' => $created_by
        ];
        
        return $this->scheduler->createSchedule($schedule_data);
    }
    
    /**
     * Get user notification preferences
     * 
     * @param int $user_id
     * @return array
     */
    public function getUserPreferences($user_id) {
        $sql = "SELECT * FROM user_notification_preferences WHERE user_id = $user_id";
        $result = mysqli_query($this->db, $sql);
        
        $preferences = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $preferences[$row['notification_type']] = $row;
        }
        
        return $preferences;
    }
    
    /**
     * Set user notification preference
     * 
     * @param int $user_id
     * @param string $notification_type
     * @param bool $enabled
     * @param string $frequency_override
     * @return bool
     */
    public function setUserPreference($user_id, $notification_type, $enabled, $frequency_override = null) {
        $user_id = (int)$user_id;
        $notification_type = mysqli_real_escape_string($this->db, $notification_type);
        $enabled = $enabled ? 1 : 0;
        $frequency_override = $frequency_override ? mysqli_real_escape_string($this->db, $frequency_override) : null;
        
        // Delete existing preference
        mysqli_query($this->db, "DELETE FROM user_notification_preferences WHERE user_id = $user_id AND notification_type = '$notification_type'");
        
        // Insert new preference
        $sql = "INSERT INTO user_notification_preferences 
                (user_id, notification_type, is_enabled, frequency_override, created_date, modified_date) 
                VALUES 
                ($user_id, '$notification_type', $enabled, " . 
                ($frequency_override ? "'$frequency_override'" : "NULL") . ", NOW(), NOW())";
        
        return mysqli_query($this->db, $sql);
    }
    
    /**
     * Get notification statistics
     * 
     * @return array
     */
    public function getNotificationStats() {
        $stats = [];
        
        // Total schedules
        $result = mysqli_query($this->db, "SELECT COUNT(*) as count FROM notification_schedules WHERE is_active = 1");
        $stats['active_schedules'] = mysqli_fetch_assoc($result)['count'];
        
        // Total templates
        $result = mysqli_query($this->db, "SELECT COUNT(*) as count FROM notification_templates WHERE is_active = 1");
        $stats['active_templates'] = mysqli_fetch_assoc($result)['count'];
        
        // Notifications sent today
        $result = mysqli_query($this->db, "SELECT COUNT(*) as count FROM notification_logs WHERE DATE(sent_date) = CURDATE()");
        $stats['sent_today'] = mysqli_fetch_assoc($result)['count'];
        
        // Notifications sent this week
        $result = mysqli_query($this->db, "SELECT COUNT(*) as count FROM notification_logs WHERE YEARWEEK(sent_date) = YEARWEEK(NOW())");
        $stats['sent_this_week'] = mysqli_fetch_assoc($result)['count'];
        
        return $stats;
    }
    
    /**
     * Check if user should receive notification type
     * 
     * @param int $user_id
     * @param string $notification_type
     * @return bool
     */
    public function shouldUserReceiveNotification($user_id, $notification_type) {
        $sql = "SELECT is_enabled FROM user_notification_preferences 
                WHERE user_id = $user_id AND notification_type = '$notification_type'";
        
        $result = mysqli_query($this->db, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return (bool)$row['is_enabled'];
        }
        
        // Default to enabled if no preference set
        return true;
    }
    
    /**
     * Process template with custom data
     * 
     * @param array $template
     * @param array $user
     * @param array $custom_data
     * @return array
     */
    private function processTemplateWithData($template, $user, $custom_data) {
        $placeholders = [
            '{{user_name}}' => $user['display_name'],
            '{{user_email}}' => $user['email'],
            '{{user_title}}' => $user['title'],
            '{{user_department}}' => $user['department'],
            '{{current_date}}' => date('Y-m-d'),
            '{{current_datetime}}' => date('Y-m-d H:i:s')
        ];
        
        // Merge with custom data
        $placeholders = array_merge($placeholders, $custom_data);
        
        $processed_subject = str_replace(array_keys($placeholders), array_values($placeholders), $template['subject']);
        $processed_body = str_replace(array_keys($placeholders), array_values($placeholders), $template['body_template']);
        
        return [
            'subject' => $processed_subject,
            'body' => $processed_body,
            'email_type' => $template['email_type']
        ];
    }
    
    /**
     * Send email (simplified version)
     * 
     * @param array $user
     * @param array $template
     * @return bool
     */
    private function sendEmail($user, $template) {
        // This is a simplified version - in production you'd use PHPMailer
        $to = $user['email'];
        $subject = $template['subject'];
        $message = nl2br($template['body']);
        
        $headers = "From: admin@accent-analytics.com\r\n";
        $headers .= "Reply-To: admin@accent-analytics.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $success = mail($to, $subject, $message, $headers);
        
        if ($success) {
            // Log to mail table
            $subject_escaped = mysqli_real_escape_string($this->db, $subject);
            $message_escaped = mysqli_real_escape_string($this->db, $message);
            $to_escaped = mysqli_real_escape_string($this->db, $to);
            
            $sql = "INSERT INTO mail (title, message, sender, recipient, time, type, user_id) 
                    VALUES ('$subject_escaped', '$message_escaped', 'admin@accent-analytics.com', '$to_escaped', NOW(), 'immediate', '{$user['id']}')";
            mysqli_query($this->db, $sql);
        }
        
        return $success;
    }
}

// Convenience functions for easy integration

/**
 * Send a quick notification
 * 
 * @param int $user_id
 * @param string $type
 * @param array $data
 * @return bool
 */
function sendQuickNotification($user_id, $type, $data = []) {
    global $connect;
    $integration = new NotificationIntegration($connect);
    return $integration->sendImmediateNotification($user_id, $type, $data);
}

/**
 * Check if user wants notifications
 * 
 * @param int $user_id
 * @param string $type
 * @return bool
 */
function userWantsNotification($user_id, $type) {
    global $connect;
    $integration = new NotificationIntegration($connect);
    return $integration->shouldUserReceiveNotification($user_id, $type);
}

/**
 * Get notification stats for dashboard
 * 
 * @return array
 */
function getNotificationDashboardStats() {
    global $connect;
    $integration = new NotificationIntegration($connect);
    return $integration->getNotificationStats();
}

?>
