<?php
require_once '../config.php';
requireRole('admin'); // Only admin can access

// If no teacher ID is provided, redirect
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage-teachers.php');
    exit;
}

$teacherId = $_GET['id'];
$error = '';
$success = '';

// Get teacher details
$sql = "SELECT t.teacher_id, t.full_name, t.qualification, t.experience,
               u.email, u.uname AS username
        FROM teachers t
        LEFT JOIN users u ON t.user_id = u.id
        WHERE t.teacher_id = '$teacherId'";
$result = mysqli_query($conn, $sql);
$teacher = mysqli_fetch_assoc($result);

if (!$teacher) {
    $_SESSION['error'] = 'Teacher not found.';
    header('Location: manage-teachers.php');
    exit;
}

// When form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize form input
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $qualification = sanitizeInput($_POST['qualification'] ?? '');
    $experience = sanitizeInput($_POST['experience'] ?? '');

    // Basic validation
    if (empty($fullName) || empty($email)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if email already exists (excluding this teacher)
        $checkEmail = "
            SELECT u.id FROM users u
            JOIN teachers t ON u.id = t.user_id
            WHERE u.email = '$email' AND t.teacher_id != '$teacherId'
        ";
        $emailResult = mysqli_query($conn, $checkEmail);

        if (mysqli_num_rows($emailResult) > 0) {
            $error = "Email already exists.";
        } else {
            // Update teacher details
            $updateTeacher = "
                UPDATE teachers 
                SET full_name = '$fullName',
                    qualification = '$qualification',
                    experience = '$experience'
                WHERE teacher_id = '$teacherId'
            ";

            // Update user email
            $updateUser = "
                UPDATE users u
                JOIN teachers t ON u.id = t.user_id
                SET u.email = '$email'
                WHERE t.teacher_id = '$teacherId'
            ";

            if (mysqli_query($conn, $updateTeacher) && mysqli_query($conn, $updateUser)) {
                $success = "Teacher updated successfully!";

                // Refresh teacher data
                $result = mysqli_query($conn, $sql);
                $teacher = mysqli_fetch_assoc($result);
            } else {
                $error = "Failed to update teacher. Please try again.";
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
                    <h2>Edit Teacher</h2>
                    <p>Update teacher information below.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Full Name *</label>
                                <input type="text" name="full_name" class="form-control"
                                       value="<?php echo htmlspecialchars($teacher['full_name']); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Username</label>
                                <input type="text" class="form-control"
                                       value="<?php echo htmlspecialchars($teacher['username']); ?>" disabled>
                                <div class="form-text">Username cannot be changed.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Email *</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Qualification</label>
                                <input type="text" name="qualification" class="form-control"
                                       value="<?php echo htmlspecialchars($teacher['qualification']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Experience (years)</label>
                                <input type="number" name="experience" class="form-control"
                                       min="0"
                                       value="<?php echo htmlspecialchars($teacher['experience']); ?>">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="manage-teachers.php" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-primary">Update Teacher</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>