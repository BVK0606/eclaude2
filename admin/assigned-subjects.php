<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Assigned Subjects';

// --- Fetch Assigned Subjects ---
$sql = "
    SELECT 
        s.subject_id, 
        s.subject_name, 
        t.full_name AS teacher_name,
        c.class_name
    FROM subjects s
    LEFT JOIN teachers t ON s.teacher_id = t.teacher_id
    LEFT JOIN classes c ON s.class_id = c.class_id
    ORDER BY s.subject_name
";

$result = mysqli_query($conn, $sql);
$assignments = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $assignments[] = $row;
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="mb-2">Assigned Subjects</h2>
                    <p class="text-muted mb-3">View all subjects and their assigned teachers and classes.</p>

                    <?php if (empty($assignments)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-book fa-3x mb-3 d-block"></i>
                            <p>No subjects have been assigned yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Subject Name</th>
                                        <th>Teacher</th>
                                        <th>Class</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignments as $i => $row): ?>
                                        <tr>
                                            <td><?php echo $i + 1; ?></td>
                                            <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                            <td>
                                                <?php 
                                                    echo $row['teacher_name'] 
                                                        ? htmlspecialchars($row['teacher_name']) 
                                                        : '<span class="text-muted">Unassigned</span>'; 
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    echo $row['class_name'] 
                                                        ? htmlspecialchars($row['class_name']) 
                                                        : '<span class="text-muted">Not Linked</span>'; 
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>