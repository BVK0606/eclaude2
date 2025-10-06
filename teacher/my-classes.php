<?php
require_once '../config.php';
requireRole('teacher');

$pageTitle = 'My Classes';

$db = Database::getInstance()->getConnection();
$teacher = [];
$assignedClasses = [];

if (!empty($_SESSION['user_id'])) {
    try {
        // Get teacher details
        $stmt = $db->prepare('SELECT teacher_id FROM teachers WHERE user_id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

        // Only proceed if teacher found
        if ($teacher && !empty($teacher['teacher_id'])) {
            // Get assigned classes
            $stmt = $db->prepare('
                SELECT c.class_id, c.class_name
                FROM teacher_classes tc
                JOIN classes c ON tc.class_id = c.class_id
                WHERE tc.teacher_id = ?
                ORDER BY c.class_name
            ');
            $stmt->execute([$teacher['teacher_id']]);
            $assignedClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // No need to unset $class since we removed the foreach
        }
    } catch (PDOException $e) {
        error_log('My classes error: ' . $e->getMessage());
    }
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
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 shadow-sm border-0">
                                        <div class="card-body text-center d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="class-icon mb-3">
                                                    <i class="fas fa-school fa-3x text-primary"></i>
                                                </div>
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div>
                                                            <span class="fw-semibold">Class:</span> <?php echo htmlspecialchars($class['class_name']); ?>
                                                            <span class="text-muted ms-2">(ID: <?php echo $class['class_id']; ?>)</span>
                                                        </div>
                                                        <a href="class-details.php?id=<?php echo $class['class_id']; ?>" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-eye me-1"></i>View Details
                                                        </a>
                                                    </div>
                                            </div>
                                                <!-- Removed subjects and students sections -->
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