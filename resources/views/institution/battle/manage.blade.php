{{-- resources/views/institution/battle/manage.blade.php --}}
@extends('institution.layout.master')
@section('content')
<style>
:root{--bg:#0a0a0f;--card:#13131e;--card2:#1a1a28;--border:rgba(255,255,255,.07);--border2:rgba(255,255,255,.04);--text:#f0f0f8;--muted:#7070a0;--accent:#7c5cfc;--green:#00e396;--red:#ff4d6a;--orange:#ff9500;--gradient:linear-gradient(135deg,#7c5cfc,#00d4ff);--radius:14px;--rsm:9px;--fh:'Syne',sans-serif;--fb:'DM Sans',sans-serif}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:var(--fb)}
.manage-layout{display:grid;grid-template-columns:1fr 340px;min-height:100vh;gap:0}
.manage-main{padding:28px;overflow-y:auto}
.manage-side{border-left:1px solid var(--border);background:rgba(0,0,0,.2);display:flex;flex-direction:column;overflow:hidden;position:sticky;top:0;height:100vh}
.pg-title{font-family:var(--fh);font-size:1.6rem;font-weight:900;margin-bottom:4px}
.pg-sub{font-size:.8rem;color:var(--muted);margin-bottom:24px}
.card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:20px;margin-bottom:16px}
.card-hd{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.card-title{font-family:var(--fh);font-weight:800;font-size:.88rem}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 15px;border-radius:8px;border:none;font-family:var(--fb);font-weight:700;font-size:.81rem;cursor:pointer;transition:.18s;text-decoration:none}
.btn-grad{background:var(--gradient);color:#fff}.btn-grad:hover{opacity:.9;transform:translateY(-1px)}
.btn-ghost{background:transparent;color:var(--text);border:1.5px solid var(--border)}.btn-ghost:hover{border-color:rgba(124,92,252,.35)}
.btn-danger{background:rgba(255,77,106,.1);border:1px solid rgba(255,77,106,.25);color:var(--red)}.btn-danger:hover{background:rgba(255,77,106,.2)}
.btn-sm{padding:6px 11px;font-size:.74rem}.btn-full{width:100%;justify-content:center}
.btn:disabled{opacity:.45;cursor:not-allowed}
.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;font-size:.67rem;font-weight:700;border:1px solid}
.badge-g{background:rgba(0,227,150,.08);color:var(--green);border-color:rgba(0,227,150,.2)}
.badge-o{background:rgba(255,149,0,.08);color:var(--orange);border-color:rgba(255,149,0,.2)}
.badge-r{background:rgba(255,77,106,.08);color:var(--red);border-color:rgba(255,77,106,.2)}
.badge-p{background:rgba(124,92,252,.1);color:var(--accent);border-color:rgba(124,92,252,.22)}
/* Status bar */
.status-bar{display:flex;align-items:center;gap:12px;padding:14px 18px;background:var(--card);border:1px solid var(--border);border-radius:var(--radius);margin-bottom:20px;flex-wrap:wrap}
.status-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.status-dot.waiting{background:var(--orange);box-shadow:0 0 8px rgba(255,149,0,.5);animation:pulse 2s infinite}
.status-dot.in_progress{background:var(--green);box-shadow:0 0 8px rgba(0,227,150,.5);animation:pulse 1.5s infinite}
.status-dot.finished{background:var(--muted)}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
/* Institution code cards */
.inst-code-card{background:var(--card2);border:1px solid var(--border);border-radius:10px;padding:14px;display:flex;align-items:center;gap:12px;margin-bottom:8px}
.ic-color{width:4px;align-self:stretch;border-radius:4px;flex-shrink:0}
.ic-name{font-weight:700;font-size:.84rem;margin-bottom:3px}
.ic-code{font-family:monospace;font-size:1rem;font-weight:900;letter-spacing:.06em}
.ic-count{font-size:.7rem;color:var(--muted);margin-top:2px}
/* Leaderboard sidebar */
.side-hd{padding:14px 16px;border-bottom:1px solid var(--border);font-family:var(--fh);font-weight:800;font-size:.82rem;flex-shrink:0;display:flex;align-items:center;justify-content:space-between}
.lb-institution{padding:10px 14px;border-bottom:1px solid var(--border2)}
.lb-inst-name{font-family:var(--fh);font-weight:800;font-size:.84rem;margin-bottom:6px;display:flex;align-items:center;gap:8px}
.lb-inst-score{font-family:var(--fh);font-size:1.3rem;font-weight:900;margin-bottom:4px}
.lb-inst-bar{height:4px;border-radius:2px;background:rgba(255,255,255,.07);overflow:hidden;margin-bottom:8px}
.lb-inst-fill{height:100%;border-radius:2px;transition:width .8s ease}
.lb-student{display:flex;align-items:center;gap:7px;padding:5px 0;font-size:.75rem}
.lb-av{width:22px;height:22px;border-radius:50%;background:var(--gradient);display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:800;color:#fff;flex-shrink:0}
.lb-name{flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lb-pts{font-family:var(--fh);font-weight:800;font-size:.78rem;flex-shrink:0}
/* Live ticker */
.live-ticker{background:rgba(0,227,150,.05);border:1px solid rgba(0,227,150,.15);border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:.78rem;color:var(--green);display:flex;align-items:center;gap:8px}
.live-dot{width:8px;height:8px;border-radius:50%;background:var(--green);animation:pulse 1s infinite;flex-shrink:0}
/* Controls grid */
.controls-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
.ctrl-card{background:var(--card);border:1px solid var(--border);border-radius:var(--rsm);padding:14px;text-align:center}
.ctrl-val{font-family:var(--fh);font-size:1.4rem;font-weight:900;margin-bottom:3px}
.ctrl-lbl{font-size:.66rem;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.4px}
/* Countdown display */
#countdownDisplay{display:none;text-align:center;padding:40px;background:linear-gradient(135deg,rgba(124,92,252,.08),rgba(0,212,255,.05));border:1px solid rgba(124,92,252,.2);border-radius:var(--radius);margin-bottom:16px}
.cd-num{font-family:var(--fh);font-size:5rem;font-weight:900;background:var(--gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;line-height:1}
.cd-lbl{font-size:.9rem;color:var(--muted);margin-top:8px}
/* Winner reveal */
#winnerBox{display:none;background:linear-gradient(135deg,rgba(255,184,0,.08),rgba(255,107,157,.06));border:1px solid rgba(255,184,0,.2);border-radius:var(--radius);padding:28px;text-align:center;margin-bottom:16px}
@media(max-width:900px){.manage-layout{grid-template-columns:1fr}.manage-side{position:relative;height:auto}}
</style>

<div class="manage-layout">
  <div class="manage-main">
    <div class="pg-title">🏫 Manage Institution Battle</div>
    <div class="pg-sub">Room: <strong style="color:var(--accent);font-family:monospace">{{ $room->code }}</strong> · {{ $room->mode }} · {{ $room->institutionParticipants->count() }} institutions</div>

    {{-- Status Bar --}}
    <div class="status-bar">
      <div class="status-dot {{ $room->status }}"></div>
      <span style="font-family:var(--fh);font-weight:700;font-size:.88rem">
        {{ $room->status === 'waiting' ? 'Waiting for students to join' : ($room->status === 'in_progress' ? 'Battle in Progress' : 'Battle Finished') }}
      </span>
      <span class="badge {{ $room->status==='waiting'?'badge-o':($room->status==='in_progress'?'badge-g':'badge-r') }}" style="margin-left:auto">{{ ucfirst(str_replace('_',' ',$room->status)) }}</span>
      @if($room->status === 'in_progress')
      <div class="live-ticker" style="margin:0">
        <div class="live-dot"></div>
        <span>LIVE — Question <span id="liveQNum">{{ $room->current_question ?? 1 }}</span>/{{ $room->total_questions }}</span>
      </div>
      @endif
    </div>

    {{-- Countdown --}}
    <div id="countdownDisplay">
      <div class="cd-num" id="cdNum">3</div>
      <div class="cd-lbl">Battle starting…</div>
    </div>

    {{-- Winner Box --}}
    <div id="winnerBox">
      <div style="font-size:3rem;margin-bottom:12px">🏆</div>
      <div style="font-family:var(--fh);font-size:1.5rem;font-weight:800;margin-bottom:6px" id="winnerText">—</div>
      <p style="color:var(--muted);font-size:.84rem">Final Results</p>
    </div>

    {{-- Stats --}}
    <div class="controls-grid">
      <div class="ctrl-card"><div class="ctrl-val" style="color:var(--accent)" id="totalStudents">{{ $room->participants->count() }}</div><div class="ctrl-lbl">Total Students</div></div>
      <div class="ctrl-card"><div class="ctrl-val" style="color:var(--green)" id="activeStudents">{{ $room->participants->where('status','playing')->count() }}</div><div class="ctrl-lbl">Active Now</div></div>
      <div class="ctrl-card"><div class="ctrl-val" style="color:var(--orange)" id="answeredCount">0</div><div class="ctrl-lbl">Answered This Q</div></div>
      <div class="ctrl-card"><div class="ctrl-val" id="timeLeft">{{ $room->question_timer }}s</div><div class="ctrl-lbl">Timer</div></div>
    </div>

    {{-- Institution Codes --}}
    <div class="card">
      <div class="card-hd"><span class="card-title">🔑 Institution Student Codes</span><span style="font-size:.74rem;color:var(--muted)">Share with each institution</span></div>
      @php
        $instColors = ['#7c5cfc','#00e396','#ff9500','#ff4d6a'];
      @endphp
      @foreach($room->institutionParticipants as $idx => $ip)
      <div class="inst-code-card">
        <div class="ic-color" style="background:{{ $instColors[$idx % 4] }}"></div>
        <div style="flex:1">
          <div class="ic-name">{{ $ip->name }}</div>
          <div class="ic-code" style="color:{{ $instColors[$idx % 4] }}">{{ $ip->student_code }}</div>
          <div class="ic-count"><span id="count-inst-{{ $ip->id }}">{{ $ip->students_count ?? 0 }}</span> students joined</div>
        </div>
        <button class="btn btn-ghost btn-sm" onclick="copyText('{{ $ip->student_code }}','Code copied!')">📋 Copy</button>
      </div>
      @endforeach
    </div>

    {{-- Controls --}}
    <div class="card">
      <div class="card-hd"><span class="card-title">🎮 Battle Controls</span></div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        @if($room->status === 'waiting')
        <button class="btn btn-grad" id="startBtn" onclick="startBattle()">⚡ Start Battle</button>
        <button class="btn btn-ghost" onclick="sendReminder()">📣 Send Reminder</button>
        @endif
        @if($room->status === 'in_progress')
        <button class="btn btn-ghost" id="nextQBtn" onclick="forceNextQ()">⏭ Force Next Question</button>
        <button class="btn btn-danger" onclick="endBattleNow()">⛔ End Battle</button>
        @endif
        @if($room->status === 'finished')
        <a href="{{ route('institution.battle.results', $room->code) }}" class="btn btn-grad">🏆 View Full Results</a>
        <button class="btn btn-ghost" onclick="rematch()">🔄 Create Rematch</button>
        @endif
        <button class="btn btn-ghost btn-sm" onclick="copyText('{{ url('/institution/battle/join/'.$room->code) }}','Join link copied!')">🔗 Copy Join Link</button>
      </div>

      @if($room->status === 'waiting')
      <div style="margin-top:14px;padding:12px;background:var(--card2);border-radius:8px;font-size:.78rem;color:var(--muted)">
        <strong style="color:var(--text)">⏳ Waiting for students:</strong> At least 2 students must join before you can start.
        Currently <strong style="color:var(--accent)" id="waitingCount">{{ $room->participants->count() }}</strong> joined.
      </div>
      @endif
    </div>

    {{-- Anti-Cheat Log --}}
    <div class="card">
      <div class="card-hd"><span class="card-title">🛡 Anti-Cheat Monitor</span><span class="badge {{ $room->anti_cheat ? 'badge-g' : 'badge-r' }}">{{ $room->anti_cheat ? 'Active' : 'Off' }}</span></div>
      <div id="violationLog" style="max-height:180px;overflow-y:auto">
        @forelse($room->violations ?? [] as $v)
        <div style="display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid var(--border2);font-size:.78rem">
          <span>⚠️</span>
          <span style="flex:1">{{ $v['name'] ?? '—' }} — {{ str_replace('_',' ',ucfirst($v['type'] ?? '')) }}</span>
          <span style="color:{{ $v['disqualified']?'var(--red)':'var(--orange)' }};font-weight:700">{{ $v['disqualified'] ? 'DQ\'d' : 'Warning' }}</span>
        </div>
        @empty
        <div style="text-align:center;padding:20px;color:var(--muted);font-size:.8rem">No violations recorded</div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Sidebar: Live Leaderboard --}}
  <div class="manage-side">
    <div class="side-hd">
      <span>🏆 Live Leaderboard</span>
      <span style="font-size:.68rem;color:var(--muted)" id="lastUpdate">—</span>
    </div>
    <div style="overflow-y:auto;flex:1" id="lbContainer">
      @foreach($room->institutionParticipants as $idx => $ip)
      @php
        $score = $room->participants->where('institution_id',$ip->id)->sum('score');
        $students = $room->participants->where('institution_id',$ip->id)->sortByDesc('score');
      @endphp
      <div class="lb-institution" id="lb-inst-{{ $ip->id }}">
        <div class="lb-inst-name">
          <span style="width:10px;height:10px;border-radius:50%;background:{{ $instColors[$idx%4] }};flex-shrink:0;display:inline-block"></span>
          {{ $ip->name }}
          <span style="margin-left:auto;font-family:var(--fh);font-weight:900;color:{{ $instColors[$idx%4] }}" id="inst-score-{{ $ip->id }}">{{ $score }}</span>
        </div>
        <div class="lb-inst-bar"><div class="lb-inst-fill" id="inst-bar-{{ $ip->id }}" style="width:0%;background:{{ $instColors[$idx%4] }}"></div></div>
        <div id="students-{{ $ip->id }}">
          @foreach($students->take(5) as $p)
          <div class="lb-student">
            <div class="lb-av">{{ strtoupper(substr($p->user->name,0,1)) }}</div>
            <span class="lb-name">{{ $p->user->name }}</span>
            <span class="lb-pts" style="color:{{ $instColors[$idx%4] }}" id="pts-{{ $p->user_id }}">{{ $p->score }}</span>
          </div>
          @endforeach
        </div>
      </div>
      @endforeach
    </div>
    <div style="padding:12px;border-top:1px solid var(--border)">
      <div style="font-size:.7rem;color:var(--muted);text-align:center">Updates every 3 seconds</div>
    </div>
  </div>
</div>

<script src="https://js.pusher.com/8.0/pusher.min.js"></script>
<script>
const CSRF = '{{ csrf_token() }}';
const ROOM_CODE = '{{ $room->code }}';
const INST_COUNT = {{ $room->institutionParticipants->count() }};
const INST_COLORS = ['#7c5cfc','#00e396','#ff9500','#ff4d6a'];
const INST_IDS = @json($room->institutionParticipants->pluck('id')->values());

function copyText(text, msg) { navigator.clipboard.writeText(text).then(() => toast('success','📋 '+msg)); }
function toast(type, msg) {
  const colors = { success:'#22c55e', error:'#ef4444', info:'#3b82f6' };
  const el = document.createElement('div');
  el.style.cssText = `position:fixed;bottom:22px;right:22px;z-index:9999;padding:11px 18px;border-radius:10px;color:#fff;font-size:.82rem;font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,.25);background:${colors[type]||'#333'}`;
  el.textContent = msg; document.body.appendChild(el);
  setTimeout(() => el.remove(), 2800);
}

// ── WebSocket ──
try {
  const pusher = new Pusher('{{ config("broadcasting.connections.reverb.key","key") }}', {
    cluster:'mt1', wsHost:'{{ config("broadcasting.connections.reverb.host","127.0.0.1") }}',
    wsPort:{{ config("broadcasting.connections.reverb.port",8080) }},
    forceTLS:false, enabledTransports:['ws','wss'], disableStats:true
  });
  const ch = pusher.subscribe(`inst-battle.${ROOM_CODE}`);
  ch.bind('scores.updated', d => updateLeaderboard(d.scores));
  ch.bind('battle.started', () => location.reload());
  ch.bind('battle.finished', d => { if (d.winner) showWinner(d.winner); });
  ch.bind('student.joined', d => { if (g('waitingCount')) g('waitingCount').textContent = d.total; });
} catch(e) { console.warn('WS init failed'); }

// ── Poll Fallback ──
setInterval(pollState, 3000);
async function pollState() {
  try {
    const r = await fetch(`/institution/battle/state/${ROOM_CODE}`, { headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':CSRF} });
    const d = await r.json();
    if (!d.success) return;
    if (d.scores) updateLeaderboard(d.scores);
    if (d.answered_count !== undefined) { const el = g('answeredCount'); if (el) el.textContent = d.answered_count; }
    if (d.active_count !== undefined) { const el = g('activeStudents'); if (el) el.textContent = d.active_count; }
    if (d.total_count !== undefined) { const el = g('totalStudents'); if (el) el.textContent = d.total_count; const wc = g('waitingCount'); if (wc) wc.textContent = d.total_count; }
    if (d.current_question) { const el = g('liveQNum'); if (el) el.textContent = d.current_question; }
    const lu = g('lastUpdate'); if (lu) lu.textContent = new Date().toLocaleTimeString();
  } catch(e) {}
}

function updateLeaderboard(scores) {
  if (!scores) return;
  const maxScore = Math.max(...scores.map(s => s.total_score || 0), 1);
  scores.forEach((inst, idx) => {
    const scoreEl = g(`inst-score-${inst.institution_id}`);
    const barEl = g(`inst-bar-${inst.institution_id}`);
    if (scoreEl) animNum(scoreEl, parseInt(scoreEl.textContent)||0, inst.total_score);
    if (barEl) barEl.style.width = (inst.total_score / maxScore * 100) + '%';
    const studentsEl = g(`students-${inst.institution_id}`);
    if (studentsEl && inst.students) {
      studentsEl.innerHTML = inst.students.slice(0,5).map(s => `
        <div class="lb-student">
          <div class="lb-av">${s.name.charAt(0).toUpperCase()}</div>
          <span class="lb-name">${esc(s.name)}</span>
          <span class="lb-pts" style="color:${INST_COLORS[idx%4]}">${s.score}</span>
        </div>`).join('');
    }
  });
  // Re-sort institution blocks by score
  const container = g('lbContainer');
  if (!container) return;
  const blocks = Array.from(container.querySelectorAll('.lb-institution'));
  blocks.sort((a, b) => {
    const sa = parseInt(a.querySelector('[id^="inst-score-"]')?.textContent||0);
    const sb = parseInt(b.querySelector('[id^="inst-score-"]')?.textContent||0);
    return sb - sa;
  });
  blocks.forEach(b => container.appendChild(b));
}

function animNum(el, from, to) {
  const d=400,s=performance.now();
  const f=n=>{const p=Math.min((n-s)/d,1);el.textContent=Math.round(from+(to-from)*p);if(p<1)requestAnimationFrame(f);};
  requestAnimationFrame(f);
}

function showWinner(winner) {
  const wb = g('winnerBox');
  if (!wb) return;
  wb.style.display = '';
  g('winnerText').textContent = '🏆 ' + winner + ' wins!';
}

// ── Controls ──
async function startBattle() {
  const btn = g('startBtn');
  if (btn) { btn.disabled = true; btn.textContent = '⏳ Starting…'; }
  // Countdown
  g('countdownDisplay').style.display = '';
  let n = 3;
  const iv = setInterval(() => {
    n--; if (n > 0) { g('cdNum').textContent = n; } else { clearInterval(iv); doStart(); }
  }, 1000);
}
async function doStart() {
  try {
    const r = await fetch(`/institution/battle/start`, {
      method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'},
      body: JSON.stringify({ code: ROOM_CODE })
    });
    const d = await r.json();
    if (d.success) { toast('success','Battle started!'); setTimeout(()=>location.reload(),1200); }
    else toast('error', d.message||'Failed to start');
  } catch { toast('error','Network error'); }
}
async function forceNextQ() {
  try {
    const r = await fetch('/institution/battle/next-question',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({code:ROOM_CODE})});
    const d = await r.json();
    if (!d.success) toast('error',d.message||'Failed');
  } catch { toast('error','Network error'); }
}
async function endBattleNow() {
  if (!confirm('End the battle now? This will finalize all scores.')) return;
  try {
    const r = await fetch('/institution/battle/end',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({code:ROOM_CODE})});
    const d = await r.json();
    if (d.success) { toast('success','Battle ended'); setTimeout(()=>window.location.href=d.resultsUrl,1200); }
    else toast('error',d.message);
  } catch { toast('error','Network error'); }
}
async function rematch() {
  try {
    const r = await fetch('/institution/battle/rematch',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({code:ROOM_CODE})});
    const d = await r.json();
    if (d.success) window.location.href = d.redirectUrl;
    else toast('error',d.message);
  } catch { toast('error','Network error'); }
}
function sendReminder() { toast('info','Reminder sent to all students!'); }

function g(id){return document.getElementById(id);}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
</script>
@endsection