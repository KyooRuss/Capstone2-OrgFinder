@extends('super-admin.layouts.app')

@section('title', 'Upcoming Events')
@section('page-title', 'Upcoming Events')
@section('page-subtitle', 'Manage and track all upcoming events')

@section('content')
<div class="card">
    <div class="toolbar">
        <div class="toolbar-left">Total Upcoming Events: {{ $events->count() }}</div>
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <div class="search-box">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="11" cy="11" r="6"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" placeholder="Search events..." value="{{ request('search') }}">
            </div>
            <div class="filter-wrap">
                <button type="button" class="filter-btn">
                    Filter {{ request('filter') ? '('.ucfirst(request('filter')).')' : '' }} ▼
                </button>
                <div class="filter-drop">
                    <a href="{{ request()->fullUrlWithQuery(['filter' => '']) }}">All</a>
                    <a href="{{ request()->fullUrlWithQuery(['filter' => 'pending']) }}">Pending</a>
                    <a href="{{ request()->fullUrlWithQuery(['filter' => 'approved']) }}">Approved</a>
                    <a href="{{ request()->fullUrlWithQuery(['filter' => 'rejected']) }}">Rejected</a>
                </div>
            </div>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Organization</th>
                    <th>Event Title</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th style="text-align:center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                <tr>
                    <td>{{ $event->organization->name }}</td>
                    <td>{{ $event->title }}</td>
                    <td>{{ $event->date->format('m-d-Y') }}</td>
                    <td>{{ date('g:i A', strtotime($event->start_time)) }}</td>
                    <td>
                        <span class="badge badge-{{ $event->status }}">
                            {{ ucfirst($event->status) }}
                        </span>
                    </td>
                    <td style="text-align:center;display:flex;gap:6px;justify-content:center;align-items:center;">
                        <button class="btn btn-primary btn-sm" onclick="viewEvent({{ $event->id }})">View Details</button>
                        <button class="icon-btn" onclick="confirmDeleteEvent({{ $event->id }}, '{{ addslashes($event->title) }}')">
                            <svg viewBox="0 0 24 24" fill="#ef4444" width="18" height="18"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:#94a3b8;padding:40px;">No upcoming events found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Event Detail Modal --}}
<div class="modal-overlay" id="eventModal">
    <div class="modal" style="max-width:600px;">
        <button class="modal-close" onclick="closeModal('eventModal')">×</button>
        <div style="display:flex;gap:20px;">
            <div id="eventPosterWrap" style="width:160px;min-height:200px;background:#e2e8f0;border-radius:10px;flex-shrink:0;overflow:hidden;">
                <img id="eventPoster" src="" style="width:100%;height:100%;object-fit:cover;display:none;">
            </div>
            <div style="flex:1;">
                <div id="eventStatusBadge" style="margin-bottom:8px;"></div>
                <div id="eventTitle" style="font-size:17px;font-weight:700;color:#1e3a5c;margin-bottom:10px;"></div>
                <div style="display:flex;flex-direction:column;gap:5px;margin-bottom:14px;">
                    <span id="eventDate" style="font-size:13px;color:#64748b;">📅</span>
                    <span id="eventTime" style="font-size:13px;color:#64748b;">🕐</span>
                    <span id="eventLocation" style="font-size:13px;color:#64748b;">📍</span>
                </div>
                <div style="margin-bottom:12px;">
                    <p style="font-size:12px;font-weight:700;color:#1e3a5c;margin-bottom:4px;">About This Event</p>
                    <p id="eventDesc" style="font-size:13px;color:#64748b;line-height:1.6;"></p>
                </div>
                <div>
                    <p style="font-size:12px;font-weight:700;color:#1e3a5c;margin-bottom:6px;">What you will gain</p>
                    <ul id="eventGains" style="list-style:disc;padding-left:18px;font-size:13px;color:#64748b;line-height:1.8;"></ul>
                </div>
            </div>
        </div>
        <div id="eventActions" style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;"></div>
    </div>
</div>

{{-- Delete Event Confirm --}}
<div class="modal-overlay" id="deleteEventModal">
    <div class="modal" style="max-width:380px;text-align:center;">
        <div class="modal-icon">⚠️</div>
        <div class="modal-body">Are you sure you want to delete <strong id="deleteEventName"></strong>?</div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('deleteEventModal')">Cancel</button>
            <button class="btn btn-danger" id="confirmDeleteEventBtn">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentEventId = null;

function viewEvent(id) {
    currentEventId = id;
    fetch(`/super-admin/events/${id}`, { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(e => {
        document.getElementById('eventTitle').textContent = `${e.title} (${e.organization})`;
        document.getElementById('eventDate').textContent = '📅 ' + e.date;
        document.getElementById('eventTime').textContent = '🕐 ' + e.start_time + (e.end_time ? ' – ' + e.end_time : '');
        document.getElementById('eventLocation').textContent = '📍 ' + (e.location || 'TBA');
        document.getElementById('eventDesc').textContent = e.description || '';

        const gainsEl = document.getElementById('eventGains');
        gainsEl.innerHTML = (e.gains || []).map(g => `<li>${g}</li>`).join('');

        const poster = document.getElementById('eventPoster');
        if (e.poster) { poster.src = e.poster; poster.style.display = 'block'; }
        else { poster.style.display = 'none'; }

        const badge = document.getElementById('eventStatusBadge');
        const colors = { approved:'badge-approved', rejected:'badge-rejected', pending:'badge-pending' };
        badge.innerHTML = `<span class="badge ${colors[e.status]}">${e.status.charAt(0).toUpperCase()+e.status.slice(1)}</span>`;

        const actions = document.getElementById('eventActions');
        if (e.status === 'pending') {
            actions.innerHTML = `
                <button class="btn btn-danger" onclick="rejectEvent(${e.id})">Reject</button>
                <button class="btn btn-success" onclick="approveEvent(${e.id})">Approve</button>
            `;
        } else {
            actions.innerHTML = '';
        }

        openModal('eventModal');
    });
}

function approveEvent(id) {
    fetch(`/super-admin/events/${id}/approve`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    }).then(() => { closeModal('eventModal'); location.reload(); });
}

function rejectEvent(id) {
    fetch(`/super-admin/events/${id}/reject`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    }).then(() => { closeModal('eventModal'); location.reload(); });
}

let deleteEventId = null;
function confirmDeleteEvent(id, name) {
    deleteEventId = id;
    document.getElementById('deleteEventName').textContent = `"${name}"`;
    openModal('deleteEventModal');
}
document.getElementById('confirmDeleteEventBtn').addEventListener('click', function() {
    fetch(`/super-admin/events/${deleteEventId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    }).then(() => location.reload());
});
</script>
@endpush
