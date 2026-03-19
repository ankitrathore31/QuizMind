import { useState, useEffect } from 'react'
import { useAuth } from '../context/AuthContext'
import api from '../utils/api'

export default function ParentDashboard() {
  const { user } = useAuth()
  const [children, setChildren] = useState([])
  const [selected, setSelected] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    api.get('/users/my-children').then(r => { setChildren(r.data); if(r.data.length) setSelected(r.data[0]) }).catch(()=>{}).finally(()=>setLoading(false))
  }, [])

  return (
    <div style={{ background:'var(--dark)', minHeight:'100vh' }}>
      <div className="orb o1" /><div className="orb o2" />
      <div className="page anim-fade">
        <div style={{ marginBottom:28 }}>
          <div style={{ fontFamily:'var(--fh)', fontSize:'.7rem', color:'var(--muted)', letterSpacing:'1.5px', textTransform:'uppercase', marginBottom:6 }}>Parent Dashboard</div>
          <h1 style={{ fontFamily:'var(--fh)', fontSize:'1.8rem', fontWeight:800 }}>
            Hello, <span className="text-grad">{user?.name?.split(' ')[0]}</span> 👨‍👩‍👧
          </h1>
          <p className="text-muted" style={{ fontSize:'.85rem', marginTop:4 }}>Track your children's learning progress</p>
        </div>

        {loading && <div style={{ textAlign:'center', padding:40 }}><span className="spinner" /></div>}

        {!loading && children.length === 0 && (
          <div className="card" style={{ textAlign:'center', padding:48 }}>
            <div style={{ fontSize:'3rem', marginBottom:12 }}>👨‍👩‍👧</div>
            <h3 style={{ fontFamily:'var(--fh)', fontWeight:700, marginBottom:8 }}>No children linked yet</h3>
            <p className="text-muted" style={{ fontSize:'.85rem', marginBottom:16 }}>Share your reference code with your child to link their account</p>
            <div style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.2rem', color:'var(--pink)', letterSpacing:2 }}>{user?.refCode}</div>
          </div>
        )}

        {!loading && children.length > 0 && (
          <>
            {/* Child selector */}
            <div style={{ display:'flex', gap:10, marginBottom:24, flexWrap:'wrap' }}>
              {children.map(c => (
                <button key={c._id} onClick={()=>setSelected(c)}
                  style={{ padding:'10px 18px', borderRadius:12, border:`1px solid ${selected?._id===c._id?'rgba(124,92,252,.5)':'var(--border)'}`, background:selected?._id===c._id?'rgba(124,92,252,.12)':'rgba(255,255,255,.03)', cursor:'pointer', fontFamily:'var(--fh)', fontWeight:700, fontSize:'.82rem', color:selected?._id===c._id?'var(--violet)':'var(--muted)', transition:'all .2s' }}>
                  👤 {c.name}
                </button>
              ))}
            </div>

            {selected && (
              <>
                <div className="grid-4 mb-20">
                  <div className="stat-card"><div className="stat-val" style={{ color:'var(--violet)' }}>{selected.name?.split(' ')[0]}</div><div className="stat-label">Child</div></div>
                  <div className="stat-card"><div className="stat-val" style={{ color:'var(--green)' }}>{selected.avgAccuracy||0}%</div><div className="stat-label">Avg Accuracy</div></div>
                  <div className="stat-card"><div className="stat-val" style={{ color:'var(--gold)' }}>{selected.streak||0}🔥</div><div className="stat-label">Day Streak</div></div>
                  <div className="stat-card"><div className="stat-val text-grad">{(selected.xp||0).toLocaleString()}</div><div className="stat-label">XP</div></div>
                </div>

                <div className="grid-2 mb-20">
                  {/* Subject stats */}
                  <div className="card">
                    <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.88rem', marginBottom:14 }}>📊 Subject Performance</div>
                    {(selected.subjectStats||[]).length === 0 && <p className="text-muted" style={{ fontSize:'.82rem' }}>No quiz data yet</p>}
                    <div className="flex-col gap-10">
                      {(selected.subjectStats||[]).slice(0,6).map(s => (
                        <div key={s.subject}>
                          <div style={{ display:'flex', justifyContent:'space-between', marginBottom:4 }}>
                            <span style={{ fontSize:'.82rem' }}>{s.subject}</span>
                            <span style={{ fontSize:'.82rem', fontWeight:700, color:s.avg>=70?'var(--green)':s.avg>=50?'var(--gold)':'var(--red)' }}>{s.avg}%</span>
                          </div>
                          <div className="progress-track">
                            <div className="progress-fill" style={{ width:`${s.avg}%`, background:s.avg>=70?'var(--grad3)':s.avg>=50?'linear-gradient(90deg,var(--gold),var(--orange))':'linear-gradient(90deg,var(--red),var(--pink))' }} />
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>

                  {/* AI Suggestions */}
                  <div className="card" style={{ background:'linear-gradient(135deg,rgba(255,107,157,.05),rgba(124,92,252,.05))', borderColor:'rgba(255,107,157,.2)' }}>
                    <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.88rem', marginBottom:12, color:'var(--pink)' }}>🤖 AI Suggestions for {selected.name?.split(' ')[0]}</div>
                    {(selected.weakSubjects||[]).length > 0 ? (
                      <>
                        <p style={{ fontSize:'.83rem', color:'var(--muted)', lineHeight:1.7, marginBottom:12 }}>
                          {selected.name?.split(' ')[0]} needs improvement in {selected.weakSubjects.join(', ')}. Consider allocating 20–30 minutes daily for practice in these areas.
                        </p>
                        <div style={{ display:'flex', gap:6, flexWrap:'wrap' }}>
                          {selected.weakSubjects.map(s => <span key={s} className="badge br">{s}</span>)}
                        </div>
                      </>
                    ) : (
                      <p style={{ fontSize:'.83rem', color:'var(--muted)', lineHeight:1.7 }}>
                        {selected.name?.split(' ')[0]} is performing well! Keep encouraging regular practice to maintain this streak. 🌟
                      </p>
                    )}
                  </div>
                </div>

                {/* Recent activity */}
                <div className="card">
                  <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.88rem', marginBottom:14 }}>📋 Recent Quiz Activity</div>
                  {(selected.recentResults||[]).length === 0 && <p className="text-muted" style={{ fontSize:'.82rem' }}>No quizzes yet</p>}
                  <div className="flex-col gap-8">
                    {(selected.recentResults||[]).map((r,i) => (
                      <div key={i} style={{ display:'flex', alignItems:'center', gap:12, padding:'9px 14px', background:'rgba(255,255,255,.02)', borderRadius:9 }}>
                        <span style={{ fontSize:'1.1rem' }}>📝</span>
                        <div style={{ flex:1 }}>
                          <div style={{ fontSize:'.83rem', fontWeight:500 }}>{r.subject || 'Quiz'} — {r.type}</div>
                          <div style={{ fontSize:'.7rem', color:'var(--muted)' }}>{new Date(r.createdAt).toLocaleDateString()}</div>
                        </div>
                        <span style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'.88rem', color:r.accuracy>=70?'var(--green)':r.accuracy>=50?'var(--gold)':'var(--red)' }}>{r.accuracy}%</span>
                      </div>
                    ))}
                  </div>
                </div>
              </>
            )}
          </>
        )}
      </div>
    </div>
  )
}
