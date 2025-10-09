<?php
// Include configuration and session helper
require_once '../config.php';

// Redirect if user already logged in
if (isLoggedIn()) {
    header("Location: ../{$_SESSION['role']}/dashboard.php");
    exit;
}

$error = '';
$success = '';

// Handle login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get and sanitize input
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Check user in database
        $query = "SELECT id, uname, upassword, role, email FROM users 
                  WHERE uname='$username' OR email='$username'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            // Verify password
            if (password_verify($password, $user['upassword'])) {
                // Store session info
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['uname'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['last_activity'] = time();

                session_regenerate_id(true);

                // Redirect to dashboard
                header("Location: ../{$user['role']}/dashboard.php");
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

// Logout and timeout alerts
if (isset($_GET['logout'])) {
    $success = 'You have been logged out successfully.';
}
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

<style>
/* ===== General Styling ===== */
body {
    background: url('../assets/img/logo/back.png') no-repeat center center fixed;
    background-size: cover;
    font-family: 'Poppins', sans-serif;
    color: #333;
}

/* Center container for auth card */
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
}

/* ===== Login Card Design ===== */
.auth-card {
    background: rgba(255, 255, 255, 0.96);
    border-radius: 18px;
    box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
    padding: 2.5rem 2rem;
    width: 100%;
    max-width: 400px;
}

/* ===== Header Section ===== */
.auth-header {
    text-align: center;
    margin-bottom: 1.8rem;
}
.auth-header h1 {
    font-size: 1.9rem;
    font-weight: 600;
    color: rgb(74 107 255 / 90%);
}
.auth-header p {
    color: #666;
    font-size: 0.95rem;
}

/* ===== Input Styling ===== */
.form-control:focus {
    border-color: rgb(74 107 255 / 90%);
    box-shadow: 0 0 0 0.2rem rgba(74, 107, 255, 0.25);
}

/* ===== Button Design ===== */
.btn-primary {
    background-color: rgb(74 107 255 / 90%);
    border: none;
    transition: all 0.3s ease;
}
.btn-primary:hover {
    background-color: rgba(74, 107, 255, 0.8);
}

/* ===== Link Styling ===== */
a {
    color: rgb(74 107 255 / 90%);
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}

/* ===== Modal Styling ===== */
.modal-content {
    border-radius: 12px;
}
.modal-header {
    background-color: rgb(74 107 255 / 90%);
    color: #fff;
}
.modal-footer .btn-secondary {
    background-color: rgb(74 107 255 / 70%);
    border: none;
}
.modal-footer .btn-secondary:hover {
    background-color: rgb(74 107 255 / 90%);
}
</style>
</head>

<body>
<div class="auth-container">
    <div class="auth-card">
        <!-- Header -->
        <div class="auth-header">
            <h1>Welcome Back</h1>
            <p>Login to your <?php echo APP_NAME; ?> account</p>
        </div>

        <!-- Alerts -->
        <?php if($error) echo showAlert($error,'danger'); ?>
        <?php if($success) echo showAlert($success,'success'); ?>

        <!-- Login Form -->
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username or Email</label>
                <input type="text" name="username" class="form-control"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">
                <i class="fas fa-sign-in-alt me-2"></i> Sign In
            </button>

            <div class="text-center">
                <p class="mb-2">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#forgotModal">Forgot your password?</a>
                </p>
                <p class="mb-0">
                    Don't have an account? <a href="register.php">Create one here</a>
                </p>
            </div>
        </form>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotModal" tabindex="-1" aria-labelledby="forgotModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forgotModalLabel">Forgot Password Help</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>If you are a <strong>student</strong> or <strong>teacher</strong>, contact your administrator to reset your password.<br>
                <small>Admins can change user passwords directly from their dashboard.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
