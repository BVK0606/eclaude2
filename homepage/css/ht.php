/* Custom styles */
:root {
--primary-color: #4e73df;
--secondary-color: #2e59d9;
--light-color: #f8f9fc;
--dark-color: #5a5c69;
}

body {
font-family: 'Nunito', sans-serif;
color: var(--dark-color);
}

/* Navbar styling */
.navbar {
background-color: white;
box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.navbar-brand {
font-weight: 800;
font-size: 1.5rem;
color: var(--primary-color);
}

.nav-link {
font-weight: 600;
padding: 0.5rem 1rem;
}

/* Hero section */
.hero-section {
background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
color: white;
padding: 5rem 0;
}

.hero-title {
font-weight: 800;
font-size: 2.5rem;
margin-bottom: 1rem;
}

.hero-subtitle {
font-size: 1.25rem;
margin-bottom: 2rem;
}

.hero-img {
max-width: 100%;
height: auto;
border-radius: 0.35rem;
}

/* Button styling */
.btn-primary {
background-color: var(--primary-color);
border-color: var(--primary-color);
}

.btn-primary:hover {
background-color: var(--secondary-color);
border-color: var(--secondary-color);
}

.btn-outline-light {
border-width: 2px;
}

/* Section styling */
.section {
padding: 5rem 0;
}

.section-title {
font-weight: 800;
margin-bottom: 2rem;
color: var(--primary-color);
}

/* Feature cards */
.feature-card {
border: none;
border-radius: 0.35rem;
box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
transition: transform 0.3s;
height: 100%;
}

.feature-card:hover {
transform: translateY(-5px);
}

.feature-icon {
font-size: 2.5rem;
color: var(--primary-color);
margin-bottom: 1rem;
}

/* Testimonials */
.testimonial-card {
border-left: 0.25rem solid var(--primary-color);
}

/* Contact form */
.contact-form .form-control {
border-radius: 0.35rem;
padding: 0.75rem 1rem;
margin-bottom: 1rem;
}

/* Footer */
.footer {
background-color: var(--light-color);
padding: 3rem 0;
}

.footer-links a {
color: var(--dark-color);
text-decoration: none;
margin: 0 0.5rem;
}

.footer-links a:hover {
color: var(--primary-color);
}