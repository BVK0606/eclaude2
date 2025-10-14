<?php
// Core setup
require_once '../config.php';
requireRole('teacher');

// Page title
$pageTitle = 'Marks Entry';
// Include layout
include '../includes/header.php';
include '../includes/sidebar.php';

// Init variables
$teacherId = null;
$subjects = [];
$students = [];
$success = false;
$error = '';
// Get subject and exam type from POST/GET
$selectedSubject = $_POST['subject_id'] ?? $_GET['subject_id'] ?? '';
$examType = $_POST['exam_type'] ?? $_GET['exam_type'] ?? '';
$currentMarks = [];

// --- Database logic ---

// 1. Get teacher's internal teacher_id
if (!empty($_SESSION['user_id']) && isset($conn)) {
    $stmt = $conn->prepare("SELECT teacher_id FROM teachers WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacherRow = $result->fetch_assoc();
    $teacherId = $teacherRow['teacher_id'] ?? null;
    $stmt->close();
}

// 2. Get subjects assigned to this teacher
if ($teacherId) {
    $stmt = $conn->prepare("SELECT subject_id, subject_name, class_id FROM subjects WHERE teacher_id = ? ORDER BY subject_name");
    $stmt->bind_param("i", $teacherId);
    $stmt->execute();
    $result = $stmt->get_result();
    $subjects = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// 3. Get students for the selected subject's class
$selectedClassId = '';
if ($selectedSubject) {
    // Find the class ID associated with the selected subject
    foreach ($subjects as $subj) {
        if ($subj['subject_id'] == $selectedSubject) {
            $selectedClassId = $subj['class_id'];
            break;
        }
    }
    
    if ($selectedClassId) {
        $stmt = $conn->prepare("SELECT student_id, roll_no, full_name FROM students WHERE class_id = ? ORDER BY roll_no");
        $stmt->bind_param("i", $selectedClassId);
        $stmt->execute();
        $result = $stmt->get_result();
        $students = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // 4. Fetch existing marks for pre-filling the form (if subject and exam type are selected)
        if ($examType) {
            $stmt = $conn->prepare("SELECT student_id, marks_obtained FROM marks WHERE subject_id = ? AND exam_type = ?");
            $stmt->bind_param("is", $selectedSubject, $examType);
            $stmt->execute();
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()) {
                $currentMarks[$row['student_id']] = $row['marks_obtained'];
            }
            $stmt->close();
        }
    }
}

// 5. Handle marks submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marks']) && $selectedSubject && $examType && $selectedClassId) {
    $marks = $_POST['marks']; // [student_id => marks]
    
    // Begin transaction for safe multi-step operation
    $conn->begin_transaction();
    try {
        // A. Delete existing marks for this subject/exam/class
        $stmt = $conn->prepare("DELETE m FROM marks m JOIN students s ON m.student_id = s.student_id WHERE m.subject_id = ? AND m.exam_type = ? AND s.class_id = ?");
        $stmt->bind_param("isi", $selectedSubject, $examType, $selectedClassId);
        $stmt->execute();
        $stmt->close();
        
        // B. Insert new marks records
        $stmt = $conn->prepare("INSERT INTO marks (student_id, subject_id, exam_type, marks_obtained) VALUES (?, ?, ?, ?)");
        foreach ($marks as $student_id => $mark) {
            // Only insert if mark is provided and is numeric
            if ($mark !== '' && is_numeric($mark)) {
                $markFloat = (float)$mark; // Ensure correct type for binding
                $stmt->bind_param("iisi", $student_id, $selectedSubject, $examType, $markFloat); // i: int, i: int, s: string, i: int/float
                $stmt->execute();
            }
        }
        $stmt->close();
        
        // Commit changes if all inserts succeeded
        $conn->commit();
        $success = true;
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        $error = 'Failed to save marks.';
        error_log('Marks save error: ' . $e->getMessage());
    }
}
?>

<div class="main-content">
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="dashboard-card mb-4">
                    <h2 class="card-title mb-4"><i class="fas fa-chart-bar me-2 text-primary"></i>Marks Entry</h2>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">Marks saved successfully!</div>
                    <?php elseif ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="get" class="mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label for="subject_id" class="form-label">Select Subject *</label>
                                <select name="subject_id" id="subject_id" class="form-select" required onchange="this.form.submit()">
                                    <option value="">-- Choose Subject --</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?php echo $subject['subject_id']; ?>" 
                                            <?php if ($selectedSubject == $subject['subject_id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($subject['subject_name']); ?> (Class: <?php echo htmlspecialchars($subject['class_id']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="exam_type" class="form-label">Exam Type *</label>
                                <select class="form-select" id="exam_type" name="exam_type" required onchange="this.form.submit()">
                                    <option value="">Select Exam Type</option>
                                    <option value="Midterm" <?php echo ($examType == 'Midterm') ? 'selected' : ''; ?>>Midterm</option>
                                    <option value="Final" <?php echo ($examType == 'Final') ? 'selected' : ''; ?>>Final</option>
                                    <option value="Quiz" <?php echo ($examType == 'Quiz') ? 'selected' : ''; ?>>Quiz</option>
                                    <option value="Assignment" <?php echo ($examType == 'Assignment') ? 'selected' : ''; ?>>Assignment</option>
                                </select>
                            </div>
                        </div>
                    </form>
                    
                    <?php if ($selectedSubject && $examType && $students): ?>
                        <h5 class="mb-3">Entry for **<?php echo htmlspecialchars($examType); ?>** Exam:</h5>
                        
                        <form method="post">
                            <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($selectedSubject); ?>">
                            <input type="hidden" name="exam_type" value="<?php echo htmlspecialchars($examType); ?>">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead>
                                        <tr class="table-light">
                                            <th>Roll No</th>
                                            <th>Student Name</th>
                                            <th class="text-center">Marks (Out of 100)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): 
                                            $markValue = $currentMarks[$student['student_id']] ?? '';
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['roll_no']); ?></td>
                                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                                <td class="text-center">
                                                    <input type="number" 
                                                        name="marks[<?php echo $student['student_id']; ?>]" 
                                                        min="0" max="100" 
                                                        class="form-control form-control-sm mx-auto" 
                                                        style="width:100px;" 
                                                        value="<?php echo htmlspecialchars($markValue); ?>">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3 w-100 py-2 fs-5">
                                <i class="fas fa-save me-2"></i>Save Marks
                            </button>
                        </form>
                        
                    <?php elseif ($selectedSubject && $examType): ?>
                        <div class="alert alert-info">No students found in the class assigned to this subject.</div>
                    <?php else: ?>
                        <div class="alert alert-warning">Please select a subject and exam type to enter marks.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>