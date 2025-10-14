<?php
// Core setup
require_once '../config.php';
requireRole('teacher');

// Page title
$pageTitle = 'Mark Attendance';

// Init variables
$teacherId = null;
$subjects = [];
$students = [];
$attendanceSaved = false;
$error = '';
// Date from POST/GET or today
$date = $_POST['date'] ?? $_GET['date'] ?? date('Y-m-d');
$selectedSubject = $_POST['subject_id'] ?? $_GET['subject_id'] ?? '';
$selectedClassId = ''; // To be determined after subject selection

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
    }
}

// 4. Handle attendance submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance']) && $selectedSubject && $date && $selectedClassId) {
    $attendance = $_POST['attendance']; // [student_id => 'present'|'absent']
    
    // Begin transaction for safe multi-step operation
    $conn->begin_transaction();
    try {
        // A. Delete existing attendance for this subject/date/class
        $stmt = $conn->prepare("DELETE a FROM attendance a JOIN students s ON a.student_id = s.student_id WHERE a.subject_id = ? AND a.date = ? AND s.class_id = ?");
        $stmt->bind_param("isi", $selectedSubject, $date, $selectedClassId);
        $stmt->execute();
        $stmt->close();
        
        // B. Insert new attendance records
        $stmt = $conn->prepare("INSERT INTO attendance (student_id, subject_id, date, status) VALUES (?, ?, ?, ?)");
        foreach ($attendance as $student_id => $status) {
            if (!empty($status)) {
                $stmt->bind_param("iiss", $student_id, $selectedSubject, $date, $status); // iiss: student_id, subject_id (int), date (string), status (string)
                $stmt->execute();
            }
        }
        $stmt->close();
        
        // Commit changes if all inserts succeeded
        $conn->commit();
        $attendanceSaved = true;
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        $error = 'Failed to save attendance.';
        error_log('Attendance save error: ' . $e->getMessage());
    }
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
                    <h2 class="card-title mb-4"><i class="fas fa-calendar-check me-2 text-primary"></i>Mark Daily Attendance</h2>
                    
                    <?php if ($attendanceSaved): ?>
                        <div class="alert alert-success">Attendance saved successfully for **<?php echo htmlspecialchars($date); ?>**!</div>
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
                                        <option value="<?php echo $subject['subject_id']; ?>" 
                                            <?php if ($selectedSubject == $subject['subject_id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($subject['subject_name']); ?> (Class: <?php echo htmlspecialchars($subject['class_id']); ?>)
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
                        <h5 class="mb-3">Attendance Roster for **<?php echo htmlspecialchars($date); ?>**</h5>
                        <form method="post">
                            <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($selectedSubject); ?>">
                            <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead>
                                        <tr class="table-light">
                                            <th>Roll No</th>
                                            <th>Student Name</th>
                                            <th class="text-center">Present</th>
                                            <th class="text-center">Absent</th>
                                            <th class="text-center">Late</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        // Fetch current attendance status for pre-filling the form
                                        $currentAttendance = [];
                                        $stmt_fetch = $conn->prepare("SELECT student_id, status FROM attendance WHERE subject_id = ? AND date = ?");
                                        $stmt_fetch->bind_param("is", $selectedSubject, $date);
                                        $stmt_fetch->execute();
                                        $result_fetch = $stmt_fetch->get_result();
                                        while($row = $result_fetch->fetch_assoc()) {
                                            $currentAttendance[$row['student_id']] = $row['status'];
                                        }
                                        $stmt_fetch->close();
                                        ?>
                                        
                                        <?php foreach ($students as $student): 
                                            $currentStatus = $currentAttendance[$student['student_id']] ?? '';
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['roll_no']); ?></td>
                                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                                
                                                <td class="text-center">
                                                    <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" value="present" <?php echo ($currentStatus === 'present' ? 'checked' : ''); ?> required>
                                                </td>
                                                <td class="text-center">
                                                    <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" value="absent" <?php echo ($currentStatus === 'absent' ? 'checked' : ''); ?>>
                                                </td>
                                                <td class="text-center">
                                                    <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" value="late" <?php echo ($currentStatus === 'late' ? 'checked' : ''); ?>>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-save me-1"></i> Save Attendance</button>
                        </form>
                    <?php elseif ($selectedSubject): ?>
                        <div class="alert alert-info">No students found in the class assigned to this subject.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>