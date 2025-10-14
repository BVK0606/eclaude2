<?php
require '../config.php';
requireRole('admin');

// --- Counts ---
$students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM students"))['c'] ?? 0;
$teachers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM teachers"))['c'] ?? 0;
$classes  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM classes"))['c'] ?? 0;

// --- Attendance & Marks ---
$a = mysqli_fetch_assoc(mysqli_query($conn, "
  SELECT COUNT(*) t, SUM(status='present') p FROM attendance"));
$attendanceRate = $a['t'] ? round(($a['p'] / $a['t']) * 100, 1) : 0;

$m = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(marks) avg FROM marks"));
$avgMarks = round($m['avg'] ?? 0, 1);

// --- Class-wise Students ---
$classStats = mysqli_query($conn, "
  SELECT c.class_name, COUNT(s.student_id) total
  FROM classes c LEFT JOIN students s ON c.class_id=s.class_id
  GROUP BY c.class_id");

// --- Recent Notices ---
$notices = mysqli_query($conn, "
  SELECT title, created_at FROM notices ORDER BY created_at DESC LIMIT 5");
?>
