{{-- resources/views/student/battle/results.blade.php --}}
@extends('student.layout.master')
@section('title', 'Battle Results ⚔️ ' . $room->code)

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap');

    :root {
        --accent:    #7c5cfc;
        --accent2:   #00d4ff;
        --green:     #00e396;
        --red:       #ff4d6a;
        --orange:    #ff9f43;
        --gold:      #ffd700;
        --silver:    #c0c0c0;
        --bronze:    #cd7f32;
        --bg:        #0b0c10;
        --card:      #13141a;
        --card2:     #1a1b24;
        --border:    rgba(255,255,255,.07);
        --border2:   rgba(255,255,255,.04);
        --text:      #eef0f7;
        --muted:     #6b7196;
        --gradient:  linear-gradient(135deg, #7c5cfc, #00d4ff);
        --fh:        'Syne', sans-serif;
        --fm:        'JetBrains Mono', monospace;
        --radius:    14px;
        --radius-sm: 9px;
    }

    body { font-family: var(--fh); background: var(--bg); color: var(--text); }

    /* ── PAGE LAYOUT ── */
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
        position: relative;
        padding: 10px 0 6px;
    }
    .res-title {
        font-size: clamp(1.9rem, 5vw, 2.8rem);
        font-weight: 900;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: -.5px;
        line-height: 1.1;
    }
    .res-sub {
        color: var(--muted);
        font-size: .82rem;
        margin-top: 6px;
        font-family: var(--fm);
    }
    .res-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,215,0,.08);
        border: 1px solid rgba(255,215,0,.2);
        color: var(--gold);
        padding: 4px 14px;
        border-radius: 20px;
        font-size: .72rem;
        font-weight: 700;
        font-family: var(--fm);
        margin-top: 10px;
    }

    /* ── WINNER BANNER ── */
    .winner-banner {
        background: linear-gradient(135deg, rgba(124,92,252,.12), rgba(0,212,255,.08));
        border: 1px solid rgba(124,92,252,.25);
        border-radius: var(--radius);
        padding: 26px 24px;
        text-align: center;
        position: relative;
        overflow: hidden;
        animation: bannerIn .6s cubic-bezier(.34,1.26,.64,1);
    }
    .winner-banner::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse at 50% 0%, rgba(255,215,0,.07), transparent 70%);
        pointer-events: none;
    }
    @keyframes bannerIn {
        from { opacity:0; transform: translateY(-18px) scale(.97); }
        to   { opacity:1; transform: none; }
    }
    .wn-crown { font-size: 2.8rem; margin-bottom: 6px; display: block; animation: crownBob 2s ease-in-out infinite; }
    @keyframes crownBob {
        0%,100% { transform: translateY(0) rotate(-3deg); }
        50%      { transform: translateY(-5px) rotate(3deg); }
    }
    .wn-label { font-size: .72rem; color: var(--muted); font-family: var(--fm); letter-spacing: .1em; text-transform: uppercase; }
    .wn-name  { font-size: clamp(1.4rem, 4vw, 2rem); font-weight: 900; color: var(--gold); margin: 4px 0 2px; }
    .wn-team  { font-size: .8rem; color: var(--muted); }

    /* ── MY CARD ── */
    .my-card {
        background: var(--card);
        border: 1px solid rgba(124,92,252,.2);
        border-radius: var(--radius);
        padding: 20px 22px;
        display: grid;
        grid-template-columns: 1fr repeat(4,auto);
        gap: 12px;
        align-items: center;
        animation: slideUp .4s .15s both;
    }
    @media(max-width:640px) {
        .my-card { grid-template-columns: 1fr 1fr; }
    }
    @keyframes slideUp {
        from { opacity:0; transform:translateY(14px); }
        to   { opacity:1; transform:none; }
    }
    .mc-title { font-weight: 800; font-size: .9rem; }
    .mc-sub   { font-size: .72rem; color: var(--muted); font-family: var(--fm); }
    .mc-stat  { text-align: center; }
    .mc-val   { font-size: 1.4rem; font-weight: 900; font-family: var(--fm); }
    .mc-lbl   { font-size: .65rem; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; }
    .mc-val.green  { color: var(--green); }
    .mc-val.red    { color: var(--red); }
    .mc-val.purple { background: var(--gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
    .mc-val.gold   { color: var(--gold); }

    /* ── SECTIONS ── */
    .section-hd {
        font-size: .72rem;
        font-weight: 800;
        color: var(--muted);
        letter-spacing: .12em;
        text-transform: uppercase;
        font-family: var(--fm);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-hd::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }

    /* ── PODIUM ── */
    .podium-row {
        display: flex;
        align-items: flex-end;
        justify-content: center;
        gap: 10px;
        padding: 0 10px 4px;
    }
    .podium-slot {
        flex: 1;
        max-width: 200px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0;
    }
    .podium-slot.p1 { order: 2; }
    .podium-slot.p2 { order: 1; }
    .podium-slot.p3 { order: 3; }

    .pod-avatar {
        width: 50px; height: 50px;
        border-radius: 50%;
        background: var(--gradient);
        display: flex; align-items: center; justify-content: center;
        font-weight: 900; font-size: 1.1rem; color: #fff;
        margin-bottom: 6px;
        position: relative;
    }
    .p1 .pod-avatar { width: 64px; height: 64px; font-size: 1.4rem; }
    .pod-avatar .pod-medal {
        position: absolute;
        bottom: -4px; right: -4px;
        font-size: 1rem;
        line-height: 1;
    }
    .pod-name {
        font-size: .75rem; font-weight: 800;
        max-width: 110px;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        text-align: center; margin-bottom: 3px;
    }
    .pod-score {
        font-family: var(--fm); font-weight: 700; font-size: .88rem;
        color: var(--accent2); margin-bottom: 6px;
    }
    .pod-block {
        width: 100%; border-radius: 8px 8px 0 0;
        display: flex; align-items: center; justify-content: center;
        font-family: var(--fm); font-weight: 900; font-size: 1.1rem;
        padding: 10px 0;
    }
    .p1 .pod-block { height: 80px; background: linear-gradient(180deg, rgba(255,215,0,.15), rgba(255,215,0,.06)); border: 1px solid rgba(255,215,0,.25); color: var(--gold); }
    .p2 .pod-block { height: 56px; background: linear-gradient(180deg, rgba(192,192,192,.1), rgba(192,192,192,.04)); border: 1px solid rgba(192,192,192,.18); color: var(--silver); }
    .p3 .pod-block { height: 40px; background: linear-gradient(180deg, rgba(205,127,50,.1), rgba(205,127,50,.04)); border: 1px solid rgba(205,127,50,.18); color: var(--bronze); }

    /* ── LEADERBOARD TABLE ── */
    .lb-table {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
    }
    .lb-row {
        display: grid;
        grid-template-columns: 36px 1fr auto auto auto auto;
        gap: 10px;
        align-items: center;
        padding: 11px 16px;
        border-bottom: 1px solid var(--border2);
        transition: background .2s;
        animation: rowIn .3s ease both;
    }
    .lb-row:last-child { border-bottom: none; }
    .lb-row:hover { background: rgba(255,255,255,.025); }
    .lb-row.me { background: rgba(124,92,252,.07); border-left: 3px solid var(--accent); }
    .lb-row.disq { opacity: .55; }
    @keyframes rowIn {
        from { opacity:0; transform:translateX(-8px); }
        to   { opacity:1; transform:none; }
    }
    .lb-rank {
        font-family: var(--fm); font-size: .8rem; font-weight: 800;
        color: var(--muted); text-align: center;
    }
    .lb-rank.top1 { color: var(--gold); }
    .lb-rank.top2 { color: var(--silver); }
    .lb-rank.top3 { color: var(--bronze); }

    .lb-info { display: flex; align-items: center; gap: 9px; min-width: 0; }
    .lb-av {
        width: 30px; height: 30px; border-radius: 50%;
        background: var(--gradient);
        display: flex; align-items: center; justify-content: center;
        font-size: .72rem; font-weight: 800; color: #fff; flex-shrink: 0;
    }
    .lb-name { font-size: .82rem; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .lb-tags { display: flex; gap: 4px; flex-shrink: 0; }
    .tag {
        font-size: .58rem; padding: 2px 6px; border-radius: 4px;
        font-weight: 800; font-family: var(--fm); white-space: nowrap;
    }
    .tag-me    { background: rgba(124,92,252,.2); color: var(--accent); }
    .tag-host  { background: rgba(255,215,0,.12); color: var(--gold); }
    .tag-a     { background: rgba(124,92,252,.15); color: var(--accent); }
    .tag-b     { background: rgba(0,227,150,.12); color: var(--green); }
    .tag-disq  { background: rgba(255,77,106,.12); color: var(--red); }
    .tag-vio   { background: rgba(255,159,67,.12); color: var(--orange); }
    .tag-streak { background: rgba(255,159,67,.1); color: var(--orange); }

    .lb-cell {
        font-family: var(--fm); font-size: .78rem; font-weight: 700;
        text-align: right; white-space: nowrap;
    }
    .lb-cell.green  { color: var(--green); }
    .lb-cell.red    { color: var(--red); }
    .lb-cell.purple { color: var(--accent); }
    .lb-cell.gold   { color: var(--gold); }
    .lb-cell small  { font-size: .65rem; color: var(--muted); font-weight: 400; }

    /* ── ANTI-CHEAT SECTION ── */
    .ac-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 12px;
    }
    .ac-card {
        background: var(--card);
        border-radius: var(--radius);
        padding: 16px 18px;
        border: 1px solid var(--border);
        position: relative;
        overflow: hidden;
        transition: border-color .2s;
    }
    .ac-card.clean  { border-color: rgba(0,227,150,.18); }
    .ac-card.warn   { border-color: rgba(255,159,67,.25); }
    .ac-card.disq   { border-color: rgba(255,77,106,.3); }
    .ac-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
    }
    .ac-card.clean::before  { background: var(--green); }
    .ac-card.warn::before   { background: var(--orange); }
    .ac-card.disq::before   { background: var(--red); }

    .ac-header { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
    .ac-av {
        width: 36px; height: 36px; border-radius: 50%;
        background: var(--gradient);
        display: flex; align-items: center; justify-content: center;
        font-size: .85rem; font-weight: 800; color: #fff; flex-shrink: 0;
    }
    .ac-name { font-weight: 800; font-size: .85rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .ac-sub  { font-size: .68rem; color: var(--muted); font-family: var(--fm); }

    .ac-status {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 20px;
        font-size: .68rem; font-weight: 800; font-family: var(--fm);
        margin-left: auto; flex-shrink: 0;
    }
    .ac-status.clean { background: rgba(0,227,150,.1); color: var(--green); border: 1px solid rgba(0,227,150,.2); }
    .ac-status.warn  { background: rgba(255,159,67,.1); color: var(--orange); border: 1px solid rgba(255,159,67,.2); }
    .ac-status.disq  { background: rgba(255,77,106,.1); color: var(--red); border: 1px solid rgba(255,77,106,.25); }

    .ac-metrics { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }
    .ac-m {
        background: rgba(255,255,255,.03);
        border-radius: 7px;
        padding: 8px 10px;
        text-align: center;
    }
    .ac-m-val { font-family: var(--fm); font-weight: 800; font-size: 1.1rem; }
    .ac-m-val.ok  { color: var(--green); }
    .ac-m-val.bad { color: var(--red); }
    .ac-m-val.neu { color: var(--accent2); }
    .ac-m-lbl { font-size: .6rem; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; margin-top: 2px; }

    .ac-disq-reason {
        margin-top: 10px;
        background: rgba(255,77,106,.06);
        border: 1px solid rgba(255,77,106,.18);
        border-radius: 6px;
        padding: 8px 10px;
        font-size: .72rem;
        color: var(--red);
        font-family: var(--fm);
        display: flex; align-items: center; gap: 6px;
    }

    /* ── TEAM SCORES ── */
    .team-vs {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 12px;
        align-items: center;
    }
    .team-box {
        background: var(--card);
        border-radius: var(--radius);
        padding: 20px;
        text-align: center;
        border: 1px solid var(--border);
        position: relative;
        overflow: hidden;
    }
    .team-box.winner {
        border-color: rgba(255,215,0,.3);
        background: linear-gradient(135deg, rgba(255,215,0,.05), var(--card));
    }
    .team-box.winner::after {
        content: '👑';
        position: absolute; top: 10px; right: 12px;
        font-size: 1.2rem;
    }
    .team-name { font-weight: 800; font-size: 1rem; margin-bottom: 8px; }
    .team-score { font-family: var(--fm); font-size: 2.2rem; font-weight: 900; }
    .team-score.a { color: var(--accent); }
    .team-score.b { color: var(--green); }
    .team-players { font-size: .7rem; color: var(--muted); margin-top: 6px; font-family: var(--fm); }
    .team-vs-divider { font-size: 1.5rem; text-align: center; font-weight: 900; color: var(--muted); }

    /* ── Q/A BREAKDOWN ── */
    .qa-grid { display: flex; flex-direction: column; gap: 8px; }
    .qa-row {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 12px 16px;
        display: grid;
        grid-template-columns: 28px 1fr auto;
        gap: 10px;
        align-items: start;
        transition: border-color .2s;
    }
    .qa-row:hover { border-color: rgba(255,255,255,.12); }
    .qa-num {
        width: 28px; height: 28px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-family: var(--fm); font-size: .68rem; font-weight: 800;
        flex-shrink: 0;
    }
    .qa-num.correct { background: rgba(0,227,150,.12); color: var(--green); border: 1px solid rgba(0,227,150,.25); }
    .qa-num.wrong   { background: rgba(255,77,106,.1); color: var(--red); border: 1px solid rgba(255,77,106,.2); }
    .qa-num.timeout { background: rgba(255,159,67,.1); color: var(--orange); border: 1px solid rgba(255,159,67,.2); }
    .qa-q { font-size: .82rem; font-weight: 600; margin-bottom: 4px; line-height: 1.5; }
    .qa-answer-row { display: flex; gap: 8px; flex-wrap: wrap; }
    .qa-ans {
        font-size: .68rem; font-family: var(--fm);
        padding: 2px 8px; border-radius: 4px;
    }
    .qa-ans.user-correct { background: rgba(0,227,150,.1); color: var(--green); border: 1px solid rgba(0,227,150,.2); }
    .qa-ans.user-wrong   { background: rgba(255,77,106,.1); color: var(--red); border: 1px solid rgba(255,77,106,.2); }
    .qa-ans.correct-ans  { background: rgba(0,212,255,.08); color: var(--accent2); border: 1px solid rgba(0,212,255,.2); }
    .qa-ans.timeout-ans  { background: rgba(255,159,67,.08); color: var(--orange); border: 1px solid rgba(255,159,67,.2); }
    .qa-pts {
        font-family: var(--fm); font-size: .8rem; font-weight: 800;
        white-space: nowrap;
    }
    .qa-pts.pos { color: var(--green); }
    .qa-pts.neg { color: var(--red); }
    .qa-expl { font-size: .72rem; color: var(--muted); margin-top: 5px; line-height: 1.6; }

    /* ── ACTION BAR ── */
    .action-bar {
        display: flex; gap: 10px; flex-wrap: wrap;
        justify-content: center;
        padding-top: 4px;
    }
    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 11px 22px; border-radius: 10px;
        font-family: var(--fh); font-size: .84rem; font-weight: 800;
        cursor: pointer; border: none; text-decoration: none;
        transition: all .18s; white-space: nowrap;
    }
    .btn-grad {
        background: linear-gradient(135deg, #7c5cfc, #00d4ff);
        color: #fff;
        box-shadow: 0 4px 20px rgba(124,92,252,.35);
    }
    .btn-grad:hover { transform: translateY(-2px); box-shadow: 0 6px 28px rgba(124,92,252,.5); }
    .btn-ghost {
        background: rgba(255,255,255,.05);
        border: 1px solid var(--border);
        color: var(--text);
    }
    .btn-ghost:hover { background: rgba(255,255,255,.09); border-color: rgba(255,255,255,.15); }
    .btn-red {
        background: rgba(255,77,106,.1);
        border: 1px solid rgba(255,77,106,.25);
        color: var(--red);
    }
    .btn-red:hover { background: rgba(255,77,106,.18); }

    /* ── STAT CHIPS ── */
    .stat-chips { display: flex; gap: 8px; flex-wrap: wrap; }
    .chip {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 10px 16px;
        display: flex; align-items: center; gap: 8px;
        font-size: .78rem;
    }
    .chip-icon { font-size: 1rem; }
    .chip-val  { font-weight: 800; font-family: var(--fm); }
    .chip-lbl  { color: var(--muted); font-size: .68rem; }

    /* ── CONFETTI CANVAS ── */
    #resCanvas { position:fixed; inset:0; pointer-events:none; z-index:9000; width:100%; height:100%; }

    /* ── RESPONSIVE ── */
    @media(max-width:640px) {
        .lb-row { grid-template-columns: 28px 1fr auto; }
        .lb-row .lb-cell:not(.lb-cell-pts) { display: none; }
        .lb-cell-pts { display: block !important; }
        .podium-row { gap: 6px; }
        .pod-name { font-size: .68rem; }
        .team-vs { grid-template-columns: 1fr auto 1fr; gap: 8px; }
    }
</style>

<canvas id="resCanvas"></canvas>

<div class="results-wrap">

    {{-- ── HEADER ── --}}
    <div class="res-header">
        <div class="res-title">⚔️ Battle Complete!</div>
        <div class="res-sub">Room {{ $room->code }} · {{ $room->participants->count() }} Players · {{ $room->total_questions }} Questions</div>
        @if($room->mode === 'team')
            <div class="res-badge">🏆 Team Battle</div>
        @elseif($room->mode === '1v1')
            <div class="res-badge">⚡ 1v1 Duel</div>
        @else
            <div class="res-badge">👥 Group Battle</div>
        @endif
    </div>

    {{-- ── WINNER BANNER ── --}}
    @php
        $sorted       = $room->participants->sortByDesc('score')->values();
        $topPlayer    = $sorted->first();
        $winnerTeam   = $room->winner_team;
        $myP          = $myParticipant;
        $myRank       = $sorted->search(fn($p) => $p->user_id === Auth::id()) + 1;
        $totalQ       = max(1, $room->total_questions);
        $myAccuracy   = $myP ? (int)round(($myP->correct / $totalQ) * 100) : 0;
    @endphp

    @if($room->mode === 'team' && $winnerTeam)
    <div class="winner-banner">
        <span class="wn-crown">🏆</span>
        <div class="wn-label">Team Winner</div>
        <div class="wn-name">{{ $winnerTeam }}</div>
        <div class="wn-team">
            @php
                $teamAScore = $room->participants->where('team','a')->sum('score');
                $teamBScore = $room->participants->where('team','b')->sum('score');
            @endphp
            {{ $room->team_a_name }} {{ $teamAScore }} — {{ $teamBScore }} {{ $room->team_b_name }}
        </div>
    </div>
    @elseif($topPlayer)
    <div class="winner-banner">
        <span class="wn-crown">🏆</span>
        <div class="wn-label">Battle Champion</div>
        <div class="wn-name">
            {{ $topPlayer->user->name }}
            @if($topPlayer->user_id === Auth::id()) <span style="font-size:.7em;color:var(--muted)">(You!)</span>@endif
        </div>
        <div class="wn-team" style="font-family:var(--fm)">{{ $topPlayer->score }} pts · {{ $topPlayer->correct }}/{{ $totalQ }} correct</div>
    </div>
    @endif

    {{-- ── MY PERFORMANCE CARD ── --}}
    @if($myP)
    <div>
        <div class="section-hd">📊 Your Performance</div>
        <div class="my-card">
            <div>
                <div class="mc-title">{{ Auth::user()->name }}</div>
                <div class="mc-sub">Rank #{{ $myRank }} of {{ $sorted->count() }} · {{ $room->mode }} mode</div>
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
                <div class="mc-val {{ $myAccuracy >= 70 ? 'green' : ($myAccuracy >= 40 ? 'gold' : 'red') }}">{{ $myAccuracy }}%</div>
                <div class="mc-lbl">Accuracy</div>
            </div>
            <div class="mc-stat">
                <div class="mc-val gold">{{ $myP->max_streak ?? $myP->streak }}</div>
                <div class="mc-lbl">Best Streak</div>
            </div>
            <div class="mc-stat">
                <div class="mc-val" style="color:var(--accent2)">+{{ $myP->xp_earned ?? 0 }}</div>
                <div class="mc-lbl">XP Earned</div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── TEAM SCORES (team mode) ── --}}
    @if($room->mode === 'team')
    @php
        $teamAScore = $room->participants->where('team','a')->sum('score');
        $teamBScore = $room->participants->where('team','b')->sum('score');
        $aWon = $teamAScore >= $teamBScore;
    @endphp
    <div>
        <div class="section-hd">🛡️ Team Scores</div>
        <div class="team-vs">
            <div class="team-box {{ $aWon ? 'winner' : '' }}">
                <div class="team-name" style="color:var(--accent)">{{ $room->team_a_name }}</div>
                <div class="team-score a">{{ $teamAScore }}</div>
                <div class="team-players">{{ $room->participants->where('team','a')->count() }} players</div>
            </div>
            <div class="team-vs-divider">VS</div>
            <div class="team-box {{ !$aWon ? 'winner' : '' }}">
                <div class="team-name" style="color:var(--green)">{{ $room->team_b_name }}</div>
                <div class="team-score b">{{ $teamBScore }}</div>
                <div class="team-players">{{ $room->participants->where('team','b')->count() }} players</div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── PODIUM (top 3) ── --}}
    @if($sorted->count() >= 2)
    <div>
        <div class="section-hd">🥇 Top Players</div>
        <div class="podium-row">
            @foreach([1 => ['p2','🥈'], 0 => ['p1','🥇'], 2 => ['p3','🥉']] as $i => [$cls, $medal])
                @if($sorted->has($i))
                @php $p = $sorted[$i]; @endphp
                <div class="podium-slot {{ $cls }}">
                    <div class="pod-avatar">
                        {{ strtoupper(substr($p->user->name, 0, 1)) }}
                        <span class="pod-medal">{{ $medal }}</span>
                    </div>
                    <div class="pod-name">{{ $p->user->name }}@if($p->user_id === Auth::id()) <span style="color:var(--accent)">✦</span>@endif</div>
                    <div class="pod-score">{{ $p->score }} pts</div>
                    <div class="pod-block">#{{ $i + 1 }}</div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── FULL LEADERBOARD ── --}}
    <div>
        <div class="section-hd">📋 Full Leaderboard</div>
        <div class="lb-table">
            @foreach($sorted as $i => $p)
            @php
                $rank = $i + 1;
                $rankCls = $rank === 1 ? 'top1' : ($rank === 2 ? 'top2' : ($rank === 3 ? 'top3' : ''));
                $pAccuracy = (int)round(($p->correct / $totalQ) * 100);
                $totalVios = ($p->tab_switches ?? 0) + ($p->window_blurs ?? 0);
            @endphp
            <div class="lb-row {{ $p->user_id === Auth::id() ? 'me' : '' }} {{ $p->disqualified ? 'disq' : '' }}"
                 style="animation-delay: {{ $i * 0.04 }}s">
                <div class="lb-rank {{ $rankCls }}">
                    @if($rank === 1) 🥇
                    @elseif($rank === 2) 🥈
                    @elseif($rank === 3) 🥉
                    @else #{{ $rank }}
                    @endif
                </div>
                <div class="lb-info">
                    <div class="lb-av">{{ strtoupper(substr($p->user->name, 0, 1)) }}</div>
                    <div>
                        <div class="lb-name">{{ $p->user->name }}</div>
                        <div class="lb-tags">
                            @if($p->user_id === Auth::id())<span class="tag tag-me">YOU</span>@endif
                            @if($p->user_id === $room->host_id)<span class="tag tag-host">HOST</span>@endif
                            @if($room->mode === 'team' && $p->team)<span class="tag tag-{{ $p->team }}">{{ strtoupper($p->team) }}</span>@endif
                            @if($p->disqualified)<span class="tag tag-disq">DISQ</span>@endif
                            @if(!$p->disqualified && $totalVios > 0)<span class="tag tag-vio">⚠️ {{ $totalVios }} VIO</span>@endif
                            @if(($p->max_streak ?? $p->streak) >= 3)<span class="tag tag-streak">🔥 ×{{ $p->max_streak ?? $p->streak }}</span>@endif
                        </div>
                    </div>
                </div>
                <div class="lb-cell green">{{ $p->correct }}<small>/{{ $totalQ }}</small></div>
                <div class="lb-cell {{ $pAccuracy >= 70 ? 'green' : ($pAccuracy >= 40 ? 'gold' : 'red') }}">{{ $pAccuracy }}%</div>
                <div class="lb-cell purple lb-cell-pts">{{ $p->score }}<small>pts</small></div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── ANTI-CHEAT REPORT ── --}}
    @if($room->anti_cheat)
    <div>
        <div class="section-hd">🛡️ Anti-Cheat Report</div>
        <div class="ac-grid">
            @foreach($sorted as $p)
            @php
                $tabs    = $p->tab_switches ?? 0;
                $blurs   = $p->window_blurs ?? 0;
                $vios    = $tabs + $blurs;
                $disq    = $p->disqualified ?? false;
                $acState = $disq ? 'disq' : ($vios > 0 ? 'warn' : 'clean');
                $pAccuracy = (int)round(($p->correct / $totalQ) * 100);
            @endphp
            <div class="ac-card {{ $acState }}">
                <div class="ac-header">
                    <div class="ac-av">{{ strtoupper(substr($p->user->name, 0, 1)) }}</div>
                    <div style="min-width:0;flex:1">
                        <div class="ac-name">
                            {{ $p->user->name }}
                            @if($p->user_id === Auth::id()) <span style="color:var(--accent);font-size:.7em">● You</span>@endif
                        </div>
                        <div class="ac-sub">
                            @if($room->mode === 'team') Team {{ strtoupper($p->team ?? '?') }} · @endif
                            {{ $pAccuracy }}% accuracy
                        </div>
                    </div>
                    <div class="ac-status {{ $acState }}">
                        @if($disq) ⛔ DISQ
                        @elseif($vios > 0) ⚠️ {{ $vios }} VIO
                        @else ✅ CLEAN
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
                        <div class="ac-m-lbl">Window Blurs</div>
                    </div>
                    <div class="ac-m">
                        <div class="ac-m-val neu">{{ $p->score }}</div>
                        <div class="ac-m-lbl">Final Score</div>
                    </div>
                </div>
                @if($disq)
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

    {{-- ── MY Q&A BREAKDOWN ── --}}
    @if($myP)
    @php
        $myAnswers = \App\Models\BattleQuestionAnswer::where('room_id', $room->id)
            ->where('user_id', Auth::id())
            ->get()
            ->keyBy('question_index');
        $questions = $room->quiz->questions ?? [];
        $letters   = ['A','B','C','D'];
    @endphp
    @if(count($questions) && $myAnswers->count())
    <div>
        <div class="section-hd">📝 Your Answer Breakdown</div>
        <div class="qa-grid">
            @foreach($questions as $idx => $q)
            @php
                $ans      = $myAnswers->get($idx);
                $correct  = (int)($q['answer'] ?? 0);
                $selected = $ans ? $ans->selected_option : -1;
                $isRight  = $ans && $ans->is_correct;
                $timedOut = !$ans || $selected === -1;
                $state    = $timedOut ? 'timeout' : ($isRight ? 'correct' : 'wrong');
                $pts      = $ans ? $ans->points_earned : 0;
            @endphp
            <div class="qa-row">
                <div class="qa-num {{ $state }}">{{ $idx + 1 }}</div>
                <div>
                    <div class="qa-q">{{ $q['question'] ?? '' }}</div>
                    <div class="qa-answer-row">
                        @if($timedOut)
                            <span class="qa-ans timeout-ans">⏰ Timed Out</span>
                            <span class="qa-ans correct-ans">✓ {{ $letters[$correct] }}: {{ $q['options'][$correct] ?? '' }}</span>
                        @elseif($isRight)
                            <span class="qa-ans user-correct">✓ {{ $letters[$selected] }}: {{ $q['options'][$selected] ?? '' }}</span>
                        @else
                            <span class="qa-ans user-wrong">✗ {{ $letters[$selected] }}: {{ $q['options'][$selected] ?? '' }}</span>
                            <span class="qa-ans correct-ans">✓ {{ $letters[$correct] }}: {{ $q['options'][$correct] ?? '' }}</span>
                        @endif
                    </div>
                    @if(!empty($q['explanation']))
                    <div class="qa-expl">{{ $q['explanation'] }}</div>
                    @endif
                </div>
                <div class="qa-pts {{ $pts > 0 ? 'pos' : 'neg' }}">
                    {{ $pts > 0 ? '+' . $pts : ($timedOut ? '–' : '0') }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endif

    {{-- ── QUICK STATS ── --}}
    @php
        $totalAnswered  = \App\Models\BattleQuestionAnswer::where('room_id', $room->id)->count();
        $totalCorrect   = \App\Models\BattleQuestionAnswer::where('room_id', $room->id)->where('is_correct', true)->count();
        $roomAccuracy   = $totalAnswered > 0 ? (int)round(($totalCorrect / $totalAnswered) * 100) : 0;
        $avgScore       = $sorted->count() ? (int)round($sorted->avg('score')) : 0;
        $duration       = $room->started_at && $room->finished_at
                            ? $room->started_at->diffInMinutes($room->finished_at) . ' min'
                            : '—';
    @endphp
    <div>
        <div class="section-hd">📈 Room Statistics</div>
        <div class="stat-chips">
            <div class="chip">
                <span class="chip-icon">👥</span>
                <div>
                    <div class="chip-val">{{ $sorted->count() }}</div>
                    <div class="chip-lbl">Players</div>
                </div>
            </div>
            <div class="chip">
                <span class="chip-icon">🎯</span>
                <div>
                    <div class="chip-val">{{ $roomAccuracy }}%</div>
                    <div class="chip-lbl">Room Accuracy</div>
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
                    <div class="chip-val">{{ $sorted->max(fn($p) => $p->max_streak ?? $p->streak) }}</div>
                    <div class="chip-lbl">Top Streak</div>
                </div>
            </div>
            @if($room->anti_cheat)
            @php $disqCount = $sorted->where('disqualified', true)->count(); @endphp
            <div class="chip" style="border-color: {{ $disqCount > 0 ? 'rgba(255,77,106,.25)' : 'rgba(0,227,150,.2)' }}">
                <span class="chip-icon">🛡️</span>
                <div>
                    <div class="chip-val" style="color: {{ $disqCount > 0 ? 'var(--red)' : 'var(--green)' }}">{{ $disqCount }}</div>
                    <div class="chip-lbl">Disqualified</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ── ACTION BAR ── --}}
    <div class="action-bar">
        @if(Auth::id() === $room->host_id)
        <button class="btn btn-grad" onclick="doRematch()">⚔️ Rematch</button>
        @endif
        <a href="{{ route('student.battle.setup') }}?quizId={{ $room->quiz_id }}" class="btn btn-ghost">🆕 New Battle</a>
        <a href="{{ route('student.battle.history') }}" class="btn btn-ghost">📜 History</a>
        <a href="{{ route('student.quiz.index') }}" class="btn btn-ghost">🏠 Dashboard</a>
    </div>

</div>

<script>
// ── CONFETTI ──────────────────────────────────────────────────────────────
const cvs = document.getElementById('resCanvas');
const cx  = cvs.getContext('2d');
cvs.width  = innerWidth;
cvs.height = innerHeight;
window.addEventListener('resize', () => { cvs.width = innerWidth; cvs.height = innerHeight; });

@php $myRankVal = $myRank ?? 99; @endphp
const MY_RANK = {{ $myRankVal }};

let confettiParts = [];
if (MY_RANK <= 3) {
    const COLORS = ['#7c5cfc','#00d4ff','#ffd700','#00e396','#ff9f43','#ff4d6a'];
    for (let i = 0; i < 160; i++) {
        confettiParts.push({
            x: Math.random() * innerWidth,
            y: -10 - Math.random() * 200,
            vx: (Math.random() - .5) * 3,
            vy: 2 + Math.random() * 4,
            r: 4 + Math.random() * 6,
            color: COLORS[Math.floor(Math.random() * COLORS.length)],
            rot: Math.random() * Math.PI * 2,
            rv: (Math.random() - .5) * .15,
            shape: Math.random() > .5 ? 'rect' : 'circle',
        });
    }
    (function loop() {
        cx.clearRect(0, 0, cvs.width, cvs.height);
        confettiParts.forEach(p => {
            p.x += p.vx; p.y += p.vy; p.rot += p.rv;
            cx.save(); cx.translate(p.x, p.y); cx.rotate(p.rot);
            cx.fillStyle = p.color;
            if (p.shape === 'circle') {
                cx.beginPath(); cx.arc(0, 0, p.r, 0, Math.PI * 2); cx.fill();
            } else {
                cx.fillRect(-p.r / 2, -p.r / 2, p.r, p.r * 1.6);
            }
            cx.restore();
        });
        confettiParts = confettiParts.filter(p => p.y < innerHeight + 20);
        if (confettiParts.length) requestAnimationFrame(loop);
        else cx.clearRect(0, 0, cvs.width, cvs.height);
    })();
}

// ── REMATCH ───────────────────────────────────────────────────────────────
async function doRematch() {
    const btn = event.target;
    btn.disabled = true; btn.textContent = 'Creating…';
    try {
        const r = await fetch('{{ route("student.battle.rematch") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ code: '{{ $room->code }}' }),
        });
        const d = await r.json();
        if (d.success) window.location.href = d.redirectUrl;
        else { alert(d.message || 'Failed'); btn.disabled = false; btn.textContent = '⚔️ Rematch'; }
    } catch(e) {
        btn.disabled = false; btn.textContent = '⚔️ Rematch';
    }
}

// ── ANIMATE NUMBER COUNTERS ───────────────────────────────────────────────
document.querySelectorAll('.mc-val[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count);
    let start = 0;
    const dur = 900, s = performance.now();
    const f = t => {
        const p = Math.min((t - s) / dur, 1);
        el.textContent = Math.round(start + (target - start) * p);
        if (p < 1) requestAnimationFrame(f);
    };
    requestAnimationFrame(f);
});
</script>
@endsection