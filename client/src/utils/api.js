import axios from 'axios'

const api = axios.create({ baseURL: '/api', timeout: 30000 })

api.interceptors.request.use(cfg => {
  const token = localStorage.getItem('qm_token')
  if (token) cfg.headers.Authorization = `Bearer ${token}`
  return cfg
})

api.interceptors.response.use(
  r => r,
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
