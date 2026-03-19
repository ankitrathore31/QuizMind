import { useState, useEffect, useRef } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { connectSocket, disconnectSocket } from '../utils/socket'
import api from '../utils/api'
import toast from 'react-hot-toast'

const LETTERS = ['A','B','C','D']

export default function BattleRoomPage() {
  const { code }   = useParams()
  const { user }   = useAuth()
  const navigate   = useNavigate()
  const socketRef  = useRef(null)

  const [room,       setRoom]       = useState(null)
  const [status,     setStatus]     = useState('loading') // loading | waiting | live | finished
  const [question,   setQuestion]   = useState(null)
  const [qIndex,     setQIndex]     = useState(0)
  const [totalQ,     setTotalQ]     = useState(0)
  const [timeLeft,   setTimeLeft]   = useState(15)
  const [answered,   setAnswered]   = useState(false)
  const [selected,   setSelected]   = useState(null)
  const [lastResult, setLastResult] = useState(null)
  const [leaderboard,setLeaderboard]= useState([])
  const [teamScores, setTeamScores] = useState([])
  const [cheatAlerts,setCheatAlerts]= useState([])
  const [gameResult, setGameResult] = useState(null)
  const [myScore,    setMyScore]    = useState(0)
  const timerRef = useRef(null)
  const isHost   = room?.hostId?._id === user?._id || room?.hostId === user?._id

  // Anti-cheat: tab switch detection
  useEffect(() => {
    if (status !== 'live') return
    const onHide = () => {
      if (document.hidden) {
        socketRef.current?.emit('cheat_event', { roomCode:code, userId:user?._id, userName:user?.name, type:'tab_switch' })
        toast.error('⚠️ Tab switch detected! Points may be deducted.')
      }
    }
    const onBlur = () => {
      socketRef.current?.emit('cheat_event', { roomCode:code, userId:user?._id, userName:user?.name, type:'window_blur' })
    }
    document.addEventListener('visibilitychange', onHide)
    window.addEventListener('blur', onBlur)
    return () => { document.removeEventListener('visibilitychange', onHide); window.removeEventListener('blur', onBlur) }
  }, [status])

  // Timer
  useEffect(() => {
    if (status !== 'live' || !question) return
    clearInterval(timerRef.current)
    setTimeLeft(15)
    timerRef.current = setInterval(() => {
      setTimeLeft(t => {
        if (t <= 1) { clearInterval(timerRef.current); if (!answered) handleAnswer(-1); return 0 }
        return t - 1
      })
    }, 1000)
    return () => clearInterval(timerRef.current)
  }, [question, status])

  // Socket setup
  useEffect(() => {
    const s = connectSocket()
    socketRef.current = s

    api.get(`/battle/${code}`).then(r => {
      setRoom(r.data)
      setStatus(r.data.status === 'live' ? 'live' : r.data.status === 'finished' ? 'finished' : 'waiting')
      setTeamScores(r.data.teams || [])
      setLeaderboard(r.data.participants?.sort((a,b)=>b.score-a.score) || [])
    }).catch(() => toast.error('Room not found'))

    s.emit('join_room', { roomCode:code, userId:user?._id, userName:user?.name })

    s.on('room_updated', ({ participants, teams, status:st }) => {
      setLeaderboard([...participants].sort((a,b)=>b.score-a.score))
      setTeamScores(teams||[])
      if (st) setStatus(st)
    })

    s.on('joined', ({ room:r }) => { setRoom(r); setTeamScores(r.teams||[]) })

    s.on('game_started', ({ question:q, questionIndex, totalQuestions, timeLimit }) => {
      setStatus('live'); setQuestion(q); setQIndex(questionIndex); setTotalQ(totalQuestions)
      setAnswered(false); setSelected(null); setLastResult(null)
      toast.success('Battle started! ⚡', { duration:1500 })
    })

    s.on('next_question', ({ question:q, questionIndex, leaderboard:lb, teamScores:ts }) => {
      setQuestion(q); setQIndex(questionIndex)
      setAnswered(false); setSelected(null); setLastResult(null)
      if (lb) setLeaderboard(lb)
      if (ts) setTeamScores(ts)
    })

    s.on('answer_result', ({ correct, points, explanation }) => {
      setLastResult({ correct, points, explanation })
      if (correct) { setMyScore(s2 => s2 + points); toast.success(`+${points} pts!`, { duration:1000 }) }
    })

    s.on('score_updated', ({ leaderboard:lb, teamScores:ts }) => {
      if (lb) setLeaderboard(lb)
      if (ts) setTeamScores(ts)
    })

    s.on('cheat_detected', ({ userId:uid, userName:uname, penalty, teamScores:ts }) => {
      setCheatAlerts(prev => [{ uid, uname, penalty, time:Date.now() }, ...prev.slice(0,4)])
      if (ts) setTeamScores(ts)
      toast.error(`⚠️ ${uname} caught cheating!`, { duration:2000 })
    })

    s.on('game_ended', ({ leaderboard:lb, teamScores:ts, winner }) => {
      setStatus('finished')
      setGameResult({ leaderboard:lb, teamScores:ts, winner })
      if (lb) setLeaderboard(lb)
    })

    s.on('error', ({ message }) => toast.error(message))

    return () => {
      clearInterval(timerRef.current)
      s.emit('leave_room', { roomCode:code })
      s.off('room_updated'); s.off('joined'); s.off('game_started')
      s.off('next_question'); s.off('answer_result'); s.off('score_updated')
      s.off('cheat_detected'); s.off('game_ended'); s.off('error')
    }
  }, [code])

  const handleAnswer = (idx) => {
    if (answered) return
    clearInterval(timerRef.current)
    setAnswered(true); setSelected(idx)
    socketRef.current?.emit('submit_answer', { roomCode:code, userId:user?._id, questionIndex:qIndex, answerIndex:idx, timeTaken:15-timeLeft })
  }

  const startGame = () => socketRef.current?.emit('start_game', { roomCode:code })
  const copyInvite = (inviteCode) => { navigator.clipboard.writeText(`${window.location.origin}/battle/${inviteCode || code}`); toast.success('Link copied!') }

  const pct = (timeLeft/15)*100
  const myEntry = leaderboard.find(p => p.userId?.toString?.()===user?._id?.toString?.() || p.userId===user?._id)
  const myTeam  = room?.teams?.find(t => room.participants?.find(p => p.userId?.toString?.()===user?._id?.toString?.())?.teamId === t.id)

  /* ── Loading ── */
  if (status === 'loading') return (
    <div style={{ display:'flex', alignItems:'center', justifyContent:'center', minHeight:'100vh', background:'var(--dark)' }}>
      <span className="spinner"/>
    </div>
  )

  /* ── Finished ── */
  if (status === 'finished' && gameResult) {
    const myRank = gameResult.leaderboard?.findIndex(p => p.userId?.toString?.()===user?._id?.toString?.())+1
    const won = gameResult.leaderboard?.[0]?.userId?.toString?.()===user?._id?.toString?.()
    return (
      <div style={{ background:'var(--dark)', minHeight:'100vh' }}>
        <div className="orb o1"/><div className="orb o2"/>
        <div className="page page-md anim-pop" style={{ paddingTop:40, textAlign:'center' }}>
          <div style={{ fontSize:'4rem', marginBottom:16 }}>{won?'🏆':myRank<=3?'🥈':'🎯'}</div>
          <h1 style={{ fontFamily:'var(--fh)', fontSize:'2rem', fontWeight:800, marginBottom:8 }}>
            {gameResult.winner ? `${gameResult.winner} wins!` : 'Battle Over!'}
          </h1>
          <p className="text-muted mb-28" style={{ fontSize:'.9rem' }}>Your rank: #{myRank} — Score: {myEntry?.score||myScore}</p>

          {/* Team scores */}
          {gameResult.teamScores?.length>0 && (
            <div style={{ display:'flex', justifyContent:'center', gap:16, marginBottom:24 }}>
              {gameResult.teamScores.map((t,i) => (
                <div key={i} className="stat-card" style={{ minWidth:120, background:gameResult.winner===t.name?'rgba(124,92,252,.12)':'var(--card)', borderColor:gameResult.winner===t.name?'rgba(124,92,252,.4)':'var(--border)' }}>
                  <div className="stat-val" style={{ color:gameResult.winner===t.name?'var(--violet)':'var(--text)', fontSize:'1.4rem' }}>{t.score}</div>
                  <div className="stat-label">{t.name}{gameResult.winner===t.name?' 🏆':''}</div>
                </div>
              ))}
            </div>
          )}

          {/* Leaderboard */}
          <div className="card" style={{ textAlign:'left', marginBottom:24 }}>
            <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.88rem', marginBottom:14 }}>🏆 Final Leaderboard</div>
            <div className="flex-col gap-6">
              {gameResult.leaderboard?.slice(0,10).map((p,i) => (
                <div key={i} style={{ display:'flex', alignItems:'center', gap:12, padding:'10px 14px', background:p.userId?.toString?.()===user?._id?.toString?.()?'rgba(124,92,252,.08)':'rgba(255,255,255,.02)', borderRadius:10, border:p.userId?.toString?.()===user?._id?.toString?.()? '1px solid rgba(124,92,252,.3)':'1px solid transparent' }}>
                  <span style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'1rem', width:24, color:i===0?'var(--gold)':i===1?'var(--muted)':i===2?'var(--orange)':'var(--muted)' }}>{i===0?'🥇':i===1?'🥈':i===2?'🥉':`#${i+1}`}</span>
                  <div style={{ flex:1 }}>
                    <div style={{ fontSize:'.86rem', fontWeight:600 }}>{p.name}{p.cheated?' ⚠️':''}</div>
                    {p.teamId && <div style={{ fontSize:'.7rem', color:'var(--muted)' }}>Team {p.teamId}</div>}
                  </div>
                  <span style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'.9rem', color:'var(--violet)' }}>{p.score} pts</span>
                </div>
              ))}
            </div>
          </div>

          <div style={{ display:'flex', gap:12, justifyContent:'center', flexWrap:'wrap' }}>
            <button className="btn btn-grad" onClick={() => navigate('/ai-quiz')}>🤖 New Battle</button>
            <button className="btn btn-ghost" onClick={() => navigate('/dashboard')}>Dashboard</button>
            <button className="btn btn-ghost" onClick={() => navigate('/leaderboard')}>🏆 Leaderboard</button>
          </div>
        </div>
      </div>
    )
  }

  /* ── Waiting room ── */
  if (status === 'waiting') {
    return (
      <div style={{ background:'var(--dark)', minHeight:'100vh' }}>
        <div className="orb o1"/><div className="orb o2"/>
        <div className="page page-md anim-fade">
          <div style={{ textAlign:'center', marginBottom:32 }}>
            <div style={{ display:'flex', alignItems:'center', justifyContent:'center', gap:10, marginBottom:12 }}>
              <div className="live-dot" style={{ background:'var(--gold)', boxShadow:'0 0 10px var(--gold)' }}/>
              <span style={{ fontFamily:'var(--fh)', fontWeight:700, color:'var(--gold)', fontSize:'.8rem' }}>WAITING</span>
            </div>
            <h1 style={{ fontFamily:'var(--fh)', fontSize:'1.8rem', fontWeight:800, marginBottom:6 }}>Battle Lobby</h1>
            <p className="text-muted mb-4" style={{ fontSize:'.85rem' }}>Room: <strong style={{ color:'var(--violet)', letterSpacing:1 }}>{code}</strong></p>
            <p className="text-muted" style={{ fontSize:'.8rem' }}>{leaderboard.length} player{leaderboard.length!==1?'s':''} joined</p>
          </div>

          {/* Team invite codes */}
          {room?.teams?.length>0 && (
            <div className="grid-2 gap-12 mb-20">
              {room.teams.map((t,i) => (
                <div key={i} className="card" style={{ textAlign:'center', background:myTeam?.id===t.id?'rgba(124,92,252,.08)':'var(--card)', borderColor:myTeam?.id===t.id?'rgba(124,92,252,.3)':'var(--border)' }}>
                  <div style={{ fontFamily:'var(--fh)', fontWeight:800, marginBottom:4, color:i===0?'var(--violet)':'var(--cyan)' }}>{t.name}</div>
                  <div style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.1rem', letterSpacing:2, marginBottom:8, color:'var(--text)' }}>{t.inviteCode}</div>
                  <button className="btn btn-ghost btn-sm" onClick={() => copyInvite(t.inviteCode)}>📋 Copy Invite</button>
                </div>
              ))}
            </div>
          )}

          {/* Room code share */}
          {!room?.teams?.length && (
            <div className="card mb-20" style={{ textAlign:'center', background:'rgba(124,92,252,.06)', borderColor:'rgba(124,92,252,.2)' }}>
              <p className="text-muted mb-8" style={{ fontSize:'.8rem' }}>Share this code with players:</p>
              <div style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.6rem', letterSpacing:3, color:'var(--violet)', marginBottom:12 }}>{code}</div>
              <button className="btn btn-ghost btn-sm" onClick={() => copyInvite()}>📋 Copy Invite Link</button>
            </div>
          )}

          {/* Players list */}
          <div className="card mb-20">
            <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.88rem', marginBottom:14 }}>👥 Players in Room</div>
            <div className="flex-col gap-6">
              {leaderboard.length === 0 && <p className="text-muted" style={{ fontSize:'.82rem' }}>Waiting for players...</p>}
              {leaderboard.map((p,i) => (
                <div key={i} style={{ display:'flex', alignItems:'center', gap:10, padding:'9px 12px', background:'rgba(255,255,255,.02)', borderRadius:9 }}>
                  <div className={`av av-${['p','c','g','o'][i%4]}`} style={{ width:32, height:32, fontSize:'.75rem' }}>{p.name?.[0]}</div>
                  <span style={{ flex:1, fontSize:'.85rem', fontWeight:500 }}>{p.name}</span>
                  {p.teamId && <span className="badge bp">Team {p.teamId}</span>}
                  {(room?.hostId===p.userId||room?.hostId?._id===p.userId) && <span className="badge bo">Host</span>}
                </div>
              ))}
            </div>
          </div>

          {isHost ? (
            <button className="btn btn-grad w-full btn-lg" onClick={startGame} disabled={leaderboard.length < (room?.type==='1v1'?2:1)}>
              {room?.type==='solo'?'▶ Start Solo Quiz':'⚔️ Start Battle!'}
            </button>
          ) : (
            <div style={{ textAlign:'center', padding:16, color:'var(--muted)', fontSize:'.85rem' }}>
              ⏳ Waiting for host to start the battle...
            </div>
          )}
        </div>
      </div>
    )
  }

  /* ── Live battle ── */
  return (
    <div style={{ background:'var(--dark)', minHeight:'100vh' }}>
      <div className="orb o1"/><div className="orb o2"/>
      <div className="page page-md anim-fade" style={{ paddingTop:20 }}>

        {/* Header */}
        <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:16, flexWrap:'wrap', gap:10 }}>
          <div>
            <div style={{ display:'flex', alignItems:'center', gap:8 }}>
              <div className="live-dot"/>
              <span style={{ fontFamily:'var(--fh)', fontWeight:700, color:'var(--green)', fontSize:'.75rem' }}>LIVE</span>
              <span style={{ color:'var(--muted)', fontSize:'.75rem' }}>Room: {code}</span>
            </div>
            <div style={{ fontSize:'.82rem', marginTop:2, color:'var(--muted)' }}>{room?.subject || 'AI Quiz'} • {leaderboard.length} players</div>
          </div>
          <div style={{ display:'flex', gap:8, alignItems:'center' }}>
            <span className="badge br">🛡️ Anti-cheat ON</span>
            <span style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'.9rem', color:'var(--violet)' }}>#{(leaderboard.findIndex(p=>p.userId?.toString?.()===user?._id?.toString?.())||0)+1} • {myEntry?.score||myScore} pts</span>
          </div>
        </div>

        {/* Anti-cheat alerts */}
        {cheatAlerts.slice(0,2).map((a,i) => (
          <div key={i} className="anticheat-alert mb-10">
            <span style={{ fontSize:'1.2rem' }}>⚠️</span>
            <span><strong className="cheat-name">{a.uname}</strong> switched tabs{a.penalty>0?` — -${a.penalty} pts for their team`:' — Warning!'}</span>
          </div>
        ))}

        {/* Team scores */}
        {teamScores?.length>0 && (
          <div style={{ display:'flex', gap:12, marginBottom:16 }}>
            {teamScores.map((t,i) => (
              <div key={i} style={{ flex:1, background:myTeam?.id===t.id?'rgba(124,92,252,.1)':'rgba(255,255,255,.03)', border:`1px solid ${myTeam?.id===t.id?'rgba(124,92,252,.3)':'var(--border)'}`, borderRadius:12, padding:'12px 16px', textAlign:'center' }}>
                <div style={{ fontSize:'.75rem', color:'var(--muted)', marginBottom:4, fontFamily:'var(--fh)', fontWeight:700 }}>{t.name}</div>
                <div style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.4rem', color:i===0?'var(--violet)':'var(--cyan)' }}>{t.score}</div>
              </div>
            ))}
          </div>
        )}

        {/* Progress */}
        <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:10 }}>
          <span className="badge bp">Q {qIndex+1}/{totalQ}</span>
          <div className={`timer-ring${timeLeft<=5?' danger':''}`}>{timeLeft}</div>
          <span className="badge badge-ai">+pts per correct</span>
        </div>
        <div style={{ height:4, background:'rgba(255,255,255,.06)', borderRadius:4, marginBottom:18, overflow:'hidden' }}>
          <div style={{ width:`${pct}%`, height:'100%', background:timeLeft<=5?'var(--red)':'var(--gradient)', borderRadius:4, transition:'width 1s linear' }}/>
        </div>

        {/* Question */}
        {question && (
          <>
            <div className="card mb-16 anim-pop" style={{ padding:24 }}>
              {question.topic && <span className="badge bp mb-12" style={{ display:'inline-flex' }}>{question.topic}</span>}
              <h2 style={{ fontSize:'1.06rem', lineHeight:1.7, fontWeight:600 }}>{question.question}</h2>
            </div>
            <div className="flex-col gap-10 mb-16">
              {question.options?.map((opt,i) => {
                let cls2 = 'qo anim-fade'
                if (answered && lastResult) { if(i===question.correctAnswer) cls2+=' correct'; else if(i===selected) cls2+=' wrong' }
                return (
                  <button key={i} className={cls2} style={{ animationDelay:`${i*.06}s` }}
                    onClick={() => handleAnswer(i)} disabled={answered}>
                    <span className="ol">{LETTERS[i]}</span>{opt}
                  </button>
                )
              })}
            </div>
            {answered && lastResult && (
              <div className="card anim-fade" style={{ borderColor:lastResult.correct?'rgba(0,227,150,.25)':'rgba(255,77,106,.2)', background:lastResult.correct?'rgba(0,227,150,.04)':'rgba(255,77,106,.04)' }}>
                <div style={{ display:'flex', gap:8, marginBottom:8 }}>
                  <span>{lastResult.correct?'✅':'❌'}</span>
                  <span style={{ fontWeight:700, fontSize:'.86rem', color:lastResult.correct?'var(--green)':'var(--red)' }}>
                    {lastResult.correct?`Correct! +${lastResult.points} pts`:'Wrong answer'}
                  </span>
                </div>
                {lastResult.explanation && <p style={{ fontSize:'.82rem', color:'var(--muted)', lineHeight:1.6 }}>{lastResult.explanation}</p>}
                <p style={{ fontSize:'.75rem', color:'var(--muted)', marginTop:8 }}>⏳ Next question loading...</p>
              </div>
            )}
          </>
        )}

        {/* Live leaderboard sidebar */}
        {leaderboard.length > 0 && (
          <div className="card mt-16">
            <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.82rem', marginBottom:10 }}>📊 Live Rankings</div>
            <div className="flex-col gap-5">
              {leaderboard.slice(0,8).map((p,i) => (
                <div key={i} style={{ display:'flex', alignItems:'center', gap:8, padding:'7px 10px', background:p.userId?.toString?.()===user?._id?.toString?.()?'rgba(124,92,252,.08)':'transparent', borderRadius:8 }}>
                  <span style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'.8rem', width:20, color:i===0?'var(--gold)':i===1?'var(--muted)':i===2?'var(--orange)':'var(--muted)' }}>#{i+1}</span>
                  <span style={{ flex:1, fontSize:'.82rem', color:p.cheated?'var(--red)':'var(--text)' }}>{p.name}{p.cheated?' ⚠️':''}</span>
                  {p.teamId && <span style={{ fontSize:'.68rem', color:'var(--muted)' }}>T{p.teamId}</span>}
                  <span style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.8rem', color:'var(--violet)' }}>{p.score}</span>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
