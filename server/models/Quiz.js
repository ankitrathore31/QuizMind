const mongoose = require('mongoose')

const questionSchema = new mongoose.Schema({
  question: { type: String, required: true },
  options: [{ type: String }],
  answer: { type: Number, required: true },
  explanation: { type: String, default: '' },
  topic: { type: String, default: '' },
  subject: { type: String, default: '' },
  source: { type: String, enum: ['topic','pdf','image','standard'], default: 'topic' },
  difficulty: { type: String, enum: ['beginner','intermediate','advanced'], default: 'intermediate' },
  createdBy: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
}, { timestamps: true })

const quizSchema = new mongoose.Schema({
  type: { type: String, enum: ['standard','ai'], default: 'standard' },
  subject: { type: String },
  class: { type: String },
  difficulty: { type: String, enum: ['beginner','intermediate','advanced'], default: 'intermediate' },
  questions: [questionSchema],
  createdBy: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  source: { type: String, enum: ['topic','pdf','image','standard'], default: 'standard' },
  topic: { type: String },
}, { timestamps: true })

const Question = mongoose.model('Question', questionSchema)
const Quiz = mongoose.model('Quiz', quizSchema)
module.exports = { Quiz, Question }
