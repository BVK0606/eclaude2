<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTrace - Student Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
     <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <!-- Navigation Bar -->
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="images/logo/logo.png" alt="EduTrace Logo" class="me-2" style="height: 40px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/login.php" >Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-lg-2 mt-2 mt-lg-0" href="../auth/register.php" >Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title">Smart way to manage students</h1>
                    <p class="hero-subtitle">EduTrace simplifies student management with powerful tools for attendance,
                        grades, and more.</p>
                    <div class="d-flex gap-3">
                        <a href="#features" class="btn btn-light btn-lg">Explore Features</a>
                        <a href="../auth/register.php" class="btn btn-outline-light btn-lg" >Get Started</a>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0">
                    <img src="images/sdash.png"
                        alt="EduTrace Dashboard" class="hero-img">
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section bg-light">
        <div class="container">
            <h2 class="text-center section-title">About EduTrace</h2>
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <img src="images/about.jpeg" alt="About EduTrace"
                        class="img-fluid rounded">
                </div>
                <div class="col-lg-6 mt-4 mt-lg-0">
                    <h3>Simplifying Student Management</h3>
                    <p>EduTrace is a comprehensive student management system designed to help educational institutions
                        efficiently manage student data, attendance, grades, and more.</p>
                    <p>Our platform provides educators with powerful tools to streamline administrative tasks, allowing
                        them to focus more on teaching and student development.</p>
                    <p>With an intuitive interface and robust features, EduTrace is suitable for schools, colleges, and
                        training centers of all sizes.</p>
                    <a href="#features" class="btn btn-primary mt-3">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section">
        <div class="container">
            <h2 class="text-center section-title">Key Features</h2>
            <p class="text-center mb-5">Discover the powerful features that make EduTrace the perfect solution for your
                institution</p>

            <div class="row g-4">
                <!-- Feature 1 -->
                <div class="col-md-4">
                    <div class="card feature-card p-4">
                        <div class="text-center">
                            <i class="fas fa-user-graduate feature-icon"></i>
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title">Student Profiles</h5>
                            <p class="card-text">Comprehensive student profiles with personal information, academic
                                records, and more.</p>
                        </div>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="col-md-4">
                    <div class="card feature-card p-4">
                        <div class="text-center">
                            <i class="fas fa-calendar-check feature-icon"></i>
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title">Attendance Tracking</h5>
                            <p class="card-text">Easily record and monitor student attendance with automated reports.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="col-md-4">
                    <div class="card feature-card p-4">
                        <div class="text-center">
                            <i class="fas fa-chart-line feature-icon"></i>
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title">Grade Management</h5>
                            <p class="card-text">Track student performance, calculate grades, and generate report cards.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="col-md-4">
                    <div class="card feature-card p-4">
                        <div class="text-center">
                            <i class="fas fa-bell feature-icon"></i>
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title">Notifications</h5>
                            <p class="card-text">Automated alerts for attendance, grades, and important announcements.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Feature 5 -->
                <div class="col-md-4">
                    <div class="card feature-card p-4">
                        <div class="text-center">
                            <i class="fas fa-chart-pie feature-icon"></i>
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title">Analytics</h5>
                            <p class="card-text">Visual reports and insights to track class and student performance.</p>
                        </div>
                    </div>
                </div>

                <!-- Feature 6 -->
                <div class="col-md-4">
                    <div class="card feature-card p-4">
                        <div class="text-center">
                            <i class="fas fa-mobile-alt feature-icon"></i>
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title">Mobile Friendly</h5>
                            <p class="card-text">Access EduTrace from any device, anywhere, anytime.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="section bg-light">
        <div class="container">
            <h2 class="text-center section-title">What Our Users Say</h2>
            <p class="text-center mb-5">Hear from educators who are using EduTrace</p>

            <div class="row g-4">
                <!-- Testimonial 1 -->
                <div class="col-md-4">
                    <div class="card testimonial-card p-4 h-100">
                        <div class="card-body">
                            <p class="card-text mb-4">"EduTrace has transformed how we manage student data. The
                                attendance tracking alone has saved us countless hours each week."</p>
                            <div class="d-flex align-items-center">
                                <img src="images/principal.jpg" alt="Testimonial"
                                    class="rounded-circle me-3" height="80px">
                                <div>
                                    <h6 class="mb-0">Sarah Johnson</h6>
                                    <small class="text-muted">Principal, Greenfield High</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="col-md-4">
                    <div class="card testimonial-card p-4 h-100">
                        <div class="card-body">
                            <p class="card-text mb-4">"The grade management system is incredibly intuitive. I can focus
                                more on teaching and less on paperwork thanks to EduTrace."</p>
                            <div class="d-flex align-items-center">
                                <img src="images/teacher.jpg" alt="Testimonial"
                                    class="rounded-circle me-3" height="80px">
                                <div>
                                    <h6 class="mb-0">Michael Chen</h6>
                                    <small class="text-muted">Teacher, Riverside Academy</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="col-md-4">
                    <div class="card testimonial-card p-4 h-100">
                        <div class="card-body">
                            <p class="card-text mb-4">"As an administrator, I appreciate how EduTrace brings all our
                                student data into one easy-to-use platform with excellent reporting."</p>
                            <div class="d-flex align-items-center">
                                <img src="images/student.jpg" alt="Testimonial"
                                    class="rounded-circle me-3" height="80px">
                                <div>
                                    <h6 class="mb-0">David Wilson</h6>
                                    <small class="text-muted">Administrator, Summit College</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section">
        <div class="container">
            <h2 class="text-center section-title">Contact Us</h2>
            <p class="text-center mb-5">Have questions? Get in touch with our team</p>

            <div class="row">
                <div class="col-lg-6">
                    <form class="contact-form">
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Your Name" required>
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Your Email" required>
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Subject">
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" rows="5" placeholder="Your Message" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
                <div class="col-lg-6 mt-4 mt-lg-0">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">Contact Information</h5>
                            <div class="d-flex align-items-start mb-4">
                                <i class="fas fa-map-marker-alt me-3 mt-1 text-primary"></i>
                                <div>
                                    <h6 class="mb-1">Address</h6>
                                    <p class="mb-0 text-muted">123 Education Street, Learning City, LC 12345</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-4">
                                <i class="fas fa-phone-alt me-3 mt-1 text-primary"></i>
                                <div>
                                    <h6 class="mb-1">Phone</h6>
                                    <p class="mb-0 text-muted">(123) 456-7890</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-4">
                                <i class="fas fa-envelope me-3 mt-1 text-primary"></i>
                                <div>
                                    <h6 class="mb-1">Email</h6>
                                    <p class="mb-0 text-muted">info@edutrace.com</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <i class="fas fa-clock me-3 mt-1 text-primary"></i>
                                <div>
                                    <h6 class="mb-1">Hours</h6>
                                    <p class="mb-0 text-muted">Monday - Friday: 9:00 AM - 5:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="mb-4"><i class="fas fa-graduation-cap me-2"></i>EduTrace</h5>
                    <p>Smart student management system for educational institutions of all sizes.</p>
                    <div class="social-icons mt-4">
                        <a href="#" class="me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h5 class="mb-4">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="../homepage/home.php" class="text-muted">Home</a></li>
                        <li class="mb-2"><a href="#about" class="text-muted">About</a></li>
                        <li class="mb-2"><a href="#features" class="text-muted">Features</a></li>
                        <li class="mb-2"><a href="#contact" class="text-muted">Contact</a></li>
                        <li><a href="../auth/login.php" class="text-muted">Login</a></li>
                        <li><a href="../auth/register.php" class="text-muted">Register</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h5 class="mb-4">Features</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="../student/profile.php" class="text-muted">Student Profiles</a></li>
                        <li class="mb-2"><a href="../student/attendance.php" class="text-muted">Attendance</a></li>
                        <li class="mb-2"><a href="../student/marks.php" class="text-muted">Gradebook</a></li>
                        <li class="mb-2"><a href="../admin/reports.php" class="text-muted">Reports</a></li>
                        <li><a href="#" class="text-muted">Mobile Access</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="mb-4">Newsletter</h5>
                    <p class="text-muted">Subscribe to get updates and educational tips.</p>
                    <form class="mt-3">
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="Your Email">
                            <button class="btn btn-primary" type="button">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-muted">&copy; 2023 EduTrace. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end footer-links">
                    <a href="#" class="text-muted">Terms of Service</a>
                    <a href="#" class="text-muted">Privacy Policy</a>
                    <a href="#" class="text-muted">Cookie Policy</a>
                    <a href="../auth/login.php" class="text-muted">Login</a>
                    <a href="../auth/register.php" class="text-muted">Register</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <!-- <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Login to EduTrace</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="loginEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="loginPassword" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Remember me</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="#" class="text-muted">Forgot password?</a>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <p class="mb-0">Don't have an account? <a href="#" data-bs-toggle="modal"
                            data-bs-target="#registerModal" data-bs-dismiss="modal">Register</a></p>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Register Modal -->
    <!-- <div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create an Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="registerEmail" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="registerEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="registerPassword" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPassword" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="agreeTerms" required>
                            <label class="form-check-label" for="agreeTerms">I agree to the <a href="#">Terms of
                                    Service</a> and <a href="#">Privacy Policy</a></label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>
                </div>
                <div class="modal-footer justify-content-center">
                    <p class="mb-0">Already have an account? <a href="#" data-bs-toggle="modal"
                            data-bs-target="#loginModal" data-bs-dismiss="modal">Login</a></p>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
     <script src="js/script.js"></script>
</body>

</html>