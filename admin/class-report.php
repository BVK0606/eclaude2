<?php
// --- Class Report Page ---
// This page shows the list of students in a selected class.

require_once '../config.php';
requireRole('admin');

$pageTitle = 'Class Report';

// Get class ID from URL
$classId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($classId <= 0) {
    header('Location: reports.php');
    exit;
}

// Fetch class name
$classQuery = mysqli_query($conn, "SELECT class_name FROM classes WHERE class_id = '$classId'");
$class = mysqli_fetch_assoc($classQuery);
if (!$class) {
    header('Location: reports.php');
    exit;
}

// Fetch students in this class
$students = [];
$result = mysqli_query($conn, "SELECT roll_no, full_name FROM students WHERE class_id = '$classId' ORDER BY roll_no");
while ($row = mysqli_fetch_assoc($result)) {
    $students[] = $row;
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <div class="dashboard-card">
            <h2>Class Report: <?php echo htmlspecialchars($class['class_name']); ?></h2>
            <p class="text-muted mb-3">List of all students in this class.</p>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr><td colspan="2" class="text-center text-muted">No students in this class.</td></tr>
                        <?php else: ?>
                            <?php foreach ($students as $s): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($s['roll_no']); ?></td>
                                    <td><?php echo htmlspecialchars($s['full_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <a href="reports.php" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left me-2"></i>Back to Reports</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
