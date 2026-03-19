import { useState, useRef, useEffect } from 'react'
import api from '../utils/api'
import toast from 'react-hot-toast'

const SUBJECTS = ['General','Math','Physics','Chemistry','Biology','History','English','Computer Science']

export default function ChatbotPage() {
  const [messages,  setMessages]  = useState([{ role:'ai', text:"Hi! I'm your AI Study Tutor 📚 Ask me anything about your studies — Math, Science, History, and more!" }])
  const [input,     setInput]     = useState('')
  const [subject,   setSubject]   = useState('General')
  const [loading,   setLoading]   = useState(false)
  const bottomRef = useRef(null)
  const inputRef  = useRef(null)

  useEffect(() => { bottomRef.current?.scrollIntoView({ behavior:'smooth' }) }, [messages])

  const send = async e => {
    e?.preventDefault()
    if (!input.trim() || loading) return
    const msg = input.trim()
    setInput('')
    setMessages(prev => [...prev, { role:'user', text:msg }])
    setLoading(true)
    try {
      const history = messages.slice(-6)
      const res = await api.post('/ai/tutor-chat', { message:msg, subject, history })
      setMessages(prev => [...prev, { role:'ai', text:res.data.response }])
    } catch(err) {
      toast.error('Tutor unavailable')
      setMessages(prev => [...prev, { role:'ai', text:"Sorry, I couldn't connect right now. Please try again! 🔄" }])
    }
    finally { setLoading(false); setTimeout(()=>inputRef.current?.focus(),100) }
  }

  const formatText = text => {
    return text
      .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
      .replace(/\*(.*?)\*/g, '<em>$1</em>')
      .replace(/`(.*?)`/g, '<code style="background:rgba(124,92,252,.15);color:var(--violet);padding:1px 6px;border-radius:4px;font-size:.85em">$1</code>')
      .replace(/\n/g, '<br/>')
  }

  return (
    <div style={{ background:'var(--dark)', minHeight:'100vh', display:'flex', flexDirection:'column' }}>
      <div className="orb o1"/><div className="orb o2"/>
      <div className="page" style={{ flex:1, display:'flex', flexDirection:'column', maxWidth:800, paddingBottom:0 }}>
        {/* Header */}
        <div style={{ marginBottom:20 }}>
          <h1 style={{ fontFamily:'var(--fh)', fontSize:'1.6rem', fontWeight:800, marginBottom:4 }}>💬 AI Study Tutor</h1>
          <p className="text-muted" style={{ fontSize:'.83rem' }}>Ask anything academic — I'll explain clearly with examples</p>
        </div>

        {/* Subject selector */}
        <div style={{ display:'flex', gap:6, flexWrap:'wrap', marginBottom:16 }}>
          {SUBJECTS.map(s => (
            <button key={s} onClick={()=>setSubject(s)} className={`subject-pill${subject===s?' active':''}`} style={{ fontSize:'.72rem' }}>{s}</button>
          ))}
        </div>

        {/* Messages */}
        <div style={{ flex:1, overflowY:'auto', display:'flex', flexDirection:'column', gap:14, padding:'4px 0 16px', minHeight:300 }}>
          {messages.map((m, i) => (
            <div key={i} style={{ display:'flex', gap:10, alignItems:'flex-start', flexDirection:m.role==='user'?'row-reverse':'row' }}>
              {/* Avatar */}
              <div style={{ width:34, height:34, borderRadius:'50%', flexShrink:0, display:'flex', alignItems:'center', justifyContent:'center', fontSize:'1rem', background:m.role==='user'?'var(--gradient)':'rgba(0,212,255,.15)', border:m.role==='ai'?'1px solid rgba(0,212,255,.25)':undefined }}>
                {m.role==='user'?'👤':'🤖'}
              </div>
              {/* Bubble */}
              <div style={{ maxWidth:'80%', padding:'12px 16px', borderRadius:m.role==='user'?'16px 4px 16px 16px':'4px 16px 16px 16px', background:m.role==='user'?'var(--gradient)':'var(--card)', border:m.role==='ai'?'1px solid var(--border)':undefined, fontSize:'.88rem', lineHeight:1.7 }}
                dangerouslySetInnerHTML={{ __html:formatText(m.text) }}
              />
            </div>
          ))}
          {loading && (
            <div style={{ display:'flex', gap:10, alignItems:'flex-start' }}>
              <div style={{ width:34, height:34, borderRadius:'50%', display:'flex', alignItems:'center', justifyContent:'center', background:'rgba(0,212,255,.15)', border:'1px solid rgba(0,212,255,.25)', flexShrink:0 }}>🤖</div>
              <div style={{ padding:'14px 18px', background:'var(--card)', border:'1px solid var(--border)', borderRadius:'4px 16px 16px 16px', display:'flex', gap:5, alignItems:'center' }}>
                {[0,1,2].map(j => <div key={j} style={{ width:7, height:7, borderRadius:'50%', background:'var(--muted)', animation:`pulse 1.2s ${j*.2}s infinite` }}/>)}
              </div>
            </div>
          )}
          <div ref={bottomRef}/>
        </div>

        {/* Input */}
        <div style={{ position:'sticky', bottom:0, background:'rgba(8,8,15,.9)', backdropFilter:'blur(12px)', paddingTop:12, paddingBottom:20, borderTop:'1px solid var(--border)' }}>
          <form onSubmit={send} style={{ display:'flex', gap:10 }}>
            <input ref={inputRef} type="text" placeholder="Ask anything about your studies... (e.g. 'Explain photosynthesis')"
              value={input} onChange={e=>setInput(e.target.value)}
              disabled={loading}
              style={{ flex:1, padding:'13px 18px', background:'rgba(255,255,255,.05)', border:'1px solid var(--border)', borderRadius:12, color:'var(--text)', fontFamily:'var(--fb)', fontSize:'.9rem', outline:'none', transition:'border-color .2s' }}
              onFocus={e=>e.target.style.borderColor='rgba(124,92,252,.5)'}
              onBlur={e=>e.target.style.borderColor='var(--border)'}
            />
            <button type="submit" className="btn btn-grad" disabled={loading||!input.trim()} style={{ padding:'13px 20px', flexShrink:0 }}>
              {loading ? <span className="spinner"/> : '↑'}
            </button>
          </form>
          <div style={{ fontSize:'.7rem', color:'var(--muted)', marginTop:8, textAlign:'center' }}>
            Study topics only • Math, Science, History, English and more
          </div>
        </div>
      </div>
    </div>
  )
}
