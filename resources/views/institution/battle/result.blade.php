{{-- resources/views/institution/battle/results.blade.php --}}
{{--
    INSTITUTION RESULTS — seen by HOST and OBSERVER ADMINS
    Shows: institution rankings (podium), per-institution stats, full leaderboard,
           anti-cheat report, my card (if host participated), action bar.
    Students are routed to student.institution-battle.results instead.
--}}
@extends('institution.layout.master')
@section('page_title', '🏆 Battle Results · ' . $battle->code)

@section('content')
    <style>
        :root {
            --gold: #ffd700;
            --silver: #c0c0c0;
            --bronze: #cd7f32;
            --green: #00e396;
            --red: #ff4d6a;
            --ice: #00d4ff;
            --fire: #ff6b35;
            --gold-dim: rgba(255, 215, 0, .08);
            --s1c: #7c5cfc;
            --s2c: #00d4ff;
            --s3c: #f5c842;
        }

        .res-wrap {
            max-width: 1160px;
            margin: 0 auto;
            padding: 32px 18px 72px;
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        /* ── HEADER ── */
        .res-header {
            text-align: center;
        }

        .res-trophy {
            font-size: 3.5rem;
            margin-bottom: 10px;
            display: block;
            animation: trophyPop .6s cubic-bezier(.34, 1.56, .64, 1);
        }

        @keyframes trophyPop {
            from {
                transform: scale(.4) rotate(-8deg);
                opacity: 0
            }

            to {
                transform: none;
                opacity: 1
            }
        }

        .res-title {
            font-family: var(--fh);
            font-size: clamp(1.8rem, 4vw, 2.6rem);
            font-weight: 900;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -.5px;
        }

        .res-meta {
            color: var(--muted);
            font-size: .8rem;
            margin-top: 6px;
            display: flex;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .res-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ── SECTION HEADER ── */
        .sec-hd {
            font-size: .7rem;
            font-weight: 800;
            color: var(--muted);
            letter-spacing: .12em;
            text-transform: uppercase;
            font-family: var(--fh);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sec-hd::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ── INSTITUTION PODIUM ── */
        .inst-podium {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 12px;
            padding: 0 10px 4px;
        }

        .inst-slot {
            flex: 1;
            max-width: 280px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .inst-slot.r1 {
            order: 2;
        }

        .inst-slot.r2 {
            order: 1;
        }

        .inst-slot.r3 {
            order: 3;
        }

        .inst-av {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--fh);
            font-size: 1.3rem;
            font-weight: 900;
            color: #fff;
            margin-bottom: 8px;
            position: relative;
            flex-shrink: 0;
        }

        .r1 .inst-av {
            width: 72px;
            height: 72px;
            font-size: 1.6rem;
        }

        .inst-av.c1 {
            background: linear-gradient(135deg, #7c5cfc, #5a3dd4);
        }

        .inst-av.c2 {
            background: linear-gradient(135deg, #00d4ff, #0099cc);
        }

        .inst-av.c3 {
            background: linear-gradient(135deg, #f5c842, #d4a520);
        }

        .inst-medal {
            position: absolute;
            bottom: -5px;
            right: -5px;
            font-size: 1.1rem;
        }

        .r1 .inst-medal {
            font-size: 1.4rem;
        }

        .inst-pod-name {
            font-size: .85rem;
            font-weight: 900;
            text-align: center;
            margin-bottom: 3px;
            max-width: 160px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .inst-pod-score {
            font-family: var(--fh);
            font-size: 1.5rem;
            font-weight: 900;
            margin-bottom: 4px;
        }

        .r1 .inst-pod-score {
            font-size: 2rem;
            color: var(--gold);
        }

        .r2 .inst-pod-score {
            color: var(--silver);
        }

        .r3 .inst-pod-score {
            color: var(--bronze);
        }

        .inst-pod-sub {
            font-size: .68rem;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .inst-pod-block {
            width: 100%;
            border-radius: 10px 10px 0 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--fh);
            font-weight: 900;
            padding: 10px 0;
        }

        .r1 .inst-pod-block {
            height: 90px;
            background: linear-gradient(180deg, rgba(255, 215, 0, .18), rgba(255, 215, 0, .06));
            border: 1px solid rgba(255, 215, 0, .3);
            color: var(--gold);
            font-size: 1.3rem;
        }

        .r2 .inst-pod-block {
            height: 62px;
            background: linear-gradient(180deg, rgba(192, 192, 192, .12), rgba(192, 192, 192, .04));
            border: 1px solid rgba(192, 192, 192, .2);
            color: var(--silver);
        }

        .r3 .inst-pod-block {
            height: 44px;
            background: linear-gradient(180deg, rgba(205, 127, 50, .12), rgba(205, 127, 50, .04));
            border: 1px solid rgba(205, 127, 50, .2);
            color: var(--bronze);
        }

        /* ── INSTITUTION STAT CARDS ── */
        .inst-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 14px;
        }

        .inst-card {
            background: var(--card);
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            position: relative;
            overflow: hidden;
            animation: cardIn .4s ease both;
        }

        @keyframes cardIn {
            from {
                opacity: 0;
                transform: translateY(12px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .inst-card.rank-1 {
            border-color: rgba(255, 215, 0, .3);
        }

        .inst-card.rank-2 {
            border-color: rgba(192, 192, 192, .2);
        }

        .inst-card.rank-3 {
            border-color: rgba(205, 127, 50, .2);
        }

        .inst-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .inst-card.rank-1::before {
            background: linear-gradient(90deg, var(--gold), #ffb700);
        }

        .inst-card.rank-2::before {
            background: linear-gradient(90deg, var(--silver), #a0a0a0);
        }

        .inst-card.rank-3::before {
            background: linear-gradient(90deg, var(--bronze), #a05a20);
        }

        .ic-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }

        .ic-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: .9rem;
            color: #fff;
            flex-shrink: 0;
        }

        .ic-icon.c1 {
            background: linear-gradient(135deg, #7c5cfc, #5a3dd4);
        }

        .ic-icon.c2 {
            background: linear-gradient(135deg, #00d4ff, #0099cc);
        }

        .ic-icon.c3 {
            background: linear-gradient(135deg, #f5c842, #d4a520);
        }

        .ic-name {
            font-weight: 900;
            font-size: .9rem;
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .ic-rank {
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .ic-score {
            font-family: var(--fh);
            font-size: 2rem;
            font-weight: 900;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        .ic-stats {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
        }

        .ic-stat {
            background: rgba(255, 255, 255, .03);
            border-radius: 8px;
            padding: 8px;
            text-align: center;
        }

        .ic-stat .v {
            font-weight: 800;
            font-size: .9rem;
        }

        .ic-stat .l {
            font-size: .6rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-top: 1px;
        }

        /* ── MY PERFORMANCE CARD ── */
        .my-card {
            background: rgba(124, 92, 252, .07);
            border: 1.5px solid rgba(124, 92, 252, .25);
            border-radius: var(--radius);
            padding: 20px 22px;
            display: grid;
            grid-template-columns: 1fr repeat(5, auto);
            gap: 14px;
            align-items: center;
            animation: cardIn .4s .1s both;
        }

        @media(max-width:640px) {
            .my-card {
                grid-template-columns: 1fr 1fr;
            }
        }

        .mc-label {
            font-size: .65rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .07em;
        }

        .mc-val {
            font-family: var(--fh);
            font-size: 1.3rem;
            font-weight: 900;
        }

        .mc-val.purple {
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .mc-val.green {
            color: var(--green);
        }

        .mc-val.red {
            color: var(--red);
        }

        .mc-val.gold {
            color: var(--gold);
        }

        .mc-val.ice {
            color: var(--ice);
        }

        /* ── LEADERBOARD TABLE ── */
        .lb-table {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .lb-row {
            display: grid;
            grid-template-columns: 38px 1fr auto auto auto auto;
            gap: 10px;
            align-items: center;
            padding: 11px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, .04);
            animation: rowIn .3s ease both;
            transition: background .2s;
        }

        .lb-row:last-child {
            border-bottom: none;
        }

        .lb-row:hover {
            background: rgba(255, 255, 255, .025);
        }

        .lb-row.me {
            background: rgba(124, 92, 252, .07);
            border-left: 3px solid var(--accent);
        }

        .lb-row.disq {
            opacity: .5;
        }

        @keyframes rowIn {
            from {
                opacity: 0;
                transform: translateX(-8px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .lb-rnk {
            font-family: var(--fh);
            font-weight: 800;
            font-size: .82rem;
            color: var(--muted);
            text-align: center;
        }

        .lb-rnk.g {
            color: var(--gold);
        }

        .lb-rnk.s {
            color: var(--silver);
        }

        .lb-rnk.b {
            color: var(--bronze);
        }

        .lb-info {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }

        .lb-av {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            font-weight: 800;
            color: #fff;
            flex-shrink: 0;
        }

        .lb-name {
            font-size: .82rem;
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .lb-inst {
            font-size: .62rem;
            color: var(--muted);
            margin-top: 1px;
        }

        .lb-tags {
            display: flex;
            gap: 4px;
            margin-top: 2px;
            flex-wrap: wrap;
        }

        .tag {
            font-size: .57rem;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 800;
            font-family: var(--fh);
        }

        .tag-me {
            background: rgba(124, 92, 252, .2);
            color: var(--accent);
        }

        .tag-disq {
            background: rgba(255, 77, 106, .12);
            color: var(--red);
        }

        .tag-streak {
            background: rgba(255, 107, 53, .1);
            color: var(--fire);
        }

        .lb-cell {
            font-family: var(--fh);
            font-size: .78rem;
            font-weight: 700;
            text-align: right;
            white-space: nowrap;
        }

        .lb-cell.green {
            color: var(--green);
        }

        .lb-cell.purple {
            color: var(--accent);
        }

        .lb-cell.gold {
            color: var(--gold);
        }

        .lb-cell small {
            font-size: .62rem;
            color: var(--muted);
            font-weight: 400;
        }

        /* ── ANTI-CHEAT REPORT ── */
        .ac-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 12px;
        }

        .ac-card {
            background: var(--card);
            border-radius: var(--radius);
            padding: 16px 18px;
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }

        .ac-card.clean {
            border-color: rgba(0, 227, 150, .2);
        }

        .ac-card.warn {
            border-color: rgba(255, 159, 67, .25);
        }

        .ac-card.disq {
            border-color: rgba(255, 77, 106, .3);
        }

        .ac-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .ac-card.clean::before {
            background: var(--green);
        }

        .ac-card.warn::before {
            background: #ff9f43;
        }

        .ac-card.disq::before {
            background: var(--red);
        }

        .ac-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .ac-av {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .82rem;
            font-weight: 800;
            color: #fff;
            flex-shrink: 0;
        }

        .ac-name {
            font-weight: 800;
            font-size: .84rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
            min-width: 0;
        }

        .ac-sub {
            font-size: .67rem;
            color: var(--muted);
        }

        .ac-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .67rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .ac-status.clean {
            background: rgba(0, 227, 150, .1);
            color: var(--green);
            border: 1px solid rgba(0, 227, 150, .2);
        }

        .ac-status.warn {
            background: rgba(255, 159, 67, .1);
            color: #ff9f43;
            border: 1px solid rgba(255, 159, 67, .2);
        }

        .ac-status.disq {
            background: rgba(255, 77, 106, .1);
            color: var(--red);
            border: 1px solid rgba(255, 77, 106, .25);
        }

        .ac-metrics {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
        }

        .ac-m {
            background: rgba(255, 255, 255, .03);
            border-radius: 7px;
            padding: 8px;
            text-align: center;
        }

        .ac-m-val {
            font-family: var(--fh);
            font-weight: 800;
            font-size: 1.05rem;
        }

        .ac-m-val.ok {
            color: var(--green);
        }

        .ac-m-val.bad {
            color: var(--red);
        }

        .ac-m-val.neu {
            color: var(--ice);
        }

        .ac-m-lbl {
            font-size: .58rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-top: 2px;
        }

        .ac-disq-reason {
            margin-top: 10px;
            background: rgba(255, 77, 106, .06);
            border: 1px solid rgba(255, 77, 106, .2);
            border-radius: 6px;
            padding: 8px 10px;
            font-size: .72rem;
            color: var(--red);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ── STAT CHIPS ── */
        .stat-chips {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .chip {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chip-icon {
            font-size: .95rem;
        }

        .chip-val {
            font-family: var(--fh);
            font-weight: 800;
            font-size: 1rem;
        }

        .chip-lbl {
            font-size: .65rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        /* ── ACTION BAR ── */
        .action-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 11px 22px;
            border-radius: 10px;
            font-family: var(--fh);
            font-size: .84rem;
            font-weight: 800;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all .18s;
            white-space: nowrap;
        }

        .btn-grad {
            background: var(--gradient);
            color: #fff;
            box-shadow: 0 4px 20px rgba(124, 92, 252, .35);
        }

        .btn-grad:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 28px rgba(124, 92, 252, .5);
        }

        .btn-ghost {
            background: rgba(255, 255, 255, .05);
            border: 1px solid var(--border);
            color: var(--text);
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, .09);
        }

        /* ── CONFETTI ── */
        #resCanvas {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 9000;
        }
    </style>

    <canvas id="resCanvas"></canvas>

    <div class="res-wrap">

        {{-- ── HEADER ── --}}
        <div class="res-header">
            <span class="res-trophy">🏆</span>
            <div class="res-title">Battle Complete!</div>
            <div class="res-meta">
                <span>🎯 {{ $battle->code }}</span>
                <span>📚 {{ $battle->total_questions }} questions</span>
                <span>👥 {{ $battle->participants->count() }} participants</span>
                <span>🏫 {{ $battle->battle_type === '3school' ? '3-School' : '2-School' }} Battle</span>
                @if ($battle->finished_at)
                    <span>⏱ {{ $battle->started_at?->diffInMinutes($battle->finished_at) }} min</span>
                @endif
            </div>
        </div>

        {{-- ── INSTITUTION PODIUM ── --}}
        @php
            $rankings = $institutionRankings ?? [];
            $podColors = ['c1', 'c2', 'c3'];
            $podMedals = ['🥇', '🥈', '🥉'];
            $podClasses = ['r1', 'r2', 'r3'];
            $podOrder = [1 => 0, 0 => 1, 2 => 2]; // visual order: 2nd left, 1st center, 3rd right
            $totalQ = max(1, $battle->total_questions);
        @endphp

        @if (count($rankings) >= 2)
            <div>
                <div class="sec-hd">🏛 Institution Rankings</div>
                <div class="inst-podium">
                    @foreach ($podOrder as $visualPos => $dataIdx)
                        @if (isset($rankings[$dataIdx]))
                            @php $r = $rankings[$dataIdx]; @endphp
                            <div class="inst-slot {{ $podClasses[$dataIdx] }}">
                                <div class="inst-av {{ $podColors[$dataIdx] }}">
                                    {{ strtoupper(substr($r['institution_name'] ?? 'S', 0, 1)) }}
                                    <span class="inst-medal">{{ $podMedals[$dataIdx] }}</span>
                                </div>
                                <div class="inst-pod-name">{{ $r['institution_name'] }}</div>
                                <div class="inst-pod-score">{{ $r['total_score'] }}</div>
                                <div class="inst-pod-sub">{{ $r['total_students'] ?? 0 }} students ·
                                    {{ $r['average_accuracy'] ?? 0 }}% acc</div>
                                <div class="inst-pod-block">{{ $podMedals[$dataIdx] }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── INSTITUTION STAT CARDS ── --}}
        @if (count($rankings))
            <div>
                <div class="sec-hd">📊 Institution Stats</div>
                <div class="inst-cards">
                    @foreach ($rankings as $ri => $r)
                        <div class="inst-card rank-{{ $ri + 1 }}" style="animation-delay:{{ $ri * 0.08 }}s">
                            <div class="ic-head">
                                <div class="ic-icon {{ $podColors[$ri] ?? 'c1' }}">
                                    {{ strtoupper(substr($r['institution_name'] ?? 'S', 0, 1)) }}</div>
                                <div style="flex:1;min-width:0">
                                    <div class="ic-name">{{ $r['institution_name'] }}</div>
                                    <div style="font-size:.65rem;color:var(--muted)">School {{ $ri + 1 }}</div>
                                </div>
                                <div class="ic-rank">{{ $podMedals[$ri] ?? '' }}</div>
                            </div>
                            <div class="ic-score">{{ $r['total_score'] }}</div>
                            <div class="ic-stats">
                                <div class="ic-stat">
                                    <div class="v" style="color:var(--green)">{{ $r['total_students'] ?? 0 }}</div>
                                    <div class="l">Students</div>
                                </div>
                                <div class="ic-stat">
                                    <div class="v" style="color:var(--ice)">{{ $r['average_accuracy'] ?? 0 }}%</div>
                                    <div class="l">Accuracy</div>
                                </div>
                                <div class="ic-stat">
                                    <div class="v" style="color:var(--gold)">
                                        {{ $r['total_students'] ? round($r['total_score'] / $r['total_students']) : 0 }}
                                    </div>
                                    <div class="l">Avg Pts</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── MY PERFORMANCE (host if they have a participant record) ── --}}
        @if ($myParticipant)
            @php
                $myAcc = (int) round(($myParticipant->correct / $totalQ) * 100);
                $allSorted = $battle->participants->sortByDesc('score')->values();
                $myRank = $allSorted->search(fn($p) => $p->user_id === Auth::id()) + 1;
            @endphp
            <div>
                <div class="sec-hd">📈 Your Performance</div>
                <div class="my-card">
                    <div>
                        <div style="font-weight:900;font-size:.95rem">{{ Auth::user()->name }}</div>
                        <div style="font-size:.72rem;color:var(--muted)">Rank #{{ $myRank }} of
                            {{ $allSorted->count() }}</div>
                    </div>
                    <div style="text-align:center">
                        <div class="mc-val purple">{{ $myParticipant->score }}</div>
                        <div class="mc-label">Points</div>
                    </div>
                    <div style="text-align:center">
                        <div class="mc-val green">{{ $myParticipant->correct }}</div>
                        <div class="mc-label">Correct</div>
                    </div>
                    <div style="text-align:center">
                        <div class="mc-val red">{{ $myParticipant->wrong }}</div>
                        <div class="mc-label">Wrong</div>
                    </div>
                    <div style="text-align:center">
                        <div class="mc-val {{ $myAcc >= 70 ? 'green' : ($myAcc >= 40 ? 'gold' : 'red') }}">
                            {{ $myAcc }}%</div>
                        <div class="mc-label">Accuracy</div>
                    </div>
                    <div style="text-align:center">
                        <div class="mc-val gold">{{ $myParticipant->max_streak ?? $myParticipant->streak }}</div>
                        <div class="mc-label">Best Streak</div>
                    </div>
                    <div style="text-align:center">
                        <div class="mc-val ice">+{{ $myParticipant->xp_earned ?? 0 }}</div>
                        <div class="mc-label">XP</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── TOP 10 STUDENTS ── --}}
        @if ($battle->top_students && count($battle->top_students))
            <div>
                <div class="sec-hd">🌟 Top 10 Students</div>
                <div class="lb-table">
                    @foreach (array_slice($battle->top_students, 0, 10) as $idx => $stu)
                        @php
                            $rCls = $idx === 0 ? 'g' : ($idx === 1 ? 's' : ($idx === 2 ? 'b' : ''));
                            $medal = $idx === 0 ? '🥇' : ($idx === 1 ? '🥈' : ($idx === 2 ? '🥉' : '#' . ($idx + 1)));
                        @endphp
                        <div class="lb-row {{ ($stu['user_id'] ?? 0) === Auth::id() ? 'me' : '' }} {{ $stu['disqualified'] ?? false ? 'disq' : '' }}"
                            style="animation-delay:{{ $idx * 0.04 }}s">
                            <div class="lb-rnk {{ $rCls }}">{{ $medal }}</div>
                            <div class="lb-info">
                                <div class="lb-av">{{ strtoupper(substr($stu['name'] ?? 'S', 0, 1)) }}</div>
                                <div style="min-width:0">
                                    <div class="lb-name">{{ $stu['name'] }}</div>
                                    <div class="lb-inst">{{ $stu['institution_name'] ?? '' }}</div>
                                    <div class="lb-tags">
                                        @if (($stu['user_id'] ?? 0) === Auth::id())
                                            <span class="tag tag-me">YOU</span>
                                        @endif
                                        @if ($stu['disqualified'] ?? false)
                                            <span class="tag tag-disq">DISQ</span>
                                        @endif
                                        @if (($stu['streak'] ?? 0) >= 3)
                                            <span class="tag tag-streak">🔥 ×{{ $stu['streak'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="lb-cell green">{{ $stu['correct'] ?? 0 }}<small>/{{ $totalQ }}</small></div>
                            <div
                                class="lb-cell {{ ($stu['accuracy'] ?? 0) >= 70 ? 'green' : (($stu['accuracy'] ?? 0) >= 40 ? 'gold' : '') }}">
                                {{ $stu['accuracy'] ?? 0 }}%</div>
                            <div class="lb-cell purple">{{ $stu['score'] ?? 0 }}<small>pts</small></div>
                            <div class="lb-cell gold">+{{ $stu['xp'] ?? 0 }}<small>xp</small></div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── FULL LEADERBOARD ── --}}
        @if ($battle->final_scores && count($battle->final_scores))
            <div>
                <div class="sec-hd">📋 Full Leaderboard <span
                        style="font-weight:400;font-size:.8em;margin-left:4px;color:var(--muted)">{{ count($battle->final_scores) }}
                        participants</span></div>
                <div class="lb-table">
                    @foreach ($battle->final_scores as $idx => $stu)
                        @php
                            $rCls = $idx === 0 ? 'g' : ($idx === 1 ? 's' : ($idx === 2 ? 'b' : ''));
                            $medal = $idx === 0 ? '🥇' : ($idx === 1 ? '🥈' : ($idx === 2 ? '🥉' : '#' . ($idx + 1)));
                            $acc = $stu['accuracy'] ?? 0;
                        @endphp
                        <div class="lb-row {{ ($stu['user_id'] ?? 0) === Auth::id() ? 'me' : '' }} {{ $stu['disqualified'] ?? false ? 'disq' : '' }}"
                            style="animation-delay:{{ min($idx * 0.03, 0.6) }}s">
                            <div class="lb-rnk {{ $rCls }}">{{ $medal }}</div>
                            <div class="lb-info">
                                <div class="lb-av">{{ strtoupper(substr($stu['name'] ?? 'S', 0, 1)) }}</div>
                                <div style="min-width:0">
                                    <div class="lb-name">{{ $stu['name'] }}</div>
                                    <div class="lb-inst">{{ $stu['institution_name'] ?? '' }}</div>
                                    <div class="lb-tags">
                                        @if (($stu['user_id'] ?? 0) === Auth::id())
                                            <span class="tag tag-me">YOU</span>
                                        @endif
                                        @if ($stu['disqualified'] ?? false)
                                            <span class="tag tag-disq">DISQ</span>
                                        @endif
                                        @if (($stu['streak'] ?? 0) >= 3)
                                            <span class="tag tag-streak">🔥 ×{{ $stu['streak'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="lb-cell green">{{ $stu['correct'] ?? 0 }}<small>/{{ $totalQ }}</small></div>
                            <div class="lb-cell {{ $acc >= 70 ? 'green' : ($acc >= 40 ? 'gold' : '') }}">
                                {{ $acc }}%</div>
                            <div class="lb-cell purple">{{ $stu['score'] ?? 0 }}<small>pts</small></div>
                            <div class="lb-cell gold">+{{ $stu['xp'] ?? 0 }}<small>xp</small></div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── ANTI-CHEAT REPORT ── --}}
        @if ($battle->anti_cheat)
            @php
                $allParts = $battle->participants->sortByDesc('score');
            @endphp
            <div>
                <div class="sec-hd">🛡️ Anti-Cheat Report</div>
                <div class="ac-grid">
                    @foreach ($allParts as $p)
                        @php
                            $tabs = $p->tab_switches ?? 0;
                            $blurs = $p->window_blurs ?? 0;
                            $vios = $tabs + $blurs;
                            $disq = $p->disqualified ?? false;
                            $acState = $disq ? 'disq' : ($vios > 0 ? 'warn' : 'clean');
                            $pAcc = (int) round(($p->correct / $totalQ) * 100);
                        @endphp
                        <div class="ac-card {{ $acState }}">
                            <div class="ac-head">
                                <div class="ac-av">{{ strtoupper(substr($p->user->name, 0, 1)) }}</div>
                                <div style="flex:1;min-width:0">
                                    <div class="ac-name">{{ $p->user->name }}</div>
                                    <div class="ac-sub">{{ optional($p->institution)->name ?? '—' }} ·
                                        {{ $pAcc }}% acc</div>
                                </div>
                                <div class="ac-status {{ $acState }}">
                                    @if ($disq)
                                        ⛔ DISQ
                                    @elseif($vios > 0)
                                        ⚠️ {{ $vios }}
                                    @else
                                        ✅ Clean
                                    @endif
                                </div>
                            </div>
                            <div class="ac-metrics">
                                <div class="ac-m">
                                    <div class="ac-m-val {{ $tabs > 0 ? 'bad' : 'ok' }}">{{ $tabs }}</div>
                                    <div class="ac-m-lbl">Tab Switches</div>
                                </div>
                                <div class="ac-m">
                                    <div class="ac-m-val {{ $blurs > 0 ? 'bad' : 'ok' }}">{{ $blurs }}</div>
                                    <div class="ac-m-lbl">Win. Blurs</div>
                                </div>
                                <div class="ac-m">
                                    <div class="ac-m-val neu">{{ $p->score }}</div>
                                    <div class="ac-m-lbl">Score</div>
                                </div>
                            </div>
                            @if ($disq)
                                <div class="ac-disq-reason">
                                    <span>⛔</span>
                                    <span>{{ $p->disqualify_reason ?? 'Too many anti-cheat violations' }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── ROOM STATISTICS ── --}}
        @php
            $avgScore = $battle->participants->count() ? (int) round($battle->participants->avg('score')) : 0;
            $topStreak = $battle->participants->max(fn($p) => $p->max_streak ?? $p->streak) ?? 0;
            $disqCount = $battle->participants->where('disqualified', true)->count();
            $roomAcc = count($battle->final_scores ?? [])
                ? (int) round(collect($battle->final_scores)->avg('accuracy'))
                : 0;
            $duration =
                $battle->started_at && $battle->finished_at
                    ? $battle->started_at->diffInMinutes($battle->finished_at) . ' min'
                    : '—';
        @endphp
        <div>
            <div class="sec-hd">📈 Battle Statistics</div>
            <div class="stat-chips">
                <div class="chip">
                    <span class="chip-icon">👥</span>
                    <div>
                        <div class="chip-val">{{ $battle->participants->count() }}</div>
                        <div class="chip-lbl">Participants</div>
                    </div>
                </div>
                <div class="chip">
                    <span class="chip-icon">🎯</span>
                    <div>
                        <div class="chip-val">{{ $roomAcc }}%</div>
                        <div class="chip-lbl">Avg Accuracy</div>
                    </div>
                </div>
                <div class="chip">
                    <span class="chip-icon">⚡</span>
                    <div>
                        <div class="chip-val">{{ $avgScore }}</div>
                        <div class="chip-lbl">Avg Score</div>
                    </div>
                </div>
                <div class="chip">
                    <span class="chip-icon">⏱️</span>
                    <div>
                        <div class="chip-val">{{ $duration }}</div>
                        <div class="chip-lbl">Duration</div>
                    </div>
                </div>
                <div class="chip">
                    <span class="chip-icon">🔥</span>
                    <div>
                        <div class="chip-val">{{ $topStreak }}</div>
                        <div class="chip-lbl">Top Streak</div>
                    </div>
                </div>
                @if ($battle->anti_cheat)
                    <div class="chip"
                        style="{{ $disqCount > 0 ? 'border-color:rgba(255,77,106,.25)' : 'border-color:rgba(0,227,150,.2)' }}">
                        <span class="chip-icon">🛡️</span>
                        <div>
                            <div class="chip-val"
                                style="{{ $disqCount > 0 ? 'color:var(--red)' : 'color:var(--green)' }}">
                                {{ $disqCount }}</div>
                            <div class="chip-lbl">Disqualified</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── ACTION BAR ── --}}
        <div class="action-bar">
            <a href="{{ route('institution.dashboard') }}" class="btn btn-ghost">← Dashboard</a>
            <a href="{{-- route('institution.quiz.index') --}}" class="btn btn-ghost">📚 My Quizzes</a>
            @if ($battle->created_by === Auth::id())
                <a href="{{ route('institution.battle.setup') }}?quizId={{ $battle->quiz_id }}" class="btn btn-grad">⚔️
                    New Battle</a>
            @endif
        </div>

    </div>

    <script>
        // Confetti for winner institution
        const cvs = document.getElementById('resCanvas');
        const cx = cvs.getContext('2d');
        cvs.width = innerWidth;
        cvs.height = innerHeight;
        window.addEventListener('resize', () => {
            cvs.width = innerWidth;
            cvs.height = innerHeight;
        });

        @php
            $winnerInstId = $rankings[0]['institution_id'] ?? null;
            $myInstId = $myInst->id ?? null;
        @endphp

        const COLORS = ['#7c5cfc', '#00d4ff', '#ffd700', '#00e396', '#ff9f43', '#f5c842'];
        let pts = [];
        for (let i = 0; i < 120; i++) {
            pts.push({
                x: Math.random() * innerWidth,
                y: -10 - Math.random() * 160,
                vx: (Math.random() - .5) * 2.5,
                vy: 1.8 + Math.random() * 3.5,
                r: 4 + Math.random() * 5,
                a: 1,
                color: COLORS[Math.floor(Math.random() * COLORS.length)],
                rot: Math.random() * Math.PI * 2,
                rv: (Math.random() - .5) * .12,
                rect: Math.random() > .5,
            });
        }
        (function loop() {
            cx.clearRect(0, 0, cvs.width, cvs.height);
            pts.forEach(p => {
                p.x += p.vx;
                p.y += p.vy;
                p.rot += p.rv;
                cx.save();
                cx.translate(p.x, p.y);
                cx.rotate(p.rot);
                cx.globalAlpha = Math.min(1, p.a);
                cx.fillStyle = p.color;
                if (p.rect) cx.fillRect(-p.r / 2, -p.r / 2, p.r, p.r * 1.5);
                else {
                    cx.beginPath();
                    cx.arc(0, 0, p.r, 0, Math.PI * 2);
                    cx.fill();
                }
                cx.restore();
            });
            pts = pts.filter(p => p.y < innerHeight + 20);
            if (pts.length) requestAnimationFrame(loop);
            else cx.clearRect(0, 0, cvs.width, cvs.height);
        })();
    </script>
@endsection
