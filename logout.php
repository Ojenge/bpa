<?php
/**
 * Root Level Logout Redirect
 * 
 * This file provides a logout endpoint at the root level that redirects
 * to the actual logout functionality in the admin directory.
 * 
 * This is needed because some JavaScript files reference logout.php
 * at the root level for compatibility.
 * 
 * @author Collins Ojenge
 * @version 1.0
 * @date 2025-06-17
 */

// Redirect to the actual logout file in the admin directory
header("Location: admin/logout.php");
exit();
?>
