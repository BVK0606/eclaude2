<?php
if (!defined('APP_NAME')) {
    require_once '../config.php';
}

requireAuth();

$currentRole = $_SESSION['role'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>

<nav class="sidebar shadow-lg" id="sidebar">
    <div class="sidebar-header">
        <a href="../homepage/home.php" class="brand">
            <div class="brand-icon"><i class="fas fa-home"></i></div>
            <span class="brand-text">Home</span>
        </a>
        <a href="../<?php echo $currentRole; ?>/dashboard.php" class="brand ms-3">
            <div class="brand-icon"><i class="fas fa-graduation-cap"></i></div>
            <span class="brand-text"><?php echo htmlspecialchars(APP_NAME); ?></span>
        </a>
    </div>

    <div class="sidebar-nav">

        <?php if ($currentRole === 'admin'): ?>
            <div class="nav-section">
                <div class="nav-section-title">Main Dashboard</div>
                <div class="nav-item">
                    <a href="../admin/dashboard.php"
                        class="nav-link <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
                        <div class="nav-icon"><i class="fas fa-tachometer-alt"></i></div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Students</div>
                <div class="nav-item">
                    <a href="#studentsSubmenu" class="nav-link collapsed" data-bs-toggle="collapse"
                        aria-expanded="<?php echo in_array($currentPage, ['add-student', 'manage-students']) ? 'true' : 'false'; ?>">
                        <div class="nav-icon"><i class="fas fa-user-graduate"></i></div>
                        <span class="nav-text">Students</span>
                        <i class="fas fa-chevron-down nav-arrow ms-auto"></i>
                    </a>
                    <div class="collapse submenu <?php echo in_array($currentPage, ['add-student', 'manage-students']) ? 'show' : ''; ?>"
                        id="studentsSubmenu">
                        <div class="nav-item">
                            <a href="add-student.php"
                                class="nav-link <?php echo ($currentPage == 'add-student') ? 'active' : ''; ?>">
                                <span class="nav-text">Add Student</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="manage-students.php"
                                class="nav-link <?php echo ($currentPage == 'manage-students') ? 'active' : ''; ?>">
                                <span class="nav-text">Manage Students</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Teachers</div>
                <div class="nav-item">
                    <a href="#teachersSubmenu" class="nav-link collapsed" data-bs-toggle="collapse"
                        aria-expanded="<?php echo in_array($currentPage, ['add-teacher', 'manage-teachers']) ? 'true' : 'false'; ?>">
                        <div class="nav-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        <span class="nav-text">Teachers</span>
                        <i class="fas fa-chevron-down nav-arrow ms-auto"></i>
                    </a>
                    <div class="collapse submenu <?php echo in_array($currentPage, ['add-teacher', 'manage-teachers']) ? 'show' : ''; ?>"
                        id="teachersSubmenu">
                        <div class="nav-item">
                            <a href="add-teacher.php"
                                class="nav-link <?php echo ($currentPage == 'add-teacher') ? 'active' : ''; ?>">
                                <span class="nav-text">Add Teacher</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="manage-teachers.php"
                                class="nav-link <?php echo ($currentPage == 'manage-teachers') ? 'active' : ''; ?>">
                                <span class="nav-text">Manage Teachers</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Academic Management</div>

                <div class="nav-item">
                    <a href="#classesSubmenu" class="nav-link collapsed" data-bs-toggle="collapse"
                        aria-expanded="<?php echo in_array($currentPage, ['classes', 'assign-class', 'assigned-classes']) ? 'true' : 'false'; ?>">
                        <div class="nav-icon"><i class="fas fa-school"></i></div>
                        <span class="nav-text">Classes</span>
                        <i class="fas fa-chevron-down nav-arrow ms-auto"></i>
                    </a>
                    <div class="collapse submenu <?php echo in_array($currentPage, ['classes', 'assign-class', 'assigned-classes']) ? 'show' : ''; ?>"
                        id="classesSubmenu">
                        <div class="nav-item"><a href="classes.php"
                                class="nav-link <?php echo ($currentPage == 'classes') ? 'active' : ''; ?>"><span
                                    class="nav-text">Manage Classes</span></a></div>
                        <div class="nav-item"><a href="assign-class.php"
                                class="nav-link <?php echo ($currentPage == 'assign-class') ? 'active' : ''; ?>"><span
                                    class="nav-text">Assign Class</span></a></div>
                        <div class="nav-item"><a href="assigned-classes.php"
                                class="nav-link <?php echo ($currentPage == 'assigned-classes') ? 'active' : ''; ?>"><span
                                    class="nav-text">Assigned Classes</span></a></div>
                    </div>
                </div>

                <div class="nav-item">
                    <a href="#subjectsSubmenu" class="nav-link collapsed" data-bs-toggle="collapse"
                        aria-expanded="<?php echo in_array($currentPage, ['subjects', 'assign-subject', 'assigned-subjects']) ? 'true' : 'false'; ?>">
                        <div class="nav-icon"><i class="fas fa-book"></i></div>
                        <span class="nav-text">Subjects</span>
                        <i class="fas fa-chevron-down nav-arrow ms-auto"></i>
                    </a>
                    <div class="collapse submenu <?php echo in_array($currentPage, ['subjects', 'assign-subject', 'assigned-subjects']) ? 'show' : ''; ?>"
                        id="subjectsSubmenu">
                        <div class="nav-item"><a href="subjects.php"
                                class="nav-link <?php echo ($currentPage == 'subjects') ? 'active' : ''; ?>"><span
                                    class="nav-text">Manage Subjects</span></a></div>
                        <div class="nav-item"><a href="assign-subject.php"
                                class="nav-link <?php echo ($currentPage == 'assign-subject') ? 'active' : ''; ?>"><span
                                    class="nav-text">Assign Subject</span></a></div>
                        <div class="nav-item"><a href="assigned-subjects.php"
                                class="nav-link <?php echo ($currentPage == 'assigned-subjects') ? 'active' : ''; ?>"><span
                                    class="nav-text">Assigned Subjects</span></a></div>
                    </div>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Records</div>

                <div class="nav-item">
                    <a href="#attendanceSubmenu" class="nav-link collapsed" data-bs-toggle="collapse"
                        aria-expanded="<?php echo in_array($currentPage, ['attendance', 'attendance-records']) ? 'true' : 'false'; ?>">
                        <div class="nav-icon"><i class="fas fa-calendar-check"></i></div>
                        <span class="nav-text">Attendance</span>
                        <i class="fas fa-chevron-down nav-arrow ms-auto"></i>
                    </a>
                    <div class="collapse submenu <?php echo in_array($currentPage, ['attendance', 'attendance-records']) ? 'show' : ''; ?>"
                        id="attendanceSubmenu">
                        <div class="nav-item"><a href="attendance.php"
                                class="nav-link <?php echo ($currentPage == 'manage-attendance') ? 'active' : ''; ?>"><span
                                    class="nav-text">Manage Attendance</span></a></div>
                        <div class="nav-item"><a href="attendance-records.php"
                                class="nav-link <?php echo ($currentPage == 'view-attendance') ? 'active' : ''; ?>"><span
                                    class="nav-text">View Attendance</span></a></div>
                    </div>
                </div>

                <div class="nav-item">
                    <a href="#marksSubmenu" class="nav-link collapsed" data-bs-toggle="collapse"
                        aria-expanded="<?php echo in_array($currentPage, ['marks', 'marks-records']) ? 'true' : 'false'; ?>">
                        <div class="nav-icon"><i class="fas fa-chart-bar"></i></div>
                        <span class="nav-text">Marks</span>
                        <i class="fas fa-chevron-down nav-arrow ms-auto"></i>
                    </a>
                    <div class="collapse submenu <?php echo in_array($currentPage, ['marks', 'marks-records']) ? 'show' : ''; ?>"
                        id="marksSubmenu">
                        <div class="nav-item"><a href="marks.php"
                                class="nav-link <?php echo ($currentPage == 'manage-marks') ? 'active' : ''; ?>"><span
                                    class="nav-text">Manage Marks</span></a></div>
                        <div class="nav-item"><a href="marks-records.php"
                                class="nav-link <?php echo ($currentPage == 'view-marks') ? 'active' : ''; ?>"><span
                                    class="nav-text">View Marks</span></a></div>
                    </div>
                </div>
            </div>

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
            <div class="nav-section">
                <div class="nav-section-title">Teacher Dashboard</div>
                <div class="nav-item">
                    <a href="../teacher/dashboard.php"
                        class="nav-link <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
                        <div class="nav-icon"><i class="fas fa-tachometer-alt"></i></div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>

                <div class="nav-section-title">My Academic Load</div>

                <div class="nav-item">
                    <a href="my-classes.php" class="nav-link <?php echo ($currentPage == 'my-classes') ? 'active' : ''; ?>">
                        <div class="nav-icon"><i class="fas fa-school"></i></div>
                        <span class="nav-text">My Classes</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="my-subjects.php"
                        class="nav-link <?php echo ($currentPage == 'my-subjects') ? 'active' : ''; ?>">
                        <div class="nav-icon"><i class="fas fa-book"></i></div>
                        <span class="nav-text">My Subjects</span>
                    </a>
                </div>

                <div class="nav-section-title">Records Management</div>

                <div class="nav-item">
                    <a href="#teacherAttendance" class="nav-link collapsed" data-bs-toggle="collapse"
                        aria-expanded="<?php echo in_array($currentPage, ['attendance', 'attendance-records']) ? 'true' : 'false'; ?>">
                        <div class="nav-icon"><i class="fas fa-calendar-check"></i></div>
                        <span class="nav-text">Attendance</span>
                        <i class="fas fa-chevron-down nav-arrow ms-auto"></i>
                    </a>
                    <div class="collapse submenu <?php echo in_array($currentPage, ['attendance', 'attendance-records']) ? 'show' : ''; ?>"
                        id="teacherAttendance">
                        <div class="nav-item"><a href="attendance.php"
                                class="nav-link <?php echo ($currentPage == 'attendance') ? 'active' : ''; ?>">Manage
                                Attendance</a></div>
                        <div class="nav-item"><a href="attendance-records.php"
                                class="nav-link <?php echo ($currentPage == 'attendance-records') ? 'active' : ''; ?>">View
                                Attendance</a></div>
                    </div>
                </div>

                <div class="nav-item">
                    <a href="#teacherMarks" class="nav-link collapsed" data-bs-toggle="collapse"
                        aria-expanded="<?php echo in_array($currentPage, ['marks', 'marks-records']) ? 'true' : 'false'; ?>">
                        <div class="nav-icon"><i class="fas fa-chart-bar"></i></div>
                        <span class="nav-text">Marks</span>
                        <i class="fas fa-chevron-down nav-arrow ms-auto"></i>
                    </a>
                    <div class="collapse submenu <?php echo in_array($currentPage, ['marks', 'marks-records']) ? 'show' : ''; ?>"
                        id="teacherMarks">
                        <div class="nav-item"><a href="marks.php"
                                class="nav-link <?php echo ($currentPage == 'marks') ? 'active' : ''; ?>">Enter Marks</a>
                        </div>
                        <div class="nav-item"><a href="marks-records.php"
                                class="nav-link <?php echo ($currentPage == 'marks-records') ? 'active' : ''; ?>">View
                                Marks</a></div>
                    </div>
                </div>

                <div class="nav-item">
                    <a href="#teacherNotices" class="nav-link collapsed" data-bs-toggle="collapse"
                        aria-expanded="<?php echo in_array($currentPage, ['notices', 'notices-manage']) ? 'true' : 'false'; ?>">
                        <div class="nav-icon"><i class="fas fa-bullhorn"></i></div>
                        <span class="nav-text">Notices</span>
                        <i class="fas fa-chevron-down nav-arrow ms-auto"></i>
                    </a>
                    <div class="collapse submenu <?php echo in_array($currentPage, ['notices', 'notices-manage']) ? 'show' : ''; ?>"
                        id="teacherNotices">
                        <div class="nav-item"><a href="notices.php"
                                class="nav-link <?php echo ($currentPage == 'notices') ? 'active' : ''; ?>">View Notices</a>
                        </div>
                        <div class="nav-item"><a href="notices-manage.php"
                                class="nav-link <?php echo ($currentPage == 'notices-manage') ? 'active' : ''; ?>">Manage
                                Notices</a></div>
                    </div>
                </div>

            </div>

        <?php elseif ($currentRole === 'student'): ?>
            <div class="nav-section">
                <div class="nav-section-title">Student Dashboard</div>
                <div class="nav-item">
                    <a href="../student/dashboard.php"
                        class="nav-link <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
                        <div class="nav-icon"><i class="fas fa-tachometer-alt"></i></div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>

                <div class="nav-section-title">Academic Records</div>
                <div class="nav-item">
                    <a href="#studentAcademic" class="nav-link collapsed" data-bs-toggle="collapse"
                        aria-expanded="<?php echo in_array($currentPage, ['profile', 'attendance', 'marks', 'subjects']) ? 'true' : 'false'; ?>">
                        <div class="nav-icon"><i class="fas fa-book-open"></i></div>
                        <span class="nav-text">Records</span>
                        <i class="fas fa-chevron-down nav-arrow ms-auto"></i>
                    </a>
                    <div class="collapse submenu <?php echo in_array($currentPage, ['profile', 'attendance', 'marks', 'subjects']) ? 'show' : ''; ?>"
                        id="studentAcademic">
                        <div class="nav-item"><a href="profile.php"
                                class="nav-link <?php echo ($currentPage == 'profile') ? 'active' : ''; ?>">My Profile</a>
                        </div>
                        <div class="nav-item"><a href="attendance.php"
                                class="nav-link <?php echo ($currentPage == 'attendance') ? 'active' : ''; ?>">My
                                Attendance</a></div>
                        <div class="nav-item"><a href="marks.php"
                                class="nav-link <?php echo ($currentPage == 'marks') ? 'active' : ''; ?>">My Marks</a></div>
                        <div class="nav-item"><a href="subjects.php"
                                class="nav-link <?php echo ($currentPage == 'subjects') ? 'active' : ''; ?>">My Subjects</a>
                        </div>
                    </div>
                </div>

                <div class="nav-item">
                    <a href="notices.php" class="nav-link <?php echo ($currentPage == 'notices') ? 'active' : ''; ?>">
                        <div class="nav-icon"><i class="fas fa-bullhorn"></i></div>
                        <span class="nav-text">Notices</span>
                    </a>
                </div>
            </div>

        <?php endif; ?>

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