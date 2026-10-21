@extends('layouts.websocket')

@section('title', 'Real-Time Server Heartbeat & Telemetry Streamer')

@section('content')
<!-- Header Banner -->
<div class="card-dark p-4 mb-4 border-0">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-info bg-opacity-20 text-info fw-bold px-3 py-1 rounded-pill">Real-Time Telemetry</span>
                <span class="badge bg-success bg-opacity-20 text-success fw-bold px-3 py-1 rounded-pill">Continuous Heartbeat</span>
            </div>
            <h2 class="fw-bold mb-1 text-white">📈 Server Heartbeat & Live Telemetry Streamer</h2>
            <p class="text-secondary mb-0">
                Pushes periodic server performance vitals (RAM usage, Peak memory, active messages, CPU load) over Soketi WebSockets into an interactive live-updating SVG telemetry chart.
            </p>
        </div>
        <div class="d-flex gap-2">
            <button id="streamHeartbeatBtn" class="btn btn-primary px-3 fw-semibold shadow" onclick="triggerHeartbeat()">
                <i class="bi bi-broadcast-pin me-1"></i> Push Live Heartbeat Now
            </button>
            <button id="autoStreamBtn" class="btn btn-outline-info px-3" onclick="toggleAutoStream()">
                <i class="bi bi-play-circle me-1"></i> Auto Stream (3s)
            </button>
        </div>
    </div>
</div>

<!-- Metrics Cards -->
<div class="row g-3 mb-4 text-center">
    <div class="col-md-3">
        <div class="card-dark p-3 border-0">
            <small class="text-secondary text-uppercase fw-bold">PHP Memory Allocated</small>
            <h3 id="statMemory" class="fw-bold text-success my-1 font-mono">{{ $initialData['memory_mb'] }} MB</h3>
            <span class="small text-secondary font-mono">Current Process</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-dark p-3 border-0">
            <small class="text-secondary text-uppercase fw-bold">Peak Memory Allocated</small>
            <h3 id="statPeakMemory" class="fw-bold text-info my-1 font-mono">{{ $initialData['peak_memory_mb'] }} MB</h3>
            <span class="small text-secondary font-mono">High-Water Mark</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-dark p-3 border-0">
            <small class="text-secondary text-uppercase fw-bold">Simulated Load Factor</small>
            <h3 id="statCpu" class="fw-bold text-warning my-1 font-mono">{{ $initialData['cpu_load_approx'] }}%</h3>
            <span class="small text-secondary font-mono">Resource Pressure</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-dark p-3 border-0">
            <small class="text-secondary text-uppercase fw-bold">Last Heartbeat Sync</small>
            <h3 id="statTimestamp" class="fw-bold text-white my-1 font-mono">{{ $initialData['timestamp'] }}</h3>
            <span class="small text-secondary font-mono">Live Clock</span>
        </div>
    </div>
</div>

<!-- Live Chart & Stream Area -->
<div class="card-dark p-4 border-0 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-graph-up text-primary"></i> Live Memory & Telemetry Heartbeat Waveform
        </h5>
        <span class="badge bg-secondary font-mono">Channel: server-telemetry</span>
    </div>

    <!-- SVG Real-Time Waveform Chart -->
    <div class="p-3 bg-black rounded-3 border border-secondary border-opacity-25 mb-3" style="position: relative;">
        <svg id="telemetrySvg" width="100%" height="220" style="overflow: visible;">
            <!-- Grid lines -->
            <line x1="0" y1="40" x2="100%" y2="40" stroke="#1f2937" stroke-dasharray="4" />
            <line x1="0" y1="90" x2="100%" y2="90" stroke="#1f2937" stroke-dasharray="4" />
            <line x1="0" y1="140" x2="100%" y2="140" stroke="#1f2937" stroke-dasharray="4" />
            <line x1="0" y1="190" x2="100%" y2="190" stroke="#1f2937" stroke-dasharray="4" />

            <!-- Path line -->
            <polyline id="telemetryPolyline" fill="none" stroke="#4f46e5" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" points="" />
        </svg>
    </div>

    <!-- Real-Time Packet Stream Log -->
    <label class="small text-secondary fw-bold mb-2">RAW WEBSOCKET TELEMETRY PACKET STREAM:</label>
    <div id="telemetryPacketLog" class="p-3 bg-black rounded-3 font-mono small text-secondary" style="height: 160px; overflow-y: auto;">
        <div>// WebSocket telemetry packets will stream here...</div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let chartPoints = [20, 25, 22, 28, 30, 26, 24, 29, 35, 30];
    let autoInterval = null;

    function renderChart() {
        const svg = document.getElementById('telemetrySvg');
        const polyline = document.getElementById('telemetryPolyline');
        const width = svg.clientWidth || 800;
        const height = 200;

        const maxVal = Math.max(...chartPoints, 50);
        const minVal = Math.min(...chartPoints, 10);
        const stepX = width / Math.max(chartPoints.length - 1, 1);

        let pointsStr = '';
        chartPoints.forEach((val, idx) => {
            const x = Math.round(idx * stepX);
            const normalized = (val - minVal) / Math.max(maxVal - minVal, 1);
            const y = Math.round(height - (normalized * (height - 30)) - 15);
            pointsStr += `${x},${y} `;
        });

        polyline.setAttribute('points', pointsStr.trim());
    }

    async function triggerHeartbeat() {
        try {
            const res = await fetch("{{ route('websocket.telemetry.stream') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            const data = await res.json();
            // Echo listener will process the broadcasted packet
        } catch (e) {
            console.error(e);
        }
    }

    function toggleAutoStream() {
        const btn = document.getElementById('autoStreamBtn');
        if (autoInterval) {
            clearInterval(autoInterval);
            autoInterval = null;
            btn.className = "btn btn-outline-info px-3";
            btn.innerHTML = `<i class="bi bi-play-circle me-1"></i> Auto Stream (3s)`;
        } else {
            triggerHeartbeat();
            autoInterval = setInterval(triggerHeartbeat, 3000);
            btn.className = "btn btn-info text-dark px-3 fw-bold";
            btn.innerHTML = `<i class="bi bi-stop-circle me-1"></i> Streaming Active (3s)`;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderChart();

        window.Echo.channel('server-telemetry')
            .listen('.server.heartbeat', (data) => {
                // Update stats
                document.getElementById('statMemory').innerText = `${data.memory_mb} MB`;
                document.getElementById('statPeakMemory').innerText = `${data.peak_memory_mb} MB`;
                document.getElementById('statCpu').innerText = `${data.cpu_load_approx}%`;
                document.getElementById('statTimestamp').innerText = data.timestamp;

                // Update chart
                chartPoints.push(data.memory_mb + (data.cpu_load_approx / 4));
                if (chartPoints.length > 25) chartPoints.shift();
                renderChart();

                // Append packet log
                const log = document.getElementById('telemetryPacketLog');
                const row = document.createElement('div');
                row.className = 'text-success mb-1';
                row.innerHTML = `<span class="text-secondary">[${data.timestamp}]</span> WebSocket Packet: memory=${data.memory_mb}MB peak=${data.peak_memory_mb}MB cpu_load=${data.cpu_load_approx}% throughput=${data.throughput_kb}KB`;
                log.appendChild(row);
                log.scrollTop = log.scrollHeight;
            });
    });
</script>
@endsection
