const express = require('express')
const { v4: uuidv4 } = require('uuid')
const BattleRoom = require('../models/BattleRoom')
const { Quiz } = require('../models/Quiz')
const Result = require('../models/Result')
const { protect, requireRole } = require('../middleware/auth')
const router = express.Router()

function genCode(prefix = '') {
  return (prefix + uuidv4().slice(0,6).toUpperCase()).slice(0,10)
}

// POST /api/battle/create
router.post('/create', protect, async (req, res) => {
  try {
    const { type, quizId, subject, difficulty, totalQ = 10, teamAName, teamBName, maxPlayers, maxStudentsPerSchool } = req.body
    const quiz = quizId ? await Quiz.findById(quizId) : null

    const roomData = {
      code: genCode(),
      type, hostId: req.user._id,
      subject: subject || quiz?.subject,
      difficulty: difficulty || quiz?.difficulty || 'intermediate',
      totalQuestions: totalQ,
      maxPlayers: maxPlayers || 10,
      questions: quiz?.questions || [],
    }

    if (type === 'team' || type === 'school') {
      const codeA = genCode('A-'), codeB = genCode('B-')
      roomData.teams = [
        { id: 'A', name: teamAName || req.user.college || 'Team A', inviteCode: codeA, score: 0 },
        { id: 'B', name: teamBName || 'Team B', inviteCode: codeB, score: 0 },
      ]
      if (type === 'school') {
        roomData.schoolAId = req.user._id
        roomData.schoolAName = req.user.college || teamAName
        roomData.maxStudentsPerSchool = maxStudentsPerSchool || 40
      }
    }

    const room = await BattleRoom.create(roomData)
    res.json({ room })
  } catch (err) { res.status(500).json({ message: err.message }) }
})

// POST /api/battle/create-with-questions
router.post('/create-with-questions', protect, async (req, res) => {
  try {
    const { type, questions, subject, difficulty, totalQ, teamAName, teamBName, maxPlayers } = req.body
    const quiz = await Quiz.create({ type: 'ai', subject, difficulty, questions, createdBy: req.user._id })
    const roomData = {
      code: genCode(), type, hostId: req.user._id,
      quizId: quiz._id, questions, subject, difficulty,
      totalQuestions: totalQ || questions.length,
      maxPlayers: maxPlayers || 10,
    }
    if (type === 'team' || type === 'school') {
      roomData.teams = [
        { id: 'A', name: teamAName || req.user.college || 'Team A', inviteCode: genCode('A-'), score: 0 },
        { id: 'B', name: teamBName || 'Team B', inviteCode: genCode('B-'), score: 0 },
      ]
      if (type === 'school') { roomData.schoolAId = req.user._id; roomData.schoolAName = teamAName }
    }
    const room = await BattleRoom.create(roomData)
    res.json({ room })
  } catch (err) { res.status(500).json({ message: err.message }) }
})

// GET /api/battle/:code
router.get('/:code', protect, async (req, res) => {
  try {
    const room = await BattleRoom.findOne({ code: req.params.code })
      .populate('hostId', 'name college').lean()
    if (!room) return res.status(404).json({ message: 'Room not found' })
    res.json(room)
  } catch (err) { res.status(500).json({ message: err.message }) }
})

// POST /api/battle/:code/join
router.post('/:code/join', protect, async (req, res) => {
  try {
    const { teamId } = req.body
    const room = await BattleRoom.findOne({ code: req.params.code })
    if (!room) return res.status(404).json({ message: 'Room not found' })
    if (room.status !== 'waiting') return res.status(400).json({ message: 'Room already started' })
    const already = room.participants.find(p => p.userId?.toString() === req.user._id.toString())
    if (!already) {
      room.participants.push({ userId: req.user._id, name: req.user.name, teamId: teamId || null, score: 0 })
      await room.save()
    }
    res.json({ room })
  } catch (err) { res.status(500).json({ message: err.message }) }
})

// POST /api/battle/:code/join-school (join as opposing school)
router.post('/:code/join-school', protect, requireRole('institution'), async (req, res) => {
  try {
    const room = await BattleRoom.findOne({ code: req.params.code })
    if (!room || room.type !== 'school') return res.status(400).json({ message: 'Invalid school battle room' })
    if (room.schoolBId) return res.status(400).json({ message: 'Opposing school already joined' })
    room.schoolBId = req.user._id
    room.schoolBName = req.user.college || req.body.schoolName || 'School B'
    room.teams[1].name = room.schoolBName
    room.teams[1].schoolId = req.user._id
    await room.save()
    res.json({ room })
  } catch (err) { res.status(500).json({ message: err.message }) }
})

// GET /api/battle/history/me
router.get('/history/me', protect, async (req, res) => {
  try {
    const results = await Result.find({ userId: req.user._id, type: { $ne: 'solo' } })
      .sort('-createdAt').limit(20).populate('battleRoomId', 'code type').lean()
    res.json(results)
  } catch (err) { res.status(500).json({ message: err.message }) }
})

// GET /api/battle/school/history (institution only)
router.get('/school/history', protect, requireRole('institution'), async (req, res) => {
  try {
    const rooms = await BattleRoom.find({
      type: 'school',
      $or: [{ schoolAId: req.user._id }, { schoolBId: req.user._id }],
      status: 'finished'
    }).sort('-endedAt').limit(20).lean()
    res.json(rooms)
  } catch (err) { res.status(500).json({ message: err.message }) }
})

module.exports = router
