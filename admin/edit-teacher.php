<?php
require_once '../config.php';
requireRole('admin');

$pageTitle = 'Edit Teacher';

// Check if teacher ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage-teachers.php');
    exit;
}

$teacherId = $_GET['id'];

// Get teacher data
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT t.teacher_id, t.full_name, t.qualification, t.experience,
               u.email, u.uname as username
        FROM teachers t
        LEFT JOIN users u ON t.user_id = u.id
        WHERE t.teacher_id = ?
    ");
    $stmt->execute([$teacherId]);
    $teacher = $stmt->fetch();
    
    if (!$teacher) {
        $_SESSION['error'] = 'Teacher not found.';
        header('Location: manage-teachers.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Failed to fetch teacher data. Please try again later.';
    error_log("Fetch teacher error: " . $e->getMessage());
    header('Location: manage-teachers.php');
    exit;
}

// Handle form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $qualification = sanitizeInput($_POST['qualification'] ?? '');
        $experience = sanitizeInput($_POST['experience'] ?? '');
        
        // Validation
        if (empty($fullName) || empty($email)) {
            $error = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                $db = Database::getInstance()->getConnection();
                
                // Check if email already exists (excluding current teacher's user)
                $stmt = $db->prepare("
                    SELECT COUNT(*) FROM users u 
                    JOIN teachers t ON u.id = t.user_id 
                    WHERE u.email = ? AND t.teacher_id != ?
                ");
                $stmt->execute([$email, $teacherId]);
                $emailExists = $stmt->fetchColumn();
                
                if ($emailExists > 0) {
                    $error = 'Email already exists.';
                } else {
                    // Update teacher and user data
                    $db->beginTransaction();
                    
                    try {
                        // Update teacher
                        $stmt = $db->prepare("
                            UPDATE teachers 
                            SET full_name = ?, qualification = ?, experience = ? 
                            WHERE teacher_id = ?
                        ");
                        $stmt->execute([$fullName, $qualification, $experience, $teacherId]);
                        
                        // Update user email
                        $stmt = $db->prepare("
                            UPDATE users u
                            JOIN teachers t ON u.id = t.user_id
                            SET u.email = ?
                            WHERE t.teacher_id = ?
                        ");
                        $stmt->execute([$email, $teacherId]);
                        
                        $db->commit();
                        $success = 'Teacher updated successfully!';
                        
                        // Refresh teacher data
                        $stmt = $db->prepare("
                            SELECT t.teacher_id, t.full_name, t.qualification, t.experience,
                                   u.email, u.uname as username
                            FROM teachers t
                            LEFT JOIN users u ON t.user_id = u.id
                            WHERE t.teacher_id = ?
                        ");
                        $stmt->execute([$teacherId]);
                        $teacher = $stmt->fetch();
                        
                    } catch (Exception $e) {
                        $db->rollback();
                        throw $e;
                    }
                }
            } catch (PDOException $e) {
                $error = 'Failed to update teacher. Please try again later.';
                error_log("Update teacher error: " . $e->getMessage());
            }
        }
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
                    <h2 class="mb-2">Edit Teacher</h2>
                    <p class="text-muted mb-0">Update teacher information.</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">
                                    <i class="fas fa-id-card me-1"></i>Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="full_name" 
                                       name="full_name" 
                                       placeholder="Enter teacher's full name"
                                       value="<?php echo htmlspecialchars($teacher['full_name']); ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Please enter the teacher's full name.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user me-1"></i>Username
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       value="<?php echo htmlspecialchars($teacher['username']); ?>"
                                       disabled>
                                <div class="form-text">Username cannot be changed.</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email Address <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       placeholder="Enter email address"
                                       value="<?php echo htmlspecialchars($teacher['email']); ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="qualification" class="form-label">
                                    <i class="fas fa-graduation-cap me-1"></i>Qualification
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="qualification" 
                                       name="qualification" 
                                       placeholder="Enter qualification"
                                       value="<?php echo htmlspecialchars($teacher['qualification']); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="experience" class="form-label">
                                    <i class="fas fa-briefcase me-1"></i>Experience (years)
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="experience" 
                                       name="experience" 
                                       placeholder="Enter years of experience"
                                       min="0"
                                       value="<?php echo htmlspecialchars($teacher['experience']); ?>">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="manage-teachers.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Teachers
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Teacher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>