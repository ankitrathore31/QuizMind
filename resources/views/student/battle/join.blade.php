{{-- resources/views/student/battle/join.blade.php --}}
@extends('student.layout.master')
@section('title', 'Join Battle')

@section('content')
<style>
.join-wrap  { max-width:600px;margin:0 auto;padding:40px 16px; }
.join-hero  { text-align:center;margin-bottom:28px; }
.join-hero-icon { font-size:3.5rem;display:block;margin-bottom:12px;animation:floatIcon 3s ease-in-out infinite; }
@keyframes floatIcon{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.join-hero h1 { font-family:var(--fh);font-size:1.8rem;font-weight:900;margin-bottom:6px;
    background:var(--gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent; }

/* Code card */
.code-card  { background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:24px;margin-bottom:14px; }
.code-input {
    width:100%;padding:14px 18px;font-family:var(--fh);font-size:1.5rem;font-weight:900;
    letter-spacing:.15em;text-align:center;text-transform:uppercase;
    background:rgba(124,92,252,.04);border:2px solid var(--border);border-radius:var(--radius-sm);
    color:var(--text);outline:none;transition:.2s;caret-color:var(--accent);
}
.code-input:focus  { border-color:var(--accent);box-shadow:0 0 0 3px rgba(124,92,252,.1); }
.code-input.valid  { border-color:rgba(0,227,150,.6);background:rgba(0,227,150,.04); }
.code-input.err    { border-color:rgba(255,77,106,.5);animation:shake .3s ease; }
@keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-6px)}75%{transform:translateX(6px)}}

.room-preview { display:none;margin-top:16px;padding:14px 16px;
    background:linear-gradient(135deg,rgba(0,212,255,.06),rgba(124,92,252,.05));
    border:1px solid rgba(0,212,255,.2);border-radius:var(--radius-sm);animation:fadeUp .3s ease; }
.room-preview.show { display:block; }
@keyframes fadeUp{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}
.rp-row  { display:flex;align-items:center;gap:10px;margin-bottom:6px; }
.rp-icon { font-size:1.4rem;flex-shrink:0; }
.rp-title{ font-weight:700;font-size:.88rem; }
.rp-meta { font-size:.74rem;color:var(--muted);display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-top:2px; }
.player-chips { display:flex;gap:5px;flex-wrap:wrap;margin-top:8px; }
.p-chip { padding:2px 8px;border-radius:20px;font-size:.7rem;font-weight:600;
    background:rgba(255,255,255,.06);border:1px solid var(--border);color:var(--muted); }

.team-sel { display:none;gap:10px;margin-top:12px; }
.team-sel.show { display:flex; }
.ts-btn { flex:1;padding:12px;border-radius:var(--radius-sm);border:1.5px solid var(--border);
    background:transparent;cursor:pointer;text-align:center;transition:.2s;font-weight:700;font-size:.84rem;color:var(--text); }
.ts-btn:hover { border-color:var(--accent);background:rgba(124,92,252,.06); }
.ts-btn.sel-a { border-color:var(--accent);background:rgba(124,92,252,.1);color:var(--accent); }
.ts-btn.sel-b { border-color:#00e396;background:rgba(0,227,150,.08);color:#00e396; }

.join-btn { width:100%;padding:14px;background:var(--gradient);color:#fff;border:none;
    border-radius:var(--radius-sm);font-family:var(--fh);font-weight:800;font-size:1rem;
    cursor:pointer;transition:.2s;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:14px; }
.join-btn:hover:not(:disabled) { opacity:.9;transform:translateY(-1px); }
.join-btn:disabled { opacity:.4;cursor:not-allowed;transform:none; }

.err-box { display:none;background:rgba(255,77,106,.08);border:1px solid rgba(255,77,106,.25);
    border-radius:var(--radius-sm);padding:10px 14px;color:var(--red);font-size:.82rem;
    font-weight:600;margin-bottom:12px;align-items:center;gap:8px; }
.err-box.show { display:flex; }

.spinner { width:16px;height:16px;border:2px solid rgba(124,92,252,.2);border-top-color:var(--accent);
    border-radius:50%;animation:spin .7s linear infinite;display:none; }
.spinner.show { display:inline-block; }
@keyframes spin{to{transform:rotate(360deg)}}

/* Divider */
.or-div { display:flex;align-items:center;gap:12px;color:var(--muted);font-size:.75rem;margin:18px 0; }
.or-div::before,.or-div::after { content:'';flex:1;height:1px;background:var(--border); }

/* Public battles */
.pub-section { margin-bottom:14px; }
.pub-title { font-family:var(--fh);font-weight:800;font-size:.88rem;margin-bottom:12px;
    display:flex;align-items:center;gap:8px; }
.pub-card {
    background:var(--card);border:1px solid var(--border);border-radius:var(--radius-sm);
    padding:14px 16px;margin-bottom:10px;display:flex;align-items:center;gap:12px;
    transition:.2s;cursor:pointer;
}
.pub-card:hover { border-color:rgba(124,92,252,.35);transform:translateY(-1px); }
.pub-icon { font-size:1.5rem;flex-shrink:0; }
.pub-info { flex:1;min-width:0; }
.pub-name { font-weight:700;font-size:.86rem;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.pub-meta { font-size:.72rem;color:var(--muted);display:flex;gap:8px;flex-wrap:wrap; }
.pub-join { background:rgba(124,92,252,.1);border:1px solid rgba(124,92,252,.25);color:var(--accent);
    padding:6px 14px;border-radius:8px;font-size:.76rem;font-weight:700;cursor:pointer;white-space:nowrap;transition:.2s; }
.pub-join:hover { background:rgba(124,92,252,.2); }

.mode-badge { display:inline-flex;align-items:center;padding:2px 7px;border-radius:12px;
    font-size:.66rem;font-weight:700;border:1px solid; }
.mb-1v1   { background:rgba(124,92,252,.1);color:var(--accent);border-color:rgba(124,92,252,.3); }
.mb-group { background:rgba(0,212,255,.08);color:#00d4ff;border-color:rgba(0,212,255,.25); }
.mb-team  { background:rgba(255,165,0,.1);color:orange;border-color:rgba(255,165,0,.3); }

.live-dot { display:inline-block;width:7px;height:7px;border-radius:50%;background:#22c55e;
    margin-right:4px;animation:pdot 1.5s infinite; }
@keyframes pdot{0%,100%{opacity:1}50%{opacity:.3}}

.hint-box { text-align:center;color:var(--muted);font-size:.76rem;padding:14px; }
.hint-box a { color:var(--accent);text-decoration:none; }

/* Pre-filled card (from invite link) */
.prefilled-card {
    background:linear-gradient(135deg,rgba(0,227,150,.06),rgba(0,212,255,.04));
    border:1px solid rgba(0,227,150,.2);border-radius:var(--radius);padding:20px;margin-bottom:16px;
    animation:fadeUp .4s ease;
}
</style>

<div class="join-wrap anim-fade">
    <div class="join-hero">
        <span class="join-hero-icon">⚔️</span>
        <h1>Join Battle Room</h1>
        <p class="text-muted" style="font-size:.86rem">Enter a code or pick a public battle below</p>
    </div>

    {{-- Pre-filled from invite link --}}
    @if(isset($room) && $room)
        <div class="prefilled-card">
            <div style="font-family:var(--fh);font-weight:800;font-size:.9rem;margin-bottom:12px;display:flex;align-items:center;gap:8px">
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

            @if($room->participants->count()>0)
            <div class="player-chips">
                @foreach($room->participants->take(8) as $p)
                <span class="p-chip">{{ substr($p->user->name,0,10) }}{{ $p->user_id===$room->host_id?' 👑':'' }}</span>
                @endforeach
                @if($room->participants->count()>8)
                <span class="p-chip">+{{ $room->participants->count()-8 }} more</span>
                @endif
            </div>
            @endif

            @if($room->mode==='team' && !isset($preselectedTeam))
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
            <a href="{{ route('student.battle.lobby', $room->code) }}" class="join-btn" style="text-decoration:none;margin-top:10px">🚀 Go to Lobby →</a>
            @else
            <button class="join-btn" id="quickJoinBtn" onclick="quickJoin()"
                style="margin-top:12px" {{ ($room->mode==='team' && !isset($preselectedTeam))?'disabled':'' }}>
                🚀 Join Battle Now →
            </button>
            @endif
        </div>
        <div class="or-div">or enter a different code</div>
    @endif

    {{-- Show joinError if any --}}
    @if(isset($joinError) && $joinError)
    <div class="err-box show" style="margin-bottom:14px">⚠️ {{ $joinError }}</div>
    @endif

    {{-- Manual code entry --}}
    <div class="code-card">
        <p class="form-label mb-12" style="font-size:.82rem;font-weight:700">🔑 Enter Room Code</p>
        <div class="err-box" id="errBox">⚠️ <span id="errMsg"></span></div>
        <div style="position:relative">
            <input type="text" id="codeInput" class="code-input" placeholder="QM-XXXX"
                maxlength="7" autocomplete="off" spellcheck="false"
                oninput="onCode(this)" onkeydown="if(event.key==='Enter')lookupCode()">
            <div class="spinner" id="spinner" style="position:absolute;right:14px;top:50%;transform:translateY(-50%)"></div>
        </div>
        <p style="font-size:.68rem;color:var(--muted);text-align:center;margin-top:6px">Format: QM-XXXX (7 characters)</p>

        <div class="room-preview" id="roomPreview">
            <div class="rp-row">
                <div class="rp-icon" id="rpIcon">⚔️</div>
                <div>
                    <div class="rp-title" id="rpTitle"></div>
                    <div class="rp-meta" id="rpMeta"></div>
                </div>
            </div>
            <div class="team-sel" id="teamSel"></div>
            <div class="player-chips" id="rpPlayers"></div>
        </div>

        <button class="join-btn" id="joinBtn" onclick="joinByCode()" disabled>⚔️ Join Room</button>
    </div>

    {{-- Public battles --}}
    <div class="or-div">or join a public battle</div>

    <div class="pub-section" id="pubSection">
        <div class="pub-title">
            🌐 Open Battles
            <span class="live-dot"></span>
            <span style="font-size:.7rem;color:var(--muted);font-weight:400" id="pubCount">Loading…</span>
        </div>
        <div id="pubList">
            <div style="text-align:center;padding:30px;color:var(--muted);font-size:.82rem">
                <div class="spinner show" style="display:inline-block;margin-bottom:8px"></div>
                <div>Finding public battles…</div>
            </div>
        </div>
    </div>

    <div class="hint-box">
        <a href="{{ route('student.quiz.index') }}">← Back to Quiz Generator</a>
        &nbsp;·&nbsp;
        <a href="{{ route('student.battle.history') }}">My Battle History</a>
    </div>
</div>

<script>
const CSRF = '{{ csrf_token() }}';
const ROUTES = {
    join   : '{{ route('student.battle.join') }}',
    lookup : '/student/battle/lookup/',
    public : '/student/battle/public',
    lobby  : code => `/student/battle/lobby/${code}`,
};

// Pre-filled from URL
@if(isset($room) && $room)
const PF = { code:'{{ $room->code }}', mode:'{{ $room->mode }}', isTeam:{{ $room->mode==='team'?'true':'false' }} };
@else
const PF = null;
@endif
@if(isset($preselectedTeam) && $preselectedTeam)
let pfTeamSel = '{{ $preselectedTeam }}';
document.addEventListener('DOMContentLoaded',()=>{ const b=document.getElementById('pfTeam'+('{{ $preselectedTeam }}'==='a'?'A':'B')); if(b) b.click(); });
@else
let pfTeamSel = null;
@endif

function pfTeam(team, btn) {
    pfTeamSel = team;
    document.querySelectorAll('#pfTeamA,#pfTeamB').forEach(b=>b.classList.remove('sel-a','sel-b'));
    btn.classList.add(team==='a'?'sel-a':'sel-b');
    const jb = document.getElementById('quickJoinBtn');
    if(jb) jb.disabled = false;
}

async function quickJoin() {
    if(!PF) return;
    const btn=document.getElementById('quickJoinBtn');
    btn.disabled=true; btn.textContent='⏳ Joining…';
    const res = await fetch(ROUTES.join,{
        method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'},
        body:JSON.stringify({code:PF.code,team:pfTeamSel}),
    });
    const d = await res.json();
    if(d.success) window.location.href=d.redirectUrl||ROUTES.lobby(PF.code);
    else { showErr(d.message||'Failed'); btn.disabled=false; btn.textContent='🚀 Join Battle Now →'; }
}

// ── Manual code entry ─────────────────────────────────
let codeTimer=null, lookedRoom=null, codeTeam=null;

function onCode(inp) {
    let v = inp.value.toUpperCase().replace(/[^A-Z0-9-]/g,'');
    if(v.length===2 && !v.includes('-')) v+='-';
    if(v.length>7) v=v.slice(0,7);
    inp.value=v;
    inp.classList.remove('valid','err');
    hideErr(); lookedRoom=null; codeTeam=null;
    document.getElementById('roomPreview').classList.remove('show');
    document.getElementById('joinBtn').disabled=true;
    if(v.length===7 && v.includes('-')){ clearTimeout(codeTimer); codeTimer=setTimeout(lookupCode,400); }
}

async function lookupCode() {
    const code=document.getElementById('codeInput').value.trim().toUpperCase();
    if(code.length<5) return;
    const sp=document.getElementById('spinner'); sp.classList.add('show');
    const preview=document.getElementById('roomPreview');
    preview.classList.add('show');
    document.getElementById('rpTitle').textContent='Looking up…';
    document.getElementById('rpMeta').textContent='';
    document.getElementById('rpPlayers').innerHTML='';
    document.getElementById('teamSel').classList.remove('show');
    hideErr();

    try{
        const r=await fetch(ROUTES.lookup+code,{headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':CSRF}});
        const d=await r.json();
        sp.classList.remove('show');

        if(!d.success){ showErr(d.message||'Room not found.'); document.getElementById('codeInput').classList.add('err'); preview.classList.remove('show'); return; }

        lookedRoom=d.room;
        document.getElementById('codeInput').classList.add('valid');
        const icons={'1v1':'⚔️',group:'👥',team:'🏆'};
        const labels={'1v1':'1v1','group':'Group','team':'Team vs Team'};
        document.getElementById('rpIcon').textContent=icons[d.room.mode]||'⚔️';
        document.getElementById('rpTitle').textContent=d.room.quizTitle;
        document.getElementById('rpMeta').innerHTML=
            `<span class="mode-badge mb-${d.room.mode}">${labels[d.room.mode]}</span>`+
            ` ${d.room.playerCount} players · Host: ${esc(d.room.hostName)}`;

        if(d.room.players&&d.room.players.length){
            document.getElementById('rpPlayers').innerHTML=d.room.players.map(p=>
                `<span class="p-chip">${esc(p.name)}${p.isHost?' 👑':''}</span>`).join('');
        }

        if(d.room.mode==='team'){
            const ts=document.getElementById('teamSel'); ts.classList.add('show');
            ts.innerHTML=`
                <button class="ts-btn" id="ts-a" onclick="selCodeTeam('a',this)">🔵 ${esc(d.room.teamAName)}<br><span style="font-size:.66rem;color:var(--muted)">${d.room.teamACount}/${d.room.maxPerTeam}</span></button>
                <button class="ts-btn" id="ts-b" onclick="selCodeTeam('b',this)">🟢 ${esc(d.room.teamBName)}<br><span style="font-size:.66rem;color:var(--muted)">${d.room.teamBCount}/${d.room.maxPerTeam}</span></button>`;
        } else {
            document.getElementById('joinBtn').disabled=false;
        }
    }catch(e){ sp.classList.remove('show'); showErr('Network error.'); preview.classList.remove('show'); }
}

function selCodeTeam(team,btn){
    codeTeam=team;
    document.querySelectorAll('#teamSel .ts-btn').forEach(b=>b.classList.remove('sel-a','sel-b'));
    btn.classList.add(team==='a'?'sel-a':'sel-b');
    document.getElementById('joinBtn').disabled=false;
}

async function joinByCode(){
    const code=document.getElementById('codeInput').value.trim().toUpperCase();
    if(!code){ showErr('Enter a room code'); return; }
    const btn=document.getElementById('joinBtn');
    btn.disabled=true; btn.textContent='⏳ Joining…';
    try{
        const r=await fetch(ROUTES.join,{
            method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'},
            body:JSON.stringify({code,team:codeTeam}),
        });
        const d=await r.json();
        if(d.success) window.location.href=d.redirectUrl||ROUTES.lobby(code);
        else{ showErr(d.message||'Could not join.'); btn.disabled=false; btn.textContent='⚔️ Join Room'; }
    }catch(e){ showErr('Network error.'); btn.disabled=false; btn.textContent='⚔️ Join Room'; }
}

// ── Public battles ────────────────────────────────────
async function loadPublic(){
    try{
        const r=await fetch(ROUTES.public,{headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':CSRF}});
        const d=await r.json();
        const list=document.getElementById('pubList');
        const cnt=document.getElementById('pubCount');

        if(!d.success||!d.rooms||!d.rooms.length){
            list.innerHTML='<div style="text-align:center;padding:24px;color:var(--muted);font-size:.8rem">No public battles right now. <a href="'+`{{ route('student.battle.setup') }}`+'" style="color:var(--accent);text-decoration:none">Create one!</a></div>';
            if(cnt) cnt.textContent='None open';
            return;
        }

        if(cnt) cnt.textContent=`${d.rooms.length} open`;
        const icons={'1v1':'⚔️',group:'👥',team:'🏆'};
        const diffColors={beginner:'#22c55e',intermediate:'orange',advanced:'var(--red)'};

        list.innerHTML=d.rooms.map(room=>`
            <div class="pub-card" onclick="window.location.href='${room.joinUrl}'">
                <div class="pub-icon">${icons[room.mode]||'⚔️'}</div>
                <div class="pub-info">
                    <div class="pub-name">${esc(room.quizTitle)}</div>
                    <div class="pub-meta">
                        <span class="mode-badge mb-${room.mode}">${room.mode}</span>
                        ${room.subject?`<span>📚 ${esc(room.subject)}</span>`:''}
                        ${room.classLevel?`<span>🎓 Class ${esc(room.classLevel)}</span>`:''}
                        ${room.difficulty?`<span style="color:${diffColors[room.difficulty]||'var(--muted)'}">● ${room.difficulty}</span>`:''}
                        <span>👥 ${room.playerCount} waiting</span>
                        <span>Host: ${esc(room.hostName)}</span>
                    </div>
                </div>
                <button class="pub-join" onclick="event.stopPropagation();window.location.href='${room.joinUrl}'">Join →</button>
            </div>`).join('');
    }catch(e){
        document.getElementById('pubList').innerHTML='<div style="text-align:center;padding:16px;color:var(--muted);font-size:.78rem">Could not load public battles.</div>';
    }
}

// ── Helpers ───────────────────────────────────────────
function showErr(msg){ const b=document.getElementById('errBox'); document.getElementById('errMsg').textContent=msg; b.classList.add('show'); }
function hideErr(){ document.getElementById('errBox').classList.remove('show'); }
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

document.addEventListener('DOMContentLoaded',()=>{
    @if(!isset($room))
        document.getElementById('codeInput')?.focus();
    @endif
    loadPublic();
    // Refresh public battles every 30s
    setInterval(loadPublic, 30000);
});
</script>
@endsection