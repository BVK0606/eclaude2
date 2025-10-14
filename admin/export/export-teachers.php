<?php
// --- Export Teachers to CSV ---
// This file allows admin to download all teacher data as a CSV file.

require_once '../../config.php';
requireRole('admin');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="teachers.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Teacher ID', 'Full Name', 'Qualification', 'Experience']);

$query = "SELECT teacher_id, full_name, qualification, experience FROM teachers ORDER BY full_name";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row);
    }
} else {
    fputcsv($output, ['No teacher data found']);
}

fclose($output);
exit;
?>