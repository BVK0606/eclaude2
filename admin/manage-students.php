<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Manage Students';

// Handle delete action with CSRF protection and validation
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && isset($_GET['csrf_token'])) {
    $studentId = (int)$_GET['delete'];
    $csrfToken = $_GET['csrf_token'];
    if (!validateCSRFToken($csrfToken)) {
        $_SESSION['error'] = 'Invalid security token. Please try again.';
        header('Location: manage-students.php');
        exit;
    }
    try {
        $db = Database::getInstance()->getConnection();
        // Get user_id from student record
        $stmt = $db->prepare("SELECT user_id FROM students WHERE student_id = ?");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch();
        if ($student) {
            // Delete student (this will cascade to delete the user due to foreign key constraint)
            $stmt = $db->prepare("DELETE FROM students WHERE student_id = ?");
            $stmt->execute([$studentId]);
            $_SESSION['success'] = 'Student deleted successfully.';
        } else {
            $_SESSION['error'] = 'Student not found.';
        }
        header('Location: manage-students.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to delete student. Please try again later.';
        error_log("Delete student error: " . $e->getMessage());
        header('Location: manage-students.php');
        exit;
    }
}

// Get all students with their details (optimized)
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query(
        "SELECT s.student_id, s.roll_no, s.full_name, s.dob, s.address, s.created_at, c.class_name, u.email
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN users u ON s.user_id = u.id
        ORDER BY s.created_at DESC"
    );
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    $students = [];
    error_log("Fetch students error: " . $e->getMessage());
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
                            <h2 class="mb-2">Manage Students</h2>
                            <p class="text-muted mb-0">View, edit, and manage all students in the system.</p>
                        </div>
                        <a href="add-student.php" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Add New Student
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <div class="table-header">
                        <h5 class="table-title">All Students</h5>
                        <div class="d-flex">
                            <input type="text" class="form-control form-control-sm me-2" placeholder="Search students..." id="searchInput">
                            <button class="btn btn-sm btn-outline-primary" onclick="exportTableToCSV('studentsTable', 'students.csv')">
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
                        <table class="table" id="studentsTable">
                            <thead>
                                <tr>
                                    <th>Roll No</th>
                                    <th>Student Name</th>
                                    <th>Email</th>
                                    <th>Class</th>
                                    <th>Date of Birth</th>
                                    <th>Added Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($students)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                            No students found. <a href="add-student.php">Add your first student</a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-semibold"><?php echo htmlspecialchars($student['roll_no']); ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                        <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                                                    </div>
                                                    <span><?php echo htmlspecialchars($student['full_name']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($student['class_name'] ?? 'Not Assigned'); ?></span>
                                            </td>
                                            <td><?php echo $student['dob'] ? date('M d, Y', strtotime($student['dob'])) : 'N/A'; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="edit-student.php?id=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-outline-success" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            title="Delete" 
                                                            onclick="confirmDelete(<?php echo $student['student_id']; ?>)">
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
        const rows = document.querySelectorAll('#studentsTable tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
    
    // Confirm delete
    function confirmDelete(studentId) {
        if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
            window.location.href = 'manage-students.php?delete=' + studentId;
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