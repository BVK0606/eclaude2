<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Edit Subject';

// --- Get Subject ID ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: subjects.php');
    exit;
}
$subjectId = (int)$_GET['id'];

$error = '';
$success = '';

// --- Fetch Subject Details ---
$subjectQuery = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id = $subjectId");
$subject = mysqli_fetch_assoc($subjectQuery);

if (!$subject) {
    $_SESSION['error'] = 'Subject not found.';
    header('Location: subjects.php');
    exit;
}

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subjectName = sanitizeInput($_POST['subject_name'] ?? '');

    if (empty($subjectName)) {
        $error = 'Please enter a subject name.';
    } else {
        // Check if subject name already exists (excluding current subject)
        $checkQuery = mysqli_query($conn, "
            SELECT subject_id FROM subjects 
            WHERE subject_name = '$subjectName' AND subject_id != $subjectId
        ");
        if (mysqli_num_rows($checkQuery) > 0) {
            $error = 'This subject name already exists.';
        } else {
            // Update subject
            $updateQuery = mysqli_query($conn, "
                UPDATE subjects SET subject_name = '$subjectName'
                WHERE subject_id = $subjectId
            ");

            if ($updateQuery) {
                $success = 'Subject updated successfully!';
                // Refresh data
                $subject = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id = $subjectId"));
            } else {
                $error = 'Failed to update subject. Please try again.';
            }
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- ======= Page Content ======= -->
<div class="main-content">
    <div class="content">

        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2>Edit Subject</h2>
                    <p class="text-muted mb-2">Update subject information below.</p>
                    <a href="subjects.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i>Back to Subjects
                    </a>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Subject Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   name="subject_name" 
                                   value="<?php echo htmlspecialchars($subject['subject_name']); ?>" 
                                   required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="subjects.php" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Subject
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>