<div class="topbar">

    <button class="menu-toggle" onclick="toggleSidebar()">☰</button>

    <div class="top-right">
        <div class="xp-pill">⚡ {{ number_format($student->xp) }}</div>
        <div class="streak-pill">🔥 {{ $student->streak }}</div>

        <div class="nav-avatar-btn">
            {{ $student->display_name ? mb_substr($student->display_name, 0, 1) : '🧑‍🎓' }}
        </div>
    </div>

</div>