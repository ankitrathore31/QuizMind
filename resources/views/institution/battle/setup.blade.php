{{-- resources/views/institution/battle/setup.blade.php --}}
{{--
    FLOW:
    1. Host selects quiz
    2. Host picks battle type (2 or 3 schools)
    3. Sets timer / anti-cheat
    4. Clicks Launch → host's institution is AUTO-JOINED as School 1
    5. Redirected to lobby (setup-page) where codes for OTHER schools are shown
--}}
@extends('institution.layout.master')
@section('page_title', 'Create Institution Battle')

@section('content')
<style>
    :root {
        --gold: #f5c842; --gold-dim: rgba(245,200,66,.12);
        --green: #00e396; --red: #ff4d6a; --ice: #00d4ff;
    }

    .sw { max-width: 660px; margin: 0 auto; padding: 40px 20px; }

    /* Hero */
    .hero-eyebrow {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--gold-dim); border: 1px solid rgba(245,200,66,.3);
        color: var(--gold); padding: 5px 14px; border-radius: 20px;
        font-size: .72rem; font-weight: 800; letter-spacing: 1px;
        text-transform: uppercase; margin-bottom: 14px;
    }
    .page-h1 { font-family: var(--fh); font-size: 2rem; font-weight: 900; margin-bottom: 6px; }
    .page-sub { color: var(--muted); font-size: .86rem; line-height: 1.65; margin-bottom: 32px; }

    /* Host institution badge */
    .host-inst-badge {
        display: flex; align-items: center; gap: 12px;
        padding: 14px 16px; border-radius: var(--radius-sm);
        background: rgba(0,227,150,.06); border: 1.5px solid rgba(0,227,150,.25);
        margin-bottom: 28px;
    }
    .hib-icon { font-size: 1.6rem; flex-shrink: 0; }
    .hib-body { flex: 1; min-width: 0; }
    .hib-label { font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .8px; color: var(--green); margin-bottom: 2px; }
    .hib-name  { font-weight: 800; font-size: .95rem; }
    .hib-chip  {
        background: rgba(0,227,150,.12); border: 1px solid rgba(0,227,150,.3);
        color: var(--green); padding: 3px 10px; border-radius: 12px;
        font-size: .7rem; font-weight: 800; flex-shrink: 0;
    }

    /* Section cards */
    .scard {
        background: var(--card); border: 1.5px solid var(--border);
        border-radius: var(--radius); padding: 22px 24px; margin-bottom: 16px;
    }
    .sc-title {
        font-size: .72rem; font-weight: 800; text-transform: uppercase;
        letter-spacing: 1.5px; color: var(--muted); margin-bottom: 16px;
        display: flex; align-items: center; gap: 8px;
    }
    .sc-title em { font-style: normal; font-size: 1rem; }

    /* Quiz list */
    .quiz-list { display: flex; flex-direction: column; gap: 7px; max-height: 270px; overflow-y: auto; padding-right: 3px; }
    .quiz-list::-webkit-scrollbar { width: 3px; }
    .quiz-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }
    .qrow {
        display: flex; align-items: center; gap: 12px; padding: 12px 14px;
        border: 1.5px solid var(--border); border-radius: var(--radius-xs);
        cursor: pointer; background: transparent; text-align: left;
        color: var(--text); width: 100%; transition: all .16s;
    }
    .qrow:hover { border-color: var(--accent); background: rgba(124,92,252,.06); }
    .qrow.sel  { border-color: var(--accent); background: rgba(124,92,252,.1); }
    .qrow-icon {
        width: 36px; height: 36px; border-radius: 8px; background: var(--gradient);
        display: flex; align-items: center; justify-content: center; font-size: .9rem; flex-shrink: 0;
    }
    .qrow-name { font-weight: 700; font-size: .87rem; margin-bottom: 1px; }
    .qrow-meta { font-size: .7rem; color: var(--muted); }
    .qrow-q { font-family: var(--fh); font-weight: 900; font-size: 1rem; color: var(--accent); margin-left: auto; flex-shrink: 0; }

    /* Battle type */
    .btype-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .btype {
        padding: 20px 14px; border: 2px solid var(--border); border-radius: var(--radius-sm);
        cursor: pointer; text-align: center; transition: all .18s; position: relative; overflow: hidden;
    }
    .btype:hover { border-color: var(--accent); transform: translateY(-2px); }
    .btype.sel { border-color: var(--gold); background: var(--gold-dim); }
    .btype.sel::after {
        content: '✓'; position: absolute; top: 9px; right: 11px;
        font-size: .72rem; font-weight: 900; color: var(--gold);
    }
    .btype-em { font-size: 2.2rem; margin-bottom: 8px; }
    .btype-name { font-weight: 800; font-size: .9rem; margin-bottom: 3px; }
    .btype-desc { font-size: .72rem; color: var(--muted); margin-bottom: 10px; }
    .btype-dots { display: flex; justify-content: center; gap: 5px; }
    .bd { width: 9px; height: 9px; border-radius: 50%; background: var(--accent); }
    .bd.you { background: var(--green); }
    .btype-you { font-size: .62rem; color: var(--green); font-weight: 700; margin-top: 6px; }

    /* Timer chips */
    .tchips { display: flex; gap: 8px; flex-wrap: wrap; }
    .tc {
        padding: 7px 16px; border: 1.5px solid var(--border); border-radius: 20px;
        background: transparent; cursor: pointer; font-size: .82rem; font-weight: 700;
        color: var(--text); transition: all .14s;
    }
    .tc:hover { border-color: var(--accent); color: var(--accent); }
    .tc.sel { background: rgba(124,92,252,.15); border-color: var(--accent); color: var(--accent); }

    /* Toggle */
    .toggle-row { display: flex; align-items: center; justify-content: space-between; }
    .tgl {
        width: 46px; height: 25px; border-radius: 13px; background: var(--border);
        position: relative; cursor: pointer; transition: background .25s; flex-shrink: 0;
    }
    .tgl.on { background: var(--green); }
    .tgl::after {
        content: ''; position: absolute; width: 19px; height: 19px; border-radius: 50%;
        background: #fff; top: 3px; left: 3px; transition: transform .25s;
    }
    .tgl.on::after { transform: translateX(21px); }

    /* Summary */
    .sumrow { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border); }
    .sumrow:last-child { border: none; }
    .sumk { font-size: .78rem; color: var(--muted); }
    .sumv { font-weight: 700; font-size: .86rem; }

    /* CTA */
    .cta {
        width: 100%; padding: 15px; border: none; border-radius: var(--radius-sm);
        background: var(--gradient); color: #fff; font-family: var(--fh);
        font-size: .98rem; font-weight: 900; cursor: pointer; transition: all .2s;
        display: flex; align-items: center; justify-content: center; gap: 10px;
        margin-top: 18px;
    }
    .cta:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(124,92,252,.45); }
    .cta:disabled { opacity: .45; cursor: not-allowed; }
    @keyframes spin { to { transform: rotate(360deg) } }

    /* Toast */
    .toast {
        position: fixed; top: 20px; right: 20px; z-index: 9999;
        padding: 12px 18px; border-radius: 10px; font-weight: 700; font-size: .82rem;
        color: #fff; animation: toastIn .3s ease;
    }
    @keyframes toastIn { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:none} }
    .toast.err { background: var(--red); }
</style>

<div class="sw">
    <div class="hero-eyebrow">⚔️ Institution Battle</div>
    <h1 class="page-h1">Host a School Battle</h1>
    <p class="page-sub">
        Your institution joins automatically as <strong>School 1</strong>.<br>
        After creating, you'll get invite codes to share with the other school(s).
    </p>

    {{-- Host institution auto-joined notice --}}
    <div class="host-inst-badge">
        <div class="hib-icon">🏫</div>
        <div class="hib-body">
            <div class="hib-label">Your Institution — Auto-joined as School 1</div>
            <div class="hib-name">{{ $institution->name ?? 'Your Institution' }}</div>
        </div>
        <div class="hib-chip">✓ HOST</div>
    </div>

    {{-- 1. Quiz --}}
    <div class="scard">
        <div class="sc-title"><em>📚</em> Select Quiz</div>
        <div class="quiz-list">
            @forelse($quizzes ?? [] as $q)
            <button class="qrow" data-id="{{ $q->id }}"
                onclick="pickQuiz({{ $q->id }},'{{ addslashes($q->title ?? 'Untitled') }}',{{ count($q->questions ?? []) }},this)">
                <div class="qrow-icon">📝</div>
                <div style="flex:1;min-width:0">
                    <div class="qrow-name">{{ $q->title ?? 'Untitled' }}</div>
                    <div class="qrow-meta">{{ $q->created_at->diffForHumans() }}</div>
                </div>
                <div class="qrow-q">{{ count($q->questions ?? []) }}Q</div>
            </button>
            @empty
            <div style="text-align:center;padding:28px;color:var(--muted)">
                <div style="font-size:2rem;margin-bottom:8px">📭</div>
                No quizzes yet. <a href="{{ route('institution.aiquiz') }}">Create one →</a>
            </div>
            @endforelse
        </div>
        <div id="quizPicked" style="display:none;margin-top:12px;padding:10px 13px;background:rgba(0,227,150,.06);border:1px solid rgba(0,227,150,.2);border-radius:var(--radius-xs);font-size:.82rem;color:var(--green)">
            ✓ <strong id="pickedName"></strong> <span style="color:var(--muted)" id="pickedQ"></span>
        </div>
    </div>

    {{-- 2. Battle Type --}}
    <div class="scard">
        <div class="sc-title"><em>🏫</em> How Many Schools?</div>
        <div class="btype-grid">
            <div class="btype" onclick="pickType('2school',this)">
                <div class="btype-em">🆚</div>
                <div class="btype-name">2 Schools</div>
                <div class="btype-desc">Head-to-head rivalry</div>
                <div class="btype-dots">
                    <div class="bd you" title="You"></div>
                    <div class="bd"></div>
                </div>
                <div class="btype-you">🟢 You + 1 invite code</div>
            </div>
            <div class="btype" onclick="pickType('3school',this)">
                <div class="btype-em">🔺</div>
                <div class="btype-name">3 Schools</div>
                <div class="btype-desc">Triple showdown</div>
                <div class="btype-dots">
                    <div class="bd you" title="You"></div>
                    <div class="bd"></div>
                    <div class="bd"></div>
                </div>
                <div class="btype-you">🟢 You + 2 invite codes</div>
            </div>
        </div>
    </div>

    {{-- 3. Settings --}}
    <div class="scard">
        <div class="sc-title"><em>⚙️</em> Settings</div>
        <div style="margin-bottom:20px">
            <div style="font-size:.8rem;font-weight:700;margin-bottom:10px;color:var(--muted)">⏱ Question Timer</div>
            <div class="tchips">
                @foreach([10,15,20,30,45,60] as $t)
                <button class="tc {{ $t==20?'sel':'' }}" onclick="pickTimer({{ $t }},this)">{{ $t }}s</button>
                @endforeach
            </div>
        </div>
        <div class="toggle-row">
            <div>
                <div style="font-weight:700;font-size:.88rem">🛡️ Anti-Cheat</div>
                <div style="font-size:.73rem;color:var(--muted);margin-top:2px">Detects tab switching & window blur</div>
            </div>
            <div class="tgl on" id="acToggle" onclick="toggleAC()"></div>
        </div>
    </div>

    {{-- 4. Summary & Launch --}}
    <div class="scard">
        <div class="sc-title"><em>🚀</em> Review & Launch</div>
        <div class="sumrow">
            <span class="sumk">Your Institution</span>
            <span class="sumv" style="color:var(--green)">{{ $institution->name ?? '—' }} ✓</span>
        </div>
        <div class="sumrow"><span class="sumk">Quiz</span><span class="sumv" id="sumQuiz">— not selected —</span></div>
        <div class="sumrow"><span class="sumk">Questions</span><span class="sumv" id="sumQ">—</span></div>
        <div class="sumrow"><span class="sumk">Format</span><span class="sumv" id="sumType">— not selected —</span></div>
        <div class="sumrow"><span class="sumk">Timer</span><span class="sumv" id="sumTimer">20s per question</span></div>
        <div class="sumrow"><span class="sumk">Anti-Cheat</span><span class="sumv" id="sumAC">Enabled</span></div>
        <button class="cta" id="launchBtn" onclick="launch()">
            ⚔️ Create Battle &amp; Get Invite Codes
        </button>
    </div>
</div>

<script>
const ST = {
    quizId: null, quizName: null, quizCount: 0,
    type: null, timer: 20, ac: true,
    hostInstId: {{ Auth::user()->institution_id ?? 'null' }},
};

function pickQuiz(id, name, count, el) {
    ST.quizId = id; ST.quizName = name; ST.quizCount = count;
    document.querySelectorAll('.qrow').forEach(r => r.classList.remove('sel'));
    el.classList.add('sel');
    document.getElementById('quizPicked').style.display = 'block';
    document.getElementById('pickedName').textContent = name;
    document.getElementById('pickedQ').textContent = `(${count} questions)`;
    document.getElementById('sumQuiz').textContent = name;
    document.getElementById('sumQ').textContent = count + ' questions';
}

function pickType(type, el) {
    ST.type = type;
    document.querySelectorAll('.btype').forEach(c => c.classList.remove('sel'));
    el.classList.add('sel');
    document.getElementById('sumType').textContent =
        type === '2school' ? '2 Schools — You + 1 other' : '3 Schools — You + 2 others';
}

function pickTimer(t, el) {
    ST.timer = t;
    document.querySelectorAll('.tc').forEach(b => b.classList.remove('sel'));
    el.classList.add('sel');
    document.getElementById('sumTimer').textContent = t + 's per question';
}

function toggleAC() {
    ST.ac = !ST.ac;
    document.getElementById('acToggle').classList.toggle('on', ST.ac);
    document.getElementById('sumAC').textContent = ST.ac ? 'Enabled' : 'Disabled';
}

async function launch() {
    if (!ST.quizId) { toast('Please select a quiz', 'err'); return; }
    if (!ST.type)   { toast('Please choose how many schools', 'err'); return; }

    const btn = document.getElementById('launchBtn');
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-block;animation:spin .6s linear infinite">⏳</span> Creating…';

    try {
        const res = await fetch('{{ route("institution.battle.create") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                quiz_id:                    ST.quizId,
                battle_type:                ST.type,
                participating_institutions: [], // server always adds host as School 1
                question_timer:             ST.timer,
                anti_cheat:                 ST.ac,
            }),
        });
        const d = await res.json();
        if (d.success) {
            window.location.href = d.redirectUrl; // → lobby (setup-page)
        } else {
            toast(d.message || 'Failed to create battle', 'err');
            btn.disabled = false;
            btn.innerHTML = '⚔️ Create Battle & Get Invite Codes';
        }
    } catch(e) {
        toast('Network error', 'err');
        btn.disabled = false;
        btn.innerHTML = '⚔️ Create Battle & Get Invite Codes';
    }
}

function toast(msg, type) {
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}

@if($quiz)
document.addEventListener('DOMContentLoaded', () => {
    const row = document.querySelector('.qrow[data-id="{{ $quiz->id }}"]');
    if (row) row.click();
});
@endif
</script>
@endsection