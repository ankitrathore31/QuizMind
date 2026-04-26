{{-- resources/views/student/battle/lobby.blade.php --}}
@extends('student.layout.master')
@section('title', 'Battle Lobby · ' . $room->code)

@section('content')
    <style>
        /* ── Layout ─────────────────────────────────────────────── */
        .lobby-wrap {
            max-width: 720px;
            margin: 0 auto;
            padding: 32px 16px;
        }

        /* ── Room code display ──────────────────────────────────── */
        .room-code-big {
            font-family: var(--fh);
            font-size: 2.8rem;
            font-weight: 900;
            letter-spacing: .15em;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .copy-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
            margin-bottom: 6px;
        }

        /* ── Invite code box (FIX: shows code only, not full URL) ── */
        .invite-box {
            background: rgba(124, 92, 252, .05);
            border: 1px solid rgba(124, 92, 252, .18);
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .invite-code {
            flex: 1;
            font-family: var(--fh);
            font-weight: 900;
            font-size: 1.1rem;
            letter-spacing: .1em;
            color: var(--text);
        }

        .invite-label {
            font-size: .72rem;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .copy-btn {
            background: rgba(124, 92, 252, .12);
            border: 1px solid rgba(124, 92, 252, .25);
            color: var(--accent);
            padding: 5px 12px;
            border-radius: 7px;
            font-size: .75rem;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: .2s;
            flex-shrink: 0;
        }

        .copy-btn:hover {
            background: rgba(124, 92, 252, .22);
        }

        .copy-btn.copied {
            background: rgba(0, 227, 150, .12);
            border-color: rgba(0, 227, 150, .3);
            color: #00e396;
        }

        /* ── Player list ────────────────────────────────────────── */
        .players-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        @media(max-width:600px) {
            .players-grid {
                grid-template-columns: 1fr;
            }
        }

        .team-panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px;
        }

        .team-panel h3 {
            font-family: var(--fh);
            font-size: .84rem;
            font-weight: 800;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .all-panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px;
            margin-bottom: 16px;
        }

        .player-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 0;
            border-bottom: 1px solid var(--border2);
            transition: background .15s;
        }

        .player-row:last-child {
            border: none;
        }

        .player-av {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            font-weight: 800;
            color: #fff;
            flex-shrink: 0;
            text-transform: uppercase;
        }

        .player-name {
            font-size: .85rem;
            font-weight: 600;
            flex: 1;
        }

        .host-badge {
            font-size: .62rem;
            background: rgba(255, 165, 0, .15);
            color: orange;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
        }

        .ready-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            animation: pulseDot 1.5s infinite;
            flex-shrink: 0;
        }

        @keyframes pulseDot {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .3
            }
        }

        .empty-slot {
            color: var(--muted);
            font-size: .78rem;
            text-align: center;
            padding: 16px;
        }

        /* ── Start button ───────────────────────────────────────── */
        .start-btn {
            background: var(--gradient);
            color: #fff;
            border: none;
            padding: 16px;
            border-radius: var(--radius-sm);
            font-weight: 800;
            font-size: 1rem;
            width: 100%;
            cursor: pointer;
            font-family: var(--fh);
            transition: .2s;
            margin-bottom: 10px;
            letter-spacing: .02em;
        }

        .start-btn:hover:not(:disabled) {
            opacity: .9;
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(124, 92, 252, .35);
        }

        .start-btn:disabled {
            opacity: .4;
            cursor: not-allowed;
            transform: none;
        }

        .wait-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px;
            text-align: center;
        }

        /* ── Live indicator ─────────────────────────────────────── */
        .live-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(34, 197, 94, .1);
            border: 1px solid rgba(34, 197, 94, .25);
            color: #22c55e;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 700;
        }

        .live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #22c55e;
            animation: pulseDot 1.5s infinite;
        }

        /* ── Timer countdown (before start) ────────────────────── */
        .countdown-wrap {
            text-align: center;
            padding: 20px 0;
        }

        .countdown-num {
            font-family: var(--fh);
            font-size: 5rem;
            font-weight: 900;
            line-height: 1;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>

    <div class="lobby-wrap anim-fade">

        {{-- Header --}}
        <div style="text-align:center;margin-bottom:28px">
            <div style="font-size:3rem;margin-bottom:10px" class="anim-float">
                {{ $room->mode === 'team' ? '🏆' : ($room->mode === '1v1' ? '⚔️' : '👥') }}
            </div>
            <h1 style="font-family:var(--fh);font-size:1.5rem;font-weight:900;margin-bottom:6px">
                {{ $room->mode === '1v1' ? '1v1 Battle' : ($room->mode === 'group' ? 'Group Battle' : 'Team vs Team') }}
                Lobby
            </h1>
            <div style="margin-bottom:10px">
                <span class="live-badge"><span class="live-dot"></span>Waiting for players</span>
            </div>
            <div class="copy-wrap">
                <span class="room-code-big">{{ $room->code }}</span>
            </div>
            <p class="text-muted" style="font-size:.76rem">Share this code with your friends to join</p>
        </div>

        {{-- FIX: Invite boxes — show only the room CODE, copy button still copies the full link --}}
        @if ($room->mode === 'team')
            <div class="invite-box">
                <span class="invite-label" style="color:var(--accent)">Team A</span>
                <span class="invite-code">{{ $room->code }}</span>
                <button class="copy-btn" onclick="copyText('{{ $room->team_a_url }}', this)">📋 Copy Link</button>
            </div>
            <div class="invite-box">
                <span class="invite-label" style="color:#00e396">Team B</span>
                <span class="invite-code">{{ $room->code }}</span>
                <button class="copy-btn" onclick="copyText('{{ $room->team_b_url }}', this)">📋 Copy Link</button>
            </div>
        @else
            <div class="invite-box">
                <span class="invite-label" style="color:var(--muted)">Code</span>
                <span class="invite-code">{{ $room->code }}</span>
                <button class="copy-btn" onclick="copyText('{{ $room->invite_url }}', this)">📋 Copy Link</button>
            </div>
        @endif

        {{-- Players display --}}
        @if ($room->mode === 'team')
            <div class="players-grid">
                <div class="team-panel">
                    <h3>
                        <span
                            style="background:rgba(124,92,252,.15);padding:2px 8px;border-radius:5px;font-size:.72rem">A</span>
                        {{ $room->team_a_name }}
                        <span class="text-muted" style="font-size:.7rem;margin-left:auto" id="countA">
                            {{ $room->participants->where('team', 'a')->count() }}
                        </span>
                    </h3>
                    <div id="teamAList">
                        @forelse($room->participants->where('team','a') as $p)
                            <div class="player-row">
                                <div class="player-av">{{ substr($p->user->name, 0, 1) }}</div>
                                <span class="player-name">{{ $p->user->name }}
                                    @if ($p->user_id === $room->host_id)
                                        <span class="host-badge">HOST</span>
                                    @endif
                                </span>
                                <span class="ready-dot"></span>
                            </div>
                        @empty
                            <div class="empty-slot" id="emptyA">Waiting…</div>
                        @endforelse
                    </div>
                </div>
                <div class="team-panel">
                    <h3>
                        <span
                            style="background:rgba(0,227,150,.12);padding:2px 8px;border-radius:5px;font-size:.72rem">B</span>
                        {{ $room->team_b_name }}
                        <span class="text-muted" style="font-size:.7rem;margin-left:auto" id="countB">
                            {{ $room->participants->where('team', 'b')->count() }}
                        </span>
                    </h3>
                    <div id="teamBList">
                        @forelse($room->participants->where('team','b') as $p)
                            <div class="player-row">
                                <div class="player-av">{{ substr($p->user->name, 0, 1) }}</div>
                                <span class="player-name">{{ $p->user->name }}</span>
                                <span class="ready-dot"></span>
                            </div>
                        @empty
                            <div class="empty-slot" id="emptyB">Waiting…</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @else
            <div class="all-panel">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                    <div class="section-title" style="margin:0">
                        👥 Players (<span id="playerCount">{{ $room->participants->count() }}</span>)
                    </div>
                    <span class="live-badge" style="font-size:.68rem"><span class="live-dot"></span>Live</span>
                </div>
                <div id="allPlayersList">
                    @foreach ($room->participants as $p)
                        <div class="player-row" id="pr-{{ $p->user_id }}">
                            <div class="player-av">{{ substr($p->user->name, 0, 1) }}</div>
                            <span class="player-name">{{ $p->user->name }}
                                @if ($p->user_id === $room->host_id)
                                    <span class="host-badge">HOST</span>
                                @endif
                            </span>
                            <span class="ready-dot"></span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Start / Wait section --}}
        @if (Auth::id() === $room->host_id)
            <button class="start-btn" id="startBtn" {{ $room->participants->count() < 2 ? 'disabled' : '' }}
                onclick="startBattle()">
                ⚡ Start Battle Now!
            </button>
            <p id="startHint" class="text-muted" style="font-size:.78rem;text-align:center">
                {{ $room->participants->count() < 2 ? '⏳ Waiting for at least 1 more player…' : '✅ Ready! Click to start the battle.' }}
            </p>
        @else
            <div class="wait-card">
                <div style="font-size:2.5rem;margin-bottom:10px" class="anim-float">⏳</div>
                <p style="font-weight:700;margin-bottom:4px">Waiting for host to start</p>
                <p class="text-muted" style="font-size:.82rem">Keep this page open — you'll jump in automatically!</p>
            </div>
        @endif

        {{-- Countdown overlay (hidden until host starts) --}}
        <div id="countdownOverlay"
            style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9999;
        display:none;align-items:center;justify-content:center;flex-direction:column;text-align:center;">
            <p style="color:var(--muted);font-size:.9rem;margin-bottom:8px">Battle starting in</p>
            <div class="countdown-num" id="countdownNum">3</div>
            <p style="color:var(--muted);font-size:.82rem;margin-top:12px">Get ready! ⚔️</p>
        </div>

    </div>

    {{-- Pusher --}}
    <script src="https://js.pusher.com/8.0/pusher.min.js"></script>
    <script>
        const ROOM_CODE = '{{ $room->code }}';
        const IS_HOST = {{ Auth::id() === $room->host_id ? 'true' : 'false' }};
        const ROOM_MODE = '{{ $room->mode }}';
        const MY_ID = {{ Auth::id() }};
        const HOST_ID = {{ $room->host_id }};
        const CSRF = '{{ csrf_token() }}';

        const ROUTES = {
            start: '{{ route('student.battle.start') }}',
            arena: '{{ route('student.battle.arena', $room->code) }}',
            poll: '/student/battle/lobby-state/{{ $room->code }}',
        };

        // ── WebSocket via Pusher/Reverb ──────────────────────────
        let pusher, channel;

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

            channel = pusher.subscribe(`battle.${ROOM_CODE}`);

            // Lobby updated → refresh player list
            channel.bind('lobby.updated', data => {
                updateParticipants(data.participants, data.roomStatus);
                if (data.roomStatus === 'in_progress') {
                    redirectToArena();
                }
            });

            // Battle started → go to arena
            channel.bind('battle.started', () => redirectToArena());

            pusher.connection.bind('connected', () => console.log('WS connected'));
            pusher.connection.bind('error', err => console.warn('WS error', err));

        } catch (e) {
            console.warn('WebSocket init failed, falling back to polling', e);
        }

        // ── Polling fallback (runs always as safety net) ─────────
        let pollInterval = setInterval(pollLobbyState, 3000);

        async function pollLobbyState() {
            try {
                const res = await fetch(ROUTES.poll, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': CSRF
                    }
                });
                if (!res.ok) return;
                const d = await res.json();
                if (!d.success) return;

                updateParticipants(d.participants, d.status);

                if (d.status === 'in_progress') {
                    clearInterval(pollInterval);
                    redirectToArena();
                }
            } catch (e) {
                /* silent */ }
        }

        function redirectToArena() {
            clearInterval(pollInterval);
            showCountdown(() => window.location.href = ROUTES.arena);
        }

        // ── Update participant UI ─────────────────────────────────
        function updateParticipants(participants, status) {
            const count = participants.length;

            if (ROOM_MODE === 'team') {
                const teamA = participants.filter(p => p.team === 'a');
                const teamB = participants.filter(p => p.team === 'b');
                renderTeam('teamAList', teamA);
                renderTeam('teamBList', teamB);
                const ca = document.getElementById('countA');
                const cb = document.getElementById('countB');
                if (ca) ca.textContent = teamA.length;
                if (cb) cb.textContent = teamB.length;
            } else {
                renderAll('allPlayersList', participants);
                const ce = document.getElementById('playerCount');
                if (ce) ce.textContent = count;
            }

            // Update start button (host only)
            const startBtn = document.getElementById('startBtn');
            const startHint = document.getElementById('startHint');
            if (startBtn) {
                startBtn.disabled = count < 2;
                if (startHint) {
                    startHint.textContent = count < 2 ?
                        `⏳ Waiting for at least 1 more player… (${count} joined)` :
                        `✅ ${count} players ready — click to start!`;
                }
            }
        }

        function renderTeam(containerId, players) {
            const wrap = document.getElementById(containerId);
            if (!wrap) return;
            if (!players.length) {
                wrap.innerHTML = '<div class="empty-slot">Waiting for players…</div>';
                return;
            }
            wrap.innerHTML = players.map(p => `
        <div class="player-row">
            <div class="player-av">${esc(p.name[0].toUpperCase())}</div>
            <span class="player-name">${esc(p.name)}${p.id===HOST_ID?'<span class="host-badge">HOST</span>':''}</span>
            <span class="ready-dot"></span>
        </div>`).join('');
        }

        function renderAll(containerId, players) {
            const wrap = document.getElementById(containerId);
            if (!wrap) return;
            wrap.innerHTML = players.map(p => `
        <div class="player-row">
            <div class="player-av">${esc(p.name[0].toUpperCase())}</div>
            <span class="player-name">${esc(p.name)}${p.id===HOST_ID?'<span class="host-badge">HOST</span>':''}</span>
            <span class="ready-dot"></span>
        </div>`).join('');
        }

        // ── Host: start battle ────────────────────────────────────
        async function startBattle() {
            const btn = document.getElementById('startBtn');
            btn.disabled = true;
            btn.textContent = '⏳ Starting…';

            try {
                const res = await fetch(ROUTES.start, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        code: ROOM_CODE
                    }),
                });
                const d = await res.json();

                if (d.success) {
                    showCountdown(() => window.location.href = d.redirectUrl || ROUTES.arena);
                } else {
                    alert(d.message || 'Failed to start');
                    btn.disabled = false;
                    btn.textContent = '⚡ Start Battle Now!';
                }
            } catch {
                alert('Network error');
                btn.disabled = false;
                btn.textContent = '⚡ Start Battle Now!';
            }
        }

        // ── Countdown overlay ─────────────────────────────────────
        function showCountdown(cb) {
            const overlay = document.getElementById('countdownOverlay');
            const numEl = document.getElementById('countdownNum');
            overlay.style.display = 'flex';

            let n = 3;
            numEl.textContent = n;

            playBeep(880);

            const tick = setInterval(() => {
                n--;
                if (n > 0) {
                    numEl.textContent = n;
                    playBeep(880);
                } else {
                    clearInterval(tick);
                    playBeep(1200, 0.5);
                    setTimeout(cb, 300);
                }
            }, 1000);
        }

        // ── Sound ─────────────────────────────────────────────────
        let audioCtx;

        function getAudioCtx() {
            if (!audioCtx) audioCtx = new(window.AudioContext || window.webkitAudioContext)();
            return audioCtx;
        }

        function playBeep(freq, dur = 0.12) {
            try {
                const ctx = getAudioCtx();
                const osc = ctx.createOscillator();
                const g = ctx.createGain();
                osc.connect(g);
                g.connect(ctx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, ctx.currentTime);
                g.gain.setValueAtTime(0.3, ctx.currentTime);
                g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + dur);
                osc.start();
                osc.stop(ctx.currentTime + dur);
            } catch (e) {}
        }

        // ── Helpers ───────────────────────────────────────────────
        async function copyText(text, btn) {
            try {
                await navigator.clipboard.writeText(text);
            } catch (e) {}
            const orig = btn.textContent;
            btn.textContent = '✅ Copied!';
            btn.classList.add('copied');
            setTimeout(() => {
                btn.textContent = orig;
                btn.classList.remove('copied');
            }, 2000);
        }

        function esc(s) {
            return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        // Run initial poll immediately
        pollLobbyState();
    </script>
@endsection
