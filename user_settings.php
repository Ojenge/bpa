<?php
/**
 * Root Level User Settings Redirect
 * 
 * This file provides a user settings endpoint at the root level that redirects
 * to the actual user settings functionality in the admin directory.
 * 
 * This is needed because JavaScript in index.php references user_settings.php
 * at the root level for compatibility.
 * 
 * @author Collins Ojenge
 * @version 1.0
 * @date 2025-06-17
 */

// Redirect to the actual user settings file in the admin directory
header("Location: admin/user_settings.php");
exit();
?>
