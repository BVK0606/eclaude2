<?php
require_once '../config.php';
requireRole('student');
$pageTitle = 'My Profile';
include '../includes/header.php';
include '../includes/sidebar.php';
$db = Database::getInstance()->getConnection();
$student = [];
if (!empty($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT s.*, u.email, u.uname, c.class_name FROM students s JOIN users u ON s.user_id = u.id LEFT JOIN classes c ON s.class_id = c.class_id WHERE u.id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<div class="main-content">
    <div class="content">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="card-title mb-4"><i class="fas fa-user me-2"></i>My Profile</h2>
                <?php if ($student): ?>
                        <table class="table table-bordered">
                            <tr><th>Name</th><td><?php echo htmlspecialchars($student['full_name'] ?? ($student['uname'] ?? '')); ?></td></tr>
                            <tr><th>Email</th><td><?php echo htmlspecialchars($student['email'] ?? ''); ?></td></tr>
                            <tr><th>Class</th><td><?php echo htmlspecialchars($student['class_name'] ?? ''); ?></td></tr>
                            <tr><th>Roll No</th><td><?php echo htmlspecialchars($student['roll_no'] ?? ''); ?></td></tr>
                            <tr><th>Date of Birth</th><td><?php echo htmlspecialchars($student['dob'] ?? ''); ?></td></tr>
                            <tr><th>Address</th><td><?php echo htmlspecialchars($student['address'] ?? ''); ?></td></tr>
                        </table>
                <?php else: ?>
                    <div class="alert alert-info">Profile not found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
