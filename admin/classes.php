<?php
// --- Classes Management Page ---
// This file allows admin to add, view, and delete classes.
// It is connected to other class services (edit, assign, report).

require_once '../config.php';
requireRole('admin'); // Only admin can access

$pageTitle = 'Manage Classes';

// Initialize message variables
$error = '';
$success = '';

// --- Add New Class ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_class'])) {
    // CSRF protection
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        // Get and sanitize class name
        $className = sanitizeInput($_POST['class_name'] ?? '');
        if (empty($className)) {
            $error = 'Please enter a class name.';
        } else {
            try {
                $db = Database::getInstance()->getConnection();
                // Check if class already exists
                $stmt = $db->prepare("SELECT COUNT(*) FROM classes WHERE class_name = ?");
                $stmt->execute([$className]);
                $exists = $stmt->fetchColumn();
                if ($exists > 0) {
                    $error = 'Class already exists.';
                } else {
                    // Insert new class
                    $stmt = $db->prepare("INSERT INTO classes (class_name) VALUES (?)");
                    $stmt->execute([$className]);
                    $success = 'Class added successfully!';
                    $_POST = []; // Clear form
                }
            } catch (PDOException $e) {
                $error = 'Failed to add class. Please try again later.';
                error_log("Add class error: " . $e->getMessage());
            }
        }
    }
}

// --- Delete Class ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $classId = $_GET['delete'];
    try {
        $db = Database::getInstance()->getConnection();
        // Check if class has students before deleting
        $stmt = $db->prepare("SELECT COUNT(*) FROM students WHERE class_id = ?");
        $stmt->execute([$classId]);
        $hasStudents = $stmt->fetchColumn();
        if ($hasStudents > 0) {
            $_SESSION['error'] = 'Cannot delete class with students. Please reassign students first.';
        } else {
            // Delete class
            $stmt = $db->prepare("DELETE FROM classes WHERE class_id = ?");
            $stmt->execute([$classId]);
            $_SESSION['success'] = 'Class deleted successfully.';
        }
        // Redirect to avoid resubmission
        header('Location: classes.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to delete class. Please try again later.';
        error_log("Delete class error: " . $e->getMessage());
        header('Location: classes.php');
        exit;
    }
}

// --- Fetch All Classes ---
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query(
        "SELECT c.*, COUNT(s.student_id) as student_count
        FROM classes c
        LEFT JOIN students s ON c.class_id = s.class_id
        GROUP BY c.class_id
        ORDER BY c.class_name"
    );
    $classes = $stmt->fetchAll();
} catch (PDOException $e) {
    $classes = [];
    error_log("Fetch classes error: " . $e->getMessage());
}

// --- Show messages from other actions ---
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="mb-2">Manage Classes</h2>
                    <p class="text-muted mb-0">Add and manage classes in the system.</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-5 mb-4">
                <div class="dashboard-card">
                    <h4 class="mb-3">Add New Class</h4>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="class_name" class="form-label">
                                <i class="fas fa-school me-1"></i>Class Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="class_name" 
                                   name="class_name" 
                                   placeholder="Enter class name (e.g., 10A)"
                                   value="<?php echo htmlspecialchars($_POST['class_name'] ?? ''); ?>"
                                   required>
                            <div class="invalid-feedback">
                                Please enter a class name.
                            </div>
                        </div>
                        
                        <button type="submit" name="add_class" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Add Class
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="col-md-7">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="mb-0">All Classes</h4>
                        <span class="badge bg-primary"><?php echo count($classes); ?> classes</span>
                    </div>
                    
                    <?php if (empty($classes)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-school fa-3x mb-3 d-block"></i>
                            <p>No classes yet. Add your first class to get started.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Students</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classes as $class): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-semibold"><?php echo htmlspecialchars($class['class_name']); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $class['student_count']; ?> students</span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($class['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="edit-class.php?id=<?php echo $class['class_id']; ?>" class="btn btn-sm btn-outline-success" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            title="Delete" 
                                                            onclick="confirmDelete(<?php echo $class['class_id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
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
    </div>
</div>

<?php
$pageScripts = "
    // Confirm delete
    function confirmDelete(classId) {
        if (confirm('Are you sure you want to delete this class? This action cannot be undone.')) {
            window.location.href = 'classes.php?delete=' + classId;
        }
    }
";

include '../includes/footer.php';
?>