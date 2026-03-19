import { useState, useRef, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import api from '../utils/api'
import toast from 'react-hot-toast'

const LETTERS = ['A','B','C','D']

const ALL_MODES = [
  { id:'solo',   icon:'⚡', label:'Solo Practice',    desc:'Practice alone, XP earned',      roles:['student','teacher','institution'] },
  { id:'1v1',    icon:'⚔️', label:'1v1 Battle',       desc:'Head-to-head with one opponent', roles:['student','teacher','institution'] },
  { id:'group',  icon:'👥', label:'Group Battle',     desc:'2–10 players compete live',      roles:['student','teacher','institution'] },
  { id:'team',   icon:'🏆', label:'Team vs Team',     desc:'Separate invite links per team', roles:['student','teacher','institution'] },
  { id:'school', icon:'🏫', label:'School vs School', desc:'2 schools • up to 100+ each',    roles:['institution'] },
]

export default function AIQuizPage() {
  const { user } = useAuth()
  const navigate  = useNavigate()

  const [tab,       setTab]      = useState('topic')
  const [loading,   setLoading]  = useState(false)
  const [creating,  setCreating] = useState(false)
  const [questions, setQuestions]= useState([])
  const [quizId,    setQuizId]   = useState(null)
  const [showModes, setShowModes]= useState(false)

  // Form state
  const [topic,   setTopic]  = useState('')
  const [subject, setSubject]= useState('Physics')
  const [count,   setCount]  = useState(10)
  const [diff,    setDiff]   = useState('intermediate')
  const [file,    setFile]   = useState(null)
  const [imgFile, setImgFile]= useState(null)
  const [imgPrev, setImgPrev]= useState(null)
  const [imgErr,  setImgErr] = useState('')
  const [teamA,   setTeamA]  = useState(user?.college || '')
  const [teamB,   setTeamB]  = useState('')

  // Solo play
  const [playing,  setPlaying] = useState(false)
  const [qIdx,     setQIdx]    = useState(0)
  const [score,    setScore]   = useState(0)
  const [answered, setAnswered]= useState(false)
  const [selected, setSelected]= useState(null)
  const [timeLeft, setTimeLeft]= useState(15)
  const [finished, setFinished]= useState(false)
  const [resLog,   setResLog]  = useState([])
  const timerRef = useRef(null)
  const fileRef  = useRef(); const imgRef = useRef()

  const startTimer = () => {
    clearInterval(timerRef.current); setTimeLeft(15)
    timerRef.current = setInterval(() => setTimeLeft(t => { if(t<=1){clearInterval(timerRef.current);handleAnswer(-1);return 0} return t-1 }), 1000)
  }
  useEffect(() => { if(playing&&questions.length) startTimer(); return ()=>clearInterval(timerRef.current) }, [qIdx,playing])

  const afterGen = (qs, id) => { setQuestions(qs); setQuizId(id); setShowModes(true) }

  /* ── Generation ── */
  const genTopic = async e => {
    e.preventDefault()
    if (!topic.trim()) return toast.error('Enter a topic')
    setLoading(true)
    try {
      const res = await api.post('/ai/generate-topic', { topic, subject, count, difficulty: diff })
      afterGen(res.data.questions, res.data.quizId)
      toast.success(`${res.data.count} questions ready! 🎉`)
    } catch(err) { toast.error(err.response?.data?.message||'Failed') }
    finally { setLoading(false) }
  }

  const genPDF = async () => {
    if (!file) return toast.error('Select a PDF')
    setLoading(true)
    const fd = new FormData(); fd.append('pdf',file); fd.append('count',count); fd.append('difficulty',diff)
    try {
      const res = await api.post('/ai/generate-pdf', fd)
      afterGen(res.data.questions, res.data.quizId)
      toast.success(`${res.data.count} questions from PDF! 📄`)
    } catch(err) { toast.error(err.response?.data?.message||'Failed') }
    finally { setLoading(false) }
  }

  const genImg = async () => {
    if (!imgFile) return toast.error('Select an image')
    setImgErr(''); setLoading(true)
    const fd = new FormData(); fd.append('image',imgFile); fd.append('count',count)
    try {
      const res = await api.post('/ai/generate-image', fd)
      afterGen(res.data.questions, res.data.quizId)
      toast.success(`${res.data.count} questions! (${res.data.detectedLabel})`)
    } catch(err) { const m=err.response?.data?.message||'Failed'; setImgErr(m); toast.error(m) }
    finally { setLoading(false) }
  }

  const onImgSelect = e => {
    const f=e.target.files[0]; if(!f) return
    if(!f.type.startsWith('image/')) { setImgErr('Upload JPG/PNG/WebP'); return }
    setImgFile(f); setImgErr('')
    const r=new FileReader(); r.onload=ev=>setImgPrev(ev.target.result); r.readAsDataURL(f)
  }

  /* ── Play ── */
  const handlePlayMode = async mode => {
    if (!questions.length) return toast.error('Generate questions first')
    if (mode==='solo') { startSolo(); return }
    if (mode==='school') { navigate('/school-battle'); return }
    if ((mode==='team')&&(!teamA.trim()||!teamB.trim())) return toast.error('Enter both team names')
    setCreating(true)
    try {
      const res = await api.post('/battle/create-with-questions', {
        type: mode, questions, quizId, subject, difficulty:diff,
        totalQ:questions.length, teamAName:teamA||user?.name, teamBName:teamB, maxPlayers:mode==='1v1'?2:10,
      })
      toast.success('Battle room created! 🎮')
      navigate(`/battle/${res.data.room.code}`)
    } catch(err) { toast.error(err.response?.data?.message||'Failed') }
    finally { setCreating(false) }
  }

  const startSolo = () => {
    setPlaying(true); setShowModes(false)
    setQIdx(0); setScore(0); setAnswered(false); setSelected(null); setFinished(false); setResLog([])
  }

  const handleAnswer = idx => {
    if (answered) return
    clearInterval(timerRef.current); setAnswered(true); setSelected(idx)
    if (idx===questions[qIdx]?.answer) setScore(s=>s+1)
    setResLog(r=>[...r,{idx,correct:idx===questions[qIdx]?.answer}])
  }

  const nextQ = () => {
    const finalScore = resLog.filter(r=>r.correct).length + (selected===questions[qIdx]?.answer?1:0)
    if (qIdx+1>=questions.length) {
      const acc=Math.round((finalScore/questions.length)*100)
      const xp=finalScore*20+(acc>=80?40:0)
      api.post('/ai/submit-solo',{score:finalScore,totalQ:questions.length,accuracy:acc,xpEarned:xp,difficulty:diff,subject,quizId}).catch(()=>{})
      toast.success(`Done! ${finalScore}/${questions.length} — ${acc}% 🎯`)
      setFinished(true); setPlaying(false); return
    }
    setQIdx(q=>q+1); setAnswered(false); setSelected(null)
  }

  const modes = ALL_MODES.filter(m => m.roles.includes(user?.role||'student'))

  /* ── Solo play screen ── */
  if (playing && questions.length) {
    const q=questions[qIdx]; const pct=(timeLeft/15)*100
    return (
      <div style={{background:'var(--dark)',minHeight:'100vh'}}>
        <div className="orb o1"/><div className="orb o2"/>
        <div className="page page-md anim-fade" style={{paddingTop:28}}>
          <div className="progress-track mb-12">
            <div className="progress-fill" style={{width:`${(qIdx/questions.length)*100}%`}}/>
          </div>
          <div style={{display:'flex',justifyContent:'space-between',alignItems:'center',marginBottom:14}}>
            <span className="badge bc">Q {qIdx+1}/{questions.length}</span>
            <div className={`timer-ring${timeLeft<=5?' danger':''}`}>{timeLeft}</div>
            <div style={{display:'flex',gap:8}}>
              <span className="badge bo">⚡ {score}</span>
              <button className="btn btn-ghost btn-sm" onClick={()=>{clearInterval(timerRef.current);setPlaying(false);setShowModes(true)}}>✕</button>
            </div>
          </div>
          <div style={{height:4,background:'rgba(255,255,255,.06)',borderRadius:4,marginBottom:22,overflow:'hidden'}}>
            <div style={{width:`${pct}%`,height:'100%',background:timeLeft<=5?'var(--red)':'var(--gradient)',borderRadius:4,transition:'width 1s linear'}}/>
          </div>
          <div className="card mb-16 anim-pop" style={{padding:24}}>
            <div style={{display:'flex',justifyContent:'space-between',marginBottom:12}}>
              <span className="badge bp">{q.topic||subject}</span>
              <span className="badge badge-ai">🤖 AI</span>
            </div>
            <h2 style={{fontSize:'1.05rem',lineHeight:1.7,fontWeight:600}}>{q.question}</h2>
          </div>
          <div className="flex-col gap-10 mb-16">
            {q.options.map((opt,i)=>{
              let c='qo anim-fade'
              if(answered){if(i===q.answer)c+=' correct';else if(i===selected)c+=' wrong'}
              return <button key={i} className={c} style={{animationDelay:`${i*.06}s`}} onClick={()=>handleAnswer(i)} disabled={answered}><span className="ol">{LETTERS[i]}</span>{opt}</button>
            })}
          </div>
          {answered && (
            <div className="card anim-fade" style={{borderColor:selected===q.answer?'rgba(0,227,150,.25)':'rgba(255,77,106,.2)',background:selected===q.answer?'rgba(0,227,150,.04)':'rgba(255,77,106,.04)'}}>
              <div style={{display:'flex',gap:8,marginBottom:8}}>
                <span>{selected===q.answer?'✅':'❌'}</span>
                <span style={{fontWeight:700,fontSize:'.86rem',color:selected===q.answer?'var(--green)':'var(--red)'}}>{selected===q.answer?'Correct!':'Wrong'}</span>
              </div>
              <p style={{fontSize:'.82rem',color:'var(--muted)',lineHeight:1.6}}>{q.explanation}</p>
              <button className="btn btn-grad btn-sm mt-12" onClick={nextQ}>{qIdx+1>=questions.length?'🏆 Results':'Next →'}</button>
            </div>
          )}
        </div>
      </div>
    )
  }

  /* ── Finished screen ── */
  if (finished) {
    const fs=resLog.filter(r=>r.correct).length; const acc=Math.round((fs/questions.length)*100)
    return (
      <div style={{background:'var(--dark)',minHeight:'100vh'}}>
        <div className="orb o1"/><div className="orb o2"/>
        <div className="page page-sm anim-pop" style={{paddingTop:60,textAlign:'center'}}>
          <div style={{fontSize:'4rem',marginBottom:16}}>{acc>=80?'🏆':acc>=60?'🎯':'📚'}</div>
          <h1 style={{fontFamily:'var(--fh)',fontSize:'2rem',fontWeight:800,marginBottom:8}}>{acc>=80?'Excellent!':acc>=60?'Good Job!':'Keep Practicing!'}</h1>
          <p className="text-muted mb-28">{topic||subject} AI Quiz</p>
          <div style={{display:'flex',justifyContent:'center',gap:20,marginBottom:32,flexWrap:'wrap'}}>
            {[{v:`${fs}/${questions.length}`,l:'Score',c:'text-grad'},{v:`${acc}%`,l:'Accuracy',c:acc>=80?'var(--green)':acc>=60?'var(--gold)':'var(--red)'},{v:`+${fs*20}`,l:'XP Earned',c:'var(--gold)'}].map((s,i)=>(
              <div key={i} className="stat-card" style={{textAlign:'center',minWidth:90}}>
                <div className={`stat-val ${s.c}`} style={{color:s.c.startsWith('var')?s.c:undefined,fontSize:'1.5rem'}}>{s.v}</div>
                <div className="stat-label">{s.l}</div>
              </div>
            ))}
          </div>
          <div style={{display:'flex',gap:12,justifyContent:'center',flexWrap:'wrap'}}>
            <button className="btn btn-grad" onClick={()=>{setFinished(false);setShowModes(true)}}>Play Again</button>
            <button className="btn btn-ghost" onClick={()=>navigate('/battle')}>⚔️ Battle</button>
            <button className="btn btn-ghost" onClick={()=>navigate('/dashboard')}>Dashboard</button>
          </div>
        </div>
      </div>
    )
  }

  /* ── Main screen ── */
  return (
    <div style={{background:'var(--dark)',minHeight:'100vh'}}>
      <div className="orb o1"/><div className="orb o2"/>
      <div className="page anim-fade">
        <div style={{marginBottom:28}}>
          <h1 style={{fontFamily:'var(--fh)',fontSize:'1.8rem',fontWeight:800,marginBottom:6}}>🤖 AI Quiz Generator</h1>
          <p className="text-muted" style={{fontSize:'.86rem'}}>Generate from topic, PDF, or image — then battle or practice solo</p>
        </div>

        {/* Tabs */}
        <div className="tabs mb-24">
          {[['topic','📝 By Topic'],['pdf','📄 From PDF'],['image','🖼️ From Image']].map(([id,lbl])=>(
            <button key={id} className={`tab ${tab===id?'active':''}`} onClick={()=>setTab(id)}>{lbl}</button>
          ))}
        </div>

        <div className="grid-2 gap-16">
          {/* Left: Generator */}
          <div>
            {tab==='topic' && (
              <form onSubmit={genTopic} className="card">
                <h3 style={{fontFamily:'var(--fh)',fontWeight:700,fontSize:'.92rem',marginBottom:16}}>📝 Generate by Topic</h3>
                <div className="form-float">
                  <input type="text" placeholder=" " value={topic} onChange={e=>setTopic(e.target.value)} required/>
                  <label>Topic (e.g. "Newton's Laws")</label>
                </div>
                <div className="form-float">
                  <input type="text" placeholder=" " value={subject} onChange={e=>setSubject(e.target.value)}/>
                  <label>Subject (optional)</label>
                </div>
                <div className="grid-2 gap-8 mb-4">
                  <div className="form-float" style={{marginBottom:0}}>
                    <input type="number" placeholder=" " value={count} onChange={e=>setCount(Number(e.target.value))} min={3} max={20}/>
                    <label>Questions</label>
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
                <button type="submit" className="btn btn-grad w-full mt-16" disabled={loading}>
                  {loading?<><span className="spinner"/> Generating...</>:'✨ Generate Questions'}
                </button>
              </form>
            )}

            {tab==='pdf' && (
              <div className="card">
                <h3 style={{fontFamily:'var(--fh)',fontWeight:700,fontSize:'.92rem',marginBottom:16}}>📄 Generate from PDF</h3>
                <div className="drop-zone mb-12"
                  style={file?{borderColor:'var(--green)',background:'rgba(0,227,150,.04)'}:{}}
                  onClick={()=>fileRef.current?.click()}
                  onDragOver={e=>{e.preventDefault();e.currentTarget.classList.add('over')}}
                  onDragLeave={e=>e.currentTarget.classList.remove('over')}
                  onDrop={e=>{e.preventDefault();e.currentTarget.classList.remove('over');const f=e.dataTransfer.files[0];if(f?.type==='application/pdf')setFile(f)}}
                >
                  <input ref={fileRef} type="file" accept=".pdf" style={{display:'none'}} onChange={e=>setFile(e.target.files[0])}/>
                  <div style={{fontSize:'2.5rem',marginBottom:8}}>{file?'✅':'📁'}</div>
                  <div style={{fontWeight:600,fontSize:'.88rem'}}>{file?file.name:'Drop PDF or click to browse'}</div>
                  <p className="text-muted" style={{fontSize:'.76rem',marginTop:4}}>{file?`${(file.size/1024/1024).toFixed(1)} MB`:'NCERT, textbooks, notes • max 10MB'}</p>
                </div>
                <div className="grid-2 gap-8 mb-12">
                  <div className="form-float" style={{marginBottom:0}}>
                    <input type="number" placeholder=" " value={count} onChange={e=>setCount(Number(e.target.value))} min={3} max={20}/>
                    <label>Questions</label>
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
                <button className="btn btn-grad w-full" onClick={genPDF} disabled={loading||!file}>
                  {loading?<><span className="spinner"/> Processing...</>:'📄 Generate from PDF'}
                </button>
              </div>
            )}

            {tab==='image' && (
              <div className="card">
                <h3 style={{fontFamily:'var(--fh)',fontWeight:700,fontSize:'.92rem',marginBottom:8}}>🖼️ From Educational Image</h3>
                <p className="text-muted mb-14" style={{fontSize:'.78rem'}}>Educational diagrams only — heart, cell, circuit, map, etc.</p>
                <div className="drop-zone mb-12"
                  style={imgFile?{borderColor:'var(--green)',background:'rgba(0,227,150,.04)',padding:14}:{}}
                  onClick={()=>imgRef.current?.click()}
                >
                  <input ref={imgRef} type="file" accept="image/*" style={{display:'none'}} onChange={onImgSelect}/>
                  {imgPrev
                    ? <><img src={imgPrev} alt="preview" style={{maxWidth:'100%',maxHeight:160,borderRadius:10,marginBottom:6,objectFit:'contain'}}/><p style={{fontSize:'.76rem',color:'var(--green)'}}>✅ {imgFile?.name}</p></>
                    : <><div style={{fontSize:'2.5rem',marginBottom:8}}>🖼️</div><div style={{fontWeight:600,fontSize:'.88rem'}}>Drop image or click</div><p className="text-muted" style={{fontSize:'.76rem',marginTop:4}}>JPG, PNG, WebP • max 5MB</p></>
                  }
                </div>
                {imgErr && <div style={{background:'rgba(255,77,106,.08)',border:'1px solid rgba(255,77,106,.2)',borderRadius:8,padding:'9px 12px',marginBottom:10,fontSize:'.8rem',color:'var(--red)'}}>⚠️ {imgErr}</div>}
                <div className="form-float" style={{marginBottom:12}}>
                  <input type="number" placeholder=" " value={count} onChange={e=>setCount(Number(e.target.value))} min={3} max={10}/>
                  <label>Questions (3–10)</label>
                </div>
                <button className="btn btn-grad w-full" onClick={genImg} disabled={loading||!imgFile}>
                  {loading?<><span className="spinner"/> Analyzing...</>:'🖼️ Generate from Image'}
                </button>
              </div>
            )}
          </div>

          {/* Right: Modes + Preview */}
          <div>
            {questions.length > 0 ? (
              <>
                {showModes && (
                  <div className="card mb-14" style={{padding:18}}>
                    <h3 style={{fontFamily:'var(--fh)',fontWeight:700,fontSize:'.9rem',marginBottom:4}}>🎮 Choose How to Play</h3>
                    <p className="text-muted mb-12" style={{fontSize:'.75rem'}}>{questions.length} questions ready</p>
                    <div className="flex-col gap-7">
                      {modes.map(m=>(
                        <button key={m.id} onClick={()=>handlePlayMode(m.id)} disabled={creating}
                          style={{display:'flex',alignItems:'center',gap:10,padding:'10px 14px',borderRadius:10,background:'rgba(255,255,255,.03)',border:'1px solid var(--border)',color:'var(--text)',cursor:'pointer',transition:'all .2s',textAlign:'left'}}
                          onMouseEnter={e=>{e.currentTarget.style.borderColor='rgba(124,92,252,.4)';e.currentTarget.style.background='rgba(124,92,252,.06)'}}
                          onMouseLeave={e=>{e.currentTarget.style.borderColor='var(--border)';e.currentTarget.style.background='rgba(255,255,255,.03)'}}
                        >
                          <span style={{fontSize:'1.3rem',flexShrink:0}}>{m.icon}</span>
                          <div><div style={{fontFamily:'var(--fh)',fontWeight:700,fontSize:'.84rem'}}>{m.label}</div><div style={{fontSize:'.72rem',color:'var(--muted)'}}>{m.desc}</div></div>
                          <span style={{marginLeft:'auto',color:'var(--muted)'}}>{creating?<span className="spinner"/>:'→'}</span>
                        </button>
                      ))}
                    </div>
                    {/* Team names for team mode */}
                    <div style={{marginTop:12,paddingTop:12,borderTop:'1px solid var(--border2)'}}>
                      <p style={{fontSize:'.72rem',color:'var(--muted)',marginBottom:8}}>Team names (for Team vs Team):</p>
                      <div className="grid-2 gap-8">
                        <div className="form-float" style={{marginBottom:0}}>
                          <input type="text" placeholder=" " value={teamA} onChange={e=>setTeamA(e.target.value)}/>
                          <label>Team A Name</label>
                        </div>
                        <div className="form-float" style={{marginBottom:0}}>
                          <input type="text" placeholder=" " value={teamB} onChange={e=>setTeamB(e.target.value)}/>
                          <label>Team B Name</label>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                <div style={{display:'flex',justifyContent:'space-between',alignItems:'center',marginBottom:12}}>
                  <h3 style={{fontFamily:'var(--fh)',fontWeight:700,fontSize:'.9rem'}}>Questions ({questions.length})</h3>
                  <button className="btn btn-ghost btn-sm" onClick={()=>setShowModes(s=>!s)}>{showModes?'Hide Modes':'🎮 Play'}</button>
                </div>

                <div className="flex-col gap-10">
                  {questions.map((q,i)=>(
                    <div key={i} className="gen-q anim-fade" style={{animationDelay:`${i*.04}s`}}>
                      <div style={{display:'flex',justifyContent:'space-between',marginBottom:8}}>
                        <span className="badge bp">Q{i+1}</span>
                        <span className="badge badge-ai">🤖 {q.source==='pdf'?'PDF':q.source==='image'?'IMG':'AI'}</span>
                      </div>
                      <p style={{fontSize:'.84rem',fontWeight:500,marginBottom:8,lineHeight:1.55}}>{q.question}</p>
                      <div style={{display:'grid',gridTemplateColumns:'1fr 1fr',gap:5}}>
                        {q.options.map((opt,j)=>(
                          <div key={j} className={`q-opt${j===q.answer?' correct-opt':''}`}>{LETTERS[j]}) {j===q.answer&&'✓ '}{opt}</div>
                        ))}
                      </div>
                      {q.explanation && <p style={{fontSize:'.71rem',color:'var(--muted)',marginTop:6,paddingTop:6,borderTop:'1px solid var(--border2)'}}>💡 {q.explanation}</p>}
                    </div>
                  ))}
                </div>
              </>
            ) : (
              <div className="card" style={{textAlign:'center',padding:48}}>
                <div style={{fontSize:'3rem',marginBottom:14}}>🤖</div>
                <h3 style={{fontFamily:'var(--fh)',fontWeight:700,fontSize:'1rem',marginBottom:8}}>Ready to Generate</h3>
                <p className="text-muted" style={{fontSize:'.82rem',lineHeight:1.6}}>Generate from topic, PDF, or image — then choose Solo, 1v1, Group, or Team battle mode</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}
