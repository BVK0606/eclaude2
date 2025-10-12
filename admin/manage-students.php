<?php
require_once '../config.php';
requireRole('admin');

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $studentId = (int)$_GET['delete'];

    // Get user_id of student
    $res = mysqli_query($conn, "SELECT user_id FROM students WHERE student_id='$studentId'");
    if (mysqli_num_rows($res) > 0) {
        $user = mysqli_fetch_assoc($res);

        // Delete student record
        mysqli_query($conn, "DELETE FROM students WHERE student_id='$studentId'");
        // Optionally delete user record
        mysqli_query($conn, "DELETE FROM users WHERE id='".$user['user_id']."'");

        $_SESSION['success'] = 'Student deleted successfully.';
    } else {
        $_SESSION['error'] = 'Student not found.';
    }
    header('Location: manage-students.php');
    exit;
}

// Fetch all students
$studentsRes = mysqli_query($conn, "
    SELECT s.student_id, s.roll_no, s.full_name, s.dob, s.address, s.created_at, c.class_name, u.email
    FROM students s
    LEFT JOIN classes c ON s.class_id=c.class_id
    LEFT JOIN users u ON s.user_id=u.id
    ORDER BY s.created_at DESC
");

$students = mysqli_fetch_all($studentsRes, MYSQLI_ASSOC);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card d-flex justify-content-between align-items-center">
                    <div>
                        <h2>Manage Students</h2>
                        <p>View and manage all students.</p>
                    </div>
                    <a href="add-student.php" class="btn btn-primary">Add Student</a>
                </div>
            </div>
        </div>

        <?php if($success) echo showAlert($success,'success'); ?>
        <?php if($error) echo showAlert($error,'danger'); ?>

        <div class="row">
            <div class="col-12">
                <input type="text" id="searchInput" class="form-control mb-2" placeholder="Search students...">
                <table class="table table-bordered" id="studentsTable">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Class</th>
                            <th>DOB</th>
                            <th>Added Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($students)): ?>
                            <tr><td colspan="7" class="text-center">No students found.</td></tr>
                        <?php else: ?>
                            <?php foreach($students as $s): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($s['roll_no']); ?></td>
                                    <td><?php echo htmlspecialchars($s['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($s['email']); ?></td>
                                    <td><?php echo htmlspecialchars($s['class_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo $s['dob'] ? date('d-m-Y', strtotime($s['dob'])) : 'N/A'; ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($s['created_at'])); ?></td>
                                    <td>
                                        <a href="edit-student.php?id=<?php echo $s['student_id']; ?>" class="btn btn-sm btn-success">Edit</a>
                                        <a href="manage-students.php?delete=<?php echo $s['student_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this student?');">Delete</a>
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

<script>
// Simple search filter
document.getElementById('searchInput').addEventListener('input', function(){
    const val = this.value.toLowerCase();
    document.querySelectorAll('#studentsTable tbody tr').forEach(row=>{
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>

<?php include '../includes/footer.php'; ?>
