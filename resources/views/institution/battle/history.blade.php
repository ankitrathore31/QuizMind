{{-- resources/views/institution/battle/history.blade.php --}}
@extends('institution.layout.master')
@section('page_title', '⚔️ Battle History')

@section('content')
<style>
    :root {
        --gold:#ffd700; --gold-dim:rgba(255,215,0,.08);
        --silver:#c0c0c0; --bronze:#cd7f32;
        --green:#00e396; --red:#ff4d6a;
        --ice:#00d4ff;   --fire:#ff6b35;
        --win-c:rgba(0,227,150,.1);   --win-b:rgba(0,227,150,.25);
        --loss-c:rgba(255,77,106,.07);--loss-b:rgba(255,77,106,.2);
    }

    .history-wrap { max-width:1140px; margin:0 auto; padding:32px 18px 72px; }

    /* ── PAGE HEADER ── */
    .ph {
        display:flex; align-items:flex-start; justify-content:space-between;
        flex-wrap:wrap; gap:16px; margin-bottom:32px;
    }
    .ph-left {}
    .ph-badge {
        display:inline-flex; align-items:center; gap:6px;
        background:var(--gold-dim); border:1px solid rgba(255,215,0,.25);
        color:var(--gold); padding:4px 12px; border-radius:16px;
        font-size:.68rem; font-weight:800; letter-spacing:1px;
        text-transform:uppercase; margin-bottom:10px;
    }
    .ph-title {
        font-family:var(--fh); font-size:clamp(1.6rem,4vw,2.2rem);
        font-weight:900; margin-bottom:4px; line-height:1.15;
    }
    .ph-sub { font-size:.8rem; color:var(--muted); }
    .ph-action {
        display:inline-flex; align-items:center; gap:7px;
        padding:10px 20px; border-radius:10px;
        background:var(--gradient); color:#fff; font-family:var(--fh);
        font-size:.83rem; font-weight:800; text-decoration:none;
        border:none; cursor:pointer; transition:all .2s; white-space:nowrap;
        box-shadow:0 4px 18px rgba(124,92,252,.3);
    }
    .ph-action:hover { transform:translateY(-2px); box-shadow:0 6px 26px rgba(124,92,252,.45); }

    /* ── SUMMARY STAT CARDS ── */
    .summary-grid {
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(150px,1fr));
        gap:12px; margin-bottom:28px;
    }
    .sum-card {
        background:var(--card); border:1.5px solid var(--border);
        border-radius:var(--radius); padding:18px 16px; text-align:center;
        position:relative; overflow:hidden;
        animation:cardUp .4s cubic-bezier(.34,1.26,.64,1) both;
    }
    .sum-card::before {
        content:''; position:absolute; top:0; left:0; right:0; height:3px;
    }
    .sum-card.c-purple::before { background:var(--gradient); }
    .sum-card.c-green::before  { background:linear-gradient(90deg,#00e396,#00a87d); }
    .sum-card.c-red::before    { background:linear-gradient(90deg,#ff4d6a,#cc2244); }
    .sum-card.c-gold::before   { background:linear-gradient(90deg,#ffd700,#ffb700); }
    .sum-card.c-ice::before    { background:linear-gradient(90deg,#00d4ff,#0099cc); }
    .sum-card.c-fire::before   { background:linear-gradient(90deg,#ff6b35,#cc4400); }

    @keyframes cardUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:none} }

    .sum-icon { font-size:1.4rem; margin-bottom:8px; display:block; }
    .sum-val  {
        font-family:var(--fh); font-size:1.8rem; font-weight:900;
        line-height:1; margin-bottom:4px;
        background:var(--gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    }
    .sum-val.green  { background:linear-gradient(135deg,#00e396,#00a87d); -webkit-background-clip:text; }
    .sum-val.red    { background:linear-gradient(135deg,#ff4d6a,#cc2244); -webkit-background-clip:text; }
    .sum-val.gold   { background:linear-gradient(135deg,#ffd700,#ffb700); -webkit-background-clip:text; }
    .sum-val.ice    { background:linear-gradient(135deg,#00d4ff,#0099cc); -webkit-background-clip:text; }
    .sum-lbl  { font-size:.67rem; color:var(--muted); text-transform:uppercase; letter-spacing:.08em; font-weight:700; }

    /* ── WIN RATE BAR ── */
    .winrate-bar-wrap {
        background:var(--card); border:1.5px solid var(--border);
        border-radius:var(--radius); padding:18px 22px; margin-bottom:28px;
        display:flex; align-items:center; gap:20px; flex-wrap:wrap;
    }
    .wr-label { font-size:.8rem; font-weight:800; flex-shrink:0; min-width:90px; }
    .wr-track {
        flex:1; height:10px; background:rgba(255,77,106,.15);
        border-radius:5px; overflow:hidden; min-width:120px;
        position:relative;
    }
    .wr-fill {
        height:100%; border-radius:5px;
        background:linear-gradient(90deg,#00e396,#00a87d);
        transition:width 1.2s cubic-bezier(.34,1.26,.64,1);
    }
    .wr-pct {
        font-family:var(--fh); font-size:1.1rem; font-weight:900;
        color:var(--green); flex-shrink:0; min-width:46px; text-align:right;
    }
    .wr-meta { font-size:.75rem; color:var(--muted); flex-shrink:0; }

    /* ── SECTION HEADER ── */
    .sec-hd {
        font-size:.7rem; font-weight:800; color:var(--muted);
        letter-spacing:.12em; text-transform:uppercase; font-family:var(--fh);
        margin-bottom:14px; display:flex; align-items:center; gap:8px;
    }
    .sec-hd::after { content:''; flex:1; height:1px; background:var(--border); }

    /* ── HISTORY CARDS ── */
    .history-list { display:flex; flex-direction:column; gap:12px; }

    .hcard {
        background:var(--card); border:1.5px solid var(--border);
        border-radius:var(--radius); overflow:hidden;
        transition:border-color .25s, transform .2s, box-shadow .2s;
        animation:rowIn .35s ease both;
        cursor:default;
    }
    .hcard:hover { transform:translateY(-2px); box-shadow:0 8px 32px rgba(0,0,0,.25); }
    .hcard.win  { border-color:var(--win-b); }
    .hcard.loss { border-color:var(--loss-b); }
    @keyframes rowIn { from{opacity:0;transform:translateX(-10px)} to{opacity:1;transform:none} }

    /* Card top stripe */
    .hcard-stripe {
        height:3px;
        background:linear-gradient(90deg,var(--green),#00a87d);
    }
    .hcard.loss .hcard-stripe { background:linear-gradient(90deg,var(--red),#cc2244); }

    .hcard-body { padding:16px 20px; display:grid; grid-template-columns:auto 1fr auto; gap:16px; align-items:center; }
    @media(max-width:640px) { .hcard-body { grid-template-columns:1fr; } }

    /* Left: rank badge */
    .hcard-rank {
        width:54px; height:54px; border-radius:14px; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
        font-size:1.5rem; font-weight:900; font-family:var(--fh);
    }
    .hcard-rank.r1 { background:rgba(255,215,0,.1); border:1.5px solid rgba(255,215,0,.3); }
    .hcard-rank.r2 { background:rgba(192,192,192,.08); border:1.5px solid rgba(192,192,192,.2); }
    .hcard-rank.r3 { background:rgba(205,127,50,.08); border:1.5px solid rgba(205,127,50,.2); }
    .hcard-rank.rX { background:rgba(255,77,106,.07); border:1.5px solid rgba(255,77,106,.18); font-size:1rem; color:var(--muted); }

    /* Middle: info */
    .hcard-info {}
    .hcard-top { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:5px; }
    .hcard-quiz { font-weight:800; font-size:.92rem; }
    .result-pill {
        padding:2px 10px; border-radius:10px; font-size:.65rem; font-weight:800;
        letter-spacing:.05em; text-transform:uppercase; flex-shrink:0;
    }
    .result-pill.win  { background:var(--win-c);  border:1px solid var(--win-b);  color:var(--green); }
    .result-pill.loss { background:var(--loss-c); border:1px solid var(--loss-b); color:var(--red); }

    .hcard-meta {
        display:flex; align-items:center; gap:12px; flex-wrap:wrap;
        font-size:.75rem; color:var(--muted);
    }
    .hcard-meta span { display:flex; align-items:center; gap:4px; }

    /* Stat chips inside card */
    .hcard-stats {
        display:flex; gap:6px; margin-top:10px; flex-wrap:wrap;
    }
    .hcs {
        background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.06);
        border-radius:7px; padding:5px 10px; font-size:.72rem; text-align:center;
        min-width:58px;
    }
    .hcs .v { font-weight:800; font-family:var(--fh); font-size:.88rem; }
    .hcs .l { font-size:.58rem; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; margin-top:1px; }
    .hcs .v.green { color:var(--green); }
    .hcs .v.ice   { color:var(--ice); }
    .hcs .v.gold  { color:var(--gold); }
    .hcs .v.fire  { color:var(--fire); }

    /* Right: action */
    .hcard-action { flex-shrink:0; display:flex; flex-direction:column; align-items:flex-end; gap:8px; }
    .hcard-date   { font-size:.68rem; color:var(--muted); white-space:nowrap; }
    .view-btn {
        display:inline-flex; align-items:center; gap:5px;
        padding:6px 14px; border-radius:8px; font-size:.72rem; font-weight:800;
        text-decoration:none; transition:all .15s; white-space:nowrap;
        background:rgba(124,92,252,.1); border:1px solid rgba(124,92,252,.25); color:var(--accent);
    }
    .view-btn:hover { background:rgba(124,92,252,.2); border-color:rgba(124,92,252,.4); }

    /* ── EMPTY STATE ── */
    .empty-state {
        text-align:center; padding:60px 24px;
        background:var(--card); border:1.5px dashed var(--border);
        border-radius:var(--radius);
    }
    .empty-icon { font-size:3.5rem; margin-bottom:14px; display:block; opacity:.6; animation:floatLoop 3s ease-in-out infinite; }
    @keyframes floatLoop { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
    .empty-title { font-family:var(--fh); font-size:1.1rem; font-weight:800; margin-bottom:6px; }
    .empty-sub   { font-size:.82rem; color:var(--muted); margin-bottom:20px; }

    /* ── PAGINATION ── */
    .pag-wrap { display:flex; justify-content:center; margin-top:24px; }
    .pag-wrap .pagination { display:flex; gap:4px; list-style:none; padding:0; margin:0; }
    .pag-wrap .page-item .page-link {
        display:flex; align-items:center; justify-content:center;
        width:36px; height:36px; border-radius:8px; font-size:.8rem; font-weight:700;
        border:1px solid var(--border); background:var(--card); color:var(--muted);
        text-decoration:none; transition:all .15s;
    }
    .pag-wrap .page-item.active .page-link { background:var(--gradient); border-color:transparent; color:#fff; }
    .pag-wrap .page-item .page-link:hover { border-color:var(--accent); color:var(--accent); }
    .pag-wrap .page-item.disabled .page-link { opacity:.4; cursor:not-allowed; }

    /* ── OPPONENTS CHIPS ── */
    .opp-chips { display:flex; gap:5px; flex-wrap:wrap; margin-top:6px; }
    .opp-chip {
        font-size:.62rem; padding:2px 8px; border-radius:6px; font-weight:700;
        background:rgba(0,212,255,.06); border:1px solid rgba(0,212,255,.15); color:var(--ice);
    }
</style>

<div class="history-wrap">

    {{-- ── PAGE HEADER ── --}}
    <div class="ph">
        <div class="ph-left">
            <div class="ph-badge">⚔️ Institution Battle History</div>
            <h1 class="ph-title">Battle Records</h1>
            <p class="ph-sub">{{ $institution->name }} · All completed institution battles</p>
        </div>
        <a href="{{ route('institution.battle.setup') }}" class="ph-action">
            ⚔️ New Battle
        </a>
    </div>

    {{-- ── SUMMARY STATS ── --}}
    @php
        $ss = $summaryStats;
    @endphp
    <div class="summary-grid">
        <div class="sum-card c-purple" style="animation-delay:.05s">
            <span class="sum-icon">⚔️</span>
            <div class="sum-val">{{ $ss['total_played'] }}</div>
            <div class="sum-lbl">Total Battles</div>
        </div>
        <div class="sum-card c-green" style="animation-delay:.1s">
            <span class="sum-icon">🏆</span>
            <div class="sum-val green">{{ $ss['total_wins'] }}</div>
            <div class="sum-lbl">Wins</div>
        </div>
        <div class="sum-card c-red" style="animation-delay:.15s">
            <span class="sum-icon">💀</span>
            <div class="sum-val red">{{ $ss['total_losses'] }}</div>
            <div class="sum-lbl">Losses</div>
        </div>
        <div class="sum-card c-gold" style="animation-delay:.2s">
            <span class="sum-icon">🎯</span>
            <div class="sum-val gold">{{ $ss['avg_accuracy'] }}%</div>
            <div class="sum-lbl">Avg Accuracy</div>
        </div>
        <div class="sum-card c-ice" style="animation-delay:.25s">
            <span class="sum-icon">⚡</span>
            <div class="sum-val ice">{{ $ss['avg_score'] }}</div>
            <div class="sum-lbl">Avg Score</div>
        </div>
        <div class="sum-card c-fire" style="animation-delay:.3s">
            <span class="sum-icon">🔥</span>
            <div class="sum-val" style="background:linear-gradient(135deg,#ff6b35,#cc4400);-webkit-background-clip:text">{{ $ss['best_score'] }}</div>
            <div class="sum-lbl">Best Score</div>
        </div>
    </div>

    {{-- ── WIN RATE BAR ── --}}
    @if($ss['total_played'] > 0)
    <div class="winrate-bar-wrap">
        <div class="wr-label">Win Rate</div>
        <div class="wr-track">
            <div class="wr-fill" id="wrFill" style="width:0%"></div>
        </div>
        <div class="wr-pct">{{ $ss['win_rate'] }}%</div>
        <div class="wr-meta">{{ $ss['total_wins'] }}W · {{ $ss['total_losses'] }}L out of {{ $ss['total_played'] }} battles</div>
    </div>
    @endif

    {{-- ── BATTLE LIST ── --}}
    <div class="sec-hd">📋 Battle History <span style="font-size:.8em;font-weight:400;margin-left:4px">{{ $histories->total() }} total</span></div>

    @if($histories->isEmpty())
    <div class="empty-state">
        <span class="empty-icon">⚔️</span>
        <div class="empty-title">No battles yet</div>
        <div class="empty-sub">Start your first institution battle and your history will appear here.</div>
        <a href="{{ route('institution.battle.setup') }}" class="ph-action" style="display:inline-flex">
            ⚔️ Start a Battle
        </a>
    </div>

    @else
    <div class="history-list">
        @foreach($histories as $i => $h)
        @php
            $battle     = $h->battle;
            $isWin      = $h->rank === 1;
            $rankClass  = match($h->rank) { 1 => 'r1', 2 => 'r2', 3 => 'r3', default => 'rX' };
            $rankEmoji  = match($h->rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => '#' . $h->rank };
            $quizTitle  = $battle->quiz->title ?? 'Untitled Quiz';
            $subject    = $battle->quiz->subject ?? null;
            $battleType = $battle->battle_type === '3school' ? '3-School' : '2-School';
            $totalQ     = $battle->total_questions ?? 0;
            $qTimer     = $battle->question_timer ?? 0;
            $finishedAt = $battle->finished_at?->diffForHumans() ?? '—';
            $duration   = ($battle->started_at && $battle->finished_at)
                ? $battle->started_at->diffInMinutes($battle->finished_at) . ' min'
                : '—';

            // Opponent institution names
            $participatingIds = $battle->participating_institutions ?? [];
            $opponents = \App\Models\Institution::whereIn('id', array_filter($participatingIds))
                ->where('id', '!=', $h->institution_id)
                ->pluck('name');
        @endphp
        <div class="hcard {{ $isWin ? 'win' : 'loss' }}" style="animation-delay:{{ min($i * .05, .5) }}s">
            <div class="hcard-stripe"></div>
            <div class="hcard-body">

                {{-- Rank badge --}}
                <div class="hcard-rank {{ $rankClass }}">{{ $rankEmoji }}</div>

                {{-- Info --}}
                <div class="hcard-info">
                    <div class="hcard-top">
                        <div class="hcard-quiz">{{ $quizTitle }}</div>
                        <span class="result-pill {{ $isWin ? 'win' : 'loss' }}">
                            {{ $isWin ? '✓ Win' : '✗ Loss' }}
                        </span>
                        @if($h->rank === 2)<span class="result-pill" style="background:rgba(192,192,192,.1);border:1px solid rgba(192,192,192,.25);color:var(--silver)">🥈 Runner-up</span>@endif
                    </div>

                    <div class="hcard-meta">
                        <span>🏛 {{ $battleType }}</span>
                        @if($subject)<span>📚 {{ $subject }}</span>@endif
                        <span>📝 {{ $totalQ }} questions</span>
                        <span>⏱ {{ $qTimer }}s/q</span>
                        <span>🕐 {{ $duration }}</span>
                        <span>🔑 {{ $battle->code }}</span>
                    </div>

                    {{-- Opponents --}}
                    @if($opponents->count())
                    <div class="opp-chips">
                        <span style="font-size:.62rem;color:var(--muted);align-self:center">vs</span>
                        @foreach($opponents as $opp)
                        <span class="opp-chip">{{ $opp }}</span>
                        @endforeach
                    </div>
                    @endif

                    {{-- Stats chips --}}
                    <div class="hcard-stats">
                        <div class="hcs">
                            <div class="v green">{{ $h->total_score }}</div>
                            <div class="l">Total Score</div>
                        </div>
                        <div class="hcs">
                            <div class="v ice">{{ $h->average_accuracy }}%</div>
                            <div class="l">Avg Accuracy</div>
                        </div>
                        <div class="hcs">
                            <div class="v">{{ $h->total_participants }}</div>
                            <div class="l">Students</div>
                        </div>
                        <div class="hcs">
                            <div class="v gold">{{ $h->total_correct }}</div>
                            <div class="l">Correct</div>
                        </div>
                        <div class="hcs">
                            <div class="v fire">{{ $h->total_wrong }}</div>
                            <div class="l">Wrong</div>
                        </div>
                        @if($h->average_time > 0)
                        <div class="hcs">
                            <div class="v">{{ $h->average_time }}s</div>
                            <div class="l">Avg Time</div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Action --}}
                <div class="hcard-action">
                    <div class="hcard-date">{{ $finishedAt }}</div>
                    @if($battle->status === 'finished')
                    <a href="{{ route('institution.battle.results', $battle->code) }}" class="view-btn">
                        📊 Results
                    </a>
                    @endif
                </div>

            </div>
        </div>
        @endforeach
    </div>

    {{-- ── PAGINATION ── --}}
    @if($histories->hasPages())
    <div class="pag-wrap">
        {{ $histories->links() }}
    </div>
    @endif
    @endif

</div>

<script>
// Animate win rate bar on load
document.addEventListener('DOMContentLoaded', () => {
    const fill = document.getElementById('wrFill');
    if (fill) {
        setTimeout(() => {
            fill.style.width = '{{ $summaryStats["win_rate"] }}%';
        }, 300);
    }

    // Animate summary numbers counting up
    document.querySelectorAll('.sum-val[data-count]').forEach(el => {
        const target = parseInt(el.dataset.count);
        let start = 0;
        const dur = 800, s = performance.now();
        const f = t => {
            const p = Math.min((t - s) / dur, 1);
            el.textContent = Math.round(start + (target - start) * p);
            if (p < 1) requestAnimationFrame(f);
        };
        requestAnimationFrame(f);
    });
});
</script>
@endsection