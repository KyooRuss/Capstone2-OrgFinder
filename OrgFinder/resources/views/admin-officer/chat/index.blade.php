@extends('admin-officer.layouts.app')

@section('title', 'Group Chat')
@section('page-title', 'Group Chat')
@section('page-subtitle', 'Chat with your organization members')

@push('styles')
<style>
.chat-wrap {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 220px);
    min-height: 400px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(74,108,247,0.08);
    overflow: hidden;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px 20px 8px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    scroll-behavior: smooth;
}

.chat-messages::-webkit-scrollbar { width: 5px; }
.chat-messages::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

.msg-row {
    display: flex;
    flex-direction: column;
    max-width: 70%;
}
.msg-row.mine  { align-self: flex-end; align-items: flex-end; }
.msg-row.other { align-self: flex-start; align-items: flex-start; }

.msg-sender {
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
    margin-bottom: 3px;
    padding: 0 4px;
}

.msg-bubble {
    padding: 9px 14px;
    border-radius: 16px;
    font-size: 14px;
    line-height: 1.45;
    word-break: break-word;
}
.msg-row.mine  .msg-bubble { background: #4A6CF7; color: #fff; border-bottom-right-radius: 4px; }
.msg-row.other .msg-bubble { background: #f0f2ff; color: #1e2f6e; border-bottom-left-radius: 4px; }

.msg-time {
    font-size: 10px;
    color: #b0bec5;
    margin-top: 3px;
    padding: 0 4px;
}

.chat-input-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 16px;
    border-top: 1px solid #f0f2ff;
    background: #fff;
}

.chat-input {
    flex: 1;
    border: 1.5px solid #e2e8f0;
    border-radius: 24px;
    padding: 10px 18px;
    font-size: 14px;
    outline: none;
    color: #1e2f6e;
    transition: border-color 0.2s;
    resize: none;
    line-height: 1.4;
    max-height: 100px;
    overflow-y: auto;
}
.chat-input:focus { border-color: #4A6CF7; }

.chat-send-btn {
    width: 42px; height: 42px;
    border-radius: 50%;
    background: #4A6CF7;
    border: none;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: background 0.2s, transform 0.1s;
}
.chat-send-btn:hover  { background: #3a5ce4; }
.chat-send-btn:active { transform: scale(0.93); }
.chat-send-btn svg    { fill: #fff; width: 20px; height: 20px; }

.chat-empty {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 8px;
    color: #94a3b8;
}
.chat-empty-icon { font-size: 36px; }
.chat-empty-text { font-size: 14px; font-weight: 500; }
</style>
@endpush

@section('content')
<div class="chat-wrap">

    <div class="chat-messages" id="chatMessages">
        @if($messages->isEmpty())
            <div class="chat-empty">
                <div class="chat-empty-icon">💬</div>
                <div class="chat-empty-text">No messages yet. Start the conversation!</div>
            </div>
        @else
            @foreach($messages as $msg)
                @php $mine = $msg->user_id === auth()->id(); @endphp
                <div class="msg-row {{ $mine ? 'mine' : 'other' }}" data-id="{{ $msg->id }}">
                    @if(!$mine)
                        <div class="msg-sender">{{ $msg->user?->name ?? 'Unknown' }}</div>
                    @endif
                    <div class="msg-bubble">{{ $msg->message }}</div>
                    <div class="msg-time">{{ $msg->created_at->setTimezone('Asia/Manila')->format('h:i A') }}</div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="chat-input-row">
        <textarea id="chatInput" class="chat-input" rows="1" placeholder="Type a message…"></textarea>
        <button class="chat-send-btn" id="sendBtn" title="Send">
            <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
const POLL_URL  = '{{ route('admin-officer.chat.poll') }}';
const SEND_URL  = '{{ route('admin-officer.chat.send') }}';
const CSRF      = csrfToken();

let lastId = {{ $messages->last()?->id ?? 0 }};

function appendMessage(msg) {
    const wrap = document.getElementById('chatMessages');
    const empty = wrap.querySelector('.chat-empty');
    if (empty) empty.remove();

    const row = document.createElement('div');
    row.className = 'msg-row ' + (msg.is_mine ? 'mine' : 'other');
    row.dataset.id = msg.id;

    let html = '';
    if (!msg.is_mine) html += `<div class="msg-sender">${msg.sender_name}</div>`;
    html += `<div class="msg-bubble">${escHtml(msg.message)}</div>`;
    html += `<div class="msg-time">${msg.time}</div>`;

    row.innerHTML = html;
    wrap.appendChild(row);
    wrap.scrollTop = wrap.scrollHeight;
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function scrollBottom() {
    const wrap = document.getElementById('chatMessages');
    wrap.scrollTop = wrap.scrollHeight;
}

// Initial scroll
scrollBottom();

// Polling every 3 seconds
setInterval(async () => {
    try {
        const res = await fetch(`${POLL_URL}?after=${lastId}`, {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        data.messages.forEach(msg => {
            appendMessage(msg);
            if (msg.id > lastId) lastId = msg.id;
        });
    } catch {}
}, 3000);

// Send message
async function sendMessage() {
    const input = document.getElementById('chatInput');
    const text  = input.value.trim();
    if (!text) return;

    input.value = '';
    input.style.height = 'auto';

    try {
        const res = await fetch(SEND_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message: text }),
        });
        const data = await res.json();
        if (data.message) {
            appendMessage(data.message);
            if (data.message.id > lastId) lastId = data.message.id;
        }
    } catch {}
}

document.getElementById('sendBtn').addEventListener('click', sendMessage);

document.getElementById('chatInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

// Auto-resize textarea
document.getElementById('chatInput').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
});
</script>
@endpush
