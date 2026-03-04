<?php
require_once 'config/database.php';
require_once 'classes/User.php';

$user = new User();

// Redirect if already logged in
if ($user->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Laboratory Order Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/landing.css">
</head>

<body class="landing-page">
    <!-- Header -->
    <header class="landing-header">
        <div class="landing-header-content">
            <div class="landing-logo">
                <a href="index.php">
                    <span class="logo-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 3V10L6 14V21H18V14L15 10V3" stroke="#fff" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M9 3H15" stroke="#fff" stroke-width="2" stroke-linecap="round" />
                            <path d="M6 14H18" stroke="#fff" stroke-width="2" stroke-linecap="round" />
                            <circle cx="10" cy="17" r="1" fill="#fff" />
                            <circle cx="14" cy="17" r="1" fill="#fff" />
                        </svg>
                    </span>
                    <?php echo APP_NAME; ?>
                </a>
            </div>
            <nav class="landing-nav">
                <a href="#features">Features</a>
                <a href="#services">Services</a>
                <a href="#how-it-works">How It Works</a>
                <a href="#about">About</a>
            </nav>
            <div class="landing-header-actions">
                <a href="login.php" class="btn btn-white">Log In</a>
                <a href="register.php" class="btn btn-white">Get Started</a>
            </div>
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <nav class="mobile-nav">
            <a href="#features">Features</a>
            <a href="#services">Services</a>
            <a href="#how-it-works">How It Works</a>
            <a href="#about">About</a>
            <a href="login.php" class="btn btn-outline">Log In</a>
            <a href="register.php" class="btn btn-white">Get Started</a>
        </nav>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-background">
            <div class="hero-shape hero-shape-1"></div>
            <div class="hero-shape hero-shape-2"></div>
            <div class="hero-shape hero-shape-3"></div>
        </div>
        <div class="hero-content">
            <h1 class="hero-title">
                Streamline Your <span class="gradient-text">Laboratory</span> Order Management
            </h1>
            <p class="hero-subtitle">
                A comprehensive solution for managing lab orders, tracking samples, and optimizing workflows.
                Built for efficiency, designed for simplicity.
            </p>
            <div class="hero-actions">
                <a href="register.php" class="btn btn-primary btn-large">
                    Start Free Trial
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>
                <a href="#how-it-works" class="btn btn-ghost btn-large">
                    Learn More
                </a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="stat-number">500+</span>
                    <span class="stat-label">Orders Processed</span>
                </div>
                <div class="hero-stat">
                    <span class="stat-number">99.9%</span>
                    <span class="stat-label">Uptime</span>
                </div>
                <div class="hero-stat">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Support</span>
                </div>
            </div>
        </div>
        <div class="hero-image">
            <div class="dashboard-preview">
                <div class="preview-header">
                    <div class="preview-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <span class="preview-title">Dashboard</span>
                </div>
                <div class="preview-content">
                    <div class="preview-sidebar">
                        <div class="preview-menu-item active"></div>
                        <div class="preview-menu-item"></div>
                        <div class="preview-menu-item"></div>
                        <div class="preview-menu-item"></div>
                    </div>
                    <div class="preview-main">
                        <div class="preview-cards">
                            <div class="preview-card"></div>
                            <div class="preview-card"></div>
                            <div class="preview-card"></div>
                        </div>
                        <div class="preview-table">
                            <div class="preview-row"></div>
                            <div class="preview-row"></div>
                            <div class="preview-row"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trusted By Section -->
    <section class="trusted-section">
        <div class="container">
            <p class="trusted-label">Trusted by leading organizations</p>
            <div class="trusted-logos">
                <div class="trusted-logo">GMJ Global Energy</div>
                <div class="trusted-logo">SAIT</div>
                <div class="trusted-logo">Research Labs</div>
                <div class="trusted-logo">Tech Industries</div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Features</span>
                <h2 class="section-title">Everything you need to manage your lab orders</h2>
                <p class="section-subtitle">Powerful tools designed to streamline your laboratory workflow from order
                    submission to completion.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"
                                stroke="#fff" stroke-width="2" stroke-linecap="round" />
                            <rect x="9" y="3" width="6" height="4" rx="1" stroke="#fff" stroke-width="2" />
                            <path d="M9 12h6M9 16h6" stroke="#fff" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <h3>Order Management</h3>
                    <p>Submit, track, and manage laboratory orders with an intuitive interface. Set priorities and get
                        real-time status updates.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 3V10L6 14V21H18V14L15 10V3" stroke="#fff" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M9 3H15" stroke="#fff" stroke-width="2" stroke-linecap="round" />
                            <path d="M6 14H18" stroke="#fff" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <h3>Sample Tracking</h3>
                    <p>Track individual samples through every stage of processing. Know exactly where each sample is at
                        any time.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="4" width="18" height="18" rx="2" stroke="#fff" stroke-width="2" />
                            <path d="M16 2v4M8 2v4M3 10h18" stroke="#fff" stroke-width="2" stroke-linecap="round" />
                            <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01" stroke="#fff" stroke-width="2"
                                stroke-linecap="round" />
                        </svg>
                    </div>
                    <h3>Queue Management</h3>
                    <p>Intelligent queuing system optimizes equipment usage and prioritizes urgent orders automatically.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"
                                stroke="#fff" stroke-width="2" />
                            <path d="M12 6v6l4 2" stroke="#fff" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <h3>Real-time Updates</h3>
                    <p>Get instant notifications when your order status changes. Never miss an important update.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="#fff" stroke-width="2"
                                stroke-linecap="round" />
                            <circle cx="9" cy="7" r="4" stroke="#fff" stroke-width="2" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="#fff" stroke-width="2"
                                stroke-linecap="round" />
                        </svg>
                    </div>
                    <h3>Role-Based Access</h3>
                    <p>Secure access control for customers, technicians, and administrators with tailored dashboards.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 3v18h18" stroke="#fff" stroke-width="2" stroke-linecap="round" />
                            <path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3" stroke="#fff" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h3>Analytics & Reports</h3>
                    <p>Comprehensive reporting tools provide insights into lab operations and help optimize performance.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works-section" id="how-it-works">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">How It Works</span>
                <h2 class="section-title">Get started in minutes</h2>
                <p class="section-subtitle">Our streamlined process makes lab order management simple and efficient.</p>
            </div>
            <div class="steps-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Create Account</h3>
                        <p>Register your company and verify your email to get started with your secure account.</p>
                    </div>
                </div>
                <div class="step-connector"></div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Submit Orders</h3>
                        <p>Create detailed lab orders with sample information, special requirements, and priority
                            levels.</p>
                    </div>
                </div>
                <div class="step-connector"></div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Track Progress</h3>
                        <p>Monitor your orders in real-time as they move through approval, processing, and completion.
                        </p>
                    </div>
                </div>
                <div class="step-connector"></div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3>Get Results</h3>
                        <p>Receive notifications when your samples are processed and access detailed reports.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services/Products Section -->
    <section class="services-section" id="services">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Our Services</span>
                <h2 class="section-title">Laboratory Analysis Services</h2>
                <p class="section-subtitle">Professional analysis for ore and liquid samples using state-of-the-art
                    equipment and methodologies.</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-image ore-sample"></div>
                    <div class="service-content">
                        <span class="service-category">Ore Analysis</span>
                        <h3>Gold Ore Analysis</h3>
                        <p>Comprehensive gold content analysis using fire assay and atomic absorption spectroscopy
                            methods.</p>
                        <ul class="service-features">
                            <li>Fire Assay Testing</li>
                            <li>Atomic Absorption</li>
                            <li>Detailed Reports</li>
                        </ul>
                        <div class="service-meta">
                            <span class="processing-time">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
                                    <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" />
                                </svg>
                                3-5 Days
                            </span>
                        </div>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-image copper-sample"></div>
                    <div class="service-content">
                        <span class="service-category">Ore Analysis</span>
                        <h3>Copper Ore Analysis</h3>
                        <p>Accurate copper grade determination and mineral composition analysis for mining operations.
                        </p>
                        <ul class="service-features">
                            <li>Grade Analysis</li>
                            <li>Mineral Composition</li>
                            <li>Quality Certification</li>
                        </ul>
                        <div class="service-meta">
                            <span class="processing-time">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
                                    <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" />
                                </svg>
                                2-4 Days
                            </span>
                        </div>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-image iron-sample"></div>
                    <div class="service-content">
                        <span class="service-category">Ore Analysis</span>
                        <h3>Iron Ore Analysis</h3>
                        <p>Complete iron ore testing including Fe content, silica, alumina, and trace element analysis.
                        </p>
                        <ul class="service-features">
                            <li>Fe Content Testing</li>
                            <li>Impurity Analysis</li>
                            <li>Compliance Reports</li>
                        </ul>
                        <div class="service-meta">
                            <span class="processing-time">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
                                    <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" />
                                </svg>
                                2-3 Days
                            </span>
                        </div>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-image water-sample"></div>
                    <div class="service-content">
                        <span class="service-category">Liquid Analysis</span>
                        <h3>Water Quality Testing</h3>
                        <p>Comprehensive water analysis for industrial, environmental, and drinking water compliance.
                        </p>
                        <ul class="service-features">
                            <li>Chemical Analysis</li>
                            <li>Contaminant Testing</li>
                            <li>EPA Standards</li>
                        </ul>
                        <div class="service-meta">
                            <span class="processing-time">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
                                    <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" />
                                </svg>
                                1-2 Days
                            </span>
                        </div>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-image oil-sample"></div>
                    <div class="service-content">
                        <span class="service-category">Liquid Analysis</span>
                        <h3>Oil & Fuel Analysis</h3>
                        <p>Testing for petroleum products including viscosity, flash point, and contamination levels.
                        </p>
                        <ul class="service-features">
                            <li>Viscosity Testing</li>
                            <li>Contamination Check</li>
                            <li>Quality Grading</li>
                        </ul>
                        <div class="service-meta">
                            <span class="processing-time">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
                                    <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" />
                                </svg>
                                2-3 Days
                            </span>
                        </div>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-image chemical-sample"></div>
                    <div class="service-content">
                        <span class="service-category">Liquid Analysis</span>
                        <h3>Chemical Solutions</h3>
                        <p>Analysis of chemical solutions for concentration, purity, and composition verification.</p>
                        <ul class="service-features">
                            <li>Concentration Testing</li>
                            <li>Purity Analysis</li>
                            <li>Batch Verification</li>
                        </ul>
                        <div class="service-meta">
                            <span class="processing-time">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
                                    <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" />
                                </svg>
                                1-3 Days
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="services-cta">
                <a href="catalog.php" class="btn btn-primary btn-large">View Full Catalog</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section" id="about">
        <div class="container">
            <div class="about-grid">
                <div class="about-content">
                    <span class="section-tag">About Us</span>
                    <h2 class="section-title">Built for Modern Laboratories</h2>
                    <p>GlobenTech is a laboratory order management system designed to bridge the gap between customers
                        and laboratory services. Our platform streamlines the entire order lifecycle from submission to
                        completion.</p>
                    <ul class="about-list">
                        <li>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            Secure and reliable platform
                        </li>
                        <li>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            Intuitive user interface
                        </li>
                        <li>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            Comprehensive order tracking
                        </li>
                        <li>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            Professional support team
                        </li>
                    </ul>
                    <a href="register.php" class="btn btn-primary">Get Started Today</a>
                </div>
                <div class="about-image">
                    <div class="about-card">
                        <div class="about-card-icon">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 3V10L6 14V21H18V14L15 10V3" stroke="#fff" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M9 3H15" stroke="#fff" stroke-width="2" stroke-linecap="round" />
                                <path d="M6 14H18" stroke="#fff" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </div>
                        <h4>Professional Lab Services</h4>
                        <p>Supporting ore and liquid sample analysis with state-of-the-art equipment.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to streamline your lab operations?</h2>
                <p>Join organizations that trust GlobenTech for their laboratory order management needs.</p>
                <div class="cta-actions">
                    <a href="register.php" class="btn btn-white btn-large">Create Free Account</a>
                    <a href="login.php" class="btn btn-outline-white btn-large">Sign In</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="landing-logo">
                        <a href="index.php">
                            <span class="logo-icon">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 3V10L6 14V21H18V14L15 10V3" stroke="#fff" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M9 3H15" stroke="#fff" stroke-width="2" stroke-linecap="round" />
                                    <path d="M6 14H18" stroke="#fff" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </span>
                            <?php echo APP_NAME; ?>
                        </a>
                    </div>
                    <p>Laboratory Order Management System designed for efficiency and simplicity.</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="catalog.php">Product Catalog</a></li>
                        <li><a href="#about">About</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Account</h4>
                    <ul>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Project Info</h4>
                    <ul>
                        <li>Course: CPSY 301-D</li>
                        <li>Phase 3 Prototype</li>
                        <li>SAIT - 2025</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 <?php echo APP_NAME; ?>. School Project - All rights reserved.</p>
                <p class="team-credits">Built by: Bhavya, Evan, Ahmad, Gaganpreet, Antonio, Justice</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        mobileMenuBtn.addEventListener('click', function () {
            mobileMenuBtn.classList.toggle('active');
            mobileMenu.classList.toggle('active');
        });

        // Close mobile menu on link click
        document.querySelectorAll('.mobile-nav a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenuBtn.classList.remove('active');
                mobileMenu.classList.remove('active');
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Header scroll effect
        const header = document.querySelector('.landing-header');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    </script>
</body>

</html>