<?php
require_once '../config.php';
requireRole('admin');

// Delete teacher
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $teacherId = $_GET['delete'];
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT teacher_id FROM teachers WHERE teacher_id=?");
        $stmt->execute([$teacherId]);
        if ($stmt->fetch()) {
            $db->beginTransaction();
            $db->prepare("UPDATE subjects SET teacher_id=NULL WHERE teacher_id=?")->execute([$teacherId]);
            $db->prepare("DELETE FROM teacher_classes WHERE teacher_id=?")->execute([$teacherId]);
            $db->prepare("DELETE FROM teachers WHERE teacher_id=?")->execute([$teacherId]);
            $db->commit();
            $_SESSION['success'] = 'Teacher deleted successfully.';
        } else {
            $_SESSION['error'] = 'Teacher not found.';
        }
    } catch (PDOException $e) {
        $db->rollback();
        $_SESSION['error'] = 'Deletion failed.';
    }
    header('Location: manage-teachers.php'); exit;
}

// Fetch all teachers
$teachers = mysqli_fetch_all(mysqli_query($conn, "
    SELECT t.teacher_id, t.full_name, t.qualification, t.experience, t.created_at, u.email
    FROM teachers t LEFT JOIN users u ON t.user_id=u.id ORDER BY t.created_at DESC
"), MYSQLI_ASSOC);

?>