@extends('admin-officer.layouts.app')

@section('title', 'Announcements')
@section('page-title', 'Announcements')
@section('page-subtitle', 'Post updates and notices to your organization members')

@section('content')
<div class="card">
    <div class="toolbar">
        <div class="toolbar-left">Total: {{ $announcements->count() }}</div>
        <button class="btn btn-primary-pill" onclick="openModal('createAnnouncementModal')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="#fff"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
            New Announcement
        </button>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Message</th>
                    <th style="text-align:center">Visibility</th>
                    <th>Linked Event</th>
                    <th>Posted</th>
                    <th style="text-align:center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($announcements as $i => $ann)
                <tr>
                    <td><span class="td-no">A{{ str_pad($i + 1, 4, '0', STR_PAD_LEFT) }}</span></td>
                    <td><span style="font-weight:600;color:#1e293b;">{{ $ann->title }}</span></td>
                    <td style="max-width:280px;">
                        <span style="color:#64748b;font-size:13px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                            {{ $ann->body }}
                        </span>
                    </td>
                    <td style="text-align:center">
                        @if(($ann->visibility ?? 'general') === 'members_only')
                            <span style="background:#fff7ed;color:#c2410c;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;">Org Only</span>
                        @else
                            <span style="background:#f0fdf4;color:#15803d;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;">General</span>
                        @endif
                    </td>
                    <td>
                        @if($ann->event)
                            <span style="background:#eff3ff;color:#4A6CF7;font-size:11px;font-weight:600;padding:3px 9px;border-radius:20px;">
                                {{ Str::limit($ann->event->title, 30) }}
                            </span>
                        @else
                            <span style="color:#94a3b8;font-size:12px;">—</span>
                        @endif
                    </td>
                    <td style="font-size:12px;color:#64748b;">{{ $ann->created_at->format('M j, Y g:i A') }}</td>
                    <td style="text-align:center">
                        <button class="btn btn-sm-pill" style="background:#fee2e2;color:#dc2626;"
                            onclick="deleteAnnouncement({{ $ann->id }})">Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:#94a3b8;padding:40px;">No announcements yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Create Announcement Modal --}}
<div class="modal-overlay" id="createAnnouncementModal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('createAnnouncementModal')">×</button>
        <div class="modal-title">New Announcement</div>
        <form id="createAnnouncementForm">
            @csrf
            <div class="form-group">
                <input type="text" name="title" class="form-control" placeholder="Announcement title" required>
            </div>
            <div class="form-group">
                <textarea name="body" class="form-control" rows="4"
                    placeholder="Write your message here (e.g. Event X has moved to Room 201)..."
                    style="resize:none;" required></textarea>
            </div>
            <div class="form-group">
                <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:6px;">
                    Visibility
                </label>
                <div style="display:flex;gap:10px;">
                    <label style="flex:1;display:flex;align-items:center;gap:8px;cursor:pointer;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:13px;font-weight:600;color:#334155;">
                        <input type="radio" name="visibility" value="general" checked style="accent-color:#4A6CF7;">
                        <span>General</span>
                        <span style="font-size:11px;font-weight:400;color:#94a3b8;margin-left:2px;">Everyone</span>
                    </label>
                    <label style="flex:1;display:flex;align-items:center;gap:8px;cursor:pointer;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:13px;font-weight:600;color:#334155;">
                        <input type="radio" name="visibility" value="members_only" style="accent-color:#4A6CF7;">
                        <span>Org Only</span>
                        <span style="font-size:11px;font-weight:400;color:#94a3b8;margin-left:2px;">Members</span>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:6px;">
                    Link to Event <span style="font-weight:400;color:#94a3b8;">(optional)</span>
                </label>
                <select name="event_id" class="form-control">
                    <option value="">— No linked event —</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}">
                            {{ $event->title }} ({{ \Carbon\Carbon::parse($event->date)->format('M j, Y') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div id="createAnnError" style="color:#ef4444;font-size:12px;margin-bottom:8px;display:none;"></div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('createAnnouncementModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Post Announcement</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Confirm Modal --}}
<div class="modal-overlay" id="deleteAnnModal">
    <div class="modal" style="max-width:420px;">
        <div class="modal-title" style="color:#dc2626;">Delete Announcement</div>
        <p style="font-size:14px;color:#475569;margin:8px 0 20px;">Are you sure you want to delete this announcement? This cannot be undone.</p>
        <input type="hidden" id="deleteAnnId">
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('deleteAnnModal')">Cancel</button>
            <button class="btn btn-primary" style="background:#dc2626;" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('createAnnouncementForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const errEl = document.getElementById('createAnnError');
    errEl.style.display = 'none';
    const formData = new FormData(this);

    fetch('/admin-officer/announcements', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeModal('createAnnouncementModal');
            location.reload();
        } else {
            errEl.textContent = data.message || 'Something went wrong.';
            errEl.style.display = 'block';
        }
    });
});

function deleteAnnouncement(id) {
    document.getElementById('deleteAnnId').value = id;
    openModal('deleteAnnModal');
}

function confirmDelete() {
    const id = document.getElementById('deleteAnnId').value;
    fetch(`/admin-officer/announcements/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(() => location.reload());
}
</script>
@endpush
