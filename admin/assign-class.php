<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Assign Class to Teacher';

// Initialize messages
$error = '';
$success = '';

// --- Fetch teachers ---
$teachers = [];
$teacherQuery = mysqli_query($conn, "SELECT teacher_id, full_name FROM teachers ORDER BY full_name");
if ($teacherQuery) {
    while ($row = mysqli_fetch_assoc($teacherQuery)) {
        $teachers[] = $row;
    }
}

// --- Fetch classes ---
$classes = [];
$classQuery = mysqli_query($conn, "SELECT class_id, class_name FROM classes ORDER BY class_name");
if ($classQuery) {
    while ($row = mysqli_fetch_assoc($classQuery)) {
        $classes[] = $row;
    }
}

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $teacherId = (int)($_POST['teacher_id'] ?? 0);
    $classId = (int)($_POST['class_id'] ?? 0);

    if (empty($teacherId) || empty($classId)) {
        $error = 'Please select both teacher and class.';
    } else {
        // Check if already assigned
        $check = mysqli_query($conn, "SELECT * FROM teacher_classes WHERE teacher_id=$teacherId AND class_id=$classId");
        if (mysqli_num_rows($check) > 0) {
            $error = 'This teacher is already assigned to this class.';
        } else {
            $insert = mysqli_query($conn, "INSERT INTO teacher_classes (teacher_id, class_id) VALUES ($teacherId, $classId)");
            if ($insert) {
                $success = 'Class assigned to teacher successfully!';
                $_POST = []; // Clear form data
            } else {
                $error = 'Failed to assign class. Please try again.';
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
                    <h2 class="mb-2">Assign Class to Teacher</h2>
                    <p class="text-muted mb-3">Select a teacher and a class to assign them together.</p>

                    <!-- Show messages -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-chalkboard-teacher me-1"></i>Teacher <span class="text-danger">*</span></label>
                            <select name="teacher_id" class="form-select" required>
                                <option value="">Select Teacher</option>
                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?php echo $t['teacher_id']; ?>"
                                        <?php echo (isset($_POST['teacher_id']) && $_POST['teacher_id'] == $t['teacher_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($t['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-school me-1"></i>Class <span class="text-danger">*</span></label>
                            <select name="class_id" class="form-select" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?php echo $c['class_id']; ?>"
                                        <?php echo (isset($_POST['class_id']) && $_POST['class_id'] == $c['class_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['class_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check me-2"></i>Assign Class
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>