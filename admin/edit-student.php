<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Edit Student';

// Check if student ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage-students.php');
    exit;
}
$studentId = (int)$_GET['id'];

// Get student data
$student = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT s.student_id, s.roll_no, s.full_name, s.dob, s.address, s.class_id,
           u.email, u.uname as username
    FROM students s
    LEFT JOIN users u ON s.user_id = u.id
    WHERE s.student_id = $studentId
"));
if (!$student) {
    $_SESSION['error'] = 'Student not found.';
    header('Location: manage-students.php');
    exit;
}

// Get classes
$classesResult = mysqli_query($conn, "SELECT class_id, class_name FROM classes ORDER BY class_name");
$classes = [];
while ($row = mysqli_fetch_assoc($classesResult)) $classes[] = $row;

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rollNo = sanitizeInput($_POST['roll_no'] ?? '');
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $classId = sanitizeInput($_POST['class_id'] ?? '');
    $dob = sanitizeInput($_POST['dob'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');

    // Validate required fields
    if (empty($rollNo) || empty($fullName) || empty($email) || empty($classId)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if roll_no exists (excluding current)
        $checkRoll = mysqli_query($conn, "SELECT student_id FROM students WHERE roll_no='$rollNo' AND student_id != $studentId");
        if (mysqli_num_rows($checkRoll) > 0) {
            $error = 'Roll number already exists.';
        } else {
            // Check if email exists (excluding current)
            $checkEmail = mysqli_query($conn, "
                SELECT u.id FROM users u
                JOIN students s ON u.id = s.user_id
                WHERE u.email='$email' AND s.student_id != $studentId
            ");
            if (mysqli_num_rows($checkEmail) > 0) {
                $error = 'Email already exists.';
            } else {
                // Update student
                $updateStudent = mysqli_query($conn, "
                    UPDATE students SET roll_no='$rollNo', full_name='$fullName', class_id='$classId', dob='$dob', address='$address'
                    WHERE student_id = $studentId
                ");
                // Update user email
                $updateUser = mysqli_query($conn, "
                    UPDATE users u
                    JOIN students s ON u.id = s.user_id
                    SET u.email='$email'
                    WHERE s.student_id = $studentId
                ");
                if ($updateStudent && $updateUser) {
                    $success = 'Student updated successfully!';
                    // Refresh student data
                    $student = mysqli_fetch_assoc(mysqli_query($conn, "
                        SELECT s.student_id, s.roll_no, s.full_name, s.dob, s.address, s.class_id,
                               u.email, u.uname as username
                        FROM students s
                        LEFT JOIN users u ON s.user_id = u.id
                        WHERE s.student_id = $studentId
                    "));
                } else {
                    $error = 'Failed to update student. Please try again.';
                }
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
                    <h2>Edit Student</h2>
                    <p class="text-muted">Update student information.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Full Name *</label>
                                <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Roll Number *</label>
                                <input type="text" class="form-control" name="roll_no" value="<?php echo htmlspecialchars($student['roll_no']); ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Username</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['username']); ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email *</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Class *</label>
                                <select class="form-select" name="class_id" required>
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['class_id']; ?>" <?php echo ($student['class_id'] == $class['class_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($class['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Date of Birth</label>
                                <input type="date" class="form-control" name="dob" value="<?php echo htmlspecialchars($student['dob']); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Address</label>
                            <textarea class="form-control" name="address"><?php echo htmlspecialchars($student['address']); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="manage-students.php" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-primary">Update Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
