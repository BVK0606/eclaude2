<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Assign Subject to Teacher/Class';

// --- Fetch Data ---
$subjects = mysqli_query($conn, "SELECT subject_id, subject_name FROM subjects ORDER BY subject_name");
$teachers = mysqli_query($conn, "SELECT teacher_id, full_name FROM teachers ORDER BY full_name");
$classes = mysqli_query($conn, "SELECT class_id, class_name FROM classes ORDER BY class_name");

$error = '';
$success = '';

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $teacherId = (int)($_POST['teacher_id'] ?? 0);
    $classId = (int)($_POST['class_id'] ?? 0);

    if (!$subjectId || !$teacherId || !$classId) {
        $error = 'Please select subject, teacher, and class.';
    } else {
        // Check if subject exists
        $checkSubject = mysqli_query($conn, "SELECT subject_id FROM subjects WHERE subject_id = $subjectId");
        if (mysqli_num_rows($checkSubject) == 0) {
            $error = 'Invalid subject selected.';
        } else {
            // Update subject assignment
            $update = mysqli_query($conn, "
                UPDATE subjects 
                SET teacher_id = '$teacherId', class_id = '$classId'
                WHERE subject_id = '$subjectId'
            ");
            if ($update) {
                $success = 'Subject assigned to teacher and class successfully!';
            } else {
                $error = 'Failed to assign subject. Please try again.';
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
                    <h2 class="mb-2">Assign Subject to Teacher/Class</h2>
                    <p class="text-muted mb-3">Select a subject, teacher, and class to link them together.</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label"><i class="fas fa-book me-1"></i>Subject</label>
                            <select name="subject_id" class="form-select" required>
                                <option value="">Select Subject</option>
                                <?php while ($s = mysqli_fetch_assoc($subjects)): ?>
                                    <option value="<?php echo $s['subject_id']; ?>">
                                        <?php echo htmlspecialchars($s['subject_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><i class="fas fa-user-tie me-1"></i>Teacher</label>
                            <select name="teacher_id" class="form-select" required>
                                <option value="">Select Teacher</option>
                                <?php while ($t = mysqli_fetch_assoc($teachers)): ?>
                                    <option value="<?php echo $t['teacher_id']; ?>">
                                        <?php echo htmlspecialchars($t['full_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><i class="fas fa-school me-1"></i>Class</label>
                            <select name="class_id" class="form-select" required>
                                <option value="">Select Class</option>
                                <?php while ($c = mysqli_fetch_assoc($classes)): ?>
                                    <option value="<?php echo $c['class_id']; ?>">
                                        <?php echo htmlspecialchars($c['class_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-12 d-flex justify-content-between mt-3">
                            <a href="subjects.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-check me-2"></i>Assign Subject
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
