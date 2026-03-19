const mongoose = require('mongoose')

const battleRoomSchema = new mongoose.Schema({
  code: { type: String, required: true, unique: true },
  type: { type: String, enum: ['solo','1v1','group','team','school'], required: true },
  hostId: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  status: { type: String, enum: ['waiting','live','finished'], default: 'waiting' },

  // Participants
  participants: [{
    userId: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
    name: String,
    teamId: String,
    score: { type: Number, default: 0 },
    answers: [{ questionIndex: Number, answer: Number, correct: Boolean, timeTaken: Number }],
    cheated: { type: Boolean, default: false },
    cheatCount: { type: Number, default: 0 },
    xpEarned: { type: Number, default: 0 },
    joinedAt: { type: Date, default: Date.now },
  }],

  // Teams (for team/school battles)
  teams: [{
    id: String,
    name: String,
    inviteCode: String,
    score: { type: Number, default: 0 },
    schoolId: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  }],

  // School battle
  schoolAId: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  schoolBId: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  schoolAName: String,
  schoolBName: String,
  maxStudentsPerSchool: { type: Number, default: 40 },

  // Quiz
  quizId: { type: mongoose.Schema.Types.ObjectId, ref: 'Quiz' },
  questions: [{ type: Object }],
  currentQuestion: { type: Number, default: 0 },
  totalQuestions: { type: Number, default: 10 },
  subject: String,
  difficulty: { type: String, default: 'intermediate' },

  // Anti-cheat
  antiCheatLogs: [{
    userId: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
    userName: String,
    type: String,
    timestamp: { type: Date, default: Date.now },
    penalty: { type: Number, default: 0 },
  }],

  maxPlayers: { type: Number, default: 10 },
  startedAt: Date,
  endedAt: Date,
  winner: { type: String },
  winnerTeam: { type: String },
}, { timestamps: true })

module.exports = mongoose.model('BattleRoom', battleRoomSchema)
