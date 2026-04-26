@extends('student.layout.master')

@section('title', $student->is_profile_complete ? 'My Profile' : 'Complete Your Profile')


<style>
    .profile-wrap {
        max-width: 980px;
        padding: 32px 20px 72px;
        margin: 0 auto;
    }

    /* ── First-time banner ── */
    .setup-banner {
        background: linear-gradient(135deg, rgba(124, 92, 252, .18), rgba(0, 212, 255, .1));
        border: 1px solid rgba(124, 92, 252, .35);
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        gap: 16px;
        animation: slideDown .4s cubic-bezier(.34, 1.56, .64, 1);
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-14px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .setup-banner-icon {
        font-size: 2rem;
        flex-shrink: 0;
    }

    .setup-banner-text h3 {
        font-family: var(--fh, sans-serif);
        font-size: .95rem;
        font-weight: 800;
        margin-bottom: 3px;
    }

    .setup-banner-text p {
        font-size: .8rem;
        color: var(--muted);
        line-height: 1.5;
    }

    /* ── Page grid ── */
    .profile-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 20px;
        align-items: start;
    }

    @media (max-width: 760px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }

    /* ══════════════════════
       LEFT — identity card
    ══════════════════════ */
    .identity-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
        position: sticky;
        top: 24px;
    }

    .identity-cover {
        height: 78px;
        background: linear-gradient(135deg, #7C5CFC 0%, #00D4FF 100%);
        position: relative;
    }

    .identity-cover::after {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23fff' fill-opacity='0.07'%3E%3Cpath d='M20 20.5V18H0v5h5v5H0v5h20v-9.5zm-8-7.5h6v6h-6v-6zm2 16H8v-6h6v6zm-8-16h6v6H6v-6zm8 0h6v6h-6v-6z'/%3E%3C/g%3E%3C/svg%3E");
    }

    .id-avatar-wrap {
        position: relative;
        margin: -34px 0 0 20px;
        width: fit-content;
    }

    .id-avatar {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: var(--card);
        border: 3px solid var(--card);
        box-shadow: 0 4px 18px rgba(0, 0, 0, .4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        transition: transform .3s;
    }

    .id-avatar:hover {
        transform: scale(1.1) rotate(-4deg);
    }

    .lv-pip {
        position: absolute;
        bottom: 0;
        right: -6px;
        background: linear-gradient(135deg, #7C5CFC, #00D4FF);
        color: #fff;
        font-size: .58rem;
        font-weight: 800;
        font-family: var(--fh, sans-serif);
        padding: 2px 7px;
        border-radius: 999px;
        border: 2px solid var(--card);
        white-space: nowrap;
    }

    .id-body {
        padding: 12px 20px 22px;
    }

    .id-name {
        font-family: var(--fh, sans-serif);
        font-size: 1.15rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 2px;
    }

    .id-name.ph {
        color: var(--muted);
        font-style: italic;
        font-weight: 400;
    }

    .id-handle {
        font-size: .72rem;
        color: var(--muted);
        margin-bottom: 9px;
    }

    .id-bio {
        font-size: .8rem;
        color: var(--muted);
        line-height: 1.6;
        margin-bottom: 12px;
        min-height: 36px;
    }

    .id-bio.empty {
        color: rgba(255, 255, 255, .18);
        font-style: italic;
    }

    .chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 12px;
    }

    .chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: .69rem;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .05);
        border: 1px solid var(--border);
        color: var(--muted);
    }

    .xp-area {
        margin-bottom: 12px;
    }

    .xp-row-sm {
        display: flex;
        justify-content: space-between;
        font-size: .68rem;
        color: var(--muted);
        margin-bottom: 4px;
    }

    .xp-track-sm {
        height: 6px;
        border-radius: 3px;
        background: rgba(255, 255, 255, .07);
        overflow: hidden;
    }

    .xp-bar-sm {
        height: 100%;
        border-radius: 3px;
        background: linear-gradient(90deg, #7C5CFC, #00D4FF);
        transition: width 1.1s cubic-bezier(.4, 0, .2, 1);
    }

    .subj-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 8px;
    }

    .subj-pill {
        font-size: .68rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 999px;
        background: rgba(124, 92, 252, .1);
        border: 1px solid rgba(124, 92, 252, .25);
        color: #9B7BFF;
    }

    .mini-stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 7px;
        padding-top: 12px;
        border-top: 1px solid var(--border);
        margin-top: 12px;
    }

    .mstat {
        background: rgba(255, 255, 255, .03);
        border: 1px solid var(--border);
        border-radius: 9px;
        padding: 9px 10px;
        text-align: center;
    }

    .mstat-val {
        font-family: var(--fh, sans-serif);
        font-size: 1rem;
        font-weight: 800;
    }

    .mstat-label {
        font-size: .62rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-top: 1px;
    }

    /* ══════════════════════
       RIGHT — edit sections
    ══════════════════════ */
    .edit-stack {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .sec-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 15px;
        overflow: hidden;
        transition: border-color .2s;
    }

    .sec-card:focus-within {
        border-color: rgba(124, 92, 252, .45);
    }

    .sec-head {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px 20px;
        border-bottom: 1px solid var(--border);
        font-family: var(--fh, sans-serif);
        font-size: .88rem;
        font-weight: 700;
    }

    .sec-icon {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        background: rgba(124, 92, 252, .12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
    }

    .sec-body {
        padding: 18px 20px;
    }

    /* Avatar picker */
    .av-pick-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    .av-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: rgba(124, 92, 252, .1);
        border: 2px solid rgba(124, 92, 252, .3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.55rem;
        flex-shrink: 0;
        transition: transform .2s;
    }

    .av-pick-row:hover .av-circle {
        transform: scale(1.08);
    }

    .emo-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .emo-btn {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 2px solid transparent;
        background: rgba(255, 255, 255, .04);
        font-size: 1.1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: border-color .15s, transform .15s, background .15s;
    }

    .emo-btn:hover {
        transform: scale(1.2);
        border-color: rgba(124, 92, 252, .6);
    }

    .emo-btn.selected {
        border-color: #7C5CFC;
        background: rgba(124, 92, 252, .17);
    }

    /* Subject tags */
    .tag-cloud {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 7px;
    }

    .subj-tag {
        padding: 5px 12px;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 600;
        border: 1.5px solid var(--border);
        color: var(--muted);
        cursor: pointer;
        transition: all .17s;
        user-select: none;
    }

    .subj-tag:hover {
        border-color: #7C5CFC;
        color: #7C5CFC;
    }

    .subj-tag.selected {
        border-color: #7C5CFC;
        background: rgba(124, 92, 252, .14);
        color: #7C5CFC;
    }

    /* Form row */
    .form-row-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    @media (max-width:500px) {
        .form-row-2 {
            grid-template-columns: 1fr;
        }
    }

    /* Save footer */
    .save-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding-top: 14px;
        border-top: 1px solid var(--border);
        margin-top: 16px;
        flex-wrap: wrap;
    }

    .save-hint {
        font-size: .72rem;
        color: var(--muted);
    }

    .inline-ok {
        display: none;
        align-items: center;
        gap: 5px;
        font-size: .76rem;
        color: var(--green, #00E396);
        font-weight: 600;
    }

    /* Spinner */
    .spinner {
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 2px solid rgba(255, 255, 255, .3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin .5s linear infinite;
        vertical-align: middle;
        margin-right: 4px;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Field errors */
    .field-error {
        display: none;
        font-size: .73rem;
        color: #FF4757;
        margin-top: 4px;
    }

    /* Badges */
    .bdg-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .bdg {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 3px;
        width: 54px;
    }

    .bdg-ico {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        transition: transform .2s;
    }

    .bdg-ico:hover {
        transform: scale(1.1) rotate(-5deg);
    }

    .bdg.earned .bdg-ico {
        background: rgba(124, 92, 252, .14);
        border: 1.5px solid rgba(124, 92, 252, .35);
    }

    .bdg.locked .bdg-ico {
        background: rgba(255, 255, 255, .04);
        border: 1.5px solid var(--border);
        filter: grayscale(1);
        opacity: .32;
    }

    .bdg-nm {
        font-size: .59rem;
        color: var(--muted);
        text-align: center;
        line-height: 1.2;
    }

    /* Danger */
    .danger-row {
        background: rgba(255, 71, 87, .05);
        border: 1px solid rgba(255, 71, 87, .22);
        border-radius: 13px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .danger-info strong {
        color: #FF4757;
        display: block;
        font-size: .86rem;
        margin-bottom: 2px;
    }

    .danger-info span {
        font-size: .75rem;
        color: var(--muted);
    }

    .btn-destroy {
        display: inline-block;
        background: rgba(255, 71, 87, .11);
        color: #FF4757;
        border: 1px solid rgba(255, 71, 87, .28);
        border-radius: 8px;
        padding: 8px 16px;
        font-size: .78rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: background .15s, transform .15s;
        white-space: nowrap;
    }

    .btn-destroy:hover {
        background: rgba(255, 71, 87, .22);
        transform: translateY(-1px);
    }
</style>


@section('content')

    <div class="orb o1"></div>
    <div class="orb o2"></div>

    <div class="page profile-wrap">

        {{-- ── First-time banner (only when profile not yet complete) ── --}}
        @if (!$student->is_profile_complete)
            <div class="setup-banner" id="setupBanner">
                <div class="setup-banner-icon">🎓</div>
                <div class="setup-banner-text">
                    <h3>Welcome to QuizMind! Let's set up your profile.</h3>
                    <p>Fill in the sections below and hit <strong>Save</strong> — required fields are marked *.</p>
                </div>
            </div>
        @endif

        <div class="profile-grid">

            <div>
                <div class="identity-card anim-fade">
                    <div class="identity-cover"></div>

                    <div class="id-avatar-wrap">
                        <div class="id-avatar" id="previewAvatar">{{ $student->avatar ?: '🧑‍🎓' }}</div>
                        <div class="lv-pip">Lv.{{ $student->level }} · {{ $student->level_title }}</div>
                    </div>

                    <div class="id-body">
                        <div class="id-name {{ $student->display_name ? '' : 'ph' }}" id="previewName">
                            {{ $student->display_name ?: 'Your name…' }}
                        </div>
                        <div class="id-handle" id="previewHandle">
                            @{{ $student - > display_name ? Str::slug($student - > display_name, '.') : 'username' }}
                        </div>
                        <div class="id-bio {{ $student->bio ? '' : 'empty' }}" id="previewBio">
                            {{ $student->bio ?: 'No bio yet…' }}
                        </div>

                        <div class="chip-row" id="previewChips">
                            @if ($student->age)
                                <span class="chip">🎂 {{ $student->age }}y</span>
                            @endif
                            @if ($student->class)
                                <span class="chip">📚 {{ $student->class }}</span>
                            @endif
                            @if ($student->school_name)
                                <span class="chip">🏫 {{ Str::limit($student->school_name, 20) }}</span>
                            @endif
                        </div>

                        <div class="xp-area">
                            <div class="xp-row-sm">
                                <span>{{ number_format($student->xp) }} XP</span>
                                <span>Next: {{ number_format($student->xpToNextLevel) }} XP</span>
                            </div>
                            <div class="xp-track-sm">
                                <div class="xp-bar-sm" id="previewXpBar" style="width:0%"></div>
                            </div>
                        </div>

                        <div class="subj-pills" id="previewSubjects">
                            @foreach ($student->subjects_interest ?? [] as $s)
                                <span class="subj-pill">{{ $s }}</span>
                            @endforeach
                        </div>

                        <div class="mini-stats-grid">
                            <div class="mstat">
                                <div class="mstat-val grad">{{ $student->total_quizzes }}</div>
                                <div class="mstat-label">Quizzes</div>
                            </div>
                            <div class="mstat">
                                <div class="mstat-val cyan">{{ $student->accuracy }}%</div>
                                <div class="mstat-label">Accuracy</div>
                            </div>
                            <div class="mstat">
                                <div class="mstat-val gold">{{ $student->win_rate }}%</div>
                                <div class="mstat-label">Win Rate</div>
                            </div>
                            <div class="mstat">
                                <div class="mstat-val green">{{ $student->streak }}</div>
                                <div class="mstat-label">Streak 🔥</div>
                            </div>
                        </div>

                        <div style="margin-top:14px;text-align:center">
                            <a href="{{ route('student.dashboard') }}" class="btn btn-ghost btn-sm"
                                style="width:100%;justify-content:center">
                                ← Back to Dashboard
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════
             RIGHT — Edit sections
        ══════════════════════════════ --}}
            <div class="edit-stack">

                {{-- SECTION 1: Identity & Avatar --}}
                <div class="sec-card anim-fade" style="animation-delay:.05s">
                    <div class="sec-head">
                        <div class="sec-icon">👤</div>
                        Identity &amp; Avatar
                    </div>
                    <div class="sec-body">

                        <div class="av-pick-row">
                            <div class="av-circle" id="pickerCircle">{{ $student->avatar ?: '🧑‍🎓' }}</div>
                            <div class="emo-grid">
                                @foreach (['🧑‍🎓', '👩‍🎓', '🦸', '🧙', '🧚', '🦊', '🐉', '🦁', '🐧', '⚡', '🌟', '🔥', '🎯', '🏆', '🚀', '🎮', '🎨', '🎵', '🦋', '🌈'] as $e)
                                    <button class="emo-btn {{ ($student->avatar ?? '🧑‍🎓') === $e ? 'selected' : '' }}"
                                        data-emoji="{{ $e }}" type="button">{{ $e }}</button>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Display Name *</label>
                            <input type="text" class="form-input" id="f_display_name"
                                value="{{ $student->display_name }}" maxlength="60" placeholder="How others will see you…"
                                oninput="liveUpdate()">
                            <div class="field-error" id="err_name">Please enter your display name.</div>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label class="form-label">Age *</label>
                                <input type="number" class="form-input" id="f_age" value="{{ $student->age }}"
                                    min="5" max="30" placeholder="e.g. 16" oninput="liveUpdate()">
                                <div class="field-error" id="err_age">Valid age (5–30) required.</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Class / Grade *</label>
                                <select class="form-input" id="f_class" onchange="liveUpdate()">
                                    <option value="">Select class…</option>
                                    @foreach (['Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10', 'Class 11', 'Class 12', '1st Year UG', '2nd Year UG', '3rd Year UG', '4th Year UG', 'Postgraduate', 'Other'] as $c)
                                        <option value="{{ $c }}"
                                            {{ $student->class === $c ? 'selected' : '' }}>{{ $c }}</option>
                                    @endforeach
                                </select>
                                <div class="field-error" id="err_class">Please select your class.</div>
                            </div>
                        </div>

                        <div class="save-row">
                            <div class="inline-ok" id="ok_identity">✅ Saved!</div>
                            <span class="save-hint">Updates name, avatar, age &amp; grade.</span>
                            <button class="btn btn-grad btn-sm" id="btn_identity" onclick="saveSection('identity')">
                                💾 Save
                            </button>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: School + Subjects --}}
                <div class="sec-card anim-fade" style="animation-delay:.09s">
                    <div class="sec-head">
                        <div class="sec-icon">🏫</div>
                        School &amp; Subjects
                    </div>
                    <div class="sec-body">

                        <div class="form-group">
                            <label class="form-label">School / College Name *</label>
                            <input type="text" class="form-input" id="f_school" value="{{ $student->school_name }}"
                                maxlength="120" placeholder="Your institution name…" oninput="liveUpdate()">
                            <div class="field-error" id="err_school">Please enter your school name.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Subjects of Interest
                                <span style="font-weight:400;text-transform:none;letter-spacing:0">(tap to toggle)</span>
                            </label>
                            <div class="tag-cloud" id="subjectCloud">
                                @php $selSubs = $student->subjects_interest ?? []; @endphp
                                @foreach (['Mathematics', 'Physics', 'Chemistry', 'Biology', 'History', 'Geography', 'English', 'Hindi', 'Computer Sci', 'Economics', 'Political Sci', 'Psychology', 'Art', 'Music', 'Sports'] as $s)
                                    <span class="subj-tag {{ in_array($s, $selSubs) ? 'selected' : '' }}"
                                        data-subject="{{ $s }}">{{ $s }}</span>
                                @endforeach
                            </div>
                        </div>

                        <div class="save-row">
                            <div class="inline-ok" id="ok_school">✅ Saved!</div>
                            <span class="save-hint">Updates school &amp; subject preferences.</span>
                            <button class="btn btn-grad btn-sm" id="btn_school" onclick="saveSection('school')">
                                💾 Save
                            </button>
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: Bio --}}
                <div class="sec-card anim-fade" style="animation-delay:.13s">
                    <div class="sec-head">
                        <div class="sec-icon">📝</div>
                        About Me
                    </div>
                    <div class="sec-body">
                        <div class="form-group">
                            <label class="form-label">Bio
                                <span style="font-weight:400;text-transform:none;letter-spacing:0">(optional, max 300
                                    chars)</span>
                            </label>
                            <textarea class="form-input" id="f_bio" rows="4" maxlength="300"
                                placeholder="Tell the QuizMind community about yourself…" oninput="liveUpdate()">{{ $student->bio }}</textarea>
                            <div style="text-align:right;font-size:.7rem;color:var(--muted);margin-top:4px"
                                id="bioCounter">
                                {{ strlen($student->bio ?? '') }} / 300
                            </div>
                        </div>

                        <div class="save-row">
                            <div class="inline-ok" id="ok_bio">✅ Saved!</div>
                            <span class="save-hint">Visible on your public profile.</span>
                            <button class="btn btn-grad btn-sm" id="btn_bio" onclick="saveSection('bio')">
                                💾 Save
                            </button>
                        </div>
                    </div>
                </div>

                {{-- SECTION 4: Badges (read-only) --}}
                <div class="sec-card anim-fade" style="animation-delay:.17s">
                    <div class="sec-head">
                        <div class="sec-icon">🎖️</div>
                        My Badges
                    </div>
                    <div class="sec-body">
                        @php
                            $badges = [
                                ['emoji' => '🔥', 'name' => 'Streak 7', 'earned' => $student->streak >= 7],
                                ['emoji' => '⚡', 'name' => 'First Quiz', 'earned' => $student->total_quizzes >= 1],
                                ['emoji' => '⚔️', 'name' => 'First Win', 'earned' => $student->total_battles_won >= 1],
                                ['emoji' => '🏆', 'name' => 'Top 10', 'earned' => false],
                                ['emoji' => '💯', 'name' => 'Perfect', 'earned' => false],
                                ['emoji' => '🌟', 'name' => 'Scholar', 'earned' => $student->level >= 10],
                                ['emoji' => '🧠', 'name' => 'Brainiac', 'earned' => false],
                                ['emoji' => '👑', 'name' => 'Champion', 'earned' => false],
                            ];
                        @endphp
                        <div class="bdg-grid">
                            @foreach ($badges as $b)
                                <div class="bdg {{ $b['earned'] ? 'earned' : 'locked' }}"
                                    title="{{ $b['earned'] ? '✅ Earned' : '🔒 Locked — keep going!' }}">
                                    <div class="bdg-ico">{{ $b['emoji'] }}</div>
                                    <span class="bdg-nm">{{ $b['name'] }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p style="font-size:.73rem;color:var(--muted);margin-top:13px">
                            🔒 Greyed badges unlock as you quiz, battle, and level up.
                        </p>
                    </div>
                </div>

                {{-- Danger zone --}}
                <div class="danger-row anim-fade" style="animation-delay:.21s">
                    <div class="danger-info">
                        <strong>⚠️ Delete Account</strong>
                        <span>Permanently removes your account and all data. Cannot be undone.</span>
                    </div>
                    <a href="{{ route('student.account.delete.page') }}" class="btn-destroy">
                        🗑️ Delete Account
                    </a>
                </div>

            </div>{{-- /edit-stack --}}
        </div>{{-- /profile-grid --}}
    </div>



    <script>
        let selAvatar = '{{ $student->avatar ?? '🧑‍🎓' }}';
        let selSubjects = @json($student->subjects_interest ?? []);


        document.addEventListener('DOMContentLoaded', () => {

            // XP bar entrance
            setTimeout(() => {
                const bar = document.getElementById('previewXpBar');
                if (bar) bar.style.width = '{{ $student->xp_progress }}%';
            }, 350);

            // Avatar buttons
            document.querySelectorAll('.emo-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.emo-btn').forEach(b => b.classList.remove(
                        'selected'));
                    btn.classList.add('selected');
                    selAvatar = btn.dataset.emoji;
                    document.getElementById('pickerCircle').textContent = selAvatar;
                    document.getElementById('previewAvatar').textContent = selAvatar;
                });
            });

            // Subject tags
            document.querySelectorAll('#subjectCloud .subj-tag').forEach(tag => {
                tag.addEventListener('click', () => {
                    tag.classList.toggle('selected');
                    const s = tag.dataset.subject;
                    tag.classList.contains('selected') ?
                        (!selSubjects.includes(s) && selSubjects.push(s)) :
                        (selSubjects = selSubjects.filter(x => x !== s));
                    refreshSubjectPills();
                });
            });

            // Bio counter
            const bio = document.getElementById('f_bio');
            if (bio) bio.addEventListener('input', () => {
                document.getElementById('bioCounter').textContent = `${bio.value.length} / 300`;
            });

            liveUpdate(); // sync preview on load
        });

        /* ════════════════════════════════════
           LIVE PREVIEW
        ════════════════════════════════════ */
        function liveUpdate() {
            const name = (document.getElementById('f_display_name')?.value || '').trim();
            const age = document.getElementById('f_age')?.value || '';
            const cls = document.getElementById('f_class')?.value || '';
            const school = (document.getElementById('f_school')?.value || '').trim();
            const bio = (document.getElementById('f_bio')?.value || '').trim();

            // Name
            const nm = document.getElementById('previewName');
            if (nm) {
                nm.textContent = name || 'Your name…';
                nm.className = 'id-name' + (name ? '' : ' ph');
            }

            // Handle
            const hd = document.getElementById('previewHandle');
            if (hd) hd.textContent = '@' + (name ? name.toLowerCase().replace(/\s+/g, '.') : 'username');

            // Bio
            const bEl = document.getElementById('previewBio');
            if (bEl) {
                bEl.textContent = bio || 'No bio yet…';
                bEl.className = 'id-bio' + (bio ? '' : ' empty');
            }

            // Chips
            const cr = document.getElementById('previewChips');
            if (cr) {
                cr.innerHTML = '';
                if (age) cr.innerHTML += `<span class="chip">🎂 ${age}y</span>`;
                if (cls) cr.innerHTML += `<span class="chip">📚 ${cls}</span>`;
                if (school) cr.innerHTML += `<span class="chip">🏫 ${school.slice(0,20)}${school.length>20?'…':''}</span>`;
            }
        }

        function refreshSubjectPills() {
            const el = document.getElementById('previewSubjects');
            if (!el) return;
            el.innerHTML = selSubjects.map(s => `<span class="subj-pill">${s}</span>`).join('');
        }

        /* ════════════════════════════════════
           VALIDATION
        ════════════════════════════════════ */
        const showErr = id => {
            const e = document.getElementById(id);
            if (e) e.style.display = 'block';
        };
        const hideErr = id => {
            const e = document.getElementById(id);
            if (e) e.style.display = 'none';
        };

        function validateIdentity() {
            let ok = true;
            const name = (document.getElementById('f_display_name')?.value || '').trim();
            const age = parseInt(document.getElementById('f_age')?.value);
            const cls = document.getElementById('f_class')?.value;
            !name ? (showErr('err_name'), ok = false) : hideErr('err_name');
            (!age || age < 5 || age > 30) ? (showErr('err_age'), ok = false) : hideErr('err_age');
            !cls ? (showErr('err_class'), ok = false) : hideErr('err_class');
            return ok;
        }

        function validateSchool() {
            const s = (document.getElementById('f_school')?.value || '').trim();
            if (!s) {
                showErr('err_school');
                return false;
            }
            hideErr('err_school');
            return true;
        }

        function saveSection(section) {
            if (section === 'identity' && !validateIdentity()) return;
            if (section === 'school' && !validateSchool()) return;

            const btn = document.getElementById(`btn_${section}`);
            const okEl = document.getElementById(`ok_${section}`);
            const orig = btn.innerHTML;
            btn.innerHTML = '<span class="spinner"></span> Saving…';
            btn.disabled = true;

            const reset = () => {
                btn.innerHTML = orig;
                btn.disabled = false;
            };

            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!token) {
                qmToast?.('error', 'CSRF token missing. Refresh page.');
                reset();
                return;
            }

            // Always read all fields so the single saveProfile route
            // receives a complete valid payload regardless of which section
            const fd = new FormData();
            fd.append('_token', token);
            fd.append('display_name', (document.getElementById('f_display_name')?.value || '').trim() ||
                '{{ addslashes($student->display_name) }}');
            fd.append('age', document.getElementById('f_age')?.value || '{{ $student->age }}');
            fd.append('class', document.getElementById('f_class')?.value || '{{ $student->class }}');
            fd.append('school_name', (document.getElementById('f_school')?.value || '').trim() ||
                '{{ addslashes($student->school_name) }}');
            fd.append('bio', (document.getElementById('f_bio')?.value || '').trim());
            fd.append('avatar', selAvatar);
            selSubjects.forEach(s => s && fd.append('subjects_interest[]', s));

            fetch("{{ route('student.profile.save') }}", {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => {
                    if (!r.ok) return r.json().then(e => {
                        throw {
                            status: r.status,
                            data: e
                        };
                    });
                    return r.json();
                })
                .then(data => {
                    if (data.success) {
                        // Hide first-time banner after first successful save
                        document.getElementById('setupBanner')?.remove();

                        // Show success message
                        qmToast?.('success', '✅ Profile updated!');

                        // Update button to show success state
                        btn.innerHTML = '✅ Saved!';
                        btn.disabled = true;

                        // Show inline success indicator
                        if (okEl) {
                            okEl.style.display = 'flex';
                            setTimeout(() => {
                                okEl.style.display = 'none';
                            }, 3000);
                        }

                        // Keep the "Saved" state for longer to confirm to user
                        // Then reset after 3.5 seconds
                        setTimeout(() => {
                            reset();
                        }, 3500);
                        return;
                    }
                    if (data.errors) Object.values(data.errors).forEach(e => qmToast?.('error', e[0]));
                    else qmToast?.('error', data.message || 'Something went wrong.');
                    reset();
                })
                .catch(err => {
                    if (err.status === 419) qmToast?.('error', 'Session expired. Refresh page.');
                    else if (err.status === 422) Object.values(err.data?.errors || {}).forEach(e => qmToast?.('error',
                        e[0]));
                    else qmToast?.('error', 'Network error. Please try again.');
                    reset();
                });
        }
    </script>
@endsection
