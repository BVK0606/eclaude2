<?php
require_once '../config.php';
requireRole('student');
$pageTitle = 'My Attendance';
include '../includes/header.php';
include '../includes/sidebar.php';
$db = Database::getInstance()->getConnection();
$studentId = null;
$attendanceRecords = [];
if (!empty($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT student_id FROM students WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $studentId = $row['student_id'] ?? null;
}
if ($studentId) {
    $stmt = $db->prepare('SELECT a.date, a.status, s.subject_name FROM attendance a JOIN subjects s ON a.subject_id = s.subject_id WHERE a.student_id = ? ORDER BY a.date DESC');
    $stmt->execute([$studentId]);
    $attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="main-content">
    <div class="content">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="card-title mb-4"><i class="fas fa-calendar-check me-2"></i>My Attendance</h2>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceRecords as $rec): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($rec['date']); ?></td>
                                    <td><?php echo htmlspecialchars($rec['subject_name']); ?></td>
                                    <td>
                                        <?php if ($rec['status'] == 'present'): ?>
                                            <span class="badge bg-success">Present</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Absent</span>
                                        <?php endif; ?>
                                    </td>
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
