const BattleRoom = require('../../models/BattleRoom')
const Result = require('../../models/Result')
const User = require('../../models/User')

const rooms = new Map() // roomCode -> { timers, questionIndex, started }

function initSocket(io) {
  io.on('connection', (socket) => {
    console.log('Socket connected:', socket.id)

    // Join room
    socket.on('join_room', async ({ roomCode, userId, userName, teamId }) => {
      try {
        socket.join(roomCode)
        const room = await BattleRoom.findOne({ code: roomCode })
        if (!room) return socket.emit('error', { message: 'Room not found' })
        // Add participant if not already in
        const already = room.participants.find(p => p.userId?.toString() === userId)
        if (!already) {
          room.participants.push({ userId, name: userName, teamId, score: 0 })
          await room.save()
        }
        io.to(roomCode).emit('room_updated', { participants: room.participants, teams: room.teams, status: room.status })
        socket.emit('joined', { room })
      } catch (err) { socket.emit('error', { message: err.message }) }
    })

    // Host starts game
    socket.on('start_game', async ({ roomCode }) => {
      try {
        const room = await BattleRoom.findOne({ code: roomCode })
        if (!room) return
        room.status = 'live'; room.startedAt = new Date(); room.currentQuestion = 0
        await room.save()
        if (!rooms.has(roomCode)) rooms.set(roomCode, { currentQ: 0, answered: new Map() })
        io.to(roomCode).emit('game_started', {
          question: sanitizeQ(room.questions[0], 0),
          questionIndex: 0,
          totalQuestions: room.questions.length,
          timeLimit: 15,
        })
        startQuestionTimer(io, roomCode, room, 0)
      } catch (err) { socket.emit('error', { message: err.message }) }
    })

    // Player submits answer
    socket.on('submit_answer', async ({ roomCode, userId, questionIndex, answerIndex, timeTaken }) => {
      try {
        const room = await BattleRoom.findOne({ code: roomCode })
        if (!room || room.status !== 'live') return
        const q = room.questions[questionIndex]
        if (!q) return
        const correct = answerIndex === q.answer
        const baseScore = correct ? 100 : 0
        const timeBonus = correct ? Math.max(0, Math.round((15 - timeTaken) * 5)) : 0
        const points = baseScore + timeBonus

        const pIdx = room.participants.findIndex(p => p.userId?.toString() === userId)
        if (pIdx === -1) return
        room.participants[pIdx].score += points
        room.participants[pIdx].answers.push({ questionIndex, answer: answerIndex, correct, timeTaken })

        // Update team score
        if (room.teams?.length) {
          const teamId = room.participants[pIdx].teamId
          const tIdx = room.teams.findIndex(t => t.id === teamId)
          if (tIdx >= 0) room.teams[tIdx].score += points
        }
        await room.save()

        const leaderboard = buildLeaderboard(room)
        io.to(roomCode).emit('score_updated', { leaderboard, teamScores: room.teams })
        socket.emit('answer_result', { correct, points, explanation: q.explanation })
      } catch (err) { socket.emit('error', { message: err.message }) }
    })

    // Anti-cheat event
    socket.on('cheat_event', async ({ roomCode, userId, userName, type }) => {
      try {
        const room = await BattleRoom.findOne({ code: roomCode })
        if (!room) return
        const pIdx = room.participants.findIndex(p => p.userId?.toString() === userId)
        if (pIdx === -1) return
        room.participants[pIdx].cheatCount += 1
        const count = room.participants[pIdx].cheatCount
        let penalty = 0
        if (count >= 2) { penalty = 5; room.participants[pIdx].score = Math.max(0, room.participants[pIdx].score - penalty) }
        if (count >= 3) room.participants[pIdx].cheated = true

        // Deduct from team
        if (penalty > 0 && room.teams?.length) {
          const teamId = room.participants[pIdx].teamId
          const tIdx = room.teams.findIndex(t => t.id === teamId)
          if (tIdx >= 0) room.teams[tIdx].score = Math.max(0, room.teams[tIdx].score - penalty)
        }

        room.antiCheatLogs.push({ userId, userName, type, penalty })
        await room.save()
        io.to(roomCode).emit('cheat_detected', { userId, userName, penalty, count, teamScores: room.teams, leaderboard: buildLeaderboard(room) })
      } catch (err) {}
    })

    socket.on('leave_room', ({ roomCode }) => socket.leave(roomCode))
    socket.on('disconnect', () => {})
  })
}

function startQuestionTimer(io, roomCode, room, qIdx) {
  const duration = 15000
  setTimeout(async () => {
    try {
      const nextIdx = qIdx + 1
      const fresh = await BattleRoom.findOne({ code: roomCode })
      if (!fresh || fresh.status !== 'live') return
      if (nextIdx >= fresh.questions.length) {
        // Game over
        fresh.status = 'finished'; fresh.endedAt = new Date()
        const lb = buildLeaderboard(fresh)
        const winner = lb[0]
        fresh.winner = winner?.name
        if (fresh.teams?.length) {
          const winTeam = [...fresh.teams].sort((a,b) => b.score - a.score)[0]
          fresh.winnerTeam = winTeam?.name
        }
        await fresh.save()
        await saveResults(fresh)
        io.to(roomCode).emit('game_ended', { leaderboard: lb, teamScores: fresh.teams, winner: fresh.winnerTeam || fresh.winner })
      } else {
        fresh.currentQuestion = nextIdx; await fresh.save()
        io.to(roomCode).emit('next_question', {
          question: sanitizeQ(fresh.questions[nextIdx], nextIdx),
          questionIndex: nextIdx,
          totalQuestions: fresh.questions.length,
          timeLimit: 15,
          leaderboard: buildLeaderboard(fresh),
          teamScores: fresh.teams,
        })
        startQuestionTimer(io, roomCode, fresh, nextIdx)
      }
    } catch {}
  }, duration)
}

function sanitizeQ(q, idx) {
  return { question: q.question, options: q.options, topic: q.topic, index: idx }
}

function buildLeaderboard(room) {
  return [...room.participants]
    .sort((a,b) => b.score - a.score)
    .map((p, i) => ({ rank: i+1, userId: p.userId, name: p.name, score: p.score, teamId: p.teamId, cheated: p.cheated, cheatCount: p.cheatCount }))
}

async function saveResults(room) {
  try {
    const saves = room.participants.map(p => Result.create({
      userId: p.userId,
      battleRoomId: room._id,
      type: room.type,
      subject: room.subject,
      score: p.score,
      totalQ: room.questions.length,
      accuracy: Math.round((p.answers.filter(a=>a.correct).length / room.questions.length) * 100),
      xpEarned: Math.round(p.score / 5),
    }))
    await Promise.all(saves)
    // Grant XP
    const xpGrants = room.participants.map(p => User.findByIdAndUpdate(p.userId, { $inc: { xp: Math.round(p.score / 5) } }))
    await Promise.all(xpGrants)
  } catch {}
}

module.exports = { initSocket }
