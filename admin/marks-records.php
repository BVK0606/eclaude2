<?php
require_once '../config.php';
requireRole('admin'); // Only admin can access

$pageTitle = 'Marks Records';

// --- Filter variables ---
$classId = $_GET['class_id'] ?? '';
$subjectId = $_GET['subject_id'] ?? '';
$examType = $_GET['exam_type'] ?? '';

$error = '';
$success = '';

// --- Fetch Classes ---
$classResult = mysqli_query($conn, "SELECT class_id, class_name FROM classes ORDER BY class_name");
$classes = mysqli_fetch_all($classResult, MYSQLI_ASSOC);

// --- Fetch Subjects ---
$subjectResult = mysqli_query($conn, "SELECT subject_id, subject_name FROM subjects ORDER BY subject_name");
$subjects = mysqli_fetch_all($subjectResult, MYSQLI_ASSOC);

// --- Fetch Marks Records ---
$records = [];
if (!empty($classId) && !empty($subjectId) && !empty($examType)) {
    $query = "
        SELECT s.roll_no, s.full_name, m.marks_obtained
        FROM marks m
        JOIN students s ON m.student_id = s.student_id
        WHERE s.class_id = '$classId' 
          AND m.subject_id = '$subjectId' 
          AND m.exam_type = '$examType'
        ORDER BY s.roll_no
    ";
    $result = mysqli_query($conn, $query);
    $records = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <div class="dashboard-card mb-4">
            <h2 class="mb-2">Marks Records</h2>

            <!-- Filter Form -->
            <form method="get" class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-select" required>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?php echo $c['class_id']; ?>" 
                                <?php if ($classId == $c['class_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($c['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" class="form-select" required>
                        <option value="">Select Subject</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?php echo $s['subject_id']; ?>" 
                                <?php if ($subjectId == $s['subject_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($s['subject_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Exam Type</label>
                    <select name="exam_type" class="form-select" required>
                        <option value="">Select Exam Type</option>
                        <option value="Midterm" <?php if ($examType == 'Midterm') echo 'selected'; ?>>Midterm</option>
                        <option value="Final" <?php if ($examType == 'Final') echo 'selected'; ?>>Final</option>
                        <option value="Quiz" <?php if ($examType == 'Quiz') echo 'selected'; ?>>Quiz</option>
                        <option value="Assignment" <?php if ($examType == 'Assignment') echo 'selected'; ?>>Assignment</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Show Records
                    </button>
                </div>
            </form>

            <!-- Marks Table -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Marks Obtained</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">No records found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['roll_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['marks_obtained']); ?></td>
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