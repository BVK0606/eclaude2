<?php
require_once '../config.php';
requireRole('teacher');

$pageTitle = 'Class Details';

// Initialize
$class = null;
$subjects = [];
$students = [];
$classId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

try {
    $db = Database::getInstance()->getConnection();

    // Get teacher ID from session user
    $stmt = $db->prepare("SELECT teacher_id FROM teachers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id'] ?? 0]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($teacher && $classId) {
        // Check if class is assigned to this teacher
        $stmt = $db->prepare("
            SELECT c.class_id, c.class_name 
            FROM teacher_classes tc
            JOIN classes c ON tc.class_id = c.class_id
            WHERE tc.class_id = ? AND tc.teacher_id = ?
        ");
        $stmt->execute([$classId, $teacher['teacher_id']]);
        $class = $stmt->fetch(PDO::FETCH_ASSOC);

        // If valid, get subjects + students
        if ($class) {
            // Subjects taught by this teacher in this class
            $stmt = $db->prepare("
                SELECT subject_id, subject_name 
                FROM subjects 
                WHERE class_id = ? AND teacher_id = ?
                ORDER BY subject_name
            ");
            $stmt->execute([$classId, $teacher['teacher_id']]);
            $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Students in this class
            $stmt = $db->prepare("
                SELECT student_id, roll_no, full_name 
                FROM students 
                WHERE class_id = ? 
                ORDER BY roll_no
            ");
            $stmt->execute([$classId]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) {
    error_log("Class Details Error: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">

        <?php if (!$class): ?>
            <div class="dashboard-card">
                <div class="alert alert-danger mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error: Class not found or not assigned to you.
                </div>
            </div>
        <?php else: ?>

            <div class="row g-4">
                <!-- Class Info -->
                <div class="col-12 col-lg-6">
                    <div class="dashboard-card h-100">
                        <h4 class="card-title mb-4">
                            <i class="fas fa-school me-2 text-primary"></i>Class Information
                        </h4>

                        <p class="mb-2"><strong>Class Name:</strong>
                            <?php echo htmlspecialchars($class['class_name']); ?>
                            <span class="badge bg-secondary ms-2">ID:
                                <?php echo htmlspecialchars($class['class_id']); ?></span>
                        </p>

                        <p class="mb-4"><strong>Total Students:</strong>
                            <span class="badge bg-info text-dark rounded-pill"><?php echo count($students); ?></span>
                        </p>

                        <div>
                            <strong>Subjects You Teach:</strong>
                            <?php if (empty($subjects)): ?>
                                <p class="text-muted mt-2">No subjects assigned in this class.</p>
                            <?php else: ?>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <?php foreach ($subjects as $subject): ?>
                                        <span class="badge bg-primary fs-6 px-3 py-2">
                                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Student List -->
                <div class="col-12 col-lg-6">
                    <div class="dashboard-card h-100">
                        <h4 class="card-title mb-4">
                            <i class="fas fa-users me-2 text-primary"></i>Students in This Class
                        </h4>

                        <?php if (empty($students)): ?>
                            <p class="text-muted">No students enrolled in this class yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Roll No</th>
                                            <th>Full Name</th>
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
        <?php endif; ?>

    </div>
</div>

<?php include '../includes/footer.php'; ?>