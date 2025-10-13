<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Reports';

// Initialize variables
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

// --- Attendance Statistics ---
$result = mysqli_query($conn, "
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present,
        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absent
    FROM attendance
");
$attendanceStats = mysqli_fetch_assoc($result);

// --- Recent Notices ---
$result = mysqli_query($conn, "SELECT * FROM notices ORDER BY created_at DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($result)) {
    $recentNotices[] = $row;
}

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

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">

        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2>Reports & Analytics</h2>
                    <p class="text-muted">View system statistics and generate reports.</p>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="dashboard-card text-center">
                    <div class="card-value"><?php echo $totalStudents; ?></div>
                    <div class="card-title">Students</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card text-center">
                    <div class="card-value"><?php echo $totalTeachers; ?></div>
                    <div class="card-title">Teachers</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card text-center">
                    <div class="card-value"><?php echo $totalClasses; ?></div>
                    <div class="card-title">Classes</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card text-center">
                    <?php 
                    $attendanceRate = $attendanceStats['total'] > 0 
                        ? round(($attendanceStats['present'] / $attendanceStats['total']) * 100, 1) 
                        : 0; 
                    ?>
                    <div class="card-value"><?php echo $attendanceRate; ?>%</div>
                    <div class="card-title">Attendance Rate</div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="dashboard-card">
                    <h5>Students by Class</h5>
                    <canvas id="classChart" height="250"></canvas>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="dashboard-card">
                    <h5>Attendance Overview</h5>
                    <canvas id="attendanceChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Class Stats Table -->
        <div class="dashboard-card mb-4">
            <h5>Class-wise Statistics</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Class Name</th>
                            <th>Students</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($classStats)): ?>
                            <tr><td colspan="3" class="text-center text-muted">No data found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($classStats as $c): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($c['class_name']); ?></td>
                                    <td><?php echo $c['student_count']; ?></td>
                                    <td>
                                        <a href="class-report.php?id=<?php echo $c['class_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-chart-bar"></i> View
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Recent Notices</h5>
                <a href="notices.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <?php if (empty($recentNotices)): ?>
                <p class="text-center text-muted">No notices found.</p>
            <?php else: ?>
                <?php foreach ($recentNotices as $n): ?>
                    <div class="mb-2">
                        <h6><?php echo htmlspecialchars($n['title']); ?></h6>
                        <p class="small"><?php echo nl2br(htmlspecialchars(substr($n['description'], 0, 120))) . '...'; ?></p>
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> <?php echo date('M d, Y', strtotime($n['created_at'])); ?>
                        </small>
                        <hr>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Export Buttons -->
        <div class="dashboard-card">
            <h5>Export Reports</h5>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <a href="export/export-students.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-file-export me-1"></i>Students
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <a href="export/export-teachers.php" class="btn btn-outline-success w-100">
                        <i class="fas fa-file-export me-1"></i>Teachers
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <a href="export/export-attendance.php" class="btn btn-outline-warning w-100">
                        <i class="fas fa-file-export me-1"></i>Attendance
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <a href="export/export-marks.php" class="btn btn-outline-info w-100">
                        <i class="fas fa-file-export me-1"></i>Marks
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ====== Charts Script ====== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const classChart = new Chart(document.getElementById('classChart'), {
    type: 'bar',
    data: {
        labels: [<?php foreach($classStats as $c){echo "'".$c['class_name']."',";} ?>],
        datasets: [{
            label: 'Students',
            data: [<?php foreach($classStats as $c){echo $c['student_count'].",";} ?>],
            backgroundColor: 'rgba(74,107,255,0.7)'
        }]
    }
});

const attendanceChart = new Chart(document.getElementById('attendanceChart'), {
    type: 'doughnut',
    data: {
        labels: ['Present', 'Absent'],
        datasets: [{
            data: [<?php echo $attendanceStats['present'] ?? 0; ?>, <?php echo $attendanceStats['absent'] ?? 0; ?>],
            backgroundColor: ['#4a6bff', '#dc3545']
        }]
    }
});
</script>

<?php include '../includes/footer.php'; ?>

//last report page.