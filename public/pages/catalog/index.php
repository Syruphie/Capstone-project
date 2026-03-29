<?php
require_once __DIR__ . '/../bootstrap_paths.php';

$user = new FrontendUser();
$isLoggedIn = $user->isLoggedIn();

$catalogData = require PAGE_DATA . '/catalog-services.php';
$oreServices = $catalogData['ore'];
$liquidServices = $catalogData['liquid'];

$publicHeaderVariant = 'catalog';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php include PAGE_PARTIALS . '/html-base.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalog - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(app_path('css/style.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(app_path('css/landing.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(app_path('css/catalog.css')); ?>">
</head>
<body class="landing-page catalog-page">
<?php /** @var string $publicHeaderVariant 'home'|'catalog' */
$publicHeaderVariant = $publicHeaderVariant ?? 'home';
$isLoggedIn = $isLoggedIn ?? false;
$headerClass = $publicHeaderVariant === 'catalog' ? 'landing-header scrolled' : 'landing-header';
$stroke = $publicHeaderVariant === 'catalog' ? 'currentColor' : '#fff';
?>
    <!-- Header -->
    <header class="<?php echo htmlspecialchars($headerClass, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="landing-header-content">
            <div class="landing-logo">
                <a href="<?php echo htmlspecialchars(app_path('index.php')); ?>">
                    <span class="logo-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 3V10L6 14V21H18V14L15 10V3" stroke="<?php echo $stroke; ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 3H15" stroke="<?php echo $stroke; ?>" stroke-width="2" stroke-linecap="round"/>
                            <path d="M6 14H18" stroke="<?php echo $stroke; ?>" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="10" cy="17" r="1" fill="<?php echo $stroke === 'currentColor' ? 'currentColor' : '#fff'; ?>"/>
                            <circle cx="14" cy="17" r="1" fill="<?php echo $stroke === 'currentColor' ? 'currentColor' : '#fff'; ?>"/>
                        </svg>
                    </span>
                    <?php echo APP_NAME; ?>
                </a>
            </div>
            <?php if ($publicHeaderVariant === 'catalog'): ?>
            <nav class="landing-nav">
                <a href="<?php echo htmlspecialchars(app_path('index.php') . '#features'); ?>">Features</a>
                <a href="<?php echo htmlspecialchars(app_path('index.php') . '#services'); ?>">Services</a>
                <a href="<?php echo htmlspecialchars(app_path('catalog/index.php')); ?>" class="active">Catalog</a>
                <a href="<?php echo htmlspecialchars(app_path('index.php') . '#about'); ?>">About</a>
            </nav>
            <div class="landing-header-actions">
                <?php if ($isLoggedIn): ?>
                    <a href="<?php echo htmlspecialchars(app_path('dashboard/index.php')); ?>" class="btn btn-white">Dashboard</a>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars(app_path('auth/login.php')); ?>" class="btn btn-outline-white">Log In</a>
                    <a href="<?php echo htmlspecialchars(app_path('auth/register.php')); ?>" class="btn btn-white">Get Started</a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <nav class="landing-nav">
                <a href="#features">Features</a>
                <a href="#services">Services</a>
                <a href="#how-it-works">How It Works</a>
                <a href="#about">About</a>
            </nav>
            <div class="landing-header-actions">
                <a href="<?php echo htmlspecialchars(app_path('auth/login.php')); ?>" class="btn btn-outline-white">Log In</a>
                <a href="<?php echo htmlspecialchars(app_path('auth/register.php')); ?>" class="btn btn-white">Get Started</a>
            </div>
            <?php endif; ?>
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
<?php /** @var string $publicHeaderVariant 'home'|'catalog' */
$publicHeaderVariant = $publicHeaderVariant ?? 'home';
$isLoggedIn = $isLoggedIn ?? false;
?>
    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <nav class="mobile-nav">
            <?php if ($publicHeaderVariant === 'catalog'): ?>
            <a href="<?php echo htmlspecialchars(app_path('index.php') . '#features'); ?>">Features</a>
            <a href="<?php echo htmlspecialchars(app_path('index.php') . '#services'); ?>">Services</a>
            <a href="<?php echo htmlspecialchars(app_path('catalog/index.php')); ?>">Catalog</a>
            <a href="<?php echo htmlspecialchars(app_path('index.php') . '#about'); ?>">About</a>
            <?php if ($isLoggedIn): ?>
                <a href="<?php echo htmlspecialchars(app_path('dashboard/index.php')); ?>" class="btn btn-primary">Dashboard</a>
            <?php else: ?>
                <a href="<?php echo htmlspecialchars(app_path('auth/login.php')); ?>" class="btn btn-outline">Log In</a>
                <a href="<?php echo htmlspecialchars(app_path('auth/register.php')); ?>" class="btn btn-white">Get Started</a>
            <?php endif; ?>
            <?php else: ?>
            <a href="#features">Features</a>
            <a href="#services">Services</a>
            <a href="#how-it-works">How It Works</a>
            <a href="#about">About</a>
            <a href="<?php echo htmlspecialchars(app_path('auth/login.php')); ?>" class="btn btn-outline">Log In</a>
            <a href="<?php echo htmlspecialchars(app_path('auth/register.php')); ?>" class="btn btn-white">Get Started</a>
            <?php endif; ?>
        </nav>
    </div>
<!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Product Catalog</h1>
            <p>Comprehensive laboratory analysis services for ore and liquid samples</p>
        </div>
    </section>
<!-- Filter Bar -->
    <section class="catalog-filters">
        <div class="container">
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">All Services</button>
                <button class="filter-tab" data-filter="ore">Ore Analysis</button>
                <button class="filter-tab" data-filter="liquid">Liquid Analysis</button>
            </div>
            <div class="filter-search">
                <input type="text" id="catalogSearch" placeholder="Search services...">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                    <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
        </div>
    </section>
<!-- Ore Analysis Section -->
    <section class="catalog-section" id="ore-section">
        <div class="container">
            <div class="catalog-section-header">
                <div class="catalog-section-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7l10 5 10-5-10-5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <h2>Ore Analysis Services</h2>
                    <p>Professional analysis for mineral and ore samples</p>
                </div>
            </div>
            <div class="catalog-grid">
                <?php foreach ($oreServices as $service): ?>
                <div class="catalog-card" data-type="ore" data-category="<?php echo strtolower($service['category']); ?>">
                    <div class="catalog-card-header">
                        <span class="catalog-category"><?php echo htmlspecialchars($service['category']); ?></span>
                        <span class="catalog-accreditation"><?php echo htmlspecialchars($service['accreditation']); ?></span>
                    </div>
                    <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                    <p><?php echo htmlspecialchars($service['description']); ?></p>

                    <div class="catalog-details">
                        <div class="detail-group">
                            <h4>Methods</h4>
                            <ul>
                                <?php foreach ($service['methods'] as $method): ?>
                                <li><?php echo htmlspecialchars($method); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="detail-group">
                            <h4>Specifications</h4>
                            <div class="spec-row">
                                <span class="spec-label">Sample Size:</span>
                                <span class="spec-value"><?php echo htmlspecialchars($service['sample_size']); ?></span>
                            </div>
                            <div class="spec-row">
                                <span class="spec-label">Detection Limit:</span>
                                <span class="spec-value"><?php echo htmlspecialchars($service['detection_limit']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="catalog-card-footer">
                        <div class="turnaround-info">
                            <div class="turnaround-item">
                                <span class="turnaround-label">Standard</span>
                                <span class="turnaround-time"><?php echo htmlspecialchars($service['turnaround']); ?></span>
                            </div>
                            <div class="turnaround-item priority">
                                <span class="turnaround-label">Priority</span>
                                <span class="turnaround-time"><?php echo htmlspecialchars($service['priority_turnaround']); ?></span>
                            </div>
                        </div>
                        <?php if ($isLoggedIn): ?>
                        <a href="<?php echo htmlspecialchars(app_path('orders/create-order.php')); ?>" class="btn btn-primary btn-small">Order Now</a>
                        <?php else: ?>
                        <a href="<?php echo htmlspecialchars(app_path('auth/register.php')); ?>" class="btn btn-primary btn-small">Get Started</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<!-- Liquid Analysis Section -->
    <section class="catalog-section" id="liquid-section">
        <div class="container">
            <div class="catalog-section-header">
                <div class="catalog-section-icon liquid">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0L12 2.69z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <h2>Liquid Analysis Services</h2>
                    <p>Comprehensive testing for water, oil, and chemical solutions</p>
                </div>
            </div>
            <div class="catalog-grid">
                <?php foreach ($liquidServices as $service): ?>
                <div class="catalog-card" data-type="liquid" data-category="<?php echo strtolower($service['category']); ?>">
                    <div class="catalog-card-header">
                        <span class="catalog-category liquid"><?php echo htmlspecialchars($service['category']); ?></span>
                        <span class="catalog-accreditation"><?php echo htmlspecialchars($service['accreditation']); ?></span>
                    </div>
                    <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                    <p><?php echo htmlspecialchars($service['description']); ?></p>

                    <div class="catalog-details">
                        <div class="detail-group">
                            <h4>Methods</h4>
                            <ul>
                                <?php foreach ($service['methods'] as $method): ?>
                                <li><?php echo htmlspecialchars($method); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="detail-group">
                            <h4>Parameters</h4>
                            <div class="spec-row">
                                <span class="spec-label">Sample Size:</span>
                                <span class="spec-value"><?php echo htmlspecialchars($service['sample_size']); ?></span>
                            </div>
                            <div class="spec-row">
                                <span class="spec-label">Tests:</span>
                                <span class="spec-value"><?php echo htmlspecialchars($service['parameters']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="catalog-card-footer">
                        <div class="turnaround-info">
                            <div class="turnaround-item">
                                <span class="turnaround-label">Standard</span>
                                <span class="turnaround-time"><?php echo htmlspecialchars($service['turnaround']); ?></span>
                            </div>
                            <div class="turnaround-item priority">
                                <span class="turnaround-label">Priority</span>
                                <span class="turnaround-time"><?php echo htmlspecialchars($service['priority_turnaround']); ?></span>
                            </div>
                        </div>
                        <?php if ($isLoggedIn): ?>
                        <a href="<?php echo htmlspecialchars(app_path('orders/create-order.php')); ?>" class="btn btn-primary btn-small">Order Now</a>
                        <?php else: ?>
                        <a href="<?php echo htmlspecialchars(app_path('auth/register.php')); ?>" class="btn btn-primary btn-small">Get Started</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<!-- CTA Section -->
    <section class="catalog-cta">
        <div class="container">
            <div class="cta-content">
                <h2>Need a Custom Analysis?</h2>
                <p>Contact us for specialized testing requirements or bulk order pricing.</p>
                <div class="cta-actions">
                    <?php if ($isLoggedIn): ?>
                    <a href="<?php echo htmlspecialchars(app_path('orders/create-order.php')); ?>" class="btn btn-white btn-large">Create Order</a>
                    <?php else: ?>
                    <a href="<?php echo htmlspecialchars(app_path('auth/register.php')); ?>" class="btn btn-white btn-large">Get Started</a>
                    <a href="<?php echo htmlspecialchars(app_path('auth/login.php')); ?>" class="btn btn-outline-white btn-large">Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php $landingLinkPrefix = 'index.php'; ?>
<?php /** @var string $landingLinkPrefix '' on index (hash-only links); 'index.php' from catalog */
$landingLinkPrefix = $landingLinkPrefix ?? '';
$fl = static function (string $hash) use ($landingLinkPrefix): string {
    if ($landingLinkPrefix === '') {
        return $hash;
    }

    return app_path('index.php') . $hash;
};
?>
    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="landing-logo">
                        <a href="<?php echo htmlspecialchars(app_path('index.php')); ?>">
                            <span class="logo-icon">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 3V10L6 14V21H18V14L15 10V3" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9 3H15" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M6 14H18" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
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
                        <li><a href="<?php echo htmlspecialchars($fl('#features')); ?>">Features</a></li>
                        <li><a href="<?php echo htmlspecialchars($fl('#services')); ?>">Services</a></li>
                        <li><a href="<?php echo htmlspecialchars(app_path('catalog/index.php')); ?>">Product Catalog</a></li>
                        <li><a href="<?php echo htmlspecialchars($fl('#about')); ?>">About</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Account</h4>
                    <ul>
                        <li><a href="<?php echo htmlspecialchars(app_path('auth/login.php')); ?>">Login</a></li>
                        <li><a href="<?php echo htmlspecialchars(app_path('auth/register.php')); ?>">Register</a></li>
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
    <script type="module" src="<?php echo htmlspecialchars(app_path('frontend/src/pages/catalog/catalog.js')); ?>"></script>
</body>
</html>
