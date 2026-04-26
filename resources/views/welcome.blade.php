@extends('layouts.app')
@section('title', 'QuizMind AI — Master Every Subject with AI Battle Quizzes')
@section('content')

    <style>
        
        @import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap');

        :root {
            --pur: #7C5CFC;
            --pur2: #9d7fff;
            --cyan: #00D4FF;
            --cyan2: #00aacc;
            --bg0: #080714;
            --bg1: #0d0c1a;
            --bg2: #13112a;
            --surface: rgba(255, 255, 255, 0.04);
            --surface2: rgba(255, 255, 255, 0.07);
            --border: rgba(124, 92, 252, 0.2);
            --border2: rgba(255, 255, 255, 0.09);
            --text0: #f0eeff;
            --text1: #b8b4d8;
            --text2: #7a78a0;
            --radius: 16px;
            --radius-lg: 22px;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg0);
            color: var(--text0);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg0);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--pur);
            border-radius: 3px;
        }

        /* ── Utility ── */
        .text-grad {
            background: linear-gradient(90deg, var(--pur), var(--cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .text-center {
            text-align: center;
        }

        .section-eyebrow {
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 3px;
            color: var(--pur);
            text-transform: uppercase;
            margin-bottom: 14px;
        }

        .section-title {
            font-family: 'Syne', sans-serif;
            font-size: clamp(30px, 4vw, 48px);
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 18px;
            color: var(--text0);
        }

        .section-sub {
            font-size: 16px;
            color: var(--text1);
            max-width: 560px;
            margin: 0 auto 0;
            line-height: 1.75;
        }

        .mb-section {
            margin-bottom: 64px;
        }

        /* ── Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 13px 28px;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all .25s ease;
            text-decoration: none;
            border: none;
        }

        .btn-lg {
            padding: 15px 34px;
            font-size: 15px;
        }

        .btn-sm {
            padding: 8px 18px;
            font-size: 13px;
        }

        .btn-grad {
            background: linear-gradient(90deg, var(--pur), #5e3fd8);
            color: #fff;
            box-shadow: 0 0 28px rgba(124, 92, 252, 0.35);
        }

        .btn-grad:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 40px rgba(124, 92, 252, 0.55);
        }

        .btn-ghost {
            background: transparent;
            color: var(--text1);
            border: 1px solid var(--border2);
        }

        .btn-ghost:hover {
            background: var(--surface2);
            color: var(--text0);
            border-color: var(--border);
        }

        .btn-cyan {
            background: linear-gradient(90deg, var(--cyan2), var(--cyan));
            color: #000;
            font-weight: 600;
        }

        /* ── Reveal animations ── */
        .reveal {
            opacity: 0;
            transform: translateY(32px);
            transition: opacity .7s ease, transform .7s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .reveal-delay-1 {
            transition-delay: .1s;
        }

        .reveal-delay-2 {
            transition-delay: .2s;
        }

        .reveal-delay-3 {
            transition-delay: .3s;
        }

        .reveal-delay-4 {
            transition-delay: .4s;
        }

        .reveal-delay-5 {
            transition-delay: .5s;
        }

        .reveal-delay-6 {
            transition-delay: .6s;
        }

        /* ── Fade-in ── */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes floatY {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .4;
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -200% center;
            }

            100% {
                background-position: 200% center;
            }
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0;
            }
        }

        /* ══════════════════════════════════════
       HERO SECTION
    ══════════════════════════════════════ */
        .hero-section {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 0px 24px 80px;
        }

        #three-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 820px;
        }

        .hero-badge {
            display: inline-block;
            padding: 6px 18px;
            border-radius: 30px;
            background: rgba(124, 92, 252, 0.15);
            border: 1px solid rgba(124, 92, 252, 0.4);
            color: var(--pur2);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 28px;
            animation: fadeUp .6s ease both;
        }

        .hero-title {
            font-family: 'Syne', sans-serif;
            font-size: clamp(40px, 6vw, 66px);
            font-weight: 800;
            line-height: 1.08;
            margin-bottom: 24px;
            animation: fadeUp .6s .1s ease both;
        }

        .hero-desc {
            font-size: clamp(16px, 1.8vw, 19px);
            color: var(--text1);
            max-width: 580px;
            margin: 0 auto 36px;
            line-height: 1.75;
            animation: fadeUp .6s .2s ease both;
        }

        .hero-buttons {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 64px;
            animation: fadeUp .6s .3s ease both;
        }

        .hero-stats {
            display: flex;
            gap: 40px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeUp .6s .4s ease both;
        }

        .hero-stat-val {
            font-family: 'Syne', sans-serif;
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(90deg, var(--pur), var(--cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-stat-label {
            font-size: 12px;
            color: var(--text2);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .scroll-indicator {
            position: absolute;
            bottom: 36px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text2);
            animation: fadeUp 1s .8s ease both;
        }

        .scroll-line {
            width: 1px;
            height: 40px;
            background: linear-gradient(to bottom, transparent, var(--pur), transparent);
            animation: pulse 2s ease infinite;
        }

        /* ══════════════════════════════════════
       SECTION WRAPPER
    ══════════════════════════════════════ */
        .lp-section {
            padding: 100px 24px;
            max-width: 1120px;
            margin: 0 auto;
        }

        .lp-section--dark {
            background: rgba(255, 255, 255, 0.015);
            border-top: 1px solid var(--border2);
            border-bottom: 1px solid var(--border2);
        }

        .lp-section--dark>* {
            max-width: 1120px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 24px;
            padding-right: 24px;
        }

        /* ══════════════════════════════════════
       GRID LAYOUTS
    ══════════════════════════════════════ */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-top: 48px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 22px;
            margin-top: 48px;
        }

        .grid-5 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
            margin-top: 48px;
        }

        /* ══════════════════════════════════════
       FEATURE CARDS
    ══════════════════════════════════════ */
        .feature-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 28px 24px;
            transition: border-color .3s, transform .3s, background .3s;
            cursor: default;
        }

        .feature-card:hover {
            border-color: rgba(124, 92, 252, 0.6);
            background: rgba(124, 92, 252, 0.07);
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 30px;
            display: block;
            margin-bottom: 16px;
        }

        .feature-title {
            font-family: 'Syne', sans-serif;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text0);
        }

        .feature-desc {
            font-size: 14px;
            color: var(--text2);
            line-height: 1.65;
        }

        /* ══════════════════════════════════════
       PDF DEMO
    ══════════════════════════════════════ */
        .pdf-section {
            padding: 100px 24px;
            text-align: center;
            max-width: 1120px;
            margin: 0 auto;
        }

        .pdf-title {
            font-family: 'Syne', sans-serif;
            font-size: clamp(26px, 3.5vw, 42px);
            font-weight: 800;
            margin-bottom: 16px;
        }

        .pdf-desc {
            font-size: 16px;
            color: var(--text1);
            max-width: 520px;
            margin: 0 auto 48px;
        }

        .pdf-demo-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 36px;
            max-width: 520px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }

        .pdf-icon {
            font-size: 40px;
            animation: floatY 3s ease infinite;
        }

        .pdf-filename {
            font-size: 14px;
            font-weight: 600;
            color: var(--pur2);
        }

        .pdf-progress-track {
            width: 100%;
            height: 6px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 4px;
            overflow: hidden;
        }

        .pdf-progress-bar {
            height: 100%;
            width: 0;
            border-radius: 4px;
            background: linear-gradient(90deg, var(--pur), var(--cyan));
            transition: width 1.6s ease;
        }

        .pdf-success {
            font-size: 13px;
            font-weight: 600;
            color: #00dc82;
            opacity: 0;
            transition: opacity .5s ease;
        }

        /* ══════════════════════════════════════
       HOW IT WORKS
    ══════════════════════════════════════ */
        .step-number {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--pur), var(--cyan));
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Syne', sans-serif;
            font-size: 22px;
            font-weight: 800;
            margin: 0 auto 20px;
            box-shadow: 0 0 28px rgba(124, 92, 252, 0.4);
        }

        .step-title {
            font-family: 'Syne', sans-serif;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .step-desc {
            font-size: 14px;
            color: var(--text2);
            line-height: 1.7;
            max-width: 280px;
            margin: 0 auto;
        }

        /* ══════════════════════════════════════
       AI TUTOR CHAT
    ══════════════════════════════════════ */
        .tutor-chat-demo {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 28px;
            max-width: 680px;
            margin: 48px auto 0;
            text-align: left;
        }

        .tutor-chat-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--border2);
            margin-bottom: 22px;
        }

        .tutor-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--pur), var(--cyan));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .tutor-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--text0);
        }

        .tutor-status {
            font-size: 11px;
            color: #00dc82;
        }

        .chat-msg {
            display: flex;
            gap: 10px;
            margin-bottom: 14px;
        }

        .chat-msg--user {
            flex-direction: row-reverse;
        }

        .chat-msg-ico {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .chat-msg--ai .chat-msg-ico {
            background: rgba(124, 92, 252, 0.2);
        }

        .chat-msg--user .chat-msg-ico {
            background: rgba(0, 212, 255, 0.15);
        }

        .chat-bubble {
            max-width: 75%;
            padding: 11px 15px;
            border-radius: 14px;
            font-size: 13px;
            line-height: 1.65;
        }

        .chat-msg--ai .chat-bubble {
            background: rgba(124, 92, 252, 0.12);
            color: #d4d0ff;
            border-bottom-left-radius: 4px;
        }

        .chat-msg--user .chat-bubble {
            background: linear-gradient(135deg, var(--pur), #5b3ed0);
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        .tutor-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            padding-top: 16px;
            border-top: 1px solid var(--border2);
            margin-top: 16px;
        }

        .tutor-meta {
            font-size: 12px;
            color: var(--text2);
        }

        /* ══════════════════════════════════════
       SOURCE CARDS (Generate MCQ)
    ══════════════════════════════════════ */
        .source-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
            margin-top: 48px;
        }

        .source-card {
            background: var(--surface);
            border: 1px solid var(--border2);
            border-radius: var(--radius-lg);
            padding: 28px 20px;
            text-align: center;
            cursor: pointer;
            transition: all .3s;
            position: relative;
            overflow: hidden;
        }

        .source-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(124, 92, 252, 0.1), rgba(0, 212, 255, 0.05));
            opacity: 0;
            transition: opacity .3s;
        }

        .source-card:hover::before {
            opacity: 1;
        }

        .source-card:hover {
            border-color: rgba(124, 92, 252, 0.5);
            transform: translateY(-5px);
        }

        .source-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 212, 255, 0.15);
            color: var(--cyan);
            font-size: 10px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 10px;
            border: 1px solid rgba(0, 212, 255, 0.3);
        }

        .source-ico {
            font-size: 32px;
            margin-bottom: 12px;
            display: block;
        }

        .source-t {
            font-size: 14px;
            font-weight: 600;
            color: var(--text0);
            margin-bottom: 6px;
        }

        .source-d {
            font-size: 12px;
            color: var(--text2);
            line-height: 1.5;
        }

        .prog-track {
            height: 5px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 4px;
            margin-top: 12px;
            overflow: hidden;
        }

        .prog-bar {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, var(--pur), var(--cyan));
            border-radius: 4px;
            transition: width 1.5s ease;
        }

        /* ══════════════════════════════════════
       MANUAL MCQ BUILDER
    ══════════════════════════════════════ */
        .mcq-builder {
            background: var(--surface);
            border: 1px solid var(--border2);
            border-radius: var(--radius-lg);
            padding: 28px;
            max-width: 680px;
            margin: 36px auto 0;
            text-align: left;
        }

        .builder-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .builder-head-title {
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: var(--text0);
        }

        .q-block {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 14px;
        }

        .q-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--pur);
            margin-bottom: 8px;
        }

        .q-text {
            font-size: 14px;
            font-weight: 500;
            color: var(--text0);
            margin-bottom: 14px;
        }

        .opts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .opt {
            padding: 9px 13px;
            border-radius: 8px;
            font-size: 12px;
            color: var(--text2);
            border: 1px solid rgba(255, 255, 255, 0.07);
            background: rgba(255, 255, 255, 0.03);
        }

        .opt--correct {
            border-color: rgba(0, 212, 255, 0.4);
            background: rgba(0, 212, 255, 0.08);
            color: var(--cyan);
        }

        .opt--wrong {
            border-color: rgba(255, 80, 80, 0.3);
            background: rgba(255, 80, 80, 0.06);
            color: #ff8888;
        }

        .builder-footer {
            display: flex;
            gap: 10px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        /* ══════════════════════════════════════
       PLAY MODES
    ══════════════════════════════════════ */
        .modes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 18px;
            margin-top: 48px;
        }

        .mode-card {
            border-radius: var(--radius-lg);
            padding: 24px;
            border: 1px solid;
            transition: transform .3s, box-shadow .3s;
            cursor: pointer;
            position: relative;
            text-align: left;
        }

        .mode-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .mode-card--solo {
            background: rgba(124, 92, 252, 0.07);
            border-color: rgba(124, 92, 252, 0.3);
        }

        .mode-card--duo {
            background: rgba(0, 212, 255, 0.06);
            border-color: rgba(0, 212, 255, 0.3);
        }

        .mode-card--team {
            background: rgba(0, 220, 130, 0.06);
            border-color: rgba(0, 220, 130, 0.3);
        }

        .mode-card--school {
            background: rgba(255, 165, 0, 0.06);
            border-color: rgba(255, 165, 0, 0.3);
        }

        .mode-card--ai {
            background: rgba(255, 80, 150, 0.06);
            border-color: rgba(255, 80, 150, 0.3);
        }

        .mode-pill {
            position: absolute;
            top: 14px;
            right: 14px;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 20px;
        }

        .mode-card--solo .mode-pill {
            background: rgba(124, 92, 252, 0.2);
            color: var(--pur);
        }

        .mode-card--duo .mode-pill {
            background: rgba(0, 212, 255, 0.15);
            color: var(--cyan);
        }

        .mode-card--team .mode-pill {
            background: rgba(0, 220, 130, 0.15);
            color: #00dc82;
        }

        .mode-card--school.mode-pill {
            background: rgba(255, 165, 0, 0.15);
            color: #ffa500;
        }

        .mode-card--ai .mode-pill {
            background: rgba(255, 80, 150, 0.15);
            color: #ff5096;
        }

        .mode-card--school .mode-pill {
            background: rgba(255, 165, 0, 0.15);
            color: #ffa500;
        }

        .mode-ico {
            font-size: 28px;
            margin-bottom: 14px;
            display: block;
        }

        .mode-title {
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: var(--text0);
            margin-bottom: 8px;
        }

        .mode-desc {
            font-size: 12px;
            color: var(--text2);
            line-height: 1.6;
        }

        /* Live match card */
        .live-match-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(0, 212, 255, 0.2);
            border-radius: var(--radius-lg);
            padding: 28px;
            max-width: 600px;
            margin: 36px auto 0;
        }

        .live-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 700;
            color: #00dc82;
        }

        .live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #00dc82;
            animation: pulse 1.5s ease infinite;
        }

        .vs-grid {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 16px;
            margin: 20px 0;
        }

        .vs-label {
            font-size: 24px;
            font-weight: 900;
            color: #ffa500;
            text-align: center;
            font-family: 'Syne', sans-serif;
        }

        .player-name {
            font-size: 15px;
            font-weight: 700;
            color: var(--text0);
            margin-bottom: 4px;
        }

        .player-xp {
            font-size: 11px;
            color: var(--text2);
            margin-bottom: 10px;
        }

        .score-bar-wrap {
            height: 7px;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 4px;
        }

        .score-bar-fill {
            height: 100%;
            border-radius: 4px;
        }

        .score-label {
            font-size: 11px;
        }

        /* ══════════════════════════════════════
       INSTITUTION SECTION
    ══════════════════════════════════════ */
        .inst-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            margin-top: 48px;
            align-items: start;
        }

        @media(max-width: 640px) {
            .inst-layout {
                grid-template-columns: 1fr;
            }
        }

        .inst-panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 28px;
            margin-bottom: 20px;
        }

        .inst-panel:last-child {
            margin-bottom: 0;
        }

        .inst-panel-title {
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: var(--text0);
            margin-bottom: 4px;
        }

        .inst-panel-sub {
            font-size: 12px;
            color: var(--text2);
            margin-bottom: 20px;
        }

        .inst-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 0;
            border-bottom: 1px solid var(--border2);
        }

        .inst-row:last-child {
            border-bottom: none;
        }

        .inst-row-ico {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(124, 92, 252, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .inst-row-name {
            font-size: 13px;
            font-weight: 500;
            color: var(--text0);
        }

        .inst-row-sub {
            font-size: 11px;
            color: var(--text2);
        }

        .status-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            margin-left: auto;
            flex-shrink: 0;
        }

        .status-dot--live {
            background: #00dc82;
            box-shadow: 0 0 8px #00dc82;
            animation: pulse 1.5s infinite;
        }

        .status-dot--soon {
            background: #ffa500;
        }

        .status-dot--done {
            background: #555;
        }

        .cfg-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 14px;
        }

        .cfg-item {
            border-radius: 10px;
            padding: 11px 13px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border2);
        }

        .cfg-item span {
            font-size: 11px;
            color: var(--text2);
            display: block;
            margin-bottom: 3px;
        }

        .cfg-item strong {
            font-size: 13px;
            color: var(--text0);
            font-weight: 600;
        }

        .lb-row {
            display: flex;
            align-items: center;
            padding: 9px 0;
            border-bottom: 1px solid var(--border2);
            font-size: 13px;
        }

        .lb-row:last-child {
            border-bottom: none;
        }

        .lb-rank {
            font-weight: 700;
            color: #ffa500;
            width: 30px;
        }

        .lb-name {
            flex: 1;
            color: var(--text1);
        }

        .lb-score {
            font-weight: 600;
            color: var(--text0);
        }

        /* ══════════════════════════════════════
       CERTIFICATE SECTION
    ══════════════════════════════════════ */
        .cert-card {
            max-width: 660px;
            margin: 48px auto 0;
            background: linear-gradient(135deg, rgba(124, 92, 252, 0.1), rgba(0, 212, 255, 0.07));
            border: 1.5px solid rgba(124, 92, 252, 0.45);
            border-radius: 24px;
            padding: 42px 40px;
            position: relative;
            overflow: hidden;
            text-align: left;
        }

        .cert-card::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: radial-gradient(rgba(124, 92, 252, 0.25), transparent 70%);
            pointer-events: none;
        }

        .cert-card::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: -30px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: radial-gradient(rgba(0, 212, 255, 0.18), transparent 70%);
            pointer-events: none;
        }

        .cert-logo {
            font-family: 'Syne', sans-serif;
            font-size: 12px;
            font-weight: 800;
            color: var(--pur);
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .cert-headline {
            font-size: 13px;
            color: var(--text2);
            margin-bottom: 18px;
        }

        .cert-issued-to {
            font-size: 12px;
            color: var(--text2);
            margin-bottom: 8px;
        }

        .cert-name {
            font-family: 'Syne', sans-serif;
            font-size: 32px;
            font-weight: 800;
            color: var(--text0);
            margin-bottom: 14px;
        }

        .cert-body {
            font-size: 14px;
            color: var(--text1);
            line-height: 1.75;
            margin-bottom: 20px;
        }

        .cert-body strong {
            color: var(--text0);
        }

        .cert-subject-pill {
            display: inline-block;
            padding: 7px 20px;
            background: linear-gradient(90deg, rgba(124, 92, 252, 0.2), rgba(0, 212, 255, 0.12));
            border: 1px solid rgba(124, 92, 252, 0.4);
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            color: #c4bcff;
            margin-bottom: 28px;
        }

        .cert-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 12px;
            color: var(--text2);
            gap: 12px;
        }

        .cert-seal {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--pur), var(--cyan));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            box-shadow: 0 0 20px rgba(124, 92, 252, 0.5);
        }

        .cert-verify {
            font-size: 10px;
            font-weight: 600;
            color: var(--pur);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 3px;
        }

        /* ══════════════════════════════════════
       FOOTER
    ══════════════════════════════════════ */
        footer {
            text-align: center;
            padding: 40px 24px;
            font-size: 13px;
            color: var(--text2);
            border-top: 1px solid var(--border2);
        }

        /* ══════════════════════════════════════
       RESPONSIVE
    ══════════════════════════════════════ */
        @media(max-width: 600px) {
            .hero-stats {
                gap: 24px;
            }

            .inst-layout {
                grid-template-columns: 1fr;
            }

            .grid-3,
            .grid-5,
            .modes-grid,
            .source-grid {
                grid-template-columns: 1fr 1fr;
            }

            .opts-grid {
                grid-template-columns: 1fr;
            }

            .cert-card {
                padding: 28px 20px;
            }
        }

        @media(max-width: 400px) {

            .grid-3,
            .grid-5,
            .modes-grid,
            .source-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    {{-- ═══════════════════════════════════════════
     HERO SECTION
═══════════════════════════════════════════ --}}
    <section class="hero-section">
        <div id="three-bg"></div>
        <div class="hero-content">
            <div class="hero-badge">✨ AI-POWERED QUIZ BATTLE PLATFORM</div>
            <h1 class="hero-title">
                Master Every Subject<br>
                <span class="text-grad">with AI Battle Quizzes</span>
            </h1>
            <p class="hero-desc">
                Generate MCQs from any topic, PDF, or image. Battle friends live.
                AI tracks your weak spots and guides improvement.
            </p>
            <div class="hero-buttons">
                <a href="{{ route('login') }}" class="btn btn-grad btn-lg">Get Started Free →</a>
                <a href="{{ route('login') }}" class="btn btn-ghost btn-lg">Login</a>
            </div>
            <div class="hero-stats">
                @foreach ([['50K+', 'Students'], ['2M+', 'Quizzes Played'], ['500+', 'Schools'], ['98%', 'Satisfaction']] as $s)
                    <div style="text-align:center">
                        <div class="hero-stat-val">{{ $s[0] }}</div>
                        <div class="hero-stat-label">{{ $s[1] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="scroll-indicator">
            <div class="scroll-line"></div>scroll
        </div>
    </section>

    {{-- ═══════════════════════════════════════════
     FEATURES
═══════════════════════════════════════════ --}}
    <section id="features" class="lp-section">
        <div class="text-center mb-section reveal">
            <div class="section-eyebrow">FEATURES</div>
            <h2 class="section-title">Everything you need to excel</h2>
        </div>
        <div class="grid-3">
            @php $features = [['🤖', 'AI Quiz Generator', 'Generate MCQs instantly from any topic, PDF, or educational image using Groq AI.'], ['⚔️', 'Live Battle System', '1v1, Team vs Team, and epic School vs School battles with real-time leaderboards.'], ['📊', 'Smart Analytics', 'AI detects weak subjects and creates personalized practice schedules automatically.'], ['🛡️', 'Anti-Cheat Engine', 'Tab switch detection, window blur tracking, and real-time penalty system.'], ['👨‍👩‍👧', 'Multi-Role Platform', 'Dedicated dashboards for students, parents, teachers, and institutions.'], ['🏆', 'Gamification', 'XP system, streaks, trophies, badges, and global leaderboards to keep you motivated.']]; @endphp
            @foreach ($features as $i => $f)
                <div class="feature-card reveal reveal-delay-{{ $i + 1 }}">
                    <span class="feature-icon">{{ $f[0] }}</span>
                    <h3 class="feature-title">{{ $f[1] }}</h3>
                    <p class="feature-desc">{{ $f[2] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ═══════════════════════════════════════════
     PDF DEMO
═══════════════════════════════════════════ --}}
    <section class="pdf-section lp-section--dark" style="padding:100px 24px;text-align:center">
        <div style="max-width:1120px;margin:0 auto">
            <h2 class="pdf-title reveal">Drop a PDF. Get MCQs instantly.</h2>
            <p class="pdf-desc reveal reveal-delay-1">
                Upload any NCERT, textbook, or study notes PDF and our AI generates quiz questions in seconds.
            </p>
            <div class="pdf-demo-box reveal reveal-delay-2" id="pdfDemo">
                <span class="pdf-icon">📄</span>
                <div class="pdf-filename">NCERT_Physics_Chapter5.pdf</div>
                <div class="pdf-progress-track">
                    <div class="pdf-progress-bar" id="pdfBar"></div>
                </div>
                <div class="pdf-success" id="pdfSuccess">✅ 10 questions generated from 47 pages!</div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════
     AI TUTOR CHAT
═══════════════════════════════════════════ --}}
    <section id="ai-tutor" class="lp-section">
        <div class="text-center mb-section reveal">
            <div class="section-eyebrow">AI TUTOR</div>
            <h2 class="section-title">Learn it. <span class="text-grad">Then quiz it.</span></h2>
            <p class="section-sub">Chat with AI to master any concept — then convert the entire conversation into an MCQ
                quiz and play instantly.</p>
        </div>
        <div class="tutor-chat-demo reveal reveal-delay-1">
            <div class="tutor-chat-header">
                <div class="tutor-avatar">🤖</div>
                <div>
                    <div class="tutor-name">QuizMind AI Tutor</div>
                    <div class="tutor-status">● Online — Physics · Class 12</div>
                </div>
            </div>
            @php $msgs = [['user', 'Explain Newton\'s Third Law with a real example.', '🙋'], ['ai', 'For every action there is an equal and opposite reaction. When you push off a wall while skating, the wall pushes you back with equal force — propelling you forward.', '🤖'], ['user', 'Give me a rocket example too?', '🙋'], ['ai', 'A rocket expels hot gas downward — the equal reaction force pushes the rocket upward. Thrust equals the rate of momentum change of expelled gases.', '🤖']]; @endphp
            @foreach ($msgs as $i => $m)
                <div class="chat-msg chat-msg--{{ $m[0] }}"
                    style="animation:fadeUp .4s {{ $i * 0.15 }}s ease both">
                    <div class="chat-msg-ico">{{ $m[2] }}</div>
                    <div class="chat-bubble">{{ $m[1] }}</div>
                </div>
            @endforeach
            <div class="tutor-actions">
                <span class="tutor-meta">2 concepts covered · 4 messages</span>
                <a href="{{ route('login') }}" class="btn btn-grad btn-sm">↗ Convert Chat to MCQ &amp; Play</a>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════
     GENERATE MCQs FROM ANY SOURCE
═══════════════════════════════════════════ --}}
    <section id="generate" class="lp-section lp-section--dark" style="padding:100px 24px">
        <div style="max-width:1120px;margin:0 auto">
            <div class="text-center mb-section reveal">
                <div class="section-eyebrow">GENERATE MCQs FROM</div>
                <h2 class="section-title">Any source. <span class="text-grad">Instant quiz.</span></h2>
                <p class="section-sub">PDF, image, AI chat, topic, URL — or build manually. Transform anything into
                    exam-ready MCQs.</p>
            </div>
            <div class="source-grid">
                @php $sources = [['📄', 'PDF / Document', 'NCERT, textbooks, notes — any PDF becomes MCQs in seconds', 'AI', 'pdf'], ['🖼️', 'Image / Photo', 'Snap a textbook page, diagram, or handwritten notes', 'Vision AI', 'img'], ['💬', 'AI Chat Session', 'Convert your tutor conversation into a quiz instantly', 'NEW', 'chat'], ['✍️', 'Manual Builder', 'Write your own questions with custom options and answers', null, null], ['🌐', 'Topic / URL', 'Enter any topic or paste a web link — AI generates from it', null, null]]; @endphp
                @foreach ($sources as $i => $s)
                    <div class="source-card reveal reveal-delay-{{ $i + 1 }}"
                        @if ($s[4]) onclick="animateSource(this,'{{ $s[4] }}')" @endif>
                        @if ($s[3])
                            <span class="source-badge">{{ $s[3] }}</span>
                        @endif
                        <span class="source-ico">{{ $s[0] }}</span>
                        <div class="source-t">{{ $s[1] }}</div>
                        <div class="source-d">{{ $s[2] }}</div>
                        @if ($s[4])
                            <div class="prog-track" style="display:none">
                                <div class="prog-bar"></div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Manual MCQ Builder --}}
            <div class="mcq-builder reveal reveal-delay-2">
                <div class="builder-head">
                    <span class="builder-head-title">✍️ Manual MCQ Builder</span>
                    <button class="btn btn-grad btn-sm">+ Add Question</button>
                </div>
                @php $qs = [['PHYSICS', 'What is the SI unit of electric current?', ['✓ Ampere', '✗ Volt', 'Ohm', 'Watt']], ['CHEMISTRY', 'Atomic number of Carbon is?', ['✗ 8', '✓ 6', '12', '14']]]; @endphp
                @foreach ($qs as $q)
                    <div class="q-block">
                        <div class="q-label">{{ $q[0] }}</div>
                        <div class="q-text">{{ $q[1] }}</div>
                        <div class="opts-grid">
                            @foreach ($q[2] as $opt)
                                <div
                                    class="opt {{ Str::startsWith($opt, '✓') ? 'opt--correct' : (Str::startsWith($opt, '✗') ? 'opt--wrong' : '') }}">
                                    {{ $opt }}</div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <div class="builder-footer">
                    <a href="{{ route('login') }}" class="btn btn-grad btn-sm">▶ Play Now</a>
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Share Quiz</a>
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Save Template</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════
     PLAY MODES
═══════════════════════════════════════════ --}}
    <section id="play" class="lp-section">
        <div class="text-center mb-section reveal">
            <div class="section-eyebrow">PLAY MODES</div>
            <h2 class="section-title">Solo. Squad. <span class="text-grad">Epic Battles.</span></h2>
            <p class="section-sub">Five ways to play — from personal practice to school-wide championship battles.</p>
        </div>
        <div class="modes-grid">
            @php $modes = [['🧠', 'Solo Practice', 'Study at your own pace. AI tracks weak spots and auto-suggests next topics.', 'PRACTICE', 'solo'], ['⚔️', 'Friend Battle', 'Invite via code. Live 1v1 MCQ battle with real-time scores and penalties.', '1v1', 'duo'], ['👥', 'Team vs Team', 'Form squads of 2–10. Combined scores decide the champion team.', 'GROUP', 'team'], ['🏫', 'School vs School', 'Institution-wide battles with full leaderboards and department breakdowns.', 'EPIC', 'school'], ['🤖', 'Battle vs AI', 'No partner? Face our adaptive AI rival that matches your exact skill level.', 'AI RIVAL', 'ai']]; @endphp
            @foreach ($modes as $i => $m)
                <div class="mode-card mode-card--{{ $m[4] }} reveal reveal-delay-{{ $i + 1 }}">
                    <span class="mode-pill">{{ $m[2] }}</span>
                    <span class="mode-ico">{{ $m[0] }}</span>
                    <div class="mode-title">{{ $m[1] }}</div>
                    <div class="mode-desc">{{ $m[3] }}</div>
                </div>
            @endforeach
        </div>

        {{-- Live match preview --}}
        <div class="live-match-card reveal reveal-delay-2">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                <span style="font-size:13px;font-weight:600;color:var(--text0)">⚡ Live Match</span>
                <div class="live-badge">
                    <div class="live-dot"></div> IN PROGRESS
                </div>
            </div>
            <div class="vs-grid">
                <div>
                    <div class="player-name">Rahul K.</div>
                    <div class="player-xp">⚡ 1,840 XP</div>
                    <div class="score-bar-wrap" style="background:rgba(124,92,252,0.15)">
                        <div class="score-bar-fill"
                            style="width:72%;background:linear-gradient(90deg,var(--pur),var(--pur2))"></div>
                    </div>
                    <div class="score-label" style="color:var(--pur)">72% · 7/10</div>
                </div>
                <div class="vs-label">VS</div>
                <div style="text-align:right">
                    <div class="player-name">Priya M.</div>
                    <div class="player-xp">⚡ 2,105 XP</div>
                    <div class="score-bar-wrap" style="background:rgba(0,212,255,0.12)">
                        <div class="score-bar-fill"
                            style="width:60%;background:linear-gradient(90deg,var(--cyan2),var(--cyan));margin-left:auto">
                        </div>
                    </div>
                    <div class="score-label" style="color:var(--cyan);text-align:right">60% · 6/10</div>
                </div>
            </div>
            <div
                style="padding-top:16px;border-top:1px solid var(--border2);font-size:12px;color:var(--text2);text-align:center">
                Question 8 of 15 · Organic Chemistry · ⏱ 00:42 remaining
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════
     HOW IT WORKS
═══════════════════════════════════════════ --}}
    <section id="how" class="lp-section lp-section--dark" style="padding:100px 24px">
        <div style="max-width:1120px;margin:0 auto">
            <div class="text-center mb-section reveal">
                <h2 class="section-title">How It Works</h2>
            </div>
            <div class="grid-3">
                @php $steps = [['1', 'Sign Up &amp; Choose Role', 'Register as student, teacher, institution, or parent. Link to your school with a reference code.'], ['2', 'Generate or Pick a Quiz', 'Use AI to generate MCQs from topic, PDF, image, or chat — or build manually.'], ['3', 'Battle &amp; Improve', 'Play solo, invite friends, or host team battles. AI tracks performance and suggests improvements.']]; @endphp
                @foreach ($steps as $i => $s)
                    <div class="text-center reveal reveal-delay-{{ $i + 1 }}">
                        <div class="step-number">{{ $s[0] }}</div>
                        <h3 class="step-title">{!! $s[1] !!}</h3>
                        <p class="step-desc">{{ $s[2] }}</p>
                    </div>
                @endforeach
            </div>
            <div class="text-center" style="margin-top:48px">
                <a href="{{ route('login') }}" class="btn btn-grad btn-lg reveal reveal-delay-1">Start for Free →</a>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════
     INSTITUTION BATTLE ORGANIZER
═══════════════════════════════════════════ --}}
    {{-- <section id="institutions" class="lp-section">
  <div class="text-center mb-section reveal">
    <div class="section-eyebrow">FOR INSTITUTIONS</div>
    <h2 class="section-title">Organize. Host. <span class="text-grad">Crown Champions.</span></h2>
    <p class="section-sub">Schools and coaching institutes get a dedicated dashboard to schedule, manage, and broadcast inter-school tournaments live.</p>
  </div>
  <div class="inst-layout reveal reveal-delay-1">
    <div>
      <div class="inst-panel">
        <div class="inst-panel-title">🏫 Battle Dashboard</div>
        <div class="inst-panel-sub">Delhi Public School · Admin Panel</div>
        @php $battles = [
          ['📅','Science Olympiad 2025','120 students · 6 teams','live'],
          ['📐','Math Championship','Starts in 2 hours','soon'],
          ['📚','History Bowl — Finals','Completed · Winner announced','done'],
        ]; @endphp
        @foreach ($battles as $b)
        <div class="inst-row">
          <div class="inst-row-ico">{{ $b[0] }}</div>
          <div style="flex:1">
            <div class="inst-row-name">{{ $b[1] }}</div>
            <div class="inst-row-sub">{{ $b[2] }}</div>
          </div>
          <div class="status-dot status-dot--{{ $b[3] }}"></div>
        </div>
        @endforeach
        <div style="display:flex;gap:10px;margin-top:18px;flex-wrap:wrap">
          <a href="{{ route('login') }}" class="btn btn-grad btn-sm">+ New Tournament</a>
          <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">View Reports</a>
        </div>
      </div>
    </div>
    <div>
      <div class="inst-panel">
        <div class="inst-panel-title">🎯 Tournament Setup</div>
        <div class="inst-panel-sub">Configure your battle in minutes</div>
        <div class="cfg-grid">
          @php $cfg = [['Format','Bracket Elimination'],['Duration','45 min / round'],['Anti-Cheat','Enabled'],['Certificate','Auto-issue AI']]; @endphp
          @foreach ($cfg as $c)
          <div class="cfg-item"><span>{{ $c[0] }}</span><strong>{{ $c[1] }}</strong></div>
          @endforeach
        </div>
      </div>
      <div class="inst-panel">
        <div class="inst-panel-title">📊 Live Leaderboard</div>
        <div class="inst-panel-sub">Science Olympiad 2025 — Round 2</div>
        @php $lb = [['#1','Team Alpha','1,920'],['#2','Team Zenith','1,780'],['#3','Team Nova','1,640'],['#4','Team Spark','1,510']]; @endphp
        @foreach ($lb as $r)
        <div class="lb-row">
          <span class="lb-rank">{{ $r[0] }}</span>
          <span class="lb-name">{{ $r[1] }}</span>
          <span class="lb-score">{{ $r[2] }} pts</span>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</section> --}}

    {{-- ═══════════════════════════════════════════
     AI CERTIFICATES
═══════════════════════════════════════════ --}}
    <section id="certificates" class="lp-section lp-section--dark" style="padding:100px 24px">
        <div style="max-width:1120px;margin:0 auto">
            <div class="text-center mb-section reveal">
                <div class="section-eyebrow">AI CERTIFICATES</div>
                <h2 class="section-title">Win. Earn. <span class="text-grad">Certify.</span></h2>
                <p class="section-sub">QuizMind AI issues verified digital certificates for tournament winners and skill
                    milestones — sharable on LinkedIn, WhatsApp and Instagram.</p>
            </div>
            <div class="cert-card reveal reveal-delay-1">
                <div class="cert-logo">QuizMind AI</div>
                <div class="cert-headline">Certificate of Achievement</div>
                <div class="cert-issued-to">This certifies that</div>
                <div class="cert-name">Aryan Sharma</div>
                <p class="cert-body">
                    has successfully demonstrated exceptional knowledge and achieved <strong>1st Place</strong> in the
                    <strong>National Science Olympiad 2025</strong> organized through QuizMind AI,
                    scoring <strong>96.4%</strong> across all rounds.
                </p>
                <div class="cert-subject-pill">Physics · Chemistry · Biology</div>
                <div class="cert-footer">
                    <div>
                        <div>Issued: April 2025</div>
                        <div class="cert-verify">Verify at quizmind.ai/cert/QM-29471</div>
                    </div>
                    <div class="cert-seal">🏆</div>
                    <div style="text-align:right">
                        <div style="font-size:13px;font-weight:700;color:var(--text0)">QuizMind AI</div>
                        <div style="font-size:11px;color:var(--text2)">Powered by Groq AI</div>
                    </div>
                </div>
            </div>
            <div class="grid-3" style="max-width:680px;margin:28px auto 0">
                @foreach ([['🥇', 'Tournament Winner', '1st, 2nd &amp; 3rd place finishers in official battles'], ['⭐', 'Skill Milestone', 'Cross 90% accuracy in any subject to earn mastery'], ['🔗', 'Share Anywhere', 'LinkedIn, WhatsApp, Instagram — one-click verified share']] as $i => $c)
                    <div class="feature-card reveal reveal-delay-{{ $i + 1 }}" style="text-align:center">
                        <span class="feature-icon">{{ $c[0] }}</span>
                        <h3 class="feature-title">{{ $c[1] }}</h3>
                        <p class="feature-desc">{!! $c[2] !!}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════ --}}
    <footer>
        © {{ date('Y') }} QuizMind AI — Built for students who want to win.
    </footer>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script>
        /* ═══════════════════════════════════════════
       Three.js Background
    ═══════════════════════════════════════════ */
        (function initThree() {
            var el = document.getElementById('three-bg');
            if (!el || !window.THREE) return;

            var W = window.innerWidth,
                H = el.parentElement.clientHeight || window.innerHeight;
            var renderer = new THREE.WebGLRenderer({
                antialias: true,
                alpha: true
            });
            renderer.setSize(W, H);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
            renderer.setClearColor(0x000000, 0);
            el.appendChild(renderer.domElement);
            Object.assign(el.style, {
                position: 'absolute',
                inset: '0',
                zIndex: '0',
                pointerEvents: 'none'
            });
            Object.assign(renderer.domElement.style, {
                width: '100%',
                height: '100%'
            });

            var scene = new THREE.Scene();
            var camera = new THREE.PerspectiveCamera(60, W / H, 0.1, 100);
            camera.position.z = 5;

            /* Particles */
            var geo = new THREE.BufferGeometry();
            var count = 700,
                pos = new Float32Array(count * 3);
            for (var i = 0; i < count; i++) {
                pos[i * 3] = (Math.random() - 0.5) * 22;
                pos[i * 3 + 1] = (Math.random() - 0.5) * 22;
                pos[i * 3 + 2] = (Math.random() - 0.5) * 22;
            }
            geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
            var pMat = new THREE.PointsMaterial({
                color: 0x7C5CFC,
                size: 0.04,
                transparent: true,
                opacity: 0.7
            });
            var particles = new THREE.Points(geo, pMat);
            scene.add(particles);

            /* Torus 1 */
            var t1 = new THREE.Mesh(
                new THREE.TorusGeometry(1.3, 0.25, 16, 60),
                new THREE.MeshBasicMaterial({
                    color: 0x7C5CFC,
                    wireframe: true,
                    transparent: true,
                    opacity: 0.15
                })
            );
            t1.position.set(3, -0.5, 0);
            scene.add(t1);

            /* Torus 2 */
            var t2 = new THREE.Mesh(
                new THREE.TorusGeometry(0.9, 0.15, 16, 40),
                new THREE.MeshBasicMaterial({
                    color: 0x00D4FF,
                    wireframe: true,
                    transparent: true,
                    opacity: 0.12
                })
            );
            t2.position.set(-3.5, 1, 0);
            scene.add(t2);

            /* Icosahedron */
            var ico = new THREE.Mesh(
                new THREE.IcosahedronGeometry(0.65, 1),
                new THREE.MeshBasicMaterial({
                    color: 0x00D4FF,
                    wireframe: true,
                    transparent: true,
                    opacity: 0.1
                })
            );
            ico.position.set(0, -2.5, -2);
            scene.add(ico);

            /* Octahedron */
            var oct = new THREE.Mesh(
                new THREE.OctahedronGeometry(0.5, 0),
                new THREE.MeshBasicMaterial({
                    color: 0x9d7fff,
                    wireframe: true,
                    transparent: true,
                    opacity: 0.1
                })
            );
            oct.position.set(-1.5, 2, -1);
            scene.add(oct);

            var mouseX = 0,
                mouseY = 0;
            document.addEventListener('mousemove', function(e) {
                mouseX = (e.clientX / window.innerWidth - 0.5) * 0.3;
                mouseY = (e.clientY / window.innerHeight - 0.5) * 0.3;
            });

            var frame;

            function animate() {
                frame = requestAnimationFrame(animate);
                var t = Date.now() * 0.0005;
                particles.rotation.y = t * 0.1;
                particles.rotation.x = t * 0.05;
                t1.rotation.x = t;
                t1.rotation.y = t * 0.7;
                t2.rotation.x = -t * 0.8;
                t2.rotation.z = t * 0.5;
                ico.rotation.x = t * 0.4;
                ico.rotation.y = t * 0.6;
                oct.rotation.y = t * 0.5;
                oct.rotation.z = t * 0.3;
                camera.position.x += (mouseX - camera.position.x) * 0.05;
                camera.position.y += (-mouseY - camera.position.y) * 0.05;
                camera.lookAt(scene.position);
                renderer.render(scene, camera);
            }
            animate();

            window.addEventListener('resize', function() {
                var nW = window.innerWidth,
                    nH = el.parentElement.clientHeight || window.innerHeight;
                camera.aspect = nW / nH;
                camera.updateProjectionMatrix();
                renderer.setSize(nW, nH);
            });

            window.addEventListener('beforeunload', function() {
                cancelAnimationFrame(frame);
                renderer.dispose();
            });
        })();


        /* ═══════════════════════════════════════════
           Scroll Reveal
        ═══════════════════════════════════════════ */
        (function initReveal() {
            var els = document.querySelectorAll('.reveal');
            if (!els.length || !('IntersectionObserver' in window)) {
                els.forEach(function(el) {
                    el.classList.add('visible');
                });
                return;
            }
            var obs = new IntersectionObserver(function(entries) {
                entries.forEach(function(e) {
                    if (e.isIntersecting) {
                        e.target.classList.add('visible');
                        obs.unobserve(e.target);
                    }
                });
            }, {
                threshold: 0.1
            });
            els.forEach(function(el) {
                obs.observe(el);
            });
        })();


        /* ═══════════════════════════════════════════
           PDF Demo Animation
        ═══════════════════════════════════════════ */
        (function initPdfDemo() {
            var box = document.getElementById('pdfDemo');
            var bar = document.getElementById('pdfBar');
            var suc = document.getElementById('pdfSuccess');
            if (!box || !bar || !suc) return;
            var played = false;
            var obs = new IntersectionObserver(function(entries) {
                if (entries[0].isIntersecting && !played) {
                    played = true;
                    setTimeout(function() {
                        bar.style.width = '85%';
                    }, 200);
                    setTimeout(function() {
                        suc.style.opacity = '1';
                    }, 1900);
                    obs.disconnect();
                }
            }, {
                threshold: 0.4
            });
            obs.observe(box);
        })();


        /* ═══════════════════════════════════════════
           Source Card Animate (click)
        ═══════════════════════════════════════════ */
        function animateSource(card, key) {
            var track = card.querySelector('.prog-track');
            var bar = card.querySelector('.prog-bar');
            var desc = card.querySelector('.source-d');
            if (!track || !bar || !desc) return;
            var orig = desc.textContent;
            track.style.display = 'block';
            setTimeout(function() {
                bar.style.width = '100%';
            }, 80);
            var msgs = {
                pdf: '✅ 12 MCQs generated from PDF!',
                img: '✅ 8 MCQs extracted from image!',
                chat: '✅ 10 MCQs from chat session!'
            };
            setTimeout(function() {
                desc.textContent = msgs[key] || '✅ Done!';
                desc.style.color = '#00dc82';
            }, 1700);
            setTimeout(function() {
                bar.style.transition = 'none';
                bar.style.width = '0';
                setTimeout(function() {
                    bar.style.transition = 'width 1.5s ease';
                    track.style.display = 'none';
                }, 50);
                desc.textContent = orig;
                desc.style.color = '';
            }, 4000);
        }
    </script>
@endsection
