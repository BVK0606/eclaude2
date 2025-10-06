<?php
require_once '../config.php';
requireRole('admin');
$pageTitle = 'Assign Subject to Teacher/Class';

$db = Database::getInstance()->getConnection();

$subjects = $db->query("SELECT subject_id, subject_name FROM subjects ORDER BY subject_name")->fetchAll();
$teachers = $db->query("SELECT teacher_id, full_name FROM teachers ORDER BY full_name")->fetchAll();
$classes = $db->query("SELECT class_id, class_name FROM classes ORDER BY class_name")->fetchAll();


$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $teacherId = (int)($_POST['teacher_id'] ?? 0);
    $classId = (int)($_POST['class_id'] ?? 0);
    if (!$subjectId || !$teacherId || !$classId) {
        $error = 'Please select subject, teacher, and class.';
    } else {
        try {
            // Check if subject exists
            $stmt = $db->prepare("SELECT subject_id FROM subjects WHERE subject_id = ?");
            $stmt->execute([$subjectId]);
            if (!$stmt->fetch()) {
                $error = 'Invalid subject selected.';
            } else {
                $stmt = $db->prepare("UPDATE subjects SET teacher_id = ?, class_id = ? WHERE subject_id = ?");
                $stmt->execute([$teacherId, $classId, $subjectId]);
                $success = 'Subject assigned to teacher and class successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Assignment failed: ' . $e->getMessage();
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
                    <h2 class="mb-2">Assign Subject to Teacher/Class</h2>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    <form method="POST" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Subject</label>
                            <select name="subject_id" class="form-select" required>
                                <option value="">Select Subject</option>
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?php echo $s['subject_id']; ?>"><?php echo htmlspecialchars($s['subject_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Teacher</label>
                            <select name="teacher_id" class="form-select" required>
                                <option value="">Select Teacher</option>
                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?php echo $t['teacher_id']; ?>"><?php echo htmlspecialchars($t['full_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
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