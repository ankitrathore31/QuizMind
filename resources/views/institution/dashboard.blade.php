@extends('institution.layout.master')

@section('page_title', '📊 Dashboard')

@push('styles')
    <style>
        /* ─── Stat Cards ─── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: default;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .stat-card.sc-purple::before {
            background: linear-gradient(90deg, #7C5CFC, #9B7BFF);
        }

        .stat-card.sc-cyan::before {
            background: linear-gradient(90deg, #00D4FF, #00E396);
        }

        .stat-card.sc-green::before {
            background: linear-gradient(90deg, #00E396, #7C5CFC);
        }

        .stat-card.sc-red::before {
            background: linear-gradient(90deg, #FF4D6A, #FF8C42);
        }

        .stat-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 80% 20%, rgba(124, 92, 252, 0.06), transparent 60%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-6px) scale(1.02);
            border-color: var(--border2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3), 0 0 30px rgba(124, 92, 252, 0.1);
        }

        .stat-card:hover::after {
            opacity: 1;
        }

        .stat-icon-wrap {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 14px;
        }

        .sc-purple .stat-icon-wrap {
            background: rgba(124, 92, 252, 0.12);
        }

        .sc-cyan .stat-icon-wrap {
            background: rgba(0, 212, 255, 0.1);
        }

        .sc-green .stat-icon-wrap {
            background: rgba(0, 227, 150, 0.1);
        }

        .sc-red .stat-icon-wrap {
            background: rgba(255, 77, 106, 0.1);
        }

        .stat-val {
            font-family: var(--fh);
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 6px;
        }

        .sc-purple .stat-val {
            background: linear-gradient(135deg, #7C5CFC, #9B7BFF);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sc-cyan .stat-val {
            background: linear-gradient(135deg, #00D4FF, #00E396);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sc-green .stat-val {
            background: linear-gradient(135deg, #00E396, #7C5CFC);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sc-red .stat-val {
            background: linear-gradient(135deg, #FF4D6A, #FF8C42);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 0.74rem;
            color: var(--muted);
            font-weight: 500;
        }

        .stat-delta {
            position: absolute;
            top: 16px;
            right: 16px;
            font-size: 0.66rem;
            font-family: var(--fh);
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
        }

        .delta-up {
            background: rgba(0, 227, 150, 0.1);
            color: var(--green);
            border: 1px solid rgba(0, 227, 150, 0.2);
        }

        .delta-down {
            background: rgba(255, 77, 106, 0.1);
            color: var(--red);
            border: 1px solid rgba(255, 77, 106, 0.2);
        }

        /* ─── Section Title ─── */
        .sec-hd {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .sec-title {
            font-family: var(--fh);
            font-size: 0.95rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sec-link {
            font-size: 0.74rem;
            color: var(--violet);
            font-family: var(--fh);
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s;
        }

        .sec-link:hover {
            color: var(--cyan);
        }

        /* ─── Dashboard Grid ─── */
        .dash-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 20px;
        }

        /* ─── Students Table Card ─── */
        .data-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px;
            position: relative;
            overflow: hidden;
        }

        .data-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--gradient);
            opacity: 0.5;
        }

        /* ─── Table ─── */
        .qm-table {
            width: 100%;
            border-collapse: collapse;
        }

        .qm-table thead th {
            font-family: var(--fh);
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--muted);
            padding: 8px 12px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .qm-table tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            transition: all 0.2s;
        }

        .qm-table tbody tr:hover {
            background: rgba(124, 92, 252, 0.04);
        }

        .qm-table tbody tr:last-child {
            border-bottom: none;
        }

        .qm-table tbody td {
            padding: 12px;
            font-size: 0.84rem;
            vertical-align: middle;
        }

        .student-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--fh);
            font-weight: 800;
            font-size: 13px;
            flex-shrink: 0;
            border: 2px solid rgba(124, 92, 252, 0.2);
        }

        .xp-badge {
            font-family: var(--fh);
            font-weight: 700;
            font-size: 0.8rem;
            color: var(--violet);
        }

        .acc-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-family: var(--fh);
            font-weight: 700;
        }

        .acc-high {
            background: rgba(0, 227, 150, 0.1);
            color: var(--green);
            border: 1px solid rgba(0, 227, 150, 0.2);
        }

        .acc-mid {
            background: rgba(255, 184, 0, 0.1);
            color: var(--gold);
            border: 1px solid rgba(255, 184, 0, 0.2);
        }

        .acc-low {
            background: rgba(255, 77, 106, 0.1);
            color: var(--red);
            border: 1px solid rgba(255, 77, 106, 0.2);
        }

        .rank-num {
            font-family: var(--fh);
            font-size: 0.8rem;
            font-weight: 800;
            color: var(--muted);
        }

        .rank-num.r1 {
            color: var(--gold);
        }

        .rank-num.r2 {
            color: #C0C0C0;
        }

        .rank-num.r3 {
            color: #CD7F32;
        }

        /* ─── Right Column ─── */
        .right-col {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        /* ─── Top Performer ─── */
        .top-performer-card {
            background: linear-gradient(135deg, rgba(124, 92, 252, 0.1), rgba(0, 212, 255, 0.05));
            border: 1px solid rgba(124, 92, 252, 0.25);
            border-radius: var(--radius);
            padding: 24px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .top-performer-card::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(124, 92, 252, 0.12), transparent);
            border-radius: 50%;
        }

        .performer-crown {
            font-size: 2rem;
            display: block;
            margin-bottom: 8px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        .performer-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--fh);
            font-weight: 800;
            font-size: 26px;
            margin: 0 auto 12px;
            border: 3px solid rgba(124, 92, 252, 0.4);
            box-shadow: 0 0 30px rgba(124, 92, 252, 0.3);
            position: relative;
            z-index: 1;
        }

        .performer-name {
            font-family: var(--fh);
            font-size: 1rem;
            font-weight: 800;
            margin-bottom: 4px;
            position: relative;
            z-index: 1;
        }

        .performer-xp {
            font-family: var(--fh);
            font-weight: 700;
            font-size: 0.85rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 4px;
            position: relative;
            z-index: 1;
        }

        .performer-acc {
            font-size: 0.74rem;
            color: var(--muted);
            position: relative;
            z-index: 1;
        }

        /* ─── Quick Stats Card ─── */
        .quick-stats {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
        }

        .qs-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }

        .qs-item:last-child {
            border-bottom: none;
        }

        .qs-left {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.84rem;
        }

        .qs-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .qs-val {
            font-family: var(--fh);
            font-size: 0.9rem;
            font-weight: 800;
        }

        /* ─── Win Rate Bar ─── */
        .win-rate-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
        }

        .wr-numbers {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-family: var(--fh);
            font-size: 0.8rem;
            font-weight: 700;
        }

        .wr-track {
            height: 8px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .wr-fill {
            height: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, var(--green), var(--cyan));
            transition: width 1.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
        }

        .wr-fill::after {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: white;
            opacity: 0.6;
            border-radius: 2px;
        }

        /* ─── Animations ─── */
        .fade-up {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUp 0.5s ease forwards;
        }

        @keyframes fadeUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .delay-1 {
            animation-delay: 0.05s;
        }

        .delay-2 {
            animation-delay: 0.1s;
        }

        .delay-3 {
            animation-delay: 0.15s;
        }

        .delay-4 {
            animation-delay: 0.2s;
        }

        .delay-5 {
            animation-delay: 0.25s;
        }

        .delay-6 {
            animation-delay: 0.35s;
        }

        /* ─── Responsive ─── */
        @media (max-width: 1100px) {
            .stat-grid {
                grid-template-columns: 1fr 1fr;
            }

            .dash-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 560px) {
            .stat-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
@endpush

@section('content')

    {{-- ── Page Header ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px" class="fade-up">
        <div>
            <div
                style="font-family:var(--fh);font-size:0.7rem;letter-spacing:2px;color:var(--violet);text-transform:uppercase;font-weight:700;margin-bottom:4px">
                Institution Overview
            </div>
            <h2 style="font-family:var(--fh);font-size:1.6rem;font-weight:800;line-height:1">
                Welcome Back 👋
            </h2>
            <div style="font-size:0.82rem;color:var(--muted);margin-top:4px">
                {{ $institution->name }} · Real-time performance snapshot
            </div>
        </div>

        <!-- Institution Card -->
        <div class="inst-card">
            <div class="inst-label">Institution Reference Code</div>
            <div class="inst-code-row">
                <span class="inst-code">{{ $institution->code }}</span>
                <button class="copy-btn" onclick="copyToClipboard('{{ $institution->code }}', this)">📋</button>
            </div>
            <div class="inst-name">{{ $institution->name }}</div>
        </div>
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="stat-grid">

        <div class="stat-card sc-purple fade-up delay-1">
            <div class="stat-icon-wrap">👥</div>
            <div class="stat-val" data-count="{{ $stats['total_students'] ?? 0 }}">0</div>
            <div class="stat-label">Total Students</div>
            <span class="stat-delta delta-up">↑ Active</span>
        </div>

        <div class="stat-card sc-cyan fade-up delay-2">
            <div class="stat-icon-wrap">✅</div>
            <div class="stat-val" data-count="{{ $stats['total_correct'] ?? 0 }}">0</div>
            <div class="stat-label">Correct Answers</div>
            <span class="stat-delta delta-up">↑ +12%</span>
        </div>

        <div class="stat-card sc-green fade-up delay-3">
            <div class="stat-icon-wrap">🏆</div>
            <div class="stat-val" data-count="{{ $stats['battle_wins'] ?? 0 }}">0</div>
            <div class="stat-label">Battle Wins</div>
            <span class="stat-delta delta-up">↑ +8%</span>
        </div>

        <div class="stat-card sc-red fade-up delay-4">
            <div class="stat-icon-wrap">❌</div>
            <div class="stat-val" data-count="{{ $stats['battle_losses'] ?? 0 }}">0</div>
            <div class="stat-label">Battle Losses</div>
            <span class="stat-delta delta-down">↓ -3%</span>
        </div>

    </div>

    {{-- ── Main Dashboard Grid ── --}}
    <div class="dash-grid">

        {{-- Students Table --}}
        <div class="data-card fade-up delay-5">
            <div class="sec-hd">
                <div class="sec-title">
                    <span style="font-size:16px">👥</span> Recent Students
                </div>
                <a href="{{ route('institution.students') }}" class="sec-link">View All →</a>
            </div>

            <div style="overflow-x:auto">
                <table class="qm-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>XP</th>
                            <th>Accuracy</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students->take(6) as $i => $s)
                            <tr>
                                <td>
                                    <span class="rank-num {{ $i == 0 ? 'r1' : ($i == 1 ? 'r2' : ($i == 2 ? 'r3' : '')) }}">
                                        {{ $i == 0 ? '🥇' : ($i == 1 ? '🥈' : ($i == 2 ? '🥉' : '#' . ($i + 1))) }}
                                    </span>
                                </td>

                                <td>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <div class="student-avatar">
                                            {{ strtoupper(substr($s->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight:600;font-size:0.85rem">{{ $s->user->name }}</div>
                                            <div style="font-size:0.7rem;color:var(--muted)">{{ $s->user->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="xp-badge">⚡ {{ number_format($s->xp ?? 0) }}</span>
                                </td>

                                <td>
                                    @php $acc = $s->accuracy ?? 0; @endphp
                                    <span
                                        class="acc-badge {{ $acc >= 70 ? 'acc-high' : ($acc >= 40 ? 'acc-mid' : 'acc-low') }}">
                                        {{ $acc }}%
                                    </span>
                                </td>

                                <td style="font-size:0.74rem;color:var(--muted)">
                                    {{ $s->created_at ? $s->created_at->format('d M') : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;color:var(--muted);padding:40px 12px">
                                    <div style="font-size:2rem;margin-bottom:8px">🎓</div>
                                    No students enrolled yet
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="right-col fade-up delay-6">

            {{-- Top Performer --}}
            <div class="top-performer-card">
                <div class="sec-hd" style="margin-bottom:12px">
                    <div class="sec-title"><span>🏆</span> Top Performer</div>
                </div>

                @if ($students->first())
                    <span class="performer-crown">👑</span>
                    <div class="performer-avatar">
                        {{ strtoupper(substr($students->first()->user->name, 0, 1)) }}
                    </div>
                    <div class="performer-name">{{ $students->first()->user->name }}</div>
                    <div class="performer-xp">⚡ {{ number_format($students->first()->xp ?? 0) }} XP</div>
                    <div class="performer-acc">Accuracy: {{ $students->first()->accuracy ?? 0 }}%</div>

                    <div style="margin-top:14px">
                        <div
                            style="display:flex;justify-content:space-between;font-size:0.7rem;color:var(--muted);margin-bottom:4px">
                            <span>Accuracy</span>
                            <span>{{ $students->first()->accuracy ?? 0 }}%</span>
                        </div>
                        <div class="wr-track">
                            <div class="wr-fill" id="top-acc-bar" style="width:0%"></div>
                        </div>
                    </div>
                @else
                    <div style="color:var(--muted);font-size:0.84rem;padding:20px 0">No data yet</div>
                @endif
            </div>

            {{-- Win Rate Card --}}
            <div class="win-rate-card">
                <div class="sec-hd" style="margin-bottom:14px">
                    <div class="sec-title"><span>⚔️</span> Battle Win Rate</div>
                </div>

                @php
                    $wins = $stats['battle_wins'] ?? 0;
                    $losses = $stats['battle_losses'] ?? 0;
                    $total = $wins + $losses;
                    $rate = $total > 0 ? round(($wins / $total) * 100) : 0;
                @endphp

                <div style="text-align:center;margin-bottom:12px">
                    <div
                        style="font-family:var(--fh);font-size:2.4rem;font-weight:800;background:var(--gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">
                        {{ $rate }}%
                    </div>
                    <div style="font-size:0.74rem;color:var(--muted)">Win Rate</div>
                </div>

                <div class="wr-track">
                    <div class="wr-fill" id="win-rate-bar" style="width:0%"></div>
                </div>

                <div class="wr-numbers">
                    <span style="color:var(--green)">🏆 {{ $wins }} Wins</span>
                    <span style="color:var(--red)">❌ {{ $losses }} Losses</span>
                </div>
            </div>

            {{-- Quick Stats ─── --}}
            <div class="quick-stats">
                <div class="sec-title" style="margin-bottom:14px"><span>📈</span> Quick Stats</div>

                <div class="qs-item">
                    <div class="qs-left">
                        <div class="qs-icon" style="background:rgba(0,212,255,0.1)">📚</div>
                        <span>Total Answers</span>
                    </div>
                    <div class="qs-val" style="color:var(--cyan)">
                        {{ number_format(($stats['total_correct'] ?? 0) + ($stats['total_incorrect'] ?? 0)) }}
                    </div>
                </div>

                <div class="qs-item">
                    <div class="qs-left">
                        <div class="qs-icon" style="background:rgba(124,92,252,0.1)">⚡</div>
                        <span>Avg XP / Student</span>
                    </div>
                    <div class="qs-val" style="color:var(--violet)">
                        @php
                            $avgXp = $students->count() > 0 ? round($students->avg('xp') ?? 0) : 0;
                        @endphp
                        {{ number_format($avgXp) }}
                    </div>
                </div>

                <div class="qs-item">
                    <div class="qs-left">
                        <div class="qs-icon" style="background:rgba(0,227,150,0.1)">🎯</div>
                        <span>Avg Accuracy</span>
                    </div>
                    <div class="qs-val" style="color:var(--green)">
                        {{ round($students->avg('accuracy') ?? 0) }}%
                    </div>
                </div>

            </div>

        </div>

    </div>

@endsection

@push('scripts')
    <script>
        // ── Count-up Animation ──
        document.querySelectorAll('.stat-val[data-count]').forEach(el => {
            const target = parseInt(el.dataset.count) || 0;
            if (target === 0) {
                el.textContent = '0';
                return;
            }
            let start = 0;
            const step = Math.ceil(target / 50);
            const timer = setInterval(() => {
                start = Math.min(start + step, target);
                el.textContent = start.toLocaleString();
                if (start >= target) clearInterval(timer);
            }, 30);
        });

        // ── Bar Animations ──
        setTimeout(() => {
            const winBar = document.getElementById('win-rate-bar');
            if (winBar) winBar.style.width = '{{ $rate }}%';

            const accBar = document.getElementById('top-acc-bar');
            if (accBar) {
                @if ($students->first())
                    accBar.style.width = '{{ $students->first()->accuracy ?? 0 }}%';
                @endif
            }
        }, 400);
    </script>
@endpush
