<?php
// Core setup
require_once '../config.php';
requireRole('teacher');

// Page title
$pageTitle = 'View Attendance Records';

// Init variables
$teacherId = null;
$subjects = [];
$records = [];
// Date and subject ID from URL (GET or today's date)
$date = $_GET['date'] ?? date('Y-m-d');
$selectedSubject = $_GET['subject_id'] ?? '';

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

// 3. Get attendance records for selected subject and date
if ($selectedSubject && $date) {
    $stmt = $conn->prepare("
        SELECT s.full_name, s.roll_no, a.status 
        FROM attendance a 
        JOIN students s ON a.student_id = s.student_id 
        WHERE a.subject_id = ? AND a.date = ? 
        ORDER BY s.roll_no
    ");
    // Bind subject ID (integer) and date (string)
    $stmt->bind_param("is", $selectedSubject, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $records = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Include layout
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="dashboard-card mb-4">
                    <h2 class="card-title mb-4"><i class="fas fa-list me-2 text-primary"></i>Attendance Records</h2>
                    
                    <form method="get" class="mb-4">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-6">
                                <label for="subject_id" class="form-label">Select Subject</label>
                                <select name="subject_id" id="subject_id" class="form-select" required>
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
                                <label for="date" class="form-label">Date</label>
                                <input type="date" name="date" id="date" class="form-control" value="<?php echo htmlspecialchars($date); ?>" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-search me-1"></i> View Records</button>
                    </form>
                    
                    <?php if ($selectedSubject && !empty($records)): ?>
                        <h5 class="mb-3">Records for **<?php echo htmlspecialchars($date); ?>**</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr class="table-light">
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
                                                <?php 
                                                    // Simple styling based on status
                                                    if ($rec['status'] == 'present'): ?>
                                                        <span class="badge bg-success">Present</span>
                                                    <?php elseif ($rec['status'] == 'absent'): ?>
                                                        <span class="badge bg-danger">Absent</span>
                                                    <?php elseif ($rec['status'] == 'late'): ?>
                                                        <span class="badge bg-warning text-dark">Late</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">-</span>
                                                    <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif ($selectedSubject && empty($records)): ?>
                        <div class="alert alert-info">No attendance records found for **<?php echo htmlspecialchars($date); ?>** in this subject.</div>
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