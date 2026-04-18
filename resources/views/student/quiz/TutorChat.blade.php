{{-- resources/views/student/quiz/tutorchat.blade.php --}}

@extends('student.layout.master')

@section('title', 'AI Study Tutor')

@section('content')

    <style>
        :root {
            --tc-bg: #0d0f1a;
            --tc-surface: #13162a;
            --tc-surface2: #1a1e36;
            --tc-border: rgba(255, 255, 255, .07);
            --tc-border2: rgba(255, 255, 255, .12);
            --tc-purple: #7c5cfc;
            --tc-cyan: #00d4ff;
            --tc-green: #00e396;
            --tc-red: #ff4d6a;
            --tc-gold: #f5a623;
            --tc-text: #e8eaf6;
            --tc-muted: #7a7f9e;
            --tc-grad: linear-gradient(135deg, #7c5cfc, #00d4ff);
            --tc-radius: 14px;
            --tc-radius-sm: 8px;
            --tc-fh: 'Space Grotesk', sans-serif;
            --tc-fb: 'Inter', sans-serif;
            --sidebar-w: 300px;
        }

        .tutor-shell {
            display: grid;
            grid-template-columns: var(--sidebar-w) 1fr;
            gap: 0;
            height: calc(100vh - 80px);
            min-height: 600px;
            border-radius: var(--tc-radius);
            overflow: hidden;
            border: 1px solid var(--tc-border2);
            background: var(--tc-surface);
        }

        .tc-sidebar {
            background: var(--tc-bg);
            border-right: 1px solid var(--tc-border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .tc-sidebar-head {
            padding: 18px 16px 14px;
            border-bottom: 1px solid var(--tc-border);
            flex-shrink: 0;
        }

        .tc-sidebar-head h2 {
            font-family: var(--tc-fh, sans-serif);
            font-size: 1rem;
            font-weight: 800;
            color: var(--tc-text);
            margin: 0 0 12px;
        }

        .btn-new-chat {
            width: 100%;
            padding: 9px 14px;
            background: var(--tc-grad);
            border: none;
            border-radius: var(--tc-radius-sm);
            color: #fff;
            font-weight: 700;
            font-size: .82rem;
            cursor: pointer;
            transition: opacity .2s, transform .15s;
            display: flex;
            align-items: center;
            gap: 7px;
            justify-content: center;
        }

        .btn-new-chat:hover {
            opacity: .88;
            transform: translateY(-1px);
        }

        .tc-subject-row {
            padding: 10px 16px;
            border-bottom: 1px solid var(--tc-border);
            flex-shrink: 0;
        }

        .tc-subject-row label {
            font-size: .7rem;
            color: var(--tc-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            display: block;
            margin-bottom: 5px;
        }

        .tc-subject-select {
            width: 100%;
            background: var(--tc-surface);
            border: 1px solid var(--tc-border2);
            border-radius: var(--tc-radius-sm);
            color: var(--tc-text);
            font-size: .82rem;
            padding: 7px 10px;
            outline: none;
            cursor: pointer;
        }

        .tc-subject-select:focus {
            border-color: var(--tc-purple);
        }

        .tc-sessions {
            flex: 1;
            overflow-y: auto;
            padding: 8px;
        }

        .tc-sessions::-webkit-scrollbar {
            width: 4px;
        }

        .tc-sessions::-webkit-scrollbar-track {
            background: transparent;
        }

        .tc-sessions::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .1);
            border-radius: 4px;
        }

        .tc-session-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: var(--tc-radius-sm);
            cursor: pointer;
            transition: background .15s;
            position: relative;
            border: 1px solid transparent;
            margin-bottom: 2px;
        }

        .tc-session-item:hover {
            background: var(--tc-surface2);
        }

        .tc-session-item.active {
            background: rgba(124, 92, 252, .15);
            border-color: rgba(124, 92, 252, .25);
        }

        .tc-session-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--tc-surface2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            flex-shrink: 0;
        }

        .tc-session-info {
            flex: 1;
            min-width: 0;
        }

        .tc-session-title {
            font-size: .8rem;
            font-weight: 600;
            color: var(--tc-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tc-session-meta {
            font-size: .68rem;
            color: var(--tc-muted);
            margin-top: 1px;
        }

        /* Action buttons column (delete + play quiz) */
        .tc-session-actions {
            display: flex;
            flex-direction: column;
            gap: 3px;
            flex-shrink: 0;
            opacity: 0;
            transition: opacity .15s;
        }

        .tc-session-item:hover .tc-session-actions {
            opacity: 1;
        }

        .tc-session-item.active .tc-session-actions {
            opacity: 1;
        }

        .tc-sess-btn {
            background: none;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            padding: 3px 6px;
            font-size: .7rem;
            font-weight: 700;
            line-height: 1;
            transition: background .15s, color .15s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .tc-sess-btn.del {
            color: var(--tc-muted);
        }

        .tc-sess-btn.del:hover {
            color: var(--tc-red);
            background: rgba(255, 77, 106, .1);
        }

        .tc-sess-btn.play {
            color: var(--tc-green);
            background: rgba(0, 227, 150, .08);
            font-size: .65rem;
            padding: 3px 5px;
        }

        .tc-sess-btn.play:hover {
            background: rgba(0, 227, 150, .18);
        }

        .tc-sessions-empty {
            text-align: center;
            padding: 40px 16px;
            color: var(--tc-muted);
            font-size: .8rem;
            line-height: 1.6;
        }

        /* Main chat */
        .tc-main {
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }

        .tc-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--tc-border);
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            background: var(--tc-surface);
        }

        .tc-header-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--tc-grad);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .tc-header-info {
            flex: 1;
        }

        .tc-header-title {
            font-family: var(--tc-fh, sans-serif);
            font-size: .95rem;
            font-weight: 800;
            color: var(--tc-text);
        }

        .tc-header-sub {
            font-size: .74rem;
            color: var(--tc-muted);
            margin-top: 1px;
        }

        .tc-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--tc-green);
            box-shadow: 0 0 6px var(--tc-green);
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .4;
            }
        }

        .tc-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px 20px 10px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            scroll-behavior: smooth;
        }

        .tc-messages::-webkit-scrollbar {
            width: 4px;
        }

        .tc-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .tc-messages::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .1);
            border-radius: 4px;
        }

        .tc-welcome {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 24px;
            gap: 16px;
        }

        .tc-welcome-icon {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: var(--tc-grad);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            animation: float-icon 3s ease-in-out infinite;
        }

        @keyframes float-icon {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        .tc-welcome h3 {
            font-family: var(--tc-fh, sans-serif);
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--tc-text);
            margin: 0;
        }

        .tc-welcome p {
            font-size: .85rem;
            color: var(--tc-muted);
            line-height: 1.6;
            max-width: 380px;
            margin: 0;
        }

        .tc-starters {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            width: 100%;
            max-width: 480px;
            margin-top: 8px;
        }

        .tc-starter-btn {
            background: var(--tc-surface2);
            border: 1px solid var(--tc-border2);
            border-radius: var(--tc-radius-sm);
            color: var(--tc-text);
            font-size: .78rem;
            padding: 10px 12px;
            cursor: pointer;
            text-align: left;
            transition: border-color .2s, background .2s;
            line-height: 1.4;
        }

        .tc-starter-btn:hover {
            border-color: var(--tc-purple);
            background: rgba(124, 92, 252, .1);
        }

        .tc-msg {
            display: flex;
            gap: 10px;
            animation: msg-in .25s ease-out;
        }

        @keyframes msg-in {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .tc-msg.user {
            flex-direction: row-reverse;
        }

        .tc-msg-avatar {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .tc-msg.user .tc-msg-avatar {
            background: var(--tc-grad);
        }

        .tc-msg.ai .tc-msg-avatar {
            background: var(--tc-surface2);
            border: 1px solid var(--tc-border2);
        }

        .tc-msg-bubble {
            max-width: 72%;
            border-radius: 14px;
            padding: 11px 15px;
            font-size: .85rem;
            line-height: 1.65;
        }

        .tc-msg.user .tc-msg-bubble {
            background: var(--tc-grad);
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        .tc-msg.ai .tc-msg-bubble {
            background: var(--tc-surface2);
            border: 1px solid var(--tc-border);
            color: var(--tc-text);
            border-bottom-left-radius: 4px;
        }

        .tc-msg.ai .tc-msg-bubble strong {
            color: #fff;
        }

        .tc-msg.ai .tc-msg-bubble em {
            color: var(--tc-cyan);
            font-style: normal;
        }

        .tc-msg.ai .tc-msg-bubble code {
            background: rgba(0, 0, 0, .3);
            border: 1px solid var(--tc-border2);
            border-radius: 5px;
            padding: 1px 6px;
            font-family: 'Fira Code', monospace;
            font-size: .8rem;
            color: var(--tc-cyan);
        }

        .tc-msg.ai .tc-msg-bubble pre {
            background: rgba(0, 0, 0, .3);
            border: 1px solid var(--tc-border2);
            border-radius: 8px;
            padding: 12px;
            overflow-x: auto;
            margin: 8px 0;
            font-family: 'Fira Code', monospace;
            font-size: .78rem;
            line-height: 1.5;
            color: var(--tc-cyan);
        }

        .tc-msg.ai .tc-msg-bubble ul,
        .tc-msg.ai .tc-msg-bubble ol {
            padding-left: 18px;
            margin: 6px 0;
        }

        .tc-msg.ai .tc-msg-bubble li {
            margin: 3px 0;
        }

        .tc-msg.ai .tc-msg-bubble p {
            margin: 0 0 6px;
        }

        .tc-msg.ai .tc-msg-bubble p:last-child {
            margin: 0;
        }

        .tc-msg-time {
            font-size: .66rem;
            color: var(--tc-muted);
            margin-top: 4px;
            text-align: right;
        }

        .tc-msg.ai .tc-msg-time {
            text-align: left;
        }

        .tc-typing {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .tc-typing-dots {
            background: var(--tc-surface2);
            border: 1px solid var(--tc-border);
            border-radius: 14px;
            border-bottom-left-radius: 4px;
            padding: 12px 16px;
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .tc-typing-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--tc-muted);
            animation: typing-bounce .9s ease-in-out infinite;
        }

        .tc-typing-dot:nth-child(2) {
            animation-delay: .15s;
        }

        .tc-typing-dot:nth-child(3) {
            animation-delay: .3s;
        }

        @keyframes typing-bounce {

            0%,
            60%,
            100% {
                transform: translateY(0);
            }

            30% {
                transform: translateY(-8px);
                background: var(--tc-purple);
            }
        }

        .tc-input-area {
            padding: 14px 20px 16px;
            border-top: 1px solid var(--tc-border);
            flex-shrink: 0;
            background: var(--tc-surface);
        }

        .tc-input-wrap {
            display: flex;
            gap: 10px;
            align-items: flex-end;
            background: var(--tc-surface2);
            border: 1px solid var(--tc-border2);
            border-radius: 14px;
            padding: 10px 12px 10px 16px;
            transition: border-color .2s;
        }

        .tc-input-wrap:focus-within {
            border-color: var(--tc-purple);
        }

        #tcInput {
            flex: 1;
            background: none;
            border: none;
            outline: none;
            color: var(--tc-text);
            font-size: .88rem;
            line-height: 1.5;
            resize: none;
            min-height: 22px;
            max-height: 130px;
            font-family: var(--tc-fb, sans-serif);
        }

        #tcInput::placeholder {
            color: var(--tc-muted);
        }

        .tc-send-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--tc-grad);
            border: none;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: opacity .2s, transform .15s;
        }

        .tc-send-btn:hover:not(:disabled) {
            opacity: .85;
            transform: scale(1.05);
        }

        .tc-send-btn:disabled {
            opacity: .4;
            cursor: not-allowed;
        }

        .tc-input-hint {
            font-size: .7rem;
            color: var(--tc-muted);
            text-align: center;
            margin-top: 8px;
        }

        /* Quiz overlay */
        #tutorQuizOverlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: var(--tc-bg);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .tq-opt-btn {
            background: var(--tc-surface2);
            border: 1px solid var(--tc-border2);
            border-radius: 12px;
            padding: 13px 18px;
            color: var(--tc-text);
            font-size: .86rem;
            text-align: left;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: border-color .15s, background .15s;
            width: 100%;
        }

        .tq-opt-btn:hover:not(:disabled) {
            border-color: var(--tc-purple);
            background: rgba(124, 92, 252, .08);
        }

        .tq-opt-btn:disabled {
            cursor: default;
        }

        .tq-opt-btn.correct {
            background: rgba(0, 227, 150, .1) !important;
            border-color: #00e396 !important;
        }

        .tq-opt-btn.wrong {
            background: rgba(255, 77, 106, .08) !important;
            border-color: #ff4d6a !important;
        }

        .tq-letter {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: rgba(124, 92, 252, .15);
            color: #a78bfa;
            font-weight: 700;
            font-size: .8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background .15s, color .15s;
        }

        .tq-opt-btn.correct .tq-letter {
            background: rgba(0, 227, 150, .2);
            color: #00e396;
        }

        .tq-opt-btn.wrong .tq-letter {
            background: rgba(255, 77, 106, .15);
            color: #ff4d6a;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .tutor-shell {
                grid-template-columns: 1fr;
                height: auto;
                min-height: calc(100vh - 80px);
            }

            .tc-sidebar {
                height: auto;
                max-height: 240px;
                border-right: none;
                border-bottom: 1px solid var(--tc-border);
            }

            .tc-sessions {
                max-height: 120px;
            }

            .tc-main {
                min-height: 500px;
            }

            .tc-messages {
                padding: 14px 14px 8px;
            }

            .tc-msg-bubble {
                max-width: 88%;
            }

            .tc-starters {
                grid-template-columns: 1fr;
            }
        }

        .hidden {
            display: none !important;
        }

        .tc-chips {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            padding: 0 20px 10px;
            flex-shrink: 0;
        }

        .tc-chip {
            background: var(--tc-surface2);
            border: 1px solid var(--tc-border2);
            border-radius: 20px;
            padding: 4px 11px;
            font-size: .74rem;
            color: var(--tc-muted);
            cursor: pointer;
            transition: border-color .2s, color .2s;
            white-space: nowrap;
        }

        .tc-chip:hover {
            border-color: var(--tc-purple);
            color: var(--tc-purple);
        }
    </style>

    {{-- Page header --}}
    <div class="flex mb-20 anim-fade" style="justify-content:space-between;flex-wrap:wrap;gap:10px;align-items:center">
        <div>
            <h1 style="font-family:var(--fh,sans-serif);font-size:1.6rem;font-weight:800;margin-bottom:3px">
                🎓 AI Study Tutor
            </h1>
            <p style="font-size:.84rem;color:var(--muted,#7a7f9e)">
                Ask anything — Math, Science, History, Coding, and more.
                🧠 Turn your chats into MCQs and ▶ play interactive quizzes.
            </p>
        </div>
        <a href="{{ route('student.quiz.index') }}" class="btn btn-ghost btn-sm" style="text-decoration:none">
            ← Back to Quiz
        </a>
    </div>

    <div class="tutor-shell">

        {{-- Sidebar --}}
        <div class="tc-sidebar">
            <div class="tc-sidebar-head">
                <h2>💬 Chats</h2>
                <button class="btn-new-chat" onclick="newChat()">✦ New Chat</button>
            </div>

            <div class="tc-subject-row">
                <label>Subject</label>
                <select class="tc-subject-select" id="subjectPicker" onchange="updateSubject()">
                    <option value="General">General</option>
                    <option value="Mathematics">Mathematics</option>
                    <option value="Physics">Physics</option>
                    <option value="Chemistry">Chemistry</option>
                    <option value="Biology">Biology</option>
                    <option value="History">History</option>
                    <option value="Geography">Geography</option>
                    <option value="Computer Science">Computer Science</option>
                    <option value="English">English</option>
                    <option value="Economics">Economics</option>
                    <option value="Political Science">Political Science</option>
                    <option value="Psychology">Psychology</option>
                </select>
            </div>

            <div class="tc-sessions" id="sessionsList">
                @if ($chatSessions->count())
                    @foreach ($chatSessions as $sess)
                        <div class="tc-session-item" id="sess-{{ $sess->id }}"
                            onclick="loadSession({{ $sess->id }})">
                            <div class="tc-session-icon">
                                {{ match ($sess->subject ?? 'General') {
                                    'Mathematics' => '📐',
                                    'Physics' => '⚛️',
                                    'Chemistry' => '🧪',
                                    'Biology' => '🧬',
                                    'History' => '📜',
                                    'Geography' => '🌍',
                                    'Computer Science' => '💻',
                                    'English' => '📖',
                                    'Economics' => '📈',
                                    default => '💬',
                                } }}
                            </div>
                            <div class="tc-session-info">
                                <div class="tc-session-title">{{ $sess->title }}</div>
                                <div class="tc-session-meta">{{ ucfirst($sess->subject ?? 'General') }} ·
                                    {{ $sess->updated_at->diffForHumans() }}</div>
                                <button class="tc-sess-btn play"
                                    onclick="event.stopPropagation(); playQuizFromSession({{ $sess->id }})"
                                    title="Convert chat to MCQ">
                                    🧠 Convert Chat To MCQ
                                </button>
                            </div>
                            <div class="tc-session-actions">

                                <button class="tc-sess-btn del"
                                    onclick="event.stopPropagation(); deleteSession({{ $sess->id }})" title="Delete">✕
                                    Del</button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="tc-sessions-empty" id="sessionsEmpty">
                        <div style="font-size:2rem;margin-bottom:8px">💬</div>
                        <div>No chats yet</div>
                        <div style="margin-top:4px">Start asking questions!</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Main Chat --}}
        <div class="tc-main">

            <div class="tc-header">
                <div class="tc-header-icon">🎓</div>
                <div class="tc-header-info">
                    <div class="tc-header-title" id="chatTitle">AI Study Tutor</div>
                    <div class="tc-header-sub" id="chatSubject">Select a subject and start asking</div>
                </div>
                <div class="tc-status-dot"></div>
            </div>

            <div class="tc-messages" id="messagesArea">
                <div class="tc-welcome" id="welcomeScreen">
                    <div class="tc-welcome-icon">🤖</div>
                    <h3>Hi {{ Auth::user()->name ?? 'Student' }}! 👋</h3>
                    <p>I'm your AI Study Tutor. Ask me anything — concepts, problems, homework, exam prep. I teach like a
                        real teacher!</p>
                    <div class="tc-starters" id="starterBtns">
                        <button class="tc-starter-btn"
                            onclick="sendStarter('Explain Newton\'s three laws of motion with examples')">
                            ⚛️ Newton's laws of motion
                        </button>
                        <button class="tc-starter-btn"
                            onclick="sendStarter('How do I solve quadratic equations? Show step by step')">
                            📐 Solve quadratic equations
                        </button>
                        <button class="tc-starter-btn" onclick="sendStarter('What were the main causes of World War 1?')">
                            📜 Causes of World War 1
                        </button>
                        <button class="tc-starter-btn" onclick="sendStarter('Explain photosynthesis in simple terms')">
                            🧬 Explain photosynthesis
                        </button>
                        <button class="tc-starter-btn"
                            onclick="sendStarter('How do I write a Python function? Give an example')">
                            💻 Python functions example
                        </button>
                        <button class="tc-starter-btn"
                            onclick="sendStarter('What is the difference between acids and bases?')">
                            🧪 Acids vs bases
                        </button>
                    </div>
                </div>
            </div>

            <div class="tc-chips hidden" id="quickChips">
                <span class="tc-chip" onclick="sendStarter('Give me a practice question on this topic')">📝 Practice
                    Q</span>
                <span class="tc-chip" onclick="sendStarter('Can you summarize the key points?')">📌 Key points</span>
                <span class="tc-chip" onclick="sendStarter('Explain this more simply')">🔤 Simplify</span>
                <span class="tc-chip" onclick="sendStarter('Give me a real-world example')">🌍 Example</span>
                <span class="tc-chip" onclick="sendStarter('What are common mistakes to avoid?')">⚠️ Mistakes</span>
            </div>

            <div class="tc-input-area">
                <div class="tc-input-wrap">
                    <textarea id="tcInput" placeholder="Ask anything — Math, Science, History, Coding…" rows="1"
                        onkeydown="handleInputKey(event)" oninput="autoResize(this)"></textarea>
                    <button class="tc-send-btn" id="sendBtn" onclick="sendMessage()" title="Send (Enter)">
                        ➤
                    </button>
                </div>
                <p class="tc-input-hint">Press Enter to send · Shift+Enter for new line</p>
            </div>

        </div>

    </div>

    <script>
        // ── Config ──────────────────────────────────────────────────────────────────
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const TC_ROUTES = {
            chat: '{{ route('student.quiz.tutor.chat') }}',
            genQuiz: '{{ route('student.quiz.tutor.gen_quiz') }}',
            newSession: '{{ route('student.quiz.tutor.new') }}',
            sessions: '{{ route('student.quiz.tutor.sessions') }}',
            session: '{{ url('student/quiz/tutor/session') }}',
            deleteSession: '{{ url('student/quiz/tutor/session') }}',
        };

        const SUBJ_ICONS = {
            'Mathematics': '📐',
            'Physics': '⚛️',
            'Chemistry': '🧪',
            'Biology': '🧬',
            'History': '📜',
            'Geography': '🌍',
            'Computer Science': '💻',
            'English': '📖',
            'Economics': '📈',
            'Political Science': '🏛️',
            'Psychology': '🧠',
            'General': '💬'
        };

        // ── State ───────────────────────────────────────────────────────────────────
        let currentSessionId = null;
        let currentSubject = 'General';
        let isLoading = false;
        let messageHistory = [];

        // ── Init ────────────────────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            autoResize(document.getElementById('tcInput'));
        });

        // ── Subject picker ──────────────────────────────────────────────────────────
        function updateSubject() {
            currentSubject = document.getElementById('subjectPicker').value;
            const icon = SUBJ_ICONS[currentSubject] || '💬';
            document.getElementById('chatSubject').textContent = icon + ' ' + currentSubject;
        }

        // ── New chat ─────────────────────────────────────────────────────────────────
        function newChat() {
            currentSessionId = null;
            messageHistory = [];

            const area = document.getElementById('messagesArea');
            area.innerHTML = '';

            const welcome = document.createElement('div');
            welcome.className = 'tc-welcome';
            welcome.id = 'welcomeScreen';
            welcome.innerHTML = `
        <div class="tc-welcome-icon">🤖</div>
        <h3>Start a new chat 💬</h3>
        <p>Ask me anything — Math, Science, History, Coding, and more!</p>
        <div class="tc-starters">
            <button class="tc-starter-btn" onclick="sendStarter('Explain Newton\\'s three laws of motion with examples')">⚛️ Newton's laws</button>
            <button class="tc-starter-btn" onclick="sendStarter('How do I solve quadratic equations?')">📐 Quadratic equations</button>
            <button class="tc-starter-btn" onclick="sendStarter('What caused World War 1?')">📜 Causes of WW1</button>
            <button class="tc-starter-btn" onclick="sendStarter('Explain photosynthesis simply')">🧬 Photosynthesis</button>
        </div>`;
            area.appendChild(welcome);

            document.getElementById('chatTitle').textContent = 'AI Study Tutor';
            document.getElementById('quickChips').classList.add('hidden');
            document.querySelectorAll('.tc-session-item').forEach(el => el.classList.remove('active'));
            document.getElementById('tcInput').focus();
        }

        // ── Load session ─────────────────────────────────────────────────────────────
        async function loadSession(id) {
            if (isLoading) return;

            document.querySelectorAll('.tc-session-item').forEach(el => el.classList.remove('active'));
            const item = document.getElementById(`sess-${id}`);
            if (item) item.classList.add('active');

            try {
                const res = await fetch(`${TC_ROUTES.session}/${id}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();

                if (!data.success) {
                    tcToast('error', 'Failed to load chat');
                    return;
                }

                currentSessionId = id;
                messageHistory = data.session.messages || [];

                const subPicker = document.getElementById('subjectPicker');
                if (data.session.subject) {
                    subPicker.value = data.session.subject;
                    currentSubject = data.session.subject;
                    updateSubject();
                }

                document.getElementById('chatTitle').textContent = data.session.title || 'Chat';

                const area = document.getElementById('messagesArea');
                area.innerHTML = '';

                if (messageHistory.length === 0) {
                    newChat();
                    return;
                }

                document.getElementById('quickChips').classList.remove('hidden');

                messageHistory.forEach(msg => {
                    appendMessage(
                        msg.role === 'user' ? 'user' : 'ai',
                        msg.content,
                        msg.time ? new Date(msg.time).toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit'
                        }) : ''
                    );
                });

                scrollToBottom();
            } catch (e) {
                tcToast('error', 'Network error');
            }
        }

        // ── Delete session ───────────────────────────────────────────────────────────
        async function deleteSession(id) {
            if (!confirm('Delete this chat?')) return;
            try {
                await fetch(`${TC_ROUTES.deleteSession}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                document.getElementById(`sess-${id}`)?.remove();

                if (document.querySelectorAll('.tc-session-item').length === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'tc-sessions-empty';
                    empty.id = 'sessionsEmpty';
                    empty.innerHTML =
                        '<div style="font-size:2rem;margin-bottom:8px">💬</div><div>No chats yet</div><div style="margin-top:4px">Start asking questions!</div>';
                    document.getElementById('sessionsList').appendChild(empty);
                }

                if (currentSessionId === id) newChat();
                tcToast('success', 'Chat deleted');
            } catch {
                tcToast('error', 'Delete failed');
            }
        }

        // ── Starter shortcut ─────────────────────────────────────────────────────────
        function sendStarter(text) {
            document.getElementById('tcInput').value = text;
            sendMessage();
        }

        // ── Send message ──────────────────────────────────────────────────────────────
        async function sendMessage() {
            if (isLoading) return;

            const input = document.getElementById('tcInput');
            const text = input.value.trim();
            if (!text) return;

            const welcome = document.getElementById('welcomeScreen');
            if (welcome) welcome.remove();
            document.getElementById('quickChips').classList.remove('hidden');

            const now = new Date().toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
            appendMessage('user', text, now);

            input.value = '';
            autoResize(input);
            scrollToBottom();

            showTyping();
            isLoading = true;
            document.getElementById('sendBtn').disabled = true;

            try {
                const payload = {
                    message: text,
                    subject: currentSubject
                };
                if (currentSessionId) payload.session_id = currentSessionId;

                const res = await fetch(TC_ROUTES.chat, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();
                hideTyping();

                if (data.success) {
                    currentSessionId = data.session_id;

                    const replyTime = new Date().toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    appendMessage('ai', data.response, replyTime);
                    scrollToBottom();

                    updateOrAddSidebarSession(data.session_id, text, currentSubject);
                } else {
                    appendMessage('ai', '⚠️ ' + (data.message || 'Something went wrong. Please try again.'), now);
                    scrollToBottom();
                }

            } catch (err) {
                hideTyping();
                appendMessage('ai', '⚠️ Network error. Please check your connection and try again.', now);
                scrollToBottom();
            }

            isLoading = false;
            document.getElementById('sendBtn').disabled = false;
            input.focus();
        }

        // ── Append message bubble ─────────────────────────────────────────────────────
        function appendMessage(role, content, time) {
            const area = document.getElementById('messagesArea');
            const wrap = document.createElement('div');
            wrap.className = `tc-msg ${role}`;

            const avatarEmoji = role === 'user' ? '🧑' : '🤖';
            const bubbleHtml = role === 'ai' ? renderMarkdown(content) : escHtml(content);

            wrap.innerHTML = `
        <div class="tc-msg-avatar">${avatarEmoji}</div>
        <div>
            <div class="tc-msg-bubble">${bubbleHtml}</div>
            ${time ? `<div class="tc-msg-time">${time}</div>` : ''}
        </div>`;
            area.appendChild(wrap);
        }

        // ── Typing indicator ─────────────────────────────────────────────────────────
        function showTyping() {
            const area = document.getElementById('messagesArea');
            const el = document.createElement('div');
            el.id = 'typingIndicator';
            el.className = 'tc-typing';
            el.innerHTML = `
        <div class="tc-msg-avatar" style="background:var(--tc-surface2);border:1px solid var(--tc-border2)">🤖</div>
        <div class="tc-typing-dots">
            <div class="tc-typing-dot"></div>
            <div class="tc-typing-dot"></div>
            <div class="tc-typing-dot"></div>
        </div>`;
            area.appendChild(el);
            scrollToBottom();
        }

        function hideTyping() {
            document.getElementById('typingIndicator')?.remove();
        }

        function scrollToBottom() {
            const area = document.getElementById('messagesArea');
            area.scrollTop = area.scrollHeight;
        }

        // ── Sidebar: update or add session ───────────────────────────────────────────
        function updateOrAddSidebarSession(id, firstMsg, subject) {
            const icon = SUBJ_ICONS[subject] || '💬';
            const title = firstMsg.length > 36 ? firstMsg.slice(0, 36) + '…' : firstMsg;

            document.getElementById('chatTitle').textContent = title;
            document.getElementById('sessionsEmpty')?.remove();

            let existing = document.getElementById(`sess-${id}`);
            if (existing) {
                existing.querySelector('.tc-session-title').textContent = title;
                existing.querySelector('.tc-session-meta').textContent = `${subject} · Just now`;
                document.querySelectorAll('.tc-session-item').forEach(s => s.classList.remove('active'));
                existing.classList.add('active');
                return;
            }

            const el = document.createElement('div');
            el.className = 'tc-session-item active';
            el.id = `sess-${id}`;
            el.onclick = () => loadSession(id);
            el.innerHTML = `
        <div class="tc-session-icon">${icon}</div>
        <div class="tc-session-info">
            <div class="tc-session-title">${escHtml(title)}</div>
            <div class="tc-session-meta">${escHtml(subject)} · Just now</div>
        </div>
        <div class="tc-session-actions">
            <button class="tc-sess-btn play"
                onclick="event.stopPropagation(); playQuizFromSession(${id})"
                title="Play Quiz from this chat">▶ Quiz</button>
            <button class="tc-sess-btn del"
                onclick="event.stopPropagation(); deleteSession(${id})"
                title="Delete">✕ Del</button>
        </div>`;

            const list = document.getElementById('sessionsList');
            list.insertBefore(el, list.firstChild);

            document.querySelectorAll('.tc-session-item').forEach(s => {
                if (s.id !== `sess-${id}`) s.classList.remove('active');
            });
        }

        // ── Play Quiz from chat session ───────────────────────────────────────────────
        // Uses a dedicated /tutor/gen-quiz route to avoid safety classifier interference
        async function playQuizFromSession(sessionId) {
            tcToast('info', '⏳ Generating quiz from your chat…');

            try {
                // If not current session, load messages first
                let messages = [];
                if (sessionId === currentSessionId) {
                    messages = messageHistory;
                } else {
                    const res = await fetch(`${TC_ROUTES.session}/${sessionId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    if (!data.success) {
                        tcToast('error', 'Could not load chat');
                        return;
                    }
                    messages = data.session.messages || [];
                }

                // Need at least 2 messages (1 user + 1 tutor)
                const validMessages = messages.filter(m => m.content && m.content.trim().length > 5);
                if (validMessages.length < 2) {
                    tcToast('error', 'Chat is too short. Have a longer lesson first!');
                    return;
                }

                // Get the subject for this session
                const sessEl = document.getElementById(`sess-${sessionId}`);
                const sessSubj = sessEl ?
                    (sessEl.querySelector('.tc-session-meta')?.textContent?.split('·')[0]?.trim() || 'General') :
                    currentSubject;

                // Call dedicated quiz generation route
                const res = await fetch(TC_ROUTES.genQuiz, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        session_id: sessionId,
                        messages: validMessages,
                        subject: sessSubj,
                    }),
                });

                const data = await res.json();

                if (!data.success) {
                    tcToast('error', data.message || 'Quiz generation failed');
                    return;
                }

                const questions = data.questions;
                if (!Array.isArray(questions) || questions.length === 0) {
                    tcToast('error', 'No questions could be generated. Try a longer chat!');
                    return;
                }

                tcToast('success', `✅ ${questions.length} questions ready! Launching quiz…`);
                setTimeout(() => launchTutorQuiz(questions), 600);

            } catch (err) {
                console.error(err);
                tcToast('error', 'Network error. Please try again.');
            }
        }

        // ── Inline Solo Quiz Player ───────────────────────────────────────────────────
        function launchTutorQuiz(qs) {
            const LETTERS = ['A', 'B', 'C', 'D'];
            let qIdx = 0;
            let score = 0;
            let answered = false;
            let timeLeft = 20;
            let timer = null;
            const log = [];

            // Remove any existing overlay
            document.getElementById('tutorQuizOverlay')?.remove();

            const overlay = document.createElement('div');
            overlay.id = 'tutorQuizOverlay';
            document.body.appendChild(overlay);
            document.body.style.overflow = 'hidden';

            function esc(s) {
                return String(s || '')
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function closeOverlay() {
                clearInterval(timer);
                document.getElementById('tutorQuizOverlay')?.remove();
                document.body.style.overflow = '';
            }

            function renderQ() {
                answered = false;
                const q = qs[qIdx];
                const progressPct = Math.round((qIdx / qs.length) * 100);

                overlay.innerHTML = `
        <div style="max-width:660px;margin:0 auto;padding:28px 20px;width:100%;box-sizing:border-box">

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
                <span style="background:rgba(124,92,252,.15);color:#a78bfa;padding:5px 14px;border-radius:20px;font-size:.78rem;font-weight:700">
                    Q${qIdx+1} / ${qs.length}
                </span>
                <button onclick="closeOverlayQuiz()"
                    style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:var(--tc-muted);cursor:pointer;padding:5px 14px;border-radius:8px;font-size:.8rem;font-weight:600">
                    ✕ Exit
                </button>
            </div>

            <div style="height:4px;background:rgba(255,255,255,.06);border-radius:4px;margin-bottom:22px;overflow:hidden">
                <div style="width:${progressPct}%;height:100%;background:linear-gradient(90deg,#7c5cfc,#00d4ff);border-radius:4px;transition:width .5s ease"></div>
            </div>

            <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px">
                <div id="tqTimer"
                    style="width:46px;height:46px;border-radius:50%;border:2px solid #7c5cfc;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.05rem;color:#a78bfa;flex-shrink:0;transition:border-color .3s,color .3s">
                    ${timeLeft}
                </div>
                <div style="flex:1;height:5px;background:rgba(255,255,255,.06);border-radius:4px;overflow:hidden">
                    <div id="tqTimerBar" style="width:${timeLeft/20*100}%;height:100%;background:linear-gradient(90deg,#7c5cfc,#00d4ff);border-radius:4px;transition:width 1s linear"></div>
                </div>
                <span style="background:rgba(245,166,35,.1);color:#f5a623;padding:4px 12px;border-radius:20px;font-size:.8rem;font-weight:700">
                    ⚡ ${score} pts
                </span>
            </div>

            <div style="background:var(--tc-surface2);border:1px solid var(--tc-border2);border-radius:14px;padding:20px 22px;margin-bottom:18px">
                <p style="font-size:.95rem;font-weight:600;line-height:1.7;color:var(--tc-text);margin:0">
                    ${esc(q.question)}
                </p>
            </div>

            <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px" id="tqOpts">
                ${q.options.map((opt, i) => `
                        <button class="tq-opt-btn" id="tqOpt${i}" onclick="tqHandleAnswer(${i})">
                            <span class="tq-letter">${LETTERS[i]}</span>
                            <span style="flex:1;text-align:left">${esc(opt)}</span>
                        </button>`).join('')}
            </div>

            <div id="tqFeedback"></div>
        </div>`;

                // Start timer fresh
                clearInterval(timer);
                timeLeft = 20;
                timer = setInterval(() => {
                    timeLeft--;
                    const tEl = document.getElementById('tqTimer');
                    const bEl = document.getElementById('tqTimerBar');
                    if (tEl) {
                        tEl.textContent = timeLeft;
                        const danger = timeLeft <= 5;
                        tEl.style.borderColor = danger ? '#ff4d6a' : '#7c5cfc';
                        tEl.style.color = danger ? '#ff4d6a' : '#a78bfa';
                    }
                    if (bEl) bEl.style.width = (timeLeft / 20 * 100) + '%';
                    if (timeLeft <= 0) {
                        clearInterval(timer);
                        tqHandleAnswer(-1);
                    }
                }, 1000);
            }

            // Exposed to inline onclick
            window.closeOverlayQuiz = closeOverlay;

            window.tqHandleAnswer = function(idx) {
                if (answered) return;
                clearInterval(timer);
                answered = true;

                const q = qs[qIdx];
                const correct = idx === q.answer;
                if (correct) score += 20;
                log.push({
                    idx,
                    correct,
                    q
                });

                // Style buttons
                for (let i = 0; i < q.options.length; i++) {
                    const btn = document.getElementById(`tqOpt${i}`);
                    if (!btn) continue;
                    btn.disabled = true;
                    if (i === q.answer) btn.classList.add('correct');
                    else if (i === idx) btn.classList.add('wrong');
                }

                // Show feedback
                const fb = document.getElementById('tqFeedback');
                if (fb) {
                    const borderColor = correct ? 'rgba(0,227,150,.25)' : 'rgba(255,77,106,.2)';
                    const bgColor = correct ? 'rgba(0,227,150,.06)' : 'rgba(255,77,106,.06)';
                    const labelColor = correct ? '#00e396' : '#ff4d6a';
                    const label = correct ? '✅ Correct! +20 pts' : (idx === -1 ? '⏰ Time\'s up!' : '❌ Wrong!');
                    const nextLabel = (qIdx + 1 >= qs.length) ? '🏆 See Results' : 'Next Question →';

                    fb.innerHTML = `
            <div style="background:${bgColor};border:1px solid ${borderColor};border-radius:12px;padding:16px 18px">
                <div style="font-weight:700;font-size:.88rem;color:${labelColor};margin-bottom:${q.explanation?'8px':'12px'}">${label}</div>
                ${q.explanation ? `<p style="font-size:.8rem;color:var(--tc-muted);line-height:1.6;margin:0 0 14px">${esc(q.explanation)}</p>` : ''}
                <button onclick="tqNextQuestion()"
                    style="background:linear-gradient(135deg,#7c5cfc,#00d4ff);border:none;color:#fff;padding:10px 24px;border-radius:10px;font-weight:700;font-size:.84rem;cursor:pointer">
                    ${nextLabel}
                </button>
            </div>`;
                }
            };

            window.tqNextQuestion = function() {
                if (qIdx + 1 >= qs.length) {
                    tqShowResults();
                } else {
                    qIdx++;
                    renderQ();
                }
            };

            function tqShowResults() {
                clearInterval(timer);
                const total = qs.length;
                const got = log.filter(r => r.correct).length;
                const acc = Math.round((got / total) * 100);
                const xp = got * 20 + (acc >= 80 ? 40 : 0);
                const emoji = acc >= 80 ? '🏆' : acc >= 60 ? '🎯' : '📚';
                const label = acc >= 80 ? 'Excellent!' : acc >= 60 ? 'Good Job!' : 'Keep Practicing!';

                overlay.innerHTML = `
        <div style="max-width:580px;margin:0 auto;padding:40px 20px;text-align:center;width:100%;box-sizing:border-box">

            <div style="font-size:3.5rem;margin-bottom:12px">${emoji}</div>
            <h1 style="font-family:var(--tc-fh,sans-serif);font-size:1.9rem;font-weight:800;color:var(--tc-text);margin:0 0 6px">${label}</h1>
            <p style="color:var(--tc-muted);font-size:.84rem;margin:0 0 28px">Quiz generated from your tutor chat</p>

            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:28px">
                ${[
                    ['Score',    `${got}/${total}`, '#7c5cfc'],
                    ['Accuracy', `${acc}%`,         '#00d4ff'],
                    ['XP',       `+${xp}`,          '#f5a623'],
                ].map(([lbl, val, clr]) => `
                        <div style="background:var(--tc-surface2);border:1px solid var(--tc-border2);border-radius:12px;padding:16px 22px;min-width:90px">
                            <div style="font-size:1.5rem;font-weight:800;color:${clr}">${val}</div>
                            <div style="font-size:.72rem;color:var(--tc-muted);margin-top:3px">${lbl}</div>
                        </div>`).join('')}
            </div>

            <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-bottom:32px">
                <button onclick="tqReplay()"
                    style="background:linear-gradient(135deg,#7c5cfc,#00d4ff);border:none;color:#fff;padding:11px 26px;border-radius:10px;font-weight:700;font-size:.86rem;cursor:pointer">
                    🔄 Play Again
                </button>
                <button onclick="closeOverlayQuiz()"
                    style="background:rgba(255,255,255,.06);border:1px solid var(--tc-border2);color:var(--tc-text);padding:11px 26px;border-radius:10px;font-weight:700;font-size:.86rem;cursor:pointer">
                    ← Back to Chat
                </button>
            </div>

            <div style="text-align:left">
                <div style="font-size:.82rem;font-weight:700;color:var(--tc-text);margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--tc-border)">
                    📋 Question Review
                </div>
                ${log.map((r, i) => `
                        <div style="display:flex;align-items:flex-start;gap:10px;padding:10px 0;border-bottom:1px solid var(--tc-border)">
                            <span style="flex-shrink:0;margin-top:1px">${r.correct ? '✅' : '❌'}</span>
                            <div style="flex:1;min-width:0">
                                <div style="font-size:.8rem;color:var(--tc-text);line-height:1.5;margin-bottom:2px">${esc(r.q?.question?.slice(0,80))}${(r.q?.question?.length||0)>80?'…':''}</div>
                                ${!r.correct && r.q?.options && r.q?.answer !== undefined
                                    ? `<div style="font-size:.72rem;color:var(--tc-green)">✓ ${esc(r.q.options[r.q.answer])}</div>` : ''}
                            </div>
                            <span style="font-size:.73rem;font-weight:700;color:${r.correct?'#00e396':'#ff4d6a'};flex-shrink:0">${r.correct?'+20 pts':'0 pts'}</span>
                        </div>`).join('')}
            </div>
        </div>`;

                window.tqReplay = function() {
                    qIdx = 0;
                    score = 0;
                    answered = false;
                    timeLeft = 20;
                    log.length = 0;
                    renderQ();
                };
            }

            // Start
            renderQ();
        }

        // ── Markdown renderer ─────────────────────────────────────────────────────────
        function renderMarkdown(text) {
            if (!text) return '';
            let html = escHtml(text);

            html = html.replace(/```([a-zA-Z]*)\n?([\s\S]*?)```/g, (_, lang, code) =>
                `<pre><code>${code.trim()}</code></pre>`);
            html = html.replace(/`([^`]+)`/g, '<code>$1</code>');
        html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');
        html = html.replace(/^###\s(.+)$/gm,
            '<strong style="font-size:.9rem;display:block;margin-top:10px;color:#fff">$1</strong>');
        html = html.replace(/^##\s(.+)$/gm,
            '<strong style="font-size:.92rem;display:block;margin-top:10px;color:#fff">$1</strong>');
        html = html.replace(/^\s*[-*]\s(.+)$/gm, '<li>$1</li>');
        html = html.replace(/(<li>[\s\S]*?<\/li>)/g, '<ul>$1</ul>');
        html = html.replace(/<\/ul>\s*<ul>/g, '');
        html = html.replace(/^\s*\d+\.\s(.+)$/gm, '<li>$1</li>');

        const parts = html.split(/(<pre>[\s\S]*?<\/pre>|<ul>[\s\S]*?<\/ul>)/g);
        html = parts.map((p, i) => {
            if (i % 2 === 1) return p;
            return p.split(/\n\n+/).map(para => para.trim() ?
                `<p>${para.replace(/\n/g, '<br>')}</p>` : '').join('');
        }).join('');

        return html;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────────
    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function autoResize(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 130) + 'px';
    }

    function handleInputKey(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    }

    function tcToast(type, msg) {
        if (typeof window.showToast === 'function') {
            window.showToast(type, msg);
            return;
        }
        const colors = {
            success: '#22c55e',
            error: '#ef4444',
            info: '#3b82f6'
        };
        const t = document.createElement('div');
        t.textContent = msg;
        t.style.cssText =
            `position:fixed;bottom:24px;right:24px;z-index:99999;padding:11px 18px;border-radius:10px;background:${colors[type]||'#333'};color:#fff;font-size:.84rem;font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,.3);transition:opacity .4s`;
            document.body.appendChild(t);
            setTimeout(() => {
                t.style.opacity = '0';
                setTimeout(() => t.remove(), 400);
            }, 3000);
        }
    </script>

@endsection
