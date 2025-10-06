<?php
require_once '../config.php';
requireRole('student');
$pageTitle = 'My Subjects';
include '../includes/header.php';
include '../includes/sidebar.php';
$db = Database::getInstance()->getConnection();
$studentId = null;
$subjects = [];
if (!empty($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT student_id FROM students WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $studentId = $row['student_id'] ?? null;
}
if ($studentId) {
    $stmt = $db->prepare('SELECT s.subject_name, c.class_name, t.full_name AS teacher_name FROM subjects s JOIN classes c ON s.class_id = c.class_id LEFT JOIN teachers t ON s.teacher_id = t.teacher_id JOIN students st ON st.class_id = c.class_id WHERE st.student_id = ?');
    $stmt->execute([$studentId]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="main-content">
    <div class="content">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="card-title mb-4"><i class="fas fa-book me-2"></i>My Subjects</h2>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Class</th>
                                <th>Teacher</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjects as $sub): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($sub['subject_name']); ?></td>
                                    <td><?php echo htmlspecialchars($sub['class_name']); ?></td>
                                    <td><?php echo htmlspecialchars($sub['teacher_name']); ?></td>
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
