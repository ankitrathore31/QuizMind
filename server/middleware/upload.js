const multer = require('multer')

const storage = multer.memoryStorage()

const pdfFilter = (req, file, cb) => {
  if (file.mimetype === 'application/pdf') cb(null, true)
  else cb(new Error('Only PDF files allowed'), false)
}

const imgFilter = (req, file, cb) => {
  if (file.mimetype.startsWith('image/')) cb(null, true)
  else cb(new Error('Only image files allowed'), false)
}

const uploadPDF = multer({ storage, fileFilter: pdfFilter, limits: { fileSize: 10 * 1024 * 1024 } })
const uploadImg = multer({ storage, fileFilter: imgFilter, limits: { fileSize: 5 * 1024 * 1024 } })

module.exports = { uploadPDF, uploadImg }
