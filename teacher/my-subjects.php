<?php
require_once '../config.php';
requireRole('teacher');

$pageTitle = 'My Subjects';

$db = Database::getInstance()->getConnection();
$teacherId = null;
$subjects = [];

if (!empty($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT teacher_id FROM teachers WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($teacher) {
        $teacherId = $teacher['teacher_id'];
    }
}

if ($teacherId) {
    // Get all subjects assigned to this teacher, with class info
    $stmt = $db->prepare('SELECT s.subject_id, s.subject_name, c.class_id, c.class_name FROM subjects s JOIN classes c ON s.class_id = c.class_id WHERE s.teacher_id = ? ORDER BY c.class_name, s.subject_name');
    $stmt->execute([$teacherId]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="dashboard-card mb-4">
            <h2 class="mb-2">My Subjects</h2>
            <?php if (empty($subjects)): ?>
                <div class="alert alert-info">No subjects assigned to you yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Class</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjects as $subject): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                    <td><?php echo htmlspecialchars($subject['class_name']); ?> (ID: <?php echo $subject['class_id']; ?>)</td>
                                    <td>
                                        <a href="attendance.php?class_id=<?php echo $subject['class_id']; ?>&subject_id=<?php echo $subject['subject_id']; ?>" class="btn btn-success btn-sm me-2">Attendance</a>
                                        <a href="marks.php?class_id=<?php echo $subject['class_id']; ?>&subject_id=<?php echo $subject['subject_id']; ?>" class="btn btn-warning btn-sm">Marks</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
