<?php
require_once '../config.php';
requireRole('student');
$pageTitle = 'Notices';
include '../includes/header.php';
include '../includes/sidebar.php';
$db = Database::getInstance()->getConnection();
$notices = [];
$stmt = $db->prepare('SELECT title, description, created_at FROM notices WHERE target_role IN ("all", "student") AND is_active = 1 ORDER BY created_at DESC');
$stmt->execute();
$notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="main-content">
    <div class="content">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="card-title mb-4"><i class="fas fa-bullhorn me-2"></i>Notices</h2>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
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
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
