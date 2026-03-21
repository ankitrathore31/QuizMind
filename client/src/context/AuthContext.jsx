import { createContext, useContext, useState, useEffect } from 'react'
import api from '../utils/api'

const AuthContext = createContext(null)

// 🔒 Safe JSON parser (prevents crash)
function safeParse(value) {
  try {
    return value ? JSON.parse(value) : null
  } catch (err) {
    console.error('Invalid JSON:', value)
    return null
  }
}

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const stored = localStorage.getItem('qm_user')
    const token = localStorage.getItem('qm_token')

    const parsedUser = safeParse(stored)

    // ❌ If corrupted data → clean it
    if (!parsedUser && stored) {
      localStorage.removeItem('qm_user')
    }

    if (parsedUser && token) {
      setUser(parsedUser)

      // 🔄 Sync with backend
      api.get('/auth/me')
        .then(res => {
          setUser(res.data)
          localStorage.setItem('qm_user', JSON.stringify(res.data))
        })
        .catch(err => {
          console.warn('Auth sync failed:', err?.response?.status)

          // 🔐 If token invalid → logout
          if (err.response?.status === 401) {
            localStorage.removeItem('qm_token')
            localStorage.removeItem('qm_user')
            setUser(null)
          }
        })
        .finally(() => setLoading(false))
    } else {
      setLoading(false)
    }
  }, [])

  /* =========================
     REGISTER
  ========================= */
  const register = async (data) => {
    const res = await api.post('/auth/register', data)
    return res.data
  }

  /* =========================
     VERIFY OTP
  ========================= */
  const verifyOTP = async (email, otp) => {
    const res = await api.post('/auth/verify-otp', { email, otp })

    localStorage.setItem('qm_token', res.data.token)
    localStorage.setItem('qm_user', JSON.stringify(res.data.user))

    setUser(res.data.user)
    return res.data
  }

  /* =========================
     LOGIN
  ========================= */
  const login = async (email, password) => {
    const res = await api.post('/auth/login', { email, password })

    localStorage.setItem('qm_token', res.data.token)
    localStorage.setItem('qm_user', JSON.stringify(res.data.user))

    setUser(res.data.user)
    return res.data
  }

  /* =========================
     LOGOUT
  ========================= */
  const logout = () => {
    localStorage.removeItem('qm_token')
    localStorage.removeItem('qm_user')
    setUser(null)
  }

  /* =========================
     REFRESH USER
  ========================= */
  const refreshUser = async () => {
    const res = await api.get('/auth/me')

    setUser(res.data)
    localStorage.setItem('qm_user', JSON.stringify(res.data))

    return res.data
  }

  return (
    <AuthContext.Provider
      value={{
        user,
        loading,
        register,
        verifyOTP,
        login,
        logout,
        refreshUser,
        setUser
      }}
    >
      {children}
    </AuthContext.Provider>
  )
}

export const useAuth = () => useContext(AuthContext)
