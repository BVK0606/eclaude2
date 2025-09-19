<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Manage Subjects';

// Handle form submission for adding subject
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subject'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $subjectName = sanitizeInput($_POST['subject_name'] ?? '');
        $classId = sanitizeInput($_POST['class_id'] ?? '');
        
        if (empty($subjectName) || empty($classId)) {
            $error = 'Please fill in all required fields.';
        } else {
            try {
                $db = Database::getInstance()->getConnection();
                
                // Check if subject already exists for this class
                $stmt = $db->prepare("SELECT COUNT(*) FROM subjects WHERE subject_name = ? AND class_id = ?");
                $stmt->execute([$subjectName, $classId]);
                $exists = $stmt->fetchColumn();
                
                if ($exists > 0) {
                    $error = 'Subject already exists for this class.';
                } else {
                    // Insert subject
                    $stmt = $db->prepare("INSERT INTO subjects (subject_name, class_id) VALUES (?, ?)");
                    $stmt->execute([$subjectName, $classId]);
                    
                    $success = 'Subject added successfully!';
                    $_POST = []; // Clear form
                }
            } catch (PDOException $e) {
                $error = 'Failed to add subject. Please try again later.';
                error_log("Add subject error: " . $e->getMessage());
            }
        }
    }
}

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $subjectId = $_GET['delete'];
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Delete subject
        $stmt = $db->prepare("DELETE FROM subjects WHERE subject_id = ?");
        $stmt->execute([$subjectId]);
        
        $_SESSION['success'] = 'Subject deleted successfully.';
        
        header('Location: subjects.php');
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to delete subject. Please try again later.';
        error_log("Delete subject error: " . $e->getMessage());
        header('Location: subjects.php');
        exit;
    }
}

// Get all subjects with class information
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("
        SELECT s.*, c.class_name
        FROM subjects s
        LEFT JOIN classes c ON s.class_id = c.class_id
        ORDER BY c.class_name, s.subject_name
    ");
    $subjects = $stmt->fetchAll();
} catch (PDOException $e) {
    $subjects = [];
    error_log("Fetch subjects error: " . $e->getMessage());
}

// Get classes for dropdown
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT class_id, class_name FROM classes ORDER BY class_name");
    $classes = $stmt->fetchAll();
} catch (PDOException $e) {
    $classes = [];
    error_log("Fetch classes error: " . $e->getMessage());
}

// Check for messages from other pages
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
                    <p class="text-muted mb-0">Add and manage subjects for each class.</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-5 mb-4">
                <div class="dashboard-card">
                    <h4 class="mb-3">Add New Subject</h4>
                    
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
                            <div class="invalid-feedback">
                                Please enter a subject name.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="class_id" class="form-label">
                                <i class="fas fa-school me-1"></i>Class <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="class_id" name="class_id" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['class_id']; ?>" 
                                        <?php echo (($_POST['class_id'] ?? '') == $class['class_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($class['class_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Please select a class.
                            </div>
                        </div>
                        
                        <button type="submit" name="add_subject" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Add Subject
                        </button>
                    </form>
                </div>
            </div>
            
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
                                        <th>Class</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subjects as $subject): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-semibold"><?php echo htmlspecialchars($subject['subject_name']); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($subject['class_name']); ?></span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($subject['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="edit-subject.php?id=<?php echo $subject['subject_id']; ?>" class="btn btn-sm btn-outline-success" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            title="Delete" 
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

<?php
$pageScripts = "
    // Confirm delete
    function confirmDelete(subjectId) {
        if (confirm('Are you sure you want to delete this subject? This action cannot be undone.')) {
            window.location.href = 'subjects.php?delete=' + subjectId;
        }
    }
";

include '../includes/footer.php';
?>