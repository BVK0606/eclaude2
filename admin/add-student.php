<?php
require_once '../config.php';
requireRole('admin'); // Only admin can access

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form input and sanitize
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $rollNo = sanitizeInput($_POST['roll_no'] ?? '');
    $classId = sanitizeInput($_POST['class_id'] ?? '');
    $dob = sanitizeInput($_POST['dob'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');

    // Validate input
    if (empty($fullName) || empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($rollNo) || empty($classId)) {
        $error = "Please fill in all required fields.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        // Check if username or email exists
        $checkUser = mysqli_query($conn, "SELECT * FROM users WHERE uname='$username' OR email='$email'");
        $checkRoll = mysqli_query($conn, "SELECT * FROM students WHERE roll_no='$rollNo'");

        if (mysqli_num_rows($checkUser) > 0) {
            $error = "Username or email already exists.";
        } elseif (mysqli_num_rows($checkRoll) > 0) {
            $error = "Roll number already exists.";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert into users table
            $sqlUser = "INSERT INTO users (uname, upassword, role, email) VALUES ('$username','$hashedPassword','student','$email')";
            if (mysqli_query($conn, $sqlUser)) {
                $userId = mysqli_insert_id($conn);

                // Insert into students table
                $sqlStudent = "INSERT INTO students (user_id, class_id, roll_no, full_name, dob, address) 
                               VALUES ('$userId','$classId','$rollNo','$fullName','$dob','$address')";
                mysqli_query($conn, $sqlStudent);

                $success = "Student added successfully!";
                $_POST = []; // Clear form data
            } else {
                $error = "Failed to add student. Please try again.";
            }
        }
    }
}

// Fetch classes for dropdown
$classResult = mysqli_query($conn, "SELECT class_id, class_name FROM classes ORDER BY class_name");
$classes = mysqli_fetch_all($classResult, MYSQLI_ASSOC);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2>Add New Student</h2>
                    <p>Fill in the details to register a new student.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <?php if ($error)
                        echo showAlert($error, 'danger'); ?>
                    <?php if ($success)
                        echo showAlert($success, 'success'); ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Full Name *</label>
                                <input type="text" name="full_name" class="form-control"
                                    value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Roll Number *</label>
                                <input type="text" name="roll_no" class="form-control"
                                    value="<?php echo htmlspecialchars($_POST['roll_no'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Username *</label>
                                <input type="text" name="username" class="form-control"
                                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email *</label>
                                <input type="email" name="email" class="form-control"
                                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Password *</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Confirm Password *</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Class *</label>
                                <select name="class_id" class="form-select" required>
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['class_id']; ?>" <?php if (($_POST['class_id'] ?? '') == $class['class_id'])
                                               echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($class['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Date of Birth</label>
                                <input type="date" name="dob" class="form-control"
                                    value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Address</label>
                            <textarea name="address" class="form-control"
                                rows="3"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="manage-students.php" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-primary">Add Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>