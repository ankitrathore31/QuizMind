import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  timeout: 30000
})

// 🔐 Attach token
api.interceptors.request.use(cfg => {
  const token = localStorage.getItem('qm_token')
  if (token) {
    cfg.headers.Authorization = `Bearer ${token}`
  }
  return cfg
})

// ⚠️ Handle auth errors
api.interceptors.response.use(
  res => res,
  err => {
    if (err.response?.status === 401) {
      localStorage.removeItem('qm_token')
      localStorage.removeItem('qm_user')
      window.location.href = '/auth'
    }
    return Promise.reject(err)
  }
)

export default api
