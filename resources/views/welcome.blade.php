@extends('layouts.app')

@section('title', 'QuizMind AI — Master Every Subject with AI Battle Quizzes')

@section('content')

{{-- ═══════════════════════════════════════════
     HERO SECTION — Three.js 3D Background
═══════════════════════════════════════════ --}}
<section class="hero-section">

    {{-- Three.js canvas --}}
    <div id="three-bg"></div>

    {{-- Hero Content --}}
    <div class="hero-content">

        <div class="hero-badge">
            ✨ AI-POWERED QUIZ BATTLE PLATFORM
        </div>

        <h1 class="hero-title">
            Master Every Subject<br>
            <span class="text-grad">with AI Battle Quizzes</span>
        </h1>

        <p class="hero-desc">
            Generate MCQs from any topic, PDF, or image. Battle friends live.
            AI tracks your weak spots and guides improvement.
        </p>

        <div class="hero-buttons">
            <a href="{{ route('login') }}" class="btn btn-grad btn-lg">Get Started Free →</a>
            <a href="{{ route('login') }}" class="btn btn-ghost btn-lg">Login</a>
        </div>

        {{-- Stats --}}
        <div class="hero-stats">
            @foreach ([['50K+','Students'], ['2M+','Quizzes Played'], ['500+','Schools'], ['98%','Satisfaction']] as $i => [$val, $label])
            <div style="text-align:center; animation-delay:{{ $i * 0.1 }}s" class="anim-fade">
                <div class="hero-stat-val">{{ $val }}</div>
                <div class="hero-stat-label">{{ $label }}</div>
            </div>
            @endforeach
        </div>

    </div>

    {{-- Scroll indicator --}}
    <div class="scroll-indicator">
        <div class="scroll-line"></div>
        scroll
    </div>

</section>

{{-- ═══════════════════════════════════════════
     FEATURES SECTION
═══════════════════════════════════════════ --}}
<section id="features" class="features-section">

    <div class="text-center mb-28 reveal">
        <div class="section-eyebrow">FEATURES</div>
        <h2 class="section-title">Everything you need to excel</h2>
    </div>

    <div class="grid-3">
        @php
        $features = [
            ['🤖', 'AI Quiz Generator',   'Generate MCQs instantly from any topic, PDF document, or educational image using Groq AI.'],
            ['⚔️', 'Live Battle System',  '1v1, Team vs Team, and epic School vs School battles with real-time leaderboards.'],
            ['📊', 'Smart Analytics',     'AI detects weak subjects and creates personalized practice schedules automatically.'],
            ['🛡️', 'Anti-Cheat Engine',   'Tab switch detection, window blur tracking, and real-time penalty system.'],
            ['👨‍👩‍👧', 'Multi-Role Platform', 'Dedicated dashboards for students, parents, teachers, and institutions.'],
            ['🏆', 'Gamification',        'XP system, streaks, trophies, badges, and global leaderboards to keep you motivated.'],
        ];
        @endphp

        @foreach ($features as $i => $f)
        <div class="feature-card reveal reveal-delay-{{ $i + 1 }}">
            <span class="feature-icon">{{ $f[0] }}</span>
            <h3 class="feature-title">{{ $f[1] }}</h3>
            <p class="feature-desc">{{ $f[2] }}</p>
        </div>
        @endforeach
    </div>

</section>

{{-- ═══════════════════════════════════════════
     PDF DEMO SECTION
═══════════════════════════════════════════ --}}
<section class="pdf-section">

    <h2 class="pdf-title reveal">Drop a PDF. Get MCQs instantly.</h2>
    <p class="pdf-desc reveal reveal-delay-1">
        Upload any NCERT, textbook, or study notes PDF and our AI generates quiz questions in seconds.
    </p>

    <div class="pdf-demo-box reveal reveal-delay-2" id="pdfDemo">
        <span class="pdf-icon">📄</span>
        <div class="pdf-filename">NCERT_Physics_Chapter5.pdf</div>
        <div class="pdf-progress-track">
            <div class="pdf-progress-bar" id="pdfBar"></div>
        </div>
        <div class="pdf-success" id="pdfSuccess">✅ 10 questions generated from 47 pages!</div>
    </div>

</section>

{{-- ═══════════════════════════════════════════
     HOW IT WORKS SECTION
═══════════════════════════════════════════ --}}
<section id="how" class="how-section">

    <div class="text-center mb-28 reveal">
        <h2 class="section-title">How It Works</h2>
    </div>

    <div class="grid-3">
        @php
        $steps = [
            ['1', 'Sign Up & Choose Role',      'Register as student, teacher, institution, or parent. Link to your school using a reference code.'],
            ['2', 'Generate or Pick a Quiz',    'Choose from standard class-based quizzes or generate AI-powered MCQs from topic, PDF, or image.'],
            ['3', 'Battle & Improve',           'Play solo, invite friends, host team battles. AI tracks performance and suggests improvements.'],
        ];
        @endphp

        @foreach ($steps as $i => $s)
        <div class="text-center reveal reveal-delay-{{ $i + 1 }}">
            <div class="step-number">{{ $s[0] }}</div>
            <h3 class="step-title">{{ $s[1] }}</h3>
            <p class="step-desc">{{ $s[2] }}</p>
        </div>
        @endforeach
    </div>

    <div class="text-center mt-16 reveal">
        <a href="{{ route('login') }}" class="btn btn-grad btn-lg">Start for Free →</a>
    </div>

</section>

{{-- ═══════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════ --}}
<footer>
    © {{ date('Y') }} QuizMind AI — Built for students who want to win.
</footer>

@endsection

@push('scripts')
<script>
/* ═══════════════════════════════════════════════
   Three.js Background — Particles + Wireframe Tori
════════════════════════════════════════════════ */
(function initThreeBackground() {
    const el = document.getElementById('three-bg');
    if (!el || !window.THREE) return;

    const W = el.clientWidth || window.innerWidth;
    const H = el.clientHeight || window.innerHeight;

    /* Renderer */
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(W, H);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setClearColor(0x000000, 0);
    el.appendChild(renderer.domElement);

    /* Scene & Camera */
    const scene  = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(60, W / H, 0.1, 100);
    camera.position.z = 5;

    /* ── Particles ── */
    const geo   = new THREE.BufferGeometry();
    const count = 600;
    const pos   = new Float32Array(count * 3);
    const sizes = new Float32Array(count);
    for (let i = 0; i < count; i++) {
        pos[i * 3]     = (Math.random() - 0.5) * 20;
        pos[i * 3 + 1] = (Math.random() - 0.5) * 20;
        pos[i * 3 + 2] = (Math.random() - 0.5) * 20;
        sizes[i] = Math.random() * 0.05 + 0.02;
    }
    geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
    const mat       = new THREE.PointsMaterial({ color: 0x7C5CFC, size: 0.04, transparent: true, opacity: 0.7 });
    const particles = new THREE.Points(geo, mat);
    scene.add(particles);

    /* ── Torus 1 (purple) ── */
    const tGeo  = new THREE.TorusGeometry(1.2, 0.25, 16, 60);
    const tMat  = new THREE.MeshBasicMaterial({ color: 0x7C5CFC, wireframe: true, transparent: true, opacity: 0.15 });
    const torus = new THREE.Mesh(tGeo, tMat);
    torus.position.x = 3;
    torus.position.y = -0.5;
    scene.add(torus);

    /* ── Torus 2 (cyan) ── */
    const tGeo2  = new THREE.TorusGeometry(0.8, 0.15, 16, 40);
    const tMat2  = new THREE.MeshBasicMaterial({ color: 0x00D4FF, wireframe: true, transparent: true, opacity: 0.12 });
    const torus2 = new THREE.Mesh(tGeo2, tMat2);
    torus2.position.x = -3.5;
    torus2.position.y = 1;
    scene.add(torus2);

    /* ── Icosahedron (extra depth) ── */
    const iGeo  = new THREE.IcosahedronGeometry(0.6, 1);
    const iMat  = new THREE.MeshBasicMaterial({ color: 0x00D4FF, wireframe: true, transparent: true, opacity: 0.08 });
    const icos  = new THREE.Mesh(iGeo, iMat);
    icos.position.set(0, -2.5, -2);
    scene.add(icos);

    /* ── Mouse parallax ── */
    let mouseX = 0, mouseY = 0;
    document.addEventListener('mousemove', e => {
        mouseX = (e.clientX / window.innerWidth  - 0.5) * 0.3;
        mouseY = (e.clientY / window.innerHeight - 0.5) * 0.3;
    });

    /* ── Animation loop ── */
    let frame;
    const animate = () => {
        frame = requestAnimationFrame(animate);
        const t = Date.now() * 0.0005;

        // Particles drift
        particles.rotation.y  = t * 0.1;
        particles.rotation.x  = t * 0.05;

        // Tori spin
        torus.rotation.x  = t;
        torus.rotation.y  = t * 0.7;
        torus2.rotation.x = -t * 0.8;
        torus2.rotation.z =  t * 0.5;

        // Icosahedron
        icos.rotation.x = t * 0.4;
        icos.rotation.y = t * 0.6;

        // Mouse parallax on camera
        camera.position.x += (mouseX - camera.position.x) * 0.05;
        camera.position.y += (-mouseY - camera.position.y) * 0.05;
        camera.lookAt(scene.position);

        renderer.render(scene, camera);
    };
    animate();

    /* ── Resize handler ── */
    window.addEventListener('resize', () => {
        const nW = el.clientWidth, nH = el.clientHeight;
        camera.aspect = nW / nH;
        camera.updateProjectionMatrix();
        renderer.setSize(nW, nH);
    });

    /* Cleanup on page unload */
    window.addEventListener('beforeunload', () => {
        cancelAnimationFrame(frame);
        renderer.dispose();
    });
})();


/* ═══════════════════════════════════════════════
   Scroll Reveal Observer
════════════════════════════════════════════════ */
(function initScrollReveal() {
    const els = document.querySelectorAll('.reveal');
    if (!els.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    els.forEach(el => observer.observe(el));
})();


/* ═══════════════════════════════════════════════
   PDF Demo Animation (triggered on scroll into view)
════════════════════════════════════════════════ */
(function initPdfDemo() {
    const box     = document.getElementById('pdfDemo');
    const bar     = document.getElementById('pdfBar');
    const success = document.getElementById('pdfSuccess');
    if (!box || !bar || !success) return;

    let played = false;
    const runAnim = () => {
        if (played) return;
        played = true;
        setTimeout(() => { bar.style.width = '75%'; }, 200);
        setTimeout(() => { success.style.opacity = '1'; }, 1900);
    };

    const observer = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting) { runAnim(); observer.disconnect(); }
    }, { threshold: 0.4 });

    observer.observe(box);
})();


/* ═══════════════════════════════════════════════
   Stat counter animation (number roll-up)
════════════════════════════════════════════════ */
(function initCounters() {
    const stats = document.querySelectorAll('.hero-stat-val');
    stats.forEach((el, i) => {
        el.style.animationDelay = (0.5 + i * 0.1) + 's';
    });
})();
</script>
@endpush