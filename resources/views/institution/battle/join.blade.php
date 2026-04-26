{{-- resources/views/institution/battle/institution-join.blade.php --}}
@extends('institution.layout.master')
@section('page_title', 'Join Battle as Institution')

@section('content')
<style>
    :root {
        --gold: #f5c842; --gold-dim: rgba(245,200,66,.12);
        --green: #00e396; --red: #ff4d6a; --ice: #00d4ff;
    }

    .ij-wrap { max-width: 540px; margin: 0 auto; padding: 56px 20px; }

    /* Hero */
    .ij-hero { text-align: center; margin-bottom: 36px; }
    .ij-icon { font-size: 3.2rem; margin-bottom: 12px; animation: float 3s ease-in-out infinite; display: block; }
    @keyframes float { 0%,100%{ transform:translateY(0) } 50%{ transform:translateY(-9px) } }
    .ij-title { font-family: var(--fh); font-size: 1.8rem; font-weight: 900; margin-bottom: 6px; }
    .ij-sub   { color: var(--muted); font-size: .86rem; line-height: 1.65; }

    /* Card */
    .ij-card { background: var(--card); border: 1.5px solid var(--border); border-radius: var(--radius); padding: 28px 26px; }

    /* Code input */
    .field-label { font-size: .75rem; font-weight: 800; letter-spacing: .6px; color: var(--muted); text-transform: uppercase; margin-bottom: 8px; }
    .code-input-wrap { position: relative; margin-bottom: 14px; }
    .code-input {
        width: 100%; padding: 15px 46px 15px 18px;
        background: rgba(255,255,255,.04); border: 2px solid var(--border);
        border-radius: var(--radius-sm); color: var(--text);
        font-family: var(--fh); font-size: 1.05rem; font-weight: 900;
        letter-spacing: 3px; text-transform: uppercase;
        transition: border-color .2s; outline: none; box-sizing: border-box;
    }
    .code-input::placeholder { color: var(--muted); font-weight: 400; letter-spacing: 1px; font-size: .88rem; font-family: inherit; }
    .code-input:focus { border-color: var(--accent); }
    .code-input.err { border-color: var(--red); }
    .code-input.ok  { border-color: var(--green); }
    .ci-clear { position: absolute; right: 13px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--muted); cursor: pointer; font-size: .85rem; padding: 4px; }

    /* Lookup btn */
    .lookup-btn {
        width: 100%; padding: 14px; border: none; border-radius: var(--radius-sm);
        background: rgba(124,92,252,.12); border: 1.5px solid rgba(124,92,252,.3);
        color: var(--accent); font-family: var(--fh); font-size: .92rem; font-weight: 900;
        cursor: pointer; transition: all .18s; display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .lookup-btn:hover:not(:disabled) { background: rgba(124,92,252,.2); transform: translateY(-1px); }
    .lookup-btn:disabled { opacity: .4; cursor: not-allowed; }

    /* Messages */
    .msg { padding: 11px 14px; border-radius: var(--radius-xs); font-size: .81rem; font-weight: 600; margin-bottom: 14px; display: none; border: 1px solid; }
    .msg.show { display: block; }
    .msg.err  { background: rgba(255,77,106,.07); border-color: rgba(255,77,106,.25); color: var(--red); }
    .msg.info { background: rgba(0,212,255,.06); border-color: rgba(0,212,255,.18); color: var(--ice); }

    /* Divider */
    .divider { display: flex; align-items: center; gap: 10px; margin: 18px 0; }
    .divider-line { flex: 1; height: 1px; background: var(--border); }
    .divider-text { font-size: .68rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .8px; }

    /* ── Battle Preview Panel ── */
    #battlePreview { display: none; }
    #battlePreview.show { display: block; animation: fadeUp .35s ease; }
    @keyframes fadeUp { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }

    .battle-preview-card {
        background: rgba(124,92,252,.06); border: 1.5px solid rgba(124,92,252,.22);
        border-radius: var(--radius-sm); overflow: hidden; margin-bottom: 16px;
    }
    .bpc-header {
        padding: 14px 16px; background: rgba(124,92,252,.1);
        border-bottom: 1px solid rgba(124,92,252,.15);
        display: flex; align-items: center; gap: 10px;
    }
    .bpc-badge {
        background: var(--gradient); padding: 4px 10px; border-radius: 12px;
        font-size: .65rem; font-weight: 900; color: #fff; text-transform: uppercase; letter-spacing: .8px;
    }
    .bpc-status {
        font-size: .7rem; font-weight: 800; padding: 3px 9px; border-radius: 10px;
        background: rgba(0,227,150,.12); border: 1px solid rgba(0,227,150,.25); color: var(--green);
    }
    .bpc-body { padding: 16px; }

    .bpc-quiz-name { font-family: var(--fh); font-size: 1.05rem; font-weight: 900; margin-bottom: 4px; }
    .bpc-quiz-meta { font-size: .74rem; color: var(--muted); margin-bottom: 14px; }

    .bpc-stats { display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; margin-bottom: 14px; }
    .bpc-stat {
        text-align: center; padding: 10px 8px;
        background: rgba(255,255,255,.04); border: 1px solid var(--border); border-radius: 8px;
    }
    .bpc-stat-val { font-family: var(--fh); font-size: 1.1rem; font-weight: 900; color: var(--accent); }
    .bpc-stat-lbl { font-size: .64rem; color: var(--muted); font-weight: 700; margin-top: 2px; text-transform: uppercase; }

    .bpc-host-row { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: rgba(0,227,150,.05); border: 1px solid rgba(0,227,150,.15); border-radius: 8px; margin-bottom: 8px; }
    .bpc-host-icon { font-size: 1.3rem; flex-shrink: 0; }
    .bpc-host-name { font-weight: 800; font-size: .88rem; }
    .bpc-host-lbl  { font-size: .68rem; color: var(--muted); margin-top: 1px; }

    .schools-row { display: flex; gap: 6px; flex-wrap: wrap; }
    .school-chip {
        display: flex; align-items: center; gap: 5px; padding: 5px 10px;
        border-radius: 20px; font-size: .72rem; font-weight: 800;
        border: 1px solid;
    }
    .school-chip.filled { background: rgba(0,227,150,.1); border-color: rgba(0,227,150,.25); color: var(--green); }
    .school-chip.yours  { background: rgba(245,200,66,.1); border-color: rgba(245,200,66,.3); color: var(--gold); }
    .school-chip.empty  { background: rgba(255,255,255,.03); border-color: var(--border); color: var(--muted); }

    .slot-label { font-size: .72rem; font-weight: 700; color: var(--muted); margin-bottom: 6px; }

    /* Join CTA */
    .join-btn {
        width: 100%; padding: 15px; border: none; border-radius: var(--radius-sm);
        background: var(--gradient); color: #fff;
        font-family: var(--fh); font-size: .98rem; font-weight: 900;
        cursor: pointer; transition: all .2s; display: flex; align-items: center; justify-content: center; gap: 10px;
    }
    .join-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 26px rgba(124,92,252,.4); }
    .join-btn:disabled { opacity: .45; cursor: not-allowed; }

    /* ── Success View ── */
    #successView { display: none; }
    #successView.show { display: block; animation: fadeUp .4s ease; }

    .success-hero { text-align: center; padding: 8px 0 18px; }
    .success-icon  { font-size: 3rem; margin-bottom: 8px; }
    .success-title { font-family: var(--fh); font-size: 1.25rem; font-weight: 900; color: var(--green); margin-bottom: 4px; }
    .success-sub   { font-size: .81rem; color: var(--muted); }

    .inst-info-row {
        display: flex; align-items: center; gap: 12px; padding: 13px 15px;
        background: rgba(0,227,150,.05); border: 1px solid rgba(0,227,150,.2);
        border-radius: var(--radius-xs); margin-bottom: 16px;
    }
    .iir-icon { font-size: 1.4rem; flex-shrink: 0; }
    .iir-name { font-weight: 800; font-size: .9rem; }
    .iir-sub  { font-size: .71rem; color: var(--muted); margin-top: 1px; }

    .stu-code-box {
        background: rgba(124,92,252,.08); border: 2px solid rgba(124,92,252,.25);
        border-radius: var(--radius-sm); padding: 20px; text-align: center; margin: 18px 0;
    }
    .scb-label { font-size: .67rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--accent); margin-bottom: 10px; }
    .scb-code  { font-family: var(--fh); font-size: 1.6rem; font-weight: 900; letter-spacing: 4px; color: var(--accent); display: block; margin-bottom: 12px; }
    .scb-actions { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; }
    .scb-copy {
        padding: 8px 20px; border: 2px solid rgba(124,92,252,.3); border-radius: 20px;
        background: rgba(124,92,252,.15); color: var(--accent); font-weight: 800; font-size: .8rem; cursor: pointer; transition: all .15s;
    }
    .scb-copy:hover { background: rgba(124,92,252,.25); }
    .scb-copy.ok { border-color: var(--green); background: rgba(0,227,150,.12); color: var(--green); }
    .scb-share {
        padding: 8px 20px; border: 2px solid rgba(0,212,255,.3); border-radius: 20px;
        background: rgba(0,212,255,.08); color: var(--ice); font-weight: 800; font-size: .8rem; cursor: pointer; transition: all .15s;
    }
    .scb-share:hover { background: rgba(0,212,255,.16); }

    .lobby-btn {
        display: block; width: 100%; margin-top: 12px; padding: 13px;
        border-radius: var(--radius-sm); background: rgba(0,227,150,.12);
        border: 1.5px solid rgba(0,227,150,.3); color: var(--green);
        font-family: var(--fh); font-size: .9rem; font-weight: 900;
        cursor: pointer; text-align: center; text-decoration: none;
        transition: all .18s; box-sizing: border-box;
    }
    .lobby-btn:hover { background: rgba(0,227,150,.2); }

    /* Steps hint */
    .steps-hint { margin-top: 20px; border-top: 1px solid var(--border); padding-top: 18px; }
    .sh-title { font-size: .67rem; font-weight: 800; text-transform: uppercase; letter-spacing: .8px; color: var(--muted); margin-bottom: 10px; }
    .sh-step  { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 9px; font-size: .77rem; color: var(--muted); line-height: 1.5; }
    .sh-num   { width: 20px; height: 20px; border-radius: 50%; background: rgba(124,92,252,.12); border: 1px solid rgba(124,92,252,.22); color: var(--accent); font-size: .67rem; font-weight: 900; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }

    @keyframes spin { to { transform: rotate(360deg) } }
</style>

<div class="ij-wrap">
    <div class="ij-hero">
        <span class="ij-icon">🏫</span>
        <h1 class="ij-title">Join as Institution</h1>
        <p class="ij-sub">
            Enter the institution invite code from the battle host.<br>
            Review the battle details, then join to receive your student code.
        </p>
    </div>

    <div class="ij-card">

        {{-- ── Entry Form ── --}}
        <div id="entryView">
            <div class="msg err"  id="errMsg"></div>
            <div class="msg info" id="infoMsg"></div>

            <div class="field-label">Institution Invite Code</div>
            <div class="code-input-wrap">
                <input type="text" class="code-input" id="codeInput"
                    placeholder="e.g. JICR9XHV-S2X9"
                    maxlength="20"
                    oninput="onCodeInput(this)"
                    onkeydown="if(event.key==='Enter') lookupCode()"
                    value="{{ $code ?? '' }}">
                <button class="ci-clear" id="clearBtn"
                    onclick="clearInput()"
                    style="display:{{ ($code ?? '') ? '' : 'none' }}">✕</button>
            </div>

            <button class="lookup-btn" id="lookupBtn" onclick="lookupCode()">
                🔍 Look Up Battle
            </button>

            {{-- ── Battle Preview (shown after lookup) ── --}}
            <div id="battlePreview">
                <div class="divider">
                    <div class="divider-line"></div>
                    <div class="divider-text">Battle Found</div>
                    <div class="divider-line"></div>
                </div>

                <div class="battle-preview-card">
                    <div class="bpc-header">
                        <span class="bpc-badge">⚔️ Institution Battle</span>
                        <span class="bpc-status" id="pvStatus">Open</span>
                        <span style="font-size:.7rem;color:var(--muted);margin-left:auto" id="pvType">—</span>
                    </div>
                    <div class="bpc-body">
                        <div class="bpc-quiz-name" id="pvQuiz">—</div>
                        <div class="bpc-quiz-meta" id="pvQuizMeta">—</div>

                        <div class="bpc-stats">
                            <div class="bpc-stat">
                                <div class="bpc-stat-val" id="pvQuestions">—</div>
                                <div class="bpc-stat-lbl">Questions</div>
                            </div>
                            <div class="bpc-stat">
                                <div class="bpc-stat-val" id="pvTimer">—</div>
                                <div class="bpc-stat-lbl">Per Q</div>
                            </div>
                            <div class="bpc-stat">
                                <div class="bpc-stat-val" id="pvSchools">—</div>
                                <div class="bpc-stat-lbl">Schools</div>
                            </div>
                        </div>

                        <div class="bpc-host-row">
                            <div class="bpc-host-icon">🏫</div>
                            <div>
                                <div class="bpc-host-name" id="pvHostName">—</div>
                                <div class="bpc-host-lbl">Organising Institution (School 1)</div>
                            </div>
                        </div>

                        <div class="slot-label" id="pvSlotsLabel">Schools registered:</div>
                        <div class="schools-row" id="pvSlotsRow"></div>
                    </div>
                </div>

                <button class="join-btn" id="joinBtn" onclick="joinNow()">
                    🏫 Confirm &amp; Join Battle
                </button>
            </div>

            <div class="steps-hint">
                <div class="sh-title">How it works</div>
                <div class="sh-step"><div class="sh-num">1</div><div>Enter the institution code from the host (e.g. JICR9XHV-S2X9)</div></div>
                <div class="sh-step"><div class="sh-num">2</div><div>Preview the battle info, then confirm to register your institution</div></div>
                <div class="sh-step"><div class="sh-num">3</div><div>You receive a student code — share it with your students</div></div>
                <div class="sh-step"><div class="sh-num">4</div><div>Students join using that code, then the host starts the battle</div></div>
            </div>
        </div>

        {{-- ── Success State ── --}}
        <div id="successView">
            <div class="success-hero">
                <div class="success-icon">🎉</div>
                <div class="success-title">Institution Joined!</div>
                <div class="success-sub">Your institution is now registered in the battle.</div>
            </div>

            <div class="inst-info-row">
                <div class="iir-icon">🏫</div>
                <div>
                    <div class="iir-name" id="instName">—</div>
                    <div class="iir-sub">Successfully joined the battle</div>
                </div>
            </div>

            <div class="stu-code-box">
                <div class="scb-label">🎓 Student Join Code — Share this with your students</div>
                <span class="scb-code" id="stuCode">—</span>
                <div class="scb-actions">
                    <button class="scb-copy" onclick="copyStudentCode()" id="copyBtn">📋 Copy Code</button>
                    <button class="scb-share" onclick="shareStudentCode()">📤 Share</button>
                </div>
            </div>

            <a id="lobbyLink" href="#" class="lobby-btn">
                🏟️ Go to Battle Lobby →
            </a>
        </div>

    </div>
</div>

<script>
const LOOKUP_URL    = '{{ route('institution.battle.lookup') }}';
const INST_JOIN_URL = '{{ route('institution.battle.inst.join') }}';
const CSRF          = '{{ csrf_token() }}';
const myInstName    = '{{ addslashes(optional(Auth::user()->institution)->name ?? '') }}';
let   stuCodeVal    = '';
let   lookedUpCode  = '';

/* ── Input helpers ── */
function onCodeInput(el) {
    el.value = el.value.toUpperCase().replace(/[^A-Z0-9\-]/g, '');
    document.getElementById('clearBtn').style.display = el.value ? '' : 'none';
    el.classList.remove('err', 'ok');
    hideMsg();
    hideBattlePreview();
}

function clearInput() {
    const el = document.getElementById('codeInput');
    el.value = '';
    el.classList.remove('err', 'ok');
    document.getElementById('clearBtn').style.display = 'none';
    hideMsg();
    hideBattlePreview();
    el.focus();
}

function hideBattlePreview() {
    document.getElementById('battlePreview').classList.remove('show');
}

/* ── Step 1: Look up the battle ── */
async function lookupCode() {
    const code = document.getElementById('codeInput').value.trim();
    if (!code) { showErr('Please enter a code'); return; }

    if (!code.includes('-')) {
        showErr('Institution codes contain a dash, e.g. JICR9XHV-S2X9. Students use a different code.');
        document.getElementById('codeInput').classList.add('err');
        return;
    }

    const btn = document.getElementById('lookupBtn');
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-block;animation:spin .6s linear infinite">⏳</span> Looking up…';
    hideMsg();

    try {
        const res = await fetch(LOOKUP_URL, {
            method: 'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     CSRF,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ code }),
        });

        const d = await res.json();

        if (d.success) {
            lookedUpCode = code;
            renderBattlePreview(d);
            document.getElementById('codeInput').classList.add('ok');
            document.getElementById('battlePreview').classList.add('show');
        } else {
            showErr(d.message || 'Battle not found. Check the code and try again.');
            document.getElementById('codeInput').classList.add('err');
        }
    } catch (e) {
        showErr('Network error. Please try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '🔍 Look Up Battle';
    }
}

function renderBattlePreview(d) {
    document.getElementById('pvQuiz').textContent      = d.quizTitle     || '—';
    document.getElementById('pvQuizMeta').textContent  = d.subject ? `📖 ${d.subject}` : 'General Quiz';
    document.getElementById('pvQuestions').textContent = d.totalQuestions || '—';
    document.getElementById('pvTimer').textContent     = (d.questionTimer || '—') + 's';
    document.getElementById('pvSchools').textContent   = d.battleType === '3school' ? '3' : '2';
    document.getElementById('pvType').textContent      = d.battleType === '3school' ? '3-School Showdown' : '2-School Head-to-Head';
    document.getElementById('pvHostName').textContent  = d.hostInstitution || 'Host School';

    // Status chip
    const statusEl = document.getElementById('pvStatus');
    statusEl.textContent  = d.status === 'setup' ? 'Open for Registration' : d.status;
    statusEl.style.color  = '#00e396';

    // Slots
    const slotsRow   = document.getElementById('pvSlotsRow');
    const totalSlots = d.battleType === '3school' ? 3 : 2;
    slotsRow.innerHTML = '';

    for (let i = 1; i <= totalSlots; i++) {
        const chip = document.createElement('div');
        const name = d.slots && d.slots[i] ? d.slots[i] : null;
        const isHost = i === 1;

        if (isHost) {
            chip.className = 'school-chip filled';
            chip.innerHTML = `🏫 School 1 — ${name || d.hostInstitution || 'Host'}`;
        } else if (name) {
            chip.className = 'school-chip filled';
            chip.innerHTML = `🏫 School ${i} — ${name}`;
        } else {
            chip.className = 'school-chip empty';
            chip.innerHTML = `🏫 School ${i} — Open`;
        }
        slotsRow.appendChild(chip);
    }

    document.getElementById('pvSlotsLabel').textContent =
        `Schools registered (${Object.keys(d.slots || {}).length}/${totalSlots}):`;
}

/* ── Step 2: Confirm and join ── */
async function joinNow() {
    const code = lookedUpCode || document.getElementById('codeInput').value.trim();
    if (!code) { showErr('Please look up a code first'); return; }

    const btn = document.getElementById('joinBtn');
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-block;animation:spin .6s linear infinite">⏳</span> Joining…';

    try {
        const res = await fetch(INST_JOIN_URL, {
            method: 'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     CSRF,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ code }),
        });

        const d = await res.json();

        if (d.success) {
            stuCodeVal = d.studentCode;
            document.getElementById('instName').textContent = d.institutionName || myInstName;
            document.getElementById('stuCode').textContent  = d.studentCode;
            document.getElementById('lobbyLink').href       = d.lobbyUrl || '#';
            document.getElementById('entryView').style.display = 'none';
            document.getElementById('successView').classList.add('show');
        } else {
            showErr(d.message || 'Failed to join. Check the code and try again.');
            btn.disabled = false;
            btn.innerHTML = '🏫 Confirm & Join Battle';
        }
    } catch (e) {
        showErr('Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '🏫 Confirm & Join Battle';
    }
}

/* ── Copy / Share ── */
function copyStudentCode() {
    navigator.clipboard.writeText(stuCodeVal).then(() => {
        const btn = document.getElementById('copyBtn');
        btn.textContent = '✓ Copied!';
        btn.classList.add('ok');
        setTimeout(() => { btn.textContent = '📋 Copy Code'; btn.classList.remove('ok'); }, 2500);
    });
}

function shareStudentCode() {
    if (navigator.share) {
        navigator.share({
            title: 'Battle Student Code',
            text:  `Join the battle! Your student code: ${stuCodeVal}`,
            url:   window.location.origin + '/institution/battle/join/' + stuCodeVal,
        });
    } else {
        copyStudentCode();
    }
}

/* ── Helpers ── */
function showErr(msg) {
    const el = document.getElementById('errMsg');
    el.textContent = '⚠️ ' + msg;
    el.classList.add('show');
}
function hideMsg() {
    document.getElementById('errMsg').classList.remove('show');
    document.getElementById('infoMsg').classList.remove('show');
}

// Auto-lookup if code pre-filled from query param
@if($code ?? '')
document.addEventListener('DOMContentLoaded', () => lookupCode());
@endif
</script>
@endsection