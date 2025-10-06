<?php
require_once '../config.php';
requireRole('teacher');

$pageTitle = 'Subject Details';

$db = Database::getInstance()->getConnection();
$subject = null;
$class = null;
$teacherId = null;

$subjectId = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

if (!empty($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT teacher_id FROM teachers WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($teacher) {
        $teacherId = $teacher['teacher_id'];
    }
}

if ($subjectId && $teacherId) {
    // Get subject info (only if assigned to this teacher)
    $stmt = $db->prepare('SELECT s.subject_id, s.subject_name, c.class_id, c.class_name FROM subjects s JOIN classes c ON s.class_id = c.class_id WHERE s.subject_id = ? AND s.teacher_id = ?');
    $stmt->execute([$subjectId, $teacherId]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($subject) {
        $class = ['class_id' => $subject['class_id'], 'class_name' => $subject['class_name']];
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="dashboard-card mb-4">
            <h2 class="mb-2">Subject Details</h2>
            <?php if (!$subject): ?>
                <div class="alert alert-danger">Subject not found or not assigned to you.</div>
            <?php else: ?>
                <div class="mb-3">
                    <span class="fw-semibold">Subject:</span> <?php echo htmlspecialchars($subject['subject_name']); ?>
                    <span class="text-muted ms-2">(ID: <?php echo $subject['subject_id']; ?>)</span><br>
                    <span class="fw-semibold">Class:</span> <?php echo htmlspecialchars($class['class_name']); ?>
                    <span class="text-muted ms-2">(ID: <?php echo $class['class_id']; ?>)</span>
                </div>
                <div class="mb-4">
                    <a href="attendance.php?class_id=<?php echo $class['class_id']; ?>&subject_id=<?php echo $subject['subject_id']; ?>" class="btn btn-success btn-sm me-2">Attendance</a>
                    <a href="marks.php?class_id=<?php echo $class['class_id']; ?>&subject_id=<?php echo $subject['subject_id']; ?>" class="btn btn-warning btn-sm">Marks</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
