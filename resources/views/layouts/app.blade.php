<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Primary Meta Tags for SEO -->
    <title>QuizMind AI - MCQ Battle Arena | Compete & Earn Certificates</title>
    <meta name="description"
        content="QuizMind AI is India's #1 AI-powered MCQ battle platform. Challenge students across institutions, level up, earn verified certificates, and compete globally. Join thousands of students now!">
    <meta name="keywords"
        content="MCQ quiz India, AI quiz platform, online competition, institution battle, quiz certificate, competitive exam prep, student quiz, leaderboard">
    <meta name="author" content="QuizMind AI">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="theme-color" content="#6C47FF">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Geo Targeting -->
    <meta name="geo.region" content="IN">
    <meta name="geo.country" content="India">
    <meta name="language" content="English">

    <!-- Open Graph Tags -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="QuizMind AI">
    <meta property="og:title" content="QuizMind AI - MCQ Battle Arena">
    <meta property="og:description"
        content="Battle students from top institutions across India. AI-generated MCQs, real-time leaderboards, verified certificates.">
    <meta property="og:image" content="{{ asset('images/og-cover.jpg') }}">
    <meta property="og:locale" content="en_IN">

    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@QuizMindAI">
    <meta name="twitter:title" content="QuizMind AI - MCQ Battle Arena">
    <meta name="twitter:description"
        content="Battle students from top institutions. Compete, level up & earn certificates worldwide.">
    <meta name="twitter:image" content="{{ asset('images/og-cover.jpg') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600&display=swap"
        rel="stylesheet">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <style>
        /* Navbar Styles */
        .qm-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(108, 71, 255, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: 70px;
        }

        /* Push page content below fixed navbar */
        body {
            padding-top: 70px;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 700;
            font-family: 'Syne', sans-serif;
            color: #fff;
            transition: opacity 0.3s ease;
            flex-shrink: 0;
        }

        .nav-brand:hover {
            opacity: 0.8;
        }

        .brand-box {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #6C47FF, #8B5CF6);
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .accent {
            color: #6C47FF;
        }

        /* Navigation Links */
        .nav-links-group {
            display: flex;
            align-items: center;
            gap: 2rem;
            list-style: none;
        }

        .nav-link {
            text-decoration: none;
            color: #e2e8f0;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: #6C47FF;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #6C47FF, #8B5CF6);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            border: none;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-grad {
            background: linear-gradient(135deg, #6C47FF, #8B5CF6);
            color: white;
            box-shadow: 0 4px 15px rgba(108, 71, 255, 0.3);
        }

        .btn-grad:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 71, 255, 0.4);
        }

        .btn-sm {
            padding: 0.65rem 1.25rem;
            font-size: 0.9rem;
        }

        /* Hamburger Menu */
        .nav-toggle {
            display: none;
            flex-direction: column;
            gap: 6px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            z-index: 1001;
            flex-shrink: 0;
        }

        .nav-toggle span {
            display: block;
            width: 28px;
            height: 3px;
            background: #fff;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .nav-toggle.open span:nth-child(1) {
            transform: translateY(9px) rotate(45deg);
        }

        .nav-toggle.open span:nth-child(2) {
            opacity: 0;
        }

        .nav-toggle.open span:nth-child(3) {
            transform: translateY(-9px) rotate(-45deg);
        }

        /* Overlay backdrop */
        .nav-backdrop {
            display: none;
            position: fixed;
            inset: 70px 0 0 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 998;
        }

        .nav-backdrop.active {
            display: block;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .qm-navbar {
                padding: 1rem;
                height: auto;
            }

            body {
                padding-top: auto;
            }

            /* Show hamburger toggle */
            .nav-toggle {
                display: flex;
            }

            /* Dropdown menu - sits directly under navbar */
            .nav-links-group {
                position: fixed;
                top: 70px;
                left: 0;
                right: 0;
                width: 100%;
                background: rgba(15, 23, 42, 0.99);
                border-bottom: 2px solid #6C47FF;
                flex-direction: column;
                align-items: stretch;
                gap: 0;
                padding: 0;
                z-index: 999;

                /* Hidden state */
                transform: translateY(-8px);
                opacity: 0;
                pointer-events: none;
                visibility: hidden;
                transition: transform 0.25s ease, opacity 0.25s ease, visibility 0.25s;
            }

            /* Open state */
            .nav-links-group.open {
                transform: translateY(0);
                opacity: 1;
                pointer-events: auto;
                visibility: visible;
            }

            .nav-link {
                padding: 1rem 2rem;
                border-radius: 0;
                border-bottom: 1px solid rgba(108, 71, 255, 0.05);
                font-size: 0.95rem;
                color: #cbd5e1;
            }

            .nav-link:last-child {
                border-bottom: none;
            }

            .nav-link:hover,
            .nav-link.active {
                background: rgba(108, 71, 255, 0.15);
                color: #fff;
                border-radius: 0;
            }

            .nav-link::after {
                display: none;
            }

            .btn {
                margin: 0.75rem 2rem;
                width: calc(100% - 4rem);
                border-radius: 6px;
            }
        }

        @media (max-width: 480px) {
            .qm-navbar {
                padding: 0.75rem;
            }

            .nav-brand {
                font-size: 1.25rem;
            }

            .brand-box {
                width: 36px;
                height: 36px;
                font-size: 0.9rem;
            }

            .nav-links-group {
                top: 60px;
            }
        }

        /* Main Content */
        main {
            min-height: calc(100vh - 100px);
        }

        /* Background Gradient */
        .bg-gradient {
            background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 100%);
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="qm-navbar">
        <!-- Logo/Brand -->
        <a href="{{ route('home') }}" class="nav-brand">
            <span class="brand-box">QM</span>
            <span>QuizMind <span class="accent">AI</span></span>
        </a>

        <!-- Hamburger Menu Button -->
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation menu" aria-expanded="false"
            aria-controls="navLinksGroup">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Navigation Links -->
        <div class="nav-links-group" id="navLinksGroup">
            <a href="{{ route('home') }}" class="nav-link">Home</a>
            <a href="#features" class="nav-link">Features</a>
            <a href="#how-it-works" class="nav-link">How It Works</a>
            {{-- <a href="#leaderboard" class="nav-link">Leaderboard</a> --}}
            <a href="{{ route('login') }}" class="btn btn-grad btn-sm">Get Started</a>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="bg-gradient">
        @yield('content')
    </main>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script>
        // Mobile Menu Toggle
        const navToggle = document.getElementById('navToggle');
        const navLinksGroup = document.getElementById('navLinksGroup');

        navToggle.addEventListener('click', function() {
            const isOpen = navLinksGroup.classList.toggle('open');
            navToggle.classList.toggle('open', isOpen);
            navToggle.setAttribute('aria-expanded', isOpen);
        });

        // Close menu when link is clicked
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                navLinksGroup.classList.remove('open');
                navToggle.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            });
        });

        // Close menu on outside click
        document.addEventListener('click', function(event) {
            const isClickInside = navLinksGroup.contains(event.target) || navToggle.contains(event.target);
            if (!isClickInside) {
                navLinksGroup.classList.remove('open');
                navToggle.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
