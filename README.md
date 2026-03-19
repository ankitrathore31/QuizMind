# QuizMind AI 🚀

> AI-powered quiz battle platform for students, teachers, institutions & parents.

---

## 🔥 Features

- **AI Quiz Generation** — MCQs from topic, PDF, or educational image (Groq AI)
- **Live Battle System** — 1v1, Group, Team vs Team, School vs School
- **Real-time** — Socket.IO with anti-cheat detection
- **4 Role Dashboards** — Student, Teacher, Institution, Parent
- **XP & Gamification** — Levels, streaks, trophies, leaderboards
- **Anti-cheat** — Tab switch detection, point penalties
- **AI Study Tutor** — Academic chatbot with safety filter

---

## 🛠 Tech Stack

| Layer | Tech |
|-------|------|
| Frontend | React 18 + Vite + Framer Motion + Three.js |
| Backend | Node.js + Express + Socket.IO |
| Database | MongoDB + Mongoose |
| AI | Groq (llama-3.3-70b + llama-4-scout vision) |
| Realtime | Socket.IO + Redis |
| Auth | JWT + OTP email |

---

## 🚀 Quick Start

### 1. Install Dependencies

```bash
npm run install:all
```

### 2. Configure Environment

```bash
cp server/.env.example server/.env
# Edit server/.env with your credentials
```

**Required env vars:**
```
MONGO_URI=mongodb://localhost:27017/quizmind
JWT_SECRET=your_secret_key
GROQ_API_KEY=your_groq_key        # Get free at console.groq.com
EMAIL_USER=your@gmail.com         # Gmail with App Password
EMAIL_PASS=your_app_password
REDIS_URL=redis://localhost:6379  # Optional: for scaling
```

### 3. Start Development

```bash
npm run dev
```

- Frontend: http://localhost:5173
- Backend: http://localhost:5000

---

## 📁 Project Structure

```
quizmind/
├── client/                    # React frontend
│   └── src/
│       ├── pages/             # All page components
│       ├── dashboards/        # Role-specific dashboards
│       ├── components/        # Shared components
│       ├── context/           # Auth context
│       ├── utils/             # API + Socket utilities
│       └── styles/            # Global CSS design system
├── server/                    # Node.js backend
│   ├── models/                # MongoDB schemas
│   ├── routes/                # API routes
│   ├── middleware/            # Auth + upload
│   ├── services/realtime/     # Socket.IO handlers
│   └── utils/                 # Email utility
└── package.json               # Root scripts
```

---

## 👥 Role System

| Role | Access | Ref Code |
|------|--------|---------|
| Student | Quiz, AI Quiz, Battle, Chatbot, History | Joins via teacher/school/parent code |
| Teacher | All + Student management | `TCH-XXXXX` |
| Institution | All + School vs School | `SCH-XXXXX` |
| Parent | View child performance only | `PAR-XXXXX` |

---

## ⚔️ Battle Types

| Type | Players | Anti-cheat |
|------|---------|-----------|
| Solo | 1 | No |
| 1v1 | 2 | Yes |
| Group | 2–10 | Yes |
| Team vs Team | 2–20+ per team | Yes, per team |
| School vs School | 2–100+ per school | Yes, per school |

---

## 🛡️ Anti-Cheat System

- Tab switch → Warning + -5 pts for team (on 2nd offence)
- Window blur → Logged silently
- Name highlighted in red on leaderboard for all players
- Logs saved to battle room record

---

## 🤖 AI Providers

Switch provider via `.env`:
```
AI_PROVIDER=groq   # groq | openai | gemini
```

Current models used:
- Text: `llama-3.3-70b-versatile` (fast, free)
- Vision: `meta-llama/llama-4-scout-17b-16e-instruct`

---

## 📦 Production Build

```bash
cd client && npm run build
# Serve client/dist with nginx or express static
```

---

## 🔑 Getting a Groq API Key

1. Go to [console.groq.com](https://console.groq.com)
2. Sign up free
3. Create an API key
4. Add to `server/.env` as `GROQ_API_KEY`

---

## 📧 Gmail SMTP Setup

1. Enable 2FA on your Gmail account
2. Go to Google Account → Security → App Passwords
3. Generate an app password for "Mail"
4. Use that 16-char password as `EMAIL_PASS` in `.env`

---

*Built with ❤️ for students who want to win.*
