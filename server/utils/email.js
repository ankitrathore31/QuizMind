const { Resend } = require('resend');
const resend = new Resend(process.env.RESEND_API_KEY);

const sendOTP = async (email, otp, name) => {
  const { error } = await resend.emails.send({
    from: 'QuizMind <onboarding@resend.dev>',
    to: email,
    subject: '🔐 Your QuizMind Verification Code',
    html: `
      <div style="font-family:sans-serif;max-width:480px;margin:auto;padding:32px;border:1px solid #eee;border-radius:12px">
        <h2 style="color:#6c47ff">QuizMind</h2>
        <p>Hi <strong>${name}</strong>,</p>
        <p>Your email verification code is:</p>
        <div style="font-size:36px;font-weight:800;letter-spacing:10px;color:#6c47ff;margin:24px 0">${otp}</div>
        <p style="color:#888;font-size:13px">This code expires in <strong>10 minutes</strong>. Do not share it with anyone.</p>
      </div>
    `
  });

  if (error) throw new Error(error.message);
};

module.exports = { sendOTP };
```

Then in your Render env vars, add your **new** key:
```
RESEND_API_KEY = re_your_new_key_here