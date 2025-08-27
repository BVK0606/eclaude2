<?php
require_once '../config.php';

// Destroy session and redirect to login
session_start();
session_unset();
session_destroy();

// Clear any remember me cookies
setcookie('remember_token', '', time() - 3600, '/');

// Redirect to login with success message
header('Location: login.php?logout=1');
exit;
?>