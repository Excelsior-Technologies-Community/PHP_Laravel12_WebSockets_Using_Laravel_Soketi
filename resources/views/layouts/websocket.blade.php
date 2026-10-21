<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Laravel 12 WebSockets & Soketi Studio')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- CDN Pusher & Laravel Echo for standalone real-time WebSocket connectivity -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <style>
        body {
            background: #0b0f19;
            color: #f1f5f9;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .navbar-custom {
            background: #111827;
            border-bottom: 1px solid #1f2937;
            padding: 12px 24px;
        }

        .brand-logo {
            font-weight: 800;
            font-size: 1.15rem;
            color: #ffffff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link-btn {
            color: #9ca3af;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.88rem;
            padding: 7px 14px;
            border-radius: 8px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }

        .nav-link-btn.active {
            color: #ffffff;
            background: #4f46e5;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
        }

        .card-dark {
            background: #111827;
            border: 1px solid #1f2937;
            border-radius: 14px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.3);
        }

        .soketi-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            border: 1px solid transparent;
        }

        .status-connected {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border-color: rgba(16, 185, 129, 0.3);
        }

        .status-reconnecting {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
            border-color: rgba(245, 158, 11, 0.3);
        }

        .status-disconnected {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.3);
        }

        /* Toast notifications container */
        #toastContainer {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 360px;
        }

        footer {
            margin-top: auto;
            background: #111827;
            border-top: 1px solid #1f2937;
            padding: 20px 0;
            color: #6b7280;
            font-size: 13px;
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Top Navigation Header -->
    <header class="navbar-custom sticky-top">
        <div class="container-fluid px-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <a href="{{ route('websocket.diagnostics') }}" class="brand-logo">
                <i class="bi bi-broadcast text-primary fs-4"></i>
                <span>Laravel 12 <span class="text-primary">Soketi WebSockets</span></span>
            </a>

            <!-- Nav Links -->
            <nav class="d-flex align-items-center gap-1 flex-wrap">
                <a href="{{ route('websocket.diagnostics') }}" class="nav-link-btn {{ request()->routeIs('websocket.diagnostics*') ? 'active' : '' }}">
                    <i class="bi bi-activity text-info"></i> Diagnostics
                </a>
                <a href="{{ route('websocket.presence') }}" class="nav-link-btn {{ request()->routeIs('websocket.presence*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill text-success"></i> Presence Radar
                </a>
                <a href="{{ route('chat.index') }}" class="nav-link-btn {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                    <i class="bi bi-chat-dots-fill text-primary"></i> Chat & Typing
                </a>
                <a href="{{ route('websocket.polls') }}" class="nav-link-btn {{ request()->routeIs('websocket.polls*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-fill text-warning"></i> Live Polls
                </a>
                <a href="{{ route('notifications.index') }}" class="nav-link-btn {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    <i class="bi bi-bell-fill text-danger"></i> Toasts
                </a>
                <a href="{{ route('websocket.telemetry') }}" class="nav-link-btn {{ request()->routeIs('websocket.telemetry*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow text-info"></i> Telemetry
                </a>
            </nav>

            <!-- WebSocket Connection Status Pill -->
            <div>
                <span id="globalSoketiPill" class="soketi-status-pill status-reconnecting">
                    <span class="spinner-grow spinner-grow-sm" role="status" style="width: 8px; height: 8px;"></span>
                    <span>Soketi: Connecting (6001)...</span>
                </span>
            </div>
        </div>
    </header>

    <!-- Floating Toast Container -->
    <div id="toastContainer"></div>

    <!-- Main Content -->
    <main class="container-fluid px-lg-5 py-4 flex-grow-1">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center rounded-3 shadow-sm border-0 mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
                <div class="fw-semibold text-dark">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-1 fw-bold text-light">Laravel 12 WebSockets &bull; Powered by Soketi (Pusher Protocol on Port 6001)</p>
            <p class="mb-0 small text-secondary">
                Public Channels &bull; Presence Channels &bull; Ephemeral Whispers &bull; Live Polls &bull; Audio Chimes &bull; Real-Time Telemetry
            </p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Global Soketi Echo Client Initializer & Audio Chime -->
    <script>
        const SOKETI_CONFIG = {
            broadcaster: 'pusher',
            key: "{{ config('broadcasting.connections.pusher.key', 'local') }}",
            wsHost: "{{ config('broadcasting.connections.pusher.options.host', '127.0.0.1') }}",
            wsPort: {{ (int) config('broadcasting.connections.pusher.options.port', 6001) }},
            wssPort: {{ (int) config('broadcasting.connections.pusher.options.port', 6001) }},
            forceTLS: false,
            disableStats: true,
            enabledTransports: ['ws', 'flash'],
        };

        // Initialize Pusher & Echo globally
        window.Pusher = Pusher;
        window.Echo = new Echo({
            ...SOKETI_CONFIG,
            authorizer: (channel, options) => {
                return {
                    authorize: (socketId, callback) => {
                        fetch('/broadcasting/auth', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                socket_id: socketId,
                                channel_name: channel.name
                            })
                        })
                        .then(response => response.json())
                        .then(data => callback(false, data))
                        .catch(error => callback(true, error));
                    }
                };
            }
        });

        // Connection State Handlers for Global Pill
        window.Echo.connector.pusher.connection.bind('connected', () => {
            const pill = document.getElementById('globalSoketiPill');
            pill.className = 'soketi-status-pill status-connected';
            pill.innerHTML = `<i class="bi bi-check-circle-fill"></i> Soketi: Connected (:6001)`;
        });

        window.Echo.connector.pusher.connection.bind('connecting', () => {
            const pill = document.getElementById('globalSoketiPill');
            pill.className = 'soketi-status-pill status-reconnecting';
            pill.innerHTML = `<span class="spinner-grow spinner-grow-sm" role="status" style="width: 8px; height: 8px;"></span> Soketi: Handshaking...`;
        });

        window.Echo.connector.pusher.connection.bind('disconnected', () => {
            const pill = document.getElementById('globalSoketiPill');
            pill.className = 'soketi-status-pill status-disconnected';
            pill.innerHTML = `<i class="bi bi-x-circle-fill"></i> Soketi: Disconnected`;
        });

        window.Echo.connector.pusher.connection.bind('unavailable', () => {
            const pill = document.getElementById('globalSoketiPill');
            pill.className = 'soketi-status-pill status-disconnected';
            pill.innerHTML = `<i class="bi bi-exclamation-triangle-fill"></i> Soketi: Port 6001 Unreachable`;
        });

        // Synthesize a pleasant chime sound using Web Audio API (Zero external assets)
        function playChimeSound(type = 'info') {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const now = ctx.currentTime;
                
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                
                osc.type = 'sine';
                if (type === 'urgent' || type === 'danger') {
                    osc.frequency.setValueAtTime(880, now);
                    osc.frequency.exponentialRampToValueAtTime(440, now + 0.3);
                } else {
                    osc.frequency.setValueAtTime(587.33, now); // D5
                    osc.frequency.exponentialRampToValueAtTime(880, now + 0.15); // A5
                }
                
                gain.gain.setValueAtTime(0.15, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.45);
                
                osc.connect(gain);
                gain.connect(ctx.destination);
                
                osc.start(now);
                osc.stop(now + 0.5);
            } catch (e) {
                // Audio autoplay might be blocked until user gesture
            }
        }

        // Global Toast Dispatcher
        function showGlobalToast(title, message, type = 'info') {
            playChimeSound(type);

            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            
            const badgeClass = {
                'success': 'bg-success',
                'warning': 'bg-warning text-dark',
                'urgent': 'bg-danger',
                'danger': 'bg-danger',
                'info': 'bg-info text-dark'
            }[type] || 'bg-primary';

            toast.className = 'card-dark p-3 shadow-lg border-start border-4 border-' + (type === 'urgent' ? 'danger' : type);
            toast.style.animation = 'slideIn 0.3s ease-out';
            toast.innerHTML = `
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge ${badgeClass} text-uppercase" style="font-size: 10px;">${type}</span>
                            <strong class="text-white small">${escapeHtml(title)}</strong>
                        </div>
                        <p class="text-secondary small mb-0">${escapeHtml(message)}</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white btn-sm" onclick="this.closest('.card-dark').remove()"></button>
                </div>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.style.opacity = '0';
                    toast.style.transition = 'opacity 0.5s';
                    setTimeout(() => toast.remove(), 500);
                }
            }, 6000);
        }

        function escapeHtml(text) {
            if (!text) return '';
            const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
            return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    </script>
    @yield('scripts')
</body>
</html>
