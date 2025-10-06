<?php
require_once '../../config.php';
requireRole('admin');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="marks.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Mark ID', 'Student ID', 'Subject ID', 'Exam Type', 'Marks Obtained']);

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT mark_id, student_id, subject_id, exam_type, marks_obtained FROM marks ORDER BY mark_id DESC");
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
