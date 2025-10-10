<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Admin Dashboard';

try {
    $db = Database::getInstance()->getConnection();

    $totalStudents = (int) ($db->query("SELECT COUNT(*) FROM students")->fetchColumn() ?? 0);
    $totalTeachers = (int) ($db->query("SELECT COUNT(*) FROM teachers")->fetchColumn() ?? 0);
    $totalClasses = (int) ($db->query("SELECT COUNT(*) FROM classes")->fetchColumn() ?? 0);
    $totalSubjects = (int) ($db->query("SELECT COUNT(*) FROM subjects")->fetchColumn() ?? 0);

    $recentStudents = (int) ($db->query("SELECT COUNT(*) FROM students WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn() ?? 0);
    $recentTeachers = (int) ($db->query("SELECT COUNT(*) FROM teachers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn() ?? 0);

    $stmt = $db->query("
        SELECT s.roll_no, u.uname as name, u.email, c.class_name, s.created_at
        FROM students s
        JOIN users u ON s.user_id = u.id
        LEFT JOIN classes c ON s.class_id = c.class_id
        ORDER BY s.created_at DESC
        LIMIT 5
    ");
    $recentStudentsList = $stmt->fetchAll();

    $today = date('Y-m-d');
    $attendanceTotals = ['present' => 0, 'absent' => 0, 'late' => 0];
    $stmt = $db->prepare("SELECT status, COUNT(*) as count FROM attendance WHERE date = ? GROUP BY status");
    $stmt->execute([$today]);
    $totalAttendance = 0;
    foreach ($stmt->fetchAll() as $row) {
        $status = $row['status'];
        $count = (int) $row['count'];
        if (isset($attendanceTotals[$status])) {
            $attendanceTotals[$status] = $count;
            $totalAttendance += $count;
        }
    }

    $attendanceStats = [
        'present' => $totalAttendance ? round($attendanceTotals['present'] * 100 / $totalAttendance) : 0,
        'absent' => $totalAttendance ? round($attendanceTotals['absent'] * 100 / $totalAttendance) : 0,
        'late' => $totalAttendance ? round($attendanceTotals['late'] * 100 / $totalAttendance) : 0
    ];

} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $totalStudents = $totalTeachers = $totalClasses = $totalSubjects = 0;
    $recentStudents = $recentTeachers = 0;
    $recentStudentsList = [];
    $attendanceStats = ['present' => 0, 'absent' => 0, 'late' => 0];
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="mb-2">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                            <p class="text-muted mb-0">Here's what's happening in your school today.</p>
                        </div>
                        <div class="d-none d-md-block text-end">
                            <div class="fs-4 fw-bold text-primary"><?php echo date('d'); ?></div>
                            <div class="text-muted"><?php echo date('M Y'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php
            $statsCards = [
                ['title' => 'Total Students', 'value' => $totalStudents, 'recent' => $recentStudents, 'icon' => 'fa-user-graduate', 'bg' => 'linear-gradient(135deg, #4A6BFF 0%, #9F7AEA 100%)', 'color' => 'primary'],
                ['title' => 'Total Teachers', 'value' => $totalTeachers, 'recent' => $recentTeachers, 'icon' => 'fa-chalkboard-teacher', 'bg' => 'linear-gradient(135deg, #28A745 0%, #20C997 100%)', 'color' => 'success'],
                ['title' => 'Total Classes', 'value' => $totalClasses, 'recent' => 0, 'icon' => 'fa-school', 'bg' => 'linear-gradient(135deg, #FFC107 0%, #FF8C00 100%)', 'color' => 'warning'],
                ['title' => 'Total Subjects', 'value' => $totalSubjects, 'recent' => 0, 'icon' => 'fa-book', 'bg' => 'linear-gradient(135deg, #DC3545 0%, #E83E8C 100%)', 'color' => 'danger']
            ];

            foreach ($statsCards as $card): ?>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                    <div class="dashboard-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="rounded-circle p-3"
                                    style="background: <?= $card['bg']; ?>; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas <?= $card['icon']; ?> text-white fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="card-value"><?php echo number_format($card['value']); ?></div>
                                <div class="card-title"><?php echo $card['title']; ?></div>
                                <?php if ($card['recent'] > 0): ?>
                                    <div class="card-change positive">
                                        <i class="fas fa-arrow-up"></i>
                                        +<?php echo $card['recent']; ?> this week
                                    </div>
                                <?php elseif ($card['recent'] === 0 && in_array($card['title'], ['Total Classes', 'Total Subjects'])): ?>
                                    <div class="card-change">
                                        <i class="fas fa-minus"></i>
                                        No change
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>


        <!-- Charts & Recent Students -->
        <div class="row">
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title">Student Enrollment Trend</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle"
                                data-bs-toggle="dropdown">Last 6 Months</button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Last 3 Months</a></li>
                                <li><a class="dropdown-item" href="#">Last 6 Months</a></li>
                                <li><a class="dropdown-item" href="#">Last Year</a></li>
                            </ul>
                        </div>
                    </div>
                    <div style="position: relative; height: 300px;"><canvas id="enrollmentChart"></canvas></div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="dashboard-card">
                    <h5 class="card-title mb-3">Today's Attendance</h5>
                    <div class="text-center" style="position: relative; height: 250px;">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                    <div class="row text-center mt-3">
                        <div class="col-4">
                            <div class="fw-bold text-success"><?php echo $attendanceStats['present']; ?>%</div>
                            <div class="small text-muted">Present</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-danger"><?php echo $attendanceStats['absent']; ?>%</div>
                            <div class="small text-muted">Absent</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-warning"><?php echo $attendanceStats['late']; ?>%</div>
                            <div class="small text-muted">Late</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Students Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <div class="table-header">
                        <h5 class="table-title">Recently Added Students</h5>
                        <a href="manage-students.php" class="btn btn-primary btn-sm"><i class="fas fa-eye me-1"></i>View
                            All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Roll No</th>
                                    <th>Student Name</th>
                                    <th>Email</th>
                                    <th>Class</th>
                                    <th>Added Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentStudentsList)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-users fa-2x mb-2 d-block"></i>No students found. <a
                                                href="add-student.php">Add your first student</a>
                                        </td>
                                    </tr>
                                <?php else:
                                    foreach ($recentStudentsList as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['roll_no']); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar me-2"
                                                        style="width:32px;height:32px;font-size:0.8rem;">
                                                        <?php echo strtoupper(substr($student['name'], 0, 1)); ?></div>
                                                    <?php echo htmlspecialchars($student['name']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                                            <td><span
                                                    class="badge bg-primary"><?php echo htmlspecialchars($student['class_name'] ?? 'Not Assigned'); ?></span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary" title="View"><i
                                                            class="fas fa-eye"></i></button>
                                                    <button class="btn btn-sm btn-outline-success" title="Edit"><i
                                                            class="fas fa-edit"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h5 class="card-title mb-3">Quick Actions</h5>
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3"><a href="add-student.php"
                                class="btn btn-outline-primary w-100 p-3"><i
                                    class="fas fa-user-plus fa-2x mb-2 d-block"></i>Add New Student</a></div>
                        <div class="col-lg-3 col-md-6 mb-3"><a href="add-teacher.php"
                                class="btn btn-outline-success w-100 p-3"><i
                                    class="fas fa-chalkboard-teacher fa-2x mb-2 d-block"></i>Add New Teacher</a></div>
                        <div class="col-lg-3 col-md-6 mb-3"><a href="notices.php"
                                class="btn btn-outline-warning w-100 p-3"><i
                                    class="fas fa-bullhorn fa-2x mb-2 d-block"></i>Post Notice</a></div>
                        <div class="col-lg-3 col-md-6 mb-3"><a href="reports.php"
                                class="btn btn-outline-info w-100 p-3"><i
                                    class="fas fa-chart-bar fa-2x mb-2 d-block"></i>View Reports</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$pageScripts = "
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') { Chart.helpers.each(Chart.instances, c => c.destroy()); }

    const enrollmentCtx = document.getElementById('enrollmentChart');
    if(enrollmentCtx){ new Chart(enrollmentCtx,{type:'line',data:{labels:['Jan','Feb','Mar','Apr','May','Jun'],datasets:[{label:'New Students',data:[12,19,15,25,22,30],borderColor:'rgb(74, 107, 255)',backgroundColor:'rgba(74,107,255,0.1)',borderWidth:3,fill:true,tension:0.4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'#f1f5f9'}},x:{grid:{display:false}}}}}); }

    const attendanceCtx = document.getElementById('attendanceChart');
    if(attendanceCtx){ new Chart(attendanceCtx,{type:'doughnut',data:{labels:['Present','Absent','Late'],datasets:[{data:[{$attendanceStats['present']},{$attendanceStats['absent']},{$attendanceStats['late']}],backgroundColor:['rgb(74,107,255)','#dc3545','#ffc107'],borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}}}}); }
});
";

include '../includes/footer.php';
?>