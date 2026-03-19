const mongoose = require('mongoose')
const bcrypt = require('bcryptjs')
const { v4: uuidv4 } = require('uuid')

const userSchema = new mongoose.Schema({
  name: { type: String, required: true, trim: true },
  email: { type: String, required: true, unique: true, lowercase: true },
  password: { type: String, required: true, minlength: 6 },
  role: { type: String, enum: ['student','teacher','institution','parent'], default: 'student' },
  college: { type: String, default: '' },
  schoolGroup: { type: String, default: '' },

  // Reference system
  refCode: { type: String, unique: true, sparse: true },
  refLink: { type: String },

  // Links (student)
  linkedTeacherId: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  linkedInstitutionId: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  linkedParentId: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },

  // Gamification
  xp: { type: Number, default: 0 },
  level: { type: Number, default: 1 },
  streak: { type: Number, default: 0 },
  lastActiveDate: { type: Date },
  badges: [{ type: String }],

  // Student profile
  class: { type: String, default: '' },
  age: { type: Number },
  interests: [{ type: String }],
  onboardingComplete: { type: Boolean, default: false },
  weakSubjects: [{ type: String }],

  // Auth
  isVerified: { type: Boolean, default: false },
  otp: { type: String },
  otpExpiry: { type: Date },
  refreshToken: { type: String },
}, { timestamps: true })

// Generate refCode for teachers/institutions/parents
userSchema.pre('save', async function(next) {
  if (this.isNew && ['teacher','institution','parent'].includes(this.role) && !this.refCode) {
    const prefix = this.role === 'teacher' ? 'TCH' : this.role === 'institution' ? 'SCH' : 'PAR'
    this.refCode = `${prefix}-${uuidv4().slice(0,5).toUpperCase()}`
    this.refLink = `/join/${this.refCode}`
  }
  if (this.isModified('password')) {
    this.password = await bcrypt.hash(this.password, 12)
  }
  next()
})

userSchema.methods.comparePassword = function(pwd) {
  return bcrypt.compare(pwd, this.password)
}

module.exports = mongoose.model('User', userSchema)
