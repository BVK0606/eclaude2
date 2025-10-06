<?php
require_once '../config.php';
requireRole('teacher');

$pageTitle = 'View Marks Records';
include '../includes/header.php';
include '../includes/sidebar.php';

$db = Database::getInstance()->getConnection();
$teacherId = null;
$subjects = [];
$records = [];
$selectedSubject = $_GET['subject_id'] ?? '';
$examType = $_GET['exam_type'] ?? '';

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

// Get exam types for this subject
$examTypes = [];
if ($selectedSubject) {
    $stmt = $db->prepare('SELECT DISTINCT exam_type FROM marks WHERE subject_id = ?');
    $stmt->execute([$selectedSubject]);
    $examTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Get marks records for selected subject and exam type
if ($selectedSubject && $examType) {
    $stmt = $db->prepare('SELECT s.full_name, s.roll_no, m.marks_obtained FROM marks m JOIN students s ON m.student_id = s.student_id WHERE m.subject_id = ? AND m.exam_type = ? ORDER BY s.roll_no');
    $stmt->execute([$selectedSubject, $examType]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="main-content">
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h2 class="card-title mb-4"><i class="fas fa-list me-2"></i>Marks Records</h2>
                        <form method="get" class="mb-4">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-6">
                                    <label for="subject_id" class="form-label">Select Subject</label>
                                    <select name="subject_id" id="subject_id" class="form-select" required onchange="this.form.submit()">
                                        <option value="">-- Choose Subject --</option>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject['subject_id']; ?>" <?php if ($selectedSubject == $subject['subject_id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($subject['subject_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="exam_type" class="form-label">Exam Type</label>
                                    <select name="exam_type" id="exam_type" class="form-select" required onchange="this.form.submit()">
                                        <option value="">-- Choose Exam --</option>
                                        <?php foreach ($examTypes as $type): ?>
                                            <option value="<?php echo htmlspecialchars($type); ?>" <?php if ($examType == $type) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($type); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </form>
                        <?php if ($selectedSubject && $examType && $records): ?>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Roll No</th>
                                            <th>Student Name</th>
                                            <th>Marks Obtained</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($records as $rec): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($rec['roll_no']); ?></td>
                                                <td><?php echo htmlspecialchars($rec['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($rec['marks_obtained']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php elseif ($selectedSubject && $examType): ?>
                            <div class="alert alert-info">No marks records found for this exam.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
