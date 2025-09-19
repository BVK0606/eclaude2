<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Edit Class';

// Get class ID
$classId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$classId) {
    header('Location: classes.php');
    exit;
}

$error = '';
$success = '';

// Fetch class
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT * FROM classes WHERE class_id = ?');
    $stmt->execute([$classId]);
    $class = $stmt->fetch();
    if (!$class) {
        header('Location: classes.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'Failed to fetch class.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $className = sanitizeInput($_POST['class_name'] ?? '');
        if (empty($className)) {
            $error = 'Please enter a class name.';
        } else {
            try {
                $stmt = $db->prepare('UPDATE classes SET class_name = ? WHERE class_id = ?');
                $stmt->execute([$className, $classId]);
                $success = 'Class updated successfully!';
                // Refresh class data
                $stmt = $db->prepare('SELECT * FROM classes WHERE class_id = ?');
                $stmt->execute([$classId]);
                $class = $stmt->fetch();
            } catch (PDOException $e) {
                $error = 'Failed to update class.';
            }
        }
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
                    <h2 class="mb-2">Edit Class</h2>
                    <a href="classes.php" class="btn btn-secondary btn-sm mb-2"><i class="fas fa-arrow-left me-2"></i>Back to Classes</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <div class="mb-3">
                            <label for="class_name" class="form-label">Class Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="class_name" name="class_name" value="<?php echo htmlspecialchars($class['class_name']); ?>" required>
                            <div class="invalid-feedback">Please enter a class name.</div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Class</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
