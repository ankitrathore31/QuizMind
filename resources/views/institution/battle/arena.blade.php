{{-- resources/views/institution/battle/arena.blade.php --}}
@extends('student.layout.master')
@section('title', 'Institution Battle ⚔️')
@section('content')
<style>
body,html{overflow:hidden!important}
#arena{position:fixed;inset:0;display:flex;flex-direction:column;background:var(--bg);z-index:500}
/* Top bar */
#topBar{display:flex;align-items:center;gap:12px;padding:10px 18px;background:rgba(0,0,0,.4);border-bottom:1px solid var(--border);backdrop-filter:blur(12px);flex-shrink:0}
.tb-code{font-family:var(--fh);font-weight:800;font-size:.82rem;color:var(--accent)}
.tb-inst{font-size:.74rem;padding:3px 10px;border-radius:20px;font-weight:700;border:1px solid}
.tb-prog{flex:1;height:5px;background:rgba(255,255,255,.08);border-radius:4px;overflow:hidden}
.tb-fill{height:100%;background:var(--gradient);border-radius:4px;transition:width .4s ease}
.tb-score{background:rgba(124,92,252,.15);border:1px solid rgba(124,92,252,.3);color:var(--accent);padding:4px 12px;border-radius:20px;font-weight:800;font-size:.82rem}
/* Body */
#arenaBody{display:grid;grid-template-columns:1fr 280px;flex:1;overflow:hidden;min-height:0}
@media(max-width:700px){#arenaBody{grid-template-columns:1fr}#sidebar{display:none}}
#mainPanel{display:flex;flex-direction:column;padding:18px 22px;overflow-y:auto;gap:14px;min-height:0}
/* Timer */
.timer-circle{position:relative;width:60px;height:60px;flex-shrink:0}
.timer-circle svg{transform:rotate(-90deg)}
.tc-bg{fill:none;stroke:rgba(255,255,255,.08);stroke-width:5}
.tc-fill{fill:none;stroke-width:5;stroke-linecap:round;stroke-dasharray:157;stroke-dashoffset:0;transition:stroke .4s}
.tc-num{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:var(--fh);font-size:1.2rem;font-weight:900}
/* Question */
.q-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:20px;animation:qSlide .35s cubic-bezier(.34,1.26,.64,1)}
@keyframes qSlide{from{opacity:0;transform:translateY(16px) scale(.97)}to{opacity:1;transform:none}}
.q-text{font-size:1rem;font-weight:600;line-height:1.65}
/* Options */
.opts-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
@media(max-width:500px){.opts-grid{grid-template-columns:1fr}}
.opt{display:flex;align-items:center;gap:10px;padding:13px 14px;border-radius:var(--rsm);border:1.5px solid var(--border);background:transparent;cursor:pointer;text-align:left;transition:.15s;font-size:.88rem;color:var(--text);width:100%;animation:optIn .25s ease both}
@keyframes optIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.opt:nth-child(1){animation-delay:.04s}.opt:nth-child(2){animation-delay:.08s}.opt:nth-child(3){animation-delay:.12s}.opt:nth-child(4){animation-delay:.16s}
.opt:hover:not(:disabled):not(.correct):not(.wrong){border-color:var(--accent);background:rgba(124,92,252,.07);transform:translateY(-1px)}
.opt-letter{width:28px;height:28px;border-radius:50%;border:2px solid rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.76rem;flex-shrink:0}
.opt.selected{border-color:var(--accent);background:rgba(124,92,252,.1)}
.opt.selected .opt-letter{border-color:var(--accent);background:var(--accent);color:#fff}
.opt.correct{border-color:#00e396!important;background:rgba(0,227,150,.08)!important;animation:correctPop .4s cubic-bezier(.34,1.56,.64,1)}
.opt.correct .opt-letter{border-color:#00e396;background:#00e396;color:#000}
@keyframes correctPop{0%{transform:scale(1)}50%{transform:scale(1.04)}100%{transform:scale(1)}}
.opt.wrong{border-color:rgba(255,77,106,.5)!important;background:rgba(255,77,106,.07)!important;animation:wrongShake .3s ease}
.opt.wrong .opt-letter{border-color:var(--red);background:var(--red);color:#fff}
@keyframes wrongShake{0%,100%{transform:translateX(0)}25%{transform:translateX(-5px)}75%{transform:translateX(5px)}}
.opt:disabled{cursor:not-allowed}
/* Feedback */
#feedback{padding:14px 16px;border-radius:var(--rsm);border:1px solid;display:none;animation:optIn .25s ease}
#feedback.show{display:block}
#feedback.fb-ok{border-color:rgba(0,227,150,.3);background:rgba(0,227,150,.05)}
#feedback.fb-bad{border-color:rgba(255,77,106,.25);background:rgba(255,77,106,.04)}
#feedback.fb-out{border-color:rgba(255,165,0,.25);background:rgba(255,165,0,.04)}
/* Sidebar */
#sidebar{border-left:1px solid var(--border);background:rgba(0,0,0,.2);display:flex;flex-direction:column;overflow:hidden}
.sb-hd{padding:12px 14px;border-bottom:1px solid var(--border);font-family:var(--fh);font-weight:800;font-size:.78rem;flex-shrink:0}
/* Institution score blocks */
.inst-score-block{padding:10px 12px;border-bottom:1px solid var(--border2)}
.isb-header{display:flex;align-items:center;gap:8px;margin-bottom:6px}
.isb-name{font-family:var(--fh);font-weight:800;font-size:.8rem;flex:1}
.isb-total{font-family:var(--fh);font-weight:900;font-size:1rem}
.isb-bar{height:3px;border-radius:2px;background:rgba(255,255,255,.07);overflow:hidden;margin-bottom:6px}
.isb-fill{height:100%;border-radius:2px;transition:width .8s ease}
.isb-student{display:flex;align-items:center;gap:6px;padding:3px 0;font-size:.72rem}
.isb-av{width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:800;color:#fff;flex-shrink:0}
/* Float FX */
.float-fx{position:fixed;pointer-events:none;z-index:9000;font-size:1.5rem;font-weight:900;font-family:var(--fh);animation:floatUp .85s ease forwards}
@keyframes floatUp{0%{opacity:1;transform:translateY(0) scale(1)}100%{opacity:0;transform:translateY(-70px) scale(1.25)}}
/* Panels */
#waitPanel{display:flex;flex-direction:column;align-items:center;justify-content:center;flex:1;text-align:center;gap:10px;padding:40px 20px}
#qPanel{display:none;flex-direction:column;gap:14px}
#finishedPanel{display:none;flex-direction:column;align-items:center;justify-content:center;flex:1;text-align:center;gap:12px;padding:40px}
#vioOverlay{display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.88);align-items:center;justify-content:center;flex-direction:column;text-align:center}
#vioOverlay.show{display:flex}
.vio-box{background:var(--card);border:2px solid var(--red);border-radius:var(--radius);padding:32px 40px;max-width:360px}
#cdOverlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9990;align-items:center;justify-content:center;flex-direction:column;text-align:center}
#cdOverlay.show{display:flex}
.cd-num{font-family:var(--fh);font-size:6rem;font-weight:900;background:var(--gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;animation:cdPop .4s cubic-bezier(.34,1.56,.64,1)}
@keyframes cdPop{from{transform:scale(.5);opacity:0}to{transform:scale(1);opacity:1}}
#fxCanvas{position:fixed;inset:0;pointer-events:none;z-index:8000;width:100%;height:100%}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
</style>

<canvas id="fxCanvas"></canvas>

<div id="arena">
  <div id="topBar">
    <span class="tb-code">🏫 {{ $room->code }}</span>
    @php
      $myInst = $room->institutionParticipants->first(); // will be set by PHP
      $instColors = ['#7c5cfc','#00e396','#ff9500','#ff4d6a'];
      $myInstIdx = 0;
    @endphp
    @if($myInstitution ?? null)
    <span class="tb-inst" style="background:rgba(124,92,252,.1);color:var(--accent);border-color:rgba(124,92,252,.25)">{{ $myInstitution->name }}</span>
    @endif
    <div class="tb-prog"><div class="tb-fill" id="progFill" style="width:0%"></div></div>
    <span class="tb-score" id="qLabel">Q 0/{{ $room->total_questions }}</span>
    <span class="tb-score">⚡<span id="myPts">0</span></span>
  </div>

  <div id="arenaBody">
    <div id="mainPanel">
      <div id="waitPanel">
        <div style="font-size:3rem" class="anim-float">🏫</div>
        <h2 style="font-family:var(--fh);font-weight:800">Waiting for battle to start…</h2>
        <p style="color:var(--muted);font-size:.84rem">The institution host will start the battle. Stay on this page!</p>
        <div style="background:var(--card);border:1px solid var(--border);border-radius:var(--rsm);padding:14px 20px;margin-top:8px;text-align:center">
          <div style="font-size:.68rem;color:var(--muted);font-weight:700;text-transform:uppercase;margin-bottom:4px">Your Institution</div>
          <div style="font-family:var(--fh);font-weight:800;font-size:1rem;color:var(--accent)">{{ $myInstitution->name ?? 'Your Institution' }}</div>
        </div>
      </div>

      <div id="qPanel">
        <div style="display:flex;align-items:center;gap:14px">
          <div class="timer-circle">
            <svg width="60" height="60" viewBox="0 0 60 60">
              <circle class="tc-bg" cx="30" cy="30" r="25"/>
              <circle class="tc-fill" id="tcFill" cx="30" cy="30" r="25" stroke="var(--accent)"/>
            </svg>
            <div class="tc-num" id="tcNum">--</div>
          </div>
          <div style="height:6px;flex:1;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden">
            <div id="timerBar" style="width:100%;height:100%;background:var(--gradient);border-radius:3px;transition:width 1s linear"></div>
          </div>
          <span style="background:rgba(124,92,252,.12);border:1px solid rgba(124,92,252,.25);color:var(--accent);padding:4px 10px;border-radius:20px;font-size:.76rem;font-weight:700">⚡<span id="myPts2">0</span></span>
        </div>

        <div class="q-card">
          <div style="display:flex;justify-content:space-between;margin-bottom:10px;gap:8px;flex-wrap:wrap">
            <span id="qTopic" style="background:rgba(0,212,255,.08);color:#00d4ff;border:1px solid rgba(0,212,255,.2);padding:3px 9px;border-radius:20px;font-size:.7rem;font-weight:700">General</span>
            <span id="qNum" style="font-size:.72rem;font-weight:700;color:var(--muted)">Q 1</span>
          </div>
          <p class="q-text" id="qText"></p>
        </div>

        <div class="opts-grid" id="optsGrid"></div>

        <div id="feedback">
          <div style="font-weight:800;font-size:.9rem;margin-bottom:4px" id="fbHead"></div>
          <div style="font-size:.8rem;color:var(--muted);line-height:1.6" id="fbExpl"></div>
        </div>

        <div id="nextBar" style="display:none;align-items:center;justify-content:center;gap:8px;font-size:.78rem;color:var(--muted)">
          Next question in
          <div style="width:80px;height:3px;background:rgba(255,255,255,.08);border-radius:2px;overflow:hidden"><div id="nbFill" style="width:100%;height:100%;background:var(--accent);border-radius:2px"></div></div>
          <span id="nbSecs">3</span>s
        </div>
      </div>

      <div id="finishedPanel">
        <div style="font-size:3.5rem">🏆</div>
        <h2 style="font-family:var(--fh);font-size:1.6rem;font-weight:900">Institution Battle Complete!</h2>
        <p style="color:var(--muted)">Loading results…</p>
        <a href="{{ route('institution.battle.results', $room->code) }}" class="btn btn-grad" style="display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;background:var(--gradient);color:#fff;font-weight:700;text-decoration:none;margin-top:8px">View Results →</a>
      </div>
    </div>

    <div id="sidebar">
      <div class="sb-hd">🏫 Institution Scores</div>
      <div id="lbContainer" style="overflow-y:auto;flex:1">
        @foreach($room->institutionParticipants as $idx => $ip)
        @php
          $ipScore = $room->participants->where('institution_id',$ip->id)->sum('score');
          $ipStudents = $room->participants->where('institution_id',$ip->id)->sortByDesc('score');
        @endphp
        <div class="inst-score-block" id="isb-{{ $ip->id }}">
          <div class="isb-header">
            <span style="width:10px;height:10px;border-radius:50%;background:{{ $instColors[$idx%4] }};flex-shrink:0;display:inline-block"></span>
            <span class="isb-name">{{ $ip->name }}</span>
            <span class="isb-total" style="color:{{ $instColors[$idx%4] }}" id="iscore-{{ $ip->id }}">{{ $ipScore }}</span>
          </div>
          <div class="isb-bar"><div class="isb-fill" id="ibar-{{ $ip->id }}" style="width:0%;background:{{ $instColors[$idx%4] }}"></div></div>
          <div id="istudents-{{ $ip->id }}">
            @foreach($ipStudents->take(4) as $p)
            <div class="isb-student">
              <div class="isb-av" style="background:{{ $instColors[$idx%4] }}">{{ strtoupper(substr($p->user->name,0,1)) }}</div>
              <span style="flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $p->user->name }}</span>
              <span style="font-family:var(--fh);font-weight:800;color:{{ $instColors[$idx%4] }}" id="ipts-{{ $p->user_id }}">{{ $p->score }}</span>
            </div>
            @endforeach
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

<div id="vioOverlay">
  <div class="vio-box">
    <div style="font-size:2.8rem;margin-bottom:10px">⚠️</div>
    <h2 style="font-family:var(--fh);font-size:1.1rem;color:var(--red);margin-bottom:8px">Anti-Cheat Warning</h2>
    <p style="color:var(--muted);font-size:.84rem;margin-bottom:6px" id="vioMsg"></p>
    <p style="font-size:.74rem;color:var(--muted)" id="vioDetail"></p>
    <button style="display:inline-flex;align-items:center;padding:8px 16px;border-radius:8px;background:transparent;border:1.5px solid var(--border);color:var(--text);font-weight:700;cursor:pointer;margin-top:12px" onclick="dismissVio()">Back to Battle</button>
  </div>
</div>

<div id="cdOverlay">
  <p style="color:var(--muted);font-size:.9rem;margin-bottom:6px">Institution Battle starting in</p>
  <div class="cd-num" id="cdNum">3</div>
  <p style="color:var(--muted);font-size:.82rem;margin-top:10px">🏫 Represent your institution!</p>
</div>

<script src="https://js.pusher.com/8.0/pusher.min.js"></script>
<script>
const CFG = {
  roomCode: '{{ $room->code }}',
  myId: {{ Auth::id() }},
  myInstId: {{ $myInstitution->id ?? 0 }},
  qTimer: {{ $room->question_timer }},
  totalQ: {{ $room->total_questions }},
  csrf: '{{ csrf_token() }}',
  status: '{{ $room->status }}',
  antiCheat: {{ $room->anti_cheat ? 'true' : 'false' }},
};
const LETTERS = ['A','B','C','D'];
const INST_COLORS = ['#7c5cfc','#00e396','#ff9500','#ff4d6a'];
const R = {
  answer: '/institution/battle/answer',
  violation: '/institution/battle/violation',
  results: '{{ route("institution.battle.results", $room->code) }}',
  poll: '/institution/battle/arena-state/{{ $room->code }}',
};

const S = { qs:[], qIdx:-1, timeLeft:CFG.qTimer, tTick:null, nTick:null, pTick:null, answered:false, submitting:false, myScore:0, vios:0, disq:false, running:false, done:false, wsWorked:false, startHandled:false };

@if($room->status === 'in_progress')
S.qs = @json(collect($room->quiz->questions ?? [])->map(fn($q) => ['question'=>$q['question'],'options'=>$q['options'],'topic'=>$q['topic']??''])->values());
S.running = true; S.startHandled = true;
const _answered = {{ $room->answers()->where('user_id',Auth::id())->count() }};
document.addEventListener('DOMContentLoaded', () => showQ(Math.min(_answered, S.qs.length-1)));
@endif

// WebSocket
try {
  const pusher = new Pusher('{{ config("broadcasting.connections.reverb.key","key") }}', {
    cluster:'mt1', wsHost:'{{ config("broadcasting.connections.reverb.host","127.0.0.1") }}',
    wsPort:{{ config("broadcasting.connections.reverb.port",8080) }},
    forceTLS:false, enabledTransports:['ws','wss'], disableStats:true
  });
  const ch = pusher.subscribe(`inst-battle.${CFG.roomCode}`);
  ch.bind('battle.started', d => { if (S.startHandled) return; S.startHandled=true; S.qs=d.questions; S.running=true; S.wsWorked=true; stopPoll(); showCountdown(()=>showQ(0)); });
  ch.bind('scores.updated', d => { S.wsWorked=true; updateBoard(d.scores); });
  ch.bind('next.question', d => { S.wsWorked=true; clearNextBar(); if(d.questionIndex<S.qs.length) showQ(d.questionIndex); else endBattle(); });
  ch.bind('battle.finished', () => { S.wsWorked=true; endBattle(); });
  ch.bind('answer.submitted', d => { S.wsWorked=true; updateBoard(d.scores); if(!S.answered&&d.questionIndex===S.qIdx) revealAnswer(d.correctInfo.correct_option,-1,false,d.correctInfo.explanation); });
} catch(e) { console.warn('WS failed',e); }

function startPoll(){if(!S.pTick)S.pTick=setInterval(doPoll,3000);}
function stopPoll(){clearInterval(S.pTick);S.pTick=null;}
startPoll();

async function doPoll() {
  try {
    const r = await fetch(R.poll,{headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':CFG.csrf}});
    const d = await r.json();
    if(!d.success)return;
    if(d.scores)updateBoard(d.scores);
    if(d.status==='finished'&&!S.done)endBattle();
    if(!S.running&&d.status==='in_progress')location.reload();
  }catch(e){}
}

function showQ(idx) {
  if(S.done)return;
  if(idx>=S.qs.length){endBattle();return;}
  S.qIdx=idx; S.answered=false; S.submitting=false; S.timeLeft=CFG.qTimer;
  clearTimer(); clearNextBar(); hideFB();
  g('waitPanel').style.display='none'; g('qPanel').style.display='flex'; g('finishedPanel').style.display='none';
  const q=S.qs[idx];
  g('progFill').style.width=(idx/CFG.totalQ*100)+'%';
  g('qLabel').textContent=`Q ${idx+1}/${CFG.totalQ}`;
  g('qNum').textContent=`Q ${idx+1}`;
  g('qTopic').textContent=q.topic||'General';
  g('qText').textContent=q.question;
  let html='';
  for(let i=0;i<4;i++) html+=`<button class="opt" id="opt${i}" onclick="handleOpt(${i})"><span class="opt-letter">${LETTERS[i]}</span><span>${esc(q.options[i]||'')}</span></button>`;
  g('optsGrid').innerHTML=html;
  startTimer(); snd('tick');
}

function startTimer(){
  clearTimer(); S.timeLeft=CFG.qTimer; drawTimer(S.timeLeft);
  S.tTick=setInterval(()=>{S.timeLeft--;drawTimer(S.timeLeft);if(S.timeLeft<=5&&S.timeLeft>0)snd('urgent');if(S.timeLeft<=0){clearTimer();if(!S.answered)doTimeout();}},1000);
}
function clearTimer(){clearInterval(S.tTick);S.tTick=null;}
function drawTimer(t){
  const pct=Math.max(0,t/CFG.qTimer);
  const off=157*(1-pct);
  const fill=g('tcFill'),num=g('tcNum'),bar=g('timerBar');
  if(fill){fill.style.strokeDashoffset=off;fill.style.stroke=pct>.5?'var(--accent)':pct>.25?'orange':'var(--red)';}
  if(num){num.textContent=t;num.style.color=pct>.25?'var(--text)':'var(--red)';}
  if(bar){bar.style.width=(pct*100)+'%';bar.style.background=pct>.5?'':pct>.25?'orange':'var(--red)';}
}

function handleOpt(idx) {
  if(S.answered||S.submitting||S.disq)return;
  S.answered=true; S.submitting=true; clearTimer();
  const btn=g(`opt${idx}`); if(btn)btn.classList.add('selected');
  lockOpts();
  doSubmit(idx,(CFG.qTimer-S.timeLeft)*1000);
}

async function doSubmit(idx,ms) {
  try {
    const res=await fetch(R.answer,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CFG.csrf,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({room_code:CFG.roomCode,question_index:S.qIdx,selected:idx,time_ms:ms})});
    S.submitting=false;
    if(!res.ok){schedNext();return;}
    const d=await res.json();
    if(d.success){
      S.myScore+=(d.points||0);
      g('myPts').textContent=S.myScore; g('myPts2').textContent=S.myScore;
      revealAnswer(d.correct,idx,d.isCorrect,d.explanation);
      if(d.isCorrect){snd('correct');spawnParts(g(`opt${d.correct}`),'#00e396');floatFX(`+${d.points}pts`,'#00e396');}
      else{snd('wrong');floatFX('Wrong!','var(--red)');}
    }
    schedNext();
  }catch(err){S.submitting=false;schedNext();}
}

function doTimeout(){S.answered=true;S.submitting=false;lockOpts();snd('timeout');showFB('fb-out','⏰ Time\'s up!','');schedNext();fetch(R.answer,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CFG.csrf,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({room_code:CFG.roomCode,question_index:S.qIdx,selected:-1,time_ms:CFG.qTimer*1000})}).catch(()=>{});}
function lockOpts(){for(let i=0;i<4;i++){const b=g(`opt${i}`);if(b)b.disabled=true;}}
function revealAnswer(ci,si,ok,expl){for(let i=0;i<4;i++){const b=g(`opt${i}`);if(!b)continue;b.classList.remove('selected');b.disabled=true;if(i===ci)b.classList.add('correct');else if(i===si&&!ok)b.classList.add('wrong');}if(ok!==null&&ok!==undefined&&si>=0){showFB(ok?'fb-ok':'fb-bad',ok?'✅ Correct!':'❌ Wrong! Correct: '+LETTERS[ci],expl||'');}}

function schedNext(){
  const bar=g('nextBar'),fill=g('nbFill'),sec=g('nbSecs');
  if(!bar)return;
  bar.style.display='flex'; let t=3.0;
  if(fill)fill.style.width='100%'; if(sec)sec.textContent=3;
  const ci=S.qIdx;
  S.nTick=setInterval(()=>{t-=.1;if(fill)fill.style.width=Math.max(0,t/3*100)+'%';if(sec)sec.textContent=Math.ceil(t);if(t<=0){clearNextBar();const wsOk=true;if(!S.wsWorked||!wsOk){const n=S.qIdx+1;if(n<S.qs.length)showQ(n);else endBattle();}else{setTimeout(()=>{if(S.qIdx===ci&&!S.done){const n=S.qIdx+1;if(n<S.qs.length)showQ(n);else endBattle();}},600);}}},100);
}
function clearNextBar(){clearInterval(S.nTick);S.nTick=null;const bar=g('nextBar');if(bar)bar.style.display='none';}

function showFB(cls,title,expl){const fb=g('feedback');if(!fb)return;fb.className=cls+' show';g('fbHead').textContent=title;g('fbExpl').textContent=expl;}
function hideFB(){const fb=g('feedback');if(fb)fb.className='';}

function updateBoard(scores){
  if(!scores)return;
  const maxScore=Math.max(...(scores.map(s=>s.total_score||0)),1);
  scores.forEach((inst,idx)=>{
    const se=g(`iscore-${inst.institution_id}`),be=g(`ibar-${inst.institution_id}`);
    if(se){const prev=parseInt(se.textContent)||0;animNum(se,prev,inst.total_score);}
    if(be)be.style.width=(inst.total_score/maxScore*100)+'%';
    const se2=g(`istudents-${inst.institution_id}`);
    if(se2&&inst.students){se2.innerHTML=inst.students.slice(0,4).map(s=>`<div class="isb-student"><div class="isb-av" style="background:${INST_COLORS[idx%4]}">${s.name.charAt(0).toUpperCase()}</div><span style="flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${esc(s.name)}</span><span style="font-family:var(--fh);font-weight:800;color:${INST_COLORS[idx%4]}">${s.score}</span></div>`).join('');}
  });
}

function animNum(el,from,to){const d=350,s=performance.now();const f=n=>{const p=Math.min((n-s)/d,1);el.textContent=Math.round(from+(to-from)*p);if(p<1)requestAnimationFrame(f);};requestAnimationFrame(f);}

function endBattle(){if(S.done)return;S.done=true;clearTimer();clearNextBar();stopPoll();g('waitPanel').style.display='none';g('qPanel').style.display='none';g('finishedPanel').style.display='flex';snd('victory');setTimeout(()=>{window.location.href=R.results;},3500);}

function showCountdown(cb){const ov=g('cdOverlay'),num=g('cdNum');ov.classList.add('show');let n=3;num.textContent=n;snd('beep');const iv=setInterval(()=>{n--;if(n>0){num.textContent=n;num.style.animation='none';num.offsetHeight;num.style.animation='';snd('beep');}else{clearInterval(iv);ov.classList.remove('show');cb();}},1000);}

if(CFG.antiCheat){let blurT;document.addEventListener('visibilitychange',()=>{if(!S.running||S.disq||S.done)return;if(document.hidden)doVio('tab_switch');});window.addEventListener('blur',()=>{if(!S.running||S.disq||S.done)return;blurT=setTimeout(()=>doVio('window_blur'),600);});window.addEventListener('focus',()=>clearTimeout(blurT));}

async function doVio(type){S.vios++;try{const r=await fetch(R.violation,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CFG.csrf,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({room_code:CFG.roomCode,type})});const d=await r.json();if(d.disqualified){S.disq=true;clearTimer();}g('vioMsg').textContent=type==='tab_switch'?'Tab switch detected!':'Window minimized!';g('vioDetail').textContent=d.disqualified?'⛔ You are disqualified.':(`Warning ${d.violations}/3 − 10 pts`);g('vioOverlay').classList.add('show');snd('vio');}catch(e){}}
function dismissVio(){g('vioOverlay').classList.remove('show');}

// Sound
let AC;
function getAC(){if(!AC)AC=new(window.AudioContext||window.webkitAudioContext)();return AC;}
function snd(name){try{const ac=getAC(),t=ac.currentTime;const tone=(f,d,tp='sine',v=.25)=>{const o=ac.createOscillator(),g2=ac.createGain();o.connect(g2);g2.connect(ac.destination);o.type=tp;o.frequency.value=f;g2.gain.setValueAtTime(v,t);g2.gain.exponentialRampToValueAtTime(.001,t+d);o.start(t);o.stop(t+d+.01);};switch(name){case'tick':tone(880,.08,'triangle',.06);break;case'urgent':tone(660,.07,'square',.12);break;case'beep':tone(1047,.12,'sine',.3);break;case'timeout':tone(220,.4,'triangle',.15);break;case'vio':tone(150,.5,'square',.18);break;case'correct':[[523,.18],[659,.18],[784,.18],[1047,.3]].forEach(([f,d],i)=>{const o=ac.createOscillator(),g2=ac.createGain();o.connect(g2);g2.connect(ac.destination);o.type='sine';o.frequency.value=f;const st=t+i*.11;g2.gain.setValueAtTime(.25,st);g2.gain.exponentialRampToValueAtTime(.001,st+d);o.start(st);o.stop(st+d+.01);});break;case'wrong':{const o=ac.createOscillator(),g2=ac.createGain();o.connect(g2);g2.connect(ac.destination);o.type='sawtooth';o.frequency.setValueAtTime(280,t);o.frequency.exponentialRampToValueAtTime(160,t+.3);g2.gain.setValueAtTime(.18,t);g2.gain.exponentialRampToValueAtTime(.001,t+.3);o.start(t);o.stop(t+.32);break;}case'victory':[[523,.25],[659,.25],[784,.25],[1047,.35],[1319,.5]].forEach(([f,d],i)=>{const o=ac.createOscillator(),g2=ac.createGain();o.connect(g2);g2.connect(ac.destination);o.type='sine';o.frequency.value=f;const st=t+i*.13;g2.gain.setValueAtTime(.3,st);g2.gain.exponentialRampToValueAtTime(.001,st+d);o.start(st);o.stop(st+d+.01);});break;}}catch(e){}}

// Particles
const cvs=document.getElementById('fxCanvas');const cx=cvs.getContext('2d');let pts2=[];
(()=>{cvs.width=innerWidth;cvs.height=innerHeight;})();
window.addEventListener('resize',()=>{cvs.width=innerWidth;cvs.height=innerHeight;});
function spawnParts(btn,color){if(!btn)return;const r=btn.getBoundingClientRect(),bx=r.left+r.width/2,by=r.top+r.height/2;for(let i=0;i<20;i++){const a=(Math.PI*2/20)*i+(Math.random()-.5)*.5,sp=2+Math.random()*5;pts2.push({x:bx,y:by,vx:Math.cos(a)*sp,vy:Math.sin(a)*sp-1,r:3+Math.random()*4,a:1,color});}if(!looping2)loop2();}
let looping2=false;
function loop2(){looping2=true;cx.clearRect(0,0,cvs.width,cvs.height);pts2=pts2.filter(p=>p.a>.02);pts2.forEach(p=>{p.x+=p.vx;p.y+=p.vy;p.vy+=.18;p.a-=.03;cx.save();cx.globalAlpha=p.a;cx.fillStyle=p.color;cx.beginPath();cx.arc(p.x,p.y,p.r,0,Math.PI*2);cx.fill();cx.restore();});if(pts2.length)requestAnimationFrame(loop2);else{looping2=false;cx.clearRect(0,0,cvs.width,cvs.height);}}

function floatFX(text,color){const d=document.createElement('div');d.className='float-fx';d.textContent=text;d.style.color=color;d.style.left=(innerWidth/2-60)+'px';d.style.top=(innerHeight/2-60)+'px';document.body.appendChild(d);setTimeout(()=>d.remove(),950);}
function g(id){return document.getElementById(id);}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
</script>
@endsection