<?php
require_once '../config.php';
requireRole('teacher');

$pageTitle = 'Mark Attendance';
include '../includes/header.php';
include '../includes/sidebar.php';

$db = Database::getInstance()->getConnection();
$teacher_id = $_SESSION['user_id'];

// Get teacher's internal teacher_id
$stmt = $db->prepare('SELECT teacher_id FROM teachers WHERE user_id = ?');
$stmt->execute([$teacher_id]);
$teacherRow = $stmt->fetch();
$teacherId = $teacherRow ? $teacherRow['teacher_id'] : null;

// Get classes assigned to this teacher
// Get subjects assigned to this teacher (global, no class)
$subjects = [];
if ($teacherId) {
    $stmt = $db->prepare('SELECT subject_id, subject_name FROM subjects WHERE teacher_id = ?');
    $stmt->execute([$teacherId]);
    $subjects = $stmt->fetchAll();
}

$selectedSubject = $_POST['subject_id'] ?? $_GET['subject_id'] ?? '';
$attendanceSaved = false;

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance']) && $selectedSubject) {
    $date = date('Y-m-d');
    $attendance = $_POST['attendance']; // [student_id => 'present'|'absent']
    foreach ($attendance as $student_id => $status) {
        // Upsert attendance (replace if exists)
        $stmt = $db->prepare('INSERT INTO attendance (student_id, subject_id, date, status) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)');
        $stmt->execute([$student_id, $selectedSubject, $date, $status]);
    }
    $attendanceSaved = true;
}

// Get all students for marking attendance (since subjects are global)
$students = [];
if ($selectedSubject) {
    $stmt = $db->query('SELECT student_id, full_name FROM students ORDER BY full_name');
    $students = $stmt->fetchAll();
}
?>

<div class="main-content">
	<div class="content">
		<h2 class="mb-4">Mark Attendance</h2>
		<?php if ($attendanceSaved): ?>
			<div class="alert alert-success">Attendance saved successfully!</div>
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
		    </div>
		</form>
		</form>

		<?php if ($selectedSubject && $students): ?>
			<form method="post">
				<input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($selectedSubject); ?>">
				<table class="table table-bordered align-middle">
					<thead>
						<tr>
							<th>Student Name</th>
							<th class="text-center">Present</th>
							<th class="text-center">Absent</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($students as $student): ?>
							<tr>
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
				<button type="submit" class="btn btn-primary">Save Attendance</button>
			</form>
		<?php elseif ($selectedSubject): ?>
			<div class="alert alert-info">No students found in this class.</div>
		<?php endif; ?>
	</div>
</div>

<?php include '../includes/footer.php'; ?>
