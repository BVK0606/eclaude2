<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Reports';

// Get statistics for reports
try {
    $db = Database::getInstance()->getConnection();
    
    // Total students
    $stmt = $db->query("SELECT COUNT(*) as total FROM students");
    $totalStudents = $stmt->fetch()['total'];
    
    // Total teachers
    $stmt = $db->query("SELECT COUNT(*) as total FROM teachers");
    $totalTeachers = $stmt->fetch()['total'];
    
    // Total classes
    $stmt = $db->query("SELECT COUNT(*) as total FROM classes");
    $totalClasses = $stmt->fetch()['total'];
    
    // Attendance statistics
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent
        FROM attendance
    ");
    $attendanceStats = $stmt->fetch();
    
    // Recent notices
    $stmt = $db->query("SELECT * FROM notices ORDER BY created_at DESC LIMIT 5");
    $recentNotices = $stmt->fetchAll();
    
    // Class-wise student count
    $stmt = $db->query("
        SELECT c.class_name, COUNT(s.student_id) as student_count
        FROM classes c
        LEFT JOIN students s ON c.class_id = s.class_id
        GROUP BY c.class_id
        ORDER BY c.class_name
    ");
    $classStats = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Reports error: " . $e->getMessage());
    $totalStudents = $totalTeachers = $totalClasses = 0;
    $attendanceStats = ['total' => 0, 'present' => 0, 'absent' => 0];
    $recentNotices = [];
    $classStats = [];
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="mb-2">Reports & Analytics</h2>
                    <p class="text-muted mb-0">View system statistics and generate reports.</p>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="dashboard-card text-center">
                    <div class="card-value"><?php echo number_format($totalStudents); ?></div>
                    <div class="card-title">Total Students</div>
                    <div class="small text-muted">Across all classes</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="dashboard-card text-center">
                    <div class="card-value"><?php echo number_format($totalTeachers); ?></div>
                    <div class="card-title">Total Teachers</div>
                    <div class="small text-muted">Teaching staff</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="dashboard-card text-center">
                    <div class="card-value"><?php echo number_format($totalClasses); ?></div>
                    <div class="card-title">Total Classes</div>
                    <div class="small text-muted">Active classes</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="dashboard-card text-center">
                    <?php if ($attendanceStats['total'] > 0): ?>
                        <div class="card-value"><?php echo number_format(($attendanceStats['present'] / $attendanceStats['total']) * 100, 1); ?>%</div>
                    <?php else: ?>
                        <div class="card-value">0%</div>
                    <?php endif; ?>
                    <div class="card-title">Attendance Rate</div>
                    <div class="small text-muted">Overall attendance</div>
                </div>
            </div>
        </div>
        
        <!-- Charts and Reports -->
        <div class="row">
            <!-- Class Distribution -->
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="dashboard-card">
                    <h5 class="card-title mb-3">Student Distribution by Class</h5>
                    <div style="position: relative; height: 300px;">
                        <canvas id="classDistributionChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Overview -->
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="dashboard-card">
                    <h5 class="card-title mb-3">Attendance Overview</h5>
                    <div style="position: relative; height: 300px;">
                        <canvas id="attendanceOverviewChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Class Statistics -->
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <h5 class="card-title mb-3">Class-wise Statistics</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Class Name</th>
                                    <th>Number of Students</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($classStats)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            <i class="fas fa-school fa-2x mb-2 d-block"></i>
                                            No class data available.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($classStats as $class): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-semibold"><?php echo htmlspecialchars($class['class_name']); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $class['student_count']; ?> students</span>
                                            </td>
                                            <td>
                                                <a href="class-report.php?id=<?php echo $class['class_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-chart-bar me-1"></i>View Report
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Notices -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title mb-0">Recent Notices</h5>
                        <a href="notices.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    
                    <?php if (empty($recentNotices)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-bullhorn fa-2x mb-2 d-block"></i>
                            <p>No notices yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="notices-list">
                            <?php foreach ($recentNotices as $notice): ?>
                                <div class="notice-item mb-3">
                                    <h6 class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></h6>
                                    <p class="notice-description small"><?php echo nl2br(htmlspecialchars(substr($notice['description'], 0, 150) . (strlen($notice['description']) > 150 ? '...' : ''))); ?></p>
                                    <div class="notice-meta text-muted small">
                                        <i class="fas fa-clock me-1"></i>
                                        Posted <?php echo date('M d, Y', strtotime($notice['created_at'])); ?>
                                    </div>
                                </div>
                                <?php if ($notice !== end($recentNotices)): ?>
                                    <hr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Export Options -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h5 class="card-title mb-3">Export Reports</h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="export-students.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-file-export me-2"></i>Export Students
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="export-teachers.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-file-export me-2"></i>Export Teachers
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="export-attendance.php" class="btn btn-outline-warning w-100">
                                <i class="fas fa-file-export me-2"></i>Export Attendance
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="export-marks.php" class="btn btn-outline-info w-100">
                                <i class="fas fa-file-export me-2"></i>Export Marks
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$pageScripts = "
    // Initialize Charts
    document.addEventListener('DOMContentLoaded', function() {
        // Class Distribution Chart
        const classCtx = document.getElementById('classDistributionChart').getContext('2d');
        new Chart(classCtx, {
            type: 'bar',
            data: {
                labels: [" . implode(',', array_map(function($class) { return "'" . htmlspecialchars($class['class_name']) . "'"; }, $classStats)) . "],
                datasets: [{
                    label: 'Number of Students',
                    data: [" . implode(',', array_column($classStats, 'student_count')) . "],
                    backgroundColor: 'rgba(74, 107, 255, 0.7)',
                    borderColor: 'rgb(74, 107, 255)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        // Attendance Overview Chart
        const attendanceCtx = document.getElementById('attendanceOverviewChart').getContext('2d');
        new Chart(attendanceCtx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent'],
                datasets: [{
                    data: [" . ($attendanceStats['present'] ?? 0) . ", " . ($attendanceStats['absent'] ?? 0) . "],
                    backgroundColor: [
                        'rgb(74, 107, 255)',
                        '#dc3545'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
";

include '../includes/footer.php';
?>