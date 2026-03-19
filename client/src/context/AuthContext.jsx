import { createContext, useContext, useState, useEffect } from 'react'
import api from '../utils/api'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const stored = localStorage.getItem('qm_user')
    const token = localStorage.getItem('qm_token')
    if (stored && token) {
      setUser(JSON.parse(stored))
      api.get('/auth/me').then(r => { setUser(r.data); localStorage.setItem('qm_user', JSON.stringify(r.data)) }).catch(() => {})
    }
    setLoading(false)
  }, [])

  const register = async (data) => {
    const res = await api.post('/auth/register', data)
    return res.data
  }

  const verifyOTP = async (email, otp) => {
    const res = await api.post('/auth/verify-otp', { email, otp })
    localStorage.setItem('qm_token', res.data.token)
    localStorage.setItem('qm_user', JSON.stringify(res.data.user))
    setUser(res.data.user)
    return res.data
  }

  const login = async (email, password) => {
    const res = await api.post('/auth/login', { email, password })
    localStorage.setItem('qm_token', res.data.token)
    localStorage.setItem('qm_user', JSON.stringify(res.data.user))
    setUser(res.data.user)
    return res.data
  }

  const logout = () => {
    localStorage.removeItem('qm_token')
    localStorage.removeItem('qm_user')
    setUser(null)
  }

  const refreshUser = async () => {
    const res = await api.get('/auth/me')
    setUser(res.data)
    localStorage.setItem('qm_user', JSON.stringify(res.data))
    return res.data
  }

  return (
    <AuthContext.Provider value={{ user, loading, register, verifyOTP, login, logout, refreshUser, setUser }}>
      {children}
    </AuthContext.Provider>
  )
}

export const useAuth = () => useContext(AuthContext)
