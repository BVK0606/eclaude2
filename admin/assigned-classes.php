<?php
require_once '../config.php';
requireRole('admin');
$pageTitle = 'Assigned Classes';

$db = Database::getInstance()->getConnection();
$sql = "SELECT tc.id, t.full_name AS teacher_name, c.class_name, tc.assigned_at
        FROM teacher_classes tc
        JOIN teachers t ON tc.teacher_id = t.teacher_id
        JOIN classes c ON tc.class_id = c.class_id
        ORDER BY tc.assigned_at DESC";
$assignments = $db->query($sql)->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="mb-2">Assigned Classes</h2>
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Teacher</th>
                                <th>Class</th>
                                <th>Assigned At</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($assignments) === 0): ?>
                            <tr><td colspan="4" class="text-center">No assignments found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($assignments as $i => $row): ?>
                                <tr>
                                    <td><?php echo $i+1; ?></td>
                                    <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['assigned_at']); ?></td>
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
