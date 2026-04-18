<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'QuizMind AI — Master Every Subject')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">

    <!-- Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    @stack('styles')
</head>
<body>

    <!-- Background Orbs -->
    <div class="orb o1"></div>
    <div class="orb o2"></div>

    <!-- Navbar -->
    <nav class="qm-navbar">
        <a href="{{ route('home') }}" class="nav-brand">
            <span class="brand-box">QM</span>
            QuizMind <span class="accent">AI</span>
        </a>
        <a href="{{route('home')}}" class="nav-link">Home</a>
        <a href="#features" class="nav-link">Features</a>
        <a href="#how" class="nav-link">How It Works</a>
        <a href="#" class="nav-link">Leaderboard</a>


        <a href="{{ route('login') }}" class="btn btn-grad btn-sm">Get Started</a>
    </nav>

    <!-- Page Content -->
    @yield('content')

    <!-- Three.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    @stack('scripts')
</body>
</html>