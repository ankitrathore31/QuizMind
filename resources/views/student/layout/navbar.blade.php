<nav class="qm-navbar">
    <a href="#" class="nav-brand">
        <span class="brand-box">QM</span>
        <span>Quiz<span class="accent">Mind</span></span>
    </a>

    <a href="{{ route('student.dashboard') }}" class="nav-link active">
        🏠 Dashboard
    </a>
    <a href="{{ route('student.quiz.index') }}" class="nav-link">📚 My Quizzes</a>
    <a href="#" class="nav-link">⚔️ Battles</a>

    <div class="xp-pill">⚡ {{ number_format($student->xp) }} XP</div>
    <div class="streak-pill">🔥 {{ $student->streak }}d</div>

    <div class="nav-avatar-btn" title="{{ $user->name }}">
        @if ($student->avatar)
            <img src="{{ asset('storage/' . $student->avatar) }}">
        @else
            <span>{{ $student->display_name ? mb_substr($student->display_name, 0, 1) : '🧑‍🎓' }}</span>
        @endif
    </div>
</nav>
