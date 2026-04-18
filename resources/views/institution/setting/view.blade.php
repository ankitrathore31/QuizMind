@extends('institution.layout.master')

@section('page_title', '⚙️ Settings')

@push('styles')
    <style>
        /* ─── Settings Layout ─── */
        .settings-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 24px;
            align-items: start;
        }

        /* ─── Settings Sidebar Card ─── */
        .settings-sidebar {
            position: sticky;
            top: 88px;
        }

        .profile-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px 22px;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin-bottom: 14px;
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 80px;
            background: linear-gradient(135deg, rgba(124, 92, 252, 0.15), rgba(0, 212, 255, 0.08));
        }

        .profile-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--fh);
            font-weight: 800;
            font-size: 28px;
            margin: 0 auto 14px;
            border: 3px solid rgba(124, 92, 252, 0.4);
            box-shadow: 0 0 30px rgba(124, 92, 252, 0.3);
            position: relative;
            z-index: 1;
        }

        .profile-name {
            font-family: var(--fh);
            font-size: 1rem;
            font-weight: 800;
            margin-bottom: 4px;
            position: relative;
            z-index: 1;
        }

        .profile-type {
            display: inline-block;
            font-size: 0.68rem;
            font-family: var(--fh);
            font-weight: 700;
            padding: 3px 12px;
            border-radius: 20px;
            background: rgba(124, 92, 252, 0.15);
            border: 1px solid rgba(124, 92, 252, 0.3);
            color: var(--violet);
            text-transform: capitalize;
            position: relative;
            z-index: 1;
            margin-bottom: 4px;
        }

        .profile-status {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 0.74rem;
            color: var(--muted);
            margin-top: 8px;
            position: relative;
            z-index: 1;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .status-dot.active {
            background: var(--green);
            box-shadow: 0 0 8px var(--green);
            animation: pulse 2s infinite;
        }

        .status-dot.inactive {
            background: var(--muted);
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .settings-nav {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 12px;
        }

        .settings-nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: var(--radius-xs);
            font-size: 0.84rem;
            color: var(--muted);
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .settings-nav-item:hover {
            color: var(--text);
            background: rgba(255, 255, 255, 0.04);
        }

        .settings-nav-item.active {
            background: rgba(124, 92, 252, 0.1);
            border-color: rgba(124, 92, 252, 0.2);
            color: var(--text);
        }

        .settings-nav-icon {
            font-size: 15px;
            width: 24px;
            text-align: center;
        }

        /* ─── Settings Form Area ─── */
        .settings-main {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-section {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            animation: fadeUp 0.4s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-sec-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.01);
        }

        .form-sec-icon {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .form-sec-title {
            font-family: var(--fh);
            font-size: 0.88rem;
            font-weight: 800;
        }

        .form-sec-desc {
            font-size: 0.74rem;
            color: var(--muted);
        }

        .form-sec-body {
            padding: 22px;
        }

        /* ─── Form Fields ─── */
        .fields-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .fields-grid.single {
            grid-template-columns: 1fr;
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field-label {
            font-family: var(--fh);
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .field-locked-badge {
            font-size: 0.56rem;
            padding: 1px 6px;
            border-radius: 10px;
            background: rgba(139, 138, 160, 0.1);
            border: 1px solid rgba(139, 138, 160, 0.2);
            color: var(--muted);
            letter-spacing: 0.5px;
        }

        .field-input {
            width: 100%;
            background: var(--dark2);
            border: 1px solid var(--border2);
            border-radius: var(--radius-xs);
            color: var(--text);
            font-family: var(--fb);
            font-size: 0.9rem;
            padding: 11px 14px;
            transition: all 0.2s;
            outline: none;
        }

        .field-input:focus {
            border-color: rgba(124, 92, 252, 0.5);
            box-shadow: 0 0 0 3px rgba(124, 92, 252, 0.1);
        }

        .field-input:disabled,
        .field-input[disabled] {
            opacity: 0.5;
            cursor: not-allowed;
            background: rgba(255, 255, 255, 0.02);
        }

        .field-input::placeholder {
            color: var(--muted);
        }

        select.field-input {
            cursor: pointer;
        }

        select.field-input option {
            background: var(--dark2);
        }

        textarea.field-input {
            resize: none;
            min-height: 90px;
        }

        /* Code field with copy */
        .code-field-wrap {
            display: flex;
            gap: 8px;
        }

        .copy-code-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 14px;
            background: rgba(124, 92, 252, 0.08);
            border: 1px solid rgba(124, 92, 252, 0.25);
            border-radius: var(--radius-xs);
            color: var(--violet);
            font-size: 0.78rem;
            font-family: var(--fh);
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .copy-code-btn:hover {
            background: rgba(124, 92, 252, 0.18);
            border-color: rgba(124, 92, 252, 0.45);
            transform: translateY(-1px);
        }

        /* ─── Save Bar ─── */
        .save-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 22px;
            border-top: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.01);
        }

        .save-hint {
            font-size: 0.74rem;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .save-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 28px;
            background: var(--gradient);
            border: none;
            border-radius: var(--radius-xs);
            color: #fff;
            font-family: var(--fh);
            font-weight: 700;
            font-size: 0.84rem;
            cursor: pointer;
            transition: all 0.25s;
            box-shadow: 0 4px 20px rgba(124, 92, 252, 0.35);
        }

        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(124, 92, 252, 0.5);
        }

        .save-btn:active {
            transform: translateY(0);
        }

        .save-btn.loading {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .save-btn .spinner {
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            display: none;
        }

        .save-btn.loading .spinner {
            display: block;
        }

        .save-btn.loading .save-icon {
            display: none;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ─── Validation errors ─── */
        .field-error {
            font-size: 0.72rem;
            color: var(--red);
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ─── Success Alert ─── */
        @if (session('success'))
            .settings-main::before {
                content: '';
                display: block;
            }
        @endif

        /* ─── Responsive ─── */
        @media (max-width: 900px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }

            .settings-sidebar {
                position: static;
            }
        }

        @media (max-width: 560px) {
            .fields-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')

    {{-- Page Header --}}
    <div style="margin-bottom:28px;animation:fadeUp 0.4s ease both">
        <div
            style="font-family:var(--fh);font-size:0.7rem;letter-spacing:2px;color:var(--violet);text-transform:uppercase;font-weight:700;margin-bottom:4px">
            Configuration
        </div>
        <h2 style="font-family:var(--fh);font-size:1.6rem;font-weight:800;line-height:1">
            Institution Settings
        </h2>
        <div style="font-size:0.82rem;color:var(--muted);margin-top:4px">
            Manage your institution profile and preferences
        </div>
    </div>

    <div class="settings-grid">

        {{-- ── Settings Sidebar ── --}}
        <div class="settings-sidebar">

            {{-- Profile Summary --}}
            <div class="profile-card">
                <div class="profile-avatar">
                    {{ strtoupper(substr($institution->name, 0, 1)) }}
                </div>
                <div class="profile-name">{{ $institution->name }}</div>
                <div class="profile-type">{{ $institution->type ?? 'institution' }}</div>
                <div class="profile-status">
                    <span class="status-dot {{ $institution->is_active ? 'active' : 'inactive' }}"></span>
                    {{ $institution->is_active ? 'Active' : 'Inactive' }}
                </div>
            </div>

            {{-- Settings Nav --}}
            <div class="settings-nav">
                <div class="settings-nav-item active">
                    <span class="settings-nav-icon">🏫</span> Institution Info
                </div>
                <div class="settings-nav-item">
                    <span class="settings-nav-icon">📍</span> Location
                </div>
                <div class="settings-nav-item">
                    <span class="settings-nav-icon">🔒</span> Security
                </div>
            </div>

        </div>

        {{-- ── Form ── --}}
        <div class="settings-main">

            <form method="POST" action="{{ route('institution.settings.update') }}" id="settings-form">
                @csrf
                @method('PUT')

                {{-- ── Section 1: Basic Info ── --}}
                <div class="form-section" style="animation-delay:0.05s">
                    <div class="form-sec-header">
                        <div class="form-sec-icon" style="background:rgba(124,92,252,0.1)">🏫</div>
                        <div>
                            <div class="form-sec-title">Institution Information</div>
                            <div class="form-sec-desc">Basic profile details — some fields are read-only</div>
                        </div>
                    </div>

                    <div class="form-sec-body">
                        <div class="fields-grid">

                            {{-- Name (locked) --}}
                            <div class="field-group">
                                <label class="field-label">
                                    🏫 Institution Name
                                    <span class="field-locked-badge">LOCKED</span>
                                </label>
                                <input class="field-input" value="{{ $institution->name }}" disabled>
                            </div>

                            {{-- Email (locked) --}}
                            <div class="field-group">
                                <label class="field-label">
                                    📧 Email
                                    <span class="field-locked-badge">LOCKED</span>
                                </label>
                                <input class="field-input" value="{{ $institution->email }}" disabled>
                            </div>

                            {{-- Referral Code (locked + copy) --}}
                            <div class="field-group">
                                <label class="field-label">
                                    🔑 Referral Code
                                    <span class="field-locked-badge">LOCKED</span>
                                </label>
                                <div class="code-field-wrap">
                                    <input class="field-input" value="{{ $institution->code }}" disabled
                                        style="flex:1;font-family:var(--fh);font-weight:700;letter-spacing:3px;font-size:1rem">
                                    <button type="button" class="copy-code-btn"
                                        onclick="copyCode('{{ $institution->code }}', this)">
                                        📋 Copy
                                    </button>
                                </div>
                            </div>

                            {{-- Principal --}}
                            <div class="field-group">
                                <label class="field-label">👤 Principal Name</label>
                                <input name="principal_name" class="field-input" placeholder="Enter principal name"
                                    value="{{ old('principal_name', $institution->principal_name) }}">
                                @error('principal_name')
                                    <div class="field-error">⚠️ {{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Phone --}}
                            <div class="field-group">
                                <label class="field-label">📱 Phone Number</label>
                                <input name="phone" class="field-input" type="tel" placeholder="Enter phone number"
                                    value="{{ old('phone', $institution->phone) }}">
                                @error('phone')
                                    <div class="field-error">⚠️ {{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Type --}}
                            <div class="field-group">
                                <label class="field-label">🏫 Institution Type</label>
                                <select name="type" class="field-input">
                                    <option value="">— Select Type —</option>
                                    <option value="school"
                                        {{ old('type', $institution->type) == 'school' ? 'selected' : '' }}>🏫 School
                                    </option>
                                    <option value="college"
                                        {{ old('type', $institution->type) == 'college' ? 'selected' : '' }}>🎓 College
                                    </option>
                                    <option value="coaching"
                                        {{ old('type', $institution->type) == 'coaching' ? 'selected' : '' }}>📚 Coaching
                                        Centre</option>
                                </select>
                                @error('type')
                                    <div class="field-error">⚠️ {{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="field-group">
                                <label class="field-label">🟢 Account Status</label>
                                <select name="is_active" class="field-input">
                                    <option value="1"
                                        {{ old('is_active', $institution->is_active) ? 'selected' : '' }}>✅ Active</option>
                                    <option value="0"
                                        {{ !old('is_active', $institution->is_active) ? 'selected' : '' }}>⛔ Inactive
                                    </option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- ── Section 2: Location ── --}}
                <div class="form-section" style="animation-delay:0.12s">
                    <div class="form-sec-header">
                        <div class="form-sec-icon" style="background:rgba(0,212,255,0.1)">📍</div>
                        <div>
                            <div class="form-sec-title">Location Details</div>
                            <div class="form-sec-desc">Where is your institution based?</div>
                        </div>
                    </div>

                    <div class="form-sec-body">
                        <div class="fields-grid">

                            {{-- City --}}
                            <div class="field-group">
                                <label class="field-label">🌆 City</label>
                                <input name="city" class="field-input" placeholder="Enter city"
                                    value="{{ old('city', $institution->city) }}">
                                @error('city')
                                    <div class="field-error">⚠️ {{ $message }}</div>
                                @enderror
                            </div>

                            {{-- State --}}
                            <div class="field-group">
                                <label class="field-label">🗺️ State</label>
                                <input name="state" class="field-input" placeholder="Enter state"
                                    value="{{ old('state', $institution->state) }}">
                                @error('state')
                                    <div class="field-error">⚠️ {{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <div class="fields-grid single" style="margin-top:16px">
                            <div class="field-group">
                                <label class="field-label">📍 Full Address</label>
                                <textarea name="address" class="field-input" placeholder="Enter full address">{{ old('address', $institution->address) }}</textarea>
                                @error('address')
                                    <div class="field-error">⚠️ {{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="save-bar">
                        <div class="save-hint">
                            <span style="font-size:16px">🔒</span>
                            Changes are encrypted and saved securely
                        </div>
                        <button type="submit" class="save-btn" id="save-btn">
                            <span class="spinner"></span>
                            <span class="save-icon">💾</span>
                            Save Changes
                        </button>
                    </div>
                </div>

            </form>

            {{-- ── Danger Zone ── --}}
            <div class="form-section" style="animation-delay:0.2s;border-color:rgba(255,77,106,0.15)">
                <div class="form-sec-header" style="border-bottom-color:rgba(255,77,106,0.1)">
                    <div class="form-sec-icon" style="background:rgba(255,77,106,0.1)">⚠️</div>
                    <div>
                        <div class="form-sec-title" style="color:var(--red)">Danger Zone</div>
                        <div class="form-sec-desc">Irreversible actions. Proceed with caution.</div>
                    </div>
                </div>

                <div class="form-sec-body">
                    <div
                        style="display:flex;align-items:center;justify-content:space-between;padding:14px;border-radius:var(--radius-xs);border:1px dashed rgba(255,77,106,0.2);background:rgba(255,77,106,0.02)">
                        <div>
                            <div style="font-family:var(--fh);font-weight:700;font-size:0.84rem;margin-bottom:2px">Reset
                                Institution Data</div>
                            <div style="font-size:0.74rem;color:var(--muted)">This will permanently delete all student
                                records and stats</div>
                        </div>
                        <button type="button"
                            style="padding:8px 18px;background:rgba(255,77,106,0.08);border:1px solid rgba(255,77,106,0.25);border-radius:var(--radius-xs);color:var(--red);font-family:var(--fh);font-weight:700;font-size:0.78rem;cursor:pointer;transition:all 0.2s"
                            onmouseover="this.style.background='rgba(255,77,106,0.18)'"
                            onmouseout="this.style.background='rgba(255,77,106,0.08)'" onclick="confirmDanger()">
                            🗑️ Delete Data
                        </button>
                    </div>
                </div>
            </div>

        </div>

    </div>



    <script>
        // ── Copy code ──
        function copyCode(code, btn) {
            navigator.clipboard.writeText(code).then(() => {
                const orig = btn.innerHTML;
                btn.innerHTML = '✅ Copied!';
                btn.style.background = 'rgba(0,227,150,0.15)';
                btn.style.borderColor = 'rgba(0,227,150,0.3)';
                btn.style.color = 'var(--green)';
                showToast('Institution code copied: ' + code, 'success');
                setTimeout(() => {
                    btn.innerHTML = orig;
                    btn.style.background = '';
                    btn.style.borderColor = '';
                    btn.style.color = '';
                }, 2000);
            });
        }

        // ── Form Submit with Loading ──
        document.getElementById('settings-form').addEventListener('submit', function() {
            const btn = document.getElementById('save-btn');
            btn.classList.add('loading');
            btn.textContent = '';
            const spinner = document.createElement('div');
            spinner.className = 'spinner';
            spinner.style.display = 'block';
            btn.appendChild(spinner);
            const txt = document.createElement('span');
            txt.textContent = ' Saving...';
            btn.appendChild(txt);
        });

        // ── Input Focus Glow ──
        document.querySelectorAll('.field-input:not([disabled])').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-1px)';
            });
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });

        // ── Danger Confirmation ──
        function confirmDanger() {
            const confirmed = confirm(
                '⚠️ This will permanently delete ALL student data. This cannot be undone.\n\nAre you absolutely sure?');
            if (confirmed) {
                showToast('Feature requires additional authorization. Contact support.', 'warn');
            }
        }

        // ── Settings Nav (client-side tab switching) ──
        document.querySelectorAll('.settings-nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.settings-nav-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // ── Stagger animations for form sections ──
        document.querySelectorAll('.form-section').forEach((el, i) => {
            el.style.animationDelay = (i * 0.08 + 0.05) + 's';
        });
    </script>
@endsection
