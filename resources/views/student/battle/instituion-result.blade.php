{{-- resources/views/student/institution-battle/results.blade.php --}}
{{--
    STUDENT INSTITUTION-BATTLE RESULTS
    Extends student.layout.master — NOT institution layout.
    Shows: my performance card, institution rankings, full leaderboard, my Q&A breakdown.
    Uses $battle (InstitutionBattle) not $room — adjusted accordingly.
    student/battle/results.blade.php is completely untouched.
--}}
@extends('student.layout.master')
@section('title', 'Battle Results ⚔️ ' . $battle->code)

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

        .results-wrap {
            max-width: 980px;
            margin: 0 auto;
            padding: 28px 18px 60px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* ── HEADER ── */
        .res-header {
            text-align: center;
            padding: 10px 0 6px;
        }

        .res-title {
            font-size: clamp(1.9rem, 5vw, 2.7rem);
            font-weight: 900;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -.5px;
        }

        .res-sub {
            color: var(--muted);
            font-size: .82rem;
            margin-top: 6px;
        }

        .res-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 215, 0, .08);
            border: 1px solid rgba(255, 215, 0, .2);
            color: var(--gold);
            padding: 4px 14px;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 700;
            margin-top: 10px;
        }

        /* ── WINNER BANNER ── */
        .winner-banner {
            background: linear-gradient(135deg, rgba(124, 92, 252, .12), rgba(0, 212, 255, .08));
            border: 1px solid rgba(124, 92, 252, .25);
            border-radius: var(--radius);
            padding: 26px 24px;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: bannerIn .6s cubic-bezier(.34, 1.26, .64, 1);
        }

        .winner-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 50% 0%, rgba(255, 215, 0, .07), transparent 70%);
            pointer-events: none;
        }

        @keyframes bannerIn {
            from {
                opacity: 0;
                transform: translateY(-18px) scale(.97)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .wn-crown {
            font-size: 2.8rem;
            margin-bottom: 6px;
            display: block;
            animation: crownBob 2s ease-in-out infinite;
        }

        @keyframes crownBob {

            0%,
            100% {
                transform: translateY(0) rotate(-3deg)
            }

            50% {
                transform: translateY(-5px) rotate(3deg)
            }
        }

        .wn-label {
            font-size: .72rem;
            color: var(--muted);
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .wn-name {
            font-size: clamp(1.3rem, 4vw, 1.9rem);
            font-weight: 900;
            color: var(--gold);
            margin: 4px 0 2px;
        }

        .wn-sub {
            font-size: .78rem;
            color: var(--muted);
        }

        /* ── SECTION HEADER ── */
        .sec-hd {
            font-size: .72rem;
            font-weight: 800;
            color: var(--muted);
            letter-spacing: .12em;
            text-transform: uppercase;
            margin-bottom: 10px;
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

        /* ── MY CARD ── */
        .my-card {
            background: var(--card);
            border: 1px solid rgba(124, 92, 252, .2);
            border-radius: var(--radius);
            padding: 20px 22px;
            display: grid;
            grid-template-columns: 1fr repeat(5, auto);
            gap: 12px;
            align-items: center;
            animation: slideUp .4s .15s both;
        }

        @media(max-width:640px) {
            .my-card {
                grid-template-columns: 1fr 1fr;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(14px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .mc-stat {
            text-align: center;
        }

        .mc-val {
            font-size: 1.4rem;
            font-weight: 900;
            font-family: var(--fh);
        }

        .mc-lbl {
            font-size: .65rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .06em;
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

        /* ── INSTITUTION RANKING ── */
        .inst-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }

        .inst-box {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: slideUp .4s both;
        }

        .inst-box.rank-1 {
            border-color: rgba(255, 215, 0, .3);
            background: linear-gradient(135deg, rgba(255, 215, 0, .05), var(--card));
        }

        .inst-box.rank-2 {
            border-color: rgba(192, 192, 192, .2);
        }

        .inst-box.rank-3 {
            border-color: rgba(205, 127, 50, .2);
        }

        .inst-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .inst-box.rank-1::before {
            background: linear-gradient(90deg, var(--gold), #ffb700);
        }

        .inst-box.rank-2::before {
            background: linear-gradient(90deg, var(--silver), #a0a0a0);
        }

        .inst-box.rank-3::before {
            background: linear-gradient(90deg, var(--bronze), #a05a20);
        }

        .inst-medal {
            font-size: 2rem;
            margin-bottom: 6px;
            display: block;
        }

        .inst-name {
            font-weight: 800;
            font-size: .9rem;
            margin-bottom: 6px;
        }

        .inst-score {
            font-family: var(--fh);
            font-size: 1.8rem;
            font-weight: 900;
            margin-bottom: 8px;
        }

        .rank-1 .inst-score {
            color: var(--gold);
        }

        .rank-2 .inst-score {
            color: var(--silver);
        }

        .rank-3 .inst-score {
            color: var(--bronze);
        }

        .inst-meta {
            font-size: .72rem;
            color: var(--muted);
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .my-inst-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 8px;
            font-size: .62rem;
            font-weight: 800;
            background: rgba(124, 92, 252, .15);
            color: var(--accent);
            border: 1px solid rgba(124, 92, 252, .25);
            margin-top: 6px;
        }

        /* ── LEADERBOARD ── */
        .lb-table {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .lb-row {
            display: grid;
            grid-template-columns: 36px 1fr auto auto auto;
            gap: 10px;
            align-items: center;
            padding: 11px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, .04);
            transition: background .2s;
            animation: rowIn .3s ease both;
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
            font-size: .82rem;
            font-weight: 800;
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
            font-size: .72rem;
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
        }

        .tag {
            font-size: .57rem;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 800;
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

        .lb-cell small {
            font-size: .62rem;
            color: var(--muted);
            font-weight: 400;
        }

        /* ── Q&A BREAKDOWN ── */
        .qa-grid {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .qa-row {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            display: flex;
            /* ← flex, not grid */
            gap: 12px;
            align-items: flex-start;
            transition: border-color .2s;
        }

        .qa-body {
            flex: 1;
            min-width: 0;
        }

        .qa-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .qa-q {
            font-size: .82rem;
            font-weight: 600;
            line-height: 1.55;
            flex: 1;
            min-width: 0;
            /* ← prevents overflow */
            margin-bottom: 0;
        }

        .qa-pts {
            font-family: var(--fh);
            font-size: .8rem;
            font-weight: 800;
            white-space: nowrap;
            flex-shrink: 0;
            /* ← never shrinks */
            margin-top: 1px;
        }

        .qa-row:hover {
            border-color: rgba(255, 255, 255, .12);
        }

        .qa-num {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--fh);
            font-size: .68rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .qa-num.correct {
            background: rgba(0, 227, 150, .12);
            color: var(--green);
            border: 1px solid rgba(0, 227, 150, .25);
        }

        .qa-num.wrong {
            background: rgba(255, 77, 106, .1);
            color: var(--red);
            border: 1px solid rgba(255, 77, 106, .2);
        }

        .qa-num.timeout {
            background: rgba(255, 159, 67, .1);
            color: #ff9f43;
            border: 1px solid rgba(255, 159, 67, .2);
        }

        .qa-q {
            font-size: .82rem;
            font-weight: 600;
            margin-bottom: 4px;
            line-height: 1.5;
        }

        .qa-answers {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 4px;
        }

        .qa-ans {
            font-size: .68rem;
            font-family: var(--fh);
            padding: 2px 8px;
            border-radius: 4px;
        }

        .qa-ans.user-correct {
            background: rgba(0, 227, 150, .1);
            color: var(--green);
            border: 1px solid rgba(0, 227, 150, .2);
        }

        .qa-ans.user-wrong {
            background: rgba(255, 77, 106, .1);
            color: var(--red);
            border: 1px solid rgba(255, 77, 106, .2);
        }

        .qa-ans.correct-ans {
            background: rgba(0, 212, 255, .08);
            color: var(--ice);
            border: 1px solid rgba(0, 212, 255, .2);
        }

        .qa-ans.timeout-ans {
            background: rgba(255, 159, 67, .08);
            color: #ff9f43;
            border: 1px solid rgba(255, 159, 67, .2);
        }

        .qa-pts {
            font-family: var(--fh);
            font-size: .8rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .qa-pts.pos {
            color: var(--green);
        }

        .qa-pts.zero {
            color: var(--muted);
        }

        .qa-expl {
            font-size: .72rem;
            color: var(--muted);
            margin-top: 5px;
            line-height: 1.6;
        }

        /* ── STAT CHIPS ── */
        .stat-chips {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .chip {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .78rem;
        }

        .chip-icon {
            font-size: 1rem;
        }

        .chip-val {
            font-weight: 800;
            font-family: var(--fh);
        }

        .chip-lbl {
            color: var(--muted);
            font-size: .68rem;
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

    <div class="results-wrap">

        {{-- ── HEADER ── --}}
        @php
            $rankings = $institutionRankings ?? [];
            $allSorted = $battle->participants->sortByDesc('score')->values();
            $totalQ = max(1, $battle->total_questions);
            $myP = $myParticipant;
            $myRank = $myP ? $allSorted->search(fn($p) => $p->user_id === Auth::id()) + 1 : null;
            $myAcc = $myP ? (int) round(($myP->correct / $totalQ) * 100) : 0;
            $topPlayer = $allSorted->first();
            $winnerInst = $rankings[0] ?? null;
            $letters = ['A', 'B', 'C', 'D'];
            $medals = ['🥇', '🥈', '🥉'];

            // Figure out which institution this student belongs to
            $myInstId = $myP ? $myP->institution_id : null;
        @endphp

        <div class="res-header">
            <div class="res-title">⚔️ Battle Complete!</div>
            <div class="res-sub">{{ $battle->code }} · {{ $battle->participants->count() }} Players · {{ $totalQ }}
                Questions</div>
            <div class="res-badge">🏛 {{ $battle->battle_type === '3school' ? '3-School' : '2-School' }} Institution Battle
            </div>
        </div>

        {{-- ── WINNER BANNER (institution winner) ── --}}
        @if ($winnerInst)
            <div class="winner-banner">
                <span class="wn-crown">🏆</span>
                <div class="wn-label">Winning Institution</div>
                <div class="wn-name">{{ $winnerInst['institution_name'] }}</div>
                <div class="wn-sub">{{ $winnerInst['total_score'] }} pts · {{ $winnerInst['total_students'] ?? 0 }} students
                    · {{ $winnerInst['average_accuracy'] ?? 0 }}% accuracy</div>
            </div>
        @elseif($topPlayer)
            <div class="winner-banner">
                <span class="wn-crown">🏆</span>
                <div class="wn-label">Top Player</div>
                <div class="wn-name">{{ $topPlayer->user->name }}@if ($topPlayer->user_id === Auth::id())
                        <span style="font-size:.7em;color:var(--muted)">(You!)</span>
                    @endif
                </div>
                <div class="wn-sub">{{ $topPlayer->score }} pts · {{ $topPlayer->correct }}/{{ $totalQ }} correct
                </div>
            </div>
        @endif

        {{-- ── MY PERFORMANCE ── --}}
        @if ($myP)
            <div>
                <div class="sec-hd">📊 Your Performance</div>
                <div class="my-card">
                    <div>
                        <div style="font-weight:800;font-size:.9rem">{{ Auth::user()->name }}</div>
                        <div style="font-size:.72rem;color:var(--muted)">
                            Rank #{{ $myRank }} of {{ $allSorted->count() }}
                            @if ($myP->institution)
                                · {{ $myP->institution->name }}
                            @endif
                        </div>
                    </div>
                    <div class="mc-stat">
                        <div class="mc-val purple">{{ $myP->score }}</div>
                        <div class="mc-lbl">Points</div>
                    </div>
                    <div class="mc-stat">
                        <div class="mc-val green">{{ $myP->correct }}</div>
                        <div class="mc-lbl">Correct</div>
                    </div>
                    <div class="mc-stat">
                        <div class="mc-val red">{{ $myP->wrong }}</div>
                        <div class="mc-lbl">Wrong</div>
                    </div>
                    <div class="mc-stat">
                        <div class="mc-val {{ $myAcc >= 70 ? 'green' : ($myAcc >= 40 ? 'gold' : 'red') }}">
                            {{ $myAcc }}%</div>
                        <div class="mc-lbl">Accuracy</div>
                    </div>
                    <div class="mc-stat">
                        <div class="mc-val gold">{{ $myP->max_streak ?? $myP->streak }}</div>
                        <div class="mc-lbl">Best Streak</div>
                    </div>
                    <div class="mc-stat">
                        <div class="mc-val ice">+{{ $myP->xp_earned ?? 0 }}</div>
                        <div class="mc-lbl">XP Earned</div>
                    </div>
                </div>
                @if ($myP->disqualified)
                    <div
                        style="margin-top:10px;padding:10px 14px;background:rgba(255,77,106,.07);border:1px solid rgba(255,77,106,.25);border-radius:8px;color:var(--red);font-size:.8rem;font-weight:700">
                        ⛔ You were disqualified — {{ $myP->disqualify_reason ?? 'Anti-cheat violation' }}
                    </div>
                @endif
            </div>
        @endif

        {{-- ── INSTITUTION RANKINGS ── --}}
        @if (count($rankings))
            <div>
                <div class="sec-hd">🏛 Institution Rankings</div>
                <div class="inst-grid">
                    @foreach ($rankings as $ri => $r)
                        <div class="inst-box rank-{{ $ri + 1 }}" style="animation-delay:{{ $ri * 0.08 }}s">
                            <span class="inst-medal">{{ $medals[$ri] ?? '#' . ($ri + 1) }}</span>
                            <div class="inst-name">{{ $r['institution_name'] }}</div>
                            <div class="inst-score">{{ $r['total_score'] }}</div>
                            <div class="inst-meta">
                                <span>👥 {{ $r['total_students'] ?? 0 }}</span>
                                <span>🎯 {{ $r['average_accuracy'] ?? 0 }}%</span>
                                <span>⚡ {{ $r['total_students'] ? round($r['total_score'] / $r['total_students']) : 0 }}
                                    avg</span>
                            </div>
                            @if ($myInstId && ($r['institution_id'] ?? null) == $myInstId)
                                <div><span class="my-inst-tag">⚔️ Your School</span></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── FULL LEADERBOARD ── --}}
        <div>
            <div class="sec-hd">📋 Full Leaderboard</div>
            <div class="lb-table">
                @foreach ($allSorted as $i => $p)
                    @php
                        $rk = $i + 1;
                        $rCls = $rk === 1 ? 'g' : ($rk === 2 ? 's' : ($rk === 3 ? 'b' : ''));
                        $medal = $rk === 1 ? '🥇' : ($rk === 2 ? '🥈' : ($rk === 3 ? '🥉' : '#' . $rk));
                        $pAcc = (int) round(($p->correct / $totalQ) * 100);
                    @endphp
                    <div class="lb-row {{ $p->user_id === Auth::id() ? 'me' : '' }} {{ $p->disqualified ? 'disq' : '' }}"
                        style="animation-delay:{{ min($i * 0.035, 0.6) }}s">
                        <div class="lb-rnk {{ $rCls }}">{{ $medal }}</div>
                        <div class="lb-info">
                            <div class="lb-av">{{ strtoupper(substr($p->user->name, 0, 1)) }}</div>
                            <div style="min-width:0">
                                <div class="lb-name">{{ $p->user->name }}</div>
                                <div class="lb-inst">{{ optional($p->institution)->name ?? '' }}</div>
                                <div class="lb-tags">
                                    @if ($p->user_id === Auth::id())
                                        <span class="tag tag-me">YOU</span>
                                    @endif
                                    @if ($p->disqualified)
                                        <span class="tag tag-disq">DISQ</span>
                                    @endif
                                    @if (($p->max_streak ?? $p->streak) >= 3)
                                        <span class="tag tag-streak">🔥 ×{{ $p->max_streak ?? $p->streak }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="lb-cell green">{{ $p->correct }}<small>/{{ $totalQ }}</small></div>
                        <div class="lb-cell {{ $pAcc >= 70 ? 'green' : ($pAcc >= 40 ? '' : '') }}">{{ $pAcc }}%
                        </div>
                        <div class="lb-cell purple">{{ $p->score }}<small>pts</small></div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── MY Q&A BREAKDOWN ── --}}
        @if ($myP)
            @php
                $myAnswers = \App\Models\InstitutionBattleQuestionAnswer::where('battle_id', $battle->id)
                    ->where('user_id', Auth::id())
                    ->get()
                    ->keyBy('question_index');
                $questions = $battle->quiz->questions ?? [];
            @endphp
            @if (count($questions) && $myAnswers->count())
                <div>
                    <div class="sec-hd">📝 Your Answer Breakdown</div>
                    <div class="qa-grid">
                        @foreach ($questions as $idx => $q)
                            @php
                                $ans = $myAnswers->get($idx);
                                $correct = (int) ($q['answer'] ?? 0);
                                $selected = $ans ? $ans->selected_option : -1;
                                $isRight = $ans && $ans->is_correct;
                                $timedOut = !$ans || $selected === -1;
                                $state = $timedOut ? 'timeout' : ($isRight ? 'correct' : 'wrong');
                                $pts = $ans ? $ans->points_earned : 0;
                            @endphp
                            <div class="qa-row">
                                <div class="qa-num {{ $state }}">{{ $idx + 1 }}</div>
                                <div class="qa-body">
                                    <div class="qa-top">
                                        <div class="qa-q">{{ $q['question'] ?? '' }}</div>
                                        <div class="qa-pts {{ $pts > 0 ? 'pos' : 'zero' }}">
                                            {{ $pts > 0 ? '+' . $pts : ($timedOut ? '—' : '0') }}
                                        </div>
                                    </div>
                                    <div class="qa-answers">
                                        {{-- REPLACE lines around the user-wrong span with this --}}
                                        @if ($timedOut)
                                            <span class="qa-ans timeout-ans">⏰ Timed Out</span>
                                            <span class="qa-ans correct-ans">✓ {{ $letters[$correct] }}:
                                                {{ $q['options'][$correct] ?? '' }}</span>
                                        @elseif($isRight)
                                            <span class="qa-ans user-correct">✓ {{ $letters[$selected] }}:
                                                {{ $q['options'][$selected] ?? '' }}</span>
                                        @else
                                            @if ($selected >= 0)
                                                <span class="qa-ans user-wrong">✗ {{ $letters[$selected] }}:
                                                    {{ $q['options'][$selected] ?? '' }}</span>
                                            @endif
                                            <span class="qa-ans correct-ans">✓ {{ $letters[$correct] }}:
                                                {{ $q['options'][$correct] ?? '' }}</span>
                                        @endif
                                    </div>
                                    <div class="qa-pts {{ $pts > 0 ? 'pos' : 'zero' }}">
                                        {{ $pts > 0 ? '+' . $pts : ($timedOut ? '—' : '0') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        {{-- ── BATTLE STATS ── --}}
        @php
            $avgScore = $allSorted->count() ? (int) round($allSorted->avg('score')) : 0;
            $topStreak = $allSorted->max(fn($p) => $p->max_streak ?? $p->streak) ?? 0;
            $disqCount = $allSorted->where('disqualified', true)->count();
            $duration =
                $battle->started_at && $battle->finished_at
                    ? $battle->started_at->diffInMinutes($battle->finished_at) . ' min'
                    : '—';
        @endphp
        <div>
            <div class="sec-hd">📈 Battle Stats</div>
            <div class="stat-chips">
                <div class="chip">
                    <span class="chip-icon">👥</span>
                    <div>
                        <div class="chip-val">{{ $allSorted->count() }}</div>
                        <div class="chip-lbl">Players</div>
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
                @if ($myRank)
                    <div class="chip" style="border-color:rgba(124,92,252,.25)">
                        <span class="chip-icon">🎯</span>
                        <div>
                            <div class="chip-val" style="color:var(--accent)">#{{ $myRank }}</div>
                            <div class="chip-lbl">Your Rank</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── ACTIONS ── --}}
        <div class="action-bar">
            <a href="{{ route('student.quiz.index') }}" class="btn btn-ghost">🏠 Dashboard</a>
            <a href="{{ route('student.battle.history') }}" class="btn btn-ghost">📜 Battle History</a>
            <a href="{{ route('student.battle.setup') }}" class="btn btn-grad">⚔️ New Battle</a>
        </div>

    </div>

    <script>
        // Confetti for top 3
        const cvs = document.getElementById('resCanvas');
        const cx = cvs.getContext('2d');
        cvs.width = innerWidth;
        cvs.height = innerHeight;
        window.addEventListener('resize', () => {
            cvs.width = innerWidth;
            cvs.height = innerHeight;
        });

        const MY_RANK = {{ $myRank ?? 99 }};
        if (MY_RANK <= 3) {
            const COLORS = ['#7c5cfc', '#00d4ff', '#ffd700', '#00e396', '#ff9f43', '#ff4d6a'];
            let pts = [];
            for (let i = 0; i < 160; i++) {
                pts.push({
                    x: Math.random() * innerWidth,
                    y: -10 - Math.random() * 200,
                    vx: (Math.random() - .5) * 3,
                    vy: 2 + Math.random() * 4,
                    r: 4 + Math.random() * 6,
                    color: COLORS[Math.floor(Math.random() * COLORS.length)],
                    rot: Math.random() * Math.PI * 2,
                    rv: (Math.random() - .5) * .15,
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
                    cx.fillStyle = p.color;
                    if (p.rect) cx.fillRect(-p.r / 2, -p.r / 2, p.r, p.r * 1.6);
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
        }
    </script>
@endsection
