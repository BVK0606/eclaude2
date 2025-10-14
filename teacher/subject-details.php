<?php
// Core setup
require_once '../config.php';
requireRole('teacher');

// Page title
$pageTitle = 'Subject Details';

// Init variables
$subject = null;
$class = null;
$teacherId = null;
$subjectId = isset($_GET['subject_id']) ? (int) $_GET['subject_id'] : 0;

// --- Database logic ---

if (!empty($_SESSION['user_id']) && isset($conn)) {
    // Get teacher ID
    $stmt = $conn->prepare("SELECT teacher_id FROM teachers WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $teacherId = $row['teacher_id'];
    }
    $stmt->close();
}

if ($subjectId && $teacherId) {
    // Get subject info (only if assigned to this teacher)
    $stmt = $conn->prepare("
        SELECT s.subject_id, s.subject_name, c.class_id, c.class_name 
        FROM subjects s 
        JOIN classes c ON s.class_id = c.class_id 
        WHERE s.subject_id = ? AND s.teacher_id = ?
    ");
    $stmt->bind_param("ii", $subjectId, $teacherId);
    $stmt->execute();
    $result = $stmt->get_result();
    $subject = $result->fetch_assoc();
    $stmt->close();

    if ($subject) {
        $class = ['class_id' => $subject['class_id'], 'class_name' => $subject['class_name']];
    }
}

// Include layout
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="dashboard-card mb-4">
            <h2 class="mb-4"><i class="fas fa-book me-2 text-primary"></i>Subject Details</h2>

            <?php if (!$subject): ?>
                <div class="alert alert-danger mb-0">Subject not found or not assigned to you.</div>
            <?php else: ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <p class="mb-1">
                            <strong>Subject Name:</strong>
                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                        </p>
                        <span class="badge bg-secondary">ID: <?php echo $subject['subject_id']; ?></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="mb-1">
                            <strong>Class Assigned:</strong>
                            <?php echo htmlspecialchars($class['class_name']); ?>
                        </p>
                        <span class="badge bg-secondary">Class ID: <?php echo $class['class_id']; ?></span>
                    </div>
                </div>

                <div class="mt-4">
                    <h5 class="mb-3">Quick Actions:</h5>
                    <a href="attendance.php?class_id=<?php echo $class['class_id']; ?>&subject_id=<?php echo $subject['subject_id']; ?>"
                        class="btn btn-success me-2">
                        <i class="fas fa-calendar-check me-1"></i> Manage Attendance
                    </a>
                    <a href="marks.php?class_id=<?php echo $class['class_id']; ?>&subject_id=<?php echo $subject['subject_id']; ?>"
                        class="btn btn-warning">
                        <i class="fas fa-chart-bar me-1"></i> Manage Marks
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
// Include footer
include '../includes/footer.php';
?>