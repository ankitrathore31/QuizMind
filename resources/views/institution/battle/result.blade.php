{{-- resources/views/institution/battle/results.blade.php --}}
@extends('institution.layout.master')
@section('content')
    <style>
        :root {
            --bg: #0a0a0f;
            --card: #13131e;
            --card2: #1a1a28;
            --border: rgba(255, 255, 255, .07);
            --border2: rgba(255, 255, 255, .04);
            --text: #f0f0f8;
            --muted: #7070a0;
            --accent: #7c5cfc;
            --green: #00e396;
            --red: #ff4d6a;
            --orange: #ff9500;
            --gradient: linear-gradient(135deg, #7c5cfc, #00d4ff);
            --radius: 14px;
            --rsm: 9px;
            --fh: 'Syne', sans-serif;
            --fb: 'DM Sans', sans-serif
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--fb)
        }

        .page {
            max-width: 900px;
            margin: 0 auto;
            padding: 32px 20px 60px
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px;
            margin-bottom: 16px
        }

        .card-hd {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px
        }

        .card-title {
            font-family: var(--fh);
            font-weight: 800;
            font-size: .9rem
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 8px;
            border: none;
            font-family: var(--fb);
            font-weight: 700;
            font-size: .82rem;
            cursor: pointer;
            transition: .18s;
            text-decoration: none
        }

        .btn-grad {
            background: var(--gradient);
            color: #fff
        }

        .btn-grad:hover {
            opacity: .9;
            transform: translateY(-1px)
        }

        .btn-ghost {
            background: transparent;
            color: var(--text);
            border: 1.5px solid var(--border)
        }

        .btn-ghost:hover {
            border-color: rgba(124, 92, 252, .35)
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: .76rem
        }

        /* Hero */
        .result-hero {
            text-align: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, rgba(124, 92, 252, .08), rgba(0, 212, 255, .05));
            border: 1px solid rgba(124, 92, 252, .2);
            border-radius: var(--radius);
            margin-bottom: 20px;
            position: relative;
            overflow: hidden
        }

        .winner-trophy {
            font-size: 4rem;
            display: block;
            margin-bottom: 12px;
            animation: float 3s ease-in-out infinite
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-8px)
            }
        }

        /* Institution podium */
        .podium {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 16px;
            margin-bottom: 28px;
            padding: 0 20px
        }

        .podium-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            flex: 1;
            max-width: 180px
        }

        .podium-block {
            width: 100%;
            border-radius: 10px 10px 0 0;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 16px;
            min-height: 60px
        }

        .podium-rank {
            font-family: var(--fh);
            font-size: 2rem;
            font-weight: 900
        }

        .podium-name {
            font-family: var(--fh);
            font-weight: 800;
            font-size: .84rem;
            text-align: center
        }

        .podium-score {
            font-family: var(--fh);
            font-weight: 900;
            font-size: 1.1rem;
            text-align: center
        }

        /* Student table */
        .res-table {
            width: 100%;
            border-collapse: collapse
        }

        .res-table th {
            padding: 9px 12px;
            font-size: .64rem;
            font-weight: 800;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 1px solid var(--border);
            text-align: left
        }

        .res-table td {
            padding: 10px 12px;
            font-size: .8rem;
            border-bottom: 1px solid var(--border2);
            vertical-align: middle
        }

        .res-table tr:last-child td {
            border: none
        }

        .res-table tbody tr:hover {
            background: rgba(124, 92, 252, .03)
        }

        .rank-gold {
            color: #ffd700
        }

        .rank-silver {
            color: #c0c0c0
        }

        .rank-bronze {
            color: #cd7f32
        }

        .stat-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 16px
        }

        .stat-mini {
            background: var(--card2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px;
            text-align: center
        }

        .sm-val {
            font-family: var(--fh);
            font-size: 1.3rem;
            font-weight: 900;
            margin-bottom: 3px
        }

        .sm-lbl {
            font-size: .64rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .4px
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: .67rem;
            font-weight: 700;
            border: 1px solid
        }

        .badge-g {
            background: rgba(0, 227, 150, .08);
            color: var(--green);
            border-color: rgba(0, 227, 150, .2)
        }

        .badge-r {
            background: rgba(255, 77, 106, .08);
            color: var(--red);
            border-color: rgba(255, 77, 106, .2)
        }

        .badge-p {
            background: rgba(124, 92, 252, .1);
            color: var(--accent);
            border-color: rgba(124, 92, 252, .22)
        }

        @keyframes confettiFall {
            0% {
                transform: translateY(-20px) rotate(0deg);
                opacity: 1
            }

            100% {
                transform: translateY(300px) rotate(720deg);
                opacity: 0
            }
        }
    </style>

    <div id="confettiCont" style="position:fixed;inset:0;pointer-events:none;z-index:9998;overflow:hidden"></div>

    <div class="page">
        {{-- Hero --}}
        <div class="result-hero">
            <span class="winner-trophy">🏆</span>
            <h1 style="font-family:var(--fh);font-size:2rem;font-weight:900;margin-bottom:8px">
                @if ($room->winner_institution)
                    {{ $room->winner_institution->name }} Wins!
                @else
                    Institution Battle Complete!
                @endif
            </h1>
            <p style="color:var(--muted);font-size:.9rem">Battle Code: <strong
                    style="font-family:monospace;color:var(--accent)">{{ $room->code }}</strong></p>
            <p style="color:var(--muted);font-size:.8rem;margin-top:4px">{{ $room->institutionParticipants->count() }}
                institutions · {{ $room->total_questions }} questions · {{ $room->participants->count() }} total students
            </p>
        </div>

        {{-- Podium --}}
        @php
            $instColors = ['#ffd700', '#c0c0c0', '#cd7f32', '#7c5cfc'];
            $podiumHeights = ['120px', '90px', '70px', '55px'];
            $sortedInstitutions = $room->institutionParticipants
                ->map(
                    fn($ip) => [
                        'ip' => $ip,
                        'score' => $room->participants->where('institution_id', $ip->id)->sum('score'),
                        'count' => $room->participants->where('institution_id', $ip->id)->count(),
                    ],
                )
                ->sortByDesc('score')
                ->values();
        @endphp
        <div class="podium">
            @foreach ($sortedInstitutions as $rank => $inst)
                <div class="podium-item">
                    <div class="podium-score" style="color:{{ $instColors[$rank] }}">{{ number_format($inst['score']) }}
                    </div>
                    <div class="podium-name">{{ $inst['ip']->name }}</div>
                    <div class="podium-block"
                        style="background:rgba({{ $rank === 0 ? '255,215,0' : ($rank === 1 ? '192,192,192' : ($rank === 2 ? '205,127,50' : '124,92,252')) }},.1);border:2px solid {{ $instColors[$rank] }};height:{{ $podiumHeights[$rank] ?? '45px' }}">
                        <div class="podium-rank" style="color:{{ $instColors[$rank] }}">
                            {{ ['🥇', '🥈', '🥉', '4️⃣'][$rank] ?? $rank + 1 }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Institution Summary --}}
        @foreach ($sortedInstitutions as $rank => $inst)
            @php
                $instStudents = $room->participants->where('institution_id', $inst['ip']->id)->sortByDesc('score');
                $correct = $room
                    ->answers()
                    ->whereIn('user_id', $instStudents->pluck('user_id'))
                    ->where('is_correct', true)
                    ->count();
                $total = $room->answers()->whereIn('user_id', $instStudents->pluck('user_id'))->count();
                $acc = $total > 0 ? round(($correct / $total) * 100) : 0;
            @endphp
            <div class="card">
                <div class="card-hd">
                    <span class="card-title">
                        <span
                            style="color:{{ $instColors[$rank] }};margin-right:6px">{{ ['🥇', '🥈', '🥉', '4️⃣'][$rank] ?? '#' . ($rank + 1) }}</span>
                        {{ $inst['ip']->name }}
                    </span>
                    <span
                        style="font-family:var(--fh);font-weight:900;font-size:1.1rem;color:{{ $instColors[$rank] }}">{{ number_format($inst['score']) }}
                        pts</span>
                </div>

                <div class="stat-row">
                    <div class="stat-mini">
                        <div class="sm-val" style="color:var(--accent)">{{ $inst['count'] }}</div>
                        <div class="sm-lbl">Students</div>
                    </div>
                    <div class="stat-mini">
                        <div class="sm-val" style="color:var(--green)">{{ $acc }}%</div>
                        <div class="sm-lbl">Accuracy</div>
                    </div>
                    <div class="stat-mini">
                        <div class="sm-val" style="color:var(--orange)">{{ number_format($inst['score']) }}</div>
                        <div class="sm-lbl">Total Score</div>
                    </div>
                    <div class="stat-mini">
                        <div class="sm-val">{{ $room->total_questions }}</div>
                        <div class="sm-lbl">Questions</div>
                    </div>
                </div>

                <div style="overflow-x:auto">
                    <table class="res-table">
                        <thead>
                            <tr>
                                <th style="width:40px">#</th>
                                <th>Student</th>
                                <th>Score</th>
                                <th>Correct</th>
                                <th>Accuracy</th>
                                <th>Streak</th>
                                <th>XP</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($instStudents as $sidx => $p)
                                @php
                                    $total_q = $room->total_questions;
                                    $pacc = $total_q > 0 ? round(($p->correct / $total_q) * 100) : 0;
                                @endphp
                                <tr>
                                    <td><span
                                            class="{{ $sidx === 0 ? 'rank-gold' : ($sidx === 1 ? 'rank-silver' : ($sidx === 2 ? 'rank-bronze' : '')) }}"
                                            style="font-weight:800;{{ $sidx > 2 ? 'color:var(--muted)' : '' }}">#{{ $sidx + 1 }}</span>
                                    </td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px">
                                            <div
                                                style="width:28px;height:28px;border-radius:50%;background:var(--gradient);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.7rem;color:#fff;flex-shrink:0">
                                                {{ strtoupper(substr($p->user->name, 0, 1)) }}</div>
                                            <span style="font-weight:600;font-size:.82rem">{{ $p->user->name }}</span>
                                        </div>
                                    </td>
                                    <td style="font-family:var(--fh);font-weight:800;color:var(--accent)">
                                        {{ number_format($p->score) }}</td>
                                    <td style="color:var(--green)">{{ $p->correct }}/{{ $room->total_questions }}</td>
                                    <td>
                                        <span
                                            style="color:{{ $pacc >= 80 ? 'var(--green)' : ($pacc >= 60 ? 'var(--orange)' : 'var(--red)') }};font-weight:700">{{ $pacc }}%</span>
                                    </td>
                                    <td>
                                        @if ($p->max_streak > 0)
                                        <span style="color:var(--orange)">🔥{{ $p->max_streak }}</span>@else<span
                                                style="color:var(--muted)">—</span>
                                        @endif
                                    </td>
                                    <td style="color:var(--accent);font-weight:700">+{{ $p->xp_earned ?? 0 }}</td>
                                    <td>
                                        @if ($p->disqualified)
                                            <span class="badge badge-r">DQ'd</span>
                                        @else
                                            <span class="badge badge-g">✓</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        {{-- Actions --}}
        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:8px">
            <a href="{{ route('institution.dashboard') }}" class="btn btn-ghost">← Dashboard</a>
            @if (auth()->id() === $room->host_id)
                <button class="btn btn-ghost" onclick="rematch()">🔄 Rematch</button>
            @endif
            <button class="btn btn-grad" onclick="shareResults()">📊 Share Results</button>
        </div>
    </div>

    <script>
        // Confetti on load
        (function() {
            const container = document.getElementById('confettiCont');
            const colors = ['#7c5cfc', '#00d4ff', '#00e396', '#ffd700', '#ff6b9d'];
            for (let i = 0; i < 80; i++) {
                const el = document.createElement('div');
                const color = colors[Math.floor(Math.random() * colors.length)];
                const size = 6 + Math.random() * 8;
                el.style.cssText =
                    `position:absolute;top:-20px;left:${Math.random()*100}%;width:${size}px;height:${size}px;background:${color};border-radius:2px;animation:confettiFall ${2+Math.random()*2}s ease-in ${Math.random()*2}s both`;
                container.appendChild(el);
            }
            setTimeout(() => container.innerHTML = '', 6000);
        })();

        async function rematch() {
            try {
                const r = await fetch('/institution/battle/rematch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        code: '{{ $room->code }}'
                    })
                });
                const d = await r.json();
                if (d.success) window.location.href = d.redirectUrl;
                else alert(d.message);
            } catch {
                alert('Network error');
            }
        }

        function shareResults() {
            navigator.clipboard.writeText(window.location.href).then(() => alert('Results link copied!'));
        }
    </script>
@endsection
