<?php
// Core setup
require_once '../config.php';
requireRole('teacher');

// Init variables
$teacherId = null;
$error = '';
$success = false;
$editId = $_GET['edit'] ?? null;
$editNotice = null;
$pageTitle = 'Manage Notices';

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

// 2. Handle edit view (Fetch data to pre-fill form)
if ($editId) {
    $stmt = $conn->prepare("SELECT notice_id, title, description FROM notices WHERE notice_id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editNotice = $result->fetch_assoc();
    $stmt->close();
}

// 3. Handle POST submission (Create/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user inputs
    $noticeId = $_POST['notice_id'] ?? null;
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $userId = $_SESSION['user_id'] ?? null; // Creator/Editor ID

    if ($title && $description && $userId) {
        if ($noticeId) {
            // EDIT operation
            $stmt = $conn->prepare("UPDATE notices SET title = ?, description = ?, created_by = ? WHERE notice_id = ?");
            // Binding types: s, s, i, i (title, description, created_by, notice_id)
            $stmt->bind_param("ssii", $title, $description, $userId, $noticeId);
            $stmt->execute();
            $stmt->close();
            $_SESSION['success'] = 'Notice updated successfully!';
            
        } else {
            // CREATE operation
            $stmt = $conn->prepare("INSERT INTO notices (title, description, created_by, target_role, is_active) VALUES (?, ?, ?, 'teacher', 1)");
            // Binding types: s, s, i (title, description, created_by)
            $stmt->bind_param("ssi", $title, $description, $userId);
            $stmt->execute();
            $stmt->close();
            $_SESSION['success'] = 'Notice created successfully!';
        }
        
        // Redirect after POST
        header('Location: notices-manage.php');
        exit;
        
    } else {
        $error = 'Title and description are required.';
    }
}

// 4. Fetch all notices (for the table list)
$notices = [];
if ($teacherId) {
    $stmt = $conn->prepare("
        SELECT n.notice_id, n.title, n.description, n.created_at
        FROM notices n
        WHERE n.target_role IN (?, ?) AND n.is_active = 1
        ORDER BY n.created_at DESC
    ");
    $target1 = "all";
    $target2 = "teacher";
    $stmt->bind_param("ss", $target1, $target2);
    $stmt->execute();
    $result = $stmt->get_result();
    $notices = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Handle session messages
$success = $_SESSION['success'] ?? false;
unset($_SESSION['success']);

// Include layout
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="dashboard-card mb-4">
                    <div class="card-body">
                        <h2 class="card-title mb-4"><i class="fas fa-bullhorn me-2 text-primary"></i>Manage Notices</h2>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php elseif ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <form method="post" class="mb-4">
                            <input type="hidden" name="notice_id" value="<?php echo htmlspecialchars($editNotice['notice_id'] ?? ''); ?>">
                            
                            <h4 class="mb-3"><?php echo $editNotice ? 'Edit Existing Notice' : 'Create New Notice'; ?></h4>

                            <div class="mb-3">
                                <label for="title" class="form-label">Title *</label>
                                <input type="text" name="title" id="title" class="form-control" required value="<?php echo htmlspecialchars($editNotice['title'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea name="description" id="description" class="form-control" rows="3" required><?php echo htmlspecialchars($editNotice['description'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> <?php echo $editNotice ? 'Update Notice' : 'Publish Notice'; ?>
                            </button>
                            <?php if ($editNotice): ?>
                                <a href="notices-manage.php" class="btn btn-secondary ms-2">Cancel Edit</a>
                            <?php endif; ?>
                        </form>
                        
                        <h4 class="mb-3 mt-5">Active Notices History</h4>
                        <div class="table-responsive">
                            <?php if (empty($notices)): ?>
                                <div class="alert alert-info">No notices created by you or for you have been found.</div>
                            <?php else: ?>
                                <table class="table table-striped table-hover align-middle">
                                    <thead>
                                        <tr class="table-light">
                                            <th>Title</th>
                                            <th>Message Preview</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($notices as $notice): ?>
                                            <tr>
                                                <td class="fw-bold"><?php echo htmlspecialchars($notice['title']); ?></td>
                                                <td><?php echo substr(htmlspecialchars($notice['description']), 0, 50); ?>...</td>
                                                <td><?php echo date('d M Y', strtotime($notice['created_at'])); ?></td>
                                                <td>
                                                    <a href="notices-manage.php?edit=<?php echo $notice['notice_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>