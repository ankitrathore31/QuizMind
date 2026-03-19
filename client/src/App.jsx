import { Routes, Route, Navigate } from 'react-router-dom'
import { AuthProvider, useAuth } from './context/AuthContext'
import Navbar from './components/Navbar'
import Home from './pages/Home'
import Auth from './pages/Auth'
import VerifyOTP from './pages/VerifyOTP'
import Onboarding from './pages/Onboarding'
import Dashboard from './pages/Dashboard'
import QuizPage from './pages/QuizPage'
import AIQuizPage from './pages/AIQuizPage'
import BattleRoomPage from './pages/BattleRoomPage'
import BattleMenu from './pages/BattleMenu'
import LeaderboardPage from './pages/LeaderboardPage'
import HistoryPage from './pages/HistoryPage'
import ProfilePage from './pages/ProfilePage'
import ChatbotPage from './pages/ChatbotPage'
import MyStudentsPage from './pages/MyStudentsPage'
import SchoolBattlePage from './pages/SchoolBattlePage'

function PrivateRoute({ children }) {
  const { user, loading } = useAuth()
  if (loading) return <div style={{ display:'flex',alignItems:'center',justifyContent:'center',minHeight:'100vh',background:'#08080F' }}><span className="spinner" /></div>
  return user ? children : <Navigate to="/auth" replace />
}

function PublicOnlyRoute({ children }) {
  const { user, loading } = useAuth()
  if (loading) return null
  return !user ? children : <Navigate to="/dashboard" replace />
}

function AppInner() {
  const { user } = useAuth()
  return (
    <>
      {user && <Navbar />}
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/auth" element={<PublicOnlyRoute><Auth /></PublicOnlyRoute>} />
        <Route path="/verify-otp" element={<VerifyOTP />} />
        <Route path="/onboarding" element={<PrivateRoute><Onboarding /></PrivateRoute>} />
        <Route path="/dashboard" element={<PrivateRoute><Dashboard /></PrivateRoute>} />
        <Route path="/quiz" element={<PrivateRoute><QuizPage /></PrivateRoute>} />
        <Route path="/ai-quiz" element={<PrivateRoute><AIQuizPage /></PrivateRoute>} />
        <Route path="/battle" element={<PrivateRoute><BattleMenu /></PrivateRoute>} />
        <Route path="/battle/:code" element={<PrivateRoute><BattleRoomPage /></PrivateRoute>} />
        <Route path="/leaderboard" element={<PrivateRoute><LeaderboardPage /></PrivateRoute>} />
        <Route path="/history" element={<PrivateRoute><HistoryPage /></PrivateRoute>} />
        <Route path="/profile" element={<PrivateRoute><ProfilePage /></PrivateRoute>} />
        <Route path="/ai-tutor" element={<PrivateRoute><ChatbotPage /></PrivateRoute>} />
        <Route path="/my-students" element={<PrivateRoute><MyStudentsPage /></PrivateRoute>} />
        <Route path="/school-battle" element={<PrivateRoute><SchoolBattlePage /></PrivateRoute>} />
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </>
  )
}

export default function App() {
  return <AuthProvider><AppInner /></AuthProvider>
}
