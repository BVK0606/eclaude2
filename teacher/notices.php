<?php
require_once '../config.php';
requireRole('teacher');

$pageTitle = 'Teacher Notices';
include '../includes/header.php';
include '../includes/sidebar.php';

$db = Database::getInstance()->getConnection();
$teacherId = null;
$notices = [];
$error = '';

// Get teacher's internal teacher_id
if (!empty($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT teacher_id FROM teachers WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $teacherRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $teacherId = $teacherRow['teacher_id'] ?? null;
}

// Fetch notices for teacher's classes/subjects
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
                        <h2 class="card-title mb-4"><i class="fas fa-bullhorn me-2"></i>Notices</h2>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <?php if ($notices): ?>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Message</th>
                                            <!-- Subject and Class columns removed -->
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($notices as $notice): ?>
                                            <tr>
                                                <td class="fw-bold"><?php echo htmlspecialchars($notice['title']); ?></td>
                                                <td><?php echo htmlspecialchars($notice['description']); ?></td>
                                                <!-- Subject and Class cells removed -->
                                                <td><?php echo date('d M Y', strtotime($notice['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No notices found for your classes/subjects.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
