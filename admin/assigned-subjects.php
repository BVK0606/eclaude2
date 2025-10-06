<?php
require_once '../config.php';
requireRole('admin');
$pageTitle = 'Assigned Subjects';

$db = Database::getInstance()->getConnection();
$sql = "SELECT s.subject_id, s.subject_name, t.full_name AS teacher_name
        FROM subjects s
        LEFT JOIN teachers t ON s.teacher_id = t.teacher_id
        ORDER BY s.subject_name";
$assignments = $db->query($sql)->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="mb-2">Assigned Subjects</h2>
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Subject</th>
                                <th>Teacher</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($assignments) === 0): ?>
                            <tr><td colspan="3" class="text-center">No assignments found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($assignments as $i => $row): ?>
                                <tr>
                                    <td><?php echo $i+1; ?></td>
                                    <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                    <td><?php echo $row['teacher_name'] ? htmlspecialchars($row['teacher_name']) : '<span class="text-muted">Unassigned</span>'; ?></td>
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
