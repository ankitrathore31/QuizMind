const express = require('express');
const jwt = require('jsonwebtoken');
const User = require('../models/User');
const { sendOTP } = require('../utils/email');
const { protect } = require('../middleware/auth');

const router = express.Router();

// 🔐 JWT token
const signToken = (id) =>
  jwt.sign({ id }, process.env.JWT_SECRET, { expiresIn: '7d' });

/* =========================
   REGISTER
========================= */
router.post('/register', async (req, res) => {
  try {
    const { name, email, password, role, college, schoolGroup, refCode: joinCode } = req.body;

    if (!name || !email || !password) {
      return res.status(400).json({ message: 'Required fields missing' });
    }

    let user = await User.findOne({ email });

    // ❌ If already verified → block
    if (user && user.isVerified) {
      return res.status(400).json({ message: 'Email already registered' });
    }

    // 🔢 Generate OTP
    const otp = Math.floor(100000 + Math.random() * 900000).toString();

    if (!user) {
      user = new User({
        name,
        email,
        password,
        role: role || 'student',
        college: college || '',
        schoolGroup: schoolGroup || '',
      });
    }

    // 🔁 Update OTP
    user.otp = otp;
    user.otpExpiry = new Date(Date.now() + 10 * 60 * 1000);

    // 🔗 Referral linking
    if (joinCode && role === 'student') {
      const ref = await User.findOne({ refCode: joinCode });
      if (ref) {
        if (ref.role === 'teacher') user.linkedTeacherId = ref._id;
        if (ref.role === 'institution') user.linkedInstitutionId = ref._id;
        if (ref.role === 'parent') user.linkedParentId = ref._id;
      }
    }

    await user.save();

    // 📩 Send OTP
    try {
      await sendOTP(email, otp, name);
      console.log("✅ OTP sent successfully");
    } catch (err) {
      console.error("❌ OTP EMAIL ERROR:", err);

      // ⚠️ Don't block registration
      return res.status(200).json({
        message: "User created but email failed",
        debugOtp: otp // 🔥 remove later
      });
    }

    res.status(201).json({ message: 'OTP sent to email', email });

  } catch (err) {
    console.error('❌ REGISTER ERROR:', err);
    res.status(500).json({ message: err.message || 'Registration failed' });
  }
});

/* =========================
   VERIFY OTP
========================= */
router.post('/verify-otp', async (req, res) => {
  try {
    const { email, otp } = req.body;

    const user = await User.findOne({ email });
    if (!user) return res.status(404).json({ message: 'User not found' });

    if (user.otp !== otp) {
      return res.status(400).json({ message: 'Invalid OTP' });
    }

    if (new Date() > user.otpExpiry) {
      return res.status(400).json({ message: 'OTP expired' });
    }

    user.isVerified = true;
    user.otp = undefined;
    user.otpExpiry = undefined;

    await user.save();

    const token = signToken(user._id);

    res.json({
      message: 'Email verified',
      token,
      user: sanitize(user),
    });

  } catch (err) {
    console.error('❌ VERIFY OTP ERROR:', err);
    res.status(500).json({ message: err.message });
  }
});

/* =========================
   RESEND OTP
========================= */
router.post('/resend-otp', async (req, res) => {
  try {
    const { email } = req.body;

    const user = await User.findOne({ email });
    if (!user) return res.status(404).json({ message: 'User not found' });

    user.otp = Math.floor(100000 + Math.random() * 900000).toString();
    user.otpExpiry = new Date(Date.now() + 10 * 60 * 1000);

    await user.save();

    try {
      await sendOTP(email, user.otp, user.name);
      console.log("✅ OTP resent successfully");
    } catch (err) {
      console.error("❌ RESEND OTP ERROR:", err);
      return res.status(500).json({ message: "Failed to resend OTP" });
    }

    res.json({ message: 'OTP resent' });

  } catch (err) {
    console.error('❌ RESEND ERROR:', err);
    res.status(500).json({ message: err.message });
  }
});

/* =========================
   LOGIN
========================= */
router.post('/login', async (req, res) => {
  try {
    const { email, password } = req.body;

    const user = await User.findOne({ email });

    if (!user || !(await user.comparePassword(password))) {
      return res.status(401).json({ message: 'Invalid email or password' });
    }

    if (!user.isVerified) {
      return res.status(403).json({
        message: 'Please verify your email first',
        needsVerification: true,
      });
    }

    const token = signToken(user._id);

    res.json({
      token,
      user: sanitize(user),
    });

  } catch (err) {
    console.error('❌ LOGIN ERROR:', err);
    res.status(500).json({ message: err.message });
  }
});

/* =========================
   GET ME
========================= */
router.get('/me', protect, (req, res) => {
  res.json(sanitize(req.user));
});

/* =========================
   SANITIZE USER
========================= */
function sanitize(u) {
  return {
    _id: u._id,
    name: u.name,
    email: u.email,
    role: u.role,
    college: u.college,
    xp: u.xp,
    level: u.level,
    streak: u.streak,
    badges: u.badges,
    onboardingComplete: u.onboardingComplete,
    class: u.class,
    age: u.age,
    interests: u.interests,
    weakSubjects: u.weakSubjects,
    refCode: u.refCode,
    refLink: u.refLink,
    linkedTeacherId: u.linkedTeacherId,
    linkedInstitutionId: u.linkedInstitutionId,
    createdAt: u.createdAt,
  };
}

module.exports = router;
