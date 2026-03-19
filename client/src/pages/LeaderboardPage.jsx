// LeaderboardPage.jsx
import { useState, useEffect } from 'react'
import { useAuth } from '../context/AuthContext'
import api from '../utils/api'

export default function LeaderboardPage() {
  const { user } = useAuth()
  const [global, setGlobal] = useState([])
  const [school, setSchool] = useState([])
  const [myRank, setMyRank] = useState(0)
  const [tab,    setTab]    = useState('global')
  const [loading,setLoading]= useState(true)

  useEffect(() => {
    Promise.all([
      api.get('/leaderboard/global').then(r => { setGlobal(r.data.users); setMyRank(r.data.myRank) }).catch(()=>{}),
      api.get('/leaderboard/school').then(r => setSchool(r.data.users)).catch(()=>{}),
    ]).finally(() => setLoading(false))
  }, [])

  const list = tab === 'global' ? global : school
  const rankColors = ['var(--gold)','var(--muted)','var(--orange)']

  return (
    <div style={{ background:'var(--dark)', minHeight:'100vh' }}>
      <div className="orb o1"/><div className="orb o2"/>
      <div className="page page-md anim-fade">
        <div style={{ marginBottom:28, textAlign:'center' }}>
          <h1 style={{ fontFamily:'var(--fh)', fontSize:'1.8rem', fontWeight:800, marginBottom:8 }}>🏆 Leaderboard</h1>
          {myRank > 0 && <p className="text-muted" style={{ fontSize:'.85rem' }}>Your rank: <strong style={{ color:'var(--violet)' }}>#{myRank}</strong></p>}
        </div>
        <div className="tabs mb-20" style={{ margin:'0 auto 20px' }}>
          <button className={`tab ${tab==='global'?'active':''}`} onClick={()=>setTab('global')}>🌍 Global</button>
          <button className={`tab ${tab==='school'?'active':''}`} onClick={()=>setTab('school')}>🏫 My School</button>
        </div>
        {loading && <div style={{ textAlign:'center', padding:40 }}><span className="spinner"/></div>}
        {!loading && list.length === 0 && <p className="text-muted" style={{ textAlign:'center', padding:32, fontSize:'.85rem' }}>No data yet</p>}
        <div className="card">
          <div className="flex-col gap-6">
            {list.map((u2, i) => (
              <div key={i} style={{ display:'flex', alignItems:'center', gap:14, padding:'12px 16px', background:u2._id===user?._id?'rgba(124,92,252,.09)':'rgba(255,255,255,.02)', borderRadius:12, border:u2._id===user?._id?'1px solid rgba(124,92,252,.3)':'1px solid transparent' }}>
                <span style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.1rem', width:32, textAlign:'center', color:rankColors[i]||'var(--muted)', flexShrink:0 }}>
                  {i===0?'🥇':i===1?'🥈':i===2?'🥉':`#${i+1}`}
                </span>
                <div style={{ width:36, height:36, borderRadius:'50%', background:'var(--gradient)', display:'flex', alignItems:'center', justifyContent:'center', fontFamily:'var(--fh)', fontWeight:800, fontSize:'.9rem', flexShrink:0 }}>{u2.name?.[0]}</div>
                <div style={{ flex:1 }}>
                  <div style={{ fontWeight:600, fontSize:'.88rem' }}>{u2.name}{u2._id===user?._id?' (you)':''}</div>
                  <div style={{ fontSize:'.72rem', color:'var(--muted)' }}>{u2.class||''} {u2.college?`• ${u2.college}`:''}</div>
                </div>
                <div style={{ textAlign:'right' }}>
                  <div style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'.9rem', background:'var(--gradient)', WebkitBackgroundClip:'text', WebkitTextFillColor:'transparent' }}>{(u2.xp||0).toLocaleString()} XP</div>
                  <div style={{ fontSize:'.7rem', color:'var(--muted)' }}>Lv.{u2.level||1} • {u2.streak||0}🔥</div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  )
}
