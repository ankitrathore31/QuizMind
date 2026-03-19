import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import api from '../utils/api'
import toast from 'react-hot-toast'

export default function BattleMenu() {
  const navigate  = useNavigate()
  const [joinCode, setJoinCode] = useState('')
  const [joining,  setJoining]  = useState(false)

  const handleJoin = async e => {
    e.preventDefault()
    if (!joinCode.trim()) return toast.error('Enter room code')
    setJoining(true)
    try {
      await api.post(`/battle/${joinCode.toUpperCase()}/join`, {})
      navigate(`/battle/${joinCode.toUpperCase()}`)
    } catch (err) { toast.error(err.response?.data?.message || 'Room not found') }
    finally { setJoining(false) }
  }

  return (
    <div style={{ background:'var(--dark)', minHeight:'100vh' }}>
      <div className="orb o1"/><div className="orb o2"/>
      <div className="page anim-fade">
        <div style={{ marginBottom:32, textAlign:'center' }}>
          <h1 style={{ fontFamily:'var(--fh)', fontSize:'2rem', fontWeight:800, marginBottom:8 }}>⚔️ Battle Arena</h1>
          <p className="text-muted" style={{ fontSize:'.9rem' }}>Create a room or join an existing battle</p>
        </div>
        <div className="grid-2 gap-20 mb-24">
          <div className="card" style={{ background:'linear-gradient(135deg,rgba(124,92,252,.07),rgba(0,212,255,.04))', borderColor:'rgba(124,92,252,.2)' }}>
            <h2 style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.1rem', marginBottom:6 }}>🎮 Create a Battle Room</h2>
            <p className="text-muted mb-20" style={{ fontSize:'.83rem' }}>Generate an AI quiz first, then pick mode</p>
            <div className="flex-col gap-8">
              {[
                { icon:'⚡', label:'Solo Practice',  desc:'Play alone, no room needed' },
                { icon:'⚔️', label:'1v1 Battle',     desc:'Head-to-head with one opponent' },
                { icon:'👥', label:'Group Battle',   desc:'Up to 10 players • invite link' },
                { icon:'🏆', label:'Team vs Team',   desc:'2 teams • separate invite codes' },
              ].map(m => (
                <button key={m.label} onClick={() => navigate('/ai-quiz')}
                  style={{ display:'flex', alignItems:'center', gap:12, padding:'12px 16px', background:'rgba(255,255,255,.03)', border:'1px solid var(--border)', borderRadius:12, cursor:'pointer', textAlign:'left', transition:'all .2s', color:'var(--text)' }}
                  onMouseEnter={e=>{e.currentTarget.style.borderColor='rgba(124,92,252,.4)';e.currentTarget.style.background='rgba(124,92,252,.06)'}}
                  onMouseLeave={e=>{e.currentTarget.style.borderColor='var(--border)';e.currentTarget.style.background='rgba(255,255,255,.03)'}}
                >
                  <span style={{ fontSize:'1.5rem', flexShrink:0 }}>{m.icon}</span>
                  <div style={{ flex:1 }}>
                    <div style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.85rem' }}>{m.label}</div>
                    <div style={{ fontSize:'.72rem', color:'var(--muted)' }}>{m.desc}</div>
                  </div>
                  <span style={{ color:'var(--muted)' }}>→</span>
                </button>
              ))}
            </div>
          </div>
          <div className="card">
            <h2 style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.1rem', marginBottom:6 }}>🔗 Join a Battle Room</h2>
            <p className="text-muted mb-20" style={{ fontSize:'.83rem' }}>Enter room code shared by the host</p>
            <form onSubmit={handleJoin}>
              <div className="form-float">
                <input type="text" placeholder=" " value={joinCode}
                  onChange={e => setJoinCode(e.target.value.toUpperCase())}
                  style={{ fontSize:'1.1rem', fontFamily:'var(--fh)', fontWeight:700, letterSpacing:2 }}
                />
                <label>Room Code (e.g. A1B2C3)</label>
              </div>
              <button type="submit" className="btn btn-grad w-full" style={{ padding:13, marginTop:8 }} disabled={joining || !joinCode.trim()}>
                {joining ? <span className="spinner"/> : '🚀 Join Battle'}
              </button>
            </form>
            <div className="divider"/>
            <div style={{ background:'rgba(0,212,255,.05)', border:'1px solid rgba(0,212,255,.12)', borderRadius:10, padding:14, fontSize:'.8rem', color:'var(--muted)', lineHeight:1.7 }}>
              <div style={{ fontFamily:'var(--fh)', fontWeight:700, color:'var(--cyan)', marginBottom:6 }}>💡 How battles work</div>
              1. Host generates AI quiz → creates room<br/>
              2. Share code with players<br/>
              3. Everyone joins the waiting lobby<br/>
              4. Host starts — live scores update instantly<br/>
              5. Winner gets trophy + XP 🏆
            </div>
          </div>
        </div>
        <div className="card" style={{ background:'linear-gradient(135deg,rgba(255,184,0,.07),rgba(124,92,252,.04))', borderColor:'rgba(255,184,0,.2)', textAlign:'center', padding:28 }}>
          <div style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.1rem', marginBottom:8 }}>🏫 School vs School Battle</div>
          <p className="text-muted mb-16" style={{ fontSize:'.85rem' }}>Epic battles between two schools — up to 100+ students per side</p>
          <button className="btn btn-grad" onClick={() => navigate('/school-battle')}>Go to School Battle →</button>
          <div style={{ fontSize:'.72rem', color:'var(--muted)', marginTop:8 }}>Institution accounts only</div>
        </div>
      </div>
    </div>
  )
}
