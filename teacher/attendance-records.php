<?php
require_once '../config.php';
requireRole('teacher');

$pageTitle = 'View Attendance Records';
include '../includes/header.php';
include '../includes/sidebar.php';

$db = Database::getInstance()->getConnection();
$teacherId = null;
$subjects = [];
$records = [];
$date = $_GET['date'] ?? date('Y-m-d');
$selectedSubject = $_GET['subject_id'] ?? '';

// Get teacher's internal teacher_id
if (!empty($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT teacher_id FROM teachers WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $teacherRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $teacherId = $teacherRow['teacher_id'] ?? null;
}

// Get subjects assigned to this teacher
if ($teacherId) {
    $stmt = $db->prepare('SELECT subject_id, subject_name FROM subjects WHERE teacher_id = ?');
    $stmt->execute([$teacherId]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get attendance records for selected subject and date
if ($selectedSubject && $date) {
    $stmt = $db->prepare('SELECT s.full_name, s.roll_no, a.status FROM attendance a JOIN students s ON a.student_id = s.student_id WHERE a.subject_id = ? AND a.date = ? ORDER BY s.roll_no');
    $stmt->execute([$selectedSubject, $date]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="main-content">
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h2 class="card-title mb-4"><i class="fas fa-list me-2"></i>Attendance Records</h2>
                        <form method="get" class="mb-4">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-6">
                                    <label for="subject_id" class="form-label">Select Subject</label>
                                    <select name="subject_id" id="subject_id" class="form-select" required>
                                        <option value="">-- Choose Subject --</option>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject['subject_id']; ?>" <?php if ($selectedSubject == $subject['subject_id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($subject['subject_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" name="date" id="date" class="form-control" value="<?php echo htmlspecialchars($date); ?>" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">View Records</button>
                        </form>
                        <?php if ($selectedSubject && $records): ?>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Roll No</th>
                                            <th>Student Name</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($records as $rec): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($rec['roll_no']); ?></td>
                                                <td><?php echo htmlspecialchars($rec['full_name']); ?></td>
                                                <td>
                                                    <?php if ($rec['status'] == 'present'): ?>
                                                        <span class="badge bg-success">Present</span>
                                                    <?php elseif ($rec['status'] == 'absent'): ?>
                                                        <span class="badge bg-danger">Absent</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php elseif ($selectedSubject): ?>
                            <div class="alert alert-info">No attendance records found for this date.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
