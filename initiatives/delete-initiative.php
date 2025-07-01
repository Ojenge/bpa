<?php
include_once("../config/config_mysqli.php");

if(!empty($_POST['selectedInitiative'])) $selectedInitiative = $_POST['selectedInitiative'];

mysqli_query($connect, "DELETE FROM initiativeimpact WHERE initiativeid = '$selectedInitiative'");
mysqli_query($connect, "DELETE FROM initiative WHERE id = '$selectedInitiative'");
mysqli_query($connect, "DELETE FROM initiative_status WHERE initiativeId = '$selectedInitiative'");
mysqli_query($connect, "DELETE FROM initiative_issue WHERE initiativeId = '$selectedInitiative'");
mysqli_query($connect, "DELETE FROM initiativeteam WHERE initiative_id = '$selectedInitiative'");
?>