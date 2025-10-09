<?php
require_once '../config.php';
requireAuth(); // Make sure user is logged in
$pageTitle = 'My Profile';

include 'header.php';
include 'sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="dashboard-card mb-4">
            <h2 class="mb-2">My Profile</h2>
            <ul>
                <li><b>Username:</b> <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></li>
                <li><b>Email:</b> <?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></li>
                <li><b>Role:</b> <?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?></li>
            </ul>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
