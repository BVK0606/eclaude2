<?php
// Core setup
require_once '../config.php';
requireRole('student');
$pageTitle = 'My Profile';

// Init variables
$student = [];

// --- Database logic ---
if (!empty($_SESSION['user_id']) && isset($conn)) {
    // Get student details
    $stmt = $conn->prepare("
        SELECT s.*, u.email, u.uname, c.class_name 
        FROM students s 
        JOIN users u ON s.user_id = u.id 
        LEFT JOIN classes c ON s.class_id = c.class_id 
        WHERE u.id = ?
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();
}

// Include layout
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="dashboard-card mb-4">
            <h2 class="card-title mb-4"><i class="fas fa-user me-2 text-primary"></i>My Profile Information</h2>
            
            <?php if ($student): ?>
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3 fw-bold text-secondary">Personal Details</h5>
                        <table class="table table-borderless table-sm">
                            <tr><th style="width: 35%;">Full Name</th><td><?php echo htmlspecialchars($student['full_name'] ?? ($student['uname'] ?? 'N/A')); ?></td></tr>
                            <tr><th>Roll No</th><td><?php echo htmlspecialchars($student['roll_no'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Date of Birth</th><td><?php echo $student['dob'] ? date('M d, Y', strtotime($student['dob'])) : 'N/A'; ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3 fw-bold text-secondary">Contact & Academic</h5>
                        <table class="table table-borderless table-sm">
                            <tr><th style="width: 35%;">Email</th><td><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Class</th><td><?php echo htmlspecialchars($student['class_name'] ?? 'Not assigned'); ?></td></tr>
                            <tr><th>Address</th><td><?php echo htmlspecialchars($student['address'] ?? 'N/A'); ?></td></tr>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Profile not found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>