@extends('student.layout.master')

@section('title', 'My Certificates')

<style>
    /* ═══════════════════════════════════════════════════════════════
   CERTIFICATES — QuizMind
   Font: Cinzel (headings) + Raleway (body)
═══════════════════════════════════════════════════════════════ */
    @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Raleway:wght@300;400;500;600;700&display=swap');

    :root {
        --cert-gold: #D4AF37;
        --cert-gold2: #F7E98E;
        --cert-silver: #C0C0C0;
        --cert-bronze: #CD7F32;
        --cert-ink: #1a1025;
        --cert-paper: #FDF8EE;
        --radius-cert: 16px;
    }

    /* ── Page layout ─────────────────────────── */
    .cert-page {
        max-width: 1300px;
        margin: 0 auto;
        padding: 0 20px 60px;
    }

    /* ── Page header ─────────────────────────── */
    .cert-hero {
        text-align: center;
        padding: 48px 20px 32px;
        position: relative;
        overflow: hidden;
    }

    .cert-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse 70% 80% at 50% -10%, rgba(212, 175, 55, 0.18) 0%, transparent 70%);
        pointer-events: none;
    }

    .cert-hero-icon {
        font-size: 3.5rem;
        display: block;
        margin-bottom: 12px;
        animation: floatBob 3s ease-in-out infinite;
    }

    .cert-hero h1 {
        font-family: 'Cinzel', serif;
        font-size: clamp(1.8rem, 4vw, 2.8rem);
        font-weight: 900;
        background: linear-gradient(135deg, var(--cert-gold), #fff 50%, var(--cert-gold2));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 8px;
    }

    .cert-hero p {
        color: var(--muted);
        font-family: 'Raleway', sans-serif;
        font-size: .95rem;
    }

    /* ── Stats bar ───────────────────────────── */
    .cert-stats {
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
        padding: 0 20px 28px;
    }

    .cert-stat-pill {
        background: rgba(212, 175, 55, 0.08);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 50px;
        padding: 10px 22px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: 'Raleway', sans-serif;
        font-size: .85rem;
    }

    .cert-stat-pill .val {
        font-weight: 700;
        font-size: 1.1rem;
        background: linear-gradient(135deg, var(--cert-gold), var(--cert-gold2));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* ── Filter tabs ─────────────────────────── */
    .cert-filter {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: center;
        margin-bottom: 32px;
    }

    .cert-tab {
        padding: 7px 18px;
        border-radius: 50px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--border);
        font-family: 'Raleway', sans-serif;
        font-size: .82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s;
        color: var(--muted);
    }

    .cert-tab:hover {
        border-color: var(--cert-gold);
        color: var(--cert-gold);
    }

    .cert-tab.active {
        background: linear-gradient(135deg, rgba(212, 175, 55, 0.15), rgba(212, 175, 55, 0.05));
        border-color: var(--cert-gold);
        color: var(--cert-gold);
    }

    /* ── Section label ───────────────────────── */
    .cert-section-title {
        font-family: 'Cinzel', serif;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .15em;
        text-transform: uppercase;
        color: var(--muted);
        margin: 32px 0 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .cert-section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, var(--border), transparent);
    }

    /* ── Certificate Grid ────────────────────── */
    .cert-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    /* ── Certificate Card ────────────────────── */
    .cert-card {
        border-radius: var(--radius-cert);
        position: relative;
        overflow: hidden;
        cursor: pointer;
        transition: transform .25s, box-shadow .25s;
        font-family: 'Raleway', sans-serif;
    }

    .cert-card:hover {
        transform: translateY(-4px);
    }

    .cert-card.earned {
        background: linear-gradient(145deg, #1c1530, #120e22);
        border: 1px solid rgba(212, 175, 55, 0.35);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), 0 0 0 0px rgba(212, 175, 55, 0);
    }

    .cert-card.earned:hover {
        box-shadow: 0 16px 48px rgba(0, 0, 0, 0.5), 0 0 24px rgba(212, 175, 55, 0.15);
    }

    .cert-card.future {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border);
        opacity: .72;
    }

    .cert-card.future:hover {
        opacity: .9;
    }

    /* Shimmer on earned */
    .cert-card.earned::before {
        content: '';
        position: absolute;
        top: -60%;
        left: -60%;
        width: 60%;
        height: 220%;
        background: linear-gradient(105deg, transparent 40%, rgba(255, 255, 255, 0.04) 50%, transparent 60%);
        animation: shimmer 4s ease-in-out infinite;
        pointer-events: none;
    }

    @keyframes shimmer {

        0%,
        100% {
            left: -60%;
        }

        50% {
            left: 120%;
        }
    }

    /* Top accent bar */
    .cert-card-bar {
        height: 4px;
        width: 100%;
    }

    /* Inner layout */
    .cert-card-inner {
        padding: 20px 20px 16px;
    }

    .cert-cat-badge {
        display: inline-block;
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        padding: 3px 10px;
        border-radius: 50px;
        margin-bottom: 12px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--muted);
    }

    .cert-card.earned .cert-cat-badge {
        background: rgba(212, 175, 55, 0.1);
        border-color: rgba(212, 175, 55, 0.25);
        color: var(--cert-gold);
    }

    .cert-icon-wrap {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        margin-bottom: 12px;
        flex-shrink: 0;
    }

    .cert-card.earned .cert-icon-wrap {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
    }

    .cert-card.future .cert-icon-wrap {
        filter: grayscale(0.6);
        opacity: .6;
    }

    .cert-card-title {
        font-family: 'Cinzel', serif;
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 3px;
        line-height: 1.3;
    }

    .cert-card.future .cert-card-title {
        color: var(--muted);
    }

    .cert-card-subtitle {
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .04em;
        margin-bottom: 8px;
    }

    .cert-card.earned .cert-card-subtitle {
        color: var(--cert-gold);
    }

    .cert-card.future .cert-card-subtitle {
        color: var(--muted);
    }

    .cert-card-desc {
        font-size: .78rem;
        color: var(--muted);
        line-height: 1.5;
        margin-bottom: 14px;
    }

    /* Progress bar (future only) */
    .cert-progress-wrap {
        margin-bottom: 14px;
    }

    .cert-progress-label {
        display: flex;
        justify-content: space-between;
        font-size: .68rem;
        color: var(--muted);
        margin-bottom: 5px;
    }

    .cert-progress-bar {
        height: 5px;
        border-radius: 50px;
        background: rgba(255, 255, 255, 0.06);
        overflow: hidden;
    }

    .cert-progress-fill {
        height: 100%;
        border-radius: 50px;
        transition: width .8s cubic-bezier(.34, 1.56, .64, 1);
    }

    /* Earned date */
    .cert-earned-date {
        font-size: .7rem;
        color: var(--cert-gold);
        display: flex;
        align-items: center;
        gap: 5px;
        padding-top: 10px;
        border-top: 1px solid rgba(212, 175, 55, 0.1);
        margin-top: 2px;
    }

    /* Card actions */
    .cert-actions {
        display: flex;
        gap: 8px;
        padding: 0 20px 16px;
        flex-wrap: wrap;
    }

    .cert-btn {
        flex: 1;
        min-width: 80px;
        padding: 8px 12px;
        border-radius: 8px;
        font-family: 'Raleway', sans-serif;
        font-size: .75rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: all .2s;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .cert-btn-view {
        background: linear-gradient(135deg, var(--cert-gold), #A0832A);
        color: #111;
    }

    .cert-btn-view:hover {
        filter: brightness(1.1);
        transform: scale(1.02);
    }

    .cert-btn-print {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid var(--border);
        color: var(--muted);
    }

    .cert-btn-print:hover {
        border-color: var(--cert-gold);
        color: var(--cert-gold);
    }

    .cert-btn-share {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid var(--border);
        color: var(--muted);
    }

    .cert-btn-share:hover {
        border-color: #00D4FF;
        color: #00D4FF;
    }

    /* Lock badge */
    .cert-lock-badge {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 30px;
        height: 30px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--border);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem;
    }

    /* Criteria chip (future) */
    .cert-criteria {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 5px 10px;
        font-size: .72rem;
        color: var(--muted);
        margin-bottom: 12px;
    }

    /* ── Print Modal ──────────────────────────── */
    .print-modal-bg {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.85);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .print-modal-bg.open {
        display: flex;
    }

    .print-modal-wrap {
        background: var(--card);
        border: 1px solid rgba(212, 175, 55, 0.3);
        border-radius: 20px;
        width: 100%;
        max-width: 740px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6), 0 0 60px rgba(212, 175, 55, 0.1);
        animation: popIn .35s cubic-bezier(.34, 1.56, .64, 1) both;
    }

    .print-modal-actions {
        padding: 16px 24px;
        border-top: 1px solid var(--border);
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    /* ── The Printable Certificate ─────────────── */
    .certificate-doc {
        padding: 32px;
        background: var(--cert-paper);
        border-radius: 12px;
        margin: 24px;
        position: relative;
        font-family: 'Cinzel', serif;
        color: var(--cert-ink);
        min-height: 420px;
        overflow: hidden;
    }

    .certificate-doc::before {
        content: '';
        position: absolute;
        inset: 12px;
        border: 2px solid;
        border-radius: 6px;
        pointer-events: none;
        z-index: 1;
    }

    .certificate-doc::after {
        content: '';
        position: absolute;
        inset: 16px;
        border: 0.5px solid;
        border-radius: 4px;
        pointer-events: none;
        z-index: 1;
        opacity: .4;
    }

    .cert-doc-bg {
        position: absolute;
        inset: 0;
        background-image:
            radial-gradient(circle at 15% 85%, rgba(212, 175, 55, 0.12) 0%, transparent 40%),
            radial-gradient(circle at 85% 15%, rgba(212, 175, 55, 0.12) 0%, transparent 40%);
    }

    .cert-doc-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 8rem;
        opacity: .04;
        pointer-events: none;
        user-select: none;
    }

    .cert-doc-content {
        position: relative;
        z-index: 2;
        text-align: center;
    }

    .cert-doc-header {
        font-size: .55rem;
        font-weight: 700;
        letter-spacing: .25em;
        text-transform: uppercase;
        color: #8a7540;
        margin-bottom: 10px;
    }

    .cert-doc-logo {
        font-size: 1.6rem;
        margin-bottom: 4px;
    }

    .cert-doc-org {
        font-family: 'Cinzel', serif;
        font-size: 1.2rem;
        font-weight: 900;
        letter-spacing: .08em;
        color: var(--cert-ink);
        margin-bottom: 20px;
    }

    .cert-doc-divider {
        width: 120px;
        height: 1px;
        margin: 0 auto 20px;
    }

    .cert-doc-presents {
        font-family: 'Raleway', sans-serif;
        font-size: .75rem;
        font-weight: 400;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: #8a7540;
        margin-bottom: 16px;
    }

    .cert-doc-big-icon {
        font-size: 3.5rem;
        margin-bottom: 12px;
        display: block;
    }

    .cert-doc-title {
        font-family: 'Cinzel', serif;
        font-size: clamp(1.4rem, 3vw, 2rem);
        font-weight: 900;
        margin-bottom: 6px;
        letter-spacing: .04em;
    }

    .cert-doc-subtitle {
        font-family: 'Raleway', sans-serif;
        font-size: .85rem;
        font-weight: 600;
        letter-spacing: .12em;
        text-transform: uppercase;
        margin-bottom: 24px;
    }

    .cert-doc-body {
        font-family: 'Raleway', sans-serif;
        font-size: .82rem;
        line-height: 1.7;
        max-width: 480px;
        margin: 0 auto 24px;
        color: #3a2e1a;
    }

    .cert-doc-name {
        font-family: 'Cinzel', serif;
        font-size: 1.35rem;
        font-weight: 700;
        margin: 6px 0;
        letter-spacing: .04em;
    }

    .cert-doc-footer {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid rgba(212, 175, 55, 0.3);
    }

    .cert-doc-footer-col {
        text-align: center;
    }

    .cert-doc-sig {
        font-family: 'Cinzel', serif;
        font-size: 1.1rem;
        font-weight: 400;
        border-bottom: 1px solid #8a7540;
        padding-bottom: 4px;
        margin-bottom: 4px;
        color: #3a2e1a;
        letter-spacing: .05em;
    }

    .cert-doc-sig-label {
        font-family: 'Raleway', sans-serif;
        font-size: .6rem;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: #8a7540;
    }

    .cert-doc-seal {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-direction: column;
        position: relative;
    }

    .cert-doc-seal-ring {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 2px solid;
        animation: sealSpin 20s linear infinite;
    }

    @keyframes sealSpin {
        to {
            transform: rotate(360deg);
        }
    }

    /* ── Share panel ─────────────────────────── */
    .share-panel {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 20px;
        margin: 0 24px 24px;
    }

    .share-panel-title {
        font-family: 'Cinzel', serif;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 14px;
    }

    .share-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .share-btn {
        flex: 1;
        min-width: 100px;
        padding: 10px 14px;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: rgba(255, 255, 255, 0.04);
        font-family: 'Raleway', sans-serif;
        font-size: .78rem;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        color: var(--muted);
    }

    .share-btn:hover {
        transform: translateY(-2px);
    }

    .share-btn.tw {
        border-color: #1DA1F2;
    }

    .share-btn.tw:hover {
        background: rgba(29, 161, 242, 0.12);
        color: #1DA1F2;
    }

    .share-btn.wa {
        border-color: #25D366;
    }

    .share-btn.wa:hover {
        background: rgba(37, 211, 102, 0.12);
        color: #25D366;
    }

    .share-btn.li {
        border-color: #0A66C2;
    }

    .share-btn.li:hover {
        background: rgba(10, 102, 194, 0.12);
        color: #0A66C2;
    }

    .share-btn.ig {
        border-color: #E1306C;
    }

    .share-btn.ig:hover {
        background: rgba(225, 48, 108, 0.12);
        color: #E1306C;
    }

    .share-btn.lnk {
        border-color: var(--cert-gold);
    }

    .share-btn.lnk:hover {
        background: rgba(212, 175, 55, 0.12);
        color: var(--cert-gold);
    }

    .share-link-box {
        margin-top: 12px;
        display: flex;
        gap: 8px;
    }

    .share-link-input {
        flex: 1;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 8px 12px;
        font-family: 'Raleway', sans-serif;
        font-size: .75rem;
        color: var(--muted);
        outline: none;
    }

    .share-link-copy {
        padding: 8px 16px;
        border-radius: 8px;
        background: rgba(212, 175, 55, 0.1);
        border: 1px solid rgba(212, 175, 55, 0.3);
        color: var(--cert-gold);
        font-family: 'Raleway', sans-serif;
        font-size: .75rem;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s;
    }

    .share-link-copy:hover {
        background: rgba(212, 175, 55, 0.2);
    }

    /* ── Empty state ─────────────────────────── */
    .cert-empty {
        text-align: center;
        padding: 48px 20px;
        border: 2px dashed var(--border);
        border-radius: 16px;
        color: var(--muted);
        font-family: 'Raleway', sans-serif;
    }

    .cert-empty .em-icon {
        font-size: 3rem;
        margin-bottom: 12px;
        opacity: .5;
    }

    /* ── Animations ──────────────────────────── */
    @keyframes floatBob {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-8px);
        }
    }

    @keyframes popIn {
        from {
            opacity: 0;
            transform: scale(.9) translateY(20px);
        }

        to {
            opacity: 1;
            transform: none;
        }
    }

    .cert-card {
        animation: fadeUp .4s both;
    }

    .cert-card:nth-child(1) {
        animation-delay: .05s;
    }

    .cert-card:nth-child(2) {
        animation-delay: .10s;
    }

    .cert-card:nth-child(3) {
        animation-delay: .15s;
    }

    .cert-card:nth-child(4) {
        animation-delay: .20s;
    }

    .cert-card:nth-child(5) {
        animation-delay: .25s;
    }

    .cert-card:nth-child(6) {
        animation-delay: .30s;
    }

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

    /* ── Print styles ────────────────────────── */
    @media print {
        body * {
            visibility: hidden !important;
        }

        .certificate-doc,
        .certificate-doc * {
            visibility: visible !important;
        }

        .certificate-doc {
            position: fixed !important;
            inset: 0 !important;
            margin: 0 !important;
            padding: 40px !important;
            border-radius: 0 !important;
            height: 100vh !important;
        }

        .print-modal-bg {
            display: block !important;
            position: static !important;
            background: none !important;
        }

        .print-modal-wrap {
            box-shadow: none !important;
            border: none !important;
        }

        .print-modal-actions,
        .share-panel,
        .cert-doc-close {
            display: none !important;
        }
    }
</style>

@section('content')

    {{-- Background orbs --}}
    <div class="orb o1"></div>
    <div class="orb o2"></div>

    <div class="cert-page">

        {{-- ── Hero ─────────────────────────────────────────────── --}}
        <div class="cert-hero">
            <span class="cert-hero-icon">🏅</span>
            <h1>Certificate Hall of Fame</h1>
            <p>Your achievements, immortalised. Earn, print, and share your QuizMind certificates.</p>
        </div>

        {{-- ── Stats bar ────────────────────────────────────────── --}}
        @php
            $earnedCount = count(array_filter($certificates, fn($c) => $c['earned']));
            $totalCount = count($certificates);
            $futureCount = $totalCount - $earnedCount;
        @endphp
        <div class="cert-stats">
            <div class="cert-stat-pill">🏆 <span class="val">{{ $earnedCount }}</span> Earned</div>
            <div class="cert-stat-pill">🔒 <span class="val">{{ $futureCount }}</span> Locked</div>
            <div class="cert-stat-pill">📋 <span class="val">{{ $totalCount }}</span> Total</div>
            <div class="cert-stat-pill">⭐ <span
                    class="val">{{ round(($earnedCount / max(1, $totalCount)) * 100) }}%</span> Completion</div>
        </div>

        {{-- ── Filter tabs ─────────────────────────────────────── --}}
        <div class="cert-filter" id="certFilter">
            <button class="cert-tab active" data-cat="All" onclick="filterCerts(this)">All Certificates</button>
            @foreach ($categories as $cat)
                <button class="cert-tab" data-cat="{{ $cat }}"
                    onclick="filterCerts(this)">{{ $cat }}</button>
            @endforeach
            <button class="cert-tab" data-cat="earned_only" onclick="filterCerts(this)">✅ Earned Only</button>
            <button class="cert-tab" data-cat="locked_only" onclick="filterCerts(this)">🔒 Not Yet</button>
        </div>

        {{-- ── Earned Certificates ──────────────────────────────── --}}
        <div class="cert-section-title">✨ Earned Certificates</div>

        <div class="cert-grid" id="earnedGrid">
            @forelse (array_filter($certificates, fn($c) => $c['earned']) as $cert)
                <div class="cert-card earned" data-cat="{{ $cert['category'] }}" data-earned="true"
                    onclick="openCertModal({{ json_encode($cert) }}, {{ json_encode($student->display_name ?: $user->name) }})">

                    {{-- Top colour bar --}}
                    <div class="cert-card-bar"
                        style="background: linear-gradient(90deg, {{ $cert['color'] }}, {{ $cert['color2'] }})"></div>

                    <div class="cert-card-inner">
                        {{-- Category --}}
                        <span class="cert-cat-badge">{{ $cert['category'] }}</span>

                        {{-- Icon --}}
                        <div class="cert-icon-wrap"
                            style="background: linear-gradient(135deg, {{ $cert['color'] }}22, {{ $cert['color2'] }}11);">
                            {{ $cert['icon'] }}
                        </div>

                        {{-- Text --}}
                        <div class="cert-card-title">{{ $cert['title'] }}</div>
                        <div class="cert-card-subtitle">{{ $cert['subtitle'] }}</div>
                        <div class="cert-card-desc">{{ $cert['description'] }}</div>

                        {{-- Earned date --}}
                        @if ($cert['earned_at'])
                            <div class="cert-earned-date">
                                ✅ Awarded {{ $cert['earned_at'] }}
                            </div>
                        @endif
                    </div>

                    {{-- Action buttons --}}
                    <div class="cert-actions" onclick="event.stopPropagation()">
                        <button class="cert-btn cert-btn-view"
                            onclick="openCertModal({{ json_encode($cert) }}, '{{ $student->display_name ?: $user->name }}')">
                            🏅 View
                        </button>
                        <button class="cert-btn cert-btn-print"
                            onclick="printCert({{ json_encode($cert) }}, '{{ $student->display_name ?: $user->name }}')">
                            🖨️ Print
                        </button>
                        <button class="cert-btn cert-btn-share"
                            onclick="openSharePanel({{ json_encode($cert) }}, '{{ $student->display_name ?: $user->name }}')">
                            🔗 Share
                        </button>
                    </div>

                </div>
            @empty
                <div class="cert-empty" style="grid-column:1/-1">
                    <div class="em-icon">🏅</div>
                    <p>You haven't earned any certificates yet.<br>Complete quizzes and battles to get started!</p>
                </div>
            @endforelse
        </div>

        {{-- ── Upcoming / Locked Certificates ─────────────────── --}}
        <div class="cert-section-title" style="margin-top:40px">🔒 Upcoming Certificates</div>

        <div class="cert-grid" id="futureGrid">
            @forelse (array_filter($certificates, fn($c) => !$c['earned']) as $cert)
                <div class="cert-card future" data-cat="{{ $cert['category'] }}" data-earned="false">

                    <div class="cert-card-bar"
                        style="background: linear-gradient(90deg, {{ $cert['color'] }}44, {{ $cert['color2'] }}22)"></div>

                    {{-- Lock badge --}}
                    <div class="cert-lock-badge">🔒</div>

                    <div class="cert-card-inner">
                        <span class="cert-cat-badge">{{ $cert['category'] }}</span>

                        <div class="cert-icon-wrap" style="background: rgba(255,255,255,0.04);">
                            {{ $cert['icon'] }}
                        </div>

                        <div class="cert-card-title">{{ $cert['title'] }}</div>
                        <div class="cert-card-subtitle">{{ $cert['subtitle'] }}</div>
                        <div class="cert-card-desc">{{ $cert['description'] }}</div>

                        {{-- Criteria --}}
                        <div class="cert-criteria">
                            🎯 {{ $cert['criteria'] }}
                        </div>

                        {{-- Progress --}}
                        @if ($cert['progress'] > 0 && $cert['target'])
                            <div class="cert-progress-wrap">
                                <div class="cert-progress-label">
                                    <span>Progress</span>
                                    <span>{{ round($cert['progress']) }}%</span>
                                </div>
                                <div class="cert-progress-bar">
                                    <div class="cert-progress-fill"
                                        style="width:{{ $cert['progress'] }}%;background:linear-gradient(90deg,{{ $cert['color'] }},{{ $cert['color2'] }})">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
            @empty
                <div class="cert-empty" style="grid-column:1/-1">
                    <div class="em-icon">🌈</div>
                    <p>You've earned every certificate — absolute legend! 🎉</p>
                </div>
            @endforelse
        </div>

    </div>{{-- /cert-page --}}


    {{-- ══════════════════════════════════════════════════════════════
     CERTIFICATE MODAL (view / print / share)
══════════════════════════════════════════════════════════════ --}}
    <div class="print-modal-bg" id="certModal" onclick="closeCertModal(event)">
        <div class="print-modal-wrap" id="certModalWrap">

            {{-- Printable certificate --}}
            <div class="certificate-doc" id="certDoc">
                <div class="cert-doc-bg"></div>
                <div class="cert-doc-watermark" id="docWatermark">🏅</div>

                <div class="cert-doc-content">
                    <div class="cert-doc-header">Certificate of Achievement</div>
                    <div class="cert-doc-logo">🎓</div>
                    <div class="cert-doc-org">QuizMind Academy</div>
                    <div class="cert-doc-divider" id="docDivider"
                        style="background: linear-gradient(90deg, transparent, #D4AF37, transparent)"></div>

                    <div class="cert-doc-presents">This certificate is proudly presented to</div>
                    <div class="cert-doc-name" id="docStudentName">—</div>
                    <div class="cert-doc-presents" style="margin-top:6px">for outstanding achievement in</div>

                    <span class="cert-doc-big-icon" id="docIcon">🏅</span>
                    <div class="cert-doc-title" id="docTitle">—</div>
                    <div class="cert-doc-subtitle" id="docSubtitle">—</div>

                    <div class="cert-doc-body" id="docDesc">—</div>

                    {{-- Criteria badge --}}
                    <div style="display:inline-block;padding:5px 16px;border-radius:50px;font-family:'Raleway',sans-serif;font-size:.72rem;font-weight:700;letter-spacing:.08em;margin-bottom:20px"
                        id="docCriteriaBadge">
                    </div>

                    {{-- Footer --}}
                    <div class="cert-doc-footer">
                        <div class="cert-doc-footer-col">
                            <div class="cert-doc-sig">QuizMind</div>
                            <div class="cert-doc-sig-label">Platform Director</div>
                        </div>

                        <div class="cert-doc-seal" id="docSeal">
                            <div class="cert-doc-seal-ring" id="docSealRing"></div>
                            <span id="docSealIcon" style="font-size:1.6rem;position:relative;z-index:1">🏅</span>
                        </div>

                        <div class="cert-doc-footer-col">
                            <div class="cert-doc-sig" id="docDate">—</div>
                            <div class="cert-doc-sig-label">Date of Award</div>
                        </div>
                    </div>

                </div>{{-- /content --}}
            </div>{{-- /certificate-doc --}}


            {{-- Share panel --}}
            <div class="share-panel" id="sharePanel" style="display:none">
                <div class="share-panel-title">📤 Share Your Achievement</div>
                <div class="share-buttons">
                    <button class="share-btn tw" onclick="shareToTwitter()">🐦 Twitter / X</button>
                    <button class="share-btn wa" onclick="shareToWhatsApp()">💬 WhatsApp</button>
                    <button class="share-btn li" onclick="shareToLinkedIn()">💼 LinkedIn</button>
                    <button class="share-btn ig" onclick="shareToInstagram()">📸 Instagram</button>
                </div>
                <div class="share-link-box">
                    <input class="share-link-input" id="shareLinkInput" readonly value="">
                    <button class="share-link-copy" onclick="copyShareLink()">📋 Copy</button>
                </div>
            </div>

            {{-- Modal action buttons --}}
            <div class="print-modal-actions">
                <button class="cert-btn cert-btn-print" onclick="window.print()" style="flex:unset;padding:9px 18px">
                    🖨️ Print Certificate
                </button>
                <button class="cert-btn cert-btn-share" id="toggleShareBtn" onclick="toggleShare()"
                    style="flex:unset;padding:9px 18px">
                    🔗 Share
                </button>
                <button class="cert-btn cert-btn-view" onclick="downloadCertPNG()"
                    style="flex:unset;padding:9px 18px;background:rgba(255,255,255,0.08);color:#fff;border:1px solid var(--border)">
                    ⬇️ Download PNG
                </button>
                <button class="cert-btn" onclick="closeCertModal()"
                    style="flex:unset;padding:9px 18px;background:rgba(255,255,255,0.04);border:1px solid var(--border);color:var(--muted)">
                    ✕ Close
                </button>
            </div>

        </div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        /* ═══════════════════════════════════════════════
       CERTIFICATE MODAL LOGIC
    ═══════════════════════════════════════════════ */
        let _activeCert = null;
        let _activeStudent = '';

        function openCertModal(cert, studentName) {
            _activeCert = cert;
            _activeStudent = studentName;

            // Populate document
            document.getElementById('docStudentName').textContent = studentName;
            document.getElementById('docIcon').textContent = cert.icon;
            document.getElementById('docTitle').textContent = cert.title;
            document.getElementById('docSubtitle').textContent = cert.subtitle;
            document.getElementById('docDesc').textContent = cert.description;
            document.getElementById('docDate').textContent = cert.earned_at || 'To Be Awarded';
            document.getElementById('docWatermark').textContent = cert.icon;

            // Color the divider & criteria badge
            document.getElementById('docDivider').style.background =
                `linear-gradient(90deg, transparent, ${cert.color}, transparent)`;

            const badge = document.getElementById('docCriteriaBadge');
            badge.textContent = '🎯 ' + cert.criteria;
            badge.style.background = cert.color + '18';
            badge.style.border = `1px solid ${cert.color}55`;
            badge.style.color = cert.color;

            // Seal
            document.getElementById('docSealIcon').textContent = cert.icon;
            document.getElementById('docSealRing').style.borderColor = cert.color;
            document.getElementById('docSeal').style.background = cert.color + '18';

            // Certificate border colour via CSS var trick
            const doc = document.getElementById('certDoc');
            doc.style.setProperty('--cert-border-col', cert.color);
            doc.style.cssText += `
        --cert-b: ${cert.color};
    `;
            // Apply border color via pseudo-element workaround
            const styleTag = document.getElementById('dynamicCertStyle') || (() => {
                const s = document.createElement('style');
                s.id = 'dynamicCertStyle';
                document.head.appendChild(s);
                return s;
            })();
            styleTag.textContent = `
        .certificate-doc::before { border-color: ${cert.color}88 !important; }
        .certificate-doc::after  { border-color: ${cert.color}44 !important; }
        .cert-doc-title          { color: ${cert.color} !important; }
        .cert-doc-subtitle       { color: ${cert.color}cc !important; }
        .cert-doc-divider        { background: linear-gradient(90deg, transparent, ${cert.color}, transparent) !important; }
    `;

            // Share link
            document.getElementById('shareLinkInput').value =
                `${window.location.origin}/student/certificates/${cert.id}`;

            // Hide share panel by default
            document.getElementById('sharePanel').style.display = 'none';

            document.getElementById('certModal').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeCertModal(e) {
            if (e && e.target !== document.getElementById('certModal')) return;
            document.getElementById('certModal').classList.remove('open');
            document.body.style.overflow = '';
        }

        function toggleShare() {
            const sp = document.getElementById('sharePanel');
            sp.style.display = sp.style.display === 'none' ? 'block' : 'none';
        }

        function openSharePanel(cert, studentName) {
            openCertModal(cert, studentName);
            setTimeout(() => document.getElementById('sharePanel').style.display = 'block', 200);
        }

        function printCert(cert, studentName) {
            openCertModal(cert, studentName);
            setTimeout(() => window.print(), 400);
        }

        /* ── Download as PNG ──────────────────────── */
        function downloadCertPNG() {
            const el = document.getElementById('certDoc');
            if (typeof html2canvas === 'undefined') {
                alert('html2canvas not loaded. Please try print instead.');
                return;
            }
            html2canvas(el, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#FDF8EE'
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = `${_activeCert?.id || 'certificate'}-quizmind.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        }

        /* ── Share functions ─────────────────────── */
        function buildShareText() {
            return encodeURIComponent(
                `🏅 I just earned the "${_activeCert?.title}" certificate on QuizMind! 🎓 #QuizMind #Learning`
            );
        }

        function buildShareUrl() {
            return encodeURIComponent(`${window.location.origin}/student/certificates/${_activeCert?.id}`);
        }

        function shareToTwitter() {
            window.open(`https://twitter.com/intent/tweet?text=${buildShareText()}&url=${buildShareUrl()}`, '_blank');
        }

        function shareToWhatsApp() {
            window.open(`https://wa.me/?text=${buildShareText()}%20${buildShareUrl()}`, '_blank');
        }

        function shareToLinkedIn() {
            window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${buildShareUrl()}`, '_blank');
        }

        function shareToInstagram() {
            // Instagram doesn't support direct URL sharing — show tip
            if (typeof qmToast === 'function') qmToast('info', '📸 Download the PNG and share it on Instagram!');
            downloadCertPNG();
        }

        function copyShareLink() {
            const input = document.getElementById('shareLinkInput');
            navigator.clipboard?.writeText(input.value)
                .then(() => {
                    if (typeof qmToast === 'function') qmToast('success', '🔗 Link copied!');
                })
                .catch(() => {
                    input.select();
                    document.execCommand('copy');
                });
        }

        /* ── Filter ──────────────────────────────── */
        function filterCerts(btn) {
            document.querySelectorAll('.cert-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const cat = btn.dataset.cat;

            document.querySelectorAll('.cert-card').forEach(card => {
                const cardCat = card.dataset.cat;
                const isEarned = card.dataset.earned === 'true';

                let show = false;
                if (cat === 'All') show = true;
                else if (cat === 'earned_only') show = isEarned;
                else if (cat === 'locked_only') show = !isEarned;
                else show = cardCat === cat;

                card.style.display = show ? '' : 'none';
            });

            // Hide section titles if all cards in that section are hidden
            const earnedVisible = [...document.querySelectorAll('#earnedGrid .cert-card')]
                .some(c => c.style.display !== 'none');
            const futureVisible = [...document.querySelectorAll('#futureGrid .cert-card')]
                .some(c => c.style.display !== 'none');

            document.querySelectorAll('.cert-section-title')[0].style.display = earnedVisible ? '' : 'none';
            document.querySelectorAll('.cert-section-title')[1].style.display = futureVisible ? '' : 'none';
        }

        /* ── Progress bar entrance animation ─────── */
        window.addEventListener('load', () => {
            document.querySelectorAll('.cert-progress-fill').forEach(bar => {
                const w = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = w;
                }, 300);
            });
        });

        /* ── Close modal on ESC ──────────────────── */
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                document.getElementById('certModal').classList.remove('open');
                document.body.style.overflow = '';
            }
        });
    </script>

@endsection
