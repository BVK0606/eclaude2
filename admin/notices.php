<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Manage Notices';

// Handle form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        
        // Validation
        if (empty($title) || empty($description)) {
            $error = 'Please fill in all required fields.';
        } else {
            try {
                $db = Database::getInstance()->getConnection();
                
                // Insert notice with created_by
                $userId = $_SESSION['user_id'] ?? null;
                $stmt = $db->prepare("INSERT INTO notices (title, description, created_by) VALUES (?, ?, ?)");
                $stmt->execute([$title, $description, $userId]);

                $success = 'Notice posted successfully!';

                // Clear form data on success
                $_POST = [];
                
            } catch (PDOException $e) {
                $error = 'Failed to post notice. Please try again later.';
                error_log("Add notice error: " . $e->getMessage());
            }
        }
    }
}

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $noticeId = $_GET['delete'];
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Delete notice
        $stmt = $db->prepare("DELETE FROM notices WHERE notice_id = ?");
        $stmt->execute([$noticeId]);
        
        $_SESSION['success'] = 'Notice deleted successfully.';
        
        header('Location: notices.php');
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to delete notice. Please try again later.';
        error_log("Delete notice error: " . $e->getMessage());
        header('Location: notices.php');
        exit;
    }
}

// Get all notices
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("
        SELECT notice_id, title, description, created_at
        FROM notices
        ORDER BY created_at DESC
    ");
    $notices = $stmt->fetchAll();
} catch (PDOException $e) {
    $notices = [];
    error_log("Fetch notices error: " . $e->getMessage());
}

// Check for messages from other pages
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
                    <h2 class="mb-2">Manage Notices</h2>
                    <p class="text-muted mb-0">Post and manage important notices for students and teachers.</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-5 mb-4">
                <div class="dashboard-card">
                    <h4 class="mb-3">Post New Notice</h4>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">
                                <i class="fas fa-heading me-1"></i>Notice Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="title" 
                                   name="title" 
                                   placeholder="Enter notice title"
                                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                                   required>
                            <div class="invalid-feedback">
                                Please enter a title for the notice.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Notice Description <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="5" 
                                      placeholder="Enter notice description"
                                      required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            <div class="invalid-feedback">
                                Please enter a description for the notice.
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane me-2"></i>Post Notice
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="col-md-7">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="mb-0">All Notices</h4>
                        <span class="badge bg-primary"><?php echo count($notices); ?> notices</span>
                    </div>
                    
                    <?php if (empty($notices)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-bullhorn fa-3x mb-3 d-block"></i>
                            <p>No notices yet. Post your first notice to get started.</p>
                        </div>
                    <?php else: ?>
                        <div class="notices-list">
                            <?php foreach ($notices as $notice): ?>
                                <div class="notice-item">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></h5>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                title="Delete" 
                                                onclick="confirmDelete(<?php echo $notice['notice_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <p class="notice-description"><?php echo nl2br(htmlspecialchars($notice['description'])); ?></p>
                                    <div class="notice-meta text-muted small">
                                        <i class="fas fa-clock me-1"></i>
                                        Posted <?php echo date('M d, Y \a\t h:i A', strtotime($notice['created_at'])); ?>
                                    </div>
                                </div>
                                <?php if ($notice !== end($notices)): ?>
                                    <hr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$pageScripts = "
    // Confirm delete
    function confirmDelete(noticeId) {
        if (confirm('Are you sure you want to delete this notice? This action cannot be undone.')) {
            window.location.href = 'notices.php?delete=' + noticeId;
        }
    }
";

include '../includes/footer.php';
?>