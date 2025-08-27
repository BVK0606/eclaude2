<?php
if (!defined('APP_NAME')) {
    require_once '../config.php';
}

$currentRole = $_SESSION['role'];
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>

<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="../<?php echo $currentRole; ?>/dashboard.php" class="brand">
            <div class="brand-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <span class="brand-text"><?php echo APP_NAME; ?></span>
        </a>
    </div>
    
    <div class="sidebar-nav">
        <?php if ($currentRole == 'admin'): ?>
            <!-- Admin Navigation -->
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <div class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">User Management</div>
                <div class="nav-item">
                    <a href="#studentsSubmenu" class="nav-link" data-bs-toggle="collapse" aria-expanded="false">
                        <div class="nav-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <span class="nav-text">Students</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </a>
                    <div class="collapse submenu" id="studentsSubmenu">
                        <div class="nav-item">
                            <a href="add-student.php" class="nav-link <?php echo ($currentPage == 'add-student') ? 'active' : ''; ?>">
                                <div class="nav-icon">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <span class="nav-text">Add Student</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="manage-students.php" class="nav-link <?php echo ($currentPage == 'manage-students') ? 'active' : ''; ?>">
                                <div class="nav-icon">
                                    <i class="fas fa-list"></i>
                                </div>
                                <span class="nav-text">Manage Students</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="nav-item">
                    <a href="#teachersSubmenu" class="nav-link" data-bs-toggle="collapse" aria-expanded="false">
                        <div class="nav-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <span class="nav-text">Teachers</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </a>
                    <div class="collapse submenu" id="teachersSubmenu">
                        <div class="nav-item">
                            <a href="add-teacher.php" class="nav-link <?php echo ($currentPage == 'add-teacher') ? 'active' : ''; ?>">
                                <div class="nav-icon">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <span class="nav-text">Add Teacher</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="manage-teachers.php" class="nav-link <?php echo ($currentPage == 'manage-teachers') ? 'active' : ''; ?>">
                                <div class="nav-icon">
                                    <i class="fas fa-list"></i>
                                </div>
                                <span class="nav-text">Manage Teachers</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Academic</div>
                <div class="nav-item">
                    <a href="classes.php" class="nav-link <?php echo ($currentPage == 'classes') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-school"></i>
                        </div>
                        <span class="nav-text">Classes</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="subjects.php" class="nav-link <?php echo ($currentPage == 'subjects') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <span class="nav-text">Subjects</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="attendance.php" class="nav-link <?php echo ($currentPage == 'attendance') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <span class="nav-text">Attendance</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="marks.php" class="nav-link <?php echo ($currentPage == 'marks') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <span class="nav-text">Marks</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Communication</div>
                <div class="nav-item">
                    <a href="notices.php" class="nav-link <?php echo ($currentPage == 'notices') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <span class="nav-text">Notices</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Reports</div>
                <div class="nav-item">
                    <a href="reports.php" class="nav-link <?php echo ($currentPage == 'reports') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <span class="nav-text">Reports</span>
                    </a>
                </div>
            </div>
            
        <?php elseif ($currentRole == 'teacher'): ?>
            <!-- Teacher Navigation -->
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <div class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Classes</div>
                <div class="nav-item">
                    <a href="my-classes.php" class="nav-link <?php echo ($currentPage == 'my-classes') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-school"></i>
                        </div>
                        <span class="nav-text">My Classes</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="my-subjects.php" class="nav-link <?php echo ($currentPage == 'my-subjects') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <span class="nav-text">My Subjects</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Academic</div>
                <div class="nav-item">
                    <a href="attendance.php" class="nav-link <?php echo ($currentPage == 'attendance') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <span class="nav-text">Attendance</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="marks.php" class="nav-link <?php echo ($currentPage == 'marks') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <span class="nav-text">Marks Entry</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Communication</div>
                <div class="nav-item">
                    <a href="notices.php" class="nav-link <?php echo ($currentPage == 'notices') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <span class="nav-text">Notices</span>
                    </a>
                </div>
            </div>
            
        <?php elseif ($currentRole == 'student'): ?>
            <!-- Student Navigation -->
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <div class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Academic</div>
                <div class="nav-item">
                    <a href="profile.php" class="nav-link <?php echo ($currentPage == 'profile') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <span class="nav-text">My Profile</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="attendance.php" class="nav-link <?php echo ($currentPage == 'attendance') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <span class="nav-text">My Attendance</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="marks.php" class="nav-link <?php echo ($currentPage == 'marks') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <span class="nav-text">My Marks</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="subjects.php" class="nav-link <?php echo ($currentPage == 'subjects') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <span class="nav-text">My Subjects</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Information</div>
                <div class="nav-item">
                    <a href="notices.php" class="nav-link <?php echo ($currentPage == 'notices') ? 'active' : ''; ?>">
                        <div class="nav-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <span class="nav-text">Notices</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Common Section for all roles -->
        <div class="nav-section">
            <div class="nav-section-title">System</div>
            <div class="nav-item">
                <a href="#" class="nav-link" onclick="toggleDarkMode()">
                    <div class="nav-icon">
                        <i class="fas fa-moon"></i>
                    </div>
                    <span class="nav-text">Dark Mode</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="../auth/logout.php" class="nav-link text-danger">
                    <div class="nav-icon">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <span class="nav-text">Logout</span>
                </a>
            </div>
        </div>
    </div>
</nav>