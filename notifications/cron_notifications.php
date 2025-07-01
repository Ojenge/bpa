<?php
/**
 * Cron Job Script for Email Notifications
 * 
 * This script should be run via cron to process scheduled notifications
 * 
 * Example cron entry (run every 15 minutes):
 * 0,15,30,45 * * * * /usr/bin/php /path/to/analytics/notifications/cron_notifications.php
 * 
 * @author Collins Ojenge
 * @version 1.0
 * @date 2025-06-14
 **/

// Ensure this script is only run from command line or cron
if (isset($_SERVER['HTTP_HOST'])) {
    die("This script can only be run from the command line.\n");
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Africa/Nairobi');

// Include required files
require_once(__DIR__ . '/../config/config_mysqli.php');
require_once(__DIR__ . '/send_notifications.php');

class CronNotificationProcessor {
    
    private $db;
    private $sender;
    private $log_file;
    private $start_time;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
        $this->sender = new NotificationSender($database_connection);
        $this->log_file = __DIR__ . '/../logs/cron_notifications.log';
        $this->start_time = microtime(true);
        
        // Ensure logs directory exists
        $logs_dir = dirname($this->log_file);
        if (!is_dir($logs_dir)) {
            mkdir($logs_dir, 0755, true);
        }
    }
    
    /**
     * Main execution method
     */
    public function run() {
        $this->log("=== Cron notification processor started ===");
        
        try {
            // Check if another instance is already running
            if ($this->isAlreadyRunning()) {
                $this->log("Another instance is already running. Exiting.");
                return;
            }
            
            // Create lock file
            $this->createLockFile();
            
            // Process notifications
            $this->processNotifications();
            
            // Clean up old logs
            $this->cleanupOldLogs();
            
            // Calculate execution time
            $execution_time = round(microtime(true) - $this->start_time, 2);
            $this->log("=== Cron notification processor completed in {$execution_time} seconds ===");
            
        } catch (Exception $e) {
            $this->log("ERROR: " . $e->getMessage());
            $this->log("Stack trace: " . $e->getTraceAsString());
        } finally {
            // Always remove lock file
            $this->removeLockFile();
        }
    }
    
    /**
     * Process all due notifications
     */
    private function processNotifications() {
        $this->log("Processing due notifications...");
        
        // Get due schedules
        $due_schedules = $this->getDueSchedules();
        
        if (empty($due_schedules)) {
            $this->log("No due schedules found.");
            return;
        }
        
        $this->log("Found " . count($due_schedules) . " due schedule(s).");
        
        foreach ($due_schedules as $schedule) {
            $this->processSchedule($schedule);
        }
    }
    
    /**
     * Get schedules that are due for execution
     */
    private function getDueSchedules() {
        $sql = "SELECT ns.*, nt.subject, nt.body_template, nt.email_type 
                FROM notification_schedules ns 
                JOIN notification_templates nt ON ns.template_id = nt.id 
                WHERE ns.is_active = 1 
                AND ns.start_date <= NOW() 
                AND (ns.end_date IS NULL OR ns.end_date >= NOW())
                AND (ns.last_executed IS NULL OR 
                     (ns.frequency = 'hourly' AND ns.last_executed < DATE_SUB(NOW(), INTERVAL ns.frequency_value HOUR)) OR
                     (ns.frequency = 'daily' AND ns.last_executed < DATE_SUB(NOW(), INTERVAL ns.frequency_value DAY)) OR
                     (ns.frequency = 'weekly' AND ns.last_executed < DATE_SUB(NOW(), INTERVAL ns.frequency_value WEEK)) OR
                     (ns.frequency = 'monthly' AND ns.last_executed < DATE_SUB(NOW(), INTERVAL ns.frequency_value MONTH)))";
        
        $result = mysqli_query($this->db, $sql);
        $schedules = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $schedules[] = $row;
            }
        }
        
        return $schedules;
    }
    
    /**
     * Process a single schedule
     */
    private function processSchedule($schedule) {
        $this->log("Processing schedule: {$schedule['name']} (ID: {$schedule['id']})");
        
        try {
            // Get target users
            $users = $this->getTargetUsers($schedule);
            
            if (empty($users)) {
                $this->log("No target users found for schedule: {$schedule['name']}");
                return;
            }
            
            $this->log("Found " . count($users) . " target user(s) for schedule: {$schedule['name']}");
            
            $sent_count = 0;
            $failed_count = 0;
            
            foreach ($users as $user) {
                if ($this->sendNotificationToUser($user, $schedule)) {
                    $sent_count++;
                } else {
                    $failed_count++;
                }
            }
            
            // Update last executed timestamp
            $this->updateLastExecuted($schedule['id']);
            
            $this->log("Schedule '{$schedule['name']}' processed: {$sent_count} sent, {$failed_count} failed");
            
            // Log to database
            $this->logScheduleExecution($schedule['id'], $sent_count, $failed_count);
            
        } catch (Exception $e) {
            $this->log("ERROR processing schedule '{$schedule['name']}': " . $e->getMessage());
        }
    }
    
    /**
     * Get target users for a schedule
     */
    private function getTargetUsers($schedule) {
        $target_users = json_decode($schedule['target_users'], true);
        $users = [];
        
        if (empty($target_users) || !isset($target_users['type'])) {
            return $users;
        }
        
        switch ($target_users['type']) {
            case 'all_users':
                $users = $this->getAllActiveUsers();
                break;
            case 'specific_users':
                $users = $this->getSpecificUsers($target_users['user_ids'] ?? []);
                break;
            case 'department':
                $users = $this->getUsersByDepartment($target_users['department'] ?? '');
                break;
            case 'role':
                $users = $this->getUsersByRole($target_users['role'] ?? '');
                break;
        }
        
        return $users;
    }
    
    /**
     * Get all active users
     */
    private function getAllActiveUsers() {
        $sql = "SELECT id, user_id, display_name, email, title, department 
                FROM uc_users 
                WHERE active = 1 AND email IS NOT NULL AND email != ''";
        
        return $this->executeUserQuery($sql);
    }
    
    /**
     * Get specific users by IDs
     */
    private function getSpecificUsers($user_ids) {
        if (empty($user_ids)) {
            return [];
        }
        
        $user_ids = array_map('intval', $user_ids);
        $ids_string = implode(',', $user_ids);
        
        $sql = "SELECT id, user_id, display_name, email, title, department 
                FROM uc_users 
                WHERE active = 1 AND id IN ($ids_string) 
                AND email IS NOT NULL AND email != ''";
        
        return $this->executeUserQuery($sql);
    }
    
    /**
     * Get users by department
     */
    private function getUsersByDepartment($department) {
        $department = mysqli_real_escape_string($this->db, $department);
        
        $sql = "SELECT id, user_id, display_name, email, title, department 
                FROM uc_users 
                WHERE active = 1 AND department LIKE '%$department%' 
                AND email IS NOT NULL AND email != ''";
        
        return $this->executeUserQuery($sql);
    }
    
    /**
     * Get users by role/title
     */
    private function getUsersByRole($role) {
        $role = mysqli_real_escape_string($this->db, $role);
        
        $sql = "SELECT id, user_id, display_name, email, title, department 
                FROM uc_users 
                WHERE active = 1 AND title LIKE '%$role%' 
                AND email IS NOT NULL AND email != ''";
        
        return $this->executeUserQuery($sql);
    }
    
    /**
     * Execute user query and return results
     */
    private function executeUserQuery($sql) {
        $result = mysqli_query($this->db, $sql);
        $users = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $users[] = $row;
            }
        }
        
        return $users;
    }
    
    /**
     * Send notification to a single user
     */
    private function sendNotificationToUser($user, $schedule) {
        try {
            // Prepare template data
            $template_data = [
                'subject' => $schedule['subject'],
                'body_template' => $schedule['body_template'],
                'email_type' => $schedule['email_type']
            ];

            // Use the NotificationSender to send the email
            return $this->sender->sendNotificationToUser($user, $template_data, $schedule);

        } catch (Exception $e) {
            $this->log("Failed to send notification to {$user['email']}: " . $e->getMessage());
            return false;
        }
    }
    

    
    /**
     * Update last executed timestamp
     */
    private function updateLastExecuted($schedule_id) {
        $schedule_id = (int)$schedule_id;
        $sql = "UPDATE notification_schedules SET last_executed = NOW() WHERE id = $schedule_id";
        mysqli_query($this->db, $sql);
    }
    
    /**
     * Log schedule execution to database
     */
    private function logScheduleExecution($schedule_id, $sent_count, $failed_count) {
        $schedule_id = (int)$schedule_id;
        $sent_count = (int)$sent_count;
        $failed_count = (int)$failed_count;
        
        $sql = "INSERT INTO notification_execution_log 
                (schedule_id, executed_at, sent_count, failed_count) 
                VALUES ($schedule_id, NOW(), $sent_count, $failed_count)";
        
        mysqli_query($this->db, $sql);
    }
    
    /**
     * Check if another instance is running
     */
    private function isAlreadyRunning() {
        $lock_file = $this->getLockFilePath();
        
        if (!file_exists($lock_file)) {
            return false;
        }
        
        $pid = file_get_contents($lock_file);
        
        // Check if process is still running (Unix/Linux only)
        if (function_exists('posix_kill')) {
            return posix_kill($pid, 0);
        }
        
        // Fallback: check file age (consider stale if older than 1 hour)
        return (time() - filemtime($lock_file)) < 3600;
    }
    
    /**
     * Create lock file
     */
    private function createLockFile() {
        $lock_file = $this->getLockFilePath();
        file_put_contents($lock_file, getmypid());
    }
    
    /**
     * Remove lock file
     */
    private function removeLockFile() {
        $lock_file = $this->getLockFilePath();
        if (file_exists($lock_file)) {
            unlink($lock_file);
        }
    }
    
    /**
     * Get lock file path
     */
    private function getLockFilePath() {
        return __DIR__ . '/../logs/cron_notifications.lock';
    }
    
    /**
     * Clean up old log files
     */
    private function cleanupOldLogs() {
        $logs_dir = __DIR__ . '/../logs/';
        $max_age = 30 * 24 * 60 * 60; // 30 days
        
        if (is_dir($logs_dir)) {
            $files = glob($logs_dir . '*.log');
            foreach ($files as $file) {
                if (time() - filemtime($file) > $max_age) {
                    unlink($file);
                    $this->log("Cleaned up old log file: " . basename($file));
                }
            }
        }
    }
    
    /**
     * Log message with timestamp
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] $message\n";
        
        // Write to log file
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
        
        // Also output to console if running from command line
        if (php_sapi_name() === 'cli') {
            echo $log_entry;
        }
    }
}

// Run the cron processor
try {
    $processor = new CronNotificationProcessor($connect);
    $processor->run();
} catch (Exception $e) {
    error_log("Cron notification processor failed: " . $e->getMessage());
    exit(1);
}

exit(0);
?>
