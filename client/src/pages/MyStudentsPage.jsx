import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import api from '../utils/api'
import toast from 'react-hot-toast'

export default function MyStudentsPage() {
  const { user }  = useAuth()
  const navigate  = useNavigate()
  const [students,  setStudents]  = useState([])
  const [loading,   setLoading]   = useState(true)
  const [search,    setSearch]    = useState('')
  const [sortBy,    setSortBy]    = useState('name')
  const [filterPerf,setFilterPerf]= useState('all')
  const [selected,  setSelected]  = useState(null)

  useEffect(() => {
    api.get('/users/my-students').then(r => setStudents(r.data)).catch(()=>{}).finally(()=>setLoading(false))
  }, [])

  const copyRef = () => { navigator.clipboard.writeText(user?.refCode||''); toast.success('Ref code copied!') }

  const filtered = students
    .filter(s => s.name?.toLowerCase().includes(search.toLowerCase()) || s.class?.toLowerCase().includes(search.toLowerCase()))
    .filter(s => filterPerf==='all' ? true : filterPerf==='strong' ? s.avgAccuracy>=70 : filterPerf==='avg' ? (s.avgAccuracy>=50&&s.avgAccuracy<70) : s.avgAccuracy<50)
    .sort((a,b) => sortBy==='name' ? a.name?.localeCompare(b.name) : sortBy==='score' ? (b.avgAccuracy||0)-(a.avgAccuracy||0) : new Date(b.createdAt)-new Date(a.createdAt))

  const avg = students.length ? Math.round(students.reduce((a,s)=>a+(s.avgAccuracy||0),0)/students.length) : 0
  const strong = students.filter(s=>s.avgAccuracy>=70).length
  const weak   = students.filter(s=>s.avgAccuracy<50).length

  return (
    <div style={{ background:'var(--dark)', minHeight:'100vh' }}>
      <div className="orb o1"/><div className="orb o2"/>
      <div className="page anim-fade">
        <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:28, flexWrap:'wrap', gap:16 }}>
          <div>
            <h1 style={{ fontFamily:'var(--fh)', fontSize:'1.8rem', fontWeight:800, marginBottom:4 }}>👥 My Students</h1>
            <p className="text-muted" style={{ fontSize:'.85rem' }}>Manage and track your students' performance</p>
          </div>
          <div style={{ display:'flex', gap:10 }}>
            <button className="btn btn-ghost btn-sm" onClick={copyRef}>📋 Copy Ref Code: {user?.refCode}</button>
            <button className="btn btn-grad btn-sm" onClick={() => navigate('/ai-quiz')}>⚔️ Host Battle</button>
          </div>
        </div>

        {/* Stats */}
        <div className="grid-4 mb-20">
          <div className="stat-card"><div className="stat-val text-grad">{students.length}</div><div className="stat-label">Total Students</div></div>
          <div className="stat-card"><div className="stat-val" style={{ color:'var(--green)' }}>{strong}</div><div className="stat-label">Strong (≥70%)</div></div>
          <div className="stat-card"><div className="stat-val" style={{ color:'var(--red)' }}>{weak}</div><div className="stat-label">Need Help (&lt;50%)</div></div>
          <div className="stat-card"><div className="stat-val" style={{ color:'var(--cyan)' }}>{avg}%</div><div className="stat-label">Class Avg</div></div>
        </div>

        {/* Filters */}
        <div style={{ display:'flex', gap:10, marginBottom:20, flexWrap:'wrap', alignItems:'center' }}>
          <input value={search} onChange={e=>setSearch(e.target.value)} placeholder="Search by name or class..."
            style={{ padding:'9px 14px', background:'rgba(255,255,255,.05)', border:'1px solid var(--border)', borderRadius:10, color:'var(--text)', fontSize:'.83rem', outline:'none', flex:'1', minWidth:180 }}
          />
          <div className="tabs" style={{ padding:4 }}>
            {[['all','All'],['strong','Strong'],['avg','Average'],['weak','Needs Help']].map(([v,l])=>(
              <button key={v} className={`tab ${filterPerf===v?'active':''}`} onClick={()=>setFilterPerf(v)} style={{ fontSize:'.7rem', padding:'5px 10px' }}>{l}</button>
            ))}
          </div>
          <select value={sortBy} onChange={e=>setSortBy(e.target.value)}
            style={{ padding:'9px 14px', background:'rgba(255,255,255,.05)', border:'1px solid var(--border)', borderRadius:10, color:'var(--text)', fontSize:'.83rem', outline:'none' }}>
            <option value="name">Sort: Name</option>
            <option value="score">Sort: Score</option>
            <option value="recent">Sort: Recent</option>
          </select>
        </div>

        {loading && <div style={{ textAlign:'center', padding:40 }}><span className="spinner"/></div>}

        {!loading && students.length === 0 && (
          <div className="card" style={{ textAlign:'center', padding:48 }}>
            <div style={{ fontSize:'3rem', marginBottom:14 }}>👥</div>
            <h3 style={{ fontFamily:'var(--fh)', fontWeight:700, marginBottom:8 }}>No students yet</h3>
            <p className="text-muted mb-16" style={{ fontSize:'.85rem' }}>Share your reference code with students to link their accounts</p>
            <div style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.3rem', color:'var(--violet)', letterSpacing:2 }}>{user?.refCode}</div>
            <button className="btn btn-ghost btn-sm" style={{ marginTop:12 }} onClick={copyRef}>📋 Copy Code</button>
          </div>
        )}

        {!loading && students.length > 0 && (
          <div className="grid-2 gap-14">
            {/* Students list */}
            <div>
              <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.82rem', color:'var(--muted)', marginBottom:12 }}>
                {filtered.length} STUDENT{filtered.length!==1?'S':''}
              </div>
              <div className="flex-col gap-8">
                {filtered.map((s,i) => (
                  <div key={i}
                    onClick={() => setSelected(selected?._id===s._id ? null : s)}
                    style={{ display:'flex', alignItems:'center', gap:12, padding:'12px 16px', background:selected?._id===s._id?'rgba(124,92,252,.1)':'rgba(255,255,255,.02)', border:`1px solid ${selected?._id===s._id?'rgba(124,92,252,.35)':'var(--border)'}`, borderRadius:12, cursor:'pointer', transition:'all .2s' }}
                    onMouseEnter={e=>{if(selected?._id!==s._id){e.currentTarget.style.background='rgba(255,255,255,.04)';e.currentTarget.style.borderColor='rgba(255,255,255,.1)'}}}
                    onMouseLeave={e=>{if(selected?._id!==s._id){e.currentTarget.style.background='rgba(255,255,255,.02)';e.currentTarget.style.borderColor='var(--border)'}}}
                  >
                    <div className={`av av-${['p','c','g','o'][i%4]}`}>{s.name?.[0]}</div>
                    <div style={{ flex:1 }}>
                      <div style={{ fontWeight:600, fontSize:'.88rem', marginBottom:2 }}>{s.name}</div>
                      <div style={{ fontSize:'.7rem', color:'var(--muted)' }}>
                        {s.class||'N/A'} • {s.streak||0}🔥 streak
                        {s.weakSubjects?.length>0 && <span style={{ color:'var(--red)', marginLeft:6 }}>⚠️ {s.weakSubjects[0]}</span>}
                      </div>
                    </div>
                    <div style={{ textAlign:'right' }}>
                      <div style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'.9rem', color:s.avgAccuracy>=70?'var(--green)':s.avgAccuracy>=50?'var(--gold)':'var(--red)' }}>
                        {s.avgAccuracy||0}%
                      </div>
                      <div style={{ width:50, marginTop:4 }}>
                        <div className="progress-track">
                          <div className="progress-fill" style={{ width:`${s.avgAccuracy||0}%`, background:s.avgAccuracy>=70?'var(--grad3)':s.avgAccuracy>=50?'linear-gradient(90deg,var(--gold),var(--orange))':'linear-gradient(90deg,var(--red),var(--pink))' }}/>
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* Student detail */}
            <div>
              {selected ? (
                <div className="card" style={{ position:'sticky', top:80 }}>
                  <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:16 }}>
                    <div style={{ display:'flex', alignItems:'center', gap:12 }}>
                      <div className="av av-p" style={{ width:44, height:44, fontSize:'.9rem' }}>{selected.name?.[0]}</div>
                      <div>
                        <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'1rem' }}>{selected.name}</div>
                        <div style={{ fontSize:'.72rem', color:'var(--muted)' }}>{selected.class||'N/A'} • Joined {new Date(selected.createdAt).toLocaleDateString()}</div>
                      </div>
                    </div>
                    <button className="btn btn-ghost btn-sm" onClick={()=>setSelected(null)}>✕</button>
                  </div>

                  <div className="grid-2 gap-8 mb-16">
                    <div className="stat-card" style={{ padding:14 }}><div className="stat-val" style={{ fontSize:'1.3rem', color:'var(--green)' }}>{selected.avgAccuracy||0}%</div><div className="stat-label">Avg Accuracy</div></div>
                    <div className="stat-card" style={{ padding:14 }}><div className="stat-val" style={{ fontSize:'1.3rem', color:'var(--gold)' }}>{selected.streak||0}🔥</div><div className="stat-label">Streak</div></div>
                  </div>

                  {selected.weakSubjects?.length>0 && (
                    <div style={{ marginBottom:16 }}>
                      <div style={{ fontSize:'.72rem', fontFamily:'var(--fh)', fontWeight:700, color:'var(--muted)', letterSpacing:'.5px', textTransform:'uppercase', marginBottom:8 }}>Weak Subjects</div>
                      <div style={{ display:'flex', gap:6, flexWrap:'wrap' }}>
                        {selected.weakSubjects.map(s => <span key={s} className="badge br">{s}</span>)}
                      </div>
                    </div>
                  )}

                  <div style={{ marginBottom:16 }}>
                    <div style={{ fontSize:'.72rem', fontFamily:'var(--fh)', fontWeight:700, color:'var(--muted)', letterSpacing:'.5px', textTransform:'uppercase', marginBottom:8 }}>Recent Results</div>
                    {selected.recentResults?.length>0 ? selected.recentResults.map((r,i)=>(
                      <div key={i} style={{ display:'flex', justifyContent:'space-between', padding:'7px 0', borderBottom:'1px solid var(--border)', fontSize:'.78rem' }}>
                        <span style={{ color:'var(--muted)' }}>{r.subject||r.type} — {new Date(r.createdAt).toLocaleDateString()}</span>
                        <span style={{ fontFamily:'var(--fh)', fontWeight:700, color:r.accuracy>=70?'var(--green)':r.accuracy>=50?'var(--gold)':'var(--red)' }}>{r.accuracy}%</span>
                      </div>
                    )) : <p className="text-muted" style={{ fontSize:'.78rem' }}>No quiz history yet</p>}
                  </div>

                  <button className="btn btn-grad w-full btn-sm" onClick={() => navigate('/ai-quiz')}>⚔️ Host Battle with This Student</button>
                </div>
              ) : (
                <div className="card" style={{ textAlign:'center', padding:40 }}>
                  <div style={{ fontSize:'2.5rem', marginBottom:10 }}>👆</div>
                  <p className="text-muted" style={{ fontSize:'.85rem' }}>Click a student to view their detailed performance</p>
                </div>
              )}
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
