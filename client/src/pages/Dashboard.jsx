import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import WelcomeAnimation from '../components/WelcomeAnimation'
import StudentDashboard from '../dashboards/StudentDashboard'
import TeacherDashboard from '../dashboards/TeacherDashboard'
import InstitutionDashboard from '../dashboards/InstitutionDashboard'
import ParentDashboard from '../dashboards/ParentDashboard'

export default function Dashboard() {
  const { user } = useAuth()
  const navigate = useNavigate()
  const [showWelcome, setShowWelcome] = useState(false)

  useEffect(() => {
    const key = `qm_welcomed_${user?._id}`
    if (!sessionStorage.getItem(key)) {
      setShowWelcome(true)
      sessionStorage.setItem(key, '1')
    }
    if (user?.role === 'student' && !user?.onboardingComplete) {
      navigate('/onboarding')
    }
  }, [user])

  const map = { student: StudentDashboard, teacher: TeacherDashboard, institution: InstitutionDashboard, parent: ParentDashboard }
  const Component = map[user?.role] || StudentDashboard

  return (
    <>
      {showWelcome && <WelcomeAnimation user={user} onDone={() => setShowWelcome(false)} />}
      <Component />
    </>
  )
}
