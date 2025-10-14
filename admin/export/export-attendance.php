<?php
// --- Export Attendance Records to CSV ---
// This file allows admin to download attendance details as a CSV file.

require_once '../../config.php';
requireRole('admin');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Attendance ID', 'Student ID', 'Subject ID', 'Date', 'Status']);

$query = "SELECT attendance_id, student_id, subject_id, date, status FROM attendance ORDER BY date DESC";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row);
    }
} else {
    fputcsv($output, ['No attendance records found']);
}

fclose($output);
exit;
?>