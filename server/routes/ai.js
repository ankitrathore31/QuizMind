const express = require('express')
const Groq = require('groq-sdk')
const pdfParse = require('pdf-parse')
const { protect } = require('../middleware/auth')
const { uploadPDF, uploadImg } = require('../middleware/upload')
const { Quiz } = require('../models/Quiz')
const Result = require('../models/Result')
const User = require('../models/User')
const router = express.Router()

const groq = new Groq({ apiKey: process.env.GROQ_API_KEY })
const MODEL = 'llama-3.3-70b-versatile'
const VISION_MODEL = 'meta-llama/llama-4-scout-17b-16e-instruct'

function buildPrompt(context, count, difficulty) {
  return `You are an expert educational quiz maker. Generate exactly ${count} MCQ questions.
Difficulty: ${difficulty}
Content: ${context}

Return ONLY a valid JSON array, no markdown, no explanation:
[{"question":"string","options":["A","B","C","D"],"answer":0,"explanation":"string","topic":"string"}]

Rules: answer = zero-based index 0-3, exactly 4 options, 1-2 sentence explanation, vary correct answer positions.`
}

function parseQ(text, source) {
  const cleaned = text.replace(/```json|```/g, '').trim()
  const arr = JSON.parse(cleaned)
  return (Array.isArray(arr) ? arr : arr.questions).map(q => ({ ...q, source }))
}

// POST /api/ai/generate-topic
router.post('/generate-topic', protect, async (req, res) => {
  try {
    const { topic, subject, count = 10, difficulty = 'intermediate' } = req.body
    if (!topic) return res.status(400).json({ message: 'Topic required' })
    const context = `Subject: ${subject || 'General'}\nTopic: ${topic}`
    const r = await groq.chat.completions.create({
      model: MODEL, temperature: 0.7,
      messages: [
        { role: 'system', content: 'You are an expert educational quiz generator. Respond with valid JSON only.' },
        { role: 'user', content: buildPrompt(context, count, difficulty) }
      ]
    })
    const questions = parseQ(r.choices[0].message.content, 'topic')
    const quiz = await Quiz.create({ type: 'ai', subject, topic, difficulty, questions, createdBy: req.user._id, source: 'topic' })
    res.json({ questions, quizId: quiz._id, count: questions.length })
  } catch (err) { res.status(500).json({ message: err.message || 'Generation failed' }) }
})

// POST /api/ai/generate-pdf
router.post('/generate-pdf', protect, uploadPDF.single('pdf'), async (req, res) => {
  try {
    if (!req.file) return res.status(400).json({ message: 'PDF required' })
    const { count = 10, difficulty = 'intermediate' } = req.body
    const data = await pdfParse(req.file.buffer)
    if (!data.text?.trim() || data.text.trim().length < 50)
      return res.status(400).json({ message: 'PDF appears empty or unreadable' })
    const context = data.text.slice(0, 4000)
    const r = await groq.chat.completions.create({
      model: MODEL, temperature: 0.6,
      messages: [
        { role: 'system', content: 'Expert educational quiz generator. Valid JSON only.' },
        { role: 'user', content: buildPrompt(context, count, difficulty) }
      ]
    })
    const questions = parseQ(r.choices[0].message.content, 'pdf')
    const quiz = await Quiz.create({ type: 'ai', difficulty, questions, createdBy: req.user._id, source: 'pdf' })
    res.json({ questions, quizId: quiz._id, count: questions.length })
  } catch (err) { res.status(500).json({ message: err.message || 'PDF processing failed' }) }
})

// POST /api/ai/generate-image
router.post('/generate-image', protect, uploadImg.single('image'), async (req, res) => {
  try {
    if (!req.file) return res.status(400).json({ message: 'Image required' })
    const { count = 5 } = req.body
    const base64 = req.file.buffer.toString('base64')
    const mime = req.file.mimetype
    const prompt = `This is an educational diagram. Identify what it shows and generate exactly ${count} MCQ questions.
Return ONLY valid JSON: {"detectedLabel":"string","questions":[{"question":"string","options":["A","B","C","D"],"answer":0,"explanation":"string","topic":"string"}]}
If NOT educational, set detectedLabel to "non_educational" and return empty questions array.`
    const r = await groq.chat.completions.create({
      model: VISION_MODEL, temperature: 0.7,
      messages: [{ role: 'user', content: [
        { type: 'text', text: prompt },
        { type: 'image_url', image_url: { url: `data:${mime};base64,${base64}` } }
      ]}]
    })
    const parsed = JSON.parse(r.choices[0].message.content.replace(/```json|```/g, '').trim())
    if (parsed.detectedLabel === 'non_educational')
      return res.status(400).json({ message: 'Not an educational diagram. Please upload biology, physics, chemistry, or geography diagrams.' })
    const questions = (parsed.questions || []).map(q => ({ ...q, source: 'image' }))
    const quiz = await Quiz.create({ type: 'ai', difficulty: 'intermediate', questions, createdBy: req.user._id, source: 'image' })
    res.json({ questions, quizId: quiz._id, count: questions.length, detectedLabel: parsed.detectedLabel })
  } catch (err) { res.status(500).json({ message: err.message || 'Image processing failed' }) }
})

// POST /api/ai/submit-solo
router.post('/submit-solo', protect, async (req, res) => {
  try {
    const { score, totalQ, accuracy, xpEarned, difficulty, subject, topic, quizId } = req.body
    await Result.create({
      userId: req.user._id, quizId, type: 'solo',
      score, totalQ, accuracy, xpEarned,
      subject, topic, difficulty,
      teacherId: req.user.linkedTeacherId,
      institutionId: req.user.linkedInstitutionId,
    })
    // Update XP
    await User.findByIdAndUpdate(req.user._id, { $inc: { xp: xpEarned } })
    // Update weak subjects
    if (accuracy < 60 && subject) {
      await User.findByIdAndUpdate(req.user._id, { $addToSet: { weakSubjects: subject } })
    } else if (accuracy >= 80 && subject) {
      await User.findByIdAndUpdate(req.user._id, { $pull: { weakSubjects: subject } })
    }
    res.json({ message: 'Result saved' })
  } catch (err) { res.status(500).json({ message: err.message }) }
})

// POST /api/ai/tutor-chat
router.post('/tutor-chat', protect, async (req, res) => {
  try {
    const { message, subject, history = [] } = req.body
    if (!message?.trim()) return res.status(400).json({ message: 'Message required' })

    // Safety check
    const check = await groq.chat.completions.create({
      model: MODEL, temperature: 0,
      messages: [
        { role: 'system', content: 'Classify as "allowed" (study/academic) or "blocked" (anything else). Reply ONLY: allowed OR blocked' },
        { role: 'user', content: message }
      ]
    })
    if (check.choices[0].message.content.trim().toLowerCase() === 'blocked')
      return res.json({ response: "⚠️ I'm your **Study Tutor** — I only help with academic topics like Math, Science, History, and more. Please ask a study-related question! 📚" })

    const systemPrompt = `You are an expert AI Study Tutor for students (Class 6 to College).
Subject focus: ${subject || 'General'}
Be friendly, encouraging, use simple language, examples, emojis occasionally.
For math/science: show step-by-step solutions. For theory: use bullet points.
ONLY answer academic questions. Max 300 words. Format nicely with line breaks.`

    const recentHistory = history.slice(-6).map(h => ({ role: h.role === 'user' ? 'user' : 'assistant', content: h.text }))
    const r = await groq.chat.completions.create({
      model: MODEL, temperature: 0.7, max_tokens: 600,
      messages: [{ role: 'system', content: systemPrompt }, ...recentHistory, { role: 'user', content: message }]
    })
    res.json({ response: r.choices[0].message.content })
  } catch (err) { res.status(500).json({ message: err.message }) }
})

// POST /api/ai/generate-standard (class+subject based quiz)
router.post('/generate-standard', protect, async (req, res) => {
  try {
    const { subject, class: cls, difficulty = 'intermediate', count = 10 } = req.body
    if (!subject || !cls) return res.status(400).json({ message: 'Subject and class required' })
    const context = `Indian school curriculum - Class ${cls} ${subject}. Generate curriculum-appropriate questions.`
    const r = await groq.chat.completions.create({
      model: MODEL, temperature: 0.7,
      messages: [
        { role: 'system', content: 'Expert Indian school curriculum quiz generator. Valid JSON only.' },
        { role: 'user', content: buildPrompt(context, count, difficulty) }
      ]
    })
    const questions = parseQ(r.choices[0].message.content, 'standard')
    const quiz = await Quiz.create({
      type: 'standard', subject, class: cls, difficulty,
      questions, createdBy: req.user._id, source: 'standard'
    })
    res.json({ questions, quizId: quiz._id, count: questions.length })
  } catch (err) { res.status(500).json({ message: err.message || 'Generation failed' }) }
})

// GET /api/ai/suggestions (AI performance suggestions)
router.get('/suggestions', protect, async (req, res) => {
  try {
    const results = await Result.find({ userId: req.user._id }).sort('-createdAt').limit(20).lean()
    if (!results.length) return res.json({ suggestion: 'Start your first quiz to get personalized suggestions! 🚀' })
    const subjectMap = {}
    results.forEach(r => {
      if (r.subject) {
        if (!subjectMap[r.subject]) subjectMap[r.subject] = []
        subjectMap[r.subject].push(r.accuracy)
      }
    })
    const weakList = Object.entries(subjectMap)
      .map(([s,arr]) => ({ subject: s, avg: Math.round(arr.reduce((a,b)=>a+b,0)/arr.length) }))
      .filter(x => x.avg < 70)
      .map(x => `${x.subject} (${x.avg}%)`)

    const prompt = `Student performance data: ${JSON.stringify(weakList.length ? weakList : ['All subjects performing well'])}
Provide a short, encouraging, personalized study suggestion (2-3 sentences, friendly tone, with emoji). Be specific about what to study.`

    const r = await groq.chat.completions.create({
      model: MODEL, temperature: 0.8, max_tokens: 150,
      messages: [{ role: 'user', content: prompt }]
    })
    res.json({ suggestion: r.choices[0].message.content, weakSubjects: weakList })
  } catch (err) { res.status(500).json({ message: err.message }) }
})

module.exports = router
