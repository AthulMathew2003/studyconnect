<?php
session_start();
$_SESSION = array();
session_destroy();

// Debug
error_log("Session destroyed and redirecting to login.php");

header("Location: login.php");
exit();
?>
