// Sidebar logic - controls what menu items to show
<?php

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}

$userRole = $_SESSION['role'];
$username = $_SESSION['username'] ?? 'Guest';

$menuItems = [];

if ($userRole == 'admin') {
    $menuItems = [
        'Dashboard' => 'dashboard.php',
        'Manage Students' => 'manage_students.php',
        'Manage Teachers' => 'manage_teachers.php',
        'Classes & Subjects' => 'classes.php'
    ];
} elseif ($userRole == 'teacher') {
    $menuItems = [
        'Dashboard' => 'dashboard.php',
        'My Classes' => 'my_classes.php',
        'Attendance' => 'attendance.php'
    ];
} elseif ($userRole == 'student') {
    $menuItems = [
        'Dashboard' => 'dashboard.php',
        'My Subjects' => 'my_subjects.php',
        'Marks' => 'marks.php'
    ];
}
?>