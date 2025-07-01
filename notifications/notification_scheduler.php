<?php
/**
 * Email Notification Scheduler Module
 * 
 * This module handles scheduling and management of email notifications
 * for the KDIC Analytics Platform
 * 
 * @author Collins Ojenge
 * @version 1.0
 * @date 2025-06-14
 */

require_once('../config/config_mysqli.php');
require_once('../functions/cryptString.php');

class NotificationScheduler {
    
    private $db;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    /**
     * Create a new notification schedule
     * 
     * @param array $schedule_data
     * @return bool|int Returns schedule ID on success, false on failure
     */
    public function createSchedule($schedule_data) {
        $name = mysqli_real_escape_string($this->db, $schedule_data['name']);
        $description = mysqli_real_escape_string($this->db, $schedule_data['description']);
        $template_id = (int)$schedule_data['template_id'];
        $frequency = mysqli_real_escape_string($this->db, $schedule_data['frequency']);
        $frequency_value = (int)$schedule_data['frequency_value'];
        $start_date = mysqli_real_escape_string($this->db, $schedule_data['start_date']);
        $end_date = isset($schedule_data['end_date']) ? mysqli_real_escape_string($this->db, $schedule_data['end_date']) : null;
        $is_active = isset($schedule_data['is_active']) ? (int)$schedule_data['is_active'] : 1;
        $target_users = mysqli_real_escape_string($this->db, json_encode($schedule_data['target_users']));
        $created_by = (int)$schedule_data['created_by'];
        
        $sql = "INSERT INTO notification_schedules 
                (name, description, template_id, frequency, frequency_value, start_date, end_date, 
                 is_active, target_users, created_by, created_date, modified_date) 
                VALUES 
                ('$name', '$description', $template_id, '$frequency', $frequency_value, '$start_date', 
                 " . ($end_date ? "'$end_date'" : "NULL") . ", $is_active, '$target_users', $created_by, 
                 NOW(), NOW())";
        
        if (mysqli_query($this->db, $sql)) {
            return mysqli_insert_id($this->db);
        }
        
        $this->logError("Failed to create schedule: " . mysqli_error($this->db));
        return false;
    }
    
    /**
     * Get all active schedules that are due for execution
     * 
     * @return array
     */
    public function getDueSchedules() {
        $sql = "SELECT ns.*, nt.subject, nt.body_template, nt.email_type 
                FROM notification_schedules ns 
                JOIN notification_templates nt ON ns.template_id = nt.id 
                WHERE ns.is_active = 1 
                AND ns.start_date <= NOW() 
                AND (ns.end_date IS NULL OR ns.end_date >= NOW())
                AND (ns.last_executed IS NULL OR 
                     (ns.frequency = 'daily' AND ns.last_executed < DATE_SUB(NOW(), INTERVAL ns.frequency_value DAY)) OR
                     (ns.frequency = 'weekly' AND ns.last_executed < DATE_SUB(NOW(), INTERVAL ns.frequency_value WEEK)) OR
                     (ns.frequency = 'monthly' AND ns.last_executed < DATE_SUB(NOW(), INTERVAL ns.frequency_value MONTH)) OR
                     (ns.frequency = 'hourly' AND ns.last_executed < DATE_SUB(NOW(), INTERVAL ns.frequency_value HOUR)))";
        
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
     * Update the last executed timestamp for a schedule
     *
     * @param int $schedule_id
     * @return bool
     */
    public function updateLastExecuted($schedule_id) {
        $schedule_id = (int)$schedule_id;
        $sql = "UPDATE notification_schedules SET last_executed = NOW() WHERE id = $schedule_id";
        return mysqli_query($this->db, $sql);
    }

    /**
     * Get a schedule by ID
     *
     * @param int $schedule_id
     * @return array|null
     */
    public function getScheduleById($schedule_id) {
        $schedule_id = (int)$schedule_id;
        $sql = "SELECT ns.*, nt.name as template_name
                FROM notification_schedules ns
                LEFT JOIN notification_templates nt ON ns.template_id = nt.id
                WHERE ns.id = $schedule_id";

        $result = mysqli_query($this->db, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $schedule = mysqli_fetch_assoc($result);
            // Decode target_users JSON
            $schedule['target_users'] = json_decode($schedule['target_users'], true);
            return $schedule;
        }

        return null;
    }

    /**
     * Update an existing notification schedule
     *
     * @param array $schedule_data
     * @return bool
     */
    public function updateSchedule($schedule_data) {
        $id = (int)$schedule_data['id'];
        $name = mysqli_real_escape_string($this->db, $schedule_data['name']);
        $description = mysqli_real_escape_string($this->db, $schedule_data['description']);
        $template_id = (int)$schedule_data['template_id'];
        $frequency = mysqli_real_escape_string($this->db, $schedule_data['frequency']);
        $frequency_value = (int)$schedule_data['frequency_value'];
        $start_date = mysqli_real_escape_string($this->db, $schedule_data['start_date']);
        $end_date = isset($schedule_data['end_date']) ? mysqli_real_escape_string($this->db, $schedule_data['end_date']) : null;
        $is_active = isset($schedule_data['is_active']) ? (int)$schedule_data['is_active'] : 1;
        $target_users = mysqli_real_escape_string($this->db, json_encode($schedule_data['target_users']));

        $sql = "UPDATE notification_schedules SET
                name = '$name',
                description = '$description',
                template_id = $template_id,
                frequency = '$frequency',
                frequency_value = $frequency_value,
                start_date = '$start_date',
                end_date = " . ($end_date ? "'$end_date'" : "NULL") . ",
                is_active = $is_active,
                target_users = '$target_users',
                modified_date = NOW()
                WHERE id = $id";

        return mysqli_query($this->db, $sql);
    }

    /**
     * Delete a notification schedule
     *
     * @param int $schedule_id
     * @return bool
     */
    public function deleteSchedule($schedule_id) {
        $schedule_id = (int)$schedule_id;

        // First check if schedule exists
        $check_sql = "SELECT id FROM notification_schedules WHERE id = $schedule_id";
        $check_result = mysqli_query($this->db, $check_sql);

        if (!$check_result || mysqli_num_rows($check_result) === 0) {
            return false; // Schedule doesn't exist
        }

        // Delete the schedule (logs will be deleted automatically due to foreign key cascade)
        $sql = "DELETE FROM notification_schedules WHERE id = $schedule_id";
        return mysqli_query($this->db, $sql);
    }

    /**
     * Get target users for a schedule
     * 
     * @param array $schedule
     * @return array
     */
    public function getTargetUsers($schedule) {
        $target_users = json_decode($schedule['target_users'], true);
        $users = [];
        
        if (empty($target_users)) {
            return $users;
        }
        
        // Handle different target types
        if (isset($target_users['type'])) {
            switch ($target_users['type']) {
                case 'all_users':
                    $users = $this->getAllActiveUsers();
                    break;
                case 'specific_users':
                    $users = $this->getSpecificUsers($target_users['user_ids']);
                    break;
                case 'department':
                    $users = $this->getUsersByDepartment($target_users['department']);
                    break;
                case 'role':
                    $users = $this->getUsersByRole($target_users['role']);
                    break;
            }
        }
        
        return $users;
    }
    
    /**
     * Get all active users
     * 
     * @return array
     */
    private function getAllActiveUsers() {
        $sql = "SELECT id, user_id, display_name, email, title, department 
                FROM uc_users 
                WHERE active = 1 AND email IS NOT NULL AND email != ''";
        
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
     * Get specific users by IDs
     * 
     * @param array $user_ids
     * @return array
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
     * Get users by department
     * 
     * @param string $department
     * @return array
     */
    private function getUsersByDepartment($department) {
        $department = mysqli_real_escape_string($this->db, $department);
        
        $sql = "SELECT id, user_id, display_name, email, title, department 
                FROM uc_users 
                WHERE active = 1 AND department LIKE '%$department%' 
                AND email IS NOT NULL AND email != ''";
        
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
     * Get users by role/title
     * 
     * @param string $role
     * @return array
     */
    private function getUsersByRole($role) {
        $role = mysqli_real_escape_string($this->db, $role);
        
        $sql = "SELECT id, user_id, display_name, email, title, department 
                FROM uc_users 
                WHERE active = 1 AND title LIKE '%$role%' 
                AND email IS NOT NULL AND email != ''";
        
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
     * Log error messages
     * 
     * @param string $message
     */
    private function logError($message) {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents('../logs/notification_errors.log', "[$timestamp] $message\n", FILE_APPEND);
    }
}

?>
