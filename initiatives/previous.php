<?php
include('../config/config_msqli.php');

$initiativeId = $_POST['initiativeId'];

$nextQuery="SELECT initiative.id AS id, initiativeimpact.linkedobjectid from initiativeimpact, initiative 
WHERE initiative.id < '$initiativeId'
AND initiative.id = initiativeimpact.initiativeid
ORDER BY initiative.id DESC LIMIT 1";
$nextResult = mysqli_query($GLOBALS["___mysqli_ston"], $nextQuery);
$nextResult = mysqli_fetch_assoc($nextResult);
echo ($nextResult && isset($nextResult["id"])) ? $nextResult["id"] : '';
?>