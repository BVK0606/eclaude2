<?php
// Core setup
require_once '../config.php';
requireRole('student');
$pageTitle = 'My Marks';

// Init variables
$studentId = null;
$marksRecords = [];

// --- Database logic ---
if (!empty($_SESSION['user_id']) && isset($conn)) {
    // Get student ID
    $stmt = $conn->prepare("SELECT student_id FROM students WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $studentId = $row['student_id'] ?? null;
    $stmt->close();
}

if ($studentId) {
    // Get marks records
    $stmt = $conn->prepare("
        SELECT 
            m.exam_type, m.marks_obtained, m.total_marks, s.subject_name 
        FROM marks m 
        JOIN subjects s ON m.subject_id = s.subject_id 
        WHERE m.student_id = ? 
        ORDER BY m.exam_type DESC, s.subject_name
    ");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $marksRecords = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Include layout
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="dashboard-card mb-4">
            <h2 class="card-title mb-4"><i class="fas fa-chart-bar me-2 text-primary"></i>My Exam Scores</h2>
            
            <?php if (empty($marksRecords)): ?>
                <div class="alert alert-info">No marks records found yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr class="table-light">
                                <th>Subject</th>
                                <th>Exam Type</th>
                                <th>Score</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($marksRecords as $rec): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($rec['subject_name']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($rec['exam_type']); ?></span></td>
                                    <td><span class="badge bg-primary fs-6"><?php echo htmlspecialchars($rec['marks_obtained']); ?></span></td>
                                    <td><?php echo htmlspecialchars($rec['total_marks']); ?></td>
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