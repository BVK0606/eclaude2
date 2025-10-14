<?php
// Core setup
require_once '../config.php';
requireRole('teacher');

// Page title
$pageTitle = 'Teacher Notices';
// Include layout
include '../includes/header.php';
include '../includes/sidebar.php';

// Init variables
$teacherId = null;
$notices = [];
$error = '';

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

// 2. Fetch notices for teacher
if ($teacherId) {
    // Select notices targeted at 'all' or 'teacher' roles, and are active
    $stmt = $conn->prepare("
        SELECT n.notice_id, n.title, n.description, n.created_at
        FROM notices n
        WHERE n.target_role IN (?, ?) AND n.is_active = 1
        ORDER BY n.created_at DESC
    ");
    $target1 = "all";
    $target2 = "teacher";
    $stmt->bind_param("ss", $target1, $target2); // Bind target roles
    $stmt->execute();
    $result = $stmt->get_result();
    $notices = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<div class="main-content">
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="dashboard-card mb-4">
                    <div class="card-body">
                        <h2 class="card-title mb-4"><i class="fas fa-bullhorn me-2 text-primary"></i>General Notices
                        </h2>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <?php if (!empty($notices)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead>
                                        <tr class="table-light">
                                            <th>Title</th>
                                            <th>Message Preview</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($notices as $notice): ?>
                                            <tr>
                                                <td class="fw-bold"><?php echo htmlspecialchars($notice['title']); ?></td>
                                                <td><?php echo substr(htmlspecialchars($notice['description']), 0, 70); ?>...
                                                </td>
                                                <td><?php echo date('d M Y', strtotime($notice['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No active notices found for teachers at this time.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>