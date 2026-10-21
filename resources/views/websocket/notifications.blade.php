@extends('layouts.websocket')

@section('title', 'Categorized Notification Toast Center')

@section('content')
<!-- Header Banner -->
<div class="card-dark p-4 mb-4 border-0">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-danger bg-opacity-20 text-danger fw-bold px-3 py-1 rounded-pill">Toast Center</span>
                <span class="badge bg-info bg-opacity-20 text-info fw-bold px-3 py-1 rounded-pill">Web Audio Synthesizer</span>
            </div>
            <h2 class="fw-bold mb-1 text-white">🔔 Categorized Notification Toast Center</h2>
            <p class="text-secondary mb-0">
                Trigger real-time notifications with severity tags (<code class="text-info font-mono">info</code>, <code class="text-success font-mono">success</code>, <code class="text-warning font-mono">warning</code>, <code class="text-danger font-mono">urgent</code>). Hear instant audio chimes synthesized via the Web Audio API without page reload.
            </p>
        </div>
        <div>
            <form action="{{ route('notifications.clear') }}" method="POST" onsubmit="return confirm('Clear notification history?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i> Clear Notifications
                </button>
            </form>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Notification Dispatcher & Presets -->
    <div class="col-lg-5">
        <div class="card-dark p-4 border-0 mb-4">
            <h5 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-send-check text-primary"></i> Dispatch New Notification
            </h5>

            <!-- Quick 1-Click Presets -->
            <label class="form-label small text-secondary fw-semibold">1-Click Sample Triggers:</label>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <button type="button" class="btn btn-sm btn-outline-success" onclick="fillNotification('Payment Received', 'Customer #9822 just paid $250.00 via Stripe.', 'success')">
                    💰 Payment Received
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="fillNotification('High CPU Spikes Detected', 'Server #3 is reaching 94% CPU capacity.', 'urgent')">
                    🚨 Server Alert
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning text-white" onclick="fillNotification('Stock Low Warning', 'Item iPhone 15 Pro is down to 2 units.', 'warning')">
                    ⚠️ Low Stock
                </button>
                <button type="button" class="btn btn-sm btn-outline-info" onclick="fillNotification('New User Registered', 'Sarah Connor created an account.', 'info')">
                    👤 New User
                </button>
            </div>

            <!-- Form -->
            <form id="notificationForm" onsubmit="handleSendNotification(event)">
                <div class="mb-3">
                    <label class="form-label text-white small fw-semibold">Notification Title</label>
                    <input type="text" id="notifTitle" class="form-control bg-dark text-white border-secondary" value="Order #1042 Dispatched" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-white small fw-semibold">Message Payload</label>
                    <textarea id="notifMessage" class="form-control bg-dark text-white border-secondary" rows="3" required>Package has been handed over to courier delivery.</textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label text-white small fw-semibold">Severity Category</label>
                    <select id="notifType" class="form-select bg-dark text-white border-secondary">
                        <option value="info">Information (Blue)</option>
                        <option value="success" selected>Success (Green)</option>
                        <option value="warning">Warning (Yellow)</option>
                        <option value="urgent">Urgent / Critical (Red)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" id="submitNotifBtn">
                    <i class="bi bi-broadcast me-1"></i> Broadcast Live Notification
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column: Live Feed & History -->
    <div class="col-lg-7">
        <div class="card-dark p-4 border-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-bell text-danger"></i> Notification History & Feed
                </h5>
                <span class="badge bg-secondary font-mono">{{ $stats['total'] }} recorded</span>
            </div>

            <!-- Category Filter Pills -->
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <a href="{{ route('notifications.index') }}" class="btn btn-sm {{ !$filterType ? 'btn-primary' : 'btn-outline-secondary' }}">
                    All ({{ $stats['total'] }})
                </a>
                <a href="{{ route('notifications.index', ['type' => 'info']) }}" class="btn btn-sm {{ $filterType === 'info' ? 'btn-info' : 'btn-outline-secondary' }}">
                    Info ({{ $stats['info'] }})
                </a>
                <a href="{{ route('notifications.index', ['type' => 'success']) }}" class="btn btn-sm {{ $filterType === 'success' ? 'btn-success' : 'btn-outline-secondary' }}">
                    Success ({{ $stats['success'] }})
                </a>
                <a href="{{ route('notifications.index', ['type' => 'warning']) }}" class="btn btn-sm {{ $filterType === 'warning' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">
                    Warning ({{ $stats['warning'] }})
                </a>
                <a href="{{ route('notifications.index', ['type' => 'urgent']) }}" class="btn btn-sm {{ $filterType === 'urgent' ? 'btn-danger' : 'btn-outline-secondary' }}">
                    Urgent ({{ $stats['urgent'] }})
                </a>
            </div>

            <!-- Notification List -->
            <div id="notificationList" class="d-flex flex-column gap-3" style="max-height: 480px; overflow-y: auto;">
                @forelse($notifications as $n)
                <div class="p-3 bg-black rounded-3 border border-secondary border-opacity-25 border-start border-4 border-{{ $n->type === 'urgent' ? 'danger' : $n->type }}">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <span class="badge bg-{{ $n->type === 'urgent' ? 'danger' : $n->type }} text-uppercase mb-1" style="font-size: 10px;">{{ $n->type }}</span>
                            <h6 class="fw-bold text-white mb-0">{{ $n->title }}</h6>
                        </div>
                        <small class="text-secondary font-mono" style="font-size: 11px;">{{ $n->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="text-secondary small mb-0">{{ $n->message }}</p>
                </div>
                @empty
                <div class="text-center py-5 text-secondary">
                    <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>
                    No notifications in this category.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function fillNotification(title, msg, type) {
        document.getElementById('notifTitle').value = title;
        document.getElementById('notifMessage').value = msg;
        document.getElementById('notifType').value = type;
    }

    async function handleSendNotification(e) {
        e.preventDefault();
        const title = document.getElementById('notifTitle').value.trim();
        const message = document.getElementById('notifMessage').value.trim();
        const type = document.getElementById('notifType').value;
        const btn = document.getElementById('submitNotifBtn');

        btn.disabled = true;

        try {
            const res = await fetch("{{ route('notifications.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ title: title, message: message, type: type })
            });
            const data = await res.json();
            if (data.status) {
                // Audio and toast will be triggered via the WebSocket channel listener
            }
        } catch (err) {
            alert('Failed: ' + err.message);
        } finally {
            btn.disabled = false;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        window.Echo.channel('notifications')
            .listen('.notification.sent', (e) => {
                const n = e.notification;
                showGlobalToast(n.title, n.message, n.type);

                // Prepend to list
                const list = document.getElementById('notificationList');
                const card = document.createElement('div');
                card.className = `p-3 bg-black rounded-3 border border-secondary border-opacity-25 border-start border-4 border-${n.type === 'urgent' ? 'danger' : n.type}`;
                card.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <span class="badge bg-${n.type === 'urgent' ? 'danger' : n.type} text-uppercase mb-1" style="font-size: 10px;">${escapeHtml(n.type)}</span>
                            <h6 class="fw-bold text-white mb-0">${escapeHtml(n.title)}</h6>
                        </div>
                        <small class="text-secondary font-mono" style="font-size: 11px;">Just now</small>
                    </div>
                    <p class="text-secondary small mb-0">${escapeHtml(n.message)}</p>
                `;
                list.insertBefore(card, list.firstChild);
            });
    });
</script>
@endsection
