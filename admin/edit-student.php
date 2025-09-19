<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Edit Student';

// Check if student ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage-students.php');
    exit;
}

$studentId = $_GET['id'];

// Get student data
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT s.student_id, s.roll_no, s.full_name, s.dob, s.address, s.class_id,
               u.email, u.uname as username
        FROM students s
        LEFT JOIN users u ON s.user_id = u.id
        WHERE s.student_id = ?
    ");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch();
    
    if (!$student) {
        $_SESSION['error'] = 'Student not found.';
        header('Location: manage-students.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Failed to fetch student data. Please try again later.';
    error_log("Fetch student error: " . $e->getMessage());
    header('Location: manage-students.php');
    exit;
}

// Handle form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $rollNo = sanitizeInput($_POST['roll_no'] ?? '');
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $classId = sanitizeInput($_POST['class_id'] ?? '');
        $dob = sanitizeInput($_POST['dob'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        
        // Validation
        if (empty($rollNo) || empty($fullName) || empty($email) || empty($classId)) {
            $error = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                $db = Database::getInstance()->getConnection();
                
                // Check if roll number already exists (excluding current student)
                $stmt = $db->prepare("SELECT COUNT(*) FROM students WHERE roll_no = ? AND student_id != ?");
                $stmt->execute([$rollNo, $studentId]);
                $rollExists = $stmt->fetchColumn();
                
                if ($rollExists > 0) {
                    $error = 'Roll number already exists.';
                } else {
                    // Check if email already exists (excluding current student's user)
                    $stmt = $db->prepare("
                        SELECT COUNT(*) FROM users u 
                        JOIN students s ON u.id = s.user_id 
                        WHERE u.email = ? AND s.student_id != ?
                    ");
                    $stmt->execute([$email, $studentId]);
                    $emailExists = $stmt->fetchColumn();
                    
                    if ($emailExists > 0) {
                        $error = 'Email already exists.';
                    } else {
                        // Update student and user data
                        $db->beginTransaction();
                        
                        try {
                            // Update student
                            $stmt = $db->prepare("
                                UPDATE students 
                                SET roll_no = ?, full_name = ?, class_id = ?, dob = ?, address = ? 
                                WHERE student_id = ?
                            ");
                            $stmt->execute([$rollNo, $fullName, $classId, $dob, $address, $studentId]);
                            
                            // Update user email
                            $stmt = $db->prepare("
                                UPDATE users u
                                JOIN students s ON u.id = s.user_id
                                SET u.email = ?
                                WHERE s.student_id = ?
                            ");
                            $stmt->execute([$email, $studentId]);
                            
                            $db->commit();
                            $success = 'Student updated successfully!';
                            
                            // Refresh student data
                            $stmt = $db->prepare("
                                SELECT s.student_id, s.roll_no, s.full_name, s.dob, s.address, s.class_id,
                                       u.email, u.uname as username
                                FROM students s
                                LEFT JOIN users u ON s.user_id = u.id
                                WHERE s.student_id = ?
                            ");
                            $stmt->execute([$studentId]);
                            $student = $stmt->fetch();
                            
                        } catch (Exception $e) {
                            $db->rollback();
                            throw $e;
                        }
                    }
                }
            } catch (PDOException $e) {
                $error = 'Failed to update student. Please try again later.';
                error_log("Update student error: " . $e->getMessage());
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

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="mb-2">Edit Student</h2>
                    <p class="text-muted mb-0">Update student information.</p>
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
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">
                                    <i class="fas fa-id-card me-1"></i>Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="full_name" 
                                       name="full_name" 
                                       placeholder="Enter student's full name"
                                       value="<?php echo htmlspecialchars($student['full_name']); ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Please enter the student's full name.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="roll_no" class="form-label">
                                    <i class="fas fa-hashtag me-1"></i>Roll Number <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="roll_no" 
                                       name="roll_no" 
                                       placeholder="Enter roll number"
                                       value="<?php echo htmlspecialchars($student['roll_no']); ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Please enter a roll number.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user me-1"></i>Username
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       value="<?php echo htmlspecialchars($student['username']); ?>"
                                       disabled>
                                <div class="form-text">Username cannot be changed.</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email Address <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       placeholder="Enter email address"
                                       value="<?php echo htmlspecialchars($student['email']); ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="class_id" class="form-label">
                                    <i class="fas fa-school me-1"></i>Class <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="class_id" name="class_id" required>
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['class_id']; ?>" 
                                            <?php echo ($student['class_id'] == $class['class_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($class['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a class.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="dob" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Date of Birth
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="dob" 
                                       name="dob" 
                                       value="<?php echo htmlspecialchars($student['dob']); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Address
                            </label>
                            <textarea class="form-control" 
                                      id="address" 
                                      name="address" 
                                      rows="3" 
                                      placeholder="Enter student's address"><?php echo htmlspecialchars($student['address']); ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="manage-students.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Students
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>