<?php
if (!defined('APP_NAME')) {
    require_once '../config.php';
}

requireAuth(); // Ensure user is logged in
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - <?php echo APP_NAME; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <!-- Additional CSS -->
    <?php if (!empty($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <header class="header d-flex justify-content-between align-items-center p-2 shadow-sm bg-white">
        <div class="d-flex align-items-center">
            <!-- Mobile toggle -->
            <button class="sidebar-toggle d-lg-none me-2">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Logo -->
            <a href="../admin/dashboard.php" class="d-flex align-items-center">
                <img src="../assets/img/logo/logo.png" alt="<?php echo APP_NAME; ?>" class="header-logo me-2"
                    height="40">
                <span class="fw-bold d-none d-lg-inline-block text-truncate"
                    style="max-width:200px"><?php echo APP_NAME; ?></span>
            </a>
        </div>

        <!-- Page title and search (desktop only) -->
        <div class="d-none d-md-flex align-items-center">
            <h5 class="mb-0 me-3"><?php echo $pageTitle; ?></h5>
            <form class="d-none d-lg-flex" role="search" action="#" method="get">
                <div class="input-group">
                    <input type="search" name="q" class="form-control form-control-sm"
                        placeholder="Search students, classes...">
                    <button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>

        <div class="d-flex align-items-center">
            <!-- Notifications -->
            <?php
            $db = Database::getInstance()->getConnection();
            $notifications = [];

            // Recent students
            $stmt = $db->query("SELECT full_name, created_at FROM students ORDER BY created_at DESC LIMIT 2");
            foreach ($stmt->fetchAll() as $row) {
                $notifications[] = [
                    'icon' => 'fas fa-user-graduate text-primary',
                    'title' => 'New student registered',
                    'description' => htmlspecialchars($row['full_name'] ?? ''),
                    'created_at' => $row['created_at']
                ];
            }

            // Recent teachers
            $stmt = $db->query("SELECT full_name, created_at FROM teachers ORDER BY created_at DESC LIMIT 1");
            foreach ($stmt->fetchAll() as $row) {
                $notifications[] = [
                    'icon' => 'fas fa-chalkboard-teacher text-success',
                    'title' => 'New teacher added',
                    'description' => htmlspecialchars($row['full_name']),
                    'created_at' => $row['created_at']
                ];
            }

            // Low attendance alert (example)
            $stmt = $db->query("SELECT class_id, class_name FROM classes ORDER BY RAND() LIMIT 1");
            if ($row = $stmt->fetch()) {
                $notifications[] = [
                    'icon' => 'fas fa-exclamation-triangle text-warning',
                    'title' => 'Low attendance alert',
                    'description' => 'Class ' . htmlspecialchars($row['class_name']) . ' has low attendance.',
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }

            // Sort and limit
            usort($notifications, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
            $notifications = array_slice($notifications, 0, 5);
            $unreadCount = count($notifications);
            ?>

            <div class="dropdown me-3">
                <button class="btn btn-link position-relative" data-bs-toggle="dropdown">
                    <i class="fas fa-bell fs-5 text-muted"></i>
                    <?php if ($unreadCount): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            style="font-size:0.6rem;">
                            <?php echo $unreadCount; ?>
                        </span>
                    <?php endif; ?>
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

            <!-- User Menu -->
            <div class="dropdown">
                <button class="user-info d-flex align-items-center" data-bs-toggle="dropdown">
                    <div
                        class="user-avatar rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-2">
                        <?php echo strtoupper(substr($_SESSION['username'] ?? '', 0, 1)); ?>
                    </div>
                    <div class="d-none d-md-block">
                        <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></div>
                        <div class="small text-muted"><?php echo ucfirst($_SESSION['role'] ?? ''); ?></div>
                    </div>
                    <i class="fas fa-chevron-down ms-2"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <div class="dropdown-header">
                        <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></div>
                        <div class="small text-muted"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></div>
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