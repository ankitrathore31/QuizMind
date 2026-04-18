{{-- resources/views/institution/battle/join.blade.php --}}
@extends('student.layout.master')
@section('title', 'Join Institution Battle')
@section('content')
<style>
:root{--bg:#0a0a0f;--card:#13131e;--card2:#1a1a28;--border:rgba(255,255,255,.07);--text:#f0f0f8;--muted:#7070a0;--accent:#7c5cfc;--green:#00e396;--red:#ff4d6a;--gradient:linear-gradient(135deg,#7c5cfc,#00d4ff);--radius:14px;--rsm:9px;--fh:'Syne',sans-serif;--fb:'DM Sans',sans-serif}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:var(--fb)}
.page{max-width:500px;margin:0 auto;padding:60px 20px}
.card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:28px}
.btn{display:inline-flex;align-items:center;gap:6px;padding:11px 20px;border-radius:8px;border:none;font-family:var(--fb);font-weight:700;font-size:.84rem;cursor:pointer;transition:.18s;text-decoration:none;width:100%;justify-content:center}
.btn-grad{background:var(--gradient);color:#fff}.btn-grad:hover{opacity:.9;transform:translateY(-1px)}
.btn-ghost{background:transparent;color:var(--text);border:1.5px solid var(--border)}.btn-ghost:hover{border-color:rgba(124,92,252,.35)}
.btn:disabled{opacity:.45;cursor:not-allowed}
.fi{width:100%;padding:12px 14px;background:rgba(255,255,255,.03);border:1.5px solid var(--border);border-radius:8px;color:var(--text);font-family:var(--fb);font-size:.9rem;outline:none;transition:.18s;text-transform:uppercase;letter-spacing:.1em;font-weight:700;text-align:center}
.fi:focus{border-color:var(--accent);background:rgba(124,92,252,.04);box-shadow:0 0 0 3px rgba(124,92,252,.1)}
.err-box{background:rgba(255,77,106,.08);border:1px solid rgba(255,77,106,.2);border-radius:8px;padding:12px 16px;font-size:.82rem;color:var(--red);margin-bottom:16px}
.info-box{background:rgba(124,92,252,.07);border:1px solid rgba(124,92,252,.15);border-radius:10px;padding:14px 16px}
</style>

<div class="page">
  <div style="text-align:center;margin-bottom:28px">
    <div style="font-size:3rem;margin-bottom:10px">🏫</div>
    <h1 style="font-family:var(--fh);font-size:1.6rem;font-weight:900;margin-bottom:6px">Join Institution Battle</h1>
    <p style="color:var(--muted);font-size:.86rem">Enter the student code given by your institution</p>
  </div>

  @if($joinError ?? null)
  <div class="err-box">⚠️ {{ $joinError }}</div>
  @endif

  @if(isset($battle) && $battle && isset($myInstitution))
  {{-- Code already validated --}}
  <div class="card">
    <div class="info-box" style="margin-bottom:20px">
      <div style="font-size:.68rem;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">Battle Info</div>
      <div style="font-family:var(--fh);font-weight:800;font-size:.9rem;margin-bottom:4px">{{ $battle->quiz->title ?? 'Institution Battle' }}</div>
      <div style="font-size:.78rem;color:var(--muted);margin-bottom:8px">{{ $battle->total_questions }} questions · {{ $battle->question_timer }}s per question</div>
      <div style="display:flex;align-items:center;gap:8px">
        <span style="width:10px;height:10px;border-radius:50%;background:var(--accent);display:inline-block"></span>
        <span style="font-weight:700;font-size:.84rem;color:var(--accent)">{{ $myInstitution->name }}</span>
      </div>
    </div>
    <button class="btn btn-grad" id="joinBtn" onclick="doJoin('{{ request()->route('code') ?? '' }}')">
      ⚡ Join Battle
    </button>
    <a href="{{ route('student.dashboard') }}" class="btn btn-ghost" style="margin-top:8px">← Back</a>
  </div>
  @else
  {{-- Enter code form --}}
  <div class="card">
    <div style="margin-bottom:16px">
      <label style="display:block;font-size:.72rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;text-align:center">Your Institution Student Code</label>
      <input class="fi" type="text" id="codeInput" placeholder="XXXXXXXX" maxlength="12" oninput="this.value=this.value.toUpperCase()" onkeydown="if(event.key==='Enter')doJoin()">
    </div>
    <div id="errBox" style="display:none" class="err-box"></div>
    <button class="btn btn-grad" id="joinBtn" onclick="doJoin()">⚡ Join Battle</button>
    <a href="{{ route('student.dashboard') }}" class="btn btn-ghost" style="margin-top:8px">← Back to Dashboard</a>
  </div>
  @endif
</div>

<script>
const CSRF = '{{ csrf_token() }}';
async function doJoin(preCode) {
  const code = preCode || document.getElementById('codeInput')?.value?.trim();
  if (!code) { showErr('Enter your institution student code'); return; }
  const btn = document.getElementById('joinBtn');
  btn.disabled = true; btn.textContent = '⏳ Joining…';
  try {
    const r = await fetch('{{ route("institution.battle.join.code") }}', {
      method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'},
      body: JSON.stringify({ student_code: code })
    });
    const d = await r.json();
    if (d.success) { window.location.href = d.redirectUrl; }
    else { showErr(d.message || 'Invalid code. Try again.'); btn.disabled=false; btn.textContent='⚡ Join Battle'; }
  } catch { showErr('Network error. Please try again.'); btn.disabled=false; btn.textContent='⚡ Join Battle'; }
}
function showErr(msg) {
  const eb = document.getElementById('errBox');
  if (eb) { eb.style.display=''; eb.textContent='⚠️ '+msg; }
  else alert(msg);
}
</script>
@endsection