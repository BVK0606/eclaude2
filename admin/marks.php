<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Manage Marks';

// Handle form submission for adding marks
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_marks'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $classId = sanitizeInput($_POST['class_id'] ?? '');
        $subjectId = sanitizeInput($_POST['subject_id'] ?? '');
        $examType = sanitizeInput($_POST['exam_type'] ?? '');
        $marksData = $_POST['marks'] ?? [];
        
        if (empty($classId) || empty($subjectId) || empty($examType)) {
            $error = 'Please fill in all required fields.';
        } else {
            try {
                $db = Database::getInstance()->getConnection();
                
                // Begin transaction
                $db->beginTransaction();
                
                try {
                    // Delete existing marks for this exam type and subject
                    $stmt = $db->prepare("
                        DELETE m FROM marks m
                        JOIN students s ON m.student_id = s.student_id
                        WHERE m.subject_id = ? AND m.exam_type = ? AND s.class_id = ?
                    ");
                    $stmt->execute([$subjectId, $examType, $classId]);
                    
                    // Insert new marks records
                    foreach ($marksData as $studentId => $marks) {
                        if (!empty($marks) && is_numeric($marks)) {
                            $stmt = $db->prepare("
                                INSERT INTO marks (student_id, subject_id, exam_type, marks_obtained) 
                                VALUES (?, ?, ?, ?)
                            ");
                            $stmt->execute([$studentId, $subjectId, $examType, $marks]);
                        }
                    }
                    
                    $db->commit();
                    $success = 'Marks added successfully!';
                    
                } catch (Exception $e) {
                    $db->rollback();
                    throw $e;
                }
            } catch (PDOException $e) {
                $error = 'Failed to add marks. Please try again later.';
                error_log("Add marks error: " . $e->getMessage());
            }
        }
    }
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

// Get all subjects for dropdown (global subjects, no class_id filter)
$subjects = [];
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT subject_id, subject_name FROM subjects ORDER BY subject_name");
    $subjects = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch subjects error: " . $e->getMessage());
}

// Get students based on selected class
$students = [];
if (isset($_POST['class_id']) && !empty($_POST['class_id'])) {
    $classId = $_POST['class_id'];
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT student_id, roll_no, full_name 
            FROM students 
            WHERE class_id = ? 
            ORDER BY roll_no
        ");
        $stmt->execute([$classId]);
        $students = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Fetch students error: " . $e->getMessage());
    }
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
                    <h2 class="mb-2">Manage Marks</h2>
                    <p class="text-muted mb-0">Add and manage student marks.</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
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
                        
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="class_id" class="form-label">
                                    <i class="fas fa-school me-1"></i>Class <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="class_id" name="class_id" required onchange="this.form.submit()">
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['class_id']; ?>" 
                                            <?php echo (($_POST['class_id'] ?? '') == $class['class_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($class['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="subject_id" class="form-label">
                                    <i class="fas fa-book me-1"></i>Subject <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="subject_id" name="subject_id" required>
                                    <option value="">Select Subject</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?php echo $subject['subject_id']; ?>" 
                                            <?php echo (($_POST['subject_id'] ?? '') == $subject['subject_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="exam_type" class="form-label">
                                    <i class="fas fa-file-alt me-1"></i>Exam Type <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="exam_type" name="exam_type" required>
                                    <option value="">Select Exam Type</option>
                                    <option value="Midterm" <?php echo (($_POST['exam_type'] ?? '') == 'Midterm') ? 'selected' : ''; ?>>Midterm</option>
                                    <option value="Final" <?php echo (($_POST['exam_type'] ?? '') == 'Final') ? 'selected' : ''; ?>>Final</option>
                                    <option value="Quiz" <?php echo (($_POST['exam_type'] ?? '') == 'Quiz') ? 'selected' : ''; ?>>Quiz</option>
                                    <option value="Assignment" <?php echo (($_POST['exam_type'] ?? '') == 'Assignment') ? 'selected' : ''; ?>>Assignment</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label d-block">&nbsp;</label>
                                <button type="submit" name="add_marks" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-2"></i>Save Marks
                                </button>
                            </div>
                        </div>
                        
                        <?php if (!empty($students)): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Roll No</th>
                                            <th>Student Name</th>
                                            <th>Marks (Out of 100)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td>
                                                    <span class="fw-semibold"><?php echo htmlspecialchars($student['roll_no']); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                                <td>
                                                    <input type="number" 
                                                           class="form-control" 
                                                           name="marks[<?php echo $student['student_id']; ?>]" 
                                                           placeholder="Enter marks"
                                                           min="0" 
                                                           max="100"
                                                           step="0.01">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <?php if (isset($_POST['class_id']) && !empty($_POST['class_id'])): ?>
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                    <p>No students found in this class.</p>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-school fa-3x mb-3 d-block"></i>
                                    <p>Please select a class to view students.</p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>