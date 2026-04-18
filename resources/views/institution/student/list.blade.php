@extends('institution.layout.master')

@section('page_title', '👥 Students')

@push('styles')
<style>
    /* ─── Search Bar ─── */
    .search-wrap {
        position: relative;
        margin-bottom: 20px;
    }

    .search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 15px;
        color: var(--muted);
        pointer-events: none;
    }

    .search-input {
        width: 100%;
        background: var(--card);
        border: 1px solid var(--border2);
        border-radius: var(--radius-sm);
        color: var(--text);
        font-family: var(--fb);
        font-size: 0.9rem;
        padding: 12px 14px 12px 42px;
        outline: none;
        transition: all 0.2s;
    }

    .search-input:focus {
        border-color: rgba(124,92,252,0.5);
        box-shadow: 0 0 0 3px rgba(124,92,252,0.1);
    }

    .search-input::placeholder { color: var(--muted); }

    .search-count {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.72rem;
        font-family: var(--fh);
        font-weight: 700;
        color: var(--muted);
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--border);
        padding: 2px 10px;
        border-radius: 20px;
    }

    /* ─── Table Card ─── */
    .table-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        position: relative;
    }

    .table-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
        background: var(--gradient);
        opacity: 0.6;
    }

    /* ─── Table ─── */
    .st-table {
        width: 100%;
        border-collapse: collapse;
    }

    .st-table thead tr {
        background: rgba(255,255,255,0.02);
        border-bottom: 1px solid var(--border);
    }

    .st-table thead th {
        font-family: var(--fh);
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--muted);
        padding: 14px 16px;
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
        transition: color 0.2s;
    }

    .st-table thead th:hover { color: var(--text); }

    .st-table thead th .sort-arrow {
        margin-left: 4px;
        opacity: 0.4;
        font-size: 10px;
    }

    .st-table thead th.sorted .sort-arrow { opacity: 1; color: var(--violet); }

    .st-table tbody tr {
        border-bottom: 1px solid rgba(255,255,255,0.03);
        cursor: pointer;
        transition: all 0.2s;
    }

    .st-table tbody tr:last-child { border-bottom: none; }

    .st-table tbody tr:hover {
        background: rgba(124,92,252,0.05);
    }

    .st-table tbody tr:hover .student-name-text {
        color: var(--violet);
    }

    .st-table tbody td {
        padding: 13px 16px;
        font-size: 0.84rem;
        vertical-align: middle;
    }

    /* ─── Student Row Elements ─── */
    .student-avatar-sm {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: var(--gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--fh);
        font-weight: 800;
        font-size: 14px;
        border: 2px solid rgba(124,92,252,0.2);
        flex-shrink: 0;
        transition: all 0.2s;
    }

    tr:hover .student-avatar-sm {
        border-color: rgba(124,92,252,0.5);
        box-shadow: 0 0 12px rgba(124,92,252,0.3);
    }

    .student-name-text {
        font-weight: 600;
        font-size: 0.87rem;
        transition: color 0.2s;
    }

    .student-email-text {
        font-size: 0.7rem;
        color: var(--muted);
    }

    .rank-medal { font-size: 1rem; }

    .xp-val {
        font-family: var(--fh);
        font-weight: 700;
        font-size: 0.85rem;
        color: var(--violet);
    }

    .level-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.72rem;
        font-family: var(--fh);
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        background: rgba(124,92,252,0.1);
        border: 1px solid rgba(124,92,252,0.2);
        color: var(--violet);
    }

    .acc-pill {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-family: var(--fh);
        font-weight: 700;
    }

    .acc-high { background: rgba(0,227,150,0.1);  color: var(--green); border: 1px solid rgba(0,227,150,0.2); }
    .acc-mid  { background: rgba(255,184,0,0.1);  color: var(--gold);  border: 1px solid rgba(255,184,0,0.2); }
    .acc-low  { background: rgba(255,77,106,0.1); color: var(--red);   border: 1px solid rgba(255,77,106,0.2); }

    .streak-val {
        font-family: var(--fh);
        font-weight: 700;
        font-size: 0.82rem;
        color: var(--orange);
    }

    .view-btn {
        font-size: 0.7rem;
        font-family: var(--fh);
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 6px;
        background: rgba(124,92,252,0.08);
        border: 1px solid rgba(124,92,252,0.2);
        color: var(--violet);
        transition: all 0.2s;
    }

    tr:hover .view-btn {
        background: rgba(124,92,252,0.18);
        border-color: rgba(124,92,252,0.4);
    }

    /* ─── Empty State ─── */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--muted);
    }

    .empty-state-icon { font-size: 3rem; margin-bottom: 12px; }
    .empty-state-title { font-family: var(--fh); font-size: 1rem; font-weight: 700; margin-bottom: 6px; }

    /* ─── Student Detail Modal ─── */
    .modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 999;
        background: rgba(8,8,15,0.88);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s;
    }

    .modal-overlay.open {
        opacity: 1;
        pointer-events: all;
    }

    .modal-panel {
        background: var(--card);
        border: 1px solid rgba(124,92,252,0.3);
        border-radius: 20px;
        width: 100%;
        max-width: 640px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 30px 80px rgba(0,0,0,0.6), 0 0 60px rgba(124,92,252,0.12);
        transform: scale(0.9) translateY(20px);
        transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
    }

    .modal-overlay.open .modal-panel {
        transform: scale(1) translateY(0);
    }

    .modal-close {
        position: absolute;
        top: 16px; right: 16px;
        width: 32px; height: 32px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
        border: 1px solid var(--border2);
        color: var(--muted);
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        z-index: 10;
    }

    .modal-close:hover {
        background: rgba(255,77,106,0.15);
        border-color: rgba(255,77,106,0.3);
        color: var(--red);
    }

    /* ─── Modal Hero ─── */
    .modal-hero {
        padding: 32px 28px 0;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .modal-hero::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 120px;
        background: linear-gradient(135deg, rgba(124,92,252,0.12), rgba(0,212,255,0.06));
    }

    .modal-hero-avatar {
        width: 80px; height: 80px;
        border-radius: 50%;
        background: var(--gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--fh);
        font-weight: 800;
        font-size: 30px;
        margin: 0 auto 14px;
        border: 3px solid rgba(124,92,252,0.5);
        box-shadow: 0 0 40px rgba(124,92,252,0.35);
        position: relative;
        z-index: 1;
    }

    .modal-hero-name {
        font-family: var(--fh);
        font-size: 1.2rem;
        font-weight: 800;
        margin-bottom: 4px;
        position: relative;
        z-index: 1;
    }

    .modal-hero-sub {
        font-size: 0.78rem;
        color: var(--muted);
        margin-bottom: 14px;
        position: relative;
        z-index: 1;
    }

    .modal-hero-tags {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 24px;
        position: relative;
        z-index: 1;
    }

    .hero-tag {
        font-size: 0.68rem;
        font-family: var(--fh);
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
    }

    /* ─── Modal Body ─── */
    .modal-body {
        padding: 0 24px 28px;
    }

    /* ─── XP / Level Ring ─── */
    .level-section {
        background: var(--card2);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 18px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .level-ring-wrap {
        width: 80px; height: 80px;
        flex-shrink: 0;
        position: relative;
    }

    .level-svg { width: 80px; height: 80px; transform: rotate(-90deg); }
    .level-bg   { fill: none; stroke: rgba(255,255,255,0.07); stroke-width: 7; }
    .level-fill { fill: none; stroke-width: 7; stroke-linecap: round; stroke: url(#levelGrad); transition: stroke-dashoffset 1.2s cubic-bezier(0.34, 1.56, 0.64, 1); }

    .level-ring-val {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .level-num {
        font-family: var(--fh);
        font-size: 1.3rem;
        font-weight: 800;
        line-height: 1;
    }

    .level-lbl {
        font-size: 0.52rem;
        color: var(--muted);
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .level-info { flex: 1; }

    .level-title-text {
        font-family: var(--fh);
        font-size: 1rem;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .level-title-badge {
        display: inline-block;
        font-size: 0.68rem;
        font-family: var(--fh);
        font-weight: 700;
        padding: 2px 10px;
        border-radius: 20px;
        background: rgba(124,92,252,0.12);
        border: 1px solid rgba(124,92,252,0.25);
        color: var(--violet);
        margin-bottom: 10px;
    }

    .xp-track {
        height: 7px;
        background: rgba(255,255,255,0.06);
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 5px;
    }

    .xp-fill-bar {
        height: 100%;
        border-radius: 4px;
        background: var(--gradient);
        width: 0%;
        transition: width 1.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .xp-row-text {
        display: flex;
        justify-content: space-between;
        font-size: 0.68rem;
        color: var(--muted);
    }

    .xp-current-text { color: var(--violet); font-weight: 700; }

    /* ─── Stats Grid in Modal ─── */
    .modal-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 16px;
    }

    .modal-stat {
        background: var(--card2);
        border: 1px solid var(--border);
        border-radius: var(--radius-xs);
        padding: 12px;
        text-align: center;
        transition: all 0.2s;
    }

    .modal-stat:hover {
        border-color: var(--border2);
        transform: translateY(-2px);
    }

    .ms-icon { font-size: 1.3rem; margin-bottom: 5px; display: block; }
    .ms-val  { font-family: var(--fh); font-size: 1.1rem; font-weight: 800; line-height: 1; margin-bottom: 3px; }
    .ms-lbl  { font-size: 0.64rem; color: var(--muted); font-weight: 500; }

    /* ─── Win Rate ─── */
    .battle-row {
        background: var(--card2);
        border: 1px solid var(--border);
        border-radius: var(--radius-xs);
        padding: 14px 16px;
        margin-bottom: 16px;
    }

    .battle-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        font-size: 0.78rem;
        font-family: var(--fh);
        font-weight: 700;
    }

    .battle-track {
        height: 8px;
        background: rgba(255,255,255,0.06);
        border-radius: 4px;
        overflow: hidden;
        position: relative;
    }

    .battle-win-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--green), var(--cyan));
        border-radius: 4px;
        transition: width 1.2s ease;
    }

    /* ─── Subjects ─── */
    .subjects-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 16px;
    }

    .subject-chip {
        font-size: 0.72rem;
        font-family: var(--fh);
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        background: rgba(0,212,255,0.08);
        border: 1px solid rgba(0,212,255,0.18);
        color: var(--cyan);
    }

    /* ─── Badges ─── */
    .badges-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .badge-chip {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 3px;
        padding: 8px 10px;
        background: var(--card2);
        border: 1px solid var(--border);
        border-radius: var(--radius-xs);
        min-width: 56px;
        font-size: 1.4rem;
        transition: all 0.2s;
    }

    .badge-chip:hover {
        border-color: rgba(124,92,252,0.3);
        transform: translateY(-3px);
    }

    .badge-chip-name {
        font-size: 0.6rem;
        font-family: var(--fh);
        font-weight: 700;
        color: var(--muted);
        text-align: center;
    }

    /* ─── Section Label inside modal ─── */
    .modal-section-lbl {
        font-family: var(--fh);
        font-size: 0.65rem;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 8px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .modal-section-lbl::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }

    /* ─── Fade-up ─── */
    .fade-up { opacity: 0; transform: translateY(18px); animation: fadeUp 0.45s ease forwards; }
    @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
    .d1 { animation-delay: 0.04s; }
    .d2 { animation-delay: 0.08s; }
    .d3 { animation-delay: 0.12s; }

    /* ─── Responsive ─── */
    @media (max-width: 640px) {
        .modal-stats { grid-template-columns: 1fr 1fr; }
        .modal-panel { max-height: 95vh; }
    }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px" class="fade-up">
    <div>
        <div style="font-family:var(--fh);font-size:0.7rem;letter-spacing:2px;color:var(--violet);text-transform:uppercase;font-weight:700;margin-bottom:4px">
            Institution Roster
        </div>
        <h2 style="font-family:var(--fh);font-size:1.6rem;font-weight:800;line-height:1">Students</h2>
        <div style="font-size:0.82rem;color:var(--muted);margin-top:4px">
            {{ $students->count() }} enrolled · Click any row to view full profile
        </div>
    </div>

    <div style="font-family:var(--fh);font-size:0.8rem;font-weight:700;padding:8px 18px;background:rgba(124,92,252,0.1);border:1px solid rgba(124,92,252,0.25);border-radius:var(--radius-xs);color:var(--violet)">
        👥 {{ $students->count() }} Total
    </div>
</div>

{{-- Search Bar --}}
<div class="search-wrap fade-up d1">
    <span class="search-icon">🔍</span>
    <input type="text"
           class="search-input"
           id="searchInput"
           placeholder="Search by name, email, level, class..."
           oninput="filterStudents(this.value)">
    <span class="search-count" id="searchCount">{{ $students->count() }} students</span>
</div>

{{-- Table Card --}}
<div class="table-card fade-up d2">
    <div style="overflow-x:auto">
        <table class="st-table">
            <thead>
                <tr>
                    <th style="width:50px">#</th>
                    <th onclick="sortTable(1)">Student <span class="sort-arrow">↕</span></th>
                    <th onclick="sortTable(2)">Level <span class="sort-arrow">↕</span></th>
                    <th onclick="sortTable(3)">XP <span class="sort-arrow">↕</span></th>
                    <th onclick="sortTable(4)">Accuracy <span class="sort-arrow">↕</span></th>
                    <th onclick="sortTable(5)">🔥 Streak <span class="sort-arrow">↕</span></th>
                    <th onclick="sortTable(6)">Battles <span class="sort-arrow">↕</span></th>
                    <th style="width:80px"></th>
                </tr>
            </thead>

            <tbody id="studentsTable">
                @forelse($students as $i => $s)
                    <tr onclick="openStudentModal({{ $s->id }})" data-id="{{ $s->id }}">

                        {{-- Rank --}}
                        <td>
                            @if($i == 0) <span class="rank-medal">🥇</span>
                            @elseif($i == 1) <span class="rank-medal">🥈</span>
                            @elseif($i == 2) <span class="rank-medal">🥉</span>
                            @else <span style="font-family:var(--fh);font-size:0.78rem;color:var(--muted)">#{{ $i + 1 }}</span>
                            @endif
                        </td>

                        {{-- Student --}}
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div class="student-avatar-sm">
                                    {{ strtoupper(substr($s->user->name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="student-name-text">{{ $s->user->name ?? 'Unknown' }}</div>
                                    <div class="student-email-text">{{ $s->user->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Level --}}
                        <td>
                            <span class="level-badge">
                                ⭐ Lv.{{ $s->level ?? 1 }}
                            </span>
                        </td>

                        {{-- XP --}}
                        <td>
                            <span class="xp-val">⚡ {{ number_format($s->xp ?? 0) }}</span>
                        </td>

                        {{-- Accuracy --}}
                        <td>
                            @php $acc = $s->accuracy ?? 0; @endphp
                            <span class="acc-pill {{ $acc >= 70 ? 'acc-high' : ($acc >= 40 ? 'acc-mid' : 'acc-low') }}">
                                🎯 {{ $acc }}%
                            </span>
                        </td>

                        {{-- Streak --}}
                        <td>
                            <span class="streak-val">🔥 {{ $s->streak ?? 0 }}</span>
                        </td>

                        {{-- Battles --}}
                        <td style="font-size:0.78rem;color:var(--muted)">
                            <span style="color:var(--green)">{{ $s->total_battles_won ?? 0 }}W</span>
                            /
                            <span style="color:var(--red)">{{ $s->total_battles_lost ?? 0 }}L</span>
                        </td>

                        {{-- View --}}
                        <td>
                            <span class="view-btn">Profile →</span>
                        </td>

                    </tr>
                @empty
                    <tr id="emptyRow">
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-state-icon">🎓</div>
                                <div class="empty-state-title">No students yet</div>
                                <div style="font-size:0.8rem">Share your institution code to get students enrolled</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- No results row (hidden by default) --}}
    <div id="noResults" style="display:none;text-align:center;padding:40px;color:var(--muted)">
        <div style="font-size:2rem;margin-bottom:8px">🔍</div>
        <div style="font-family:var(--fh);font-weight:700">No students match your search</div>
    </div>
</div>

{{-- ══════════════════════════════
     STUDENT DETAIL MODAL
══════════════════════════════ --}}
<div class="modal-overlay" id="studentModal" onclick="closeModalOutside(event)">
    <div class="modal-panel" id="modalPanel">

        <button class="modal-close" onclick="closeStudentModal()">✕</button>

        {{-- Hero --}}
        <div class="modal-hero" id="modalHero">
            <div class="modal-hero-avatar" id="modalAvatar">?</div>
            <div class="modal-hero-name" id="modalName">—</div>
            <div class="modal-hero-sub" id="modalSub">—</div>
            <div class="modal-hero-tags" id="modalTags"></div>
        </div>

        {{-- Body --}}
        <div class="modal-body">

            {{-- XP / Level Ring --}}
            <div class="modal-section-lbl">Level Progress</div>
            <div class="level-section">
                <div class="level-ring-wrap">
                    <svg class="level-svg" viewBox="0 0 80 80">
                        <defs>
                            <linearGradient id="levelGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#7C5CFC"/>
                                <stop offset="100%" style="stop-color:#00D4FF"/>
                            </linearGradient>
                        </defs>
                        <circle class="level-bg"   cx="40" cy="40" r="33"/>
                        <circle class="level-fill" cx="40" cy="40" r="33" id="levelRingCircle"
                            stroke-dasharray="207.3"
                            stroke-dashoffset="207.3"/>
                    </svg>
                    <div class="level-ring-val">
                        <div class="level-num" id="modalLevel">1</div>
                        <div class="level-lbl">LEVEL</div>
                    </div>
                </div>

                <div class="level-info">
                    <div class="level-title-text" id="modalLevelTitle">Novice</div>
                    <div class="level-title-badge" id="modalLevelBadge">Novice</div>
                    <div class="xp-track">
                        <div class="xp-fill-bar" id="modalXpBar"></div>
                    </div>
                    <div class="xp-row-text">
                        <span class="xp-current-text" id="modalXpCurrent">0 XP</span>
                        <span id="modalXpNext">/ 0 XP needed</span>
                    </div>
                </div>
            </div>

            {{-- Stats Grid --}}
            <div class="modal-section-lbl">Performance Stats</div>
            <div class="modal-stats">
                <div class="modal-stat">
                    <span class="ms-icon">✅</span>
                    <div class="ms-val" id="mCorrect" style="color:var(--green)">0</div>
                    <div class="ms-lbl">Correct</div>
                </div>
                <div class="modal-stat">
                    <span class="ms-icon">❌</span>
                    <div class="ms-val" id="mWrong" style="color:var(--red)">0</div>
                    <div class="ms-lbl">Wrong</div>
                </div>
                <div class="modal-stat">
                    <span class="ms-icon">📚</span>
                    <div class="ms-val" id="mQuizzes" style="color:var(--cyan)">0</div>
                    <div class="ms-lbl">Quizzes</div>
                </div>
                <div class="modal-stat">
                    <span class="ms-icon">🎯</span>
                    <div class="ms-val" id="mAccuracy" style="color:var(--violet)">0%</div>
                    <div class="ms-lbl">Accuracy</div>
                </div>
                <div class="modal-stat">
                    <span class="ms-icon">🔥</span>
                    <div class="ms-val" id="mStreak" style="color:var(--orange)">0</div>
                    <div class="ms-lbl">Streak</div>
                </div>
                <div class="modal-stat">
                    <span class="ms-icon">⚡</span>
                    <div class="ms-val" id="mXp" style="color:var(--violet)">0</div>
                    <div class="ms-lbl">Total XP</div>
                </div>
            </div>

            {{-- Battle Win Rate --}}
            <div class="modal-section-lbl">Battle Record</div>
            <div class="battle-row">
                <div class="battle-header">
                    <span style="color:var(--green)" id="mBattleWins">0 Wins</span>
                    <span style="color:var(--violet)" id="mWinRate">0% Win Rate</span>
                    <span style="color:var(--red)" id="mBattleLosses">0 Losses</span>
                </div>
                <div class="battle-track">
                    <div class="battle-win-fill" id="mWinBar" style="width:0%"></div>
                </div>
            </div>

            {{-- Subjects --}}
            <div id="subjectsSection" style="display:none;margin-bottom:16px">
                <div class="modal-section-lbl">Subjects of Interest</div>
                <div class="subjects-wrap" id="mSubjects"></div>
            </div>

            {{-- Badges --}}
            <div id="badgesSection" style="display:none">
                <div class="modal-section-lbl">Badges Earned</div>
                <div class="badges-wrap" id="mBadges"></div>
            </div>

        </div>
    </div>
</div>

{{-- Inline student data --}}
<script>
const STUDENTS = {
    @foreach($students as $s)
    {{ $s->id }}: {
        id:           {{ $s->id }},
        name:         @json($s->user->name ?? 'Unknown'),
        email:        @json($s->user->email ?? ''),
        avatar:       @json(strtoupper(substr($s->user->name ?? '?', 0, 1))),
        display_name: @json($s->display_name ?? ''),
        class:        @json($s->class ?? ''),
        age:          {{ $s->age ?? 0 }},
        school_name:  @json($s->school_name ?? ''),
        bio:          @json($s->bio ?? ''),
        level:        {{ $s->level ?? 1 }},
        xp:           {{ $s->xp ?? 0 }},
        xp_to_next:   {{ (int)(100 * pow($s->level ?? 1, 1.5)) }},
        xp_progress:  {{ $s->xp_progress ?? 0 }},
        level_title:  @json($s->level_title ?? 'Novice'),
        streak:       {{ $s->streak ?? 0 }},
        total_quizzes:       {{ $s->total_quizzes ?? 0 }},
        total_correct:       {{ $s->total_correct ?? 0 }},
        total_wrong:         {{ $s->total_wrong ?? 0 }},
        total_battles_won:   {{ $s->total_battles_won ?? 0 }},
        total_battles_lost:  {{ $s->total_battles_lost ?? 0 }},
        accuracy:    {{ $s->accuracy ?? 0 }},
        win_rate:    {{ $s->win_rate ?? 0 }},
        subjects:    @json($s->subjects_interest ?? []),
        badges:      @json($s->badges ?? []),
        rank:        @json($s->rank ?? ''),
    },
    @endforeach
};
</script>


<script>
    // ── Filter / Search ──
    const allRows = () => document.querySelectorAll('#studentsTable tr[data-id]');
    let visibleCount = allRows().length;

    function filterStudents(q) {
        q = q.toLowerCase().trim();
        let count = 0;
        allRows().forEach(row => {
            const txt = row.innerText.toLowerCase();
            const show = !q || txt.includes(q);
            row.style.display = show ? '' : 'none';
            if (show) count++;
        });
        visibleCount = count;
        document.getElementById('searchCount').textContent = count + ' students';
        document.getElementById('noResults').style.display = count === 0 ? 'block' : 'none';
    }

    // ── Sort Table ──
    let sortDir = {};
    function sortTable(colIdx) {
        const tbody = document.getElementById('studentsTable');
        const rows  = Array.from(tbody.querySelectorAll('tr[data-id]'));
        const dir   = sortDir[colIdx] = !(sortDir[colIdx]);

        rows.sort((a, b) => {
            const aVal = a.cells[colIdx]?.innerText.replace(/[^0-9.]/g, '') || a.cells[colIdx]?.innerText || '';
            const bVal = b.cells[colIdx]?.innerText.replace(/[^0-9.]/g, '') || b.cells[colIdx]?.innerText || '';
            const aNum = parseFloat(aVal), bNum = parseFloat(bVal);
            if (!isNaN(aNum) && !isNaN(bNum)) return dir ? aNum - bNum : bNum - aNum;
            return dir ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
        });

        rows.forEach(r => tbody.appendChild(r));

        document.querySelectorAll('.st-table thead th').forEach((th, i) => {
            th.classList.toggle('sorted', i === colIdx);
            const arrow = th.querySelector('.sort-arrow');
            if (arrow && i === colIdx) arrow.textContent = dir ? '↑' : '↓';
            else if (arrow) arrow.textContent = '↕';
        });
    }

    // ── Open Modal ──
    function openStudentModal(id) {
        const s = STUDENTS[id];
        if (!s) return;

        // Hero
        document.getElementById('modalAvatar').textContent = s.avatar;
        document.getElementById('modalName').textContent   = s.display_name || s.name;
        document.getElementById('modalSub').textContent    = s.email + (s.class ? ' · Class ' + s.class : '') + (s.age ? ' · Age ' + s.age : '');

        // Tags
        const tags = document.getElementById('modalTags');
        tags.innerHTML = '';
        const tagData = [
            { label: '⭐ Level ' + s.level,   bg: 'rgba(124,92,252,0.12)', bc: 'rgba(124,92,252,0.25)', color: 'var(--violet)' },
            { label: s.level_title,            bg: 'rgba(0,212,255,0.08)',  bc: 'rgba(0,212,255,0.2)',  color: 'var(--cyan)' },
            { label: '🔥 ' + s.streak + ' Streak', bg: 'rgba(255,140,66,0.1)', bc: 'rgba(255,140,66,0.25)', color: 'var(--orange)' },
        ];
        if (s.rank) tagData.push({ label: '🏅 ' + s.rank, bg: 'rgba(255,184,0,0.1)', bc: 'rgba(255,184,0,0.2)', color: 'var(--gold)' });
        tagData.forEach(t => {
            const span = document.createElement('span');
            span.className = 'hero-tag';
            span.style.cssText = `background:${t.bg};border:1px solid ${t.bc};color:${t.color}`;
            span.textContent = t.label;
            tags.appendChild(span);
        });

        // Level ring
        document.getElementById('modalLevel').textContent       = s.level;
        document.getElementById('modalLevelTitle').textContent  = s.level_title;
        document.getElementById('modalLevelBadge').textContent  = s.level_title;

        const circumference = 207.3;
        const offset = circumference - (s.xp_progress / 100) * circumference;
        setTimeout(() => {
            document.getElementById('levelRingCircle').style.strokeDashoffset = offset;
            document.getElementById('modalXpBar').style.width = s.xp_progress + '%';
        }, 100);

        document.getElementById('modalXpCurrent').textContent = s.xp.toLocaleString() + ' XP';
        document.getElementById('modalXpNext').textContent    = '/ ' + s.xp_to_next.toLocaleString() + ' XP needed';

        // Stats
        document.getElementById('mCorrect').textContent = s.total_correct.toLocaleString();
        document.getElementById('mWrong').textContent   = s.total_wrong.toLocaleString();
        document.getElementById('mQuizzes').textContent = s.total_quizzes.toLocaleString();
        document.getElementById('mAccuracy').textContent= s.accuracy + '%';
        document.getElementById('mStreak').textContent  = s.streak;
        document.getElementById('mXp').textContent      = s.xp.toLocaleString();

        // Battle
        document.getElementById('mBattleWins').textContent   = s.total_battles_won + ' Wins';
        document.getElementById('mBattleLosses').textContent = s.total_battles_lost + ' Losses';
        document.getElementById('mWinRate').textContent      = s.win_rate + '% Win Rate';
        setTimeout(() => {
            document.getElementById('mWinBar').style.width = s.win_rate + '%';
        }, 150);

        // Subjects
        const subjectsSection = document.getElementById('subjectsSection');
        const mSubjects = document.getElementById('mSubjects');
        if (s.subjects && s.subjects.length > 0) {
            mSubjects.innerHTML = s.subjects.map(sub =>
                `<span class="subject-chip">${sub}</span>`
            ).join('');
            subjectsSection.style.display = 'block';
        } else {
            subjectsSection.style.display = 'none';
        }

        // Badges
        const badgesSection = document.getElementById('badgesSection');
        const mBadges = document.getElementById('mBadges');
        if (s.badges && s.badges.length > 0) {
            mBadges.innerHTML = s.badges.map(b =>
                `<div class="badge-chip"><span>${b.icon || '🏅'}</span><span class="badge-chip-name">${b.name || b}</span></div>`
            ).join('');
            badgesSection.style.display = 'block';
        } else {
            badgesSection.style.display = 'none';
        }

        // Open overlay
        document.getElementById('studentModal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    // ── Close Modal ──
    function closeStudentModal() {
        document.getElementById('studentModal').classList.remove('open');
        document.body.style.overflow = '';
        // Reset ring + bar
        document.getElementById('levelRingCircle').style.strokeDashoffset = '207.3';
        document.getElementById('modalXpBar').style.width = '0%';
        document.getElementById('mWinBar').style.width = '0%';
    }

    function closeModalOutside(e) {
        if (e.target === document.getElementById('studentModal')) closeStudentModal();
    }

    // Keyboard close
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeStudentModal();
    });
</script>
@endsection