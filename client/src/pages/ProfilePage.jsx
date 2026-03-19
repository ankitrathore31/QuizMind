import { useState } from 'react'
import { useAuth } from '../context/AuthContext'
import api from '../utils/api'
import toast from 'react-hot-toast'

export default function ProfilePage() {
  const { user, refreshUser } = useAuth()
  const [form,    setForm]    = useState({ name:user?.name||'', college:user?.college||'' })
  const [loading, setLoading] = useState(false)
  const [copied,  setCopied]  = useState(false)

  const set = (k,v) => setForm(f => ({...f,[k]:v}))

  const handleSave = async e => {
    e.preventDefault()
    setLoading(true)
    try {
      await api.put('/users/profile', form)
      await refreshUser()
      toast.success('Profile updated!')
    } catch(err) { toast.error(err.response?.data?.message||'Failed') }
    finally { setLoading(false) }
  }

  const copyRef = () => {
    if (!user?.refCode) return
    navigator.clipboard.writeText(user.refCode)
    setCopied(true); toast.success('Ref code copied!')
    setTimeout(() => setCopied(false), 2000)
  }

  const xpLevel = Math.floor((user?.xp||0)/500)+1
  const xpInLvl = (user?.xp||0) % 500

  return (
    <div style={{ background:'var(--dark)', minHeight:'100vh' }}>
      <div className="orb o1"/><div className="orb o2"/>
      <div className="page page-md anim-fade">
        <div style={{ marginBottom:32 }}>
          <h1 style={{ fontFamily:'var(--fh)', fontSize:'1.8rem', fontWeight:800 }}>👤 My Profile</h1>
        </div>

        {/* Profile header card */}
        <div className="card mb-20" style={{ background:'linear-gradient(135deg,rgba(124,92,252,.08),rgba(0,212,255,.05))', borderColor:'rgba(124,92,252,.2)' }}>
          <div style={{ display:'flex', alignItems:'center', gap:20, flexWrap:'wrap' }}>
            <div style={{ width:72, height:72, borderRadius:'50%', background:'var(--gradient)', display:'flex', alignItems:'center', justifyContent:'center', fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.8rem', flexShrink:0 }}>
              {user?.name?.[0]?.toUpperCase()}
            </div>
            <div style={{ flex:1 }}>
              <h2 style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.3rem', marginBottom:4 }}>{user?.name}</h2>
              <div style={{ display:'flex', gap:8, flexWrap:'wrap' }}>
                <span className="badge bp" style={{ textTransform:'capitalize' }}>{user?.role}</span>
                {user?.class && <span className="badge bc">{user.class}</span>}
                {user?.college && <span className="badge bo">{user.college}</span>}
              </div>
            </div>
            {user?.role === 'student' && (
              <div style={{ textAlign:'right' }}>
                <div style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.4rem', background:'var(--gradient)', WebkitBackgroundClip:'text', WebkitTextFillColor:'transparent' }}>{(user.xp||0).toLocaleString()} XP</div>
                <div style={{ fontSize:'.72rem', color:'var(--muted)' }}>Level {xpLevel} • {user.streak||0}🔥 streak</div>
              </div>
            )}
          </div>
          {user?.role === 'student' && (
            <div style={{ marginTop:16 }}>
              <div style={{ display:'flex', justifyContent:'space-between', marginBottom:4, fontSize:'.72rem', color:'var(--muted)' }}>
                <span>Level {xpLevel}</span><span>{xpInLvl}/500 XP</span>
              </div>
              <div className="progress-track">
                <div className="progress-fill" style={{ width:`${(xpInLvl/500)*100}%` }}/>
              </div>
            </div>
          )}
        </div>

        {/* Ref Code (teacher/institution/parent) */}
        {user?.refCode && (
          <div className="card mb-20" style={{ background:'rgba(255,184,0,.05)', borderColor:'rgba(255,184,0,.2)' }}>
            <div style={{ fontFamily:'var(--fh)', fontWeight:700, marginBottom:10, color:'var(--gold)', fontSize:'.88rem' }}>🔗 Your Reference Code</div>
            <p className="text-muted mb-12" style={{ fontSize:'.8rem' }}>Share this code with students to link them to your account</p>
            <div style={{ display:'flex', gap:12, alignItems:'center', flexWrap:'wrap' }}>
              <div style={{ fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.4rem', letterSpacing:3, color:'var(--gold)', background:'rgba(255,184,0,.1)', border:'1px solid rgba(255,184,0,.25)', borderRadius:10, padding:'10px 20px' }}>{user.refCode}</div>
              <button className="btn btn-ghost" onClick={copyRef}>{copied?'✅ Copied!':'📋 Copy Code'}</button>
            </div>
          </div>
        )}

        {/* Edit form */}
        <div className="card">
          <h3 style={{ fontFamily:'var(--fh)', fontWeight:700, fontSize:'.92rem', marginBottom:20 }}>Edit Profile</h3>
          <form onSubmit={handleSave}>
            <div className="form-float">
              <input type="text" placeholder=" " value={form.name} onChange={e=>set('name',e.target.value)} required/>
              <label>Full Name</label>
            </div>
            <div className="form-float">
              <input type="email" value={user?.email||''} disabled style={{ opacity:.5 }}/>
              <label>Email (cannot change)</label>
            </div>
            <div className="form-float">
              <input type="text" placeholder=" " value={form.college} onChange={e=>set('college',e.target.value)}/>
              <label>College / School Name</label>
            </div>
            {user?.role === 'student' && (
              <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:12 }}>
                <div style={{ padding:'12px 16px', background:'rgba(255,255,255,.03)', borderRadius:10, border:'1px solid var(--border)' }}>
                  <div style={{ fontSize:'.7rem', color:'var(--muted)', marginBottom:4 }}>CLASS</div>
                  <div style={{ fontFamily:'var(--fh)', fontWeight:700 }}>{user.class || 'Not set'}</div>
                </div>
                <div style={{ padding:'12px 16px', background:'rgba(255,255,255,.03)', borderRadius:10, border:'1px solid var(--border)' }}>
                  <div style={{ fontSize:'.7rem', color:'var(--muted)', marginBottom:4 }}>INTERESTS</div>
                  <div style={{ fontSize:'.78rem', fontFamily:'var(--fh)', fontWeight:700 }}>{user.interests?.slice(0,3).join(', ')||'None'}</div>
                </div>
              </div>
            )}
            <button type="submit" className="btn btn-grad" style={{ marginTop:20 }} disabled={loading}>
              {loading ? <span className="spinner"/> : 'Save Changes'}
            </button>
          </form>
        </div>
      </div>
    </div>
  )
}
