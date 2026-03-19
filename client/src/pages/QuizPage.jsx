import { useState, useRef, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import api from '../utils/api'
import toast from 'react-hot-toast'

const SUBJECTS = ['Math','Physics','Chemistry','Biology','English','History','Geography','Computer Science','Economics','Hindi','Science','GK','Sanskrit']
const CLASSES  = ['Class 1','Class 2','Class 3','Class 4','Class 5','Class 6','Class 7','Class 8','Class 9','Class 10','Class 11','Class 12','Undergraduate','Postgraduate']
const LETTERS  = ['A','B','C','D']

export default function QuizPage() {
  const { user } = useAuth()
  const navigate  = useNavigate()

  // Config
  const [subject,   setSubject]  = useState(user?.interests?.[0] || 'Physics')
  const [cls,       setCls]      = useState(user?.class || 'Class 10')
  const [diff,      setDiff]     = useState('intermediate')
  const [count,     setCount]    = useState(10)
  const [mode,      setMode]     = useState('solo')
  const [loading,   setLoading]  = useState(false)

  // Play state
  const [questions, setQuestions] = useState([])
  const [quizId,    setQuizId]    = useState(null)
  const [playing,   setPlaying]   = useState(false)
  const [qIdx,      setQIdx]      = useState(0)
  const [score,     setScore]     = useState(0)
  const [selected,  setSelected]  = useState(null)
  const [answered,  setAnswered]  = useState(false)
  const [timeLeft,  setTimeLeft]  = useState(15)
  const [finished,  setFinished]  = useState(false)
  const [results,   setResults]   = useState([])
  const timerRef = useRef(null)

  const startTimer = () => {
    clearInterval(timerRef.current)
    setTimeLeft(15)
    timerRef.current = setInterval(() => {
      setTimeLeft(t => {
        if (t <= 1) { clearInterval(timerRef.current); handleAnswer(-1); return 0 }
        return t - 1
      })
    }, 1000)
  }

  useEffect(() => {
    if (playing && questions.length) startTimer()
    return () => clearInterval(timerRef.current)
  }, [qIdx, playing])

  const generate = async () => {
    setLoading(true)
    try {
      const res = await api.post('/ai/generate-standard', { subject, class: cls, difficulty: diff, count })
      setQuestions(res.data.questions)
      setQuizId(res.data.quizId)
      if (mode === 'solo') {
        setPlaying(true); setQIdx(0); setScore(0); setResults([])
        setAnswered(false); setSelected(null); setFinished(false)
      } else {
        // Multiplayer — create room then redirect
        const r = await api.post('/battle/create-with-questions', {
          type: 'group', questions: res.data.questions,
          subject, difficulty: diff, totalQ: count, maxPlayers: 10,
        })
        toast.success('Room created!')
        navigate(`/battle/${r.data.room.code}`)
      }
    } catch (err) { toast.error(err.response?.data?.message || 'Failed') }
    finally { setLoading(false) }
  }

  const handleAnswer = (idx) => {
    if (answered) return
    clearInterval(timerRef.current)
    setAnswered(true); setSelected(idx)
    const correct = idx === questions[qIdx]?.answer
    if (correct) setScore(s => s + 1)
    setResults(r => [...r, { questionIndex: qIdx, selected: idx, correct, timeTaken: 15 - timeLeft }])
  }

  const nextQ = () => {
    const next = qIdx + 1
    if (next >= questions.length) {
      setFinished(true); setPlaying(false)
      const finalScore = results.filter(r => r.correct).length + (selected === questions[qIdx]?.answer ? 1 : 0)
      const acc = Math.round((finalScore / questions.length) * 100)
      const xp = finalScore * 15 + (acc >= 80 ? 30 : 0)
      api.post('/ai/submit-solo', { score: finalScore, totalQ: questions.length, accuracy: acc, xpEarned: xp, difficulty: diff, subject, quizId }).catch(() => {})
      return
    }
    setQIdx(next); setAnswered(false); setSelected(null)
  }

  const finalScore = results.filter(r => r.correct).length
  const accuracy   = questions.length ? Math.round((finalScore / questions.length) * 100) : 0

  /* ── Finished screen ── */
  if (finished) {
    return (
      <div style={{ background:'var(--dark)', minHeight:'100vh' }}>
        <div className="orb o1"/><div className="orb o2"/>
        <div className="page page-sm anim-pop" style={{ paddingTop:60, textAlign:'center' }}>
          <div style={{ fontSize:'4rem', marginBottom:16 }}>{accuracy>=80?'🏆':accuracy>=60?'🎯':'📚'}</div>
          <h1 style={{ fontFamily:'var(--fh)', fontSize:'2rem', fontWeight:800, marginBottom:8 }}>
            {accuracy>=80?'Excellent!':accuracy>=60?'Good Job!':'Keep Practicing!'}
          </h1>
          <p className="text-muted mb-28">{subject} — {cls}</p>
          <div style={{ display:'flex', justifyContent:'center', gap:24, marginBottom:32 }}>
            <div className="stat-card" style={{ textAlign:'center', minWidth:100 }}>
              <div className="stat-val text-grad">{finalScore}/{questions.length}</div>
              <div className="stat-label">Score</div>
            </div>
            <div className="stat-card" style={{ textAlign:'center', minWidth:100 }}>
              <div className="stat-val" style={{ color:accuracy>=80?'var(--green)':accuracy>=60?'var(--gold)':'var(--red)' }}>{accuracy}%</div>
              <div className="stat-label">Accuracy</div>
            </div>
            <div className="stat-card" style={{ textAlign:'center', minWidth:100 }}>
              <div className="stat-val" style={{ color:'var(--gold)' }}>+{finalScore*15}</div>
              <div className="stat-label">XP Earned</div>
            </div>
          </div>
          <div style={{ display:'flex', gap:12, justifyContent:'center', flexWrap:'wrap' }}>
            <button className="btn btn-grad" onClick={() => { setFinished(false); setQuestions([]); }}>Play Again</button>
            <button className="btn btn-ghost" onClick={() => navigate('/ai-quiz')}>🤖 AI Quiz</button>
            <button className="btn btn-ghost" onClick={() => navigate('/dashboard')}>Dashboard</button>
          </div>
          {/* Answer review */}
          <div style={{ textAlign:'left', marginTop:36 }}>
            <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.88rem', marginBottom:14 }}>📋 Answer Review</div>
            <div className="flex-col gap-8">
              {questions.map((q,i) => {
                const res = results[i]; const correct = res?.correct
                return (
                  <div key={i} className="card-sm" style={{ borderColor:correct?'rgba(0,227,150,.2)':'rgba(255,77,106,.15)' }}>
                    <div style={{ display:'flex', gap:8, marginBottom:8 }}>
                      <span>{correct?'✅':'❌'}</span>
                      <span style={{ fontSize:'.83rem', fontWeight:500 }}>Q{i+1}: {q.question}</span>
                    </div>
                    <div style={{ fontSize:'.75rem', color:'var(--muted)' }}>
                      <span style={{ color:correct?'var(--green)':'var(--red)' }}>Your answer: {res?.selected>=0?q.options[res.selected]:'(timed out)'}</span>
                      {!correct && <span style={{ color:'var(--green)', marginLeft:12 }}>✓ {q.options[q.answer]}</span>}
                    </div>
                    {q.explanation && <p style={{ fontSize:'.72rem', color:'var(--muted)', marginTop:6, paddingTop:6, borderTop:'1px solid var(--border)' }}>💡 {q.explanation}</p>}
                  </div>
                )
              })}
            </div>
          </div>
        </div>
      </div>
    )
  }

  /* ── Playing screen ── */
  if (playing && questions.length) {
    const q = questions[qIdx]
    const pct = (timeLeft / 15) * 100
    return (
      <div style={{ background:'var(--dark)', minHeight:'100vh' }}>
        <div className="orb o1"/><div className="orb o2"/>
        <div className="page page-md anim-fade" style={{ paddingTop:28 }}>
          {/* Progress bar */}
          <div className="progress-track mb-12">
            <div className="progress-fill" style={{ width:`${(qIdx/questions.length)*100}%` }}/>
          </div>
          <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:14 }}>
            <span className="badge bc">Q {qIdx+1}/{questions.length}</span>
            <div className={`timer-ring${timeLeft<=5?' danger':''}`}>{timeLeft}</div>
            <span className="badge bo">⚡ {score} pts</span>
          </div>
          {/* Timer fill */}
          <div style={{ height:4, background:'rgba(255,255,255,.06)', borderRadius:4, marginBottom:22, overflow:'hidden' }}>
            <div style={{ width:`${pct}%`, height:'100%', background:timeLeft<=5?'var(--red)':'var(--gradient)', borderRadius:4, transition:'width 1s linear' }}/>
          </div>
          {/* Question */}
          <div className="card mb-16 anim-pop" style={{ padding:24 }}>
            <div style={{ display:'flex', justifyContent:'space-between', marginBottom:12 }}>
              <span className="badge bp">{q.topic || subject}</span>
              <span className="badge bc" style={{ textTransform:'capitalize' }}>{diff}</span>
            </div>
            <h2 style={{ fontSize:'1.05rem', lineHeight:1.7, fontWeight:600 }}>{q.question}</h2>
          </div>
          {/* Options */}
          <div className="flex-col gap-10 mb-16">
            {q.options.map((opt,i) => {
              let cls2 = 'qo anim-fade'
              if (answered) { if(i===q.answer) cls2+=' correct'; else if(i===selected) cls2+=' wrong' }
              return (
                <button key={i} className={cls2} style={{ animationDelay:`${i*.06}s` }}
                  onClick={() => handleAnswer(i)} disabled={answered}>
                  <span className="ol">{LETTERS[i]}</span>{opt}
                </button>
              )
            })}
          </div>
          {/* Explanation */}
          {answered && (
            <div className="card anim-fade" style={{ borderColor:selected===q.answer?'rgba(0,227,150,.25)':'rgba(255,77,106,.2)', background:selected===q.answer?'rgba(0,227,150,.04)':'rgba(255,77,106,.04)' }}>
              <div style={{ display:'flex', gap:8, marginBottom:8 }}>
                <span>{selected===q.answer?'✅':'❌'}</span>
                <span style={{ fontWeight:700, fontSize:'.86rem', color:selected===q.answer?'var(--green)':'var(--red)' }}>
                  {selected===q.answer?'Correct! +15 pts':'Wrong answer'}
                </span>
              </div>
              <p style={{ fontSize:'.82rem', color:'var(--muted)', lineHeight:1.6 }}>{q.explanation}</p>
              <button className="btn btn-grad btn-sm mt-12" onClick={nextQ}>
                {qIdx+1>=questions.length?'🏆 See Results':'Next →'}
              </button>
            </div>
          )}
        </div>
      </div>
    )
  }

  /* ── Config screen ── */
  return (
    <div style={{ background:'var(--dark)', minHeight:'100vh' }}>
      <div className="orb o1"/><div className="orb o2"/>
      <div className="page anim-fade">
        <div style={{ marginBottom:28 }}>
          <h1 style={{ fontFamily:'var(--fh)', fontSize:'1.8rem', fontWeight:800, marginBottom:6 }}>📝 Standard Quiz</h1>
          <p className="text-muted" style={{ fontSize:'.86rem' }}>Class & subject based MCQs generated by AI</p>
        </div>
        <div className="grid-2 gap-16">
          {/* Left: Config */}
          <div className="card">
            <h3 style={{ fontFamily:'var(--fh)', fontWeight:700, marginBottom:18, fontSize:'.92rem' }}>Configure Your Quiz</h3>
            {/* Subject */}
            <div style={{ marginBottom:18 }}>
              <div style={{ fontSize:'.72rem', color:'var(--muted)', fontFamily:'var(--fh)', fontWeight:700, letterSpacing:'.5px', textTransform:'uppercase', marginBottom:10 }}>Subject</div>
              <div style={{ display:'flex', flexWrap:'wrap', gap:7 }}>
                {SUBJECTS.map(s => (
                  <button key={s} onClick={() => setSubject(s)} className={`subject-pill${subject===s?' active':''}`}>{s}</button>
                ))}
              </div>
            </div>
            {/* Class */}
            <div className="form-float">
              <select value={cls} onChange={e => setCls(e.target.value)}>
                {CLASSES.map(c => <option key={c}>{c}</option>)}
              </select>
              <label>Class / Grade</label>
            </div>
            {/* Difficulty */}
            <div style={{ marginBottom:16 }}>
              <div style={{ fontSize:'.72rem', color:'var(--muted)', fontFamily:'var(--fh)', fontWeight:700, letterSpacing:'.5px', textTransform:'uppercase', marginBottom:10 }}>Difficulty</div>
              <div style={{ display:'flex', gap:8 }}>
                {['beginner','intermediate','advanced'].map(d => (
                  <button key={d} onClick={() => setDiff(d)} className={`subject-pill${diff===d?' active':''}`} style={{ textTransform:'capitalize' }}>{d}</button>
                ))}
              </div>
            </div>
            {/* Count */}
            <div className="form-float">
              <input type="number" placeholder=" " value={count} onChange={e => setCount(Number(e.target.value))} min={3} max={20}/>
              <label>Number of Questions (3–20)</label>
            </div>
          </div>

          {/* Right: Mode + Start */}
          <div className="card">
            <h3 style={{ fontFamily:'var(--fh)', fontWeight:700, marginBottom:18, fontSize:'.92rem' }}>Choose Play Mode</h3>
            <div className="flex-col gap-8 mb-20">
              {[
                { id:'solo', icon:'⚡', label:'Solo Practice', desc:'Play alone • score saved to history & linked teacher/school' },
                { id:'multi', icon:'👥', label:'Multiplayer', desc:'Invite link • up to 10 players compete live' },
              ].map(m => (
                <button key={m.id} onClick={() => setMode(m.id)}
                  style={{ display:'flex', alignItems:'center', gap:12, padding:'14px 16px', background:mode===m.id?'rgba(124,92,252,.1)':'rgba(255,255,255,.03)', border:`1px solid ${mode===m.id?'rgba(124,92,252,.4)':'var(--border)'}`, borderRadius:12, cursor:'pointer', textAlign:'left', transition:'all .2s' }}>
                  <span style={{ fontSize:'1.6rem' }}>{m.icon}</span>
                  <div>
                    <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.86rem', color:'var(--text)' }}>{m.label}</div>
                    <div style={{ fontSize:'.73rem', color:'var(--muted)' }}>{m.desc}</div>
                  </div>
                  {mode===m.id && <span style={{ marginLeft:'auto', color:'var(--violet)' }}>✓</span>}
                </button>
              ))}
            </div>
            <div className="card-sm mb-16" style={{ background:'rgba(0,212,255,.05)', borderColor:'rgba(0,212,255,.15)' }}>
              <div style={{ fontSize:'.75rem', color:'var(--muted)', lineHeight:1.7 }}>
                📌 Your quiz results automatically sync to your linked teacher and institution dashboards.
              </div>
            </div>
            <button className="btn btn-grad w-full btn-lg" onClick={generate} disabled={loading}>
              {loading ? <><span className="spinner"/> Generating...</> : `✨ Generate ${count} Questions`}
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}
