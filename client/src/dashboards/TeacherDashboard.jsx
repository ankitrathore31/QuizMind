import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import api from '../utils/api'
import toast from 'react-hot-toast'

export default function TeacherDashboard() {
  const { user } = useAuth()
  const navigate = useNavigate()
  const [students, setStudents] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')

  useEffect(() => {
    api.get('/users/my-students').then(r => setStudents(r.data)).catch(() => {}).finally(() => setLoading(false))
  }, [])

  const copyRef = () => { navigator.clipboard.writeText(user.refCode); toast.success('Ref code copied!') }
  const filtered = students.filter(s => s.name?.toLowerCase().includes(search.toLowerCase()))
  const avgScore = students.length ? Math.round(students.reduce((a,s) => a + (s.avgAccuracy||0), 0) / students.length) : 0

  return (
    <div style={{ background:'var(--dark)', minHeight:'100vh' }}>
      <div className="orb o1" /><div className="orb o2" />
      <div className="page anim-fade">
        <div style={{ marginBottom:28 }}>
          <div style={{ fontFamily:'var(--fh)', fontSize:'.7rem', color:'var(--muted)', letterSpacing:'1.5px', textTransform:'uppercase', marginBottom:6 }}>Teacher Dashboard</div>
          <h1 style={{ fontFamily:'var(--fh)', fontSize:'1.8rem', fontWeight:800 }}>
            Welcome, <span className="text-grad">{user?.name?.split(' ')[0]}</span> 👨‍🏫
          </h1>
        </div>

        {/* Stats */}
        <div className="grid-4 mb-20">
          <div className="stat-card"><div className="stat-val text-grad">{students.length}</div><div className="stat-label">Total Students</div></div>
          <div className="stat-card"><div className="stat-val" style={{ color:'var(--green)' }}>{avgScore}%</div><div className="stat-label">Avg Score</div></div>
          <div className="stat-card" style={{ cursor:'pointer' }} onClick={copyRef}>
            <div style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'.95rem', color:'var(--violet)', letterSpacing:1 }}>{user?.refCode}</div>
            <div className="stat-label" style={{ marginTop:4 }}>Ref Code (click to copy)</div>
          </div>
          <div className="stat-card"><div className="stat-val" style={{ color:'var(--cyan)' }}>+{user?.refLink}</div><div className="stat-label">Ref Link</div></div>
        </div>

        <div className="grid-2 mb-20">
          {/* Students list */}
          <div className="card">
            <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:14, flexWrap:'wrap', gap:8 }}>
              <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.88rem' }}>👥 My Students ({students.length})</div>
              <input value={search} onChange={e=>setSearch(e.target.value)} placeholder="Search..."
                style={{ padding:'6px 12px', background:'rgba(255,255,255,.05)', border:'1px solid var(--border)', borderRadius:8, color:'var(--text)', fontSize:'.78rem', outline:'none', width:130 }} />
            </div>
            {loading && <div style={{ textAlign:'center', padding:20 }}><span className="spinner" /></div>}
            {!loading && filtered.length === 0 && (
              <div style={{ textAlign:'center', padding:'24px 0' }}>
                <p className="text-muted" style={{ fontSize:'.83rem' }}>No students yet. Share your ref code!</p>
                <div style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.1rem', color:'var(--violet)', marginTop:10, letterSpacing:1 }}>{user?.refCode}</div>
                <button className="btn btn-ghost btn-sm" style={{ marginTop:8 }} onClick={copyRef}>Copy Code</button>
              </div>
            )}
            <div className="flex-col gap-6">
              {filtered.map((s, i) => (
                <div key={i} style={{ display:'flex', alignItems:'center', gap:10, padding:'9px 12px', background:'rgba(255,255,255,.02)', borderRadius:10 }}>
                  <div className={`av av-${['p','c','g','o'][i%4]}`}>{s.name[0]}</div>
                  <div style={{ flex:1 }}>
                    <div style={{ fontSize:'.84rem', fontWeight:600 }}>{s.name}</div>
                    <div style={{ fontSize:'.7rem', color:'var(--muted)' }}>{s.class || 'N/A'} • {s.avgAccuracy||0}% avg</div>
                  </div>
                  <div className="progress-track" style={{ width:50 }}>
                    <div className="progress-fill" style={{ width:`${s.avgAccuracy||0}%`, background:s.avgAccuracy>=70?'var(--gradient)':s.avgAccuracy>=50?'linear-gradient(90deg,var(--gold),var(--orange))':'linear-gradient(90deg,var(--red),var(--pink))' }} />
                  </div>
                  <span className={`badge ${s.avgAccuracy>=70?'bg':s.avgAccuracy>=50?'bo':'br'}`}>{s.avgAccuracy>=70?'Strong':s.avgAccuracy>=50?'Avg':'Weak'}</span>
                </div>
              ))}
            </div>
          </div>

          {/* Host Battle */}
          <div className="card">
            <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.88rem', marginBottom:16 }}>⚔️ Host a Battle</div>
            <div className="flex-col gap-8">
              {[
                { icon:'⚡', label:'Solo Quiz', desc:'Play yourself or generate for students', to:'/ai-quiz' },
                { icon:'👥', label:'Multiplayer', desc:'Invite link • real-time quiz', to:'/battle' },
                { icon:'🏆', label:'Team vs Team', desc:'2 teams • separate invite links', to:'/battle' },
              ].map(m => (
                <button key={m.label} onClick={() => navigate(m.to)}
                  style={{ display:'flex', alignItems:'center', gap:12, padding:'12px 14px', background:'rgba(255,255,255,.03)', border:'1px solid var(--border)', borderRadius:10, cursor:'pointer', textAlign:'left', transition:'all .2s' }}
                  onMouseEnter={e=>{e.currentTarget.style.borderColor='rgba(124,92,252,.4)';e.currentTarget.style.background='rgba(124,92,252,.06)'}}
                  onMouseLeave={e=>{e.currentTarget.style.borderColor='var(--border)';e.currentTarget.style.background='rgba(255,255,255,.03)'}}
                >
                  <span style={{ fontSize:'1.4rem' }}>{m.icon}</span>
                  <div>
                    <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.84rem', color:'var(--text)' }}>{m.label}</div>
                    <div style={{ fontSize:'.72rem', color:'var(--muted)' }}>{m.desc}</div>
                  </div>
                  <span style={{ marginLeft:'auto', color:'var(--muted)' }}>→</span>
                </button>
              ))}
            </div>
            <div className="divider" />
            <div style={{ display:'flex', gap:10 }}>
              <button className="btn btn-grad btn-sm" style={{ flex:1 }} onClick={() => navigate('/quiz')}>📝 Quiz</button>
              <button className="btn btn-ghost btn-sm" style={{ flex:1 }} onClick={() => navigate('/ai-quiz')}>🤖 AI Quiz</button>
            </div>
          </div>
        </div>

        {/* My Students full page link */}
        <div className="card" style={{ padding:18, background:'linear-gradient(135deg,rgba(124,92,252,.06),rgba(0,212,255,.04))', borderColor:'rgba(124,92,252,.2)' }}>
          <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between' }}>
            <div>
              <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.88rem' }}>📊 Detailed Student Analytics</div>
              <div style={{ fontSize:'.78rem', color:'var(--muted)', marginTop:4 }}>View per-student performance, quiz history, and weak subjects</div>
            </div>
            <button className="btn btn-grad btn-sm" onClick={() => navigate('/my-students')}>View All Students →</button>
          </div>
        </div>
      </div>
    </div>
  )
}
