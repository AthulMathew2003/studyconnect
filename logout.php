<?php
session_start();

// Clear all session variables
$_SESSION = array();
session_unset();

// Destroy the session
session_destroy();

// Ensure there's no output before redirect
if (!headers_sent()) {
    header("Location: login.php");
    exit();
} else {
    echo '<script>window.location.href="login.php";</script>';
    exit();
}
?>