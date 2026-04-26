{{-- resources/views/student/battle/arena.blade.php --}}
{{--
    STUDENT ONLY — extends student.layout.master
    Full quiz interface: timer, question, options, feedback, sidebar leaderboard
    No host controls. Anti-cheat enforced. Routed here from InstitutionBattleController::arena()
--}}
@extends('student.layout.master')
@section('title', 'Battle ⚔️ ' . $battle->code)

@section('content')
<style>
    body,html{overflow:hidden!important}

    :root{
        --gold:#f5c842;--gold-dim:rgba(245,200,66,.12);
        --green:#00e396;--red:#ff4d6a;--ice:#00d4ff;--fire:#ff6b35;
    }

    #arena{position:fixed;inset:0;display:flex;flex-direction:column;background:var(--bg);z-index:500}

    /* ── TOP BAR ── */
    #topBar{
        display:flex;align-items:center;gap:10px;
        padding:8px 16px;background:rgba(0,0,0,.5);
        border-bottom:1px solid rgba(255,255,255,.07);
        backdrop-filter:blur(14px);flex-shrink:0;min-height:48px;
    }
    .tb-code{
        font-family:var(--fh);font-weight:900;font-size:.8rem;
        color:var(--gold);letter-spacing:2px;
        background:var(--gold-dim);padding:3px 10px;border-radius:12px;flex-shrink:0;
    }
    .tb-school{
        padding:3px 10px;border-radius:12px;font-size:.7rem;font-weight:800;flex-shrink:0;
        background:rgba(124,92,252,.15);color:var(--accent);border:1px solid rgba(124,92,252,.3);
    }
    .tb-prog{flex:1;height:4px;background:rgba(255,255,255,.07);border-radius:2px;overflow:hidden;}
    .tb-fill{height:100%;background:var(--gradient);border-radius:2px;transition:width .5s ease;}
    .tb-ql{font-size:.76rem;font-weight:800;color:var(--muted);flex-shrink:0;}
    .tb-pts{
        background:rgba(124,92,252,.15);border:1px solid rgba(124,92,252,.3);
        color:var(--accent);padding:3px 10px;border-radius:12px;
        font-weight:900;font-size:.78rem;flex-shrink:0;
    }

    /* ══ STUDENT ARENA BODY ══ */
    #studentArena{display:flex;flex:1;overflow:hidden;}
    #studentMain{flex:1;overflow-y:auto;padding:18px 20px;display:flex;flex-direction:column;gap:13px;}
    #studentMain::-webkit-scrollbar{width:4px;}
    #studentMain::-webkit-scrollbar-thumb{background:rgba(255,255,255,.08);border-radius:2px;}

    /* ── Sidebar ── */
    #sidebar{
        width:260px;border-left:1px solid rgba(255,255,255,.07);
        background:rgba(0,0,0,.18);display:flex;flex-direction:column;overflow:hidden;
    }
    @media(max-width:780px){#sidebar{display:none;}}

    /* ── Timer row ── */
    .timer-row{display:flex;align-items:center;gap:13px;}
    .tc-wrap{position:relative;width:54px;height:54px;flex-shrink:0;}
    .tc-wrap svg{transform:rotate(-90deg);}
    .tc-bg{fill:none;stroke:rgba(255,255,255,.08);stroke-width:5;}
    .tc-fill{fill:none;stroke-width:5;stroke-linecap:round;stroke-dasharray:157;stroke-dashoffset:0;transition:stroke .4s;}
    .tc-num{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:var(--fh);font-size:1.1rem;font-weight:900;}
    .tbar-wrap{flex:1;height:5px;background:rgba(255,255,255,.07);border-radius:3px;overflow:hidden;}
    .tbar{height:100%;background:var(--gradient);border-radius:3px;transition:width 1s linear,background .4s;}

    /* ── Question card ── */
    .q-card{
        background:var(--card);border:1.5px solid rgba(255,255,255,.08);
        border-radius:var(--radius);padding:20px;
        animation:qIn .3s cubic-bezier(.34,1.26,.64,1);
    }
    @keyframes qIn{from{opacity:0;transform:translateY(14px) scale(.98)}to{opacity:1;transform:none}}
    .q-meta{display:flex;justify-content:space-between;margin-bottom:10px;flex-wrap:wrap;gap:6px;}
    .q-topic{
        background:rgba(0,212,255,.08);color:#00d4ff;
        border:1px solid rgba(0,212,255,.2);
        padding:3px 10px;border-radius:20px;font-size:.68rem;font-weight:700;
    }
    .q-num{font-size:.7rem;padding:3px 8px;background:rgba(255,255,255,.06);border-radius:8px;font-weight:800;}
    .q-text{font-size:1rem;font-weight:600;line-height:1.7;margin:0;}

    /* ── Options grid ── */
    .opts-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
    @media(max-width:480px){.opts-grid{grid-template-columns:1fr;}}

    .opt{
        display:flex;align-items:center;gap:10px;padding:13px 14px;
        border-radius:var(--radius-sm);border:1.5px solid rgba(255,255,255,.08);
        background:transparent;cursor:pointer;text-align:left;
        transition:border-color .15s,background .15s,transform .1s;
        font-size:.87rem;color:var(--text);width:100%;
        animation:optIn .25s ease both;
    }
    .opt:nth-child(1){animation-delay:.04s}.opt:nth-child(2){animation-delay:.08s}
    .opt:nth-child(3){animation-delay:.12s}.opt:nth-child(4){animation-delay:.16s}
    @keyframes optIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}

    .opt:hover:not(:disabled):not(.correct):not(.wrong){
        border-color:var(--accent);background:rgba(124,92,252,.08);transform:translateY(-1px);
    }
    .opt-letter{
        width:26px;height:26px;border-radius:50%;
        border:2px solid rgba(255,255,255,.15);
        display:flex;align-items:center;justify-content:center;
        font-weight:800;font-size:.74rem;flex-shrink:0;transition:.15s;
    }
    .opt:hover:not(:disabled):not(.correct):not(.wrong) .opt-letter{
        border-color:var(--accent);background:rgba(124,92,252,.2);
    }
    .opt.selected{border-color:var(--accent);background:rgba(124,92,252,.1);}
    .opt.selected .opt-letter{border-color:var(--accent);background:var(--accent);color:#fff;}

    .opt.correct{border-color:#00e396!important;background:rgba(0,227,150,.08)!important;animation:correctPop .4s ease;}
    .opt.correct .opt-letter{border-color:#00e396;background:#00e396;color:#000;}
    @keyframes correctPop{0%{transform:scale(1)}50%{transform:scale(1.04)}100%{transform:scale(1)}}

    .opt.wrong{border-color:rgba(255,77,106,.5)!important;background:rgba(255,77,106,.07)!important;animation:wrongShake .3s ease;}
    .opt.wrong .opt-letter{border-color:var(--red);background:var(--red);color:#fff;}
    @keyframes wrongShake{0%,100%{transform:translateX(0)}25%{transform:translateX(-4px)}75%{transform:translateX(4px)}}

    /* ── Feedback ── */
    #feedback{padding:13px 16px;border-radius:var(--radius-sm);border:1px solid;display:none;}
    #feedback.show{display:block;animation:optIn .25s ease;}
    #feedback.fb-ok {border-color:rgba(0,227,150,.3);background:rgba(0,227,150,.05);}
    #feedback.fb-bad{border-color:rgba(255,77,106,.25);background:rgba(255,77,106,.04);}
    #feedback.fb-out{border-color:rgba(255,165,0,.25);background:rgba(255,165,0,.04);}
    .fb-head{font-weight:800;font-size:.88rem;margin-bottom:3px;}
    .fb-expl{font-size:.78rem;color:var(--muted);line-height:1.6;}

    /* ── Next bar ── */
    #nextBar{display:none;align-items:center;justify-content:center;gap:8px;font-size:.75rem;color:var(--muted);}
    #nextBar.show{display:flex;}
    .nb-track{width:60px;height:3px;background:rgba(255,255,255,.08);border-radius:2px;overflow:hidden;}
    .nb-fill{height:100%;background:var(--accent);border-radius:2px;}

    /* ── Sidebar ── */
    .sb-title{
        padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.07);
        font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;
        color:var(--muted);flex-shrink:0;
    }
    .sb-inst-sec{padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.07);flex-shrink:0;}
    .sbi-row{display:flex;align-items:center;gap:8px;margin-bottom:7px;}
    .sbi-row:last-child{margin:0;}
    .sbi-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
    .sbi-name{flex:1;font-size:.74rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .sbi-score{font-family:var(--fh);font-weight:900;font-size:.82rem;color:var(--accent);}

    #sbList{flex:1;overflow-y:auto;}
    #sbList::-webkit-scrollbar{width:3px;}
    #sbList::-webkit-scrollbar-thumb{background:rgba(255,255,255,.08);border-radius:2px;}
    .sbrow{
        display:flex;align-items:center;gap:7px;padding:7px 12px;
        border-bottom:1px solid rgba(255,255,255,.05);transition:background .2s;
    }
    .sbrow:last-child{border:none;}
    .sbrow.me{background:rgba(124,92,252,.07);}
    .sbrow-rk{font-size:.62rem;font-weight:900;color:var(--muted);width:16px;flex-shrink:0;}
    .sbrow-av{
        width:22px;height:22px;border-radius:50%;background:var(--gradient);
        display:flex;align-items:center;justify-content:center;
        font-size:.6rem;font-weight:800;color:#fff;flex-shrink:0;
    }
    .sbrow-nm{flex:1;font-size:.74rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .sbrow-inst{font-size:.58rem;color:var(--muted);width:34px;text-align:right;flex-shrink:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .sbrow-pts{font-family:var(--fh);font-weight:800;font-size:.8rem;flex-shrink:0;}

    /* ── Panels ── */
    .wait-panel,.fin-panel{
        display:none;flex-direction:column;align-items:center;justify-content:center;
        flex:1;text-align:center;gap:14px;padding:40px 20px;
    }
    .wait-panel.show,.fin-panel.show{display:flex;}
    .big-icon{font-size:3.8rem;animation:floatLoop 3s ease-in-out infinite;}
    @keyframes floatLoop{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}

    /* ── My score pulse on award ── */
    .pts-pop{animation:ptsPop .35s cubic-bezier(.34,1.56,.64,1);}
    @keyframes ptsPop{0%{transform:scale(1)}50%{transform:scale(1.35)}100%{transform:scale(1)}}

    /* ── Streak badge ── */
    #streakBadge{
        display:none;align-items:center;gap:6px;
        padding:5px 14px;border-radius:20px;
        background:rgba(255,107,53,.12);border:1px solid rgba(255,107,53,.3);
        color:#ff6b35;font-weight:800;font-size:.78rem;
        animation:optIn .2s ease;
    }
    #streakBadge.show{display:flex;}

    /* ── Countdown overlay ── */
    #cdOverlay{
        display:none;position:fixed;inset:0;z-index:9990;
        background:rgba(0,0,0,.88);flex-direction:column;
        align-items:center;justify-content:center;text-align:center;
    }
    #cdOverlay.show{display:flex;}
    .cd-num{
        font-family:var(--fh);font-size:7rem;font-weight:900;line-height:1;
        background:var(--gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;
        animation:cdPop .4s cubic-bezier(.34,1.56,.64,1);
    }
    @keyframes cdPop{from{transform:scale(.5);opacity:0}to{transform:scale(1);opacity:1}}

    /* ── Violation overlay ── */
    #vioOverlay{display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.9);align-items:center;justify-content:center;}
    #vioOverlay.show{display:flex;animation:fadeIn .2s;}
    @keyframes fadeIn{from{opacity:0}to{opacity:1}}
    .vio-box{background:var(--card);border:2px solid var(--red);border-radius:var(--radius);padding:30px 36px;max-width:340px;text-align:center;}

    /* ── Float FX ── */
    .float-fx{
        position:fixed;pointer-events:none;z-index:9000;
        font-size:1.4rem;font-weight:900;font-family:var(--fh);
        animation:floatUp .9s ease forwards;white-space:nowrap;
    }
    @keyframes floatUp{0%{opacity:1;transform:translateY(0) scale(1)}100%{opacity:0;transform:translateY(-80px) scale(1.2)}}

    #fxCanvas{position:fixed;inset:0;pointer-events:none;z-index:8000;}
    @keyframes spin{to{transform:rotate(360deg)}}
</style>

<canvas id="fxCanvas"></canvas>

<div id="arena">

    {{-- ── TOP BAR ── --}}
    <div id="topBar">
        <span class="tb-code">{{ $battle->code }}</span>
        @php
            $myParticipant = $battle->participants->firstWhere('user_id', Auth::id());
            $myInst = $myParticipant ? optional($myParticipant->institution)->name : null;
        @endphp
        <span class="tb-school">⚔️ {{ $myInst ? strtoupper(substr($myInst, 0, 8)) : 'PLAYER' }}</span>
        <div class="tb-prog"><div class="tb-fill" id="progFill" style="width:0%"></div></div>
        <span class="tb-ql" id="qLabel">Q 0/{{ $battle->total_questions }}</span>
        <span class="tb-pts">⚡ <span id="myPts">0</span></span>
    </div>

    {{-- ── STUDENT ARENA BODY ── --}}
    <div id="studentArena">

        {{-- Main quiz area --}}
        <div id="studentMain">

            {{-- Wait panel --}}
            <div class="wait-panel show" id="waitPanel">
                <div class="big-icon">⏳</div>
                <h2 style="font-family:var(--fh);font-weight:900;font-size:1.4rem">Waiting for battle to start…</h2>
                <p style="color:var(--muted);font-size:.84rem">You'll jump in automatically when the host launches!</p>
                @if($myInst)
                <div style="display:flex;align-items:center;gap:8px;padding:8px 16px;background:rgba(124,92,252,.08);border:1px solid rgba(124,92,252,.2);border-radius:12px;font-size:.8rem;font-weight:700;">
                    🏫 {{ $myInst }}
                </div>
                @endif
            </div>

            {{-- Quiz panel --}}
            <div id="qPanel" style="display:none;flex-direction:column;gap:13px">

                {{-- Streak badge --}}
                <div id="streakBadge">🔥 <span id="streakNum">0</span>× Streak!</div>

                {{-- Timer --}}
                <div class="timer-row">
                    <div class="tc-wrap">
                        <svg width="54" height="54" viewBox="0 0 60 60">
                            <circle class="tc-bg"   cx="30" cy="30" r="25"/>
                            <circle class="tc-fill" id="tcFill" cx="30" cy="30" r="25" stroke="var(--accent)"/>
                        </svg>
                        <div class="tc-num" id="tcNum">—</div>
                    </div>
                    <div class="tbar-wrap"><div class="tbar" id="timerBar" style="width:100%"></div></div>
                </div>

                {{-- Question card --}}
                <div class="q-card">
                    <div class="q-meta">
                        <span class="q-topic" id="qTopic">General</span>
                        <span class="q-num"   id="qNum">Q1</span>
                    </div>
                    <p class="q-text" id="qText"></p>
                </div>

                {{-- Options --}}
                <div class="opts-grid" id="optsGrid"></div>

                {{-- Feedback --}}
                <div id="feedback">
                    <div class="fb-head" id="fbHead"></div>
                    <div class="fb-expl" id="fbExpl"></div>
                </div>

                {{-- Next bar --}}
                <div id="nextBar">
                    Next in
                    <div class="nb-track"><div class="nb-fill" id="nbFill"></div></div>
                    <span id="nbSecs">3</span>s
                </div>
            </div>

            {{-- Finished panel --}}
            <div class="fin-panel" id="finishedPanel">
                <div class="big-icon">🏆</div>
                <h2 style="font-family:var(--fh);font-weight:900;font-size:1.4rem">Battle Complete!</h2>
                <p style="color:var(--muted);font-size:.84rem">Final score: <strong style="color:var(--accent)" id="finalScore">0</strong> pts</p>
                <a href="{{ route('institution.battle.results', $battle->code) }}" class="btn btn-grad" style="margin-top:6px">View Results →</a>
            </div>

        </div>{{-- /studentMain --}}

        {{-- Sidebar leaderboard --}}
        <div id="sidebar">
            <div class="sb-title">🏛 Institutions</div>
            <div class="sb-inst-sec" id="sbInstSec">
                @php
                    $instListSb = \App\Models\Institution::whereIn('id', $battle->participating_institutions ?? [])->get();
                    $sbColors   = ['#7c5cfc','#00d4ff','#f5c842'];
                @endphp
                @foreach($instListSb as $si => $inst)
                <div class="sbi-row">
                    <div class="sbi-dot" style="background:{{ $sbColors[$si] ?? '#fff' }}"></div>
                    <div class="sbi-name">{{ $inst->name }}</div>
                    <div class="sbi-score" id="sbInst-{{ $inst->id }}">0</div>
                </div>
                @endforeach
            </div>

            <div class="sb-title">👥 All Students</div>
            <div id="sbList">
                @foreach($battle->participants->sortByDesc('score') as $p)
                <div class="sbrow {{ $p->user_id === Auth::id() ? 'me' : '' }}" id="sbr-{{ $p->user_id }}">
                    <span class="sbrow-rk">#{{ $loop->iteration }}</span>
                    <div class="sbrow-av">{{ strtoupper(substr($p->user->name, 0, 1)) }}</div>
                    <span class="sbrow-nm">{{ $p->user->name }}</span>
                    <span class="sbrow-inst">{{ strtoupper(substr(optional($p->institution)->name ?? '', 0, 4)) }}</span>
                    <span class="sbrow-pts" id="sbpts-{{ $p->user_id }}">0</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>{{-- /studentArena --}}

</div>{{-- /arena --}}

{{-- Countdown overlay --}}
<div id="cdOverlay">
    <p style="color:var(--muted);font-size:.9rem;margin-bottom:4px">Battle starting in</p>
    <div class="cd-num" id="cdNum">3</div>
    <p style="color:var(--muted);font-size:.82rem;margin-top:8px">⚔️ Get ready!</p>
</div>

{{-- Violation overlay --}}
<div id="vioOverlay">
    <div class="vio-box">
        <div style="font-size:2.5rem;margin-bottom:10px">⚠️</div>
        <h3 style="font-family:var(--fh);color:var(--red);margin-bottom:8px">Anti-Cheat Warning</h3>
        <p style="color:var(--muted);font-size:.82rem;margin-bottom:6px" id="vioMsg"></p>
        <p style="font-size:.72rem;color:var(--muted)" id="vioDetail"></p>
        <button class="btn btn-ghost" style="width:100%;margin-top:14px" onclick="dismissVio()">Return to Battle</button>
    </div>
</div>

<script src="https://js.pusher.com/8.0/pusher.min.js"></script>
<script>
// ── Config ────────────────────────────────────────────────────────────────────
const CFG = {
    code:       '{{ $battle->code }}',
    myId:       {{ Auth::id() }},
    isCreator:  false,
    qTimer:     {{ $battle->question_timer }},
    totalQ:     {{ $battle->total_questions }},
    csrf:       '{{ csrf_token() }}',
    status:     '{{ $battle->status }}',
    antiCheat:  {{ $battle->anti_cheat ? 'true' : 'false' }},
    instIds:    @json($battle->participating_institutions ?? []),
};

const LETTERS = ['A','B','C','D'];

const R = {
    answer:    '{{ route('institution.battle.answer') }}',
    violation: '{{ route('institution.battle.violation') }}',
    results:   '{{ route('institution.battle.results', $battle->code) }}',
    poll:      '/institution/battle/arena-state/{{ $battle->code }}',
};

// ── State ────────────────────────────────────────────────────────────────────
const S = {
    qs: [], qIdx: -1, timeLeft: CFG.qTimer,
    tTick: null, nTick: null, pTick: null,
    answered: false, submitting: false,
    myScore: 0, myStreak: 0,
    disq: false, running: false, done: false,
    wsOk: false, startHandled: false,
};

// Pre-load if battle already in_progress (student rejoining mid-battle)
@if($battle->status === 'in_progress')
S.qs           = @json(collect($battle->quiz->questions ?? [])->map(fn($q) => ['question' => $q['question'], 'options' => $q['options'], 'topic' => $q['topic'] ?? ''])->values());
S.running      = true;
S.startHandled = true;
@php
    $myP = $battle->participants->firstWhere('user_id', Auth::id());
@endphp
S.myScore  = {{ $myP ? $myP->score : 0 }};
S.myStreak = {{ $myP ? $myP->streak : 0 }};
document.addEventListener('DOMContentLoaded', () => {
    g('myPts').textContent = S.myScore;
    showQ(0);
});
@endif

// ── Pusher / WebSocket ────────────────────────────────────────────────────────
let pusher, ch;
try {
    pusher = new Pusher('{{ config('broadcasting.connections.reverb.key', config('broadcasting.connections.pusher.key', 'key')) }}', {
        cluster:           '{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}',
        wsHost:            '{{ config('broadcasting.connections.reverb.host', '127.0.0.1') }}',
        wsPort:            {{ config('broadcasting.connections.reverb.port', 8080) }},
        wssPort:           {{ config('broadcasting.connections.reverb.port', 8080) }},
        forceTLS:          {{ config('broadcasting.connections.reverb.scheme', 'http') === 'https' ? 'true' : 'false' }},
        enabledTransports: ['ws', 'wss'],
        disableStats:      true,
    });
    ch = pusher.subscribe(`institution-battle.${CFG.code}`);

    // Battle started by host
    ch.bind('institution-battle.started', d => {
        if (S.startHandled) return;
        S.startHandled = true; S.qs = d.questions; S.running = true; S.wsOk = true;
        stopPoll();
        showCountdown(() => showQ(0));
    });

    // Someone submitted an answer → update sidebar leaderboard
    ch.bind('institution-answer.submitted', d => {
        S.wsOk = true;
        updateSidebar(d.scores);
        // If we haven't answered yet, reveal the correct answer when everyone is done
        if (!S.answered && d.questionIndex === S.qIdx) {
            revealAnswer(d.correctInfo.correct_option, -1, false, d.correctInfo.explanation);
        }
    });

    // Next question pushed by server
    ch.bind('institution-next.question', d => {
        S.wsOk = true;
        clearNextBar();
        if (d.questionIndex < S.qs.length) showQ(d.questionIndex);
        else endBattle();
    });

    // Battle finished
    ch.bind('institution-battle.finished', () => { S.wsOk = true; endBattle(); });

    // Countdown ticks (optional visual)
    ch.bind('institution-battle.countdown-updated', d => {
        if (!S.startHandled) {
            const ov = g('cdOverlay'), num = g('cdNum');
            if (ov && num) { ov.classList.add('show'); num.textContent = d.seconds || ''; }
        }
    });

} catch(e) { console.warn('WS init error', e); }

// ── Polling fallback ──────────────────────────────────────────────────────────
function startPoll() { if (!S.pTick) S.pTick = setInterval(doPoll, 4000); }
function stopPoll()  { clearInterval(S.pTick); S.pTick = null; }
startPoll();

async function doPoll() {
    try {
        const r = await fetch(R.poll, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CFG.csrf } });
        const d = await r.json();
        if (!d.success) return;
        if (d.scores) updateSidebar(d.scores);
        if (d.status === 'finished' && !S.done) endBattle();
        if (!S.running && d.status === 'in_progress') location.reload();
    } catch(e) {}
}

// ── Show question ─────────────────────────────────────────────────────────────
function showQ(idx) {
    if (S.done) return;
    if (idx >= S.qs.length) { endBattle(); return; }

    S.qIdx      = idx;
    S.answered  = false;
    S.submitting = false;
    S.timeLeft  = CFG.qTimer;

    clearTimer(); clearNextBar(); hideFB();

    g('waitPanel').classList.remove('show');
    g('finishedPanel').classList.remove('show');
    g('qPanel').style.display = 'flex';

    const q = S.qs[idx];
    g('progFill').style.width  = ((idx + 1) / CFG.totalQ * 100) + '%';
    g('qLabel').textContent    = `Q ${idx + 1}/${CFG.totalQ}`;
    g('qNum').textContent      = `Q${idx + 1}`;
    g('qTopic').textContent    = q.topic || 'General';
    g('qText').textContent     = q.question;

    // Render options
    let html = '';
    for (let i = 0; i < 4; i++) {
        html += `<button class="opt" id="opt${i}" onclick="handleOpt(${i})">
            <span class="opt-letter">${LETTERS[i]}</span>
            <span>${escH(q.options[i] || '')}</span>
        </button>`;
    }
    g('optsGrid').innerHTML = html;

    // Streak badge
    const sb = g('streakBadge');
    if (S.myStreak >= 3 && sb) {
        sb.classList.add('show');
        g('streakNum').textContent = S.myStreak;
    } else if (sb) {
        sb.classList.remove('show');
    }

    startTimer();
    snd('tick');
}

// ── Timer ─────────────────────────────────────────────────────────────────────
function startTimer() {
    clearTimer(); S.timeLeft = CFG.qTimer; drawTimer(S.timeLeft);
    S.tTick = setInterval(() => {
        S.timeLeft--;
        drawTimer(S.timeLeft);
        if (S.timeLeft <= 5 && S.timeLeft > 0) snd('urgent');
        if (S.timeLeft <= 0) { clearTimer(); if (!S.answered) doTimeout(); }
    }, 1000);
}
function clearTimer() { clearInterval(S.tTick); S.tTick = null; }
function drawTimer(t) {
    const pct = Math.max(0, t / CFG.qTimer);
    const off = 157 * (1 - pct);
    const clr = pct > .5 ? 'var(--accent)' : pct > .25 ? 'orange' : 'var(--red)';
    const fill = g('tcFill'), num = g('tcNum'), bar = g('timerBar');
    if (fill) { fill.style.strokeDashoffset = off; fill.style.stroke = clr; }
    if (num)  { num.textContent = t; num.style.color = pct > .25 ? 'var(--text)' : 'var(--red)'; }
    if (bar)  { bar.style.width = (pct * 100) + '%'; bar.style.background = pct > .5 ? '' : pct > .25 ? 'orange' : 'var(--red)'; }
}

// ── Option handler ────────────────────────────────────────────────────────────
function handleOpt(idx) {
    if (S.answered || S.submitting || S.disq) return;
    S.answered  = true;
    S.submitting = true;
    clearTimer();
    const btn = g(`opt${idx}`);
    if (btn) btn.classList.add('selected');
    lockOpts();
    doSubmit(idx, (CFG.qTimer - S.timeLeft) * 1000);
}

async function doSubmit(idx, ms) {
    try {
        const res = await fetch(R.answer, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CFG.csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ code: CFG.code, question_index: S.qIdx, selected: idx, time_ms: ms }),
        });
        S.submitting = false;
        if (!res.ok) { schedNext(); return; }

        const d = await res.json();
        if (d.success) {
            S.myScore  += (d.points || 0);
            S.myStreak  = d.streak || 0;

            // Animate my score
            const ptsEl = g('myPts');
            if (ptsEl) {
                animNum(ptsEl, parseInt(ptsEl.textContent) || 0, S.myScore);
                ptsEl.classList.remove('pts-pop');
                ptsEl.offsetHeight;
                ptsEl.classList.add('pts-pop');
            }

            revealAnswer(d.correct, idx, d.isCorrect, d.explanation);

            if (d.isCorrect) {
                snd('correct');
                spawnParts(g(`opt${d.correct}`), '#00e396');
                floatFX(`+${d.points}`, '#00e396');
                if (d.streak >= 3) floatFX(`🔥×${d.streak}`, 'orange', 50);
            } else {
                snd('wrong');
                floatFX('Wrong!', 'var(--red)');
            }
        }
        schedNext();
    } catch(e) {
        S.submitting = false;
        schedNext();
    }
}

function doTimeout() {
    S.answered  = true;
    S.submitting = false;
    S.myStreak  = 0;
    lockOpts(); snd('timeout');
    showFB('fb-out', '⏰ Time\'s up!', 'No answer recorded.');
    schedNext();
    // Submit timeout answer silently
    fetch(R.answer, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CFG.csrf, 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ code: CFG.code, question_index: S.qIdx, selected: -1, time_ms: CFG.qTimer * 1000 }),
    }).catch(() => {});
}

function lockOpts() {
    for (let i = 0; i < 4; i++) {
        const b = g(`opt${i}`); if (b) b.disabled = true;
    }
}

function revealAnswer(correctIdx, selectedIdx, isCorrect, explanation) {
    for (let i = 0; i < 4; i++) {
        const b = g(`opt${i}`); if (!b) continue;
        b.classList.remove('selected'); b.disabled = true;
        if (i === correctIdx) b.classList.add('correct');
        else if (i === selectedIdx && !isCorrect) b.classList.add('wrong');
    }
    if (isCorrect !== null && selectedIdx >= 0) {
        showFB(
            isCorrect ? 'fb-ok' : 'fb-bad',
            isCorrect ? '✅ Correct!' : `❌ Wrong! Correct: ${LETTERS[correctIdx]}`,
            explanation || ''
        );
    }
}

// ── Next question countdown bar ───────────────────────────────────────────────
function schedNext() {
    const bar = g('nextBar'), fill = g('nbFill'), sec = g('nbSecs');
    if (!bar) return;
    bar.classList.add('show');
    let t = 3.0;
    if (fill) fill.style.width = '100%';
    if (sec)  sec.textContent = 3;
    const cap = S.qIdx;
    S.nTick = setInterval(() => {
        t -= .1;
        if (fill) fill.style.width = Math.max(0, (t / 3) * 100) + '%';
        if (sec)  sec.textContent = Math.ceil(t);
        if (t <= 0) {
            clearNextBar();
            const wsConn = pusher && pusher.connection && pusher.connection.state === 'connected';
            if (!S.wsOk || !wsConn) {
                const n = S.qIdx + 1;
                if (n < S.qs.length) showQ(n); else endBattle();
            } else {
                setTimeout(() => {
                    if (S.qIdx === cap && !S.done) {
                        const n = S.qIdx + 1;
                        if (n < S.qs.length) showQ(n); else endBattle();
                    }
                }, 600);
            }
        }
    }, 100);
}
function clearNextBar() {
    clearInterval(S.nTick); S.nTick = null;
    const b = g('nextBar'); if (b) b.classList.remove('show');
}

// ── Feedback helpers ──────────────────────────────────────────────────────────
function showFB(cls, title, expl) {
    const fb = g('feedback'); if (!fb) return;
    fb.className = cls + ' show';
    g('fbHead').textContent = title;
    g('fbExpl').textContent = expl;
}
function hideFB() {
    const fb = g('feedback'); if (fb) fb.className = '';
}

// ── Sidebar leaderboard update ────────────────────────────────────────────────
function updateSidebar(scores) {
    if (!scores) return;

    // Institution totals
    const byInst = {};
    scores.forEach(s => {
        if (!byInst[s.institution_id]) byInst[s.institution_id] = 0;
        byInst[s.institution_id] += s.score;
    });
    Object.entries(byInst).forEach(([id, total]) => {
        const el = g('sbInst-' + id);
        if (el) animNum(el, parseInt(el.textContent) || 0, total);
    });

    // Student rows — update points, re-sort
    const sbList = g('sbList');
    scores.forEach(s => {
        const pts = g('sbpts-' + s.user_id);
        if (pts) animNum(pts, parseInt(pts.textContent) || 0, s.score);
    });

    // Re-sort rows by score descending
    if (sbList) {
        const rows = [...sbList.querySelectorAll('.sbrow')];
        rows.sort((a, b) => {
            const aP = parseInt(g('sbpts-' + a.id.replace('sbr-', ''))?.textContent) || 0;
            const bP = parseInt(g('sbpts-' + b.id.replace('sbr-', ''))?.textContent) || 0;
            return bP - aP;
        });
        rows.forEach((r, i) => {
            const rk = r.querySelector('.sbrow-rk');
            if (rk) rk.textContent = '#' + (i + 1);
            sbList.appendChild(r);
        });
    }
}

// ── End battle ────────────────────────────────────────────────────────────────
function endBattle() {
    if (S.done) return; S.done = true;
    clearTimer(); clearNextBar(); stopPoll();
    g('qPanel').style.display = 'none';
    g('waitPanel').classList.remove('show');
    g('finishedPanel').classList.add('show');
    const fs = g('finalScore'); if (fs) fs.textContent = S.myScore;
    snd('victory');
    setTimeout(() => window.location.href = R.results, 3500);
}

// ── Countdown overlay ─────────────────────────────────────────────────────────
function showCountdown(cb) {
    const ov = g('cdOverlay'), num = g('cdNum');
    ov.classList.add('show'); let n = 3; num.textContent = n; snd('beep');
    const iv = setInterval(() => {
        n--;
        if (n > 0) {
            num.textContent = n;
            num.style.animation = 'none'; num.offsetHeight; num.style.animation = '';
            snd('beep');
        } else { clearInterval(iv); ov.classList.remove('show'); cb(); }
    }, 1000);
}

// ── Anti-cheat ────────────────────────────────────────────────────────────────
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
    try {
        const r = await fetch(R.violation, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CFG.csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ code: CFG.code, type }),
        });
        const d = await r.json();
        if (d.disqualified) { S.disq = true; clearTimer(); }
        g('vioMsg').textContent    = type === 'tab_switch' ? 'Tab switch detected!' : 'Window minimized!';
        g('vioDetail').textContent = d.disqualified ? '⛔ You are disqualified.' : `Warning ${d.violations}/3`;
        g('vioOverlay').classList.add('show');
        snd('vio');
    } catch(e) {}
}
function dismissVio() { g('vioOverlay').classList.remove('show'); }

// ── Sound engine ──────────────────────────────────────────────────────────────
let AC;
function getAC() { if (!AC) AC = new (window.AudioContext || window.webkitAudioContext)(); return AC; }
function snd(name) {
    try {
        const ac = getAC(), t = ac.currentTime;
        const tone = (f, d, tp = 'sine', v = .25) => {
            const o = ac.createOscillator(), g2 = ac.createGain();
            o.connect(g2); g2.connect(ac.destination);
            o.type = tp; o.frequency.value = f;
            g2.gain.setValueAtTime(v, t);
            g2.gain.exponentialRampToValueAtTime(.001, t + d);
            o.start(t); o.stop(t + d + .01);
        };
        switch(name) {
            case 'tick':    tone(880, .08, 'triangle', .06); break;
            case 'urgent':  tone(660, .07, 'square', .1); break;
            case 'beep':    tone(1047, .12, 'sine', .3); break;
            case 'timeout': tone(220, .4, 'triangle', .15); break;
            case 'vio':     tone(150, .5, 'square', .18); break;
            case 'correct':
                [[523,.18],[659,.18],[784,.18],[1047,.3]].forEach(([f, d], i) => {
                    const o = ac.createOscillator(), g2 = ac.createGain();
                    o.connect(g2); g2.connect(ac.destination); o.type = 'sine'; o.frequency.value = f;
                    const st = t + i * .11;
                    g2.gain.setValueAtTime(.25, st); g2.gain.exponentialRampToValueAtTime(.001, st + d);
                    o.start(st); o.stop(st + d + .01);
                }); break;
            case 'wrong': {
                const o = ac.createOscillator(), g2 = ac.createGain();
                o.connect(g2); g2.connect(ac.destination); o.type = 'sawtooth';
                o.frequency.setValueAtTime(280, t); o.frequency.exponentialRampToValueAtTime(160, t + .3);
                g2.gain.setValueAtTime(.18, t); g2.gain.exponentialRampToValueAtTime(.001, t + .3);
                o.start(t); o.stop(t + .32); break;
            }
            case 'victory':
                [[523,.25],[659,.25],[784,.25],[1047,.35],[1319,.5]].forEach(([f, d], i) => {
                    const o = ac.createOscillator(), g2 = ac.createGain();
                    o.connect(g2); g2.connect(ac.destination); o.type = 'sine'; o.frequency.value = f;
                    const st = t + i * .13;
                    g2.gain.setValueAtTime(.3, st); g2.gain.exponentialRampToValueAtTime(.001, st + d);
                    o.start(st); o.stop(st + d + .01);
                }); break;
        }
    } catch(e) {}
}

// ── Particle FX ───────────────────────────────────────────────────────────────
const cvs = g('fxCanvas'), cx = cvs ? cvs.getContext('2d') : null;
let ptArr = [], looping = false;
if (cvs) { cvs.width = innerWidth; cvs.height = innerHeight; }
window.addEventListener('resize', () => { if (cvs) { cvs.width = innerWidth; cvs.height = innerHeight; } });

function spawnParts(btn, color) {
    if (!btn || !cvs) return;
    const r = btn.getBoundingClientRect(), bx = r.left + r.width / 2, by = r.top + r.height / 2;
    for (let i = 0; i < 18; i++) {
        const a = (Math.PI * 2 / 18) * i + (Math.random() - .5) * .5;
        const sp = 2 + Math.random() * 5;
        ptArr.push({ x: bx, y: by, vx: Math.cos(a) * sp, vy: Math.sin(a) * sp - 1, r: 3 + Math.random() * 3, a: 1, color });
    }
    if (!looping) loop();
}
function loop() {
    looping = true;
    cx.clearRect(0, 0, cvs.width, cvs.height);
    ptArr = ptArr.filter(p => p.a > .02);
    ptArr.forEach(p => {
        p.x += p.vx; p.y += p.vy; p.vy += .15; p.a -= .03;
        cx.save(); cx.globalAlpha = p.a; cx.fillStyle = p.color;
        cx.beginPath(); cx.arc(p.x, p.y, p.r, 0, Math.PI * 2); cx.fill(); cx.restore();
    });
    if (ptArr.length) requestAnimationFrame(loop);
    else { looping = false; cx.clearRect(0, 0, cvs.width, cvs.height); }
}

function floatFX(text, color, ox = 0) {
    const d = document.createElement('div');
    d.className = 'float-fx'; d.textContent = text;
    d.style.color = color;
    d.style.left  = (innerWidth / 2 - 50 + ox) + 'px';
    d.style.top   = (innerHeight / 2 - 60) + 'px';
    document.body.appendChild(d);
    setTimeout(() => d.remove(), 950);
}

// ── Utilities ─────────────────────────────────────────────────────────────────
function animNum(el, from, to) {
    if (!el) return;
    const dur = 350, s = performance.now();
    const f = n => {
        const p = Math.min((n - s) / dur, 1);
        el.textContent = Math.round(from + (to - from) * p);
        if (p < 1) requestAnimationFrame(f);
    };
    requestAnimationFrame(f);
}
function g(id)   { return document.getElementById(id); }
function escH(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>
@endsection