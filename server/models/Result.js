const mongoose = require('mongoose')

const resultSchema = new mongoose.Schema({
  userId: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  battleRoomId: { type: mongoose.Schema.Types.ObjectId, ref: 'BattleRoom' },
  quizId: { type: mongoose.Schema.Types.ObjectId, ref: 'Quiz' },
  type: { type: String, enum: ['solo','1v1','group','team','school','standard'], default: 'solo' },
  subject: String,
  topic: String,
  difficulty: String,
  score: { type: Number, default: 0 },
  totalQ: { type: Number, default: 0 },
  accuracy: { type: Number, default: 0 },
  xpEarned: { type: Number, default: 0 },
  timeTaken: { type: Number, default: 0 },
  rank: { type: Number },
  teacherId: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  institutionId: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
}, { timestamps: true })

module.exports = mongoose.model('Result', resultSchema)
