<?php
// Core setup
require_once '../config.php';
requireRole('student');
$pageTitle = 'Notices';

// Init variables
$notices = [];

// --- Database logic ---
if (isset($conn)) {
    // Fetch notices targeted at 'all' or 'student' roles, and are active
    $stmt = $conn->prepare("
        SELECT title, description, created_at 
        FROM notices 
        WHERE target_role IN (?, ?) AND is_active = 1 
        ORDER BY created_at DESC
    ");
    $target1 = "all";
    $target2 = "student";
    $stmt->bind_param("ss", $target1, $target2);
    $stmt->execute();
    $result = $stmt->get_result();
    $notices = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Include layout
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="dashboard-card mb-4">
            <h2 class="card-title mb-4"><i class="fas fa-bullhorn me-2 text-primary"></i>General Notices</h2>
            
            <?php if (empty($notices)): ?>
                <div class="alert alert-info">No active notices found at this time.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr class="table-light">
                                <th>Title</th>
                                <th>Message</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notices as $notice): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($notice['title']); ?></td>
                                    <td><?php echo htmlspecialchars($notice['description']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($notice['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>