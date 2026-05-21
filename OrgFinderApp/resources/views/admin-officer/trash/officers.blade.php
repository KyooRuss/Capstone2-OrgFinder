@extends('admin-officer.layouts.app')

@section('title', 'Trash - Officers')
@section('page-title', 'Archived Officers')
@section('page-subtitle', 'View and restore removed officers')

@section('content')
<div class="card">
    <div class="toolbar">
        <div class="toolbar-left">Total Archived Officers: {{ $officers->count() }}</div>
        <form method="GET" style="display:contents;">
            <div class="search-box">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" placeholder="Search officers..." value="{{ request('search') }}">
            </div>
            <div class="filter-wrap">
                <button type="button" class="filter-btn" onclick="toggleFilter('trashOfficerFilterDrop')">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="#374151"><path d="M4.25 5.61C6.27 8.2 10 13 10 13v6c0 .55.45 1 1 1h2c.55 0 1-.45 1-1v-6s3.72-4.8 5.74-7.39A.998.998 0 0 0 18.95 4H5.04a1 1 0 0 0-.79 1.61z"/></svg>
                    Filter
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="#374151"><path d="M7 10l5 5 5-5z"/></svg>
                </button>
                <div class="filter-drop" id="trashOfficerFilterDrop">
                    <a href="{{ request()->fullUrlWithQuery(['status' => '']) }}">All</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'active']) }}">Active</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'blocked']) }}">Blocked</a>
                </div>
            </div>
        </form>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Officer No.</th>
                    <th>Name</th>
                    <th>Year Level</th>
                    <th>Email Address</th>
                    <th style="text-align:center">Status</th>
                    <th style="text-align:center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($officers as $i => $officer)
                <tr>
                    <td><span class="td-no">O{{ str_pad($i + 1, 4, '0', STR_PAD_LEFT) }}</span></td>
                    <td><span class="td-name">{{ $officer->name }}</span></td>
                    <td>{{ $officer->year_level ?? '—' }}</td>
                    <td><span class="td-email">{{ $officer->email }}</span></td>
                    <td style="text-align:center">
                        <span class="status-{{ ($officer->status ?? 'active') === 'active' ? 'active' : 'blocked' }}">
                            {{ ($officer->status ?? 'active') === 'active' ? 'Active' : 'Blocked' }}
                        </span>
                    </td>
                    <td style="text-align:center;white-space:nowrap;display:flex;gap:6px;justify-content:center;">
                        <button class="btn btn-success btn-sm" onclick="restoreUser({{ $officer->id }})">Restore</button>
                        <button class="btn btn-danger btn-sm" onclick="confirmDelete({{ $officer->id }})">Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:#94a3b8;padding:40px;border-radius:10px;">No archived officers.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Delete Confirm Modal --}}
<div class="modal-overlay" id="deleteModal">
    <div class="modal" style="max-width:380px;text-align:center;">
        <button class="modal-close" onclick="closeModal('deleteModal')">×</button>
        <div class="modal-warn-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="#ef4444"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        </div>
        <div class="modal-body">Permanently delete this user? This cannot be undone.</div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('deleteModal')">Cancel</button>
            <button class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let deleteId = null;

function restoreUser(id) {
    fetch(`/admin-officer/trash/users/${id}/restore`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    }).then(() => location.reload());
}
function confirmDelete(id) {
    deleteId = id;
    openModal('deleteModal');
}
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    fetch(`/admin-officer/trash/users/${deleteId}`, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    }).then(() => { closeModal('deleteModal'); location.reload(); });
});
</script>
@endpush
