{{-- resources/views/student/battle/arena.blade.php --}}
@extends('student.layout.master')
@section('title', 'Battle ⚔️ ' . $room->code)

@section('content')
    <style>
        body,
        html {
            overflow: hidden !important
        }

        #arena {
            position: fixed;
            inset: 0;
            display: flex;
            flex-direction: column;
            background: var(--bg);
            z-index: 500
        }

        /* Top bar */
        #topBar {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 18px;
            background: rgba(0, 0, 0, .4);
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(12px);
            flex-shrink: 0
        }

        .tb-code {
            font-family: var(--fh);
            font-weight: 800;
            font-size: .82rem;
            color: var(--accent)
        }

        .tb-prog {
            flex: 1;
            height: 5px;
            background: rgba(255, 255, 255, .08);
            border-radius: 4px;
            overflow: hidden
        }

        .tb-fill {
            height: 100%;
            background: var(--gradient);
            border-radius: 4px;
            transition: width .4s ease
        }

        .tb-score {
            background: rgba(124, 92, 252, .15);
            border: 1px solid rgba(124, 92, 252, .3);
            color: var(--accent);
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 800;
            font-size: .82rem
        }

        /* Body */
        #arenaBody {
            display: grid;
            grid-template-columns: 1fr 260px;
            flex: 1;
            overflow: hidden;
            min-height: 0
        }

        @media(max-width:700px) {
            #arenaBody {
                grid-template-columns: 1fr
            }

            #sidebar {
                display: none
            }
        }

        #mainPanel {
            display: flex;
            flex-direction: column;
            padding: 18px 22px;
            overflow-y: auto;
            gap: 14px;
            min-height: 0
        }

        /* Timer */
        .timer-row {
            display: flex;
            align-items: center;
            gap: 14px
        }

        .timer-circle {
            position: relative;
            width: 60px;
            height: 60px;
            flex-shrink: 0
        }

        .timer-circle svg {
            transform: rotate(-90deg)
        }

        .tc-bg {
            fill: none;
            stroke: rgba(255, 255, 255, .08);
            stroke-width: 5
        }

        .tc-fill {
            fill: none;
            stroke-width: 5;
            stroke-linecap: round;
            stroke-dasharray: 157;
            stroke-dashoffset: 0;
            transition: stroke .4s
        }

        .tc-num {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--fh);
            font-size: 1.2rem;
            font-weight: 900
        }

        .timer-bar-wrap {
            flex: 1;
            height: 6px;
            background: rgba(255, 255, 255, .07);
            border-radius: 3px;
            overflow: hidden
        }

        .timer-bar {
            height: 100%;
            background: var(--gradient);
            border-radius: 3px;
            transition: width 1s linear, background .4s
        }

        /* Question card */
        .q-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            animation: qSlide .35s cubic-bezier(.34, 1.26, .64, 1)
        }

        @keyframes qSlide {
            from {
                opacity: 0;
                transform: translateY(16px) scale(.97)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .q-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            gap: 8px;
            flex-wrap: wrap
        }

        .q-topic {
            background: rgba(0, 212, 255, .08);
            color: #00d4ff;
            border: 1px solid rgba(0, 212, 255, .2);
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700
        }

        .q-text {
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.65;
            color: var(--text)
        }

        /* Options */
        .opts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px
        }

        @media(max-width:500px) {
            .opts-grid {
                grid-template-columns: 1fr
            }
        }

        .opt {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 14px;
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            background: transparent;
            cursor: pointer;
            text-align: left;
            transition: border-color .15s, background .15s, transform .12s;
            font-size: .88rem;
            color: var(--text);
            width: 100%;
            animation: optIn .25s ease both
        }

        .opt:nth-child(1) {
            animation-delay: .04s
        }

        .opt:nth-child(2) {
            animation-delay: .08s
        }

        .opt:nth-child(3) {
            animation-delay: .12s
        }

        .opt:nth-child(4) {
            animation-delay: .16s
        }

        @keyframes optIn {
            from {
                opacity: 0;
                transform: translateY(8px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .opt:hover:not(:disabled):not(.correct):not(.wrong):not(.selected) {
            border-color: var(--accent);
            background: rgba(124, 92, 252, .07);
            transform: translateY(-1px)
        }

        .opt-letter {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, .15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: .76rem;
            flex-shrink: 0;
            transition: .15s
        }

        .opt:hover:not(:disabled):not(.correct):not(.wrong):not(.selected) .opt-letter {
            border-color: var(--accent);
            background: rgba(124, 92, 252, .2)
        }

        .opt.selected {
            border-color: var(--accent);
            background: rgba(124, 92, 252, .1)
        }

        .opt.selected .opt-letter {
            border-color: var(--accent);
            background: var(--accent);
            color: #fff
        }

        .opt.correct {
            border-color: #00e396 !important;
            background: rgba(0, 227, 150, .08) !important;
            animation: correctPop .4s cubic-bezier(.34, 1.56, .64, 1)
        }

        .opt.correct .opt-letter {
            border-color: #00e396;
            background: #00e396;
            color: #000
        }

        @keyframes correctPop {
            0% {
                transform: scale(1)
            }

            50% {
                transform: scale(1.04)
            }

            100% {
                transform: scale(1)
            }
        }

        .opt.wrong {
            border-color: rgba(255, 77, 106, .5) !important;
            background: rgba(255, 77, 106, .07) !important;
            animation: wrongShake .3s ease
        }

        .opt.wrong .opt-letter {
            border-color: var(--red);
            background: var(--red);
            color: #fff
        }

        @keyframes wrongShake {

            0%,
            100% {
                transform: translateX(0)
            }

            25% {
                transform: translateX(-5px)
            }

            75% {
                transform: translateX(5px)
            }
        }

        .opt:disabled {
            cursor: not-allowed
        }

        /* Feedback */
        #feedback {
            padding: 14px 16px;
            border-radius: var(--radius-sm);
            border: 1px solid;
            display: none;
            animation: optIn .25s ease
        }

        #feedback.show {
            display: block
        }

        #feedback.fb-ok {
            border-color: rgba(0, 227, 150, .3);
            background: rgba(0, 227, 150, .05)
        }

        #feedback.fb-bad {
            border-color: rgba(255, 77, 106, .25);
            background: rgba(255, 77, 106, .04)
        }

        #feedback.fb-out {
            border-color: rgba(255, 165, 0, .25);
            background: rgba(255, 165, 0, .04)
        }

        .fb-head {
            font-weight: 800;
            font-size: .9rem;
            margin-bottom: 4px
        }

        .fb-expl {
            font-size: .8rem;
            color: var(--muted);
            line-height: 1.6
        }

        /* Next bar */
        #nextBar {
            display: none;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: .78rem;
            color: var(--muted)
        }

        #nextBar.show {
            display: flex
        }

        .nb-track {
            width: 80px;
            height: 3px;
            background: rgba(255, 255, 255, .08);
            border-radius: 2px;
            overflow: hidden
        }

        .nb-fill {
            height: 100%;
            background: var(--accent);
            border-radius: 2px
        }

        /* Sidebar */
        #sidebar {
            border-left: 1px solid var(--border);
            background: rgba(0, 0, 0, .2);
            display: flex;
            flex-direction: column;
            overflow: hidden
        }

        .sb-hd {
            padding: 12px 14px;
            border-bottom: 1px solid var(--border);
            font-family: var(--fh);
            font-weight: 800;
            font-size: .78rem;
            flex-shrink: 0
        }

        .team-wrap {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 6px;
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0
        }

        .ts {
            text-align: center;
            padding: 9px 4px;
            border-radius: 7px;
            border: 1px solid var(--border)
        }

        .ts-n {
            font-size: .62rem;
            font-weight: 700;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .ts-s {
            font-family: var(--fh);
            font-size: 1.3rem;
            font-weight: 900
        }

        #scoreList {
            overflow-y: auto;
            flex: 1
        }

        .sr {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 8px 12px;
            border-bottom: 1px solid var(--border2);
            transition: background .2s
        }

        .sr:last-child {
            border: none
        }

        .sr.me {
            background: rgba(124, 92, 252, .07)
        }

        .sr-rank {
            font-size: .68rem;
            font-weight: 800;
            color: var(--muted);
            width: 18px;
            flex-shrink: 0
        }

        .sr-av {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .68rem;
            font-weight: 800;
            color: #fff;
            flex-shrink: 0
        }

        .sr-nm {
            flex: 1;
            font-size: .76rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .sr-pts {
            font-family: var(--fh);
            font-weight: 800;
            font-size: .82rem;
            flex-shrink: 0
        }

        .sr-tag {
            font-size: .56rem;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: 700;
            flex-shrink: 0
        }

        .tag-a {
            background: rgba(124, 92, 252, .18);
            color: var(--accent)
        }

        .tag-b {
            background: rgba(0, 227, 150, .12);
            color: #00e396
        }

        .sr-streak {
            font-size: .62rem
        }

        /* Float FX */
        .float-fx {
            position: fixed;
            pointer-events: none;
            z-index: 9000;
            font-size: 1.5rem;
            font-weight: 900;
            font-family: var(--fh);
            animation: floatUp .85s ease forwards;
            white-space: nowrap
        }

        @keyframes floatUp {
            0% {
                opacity: 1;
                transform: translateY(0) scale(1)
            }

            100% {
                opacity: 0;
                transform: translateY(-70px) scale(1.25)
            }
        }

        /* Panels */
        #waitPanel {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            text-align: center;
            gap: 10px;
            padding: 40px 20px
        }

        #qPanel {
            display: none;
            flex-direction: column;
            gap: 14px
        }

        #finishedPanel {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            text-align: center;
            gap: 12px;
            padding: 40px
        }

        /* Violation overlay */
        #vioOverlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, .88);
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center
        }

        #vioOverlay.show {
            display: flex;
            animation: optIn .2s
        }

        .vio-box {
            background: var(--card);
            border: 2px solid var(--red);
            border-radius: var(--radius);
            padding: 32px 40px;
            max-width: 360px
        }

        /* Countdown overlay */
        #cdOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .85);
            z-index: 9990;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center
        }

        #cdOverlay.show {
            display: flex
        }

        .cd-num {
            font-family: var(--fh);
            font-size: 6rem;
            font-weight: 900;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: cdPop .4s cubic-bezier(.34, 1.56, .64, 1)
        }

        @keyframes cdPop {
            from {
                transform: scale(.5);
                opacity: 0
            }

            to {
                transform: scale(1);
                opacity: 1
            }
        }

        #fxCanvas {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 8000;
            width: 100%;
            height: 100%
        }
    </style>

    <canvas id="fxCanvas"></canvas>

    <div id="arena">
        <div id="topBar">
            <span class="tb-code">⚔️ {{ $room->code }}</span>
            <div class="tb-prog">
                <div class="tb-fill" id="progFill" style="width:0%"></div>
            </div>
            <span class="tb-score" id="qLabel">Q 0/{{ $room->total_questions }}</span>
            <span class="tb-score">⚡<span id="myPts">0</span></span>
        </div>

        <div id="arenaBody">
            <div id="mainPanel">
                <!-- WAITING -->
                <div id="waitPanel">
                    <div style="font-size:3rem" class="anim-float">⏳</div>
                    <h2 style="font-family:var(--fh);font-weight:800">Waiting for host to start…</h2>
                    <p class="text-muted" style="font-size:.84rem">Stay on this page — battle begins automatically!</p>
                </div>

                <!-- QUESTION -->
                <div id="qPanel">
                    <div class="timer-row">
                        <div class="timer-circle">
                            <svg width="60" height="60" viewBox="0 0 60 60">
                                <circle class="tc-bg" cx="30" cy="30" r="25" />
                                <circle class="tc-fill" id="tcFill" cx="30" cy="30" r="25"
                                    stroke="var(--accent)" />
                            </svg>
                            <div class="tc-num" id="tcNum">--</div>
                        </div>
                        <div class="timer-bar-wrap">
                            <div class="timer-bar" id="timerBar" style="width:100%"></div>
                        </div>
                    </div>
                    <div class="q-card">
                        <div class="q-meta">
                            <span class="q-topic" id="qTopic">General</span>
                            <span class="badge bc" id="qNum">Q 1</span>
                        </div>
                        <p class="q-text" id="qText"></p>
                    </div>
                    <div class="opts-grid" id="optsGrid"></div>
                    <div id="feedback">
                        <div class="fb-head" id="fbHead"></div>
                        <div class="fb-expl" id="fbExpl"></div>
                    </div>
                    <div id="nextBar">
                        Next question in
                        <div class="nb-track">
                            <div class="nb-fill" id="nbFill" style="width:100%"></div>
                        </div>
                        <span id="nbSecs">3</span>s
                    </div>
                </div>

                <!-- FINISHED -->
                <div id="finishedPanel">
                    <div style="font-size:3.5rem">🏆</div>
                    <h2 style="font-family:var(--fh);font-size:1.6rem;font-weight:900">Battle Complete!</h2>
                    <p class="text-muted">Loading results…</p>
                    <a href="{{ route('student.battle.results', $room->code) }}" class="btn btn-grad"
                        style="margin-top:8px">View Results →</a>
                </div>
            </div>

            <!-- SIDEBAR -->
            <div id="sidebar">
                <div class="sb-hd">📊 Live Scores</div>
                @if ($room->mode === 'team')
                    <div class="team-wrap">
                        <div class="ts" style="border-color:rgba(124,92,252,.25)">
                            <div class="ts-n" style="color:var(--accent)">{{ $room->team_a_name }}</div>
                            <div class="ts-s grad" id="tsA">0</div>
                        </div>
                        <div style="display:flex;align-items:center">⚔️</div>
                        <div class="ts" style="border-color:rgba(0,227,150,.2)">
                            <div class="ts-n" style="color:#00e396">{{ $room->team_b_name }}</div>
                            <div class="ts-s green" id="tsB">0</div>
                        </div>
                    </div>
                @endif
                <div id="scoreList">
                    @foreach ($room->participants as $p)
                        <div class="sr {{ $p->user_id === Auth::id() ? 'me' : '' }}" id="sr-{{ $p->user_id }}">
                            <span class="sr-rank">#{{ $loop->iteration }}</span>
                            <div class="sr-av">{{ strtoupper(substr($p->user->name, 0, 1)) }}</div>
                            @if ($room->mode === 'team')
                                <span
                                    class="sr-tag {{ $p->team === 'a' ? 'tag-a' : 'tag-b' }}">{{ strtoupper($p->team ?? '?') }}</span>
                            @endif
                            <span class="sr-nm">{{ $p->user->name }}{{ $p->user_id === $room->host_id ? ' 👑' : '' }}</span>
                            <span class="sr-pts" id="pts-{{ $p->user_id }}">0</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Violation overlay -->
    <div id="vioOverlay">
        <div class="vio-box">
            <div style="font-size:2.8rem;margin-bottom:10px">⚠️</div>
            <h2 style="font-family:var(--fh);font-size:1.1rem;color:var(--red);margin-bottom:8px">Anti-Cheat Warning</h2>
            <p class="text-muted" style="font-size:.84rem;margin-bottom:6px" id="vioMsg"></p>
            <p style="font-size:.74rem;color:var(--muted)" id="vioDetail"></p>
            <button class="btn btn-ghost mt-12" onclick="dismissVio()">Back to Battle</button>
        </div>
    </div>

    <!-- Countdown overlay -->
    <div id="cdOverlay">
        <p style="color:var(--muted);font-size:.9rem;margin-bottom:6px">Battle starting in</p>
        <div class="cd-num" id="cdNum">3</div>
        <p style="color:var(--muted);font-size:.82rem;margin-top:10px">⚔️ Get ready!</p>
    </div>

    <script src="https://js.pusher.com/8.0/pusher.min.js"></script>
    <script>
        // ═══════════════════════════════════════════════════════
        // CONFIG
        // ═══════════════════════════════════════════════════════
        const CFG = {
            roomCode: '{{ $room->code }}',
            myId: {{ Auth::id() }},
            isHost: {{ Auth::id() === $room->host_id ? 'true' : 'false' }},
            mode: '{{ $room->mode }}',
            qTimer: {{ $room->question_timer }},
            totalQ: {{ $room->total_questions }},
            csrf: '{{ csrf_token() }}',
            status: '{{ $room->status }}',
            antiCheat: {{ $room->anti_cheat ?? 1 ? 'true' : 'false' }},
        };
        const LETTERS = ['A', 'B', 'C', 'D'];
        const R = {
            answer: '{{ route('student.battle.answer') }}',
            violation: '{{ route('student.battle.violation') }}',
            results: '{{ route('student.battle.results', $room->code) }}',
            poll: '/student/battle/arena-state/{{ $room->code }}',
        };

        // ═══════════════════════════════════════════════════════
        // STATE
        // ═══════════════════════════════════════════════════════
        const S = {
            qs: [],
            qIdx: -1,
            timeLeft: CFG.qTimer,
            tTick: null,
            nTick: null,
            pTick: null,
            answered: false,
            submitting: false,
            myScore: 0,
            vios: 0,
            disq: false,
            running: false,
            done: false,
            wsWorked: false,
            // FIX: track whether battle start has already been handled
            startHandled: false,
        };

        // ═══════════════════════════════════════════════════════
        // PRE-LOAD when already in_progress
        // ═══════════════════════════════════════════════════════
        @if ($room->status === 'in_progress')
            S.qs = @json(collect($room->quiz->questions ?? [])->map(fn($q) => ['question' => $q['question'], 'options' => $q['options'], 'topic' => $q['topic'] ?? ''])->values());
            S.running = true;
            S.startHandled = true; // FIX: mark as already started so WS battle.started won't re-trigger countdown
            const _answered = {{ $room->answers()->where('user_id', Auth::id())->count() }};
            document.addEventListener('DOMContentLoaded', () => {
                showQ(Math.min(_answered, S.qs.length - 1));
            });
        @endif

        // ═══════════════════════════════════════════════════════
        // WEBSOCKET
        // ═══════════════════════════════════════════════════════
        let pusher, ch;
        try {
            pusher = new Pusher(
                '{{ config('broadcasting.connections.reverb.key', config('broadcasting.connections.pusher.key', 'key')) }}', {
                    cluster: '{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}',
                    wsHost: '{{ config('broadcasting.connections.reverb.host', '127.0.0.1') }}',
                    wsPort: {{ config('broadcasting.connections.reverb.port', 8080) }},
                    wssPort: {{ config('broadcasting.connections.reverb.port', 8080) }},
                    forceTLS: {{ config('broadcasting.connections.reverb.scheme', 'http') === 'https' ? 'true' : 'false' }},
                    enabledTransports: ['ws', 'wss'],
                    disableStats: true,
                });
            ch = pusher.subscribe(`battle.${CFG.roomCode}`);

            // FIX: guard against double-start — only run countdown once
            ch.bind('battle.started', data => {
                if (S.startHandled) return;
                S.startHandled = true;
                S.qs = data.questions;
                S.running = true;
                S.wsWorked = true;
                stopPoll();
                showCountdown(() => showQ(0));
            });

            ch.bind('answer.submitted', data => {
                S.wsWorked = true;
                updateBoard(data.scores);
                if (!S.answered && data.questionIndex === S.qIdx) {
                    revealAnswer(data.correctInfo.correct_option, -1, false, data.correctInfo.explanation);
                }
            });

            ch.bind('next.question', data => {
                S.wsWorked = true;
                clearNextBar();
                if (data.questionIndex < S.qs.length) showQ(data.questionIndex);
                else endBattle();
            });

            ch.bind('battle.finished', () => {
                S.wsWorked = true;
                endBattle();
            });

            // FIX: corrected broken string — was `d ',' var (--red)` which is a syntax error
            ch.bind('violation', data => {
                if (data.userId !== CFG.myId && data.disqualified) floatFX('⛔ Player DQ\'d', 'var(--red)');
            });

        } catch (e) {
            console.warn('WS init failed', e);
        }
        // FIX: poll/question functions are now properly OUTSIDE the try/catch block

        // ═══════════════════════════════════════════════════════
        // POLL FALLBACK
        // ═══════════════════════════════════════════════════════
        function startPoll() {
            if (!S.pTick) S.pTick = setInterval(doPoll, 3000);
        }

        function stopPoll() {
            clearInterval(S.pTick);
            S.pTick = null;
        }
        startPoll();

        async function doPoll() {
            try {
                const res = await fetch(R.poll, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': CFG.csrf
                    }
                });
                const d = await res.json();
                if (!d.success) return;
                if (d.scores) updateBoard(d.scores);
                if (d.status === 'finished' && !S.done) endBattle();
                if (!S.running && d.status === 'in_progress') location.reload();
            } catch (e) {}
        }

        // ═══════════════════════════════════════════════════════
        // SHOW QUESTION
        // ═══════════════════════════════════════════════════════
        function showQ(idx) {
            if (S.done) return;
            if (idx >= S.qs.length) {
                endBattle();
                return;
            }

            S.qIdx = idx;
            S.answered = false;
            S.submitting = false;
            S.timeLeft = CFG.qTimer;

            clearTimer();
            clearNextBar();
            hideFB();

            g('waitPanel').style.display = 'none';
            g('qPanel').style.display = 'flex';
            g('finishedPanel').style.display = 'none';

            const q = S.qs[idx];
            g('progFill').style.width = (idx / CFG.totalQ * 100) + '%';
            g('qLabel').textContent = `Q ${idx+1}/${CFG.totalQ}`;
            g('qNum').textContent = `Q ${idx+1}`;
            g('qTopic').textContent = q.topic || 'General';
            g('qText').textContent = q.question;

            let html = '';
            for (let i = 0; i < 4; i++) {
                html += `<button class="opt" id="opt${i}" onclick="handleOpt(${i})">` +
                    `<span class="opt-letter">${LETTERS[i]}</span>` +
                    `<span>${esc(q.options[i]||'')}</span></button>`;
            }
            g('optsGrid').innerHTML = html;

            startTimer();
            snd('tick');
        }

        // ═══════════════════════════════════════════════════════
        // TIMER
        // ═══════════════════════════════════════════════════════
        function startTimer() {
            clearTimer();
            S.timeLeft = CFG.qTimer;
            drawTimer(S.timeLeft);
            S.tTick = setInterval(() => {
                S.timeLeft--;
                drawTimer(S.timeLeft);
                if (S.timeLeft <= 5 && S.timeLeft > 0) snd('urgent');
                if (S.timeLeft <= 0) {
                    clearTimer();
                    if (!S.answered) doTimeout();
                }
            }, 1000);
        }

        function clearTimer() {
            clearInterval(S.tTick);
            S.tTick = null;
        }

        function drawTimer(t) {
            const pct = Math.max(0, t / CFG.qTimer);
            const off = 157 * (1 - pct);
            const fill = g('tcFill'), num = g('tcNum'), bar = g('timerBar');
            if (fill) {
                fill.style.strokeDashoffset = off;
                fill.style.stroke = pct > .5 ? 'var(--accent)' : pct > .25 ? 'orange' : 'var(--red)';
            }
            if (num) {
                num.textContent = t;
                num.style.color = pct > .25 ? 'var(--text)' : 'var(--red)';
            }
            if (bar) {
                bar.style.width = (pct * 100) + '%';
                bar.style.background = pct > .5 ? '' : pct > .25 ? 'orange' : 'var(--red)';
            }
        }

        // ═══════════════════════════════════════════════════════
        // ANSWER
        // ═══════════════════════════════════════════════════════
        function handleOpt(idx) {
            if (S.answered || S.submitting || S.disq) return;

            S.answered = true;
            S.submitting = true;
            clearTimer();

            const btn = g(`opt${idx}`);
            if (btn) btn.classList.add('selected');
            lockOpts();

            const ms = (CFG.qTimer - S.timeLeft) * 1000;
            doSubmit(idx, ms);
        }

        async function doSubmit(idx, ms) {
            try {
                const res = await fetch(R.answer, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CFG.csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        room_code: CFG.roomCode,
                        question_index: S.qIdx,
                        selected: idx,
                        time_ms: ms
                    }),
                });

                S.submitting = false;

                if (!res.ok) {
                    schedNext();
                    return;
                }

                const d = await res.json();

                if (d.success) {
                    S.myScore += (d.points || 0);
                    g('myPts').textContent = S.myScore;
                    revealAnswer(d.correct, idx, d.isCorrect, d.explanation);

                    if (d.isCorrect) {
                        snd('correct');
                        spawnParts(g(`opt${d.correct}`), '#00e396');
                        floatFX(`+${d.points} pts${d.streak>=3?' 🔥':''}`, '#00e396');
                        if (d.streak >= 3) floatFX(`🔥 ×${d.streak} Streak!`, 'orange', 60);
                    } else {
                        snd('wrong');
                        floatFX('Wrong!', 'var(--red)');
                    }
                }
                schedNext();

            } catch (err) {
                console.error('submit err:', err);
                S.submitting = false;
                schedNext();
            }
        }

        function doTimeout() {
            S.answered = true;
            S.submitting = false;
            lockOpts();
            snd('timeout');
            showFB('fb-out', '⏰ Time\'s up!', '');
            schedNext();
            fetch(R.answer, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CFG.csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    room_code: CFG.roomCode,
                    question_index: S.qIdx,
                    selected: -1,
                    time_ms: CFG.qTimer * 1000
                }),
            }).catch(() => {});
        }

        function lockOpts() {
            for (let i = 0; i < 4; i++) {
                const b = g(`opt${i}`);
                if (b) b.disabled = true;
            }
        }

        // ═══════════════════════════════════════════════════════
        // REVEAL ANSWER
        // ═══════════════════════════════════════════════════════
        function revealAnswer(correctIdx, selectedIdx, isCorrect, explanation) {
            for (let i = 0; i < 4; i++) {
                const b = g(`opt${i}`);
                if (!b) continue;
                b.classList.remove('selected');
                b.disabled = true;
                if (i === correctIdx) b.classList.add('correct');
                else if (i === selectedIdx && !isCorrect) b.classList.add('wrong');
            }
            if (isCorrect !== null && isCorrect !== undefined && selectedIdx >= 0) {
                const cls = isCorrect ? 'fb-ok' : 'fb-bad';
                const title = isCorrect ? `✅ Correct!` : `❌ Wrong! Correct: ${LETTERS[correctIdx]}`;
                showFB(cls, title, explanation || '');
            }
        }

        // ═══════════════════════════════════════════════════════
        // NEXT QUESTION BAR
        // ═══════════════════════════════════════════════════════
        function schedNext() {
            const bar = g('nextBar'), fill = g('nbFill'), sec = g('nbSecs');
            if (!bar) return;
            bar.classList.add('show');
            let t = 3.0;
            if (fill) fill.style.width = '100%';
            if (sec) sec.textContent = 3;

            // FIX: capture current qIdx so the timeout comparison is valid
            const capturedIdx = S.qIdx;

            S.nTick = setInterval(() => {
                t -= .1;
                if (fill) fill.style.width = Math.max(0, t / 3 * 100) + '%';
                if (sec) sec.textContent = Math.ceil(t);
                if (t <= 0) {
                    clearNextBar();
                    const wsConnected = pusher && pusher.connection && pusher.connection.state === 'connected';
                    if (!S.wsWorked || !wsConnected) {
                        // No WS — advance locally
                        const next = S.qIdx + 1;
                        if (next < S.qs.length) showQ(next);
                        else endBattle();
                    } else {
                        // WS connected — give it 600ms to fire next.question, then advance as safety net
                        setTimeout(() => {
                            // FIX: compare against capturedIdx, not S.qIdx === S.qIdx (always true)
                            if (S.qIdx === capturedIdx && !S.done) {
                                const next = S.qIdx + 1;
                                if (next < S.qs.length) showQ(next);
                                else endBattle();
                            }
                        }, 600);
                    }
                }
            }, 100);
        }

        function clearNextBar() {
            clearInterval(S.nTick);
            S.nTick = null;
            const bar = g('nextBar');
            if (bar) bar.classList.remove('show');
        }

        // ═══════════════════════════════════════════════════════
        // FEEDBACK
        // ═══════════════════════════════════════════════════════
        function showFB(cls, title, expl) {
            const fb = g('feedback');
            if (!fb) return;
            fb.className = cls + ' show';
            g('fbHead').textContent = title;
            g('fbExpl').textContent = expl;
        }

        function hideFB() {
            const fb = g('feedback');
            if (fb) fb.className = '';
        }

        // ═══════════════════════════════════════════════════════
        // LEADERBOARD
        // ═══════════════════════════════════════════════════════
        function updateBoard(scores) {
            if (!scores || !scores.length) return;
            const list = g('scoreList');
            scores.forEach((s, rank) => {
                const row = g(`sr-${s.user_id}`), pts = g(`pts-${s.user_id}`);
                if (!row || !pts) return;
                const prev = parseInt(pts.textContent) || 0;
                if (s.score !== prev) animNum(pts, prev, s.score);
                if (list) list.appendChild(row);
                let stk = row.querySelector('.sr-streak');
                if (s.streak >= 3) {
                    if (!stk) {
                        stk = document.createElement('span');
                        stk.className = 'sr-streak';
                        row.insertBefore(stk, pts);
                    }
                    stk.textContent = `🔥${s.streak}`;
                } else if (stk) stk.remove();
            });
            if (CFG.mode === 'team') {
                const a = scores.filter(s => s.team === 'a').reduce((x, s) => x + s.score, 0);
                const b = scores.filter(s => s.team === 'b').reduce((x, s) => x + s.score, 0);
                if (g('tsA')) g('tsA').textContent = a;
                if (g('tsB')) g('tsB').textContent = b;
            }
        }

        function animNum(el, from, to) {
            const d = 350, s = performance.now();
            const f = n => {
                const p = Math.min((n - s) / d, 1);
                el.textContent = Math.round(from + (to - from) * p);
                if (p < 1) requestAnimationFrame(f);
            };
            requestAnimationFrame(f);
        }

        // ═══════════════════════════════════════════════════════
        // END BATTLE
        // ═══════════════════════════════════════════════════════
        function endBattle() {
            if (S.done) return;
            S.done = true;
            clearTimer();
            clearNextBar();
            stopPoll();
            g('waitPanel').style.display = 'none';
            g('qPanel').style.display = 'none';
            g('finishedPanel').style.display = 'flex';
            snd('victory');
            if (typeof confetti === 'function') confetti({ particleCount: 120, spread: 70, origin: { y: .5 } });
            setTimeout(() => { window.location.href = R.results; }, 3500);
        }

        // ═══════════════════════════════════════════════════════
        // COUNTDOWN
        // ═══════════════════════════════════════════════════════
        function showCountdown(cb) {
            const ov = g('cdOverlay'), num = g('cdNum');
            ov.classList.add('show');
            let n = 3;
            num.textContent = n;
            snd('beep');
            const iv = setInterval(() => {
                n--;
                if (n > 0) {
                    num.textContent = n;
                    // FIX: re-trigger animation so each number animates in
                    num.style.animation = 'none';
                    num.offsetHeight; // reflow
                    num.style.animation = '';
                    snd('beep');
                } else {
                    clearInterval(iv);
                    ov.classList.remove('show');
                    cb();
                }
            }, 1000);
        }

        // ═══════════════════════════════════════════════════════
        // ANTI-CHEAT
        // ═══════════════════════════════════════════════════════
        if (CFG.antiCheat) {
            let blurT;
            document.addEventListener('visibilitychange', () => {
                if (!S.running || S.disq || S.done) return;
                if (document.hidden) doVio('tab_switch');
            });
            window.addEventListener('blur', () => {
                if (!S.running || S.disq || S.done) return;
                blurT = setTimeout(() => doVio('window_blur'), 600);
            });
            window.addEventListener('focus', () => clearTimeout(blurT));
        }

        async function doVio(type) {
            S.vios++;
            try {
                const r = await fetch(R.violation, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CFG.csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ room_code: CFG.roomCode, type }),
                });
                const d = await r.json();
                if (d.disqualified) {
                    S.disq = true;
                    clearTimer();
                }
                g('vioMsg').textContent = type === 'tab_switch' ? 'Tab switch detected!' : 'Window minimized!';
                g('vioDetail').textContent = d.disqualified ? '⛔ You are disqualified.' : `Warning ${d.violations}/3 · −10 pts`;
                g('vioOverlay').classList.add('show');
                snd('vio');
            } catch (e) {}
        }

        function dismissVio() {
            g('vioOverlay').classList.remove('show');
        }

        // ═══════════════════════════════════════════════════════
        // SOUND
        // ═══════════════════════════════════════════════════════
        let AC;

        function getAC() {
            if (!AC) AC = new(window.AudioContext || window.webkitAudioContext)();
            return AC;
        }

        function snd(name) {
            try {
                const ac = getAC(), t = ac.currentTime;
                const tone = (f, d, tp = 'sine', v = .25) => {
                    const o = ac.createOscillator(), g = ac.createGain();
                    o.connect(g); g.connect(ac.destination);
                    o.type = tp; o.frequency.value = f;
                    g.gain.setValueAtTime(v, t);
                    g.gain.exponentialRampToValueAtTime(.001, t + d);
                    o.start(t); o.stop(t + d + .01);
                };
                switch (name) {
                    case 'tick':    tone(880, .08, 'triangle', .06); break;
                    case 'urgent':  tone(660, .07, 'square', .12); break;
                    case 'beep':    tone(1047, .12, 'sine', .3); break;
                    case 'timeout': tone(220, .4, 'triangle', .15); break;
                    case 'vio':     tone(150, .5, 'square', .18); break;
                    case 'correct':
                        [[523,.18],[659,.18],[784,.18],[1047,.3]].forEach(([f,d],i) => {
                            const o = ac.createOscillator(), gg = ac.createGain();
                            o.connect(gg); gg.connect(ac.destination);
                            o.type = 'sine'; o.frequency.value = f;
                            const st = t + i * .11;
                            gg.gain.setValueAtTime(.25, st);
                            gg.gain.exponentialRampToValueAtTime(.001, st + d);
                            o.start(st); o.stop(st + d + .01);
                        });
                        break;
                    case 'wrong': {
                        const o = ac.createOscillator(), gg = ac.createGain();
                        o.connect(gg); gg.connect(ac.destination);
                        o.type = 'sawtooth';
                        o.frequency.setValueAtTime(280, t);
                        o.frequency.exponentialRampToValueAtTime(160, t + .3);
                        gg.gain.setValueAtTime(.18, t);
                        gg.gain.exponentialRampToValueAtTime(.001, t + .3);
                        o.start(t); o.stop(t + .32);
                        break;
                    }
                    case 'victory':
                        [[523,.25],[659,.25],[784,.25],[1047,.35],[1319,.5]].forEach(([f,d],i) => {
                            const o = ac.createOscillator(), gg = ac.createGain();
                            o.connect(gg); gg.connect(ac.destination);
                            o.type = 'sine'; o.frequency.value = f;
                            const st = t + i * .13;
                            gg.gain.setValueAtTime(.3, st);
                            gg.gain.exponentialRampToValueAtTime(.001, st + d);
                            o.start(st); o.stop(st + d + .01);
                        });
                        break;
                }
            } catch (e) {}
        }

        // ═══════════════════════════════════════════════════════
        // PARTICLES
        // ═══════════════════════════════════════════════════════
        const cvs = document.getElementById('fxCanvas');
        const cx = cvs.getContext('2d');
        let pts2 = [];
        (() => { cvs.width = innerWidth; cvs.height = innerHeight; })();
        window.addEventListener('resize', () => { cvs.width = innerWidth; cvs.height = innerHeight; });

        function spawnParts(btn, color) {
            if (!btn) return;
            const r = btn.getBoundingClientRect(), bx = r.left + r.width / 2, by = r.top + r.height / 2;
            for (let i = 0; i < 20; i++) {
                const a = (Math.PI * 2 / 20) * i + (Math.random() - .5) * .5, sp = 2 + Math.random() * 5;
                pts2.push({ x: bx, y: by, vx: Math.cos(a) * sp, vy: Math.sin(a) * sp - 1, r: 3 + Math.random() * 4, a: 1, color });
            }
            if (!looping2) loop2();
        }
        let looping2 = false;

        function loop2() {
            looping2 = true;
            cx.clearRect(0, 0, cvs.width, cvs.height);
            pts2 = pts2.filter(p => p.a > .02);
            pts2.forEach(p => {
                p.x += p.vx; p.y += p.vy; p.vy += .18; p.a -= .03;
                cx.save(); cx.globalAlpha = p.a; cx.fillStyle = p.color;
                cx.beginPath(); cx.arc(p.x, p.y, p.r, 0, Math.PI * 2); cx.fill(); cx.restore();
            });
            if (pts2.length) requestAnimationFrame(loop2);
            else { looping2 = false; cx.clearRect(0, 0, cvs.width, cvs.height); }
        }

        // ═══════════════════════════════════════════════════════
        // FLOATING TEXT
        // ═══════════════════════════════════════════════════════
        function floatFX(text, color, ox = 0) {
            const d = document.createElement('div');
            d.className = 'float-fx';
            d.textContent = text;
            d.style.color = color;
            d.style.left = (innerWidth / 2 - 60 + ox) + 'px';
            d.style.top = (innerHeight / 2 - 60) + 'px';
            document.body.appendChild(d);
            setTimeout(() => d.remove(), 950);
        }

        // ═══════════════════════════════════════════════════════
        // UTILS
        // ═══════════════════════════════════════════════════════
        function g(id) { return document.getElementById(id); }

        function esc(s) {
            return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }
    </script>
@endsection