import { useState, useEffect, useRef } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { connectSocket } from '../utils/socket'
import api from '../utils/api'
import toast from 'react-hot-toast'

export default function SchoolBattlePage() {
  const { user }  = useAuth()
  const navigate  = useNavigate()
  const socketRef = useRef(null)

  const [view,    setView]   = useState('menu')   // menu | create | lobby | live | finished
  const [room,    setRoom]   = useState(null)
  const [joinCode,setJoinCode]= useState('')
  const [loading, setLoading]= useState(false)

  // Create form
  const [maxStudents, setMaxStudents] = useState(40)
  const [quizSource,  setQuizSource]  = useState('topic')
  const [topic,       setTopic]       = useState('')
  const [diff,        setDiff]        = useState('intermediate')
  const [qCount,      setQCount]      = useState(15)
  const [pdfFile,     setPdfFile]     = useState(null)
  const pdfRef = useRef()

  // Battle state
  const [leaderA,   setLeaderA]   = useState([])
  const [leaderB,   setLeaderB]   = useState([])
  const [teamScores,setTeamScores]= useState([])
  const [question,  setQuestion]  = useState(null)
  const [qIndex,    setQIndex]    = useState(0)
  const [totalQ,    setTotalQ]    = useState(0)
  const [timeLeft,  setTimeLeft]  = useState(15)
  const [answered,  setAnswered]  = useState(false)
  const [selected,  setSelected]  = useState(null)
  const [lastResult,setLastResult]= useState(null)
  const [cheatLog,  setCheatLog]  = useState([])
  const [gameResult,setGameResult]= useState(null)
  const timerRef = useRef(null)

  const LETTERS = ['A','B','C','D']
  const isHost  = room?.hostId === user?._id || room?.hostId?._id === user?._id
  const mySchool= room?.schoolAId===user?._id||room?.schoolAId?._id===user?._id ? 'A' : 'B'

  // Anti-cheat
  useEffect(() => {
    if (view !== 'live') return
    const onHide = () => {
      if (document.hidden) {
        socketRef.current?.emit('cheat_event', { roomCode:room?.code, userId:user?._id, userName:user?.name, type:'tab_switch' })
        toast.error('⚠️ Tab switch detected!')
      }
    }
    document.addEventListener('visibilitychange', onHide)
    return () => document.removeEventListener('visibilitychange', onHide)
  }, [view, room])

  // Timer
  useEffect(() => {
    if (view !== 'live' || !question) return
    clearInterval(timerRef.current); setTimeLeft(15)
    timerRef.current = setInterval(() => {
      setTimeLeft(t => { if(t<=1){clearInterval(timerRef.current);if(!answered)handleAnswer(-1);return 0} return t-1 })
    }, 1000)
    return () => clearInterval(timerRef.current)
  }, [question, view])

  const connectSocketForRoom = (code) => {
    const s = connectSocket(); socketRef.current = s
    s.emit('join_room', { roomCode:code, userId:user?._id, userName:user?.name, teamId:mySchool })
    s.on('room_updated', ({ participants, teams }) => {
      const pA = participants.filter(p=>p.teamId==='A').sort((a,b)=>b.score-a.score)
      const pB = participants.filter(p=>p.teamId==='B').sort((a,b)=>b.score-a.score)
      setLeaderA(pA); setLeaderB(pB); setTeamScores(teams||[])
    })
    s.on('game_started', ({question:q,questionIndex,totalQuestions}) => {
      setView('live'); setQuestion(q); setQIndex(questionIndex); setTotalQ(totalQuestions)
      setAnswered(false); setSelected(null)
    })
    s.on('next_question', ({question:q,questionIndex,teamScores:ts}) => {
      setQuestion(q); setQIndex(questionIndex)
      setAnswered(false); setSelected(null); setLastResult(null)
      if(ts)setTeamScores(ts)
    })
    s.on('answer_result', ({correct,points}) => { setLastResult({correct,points}); if(correct) toast.success(`+${points}`,{duration:800}) })
    s.on('score_updated', ({leaderboard:lb,teamScores:ts}) => {
      if(ts)setTeamScores(ts)
      if(lb){setLeaderA(lb.filter(p=>p.teamId==='A'));setLeaderB(lb.filter(p=>p.teamId==='B'))}
    })
    s.on('cheat_detected', ({userId:uid,userName:uname,penalty,teamScores:ts}) => {
      setCheatLog(prev=>[{uid,uname,penalty,time:Date.now()},...prev.slice(0,9)])
      if(ts)setTeamScores(ts)
    })
    s.on('game_ended', ({leaderboard:lb,teamScores:ts,winner}) => {
      setView('finished'); setGameResult({lb,ts,winner})
    })
  }

  const createRoom = async () => {
    if (!user?.college) return toast.error('Please set your school name in profile first')
    setLoading(true)
    try {
      let questions = []
      // Generate quiz
      if (quizSource === 'topic') {
        const r = await api.post('/ai/generate-topic', { topic, subject:'General', count:qCount, difficulty:diff })
        questions = r.data.questions
      } else if (quizSource === 'pdf' && pdfFile) {
        const fd = new FormData(); fd.append('pdf',pdfFile); fd.append('count',qCount); fd.append('difficulty',diff)
        const r = await api.post('/ai/generate-pdf', fd)
        questions = r.data.questions
      }
      if (!questions.length) return toast.error('Generate quiz questions first')

      const res = await api.post('/battle/create-with-questions', {
        type:'school', questions, subject:'General', difficulty:diff,
        totalQ:qCount, teamAName:user.college, teamBName:'Opposing School',
        maxStudentsPerSchool:maxStudents,
      })
      setRoom(res.data.room)
      connectSocketForRoom(res.data.room.code)
      setView('lobby')
      toast.success('School battle room created! 🏫')
    } catch(err) { toast.error(err.response?.data?.message||'Failed') }
    finally { setLoading(false) }
  }

  const joinRoom = async e => {
    e.preventDefault()
    if (!joinCode.trim()) return
    setLoading(true)
    try {
      const r = await api.post(`/battle/${joinCode.toUpperCase()}/join-school`, { schoolName:user.college })
      setRoom(r.data.room)
      connectSocketForRoom(joinCode.toUpperCase())
      setView('lobby')
      toast.success('Joined school battle! 🏫')
    } catch(err) { toast.error(err.response?.data?.message||'Failed') }
    finally { setLoading(false) }
  }

  const startBattle = () => socketRef.current?.emit('start_game', { roomCode:room?.code })
  const copyInvite = (code2) => { navigator.clipboard.writeText(code2); toast.success('Code copied!') }
  const handleAnswer = idx => {
    if (answered) return
    clearInterval(timerRef.current); setAnswered(true); setSelected(idx)
    socketRef.current?.emit('submit_answer', { roomCode:room?.code, userId:user?._id, questionIndex:qIndex, answerIndex:idx, timeTaken:15-timeLeft })
  }

  const pct = (timeLeft/15)*100
  const teamA = teamScores.find(t=>t.id==='A')
  const teamB = teamScores.find(t=>t.id==='B')

  /* ── Menu ── */
  if (view === 'menu') return (
    <div style={{background:'var(--dark)',minHeight:'100vh'}}>
      <div className="orb o1"/><div className="orb o2"/>
      <div className="page anim-fade">
        <div style={{marginBottom:32,textAlign:'center'}}>
          <span className="badge bo" style={{marginBottom:12,display:'inline-flex'}}>Institution Exclusive</span>
          <h1 style={{fontFamily:'var(--fh)',fontSize:'2rem',fontWeight:800,marginBottom:8}}>🏫 School vs School Battle</h1>
          <p className="text-muted" style={{fontSize:'.9rem'}}>Epic live battles between two institutions — up to 100+ students per school</p>
        </div>
        <div className="grid-2 gap-20">
          <div className="card" style={{background:'linear-gradient(135deg,rgba(255,184,0,.07),rgba(124,92,252,.05))',borderColor:'rgba(255,184,0,.25)'}}>
            <h2 style={{fontFamily:'var(--fh)',fontWeight:800,fontSize:'1.1rem',marginBottom:8}}>🎯 Host a Battle</h2>
            <p className="text-muted mb-20" style={{fontSize:'.83rem'}}>Create room, generate quiz, invite opposing school</p>
            <div className="flex-col gap-10 mb-20">
              {['Generate AI quiz from topic or PDF','Set student limit (2–100+ per school)','Get separate invite codes for each school','Start when both schools are ready','Live leaderboard + anti-cheat + trophies'].map((f,i)=>(
                <div key={i} style={{display:'flex',gap:8,fontSize:'.82rem',color:'var(--muted)'}}>
                  <span style={{color:'var(--green)',flexShrink:0}}>✓</span>{f}
                </div>
              ))}
            </div>
            <button className="btn btn-grad w-full" onClick={()=>setView('create')}>+ Create School Battle →</button>
          </div>
          <div className="card">
            <h2 style={{fontFamily:'var(--fh)',fontWeight:800,fontSize:'1.1rem',marginBottom:8}}>🔗 Join a Battle</h2>
            <p className="text-muted mb-20" style={{fontSize:'.83rem'}}>Enter the room code from the host school</p>
            <form onSubmit={joinRoom}>
              <div className="form-float">
                <input type="text" placeholder=" " value={joinCode} onChange={e=>setJoinCode(e.target.value.toUpperCase())} style={{fontSize:'1.1rem',fontFamily:'var(--fh)',fontWeight:700,letterSpacing:2}}/>
                <label>Room Code from Host School</label>
              </div>
              <button type="submit" className="btn btn-grad w-full" style={{padding:13,marginTop:8}} disabled={loading||!joinCode.trim()}>
                {loading?<span className="spinner"/>:'🏫 Join School Battle'}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  )

  /* ── Create ── */
  if (view === 'create') return (
    <div style={{background:'var(--dark)',minHeight:'100vh'}}>
      <div className="orb o1"/><div className="orb o2"/>
      <div className="page page-md anim-fade">
        <button className="btn btn-ghost btn-sm mb-20" onClick={()=>setView('menu')}>← Back</button>
        <h1 style={{fontFamily:'var(--fh)',fontSize:'1.6rem',fontWeight:800,marginBottom:24}}>🏫 Create School Battle</h1>
        <div className="card">
          <div className="form-float">
            <input type="text" value={user?.college||''} disabled style={{opacity:.6}}/>
            <label>Your School (from profile)</label>
          </div>
          <div style={{marginBottom:16}}>
            <div style={{fontSize:'.72rem',color:'var(--muted)',fontFamily:'var(--fh)',fontWeight:700,letterSpacing:'.5px',textTransform:'uppercase',marginBottom:10}}>Quiz Source</div>
            <div style={{display:'flex',gap:8}}>
              {['topic','pdf'].map(s=>(
                <button key={s} onClick={()=>setQuizSource(s)} className={`subject-pill${quizSource===s?' active':''}`} style={{textTransform:'capitalize'}}>{s==='topic'?'📝 By Topic':'📄 From PDF'}</button>
              ))}
            </div>
          </div>
          {quizSource==='topic'
            ? <div className="form-float"><input type="text" placeholder=" " value={topic} onChange={e=>setTopic(e.target.value)}/><label>Topic (e.g. "Human Body")</label></div>
            : <div style={{marginBottom:16}}>
                <div className="drop-zone" onClick={()=>pdfRef.current?.click()} style={pdfFile?{borderColor:'var(--green)'}:{}}>
                  <input ref={pdfRef} type="file" accept=".pdf" style={{display:'none'}} onChange={e=>setPdfFile(e.target.files[0])}/>
                  <div style={{fontSize:'2rem',marginBottom:6}}>{pdfFile?'✅':'📁'}</div>
                  <div style={{fontSize:'.85rem',fontWeight:600}}>{pdfFile?pdfFile.name:'Click to upload PDF'}</div>
                </div>
              </div>
          }
          <div className="grid-2 gap-8 mb-8">
            <div className="form-float" style={{marginBottom:0}}>
              <input type="number" placeholder=" " value={qCount} onChange={e=>setQCount(Number(e.target.value))} min={5} max={30}/>
              <label>Questions (5–30)</label>
            </div>
            <div className="form-float" style={{marginBottom:0}}>
              <select value={diff} onChange={e=>setDiff(e.target.value)}>
                <option value="beginner">Beginner</option>
                <option value="intermediate">Intermediate</option>
                <option value="advanced">Advanced</option>
              </select>
              <label>Difficulty</label>
            </div>
          </div>
          <div className="form-float">
            <input type="number" placeholder=" " value={maxStudents} onChange={e=>setMaxStudents(Number(e.target.value))} min={2} max={200}/>
            <label>Max Students Per School (2–200)</label>
          </div>
          <button className="btn btn-grad w-full btn-lg" onClick={createRoom} disabled={loading}>
            {loading?<><span className="spinner"/> Creating...</>:'🚀 Create Battle Room'}
          </button>
        </div>
      </div>
    </div>
  )

  /* ── Lobby ── */
  if (view === 'lobby') return (
    <div style={{background:'var(--dark)',minHeight:'100vh'}}>
      <div className="orb o1"/><div className="orb o2"/>
      <div className="page page-md anim-fade">
        <div style={{textAlign:'center',marginBottom:28}}>
          <div style={{fontFamily:'var(--fh)',fontWeight:700,color:'var(--gold)',fontSize:'.8rem',marginBottom:8}}>⏳ WAITING FOR BOTH SCHOOLS</div>
          <h1 style={{fontFamily:'var(--fh)',fontSize:'1.6rem',fontWeight:800,marginBottom:6}}>School Battle Lobby</h1>
          <p className="text-muted" style={{fontSize:'.82rem'}}>Room: <strong style={{color:'var(--violet)',letterSpacing:1}}>{room?.code}</strong></p>
        </div>
        <div className="grid-2 gap-12 mb-20">
          {room?.teams?.map((t,i)=>(
            <div key={i} className="card" style={{textAlign:'center'}}>
              <div style={{fontFamily:'var(--fh)',fontWeight:800,marginBottom:8,color:i===0?'var(--violet)':'var(--cyan)',fontSize:'.95rem'}}>{t.name}</div>
              <div style={{fontFamily:'var(--fh)',fontWeight:800,fontSize:'1.3rem',letterSpacing:2,marginBottom:6}}>{t.inviteCode}</div>
              <div style={{fontSize:'.75rem',color:'var(--muted)',marginBottom:10}}>
                {i===0?leaderA.length:leaderB.length} / {room.maxStudentsPerSchool} students joined
              </div>
              <button className="btn btn-ghost btn-sm" onClick={()=>copyInvite(t.inviteCode)}>📋 Copy Code</button>
            </div>
          ))}
        </div>
        <div className="grid-2 gap-12 mb-20">
          <div className="card">
            <div style={{fontFamily:'var(--fh)',fontWeight:700,fontSize:'.82rem',marginBottom:10,color:'var(--violet)'}}>{room?.schoolAName} ({leaderA.length})</div>
            {leaderA.map((p,i)=>(
              <div key={i} style={{display:'flex',alignItems:'center',gap:8,padding:'7px 10px',borderRadius:8,background:'rgba(255,255,255,.02)'}}>
                <div className="av av-p" style={{width:26,height:26,fontSize:'.72rem'}}>{p.name?.[0]}</div>
                <span style={{fontSize:'.82rem'}}>{p.name}</span>
              </div>
            ))}
            {leaderA.length===0&&<p className="text-muted" style={{fontSize:'.78rem'}}>Waiting for students...</p>}
          </div>
          <div className="card">
            <div style={{fontFamily:'var(--fh)',fontWeight:700,fontSize:'.82rem',marginBottom:10,color:'var(--cyan)'}}>{room?.schoolBName||'Opposing School'} ({leaderB.length})</div>
            {leaderB.map((p,i)=>(
              <div key={i} style={{display:'flex',alignItems:'center',gap:8,padding:'7px 10px',borderRadius:8,background:'rgba(255,255,255,.02)'}}>
                <div className="av av-c" style={{width:26,height:26,fontSize:'.72rem'}}>{p.name?.[0]}</div>
                <span style={{fontSize:'.82rem'}}>{p.name}</span>
              </div>
            ))}
            {leaderB.length===0&&<p className="text-muted" style={{fontSize:'.78rem',paddingLeft:4}}>{room?.schoolBId?'Waiting for students...':'⏳ Opposing school not joined yet'}</p>}
          </div>
        </div>
        {isHost
          ? <button className="btn btn-grad w-full btn-lg" onClick={startBattle} disabled={!room?.schoolBId}>
              {!room?.schoolBId?'⏳ Waiting for opposing school to join...':'⚔️ Start School Battle!'}
            </button>
          : <div style={{textAlign:'center',color:'var(--muted)',fontSize:'.85rem',padding:16}}>⏳ Waiting for host to start the battle...</div>
        }
      </div>
    </div>
  )

  /* ── Live ── */
  if (view === 'live') return (
    <div style={{background:'var(--dark)',minHeight:'100vh'}}>
      <div className="orb o1"/><div className="orb o2"/>
      <div className="page page-md anim-fade" style={{paddingTop:20}}>
        {/* Header */}
        <div style={{display:'flex',justifyContent:'space-between',alignItems:'center',marginBottom:16,flexWrap:'wrap',gap:10}}>
          <div style={{display:'flex',alignItems:'center',gap:8}}>
            <div className="live-dot"/><span style={{fontFamily:'var(--fh)',fontWeight:700,color:'var(--green)',fontSize:'.75rem'}}>LIVE SCHOOL BATTLE</span>
          </div>
          <span className="badge br">🛡️ Anti-cheat ON</span>
        </div>

        {/* Cheat alerts */}
        {cheatLog.slice(0,2).map((a,i)=>(
          <div key={i} className="anticheat-alert mb-10">
            <span>⚠️</span>
            <span><strong>{a.uname}</strong> switched tabs{a.penalty>0?` — Team –${a.penalty} pts`:' — Warning!'}</span>
          </div>
        ))}

        {/* Score board */}
        <div style={{display:'flex',alignItems:'stretch',gap:0,background:'rgba(255,255,255,.03)',border:'1px solid var(--border)',borderRadius:16,marginBottom:18,overflow:'hidden'}}>
          <div style={{flex:1,padding:'18px 20px',textAlign:'center',borderRight:'1px solid var(--border)'}}>
            <div style={{fontSize:'.8rem',color:'var(--muted)',marginBottom:4,fontFamily:'var(--fh)',fontWeight:700}}>{teamA?.name||room?.schoolAName}</div>
            <div style={{fontFamily:'var(--fh)',fontWeight:800,fontSize:'2rem',background:'var(--gradient)',WebkitBackgroundClip:'text',WebkitTextFillColor:'transparent'}}>{teamA?.score||0}</div>
            <div style={{fontSize:'.72rem',color:'var(--muted)',marginTop:4}}>{leaderA.length} students</div>
          </div>
          <div style={{padding:'18px 16px',display:'flex',alignItems:'center',color:'var(--muted)',fontFamily:'var(--fh)',fontWeight:800,fontSize:'1.1rem'}}>VS</div>
          <div style={{flex:1,padding:'18px 20px',textAlign:'center',borderLeft:'1px solid var(--border)'}}>
            <div style={{fontSize:'.8rem',color:'var(--muted)',marginBottom:4,fontFamily:'var(--fh)',fontWeight:700}}>{teamB?.name||room?.schoolBName}</div>
            <div style={{fontFamily:'var(--fh)',fontWeight:800,fontSize:'2rem',color:'var(--cyan)'}}>{teamB?.score||0}</div>
            <div style={{fontSize:'.72rem',color:'var(--muted)',marginTop:4}}>{leaderB.length} students</div>
          </div>
        </div>

        {/* Q progress */}
        <div style={{display:'flex',justifyContent:'space-between',alignItems:'center',marginBottom:10}}>
          <span className="badge bp">Q {qIndex+1}/{totalQ}</span>
          <div className={`timer-ring${timeLeft<=5?' danger':''}`}>{timeLeft}</div>
          <span className="badge badge-ai">+pts per correct</span>
        </div>
        <div style={{height:4,background:'rgba(255,255,255,.06)',borderRadius:4,marginBottom:18,overflow:'hidden'}}>
          <div style={{width:`${pct}%`,height:'100%',background:timeLeft<=5?'var(--red)':'var(--gradient)',borderRadius:4,transition:'width 1s linear'}}/>
        </div>

        {/* Question */}
        {question && (
          <>
            <div className="card mb-16 anim-pop" style={{padding:24}}>
              {question.topic && <span className="badge bp mb-12" style={{display:'inline-flex'}}>{question.topic}</span>}
              <h2 style={{fontSize:'1.05rem',lineHeight:1.7,fontWeight:600}}>{question.question}</h2>
            </div>
            <div className="flex-col gap-10 mb-16">
              {question.options?.map((opt,i)=>{
                let c2='qo anim-fade'
                if(answered&&lastResult){if(i===question.correctAnswer)c2+=' correct';else if(i===selected)c2+=' wrong'}
                return <button key={i} className={c2} style={{animationDelay:`${i*.06}s`}} onClick={()=>handleAnswer(i)} disabled={answered}><span className="ol">{LETTERS[i]}</span>{opt}</button>
              })}
            </div>
            {answered&&lastResult&&(
              <div className="card anim-fade" style={{borderColor:lastResult.correct?'rgba(0,227,150,.25)':'rgba(255,77,106,.2)'}}>
                <span style={{fontWeight:700,color:lastResult.correct?'var(--green)':'var(--red)'}}>{lastResult.correct?`✅ Correct! +${lastResult.points}`:'❌ Wrong'}</span>
                <p style={{fontSize:'.75rem',color:'var(--muted)',marginTop:6}}>⏳ Next question loading...</p>
              </div>
            )}
          </>
        )}

        {/* Mini leaderboards */}
        <div className="grid-2 gap-12 mt-16">
          <div className="card">
            <div style={{fontFamily:'var(--fh)',fontWeight:700,fontSize:'.8rem',marginBottom:8,color:'var(--violet)'}}>🏫 {teamA?.name}</div>
            {leaderA.slice(0,5).map((p,i)=>(
              <div key={i} style={{display:'flex',alignItems:'center',gap:8,padding:'5px 0',borderBottom:'1px solid var(--border)'}}>
                <span style={{fontSize:'.72rem',fontFamily:'var(--fh)',fontWeight:700,width:16,color:i===0?'var(--gold)':'var(--muted)'}}>{i+1}</span>
                <span style={{flex:1,fontSize:'.78rem',color:p.cheated?'var(--red)':'var(--text)'}}>{p.name}{p.cheated?' ⚠️':''}</span>
                <span style={{fontFamily:'var(--fh)',fontWeight:700,fontSize:'.78rem',color:'var(--violet)'}}>{p.score}</span>
              </div>
            ))}
          </div>
          <div className="card">
            <div style={{fontFamily:'var(--fh)',fontWeight:700,fontSize:'.8rem',marginBottom:8,color:'var(--cyan)'}}>🏫 {teamB?.name}</div>
            {leaderB.slice(0,5).map((p,i)=>(
              <div key={i} style={{display:'flex',alignItems:'center',gap:8,padding:'5px 0',borderBottom:'1px solid var(--border)'}}>
                <span style={{fontSize:'.72rem',fontFamily:'var(--fh)',fontWeight:700,width:16,color:i===0?'var(--gold)':'var(--muted)'}}>{i+1}</span>
                <span style={{flex:1,fontSize:'.78rem',color:p.cheated?'var(--red)':'var(--text)'}}>{p.name}{p.cheated?' ⚠️':''}</span>
                <span style={{fontFamily:'var(--fh)',fontWeight:700,fontSize:'.78rem',color:'var(--cyan)'}}>{p.score}</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  )

  /* ── Finished ── */
  if (view === 'finished' && gameResult) {
    const wonTeam = gameResult.ts?.sort((a,b)=>b.score-a.score)[0]
    return (
      <div style={{background:'var(--dark)',minHeight:'100vh'}}>
        <div className="orb o1"/><div className="orb o2"/>
        <div className="page page-md anim-pop" style={{paddingTop:40,textAlign:'center'}}>
          <div style={{fontSize:'5rem',marginBottom:16}}>🏆</div>
          <h1 style={{fontFamily:'var(--fh)',fontSize:'2.2rem',fontWeight:800,marginBottom:8}}>{wonTeam?.name} Wins!</h1>
          <p className="text-muted mb-32" style={{fontSize:'.9rem'}}>School Battle Complete</p>
          <div style={{display:'flex',justifyContent:'center',gap:16,marginBottom:32}}>
            {gameResult.ts?.sort((a,b)=>b.score-a.score).map((t,i)=>(
              <div key={i} className="stat-card" style={{textAlign:'center',minWidth:130,background:i===0?'rgba(124,92,252,.12)':'var(--card)',borderColor:i===0?'rgba(124,92,252,.4)':'var(--border)'}}>
                <div style={{fontSize:'1.5rem',marginBottom:4}}>{i===0?'🏆':'🥈'}</div>
                <div className={`stat-val ${i===0?'text-grad':''}`} style={{fontSize:'1.4rem',color:i!==0?'var(--cyan)':undefined}}>{t.score}</div>
                <div className="stat-label">{t.name}</div>
              </div>
            ))}
          </div>
          <div style={{display:'flex',gap:12,justifyContent:'center',flexWrap:'wrap'}}>
            <button className="btn btn-grad" onClick={()=>setView('menu')}>🏫 New School Battle</button>
            <button className="btn btn-ghost" onClick={()=>navigate('/dashboard')}>Dashboard</button>
          </div>
        </div>
      </div>
    )
  }

  return null
}
