<?php
require_once '../config.php';
requireRole('admin');
$pageTitle = 'Attendance Records';

$db = Database::getInstance()->getConnection();

// Filters
$classId = $_GET['class_id'] ?? '';
$subjectId = $_GET['subject_id'] ?? '';
$date = $_GET['date'] ?? '';

// Fetch classes and subjects for filters
$classes = $db->query("SELECT class_id, class_name FROM classes ORDER BY class_name")->fetchAll();
$subjects = $db->query("SELECT subject_id, subject_name FROM subjects ORDER BY subject_name")->fetchAll();

$records = [];
if ($classId && $subjectId && $date) {
    $stmt = $db->prepare("
        SELECT s.roll_no, s.full_name, a.status
        FROM attendance a
        JOIN students s ON a.student_id = s.student_id
        WHERE s.class_id = ? AND a.subject_id = ? AND a.date = ?
        ORDER BY s.roll_no
    ");
    $stmt->execute([$classId, $subjectId, $date]);
    $records = $stmt->fetchAll();
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="dashboard-card mb-4">
            <h2 class="mb-2">Attendance Records</h2>
            <form method="get" class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-select" required>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?php echo $c['class_id']; ?>" <?php if($classId==$c['class_id']) echo 'selected'; ?>><?php echo htmlspecialchars($c['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" class="form-select" required>
                        <option value="">Select Subject</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?php echo $s['subject_id']; ?>" <?php if($subjectId==$s['subject_id']) echo 'selected'; ?>><?php echo htmlspecialchars($s['subject_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date); ?>" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Show Records</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr><td colspan="3" class="text-center text-muted">No records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($records as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['roll_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td>
                                        <?php if ($row['status'] == 'present'): ?>
                                            <span class="badge bg-success">Present</span>
                                        <?php elseif ($row['status'] == 'absent'): ?>
                                            <span class="badge bg-danger">Absent</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Other</span>
                                        <?php endif; ?>
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
<?php include '../includes/footer.php'; ?>
