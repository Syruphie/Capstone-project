<?php
require_once 'config/database.php';
require_once 'src/classes/Frontend/bootstrap.php';

$user = new FrontendUser();
$isLoggedIn = $user->isLoggedIn();

// Sample catalog data
$oreServices = [
    [
        'id' => 'gold-ore',
        'name' => 'Gold Ore Analysis',
        'category' => 'Precious Metals',
        'description' => 'Comprehensive gold content analysis using fire assay and atomic absorption spectroscopy (AAS) methods. Ideal for mining exploration and production quality control.',
        'methods' => ['Fire Assay', 'Atomic Absorption Spectroscopy', 'Gravimetric Analysis'],
        'turnaround' => '3-5 business days',
        'priority_turnaround' => '1-2 business days',
        'sample_size' => '50-100g minimum',
        'detection_limit' => '0.01 ppm',
        'accreditation' => 'ISO 17025'
    ],
    [
        'id' => 'silver-ore',
        'name' => 'Silver Ore Analysis',
        'category' => 'Precious Metals',
        'description' => 'Accurate silver content determination for ore samples using fire assay and ICP-OES methods.',
        'methods' => ['Fire Assay', 'ICP-OES', 'Gravimetric Analysis'],
        'turnaround' => '3-5 business days',
        'priority_turnaround' => '1-2 business days',
        'sample_size' => '50-100g minimum',
        'detection_limit' => '0.5 ppm',
        'accreditation' => 'ISO 17025'
    ],
    [
        'id' => 'copper-ore',
        'name' => 'Copper Ore Analysis',
        'category' => 'Base Metals',
        'description' => 'Complete copper grade determination and mineral composition analysis for mining operations and exploration projects.',
        'methods' => ['ICP-OES', 'XRF Analysis', 'Titration'],
        'turnaround' => '2-4 business days',
        'priority_turnaround' => '1 business day',
        'sample_size' => '30-50g minimum',
        'detection_limit' => '0.01%',
        'accreditation' => 'ISO 17025'
    ],
    [
        'id' => 'iron-ore',
        'name' => 'Iron Ore Analysis',
        'category' => 'Base Metals',
        'description' => 'Complete iron ore testing including total Fe content, FeO, silica, alumina, phosphorus, and trace element analysis.',
        'methods' => ['XRF Analysis', 'Titration', 'ICP-OES'],
        'turnaround' => '2-3 business days',
        'priority_turnaround' => '1 business day',
        'sample_size' => '30-50g minimum',
        'detection_limit' => '0.01%',
        'accreditation' => 'ISO 17025'
    ],
    [
        'id' => 'zinc-lead-ore',
        'name' => 'Zinc & Lead Ore Analysis',
        'category' => 'Base Metals',
        'description' => 'Multi-element analysis for zinc and lead ores including associated elements and impurity profiling.',
        'methods' => ['ICP-OES', 'AAS', 'XRF Analysis'],
        'turnaround' => '2-4 business days',
        'priority_turnaround' => '1 business day',
        'sample_size' => '30-50g minimum',
        'detection_limit' => '0.01%',
        'accreditation' => 'ISO 17025'
    ],
    [
        'id' => 'nickel-ore',
        'name' => 'Nickel Ore Analysis',
        'category' => 'Base Metals',
        'description' => 'Comprehensive nickel ore analysis including cobalt, magnesium, and other associated elements.',
        'methods' => ['ICP-OES', 'XRF Analysis', 'Gravimetric'],
        'turnaround' => '2-4 business days',
        'priority_turnaround' => '1 business day',
        'sample_size' => '30-50g minimum',
        'detection_limit' => '0.01%',
        'accreditation' => 'ISO 17025'
    ]
];

$liquidServices = [
    [
        'id' => 'water-quality',
        'name' => 'Water Quality Testing',
        'category' => 'Environmental',
        'description' => 'Comprehensive water analysis for industrial, environmental, and drinking water compliance testing.',
        'methods' => ['ICP-MS', 'Ion Chromatography', 'Spectrophotometry'],
        'turnaround' => '1-2 business days',
        'priority_turnaround' => 'Same day',
        'sample_size' => '500ml minimum',
        'parameters' => 'pH, TDS, Heavy Metals, Anions, Cations',
        'accreditation' => 'ISO 17025, EPA Methods'
    ],
    [
        'id' => 'wastewater',
        'name' => 'Wastewater Analysis',
        'category' => 'Environmental',
        'description' => 'Industrial and municipal wastewater testing for regulatory compliance and treatment optimization.',
        'methods' => ['COD/BOD Analysis', 'ICP-OES', 'Spectrophotometry'],
        'turnaround' => '2-3 business days',
        'priority_turnaround' => '1 business day',
        'sample_size' => '1L minimum',
        'parameters' => 'COD, BOD, TSS, Nutrients, Metals',
        'accreditation' => 'ISO 17025, EPA Methods'
    ],
    [
        'id' => 'oil-fuel',
        'name' => 'Oil & Fuel Analysis',
        'category' => 'Petroleum',
        'description' => 'Testing for petroleum products including viscosity, flash point, water content, and contamination levels.',
        'methods' => ['Viscometry', 'Karl Fischer', 'ICP-OES'],
        'turnaround' => '2-3 business days',
        'priority_turnaround' => '1 business day',
        'sample_size' => '250ml minimum',
        'parameters' => 'Viscosity, Flash Point, Water Content, Metals',
        'accreditation' => 'ISO 17025, ASTM Methods'
    ],
    [
        'id' => 'lubricants',
        'name' => 'Lubricant Analysis',
        'category' => 'Petroleum',
        'description' => 'Used oil analysis and lubricant quality testing for equipment maintenance programs.',
        'methods' => ['ICP-OES', 'Viscometry', 'Particle Count'],
        'turnaround' => '1-2 business days',
        'priority_turnaround' => 'Same day',
        'sample_size' => '100ml minimum',
        'parameters' => 'Wear Metals, Viscosity, TAN/TBN, Contaminants',
        'accreditation' => 'ISO 17025'
    ],
    [
        'id' => 'chemical-solutions',
        'name' => 'Chemical Solutions Analysis',
        'category' => 'Industrial',
        'description' => 'Analysis of chemical solutions for concentration, purity, and composition verification.',
        'methods' => ['Titration', 'ICP-OES', 'Spectrophotometry'],
        'turnaround' => '1-3 business days',
        'priority_turnaround' => '1 business day',
        'sample_size' => '100ml minimum',
        'parameters' => 'Concentration, Purity, Specific Gravity',
        'accreditation' => 'ISO 17025'
    ],
    [
        'id' => 'process-liquors',
        'name' => 'Process Liquor Analysis',
        'category' => 'Industrial',
        'description' => 'Mining and metallurgical process solution analysis for process control and optimization.',
        'methods' => ['ICP-OES', 'AAS', 'Titration'],
        'turnaround' => '1-2 business days',
        'priority_turnaround' => 'Same day',
        'sample_size' => '250ml minimum',
        'parameters' => 'Metal Content, Acid/Base, Free Cyanide',
        'accreditation' => 'ISO 17025'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalog - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/landing.css">
    <link rel="stylesheet" href="css/catalog.css">
</head>
<body class="landing-page catalog-page">
    <!-- Header -->
    <header class="landing-header scrolled">
        <div class="landing-header-content">
            <div class="landing-logo">
                <a href="index.php">
                    <span class="logo-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 3V10L6 14V21H18V14L15 10V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 3H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M6 14H18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="10" cy="17" r="1" fill="currentColor"/>
                            <circle cx="14" cy="17" r="1" fill="currentColor"/>
                        </svg>
                    </span>
                    <?php echo APP_NAME; ?>
                </a>
            </div>
            <nav class="landing-nav">
                <a href="index.php#features">Features</a>
                <a href="index.php#services">Services</a>
                <a href="catalog.php" class="active">Catalog</a>
                <a href="index.php#about">About</a>
            </nav>
            <div class="landing-header-actions">
                <?php if ($isLoggedIn): ?>
                    <a href="dashboard.php" class="btn btn-white">Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-white">Log In</a>
                    <a href="register.php" class="btn btn-white">Get Started</a>
                <?php endif; ?>
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
            <a href="index.php#features">Features</a>
            <a href="index.php#services">Services</a>
            <a href="catalog.php">Catalog</a>
            <a href="index.php#about">About</a>
            <?php if ($isLoggedIn): ?>
                <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline">Log In</a>
                <a href="register.php" class="btn btn-white">Get Started</a>
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
                        <a href="create-order.php" class="btn btn-primary btn-small">Order Now</a>
                        <?php else: ?>
                        <a href="register.php" class="btn btn-primary btn-small">Get Started</a>
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
                        <a href="create-order.php" class="btn btn-primary btn-small">Order Now</a>
                        <?php else: ?>
                        <a href="register.php" class="btn btn-primary btn-small">Get Started</a>
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
                    <a href="create-order.php" class="btn btn-white btn-large">Create Order</a>
                    <?php else: ?>
                    <a href="register.php" class="btn btn-white btn-large">Get Started</a>
                    <a href="login.php" class="btn btn-outline-white btn-large">Sign In</a>
                    <?php endif; ?>
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
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 3V10L6 14V21H18V14L15 10V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9 3H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M6 14H18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
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
                        <li><a href="index.php#features">Features</a></li>
                        <li><a href="index.php#services">Services</a></li>
                        <li><a href="catalog.php">Product Catalog</a></li>
                        <li><a href="index.php#about">About</a></li>
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

        mobileMenuBtn.addEventListener('click', function() {
            mobileMenuBtn.classList.toggle('active');
            mobileMenu.classList.toggle('active');
        });

        // Filter functionality
        const filterTabs = document.querySelectorAll('.filter-tab');
        const catalogCards = document.querySelectorAll('.catalog-card');
        const oreSection = document.getElementById('ore-section');
        const liquidSection = document.getElementById('liquid-section');

        filterTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                filterTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const filter = this.dataset.filter;

                if (filter === 'all') {
                    oreSection.style.display = 'block';
                    liquidSection.style.display = 'block';
                    catalogCards.forEach(card => card.style.display = 'block');
                } else if (filter === 'ore') {
                    oreSection.style.display = 'block';
                    liquidSection.style.display = 'none';
                } else if (filter === 'liquid') {
                    oreSection.style.display = 'none';
                    liquidSection.style.display = 'block';
                }
            });
        });

        // Search functionality
        const searchInput = document.getElementById('catalogSearch');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            catalogCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>

