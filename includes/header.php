<?php
// PHP SECTION: Configuration, Authentication, and Data Retrieval

// 1. Maintain File Links and Configuration (CRUCIAL: DO NOT CHANGE)
if (!defined('APP_NAME')) {
    require_once '../config.php';
}
requireAuth();

$pageTitle = $pageTitle ?? 'Dashboard';
$additionalCSS = $additionalCSS ?? [];
$notifications = [];

/*
| PHP: Notification Data Retrieval (Using MySQLi OOP methods)
| Assumes $conn is a MySQLi connection object from '../config.php'.
*/
if (isset($conn) && $conn instanceof mysqli) {

    // Recent students
    $sql_students = "SELECT full_name, created_at FROM students ORDER BY created_at DESC LIMIT 2";
    $result_students = $conn->query($sql_students);

    if ($result_students) {
        while ($row = $result_students->fetch_assoc()) {
            $notifications[] = [
                'icon' => 'fas fa-user-graduate text-primary',
                'title' => 'New student registered',
                'description' => htmlspecialchars($row['full_name'] ?? '') . ' joined.',
                'created_at' => $row['created_at']
            ];
        }
        $result_students->free();
    }

    // Recent teachers
    $sql_teachers = "SELECT full_name, created_at FROM teachers ORDER BY created_at DESC LIMIT 1";
    $result_teachers = $conn->query($sql_teachers);

    if ($result_teachers) {
        while ($row = $result_teachers->fetch_assoc()) {
            $notifications[] = [
                'icon' => 'fas fa-chalkboard-teacher text-success',
                'title' => 'New teacher added',
                'description' => htmlspecialchars($row['full_name'] ?? '') . ' joined.',
                'created_at' => $row['created_at']
            ];
        }
        $result_teachers->free();
    }

    // Low attendance alert (Dummy)
    $sql_alert = "SELECT class_id, class_name FROM classes ORDER BY RAND() LIMIT 1";
    $result_alert = $conn->query($sql_alert);

    if ($result_alert && $result_alert->num_rows > 0) {
        if ($row = $result_alert->fetch_assoc()) {
            $notifications[] = [
                'icon' => 'fas fa-exclamation-triangle text-warning',
                'title' => 'Low attendance alert',
                'description' => 'Class ' . htmlspecialchars($row['class_name']) . ' has low attendance.',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        $result_alert->free();
    }
}

// Sort notifications by date
usort($notifications, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
$notifications = array_slice($notifications, 0, 5);
$unreadCount = count($notifications);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - <?php echo APP_NAME; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="../assets/css/style.css">

    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>

    <header class="header">
        <div class="header-left d-flex align-items-center">
            <button class="sidebar-toggle mobile-toggle" aria-label="Toggle sidebar (mobile)">
                <i class="fas fa-bars"></i>
            </button>

            <button class="sidebar-toggle desktop-toggle" aria-label="Toggle sidebar (desktop)">
                <i class="fas fa-bars"></i>
            </button>

            <a href="../admin/dashboard.php" class="align-items-center ms-3 show-on-desktop">
                <img src="../assets/img/logo/logo.png" alt="<?php echo APP_NAME; ?>" class="header-logo me-2"
                    onerror="this.onerror=null;this.src='../assets/img/logo/logo-compact.png'">
                <span class="fw-bold d-none d-lg-inline-block text-truncate"
                    style="max-width:200px"><?php echo APP_NAME; ?></span>
            </a>
        </div>

        <div class="header-center d-none d-md-flex align-items-center justify-content-center">
            <h1 class="page-title"><?php echo $pageTitle; ?></h1>
            <form class="ms-3 d-none d-lg-flex" role="search" action="#" method="get">
                <div class="input-group">
                    <input type="search" name="q" class="form-control form-control-sm"
                        placeholder="Search students, classes...">
                    <button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>

        <div class="header-right">

            <div class="dropdown me-3">
                <button class="btn btn-link position-relative" data-bs-toggle="dropdown">
                    <i class="fas fa-bell fs-5 text-muted"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                        style="font-size: 0.6rem;">
                        <?php echo $unreadCount; ?>
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                    <div class="dropdown-header d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        <small class="text-muted"><?php echo $unreadCount; ?> unread</small>
                    </div>
                    <div class="dropdown-divider"></div>
                    <?php if (empty($notifications)): ?>
                        <div class="dropdown-item text-muted">No notifications</div>
                    <?php else: ?>
                        <?php foreach ($notifications as $note): ?>
                            <a href="#" class="dropdown-item">
                                <div class="d-flex">
                                    <div class="flex-shrink-0"><i class="<?php echo $note['icon']; ?>"></i></div>
                                    <div class="flex-grow-1 ms-2">
                                        <div class="fw-semibold"><?php echo $note['title']; ?></div>
                                        <div class="small text-muted"><?php echo $note['description']; ?></div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item text-center text-primary">View all notifications</a>
                </div>
            </div>

            <div class="dropdown">
                <button class="user-info" data-bs-toggle="dropdown">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?></div>
                    <div class="d-none d-md-block">
                        <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
                        <div class="small text-muted"><?php echo ucfirst($_SESSION['role'] ?? 'Guest'); ?></div>
                    </div>
                    <i class="fas fa-chevron-down ms-2"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <div class="dropdown-header">
                        <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
                        <div class="small text-muted">
                            <?php echo htmlspecialchars($_SESSION['email'] ?? 'email@example.com'); ?></div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="../includes/profile.php" class="dropdown-item"><i class="fas fa-user me-2"></i>Profile</a>
                    <a href="../includes/help.php" class="dropdown-item"><i
                            class="fas fa-question-circle me-2"></i>Help</a>
                    <div class="dropdown-divider"></div>
                    <a href="../auth/logout.php" class="dropdown-item text-danger"><i
                            class="fas fa-sign-out-alt me-2"></i>Logout</a>
                </div>
            </div>
        </div>
    </header>
    <div class="content-wrapper">
        <div class="content">