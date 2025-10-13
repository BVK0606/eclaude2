<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Manage Subjects';

$error = '';
$success = '';

// --- Add New Subject ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subject'])) {
    $subjectName = sanitizeInput($_POST['subject_name'] ?? '');

    if (empty($subjectName)) {
        $error = 'Please enter a subject name.';
    } else {
        // Check if subject already exists
        $checkQuery = "SELECT subject_id FROM subjects WHERE subject_name = '$subjectName'";
        $checkResult = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($checkResult) > 0) {
            $error = 'Subject already exists.';
        } else {
            $insertQuery = "INSERT INTO subjects (subject_name) VALUES ('$subjectName')";
            if (mysqli_query($conn, $insertQuery)) {
                $success = 'Subject added successfully!';
                $_POST = []; // clear form input
            } else {
                $error = 'Failed to add subject. Please try again.';
            }
        }
    }
}

// --- Delete Subject ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $subjectId = (int)$_GET['delete'];
    $deleteQuery = "DELETE FROM subjects WHERE subject_id = $subjectId";
    if (mysqli_query($conn, $deleteQuery)) {
        $_SESSION['success'] = 'Subject deleted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to delete subject. Please try again later.';
    }
    header('Location: subjects.php');
    exit;
}

// --- Fetch All Subjects ---
$subjectsResult = mysqli_query($conn, "SELECT * FROM subjects ORDER BY subject_name");
$subjects = [];
if ($subjectsResult) {
    while ($row = mysqli_fetch_assoc($subjectsResult)) {
        $subjects[] = $row;
    }
}

// --- Handle Messages from Other Pages ---
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
                    <h2 class="mb-2">Manage Subjects</h2>
                    <p class="text-muted mb-0">Add and manage subjects in the system.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Add Subject Form -->
            <div class="col-md-5 mb-4">
                <div class="dashboard-card">
                    <h4 class="mb-3">Add New Subject</h4>

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

                    <form method="POST">
                        <div class="mb-3">
                            <label for="subject_name" class="form-label">
                                <i class="fas fa-book me-1"></i>Subject Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="subject_name" 
                                   name="subject_name" 
                                   placeholder="Enter subject name"
                                   value="<?php echo htmlspecialchars($_POST['subject_name'] ?? ''); ?>"
                                   required>
                        </div>
                        <button type="submit" name="add_subject" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Add Subject
                        </button>
                    </form>
                </div>
            </div>

            <!-- List of Subjects -->
            <div class="col-md-7">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="mb-0">All Subjects</h4>
                        <span class="badge bg-primary"><?php echo count($subjects); ?> subjects</span>
                    </div>

                    <?php if (empty($subjects)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-book fa-3x mb-3 d-block"></i>
                            <p>No subjects yet. Add your first subject to get started.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Subject Name</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subjects as $subject): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($subject['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="edit-subject.php?id=<?php echo $subject['subject_id']; ?>" class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-danger"
                                                            onclick="confirmDelete(<?php echo $subject['subject_id']; ?>)">
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
function confirmDelete(subjectId) {
    if (confirm('Are you sure you want to delete this subject?')) {
        window.location.href = 'subjects.php?delete=' + subjectId;
    }
}
</script>

<?php include '../includes/footer.php'; ?>