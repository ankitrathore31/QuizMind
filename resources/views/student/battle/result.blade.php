{{-- resources/views/student/battle/results.blade.php --}}
@extends('student.layout.master')
@section('title', 'Battle Results · ' . $room->code)

@section('content')
<style>
.results-wrap { max-width:720px;margin:0 auto;padding:32px 16px; }
.winner-hero  { text-align:center;padding:40px 24px;margin-bottom:24px;
    background:linear-gradient(135deg,rgba(124,92,252,.08),rgba(0,212,255,.05));
    border:1px solid rgba(124,92,252,.2);border-radius:var(--radius);position:relative;overflow:hidden; }
.winner-hero::before { content:'';position:absolute;inset:0;
    background:radial-gradient(ellipse at 50% 0%,rgba(124,92,252,.12) 0%,transparent 70%); }
.winner-emoji { font-size:4rem;margin-bottom:14px;display:block;animation:popIn .6s cubic-bezier(.34,1.56,.64,1); }
@keyframes popIn{ from{transform:scale(0) rotate(-10deg)}to{transform:scale(1) rotate(0)} }
.winner-name  { font-family:var(--fh);font-size:1.8rem;font-weight:900;margin-bottom:6px; }
.winner-sub   { color:var(--muted);font-size:.88rem; }

.team-scores  { display:grid;grid-template-columns:1fr auto 1fr;gap:16px;margin-bottom:24px;align-items:center; }
.team-score-box { text-align:center;padding:20px;border-radius:var(--radius);border:1px solid var(--border);background:var(--card); }
.team-score-box.winner { border-color:rgba(255,165,0,.4);background:rgba(255,165,0,.04); }
.ts-name  { font-weight:800;font-size:.88rem;margin-bottom:8px; }
.ts-score { font-family:var(--fh);font-size:2.2rem;font-weight:900; }

.score-table  { background:var(--card);border:1px solid var(--border);border-radius:var(--radius);
    overflow:hidden;margin-bottom:20px; }
.st-head      { display:grid;grid-template-columns:36px 1fr repeat(4,70px);
    padding:10px 16px;border-bottom:1px solid var(--border);font-size:.72rem;
    font-weight:700;color:var(--muted);text-align:center; }
.st-head :first-child,.st-row>:first-child { text-align:left; }
.st-head :nth-child(2){ text-align:left; }
.st-row       { display:grid;grid-template-columns:36px 1fr repeat(4,70px);
    padding:12px 16px;border-bottom:1px solid var(--border2);align-items:center;
    font-size:.84rem;transition:background .2s; }
.st-row:last-child{ border:none; }
.st-row.me    { background:rgba(124,92,252,.06); }
.st-row:hover { background:rgba(255,255,255,.02); }
.st-rank      { font-weight:800;font-size:.88rem; }
.rank-1 { color:gold; }
.rank-2 { color:#c0c0c0; }
.rank-3 { color:#cd7f32; }
.st-player    { display:flex;align-items:center;gap:8px; }
.st-avatar    { width:28px;height:28px;border-radius:50%;background:var(--gradient);
    display:flex;align-items:center;justify-content:center;font-size:.76rem;
    font-weight:700;color:#fff;flex-shrink:0; }
.st-name      { font-weight:600; }
.st-team-tag  { font-size:.65rem;padding:1px 6px;border-radius:4px;font-weight:700;margin-left:4px; }
.team-a-tag   { background:rgba(124,92,252,.2);color:var(--accent); }
.team-b-tag   { background:rgba(0,227,150,.15);color:#00e396; }
.st-val       { text-align:center;font-weight:600; }
.st-val.xp    { color:gold;font-weight:800; }
.st-val.disq  { color:var(--red);font-size:.72rem; }

.my-stats-grid{ display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px; }
@media(max-width:600px){ .my-stats-grid{ grid-template-columns:1fr 1fr; } }

.per-q-review { background:var(--card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden; }
.pq-head      { padding:16px 20px;border-bottom:1px solid var(--border); }
.pq-row       { display:flex;align-items:center;gap:10px;padding:12px 20px;
    border-bottom:1px solid var(--border2);font-size:.83rem; }
.pq-row:last-child{ border:none; }
.pq-icon      { font-size:1rem;flex-shrink:0; }
.pq-q         { flex:1;color:var(--muted);line-height:1.4; }
.pq-result    { font-weight:700;font-size:.78rem;white-space:nowrap; }
</style>

<div class="results-wrap anim-fade">

    {{-- Winner announcement --}}
    @php
        $finalScores = $room->final_scores ?? [];
        $myData      = collect($finalScores)->firstWhere('user_id', Auth::id());
        $myRank      = collect($finalScores)->search(fn($s) => $s['user_id'] === Auth::id()) + 1;
        $teamMode    = $room->mode === 'team';
    @endphp

    <div class="winner-hero">
        @if($teamMode)
            <span class="winner-emoji">🏆</span>
            <div class="winner-name">{{ $room->winner_team ?? 'Draw!' }} Wins!</div>
            <div class="winner-sub">Team Battle · {{ $room->code }}</div>
        @else
            @if($room->winner)
                <span class="winner-emoji">{{ $room->winner->id === Auth::id() ? '🥇' : '🏆' }}</span>
                <div class="winner-name">{{ $room->winner->name }} Wins!</div>
                <div class="winner-sub">{{ ucfirst($room->mode) }} Battle · {{ $room->code }}</div>
            @else
                <span class="winner-emoji">🏅</span>
                <div class="winner-name">Battle Complete!</div>
                <div class="winner-sub">{{ $room->code }}</div>
            @endif
        @endif

        @if($myData)
            <div class="flex gap-10 mt-20" style="justify-content:center;flex-wrap:wrap">
                <div class="badge bp">Rank #{{ $myRank }}</div>
                <div class="badge bc">{{ $myData['score'] ?? 0 }} pts</div>
                <div class="badge" style="background:rgba(255,165,0,.15);color:gold;border:1px solid rgba(255,165,0,.3)">
                    +{{ $myData['xp'] ?? 0 }} XP
                </div>
                @if($myData['disq'] ?? false)
                    <div class="badge" style="background:rgba(255,77,106,.15);color:var(--red);border:1px solid rgba(255,77,106,.3)">
                        ⛔ Disqualified
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Team score comparison --}}
    @if($teamMode)
        @php
            $scoreA = collect($finalScores)->where('team','a')->sum('score');
            $scoreB = collect($finalScores)->where('team','b')->sum('score');
            $winnerTeamKey = $scoreA > $scoreB ? 'a' : 'b';
        @endphp
        <div class="team-scores mb-24">
            <div class="team-score-box {{ $winnerTeamKey === 'a' ? 'winner' : '' }}">
                <div class="ts-name">{{ $room->team_a_name }}</div>
                <div class="ts-score grad">{{ $scoreA }}</div>
            </div>
            <div style="text-align:center;font-size:1.5rem;font-weight:900">⚔️</div>
            <div class="team-score-box {{ $winnerTeamKey === 'b' ? 'winner' : '' }}">
                <div class="ts-name">{{ $room->team_b_name }}</div>
                <div class="ts-score green">{{ $scoreB }}</div>
            </div>
        </div>
    @endif

    {{-- My stats --}}
    @if($myData)
        <div class="section-title mb-12">📊 Your Stats</div>
        <div class="my-stats-grid mb-24">
            <div class="stat-card sc-purple text-center">
                <div class="stat-val grad">{{ $myData['score'] ?? 0 }}</div>
                <div class="stat-label">Score</div>
            </div>
            <div class="stat-card sc-cyan text-center">
                <div class="stat-val cyan">{{ $myData['accuracy'] ?? 0 }}%</div>
                <div class="stat-label">Accuracy</div>
            </div>
            <div class="stat-card sc-gold text-center">
                <div class="stat-val gold">🔥 {{ $myData['streak'] ?? 0 }}</div>
                <div class="stat-label">Max Streak</div>
            </div>
            <div class="stat-card sc-green text-center">
                <div class="stat-val green">+{{ $myData['xp'] ?? 0 }}</div>
                <div class="stat-label">XP Earned</div>
            </div>
        </div>
    @endif

    {{-- Leaderboard --}}
    <div class="section-title mb-12">🏅 Final Leaderboard</div>
    <div class="score-table mb-20">
        <div class="st-head">
            <span>#</span>
            <span>Player</span>
            <span>Score</span>
            <span>Correct</span>
            <span>Streak</span>
            <span>XP</span>
        </div>
        @foreach($finalScores as $i => $s)
            <div class="st-row {{ $s['user_id'] === Auth::id() ? 'me' : '' }}">
                <span class="st-rank rank-{{ $i+1 }}">
                    {{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : '#'.($i+1))) }}
                </span>
                <div class="st-player">
                    <div class="st-avatar">{{ substr($s['name'],0,1) }}</div>
                    <div>
                        <div class="st-name">
                            {{ $s['name'] }}
                            @if($s['user_id'] === Auth::id()) <span style="font-size:.7rem;color:var(--accent)">(You)</span> @endif
                        </div>
                        @if($teamMode && isset($s['team']))
                            <span class="st-team-tag {{ $s['team']==='a' ? 'team-a-tag' : 'team-b-tag' }}">
                                {{ $s['team'] === 'a' ? $room->team_a_name : $room->team_b_name }}
                            </span>
                        @endif
                    </div>
                </div>
                <span class="st-val">{{ $s['score'] ?? 0 }}</span>
                <span class="st-val">{{ $s['correct'] ?? 0 }}/{{ count($room->quiz->questions ?? []) }}</span>
                <span class="st-val">{{ $s['streak'] ?? 0 }}🔥</span>
                <span class="st-val xp {{ ($s['disq'] ?? false) ? 'disq' : '' }}">
                    {{ ($s['disq'] ?? false) ? '⛔ DQ' : '+'.($s['xp'] ?? 0) }}
                </span>
            </div>
        @endforeach
    </div>

    {{-- Per-question review (my answers) --}}
    @if($myParticipant && $myParticipant->answer_log)
        <div class="section-title mb-12">🔍 Question Review</div>
        <div class="per-q-review mb-24">
            <div class="pq-head">
                <div class="section-title" style="margin:0;font-size:.84rem">Your answer-by-answer breakdown</div>
            </div>
            @foreach($room->quiz->questions ?? [] as $qi => $q)
                @php $log = collect($myParticipant->answer_log)->firstWhere('q_idx', $qi); @endphp
                <div class="pq-row">
                    <span class="pq-icon">{{ ($log && $log['correct']) ? '✅' : '❌' }}</span>
                    <span class="pq-q">Q{{ $qi+1 }}: {{ Str::limit($q['question'], 70) }}</span>
                    <span class="pq-result" style="color:{{ ($log && $log['correct']) ? 'var(--green)' : 'var(--red)' }}">
                        {{ ($log && $log['correct']) ? 'Correct' : (($log && $log['selected'] === -1) ? 'Timeout' : 'Wrong') }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Action buttons --}}
    <div class="flex gap-10" style="flex-wrap:wrap">
        @if(Auth::id() === $room->host_id)
            <button class="btn btn-grad" id="rematchBtn" onclick="doRematch()">🔄 Rematch</button>
        @endif
        <a href="{{ route('student.battle.history') }}" class="btn btn-ghost">📜 My Battle History</a>
        <a href="{{ route('student.quiz.index') }}"     class="btn btn-ghost">← Back to Quiz</a>
    </div>

</div>

<script>
async function doRematch() {
    const btn = document.getElementById('rematchBtn');
    btn.disabled = true;
    btn.textContent = '⏳ Creating rematch…';

    const res = await fetch('{{ route('student.battle.rematch') }}', {
        method: 'POST',
        headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','X-Requested-With':'XMLHttpRequest' },
        body: JSON.stringify({ code: '{{ $room->code }}' }),
    });
    const d = await res.json();
    if (d.success) window.location.href = d.redirectUrl;
    else {
        alert(d.message || 'Failed');
        btn.disabled = false;
        btn.textContent = '🔄 Rematch';
    }
}

// Confetti if winner
@if($room->winner_user_id === Auth::id() || (isset($winnerTeamKey) && $room->winner_team && Auth::user()))
    if (typeof window.confetti === 'function') {
        window.confetti({ particleCount:150, spread:80, origin:{y:.5} });
    }
@endif
</script>
@endsection