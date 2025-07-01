<?php
$username="accenta0_NGIwY";
$password="bvM1369";
$database="kdic_local";
$server="localhost";
$port = "3306";

$connect = ($GLOBALS["___mysqli_ston"] = mysqli_connect($server,  $username,  $password, $database, $port)) or die ('Cannot connect to the database because: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));

?>
