<?php
// Core setup
require_once '../config.php';
requireRole('student');
$pageTitle = 'My Attendance';

// Init variables
$studentId = null;
$attendanceRecords = [];

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
    // Get attendance records
    $stmt = $conn->prepare("
        SELECT a.date, a.status, s.subject_name 
        FROM attendance a 
        JOIN subjects s ON a.subject_id = s.subject_id 
        WHERE a.student_id = ? 
        ORDER BY a.date DESC
    ");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $attendanceRecords = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Include layout
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="dashboard-card mb-4">
            <h2 class="card-title mb-4"><i class="fas fa-calendar-check me-2 text-primary"></i>My Attendance History
            </h2>

            <?php if (empty($attendanceRecords)): ?>
                <div class="alert alert-info">No attendance records found yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr class="table-light">
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceRecords as $rec): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime(htmlspecialchars($rec['date']))); ?></td>
                                    <td><?php echo htmlspecialchars($rec['subject_name']); ?></td>
                                    <td>
                                        <?php if ($rec['status'] == 'present'): ?>
                                            <span class="badge bg-success">Present</span>
                                        <?php elseif ($rec['status'] == 'absent'): ?>
                                            <span class="badge bg-danger">Absent</span>
                                        <?php elseif ($rec['status'] == 'late'): ?>
                                            <span class="badge bg-warning text-dark">Late</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">-</span>
                                        <?php endif; ?>
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