<?php
require_once '../config.php';
requireRole('teacher');

$pageTitle = 'Subject Marks';

$db = Database::getInstance()->getConnection();
$subjectId = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$teacherId = null;
$subject = null;
$class = null;
$students = [];

if (!empty($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT teacher_id FROM teachers WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($teacher) {
        $teacherId = $teacher['teacher_id'];
    }
}

if ($subjectId && $classId && $teacherId) {
    // Get subject info (only if assigned to this teacher)
    $stmt = $db->prepare('SELECT subject_id, subject_name FROM subjects WHERE subject_id = ? AND class_id = ? AND teacher_id = ?');
    $stmt->execute([$subjectId, $classId, $teacherId]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);
    // Get class info
    $stmt = $db->prepare('SELECT class_id, class_name FROM classes WHERE class_id = ?');
    $stmt->execute([$classId]);
    $class = $stmt->fetch(PDO::FETCH_ASSOC);
    // Get students in this class
    $stmt = $db->prepare('SELECT student_id, roll_no, full_name FROM students WHERE class_id = ?');
    $stmt->execute([$classId]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="dashboard-card mb-4">
            <h2 class="mb-2">Subject Marks</h2>
            <?php if (!$subject || !$class): ?>
                <div class="alert alert-danger">Subject or class not found or not assigned to you.</div>
            <?php else: ?>
                <div class="mb-3">
                    <span class="fw-semibold">Subject:</span> <?php echo htmlspecialchars($subject['subject_name']); ?>
                    <span class="text-muted ms-2">(ID: <?php echo $subject['subject_id']; ?>)</span><br>
                    <span class="fw-semibold">Class:</span> <?php echo htmlspecialchars($class['class_name']); ?>
                    <span class="text-muted ms-2">(ID: <?php echo $class['class_id']; ?>)</span>
                </div>
                <div class="mb-4">
                    <h5>Enter Marks (UI only, backend not implemented)</h5>
                    <?php if (empty($students)): ?>
                        <div class="text-muted">No students enrolled in this class.</div>
                    <?php else: ?>
                        <form method="POST">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Roll No</th>
                                        <th>Name</th>
                                        <th>Marks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['roll_no']); ?></td>
                                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                            <td><input type="number" name="marks[<?php echo $student['student_id']; ?>]" min="0" max="100" class="form-control form-control-sm" style="width:80px;"></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <button type="submit" class="btn btn-warning">Save Marks</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
