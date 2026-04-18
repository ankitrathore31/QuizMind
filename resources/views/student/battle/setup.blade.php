{{-- resources/views/student/battle/setup.blade.php --}}
@extends('student.layout.master')
@section('title', 'Battle Setup')

@section('content')
<style>
.setup-wrap { max-width:580px;margin:0 auto;padding:40px 16px; }
.setup-hero  { text-align:center;margin-bottom:32px; }
.setup-hero h1 { font-family:var(--fh);font-size:1.8rem;font-weight:900;margin-bottom:6px; }

.setup-card { background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:22px 22px;margin-bottom:14px; }
.card-title { font-family:var(--fh);font-weight:800;font-size:.88rem;margin-bottom:16px;display:flex;align-items:center;gap:8px; }

/* Mode pills */
.mode-pill { display:flex;align-items:center;gap:12px;padding:13px 16px;border-radius:var(--radius-sm);
    border:1.5px solid var(--border);background:transparent;cursor:pointer;width:100%;
    margin-bottom:10px;transition:.2s;text-align:left; }
.mode-pill:hover,.mode-pill.active { border-color:var(--accent);background:rgba(124,92,252,.06); }
.mp-icon { font-size:1.5rem; }
.mp-info { flex:1 }
.mp-title{ font-weight:700;font-size:.88rem; }
.mp-desc { font-size:.74rem;color:var(--muted); }
.mp-check{ width:20px;height:20px;border-radius:50%;border:2px solid var(--border);
    display:flex;align-items:center;justify-content:center;transition:.2s;flex-shrink:0; }
.mode-pill.active .mp-check { border-color:var(--accent);background:var(--accent); }
.mode-pill.active .mp-check::after { content:'✓';color:#fff;font-size:.7rem;font-weight:700; }

/* Toggle switch */
.toggle-row { display:flex;align-items:center;justify-content:space-between;padding:10px 0;
    border-bottom:1px solid var(--border2); }
.toggle-row:last-child { border:none; }
.toggle-label { font-size:.84rem;font-weight:600; }
.toggle-sub   { font-size:.72rem;color:var(--muted);margin-top:2px; }
.toggle-switch {
    position:relative;width:44px;height:24px;flex-shrink:0;
}
.toggle-switch input { opacity:0;width:0;height:0; }
.toggle-thumb {
    position:absolute;inset:0;border-radius:24px;background:rgba(255,255,255,.1);
    cursor:pointer;transition:.25s;border:1px solid var(--border);
}
.toggle-thumb::before {
    content:'';position:absolute;width:18px;height:18px;border-radius:50%;
    background:#fff;top:2px;left:2px;transition:.25s;
}
.toggle-switch input:checked + .toggle-thumb { background:var(--accent);border-color:var(--accent); }
.toggle-switch input:checked + .toggle-thumb::before { transform:translateX(20px); }

/* Privacy badge */
.privacy-badge {
    display:inline-flex;align-items:center;gap:6px;padding:5px 12px;
    border-radius:20px;font-size:.76rem;font-weight:700;border:1px solid;margin-top:8px;cursor:pointer;
    transition:.2s;
}
.badge-public  { background:rgba(0,212,255,.08);color:#00d4ff;border-color:rgba(0,212,255,.2); }
.badge-private { background:rgba(124,92,252,.08);color:var(--accent);border-color:rgba(124,92,252,.2); }
.badge-public:hover  { background:rgba(0,212,255,.15); }
.badge-private:hover { background:rgba(124,92,252,.15); }

.team-inputs-wrap { display:none;margin-top:14px;padding-top:14px;border-top:1px solid var(--border2); }
.config-row { display:flex;gap:10px;margin-bottom:12px; }
.config-row .form-float { margin:0;flex:1; }

/* Quiz chip */
.quiz-chip { display:inline-flex;align-items:center;gap:8px;padding:6px 12px;
    background:rgba(0,212,255,.06);border:1px solid rgba(0,212,255,.18);
    border-radius:20px;font-size:.8rem;margin-bottom:16px;max-width:100%;overflow:hidden; }

.launch-btn { background:var(--gradient);color:#fff;border:none;padding:15px;
    border-radius:var(--radius-sm);font-weight:800;font-size:1rem;width:100%;
    cursor:pointer;font-family:var(--fh);transition:.2s;margin-top:4px; }
.launch-btn:hover { opacity:.9;transform:translateY(-1px);box-shadow:0 6px 24px rgba(124,92,252,.35); }
.launch-btn:disabled { opacity:.45;cursor:not-allowed;transform:none; }

.public-notice {
    background:rgba(0,212,255,.05);border:1px solid rgba(0,212,255,.18);
    border-radius:var(--radius-sm);padding:10px 14px;font-size:.78rem;
    color:var(--muted);margin-top:10px;display:none;
}
.public-notice.show { display:block; }
</style>

<div class="setup-wrap anim-fade">
    <div class="setup-hero">
        <div style="font-size:3.5rem;margin-bottom:12px" class="anim-float">⚔️</div>
        <h1>Create Battle Room</h1>
        <p class="text-muted" style="font-size:.86rem">Configure your quiz battle settings</p>
    </div>

    {{-- Quiz info --}}
    <div class="setup-card">
        <div class="card-title">📚 Quiz</div>
        <div class="quiz-chip">
            <span>🤖</span>
            <span style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $quiz->title ?? 'Untitled Quiz' }}</span>
            <span class="text-muted">·</span>
            <span class="text-muted;white-space:nowrap">{{ count($quiz->questions ?? []) }} Q</span>
            <span class="text-muted">·</span>
            <span class="text-muted">{{ ucfirst($quiz->difficulty ?? 'intermediate') }}</span>
        </div>

        {{-- Battle mode --}}
        <div class="card-title" style="margin-bottom:12px">⚔️ Battle Mode</div>
        <button class="mode-pill active" onclick="selMode('1v1',this)">
            <span class="mp-icon">⚔️</span>
            <div class="mp-info"><div class="mp-title">1v1 Battle</div><div class="mp-desc">Head-to-head with one opponent</div></div>
            <span class="mp-check"></span>
        </button>
        <button class="mode-pill" onclick="selMode('group',this)">
            <span class="mp-icon">👥</span>
            <div class="mp-info"><div class="mp-title">Group Battle</div><div class="mp-desc">2–10 players compete live</div></div>
            <span class="mp-check"></span>
        </button>
        <button class="mode-pill" onclick="selMode('team',this)">
            <span class="mp-icon">🏆</span>
            <div class="mp-info"><div class="mp-title">Team vs Team</div><div class="mp-desc">Two teams compete together</div></div>
            <span class="mp-check"></span>
        </button>

        {{-- Team name inputs --}}
        <div class="team-inputs-wrap" id="teamInputs">
            <div class="config-row">
                <div class="form-float">
                    <input type="text" id="teamAName" placeholder=" " maxlength="30" value="Team A">
                    <label>Team A Name</label>
                </div>
                <div class="form-float">
                    <input type="text" id="teamBName" placeholder=" " maxlength="30" value="Team B">
                    <label>Team B Name</label>
                </div>
            </div>
            <div class="form-float">
                <select id="maxPerTeam">
                    @foreach([2,3,4,5,8,10,15,20] as $n)
                        <option value="{{ $n }}" {{ $n===5?'selected':'' }}>Max {{ $n }} players per team</option>
                    @endforeach
                </select>
                <label>Players per Team</label>
            </div>
        </div>
    </div>

    {{-- Settings --}}
    <div class="setup-card">
        <div class="card-title">⚙️ Settings</div>

        <div class="config-row">
            <div class="form-float">
                <select id="qTimer">
                    <option value="10">10 seconds per question</option>
                    <option value="15">15 seconds per question</option>
                    <option value="20" selected>20 seconds per question</option>
                    <option value="30">30 seconds per question</option>
                    <option value="45">45 seconds per question</option>
                    <option value="60">60 seconds per question</option>
                </select>
                <label>Question Timer</label>
            </div>
        </div>

        {{-- Anti-cheat toggle --}}
        <div class="toggle-row">
            <div>
                <div class="toggle-label">🛡️ Anti-Cheat</div>
                <div class="toggle-sub">Penalise tab switching & window minimising</div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="antiCheatToggle" checked onchange="updateAntiCheat(this)">
                <span class="toggle-thumb"></span>
            </label>
        </div>

        {{-- Privacy toggle --}}
        <div class="toggle-row">
            <div>
                <div class="toggle-label" id="privacyLabel">🔒 Private Room</div>
                <div class="toggle-sub" id="privacySub">Only joinable with room code</div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="privacyToggle" checked onchange="updatePrivacy(this)">
                <span class="toggle-thumb"></span>
            </label>
        </div>

        <div class="public-notice" id="publicNotice">
            🌐 <strong>Public room</strong> — Students matching your quiz subject, class and difficulty will see this battle in their dashboard and can join. Great for finding random opponents!
        </div>
    </div>

    <button class="launch-btn" id="launchBtn" onclick="launchRoom()">
        🚀 Create Room &amp; Get Invite Link
    </button>
</div>

<script>
let currentMode  = '1v1';
let antiCheat    = true;
let isPublic     = false;

function selMode(mode, btn) {
    currentMode = mode;
    document.querySelectorAll('.mode-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('teamInputs').style.display = mode === 'team' ? '' : 'none';
}

function updateAntiCheat(cb) { antiCheat = cb.checked; }

function updatePrivacy(cb) {
    isPublic = !cb.checked;
    document.getElementById('privacyLabel').textContent = isPublic ? '🌐 Public Room' : '🔒 Private Room';
    document.getElementById('privacySub').textContent   = isPublic
        ? 'Visible to matching students in their dashboard'
        : 'Only joinable with room code';
    const notice = document.getElementById('publicNotice');
    notice.classList.toggle('show', isPublic);
}

async function launchRoom() {
    const btn = document.getElementById('launchBtn');
    btn.disabled    = true;
    btn.textContent = '⏳ Creating room…';

    const payload = {
        quiz_id        : {{ $quiz->id }},
        mode           : currentMode,
        question_timer : +document.getElementById('qTimer').value,
        anti_cheat     : antiCheat,
        is_public      : isPublic,
        subject        : '{{ $quiz->subject ?? '' }}',
        difficulty     : '{{ $quiz->difficulty ?? 'intermediate' }}',
        class_level    : '{{ $quiz->class ?? '' }}',
    };

    if (currentMode === 'team') {
        const a = document.getElementById('teamAName').value.trim();
        const b = document.getElementById('teamBName').value.trim();
        if (!a || !b) {
            showMsg('Enter both team names');
            btn.disabled    = false;
            btn.textContent = '🚀 Create Room & Get Invite Link';
            return;
        }
        payload.team_a_name  = a;
        payload.team_b_name  = b;
        payload.max_per_team = +document.getElementById('maxPerTeam').value;
    }

    try {
        const res = await fetch('{{ route('student.battle.create') }}', {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','X-Requested-With':'XMLHttpRequest'},
            body:JSON.stringify(payload),
        });
        const d = await res.json();
        if (d.success) {
            window.location.href = d.redirectUrl;
        } else {
            showMsg(d.message || 'Failed to create room');
            btn.disabled    = false;
            btn.textContent = '🚀 Create Room & Get Invite Link';
        }
    } catch {
        showMsg('Network error');
        btn.disabled    = false;
        btn.textContent = '🚀 Create Room & Get Invite Link';
    }
}

function showMsg(msg) {
    if (typeof window.showToast === 'function') window.showToast('error', msg);
    else alert(msg);
}
</script>
@endsection