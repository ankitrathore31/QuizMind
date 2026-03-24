@extends('layouts.app')

@section('title', 'QuizMind AI — Login or Sign Up')

@section('content')

    <div class="auth-page">

        {{-- Animated background grid --}}
        <div class="auth-grid-bg"></div>

        {{-- Glow orbs --}}
        <div class="auth-orb auth-orb-1"></div>
        <div class="auth-orb auth-orb-2"></div>
        <div class="auth-orb auth-orb-3"></div>

        <div class="auth-wrap">

            {{-- ── Left panel (visible on wide screens) ── --}}
            <div class="auth-left">
                <a href="{{ route('home') }}" class="auth-logo">
                    <span class="brand-box">QM</span>
                    Quiz<span class="accent">Mind</span> AI
                </a>
                <div class="auth-left-body">
                    <h2 class="auth-left-title">Level up your<br><span class="text-grad">learning game.</span></h2>
                    <p class="auth-left-sub">50,000+ students · 2M+ quizzes · 98% satisfaction</p>
                    <ul class="auth-perks">
                        <li><span class="perk-dot"></span>AI-generated MCQs from any topic or PDF</li>
                        <li><span class="perk-dot"></span>Live 1v1 and team battle rooms</li>
                        <li><span class="perk-dot"></span>Smart analytics that track your weak spots</li>
                        <li><span class="perk-dot"></span>XP, streaks, trophies & leaderboards</li>
                    </ul>
                </div>
                {{-- Floating card preview --}}
                <div class="auth-preview-card">
                    <div class="preview-top">
                        <span class="live-dot"></span>
                        <span style="font-size:.72rem;font-family:var(--fh);font-weight:700;color:var(--green)">LIVE
                            BATTLE</span>
                        <span style="margin-left:auto;font-size:.72rem;color:var(--muted)">Q 4 / 10</span>
                    </div>
                    <div class="preview-q">What is Newton's 2nd Law of Motion?</div>
                    <div class="preview-opts">
                        <div class="preview-opt preview-opt-correct">A &nbsp; F = ma ✓</div>
                        <div class="preview-opt">B &nbsp; E = mc²</div>
                    </div>
                    <div class="preview-bar">
                        <div class="preview-fill"></div>
                    </div>
                </div>
            </div>

            {{-- ── Right panel — form ── --}}
            <div class="auth-right">
                <div class="auth-card" id="auth-card">

                    {{-- Mobile logo --}}
                    <a href="{{ route('home') }}" class="auth-logo auth-logo-mobile">
                        <span class="brand-box">QM</span>
                        Quiz<span class="accent">Mind</span> AI
                    </a>

                    {{-- Flash messages --}}
                    @if ($errors->any())
                        <div class="flash flash-error">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                            {{ $errors->first() }}
                        </div>
                    @endif
                    @if (session('status'))
                        <div class="flash flash-success">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            {{ session('status') }}
                        </div>
                    @endif

                    {{-- Tab switcher --}}
                    <div class="auth-tabs" id="auth-tabs">
                        <button class="auth-tab active" id="tab-login" onclick="switchMode('login')">Log In</button>
                        <button class="auth-tab" id="tab-register" onclick="switchMode('register')">Sign Up</button>
                        <div class="auth-tab-slider" id="tab-slider"></div>
                    </div>

                    {{-- ═══════════ LOGIN PANEL ═══════════ --}}
                    <div class="auth-panel" id="panel-login">
                        <div class="panel-head">
                            <h2 class="panel-title">Welcome back 👋</h2>
                            <p class="panel-sub">Sign in to continue your quiz journey</p>
                        </div>

                        {{-- Google --}}
                        <a href="{{ route('auth.google') }}" class="btn-google">
                            <svg width="18" height="18" viewBox="0 0 48 48">
                                <path fill="#EA4335"
                                    d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.08 17.74 9.5 24 9.5z" />
                                <path fill="#4285F4"
                                    d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
                                <path fill="#FBBC05"
                                    d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z" />
                                <path fill="#34A853"
                                    d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.36-8.16 2.36-6.26 0-11.57-3.59-13.46-8.83l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
                            </svg>
                            Continue with Google
                        </a>

                        <div class="or-divider"><span>or</span></div>

                        <form method="POST" action="{{ route('login') }}" id="form-login" novalidate>
                            @csrf

                            <div class="field-group">
                                <div class="field" id="field-login-email">
                                    <label class="field-label" for="login-email">Email address</label>
                                    <div class="field-inner">
                                        <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path
                                                d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                            <polyline points="22,6 12,13 2,6" />
                                        </svg>
                                        <input type="email" name="email" id="login-email"
                                            placeholder="you@example.com" value="{{ old('email') }}" required
                                            autocomplete="email" oninput="clearFieldError('field-login-email')">
                                    </div>
                                </div>

                                <div class="field" id="field-login-pass">
                                    <label class="field-label" for="login-pass">Password</label>
                                    <div class="field-inner">
                                        <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="1.8">
                                            <rect x="3" y="11" width="18" height="11" rx="2"
                                                ry="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                        <input type="password" name="password" id="login-pass" placeholder="••••••••"
                                            required autocomplete="current-password"
                                            oninput="clearFieldError('field-login-pass')">
                                        <button type="button" class="eye-btn" onclick="togglePwd('login-pass', this)"
                                            tabindex="-1">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.8" class="eye-open">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.8" class="eye-closed"
                                                style="display:none">
                                                <path
                                                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                                <line x1="1" y1="1" x2="23" y2="23" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="field-row-end">
                                <a href="{{ route('password.request') }}" class="link-muted">Forgot password?</a>
                            </div>

                            <button type="submit" class="btn-submit" id="btn-login">
                                <span class="btn-submit-text">Log In</span>
                                <svg class="btn-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5">
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                    <polyline points="12 5 19 12 12 19" />
                                </svg>
                                <span class="btn-spinner" id="sp-login" style="display:none">
                                    <span class="spinner"></span>
                                </span>
                            </button>

                            <p class="switch-hint">No account? <button type="button" class="link-accent"
                                    onclick="switchMode('register')">Sign up free →</button></p>
                        </form>
                    </div>

                    {{-- ═══════════ REGISTER PANEL ═══════════ --}}
                    <div class="auth-panel auth-panel-hidden" id="panel-register">
                        <div class="panel-head">
                            <h2 class="panel-title">Create Account 🚀</h2>
                            <p class="panel-sub">Join 50,000+ students competing nationwide</p>
                        </div>

                        {{-- Google --}}
                        <a href="{{ route('auth.google') }}" class="btn-google">
                            <svg width="18" height="18" viewBox="0 0 48 48">
                                <path fill="#EA4335"
                                    d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.08 17.74 9.5 24 9.5z" />
                                <path fill="#4285F4"
                                    d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
                                <path fill="#FBBC05"
                                    d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z" />
                                <path fill="#34A853"
                                    d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.36-8.16 2.36-6.26 0-11.57-3.59-13.46-8.83l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
                            </svg>
                            Sign up with Google
                        </a>

                        <div class="or-divider"><span>or</span></div>

                        <form method="POST" action="{{ route('register') }}" id="form-register" novalidate>
                            @csrf

                            <div class="field-group">
                                <div class="field" id="field-reg-name">
                                    <label class="field-label" for="reg-name">Full Name</label>
                                    <div class="field-inner">
                                        <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                            <circle cx="12" cy="7" r="4" />
                                        </svg>
                                        <input type="text" name="name" id="reg-name" placeholder="Arjun Sharma"
                                            value="{{ old('name') }}" required autocomplete="name"
                                            oninput="clearFieldError('field-reg-name')">
                                    </div>
                                </div>

                                <div class="field" id="field-reg-email">
                                    <label class="field-label" for="reg-email">Email Address</label>
                                    <div class="field-inner">
                                        <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path
                                                d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                            <polyline points="22,6 12,13 2,6" />
                                        </svg>
                                        <input type="email" name="email" id="reg-email"
                                            placeholder="you@example.com" value="{{ old('email') }}" required
                                            autocomplete="email" oninput="clearFieldError('field-reg-email')">
                                    </div>
                                </div>

                                <div class="field-row">
                                    <div class="field" id="field-reg-pass">
                                        <label class="field-label" for="reg-pass">Password</label>
                                        <div class="field-inner">
                                            <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="1.8">
                                                <rect x="3" y="11" width="18" height="11" rx="2" />
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                            </svg>
                                            <input type="password" name="password" id="reg-pass"
                                                placeholder="Min 6 chars" required minlength="6"
                                                oninput="clearFieldError('field-reg-pass')">
                                            <button type="button" class="eye-btn" onclick="togglePwd('reg-pass', this)"
                                                tabindex="-1">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="1.8" class="eye-open">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="1.8" class="eye-closed"
                                                    style="display:none">
                                                    <path
                                                        d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                                    <line x1="1" y1="1" x2="23" y2="23" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="field" id="field-reg-pass2">
                                        <label class="field-label" for="reg-pass2">Confirm</label>
                                        <div class="field-inner">
                                            <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="1.8">
                                                <polyline points="20 6 9 17 4 12" />
                                            </svg>
                                            <input type="password" name="password_confirmation" id="reg-pass2"
                                                placeholder="Repeat" required
                                                oninput="clearFieldError('field-reg-pass2')">
                                        </div>
                                    </div>
                                </div>

                                <div class="field" id="field-reg-college">
                                    <label class="field-label" for="reg-college">College / School <span
                                            class="opt-tag">optional</span></label>
                                    <div class="field-inner">
                                        <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                            <polyline points="9 22 9 12 15 12 15 22" />
                                        </svg>
                                        <input type="text" name="college" id="reg-college"
                                            placeholder="Your institution name" value="{{ old('college') }}"
                                            autocomplete="organization">
                                    </div>
                                </div>
                            </div>

                            {{-- Role picker --}}
                            <input type="hidden" name="role" id="role-input" value="{{ old('role', 'student') }}">
                            <div class="role-section">
                                <span class="field-label" style="display:block;margin-bottom:10px;">I am a</span>
                                <div class="role-grid">
                                    @foreach ([['student', '👨‍🎓', 'Student'], ['teacher', '👨‍🏫', 'Teacher'], ['institution', '🏛️', 'Institution'], ['parent', '👨‍👩‍👧', 'Parent']] as [$rid, $rico, $rname])
                                        <button type="button"
                                            class="role-btn {{ old('role', 'student') === $rid ? 'role-active' : '' }}"
                                            data-role="{{ $rid }}" onclick="selectRole(this)">
                                            <span class="role-icon">{{ $rico }}</span>
                                            <span class="role-name">{{ $rname }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Ref code --}}
                            <div class="field" id="ref-code-wrap"
                                style="{{ old('role', 'student') !== 'student' ? 'display:none' : '' }}; margin-bottom:0">
                                <label class="field-label" for="reg-ref">Reference Code <span
                                        class="opt-tag">optional</span></label>
                                <div class="field-inner">
                                    <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                                    </svg>
                                    <input type="text" name="ref_code" id="reg-ref"
                                        placeholder="Teacher / Instituion / parent code" value="{{ old('ref_code') }}">
                                </div>
                            </div>

                            <button type="submit" class="btn-submit" id="btn-register">
                                <span class="btn-submit-text">Create Account</span>
                                <svg class="btn-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5">
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                    <polyline points="12 5 19 12 12 19" />
                                </svg>
                                <span class="btn-spinner" id="sp-register" style="display:none">
                                    <span class="spinner"></span>
                                </span>
                            </button>

                            <p class="switch-hint">Have an account? <button type="button" class="link-accent"
                                    onclick="switchMode('login')">Log in →</button></p>
                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>

@endsection

@push('styles')
    <style>
        /* ══════════════════════════════════════════════
       AUTH PAGE — Full redesign
    ══════════════════════════════════════════════ */

        /* ── Page shell ──────────────────────────────── */
        .auth-page {
            min-height: 100vh;
            background: var(--dark);
            display: flex;
            align-items: stretch;
            position: relative;
            overflow: hidden;
        }

        /* ── Animated grid background ───────────────── */
        .auth-grid-bg {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(124, 92, 252, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(124, 92, 252, 0.04) 1px, transparent 1px);
            background-size: 48px 48px;
            animation: gridShift 20s linear infinite;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes gridShift {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 48px 48px;
            }
        }

        /* ── Orbs ────────────────────────────────────── */
        .auth-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(90px);
            pointer-events: none;
            z-index: 0;
            animation: orbFloat 8s ease-in-out infinite;
        }

        .auth-orb-1 {
            width: 500px;
            height: 500px;
            background: rgba(124, 92, 252, 0.1);
            top: -150px;
            right: -100px;
        }

        .auth-orb-2 {
            width: 400px;
            height: 400px;
            background: rgba(0, 212, 255, 0.07);
            bottom: -100px;
            left: -100px;
            animation-delay: -3s;
        }

        .auth-orb-3 {
            width: 300px;
            height: 300px;
            background: rgba(255, 107, 157, 0.05);
            top: 40%;
            left: 30%;
            animation-delay: -6s;
        }

        @keyframes orbFloat {

            0%,
            100% {
                transform: translateY(0) scale(1);
            }

            50% {
                transform: translateY(-20px) scale(1.05);
            }
        }

        /* ── Two-column layout ───────────────────────── */
        .auth-wrap {
            display: flex;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        /* ── Left decorative panel ───────────────────── */
        .auth-left {
            flex: 0 0 45%;
            background: linear-gradient(160deg, rgba(124, 92, 252, 0.08) 0%, rgba(0, 212, 255, 0.04) 100%);
            border-right: 1px solid var(--border);
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .auth-left::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            border: 1px solid rgba(124, 92, 252, 0.12);
            bottom: -80px;
            right: -80px;
            animation: spinSlow 30s linear infinite;
        }

        .auth-left::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            border: 1px solid rgba(0, 212, 255, 0.1);
            bottom: -20px;
            right: -20px;
            animation: spinSlow 20s linear infinite reverse;
        }

        @keyframes spinSlow {
            to {
                transform: rotate(360deg);
            }
        }

        .auth-logo {
            font-family: var(--fh);
            font-weight: 800;
            font-size: 1rem;
            color: var(--text);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 60px;
            animation: fadeSlideUp .5s ease both;
        }

        .auth-logo-mobile {
            display: none;
            margin-bottom: 24px;
        }

        .auth-left-body {
            flex: 1;
            animation: fadeSlideUp .5s ease .1s both;
        }

        .auth-left-title {
            font-family: var(--fh);
            font-size: clamp(1.7rem, 2.5vw, 2.4rem);
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 14px;
        }

        .auth-left-sub {
            font-size: .8rem;
            color: var(--muted);
            margin-bottom: 32px;
            font-family: var(--fh);
            font-weight: 600;
        }

        .auth-perks {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .auth-perks li {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: .84rem;
            color: var(--muted);
            animation: fadeSlideUp .5s ease both;
        }

        .auth-perks li:nth-child(1) {
            animation-delay: .2s;
        }

        .auth-perks li:nth-child(2) {
            animation-delay: .3s;
        }

        .auth-perks li:nth-child(3) {
            animation-delay: .4s;
        }

        .auth-perks li:nth-child(4) {
            animation-delay: .5s;
        }

        .perk-dot {
            width: 6px;
            height: 6px;
            flex-shrink: 0;
            border-radius: 50%;
            background: var(--gradient);
            box-shadow: 0 0 8px rgba(124, 92, 252, 0.6);
        }

        /* ── Preview card ────────────────────────────── */
        .auth-preview-card {
            background: var(--card);
            border: 1px solid var(--border2);
            border-radius: var(--radius);
            padding: 18px;
            margin-top: 40px;
            position: relative;
            z-index: 1;
            animation: fadeSlideUp .6s ease .6s both;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(124, 92, 252, 0.1);
        }

        .preview-top {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 10px var(--green);
            animation: pulse 1.5s infinite;
        }

        .preview-q {
            font-family: var(--fh);
            font-size: .82rem;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .preview-opts {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 14px;
        }

        .preview-opt {
            padding: 7px 12px;
            border-radius: 8px;
            font-size: .74rem;
            font-family: var(--fh);
            font-weight: 700;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            color: var(--muted);
        }

        .preview-opt-correct {
            background: rgba(0, 227, 150, 0.08);
            border-color: rgba(0, 227, 150, 0.25);
            color: var(--green);
        }

        .preview-bar {
            height: 3px;
            background: rgba(255, 255, 255, .06);
            border-radius: 2px;
            overflow: hidden;
        }

        .preview-fill {
            height: 100%;
            width: 40%;
            background: var(--gradient);
            border-radius: 2px;
            animation: timerShrink 10s linear infinite;
        }

        @keyframes timerShrink {
            0% {
                width: 100%;
            }

            100% {
                width: 0%;
            }
        }

        /* ── Right form panel ────────────────────────── */
        .auth-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 32px;
            overflow-y: auto;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            animation: cardEntrance .6s cubic-bezier(.22, 1, .36, 1) both;
        }

        @keyframes cardEntrance {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Flash messages ──────────────────────────── */
        .flash {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 11px 14px;
            border-radius: var(--radius-xs);
            font-size: .82rem;
            margin-bottom: 18px;
            animation: fadeSlideUp .3s ease both;
        }

        .flash-error {
            background: rgba(255, 77, 106, .08);
            border: 1px solid rgba(255, 77, 106, .25);
            color: var(--red);
        }

        .flash-success {
            background: rgba(0, 227, 150, .08);
            border: 1px solid rgba(0, 227, 150, .2);
            color: var(--green);
        }

        /* ── Tabs ────────────────────────────────────── */
        .auth-tabs {
            display: flex;
            position: relative;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 28px;
            gap: 0;
        }

        .auth-tab {
            flex: 1;
            padding: 9px 0;
            border: none;
            border-radius: 9px;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            font-family: var(--fh);
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .3px;
            transition: color .25s;
            position: relative;
            z-index: 1;
        }

        .auth-tab.active {
            color: #fff;
        }

        .auth-tab-slider {
            position: absolute;
            top: 4px;
            bottom: 4px;
            width: calc(50% - 4px);
            left: 4px;
            background: var(--gradient);
            border-radius: 9px;
            transition: transform .3s cubic-bezier(.34, 1.56, .64, 1);
            box-shadow: 0 2px 12px rgba(124, 92, 252, 0.4);
        }

        .auth-tab-slider.slide-right {
            transform: translateX(calc(100% + 0px));
        }

        /* ── Panel switching ─────────────────────────── */
        .auth-panel {
            animation: panelIn .35s ease both;
        }

        .auth-panel-hidden {
            display: none;
        }

        @keyframes panelIn {
            from {
                opacity: 0;
                transform: translateX(12px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* ── Panel header ────────────────────────────── */
        .panel-head {
            margin-bottom: 22px;
        }

        .panel-title {
            font-family: var(--fh);
            font-size: 1.45rem;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .panel-sub {
            font-size: .82rem;
            color: var(--muted);
        }

        /* ── Google button ───────────────────────────── */
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 11px;
            width: 100%;
            padding: 11px 20px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.04);
            color: var(--text);
            font-family: var(--fh);
            font-weight: 700;
            font-size: .84rem;
            cursor: pointer;
            transition: all .2s;
            text-decoration: none;
            margin-bottom: 18px;
            position: relative;
            overflow: hidden;
        }

        .btn-google::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0);
            transition: background .2s;
        }

        .btn-google:hover::before {
            background: rgba(255, 255, 255, 0.04);
        }

        .btn-google:hover {
            border-color: rgba(255, 255, 255, 0.22);
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
            color: var(--text);
        }

        .btn-google:active {
            transform: translateY(0);
        }

        /* ── OR divider ──────────────────────────────── */
        .or-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            color: var(--muted);
            font-size: .72rem;
            font-family: var(--fh);
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .or-divider::before,
        .or-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ── Field group ─────────────────────────────── */
        .field-group {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-bottom: 14px;
        }

        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        /* ── Field ───────────────────────────────────── */
        .field {
            position: relative;
        }

        .field-label {
            display: block;
            font-family: var(--fh);
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 7px;
            transition: color .2s;
        }

        .field:focus-within .field-label {
            color: var(--violet);
        }

        .field-inner {
            position: relative;
            display: flex;
            align-items: center;
        }

        .field-icon {
            position: absolute;
            left: 13px;
            color: var(--muted);
            pointer-events: none;
            transition: color .2s;
            flex-shrink: 0;
        }

        .field:focus-within .field-icon {
            color: var(--violet);
        }

        .field-inner input {
            width: 100%;
            padding: 11px 40px 11px 40px;
            background: rgba(255, 255, 255, 0.04);
            border: 1.5px solid rgba(255, 255, 255, 0.09);
            border-radius: 10px;
            color: var(--text);
            font-family: var(--fb);
            font-size: .9rem;
            outline: none;
            transition: border-color .2s, background .2s, box-shadow .2s;
            -webkit-appearance: none;
        }

        .field-inner input::placeholder {
            color: rgba(139, 138, 160, 0.5);
        }

        .field-inner input:focus {
            border-color: rgba(124, 92, 252, 0.6);
            background: rgba(124, 92, 252, 0.04);
            box-shadow: 0 0 0 3px rgba(124, 92, 252, 0.1);
        }

        .field-inner input:hover:not(:focus) {
            border-color: rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.06);
        }

        /* error state */
        .field.field-error .field-inner input {
            border-color: rgba(255, 77, 106, 0.6);
            box-shadow: 0 0 0 3px rgba(255, 77, 106, 0.1);
        }

        .field.field-error .field-label {
            color: var(--red);
        }

        .field-error-msg {
            font-size: .72rem;
            color: var(--red);
            margin-top: 5px;
            display: none;
        }

        .field.field-error .field-error-msg {
            display: block;
        }

        /* ── Password toggle ─────────────────────────── */
        .eye-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            padding: 2px;
            display: flex;
            align-items: center;
            transition: color .2s;
        }

        .eye-btn:hover {
            color: var(--text);
        }

        /* ── End row ─────────────────────────────────── */
        .field-row-end {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 18px;
            margin-top: -6px;
        }

        .link-muted {
            font-size: .76rem;
            color: var(--muted);
            text-decoration: none;
            transition: color .2s;
            font-family: var(--fh);
            font-weight: 600;
        }

        .link-muted:hover {
            color: var(--violet);
        }

        /* ── Submit button ───────────────────────────── */
        .btn-submit {
            width: 100%;
            padding: 13px 24px;
            border: none;
            border-radius: 10px;
            background: var(--gradient);
            color: #fff;
            font-family: var(--fh);
            font-weight: 800;
            font-size: .9rem;
            letter-spacing: .4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 18px;
            margin-bottom: 16px;
            transition: all .2s;
            box-shadow: 0 4px 20px rgba(124, 92, 252, 0.35);
            position: relative;
            overflow: hidden;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), transparent);
            opacity: 0;
            transition: opacity .2s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(124, 92, 252, 0.5);
        }

        .btn-submit:hover::before {
            opacity: 1;
        }

        .btn-submit:active {
            transform: translateY(0);
            box-shadow: 0 4px 16px rgba(124, 92, 252, 0.3);
        }

        .btn-submit:disabled {
            opacity: .7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-arrow {
            transition: transform .2s;
            flex-shrink: 0;
        }

        .btn-submit:hover .btn-arrow {
            transform: translateX(3px);
        }

        .btn-spinner {
            display: flex;
            align-items: center;
        }

        /* ── Optional tag ────────────────────────────── */
        .opt-tag {
            font-size: .62rem;
            color: var(--muted);
            text-transform: lowercase;
            letter-spacing: 0;
            margin-left: 4px;
            background: rgba(255, 255, 255, 0.05);
            padding: 1px 6px;
            border-radius: 4px;
            font-weight: 400;
        }

        /* ── Role picker ─────────────────────────────── */
        .role-section {
            margin-bottom: 14px;
        }

        .role-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .role-btn {
            padding: 10px 10px;
            border-radius: 10px;
            border: 1.5px solid rgba(255, 255, 255, 0.09);
            background: rgba(255, 255, 255, 0.03);
            color: var(--muted);
            cursor: pointer;
            font-family: var(--fh);
            font-weight: 700;
            font-size: .76rem;
            transition: all .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            text-align: center;
        }

        .role-btn:hover {
            border-color: rgba(124, 92, 252, 0.4);
            color: var(--text);
            background: rgba(124, 92, 252, 0.06);
        }

        .role-btn.role-active {
            background: linear-gradient(135deg, rgba(124, 92, 252, 0.2), rgba(0, 212, 255, 0.12));
            border-color: rgba(124, 92, 252, 0.5);
            color: var(--violet);
            box-shadow: 0 0 0 3px rgba(124, 92, 252, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        .role-icon {
            font-size: 1rem;
            line-height: 1;
        }

        .role-name {
            font-size: .74rem;
        }

        /* ── Switch hint ─────────────────────────────── */
        .switch-hint {
            text-align: center;
            font-size: .79rem;
            color: var(--muted);
            margin: 0;
        }

        .link-accent {
            background: none;
            border: none;
            color: var(--violet);
            cursor: pointer;
            font-weight: 700;
            font-size: inherit;
            padding: 0;
            font-family: var(--fh);
            transition: color .2s;
        }

        .link-accent:hover {
            color: var(--cyan);
        }

        /* ── Spinner ─────────────────────────────────── */
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.25);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
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

        /* ── Responsive ──────────────────────────────── */
        @media (max-width: 900px) {
            .auth-left {
                display: none;
            }

            .auth-right {
                padding: 32px 20px;
            }

            .auth-logo-mobile {
                display: inline-flex;
            }
        }

        @media (max-width: 480px) {
            .field-row {
                grid-template-columns: 1fr;
            }

            .auth-card {
                max-width: 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        /* ── Tab / panel switching ───────────────────── */
        function switchMode(mode) {
            const isLogin = mode === 'login';

            // Panels
            const pLogin = document.getElementById('panel-login');
            const pReg = document.getElementById('panel-register');
            pLogin.classList.toggle('auth-panel-hidden', !isLogin);
            pReg.classList.toggle('auth-panel-hidden', isLogin);
            if (!isLogin) {
                pReg.classList.add('auth-panel');
            }

            // Tabs
            document.getElementById('tab-login').classList.toggle('active', isLogin);
            document.getElementById('tab-register').classList.toggle('active', !isLogin);

            // Slider
            const slider = document.getElementById('tab-slider');
            slider.classList.toggle('slide-right', !isLogin);

            // Re-trigger panel animation
            const panel = isLogin ? pLogin : pReg;
            panel.style.animation = 'none';
            panel.offsetHeight; // reflow
            panel.style.animation = '';
        }

        /* ── Role picker ─────────────────────────────── */
        function selectRole(btn) {
            document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('role-active'));
            btn.classList.add('role-active');
            const role = btn.dataset.role;
            document.getElementById('role-input').value = role;
            const refWrap = document.getElementById('ref-code-wrap');
            refWrap.style.display = role === 'student' ? '' : 'none';
            refWrap.style.marginBottom = role === 'student' ? '14px' : '0';
        }

        /* ── Password toggle ─────────────────────────── */
        function togglePwd(inputId, btn) {
            const input = document.getElementById(inputId);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.querySelector('.eye-open').style.display = show ? 'none' : '';
            btn.querySelector('.eye-closed').style.display = show ? '' : 'none';
        }

        /* ── Clear field error on user input ────────── */
        function clearFieldError(fieldId) {
            document.getElementById(fieldId)?.classList.remove('field-error');
        }

        /* ── Spinner on submit ───────────────────────── */
        function attachSubmitSpinner(formId, btnId, spinnerId) {
            const form = document.getElementById(formId);
            if (!form) return;
            form.addEventListener('submit', function(e) {
                // Basic client-side required check
                const inputs = form.querySelectorAll('input[required]');
                let valid = true;
                inputs.forEach(inp => {
                    if (!inp.value.trim()) {
                        inp.closest('.field')?.classList.add('field-error');
                        valid = false;
                    }
                });
                if (!valid) {
                    e.preventDefault();
                    return;
                }

                const btn = document.getElementById(btnId);
                const spinner = document.getElementById(spinnerId);
                btn.disabled = true;
                btn.querySelector('.btn-submit-text').style.opacity = '0.4';
                btn.querySelector('.btn-arrow').style.display = 'none';
                spinner.style.display = 'flex';
            });
        }
        attachSubmitSpinner('form-login', 'btn-login', 'sp-login');
        attachSubmitSpinner('form-register', 'btn-register', 'sp-register');

        /* ── Keep register tab if validation errors exist & name field had value ── */
        @if ($errors->any() && old('name'))
            switchMode('register');
        @endif

        /* ── Input focus glow on card ────────────────── */
        document.querySelectorAll('.field-inner input').forEach(inp => {
            inp.addEventListener('focus', () => {
                inp.closest('.auth-card').style.boxShadow =
                    '0 0 0 1px rgba(124,92,252,0.15), 0 30px 60px rgba(0,0,0,0.4)';
            });
            inp.addEventListener('blur', () => {
                inp.closest('.auth-card').style.boxShadow = '';
            });
        });
    </script>
@endpush
