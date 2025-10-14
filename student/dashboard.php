<?php
// Core setup
require_once '../config.php';
requireRole('student');

// Page title
$pageTitle = 'Student Dashboard';

// Init variables
$student = [];
$attendanceStats = ['total' => 0, 'present' => 0];
$averageMarks = 0;
$recentNotices = [];
$upcomingExams = [];
$studentId = null;

// --- Database logic ---
try {
    if (!empty($_SESSION['user_id']) && isset($conn)) {

        // 1. Get student details and ID
        $stmt = $conn->prepare("
            SELECT s.*, u.email, u.uname, c.class_name 
            FROM students s 
            JOIN users u ON s.user_id = u.id 
            LEFT JOIN classes c ON s.class_id = c.class_id
            WHERE u.id = ?
        ");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $studentId = $student['student_id'] ?? null;
        $stmt->close();

        if ($studentId) {
            // 2. Get attendance statistics
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present
                FROM attendance 
                WHERE student_id = ?
            ");
            $stmt->bind_param("i", $studentId);
            $stmt->execute();
            $attendanceStats = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // 3. Get average marks
            $stmt = $conn->prepare("
                SELECT AVG(marks_obtained) as average_marks 
                FROM marks 
                WHERE student_id = ?
            ");
            $stmt->bind_param("i", $studentId);
            $stmt->execute();
            $averageMarks = $stmt->get_result()->fetch_assoc()['average_marks'] ?? 0;
            $stmt->close();

            // 4. Get upcoming exams (based on distinct exam types)
            $stmt = $conn->prepare("
                SELECT DISTINCT exam_type 
                FROM marks 
                WHERE student_id = ? 
                ORDER BY exam_date DESC, created_at DESC 
                LIMIT 3
            ");
            $stmt->bind_param("i", $studentId);
            $stmt->execute();
            $upcomingExams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }

        // 5. Get recent notices (No variables needed, so simple query is fine)
        $result = $conn->query("SELECT title, description, created_at FROM notices WHERE target_role IN ('all', 'student') AND is_active = 1 ORDER BY created_at DESC LIMIT 5");
        $recentNotices = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    }
} catch (mysqli_sql_exception $e) {
    error_log("Student dashboard error: " . $e->getMessage());
    // Fallback in case of database error
    $student = [];
    $attendanceStats = ['total' => 0, 'present' => 0];
    $averageMarks = 0;
    $recentNotices = [];
    $upcomingExams = [];
}

$attendanceRate = ($attendanceStats['total'] > 0) ? ($attendanceStats['present'] / $attendanceStats['total']) * 100 : 0;

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="mb-2">Welcome,
                                <?php echo htmlspecialchars($student['full_name'] ?? 'Student'); ?>!</h2>
                            <p class="text-muted mb-0">Here's your student dashboard for today.</p>
                        </div>
                        <div class="d-none d-md-block text-end">
                            <div class="fs-4 fw-bold text-primary"><?php echo date('d'); ?></div>
                            <div class="text-muted small"><?php echo date('M Y'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4 g-4">

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="dashboard-card text-center">
                    <i class="fas fa-percent fa-2x text-primary mb-2"></i>
                    <div class="card-value"><?php echo number_format($attendanceRate, 1); ?>%</div>
                    <div class="card-title">Attendance Rate</div>
                    <div class="small text-muted">Overall attendance score</div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="dashboard-card text-center">
                    <i class="fas fa-award fa-2x text-success mb-2"></i>
                    <div class="card-value"><?php echo number_format($averageMarks, 1); ?></div>
                    <div class="card-title">Average Marks</div>
                    <div class="small text-muted">Overall performance</div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="dashboard-card text-center">
                    <i class="fas fa-file-alt fa-2x text-warning mb-2"></i>
                    <div class="card-value"><?php echo count($upcomingExams); ?></div>
                    <div class="card-title">Upcoming Exams</div>
                    <div class="small text-muted">Distinct exam types recorded</div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="dashboard-card text-center">
                    <i class="fas fa-school fa-2x text-info mb-2"></i>
                    <div class="card-value"><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></div>
                    <div class="card-title">My Class</div>
                    <div class="small text-muted">Current assigned class</div>
                </div>
            </div>
        </div>

        <div class="row mb-4 g-4">
            <div class="col-md-6">
                <div class="dashboard-card h-100">
                    <h5 class="card-title mb-3"><i class="fas fa-bolt me-2"></i>Quick Access</h5>
                    <div class="row g-3">
                        <div class="col-lg-6 col-md-12">
                            <a href="attendance.php" class="btn btn-primary w-100 p-3">
                                <i class="fas fa-calendar-check fa-2x mb-2 d-block"></i>My Attendance
                            </a>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <a href="marks.php" class="btn btn-success w-100 p-3">
                                <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>My Marks
                            </a>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <a href="subjects.php" class="btn btn-warning w-100 p-3">
                                <i class="fas fa-book fa-2x mb-2 d-block"></i>My Subjects
                            </a>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <a href="notices.php" class="btn btn-info w-100 p-3">
                                <i class="fas fa-bullhorn fa-2x mb-2 d-block"></i>Notices
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="dashboard-card h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title mb-0"><i class="fas fa-clipboard-list me-2"></i>Recent Notices</h5>
                        <a href="notices.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>

                    <?php if (empty($recentNotices)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-bullhorn fa-2x mb-2 d-block"></i>
                            <p>No recent notices found.</p>
                        </div>
                    <?php else: ?>
                        <div class="notices-list">
                            <?php foreach ($recentNotices as $index => $notice): ?>
                                <div class="notice-item">
                                    <h6 class="notice-title fw-bold text-primary">
                                        <?php echo htmlspecialchars($notice['title']); ?></h6>
                                    <p class="notice-description small text-muted mb-1">
                                        <?php echo nl2br(htmlspecialchars(substr($notice['description'], 0, 80) . (strlen($notice['description']) > 80 ? '...' : ''))); ?>
                                    </p>
                                    <div class="notice-meta text-muted small">
                                        <i class="fas fa-clock me-1"></i>
                                        Posted <?php echo date('M d, Y', strtotime($notice['created_at'])); ?>
                                    </div>
                                </div>
                                <?php if ($index < count($recentNotices) - 1): ?>
                                    <hr class="my-3">
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h5 class="card-title mb-3"><i class="fas fa-info-circle me-2"></i>My Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Full Name</label>
                                <p><?php echo htmlspecialchars($student['full_name'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Roll Number</label>
                                <p><?php echo htmlspecialchars($student['roll_no'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email</label>
                                <p><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Class</label>
                                <p><?php echo htmlspecialchars($student['class_name'] ?? 'Not assigned'); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Date of Birth</label>
                                <p><?php echo $student['dob'] ? date('M d, Y', strtotime($student['dob'])) : 'N/A'; ?>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Address</label>
                                <p><?php echo htmlspecialchars($student['address'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>