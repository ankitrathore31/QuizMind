import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import toast from 'react-hot-toast'
import api from '../utils/api'

const INTERESTS = ['Math','Physics','Chemistry','Biology','English','History','Geography','Computer Science','Economics','GK','Hindi','Science']
const CLASSES = ['Class 1','Class 2','Class 3','Class 4','Class 5','Class 6','Class 7','Class 8','Class 9','Class 10','Class 11','Class 12','Undergraduate','Postgraduate']

export default function Onboarding() {
  const { refreshUser } = useAuth()
  const navigate = useNavigate()
  const [cls, setCls] = useState('Class 10')
  const [age, setAge] = useState('')
  const [interests, setInterests] = useState([])
  const [loading, setLoading] = useState(false)

  const toggle = sub => setInterests(prev => prev.includes(sub) ? prev.filter(x=>x!==sub) : [...prev, sub])

  const handleSubmit = async e => {
    e.preventDefault()
    if (!age) return toast.error('Please enter your age')
    setLoading(true)
    try {
      await api.post('/auth/onboarding', { class: cls, age: Number(age), interests })
      await refreshUser()
      toast.success('Profile set up! Let\'s go 🚀')
      navigate('/dashboard')
    } catch (err) { toast.error(err.response?.data?.message || 'Failed') }
    finally { setLoading(false) }
  }

  return (
    <div style={{ minHeight:'100vh', background:'var(--dark)', display:'flex', alignItems:'center', justifyContent:'center', padding:'40px 16px' }}>
      <div className="orb o1" /><div className="orb o2" />
      <div style={{ width:'100%', maxWidth:520, zIndex:1 }} className="anim-fade">
        <div style={{ textAlign:'center', marginBottom:32 }}>
          <div style={{ fontSize:'3rem', marginBottom:12 }}>🎓</div>
          <h1 style={{ fontFamily:'var(--fh)', fontSize:'1.8rem', fontWeight:800, marginBottom:8 }}>Tell us about yourself</h1>
          <p className="text-muted" style={{ fontSize:'.88rem' }}>One-time setup to personalize your experience</p>
        </div>
        <div className="card" style={{ padding:28 }}>
          <form onSubmit={handleSubmit}>
            <div className="form-float">
              <select value={cls} onChange={e=>setCls(e.target.value)}>
                {CLASSES.map(c => <option key={c} value={c}>{c}</option>)}
              </select>
              <label>Your Class / Grade</label>
            </div>
            <div className="form-float">
              <input type="number" placeholder=" " value={age} onChange={e=>setAge(e.target.value)} min={5} max={40} />
              <label>Your Age</label>
            </div>
            <div style={{ marginBottom:20 }}>
              <div style={{ fontSize:'.72rem', color:'var(--muted)', fontFamily:'var(--fh)', fontWeight:700, letterSpacing:'.5px', textTransform:'uppercase', marginBottom:10 }}>
                Interests (select all that apply)
              </div>
              <div style={{ display:'flex', flexWrap:'wrap', gap:8 }}>
                {INTERESTS.map(sub => (
                  <button key={sub} type="button" onClick={() => toggle(sub)}
                    className={`subject-pill${interests.includes(sub)?' active':''}`}>
                    {sub}
                  </button>
                ))}
              </div>
            </div>
            <button type="submit" className="btn btn-grad w-full" style={{ padding:13 }} disabled={loading}>
              {loading ? <span className="spinner" /> : 'Let\'s Go! 🚀'}
            </button>
          </form>
        </div>
      </div>
    </div>
  )
}
