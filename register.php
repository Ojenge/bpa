<?php
/**
 * Root Level Registration Redirect
 * 
 * This file provides a registration endpoint at the root level that redirects
 * to the actual registration functionality in the admin directory.
 * 
 * This is needed because the main login page (index.php) references register.php
 * at the root level for compatibility.
 * 
 * @author Collins Ojenge
 * @version 1.0
 * @date 2025-06-17
 */

// Redirect to the actual registration file in the admin directory
header("Location: admin/register.php");
exit();
?>
