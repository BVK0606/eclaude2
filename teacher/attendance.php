<?php
require_once '../config.php';
requireRole('teacher');

$pageTitle = 'Mark Attendance';
include '../includes/header.php';
include '../includes/sidebar.php';

$db = Database::getInstance()->getConnection();
$teacherId = null;
$subjects = [];
$students = [];
$attendanceSaved = false;
$error = '';
$date = $_POST['date'] ?? date('Y-m-d');
$selectedSubject = $_POST['subject_id'] ?? $_GET['subject_id'] ?? '';

// Get teacher's internal teacher_id
if (!empty($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT teacher_id FROM teachers WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $teacherRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $teacherId = $teacherRow['teacher_id'] ?? null;
}

// Get subjects assigned to this teacher
if ($teacherId) {
    $stmt = $db->prepare('SELECT subject_id, subject_name, class_id FROM subjects WHERE teacher_id = ?');
    $stmt->execute([$teacherId]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get students for the selected subject's class
$selectedClassId = '';
if ($selectedSubject) {
    foreach ($subjects as $subj) {
        if ($subj['subject_id'] == $selectedSubject) {
            $selectedClassId = $subj['class_id'];
            break;
        }
    }
    if ($selectedClassId) {
        $stmt = $db->prepare('SELECT student_id, roll_no, full_name FROM students WHERE class_id = ? ORDER BY roll_no');
        $stmt->execute([$selectedClassId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance']) && $selectedSubject && $date) {
    $attendance = $_POST['attendance']; // [student_id => 'present'|'absent']
    try {
        $db->beginTransaction();
        // Delete existing attendance for this subject/date/class
        $stmt = $db->prepare('DELETE a FROM attendance a JOIN students s ON a.student_id = s.student_id WHERE a.subject_id = ? AND a.date = ? AND s.class_id = ?');
        $stmt->execute([$selectedSubject, $date, $selectedClassId]);
        // Insert new attendance records
        foreach ($attendance as $student_id => $status) {
            if (!empty($status)) {
                $stmt = $db->prepare('INSERT INTO attendance (student_id, subject_id, date, status) VALUES (?, ?, ?, ?)');
                $stmt->execute([$student_id, $selectedSubject, $date, $status]);
            }
        }
        $db->commit();
        $attendanceSaved = true;
    } catch (PDOException $e) {
        $db->rollBack();
        $error = 'Failed to save attendance.';
        error_log('Attendance save error: ' . $e->getMessage());
    }
}
?>
<div class="main-content">
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h2 class="card-title mb-4"><i class="fas fa-calendar-check me-2"></i>Mark Attendance</h2>
                        <?php if ($attendanceSaved): ?>
                            <div class="alert alert-success">Attendance saved successfully!</div>
                        <?php elseif ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
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
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" name="date" id="date" class="form-control" value="<?php echo htmlspecialchars($date); ?>" required onchange="this.form.submit()">
                                </div>
                            </div>
                        </form>
                        <?php if ($selectedSubject && $students): ?>
                            <form method="post">
                                <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($selectedSubject); ?>">
                                <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>Roll No</th>
                                                <th>Student Name</th>
                                                <th class="text-center">Present</th>
                                                <th class="text-center">Absent</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($students as $student): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($student['roll_no']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                                    <td class="text-center">
                                                        <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" value="present" required>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" value="absent">
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">Save Attendance</button>
                            </form>
                        <?php elseif ($selectedSubject): ?>
                            <div class="alert alert-info">No students found in this class.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
