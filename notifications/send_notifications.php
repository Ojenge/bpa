<?php
/**
 * Email Notification Sender
 * 
 * This script processes scheduled notifications and sends emails
 * 
 * @author Collins Ojenge
 * @version 1.0
 * @date 2025-06-14
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../PHPMailer/src/Exception.php';
require __DIR__ . '/../../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../../PHPMailer/src/SMTP.php';

require_once('notification_scheduler.php');
require_once('notification_templates.php');

class NotificationSender {
    
    private $db;
    private $scheduler;
    private $templateManager;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
        $this->scheduler = new NotificationScheduler($database_connection);
        $this->templateManager = new NotificationTemplateManager($database_connection);
    }
    
    /**
     * Process all due notifications
     */
    public function processDueNotifications() {
        $due_schedules = $this->scheduler->getDueSchedules();

        foreach ($due_schedules as $schedule) {
            $this->processSchedule($schedule);
        }
    }

    /**
     * Send a single notification to a user
     *
     * @param array $user
     * @param array $template
     * @param array $schedule
     * @return bool
     */
    public function sendNotificationToUser($user, $template, $schedule) {
        // Check user preferences
        if (!$this->shouldSendToUser($user, $schedule)) {
            return true; // Not a failure, just skipped
        }

        // Process template with user data
        $processed_template = $this->processTemplate($template, $user, $schedule);

        // Send email
        return $this->sendEmail($user, $processed_template, $schedule);
    }
    
    /**
     * Process a single notification schedule
     * 
     * @param array $schedule
     */
    private function processSchedule($schedule) {
        try {
            // Get target users
            $users = $this->scheduler->getTargetUsers($schedule);
            
            if (empty($users)) {
                $this->logMessage("No target users found for schedule: " . $schedule['name']);
                return;
            }
            
            // Process template
            $template_data = $this->templateManager->getTemplate($schedule['template_id']);
            if (!$template_data) {
                $this->logError("Template not found for schedule: " . $schedule['name']);
                return;
            }
            
            $sent_count = 0;
            $failed_count = 0;
            
            foreach ($users as $user) {
                // Check user notification preferences
                if (!$this->shouldSendToUser($user, $schedule)) {
                    continue;
                }
                
                // Process template with user data
                $processed_template = $this->processTemplate($template_data, $user, $schedule);
                
                // Send email
                if ($this->sendEmail($user, $processed_template, $schedule)) {
                    $sent_count++;
                    $this->logNotificationSent($schedule['id'], $user['id'], $processed_template['subject']);
                } else {
                    $failed_count++;
                }
            }
            
            // Update last executed timestamp
            $this->scheduler->updateLastExecuted($schedule['id']);
            
            $this->logMessage("Schedule '{$schedule['name']}' processed: {$sent_count} sent, {$failed_count} failed");
            
        } catch (\Exception $e) {
            $this->logError("Error processing schedule '{$schedule['name']}': " . $e->getMessage());
        }
    }
    
    /**
     * Check if notification should be sent to user based on preferences
     * 
     * @param array $user
     * @param array $schedule
     * @return bool
     */
    private function shouldSendToUser($user, $schedule) {
        // Check if user has disabled this type of notification
        $sql = "SELECT * FROM user_notification_preferences 
                WHERE user_id = {$user['id']} 
                AND notification_type = '{$schedule['email_type']}' 
                AND is_enabled = 0";
        
        $result = mysqli_query($this->db, $sql);
        
        // If preference exists and is disabled, don't send
        if ($result && mysqli_num_rows($result) > 0) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Process template with user and schedule data
     * 
     * @param array $template
     * @param array $user
     * @param array $schedule
     * @return array
     */
    private function processTemplate($template, $user, $schedule) {
        $placeholders = [
            '{{user_name}}' => $user['display_name'],
            '{{user_email}}' => $user['email'],
            '{{user_title}}' => $user['title'],
            '{{user_department}}' => $user['department'],
            '{{current_date}}' => date('Y-m-d'),
            '{{current_datetime}}' => date('Y-m-d H:i:s'),
            '{{schedule_name}}' => $schedule['name']
        ];
        
        // Add dynamic data based on notification type
        $dynamic_data = $this->getDynamicData($template['email_type'], $user);
        $placeholders = array_merge($placeholders, $dynamic_data);
        
        $processed_subject = str_replace(array_keys($placeholders), array_values($placeholders), $template['subject']);
        $processed_body = str_replace(array_keys($placeholders), array_values($placeholders), $template['body_template']);
        
        return [
            'subject' => $processed_subject,
            'body' => $processed_body,
            'email_type' => $template['email_type']
        ];
    }
    
    /**
     * Get dynamic data based on notification type
     * 
     * @param string $email_type
     * @param array $user
     * @return array
     */
    private function getDynamicData($email_type, $user) {
        $data = [];
        
        switch ($email_type) {
            case 'measure_reminder':
                $data = $this->getMeasureReminderData($user);
                break;
            case 'initiative_update':
                $data = $this->getInitiativeUpdateData($user);
                break;
            case 'performance_summary':
                $data = $this->getPerformanceSummaryData($user);
                break;
            case 'system_announcement':
                $data = $this->getSystemAnnouncementData($user);
                break;
        }
        
        return $data;
    }
    
    /**
     * Get measure reminder data for user
     * 
     * @param array $user
     * @return array
     */
    private function getMeasureReminderData($user) {
        // Get measures that need updating
        $sql = "SELECT m.id, m.name, m.frequency, 
                       COALESCE(MAX(md.date), MAX(mw.date), MAX(mm.date)) as last_update
                FROM measure m
                LEFT JOIN measuredays md ON m.id = md.measureId
                LEFT JOIN measureweeks mw ON m.id = mw.measureId  
                LEFT JOIN measuremonths mm ON m.id = mm.measureId
                WHERE m.owner = '{$user['user_id']}'
                GROUP BY m.id, m.name, m.frequency
                HAVING last_update IS NULL OR 
                       (m.frequency = 'daily' AND last_update < DATE_SUB(NOW(), INTERVAL 1 DAY)) OR
                       (m.frequency = 'weekly' AND last_update < DATE_SUB(NOW(), INTERVAL 1 WEEK)) OR
                       (m.frequency = 'monthly' AND last_update < DATE_SUB(NOW(), INTERVAL 1 MONTH))";
        
        $result = mysqli_query($this->db, $sql);
        $measures = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $measures[] = $row;
            }
        }
        
        $measure_list = '';
        foreach ($measures as $measure) {
            $measure_list .= "- {$measure['name']} (Last updated: " . 
                           ($measure['last_update'] ? $measure['last_update'] : 'Never') . ")\n";
        }
        
        return [
            '{{pending_measures_count}}' => count($measures),
            '{{pending_measures_list}}' => $measure_list
        ];
    }
    
    /**
     * Get initiative update data for user
     * 
     * @param array $user
     * @return array
     */
    private function getInitiativeUpdateData($user) {
        // Get initiatives that need status updates
        $sql = "SELECT i.id, i.name, i.dueDate, 
                       COALESCE(MAX(is.updatedOn), i.created_date) as last_update
                FROM initiative i
                LEFT JOIN initiative_status is ON i.id = is.initiativeId
                WHERE i.projectManager = '{$user['user_id']}'
                AND i.status != 'Completed'
                GROUP BY i.id, i.name, i.dueDate, i.created_date
                HAVING last_update < DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        
        $result = mysqli_query($this->db, $sql);
        $initiatives = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $initiatives[] = $row;
            }
        }
        
        $initiative_list = '';
        foreach ($initiatives as $initiative) {
            $initiative_list .= "- {$initiative['name']} (Due: {$initiative['dueDate']})\n";
        }
        
        return [
            '{{pending_initiatives_count}}' => count($initiatives),
            '{{pending_initiatives_list}}' => $initiative_list
        ];
    }
    
    /**
     * Get performance summary data for user
     * 
     * @param array $user
     * @return array
     */
    private function getPerformanceSummaryData($user) {
        // This would contain performance metrics for the user
        // TODO: Implement actual performance calculation using $user data
        unset($user); // Suppress unused parameter warning
        return [
            '{{performance_score}}' => '85%',
            '{{performance_trend}}' => 'Improving'
        ];
    }

    /**
     * Get system announcement data
     *
     * @param array $user
     * @return array
     */
    private function getSystemAnnouncementData($user) {
        // TODO: Implement user-specific announcements
        unset($user); // Suppress unused parameter warning
        return [
            '{{announcement_title}}' => 'System Update',
            '{{announcement_details}}' => 'Please check the latest system updates.'
        ];
    }
    
    /**
     * Send email using PHPMailer
     * 
     * @param array $user
     * @param array $template
     * @param array $schedule
     * @return bool
     */
    private function sendEmail($user, $template, $schedule) {
        try {
            // Suppress unused parameter warning
            unset($schedule);

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            // Server settings
            $mail->Host = 'localhost';
            $mail->Port = 25;

            $mail->setFrom('admin@accent-analytics.com', 'KDIC Analytics Platform');
            $mail->addAddress($user['email'], $user['display_name']);
            $mail->addBCC('admin@accent-analytics.com');

            $mail->isHTML(true);
            $mail->Subject = $template['subject'];
            $mail->Body = $this->formatEmailBody($template['body']);

            $mail->send();

            // Log to mail table
            $this->logToMailTable($template['subject'], $template['body'], $user['email'], $user['id']);

            return true;

        } catch (\PHPMailer\PHPMailer\Exception $e) {
            $this->logError("Failed to send email to {$user['email']}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Format email body with HTML structure
     * 
     * @param string $body
     * @return string
     */
    private function formatEmailBody($body) {
        return '<html><body>' . nl2br($body) . '</body></html>';
    }
    
    /**
     * Log sent notification
     * 
     * @param int $schedule_id
     * @param int $user_id
     * @param string $subject
     */
    private function logNotificationSent($schedule_id, $user_id, $subject) {
        $sql = "INSERT INTO notification_logs (schedule_id, user_id, subject, sent_date) 
                VALUES ($schedule_id, $user_id, '" . mysqli_real_escape_string($this->db, $subject) . "', NOW())";
        mysqli_query($this->db, $sql);
    }
    
    /**
     * Log to mail table for compatibility
     * 
     * @param string $subject
     * @param string $body
     * @param string $recipient
     * @param int $user_id
     */
    private function logToMailTable($subject, $body, $recipient, $user_id) {
        $subject = mysqli_real_escape_string($this->db, $subject);
        $body = mysqli_real_escape_string($this->db, $body);
        $recipient = mysqli_real_escape_string($this->db, $recipient);
        
        $sql = "INSERT INTO mail (title, message, sender, recipient, time, type, user_id) 
                VALUES ('$subject', '$body', 'admin@accent-analytics.com', '$recipient', NOW(), 'scheduled', '$user_id')";
        mysqli_query($this->db, $sql);
    }
    
    /**
     * Log messages
     * 
     * @param string $message
     */
    private function logMessage($message) {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents('../logs/notification_sender.log', "[$timestamp] $message\n", FILE_APPEND);
    }
    
    /**
     * Log error messages
     * 
     * @param string $message
     */
    private function logError($message) {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents('../logs/notification_errors.log', "[$timestamp] ERROR: $message\n", FILE_APPEND);
    }
}

// If called directly, process notifications
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    $sender = new NotificationSender($connect);
    $sender->processDueNotifications();
}

?>
