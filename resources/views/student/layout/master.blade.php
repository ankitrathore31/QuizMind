<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Dashboard — QuizMind</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

</head>

{{-- Navbar --}}
<div id="confettiContainer" class="confetti-container"></div>
<div id="toastContainer" class="toast-container"></div>
@include('student.layout.navbar')

<div class="app-layout">

    {{-- Sidebar --}}
    {{-- @include('student.layout.sidebar') --}}

    {{-- Main Content --}}
    <main class="main-content">
        @yield('content')
    </main>

</div>

{{-- Scripts --}}
@yield('scripts')

</body>

</html>
