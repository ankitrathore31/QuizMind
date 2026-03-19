import { useState, useEffect } from 'react'
import { useLocation, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import toast from 'react-hot-toast'
import api from '../utils/api'

export default function VerifyOTP() {
  const { verifyOTP } = useAuth()
  const navigate = useNavigate()
  const { state } = useLocation()
  const email = state?.email || ''
  const [otp, setOtp] = useState('')
  const [loading, setLoading] = useState(false)
  const [resendCooldown, setResendCooldown] = useState(60)

  useEffect(() => {
    if (!email) { navigate('/auth'); return }
    const t = setInterval(() => setResendCooldown(c => c > 0 ? c - 1 : 0), 1000)
    return () => clearInterval(t)
  }, [email])

  const handleVerify = async e => {
    e.preventDefault()
    if (otp.length !== 6) return toast.error('Enter 6-digit OTP')
    setLoading(true)
    try {
      const { user } = await verifyOTP(email, otp)
      toast.success('Email verified! 🎉')
      navigate(user.role === 'student' && !user.onboardingComplete ? '/onboarding' : '/dashboard')
    } catch (err) { toast.error(err.response?.data?.message || 'Invalid OTP') }
    finally { setLoading(false) }
  }

  const handleResend = async () => {
    if (resendCooldown > 0) return
    try {
      await api.post('/auth/resend-otp', { email })
      toast.success('OTP resent!')
      setResendCooldown(60)
    } catch { toast.error('Failed to resend') }
  }

  return (
    <div style={{ minHeight:'100vh', background:'var(--dark)', display:'flex', alignItems:'center', justifyContent:'center', padding:'40px 16px' }}>
      <div className="orb o1" /><div className="orb o2" />
      <div style={{ width:'100%', maxWidth:420, zIndex:1 }} className="anim-pop">
        <div className="card" style={{ padding:'36px 30px', textAlign:'center' }}>
          <div style={{ fontSize:'3rem', marginBottom:16 }}>📧</div>
          <h2 style={{ fontFamily:'var(--fh)', fontSize:'1.5rem', fontWeight:800, marginBottom:8 }}>Check your email</h2>
          <p className="text-muted mb-24" style={{ fontSize:'.85rem' }}>
            We sent a 6-digit code to<br />
            <strong style={{ color:'var(--violet)' }}>{email}</strong>
          </p>
          <form onSubmit={handleVerify}>
            <div className="form-float">
              <input
                type="text" placeholder=" " value={otp}
                onChange={e => setOtp(e.target.value.replace(/\D/g,'').slice(0,6))}
                style={{ fontSize:'2rem', letterSpacing:'12px', textAlign:'center', fontFamily:'var(--fh)', fontWeight:800 }}
                required
              />
              <label style={{ textAlign:'center', left:'50%', transform:'translateX(-50%)' }}>Enter OTP</label>
            </div>
            <button type="submit" className="btn btn-grad w-full" style={{ padding:13, marginTop:8 }} disabled={loading || otp.length !== 6}>
              {loading ? <span className="spinner" /> : 'Verify Email →'}
            </button>
          </form>
          <button onClick={handleResend} style={{ background:'none', border:'none', color:resendCooldown>0?'var(--muted)':'var(--violet)', cursor:resendCooldown>0?'default':'pointer', marginTop:16, fontSize:'.82rem', fontWeight:700 }}>
            {resendCooldown > 0 ? `Resend in ${resendCooldown}s` : '↺ Resend OTP'}
          </button>
        </div>
      </div>
    </div>
  )
}
