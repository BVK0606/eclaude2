<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Edit Teacher';

// Check if teacher ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage-teachers.php');
    exit;
}

$teacherId = (int)$_GET['id'];

// Get teacher data
$teacher = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT t.teacher_id, t.full_name, t.qualification, t.experience,
           u.email, u.uname AS username
    FROM teachers t
    LEFT JOIN users u ON t.user_id = u.id
    WHERE t.teacher_id = $teacherId
"));

if (!$teacher) {
    $_SESSION['error'] = 'Teacher not found.';
    header('Location: manage-teachers.php');
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $qualification = sanitizeInput($_POST['qualification'] ?? '');
    $experience = sanitizeInput($_POST['experience'] ?? '');

    if (empty($fullName) || empty($email)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email already exists (excluding current teacher)
        $checkEmail = mysqli_query($conn, "
            SELECT u.id FROM users u
            JOIN teachers t ON u.id = t.user_id
            WHERE u.email = '$email' AND t.teacher_id != $teacherId
        ");
        if (mysqli_num_rows($checkEmail) > 0) {
            $error = 'Email already exists.';
        } else {
            // Update teacher info
            $updateTeacher = mysqli_query($conn, "
                UPDATE teachers 
                SET full_name = '$fullName', qualification = '$qualification', experience = '$experience'
                WHERE teacher_id = $teacherId
            ");

            // Update user email
            $updateUser = mysqli_query($conn, "
                UPDATE users u
                JOIN teachers t ON u.id = t.user_id
                SET u.email = '$email'
                WHERE t.teacher_id = $teacherId
            ");

            if ($updateTeacher && $updateUser) {
                $success = 'Teacher updated successfully!';
                // Refresh teacher data
                $teacher = mysqli_fetch_assoc(mysqli_query($conn, "
                    SELECT t.teacher_id, t.full_name, t.qualification, t.experience,
                           u.email, u.uname AS username
                    FROM teachers t
                    LEFT JOIN users u ON t.user_id = u.id
                    WHERE t.teacher_id = $teacherId
                "));
            } else {
                $error = 'Failed to update teacher. Please try again.';
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
                    <p class="text-muted">Update teacher information.</p>
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
                                <input type="text" class="form-control" name="full_name" 
                                       value="<?php echo htmlspecialchars($teacher['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email *</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Qualification</label>
                                <input type="text" class="form-control" name="qualification" 
                                       value="<?php echo htmlspecialchars($teacher['qualification']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Experience (years)</label>
                                <input type="number" class="form-control" name="experience" min="0"
                                       value="<?php echo htmlspecialchars($teacher['experience']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Username</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo htmlspecialchars($teacher['username']); ?>" disabled>
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