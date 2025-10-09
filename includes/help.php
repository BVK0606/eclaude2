<?php
require_once '../config.php';
requireAuth();
$pageTitle = 'Help';

include 'header.php';
include 'sidebar.php';
?>
<div class="main-content">
    <div class="content">
        <div class="dashboard-card mb-4">
            <h2 class="mb-2">Help & Support</h2>

            <h5>Getting Started</h5>
            <ul>
                <li>Log in with your username and password from the login page.</li>
                <li>Use the sidebar to navigate to Dashboard, Profile, Attendance, Marks, and other sections.</li>
                <li>Click your name in the top right to access your profile, help, or logout.</li>
            </ul>

            <h5>Common Tasks</h5>
            <ul>
                <li><b>Students:</b> View your attendance, marks, and notifications from the dashboard.</li>
                <li><b>Teachers:</b> Mark attendance, add marks, and view your assigned classes.</li>
                <li><b>Admins:</b> Add/manage students, teachers, classes, and subjects. Assign classes/subjects and view reports.</li>
            </ul>

            <h5>Frequently Asked Questions (FAQs)</h5>
            <ul>
                <li><b>How do I reset my password?</b> Contact your admin or IT support for assistance.</li>
                <li><b>What if I can't see my data?</b> Make sure you are assigned to the correct class or subject. Contact admin if the issue persists.</li>
                <li><b>Who can assign classes or subjects?</b> Only admin users have this permission.</li>
            </ul>

            <h5>Contact & Support</h5>
            <ul>
                <li>Email: <a href="mailto:support@edutrace.local">support@edutrace.local</a></li>
                <li>Phone: 123-456-7890</li>
                <li>Support Hours: Mon-Fri, 9am-5pm</li>
            </ul>

            <h5>About EduTrace</h5>
            <p>EduTrace is a simple student management system for colleges and schools. It helps manage students, teachers, classes, attendance, and marks in one place.</p>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
