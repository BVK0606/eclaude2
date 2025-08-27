<?php
require_once 'config.php';

// Redirect based on authentication status and role
if (isLoggedIn()) {
    $role = $_SESSION['role'];
    header("Location: {$role}/dashboard.php");
} else {
    header('Location: auth/login.php');
}
exit;
?>