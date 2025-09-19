<?php
require_once '../config.php';
requireRole('teacher');

$pageTitle = 'Teacher Dashboard';

// Get teacher information
try {
    $db = Database::getInstance()->getConnection();
    
    // Get teacher details
    $stmt = $db->prepare("
        SELECT t.*, u.email 
        FROM teachers t 
        JOIN users u ON t.user_id = u.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $teacher = $stmt->fetch();
    
    // Get assigned classes count
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT class_id) as class_count 
        FROM subjects 
        WHERE teacher_id = ?
    ");
    $stmt->execute([$teacher['teacher_id']]);
    $classCount = $stmt->fetch()['class_count'];
    
    // Get assigned subjects count
    $stmt = $db->prepare("
        SELECT COUNT(*) as subject_count 
        FROM subjects 
        WHERE teacher_id = ?
    ");
    $stmt->execute([$teacher['teacher_id']]);
    $subjectCount = $stmt->fetch()['subject_count'];
    
    // Get total students in assigned classes
    $stmt = $db->prepare("
        SELECT COUNT(*) as student_count 
        FROM students s
        JOIN subjects sub ON s.class_id = sub.class_id
        WHERE sub.teacher_id = ?
    ");
    $stmt->execute([$teacher['teacher_id']]);
    $studentCount = $stmt->fetch()['student_count'];
    
    // Get recent notices
    $stmt = $db->query("SELECT * FROM notices ORDER BY created_at DESC LIMIT 5");
    $recentNotices = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Teacher dashboard error: " . $e->getMessage());
    $teacher = [];
    $classCount = $subjectCount = $studentCount = 0;
    $recentNotices = [];
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
                            <h2 class="mb-2">Welcome back, <?php echo htmlspecialchars($teacher['full_name'] ?? 'Teacher'); ?>!</h2>
                            <p class="text-muted mb-0">Here's your teaching dashboard for today.</p>
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
                    <div class="card-value"><?php echo number_format($classCount); ?></div>
                    <div class="card-title">Assigned Classes</div>
                    <div class="small text-muted">Classes you teach</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="dashboard-card text-center">
                    <div class="card-value"><?php echo number_format($subjectCount); ?></div>
                    <div class="card-title">Subjects</div>
                    <div class="small text-muted">Subjects you teach</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="dashboard-card text-center">
                    <div class="card-value"><?php echo number_format($studentCount); ?></div>
                    <div class="card-title">Students</div>
                    <div class="small text-muted">Total students</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="dashboard-card text-center">
                    <div class="card-value">0</div>
                    <div class="card-title">Pending Tasks</div>
                    <div class="small text-muted">Tasks to complete</div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h5 class="card-title mb-3">Quick Actions</h5>
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="attendance.php" class="btn btn-outline-primary w-100 p-3">
                                <i class="fas fa-calendar-check fa-2x mb-2 d-block"></i>
                                Mark Attendance
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="marks.php" class="btn btn-outline-success w-100 p-3">
                                <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                                Enter Marks
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="my-classes.php" class="btn btn-outline-warning w-100 p-3">
                                <i class="fas fa-school fa-2x mb-2 d-block"></i>
                                My Classes
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="notices.php" class="btn btn-outline-info w-100 p-3">
                                <i class="fas fa-bullhorn fa-2x mb-2 d-block"></i>
                                View Notices
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Notices -->
        <div class="row">
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
            
            <!-- Upcoming Events -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h5 class="card-title mb-3">Upcoming Events</h5>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-calendar fa-3x mb-3 d-block"></i>
                        <p>No upcoming events.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>