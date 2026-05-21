@extends('super-admin.layouts.app')

@section('title', 'Trash - Organizations')
@section('page-title', 'Archived Organizations')
@section('page-subtitle', 'View and restore removed organizations')

@section('content')
<div class="card">
    <div class="toolbar">
        <div class="toolbar-left">Total Archived Organizations: {{ $organizations->count() }}</div>
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <div class="search-box">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="11" cy="11" r="6"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" placeholder="Search organizations..." value="{{ request('search') }}">
            </div>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Organization</th>
                    <th style="text-align:center">No. of Members</th>
                    <th style="text-align:center">No. of Events</th>
                    <th style="text-align:center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($organizations as $org)
                <tr>
                    <td><span style="color:#3b82f6;font-weight:600;">{{ $org->name }}</span></td>
                    <td style="text-align:center">{{ $org->members_count }}</td>
                    <td style="text-align:center">{{ $org->events_count }}</td>
                    <td style="text-align:center;">
                        <div style="display:flex;gap:8px;justify-content:center;">
                            <button class="btn btn-success btn-sm" onclick="restoreOrg({{ $org->id }})">Restore</button>
                            <button class="btn btn-danger btn-sm" onclick="confirmForceDelete({{ $org->id }}, '{{ addslashes($org->name) }}')">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;color:#94a3b8;padding:40px;">No archived organizations.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Permanent Delete Confirm --}}
<div class="modal-overlay" id="forceDeleteModal">
    <div class="modal" style="max-width:400px;text-align:center;">
        <div class="modal-icon">⚠️</div>
        <div class="modal-body">
            Are you sure you want to permanently delete this organization <strong id="forceDeleteName"></strong>? This action cannot be undone.
        </div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('forceDeleteModal')">Cancel</button>
            <button class="btn btn-danger" id="confirmForceDeleteBtn">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function restoreOrg(id) {
    fetch(`/super-admin/trash/organizations/${id}/restore`, {
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
    fetch(`/super-admin/trash/organizations/${forceDeleteId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    }).then(() => location.reload());
});
</script>
@endpush
