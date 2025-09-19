<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Add Student';

// Handle form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $rollNo = sanitizeInput($_POST['roll_no'] ?? '');
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $classId = sanitizeInput($_POST['class_id'] ?? '');
        $dob = sanitizeInput($_POST['dob'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        
        // Validation
        if (empty($username) || empty($email) || empty($password) || empty($confirmPassword) || 
            empty($rollNo) || empty($fullName) || empty($classId)) {
            $error = 'Please fill in all required fields.';
        } elseif (strlen($username) < 3) {
            $error = 'Username must be at least 3 characters long.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            try {
                $db = Database::getInstance()->getConnection();
                
                // Check if username or email already exists
                $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE uname = ? OR email = ?");
                $stmt->execute([$username, $email]);
                $exists = $stmt->fetchColumn();
                
                if ($exists > 0) {
                    $error = 'Username or email already exists.';
                } else {
                    // Check if roll number already exists
                    $stmt = $db->prepare("SELECT COUNT(*) FROM students WHERE roll_no = ?");
                    $stmt->execute([$rollNo]);
                    $rollExists = $stmt->fetchColumn();
                    
                    if ($rollExists > 0) {
                        $error = 'Roll number already exists.';
                    } else {
                        // Hash the password
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Begin transaction
                        $db->beginTransaction();
                        
                        try {
                            // Insert user
                            $stmt = $db->prepare("INSERT INTO users (uname, upassword, role, email) VALUES (?, ?, 'student', ?)");
                            $stmt->execute([$username, $hashedPassword, $email]);
                            $userId = $db->lastInsertId();
                            
                            // Insert student
                            $stmt = $db->prepare("INSERT INTO students (user_id, class_id, roll_no, full_name, dob, address) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$userId, $classId, $rollNo, $fullName, $dob, $address]);
                            
                            $db->commit();
                            $success = 'Student added successfully!';
                            
                            // Clear form data on success
                            $_POST = [];
                            
                        } catch (Exception $e) {
                            $db->rollback();
                            throw $e;
                        }
                    }
                }
            } catch (PDOException $e) {
                $error = 'Failed to add student. Please try again later.';
                error_log("Add student error: " . $e->getMessage());
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
                    <h2 class="mb-2">Add New Student</h2>
                    <p class="text-muted mb-0">Fill in the details to register a new student.</p>
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
                                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
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
                                       value="<?php echo htmlspecialchars($_POST['roll_no'] ?? ''); ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Please enter a roll number.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user me-1"></i>Username <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       placeholder="Choose a username"
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                       minlength="3"
                                       required>
                                <div class="invalid-feedback">
                                    Username must be at least 3 characters long.
                                </div>
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
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Password <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Enter password"
                                           minlength="6"
                                           required>
                                    <button type="button" 
                                            class="btn btn-link position-absolute top-50 end-0 translate-middle-y me-2 p-0 border-0 bg-transparent"
                                            onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password-toggle"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Password must be at least 6 characters long.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Confirm Password <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="password" 
                                           class="form-control" 
                                           id="confirm_password" 
                                           name="confirm_password" 
                                           placeholder="Confirm password"
                                           minlength="6"
                                           required>
                                    <button type="button" 
                                            class="btn btn-link position-absolute top-50 end-0 translate-middle-y me-2 p-0 border-0 bg-transparent"
                                            onclick="togglePassword('confirm_password')">
                                        <i class="fas fa-eye" id="confirm_password-toggle"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Please confirm your password.
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
                                            <?php echo (($_POST['class_id'] ?? '') == $class['class_id']) ? 'selected' : ''; ?>>
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
                                       value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>">
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
                                      placeholder="Enter student's address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="manage-students.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Students
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Add Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$pageScripts = "
    // Toggle password visibility
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const toggle = document.getElementById(inputId + '-toggle');
        
        if (input.type === 'password') {
            input.type = 'text';
            toggle.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            toggle.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
    
    // Password confirmation validation
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        
        if (password !== confirmPassword) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });
";

include '../includes/footer.php';
?>