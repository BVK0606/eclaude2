<?php
// Core setup
require_once '../config.php';
requireRole('teacher');

// Page title
$pageTitle = 'Teacher Dashboard';

// Init variables
$teacher = [];
$classCount = $subjectCount = $studentCount = 0;
$recentNotices = [];
$teacherId = null;

// --- Database logic ---
try {
    if (!empty($_SESSION['user_id']) && isset($conn)) {
        
        // 1. Get teacher details and ID
        $stmt = $conn->prepare("
            SELECT t.teacher_id, t.full_name, u.email 
            FROM teachers t 
            JOIN users u ON t.user_id = u.id 
            WHERE u.id = ?
        ");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $teacher = $result->fetch_assoc();
        $teacherId = $teacher['teacher_id'] ?? null;
        $stmt->close();

        if ($teacherId) {
            // 2. Get assigned classes count
            $stmt = $conn->prepare("
                SELECT COUNT(DISTINCT class_id) as class_count 
                FROM subjects 
                WHERE teacher_id = ?
            ");
            $stmt->bind_param("i", $teacherId);
            $stmt->execute();
            $classCount = $stmt->get_result()->fetch_assoc()['class_count'];
            $stmt->close();

            // 3. Get assigned subjects count
            $stmt = $conn->prepare("
                SELECT COUNT(*) as subject_count 
                FROM subjects 
                WHERE teacher_id = ?
            ");
            $stmt->bind_param("i", $teacherId);
            $stmt->execute();
            $subjectCount = $stmt->get_result()->fetch_assoc()['subject_count'];
            $stmt->close();

            // 4. Get total students in assigned classes (Distinct count)
            $stmt = $conn->prepare("
                SELECT COUNT(DISTINCT s.student_id) as student_count 
                FROM students s
                JOIN subjects sub ON s.class_id = sub.class_id
                WHERE sub.teacher_id = ?
            ");
            $stmt->bind_param("i", $teacherId);
            $stmt->execute();
            $studentCount = $stmt->get_result()->fetch_assoc()['student_count'];
            $stmt->close();
        }

        // 5. Get recent notices
        $result = $conn->query("SELECT * FROM notices ORDER BY created_at DESC LIMIT 5");
        if ($result) {
            $recentNotices = $result->fetch_all(MYSQLI_ASSOC);
        }
        
    }
} catch (mysqli_sql_exception $e) {
    error_log("Teacher dashboard error: " . $e->getMessage());
    // Fallback in case of database error
    $teacher = [];
    $classCount = $subjectCount = $studentCount = 0;
    $recentNotices = [];
}

// Include layout
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <!-- 1. Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="mb-2">Welcome back, <?php echo htmlspecialchars($teacher['full_name'] ?? 'Teacher'); ?>!</h2>
                            <p class="text-muted mb-0">Your dashboard summary for today.</p>
                        </div>
                        <div class="d-none d-md-block text-end">
                            <div class="fs-4 fw-bold text-primary"><?php echo date('d'); ?></div>
                            <div class="text-muted small"><?php echo date('M Y'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 2. Consolidated Statistics Cards (Simple 4-column grid) -->
        <div class="row mb-4 g-4">
            <!-- Assigned Classes Card -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="dashboard-card text-center">
                    <i class="fas fa-school fa-2x text-primary mb-2"></i>
                    <div class="card-value"><?php echo number_format($classCount); ?></div>
                    <div class="card-title">Classes</div>
                    <div class="small text-muted">Total distinct classes</div>
                </div>
            </div>
            
            <!-- Assigned Subjects Card -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="dashboard-card text-center">
                    <i class="fas fa-book fa-2x text-success mb-2"></i>
                    <div class="card-value"><?php echo number_format($subjectCount); ?></div>
                    <div class="card-title">Subjects</div>
                    <div class="small text-muted">Subjects you teach</div>
                </div>
            </div>
            
            <!-- Total Students Card -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="dashboard-card text-center">
                    <i class="fas fa-user-graduate fa-2x text-info mb-2"></i>
                    <div class="card-value"><?php echo number_format($studentCount); ?></div>
                    <div class="card-title">Students</div>
                    <div class="small text-muted">Total students impacted</div>
                </div>
            </div>
            
            <!-- Placeholder Card (Simplified) -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="dashboard-card text-center">
                    <i class="fas fa-tasks fa-2x text-warning mb-2"></i>
                    <div class="card-value">0</div>
                    <div class="card-title">Pending Tasks</div>
                    <div class="small text-muted">Tasks to complete</div>
                </div>
            </div>
        </div>
        
        <!-- 3. Quick Actions & Notices (Simplified to a clean 50/50 split) -->
        <div class="row g-4">
            <!-- Quick Actions -->
            <div class="col-md-6">
                <div class="dashboard-card h-100">
                    <h5 class="card-title mb-3"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    <div class="row g-3">
                        <div class="col-lg-6 col-md-12">
                            <a href="attendance.php" class="btn btn-primary w-100 p-3">
                                <i class="fas fa-calendar-check fa-2x mb-2 d-block"></i>Mark Attendance
                            </a>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <a href="marks.php" class="btn btn-success w-100 p-3">
                                <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>Enter Marks
                            </a>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <a href="my-classes.php" class="btn btn-warning w-100 p-3">
                                <i class="fas fa-school fa-2x mb-2 d-block"></i>My Classes
                            </a>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <a href="notices-manage.php" class="btn btn-info w-100 p-3">
                                <i class="fas fa-bullhorn fa-2x mb-2 d-block"></i>Manage Notices
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Notices -->
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
                                    <h6 class="notice-title fw-bold text-primary"><?php echo htmlspecialchars($notice['title']); ?></h6>
                                    <p class="notice-description small text-muted mb-1"><?php echo nl2br(htmlspecialchars(substr($notice['description'], 0, 80) . (strlen($notice['description']) > 80 ? '...' : ''))); ?></p>
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
    </div>
</div>

<?php
include '../includes/footer.php';
?>