{{-- resources/views/institution/battle/arena.blade.php --}}
{{--
    WHO SEES THIS:
      isCreator=true        → HOST: full split-screen watcher with start controls
      isCreator=false,
      isObserverAdmin=true  → OBSERVER ADMIN (school 2/3): same split-screen, no start button

    Students are routed to student.battle.arena instead (via controller).
--}}
@extends('institution.layout.master')
@section('title', 'Battle ⚔️ ' . $battle->code)

@section('content')
    <style>
        body,
        html {
            overflow: hidden !important
        }

        :root {
            --gold: #f5c842;
            --gold-dim: rgba(245, 200, 66, .12);
            --green: #00e396;
            --red: #ff4d6a;
            --ice: #00d4ff;
            --fire: #ff6b35;
        }

        /* ✅ FIX: Account for sidebar margin — start at left:var(--sidebar-w) */
        #arena {
            position: fixed;
            top: 0;
            left: var(--sidebar-w);
            right: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            background: var(--bg);
            z-index: 500;
        }

        /* ── TOP BAR ── */
        #topBar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            background: rgba(0, 0, 0, .5);
            border-bottom: 1px solid rgba(255, 255, 255, .07);
            backdrop-filter: blur(14px);
            flex-shrink: 0;
            min-height: 48px;
        }

        .tb-code {
            font-family: var(--fh);
            font-weight: 900;
            font-size: .8rem;
            color: var(--gold);
            letter-spacing: 2px;
            background: var(--gold-dim);
            padding: 3px 10px;
            border-radius: 12px;
            flex-shrink: 0;
        }

        .tb-badge {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: .7rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .tb-badge.host {
            background: rgba(255, 165, 0, .15);
            color: #ffa833;
            border: 1px solid rgba(255, 165, 0, .3);
        }

        .tb-badge.observer {
            background: rgba(0, 212, 255, .15);
            color: var(--ice);
            border: 1px solid rgba(0, 212, 255, .3);
        }

        .tb-prog {
            flex: 1;
            height: 4px;
            background: rgba(255, 255, 255, .07);
            border-radius: 2px;
            overflow: hidden;
        }

        .tb-fill {
            height: 100%;
            background: var(--gradient);
            border-radius: 2px;
            transition: width .5s ease;
        }

        .tb-ql {
            font-size: .76rem;
            font-weight: 800;
            color: var(--muted);
            flex-shrink: 0;
        }

        /* ══════════════ SPLIT-SCREEN LAYOUT ══════════════ */
        #hostArena {
            display: flex;
            flex: 1;
            overflow: hidden;
            flex-direction: column;
        }

        /* ── Institution rank banner (top strip above lanes) ── */
        #rankBanner {
            display: none;
            flex-shrink: 0;
            background: rgba(0, 0, 0, .3);
            border-bottom: 1px solid rgba(255, 255, 255, .07);
            padding: 8px 16px;
        }

        #rankBanner.show {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .rb-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 800;
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .08);
            transition: all .4s;
        }

        .rb-item.rank-1 {
            background: rgba(255, 215, 0, .1);
            border-color: rgba(255, 215, 0, .3);
            color: #ffd700;
        }

        .rb-item.rank-2 {
            background: rgba(192, 192, 192, .08);
            border-color: rgba(192, 192, 192, .25);
            color: #c0c0c0;
        }

        .rb-item.rank-3 {
            background: rgba(205, 127, 50, .08);
            border-color: rgba(205, 127, 50, .25);
            color: #cd7f32;
        }

        .rb-score {
            font-family: var(--fh);
            font-size: 1rem;
            font-weight: 900;
        }

        .rb-sep {
            color: rgba(255, 255, 255, .15);
            font-size: .9rem;
        }

        #lanesWrap {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* ── Per-institution lane ── */
        .inst-lane {
            flex: 1;
            display: flex;
            flex-direction: column;
            border-right: 2px solid rgba(255, 255, 255, .06);
            overflow: hidden;
            transition: border-color .4s;
        }

        .inst-lane:last-child {
            border-right: none;
        }

        .inst-lane.rank-1 {
            border-right-color: rgba(255, 215, 0, .35);
        }

        .inst-lane.rank-2 {
            border-right-color: rgba(192, 192, 192, .25);
        }

        /* ── Lane header: institution score area ── */
        .lane-hdr {
            padding: 12px 16px;
            flex-shrink: 0;
            background: rgba(0, 0, 0, .25);
            border-bottom: 2px solid rgba(255, 255, 255, .06);
            transition: border-color .4s, background .4s;
        }

        .lane-hdr.rank-1 {
            border-bottom-color: rgba(255, 215, 0, .5);
            background: rgba(255, 215, 0, .04);
        }

        .lane-hdr.rank-2 {
            border-bottom-color: rgba(192, 192, 192, .3);
        }

        .lane-hdr.rank-3 {
            border-bottom-color: rgba(205, 127, 50, .25);
        }

        .lane-top {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .lane-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            font-weight: 900;
            color: #fff;
            flex-shrink: 0;
        }

        .lane-icon.s1 {
            background: linear-gradient(135deg, #7c5cfc, #5a3dd4);
        }

        .lane-icon.s2 {
            background: linear-gradient(135deg, #00d4ff, #0099cc);
        }

        .lane-icon.s3 {
            background: linear-gradient(135deg, #f5c842, #d4a520);
        }

        .lane-inst-name {
            font-weight: 900;
            font-size: .9rem;
            flex: 1;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .lane-rank-em {
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        /* Big institution score */
        .lane-score {
            font-family: var(--fh);
            font-size: 2.2rem;
            font-weight: 900;
            line-height: 1;
            text-align: center;
            margin-bottom: 8px;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Score bar — visual comparison */
        .lane-score-bar-wrap {
            height: 4px;
            background: rgba(255, 255, 255, .06);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .lane-score-bar {
            height: 100%;
            border-radius: 2px;
            transition: width .6s ease;
            width: 0%;
        }

        .s1 .lane-score-bar {
            background: linear-gradient(90deg, #7c5cfc, #5a3dd4);
        }

        .s2 .lane-score-bar {
            background: linear-gradient(90deg, #00d4ff, #0099cc);
        }

        .s3 .lane-score-bar {
            background: linear-gradient(90deg, #f5c842, #d4a520);
        }

        .lane-stats {
            display: flex;
            gap: 6px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .lstat {
            text-align: center;
            background: rgba(255, 255, 255, .04);
            border-radius: 6px;
            padding: 5px 8px;
            flex: 1;
            min-width: 44px;
        }

        .lstat .v {
            font-weight: 800;
            font-size: .85rem;
        }

        .lstat .l {
            font-size: .58rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        /* ── Student rows inside lane body ── */
        .lane-body {
            flex: 1;
            overflow-y: auto;
            padding: 6px;
        }

        .lane-body::-webkit-scrollbar {
            width: 3px;
        }

        .lane-body::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .08);
            border-radius: 2px;
        }

        .stu-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            border-radius: 8px;
            margin-bottom: 3px;
            transition: background .2s;
            border: 1px solid transparent;
            animation: stuIn .2s ease;
        }

        @keyframes stuIn {
            from {
                opacity: 0;
                transform: translateX(-6px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .stu-row.top {
            background: rgba(255, 215, 0, .06);
            border-color: rgba(255, 215, 0, .15);
        }

        .stu-av {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .62rem;
            font-weight: 800;
            color: #fff;
            flex-shrink: 0;
        }

        .stu-rank {
            font-size: .6rem;
            font-weight: 900;
            color: var(--muted);
            width: 18px;
            text-align: center;
            flex-shrink: 0;
        }

        .stu-name {
            flex: 1;
            font-size: .76rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .stu-streak {
            font-size: .68rem;
            color: #ff6b35;
            flex-shrink: 0;
        }

        .stu-pts {
            font-family: var(--fh);
            font-weight: 900;
            font-size: .82rem;
            color: var(--accent);
            flex-shrink: 0;
        }

        .stu-disq {
            font-size: .62rem;
            color: var(--red);
            flex-shrink: 0;
        }

        .stu-correct {
            font-size: .62rem;
            color: var(--green);
            flex-shrink: 0;
        }

        /* ── Host Q bar (bottom strip) ── */
        #hostQBar {
            display: none;
            align-items: center;
            gap: 14px;
            padding: 10px 18px;
            background: rgba(0, 0, 0, .35);
            border-top: 1px solid rgba(255, 255, 255, .07);
            flex-shrink: 0;
        }

        #hostQBar.show {
            display: flex;
        }

        .hq-tc {
            position: relative;
            width: 46px;
            height: 46px;
            flex-shrink: 0;
        }

        .hq-tc svg {
            transform: rotate(-90deg);
        }

        .hqtc-bg {
            fill: none;
            stroke: rgba(255, 255, 255, .08);
            stroke-width: 5;
        }

        .hqtc-fill {
            fill: none;
            stroke-width: 5;
            stroke-linecap: round;
            stroke-dasharray: 132;
            stroke-dashoffset: 0;
            transition: stroke .4s;
        }

        .hq-num {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--fh);
            font-size: .95rem;
            font-weight: 900;
        }

        .hq-text {
            flex: 1;
            font-size: .82rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .hq-meta {
            font-size: .7rem;
            color: var(--muted);
            margin-top: 2px;
        }

        .hq-answered {
            font-size: .72rem;
            color: var(--muted);
            flex-shrink: 0;
            text-align: right;
        }

        /* ── Wait/Fin panels ── */
        .wait-panel,
        .fin-panel {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            text-align: center;
            gap: 14px;
            padding: 40px 24px;
        }

        .wait-panel.show,
        .fin-panel.show {
            display: flex;
        }

        .big-icon {
            font-size: 3.5rem;
            animation: floatLoop 3s ease-in-out infinite;
        }

        @keyframes floatLoop {

            0%,
            100% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-10px)
            }
        }

        /* ── Countdown overlay ── */
        #cdOverlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9990;
            background: rgba(0, 0, 0, .88);
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        #cdOverlay.show {
            display: flex;
        }

        .cd-num {
            font-family: var(--fh);
            font-size: 7rem;
            font-weight: 900;
            line-height: 1;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: cdPop .4s cubic-bezier(.34, 1.56, .64, 1);
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

        @keyframes spin {
            to {
                transform: rotate(360deg)
            }
        }
    </style>

    <div id="arena">

        {{-- ── TOP BAR ── --}}
        <div id="topBar">
            <span class="tb-code">{{ $battle->code }}</span>
            @if ($isCreator)
                <span class="tb-badge host">👑 HOST</span>
            @else
                <span class="tb-badge observer">👁 OBSERVER</span>
            @endif
            <div class="tb-prog">
                <div class="tb-fill" id="progFill" style="width:0%"></div>
            </div>
            <span class="tb-ql" id="qLabel">Q 0/{{ $battle->total_questions }}</span>
        </div>

        {{-- ── HOST / OBSERVER ARENA BODY ── --}}
        <div id="hostArena">

            {{-- Rank banner --}}
            <div id="rankBanner"></div>

            {{-- Wait state --}}
            <div class="wait-panel show" id="waitPanel">
                <div class="big-icon">⚔️</div>
                <h2 style="font-family:var(--fh);font-weight:900;font-size:1.4rem">
                    @if ($isCreator)
                        Watching the battle…
                    @else
                        Observing the battle…
                    @endif
                </h2>
                <p style="color:var(--muted);font-size:.84rem">
                    @if ($isCreator)
                        The battle has started. You are the host — you watch, not play.
                    @else
                        {{ optional(\App\Models\Institution::find(optional(\App\Models\Institution::where('user_id', Auth::id())->first())->id))->name ?? 'Your institution' }}
                        is competing. Watch the live scores below.
                    @endif
                </p>
                @if ($isCreator)
                    <a href="{{ route('institution.battle.setup-page', $battle->code) }}" class="btn btn-ghost"
                        style="margin-top:4px">← Back to Lobby</a>
                @endif
            </div>

            {{-- Institution lanes --}}
            <div id="lanesWrap" style="display:none">
                @php
                    $instList = \App\Models\Institution::whereIn(
                        'id',
                        $battle->participating_institutions ?? [],
                    )->get();
                    $laneIcons = ['s1', 's2', 's3'];
                    $laneEmoji = ['🥇', '🥈', '🥉'];
                @endphp
                @foreach ($instList as $li => $inst)
                    <div class="inst-lane {{ $laneIcons[$li] ?? 's1' }}" id="lane-{{ $inst->id }}"
                        data-inst="{{ $inst->id }}">
                        <div class="lane-hdr" id="lhdr-{{ $inst->id }}">
                            <div class="lane-top">
                                <div class="lane-icon {{ $laneIcons[$li] ?? 's1' }}">
                                    {{ strtoupper(substr($inst->name, 0, 1)) }}</div>
                                <div style="flex:1;min-width:0">
                                    <div class="lane-inst-name">{{ $inst->name }}</div>
                                    <div style="font-size:.65rem;color:var(--muted)">School {{ $li + 1 }}</div>
                                </div>
                                <div class="lane-rank-em" id="lrank-{{ $inst->id }}">{{ $laneEmoji[$li] ?? '' }}</div>
                            </div>
                            <div class="lane-score" id="lscore-{{ $inst->id }}">0</div>
                            <div class="lane-score-bar-wrap">
                                <div class="lane-score-bar" id="lbar-{{ $inst->id }}"></div>
                            </div>
                            <div class="lane-stats">
                                <div class="lstat">
                                    <div class="v" id="lstu-{{ $inst->id }}">0</div>
                                    <div class="l">Players</div>
                                </div>
                                <div class="lstat">
                                    <div class="v" id="lcor-{{ $inst->id }}">0</div>
                                    <div class="l">Correct</div>
                                </div>
                                <div class="lstat">
                                    <div class="v" id="lacc-{{ $inst->id }}">—</div>
                                    <div class="l">Accuracy</div>
                                </div>
                                <div class="lstat">
                                    <div class="v" id="lavg-{{ $inst->id }}">0</div>
                                    <div class="l">Avg Pts</div>
                                </div>
                            </div>
                        </div>
                        <div class="lane-body" id="lbody-{{ $inst->id }}">
                            <div style="text-align:center;padding:20px;color:var(--muted);font-size:.78rem">Waiting for
                                scores…</div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Finished panel --}}
            <div class="fin-panel" id="finishedPanel">
                <div class="big-icon">🏆</div>
                <h2 style="font-family:var(--fh);font-weight:900;font-size:1.4rem">Battle Complete!</h2>
                <a href="{{ route('institution.battle.results', $battle->code) }}" class="btn btn-grad">View Results →</a>
            </div>

            {{-- Q bar at bottom --}}
            <div id="hostQBar">
                <div class="hq-tc">
                    <svg width="46" height="46" viewBox="0 0 60 60">
                        <circle class="hqtc-bg" cx="30" cy="30" r="21" />
                        <circle class="hqtc-fill" id="tcFill" cx="30" cy="30" r="21"
                            stroke="var(--accent)" />
                    </svg>
                    <div class="hq-num" id="tcNum">—</div>
                </div>
                <div style="flex:1;min-width:0">
                    <div class="hq-text" id="hostQText">Waiting for question…</div>
                    <div class="hq-meta" id="hostQMeta"></div>
                </div>
                <div class="hq-answered" id="hostAnswerCount"></div>
            </div>
        </div>

    </div>{{-- /arena --}}

    {{-- Countdown overlay --}}
    <div id="cdOverlay">
        <p style="color:var(--muted);font-size:.9rem;margin-bottom:4px">Battle starting in</p>
        <div class="cd-num" id="cdNum">3</div>
        <p style="color:var(--muted);font-size:.82rem;margin-top:8px">⚔️ Get ready!</p>
    </div>

    <script src="https://js.pusher.com/8.0/pusher.min.js"></script>
    <script>
        const CFG = {
            code: '{{ $battle->code }}',
            myId: {{ Auth::id() }},
            isCreator: {{ $isCreator ? 'true' : 'false' }},
            isObserver: {{ $isObserverAdmin ?? false ? 'true' : 'false' }},
            qTimer: {{ $battle->question_timer }},
            totalQ: {{ $battle->total_questions }},
            csrf: '{{ csrf_token() }}',
            status: '{{ $battle->status }}',
            instIds: @json($battle->participating_institutions ?? []),
        };

        const RANK_EM = ['🥇', '🥈', '🥉'];
        const LANE_ICONS = ['s1', 's2', 's3'];

        const R = {
            results: '{{ route('institution.battle.results', $battle->code) }}',
            poll: '/institution/battle/arena-state/{{ $battle->code }}',
        };

        const S = {
            qs: [],
            qIdx: -1,
            timeLeft: CFG.qTimer,
            tTick: null,
            pTick: null,
            running: false,
            done: false,
            wsOk: false,
            startHandled: false,
            maxScore: 1,
        };

        // Pre-load questions if battle already in progress
        @if ($battle->status === 'in_progress')
            S.qs = @json(collect($battle->quiz->questions ?? [])->map(fn($q) => ['question' => $q['question'], 'options' => $q['options'], 'topic' => $q['topic'] ?? ''])->values());
            S.running = true;
            S.startHandled = true;
            document.addEventListener('DOMContentLoaded', () => openHostBattle());
        @endif

        // ── Pusher / WebSocket ──────────────────────────────────────────────────────
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
            ch = pusher.subscribe(`institution-battle.${CFG.code}`);

            ch.bind('institution-battle.started', d => {
                if (S.startHandled) return;
                S.startHandled = true;
                S.qs = d.questions;
                S.running = true;
                S.wsOk = true;
                stopPoll();
                showCountdown(() => openHostBattle());
            });

            ch.bind('institution-answer.submitted', d => {
                S.wsOk = true;
                updateLanes(d.scores);
            });

            ch.bind('institution-next.question', d => {
                S.wsOk = true;
                if (d.questionIndex < S.qs.length) hostShowQ(d.questionIndex);
                else endBattle();
            });

            ch.bind('institution-battle.finished', () => {
                S.wsOk = true;
                endBattle();
            });

        } catch (e) {
            console.warn('WS init error', e);
        }

        // ── Polling fallback ─────────────────────────────────────────────────────────
        function startPoll() {
            if (!S.pTick) S.pTick = setInterval(doPoll, 4000);
        }

        function stopPoll() {
            clearInterval(S.pTick);
            S.pTick = null;
        }
        startPoll();

        async function doPoll() {
            try {
                const r = await fetch(R.poll, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': CFG.csrf
                    }
                });
                const d = await r.json();
                if (!d.success) return;
                if (d.scores) updateLanes(d.scores);
                if (d.status === 'finished' && !S.done) endBattle();
                if (!S.running && d.status === 'in_progress') location.reload();
            } catch (e) {}
        }

        // ── HOST / OBSERVER BATTLE OPEN ──────────────────────────────────────────────
        function openHostBattle() {
            g('waitPanel').classList.remove('show');
            const lw = g('lanesWrap');
            if (lw) lw.style.display = 'flex';
            const qb = g('hostQBar');
            if (qb) qb.classList.add('show');
            const rb = g('rankBanner');
            if (rb) rb.classList.add('show');
            hostShowQ(0);
        }

        function hostShowQ(idx) {
            if (!S.qs[idx]) return;
            S.qIdx = idx;
            const q = S.qs[idx];

            g('progFill').style.width = ((idx + 1) / CFG.totalQ * 100) + '%';
            g('qLabel').textContent = `Q ${idx + 1}/${CFG.totalQ}`;
            if (g('hostQText')) g('hostQText').textContent = `Q${idx + 1}: ${q.question}`;
            if (g('hostQMeta')) g('hostQMeta').textContent = `Topic: ${q.topic || 'General'}`;
            startTimer();
        }

        // ── Timer (host view — just display, no submission) ──────────────────────────
        function startTimer() {
            clearTimer();
            S.timeLeft = CFG.qTimer;
            drawTimer(S.timeLeft);
            S.tTick = setInterval(() => {
                S.timeLeft--;
                drawTimer(S.timeLeft);
                if (S.timeLeft <= 0) {
                    clearTimer();
                }
            }, 1000);
        }

        function clearTimer() {
            clearInterval(S.tTick);
            S.tTick = null;
        }

        function drawTimer(t) {
            const pct = Math.max(0, t / CFG.qTimer);
            const off = 132 * (1 - pct);
            const clr = pct > .5 ? 'var(--accent)' : pct > .25 ? 'orange' : 'var(--red)';
            const fill = g('tcFill'),
                num = g('tcNum');
            if (fill) {
                fill.style.strokeDashoffset = off;
                fill.style.stroke = clr;
            }
            if (num) {
                num.textContent = t;
                num.style.color = pct > .25 ? 'var(--text)' : 'var(--red)';
            }
        }

        // ── Lane updates ─────────────────────────────────────────────────────────────
        function updateLanes(scores) {
            if (!scores || !scores.length) return;

            // Aggregate per institution
            const byInst = {};
            scores.forEach(s => {
                const id = s.institution_id;
                if (!byInst[id]) byInst[id] = {
                    name: s.institution_name,
                    score: 0,
                    correct: 0,
                    students: [],
                    total: 0
                };
                byInst[id].score += s.score;
                byInst[id].correct += s.correct;
                byInst[id].students.push(s);
                byInst[id].total++;
            });

            // Find max score for bar scaling
            const allScores = Object.values(byInst).map(x => x.score);
            S.maxScore = Math.max(1, ...allScores);

            // Sort by score descending
            const ranked = Object.entries(byInst).sort((a, b) => b[1].score - a[1].score);

            // Update rank banner
            const banner = g('rankBanner');
            if (banner) {
                banner.innerHTML = ranked.map(([id, d], rank) => `
            <div class="rb-item rank-${rank+1}">
                ${RANK_EM[rank] || ''} <span>${escH(d.name)}</span>
                <span class="rb-score">${d.score}</span>
            </div>
            ${rank < ranked.length - 1 ? '<span class="rb-sep">›</span>' : ''}
        `).join('');
            }

            // Update answer count in Q bar
            const totalAnswered = scores.filter(s => !s.disqualified).length;
            const totalPlayers = scores.length;
            if (g('hostAnswerCount')) {
                g('hostAnswerCount').innerHTML =
                    `<div style="font-size:.7rem;color:var(--muted)">Q${S.qIdx+1} answered</div><div style="font-weight:900">${totalAnswered}/${totalPlayers}</div>`;
            }

            ranked.forEach(([instId, data], rank) => {
                // Header classes
                const hdr = g('lhdr-' + instId);
                if (hdr) hdr.className = 'lane-hdr rank-' + (rank + 1);

                // Lane border
                const lane = g('lane-' + instId);
                if (lane) {
                    lane.classList.remove('rank-1', 'rank-2', 'rank-3');
                    lane.classList.add('rank-' + (rank + 1));
                }

                // Rank emoji
                if (g('lrank-' + instId)) g('lrank-' + instId).textContent = RANK_EM[rank] || '';

                // Score (animated)
                animNum(g('lscore-' + instId), parseInt(g('lscore-' + instId)?.textContent) || 0, data.score);

                // Score bar
                const bar = g('lbar-' + instId);
                if (bar) bar.style.width = Math.round((data.score / S.maxScore) * 100) + '%';

                // Stats
                if (g('lstu-' + instId)) g('lstu-' + instId).textContent = data.total;
                if (g('lcor-' + instId)) g('lcor-' + instId).textContent = data.correct;

                const answeredTotal = data.total * (S.qIdx + 1 || 1);
                const acc = answeredTotal ? Math.round((data.correct / answeredTotal) * 100) : 0;
                if (g('lacc-' + instId)) g('lacc-' + instId).textContent = acc + '%';

                const avg = data.total ? Math.round(data.score / data.total) : 0;
                if (g('lavg-' + instId)) g('lavg-' + instId).textContent = avg;

                // Student rows
                const body = g('lbody-' + instId);
                if (!body) return;

                const sorted = [...data.students].sort((a, b) => b.score - a.score);
                body.innerHTML = sorted.map((s, ri) => `
            <div class="stu-row ${ri === 0 ? 'top' : ''}">
                <span class="stu-rank">${ri === 0 ? '🥇' : '#' + (ri + 1)}</span>
                <div class="stu-av">${escH(s.avatar || (s.name || '?')[0])}</div>
                <span class="stu-name">${escH(s.name)}</span>
                ${s.correct > 0 ? `<span class="stu-correct">✓${s.correct}</span>` : ''}
                ${s.streak >= 3 ? `<span class="stu-streak">🔥${s.streak}</span>` : ''}
                ${s.disqualified ? `<span class="stu-disq">⛔</span>` : ''}
                <span class="stu-pts">${s.score}</span>
            </div>
        `).join('');
            });
        }

        // ── End ──────────────────────────────────────────────────────────────────────
        function endBattle() {
            if (S.done) return;
            S.done = true;
            clearTimer();
            stopPoll();
            g('waitPanel').classList.remove('show');
            const lw = g('lanesWrap');
            if (lw) lw.style.display = 'none';
            const qb = g('hostQBar');
            if (qb) qb.classList.remove('show');
            const rb = g('rankBanner');
            if (rb) rb.classList.remove('show');
            const fp = g('finishedPanel');
            if (fp) fp.classList.add('show');
            setTimeout(() => window.location.href = R.results, 3500);
        }

        // ── Countdown overlay ─────────────────────────────────────────────────────────
        function showCountdown(cb) {
            const ov = g('cdOverlay'),
                num = g('cdNum');
            ov.classList.add('show');
            let n = 3;
            num.textContent = n;
            const iv = setInterval(() => {
                n--;
                if (n > 0) {
                    num.textContent = n;
                    num.style.animation = 'none';
                    num.offsetHeight;
                    num.style.animation = '';
                } else {
                    clearInterval(iv);
                    ov.classList.remove('show');
                    cb();
                }
            }, 1000);
        }

        // ── Helpers ───────────────────────────────────────────────────────────────────
        function animNum(el, from, to) {
            if (!el) return;
            const dur = 400,
                s = performance.now();
            const f = n => {
                const p = Math.min((n - s) / dur, 1);
                el.textContent = Math.round(from + (to - from) * p);
                if (p < 1) requestAnimationFrame(f);
            };
            requestAnimationFrame(f);
        }

        function g(id) {
            return document.getElementById(id);
        }

        function escH(s) {
            return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }
    </script>
@endsection
