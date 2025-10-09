<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EduTrace - Student Management System</title>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Custom CSS -->
<link rel="stylesheet" href="css/style.css">
</head>

<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
  <div class="container">
    <a class="navbar-brand" href="#"><img src="images/logo/logo.png" alt="EduTrace" style="height:40px;" class="me-2"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
        <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
        <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
        <li class="nav-item"><a class="nav-link" href="../auth/login.php">Login</a></li>
        <li class="nav-item"><a class="btn btn-primary ms-lg-2 mt-2 mt-lg-0" href="../auth/register.php">Register</a></li>
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
        <p class="hero-subtitle">EduTrace simplifies student management with powerful tools for attendance, grades, and more.</p>
        <div class="d-flex gap-3">
          <a href="#features" class="btn btn-light btn-lg">Explore Features</a>
          <a href="../auth/register.php" class="btn btn-outline-light btn-lg">Get Started</a>
        </div>
      </div>
      <div class="col-lg-6 mt-5 mt-lg-0">
        <img src="images/sdash.png" alt="EduTrace Dashboard" class="hero-img">
      </div>
    </div>
  </div>
</section>

<!-- About Section -->
<section id="about" class="section bg-light">
  <div class="container">
    <h2 class="text-center section-title">About EduTrace</h2>
    <div class="row align-items-center">
      <div class="col-lg-6"><img src="images/about.jpeg" class="img-fluid rounded" alt="About EduTrace"></div>
      <div class="col-lg-6 mt-4 mt-lg-0">
        <h3>Simplifying Student Management</h3>
        <p>EduTrace helps institutions manage student data, attendance, grades efficiently.</p>
        <p>Our platform provides powerful tools for educators to focus on teaching and student growth.</p>
        <p>Suitable for schools, colleges, and training centers of all sizes.</p>
        <a href="#features" class="btn btn-primary mt-3">Learn More</a>
      </div>
    </div>
  </div>
</section>

<!-- Features Section -->
<section id="features" class="section">
  <div class="container">
    <h2 class="text-center section-title">Key Features</h2>
    <p class="text-center mb-5">Discover the powerful features of EduTrace</p>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card feature-card p-4 text-center">
          <i class="fas fa-user-graduate feature-icon"></i>
          <h5>Student Profiles</h5>
          <p>Complete student profiles with academic and personal info.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card feature-card p-4 text-center">
          <i class="fas fa-calendar-check feature-icon"></i>
          <h5>Attendance Tracking</h5>
          <p>Easily record and monitor student attendance with automated reports.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card feature-card p-4 text-center">
          <i class="fas fa-chart-line feature-icon"></i>
          <h5>Grade Management</h5>
          <p>Track performance, calculate grades, and generate report cards.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card feature-card p-4 text-center">
          <i class="fas fa-bell feature-icon"></i>
          <h5>Notifications</h5>
          <p>Automated alerts for attendance, grades, and announcements.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card feature-card p-4 text-center">
          <i class="fas fa-chart-pie feature-icon"></i>
          <h5>Analytics</h5>
          <p>Visual reports to track class and student performance.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card feature-card p-4 text-center">
          <i class="fas fa-mobile-alt feature-icon"></i>
          <h5>Mobile Friendly</h5>
          <p>Access EduTrace from any device, anywhere.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Contact Section -->
<section id="contact" class="section bg-light">
  <div class="container">
    <h2 class="text-center section-title">Contact Us</h2>
    <div class="row">
      <div class="col-lg-6">
        <form class="contact-form">
          <input type="text" class="form-control" placeholder="Your Name" required>
          <input type="email" class="form-control" placeholder="Your Email" required>
          <input type="text" class="form-control" placeholder="Subject">
          <textarea class="form-control" rows="5" placeholder="Your Message" required></textarea>
          <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
      </div>
      <div class="col-lg-6 mt-4 mt-lg-0">
        <div class="card border-0 shadow-sm h-100 p-4">
          <h5>Contact Information</h5>
          <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-primary"></i>123 Education Street, Learning City</p>
          <p class="mb-1"><i class="fas fa-phone-alt me-2 text-primary"></i>(123) 456-7890</p>
          <p class="mb-1"><i class="fas fa-envelope me-2 text-primary"></i>info@edutrace.com</p>
          <p class="mb-0"><i class="fas fa-clock me-2 text-primary"></i>Mon-Fri: 9:00 AM - 5:00 PM</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="footer">
  <div class="container text-center">
    <p class="mb-0">&copy; 2025 EduTrace. All rights reserved.</p>
  </div>
</footer>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
