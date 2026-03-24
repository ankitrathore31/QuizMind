{{-- resources/views/student/quiz/aiquiz.blade.php --}}

@extends('student.layout.master')

@section('title', 'AI Quiz Generator')

@section('content')

    {{-- ── Three.js Canvas Overlay (shared for all animations) ── --}}
    <canvas id="threeCanvas" style="position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:9998;opacity:0;transition:opacity .3s"></canvas>

    <div class="play-screen" id="playScreen">
        <div class="orb o1"></div>
        <div class="orb o2"></div>
        <div class="play-inner" id="playInner"></div>
    </div>

    <div class="modal-overlay hidden" id="battleModal">
        <div class="modal-panel modal-sm">
            <div class="text-center mb-16">
                <span style="font-size:2.5rem;display:block;margin-bottom:8px" class="anim-float">⚔️</span>
                <div class="modal-title" style="font-size:1.15rem">Choose Battle Mode</div>
                <p class="modal-sub" style="font-size:.82rem" id="battleSubInfo">
                    {{ count($myQuizzes) > 0 ? 'Select how you want to battle' : 'Generate questions first, then select a mode' }}
                </p>
            </div>

            <div class="flex-col gap-8 mb-16" id="battleModes">
                <button class="mode-card" onclick="selectBattleMode('1v1', this)">
                    <span class="m-icon">⚔️</span>
                    <div>
                        <div class="m-title">1v1 Battle</div>
                        <div class="m-desc">Head-to-head with one opponent</div>
                    </div>
                    <span class="m-arrow">→</span>
                </button>
                <button class="mode-card" onclick="selectBattleMode('group', this)">
                    <span class="m-icon">👥</span>
                    <div>
                        <div class="m-title">Group Battle</div>
                        <div class="m-desc">2–10 players compete live</div>
                    </div>
                    <span class="m-arrow">→</span>
                </button>
                <button class="mode-card" onclick="selectBattleMode('team', this)">
                    <span class="m-icon">🏆</span>
                    <div>
                        <div class="m-title">Team vs Team</div>
                        <div class="m-desc">Two teams compete against each other</div>
                    </div>
                    <span class="m-arrow">→</span>
                </button>
                @if (auth()->user()->role === 'institution')
                    <button class="mode-card" onclick="selectBattleMode('school', this)">
                        <span class="m-icon">🏫</span>
                        <div>
                            <div class="m-title">School vs School</div>
                            <div class="m-desc">Up to 100+ players per school</div>
                        </div>
                        <span class="m-arrow">→</span>
                    </button>
                @endif
            </div>

            <div id="teamNameInputs" style="display:none;margin-bottom:16px">
                <div style="height:1px;background:var(--border);margin-bottom:14px"></div>
                <p class="form-label mb-8">Team Names</p>
                <div class="grid-2">
                    <input type="text" id="teamAName" class="form-input" placeholder="Team A name…">
                    <input type="text" id="teamBName" class="form-input" placeholder="Team B name…">
                </div>
            </div>

            <div class="flex gap-10">
                <button class="btn btn-ghost w-full" onclick="closeBattleModal()">Cancel</button>
                <button class="btn btn-grad w-full" id="battleConfirmBtn" onclick="confirmBattle()" disabled>
                    Confirm Battle →
                </button>
            </div>
        </div>
    </div>

    <div class="flex mb-24 anim-fade" style="justify-content:space-between;flex-wrap:wrap;gap:12px;align-items:flex-start">
        <div>
            <h1 style="font-family:var(--fh);font-size:1.7rem;font-weight:800;margin-bottom:4px">
                🤖 AI Quiz Generator
            </h1>
            <p class="text-muted" style="font-size:.86rem">
                Generate from topic, PDF, or image — then play solo or jump into a battle
            </p>
        </div>
        <div class="flex gap-8" style="flex-wrap:wrap">
            <button class="btn btn-ghost btn-sm" onclick="switchTab('manual', document.querySelectorAll('.tab')[4])">
                ✍️ Manual MCQ
            </button>
            <button class="btn btn-ghost btn-sm" onclick="loadSuggestions()">
                💡 AI Tips
            </button>
        </div>
    </div>

    <div id="tipBanner"
        style="display:none;background:linear-gradient(135deg,rgba(0,212,255,0.08),rgba(124,92,252,0.06));border:1px solid rgba(0,212,255,0.2);border-radius:var(--radius-sm);padding:14px 18px;margin-bottom:20px;font-size:.86rem"
        class="anim-fade">
        <span id="tipText"></span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start" id="mainGrid">

        {{-- ── LEFT: Generator tabs ── --}}
        <div>

            <div class="tabs mb-20">
                <button class="tab active" onclick="switchTab('topic', this)">📝 By Topic</button>
                <button class="tab" onclick="switchTab('pdf',      this)">📄 From PDF</button>
                <button class="tab" onclick="switchTab('image',    this)">🖼️ From Image</button>
                <button class="tab" onclick="switchTab('standard', this)">📚 Standard</button>
                <button class="tab" onclick="switchTab('manual',   this)">✍️ Manual</button>
            </div>

            {{-- ── Topic Panel (no subject field) ── --}}
            <div class="tab-panel card" id="panel-topic">
                <h3 style="font-family:var(--fh);font-weight:800;font-size:.95rem" class="mb-16">📝 Generate by Topic</h3>
                <div class="form-float">
                    <input type="text" id="topicInput" placeholder=" " maxlength="200">
                    <label>Topic (e.g. "Newton's Laws", "Photosynthesis")</label>
                </div>
                <div class="grid-2" style="gap:10px">
                    <div class="form-float" style="margin-bottom:0">
                        <input type="number" id="countTopic" placeholder=" " value="10" min="3" max="20">
                        <label>Questions (3–20)</label>
                    </div>
                    <div class="form-float" style="margin-bottom:0">
                        <select id="diffTopic">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate" selected>Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                        <label>Difficulty</label>
                    </div>
                </div>
                <button class="btn btn-grad w-full mt-16" id="genTopicBtn" onclick="genTopic()">
                    ✨ Generate Questions
                </button>
            </div>

            {{-- ── PDF Panel ── --}}
            <div class="tab-panel card" id="panel-pdf" style="display:none">
                <h3 style="font-family:var(--fh);font-weight:800;font-size:.95rem" class="mb-16">📄 Generate from PDF</h3>
                <div class="drop-zone mb-12" id="pdfZone"
                    onclick="document.getElementById('pdfInput').click()"
                    ondragover="event.preventDefault();this.classList.add('over')"
                    ondragleave="this.classList.remove('over')"
                    ondrop="dropPdf(event)">
                    <input type="file" id="pdfInput" accept=".pdf" style="display:none" onchange="selectPdf(this)">
                    <div id="pdfZoneContent">
                        <div style="font-size:2.5rem;margin-bottom:8px">📁</div>
                        <div style="font-weight:600;font-size:.88rem">Drop PDF or click to browse</div>
                        <p class="text-muted" style="font-size:.76rem;margin-top:4px">NCERT, textbooks, notes • max 10MB</p>
                    </div>
                </div>
                <div class="grid-2" style="gap:10px;margin-bottom:12px">
                    <div class="form-float" style="margin-bottom:0">
                        <input type="number" id="countPdf" placeholder=" " value="10" min="3" max="20">
                        <label>Questions</label>
                    </div>
                    <div class="form-float" style="margin-bottom:0">
                        <select id="diffPdf">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate" selected>Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                        <label>Difficulty</label>
                    </div>
                </div>
                <button class="btn btn-grad w-full" id="genPdfBtn" onclick="genPdf()">
                    📄 Generate from PDF
                </button>
            </div>

            {{-- ── Image Panel ── --}}
            <div class="tab-panel card" id="panel-image" style="display:none">
                <h3 style="font-family:var(--fh);font-weight:800;font-size:.95rem" class="mb-4">🖼️ From Educational Diagram</h3>
                <p class="text-muted mb-14" style="font-size:.78rem">Educational diagrams only — heart, cell, circuit, map, etc.</p>
                <div class="drop-zone mb-12" id="imgZone" onclick="document.getElementById('imgInput').click()">
                    <input type="file" id="imgInput" accept="image/*" style="display:none" onchange="selectImg(this)">
                    <div id="imgZoneContent">
                        <div style="font-size:2.5rem;margin-bottom:8px">🖼️</div>
                        <div style="font-weight:600;font-size:.88rem">Drop image or click to browse</div>
                        <p class="text-muted" style="font-size:.76rem;margin-top:4px">JPG, PNG, WebP • max 5MB</p>
                    </div>
                </div>
                <div id="imgErrBox"
                    style="display:none;background:rgba(255,77,106,0.08);border:1px solid rgba(255,77,106,0.2);border-radius:var(--radius-xs);padding:9px 12px;margin-bottom:10px;font-size:.8rem;color:var(--red)">
                </div>
                <div class="form-float mb-12">
                    <input type="number" id="countImg" placeholder=" " value="5" min="3" max="10">
                    <label>Questions (3–10)</label>
                </div>
                <button class="btn btn-grad w-full" id="genImgBtn" onclick="genImg()">
                    🖼️ Analyze &amp; Generate
                </button>
            </div>

            {{-- ── Standard Panel ── --}}
            <div class="tab-panel card" id="panel-standard" style="display:none">
                <h3 style="font-family:var(--fh);font-weight:800;font-size:.95rem" class="mb-16">📚 Standard Curriculum Quiz</h3>
                <div class="form-float">
                    <select id="classInput">
                        <option value="">Select class…</option>
                        @foreach (['5','6','7','8','9','10','11','12','UG-1','UG-2','UG-3','UG-4'] as $c)
                            <option value="{{ $c }}">Class {{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-float">
                    <select id="subjectStd">
                        <option value="">Select subject…</option>
                        @foreach (['Mathematics','Physics','Chemistry','Biology','History','Geography','English','Hindi','Computer Science','Economics','Political Science','Psychology'] as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid-2" style="gap:10px">
                    <div class="form-float" style="margin-bottom:0">
                        <input type="number" id="countStd" placeholder=" " value="10" min="3" max="20">
                        <label>Questions</label>
                    </div>
                    <div class="form-float" style="margin-bottom:0">
                        <select id="diffStd">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate" selected>Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                        <label>Difficulty</label>
                    </div>
                </div>
                <button class="btn btn-grad w-full mt-16" id="genStdBtn" onclick="genStandard()">
                    📚 Generate Standard Quiz
                </button>
            </div>

            {{-- ── Manual MCQ Panel ── --}}
            <div class="tab-panel card" id="panel-manual" style="display:none">
                <h3 style="font-family:var(--fh);font-weight:800;font-size:.95rem" class="mb-16">✍️ Build MCQ Manually</h3>
                <div class="form-float">
                    <input type="text" id="manualTitle" placeholder=" " maxlength="200">
                    <label>Quiz Title *</label>
                </div>
                <div class="grid-2" style="gap:10px;margin-bottom:16px">
                    <div class="form-float" style="margin-bottom:0">
                        <input type="text" id="manualSubject" placeholder=" ">
                        <label>Subject</label>
                    </div>
                    <div class="form-float" style="margin-bottom:0">
                        <select id="manualDiff">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate" selected>Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                        <label>Difficulty</label>
                    </div>
                </div>

                <div id="mcqList"></div>

                <div class="flex gap-8 mt-4">
                    <button class="btn btn-ghost btn-sm" onclick="addMcqItem()">+ Add Question</button>
                    <button class="btn btn-grad  btn-sm" id="saveManualBtn" onclick="saveManual()">💾 Save Quiz</button>
                </div>
                <p id="manualQCount" class="text-muted mt-8" style="font-size:.74rem">0 questions added</p>
            </div>

        </div>{{-- /left --}}

        {{-- ── RIGHT: Preview + My Quizzes ── --}}
        <div>

            <div id="questionsPreviewWrap" style="display:none">
                <div class="card mb-16">
                    <div class="section-hd">
                        <div class="section-title">✅ Questions Ready</div>
                        <div class="flex gap-6">
                            <span class="badge bp" id="qCountBadge">0</span>
                            <span class="badge bc" id="qSourceBadge">AI</span>
                        </div>
                    </div>
                    <div class="flex-col gap-8 mb-16">
                        <button class="btn btn-grad  w-full" onclick="startSolo()">⚡ Play Solo</button>
                        <button class="btn btn-ghost w-full" onclick="openBattleModal()">⚔️ Battle with this Quiz</button>
                    </div>
                    <div id="questionsList"
                        style="max-height:380px;overflow-y:auto;display:flex;flex-direction:column;gap:8px"></div>
                </div>
            </div>

            <div id="emptyPreview" class="card text-center" style="padding:40px 24px">
                <div style="font-size:3rem;margin-bottom:12px" class="anim-float">🤖</div>
                <div style="font-family:var(--fh);font-weight:700;font-size:.95rem;margin-bottom:8px">Ready to Generate</div>
                <p class="text-muted" style="font-size:.82rem;line-height:1.6">
                    Generate from a topic, PDF, image or curriculum — then practice solo or battle
                </p>
            </div>

            @if ($myQuizzes->count())
                <div class="card mt-16">
                    <div class="section-hd">
                        <div class="section-title">📂 My Quizzes</div>
                        <span class="text-muted" style="font-size:.72rem">{{ $myQuizzes->count() }} saved</span>
                    </div>
                    <div id="myQuizzesList">
                        @foreach ($myQuizzes as $q)
                            <div class="my-quiz-item" id="myquiz-{{ $q->id }}">
                                <div class="mq-icon">
                                    {{ $q->source === 'pdf' ? '📄' : ($q->source === 'image' ? '🖼️' : ($q->source === 'manual' ? '✍️' : '🤖')) }}
                                </div>
                                <div style="flex:1;min-width:0">
                                    <div class="mq-title">{{ $q->title ?? 'Untitled Quiz' }}</div>
                                    <div class="mq-meta">{{ count($q->questions ?? []) }} Q · {{ ucfirst($q->difficulty) }}</div>
                                </div>
                                <button class="mq-play"
                                    onclick='loadExistingQuiz({{ $q->id }}, {{ json_encode($q->questions) }})'>
                                    ▶ Play
                                </button>
                                <button onclick="deleteQuiz({{ $q->id }})"
                                    style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:.8rem;margin-left:4px;padding:2px 6px;border-radius:4px;transition:color .2s"
                                    title="Delete"
                                    onmouseover="this.style.color='var(--red)'"
                                    onmouseout="this.style.color='var(--muted)'">🗑</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>{{-- /right --}}

    </div>{{-- /grid --}}

    <!-- Three.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    <script>
        const LETTERS = ['A', 'B', 'C', 'D'];

        // ── CSRF token helper ──────────────────────
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        const ROUTES = {
            genTopic:   '{{ route('student.quiz.generate.topic') }}',
            genPdf:     '{{ route('student.quiz.generate.pdf') }}',
            genImage:   '{{ route('student.quiz.generate.image') }}',
            genStandard:'{{ route('student.quiz.generate.standard') }}',
            saveManual: '{{ route('student.quiz.manual.save') }}',
            submitSolo: '{{ route('student.quiz.submit.solo') }}',
            suggestions:'{{ route('student.quiz.suggestions') }}',
            deleteQuiz: '{{ url('student/quiz') }}',
        };

        // ── State ──────────────────────────────────
        let questions      = [];
        let currentQuizId  = null;
        let selectedMode   = null;
        let pdfFile        = null;
        let imgFile        = null;
        let mcqItems       = [];
        let mcqCounter     = 0;
        let playState      = {};

        // ══════════════════════════════════════════════════════════════
        // THREE.JS ANIMATION ENGINE
        // ══════════════════════════════════════════════════════════════
        const ThreeAnim = (() => {
            let renderer, scene, camera, animId, particles, clock;
            let activeAnimation = null;
            const canvas = document.getElementById('threeCanvas');

            function init() {
                if (renderer) return;
                renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
                renderer.setPixelRatio(window.devicePixelRatio);
                renderer.setSize(window.innerWidth, window.innerHeight);
                renderer.setClearColor(0x000000, 0);

                scene  = new THREE.Scene();
                camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 1000);
                camera.position.z = 5;
                clock  = new THREE.Clock();

                window.addEventListener('resize', () => {
                    renderer.setSize(window.innerWidth, window.innerHeight);
                    camera.aspect = window.innerWidth / window.innerHeight;
                    camera.updateProjectionMatrix();
                });
            }

            function clearScene() {
                if (!scene) return;
                while (scene.children.length) {
                    const obj = scene.children[0];
                    if (obj.geometry) obj.geometry.dispose();
                    if (obj.material) {
                        if (Array.isArray(obj.material)) obj.material.forEach(m => m.dispose());
                        else obj.material.dispose();
                    }
                    scene.remove(obj);
                }
            }

            function showCanvas() { canvas.style.opacity = '1'; }
            function hideCanvas() { canvas.style.opacity = '0'; }

            function stop() {
                cancelAnimationFrame(animId);
                clearScene();
                hideCanvas();
                activeAnimation = null;
            }

            // ── Animation 1: PDF → MCQ (page shred + question particles burst) ──
            function playPdfToMcq(questionCount) {
                init();
                clearScene();
                showCanvas();
                activeAnimation = 'pdf';

                const totalDuration = 3.0;
                clock.start();

                // ── Phase 1 objects: PDF page shards ──
                const shardCount = 40;
                const shards     = [];
                const shardGeo   = new THREE.PlaneGeometry(0.35, 0.45);

                for (let i = 0; i < shardCount; i++) {
                    const mat = new THREE.MeshBasicMaterial({
                        color:       new THREE.Color().setHSL(0.62 + Math.random() * 0.08, 0.6, 0.7),
                        side:        THREE.DoubleSide,
                        transparent: true,
                        opacity:     0.9,
                    });
                    const mesh = new THREE.Mesh(shardGeo, mat);
                    mesh.position.set(
                        (Math.random() - 0.5) * 0.5,
                        (Math.random() - 0.5) * 0.5,
                        (Math.random() - 0.5) * 0.2
                    );
                    mesh.rotation.z = (Math.random() - 0.5) * 0.4;

                    // Explosion velocity
                    mesh.userData.vx  = (Math.random() - 0.5) * 4.5;
                    mesh.userData.vy  = (Math.random() - 0.5) * 4.5;
                    mesh.userData.vz  = (Math.random() - 0.5) * 2.0;
                    mesh.userData.vRz = (Math.random() - 0.5) * 6.0;
                    shards.push(mesh);
                    scene.add(mesh);
                }

                // ── Phase 2 objects: MCQ question dots ──
                const mcqCount  = Math.min(questionCount * 4, 60);
                const mcqColors = [0x7c5cfc, 0x00d4ff, 0x00e396, 0xffa500];
                const mcqMeshes = [];

                for (let i = 0; i < mcqCount; i++) {
                    const geo = new THREE.SphereGeometry(0.06 + Math.random() * 0.05, 8, 8);
                    const mat = new THREE.MeshBasicMaterial({
                        color:       mcqColors[i % mcqColors.length],
                        transparent: true,
                        opacity:     0,
                    });
                    const mesh = new THREE.Mesh(geo, mat);
                    // Start clustered at centre
                    mesh.position.set(
                        (Math.random() - 0.5) * 0.3,
                        (Math.random() - 0.5) * 0.3,
                        0
                    );
                    // Target spread position (arranged in a loose grid)
                    const col = i % 8;
                    const row = Math.floor(i / 8);
                    mesh.userData.tx  = (col - 3.5) * 0.55;
                    mesh.userData.ty  = (2 - row) * 0.55;
                    mesh.userData.tz  = 0;
                    mesh.userData.delay = 1.2 + (i / mcqCount) * 0.8;
                    mcqMeshes.push(mesh);
                    scene.add(mesh);
                }

                // Add ambient light glow (point light)
                const light = new THREE.PointLight(0x7c5cfc, 2, 10);
                light.position.set(0, 0, 3);
                scene.add(light);

                function animate() {
                    animId = requestAnimationFrame(animate);
                    const t = clock.getElapsedTime();

                    // Phase 1: shards fly outward & fade (0 → 1.5s)
                    shards.forEach(s => {
                        const progress = Math.min(t / 1.5, 1);
                        s.position.x  += s.userData.vx * 0.016;
                        s.position.y  += s.userData.vy * 0.016;
                        s.position.z  += s.userData.vz * 0.016;
                        s.rotation.z  += s.userData.vRz * 0.016;
                        s.material.opacity = 0.9 * (1 - progress);
                        s.scale.setScalar(1 - progress * 0.5);
                    });

                    // Phase 2: MCQ dots emerge & fly to grid (1.2s → 3.0s)
                    mcqMeshes.forEach(m => {
                        const elapsed = t - m.userData.delay;
                        if (elapsed < 0) return;
                        const p = Math.min(elapsed / 0.6, 1);
                        // Ease out cubic
                        const ease = 1 - Math.pow(1 - p, 3);
                        m.position.x    = m.position.x + (m.userData.tx - m.position.x) * ease * 0.15;
                        m.position.y    = m.position.y + (m.userData.ty - m.position.y) * ease * 0.15;
                        m.material.opacity = ease;
                        // Pulse scale
                        m.scale.setScalar(1 + Math.sin(t * 4 + m.userData.delay) * 0.1);
                    });

                    // Rotate scene gently
                    scene.rotation.y = Math.sin(t * 0.3) * 0.15;

                    renderer.render(scene, camera);

                    if (t > totalDuration) stop();
                }
                animate();
            }

            // ── Animation 2: Image → MCQ (pixel shatter explosion) ──
            function playImageExplode(imgSrc, questionCount) {
                init();
                clearScene();
                showCanvas();
                activeAnimation = 'image';

                clock.start();
                const totalDuration = 3.5;

                // Create a texture from the uploaded image
                const loader  = new THREE.TextureLoader();
                const texture = loader.load(imgSrc);

                // ── Central image plane (will shatter) ──
                const imgGeo  = new THREE.PlaneGeometry(2.4, 1.8, 12, 9);
                const imgMat  = new THREE.MeshBasicMaterial({ map: texture, side: THREE.DoubleSide, transparent: true });
                const imgMesh = new THREE.Mesh(imgGeo, imgMat);
                scene.add(imgMesh);

                // ── Pre-compute shard velocities from vertex positions ──
                const posAttr  = imgGeo.attributes.position;
                const shardVel = [];
                for (let i = 0; i < posAttr.count; i++) {
                    const x  = posAttr.getX(i);
                    const y  = posAttr.getY(i);
                    const d  = Math.sqrt(x * x + y * y) + 0.001;
                    shardVel.push({
                        vx: (x / d) * (2 + Math.random() * 3),
                        vy: (y / d) * (2 + Math.random() * 3),
                        vz: (Math.random() - 0.5) * 2,
                    });
                }

                // ── MCQ answer cards (coloured quads that assemble after explosion) ──
                const cardColors  = [0x7c5cfc, 0x00d4ff, 0x00e396, 0xff6b6b];
                const cardLetters = ['A', 'B', 'C', 'D'];
                const cards       = [];
                const cardCount   = Math.min(questionCount, 5) * 4;

                for (let i = 0; i < cardCount; i++) {
                    const geo = new THREE.PlaneGeometry(0.42, 0.22);
                    const mat = new THREE.MeshBasicMaterial({
                        color:       cardColors[i % 4],
                        transparent: true,
                        opacity:     0,
                        side:        THREE.DoubleSide,
                    });
                    const mesh = new THREE.Mesh(geo, mat);

                    // Scatter start positions
                    mesh.position.set(
                        (Math.random() - 0.5) * 6,
                        (Math.random() - 0.5) * 4,
                        (Math.random() - 0.5) * 2
                    );

                    // Target: neat rows
                    const col = i % 4;
                    const row = Math.floor(i / 4);
                    mesh.userData.tx    = (col - 1.5) * 0.55;
                    mesh.userData.ty    = (1.5 - row) * 0.32;
                    mesh.userData.tz    = 0;
                    mesh.userData.delay = 1.4 + (i / cardCount) * 1.0;
                    cards.push(mesh);
                    scene.add(mesh);
                }

                // ── Sparkle particles ──
                const sparkCount = 200;
                const sparkGeo   = new THREE.BufferGeometry();
                const sparkPos   = new Float32Array(sparkCount * 3);
                const sparkVel   = [];

                for (let i = 0; i < sparkCount; i++) {
                    sparkPos[i * 3]     = (Math.random() - 0.5) * 0.2;
                    sparkPos[i * 3 + 1] = (Math.random() - 0.5) * 0.2;
                    sparkPos[i * 3 + 2] = 0;
                    const angle = Math.random() * Math.PI * 2;
                    const speed = 0.5 + Math.random() * 3;
                    sparkVel.push({
                        vx: Math.cos(angle) * speed,
                        vy: Math.sin(angle) * speed,
                        vz: (Math.random() - 0.5) * 1.5,
                    });
                }
                sparkGeo.setAttribute('position', new THREE.BufferAttribute(sparkPos, 3));
                const sparkMat = new THREE.PointsMaterial({
                    color: 0xffffff, size: 0.03, transparent: true, opacity: 0.85,
                });
                const sparkPoints = new THREE.Points(sparkGeo, sparkMat);
                scene.add(sparkPoints);

                function animate() {
                    animId = requestAnimationFrame(animate);
                    const t  = clock.getElapsedTime();
                    const dt = clock.getDelta() * 0; // manual delta via t

                    // Phase 1 (0 → 1.2s): image pulses, then shatters
                    if (t < 1.2) {
                        imgMesh.material.opacity = 1;
                        imgMesh.scale.setScalar(1 + Math.sin(t * 8) * 0.02);
                    } else {
                        // Shatter: displace vertices outward
                        const shatterT = Math.min((t - 1.2) / 0.5, 1);
                        for (let i = 0; i < posAttr.count; i++) {
                            const v = shardVel[i];
                            posAttr.setXYZ(
                                i,
                                posAttr.getX(i) + v.vx * shatterT * 0.04,
                                posAttr.getY(i) + v.vy * shatterT * 0.04,
                                posAttr.getZ(i) + v.vz * shatterT * 0.04,
                            );
                        }
                        posAttr.needsUpdate       = true;
                        imgMesh.material.opacity  = 1 - shatterT;
                        imgMesh.rotation.z       += 0.005 * shatterT;
                    }

                    // Sparkles: fly outward
                    const sArr = sparkGeo.attributes.position.array;
                    for (let i = 0; i < sparkCount; i++) {
                        sArr[i * 3]     += sparkVel[i].vx * 0.014;
                        sArr[i * 3 + 1] += sparkVel[i].vy * 0.014;
                        sArr[i * 3 + 2] += sparkVel[i].vz * 0.014;
                    }
                    sparkGeo.attributes.position.needsUpdate = true;
                    sparkMat.opacity = Math.max(0, 0.85 - t * 0.3);

                    // Phase 2 (1.4s+): MCQ cards fly in and assemble
                    cards.forEach(c => {
                        const elapsed = t - c.userData.delay;
                        if (elapsed < 0) return;
                        const p    = Math.min(elapsed / 0.5, 1);
                        const ease = 1 - Math.pow(1 - p, 3);
                        c.position.x    = c.position.x + (c.userData.tx - c.position.x) * ease * 0.14;
                        c.position.y    = c.position.y + (c.userData.ty - c.position.y) * ease * 0.14;
                        c.position.z    = c.position.z + (c.userData.tz - c.position.z) * ease * 0.14;
                        c.material.opacity = ease;
                        c.scale.setScalar(1 + Math.sin(t * 3 + c.userData.delay) * 0.05);
                    });

                    scene.rotation.y = Math.sin(t * 0.25) * 0.12;
                    renderer.render(scene, camera);
                    if (t > totalDuration) stop();
                }
                animate();
            }

            // ── Animation 3: Generic topic/standard star-burst ──
            function playTopicBurst(count) {
                init();
                clearScene();
                showCanvas();
                activeAnimation = 'topic';

                clock.start();
                const totalDuration = 2.2;

                const colors   = [0x7c5cfc, 0x00d4ff, 0x00e396, 0xffa500, 0xff6b6b];
                const dotCount = Math.min(count * 5, 80);
                const dots     = [];

                for (let i = 0; i < dotCount; i++) {
                    const angle  = (i / dotCount) * Math.PI * 2;
                    const radius = 0.1 + Math.random() * 0.2;
                    const geo    = new THREE.SphereGeometry(0.05 + Math.random() * 0.04, 6, 6);
                    const mat    = new THREE.MeshBasicMaterial({
                        color:       colors[i % colors.length],
                        transparent: true,
                        opacity:     1,
                    });
                    const mesh = new THREE.Mesh(geo, mat);
                    mesh.position.set(Math.cos(angle) * radius, Math.sin(angle) * radius, 0);

                    const speed     = 1.5 + Math.random() * 3;
                    mesh.userData.vx = Math.cos(angle) * speed;
                    mesh.userData.vy = Math.sin(angle) * speed;
                    mesh.userData.vz = (Math.random() - 0.5) * 1.5;

                    dots.push(mesh);
                    scene.add(mesh);
                }

                function animate() {
                    animId = requestAnimationFrame(animate);
                    const t = clock.getElapsedTime();

                    dots.forEach(d => {
                        d.position.x      += d.userData.vx * 0.016;
                        d.position.y      += d.userData.vy * 0.016;
                        d.position.z      += d.userData.vz * 0.016;
                        d.material.opacity = Math.max(0, 1 - t / totalDuration);
                        d.scale.setScalar(1 + t * 0.5);
                    });

                    scene.rotation.z += 0.008;
                    renderer.render(scene, camera);
                    if (t > totalDuration) stop();
                }
                animate();
            }

            return { playPdfToMcq, playImageExplode, playTopicBurst, stop };
        })();
        // ══════════════════════════════════════════════════════════════

        // ── HTTP helpers ───────────────────────────
        async function qmPost(url, data) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(data),
            });
            return res.json();
        }

        async function qmPostForm(url, formData) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });
            return res.json();
        }

        async function qmDelete(url) {
            const res = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            return res.json();
        }

        // ── UI helpers ─────────────────────────────
        function qmEsc(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function qmToast(type, msg) {
            if (typeof window.showToast === 'function') {
                window.showToast(type, msg);
            } else {
                const colors = { success: '#22c55e', error: '#ef4444', info: '#3b82f6' };
                const t = document.createElement('div');
                t.textContent = msg;
                t.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;padding:12px 20px;border-radius:10px;background:${colors[type]||'#333'};color:#fff;font-size:.86rem;font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,.2);transition:opacity .4s`;
                document.body.appendChild(t);
                setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 400); }, 3000);
            }
        }

        function qmBtnLoad(id, loading, originalText) {
            const btn = document.getElementById(id);
            if (!btn) return;
            btn.disabled = loading;
            btn.textContent = loading ? '⏳ Please wait…' : originalText;
        }

        function qmConfetti() {
            if (typeof window.confetti === 'function') window.confetti();
        }

        // ── Tab Switch ──────────────────────────────
        function switchTab(name, btn) {
            document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById('panel-' + name).style.display = '';
            btn.classList.add('active');
        }

        // ── After generation ────────────────────────
        function afterGen(qs, quizId) {
            questions     = qs;
            currentQuizId = quizId;
            renderPreview();
            qmToast('success', `${qs.length} questions ready! 🎉`);
        }

        function renderPreview() {
            if (!questions.length) {
                document.getElementById('questionsPreviewWrap').style.display = 'none';
                document.getElementById('emptyPreview').style.display = '';
                return;
            }
            document.getElementById('questionsPreviewWrap').style.display = '';
            document.getElementById('emptyPreview').style.display = 'none';
            document.getElementById('qCountBadge').textContent = questions.length + ' Qs';

            const src = questions[0]?.source || 'ai';
            const srcLabel = {
                topic:    '🤖 AI',
                pdf:      '📄 PDF',
                image:    '🖼️ IMG',
                manual:   '✍️ Manual',
                standard: '📚 Std',
            }[src] || '🤖 AI';
            document.getElementById('qSourceBadge').textContent = srcLabel;

            document.getElementById('questionsList').innerHTML = questions.map((q, i) => `
                <div class="gen-q anim-fade" style="animation-delay:${i * .04}s">
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                        <span class="badge bp">Q${i + 1}</span>
                        <span class="badge bc">${srcLabel}</span>
                    </div>
                    <p style="font-size:.82rem;font-weight:500;margin-bottom:8px;line-height:1.55">${qmEsc(q.question)}</p>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px">
                        ${q.options.map((opt, j) => `
                            <div class="q-opt${j === q.answer ? ' correct-opt' : ''}">
                                ${LETTERS[j]}) ${j === q.answer ? '✓ ' : ''}${qmEsc(opt)}
                            </div>`).join('')}
                    </div>
                    ${q.explanation ? `<p style="font-size:.7rem;color:var(--muted);margin-top:6px;padding-top:6px;border-top:1px solid var(--border2)">💡 ${qmEsc(q.explanation)}</p>` : ''}
                </div>
            `).join('');
        }

        // ── Topic ───────────────────────────────────
        async function genTopic() {
            const topic = document.getElementById('topicInput').value.trim();
            if (!topic) { qmToast('error', 'Enter a topic'); return; }

            qmBtnLoad('genTopicBtn', true, '✨ Generate Questions');
            try {
                const d = await qmPost(ROUTES.genTopic, {
                    topic,
                    count:      +document.getElementById('countTopic').value || 10,
                    difficulty: document.getElementById('diffTopic').value,
                });
                if (d.success) {
                    // ── 3D: star-burst for topic generation ──
                    ThreeAnim.playTopicBurst(d.questions.length);
                    afterGen(d.questions, d.quizId);
                } else {
                    qmToast('error', d.message || 'Generation failed');
                }
            } catch { qmToast('error', 'Network error'); }
            qmBtnLoad('genTopicBtn', false, '✨ Generate Questions');
        }

        // ── PDF ─────────────────────────────────────
        function selectPdf(input) {
            pdfFile = input.files[0];
            if (!pdfFile) return;
            document.getElementById('pdfZoneContent').innerHTML = `
                <div style="font-size:2rem;margin-bottom:6px">✅</div>
                <div style="font-weight:600;font-size:.88rem">${qmEsc(pdfFile.name)}</div>
                <p class="text-muted" style="font-size:.76rem;margin-top:4px">${(pdfFile.size / 1024 / 1024).toFixed(1)} MB</p>`;
            document.getElementById('pdfZone').classList.add('drop-zone-success');
        }

        function dropPdf(e) {
            e.preventDefault();
            document.getElementById('pdfZone').classList.remove('over');
            const f = e.dataTransfer.files[0];
            if (f?.type === 'application/pdf') {
                document.getElementById('pdfInput').files = e.dataTransfer.files;
                selectPdf(document.getElementById('pdfInput'));
            } else qmToast('error', 'Drop a PDF file');
        }

        async function genPdf() {
            if (!pdfFile) { qmToast('error', 'Select a PDF'); return; }
            qmBtnLoad('genPdfBtn', true, '📄 Generate from PDF');
            const fd = new FormData();
            fd.append('pdf', pdfFile);
            fd.append('count', document.getElementById('countPdf').value || 10);
            fd.append('difficulty', document.getElementById('diffPdf').value);
            try {
                const d = await qmPostForm(ROUTES.genPdf, fd);
                if (d.success) {
                    // ── 3D: PDF page shred → MCQ dots ──
                    ThreeAnim.playPdfToMcq(d.questions.length);
                    afterGen(d.questions, d.quizId);
                } else {
                    qmToast('error', d.message || 'PDF processing failed');
                }
            } catch { qmToast('error', 'Network error'); }
            qmBtnLoad('genPdfBtn', false, '📄 Generate from PDF');
        }

        // ── Image ───────────────────────────────────
        let _imgPreviewSrc = null; // store data URL for animation

        function selectImg(input) {
            imgFile = input.files[0];
            if (!imgFile) return;
            if (!imgFile.type.startsWith('image/')) {
                qmToast('error', 'Upload JPG/PNG/WebP');
                imgFile = null;
                return;
            }
            const reader = new FileReader();
            reader.onload = ev => {
                _imgPreviewSrc = ev.target.result; // save for Three.js texture
                document.getElementById('imgZoneContent').innerHTML = `
                    <img src="${ev.target.result}" style="max-width:100%;max-height:150px;border-radius:8px;margin-bottom:6px;object-fit:contain">
                    <p style="font-size:.76rem;color:var(--green)">✅ ${qmEsc(imgFile.name)}</p>`;
                document.getElementById('imgZone').classList.add('drop-zone-success');
            };
            reader.readAsDataURL(imgFile);
            document.getElementById('imgErrBox').style.display = 'none';
        }

        async function genImg() {
            if (!imgFile) { qmToast('error', 'Select an image'); return; }
            qmBtnLoad('genImgBtn', true, '🖼️ Analyze & Generate');
            const fd = new FormData();
            fd.append('image', imgFile);
            fd.append('count', document.getElementById('countImg').value || 5);
            try {
                const d = await qmPostForm(ROUTES.genImage, fd);
                if (d.success) {
                    // ── 3D: image pixel explosion → MCQ cards ──
                    ThreeAnim.playImageExplode(_imgPreviewSrc || '', d.questions.length);
                    afterGen(d.questions, d.quizId);
                    qmToast('success', `Detected: ${d.detectedLabel}`);
                } else {
                    const eb = document.getElementById('imgErrBox');
                    eb.style.display = '';
                    eb.textContent = '⚠️ ' + (d.message || 'Image error');
                    qmToast('error', d.message || 'Image error');
                }
            } catch { qmToast('error', 'Network error'); }
            qmBtnLoad('genImgBtn', false, '🖼️ Analyze & Generate');
        }

        // ── Standard ────────────────────────────────
        async function genStandard() {
            const cls = document.getElementById('classInput').value;
            const sub = document.getElementById('subjectStd').value;
            if (!cls || !sub) { qmToast('error', 'Select class and subject'); return; }
            qmBtnLoad('genStdBtn', true, '📚 Generate Standard Quiz');
            try {
                const d = await qmPost(ROUTES.genStandard, {
                    subject:    sub,
                    class:      cls,
                    count:      +document.getElementById('countStd').value || 10,
                    difficulty: document.getElementById('diffStd').value,
                });
                if (d.success) {
                    // ── 3D: same star-burst for standard too ──
                    ThreeAnim.playTopicBurst(d.questions.length);
                    afterGen(d.questions, d.quizId);
                } else {
                    qmToast('error', d.message || 'Generation failed');
                }
            } catch { qmToast('error', 'Network error'); }
            qmBtnLoad('genStdBtn', false, '📚 Generate Standard Quiz');
        }

        // ── Manual MCQ ──────────────────────────────
        function addMcqItem() {
            const id = ++mcqCounter;
            mcqItems.push({ id, question: '', options: ['', '', '', ''], answer: 0, explanation: '' });
            renderMcqList();
        }

        function removeMcqItem(id) {
            mcqItems = mcqItems.filter(m => m.id !== id);
            renderMcqList();
        }

        function setAnswer(idx, j) {
            mcqItems[idx].answer = j;
            renderMcqList();
        }

        function renderMcqList() {
            document.getElementById('mcqList').innerHTML = mcqItems.map((item, idx) => `
                <div class="mcq-item">
                    <div class="mcq-item-header">
                        <div class="mcq-num">Q${idx + 1}</div>
                        <span style="font-family:var(--fh);font-size:.8rem;font-weight:700">Question ${idx + 1}</span>
                        ${mcqItems.length > 1 ? `<button class="mcq-delete" onclick="removeMcqItem(${item.id})">✕ Remove</button>` : ''}
                    </div>
                    <input class="mcq-opt-input w-full mb-10" type="text" placeholder="Enter question text…"
                           value="${qmEsc(item.question)}" oninput="mcqItems[${idx}].question=this.value">
                    <div class="mcq-options-grid">
                        ${[0, 1, 2, 3].map(j => `
                            <div class="mcq-opt-wrap">
                                <span class="mcq-opt-letter">${LETTERS[j]}</span>
                                <input class="mcq-opt-input" type="text" placeholder="Option ${LETTERS[j]}…"
                                       value="${qmEsc(item.options[j] || '')}"
                                       oninput="mcqItems[${idx}].options[${j}]=this.value">
                            </div>`).join('')}
                    </div>
                    <div class="answer-select-row">
                        <span class="form-label" style="margin:0;font-size:.7rem">Correct:</span>
                        ${[0, 1, 2, 3].map(j => `
                            <button class="answer-radio-btn${item.answer === j ? ' selected' : ''}"
                                    onclick="setAnswer(${idx},${j})">${LETTERS[j]}</button>`).join('')}
                    </div>
                    <input class="mcq-opt-input w-full mt-8" type="text" placeholder="Explanation (optional)…"
                           value="${qmEsc(item.explanation || '')}" oninput="mcqItems[${idx}].explanation=this.value">
                </div>
            `).join('');
            document.getElementById('manualQCount').textContent =
                `${mcqItems.length} question${mcqItems.length !== 1 ? 's' : ''} added`;
        }

        async function saveManual() {
            const title = document.getElementById('manualTitle').value.trim();
            if (!title)        { qmToast('error', 'Enter a quiz title'); return; }
            if (!mcqItems.length) { qmToast('error', 'Add at least one question'); return; }
            if (mcqItems.find(m => !m.question.trim() || m.options.some(o => !o.trim()))) {
                qmToast('error', 'Fill in all questions and options'); return;
            }
            qmBtnLoad('saveManualBtn', true, '💾 Save Quiz');
            try {
                const d = await qmPost(ROUTES.saveManual, {
                    title,
                    subject:    document.getElementById('manualSubject').value.trim(),
                    difficulty: document.getElementById('manualDiff').value,
                    questions:  mcqItems.map(({ question, options, answer, explanation }) =>
                                    ({ question, options, answer, explanation })),
                });
                if (d.success) {
                    qmToast('success', `Quiz saved! ${d.count} questions 🎉`);
                    afterGen(mcqItems.map(m => ({ ...m, source: 'manual' })), d.quizId);
                    mcqItems   = [];
                    mcqCounter = 0;
                    renderMcqList();
                } else qmToast('error', d.message || 'Save failed');
            } catch { qmToast('error', 'Network error'); }
            qmBtnLoad('saveManualBtn', false, '💾 Save Quiz');
        }

        // ── Existing quiz loader ─────────────────────
        function loadExistingQuiz(id, qs) {
            questions     = qs;
            currentQuizId = id;
            renderPreview();
            qmToast('info', 'Quiz loaded — choose how to play!');
        }

        async function deleteQuiz(id) {
            if (!confirm('Delete this quiz?')) return;
            try {
                const d = await qmDelete(`${ROUTES.deleteQuiz}/${id}`);
                if (d.success) {
                    document.getElementById(`myquiz-${id}`)?.remove();
                    qmToast('success', 'Quiz deleted');
                    if (currentQuizId === id) {
                        questions     = [];
                        currentQuizId = null;
                        renderPreview();
                    }
                } else qmToast('error', d.message || 'Delete failed');
            } catch { qmToast('error', 'Network error'); }
        }

        // ── Battle Modal ─────────────────────────────
        function openBattleModal() {
            if (!questions.length) { qmToast('error', 'Generate questions first'); return; }
            selectedMode = null;
            document.querySelectorAll('.mode-card').forEach(c => c.classList.remove('selected'));
            document.getElementById('teamNameInputs').style.display = 'none';
            document.getElementById('battleConfirmBtn').disabled = true;
            document.getElementById('battleModal').classList.remove('hidden');
        }

        function closeBattleModal() {
            document.getElementById('battleModal').classList.add('hidden');
        }

        function selectBattleMode(mode, btn) {
            selectedMode = mode;
            document.querySelectorAll('.mode-card').forEach(c => c.classList.remove('selected'));
            btn.classList.add('selected');
            document.getElementById('teamNameInputs').style.display = mode === 'team' ? '' : 'none';
            document.getElementById('battleConfirmBtn').disabled = false;
        }

        function confirmBattle() {
            if (!selectedMode) return;
            if (selectedMode === 'team') {
                const a = document.getElementById('teamAName').value.trim();
                const b = document.getElementById('teamBName').value.trim();
                if (!a || !b) { qmToast('error', 'Enter both team names'); return; }
            }
            closeBattleModal();
            const params = new URLSearchParams({
                quizId: currentQuizId || '',
                mode:   selectedMode,
                ...(selectedMode === 'team' ? {
                    teamA: document.getElementById('teamAName').value.trim(),
                    teamB: document.getElementById('teamBName').value.trim(),
                } : {}),
            });
            window.location.href = `/student/battle/setup?${params}`;
        }

        document.getElementById('battleModal').addEventListener('click', e => {
            if (e.target === document.getElementById('battleModal')) closeBattleModal();
        });

        // ── Solo Play ────────────────────────────────
        function startSolo() {
            if (!questions.length) { qmToast('error', 'No questions loaded'); return; }
            playState = {
                qIdx:      0,
                score:     0,
                answered:  false,
                selected:  null,
                timeLeft:  20,
                timer:     null,
                log:       [],
                startTime: Date.now(),
            };
            document.getElementById('playScreen').classList.add('open');
            document.body.style.overflow = 'hidden';
            renderPlayQ();
            startTimer();
        }

        function exitPlay() {
            clearInterval(playState.timer);
            document.getElementById('playScreen').classList.remove('open');
            document.body.style.overflow = '';
        }

        function startTimer() {
            clearInterval(playState.timer);
            playState.timeLeft = 20;
            playState.timer = setInterval(() => {
                playState.timeLeft--;
                const ring = document.getElementById('timerRingEl');
                const bar  = document.getElementById('timerBar');
                if (ring) {
                    ring.textContent = playState.timeLeft;
                    ring.className   = 'timer-ring' + (playState.timeLeft <= 5 ? ' danger' : '');
                }
                if (bar) bar.style.width = (playState.timeLeft / 20 * 100) + '%';
                if (playState.timeLeft <= 0) {
                    clearInterval(playState.timer);
                    handleAnswer(-1);
                }
            }, 1000);
        }

        function renderPlayQ() {
            const q = questions[playState.qIdx];
            document.getElementById('playInner').innerHTML = `
                <div>
                    <div class="flex mb-12" style="justify-content:space-between">
                        <span class="badge bc">Q ${playState.qIdx + 1} / ${questions.length}</span>
                        <button class="btn btn-ghost btn-sm" onclick="exitPlay()" style="padding:4px 10px">✕ Exit</button>
                    </div>
                    <div class="progress-track mb-20">
                        <div class="progress-fill" style="width:${playState.qIdx / questions.length * 100}%"></div>
                    </div>
                    <div class="flex mb-12" style="justify-content:space-between;align-items:center">
                        <div class="timer-ring" id="timerRingEl">${playState.timeLeft}</div>
                        <div style="height:4px;flex:1;background:rgba(255,255,255,.06);border-radius:4px;margin:0 14px;overflow:hidden">
                            <div id="timerBar" style="width:${playState.timeLeft / 20 * 100}%;height:100%;background:var(--gradient);border-radius:4px;transition:width 1s linear"></div>
                        </div>
                        <span class="badge bo">⚡ ${playState.score}</span>
                    </div>
                    <div class="card mb-14 anim-pop">
                        <div class="flex mb-10" style="justify-content:space-between">
                            <span class="badge bp">${qmEsc(q.topic || 'General')}</span>
                            <span class="badge bc">${q.source === 'pdf' ? '📄 PDF' : q.source === 'image' ? '🖼️ IMG' : '🤖 AI'}</span>
                        </div>
                        <h2 style="font-size:1rem;line-height:1.65;font-weight:600">${qmEsc(q.question)}</h2>
                    </div>
                    <div class="flex-col gap-10 mb-14" id="optionsWrap">
                        ${q.options.map((opt, i) => `
                            <button class="qo anim-fade" style="animation-delay:${i * .06}s" onclick="handleAnswer(${i})" id="opt${i}">
                                <span class="ol">${LETTERS[i]}</span>${qmEsc(opt)}
                            </button>`).join('')}
                    </div>
                    <div id="explanationBox"></div>
                </div>`;
        }

        function handleAnswer(idx) {
            if (playState.answered) return;
            clearInterval(playState.timer);
            playState.answered = true;
            playState.selected = idx;
            const q       = questions[playState.qIdx];
            const correct = idx === q.answer;
            if (correct) playState.score++;
            playState.log.push({ idx, correct });

            for (let i = 0; i < 4; i++) {
                const btn = document.getElementById(`opt${i}`);
                if (!btn) continue;
                btn.disabled = true;
                if (i === q.answer) btn.classList.add('correct');
                else if (i === idx) btn.classList.add('wrong');
            }

            const box = document.getElementById('explanationBox');
            if (box) box.innerHTML = `
                <div class="card anim-fade" style="border-color:${correct ? 'rgba(0,227,150,.25)' : 'rgba(255,77,106,.2)'};background:${correct ? 'rgba(0,227,150,.04)' : 'rgba(255,77,106,.04)'}">
                    <div class="flex gap-8 mb-8">
                        <span>${correct ? '✅' : '❌'}</span>
                        <span style="font-weight:700;font-size:.86rem;color:${correct ? 'var(--green)' : 'var(--red)'}">${correct ? 'Correct! +20 XP' : 'Wrong!'}</span>
                    </div>
                    ${q.explanation ? `<p class="text-muted mb-12" style="font-size:.82rem;line-height:1.6">${qmEsc(q.explanation)}</p>` : ''}
                    <button class="btn btn-grad btn-sm" onclick="nextQ()">
                        ${playState.qIdx + 1 >= questions.length ? '🏆 See Results' : 'Next →'}
                    </button>
                </div>`;
        }

        function nextQ() {
            if (playState.qIdx + 1 >= questions.length) { finishSolo(); return; }
            playState.qIdx++;
            playState.answered = false;
            playState.selected = null;
            renderPlayQ();
            startTimer();
        }

        function finishSolo() {
            clearInterval(playState.timer);
            const total   = questions.length;
            const score   = playState.log.filter(r => r.correct).length;
            const acc     = Math.round((score / total) * 100);
            const xp      = score * 20 + (acc >= 80 ? 40 : 0);
            const elapsed = Math.round((Date.now() - playState.startTime) / 1000);

            const subject = document.getElementById('subjectStd')?.value
                         || document.getElementById('manualSubject')?.value
                         || '';

            qmPost(ROUTES.submitSolo, {
                quiz_id:    currentQuizId,
                score,
                total_q:    total,
                accuracy:   acc,
                xp_earned:  xp,
                time_taken: elapsed,
                answer_log: playState.log,
                subject,
            }).catch(() => {});

            if (acc >= 80) qmConfetti();

            document.getElementById('playInner').innerHTML = `
                <div class="text-center anim-pop" style="padding-top:40px">
                    <div style="font-size:4rem;margin-bottom:14px">${acc >= 80 ? '🏆' : acc >= 60 ? '🎯' : '📚'}</div>
                    <h1 style="font-family:var(--fh);font-size:1.9rem;font-weight:800;margin-bottom:6px">
                        ${acc >= 80 ? 'Excellent!' : acc >= 60 ? 'Good Job!' : 'Keep Practicing!'}
                    </h1>
                    <p class="text-muted mb-28">Quiz Complete</p>
                    <div class="flex gap-14 mb-28" style="justify-content:center;flex-wrap:wrap">
                        <div class="stat-card sc-purple text-center" style="min-width:90px">
                            <div class="stat-val grad" style="font-size:1.5rem">${score}/${total}</div>
                            <div class="stat-label">Score</div>
                        </div>
                        <div class="stat-card sc-cyan text-center" style="min-width:90px">
                            <div class="stat-val cyan" style="font-size:1.5rem">${acc}%</div>
                            <div class="stat-label">Accuracy</div>
                        </div>
                        <div class="stat-card sc-gold text-center" style="min-width:90px">
                            <div class="stat-val gold" style="font-size:1.5rem">+${xp}</div>
                            <div class="stat-label">XP Earned</div>
                        </div>
                        <div class="stat-card sc-green text-center" style="min-width:90px">
                            <div class="stat-val green" style="font-size:1.5rem">${elapsed}s</div>
                            <div class="stat-label">Time</div>
                        </div>
                    </div>
                    <div class="flex gap-10" style="justify-content:center;flex-wrap:wrap">
                        <button class="btn btn-grad"  onclick="startSolo()">🔄 Play Again</button>
                        <button class="btn btn-ghost" onclick="openBattleModal();exitPlay()">⚔️ Battle</button>
                        <button class="btn btn-ghost" onclick="exitPlay()">← Back</button>
                    </div>
                    <div class="mt-28 text-left">
                        <div class="section-title mb-12">Question Review</div>
                        ${playState.log.map((r, i) => `
                            <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border)">
                                <span>${r.correct ? '✅' : '❌'}</span>
                                <span class="text-muted" style="font-size:.82rem;flex:1">Q${i + 1}: ${qmEsc(questions[i]?.question?.slice(0, 60))}…</span>
                                <span style="font-size:.76rem;font-weight:700;color:${r.correct ? 'var(--green)' : 'var(--red)'}">${r.correct ? 'Correct' : 'Wrong'}</span>
                            </div>`).join('')}
                    </div>
                </div>`;
        }

        // ── AI Suggestions ───────────────────────────
        async function loadSuggestions() {
            const banner = document.getElementById('tipBanner');
            banner.style.display = '';
            document.getElementById('tipText').innerHTML = '💡 Loading AI tips…';
            try {
                const d = await fetch(ROUTES.suggestions, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(r => r.json());
                document.getElementById('tipText').innerHTML = '💡 ' + (d.suggestion || 'Keep studying!');
            } catch {
                document.getElementById('tipText').innerHTML = '💡 Complete more quizzes to unlock personalized tips!';
            }
        }

        // ── Init ─────────────────────────────────────
        addMcqItem();
    </script>

@endsection