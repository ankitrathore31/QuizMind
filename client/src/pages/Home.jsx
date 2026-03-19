import { useEffect, useRef } from 'react'
import { Link } from 'react-router-dom'
import * as THREE from 'three'

function ThreeBackground() {
  const mountRef = useRef(null)
  useEffect(() => {
    const el = mountRef.current
    if (!el) return
    const W = el.clientWidth, H = el.clientHeight
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true })
    renderer.setSize(W, H); renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
    el.appendChild(renderer.domElement)
    const scene = new THREE.Scene()
    const camera = new THREE.PerspectiveCamera(60, W / H, 0.1, 100)
    camera.position.z = 5

    // Particles
    const geo = new THREE.BufferGeometry()
    const count = 600
    const pos = new Float32Array(count * 3)
    for (let i = 0; i < count * 3; i++) pos[i] = (Math.random() - 0.5) * 20
    geo.setAttribute('position', new THREE.BufferAttribute(pos, 3))
    const mat = new THREE.PointsMaterial({ color: 0x7C5CFC, size: 0.04, transparent: true, opacity: 0.7 })
    const particles = new THREE.Points(geo, mat)
    scene.add(particles)

    // Floating torus
    const tGeo = new THREE.TorusGeometry(1.2, 0.25, 16, 60)
    const tMat = new THREE.MeshBasicMaterial({ color: 0x7C5CFC, wireframe: true, transparent: true, opacity: 0.15 })
    const torus = new THREE.Mesh(tGeo, tMat)
    torus.position.x = 3; torus.position.y = -0.5
    scene.add(torus)

    const tGeo2 = new THREE.TorusGeometry(0.8, 0.15, 16, 40)
    const tMat2 = new THREE.MeshBasicMaterial({ color: 0x00D4FF, wireframe: true, transparent: true, opacity: 0.12 })
    const torus2 = new THREE.Mesh(tGeo2, tMat2)
    torus2.position.x = -3.5; torus2.position.y = 1
    scene.add(torus2)

    let frame
    const animate = () => {
      frame = requestAnimationFrame(animate)
      const t = Date.now() * 0.0005
      particles.rotation.y = t * 0.1
      particles.rotation.x = t * 0.05
      torus.rotation.x = t; torus.rotation.y = t * 0.7
      torus2.rotation.x = -t * 0.8; torus2.rotation.z = t * 0.5
      renderer.render(scene, camera)
    }
    animate()

    const handleResize = () => {
      const nW = el.clientWidth, nH = el.clientHeight
      camera.aspect = nW / nH; camera.updateProjectionMatrix()
      renderer.setSize(nW, nH)
    }
    window.addEventListener('resize', handleResize)
    return () => {
      cancelAnimationFrame(frame)
      window.removeEventListener('resize', handleResize)
      el.removeChild(renderer.domElement)
      renderer.dispose()
    }
  }, [])
  return <div ref={mountRef} style={{ position:'absolute', inset:0, zIndex:0 }} />
}

export default function Home() {
  return (
    <div style={{ background:'var(--dark)', minHeight:'100vh', overflowX:'hidden' }}>
      {/* Hero */}
      <section style={{ position:'relative', minHeight:'100vh', display:'flex', alignItems:'center', justifyContent:'center', overflow:'hidden' }}>
        <ThreeBackground />
        <div style={{ position:'relative', zIndex:1, textAlign:'center', padding:'80px 24px 60px', maxWidth:700, margin:'0 auto' }}>
          <div className="anim-fade" style={{ animationDelay:'.1s' }}>
            <div style={{ display:'inline-flex', alignItems:'center', gap:8, background:'rgba(124,92,252,.12)', border:'1px solid rgba(124,92,252,.25)', borderRadius:100, padding:'6px 18px', marginBottom:28, fontSize:'.75rem', color:'var(--violet)', fontFamily:'var(--fh)', fontWeight:700, letterSpacing:'.5px' }}>
              ✨ AI-POWERED QUIZ BATTLE PLATFORM
            </div>
            <h1 style={{ fontFamily:'var(--fh)', fontSize:'clamp(2.2rem,6vw,3.8rem)', fontWeight:800, lineHeight:1.12, marginBottom:20 }}>
              Master Every Subject<br /><span className="text-grad">with AI Battle Quizzes</span>
            </h1>
            <p style={{ color:'var(--muted)', fontSize:'clamp(.9rem,2vw,1.1rem)', lineHeight:1.8, marginBottom:36, maxWidth:520, margin:'0 auto 36px' }}>
              Generate MCQs from any topic, PDF, or image. Battle friends live. AI tracks your weak spots and guides improvement.
            </p>
            <div style={{ display:'flex', gap:14, justifyContent:'center', flexWrap:'wrap' }}>
              <Link to="/auth" className="btn btn-grad btn-lg">Get Started Free →</Link>
              <Link to="/auth" className="btn btn-ghost btn-lg">Login</Link>
            </div>
          </div>
          <div className="anim-fade" style={{ animationDelay:'.5s', display:'flex', justifyContent:'center', gap:36, marginTop:56, flexWrap:'wrap' }}>
            {[['50K+','Students'],['2M+','Quizzes Played'],['500+','Schools'],['98%','Satisfaction']].map(([v,l]) => (
              <div key={l} style={{ textAlign:'center' }}>
                <div style={{ fontFamily:'var(--fh)', fontSize:'1.6rem', fontWeight:800, background:'var(--gradient)', WebkitBackgroundClip:'text', WebkitTextFillColor:'transparent' }}>{v}</div>
                <div style={{ fontSize:'.72rem', color:'var(--muted)' }}>{l}</div>
              </div>
            ))}
          </div>
        </div>
        {/* Scroll indicator */}
        <div style={{ position:'absolute', bottom:32, left:'50%', transform:'translateX(-50%)', display:'flex', flexDirection:'column', alignItems:'center', gap:6, color:'var(--muted)', fontSize:'.72rem' }}>
          <div style={{ width:1, height:40, background:'linear-gradient(to bottom, transparent, var(--muted))', animation:'pulse 2s infinite' }} />
          scroll
        </div>
      </section>

      {/* Features */}
      <section id="features" style={{ padding:'80px 24px', maxWidth:1100, margin:'0 auto' }}>
        <div style={{ textAlign:'center', marginBottom:52 }}>
          <div style={{ fontFamily:'var(--fh)', fontSize:'.72rem', letterSpacing:'2px', color:'var(--violet)', marginBottom:12, fontWeight:700 }}>FEATURES</div>
          <h2 style={{ fontFamily:'var(--fh)', fontSize:'2rem', fontWeight:800 }}>Everything you need to excel</h2>
        </div>
        <div className="grid-3">
          {[
            { icon:'🤖', title:'AI Quiz Generator', desc:'Generate MCQs instantly from any topic, PDF document, or educational image using Groq AI.' },
            { icon:'⚔️', title:'Live Battle System', desc:'1v1, Team vs Team, and epic School vs School battles with real-time leaderboards.' },
            { icon:'📊', title:'Smart Analytics', desc:'AI detects weak subjects and creates personalized practice schedules automatically.' },
            { icon:'🛡️', title:'Anti-Cheat Engine', desc:'Tab switch detection, window blur tracking, and real-time penalty system.' },
            { icon:'👨‍👩‍👧', title:'Multi-Role Platform', desc:'Dedicated dashboards for students, parents, teachers, and institutions.' },
            { icon:'🏆', title:'Gamification', desc:'XP system, streaks, trophies, badges, and global leaderboards to keep you motivated.' },
          ].map((f, i) => (
            <div key={i} className="card" style={{ transition:'all .3s', cursor:'default', animationDelay:`${i*.08}s` }}
              onMouseEnter={e => { e.currentTarget.style.borderColor='rgba(124,92,252,.3)'; e.currentTarget.style.transform='translateY(-4px)'; }}
              onMouseLeave={e => { e.currentTarget.style.borderColor='var(--border)'; e.currentTarget.style.transform='translateY(0)'; }}
            >
              <div style={{ fontSize:'2rem', marginBottom:14 }}>{f.icon}</div>
              <h3 style={{ fontFamily:'var(--fh)', fontSize:'1rem', fontWeight:700, marginBottom:8 }}>{f.title}</h3>
              <p style={{ color:'var(--muted)', fontSize:'.85rem', lineHeight:1.7 }}>{f.desc}</p>
            </div>
          ))}
        </div>
      </section>

      {/* PDF Demo */}
      <section style={{ padding:'60px 24px', maxWidth:800, margin:'0 auto', textAlign:'center' }}>
        <h2 style={{ fontFamily:'var(--fh)', fontSize:'1.8rem', fontWeight:800, marginBottom:12 }}>Drop a PDF. Get MCQs instantly.</h2>
        <p style={{ color:'var(--muted)', marginBottom:32, fontSize:'.9rem' }}>Upload any NCERT, textbook, or study notes PDF and our AI generates quiz questions in seconds.</p>
        <div className="drop-zone" style={{ maxWidth:500, margin:'0 auto', cursor:'default' }}>
          <div style={{ fontSize:'3rem', marginBottom:12 }}>📄</div>
          <div style={{ fontFamily:'var(--fh)', fontWeight:700, marginBottom:6 }}>NCERT_Physics_Chapter5.pdf</div>
          <div style={{ height:3, background:'rgba(255,255,255,.07)', borderRadius:2, margin:'16px 0', overflow:'hidden' }}>
            <div style={{ height:'100%', width:'75%', background:'var(--gradient)', borderRadius:2 }} />
          </div>
          <div style={{ color:'var(--green)', fontSize:'.82rem', fontWeight:600 }}>✅ 10 questions generated from 47 pages!</div>
        </div>
      </section>

      {/* How it works */}
      <section id="how" style={{ padding:'60px 24px 100px', maxWidth:900, margin:'0 auto' }}>
        <div style={{ textAlign:'center', marginBottom:48 }}>
          <h2 style={{ fontFamily:'var(--fh)', fontSize:'1.8rem', fontWeight:800 }}>How It Works</h2>
        </div>
        <div className="grid-3">
          {[
            { n:'1', title:'Sign Up & Choose Role', desc:'Register as student, teacher, institution, or parent. Link to your school using a reference code.' },
            { n:'2', title:'Generate or Pick a Quiz', desc:'Choose from standard class-based quizzes or generate AI-powered MCQs from topic, PDF, or image.' },
            { n:'3', title:'Battle & Improve', desc:'Play solo, invite friends, host team battles. AI tracks performance and suggests improvements.' },
          ].map((s, i) => (
            <div key={i} style={{ textAlign:'center' }}>
              <div style={{ width:52, height:52, borderRadius:'50%', background:'var(--gradient)', display:'flex', alignItems:'center', justifyContent:'center', fontFamily:'var(--fh)', fontWeight:800, fontSize:'1.2rem', margin:'0 auto 16px' }}>{s.n}</div>
              <h3 style={{ fontFamily:'var(--fh)', fontSize:'.95rem', fontWeight:700, marginBottom:8 }}>{s.title}</h3>
              <p style={{ color:'var(--muted)', fontSize:'.83rem', lineHeight:1.7 }}>{s.desc}</p>
            </div>
          ))}
        </div>
        <div style={{ textAlign:'center', marginTop:56 }}>
          <Link to="/auth" className="btn btn-grad btn-lg">Start for Free →</Link>
        </div>
      </section>

      <footer style={{ borderTop:'1px solid var(--border)', padding:'24px', textAlign:'center', color:'var(--muted)', fontSize:'.78rem' }}>
        © 2025 QuizMind AI — Built for students who want to win.
      </footer>
    </div>
  )
}
