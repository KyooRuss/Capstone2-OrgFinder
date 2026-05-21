@extends('admin-officer.layouts.app')

@section('title', 'Officers')
@section('page-title', 'Officers')
@section('page-subtitle', 'Manage and track officers')

@section('content')
<div class="card">
    <div class="toolbar">
        <div class="toolbar-left">Total Officers: {{ $officers->count() }}</div>
        <form method="GET" style="display:contents;">
            <div class="search-box">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" placeholder="Search officers..." value="{{ request('search') }}">
            </div>
            <div class="filter-wrap">
                <button type="button" class="filter-btn" onclick="toggleFilter('officerFilterDrop')">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="#374151"><path d="M4.25 5.61C6.27 8.2 10 13 10 13v6c0 .55.45 1 1 1h2c.55 0 1-.45 1-1v-6s3.72-4.8 5.74-7.39A.998.998 0 0 0 18.95 4H5.04a1 1 0 0 0-.79 1.61z"/></svg>
                    Filter
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="#374151"><path d="M7 10l5 5 5-5z"/></svg>
                </button>
                <div class="filter-drop" id="officerFilterDrop">
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
                    <td style="text-align:center;white-space:nowrap;">
                        @if(($officer->status ?? 'active') === 'active')
                        <button class="icon-btn" title="Block"
                            onclick="confirmBlock({{ $officer->id }}, '{{ addslashes($officer->name) }}')">
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
                            onclick="confirmRemove({{ $officer->id }}, '{{ addslashes($officer->name) }}')">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#475569">
                                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                            </svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:#94a3b8;padding:40px;border-radius:10px;">No officers found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Block Officer Confirm --}}
<div class="modal-overlay" id="blockOfficerModal">
    <div class="modal" style="max-width:380px;text-align:center;">
        <button class="modal-close" onclick="closeModal('blockOfficerModal')">×</button>
        <div class="modal-warn-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="#ef4444"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        </div>
        <div class="modal-body">
            Are you sure you want to block this officer<br>"<strong id="blockOfficerName"></strong>"?
        </div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('blockOfficerModal')">Cancel</button>
            <button class="btn btn-danger" id="confirmBlockBtn">Block</button>
        </div>
    </div>
</div>

{{-- Remove Officer Confirm --}}
<div class="modal-overlay" id="removeOfficerModal">
    <div class="modal" style="max-width:380px;text-align:center;">
        <button class="modal-close" onclick="closeModal('removeOfficerModal')">×</button>
        <div class="modal-warn-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="#ef4444"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        </div>
        <div class="modal-body">
            Are you sure you want to remove this officer<br>"<strong id="removeOfficerName"></strong>"?
        </div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('removeOfficerModal')">Cancel</button>
            <button class="btn btn-danger" id="confirmRemoveBtn">Remove</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentId = null;

function confirmBlock(id, name) {
    currentId = id;
    document.getElementById('blockOfficerName').textContent = name;
    openModal('blockOfficerModal');
}
function confirmRemove(id, name) {
    currentId = id;
    document.getElementById('removeOfficerName').textContent = name;
    openModal('removeOfficerModal');
}

document.getElementById('confirmBlockBtn').addEventListener('click', function() {
    fetch(`/admin-officer/officers/${currentId}/block`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    }).then(() => location.reload());
});
document.getElementById('confirmRemoveBtn').addEventListener('click', function() {
    fetch(`/admin-officer/officers/${currentId}`, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    }).then(() => location.reload());
});
</script>
@endpush
