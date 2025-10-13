<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Manage Classes';

$error = '';
$success = '';

// --- Add New Class ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_class'])) {
    $className = sanitizeInput($_POST['class_name'] ?? '');

    // Basic validation
    if (empty($className)) {
        $error = 'Please enter a class name.';
    } elseif (!preg_match('/^[A-Za-z0-9\s]{1,50}$/', $className)) {
        $error = 'Class name can only contain letters, numbers, and spaces (max 50 characters).';
    } else {
        // Check duplicate class
        $checkClass = mysqli_query($conn, "SELECT class_id FROM classes WHERE class_name='$className'");
        if (mysqli_num_rows($checkClass) > 0) {
            $error = 'This class name already exists.';
        } else {
            $insert = mysqli_query($conn, "INSERT INTO classes (class_name) VALUES ('$className')");
            if ($insert) {
                $success = 'Class added successfully!';
                $_POST = []; // Clear form
            } else {
                $error = 'Failed to add class. Please try again.';
            }
        }
    }
}

// --- Delete Class ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $classId = (int)$_GET['delete'];

    // Check if class has students
    $checkStudents = mysqli_query($conn, "SELECT COUNT(*) AS total FROM students WHERE class_id=$classId");
    $row = mysqli_fetch_assoc($checkStudents);
    if ($row['total'] > 0) {
        $_SESSION['error'] = 'Cannot delete a class that has students. Reassign or remove them first.';
    } else {
        $delete = mysqli_query($conn, "DELETE FROM classes WHERE class_id=$classId");
        if ($delete) {
            $_SESSION['success'] = 'Class deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete class. Please try again.';
        }
    }

    header('Location: classes.php');
    exit;
}

// --- Fetch All Classes ---
$result = mysqli_query($conn, "
    SELECT c.*, COUNT(s.student_id) AS student_count
    FROM classes c
    LEFT JOIN students s ON c.class_id = s.class_id
    GROUP BY c.class_id
    ORDER BY c.class_name
");

$classes = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $classes[] = $row;
    }
}

// --- Show messages ---
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
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
                    <p class="text-muted mb-0">Add, view, and manage all classes in the system.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Add New Class -->
            <div class="col-md-5 mb-4">
                <div class="dashboard-card">
                    <h4 class="mb-3">Add New Class</h4>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label>Class Name *</label>
                            <input type="text" class="form-control" name="class_name"
                                   placeholder="Enter class name (e.g., 10A)"
                                   value="<?php echo htmlspecialchars($_POST['class_name'] ?? ''); ?>" required>
                        </div>
                        <button type="submit" name="add_class" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Add Class
                        </button>
                    </form>
                </div>
            </div>

            <!-- Class List -->
            <div class="col-md-7">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="mb-0">All Classes</h4>
                        <span class="badge bg-primary"><?php echo count($classes); ?> classes</span>
                    </div>

                    <?php if (empty($classes)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-school fa-3x mb-3 d-block"></i>
                            <p>No classes found. Add your first class to begin.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Students</th>
                                        <th>Created On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classes as $class): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $class['student_count']; ?> students</span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($class['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="edit-class.php?id=<?php echo $class['class_id']; ?>" 
                                                       class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
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

<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this class?')) {
        window.location.href = 'classes.php?delete=' + id;
    }
}
</script>

<?php include '../includes/footer.php'; ?>