<?php
require_once '../config.php';
requireRole('student');

$pageTitle = 'Student Dashboard';

// Get student information
try {
    $db = Database::getInstance()->getConnection();
    
    // Get student details
    $stmt = $db->prepare("
        SELECT s.*, u.email, u.uname, c.class_name 
        FROM students s 
        JOIN users u ON s.user_id = u.id 
        LEFT JOIN classes c ON s.class_id = c.class_id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $student = $stmt->fetch();
    
    // Get attendance statistics
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present
        FROM attendance 
        WHERE student_id = ?
    ");
    $stmt->execute([$student['student_id']]);
    $attendanceStats = $stmt->fetch();
    
    // Get average marks
    $stmt = $db->prepare("
        SELECT AVG(marks_obtained) as average_marks 
        FROM marks 
        WHERE student_id = ?
    ");
    $stmt->execute([$student['student_id']]);
    $averageMarks = $stmt->fetch()['average_marks'];
    
    // Get recent notices
    $stmt = $db->query("SELECT * FROM notices ORDER BY created_at DESC LIMIT 5");
    $recentNotices = $stmt->fetchAll();
    
    // Get upcoming exams
    $stmt = $db->prepare("
        SELECT DISTINCT exam_type 
        FROM marks 
        WHERE student_id = ? 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    $stmt->execute([$student['student_id']]);
    $upcomingExams = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Student dashboard error: " . $e->getMessage());
    $student = [];
    $attendanceStats = ['total' => 0, 'present' => 0];
    $averageMarks = 0;
    $recentNotices = [];
    $upcomingExams = [];
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="mb-2">Welcome, <?php echo htmlspecialchars($student['full_name'] ?? 'Student'); ?>!</h2>
                            <p class="text-muted mb-0">Here's your student dashboard for today.</p>
                        </div>
                        <div class="d-none d-md-block">
                            <div class="text-end">
                                <div class="fs-4 fw-bold text-primary"><?php echo date('d'); ?></div>
                                <div class="text-muted"><?php echo date('M Y'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="dashboard-card text-center">
                    <?php if ($attendanceStats['total'] > 0): ?>
                        <div class="card-value"><?php echo number_format(($attendanceStats['present'] / $attendanceStats['total']) * 100, 1); ?>%</div>
                    <?php else: ?>
                        <div class="card-value">0%</div>
                    <?php endif; ?>
                    <div class="card-title">Attendance</div>
                    <div class="small text-muted">Overall attendance rate</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="dashboard-card text-center">
                    <div class="card-value"><?php echo number_format($averageMarks, 1); ?>%</div>
                    <div class="card-title">Average Marks</div>
                    <div class="small text-muted">Overall performance</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="dashboard-card text-center">
                    <div class="card-value"><?php echo count($upcomingExams); ?></div>
                    <div class="card-title">Upcoming Exams</div>
                    <div class="small text-muted">Exams scheduled</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="dashboard-card text-center">
                    <div class="card-value">0</div>
                    <div class="card-title">Assignments</div>
                    <div class="small text-muted">Pending assignments</div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h5 class="card-title mb-3">Quick Access</h5>
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="attendance.php" class="btn btn-outline-primary w-100 p-3">
                                <i class="fas fa-calendar-check fa-2x mb-2 d-block"></i>
                                My Attendance
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="marks.php" class="btn btn-outline-success w-100 p-3">
                                <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                                My Marks
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="subjects.php" class="btn btn-outline-warning w-100 p-3">
                                <i class="fas fa-book fa-2x mb-2 d-block"></i>
                                My Subjects
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="notices.php" class="btn btn-outline-info w-100 p-3">
                                <i class="fas fa-bullhorn fa-2x mb-2 d-block"></i>
                                Notices
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Recent Notices -->
            <div class="col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title mb-0">Recent Notices</h5>
                        <a href="notices.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    
                    <?php if (empty($recentNotices)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-bullhorn fa-2x mb-2 d-block"></i>
                            <p>No notices yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="notices-list">
                            <?php foreach ($recentNotices as $notice): ?>
                                <div class="notice-item mb-3">
                                    <h6 class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></h6>
                                    <p class="notice-description small"><?php echo nl2br(htmlspecialchars(substr($notice['description'], 0, 100) . (strlen($notice['description']) > 100 ? '...' : ''))); ?></p>
                                    <div class="notice-meta text-muted small">
                                        <i class="fas fa-clock me-1"></i>
                                        Posted <?php echo date('M d, Y', strtotime($notice['created_at'])); ?>
                                    </div>
                                </div>
                                <?php if ($notice !== end($recentNotices)): ?>
                                    <hr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Upcoming Exams -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title mb-0">Upcoming Exams</h5>
                        <a href="marks.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    
                    <?php if (empty($upcomingExams)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-file-alt fa-2x mb-2 d-block"></i>
                            <p>No upcoming exams.</p>
                        </div>
                    <?php else: ?>
                        <div class="exams-list">
                            <?php foreach ($upcomingExams as $exam): ?>
                                <div class="exam-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-file-alt text-primary fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($exam['exam_type']); ?> Exam</h6>
                                            <p class="small text-muted mb-0">Date to be announced</p>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($exam !== end($upcomingExams)): ?>
                                    <hr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Student Information -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h5 class="card-title mb-3">My Information</h5>
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
                                <p><?php echo $student['dob'] ? date('M d, Y', strtotime($student['dob'])) : 'N/A'; ?></p>
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