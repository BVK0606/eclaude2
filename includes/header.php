<?php
if (!defined('APP_NAME')) {
    require_once '../config.php';
}

requireAuth();
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
    
    <!-- Chart.js (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- DataTables CSS (Optional) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Additional CSS -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    
    <header class="header">
        <div class="header-left">
            <button class="sidebar-toggle" type="button">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="page-title"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
        </div>
        
        <div class="header-right">
            <!-- Notifications -->
            <div class="dropdown me-3">
                <button class="btn btn-link position-relative" data-bs-toggle="dropdown">
                    <i class="fas fa-bell fs-5 text-muted"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                        3
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                    <div class="dropdown-header d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        <small class="text-muted">3 unread</small>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <div class="fw-semibold">New student registered</div>
                                <div class="small text-muted">John Doe has been added to class 10A</div>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="dropdown-item">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <div class="fw-semibold">Low attendance alert</div>
                                <div class="small text-muted">Class 12B has low attendance this week</div>
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item text-center text-primary">
                        View all notifications
                    </a>
                </div>
            </div>
            
            <!-- User Menu -->
            <div class="dropdown">
                <button class="user-info" data-bs-toggle="dropdown">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                    <div class="d-none d-md-block">
                        <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                        <div class="small text-muted"><?php echo ucfirst($_SESSION['role']); ?></div>
                    </div>
                    <i class="fas fa-chevron-down ms-2"></i>
                </button>
                
                <div class="dropdown-menu dropdown-menu-end">
                    <div class="dropdown-header">
                        <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                        <div class="small text-muted"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-user me-2"></i>Profile
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-cog me-2"></i>Settings
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-question-circle me-2"></i>Help
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="../auth/logout.php" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </header>