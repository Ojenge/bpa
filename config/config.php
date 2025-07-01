<?php
//error_reporting(E_ALL ^ E_DEPRECATED);
//error_reporting(E_ALL ^ E_WARNING); 

// Turn off all error reporting
//error_reporting(0);

//update funcs.php line 433 as well for user permissions update...
$username="accenta0_NGIwY";
$password="bvM1369";
$database="kdic_local";
$server="localhost";

@mysql_connect($server,$username,$password);
mysql_select_db($database) or die( "Unable to select database");
?>
