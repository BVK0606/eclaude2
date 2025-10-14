<?php
// --- Export Students to CSV ---
// This file allows admin to download all student data as a CSV file.

require_once '../../config.php';
requireRole('admin');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="students.csv"');

// Open file output stream
$output = fopen('php://output', 'w');

// Write column headers
fputcsv($output, ['Student ID', 'Roll No', 'Full Name', 'DOB', 'Address', 'Class ID']);

// Fetch student data
$query = "SELECT student_id, roll_no, full_name, dob, address, class_id FROM students ORDER BY roll_no";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row);
    }
} else {
    fputcsv($output, ['No student data found']);
}

// Close file
fclose($output);
exit;
?>