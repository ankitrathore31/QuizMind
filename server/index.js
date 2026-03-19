require('dotenv').config()
const express = require('express')
const http = require('http')
const { Server } = require('socket.io')
const mongoose = require('mongoose')
const cors = require('cors')
const helmet = require('helmet')
const rateLimit = require('express-rate-limit')

const authRoutes = require('./routes/auth')
const userRoutes = require('./routes/user')
const quizRoutes = require('./routes/quiz')
const aiRoutes = require('./routes/ai')
const battleRoutes = require('./routes/battle')
const leaderboardRoutes = require('./routes/leaderboard')
const { initSocket } = require('./services/realtime/socket')

const app = express()
const server = http.createServer(app)

// Socket.IO
const io = new Server(server, {
  cors: { origin: process.env.CLIENT_URL || 'http://localhost:5173', methods: ['GET','POST'] }
})
initSocket(io)

// Middleware
app.use(helmet())
app.use(cors({ origin: process.env.CLIENT_URL || 'http://localhost:5173', credentials: true }))
app.use(express.json({ limit: '10mb' }))
app.use(express.urlencoded({ extended: true, limit: '10mb' }))

// Rate limiting
const limiter = rateLimit({ windowMs: 15 * 60 * 1000, max: 200 })
app.use('/api/', limiter)
const aiLimiter = rateLimit({ windowMs: 60 * 1000, max: 10 })
app.use('/api/ai/', aiLimiter)

// Routes
app.use('/api/auth', authRoutes)
app.use('/api/users', userRoutes)
app.use('/api/quiz', quizRoutes)
app.use('/api/ai', aiRoutes)
app.use('/api/battle', battleRoutes)
app.use('/api/leaderboard', leaderboardRoutes)

app.get('/api/health', (req, res) => res.json({ status: 'ok', time: new Date() }))

// DB + Start
mongoose.connect(process.env.MONGO_URI)
  .then(() => {
    console.log('✅ MongoDB connected')
    server.listen(process.env.PORT || 5000, () =>
      console.log(`🚀 Server running on port ${process.env.PORT || 5000}`)
    )
  })
  .catch(err => { console.error('MongoDB error:', err); process.exit(1) })
