@extends('student.layout.master')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* ── Setup Modal ── */
    .setup-modal {
        background: var(--card);
        border: 1px solid rgba(124, 92, 252, 0.3);
        border-radius: 20px;
        width: 100%;
        max-width: 540px;
        /* FIX: use flex column so footer is always visible */
        display: flex;
        flex-direction: column;
        max-height: 90vh;
        box-shadow: 0 30px 80px rgba(0,0,0,0.6), 0 0 60px rgba(124,92,252,0.15);
        animation: popIn .4s cubic-bezier(.34,1.56,.64,1) both;
    }

    /* Scrollable content area — footer stays pinned */
    .step-content {
        flex: 1 1 auto;
        overflow-y: auto;
        padding: 0 24px;
    }

    .step-content::-webkit-scrollbar { width: 4px; }
    .step-content::-webkit-scrollbar-thumb { background: rgba(124,92,252,0.2); border-radius: 2px; }

    /* Footer always pinned at bottom */
    .modal-footer {
        flex-shrink: 0;
        padding: 16px 24px 20px;
        border-top: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--card);
        border-radius: 0 0 20px 20px;
    }

    .modal-header {
        flex-shrink: 0;
        padding: 28px 24px 16px;
        text-align: center;
    }

    .steps-indicator {
        flex-shrink: 0;
        display: flex;
        justify-content: center;
        gap: 8px;
        padding: 0 24px 16px;
    }

    /* Spinner for loading state */
    .spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin .6s linear infinite;
        vertical-align: middle;
        margin-right: 6px;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush

@section('content')

    {{-- Background orbs --}}
    <div class="orb o1"></div>
    <div class="orb o2"></div>
    <div class="orb o3"></div>

    <div class="modal-overlay {{ $student->is_profile_complete ? 'hidden' : '' }}" id="setupModal">
        <div class="setup-modal" id="setupModalCard">

            {{-- Header (pinned) --}}
            <div class="modal-header">
                <span class="modal-wizard-emoji">🎓</span>
                <h2 class="modal-title">Welcome to <span class="accent">QuizMind!</span></h2>
                <p class="modal-sub">Let's set up your student profile in just 3 quick steps.</p>
            </div>

            {{-- Step dots (pinned) --}}
            <div class="steps-indicator">
                <div class="step-dot active" id="dot0"></div>
                <div class="step-dot" id="dot1"></div>
                <div class="step-dot" id="dot2"></div>
            </div>

            {{-- Scrollable content --}}
            <div class="step-content">

                {{-- Step 0: Identity --}}
                <div class="step-pane active" id="step0">
                    <p class="form-label mb-8">Choose Your Avatar</p>
                    <div class="avatar-picker">
                        <div class="avatar-preview" id="avatarPreview">🧑‍🎓</div>
                        <div class="avatar-emojis">
                            @foreach (['🧑‍🎓','👩‍🎓','🦸','🧙','🧚','🦊','🐉','🦁','🐧','⚡','🌟','🔥'] as $e)
                                <button class="avatar-emoji-btn" data-emoji="{{ $e }}" type="button">{{ $e }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Display Name *</label>
                        <input type="text" class="form-input" id="field_display_name"
                            placeholder="How others will see you…" maxlength="60">
                        <div class="field-error" id="err_display_name">Please enter your display name.</div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Age *</label>
                            <input type="number" class="form-input" id="field_age" placeholder="e.g. 16" min="5" max="30">
                            <div class="field-error" id="err_age">Please enter your age (5–30).</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Class / Grade *</label>
                            <select class="form-input" id="field_class">
                                <option value="">Select class…</option>
                                @foreach (['Class 5','Class 6','Class 7','Class 8','Class 9','Class 10','Class 11','Class 12','1st Year UG','2nd Year UG','3rd Year UG','4th Year UG','Postgraduate','Other'] as $c)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                            </select>
                            <div class="field-error" id="err_class">Please select your class.</div>
                        </div>
                    </div>
                </div>

                {{-- Step 1: School + Subjects --}}
                <div class="step-pane" id="step1">
                    <div class="form-group">
                        <label class="form-label">School / College Name *</label>
                        <input type="text" class="form-input" id="field_school_name"
                            placeholder="Your institution name…" maxlength="120">
                        <div class="field-error" id="err_school_name">Please enter your school name.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subjects You're Interested In</label>
                        <div class="subject-tags" id="subjectTags">
                            @foreach (['Mathematics','Physics','Chemistry','Biology','History','Geography','English','Hindi','Computer Sci','Economics','Political Sci','Psychology','Art','Music','Sports'] as $s)
                                <span class="subject-tag" data-subject="{{ $s }}">{{ $s }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Step 2: Bio + Finish --}}
                <div class="step-pane" id="step2">
                    <div class="text-center mb-16">
                        <div style="font-size:3rem" class="anim-float mb-8">🚀</div>
                        <p style="font-family:var(--fh);font-weight:700;font-size:1rem">Almost there!</p>
                        <p class="text-muted" style="font-size:.85rem;margin-top:4px">Add a quick bio and you're all set.</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bio
                            <span style="font-weight:400;text-transform:none;letter-spacing:0">(optional)</span>
                        </label>
                        <textarea class="form-input" id="field_bio"
                            placeholder="Tell the QuizMind community a bit about yourself…"
                            rows="4"
                            maxlength="300"></textarea>
                        <div class="text-right" style="font-size:.72rem;color:var(--muted);margin-top:4px" id="bioCounter">0 / 300</div>
                    </div>
                    <div style="background:rgba(124,92,252,0.06);border:1px solid rgba(124,92,252,0.15);border-radius:var(--radius-xs);padding:14px 16px;margin-bottom:8px">
                        <p style="font-family:var(--fh);font-size:.8rem;font-weight:700;margin-bottom:6px">✨ What you'll unlock:</p>
                        <p style="font-size:.8rem;color:var(--muted);line-height:1.8">
                            🔥 Daily streak tracking &nbsp;|&nbsp; ⚔️ Battle access<br>
                            🏆 Leaderboard ranking &nbsp;|&nbsp; 🎖️ XP &amp; Level system
                        </p>
                    </div>
                </div>

            </div>{{-- /step-content --}}

            {{-- Footer (pinned — always visible) --}}
            <div class="modal-footer">
                <span class="step-counter" id="stepCounter">Step 1 of 3</span>
                <div class="flex gap-10">
                    <button class="btn btn-ghost btn-sm" id="prevBtn" style="display:none" type="button" onclick="modalPrev()">← Back</button>
                    <button class="btn btn-grad btn-sm" id="nextBtn" type="button" onclick="modalNext()">Continue →</button>
                </div>
            </div>

        </div>
    </div>


    <div class="page" style="max-width:1280px">

        {{-- ── Hero Banner with Three.js ── --}}
        <div class="hero-banner anim-fade" id="heroBanner" style="position:relative;min-height:180px;overflow:hidden">
            <canvas id="qmCanvas" style="position:absolute;inset:0;width:100%;height:100%;z-index:0;pointer-events:none;display:block"></canvas>
            <div class="banner-3d-sphere" id="sphere3d"></div>
            <div style="position:relative;z-index:1">
                <div class="banner-welcome">Welcome back</div>
                <div class="banner-name">Hey, {{ $student->display_name ?: $user->name }} 👋</div>
                <p class="banner-sub">
                    You're on a <strong style="color:var(--orange)">{{ $student->streak }}-day streak</strong> 🔥
                    Keep it up and claim your weekly bonus XP!
                </p>
                <div class="banner-actions">
                    <a href="{{ route('student.quiz.index') }}" class="btn btn-grad">⚡ Start Quiz</a>
                    <a href="{{ route('student.battle.join.page') }}" class="btn btn-ghost">⚔️ Find Battle</a>
                </div>
            </div>
        </div>

        {{-- ── Stat Cards ── --}}
        <div class="stat-cards">
            <div class="stat-card sc-purple anim-fade" style="animation-delay:.00s">
                <span class="stat-icon">🎯</span>
                <div class="stat-val grad">{{ $student->total_quizzes }}</div>
                <div class="stat-label">Quizzes Taken</div>
                <div class="stat-delta">+3 this week</div>
            </div>
            <div class="stat-card sc-cyan anim-fade" style="animation-delay:.08s">
                <span class="stat-icon">✅</span>
                <div class="stat-val cyan">{{ $student->accuracy }}%</div>
                <div class="stat-label">Accuracy Rate</div>
                <div class="stat-delta">▲ 4%</div>
            </div>
            <div class="stat-card sc-gold anim-fade" style="animation-delay:.16s">
                <span class="stat-icon">⚔️</span>
                <div class="stat-val gold">{{ $student->win_rate }}%</div>
                <div class="stat-label">Battle Win Rate</div>
                <div class="stat-delta">{{ $student->total_battles_won }}W / {{ $student->total_battles_lost }}L</div>
            </div>
            <div class="stat-card sc-green anim-fade" style="animation-delay:.24s">
                <span class="stat-icon">🔥</span>
                <div class="stat-val green">{{ $student->streak }}</div>
                <div class="stat-label">Day Streak</div>
                <div class="stat-delta">🌟 Personal best</div>
            </div>
        </div>

        {{-- ── Main 2-col grid ── --}}
        <div class="dash-grid">

            {{-- LEFT --}}
            <div>

                {{-- Level Card --}}
                <div class="level-card">
                    <div class="flex gap-20">
                        <div class="level-ring-wrap">
                            <svg class="level-ring-svg" viewBox="0 0 90 90">
                                <circle class="level-ring-bg" cx="45" cy="45" r="36"/>
                                <circle class="level-ring-fill" id="xpRingFill" cx="45" cy="45" r="36"
                                    stroke="url(#ringGrad)"
                                    stroke-dasharray="{{ round(2 * 3.14159 * 36, 2) }}"
                                    stroke-dashoffset="{{ round(2 * 3.14159 * 36 * (1 - $student->xp_progress / 100), 2) }}"/>
                                <defs>
                                    <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#7C5CFC"/>
                                        <stop offset="100%" stop-color="#00D4FF"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div class="level-ring-val">
                                <span class="level-ring-num">{{ $student->level }}</span>
                                <span class="level-ring-lbl">Level</span>
                            </div>
                        </div>
                        <div class="level-meta">
                            <div class="level-title-txt">{{ $student->display_name ?: $user->name }}</div>
                            <div class="level-title-badge">{{ $student->level_title }}</div>
                            <div class="xp-track">
                                <div class="xp-fill" id="xpBar" style="width:{{ $student->xp_progress }}%"></div>
                            </div>
                            <div class="xp-row">
                                <span class="xp-current">{{ number_format($student->xp) }} XP</span>
                                <span>Next: {{ number_format($student->xpToNextLevel) }} XP</span>
                            </div>
                        </div>
                    </div>

                    @if ($student->subjects_interest && count($student->subjects_interest))
                        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border)">
                            <div class="form-label mb-8">Your Subjects</div>
                            <div class="flex gap-6" style="flex-wrap:wrap">
                                @foreach ($student->subjects_interest as $subj)
                                    <span class="badge bp">{{ $subj }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Recent Activity --}}
                <div class="card reveal reveal-delay-1">
                    <div class="section-hd">
                        <div class="section-title">📋 Recent Activity</div>
                        <a href="#" class="section-action">View all →</a>
                    </div>
                    @foreach ($recentActivity as $a)
                        <div class="activity-item">
                            <div class="act-icon-box">{{ $a['icon'] }}</div>
                            <div style="flex:1;min-width:0">
                                <div class="act-subject">{{ $a['subject'] }}</div>
                                <div class="act-meta">{{ $a['time'] }} · <span class="act-xp">+{{ $a['xp'] }} XP</span></div>
                            </div>
                            <div class="act-score-badge {{ $a['score'] >= 80 ? 'high' : ($a['score'] >= 60 ? 'mid' : 'low') }}">
                                {{ $a['score'] }}%
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Badges --}} <br>
                <div class="card mt-20 reveal">
                    <div class="section-hd">
                        <div class="section-title">🎖️ Badges</div>
                        <a href="#" class="section-action">See all →</a>
                    </div>
                    <div class="badge-grid">
                        @php
                            $allBadges = [
                                ['emoji' => '🔥', 'name' => 'Streak 7',   'earned' => $student->streak >= 7],
                                ['emoji' => '⚡', 'name' => 'First Quiz', 'earned' => $student->total_quizzes >= 1],
                                ['emoji' => '⚔️', 'name' => 'First Win',  'earned' => $student->total_battles_won >= 1],
                                ['emoji' => '🏆', 'name' => 'Top 10',     'earned' => false],
                                ['emoji' => '💯', 'name' => 'Perfect',    'earned' => false],
                                ['emoji' => '🌟', 'name' => 'Scholar',    'earned' => $student->level >= 10],
                                ['emoji' => '🧠', 'name' => 'Brainiac',   'earned' => false],
                                ['emoji' => '👑', 'name' => 'Champion',   'earned' => false],
                            ];
                        @endphp
                        @foreach ($allBadges as $badge)
                            <div class="badge-item {{ $badge['earned'] ? 'earned' : 'locked' }}">
                                <span class="badge-emoji">{{ $badge['emoji'] }}</span>
                                <span class="badge-name">{{ $badge['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>{{-- /left --}}

            {{-- RIGHT --}}
            <div>

                {{-- Streak --}}
                <div class="card card-glow mb-16 reveal">
                    <div class="section-hd">
                        <div class="section-title">🔥 Streak</div>
                        @if ($student->streak >= 7)
                            <span class="badge bk">🌟 Week warrior!</span>
                        @elseif($student->streak >= 3)
                            <span class="badge bk">💪 Keep going!</span>
                        @endif
                    </div>
                    <div class="streak-display">
                        <div class="streak-flame">🔥</div>
                        <div class="streak-num">{{ $student->streak }}</div>
                        <div class="streak-label">day streak</div>
                        <div class="streak-dots">
                            @for ($d = 6; $d >= 0; $d--)
                                @php
                                    $active = $student->streak_last_date &&
                                        now()->subDays($d)->toDateString() <= $student->streak_last_date->toDateString() &&
                                        $d <= $student->streak - 1;
                                @endphp
                                <div class="streak-dot {{ $active ? 'active' : '' }}"
                                    title="{{ now()->subDays($d)->format('D') }}"></div>
                            @endfor
                        </div>
                    </div>
                </div>

                {{-- Leaderboard --}}
                <div class="card reveal reveal-delay-1">
                    <div class="section-hd">
                        <div class="section-title">🏆 Top Students</div>
                        <a href="#" class="section-action">Full board →</a>
                    </div>
                    @foreach ($leaderboard as $i => $lb)
                        <div class="lb-item">
                            <div class="lb-rank {{ $i === 0 ? 'gold-rank' : ($i === 1 ? 'silver-rank' : ($i === 2 ? 'bronze-rank' : '')) }}">
                                {{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : '#'.($i+1))) }}
                            </div>
                            <div class="lb-avatar">{{ mb_substr($lb['name'], 0, 1) }}</div>
                            <div style="flex:1;min-width:0">
                                <div class="lb-name">{{ $lb['name'] }}</div>
                                <div class="lb-level">Lv.{{ $lb['level'] }} · 🔥{{ $lb['streak'] }}d</div>
                            </div>
                            <div class="lb-xp">{{ number_format($lb['xp']) }}</div>
                        </div>
                    @endforeach
                </div>

            </div>{{-- /right --}}

        </div>{{-- /dash-grid --}}

    </div>{{-- /page --}}


    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script>
        /* ═══════════════════════════════════════════════════
           THREE.JS — Floating particles + orb inside hero banner
        ═══════════════════════════════════════════════════ */
        (function () {
            const canvas = document.getElementById('qmCanvas');
            const banner = document.getElementById('heroBanner');
            if (!canvas || !banner || typeof THREE === 'undefined') return;

            const cssOrb = document.getElementById('sphere3d');
            if (cssOrb) cssOrb.style.display = 'none';

            const W = () => banner.offsetWidth;
            const H = () => banner.offsetHeight;

            const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
            renderer.setSize(W(), H());
            renderer.setClearColor(0x000000, 0);

            const scene  = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(60, W() / H(), 0.1, 200);
            camera.position.z = 5;

            const COUNT = 120;
            const geo = new THREE.BufferGeometry();
            const pos = new Float32Array(COUNT * 3);
            const col = new Float32Array(COUNT * 3);

            const palette = [
                new THREE.Color('#7C5CFC'),
                new THREE.Color('#00D4FF'),
                new THREE.Color('#00E396'),
                new THREE.Color('#FFB800'),
                new THREE.Color('#FF6B9D'),
            ];

            for (let i = 0; i < COUNT; i++) {
                pos[i * 3]     = (Math.random() - 0.5) * 18;
                pos[i * 3 + 1] = (Math.random() - 0.5) * 7;
                pos[i * 3 + 2] = (Math.random() - 0.5) * 3;
                const c = palette[Math.floor(Math.random() * palette.length)];
                col[i * 3] = c.r; col[i * 3 + 1] = c.g; col[i * 3 + 2] = c.b;
            }

            geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
            geo.setAttribute('color',    new THREE.BufferAttribute(col, 3));

            const ptMat = new THREE.PointsMaterial({ size: 0.07, vertexColors: true, transparent: true, opacity: 0.85, sizeAttenuation: true });
            scene.add(new THREE.Points(geo, ptMat));

            const orbGeo = new THREE.IcosahedronGeometry(0.72, 1);
            const orbMat = new THREE.MeshPhongMaterial({ color: 0x7C5CFC, emissive: 0x3a1a9a, transparent: true, opacity: 0.65, shininess: 120 });
            const orb = new THREE.Mesh(orbGeo, orbMat);
            orb.position.set(4.8, 0, 0);
            scene.add(orb);

            const wireGeo = new THREE.IcosahedronGeometry(0.82, 1);
            const wireMat = new THREE.MeshBasicMaterial({ color: 0x9B7BFF, wireframe: true, transparent: true, opacity: 0.28 });
            const wire = new THREE.Mesh(wireGeo, wireMat);
            wire.position.copy(orb.position);
            scene.add(wire);

            const ringGeo = new THREE.TorusGeometry(1.15, 0.014, 8, 64);
            const ringMat = new THREE.MeshBasicMaterial({ color: 0x00D4FF, transparent: true, opacity: 0.22 });
            const ring = new THREE.Mesh(ringGeo, ringMat);
            ring.position.copy(orb.position);
            ring.rotation.x = Math.PI / 3;
            scene.add(ring);

            scene.add(new THREE.AmbientLight(0xffffff, 0.5));
            const pl1 = new THREE.PointLight(0x7C5CFC, 3, 12); pl1.position.set(3, 2, 3); scene.add(pl1);
            const pl2 = new THREE.PointLight(0x00D4FF, 2, 10); pl2.position.set(-4, -1, 2); scene.add(pl2);

            let mx = 0, my = 0;
            banner.addEventListener('mousemove', e => {
                const r = banner.getBoundingClientRect();
                mx = ((e.clientX - r.left) / r.width - 0.5) * 2;
                my = ((e.clientY - r.top) / r.height - 0.5) * 2;
            });
            banner.addEventListener('mouseleave', () => { mx = 0; my = 0; });

            let t = 0;
            const points = scene.children[0];
            (function animate() {
                requestAnimationFrame(animate);
                t += 0.01;
                points.rotation.y = t * 0.04 + mx * 0.05;
                points.rotation.x = my * 0.025;
                orb.rotation.x = t * 0.5; orb.rotation.y = t * 0.7;
                orb.position.y = Math.sin(t * 0.9) * 0.22;
                wire.rotation.x = -t * 0.4; wire.rotation.y = t * 0.65;
                wire.position.y = orb.position.y;
                ring.rotation.z = t * 0.25; ring.position.y = orb.position.y;
                pl1.position.x = Math.sin(t * 0.7) * 5;
                pl1.position.y = Math.cos(t * 0.5) * 2;
                renderer.render(scene, camera);
            })();

            window.addEventListener('resize', () => {
                camera.aspect = W() / H();
                camera.updateProjectionMatrix();
                renderer.setSize(W(), H());
            });
        })();


        /* ═══════════════════════════════════════════════════
           PROFILE SETUP MODAL
        ═══════════════════════════════════════════════════ */
        let currentStep   = 0;
        const totalSteps  = 3;
        let selectedEmoji    = '🧑‍🎓';
        let selectedSubjects = [];

        // Avatar picker
        document.querySelectorAll('.avatar-emoji-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.avatar-emoji-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                selectedEmoji = btn.dataset.emoji;
                document.getElementById('avatarPreview').textContent = selectedEmoji;
            });
        });

        // Subject tags
        document.querySelectorAll('.subject-tag').forEach(tag => {
            tag.addEventListener('click', () => {
                tag.classList.toggle('selected');
                const s = tag.dataset.subject;
                if (tag.classList.contains('selected')) {
                    selectedSubjects.push(s);
                } else {
                    selectedSubjects = selectedSubjects.filter(x => x !== s);
                }
            });
        });

        // Bio counter
        const bioField = document.getElementById('field_bio');
        if (bioField) {
            bioField.addEventListener('input', () => {
                document.getElementById('bioCounter').textContent = `${bioField.value.length} / 300`;
            });
        }

        function updateDots(step) {
            for (let i = 0; i < totalSteps; i++) {
                const dot = document.getElementById(`dot${i}`);
                if (!dot) continue;
                dot.className = 'step-dot';
                if (i < step)  dot.classList.add('done');
                if (i === step) dot.classList.add('active');
            }
            document.getElementById('stepCounter').textContent = `Step ${step + 1} of ${totalSteps}`;
            document.getElementById('prevBtn').style.display  = step > 0 ? '' : 'none';
            document.getElementById('nextBtn').textContent    = step === totalSteps - 1 ? "🎉 Let's Go!" : 'Continue →';
        }

        function validateStep(step) {
            let valid = true;
            const show = id => { const f = document.getElementById(id); if (f) f.style.display = 'block'; };
            const hide = id => { const f = document.getElementById(id); if (f) f.style.display = 'none'; };

            if (step === 0) {
                const name = document.getElementById('field_display_name')?.value.trim();
                const age  = parseInt(document.getElementById('field_age')?.value);
                const cls  = document.getElementById('field_class')?.value;
                if (!name)                    { show('err_display_name'); valid = false; } else hide('err_display_name');
                if (!age || age < 5 || age > 30) { show('err_age');  valid = false; } else hide('err_age');
                if (!cls)                     { show('err_class'); valid = false; } else hide('err_class');
            }
            if (step === 1) {
                const school = document.getElementById('field_school_name')?.value.trim();
                if (!school) { show('err_school_name'); valid = false; } else hide('err_school_name');
            }
            return valid;
        }

        function modalNext() {
            if (!validateStep(currentStep)) return;
            if (currentStep === totalSteps - 1) {
                submitProfile();
                return;
            }
            document.getElementById(`step${currentStep}`).classList.remove('active');
            currentStep++;
            document.getElementById(`step${currentStep}`).classList.add('active');
            updateDots(currentStep);
        }

        function modalPrev() {
            if (currentStep === 0) return;
            document.getElementById(`step${currentStep}`).classList.remove('active');
            currentStep--;
            document.getElementById(`step${currentStep}`).classList.add('active');
            updateDots(currentStep);
        }

        function submitProfile() {
            const nextBtn = document.getElementById('nextBtn');
            nextBtn.innerHTML = '<span class="spinner"></span> Saving…';
            nextBtn.disabled  = true;

            function resetBtn() {
                nextBtn.innerHTML = "🎉 Let's Go!";
                nextBtn.disabled  = false;
            }

            try {
                const fd = new FormData();
                fd.append('display_name', document.getElementById('field_display_name')?.value.trim() || '');
                fd.append('age',          document.getElementById('field_age')?.value || '');
                fd.append('class',        document.getElementById('field_class')?.value || '');
                fd.append('school_name',  document.getElementById('field_school_name')?.value.trim() || '');
                fd.append('bio',          document.getElementById('field_bio')?.value.trim() || '');
                fd.append('avatar',       selectedEmoji || '🧑‍🎓');  // emoji string, not file

                selectedSubjects.forEach(s => { if (s) fd.append('subjects_interest[]', s); });

                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!csrf) { qmToast('error', 'Security token missing. Refresh page.'); resetBtn(); return; }
                fd.append('_token', csrf);

                const url = "{{ route('student.profile.save') }}";

                fetch(url, {
                    method:  'POST',
                    body:    fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(err => { throw { status: res.status, data: err }; });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        if (typeof qmConfetti === 'function') qmConfetti();
                        document.getElementById('setupModal')?.classList.add('hidden');
                        if (typeof qmToast === 'function') qmToast('success', '🎉 Profile saved! Welcome to QuizMind!');
                        return;
                    }
                    if (data.errors) {
                        Object.values(data.errors).forEach(e => { if (e?.[0] && typeof qmToast === 'function') qmToast('error', e[0]); });
                    } else {
                        if (typeof qmToast === 'function') qmToast('error', data.message || 'Something went wrong.');
                    }
                    resetBtn();
                })
                .catch(err => {
                    console.error('❌ Request failed:', err);
                    if (typeof qmToast === 'function') {
                        if      (err.status === 419) qmToast('error', 'Session expired. Refresh page.');
                        else if (err.status === 422 && err.data?.errors) Object.values(err.data.errors).forEach(e => qmToast('error', e[0]));
                        else if (err.status === 500) qmToast('error', 'Server error. Check backend.');
                        else                         qmToast('error', 'Network error. Try again.');
                    }
                    resetBtn();
                });

            } catch (e) {
                console.error('❌ JS Error:', e);
                if (typeof qmToast === 'function') qmToast('error', 'Unexpected error occurred.');
                resetBtn();
            }
        }

        // Init dots
        updateDots(0);

        /* ── XP bar entrance animation ── */
        window.addEventListener('load', () => {
            const bar = document.getElementById('xpBar');
            if (bar) {
                const target = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => { bar.style.width = target; }, 400);
            }
        });

        /* ── Scroll reveal ── */
        document.querySelectorAll('.reveal').forEach(el => {
            new IntersectionObserver(entries => {
                entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
            }, { threshold: 0.1 }).observe(el);
        });
    </script>

@endsection