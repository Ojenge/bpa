<?php
/**
 * Notification Template Manager
 * 
 * This class handles email notification templates
 * 
 * @author Collins Ojenge
 * @version 1.0
 * @date 2025-06-14
 */

class NotificationTemplateManager {
    
    private $db;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    /**
     * Create a new notification template
     * 
     * @param array $template_data
     * @return bool|int Returns template ID on success, false on failure
     */
    public function createTemplate($template_data) {
        $name = mysqli_real_escape_string($this->db, $template_data['name']);
        $description = mysqli_real_escape_string($this->db, $template_data['description']);
        $email_type = mysqli_real_escape_string($this->db, $template_data['email_type']);
        $subject = mysqli_real_escape_string($this->db, $template_data['subject']);
        $body_template = mysqli_real_escape_string($this->db, $template_data['body_template']);
        $is_active = isset($template_data['is_active']) ? (int)$template_data['is_active'] : 1;
        $created_by = (int)$template_data['created_by'];
        
        $sql = "INSERT INTO notification_templates 
                (name, description, email_type, subject, body_template, is_active, created_by, created_date, modified_date) 
                VALUES 
                ('$name', '$description', '$email_type', '$subject', '$body_template', $is_active, $created_by, NOW(), NOW())";
        
        if (mysqli_query($this->db, $sql)) {
            return mysqli_insert_id($this->db);
        }
        
        $this->logError("Failed to create template: " . mysqli_error($this->db));
        return false;
    }
    
    /**
     * Get a template by ID
     * 
     * @param int $template_id
     * @return array|false
     */
    public function getTemplate($template_id) {
        $template_id = (int)$template_id;
        $sql = "SELECT * FROM notification_templates WHERE id = $template_id AND is_active = 1";
        
        $result = mysqli_query($this->db, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        
        return false;
    }
    
    /**
     * Get all templates
     * 
     * @param bool $active_only
     * @return array
     */
    public function getAllTemplates($active_only = true) {
        $sql = "SELECT * FROM notification_templates";
        if ($active_only) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY name";
        
        $result = mysqli_query($this->db, $sql);
        $templates = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $templates[] = $row;
            }
        }
        
        return $templates;
    }
    
    /**
     * Get templates by type
     * 
     * @param string $email_type
     * @return array
     */
    public function getTemplatesByType($email_type) {
        $email_type = mysqli_real_escape_string($this->db, $email_type);
        $sql = "SELECT * FROM notification_templates WHERE email_type = '$email_type' AND is_active = 1 ORDER BY name";
        
        $result = mysqli_query($this->db, $sql);
        $templates = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $templates[] = $row;
            }
        }
        
        return $templates;
    }
    
    /**
     * Update a template
     * 
     * @param int $template_id
     * @param array $template_data
     * @return bool
     */
    public function updateTemplate($template_id, $template_data) {
        $template_id = (int)$template_id;
        $name = mysqli_real_escape_string($this->db, $template_data['name']);
        $description = mysqli_real_escape_string($this->db, $template_data['description']);
        $email_type = mysqli_real_escape_string($this->db, $template_data['email_type']);
        $subject = mysqli_real_escape_string($this->db, $template_data['subject']);
        $body_template = mysqli_real_escape_string($this->db, $template_data['body_template']);
        $is_active = isset($template_data['is_active']) ? (int)$template_data['is_active'] : 1;
        
        $sql = "UPDATE notification_templates SET 
                name = '$name',
                description = '$description',
                email_type = '$email_type',
                subject = '$subject',
                body_template = '$body_template',
                is_active = $is_active,
                modified_date = NOW()
                WHERE id = $template_id";
        
        return mysqli_query($this->db, $sql);
    }
    
    /**
     * Delete a template (soft delete)
     * 
     * @param int $template_id
     * @return bool
     */
    public function deleteTemplate($template_id) {
        $template_id = (int)$template_id;
        $sql = "UPDATE notification_templates SET is_active = 0, modified_date = NOW() WHERE id = $template_id";
        return mysqli_query($this->db, $sql);
    }
    
    /**
     * Get available email types
     * 
     * @return array
     */
    public function getEmailTypes() {
        return [
            'measure_reminder' => 'Measure Update Reminder',
            'initiative_update' => 'Initiative Status Update',
            'performance_summary' => 'Performance Summary',
            'system_announcement' => 'System Announcement',
            'deadline_alert' => 'Deadline Alert',
            'weekly_digest' => 'Weekly Digest',
            'monthly_report' => 'Monthly Report'
        ];
    }
    
    /**
     * Get available template placeholders
     * 
     * @return array
     */
    public function getTemplatePlaceholders() {
        return [
            'user' => [
                '{{user_name}}' => 'User display name',
                '{{user_email}}' => 'User email address',
                '{{user_title}}' => 'User job title',
                '{{user_department}}' => 'User department'
            ],
            'date' => [
                '{{current_date}}' => 'Current date (YYYY-MM-DD)',
                '{{current_datetime}}' => 'Current date and time'
            ],
            'system' => [
                '{{schedule_name}}' => 'Name of the notification schedule'
            ],
            'measure_reminder' => [
                '{{pending_measures_count}}' => 'Number of pending measures',
                '{{pending_measures_list}}' => 'List of pending measures'
            ],
            'initiative_update' => [
                '{{pending_initiatives_count}}' => 'Number of pending initiatives',
                '{{pending_initiatives_list}}' => 'List of pending initiatives'
            ],
            'performance_summary' => [
                '{{performance_score}}' => 'Overall performance score',
                '{{performance_trend}}' => 'Performance trend'
            ],
            'system_announcement' => [
                '{{announcement_title}}' => 'Announcement title',
                '{{announcement_details}}' => 'Announcement details'
            ]
        ];
    }
    
    /**
     * Create default templates
     * 
     * @param int $created_by
     * @return bool
     */
    public function createDefaultTemplates($created_by) {
        $default_templates = [
            [
                'name' => 'Measure Update Reminder',
                'description' => 'Reminds users to update their measures',
                'email_type' => 'measure_reminder',
                'subject' => 'Reminder: Please Update Your Measures',
                'body_template' => "Dear {{user_name}},\n\nThis is a friendly reminder that you have {{pending_measures_count}} measure(s) that need updating:\n\n{{pending_measures_list}}\n\nPlease log into the system to update your measures.\n\nBest regards,\nKDIC Analytics Platform",
                'is_active' => 1,
                'created_by' => $created_by
            ],
            [
                'name' => 'Initiative Status Update',
                'description' => 'Reminds users to update initiative status',
                'email_type' => 'initiative_update',
                'subject' => 'Initiative Status Update Required',
                'body_template' => "Dear {{user_name}},\n\nYou have {{pending_initiatives_count}} initiative(s) that require status updates:\n\n{{pending_initiatives_list}}\n\nPlease update the status of these initiatives in the system.\n\nBest regards,\nKDIC Analytics Platform",
                'is_active' => 1,
                'created_by' => $created_by
            ],
            [
                'name' => 'Weekly Performance Summary',
                'description' => 'Weekly performance summary for users',
                'email_type' => 'performance_summary',
                'subject' => 'Your Weekly Performance Summary',
                'body_template' => "Dear {{user_name}},\n\nHere's your performance summary for this week:\n\nOverall Score: {{performance_score}}\nTrend: {{performance_trend}}\n\nKeep up the great work!\n\nBest regards,\nKDIC Analytics Platform",
                'is_active' => 1,
                'created_by' => $created_by
            ],
            [
                'name' => 'System Announcement',
                'description' => 'General system announcements',
                'email_type' => 'system_announcement',
                'subject' => 'System Announcement: {{announcement_title}}',
                'body_template' => "Dear {{user_name}},\n\n{{announcement_details}}\n\nFor more information, please contact the system administrator.\n\nBest regards,\nKDIC Analytics Platform",
                'is_active' => 1,
                'created_by' => $created_by
            ]
        ];
        
        $success = true;
        foreach ($default_templates as $template) {
            if (!$this->createTemplate($template)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    /**
     * Preview template with sample data
     * 
     * @param array $template
     * @return array
     */
    public function previewTemplate($template) {
        $sample_data = [
            '{{user_name}}' => 'John Doe',
            '{{user_email}}' => 'john.doe@example.com',
            '{{user_title}}' => 'Manager',
            '{{user_department}}' => 'Finance',
            '{{current_date}}' => date('Y-m-d'),
            '{{current_datetime}}' => date('Y-m-d H:i:s'),
            '{{schedule_name}}' => 'Sample Schedule',
            '{{pending_measures_count}}' => '3',
            '{{pending_measures_list}}' => "- Revenue Growth (Last updated: 2025-06-01)\n- Customer Satisfaction (Last updated: Never)\n- Cost Reduction (Last updated: 2025-06-05)",
            '{{pending_initiatives_count}}' => '2',
            '{{pending_initiatives_list}}' => "- Digital Transformation (Due: 2025-07-01)\n- Process Improvement (Due: 2025-06-30)",
            '{{performance_score}}' => '85%',
            '{{performance_trend}}' => 'Improving',
            '{{announcement_title}}' => 'System Maintenance',
            '{{announcement_details}}' => 'The system will be under maintenance on Sunday from 2:00 AM to 4:00 AM.'
        ];
        
        $preview_subject = str_replace(array_keys($sample_data), array_values($sample_data), $template['subject']);
        $preview_body = str_replace(array_keys($sample_data), array_values($sample_data), $template['body_template']);
        
        return [
            'subject' => $preview_subject,
            'body' => $preview_body
        ];
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
