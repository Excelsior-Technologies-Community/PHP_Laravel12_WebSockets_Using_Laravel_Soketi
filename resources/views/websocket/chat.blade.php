@extends('layouts.websocket')

@section('title', 'Real-Time Chat & Typing Indicator')

@section('styles')
<style>
    .chat-card {
        max-width: 780px;
        margin: 0 auto;
    }
    .chat-messages-box {
        height: 460px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .message-bubble {
        max-width: 75%;
        padding: 12px 16px;
        border-radius: 16px;
        word-break: break-word;
    }
    .bubble-other {
        background: #1f2937;
        color: #f3f4f6;
        align-self: flex-start;
        border-bottom-left-radius: 4px;
    }
    .bubble-self {
        background: #4f46e5;
        color: #ffffff;
        align-self: flex-end;
        border-bottom-right-radius: 4px;
    }
    .typing-badge {
        font-size: 12px;
        color: #9ca3af;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .dot-flashing {
        width: 6px;
        height: 6px;
        border-radius: 5px;
        background-color: #9ca3af;
        animation: dotFlashing 1s infinite linear alternate;
    }
    @keyframes dotFlashing {
        0% { opacity: 0.2; }
        100% { opacity: 1; }
    }
</style>
@endsection

@section('content')
<!-- Header Banner -->
<div class="card-dark p-4 mb-4 border-0">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary bg-opacity-20 text-primary fw-bold px-3 py-1 rounded-pill">Real-Time Messaging</span>
                <span class="badge bg-warning bg-opacity-20 text-warning fw-bold px-3 py-1 rounded-pill">Typing Indicators</span>
            </div>
            <h2 class="fw-bold mb-1 text-white">✍️ Real-Time Chat Studio & Activity Whisper</h2>
            <p class="text-secondary mb-0">
                Type in the box below to dispatch debounced <code class="text-warning font-mono">user.typing</code> events to other connected browsers via Soketi WebSockets. Messages broadcast immediately without page reload.
            </p>
        </div>
        <div>
            <form action="{{ route('chat.clear') }}" method="POST" onsubmit="return confirm('Clear chat history?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i> Clear Messages
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Main Chat Card -->
<div class="card-dark chat-card p-4 border-0">
    <div class="d-flex justify-content-between align-items-center border-bottom border-secondary border-opacity-25 pb-3 mb-3">
        <div class="d-flex align-items-center gap-2">
            <span class="fs-4">💬</span>
            <div>
                <h5 class="fw-bold text-white mb-0">Public Lounge Room</h5>
                <small class="text-secondary font-mono">Channel: chat-channel &bull; Event: message.sent</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <label class="small text-secondary mb-0">Your Handle:</label>
            <input type="text" id="senderName" class="form-control form-control-sm font-mono bg-dark text-white border-secondary" style="width: 140px;" value="{{ session('guest_presence_name', 'User_' . rand(10, 99)) }}">
        </div>
    </div>

    <!-- Messages Display Area -->
    <div id="messagesArea" class="chat-messages-box p-3 bg-black rounded-3 border border-secondary border-opacity-25 mb-3">
        @forelse($messages as $msg)
            <div class="message-bubble bubble-other">
                <div class="fw-bold text-info small mb-1">{{ $msg->name }}</div>
                <div>{{ $msg->message }}</div>
                <div class="text-end text-secondary font-mono" style="font-size: 10px;">{{ $msg->created_at->format('h:i A') }}</div>
            </div>
        @empty
            <div id="emptyChatMessage" class="text-center py-5 text-secondary">
                <i class="bi bi-chat-heart fs-1 d-block mb-2"></i>
                No messages yet. Send the first message to test the WebSocket!
            </div>
        @endforelse
    </div>

    <!-- Typing Indicator Area -->
    <div id="typingIndicatorBox" class="typing-badge mb-2 d-none">
        <span class="dot-flashing"></span>
        <span id="typingUserText">Someone is typing...</span>
    </div>

    <!-- Message Input Bar -->
    <form id="chatForm" onsubmit="handleSendMessage(event)">
        <div class="input-group">
            <input type="text" id="chatMessageInput" class="form-control bg-dark text-white border-secondary" placeholder="Type a message... (notice typing indicator in other tabs)" oninput="handleTypingInput()">
            <button class="btn btn-primary px-4 fw-semibold" type="submit" id="sendBtn">
                <i class="bi bi-send-fill me-1"></i> Send
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    let typingTimer = null;
    let isBroadcastingTyping = false;

    function handleTypingInput() {
        const username = document.getElementById('senderName').value.trim() || 'Anonymous';
        
        // Broadcast typing start if not already broadcasting
        if (!isBroadcastingTyping) {
            isBroadcastingTyping = true;
            broadcastTyping(username, true);
        }

        // Debounce typing reset after 1.5 seconds of silence
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            isBroadcastingTyping = false;
            broadcastTyping(username, false);
        }, 1500);
    }

    async function broadcastTyping(username, isTyping) {
        try {
            await fetch("{{ route('chat.typing') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ username: username, is_typing: isTyping })
            });
        } catch (e) {
            console.error(e);
        }
    }

    async function handleSendMessage(e) {
        e.preventDefault();
        const msgInput = document.getElementById('chatMessageInput');
        const nameInput = document.getElementById('senderName');
        const message = msgInput.value.trim();
        const name = nameInput.value.trim() || 'Anonymous';

        if (!message) return;

        // Reset typing
        clearTimeout(typingTimer);
        isBroadcastingTyping = false;
        broadcastTyping(name, false);

        msgInput.value = '';

        try {
            const res = await fetch("{{ route('chat.send') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name: name, message: message })
            });
            const data = await res.json();
            // Self-append or wait for echo
        } catch (err) {
            alert('Failed to send message: ' + err.message);
        }
    }

    function appendMessage(name, message, time) {
        const empty = document.getElementById('emptyChatMessage');
        if (empty) empty.remove();

        const box = document.getElementById('messagesArea');
        const myName = document.getElementById('senderName').value.trim();
        const isSelf = (name === myName);

        const bubble = document.createElement('div');
        bubble.className = `message-bubble ${isSelf ? 'bubble-self' : 'bubble-other'}`;
        bubble.innerHTML = `
            <div class="fw-bold ${isSelf ? 'text-white' : 'text-info'} small mb-1">${escapeHtml(name)}</div>
            <div>${escapeHtml(message)}</div>
            <div class="text-end text-light font-mono opacity-75" style="font-size: 10px;">${time}</div>
        `;
        box.appendChild(bubble);
        box.scrollTop = box.scrollHeight;
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Scroll to bottom
        const box = document.getElementById('messagesArea');
        box.scrollTop = box.scrollHeight;

        // Listen on chat-channel
        window.Echo.channel('chat-channel')
            .listen('.message.sent', (e) => {
                appendMessage(e.name, e.message, e.created_at);
                playChimeSound('info');
            })
            .listen('.user.typing', (e) => {
                const myName = document.getElementById('senderName').value.trim();
                if (e.username === myName) return; // Ignore own typing

                const indicator = document.getElementById('typingIndicatorBox');
                const text = document.getElementById('typingUserText');

                if (e.is_typing) {
                    text.innerText = `✍️ ${e.username} is typing...`;
                    indicator.classList.remove('d-none');
                } else {
                    indicator.classList.add('d-none');
                }
            });
    });
</script>
@endsection
