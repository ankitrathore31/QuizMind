import { useEffect, useState } from 'react'
import { motion, AnimatePresence } from 'framer-motion'

export default function WelcomeAnimation({ user, onDone }) {
  const [show, setShow] = useState(true)

  useEffect(() => {
    const t = setTimeout(() => { setShow(false); setTimeout(onDone, 400) }, 2800)
    return () => clearTimeout(t)
  }, [])

  const roleEmoji = { student: '👨‍🎓', teacher: '👨‍🏫', institution: '🏛️', parent: '👨‍👩‍👧' }
  const msgs = {
    student: `You're on a ${user?.streak || 0}-day streak 🔥`,
    teacher: `${user?.college ? user.college + ' • ' : ''}Welcome back!`,
    institution: 'School dashboard ready 🏫',
    parent: 'Your children are waiting! 👀',
  }

  return (
    <AnimatePresence>
      {show && (
        <motion.div className="welcome-overlay"
          initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
          onClick={() => { setShow(false); setTimeout(onDone, 200) }}
        >
          {/* Floating particles */}
          {[...Array(12)].map((_, i) => (
            <motion.div key={i}
              style={{ position:'absolute', width: Math.random()*6+3, height: Math.random()*6+3, borderRadius:'50%', background:`rgba(${Math.random()>0.5?'124,92,252':'0,212,255'},0.6)` }}
              initial={{ x: Math.random()*window.innerWidth, y: Math.random()*window.innerHeight, opacity: 0 }}
              animate={{ y: [null, Math.random()*-200-100], opacity: [0, 1, 0], scale: [0, 1, 0] }}
              transition={{ duration: 2+Math.random()*1, delay: Math.random()*0.5, ease:'easeOut' }}
            />
          ))}

          <motion.div className="welcome-card"
            initial={{ scale: 0.8, y: 30, opacity: 0 }}
            animate={{ scale: 1, y: 0, opacity: 1 }}
            transition={{ type: 'spring', stiffness: 260, damping: 20, delay: 0.1 }}
          >
            <motion.div style={{ fontSize: '3.5rem', marginBottom: 16 }}
              animate={{ rotate: [0, -15, 15, -10, 10, 0], scale: [1, 1.2, 1] }}
              transition={{ duration: 0.8, delay: 0.3 }}
            >
              {roleEmoji[user?.role] || '🚀'}
            </motion.div>

            <motion.h1 style={{ fontFamily:'var(--fh)', fontSize:'1.8rem', fontWeight:800, marginBottom:8, lineHeight:1.2 }}
              initial={{ opacity:0, y:10 }} animate={{ opacity:1, y:0 }} transition={{ delay:0.4 }}
            >
              Welcome back,<br />
              <span className="text-grad">{user?.name?.split(' ')[0]}!</span>
            </motion.h1>

            <motion.p style={{ color:'var(--muted)', fontSize:'.9rem', marginBottom:24 }}
              initial={{ opacity:0 }} animate={{ opacity:1 }} transition={{ delay:0.6 }}
            >
              {msgs[user?.role]}
            </motion.p>

            {user?.role === 'student' && (
              <motion.div style={{ display:'flex', justifyContent:'center', gap:20, marginBottom:24 }}
                initial={{ opacity:0, y:10 }} animate={{ opacity:1, y:0 }} transition={{ delay:0.7 }}
              >
                <div style={{ textAlign:'center' }}>
                  <div style={{ fontFamily:'var(--fh)', fontSize:'1.4rem', fontWeight:800, background:'var(--gradient)', WebkitBackgroundClip:'text', WebkitTextFillColor:'transparent' }}>{(user.xp||0).toLocaleString()}</div>
                  <div style={{ fontSize:'.68rem', color:'var(--muted)' }}>XP</div>
                </div>
                <div style={{ textAlign:'center' }}>
                  <div style={{ fontFamily:'var(--fh)', fontSize:'1.4rem', fontWeight:800, color:'var(--gold)' }}>{user.streak || 0}🔥</div>
                  <div style={{ fontSize:'.68rem', color:'var(--muted)' }}>Streak</div>
                </div>
              </motion.div>
            )}

            <motion.div style={{ height:4, background:'rgba(255,255,255,.07)', borderRadius:2, overflow:'hidden' }}
              initial={{ opacity:0 }} animate={{ opacity:1 }} transition={{ delay:0.8 }}
            >
              <motion.div style={{ height:'100%', background:'var(--gradient)', borderRadius:2 }}
                initial={{ width:0 }} animate={{ width:'100%' }} transition={{ duration:2, delay:0.9, ease:'easeInOut' }}
              />
            </motion.div>
            <p style={{ color:'var(--muted)', fontSize:'.72rem', marginTop:8 }}>Click anywhere to continue</p>
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  )
}
