<?php
// --- Reports Page ---
// This page shows basic system statistics and reports.

require_once '../config.php';
requireRole('admin');

$pageTitle = 'Reports';

// --- Initialize Counters ---
$totalStudents = $totalTeachers = $totalClasses = 0;
$attendanceStats = ['total' => 0, 'present' => 0, 'absent' => 0];
$recentNotices = [];
$classStats = [];

// --- Total Students ---
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM students");
$row = mysqli_fetch_assoc($result);
$totalStudents = $row['total'] ?? 0;

// --- Total Teachers ---
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM teachers");
$row = mysqli_fetch_assoc($result);
$totalTeachers = $row['total'] ?? 0;

// --- Total Classes ---
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM classes");
$row = mysqli_fetch_assoc($result);
$totalClasses = $row['total'] ?? 0;

// --- Attendance Summary ---
$result = mysqli_query($conn, "
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present,
        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absent
    FROM attendance
");
$attendanceStats = mysqli_fetch_assoc($result);
$attendanceRate = ($attendanceStats['total'] > 0)
    ? round(($attendanceStats['present'] / $attendanceStats['total']) * 100, 1)
    : 0;

// --- Class-wise Student Count ---
$result = mysqli_query($conn, "
    SELECT c.class_id, c.class_name, COUNT(s.student_id) AS student_count
    FROM classes c
    LEFT JOIN students s ON c.class_id = s.class_id
    GROUP BY c.class_id, c.class_name
    ORDER BY c.class_name
");
while ($row = mysqli_fetch_assoc($result)) {
    $classStats[] = $row;
}

// --- Recent Notices ---
$result = mysqli_query($conn, "SELECT * FROM notices ORDER BY created_at DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($result)) {
    $recentNotices[] = $row;
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- ====== Reports Page ====== -->
<div class="main-content">
    <div class="content">

        <div class="dashboard-card mb-4">
            <h2>Reports & Analytics</h2>
            <p class="text-muted">View important system data and quick reports.</p>
        </div>

        <!-- Summary Boxes -->
        <div class="row mb-4 text-center">
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <h3><?php echo $totalStudents; ?></h3>
                    <p class="text-muted mb-0">Students</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <h3><?php echo $totalTeachers; ?></h3>
                    <p class="text-muted mb-0">Teachers</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <h3><?php echo $totalClasses; ?></h3>
                    <p class="text-muted mb-0">Classes</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <h3><?php echo $attendanceRate; ?>%</h3>
                    <p class="text-muted mb-0">Attendance Rate</p>
                </div>
            </div>
        </div>

        <!-- Class Stats Table -->
        <div class="dashboard-card mb-4">
            <h4>Class-wise Student Statistics</h4>
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Class Name</th>
                            <th>Total Students</th>
                            <th>View Report</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($classStats)): ?>
                            <tr><td colspan="3" class="text-center text-muted">No class data found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($classStats as $c): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($c['class_name']); ?></td>
                                    <td><?php echo $c['student_count']; ?></td>
                                    <td>
                                        <a href="class-report.php?id=<?php echo $c['class_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Notices -->
        <div class="dashboard-card mb-4">
            <h4>Recent Notices</h4>
            <?php if (empty($recentNotices)): ?>
                <p class="text-center text-muted my-3">No recent notices found.</p>
            <?php else: ?>
                <?php foreach ($recentNotices as $n): ?>
                    <div class="mb-2">
                        <h6><?php echo htmlspecialchars($n['title']); ?></h6>
                        <p class="small mb-1"><?php echo nl2br(htmlspecialchars(substr($n['description'], 0, 100))) . '...'; ?></p>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i> <?php echo date('M d, Y', strtotime($n['created_at'])); ?>
                        </small>
                        <hr>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Export Buttons -->
        <div class="dashboard-card">
            <h4>Export Reports</h4>
            <div class="row mt-3">
                <div class="col-md-3 mb-2">
                    <a href="export/export-students.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-file-export me-1"></i> Students
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <a href="export/export-teachers.php" class="btn btn-outline-success w-100">
                        <i class="fas fa-file-export me-1"></i> Teachers
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <a href="export/export-attendance.php" class="btn btn-outline-warning w-100">
                        <i class="fas fa-file-export me-1"></i> Attendance
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <a href="export/export-marks.php" class="btn btn-outline-info w-100">
                        <i class="fas fa-file-export me-1"></i> Marks
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>