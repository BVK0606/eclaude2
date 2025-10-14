<?php
// --- Export Marks to CSV ---
// This file allows admin to download student marks as a CSV file.

require_once '../../config.php';
requireRole('admin');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="marks.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Mark ID', 'Student ID', 'Subject ID', 'Exam Type', 'Marks Obtained']);

$query = "SELECT mark_id, student_id, subject_id, exam_type, marks_obtained FROM marks ORDER BY mark_id DESC";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row);
    }
} else {
    fputcsv($output, ['No marks data found']);
}

fclose($output);
exit;
?>