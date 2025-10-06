<?php
require_once '../config.php';
requireRole('student');
$pageTitle = 'My Marks';
include '../includes/header.php';
include '../includes/sidebar.php';
$db = Database::getInstance()->getConnection();
$studentId = null;
$marksRecords = [];
if (!empty($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT student_id FROM students WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $studentId = $row['student_id'] ?? null;
}
if ($studentId) {
    $stmt = $db->prepare('SELECT m.exam_type, m.marks_obtained, m.total_marks, s.subject_name FROM marks m JOIN subjects s ON m.subject_id = s.subject_id WHERE m.student_id = ? ORDER BY m.exam_type, s.subject_name');
    $stmt->execute([$studentId]);
    $marksRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="main-content">
    <div class="content">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="card-title mb-4"><i class="fas fa-chart-bar me-2"></i>My Marks</h2>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Exam Type</th>
                                <th>Marks Obtained</th>
                                <th>Total Marks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($marksRecords as $rec): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($rec['subject_name']); ?></td>
                                    <td><?php echo htmlspecialchars($rec['exam_type']); ?></td>
                                    <td><?php echo htmlspecialchars($rec['marks_obtained']); ?></td>
                                    <td><?php echo htmlspecialchars($rec['total_marks']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
