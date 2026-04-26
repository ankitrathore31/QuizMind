{{-- resources/views/student/battle/join.blade.php --}}
{{-- MERGED: Normal Battle + Public Battle + Institution Battle --}}
@extends('student.layout.master')
@section('title', 'Join Battle')

@section('content')
<style>
/* ══════════════════════════════════════════════
   JOIN PAGE — UNIFIED BATTLE HUB
   Supports: Normal (code) | Public | Institution
   ══════════════════════════════════════════════ */

.jh-wrap   { max-width: 640px; margin: 0 auto; padding: 40px 16px 60px; }

/* ── Hero ── */
.jh-hero   { text-align: center; margin-bottom: 32px; }
.jh-icon   { font-size: 3.2rem; display: block; margin-bottom: 10px;
             animation: floatIcon 3s ease-in-out infinite; }
@keyframes floatIcon { 0%,100%{ transform:translateY(0) } 50%{ transform:translateY(-9px) } }
.jh-title  { font-family:var(--fh); font-size:1.9rem; font-weight:900; margin-bottom:6px;
             background:var(--gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
.jh-sub    { color:var(--muted); font-size:.84rem; line-height:1.65; }

/* ── Student welcome chip ── */
.student-chip {
    display:flex; align-items:center; gap:10px; padding:11px 14px;
    background:rgba(124,92,252,.07); border:1.5px solid rgba(124,92,252,.2);
    border-radius:var(--radius-sm); margin-bottom:24px;
}
.student-avatar {
    width:34px; height:34px; border-radius:50%; background:var(--gradient);
    display:flex; align-items:center; justify-content:center;
    font-weight:900; font-size:.88rem; color:#fff; flex-shrink:0;
}
.student-hello { font-size:.82rem; font-weight:700; }
.student-name  { font-size:.73rem; color:var(--muted); }

/* ── Pre-filled invite card ── */
.prefilled-card {
    background:linear-gradient(135deg,rgba(0,227,150,.06),rgba(0,212,255,.04));
    border:1px solid rgba(0,227,150,.2); border-radius:var(--radius); padding:20px;
    margin-bottom:18px; animation:fadeUp .4s ease;
}
.pf-head {
    font-family:var(--fh); font-weight:800; font-size:.9rem; margin-bottom:12px;
    display:flex; align-items:center; gap:8px;
}
.live-dot {
    display:inline-block; width:7px; height:7px; border-radius:50%;
    background:#22c55e; animation:pdot 1.5s infinite;
}
@keyframes pdot{0%,100%{opacity:1}50%{opacity:.3}}

/* ── Tab switcher ── */
.tab-bar {
    display:grid; grid-template-columns:repeat(3,1fr);
    background:rgba(255,255,255,.03); border:1px solid var(--border);
    border-radius:var(--radius-sm); padding:4px; gap:4px; margin-bottom:20px;
}
.tab-btn {
    padding:9px 6px; border:none; border-radius:6px; background:transparent;
    color:var(--muted); font-family:var(--fh); font-size:.76rem; font-weight:700;
    cursor:pointer; transition:.2s; text-align:center; white-space:nowrap;
}
.tab-btn:hover    { color:var(--text); background:rgba(255,255,255,.04); }
.tab-btn.active   { background:var(--gradient); color:#fff; }

/* ── Panels ── */
.tab-panel { display:none; animation:fadeUp .3s ease; }
.tab-panel.active { display:block; }
@keyframes fadeUp { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:none} }

/* ── Shared card shell ── */
.jh-card  {
    background:var(--card); border:1.5px solid var(--border);
    border-radius:var(--radius); padding:24px;
}

/* ── Code input ── */
.code-input-wrap { position:relative; margin-bottom:14px; }
.code-input {
    width:100%; padding:14px 42px 14px 18px;
    font-family:var(--fh); font-size:1.3rem; font-weight:900;
    letter-spacing:.15em; text-align:center; text-transform:uppercase;
    background:rgba(124,92,252,.04); border:2px solid var(--border);
    border-radius:var(--radius-sm); color:var(--text); outline:none;
    transition:.2s; caret-color:var(--accent); box-sizing:border-box;
}
.code-input::placeholder { font-size:.88rem; letter-spacing:1px; font-weight:400; }
.code-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(124,92,252,.1); }
.code-input.valid { border-color:rgba(0,227,150,.6); background:rgba(0,227,150,.04); }
.code-input.err   { border-color:rgba(255,77,106,.5); animation:shake .3s ease; }
@keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-6px)}75%{transform:translateX(6px)}}
.ci-clear {
    position:absolute; right:12px; top:50%; transform:translateY(-50%);
    background:none; border:none; color:var(--muted); cursor:pointer; font-size:.85rem; padding:4px;
}

/* ── Room preview (normal battle) ── */
.room-preview {
    display:none; margin-top:14px; padding:14px 16px;
    background:linear-gradient(135deg,rgba(0,212,255,.06),rgba(124,92,252,.05));
    border:1px solid rgba(0,212,255,.2); border-radius:var(--radius-sm); animation:fadeUp .3s ease;
}
.room-preview.show { display:block; }
.rp-row   { display:flex; align-items:center; gap:10px; margin-bottom:6px; }
.rp-icon  { font-size:1.4rem; flex-shrink:0; }
.rp-title { font-weight:700; font-size:.88rem; }
.rp-meta  { font-size:.74rem; color:var(--muted); display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-top:2px; }
.player-chips { display:flex; gap:5px; flex-wrap:wrap; margin-top:8px; }
.p-chip {
    padding:2px 8px; border-radius:20px; font-size:.7rem; font-weight:600;
    background:rgba(255,255,255,.06); border:1px solid var(--border); color:var(--muted);
}
.team-sel { display:none; gap:10px; margin-top:12px; }
.team-sel.show { display:flex; }

/* ── Team selector buttons ── */
.ts-btn {
    flex:1; padding:12px; border-radius:var(--radius-sm); border:1.5px solid var(--border);
    background:transparent; cursor:pointer; text-align:center;
    transition:.2s; font-weight:700; font-size:.84rem; color:var(--text);
}
.ts-btn:hover       { border-color:var(--accent); background:rgba(124,92,252,.06); }
.ts-btn.sel-a       { border-color:var(--accent); background:rgba(124,92,252,.1); color:var(--accent); }
.ts-btn.sel-b       { border-color:#00e396; background:rgba(0,227,150,.08); color:#00e396; }

/* ── Primary action button ── */
.join-btn {
    width:100%; padding:14px; background:var(--gradient); color:#fff; border:none;
    border-radius:var(--radius-sm); font-family:var(--fh); font-weight:800; font-size:.98rem;
    cursor:pointer; transition:.2s; display:flex; align-items:center; justify-content:center;
    gap:8px; margin-top:14px; text-decoration:none;
}
.join-btn:hover:not(:disabled) { opacity:.9; transform:translateY(-1px); box-shadow:0 8px 28px rgba(124,92,252,.35); }
.join-btn:disabled { opacity:.4; cursor:not-allowed; transform:none; }

/* ── Error / info boxes ── */
.err-box {
    display:none; background:rgba(255,77,106,.08); border:1px solid rgba(255,77,106,.25);
    border-radius:var(--radius-sm); padding:10px 14px; color:var(--red); font-size:.82rem;
    font-weight:600; margin-bottom:12px; align-items:center; gap:8px;
}
.err-box.show { display:flex; }
.msg { padding:11px 14px; border-radius:var(--radius-sm); font-size:.81rem; font-weight:600; margin-bottom:14px; display:none; border:1px solid; }
.msg.show { display:block; }
.msg.err  { background:rgba(255,77,106,.07); border-color:rgba(255,77,106,.25); color:var(--red); }
.msg.ok   { background:rgba(0,227,150,.06); border-color:rgba(0,227,150,.2);   color:#00e396; }

/* ── Spinner ── */
.spinner {
    width:16px; height:16px; border:2px solid rgba(124,92,252,.2); border-top-color:var(--accent);
    border-radius:50%; display:none;
}
.spinner.show { display:inline-block; animation:spin .7s linear infinite; }
@keyframes spin { to{ transform:rotate(360deg) } }

/* ── Divider ── */
.or-div {
    display:flex; align-items:center; gap:12px;
    color:var(--muted); font-size:.75rem; margin:20px 0;
}
.or-div::before,.or-div::after { content:''; flex:1; height:1px; background:var(--border); }

/* ── Mode badges ── */
.mode-badge {
    display:inline-flex; align-items:center; padding:2px 7px; border-radius:12px;
    font-size:.66rem; font-weight:700; border:1px solid;
}
.mb-1v1   { background:rgba(124,92,252,.1);  color:var(--accent); border-color:rgba(124,92,252,.3); }
.mb-group { background:rgba(0,212,255,.08);   color:#00d4ff;       border-color:rgba(0,212,255,.25); }
.mb-team  { background:rgba(255,165,0,.1);    color:orange;        border-color:rgba(255,165,0,.3); }

/* ── Public battles (tab 2) ── */
.pub-title {
    font-family:var(--fh); font-weight:800; font-size:.88rem; margin-bottom:12px;
    display:flex; align-items:center; gap:8px;
}
.pub-card {
    background:var(--card); border:1px solid var(--border); border-radius:var(--radius-sm);
    padding:14px 16px; margin-bottom:10px; display:flex; align-items:center; gap:12px;
    transition:.2s; cursor:pointer;
}
.pub-card:hover { border-color:rgba(124,92,252,.35); transform:translateY(-1px); }
.pub-icon  { font-size:1.5rem; flex-shrink:0; }
.pub-info  { flex:1; min-width:0; }
.pub-name  { font-weight:700; font-size:.86rem; margin-bottom:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.pub-meta  { font-size:.72rem; color:var(--muted); display:flex; gap:8px; flex-wrap:wrap; }
.pub-join  {
    background:rgba(124,92,252,.1); border:1px solid rgba(124,92,252,.25); color:var(--accent);
    padding:6px 14px; border-radius:8px; font-size:.76rem; font-weight:700;
    cursor:pointer; white-space:nowrap; transition:.2s;
}
.pub-join:hover { background:rgba(124,92,252,.2); }

/* ── Institution battle (tab 3) ── */
.inst-badge {
    display:inline-flex; align-items:center; gap:7px;
    background:rgba(245,200,66,.12); border:1px solid rgba(245,200,66,.3);
    color:#f5c842; padding:5px 14px; border-radius:20px;
    font-size:.7rem; font-weight:800; letter-spacing:1px;
    text-transform:uppercase; margin-bottom:16px;
}
.preview-panel {
    background:rgba(124,92,252,.06); border:1.5px solid rgba(124,92,252,.2);
    border-radius:var(--radius-sm); overflow:hidden; margin-bottom:14px;
    animation:fadeUp .35s ease;
}
.pp-head {
    padding:12px 16px; background:rgba(124,92,252,.1);
    border-bottom:1px solid rgba(124,92,252,.15);
    display:flex; align-items:center; gap:8px; flex-wrap:wrap;
}
.pp-tag {
    background:var(--gradient); padding:3px 10px; border-radius:12px;
    font-size:.63rem; font-weight:900; color:#fff; text-transform:uppercase; letter-spacing:.8px;
}
.pp-status-chip {
    padding:3px 9px; border-radius:10px; font-size:.68rem; font-weight:800;
    background:rgba(0,227,150,.1); border:1px solid rgba(0,227,150,.25); color:#00e396;
}
.pp-body  { padding:16px; }
.pp-quiz  { font-family:var(--fh); font-size:1rem; font-weight:900; margin-bottom:3px; }
.pp-meta-txt { font-size:.72rem; color:var(--muted); margin-bottom:14px; }
.pp-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-bottom:14px; }
.pp-stat  {
    text-align:center; padding:9px 6px;
    background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:8px;
}
.pp-stat-val { font-family:var(--fh); font-size:1.05rem; font-weight:900; color:var(--accent); }
.pp-stat-lbl { font-size:.62rem; color:var(--muted); font-weight:700; margin-top:2px; text-transform:uppercase; }
.pp-school-row {
    display:flex; align-items:center; gap:10px; padding:9px 12px;
    background:rgba(0,227,150,.05); border:1px solid rgba(0,227,150,.15);
    border-radius:8px; margin-bottom:8px;
}
.pp-school-name { font-weight:800; font-size:.86rem; }
.pp-school-lbl  { font-size:.66rem; color:var(--muted); }
.pp-your-school {
    padding:9px 12px; background:rgba(245,200,66,.07); border:1px solid rgba(245,200,66,.25);
    border-radius:8px; display:flex; align-items:center; gap:8px;
}
.pp-your-label { font-size:.66rem; font-weight:800; color:#f5c842; text-transform:uppercase; letter-spacing:.6px; margin-bottom:2px; }
.pp-your-name  { font-size:.86rem; font-weight:800; }

/* ── Steps hint (institution) ── */
.steps-hint { margin-top:20px; border-top:1px solid var(--border); padding-top:16px; }
.sh-title { font-size:.67rem; font-weight:800; text-transform:uppercase; letter-spacing:.8px; color:var(--muted); margin-bottom:10px; }
.sh-step  { display:flex; align-items:flex-start; gap:10px; margin-bottom:9px; font-size:.77rem; color:var(--muted); line-height:1.5; }
.sh-num   {
    width:20px; height:20px; border-radius:50%; background:rgba(124,92,252,.12);
    border:1px solid rgba(124,92,252,.22); color:var(--accent); font-size:.67rem; font-weight:900;
    display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:1px;
}

/* ── Bottom hints ── */
.hint-box { text-align:center; color:var(--muted); font-size:.76rem; padding:18px 14px 0; }
.hint-box a { color:var(--accent); text-decoration:none; }
.hint-box a:hover { text-decoration:underline; }

/* ── Field label ── */
.field-label {
    font-size:.74rem; font-weight:800; letter-spacing:.6px;
    color:var(--muted); text-transform:uppercase; margin-bottom:8px;
}
</style>

<div class="jh-wrap anim-fade">

    {{-- Hero --}}
    <div class="jh-hero">
        <span class="jh-icon">⚔️</span>
        <h1 class="jh-title">Join a Battle</h1>
        <p class="jh-sub">Pick your battle type below — enter a code, browse public rooms,<br>or use your institution student code.</p>
    </div>

    {{-- Student welcome chip --}}
    @auth
    <div class="student-chip">
        <div class="student-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
        <div>
            <div class="student-hello">👋 Joining as</div>
            <div class="student-name">{{ Auth::user()->name }}</div>
        </div>
    </div>
    @endauth

    {{-- Pre-filled invite card (only when arriving via invite link with a normal room code) --}}
    @if(isset($room) && $room)
    <div class="prefilled-card">
        <div class="pf-head">
            ✅ Room Found!
            <span class="live-dot" style="margin-left:auto"></span>
            <span style="font-size:.7rem;color:#22c55e;font-weight:700">Live</span>
        </div>
        <div class="rp-row">
            <div class="rp-icon">{{ $room->mode==='team'?'🏆':($room->mode==='1v1'?'⚔️':'👥') }}</div>
            <div>
                <div class="rp-title">{{ $room->quiz->title ?? 'Quiz Battle' }}</div>
                <div class="rp-meta">
                    <span class="mode-badge mb-{{ $room->mode }}">{{ $room->mode }}</span>
                    {{ $room->participants->count() }} players · Host: {{ $room->host->name }}
                    · <strong style="color:var(--accent)">{{ $room->code }}</strong>
                </div>
            </div>
        </div>

        @if($room->participants->count() > 0)
        <div class="player-chips">
            @foreach($room->participants->take(8) as $p)
                <span class="p-chip">{{ substr($p->user->name, 0, 10) }}{{ $p->user_id === $room->host_id ? ' 👑' : '' }}</span>
            @endforeach
            @if($room->participants->count() > 8)
                <span class="p-chip">+{{ $room->participants->count() - 8 }} more</span>
            @endif
        </div>
        @endif

        @if($room->mode === 'team' && !isset($preselectedTeam))
        <div style="margin-top:12px">
            <p style="font-size:.76rem;font-weight:700;color:var(--muted);margin-bottom:8px">Choose your team:</p>
            <div style="display:flex;gap:10px">
                <button class="ts-btn" id="pfTeamA" onclick="pfTeam('a',this)">
                    🔵 {{ $room->team_a_name }}<br>
                    <span style="font-size:.68rem;color:var(--muted)">{{ $room->participants->where('team','a')->count() }}/{{ $room->max_per_team }}</span>
                </button>
                <button class="ts-btn" id="pfTeamB" onclick="pfTeam('b',this)">
                    🟢 {{ $room->team_b_name }}<br>
                    <span style="font-size:.68rem;color:var(--muted)">{{ $room->participants->where('team','b')->count() }}/{{ $room->max_per_team }}</span>
                </button>
            </div>
        </div>
        @endif

        @if(isset($alreadyJoined) && $alreadyJoined)
            <div style="margin-top:12px;padding:10px;background:rgba(0,227,150,.08);border-radius:8px;font-size:.82rem;font-weight:600;color:#00e396;text-align:center">
                ✅ You're already in this room!
            </div>
            <a href="{{ route('student.battle.lobby', $room->code) }}" class="join-btn" style="margin-top:10px">🚀 Go to Lobby →</a>
        @else
            <button class="join-btn" id="quickJoinBtn" onclick="quickJoin()"
                style="margin-top:12px" {{ ($room->mode === 'team' && !isset($preselectedTeam)) ? 'disabled' : '' }}>
                🚀 Join Battle Now →
            </button>
        @endif
    </div>
    @endif

    {{-- Global join error (flash) --}}
    @if(isset($joinError) && $joinError)
    <div class="err-box show" style="margin-bottom:16px">⚠️ {{ $joinError }}</div>
    @endif

    {{-- ══════════════════════════
         THREE-TAB SWITCHER
         Tab 1 → Normal (code)
         Tab 2 → Public battles
         Tab 3 → Institution
         ══════════════════════════ --}}
    <div class="tab-bar" role="tablist">
        <button class="tab-btn active" id="tab-normal" role="tab" onclick="switchTab('normal')">🔑 Room Code</button>
        <button class="tab-btn"        id="tab-public" role="tab" onclick="switchTab('public')">🌐 Public</button>
        <button class="tab-btn"        id="tab-inst"   role="tab" onclick="switchTab('inst')">🏫 Institution</button>
    </div>

    {{-- ══════════════════════════════
         PANEL 1 — Normal battle (code)
         ══════════════════════════════ --}}
    <div class="tab-panel active" id="panel-normal">
        <div class="jh-card">
            <p class="field-label">🔑 Enter Room Code</p>
            <div class="err-box" id="normalErrBox">⚠️ <span id="normalErrMsg"></span></div>
            <div style="position:relative">
                <input type="text" id="codeInput" class="code-input" placeholder="QM-XXXX"
                    maxlength="7" autocomplete="off" spellcheck="false"
                    oninput="onCode(this)" onkeydown="if(event.key==='Enter') lookupCode()">
                <div class="spinner" id="normalSpinner"
                    style="position:absolute;right:14px;top:50%;transform:translateY(-50%)"></div>
            </div>
            <p style="font-size:.68rem;color:var(--muted);text-align:center;margin-top:6px">Format: QM-XXXX (7 characters)</p>

            <div class="room-preview" id="roomPreview">
                <div class="rp-row">
                    <div class="rp-icon" id="rpIcon">⚔️</div>
                    <div>
                        <div class="rp-title" id="rpTitle"></div>
                        <div class="rp-meta"  id="rpMeta"></div>
                    </div>
                </div>
                <div class="team-sel" id="teamSel"></div>
                <div class="player-chips" id="rpPlayers"></div>
            </div>

            <button class="join-btn" id="normalJoinBtn" onclick="joinByCode()" disabled>⚔️ Join Room</button>
        </div>
    </div>

    {{-- ══════════════════════════════════
         PANEL 2 — Public battles browser
         ══════════════════════════════════ --}}
    <div class="tab-panel" id="panel-public">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
            <div class="pub-title" style="margin-bottom:0">
                🌐 Open Battles
                <span class="live-dot"></span>
            </div>
            <span style="font-size:.72rem;color:var(--muted)" id="pubCount">Loading…</span>
        </div>
        <div id="pubList">
            <div style="text-align:center;padding:30px;color:var(--muted);font-size:.82rem">
                <div class="spinner show" style="margin-bottom:8px"></div>
                <div>Finding public battles…</div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════
         PANEL 3 — Institution battle join
         ══════════════════════════════════ --}}
    <div class="tab-panel" id="panel-inst">
        <div class="jh-card">
            <div class="inst-badge">⚔️ Institution Battle</div>

            <div class="msg err" id="instErrMsg"></div>
            <div class="msg ok"  id="instOkMsg"></div>

            <p class="field-label">Your Student Code</p>
            <div class="code-input-wrap">
                <input type="text" class="code-input" id="instCodeInput"
                    placeholder="e.g. JICR9XHV-STU2"
                    maxlength="20"
                    oninput="onInstCode(this)"
                    onkeydown="if(event.key==='Enter') joinInstitution()"
                    value="{{ $instCode ?? '' }}"
                    style="letter-spacing:2px;font-size:1rem">
                <button class="ci-clear" id="instClearBtn"
                    onclick="clearInst()"
                    style="display:{{ ($instCode ?? '') ? '' : 'none' }}">✕</button>
            </div>

            {{-- Institution battle preview (shown after successful lookup) --}}
            <div id="instPreview" style="display:none">
                <div class="or-div">Battle Details</div>
                <div class="preview-panel">
                    <div class="pp-head">
                        <span class="pp-tag">⚔️ Institution Battle</span>
                        <span class="pp-status-chip" id="instPvStatus">Open</span>
                    </div>
                    <div class="pp-body">
                        <div class="pp-quiz" id="instPvQuiz">—</div>
                        <div class="pp-meta-txt" id="instPvMeta">—</div>
                        <div class="pp-stats">
                            <div class="pp-stat">
                                <div class="pp-stat-val" id="instPvQ">—</div>
                                <div class="pp-stat-lbl">Questions</div>
                            </div>
                            <div class="pp-stat">
                                <div class="pp-stat-val" id="instPvTimer">—</div>
                                <div class="pp-stat-lbl">Per Q</div>
                            </div>
                            <div class="pp-stat">
                                <div class="pp-stat-val" id="instPvPlayers">—</div>
                                <div class="pp-stat-lbl">Players In</div>
                            </div>
                        </div>
                        <div class="pp-school-row">
                            <div style="font-size:1.2rem;flex-shrink:0">🏫</div>
                            <div>
                                <div class="pp-school-name" id="instPvOrg">—</div>
                                <div class="pp-school-lbl">Organising School</div>
                            </div>
                        </div>
                        <div class="pp-your-school" id="instPvYourWrap" style="display:none">
                            <div>🏆</div>
                            <div>
                                <div class="pp-your-label">Your School</div>
                                <div class="pp-your-name" id="instPvYourSchool">—</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button class="join-btn" id="instJoinBtn" onclick="joinInstitution()">⚔️ Join Battle</button>

            <div class="steps-hint">
                <div class="sh-title">What happens next</div>
                <div class="sh-step"><div class="sh-num">1</div><div>Enter the code your teacher gave you</div></div>
                <div class="sh-step"><div class="sh-num">2</div><div>You're added to your school's team in the battle lobby</div></div>
                <div class="sh-step"><div class="sh-num">3</div><div>The host starts the battle — questions appear on screen</div></div>
                <div class="sh-step"><div class="sh-num">4</div><div>Answer fast &amp; correctly to earn points for your school!</div></div>
            </div>
        </div>
    </div>

    {{-- Footer hints --}}
    <div class="hint-box">
        <a href="{{ route('student.quiz.index') }}">← Back to Quiz Generator</a>
        &nbsp;·&nbsp;
        <a href="{{ route('student.battle.history') }}">My Battle History</a>
        &nbsp;·&nbsp;
        <a href="{{ route('student.battle.setup') }}">Create a Battle</a>
    </div>
</div>

<script>
/* ════════════════════════════════════════════════════════
   UNIFIED JOIN PAGE — JavaScript
   ════════════════════════════════════════════════════════ */

const CSRF   = '{{ csrf_token() }}';
const ROUTES = {
    join        : '{{ route('student.battle.join') }}',
    instJoin    : '{{ route('institution.battle.join.post') }}',
    lookup      : '/student/battle/lookup/',
    public      : '/student/battle/public',
    lobby       : code => `/student/battle/lobby/${code}`,
    setup       : '{{ route('student.battle.setup') }}',
};

/* ── Pre-filled invite (normal battle via URL) ── */
@if(isset($room) && $room)
const PF = { code:'{{ $room->code }}', mode:'{{ $room->mode }}', isTeam:{{ $room->mode === 'team' ? 'true' : 'false' }} };
@else
const PF = null;
@endif

@if(isset($preselectedTeam) && $preselectedTeam)
let pfTeamSel = '{{ $preselectedTeam }}';
document.addEventListener('DOMContentLoaded', () => {
    const b = document.getElementById('pfTeam' + ('{{ $preselectedTeam }}' === 'a' ? 'A' : 'B'));
    if (b) b.click();
});
@else
let pfTeamSel = null;
@endif

/* ─────────────────────────────────────────────
   TAB SWITCHER
   ───────────────────────────────────────────── */
function switchTab(tab) {
    ['normal','public','inst'].forEach(t => {
        document.getElementById('panel-'+t).classList.toggle('active', t === tab);
        document.getElementById('tab-'+t).classList.toggle('active',   t === tab);
    });
    if (tab === 'public') loadPublic();
}

/* ─────────────────────────────────────────────
   QUICK JOIN (pre-filled invite card)
   ───────────────────────────────────────────── */
function pfTeam(team, btn) {
    pfTeamSel = team;
    document.querySelectorAll('#pfTeamA,#pfTeamB').forEach(b => b.classList.remove('sel-a','sel-b'));
    btn.classList.add(team === 'a' ? 'sel-a' : 'sel-b');
    const jb = document.getElementById('quickJoinBtn');
    if (jb) jb.disabled = false;
}

async function quickJoin() {
    if (!PF) return;
    const btn = document.getElementById('quickJoinBtn');
    btn.disabled = true; btn.textContent = '⏳ Joining…';
    const res = await fetch(ROUTES.join, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({ code: PF.code, team: pfTeamSel }),
    });
    const d = await res.json();
    if (d.success) window.location.href = d.redirectUrl || ROUTES.lobby(PF.code);
    else { showNormalErr(d.message || 'Failed to join'); btn.disabled = false; btn.textContent = '🚀 Join Battle Now →'; }
}

/* ─────────────────────────────────────────────
   PANEL 1 — NORMAL BATTLE (code entry)
   ───────────────────────────────────────────── */
let codeTimer = null, lookedRoom = null, codeTeam = null;

function onCode(inp) {
    let v = inp.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
    if (v.length === 2 && !v.includes('-')) v += '-';
    if (v.length > 7) v = v.slice(0, 7);
    inp.value = v;
    inp.classList.remove('valid','err');
    hideNormalErr(); lookedRoom = null; codeTeam = null;
    document.getElementById('roomPreview').classList.remove('show');
    document.getElementById('normalJoinBtn').disabled = true;
    if (v.length === 7 && v.includes('-')) {
        clearTimeout(codeTimer);
        codeTimer = setTimeout(lookupCode, 400);
    }
}

async function lookupCode() {
    const code    = document.getElementById('codeInput').value.trim().toUpperCase();
    if (code.length < 5) return;
    const sp      = document.getElementById('normalSpinner');
    const preview = document.getElementById('roomPreview');
    sp.classList.add('show');
    preview.classList.add('show');
    document.getElementById('rpTitle').textContent   = 'Looking up…';
    document.getElementById('rpMeta').textContent    = '';
    document.getElementById('rpPlayers').innerHTML   = '';
    document.getElementById('teamSel').classList.remove('show');
    hideNormalErr();

    try {
        const r = await fetch(ROUTES.lookup + code, {
            headers: { 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN':CSRF },
        });
        const d = await r.json();
        sp.classList.remove('show');

        if (!d.success) {
            showNormalErr(d.message || 'Room not found.');
            document.getElementById('codeInput').classList.add('err');
            preview.classList.remove('show');
            return;
        }

        lookedRoom = d.room;
        document.getElementById('codeInput').classList.add('valid');
        const icons  = { '1v1':'⚔️', group:'👥', team:'🏆' };
        const labels = { '1v1':'1v1', group:'Group', team:'Team vs Team' };
        document.getElementById('rpIcon').textContent  = icons[d.room.mode] || '⚔️';
        document.getElementById('rpTitle').textContent = d.room.quizTitle;
        document.getElementById('rpMeta').innerHTML    =
            `<span class="mode-badge mb-${d.room.mode}">${labels[d.room.mode]}</span>` +
            ` ${d.room.playerCount} players · Host: ${esc(d.room.hostName)}`;

        if (d.room.players && d.room.players.length) {
            document.getElementById('rpPlayers').innerHTML = d.room.players.map(p =>
                `<span class="p-chip">${esc(p.name)}${p.isHost ? ' 👑' : ''}</span>`
            ).join('');
        }

        if (d.room.mode === 'team') {
            const ts = document.getElementById('teamSel');
            ts.classList.add('show');
            ts.innerHTML = `
                <button class="ts-btn" id="ts-a" onclick="selCodeTeam('a',this)">
                    🔵 ${esc(d.room.teamAName)}<br>
                    <span style="font-size:.66rem;color:var(--muted)">${d.room.teamACount}/${d.room.maxPerTeam}</span>
                </button>
                <button class="ts-btn" id="ts-b" onclick="selCodeTeam('b',this)">
                    🟢 ${esc(d.room.teamBName)}<br>
                    <span style="font-size:.66rem;color:var(--muted)">${d.room.teamBCount}/${d.room.maxPerTeam}</span>
                </button>`;
        } else {
            document.getElementById('normalJoinBtn').disabled = false;
        }
    } catch(e) {
        sp.classList.remove('show');
        showNormalErr('Network error. Please try again.');
        preview.classList.remove('show');
    }
}

function selCodeTeam(team, btn) {
    codeTeam = team;
    document.querySelectorAll('#teamSel .ts-btn').forEach(b => b.classList.remove('sel-a','sel-b'));
    btn.classList.add(team === 'a' ? 'sel-a' : 'sel-b');
    document.getElementById('normalJoinBtn').disabled = false;
}

async function joinByCode() {
    const code = document.getElementById('codeInput').value.trim().toUpperCase();
    if (!code) { showNormalErr('Enter a room code'); return; }
    const btn = document.getElementById('normalJoinBtn');
    btn.disabled = true; btn.textContent = '⏳ Joining…';
    try {
        const r = await fetch(ROUTES.join, {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({ code, team: codeTeam }),
        });
        const d = await r.json();
        if (d.success) window.location.href = d.redirectUrl || ROUTES.lobby(code);
        else { showNormalErr(d.message || 'Could not join.'); btn.disabled = false; btn.innerHTML = '⚔️ Join Room'; }
    } catch(e) {
        showNormalErr('Network error.');
        btn.disabled = false; btn.innerHTML = '⚔️ Join Room';
    }
}

function showNormalErr(msg) {
    const b = document.getElementById('normalErrBox');
    document.getElementById('normalErrMsg').textContent = msg;
    b.classList.add('show');
}
function hideNormalErr() { document.getElementById('normalErrBox').classList.remove('show'); }

/* ─────────────────────────────────────────────
   PANEL 2 — PUBLIC BATTLES
   ───────────────────────────────────────────── */
let pubLoaded = false;

async function loadPublic() {
    if (pubLoaded) return; // only fetch once per page load; tab switches reuse data
    try {
        const r    = await fetch(ROUTES.public, { headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':CSRF} });
        const d    = await r.json();
        const list = document.getElementById('pubList');
        const cnt  = document.getElementById('pubCount');

        if (!d.success || !d.rooms || !d.rooms.length) {
            list.innerHTML = `<div style="text-align:center;padding:24px;color:var(--muted);font-size:.8rem">
                No public battles right now.
                <a href="${ROUTES.setup}" style="color:var(--accent);text-decoration:none">Create one!</a>
            </div>`;
            if (cnt) cnt.textContent = 'None open';
            pubLoaded = true;
            return;
        }

        if (cnt) cnt.textContent = `${d.rooms.length} open`;
        const icons      = { '1v1':'⚔️', group:'👥', team:'🏆' };
        const diffColors = { beginner:'#22c55e', intermediate:'orange', advanced:'var(--red)' };

        list.innerHTML = d.rooms.map(room => `
            <div class="pub-card" onclick="window.location.href='${room.joinUrl}'">
                <div class="pub-icon">${icons[room.mode] || '⚔️'}</div>
                <div class="pub-info">
                    <div class="pub-name">${esc(room.quizTitle)}</div>
                    <div class="pub-meta">
                        <span class="mode-badge mb-${room.mode}">${room.mode}</span>
                        ${room.subject     ? `<span>📚 ${esc(room.subject)}</span>` : ''}
                        ${room.classLevel  ? `<span>🎓 Class ${esc(room.classLevel)}</span>` : ''}
                        ${room.difficulty  ? `<span style="color:${diffColors[room.difficulty]||'var(--muted)'}">● ${room.difficulty}</span>` : ''}
                        <span>👥 ${room.playerCount} waiting</span>
                        <span>Host: ${esc(room.hostName)}</span>
                    </div>
                </div>
                <button class="pub-join" onclick="event.stopPropagation();window.location.href='${room.joinUrl}'">Join →</button>
            </div>`).join('');

        pubLoaded = true;
    } catch(e) {
        document.getElementById('pubList').innerHTML =
            '<div style="text-align:center;padding:16px;color:var(--muted);font-size:.78rem">Could not load public battles.</div>';
    }
}

// Refresh public list every 30s regardless of active tab
setInterval(() => { pubLoaded = false; if (document.getElementById('panel-public').classList.contains('active')) loadPublic(); }, 30000);

/* ─────────────────────────────────────────────
   PANEL 3 — INSTITUTION BATTLE
   ───────────────────────────────────────────── */
let instPreviewShown = false;

function onInstCode(el) {
    el.value = el.value.toUpperCase().replace(/[^A-Z0-9\-]/g, '');
    document.getElementById('instClearBtn').style.display = el.value ? '' : 'none';
    el.classList.remove('err','ok');
    hideInstMsg();
    if (instPreviewShown) {
        document.getElementById('instPreview').style.display = 'none';
        instPreviewShown = false;
    }
}

function clearInst() {
    const el = document.getElementById('instCodeInput');
    el.value = '';
    el.classList.remove('err','ok');
    document.getElementById('instClearBtn').style.display = 'none';
    hideInstMsg();
    document.getElementById('instPreview').style.display = 'none';
    instPreviewShown = false;
    el.focus();
}

async function joinInstitution() {
    const code = document.getElementById('instCodeInput').value.trim();
    if (!code) { showInstErr('Please enter your student code'); return; }

    const btn = document.getElementById('instJoinBtn');
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-block;animation:spin .6s linear infinite">⏳</span> Joining…';
    hideInstMsg();

    try {
        const res = await fetch(ROUTES.instJoin, {
            method:'POST',
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':CSRF,
                'X-Requested-With':'XMLHttpRequest',
            },
            body: JSON.stringify({ code }),
        });
        const d = await res.json();

        if (d.success) {
            document.getElementById('instCodeInput').classList.add('ok');
            if (d.battleInfo) renderInstPreview(d.battleInfo);
            showInstOk('✓ Joined! Taking you to the arena…');
            setTimeout(() => window.location.href = d.redirectUrl, 1200);
        } else {
            showInstErr(d.message || 'Could not join. Check your code and try again.');
            document.getElementById('instCodeInput').classList.add('err');
            btn.disabled = false;
            btn.innerHTML = '⚔️ Join Battle';
        }
    } catch(e) {
        showInstErr('Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '⚔️ Join Battle';
    }
}

function renderInstPreview(info) {
    document.getElementById('instPvQuiz').textContent    = info.quizTitle      || '—';
    document.getElementById('instPvMeta').textContent    = info.subject ? `📖 ${info.subject}` : 'General Quiz';
    document.getElementById('instPvQ').textContent       = info.totalQuestions || '—';
    document.getElementById('instPvTimer').textContent   = (info.questionTimer  || '—') + 's';
    document.getElementById('instPvPlayers').textContent = info.playerCount    || '—';
    document.getElementById('instPvOrg').textContent     = info.hostInstitution || '—';
    document.getElementById('instPvStatus').textContent  = 'Open';
    if (info.yourSchool) {
        document.getElementById('instPvYourSchool').textContent = info.yourSchool;
        document.getElementById('instPvYourWrap').style.display = 'flex';
    }
    document.getElementById('instPreview').style.display = 'block';
    instPreviewShown = true;
}

function showInstErr(msg) { const el=document.getElementById('instErrMsg'); el.textContent='⚠️ '+msg; el.classList.add('show'); }
function showInstOk(msg)  { const el=document.getElementById('instOkMsg');  el.textContent=msg;       el.classList.add('show'); }
function hideInstMsg()    { ['instErrMsg','instOkMsg'].forEach(id => document.getElementById(id).classList.remove('show')); }

/* ─────────────────────────────────────────────
   SHARED HELPERS
   ───────────────────────────────────────────── */
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

/* ─────────────────────────────────────────────
   INIT
   ───────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    // Focus first visible code input
    @if(!isset($room))
        document.getElementById('codeInput')?.focus();
    @endif

    // If institution code was pre-filled via URL param → auto switch & auto-join
    @if($instCode ?? '')
        switchTab('inst');
        joinInstitution();
    @endif

    // Eager-load public list in background (tab 2)
    loadPublic();
});
</script>
@endsection