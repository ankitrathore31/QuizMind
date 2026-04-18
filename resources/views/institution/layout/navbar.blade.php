<aside class="inst-sidebar">
        <div class="inst-logo">
            <div class="inst-logo-text">
                <span style="font-size:1.4rem">🏛️</span>
                <span>{{ $institution->name }}</span>
            </div>
            <p style="font-size:.7rem;color:var(--muted);margin-top:6px">{{ $institution->code }}</p>
        </div>

        <nav class="inst-nav">
            <a href="javascript:showTab('dashboard')" class="inst-nav-item active" data-tab="dashboard">
                <span class="inst-nav-icon">📊</span> Dashboard
            </a>
            <a href="javascript:showTab('students')" class="inst-nav-item" data-tab="students">
                <span class="inst-nav-icon">👥</span> Students
            </a>
            <a href="javascript:showTab('referral')" class="inst-nav-item" data-tab="referral">
                <span class="inst-nav-icon">🔗</span> Referral Codes
            </a>
            <a href="javascript:showTab('battle')" class="inst-nav-item" data-tab="battle">
                <span class="inst-nav-icon">⚔️</span> Create Battle
            </a>
            <a href="javascript:showTab('history')" class="inst-nav-item" data-tab="history">
                <span class="inst-nav-icon">📋</span> Battle History
            </a>
            <a href="javascript:showTab('settings')" class="inst-nav-item" data-tab="settings">
                <span class="inst-nav-icon">⚙️</span> Settings
            </a>
            <hr style="border:none;border-top:1px solid var(--border);margin:10px 0">
            <a href="{{ route('logout') }}" class="inst-nav-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <span class="inst-nav-icon">🚪</span> Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">
                @csrf
            </form>
        </nav>
    </aside>