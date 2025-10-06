<?php
require_once '../config.php';
requireRole('teacher');

$db = Database::getInstance()->getConnection();
$teacherId = null;
// $subjects = [];
$error = '';
$success = false;

// Get teacher's internal teacher_id
if (!empty($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT teacher_id FROM teachers WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $teacherRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $teacherId = $teacherRow['teacher_id'] ?? null;
}

// Subjects not needed for notices

// Handle create/edit
$editId = $_GET['edit'] ?? null;
$editNotice = null;
if ($editId) {
    $stmt = $db->prepare('SELECT * FROM notices WHERE notice_id = ?');
    $stmt->execute([$editId]);
    $editNotice = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $message = $_POST['message'] ?? '';
    $description = $_POST['description'] ?? '';
    if ($title && $description) {
        $userId = $_SESSION['user_id'] ?? null;
        if (isset($_POST['notice_id']) && $_POST['notice_id']) {
            // Edit
            $stmt = $db->prepare('UPDATE notices SET title = ?, description = ?, created_by = ? WHERE notice_id = ?');
            $stmt->execute([$title, $description, $userId, $_POST['notice_id']]);
            header('Location: notices-manage.php');
            exit;
        } else {
            // Create
            $stmt = $db->prepare('INSERT INTO notices (title, description, created_by, created_at) VALUES (?, ?, ?, NOW())');
            $stmt->execute([$title, $description, $userId]);
            header('Location: notices-manage.php');
            exit;
        }
    } else {
        $error = 'All fields are required.';
    }
}

$pageTitle = 'Manage Notices';
include '../includes/header.php';
include '../includes/sidebar.php';

// Fetch all notices for teacher's subjects
$notices = [];
if ($teacherId) {
    $stmt = $db->prepare('SELECT n.notice_id, n.title, n.description, n.created_at
        FROM notices n
        WHERE n.target_role IN ("all", "teacher") AND n.is_active = 1
        ORDER BY n.created_at DESC');
    $stmt->execute();
    $notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="main-content">
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h2 class="card-title mb-4"><i class="fas fa-bullhorn me-2"></i>Manage Notices</h2>
                        <?php if ($success): ?>
                            <div class="alert alert-success">Notice saved successfully!</div>
                        <?php elseif ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form method="post" class="mb-4">
                            <input type="hidden" name="notice_id" value="<?php echo $editNotice['notice_id'] ?? ''; ?>">
                            <div class="mb-2">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" name="title" id="title" class="form-control" required value="<?php echo htmlspecialchars($editNotice['title'] ?? ''); ?>">
                            </div>
                            <div class="mb-2">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3" required><?php echo htmlspecialchars($editNotice['description'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Notice</button>
                            <?php if ($editNotice): ?>
                                <a href="notices-manage.php" class="btn btn-secondary ms-2">Cancel</a>
                            <?php endif; ?>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($notices as $notice): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($notice['title']); ?></td>
                                            <td><?php echo htmlspecialchars($notice['description']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($notice['created_at'])); ?></td>
                                            <td>
                                                <a href="notices-manage.php?edit=<?php echo $notice['notice_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
