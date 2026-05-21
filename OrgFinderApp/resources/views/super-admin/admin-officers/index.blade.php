@extends('super-admin.layouts.app')

@section('title', 'Admin Officers')
@section('page-title', 'Admin Officers')
@section('page-subtitle', 'Manage and track all admin officers')

@section('content')
<div class="card">
    <div class="toolbar">
        <div class="toolbar-left">Total Admin Officers: {{ count($officers) }}</div>
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <div class="search-box">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="11" cy="11" r="6"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" placeholder="Search admins..." value="{{ request('search') }}">
            </div>
            <div class="filter-wrap">
                <button type="button" class="filter-btn">Filter ▼</button>
                <div class="filter-drop">
                    <a href="{{ request()->fullUrlWithQuery(['filter' => '']) }}">All</a>
                    <a href="{{ request()->fullUrlWithQuery(['filter' => 'active']) }}">Active</a>
                    <a href="{{ request()->fullUrlWithQuery(['filter' => 'blocked']) }}">Blocked</a>
                </div>
            </div>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Admin No.</th>
                    <th>Admin Name</th>
                    <th>Organization</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th style="text-align:center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($officers as $officer)
                <tr>
                    <td style="font-weight:600;color:#1e3a5c;">{{ $officer['admin_number'] }}</td>
                    <td>
                        <span style="color:#3b82f6;font-weight:600;">{{ $officer['name'] }}</span>
                    </td>
                    <td>{{ $officer['organization'] }}</td>
                    <td style="color:#7c3aed;font-weight:600;">{{ $officer['position'] }}</td>
                    <td>
                        @if($officer['status'] === 'active')
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Blocked</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <div style="display:flex;gap:6px;justify-content:center;align-items:center;">
                            @if($officer['status'] === 'active')
                                <button class="icon-btn" title="Block" onclick="confirmBlock({{ $officer['id'] }}, '{{ addslashes($officer['name']) }}')">
                                    <svg viewBox="0 0 24 24" fill="#94a3b8" width="20" height="20"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg>
                                </button>
                            @else
                                <button class="icon-btn" title="Unblock" onclick="unblockOfficer({{ $officer['id'] }})">
                                    <svg viewBox="0 0 24 24" fill="#22c55e" width="20" height="20"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg>
                                </button>
                            @endif
                            <button class="icon-btn" title="Delete" onclick="confirmDelete({{ $officer['id'] }}, '{{ addslashes($officer['name']) }}')">
                                <svg viewBox="0 0 24 24" fill="#ef4444" width="18" height="18"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:#94a3b8;padding:40px;">No admin officers found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Block Confirm --}}
<div class="modal-overlay" id="blockModal">
    <div class="modal" style="max-width:380px;text-align:center;">
        <div class="modal-icon">⚠️</div>
        <div class="modal-body">
            Are you sure you want to block this admin <strong id="blockName"></strong>?
        </div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('blockModal')">Cancel</button>
            <button class="btn btn-danger" id="confirmBlockBtn">Block</button>
        </div>
    </div>
</div>

{{-- Delete Confirm --}}
<div class="modal-overlay" id="deleteModal">
    <div class="modal" style="max-width:380px;text-align:center;">
        <div class="modal-icon">⚠️</div>
        <div class="modal-body">
            Are you sure you want to remove <strong id="deleteName"></strong>?
        </div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('deleteModal')">Cancel</button>
            <button class="btn btn-danger" id="confirmDeleteBtn">Remove</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let blockId = null, deleteId = null;

function confirmBlock(id, name) {
    blockId = id;
    document.getElementById('blockName').textContent = `"${name}"`;
    openModal('blockModal');
}
document.getElementById('confirmBlockBtn').addEventListener('click', function() {
    fetch(`/super-admin/admin-officers/${blockId}/block`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    }).then(() => location.reload());
});

function unblockOfficer(id) {
    fetch(`/super-admin/admin-officers/${id}/unblock`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    }).then(() => location.reload());
}

function confirmDelete(id, name) {
    deleteId = id;
    document.getElementById('deleteName').textContent = `"${name}"`;
    openModal('deleteModal');
}
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    fetch(`/super-admin/admin-officers/${deleteId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    }).then(() => location.reload());
});
</script>
@endpush
