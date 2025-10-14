<?php
require_once '../config.php';
requireRole('teacher');

$pageTitle = 'My Classes';

// Connect to database
$db = Database::getInstance()->getConnection();
$assignedClasses = [];

try {
    // Find teacher ID linked to current logged-in user
    $stmt = $db->prepare("SELECT teacher_id FROM teachers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id'] ?? 0]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch assigned classes if teacher exists
    if ($teacher && !empty($teacher['teacher_id'])) {
        $stmt = $db->prepare("
            SELECT c.class_id, c.class_name
            FROM teacher_classes tc
            JOIN classes c ON tc.class_id = c.class_id
            WHERE tc.teacher_id = ?
            ORDER BY c.class_name
        ");
        $stmt->execute([$teacher['teacher_id']]);
        $assignedClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("My Classes Error: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="mb-2"><i class="fas fa-school me-2 text-primary"></i>My Classes</h2>
                    <p class="text-muted mb-0">View the list of classes assigned to you.</p>
                </div>
            </div>
        </div>

        <!-- Class List -->
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <?php if (empty($assignedClasses)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-school fa-3x mb-3 d-block"></i>
                            <p>No classes assigned to you yet.</p>
                            <a href="../admin/classes.php" class="btn btn-primary">
                                <i class="fas fa-envelope me-1"></i>Contact Admin
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($assignedClasses as $class): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="fas fa-chalkboard-teacher fa-3x text-primary mb-3"></i>
                                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($class['class_name']); ?></h5>
                                            <p class="text-muted small mb-3">Class ID:
                                                <?php echo htmlspecialchars($class['class_id']); ?></p>
                                            <a href="class-details.php?id=<?php echo $class['class_id']; ?>"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </a>
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

<?php include '../includes/footer.php'; ?>