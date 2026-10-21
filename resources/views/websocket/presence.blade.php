@extends('layouts.websocket')

@section('title', 'Presence Channels & Live Active Users Radar')

@section('content')
<!-- Header Banner -->
<div class="card-dark p-4 mb-4 border-0">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success bg-opacity-20 text-success fw-bold px-3 py-1 rounded-pill">PresenceChannel</span>
                <span class="badge bg-info bg-opacity-20 text-info fw-bold px-3 py-1 rounded-pill">Identity Aware</span>
            </div>
            <h2 class="fw-bold mb-1 text-white">👥 Presence Channels & Live Active Users Radar</h2>
            <p class="text-secondary mb-0">
                Demonstrates Laravel's <code class="text-primary font-mono">PresenceChannel</code>. As browser tabs open or close, clients trigger <code class="text-info font-mono">here()</code>, <code class="text-success font-mono">joining()</code>, and <code class="text-danger font-mono">leaving()</code> lifecycle events in real-time.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary px-3 py-2 fs-6 font-mono">
                <i class="bi bi-broadcast me-1"></i> Channel: presence-room.{{ $roomId }}
            </span>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: User Radar & Live Counter -->
    <div class="col-lg-7">
        <div class="card-dark p-4 border-0 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-radar text-success"></i> Connected Room Participants
                </h5>
                <span class="badge bg-success bg-opacity-20 text-success px-3 py-1 rounded-pill font-mono">
                    <span id="activeCount">0</span> Online
                </span>
            </div>

            <!-- Participant Avatars Grid -->
            <div id="usersRadarGrid" class="row g-3 mb-4">
                <div class="col-12 text-center py-5 text-secondary">
                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                    Connecting to presence channel...
                </div>
            </div>

            <!-- Lifecycle Activity Feed -->
            <h6 class="fw-bold text-white mb-2"><i class="bi bi-clock-history text-secondary me-1"></i> Real-Time Room Activity Feed</h6>
            <div id="presenceActivityFeed" class="p-3 bg-black rounded-3 font-mono small text-secondary" style="height: 180px; overflow-y: auto;">
                <div>// Room presence join/leave events will stream here...</div>
            </div>
        </div>
    </div>

    <!-- Right Column: Switch Identity & Multi-Tab Testing -->
    <div class="col-lg-5">
        <!-- Current Identity Card -->
        <div class="card-dark p-4 border-0 mb-4">
            <h5 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-person-badge text-primary"></i> Your Active Session
            </h5>
            <div class="p-3 bg-black rounded-3 border border-secondary border-opacity-25 mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-1">👤</div>
                    <div>
                        <h6 class="fw-bold text-white mb-0">{{ $activeIdentity }}</h6>
                        <small class="text-secondary">{{ $currentUser ? $currentUser->email : 'Guest Session' }}</small>
                    </div>
                </div>
            </div>

            <!-- Switch User Form -->
            <h6 class="fw-bold text-white mb-2">Simulate Other Users in Second Tab</h6>
            <p class="small text-secondary mb-3">Open this page in a private window or another browser tab with a different identity to observe instant radar updates:</p>

            <form action="{{ route('websocket.presence.switch') }}" method="POST">
                @csrf
                <div class="d-flex flex-column gap-2 mb-3">
                    @foreach($users as $u)
                    <button type="submit" name="user_id" value="{{ $u->id }}" class="btn btn-outline-secondary text-start p-2 d-flex align-items-center justify-content-between {{ ($currentUser && $currentUser->id === $u->id) ? 'active border-primary' : '' }}">
                        <span class="text-white">{{ $u->name }}</span>
                        <span class="badge bg-secondary font-mono small">User #{{ $u->id }}</span>
                    </button>
                    @endforeach
                    <button type="submit" name="user_id" value="" class="btn btn-outline-warning text-start p-2">
                        <span>🌐 Switch to Anonymous Guest</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let onlineUsers = new Map();

    function logPresenceActivity(msg, type = 'info') {
        const feed = document.getElementById('presenceActivityFeed');
        const div = document.createElement('div');
        const time = new Date().toLocaleTimeString();

        const colorClass = {
            'join': 'text-success',
            'leave': 'text-danger',
            'info': 'text-info'
        }[type] || 'text-secondary';

        div.className = `mb-1 ${colorClass}`;
        div.innerHTML = `<span class="text-secondary">[${time}]</span> ${msg}`;
        feed.appendChild(div);
        feed.scrollTop = feed.scrollHeight;
    }

    function renderUsers() {
        const grid = document.getElementById('usersRadarGrid');
        const countSpan = document.getElementById('activeCount');
        countSpan.innerText = onlineUsers.size;

        grid.innerHTML = '';
        if (onlineUsers.size === 0) {
            grid.innerHTML = `<div class="col-12 text-center py-4 text-secondary">No participants in room.</div>`;
            return;
        }

        onlineUsers.forEach((u) => {
            const col = document.createElement('div');
            col.className = 'col-sm-6 col-md-4';
            col.innerHTML = `
                <div class="p-3 bg-black rounded-3 border border-secondary border-opacity-25 text-center">
                    <div class="fs-2 mb-1">${u.avatar || '👤'}</div>
                    <div class="fw-bold text-white text-truncate small">${escapeHtml(u.name)}</div>
                    <small class="text-success font-mono" style="font-size: 11px;">🟢 Online</small>
                </div>
            `;
            grid.appendChild(col);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        logPresenceActivity("Subscribing to presence-room.general...", "info");

        window.Echo.join('presence-room.general')
            .here((users) => {
                onlineUsers.clear();
                users.forEach(u => onlineUsers.set(u.id, u));
                renderUsers();
                logPresenceActivity(`Synced room roster: ${users.length} user(s) currently active.`, "info");
            })
            .joining((user) => {
                onlineUsers.set(user.id, user);
                renderUsers();
                logPresenceActivity(`👋 ${user.name} joined the room.`, "join");
                showGlobalToast('User Joined Room', `${user.name} is now online.`, 'info');
            })
            .leaving((user) => {
                onlineUsers.delete(user.id);
                renderUsers();
                logPresenceActivity(`🏃 ${user.name} left the room.`, "leave");
                showGlobalToast('User Left Room', `${user.name} disconnected.`, 'warning');
            })
            .error((error) => {
                logPresenceActivity(`Channel authentication error: ${JSON.stringify(error)}`, "leave");
            });
    });
</script>
@endsection
