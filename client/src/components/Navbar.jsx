import { useState, useRef, useEffect } from 'react'
import { Link, NavLink, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import toast from 'react-hot-toast'

export default function Navbar() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const [drop, setDrop] = useState(false)
  const dropRef = useRef(null)

  useEffect(() => {
    const fn = e => { if (dropRef.current && !dropRef.current.contains(e.target)) setDrop(false) }
    document.addEventListener('mousedown', fn)
    return () => document.removeEventListener('mousedown', fn)
  }, [])

  const handleLogout = () => { setDrop(false); logout(); toast.success('Logged out'); navigate('/auth') }
  const initial = user?.name?.[0]?.toUpperCase() || 'U'

  const studentNav = [
    { to: '/dashboard', label: 'Dashboard' },
    { to: '/quiz', label: '📝 Quiz' },
    { to: '/ai-quiz', label: '🤖 AI Quiz' },
    { to: '/battle', label: '⚔️ Battle' },
    { to: '/ai-tutor', label: '💬 Tutor' },
    { to: '/leaderboard', label: '🏆 Ranks' },
  ]
  const teacherNav = [
    { to: '/dashboard', label: 'Dashboard' },
    { to: '/my-students', label: '👥 Students' },
    { to: '/quiz', label: '📝 Quiz' },
    { to: '/ai-quiz', label: '🤖 AI Quiz' },
    { to: '/battle', label: '⚔️ Battle' },
    { to: '/leaderboard', label: '🏆 Ranks' },
  ]
  const institutionNav = [
    { to: '/dashboard', label: 'Dashboard' },
    { to: '/my-students', label: '👥 Students' },
    { to: '/quiz', label: '📝 Quiz' },
    { to: '/ai-quiz', label: '🤖 AI Quiz' },
    { to: '/school-battle', label: '🏫 School Battle' },
    { to: '/leaderboard', label: '🏆 Ranks' },
  ]
  const parentNav = [
    { to: '/dashboard', label: 'Dashboard' },
    { to: '/dashboard', label: '👨‍👧 My Children' },
  ]

  const navMap = { student: studentNav, teacher: teacherNav, institution: institutionNav, parent: parentNav }
  const NAV = navMap[user?.role] || studentNav

  const roleColors = {
    teacher: { bg: 'rgba(0,212,255,.1)', border: 'rgba(0,212,255,.25)', color: 'var(--cyan)', icon: '👨‍🏫' },
    institution: { bg: 'rgba(255,184,0,.1)', border: 'rgba(255,184,0,.25)', color: 'var(--gold)', icon: '🏛️' },
    parent: { bg: 'rgba(255,107,157,.1)', border: 'rgba(255,107,157,.25)', color: 'var(--pink)', icon: '👨‍👩‍👧' },
  }
  const rc = roleColors[user?.role]

  return (
    <nav className="qm-navbar">
      <Link to="/dashboard" className="nav-brand">
        <div className="brand-box">QM</div>
        Quiz<span className="accent">Mind</span> AI
      </Link>

      <div className="flex gap-4" style={{ overflow: 'auto' }}>
        {NAV.map(l => (
          <NavLink key={l.to + l.label} to={l.to}
            className={({ isActive }) => `nav-link${isActive ? ' active' : ''}`}>
            {l.label}
          </NavLink>
        ))}
      </div>

      <div className="flex gap-8" style={{ marginLeft: 'auto', paddingLeft: 12 }}>
        {user?.role === 'student' && <div className="xp-pill">⚡ {(user.xp || 0).toLocaleString()} XP</div>}
        {rc && (
          <div style={{ padding:'4px 12px', borderRadius:100, background:rc.bg, border:`1px solid ${rc.border}`, color:rc.color, fontSize:'.72rem', fontWeight:700, fontFamily:'var(--fh)', whiteSpace:'nowrap' }}>
            {rc.icon} {user.role}
          </div>
        )}
        <div style={{ position: 'relative' }} ref={dropRef}>
          <button className="avatar-btn" onClick={() => setDrop(d => !d)}>
            <div className="av-circle">{initial}</div>
            <span>{user?.name?.split(' ')[0]}</span> ▾
          </button>
          {drop && (
            <div className="dd-menu">
              <div style={{ padding:'8px 14px 8px', borderBottom:'1px solid var(--border2)', marginBottom:4 }}>
                <div style={{ fontSize:'.84rem', fontWeight:700 }}>{user.name}</div>
                <div style={{ fontSize:'.72rem', color:'var(--muted)', textTransform:'capitalize' }}>{user.role}</div>
              </div>
              {user.refCode && (
                <div style={{ padding:'6px 14px', borderBottom:'1px solid var(--border2)', marginBottom:4 }}>
                  <div style={{ fontSize:'.68rem', color:'var(--muted)', marginBottom:3 }}>Your Ref Code</div>
                  <div style={{ fontFamily:'var(--fh)', fontSize:'.8rem', fontWeight:800, color:'var(--violet)', letterSpacing:1 }}>{user.refCode}</div>
                </div>
              )}
              <Link to="/profile" className="dd-item" onClick={() => setDrop(false)}>👤 My Profile</Link>
              <Link to="/history" className="dd-item" onClick={() => setDrop(false)}>📋 History</Link>
              <Link to="/leaderboard" className="dd-item" onClick={() => setDrop(false)}>🏆 Leaderboard</Link>
              <div className="divider" />
              <div className="dd-item danger" onClick={handleLogout}>🚪 Logout</div>
            </div>
          )}
        </div>
      </div>
    </nav>
  )
}
