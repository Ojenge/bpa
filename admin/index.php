<?php
/**
 * Admin Directory Index
 * 
 * This file provides a default landing page for the admin directory.
 * It redirects to the main admin users management page.
 * 
 * @author Collins Ojenge
 * @version 1.0
 * @date 2025-06-17
 */

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

// Redirect to the main admin users page
header("Location: admin_users.php");
exit();
?>
