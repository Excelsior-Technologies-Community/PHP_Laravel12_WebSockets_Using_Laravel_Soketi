@extends('layouts.websocket')

@section('title', 'Live Interactive Polling & Instant Voting Room')

@section('styles')
<style>
    .poll-option-card {
        background: #111827;
        border: 1px solid #1f2937;
        border-radius: 12px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
    }
    .poll-option-card:hover {
        border-color: #4f46e5;
        transform: translateY(-2px);
    }
    .poll-progress-bg {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        background: rgba(79, 70, 229, 0.2);
        transition: width 0.4s ease-out;
        z-index: 1;
    }
    .poll-content-relative {
        position: relative;
        z-index: 2;
    }
</style>
@endsection

@section('content')
<!-- Header Banner -->
<div class="card-dark p-4 mb-4 border-0">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning bg-opacity-20 text-warning fw-bold px-3 py-1 rounded-pill">Real-Time Polling</span>
                <span class="badge bg-primary bg-opacity-20 text-primary fw-bold px-3 py-1 rounded-pill">Multi-Client Sync</span>
            </div>
            <h2 class="fw-bold mb-1 text-white">📊 Live Interactive Polling & Voting Room</h2>
            <p class="text-secondary mb-0">
                Vote on any option below. The vote count, percentage distribution, and progress bars animate immediately across all connected browsers via Soketi WebSockets.
            </p>
        </div>
        <div>
            <form action="{{ route('websocket.polls.reset') }}" method="POST" onsubmit="return confirm('Reset all vote tallies to zero?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset Tallies
                </button>
            </form>
        </div>
    </div>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="card-dark p-4 border-0">
            <!-- Poll Question -->
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <span class="badge bg-primary mb-2 font-mono">LIVE COMMUNITY POLL</span>
                    <h4 class="fw-bold text-white mb-1">{{ $poll ? $poll->question : 'Active Poll' }}</h4>
                    <small class="text-secondary">Click any option below to cast an instant vote.</small>
                </div>
                <div class="text-end">
                    <span class="fs-4 fw-bold text-warning font-mono" id="totalVotesDisplay">{{ $totalVotes }}</span>
                    <small class="text-secondary d-block">Total Votes</small>
                </div>
            </div>

            <!-- Options Container -->
            <div class="d-flex flex-column gap-3 mb-4" id="pollOptionsContainer">
                @if($poll)
                    @foreach($stats as $opt)
                    <div class="poll-option-card" onclick="castVote({{ $opt['id'] }})">
                        <div class="poll-progress-bg" id="bar-{{ $opt['id'] }}" style="width: {{ $opt['percentage'] }}%;"></div>
                        <div class="poll-content-relative d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold text-white d-block mb-1">{{ $opt['option_text'] }}</span>
                                <small class="text-secondary font-mono" id="count-{{ $opt['id'] }}">{{ $opt['votes_count'] }} votes</small>
                            </div>
                            <div class="fs-5 fw-bold text-primary font-mono" id="pct-{{ $opt['id'] }}">
                                {{ $opt['percentage'] }}%
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>

            <div class="p-3 bg-black rounded-3 font-mono small text-secondary d-flex justify-content-between align-items-center">
                <span><i class="bi bi-broadcast text-success me-1"></i> Listening on channel: <code class="text-info">live-polls</code></span>
                <span id="lastVoteNotice" class="text-light">Awaiting votes...</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function castVote(optionId) {
        try {
            const res = await fetch("{{ route('websocket.polls.vote') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ option_id: optionId })
            });

            const data = await res.json();
            if (data.success) {
                updatePollUI(data.total_votes, data.options, "Your vote was cast!");
            }
        } catch (e) {
            console.error(e);
        }
    }

    function updatePollUI(totalVotes, options, notice) {
        document.getElementById('totalVotesDisplay').innerText = totalVotes;
        if (notice) {
            document.getElementById('lastVoteNotice').innerText = notice;
        }

        options.forEach(opt => {
            const bar = document.getElementById(`bar-${opt.id}`);
            const pct = document.getElementById(`pct-${opt.id}`);
            const cnt = document.getElementById(`count-${opt.id}`);

            if (bar) bar.style.width = `${opt.percentage}%`;
            if (pct) pct.innerText = `${opt.percentage}%`;
            if (cnt) cnt.innerText = `${opt.votes_count} votes`;
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        window.Echo.channel('live-polls')
            .listen('.poll.vote.casted', (e) => {
                updatePollUI(e.total_votes, e.options, `New vote registered at ${new Date(e.voted_at).toLocaleTimeString()}`);
                playChimeSound('info');
            });
    });
</script>
@endsection
