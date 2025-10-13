<?php
// ========================================================
// Assigned Classes Page
// Shows which teacher is assigned to which class
// ========================================================

require_once '../config.php';
requireRole('admin'); // Only admin can access

$pageTitle = 'Assigned Classes';

// Initialize array and error message
$assignments = [];
$error = '';

// --- Fetch assigned classes ---
$query = "
    SELECT tc.id, t.full_name AS teacher_name, c.class_name, tc.assigned_at
    FROM teacher_classes tc
    JOIN teachers t ON tc.teacher_id = t.teacher_id
    JOIN classes c ON tc.class_id = c.class_id
    ORDER BY tc.assigned_at DESC
";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $assignments[] = $row;
    }
} else {
    $error = 'Failed to load assigned classes. Please try again later.';
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- ======================================================
     Main Content
====================================================== -->
<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="mb-2">Assigned Classes</h2>
                    <p class="text-muted mb-3">View all teacher-class assignments in the system.</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Teacher</th>
                                    <th>Class</th>
                                    <th>Assigned On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($assignments)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">
                                            <i class="fas fa-info-circle me-1"></i>
                                            No assignments found.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($assignments as $i => $row): ?>
                                        <tr>
                                            <td><?php echo $i + 1; ?></td>
                                            <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                            <td><?php echo date('M d, Y - h:i A', strtotime($row['assigned_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <a href="assign-class.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Assign New Class
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>