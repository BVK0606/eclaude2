<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Manage Teachers';

// Handle delete
// Handle delete action
// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $teacherId = $_GET['delete'];

    try {
        $db = Database::getInstance()->getConnection();

        // Set teacher_id to NULL in subjects table before deleting
        $stmt = $db->prepare("UPDATE subjects SET teacher_id = NULL WHERE teacher_id = ?");
        $stmt->execute([$teacherId]);

        // Delete teacher from database
        $stmt = $db->prepare("DELETE FROM teachers WHERE teacher_id = ?");
        $stmt->execute([$teacherId]);

        $_SESSION['success'] = 'Teacher deleted successfully.';
        header('Location: manage-teachers.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to delete teacher. Please try again later.';
        error_log("Teacher delete error: " . $e->getMessage());
        header('Location: manage-teachers.php');
        exit;
    }
}

// Get all teachers
$res = mysqli_query($conn, "
    SELECT t.teacher_id, t.full_name, t.qualification, t.experience, t.created_at,
           u.email
    FROM teachers t
    LEFT JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
");
$teachers = [];
while ($row = mysqli_fetch_assoc($res)) {
    $teachers[] = $row;
}

// Messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card d-flex justify-content-between align-items-center">
                    <div>
                        <h2>Manage Teachers</h2>
                        <p class="text-muted">View, edit, and manage all teachers in the system.</p>
                    </div>
                    <a href="add-teacher.php" class="btn btn-primary">
                        Add New Teacher
                    </a>
                </div>
            </div>
        </div>

        <?php if ($success)
            echo "<div class='alert alert-success'>$success</div>"; ?>
        <?php if ($error)
            echo "<div class='alert alert-danger'>$error</div>"; ?>

        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table" id="teachersTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Qualification</th>
                                <th>Experience</th>
                                <th>Added Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($teachers)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No teachers found. <a href="add-teacher.php">Add a
                                            teacher</a></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($teachers as $t): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($t['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($t['email']); ?></td>
                                        <td><?php echo htmlspecialchars($t['qualification'] ?: 'N/A'); ?></td>
                                        <td><?php echo $t['experience'] ? $t['experience'] . ' years' : 'N/A'; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($t['created_at'])); ?></td>
                                        <td>
                                            <a href="edit-teacher.php?id=<?php echo $t['teacher_id']; ?>"
                                                class="btn btn-sm btn-success">Edit</a>
                                            <a href="manage-teachers.php?delete=<?php echo $t['teacher_id']; ?>"
                                                onclick="return confirm('Delete this teacher?');"
                                                class="btn btn-sm btn-danger">Delete</a>
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
</div>

<?php include '../includes/footer.php'; ?>