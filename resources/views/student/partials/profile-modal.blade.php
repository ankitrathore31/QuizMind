  <div class="modal-overlay {{ $student->is_profile_complete ? 'hidden' : '' }}" id="setupModal">
      <div class="setup-modal" id="setupModalCard">

          <div class="modal-header">
              <span class="modal-wizard-emoji">🎓</span>
              <h2 class="modal-title">Welcome to <span class="accent">QuizMind!</span></h2>
              <p class="modal-sub">Let's set up your student profile in just a few steps. This helps us personalise
                  your experience.</p>
          </div>

          <!-- Steps indicator -->
          <div class="steps-indicator">
              <div class="step-dot active" id="dot0"></div>
              <div class="step-dot" id="dot1"></div>
              <div class="step-dot" id="dot2"></div>
          </div>

          <div class="step-content">

              <!-- ── Step 0: Identity ── -->
              <div class="step-pane active" id="step0">
                  <div style="margin-bottom:20px;">
                      <p
                          style="font-size:.8rem;color:var(--muted);font-family:var(--fh);font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:14px;">
                          Choose Your Avatar</p>
                      <div class="avatar-picker">
                          <div class="avatar-preview" id="avatarPreview">🧑‍🎓</div>
                          <div class="avatar-emojis">
                              @php
                                  $avatars = ['🧑‍🎓', '👩‍🎓', '🦸', '🧙', '🧚', '🦊', '🐉', '🦁', '🐧', '⚡', '🌟', '🔥'];
                              @endphp
                              @foreach ($avatars as $e)
                                  <button class="avatar-emoji-btn" data-emoji="{{ $e }}"
                                      type="button">{{ $e }}</button>
                              @endforeach
                          </div>
                      </div>
                  </div>

                  <div class="form-group">
                      <label class="form-label">Display Name *</label>
                      <input type="text" class="form-input" id="field_display_name"
                          placeholder="How others will see you…" maxlength="60">
                      <div class="field-error" id="err_display_name">Please enter your display name.</div>
                  </div>

                  <div class="form-row">
                      <div class="form-group">
                          <label class="form-label">Age *</label>
                          <input type="number" class="form-input" id="field_age" placeholder="e.g. 16" min="5"
                              max="30">
                          <div class="field-error" id="err_age">Please enter your age (5–30).</div>
                      </div>
                      <div class="form-group">
                          <label class="form-label">Class / Grade *</label>
                          <select class="form-input" id="field_class">
                              <option value="">Select class…</option>
                              @foreach (['Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10', 'Class 11', 'Class 12', '1st Year UG', '2nd Year UG', '3rd Year UG', '4th Year UG', 'Postgraduate', 'Other'] as $c)
                                  <option value="{{ $c }}">{{ $c }}</option>
                              @endforeach
                          </select>
                          <div class="field-error" id="err_class">Please select your class.</div>
                      </div>
                  </div>
              </div>

              <!-- ── Step 1: School & Subjects ── -->
              <div class="step-pane" id="step1">
                  <div class="form-group">
                      <label class="form-label">School / College Name *</label>
                      <input type="text" class="form-input" id="field_school_name"
                          placeholder="Your institution name…" maxlength="120">
                      <div class="field-error" id="err_school_name">Please enter your school name.</div>
                  </div>

                  <div class="form-group">
                      <label class="form-label">Subjects you're interested in</label>
                      <div class="subject-tags" id="subjectTags">
                          @php
                              $subjects = [
                                  'Mathematics',
                                  'Physics',
                                  'Chemistry',
                                  'Biology',
                                  'History',
                                  'Geography',
                                  'English',
                                  'Hindi',
                                  'Computer Sci',
                                  'Economics',
                                  'Political Sci',
                                  'Psychology',
                                  'Art',
                                  'Music',
                                  'Sports',
                              ];
                          @endphp
                          @foreach ($subjects as $s)
                              <span class="subject-tag" data-subject="{{ $s }}">{{ $s }}</span>
                          @endforeach
                      </div>
                  </div>
              </div>

              <!-- ── Step 2: Bio & Finish ── -->
              <div class="step-pane" id="step2">
                  <div style="text-align:center;padding:10px 0 20px;">
                      <div style="font-size:3rem;margin-bottom:8px;animation:float 2s ease-in-out infinite;">🚀</div>
                      <p style="font-family:var(--fh);font-weight:700;font-size:1rem;margin-bottom:6px;">Almost
                          there!</p>
                      <p style="color:var(--muted);font-size:.85rem;">Add a quick bio (optional) and you're all set.
                      </p>
                  </div>
                  <div class="form-group">
                      <label class="form-label">Bio (Optional)</label>
                      <textarea class="form-input" id="field_bio" placeholder="Tell the QuizMind community a bit about yourself…"
                          maxlength="300"></textarea>
                      <div style="font-size:.72rem;color:var(--muted);margin-top:4px;text-align:right;" id="bioCounter">
                          0
                          / 300</div>
                  </div>
                  <div
                      style="background:rgba(124,92,252,0.06);border:1px solid rgba(124,92,252,0.15);border-radius:var(--radius-xs);padding:14px 16px;margin-bottom:4px;">
                      <p style="font-family:var(--fh);font-size:.8rem;font-weight:700;margin-bottom:6px;">✨ What
                          you'll unlock:</p>
                      <p style="font-size:.8rem;color:var(--muted);line-height:1.8;">
                          🔥 Daily streak tracking &nbsp;|&nbsp; ⚔️ Battle access<br>
                          🏆 Leaderboard ranking &nbsp;|&nbsp; 🎖️ XP & Level system
                      </p>
                  </div>
              </div>

          </div><!-- /step-content -->

          <div class="modal-footer">
              <span class="step-counter" id="stepCounter">Step 1 of 3</span>
              <div style="display:flex;gap:10px;">
                  <button class="btn btn-ghost btn-sm" id="prevBtn" style="display:none;" onclick="modalPrev()">←
                      Back</button>
                  <button class="btn btn-grad btn-sm" id="nextBtn" onclick="modalNext()">Continue →</button>
              </div>
          </div>
      </div><!-- /setup-modal -->
  </div><!-- /modal-overlay -->
