<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Manage Teachers';

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $teacherId = $_GET['delete'];
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Get user_id from teacher record
        $stmt = $db->prepare("SELECT user_id FROM teachers WHERE teacher_id = ?");
        $stmt->execute([$teacherId]);
        $teacher = $stmt->fetch();
        
        if ($teacher) {
            // Delete teacher (this will cascade to delete the user due to foreign key constraint)
            $stmt = $db->prepare("DELETE FROM teachers WHERE teacher_id = ?");
            $stmt->execute([$teacherId]);
            
            $_SESSION['success'] = 'Teacher deleted successfully.';
        } else {
            $_SESSION['error'] = 'Teacher not found.';
        }
        
        header('Location: manage-teachers.php');
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to delete teacher. Please try again later.';
        error_log("Delete teacher error: " . $e->getMessage());
        header('Location: manage-teachers.php');
        exit;
    }
}

// Get all teachers with their details
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("
        SELECT t.teacher_id, t.full_name, t.qualification, t.experience, t.created_at,
               u.email
        FROM teachers t
        LEFT JOIN users u ON t.user_id = u.id
        ORDER BY t.created_at DESC
    ");
    $teachers = $stmt->fetchAll();
} catch (PDOException $e) {
    $teachers = [];
    error_log("Fetch teachers error: " . $e->getMessage());
}

// Check for messages from other pages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="mb-2">Manage Teachers</h2>
                            <p class="text-muted mb-0">View, edit, and manage all teachers in the system.</p>
                        </div>
                        <a href="add-teacher.php" class="btn btn-primary">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Add New Teacher
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <div class="table-header">
                        <h5 class="table-title">All Teachers</h5>
                        <div class="d-flex">
                            <input type="text" class="form-control form-control-sm me-2" placeholder="Search teachers..." id="searchInput">
                            <button class="btn btn-sm btn-outline-primary" onclick="exportTableToCSV('teachersTable', 'teachers.csv')">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                        </div>
                    </div>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table" id="teachersTable">
                            <thead>
                                <tr>
                                    <th>Teacher Name</th>
                                    <th>Email</th>
                                    <th>Qualification</th>
                                    <th>Experience</th>
                                    <th>Added Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($teachers)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-chalkboard-teacher fa-2x mb-2 d-block"></i>
                                            No teachers found. <a href="add-teacher.php">Add your first teacher</a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                        <?php echo strtoupper(substr($teacher['full_name'], 0, 1)); ?>
                                                    </div>
                                                    <span><?php echo htmlspecialchars($teacher['full_name']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['qualification'] ?: 'N/A'); ?></td>
                                            <td>
                                                <?php if ($teacher['experience']): ?>
                                                    <span class="badge bg-info"><?php echo $teacher['experience']; ?> years</span>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($teacher['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="edit-teacher.php?id=<?php echo $teacher['teacher_id']; ?>" class="btn btn-sm btn-outline-success" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            title="Delete" 
                                                            onclick="confirmDelete(<?php echo $teacher['teacher_id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
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
</div>

<?php
$pageScripts = "
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('#teachersTable tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
    
    // Confirm delete
    function confirmDelete(teacherId) {
        if (confirm('Are you sure you want to delete this teacher? This action cannot be undone.')) {
            window.location.href = 'manage-teachers.php?delete=' + teacherId;
        }
    }
    
    // Export to CSV
    function exportTableToCSV(tableId, filename) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const rows = table.querySelectorAll('tr');
        const csvContent = [];
        
        rows.forEach(row => {
            const cols = row.querySelectorAll('td, th');
            const rowData = [];
            
            cols.forEach(col => {
                // Skip action columns
                if (!col.querySelector('.btn-group')) {
                    rowData.push('\"' + col.textContent.replace(/\"/g, '\"\"') + '\"');
                }
            });
            
            csvContent.push(rowData.join(','));
        });
        
        const csvString = csvContent.join('\\n');
        const blob = new Blob([csvString], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.click();
        
        window.URL.revokeObjectURL(url);
    }
";

include '../includes/footer.php';
?>