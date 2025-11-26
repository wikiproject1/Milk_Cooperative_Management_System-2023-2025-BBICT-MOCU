<?php
// Initialize the session
session_start();

// Check if the user is logged in, if yes then redirect to appropriate dashboard
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    switch($_SESSION["role"]) {
        case "admin":
            header("location: modules/coop/dashboard.php");
            break;
        case "farmer":
            header("location: modules/farmer/dashboard.php");
            break;
        case "industry":
            header("location: modules/industry/dashboard.php");
            break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milk Cooperative System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .hero-section {
            position: relative;
            background: url('assets/images/dairy-bg.jpg') center/cover no-repeat;
            color: white;
            padding: 110px 0 90px 0;
            min-height: 60vh;
            overflow: hidden;
            animation: fadeInHero 1.2s;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.65);
            z-index: 1;
        }
        .hero-section > .container {
            position: relative;
            z-index: 2;
        }
        @keyframes fadeInHero {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: none; }
        }
        .login-options {
            margin-top: 50px;
        }
        .login-card {
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 24px rgba(0,0,0,0.13);
            border-radius: 18px;
            background: #fff;
            color: #222;
        }
        .login-card .card-body {
            padding: 2.2rem 1.2rem;
        }
        .login-card:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        }
        .login-card h3 {
            font-weight: 700;
            color: #007bff;
        }
        .login-card .fa-user { color: #007bff; }
        .login-card .fa-industry { color: #28a745; }
        .login-card .fa-user-shield { color: #dc3545; }
        .login-card p { color: #666; }
        .display-4 {
            font-size: 2.8rem;
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 1.2rem;
        }
        .hero-section .lead {
            font-size: 1.35rem;
            font-weight: 400;
            margin-bottom: 2.2rem;
        }
        @media (max-width: 767px) {
            .display-4 { font-size: 2rem; }
            .hero-section { padding: 60px 0 40px 0; }
        }
        /* Features Section */
        .features-section {
            background: #f8fafc;
            padding: 60px 0 40px 0;
        }
        .feature-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            background: #fff;
            transition: box-shadow 0.3s;
        }
        .feature-card:hover {
            box-shadow: 0 8px 32px rgba(0,0,0,0.13);
        }
        .feature-card .fa-3x {
            margin-bottom: 1.2rem;
        }
        .feature-card .fa-truck { color: #007bff; }
        .feature-card .fa-chart-line { color: #28a745; }
        .feature-card .fa-money-bill-wave { color: #ffc107; }
        .feature-card h4 {
            font-weight: 700;
            margin-bottom: 0.7rem;
        }
        .feature-card p {
            color: #555;
        }
        /* Footer */
        footer.bg-dark {
            background: #23272b !important;
            color: #fff;
            padding: 18px 0 10px 0;
            font-size: 1rem;
            border-top: 2px solid #007bff;
        }
        footer p {
            margin-bottom: 0;
            letter-spacing: 0.5px;
        }
        .cta-section {
            background: #007bff;
            color: #fff;
            padding: 40px 0 30px 0;
            text-align: center;
        }
        .cta-section .btn {
            margin: 0 10px;
            font-size: 1.1rem;
            padding: 10px 28px;
            border-radius: 30px;
            font-weight: 600;
        }
        .testimonials-section {
            background: #f8fafc;
            padding: 50px 0 40px 0;
        }
        .testimonial {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 2rem 1.5rem 1.5rem 1.5rem;
            margin: 0 10px;
            text-align: center;
        }
        .testimonial .fa-quote-left {
            color: #007bff;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .testimonial .testimonial-author {
            font-weight: 700;
            color: #007bff;
            margin-top: 1rem;
        }
        .how-it-works-section {
            background: #fff;
            padding: 50px 0 30px 0;
        }
        .how-step {
            text-align: center;
            padding: 20px 10px;
        }
        .how-step .step-icon {
            font-size: 2.5rem;
            color: #007bff;
            margin-bottom: 1rem;
        }
        .how-step-title {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .how-step-desc {
            color: #555;
        }
        /* WhatsApp floating button */
        .whatsapp-float {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 999;
        }
        .whatsapp-float a {
            display: flex;
            align-items: center;
            background: #25d366;
            color: #fff;
            border-radius: 50px;
            padding: 12px 18px;
            font-size: 1.3rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.13);
            text-decoration: none;
            transition: background 0.2s;
        }
        .whatsapp-float a:hover {
            background: #128c7e;
        }
        .whatsapp-float .fa-whatsapp {
            font-size: 1.7rem;
            margin-right: 8px;
        }
        /* Animated wave SVG */
        .hero-wave {
            position: relative;
            left: 0; right: 0; bottom: -1px;
            width: 100%;
            z-index: 3;
            margin-top: -30px;
        }
        @media (max-width: 767px) {
            .hero-wave { margin-top: -10px; }
        }
        /* Loading overlay styles */
        #loading-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100vw; height: 100vh;
            background: #fff;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s;
        }
        #loading-overlay .spinner {
            border: 8px solid #f3f3f3;
            border-top: 8px solid #007bff;
            border-radius: 50%;
            width: 70px;
            height: 70px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        body.loading {
            overflow: hidden;
        }
        .language-switcher {
            position: absolute;
            top: 24px;
            right: 32px;
            z-index: 20;
        }
        .language-switcher select {
            border-radius: 20px;
            padding: 4px 16px;
            font-size: 1rem;
            border: 1px solid #007bff;
            color: #007bff;
            background: #fff;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Language Switcher -->
    <div class="language-switcher">
        <select id="langSelect">
            <option value="en">English</option>
            <option value="sw">Swahili</option>
        </select>
    </div>
    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 mb-4" data-t-en="Welcome to Milk Cooperative System" data-t-sw="Karibu kwenye Mfumo wa Ushirika wa Maziwa">Welcome to <span style="color:#007bff;">Milk Cooperative System</span></h1>
            <p class="lead mb-5" data-t-en="Empowering dairy farmers, cooperatives, and industries with a modern, transparent, and efficient platform for milk collection, payments, and analytics." data-t-sw="Kuwawezesha wakulima wa maziwa, vyama vya ushirika, na viwanda kwa jukwaa la kisasa, wazi, na bora la ukusanyaji wa maziwa, malipo, na uchambuzi.">Empowering <b>dairy farmers</b>, <b>cooperatives</b>, and <b>industries</b> with a modern, transparent, and efficient platform for milk collection, payments, and analytics.</p>
            
            <div class="row login-options justify-content-center">
                <div class="col-md-4 mb-4">
                    <div class="card login-card" onclick="window.location.href='modules/auth/login.php?type=farmer'">
                        <div class="card-body text-center">
                            <i class="fas fa-user fa-3x text-primary mb-3"></i>
                            <h3 data-t-en="Farmer Login" data-t-sw="Ingia Mkulima">Farmer Login</h3>
                            <p class="text-muted" data-t-en="Access your farmer dashboard" data-t-sw="Fikia dashibodi yako ya mkulima">Access your farmer dashboard</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card login-card" onclick="window.location.href='modules/auth/login.php?type=industry'">
                        <div class="card-body text-center">
                            <i class="fas fa-industry fa-3x text-success mb-3"></i>
                            <h3 data-t-en="Industry Login" data-t-sw="Ingia Kiwanda">Industry Login</h3>
                            <p class="text-muted" data-t-en="Access your industry dashboard" data-t-sw="Fikia dashibodi yako ya kiwanda">Access your industry dashboard</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card login-card" onclick="window.location.href='modules/auth/login.php?type=admin'">
                        <div class="card-body text-center">
                            <i class="fas fa-user-shield fa-3x text-danger mb-3"></i>
                            <h3 data-t-en="Admin Login" data-t-sw="Ingia Admin">Admin Login</h3>
                            <p class="text-muted" data-t-en="Access cooperative station dashboard" data-t-sw="Fikia dashibodi ya ushirika">Access cooperative station dashboard</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="text-center mb-5" style="font-weight:800;letter-spacing:1px;" data-t-en="Key Features" data-t-sw="Vipengele Muhimu">Key Features</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-truck fa-3x"></i>
                            <h4 data-t-en="Milk Collection" data-t-sw="Ukusanyaji wa Maziwa">Milk Collection</h4>
                            <p data-t-en="Efficient milk collection and quality testing system for all stakeholders." data-t-sw="Ukusanyaji wa maziwa kwa ufanisi na mfumo wa kupima ubora kwa wadau wote.">Efficient milk collection and quality testing system for all stakeholders.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-3x"></i>
                            <h4 data-t-en="Analytics" data-t-sw="Uchambuzi">Analytics</h4>
                            <p data-t-en="Real-time analytics and reporting tools for better decision making." data-t-sw="Zana za uchambuzi na taarifa papo hapo kwa maamuzi bora.">Real-time analytics and reporting tools for better decision making.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-money-bill-wave fa-3x"></i>
                            <h4 data-t-en="Payments" data-t-sw="Malipo">Payments</h4>
                            <p data-t-en="Secure and transparent payment system for all transactions." data-t-sw="Mfumo salama na wazi wa malipo kwa miamala yote.">Secure and transparent payment system for all transactions.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Animated Wave SVG -->
    <div class="hero-wave">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#f8fafc" fill-opacity="1" d="M0,64L48,74.7C96,85,192,107,288,117.3C384,128,480,128,576,117.3C672,107,768,85,864,85.3C960,85,1056,107,1152,117.3C1248,128,1344,128,1392,128L1440,128L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>
    </div>

    <!-- CTA Section -->
    <section class="cta-section">
        <h2 class="mb-3" style="font-weight:700;" data-t-en="Ready to get started?" data-t-sw="Tayari kuanza?">Ready to get started?</h2>
        <p class="mb-4" data-t-en="Register now or contact our support team for more information." data-t-sw="Jisajili sasa au wasiliana na timu yetu ya msaada kwa maelezo zaidi.">Register now or contact our support team for more information.</p>
        <a href="modules/auth/login.php?type=farmer" class="btn btn-light text-primary" data-t-en="Register as Farmer" data-t-sw="Jisajili kama Mkulima"><i class="fas fa-user-plus"></i> Register as Farmer</a>
        <a href="modules/auth/login.php?type=industry" class="btn btn-light text-success" data-t-en="Register as Industry" data-t-sw="Jisajili kama Kiwanda"><i class="fas fa-industry"></i> Register as Industry</a>
        <a href="mailto:support@milkcoop.com" class="btn btn-outline-light" data-t-en="Contact Support" data-t-sw="Wasiliana na Msaada"><i class="fas fa-envelope"></i> Contact Support</a>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works-section">
        <div class="container">
            <h2 class="text-center mb-5" style="font-weight:800;letter-spacing:1px;" data-t-en="How It Works" data-t-sw="Jinsi Inavyofanya Kazi">How It Works</h2>
            <div class="row justify-content-center">
                <div class="col-md-4 how-step">
                    <div class="step-icon"><i class="fas fa-user-plus"></i></div>
                    <div class="how-step-title" data-t-en="1. Register" data-t-sw="1. Jisajili">1. Register</div>
                    <div class="how-step-desc" data-t-en="Sign up as a farmer or industry to join the cooperative platform." data-t-sw="Jisajili kama mkulima au kiwanda kujiunga na jukwaa la ushirika.">Sign up as a farmer or industry to join the cooperative platform.</div>
                </div>
                <div class="col-md-4 how-step">
                    <div class="step-icon"><i class="fas fa-truck"></i></div>
                    <div class="how-step-title" data-t-en="2. Deliver Milk" data-t-sw="2. Peleka Maziwa">2. Deliver Milk</div>
                    <div class="how-step-desc" data-t-en="Submit your milk deliveries and track your history online." data-t-sw="Wasilisha maziwa yako na fuatilia historia yako mtandaoni.">Submit your milk deliveries and track your history online.</div>
                </div>
                <div class="col-md-4 how-step">
                    <div class="step-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="how-step-title" data-t-en="3. Get Paid" data-t-sw="3. Pokea Malipo">3. Get Paid</div>
                    <div class="how-step-desc" data-t-en="Receive secure, transparent payments directly to your account." data-t-sw="Pokea malipo salama na wazi moja kwa moja kwenye akaunti yako.">Receive secure, transparent payments directly to your account.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <h2 class="text-center mb-5" style="font-weight:800;letter-spacing:1px;" data-t-en="What Our Users Say" data-t-sw="Wanasema Nini?">What Our Users Say</h2>
            <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="testimonial mx-auto" style="max-width:500px;">
                            <i class="fas fa-quote-left"></i>
                            <p data-t-en="The Milk Cooperative System has made it so easy to track my deliveries and get paid on time. Highly recommended!" data-t-sw="Mfumo wa Ushirika wa Maziwa umenisaidia kufuatilia maziwa yangu na kupata malipo kwa wakati. Napendekeza sana!">"The Milk Cooperative System has made it so easy to track my deliveries and get paid on time. Highly recommended!"</p>
                            <div class="testimonial-author" data-t-en="— John M., Farmer" data-t-sw="— John M., Mkulima">— John M., Farmer</div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="testimonial mx-auto" style="max-width:500px;">
                            <i class="fas fa-quote-left"></i>
                            <p data-t-en="As an industry partner, I appreciate the transparency and efficiency. The analytics tools are a game changer." data-t-sw="Kama mshirika wa kiwanda, nathamini uwazi na ufanisi. Zana za uchambuzi ni bora sana.">"As an industry partner, I appreciate the transparency and efficiency. The analytics tools are a game changer."</p>
                            <div class="testimonial-author" data-t-en="— Sarah K., Industry" data-t-sw="— Sarah K., Kiwanda">— Sarah K., Industry</div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="testimonial mx-auto" style="max-width:500px;">
                            <i class="fas fa-quote-left"></i>
                            <p data-t-en="Support is always responsive and helpful. The platform is user-friendly and secure." data-t-sw="Msaada ni wa haraka na wenye msaada. Jukwaa ni rahisi kutumia na salama.">"Support is always responsive and helpful. The platform is user-friendly and secure."</p>
                            <div class="testimonial-author" data-t-en="— Peter D., Farmer" data-t-sw="— Peter D., Mkulima">— Peter D., Farmer</div>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </section>

    <!-- WhatsApp Floating Button -->
    <div class="whatsapp-float">
        <a href="https://wa.me/25562030877" target="_blank" title="Chat with Support">
            <i class="fab fa-whatsapp"></i> Chat Support
        </a>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white">
        <div class="container text-center">
            <p data-t-en="All rights reserved." data-t-sw="Haki zote zimehifadhiwa.">&copy; <?php echo date('Y'); ?> <span style="color:#007bff;font-weight:600;">Milk Cooperative System</span>. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <div id="loading-overlay">
        <div class="spinner"></div>
    </div>
    <script>
        // Show loading overlay for 2 seconds, then fade out
        document.body.classList.add('loading');
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('loading-overlay').style.opacity = 0;
                setTimeout(function() {
                    document.getElementById('loading-overlay').style.display = 'none';
                    document.body.classList.remove('loading');
                }, 500);
            }, 2000);
        });

        // Language switcher logic
        document.getElementById('langSelect').addEventListener('change', function() {
            var lang = this.value;
            document.querySelectorAll('[data-t-en]').forEach(function(el) {
                el.innerHTML = el.getAttribute('data-t-' + lang);
            });
        });
    </script>
</body>
</html> 