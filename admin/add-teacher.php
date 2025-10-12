<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Add Teacher';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $qualification = sanitizeInput($_POST['qualification'] ?? '');
    $experience = sanitizeInput($_POST['experience'] ?? '');

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($fullName)) {
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
        // Simple insert without hashing
        $stmt = mysqli_query($conn, "SELECT * FROM users WHERE uname='$username' OR email='$email'");
        if (mysqli_num_rows($stmt) > 0) {
            $error = 'Username or email already exists.';
        } else {
            $sql = "INSERT INTO users (uname, upassword, role, email) VALUES ('$username','$password','teacher','$email')";
            if (mysqli_query($conn, $sql)) {
                $userId = mysqli_insert_id($conn);
                mysqli_query($conn, "INSERT INTO teachers (user_id, full_name, qualification, experience) VALUES ('$userId','$fullName','$qualification','$experience')");
                $success = 'Teacher added successfully!';
                $_POST = []; // clear form
            } else {
                $error = 'Failed to add teacher. Please try again.';
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
                    <h2 class="mb-2">Add New Teacher</h2>
                    <p class="text-muted mb-0">Fill in the details to register a new teacher.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <?php if ($error) echo showAlert($error,'danger'); ?>
                    <?php if ($success) echo showAlert($success,'success'); ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label>Full Name *</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label>Username *</label>
                            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label>Email *</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label>Password *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Confirm Password *</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Qualification</label>
                            <input type="text" name="qualification" class="form-control" value="<?php echo htmlspecialchars($_POST['qualification'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label>Experience (years)</label>
                            <input type="number" name="experience" class="form-control" value="<?php echo htmlspecialchars($_POST['experience'] ?? ''); ?>">
                        </div>

                        <button type="submit" class="btn btn-primary">Add Teacher</button>
                        <a href="manage-teachers.php" class="btn btn-secondary">Back</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
