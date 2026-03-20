const nodemailer = require('nodemailer');

// ✅ Create transporter
const transporter = nodemailer.createTransport({
  service: 'gmail',
  auth: {
    user: process.env.EMAIL_USER,
    pass: process.env.EMAIL_PASS,
  },
});

// ✅ Verify SMTP connection (runs on server start)
transporter.verify((err, success) => {
  if (err) {
    console.error('❌ SMTP ERROR:', err);
  } else {
    console.log('✅ SMTP READY');
  }
});

// ✅ Send OTP function
const sendOTP = async (email, otp, name) => {
  try {
    console.log('📩 Sending OTP to:', email);

    const info = await transporter.sendMail({
      from: `"QuizMind AI" <${process.env.EMAIL_USER}>`,
      to: email,
      subject: 'Your QuizMind OTP Code',
      html: `
        <div style="font-family:Arial,sans-serif;max-width:480px;margin:auto;background:#0A0A0F;padding:40px;border-radius:16px;">
          <h2 style="color:#7C5CFC;margin-bottom:8px;">QuizMind AI 🚀</h2>
          <p style="color:#ccc;">Hi <strong>${name || 'User'}</strong>,</p>
          <p style="color:#ccc;">Your verification code is:</p>

          <div style="font-size:36px;font-weight:800;letter-spacing:8px;color:#fff;background:rgba(124,92,252,.15);border:1px solid rgba(124,92,252,.3);padding:20px;border-radius:12px;text-align:center;margin:20px 0;">
            ${otp}
          </div>

          <p style="color:#888;font-size:13px;">This code expires in 10 minutes. Do not share it.</p>
        </div>
      `
    });

    console.log('✅ Email sent:', info.response);
    return true;

  } catch (error) {
    console.error('❌ EMAIL SEND ERROR:', error);
    throw error; // 🔴 VERY IMPORTANT (so auth.js can catch it)
  }
};

module.exports = { sendOTP };
