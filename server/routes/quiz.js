const express = require('express')
const { protect } = require('../middleware/auth')
const { Quiz } = require('../models/Quiz')
const router = express.Router()

// GET /api/quiz/subjects
router.get('/subjects', protect, (req, res) => {
  res.json(['Math','English','Physics','Chemistry','Biology','History','Geography','Computer Science','Economics','Hindi','Sanskrit','GK','Science'])
})

// GET /api/quiz/classes
router.get('/classes', protect, (req, res) => {
  res.json(['Class 1','Class 2','Class 3','Class 4','Class 5','Class 6','Class 7','Class 8','Class 9','Class 10','Class 11','Class 12','Undergraduate','Postgraduate'])
})

module.exports = router
