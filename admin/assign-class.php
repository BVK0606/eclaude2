<?php
require_once '../config.php';
requireRole('admin');
$pageTitle = 'Assign Class to Teacher';

// Fetch teachers and classes
$db = Database::getInstance()->getConnection();
$teachers = $db->query("SELECT teacher_id, full_name FROM teachers ORDER BY full_name")->fetchAll();
$classes = $db->query("SELECT class_id, class_name FROM classes ORDER BY class_name")->fetchAll();

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacherId = (int)($_POST['teacher_id'] ?? 0);
    $classId = (int)($_POST['class_id'] ?? 0);
    if (!$teacherId || !$classId) {
        $error = 'Please select both teacher and class.';
    } else {
        try {
            $stmt = $db->prepare("INSERT IGNORE INTO teacher_classes (teacher_id, class_id) VALUES (?, ?)");
            $stmt->execute([$teacherId, $classId]);
            $success = 'Class assigned to teacher successfully!';
        } catch (PDOException $e) {
            $error = 'Assignment failed.';
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="mb-2">Assign Class to Teacher</h2>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Teacher</label>
                            <select name="teacher_id" class="form-select" required>
                                <option value="">Select Teacher</option>
                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?php echo $t['teacher_id']; ?>"><?php echo htmlspecialchars($t['full_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Class</label>
                            <select name="class_id" class="form-select" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?php echo $c['class_id']; ?>"><?php echo htmlspecialchars($c['class_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" type="submit">Assign</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
