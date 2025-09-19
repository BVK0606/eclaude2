<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Edit Subject';

// Get subject ID
$subjectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$subjectId) {
    header('Location: subjects.php');
    exit;
}

$error = '';
$success = '';

// Fetch subject
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT * FROM subjects WHERE subject_id = ?');
    $stmt->execute([$subjectId]);
    $subject = $stmt->fetch();
    if (!$subject) {
        header('Location: subjects.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'Failed to fetch subject.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $subjectName = sanitizeInput($_POST['subject_name'] ?? '');
        if (empty($subjectName)) {
            $error = 'Please enter a subject name.';
        } else {
            try {
                $stmt = $db->prepare('UPDATE subjects SET subject_name = ? WHERE subject_id = ?');
                $stmt->execute([$subjectName, $subjectId]);
                $success = 'Subject updated successfully!';
                // Refresh subject data
                $stmt = $db->prepare('SELECT * FROM subjects WHERE subject_id = ?');
                $stmt->execute([$subjectId]);
                $subject = $stmt->fetch();
            } catch (PDOException $e) {
                $error = 'Failed to update subject.';
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
                    <h2 class="mb-2">Edit Subject</h2>
                    <a href="subjects.php" class="btn btn-secondary btn-sm mb-2"><i class="fas fa-arrow-left me-2"></i>Back to Subjects</a>
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
                            <label for="subject_name" class="form-label">Subject Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject_name" name="subject_name" value="<?php echo htmlspecialchars($subject['subject_name']); ?>" required>
                            <div class="invalid-feedback">Please enter a subject name.</div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Subject</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
