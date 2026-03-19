const express = require('express')
const User = require('../models/User')
const { protect } = require('../middleware/auth')
const router = express.Router()

// GET /api/leaderboard/global
router.get('/global', protect, async (req, res) => {
  try {
    const users = await User.find({ role: 'student' })
      .sort('-xp').limit(100)
      .select('name xp level streak class college')
      .lean()
    const myRank = users.findIndex(u => u._id.toString() === req.user._id.toString()) + 1
    res.json({ users, myRank })
  } catch (err) { res.status(500).json({ message: err.message }) }
})

// GET /api/leaderboard/school (within same institution)
router.get('/school', protect, async (req, res) => {
  try {
    const institutionId = req.user.role === 'institution' ? req.user._id : req.user.linkedInstitutionId
    if (!institutionId) return res.json({ users: [], myRank: 0 })
    const users = await User.find({ linkedInstitutionId: institutionId, role: 'student' })
      .sort('-xp').limit(50).select('name xp level streak class').lean()
    const myRank = users.findIndex(u => u._id.toString() === req.user._id.toString()) + 1
    res.json({ users, myRank })
  } catch (err) { res.status(500).json({ message: err.message }) }
})

module.exports = router
