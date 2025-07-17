
DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cleanup_notification_logs` (IN `days_to_keep` INT)   BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    START TRANSACTION;
    DELETE FROM notification_logs
    WHERE sent_date < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    DELETE FROM notification_execution_log
    WHERE executed_at < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    DELETE FROM notification_queue
    WHERE created_date < DATE_SUB(NOW(), INTERVAL days_to_keep DAY)
    AND status IN ('sent', 'failed');
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_notification_stats` (IN `date_from` DATE, IN `date_to` DATE)   BEGIN
    SELECT
        DATE(nl.sent_date) as send_date,
        nt.email_type,
        COUNT(*) as total_sent,
        COUNT(CASE WHEN nl.status = 'sent' THEN 1 END) as successful,
        COUNT(CASE WHEN nl.status = 'failed' THEN 1 END) as failed
    FROM notification_logs nl
    JOIN notification_schedules ns ON nl.schedule_id = ns.id
    JOIN notification_templates nt ON ns.template_id = nt.id
    WHERE DATE(nl.sent_date) BETWEEN date_from AND date_to
    GROUP BY DATE(nl.sent_date), nt.email_type
    ORDER BY send_date DESC, nt.email_type;
END$$

DELIMITER ;
