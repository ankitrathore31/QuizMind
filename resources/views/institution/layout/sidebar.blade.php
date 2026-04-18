<aside class="sidebar">

    <div class="sidebar-section">Main</div>

    <a href="{{ route('student.dashboard') }}" class="sidebar-link active">
        🏠 Dashboard
    </a>

    <a href="{{route('student.quiz.index')}}" class="sidebar-link">📚 My Quizzes</a>
    <a href="#" class="sidebar-link">⚔️ Battles</a>

    <div class="sidebar-section">Progress</div>

    <a href="#" class="sidebar-link">📊 Stats</a>
    <a href="#" class="sidebar-link">🏆 Leaderboard</a>

    <div class="sidebar-section">Settings</div>

    <a href="#" class="sidebar-link">👤 Profile</a>

    <a href="{{ route('logout') }}" class="sidebar-link"
       onclick="event.preventDefault();document.getElementById('logoutForm').submit();">
        🚪 Logout
    </a>

    <form id="logoutForm" method="POST" action="{{ route('logout') }}">
        @csrf
    </form>

</aside>