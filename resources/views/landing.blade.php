<!doctype html>
<html lang="en">
<head>
    <title>NAAP Lost & Found — Report, Match, Recover</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="NAAP Lost & Found Management System — AI-powered matching to help you find lost items faster at National Aviation Academy of the Philippines." />
    <link rel="icon" type="image/png" href="{{ asset('image.png') }}" sizes="192x192" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="{{ asset('css/landing.css') }}" rel="stylesheet" />
</head>
<body>

<!-- Navigation -->
<nav class="landing-nav" id="landingNav">
    <div class="nav-container">
        <a href="/" class="nav-brand">
            <div class="nav-logo">
                <img src="{{ asset('image.png') }}" alt="NAAP Logo">
            </div>
            <span class="nav-brand-text">NAAP Lost & Found</span>
        </a>

        <div class="nav-links" id="navLinks">
            <a href="#features" class="nav-link">Features</a>
            <a href="#how-it-works" class="nav-link">How It Works</a>
            <a href="#stats" class="nav-link">Stats</a>
            <a href="#faq" class="nav-link">FAQ</a>
        </div>

        <div class="nav-actions">
            <a href="{{ route('login') }}" class="nav-btn nav-btn-ghost">Sign In</a>
            <a href="{{ route('register') }}" class="nav-btn nav-btn-primary">Get Started</a>
        </div>

        <button class="nav-mobile-toggle" id="mobileToggle" aria-label="Toggle menu">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div class="nav-mobile-menu" id="mobileMenu">
        <a href="#features" class="nav-mobile-link">Features</a>
        <a href="#how-it-works" class="nav-mobile-link">How It Works</a>
        <a href="#stats" class="nav-mobile-link">Stats</a>
        <a href="#faq" class="nav-mobile-link">FAQ</a>
        <div class="nav-mobile-actions">
            <a href="{{ route('login') }}" class="nav-btn nav-btn-ghost">Sign In</a>
            <a href="{{ route('register') }}" class="nav-btn nav-btn-primary">Get Started</a>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg">
        <div class="hero-gradient"></div>
        <div class="hero-grid"></div>
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="hero-orb hero-orb-3"></div>
    </div>

    <div class="hero-content">
        <div class="hero-badge">
            <i class="bi bi-stars"></i>
            <span>AI-Powered Matching System</span>
        </div>

        <h1 class="hero-title">
            Report. Match.<br>
            <span class="hero-title-gradient">Recover.</span>
        </h1>

        <p class="hero-subtitle">
            NAAP's intelligent lost & found platform. Upload a photo of your lost item and our AI will scan all found reports to find a match — in seconds, not days.
        </p>

        <div class="hero-cta">
            <a href="{{ route('register') }}" class="hero-btn hero-btn-primary">
                <i class="bi bi-rocket-takeoff"></i>
                Get Started Free
            </a>
            <a href="#how-it-works" class="hero-btn hero-btn-outline">
                <i class="bi bi-play-circle"></i>
                See How It Works
            </a>
        </div>

        <div class="hero-metrics">
            <div class="hero-metric">
                <span class="hero-metric-value">500+</span>
                <span class="hero-metric-label">Items Reported</span>
            </div>
            <div class="hero-metric-divider"></div>
            <div class="hero-metric">
                <span class="hero-metric-value">95%</span>
                <span class="hero-metric-label">Recovery Rate</span>
            </div>
            <div class="hero-metric-divider"></div>
            <div class="hero-metric">
                <span class="hero-metric-value">&lt;24h</span>
                <span class="hero-metric-label">Avg. Match Time</span>
            </div>
        </div>
    </div>

    <div class="hero-visual">
        <div class="hero-phone">
            <div class="phone-notch"></div>
            <div class="phone-screen">
                <div class="phone-header">
                    <div class="phone-header-dot"></div>
                    <span>Lost & Found</span>
                    <i class="bi bi-bell"></i>
                </div>
                <div class="phone-card phone-card-lost">
                    <div class="phone-card-badge phone-badge-lost"><i class="bi bi-exclamation-circle"></i> Lost</div>
                    <div class="phone-card-body">
                        <div class="phone-card-icon"><i class="bi bi-phone"></i></div>
                        <div class="phone-card-info">
                            <strong>iPhone 14 Pro</strong>
                            <span><i class="bi bi-geo-alt-fill"></i> Library, 2nd Floor</span>
                            <span><i class="bi bi-clock"></i> 2 hours ago</span>
                        </div>
                    </div>
                </div>
                <div class="phone-match-alert">
                    <div class="match-pulse"></div>
                    <i class="bi bi-diagram-2-fill"></i>
                    <div class="match-alert-text">
                        <strong>Match Found!</strong>
                        <span>95% confidence score</span>
                    </div>
                </div>
                <div class="phone-card phone-card-found">
                    <div class="phone-card-badge phone-badge-found"><i class="bi bi-check-circle"></i> Found</div>
                    <div class="phone-card-body">
                        <div class="phone-card-icon"><i class="bi bi-phone"></i></div>
                        <div class="phone-card-info">
                            <strong>iPhone 14 Pro</strong>
                            <span><i class="bi bi-geo-alt-fill"></i> Cafeteria Counter</span>
                            <span><i class="bi bi-clock"></i> 1 hour ago</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-float-badge hero-float-1">
            <i class="bi bi-camera-fill"></i> Photo AI
        </div>
        <div class="hero-float-badge hero-float-2">
            <i class="bi bi-shield-check"></i> Verified
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features" id="features">
    <div class="section-container">
        <div class="section-header">
            <div class="section-badge">Features</div>
            <h2 class="section-title">Everything you need to recover lost items</h2>
            <p class="section-subtitle">Our system combines smart technology with a simple process to maximize your chances of finding what you've lost.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon feature-icon-blue">
                    <i class="bi bi-camera-fill"></i>
                </div>
                <h3 class="feature-title">Photo Upload & Camera</h3>
                <p class="feature-desc">Snap photos directly from your phone or upload existing images. Our system analyzes visual details for better matching.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon feature-icon-purple">
                    <i class="bi bi-cpu-fill"></i>
                </div>
                <h3 class="feature-title">AI-Powered Matching</h3>
                <p class="feature-desc">GPT-4 Vision analyzes item photos to extract colors, brands, and unique features — then matches them automatically.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon feature-icon-green">
                    <i class="bi bi-bell-fill"></i>
                </div>
                <h3 class="feature-title">Instant Notifications</h3>
                <p class="feature-desc">Get notified immediately when a potential match is found. Email alerts keep you informed even when you're offline.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon feature-icon-orange">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3 class="feature-title">Secure Verification</h3>
                <p class="feature-desc">OTP email verification ensures only real users participate. Claim verification protects rightful owners.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon feature-icon-cyan">
                    <i class="bi bi-phone-fill"></i>
                </div>
                <h3 class="feature-title">Mobile Responsive</h3>
                <p class="feature-desc">Report items on the go from any device. The interface adapts perfectly to phones, tablets, and desktops.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon feature-icon-red">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h3 class="feature-title">Complete History</h3>
                <p class="feature-desc">Track your report's journey from submission to recovery. Full audit trail for transparency and accountability.</p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works" id="how-it-works">
    <div class="section-container">
        <div class="section-header">
            <div class="section-badge">How It Works</div>
            <h2 class="section-title">Three simple steps to recovery</h2>
            <p class="section-subtitle">Our streamlined process makes reporting and finding items effortless.</p>
        </div>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <div class="step-icon"><i class="bi bi-pencil-square"></i></div>
                <h3 class="step-title">Report</h3>
                <p class="step-desc">Submit a report with details and photos of your lost or found item. The more details, the better the match.</p>
            </div>

            <div class="step-connector">
                <i class="bi bi-arrow-right"></i>
            </div>

            <div class="step-card">
                <div class="step-number">2</div>
                <div class="step-icon"><i class="bi bi-diagram-2"></i></div>
                <h3 class="step-title">Match</h3>
                <p class="step-desc">Our AI analyzes photos and descriptions to find potential matches across all reports in the system.</p>
            </div>

            <div class="step-connector">
                <i class="bi bi-arrow-right"></i>
            </div>

            <div class="step-card">
                <div class="step-number">3</div>
                <div class="step-icon"><i class="bi bi-hand-thumbs-up"></i></div>
                <h3 class="step-title">Recover</h3>
                <p class="step-desc">Once a match is confirmed, coordinate with the finder through our secure system to get your item back.</p>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section" id="stats">
    <div class="section-container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" data-count="500">0</div>
                <div class="stat-label">Items Reported</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" data-count="85">0</div>
                <div class="stat-label">Successful Matches</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" data-count="95">0</div>
                <div class="stat-suffix">%</div>
                <div class="stat-label">Recovery Rate</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" data-count="24">0</div>
                <div class="stat-suffix">hrs</div>
                <div class="stat-label">Avg. Match Time</div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section" id="faq">
    <div class="section-container">
        <div class="section-header">
            <div class="section-badge">FAQ</div>
            <h2 class="section-title">Frequently asked questions</h2>
            <p class="section-subtitle">Got questions? We've got answers.</p>
        </div>

        <div class="faq-list">
            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>Who can use this system?</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>All NAAP community members — students, faculty, staff, and visitors — can create an account and report lost or found items.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>How does AI matching work?</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>When you upload a photo, our AI analyzes the image to identify the item's color, brand, category, and unique features. It then compares these against other reports to find potential matches, giving each a confidence score.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>Is my personal information safe?</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Yes. We use email OTP verification, encrypted passwords, and your contact details are never publicly visible. Only verified matches share limited information for coordination.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>What if I found an item on campus?</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Create a "Found" report with a photo and description. You can also turn it in to the OSA office. Our system will automatically try to match it with existing lost reports.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>How long are reports kept active?</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Reports remain active until resolved (matched, claimed, returned) or archived by staff. There's no automatic expiration so you never lose a chance at recovery.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="section-container">
        <div class="cta-card">
            <div class="cta-content">
                <h2 class="cta-title">Ready to find what you've lost?</h2>
                <p class="cta-subtitle">Create your account in under a minute and start reporting.</p>
                <div class="cta-actions">
                    <a href="{{ route('register') }}" class="cta-btn cta-btn-white">
                        <i class="bi bi-person-plus"></i>
                        Create Account
                    </a>
                    <a href="{{ route('login') }}" class="cta-btn cta-btn-ghost">
                        Already have an account? Sign in
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="landing-footer">
    <div class="section-container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="footer-logo">
                    <img src="{{ asset('image.png') }}" alt="NAAP Logo" loading="lazy">
                </div>
                <div class="footer-brand-text">
                    <strong>NAAP Lost & Found</strong>
                    <span>National Aviation Academy of the Philippines</span>
                    <span>Piccio Garden, Villamor, Pasay City</span>
                </div>
            </div>

            <div class="footer-links">
                <div class="footer-col">
                    <h4>System</h4>
                    <a href="{{ route('login') }}">Sign In</a>
                    <a href="{{ route('register') }}">Register</a>
                    <a href="#features">Features</a>
                </div>
                <div class="footer-col">
                    <h4>Support</h4>
                    <a href="#faq">FAQ</a>
                    <a href="mailto:naaplostandfound@gmail.com">Contact Us</a>
                </div>
                <div class="footer-col">
                    <h4>Legal</h4>
                    <a href="{{ route('terms') }}">Terms of Service</a>
                    <a href="{{ route('privacy') }}">Privacy Policy</a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} NAAP Lost & Found. All rights reserved.</p>
        </div>
    </div>
</footer>

<script>
// Navbar scroll effect
window.addEventListener('scroll', function() {
    const nav = document.getElementById('landingNav');
    nav.classList.toggle('scrolled', window.scrollY > 20);
});

// Mobile menu toggle
document.getElementById('mobileToggle').addEventListener('click', function() {
    const menu = document.getElementById('mobileMenu');
    const icon = this.querySelector('i');
    menu.classList.toggle('show');
    icon.className = menu.classList.contains('show') ? 'bi bi-x-lg' : 'bi bi-list';
});

// Close mobile menu on link click
document.querySelectorAll('.nav-mobile-link').forEach(link => {
    link.addEventListener('click', () => {
        document.getElementById('mobileMenu').classList.remove('show');
        document.querySelector('#mobileToggle i').className = 'bi bi-list';
    });
});

// FAQ accordion
document.querySelectorAll('.faq-question').forEach(btn => {
    btn.addEventListener('click', function() {
        const item = this.parentElement;
        const isOpen = item.classList.contains('open');

        // Close all
        document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));

        // Open clicked if wasn't open
        if (!isOpen) item.classList.add('open');
    });
});

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// Counter animation
function animateCounters() {
    document.querySelectorAll('.stat-number').forEach(el => {
        const target = parseInt(el.dataset.count);
        const duration = 2000;
        const start = performance.now();

        function update(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.floor(target * eased);
            if (progress < 1) requestAnimationFrame(update);
        }

        requestAnimationFrame(update);
    });
}

// Trigger counter on scroll
const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            animateCounters();
            statsObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.3 });

const statsSection = document.getElementById('stats');
if (statsSection) statsObserver.observe(statsSection);

// Scroll animations
const scrollObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
        }
    });
}, { threshold: 0.1, rootMargin: '0px 0px -60px 0px' });

document.querySelectorAll('.feature-card, .step-card, .faq-item, .stat-card').forEach(el => {
    scrollObserver.observe(el);
});
</script>
</body>
</html>
