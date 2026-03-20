require('dotenv').config();

const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const mongoose = require('mongoose');
const cors = require('cors');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');

// Routes
const authRoutes = require('./routes/auth');
const userRoutes = require('./routes/user');
const quizRoutes = require('./routes/quiz');
const aiRoutes = require('./routes/ai');
const battleRoutes = require('./routes/battle');
const leaderboardRoutes = require('./routes/leaderboard');
const { initSocket } = require('./services/realtime/socket');

const app = express();
const server = http.createServer(app);

// 🔴 GLOBAL ERROR HANDLERS (VERY IMPORTANT)
process.on('uncaughtException', err => {
  console.error('❌ UNCAUGHT EXCEPTION:', err);
});

process.on('unhandledRejection', err => {
  console.error('❌ UNHANDLED REJECTION:', err);
});

// 🔍 DEBUG ENV (remove later)
console.log('🔍 MONGO_URI:', process.env.MONGO_URI ? 'Loaded ✅' : 'Missing ❌');
console.log('🔍 CLIENT_URL:', process.env.CLIENT_URL);

// Socket.IO
const io = new Server(server, {
  cors: {
    origin: process.env.CLIENT_URL || 'http://localhost:5173',
    methods: ['GET', 'POST']
  }
});
initSocket(io);

// Middleware
app.use(helmet());
app.use(cors({
  origin: process.env.CLIENT_URL || 'http://localhost:5173',
  credentials: true
}));

app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Rate limiting
const limiter = rateLimit({ windowMs: 15 * 60 * 1000, max: 200 });
app.use('/api/', limiter);

const aiLimiter = rateLimit({ windowMs: 60 * 1000, max: 10 });
app.use('/api/ai/', aiLimiter);

// Routes
app.use('/api/auth', authRoutes);
app.use('/api/users', userRoutes);
app.use('/api/quiz', quizRoutes);
app.use('/api/ai', aiRoutes);
app.use('/api/battle', battleRoutes);
app.use('/api/leaderboard', leaderboardRoutes);

app.get('/api/health', (req, res) => {
  res.json({ status: 'ok', time: new Date() });
});

// ✅ START SERVER FUNCTION
const startServer = async () => {
  try {
    // 🔴 CHECK ENV VARIABLE
    if (!process.env.MONGO_URI) {
      throw new Error('MONGO_URI is not defined in environment variables');
    }

    // ✅ CONNECT MONGODB
    await mongoose.connect(process.env.MONGO_URI, {
      useNewUrlParser: true,
      useUnifiedTopology: true,
    });

    console.log('✅ MongoDB connected');

    // ✅ START SERVER
    const PORT = process.env.PORT || 5000;

    server.listen(PORT, () => {
      console.log(`🚀 Server running on port ${PORT}`);
    });

  } catch (err) {
    console.error('❌ STARTUP ERROR:', err.message);
    process.exit(1);
  }
};

startServer();
