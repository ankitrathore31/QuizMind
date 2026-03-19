const express = require('express')
const User = require('../models/User')
const Result = require('../models/Result')
const { protect, requireRole } = require('../middleware/auth')
const router = express.Router()

// GET /api/users/my-students  (teacher or institution)
router.get('/my-students', protect, requireRole('teacher','institution'), async (req, res) => {
  try {
    const field = req.user.role === 'teacher' ? 'linkedTeacherId' : 'linkedInstitutionId'
    const students = await User.find({ [field]: req.user._id, role: 'student' })
      .select('name email class xp streak weakSubjects createdAt')
      .lean()

    // Attach latest stats
    const enriched = await Promise.all(students.map(async s => {
      const results = await Result.find({ userId: s._id }).sort('-createdAt').limit(5).lean()
      const avg = results.length ? Math.round(results.reduce((a,r) => a + r.accuracy, 0) / results.length) : 0
      return { ...s, avgAccuracy: avg, recentResults: results.slice(0,3) }
    }))
    res.json(enriched)
  } catch (err) { res.status(500).json({ message: err.message }) }
})

// GET /api/users/my-children  (parent)
router.get('/my-children', protect, requireRole('parent'), async (req, res) => {
  try {
    const children = await User.find({ linkedParentId: req.user._id, role: 'student' })
      .select('name email class xp streak weakSubjects onboardingComplete')
      .lean()
    const enriched = await Promise.all(children.map(async c => {
      const results = await Result.find({ userId: c._id }).sort('-createdAt').limit(10).lean()
      const avg = results.length ? Math.round(results.reduce((a,r) => a + r.accuracy, 0) / results.length) : 0
      const subjectMap = {}
      results.forEach(r => {
        if (r.subject) {
          if (!subjectMap[r.subject]) subjectMap[r.subject] = { total: 0, count: 0 }
          subjectMap[r.subject].total += r.accuracy
          subjectMap[r.subject].count++
        }
      })
      const subjectStats = Object.entries(subjectMap).map(([s,v]) => ({ subject: s, avg: Math.round(v.total/v.count) }))
      return { ...c, avgAccuracy: avg, recentResults: results.slice(0,5), subjectStats }
    }))
    res.json(enriched)
  } catch (err) { res.status(500).json({ message: err.message }) }
})

// GET /api/users/student/:id  (teacher/institution/parent viewing student)
router.get('/student/:id', protect, async (req, res) => {
  try {
    const student = await User.findById(req.params.id).select('-password -otp').lean()
    if (!student) return res.status(404).json({ message: 'Student not found' })
    const results = await Result.find({ userId: req.params.id }).sort('-createdAt').limit(20).lean()
    res.json({ ...student, results })
  } catch (err) { res.status(500).json({ message: err.message }) }
})

// PUT /api/users/profile
router.put('/profile', protect, async (req, res) => {
  try {
    const { name, college, class: cls, age, interests } = req.body
    const updated = await User.findByIdAndUpdate(req.user._id,
      { name, college, class: cls, age, interests },
      { new: true, select: '-password -otp' }
    )
    res.json(updated)
  } catch (err) { res.status(500).json({ message: err.message }) }
})

// GET /api/users/history
router.get('/history', protect, async (req, res) => {
  try {
    const results = await Result.find({ userId: req.user._id }).sort('-createdAt').limit(50).lean()
    res.json(results)
  } catch (err) { res.status(500).json({ message: err.message }) }
})

module.exports = router
