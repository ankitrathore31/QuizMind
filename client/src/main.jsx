import React from 'react'
import ReactDOM from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'
import { Toaster } from 'react-hot-toast'
import App from './App'
import './styles/global.css'

ReactDOM.createRoot(document.getElementById('root')).render(
  <BrowserRouter>
    <App />
    <Toaster
      position="top-right"
      toastOptions={{
        style: { background: '#161628', color: '#EEEDf8', border: '1px solid rgba(255,255,255,0.1)', borderRadius: '12px', fontSize: '13px', fontFamily: "'DM Sans', sans-serif" },
        success: { iconTheme: { primary: '#00E396', secondary: '#161628' } },
        error: { iconTheme: { primary: '#FF4D6A', secondary: '#161628' } },
      }}
    />
  </BrowserRouter>
)
