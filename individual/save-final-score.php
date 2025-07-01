<?php
include_once("../config/config_mysqli.php");

@$indId = $_POST['indId'];
@$date = $_POST['date'];
@$score = $_POST['score'];

date_default_timezone_set('Africa/Nairobi');
$date = date('Y-m-d');

mysqli_query($connect, "INSERT INTO individual_score (id, ind_id, score, date) VALUES ('', '$indId', '$score', '$date')");

?>