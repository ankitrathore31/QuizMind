{{-- resources/views/student/quiz/my-quizzes.blade.php --}}
@extends('student.layout.master')
@section('title', 'My Quiz History')

@section('content')

{{-- Quiz Review Modal --}}
<div class="modal-overlay hidden" id="reviewModal">
    <div class="modal-panel" style="max-width:680px;max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <div class="modal-title" id="reviewModalTitle">Quiz Review</div>
            <button class="btn btn-ghost btn-sm" onclick="closeReview()">✕ Close</button>
        </div>
        <div id="reviewModalBody">
            <div style="text-align:center;padding:40px;color:var(--muted)">
                <div style="font-size:2rem;margin-bottom:8px">⏳</div>
                Loading review…
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirm Modal --}}
<div class="modal-overlay hidden" id="deleteModal">
    <div class="modal-panel modal-sm" style="max-width:380px">
        <div style="text-align:center;padding:8px 0 20px">
            <div style="font-size:2.5rem;margin-bottom:10px">🗑️</div>
            <div class="modal-title" style="font-size:1rem;margin-bottom:8px" id="deleteModalTitle">Delete this entry?</div>
            <p class="text-muted" style="font-size:.82rem" id="deleteModalDesc">This action cannot be undone.</p>
        </div>
        <div class="flex gap-10">
            <button class="btn btn-ghost w-full" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn w-full" id="deleteConfirmBtn"
                style="background:var(--red,#ff4d6a);color:#fff;border:none;border-radius:var(--radius-sm);cursor:pointer;font-weight:700;padding:10px"
                onclick="confirmDelete()">
                Delete
            </button>
        </div>
    </div>
</div>

<style>
.page-hd   { margin-bottom:24px; }
.page-hd h1{ font-family:var(--fh);font-size:1.6rem;font-weight:900;margin-bottom:4px; }

/* Stats bar */
.stats-bar {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(110px,1fr));
    gap:10px;
    margin-bottom:24px;
}
.sb-card {
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius-sm);
    padding:14px 12px;
    text-align:center;
}
.sb-val  { font-family:var(--fh);font-weight:800;font-size:1.1rem; }
.sb-lbl  { font-size:.68rem;color:var(--muted);margin-top:3px; }

.filter-bar{ display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px;align-items:center; }
.filter-btn{ padding:5px 13px;border-radius:20px;border:1px solid var(--border);
    background:transparent;cursor:pointer;font-size:.77rem;font-weight:600;
    color:var(--muted);transition:.2s;white-space:nowrap; }
.filter-btn:hover,.filter-btn.active{ border-color:var(--accent,#7c5cfc);color:var(--accent,#7c5cfc);
    background:rgba(124,92,252,.08); }

/* Quiz history grid */
.quiz-history-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px; }

.qh-card  { background:var(--card);border:1px solid var(--border);border-radius:var(--radius);
    padding:18px;transition:.2s;position:relative; }
.qh-card:hover{ border-color:rgba(124,92,252,.4);transform:translateY(-2px); }
.qh-top   { display:flex;align-items:flex-start;gap:12px;margin-bottom:14px; }
.qh-icon  { font-size:1.8rem;flex-shrink:0; }
.qh-info  { flex:1;min-width:0; }
.qh-title { font-weight:700;font-size:.9rem;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.qh-meta  { font-size:.74rem;color:var(--muted); }

.qh-actions {
    display:flex;gap:6px;margin-top:12px;padding-top:12px;border-top:1px solid var(--border);
}
.qh-btn {
    flex:1;padding:7px;border-radius:7px;border:1px solid var(--border2);
    background:transparent;cursor:pointer;font-size:.76rem;font-weight:600;
    color:var(--muted);transition:.2s;
}
.qh-btn:hover{ border-color:var(--accent,#7c5cfc);color:var(--accent,#7c5cfc); }
.qh-btn.danger:hover{ border-color:var(--red,#ff4d6a);color:var(--red,#ff4d6a); }

.qh-stats { display:grid;grid-template-columns:repeat(3,1fr);gap:8px; }
.qhs      { text-align:center;padding:8px;background:rgba(255,255,255,.03);border-radius:8px;border:1px solid var(--border2); }
.qhs-val  { font-family:var(--fh);font-weight:800;font-size:1rem; }
.qhs-lbl  { font-size:.66rem;color:var(--muted);margin-top:2px; }

/* Review modal */
.rq-card  { border:1px solid var(--border);border-radius:var(--radius-sm);padding:14px;margin-bottom:12px; }
.rq-q     { font-weight:600;font-size:.88rem;margin-bottom:10px;line-height:1.55; }
.rq-opts  { display:grid;grid-template-columns:1fr 1fr;gap:6px; }
.rq-opt   { padding:7px 10px;border-radius:6px;font-size:.78rem;border:1px solid var(--border2);
    display:flex;align-items:center;gap:6px;transition:.15s; }
.rq-opt.correct{ border-color:rgba(0,227,150,.4);background:rgba(0,227,150,.07);color:var(--green,#00e396); }
.rq-opt.wrong  { border-color:rgba(255,77,106,.35);background:rgba(255,77,106,.06);color:var(--red,#ff4d6a); }
.rq-opt.my-ans { font-weight:700; }
.rq-opt.skipped{ border-color:rgba(245,166,35,.3);background:rgba(245,166,35,.05);color:#f5a623; }
.rq-expl  { margin-top:8px;font-size:.76rem;color:var(--muted);padding-top:8px;border-top:1px solid var(--border2); }

.score-hero{ text-align:center;margin-bottom:24px;padding:24px;
    background:linear-gradient(135deg,rgba(124,92,252,.08),rgba(0,212,255,.05));
    border:1px solid rgba(124,92,252,.15);border-radius:var(--radius); }

.empty-state{ text-align:center;padding:60px 24px;color:var(--muted); }
.empty-state .es-icon{ font-size:3.5rem;margin-bottom:14px; }

/* Battle history */
.bh-card  { background:var(--card);border:1px solid var(--border);border-radius:var(--radius);
    padding:16px;margin-bottom:12px;display:flex;align-items:center;gap:14px;transition:.2s; }
.bh-card:hover{ border-color:rgba(124,92,252,.3); }
.bh-mode  { font-size:1.6rem;flex-shrink:0; }
.bh-info  { flex:1;min-width:0; }
.bh-title { font-weight:700;font-size:.88rem; }
.bh-meta  { font-size:.74rem;color:var(--muted);margin-top:2px; }
.bh-result{ text-align:right;flex-shrink:0; }
.bh-rank  { font-family:var(--fh);font-weight:800;font-size:1rem; }
.bh-xp    { font-size:.74rem;color:gold;font-weight:700; }

/* Loading skeleton */
.skeleton {
    background:linear-gradient(90deg,rgba(255,255,255,.05) 25%,rgba(255,255,255,.1) 50%,rgba(255,255,255,.05) 75%);
    background-size:200% 100%;
    animation:skeleton-shine 1.4s infinite;
    border-radius:6px;
    height:14px;
}
@keyframes skeleton-shine {
    0%  { background-position: 200% 0; }
    100%{ background-position:-200% 0; }
}
</style>

<div class="page-hd anim-fade">
    <h1>📚 My Quiz History</h1>
    <p class="text-muted" style="font-size:.84rem">Review past quizzes, track accuracy, and see battle results</p>
</div>

{{-- ── Stats Summary Bar ── --}}
@if(isset($stats) && $stats['total_quizzes'] > 0)
<div class="stats-bar anim-fade">
    <div class="sb-card">
        <div class="sb-val" style="background:var(--gradient,linear-gradient(135deg,#7c5cfc,#00d4ff));-webkit-background-clip:text;-webkit-text-fill-color:transparent">
            {{ $stats['total_quizzes'] }}
        </div>
        <div class="sb-lbl">Quizzes Played</div>
    </div>
    <div class="sb-card">
        <div class="sb-val" style="color:{{ $stats['avg_accuracy'] >= 80 ? 'var(--green,#00e396)' : ($stats['avg_accuracy'] >= 60 ? 'var(--accent,#7c5cfc)' : 'var(--red,#ff4d6a)') }}">
            {{ $stats['avg_accuracy'] }}%
        </div>
        <div class="sb-lbl">Avg Accuracy</div>
    </div>
    <div class="sb-card">
        <div class="sb-val" style="color:gold">+{{ number_format($stats['total_xp']) }}</div>
        <div class="sb-lbl">Total XP</div>
    </div>
    <div class="sb-card">
        <div class="sb-val" style="color:var(--cyan,#00d4ff)">{{ $stats['best_accuracy'] }}%</div>
        <div class="sb-lbl">Best Accuracy</div>
    </div>
    <div class="sb-card">
        <div class="sb-val">{{ $stats['total_correct'] }}/{{ $stats['total_answered'] }}</div>
        <div class="sb-lbl">Total Correct</div>
    </div>
</div>
@endif

{{-- Section tabs --}}
<div class="tabs mb-20">
    <button class="tab active" onclick="showSection('solo',this)">🎯 Solo Quizzes</button>
    <button class="tab"        onclick="showSection('battle',this)">⚔️ Battles</button>
</div>

{{-- ═══════════════ SOLO SECTION ═══════════════ --}}
<div id="section-solo">

    <div class="filter-bar">
        <span style="font-size:.78rem;color:var(--muted);font-weight:600">Filter:</span>
        <button class="filter-btn active" onclick="filterResults('all',this)">All</button>
        <button class="filter-btn" onclick="filterResults('topic',this)">🤖 AI Topic</button>
        <button class="filter-btn" onclick="filterResults('pdf',this)">📄 PDF</button>
        <button class="filter-btn" onclick="filterResults('image',this)">🖼️ Image</button>
        <button class="filter-btn" onclick="filterResults('standard',this)">📚 Standard</button>
        <button class="filter-btn" onclick="filterResults('manual',this)">✍️ Manual</button>
    </div>

    @if($soloResults->count())
        <div class="quiz-history-grid" id="soloGrid">
            @foreach($soloResults as $r)
                @php
                    $srcIcons = ['topic'=>'🤖','pdf'=>'📄','image'=>'🖼️','standard'=>'📚','manual'=>'✍️'];
                    $quiz     = $r->quiz;
                    $src      = $quiz?->source ?? 'topic';
                    $icon     = $srcIcons[$src] ?? '🤖';
                    $acc      = $r->accuracy;
                    $accColor = $acc >= 80 ? 'var(--green)' : ($acc >= 60 ? 'var(--accent)' : 'var(--red)');
                @endphp
                <div class="qh-card anim-fade" data-source="{{ $src }}" id="result-card-{{ $r->id }}">
                    <div class="qh-top">
                        <div class="qh-icon">{{ $icon }}</div>
                        <div class="qh-info">
                            <div class="qh-title">{{ $quiz?->title ?? 'Untitled Quiz' }}</div>
                            <div class="qh-meta">
                                {{ ucfirst($src) }} · {{ ucfirst($r->subject ?? 'General') }}
                                · {{ $r->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                    <div class="qh-stats">
                        <div class="qhs">
                            <div class="qhs-val">{{ $r->score }}/{{ $r->total_q }}</div>
                            <div class="qhs-lbl">Score</div>
                        </div>
                        <div class="qhs">
                            <div class="qhs-val" style="color:{{ $accColor }}">{{ $r->accuracy }}%</div>
                            <div class="qhs-lbl">Accuracy</div>
                        </div>
                        <div class="qhs">
                            <div class="qhs-val" style="color:gold">+{{ $r->xp_earned }}</div>
                            <div class="qhs-lbl">XP</div>
                        </div>
                    </div>
                    <div class="qh-actions">
                        <button class="qh-btn" onclick="openReview({{ $r->id }})">
                            📋 Review Answers
                        </button>
                        @if($quiz)
                        <button class="qh-btn" onclick="event.stopPropagation(); window.location='{{ route('student.quiz.index') }}'">
                            🔄 Retry
                        </button>
                        @endif
                        <button class="qh-btn danger" onclick="event.stopPropagation(); promptDeleteResult({{ $r->id }}, '{{ addslashes($quiz?->title ?? 'this result') }}')">
                            🗑
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-20">{{ $soloResults->links() }}</div>

    @else
        <div class="empty-state">
            <div class="es-icon">🎯</div>
            <p style="font-weight:700;margin-bottom:8px">No solo quizzes yet</p>
            <p style="font-size:.84rem">Generate a quiz and play solo to see your history here</p>
            <a href="{{ route('student.quiz.index') }}" class="btn btn-grad mt-16">Generate a Quiz</a>
        </div>
    @endif
</div>

{{-- ═══════════════ BATTLE SECTION ═══════════════ --}}
<div id="section-battle" style="display:none">

    @if($battleRooms->count())
        @foreach($battleRooms as $room)
            @php
                $myP       = $room->participants->firstWhere('user_id', Auth::id());
                $modeIcons = ['1v1'=>'⚔️','group'=>'👥','team'=>'🏆'];
                $modeIcon  = $modeIcons[$room->mode] ?? '⚔️';
                $finalScores = $room->final_scores ?? [];
                $myData    = collect($finalScores)->firstWhere('user_id', Auth::id());
                $myRank    = collect($finalScores)->search(fn($s) => ($s['user_id'] ?? null) === Auth::id());
                $myRank    = $myRank !== false ? $myRank + 1 : null;
                $isWinner  = ($room->winner_user_id === Auth::id())
                             || ($room->mode === 'team' && $myData
                                 && (($myData['team'] ?? '') === 'a' && $room->winner_team === $room->team_a_name)
                                 || (($myData['team'] ?? '') === 'b' && $room->winner_team === $room->team_b_name));
            @endphp
            <div class="bh-card">
                <div class="bh-mode">{{ $modeIcon }}</div>
                <div class="bh-info">
                    <div class="bh-title">
                        {{ ucfirst($room->mode) }} Battle
                        @if($room->mode === 'team')
                            · {{ $room->team_a_name }} vs {{ $room->team_b_name }}
                        @endif
                    </div>
                    <div class="bh-meta">
                        Code: {{ $room->code }}
                        · {{ $room->participants->count() }} players
                        · {{ $room->finished_at?->diffForHumans() ?? 'In progress' }}
                        @if($myP && $myP->disqualified)
                            · <span style="color:var(--red);font-weight:700">⛔ Disqualified</span>
                        @endif
                    </div>
                </div>
                <div class="bh-result">
                    <div class="bh-rank">
                        @if($isWinner) 🥇 Winner!
                        @elseif($myRank) #{{ $myRank }}
                        @else —
                        @endif
                    </div>
                    @if($myData)
                        <div style="font-size:.78rem;color:var(--muted)">{{ $myData['score'] ?? 0 }} pts</div>
                        <div class="bh-xp">+{{ $myData['xp'] ?? 0 }} XP</div>
                    @endif
                    <a href="{{ route('student.battle.results', $room->code) }}"
                       class="btn btn-ghost btn-sm mt-8" style="font-size:.72rem;padding:4px 10px;text-decoration:none">
                        View →
                    </a>
                </div>
            </div>
        @endforeach
        <div class="mt-20">{{ $battleRooms->links() }}</div>
    @else
        <div class="empty-state">
            <div class="es-icon">⚔️</div>
            <p style="font-weight:700;margin-bottom:8px">No battles yet</p>
            <p style="font-size:.84rem">Generate a quiz and challenge someone to a battle!</p>
            <a href="{{ route('student.quiz.index') }}" class="btn btn-grad mt-16">Start a Battle</a>
        </div>
    @endif
</div>

<script>
const LETTERS = ['A','B','C','D'];
const CSRF    = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ── Section toggle ─────────────────────────────────────────────────────────
function showSection(name, btn) {
    ['solo','battle'].forEach(s => {
        document.getElementById(`section-${s}`).style.display = s === name ? '' : 'none';
    });
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
}

// ── Source filter ──────────────────────────────────────────────────────────
function filterResults(src, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.qh-card').forEach(card => {
        card.style.display = (src === 'all' || card.dataset.source === src) ? '' : 'none';
    });
}

// ── Review modal (AJAX) ────────────────────────────────────────────────────
async function openReview(resultId) {
    document.getElementById('reviewModalTitle').textContent = 'Loading…';
    document.getElementById('reviewModalBody').innerHTML = `
        <div style="padding:40px;text-align:center;color:var(--muted)">
            <div style="font-size:2rem;margin-bottom:10px">⏳</div>
            Loading your answers…
        </div>`;
    document.getElementById('reviewModal').classList.remove('hidden');

    try {
        const res  = await fetch(`{{ url('student/history/result') }}/${resultId}/detail`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        if (!data.success) {
            document.getElementById('reviewModalBody').innerHTML =
                `<p style="color:var(--red,#ff4d6a);text-align:center;padding:30px">Failed to load review.</p>`;
            return;
        }

        renderReview(data.result);

    } catch {
        document.getElementById('reviewModalBody').innerHTML =
            `<p style="color:var(--red,#ff4d6a);text-align:center;padding:30px">Network error. Please try again.</p>`;
    }
}

function renderReview(data) {
    document.getElementById('reviewModalTitle').textContent = data.title;

    const acc      = data.accuracy;
    const accColor = acc >= 80 ? 'var(--green,#00e396)' : (acc >= 60 ? 'var(--accent,#7c5cfc)' : 'var(--red,#ff4d6a)');
    const emoji    = acc >= 80 ? '🏆' : acc >= 60 ? '🎯' : '📚';
    const label    = acc >= 80 ? 'Excellent!' : acc >= 60 ? 'Good Job!' : 'Keep Practicing!';

    // Subject badge color
    const srcIcons = {topic:'🤖',pdf:'📄',image:'🖼️',standard:'📚',manual:'✍️'};
    const srcIcon  = srcIcons[data.source] || '🤖';

    let html = `
        <div class="score-hero">
            <div style="font-size:3rem;margin-bottom:10px">${emoji}</div>
            <div style="font-family:var(--fh,sans-serif);font-size:1.4rem;font-weight:800;margin-bottom:6px">${label}</div>
            <div style="font-size:.8rem;color:var(--muted);margin-bottom:18px">
                ${srcIcon} ${escHtml(data.subject || 'General')} · ${data.created_at}
            </div>
            <div class="flex gap-14" style="justify-content:center;flex-wrap:wrap">
                <div class="stat-card sc-purple text-center" style="min-width:80px">
                    <div class="stat-val grad">${data.score}/${data.total}</div>
                    <div class="stat-label">Score</div>
                </div>
                <div class="stat-card sc-cyan text-center" style="min-width:80px">
                    <div class="stat-val" style="color:${accColor}">${acc}%</div>
                    <div class="stat-label">Accuracy</div>
                </div>
                <div class="stat-card sc-gold text-center" style="min-width:80px">
                    <div class="stat-val gold">+${data.xp}</div>
                    <div class="stat-label">XP</div>
                </div>
                <div class="stat-card sc-green text-center" style="min-width:80px">
                    <div class="stat-val green">${data.time ?? '—'}s</div>
                    <div class="stat-label">Time</div>
                </div>
            </div>
        </div>
        <div class="section-title mb-16">📝 Question-by-Question Review</div>`;

    const questions = data.questions || [];
    const log       = data.log || [];

    if (questions.length === 0) {
        html += `<p style="text-align:center;color:var(--muted);padding:20px">No question data available for this quiz.</p>`;
    } else {
        questions.forEach((q, i) => {
            const entry  = log[i] || {};
            // Support both {idx, correct} and {selected, correct} formats
            const myAns  = entry.idx ?? entry.selected ?? -1;
            const correct = entry.correct ?? false;
            const skipped = myAns === -1;

            html += `<div class="rq-card">
                <div class="flex mb-8" style="justify-content:space-between;align-items:center">
                    <span class="badge bp">Q${i + 1}</span>
                    <span style="font-size:.78rem;font-weight:700;color:${
                        skipped ? '#f5a623' : correct ? 'var(--green,#00e396)' : 'var(--red,#ff4d6a)'
                    }">
                        ${skipped ? '⏭ Skipped' : correct ? '✅ Correct' : '❌ Wrong'}
                    </span>
                </div>
                <p class="rq-q">${escHtml(q.question)}</p>
                <div class="rq-opts">
                    ${(q.options || []).map((opt, j) => {
                        let cls = 'rq-opt';
                        let icon = LETTERS[j];

                        if (j === q.answer) {
                            cls += ' correct';
                            icon = '✓';
                        } else if (j === myAns && !correct) {
                            cls += ' wrong my-ans';
                            icon = '✗';
                        }

                        if (j === myAns && j === q.answer) {
                            cls += ' my-ans';
                        }

                        return `<div class="${cls}">
                            <span style="font-weight:800;font-size:.75rem;min-width:14px">${icon}</span>
                            ${escHtml(opt)}
                        </div>`;
                    }).join('')}
                </div>
                ${!correct && !skipped ? `
                    <div class="rq-expl" style="color:var(--green,#00e396);font-size:.78rem">
                        ✓ Correct answer: <strong>${escHtml(q.options[q.answer] ?? '')}</strong>
                    </div>` : ''}
                ${q.explanation ? `<div class="rq-expl">💡 ${escHtml(q.explanation)}</div>` : ''}
            </div>`;
        });
    }

    document.getElementById('reviewModalBody').innerHTML = html;
}

function closeReview() {
    document.getElementById('reviewModal').classList.add('hidden');
}

document.getElementById('reviewModal').addEventListener('click', e => {
    if (e.target === document.getElementById('reviewModal')) closeReview();
});

// ── Delete flow ────────────────────────────────────────────────────────────
let _deleteTarget = null; // { type: 'result'|'quiz', id, cardId }

function promptDeleteResult(resultId, title) {
    _deleteTarget = { type: 'result', id: resultId, cardId: `result-card-${resultId}` };
    document.getElementById('deleteModalTitle').textContent = 'Delete this result?';
    document.getElementById('deleteModalDesc').textContent  =
        `"${title}" will be removed from your history. This cannot be undone.`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    _deleteTarget = null;
}

async function confirmDelete() {
    if (!_deleteTarget) return;

    const btn = document.getElementById('deleteConfirmBtn');
    btn.textContent = '⏳ Deleting…';
    btn.disabled    = true;

    const url = _deleteTarget.type === 'result'
        ? `{{ url('student/history/result') }}/${_deleteTarget.id}`
        : `{{ url('student/history/quiz') }}/${_deleteTarget.id}`;

    try {
        const res  = await fetch(url, {
            method:  'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        if (data.success) {
            // Animate card out
            const card = document.getElementById(_deleteTarget.cardId);
            if (card) {
                card.style.transition = 'opacity .3s, transform .3s';
                card.style.opacity    = '0';
                card.style.transform  = 'scale(.95)';
                setTimeout(() => card.remove(), 300);
            }
            closeDeleteModal();
            showToastMsg('success', data.message || 'Deleted successfully');

            // If grid is now empty, show empty state
            setTimeout(() => {
                const grid = document.getElementById('soloGrid');
                if (grid && grid.querySelectorAll('.qh-card:not([style*="display: none"])').length === 0) {
                    grid.innerHTML = `
                        <div class="empty-state" style="grid-column:1/-1">
                            <div class="es-icon">🎯</div>
                            <p style="font-weight:700;margin-bottom:8px">No quizzes here</p>
                            <a href="{{ route('student.quiz.index') }}" class="btn btn-grad mt-16">Generate a Quiz</a>
                        </div>`;
                }
            }, 350);
        } else {
            showToastMsg('error', data.message || 'Delete failed');
        }
    } catch {
        showToastMsg('error', 'Network error. Please try again.');
    }

    btn.textContent = 'Delete';
    btn.disabled    = false;
}

document.getElementById('deleteModal').addEventListener('click', e => {
    if (e.target === document.getElementById('deleteModal')) closeDeleteModal();
});

// ── Toast helper ───────────────────────────────────────────────────────────
function showToastMsg(type, msg) {
    if (typeof window.showToast === 'function') { window.showToast(type, msg); return; }
    const colors = { success: '#22c55e', error: '#ef4444', info: '#3b82f6' };
    const t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;padding:11px 18px;
        border-radius:10px;background:${colors[type]||'#333'};color:#fff;font-size:.84rem;
        font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,.25);transition:opacity .4s`;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 400); }, 3000);
}

function escHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

@endsection