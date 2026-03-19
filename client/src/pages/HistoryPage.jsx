import { useState, useEffect } from 'react'
import api from '../utils/api'

const typeIcon = { solo:'⚡', '1v1':'⚔️', group:'👥', team:'🏆', school:'🏫', standard:'📝' }
const typeLabel = { solo:'Solo', '1v1':'1v1 Battle', group:'Group Battle', team:'Team Battle', school:'School Battle', standard:'Standard' }

export default function HistoryPage() {
  const [history, setHistory] = useState([])
  const [loading, setLoading] = useState(true)
  const [filter,  setFilter]  = useState('all')

  useEffect(() => {
    api.get('/users/history').then(r => setHistory(r.data)).catch(()=>{}).finally(()=>setLoading(false))
  }, [])

  const filtered = filter === 'all' ? history : history.filter(r => r.type === filter)
  const avgAcc   = history.length ? Math.round(history.reduce((a,r)=>a+r.accuracy,0)/history.length) : 0
  const totalXP  = history.reduce((a,r)=>a+(r.xpEarned||0),0)

  return (
    <div style={{ background:'var(--dark)', minHeight:'100vh' }}>
      <div className="orb o1"/><div className="orb o2"/>
      <div className="page anim-fade">
        <div style={{ marginBottom:28 }}>
          <h1 style={{ fontFamily:'var(--fh)', fontSize:'1.8rem', fontWeight:800, marginBottom:6 }}>📋 Quiz History</h1>
          <p className="text-muted" style={{ fontSize:'.86rem' }}>All your past quizzes and battles</p>
        </div>

        <div className="grid-3 mb-20">
          <div className="stat-card"><div className="stat-val text-grad">{history.length}</div><div className="stat-label">Total Sessions</div></div>
          <div className="stat-card"><div className="stat-val" style={{ color:'var(--green)' }}>{avgAcc}%</div><div className="stat-label">Avg Accuracy</div></div>
          <div className="stat-card"><div className="stat-val" style={{ color:'var(--gold)' }}>+{totalXP.toLocaleString()}</div><div className="stat-label">XP Earned</div></div>
        </div>

        {/* Filter tabs */}
        <div className="tabs mb-20">
          {[['all','All'],['solo','Solo'],['standard','Standard'],['group','Multiplayer'],['team','Team'],['school','School']].map(([v,l]) => (
            <button key={v} className={`tab ${filter===v?'active':''}`} onClick={()=>setFilter(v)}>{l}</button>
          ))}
        </div>

        {loading && <div style={{ textAlign:'center', padding:40 }}><span className="spinner"/></div>}
        {!loading && filtered.length === 0 && (
          <div style={{ textAlign:'center', padding:48 }}>
            <div style={{ fontSize:'3rem', marginBottom:12 }}>📭</div>
            <p className="text-muted" style={{ fontSize:'.85rem' }}>No history yet for this filter</p>
          </div>
        )}
        <div className="flex-col gap-8">
          {filtered.map((r, i) => (
            <div key={i} className="card-sm" style={{ display:'flex', alignItems:'center', gap:14 }}>
              <div style={{ width:44, height:44, borderRadius:12, background:'rgba(124,92,252,.12)', display:'flex', alignItems:'center', justifyContent:'center', fontSize:'1.4rem', flexShrink:0 }}>{typeIcon[r.type]||'📝'}</div>
              <div style={{ flex:1 }}>
                <div style={{ fontWeight:600, fontSize:'.88rem', marginBottom:3 }}>
                  {typeLabel[r.type]||r.type}
                  {r.subject ? ` — ${r.subject}` : ''}
                  {r.topic ? ` (${r.topic})` : ''}
                </div>
                <div style={{ fontSize:'.72rem', color:'var(--muted)' }}>
                  {new Date(r.createdAt).toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'})}
                  {r.difficulty ? ` • ${r.difficulty}` : ''}
                </div>
              </div>
              <div style={{ textAlign:'right', flexShrink:0 }}>
                <div style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'.95rem', color:r.accuracy>=80?'var(--green)':r.accuracy>=60?'var(--gold)':'var(--red)' }}>{r.accuracy}%</div>
                <div style={{ fontSize:'.7rem', color:'var(--muted)' }}>{r.score||0}/{r.totalQ||0} • +{r.xpEarned||0} XP</div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}
