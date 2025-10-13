<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Manage Marks';

$error = '';
$success = '';

// --- Handle Marks Submission ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_marks'])) {
    $classId = sanitizeInput($_POST['class_id'] ?? '');
    $subjectId = sanitizeInput($_POST['subject_id'] ?? '');
    $examType = sanitizeInput($_POST['exam_type'] ?? '');
    $marksData = $_POST['marks'] ?? [];

    if (empty($classId) || empty($subjectId) || empty($examType)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Delete existing marks before inserting new ones
        $deleteQuery = "
            DELETE m FROM marks m
            JOIN students s ON m.student_id = s.student_id
            WHERE m.subject_id = '$subjectId' AND m.exam_type = '$examType' AND s.class_id = '$classId'
        ";

        if (mysqli_query($conn, $deleteQuery)) {
            foreach ($marksData as $studentId => $marks) {
                if ($marks !== '' && is_numeric($marks)) {
                    $insertQuery = "
                        INSERT INTO marks (student_id, subject_id, exam_type, marks_obtained)
                        VALUES ('$studentId', '$subjectId', '$examType', '$marks')
                    ";
                    mysqli_query($conn, $insertQuery);
                }
            }
            $success = 'Marks added successfully!';
        } else {
            $error = 'Failed to save marks. Please try again.';
        }
    }
}

// --- Fetch Classes ---
$classes = [];
$classResult = mysqli_query($conn, "SELECT class_id, class_name FROM classes ORDER BY class_name");
while ($row = mysqli_fetch_assoc($classResult)) {
    $classes[] = $row;
}

// --- Fetch Subjects ---
$subjects = [];
$subjectResult = mysqli_query($conn, "SELECT subject_id, subject_name FROM subjects ORDER BY subject_name");
while ($row = mysqli_fetch_assoc($subjectResult)) {
    $subjects[] = $row;
}

// --- Fetch Students for Selected Class ---
$students = [];
if (!empty($_POST['class_id'])) {
    $classId = sanitizeInput($_POST['class_id']);
    $studentResult = mysqli_query($conn, "
        SELECT student_id, roll_no, full_name
        FROM students
        WHERE class_id = '$classId'
        ORDER BY roll_no
    ");
    while ($row = mysqli_fetch_assoc($studentResult)) {
        $students[] = $row;
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- ====== Manage Marks Page ====== -->
<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2>Manage Marks</h2>
                    <p class="text-muted">Add and manage student marks easily.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Class *</label>
                                <select name="class_id" class="form-select" required onchange="this.form.submit()">
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['class_id']; ?>"
                                            <?php echo ($_POST['class_id'] ?? '') == $class['class_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($class['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Subject *</label>
                                <select name="subject_id" class="form-select" required>
                                    <option value="">Select Subject</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?php echo $subject['subject_id']; ?>"
                                            <?php echo ($_POST['subject_id'] ?? '') == $subject['subject_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Exam Type *</label>
                                <select name="exam_type" class="form-select" required>
                                    <option value="">Select Exam Type</option>
                                    <option value="Midterm" <?php echo (($_POST['exam_type'] ?? '') == 'Midterm') ? 'selected' : ''; ?>>Midterm</option>
                                    <option value="Final" <?php echo (($_POST['exam_type'] ?? '') == 'Final') ? 'selected' : ''; ?>>Final</option>
                                    <option value="Quiz" <?php echo (($_POST['exam_type'] ?? '') == 'Quiz') ? 'selected' : ''; ?>>Quiz</option>
                                    <option value="Assignment" <?php echo (($_POST['exam_type'] ?? '') == 'Assignment') ? 'selected' : ''; ?>>Assignment</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label d-block">&nbsp;</label>
                                <button type="submit" name="add_marks" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-2"></i>Save Marks
                                </button>
                            </div>
                        </div>

                        <?php if (!empty($students)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Roll No</th>
                                            <th>Student Name</th>
                                            <th>Marks (Out of 100)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['roll_no']); ?></td>
                                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                                <td>
                                                    <input type="number"
                                                        name="marks[<?php echo $student['student_id']; ?>]"
                                                        class="form-control"
                                                        placeholder="Enter marks"
                                                        min="0" max="100" step="0.01">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <?php if (!empty($_POST['class_id'])): ?>
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                    <p>No students found in this class.</p>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-school fa-3x mb-3 d-block"></i>
                                    <p>Please select a class to view students.</p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>