<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Edit Class';

// Get class ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: classes.php');
    exit;
}
$classId = (int)$_GET['id'];

$error = '';
$success = '';

// --- Fetch Class Data ---
$query = "SELECT * FROM classes WHERE class_id = $classId";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $class = mysqli_fetch_assoc($result);
} else {
    $_SESSION['error'] = 'Class not found.';
    header('Location: classes.php');
    exit;
}

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $className = sanitizeInput($_POST['class_name'] ?? '');

    if (empty($className)) {
        $error = 'Please enter a class name.';
    } else {
        // Check for duplicate class name (excluding current)
        $checkQuery = "SELECT class_id FROM classes WHERE class_name = '$className' AND class_id != $classId";
        $checkResult = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($checkResult) > 0) {
            $error = 'Class name already exists.';
        } else {
            // Update class
            $updateQuery = "UPDATE classes SET class_name = '$className' WHERE class_id = $classId";
            if (mysqli_query($conn, $updateQuery)) {
                $success = 'Class updated successfully!';
                // Refresh class data
                $class = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM classes WHERE class_id = $classId"));
            } else {
                $error = 'Failed to update class. Please try again.';
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
                    <p class="text-muted mb-3">Update the class information below.</p>
                    <a href="classes.php" class="btn btn-secondary btn-sm mb-3">
                        <i class="fas fa-arrow-left me-2"></i>Back to Classes
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="class_name" class="form-label">
                                <i class="fas fa-school me-1"></i>Class Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="class_name"
                                   name="class_name"
                                   value="<?php echo htmlspecialchars($class['class_name']); ?>"
                                   required>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Class
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
