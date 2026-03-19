import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import api from '../utils/api'

export default function StudentDashboard() {
  const { user } = useAuth()
  const navigate = useNavigate()
  const [history, setHistory] = useState([])
  const [suggestion, setSuggestion] = useState('')
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    Promise.all([
      api.get('/users/history').then(r => setHistory(r.data)).catch(()=>{}),
      api.get('/ai/suggestions').then(r => setSuggestion(r.data.suggestion)).catch(()=>{}),
    ]).finally(() => setLoading(false))
  }, [])

  const recentResults = history.slice(0, 5)
  const subjectMap = {}
  history.forEach(r => {
    if (r.subject) { if (!subjectMap[r.subject]) subjectMap[r.subject] = []; subjectMap[r.subject].push(r.accuracy) }
  })
  const subjectStats = Object.entries(subjectMap).map(([s,arr]) => ({ subject:s, avg:Math.round(arr.reduce((a,b)=>a+b,0)/arr.length) })).sort((a,b)=>b.avg-a.avg).slice(0,5)
  const avgAccuracy = history.length ? Math.round(history.reduce((a,r)=>a+r.accuracy,0)/history.length) : 0
  const xpLevel = Math.floor((user?.xp||0)/500) + 1
  const xpInLevel = (user?.xp||0) % 500
  const typeIcon = { solo:'⚡', '1v1':'⚔️', group:'👥', team:'🏆', school:'🏫', standard:'📝' }

  return (
    <div style={{ background:'var(--dark)', minHeight:'100vh' }}>
      <div className="orb o1" /><div className="orb o2" />
      <div className="page anim-fade">

        {/* Header */}
        <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:28, flexWrap:'wrap', gap:16 }}>
          <div>
            <div style={{ fontFamily:'var(--fh)', fontSize:'.7rem', color:'var(--muted)', letterSpacing:'1.5px', textTransform:'uppercase', marginBottom:6 }}>Student Dashboard</div>
            <h1 style={{ fontFamily:'var(--fh)', fontSize:'1.8rem', fontWeight:800, lineHeight:1.2 }}>
              Hey, <span className="text-grad">{user?.name?.split(' ')[0]}</span> 👋
            </h1>
            <p className="text-muted" style={{ fontSize:'.85rem', marginTop:4 }}>{user?.class} • {user?.college || 'QuizMind AI'}</p>
          </div>
          <div style={{ display:'flex', gap:10, flexWrap:'wrap' }}>
            <button className="btn btn-grad" onClick={() => navigate('/quiz')}>📝 Start Quiz</button>
            <button className="btn btn-ghost" onClick={() => navigate('/battle')}>⚔️ Battle</button>
          </div>
        </div>

        {/* XP Progress */}
        <div className="card card-glow mb-20">
          <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:12, flexWrap:'wrap', gap:8 }}>
            <div style={{ display:'flex', alignItems:'center', gap:12 }}>
              <div style={{ width:44, height:44, borderRadius:'50%', background:'var(--gradient)', display:'flex', alignItems:'center', justifyContent:'center', fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.1rem', flexShrink:0 }}>{xpLevel}</div>
              <div>
                <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.9rem' }}>Level {xpLevel}</div>
                <div style={{ fontSize:'.72rem', color:'var(--muted)' }}>{xpInLevel}/500 XP to next level</div>
              </div>
            </div>
            <div style={{ display:'flex', gap:16 }}>
              <div style={{ textAlign:'center' }}><div style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.1rem', color:'var(--gold)' }}>{user?.streak||0}🔥</div><div style={{ fontSize:'.68rem', color:'var(--muted)' }}>Streak</div></div>
              <div style={{ textAlign:'center' }}><div style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.1rem', background:'var(--gradient)', WebkitBackgroundClip:'text', WebkitTextFillColor:'transparent' }}>{(user?.xp||0).toLocaleString()}</div><div style={{ fontSize:'.68rem', color:'var(--muted)' }}>XP</div></div>
            </div>
          </div>
          <div className="progress-track">
            <div className="progress-fill" style={{ width:`${(xpInLevel/500)*100}%` }} />
          </div>
        </div>

        {/* Stats */}
        <div className="grid-4 mb-20">
          {[
            { val:history.length, label:'Quizzes Played', color:'var(--violet)' },
            { val:history.filter(r=>r.type!=='solo').length, label:'Battles', color:'var(--cyan)' },
            { val:`${avgAccuracy}%`, label:'Avg Accuracy', color:'var(--green)' },
            { val:`#${Math.floor(Math.random()*200)+1}`, label:'Global Rank', color:'var(--gold)' },
          ].map((s,i) => (
            <div key={i} className="stat-card">
              <div className="stat-val" style={{ color:s.color, fontSize:'1.6rem' }}>{s.val}</div>
              <div className="stat-label">{s.label}</div>
            </div>
          ))}
        </div>

        <div className="grid-2 mb-20">
          {/* Subject Performance */}
          <div className="card">
            <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.88rem', marginBottom:16 }}>🎯 Subject Performance</div>
            {subjectStats.length === 0 && !loading && (
              <p style={{ color:'var(--muted)', fontSize:'.82rem' }}>Play some quizzes to see your stats!</p>
            )}
            <div className="flex-col gap-10">
              {subjectStats.map(s => (
                <div key={s.subject}>
                  <div style={{ display:'flex', justifyContent:'space-between', marginBottom:5 }}>
                    <span style={{ fontSize:'.82rem' }}>{s.subject}</span>
                    <span style={{ fontSize:'.82rem', fontWeight:700, color:s.avg>=80?'var(--green)':s.avg>=60?'var(--gold)':'var(--red)' }}>{s.avg}%</span>
                  </div>
                  <div className="progress-track">
                    <div className="progress-fill" style={{ width:`${s.avg}%`, background:s.avg>=80?'var(--grad3)':s.avg>=60?'linear-gradient(90deg,var(--gold),var(--orange))':'linear-gradient(90deg,var(--red),var(--pink))' }} />
                  </div>
                </div>
              ))}
              {loading && <span className="spinner" />}
            </div>
          </div>

          {/* AI Suggestion */}
          <div className="card" style={{ background:'linear-gradient(135deg,rgba(0,212,255,.05),rgba(124,92,252,.05))', borderColor:'rgba(0,212,255,.15)' }}>
            <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.88rem', marginBottom:12, color:'var(--cyan)' }}>🤖 AI Suggestions</div>
            {suggestion ? (
              <p style={{ fontSize:'.85rem', color:'var(--muted)', lineHeight:1.8 }}>{suggestion}</p>
            ) : (
              <div style={{ display:'flex', alignItems:'center', gap:8 }}><span className="spinner" /><span style={{ color:'var(--muted)', fontSize:'.82rem' }}>Analyzing your performance...</span></div>
            )}
            {(user?.weakSubjects?.length > 0) && (
              <div style={{ marginTop:14 }}>
                <div style={{ fontSize:'.72rem', color:'var(--muted)', marginBottom:8 }}>Weak areas:</div>
                <div style={{ display:'flex', gap:6, flexWrap:'wrap' }}>
                  {user.weakSubjects.map(s => <span key={s} className="badge br">{s}</span>)}
                </div>
              </div>
            )}
            <button className="btn btn-ghost btn-sm" style={{ marginTop:14 }} onClick={() => navigate('/ai-tutor')}>💬 Ask AI Tutor</button>
          </div>
        </div>

        {/* Quick Actions */}
        <div className="card mb-20" style={{ padding:20 }}>
          <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.88rem', marginBottom:14 }}>⚡ Quick Actions</div>
          <div style={{ display:'flex', gap:10, flexWrap:'wrap' }}>
            {[
              { icon:'📝', label:'Standard Quiz', to:'/quiz' },
              { icon:'🤖', label:'AI Quiz', to:'/ai-quiz' },
              { icon:'⚔️', label:'Solo Battle', to:'/battle' },
              { icon:'👥', label:'Multiplayer', to:'/battle' },
              { icon:'💬', label:'AI Tutor', to:'/ai-tutor' },
              { icon:'🏆', label:'Leaderboard', to:'/leaderboard' },
            ].map(a => (
              <button key={a.label} onClick={() => navigate(a.to)} className="btn btn-ghost btn-sm">
                {a.icon} {a.label}
              </button>
            ))}
          </div>
        </div>

        {/* Recent Activity */}
        <div className="card">
          <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:16 }}>
            <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.88rem' }}>📋 Recent Activity</div>
            <button className="btn btn-ghost btn-sm" onClick={() => navigate('/history')}>View All</button>
          </div>
          {recentResults.length === 0 && !loading && (
            <div style={{ textAlign:'center', padding:'28px 0' }}>
              <div style={{ fontSize:'2.5rem', marginBottom:10 }}>🎯</div>
              <p className="text-muted" style={{ fontSize:'.85rem' }}>No activity yet. Start your first quiz!</p>
              <button className="btn btn-grad btn-sm" style={{ marginTop:12 }} onClick={() => navigate('/quiz')}>Start Quiz →</button>
            </div>
          )}
          <div className="flex-col gap-8">
            {recentResults.map((r, i) => (
              <div key={i} style={{ display:'flex', alignItems:'center', gap:12, padding:'10px 14px', background:'rgba(255,255,255,.02)', borderRadius:10 }}>
                <span style={{ fontSize:'1.2rem' }}>{typeIcon[r.type] || '📝'}</span>
                <div style={{ flex:1 }}>
                  <div style={{ fontSize:'.84rem', fontWeight:500 }}>{r.subject || 'Quiz'} {r.topic ? `— ${r.topic}` : ''}</div>
                  <div style={{ fontSize:'.72rem', color:'var(--muted)' }}>{new Date(r.createdAt).toLocaleDateString()} • {r.type}</div>
                </div>
                <span style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'.9rem', color:r.accuracy>=80?'var(--green)':r.accuracy>=60?'var(--gold)':'var(--red)' }}>{r.accuracy}%</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  )
}
