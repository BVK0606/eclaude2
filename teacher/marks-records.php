<?php
// Core setup
require_once '../config.php';
requireRole('teacher');

// Page title
$pageTitle = 'View Marks Records';
// Include layout
include '../includes/header.php';
include '../includes/sidebar.php';

// Init variables
$teacherId = null;
$subjects = [];
$records = [];
$examTypes = [];
$selectedSubject = $_GET['subject_id'] ?? '';
$examType = $_GET['exam_type'] ?? '';

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
    $stmt = $conn->prepare("SELECT subject_id, subject_name FROM subjects WHERE teacher_id = ? ORDER BY subject_name");
    $stmt->bind_param("i", $teacherId);
    $stmt->execute();
    $result = $stmt->get_result();
    $subjects = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// 3. Get distinct exam types for this subject (if selected)
if ($selectedSubject) {
    $stmt = $conn->prepare("SELECT DISTINCT exam_type FROM marks WHERE subject_id = ?");
    $stmt->bind_param("i", $selectedSubject);
    $stmt->execute();
    $result = $stmt->get_result();
    // Fetch all exam types into an indexed array
    while($row = $result->fetch_array(MYSQLI_NUM)) {
        $examTypes[] = $row[0];
    }
    $stmt->close();
}

// 4. Get marks records for selected subject and exam type
if ($selectedSubject && $examType) {
    $stmt = $conn->prepare("
        SELECT s.full_name, s.roll_no, m.marks_obtained 
        FROM marks m 
        JOIN students s ON m.student_id = s.student_id 
        WHERE m.subject_id = ? AND m.exam_type = ? 
        ORDER BY s.roll_no
    ");
    $stmt->bind_param("is", $selectedSubject, $examType);
    $stmt->execute();
    $result = $stmt->get_result();
    $records = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<div class="main-content">
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="dashboard-card mb-4">
                    <h2 class="card-title mb-4"><i class="fas fa-list me-2 text-primary"></i>Marks Records</h2>
                    
                    <form method="get" class="mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label for="subject_id" class="form-label">Select Subject</label>
                                <select name="subject_id" id="subject_id" class="form-select" required onchange="this.form.submit()">
                                    <option value="">-- Choose Subject --</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?php echo $subject['subject_id']; ?>" 
                                            <?php if ($selectedSubject == $subject['subject_id']) echo 'selected'; ?>>
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
                                        <option value="<?php echo htmlspecialchars($type); ?>" 
                                            <?php if ($examType == $type) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($type); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </form>
                    
                    <?php if ($selectedSubject && $examType && !empty($records)): ?>
                        <h5 class="mb-3">Marks for **<?php echo htmlspecialchars($examType); ?>**</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr class="table-light">
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
                                            <td><span class="badge bg-primary fs-6"><?php echo htmlspecialchars($rec['marks_obtained']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif ($selectedSubject && $examType): ?>
                        <div class="alert alert-info">No marks records found for this exam type.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php 
// Include footer
include '../includes/footer.php'; 
?>