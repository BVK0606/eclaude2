<?php
require_once '../config.php';
requireRole('admin'); // Only admin can access this page

$pageTitle = 'Manage Notices';

// Initialize message variables
$error = '';
$success = '';

// --- Add New Notice ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_notice'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Simple validation
    if (empty($title) || empty($description)) {
        $error = "Please fill in all fields.";
    } else {
        $userId = $_SESSION['user_id'] ?? 0;
        $query = "INSERT INTO notices (title, description, created_by) VALUES ('$title', '$description', '$userId')";
        if (mysqli_query($conn, $query)) {
            $success = "Notice added successfully!";
            $_POST = []; // Clear form
        } else {
            $error = "Failed to add notice. Please try again.";
        }
    }
}

// --- Delete Notice ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $noticeId = $_GET['delete'];
    $query = "DELETE FROM notices WHERE notice_id = '$noticeId'";
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Notice deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete notice.";
    }
    header("Location: notices.php");
    exit;
}

// --- Fetch All Notices ---
$notices = [];
$result = mysqli_query($conn, "SELECT * FROM notices ORDER BY created_at DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $notices[] = $row;
}

// --- Display messages from session ---
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2>Manage Notices</h2>
                    <p class="text-muted">Post and manage important notices.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Add Notice Form -->
            <div class="col-md-5 mb-4">
                <div class="dashboard-card">
                    <h4>Post New Notice</h4>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Notice Title *</label>
                            <input type="text" name="title" class="form-control" 
                                   placeholder="Enter notice title"
                                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea name="description" rows="5" class="form-control"
                                      placeholder="Enter notice description" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" name="add_notice" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane me-1"></i>Post Notice
                        </button>
                    </form>
                </div>
            </div>

            <!-- Notice List -->
            <div class="col-md-7">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">All Notices</h4>
                        <span class="badge bg-primary"><?php echo count($notices); ?> total</span>
                    </div>

                    <?php if (empty($notices)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-bullhorn fa-3x mb-3"></i>
                            <p>No notices yet. Add one above.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notices as $notice): ?>
                            <div class="mb-3 border-bottom pb-2">
                                <div class="d-flex justify-content-between">
                                    <h5><?php echo htmlspecialchars($notice['title']); ?></h5>
                                    <a href="?delete=<?php echo $notice['notice_id']; ?>" 
                                       onclick="return confirm('Delete this notice?');" 
                                       class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                                <p><?php echo nl2br(htmlspecialchars($notice['description'])); ?></p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo date('M d, Y h:i A', strtotime($notice['created_at'])); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
