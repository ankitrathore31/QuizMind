import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import api from '../utils/api'
import toast from 'react-hot-toast'

export default function InstitutionDashboard() {
  const { user } = useAuth()
  const navigate = useNavigate()
  const [students, setStudents] = useState([])
  const [battleHistory, setBattleHistory] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    Promise.all([
      api.get('/users/my-students').then(r => setStudents(r.data)).catch(()=>{}),
      api.get('/battle/school/history').then(r => setBattleHistory(r.data)).catch(()=>{}),
    ]).finally(() => setLoading(false))
  }, [])

  const copyRef = () => { navigator.clipboard.writeText(user.refCode); toast.success('School code copied!') }
  const avg = students.length ? Math.round(students.reduce((a,s)=>a+(s.avgAccuracy||0),0)/students.length) : 0
  const trophies = battleHistory.filter(b => b.winnerTeam === user.college || (b.schoolAId?.toString()===user._id?.toString() && b.teams?.[0]?.score > b.teams?.[1]?.score)).length

  return (
    <div style={{ background:'var(--dark)', minHeight:'100vh' }}>
      <div className="orb o1" /><div className="orb o2" />
      <div className="page anim-fade">
        <div style={{ marginBottom:28 }}>
          <div style={{ fontFamily:'var(--fh)', fontSize:'.7rem', color:'var(--muted)', letterSpacing:'1.5px', textTransform:'uppercase', marginBottom:6 }}>Institution Dashboard</div>
          <h1 style={{ fontFamily:'var(--fh)', fontSize:'1.8rem', fontWeight:800 }}>
            <span className="text-grad">{user?.college || user?.name}</span> 🏛️
          </h1>
        </div>

        <div className="grid-4 mb-20">
          <div className="stat-card"><div className="stat-val text-grad">{students.length}</div><div className="stat-label">Total Students</div></div>
          <div className="stat-card"><div className="stat-val" style={{ color:'var(--green)' }}>{avg}%</div><div className="stat-label">Avg Score</div></div>
          <div className="stat-card"><div className="stat-val" style={{ color:'var(--gold)' }}>{trophies}🏆</div><div className="stat-label">Trophies Won</div></div>
          <div className="stat-card" style={{ cursor:'pointer' }} onClick={copyRef}>
            <div style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'.9rem', color:'var(--gold)', letterSpacing:1 }}>{user?.refCode}</div>
            <div className="stat-label" style={{ marginTop:4 }}>School Code (copy)</div>
          </div>
        </div>

        {/* School Battle CTA */}
        <div className="card mb-20" style={{ background:'linear-gradient(135deg,rgba(255,184,0,.07),rgba(124,92,252,.05))', borderColor:'rgba(255,184,0,.25)' }}>
          <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between', flexWrap:'wrap', gap:14 }}>
            <div>
              <div style={{ display:'flex', alignItems:'center', gap:8, marginBottom:6 }}>
                <span style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'1rem' }}>🏫 School vs School Battle</span>
                <span className="badge bo">Institution Exclusive</span>
              </div>
              <p style={{ color:'var(--muted)', fontSize:'.82rem' }}>Host epic school battles with up to 100+ students per school</p>
            </div>
            <div style={{ display:'flex', gap:10 }}>
              <button className="btn btn-grad" onClick={() => navigate('/school-battle')}>+ Host School Battle</button>
              <button className="btn btn-ghost btn-sm" onClick={() => navigate('/school-battle')}>Join Battle</button>
            </div>
          </div>
        </div>

        <div className="grid-2 mb-20">
          {/* Students */}
          <div className="card">
            <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:14 }}>
              <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.88rem' }}>👥 Students ({students.length})</div>
              <button className="btn btn-ghost btn-sm" onClick={() => navigate('/my-students')}>View All</button>
            </div>
            {loading && <span className="spinner" />}
            <div className="flex-col gap-6">
              {students.slice(0,6).map((s,i) => (
                <div key={i} style={{ display:'flex', alignItems:'center', gap:10, padding:'8px 12px', background:'rgba(255,255,255,.02)', borderRadius:9 }}>
                  <div className={`av av-${['p','c','g','o'][i%4]}`} style={{ width:32, height:32, fontSize:'.8rem' }}>{s.name[0]}</div>
                  <div style={{ flex:1 }}>
                    <div style={{ fontSize:'.82rem', fontWeight:600 }}>{s.name}</div>
                    <div style={{ fontSize:'.68rem', color:'var(--muted)' }}>{s.class || 'N/A'}</div>
                  </div>
                  <span style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.8rem', color:s.avgAccuracy>=70?'var(--green)':s.avgAccuracy>=50?'var(--gold)':'var(--red)' }}>{s.avgAccuracy||0}%</span>
                </div>
              ))}
            </div>
          </div>

          {/* Battle History */}
          <div className="card">
            <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.88rem', marginBottom:14 }}>🏆 Battle History</div>
            {battleHistory.length === 0 && !loading && (
              <div style={{ textAlign:'center', padding:'24px 0' }}>
                <div style={{ fontSize:'2.5rem', marginBottom:8 }}>🏫</div>
                <p className="text-muted" style={{ fontSize:'.82rem' }}>No school battles yet. Host your first one!</p>
              </div>
            )}
            <div className="flex-col gap-8">
              {battleHistory.slice(0,5).map((b,i) => {
                const weAre = b.schoolAId?.toString()===user._id?.toString() ? 'A' : 'B'
                const ourScore = b.teams?.[weAre==='A'?0:1]?.score || 0
                const theirScore = b.teams?.[weAre==='A'?1:0]?.score || 0
                const won = ourScore > theirScore
                return (
                  <div key={i} style={{ display:'flex', alignItems:'center', gap:10, padding:'10px 12px', background:`rgba(${won?'0,227,150':'255,77,106'},.04)`, border:`1px solid rgba(${won?'0,227,150':'255,77,106'},.12)`, borderRadius:9 }}>
                    <span>{won?'🏆':'🥈'}</span>
                    <div style={{ flex:1 }}>
                      <div style={{ fontSize:'.82rem', fontWeight:600 }}>{b.schoolAName} vs {b.schoolBName}</div>
                      <div style={{ fontSize:'.68rem', color:'var(--muted)' }}>{new Date(b.endedAt).toLocaleDateString()}</div>
                    </div>
                    <span className={`badge ${won?'bg':'br'}`}>{won?'Won':'Lost'} {ourScore}–{theirScore}</span>
                  </div>
                )
              })}
            </div>
          </div>
        </div>

        <div style={{ display:'flex', gap:12, flexWrap:'wrap' }}>
          <button className="btn btn-grad" onClick={() => navigate('/quiz')}>📝 Standard Quiz</button>
          <button className="btn btn-ghost" onClick={() => navigate('/ai-quiz')}>🤖 AI Quiz</button>
          <button className="btn btn-ghost" onClick={() => navigate('/leaderboard')}>🏆 Leaderboard</button>
        </div>
      </div>
    </div>
  )
}
