@extends('layouts.websocket')

@section('title', 'Soketi Server Health & Diagnostics Studio')

@section('content')
<!-- Header Banner -->
<div class="card-dark p-4 mb-4 border-0">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary bg-opacity-20 text-primary fw-bold px-3 py-1 rounded-pill">Health Monitor</span>
                <span class="badge bg-success bg-opacity-20 text-success fw-bold px-3 py-1 rounded-pill">Port 6001 Inspector</span>
            </div>
            <h2 class="fw-bold mb-1 text-white">⚡ Soketi Server Health & Real-Time Diagnostics</h2>
            <p class="text-secondary mb-0">
                Inspect active WebSocket socket connections, measure browser-to-server Ping/Pong round-trip latency, monitor channel subscriptions, and dispatch test events.
            </p>
        </div>
        <div>
            <button id="sendPingBtn" class="btn btn-primary px-3 fw-semibold shadow" onclick="triggerPing()">
                <i class="bi bi-send-fill me-1"></i> Dispatch Test Ping Event
            </button>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Metrics & Server Config -->
    <div class="col-lg-5">
        <!-- Live Connection State Card -->
        <div class="card-dark p-4 mb-4 border-0">
            <h5 class="fw-bold mb-3 text-white d-flex align-items-center gap-2">
                <i class="bi bi-router text-primary"></i> Socket Connection Telemetry
            </h5>

            <div class="row g-3 text-center mb-4">
                <div class="col-6">
                    <div class="p-3 rounded-3 bg-black bg-opacity-50 border border-secondary border-opacity-25">
                        <small class="text-secondary d-block mb-1">Connection State</small>
                        <span id="diagnosticsState" class="fw-bold fs-5 text-warning font-mono">CONNECTING</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 bg-black bg-opacity-50 border border-secondary border-opacity-25">
                        <small class="text-secondary d-block mb-1">Round-Trip Latency</small>
                        <span id="diagnosticsLatency" class="fw-bold fs-5 text-success font-mono">-- ms</span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 bg-black bg-opacity-50 border border-secondary border-opacity-25 text-start font-mono small">
                        <div class="text-secondary mb-1">Assigned Socket ID:</div>
                        <span id="diagnosticsSocketId" class="text-info fw-bold">Waiting for handshake...</span>
                    </div>
                </div>
            </div>

            <!-- Configuration Table -->
            <h6 class="fw-bold text-white mb-2">Broadcasting Configuration</h6>
            <div class="table-responsive border border-secondary border-opacity-25 rounded-3">
                <table class="table table-dark table-sm font-mono small mb-0">
                    <tbody>
                        <tr><td class="text-secondary">Host</td><td class="text-white">{{ $config['host'] }}</td></tr>
                        <tr><td class="text-secondary">Port</td><td class="text-warning">{{ $config['port'] }} (Soketi Default)</td></tr>
                        <tr><td class="text-secondary">App Key</td><td class="text-info">{{ $config['app_key'] }}</td></tr>
                        <tr><td class="text-secondary">Protocol</td><td class="text-white">Pusher v7 / WebSockets (ws://)</td></tr>
                        <tr><td class="text-secondary">Active Channel</td><td class="text-success">soketi-diagnostics</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- How to Start Soketi -->
        <div class="card-dark p-4 border-0">
            <h6 class="fw-bold text-white mb-2"><i class="bi bi-terminal text-info me-1"></i> Starting the Soketi Server:</h6>
            <div class="p-3 bg-black rounded-3 font-mono text-info small mb-2">
                npx @soketi/soketi start
            </div>
            <p class="small text-secondary mb-0">
                Soketi is an ultra-fast, open-source Pusher-compatible WebSocket server built on top of uWebSockets.js. Run the command above in a separate terminal.
            </p>
        </div>
    </div>

    <!-- Right Column: Real-Time Event Log Terminal -->
    <div class="col-lg-7">
        <div class="card-dark p-4 border-0 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-terminal-fill text-success"></i> Live WebSocket Frame Terminal
                </h5>
                <button class="btn btn-sm btn-outline-secondary" onclick="clearTerminal()">
                    <i class="bi bi-trash"></i> Clear Log
                </button>
            </div>

            <div id="terminalStream" class="p-3 rounded-3 bg-black border border-secondary border-opacity-25 font-mono text-light" style="height: 480px; overflow-y: auto; font-size: 12px; line-height: 1.6;">
                <div class="text-secondary">// Real-time WebSocket event frames will stream below...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let pingStartTime = 0;

    function logTerminal(message, type = 'info') {
        const stream = document.getElementById('terminalStream');
        const line = document.createElement('div');
        const timestamp = new Date().toLocaleTimeString();

        const colorClass = {
            'info': 'text-info',
            'success': 'text-success',
            'warning': 'text-warning',
            'error': 'text-danger'
        }[type] || 'text-light';

        line.className = `mb-1 ${colorClass}`;
        line.innerHTML = `<span class="text-secondary">[${timestamp}]</span> ${message}`;
        stream.appendChild(line);
        stream.scrollTop = stream.scrollHeight;
    }

    function clearTerminal() {
        document.getElementById('terminalStream').innerHTML = `<div class="text-secondary">// Terminal cleared.</div>`;
    }

    async function triggerPing() {
        const btn = document.getElementById('sendPingBtn');
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Dispatching...`;

        pingStartTime = performance.now();
        logTerminal(`[OUTBOUND] Dispatching test ping to backend broadcast router...`, 'warning');

        try {
            const res = await fetch("{{ route('websocket.diagnostics.ping') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await res.json();
            if (data.success) {
                logTerminal(`[SERVER ACK] Event dispatched. ID: ${data.ping_id}`, 'info');
            } else {
                logTerminal(`[ERROR] Server broadcast rejected: ${data.error}`, 'error');
                if (data.suggestion) logTerminal(`[TIP] ${data.suggestion}`, 'warning');
            }
        } catch (err) {
            logTerminal(`[ERROR] HTTP Request failed: ${err.message}`, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-send-fill me-1"></i> Dispatch Test Ping Event`;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Listen to Pusher connection life-cycle events
        const pusher = window.Echo.connector.pusher;

        pusher.connection.bind('connected', () => {
            const socketId = pusher.connection.socket_id;
            document.getElementById('diagnosticsState').innerText = 'CONNECTED';
            document.getElementById('diagnosticsState').className = 'fw-bold fs-5 text-success font-mono';
            document.getElementById('diagnosticsSocketId').innerText = socketId;
            logTerminal(`[HANDSHAKE SUCCESS] WebSocket connection established to Soketi. Socket ID: ${socketId}`, 'success');
        });

        pusher.connection.bind('connecting', () => {
            document.getElementById('diagnosticsState').innerText = 'CONNECTING';
            document.getElementById('diagnosticsState').className = 'fw-bold fs-5 text-warning font-mono';
            logTerminal(`[CONNECTING] Initiating WebSocket handshake on ws://127.0.0.1:6001/app/local`, 'info');
        });

        pusher.connection.bind('unavailable', () => {
            document.getElementById('diagnosticsState').innerText = 'OFFLINE';
            document.getElementById('diagnosticsState').className = 'fw-bold fs-5 text-danger font-mono';
            logTerminal(`[UNAVAILABLE] Soketi server is unreachable on port 6001. Please run 'npx @soketi/soketi start'.`, 'error');
        });

        pusher.connection.bind('disconnected', () => {
            document.getElementById('diagnosticsState').innerText = 'DISCONNECTED';
            document.getElementById('diagnosticsState').className = 'fw-bold fs-5 text-danger font-mono';
            logTerminal(`[DISCONNECTED] WebSocket connection closed.`, 'warning');
        });

        // Subscribe to diagnostics channel
        logTerminal(`[SUBSCRIBE] Subscribing to public channel: 'soketi-diagnostics'`, 'info');
        window.Echo.channel('soketi-diagnostics')
            .listen('.diagnostics.ping', (e) => {
                const roundTrip = pingStartTime > 0 ? Math.round(performance.now() - pingStartTime) : 15;
                document.getElementById('diagnosticsLatency').innerText = `${roundTrip} ms`;
                logTerminal(`⚡ [EVENT RECEIVED] '.diagnostics.ping' arrived over WebSocket in ${roundTrip}ms! Payload: ${JSON.stringify(e)}`, 'success');
                showGlobalToast('⚡ Soketi Ping Received', `Round-trip latency: ${roundTrip}ms from server.`, 'success');
            });
    });
</script>
@endsection
