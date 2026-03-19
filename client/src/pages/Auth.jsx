import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import toast from 'react-hot-toast'

export default function Auth() {
  const { register, login } = useAuth()
  const navigate = useNavigate()
  const [mode, setMode] = useState('login')
  const [role, setRole] = useState('student')
  const [loading, setLoading] = useState(false)
  const [form, setForm] = useState({ name:'', email:'', password:'', college:'', schoolGroup:'', refCode:'' })
  const set = (k, v) => setForm(f => ({ ...f, [k]: v }))

  const handleRegister = async e => {
    e.preventDefault()
    if (!form.name || !form.email || !form.password) return toast.error('Fill all required fields')
    setLoading(true)
    try {
      await register({ ...form, role })
      toast.success('OTP sent to your email! 📧')
      navigate('/verify-otp', { state: { email: form.email } })
    } catch (err) { toast.error(err.response?.data?.message || 'Registration failed') }
    finally { setLoading(false) }
  }

  const handleLogin = async e => {
    e.preventDefault()
    setLoading(true)
    try {
      const { user } = await login(form.email, form.password)
      toast.success(`Welcome back, ${user.name.split(' ')[0]}! 🎉`)
      navigate('/dashboard')
    } catch (err) {
      const msg = err.response?.data?.message || 'Login failed'
      if (err.response?.data?.needsVerification) {
        toast.error('Please verify your email first')
        navigate('/verify-otp', { state: { email: form.email } })
      } else toast.error(msg)
    }
    finally { setLoading(false) }
  }

  const roles = [
    { id:'student', icon:'👨‍🎓', label:'Student' },
    { id:'teacher', icon:'👨‍🏫', label:'Teacher' },
    { id:'institution', icon:'🏛️', label:'Institution' },
    { id:'parent', icon:'👨‍👩‍👧', label:'Parent' },
  ]

  return (
    <div style={{ minHeight:'100vh', background:'var(--dark)', display:'flex', alignItems:'center', justifyContent:'center', padding:'60px 16px', position:'relative', overflow:'hidden' }}>
      <div className="orb o1" /><div className="orb o2" />
      <div style={{ width:'100%', maxWidth:460, position:'relative', zIndex:1 }} className="anim-fade">
        <div className="card" style={{ borderRadius:24, padding:'34px 30px' }}>
          <Link to="/" style={{ display:'flex', alignItems:'center', gap:9, fontFamily:'var(--fh)', fontWeight:800, fontSize:'1rem', color:'var(--text)', textDecoration:'none', marginBottom:24 }}>
            <div className="brand-box">QM</div>Quiz<span className="accent">Mind</span> AI
          </Link>

          <div className="tabs mb-24">
            <button className={`tab ${mode==='login'?'active':''}`} onClick={() => setMode('login')}>Log In</button>
            <button className={`tab ${mode==='register'?'active':''}`} onClick={() => setMode('register')}>Sign Up</button>
          </div>

          {mode === 'login' ? (
            <form onSubmit={handleLogin}>
              <h2 style={{ fontFamily:'var(--fh)', fontSize:'1.5rem', fontWeight:800, marginBottom:4 }}>Welcome back 👋</h2>
              <p className="text-muted mb-24" style={{ fontSize:'.84rem' }}>Log in to continue your quiz journey</p>
              <div className="form-float">
                <input type="email" placeholder=" " value={form.email} onChange={e=>set('email',e.target.value)} required />
                <label>Email Address</label>
              </div>
              <div className="form-float">
                <input type="password" placeholder=" " value={form.password} onChange={e=>set('password',e.target.value)} required />
                <label>Password</label>
              </div>
              <button type="submit" className="btn btn-grad w-full" style={{ padding:13, marginTop:4 }} disabled={loading}>
                {loading ? <span className="spinner" /> : 'Log In →'}
              </button>
              <p style={{ textAlign:'center', marginTop:16, fontSize:'.8rem', color:'var(--muted)' }}>
                No account? <button type="button" onClick={()=>setMode('register')} style={{ background:'none', border:'none', color:'var(--violet)', cursor:'pointer', fontWeight:700 }}>Sign up free</button>
              </p>
            </form>
          ) : (
            <form onSubmit={handleRegister}>
              <h2 style={{ fontFamily:'var(--fh)', fontSize:'1.5rem', fontWeight:800, marginBottom:4 }}>Create Account 🚀</h2>
              <p className="text-muted mb-20" style={{ fontSize:'.84rem' }}>Join 50,000+ students competing nationwide</p>
              <div className="form-float">
                <input type="text" placeholder=" " value={form.name} onChange={e=>set('name',e.target.value)} required />
                <label>Full Name *</label>
              </div>
              <div className="form-float">
                <input type="email" placeholder=" " value={form.email} onChange={e=>set('email',e.target.value)} required />
                <label>Email Address *</label>
              </div>
              <div className="form-float">
                <input type="password" placeholder=" " value={form.password} onChange={e=>set('password',e.target.value)} required />
                <label>Password * (min 6 chars)</label>
              </div>
              <div className="form-float">
                <input type="text" placeholder=" " value={form.college} onChange={e=>set('college',e.target.value)} />
                <label>College / School Name</label>
              </div>
              {role === 'student' && (
                <div className="form-float">
                  <input type="text" placeholder=" " value={form.refCode} onChange={e=>set('refCode',e.target.value)} />
                  <label>Reference Code (Teacher/School/Parent)</label>
                </div>
              )}
              <div style={{ marginBottom:20 }}>
                <div style={{ fontSize:'.72rem', color:'var(--muted)', marginBottom:10, fontFamily:'var(--fh)', fontWeight:700, letterSpacing:'.5px', textTransform:'uppercase' }}>I am a:</div>
                <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:8 }}>
                  {roles.map(r => (
                    <button key={r.id} type="button" onClick={() => setRole(r.id)}
                      style={{ padding:'10px 8px', borderRadius:10, border:`1px solid ${role===r.id?'transparent':'var(--border)'}`, background:role===r.id?'var(--gradient)':'transparent', color:role===r.id?'#fff':'var(--muted)', fontSize:'.78rem', fontWeight:700, cursor:'pointer', fontFamily:'var(--fh)', transition:'all .2s', display:'flex', alignItems:'center', justifyContent:'center', gap:6 }}>
                      {r.icon} {r.label}
                    </button>
                  ))}
                </div>
              </div>
              <button type="submit" className="btn btn-grad w-full" style={{ padding:13 }} disabled={loading}>
                {loading ? <span className="spinner" /> : 'Create Account →'}
              </button>
            </form>
          )}
        </div>
      </div>
    </div>
  )
}
