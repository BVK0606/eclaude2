<?php
require_once '../config.php';
requireRole('admin');

$classId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($classId <= 0) {
    die('Invalid class ID.');
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT class_name FROM classes WHERE class_id = ?");
    $stmt->execute([$classId]);
    $class = $stmt->fetch();
    if (!$class) {
        die('Class not found.');
    }
    $stmt = $db->prepare("SELECT roll_no, full_name FROM students WHERE class_id = ? ORDER BY roll_no");
    $stmt->execute([$classId]);
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Database error.');
}
?>
<?php include '../includes/header.php'; include '../includes/sidebar.php'; ?>
<div class="main-content">
    <div class="content">
        <div class="dashboard-card">
            <h2>Class Report: <?php echo htmlspecialchars($class['class_name']); ?></h2>
            <table class="table mt-4">
                <thead><tr><th>Roll No</th><th>Student Name</th></tr></thead>
                <tbody>
                <?php foreach ($students as $student): ?>
                    <tr><td><?php echo htmlspecialchars($student['roll_no']); ?></td><td><?php echo htmlspecialchars($student['full_name']); ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($students)): ?>
                    <tr><td colspan="2" class="text-center text-muted">No students in this class.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
