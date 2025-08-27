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

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT id, uname, upassword, role, email FROM users WHERE uname = ? OR email = ?");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['upassword'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['uname'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['last_activity'] = time();
                    
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    // Redirect based on role
                    $redirectUrl = "../{$user['role']}/dashboard.php";
                    header("Location: $redirectUrl");
                    exit;
                } else {
                    $error = 'Invalid username or password.';
                }
            } catch (PDOException $e) {
                $error = 'Database error. Please try again later.';
                error_log("Login error: " . $e->getMessage());
            }
        }
    }
}

// Check for logout message
if (isset($_GET['logout'])) {
    $success = 'You have been logged out successfully.';
}

// Check for timeout message
if (isset($_GET['timeout'])) {
    $error = 'Your session has expired. Please login again.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to your <?php echo APP_NAME; ?> account</p>
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
                
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fas fa-user me-1"></i>Username or Email
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="username" 
                           name="username" 
                           placeholder="Enter your username or email"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           required>
                    <div class="invalid-feedback">
                        Please enter your username or email.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-1"></i>Password
                    </label>
                    <div class="position-relative">
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Enter your password"
                               required>
                        <button type="button" 
                                class="btn btn-link position-absolute top-50 end-0 translate-middle-y me-2 p-0 border-0 bg-transparent"
                                onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="password-toggle"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback">
                        Please enter your password.
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
                
                <div class="text-center">
                    <p class="mb-2">
                        <a href="forgot-password.php" class="text-decoration-none">
                            Forgot your password?
                        </a>
                    </p>
                    <p class="mb-0">
                        Don't have an account? 
                        <a href="register.php" class="text-decoration-none fw-semibold">
                            Create one here
                        </a>
                    </p>
                </div>
            </form>
            
            <!-- Demo Credentials -->
            <div class="mt-4 p-3 bg-light rounded">
                <h6 class="mb-2">Demo Credentials:</h6>
                <div class="small">
                    <div class="mb-1">
                        <strong>Admin:</strong> admin / password
                    </div>
                    <div class="mb-1">
                        <strong>Teacher:</strong> teacher / password
                    </div>
                    <div>
                        <strong>Student:</strong> student / password
                    </div>
                </div>
            </div>
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
        
        // Auto-fill demo credentials
        document.addEventListener('DOMContentLoaded', function() {
            const demoButtons = document.querySelectorAll('[data-demo]');
            demoButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const [username, password] = this.dataset.demo.split(':');
                    document.getElementById('username').value = username;
                    document.getElementById('password').value = password;
                });
            });
        });
    </script>
</body>
</html>