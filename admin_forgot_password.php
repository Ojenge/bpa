<?php
/**
 * Root Level Forgot Password Redirect
 * 
 * This file provides a forgot password endpoint at the root level that redirects
 * to the actual forgot password functionality in the admin directory.
 * 
 * This is needed because the main login page (index.php) references admin_forgot_password.php
 * at the root level for compatibility.
 * 
 * @author Collins Ojenge
 * @version 1.0
 * @date 2025-06-17
 */

// Redirect to the actual forgot password file in the admin directory
header("Location: admin/admin_forgot_password.php");
exit();
?>
