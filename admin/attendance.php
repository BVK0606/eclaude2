<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Manage Attendance';

$error = '';
$success = '';

// --- Handle Attendance Submission ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_attendance'])) {
    $classId = sanitizeInput($_POST['class_id'] ?? '');
    $subjectId = sanitizeInput($_POST['subject_id'] ?? '');
    $date = sanitizeInput($_POST['date'] ?? '');
    $attendanceData = $_POST['attendance'] ?? [];

    if (empty($classId) || empty($subjectId) || empty($date)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Delete existing attendance before inserting new records
        $deleteQuery = "
            DELETE a FROM attendance a
            JOIN students s ON a.student_id = s.student_id
            WHERE a.subject_id = '$subjectId' AND a.date = '$date' AND s.class_id = '$classId'
        ";
        if (mysqli_query($conn, $deleteQuery)) {
            foreach ($attendanceData as $studentId => $status) {
                if (!empty($status)) {
                    $insertQuery = "
                        INSERT INTO attendance (student_id, subject_id, date, status)
                        VALUES ('$studentId', '$subjectId', '$date', '$status')
                    ";
                    mysqli_query($conn, $insertQuery);
                }
            }
            $success = 'Attendance marked successfully!';
        } else {
            $error = 'Failed to save attendance. Please try again.';
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

<!-- ====== Manage Attendance Page ====== -->
<div class="main-content">
    <div class="content">

        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2>Manage Attendance</h2>
                    <p class="text-muted">Mark and view student attendance records.</p>
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
                            <div class="col-md-4">
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

                            <div class="col-md-4">
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

                            <div class="col-md-4">
                                <label class="form-label">Date *</label>
                                <input type="date" class="form-control" name="date" 
                                       value="<?php echo htmlspecialchars($_POST['date'] ?? date('Y-m-d')); ?>" required>
                            </div>
                        </div>

                        <?php if (!empty($students)): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Roll No</th>
                                            <th>Student Name</th>
                                            <th>Attendance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['roll_no']); ?></td>
                                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <input type="radio" 
                                                               class="btn-check" 
                                                               name="attendance[<?php echo $student['student_id']; ?>]" 
                                                               id="present_<?php echo $student['student_id']; ?>" 
                                                               value="present">
                                                        <label class="btn btn-outline-success" for="present_<?php echo $student['student_id']; ?>">Present</label>

                                                        <input type="radio" 
                                                               class="btn-check" 
                                                               name="attendance[<?php echo $student['student_id']; ?>]" 
                                                               id="absent_<?php echo $student['student_id']; ?>" 
                                                               value="absent">
                                                        <label class="btn btn-outline-danger" for="absent_<?php echo $student['student_id']; ?>">Absent</label>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" name="mark_attendance" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Save Attendance
                                </button>
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
                                    <p>Please select a class to load students.</p>
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