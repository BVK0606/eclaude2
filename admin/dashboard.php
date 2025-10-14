<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Admin Dashboard';

try {
    $db = Database::getInstance()->getConnection();

    // --- Basic counts ---
    $totalStudents = (int) ($db->query("SELECT COUNT(*) FROM students")->fetchColumn() ?? 0);
    $totalTeachers = (int) ($db->query("SELECT COUNT(*) FROM teachers")->fetchColumn() ?? 0);
    $totalClasses  = (int) ($db->query("SELECT COUNT(*) FROM classes")->fetchColumn() ?? 0);
    $totalSubjects = (int) ($db->query("SELECT COUNT(*) FROM subjects")->fetchColumn() ?? 0);

    // --- Recently added students (last 5) ---
    $stmt = $db->query("
        SELECT s.roll_no, u.uname AS name, u.email, c.class_name, s.created_at
        FROM students s
        JOIN users u ON s.user_id = u.id
        LEFT JOIN classes c ON s.class_id = c.class_id
        ORDER BY s.created_at DESC
        LIMIT 5
    ");
    $recentStudentsList = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $totalStudents = $totalTeachers = $totalClasses = $totalSubjects = 0;
    $recentStudentsList = [];
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">

        <!-- Welcome section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="mb-2">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                            <p class="text-muted mb-0">Here's a quick look at your school system.</p>
                        </div>
                        <div class="text-end d-none d-md-block">
                            <div class="fs-4 fw-bold text-primary"><?php echo date('d'); ?></div>
                            <div class="text-muted"><?php echo date('M Y'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics cards -->
        <div class="row mb-4">
            <?php
            $statsCards = [
                ['title' => 'Total Students', 'value' => $totalStudents, 'icon' => 'fa-user-graduate', 'bg' => '#4A6BFF'],
                ['title' => 'Total Teachers', 'value' => $totalTeachers, 'icon' => 'fa-chalkboard-teacher', 'bg' => '#28A745'],
                ['title' => 'Total Classes',  'value' => $totalClasses,  'icon' => 'fa-school', 'bg' => '#FFC107'],
                ['title' => 'Total Subjects', 'value' => $totalSubjects, 'icon' => 'fa-book', 'bg' => '#DC3545']
            ];
            foreach ($statsCards as $card): ?>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                    <div class="dashboard-card text-center py-3">
                        <div class="rounded-circle mx-auto mb-3" style="background: <?= $card['bg']; ?>; width:60px; height:60px; display:flex; align-items:center; justify-content:center;">
                            <i class="fas <?= $card['icon']; ?> text-white fs-4"></i>
                        </div>
                        <div class="card-value fs-3 fw-bold"><?php echo number_format($card['value']); ?></div>
                        <div class="card-title"><?php echo $card['title']; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Recently added students -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Recently Added Students</h5>
                        <a href="manage-students.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye me-1"></i> View All
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Roll No</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Class</th>
                                    <th>Added On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentStudentsList)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                            No recent students found.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentStudentsList as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['roll_no']); ?></td>
                                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                                            <td><?php echo htmlspecialchars($student['class_name'] ?? 'Not Assigned'); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <h5 class="mb-3">Quick Actions</h5>
                    <div class="row text-center">
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

    </div>
</div>

<?php include '../includes/footer.php'; ?>