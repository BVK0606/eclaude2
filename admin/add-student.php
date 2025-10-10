<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Add Student';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
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

        if (
            empty($username) || empty($email) || empty($password) || empty($confirmPassword) ||
            empty($rollNo) || empty($fullName) || empty($classId)
        ) {
            $error = 'Please fill in all required fields.';
        } elseif (strlen($username) < 3 || strlen($username) > 20) {
            $error = 'Username must be 3-20 characters long.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 50) {
            $error = 'Please enter a valid email (max 50 characters).';
        } elseif (strlen($password) < 6 || strlen($password) > 20) {
            $error = 'Password must be 6-20 characters long.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (!preg_match('/^[0-9]{1,2}$/', $rollNo)) {
            $error = 'Roll number must be numbers only (1–2 digits).';
        } elseif (!preg_match('/^[a-zA-Z\s]{1,50}$/', $fullName)) {
            $error = 'Full name can contain only letters and spaces (max 50 characters).';
        } elseif ($classId <= 0) {
            $error = 'Please select a valid class.';
        } elseif (!empty($address) && strlen($address) > 200) {
            $error = 'Address cannot exceed 200 characters.';
        } elseif (!empty($dob) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
            $error = 'Invalid date format.';
        } else {
            try {
                $db = Database::getInstance()->getConnection();

                $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE uname = ? OR email = ?");
                $stmt->execute([$username, $email]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'Username or email already exists.';
                } else {
                    $stmt = $db->prepare("SELECT COUNT(*) FROM students WHERE roll_no = ?");
                    $stmt->execute([$rollNo]);
                    if ($stmt->fetchColumn() > 0) {
                        $error = 'Roll number already exists.';
                    } else {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $db->beginTransaction();
                        $stmt = $db->prepare("INSERT INTO users (uname, upassword, role, email) VALUES (?, ?, 'student', ?)");
                        $stmt->execute([$username, $hashedPassword, $email]);
                        $userId = $db->lastInsertId();
                        $stmt = $db->prepare("INSERT INTO students (user_id, class_id, roll_no, full_name, dob, address) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$userId, $classId, $rollNo, $fullName, $dob, $address]);
                        $db->commit();
                        $success = 'Student added successfully!';
                        $_POST = [];
                    }
                }
            } catch (Exception $e) {
                $db->rollback();
                $error = 'Failed to add student. Try again.';
                error_log($e->getMessage());
            }
        }
    }
}

try {
    $db = Database::getInstance()->getConnection();
    $classes = $db->query("SELECT class_id, class_name FROM classes ORDER BY class_name")->fetchAll();
} catch (PDOException $e) {
    $classes = [];
    error_log($e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2>Add New Student</h2>
                    <p class="text-muted">Fill in details to register a new student.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show"><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="full_name"
                                    value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Enter full name.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Roll Number *</label>
                                <input type="text" class="form-control" name="roll_no"
                                    value="<?php echo htmlspecialchars($_POST['roll_no'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Enter roll number.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username *</label>
                                <input type="text" class="form-control" name="username"
                                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" minlength="3"
                                    required>
                                <div class="invalid-feedback">At least 3 characters.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email"
                                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Enter valid email.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" minlength="6"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="confirm_password"
                                    name="confirm_password" minlength="6" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Class *</label>
                                <select class="form-select" name="class_id" required>
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['class_id']; ?>" <?php echo (($_POST['class_id'] ?? '') == $class['class_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($class['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" name="dob"
                                    value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address"
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

<?php
$pageScripts = "
document.getElementById('confirm_password').addEventListener('input', function() {
    this.setCustomValidity(this.value !== document.getElementById('password').value ? 'Passwords do not match' : '');
});
";
include '../includes/footer.php';
?>