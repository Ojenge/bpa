<?php
$username="accenta0_user";
$password="bvM1369";
$database="kdic_local";
$server="localhost";
$port = "3306";
$GLOBALS["___mysqli_ston"] = mysqli_connect($server,  $username,  $password, $database, $port) or file_put_contents ("connectError.txt",'Cannot connect to the database because: ' . mysqli_connect_error());


?>
