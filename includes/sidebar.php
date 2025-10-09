<?php
if (!defined('APP_NAME')) {
    require_once '../config.php';
}

requireAuth();

$currentRole = $_SESSION['role'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>

<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="../homepage/home.php" class="brand">
            <div class="brand-icon"><i class="fas fa-home"></i></div>
            <span class="brand-text">Home</span>
        </a>
        <a href="../<?php echo $currentRole; ?>/dashboard.php" class="brand ms-3">
            <div class="brand-icon"><i class="fas fa-graduation-cap"></i></div>
            <span class="brand-text"><?php echo APP_NAME; ?></span>
        </a>
    </div>

    <div class="sidebar-nav">

        <?php if ($currentRole === 'admin'): ?>
            <!-- Admin Sidebar -->
            <div class="nav-section">
                <div class="nav-section-title">Dashboard</div>
                <div class="nav-item">
                    <a href="../admin/dashboard.php" class="nav-link <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
                        <div class="nav-icon"><i class="fas fa-tachometer-alt"></i></div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
            </div>

            <!-- Students -->
            <div class="nav-section">
                <div class="nav-section-title">Students</div>
                <div class="nav-item">
                    <a href="#studentsSubmenu" class="nav-link" data-bs-toggle="collapse" aria-expanded="false">
                        <div class="nav-icon"><i class="fas fa-user-graduate"></i></div>
                        <span class="nav-text">Students</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </a>
                    <div class="collapse submenu" id="studentsSubmenu">
                        <div class="nav-item">
                            <a href="add-student.php" class="nav-link <?php echo ($currentPage == 'add-student') ? 'active' : ''; ?>">
                                <div class="nav-icon"><i class="fas fa-plus"></i></div>
                                <span class="nav-text">Add Student</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="manage-students.php" class="nav-link <?php echo ($currentPage == 'manage-students') ? 'active' : ''; ?>">
                                <div class="nav-icon"><i class="fas fa-list"></i></div>
                                <span class="nav-text">Manage Students</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Teachers -->
            <div class="nav-section">
                <div class="nav-section-title">Teachers</div>
                <div class="nav-item">
                    <a href="#teachersSubmenu" class="nav-link" data-bs-toggle="collapse" aria-expanded="false">
                        <div class="nav-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        <span class="nav-text">Teachers</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </a>
                    <div class="collapse submenu" id="teachersSubmenu">
                        <div class="nav-item">
                            <a href="add-teacher.php" class="nav-link <?php echo ($currentPage == 'add-teacher') ? 'active' : ''; ?>">
                                <div class="nav-icon"><i class="fas fa-plus"></i></div>
                                <span class="nav-text">Add Teacher</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="manage-teachers.php" class="nav-link <?php echo ($currentPage == 'manage-teachers') ? 'active' : ''; ?>">
                                <div class="nav-icon"><i class="fas fa-list"></i></div>
                                <span class="nav-text">Manage Teachers</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Classes -->
            <div class="nav-section">
                <div class="nav-section-title">Academic</div>
                <div class="nav-item">
                    <a href="#classesSubmenu" class="nav-link" data-bs-toggle="collapse" aria-expanded="false">
                        <div class="nav-icon"><i class="fas fa-school"></i></div>
                        <span class="nav-text">Classes</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </a>
                    <div class="collapse submenu" id="classesSubmenu">
                        <div class="nav-item">
                            <a href="classes.php" class="nav-link <?php echo ($currentPage == 'classes') ? 'active' : ''; ?>">
                                <div class="nav-icon"><i class="fas fa-list"></i></div>
                                <span class="nav-text">Manage Classes</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="assign-class.php" class="nav-link <?php echo ($currentPage == 'assign-class') ? 'active' : ''; ?>">
                                <div class="nav-icon"><i class="fas fa-user-plus"></i></div>
                                <span class="nav-text">Assign Class</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="assigned-classes.php" class="nav-link <?php echo ($currentPage == 'assigned-classes') ? 'active' : ''; ?>">
                                <div class="nav-icon"><i class="fas fa-users"></i></div>
                                <span class="nav-text">Assigned Classes</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Subjects -->
                <div class="nav-item">
                    <a href="#subjectsSubmenu" class="nav-link" data-bs-toggle="collapse" aria-expanded="false">
                        <div class="nav-icon"><i class="fas fa-book"></i></div>
                        <span class="nav-text">Subjects</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </a>
                    <div class="collapse submenu" id="subjectsSubmenu">
                        <div class="nav-item">
                            <a href="subjects.php" class="nav-link <?php echo ($currentPage == 'subjects') ? 'active' : ''; ?>">
                                <div class="nav-icon"><i class="fas fa-list"></i></div>
                                <span class="nav-text">Manage Subjects</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="assign-subject.php" class="nav-link <?php echo ($currentPage == 'assign-subject') ? 'active' : ''; ?>">
                                <div class="nav-icon"><i class="fas fa-user-plus"></i></div>
                                <span class="nav-text">Assign Subject</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="assigned-subjects.php" class="nav-link <?php echo ($currentPage == 'assigned-subjects') ? 'active' : ''; ?>">
                                <div class="nav-icon"><i class="fas fa-users"></i></div>
                                <span class="nav-text">Assigned Subjects</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance & Marks -->
            <div class="nav-section">
                <div class="nav-section-title">Attendance & Marks</div>
                <div class="nav-item">
                    <a href="#attendanceSubmenu" class="nav-link" data-bs-toggle="collapse" aria-expanded="false">
                        <div class="nav-icon"><i class="fas fa-calendar-check"></i></div>
                        <span class="nav-text">Attendance</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </a>
                    <div class="collapse submenu" id="attendanceSubmenu">
                        <div class="nav-item">
                            <a href="attendance.php" class="nav-link <?php echo ($currentPage == 'manage-attendance') ? 'active' : ''; ?>">
                                <div class="nav-icon"><i class="fas fa-list"></i></div>
                                <span class="nav-text">Manage Attendance</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="attendance-records.php" class="nav-link <?php echo ($currentPage == 'view-attendance') ? 'active' : ''; ?>">
                                <div class="nav-icon"><i class="fas fa-user-plus"></i></div>
                                <span class="nav-text">View Attendance</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="nav-item">
                    <a href="#marksSubmenu" class="nav-link" data-bs-toggle="collapse" aria-expanded="false">
                        <div class="nav-icon"><i class="fas fa-chart-bar"></i></div>
                        <span class="nav-text">Marks</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </a>
                    <div class="collapse submenu" id="marksSubmenu">
                        <div class="nav-item">
                            <a href="marks.php" class="nav-link <?php echo ($currentPage == 'manage-marks') ? 'active' : ''; ?>">
                                <div class="nav-icon"><i class="fas fa-list"></i></div>
                                <span class="nav-text">Manage Marks</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="marks-records.php" class="nav-link <?php echo ($currentPage == 'view-marks') ? 'active' : ''; ?>">
                                <div class="nav-icon"><i class="fas fa-user-plus"></i></div>
                                <span class="nav-text">View Marks</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notices -->
            <div class="nav-section">
                <div class="nav-section-title">Communication</div>
                <div class="nav-item">
                    <a href="notices.php" class="nav-link <?php echo ($currentPage == 'notices') ? 'active' : ''; ?>">
                        <div class="nav-icon"><i class="fas fa-bullhorn"></i></div>
                        <span class="nav-text">Notices</span>
                    </a>
                </div>
            </div>

        <?php elseif ($currentRole === 'teacher'): ?>
            <!-- Teacher Sidebar with Submenus -->
            <div class="nav-section">
                <div class="nav-section-title">Dashboard</div>
                <div class="nav-item">
                    <a href="../teacher/dashboard.php" class="nav-link <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
                        <div class="nav-icon"><i class="fas fa-tachometer-alt"></i></div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>

                <!-- Classes -->
                <div class="nav-item">
                    <a href="#teacherClasses" class="nav-link" data-bs-toggle="collapse" aria-expanded="false">
                        <div class="nav-icon"><i class="fas fa-school"></i></div>
                        <span class="nav-text">My Classes</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </a>
                    <div class="collapse submenu" id="teacherClasses">
                        <div class="nav-item">
                            <a href="my-classes.php" class="nav-link <?php echo ($currentPage == 'my-classes') ? 'active' : ''; ?>">View Classes</a>
                        </div>
                    </div>
                </div>

                <!-- Subjects -->
                <div class="nav-item">
                    <a href="#teacherSubjects" class="nav-link" data-bs-toggle="collapse" aria-expanded="false">
                        <div class="nav-icon"><i class="fas fa-book"></i></div>
                        <span class="nav-text">My Subjects</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </a>
                    <div class="collapse submenu" id="teacherSubjects">
                        <div class="nav-item">
                            <a href="my-subjects.php" class="nav-link <?php echo ($currentPage == 'my-subjects') ? 'active' : ''; ?>">View Subjects</a>
                        </div>
                    </div>
                </div>

                <!-- Attendance -->
                <div class="nav-item">
                    <a href="#teacherAttendance" class="nav-link" data-bs-toggle="collapse" aria-expanded="false">
                        <div class="nav-icon"><i class="fas fa-calendar-check"></i></div>
                        <span class="nav-text">Attendance</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </a>
                    <div class="collapse submenu" id="teacherAttendance">
                        <div class="nav-item"><a href="attendance.php" class="nav-link <?php echo ($currentPage == 'attendance') ? 'active' : ''; ?>">Manage Attendance</a></div>
                        <div class="nav-item"><a href="attendance-records.php" class="nav-link <?php echo ($currentPage == 'attendance-records') ? 'active' : ''; ?>">View Attendance</a></div>
                    </div>
                </div>

                <!-- Marks -->
                <div class="nav-item">
                    <a href="#teacherMarks" class="nav-link" data-bs-toggle="collapse" aria-expanded="false">
                        <div class="nav-icon"><i class="fas fa-chart-bar"></i></div>
                        <span class="nav-text">Marks</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </a>
                    <div class="collapse submenu" id="teacherMarks">
                        <div class="nav-item"><a href="marks.php" class="nav-link <?php echo ($currentPage == 'marks') ? 'active' : ''; ?>">Enter Marks</a></div>
                        <div class="nav-item"><a href="marks-records.php" class="nav-link <?php echo ($currentPage == 'marks-records') ? 'active' : ''; ?>">View Marks</a></div>
                    </div>
                </div>

                <!-- Notices -->
                <div class="nav-item">
                    <a href="#teacherNotices" class="nav-link" data-bs-toggle="collapse" aria-expanded="false">
                        <div class="nav-icon"><i class="fas fa-bullhorn"></i></div>
                        <span class="nav-text">Notices</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </a>
                    <div class="collapse submenu" id="teacherNotices">
                        <div class="nav-item"><a href="notices.php" class="nav-link <?php echo ($currentPage == 'notices') ? 'active' : ''; ?>">View Notices</a></div>
                        <div class="nav-item"><a href="notices-manage.php" class="nav-link <?php echo ($currentPage == 'notices-manage') ? 'active' : ''; ?>">Manage Notices</a></div>
                    </div>
                </div>

            </div>

        <?php elseif ($currentRole === 'student'): ?>
            <!-- Student Sidebar with Submenus -->
            <div class="nav-section">
                <div class="nav-section-title">Dashboard</div>
                <div class="nav-item">
                    <a href="../student/dashboard.php" class="nav-link <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
                        <div class="nav-icon"><i class="fas fa-tachometer-alt"></i></div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>

                <!-- Academic Submenu -->
                <div class="nav-item">
                    <a href="#studentAcademic" class="nav-link" data-bs-toggle="collapse" aria-expanded="false">
                        <div class="nav-icon"><i class="fas fa-book"></i></div>
                        <span class="nav-text">Academic</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </a>
                    <div class="collapse submenu" id="studentAcademic">
                        <div class="nav-item"><a href="profile.php" class="nav-link <?php echo ($currentPage == 'profile') ? 'active' : ''; ?>">My Profile</a></div>
                        <div class="nav-item"><a href="attendance.php" class="nav-link <?php echo ($currentPage == 'attendance') ? 'active' : ''; ?>">My Attendance</a></div>
                        <div class="nav-item"><a href="marks.php" class="nav-link <?php echo ($currentPage == 'marks') ? 'active' : ''; ?>">My Marks</a></div>
                        <div class="nav-item"><a href="subjects.php" class="nav-link <?php echo ($currentPage == 'subjects') ? 'active' : ''; ?>">My Subjects</a></div>
                    </div>
                </div>

                <!-- Notices Submenu -->
                <div class="nav-item">
                    <a href="#studentNotices" class="nav-link" data-bs-toggle="collapse" aria-expanded="false">
                        <div class="nav-icon"><i class="fas fa-bullhorn"></i></div>
                        <span class="nav-text">Notices</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </a>
                    <div class="collapse submenu" id="studentNotices">
                        <div class="nav-item"><a href="notices.php" class="nav-link <?php echo ($currentPage == 'notices') ? 'active' : ''; ?>">View Notices</a></div>
                    </div>
                </div>
            </div>

        <?php endif; ?>

        <!-- Logout for all roles -->
        <div class="nav-section">
            <div class="nav-section-title">System</div>
            <div class="nav-item">
                <a href="../auth/logout.php" class="nav-link text-danger">
                    <div class="nav-icon"><i class="fas fa-sign-out-alt"></i></div>
                    <span class="nav-text">Logout</span>
                </a>
            </div>
        </div>

    </div>
</nav>
