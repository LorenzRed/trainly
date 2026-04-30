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
            align-items: center;
            gap: 16px;
        }

        .profile-menu {
            position: relative;
        }

        .profile-btn {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            border: 1px solid rgba(251, 191, 36, 0.55);
            background: rgba(251, 191, 36, 0.12);
            color: #fbbf24;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .profile-btn:hover {
            background: rgba(251, 191, 36, 0.22);
            color: #fde68a;
            transform: translateY(-2px);
        }

        .profile-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            min-width: 170px;
            background: #0f1b13;
            border: 1px solid rgba(251, 191, 36, 0.35);
            border-radius: 14px;
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            display: none;
            z-index: 1200;
        }

        .profile-dropdown.open {
            display: block;
        }

        .dropdown-item {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            color: #e5ecf9;
            text-decoration: none;
            font-size: 0.92rem;
            border: 0;
            background: transparent;
            cursor: pointer;
            text-align: left;
        }

        .dropdown-item:hover {
            background: rgba(251, 191, 36, 0.12);
            color: #fde68a;
        }

        .profile-name {
            color: #fde68a;
            font-weight: 600;
            font-size: 0.95rem;
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

<body>

    <div class="container">
        <!-- Navigation -->
        <nav class="navbar">
            <div class="logo">
                <i class="fas fa-dumbbell"></i>
                <span>TRAIN<span style="color:#fbbf24;">LY</span></span>
            </div>

            <div class="nav-buttons">
                <span class="profile-name">{{ Auth::user()->name ?? 'Member' }}</span>
                <div class="profile-menu" id="profileMenu">
                    <button type="button" class="profile-btn" id="profileToggle" aria-label="Profile menu" aria-expanded="false">
                        <i class="fas fa-user"></i>
                    </button>
                    <div class="profile-dropdown" id="profileDropdown">
                        <a href="{{ route('admin.profile') }}" class="dropdown-item">
                            <i class="fas fa-id-badge"></i>
                            Profile
                        </a>
                        <form action="{{ route('auth.logout') }}" method="post">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-right-from-bracket"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
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
                    @if($business)
                        <a href="{{ route('admin.business.settings', $business) }}" class="btn-primary"><i class="fas fa-gear"></i> Manage your Gym</a>
                    @else
                        <a href="{{ route('admin.business') }}" class="btn-primary"><i class="fas fa-location-dot"></i> Add your Business Location</a>
                    @endif
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

        <!-- Footer -->
        <footer>
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

    <script>
        const profileMenu = document.getElementById('profileMenu');
        const profileToggle = document.getElementById('profileToggle');
        const profileDropdown = document.getElementById('profileDropdown');

        profileToggle.addEventListener('click', function () {
            const isOpen = profileDropdown.classList.toggle('open');
            profileToggle.setAttribute('aria-expanded', String(isOpen));
        });

        document.addEventListener('click', function (event) {
            if (!profileMenu.contains(event.target)) {
                profileDropdown.classList.remove('open');
                profileToggle.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                profileDropdown.classList.remove('open');
                profileToggle.setAttribute('aria-expanded', 'false');
            }
        });
    </script>

</body>

</html>