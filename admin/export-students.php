<?php
require_once '../config.php';
requireRole('admin');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="students.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Student ID', 'Roll No', 'Full Name', 'DOB', 'Address', 'Class ID']);

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT student_id, roll_no, full_name, dob, address, class_id FROM students ORDER BY roll_no");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
    if (empty($rows)) {
        // Output a row indicating no data
        fputcsv($output, ['No data found']);
    }
} catch (PDOException $e) {
    fputcsv($output, ['Error: ' . $e->getMessage()]);
}
fclose($output);
exit;
