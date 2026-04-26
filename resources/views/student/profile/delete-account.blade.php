@extends('student.layout.master')

@section('title', 'Delete Account')

<style>
    .danger-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px 16px;
    }

    .danger-card {
        background: var(--card);
        border: 1px solid rgba(255, 71, 87, 0.35);
        border-radius: 20px;
        width: 100%;
        max-width: 480px;
        padding: 40px 36px;
        box-shadow: 0 20px 60px rgba(0,0,0,.55), 0 0 40px rgba(255,71,87,.08);
        animation: popIn .35s cubic-bezier(.34,1.56,.64,1) both;
    }

    @keyframes popIn {
        from { opacity:0; transform:scale(.9) translateY(16px); }
        to   { opacity:1; transform:scale(1)  translateY(0);    }
    }

    .danger-icon {
        font-size: 3rem;
        display: block;
        text-align: center;
        margin-bottom: 14px;
    }

    .danger-title {
        font-family: var(--fh);
        font-size: 1.4rem;
        font-weight: 800;
        color: #FF4757;
        text-align: center;
        margin-bottom: 8px;
    }

    .danger-sub {
        color: var(--muted);
        font-size: .85rem;
        text-align: center;
        line-height: 1.6;
        margin-bottom: 28px;
    }

    .warning-list {
        background: rgba(255,71,87,.07);
        border: 1px solid rgba(255,71,87,.2);
        border-radius: 10px;
        padding: 14px 18px;
        margin-bottom: 24px;
    }

    .warning-list p {
        font-size: .82rem;
        color: #FF6B7A;
        line-height: 1.9;
        margin: 0;
    }

    .btn-danger {
        background: linear-gradient(135deg, #FF4757, #C0392b);
        color: #fff;
        border: none;
        border-radius: var(--radius-xs, 8px);
        padding: 12px 24px;
        font-weight: 700;
        font-size: .9rem;
        cursor: pointer;
        width: 100%;
        transition: opacity .2s, transform .15s;
    }

    .btn-danger:hover   { opacity:.88; transform:translateY(-1px); }
    .btn-danger:active  { transform:translateY(0); }
    .btn-danger:disabled { opacity:.5; cursor:not-allowed; transform:none; }

    .input-error {
        color: #FF4757;
        font-size: .78rem;
        margin-top: 6px;
        display: none;
    }

    .divider { border:none; border-top:1px solid var(--border); margin:20px 0; }
</style>

@section('content')
    <div class="orb o1"></div>
    <div class="orb o2"></div>

    <div class="danger-page">
        <div class="danger-card">

            <span class="danger-icon">⚠️</span>
            <div class="danger-title">Delete Your Account</div>
            <p class="danger-sub">
                This action is <strong>permanent and irreversible</strong>.<br>
                All your data will be deleted immediately.
            </p>

            <div class="warning-list">
                <p>
                    🗑️ Your profile &amp; display name will be removed<br>
                    📊 All XP, levels, and badges will be lost<br>
                    🔥 Your streak history will be wiped<br>
                    ⚔️ Battle records will be deleted<br>
                    🏆 Leaderboard rank will be removed
                </p>
            </div>

            @if ($errors->any())
                <div style="background:rgba(255,71,87,.1);border:1px solid rgba(255,71,87,.3);border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:.84rem;color:#FF4757">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('student.account.delete') }}" id="deleteForm">
                @csrf
                @method('DELETE')

                <div class="form-group">
                    <label class="form-label" style="color:var(--text)">
                        Confirm with your password
                    </label>
                    <input
                        type="password"
                        name="password"
                        id="passwordField"
                        class="form-input"
                        placeholder="Enter your current password…"
                        autocomplete="current-password"
                        required
                    >
                    <div class="input-error" id="pwErr">Please enter your password.</div>
                </div>

                <button type="submit" class="btn-danger" id="deleteBtn" onclick="return confirmDelete(event)">
                    🗑️ Permanently Delete My Account
                </button>
            </form>

            <hr class="divider">

            <div style="text-align:center">
                <a href="{{ route('student.dashboard') }}" class="btn btn-ghost btn-sm">
                    ← Go Back to Dashboard
                </a>
            </div>

        </div>
    </div>


<script>
    function confirmDelete(e) {
        const pw = document.getElementById('passwordField').value.trim();
        if (!pw) {
            e.preventDefault();
            document.getElementById('pwErr').style.display = 'block';
            return false;
        }
        document.getElementById('pwErr').style.display = 'none';

        const confirmed = window.confirm(
            '⚠️ Are you absolutely sure?\n\nThis will permanently delete your account and ALL data. This cannot be undone.'
        );

        if (!confirmed) {
            e.preventDefault();
            return false;
        }

        document.getElementById('deleteBtn').textContent = 'Deleting…';
        document.getElementById('deleteBtn').disabled    = true;
        return true;
    }
</script>
@endsection