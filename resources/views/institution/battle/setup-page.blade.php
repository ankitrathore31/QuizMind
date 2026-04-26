{{-- resources/views/institution/battle/setup-page.blade.php --}}
@extends('institution.layout.master')
@section('page_title', 'Battle Lobby')

@section('content')
<style>
    :root {
        --gold:#f5c842; --gold-dim:rgba(245,200,66,.12);
        --green:#00e396; --red:#ff4d6a; --ice:#00d4ff;
        --s1:#7c5cfc; --s2:#00d4ff; --s3:#f5c842;
    }

    .lobby-wrap { max-width:1200px; margin:0 auto; padding:36px 20px 60px; }

    /* ── Header ── */
    .lobby-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:28px; }
    .lh-left {}
    .lh-badge { display:inline-flex; align-items:center; gap:7px; background:var(--gold-dim); border:1px solid rgba(245,200,66,.3); color:var(--gold); padding:4px 12px; border-radius:16px; font-size:.68rem; font-weight:800; letter-spacing:1px; text-transform:uppercase; margin-bottom:10px; }
    .lh-title { font-family:var(--fh); font-size:1.7rem; font-weight:900; margin-bottom:4px; }
    .lh-meta  { font-size:.8rem; color:var(--muted); display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
    .lh-meta span { display:flex; align-items:center; gap:5px; }
    .status-pill { padding:4px 12px; border-radius:20px; font-size:.72rem; font-weight:800; }
    .status-pill.setup        { background:rgba(124,92,252,.12); border:1px solid rgba(124,92,252,.3); color:var(--accent); }
    .status-pill.registration { background:rgba(0,227,150,.1);   border:1px solid rgba(0,227,150,.3); color:var(--green); }
    .status-pill.in_progress  { background:rgba(245,200,66,.12); border:1px solid rgba(245,200,66,.3); color:var(--gold); }

    /* Live indicator */
    .live-dot { width:8px; height:8px; border-radius:50%; background:var(--green); animation:pulse 1.4s ease infinite; flex-shrink:0; }
    @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.7)} }

    /* Total counter */
    .total-counter {
        display:flex; align-items:center; gap:10px; padding:10px 16px;
        background:rgba(124,92,252,.07); border:1.5px solid rgba(124,92,252,.2);
        border-radius:var(--radius-sm); font-size:.82rem; font-weight:700;
    }
    .tc-num { font-family:var(--fh); font-size:1.5rem; font-weight:900; color:var(--accent); }

    /* ── Split-screen school columns ── */
    .schools-grid { display:grid; gap:14px; margin-bottom:24px; }
    .schools-grid.s2 { grid-template-columns:1fr 1fr; }
    .schools-grid.s3 { grid-template-columns:1fr 1fr 1fr; }
    @media(max-width:700px) { .schools-grid.s2,.schools-grid.s3 { grid-template-columns:1fr; } }

    .school-col {
        background:var(--card); border:1.5px solid var(--border);
        border-radius:var(--radius); overflow:hidden;
        transition:border-color .3s;
    }
    .school-col.joined { border-color:rgba(0,227,150,.35); }
    .school-col.s1 .sc-head { background:rgba(124,92,252,.1); border-bottom:1.5px solid rgba(124,92,252,.2); }
    .school-col.s2 .sc-head { background:rgba(0,212,255,.08); border-bottom:1.5px solid rgba(0,212,255,.18); }
    .school-col.s3 .sc-head { background:rgba(245,200,66,.08); border-bottom:1.5px solid rgba(245,200,66,.2); }

    .sc-head { padding:14px 16px; }
    .sc-head-top  { display:flex; align-items:center; gap:8px; margin-bottom:6px; }
    .sc-num-badge { padding:3px 9px; border-radius:12px; font-size:.65rem; font-weight:900; text-transform:uppercase; letter-spacing:.7px; }
    .s1 .sc-num-badge { background:rgba(124,92,252,.15); color:var(--s1); border:1px solid rgba(124,92,252,.3); }
    .s2 .sc-num-badge { background:rgba(0,212,255,.1);   color:var(--s2); border:1px solid rgba(0,212,255,.25); }
    .s3 .sc-num-badge { background:rgba(245,200,66,.1);  color:var(--s3); border:1px solid rgba(245,200,66,.25); }
    .sc-inst-name { font-weight:900; font-size:.95rem; margin-bottom:8px; }
    .sc-waiting   { font-size:.78rem; color:var(--muted); font-style:italic; }
    .sc-host-chip { font-size:.62rem; padding:2px 8px; border-radius:10px; background:rgba(0,227,150,.1); border:1px solid rgba(0,227,150,.25); color:var(--green); font-weight:800; margin-left:auto; }
    .sc-count { display:flex; align-items:center; gap:6px; margin-bottom:10px; }
    .sc-count-num { font-family:var(--fh); font-size:1.4rem; font-weight:900; }
    .s1 .sc-count-num { color:var(--s1); }
    .s2 .sc-count-num { color:var(--s2); }
    .s3 .sc-count-num { color:var(--s3); }
    .sc-count-lbl { font-size:.7rem; color:var(--muted); font-weight:700; }

    /* ── Codes section (under institution name) ── */
    .codes-section {
        padding:10px 14px;
        background:rgba(255,255,255,.02);
        border-top:1px solid rgba(255,255,255,.05);
        margin-bottom:10px;
    }
    .code-row {
        display:flex; align-items:center; gap:8px; margin-bottom:6px;
    }
    .code-row:last-child { margin-bottom:0; }
    .code-label {
        font-size:.65rem; font-weight:800; text-transform:uppercase; letter-spacing:.6px;
        color:var(--muted); min-width:80px;
    }
    .code-display {
        font-family:var(--fh); font-size:.85rem; font-weight:900; letter-spacing:2px;
        padding:5px 10px; background:rgba(255,255,255,.05); border-radius:8px;
        flex:1; min-width:0; word-break:break-all;
    }
    .code-copy {
        padding:4px 10px; border-radius:8px; border:1px solid rgba(255,255,255,.15);
        background:transparent; color:var(--muted); font-size:.65rem; font-weight:800;
        cursor:pointer; transition:all .15s; flex-shrink:0; white-space:nowrap;
    }
    .code-copy:hover { border-color:var(--accent); color:var(--accent); }
    .code-copy.ok { border-color:var(--green); color:var(--green); }

    /* ── Joined indicator ── */
    .joined-indicator {
        font-size:.65rem; padding:3px 8px; border-radius:8px;
        background:rgba(0,227,150,.1); border:1px solid rgba(0,227,150,.25);
        color:var(--green); font-weight:800; display:none;
    }
    .joined-indicator.show { display:inline-block; }

    /* Student list */
    .sc-body { padding:0; }
    .student-list { list-style:none; margin:0; padding:0; max-height:300px; overflow-y:auto; }
    .student-list::-webkit-scrollbar { width:3px; }
    .student-list::-webkit-scrollbar-thumb { background:var(--border); border-radius:2px; }

    .student-item {
        display:flex; align-items:center; gap:10px; padding:10px 16px;
        border-bottom:1px solid var(--border); animation:slideIn .25s ease;
    }
    .student-item:last-child { border-bottom:none; }
    @keyframes slideIn { from{opacity:0;transform:translateX(-8px)} to{opacity:1;transform:none} }

    .student-avatar {
        width:30px; height:30px; border-radius:50%; background:var(--gradient);
        display:flex; align-items:center; justify-content:center;
        font-size:.75rem; font-weight:900; color:#fff; flex-shrink:0;
    }
    .student-name { font-size:.82rem; font-weight:700; flex:1; min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .student-status { font-size:.65rem; padding:2px 7px; border-radius:8px; font-weight:700; }
    .student-status.registered { background:rgba(0,227,150,.1); color:var(--green); }
    .student-status.playing    { background:rgba(245,200,66,.1); color:var(--gold); }

    .sc-empty {
        padding:28px 16px; text-align:center; color:var(--muted);
        font-size:.8rem;
    }
    .sc-empty-icon { font-size:1.8rem; margin-bottom:8px; opacity:.5; }

    /* ── Bottom controls (host only) ── */
    .controls-bar { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
    .start-btn {
        flex:1; min-width:200px; padding:15px 28px; border:none; border-radius:var(--radius-sm);
        background:var(--gradient); color:#fff; font-family:var(--fh); font-size:1rem; font-weight:900;
        cursor:pointer; transition:all .2s; display:flex; align-items:center; justify-content:center; gap:10px;
    }
    .start-btn:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 8px 28px rgba(124,92,252,.45); }
    .start-btn:disabled { opacity:.4; cursor:not-allowed; }
    .start-hint { font-size:.78rem; color:var(--muted); }

    /* Observer view bottom */
    .observer-bar {
        padding:16px 20px; background:rgba(0,212,255,.06); border:1.5px solid rgba(0,212,255,.18);
        border-radius:var(--radius-sm); text-align:center;
    }
    .ob-title { font-weight:800; font-size:.9rem; color:var(--ice); margin-bottom:4px; }
    .ob-sub   { font-size:.78rem; color:var(--muted); }

    @keyframes spin { to { transform:rotate(360deg) } }
</style>

<div class="lobby-wrap">

    {{-- Header --}}
    <div class="lobby-header">
        <div class="lh-left">
            <div class="lh-badge">⚔️ Institution Battle Lobby</div>
            <h1 class="lh-title">{{ $battle->quiz->title ?? 'Battle' }}</h1>
            <div class="lh-meta">
                <span><div class="live-dot"></div> Live</span>
                <span>📚 {{ $battle->total_questions }} questions</span>
                <span>⏱ {{ $battle->question_timer }}s per question</span>
                <span>🏫 {{ $battle->battle_type === '3school' ? '3-School' : '2-School' }} Battle</span>
                <span class="status-pill {{ $battle->status }}" id="statusPill">
                    {{ ucfirst(str_replace('_',' ',$battle->status)) }}
                </span>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <div class="total-counter">
                <div>
                    <div style="font-size:.67rem;font-weight:800;text-transform:uppercase;letter-spacing:.7px;color:var(--muted)">Total Players</div>
                    <div class="tc-num" id="totalCount">{{ $battle->participants->count() }}</div>
                </div>
                🎓
            </div>
        </div>
    </div>

    {{-- ── Split-screen school columns with codes and students ── --}}
    @php
        $isHost      = $battle->created_by === Auth::id();
        $myInst      = \App\Models\Institution::where('user_id', Auth::id())->first()
                       ?? optional(Auth::user())->institution;
        $suffixes    = \App\Models\InstitutionBattle::institutionSubSuffixes();
        $totalSlots  = $battle->battle_type === '3school' ? 3 : 2;
        $participating = $battle->participating_institutions ?? [];
    @endphp

    <div class="schools-grid {{ $battle->battle_type === '3school' ? 's3' : 's2' }}" id="schoolsGrid">
        @for($slot = 1; $slot <= $totalSlots; $slot++)
            @php
                $instId   = $participating[$slot - 1] ?? null;
                $inst     = $instId ? \App\Models\Institution::find($instId) : null;
                $students = $battle->participants->where('institution_id', $instId)->values();
                $colors   = ['s1','s2','s3'];
                $colClass = $colors[$slot - 1];
                $inviteCode = $slot > 1 ? $battle->code . '-' . $suffixes[$slot - 2] : '';
                $studentCode = $instId ? $battle->studentCodeForSlot($slot) : '';
            @endphp
            <div class="school-col {{ $colClass }} {{ $inst ? 'joined' : '' }}" id="schoolCol{{ $slot }}">
                <div class="sc-head">
                    <div class="sc-head-top">
                        <span class="sc-num-badge">School {{ $slot }}</span>
                        @if($slot === 1)<span class="sc-host-chip">HOST</span>@endif
                    </div>
                    <div class="sc-inst-name" id="scInstName{{ $slot }}">
                        {{ $inst ? $inst->name : '⏳ Waiting for institution to join…' }}
                    </div>
                    <div class="sc-count" style="margin-bottom:8px">
                        <div class="sc-count-num" id="scCount{{ $slot }}">{{ $students->count() }}</div>
                        <div class="sc-count-lbl">students joined</div>
                    </div>

                    {{-- Codes Section --}}
                    <div class="codes-section">
                        {{-- Institution Invite Code (host sees all, observer sees only school 2/3) --}}
                        @if($slot > 1 && ($isHost || ($myInst && in_array($myInst->id, $participating))))
                        <div class="code-row">
                            <span class="code-label">📋 Invite</span>
                            <span class="code-display" id="inviteCode{{ $slot }}">{{ $inviteCode }}</span>
                            @if($isHost)
                            <button class="code-copy" onclick="copyCode('{{ $inviteCode }}', this)">Copy</button>
                            <span class="joined-indicator" id="joinedInd{{ $slot }}">✓ Joined</span>
                            @endif
                        </div>
                        @endif

                        {{-- Student Code --}}
                        @if($studentCode && ($isHost || ($myInst && $myInst->id === $instId)))
                        <div class="code-row">
                            <span class="code-label">🎓 Students</span>
                            <span class="code-display" id="studentCode{{ $slot }}">{{ $studentCode }}</span>
                            <button class="code-copy" onclick="copyCode('{{ $studentCode }}', this)">Copy</button>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="sc-body">
                    <ul class="student-list" id="studentList{{ $slot }}">
                        @forelse($students as $p)
                            <li class="student-item" data-uid="{{ $p->user_id }}">
                                <div class="student-avatar">{{ strtoupper(substr($p->user->name,0,1)) }}</div>
                                <div class="student-name">{{ $p->user->name }}</div>
                                <span class="student-status {{ $p->status }}">{{ $p->status }}</span>
                            </li>
                        @empty
                            <li class="sc-empty" id="emptyMsg{{ $slot }}">
                                <div class="sc-empty-icon">🎓</div>
                                <div>No students yet</div>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endfor
    </div>

    {{-- ── Bottom bar ── --}}
    @if($isHost)
    <div class="controls-bar">
        <button class="start-btn" id="startBtn" onclick="startBattle()"
            {{ $battle->participants->count() < 2 ? 'disabled' : '' }}>
            🚀 Start Battle
        </button>
        <div class="start-hint" id="startHint">
            @if($battle->participants->count() < 2)
                Need at least 2 students to start
            @else
                {{ $battle->participants->count() }} student(s) ready
            @endif
        </div>
    </div>
    @else
    <div class="observer-bar">
        <div class="ob-title">⏳ Waiting for the host to start the battle…</div>
        <div class="ob-sub">Your students are joining. The host will launch the battle when everyone is ready.</div>
    </div>
    @endif

</div>

<script>
const BATTLE_CODE    = '{{ $battle->code }}';
const LOBBY_STATE_URL= '{{ route('institution.battle.lobby-state', $battle->code) }}';
const START_URL      = '{{ route('institution.battle.start') }}';
const ARENA_URL      = '{{ route('institution.battle.arena', $battle->code) }}';
const CSRF           = '{{ csrf_token() }}';
const IS_HOST        = {{ $isHost ? 'true' : 'false' }};
const TOTAL_SLOTS    = {{ $totalSlots }};

// ── Participating institutions map
let schoolInstMap = @json(array_values($participating));

// ─────────────────────────────────────────────────────────────
// LIVE UPDATES — Echo + polling fallback
// ─────────────────────────────────────────────────────────────

// Try Laravel Echo (Pusher/Ably) first
if (typeof window.Echo !== 'undefined') {
    window.Echo.channel(`institution-battle.${BATTLE_CODE}`)
        .listen('institution-battle.lobby-updated', (e) => {
            refreshFromServer();
        })
        .listen('institution-battle.started', () => {
            window.location.href = ARENA_URL;
        })
        .listen('institution-battle.countdown-updated', () => {
            window.location.href = ARENA_URL;
        });
}

// Polling fallback (every 3s)
let pollInterval = setInterval(refreshFromServer, 3000);

async function refreshFromServer() {
    try {
        const res = await fetch(LOBBY_STATE_URL, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const d = await res.json();
        if (!d.success) return;

        // Redirect if battle started
        if (['in_progress','countdown'].includes(d.status)) {
            clearInterval(pollInterval);
            window.location.href = ARENA_URL;
            return;
        }

        // Update total count
        document.getElementById('totalCount').textContent = d.totalStudents;

        // Update status pill
        const pill = document.getElementById('statusPill');
        if (pill) {
            pill.className = `status-pill ${d.status}`;
            pill.textContent = d.status.replace('_',' ').replace(/\b\w/g,c=>c.toUpperCase());
        }

        // Update each school column
        d.schools.forEach(school => {
            updateSchoolColumn(school);
        });

        // Update start button
        if (IS_HOST) {
            const startBtn = document.getElementById('startBtn');
            const startHint = document.getElementById('startHint');
            if (d.totalStudents >= 2) {
                startBtn.disabled = false;
                startHint.textContent = `${d.totalStudents} student(s) ready — good to go!`;
            } else {
                startBtn.disabled = true;
                startHint.textContent = 'Need at least 2 students to start';
            }
        }
    } catch(e) {
        // Silently fail
    }
}

function updateSchoolColumn(school) {
    const col = document.getElementById(`schoolCol${school.slot}`);
    if (!col) return;

    // Institution name
    const nameEl = document.getElementById(`scInstName${school.slot}`);
    if (nameEl) {
        nameEl.textContent = school.institution
            ? school.institution.name
            : '⏳ Waiting for institution to join…';
    }

    // Joined class
    if (school.joined) {
        col.classList.add('joined');
    }

    // Count
    const countEl = document.getElementById(`scCount${school.slot}`);
    if (countEl) countEl.textContent = school.studentCount;

    // Show/hide joined indicator
    if (IS_HOST) {
        const ind = document.getElementById(`joinedInd${school.slot}`);
        if (ind) {
            if (school.joined && school.institution) {
                ind.classList.add('show');
                ind.textContent = `✓ ${school.institution.name}`;
            } else {
                ind.classList.remove('show');
            }
        }
    }

    // Student list
    const listEl = document.getElementById(`studentList${school.slot}`);
    if (!listEl) return;

    const existingIds = new Set(
        [...listEl.querySelectorAll('.student-item')].map(el => el.dataset.uid)
    );

    // Remove empty placeholder if students exist
    if (school.students.length > 0) {
        const emptyEl = document.getElementById(`emptyMsg${school.slot}`);
        if (emptyEl) emptyEl.remove();
    }

    school.students.forEach(stu => {
        if (!existingIds.has(String(stu.user_id))) {
            const li = document.createElement('li');
            li.className = 'student-item';
            li.dataset.uid = stu.user_id;
            li.innerHTML = `
                <div class="student-avatar">${stu.avatar}</div>
                <div class="student-name">${escHtml(stu.name)}</div>
                <span class="student-status ${stu.status}">${stu.status}</span>
            `;
            listEl.appendChild(li);
        }
    });
}

// ─────────────────────────────────────────────────────────────
// Start battle (host only)
// ─────────────────────────────────────────────────────────────
async function startBattle() {
    const btn = document.getElementById('startBtn');
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-block;animation:spin .6s linear infinite">⏳</span> Starting…';

    try {
        const res = await fetch(START_URL, {
            method: 'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     CSRF,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ code: BATTLE_CODE }),
        });
        const d = await res.json();
        if (d.success) {
            clearInterval(pollInterval);
            window.location.href = d.redirectUrl || ARENA_URL;
        } else {
            alert(d.message || 'Could not start battle.');
            btn.disabled = false;
            btn.innerHTML = '🚀 Start Battle';
        }
    } catch(e) {
        alert('Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '🚀 Start Battle';
    }
}

// ─────────────────────────────────────────────────────────────
// Copy helpers
// ─────────────────────────────────────────────────────────────
function copyCode(code, btn) {
    navigator.clipboard.writeText(code).then(() => {
        const orig = btn.textContent;
        btn.textContent = '✓ Copied!';
        btn.classList.add('ok');
        setTimeout(() => { btn.textContent = orig; btn.classList.remove('ok'); }, 2500);
    });
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Initial refresh immediately on load
refreshFromServer();
</script>
@endsection