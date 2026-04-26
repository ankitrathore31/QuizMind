<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Dashboard — QuizMind</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>

<style>
    
    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    /* ═══════════════════════════════════
   NAVBAR
═══════════════════════════════════ */
    .qm-navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
        height: 60px;
        background: #020617;
        border-bottom: 1px solid #1f2937;
        position: fixed;
        /* fixed so menu overlay works correctly */
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
    }

    /* push page content below the fixed navbar */
    body {
        padding-top: 60px;
    }

    /* ── Left ── */
    .nav-left {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .nav-brand {
        display: flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        color: white;
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 15px;
    }

    .brand-box {
        background: #7c5cff;
        padding: 5px 8px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 0.5px;
    }

    .accent {
        color: #7c5cff;
    }

    /* ── Center menu (desktop) ── */
    .nav-menu {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .nav-link {
        color: #9ca3af;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        padding: 7px 12px;
        border-radius: 8px;
        transition: background .2s, color .2s;
        white-space: nowrap;
    }

    .nav-link:hover,
    .nav-link.active {
        color: #fff;
        background: rgba(124, 92, 255, 0.18);
    }

    /* ── Right ── */
    .nav-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .xp-pill,
    .streak-pill {
        background: #0f172a;
        border: 1px solid #1f2937;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: #e2e8f0;
        white-space: nowrap;
    }

    .nav-avatar-btn {
        width: 34px;
        height: 34px;
        background: #7c5cff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        font-size: 14px;
        cursor: pointer;
        border: 2px solid rgba(124, 92, 255, 0.5);
        flex-shrink: 0;
    }

    .nav-avatar-btn img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* ── Hamburger toggle (hidden on desktop) ── */
    .menu-toggle {
        display: none;
        background: none;
        border: 1px solid #1f2937;
        border-radius: 8px;
        color: #e2e8f0;
        font-size: 18px;
        width: 36px;
        height: 36px;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        transition: background .2s;
        flex-shrink: 0;
    }

    .menu-toggle:hover {
        background: rgba(255, 255, 255, 0.07);
    }

    /* ── Overlay backdrop ── */
    .nav-backdrop {
        display: none;
        position: fixed;
        inset: 60px 0 0 0;
        /* starts right below the navbar */
        background: rgba(0, 0, 0, 0.55);
        z-index: 998;
    }

    .nav-backdrop.active {
        display: block;
    }

    /* ═══════════════════════════════════
   MOBILE STYLES  (≤ 768px)
═══════════════════════════════════ */
    @media (max-width: 768px) {

        /* show toggle button */
        .menu-toggle {
            display: flex;
        }

        /* hide xp/streak pills on very small screens to save space */
        .xp-pill,
        .streak-pill {
            display: none;
        }

        /* dropdown menu — sits directly under the navbar */
        .nav-menu {
            position: fixed;
            top: 60px;
            /* exactly navbar height */
            left: 0;
            right: 0;
            width: 100%;
            background: #020617;
            border-bottom: 2px solid #7c5cff;
            flex-direction: column;
            align-items: stretch;
            gap: 0;
            padding: 8px 0;
            z-index: 999;
            /* above backdrop */

            /* hidden state */
            transform: translateY(-8px);
            opacity: 0;
            pointer-events: none;
            visibility: hidden;
            transition: transform .25s ease, opacity .25s ease, visibility .25s;
        }

        /* open state */
        .nav-menu.active {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
            visibility: visible;
        }

        .nav-link {
            padding: 13px 20px;
            border-radius: 0;
            border-bottom: 1px solid #0f1a2e;
            font-size: 14px;
            color: #cbd5e1;
        }

        .nav-link:last-child {
            border-bottom: none;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(124, 92, 255, 0.15);
            color: #fff;
            border-radius: 0;
        }
    }

    @media (max-width: 400px) {
        .brand-text {
            display: none;
        }
    }

    /* ═══════════════════════════════════
   PAGE LAYOUT
═══════════════════════════════════ */
    .app-layout {
        min-height: calc(100vh - 60px);
    }

    .main-content {
        padding: 24px 20px;
        max-width: 1200px;
        margin: 0 auto;
    }
    /* Wrapper */
.nav-avatar-wrapper {
    position: relative;
}

/* Avatar */
.nav-avatar-btn {
    width: 35px;
    height: 35px;
    background: #7c5cff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    overflow: hidden;
}

.nav-avatar-btn img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Dropdown */
.profile-menu {
    position: absolute;
    right: 0;
    top: 45px;
    background: #020617;
    border: 1px solid #1f2937;
    border-radius: 10px;
    width: 150px;
    display: none;
    flex-direction: column;
    z-index: 1000;
}

/* Show */
.profile-menu.active {
    display: flex;
}

/* Items */
.profile-menu a,
.profile-menu button {
    padding: 10px;
    text-align: left;
    color: #cbd5f5;
    background: none;
    border: none;
    width: 100%;
    cursor: pointer;
    text-decoration: none;
}

.profile-menu a:hover,
.profile-menu button:hover {
    background: #0f172a;
    color: #7c5cff;
}
</style>

<body>

    <div id="confettiContainer" class="confetti-container"></div>
    <div id="toastContainer" class="toast-container"></div>

    {{-- ═══ BACKDROP (closes menu on tap outside) ═══ --}}
    <div class="nav-backdrop" id="navBackdrop"></div>

    {{-- ═══ NAVBAR ═══ --}}
    <nav class="qm-navbar">

        {{-- LEFT: toggle + brand --}}
        <div class="nav-left">
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu" aria-expanded="false">☰</button>
            <a href="#" class="nav-brand">
                <span class="brand-box">QM</span>
                <span class="brand-text">Quiz<span class="accent">Mind</span></span>
            </a>
        </div>

        {{-- CENTER: links --}}
        <div class="nav-menu" id="navMenu">
            <a href="{{ route('student.dashboard') }}" class="nav-link">🏠 Dashboard</a>
            <a href="{{ route('student.quiz.index') }}" class="nav-link">🤖 AI Quiz</a>
            <a href="{{ route('student.quiz.tutor.index') }}" class="nav-link">💬 Tutor</a>
            <a href="{{ route('student.battle.join.page') }}" class="nav-link">⚔️ Battles</a>
            <a href="{{ route('student.history.index') }}" class="nav-link">📚 History</a>
            <a href="{{ route('student.certificates') }}" class="nav-link">📜 Certificate</a>

        </div>

        {{-- RIGHT: XP + streak + avatar --}}
        <div class="nav-right">
            <div class="xp-pill">⚡ {{ number_format($student->xp) }}</div>
            <div class="streak-pill">🔥 {{ $student->streak }}</div>
            <div class="nav-avatar-wrapper">

                <div class="nav-avatar-btn" onclick="toggleProfileMenu()">
                    @if ($student->avatar)
                        <img src="{{ asset('storage/' . $student->avatar) }}" alt="avatar">
                    @else
                        <span>{{ $student->display_name ? mb_substr($student->display_name, 0, 1) : '🧑‍🎓' }}</span>
                    @endif
                </div>

                <div class="profile-menu" id="profileMenu">
                    <a href="{{ route('student.profile')}}">⚙️ Settings</a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">🚪 Logout</button>
                    </form>
                </div>

            </div>
        </div>

    </nav>

    {{-- ═══ MAIN CONTENT ═══ --}}
    <div class="app-layout">
        <main class="main-content">
            @yield('content')
        </main>
    </div>

    {{-- ═══ SCRIPTS ═══ --}}
    @yield('scripts')

    <script>
        (function() {
            var toggle = document.getElementById('menuToggle');
            var menu = document.getElementById('navMenu');
            var backdrop = document.getElementById('navBackdrop');

            function openMenu() {
                menu.classList.add('active');
                backdrop.classList.add('active');
                toggle.setAttribute('aria-expanded', 'true');
                toggle.textContent = '✕';
            }

            function closeMenu() {
                menu.classList.remove('active');
                backdrop.classList.remove('active');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.textContent = '☰';
            }

            function toggleMenu() {
                menu.classList.contains('active') ? closeMenu() : openMenu();
            }

            /* toggle button */
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleMenu();
            });

            /* clicking the backdrop closes menu */
            backdrop.addEventListener('click', closeMenu);

            /* clicking a nav link closes menu (for single-page feel) */
            menu.querySelectorAll('.nav-link').forEach(function(link) {
                link.addEventListener('click', closeMenu);
            });

            /* close on Escape key */
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeMenu();
            });

            /* highlight active link */
            var current = window.location.pathname;
            menu.querySelectorAll('.nav-link').forEach(function(link) {
                if (link.getAttribute('href') && link.getAttribute('href') !== '#') {
                    var path = new URL(link.href, window.location.origin).pathname;
                    if (current === path || (current.startsWith(path) && path !== '/')) {
                        link.classList.add('active');
                    }
                }
            });
        })();
    </script>
    <script>
function toggleProfileMenu() {
    document.getElementById('profileMenu').classList.toggle('active');
}

// क्लिक बाहर → close
document.addEventListener('click', function(e) {
    const menu = document.getElementById('profileMenu');
    const avatar = document.querySelector('.nav-avatar-btn');

    if (!menu.contains(e.target) && !avatar.contains(e.target)) {
        menu.classList.remove('active');
    }
});
</script>

</body>

</html>
