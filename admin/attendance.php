<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Manage Attendance';

// Handle form submission for marking attendance
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_attendance'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $classId = sanitizeInput($_POST['class_id'] ?? '');
        $subjectId = sanitizeInput($_POST['subject_id'] ?? '');
        $date = sanitizeInput($_POST['date'] ?? '');
        $attendanceData = $_POST['attendance'] ?? [];
        
        if (empty($classId) || empty($subjectId) || empty($date)) {
            $error = 'Please fill in all required fields.';
        } else {
            try {
                $db = Database::getInstance()->getConnection();
                
                // Begin transaction
                $db->beginTransaction();
                
                try {
                    // Delete existing attendance for this date and subject
                    $stmt = $db->prepare("
                        DELETE a FROM attendance a
                        JOIN students s ON a.student_id = s.student_id
                        WHERE a.subject_id = ? AND a.date = ? AND s.class_id = ?
                    ");
                    $stmt->execute([$subjectId, $date, $classId]);
                    
                    // Insert new attendance records
                    foreach ($attendanceData as $studentId => $status) {
                        if (!empty($status)) {
                            $stmt = $db->prepare("
                                INSERT INTO attendance (student_id, subject_id, date, status) 
                                VALUES (?, ?, ?, ?)
                            ");
                            $stmt->execute([$studentId, $subjectId, $date, $status]);
                        }
                    }
                    
                    $db->commit();
                    $success = 'Attendance marked successfully!';
                    
                } catch (Exception $e) {
                    $db->rollback();
                    throw $e;
                }
            } catch (PDOException $e) {
                $error = 'Failed to mark attendance. Please try again later.';
                error_log("Mark attendance error: " . $e->getMessage());
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

// Get subjects based on selected class
$subjects = [];
if (isset($_POST['class_id']) && !empty($_POST['class_id'])) {
    $classId = $_POST['class_id'];
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT subject_id, subject_name 
            FROM subjects 
            WHERE class_id = ? 
            ORDER BY subject_name
        ");
        $stmt->execute([$classId]);
        $subjects = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Fetch subjects error: " . $e->getMessage());
    }
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
                    <h2 class="mb-2">Manage Attendance</h2>
                    <p class="text-muted mb-0">Mark and view student attendance.</p>
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
                            <div class="col-md-4">
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
                            
                            <div class="col-md-4">
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
                            
                            <div class="col-md-4">
                                <label for="date" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="date" 
                                       name="date" 
                                       value="<?php echo htmlspecialchars($_POST['date'] ?? date('Y-m-d')); ?>"
                                       required>
                            </div>
                        </div>
                        
                        <?php if (!empty($students)): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Roll No</th>
                                            <th>Student Name</th>
                                            <th>Attendance</th>
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
                                                    <div class="btn-group" role="group">
                                                        <input type="radio" 
                                                               class="btn-check" 
                                                               name="attendance[<?php echo $student['student_id']; ?>]" 
                                                               id="present_<?php echo $student['student_id']; ?>" 
                                                               value="present" 
                                                               autocomplete="off">
                                                        <label class="btn btn-outline-success" for="present_<?php echo $student['student_id']; ?>">
                                                            Present
                                                        </label>
                                                        
                                                        <input type="radio" 
                                                               class="btn-check" 
                                                               name="attendance[<?php echo $student['student_id']; ?>]" 
                                                               id="absent_<?php echo $student['student_id']; ?>" 
                                                               value="absent" 
                                                               autocomplete="off">
                                                        <label class="btn btn-outline-danger" for="absent_<?php echo $student['student_id']; ?>">
                                                            Absent
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" name="mark_attendance" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Save Attendance
                                </button>
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