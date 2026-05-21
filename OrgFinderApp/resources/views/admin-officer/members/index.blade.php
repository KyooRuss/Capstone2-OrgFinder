@extends('admin-officer.layouts.app')

@section('title', 'Members')
@section('page-title', 'Members')
@section('page-subtitle', 'Manage and track members')

@section('content')
<div class="card">
    <div class="toolbar">
        <div class="toolbar-left">Total Members: {{ $members->count() }}</div>
        <form method="GET" style="display:contents;">
            <div class="search-box">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" placeholder="Search members..." value="{{ request('search') }}">
            </div>
            <div class="filter-wrap">
                <button type="button" class="filter-btn" onclick="toggleFilter('memberFilterDrop')">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="#374151"><path d="M4.25 5.61C6.27 8.2 10 13 10 13v6c0 .55.45 1 1 1h2c.55 0 1-.45 1-1v-6s3.72-4.8 5.74-7.39A.998.998 0 0 0 18.95 4H5.04a1 1 0 0 0-.79 1.61z"/></svg>
                    Filter
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="#374151"><path d="M7 10l5 5 5-5z"/></svg>
                </button>
                <div class="filter-drop" id="memberFilterDrop">
                    <a href="{{ request()->fullUrlWithQuery(['status' => '']) }}">All</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'active']) }}">Active</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'blocked']) }}">Blocked</a>
                </div>
            </div>
        </form>
        <button class="btn btn-primary-pill" onclick="openModal('addMemberModal')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="#fff"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
            Add member
        </button>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Member No.</th>
                    <th>Name</th>
                    <th>Year Level</th>
                    <th>Email Address</th>
                    <th style="text-align:center">Status</th>
                    <th style="text-align:center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $i => $member)
                <tr>
                    <td><span class="td-no">M{{ str_pad($i + 1, 4, '0', STR_PAD_LEFT) }}</span></td>
                    <td><span class="td-name">{{ $member->name }}</span></td>
                    <td>{{ $member->year_level ?? '—' }}</td>
                    <td><span class="td-email">{{ $member->email }}</span></td>
                    <td style="text-align:center">
                        <span class="status-{{ ($member->status ?? 'active') === 'active' ? 'active' : 'blocked' }}">
                            {{ ($member->status ?? 'active') === 'active' ? 'Active' : 'Blocked' }}
                        </span>
                    </td>
                    <td style="text-align:center;white-space:nowrap;">
                        <button class="btn btn-dark btn-sm-pill" style="margin-right:4px;"
                            onclick="confirmMakeOfficer({{ $member->id }}, '{{ addslashes($member->name) }}')">
                            Make Officer
                        </button>
                        @if(($member->status ?? 'active') === 'active')
                        <button class="icon-btn" title="Block"
                            onclick="confirmBlock({{ $member->id }}, '{{ addslashes($member->name) }}')">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#e96500" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                            </svg>
                        </button>
                        @else
                        <button class="icon-btn" disabled style="opacity:.4;cursor:default;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                            </svg>
                        </button>
                        @endif
                        <button class="icon-btn" title="Remove"
                            onclick="confirmRemove({{ $member->id }}, '{{ addslashes($member->name) }}')">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#475569">
                                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                            </svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:#94a3b8;padding:40px;border-radius:10px;">No members found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Member Modal --}}
<div class="modal-overlay" id="addMemberModal">
    <div class="modal" style="max-width:440px;">
        <button class="modal-close" onclick="closeModal('addMemberModal')">×</button>
        <div class="modal-title" style="text-align:center;">Add Member</div>
        <form id="addMemberForm">
            @csrf
            <div class="form-group">
                <div class="input-icon-wrap">
                    <span class="icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#94a3b8"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    </span>
                    <input type="email" name="email" class="form-control" placeholder="Enter institutional email" required>
                </div>
            </div>
            <div id="addMemberError" style="color:#ef4444;font-size:12px;margin-bottom:8px;display:none;"></div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('addMemberModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Member</button>
            </div>
        </form>
    </div>
</div>

{{-- Make Officer Confirm --}}
<div class="modal-overlay" id="makeOfficerModal">
    <div class="modal" style="max-width:400px;text-align:center;">
        <button class="modal-close" onclick="closeModal('makeOfficerModal')">×</button>
        <div class="modal-title" style="text-align:center;">Make an Admin</div>
        <div class="modal-body">
            Are you sure you want to make this student<br>
            "<strong id="makeOfficerName"></strong>" an admin officer?
        </div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('makeOfficerModal')">Cancel</button>
            <button class="btn btn-success" id="confirmMakeOfficerBtn">Confirm</button>
        </div>
    </div>
</div>

{{-- Block Member Confirm --}}
<div class="modal-overlay" id="blockMemberModal">
    <div class="modal" style="max-width:380px;text-align:center;">
        <button class="modal-close" onclick="closeModal('blockMemberModal')">×</button>
        <div class="modal-warn-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="#ef4444"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        </div>
        <div class="modal-body">
            Are you sure you want to block this member<br>"<strong id="blockMemberName"></strong>"?
        </div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('blockMemberModal')">Cancel</button>
            <button class="btn btn-danger" id="confirmBlockBtn">Block</button>
        </div>
    </div>
</div>

{{-- Remove Member Confirm --}}
<div class="modal-overlay" id="removeMemberModal">
    <div class="modal" style="max-width:380px;text-align:center;">
        <button class="modal-close" onclick="closeModal('removeMemberModal')">×</button>
        <div class="modal-warn-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="#ef4444"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        </div>
        <div class="modal-body">
            Are you sure you want to remove this member<br>"<strong id="removeMemberName"></strong>"?
        </div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('removeMemberModal')">Cancel</button>
            <button class="btn btn-danger" id="confirmRemoveBtn">Remove</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentMemberId = null;

function confirmMakeOfficer(id, name) {
    currentMemberId = id;
    document.getElementById('makeOfficerName').textContent = name;
    openModal('makeOfficerModal');
}
function confirmBlock(id, name) {
    currentMemberId = id;
    document.getElementById('blockMemberName').textContent = name;
    openModal('blockMemberModal');
}
function confirmRemove(id, name) {
    currentMemberId = id;
    document.getElementById('removeMemberName').textContent = name;
    openModal('removeMemberModal');
}

document.getElementById('confirmMakeOfficerBtn').addEventListener('click', function() {
    fetch(`/admin-officer/members/${currentMemberId}/make-officer`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    }).then(() => location.reload());
});
document.getElementById('confirmBlockBtn').addEventListener('click', function() {
    fetch(`/admin-officer/members/${currentMemberId}/block`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    }).then(() => location.reload());
});
document.getElementById('confirmRemoveBtn').addEventListener('click', function() {
    fetch(`/admin-officer/members/${currentMemberId}`, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    }).then(() => location.reload());
});

document.getElementById('addMemberForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const errEl = document.getElementById('addMemberError');
    errEl.style.display = 'none';
    fetch('/admin-officer/members', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: this.email.value })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { closeModal('addMemberModal'); location.reload(); }
        else { errEl.textContent = data.message || 'Something went wrong.'; errEl.style.display = 'block'; }
    });
});
</script>
@endpush
