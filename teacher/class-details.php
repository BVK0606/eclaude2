<?php
require_once '../config.php';
requireRole('teacher');

$pageTitle = 'Class Details';

$db = Database::getInstance()->getConnection();
$class = null;
$subjects = [];
$students = [];

$classId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$teacherId = null;

if (!empty($_SESSION['user_id'])) {
    // Get teacher id
    $stmt = $db->prepare('SELECT teacher_id FROM teachers WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($teacher) {
        $teacherId = $teacher['teacher_id'];
    }
}

if ($classId && $teacherId) {
    // Get class info (only if assigned to this teacher)
    $stmt = $db->prepare('SELECT c.class_id, c.class_name FROM teacher_classes tc JOIN classes c ON tc.class_id = c.class_id WHERE tc.class_id = ? AND tc.teacher_id = ?');
    $stmt->execute([$classId, $teacherId]);
    $class = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($class) {
        // Get subjects for this class taught by this teacher
        $stmt = $db->prepare('SELECT subject_id, subject_name FROM subjects WHERE class_id = ? AND teacher_id = ?');
        $stmt->execute([$classId, $teacherId]);
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get students in this class
        $stmt = $db->prepare('SELECT student_id, roll_no, full_name FROM students WHERE class_id = ?');
        $stmt->execute([$classId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-3"><i class="fas fa-school me-2"></i>Class Details</h3>
                        <?php if (!$class): ?>
                            <div class="alert alert-danger">Class not found or not assigned to you.</div>
                        <?php else: ?>
                            <div class="mb-2">
                                <span class="fw-semibold">Class Name:</span> <?php echo htmlspecialchars($class['class_name']); ?>
                                <span class="badge bg-secondary ms-2">ID: <?php echo $class['class_id']; ?></span>
                            </div>
                            <div class="mb-3">
                                <span class="fw-semibold">Total Students:</span> <span class="badge bg-info text-dark"><?php echo count($students); ?></span>
                            </div>
                            <div>
                                <span class="fw-semibold">Subjects You Teach:</span>
                                <?php if (empty($subjects)): ?>
                                    <span class="text-muted ms-2">No subjects assigned to you in this class.</span>
                                <?php else: ?>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <?php foreach ($subjects as $subject): ?>
                                            <span class="badge bg-primary fs-6 px-3 py-2"><?php echo htmlspecialchars($subject['subject_name']); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-3"><i class="fas fa-users me-2"></i>Students in This Class</h3>
                        <?php if (!$class): ?>
                            <div class="text-muted">No class selected.</div>
                        <?php elseif (empty($students)): ?>
                            <div class="text-muted">No students enrolled in this class.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Roll No</th>
                                            <th>Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['roll_no']); ?></td>
                                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
