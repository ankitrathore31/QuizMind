<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QuizMind — @yield('page_title', 'Dashboard')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    {{-- Fonts --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap"
        rel="stylesheet">

    {{-- Three.js CDN --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    <style>
        /* ─── CSS Variables (from QuizMind design system) ─── */
        :root {
            --dark: #08080F;
            --dark2: #0D0D18;
            --card: #111120;
            --card2: #161628;
            --border: rgba(255, 255, 255, 0.07);
            --border2: rgba(255, 255, 255, 0.12);
            --text: #EEEDf8;
            --muted: #8B8AA0;
            --purple: #7C5CFC;
            --violet: #9B7BFF;
            --cyan: #00D4FF;
            --green: #00E396;
            --gold: #FFB800;
            --red: #FF4D6A;
            --pink: #FF6B9D;
            --orange: #FF8C42;
            --gradient: linear-gradient(135deg, #7C5CFC, #00D4FF);
            --grad2: linear-gradient(135deg, #FF6B9D, #FFB800);
            --grad3: linear-gradient(135deg, #00E396, #00D4FF);
            --fh: 'Syne', sans-serif;
            --fb: 'DM Sans', sans-serif;
            --radius: 16px;
            --radius-sm: 10px;
            --radius-xs: 8px;
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            --shadow-purple: 0 8px 32px rgba(124, 92, 252, 0.25);
            --sidebar-w: 260px;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background: var(--dark);
            color: var(--text);
            font-family: var(--fb);
            font-size: 15px;
            line-height: 1.6;
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── Three.js Canvas ─── */
        #three-canvas {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        /* ─── App Shell ─── */
        .app-shell {
            /* display: flex; */
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* ─── Sidebar ─── */
        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: rgba(8, 8, 15, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-right: 1px solid rgba(124, 92, 252, 0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 24px 16px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 50;
            transition: transform 0.3s ease;
        }

        /* Sidebar top glow line */
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--gradient);
            opacity: 0.7;
        }

        /* ─── Logo / Brand ─── */
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
            padding: 0 4px;
            text-decoration: none;
        }

        .brand-cube {
            width: 38px;
            height: 38px;
            background: var(--gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 0 20px rgba(124, 92, 252, 0.5);
            animation: brandPulse 3s ease-in-out infinite;
            flex-shrink: 0;
        }

        .brand-text {
            font-family: var(--fh);
            font-size: 1.15rem;
            font-weight: 800;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 0.5px;
        }

        .brand-version {
            font-size: 0.55rem;
            color: var(--muted);
            font-family: var(--fh);
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        @keyframes brandPulse {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(124, 92, 252, 0.5);
            }

            50% {
                box-shadow: 0 0 35px rgba(0, 212, 255, 0.6);
            }
        }

        /* ─── Institution Card ─── */
        .inst-card {
            background: linear-gradient(135deg, rgba(124, 92, 252, 0.08), rgba(0, 212, 255, 0.04));
            border: 1px solid rgba(124, 92, 252, 0.2);
            border-radius: var(--radius-sm);
            padding: 14px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }

        .inst-card::before {
            content: '';
            position: absolute;
            top: -30px;
            right: -30px;
            width: 80px;
            height: 80px;
            background: radial-gradient(circle, rgba(124, 92, 252, 0.15), transparent);
            border-radius: 50%;
        }

        .inst-card:hover {
            border-color: rgba(124, 92, 252, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(124, 92, 252, 0.15);
        }

        .inst-label {
            font-size: 0.62rem;
            font-family: var(--fh);
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .inst-code-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .inst-code {
            font-family: var(--fh);
            font-size: 1rem;
            font-weight: 800;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 2px;
        }

        .copy-btn {
            width: 28px;
            height: 28px;
            border: 1px solid rgba(124, 92, 252, 0.25);
            background: rgba(124, 92, 252, 0.08);
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: var(--muted);
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .copy-btn:hover {
            border-color: var(--violet);
            background: rgba(124, 92, 252, 0.18);
            transform: scale(1.1);
        }

        .inst-name {
            font-size: 0.74rem;
            color: var(--muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ─── Nav Menu ─── */
        .nav-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 3px;
            flex: 1;
        }

        .nav-item-label {
            font-size: 0.6rem;
            font-family: var(--fh);
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: rgba(139, 138, 160, 0.5);
            padding: 10px 12px 4px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: var(--radius-xs);
            color: var(--muted);
            font-size: 0.84rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 0;
            background: rgba(124, 92, 252, 0.12);
            border-radius: var(--radius-xs);
            transition: width 0.2s;
        }

        .nav-link:hover::before {
            width: 100%;
        }

        .nav-link:hover {
            color: var(--text);
            transform: translateX(4px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, rgba(124, 92, 252, 0.18), rgba(0, 212, 255, 0.06));
            border: 1px solid rgba(124, 92, 252, 0.3);
            color: var(--text);
            box-shadow: 0 4px 16px rgba(124, 92, 252, 0.15);
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            bottom: 20%;
            width: 3px;
            background: var(--gradient);
            border-radius: 2px;
        }

        .nav-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.04);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        .nav-link:hover .nav-icon,
        .nav-link.active .nav-icon {
            background: rgba(124, 92, 252, 0.15);
        }

        .nav-badge {
            margin-left: auto;
            font-size: 0.6rem;
            font-family: var(--fh);
            font-weight: 700;
            background: rgba(124, 92, 252, 0.2);
            border: 1px solid rgba(124, 92, 252, 0.3);
            color: var(--violet);
            padding: 1px 7px;
            border-radius: 20px;
        }

        /* ─── Sidebar Footer ─── */
        .sidebar-footer {
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }

        .user-mini {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: var(--radius-xs);
            margin-bottom: 12px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border);
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-family: var(--fh);
            font-weight: 800;
            flex-shrink: 0;
        }

        .user-info-name {
            font-size: 0.8rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-info-role {
            font-size: 0.64rem;
            color: var(--muted);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 10px;
            border-radius: var(--radius-xs);
            background: rgba(255, 77, 106, 0.08);
            border: 1px solid rgba(255, 77, 106, 0.2);
            color: var(--red);
            font-family: var(--fh);
            font-weight: 700;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background: rgba(255, 77, 106, 0.18);
            border-color: rgba(255, 77, 106, 0.4);
            box-shadow: 0 0 20px rgba(255, 77, 106, 0.2);
            transform: translateY(-1px);
        }

        /* ─── Top Bar ─── */
        .topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-w);
            right: 0;
            height: 60px;
            background: rgba(8, 8, 15, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            z-index: 40;
        }

        .topbar-left {
            display: flex;
            flex-direction: column;
        }

        .topbar-title {
            font-family: var(--fh);
            font-size: 0.9rem;
            font-weight: 800;
        }

        .topbar-breadcrumb {
            font-size: 0.7rem;
            color: var(--muted);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-time {
            font-family: var(--fh);
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--muted);
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            padding: 4px 12px;
            border-radius: 20px;
        }

        .topbar-notif {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }

        .topbar-notif:hover {
            border-color: var(--violet);
            background: rgba(124, 92, 252, 0.1);
        }

        .notif-dot {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 8px;
            height: 8px;
            background: var(--red);
            border-radius: 50%;
            border: 2px solid var(--dark);
            animation: notifPulse 2s infinite;
        }

        @keyframes notifPulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.3);
            }
        }

        /* ─── Main Content ─── */
        .main-content {
            margin-left: var(--sidebar-w);
            padding: 88px 28px 60px;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* ─── Scrollbar ─── */
        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(124, 92, 252, 0.25);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(124, 92, 252, 0.45);
        }

        /* ─── Toast ─── */
        .toast-zone {
            position: fixed;
            bottom: 28px;
            right: 28px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .toast-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 18px;
            background: var(--card2);
            border: 1px solid var(--border2);
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow);
            min-width: 260px;
            animation: toastIn 0.3s ease both;
            font-size: 0.84rem;
        }

        .toast-item.success {
            border-left: 3px solid var(--green);
        }

        .toast-item.error {
            border-left: 3px solid var(--red);
        }

        .toast-item.info {
            border-left: 3px solid var(--cyan);
        }

        @keyframes toastIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* ─── Particle Overlay ─── */
        .particle-overlay {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        /* ─── Responsive ─── */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 80px 16px 60px;
            }

            .topbar {
                left: 0;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

    <!-- Three.js Canvas -->
    <canvas id="three-canvas"></canvas>

    <!-- App Shell -->
    <div class="app-shell">

        <!-- ══ SIDEBAR ══ -->
        <aside class="sidebar" id="sidebar">

            <!-- Brand -->
            <div>
                <a href="{{ route('institution.dashboard') }}" class="brand">
                    <div class="brand-cube">🧠</div>
                    <div>
                        <div class="brand-text">QuizMind</div>
                        <div class="brand-version">Institution</div>
                    </div>
                </a>

                <!-- Navigation -->
                <ul class="nav-menu">
                    <li class="nav-item-label">Main</li>

                    <li>
                        <a href="{{ route('institution.dashboard') }}"
                            class="nav-link {{ request()->routeIs('institution.dashboard') ? 'active' : '' }}">
                            <span class="nav-icon">📊</span>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('institution.students') }}"
                            class="nav-link {{ request()->routeIs('institution.students') ? 'active' : '' }}">
                            <span class="nav-icon">👥</span>
                            <span>Students</span>
                            @if (isset($stats['total_students']) && $stats['total_students'] > 0)
                                <span class="nav-badge">{{ $stats['total_students'] }}</span>
                            @endif
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('institution.aiquiz') }}"
                            class="nav-link {{ request()->routeIs('institution.aiquiz') ? 'active' : '' }}">
                            <span class="nav-icon">🤖</span>
                            <span>AI Quiz</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{-- route('institution.battles') --}}"
                            class="nav-link {{ request()->routeIs('institution.battles') ? 'active' : '' }}">
                            <span class="nav-icon">⚔️</span>
                            <span>Battle Arena</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{-- route('institution.history') --}}"
                            class="nav-link {{ request()->routeIs('institution.history') ? 'active' : '' }}">
                            <span class="nav-icon">📜</span>
                            <span>History</span>
                        </a>
                    </li>

                    <li class="nav-item-label">Config</li>

                    <li>
                        <a href="{{ route('institution.settings') }}"
                            class="nav-link {{ request()->routeIs('institution.settings') ? 'active' : '' }}">
                            <span class="nav-icon">⚙️</span>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer">
                <div class="user-mini">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div style="overflow:hidden">
                        <div class="user-info-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                        <div class="user-info-role">Institution Admin</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <span>🚪</span> Logout
                    </button>
                </form>
            </div>

        </aside>

        <!-- ══ TOPBAR ══ -->
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:14px">
                <button onclick="toggleSidebar()"
                    style="background:none;border:none;color:var(--muted);font-size:18px;cursor:pointer;display:none"
                    id="menu-toggle">☰</button>
                <div class="topbar-left">
                    <div class="topbar-title">@yield('page_title', 'Dashboard')</div>
                    <div class="topbar-breadcrumb">QuizMind › @yield('page_title', 'Dashboard')</div>
                </div>
            </div>

            <div class="topbar-right">
                <div class="topbar-time" id="topbar-clock">--:--</div>

                <div class="topbar-notif">
                    🔔
                    <span class="notif-dot"></span>
                </div>

                <div
                    style="width:32px;height:32px;border-radius:50%;background:var(--gradient);display:flex;align-items:center;justify-content:center;font-family:var(--fh);font-weight:800;font-size:13px;border:2px solid rgba(124,92,252,0.3)">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
            </div>
        </header>

        <!-- ══ MAIN CONTENT ══ -->
        <main class="main-content">
            @if (session('success'))
                <div id="flash-success" style="display:none" data-msg="{{ session('success') }}"></div>
            @endif
            @if (session('error'))
                <div id="flash-error" style="display:none" data-msg="{{ session('error') }}"></div>
            @endif

            @yield('content')
        </main>

    </div>

    <!-- Toast Zone -->
    <div class="toast-zone" id="toastZone"></div>

    <!-- ══ THREE.JS BACKGROUND ══ -->
    <script>
        (function() {
            const canvas = document.getElementById('three-canvas');
            const renderer = new THREE.WebGLRenderer({
                canvas,
                alpha: true,
                antialias: true
            });
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
            renderer.setSize(window.innerWidth, window.innerHeight);

            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            camera.position.z = 5;

            // ── Floating Geometries ──
            const geometries = [
                new THREE.OctahedronGeometry(0.4, 0),
                new THREE.TetrahedronGeometry(0.35, 0),
                new THREE.IcosahedronGeometry(0.3, 0),
                new THREE.OctahedronGeometry(0.25, 0),
                new THREE.TetrahedronGeometry(0.45, 0),
                new THREE.IcosahedronGeometry(0.2, 0),
            ];

            const materials = [
                new THREE.MeshBasicMaterial({
                    color: 0x7C5CFC,
                    wireframe: true,
                    transparent: true,
                    opacity: 0.3
                }),
                new THREE.MeshBasicMaterial({
                    color: 0x00D4FF,
                    wireframe: true,
                    transparent: true,
                    opacity: 0.25
                }),
                new THREE.MeshBasicMaterial({
                    color: 0x9B7BFF,
                    wireframe: true,
                    transparent: true,
                    opacity: 0.2
                }),
                new THREE.MeshBasicMaterial({
                    color: 0x00E396,
                    wireframe: true,
                    transparent: true,
                    opacity: 0.18
                }),
                new THREE.MeshBasicMaterial({
                    color: 0xFF6B9D,
                    wireframe: true,
                    transparent: true,
                    opacity: 0.15
                }),
            ];

            const meshes = [];
            for (let i = 0; i < 18; i++) {
                const geo = geometries[i % geometries.length];
                const mat = materials[i % materials.length];
                const mesh = new THREE.Mesh(geo, mat);
                mesh.position.set(
                    (Math.random() - 0.5) * 18,
                    (Math.random() - 0.5) * 12,
                    (Math.random() - 0.5) * 8
                );
                mesh.rotation.set(Math.random() * Math.PI, Math.random() * Math.PI, 0);
                mesh.userData = {
                    speedX: (Math.random() - 0.5) * 0.004,
                    speedY: (Math.random() - 0.5) * 0.003,
                    speedZ: (Math.random() - 0.5) * 0.002,
                    floatSpeed: Math.random() * 0.0008 + 0.0004,
                    floatOffset: Math.random() * Math.PI * 2,
                };
                scene.add(mesh);
                meshes.push(mesh);
            }

            // ── Particle Field ──
            const particleCount = 180;
            const particleGeo = new THREE.BufferGeometry();
            const positions = new Float32Array(particleCount * 3);
            for (let i = 0; i < particleCount; i++) {
                positions[i * 3] = (Math.random() - 0.5) * 25;
                positions[i * 3 + 1] = (Math.random() - 0.5) * 18;
                positions[i * 3 + 2] = (Math.random() - 0.5) * 10;
            }
            particleGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            const particleMat = new THREE.PointsMaterial({
                color: 0x7C5CFC,
                size: 0.04,
                transparent: true,
                opacity: 0.6
            });
            const particles = new THREE.Points(particleGeo, particleMat);
            scene.add(particles);

            // ── Mouse Parallax ──
            let mouseX = 0,
                mouseY = 0;
            document.addEventListener('mousemove', e => {
                mouseX = (e.clientX / window.innerWidth - 0.5) * 0.4;
                mouseY = (e.clientY / window.innerHeight - 0.5) * 0.3;
            });

            // ── Animate ──
            let t = 0;

            function animate() {
                requestAnimationFrame(animate);
                t += 0.01;

                meshes.forEach(mesh => {
                    mesh.rotation.x += mesh.userData.speedX;
                    mesh.rotation.y += mesh.userData.speedY;
                    mesh.rotation.z += mesh.userData.speedZ;
                    mesh.position.y += Math.sin(t + mesh.userData.floatOffset) * mesh.userData.floatSpeed;
                });

                particles.rotation.y += 0.0004;
                particles.rotation.x += 0.0002;

                camera.position.x += (mouseX - camera.position.x) * 0.04;
                camera.position.y += (-mouseY - camera.position.y) * 0.04;

                renderer.render(scene, camera);
            }
            animate();

            window.addEventListener('resize', () => {
                camera.aspect = window.innerWidth / window.innerHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(window.innerWidth, window.innerHeight);
            });
        })();
    </script>

    <!-- ══ GLOBAL JS ══ -->
    <script>
        // ── Clock ──
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('topbar-clock').textContent = h + ':' + m;
        }
        updateClock();
        setInterval(updateClock, 30000);

        // ── Toast System ──
        function showToast(msg, type = 'info') {
            const icons = {
                success: '✅',
                error: '❌',
                info: 'ℹ️',
                warn: '⚠️'
            };
            const zone = document.getElementById('toastZone');
            const el = document.createElement('div');
            el.className = `toast-item ${type}`;
            el.innerHTML = `<span>${icons[type] || 'ℹ️'}</span><span>${msg}</span>`;
            zone.appendChild(el);
            setTimeout(() => {
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            }, 3500);
        }

        // ── Flash Messages ──
        const flashSuccess = document.getElementById('flash-success');
        const flashError = document.getElementById('flash-error');
        if (flashSuccess) showToast(flashSuccess.dataset.msg, 'success');
        if (flashError) showToast(flashError.dataset.msg, 'error');

        // ── Copy to Clipboard ──
        function copyToClipboard(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const original = btn.innerHTML;
                btn.innerHTML = '✅';
                showToast('Code copied: ' + text, 'success');
                setTimeout(() => btn.innerHTML = original, 1500);
            });
        }

        // ── Mobile Sidebar Toggle ──
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }

        // ── Responsive menu toggle visibility ──
        if (window.innerWidth <= 768) {
            document.getElementById('menu-toggle').style.display = 'flex';
        }
    </script>

    @stack('scripts')

</body>

</html>
