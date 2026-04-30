<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Trainly | Gym App – Transform Your Body</title>
    <!-- Google Fonts + simple CSS reset -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <!-- Font Awesome 6 (free icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #07130b;
            color: #f0f3f8;
            line-height: 1.4;
            scroll-behavior: smooth;
        }

        /* custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #112017;
        }

        ::-webkit-scrollbar-thumb {
            background: #fbbf24;
            border-radius: 10px;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 32px;
        }

        /* buttons & utilities */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(95deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            font-weight: 700;
            padding: 14px 32px;
            border-radius: 60px;
            text-decoration: none;
            font-size: 1rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(251, 191, 36, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            background: linear-gradient(95deg, #facc15 0%, #eab308 100%);
            box-shadow: 0 14px 26px rgba(251, 191, 36, 0.4);
        }

        .btn-primary.attention {
            box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.45), 0 14px 26px rgba(251, 191, 36, 0.55);
            transform: translateY(-2px);
        }

        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            border: 1.5px solid rgba(251, 191, 36, 0.7);
            color: #fbbf24;
            font-weight: 600;
            padding: 12px 28px;
            border-radius: 60px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-outline:hover {
            background: rgba(251, 191, 36, 0.12);
            border-color: #fbbf24;
            color: #fde68a;
        }

        /* HEADER / NAV */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 24px 0;
            flex-wrap: wrap;
            gap: 20px;
            background: rgba(7, 19, 11, 0.92);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(251, 191, 36, 0.12);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .logo i {
            color: #fbbf24;
            font-size: 2rem;
        }

        .nav-links {
            display: flex;
            gap: 32px;
            list-style: none;
            font-weight: 500;
        }

        .nav-links a {
            color: #eef2ff;
            text-decoration: none;
            transition: 0.2s;
            font-size: 1rem;
        }

        .nav-links a:hover {
            color: #fbbf24;
        }

        .nav-buttons {
            display: flex;
            gap: 16px;
        }

        .login-btn {
            background: transparent;
            border: 1px solid #2c2f36;
            color: white;
            padding: 10px 24px;
            border-radius: 40px;
            font-weight: 600;
            transition: 0.2s;
            text-decoration: none;
        }

        .login-btn:hover {
            border-color: #fbbf24;
            color: #fbbf24;
        }

        /* Hero Section */
        .hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 48px;
            padding: 40px 0 60px;
        }

        .hero-content {
            flex: 1.2;
        }

        .hero-badge {
            background: rgba(251, 191, 36, 0.18);
            display: inline-block;
            padding: 6px 14px;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #fde68a;
            margin-bottom: 24px;
            backdrop-filter: blur(2px);
        }

        .hero-content h1 {
            font-size: 3.6rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 24px;
            letter-spacing: -1px;
        }

        .hero-gradient-text {
            background: linear-gradient(120deg, #fbbf24, #f59e0b);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-content p {
            font-size: 1.2rem;
            color: #b9c2d4;
            max-width: 550px;
            margin-bottom: 36px;
            line-height: 1.5;
        }

        .hero-stats {
            display: flex;
            gap: 32px;
            margin-top: 32px;
        }

        .stat-item h3 {
            font-size: 1.8rem;
            font-weight: 800;
        }

        .stat-item p {
            font-size: 0.85rem;
            color: #a1abbd;
            margin: 0;
        }

        .hero-image {
            flex: 1;
            position: relative;
            display: flex;
            justify-content: center;
        }

        .hero-image img {
            max-width: 100%;
            border-radius: 32px;
            filter: drop-shadow(0 20px 30px rgba(0, 0, 0, 0.5));
        }

        .floating-card {
            position: absolute;
            bottom: 20px;
            left: -20px;
            background: #122017;
            backdrop-filter: blur(12px);
            padding: 12px 20px;
            border-radius: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .floating-card i {
            font-size: 1.8rem;
            color: #fbbf24;
        }

        /* Features Section */
        .section {
            padding: 80px 0;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .section-sub {
            text-align: center;
            color: #a5b0c2;
            max-width: 650px;
            margin: 0 auto 56px;
            font-size: 1.1rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 32px;
        }

        .feature-card {
            background: #0f1b13;
            padding: 32px 24px;
            border-radius: 32px;
            transition: all 0.25s ease;
            border: 1px solid #1f232c;
            text-align: center;
        }

        .feature-card:hover {
            transform: translateY(-6px);
            border-color: #fbbf2430;
            background: #132117;
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(145deg, #fbbf2420, #fde68a10);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 24px;
            margin: 0 auto 24px;
            font-size: 2.2rem;
            color: #fbbf24;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 12px;
        }

        .feature-card p {
            color: #9aa4b8;
            line-height: 1.5;
        }

        /* workout plans preview */
        .workout-showcase {
            background: linear-gradient(135deg, #0b1a10 0%, #08140d 100%);
            border-radius: 56px;
            padding: 56px 48px;
            margin: 40px 0;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 40px;
            border: 1px solid #242830;
        }

        .workout-text {
            flex: 1;
        }

        .workout-text h2 {
            font-size: 2rem;
            margin-bottom: 18px;
        }

        .workout-text ul {
            list-style: none;
            margin: 20px 0;
        }

        .workout-text li {
            margin: 14px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .workout-text li i {
            color: #fbbf24;
            font-size: 1.3rem;
        }

        .workout-img {
            flex: 1;
            text-align: center;
        }

        .workout-img img {
            width: 100%;
            max-width: 450px;
            border-radius: 28px;
            box-shadow: 0 18px 28px rgba(0, 0, 0, 0.5);
        }

        /* Testimonials */
        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 28px;
            margin-top: 20px;
        }

        .testimonial-card {
            background: #0f1b13;
            padding: 28px;
            border-radius: 28px;
            border: 1px solid #20232b;
        }

        .stars {
            color: #fbbf24;
            margin-bottom: 16px;
            letter-spacing: 2px;
        }

        .testimonial-card p {
            font-style: italic;
            line-height: 1.5;
            color: #cfd9ed;
        }

        .user {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 20px;
        }

        .user img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            background: #2c2f36;
        }

        .user-info h4 {
            font-size: 1rem;
        }

        .user-info span {
            font-size: 0.8rem;
            color: #8d97ab;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(125deg, #fbbf24, #d97706);
            border-radius: 48px;
            padding: 64px 48px;
            text-align: center;
            margin: 60px 0 40px;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 18px;
        }

        .cta-section p {
            max-width: 600px;
            margin: 0 auto 32px;
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .btn-dark {
            background: #07130b;
            color: white;
            box-shadow: none;
        }

        .btn-dark:hover {
            background: #122217;
            transform: translateY(-2px);
        }

        /* Footer */
        footer {
            padding: 48px 0 32px;
            border-top: 1px solid #1e2128;
            margin-top: 20px;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 32px;
        }

        .footer-col p {
            color: #8f99ae;
            margin-top: 16px;
            max-width: 260px;
        }

        .footer-col h4 {
            margin-bottom: 18px;
            font-weight: 600;
        }

        .footer-col ul {
            list-style: none;
        }

        .footer-col li {
            margin: 10px 0;
        }

        .footer-col a {
            color: #b7c0d0;
            text-decoration: none;
            transition: 0.2s;
        }

        .footer-col a:hover {
            color: #fbbf24;
        }

        .social-icons {
            display: flex;
            gap: 18px;
            font-size: 1.4rem;
            margin-top: 12px;
        }

        .copyright {
            text-align: center;
            margin-top: 48px;
            font-size: 0.8rem;
            color: #6c7486;
        }

        .login-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(7, 19, 11, 0.45);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 20px;
        }

        .login-modal-overlay.active {
            display: flex;
        }

        .signup-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(7, 19, 11, 0.45);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 20px;
        }

        .signup-modal-overlay.active {
            display: flex;
        }

        .business-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(7, 19, 11, 0.45);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 20px;
        }

        .business-modal-overlay.active {
            display: flex;
        }

        .login-modal {
            width: 100%;
            max-width: 420px;
            background: #0f1b13;
            border: 1px solid rgba(251, 191, 36, 0.25);
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.45);
        }

        .login-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .login-modal h3 {
            font-size: 1.5rem;
            color: #f0f3f8;
        }

        .login-close {
            background: transparent;
            border: 0;
            color: #fbbf24;
            font-size: 1.3rem;
            cursor: pointer;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .login-form label {
            font-size: 0.9rem;
            color: #cfd9ed;
            margin-bottom: 6px;
            display: block;
        }

        .login-form input {
            width: 100%;
            background: #07130b;
            border: 1px solid #1f2b22;
            color: #f0f3f8;
            border-radius: 12px;
            padding: 12px 14px;
            outline: none;
        }

        .login-form input:focus {
            border-color: #fbbf24;
            box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.2);
        }

        .show-password-row {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #cfd9ed;
            font-size: 0.9rem;
            margin-top: 2px;
        }

        .show-password-row input {
            width: auto;
            accent-color: #fbbf24;
        }

        .signup-link {
            margin-top: 14px;
            text-align: center;
            color: #9aa4b8;
            font-size: 0.92rem;
        }

        .signup-link a {
            color: #fbbf24;
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link a:hover {
            color: #fde68a;
        }

        .notice-toast {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 2600;
            background: rgba(15, 27, 19, 0.96);
            color: #f0f3f8;
            border: 1px solid rgba(251, 191, 36, 0.45);
            border-radius: 14px;
            padding: 12px 14px;
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.35);
            min-width: 240px;
            max-width: 320px;
            display: flex;
            align-items: center;
            gap: 10px;
            opacity: 0;
            transform: translateY(-6px);
            pointer-events: none;
            transition: opacity 0.22s ease, transform 0.22s ease;
        }

        .notice-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .notice-toast i {
            color: #fbbf24;
            font-size: 1rem;
            flex-shrink: 0;
        }


        @media (max-width: 800px) {
            .container {
                padding: 0 24px;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .navbar {
                flex-direction: column;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }

            .workout-showcase {
                padding: 32px;
            }
        }
    </style>
</head>

<body id="top" data-open-login="{{ session('open_login') || $errors->login->any() ? '1' : '0' }}" data-open-signup="{{ session('open_signup') || $errors->signup->any() ? '1' : '0' }}" data-open-business="{{ session('open_business') || $errors->business->any() ? '1' : '0' }}">

    <div class="container">
        <!-- Navigation -->
        <nav class="navbar">
            <div class="logo">
                <i class="fas fa-dumbbell"></i>
                <span>TRAIN<span style="color:#fbbf24;">LY</span></span>
            </div>
            <ul class="nav-links">
                <li><a href="#top">Home</a></li>
                <li><a href="#business-plan">Manage Plan</a></li>
                <li><a href="#about-us">About Us</a></li>
            </ul>
            <div class="nav-buttons">
                <a href="#" id="openLoginModal" class="btn-primary" style="padding: 10px 24px;">Login <i class="fas fa-arrow-right"></i></a>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="hero">
            <div class="hero-content">
                <h1>
                    Train Smarter, <br>
                    <span class="hero-gradient-text">Get Stronger</span> Every Day
                </h1>
                <p>
                    Cloud-based app booking and membership system for real-time scheduling and management.
                </p>
                <div style="display: flex; gap: 18px; flex-wrap: wrap;">
                    <a href="#" id="openNearestGymLogin" class="btn-primary"><i class="fas fa-location-dot"></i> View Nearest Gym</a>
                    <a href="#business-plan" id="scrollToBusinessBtn" class="btn-primary"><i class="fas fa-briefcase"></i> Add your business here</a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <h3>100,000+</h3>
                        <p>Active members</p>
                    </div>
                    <div class="stat-item">
                        <h3>1,200+</h3>
                        <p>Expert workouts</p>
                    </div>
                    <div class="stat-item">
                        <h3>4.9★</h3>
                        <p>App store rating</p>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://t4.ftcdn.net/jpg/08/91/58/59/240_F_891585995_P2Wt8a8p7owQtJp5w9CJgridoeB7WJwR.jpg" alt="athlete training" style="border-radius: 32px; width: 100%; object-fit: cover;">
                <div class="floating-card">
                    <i class="fas fa-chart-line"></i>
                    <div><strong>+38% strength gain</strong><br>in 8 weeks avg</div>
                </div>
            </div>
        </div>

        <!-- Features section -->
        <div class="section">
            <h2 class="section-title">Everything you need to <span style="color:#fbbf24;">dominate</span></h2>
            <p class="section-sub">Smart tools, expert guidance, and a supportive community — all in one web app.</p>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
                    <h3>Realtime Booking</h3>
                    <p>Book trainers, classes, and gym slots instantly with live availability and fast confirmation.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-map-location-dot"></i></div>
                    <h3>Map Tracking</h3>
                    <p>Users can see the nearest gym locations based on their current location and check available gym slots.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-id-card"></i></div>
                    <h3>Subscription</h3>
                    <p>Get coach subscriptions at the gym and enjoy early access to gym facilities and classes.</p>
                </div>
            </div>
        </div>

        <div id="business-plan" class="section" style="padding-top: 20px; padding-bottom: 10px;">
            <h2 class="section-title"><span style="color:#fbbf24;">Business Plan</span></h2>
            <p class="section-sub" style="margin-bottom: 20px;">Grow your gym business with tools for members, scheduling, and location visibility.</p>
        </div>

        <!-- Workout preview / showcase -->
        <div id="workout-showcase" class="workout-showcase">
            <div class="workout-text">
                <h2>🔥 <span style="color:#fbbf24;">14-day free trial </span> Business Plan</h2>
                <p>Build your business here. This are the following benefits.</p>
                <ul>
                    <li><i class="fas fa-check-circle"></i> Monitoring and tracking members.</li>
                    <li><i class="fas fa-check-circle"></i> Able to place your Gym Location.</li>
                    <li><i class="fas fa-check-circle"></i> Overall access.</li>
                </ul>
                <a href="#" id="addBusinessActionBtn" class="btn-primary" style="margin-top: 12px;"><i class="fas fa-briefcase"></i> Add your business</a>
            </div>
            <div class="workout-img">
                <i class="fas fa-crown" style="font-size: 120px; color: #fbbf24; border: 3px solid #fbbf24; width: 365px; height: 265px; border-radius: 28px; display: inline-flex; align-items: center; justify-content: center; margin-left: 45px; box-shadow: 0 0 0 1px rgba(251,191,36,0.35) inset, 0 0 22px rgba(251,191,36,0.45), 0 12px 24px rgba(0,0,0,0.35);"></i>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="cta-section">
            <h2>Ready to crush your goals? 💪</h2>
            <p>Join Trainly today and get 14 days free. Cancel anytime. Start your strongest chapter.</p>
            <div class="cta-buttons">
                <a href="#" id="tryWebAppBtn" class="btn-outline" style="background: rgba(0,0,0,0.3); border-color:white; color:white;">Try Web App →</a>
            </div>
        </div>

        <!-- Footer -->
        <footer id="about-us">
            <div class="footer-content">
                <div class="footer-col">
                    <div class="logo" style="margin-bottom: 10px;">
                        <i class="fas fa-dumbbell"></i>
                        <span>TRAINLY</span>
                    </div>
                    <p>Unlock your strongest self with science-backed training, nutrition & community.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Product</h4>
                    <ul>
                        <li><a href="#">Workouts</a></li>
                        <li><a href="#">Nutrition</a></li>
                        <li><a href="#">Pricing</a></li>
                        <li><a href="#">Challenges</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="#">About us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Press</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Community Rules</a></li>
                        <li><a href="#">Privacy & Terms</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                © 2025 Trainly Fitness Inc. — Sweat. Grow. Conquer.
            </div>
        </footer>
    </div>

    <div class="login-modal-overlay" id="loginModalOverlay">
        <div class="login-modal" id="loginModal" role="dialog" aria-modal="true" aria-labelledby="loginModalTitle">
            <div class="login-modal-header">
                <h3 id="loginModalTitle">Login</h3>
                <button type="button" class="login-close" id="closeLoginModal" aria-label="Close login modal">&times;</button>
            </div>
            <form class="login-form" action="{{ route('auth.login') }}" method="post">
                @csrf
                @if(session('status'))
                    <p style="color:#fde68a; font-size:0.9rem; margin-bottom: 2px;">{{ session('status') }}</p>
                @endif
                <div>
                    <label for="loginEmail">Email</label>
                    <input id="loginEmail" name="email" type="email" placeholder="Enter your email" value="{{ old('email') }}" required>
                    @if($errors->login->has('email'))
                        <p style="color:#fca5a5; font-size:0.82rem; margin-top:6px;">{{ $errors->login->first('email') }}</p>
                    @endif
                </div>
                <div>
                    <label for="loginPassword">Password</label>
                    <input id="loginPassword" name="password" type="password" placeholder="Enter your password" required>
                    @if($errors->login->has('password'))
                        <p style="color:#fca5a5; font-size:0.82rem; margin-top:6px;">{{ $errors->login->first('password') }}</p>
                    @endif
                </div>
                <label class="show-password-row" for="showPassword">
                    <input id="showPassword" type="checkbox">
                    Show password
                </label>
                <button type="submit" class="btn-primary" style="justify-content: center; width: 100%; margin-top: 6px;">Login</button>
            </form>
            <p class="signup-link">Don’t have an account? <a href="#" id="switchToSignup">Sign up</a></p>
        </div>
    </div>

    <div class="signup-modal-overlay" id="signupModalOverlay">
        <div class="login-modal" role="dialog" aria-modal="true" aria-labelledby="signupModalTitle">
            <div class="login-modal-header">
                <h3 id="signupModalTitle">Sign up</h3>
                <button type="button" class="login-close" id="closeSignupModal" aria-label="Close signup modal">&times;</button>
            </div>
            <form class="login-form" action="{{ route('auth.signup') }}" method="post">
                @csrf
                <div>
                    <label for="signupName">Full name</label>
                    <input id="signupName" name="name" type="text" placeholder="Enter your full name" value="{{ old('name') }}" required>
                    @if($errors->signup->has('name'))
                        <p style="color:#fca5a5; font-size:0.82rem; margin-top:6px;">{{ $errors->signup->first('name') }}</p>
                    @endif
                </div>
                <div>
                    <label for="signupEmail">Email</label>
                    <input id="signupEmail" name="email" type="email" placeholder="Enter your email" value="{{ old('email') }}" required>
                    @if($errors->signup->has('email'))
                        <p style="color:#fca5a5; font-size:0.82rem; margin-top:6px;">{{ $errors->signup->first('email') }}</p>
                    @endif
                </div>
                <div>
                    <label for="signupPassword">Password</label>
                    <input id="signupPassword" name="password" type="password" placeholder="Create your password" required>
                    @if($errors->signup->has('password'))
                        <p style="color:#fca5a5; font-size:0.82rem; margin-top:6px;">{{ $errors->signup->first('password') }}</p>
                    @endif
                </div>
                <div>
                    <label for="signupPasswordConfirm">Confirm password</label>
                    <input id="signupPasswordConfirm" name="password_confirmation" type="password" placeholder="Confirm your password" required>
                </div>
                <label class="show-password-row" for="showSignupPassword">
                    <input id="showSignupPassword" type="checkbox">
                    Show password
                </label>
                <button type="submit" class="btn-primary" style="justify-content: center; width: 100%; margin-top: 6px;">Create account</button>
            </form>
            <p class="signup-link">Already have an account? <a href="#" id="switchToLogin">Login</a></p>
        </div>
    </div>

    <div class="business-modal-overlay" id="businessModalOverlay">
        <div class="login-modal" role="dialog" aria-modal="true" aria-labelledby="businessModalTitle">
            <div class="login-modal-header">
                <h3 id="businessModalTitle">Add Your Business</h3>
                <button type="button" class="login-close" id="closeBusinessModal" aria-label="Close business modal">&times;</button>
            </div>
            <form class="login-form" action="{{ route('auth.business.create') }}" method="post">
                @csrf
                <div>
                    <label for="businessName">Business Name</label>
                    <input id="businessName" name="business_name" type="text" placeholder="Enter your business name" value="{{ old('business_name') }}" required>
                    @if($errors->business->has('business_name'))
                        <p style="color:#fca5a5; font-size:0.82rem; margin-top:6px;">{{ $errors->business->first('business_name') }}</p>
                    @endif
                </div>
                <div>
                    <label for="businessFullName">Full Name</label>
                    <input id="businessFullName" name="full_name" type="text" placeholder="Enter your full name" value="{{ old('full_name') }}" required>
                    @if($errors->business->has('full_name'))
                        <p style="color:#fca5a5; font-size:0.82rem; margin-top:6px;">{{ $errors->business->first('full_name') }}</p>
                    @endif
                </div>
                <div>
                    <label for="businessEmail">Email</label>
                    <input id="businessEmail" name="email" type="email" placeholder="Enter your email" value="{{ old('email') }}" required>
                    @if($errors->business->has('email'))
                        <p style="color:#fca5a5; font-size:0.82rem; margin-top:6px;">{{ $errors->business->first('email') }}</p>
                    @endif
                </div>
                <div>
                    <label for="businessContactNumber">Contact Number</label>
                    <input id="businessContactNumber" name="contact_number" type="text" placeholder="Enter your contact number" value="{{ old('contact_number') }}" required>
                    @if($errors->business->has('contact_number'))
                        <p style="color:#fca5a5; font-size:0.82rem; margin-top:6px;">{{ $errors->business->first('contact_number') }}</p>
                    @endif
                </div>
                <div>
                    <label for="businessPassword">Password</label>
                    <input id="businessPassword" name="password" type="password" placeholder="Enter your password" required>
                    @if($errors->business->has('password'))
                        <p style="color:#fca5a5; font-size:0.82rem; margin-top:6px;">{{ $errors->business->first('password') }}</p>
                    @endif
                    <p style="color:#9aa4b8; font-size:0.8rem; margin-top:6px; text-align:center;">Publish your business inside.</p>
                </div>
                <button type="submit" class="btn-primary" style="justify-content: center; width: 100%; margin-top: 6px;">Create</button>
            </form>
        </div>
    </div>

    <div class="notice-toast" id="loginNoticeToast" role="status" aria-live="polite">
        <i class="fas fa-circle-exclamation"></i>
        <span>Please login first to view nearest gyms.</span>
    </div>

    <script>
        const openLoginModal = document.getElementById('openLoginModal');
        const closeLoginModal = document.getElementById('closeLoginModal');
        const closeSignupModal = document.getElementById('closeSignupModal');
        const loginModalOverlay = document.getElementById('loginModalOverlay');
        const signupModalOverlay = document.getElementById('signupModalOverlay');
        const loginPassword = document.getElementById('loginPassword');
        const showPassword = document.getElementById('showPassword');
        const signupPassword = document.getElementById('signupPassword');
        const signupPasswordConfirm = document.getElementById('signupPasswordConfirm');
        const showSignupPassword = document.getElementById('showSignupPassword');
        const switchToSignup = document.getElementById('switchToSignup');
        const switchToLogin = document.getElementById('switchToLogin');
        const openNearestGymLogin = document.getElementById('openNearestGymLogin');
        const tryWebAppBtn = document.getElementById('tryWebAppBtn');
        const scrollToBusinessBtn = document.getElementById('scrollToBusinessBtn');
        const addBusinessActionBtn = document.getElementById('addBusinessActionBtn');
        const businessPlanSection = document.getElementById('business-plan');
        const businessModalOverlay = document.getElementById('businessModalOverlay');
        const closeBusinessModal = document.getElementById('closeBusinessModal');
        const loginNoticeToast = document.getElementById('loginNoticeToast');
        let loginNoticeTimer;

        function highlightLoginButton() {
            openLoginModal.classList.add('attention');

            setTimeout(function () {
                openLoginModal.classList.remove('attention');
            }, 900);
        }

        function highlightBusinessButton() {
            addBusinessActionBtn.classList.add('attention');

            setTimeout(function () {
                addBusinessActionBtn.classList.remove('attention');
            }, 900);
        }

        function showLoginNotice(message) {
            if (!loginNoticeToast) {
                return;
            }

            loginNoticeToast.querySelector('span').textContent = message;
            const loginBtnRect = openLoginModal.getBoundingClientRect();
            const toastWidth = 300;
            const gap = 10;
            let left = loginBtnRect.left;
            let top = loginBtnRect.bottom + gap;

            if (left + toastWidth > window.innerWidth - 12) {
                left = window.innerWidth - toastWidth - 12;
            }

            if (left < 12) {
                left = 12;
            }

            if (top > window.innerHeight - 80) {
                top = Math.max(12, loginBtnRect.top - 58);
            }

            loginNoticeToast.style.left = left + 'px';
            loginNoticeToast.style.top = top + 'px';
            loginNoticeToast.classList.add('show');

            clearTimeout(loginNoticeTimer);
            loginNoticeTimer = setTimeout(function () {
                loginNoticeToast.classList.remove('show');
            }, 1700);
        }

        openLoginModal.addEventListener('click', function (event) {
            event.preventDefault();
            loginModalOverlay.classList.add('active');
        });

        openNearestGymLogin.addEventListener('click', function (event) {
            event.preventDefault();
            showLoginNotice('Please login first to view nearest gyms.');
            highlightLoginButton();
        });

        tryWebAppBtn.addEventListener('click', function (event) {
            event.preventDefault();
            highlightLoginButton();
        });

        scrollToBusinessBtn.addEventListener('click', function (event) {
            event.preventDefault();
            businessPlanSection.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            setTimeout(function () {
                highlightBusinessButton();
            }, 450);
        });

        addBusinessActionBtn.addEventListener('click', function (event) {
            event.preventDefault();
            businessModalOverlay.classList.add('active');
        });

        closeBusinessModal.addEventListener('click', function () {
            businessModalOverlay.classList.remove('active');
        });

        businessModalOverlay.addEventListener('click', function (event) {
            if (event.target === businessModalOverlay) {
                businessModalOverlay.classList.remove('active');
            }
        });

        closeLoginModal.addEventListener('click', function () {
            loginModalOverlay.classList.remove('active');
        });

        closeSignupModal.addEventListener('click', function () {
            signupModalOverlay.classList.remove('active');
        });

        loginModalOverlay.addEventListener('click', function (event) {
            if (event.target === loginModalOverlay) {
                loginModalOverlay.classList.remove('active');
            }
        });

        signupModalOverlay.addEventListener('click', function (event) {
            if (event.target === signupModalOverlay) {
                signupModalOverlay.classList.remove('active');
            }
        });

        switchToSignup.addEventListener('click', function (event) {
            event.preventDefault();
            loginModalOverlay.classList.remove('active');
            signupModalOverlay.classList.add('active');
        });

        switchToLogin.addEventListener('click', function (event) {
            event.preventDefault();
            signupModalOverlay.classList.remove('active');
            loginModalOverlay.classList.add('active');
        });

        showPassword.addEventListener('change', function () {
            loginPassword.type = this.checked ? 'text' : 'password';
        });

        showSignupPassword.addEventListener('change', function () {
            const passwordType = this.checked ? 'text' : 'password';
            signupPassword.type = passwordType;
            signupPasswordConfirm.type = passwordType;
        });

        if (document.body.dataset.openLogin === '1') {
            loginModalOverlay.classList.add('active');
        }

        if (document.body.dataset.openSignup === '1') {
            signupModalOverlay.classList.add('active');
        }

        if (document.body.dataset.openBusiness === '1') {
            businessModalOverlay.classList.add('active');
        }
    </script>

</body>

</html>