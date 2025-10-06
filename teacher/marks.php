<?php
require_once '../config.php';
requireRole('teacher');

$pageTitle = 'Marks Entry';
include '../includes/header.php';
include '../includes/sidebar.php';

$db = Database::getInstance()->getConnection();
$teacherId = null;
$subjects = [];
$students = [];
$success = false;
$error = '';
$selectedSubject = $_POST['subject_id'] ?? $_GET['subject_id'] ?? '';
$examType = $_POST['exam_type'] ?? $_GET['exam_type'] ?? '';

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

// Handle marks submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marks']) && $selectedSubject && $examType) {
    $marks = $_POST['marks']; // [student_id => marks]
    try {
        $db->beginTransaction();
        // Delete existing marks for this subject/exam/class
        $stmt = $db->prepare('DELETE m FROM marks m JOIN students s ON m.student_id = s.student_id WHERE m.subject_id = ? AND m.exam_type = ? AND s.class_id = ?');
        $stmt->execute([$selectedSubject, $examType, $selectedClassId]);
        // Insert new marks records
        foreach ($marks as $student_id => $mark) {
            if ($mark !== '' && is_numeric($mark)) {
                $stmt = $db->prepare('INSERT INTO marks (student_id, subject_id, exam_type, marks_obtained) VALUES (?, ?, ?, ?)');
                $stmt->execute([$student_id, $selectedSubject, $examType, $mark]);
            }
        }
        $db->commit();
        $success = true;
    } catch (PDOException $e) {
        $db->rollBack();
        $error = 'Failed to save marks.';
        error_log('Marks save error: ' . $e->getMessage());
    }
}
?>
<div class="main-content">
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h2 class="card-title mb-4"><i class="fas fa-chart-bar me-2"></i>Marks Entry</h2>
                        <?php if ($success): ?>
                            <div class="alert alert-success">Marks saved successfully!</div>
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
                                    <label for="exam_type" class="form-label">Exam Type</label>
                                    <select class="form-select" id="exam_type" name="exam_type" required onchange="this.form.submit()">
                                        <option value="">Select Exam Type</option>
                                        <option value="Midterm" <?php echo (($examType ?? '') == 'Midterm') ? 'selected' : ''; ?>>Midterm</option>
                                        <option value="Final" <?php echo (($examType ?? '') == 'Final') ? 'selected' : ''; ?>>Final</option>
                                        <option value="Quiz" <?php echo (($examType ?? '') == 'Quiz') ? 'selected' : ''; ?>>Quiz</option>
                                        <option value="Assignment" <?php echo (($examType ?? '') == 'Assignment') ? 'selected' : ''; ?>>Assignment</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                        <?php if ($selectedSubject && $examType && $students): ?>
                            <form method="post">
                                <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($selectedSubject); ?>">
                                <input type="hidden" name="exam_type" value="<?php echo htmlspecialchars($examType); ?>">
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>Roll No</th>
                                                <th>Student Name</th>
                                                <th class="text-center">Marks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($students as $student): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($student['roll_no']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                                    <td class="text-center">
                                                        <input type="number" name="marks[<?php echo $student['student_id']; ?>]" min="0" max="100" class="form-control form-control-sm mx-auto" style="width:100px;" value="">
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3 w-100 py-2 fs-5"><i class="fas fa-save me-2"></i>Save Marks</button>
                            </form>
                        <?php elseif ($selectedSubject && $examType): ?>
                            <div class="alert alert-info">No students found in this class.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
