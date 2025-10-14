<?php
// Core setup
require_once '../config.php';
requireRole('student');
$pageTitle = 'My Subjects';

// Init variables
$studentId = null;
$subjects = [];

// --- Database logic ---

if (!empty($_SESSION['user_id']) && isset($conn)) {
    // 1. Get student ID
    $stmt = $conn->prepare("SELECT student_id FROM students WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $studentId = $row['student_id'] ?? null;
    $stmt->close();
}

if ($studentId) {
    // 2. Get subjects based on the student's class
    $stmt = $conn->prepare("
        SELECT 
            s.subject_name, 
            c.class_name, 
            t.full_name AS teacher_name 
        FROM subjects s 
        JOIN classes c ON s.class_id = c.class_id 
        LEFT JOIN teachers t ON s.teacher_id = t.teacher_id 
        JOIN students st ON st.class_id = c.class_id 
        WHERE st.student_id = ? 
        ORDER BY s.subject_name
    ");
    $stmt->bind_param("i", $studentId);
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
            <h2 class="card-title mb-4"><i class="fas fa-book me-2 text-primary"></i>My Subjects</h2>
            
            <?php if (empty($subjects)): ?>
                <div class="alert alert-info">You are not currently assigned to any classes or subjects.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr class="table-light">
                                <th>Subject</th>
                                <th>Class</th>
                                <th>Teacher</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjects as $sub): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($sub['subject_name']); ?></td>
                                    <td><?php echo htmlspecialchars($sub['class_name']); ?></td>
                                    <td><?php echo htmlspecialchars($sub['teacher_name'] ?? 'N/A'); ?></td>
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