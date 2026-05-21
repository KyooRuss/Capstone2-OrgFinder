@extends('super-admin.layouts.app')

@section('title', 'Trash - Admin Officers')
@section('page-title', 'Archived Admin Officers')
@section('page-subtitle', 'View and restore removed admin officers')

@section('content')
<div class="card">
    <div class="toolbar">
        <div class="toolbar-left">Total Archived Admin Officers: {{ $officers->count() }}</div>
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <div class="search-box">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="11" cy="11" r="6"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" placeholder="Search officers..." value="{{ request('search') }}">
            </div>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th style="text-align:center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($officers as $officer)
                <tr>
                    <td><span style="color:#3b82f6;font-weight:600;">{{ $officer->name }}</span></td>
                    <td>{{ $officer->email }}</td>
                    <td><span class="badge badge-danger">Removed</span></td>
                    <td style="text-align:center;">
                        <div style="display:flex;gap:8px;justify-content:center;">
                            <button class="btn btn-success btn-sm" onclick="restoreUser({{ $officer->id }})">Restore</button>
                            <button class="btn btn-danger btn-sm" onclick="confirmForceDelete({{ $officer->id }}, '{{ addslashes($officer->name) }}')">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;color:#94a3b8;padding:40px;">No archived admin officers.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="forceDeleteModal">
    <div class="modal" style="max-width:400px;text-align:center;">
        <div class="modal-icon">⚠️</div>
        <div class="modal-body">Permanently delete <strong id="forceDeleteName"></strong>? This cannot be undone.</div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('forceDeleteModal')">Cancel</button>
            <button class="btn btn-danger" id="confirmForceDeleteBtn">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function restoreUser(id) {
    fetch(`/super-admin/trash/users/${id}/restore`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    }).then(() => location.reload());
}
let forceDeleteId = null;
function confirmForceDelete(id, name) {
    forceDeleteId = id;
    document.getElementById('forceDeleteName').textContent = `"${name}"`;
    openModal('forceDeleteModal');
}
document.getElementById('confirmForceDeleteBtn').addEventListener('click', function() {
    fetch(`/super-admin/trash/users/${forceDeleteId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    }).then(() => location.reload());
});
</script>
@endpush
