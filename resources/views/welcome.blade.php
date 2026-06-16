<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ULC System</title>

    <!-- Fonts -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary: #FF5F00;
            --primary-glow: rgba(255, 95, 0, 0.4);
            --black: #000000;
            --dark: #0a0a0a;
            --card-bg: #121212;
            --text-light: #ffffff;
            --text-muted: #a0a0a0;
            --border: rgba(255, 255, 255, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--black);
            color: var(--text-light);
            overflow-x: hidden;
            line-height: 1.5;
        }

        /* --- Background Decoration --- */
        .bg-glow {
            position: fixed;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
            border-radius: 50%;
            z-index: -1;
            filter: blur(80px);
            opacity: 0.3;
        }

        .glow-1 {
            top: -200px;
            right: -200px;
        }

        .glow-2 {
            bottom: -200px;
            left: -200px;
        }

        /* --- Custom Scrollbar --- */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--black);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
        }

        /* --- Header --- */
        header {
            padding: 2rem 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: 0.4s;
            background: transparent;
        }

        header.scrolled {
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(15px);
            padding: 1.2rem 8%;
            border-bottom: 1px solid var(--border);
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -1px;
            text-decoration: none;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo span {
            color: var(--primary);
        }

        .nav-menu {
            display: flex;
            gap: 2.5rem;
            align-items: center;
        }

        .nav-menu a {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
            font-size: 0.95rem;
        }

        .nav-menu a:hover {
            color: var(--primary);
        }

        .btn-cta {
            background: var(--primary);
            color: white !important;
            padding: 0.75rem 1.8rem;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
        }

        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px var(--primary-glow);
            filter: brightness(1.1);
        }

        /* --- Hero Section --- */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 8%;
            text-align: center;
            position: relative;
            padding-top: 100px;
        }

        .hero-content {
            max-width: 900px;
        }

        .hero h1 {
            font-size: 5rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 2rem;
            letter-spacing: -2px;
        }

        .hero h1 span {
            background: linear-gradient(to right, var(--primary), #ffa570);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--text-muted);
            margin-bottom: 3rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-btns {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
        }

        .btn-outline {
            border: 1px solid var(--border);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--text-muted);
        }

        /* --- Dashboard Preview --- */
        .preview-container {
            margin-top: -15vh;
            padding: 0 8%;
            perspective: 1000px;
        }

        .preview-img {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: 0 50px 100px rgba(0, 0, 0, 0.8);
            transform: rotateX(10deg);
            transition: 0.5s;
            display: block;
        }

        .preview-container:hover .preview-img {
            transform: rotateX(0deg) translateY(-20px);
        }

        /* --- Features Section --- */
        .features {
            padding: 100px 8%;
        }

        .section-header {
            text-align: center;
            margin-bottom: 80px;
        }

        .section-header h2 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .section-header p {
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: var(--card-bg);
            padding: 3rem;
            border-radius: 24px;
            border: 1px solid var(--border);
            transition: 0.4s;
            text-align: left;
        }

        .feature-card:hover {
            border-color: var(--primary);
            transform: translateY(-10px);
            background: #161616;
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 95, 0, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            font-size: 1.5rem;
            margin-bottom: 2rem;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.7;
        }

        /* --- CTA Section --- */
        .cta {
            padding: 120px 8%;
            text-align: center;
        }

        .cta-inner {
            background: linear-gradient(135deg, var(--dark) 0%, #1a1a1a 100%);
            padding: 80px 40px;
            border-radius: 40px;
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }

        .cta-inner h2 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
        }

        /* --- Footer --- */
        footer {
            padding: 60px 8%;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .footer-links {
            display: flex;
            gap: 2rem;
        }

        .footer-links a {
            color: var(--text-muted);
            text-decoration: none;
            transition: 0.3s;
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        /* --- Responsive --- */
        @media (max-width: 992px) {
            .hero h1 {
                font-size: 3.5rem;
            }

            header {
                padding: 1.5rem 5%;
            }
        }

        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }

            .hero h1 {
                font-size: 2.8rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .section-header h2 {
                font-size: 2.2rem;
            }

            .cta-inner h2 {
                font-size: 2.2rem;
            }

            .hero-btns {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="bg-glow glow-1"></div>
    <div class="bg-glow glow-2"></div>

    <!-- Header -->
    <header id="header">
        <a href="/" class="logo">
            <i class="fas fa-bolt"></i> ULC<span>SYSTEM</span>
        </a>
        <div class="nav-menu">
            @if (Route::has('auth.login.page'))
                @auth
                    <a href="{{ url('/home') }}" class="btn-cta">Go to Dashboard</a>
                @else
                    <a href="{{ route('auth.login.page') }}" class="btn-cta">Sign In</a>
                @endauth
            @endif
        </div>
    </header>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-content" data-aos="fade-up">
            <h1>The New Era of <span>Lending</span> Management.</h1>
            <p>Empower your financial operations with ULC System. Sleek, fast, and secure platform designed for modern
                lending businesses.</p>
            <div class="hero-btns">
                <a href="{{ route('auth.login.page') }}" class="btn-cta">Get Started Free</a>
                <a href="#features" class="btn-outline">Explore Features</a>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features" id="features">
        <div class="section-header" data-aos="fade-up">
            <h2>Superior Features</h2>
            <p>Engineered for efficiency, built for scale.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-icon"><i class="fas fa-user-shield"></i></div>
                <h3>Advanced Security</h3>
                <p>Enterprise-grade encryption protecting your client data and transaction history around the clock.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-icon"><i class="fas fa-rocket"></i></div>
                <h3>Automated Workflow</h3>
                <p>Smart loan term tracking and automatic 100-day calculations to eliminate manual errors.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-icon"><i class="fas fa-chart-pie"></i></div>
                <h3>Insights & Data</h3>
                <p>Gain deep visibility into your collection performance with real-time interactive dashboards.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-icon"><i class="fas fa-mobile-screen"></i></div>
                <h3>Mobile Mastery</h3>
                <p>A fully responsive interface that looks stunning on your phone, tablet, or desktop.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
                <div class="feature-icon"><i class="fas fa-sync"></i></div>
                <h3>Instant Sync</h3>
                <p>Your team stays updated in real-time. No more double entries or outdated client info.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="600">
                <div class="feature-icon"><i class="fas fa-headphones"></i></div>
                <h3>24/7 Reliability</h3>
                <p>High-performance infrastructure ensuring your system is always online when you need it.</p>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta" data-aos="fade-up">
        <div class="cta-inner">
            <h2>Ready to Scale?</h2>
            <p>Join the future of lending management today.</p>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; {{ date('Y') }} ULC System. Developed for Excellence.</p>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 200
        });

        // Header Scroll Effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    </script>
</body>

</html>
