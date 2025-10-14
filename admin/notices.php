<?php
// --- Admin Dashboard ---
// Shows a summary of key data like total students, teachers, classes, and subjects.

require_once '../config.php';
requireRole('admin');

$pageTitle = 'Admin Dashboard';

// Initialize variables
$totalStudents = $totalTeachers = $totalClasses = $totalSubjects = 0;
$recentStudentsList = [];

// --- Fetch Dashboard Data ---
$totalStudents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM students"))['total'] ?? 0;
$totalTeachers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM teachers"))['total'] ?? 0;
$totalClasses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM classes"))['total'] ?? 0;
$totalSubjects = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM subjects"))['total'] ?? 0;

// --- Fetch Recently Added Students (last 5) ---
$query = "
    SELECT s.roll_no, s.full_name, s.class_id, s.created_at, c.class_name
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.class_id
    ORDER BY s.created_at DESC
    LIMIT 5
";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recentStudentsList[] = $row;
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                    <p class="text-muted mb-0">Here’s your quick overview of school statistics.</p>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="dashboard-card text-center">
                    <div class="fs-4 fw-bold text-primary"><?php echo $totalStudents; ?></div>
                    <div class="text-muted">Total Students</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card text-center">
                    <div class="fs-4 fw-bold text-success"><?php echo $totalTeachers; ?></div>
                    <div class="text-muted">Total Teachers</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card text-center">
                    <div class="fs-4 fw-bold text-warning"><?php echo $totalClasses; ?></div>
                    <div class="text-muted">Total Classes</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card text-center">
                    <div class="fs-4 fw-bold text-danger"><?php echo $totalSubjects; ?></div>
                    <div class="text-muted">Total Subjects</div>
                </div>
            </div>
        </div>

        <!-- Recently Added Students -->
        <div class="dashboard-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Recently Added Students</h5>
                <a href="manage-students.php" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye me-1"></i>View All
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Roll No</th>
                            <th>Full Name</th>
                            <th>Class</th>
                            <th>Added Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentStudentsList)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>No students found.
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($recentStudentsList as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['roll_no']); ?></td>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo htmlspecialchars($student['class_name'] ?? 'Not Assigned'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-card">
            <h5 class="mb-3">Quick Actions</h5>
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="add-student.php" class="btn btn-outline-primary w-100 p-3">
                        <i class="fas fa-user-plus fa-2x mb-2 d-block"></i>Add Student
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="add-teacher.php" class="btn btn-outline-success w-100 p-3">
                        <i class="fas fa-chalkboard-teacher fa-2x mb-2 d-block"></i>Add Teacher
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="notices.php" class="btn btn-outline-warning w-100 p-3">
                        <i class="fas fa-bullhorn fa-2x mb-2 d-block"></i>Post Notice
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="reports.php" class="btn btn-outline-info w-100 p-3">
                        <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>View Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>