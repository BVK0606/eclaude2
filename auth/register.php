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

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // CSRF protection
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        // Sanitize form input
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = sanitizeInput($_POST['role'] ?? '');

        // Validate input
        if (empty($fullName) || empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($role)) {
            $error = 'Please fill in all required fields.';
        } elseif (strlen($username) < 3) {
            $error = 'Username must be at least 3 characters.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (!in_array($role, ['student','teacher'])) {
            $error = 'Please select a valid role.';
        } else {
            // Check if username or email exists
            $stmt = mysqli_query($conn, "SELECT * FROM users WHERE uname='$username' OR email='$email'");
            if (mysqli_num_rows($stmt) > 0) {
                $error = 'Username or email already exists.';
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert user
                $sql = "INSERT INTO users (uname, upassword, role, email) VALUES ('$username','$hashedPassword','$role','$email')";
                if (mysqli_query($conn, $sql)) {
                    $userId = mysqli_insert_id($conn);

                    // Role-specific insertion
                    if ($role == 'student') {
                        $classResult = mysqli_query($conn, "SELECT class_id FROM classes ORDER BY RAND() LIMIT 1");
                        $classId = mysqli_fetch_assoc($classResult)['class_id'];
                        $rollNo = 'S'.date('Y').str_pad($userId,4,'0',STR_PAD_LEFT);
                        mysqli_query($conn, "INSERT INTO students (user_id, class_id, roll_no, dob, address) VALUES ('$userId','$classId','$rollNo','','')");
                    } else {
                        mysqli_query($conn, "INSERT INTO teachers (user_id, qualification, experience) VALUES ('$userId','','0')");
                    }

                    $success = 'Registration successful! You can now login.';
                    $_POST = []; // clear form
                } else {
                    $error = 'Registration failed. Please try again.';
                }
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

<style>
/* ===== Background Styling ===== */
body {
    background: url('../assets/img/logo/back.png') no-repeat center center fixed;
    background-size: cover;
    font-family: 'Poppins', sans-serif;
    color: #333;
}

/* Center container */
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
}

/* Card Styling */
.auth-card {
    background: rgba(255,255,255,0.96);
    border-radius: 18px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.15);
    padding: 2.5rem 2rem;
    width: 100%;
    max-width: 450px;
}

/* Header */
.auth-card h2 {
    color: rgb(74 107 255 / 90%);
    font-weight: 600;
    margin-bottom: 1.5rem;
    text-align: center;
}

/* Input Focus */
.form-control:focus, .form-select:focus {
    border-color: rgb(74 107 255 / 90%);
    box-shadow: 0 0 0 0.2rem rgba(74,107,255,0.25);
}

/* Button Styling */
.btn-primary {
    background-color: rgb(74 107 255 / 90%);
    border: none;
    transition: all 0.3s ease;
}
.btn-primary:hover {
    background-color: rgba(74, 107, 255, 0.8);
}

/* Links */
a {
    color: rgb(74 107 255 / 90%);
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>
</head>

<body>
<div class="auth-container">
    <div class="auth-card">
        <h2>Create Account</h2>

        <!-- Alerts -->
        <?php if($error) echo showAlert($error,'danger'); ?>
        <?php if($success) echo showAlert($success,'success'); ?>

        <!-- Registration Form -->
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-select" required>
                    <option value="">Select Role</option>
                    <option value="student" <?php if(($_POST['role']??'')=='student') echo 'selected'; ?>>Student</option>
                    <option value="teacher" <?php if(($_POST['role']??'')=='teacher') echo 'selected'; ?>>Teacher</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Register</button>
            <p class="mt-2 text-center">Already have an account? <a href="login.php">Login</a></p>
        </form>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
