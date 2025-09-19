<?php
require_once '../config.php';
requireRole('teacher');

$pageTitle = 'My Classes';

// Get teacher information
try {
    $db = Database::getInstance()->getConnection();
    
    // Get teacher details
    $stmt = $db->prepare("
        SELECT teacher_id 
        FROM teachers 
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $teacher = $stmt->fetch();
    
    // Get assigned classes with student count
    $stmt = $db->prepare("
        SELECT c.class_id, c.class_name, COUNT(s.student_id) as student_count
        FROM classes c
        JOIN subjects sub ON c.class_id = sub.class_id
        LEFT JOIN students s ON c.class_id = s.class_id
        WHERE sub.teacher_id = ?
        GROUP BY c.class_id
        ORDER BY c.class_name
    ");
    $stmt->execute([$teacher['teacher_id']]);
    $assignedClasses = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("My classes error: " . $e->getMessage());
    $teacher = [];
    $assignedClasses = [];
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="mb-2">My Classes</h2>
                    <p class="text-muted mb-0">View your assigned classes and students.</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <?php if (empty($assignedClasses)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-school fa-3x mb-3 d-block"></i>
                            <p>No classes assigned to you yet.</p>
                            <a href="../admin/classes.php" class="btn btn-primary">Contact Admin</a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($assignedClasses as $class): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <div class="class-icon mb-3">
                                                <i class="fas fa-school fa-3x text-primary"></i>
                                            </div>
                                            <h5 class="card-title"><?php echo htmlspecialchars($class['class_name']); ?></h5>
                                            <p class="card-text">
                                                <span class="badge bg-info"><?php echo $class['student_count']; ?> students</span>
                                            </p>
                                            <div class="mt-3">
                                                <a href="class-details.php?id=<?php echo $class['class_id']; ?>" class="btn btn-primary btn-sm me-2">
                                                    <i class="fas fa-eye me-1"></i>View Details
                                                </a>
                                                <a href="attendance.php?class_id=<?php echo $class['class_id']; ?>" class="btn btn-success btn-sm">
                                                    <i class="fas fa-calendar-check me-1"></i>Attendance
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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