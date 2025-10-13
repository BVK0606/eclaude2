<?php
// --- Attendance Records Page ---
// This page allows the admin to view student attendance by Class, Subject, and Date.

require_once '../config.php';
requireRole('admin');

$pageTitle = 'Attendance Records';

$error = '';
$records = [];

// --- Get filter values from URL ---
$classId = $_GET['class_id'] ?? '';
$subjectId = $_GET['subject_id'] ?? '';
$date = $_GET['date'] ?? '';

// --- Fetch Classes for Dropdown ---
$classes = [];
$classQuery = mysqli_query($conn, "SELECT class_id, class_name FROM classes ORDER BY class_name");
while ($row = mysqli_fetch_assoc($classQuery)) {
    $classes[] = $row;
}

// --- Fetch Subjects for Dropdown ---
$subjects = [];
$subjectQuery = mysqli_query($conn, "SELECT subject_id, subject_name FROM subjects ORDER BY subject_name");
while ($row = mysqli_fetch_assoc($subjectQuery)) {
    $subjects[] = $row;
}

// --- Fetch Attendance Records ---
if (!empty($classId) && !empty($subjectId) && !empty($date)) {
    $query = "
        SELECT s.roll_no, s.full_name, a.status
        FROM attendance a
        JOIN students s ON a.student_id = s.student_id
        WHERE s.class_id = '$classId' AND a.subject_id = '$subjectId' AND a.date = '$date'
        ORDER BY s.roll_no
    ";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $records[] = $row;
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- ====== Attendance Records Page ====== -->
<div class="main-content">
    <div class="content">
        <div class="dashboard-card mb-4">
            <h2 class="mb-2">Attendance Records</h2>
            <p class="text-muted mb-3">View student attendance by selecting Class, Subject, and Date.</p>

            <!-- Filter Form -->
            <form method="get" class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-select" required>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?php echo $c['class_id']; ?>" 
                                <?php echo ($classId == $c['class_id']) ? 'selected' : ''; ?>>
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
                                <?php echo ($subjectId == $s['subject_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['subject_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date); ?>" required>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Show Records
                    </button>
                </div>
            </form>

            <!-- Attendance Records Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Status</th>
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
                                    <td>
                                        <?php if ($row['status'] == 'present'): ?>
                                            <span class="badge bg-success">Present</span>
                                        <?php elseif ($row['status'] == 'absent'): ?>
                                            <span class="badge bg-danger">Absent</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Other</span>
                                        <?php endif; ?>
                                    </td>
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