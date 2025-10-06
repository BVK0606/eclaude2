<?php
require_once '../../config.php';
requireRole('admin');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Attendance ID', 'Student ID', 'Subject ID', 'Date', 'Status']);

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT attendance_id, student_id, subject_id, date, status FROM attendance ORDER BY date DESC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
    if (empty($rows)) {
        fputcsv($output, ['No data found']);
    }
} catch (PDOException $e) {
    fputcsv($output, ['Error: ' . $e->getMessage()]);
}
fclose($output);
exit;
