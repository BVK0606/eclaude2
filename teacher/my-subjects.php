<?php
// Core setup
require_once '../config.php';
requireRole('teacher');

// Page title
$pageTitle = 'My Subjects';

// Init variables
$teacherId = null;
$subjects = [];

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

if ($teacherId) {
    // Get all subjects assigned to this teacher, with class info
    $stmt = $conn->prepare("
        SELECT s.subject_id, s.subject_name, c.class_id, c.class_name 
        FROM subjects s 
        JOIN classes c ON s.class_id = c.class_id 
        WHERE s.teacher_id = ? 
        ORDER BY c.class_name, s.subject_name
    ");
    $stmt->bind_param("i", $teacherId);
    $stmt->execute();
    $result = $stmt->get_result();
    $subjects = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Include layout
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="dashboard-card mb-4">
            <h2 class="mb-2"><i class="fas fa-book me-2 text-primary"></i>My Assigned Subjects</h2>
            <p class="text-muted mb-0">List of all subjects you are currently teaching.</p>
            
            <?php if (empty($subjects)): ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-book fa-3x mb-3 d-block text-primary"></i>
                    <h4>No Subjects Assigned</h4>
                    <p>You have not been assigned any subjects yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
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
                                    <td>
                                        <?php echo htmlspecialchars($subject['class_name']); ?> 
                                        <span class="text-muted small">(ID: <?php echo $subject['class_id']; ?>)</span>
                                    </td>
                                    <td>
                                        <a href="subject-details.php?subject_id=<?php echo $subject['subject_id']; ?>" class="btn btn-sm btn-info me-2 text-white">
                                            <i class="fas fa-eye"></i> Details
                                        </a>
                                        <a href="attendance.php?class_id=<?php echo $subject['class_id']; ?>&subject_id=<?php echo $subject['subject_id']; ?>" class="btn btn-sm btn-success me-2">
                                            <i class="fas fa-calendar-check"></i> Attendance
                                        </a>
                                        <a href="marks.php?class_id=<?php echo $subject['class_id']; ?>&subject_id=<?php echo $subject['subject_id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-chart-bar"></i> Marks
                                        </a>
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