@extends('admin-officer.layouts.app')

@section('title', 'Events')
@section('page-title', 'Events')
@section('page-subtitle', 'Track all organizational events')

@section('content')
<div class="card">
    <div class="toolbar">
        <div class="toolbar-left">Total Events: {{ $events->count() }}</div>
        <form method="GET" style="display:contents;">
            <div class="search-box">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" placeholder="Search events..." value="{{ request('search') }}">
            </div>
            <div class="filter-wrap">
                <button type="button" class="filter-btn" onclick="toggleFilter('eventFilterDrop')">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="#374151"><path d="M4.25 5.61C6.27 8.2 10 13 10 13v6c0 .55.45 1 1 1h2c.55 0 1-.45 1-1v-6s3.72-4.8 5.74-7.39A.998.998 0 0 0 18.95 4H5.04a1 1 0 0 0-.79 1.61z"/></svg>
                    Filter
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="#374151"><path d="M7 10l5 5 5-5z"/></svg>
                </button>
                <div class="filter-drop" id="eventFilterDrop">
                    <a href="{{ request()->fullUrlWithQuery(['status' => '']) }}">All</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}">Pending</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'approved']) }}">Approved</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'rejected']) }}">Rejected</a>
                </div>
            </div>
        </form>
        <button class="btn btn-primary-pill" onclick="openModal('createEventModal')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="#fff"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
            Create event
        </button>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Event No.</th>
                    <th>Event Title</th>
                    <th>Date</th>
                    <th style="text-align:center">Status</th>
                    <th style="text-align:center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $i => $event)
                <tr>
                    <td><span class="td-no">E{{ str_pad($i + 1, 4, '0', STR_PAD_LEFT) }}</span></td>
                    <td>
                        <span class="td-name" onclick="viewEvent({{ $event->id }})">{{ $event->title }}</span>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($event->date)->format('n-d-Y') }}</td>
                    <td style="text-align:center">
                        <span class="status-{{ $event->status }}">{{ ucfirst($event->status) }}</span>
                    </td>
                    <td style="text-align:center">
                        <button class="btn btn-dark btn-sm-pill" onclick="viewEvent({{ $event->id }})">View Details</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:#94a3b8;padding:40px;border-radius:10px;">No events found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- View Event Details Modal --}}
<div class="modal-overlay" id="eventDetailModal">
    <div class="modal modal-wide">
        <button class="modal-close" onclick="closeModal('eventDetailModal')">×</button>
        <div style="display:flex;gap:24px;">
            <div class="event-image-box" id="eventImage">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="#bbb"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
            </div>
            <div style="flex:1;min-width:0;">
                <div id="detailStatus" style="font-size:13px;font-weight:600;margin-bottom:6px;"></div>
                <div id="detailTitle" style="font-size:18px;font-weight:700;color:#1a2e6e;margin-bottom:14px;line-height:1.3;"></div>
                <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:16px;">
                    <div id="detailDate" style="display:flex;align-items:center;gap:8px;font-size:13px;color:#555;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="#555"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
                        <span></span>
                    </div>
                    <div id="detailTime" style="display:flex;align-items:center;gap:8px;font-size:13px;color:#555;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="#555"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/></svg>
                        <span></span>
                    </div>
                    <div id="detailVenue" style="display:flex;align-items:center;gap:8px;font-size:13px;color:#555;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="#555"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                        <span></span>
                    </div>
                </div>
                <div style="margin-bottom:14px;">
                    <div style="font-weight:700;font-size:13.5px;color:#1e293b;margin-bottom:6px;">About This Event</div>
                    <p id="detailAbout" style="font-size:13px;color:#64748b;line-height:1.6;"></p>
                </div>
                <div>
                    <div style="font-weight:700;font-size:13.5px;color:#1e293b;margin-bottom:6px;">What you will gain</div>
                    <ul id="detailGains" style="font-size:13px;color:#334155;padding-left:18px;line-height:1.8;"></ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create Event Modal --}}
<div class="modal-overlay" id="createEventModal">
    <div class="modal modal-wide">
        <button class="modal-close" onclick="closeModal('createEventModal')">×</button>
        <div style="display:flex;gap:24px;">
            <div class="event-image-box upload-box" id="uploadBox" onclick="document.getElementById('eventImageInput').click()">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="#aaa"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="#aaa" style="margin-top:-10px;"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                <input type="file" id="eventImageInput" accept="image/*" style="display:none;" onchange="previewImage(this)">
            </div>
            <div style="flex:1;">
                <div class="modal-title">Submit Event Request</div>
                <form id="createEventForm">
                    @csrf
                    <div class="form-group">
                        <input type="text" name="title" class="form-control" placeholder="Enter event title" required>
                    </div>
                    <div style="display:flex;gap:10px;margin-bottom:14px;">
                        <div style="flex:1;">
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div style="flex:1;">
                            <input type="time" name="time" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" name="venue" class="form-control" placeholder="Enter venue / location">
                    </div>
                    <div class="form-group">
                        <textarea name="description" class="form-control" rows="3" placeholder="About This Event" style="resize:none;"></textarea>
                    </div>
                    <div class="form-group">
                        <textarea name="gains" class="form-control" rows="3" placeholder="What you will gain (one per line)" style="resize:none;"></textarea>
                    </div>
                    <div id="createEventError" style="color:#ef4444;font-size:12px;margin-bottom:8px;display:none;"></div>
                    <div style="text-align:right;">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const statusColors = { pending: '#e96500', approved: '#0f9800', rejected: '#eb3223' };

function viewEvent(id) {
    fetch(`/admin-officer/events/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            const el = (i) => document.getElementById(i);
            el('detailStatus').textContent = data.status ? data.status.charAt(0).toUpperCase() + data.status.slice(1) : '';
            el('detailStatus').style.color = statusColors[data.status] || '#555';
            el('detailTitle').textContent = data.title;
            el('detailDate').querySelector('span').textContent = data.date || '';
            el('detailTime').querySelector('span').textContent = data.time || '';
            el('detailVenue').querySelector('span').textContent = data.venue || '';
            el('detailAbout').textContent = data.description || '';
            const gainsList = el('detailGains');
            gainsList.innerHTML = '';
            if (data.gains) {
                data.gains.split('\n').filter(g => g.trim()).forEach(g => {
                    const li = document.createElement('li');
                    li.textContent = g.trim();
                    gainsList.appendChild(li);
                });
            }
            el('eventImage').innerHTML = data.image_url
                ? `<img src="${data.image_url}" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">`
                : `<svg width="40" height="40" viewBox="0 0 24 24" fill="#bbb"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>`;
            openModal('eventDetailModal');
        });
}

document.getElementById('createEventForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const errEl = document.getElementById('createEventError');
    errEl.style.display = 'none';
    const formData = new FormData(this);
    const imgInput = document.getElementById('eventImageInput');
    if (imgInput && imgInput.files[0]) formData.append('image', imgInput.files[0]);

    fetch('/admin-officer/events', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success || data.message === 'Event submitted.') {
            closeModal('createEventModal');
            location.reload();
        } else {
            errEl.textContent = data.message || 'Something went wrong.';
            errEl.style.display = 'block';
        }
    });
});

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('uploadBox').innerHTML =
                `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">
                 <input type="file" id="eventImageInput" accept="image/*" style="display:none;" onchange="previewImage(this)">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
