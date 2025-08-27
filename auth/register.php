<?php
require_once '../config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = $_SESSION['role'];
    header("Location: ../{$role}/dashboard.php");
    exit;
}

$error = '';
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = sanitizeInput($_POST['role'] ?? '');
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        
        // Validation
        if (empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($role) || empty($fullName)) {
            $error = 'Please fill in all required fields.';
        } elseif (strlen($username) < 3) {
            $error = 'Username must be at least 3 characters long.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (!in_array($role, ['student', 'teacher'])) {
            $error = 'Please select a valid role.';
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
                    // Hash the password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Begin transaction
                    $db->beginTransaction();
                    
                    try {
                        // Insert user
                        $stmt = $db->prepare("INSERT INTO users (uname, upassword, role, email) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$username, $hashedPassword, $role, $email]);
                        $userId = $db->lastInsertId();
                        
                        // Insert role-specific data
                        if ($role == 'student') {
                            // For demo purposes, assign to a random class
                            $classStmt = $db->prepare("SELECT class_id FROM classes ORDER BY RAND() LIMIT 1");
                            $classStmt->execute();
                            $classId = $classStmt->fetchColumn();
                            
                            $rollNo = 'S' . date('Y') . str_pad($userId, 4, '0', STR_PAD_LEFT);
                            
                            $stmt = $db->prepare("INSERT INTO students (user_id, class_id, roll_no, dob, address) VALUES (?, ?, ?, ?, ?)");
                            $stmt->execute([$userId, $classId, $rollNo, null, '']);
                            
                        } elseif ($role == 'teacher') {
                            $stmt = $db->prepare("INSERT INTO teachers (user_id, qualification, experience) VALUES (?, ?, ?)");
                            $stmt->execute([$userId, '', 0]);
                        }
                        
                        $db->commit();
                        $success = 'Registration successful! You can now login with your credentials.';
                        
                        // Clear form data on success
                        $_POST = [];
                        
                    } catch (Exception $e) {
                        $db->rollback();
                        throw $e;
                    }
                }
            } catch (PDOException $e) {
                $error = 'Registration failed. Please try again later.';
                error_log("Registration error: " . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card" style="max-width: 500px;">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Join <?php echo APP_NAME; ?> today</p>
            </div>
            
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
                            <i class="fas fa-id-card me-1"></i>Full Name
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="full_name" 
                               name="full_name" 
                               placeholder="Enter your full name"
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                               required>
                        <div class="invalid-feedback">
                            Please enter your full name.
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">
                            <i class="fas fa-user me-1"></i>Username
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
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1"></i>Email Address
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               placeholder="Enter your email"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               required>
                        <div class="invalid-feedback">
                            Please enter a valid email address.
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">
                            <i class="fas fa-users me-1"></i>Role
                        </label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Select your role</option>
                            <option value="student" <?php echo (($_POST['role'] ?? '') == 'student') ? 'selected' : ''; ?>>
                                Student
                            </option>
                            <option value="teacher" <?php echo (($_POST['role'] ?? '') == 'teacher') ? 'selected' : ''; ?>>
                                Teacher
                            </option>
                        </select>
                        <div class="invalid-feedback">
                            Please select your role.
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Password
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
                            <i class="fas fa-lock me-1"></i>Confirm Password
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
                
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" class="text-decoration-none">Terms and Conditions</a> 
                            and <a href="#" class="text-decoration-none">Privacy Policy</a>
                        </label>
                        <div class="invalid-feedback">
                            You must agree to the terms and conditions.
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
                
                <div class="text-center">
                    <p class="mb-0">
                        Already have an account? 
                        <a href="login.php" class="text-decoration-none fw-semibold">
                            Sign in here
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../assets/js/script.js"></script>
    
    <script>
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
        
        // Real-time password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('password-strength');
            
            if (strengthBar) {
                let strength = 0;
                
                if (password.length >= 6) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;
                
                const strengthLevels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
                const strengthColors = ['danger', 'warning', 'info', 'success', 'success'];
                
                strengthBar.className = `progress-bar bg-${strengthColors[strength - 1]}`;
                strengthBar.style.width = `${(strength / 5) * 100}%`;
                strengthBar.textContent = strengthLevels[strength - 1] || '';
            }
        });
    </script>
</body>
</html>