<?php
require_once '../config.php';
requireRole('admin');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="teachers.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Teacher ID', 'Full Name', 'Qualification', 'Experience']);

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT teacher_id, full_name, qualification, experience FROM teachers ORDER BY full_name");
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
